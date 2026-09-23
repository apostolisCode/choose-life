<?php

namespace WPML\PB\Elementor\Hooks;

use WPML\FP\Obj;
use WPML\PB\Elementor\Config\DynamicElements\Provider;
use WPML\PB\Elementor\Helper\ElementTree;
use function WPML\FP\curryN;
use function WPML\FP\spreadArgs;

class DynamicElements implements \IWPML_Frontend_Action, \IWPML_DIC_Action {

	public function add_hooks() {
		add_filter( 'elementor/frontend/builder_content_data', [ $this, 'convert' ] );
	}

	public function convert( array $data ) {
		$convertTag = curryN( 3, [ __CLASS__, 'convertTag' ] );

		$assignConvertCallable = function( $shouldConvert, $lens, $allowedTag = null, $idKey = null ) use ( $convertTag ) {
			if ( $allowedTag && $idKey ) {
				$convert = $convertTag( $allowedTag, $idKey );
			} else {
				$convert = [ __CLASS__, 'convertId' ];
			}

			return [ $shouldConvert, Obj::over( $lens, $convert ) ];
		};
		
		$converters = apply_filters( 'wpml_pb_elementor_widget_dynamic_id_converters', Provider::get() );

		$converters = wpml_collect( $converters )
			->map( spreadArgs( $assignConvertCallable ) )
			->toArray();

		return ElementTree::map( $data, self::applyConverters( $converters ) );
	}

	private static function applyConverters( array $converters ) {
		return function ( array $element ) use ( $converters ) {
			foreach ( $converters as $converter ) {
				list( $shouldConvert, $convert ) = $converter;

				if ( $shouldConvert( $element ) ) {
					$element = $convert( $element );
				}
			}

			return $element;
		};
	}

	public static function convertTag( $allowedTag, $idKey, $tagString ) {
		if ( ! $tagString ) {
			return $tagString;
		}

		preg_match( '/name="(.*?(?="))"/', $tagString, $tagNameMatch );

		if ( ! $tagNameMatch || $tagNameMatch[1] !== $allowedTag ) {
			return $tagString;
		}

		return preg_replace_callback( '/settings="(.*?(?="]))/', function( array $matches ) use ( $idKey ) {
			$settings = json_decode( urldecode( $matches[1] ), true );

			if ( ! isset( $settings[ $idKey ] ) ) {
				return $matches[0];
			}

			$settings[ $idKey ] = self::convertId( $settings[ $idKey ] );
			$replace            = urlencode( json_encode( $settings ) );

			return str_replace( $matches[1], $replace, $matches[0] );

		}, $tagString );
	}

	public static function convertId( $elementId ) {
		return apply_filters( 'wpml_object_id', $elementId, get_post_type( $elementId ), true );
	}
}
