<?php

defined( 'ABSPATH' ) or die();

class Inc_Admin {

	public static $instance = null;

	public static function get_instance() {
		null === self::$instance and self::$instance = new self();

		return self::$instance;
	}

	public function __construct() {
		if ( is_admin() ) {
			add_filter( 'manage_donations_posts_columns', [ $this, 'set_custom_donations_columns' ] );
			add_action( 'manage_donations_posts_custom_column', [ $this, 'custom_donations_column' ], 10, 2 );

			add_filter( 'manage_subscriptions_posts_columns', [ $this, 'set_custom_subscriptions_columns' ] );
			add_action( 'manage_subscriptions_posts_custom_column', [ $this, 'custom_subscriptions_column' ], 10, 2 );

			add_filter( 'manage_edit-donations_sortable_columns', [ $this, 'set_custom_donations_sortable_columns' ] );
			add_action( 'pre_get_posts', [ $this, 'orders_custom_orderby' ] );

			add_action( 'restrict_manage_posts', [ $this, 'add_extra_tablenav_donation_status' ] );
			add_filter( 'parse_query', [ $this, 'prefix_parse_filter' ] );
		}
	}

	public function set_custom_donations_columns( $columns ) {

		$date = $columns['date'];
		unset( $columns['date'] );

		$columns['order_user'] = 'Email';
		$columns['donation_status'] = 'Status';
		$columns['donation_type'] = 'Type';
		$columns['donation_amount'] = 'Amount';
		$columns['donor_list_display'] = 'Donors list';

		$columns['date'] = $date;

		return $columns;
	}

	public function set_custom_subscriptions_columns( $columns ) {

		$date = $columns['date'];
		unset( $columns['date'] );

		$columns['order_user'] = 'Email';
		$columns['donation_status'] = 'Status';
		$columns['donation_amount'] = 'Amount';

		$columns['date'] = $date;

		return $columns;
	}

	public function custom_donations_column( $column, $post_id ) {
		switch ( $column ) {
			case 'donation_amount' :
				$donation_amount = get_field('donation_amount', $post_id);
				echo $donation_amount . ' €';
				break;
			case 'donation_status' :
				$donation_statuses = [
					'completed' => 'Payment completed',
					'pending' => 'Pending payment',
					'failed' => 'Payment failed'
				];
				$donation_status = get_field('donation_status', $post_id);
				echo '<span class="donation-status ' . $donation_status . '">' . $donation_statuses[ $donation_status ] . '</span>';
				break;
			case 'donation_type' :
				$donation_types = [
					'one-time' => 'One time payment',
					'recurring' => 'Recurring payment',
				];
				$donation_type = get_field('donation_type', $post_id);
				echo '<span class="donation-type ' . $donation_type . '">' . $donation_types[ $donation_type ] . '</span>';
				break;
			case 'donor_list_display' :
				$display = get_field( 'donor_list_display', $post_id );
				if ( $display === 'name' ) {
					echo esc_html( trim( get_field( 'first_name', $post_id ) . ' ' . get_field( 'last_name', $post_id ) ) );
				} elseif ( $display === 'other' ) {
					echo esc_html( get_field( 'donor_list_name', $post_id ) ) . ' <em>(other name)</em>';
				} else {
					echo '<em>Anonymous</em>';
				}
				break;
			case 'order_user' :
				$author_id    = get_post_field( 'post_author', $post_id );
				if ($author_id) {
					$email = get_the_author_meta( 'email', $author_id );
					$edit_link    = add_query_arg( 'user_id', $author_id, self_admin_url( 'user-edit.php' ) );
					echo sprintf( '<a href="%s" target="_blank">%s</a>', $edit_link, $email );
				} else {
					$email = get_field('email', $post_id );
					if ($email) {
						echo $email;
					}
				}
				break;
		}
	}

