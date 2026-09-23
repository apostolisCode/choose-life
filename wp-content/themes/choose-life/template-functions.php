<?php
/* =========================================
   THEME ACTIONS & FILTERS
  ========================================= */

if ( ! function_exists( 'theme_setup' ) ) {
	/**
	 * Setup the theme
	 *
	 * @since 1.0
	 */
	function theme_setup() {

		// Theme translations (languages/{locale}.l10n.php)
		load_theme_textdomain( 'choose-life', get_template_directory() . '/languages' );

		// Let wp know we want to use html5 for content
		add_theme_support( 'html5', array(
			'comment-list',
			'comment-form',
			'search-form',
			'gallery',
			'caption',
			'style',
			'script'
		) );

		// Let wp know we want to use post thumbnails
		/* add_theme_support( 'post-thumbnails', array(
			'post',
			..more post types here
		) ); */

		// Add Custom Logo Support.
		/*add_theme_support( 'custom-logo', array(
			'width'      => 200, // Example Width Size
			'height'     => 127,  // Example Height Size
			'flex-width' => true,
		) );*/

		add_image_size( 'picto', 80, 80 );

		// Register navigation menus for theme
		register_nav_menus( array(
			'header-menu-left'  => 'Header Menu (left)',
			'header-menu-right' => 'Header Menu (right)',
			'footer-menu'       => 'Footer Menu',
			'footer-menu-info'  => 'Footer Menu (information)',
		) );

		// Let wp know we are going to handle styling galleries
		// add_filter( 'use_default_gallery_style', '__return_false' );

		// Stop WP from printing emoji service on the front
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );

		// remove wordpress version on head
		remove_action( 'wp_head', 'wp_generator' );

		// Remove toolbar for all users in front end
		// show_admin_bar( false );

		/*  Add Custom Image Sizes
		 *
		 *  Docs: https://developer.wordpress.org/reference/functions/add_image_size/
		 */ // add_image_size( 'SIZE_NAME_HERE', 100, 100, true );

		// WPML configuration
		// disable plugin from printing styles and js
		// we are going to handle all that ourselves.
		if ( ! is_admin() ) {
			define( 'ICL_DONT_LOAD_NAVIGATION_CSS', true );
			define( 'ICL_DONT_LOAD_LANGUAGE_SELECTOR_CSS', true );
			define( 'ICL_DONT_LOAD_LANGUAGES_JS', true );
		}

		// Register Autoloaders Loader
		$theme_dir = get_template_directory();
		include "$theme_dir/library/library-loader.php";
		include "$theme_dir/components/components-loader.php";
		include "$theme_dir/includes/includes-loader.php";

	}
}

if ( ! function_exists( 'theme_asset_version' ) ) {
	/**
	 * Cache-busting version for a built asset (its modification time),
	 * so browsers pick up new builds of the fixed-name entry files.
	 *
	 * @param string $path Path relative to the theme's assets/ directory.
	 *
	 * @return string|null
	 */
	function theme_asset_version( $path ) {
		$file = get_template_directory() . '/assets/' . $path;

		return file_exists( $file ) ? (string) filemtime( $file ) : null;
	}
}

if ( ! function_exists( 'theme_styles' ) ) {
	/**
	 * Register and/or Enqueue
	 * Styles for the theme
	 *
	 * @since 1.0
	 */
	function theme_styles() {
		$theme_dir = get_template_directory_uri();
		wp_enqueue_style( 'main', "$theme_dir/assets/css/main.css", array(), theme_asset_version( 'css/main.css' ), 'all' );
	}
}
if ( ! function_exists( 'remove_block_style' ) ) {
	/**
	 * Remove Block Library & Styles
	 *
	 * @since 1.0
	 */
	function remove_block_style() {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wc-block-style' );
	}
}

