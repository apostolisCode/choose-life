<?php

namespace WPML\LanguageEditor;

class RtlLanguages {

	const DEFAULT_RTL_LANGUAGES = [ 'ar', 'he', 'fa', 'ku', 'ur' ];

	private static $storedOverrides;

	public static function isRtl( $code ) {
		$code = strtolower( trim( (string) $code ) );
		if ( '' === $code ) {
			return false;
		}

		return (bool) apply_filters( 'wpml_language_is_rtl', self::resolve( $code ), $code );
	}

	private static function resolve( $code ) {

		$override = self::storedOverride( $code );
		if ( null !== $override ) {
			return $override;
		}

		$rtlCodes = apply_filters( 'wpml_rtl_languages_codes', self::DEFAULT_RTL_LANGUAGES );
		if ( ! is_array( $rtlCodes ) ) {
			return false;
		}
		$rtlCodes = array_map( 'strtolower', array_map( 'strval', $rtlCodes ) );

		if ( in_array( $code, $rtlCodes, true ) ) {
			return true;
		}

		$resolved = LanguageCodeResolution::resolve( $code );
		if ( null !== $resolved ) {
			return in_array( strtolower( (string) $resolved['language'] ), $rtlCodes, true );
		}

		$head = LanguageCodeResolution::head( $code );
		if ( $head === $code ) {
			return false;
		}

		$headResolved  = LanguageCodeResolution::resolve( $head );
		$headLanguage  = null !== $headResolved ? (string) $headResolved['language'] : $head;

		return in_array( strtolower( $headLanguage ), $rtlCodes, true );
	}

	private static function storedOverride( $code ) {
		if ( null === self::$storedOverrides ) {
			self::$storedOverrides = self::readStoredOverrides();
		}

		return array_key_exists( $code, self::$storedOverrides ) ? self::$storedOverrides[ $code ] : null;
	}

	private static function readStoredOverrides() {
		global $wpdb;

		if ( ! is_object( $wpdb ) || ! method_exists( $wpdb, 'get_results' ) ) {
			return [];
		}

		$has = $wpdb->get_var(
			$wpdb->prepare( "SHOW COLUMNS FROM `{$wpdb->prefix}icl_languages` LIKE %s", 'is_rtl' )
		);
		if ( ! $has ) {
			return [];
		}

		$map  = [];
		$rows = $wpdb->get_results(
			"SELECT code, is_rtl FROM {$wpdb->prefix}icl_languages WHERE is_rtl IS NOT NULL",
			ARRAY_A
		);
		foreach ( (array) $rows as $row ) {
			if ( ! is_array( $row ) || ! isset( $row['code'] ) ) {
				continue;
			}
			$map[ strtolower( (string) $row['code'] ) ] = (bool) (int) $row['is_rtl'];
		}

		return $map;
	}

	public static function resetStoredOverrides() {
		self::$storedOverrides = null;
	}
}
