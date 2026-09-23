<?php

namespace WPML\TM\ATE\Review;

use WPML\FP\Obj;
use WPML\TM\API\Jobs;
use WPML\TM\ATE\JobRecords;

class TermReviewOpener {

	private $api;

	private $records;

	public function __construct( \WPML_TM_ATE_API $api, JobRecords $records ) {
		$this->api     = $api;
		$this->records = $records;
	}

	public function openUrl( $jobId, $returnUrl ) {
		$jobId = (int) $jobId;
		$job   = TermJob::get( $jobId );
		if ( ! $job ) {
			return false;
		}

		$ateJobId = (int) Obj::prop( 'editor_job_id', $job );
		if ( ! $ateJobId ) {
			return false;
		}

		$isCompleted = ICL_TM_COMPLETE === (int) Obj::prop( 'status', $job ) || 1 === (int) Obj::prop( 'translated', $job );
		if ( $isCompleted ) {
			$clone = $this->api->clone_term_job( $ateJobId, $jobId, (int) Obj::prop( 'rid', $job ), Jobs::SENT_FROM_REVIEW );
			if ( ! is_array( $clone ) || empty( $clone['id'] ) ) {
				return false;
			}

			$ateJobId = (int) $clone['id'];
			$this->records->store( $jobId, [ JobRecords::FIELD_ATE_JOB_ID => $ateJobId ] );
		}

		$url = $this->api->get_editor_url( $ateJobId, (string) $returnUrl );
		if ( ! is_string( $url ) || '' === $url ) {
			return false;
		}

		Jobs::setStatus( $jobId, ICL_TM_IN_PROGRESS );
		Jobs::setReviewStatus( $jobId, ReviewStatus::EDITING );

		return $url;
	}
}
