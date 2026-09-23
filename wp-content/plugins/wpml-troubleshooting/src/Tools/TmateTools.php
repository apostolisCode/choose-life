<?php

namespace WPML\Troubleshooting\Tools;

use WPML\Troubleshooting\Ajax\SyncTranslatorsAjaxHandler;
use WPML\Troubleshooting\Integration\ICLToATEMigration\Loader as IclToAteMigrationLoader;
use WPML\Troubleshooting\Integration\TranslationManagement\ResetPreferredTranslationService;
use WPML\Troubleshooting\Integration\TranslationManagement\SynchronizeSourceIdOfATEJobs\TriggerSynchronization;
use WPML\Troubleshooting\Tool\ATESyncController;
use WPML\UserInterface\Web\Core\Component\Support\Application\SupportTool;

class TmateTools {

	const PROVIDER = 'wpml-troubleshooting';

	const LEGACY_CLASS_FILES = [
		'/src/Integration/TranslationManagement/class-wpml-tm-troubleshooting-clear-ts.php',
		'/src/Integration/TranslationManagement/class-wpml-tm-troubleshooting-reset-pro-trans-config.php',
	];


	public static function isTranslationManagementLoaded(): bool {
		return defined( 'WPML_TM_VERSION' );
	}


	public function register( array $tools ): array {
		if ( ! self::isTranslationManagementLoaded() ) {
			return $tools;
		}

		$tools[] = [
			'slug'        => 'ate-sync',
			'title'       => __( 'Synchronize with the Advanced Translation Editor', 'wpml-troubleshooting' ),
			'description' => __( 'Re-sync job IDs and translator/manager assignments with the ATE server', 'wpml-troubleshooting' ),
			'tier'        => SupportTool::TIER_ADVANCED,
			'order'       => 10,
			'controller'  => function () {
				return new ATESyncController();
			},
			'requiresTm'  => true,
			'provider'    => self::PROVIDER,
			'icon'        => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
			'search'      => [
				[ 'label' => __( 'Sync local job IDs with ATE', 'wpml-troubleshooting' ), 'anchor' => 'sync-jobs' ],
				[ 'label' => __( 'Sync translators and managers with ATE', 'wpml-troubleshooting' ), 'anchor' => 'sync-users' ],
			],
		];

		return $tools;
	}


	public function adminPagesConfig( array $config ): array {
		return $config;
	}


	public function hooks(): void {
		if ( ! self::isTranslationManagementLoaded() ) {
			return;
		}

		$this->loader()->load( [ SyncTranslatorsAjaxHandler::class ] );

		if ( did_action( 'wpml_after_tm_loaded' ) ) {
			$this->loadTranslationManagementDoors();
		} else {
			add_action( 'wpml_after_tm_loaded', [ $this, 'loadTranslationManagementDoors' ] );
		}
	}


	public function loadTranslationManagementDoors(): void {
		global $sitepress, $wpdb;

		if ( is_admin() ) {
			self::requireLegacyClasses();

			$wpml_wp_api       = new \WPML_WP_API();
			$translation_proxy = new \WPML_Translation_Proxy_API();
			new \WPML_TM_Troubleshooting_Reset_Pro_Trans_Config( $sitepress, $translation_proxy, $wpml_wp_api, $wpdb );
			new \WPML_TM_Troubleshooting_Clear_TS( $wpml_wp_api );
		}

		$this->loader()->load(
			[
				TriggerSynchronization::class,
				ResetPreferredTranslationService::class,
				IclToAteMigrationLoader::class,
			]
		);
	}


	public static function requireLegacyClasses() {
		foreach ( self::LEGACY_CLASS_FILES as $file ) {
			require_once WPML_TROUBLESHOOTING_PATH . $file;
		}
	}


	private function loader(): \WPML_Action_Filter_Loader {
		return new \WPML_Action_Filter_Loader();
	}


}
