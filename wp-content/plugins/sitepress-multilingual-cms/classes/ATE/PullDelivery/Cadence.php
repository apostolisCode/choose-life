<?php

namespace WPML\TM\ATE\PullDelivery;

class Cadence {

	const ACTIVE_FLOOR = 60;

	const ACTIVE_CEILING = 300;

	const IDLE_FLOOR = 900;

	const IDLE_CEILING = 3600;

	const LEASE_WATCH = 300;

	const WATCHDOG_FLOOR = 600;

	const FIRST_CONTACT = 180;

	const REACHABILITY_RECHECK = 86400;

	const EXPECTED_WEBHOOK_LATENCY = 120;

	const STALENESS_MULTIPLIER = 3;

	const MIN_PING = 30;

	const MANUAL_FORCE_WINDOW = 1800;

	const COLLECT_MAX_JOBS = 20;

	const COLLECT_MAX_SECONDS = 20;

	const COLLECTING_MARGIN = 10;

	public static function activeFloor() {
		return self::filtered( 'wpml_pull_delivery_active_floor', self::ACTIVE_FLOOR );
	}

	public static function activeCeiling() {
		return self::filtered( 'wpml_pull_delivery_active_ceiling', self::ACTIVE_CEILING );
	}

	public static function idleFloor() {
		return self::filtered( 'wpml_pull_delivery_idle_floor', self::IDLE_FLOOR );
	}

	public static function idleCeiling() {
		return self::filtered( 'wpml_pull_delivery_idle_ceiling', self::IDLE_CEILING );
	}

	public static function leaseWatch() {
		return self::filtered( 'wpml_pull_delivery_lease_watch', self::LEASE_WATCH );
	}

	public static function leaseLength( $cadence ) {
		$cadence = max( self::MIN_PING, (int) $cadence );

		return self::filtered( 'wpml_pull_delivery_lease_length', 2 * $cadence );
	}

	public static function stalenessThreshold() {
		$computed = self::filtered( 'wpml_pull_delivery_expected_webhook_latency', self::EXPECTED_WEBHOOK_LATENCY )
			* self::STALENESS_MULTIPLIER;

		return max( self::filtered( 'wpml_pull_delivery_watchdog_floor', self::WATCHDOG_FLOOR ), $computed );
	}

	public static function firstContactThreshold() {
		return max( 1, self::filtered( 'wpml_pull_delivery_first_contact_threshold', self::FIRST_CONTACT ) );
	}

	public static function reachabilityRecheckInterval() {
		return max( 1, self::filtered( 'wpml_pull_delivery_reachability_recheck', self::REACHABILITY_RECHECK ) );
	}

	public static function watchdogFloor() {
		return self::filtered( 'wpml_pull_delivery_watchdog_floor', self::WATCHDOG_FLOOR );
	}

	public static function minPing() {
		return max( 1, self::filtered( 'wpml_pull_delivery_min_ping', self::MIN_PING ) );
	}

	public static function manualForceWindow() {
		return self::filtered( 'wpml_pull_delivery_manual_force_window', self::MANUAL_FORCE_WINDOW );
	}

	public static function maxJobsPerRun() {
		return max( 1, self::filtered( 'wpml_pull_delivery_max_jobs_per_run', self::COLLECT_MAX_JOBS ) );
	}

	public static function maxSecondsPerRun() {
		return max( 1, self::filtered( 'wpml_pull_delivery_max_seconds_per_run', self::COLLECT_MAX_SECONDS ) );
	}

	public static function collectingBound() {
		return self::maxSecondsPerRun()
			+ max( 0, self::filtered( 'wpml_pull_delivery_collecting_margin', self::COLLECTING_MARGIN ) );
	}

	public static function collectionInterval( $mode, $emptyRuns ) {
		$emptyRuns = max( 0, (int) $emptyRuns );

		if ( Modes::PUSH_OK === $mode ) {
			return 0;
		}

		if ( Modes::SUSPECT === $mode ) {
			return self::activeFloor();
		}

		if ( Modes::PULL_IDLE === $mode ) {
			return self::ladder( self::idleFloor(), self::idleCeiling(), $emptyRuns );
		}

		return self::ladder( self::activeFloor(), self::activeCeiling(), $emptyRuns );
	}

	public static function pingCadence( $mode, $emptyRuns, $isPacer, $pendingCount = 0 ) {
		if ( Modes::PUSH_OK === $mode && (int) $pendingCount > 0 ) {
			return max( self::minPing(), self::activeFloor() );
		}

		if ( ! $isPacer ) {
			return max( self::minPing(), self::leaseWatch() );
		}

		$interval = self::collectionInterval( $mode, $emptyRuns );

		if ( $interval <= 0 ) {
			return max( self::minPing(), self::leaseWatch() );
		}

		return max( self::minPing(), $interval );
	}

	private static function ladder( $floor, $ceiling, $emptyRuns ) {
		$floor   = max( 1, (int) $floor );
		$ceiling = max( $floor, (int) $ceiling );

		$steps    = min( 16, $emptyRuns );
		$interval = $floor * (int) pow( 2, $steps );

		return (int) min( $ceiling, $interval );
	}

	private static function filtered( $filter, $default ) {
		$value = apply_filters( $filter, $default );

		return is_numeric( $value ) ? (int) $value : (int) $default;
	}
}
