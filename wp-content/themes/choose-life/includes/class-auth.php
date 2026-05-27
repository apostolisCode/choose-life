<?php

defined( 'ABSPATH' ) or die();

class Inc_Auth {

	public static $instance = null;

	public static function get_instance() {
		null === self::$instance and self::$instance = new self();

		return self::$instance;
	}

	private $nonce_action = 'cl_ajax';

	public function __construct() {

		add_action( 'wp_ajax_cl_login', [ $this, 'login' ] );
		add_action( 'wp_ajax_nopriv_cl_login', [ $this, 'login' ] );

		add_action( 'wp_ajax_cl_register', [ $this, 'register' ] );
		add_action( 'wp_ajax_nopriv_cl_register', [ $this, 'register' ] );

		add_action( 'wp_ajax_cl_me', [ $this, 'me' ] );
		add_action( 'wp_ajax_nopriv_cl_me', [ $this, 'me' ] );

		add_action( 'wp_ajax_cl_logout', [ $this, 'logout' ] );

		// Make sure wp_create_nonce() computed right after a login within the
		// same request uses the freshly issued session token (the cookie is not
		// yet present in $_COOKIE on the request that sets it).
		add_action( 'set_logged_in_cookie', function ( $logged_in_cookie ) {
			$_COOKIE[ LOGGED_IN_COOKIE ] = $logged_in_cookie;
		} );

	}

	/**
	 * Verify the ajax nonce, dying with a JSON error if it is invalid.
	 */
	private function verify_nonce() {
		if ( ! check_ajax_referer( $this->nonce_action, '_ajax_nonce', false ) ) {
			wp_send_json( [
				'success'    => false,
				'statusCode' => 403,
				'code'       => 'invalid_nonce',
				'message'    => __( 'Your session has expired. Please refresh the page and try again.', 'choose-life' ),
				'data'       => []
			] );
		}
	}

	/**
	 * Read the JSON payload sent by the front-end.
	 */
	private function get_payload() {
		return json_decode( wp_unslash( $_POST['payload'] ?? '{}' ), true ) ?: [];
	}

	/**
	 * Build the authenticated response for a logged-in user: profile fields
	 * plus a fresh nonce valid for the new session.
	 */
	private function authenticated_response( $user_id ) {
		$user_class = new Inc_User( $user_id );

		return [
			'success'    => true,
			'statusCode' => 200,
			'code'       => 'authenticated',
			'message'    => '',
			'data'       => $user_class->get_user_fields(),
			'nonce'      => wp_create_nonce( $this->nonce_action ),
		];
	}

	public function login() {

		$this->verify_nonce();

		$response = [
			'success'    => false,
			'statusCode' => 400,
			'code'       => 'login_error',
			'message'    => __( 'Wrong email or password.', 'choose-life' ),
			'data'       => []
		];

		$payload  = $this->get_payload();
		$username = $payload['username'] ?? '';
		$password = $payload['password'] ?? '';

		if ( ! $username || ! $password ) {
			wp_send_json( $response );
		}

		$user = wp_signon( [
			'user_login'    => $username,
			'user_password' => $password,
			'remember'      => true,
		], is_ssl() );

		if ( is_wp_error( $user ) ) {
			wp_send_json( $response );
		}

		wp_set_current_user( $user->ID );

		wp_send_json( $this->authenticated_response( $user->ID ) );
	}

	public function register() {

		$this->verify_nonce();

		$response = [
			'success'    => false,
			'statusCode' => 400,
			'code'       => 'register_user_error',
			'message'    => '',
			'data'       => []
		];

		$payload  = $this->get_payload();
		$email    = $payload['email'] ?? '';
		$password = $payload['password'] ?? '';

		try {
			$user_id = Inc_User::create_user( $email, $password );
		} catch ( Exception $e ) {
			$response['message'] = $e->getMessage();
			wp_send_json( $response );
		}

		// Auto-login the newly created user (preserves previous behaviour).
		wp_set_auth_cookie( $user_id, true, is_ssl() );
		wp_set_current_user( $user_id );

		$response               = $this->authenticated_response( $user_id );
		$response['code']       = 'register_user_success';
		$response['message']    = __( 'Your account has been created', 'choose-life' );

		wp_send_json( $response );
	}

	public function me() {

		$this->verify_nonce();

		if ( ! is_user_logged_in() ) {
			wp_send_json( [
				'success'    => false,
				'statusCode' => 401,
				'code'       => 'not_logged_in',
				'message'    => '',
				'data'       => []
			] );
		}

		wp_send_json( $this->authenticated_response( get_current_user_id() ) );
	}

	public function logout() {

		$this->verify_nonce();

		wp_logout();

		wp_send_json( [
			'success'    => true,
			'statusCode' => 200,
			'code'       => 'logout_success',
			'message'    => '',
			'data'       => []
		] );
	}

}
