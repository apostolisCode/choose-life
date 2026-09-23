<?php
/**
 * Plugin Name: WPML Multilingual for CF7
 * Plugin URI: https://wpml.org/documentation/wpml-core-and-add-on-plugins/wpml-multilingual-for-cf7/
 * Description: Make forms from Contact Form 7 translatable with WPML | <a href="https://wpml.org/documentation/wpml-core-and-add-on-plugins/wpml-multilingual-for-cf7/">Documentation</a>
 * Author: OnTheGoSystems
 * Author URI: http://www.onthegosystems.com/
 * Version: 5.0.0
 * Plugin Slug: contact-form-7-multilingual
 *
 * @package wpml/cf7
 */

if ( defined( 'CF7ML_VERSION' ) ) {
	return;
}

define( 'CF7ML_VERSION', '5.0.0' );
define( 'CF7ML_PLUGIN_PATH', dirname( __FILE__ ) );

function cf7ml_init() {
	if ( ! class_exists( 'WPML_Core_Version_Check' ) ) {
		require_once CF7ML_PLUGIN_PATH . '/vendor/wpml-shared/wpml-lib-dependencies/src/dependencies/class-wpml-core-version-check.php';
	}

	if ( ! WPML_Core_Version_Check::is_ok( CF7ML_PLUGIN_PATH . '/wpml-dependencies.json' ) ) {
		return;
	}

	require_once CF7ML_PLUGIN_PATH . '/vendor/autoload.php';

	$action_loader = new \WPML_Action_Filter_Loader();
	$action_loader->load(
		[
			WPML\CF7\Templates::class,
			WPML\CF7\Translations::class,
			WPML\CF7\Language_Metabox::class,
			WPML\CF7\Locale::class,
			WPML\CF7\Shortcodes::class,
			WPML\CF7\TranslationReview::class,
			\WPML\CF7\TranslationEditor\JobFilter::class,
			\WPML\CF7\TranslationEditor\TagTexts::class,
		]
	);
}

add_action( 'wpml_loaded', 'cf7ml_init' );
