<?php

defined( 'ABSPATH' ) or die();

class Inc_Auth {

	public static $instance = null;

	public static function get_instance() {
		null === self::$instance and self::$instance = new self();

		return self::$instance;
	}

	public function __construct() {

		add_filter( 'jwt_auth_valid_credential_response', [ $this, 'modify_jwt_payload' ], 10, 2 );
		add_filter( 'jwt_auth_valid_token_response', [ $this, 'modify_jwt_valid_payload' ], 10, 3 );

	}

	public function modify_jwt_valid_payload( $response, $user, $token ) {

		$user_class = new Inc_User($user->ID);

		$response['data'] = array_merge( $user_class->get_user_fields(), [ "token" => $token ] );

		return $response;
	}

	public function modify_jwt_payload( $response, $user ) {

		$user_class = new Inc_User($user->ID);

		unset( $response['data']['displayName'] );
		unset( $response['data']['firstName'] );
		unset( $response['data']['id'] );
		unset( $response['data']['lastName'] );
		unset( $response['data']['nicename'] );

		$response['data'] = array_merge( $response['data'], $user_class->get_user_fields() );

		return $response;
	}


}
