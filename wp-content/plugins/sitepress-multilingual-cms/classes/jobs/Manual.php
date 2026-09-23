<?php


namespace WPML\TM\Jobs;

use WPML\Element\API\PostTranslations;
use WPML\FP\Lst;
use WPML\FP\Maybe;
use WPML\FP\Obj;
use WPML\FP\Relation;
use WPML\LIB\WP\User;
use WPML\Records\Translations as TranslationRecords;
use WPML\TM\API\Jobs;
use function WPML\FP\pipe;

class Manual {
	public function createOrReuse( array $params ) {
		$jobId    = (int) filter_var( Obj::propOr( 0, 'job_id', $params ), FILTER_SANITIZE_NUMBER_INT );
		$isReview = (bool) filter_var( Obj::propOr( 0, 'preview', $params ), FILTER_SANITIZE_NUMBER_INT );

		list( $jobId, $trid, $updateNeeded, $targetLanguageCode, $elementType ) = $this->get_job_data_for_restore( $jobId, $params );
		$sourceLangCode = filter_var( Obj::prop( 'source_language_code', $params ), FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( $jobId && ! $updateNeeded && ! $isReview ) {
			$updateNeeded = $this->jobSnapshotIsStale( $jobId, $trid, $targetLanguageCode, $elementType );
		}

		$needsUpdateAndIsNotReviewMode = $updateNeeded && ! $isReview;

		if ( $trid && $targetLanguageCode && ( $needsUpdateAndIsNotReviewMode || ! $jobId ) ) {
			$postId = $this->getOriginalPostId( $trid );

			if ( ! $jobId ) {
				$postId = $this->getPostIdInLang( $trid, $sourceLangCode ) ?: $postId;
			}

			if ( $postId && $this->can_user_translate( $sourceLangCode, $targetLanguageCode, $postId ) ) {
				BaselineJobCreator::build()->create_missing( $postId, BaselineJobCreator::TRIGGER_EDITOR_OPEN );

				$createdJob = $this->markJobAsManual( $this->createLocalJob( $postId, $sourceLangCode, $targetLanguageCode, $elementType ) );
				if ( $createdJob ) {
					JobLog::add( 'manual_editor_job_prepared', [
						'job_id'      => JobLog::safeCall( $createdJob, 'get_id' ),
						'post_id'     => $postId,
						'target_lang' => $targetLanguageCode,
						'reused'      => false,
					] );
				}
				return $createdJob;
			}
		}

		$reusedJob = $jobId ? $this->markJobAsManual( wpml_tm_load_job_factory()->get_translation_job_as_active_record( $jobId ) ) : null;
		if ( $reusedJob ) {
			JobLog::add( 'manual_editor_job_prepared', [
				'job_id'      => JobLog::safeCall( $reusedJob, 'get_id' ),
				'target_lang' => $targetLanguageCode,
				'reused'      => true,
			] );
		}
		return $reusedJob;
	}

	public function recreateFromCurrentContent( array $params ) {
		$trid           = (int) filter_var( Obj::prop( 'trid', $params ), FILTER_SANITIZE_NUMBER_INT );
		$targetLanguage = (string) filter_var( Obj::prop( 'language_code', $params ), FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$sourceLanguage = (string) filter_var( Obj::prop( 'source_language_code', $params ), FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( ! $trid || ! $targetLanguage ) {
			return null;
		}

		$source = TranslationRecords::getSourceByTrid( $trid );

		if ( ! $sourceLanguage ) {
			$sourceLanguage = (string) Obj::prop( 'language_code', $source );
		}

		$postId = $this->getPostIdInLang( $trid, $sourceLanguage ) ?: Obj::prop( 'element_id', $source );

		if ( ! $postId || ! $this->can_user_translate( $sourceLanguage, $targetLanguage, $postId ) ) {
			return null;
		}

		$elementType = Obj::path( [ 0, 'element_type' ], TranslationRecords::getByTrid( $trid ) );

		return $this->markJobAsManual( $this->createLocalJob( $postId, $sourceLanguage, $targetLanguage, $elementType ) );
	}

	public function maybeGetDataIfTranslationCreatedInNativeEditorViaConnection( array $params ) {
		$jobId = (int) filter_var( Obj::propOr( 0, 'job_id', $params ), FILTER_SANITIZE_NUMBER_INT );
		list( , $trid, , $targetLanguageCode ) = $this->get_job_data_for_restore( $jobId, $params );

		if ( $trid && $targetLanguageCode ) {
			$originalPostId = $this->getOriginalPostId( $trid );
			if ( $this->isDuplicate( $originalPostId, $targetLanguageCode ) ) {
				return null;
			}

			$translatedPostId = (int) $this->getPostIdInLang( $trid, $targetLanguageCode );

			if ( $translatedPostId ) {
				$translatedPost = get_post( $translatedPostId );

				if ( $translatedPost ) {
					return [
						'targetLanguageCode' => $targetLanguageCode,
						'translatedPostId'   => $translatedPostId,
						'originalPostId'     => $originalPostId,
						'postType'           => $translatedPost->post_type,
					];
				}
			}
		}

		return null;
	}

	private function getOriginalPostId( $trid ) {
		return Obj::prop( 'element_id', TranslationRecords::getSourceByTrid( $trid ) );
	}

	private function jobSnapshotIsStale( $jobId, $trid, $languageCode, $elementType ) {
		if ( 0 !== strpos( (string) $elementType, 'post_' ) ) {
			return false;
		}

		$originalPostId = $this->getOriginalPostId( $trid );
		$post           = $originalPostId ? get_post( $originalPostId ) : null;
		if ( ! $post ) {
			return false;
		}

		$storedMd5 = Obj::prop( 'md5', wpml_load_core_tm()->get_element_translation( $originalPostId, $languageCode, $elementType ) );
		$isStale = $storedMd5 && ! ( new \WPML_TM_Action_Helper() )->post_md5_matches( $post, $storedMd5 );

		if ( $isStale ) {
			JobLog::add( 'manual_editor_stale_snapshot_detected', [
				'job_id'      => $jobId,
				'post_id'     => $originalPostId,
				'target_lang' => $languageCode,
			] );
		}

		return $isStale;
	}

	private function getPostIdInLang( $trid, $lang ) {
		$getElementId = pipe( Lst::find( Relation::propEq( 'language_code', $lang ) ), Obj::prop( 'element_id' ) );

		return $getElementId( TranslationRecords::getByTrid( $trid ) );
	}

	public function maybeGetDataForNewTranslationInNativeEditor( array $params ) {
		$jobId = (int) filter_var( Obj::propOr( 0, 'job_id', $params ), FILTER_SANITIZE_NUMBER_INT );
		list( , $trid, , $targetLanguageCode ) = $this->get_job_data_for_restore( $jobId, $params );

		if ( ! $trid || ! $targetLanguageCode ) {
			return null;
		}

		if ( $this->getPostIdInLang( $trid, $targetLanguageCode ) ) {
			return null;
		}

		$source = TranslationRecords::getSourceByTrid( $trid );

		$sourceLanguageCode = (string) filter_var( Obj::prop( 'source_language_code', $params ), FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$sourceLanguageCode = $sourceLanguageCode ?: (string) Obj::prop( 'language_code', $source );

		$originalPostId = (int) ( $this->getPostIdInLang( $trid, $sourceLanguageCode ) ?: Obj::prop( 'element_id', $source ) );

		if ( ! $originalPostId ) {
			return null;
		}

		$originalPost = get_post( $originalPostId );

		if ( ! $originalPost ) {
			return null;
		}

		return [
			'targetLanguageCode' => $targetLanguageCode,
			'sourceLanguageCode' => $sourceLanguageCode,
			'trid'               => (int) $trid,
			'originalPostId'     => $originalPostId,
			'postType'           => $originalPost->post_type,
		];
	}

	public function isLocalJobInProgress( array $params ) {
		$trid               = (int) filter_var( Obj::prop( 'trid', $params ), FILTER_SANITIZE_NUMBER_INT );
		$targetLanguageCode = (string) filter_var( Obj::prop( 'language_code', $params ), FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$jobId              = (int) filter_var( Obj::propOr( 0, 'job_id', $params ), FILTER_SANITIZE_NUMBER_INT );

		$job = $trid && $targetLanguageCode
			? Jobs::getTridJob( $trid, $targetLanguageCode )
			: ( $jobId ? Jobs::get( $jobId ) : null );

		if ( ! $job ) {
			return false;
		}

		$translationService = Obj::prop( 'translation_service', $job );
		$isRemote           = $translationService && 'local' !== $translationService;

		$inProgress = Lst::includes(
			(int) Obj::prop( 'status', $job ),
			[ ICL_TM_IN_PROGRESS, ICL_TM_WAITING_FOR_TRANSLATOR, ICL_TM_ATE_NEEDS_RETRY ]
		);

		return $inProgress && ! $isRemote;
	}

	private function get_job_data_for_restore( $jobId, array $params ) {
		$trid         = (int) filter_var( Obj::prop( 'trid', $params ), FILTER_SANITIZE_NUMBER_INT );
		$updateNeeded = (bool) filter_var( Obj::prop( 'update_needed', $params ), FILTER_SANITIZE_NUMBER_INT );
		$languageCode = (string) filter_var( Obj::prop( 'language_code', $params ), FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		$job = null;

		if ( $trid && $languageCode ) {
			$job = Jobs::getTridJob( $trid, $languageCode );
		} elseif ( $jobId ) {
			$job = Jobs::get( $jobId );
		}

		if ( is_object( $job ) ) {
			$jobTrid     = Obj::prop( 'trid', $job );
			$jobLanguage = Obj::prop( 'language_code', $job );

			return [
				Obj::prop( 'job_id', $job ),
				$jobTrid,
				$this->rebuildJobUnlessReusable( Obj::prop( 'needs_update', $job ), $job, $jobTrid, $jobLanguage ),
				$jobLanguage,
				Obj::prop( 'original_post_type', $job )
			];
		}

		$elementType  = $trid ? Obj::path( [ 0, 'element_type' ], TranslationRecords::getByTrid( $trid ) ) : null;
		$updateNeeded = $this->rebuildJobUnlessReusable( $updateNeeded, null, $trid, $languageCode );

		return [ $jobId, $trid, $updateNeeded, $languageCode, $elementType, ];
	}

	private function rebuildJobUnlessReusable( $updateNeeded, $job, $trid, $languageCode ) {
		if ( ! has_filter( 'wpml_tm_can_reuse_translation_job' ) ) {
			return $updateNeeded;
		}

		$source = $trid ? TranslationRecords::getSourceByTrid( $trid ) : null;

		$canReuse = apply_filters(
			'wpml_tm_can_reuse_translation_job',
			true,
			$job,
			[
				'trid'          => (int) $trid,
				'language_code' => (string) $languageCode,
				'element_id'    => (int) Obj::propOr( 0, 'element_id', $source ),
				'element_type'  => (string) Obj::propOr( '', 'element_type', $source ),
			]
		);

		return $canReuse ? $updateNeeded : true;
	}

	private function can_user_translate( $sourceLangCode, $targetLangCode, $postId ) {
		$args = [
			'lang_from' => $sourceLangCode,
			'lang_to'   => $targetLangCode,
			'post_id'   => $postId,
		];

		return wpml_tm_load_blog_translators()->is_translator( User::getCurrentId(), $args );
	}

	private function createLocalJob( $originalPostId, $sourceLangCode, $targetLangCode, $elementType ) {
		$jobId = wpml_tm_load_job_factory()->create_local_job( $originalPostId, $targetLangCode, null, $elementType, Jobs::SENT_MANUALLY, $sourceLangCode );

		return Maybe::fromNullable( $jobId )
		            ->map( [ wpml_tm_load_job_factory(), 'get_translation_job_as_active_record' ] )
		            ->map( $this->maybeAssignTranslator() )
		            ->map( $this->maybeSetJobStatus() )
		            ->getOrElse( null );
	}

	private function maybeAssignTranslator() {
		return function ( $jobObject ) {
			if ( $jobObject->get_translator_id() <= 0 ) {
				$jobObject->assign_to( User::getCurrentId() );
			}

			return $jobObject;
		};
	}

	private function maybeSetJobStatus() {
		return function ( $jobObject ) {
			if ( $this->isDuplicate( $jobObject->get_original_element_id(), $jobObject->get_language_code() ) ) {
				Jobs::setStatus( (int) $jobObject->get_id(), ICL_TM_DUPLICATE );
			} elseif ( (int) $jobObject->get_status_value() !== ICL_TM_COMPLETE ) {
				Jobs::setStatus( (int) $jobObject->get_id(), ICL_TM_IN_PROGRESS );
			}

			return $jobObject;
		};
	}

	private function markJobAsManual( $jobObject ) {
		$jobObject && Jobs::clearAutomatic( $jobObject->get_id() );

		return $jobObject;
	}

	private function isDuplicate( $originalElementId, $targetLanguageCode ): bool {
		return Maybe::of( $originalElementId )
		            ->map( PostTranslations::get() )
		            ->map( Obj::prop( $targetLanguageCode ) )
		            ->map( Obj::prop( 'element_id' ) )
		            ->map( [ wpml_get_post_status_helper(), 'is_duplicate' ] )
		            ->getOrElse( false );
	}
}
