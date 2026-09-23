<?php

namespace WPML\Troubleshooting\Tools;

use WPML\Request\Adapter\AdminPost;
use WPML\Request\Policy\Authenticity;
use WPML\Request\Policy\Policy;
use WPML\Troubleshooting\Tool\InternalLinksController;
use WPML\Troubleshooting\Tool\ResetController;
use WPML\Troubleshooting\Tool\WcmlFixTranslationsController;
use WPML\Troubleshooting\Tool\WcmlTranslationMaintenanceController;
use WPML\UserInterface\Web\Core\Component\Support\Application\SupportTool;
use WPML\UserInterface\Web\Infrastructure\WordPress\Component\Wcml\WcmlBonded;

class ResetTools {

	const PROVIDER = 'wpml-troubleshooting';

	private $reset;


	public function register( array $tools ): array {
		$wcml     = [ WcmlBonded::class, 'rendersEmbeddedBodies' ];
		$cartIcon = 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z';

		$tools[] = [
			'slug'        => 'internal-links',
			'title'       => __( 'Update internal links', 'wpml-troubleshooting' ),
			'description' => __( 'Rewire internal links on legacy pre-4.7 posts that still need adjustment', 'wpml-troubleshooting' ),
			'tier'        => SupportTool::TIER_SAFE,
			'order'       => 40,
			'controller'  => new InternalLinksController(),
			'applicable'  => [ InternalLinksController::class, 'isApplicable' ],
			'provider'    => self::PROVIDER,
			'icon'        => 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1',
			'search'      => [
				[ 'label' => __( 'Adjust legacy pre-4.7 internal links', 'wpml-troubleshooting' ), 'anchor' => '' ],
			],
		];

		$tools[] = [
			'slug'        => 'wcml-fix-translations',
			'title'       => __( 'Fix WCML translations', 'wpml-troubleshooting' ),
			'description' => __( 'Sync product variations, galleries, taxonomies and stock; register missing reviews; create missing attribute and product-type translations', 'wpml-troubleshooting' ),
			'tier'        => SupportTool::TIER_SAFE,
			'order'       => 80,
			'searchOrder' => 9020,
			'controller'  => new WcmlFixTranslationsController(),
			'applicable'  => $wcml,
			'provider'    => self::PROVIDER,
			'icon'        => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
			'search'      => [
				[ 'label' => __( 'Synchronize product variations', 'wpml-troubleshooting' ), 'anchor' => '' ],
				[ 'label' => __( 'Synchronize products image galleries', 'wpml-troubleshooting' ), 'anchor' => '' ],
				[ 'label' => __( 'Synchronize category metadata', 'wpml-troubleshooting' ), 'anchor' => '' ],
				[ 'label' => __( 'Synchronize stock for products and variations', 'wpml-troubleshooting' ), 'anchor' => '' ],
				[ 'label' => __( 'Create missing product attribute translations', 'wpml-troubleshooting' ), 'anchor' => '' ],
				[ 'label' => __( 'Allow translation of missing product reviews', 'wpml-troubleshooting' ), 'anchor' => '' ],
			],
		];

		$tools[] = [
			'slug'        => 'reset',
			/* translators: Name of a Support tool: its entry in the tool list on WPML → Support, the heading of its own screen, and its button label. Verb phrase, imperative (reset WPML on this site). */
			'title'       => __( 'Reset WPML', 'wpml-troubleshooting' ),
			'description' => __( 'Reset WPML entirely — last-resort recovery', 'wpml-troubleshooting' ),
			'tier'        => SupportTool::TIER_ADVANCED,
			'order'       => 60,
			'controller'  => $this->resetController(),
			'provider'    => self::PROVIDER,
			'icon'        => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2',
			'search'      => [
				[ 'label' => __( 'Reset WPML entirely', 'wpml-troubleshooting' ), 'anchor' => 'reset-all' ],
			],
		];

		$tools[] = [
			'slug'        => 'wcml-translation-maintenance',
			'title'       => __( 'WCML translation maintenance', 'wpml-troubleshooting' ),
			'description' => __( "Fix WPML's variation-translation relationship tables; permanently delete unused custom fields from product translations", 'wpml-troubleshooting' ),
			'tier'        => SupportTool::TIER_ADVANCED,
			'order'       => 70,
			'searchOrder' => 9040,
			'controller'  => new WcmlTranslationMaintenanceController(),
			'applicable'  => $wcml,
			'provider'    => self::PROVIDER,
			'icon'        => $cartIcon,
			'search'      => [
				[ 'label' => __( 'Fix variation-translation relationships', 'wpml-troubleshooting' ), 'anchor' => '' ],
				[ 'label' => __( 'Remove unused custom fields from product translations', 'wpml-troubleshooting' ), 'anchor' => '' ],
			],
		];

		return $tools;
	}


	public function adminPagesConfig( array $config ): array {
		return $config;
	}


	public function hooks(): void {
		if ( ! is_admin() ) {
			return;
		}

		AdminPost::register(
			'wpml_reset_all',
			Policy::capability(
				'wpml_manage_support',
				Authenticity::actionNonce( 'icl_reset_all', 'icl_reset_allnonce' )
			),
			[ $this->resetController(), 'handle_reset_all' ]
		);
	}


	private function resetController(): ResetController {
		if ( $this->reset === null ) {
			$this->reset = new ResetController();
		}

		return $this->reset;
	}


}
