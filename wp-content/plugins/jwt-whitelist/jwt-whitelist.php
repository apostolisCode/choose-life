<?php
/**
 * Plugin Name: JWT Whitelist
 * Description: WordPress JWT Whitelist endpoints for Cardlink API.
 * Version:     0.0.1
 * Author:      Cardlink
 * Author URI:  https://cardlink.gr
 * License:     GPL-3.0
 * Text Domain: jwt-whitelist
 *
 * @package jwt-whitelist
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );


add_filter( 'jwt_auth_default_whitelist', function ( $default_whitelist ) {

	// plugin endpoints
	$default_whitelist[] = '/wp-json/contact-form-7';
	// my endpoints
	$default_whitelist[] = '/wp-json/api/v1/user/register';
	$default_whitelist[] = '/wp-json/api/v1/user/reset-password/init';
	$default_whitelist[] = '/wp-json/api/v1/user/reset-password';
	$default_whitelist[] = '/wp-json/api/v1/place-order';
	$default_whitelist[] = '/wp-json/api/v1/payment';
	$default_whitelist[] = '/wp-json/api/v1/donation/get';
	$default_whitelist[] = '/wp-json/api/v1/donation/pay';
	$default_whitelist[] = '/wp-json/api/v1/donation/recurring';

	return $default_whitelist;
} );
