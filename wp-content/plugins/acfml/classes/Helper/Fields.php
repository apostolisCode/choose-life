<?php

namespace ACFML\Helper;

use WPML\FP\Relation;
use WPML\FP\Obj;

class Fields {

	const WRAPPER_FIELDS = [ 'repeater', 'flexible_content' ];

	public static function iterate( $fields, $transformField, $transformLayout, $fieldBasePattern = '' ) {
		foreach ( $fields as &$field ) {
			$fieldPattern = $fieldBasePattern . preg_quote( $field['name'] );
			$field        = $transformField( $field, $fieldPattern );

			if ( isset( $field['sub_fields'] ) ) {
				$fieldPatternSuffix  = 'group' === Obj::prop( 'type', $field ) ? '_' : '_\d+_';
				$field['sub_fields'] = self::iterate( $field['sub_fields'], $transformField, $transformLayout, $fieldPattern . $fieldPatternSuffix );
			}

			if ( isset( $field['layouts'] ) ) {
				foreach ( $field['layouts'] as &$layout ) {
					$layout = $transformLayout( $layout );

					if ( isset( $layout['sub_fields'] ) ) {
						$layout['sub_fields'] = self::iterate( $layout['sub_fields'], $transformField, $transformLayout, $fieldPattern . '_\d+_' );
					}
				}
			}
		}

		return $fields;
	}

	public static function getFresh( array $fieldGroup ): array {
		self::evictFromStore( acf_get_store( 'fields' ), $fieldGroup['ID'] );

		return acf_get_fields( $fieldGroup );
	}

	private static function evictFromStore( $store, $parentId ) {
		foreach ( acf_get_raw_fields( $parentId ) as $field ) {
			$store->remove( $field['key'] );
			self::evictFromStore( $store, $field['ID'] );
		}
	}

	public static function containsType( $fields, $type ) {
		$isType = Relation::propEq( 'type', $type );
		return (bool) wpml_collect( $fields )
			->first( $isType );
	}

	public static function isWrapper( $field ) {
		return in_array(
			Obj::prop( 'type', $field ),
			self::WRAPPER_FIELDS,
			true
		);
	}

	public static function isWrapperOrGroup( $field ) {
		return in_array(
			Obj::prop( 'type', $field ),
			array_merge( [ 'group' ], self::WRAPPER_FIELDS ),
			true
		);
	}
}
