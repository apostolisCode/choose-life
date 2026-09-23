<?php

namespace WPML\Troubleshooting;

use WPML\Troubleshooting\Tools\DbStringsTools;
use WPML\Troubleshooting\Tools\FixToolsTools;
use WPML\Troubleshooting\Tools\ResetTools;
use WPML\Troubleshooting\Tools\TmateTools;

class Plugin {

	public static function boot() {
		load_plugin_textdomain( 'wpml-troubleshooting', false, WPML_TROUBLESHOOTING_FOLDER . '/locale' );

		foreach ( self::groups() as $group ) {
			add_filter( 'wpml_support_tools', [ $group, 'register' ] );
			add_filter( 'wpml_admin_pages_config', [ $group, 'adminPagesConfig' ] );
			$group->hooks();
		}

		add_action( 'admin_enqueue_scripts', [ self::class, 'enqueueToolsStylesheet' ] );
	}


	public static function enqueueToolsStylesheet() {
		if ( ! defined( 'WPML_PLUGIN_FOLDER' ) || ! class_exists( '\\WPML\\SuperGlobals\\Request' ) ) {
			return;
		}
		if ( WPML_PLUGIN_FOLDER . '/menu/support.php' !== \WPML\SuperGlobals\Request::page() ) {
			return;
		}

		wp_enqueue_style(
			'wpml-troubleshooting-tools',
			WPML_TROUBLESHOOTING_URL . '/res/css/tools.css',
			[ 'wpml-support-tailwind' ],
			WPML_TROUBLESHOOTING_VERSION
		);
	}


	private static function groups(): array {
		return [
			new ResetTools(),
			new FixToolsTools(),
			new DbStringsTools(),
			new TmateTools(),
		];
	}
}
