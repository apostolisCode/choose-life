<?php

namespace ACFML\Field;

class LinkUrlLoadHooks implements \IWPML_Backend_Action, \IWPML_Frontend_Action, \IWPML_DIC_Action {

	private $fieldResolver;

	private $cache = [];

	public function __construct( Resolver $fieldResolver ) {
		$this->fieldResolver = $fieldResolver;
	}

	public function add_hooks() {
		add_filter( 'acf/load_value/type=url', [ $this, 'convertUrlField' ], 10, 3 );
		add_filter( 'acf/load_value/type=link', [ $this, 'convertLinkField' ], 10, 3 );
	}

	public function convertUrlField( $value, $postId, $field ) {
		return $this->translateUrlValue( $value, $this->resolveTargetLang( $postId ) );
	}

	public function convertLinkField( $value, $postId, $field ) {
		if ( is_array( $value ) && isset( $value['url'] ) ) {
			return $this->translateLinkValue( $value, $this->resolveTargetLang( $postId ) );
		}
		return $value;
	}

	private function resolveTargetLang( $postId ) {
		if ( is_numeric( $postId ) ) {
			$lang = apply_filters( 'wpml_element_language_code', null, [
				'element_id'   => (int) $postId,
				'element_type' => 'post_' . get_post_type( $postId ),
			] );
			if ( $lang ) {
				return $lang;
			}
		}

		return apply_filters( 'wpml_current_language', null );
	}

	private function translateUrlValue( $url, $targetLang ) {
		if ( ! is_string( $url ) || '' === $url || ! $targetLang ) {
			return $url;
		}

		$cacheKey = get_current_blog_id() . '|' . $targetLang . '|' . $url;
		if ( ! array_key_exists( $cacheKey, $this->cache ) ) {
			$processedData            = new \WPML_ACF_Processed_Data( $url, $targetLang, [ 'type' => 'url' ] );
			$this->cache[ $cacheKey ] = $this->fieldResolver->run( $processedData )->convert_ids();
		}

		return $this->cache[ $cacheKey ];
	}

	private function translateLinkValue( array $value, $targetLang ) {
		if ( ! is_string( $value['url'] ) || '' === $value['url'] || ! $targetLang ) {
			return $value;
		}

		$cacheKey = get_current_blog_id() . '|' . $targetLang . '|link|' . md5( serialize( $value ) );
		if ( ! array_key_exists( $cacheKey, $this->cache ) ) {
			$processedData            = new \WPML_ACF_Processed_Data( $value, $targetLang, [ 'type' => 'link' ] );
			$this->cache[ $cacheKey ] = $this->fieldResolver->run( $processedData )->convert_ids();
		}

		return $this->cache[ $cacheKey ];
	}
}
