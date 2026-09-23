<?php

namespace WPML\WPSEO\RankMathSEO\Compatibility\WooCommerce;

use WPML\FP\Obj;

class CategoryBaseHooks implements \IWPML_Frontend_Action, \IWPML_Backend_Action, \IWPML_REST_Action {

	const PRIORITY_AFTER_WPML_LANGUAGE_PREFIX = 2;

	const GENERAL_OPTIONS_KEY = 'rank-math-options-general';

	const REMOVE_PARENT_SLUGS_KEY = 'wc_remove_category_parent_slugs';

	public function add_hooks() {
		if ( $this->isRemoveParentSlugsEnabled() ) {
			add_filter(
				'term_link',
				[ $this, 'restoreTranslatedCategoryBase' ],
				self::PRIORITY_AFTER_WPML_LANGUAGE_PREFIX,
				3
			);
		}
	}

	public function restoreTranslatedCategoryBase( $link, $term, $taxonomy ) {
		if ( 'product_cat' !== $taxonomy ) {
			return $link;
		}

		$base = $this->getCategoryBase();

		if ( '' === $base ) {
			return $link;
		}

		$language = $this->getTermLanguage( $term, $taxonomy );

		if ( ! $language ) {
			return $link;
		}

		$translatedBase = $this->getTranslatedBase( $base, $taxonomy, $language );

		if ( $translatedBase === $base ) {
			return $link;
		}

		return $this->replaceBase( $link, $term->slug, $base, $translatedBase );
	}

	private function replaceBase( string $link, string $slug, string $base, string $translatedBase ): string {
		$pathLength = strcspn( $link, '?#' );
		$path       = substr( $link, 0, $pathLength );
		$search     = '/' . $base . '/' . $slug;
		$position   = strrpos( $path, $search );

		if ( false === $position ) {
			return $link;
		}

		$path = substr_replace( $path, '/' . $translatedBase . '/' . $slug, $position, strlen( $search ) );

		return $path . substr( $link, $pathLength );
	}

	private function getTermLanguage( $term, string $taxonomy ): ?string {
		$language = apply_filters(
			'wpml_element_language_code',
			null,
			[
				'element_id'   => $term->term_taxonomy_id,
				'element_type' => 'tax_' . $taxonomy,
			]
		);

		return $language ? (string) $language : null;
	}

	private function getTranslatedBase( string $base, string $taxonomy, string $language ): string {
		return (string) apply_filters(
			'wpml_translate_single_string',
			$base,
			'WordPress',
			sprintf( 'URL %s tax slug', $taxonomy ),
			$language
		);
	}

	private function getCategoryBase(): string {
		return trim( (string) Obj::prop( 'category_rewrite_slug', wc_get_permalink_structure() ), '/' );
	}

	private function isRemoveParentSlugsEnabled(): bool {
		$settings = (array) get_option( self::GENERAL_OPTIONS_KEY, [] );

		return isset( $settings[ self::REMOVE_PARENT_SLUGS_KEY ] )
			&& 'on' === $settings[ self::REMOVE_PARENT_SLUGS_KEY ];
	}
}
