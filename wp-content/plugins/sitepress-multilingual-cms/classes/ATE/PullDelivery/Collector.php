<?php

namespace WPML\TM\ATE\PullDelivery;

use WPML\FP\Obj;
use WPML\TM\API\Jobs;
use WPML\TM\ATE\API\ClientRestrictionState;
use WPML\TM\ATE\API\SpendCapState;
use WPML\TM\ATE\Receive\ClientEdits;
use WPML\TM\ATE\Receive\JobKind;
use WPML\TM\ATE\Receive\TranslationApplier;
use WPML\TM\ATE\Sync\Arguments;
use WPML\TM\ATE\Sync\Process;
use WPML\TM\ATE\SyncLock;
use WPML\TM\Jobs\JobLog;
use WPML_TM_ATE_AMS_Endpoints;
use WPML_TM_ATE_API;

class Collector {

	const EXIT_NO_PENDING = 'no_pending';

	const EXIT_LOCK_BUSY = 'lock_busy';

	const EXIT_NOTHING_READY = 'nothing_ready';

	const EXIT_DRAINED = 'drained';

	const EXIT_BOUNDED_JOBS = 'bounded_jobs';

	const EXIT_BOUNDED_TIME = 'bounded_time';

	const EXIT_ERROR = 'error';

	private $lock;

	private $syncProcess;

	private $applier;

	private $pendingJobs;

	private $meter;

	private $reported = [];

	public function __construct(
		SyncLock $lock,
		Process $syncProcess,
		TranslationApplier $applier,
		PendingJobs $pendingJobs,
		Meter $meter
	) {
		$this->lock        = $lock;
		$this->syncProcess = $syncProcess;
		$this->applier     = $applier;
		$this->pendingJobs = $pendingJobs;
		$this->meter       = $meter;
	}

	public function run() {
		$this->meter->start();

		$ownsBackgroundRequest = JobLog::maybeInitBackgroundRequest( 'wpml-pull-collect' );

		JobLog::createNewGroup(
			JobLog::GROUP_ID_DOWNLOAD_JOBS,
			'ATE pull collection',
			[ 'mode' => State::mode() ]
		);

		$holdsLock = false;

		try {
			JobLog::add( 'pull_collect_start', [ 'mode' => State::mode() ] );

			if ( ! $this->lock->create() ) {
				return $this->finish( self::EXIT_LOCK_BUSY, [], 0, null );
			}

			$holdsLock = true;

			State::onCollectionStart();

			$pendingBefore = $this->pendingJobs->summary();

			if ( $pendingBefore['count'] < 1 ) {
				return $this->finish( self::EXIT_NO_PENDING, [], 0, $pendingBefore );
			}

			list( $applied, $stillInProgress, $reason ) = $this->collectReadyJobs();

			return $this->finish( $reason, $applied, $stillInProgress, null );
		} catch ( \Throwable $e ) {
			JobLog::addError(
				'pull_collect_failed',
				[
					'message' => $e->getMessage(),
					'origin'  => $e->getFile() . ':' . $e->getLine(),
				]
			);

			return $this->finish( self::EXIT_ERROR, [], 0, null );
		} finally {
			if ( $holdsLock ) {
				$this->lock->release();
			}

			JobLog::finishCurrentGroup();

			if ( $ownsBackgroundRequest ) {
				JobLog::flushBackgroundRequest();
			}
		}
	}

	private function collectReadyJobs() {
		$deadline        = microtime( true ) + Cadence::maxSecondsPerRun();
		$maxJobs         = Cadence::maxJobsPerRun();
		$applied         = [];
		$stillInProgress = 0;
		$reason          = self::EXIT_NOTHING_READY;

		$this->reported = [
			'insufficient_balance_job_ids' => [],
			'insufficient_balance_ate_job_ids' => [],
			'unsolvable_job_ids'           => [],
			'eta_available'                => false,
			'eta_minutes'                  => 0,
		];

		$args                                   = new Arguments();
		$args->includeManualAndLongstandingJobs = true;

		do {
			$result = $this->syncProcess->run( $args );
			$jobs   = is_array( $result->jobs ) ? $result->jobs : [];

			$ready            = [];
			$pageInProgress   = 0;

			foreach ( $jobs as $job ) {
				if ( $this->isReady( $job ) ) {
					$ready[] = $job;
				} elseif ( $this->isUnsolvable( $job ) ) {
					$this->reported['unsolvable_job_ids'][] = (int) Obj::prop( 'jobId', $job );
				} elseif ( $this->isOutOfCredit( $job ) ) {
					$this->reported['insufficient_balance_job_ids'][] = (int) Obj::prop( 'jobId', $job );

					$ateJobId = (int) Obj::prop( 'ateJobId', $job );

					if ( $ateJobId ) {
						$this->reported['insufficient_balance_ate_job_ids'][] = $ateJobId;
					}
				} else {
					$pageInProgress++;
				}
			}

			$this->recordEta( $result );

			$stillInProgress += $pageInProgress;

			foreach ( $ready as $job ) {
				if ( count( $applied ) >= $maxJobs ) {
					return [ $applied, $stillInProgress, self::EXIT_BOUNDED_JOBS ];
				}

				if ( microtime( true ) >= $deadline ) {
					return [ $applied, $stillInProgress, self::EXIT_BOUNDED_TIME ];
				}

				if ( $this->applyJob( $job ) ) {
					$applied[] = $job;
				}
			}

			if ( $ready ) {
				$reason = self::EXIT_DRAINED;
			}

			$args = $this->nextPageArguments( $result );
		} while ( $args );

		return [ $applied, $stillInProgress, $reason ];
	}

