<?php

defined( 'ABSPATH' ) or die();

class Inc_User {

	public $user_id = null;

	public $field_keys = [
		'first_name',
		'last_name',
		'telephone',
		'billing_address',
		'billing_city',
		'billing_postal_code',
		'billing_country',
		'email',
		'marketing_acceptance',
	];

	public function __construct( $user_id ) {

		$this->user_id = $user_id;
	}

	public function get_user_fields() {

		$user_info = get_userdata( $this->user_id );

		return [
			'first_name'           => get_field( 'first_name', 'user_' . $this->user_id ),
			'last_name'            => get_field( 'last_name', 'user_' . $this->user_id ),
			'telephone'            => get_field( 'telephone', 'user_' . $this->user_id ),
			'billing_address'      => get_field( 'billing_address', 'user_' . $this->user_id ),
			'billing_city'         => get_field( 'billing_city', 'user_' . $this->user_id ),
			'billing_postal_code'  => get_field( 'billing_postal_code', 'user_' . $this->user_id ),
			'billing_country'      => get_field( 'billing_country', 'user_' . $this->user_id ),
			// Return the checkbox-compatible string ('1'/'') instead of the ACF
			// true_false boolean, so the front-end checkbox (value="1") hydrates
			// as checked. See Account.vue / Complete.vue.
			'marketing_acceptance' => get_field( 'marketing_acceptance', 'user_' . $this->user_id ) ? '1' : '',
			'email'                => $user_info->user_email
		];
	}

	public function save_profile_fields( $fields ) {

		$old_values = $this->get_user_fields();

		if ( ! array_key_exists( 'email', $fields ) ) {
			throw new Exception( __( 'The email is required', 'choose-life' ), 400 );
		}
		if ( ! is_email( $fields['email'] ) ) {
			throw new Exception( __( 'The email is invalid', 'choose-life' ), 400 );
		}
		foreach ( $this->field_keys as $field_key ) {
			$value = esc_attr( $fields[ $field_key ] );
			if ( ! array_key_exists( $field_key, $fields ) ) {
				continue;
			}
			if ( $value == $old_values[ $field_key ] ) {
				continue;
			}
			if ( $field_key === 'email' ) {
				wp_update_user( [
					'ID'         => $this->user_id,
					'user_email' => $value
				] );
				continue;
			}
			update_user_meta( $this->user_id, $field_key, $value );
		}
	}

	public static function create_user( $email = null, $password = null ) {

		if ( ! $email || ! $password ) {
			throw new Exception( __( 'Email or Password is invalid', 'choose-life' ), 400 );
		}

		$is_valid_password = CRL_Utils::strong_password_check( $password );
		if ( ! $is_valid_password ) {
			throw new Exception( __( 'You must enter a strong password', 'choose-life' ), 400 );
		}

		$email_parts = explode( '@', $email );

		if ( username_exists( $email_parts[0] ) ) {
			$email_parts[0] = $email_parts[0] . CRL_Utils::generate_random_string( 5 );
		}
		$user_id = wp_insert_user( [
			'user_login' => $email_parts[0],
			'user_pass'  => $password,
			'user_email' => $email,
			'role'       => 'subscriber'
		] );

		if ( is_wp_error( $user_id ) ) {
			$error_code = $user_id->get_error_code();
			throw new Exception( wp_strip_all_tags( $user_id->get_error_message( $error_code ) ), 500 );
		}

		return $user_id;
	}

	public function get_user_subscriptions( $paged = 1, $per_page = 10 ) {
		$subscriptions = [];

		$args  = [
			'post_type'      => 'subscriptions',
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'paged'          => $paged,
			'author'         => $this->user_id
		];
		$query = new WP_Query( $args );

		$status_labels = [
			'active'   => [
				'class' => 'success',
				'label' => __( 'Active', 'choose-life' )
			],
			'inactive'   => [
				'class' => 'danger',
				'label' => __( 'Inactive', 'choose-life' )
			],
		];

		$total = 0;
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) : $query->the_post();

				$start_date           = get_field( 'start_date' );
				$start_date_datetime  = DateTime::createFromFormat( 'Ymd', $start_date );
				$start_date_formatted = date_i18n('F j, Y', $start_date_datetime->getTimestamp());

				$end_date           = get_field( 'end_date' );
				$end_date_datetime  = DateTime::createFromFormat( 'Ymd', $end_date );
				$end_date_formatted = date_i18n( 'j F, Y', $end_date_datetime->getTimestamp() );

				$status          = get_field( 'subscription_status' );
				$payment_cycle   = get_field( 'payment_cycle' );
				$donation_amount = get_field( 'donation_amount' );

				$status_output = sprintf( '<span class="cl-badge cl-badge--%s">%s</span>',
					$status_labels[ $status ]['class'],
					$status_labels[ $status ]['label']
				);

				$payment_cycle_output = __( 'Monthly', 'choose-life' );
				if ( $payment_cycle > 1 ) {
					$payment_cycle_output = sprintf( __( 'Every %s months', 'choose-life' ), $payment_cycle );
				}

				$subscription_data = [
					'id'            => '#'.get_the_ID(),
					'status'        => $status_output,
					'amount'        => $donation_amount . '€',
					//'start_date'    => $start_date_formatted,
					'end_date'      => $end_date_formatted,
					'payment_cycle' => $payment_cycle_output
				];
				$subscriptions[]   = $subscription_data;

			endwhile;
			$total = $query->max_num_pages;
		}
		wp_reset_query();

		return [
			'posts'        => $subscriptions,
			'current_page' => $paged,
			'total'        => $total,
		];
	}

	public function get_user_donations( $paged = 1, $per_page = 10 ) {

		$donations = [];

		$args  = [
			'post_type'      => 'donations',
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'paged'          => $paged,
			'author'         => $this->user_id
		];
		$query = new WP_Query( $args );

		$donation_class = Inc_Donation::get_instance();

		$status_labels = [
			'pending'   => [
				'class' => 'warning',
				'label' => __( 'Pending payment', 'choose-life' )
			],
			'failed'   => [
				'class' => 'danger',
				'label' => __( 'Payment failed', 'choose-life' )
			],
			'completed'   => [
				'class' => 'success',
				'label' => __( 'Paid', 'choose-life' )
			],
		];

		$total = 0;
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) : $query->the_post();

				$fields          = $donation_class->get_donation_fields_by_id( get_the_ID() );
				$donation_status = get_field( 'donation_status' );

				$status_output = sprintf( '<span class="cl-badge cl-badge--%s">%s</span>',
					$status_labels[ $donation_status ]['class'],
					$status_labels[ $donation_status ]['label']
				);

				$donation_data = [
					'id'      => '#'.get_the_ID(),
					'status'  => $status_output,
					'amount'  => $fields['donation_amount']['value'] . '€',
					'date'    => get_the_date( 'j F Y, H:i' ),
					// failed payments get a "retry", unpaid (pending) ones a "pay" button
					'actions' => $donation_status === 'failed' ? 'retry_action' : ( $donation_status !== 'completed' ? 'pay_action' : '' )
				];
				$donations[]   = $donation_data;

			endwhile;
			$total = $query->max_num_pages;
		}
		wp_reset_query();

		return [
			'posts'        => $donations,
			'current_page' => $paged,
			'total'        => $total,
		];
	}

}
