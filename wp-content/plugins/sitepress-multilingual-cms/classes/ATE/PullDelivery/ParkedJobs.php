<?php

namespace WPML\TM\ATE\PullDelivery;

use WPML\TM\Jobs\JobLog;

class ParkedJobs {

	public function prune( array $state ) {
		$ids = $this->ids( $state, 'insufficient_balance_job_ids' );

		if ( ! $ids ) {
			return $state;
		}

		$inFlight = $this->inFlight( $ids );
		$keep     = array_values( array_intersect( $ids, array_keys( $inFlight ) ) );

		if ( count( $keep ) === count( $ids ) ) {
			return $state;
		}

		$dropped = array_values( array_diff( $ids, $keep ) );
		$ateIds  = array_values( array_unique( array_filter( array_map( 'intval', array_values( $inFlight ) ) ) ) );

		JobLog::add( 'pull_parked_jobs_forgotten', [ 'jobIds' => $dropped, 'kept' => $keep ] );

		return State::update(
			[
				'insufficient_balance_job_ids'     => $keep,
				'insufficient_balance_ate_job_ids' => $ateIds,
			]
		);
	}

	public static function forget() {
		$state = State::get();

		if ( ! $state['insufficient_balance_job_ids'] && ! $state['insufficient_balance_ate_job_ids'] ) {
			return;
		}

		JobLog::add( 'pull_parked_jobs_forgotten', [ 'jobIds' => array_values( (array) $state['insufficient_balance_job_ids'] ), 'reason' => 'cancel_all_automatic_jobs' ] );

		State::update(
			[
				'insufficient_balance_job_ids'     => [],
				'insufficient_balance_ate_job_ids' => [],
			]
		);
	}

	private function ids( array $state, $key ) {
		$ids = array_map( 'intval', (array) ( isset( $state[ $key ] ) ? $state[ $key ] : [] ) );

		return array_values( array_unique( array_filter( $ids ) ) );
	}

	private function inFlight( array $jobIds ) {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT job.job_id, job.editor_job_id
				   FROM {$wpdb->prefix}icl_translate_job job
				   JOIN {$wpdb->prefix}icl_translation_status status ON status.rid = job.rid
				  WHERE job.job_id IN ( " . implode( ', ', array_fill( 0, count( $jobIds ), '%d' ) ) . " )
				    AND status.status IN ( %d, %d )
				    AND job.job_id = (
				        SELECT MAX( newest.job_id ) FROM {$wpdb->prefix}icl_translate_job newest WHERE newest.rid = job.rid
				    )",
				...array_merge( $jobIds, [ ICL_TM_WAITING_FOR_TRANSLATOR, ICL_TM_IN_PROGRESS ] )
			),
			ARRAY_A
		);

		$inFlight = [];
		foreach ( is_array( $rows ) ? $rows : [] as $row ) {
			$inFlight[ (int) $row['job_id'] ] = (int) $row['editor_job_id'];
		}

		return $inFlight;
	}
}
