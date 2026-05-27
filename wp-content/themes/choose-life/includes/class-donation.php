<?php

defined( 'ABSPATH' ) or die();

class Inc_Donation {

	public static $instance = null;

	public static function get_instance() {
		null === self::$instance and self::$instance = new self();

		return self::$instance;
	}

	public $id;
	public $donation_key;
	public $fields = [];
	public $submission;

	public function __construct() {
		$this->fields = [
			'first_name'          => [
				'type'     => 'string',
				'required' => true,
				'label'    => __( 'First name', 'choose-life' )
			],
			'last_name'           => [
				'type'     => 'string',
				'required' => true,
				'label'    => __( 'Last name', 'choose-life' )
			],
			'telephone'           => [
				'type'     => 'string',
				'required' => true,
				'label'    => __( 'Telephone', 'choose-life' )
			],
			'billing_address'     => [
				'type'     => 'string',
				'required' => true,
				'label'    => __( 'Billing address', 'choose-life' )
			],
			'billing_city'        => [
				'type'     => 'string',
				'required' => true,
				'label'    => __( 'Billing city', 'choose-life' )
			],
			'billing_postal_code' => [
				'type'     => 'string',
				'required' => true,
				'label'    => __( 'Postal code', 'choose-life' )
			],
			'billing_country'     => [
				'type'     => 'string',
				'required' => true,
				'label'    => __( 'Billing country', 'choose-life' )
			],
			'email'               => [
				'type'     => 'string',
				'required' => true,
				'label'    => __( 'Email', 'choose-life' )
			],
			'donation_amount'     => [
				'type'     => 'int',
				'required' => true,
				'label'    => __( 'Donation amount', 'choose-life' )
			],
			'donation_type'       => [
				'type'     => 'string',
				'required' => true,
				'label'    => __( 'Donation type', 'choose-life' )
			],
			'donation_frequency'  => [
				'type'     => 'int',
				'required' => false,
				'label'    => __( 'Donation occurs every x months', 'choose-life' )
			],
			'recurring_end_date'  => [
				'type'     => 'string',
				'required' => false,
				'label'    => __( 'Donation end date', 'choose-life' )
			],
			'marketing_acceptance'  => [
				'type'     => 'boolean',
				'required' => false,
				'label'    => __( 'Marketing acceptance', 'choose-life' )
			],
		];

	}

	public function create( $submission_fields ) {
		$this->submission = $submission_fields;
		$response         = [
			'success' => true,
			'message' => '',
			'data'    => []
		];
		try {
			$this->validate_fields();
			$this->save();
			$response['data'] = $this->handle_payment();

		} catch ( Exception $e ) {
			$response['success'] = false;
			$response['message'] = $e->getMessage();
		}

		if ( ! $response['success'] ) {
			throw new Exception( $response['message'], 400 );
		}

		return $response['data'];
	}

	private function validate_fields() {

		foreach ( $this->fields as $key => $value ) {
			if ( $value['required'] && ! array_key_exists( $key, $this->submission ) ) {
				throw new Exception( sprintf( __( 'The required field %s is missing.', 'choose-life' ), $key ) );
			}
			if ( ! array_key_exists( $key, $this->submission ) ) {
				continue;
			}
			if ( isset( $this->submission[ $key ] ) && $this->submission[ $key ] === '' ) {
				throw new Exception( sprintf( __( 'The required field %s is missing.', 'choose-life' ), $key ) );
			}
			$field_value = $this->submission[ $key ];
			switch ( $key ) {
				case 'billing_country':
					$countries_list = acf()->fields->get_field_type( 'country' )->get_countries();
					if ( ! array_key_exists( $field_value, $countries_list ) ) {
						throw new Exception( sprintf( __( 'The Country Code %s is invalid.', 'choose-life' ), $field_value ) );
					}
					break;
				case 'donation_type':
					$valid_values = [ 'one-time', 'recurring' ];
					if ( ! in_array( $field_value, $valid_values ) ) {
						throw new Exception( sprintf( __( 'The Donation type %s is invalid.', 'choose-life' ), $field_value ) );
					}
					if ( $field_value == 'recurring' && ! array_key_exists( 'donation_frequency', $this->submission ) || (int) $this->submission['donation_frequency'] <= 0 ) {
						throw new Exception( sprintf( __( 'Invalid recurring frequency value.', 'choose-life' ), $field_value ) );
					}
					break;
			}
		}

	}

