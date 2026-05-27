<?php

defined( 'ABSPATH' ) or die();

class Inc_Api {

	public static $instance = null;

	public static function get_instance() {
		null === self::$instance and self::$instance = new self();

		return self::$instance;
	}

	private $namespace = 'api/v1';

	public function __construct() {

		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
		add_filter( 'http_request_timeout', function () {
			return 60; // 60 seconds
		} );

	}

	public function register_rest_routes() {
		$routes = [
			'token'                    => [ 'POST', 'get_token' ],
			'user/update'              => [ 'POST', 'update_user_fields' ],
			'user/register'            => [ 'POST', 'register_user' ],
			'user/donations'           => [ 'POST', 'user_donations' ],
			'user/subscriptions'       => [ 'POST', 'user_subscriptions' ],
			'user/reset-password/init' => [ 'POST', 'user_reset_password_init' ],
			'user/reset-password'      => [ 'POST', 'user_reset_password' ],
			'place-order'              => [ 'POST', 'place_order' ],
			'payment'                  => [ 'POST', 'handle_payment_response' ],
			'donation/get'             => [ 'POST', 'get_donation' ],
			'donation/pay'             => [ 'POST', 'pay_donation' ],
			'donation/recurring'       => [ 'POST', 'handle_recurring_payment_response' ],
		];
		foreach ( $routes as $route_name => $value ) {
			register_rest_route( $this->namespace, $route_name, array(
				'methods'             => $value[0],
				'callback'            => [ $this, $value[1] ],
				'permission_callback' => '__return_true',
			) );
		}
	}

	public function register_user( WP_REST_Request $request ) {
		$response = [
			'success'    => false,
			'statusCode' => 400,
			'code'       => 'register_user_error',
			'message'    => '',
			'data'       => []
		];

		$email    = $request->get_param( 'email' );
		$password = $request->get_param( 'password' );

		try {
			$response['data'] = Inc_User::create_user( $email, $password );
		} catch ( Exception $e ) {
			$response['message'] = $e->getMessage();

			return new WP_REST_Response( $response, $response['statusCode'] );
		}

		$response['success']    = true;
		$response['statusCode'] = 200;
		$response['code']       = 'register_user_success';
		$response['message']    = __( 'Your account has been created', 'choose-life' );

		return new WP_REST_Response( $response, $response['statusCode'] );
	}

	public function user_donations( WP_REST_Request $request ) {
		$response = [
			'success'    => true,
			'statusCode' => 200,
			'code'       => 'user_payments_success',
			'data'       => []
		];

		$page = $request->get_param( 'page' );

		$user_id          = get_current_user_id();
		$user_class       = new Inc_User( $user_id );
		$response['data'] = $user_class->get_user_donations( $page );

		return new WP_REST_Response( $response, $response['statusCode'] );
	}

	public function user_subscriptions( WP_REST_Request $request ) {
		$response = [
			'success'    => true,
			'statusCode' => 200,
			'code'       => 'user_subscriptions_success',
			'data'       => []
		];

		$page = $request->get_param( 'page' );

		$user_id          = get_current_user_id();
		$user_class       = new Inc_User( $user_id );
		$response['data'] = $user_class->get_user_subscriptions( $page );

		return new WP_REST_Response( $response, $response['statusCode'] );
	}

	public function update_user_fields( WP_REST_Request $request ) {

		$response = [
			'success'    => false,
			'statusCode' => 400,
			'code'       => 'update_user_error',
			'message'    => ''
		];

		$fields     = $request->get_param( 'fields' );
		$user_id    = get_current_user_id();
		$user_class = new Inc_User( $user_id );

		try {
			$user_class->save_profile_fields( $fields );
		} catch ( Exception $e ) {
			$response['message'] = $e->getMessage();

			return new WP_REST_Response( $response, $response['statusCode'] );
		}

		$response['success']    = true;
		$response['statusCode'] = 200;
		$response['code']       = 'update_user_success';
		$response['message']    = __( 'Profile updated', 'choose-life' );

		return new WP_REST_Response( $response, $response['statusCode'] );
	}

	public function pay_donation( WP_REST_Request $request ) {
		$response = [
			'success'    => false,
			'statusCode' => 400,
			'code'       => 'pay_donation_error',
			'message'    => '',
			'data'       => []
		];

		$error_msg = __( 'There was an error. Please try again.', 'choose-life' );

		$donation_key = $request->get_param( 'id' );
		$field        = $request->get_param( 'field' );
		if ( ! $donation_key ) {
			$response['message'] = $error_msg;

			return new WP_REST_Response( $response, $response['statusCode'] );
		}

		$donation_class = Inc_Donation::get_instance();
		$donation       = $donation_class->get_donation_by( $donation_key, $field === 'id' ? false : true );
		if ( ! $donation ) {
			$response['message'] = $error_msg;

			return new WP_REST_Response( $response, $response['statusCode'] );
		}

		$payment = new Inc_Payment();

		$response['success']    = true;
		$response['statusCode'] = 200;
		$response['code']       = 'pay_donation_success';
		$response['data']       = $payment->get_payment_form_params( $donation['data']['donation_id'] );

		return new WP_REST_Response( $response, $response['statusCode'] );
	}

