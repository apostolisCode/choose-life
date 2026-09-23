<?php

namespace WPML\WPSEO\YoastSEO\Terms;

use WPML\Element\API\Translations;
use WPML\FP\Fns;
use WPML\FP\Maybe;
use WPML\FP\Obj;
use WPML\LIB\WP\Hooks;
use WPSEO_Taxonomy_Meta;
use function WPML\FP\spreadArgs;

class AdminHooks implements \IWPML_Backend_Action {

	public function add_hooks() {
		Hooks::onAction( 'created_term', 10, 3 )
			->then( spreadArgs( [ $this, 'copyOnceTermMeta' ] ) );
	}

	public function copyOnceTermMeta( $termId, $ttId, $taxonomy ) {
		$disableTermAdjustId = Fns::always( true );

		add_filter( 'wpml_disable_term_adjust_id', $disableTermAdjustId );

		$wpSeoTaxonomyMeta = WPSEO_Taxonomy_Meta::get_instance();

		$getTermByElement = function ( $translationElement ) use ( $taxonomy ) {
			return get_term_by( 'term_taxonomy_id', $translationElement->element_id, $taxonomy );
		};

		$getOriginalMeta = function ( $originalTerm ) use ( $wpSeoTaxonomyMeta, $taxonomy ) {
			return $wpSeoTaxonomyMeta->get_term_meta( $originalTerm, $taxonomy );
		};

		$filterValuesToCopyOnce = Obj::pick( self::getKeysToCopyOnce() );

		$setTranslationMeta = function ( $filteredMeta ) use ( $wpSeoTaxonomyMeta, $termId, $taxonomy ) {
			$wpSeoTaxonomyMeta->set_values( $termId, $taxonomy, $filteredMeta );
		};

		Maybe::fromNullable( Translations::getOriginal( $ttId, "tax_$taxonomy" ) )
			->map( $getTermByElement )
			->map( $getOriginalMeta )
			->map( $filterValuesToCopyOnce )
			->map( $setTranslationMeta );

		remove_filter( 'wpml_disable_term_adjust_id', $disableTermAdjustId );
	}

	private static function getKeysToCopyOnce() {
		$keys = [
			'wpseo_noindex',
			'wpseo_opengraph-image',
			'wpseo_opengraph-image-id',
			'wpseo_twitter-image',
			'wpseo_twitter-image-id',
		];

		return (array) apply_filters( 'wpmlseo_yoast_term_meta_keys_to_copy_once', $keys );
	}
}
