<?php

defined( 'ABSPATH' ) or die();

class Inc_Email {

	public static $instance = null;

	public static function get_instance() {
		null === self::$instance and self::$instance = new self();

		return self::$instance;
	}

	public $donation_id = null;
	public $emails = [];

	public function __construct( $donation_id = null ) {
		$this->donation_id = $donation_id;
		$this->emails[]    = get_field( 'email', $this->donation_id );
	}

	public function send_email() {
		$to        = $this->emails;
		$subject   = wp_strip_all_tags( html_entity_decode( $this->get_email_subject() ) );
		$body      = $this->get_email_body();
		$headers   = [];
		$headers[] = 'Content-Type: text/html';
		$headers[] = 'charset=UTF-8';
		//$headers[] = 'Bcc: booking@csr-air.gr';

		$mail_send = wp_mail( $to, $subject, $body, $headers );
		if ( ! $mail_send ) {
			self::schedule_email( $to, $subject, $body, $headers );
		}

		return $mail_send;
	}

	public static function schedule_email( $to, $subject, $body, $headers = [], $attachments = [] ) {

		wp_schedule_single_event( time() + 300, 'cl_schedule_email_notification', [
			$to,
			$subject,
			$body,
			$headers,
			$attachments
		] );
	}

	private function get_email_subject() {
		$subject = get_field( 'thank_you_email_subject', 'options' );
		if ( $subject !== '' ) {
			$subject = $this->add_dynamic_variables( $subject );
		}

		return apply_filters( 'the_content', $subject );
	}

	private function get_email_body() {
		ob_start();

		get_template_part( 'elements/email-template/header' );
		echo $this->get_email_content();
		get_template_part( 'elements/email-template/footer' );

		return ob_get_clean();
	}

	public function get_email_content() {
		$body = get_field( 'thank_you_email_content', 'options' );

		if ( $body !== '' ) {
			$body = $this->add_dynamic_variables( $body );
		}

		return apply_filters( 'the_content', $body );
	}

	public function add_dynamic_variables( $text ) {

		$dynamicVariables = [
			'{ORDER_NUMBER}' => '#' . $this->donation_id,
			//'{ORDER_TABLE}'  => '[booking_table donation_id="' . $this->donation_id . '"]',
		];

		foreach ( $dynamicVariables as $key => $value ) {
			$text = str_replace( $key, $value, $text );
		}

		return $text;

	}

	public static function reset_password( $email ) {

	}

	public static function reset_password_init( $email ) {

		$otp = CRL_Utils::get_upper( CRL_Utils::generate_random_string( 5 ) );
		CRL_Cache::write( $otp, [
			'email' => $email
		], 720 ); // 5 minutes

		$to             = $email;
		$subject        = __( 'Your one time password', 'choose-life' );
		$custom_subject = get_field( 'otp_email_subject', 'options' );
		if ( $custom_subject ) {
			$subject = $custom_subject;
		}
		$body             = get_field( 'otp_email_content', 'options' );
		$dynamicVariables = [
			'{USER_EMAIL}' => $email,
			'{OTP}'        => $otp,
		];
		foreach ( $dynamicVariables as $key => $value ) {
			$body = str_replace( $key, $value, $body );
		}

		$headers   = [];
		$headers[] = 'Content-Type: text/html';
		$headers[] = 'charset=UTF-8';

		$mail_send = wp_mail( $to, $subject, $body, $headers );
		if ( ! $mail_send ) {
			self::schedule_email( $to, $subject, $body, $headers );
		}

		return $otp;
	}

}
