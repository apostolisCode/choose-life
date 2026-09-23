<?php

class WPML_Resolve_Single_Url {

	private $sitepress;

	private $url_converter;

	private $translate_link_targets;

	private $persistent_cache;

	private $inline_resolver;

	private $cache = [];

	public function __construct(
		SitePress $sitepress,
		WPML_URL_Converter $url_converter,
		WPML_Translate_Link_Targets $translate_link_targets,
		?WPML_Single_Url_Resolution_Cache $persistent_cache = null
	) {
		$this->sitepress              = $sitepress;
		$this->url_converter          = $url_converter;
		$this->translate_link_targets = $translate_link_targets;
		$this->persistent_cache       = $persistent_cache;
	}

	public function set_inline_resolver( WPML_Single_Url_Inline_Resolver $inline_resolver ) {
		$this->inline_resolver = $inline_resolver;
	}

	public function resolve( $url, $target_language, $source_language_hint = null ) {
		$source_language  = $this->resolve_source_language( $url, $source_language_hint );
		$result           = $this->default_result( $url, $source_language );
		$active_languages = array_keys( $this->sitepress->get_active_languages() );

		if (
			! is_string( $url )
			|| '' === $url
			|| ! is_string( $target_language )
			|| '' === $target_language
			|| ! in_array( $target_language, $active_languages, true )
			|| ! $source_language
		) {
			return $result;
		}

		$cache_key = md5( get_current_blog_id() . "\0" . $source_language . "\0" . $target_language . "\0" . $url );
		if ( array_key_exists( $cache_key, $this->cache ) ) {
			return $this->cache[ $cache_key ];
		}

		if ( ! $this->is_internal_url( $url ) ) {
			$this->cache[ $cache_key ] = $result;
			return $result;
		}

		if ( $this->is_content_directory_url( $url ) ) {
			$this->cache[ $cache_key ] = $result;
			return $result;
		}

		$should_resolve = apply_filters(
			'wpml_should_resolve_single_url',
			true,
			$url,
			$target_language,
			$source_language
		);

		if ( false === $should_resolve ) {
			$this->cache[ $cache_key ] = $result;
			return $result;
		}

		$should_cache = apply_filters(
			'wpml_should_cache_single_url_resolution',
			true,
			$url,
			$target_language,
			$source_language
		);

		if ( strlen( $url ) > 4096 ) {
			$this->cache[ $cache_key ] = $result;
			return $result;
		}

		if (
			$this->persistent_cache
			&& false !== $should_cache
		) {
			$result = $this->persistent_cache->get_or_defer(
				$url,
				$source_language,
				$target_language
			);

			if (
				$this->inline_resolver
				&& isset( $result['resolution_state'] )
				&& WPML_Single_Url_Cache_Entry::PUBLIC_DEFERRED === $result['resolution_state']
			) {
				$inline_result = $this->inline_resolver->try_resolve(
					$url,
					$source_language,
					$target_language
				);
				if ( $inline_result ) {
					$result = $inline_result;
				}
			}

			$result                    = $this->restore_private_permalink_for_current_user(
				$result,
				$target_language
			);
			$this->cache[ $cache_key ] = $result;
			return $result;
		}

		$result                    = $this->resolve_uncached( $url, $target_language, $source_language );
		$this->cache[ $cache_key ] = $this->public_result( $result, $url );

		return $this->cache[ $cache_key ];
	}

	public function resolve_uncached_for_cache( $url, $target_language, $source_language ) {
		return $this->resolve_uncached( $url, $target_language, $source_language );
	}

