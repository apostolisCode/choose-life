<?php

namespace WPML\TM\Menu\TranslationQueue;

use WPML\FP\Either;
use WPML\FP\Obj;
use WPML\TM\API\Job\Map;
use WPML\TM\ATE\NothingToTranslateJob;
use WPML\TM\Jobs\JobLog;
use WPML_Element_Translation_Job;
use WPML_TM_Editors;
use WPML_TM_ATE_Jobs;
use WPML\TM\ATE\JobRecords;
use WPML_TM_ATE_API;

class CloneJobs {
	const RESULT_ATE_JOB_CREATED = 'ate_job_created';

	const RESULT_COMPLETED_LOCALLY = 'completed_locally';

	const RESULT_FAILED = 'failed';

	private $ateJobs;

	private $apiClient;

	private $repeatInterval;

	public function __construct( WPML_TM_ATE_Jobs $ateJobs, WPML_TM_ATE_API $apiClient, $repeatInterval = 5000000 ) {
		$this->ateJobs        = $ateJobs;
		$this->apiClient      = $apiClient;
		$this->repeatInterval = $repeatInterval;
	}

	public function cloneCompletedATEJob( WPML_Element_Translation_Job $jobObject, $sentFrom = null, $hasBeenAlreadyRepeated = false ) {
		$ateJobId = (int) $jobObject->get_basic_data_property('editor_job_id');
		$result   = $this->apiClient->clone_job( $ateJobId, $jobObject, $sentFrom );
		if ( $result ) {
			$wpmlJobId   = (int) $jobObject->get_id();
			$newAteJobId = (int) $result['id'];

			$this->ateJobs->store( $wpmlJobId, [ JobRecords::FIELD_ATE_JOB_ID => $newAteJobId ] );

			do_action( 'wpml_tm_ate_jobs_created', [ $wpmlJobId => $newAteJobId ] );

			return Either::of( $jobObject );
		} elseif ( ! $hasBeenAlreadyRepeated ) {
			usleep( $this->repeatInterval );

			return $this->cloneCompletedATEJob( $jobObject, $sentFrom, true );
		} else {
			return Either::left( $jobObject );
		}
	}

	public function cloneWPMLJob( $wpmlJobId ) {
		$model = wpml_tm_create_ATE_job_creation_model( $wpmlJobId );

		if ( ! $model ) {
			return self::RESULT_FAILED;
		}

		if ( NothingToTranslateJob::isJobModel( $model ) ) {
			try {
				NothingToTranslateJob::completeJob( $wpmlJobId );
			} catch ( \Throwable $throwable ) {
				JobLog::addError(
					'CloneJobs: local completion of empty job failed',
					[
						'job_id'  => (int) $wpmlJobId,
						'message' => $throwable->getMessage(),
					]
				);

				return self::RESULT_FAILED;
			}

			return self::RESULT_COMPLETED_LOCALLY;
		}

		$params = json_decode( (string) wp_json_encode( [
			'jobs' => [ $model ],
		] ), true );

		$response = $this->apiClient->create_jobs( $params );

		if ( ! is_wp_error( $response ) && Obj::prop( 'jobs', $response ) ) {
			$newAteJobId = Obj::path( [ 'jobs', Map::fromJobId( $wpmlJobId ) ], $response );

			$this->ateJobs->store( $wpmlJobId, [ JobRecords::FIELD_ATE_JOB_ID => $newAteJobId ] );
			wpml_tm_load_old_jobs_editor()->set( $wpmlJobId, WPML_TM_Editors::ATE );
			$this->ateJobs->warm_cache( [ $wpmlJobId ] );

			do_action( 'wpml_tm_ate_jobs_created', [ (int) $wpmlJobId => (int) $newAteJobId ] );

			return self::RESULT_ATE_JOB_CREATED;
		}

		return self::RESULT_FAILED;
	}
}