	public function custom_subscriptions_column( $column, $post_id ) {
		switch ( $column ) {
			case 'donation_amount' :
				$donation_amount = get_field('donation_amount', $post_id);
				echo $donation_amount . ' €';
				break;
			case 'donation_status' :
				$subscription_statuses = [
					'active' => 'Active',
					'inactive' => 'Inactive',
				];
				$subscription_status = get_field('subscription_status', $post_id);
				echo '<span class="subscription-status ' . $subscription_status . '">' . $subscription_statuses[ $subscription_status ] . '</span>';
				break;
			case 'order_user' :
				$author_id    = get_post_field( 'post_author', $post_id );
				if ($author_id) {
					$email = get_the_author_meta( 'email', $author_id );
					$edit_link    = add_query_arg( 'user_id', $author_id, self_admin_url( 'user-edit.php' ) );
					echo sprintf( '<a href="%s" target="_blank">%s</a>', $edit_link, $email );
				} else {
					$email = get_field('email', $post_id );
					if ($email) {
						echo $email;
					}
				}
				break;
		}
	}

	public function set_custom_donations_sortable_columns( $columns ) {
		$columns['donation_status'] = 'donation_status';

		return $columns;
	}

	public function orders_custom_orderby( $query ) {
		if ( ! is_admin() ) {
			return;
		}

		$orderby = $query->get( 'orderby' );

		if ( 'donation_status' == $orderby ) {
			$query->set( 'meta_key', 'donation_status' );
			$query->set( 'orderby', 'meta_value' );
		}
	}

	public function add_extra_tablenav_donation_status( $post_type ) {

		global $wpdb;

		/** Ensure this is the correct Post Type*/
		if ( $post_type !== 'donations' ) {
			return;
		}

		/** Grab the results from the DB */
		$query   = $wpdb->prepare( '
        SELECT DISTINCT pm.meta_value FROM %1$s pm
        LEFT JOIN %2$s p ON p.ID = pm.post_id
        WHERE pm.meta_key = "%3$s" 
        AND p.post_status = "%4$s" 
        AND p.post_type = "%5$s"
        ORDER BY "%6$s"',
			$wpdb->postmeta,
			$wpdb->posts,
			'donation_status', // Your meta key - change as required
			'publish',          // Post status - change as required
			$post_type,
			'donation_status'
		);
		$results = $wpdb->get_col( $query );

		/** Ensure there are options to show */
		if ( empty( $results ) ) {
			return;
		}

		// get selected option if there is one selected
		if ( isset( $_GET['donation_status'] ) && $_GET['donation_status'] != '' ) {
			$selectedName = $_GET['donation_status'];
		} else {
			$selectedName = - 1;
		}

		/** Grab all of the options that should be shown */
		$options[] = sprintf( '<option value="">%1$s</option>', 'All Statuses' );
		$statuses = [
			'failed' => 'Payment Failed',
			'completed' => 'Payment Completed',
			'pending' => 'Pending payment',
		];
		foreach ( $results as $result ) :
			$label = $statuses[ $result ];
			if ( $result == $selectedName ) {
				$options[] = sprintf( '<option value="%1$s" selected>%2$s</option>', esc_attr( $result ), $label );
			} else {
				$options[] = sprintf( '<option value="%1$s">%2$s</option>', esc_attr( $result ), $label );
			}
		endforeach;

		/** Output the dropdown menu */
		echo '<select class="" id="donation_status" name="donation_status">';
		echo join( "\n", $options );
		echo '</select>';

	}

	public function prefix_parse_filter( $query ) {
		global $pagenow;

		$current_page = isset( $_GET['post_type'] ) ? $_GET['post_type'] : '';

		if ( is_admin() && 'donations' == $current_page && 'edit.php' == $pagenow ) {
			$filters    = [
				'donation_status',
			];
			$meta_query = [];
			foreach ( $filters as $filter_key ) {
				if ( isset( $_GET[ $filter_key ] ) && $_GET[ $filter_key ] != '' ) {
					$value        = $_GET[ $filter_key ];
					$meta_query[] = [
						'key'     => $filter_key,
						'value'   => $value,
						'compare' => '='
					];
				}
			}
			if ( count( $meta_query ) > 1 ) {
				$meta_query['relation'] = 'AND';
			}
			if ( ! empty( $meta_query ) ) {
				$query->query_vars['meta_query'] = $meta_query;
			}
		}
	}
}