	private function resolve_uncached( $url, $target_language, $source_language ) {
		$result = $this->default_result( $url, $source_language );

		$front_page_id = $this->get_front_page_id( $url, $source_language );
		if ( $front_page_id ) {
			$result['source_post_id']      = $front_page_id;
			$result['translated_post_id']  = $this->translate_post_id( $front_page_id, $target_language );
			$result['url']                 = $this->url_converter->convert_url( $url, $target_language );
			$result['_cache_state']        = $result['translated_post_id']
				? WPML_Single_Url_Cache_Entry::STATE_POSITIVE
				: WPML_Single_Url_Cache_Entry::STATE_MISSING_TRANSLATION;
			$result['_source_object_kind'] = 'post';
			$result['_source_object_id']   = $front_page_id;
			$result['_translation_trid']   = $this->get_post_trid( $front_page_id );
			return $result;
		}

		$collector     = new WPML_Single_Url_Collector();
		$converted_url = $this->convert_scoped( $url, $source_language, $target_language, $collector );
		$source_id     = $collector->get_post_id();
		$resolved_term = $collector->get_term_id();

		if ( ! $source_id && ! $resolved_term ) {
			$source_id = $this->get_query_post_id( $url );
		}
		if ( ! $source_id && ! $resolved_term ) {
			$source_id = $this->resolve_native_post_id( $url, $source_language );
		}

		if ( $source_id ) {
			$translated_id = $this->translate_post_id( $source_id, $target_language );

			$result['source_post_id']     = $source_id;
			$result['translated_post_id'] = $translated_id;

			if ( $translated_id ) {
				$translated_permalink = $this->get_permalink_in_language( $translated_id, $target_language );
				if ( is_string( $translated_permalink ) && '' !== $translated_permalink ) {
					$result['url'] = $translated_permalink;
				}
			}

			$result['_cache_state']        = $translated_id
				? WPML_Single_Url_Cache_Entry::STATE_POSITIVE
				: WPML_Single_Url_Cache_Entry::STATE_MISSING_TRANSLATION;
			$result['_source_object_kind'] = 'post';
			$result['_source_object_id']   = $source_id;
			$result['_translation_trid']   = $this->get_post_trid( $source_id );
		} elseif ( $resolved_term ) {
			$term          = get_term( $resolved_term );
			$translated_id = $term instanceof WP_Term
				? $this->translate_term_id( $resolved_term, $term->taxonomy, $target_language )
				: null;

			$term_url = $this->resolved_term_url(
				$converted_url,
				$url,
				$term,
				$translated_id,
				$target_language
			);
			if ( is_string( $term_url ) && '' !== $term_url ) {
				$result['url'] = $term_url;
			}
			$result['_cache_state']          = $translated_id
				? WPML_Single_Url_Cache_Entry::STATE_POSITIVE
				: WPML_Single_Url_Cache_Entry::STATE_MISSING_TRANSLATION;
			$result['_source_object_kind']   = 'term';
			$result['_source_object_id']     = $resolved_term;
			$result['_translated_object_id'] = $translated_id;
			$result['_translation_trid']     = $term instanceof WP_Term
				? $this->get_term_trid( $term )
				: null;
		} elseif ( is_string( $converted_url ) && '' !== $converted_url ) {
			$result['url']          = $converted_url;
			$result['_cache_state'] = $converted_url !== $url
				? WPML_Single_Url_Cache_Entry::STATE_ROUTE
				: WPML_Single_Url_Cache_Entry::STATE_NEGATIVE;
		} else {
			$result['_cache_state'] = WPML_Single_Url_Cache_Entry::STATE_NEGATIVE;
		}

		return $result;
	}

	private function default_result( $url, $source_language ) {
		return [
			'url'                => $url,
			'source_post_id'     => null,
			'translated_post_id' => null,
			'source_language'    => $source_language,
			'resolution_state'   => WPML_Single_Url_Cache_Entry::PUBLIC_UNCHANGED,
		];
	}

	private function restore_private_permalink_for_current_user( array $result, $target_language ) {
		if (
			! isset( $result['resolution_state'], $result['url'] )
			|| WPML_Single_Url_Cache_Entry::PUBLIC_RESOLVED !== $result['resolution_state']
			|| empty( $result['translated_post_id'] )
			|| ! $this->is_plain_post_permalink( $result['url'], (int) $result['translated_post_id'] )
		) {
			return $result;
		}

		$translated_post_id     = (int) $result['translated_post_id'];
		$translated_post_status = get_post_status( $translated_post_id );
		if ( ! is_string( $translated_post_status ) || '' === $translated_post_status ) {
			return $result;
		}

		$post_status = get_post_status_object( $translated_post_status );
		if (
			! $post_status
			|| empty( $post_status->private )
			|| ! current_user_can( 'read_post', $translated_post_id )
		) {
			return $result;
		}

		$permalink = $this->get_permalink_in_language( $translated_post_id, $target_language );
		if ( is_string( $permalink ) && '' !== $permalink ) {
			$result['url'] = $permalink;
		}

		return $result;
	}

