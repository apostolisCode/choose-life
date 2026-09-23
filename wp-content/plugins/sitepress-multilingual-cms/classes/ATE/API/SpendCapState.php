<?php

namespace WPML\TM\ATE\API;

use WPML\FP\Obj;
use WPML\LIB\WP\Option;
use WPML\TM\ATE\PullDelivery\State as DeliveryState;

class SpendCapState {

	const OPTION = 'wpml_ate_spend_cap';

	public static function save( SpendCap $spendCap ) {
		Option::updateWithoutAutoLoad( self::OPTION, $spendCap->toArray() );
	}

	public static function get() {
		wp_cache_delete( self::OPTION, 'options' );

		$data = self::read();

		return is_array( $data ) && $data ? SpendCap::fromArray( $data ) : null;
	}

	public static function isReached() {
		wp_cache_delete( self::OPTION, 'options' );

		return (bool) self::read();
	}

	public static function clear() {
		if ( self::isReached() ) {
			Option::delete( self::OPTION );
		}
	}

	public static function syncWithCredits( array $credits ) {
		$fresh   = SpendCap::fromCredits( $credits );
		$current = self::get();

		if ( $current && self::isPayAsYouGoNoDebt( $credits ) && self::hasParkedJobs() ) {
			self::save( $fresh ? $current->merge( $fresh ) : $current );

			return;
		}

		self::clear();
	}

	public static function inferFromParkedJobs( array $credits, array $parkedAteIds ) {
		if ( ! $parkedAteIds || ! self::isCappedPayAsYouGo( $credits ) ) {
			return false;
		}

		$current = self::get();
		if ( $current ) {
			self::save( $current->merge( SpendCap::fromCredits( $credits ) ) );

			return true;
		}

		self::save( SpendCap::fromCredits( $credits )->asRefused() );

		return true;
	}

	private static function hasParkedJobs() {
		$state = DeliveryState::getFresh();

		return ! empty( $state['insufficient_balance_ate_job_ids'] );
	}

	private static function isPayAsYouGoNoDebt( array $credits ) {
		return (bool) Obj::propOr( false, 'pay_as_you_go', $credits )
			&& (int) Obj::propOr( 0, 'subscription_debt', $credits ) <= 0;
	}

	private static function isCappedPayAsYouGo( array $credits ) {
		return self::isPayAsYouGoNoDebt( $credits )
			&& null !== SpendCap::fromCredits( $credits );
	}

	private static function read() {
		return Option::getOr( self::OPTION, [] );
	}
}
