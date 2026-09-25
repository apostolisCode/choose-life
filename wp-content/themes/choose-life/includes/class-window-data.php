<?php

defined( 'ABSPATH' ) or die();

class Inc_Window_data {

	public static $instance = null;

	public static function get_instance() {
		null === self::$instance and self::$instance = new self();

		return self::$instance;
	}

	public function __construct() {

		add_action( 'wp_footer', [ $this, 'print_object' ] );

	}

	public function print_object() {

		$data = [
			'strings'        => $this->get_strings(),
			'my_account_url' => get_field( 'my_account_url', 'options' )
		];

		if ( is_page_template( 'templates/my-account.php' ) || is_page_template( 'templates/checkout.php' ) ) {
			$data['countries_list'] = acf()->fields->get_field_type( 'country' )->get_countries();
			// the session as the page was built, so the apps start without a
			// cl_me round trip (the page already carries a per-session nonce)
			$data['user'] = is_user_logged_in() ? ( new Inc_User( get_current_user_id() ) )->get_user_fields() : false;
		}

		if ( ! empty( $data ) ) {
			echo '<script>var app_config = ' . json_encode( $data ) . ';</script>';
		}

	}

	public function get_strings() {
		return [
			'login'                         => __( 'Login', 'choose-life' ),
			'email_address'                 => __( 'Email address', 'choose-life' ),
			'password'                      => __( 'Password', 'choose-life' ),
			'retype_password'               => __( 'Retype password', 'choose-life' ),
			'new_password'                  => __( 'New password', 'choose-life' ),
			'close'                         => __( 'Close', 'choose-life' ),
			'show_password'                 => __( 'Show password', 'choose-life' ),
			'hide_password'                 => __( 'Hide password', 'choose-life' ),
			'or_upper'                      => __( 'OR', 'choose-life' ),
			'login_to_account'              => __( 'Login to your Account', 'choose-life' ),
			'create_account'                => __( 'Create Account', 'choose-life' ),
			'continue_as_guest'             => __( 'Continue as Guest', 'choose-life' ),
			'required_field_message'        => __( 'Required field', 'choose-life' ),
			'required_email_field_message'  => __( 'Invalid email address', 'choose-life' ),
			'first_name'                    => __( 'First name', 'choose-life' ),
			'last_name'                     => __( 'Last name', 'choose-life' ),
			'telephone'                     => __( 'Telephone', 'choose-life' ),
			'address'                       => __( 'Address', 'choose-life' ),
			'city'                          => __( 'City', 'choose-life' ),
			'postal_code'                   => __( 'Postal code', 'choose-life' ),
			'country'                       => __( 'Country', 'choose-life' ),
			'menu_my_account'               => __( 'My Account', 'choose-life' ),
			'menu_account'                  => __( 'Account', 'choose-life' ),
			'menu_donations'                => __( 'Donations', 'choose-life' ),
			'menu_logout'                   => __( 'Logout', 'choose-life' ),
			'user_login'                    => __( 'Login to your account', 'choose-life' ),
			'user_account'                  => __( 'Create your account', 'choose-life' ),
			'register'                      => __( 'Register', 'choose-life' ),
			'invalid_same_as_password'      => __( 'Passwords do not match', 'choose-life' ),
			'invalid_password_strength'     => __( 'Your password must be at least 8 characters long and contain at least one number, one special character such as ! " ? $ % ^ & ), an upper and lower case', 'choose-life' ),
			'marketing_acceptance_text'     => __( 'I agree to the use of my personal information for future advertising messages and promotions.', 'choose-life' ),
			'back_to_homepage'              => __( 'Back to Homepage', 'choose-life' ),
			'pay_again'                     => __( 'Complete Donation', 'choose-life' ),
			'no_donation_title'             => __( 'Choose a donation first', 'choose-life' ),
			'no_active_subscriptions_found' => __( 'You have no active Subscriptions yet.', 'choose-life' ),
			'no_donations_found'            => __( 'No Donations yet.', 'choose-life' ),
			'table_reference_id'            => __( 'Reference ID', 'choose-life' ),
			'table_payment_id'              => __( 'Payment ID', 'choose-life' ),
			'table_price_amount'            => __( 'Amount', 'choose-life' ),
			'table_date'                    => __( 'Date', 'choose-life' ),
			'table_status'                  => __( 'Status', 'choose-life' ),
			'table_recurring_cycle'         => __( 'Recurring cycle', 'choose-life' ),
			'table_actions'                 => __( 'Actions', 'choose-life' ),
			'table_pay_donation'            => __( 'Pay', 'choose-life' ),
			'table_retry_payment'           => __( 'Retry payment', 'choose-life' ),
			'pagination'                    => __( 'Pagination', 'choose-life' ),
			'lost_password'                 => __( 'Lost your password?', 'choose-life' ),
			'reset_password'                => __( 'Reset password', 'choose-life' ),
			'enter_account_email'           => __( 'Enter your account email address.', 'choose-life' ),
			'form_submit'                   => __( 'Submit', 'choose-life' ),
			'or'                            => __( 'or', 'choose-life' ),
			'reset_welcome_title'           => __( 'Forgot your password?', 'choose-life' ),
			'reset_welcome_text'            => __( 'No worries. Enter your account email and we will send you a one-time code to set a new password.', 'choose-life' ),
			'reset_otp_text'                => __( 'Check your email for the one-time code, then choose your new password.', 'choose-life' ),
			'reset_tagline'                 => __( 'In a few steps you will have access to your account again.', 'choose-life' ),
			'send_code'                     => __( 'Send code', 'choose-life' ),
			'otp_code'                      => __( 'One-time code (OTP)', 'choose-life' ),
			'resend_code'                   => __( 'Didn’t get a code? Send again', 'choose-life' ),
			'save_password'                 => __( 'Save new password', 'choose-life' ),
			'back_to_login'                 => __( 'Back to login', 'choose-life' ),
			'checkout_step_amount'          => __( 'Donation', 'choose-life' ),
			'checkout_step_payment'         => __( 'Payment', 'choose-life' ),
			'checkout_step_thank_you'       => __( 'Thank you', 'choose-life' ),
			'login_welcome_title'           => __( 'Welcome!', 'choose-life' ),
			'login_welcome_text'            => __( 'Log in to complete your donation and follow its impact.', 'choose-life' ),
			'login_tagline'                 => __( 'Every login brings us one step closer to another donor.', 'choose-life' ),
			'email_placeholder'             => __( 'name@email.com', 'choose-life' ),
			'account_login_text'            => __( 'Log in to see your donations and manage your details.', 'choose-life' ),
			'account_register_text'         => __( 'Create an account to keep track of all your donations in one place.', 'choose-life' ),
			'register_welcome_title'        => __( 'Join us!', 'choose-life' ),
			'register_welcome_text'         => __( 'Create an account to complete your donation and keep track of all your donations in one place.', 'choose-life' ),
			'register_tagline'              => __( 'Every new account brings us one step closer to another donor.', 'choose-life' ),
			'donation_amounts_title'        => __( 'Support amounts:', 'choose-life' ),
			'donation_amounts_text'         => __( 'Choose the amount you wish to donate or enter a different amount.', 'choose-life' ),
			'donation_amount_label'         => __( 'Donation amount:', 'choose-life' ),
			'donation_add'                  => __( 'Add donation', 'choose-life' ),
			'donation_most_popular'         => __( 'Most popular', 'choose-life' ),
			'donation_custom_amount'        => __( 'Enter amount', 'choose-life' ),
			'donation_invalid_amount'       => __( 'Please enter a valid amount.', 'choose-life' ),
			/* translators: %s: formatted amount, e.g. 5 € */
			'donation_min_amount'           => __( 'The minimum donation amount is %s.', 'choose-life' ),
			/* translators: %s: formatted amount, e.g. 9.999 € */
			'donation_max_amount'           => __( 'The maximum donation amount is %s.', 'choose-life' ),
			'previous'                      => __( 'Previous', 'choose-life' ),
			'next'                          => __( 'Next', 'choose-life' ),
		];
	}

}