	private function is_plain_post_permalink( $url, $post_id ) {
		if ( ! is_string( $url ) || '' === $url || $post_id < 1 ) {
			return false;
		}

		$query = wp_parse_url( $url, PHP_URL_QUERY );
		if ( ! is_string( $query ) || '' === $query ) {
			return false;
		}

		parse_str( $query, $query_vars );

		return (
			! empty( $query_vars['p'] )
			&& (int) $query_vars['p'] === $post_id
		) || (
			! empty( $query_vars['page_id'] )
			&& (int) $query_vars['page_id'] === $post_id
		);
	}

	private function public_result( array $result, $source_url ) {
		$state = isset( $result['_cache_state'] )
			? $result['_cache_state']
			: WPML_Single_Url_Cache_Entry::STATE_NEGATIVE;

		$result['resolution_state'] = (
			in_array(
				$state,
				[ WPML_Single_Url_Cache_Entry::STATE_POSITIVE, WPML_Single_Url_Cache_Entry::STATE_ROUTE ],
				true
			)
			|| ( isset( $result['url'] ) && $result['url'] !== $source_url )
		)
			? WPML_Single_Url_Cache_Entry::PUBLIC_RESOLVED
			: WPML_Single_Url_Cache_Entry::PUBLIC_UNCHANGED;

		foreach ( array_keys( $result ) as $key ) {
			if ( 0 === strpos( $key, '_' ) ) {
				unset( $result[ $key ] );
			}
		}

		return $result;
	}

	private function get_post_trid( $post_id ) {
		$post_type = get_post_type( $post_id );
		if ( ! $post_type ) {
			return null;
		}

		$trid = $this->sitepress->get_element_trid( $post_id, 'post_' . $post_type );
		return $trid ? (int) $trid : null;
	}

	private function resolve_source_language( $url, $source_language_hint ) {
		$active_languages = array_keys( $this->sitepress->get_active_languages() );
		$default_language = $this->sitepress->get_default_language();

		if ( is_string( $url ) && '' !== $url ) {
			$detected_language = $this->url_converter->get_language_from_url( $url );

			if (
				in_array( $detected_language, $active_languages, true )
				&& $detected_language !== $default_language
			) {
				return $detected_language;
			}
		}

		if ( in_array( $source_language_hint, $active_languages, true ) ) {
			return $source_language_hint;
		}

		return in_array( $default_language, $active_languages, true ) ? $default_language : null;
	}

	private function is_internal_url( $url ) {
		if ( 0 === strpos( $url, '#' ) ) {
			return false;
		}

		$parts = wp_parse_url( $url );
		if ( false === $parts ) {
			return false;
		}

		$scheme = is_array( $parts ) && isset( $parts['scheme'] ) ? strtolower( $parts['scheme'] ) : '';

		if ( '' !== $scheme && ! in_array( $scheme, [ 'http', 'https' ], true ) ) {
			return false;
		}

		if ( '' === $scheme && 0 !== strpos( $url, '//' ) ) {
			return true;
		}

		$absolute_url = 0 === strpos( $url, '//' ) ? 'http:' . $url : $url;

		if ( '' !== $scheme ) {
			$absolute_url = preg_replace( '#^[a-z][a-z0-9+.-]*:#i', $scheme . ':', $absolute_url );
		}

		if ( WPML_Same_Site_Url_Normalizer::is_same_site( $absolute_url ) ) {
			return true;
		}

		$settings = $this->sitepress->get_settings();
		$domains  = isset( $settings['language_domains'] ) && is_array( $settings['language_domains'] )
			? $settings['language_domains']
			: [];
		$url_host = WPML_Same_Site_Url_Normalizer::get_normalized_domain( $absolute_url );

		foreach ( $domains as $domain ) {
			if ( ! is_string( $domain ) || '' === $domain ) {
				continue;
			}
			if ( WPML_Same_Site_Url_Normalizer::get_normalized_domain( $domain ) === $url_host ) {
				return true;
			}
		}

		return false;
	}

