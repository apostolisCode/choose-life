<?php

class WPML_Language_Domains {

	private $domains;

	public function __construct(
		SitePress $sitepress,
		WPML_URL_Converter_Url_Helper $converter_url_helper
	) {
		$domains = $sitepress->get_setting( 'language_domains' );

		$this->domains = is_array( $domains ) ? $domains : array();

		$this->domains[ $sitepress->get_default_language() ] = wpml_parse_url( $converter_url_helper->get_abs_home(), PHP_URL_HOST );
	}

	public function get( $lang ) {
		return isset( $this->domains[ $lang ] ) ? $this->domains[ $lang ] : null;
	}

	public static function hostOf( $domain ) {
		$domain = trim( (string) $domain );

		if ( '' === $domain ) {
			return null;
		}

		if ( ! preg_match( '#^[a-z][a-z0-9+.-]*://#i', $domain ) ) {
			$domain = '//' . ltrim( $domain, '/' );
		}

		$parts = wp_parse_url( $domain );

		return is_array( $parts ) && ! empty( $parts['host'] ) ? strtolower( (string) $parts['host'] ) : null;
	}

	public static function hostsOf( $domains ) {
		$hosts = array();

		foreach ( is_array( $domains ) ? $domains : array() as $language => $domain ) {
			$host = self::hostOf( $domain );

			if ( $host ) {
				$hosts[ $language ] = $host;
			}
		}

		return $hosts;
	}

	public static function baseUrlOf( $domain, $default_scheme ) {
		$domain = rtrim( trim( (string) $domain ), '/' );

		if ( '' === $domain ) {
			return '';
		}

		if ( preg_match( '#^[a-z][a-z0-9+.-]*://#i', $domain ) ) {
			return $domain;
		}

		return $default_scheme . '://' . ltrim( $domain, '/' );
	}
}
