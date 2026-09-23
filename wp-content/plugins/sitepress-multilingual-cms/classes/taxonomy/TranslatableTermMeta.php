<?php

namespace WPML\TM\Taxonomy;

class TranslatableTermMeta {

	const UNIT_ID_PREFIX = 'term-meta-';

	public static function keys( string $taxonomy, ?\WP_Term $term = null ): array {
		$keys = self::keysFromTmSettings();

		$keys = apply_filters( 'wpml_translatable_term_meta', $keys, $taxonomy, $term );

		if ( ! is_array( $keys ) ) {
			return [];
		}

		return array_values( array_unique( array_filter( array_map( 'strval', $keys ) ) ) );
	}

	public static function values( \WP_Term $term ): array {
		return self::valuesById( (int) $term->term_id, self::keys( (string) $term->taxonomy, $term ), (string) $term->taxonomy );
	}

	public static function metaTextById( int $termId, array $keys, string $taxonomy = '' ): string {
		return implode( "\n", self::valuesById( $termId, $keys, $taxonomy ) );
	}

	public static function metaText( \WP_Term $term ): string {
		return implode( "\n", self::values( $term ) );
	}

	private static function valuesById( int $termId, array $keys, string $taxonomy = '' ): array {
		if ( ! $termId ) {
			return [];
		}

		$values = [];
		if ( $keys ) {
			foreach ( $keys as $key ) {
				$value = get_term_meta( $termId, $key, true );
				if ( ! is_string( $value ) && ! is_numeric( $value ) ) {
					continue;
				}
				$value = (string) $value;
				if ( '' === trim( $value ) ) {
					continue;
				}
				$values[ $key ] = $value;
			}
		}

		$values = apply_filters( 'wpml_translatable_term_meta_values', $values, $termId, $taxonomy );

		return self::sanitizeValues( $values );
	}

	private static function sanitizeValues( $values ): array {
		if ( ! is_array( $values ) ) {
			return [];
		}

		$clean = [];
		foreach ( $values as $key => $value ) {
			if ( ! is_string( $key ) || '' === $key ) {
				continue;
			}
			if ( ! is_string( $value ) && ! is_numeric( $value ) ) {
				continue;
			}
			$value = (string) $value;
			if ( '' === trim( $value ) ) {
				continue;
			}
			$clean[ $key ] = $value;
		}

		return $clean;
	}

	private static function keysFromTmSettings(): array {
		global $iclTranslationManagement;

		if (
			! $iclTranslationManagement
			|| ! defined( 'WPML_TERM_META_SETTING_INDEX_PLURAL' )
			|| ! defined( 'WPML_TRANSLATE_CUSTOM_FIELD' )
			|| ! isset( $iclTranslationManagement->settings[ WPML_TERM_META_SETTING_INDEX_PLURAL ] )
			|| ! is_array( $iclTranslationManagement->settings[ WPML_TERM_META_SETTING_INDEX_PLURAL ] )
		) {
			return [];
		}

		$keys = [];
		foreach ( $iclTranslationManagement->settings[ WPML_TERM_META_SETTING_INDEX_PLURAL ] as $key => $state ) {
			if ( (int) WPML_TRANSLATE_CUSTOM_FIELD === (int) $state ) {
				$keys[] = (string) $key;
			}
		}

		return $keys;
	}
}
