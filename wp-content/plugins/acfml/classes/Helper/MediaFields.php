<?php

namespace ACFML\Helper;

class MediaFields {

	private const FIELD_TYPES = [ 'image', 'gallery', 'file' ];

	public static function getValues( $post_id ) : array {
		return iterator_to_array( self::eachValue( $post_id ) );
	}

	public static function hasValues( $post_id ) : bool {
		foreach ( self::eachValue( $post_id ) as $value ) {
			return true;
		}

		return false;
	}

	private static function eachValue( $post_id ) : \Generator {
		$postMeta = get_post_meta( $post_id );
		if ( ! is_array( $postMeta ) ) {
			return;
		}

		$fieldsCache = [];

		foreach ( $postMeta as $metaKey => $metaValue ) {
			if ( 0 === strpos( $metaKey, '_' ) ) {
				continue;
			}

			$fieldKey = $postMeta[ '_' . $metaKey ][0] ?? '';
			if ( ! $fieldKey || 0 !== strpos( $fieldKey, 'field_' ) ) {
				continue;
			}

			if ( ! array_key_exists( $fieldKey, $fieldsCache ) ) {
				$fieldsCache[ $fieldKey ] = acf_get_field( $fieldKey );
			}
			$field = $fieldsCache[ $fieldKey ];

			if ( ! is_array( $field ) || empty( $field['type'] ) || ! in_array( $field['type'], self::FIELD_TYPES, true ) ) {
				continue;
			}

			yield $metaKey => $metaValue[0];
		}
	}
}
