<?php

namespace WPML\Troubleshooting\Tools;

use WPML\Request\Adapter\Ajax;
use WPML\Request\Policy\Authenticity;
use WPML\Request\Policy\Policy;
use WPML\Troubleshooting\Actions\AddMissingLanguageInformation;
use WPML\Troubleshooting\Actions\AssignTranslationStatusToDuplicates;
use WPML\Troubleshooting\Actions\FixElementTypeCollation;
use WPML\Troubleshooting\Actions\FixTermsCount;
use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\MigrationStatus\MigrationStatusStorageInterface;
use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\MigrationStatus\PreliminaryConditionQueryInterface;
use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\PreviousState\Factory as PreviousStateFactory;
use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\TranslationElements\Compress\Factory as CompressFactory;
use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\TranslationElements\CompressFix\Factory as CompressFixFactory;
use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\TranslationElements\RemoveOld\Factory as RemoveOldFactory;
use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\TranslationPackageColumnInterface;
use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Infrastructure\Domain\MigrationStatus\MigrationStatusStorage;
use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Infrastructure\Domain\MigrationStatus\PreliminaryConditionQuery;
use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Infrastructure\Domain\PreviousState\Factory as PreviousStateFactoryImpl;
use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Infrastructure\Domain\TranslationElements\Compress\Factory as CompressFactoryImpl;
use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Infrastructure\Domain\TranslationElements\CompressFix\Factory as CompressFixFactoryImpl;
use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Infrastructure\Domain\TranslationElements\RemoveOld\Factory as RemoveOldFactoryImpl;
use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Infrastructure\Domain\TranslationPackageColumn;
use WPML\Troubleshooting\Engine\WPML_Troubleshoot_Action;
use WPML\Troubleshooting\Engine\WPML_Troubleshoot_Sync_Posts_Taxonomies;
use WPML\Troubleshooting\Tool\FixLanguageDataController;
use WPML\Troubleshooting\Tool\FixTranslationsController;
use WPML\Troubleshooting\Tool\UrlResolutionCacheSection;
use WPML\UserInterface\Web\Core\Component\Support\Application\SupportTool;

class FixToolsTools {

	const PROVIDER = 'wpml-troubleshooting';

	const DEBUG_ACTIONS = [
		'icl_fix_collation'                       => FixElementTypeCollation::class,
		'assign_translation_status_to_duplicates' => AssignTranslationStatusToDuplicates::class,
		'icl_ts_add_missing_language'             => AddMissingLanguageInformation::class,
		'icl_fix_terms_count'                     => FixTermsCount::class,
	];

	const INTERFACE_MAPPINGS = [
		TranslationPackageColumnInterface::class  => TranslationPackageColumn::class,
		PreliminaryConditionQueryInterface::class => PreliminaryConditionQuery::class,
		PreviousStateFactory::class               => PreviousStateFactoryImpl::class,
		CompressFactory::class                    => CompressFactoryImpl::class,
		CompressFixFactory::class                 => CompressFixFactoryImpl::class,
		RemoveOldFactory::class                   => RemoveOldFactoryImpl::class,
		MigrationStatusStorageInterface::class    => MigrationStatusStorage::class,
	];


