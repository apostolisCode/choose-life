<?php

namespace WPML\Troubleshooting\Actions;

class FixTermsCount {

	public function run() {
		global $sitepress;

		$has_get_terms_args_filter = remove_filter( 'get_terms_args', array( $sitepress, 'get_terms_args_filter' ) );
		$has_get_term_filter       = remove_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1 );
		$has_terms_clauses_filter  = remove_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ) );

		foreach ( get_taxonomies( array(), 'names' ) as $taxonomy ) {
			$terms_objects = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => 0,
				)
			);
			if ( is_array( $terms_objects ) ) {
				$term_taxonomy_ids = array_map(
					function ( $term_object ) {
						return $term_object->term_taxonomy_id;
					},
					$terms_objects
				);
				wp_update_term_count( $term_taxonomy_ids, $taxonomy, true );
			}
		}

		if ( $has_terms_clauses_filter ) {
			add_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ), 10, 3 );
		}
		if ( $has_get_term_filter ) {
			add_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1, 1 );
		}
		if ( $has_get_terms_args_filter ) {
			add_filter( 'get_terms_args', array( $sitepress, 'get_terms_args_filter' ), 10, 2 );
		}
	}
}