	public function place_order( WP_REST_Request $request ) {
		$response = [
			'success'    => false,
			'statusCode' => 400,
			'code'       => 'place_order_error',
			'message'    => '',
			'data'       => []
		];

		$fields = $request->get_param( 'fields' );
		if ( ! $fields ) {
			$response['message'] = __( 'There was an error. Please try again.', 'choose-life' );

			return new WP_REST_Response( $response, $response['statusCode'] );
		}

		$donation_class = new Inc_Donation();

		try {
			$response['data'] = $donation_class->create( $fields );
		} catch ( Exception $e ) {
			$response['message'] = $e->getMessage();

			return new WP_REST_Response( $response, $response['statusCode'] );
		}

		$response['success']    = true;
		$response['statusCode'] = 200;
		$response['code']       = 'place_order_success';

		return new WP_REST_Response( $response, $response['statusCode'] );
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

	public function get_donation( WP_REST_Request $request ) {
		$response = [
			'success'    => false,
			'statusCode' => 400,
			'code'       => 'get_donation_error',
			'message'    => '',
			'data'       => []
		];

		$donation_key   = $request->get_param( 'id' );
		$donation_class = new Inc_Donation();
		$donation       = $donation_class->get_donation_by( $donation_key, true );

		if ( ! $donation ) {
			return new WP_REST_Response( $response, $response['statusCode'] );
		}

		$payment_class              = new Inc_Payment();
		$response['data']['status'] = get_field( 'donation_status', $donation['data']['donation_id'] );
		$response['data']['texts']  = $payment_class->get_payment_status_messages( $response['data']['status'] );

		$response['success']    = true;
		$response['statusCode'] = 200;
		$response['code']       = 'get_donation_success';

		return new WP_REST_Response( $response, $response['statusCode'] );
	}

	public function user_reset_password_init( WP_REST_Request $request ) {
		$response = [
			'success'    => false,
			'statusCode' => 400,
			'code'       => 'user_reset_password_init_error',
			'message'    => '',
			'data'       => []
		];

		$email       = $request->get_param( 'email' );
		$error_msg   = __( 'The email you entered is invalid.', 'choose-life' );
		$success_msg = sprintf( __( 'A One-Time Password (OTP) has been sent to %s. Please enter the OTP and your password in the form below. The OTP will expire in five minutes.', 'choose-life' ), $email );

		if ( ! is_email( $email ) ) {
			$response['message'] = $error_msg;

			return new WP_REST_Response( $response, $response['statusCode'] );
		}

		$user = get_user_by( "email", $email );
		if ( ! $user ) {
			$response['message'] = $error_msg;

			return new WP_REST_Response( $response, $response['statusCode'] );
		}

		$response['otp'] = Inc_Email::reset_password_init( $email );

		$response['success']    = true;
		$response['statusCode'] = 200;
		$response['message']    = $success_msg;
		$response['code']       = 'user_reset_password_init_success';

		return new WP_REST_Response( $response, $response['statusCode'] );

	}

	public function user_reset_password( WP_REST_Request $request ) {

		$response = [
			'success'    => false,
			'statusCode' => 400,
			'code'       => 'user_reset_password_error',
			'message'    => '',
			'data'       => []
		];
		$otp      = $request->get_param( 'otp' );
		$password = $request->get_param( 'password' );

		if ( ! $otp || ! $password ) {
			$response['message'] = __( 'Invalid data. Please refresh the page and try again.', 'choose-life' );

			return new WP_REST_Response( $response, $response['statusCode'] );
		}
		$is_valid_password = CRL_Utils::strong_password_check( $password );
		if ( ! $is_valid_password ) {
			$response['message'] = __( 'You must enter a strong password.', 'choose-life' );

			return new WP_REST_Response( $response, $response['statusCode'] );
		}
		$otp_data = CRL_Cache::read( $otp );
		if ( ! $otp_data ) {
			$response['message'] = __( 'The One-Time Password (OTP) has expired. Please restart the password reset process.', 'choose-life' );

			return new WP_REST_Response( $response, $response['statusCode'] );
		}
		if ( ! array_key_exists( 'email', $otp_data ) || ! is_email( $otp_data['email'] ) ) {
			$response['message'] = __( 'Invalid One-Time Password (OTP). Please restart the password reset process.', 'choose-life' );

			return new WP_REST_Response( $response, $response['statusCode'] );
		}
		$user = get_user_by( 'email', $otp_data['email'] );
		if ( ! $user ) {
			$response['message'] = __( 'Invalid One-Time Password (OTP). Please restart the password reset process.', 'choose-life' );

			return new WP_REST_Response( $response, $response['statusCode'] );
		}

		wp_set_password( $password, $user->ID );

		$response['success']    = true;
		$response['statusCode'] = 200;
		$response['message']    = __( 'Your password has been changed successfully. You can now login with the new password.', 'choose-life' );
		$response['code']       = 'user_reset_password_success';

		return new WP_REST_Response( $response, $response['statusCode'] );
	}

}
