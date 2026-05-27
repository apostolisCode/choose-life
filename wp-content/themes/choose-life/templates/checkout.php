<?php
/**
 * Template Name: Checkout
 */

get_header();

$page_content = [
	"start"    => [
		"title"   => get_field( 'login_register_title' ),
		"content" => get_field( 'login_register_content' ),
	],
	'complete' => [
		"title"                     => get_field( 'complete_donation_title' ),
		"content"                   => get_field( 'complete_donation_content' ),
		'personal_info'             => __( 'Personal details', 'choose-life' ),
		'billing_info'              => __( 'Billing details', 'choose-life' ),
		'donations_amount'          => __( 'Donation amount', 'choose-life' ),
		'donations_type'            => __( 'Donation type', 'choose-life' ),
		'one_time_pay'              => __( 'One time payment', 'choose-life' ),
		'recurring_pay'             => __( 'Recurring payment', 'choose-life' ),
		'recurring_freq_1'          => __( 'Monthly', 'choose-life' ),
		'recurring_freq_2'          => __( 'Every 3 months', 'choose-life' ),
		'terms_acceptance'          => __( 'Terms acceptance', 'choose-life' ),
		'terms_acceptance_text'     => __( 'I have read and accept the donation terms and policy.', 'choose-life' ),
		'donation'                  => __( 'Donation', 'choose-life' ),
		'total'                     => __( 'Total', 'choose-life' ),
		'complete_order'            => __( 'Proceed to payment', 'choose-life' ),
		'accept_terms_error'        => __( 'You have to accept the donation terms and policy', 'choose-life' ),
		'processing_data_text'      => __( 'To find out about the processing of your personal data, click <a href="%s" title="" target="_blank">here</a>.', 'choose-life' ),
	]
];
?>
    <div id="checkout-page"></div>
    <script>
        var page_content = <?php echo json_encode( $page_content ); ?>;
    </script>
<?php
get_footer();