	public function register( array $tools ): array {
		$fixLanguageDataDescription = UrlResolutionCacheSection::isAvailable()
			? __( 'Assign language to content, repair language data, sync taxonomies and rebuild the URL cache', 'wpml-troubleshooting' )
			: __( 'Assign language to content, repair missing language codes, sync taxonomies', 'wpml-troubleshooting' );

		$fixLanguageDataSubs = [
			/* translators: Name of a Support tool section: its entry in the Support search list, its heading and its button label. Verb phrase, imperative. */
			[ 'label' => __( 'Set language information', 'wpml-troubleshooting' ), 'anchor' => 'set-language' ],
			[ 'label' => __( 'Synchronize posts taxonomies', 'wpml-troubleshooting' ), 'anchor' => 'sync-taxonomies' ],
			[ 'label' => __( 'Fix terms count', 'wpml-troubleshooting' ), 'anchor' => 'fix-terms-count' ],
			[ 'label' => __( 'Fix post type assignment', 'wpml-troubleshooting' ), 'anchor' => 'fix-post-type' ],
		];
		if ( UrlResolutionCacheSection::isAvailable() ) {
			$fixLanguageDataSubs[] = [ 'label' => __( 'Check URL resolution cache status', 'wpml-troubleshooting' ), 'anchor' => 'url-resolution-cache' ];
			$fixLanguageDataSubs[] = [ 'label' => __( 'Clear and rebuild the URL resolution cache', 'wpml-troubleshooting' ), 'anchor' => 'url-resolution-cache' ];
			$fixLanguageDataSubs[] = [ 'label' => __( 'Fix front-end links pointing at the wrong language', 'wpml-troubleshooting' ), 'anchor' => 'url-resolution-cache' ];
		}

		$tools[] = [
			'slug'        => 'fix-language-data',
			'title'       => __( 'Fix language data', 'wpml-troubleshooting' ),
			'description' => $fixLanguageDataDescription,
			'tier'        => SupportTool::TIER_SAFE,
			'order'       => 20,
			'controller'  => new FixLanguageDataController( new UrlResolutionCacheSection() ),
			'provider'    => self::PROVIDER,
			'icon'        => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
			'search'      => $fixLanguageDataSubs,
		];

		$tools[] = [
			'slug'        => 'fix-translations',
			/* translators: Name of a Support tool: its entry in the tool list on WPML → Support and the heading of its own screen. Verb phrase, imperative. */
			'title'       => __( 'Fix translations', 'wpml-troubleshooting' ),
			'description' => __( 'Repair duplicate statuses, refresh pending jobs, clear cache, reset translation service', 'wpml-troubleshooting' ),
			'tier'        => SupportTool::TIER_SAFE,
			'order'       => 30,
			'controller'  => new FixTranslationsController(),
			'provider'    => self::PROVIDER,
			'icon'        => 'M15.232 5.232l3.536 3.536M9 11l6-6 3.536 3.536L12.536 14.536H9V11zM4 20h16',
			'search'      => [
				[ 'label' => __( 'Clear the cache in WPML', 'wpml-troubleshooting' ), 'anchor' => 'clear-cache' ],
				[ 'label' => __( 'Retry stuck automatic translations', 'wpml-troubleshooting' ), 'anchor' => 'retry-stuck' ],
				[ 'label' => __( 'Refresh Translation Services', 'wpml-troubleshooting' ), 'anchor' => 'refresh-services' ],
				[ 'label' => __( 'Update domain name in language switcher', 'wpml-troubleshooting' ), 'anchor' => 'update-domain' ],
				/* translators: Name of a Support tool section: its entry in the Support search list and its heading. Verb phrase, imperative. */
				[ 'label' => __( 'Assign translation status to duplicates', 'wpml-troubleshooting' ), 'anchor' => 'assign-status' ],
				[ 'label' => __( 'Refresh preferred translation service', 'wpml-troubleshooting' ), 'anchor' => 'reset-service' ],
				/* translators: Name of a Support tool section: its entry in the Support search list, its heading and its button label. Verb phrase, imperative. */
				[ 'label' => __( 'Reset professional translation state', 'wpml-troubleshooting' ), 'anchor' => 'reset-professional' ],
			],
		];

		return $tools;
	}


	public function adminPagesConfig( array $config ): array {
		return $config;
	}


	public function hooks(): void {
		$this->defineAliases();

		add_filter( 'wpml_support_debug_actions', [ $this, 'debugActions' ] );

		require_once WPML_TROUBLESHOOTING_PATH . '/inc/functions-troubleshooting.php';
		Ajax::register(
			'icl_repair_broken_type_and_language_assignments',
			Policy::capability( 'wpml_manage_troubleshooting', Authenticity::actionNonce( 'broken_type_nonce', 'icl_nonce' ) ),
			'icl_repair_broken_type_and_language_assignments'
		);

		add_action( 'admin_init', [ $this, 'syncPostsTaxonomies' ] );
	}


	public function debugActions( $actions ): array {
		$actions = is_array( $actions ) ? $actions : [];

		return array_merge( $actions, self::DEBUG_ACTIONS );
	}


	public function syncPostsTaxonomies(): void {
		global $sitepress;

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$action = new WPML_Troubleshoot_Action();
		if ( $action->is_valid_request() ) {
			$sync = new WPML_Troubleshoot_Sync_Posts_Taxonomies( $sitepress, new \WPML_Term_Translation_Utils( $sitepress ) );
			$sync->run();
		}
	}


	private function defineAliases(): void {
		global $wpml_dic;

		if ( is_object( $wpml_dic ) && method_exists( $wpml_dic, 'alias' ) ) {
			$this->registerAliases( $wpml_dic );

			return;
		}

		add_action( 'plugins_loaded', [ $this, 'defineAliasesOnPluginsLoaded' ], 11 );
	}

	public function defineAliasesOnPluginsLoaded(): void {
		global $wpml_dic;

		if ( is_object( $wpml_dic ) && method_exists( $wpml_dic, 'alias' ) ) {
			$this->registerAliases( $wpml_dic );
		}
	}

	private function registerAliases( $dic ): void {
		foreach ( self::INTERFACE_MAPPINGS as $interface => $implementation ) {
			$dic->alias( $interface, $implementation );
		}
	}


}
