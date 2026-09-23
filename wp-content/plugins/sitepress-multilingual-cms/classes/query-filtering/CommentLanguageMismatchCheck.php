<?php

namespace WPML\QueryFiltering;

class CommentLanguageMismatchCheck {

	const HAS_ROWS_OPTION = 'wpml_has_comment_language_rows';

	const CACHE_GROUP = 'wpml_comment_lang';

	const POST_TYPE_PATTERN = 'post\\_%';

	const NO_MAX_AGE = 86400;

	private static $site_has_rows = array();

	private $wpdb;

	private $trid_by_post = array();

	public function __construct( \wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	public static function resetRequestCache() {
		self::$site_has_rows = array();
	}

	public function groupHasMismatchedCommentLanguage( $post_id ) {
		$post_id = (int) $post_id;
		if ( ! $post_id ) {
			return false;
		}

		if ( ! $this->siteHasCommentLanguageRows() ) {
			return false;
		}

		$trid = $this->tridForPost( $post_id );
		if ( ! $trid ) {
			return false;
		}

		$cached = wp_cache_get( $trid, self::CACHE_GROUP );
		if ( false !== $cached ) {
			return (bool) $cached;
		}

		$mismatch = (bool) $this->probeGroupForMismatch( $trid );
		wp_cache_set( $trid, $mismatch ? 1 : 0, self::CACHE_GROUP );

		return $mismatch;
	}

	public function siteHasCommentLanguageRows() {
		$blog_id = get_current_blog_id();
		if ( isset( self::$site_has_rows[ $blog_id ] ) ) {
			return self::$site_has_rows[ $blog_id ];
		}

		$stored = get_option( self::HAS_ROWS_OPTION );
		if ( is_array( $stored ) && isset( $stored['value'] ) ) {
			if ( 'yes' === $stored['value'] ) {
				self::$site_has_rows[ $blog_id ] = true;

				return true;
			}

			$computed_at = isset( $stored['computed_at'] ) ? (int) $stored['computed_at'] : 0;
			if ( 'no' === $stored['value'] && ( time() - $computed_at ) < self::NO_MAX_AGE ) {
				self::$site_has_rows[ $blog_id ] = false;

				return false;
			}
		}

		self::$site_has_rows[ $blog_id ] = (bool) $this->probeForCommentLanguageRows();
		$this->storeSitewideFlag( self::$site_has_rows[ $blog_id ] ? 'yes' : 'no' );

		return self::$site_has_rows[ $blog_id ];
	}

	public function rememberSiteHasCommentLanguageRows() {
		self::$site_has_rows[ get_current_blog_id() ] = true;
		$this->storeSitewideFlag( 'yes' );
	}

	public function forgetSitewideFlag() {
		unset( self::$site_has_rows[ get_current_blog_id() ] );
		delete_option( self::HAS_ROWS_OPTION );
	}

	public function forgetGroupForComment( $comment_id ) {
		$comment_id = (int) $comment_id;
		if ( ! $comment_id ) {
			return;
		}

		$wpdb   = $this->wpdb;
		$post_id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT comment_post_ID FROM {$wpdb->comments} WHERE comment_ID = %d",
				$comment_id
			)
		);

		$this->forgetGroupForPost( $post_id );
	}

	public function forgetGroupForPost( $post_id ) {
		$post_id = (int) $post_id;
		if ( ! $post_id ) {
			return;
		}

		unset( $this->trid_by_post[ $post_id ] );

		$this->forgetGroupByTrid( $this->tridForPost( $post_id ) );
	}

	public function forgetGroupByTrid( $trid ) {
		$trid = (int) $trid;
		if ( ! $trid ) {
			return;
		}

		wp_cache_delete( $trid, self::CACHE_GROUP );
	}

	private function tridForPost( $post_id ) {
		if ( isset( $this->trid_by_post[ $post_id ] ) ) {
			return $this->trid_by_post[ $post_id ];
		}

		$wpdb = $this->wpdb;
		$trid = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT trid FROM {$wpdb->prefix}icl_translations
					WHERE element_id = %d AND element_type LIKE %s
					LIMIT 1",
				$post_id,
				self::POST_TYPE_PATTERN
			)
		);

		$this->trid_by_post[ $post_id ] = $trid;

		return $trid;
	}

	private function probeForCommentLanguageRows() {
		$found = $this->wpdb->get_var(
			"SELECT translation_id FROM {$this->wpdb->prefix}icl_translations
				WHERE element_type = 'comment'
				LIMIT 1"
		);

		return $found;
	}

	private function probeGroupForMismatch( $trid ) {
		$wpdb  = $this->wpdb;
		$found = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT 1
					FROM {$wpdb->prefix}icl_translations post_row
					INNER JOIN {$wpdb->comments} wpml_c
						ON wpml_c.comment_post_ID = post_row.element_id
					INNER JOIN {$wpdb->prefix}icl_translations comment_row
						ON comment_row.element_type = 'comment'
						AND comment_row.element_id = wpml_c.comment_ID
					WHERE post_row.trid = %d
						AND post_row.element_type LIKE %s
						AND comment_row.language_code <> post_row.language_code
					LIMIT 1",
				$trid,
				self::POST_TYPE_PATTERN
			)
		);

		return $found;
	}

	private function storeSitewideFlag( $value ) {
		update_option(
			self::HAS_ROWS_OPTION,
			array(
				'value'       => $value,
				'computed_at' => time(),
			),
			false
		);
	}
}