if ( ! function_exists( 'theme_scripts' ) ) {
	/**
	 * Register and/or Enqueue
	 * Scripts for the theme
	 *
	 * @since 1.0
	 */
	function theme_scripts() {
		$theme_dir = get_template_directory_uri();
		wp_enqueue_script( 'vendors', "$theme_dir/assets/js/vendors.js", ['jquery'], theme_asset_version( 'js/vendors.js' ), true );
		wp_enqueue_script( 'main', "$theme_dir/assets/js/main.js", ['jquery', 'vendors'], theme_asset_version( 'js/main.js' ), true );
		if ( is_page_template( 'templates/my-account.php' ) ) {
			wp_enqueue_script( 'my-account', "$theme_dir/assets/js/my-account.js", ['jquery', 'vendors'], theme_asset_version( 'js/my-account.js' ), true );
		}
		if ( is_page_template( 'templates/checkout.php' ) ) {
			wp_enqueue_script( 'checkout', "$theme_dir/assets/js/checkout.js", ['jquery', 'vendors'], theme_asset_version( 'js/checkout.js' ), true );
		}
		if ( is_page_template( 'templates/donation.php' ) ) {
			wp_enqueue_script( 'donation', "$theme_dir/assets/js/donation.js", ['jquery', 'vendors'], theme_asset_version( 'js/donation.js' ), true );
		}
		if ( is_page_template( 'templates/volunteer.php' ) ) {
			wp_enqueue_script( 'volunteer', "$theme_dir/assets/js/volunteer.js", ['jquery', 'vendors'], theme_asset_version( 'js/volunteer.js' ), true );
		}
	}
}

if ( ! function_exists( 'theme_scripts_localize' ) ) {
	/**
	 * Attach variables we want
	 * to expose to our JS
	 *
	 * @since 3.12.0
	 */
	function theme_scripts_localize() {
		wp_localize_script( 'main', 'urls', [
			'home'    => get_home_url(),
			'theme'   => get_stylesheet_directory_uri(),
			'assets'  => get_stylesheet_directory_uri() . '/assets',
			'ajax'    => admin_url( 'admin-ajax.php' ),
			'rest'    => get_rest_url(),
			'privacy' => get_privacy_policy_url(),
			'nonce'   => wp_create_nonce( 'cl_ajax' ),
		] );
	}
}

if ( ! function_exists( 'acf_json_save_point' ) ) {
	/**
	 * ACF save field groups to a folder
	 *
	 */
	function acf_json_save_point( $path ) {
		$path = __DIR__ . '/acf-json';

		return $path;
	}
}

if ( ! function_exists( 'acf_json_load_point' ) ) {
	/**
	 * ACF load field groups from parent theme folder
	 *
	 */
	function acf_json_load_point( $paths ) {
		$paths[] = get_template_directory() . '/acf-json';

		return $paths;
	}
}


if ( ! function_exists( 'acf_init_options_page' ) ) {
	/**
	 * Add Theme options page.
	 */
	function acf_init_options_page() {
		if ( function_exists( 'acf_add_options_page' ) ) {
			$option_page = acf_add_options_page( array(
				'page_title' => 'General Settings',
				'menu_title' => 'Theme Options',
				'menu_slug'  => 'theme-general-settings',
				'capability' => 'edit_posts',
				'redirect'   => false
			) );
		}
	}
}

/* =========================================
  YOUR CUSTOM FUNCTIONS
  ========================================= */

if ( ! function_exists( 'admin_styles' ) ) {
	function admin_styles() {
		$theme_dir = get_template_directory_uri();
		wp_enqueue_style( 'admin', "$theme_dir/assets/css/admin.css", array(), theme_asset_version( 'css/admin.css' ), 'all' );
	}
}

if ( ! function_exists( 'admin_scripts' ) ) {
	function admin_scripts() {
		$theme_dir = get_template_directory_uri();
		wp_enqueue_script( 'admin-main', "$theme_dir/assets/js/admin.js", array( 'jquery' ), theme_asset_version( 'js/admin.js' ), true );
	}
}

if ( ! function_exists( 'write_log' ) ) {
	function write_log( $log ) {
		$current_date_time = "[" . wp_date( 'Y-m-d H:i:s T' ) . "]";
		if ( is_array( $log ) || is_object( $log ) ) {
			error_log( $current_date_time . " " . print_r( $log, true ) . "\n", 3, WP_CONTENT_DIR . "/site-debug.log" );
		} else {
			error_log( $current_date_time . " " . $log . "\n", 3, WP_CONTENT_DIR . "/site-debug.log" );
		}
	}
}
