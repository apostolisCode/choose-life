<?php

namespace WPML\Setup;

use WPML\SuperGlobals\Request;

class FunnelRedirect implements \IWPML_Backend_Action, \IWPML_DIC_Action {

	const SETUP_PAGE = WPML_PLUGIN_FOLDER . '/menu/setup.php';

	const LANGUAGES_PAGE    = 'tm/menu/settings';
	const LANGUAGES_SECTION = 'languages';

	private $sitepress;

	public function __construct( \SitePress $sitepress ) {
		$this->sitepress = $sitepress;
	}

	public function add_hooks() {
		add_action( 'admin_init', [ $this, 'maybe_redirect' ], 1 );
	}

	public function maybe_redirect() {
		$method = isset( $_SERVER['REQUEST_METHOD'] )
			? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) )
			: '';
		if ( 'GET' !== strtoupper( $method ) ) {
			return;
		}

		if ( wp_doing_ajax() ) {
			return;
		}

		if ( is_network_admin() ) {
			return;
		}

		global $pagenow;
		if ( 'admin.php' !== $pagenow ) {
			return;
		}

		if ( ! $this->should_redirect( Request::page() ) ) {
			return;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=' . self::SETUP_PAGE ) );
		exit;
	}

	private function should_redirect( $page ) {
		return $this->needs_the_wizard( $page )
			&& $this->is_half_configured()
			&& ! $this->keeps_removed_content_reachable( $page );
	}

	private function keeps_removed_content_reachable( $page ) {
		return self::LANGUAGES_PAGE === $page
			&& self::LANGUAGES_SECTION === Request::param( 'section' )
			&& $this->has_removed_language_content();
	}

	protected function has_removed_language_content() {
		return array() !== \WPML\LanguageEditor\RemovedLanguages\Directory::all();
	}

	private function needs_the_wizard( $page ) {
		if ( ! $this->is_wpml_page( $page ) ) {
			return false;
		}

		$always_reachable = [
			self::SETUP_PAGE,
			WPML_PLUGIN_FOLDER . '/menu/support.php',
			WPML_PLUGIN_FOLDER . '/menu/troubleshooting.php',
			WPML_PLUGIN_FOLDER . '/menu/debug-information.php',
			WPML_PLUGIN_FOLDER . '/menu/network.php',
			'wpml-activate-update',
		];

		return ! in_array( $page, $always_reachable, true );
	}

	private function is_wpml_page( $page ) {
		if ( ! $page ) {
			return false;
		}

		foreach ( [ WPML_PLUGIN_FOLDER . '/menu/', 'tm/menu/', 'wpml-' ] as $prefix ) {
			if ( strpos( $page, $prefix ) === 0 ) {
				return true;
			}
		}

		return false;
	}

	private function is_half_configured() {
		return $this->sitepress->is_setup_complete()
			&& 2 > count( $this->sitepress->get_active_languages() );
	}
}
