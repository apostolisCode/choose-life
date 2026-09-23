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

	/**
	 * Post Type: FAQs (question = title, answer = content).
	 * Shown on the FAQ page (templates/faq.php) and picked per page elsewhere.
	 */

	$labels = [
		"name"          => esc_html__( "FAQs", "choose-life" ),
		"singular_name" => esc_html__( "FAQ", "choose-life" ),
		"add_new_item"  => esc_html__( "Add question", "choose-life" ),
		"edit_item"     => esc_html__( "Edit question", "choose-life" ),
	];

	$args = [
		"label"               => esc_html__( "FAQs", "choose-life" ),
		"labels"              => $labels,
		"public"              => false,
		"publicly_queryable"  => false,
		"show_ui"             => true,
		"show_in_rest"        => false,
		"has_archive"         => false,
		"show_in_menu"        => true,
		"show_in_nav_menus"   => false,
		"exclude_from_search" => true,
		"capability_type"     => "post",
		"map_meta_cap"        => true,
		"hierarchical"        => false,
		"rewrite"             => false,
		"query_var"           => false,
		"menu_icon"           => "dashicons-editor-help",
		"supports"            => [ "title", "editor", "page-attributes" ],
		"taxonomies"          => [ "faq_category" ],
	];

	register_post_type( "faq", $args );

	/**
	 * Taxonomy: FAQ categories (the tabs of the FAQ page).
	 */

	register_taxonomy( "faq_category", [ "faq" ], [
		"labels"            => [
			"name"          => esc_html__( "FAQ categories", "choose-life" ),
			"singular_name" => esc_html__( "FAQ category", "choose-life" ),
		],
		"public"            => false,
		"show_ui"           => true,
		"show_admin_column" => true,
		"show_in_nav_menus" => false,
		"show_in_rest"      => false,
		"hierarchical"      => true,
		"rewrite"           => false,
		"query_var"         => false,
	] );

	/**
	 * Post Type: Journeys of hope (one per donation: donor → hospital → patient).
	 * Shown on the globe of the volunteer page (templates/volunteer.php).
	 */

	$labels = [
		"name"          => esc_html__( "Journeys of hope", "choose-life" ),
		"singular_name" => esc_html__( "Journey of hope", "choose-life" ),
		"add_new_item"  => esc_html__( "Add journey", "choose-life" ),
		"edit_item"     => esc_html__( "Edit journey", "choose-life" ),
	];

	$args = [
		"label"               => esc_html__( "Journeys of hope", "choose-life" ),
		"labels"              => $labels,
		"public"              => false,
		"publicly_queryable"  => false,
		"show_ui"             => true,
		"show_in_rest"        => false,
		"has_archive"         => false,
		"show_in_menu"        => true,
		"show_in_nav_menus"   => false,
		"exclude_from_search" => true,
		"capability_type"     => "post",
		"map_meta_cap"        => true,
		"hierarchical"        => false,
		"rewrite"             => false,
		"query_var"           => false,
		"menu_icon"           => "dashicons-admin-site-alt3",
		"supports"            => [ "title", "page-attributes" ],
		"taxonomies"          => [ "journey_hospital" ],
	];

	register_post_type( "journey", $args );

	/**
	 * Taxonomy: hospitals of donation (city + coordinates as ACF term fields).
	 */

	register_taxonomy( "journey_hospital", [ "journey" ], [
		"labels"            => [
			"name"          => esc_html__( "Hospitals", "choose-life" ),
			"singular_name" => esc_html__( "Hospital", "choose-life" ),
			"add_new_item"  => esc_html__( "Add hospital", "choose-life" ),
		],
		"public"            => false,
		"show_ui"           => true,
		"show_admin_column" => true,
		"show_in_nav_menus" => false,
		"show_in_rest"      => false,
		"hierarchical"      => false,
		"meta_box_cb"       => false,   // picked with the ACF "Hospital" field
		"rewrite"           => false,
		"query_var"         => false,
	] );
}

add_action( 'init', 'cptui_register_my_cpts' );
