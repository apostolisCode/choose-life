<?php
function cptui_register_my_cpts() {

	/**
	 * Post Type: Donations.
	 */

	$labels = [
		"name"          => esc_html__( "Donations", "choose-life" ),
		"singular_name" => esc_html__( "Donation", "choose-life" ),
	];

	$args = [
		"label"                 => esc_html__( "Donations", "choose-life" ),
		"labels"                => $labels,
		"description"           => "",
		"public"                => false,
		"publicly_queryable"    => true,
		"show_ui"               => true,
		"show_in_rest"          => false,
		"rest_base"             => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace"        => "wp/v2",
		"has_archive"           => false,
		"show_in_menu"          => true,
		"show_in_nav_menus"     => false,
		"delete_with_user"      => false,
		"exclude_from_search"   => true,
		"capability_type"       => "post",
		'capabilities'        => [
			'create_posts' => false,
		],
		"map_meta_cap"          => true,
		"hierarchical"          => false,
		"can_export"            => false,
		"rewrite"               => [ "slug" => "donations", "with_front" => true ],
		"query_var"             => true,
		"menu_icon"             => "dashicons-money-alt",
		"supports"              => [ "title" ],
		"show_in_graphql"       => false,
	];

	register_post_type( "donations", $args );

	/**
	 * Post Type: Subscriptions.
	 */

	$labels = [
		"name"          => esc_html__( "Subscriptions", "choose-life" ),
		"singular_name" => esc_html__( "Subscription", "choose-life" ),
	];

	$args = [
		"label"                 => esc_html__( "Subscriptions", "choose-life" ),
		"labels"                => $labels,
		"description"           => "",
		"public"                => false,
		"publicly_queryable"    => true,
		"show_ui"               => true,
		"show_in_rest"          => false,
		"rest_base"             => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace"        => "wp/v2",
		"has_archive"           => false,
		"show_in_menu"          => true,
		"show_in_nav_menus"     => false,
		"delete_with_user"      => false,
		"exclude_from_search"   => true,
		"capability_type"       => "post",
		'capabilities'        => [
			'create_posts' => false,
		],
		"map_meta_cap"          => true,
		"hierarchical"          => false,
		"can_export"            => false,
		"rewrite"               => [ "slug" => "subscriptions", "with_front" => true ],
		"query_var"             => true,
		"menu_icon"             => "dashicons-money",
		"supports"              => [ "title" ],
		"show_in_graphql"       => false,
	];

	register_post_type( "subscriptions", $args );
}

add_action( 'init', 'cptui_register_my_cpts' );