	private function applyJob( $job ) {
		$jobId = (int) Obj::prop( 'jobId', $job );

		if ( ! $jobId ) {
			return false;
		}

		$wpmlJob = Jobs::get( $jobId );

		if ( ! $wpmlJob ) {
			if ( ! JobKind::isTaxonomyTerm( $jobId ) ) {
				JobLog::add( 'pull_collect_skipped', [ 'jobId' => $jobId, 'reason' => 'job_missing' ] );

				return false;
			}

			$job->automatic = true;

			return $this->recordOutcome( $jobId, $this->applier->applyForTerm( $jobId ) );
		}

		if ( ClientEdits::areInTheWay( $wpmlJob ) ) {
			JobLog::add( 'pull_collect_skipped', [ 'jobId' => $jobId, 'reason' => ClientEdits::REASON ] );

			return false;
		}

		$job->automatic = (bool) Obj::propOr( false, 'automatic', $wpmlJob );

		$outcome = $this->applier->applyForPost(
			Obj::prop( 'job_id', $wpmlJob ),
			Obj::prop( 'original_doc_id', $wpmlJob )
		);

		return $this->recordOutcome( $jobId, $outcome );
	}

	private function recordOutcome( $jobId, $outcome ) {
		if ( TranslationApplier::APPLIED === $outcome ) {
			JobLog::add( 'pull_collect_applied', [ 'jobId' => $jobId ] );

			return true;
		}

		JobLog::add( 'pull_collect_skipped', [ 'jobId' => $jobId, 'reason' => $outcome ] );

		return false;
	}

	private function nextPageArguments( $result ) {
		if ( ! $result->ateToken || ! $result->nextPage ) {
			return null;
		}

		$args                                   = new Arguments();
		$args->ateToken                         = $result->ateToken;
		$args->page                             = $result->nextPage;
		$args->numberOfPages                    = $result->numberOfPages;
		$args->includeManualAndLongstandingJobs = true;

		return $args;
	}

	private function recordEta( $result ) {
		$eta = isset( $result->eta ) ? $result->eta : null;

		$available = is_object( $eta ) && ( ! isset( $eta->available ) || false !== $eta->available );

		$this->reported['eta_available'] = $available;
		$this->reported['eta_minutes']   = $available && isset( $eta->estimated_minutes )
			? (int) $eta->estimated_minutes
			: 0;
	}

	private function isReady( $job ) {
		$status = (int) Obj::prop( 'ateStatus', $job );

		return in_array(
			$status,
			[
				WPML_TM_ATE_AMS_Endpoints::ATE_JOB_STATUS_TRANSLATED,
				WPML_TM_ATE_AMS_Endpoints::ATE_JOB_STATUS_DELIVERING,
				WPML_TM_ATE_AMS_Endpoints::ATE_JOB_STATUS_EDITED,
			],
			true
		) && ! $this->isUnsolvable( $job );
	}

	private function isUnsolvable( $job ) {
		return (bool) Obj::prop( 'isUnsolvable', $job );
	}

	public static function spendCapForState() {
		$spendCap = SpendCapState::get();

		return $spendCap ? $spendCap->toArray() : [];
	}

	private function isOutOfCredit( $job ) {
		return WPML_TM_ATE_API::NOT_ENOUGH_CREDIT_STATUS === (int) Obj::prop( 'ateStatus', $job );
	}

	private function finish( $reason, array $applied, $stillInProgress, $pendingAfter ) {
		if ( $applied ) {
			do_action( 'wpml_tm_ate_jobs_downloaded', wpml_collect( $applied ) );
		}

		if ( self::EXIT_LOCK_BUSY !== $reason ) {
			$pendingAfter = null === $pendingAfter ? $this->pendingJobs->summary() : $pendingAfter;

			if ( $this->reported ) {
				if ( ! empty( $this->reported['insufficient_balance_ate_job_ids'] ) ) {
					$credits = \WPML\TM\API\ATE\Account::getCredits( true )->getOrElse( null );
					if ( is_array( $credits ) ) {
						SpendCapState::inferFromParkedJobs( $credits, $this->reported['insufficient_balance_ate_job_ids'] );
					}
				}

				State::update(
					array_merge(
						$this->reported,
						[
							'client_restricted' => ClientRestrictionState::isRestricted(),
							'spend_cap'         => self::spendCapForState(),
							'ate_signals_at'    => time(),
						]
					)
				);
			}

			State::update( [ 'last_applied_jobs' => $this->appliedJobsForTheUi( $applied ) ] );

			State::onCollectionResult(
				count( $applied ),
				$pendingAfter['count'],
				$stillInProgress,
				$pendingAfter['oldest_at'],
				isset( $pendingAfter['oldest_automatic_at'] ) ? $pendingAfter['oldest_automatic_at'] : 0
			);
		} else {
			$pendingAfter = [ 'count' => (int) State::getKey( 'pending_count', 0 ), 'oldest_at' => 0 ];
		}

		JobLog::add(
			'pull_collect_exit',
			[
				'reason'            => $reason,
				'applied'           => count( $applied ),
				'pending'           => $pendingAfter['count'],
				'still_in_progress' => $stillInProgress,
				'cost'              => $this->meter->stop(),
			]
		);

		return [
			'reason'            => $reason,
			'applied'           => count( $applied ),
			'pending'           => (int) $pendingAfter['count'],
			'still_in_progress' => (int) $stillInProgress,
		];
	}

	private function appliedJobsForTheUi( array $applied ) {
		$jobs = [];

		foreach ( $applied as $job ) {
			$jobs[] = [
				'jobId'     => (int) Obj::prop( 'jobId', $job ),
				'automatic' => (bool) Obj::propOr( false, 'automatic', $job ),
			];
		}

		return $jobs;
	}
}
