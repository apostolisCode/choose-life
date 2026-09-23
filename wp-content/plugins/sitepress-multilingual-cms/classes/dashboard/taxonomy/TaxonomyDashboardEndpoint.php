<?php

namespace WPML\TM\Dashboard\Taxonomy;

use WPML\Element\API\Languages;
use WPML\LIB\WP\User;
use WPML\Setup\Option;
use WPML\TM\API\Job\Map;
use WPML\TM\ATE\JobRecords;
use WPML\TM\ATE\Review\TermJob;
use WPML\TM\Taxonomy\Job\BuildsTaxonomyTermJobModel;
use WPML\TM\XLIFF\TaxonomyTermXliffBuilder;
use WP_REST_Request;
use WP_REST_Server;
use WPML_TM_ATE;
use WPML_TM_ATE_AMS_Endpoints;
use WPML_TM_ATE_API;
use WPML_TM_ATE_Models_Job_Create;
use WPML_TM_Editors;

class TaxonomyDashboardEndpoint extends \WPML_REST_Base implements \IWPML_Backend_Action, \IWPML_REST_Action {

	use BuildsTaxonomyTermJobModel;

	const OPTION_REQUESTED_TAXONOMIES = 'wpml-dashboard-taxonomy-translation-requests';

	const ATE_JOB_BATCH_SIZE = 20;

	const SEND_TIME_BUDGET_RATIO = 0.8;

	const SEND_TIME_BUDGET_SECONDS = 20;

	const MAX_JOBS_BUILT_PER_REQUEST = self::ATE_JOB_BATCH_SIZE;

	private $sendRollback = [];

	private $insertedJobIds = [];

	private $acceptedRids = [];

	const EVENT_SEND_BUDGET_REACHED   = 'taxonomy_send_stopped_on_time_budget';
	const EVENT_SEND_BATCH_FAILED     = 'taxonomy_send_batch_failed';
	const EVENT_SEND_ATE_ERROR        = 'taxonomy_send_ate_error';
	const EVENT_SEND_ATE_NO_JOBS_MAP  = 'taxonomy_send_ate_response_without_jobs_map';
	const EVENT_SEND_RID_UNMAPPED     = 'taxonomy_send_rid_not_mapped_to_job';
	const EVENT_SEND_XLIFF_FAILED     = 'taxonomy_send_xliff_build_failed';
	const EVENT_SEND_API_UNAVAILABLE  = 'taxonomy_send_ate_api_unavailable';
	const EVENT_SEND_LABELS_FAILED    = 'taxonomy_send_labels_dispatch_failed';
	const EVENT_SYNC_DOWNLOAD_FAILED  = 'taxonomy_term_job_sync_download_failed';
	const EVENT_SEND_ROUND_FINISHED   = 'taxonomy_send_round_finished';
	const EVENT_SEND_ROLLED_BACK      = 'taxonomy_send_rolled_back_unsent_rows';

	public function __construct() {
		parent::__construct( \WPML_REST_Base::REST_NAMESPACE );
	}

	public function add_hooks() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {
		$this->register_route(
			'/dashboard/taxonomies',
			[
				'methods'  => WP_REST_Server::READABLE,
				'callback' => [ $this, 'getRows' ],
				'args'     => [
					'since' => [
						'type'     => 'string',
						'required' => false,
					],
					'search' => [
						'type'     => 'string',
						'required' => false,
					],
				],
			]
		);

		$this->register_route(
			'/dashboard/taxonomies/rescan-labels',
			[
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => [ $this, 'rescanLabels' ],
				'args'     => [
					'taxonomy' => [
						'type'     => 'string',
						'required' => true,
					],
				],
			]
		);

		$this->register_route(
			'/dashboard/taxonomies/translate',
			[
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => [ $this, 'translate' ],
				'args'     => [
					'selectedTaxonomies' => [
						'type'     => 'array',
						'required' => true,
					],
					'selectedLabels'     => [
						'type'    => 'array',
						'default' => [],
					],
					'languageCodes'      => [
						'type' => 'array',
					],
					'alreadyTranslated'  => [
						'type'    => 'string',
						'default' => 'leave',
					],
					'whenFinished'       => [
						'type'    => 'string',
						'default' => 'publish',
					],
				],
			]
		);
	}

