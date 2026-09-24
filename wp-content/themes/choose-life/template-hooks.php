<?php

/**
 * @hook after_setup_theme
 *
 * @see  theme_setup()
 */
add_action( 'after_setup_theme', 'theme_setup' );

/**
 * @hook wp_enqueue_scripts
 *
 * @see  theme_styles()
 * @see  theme_scripts()
 * @see  theme_scripts_localize()
 */
add_action( 'wp_enqueue_scripts', 'theme_styles' );
add_action( 'admin_enqueue_scripts', 'admin_styles' );
add_action( 'wp_enqueue_scripts', 'theme_scripts' );
add_action( 'wp_enqueue_scripts', 'theme_scripts_localize', 20 );
add_action( 'admin_enqueue_scripts', 'admin_scripts' );

/**
 * @hook wp_print_styles
 *
 * @see  remove_block_style()
 */
add_action( 'wp_print_styles', 'remove_block_style' );

/**
 * ACF functions & filters
 *
 * @see  acf_json_save_point()
 * @see  acf_json_load_point()
 * @see  acf_init_options_page()
 */
add_filter( 'acf/settings/save_json', 'acf_json_save_point' );
add_filter( 'acf/settings/load_json', 'acf_json_load_point' );
add_action( 'acf/init', 'acf_init_options_page' );


add_action( 'init', function () {

	// Remove the REST API endpoint.
	remove_action( 'rest_api_init', 'wp_oembed_register_route' );

	// Turn off oEmbed auto discovery.
	add_filter( 'embed_oembed_discover', '__return_false' );

	// Don't filter oEmbed results.
	remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );

	// Remove oEmbed discovery links.
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

	// Remove oEmbed-specific JavaScript from the front-end and back-end.
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	add_filter( 'tiny_mce_plugins', function ( $plugins ) {
		return array_diff( $plugins, array( 'wpembed' ) );
	} );

	// Remove all embeds rewrite rules.
	add_filter( 'rewrite_rules_array', function ( $rules ) {
		foreach ( $rules as $rule => $rewrite ) {
			if ( false !== strpos( $rewrite, 'embed=true' ) ) {
				unset( $rules[ $rule ] );
			}
		}

		return $rules;
	} );

	// Remove filter of the oEmbed result before any HTTP requests are made.
	remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );
}, 9999 );


// Disable use XML-RPC
add_filter( 'xmlrpc_enabled', function () {
	return false;
} );

// Disable X-Pingback to header
add_filter( 'wp_headers', function ( $headers ) {
	unset( $headers['X-Pingback'] );

	return $headers;
} );

// Remove WP version information
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', function () {
	return '';
} );

// Pick out the version number from scripts and styles
function remove_version_from_style_js( $src ) {
	if ( strpos( $src, 'ver=' . get_bloginfo( 'version' ) ) ) {
		$src = remove_query_arg( 'ver', $src );
	}

	return $src;
}

add_filter( 'style_loader_src', 'remove_version_from_style_js' );
add_filter( 'script_loader_src', 'remove_version_from_style_js' );

/* =========================================
  YOUR CUSTOM ACTIONS & FILTERS
  ========================================= */

add_filter( 'acf/settings/enable_post_types', '__return_false' );

// CF7 form markup comes from theme templates (elements/cf7-*.php): no auto <p>/<br>
add_filter( 'wpcf7_autop_or_not', '__return_false' );

// CF7 [select data:countries] (Listo plugin): Listo has no Greek translation, so
// name its countries in the site language via intl, sorted, with Greece first.
add_filter( 'wpcf7_form_tag_data_option', function ( $data, $options ) {
	if ( ! in_array( 'countries', (array) $options, true ) || ! is_array( $data ) || ! class_exists( 'Locale' ) || ! function_exists( 'listo' ) ) {
		return $data;
	}

	$locale  = get_locale();
	$a3_to_a2 = array_flip( array_map( 'strtolower', listo( 'countries_a2a3' ) ?: [] ) );

	$countries = [];
	foreach ( $data as $a3 => $name ) {
		$a2 = $a3_to_a2[ $a3 ] ?? '';
		$localized = $a2 ? Locale::getDisplayRegion( '-' . strtoupper( $a2 ), $locale ) : '';
		// intl echoes the code back when it doesn't know the region
		$countries[ $a3 ] = $localized && strcasecmp( $localized, $a2 ) !== 0 ? $localized : $name;
	}

	if ( class_exists( 'Collator' ) ) {
		( new Collator( $locale ) )->asort( $countries );
	} else {
		asort( $countries );
	}

	if ( isset( $countries['grc'] ) ) {
		$countries = [ 'grc' => $countries['grc'] ] + $countries;
	}

	return $countries;
}, 20, 2 );

// The front-end (checkout / my account) talks to admin-ajax.php, where WordPress
// would answer in the logged-in user's profile language. Answer the theme's
// cl_* actions in the site language instead, like the pages themselves.
add_action( 'admin_init', function () {
	$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';
	if ( wp_doing_ajax() && strpos( $action, 'cl_' ) === 0 && get_locale() !== determine_locale() ) {
		switch_to_locale( get_locale() );
	}
}, 1 );

// retry of the emails wp_mail() could not send (Inc_Email::schedule_email)
add_action( 'cl_schedule_email_notification', [ 'Inc_Email', 'send_scheduled_email' ], 10, 5 );

add_action( 'after_setup_theme', [ 'Inc_Api', 'get_instance' ] );
add_action( 'after_setup_theme', [ 'Inc_Auth', 'get_instance' ] );
add_action( 'after_setup_theme', [ 'Inc_Subscription', 'get_instance' ] );
add_action( 'acf/init', [ 'Inc_Admin', 'get_instance' ] );
add_action( 'acf/init', [ 'Inc_Window_Data', 'get_instance' ] );
