<?php

namespace WPML\Troubleshooting\Endpoints\RetryStuckAutomaticJobs;

use WPML\Ajax\Authorization\Authorized;
use WPML\Ajax\IHandler;
use WPML\Collect\Support\Collection;
use WPML\FP\Either;
use WPML\FP\Right;
use WPML\Troubleshooting\Endpoints\AuthorizedForTroubleshooting;

class RequestHandler implements IHandler, Authorized {

	use AuthorizedForTroubleshooting;

	const LIMIT = 100;

	private $getJobsQuery;

	private $repairJobsQuery;

	public function __construct( GetJobsQuery $getJobsQuery, RepairJobsQuery $repairJobsQuery ) {
		$this->getJobsQuery    = $getJobsQuery;
		$this->repairJobsQuery = $repairJobsQuery;
	}

	public function run( Collection $payload ) {
		try {
			$result    = $this->getJobsQuery->get( self::LIMIT + 1 );
			$harMore   = count( $result ) > self::LIMIT;

			if ( $harMore ) {
				array_pop( $result );
			}

			$jobsCount = count( $result );

			if ( $jobsCount ) {
				$this->repairJobsQuery->repair( $result );
			}

			if ( $jobsCount && ! $harMore ) {
				delete_option( 'WPML(last)' );
			}

			return Either::of( [
				'count'    => $jobsCount,
				'success'  => true,
				'hasMore'  => $harMore
			] );
		} catch ( \Exception $e ) {
			return Either::left( $e->getMessage() );
		}
	}

}
