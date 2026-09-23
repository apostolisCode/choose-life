<?php

namespace ACFML\Field;

use ACFML\Helper\Fields;
use WPML\FP\Fns;
use WPML\FP\Obj;

class LinkJobAttributes implements \IWPML_Backend_Action, \IWPML_Frontend_Action, \IWPML_REST_Action, \IWPML_DIC_Action {

	const FIELD_TYPE = 'link';

	private static $patterns;

	private static $isLinkKeyCache = [];

	public function add_hooks() {
		if ( ! function_exists( 'acf_get_field_groups' ) ) {
			return;
		}

		add_filter( 'wpml_translation_job_post_meta_value_translated', [ $this, 'onlyTheLinkContentTranslates' ], 10, 2 );

		add_filter( 'acf/update_field_group', [ $this, 'invalidate' ], 20, 1 );
		add_filter( 'acf/update_field', [ $this, 'invalidate' ], 20, 1 );
		add_filter( 'acf/delete_field', [ $this, 'invalidate' ], 20, 1 );
		add_filter( 'acf/delete_field_group', [ $this, 'invalidate' ], 20, 1 );

		add_action( 'switch_blog', [ $this, 'invalidateOnBlogSwitch' ], 10, 0 );
	}

	public function invalidate( $value = null ) {
		self::$patterns       = null;
		self::$isLinkKeyCache = [];

		return $value;
	}

	public function invalidateOnBlogSwitch() {
		$this->invalidate();
	}

	public function onlyTheLinkContentTranslates( $isTranslatable, $fieldType ) {
		if ( ! $isTranslatable || ! is_string( $fieldType ) ) {
			return $isTranslatable;
		}

		list( $metaKey, $attributes ) = \WPML_TM_Field_Type_Encoding::decode( $fieldType );

		if ( ! $metaKey || ! $attributes || ! self::isLinkKey( $metaKey ) ) {
			return $isTranslatable;
		}

		return in_array( end( $attributes ), CompositeValue::getSubKeysForType( self::FIELD_TYPE ), true )
			? $isTranslatable
			: 0;
	}

	public static function isLinkKey( $metaKey ) {
		if ( ! array_key_exists( $metaKey, self::$isLinkKeyCache ) ) {
			self::$isLinkKeyCache[ $metaKey ] = self::matchesAPattern( $metaKey );
		}

		return self::$isLinkKeyCache[ $metaKey ];
	}

	private static function matchesAPattern( $metaKey ) {
		foreach ( self::getPatterns() as $pattern ) {
			if ( preg_match( '#^' . $pattern . '$#', $metaKey ) ) {
				return true;
			}
		}

		return false;
	}

	private static function getPatterns() {
		if ( null === self::$patterns ) {
			$built = self::buildPatterns();
			if ( null === $built ) {
				return [];
			}
			self::$patterns = $built;
		}

		return self::$patterns;
	}

	private static function buildPatterns() {
		if ( ! function_exists( 'acf_get_field_groups' ) || ! function_exists( 'acf_get_fields' ) ) {
			return null;
		}

		$patterns = [];

		$collect = function ( $field, $fieldPattern ) use ( &$patterns ) {
			if ( self::FIELD_TYPE === Obj::prop( 'type', $field ) ) {
				$patterns[] = $fieldPattern;
			}

			return $field;
		};

		foreach ( acf_get_field_groups() as $fieldGroup ) {
			Fields::iterate( acf_get_fields( $fieldGroup ), $collect, Fns::identity() );
		}

		return $patterns;
	}
}
