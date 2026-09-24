<?php

defined( 'ABSPATH' ) or die();

class Inc_Email {

	public static $instance = null;

	public static function get_instance() {
		null === self::$instance and self::$instance = new self();

		return self::$instance;
	}

	// set on a donation once its thank you email has gone out (or is queued)
	const SENT_META = '_thank_you_email_sent';

	// password reset codes: one per user, kept as a hash in a transient
	const RESET_TRANSIENT = 'cl_reset_password_';
	const RESET_EXPIRATION = 720;
	const RESET_MAX_ATTEMPTS = 5;

	public $donation_id = null;
	public $emails = [];

	private $previous_language = null;
	private $switched_locale = false;

	public function __construct( $donation_id = null ) {
		$this->donation_id = $donation_id;
		$this->emails[]    = get_field( 'email', $this->donation_id );
	}

	/**
	 * Thank you email with the donation details, after a successful payment:
	 * a one-off donation, the first payment of a recurring one and every
	 * recurring charge after it. Once per donation, in the donation's language.
	 *
	 * Texts: Theme Options → Success donation email, with defaults when empty.
	 *
	 * @return bool
	 */
	public function send_email() {
		$to = array_values( array_filter( $this->emails, 'is_email' ) );
		if ( ! $this->donation_id || ! $to || get_post_meta( $this->donation_id, self::SENT_META, true ) ) {
			return false;
		}
		// before sending: a repeated gateway callback must not send it twice
		update_post_meta( $this->donation_id, self::SENT_META, time() );

		$this->switch_language();
		$subject = $this->get_email_subject();
		$body    = $this->get_email_body();
		$this->restore_language();

		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];

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

	/**
	 * Retry of an email wp_mail() could not send (cl_schedule_email_notification)
	 */
	public static function send_scheduled_email( $to, $subject, $body, $headers = [], $attachments = [] ) {
		if ( ! wp_mail( $to, $subject, $body, $headers, $attachments ) ) {
			write_log( 'Scheduled email failed: ' . $subject );
		}
	}

	/**
	 * Texts, labels and dates in the language the donation was made in: the
	 * gateway callbacks run in the default language.
	 */
	private function switch_language() {
		$language = get_post_meta( $this->donation_id, 'donation_language', true );
		if ( ! $language || ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return;
		}

		$this->previous_language = apply_filters( 'wpml_current_language', null );
		do_action( 'wpml_switch_language', $language );
		// ACF keeps its own copy of the language (options_{lang}_* fields)
		acf_update_setting( 'current_language', $language );

		$languages             = apply_filters( 'wpml_active_languages', null, [ 'skip_missing' => 0 ] );
		$locale                = $languages[ $language ]['default_locale'] ?? '';
		$this->switched_locale = $locale && switch_to_locale( $locale );
	}

	private function restore_language() {
		if ( $this->switched_locale ) {
			restore_previous_locale();
			$this->switched_locale = false;
		}
		if ( $this->previous_language ) {
			do_action( 'wpml_switch_language', $this->previous_language );
			acf_update_setting( 'current_language', $this->previous_language );
			$this->previous_language = null;
		}
	}

	private function get_email_subject() {
		$subject = trim( (string) get_field( 'thank_you_email_subject', 'options' ) );
		if ( $subject === '' ) {
			$subject = __( 'Thank you for your donation {ORDER_NUMBER}', 'choose-life' );
		}

		return wp_strip_all_tags( html_entity_decode( $this->add_dynamic_variables( $subject ), ENT_QUOTES, 'UTF-8' ) );
	}

	private function get_email_body() {
		$fields = Inc_Donation::get_instance()->get_donation_fields_by_id( $this->donation_id );
		$amount = self::format_amount( $fields['donation_amount']['value'] );

		$frequency = __( 'One time', 'choose-life' );
		if ( $fields['donation_type']['value'] === 'recurring' ) {
			$frequency = (int) $fields['donation_frequency']['value'] === 3 ? __( 'Every 3 months', 'choose-life' ) : __( 'Monthly', 'choose-life' );
		}

		// logged-in donors can follow their donations in My account
		$button         = null;
		$my_account_url = get_field( 'my_account_url', 'options' );
		if ( $my_account_url && get_post_field( 'post_author', $this->donation_id ) ) {
			$button = [
				'url'   => untrailingslashit( $my_account_url ) . '/#/donations',
				'label' => __( 'See your donations', 'choose-life' ),
			];
		}

		return self::render( [
			'icon'      => true,
			'preheader' => sprintf( __( 'Your donation of %1$s has been received. Reference: %2$s', 'choose-life' ), $amount, Inc_Donation::get_reference( $this->donation_id ) ),
			'title'     => __( 'Thank you!', 'choose-life' ),
			'content'   => $this->get_email_content(),
			'rows'      => [
				[ 'label' => __( 'Donation reference', 'choose-life' ), 'value' => Inc_Donation::get_reference( $this->donation_id ) ],
				[ 'label' => __( 'Date', 'choose-life' ), 'value' => $this->get_date() ],
				[ 'label' => __( 'Full name', 'choose-life' ), 'value' => trim( $fields['first_name']['value'] . ' ' . $fields['last_name']['value'] ) ],
				[ 'label' => __( 'Frequency', 'choose-life' ), 'value' => $frequency ],
				[ 'label' => __( 'Amount', 'choose-life' ), 'value' => $amount ],
			],
			'total'     => [ 'label' => __( 'Total', 'choose-life' ), 'value' => $amount ],
			'button'    => $button,
		] );
	}

