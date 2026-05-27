<?php
/**
 * Generic/Miscellaneous Utility
 * functions
 *
 */

/**
 * Class CRL_Utils
 *
 */
class CRL_Utils {

	public static function generate_random_string( $length = 22 ) {
		$characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen( $characters );
		$randomString     = '';
		for ( $i = 0; $i < $length; $i ++ ) {
			$randomString .= $characters[ rand( 0, $charactersLength - 1 ) ];
		}

		return $randomString;
	}

	public static function strong_password_check( $password ) {
		// Minimum length of 8 characters
		if ( strlen( $password ) < 8 ) {
			return false;
		}

		// At least one number
		if ( ! preg_match( "/[0-9]/", $password ) ) {
			return false;
		}

		// At least one special character
		if ( ! preg_match( "/[\W]/", $password ) ) {
			return false;
		}

		// At least one uppercase and one lowercase letter
		if ( ! preg_match( "/[a-z]/", $password ) || ! preg_match( "/[A-Z]/", $password ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Return an escaped email
	 * string to be used with
	 * <a href="mailto:{...}"></a>
	 *
	 * @param $email {string}
	 *
	 * @return string
	 */
	public static function get_esc_email( $email ) {
		$sanitized_email = sanitize_email( $email );

		return is_email( $sanitized_email ) ? esc_attr( $sanitized_email ) : '';
	}

	/**
	 * Print an escaped email
	 * string to be used with
	 * <a href="mailto:{...}"></a>
	 *
	 * @param $email
	 */
	public static function esc_email( $email ) {
		echo self::get_esc_email( $email );
	}

	/**
	 * Return an escaped telephone
	 * string to be used with
	 * <a href="tel:{...}"></a>
	 *
	 * @param $tel {string}
	 *
	 * @return mixed
	 */
	public static function get_esc_tel( $tel ) {
		return esc_attr( preg_replace( '/[^+0-9]/i', '', $tel ) );
	}

	/**
	 * Print an escaped telephone
	 * string to be used with
	 * <a href="tel:{...}"></a>
	 *
	 * @param $tel {string}
	 */
	public static function esc_tel( $tel ) {
		echo self::get_esc_tel( $tel );
	}

	/**
	 * Return a url without protocol
	 * string to be used with
	 * <a href="">{...}</a>
	 *
	 * @param $url {string}
	 *
	 * @return mixed
	 */
	public static function get_esc_protocol( $url ) {
		$url = trim( $url, '/' );

		return esc_attr( preg_replace( '(^https?://)', '', $url ) );
	}

	/**
	 * Return a url without protocol
	 * string to be used with
	 * <a href="">{...}</a>
	 *
	 * @param $url {string}
	 */
	public static function esc_protocol( $url ) {
		echo self::get_esc_protocol( $url );
	}

	/**
	 * Removes accents from
	 * characters in
	 * string
	 *
	 * @param string $str
	 *
	 * @returns string
	 */
	public static function remove_accents( $str ) {
		$accents_to_remove = array(
			'ά',
			'έ',
			'ή',
			'ί',
			'ό',
			'ύ',
			'ώ',
			'Ά',
			'Έ',
			'Ή',
			'Ί',
			'ΐ',
			'Ό',
			'Ύ',
			'Ώ',
			'ς',
			'À',
			'Â',
			'Á',
			'Ã',
			'Ä',
			'Ç',
			'È',
			'É',
			'Ê',
			'Ë',
			'Î',
			'Ò',
			'Ó',
			'Ô',
			'Õ',
			'Ö',
			'Ù',
			'Ú',
			'Û',
			'à',
			'à',
			'á',
			'â',
			'ã',
			'ä',
			'ç',
			'è',
			'é',
			'ê',
			'ë',
			'ì',
			'í',
			'î',
			'ï',
			'ò',
			'ó',
			'ô',
			'õ',
			'ù',
			'ú',
			'û',
			'ü'
		);
		$replace_with      = array(
			'α',
			'ε',
			'η',
			'ι',
			'ο',
			'υ',
			'ω',
			'Α',
			'Ε',
			'Η',
			'Ι',
			'ι',
			'Ο',
			'Υ',
			'Ω',
			'Σ',
			'A',
			'A',
			'A',
			'A',
			'A',
			'C',
			'E',
			'E',
			'E',
			'E',
			'I',
			'O',
			'O',
			'O',
			'O',
			'O',
			'U',
			'U',
			'U',
			'a',
			'a',
			'a',
			'a',
			'a',
			'a',
			'c',
			'e',
			'e',
			'e',
			'e',
			'i',
			'i',
			'i',
			'i',
			'o',
			'o',
			'o',
			'o',
			'u',
			'u',
			'u',
			'u'
		);

		return str_replace( $accents_to_remove, $replace_with, remove_accents( $str ) );
	}

	/**
	 * Capitalize text keeping Greek OR (ή) intact
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public static function capitalize_text( $text ) {
		$text         = mb_convert_case( ( string ) $text, MB_CASE_UPPER, "UTF-8" );
		$replacements = array(
			array( 'Ά', 'Α' ),
			array( 'Έ', 'Ε' ),
			array( 'Ί', 'Ι' ),
			array( 'Ύ', 'Υ' ),
			array( 'Ό', 'Ο' ),
			array( ' Ή ', ' -GR_OR- ' ),
			array( 'Ή', 'Η' ),
			array( ' -GR_OR- ', ' Ή ' ),
			array( 'Ώ', 'Ω' )
		);
		foreach ( $replacements as $char ) {
			$accented   = $char[0];
			$unaccented = $char[1];
			$text       = str_replace( $accented, $unaccented, $text );
		}

		return $text;
	}

	/**
	 * Titlize text keeping Greek OR (ή) intact
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public static function titleize_text( $text ) {
		$text         = mb_convert_case( ( string ) $text, MB_CASE_TITLE, "UTF-8" );
		$replacements = array(
			array( 'Ά', 'Α' ),
			array( 'Έ', 'Ε' ),
			array( 'Ί', 'Ι' ),
			array( 'Ύ', 'Υ' ),
			array( 'Ό', 'Ο' ),
			array( ' Ή ', ' -GR_OR- ' ),
			array( 'Ή', 'Η' ),
			array( ' -GR_OR- ', ' Ή ' ),
			array( 'Ώ', 'Ω' )
		);
		foreach ( $replacements as $char ) {
			$accented   = $char[0];
			$unaccented = $char[1];
			$text       = str_replace( $accented, $unaccented, $text );
		}

		return $text;
	}

	/**
	 * Convert a string to
	 * uppercase and remove
	 * accents
	 *
	 * NOTE: Avoid passing in HTML
	 *       This method will dumbly
	 *       transform HTML tags and
	 *       attributes to uppercase.
	 *       HTML entities eg: "&nbsp;"
	 *       are OK
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	public static function get_upper( $text ) {
		$text         = mb_convert_case( ( string ) $text, MB_CASE_UPPER, "UTF-8" );
		$replacements = array(
			array( 'Ά', 'Α' ),
			array( 'Έ', 'Ε' ),
			array( 'Ί', 'Ι' ),
			array( 'Ϊ', 'Ι' ),
			array( 'Ϊ́', 'Ι' ),
			array( 'Ύ', 'Υ' ),
			array( 'Ό', 'Ο' ),
			array( ' Ή ', ' -GR_OR- ' ),
			array( 'Ή', 'Η' ),
			array( ' -GR_OR- ', ' Ή ' ),
			array( 'Ώ', 'Ω' )
		);
		foreach ( $replacements as $char ) {
			$accented   = $char[0];
			$unaccented = $char[1];
			$text       = str_replace( $accented, $unaccented, $text );
		}

		return $text;
	}

	/**
	 * Convert a string to
	 * uppercase and remove
	 * accents then print it
	 *
	 * NOTE: Avoid passing in HTML
	 *       This method will dumbly
	 *       transform HTML tags and
	 *       attributes to uppercase.
	 *       HTML entities eg: "&nbsp;"
	 *       are OK
	 *
	 * @param string $str
	 */
	public static function upper( $str ) {
		echo self::get_upper( $str );
	}

	/**
	 * Get the copyright years string
	 * depending on the current year
	 * and the original copyright
	 * year
	 *
	 * eg: '2014-2020'
	 *
	 * @param int $original_copyright_year
	 * @param string $separator
	 *
	 * @return string
	 */
	public static function get_copyright_years( $original_copyright_year, $separator = '-' ) {
		$current_year = ( int ) date( 'Y' );

		return $current_year > ( int ) $original_copyright_year ? "{$original_copyright_year}{$separator}{$current_year}" : $original_copyright_year;
	}

	/**
	 * Print the copyright years string
	 * depending on the current year
	 * and the original copyright
	 * year
	 *
	 * eg: '2014-2020'
	 *
	 * @param int $original_copyright_year
	 * @param string $separator
	 *
	 * @return string
	 */
	public static function copyright_years( $original_copyright_year, $separator = '-' ) {
		echo self::get_copyright_years( $original_copyright_year, $separator );
	}

	public static function array_merge_recursive_distinct( array $array1, array $array2 ) {
		$merged = $array1;

		foreach ( $array2 as $key => &$value ) {
			if ( is_array( $value ) && isset( $merged[ $key ] ) && is_array( $merged[ $key ] ) ) {
				$merged[ $key ] = self::array_merge_recursive_distinct( $merged[ $key ], $value );
			} else {
				$merged[ $key ] = $value;
			}
		}

		return $merged;
	}

	/**
	 * Clean up bad HTML content string
	 *
	 * @param string $badHtml
	 *
	 * @return string
	 */
	public static function html_cleanup( $badHtml ) {

		libxml_use_internal_errors( true ); //use this to prevent warning messages from displaying because of the bad HTML

		$doc                     = new DOMDocument( '1.0', 'UTF-8' );
		$doc->preserveWhiteSpace = true;
		$doc->substituteEntities = false;
		$doc->formatOutput       = false;
		$doc->encoding           = 'UTF-8';
		$doc->loadHTML(
			'<?xml version="1.0" encoding="UTF-8" ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">' .
			'<html xmlns="http://www.w3.org/1999/xhtml">' .
			'<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>' .
			'<body>' .
			$badHtml
			. '</body>'
			. '</html>'
		);
		$goodHtml = $doc->saveHTML();

		return html_entity_decode( trim( preg_replace( '/<body>(.*)<\/body>/', '$1', $goodHtml ) ) );
	}

	public static function inline_css( $classes = [], $echo = true ) {
		$output = '';
		foreach ( $classes as $class_name => $options ) {
			$styling = '';
			foreach ( $options as $style => $value ) {
				if ( $value !== null || $value !== '' ) {
					$styling .= $style . ':' . $value . ';';
				}
			}
			if ( $styling !== '' ) {
				$output .= $class_name . '{' . $styling . '}';
			}
		}
		if ( ! $echo ) {
			return $output;
		}
		if ( $output !== '' ) {
			echo '<style>' . $output . '</style>';
		}

		return true;
	}

	public static function get_field_type( $type, $prefix, $name, $sub_field = false ) {
		if ( $type === 'field' ) {
			return $prefix[ $name ];
		}

		if ( $sub_field ) {
			return get_sub_field( $prefix . '_' . $name );
		}

		return get_field( $prefix . '_' . $name );
	}


	public static function truncate_string( $string, $length, $dots = "..." ) {
		if ( strlen( $string ) > $length ) {
			$str       = substr( $string, 0, $length - strlen( $dots ) );
			$str_array = explode( ' ', $str );
			$str_rem   = array_pop( $str_array );
			$new_str   = implode( ' ', $str_array );
			$final_str = $new_str . $dots;
		} else {
			$final_str = $string;
		};

		return $final_str;
	}

	public static function covert_to_latin( $title, $uppercase = true ) {

		$title = mb_strtolower( $title, 'UTF-8' );

		$expressions = array(
			'/[αάΑΆ]/u'                                 => 'a',
			'/[βΒ]/u'                                   => 'v',
			'/[γΓ]/u'                                   => 'g',
			'/[δΔ]/u'                                   => 'd',
			'/[εέΕΈ]/u'                                 => 'e',
			'/[ζΖ]/u'                                   => 'z',
			'/[ηήΗΉ]/u'                                 => 'i',
			'/[θΘ]/u'                                   => 'th',
			'/[ιίϊΙΊΪ]/u'                               => 'i',
			'/[κΚ]/u'                                   => 'k',
			'/[λΛ]/u'                                   => 'l',
			'/[μΜ]/u'                                   => 'm',
			'/[νΝ]/u'                                   => 'n',
			'/[ξΞ]/u'                                   => 'x',
			'/[οόΟΌ]/u'                                 => 'o',
			'/[πΠ]/u'                                   => 'p',
			'/[ρΡ]/u'                                   => 'r',
			'/[σςΣ]/u'                                  => 's',
			'/[τΤ]/u'                                   => 't',
			'/[υύϋΥΎΫ]/u'                               => 'y',
			'/[φΦ]/iu'                                  => 'f',
			'/[χΧ]/u'                                   => 'ch',
			'/[ψΨ]/u'                                   => 'ps',
			'/[ωώ]/iu'                                  => 'o',
			'/[αΑ][ιίΙΊ]/u'                             => 'e',
			'/[οΟΕε][ιίΙΊ]/u'                           => 'i',
			'/[αΑ][υύΥΎ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'af$1',
			'/[αΑ][υύΥΎ]/u'                             => 'av',
			'/[εΕ][υύΥΎ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'ef$1',
			'/[εΕ][υύΥΎ]/u'                             => 'ev',
			'/[οΟ][υύΥΎ]/u'                             => 'ou',
			'/(^|\s)[μΜ][πΠ]/u'                         => '$1b',
			'/[μΜ][πΠ](\s|$)/u'                         => 'b$1',
			'/[μΜ][πΠ]/u'                               => 'b',
			'/[νΝ][τΤ]/u'                               => 'nt',
			'/[τΤ][σΣ]/u'                               => 'ts',
			'/[τΤ][ζΖ]/u'                               => 'tz',
			'/[γΓ][γΓ]/u'                               => 'ng',
			'/[γΓ][κΚ]/u'                               => 'gk',
			'/[ηΗ][υΥ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u'   => 'if$1',
			'/[ηΗ][υΥ]/u'                               => 'iu',

		);
		$title       = preg_replace( array_keys( $expressions ), array_values( $expressions ), $title );
		if ( $uppercase ) {
			return strtoupper( $title );
		}

		return $title;
	}

	public static function clean_string( $string ) {
		$string = CRL_Utils::get_upper( $string );
		$string = preg_replace( '/[^α-ωΑ-ΩA-Za-z0-9\ -]/', '', $string ); // Removes special chars.
		$string = str_replace( '-', '', $string );
		$string = str_replace( '  ', ' ', $string );

		return $string; // Replaces multiple hyphens with single one.
	}

	public static function get_string_between( $string, $start, $end ) {
		$string = ' ' . $string;
		$ini    = strpos( $string, $start );
		if ( $ini == 0 ) {
			return '';
		}
		$ini += strlen( $start );
		$len = strpos( $string, $end, $ini ) - $ini;

		return substr( $string, $ini, $len );
	}

}