	private function is_content_directory_url( $url ) {
		$is_root_relative = 0 === strpos( $url, '/' );
		$is_http_absolute = 1 === preg_match( '#^https?://#i', $url );

		if ( ! $is_root_relative && ! $is_http_absolute ) {
			return false;
		}

		$content_url = content_url();
		if ( ! is_string( $content_url ) || ! $this->is_internal_url( $content_url ) ) {
			return false;
		}

		$content_path = wp_parse_url( $content_url, PHP_URL_PATH );
		$url_path     = wp_parse_url( $url, PHP_URL_PATH );

		if (
			! is_string( $content_path )
			|| '' === $content_path
			|| ! is_string( $url_path )
			|| '' === $url_path
			|| $this->has_ambiguous_path_syntax( $content_path )
			|| $this->has_ambiguous_path_syntax( $url_path )
		) {
			return false;
		}

		$content_path = trim( $content_path, '/' );
		if ( '' === $content_path ) {
			return false;
		}

		$content_path = '/' . $content_path;
		$url_path     = '/' . ltrim( $url_path, '/' );

		return $content_path === $url_path
			|| 0 === strpos( $url_path, $content_path . '/' );
	}

	private function has_ambiguous_path_syntax( $path ) {
		if ( false !== strpos( $path, '%' ) || false !== strpos( $path, '\\' ) ) {
			return true;
		}

		$segments = explode( '/', $path );

		return in_array( '.', $segments, true )
			|| in_array( '..', $segments, true );
	}

	private function convert_scoped( $url, $source_language, $target_language, WPML_Single_Url_Collector $collector ) {
		$previous_language = $this->sitepress->get_current_language();
		if ( $previous_language !== $target_language ) {
			$this->sitepress->switch_lang( $target_language );
		}

		try {
			$converted_url = $this->translate_link_targets->convert_url_for_language(
				$url,
				$source_language,
				$collector
			);
		} finally {
			if ( $previous_language !== $target_language ) {
				$this->sitepress->switch_lang();
			}
		}

		return $converted_url;
	}

	private function get_front_page_id( $url, $source_language ) {
		$previous_language = $this->sitepress->get_current_language();
		if ( $previous_language !== $source_language ) {
			$this->sitepress->switch_lang( $source_language );
		}

		try {
			$page_on_front  = (int) get_option( 'page_on_front' );
			$front_page_url = $page_on_front > 0 ? get_permalink( $page_on_front ) : false;
		} finally {
			if ( $previous_language !== $source_language ) {
				$this->sitepress->switch_lang();
			}
		}

		if ( $page_on_front < 1 ) {
			return 0;
		}

		if ( ! is_string( $front_page_url ) ) {
			return 0;
		}

		$front_page_url = WPML_Same_Site_Url_Normalizer::normalize_url( $front_page_url );
		$url            = WPML_Same_Site_Url_Normalizer::normalize_url( $url );

		return trailingslashit( $front_page_url ) === trailingslashit( $url ) ? $page_on_front : 0;
	}

	private function get_query_post_id( $url ) {
		$query = wp_parse_url( $url, PHP_URL_QUERY );
		if ( ! is_string( $query ) || '' === $query ) {
			return 0;
		}

		parse_str( $query, $query_vars );
		foreach ( [ 'p', 'page_id' ] as $query_var ) {
			if ( ! empty( $query_vars[ $query_var ] ) ) {
				$post_id = absint( $query_vars[ $query_var ] );
				if ( $post_id && get_post( $post_id ) ) {
					return $post_id;
				}
			}
		}

		return 0;
	}

