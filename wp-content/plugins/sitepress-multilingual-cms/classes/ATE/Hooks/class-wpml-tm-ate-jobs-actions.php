<?php

use WPML\API\Sanitize;
use WPML\FP\Fns;
use WPML\FP\Json;
use WPML\FP\Logic;
use WPML\FP\Lst;
use WPML\FP\Obj;
use WPML\FP\Str;
use WPML\FP\Relation;
use WPML\TM\API\Jobs;
use WPML\TM\API\Job\Map;
use WPML\FP\Wrapper;
use WPML\Settings\PostType\Automatic;
use WPML\Setup\Option;
use WPML\TM\ATE\API\ClientRestriction;
use WPML\TM\ATE\API\ErrorMessages;
use WPML\TM\ATE\JobRecords;
use WPML\TM\ATE\NothingToTranslateJob;
use WPML\TM\ATE\Log\Storage;
use WPML\TM\ATE\Log\Entry;
use function WPML\FP\partialRight;
use function WPML\FP\pipe;
use WPML\TM\API\ATE\LanguageMappings;
use WPML\Element\API\Languages;
use WPML\TM\Jobs\JobLog;
use WPML\Translation\AteSyncOrderingServiceFactory;

class WPML_TM_ATE_Jobs_Actions implements IWPML_Action {
	const ATE_RECORD_SPENT_STATUSES = [ 8, 20, 34, 40, 42 ];

	const RESPONSE_ATE_NOT_ACTIVE_ERROR     = 403;
	const RESPONSE_ATE_DUPLICATED_SOURCE_ID = 417;
	const RESPONSE_ATE_CLIENT_RESTRICTED    = 428;
	const RESPONSE_ATE_UNEXPECTED_ERROR     = 500;

	const RESPONSE_ATE_ERROR_NOTICE_ID    = 'ate-update-error';
	const RESPONSE_ATE_ERROR_NOTICE_GROUP = 'default';

	const CREATE_ATE_JOB_CHUNK_WORDS_LIMIT = 2000;

	private $ate_api;
	private $ate_jobs;

	private $translator_activation_records;

	private $is_second_attempt_to_get_jobs_data = false;
	private $sitepress;
	private $current_screen;

	private $wp_api;

	public function __construct(
		WPML_TM_ATE_API $ate_api,
		WPML_TM_ATE_Jobs $ate_jobs,
		SitePress $sitepress,
		WPML_Current_Screen $current_screen,
		WPML_TM_AMS_Translator_Activation_Records $translator_activation_records,
		WPML_WP_API $wp_api
	) {
		$this->ate_api                       = $ate_api;
		$this->ate_jobs                      = $ate_jobs;
		$this->sitepress                     = $sitepress;
		$this->current_screen                = $current_screen;
		$this->translator_activation_records = $translator_activation_records;
		$this->wp_api                        = $wp_api;
	}

	public function add_hooks() {
		add_action( 'wpml_added_translation_job', [ $this, 'added_translation_job' ], 10, 2 );
		add_action( 'wpml_added_translation_jobs', [ $this, 'added_translation_jobs' ], 10, 3 );

		if ( ! $this->wp_api->is_front_end() ) {
			add_action( 'admin_notices', [ $this, 'handle_messages' ] );

			add_filter( 'wpml_tm_ate_jobs_data', [ $this, 'get_ate_jobs_data_filter' ], 10, 2 );
			add_filter( 'wpml_tm_ate_jobs_editor_url', [ $this, 'get_editor_url' ], 10, 3 );
		}
	}

	public function handle_messages() {
		if ( $this->current_screen->id_ends_with( WPML_TM_FOLDER . '/menu/translations-queue' ) ) {

			if ( array_key_exists( 'message', $_GET ) ) {
				$message = Sanitize::stringProp( 'message', $_GET );
				?>

				<div class="error notice-error notice otgs-notice">
					<p><?php echo $message; ?></p>
				</div>

				<?php
			}
		}
	}

	public function added_translation_job( $job_id, $translation_service ) {
		$this->added_translation_jobs( array( $translation_service => array( $job_id ) ) );
	}

