<?php

defined( 'ABSPATH' ) or die();

class Inc_Payment {

	public static $instance = null;

	public static function get_instance() {
		null === self::$instance and self::$instance = new self();

		return self::$instance;
	}

	public $home_url;
	public $redirect_url = [
		'test' => 'https://ecommerce-test.cardlink.gr/vpos/shophandlermpi',
		'prod' => 'https://ecommerce.cardlink.gr/vpos/shophandlermpi'
	];
	public $is_test;
	public $mid;
	public $secret;
	public $donation_id;

	public function __construct() {

		$this->home_url = get_site_url() . '/';
		$this->is_test  = get_field( 'enable_test_environment', 'options' );
		$this->mid      = get_field( 'payment_mid', 'options' );
		$this->secret   = get_field( 'payment_secret', 'options' );

	}

	public function get_payment_form_params( $donation_id ) {

		$this->donation_id = $donation_id;
		$type              = get_field( 'donation_type', $donation_id );

		$posted_data_array = $this->get_posted_data();

		if ( $type == 'recurring' ) {
			$confirm_url = $posted_data_array['confirmUrl'];
			$cancel_url  = $posted_data_array['cancelUrl'];
			unset( $posted_data_array['confirmUrl'] );
			unset( $posted_data_array['cancelUrl'] );

			$months   = (int) get_field( 'donation_frequency', $donation_id );
			$end_date = get_field( 'recurring_end_date', $donation_id );

			$posted_data_array['extRecurringfrequency'] = round($months * 28);
			$posted_data_array['extRecurringenddate']   = $end_date;
			$posted_data_array['confirmUrl']            = $confirm_url;
			$posted_data_array['cancelUrl']             = $cancel_url;
		}

		$posted_data_string = '';
		foreach ( $posted_data_array as $k => $v ) {
			$posted_data_string .= $v;
		}
		$form_data                   = iconv( 'utf-8', 'utf-8//IGNORE', $posted_data_string ) . $this->secret;
		$posted_data_array['digest'] = $this->calculate_digest( $form_data );

		return [
			'post_url' => $this->get_redirect_url(),
			'fields'   => $posted_data_array
		];
	}

	private function get_redirect_url() {
		if ( $this->is_test ) {
			return $this->redirect_url['test'];
		}

		return $this->redirect_url['prod'];
	}

	private function get_posted_data() {

		$current_lang = get_locale();
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$current_lang = apply_filters( 'wpml_current_language', null );
		}

		$donation_class  = Inc_Donation::get_instance();
		$donation_fields = $donation_class->get_donation_fields_by_id( $this->donation_id );

		$order_total      = $donation_fields['donation_amount']['value'];
		$user_email       = $donation_fields['email']['value'];
		$billing_postcode = $donation_fields['billing_postal_code']['value'];
		$billing_city     = $donation_fields['billing_city']['value'];
		$billing_address  = $donation_fields['billing_address']['value'];
		$billing_country  = $donation_fields['billing_country']['value'];
		$donation_key     = get_post_meta( $this->donation_id, 'donation_key', true );

		return [
			'version'     => 2,
			'mid'         => $this->mid,
			'lang'        => $current_lang,
			'orderid'     => $this->donation_id . 'at' . date( 'Ymdhisu' ),
			'orderDesc'   => 'Order #' . $this->donation_id,
			'orderAmount' => number_format( $order_total, 2, '.', '' ),
			'currency'    => 'EUR',
			'payerEmail'  => $user_email,
			'billCountry' => $billing_country,
			'billZip'     => $billing_postcode,
			'billCity'    => CRL_Utils::clean_string( $billing_city ),
			'billAddress' => CRL_Utils::clean_string( $billing_address ),
			'trType'      => 1,
			'confirmUrl'  => $this->home_url . "wp-json/api/v1/payment?donation_key=" . $donation_key,
			'cancelUrl'   => $this->home_url . "wp-json/api/v1/payment?donation_key=" . $donation_key
		];

	}

	public function calculate_digest( $input ) {

		return base64_encode( hash( 'sha256', ( $input ), true ) );
	}

	public function handle_recurring_payment_response( $posted_data, $donation_id ) {

		$result         = [
			'status'      => 'failed',
			'donation_id' => $donation_id,
		];

		// the digest is checked by Inc_Api::handle_recurring_payment_response()
		if ( $posted_data['status'] === 'CAPTURED' || $posted_data['status'] === 'AUTHORIZED' ) {
			$result['status'] = 'completed';
		}
		update_field( 'donation_status', $result['status'], $donation_id );

		if ( $result['status'] === 'completed' ) {
			( new Inc_Email( $donation_id ) )->send_email();
		}

		return $result;
	}

	public function handle_payment_response( $posted_data, $order_key ) {

		$result         = [
			'status'      => 'failed',
			'donation_id' => null,
		];
		$donation_class = Inc_Donation::get_instance();
		$donation       = $donation_class->get_donation_by( $order_key, true );
		if ( ! $donation || ! $donation['success'] ) {
			return $result;
		}
		$result['donation_id'] = $donation['data']['donation_id'];
		$status                = get_field( 'donation_status', $result['donation_id'] );
		if ( $status === 'completed' ) {
			$result['status'] = 'completed';

			return $result;
		}

		$computed_digest = $this->get_response_digest( $posted_data );
		if ( ! hash_equals( $computed_digest, (string) ( $posted_data['digest'] ?? '' ) ) ) {
			write_log( 'Payment callback rejected: digest mismatch' );
			write_log( $posted_data );

			return $result;
		}

		if ( $posted_data['status'] === 'CAPTURED' || $posted_data['status'] === 'AUTHORIZED' ) {
			$result['status'] = 'completed';
		}
		update_field( 'donation_status', $result['status'], $result['donation_id'] );

		if ( $result['status'] === 'completed' ) {
			( new Inc_Email( $result['donation_id'] ) )->send_email();
		}

		if ($donation['data']['donation_type']['value'] === 'recurring' &&
		    $result['status'] === 'completed' ) {
			try {
				Inc_Subscription::create($result['donation_id']);
			} catch ( Exception $e ) {
				write_log("Create subscription error");
				write_log($e);
			}
		}

		return $result;
	}

	public function get_response_digest( $posted_data ) {
		$form_data = '';
		foreach ( $posted_data as $k => $v ) {
			if ( $k == 'digest' ) {
				continue;
			}
			$form_data .= $v;
		}
		$form_data .= $this->secret;

		return $this->calculate_digest( $form_data );
	}

	public static function calculate_recurring_end_date() {

		$today = new DateTime();
		$today->add( new DateInterval( 'P4Y' ) );
		$end_date = $today->format( 'Ymd' );

		return $end_date;
	}

	public function get_payment_status_messages( $status = 'failed' ) {

		$messages = [
			'completed' => get_field( 'payment_success_messages', 'options' ),
			'failed'    => get_field( 'payment_failed_messages', 'options' )
		];

		// anything not completed (failed, or still pending) gets the "failed" texts
		$texts = $messages[ $status ] ?? $messages['failed'];

		return [
			'title'        => $texts['title'] ?? '',
			'content'      => $texts['content'] ?? '',
			'closing_text' => $texts['closing_text'] ?? '',
		];

	}
}
