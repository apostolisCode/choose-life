<?php

namespace WPML\TM\Editor;

class RichText {

	public static function allowedHtml() {
		$allowed = wp_kses_allowed_html( 'post' );

		return (array) apply_filters( 'wpml_tm_editor_allowed_html', $allowed );
	}

	public static function sanitize( $html ) {
		return wp_kses( (string) $html, self::allowedHtml() );
	}

	public static function isRenderable( $html ) {
		$html = (string) $html;

		if ( false === strpos( $html, '<' ) ) {
			return true;
		}

		return self::sanitize( $html ) === wp_kses( $html, self::everythingUsedIn( $html ) );
	}

	private static function everythingUsedIn( $html ) {
		$used = array();

		if ( ! preg_match_all( '%<([a-zA-Z][a-zA-Z0-9:_-]*)((?:\s[^>]*)?)>%', $html, $matches, PREG_SET_ORDER ) ) {
			return $used;
		}

		foreach ( $matches as $match ) {
			$element = strtolower( $match[1] );
			if ( ! isset( $used[ $element ] ) ) {
				$used[ $element ] = array();
			}
			foreach ( wp_kses_hair( $match[2], wp_allowed_protocols() ) as $attribute ) {
				$used[ $element ][ strtolower( $attribute['name'] ) ] = true;
			}
		}

		return $used;
	}
}
