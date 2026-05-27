<?php

defined( 'ABSPATH' ) or die();

class Inc_Subscription {

	public static $instance = null;

	public static function get_instance() {
		null === self::$instance and self::$instance = new self();

		return self::$instance;
	}

	public $id;

	public function __construct() {}

	public static function create( $donation_id = null ) {

		if ( ! $donation_id ) {
			throw new Exception( __( 'No donation id provided', 'choose-life' ) );
		}

		$donation_class  = Inc_Donation::get_instance();
		$donation_fields = $donation_class->get_donation_fields_by_id( $donation_id );

		$today      = new DateTime();
		$start_date = $today->format( 'Ymd' );

		$data_to_save    = [
			'start_date'          => $start_date,
			'end_date'            => $donation_fields['recurring_end_date']['value'],
			'payment_cycle'       => $donation_fields['donation_frequency']['value'],
			'donation_amount'     => $donation_fields['donation_amount']['value'],
			'subscription_status' => 'active',
			'payments'            => [ $donation_id ],
		];
		$post_data       = [
			'post_title'  => 'Subscription',
			'post_type'   => 'subscriptions',
			'post_status' => 'publish',
			'meta_input'  => $data_to_save,
			'post_author' => get_post_field( 'post_author', $donation_id )
		];
		$subscription_id = wp_insert_post( $post_data, true );
		if ( is_wp_error( $subscription_id ) ) {
			throw new Exception( __( 'There was an error. Please try again', 'choose-life' ) );
		}

		$first_name = get_field( 'first_name', $donation_id );
		$last_name  = get_field( 'last_name', $donation_id );

		wp_update_post( [
			'ID'         => $subscription_id,
			'post_title' => '#' . $subscription_id . ' ' . $first_name . ' ' . $last_name,
		] );

		update_field( 'subscription_id', $subscription_id, $donation_id );
	}

}
