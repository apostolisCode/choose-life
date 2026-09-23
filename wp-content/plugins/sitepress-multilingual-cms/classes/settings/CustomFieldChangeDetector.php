<?php

namespace WPML\TM\Settings;

use WPML\Core\BackgroundTask\Service\BackgroundTaskService;
use WPML\Infrastructure\WordPress\Component\CustomFieldPreferences\ContainerFreeServices;
use WPML\FP\Lst;
use WPML\LIB\WP\Hooks;
use WPML\WP\OptionManager;
use function WPML\Container\make;

class CustomFieldChangeDetector implements \IWPML_Backend_Action, \IWPML_DIC_Action {
	const PREVIOUS_SETTING = 'previous-custom-fields-to-translate';
	const DETECTED_SETTING = 'detected-custom-fields-to-translate';
	const RECHECK_SECONDS  = 86400;

	private $backgroundTaskService;

	public function __construct( BackgroundTaskService $backgroundTaskService ) {
		$this->backgroundTaskService = $backgroundTaskService;
	}

	public function add_hooks() {
		Hooks::onAction( 'wpml_after_tm_loaded', 1 )
		     ->then( [ self::class, 'detectIfDue' ] );
	}

	public static function detectIfDue() {
		$signal = ContainerFreeServices::changeSignal();
		if ( ! $signal->isCheckDue( self::RECHECK_SECONDS ) ) {
			return;
		}

		self::notify( self::getNew() );
		self::updatePrevious();

		$signal->markChecked();
	}

	public static function getNew() {
		if ( is_null( OptionManager::getOr( null, 'TM', self::PREVIOUS_SETTING ) ) ) {
			self::updatePrevious();
		}

		return Lst::diff(
			Repository::getCustomFieldsToTranslate() ?: [],
			OptionManager::getOr( [], 'TM', self::PREVIOUS_SETTING )
		);
	}

	public static function notify( array $newFields ) {
		$newFields = self::withoutConsented( $newFields );

		if ( count( $newFields ) ) {
			OptionManager::update(
				'TM',
				self::DETECTED_SETTING,
				Lst::concat( self::getDetected(), $newFields )
			);
		}
	}

	private static function withoutConsented( array $fields ) {
		if ( ! count( $fields ) ) {
			return $fields;
		}

		$consented = PreferenceReapplyConsent::consume( $fields );

		return $consented ? array_values( array_diff( $fields, $consented ) ) : $fields;
	}

	public static function remove( array $fields ) {
		if ( count( $fields ) ) {
			OptionManager::update(
				'TM',
				self::DETECTED_SETTING,
				Lst::diff( self::getDetected(), $fields )
			);
		}
	}

	public static function updatePrevious() {
		OptionManager::update( 'TM', self::PREVIOUS_SETTING, Repository::getCustomFieldsToTranslate() );
	}

	public static function getDetected() {
		return OptionManager::getOr( [], 'TM', self::DETECTED_SETTING );
	}

	public function processNewFields() {
		$consented = PreferenceReapplyConsent::consume( self::getDetected() );
		if ( $consented ) {
			self::remove( $consented );
		}

		$newFields = self::getDetected();
		if ( count( $newFields ) > 0 ) {
			$newFields = array_unique( $newFields );

			$backroundTaskEndpoint = make( ProcessNewTranslatableFields::class );

			$payload = wpml_collect( [ 'newFields' => $newFields ] );

			$task = $this->backgroundTaskService->add( $backroundTaskEndpoint, $payload );

			if ( $task ) {
				self::remove( $newFields );
			}
		}
	}
}
