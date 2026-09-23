<?php
namespace WPML\WPSEO\RankMathSEO;

use RankMath\Helper;

class Utils {

	public static function isIndexablePost( $postId ) {
		return Helper::is_post_indexable( $postId );
	}

	public static function isIndexableTerm( $term ) {
		return Helper::is_term_indexable( $term );
	}
}
