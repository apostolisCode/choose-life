<?php

// make sure this file is called by wp
defined( 'ABSPATH' ) or die();

/**
 * Class CRL_Cache
 *
 * if ( ($data = CRL_Cache::read( 'weather' )) === false ) {
 *      CRL_Cache::write( 'weather', $data );
 *      return $data;
 * }
 *
 */
class CRL_Cache {

	public static $prefix = 'CRL_';
	public static $enabled = true;
	public static $configs = [
		'default' => 900,
		'short' => 120,
		'long' => 3600,
		'sitemap' => 43200,
	];

	public static function enable( $mode = true ) {
		self::$enabled = $mode;
	}

	public static function write( $transient, $value, $expiration = null ) {
		if ( self::$enabled === false ) {
			return true;
		}
		if ( $expiration === null ) {
			$expiration = self::$configs['default'];
		}

		return set_transient( $transient, $value, $expiration );
	}

	public static function read( $transient = null ) {
		if ( self::$enabled === false ) {
			return false;
		}

		return get_transient( $transient );
	}

	public static function clear( $transient_key = null ) {
		if ( $transient_key == null || strtolower( $transient_key ) == 'all' || $transient_key === '1' ) {
			global $wpdb;

			$transient_key = self::$prefix;
			$transients = $wpdb->get_col( 'SELECT `option_id` FROM ' . $wpdb->options . ' WHERE `option_name` LIKE "_transient_' . $transient_key . '%" ' );

			foreach ( $transients as $id ) {
				$wpdb->delete( $wpdb->options, array (
					'option_id' => $id ) );
			}
		} else {
			delete_transient( $transient_key );
		}

		return true;
	}

	public static function create_key( $key = null ) {
		return self::$prefix . md5( json_encode( $key ) );
	}

	public static function create_page_key( $key = null, array $extras = [] ) {

		$key_parts = array_merge( [
			'key' => $key,
			'protocol' => (!empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http",
			'hostname' => filter_input( INPUT_SERVER, 'HTTP_HOST' ),
			'page' => filter_input( INPUT_SERVER, 'REQUEST_URI' ),
			'posted_data' => $_POST,
				], $extras );

		return self::$prefix . '_cache_' . md5( json_encode( $key_parts ) );
	}

}