	private function save() {

		$data = $this->submission;

		$data_to_save = [];
		foreach ( $data as $key => $v ) {
			if ( ! array_key_exists( $key, $this->fields ) ) {
				continue;
			}
			$field_type  = $this->fields[ $key ]['type'];
			$field_value = sanitize_text_field( $v );
			switch ( $field_type ) {
				case 'boolean' :
					$field_value = (boolean) $v;
					break;
				case 'int' :
					$field_value = (int) $v;
					break;
			}
			$data_to_save[ $key ] = $field_value;
		}

		$data_to_save['donation_status'] = 'pending';

		if ( $data_to_save['donation_type'] == 'recurring' ) {
			$data_to_save['recurring_end_date'] = Inc_Payment::calculate_recurring_end_date();
		} else {
			unset( $data_to_save['donation_frequency'] );
		}

		$post_data = [
			'post_title'  => 'Payment',
			'post_type'   => 'donations',
			'post_status' => 'publish',
			'meta_input'  => $data_to_save
		];
		if ( get_current_user_id() ) {
			$post_data['post_author'] = get_current_user_id();
			$user_class               = new Inc_User( $post_data['post_author'] );
			try {
				$user_class->save_profile_fields( $data_to_save );
			} catch ( Exception $e ) {
			}
		}
		$this->id = wp_insert_post( $post_data, true );

		if ( is_wp_error( $this->id ) ) {
			throw new Exception( __( 'There was an error. Please try again', 'choose-life' ) );
		}

		$this->donation_key = $this->id . date( 'Ymdhisu' );
		update_post_meta( $this->id, 'donation_key', $this->donation_key );

		$first_name = get_field( 'first_name', $this->id );
		$last_name  = get_field( 'last_name', $this->id );

		wp_update_post( [
			'ID'         => $this->id,
			'post_title' => '#' . $this->id . ' ' . $first_name . ' ' . $last_name,
		] );
	}

	public function get_donation_fields_by_id( $id = null ) {
		if ( ! $id ) {
			return false;
		}
		$this->id = $id;

		$field_data = [];
		foreach ( $this->fields as $k => $v ) {
			$field_data[ $k ] = [
				'label' => $v['label'],
				'value' => get_field( $k, $this->id )
			];
		}

		return $field_data;
	}

	public function get_donation_by( $id, $key_lookup = false ) {
		if ( ! $id ) {
			return false;
		}

		$response = [
			'success' => false,
			'data'    => []
		];

		$args = [
			'post_type'      => 'donations',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids'
		];
		if ( $key_lookup ) {
			$args['meta_query'] = [
				[
					'key'     => 'donation_key',
					'value'   => $id,
					'compare' => '='
				]
			];
		} else {
			$args['post__in'] = [ $id ];
		}

		$query = new WP_Query( $args );

		if ( ! empty( $query->posts ) ) {
			$response['success']             = true;
			$donation_id                     = $query->posts[0];
			$response['data']                = $this->get_donation_fields_by_id( $donation_id );
			$response['data']['donation_id'] = $donation_id;
		}

		wp_reset_query();

		return $response;
	}


	private function handle_payment() {
		$payment = new Inc_Payment();

		return [
			'key'    => $this->donation_key,
			'params' => $payment->get_payment_form_params( $this->id )
		];
	}

	public function create_recurring_donation($first_donation_id, $posted_data) {

		if ( ! $first_donation_id ) {
			return false;
		}

		$author_id = get_post_field ('post_author', $first_donation_id);
		$fields = $this->get_donation_fields_by_id($first_donation_id);
		if ( ! $fields ) {
			return false;
		}

		$subscription_id = get_field('subscription_id', $first_donation_id);

		$data_to_save = [
			'donation_status' => 'pending',
			'subscription_id' => $subscription_id
		];
		foreach ( $fields as $k => $v ) {
			$data_to_save[ $k ] = $v['value'];
		}
		$data_to_save['ecommerce_request_data'] = $posted_data;

		$post_data = [
			'post_title'  => 'Payment',
			'post_type'   => 'donations',
			'post_status' => 'publish',
			'meta_input'  => $data_to_save,
		];
		if ($author_id) {
			$post_data['post_author'] = $author_id;
		}
		$post_id = wp_insert_post( $post_data, true );
		if ( is_wp_error( $post_id ) ) {
			return false;
		}

		$donation_key = $post_id . date( 'Ymdhisu' );
		update_post_meta( $post_id, 'donation_key', $donation_key );

		$first_name = get_field( 'first_name', $post_id );
		$last_name  = get_field( 'last_name', $post_id );

		wp_update_post( [
			'ID'         => $post_id,
			'post_title' => '#' . $post_id . ' ' . $first_name . ' ' . $last_name . ' - recurring',
		] );

		$subscription_payments = get_field('payments', $subscription_id);
		$subscription_payments[] = $post_id;
		update_field('payments', $subscription_payments, $subscription_id);

		return $post_id;
	}

}
