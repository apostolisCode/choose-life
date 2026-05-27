<?php

defined( 'ABSPATH' ) or die();

class Inc_Api {

	public static $instance = null;

	public static function get_instance() {
		null === self::$instance and self::$instance = new self();

		return self::$instance;
	}

	private $namespace = 'api/v1';

	private $nonce_action = 'cl_ajax';

	public function __construct() {

		// Payment gateway callbacks (called by Cardlink, not the front-end).
		// They are verified via HMAC digest and must stay reachable without a
		// nonce or a logged-in session, so they remain REST routes.
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
		add_filter( 'http_request_timeout', function () {
			return 60; // 60 seconds
		} );

		// Front-end (Vue) endpoints over admin-ajax with cookie + nonce auth.
		$this->register_ajax_actions();

	}

	public function register_rest_routes() {
		$routes = [
			'payment'            => [ 'POST', 'handle_payment_response' ],
			'donation/recurring' => [ 'POST', 'handle_recurring_payment_response' ],
		];
		foreach ( $routes as $route_name => $value ) {
			register_rest_route( $this->namespace, $route_name, array(
				'methods'             => $value[0],
				'callback'            => [ $this, $value[1] ],
				'permission_callback' => '__return_true',
			) );
		}
	}

	public function register_ajax_actions() {
		// action => requires logged-in user (priv only) ?
		$actions = [
			'cl_update_user'         => true,
			'cl_user_donations'      => true,
			'cl_user_subscriptions'  => true,
			'cl_reset_password_init' => false,
			'cl_reset_password'      => false,
			'cl_place_order'         => false,
			'cl_get_donation'        => false,
			'cl_pay_donation'        => false,
		];

		$callbacks = [
			'cl_update_user'         => 'update_user_fields',
			'cl_user_donations'      => 'user_donations',
			'cl_user_subscriptions'  => 'user_subscriptions',
			'cl_reset_password_init' => 'user_reset_password_init',
			'cl_reset_password'      => 'user_reset_password',
			'cl_place_order'         => 'place_order',
			'cl_get_donation'        => 'get_donation',
			'cl_pay_donation'        => 'pay_donation',
		];

		foreach ( $actions as $action => $priv_only ) {
			$callback = [ $this, $callbacks[ $action ] ];
			add_action( "wp_ajax_{$action}", $callback );
			if ( ! $priv_only ) {
				add_action( "wp_ajax_nopriv_{$action}", $callback );
			}
		}
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

	public function user_donations() {
		$this->verify_nonce();

		$response = [
			'success'    => true,
			'statusCode' => 200,
			'code'       => 'user_payments_success',
			'data'       => []
		];

		$payload = $this->get_payload();
		$page    = $payload['page'] ?? 1;

		$user_id          = get_current_user_id();
		$user_class       = new Inc_User( $user_id );
		$response['data'] = $user_class->get_user_donations( $page );

		wp_send_json( $response );
	}

	public function user_subscriptions() {
		$this->verify_nonce();

		$response = [
			'success'    => true,
			'statusCode' => 200,
			'code'       => 'user_subscriptions_success',
			'data'       => []
		];

		$payload = $this->get_payload();
		$page    = $payload['page'] ?? 1;

		$user_id          = get_current_user_id();
		$user_class       = new Inc_User( $user_id );
		$response['data'] = $user_class->get_user_subscriptions( $page );

		wp_send_json( $response );
	}

	public function update_user_fields() {
		$this->verify_nonce();

		$response = [
			'success'    => false,
			'statusCode' => 400,
			'code'       => 'update_user_error',
			'message'    => ''
		];

		$payload    = $this->get_payload();
		$fields     = $payload['fields'] ?? [];
		$user_id    = get_current_user_id();
		$user_class = new Inc_User( $user_id );

		try {
			$user_class->save_profile_fields( $fields );
		} catch ( Exception $e ) {
			$response['message'] = $e->getMessage();
			wp_send_json( $response );
		}

		$response['success']    = true;
		$response['statusCode'] = 200;
		$response['code']       = 'update_user_success';
		$response['message']    = __( 'Profile updated', 'choose-life' );

		wp_send_json( $response );
	}

	public function pay_donation() {
		$this->verify_nonce();

		$response = [
			'success'    => false,
			'statusCode' => 400,
			'code'       => 'pay_donation_error',
			'message'    => '',
			'data'       => []
		];

		$error_msg = __( 'There was an error. Please try again.', 'choose-life' );

		$payload      = $this->get_payload();
		$donation_key = $payload['id'] ?? '';
		$field        = $payload['field'] ?? 'key';
		if ( ! $donation_key ) {
			$response['message'] = $error_msg;
			wp_send_json( $response );
		}

		$donation_class = Inc_Donation::get_instance();
		$donation       = $donation_class->get_donation_by( $donation_key, $field === 'id' ? false : true );
		if ( ! $donation ) {
			$response['message'] = $error_msg;
			wp_send_json( $response );
		}

		$payment = new Inc_Payment();

		$response['success']    = true;
		$response['statusCode'] = 200;
		$response['code']       = 'pay_donation_success';
		$response['data']       = $payment->get_payment_form_params( $donation['data']['donation_id'] );

		wp_send_json( $response );
	}

	public function place_order() {
		$this->verify_nonce();

		$response = [
			'success'    => false,
			'statusCode' => 400,
			'code'       => 'place_order_error',
			'message'    => '',
			'data'       => []
		];

		$payload = $this->get_payload();
		$fields  = $payload['fields'] ?? [];
		if ( ! $fields ) {
			$response['message'] = __( 'There was an error. Please try again.', 'choose-life' );
			wp_send_json( $response );
		}

		$donation_class = new Inc_Donation();

		try {
			$response['data'] = $donation_class->create( $fields );
		} catch ( Exception $e ) {
			$response['message'] = $e->getMessage();
			wp_send_json( $response );
		}

		$response['success']    = true;
		$response['statusCode'] = 200;
		$response['code']       = 'place_order_success';

		wp_send_json( $response );
	}

	public function get_donation() {
		$this->verify_nonce();

		$response = [
			'success'    => false,
			'statusCode' => 400,
			'code'       => 'get_donation_error',
			'message'    => '',
			'data'       => []
		];

		$payload        = $this->get_payload();
		$donation_key   = $payload['id'] ?? '';
		$donation_class = new Inc_Donation();
		$donation       = $donation_class->get_donation_by( $donation_key, true );

		if ( ! $donation ) {
			wp_send_json( $response );
		}

		$payment_class              = new Inc_Payment();
		$response['data']['status'] = get_field( 'donation_status', $donation['data']['donation_id'] );
		$response['data']['texts']  = $payment_class->get_payment_status_messages( $response['data']['status'] );

		$response['success']    = true;
		$response['statusCode'] = 200;
		$response['code']       = 'get_donation_success';

		wp_send_json( $response );
	}

	public function user_reset_password_init() {
		$this->verify_nonce();

		$response = [
			'success'    => false,
			'statusCode' => 400,
			'code'       => 'user_reset_password_init_error',
			'message'    => '',
			'data'       => []
		];

		$payload     = $this->get_payload();
		$email       = $payload['email'] ?? '';
		$error_msg   = __( 'The email you entered is invalid.', 'choose-life' );
		$success_msg = sprintf( __( 'A One-Time Password (OTP) has been sent to %s. Please enter the OTP and your password in the form below. The OTP will expire in five minutes.', 'choose-life' ), $email );

		if ( ! is_email( $email ) ) {
			$response['message'] = $error_msg;
			wp_send_json( $response );
		}

		$user = get_user_by( "email", $email );
		if ( ! $user ) {
			$response['message'] = $error_msg;
			wp_send_json( $response );
		}

		$response['otp'] = Inc_Email::reset_password_init( $email );

		$response['success']    = true;
		$response['statusCode'] = 200;
		$response['message']    = $success_msg;
		$response['code']       = 'user_reset_password_init_success';

		wp_send_json( $response );
	}

	public function user_reset_password() {
		$this->verify_nonce();

		$response = [
			'success'    => false,
			'statusCode' => 400,
			'code'       => 'user_reset_password_error',
			'message'    => '',
			'data'       => []
		];

		$payload  = $this->get_payload();
		$otp      = $payload['otp'] ?? '';
		$password = $payload['password'] ?? '';

		if ( ! $otp || ! $password ) {
			$response['message'] = __( 'Invalid data. Please refresh the page and try again.', 'choose-life' );
			wp_send_json( $response );
		}
		$is_valid_password = CRL_Utils::strong_password_check( $password );
		if ( ! $is_valid_password ) {
			$response['message'] = __( 'You must enter a strong password.', 'choose-life' );
			wp_send_json( $response );
		}
		$otp_data = CRL_Cache::read( $otp );
		if ( ! $otp_data ) {
			$response['message'] = __( 'The One-Time Password (OTP) has expired. Please restart the password reset process.', 'choose-life' );
			wp_send_json( $response );
		}
		if ( ! array_key_exists( 'email', $otp_data ) || ! is_email( $otp_data['email'] ) ) {
			$response['message'] = __( 'Invalid One-Time Password (OTP). Please restart the password reset process.', 'choose-life' );
			wp_send_json( $response );
		}
		$user = get_user_by( 'email', $otp_data['email'] );
		if ( ! $user ) {
			$response['message'] = __( 'Invalid One-Time Password (OTP). Please restart the password reset process.', 'choose-life' );
			wp_send_json( $response );
		}

		wp_set_password( $password, $user->ID );

		$response['success']    = true;
		$response['statusCode'] = 200;
		$response['message']    = __( 'Your password has been changed successfully. You can now login with the new password.', 'choose-life' );
		$response['code']       = 'user_reset_password_success';

		wp_send_json( $response );
	}

	public function handle_recurring_payment_response( WP_REST_Request $request ) {

		$response = false;

		$data = $request->get_params();
		preg_match( '/(.*?)at/', $data['orderid'], $matches );
		$first_donation_id = $matches[1];

		$donation_class = Inc_Donation::get_instance();
		$donation_id    = $donation_class->create_recurring_donation( $first_donation_id, $data );
		if ( $donation_id ) {
			$payment  = Inc_Payment::get_instance();
			$response = $payment->handle_recurring_payment_response( $data, $donation_id );
		}

		return new WP_REST_Response( $response );
	}

	public function handle_payment_response( WP_REST_Request $request ) {

		$donation_key = $request->get_param( 'donation_key' );

		$booking_page_url = get_field( 'checkout_page_url', 'options' );
		$redirect_url     = $booking_page_url . '/#/payment/' . $donation_key;

		$data = $request->get_params();
		unset( $data['donation_key'] );

		$payment = Inc_Payment::get_instance();
		$payment->handle_payment_response( $data, $donation_key );

		wp_redirect( $redirect_url );
		exit();
	}

}