	public function added_translation_jobs( array $jobs, $sentFrom = null, ?\WPML_TM_Translation_Batch $batch = null ) {
		$additionalErrorMsg            = '';
		$translationModeSetInDashboard = null;
		$created_jobs                  = [];
		$adopted_jobs                  = [];
		$job_ids                       = [];

		try {
			$oldEditor = wpml_tm_load_old_jobs_editor();
			$job_ids   = Fns::reject( [ $oldEditor, 'shouldStickToWPMLEditor' ], Obj::propOr( [], 'local', $jobs ) );

			if ( ! $job_ids ) {
				return;
			}

			if ( Jobs::SENT_RETRY === $sentFrom ) {
				$adopted_jobs = $this->adoptJobsAlreadyAtAte( $job_ids );
				$job_ids      = array_values( array_diff( $job_ids, array_keys( $adopted_jobs ) ) );
			}

			if ( $job_ids ) {
				$applyTranslationMemory = $this->shouldApplyTranslationMemory( $batch );

				$jobs = Fns::map(
					function ( $jobId ) use ( $applyTranslationMemory ) {
						return wpml_tm_create_ATE_job_creation_model( $jobId, $applyTranslationMemory );
					},
					$job_ids
				);
				$jobs = array_values( array_filter( $jobs ) );

				list( $jobsWithNothingToTranslate, $jobs ) = Lst::partition(
					[ NothingToTranslateJob::class, 'isJobModel' ],
					$jobs
				);

				if ( $jobsWithNothingToTranslate ) {
					$emptyJobIds = [];
					foreach ( $jobsWithNothingToTranslate as $emptyJobModel ) {
						$this->complete_job_with_nothing_to_translate( (int) $emptyJobModel->id );
						$emptyJobIds[] = $emptyJobModel->id;
					}

					$job_ids = array_values( array_diff( $job_ids, $emptyJobIds ) );

					if ( ! $jobs ) {
						if ( ! $adopted_jobs ) {
							return;
						}

						$job_ids = [];
					}
				}
			}

			if ( $job_ids ) {
				$translationModeSetInDashboard = $this->getTranslationModeFromBatch( $batch );
				$responses                     = Fns::map(
					Fns::unary( partialRight( [ $this, 'create_jobs' ], $sentFrom ) ),
					$this->getChunkedJobs( $jobs, $translationModeSetInDashboard )
				);
				$created_jobs                  = $this->getResponsesJobs( $responses, $jobs );
			}
		} catch ( \Throwable $throwable ) {
			$created_jobs       = [];
			$additionalErrorMsg = $this->getErrorMessage( $throwable );
			$this->maybeLogRuntimeError( $additionalErrorMsg );

			if ( empty( $oldEditor ) || ( empty( $job_ids ) && empty( $adopted_jobs ) ) ) {
				return;
			}
		}

		$bound_jobs = $adopted_jobs + ( $created_jobs ? $this->map_response_jobs( $created_jobs ) : [] );

		if ( $bound_jobs ) {

			$this->ate_jobs->warm_cache( array_keys( $bound_jobs ) );

			foreach ( $bound_jobs as $wpml_job_id => $ate_job_id ) {
				$this->ate_jobs->store( $wpml_job_id, [ JobRecords::FIELD_ATE_JOB_ID => $ate_job_id ] );
				$oldEditor->set( $wpml_job_id, WPML_TM_Editors::ATE );
				$translationJob = wpml_tm_load_job_factory()->get_translation_job( $wpml_job_id, false, 0, true );
                if ( $translationJob ) {
	                $jobType = $this->getJobType( $translationJob, $translationModeSetInDashboard );
	                Jobs::setAutomaticStatus( $wpml_job_id, $jobType === 'auto' );
                }

				JobLog::add( 'ate_job_bound', [
					'job_id'              => (int) $wpml_job_id,
					'ate_job_id'          => (int) $ate_job_id,
					'sent_from'           => $sentFrom,
					'target_lang'         => JobLog::safeCall( $translationJob, 'get_language_code' ),
					'original_element_id' => JobLog::safeCall( $translationJob, 'get_original_element_id' ),
				] );

				if ( $sentFrom === Jobs::SENT_RETRY ) {
					$isAutomatic = $translationJob
						&& $this->getJobType( $translationJob, $translationModeSetInDashboard ) === 'auto';
					Jobs::setStatus(
						$wpml_job_id,
						$isAutomatic ? ICL_TM_IN_PROGRESS : ICL_TM_WAITING_FOR_TRANSLATOR
					);
				}
			}

			/* translators: Message shown after content is handed to the Advanced Translation Editor. %1$s: how many translation jobs were handed over. */
			$message = __( '%1$s jobs added to the Advanced Translation Editor.', 'sitepress' );
			$this->add_message( 'updated', sprintf( $message, count( $bound_jobs ) ), 'wpml_tm_ate_create_job' );

			do_action( 'wpml_tm_ate_jobs_created', $bound_jobs );
		}

		if ( ! $created_jobs && $job_ids ) {
			if ( Lst::includes( $sentFrom, [ Jobs::SENT_AUTOMATICALLY, Jobs::SENT_RETRY ] ) ) {
				if ( $sentFrom === Jobs::SENT_RETRY ) {
					$updateJob = function ( $jobId ) {
						Jobs::incrementRetryCount( $jobId );
						$this->logRetryError( $jobId );
					};
				} else {
					$updateJob = function ( $jobId ) use ( $oldEditor, $additionalErrorMsg ) {
						$this->logError( $jobId, $additionalErrorMsg );

						Jobs::setStatus( $jobId, ICL_TM_ATE_NEEDS_RETRY );
						$oldEditor->set( $jobId, WPML_TM_Editors::ATE );
						wpml_tm_load_job_factory()->update_job_data( $jobId, [ 'automatic' => 1 ] );
					};
				}

				wpml_collect( $job_ids )->map( $updateJob );
			}
		}
	}

