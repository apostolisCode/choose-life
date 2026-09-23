<?php

namespace WPML\TM\API\ATE;

use WPML\FP\Either;
use WPML\FP\Fns;
use WPML\FP\Obj;
use WPML\TM\ATE\API\SpendCap;
use WPML\TM\ATE\API\SpendCapState;
use WPML\LIB\WP\Transient;
use WPML\LIB\WP\WordPress;
use WPML\TM\ATE\BuyWords\ManageCreditsSite;
use WPML\TM\ATE\ClonedSites\ReconnectState;
use WPML\WP\OptionManager;
use function WPML\Container\make;

class Account {

	const CREDITS_CACHE_KEY  = 'wpml-ate-account-credits';
	const BALANCES_CACHE_KEY = 'wpml-ate-account-balances';
	const CACHE_TTL          = 60;

	public static function getCredits( $allowCached = false ) {
		if ( ReconnectState::isReconnecting() ) {
			return Either::left( [ 'error' => 'communication error' ] );
		}

		if ( $allowCached ) {
			$cached = Transient::get( self::CREDITS_CACHE_KEY );
			if ( is_array( $cached ) ) {
				return Either::of( $cached );
			}
		}

		$credits = make( \WPML_TM_AMS_API::class )->getCredits();

		if ( is_wp_error( $credits ) || ! $credits ) {
			return Either::left( [ 'error' => 'communication error' ] );
		}

		OptionManager::update( 'TM', 'Account::credits', $credits );

		SpendCapState::syncWithCredits( $credits );

		if ( self::isHealthyCreditState( $credits ) ) {
			Transient::set( self::CREDITS_CACHE_KEY, $credits, self::CACHE_TTL );
		} else {
			self::clearCache();
		}

		return Either::of( $credits );
	}

	public static function getAccountBalances( $allowCached = false ) {
		if ( ReconnectState::isReconnecting() ) {
			return Either::left( [ 'error' => 'communication error' ] );
		}

		if ( $allowCached ) {
			$cached = Transient::get( self::BALANCES_CACHE_KEY );
			if ( is_array( $cached ) ) {
				return Either::of( $cached );
			}
		}

		return WordPress::handleError( make( \WPML_TM_AMS_API::class )->getAccountBalances() )
		                ->filter( Fns::identity() )
						->bimap(
							function ( $response ) {
								if ( is_wp_error( $response ) ) {
									return [
										'error' => $response->get_error_message(),
									];
								}
								return $response;
							},
							function ( $balances ) {
								if ( ! is_array( $balances ) ) {
									return $balances;
								}

								$balances = ManageCreditsSite::apply( $balances );

								if ( is_array( Transient::get( self::CREDITS_CACHE_KEY ) ) ) {
									Transient::set( self::BALANCES_CACHE_KEY, $balances, self::CACHE_TTL );
								}

								return $balances;
							}
						);
	}

	private static function isHealthyCreditState( array $credits ) {
		$hasDebt = (int) Obj::propOr( 0, 'subscription_debt', $credits ) > 0;

		$spendCap  = SpendCap::fromCredits( $credits );
		$atCap     = ( $spendCap && $spendCap->isReached() ) || SpendCapState::isReached();

		return ! $hasDebt
			&& ! $atCap
			&& ( self::hasActiveSubscription( $credits ) || self::getAvailableBalance( $credits ) > 0 );
	}

	public static function clearCache() {
		Transient::delete( self::CREDITS_CACHE_KEY );
		Transient::delete( self::BALANCES_CACHE_KEY );
	}

	public static function hasActiveSubscription( array $creditInfo ) {
		return (bool) Obj::propOr( false, 'active_subscription', $creditInfo );
	}

	public static function getAvailableBalance( array $creditInfo ) {
		return (int) Obj::propOr( 0, 'available_balance', $creditInfo );
	}

	public static function isAbleToTranslateAutomatically() {
		$creditInfo = OptionManager::getOr( [], 'TM', 'Account::credits' );

		if ( ! array_key_exists( 'active_subscription', $creditInfo ) ) {
			$creditInfo = self::getCredits()->getOrElse( [] );
		}

		return self::hasActiveSubscription( $creditInfo ) || self::getAvailableBalance( $creditInfo ) > 0;
	}
}
