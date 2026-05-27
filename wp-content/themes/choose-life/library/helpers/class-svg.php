<?php
/**
 * Static helpers for using svgs
 * in your theme
 *
 */


/**
 * Class CRL_SVG
 */
class CRL_SVG {


	/**
	 * Get a given svg's
	 * markup
	 *
	 * @param $filename
	 *
	 * @return string
	 */
	public static function get_svg( $filename ) {
		ob_start();
		locate_template( "assets/svg/$filename.svg", true, false );

		return ob_get_clean();
	}

	/**
	 * Print a given svg's
	 * markup
	 *
	 * @param $filename
	 */
	public static function svg( $filename ) {
		echo self::get_svg( $filename );
	}

}
