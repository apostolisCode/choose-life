<?php
/**
 * Plugin Name: WPML Troubleshooting
 * Plugin URI: https://wpml.org/
 * Description: The repair tools for a WPML site, as a plugin of their own | <a href="https://wpml.org/troubleshooting/troubleshooting-tools/">Documentation</a>
 * Author: OnTheGoSystems
 * Author URI: http://www.onthegosystems.com/
 * Version: 5.0.0
 * Plugin Slug: wpml-troubleshooting
 * Text Domain: wpml-troubleshooting
 *
 * @package WPML\Troubleshooting
 */

if ( defined( 'WPML_TROUBLESHOOTING_VERSION' ) ) {
	return;
}

define( 'WPML_TROUBLESHOOTING_VERSION', '5.0.0' );
define( 'WPML_TROUBLESHOOTING_PATH', dirname( __FILE__ ) );
define( 'WPML_TROUBLESHOOTING_URL', plugins_url( '', __FILE__ ) );
define( 'WPML_TROUBLESHOOTING_FOLDER', dirname( plugin_basename( __FILE__ ) ) );

$autoloader_dir = WPML_TROUBLESHOOTING_PATH . '/vendor';
if ( version_compare( PHP_VERSION, '5.3.0' ) >= 0 ) {
	$autoloader = $autoloader_dir . '/autoload.php';
} else {
	$autoloader = $autoloader_dir . '/autoload_52.php';
}
require_once $autoloader;

add_action( 'admin_init', 'wpml_troubleshooting_verify_wpml' );

if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
	return;
}

if ( ! class_exists( 'WPML_Core_Version_Check' )
	|| ! WPML_Core_Version_Check::is_ok( __DIR__ . '/wpml-dependencies.json' )
) {
	return;
}

add_action( 'wpml_loaded', [ WPML\Troubleshooting\Plugin::class, 'boot' ] );

function wpml_troubleshooting_verify_wpml() {
	if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
		add_action( 'admin_notices', 'wpml_troubleshooting_no_wpml_notice' );
	} elseif ( ! class_exists( 'WPML_Core_Version_Check' )
		|| ! WPML_Core_Version_Check::is_ok( __DIR__ . '/wpml-dependencies.json' )
	) {
		add_action( 'admin_notices', 'wpml_troubleshooting_core_outdated_notice' );
	}
}

function wpml_troubleshooting_no_wpml_notice() {
	?>
	<div class="notice notice-error wpml-admin-notice wpml-troubleshooting-inactive wpml-inactive">
		<p><?php echo esc_html__( 'Please activate WPML Multilingual CMS to have WPML Troubleshooting working.', 'wpml-troubleshooting' ); ?></p>
	</div>
	<?php
}

function wpml_troubleshooting_core_outdated_notice() {
	?>
	<div class="notice notice-error wpml-admin-notice wpml-troubleshooting-inactive wpml-outdated">
		<p><?php echo esc_html__( 'WPML Troubleshooting needs WPML 5.0.0 or newer. Please update WPML first.', 'wpml-troubleshooting' ); ?></p>
	</div>
	<?php
}