	private function adoptJobsAlreadyAtAte( array $job_ids ) {
		$job_ids = array_values( array_map( 'intval', $job_ids ) );

		if ( ! $job_ids ) {
			return [];
		}

		$ridByJob = [];
		foreach ( $job_ids as $job_id ) {
			$rid = (int) Map::fromJobId( $job_id );
			if ( $rid > 0 ) {
				$ridByJob[ $job_id ] = $rid;
			}
		}

		if ( ! $ridByJob ) {
			return [];
		}

		try {
			$response = $this->ate_api->get_jobs_by_wpml_ids( array_values( array_unique( $ridByJob ) ) );
		} catch ( \Throwable $throwable ) {
			$this->maybeLogRuntimeError( $this->getErrorMessage( $throwable ) );

			return [];
		}

		if ( ! $response || is_wp_error( $response ) ) {
			return [];
		}

		$adopted = [];
		$refused = [];
		foreach ( $ridByJob as $job_id => $rid ) {
			$entry = null;
			if ( is_object( $response ) && isset( $response->{$rid} ) ) {
				$entry = $response->{$rid};
			} elseif ( is_array( $response ) && isset( $response[ $rid ] ) ) {
				$entry = $response[ $rid ];
			}

			$ate_job_id = (int) Obj::prop( 'ate_job_id', $entry );

			if ( $ate_job_id <= 0 ) {
				continue;
			}

			$maker = (int) Obj::prop( 'wpml_job_id', $entry );
			if ( $maker && $maker !== $job_id ) {
				$refused[ $job_id ] = [ 'rid' => $rid, 'ate_job_id' => $ate_job_id, 'reason' => 'other_job', 'made_by' => $maker ];
				continue;
			}

			$newest = (int) Map::fromRid( $rid );
			if ( $newest && $newest !== $job_id ) {
				$refused[ $job_id ] = [ 'rid' => $rid, 'ate_job_id' => $ate_job_id, 'reason' => 'superseded', 'newest_job_id' => $newest ];
				continue;
			}

			$holder = (int) $this->ate_jobs->get_wpml_job_id( $ate_job_id );
			if ( $holder && $holder !== $job_id ) {
				$refused[ $job_id ] = [ 'rid' => $rid, 'ate_job_id' => $ate_job_id, 'reason' => 'held', 'held_by' => $holder ];
				continue;
			}

			$ate_status = Obj::prop( 'status_id', $entry );
			if ( null === $ate_status ) {
				$ate_status = Obj::prop( 'status', $entry );
			}
			if ( null !== $ate_status && in_array( (int) $ate_status, self::ATE_RECORD_SPENT_STATUSES, true ) ) {
				$refused[ $job_id ] = [ 'rid' => $rid, 'ate_job_id' => $ate_job_id, 'reason' => 'spent', 'ate_status' => (int) $ate_status ];
				continue;
			}

			$adopted[ $job_id ] = $ate_job_id;
		}

		if ( $refused ) {
			JobLog::add(
				'ate_job_adoption_refused',
				[
					'jobs'  => $refused,
					'count' => count( $refused ),
				]
			);
		}

		if ( $adopted ) {
			JobLog::add(
				'ate_jobs_adopted_on_retry',
				[
					'jobs'  => $adopted,
					'count' => count( $adopted ),
				]
			);
		}

		return $adopted;
	}

