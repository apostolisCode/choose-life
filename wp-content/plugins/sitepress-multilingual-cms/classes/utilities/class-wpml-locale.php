<?php

use WPML\Collect\Support\Collection;

class WPML_Locale {
	private $wpdb;
	private $sitepress;
	private $locale;
	private $locale_cache;

	private $all_locales;

	public function __construct( wpdb &$wpdb, SitePress &$sitepress, &$locale ) {
		$this->wpdb         =& $wpdb;
		$this->sitepress    =& $sitepress;
		$this->locale       =& $locale;
		$this->locale_cache = null;
	}

	public function init() {
		if ( $this->language_needs_title_sanitization() ) {
			add_filter( 'sanitize_title', array( $this, 'filter_sanitize_title' ), 10, 2 );
		}
	}

	public function reset_cached_data() {
		$this->locale_cache = null;
		$this->all_locales  = null;
	}

	public function filter_sanitize_title( $title, $raw_title ) {
		if ( $title !== $raw_title ) {
			remove_filter( 'sanitize_title', array( $this, 'filter_sanitize_title' ), 10 );
			$chars                            = array();
			$chars[ chr( 195 ) . chr( 132 ) ] = 'Ae';
			$chars[ chr( 195 ) . chr( 133 ) ] = 'Aa';
			$chars[ chr( 195 ) . chr( 134 ) ] = 'Ae';
			$chars[ chr( 195 ) . chr( 150 ) ] = 'Oe';
			$chars[ chr( 195 ) . chr( 152 ) ] = 'Oe';
			$chars[ chr( 195 ) . chr( 156 ) ] = 'Ue';
			$chars[ chr( 195 ) . chr( 159 ) ] = 'ss';
			$chars[ chr( 195 ) . chr( 164 ) ] = 'ae';
			$chars[ chr( 195 ) . chr( 165 ) ] = 'aa';
			$chars[ chr( 195 ) . chr( 166 ) ] = 'ae';
			$chars[ chr( 195 ) . chr( 182 ) ] = 'oe';
			$chars[ chr( 195 ) . chr( 184 ) ] = 'oe';
			$chars[ chr( 195 ) . chr( 188 ) ] = 'ue';
			$title                            = sanitize_title( strtr( $raw_title, $chars ) );
			add_filter( 'sanitize_title', array( $this, 'filter_sanitize_title' ), 10, 2 );
		}

		return $title;
	}

	public function locale() {
		if ( null === $this->locale_cache ) {
			add_filter( 'language_attributes', array( $this, '_language_attributes' ) );

			$wp_api  = $this->sitepress->get_wp_api();
			$is_ajax = $wp_api->is_ajax();
			$requested_lang_code = $is_ajax && isset( $_REQUEST['action'], $_REQUEST['lang'] )
				? \WPML\Language\RequestedLanguage::forPrivileged( sanitize_text_field( wp_unslash( $_REQUEST['lang'] ) ), true )
				: null;
			if ( null !== $requested_lang_code ) {
				$locale_lang_code = $requested_lang_code;
			} elseif ( ( $wp_api->is_admin()
						 && ( ! $is_ajax || $this->sitepress->check_if_admin_action_from_referer() ) )
					   || $this->sitepress->is_admin_originated_rest_request()
			) {
				$locale_lang_code = $this->sitepress->get_default_language();
				if ( ! $locale_lang_code ) {
					$locale_lang_code = $this->sitepress->get_current_language();
				}
			} else {
				$locale_lang_code = $this->sitepress->get_current_language();
			}
			$locale = $this->get_locale( $locale_lang_code );

			if ( did_action( 'plugins_loaded' ) ) {
				$this->locale_cache = $locale;
			}

			return $locale;
		}

		return $this->locale_cache;
	}

	public function get_locale( $code ) {
		if ( ! $code ) {
			return false;
		}

		return $this->get_all_locales()->get( $code, false );
	}

	public function get_all_locales() {
		if ( ! $this->all_locales ) {
			$wpdb = $this->wpdb;
			$this->all_locales = wpml_collect(
				$wpdb->get_results(
					"SELECT
						l.code,
						m.locale,
						l.default_locale
					FROM {$wpdb->prefix}icl_languages AS l
					LEFT JOIN {$wpdb->prefix}icl_locale_map AS m ON m.code = l.code"
				)
			)
				->mapWithKeys(
					function( $row ) {
						if ( $row->locale ) {
							$locale = $row->locale;
						} elseif ( $row->default_locale ) {
							$locale = $row->default_locale;
						} else {
							$locale = false;
						}

						return [ $row->code => $locale ];
					}
				);
		}

		return $this->all_locales;
	}

	public function switch_locale( $lang_code = false ) {
		global $l10n;
		static $original_l10n;
		if ( ! empty( $lang_code ) ) {
			$original_l10n = isset( $l10n['sitepress'] ) ? $l10n['sitepress'] : null;
			if ( $original_l10n !== null ) {
				unset( $l10n['sitepress'] );
			}
			$locale = $this->get_locale( $lang_code );
			if ( false !== $locale ) {
				load_textdomain(
					'sitepress',
					WPML_PLUGIN_PATH . '/locale/sitepress-' . $locale . '.mo',
					$locale
				);
			}
		} else {
			$l10n['sitepress'] = $original_l10n;
		}
	}

	public function get_locale_file_names() {
		$wpdb = $this->wpdb;

		$locales = array();
		$res     = $wpdb->get_results(
			"
			SELECT l.code, lm.locale, l.default_locale
			FROM {$wpdb->prefix}icl_languages l
			LEFT JOIN {$wpdb->prefix}icl_locale_map lm ON lm.code = l.code
			WHERE l.active = 1"
		);
		foreach ( $res as $row ) {
			$locale = $row->locale ? $row->locale : $row->default_locale;
			if ( $locale ) {
				$locales[ $row->code ] = $locale;
			}
		}

		return $locales;
	}

	private function language_needs_title_sanitization() {
		$lang_needs_filter = array( 'de_DE', 'da_DK' );
		$current_lang      = $this->sitepress->get_language_details( $this->sitepress->get_current_language() );
		$needs_filter      = false;

		if ( ! isset( $current_lang['default_locale'] ) ) {
			return $needs_filter;
		}

		if ( in_array( $current_lang['default_locale'], $lang_needs_filter, true ) ) {
			$needs_filter = true;
		}

		return $needs_filter;
	}

	function _language_attributes( $latr ) {

		return preg_replace(
			'#lang="[a-z0-9_-]*"#i',
			'lang="' . self::escape_replacement( str_replace( '_', '-', $this->get_render_locale() ) ) . '"',
			$latr
		);
	}

	private function get_render_locale() {
		if ( $this->sitepress->is_wpml_switch_language_triggered() ) {
			$switched = $this->get_locale( $this->sitepress->get_current_language() );
			if ( $switched ) {
				return (string) $switched;
			}
		}

		if ( is_locale_switched() ) {
			return (string) determine_locale();
		}

		if ( $this->sitepress->get_wp_api()->is_admin() ) {
			return (string) get_user_locale();
		}

		return (string) $this->locale;
	}

	private static function escape_replacement( $replacement ) {
		return str_replace( array( '\\', '$' ), array( '\\\\', '\\$' ), (string) $replacement );
	}

	public static function get_instance_from_sitepress() {
		global $sitepress;

		return $sitepress->get_wpml_locale();
	}
}
