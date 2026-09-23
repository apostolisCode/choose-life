<?php

namespace WPML\Troubleshooting\Tools;

use WPML\Troubleshooting\Actions\GhostClean;
use WPML\Troubleshooting\Integration\StringTranslation\AjaxFactory;
use WPML\Troubleshooting\Integration\StringTranslation\BackendHooks;
use WPML\Troubleshooting\Integration\StringTranslation\StringTroubleshootingMenuAlias;
use WPML\Troubleshooting\Integration\TranslationManagement\WPML_TM_Troubleshooting_Fix_Translation_Jobs_TP_ID_Factory;
use WPML\Troubleshooting\Tool\DatabaseStringsController;
use WPML\Troubleshooting\Tool\PackagesController;
use WPML\UserInterface\Web\Core\Component\Support\Application\SupportTool;

class DbStringsTools {

	const PROVIDER = 'wpml-troubleshooting';

	const ST_ACTIONS = [
		StringTroubleshootingMenuAlias::class,
		BackendHooks::class,
		AjaxFactory::class,
	];

	const TM_ACTIONS = [
		WPML_TM_Troubleshooting_Fix_Translation_Jobs_TP_ID_Factory::class,
	];

	public function register( array $tools ): array {
		$tools[] = [
			'slug'        => 'db-strings',
			'title'       => __( 'Database & strings maintenance', 'wpml-troubleshooting' ),
			'description' => __( 'Optimize tables, clean up strings, remove ghost entries, regenerate MO files', 'wpml-troubleshooting' ),
			'tier'        => SupportTool::TIER_ADVANCED,
			'order'       => 30,
			'controller'  => new DatabaseStringsController(),
			'provider'    => self::PROVIDER,
			'icon'        => 'M4 7v10a2 2 0 002 2h12a2 2 0 002-2V7M4 7l2-3h12l2 3M4 7h16M9 11h6',
			'search'      => [
				[ 'label' => __( 'Database Tables Optimization', 'wpml-troubleshooting' ), 'anchor' => 'db-optimize' ],
				[ 'label' => __( 'Cleanup and optimize string tables', 'wpml-troubleshooting' ), 'anchor' => 'strings-cleanup' ],
				[ 'label' => __( 'Remove ghost entries from translation tables', 'wpml-troubleshooting' ), 'anchor' => 'ghosts' ],
				[ 'label' => __( 'Show custom MO files pre-generation dialog', 'wpml-troubleshooting' ), 'anchor' => 'mo-dialog' ],
				[ 'label' => __( 'Check for string issues', 'wpml-troubleshooting' ), 'anchor' => 'check-string-issues' ],
				[ 'label' => __( 'Fix element_type collation', 'wpml-troubleshooting' ), 'anchor' => 'fix-element-type-collation' ],
				[ 'label' => __( 'Fix WPML tables collation', 'wpml-troubleshooting' ), 'anchor' => 'fix-tables-collation' ],
				[ 'label' => __( 'Remove language suffixes from taxonomy names', 'wpml-troubleshooting' ), 'anchor' => 'lang-suffixes' ],
				[ 'label' => __( 'Fix tp_id field', 'wpml-troubleshooting' ), 'anchor' => 'fix-tp-id' ],
			],
		];

		$tools[] = [
			'slug'        => 'packages',
			/* translators: Name of a Support tool: its entry in the tool list on WPML → Support and the heading of its own screen. A package is a group of texts from one source, such as a form or a block. */
			'title'       => __( 'Package management', 'wpml-troubleshooting' ),
			'description' => __( 'String packages from Gravity Forms, Gutenberg blocks, Toolset, etc.', 'wpml-troubleshooting' ),
			'tier'        => SupportTool::TIER_ADVANCED,
			'order'       => 40,
			'controller'  => new PackagesController(),
			'provider'    => self::PROVIDER,
			'icon'        => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
			'search'      => [
				[ 'label' => __( 'Browse string-translation packages', 'wpml-troubleshooting' ), 'anchor' => '' ],
				[ 'label' => __( 'Re-register packages', 'wpml-troubleshooting' ), 'anchor' => '' ],
			],
		];

		return $tools;
	}


	public function adminPagesConfig( array $config ): array {
		return $config;
	}


	public function hooks(): void {
		add_filter( 'wpml_support_debug_actions', [ $this, 'debugActions' ] );

		$this->registerCoreEngineDoors();

		$loader = new \WPML_Action_Filter_Loader();

		if ( defined( 'WPML_TM_VERSION' ) ) {
			$loader->load( self::TM_ACTIONS );
		}

		if ( defined( 'WPML_ST_VERSION' ) ) {
			$loader->load( self::ST_ACTIONS );
		}
	}


	private function registerCoreEngineDoors(): void {
		\WPML\Request\Adapter\Ajax::register(
			'wpml_update_term_names_troubleshoot',
			\WPML\Request\Policy\Policy::capability(
				'wpml_manage_troubleshooting',
				\WPML\Request\Policy\Authenticity::actionNonce( 'update_term_names_nonce', '_icl_nonce' )
			),
			[ 'WPML_Troubleshooting_Terms_Menu', 'wpml_update_term_names_troubleshoot' ]
		);

		\WPML\Request\Adapter\Ajax::register(
			\WPML_Table_Collate_Fix::AJAX_ACTION,
			\WPML\Request\Policy\Policy::capability(
				'wpml_manage_troubleshooting',
				\WPML\Request\Policy\Authenticity::actionNonce( \WPML_Table_Collate_Fix::AJAX_ACTION, 'nonce' )
			),
			[ self::class, 'fixTablesCollationAjaxHandler' ]
		);
	}


	public static function fixTablesCollationAjaxHandler(): void {
		global $wpml_dic;
		$wpml_dic->make( \WPML_Table_Collate_Fix::class )->fix_collate_ajax();
	}


	public function debugActions( $actions ): array {
		$actions = is_array( $actions ) ? $actions : [];

		$actions['ghost_clean'] = GhostClean::class;

		return $actions;
	}


}