	private function complete_job_with_nothing_to_translate( $jobId ) {
		try {
			NothingToTranslateJob::completeJob( $jobId );
		} catch ( \Throwable $throwable ) {
			$this->maybeLogRuntimeError( $this->getErrorMessage( $throwable ) );
		}
	}

	private function map_response_jobs( $responseJobs ) {
		$result = [];
		foreach ( $responseJobs as $rid => $ate_job_id ) {
			$jobId = \WPML\TM\API\Job\Map::fromRid( $rid );
			if ( $jobId ) {
				$result[ $jobId ] = $ate_job_id;
			}
		}

		return $result;
	}

	private function add_message( $type, $message, $id = null ) {
		do_action( 'wpml_tm_add_message', $type, $message, $id );
	}

	public function create_jobs( array $jobsData, $sentFrom  ) {
		$setJobType = Logic::ifElse( Fns::always( $sentFrom ), Obj::assoc( 'job_type', $sentFrom ), Fns::identity() );

		list( $existing, $new ) = Lst::partition(
			pipe( Obj::propOr( null, 'existing_ate_id' ), Logic::isNotNull() ),
			$jobsData['jobs']
		);

		$isAuto = Relation::propEq( 'type', 'auto', $jobsData );

		$ordering = $this->buildOrderingForJobs( $jobsData['jobs'] );

		return Wrapper::of( [ 'jobs' => $new, 'existing_jobs' => Lst::pluck( 'existing_ate_id', $existing ) ] )
			->map( Obj::assoc( 'auto_translate', $isAuto ) )
			->map( Obj::assoc( 'preview', $isAuto && Option::shouldBeReviewed() ) )
			->map( $setJobType )
			->map( $this->addOrderingToPayload( $ordering ) )
			->map( 'wp_json_encode' )
			->map( Json::toArray() )
			->map( [ $this->ate_api, 'create_jobs' ] )
			->get();
	}

	private function buildOrderingForJobs( array $jobs ): array {
		$postIds    = [];
		$stringIds  = [];
		$packageIds = [];
		$jobFactory = wpml_tm_load_job_factory();

		foreach ( $jobs as $job ) {
			$jobId = is_object( $job ) ? ( $job->id ?? null ) : ( $job['id'] ?? null );
			if ( ! $jobId ) {
				continue;
			}

			$translationJob = $jobFactory->get_translation_job( $jobId, false, 0, true );
			if ( $translationJob instanceof \WPML_Post_Translation_Job ) {
				$postIds[] = $translationJob->get_original_element_id();
			} elseif ( $translationJob instanceof \WPML_Package_Translation_Job ) {
				$packageIds[] = $translationJob->get_original_element_id();
			} elseif ( strpos( $translationJob->get_basic_data_property( 'original_post_type' ) ?? '', 'st-batch_' ) === 0 ) {
				$stringIds[] = $translationJob->get_original_element_id();
			}
		}

		if ( empty( $postIds ) && empty( $stringIds ) && empty( $packageIds ) ) {
			return [];
		}

		$orderingService = AteSyncOrderingServiceFactory::create();
		return $orderingService->getOrderingPayloadArrayForPosts(
			array_unique( $postIds ),
			array_unique( $stringIds ),
			array_unique( $packageIds )
		);
	}

	private function addOrderingToPayload( array $ordering ): callable {
		return function ( $payload ) use ( $ordering ) {
			if ( ! empty( $ordering['positions'] ) ) {
				$payload['ordering'] = $ordering;
			}
			return $payload;
		};
	}

	private function get_ate_jobs_data( array $translation_jobs ) {
		$ate_jobs_data      = array();
		$skip_getting_data  = false;
		$ate_jobs_to_create = array();

		$this->ate_jobs->warm_cache( wpml_collect( $translation_jobs )->pluck( 'job_id' )->toArray() );

		foreach ( $translation_jobs as $translation_job ) {
			if ( $this->is_ate_translation_job( $translation_job ) ) {
				$ate_job_id = $this->get_ate_job_id( $translation_job->job_id );
				if ( ! $ate_job_id ) {
					$ate_jobs_to_create[] = $translation_job->job_id;
					$skip_getting_data    = true;
				}

				if ( ! $skip_getting_data ) {
					$ate_jobs_data[ $translation_job->job_id ] = [ 'ate_job_id' => $ate_job_id ];
				}
			}
		}

		if (
			! $this->is_second_attempt_to_get_jobs_data &&
			$ate_jobs_to_create
		) {
			$this->added_translation_jobs( array( 'local' => $ate_jobs_to_create ) );
			$ate_jobs_data                            = $this->get_ate_jobs_data( $translation_jobs );
			$this->is_second_attempt_to_get_jobs_data = true;
		}

		return $ate_jobs_data;
	}

