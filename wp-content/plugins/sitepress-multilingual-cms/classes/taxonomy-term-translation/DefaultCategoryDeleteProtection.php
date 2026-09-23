<?php

namespace WPML\TaxonomyTermTranslation;

class DefaultCategoryDeleteProtection implements \IWPML_Backend_Action, \IWPML_AJAX_Action, \IWPML_REST_Action, \IWPML_DIC_Action {

	private static $protected_term_ids;

	private $sitepress;

	public function __construct( \SitePress $sitepress ) {
		$this->sitepress = $sitepress;
	}

	public static function resetProtectedTermIds() {
		self::$protected_term_ids = null;
	}

	public function add_hooks() {
		add_filter( 'map_meta_cap', array( $this, 'protectDefaultCategories' ), 10, 4 );
		add_action( 'load-edit-tags.php', array( $this, 'dropProtectedTermsFromBulkDelete' ) );
	}

	public function protectDefaultCategories( $caps, $cap, $user_id, $args ) {
		if ( 'delete_term' !== $cap ) {
			return $caps;
		}

		$default_categories = (array) $this->sitepress->get_setting( 'default_categories', array() );
		if ( ! $default_categories ) {
			return $caps;
		}

		$term_id = isset( $args[0] ) ? (int) $args[0] : 0;
		if ( ! $term_id ) {
			return $caps;
		}

		$term = get_term( $term_id );
		if ( ! $term || is_wp_error( $term ) || ! isset( $term->taxonomy ) || 'category' !== $term->taxonomy ) {
			return $caps;
		}

		if ( in_array( $term_id, self::protectedTermIds( $default_categories ), true ) ) {
			$caps[] = 'do_not_allow';
		}

		return $caps;
	}

	private static function protectedTermIds( array $default_categories ) {
		if ( null !== self::$protected_term_ids ) {
			return self::$protected_term_ids;
		}

		global $wpdb;

		$ttids = array_filter( array_map( 'intval', $default_categories ) );

		self::$protected_term_ids = $ttids
			? array_map(
				'intval',
				$wpdb->get_col(
					"SELECT term_id
					 FROM {$wpdb->term_taxonomy}
					 WHERE taxonomy='category'
					 AND term_taxonomy_id IN (" . wpml_prepare_in( $ttids, '%d' ) . ')'
				)
			)
			: array();

		return self::$protected_term_ids;
	}

	public function dropProtectedTermsFromBulkDelete() {
		$taxonomy = isset( $_REQUEST['taxonomy'] )
			? sanitize_text_field( wp_unslash( $_REQUEST['taxonomy'] ) ) : 'post_tag';
		if ( 'category' !== $taxonomy || ! isset( $_REQUEST['delete_tags'] ) ) {
			return;
		}

		$default_categories = (array) $this->sitepress->get_setting( 'default_categories', array() );
		if ( ! $default_categories ) {
			return;
		}

		$protected = array();
		foreach ( $default_categories as $default_category ) {
			$protected[] = (int) $default_category;
		}

		$kept          = array();
		$submitted_ids = array_map( 'intval', (array) wp_unslash( $_REQUEST['delete_tags'] ) );
		foreach ( $submitted_ids as $term_id ) {
			$term_taxonomy_id = $this->category_ttid_of_term_id( $term_id );
			if ( $term_taxonomy_id && in_array( $term_taxonomy_id, $protected, true ) ) {
				continue;
			}

			$kept[] = $term_id;
		}

		$_REQUEST['delete_tags'] = $kept;
		$_POST['delete_tags']    = $kept;
	}

	private function category_ttid_of_term_id( $term_id ) {
		global $wpdb;

		$term_id = (int) $term_id;
		if ( $term_id <= 0 ) {
			return 0;
		}

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT term_taxonomy_id
				     FROM {$wpdb->term_taxonomy}
				     WHERE term_id = %d
				     AND taxonomy = 'category'",
				$term_id
			)
		);
	}
}