	private function resolve_native_post_id( $url, $source_language ) {
		$previous_language = $this->sitepress->get_current_language();
		$filter_priority   = has_filter( 'url_to_postid', [ $this->sitepress, 'url_to_postid' ] );
		$post_id           = 0;

		if ( $previous_language !== $source_language ) {
			$this->sitepress->switch_lang( $source_language );
		}
		if ( false !== $filter_priority ) {
			remove_filter( 'url_to_postid', [ $this->sitepress, 'url_to_postid' ], $filter_priority );
		}

		try {
			$post_id = function_exists( 'wpcom_vip_url_to_postid' )
				? wpcom_vip_url_to_postid( $url )
				: url_to_postid( $url );
		} finally {
			if ( false !== $filter_priority ) {
				add_filter( 'url_to_postid', [ $this->sitepress, 'url_to_postid' ], $filter_priority );
			}
			if ( $previous_language !== $source_language ) {
				$this->sitepress->switch_lang();
			}
		}

		return $post_id > 0 ? (int) $post_id : 0;
	}

	private function translate_post_id( $source_id, $target_language ) {
		$post_type = get_post_type( $source_id );
		if ( ! $post_type ) {
			return null;
		}

		$translated_id = apply_filters(
			'wpml_object_id',
			$source_id,
			$post_type,
			false,
			$target_language
		);

		return $translated_id ? (int) $translated_id : null;
	}

	private function translate_term_id( $source_id, $taxonomy, $target_language ) {
		$translated_id = apply_filters(
			'wpml_object_id',
			$source_id,
			$taxonomy,
			false,
			$target_language
		);

		return $translated_id ? (int) $translated_id : null;
	}

	private function resolved_term_url( $converted_url, $url, $term, $translated_id, $target_language ) {
		if (
			is_string( $converted_url )
			&& '' !== $converted_url
			&& ! $this->is_unconverted_sticky_link( $converted_url )
		) {
			return $converted_url;
		}

		if ( ! $translated_id || ! $term instanceof WP_Term ) {
			return null;
		}

		$term_link = $this->get_term_link_in_language( (int) $translated_id, $term->taxonomy, $target_language );
		if ( ! is_string( $term_link ) || '' === $term_link ) {
			return null;
		}

		$query    = wp_parse_url( $url, PHP_URL_QUERY );
		$fragment = wp_parse_url( $url, PHP_URL_FRAGMENT );

		if ( is_string( $query ) && '' !== $query ) {
			$term_link .= ( false === strpos( $term_link, '?' ) ? '?' : '&' ) . $query;
		}
		if ( is_string( $fragment ) && '' !== $fragment ) {
			$term_link .= '#' . $fragment;
		}

		return $term_link;
	}

	private function is_unconverted_sticky_link( $converted_url ) {
		$query = wp_parse_url( $converted_url, PHP_URL_QUERY );
		if ( ! is_string( $query ) || '' === $query ) {
			return false;
		}

		$args = [];
		parse_str( $query, $args );

		return isset( $args['cat_ID'] );
	}

	private function get_term_link_in_language( $term_id, $taxonomy, $language ) {
		$previous_language = $this->sitepress->get_current_language();
		if ( $previous_language !== $language ) {
			$this->sitepress->switch_lang( $language );
		}

		try {
			$term_link = get_term_link( $term_id, $taxonomy );
		} finally {
			if ( $previous_language !== $language ) {
				$this->sitepress->switch_lang();
			}
		}

		return is_wp_error( $term_link ) ? '' : $term_link;
	}

	private function get_term_trid( WP_Term $term ) {
		$trid = $this->sitepress->get_element_trid(
			(int) $term->term_taxonomy_id,
			'tax_' . $term->taxonomy
		);

		return $trid ? (int) $trid : null;
	}

	private function get_permalink_in_language( $post_id, $language ) {
		$previous_language = $this->sitepress->get_current_language();
		if ( $previous_language !== $language ) {
			$this->sitepress->switch_lang( $language );
		}

		try {
			$permalink = get_permalink( $post_id );
		} finally {
			if ( $previous_language !== $language ) {
				$this->sitepress->switch_lang();
			}
		}

		return $permalink;
	}
}
