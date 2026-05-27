<?php
/**
 * WordPress Responsive Images
 * Implementation
 *
 */

/**
 * Class CRL_Image
 *
 */
class CRL_Image {

	/**
	 * Get an image's alt
	 * attribute content
	 *
	 * @param $image
	 *
	 * @return string
	 */
	public static function get_img_alt( $image ) {
		return trim( strip_tags( get_post_meta( $image, '_wp_attachment_image_alt', true ) ) );
	}

	/**
	 * Print an image's alt
	 * attribute content
	 *
	 * @param $image
	 */
	public static function img_alt( $image ) {
		echo self::get_img_alt( $image );
	}

	/**
	 * Get an image's src
	 * attribute for the
	 * specified size
	 *
	 * @param        $image
	 * @param string $size
	 *
	 * @return bool
	 */
	public static function get_src( $image, $size = 'full' ) {
		if ( $src = wp_get_attachment_image_src( $image, $size, false ) ) {
			return $src[0];
		}

		return false;
	}


	/**
	 * Print an image's src
	 * attribute for the
	 * specified size
	 *
	 * @param        $image
	 * @param string $size
	 */
	public static function src( $image, $size = 'full' ) {
		echo self::get_src( $image, $size );
	}
	
	public static function responsive_background_image($default, $base, $sizes = array(), $attrs = array()) {
		echo self::get_responsive_background_image($default, $base, $sizes, $attrs);
	}

	public static function get_responsive_background_image($default, $base, $sizes = array(), $attrs = array()) {
		if ( ! wp_attachment_is_image( $default ) ) {
			return false;
		}

		$bgset = array();
		if ($sizes) {
			foreach ( $sizes as $query => $size ) {
				if ( ! wp_attachment_is_image( $size['id'] ) ) {
					continue;
				}
				$url = esc_attr( self::get_src($size['id'], $size['size']));
				$bgset[] = "$url [$query]";
			}
		}
		$baseUrl = esc_attr( self::get_src($base['id'], $base['size']));
		$defaultUrl = esc_attr( self::get_src($default, 'large'));
		$bgset[] = $baseUrl ? $baseUrl : $defaultUrl;

		$bg_attrs = array_merge( array(
			'role' => 'img',
			'data-bgset' => implode( ' | ', $bgset)
		), ( array ) $attrs );

		$bg_attrs['class'] = isset( $attrs['class'] ) ? "{$attrs[ 'class' ]} bg-picture lazyload" : 'bg-picture lazyload';

		return CRL_Html::get_element( 'span', $bg_attrs );

	}

	public static function lazify_attrs($attrs, $type = 'source') {

		if ($type == 'img') {
			$lazifiables = array( 'src');
			foreach ( $lazifiables as $lazifiable ) {
				if ( isset( $attrs[ $lazifiable ] ) ) {
					$attrs["data-$lazifiable"] = $attrs[ $lazifiable ];
				}
			}
			$attrs['class'] = isset( $attrs['class'] ) ? "{$attrs[ 'class' ]} lazyload" : 'lazyload';
			$attrs['src'] = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
		}

		if ($type == 'source') {
			$lazifiables = array( 'src', 'srcset');
			foreach ( $lazifiables as $lazifiable ) {
				if ( isset( $attrs[ $lazifiable ] ) ) {
					$attrs["data-$lazifiable"] = $attrs[ $lazifiable ];
				}
			}
			unset($attrs['srcset']);
		}

		return $attrs;
	}

	public static function get_responsive_picture( $image, $baseSize, $sizes, $attrs = array(), $flags = array() ) {
		if ( ! wp_attachment_is_image( $image ) ) {
			return false;
		}

		$content = array();

		if($sizes) {
			// required for IE9 support...
			$content[] = '<!--[if IE 9]><video style="display: none;"><![endif]-->';

			foreach ( array_reverse( $sizes ) as $size => $query ) {
				$source_attrs = array(
					'srcset' => esc_attr( self::get_src( $image, $size ) ),
					'media'  => esc_attr( $query ),
					'type'   => esc_attr( get_post_mime_type( $image ) )
				);
	
				if (isset($flags['lazy']) && $flags['lazy']) {
					$source_attrs = self::lazify_attrs( $source_attrs, 'source');
				}
	
				$content[] = CRL_Html::get_sc_element( 'source', $source_attrs );
	
			}

			$source_attrs_base = array(
				'srcset' => esc_attr( self::get_src( $image, $baseSize ) ),
				'type'   => esc_attr( get_post_mime_type( $image ) )
			);

			if (isset($flags['lazy']) && $flags['lazy']) {
				$source_attrs_base = self::lazify_attrs( $source_attrs_base, 'source');
			}

			$content[] = CRL_Html::get_sc_element( 'source', $source_attrs_base );

			$content[] = '<!--[if IE 9]></video><![endif]-->';
		}

		$imageUrl  = self::get_src( $image, $baseSize );

		$img_attrs = array_merge( array(
			'src'    => esc_attr( $imageUrl ),
			'alt'    => self::get_img_alt( $image )
		), ( array ) $attrs );

		if (isset($flags['lazy']) && $flags['lazy']) {
			$img_attrs = self::lazify_attrs($img_attrs, 'img');
		}
		
		$content[] = CRL_Html::get_sc_element( 'img', $img_attrs );

		return CRL_Html::get_element( 'picture', null, implode( '', $content ) );
	}

	public static function responsive_picture( $image, $baseSize = 'full', $sizes = array(), $attrs = array(), $flags = array() ) {
		echo self::get_responsive_picture( $image, $baseSize, $sizes, $attrs, $flags );
	}
}