	public function get_editor_url( $default_url, $job_id, $return_url = null ) {
		$isUserActivated = $this->translator_activation_records->is_current_user_activated();

		$canUseAutomatic = \WPML\TM\Jobs\EditAccess\AutomaticTranslationPermission::isAllowedForCurrentUser();

		if ( ( $isUserActivated && $canUseAutomatic ) || is_admin() ) {
			$ate_job_id = $this->ate_jobs->get_ate_job_id( $job_id );
			if ( $ate_job_id ) {
				if ( ! $return_url ) {
					$return_url = add_query_arg(
						array(
							'page' => WPML_TM_FOLDER . '/menu/main.php',
							'tab'  => 'tasks',
						),
						admin_url( '/admin.php' )
					);
				}

				$return_url = add_query_arg( 'ate-return-job', $job_id, $return_url );

				$return_url  = \WPML\TM\ATE\ReturnToken::sign( $return_url, $job_id );
				$ate_job_url = $this->ate_api->get_editor_url( $ate_job_id, $return_url );
				if ( $ate_job_url && ! is_wp_error( $ate_job_url ) ) {
					return $ate_job_url;
				}
			}
		}

		return $default_url;
	}

	public function get_ate_jobs_data_filter( $ignore, array $translation_jobs ) {
		return $this->get_ate_jobs_data( $translation_jobs );
	}

	private function get_ate_job_id( $job_id ) {
		return $this->ate_jobs->get_ate_job_id( $job_id );
	}

	protected function check_response_error( $response ) {
		if ( is_wp_error( $response ) ) {
			$code    = 0;
			$message = $response->get_error_message();
			if ( $response->error_data && is_array( $response->error_data ) ) {
				foreach ( $response->error_data as $error_data ) {
					$code    = (int) Obj::pathOr( 0, [ 0, 'status' ], $error_data );
					$message = ( $code ? $code . ' ' : '' ) . Obj::pathOr( '', [ 0, 'message' ], $error_data ) . "\n\n";

					switch ( $code ) {
						case self::RESPONSE_ATE_NOT_ACTIVE_ERROR:
							$message .= ErrorMessages::ateNotActive();
							break;
						case self::RESPONSE_ATE_CLIENT_RESTRICTED:
							$restriction = ClientRestriction::fromAteError( $response );
							$message     = $restriction
								? ErrorMessages::clientRestricted()
								: $message . __( 'Advanced Translation Editor error.', 'sitepress' );
							break;
						case self::RESPONSE_ATE_DUPLICATED_SOURCE_ID:
						case self::RESPONSE_ATE_UNEXPECTED_ERROR:
						default:
							$message .= __( 'Advanced Translation Editor error.', 'sitepress' );
					}
				}
			}
			throw new RuntimeException( $message, $code );
		}
	}

	private function is_ate_translation_job( $translation_job ) {
		return 'local' === $translation_job->translation_service
			   && WPML_TM_Editors::ATE === $translation_job->editor;
	}

	private function getResponsesJobs( $responses, $sentJobs ) {
		$jobs = [];

		foreach ( $responses as $response ) {
			try {
				$this->check_response_error( $response );

				if ( $response && isset( $response->jobs ) ) {
					$jobs = $jobs + (array) $response->jobs;
				}
			} catch ( RuntimeException $ex ) {
				do_action( 'wpml_tm_add_message', 'error', $ex->getMessage() );
			}
		}

		$existingJobs = wpml_collect( $sentJobs )
			->filter( Obj::prop( 'existing_ate_id' ) )
			->map( Obj::pick( [ 'source_id', 'existing_ate_id' ] ) )
			->keyBy( 'source_id' )
			->map( Obj::prop( 'existing_ate_id' ) )
			->toArray();

		return $jobs + $existingJobs;
	}

