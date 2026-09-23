<?php

use WPML\Blocks\AttributeUrls;
use WPML\Utils\AutoAdjustIds;

class WPML_Absolute_To_Permalinks {

	private $taxonomies_query;
	private $lang;

	private $attribute_replacements = [];

	private $sitepress;

	private $auto_adjust_ids;

	public function __construct( SitePress $sitepress, ?AutoAdjustIds $auto_adjust_ids = null ) {
		$this->sitepress       = $sitepress;
		$this->auto_adjust_ids = $auto_adjust_ids ?: new AutoAdjustIds( $sitepress );
	}

	public function convert_text( $text ) {

		$this->lang = $this->sitepress->get_current_language();

		$active_langs_reg_ex = implode(
			'|',
			array_map(
				function ( $code ) {
					return preg_quote( $code, '@' );
				},
				$this->get_url_language_codes()
			)
		);

		if ( ! $this->taxonomies_query ) {
			$this->taxonomies_query = new WPML_WP_Taxonomy_Query( $this->sitepress->get_wp_api() );
		}

		$home    = rtrim( $this->sitepress->get_wp_api()->get_option( 'home' ), '/' );
		$parts   = parse_url( $home );
		$port    = isset( $parts['port'] ) ? ':' . $parts['port'] : '';
		$abshome = $parts['scheme'] . '://' . $parts['host'] . $port;
		$path    = isset( $parts['path'] ) ? ltrim( $parts['path'], '/' ) : '';
		$tx_qvs  = join(
			'|',
			array_map(
				function ( $query_var ) {
					return preg_quote( $query_var, '@' );
				},
				$this->taxonomies_query->get_query_vars()
			)
		);
        $reg_ex  = '@<a([^>]+)?href="((' . preg_quote( $abshome, '@' ) . ')?/' . preg_quote( $path, '@' ) . '/?(' . $active_langs_reg_ex . ')?/?\?(p|page_id|cat_ID|' . $tx_qvs . ')=([^"&#]+))(#?[^"]*)"([^>]+)?>@iu';

		$this->attribute_replacements = [];

		$text = preg_replace_callback( $reg_ex, [ $this, 'show_permalinks_cb' ], $text );

		$text = AttributeUrls::replaceInDelimiters( $text, $this->attribute_replacements );

		$this->attribute_replacements = [];

		return $text;
	}

	private function get_url_language_codes() {
		$codes = array_keys( $this->sitepress->get_active_languages() );
		if ( [] === $codes ) {
			return $codes;
		}

		$map   = apply_filters( 'wpml_language_codes_map', array_combine( $codes, $codes ) );
		$codes = is_array( $map )
			? array_values( array_unique( array_merge( $codes, array_map( 'strval', $map ) ) ) )
			: $codes;

		usort(
			$codes,
			function ( $a, $b ) {
				return strlen( $b ) - strlen( $a );
			}
		);

		return $codes;
	}

	function show_permalinks_cb( $matches ) {

		$parts = $this->get_found_parts( $matches );

		$resolve_url = function () use ( $parts ) {
			return $this->get_url( $parts );
		};

		$url = $this->should_keep_original( $parts )
			? $this->auto_adjust_ids->runWithout( $resolve_url )
			: $this->auto_adjust_ids->runWith( $resolve_url );

		if ( $this->sitepress->get_wp_api()->is_wp_error( $url ) || empty( $url ) ) {
			return $parts->whole;
		}

		$fragment = $this->get_fragment( $url, $parts );

		if ( 'widget_text' == $this->sitepress->get_wp_api()->current_filter() ) {
			$url = $this->sitepress->convert_url( $url );
		}

		$this->remember_block_attribute_replacement( $parts, $url . $fragment );

		return '<a' . $parts->pre_href . 'href="' . $url . $fragment . '"' . $parts->trail . '>';
	}

