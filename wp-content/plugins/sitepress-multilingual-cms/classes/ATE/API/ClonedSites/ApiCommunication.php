<?php

namespace WPML\TM\ATE\ClonedSites;

use WPML\FP\Fns;
use WPML\FP\Lst;
use WPML\FP\Obj;
use WPML\FP\Str;
use WPML\TM\ATE\API\AmsCredentialsStorage;
use WPML\TM\ATE\ClonedSites\AutoMigration\Handler as AutoMigrationHandler;
use function WPML\Container\make;

class ApiCommunication {

	const SITE_CLONED_ERROR = 426;

	const SERVICE_UNAVAILABLE = 503;

	const SERVER_ERROR_FLOOR = 500;

	const GATEWAY_ERROR_KEY = 'gateway_error';

	const RECONNECTING_ERROR_CODE = 'wpml_ate_reconnecting';

	const RECONNECTING_MESSAGE = 'Reconnecting to the translation service.';

	const NO_ANSWER_ERROR_KEY = 'no_answer';

	const TRANSIENT_STATUS = 'transient';

	const TRANSIENT_ERROR_KEY = 'transient';

	private static $replaying = false;

	private static $handlingIdentity = false;

	private static $probeOutcome = null;

	private static $forceNextProbe = false;

	private static $probeReason = '';

	private static $requestContextOverride = null;

	private $autoMigrationHandler;

	private $credentialsStorage;

	private $endpoints;

	public function __construct(
		AutoMigrationHandler $autoMigrationHandler,
		AmsCredentialsStorage $credentialsStorage,
		?\WPML_TM_ATE_AMS_Endpoints $endpoints = null
	) {
		$this->autoMigrationHandler = $autoMigrationHandler;
		$this->credentialsStorage   = $credentialsStorage;
		$this->endpoints            = $endpoints;
	}

	public function handleClonedSiteError( $response, $service = '' ) {
		$code = (int) Obj::pathOr( 0, [ 'response', 'code' ], $response );

		if ( self::SERVICE_UNAVAILABLE === $code ) {
			return $this->handleTransientFailure( $response, $service );
		}

		if ( self::SITE_CLONED_ERROR !== $code ) {
			if ( $code >= self::SERVER_ERROR_FLOOR ) {
				return $this->handleGatewayFailure( $response, $code, $service );
			}

			return $response;
		}

		$body = $this->decodeBody( $response );

		$resolution = Obj::propOr( null, 'resolution', $body );

		return self::whileHandlingIdentity(
			function () use ( $resolution, $body ) {
				if ( is_array( $resolution ) ) {
					return $this->handleServerResolution( $resolution, $body );
				}

				return $this->handleLegacyMismatch( $body );
			}
		);
	}

	public function handleTransportFailure( $response, $service = '' ) {
		if ( ! ( $response instanceof \WP_Error ) ) {
			return;
		}

		if ( self::$replaying || self::$handlingIdentity ) {
			return;
		}

		$reattempt          = self::currentProbeIsReattempt();
		self::$probeOutcome = 'failed';

		ReconnectState::recordFailure(
			'',
			'',
			0,
			self::NO_ANSWER_ERROR_KEY . ':' . $response->get_error_code(),
			0,
			ReconnectState::KIND_NO_ANSWER,
			$this->serviceOrDefault( $service ),
			$this->hostOf( $service ),
			$reattempt
		);

		ReconnectDriver::arm();
	}

	private static function currentProbeIsReattempt() {
		return 'floor' === self::$probeReason || 'forced' === self::$probeReason;
	}

	public static function forceProbe() {
		self::$forceNextProbe = true;
	}

	public static function cancelForcedProbe() {
		self::$forceNextProbe = false;
	}

	public static function probedThisRequest() {
		return null !== self::$probeOutcome;
	}

	public static function resetRequestState() {
		self::$probeOutcome           = null;
		self::$forceNextProbe         = false;
		self::$probeReason            = '';
		self::$requestContextOverride = null;
	}

	public static function overrideRequestContext( $isComponentPageLoad ) {
		self::$requestContextOverride = null === $isComponentPageLoad ? null : (bool) $isComponentPageLoad;
	}

	private static function isComponentPageLoad() {
		if ( null !== self::$requestContextOverride ) {
			return self::$requestContextOverride;
		}

		return self::isPageRequest() && ReconnectNotice::isComponentScreen();
	}

