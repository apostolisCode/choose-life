<?php

namespace WPML\TM\ATE\Receive;

use WPML\FP\Obj;
use WPML\TM\API\Jobs;
use WPML\TM\ATE\SyncLock;

class SingleJobDelivery {

	const APPLIED      = TranslationApplier::APPLIED;
	const NOT_READY    = TranslationApplier::XLIFF_NOT_READY;
	const APPLY_FAILED = TranslationApplier::APPLY_FAILED;
	const LOCK_BUSY    = 'lock_busy';
	const CANCELLED    = 'cancelled';
	const JOB_MISSING  = 'job_missing';
	const CLIENT_EDITS = 'client_edits';
	const SUPERSEDED   = 'superseded';

	const RETRYABLE = [ self::NOT_READY, self::APPLY_FAILED, self::LOCK_BUSY ];

	private $lock;

	private $applier;

	public function __construct( SyncLock $lock, $applier ) {
		$this->lock    = $lock;
		$this->applier = $applier;
	}

	private function applier() {
		if ( ! $this->applier instanceof TranslationApplier ) {
			$this->applier = call_user_func( $this->applier );
		}

		return $this->applier;
	}

	public function deliver( $wpmlJobId, $lockName, ?callable $onLockAcquired = null ) {
		$wpmlJobId = (int) $wpmlJobId;

		if ( ! $this->lock->create( $lockName ) ) {
			return self::outcome( self::LOCK_BUSY );
		}

		try {
			if ( $onLockAcquired ) {
				$onLockAcquired();
			}

			return $this->decide( $wpmlJobId );
		} finally {
			$this->lock->release();
		}
	}

	private function decide( $wpmlJobId ) {
		$wpmlJob = Jobs::get( $wpmlJobId );

		if ( $this->isCancelledLocally( $wpmlJobId ) ) {
			return self::outcome( self::CANCELLED );
		}

		if ( ! $wpmlJob && JobKind::isTaxonomyTerm( $wpmlJobId ) ) {
			return self::outcome( $this->applier()->applyForTerm( $wpmlJobId ) );
		}

		if ( ! $wpmlJob ) {
			return self::outcome( self::JOB_MISSING );
		}

		if ( ClientEdits::areInTheWay( $wpmlJob ) ) {
			return self::outcome( self::CLIENT_EDITS );
		}

		$supersededBy = $this->findSupersedingJob( $wpmlJobId );

		if ( $supersededBy ) {
			return self::outcome( self::SUPERSEDED, $supersededBy );
		}

		return self::outcome(
			$this->applier()->applyForPost(
				Obj::prop( 'job_id', $wpmlJob ),
				Obj::prop( 'original_doc_id', $wpmlJob )
			)
		);
	}

	private static function outcome( $code, ?array $supersededBy = null ) {
		return [
			'outcome'       => $code,
			'superseded_by' => $supersededBy,
		];
	}

	public static function isRetryable( $code ) {
		return in_array( $code, self::RETRYABLE, true );
	}

	private function isCancelledLocally( $wpmlJobId ) {
		global $wpdb;

		$status = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ts.status
				   FROM {$wpdb->prefix}icl_translate_job j
				   INNER JOIN {$wpdb->prefix}icl_translation_status ts ON ts.rid = j.rid
				  WHERE j.job_id = %d",
				(int) $wpmlJobId
			)
		);

		return null !== $status && ICL_TM_ATE_CANCELLED === (int) $status;
	}

	private function findSupersedingJob( $wpmlJobId ) {
		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT j.rid AS rid,
				        ( SELECT MAX( newer.job_id )
				            FROM {$wpdb->prefix}icl_translate_job newer
				           WHERE newer.rid = j.rid ) AS latest_job_id
				   FROM {$wpdb->prefix}icl_translate_job j
				  WHERE j.job_id = %d",
				(int) $wpmlJobId
			)
		);

		if ( ! is_object( $row ) || ! isset( $row->latest_job_id ) ) {
			return null;
		}

		if ( (int) $row->latest_job_id <= (int) $wpmlJobId ) {
			return null;
		}

		return [
			'rid'          => (int) $row->rid,
			'newer_job_id' => (int) $row->latest_job_id,
		];
	}
}
