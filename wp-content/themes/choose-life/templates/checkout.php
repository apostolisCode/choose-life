<?php
/**
 * Template Name: Checkout
 */

get_header();

$page_content = [
	"donation_amounts" => Inc_Donation::get_amount_options(),
	"donation_limits"  => Inc_Donation::get_amount_limits(),
	"start"    => [
		"title"   => get_field( 'login_register_title' ),
		"content" => get_field( 'login_register_content' ),
	],
	"register" => [
		"title"   => get_field( 'register_title' ),
		"content" => get_field( 'register_content' ),
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
		'back'                      => __( 'Back', 'choose-life' ),
		'your_donation'             => __( 'Your donation', 'choose-life' ),
		'change_amount'             => __( 'Change amount', 'choose-life' ),
		'donation_frequency'        => __( 'Donation frequency', 'choose-life' ),
		'donor_list_title'          => __( 'Donors list display', 'choose-life' ),
		'donor_list_anonymous'      => __( 'Show me as anonymous in the donors list', 'choose-life' ),
		'donor_list_name'           => __( 'I want my name to appear in the donors list', 'choose-life' ),
		'donor_list_other'          => __( 'I want a different name to appear in the donors list', 'choose-life' ),
		'donor_list_other_label'    => __( 'Name to show in the donors list', 'choose-life' ),
		'donor_list_max_length'     => Inc_Donation::DONOR_LIST_NAME_MAX_LENGTH,
	],
	'payment'  => [
		'reference'        => __( 'Donation reference', 'choose-life' ),
		'amount'           => __( 'Amount', 'choose-life' ),
		'frequency'        => __( 'Frequency', 'choose-life' ),
		'frequency_once'   => __( 'One time', 'choose-life' ),
		'frequency_1'      => __( 'Monthly', 'choose-life' ),
		'frequency_3'      => __( 'Every 3 months', 'choose-life' ),
		'total'            => __( 'Total', 'choose-life' ),
		'back_to_homepage' => __( 'Back to Homepage', 'choose-life' ),
		'try_again'        => __( 'Try again', 'choose-life' ),
		'donors_list'      => __( 'Donors list', 'choose-life' ),
	]
];
?>
    <div id="checkout-page"></div>
    <script>
        var page_content = <?php echo json_encode( $page_content ); ?>;
    </script>
<?php
get_footer();