	public function get_email_content() {
		$body = (string) get_field( 'thank_you_email_content', 'options' );

		if ( trim( wp_strip_all_tags( $body ) ) === '' ) {
			$body = implode( '', [
				'<p>' . __( 'Hi {FIRST_NAME},', 'choose-life' ) . '</p>',
				'<p>' . __( 'Thank you so much for your donation. Your support helps us continue our work and bring hope to the people who need it most.', 'choose-life' ) . '</p>',
				'<p>' . __( 'You will find the details of your donation below.', 'choose-life' ) . '</p>',
				'<p>' . __( 'With gratitude,', 'choose-life' ) . '<br>' . __( 'The Choose Life team', 'choose-life' ) . '</p>',
			] );
		}

		return $this->add_dynamic_variables( $body );
	}

	public function add_dynamic_variables( $text ) {

		$dynamicVariables = [
			'{ORDER_NUMBER}' => Inc_Donation::get_reference( $this->donation_id ),
			'{FIRST_NAME}'   => esc_html( get_field( 'first_name', $this->donation_id ) ),
			'{LAST_NAME}'    => esc_html( get_field( 'last_name', $this->donation_id ) ),
			'{AMOUNT}'       => self::format_amount( get_field( 'donation_amount', $this->donation_id ) ),
			'{DATE}'         => $this->get_date(),
		];

		return str_replace( array_keys( $dynamicVariables ), array_values( $dynamicVariables ), $text );
	}

	private function get_date() {
		return wp_maybe_decline_date( wp_date( 'j F Y', get_post_timestamp( $this->donation_id ) ) );
	}

	/**
	 * "150€" / "1.500€", as on the checkout pages
	 */
	public static function format_amount( $amount ) {
		return number_format( (float) $amount, 0, ',', '.' ) . '€';
	}

	/**
	 * Emails a one-time code for resetting the user's password. Only a hash of
	 * the code is kept, for RESET_EXPIRATION seconds and RESET_MAX_ATTEMPTS
	 * tries; a new code replaces the previous one.
	 *
	 * Texts: Theme Options → OTP email, with defaults when empty.
	 *
	 * @param WP_User $user
	 *
	 * @return bool
	 */
	public static function reset_password_init( WP_User $user ) {

		$otp = (string) random_int( 100000, 999999 );
		set_transient( self::RESET_TRANSIENT . $user->ID, [
			'hash'     => wp_hash_password( $otp ),
			'attempts' => 0,
			'expires'  => time() + self::RESET_EXPIRATION,
		], self::RESET_EXPIRATION );

		$subject = trim( (string) get_field( 'otp_email_subject', 'options' ) );
		if ( $subject === '' ) {
			$subject = __( 'Your one time password', 'choose-life' );
		}

		$content = (string) get_field( 'otp_email_content', 'options' );
		if ( trim( wp_strip_all_tags( $content ) ) === '' ) {
			$content = implode( '', [
				'<p>' . __( 'Use this one-time code to choose a new password for {USER_EMAIL}:', 'choose-life' ) . '</p>',
				'<p style="font-size:34px;line-height:42px;font-weight:700;letter-spacing:6px;">{OTP}</p>',
				'<p>' . __( 'The code expires in a few minutes. If you did not ask for a new password, you can ignore this email.', 'choose-life' ) . '</p>',
			] );
		}

		$dynamicVariables = [
			'{USER_EMAIL}' => esc_html( $user->user_email ),
			'{OTP}'        => $otp,
		];
		$content = str_replace( array_keys( $dynamicVariables ), array_values( $dynamicVariables ), $content );
		$subject = wp_strip_all_tags( html_entity_decode( str_replace( array_keys( $dynamicVariables ), array_values( $dynamicVariables ), $subject ), ENT_QUOTES, 'UTF-8' ) );

		$body    = self::render( [
			'title'   => __( 'Password reset', 'choose-life' ),
			'content' => $content,
		] );
		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];

		$mail_send = wp_mail( $user->user_email, $subject, $body, $headers );
		if ( ! $mail_send ) {
			self::schedule_email( $user->user_email, $subject, $body, $headers );
		}

		return $mail_send;
	}

	/**
	 * Checks a password reset code; a correct one can be used once.
	 *
	 * @param WP_User $user
	 * @param string  $otp
	 *
	 * @return true|string true, or the error message
	 */
	public static function check_reset_code( WP_User $user, $otp ) {
		$key  = self::RESET_TRANSIENT . $user->ID;
		$data = get_transient( $key );
		if ( ! is_array( $data ) || empty( $data['hash'] ) ) {
			return __( 'The One-Time Password (OTP) has expired. Please restart the password reset process.', 'choose-life' );
		}

		if ( wp_check_password( (string) $otp, $data['hash'] ) ) {
			delete_transient( $key );

			return true;
		}

		$data['attempts'] = (int) $data['attempts'] + 1;
		if ( $data['attempts'] >= self::RESET_MAX_ATTEMPTS ) {
			delete_transient( $key );

			return __( 'Too many wrong codes. Please restart the password reset process.', 'choose-life' );
		}
		set_transient( $key, $data, max( 1, (int) $data['expires'] - time() ) );

		return __( 'The One-Time Password (OTP) is not correct. Please try again.', 'choose-life' );
	}

	/**
	 * An email in the site's layout (elements/email-template/)
	 *
	 * @param array $args see elements/email-template/body.php
	 *
	 * @return string
	 */
	public static function render( $args ) {
		ob_start();

		get_template_part( 'elements/email-template/header', null, $args );
		get_template_part( 'elements/email-template/body', null, $args );
		get_template_part( 'elements/email-template/footer', null, $args );

		return ob_get_clean();
	}

}
