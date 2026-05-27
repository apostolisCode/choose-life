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
			'lost_password'                 => __( 'Lost your password?', 'choose-life' ),
			'reset_password'                => __( 'Reset password', 'choose-life' ),
			'enter_account_email'           => __( 'Enter your account email address.', 'choose-life' ),
			'form_submit'                   => __( 'Submit', 'choose-life' ),
		];
	}

}