	private function getChunkedJobs( $jobs, $translationModeSetInDashboard ) {
		usort(
			$jobs,
			function ( $a, $b ) {
				$tierA = isset( $a->tier ) ? (int) $a->tier : PHP_INT_MAX;
				$tierB = isset( $b->tier ) ? (int) $b->tier : PHP_INT_MAX;
				if ( $tierA !== $tierB ) {
					return $tierA - $tierB;
				}
				$rankA = $a->rank ?? [];
				$rankB = $b->rank ?? [];
				foreach ( array_keys( array_merge( $rankA, $rankB ) ) as $i ) {
					$ra = $rankA[ $i ] ?? PHP_INT_MAX;
					$rb = $rankB[ $i ] ?? PHP_INT_MAX;
					if ( $ra !== $rb ) {
						return $ra <=> $rb;
					}
				}
				return 0;
			}
		);
		$chunkedJobs      = [];
		$currentChunk     = -1;
		$currentWordCount = 0;
		$chunkType = 'auto';

		$newChunk = function( $chunkType ) use ( &$chunkedJobs, &$currentChunk, &$currentWordCount ) {
			$currentChunk ++;
			$currentWordCount             = 0;
			$chunkedJobs[ $currentChunk ] = [ 'type' => $chunkType, 'jobs' => [] ];
		};

		$newChunk( $chunkType );

		foreach ( $jobs as $job ) {

			$translationJob = wpml_tm_load_job_factory()->get_translation_job( $job->id, false, 0, true );
			if ( $translationJob ) {

				$jobWords = Obj::prop( 'existing_ate_id', $job )
					? 0
					: $this->getJobSizeInWords( $translationJob );

				$currentWordCount += $jobWords;

				$jobType = $this->getJobType( $translationJob, $translationModeSetInDashboard );
				if ( $jobType !== $chunkType ) {
					$chunkType = $jobType;
					$newChunk( $chunkType );
					$currentWordCount = $jobWords;
				}
				if (
					$currentWordCount > self::CREATE_ATE_JOB_CHUNK_WORDS_LIMIT
					&& count( $chunkedJobs[ $currentChunk ]['jobs'] ) > 0
				) {
					$newChunk( $chunkType );
					$currentWordCount = $jobWords;
				}
			}

			$chunkedJobs[ $currentChunk ]['jobs'] [] = $job;

		}

		$hasJobs = pipe( Obj::prop( 'jobs' ), Lst::length() );

		return Fns::filter( $hasJobs, $chunkedJobs );
	}

	private function getJobSizeInWords( $translationJob ) {
		return (int) $translationJob->estimate_word_count();
	}

	private function logRetryError( $jobId ) {
		$job = Jobs::get( $jobId );

		if ( isset( $job->ate_comm_retry_count ) && $job->ate_comm_retry_count ) {
			Storage::add( Entry::retryJob( $jobId,
				[
					'retry_count' => $job->ate_comm_retry_count
				]
			) );
		}
	}

	private function logError( $jobId, string $additionalErrorMsg = '' ) {
		$job = Jobs::get( $jobId );
		if ( $job ) {
			$extraData = [
				'retry_count' => 0,
				'comment'     => 'Sending job to ate failed, queued to be sent again.',
			];
			if ( $additionalErrorMsg ) {
				$extraData['errorMessage'] = $additionalErrorMsg;
			}

			Storage::add( Entry::retryJob( $jobId, $extraData ) );
		}
	}

	private function getJobType( $translationJob, $translationModeSetInDashboard ) {
		$document = $translationJob->get_original_document();
		if ( ! $document ) {
			return 'manual';
		} elseif ( $translationModeSetInDashboard ) {
			return $translationModeSetInDashboard;
		} else {
			return Jobs::isEligibleForAutomaticTranslations( $translationJob->get_id() ) ? 'auto' : 'manual';
		}
	}

	private function shouldApplyTranslationMemory(?\WPML_TM_Translation_Batch $batch = null) {
		if ( $this->getTranslationModeFromBatch( $batch ) === 'auto' ) {
			return ! ( $batch &&  $batch->getHowToHandleExisting() === \WPML_TM_Translation_Batch::HANDLE_EXISTING_OVERRIDE );
		} else {
			return true;
		}
	}

	private function getTranslationModeFromBatch(?\WPML_TM_Translation_Batch $batch = null) {
		return $batch ? $batch->getTranslationMode() : null;
	}

	private function maybeLogRuntimeError( string $message ) {
		JobLog::addError(
			'WPML_TM_ATE_Jobs_Actions error',
			[
				'message' => $message,
			]
		);

		if ( $this->wp_api->constant( 'WP_DEBUG' ) ) {
			$this->wp_api->error_log( $message );
		}
	}

	private function getErrorMessage( \Throwable $throwable ): string {
		return 'Error: ' . $throwable->getMessage() . ' in ' . $throwable->getFile() . ':' . $throwable->getLine();
	}
}
