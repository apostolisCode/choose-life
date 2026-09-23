<?php

namespace WPML\Troubleshooting\Integration\ICLToATEMigration;

use WPML\Element\API\Languages;
use WPML\FP\Obj;
use WPML\Troubleshooting\Integration\ICLToATEMigration\Endpoints\AuthenticateICL;
use WPML\Troubleshooting\Integration\ICLToATEMigration\Endpoints\DeactivateICL;
use WPML\Troubleshooting\Integration\ICLToATEMigration\Endpoints\TranslationMemory\CheckMigrationStatus;
use WPML\Troubleshooting\Integration\ICLToATEMigration\Endpoints\TranslationMemory\StartMigration;
use WPML\Troubleshooting\Integration\ICLToATEMigration\Endpoints\Translators\GetFromICL;
use WPML\Troubleshooting\Integration\ICLToATEMigration\Endpoints\Translators\Save;
use WPML\LanguageEditor\ActiveLanguages;
use WPML\LIB\WP\Hooks;
use WPML\Core\WP\App\Resources;
use WPML\SuperGlobals\Request;
use function WPML\Container\make;

class Loader implements \IWPML_Backend_Action {

	const ICL_NAME = 'ICanLocalize';

	const SUPPORT_LANDING_HOOK = 'wpml_admin_support_landing_top';

	public function add_hooks() {
		if ( self::shouldShowMigration() ) {
			Hooks::onAction( 'wp_loaded' )
				 ->then( [ self::class, 'getData' ] )
				 ->then( Resources::enqueueApp( 'icl-to-ate-migration' ) );

			add_action( self::SUPPORT_LANDING_HOOK, [ self::class, 'renderContainerOnSupportLanding' ] );
		}
	}

	public static function renderContainerOnSupportLanding() {
		echo wp_kses_post( self::renderContainerIfNeeded() );
	}

	public static function shouldShowMigration() {

		if ( ! defined( 'WPML_ICL_ATE_MIGRATION_ENABLED' ) || ! WPML_ICL_ATE_MIGRATION_ENABLED ) {
			return false;
		}
		return ! wpml_is_ajax() && ( self::isLegacyTroubleshootingPage() || self::isSupportLanding() ) &&
		       ( '' !== Request::param( 'icl-to-ate' ) || make(ICLStatus::class)->isActivatedAndAuthorized() || Data::isICLDeactivated() );
	}

	private static function isLegacyTroubleshootingPage() {
		return WPML_PLUGIN_FOLDER . '/menu/troubleshooting.php' === Request::page();
	}

	private static function isSupportLanding() {
		return WPML_PLUGIN_FOLDER . '/menu/support.php' === Request::page()
			   && '' === Request::param( 'tool' );
	}

	public static function renderContainerIfNeeded() {
		if ( self::shouldShowMigration() ) {
			return '<div id="wpml-icl-to-ate-migration"></div>';
		}

		return '';
	}

	public static function getData() {
		$originalLanguageCode = Languages::getDefaultCode();
		$userLanguageCode     = Languages::getUserLanguageCode()->getOrElse( $originalLanguageCode );
		$languages            = Languages::withFlags( Languages::getAll( $userLanguageCode ) );

		return [
			'name' => 'wpmlIclToAteMigration',
			'data' => [
				'endpoints'      => [
					'GetTranslatorsFromICL'                => GetFromICL::class,
					'StartImportTranslationMemory'         => StartMigration::class,
					'CheckImportTranslationMemoryProgress' => CheckMigrationStatus::class,
					'SaveTranslators'                      => Save::class,
					'AuthenticateICL'                      => AuthenticateICL::class,
					'DeactivateICL'                        => DeactivateICL::class,
				],
				'languages'      => [
					'list'        => $languages ? ActiveLanguages::withLanguageField( (array) Obj::values( $languages ) ) : [],
					'secondaries' => Languages::getSecondaryCodes(),
					'original'    => $originalLanguageCode,
				],
				'isICLActive'    => make( ICLStatus::class )->isActivatedAndAuthorized(),
				'migrationsDone' => [
					'memory' => Data::isMemoryMigrated(),
				],
				'ICLDeactivated' => Data::isICLDeactivated(),
			],
		];
	}
}