	private function remember_block_attribute_replacement( $parts, $resolved ) {
		$sticky = $parts->url . $parts->fragment;

		$this->attribute_replacements[ $sticky ] = $resolved;

		$decoded = str_replace( [ '&#038;', '&amp;' ], '&', $sticky );

		if ( $decoded !== $sticky ) {
			$this->attribute_replacements[ $decoded ] = $resolved;
		}
	}

	private function get_found_parts( $matches ) {
		return (object) array(
			'whole'        => $matches[0],
			'pre_href'     => $matches[1],
			'url'          => $matches[2],
			'content_type' => $matches[5],
			'id'           => $matches[6],
			'fragment'     => $matches[7],
			'trail'        => isset( $matches[8] ) ? $matches[8] : '',
		);
	}

	private function get_url( $parts ) {
		$tax = $this->taxonomies_query->find( $parts->content_type );

		if ( $parts->content_type == 'cat_ID' ) {
			$url = $this->sitepress->get_wp_api()->get_category_link( $parts->id );
		} elseif ( $tax ) {
			$url = $this->sitepress->get_wp_api()->get_term_link( $parts->id, $tax );
		} else {
			$url = $this->sitepress->get_wp_api()->get_permalink( $parts->id );
		}

		return $url;
	}

	private function should_keep_original( $parts ) {
		if ( 'cat_ID' === $parts->content_type || $this->taxonomies_query->find( $parts->content_type ) ) {
			return false;
		}

		$original_id = (int) $parts->id;
		$post_type   = get_post_type( $original_id );
		if ( ! $post_type ) {
			return false;
		}

		$translated_id = $this->sitepress->get_object_id( $original_id, $post_type, false, $this->lang );
		$translated    = $translated_id ? get_post( $translated_id ) : null;
		if ( ! $translated || $translated->ID === $original_id ) {
			return false;
		}

		$not_convert_post_statuses = apply_filters(
			'wpml_link_target_not_convert_post_statuses',
			array( 'draft' ),
			$translated
		);

		return in_array( $translated->post_status, $not_convert_post_statuses, true );
	}

	private function get_fragment( $url, $parts ) {
		$fragment = $parts->fragment;
		$fragment = $this->remove_query_in_wrong_lang( $fragment );
		if ( is_string( $fragment ) && $fragment != '' ) {
			$fragment = str_replace( '&#038;', '&', $fragment );
			$fragment = str_replace( '&amp;', '&', $fragment );
			if ( $fragment[0] == '&' ) {
				if ( strpos( $fragment, '?' ) === false && strpos( $url, '?' ) === false ) {
					$fragment[0] = '?';
				}
			}

			if ( strpos( $url, '?' ) ) {
				$fragment = $this->check_for_duplicate_lang_query( $fragment, $url );
			}
		}

		return $fragment;
	}

	private function remove_query_in_wrong_lang( $fragment ) {
		if ( is_string( $fragment ) && $fragment != '' ) {
			$fragment = str_replace( '&#038;', '&', $fragment );
			$fragment = str_replace( '&amp;', '&', $fragment );
			$start    = $fragment[0];
			parse_str( substr( $fragment, 1 ), $fragment_query );
			if ( isset( $fragment_query['lang'] ) ) {
				if ( $fragment_query['lang'] != $this->lang ) {
					unset( $fragment_query['lang'] );

					$fragment = build_query( $fragment_query );
					if ( strlen( $fragment ) ) {
						$fragment = $start . $fragment;
					}
				}
			}
		}
		return $fragment;
	}

	private function check_for_duplicate_lang_query( $fragment, $url ) {
		$url_parts = explode( '?', $url );
		parse_str( $url_parts[1], $url_query );

		if ( isset( $url_query['lang'] ) ) {
			parse_str( substr( $fragment, 1 ), $fragment_query );
			if ( isset( $fragment_query['lang'] ) ) {
				unset( $fragment_query['lang'] );
				$fragment = build_query( $fragment_query );
				if ( strlen( $fragment ) ) {
					$fragment = '&' . $fragment;
				}
			}
		}
		return $fragment;
	}
}
