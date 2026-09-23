<?php
/**
 * Template Name: My Account
 */

get_header();

$page_content = [
	"login"      => [
		"title"   => get_field( 'my_account_title' ),
		"content" => get_field( 'my_account_content' )
	],
	"register"   => [
		"title"   => get_field( 'register_title' ),
		"content" => get_field( 'register_content' )
	],
	"my_account" => [
		"my_account"    => __( 'My Account', 'choose-life' ),
		"save_changes"  => __( 'Save changes', 'choose-life' ),
		"personal_info" => __( 'Personal information', 'choose-life' ),
		"address_info"  => __( 'Address information', 'choose-life' ),
	],
	"donations"  => [
		"recurring_donations_title"   => get_field( 'recurring_donations_title' ),
		"recurring_donations_content" => get_field( 'recurring_donations_content' ),
		"completed_donations_title"   => get_field( 'completed_donations_title' ),
		"completed_donations_content" => get_field( 'completed_donations_content' ),
		"donations"                   => __( 'Donations', 'choose-life' ),
		"amount"                      => __( 'Amount', 'choose-life' ),
		"status"                      => __( 'Status', 'choose-life' ),
		"trans_date"                  => __( 'Transaction date', 'choose-life' ),
		"recurring_freq"              => __( 'Recurring frequency', 'choose-life' ),
		"actions"                     => __( 'Actions', 'choose-life' ),
	]
];
?>
    <div id="my-account-page"></div>
    <script>
        var page_content = <?php echo json_encode( $page_content ); ?>;
    </script>
<?php
get_footer();