	private static function isPageRequest() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return false;
		}
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return false;
		}
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return false;
		}
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return false;
		}

		return true;
	}

	private function serviceOrDefault( $service ) {
		return ReconnectState::SERVICE_AMS === $service ? ReconnectState::SERVICE_AMS : ReconnectState::SERVICE_ATE;
	}

	private function hostOf( $service ) {
		try {
			if ( null === $this->endpoints ) {
				$this->endpoints = make( \WPML_TM_ATE_AMS_Endpoints::class );
			}

			$host = ReconnectState::SERVICE_AMS === $service
				? $this->endpoints->get_AMS_host()
				: $this->endpoints->get_ATE_host();
		} catch ( \Throwable $e ) {
			$host = '';
		}

		return is_string( $host ) ? $host : '';
	}

	public function noteServerAnswered( $response = null ) {
		if ( null !== $response && self::isServerError( $response ) ) {
			return;
		}

		if ( 'failed' === self::$probeOutcome ) {
			return;
		}

		self::$probeOutcome = 'answered';

		if ( ReconnectState::get() && ReconnectState::isNoAnswer() ) {
			ReconnectState::clear();
		}
	}

	private static function isServerError( $response ) {
		return (int) Obj::pathOr( 0, [ 'response', 'code' ], $response ) >= self::SERVER_ERROR_FLOOR;
	}

	private function handleGatewayFailure( $response, $code, $service = '' ) {
		if ( self::$replaying || self::$handlingIdentity ) {
			return $response;
		}

		$reattempt          = self::currentProbeIsReattempt();
		self::$probeOutcome = 'failed';

		ReconnectState::recordFailure(
			'',
			'',
			$code,
			self::GATEWAY_ERROR_KEY . ':' . $code,
			0,
			ReconnectState::KIND_NO_ANSWER,
			$this->serviceOrDefault( $service ),
			$this->hostOf( $service ),
			$reattempt
		);

		ReconnectDriver::arm();

		return self::reconnectingError();
	}

	public static function whileHandlingIdentity( callable $call ) {
		$previous               = self::$handlingIdentity;
		self::$handlingIdentity = true;

		try {
			return $call();
		} finally {
			self::$handlingIdentity = $previous;
		}
	}

	public function checkCloneSiteLock( $endpointUrl = '' ) {
		$isOnWhiteList = function ( $endpointUrl ) {
			$endpointsWhitelist = \apply_filters( 'wpml_ate_locked_endpoints_whitelist', [] );

			return (bool) Lst::find( Str::includes( Fns::__, $endpointUrl ), $endpointsWhitelist );
		};

		if ( $isOnWhiteList( $endpointUrl ) ) {
			return null;
		}

		if ( self::$forceNextProbe && ( ! ReconnectState::get() || ReconnectState::isNoAnswer() ) ) {
			self::$forceNextProbe = false;
			self::$probeOutcome   = 'probing';
			self::$probeReason    = 'forced';

			return null;
		}

		if ( ! ReconnectState::isReconnecting() ) {
			return null;
		}

		if ( ReconnectState::isNoAnswer() ) {
			if ( ReconnectState::isDue() ) {
				self::$probeOutcome = 'probing';
				self::$probeReason  = 'floor';

				return null;
			}

			if ( null === self::$probeOutcome && self::isComponentPageLoad() ) {
				self::$probeOutcome = 'probing';
				self::$probeReason  = 'page';

				return null;
			}

			return self::reconnectingError();
		}

		return self::reconnectingError();
	}

	public static function reconnectingError( array $data = [] ) {
		return new \WP_Error(
			self::RECONNECTING_ERROR_CODE,
			__( 'Reconnecting to the translation service.', 'sitepress' ),
			array_merge( [ 'status' => self::SERVICE_UNAVAILABLE ], $data )
		);
	}

	public static function shouldReplay( $result ) {
		if ( self::$replaying ) {
			return false;
		}

		if ( ! ( $result instanceof \WP_Error ) ) {
			return false;
		}

		if ( self::RECONNECTING_ERROR_CODE !== $result->get_error_code() ) {
			return false;
		}

		$data = $result->get_error_data();

		return is_array( $data ) && ! empty( $data['replay'] );
	}

	public static function replay( callable $call ) {
		self::$replaying = true;

		try {
			return $call();
		} finally {
			self::$replaying = false;
		}
	}

	private function handleServerResolution( array $resolution, array $body ) {
		$urls   = ReconnectState::extractUrls( $this->firstError( $body ) );
		$status = Obj::propOr( '', 'status', $resolution );

		if ( self::TRANSIENT_STATUS === $status ) {
			return $this->recordNoAnswerFailure(
				$urls,
				self::SITE_CLONED_ERROR,
				self::TRANSIENT_ERROR_KEY,
				self::retryAfterFrom( $resolution )
			);
		}

		if ( $status !== 'forked' ) {
			ReconnectState::recordFailure(
				$urls['old_url'],
				$urls['new_url'],
				self::SITE_CLONED_ERROR,
				(string) Obj::propOr( 'unresolved', 'error_code', $resolution )
			);

			return self::reconnectingError();
		}

		if ( ! $this->hasCredentials( $resolution ) ) {
			ReconnectState::recordFailure(
				$urls['old_url'],
				$urls['new_url'],
				self::SITE_CLONED_ERROR,
				'resolution_incomplete'
			);

			return self::reconnectingError();
		}

		if ( ! $this->credentialsStorage->store( $resolution ) ) {
			ReconnectState::recordFailure(
				$urls['old_url'],
				$urls['new_url'],
				self::SITE_CLONED_ERROR,
				'credentials_not_stored'
			);

			return self::reconnectingError();
		}

		ReconnectState::clear();

		return self::reconnectingError( [ 'replay' => true ] );
	}

	private function handleLegacyMismatch( array $body ) {
		$error = $this->firstError( $body );
		$urls  = ReconnectState::extractUrls( $error );

		if ( ! $error ) {
			return self::reconnectingError();
		}

		if ( $this->autoMigrationHandler->tryMigrate( $urls['old_url'], $urls['new_url'] ) ) {
			ReconnectState::clear();

			return self::reconnectingError( [ 'replay' => true ] );
		}

		ReconnectState::recordFailure(
			$urls['old_url'],
			$urls['new_url'],
			self::SITE_CLONED_ERROR,
			$this->withStep( 'legacy_migration_failed' )
		);

		return self::reconnectingError();
	}

	private function withStep( $base ) {
		$reason = $this->autoMigrationHandler->getLastFailureReason();

		return '' !== $reason ? $base . ':' . $reason : $base;
	}

	private function handleTransientFailure( $response, $service = '' ) {
		$body = $this->decodeBody( $response );

		return $this->recordNoAnswerFailure(
			[ 'old_url' => '', 'new_url' => '' ],
			self::SERVICE_UNAVAILABLE,
			(string) Obj::propOr( 'service_unavailable', 'error_code', $body ),
			self::retryAfterFrom( $body, Obj::pathOr( 0, [ 'headers', 'retry-after' ], $response ) ),
			$service
		);
	}

	private function recordNoAnswerFailure( array $urls, $httpStatus, $errorKey, $retryAfter, $service = '' ) {
		$reattempt          = self::currentProbeIsReattempt();
		self::$probeOutcome = 'failed';

		ReconnectState::recordFailure(
			$urls['old_url'],
			$urls['new_url'],
			$httpStatus,
			(string) $errorKey,
			$retryAfter,
			ReconnectState::KIND_NO_ANSWER,
			'' !== $service ? $this->serviceOrDefault( $service ) : ReconnectState::SERVICE_AMS,
			$this->hostOf( '' !== $service ? $service : ReconnectState::SERVICE_AMS ),
			$reattempt,
			false
		);

		ReconnectDriver::arm();

		return self::reconnectingError();
	}

	private static function retryAfterFrom( $source, $fallback = 0 ) {
		$retryAfter = (int) Obj::propOr( $fallback, 'retry_after', $source );

		return $retryAfter > 0 ? $retryAfter : 0;
	}

	private function hasCredentials( array $resolution ) {
		foreach ( [ 'new_shared_key', 'new_secret_key', 'new_website_uuid' ] as $key ) {
			if ( empty( $resolution[ $key ] ) ) {
				return false;
			}
		}

		return true;
	}

	private function firstError( array $body ) {
		$errors = Obj::propOr( [], 'errors', $body );

		if ( ! is_array( $errors ) || ! $errors ) {
			return [];
		}

		$error = array_pop( $errors );

		return is_array( $error ) ? $error : [];
	}

	private function decodeBody( $response ) {
		$raw = Obj::propOr( '', 'body', $response );

		if ( ! is_string( $raw ) || $raw === '' ) {
			return [];
		}

		$decoded = json_decode( $raw, true );

		return is_array( $decoded ) ? $decoded : [];
	}
}