	public function get_allowed_capabilities( WP_REST_Request $request ) {
		return [ User::CAP_ADMINISTRATOR, User::CAP_MANAGE_TRANSLATIONS ];
	}

	public function getRows( WP_REST_Request $request ) {
		$since  = (string) $request->get_param( 'since' );
		$search = trim( (string) $request->get_param( 'search' ) );

		$snapshot = $this->getStateSnapshot();

		if ( '' === $search && '' !== $since && $since === $snapshot['token'] && 0 === $snapshot['inProgressCount'] ) {
			return [
				'unchanged' => true,
				'token'     => $snapshot['token'],
			];
		}

		$this->syncInProgressTermJobs();

		$data = ( new TaxonomyDashboardData() )->get( $search );

		$snapshot      = $this->getStateSnapshot();
		$data['token'] = $snapshot['token'];

		return $data;
	}

	private function getStateSnapshot() {
		global $wpdb;

		$termState = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT COUNT(*) AS cnt,
				MAX(ts.timestamp) AS latest,
				COALESCE( SUM(ts.status), 0 ) AS statusSum,
				COALESCE( SUM(ts.needs_update), 0 ) AS needsUpdateSum,
				COALESCE( SUM( CASE WHEN ts.status IN ( %d, %d ) THEN 1 ELSE 0 END ), 0 ) AS inProgress
				FROM {$wpdb->prefix}icl_translation_status ts
				INNER JOIN {$wpdb->prefix}icl_translations t ON t.translation_id = ts.translation_id
				WHERE t.element_type LIKE %s",
				ICL_TM_WAITING_FOR_TRANSLATOR,
				ICL_TM_IN_PROGRESS,
				$wpdb->esc_like( 'tax_' ) . '%'
			),
			ARRAY_A
		);

		$termPopulation = $wpdb->get_row(
			"SELECT COUNT(*) AS cnt, COALESCE( MAX(translation_id), 0 ) AS maxId
			FROM {$wpdb->prefix}icl_translations
			WHERE element_type LIKE 'tax\\_%'",
			ARRAY_A
		);

		$stringState = [
			'cnt'       => 0,
			'maxId'     => 0,
			'statusSum' => 0,
			'strings'   => 0,
			'maxString' => 0,
		];
		if ( defined( 'WPML_ST_VERSION' ) ) {
			$translations = $wpdb->get_row(
				"SELECT COUNT(*) AS cnt, COALESCE( MAX(id), 0 ) AS maxId, COALESCE( SUM(status), 0 ) AS statusSum
				FROM {$wpdb->prefix}icl_string_translations",
				ARRAY_A
			);
			$strings      = $wpdb->get_row(
				"SELECT COUNT(*) AS cnt, COALESCE( MAX(id), 0 ) AS maxId
				FROM {$wpdb->prefix}icl_strings",
				ARRAY_A
			);
			if ( is_array( $translations ) && is_array( $strings ) ) {
				$stringState = [
					'cnt'       => $translations['cnt'],
					'maxId'     => $translations['maxId'],
					'statusSum' => $translations['statusSum'],
					'strings'   => $strings['cnt'],
					'maxString' => $strings['maxId'],
				];
			}
		}

		$components = array_merge(
			array_values( is_array( $termState ) ? $termState : [] ),
			array_values( is_array( $termPopulation ) ? $termPopulation : [] ),
			array_values( $stringState ),
			[ \WPML\Setup\Option::shouldTranslateEverything() ? '1' : '0' ]
		);

		return [
			'token'           => md5( implode( '|', array_map( 'strval', $components ) ) ),
			'inProgressCount' => is_array( $termState ) ? (int) $termState['inProgress'] : 0,
		];
	}

	protected function syncInProgressTermJobs() {
		$termJobIds = $this->getInProgressTermJobIds();
		if ( empty( $termJobIds ) ) {
			return;
		}

		$lock    = \WPML\Container\make( \WPML\TM\ATE\SyncLock::class );
		$lockKey = $lock->create( null );
		if ( ! $lockKey ) {
			return;
		}

		try {
			$result = \WPML\Container\make( \WPML\TM\ATE\Sync\Process::class )
				->run( new \WPML\TM\ATE\Sync\Arguments() );

			$jobs = ( isset( $result->jobs ) && is_array( $result->jobs ) ) ? $result->jobs : [];

			$termJobs = array_values(
				array_filter(
					$jobs,
					function ( $job ) use ( $termJobIds ) {
						return is_object( $job )
							&& property_exists( $job, 'jobId' )
							&& in_array( (int) $job->jobId, $termJobIds, true );
					}
				)
			);

			if ( $termJobs ) {
				$applied = \WPML\Container\make( \WPML\TM\ATE\Download\Process::class )->run( $termJobs );
				$applied = is_object( $applied ) && method_exists( $applied, 'all' ) ? $applied->all() : (array) $applied;

				$appliedForUi = \WPML\TM\ATE\PullDelivery\State::appliedJobsForTheUi( $applied );
				if ( $appliedForUi ) {
					\WPML\TM\ATE\PullDelivery\State::onJobsDelivered( $appliedForUi, $this->pendingJobs()->summary() );
				}
			}

			$this->unstuckDeliveredTermJobs( $jobs, $termJobIds );
		} catch ( \Throwable $e ) {
			\WPML\TM\Jobs\JobLog::addError(
				self::EVENT_SYNC_DOWNLOAD_FAILED,
				[ 'error' => $e->getMessage() ]
			);
		} finally {
			$lock->release();
		}
	}

	protected function pendingJobs() {
		return \WPML\Container\make( \WPML\TM\ATE\PullDelivery\PendingJobs::class );
	}

	private function getInProgressTermJobIds(): array {
		global $wpdb;

		$jobIds = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT j.job_id
				FROM {$wpdb->prefix}icl_translate_job j
				INNER JOIN (
					SELECT pending_jobs.rid, MAX(pending_jobs.job_id) AS job_id
					FROM {$wpdb->prefix}icl_translate_job pending_jobs
					INNER JOIN {$wpdb->prefix}icl_translation_status pending_status
						ON pending_status.rid = pending_jobs.rid
					WHERE pending_status.status IN ( %d, %d )
					GROUP BY pending_jobs.rid
				) latest_job ON latest_job.job_id = j.job_id
				INNER JOIN {$wpdb->prefix}icl_translation_status ts ON ts.rid = j.rid
				INNER JOIN {$wpdb->prefix}icl_translations t ON t.translation_id = ts.translation_id
				WHERE j.editor = %s
					AND ts.status IN ( %d, %d )
					AND t.element_type LIKE %s",
				ICL_TM_IN_PROGRESS,
				ICL_TM_WAITING_FOR_TRANSLATOR,
				WPML_TM_Editors::ATE,
				ICL_TM_IN_PROGRESS,
				ICL_TM_WAITING_FOR_TRANSLATOR,
				$wpdb->esc_like( 'tax_' ) . '%'
			)
		);

		return array_map( 'intval', (array) $jobIds );
	}

	private function unstuckDeliveredTermJobs( $jobs, array $termJobIds ) {
		if ( ! is_array( $jobs ) || empty( $termJobIds ) ) {
			return;
		}

		foreach ( $jobs as $job ) {
			if (
				is_object( $job )
				&& property_exists( $job, 'jobId' )
				&& property_exists( $job, 'ateStatus' )
				&& \WPML_TM_ATE_Job::ATE_JOB_DELIVERED === $job->ateStatus
				&& in_array( (int) $job->jobId, $termJobIds, true )
			) {
				\WPML\TM\API\Jobs::setStatus( $job->jobId, ICL_TM_COMPLETE );
			}
		}
	}

	public function rescanLabels( WP_REST_Request $request ) {
		$taxonomy = (string) $request->get_param( 'taxonomy' );

		return $this->makeLabelStatus()->rescan( $taxonomy );
	}

	protected function makeLabelStatus(): TaxonomyLabelStatus {
		return new TaxonomyLabelStatus();
	}

	public function translate( WP_REST_Request $request ) {
		$selected       = array_values( array_filter( (array) $request->get_param( 'selectedTaxonomies' ) ) );
		$selectedLabels = array_values( array_filter( (array) $request->get_param( 'selectedLabels' ) ) );
		$languages      = (array) $request->get_param( 'languageCodes' );
		$overwrite      = $request->get_param( 'alreadyTranslated' ) === 'overwrite';
		$methods        = (array) $request->get_param( 'methods' );
		$isContinuation = (bool) $request->get_param( 'continuation' );

		\WPML\TM\Jobs\JobLog::maybeInitRequest();
		\WPML\TM\Jobs\JobLog::createNewGroup(
			\WPML\TM\Jobs\JobLog::GROUP_ID_SEND_JOBS,
			'Sending taxonomy terms to translation',
			[
				'selectedTaxonomies' => $selected,
				'selectedLabels'     => $selectedLabels,
				'languageCodes'      => (array) $request->get_param( 'languageCodes' ),
				'methods'            => $methods,
				'alreadyTranslated'  => $overwrite ? 'overwrite' : 'leave',
			]
		);

		if ( empty( $languages ) ) {
			$languages = Languages::getSecondaryCodes();
		}

		$languages = \WPML\LanguageEditor\TranslationPause::filterTranslatable( $languages );

		$labelLanguages = array_values(
			array_filter(
				$languages,
				function ( $lang ) use ( $methods ) {
					return self::createsLabelJobs( $methods, (string) $lang );
				}
			)
		);
		$labelsQueued = $this->translateLabels( $selectedLabels, $labelLanguages, $overwrite );

		if ( empty( $selected ) ) {
			return [
				'queued'        => 0,
				'sentToAte'     => 0,
				'remaining'     => 0,
				'completed'     => true,
				'taxonomies'    => 0,
				'labelsQueued'  => $labelsQueued,
				'labels'        => count( $selectedLabels ),
				'languages'     => $languages,
				'teaActive'     => (bool) Option::shouldTranslateEverything(),
			];
		}

		global $wpdb;
		$this->sendRollback   = [];
		$this->insertedJobIds = [];
		$this->acceptedRids   = [];

		$data        = new TaxonomyDashboardData();
		$ateJobs     = [];
		$queued      = 0;
		$moreToBuild = false;

		foreach ( $selected as $taxonomy ) {
			foreach ( $languages as $lang ) {
				if ( ! self::createsTermJobs( $methods, (string) $lang ) ) {
					continue;
				}

				$excludeLiveJobs = $isContinuation || ! $overwrite;
				$terms           = $data->getUntranslatedTermsWithSource( (string) $taxonomy, (string) $lang, $overwrite, $excludeLiveJobs );

				foreach ( $terms as $termId => $sourceLang ) {
					if ( count( $ateJobs ) >= self::MAX_JOBS_BUILT_PER_REQUEST ) {
						$moreToBuild = true;
						break 3;
					}

					$jobModel = $this->prepareTermJob(
						(int) $termId,
						(string) $taxonomy,
						(string) $sourceLang,
						(string) $lang,
						$overwrite
					);
					if ( $jobModel ) {
						$ateJobs[] = $jobModel;
						$queued++;
					}
				}
			}
		}

		$sentToAte = 0;
		$ateError  = null;

		if ( $ateJobs ) {
			$sentToAte = $this->postJobsToAte( $ateJobs, $ateError );

			$this->rollBackUnsentJobs( $this->jobsAteDidNotAccept( $ateJobs ) );
		}

		$this->recordRequestedTaxonomies( $selected, $languages );

		do_action(
			'wpml_dashboard_taxonomy_translation_requested',
			[
				'selectedTaxonomies' => $selected,
				'languageCodes'      => $languages,
				'alreadyTranslated'  => $overwrite ? 'overwrite' : 'leave',
				'whenFinished'       => $request->get_param( 'whenFinished' ),
			]
		);

		$remaining = max( 0, count( $ateJobs ) - $sentToAte );

		$isComplete = 0 === $remaining && ! $moreToBuild;

		\WPML\TM\Jobs\JobLog::add(
			self::EVENT_SEND_ROUND_FINISHED,
			[
				'continuation' => $isContinuation,
				'built'        => count( $ateJobs ),
				'sentToAte'    => $sentToAte,
				'remaining'    => $remaining,
				'moreToBuild'  => $moreToBuild,
				'completed'    => $isComplete,
				'queued'       => $queued,
				'labelsQueued' => $labelsQueued,
				'ateError'     => $ateError,
			]
		);

		$response = [
			'queued'        => $queued,
			'sentToAte'     => $sentToAte,
			'remaining'     => $remaining,
			'completed'     => $isComplete,
			'taxonomies'    => count( $selected ),
			'labelsQueued'  => $labelsQueued,
			'labels'        => count( $selectedLabels ),
			'languages'     => array_values( $languages ),
			'teaActive'     => (bool) Option::shouldTranslateEverything(),
		];
		if ( $ateError ) {
			$response['ateError'] = $ateError;
		}

		return $response;
	}

	private function translateLabels( array $labelTaxonomies, array $languages, bool $overwrite = false ): int {
		if ( empty( $labelTaxonomies ) ) {
			return 0;
		}

		try {
			return $this->makeLabelStatus()->dispatchLabels( $labelTaxonomies, $languages, $overwrite );
		} catch ( \Throwable $e ) {
			\WPML\TM\Jobs\JobLog::addError(
				self::EVENT_SEND_LABELS_FAILED,
				[ 'taxonomies' => $labelTaxonomies, 'languages' => $languages, 'error' => $e->getMessage() ]
			);

			return 0;
		}
	}

	private function prepareTermJob(
		int $termTaxonomyId,
		string $taxonomy,
		string $sourceLang,
		string $targetLang,
		bool $overwrite
	) {
		global $wpdb;

		$source = $this->resolveTaxonomyTermSource( $wpdb, $termTaxonomyId, $taxonomy );
		if ( null === $source ) {
			return null;
		}
		list( $term, $trid ) = $source;

		$elementType = 'tax_' . $taxonomy;

		$targetTranslationId = $this->ensureTargetTranslationRow( $trid, $elementType, $targetLang, $sourceLang );
		if ( ! $targetTranslationId ) {
			return null;
		}

		$rid = $this->ensureTranslationStatusRow( $targetTranslationId, $overwrite );
		if ( ! $rid ) {
			return null;
		}

		$jobId = $this->ensureTranslateJobRow( $rid, $term->name );
		if ( ! $jobId ) {
			return null;
		}

		$this->storeSourceSnapshot( $term, $targetLang );

		try {
			$xliff = ( new TaxonomyTermXliffBuilder() )->build( $term, $sourceLang, $targetLang );
		} catch ( \Throwable $e ) {
			\WPML\TM\Jobs\JobLog::addError(
				self::EVENT_SEND_XLIFF_FAILED,
				[ 'termId' => $term->term_id, 'targetLang' => $targetLang, 'error' => $e->getMessage() ]
			);
			return null;
		}

		$this->logJobPreview( $term, $taxonomy, $sourceLang, $targetLang, $termTaxonomyId, $jobId, $rid, $xliff );

		return $this->buildTaxonomyJobModel(
			$jobId,
			$rid,
			$termTaxonomyId,
			$term,
			$sourceLang,
			$targetLang,
			$xliff
		);
	}

	private function storeSourceSnapshot( \WP_Term $term, string $targetLang ) {
		$metaText = \WPML\TM\Taxonomy\TranslatableTermMeta::metaText( $term );
		$source   = trim(
			wp_strip_all_tags(
				$term->name . "\n" . ( $term->description ?? '' ) . ( '' !== $metaText ? "\n" . $metaText : '' )
			)
		);

		update_term_meta(
			(int) $term->term_id,
			TaxonomyDashboardData::WC_SNAPSHOT_META_PREFIX . $targetLang,
			$source
		);
	}

	private function ensureTargetTranslationRow( int $trid, string $elementType, string $targetLang, string $sourceLang ): int {
		global $wpdb;

		$existing = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT translation_id FROM {$wpdb->prefix}icl_translations
				WHERE trid = %d AND language_code = %s",
				$trid,
				$targetLang
			),
			ARRAY_A
		);
		if ( $existing ) {
			return (int) $existing['translation_id'];
		}

		$wpdb->insert(
			$wpdb->prefix . 'icl_translations',
			[
				'element_type'         => $elementType,
				'trid'                 => $trid,
				'language_code'        => $targetLang,
				'source_language_code' => $sourceLang,
			],
			[ '%s', '%d', '%s', '%s' ]
		);

		return (int) $wpdb->insert_id;
	}

	private function ensureTranslationStatusRow( int $translationId, bool $overwrite ): int {
		global $wpdb;

		$existing = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT rid, status, needs_update, translation_service, translator_id FROM {$wpdb->prefix}icl_translation_status WHERE translation_id = %d",
				$translationId
			),
			ARRAY_A
		);

		if ( $existing ) {
			$currentStatus = (int) $existing['status'];
			$needsUpdate   = (int) ( $existing['needs_update'] ?? 0 );
			if ( $currentStatus === (int) ICL_TM_COMPLETE && 0 === $needsUpdate && ! $overwrite ) {
				return 0;
			}
			$this->sendRollback[ (int) $existing['rid'] ] = [
				'created'      => false,
				'status'       => $currentStatus,
				'needs_update' => $needsUpdate,
				'service'      => (string) ( $existing['translation_service'] ?? '' ),
				'translator'   => (int) ( $existing['translator_id'] ?? 0 ),
			];

			$wpdb->update(
				$wpdb->prefix . 'icl_translation_status',
				[
					'status'              => ICL_TM_IN_PROGRESS,
					'translation_service' => 'local',
					'translator_id'       => 0,
					'needs_update'        => 0,
				],
				[ 'translation_id' => $translationId ],
				[ '%d', '%s', '%d', '%d' ],
				[ '%d' ]
			);
			return (int) $existing['rid'];
		}

		$wpdb->insert(
			$wpdb->prefix . 'icl_translation_status',
			[
				'translation_id'      => $translationId,
				'status'              => ICL_TM_IN_PROGRESS,
				'translation_service' => 'local',
				'translator_id'       => 0,
				'batch_id'            => 0,
				'needs_update'        => 0,
				'md5'                 => '',
				'translation_package' => '',
			],
			[ '%d', '%d', '%s', '%d', '%d', '%d', '%s', '%s' ]
		);

		$rid = (int) $wpdb->insert_id;
		$this->sendRollback[ $rid ] = [ 'created' => true ];

		return $rid;
	}

	private function ensureTranslateJobRow( int $rid, string $title ): int {
		global $wpdb;

		$factory = new \WPML\TM\Taxonomy\Job\TermJobRowFactory( $wpdb, User::getCurrentId() );
		$jobId   = $factory->resolveJobRow( $rid, $title );

		$insertedJobId = $factory->getInsertedJobId();
		if ( $insertedJobId ) {
			$this->insertedJobIds[ $rid ][] = $insertedJobId;
		}

		return $jobId;
	}

	protected function postJobsToAte( array $jobs, ?string &$error ): int {
		try {
			$ateApi = \WPML\Container\make( WPML_TM_ATE_API::class );
		} catch ( \Throwable $e ) {
			$error = 'ATE API container resolution failed: ' . $e->getMessage();
			\WPML\TM\Jobs\JobLog::addError( self::EVENT_SEND_API_UNAVAILABLE, [ 'error' => $error ] );
			return 0;
		}

		$batches    = array_chunk( array_values( $jobs ), self::ATE_JOB_BATCH_SIZE );
		$batchCount = count( $batches );
		$accepted   = 0;
		$startedAt  = self::isWebRequest() && isset( $_SERVER['REQUEST_TIME_FLOAT'] )
			? (float) $_SERVER['REQUEST_TIME_FLOAT']
			: microtime( true );
		$slowest    = 0.0;

		foreach ( $batches as $index => $batch ) {
			if ( $this->wouldExceedTimeBudget( $startedAt, $slowest ) ) {
				\WPML\TM\Jobs\JobLog::add(
					self::EVENT_SEND_BUDGET_REACHED,
					[
						'batch'      => $index + 1,
						'batchCount' => $batchCount,
						'persisted'  => $accepted,
						'budget'     => $this->timeBudget(),
						'elapsed'    => round( microtime( true ) - $startedAt, 1 ),
					]
				);
				break;
			}

			$batchStartedAt = microtime( true );
			$batchError     = null;
			$accepted      += $this->sendBatchToAte( $ateApi, $batch, $index, $batchCount, $batchError );
			$slowest        = max( $slowest, microtime( true ) - $batchStartedAt );

			if ( $batchError ) {
				$error = $batchError;
				\WPML\TM\Jobs\JobLog::addError(
					self::EVENT_SEND_BATCH_FAILED,
					[
						'batch'      => $index + 1,
						'batchCount' => $batchCount,
						'persisted'  => $accepted,
						'error'      => $batchError,
					]
				);
				break;
			}
		}

		return $accepted;
	}

	private static function createsTermJobs( array $methods, string $lang ): bool {
		return isset( $methods[ $lang ] ) && 'automatic' === (string) $methods[ $lang ];
	}

	private static function createsLabelJobs( array $methods, string $lang ): bool {
		if ( ! isset( $methods[ $lang ] ) ) {
			return false;
		}

		return ! in_array( (string) $methods[ $lang ], [ 'do_nothing', 'duplicate', 'please_choose' ], true );
	}

	private function jobsAteDidNotAccept( array $ateJobs ): array {
		return array_values(
			array_filter(
				$ateJobs,
				function ( $job ) {
					$rid = (int) ( $job->source_id ?? 0 );

					return ! $rid || ! isset( $this->acceptedRids[ $rid ] );
				}
			)
		);
	}

	private function rollBackUnsentJobs( array $unsent ) {
		if ( ! $unsent ) {
			return;
		}

		global $wpdb;

		$removedJobs = 0;
		$restored = 0;

		foreach ( $unsent as $job ) {
			$rid = (int) ( $job->source_id ?? 0 );
			if ( ! $rid ) {
				continue;
			}

			foreach ( $this->insertedJobIds[ $rid ] ?? [] as $ownJobId ) {
				$removedJobs += (int) $wpdb->query(
					$wpdb->prepare(
						"DELETE FROM {$wpdb->prefix}icl_translate_job WHERE rid = %d AND job_id = %d",
						$rid,
						(int) $ownJobId
					)
				);
			}

			$before = $this->sendRollback[ $rid ] ?? null;
			if ( ! $before ) {
				continue;
			}

			if ( ! empty( $before['created'] ) ) {
				$restored += (int) $wpdb->update(
					$wpdb->prefix . 'icl_translation_status',
					[
						'status'              => ICL_TM_NOT_TRANSLATED,
						'needs_update'        => 0,
						'translation_service' => '',
						'translator_id'       => 0,
					],
					[ 'rid' => $rid ],
					[ '%d', '%d', '%s', '%d' ],
					[ '%d' ]
				);
				continue;
			}

			$restored += (int) $wpdb->update(
				$wpdb->prefix . 'icl_translation_status',
				[
					'status'              => (int) $before['status'],
					'needs_update'        => (int) $before['needs_update'],
					'translation_service' => (string) $before['service'],
					'translator_id'       => (int) $before['translator'],
				],
				[ 'rid' => $rid ],
				[ '%d', '%d', '%s', '%d' ],
				[ '%d' ]
			);
		}

		\WPML\TM\Jobs\JobLog::add(
			self::EVENT_SEND_ROLLED_BACK,
			[
				'unsent'           => count( $unsent ),
				'job_rows_removed' => $removedJobs,
				'status_restored'  => $restored,
			]
		);
	}

	protected function timeBudget(): float {
		$limit = (int) ini_get( 'max_execution_time' );

		$absolute  = self::isWebRequest() ? (float) self::SEND_TIME_BUDGET_SECONDS : 0.0;
		$fromLimit = $limit > 0 ? $limit * self::SEND_TIME_BUDGET_RATIO : 0.0;

		$bounds = array_filter( [ $absolute, $fromLimit ] );
		$budget = $bounds ? (float) min( $bounds ) : 0.0;

		return (float) apply_filters( 'wpml_taxonomy_send_time_budget', $budget, $limit );
	}

	protected static function isWebRequest(): bool {
		return 'cli' !== PHP_SAPI && 'phpdbg' !== PHP_SAPI;
	}

	private function wouldExceedTimeBudget( float $startedAt, float $slowest ): bool {
		$budget = $this->timeBudget();
		if ( $budget <= 0 ) {
			return false;
		}

		if ( $slowest <= 0.0 ) {
			return false;
		}

		return ( microtime( true ) - $startedAt ) + $slowest > $budget;
	}

	private function sendBatchToAte( WPML_TM_ATE_API $ateApi, array $batch, int $index, int $batchCount, ?string &$error ): int {
		$encodedPayload = wp_json_encode(
			[
				'jobs'           => array_values( $batch ),
				'existing_jobs'  => [],
				'auto_translate' => true,
				'preview'        => (bool) Option::shouldBeReviewed(),
				'job_type'       => 'auto',
			]
		);
		$payload = is_string( $encodedPayload ) ? json_decode( $encodedPayload, true ) : null;


		$response = $this->createAteJobs( $ateApi, $payload );

		if ( is_wp_error( $response ) ) {
			$error = $response->get_error_message();
			\WPML\TM\Jobs\JobLog::addError(
				self::EVENT_SEND_ATE_ERROR,
				[ 'batch' => $index + 1, 'batchCount' => $batchCount, 'error' => $error ]
			);
			return 0;
		}


		$encodedResponse = wp_json_encode( $response );
		$normalised      = is_string( $encodedResponse ) ? json_decode( $encodedResponse, true ) : null;
		if ( ! is_array( $normalised ) || empty( $normalised['jobs'] ) || ! is_array( $normalised['jobs'] ) ) {
			\WPML\TM\Jobs\JobLog::addError(
				self::EVENT_SEND_ATE_NO_JOBS_MAP,
				[ 'batch' => $index + 1, 'batchCount' => $batchCount ]
			);
			return 0;
		}

		$jobRecords   = $this->makeJobRecords();
		$acceptedIds  = [];
		$acceptedRids = [];

		foreach ( $normalised['jobs'] as $rid => $ateJobId ) {
			$wpmlJobId = (int) Map::fromRid( (int) $rid );
			if ( ! $wpmlJobId ) {
				\WPML\TM\Jobs\JobLog::addError( self::EVENT_SEND_RID_UNMAPPED, [ 'rid' => $rid ] );
				continue;
			}

			$jobRecords->store( $wpmlJobId, [ JobRecords::FIELD_ATE_JOB_ID => (int) $ateJobId ] );
			$acceptedIds[]  = $wpmlJobId;
			$acceptedRids[] = (int) $rid;
		}

		TermJob::flagAcceptedJobs( $acceptedIds );

		foreach ( $acceptedRids as $acceptedRid ) {
			$this->acceptedRids[ $acceptedRid ] = true;
		}

		return count( $acceptedRids );
	}

	protected function createAteJobs( WPML_TM_ATE_API $ateApi, $payload ) {
		return $ateApi->create_jobs( $payload );
	}

	protected function makeJobRecords(): JobRecords {
		return \WPML\Container\make( JobRecords::class );
	}

	protected function resolveAteJobsUrl(): string {
		try {
			$endpoints = \WPML\Container\make( WPML_TM_ATE_AMS_Endpoints::class );
			return (string) $endpoints->get_ate_jobs();
		} catch ( \Throwable $e ) {
			return '';
		}
	}

	private function logJobPreview(
		\WP_Term $term,
		string $taxonomy,
		string $sourceLang,
		string $targetLang,
		int $termTaxonomyId,
		int $jobId,
		int $rid,
		string $xliff
	) {
		$attributes = [
			'wpml_job_id'      => $jobId,
			'rid'              => $rid,
			'term_taxonomy_id' => $termTaxonomyId,
			'term_id'          => (int) $term->term_id,
			'taxonomy'         => $taxonomy,
			'source_language'  => $sourceLang,
			'target_language'  => $targetLang,
			'term_name'        => $term->name,
			'term_slug'        => $term->slug,
			'has_description'  => $term->description !== '',
			'engine'           => 'PTC',
			'element_type'     => 'tax_' . $taxonomy,
		];

	}

	private function recordRequestedTaxonomies( array $taxonomies, array $languages ) {
		$requested = get_option( self::OPTION_REQUESTED_TAXONOMIES, [] );
		if ( ! is_array( $requested ) ) {
			$requested = [];
		}

		foreach ( $taxonomies as $taxonomy ) {
			$existing               = $requested[ $taxonomy ] ?? [];
			$requested[ $taxonomy ] = array_values( array_unique( array_merge( $existing, $languages ) ) );
		}

		update_option( self::OPTION_REQUESTED_TAXONOMIES, $requested, false );
	}
}
