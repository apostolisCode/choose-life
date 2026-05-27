<?php

class Component_Base {

	protected static $class_instances = [];

	public static function get_render( $params = [] ) {

		return '';
	}

	public static function render( $params = [] ) {
		echo static::get_render( $params );
	}

	public static function get_cacheable_rendered_output( $params = [], $ttl = 900, $cacheTypes = 'url', $cacheGroup = '0' ) {

		$className = strtolower( get_called_class() );
		$httpData = [
			'protocol' => (!empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http",
			'hostname' => filter_input( INPUT_SERVER, 'HTTP_HOST' )
		];

		if ( !isset( static::$class_instances[$className] ) ) {
			static::$class_instances[$className] = 0;
		}
		static::$class_instances[$className] ++;

		$cachePrefix = '';

		foreach ( explode( '|', $cacheTypes ) as $cacheType ) {

			switch ( $cacheType ) {
				case 'global':

					break;

				case 'user_ip':
					$httpData['user_ip'] = filter_input( INPUT_SERVER, 'REMOTE_ADDR' );
					break;

				case 'instance':
					$cachePrefix .= static::$class_instances[$className] . '|';

				case 'url':
					$cachePrefix .= @filter_input( INPUT_SERVER, 'REQUEST_URI' ) . json_encode( $_POST ) . '|';
					break;

				default:
					$cachePrefix .= $cacheType . '|';
					break;
			}
		}

		$cacheKey = 'cached|' . $className . '|' . $cacheGroup . '|' . md5( $cachePrefix . json_encode( $httpData ) . json_encode( $params ) );

		$cachedOutput = MOZ_Cache::read( $cacheKey );

		if ( $cachedOutput === FALSE ) {
			$output = static::get_render( $params );
			MOZ_Cache::write( $cacheKey, $output, $ttl );
		} else {
			$output = $cachedOutput;
		}

		return $output;
	}

	public static function cacheable_rendered_output( $params = [], $ttl = 900, $cacheTypes = 'url', $cacheGroup = '0' ) {
		echo static::get_cacheable_rendered_output( $params, $ttl, $cacheTypes, $cacheGroup );
	}

	public static function clear_cached_class() {
		$className = strtolower( get_class() );
		MOZ_Cache::clear( 'cached|' . $className );
	}

}
