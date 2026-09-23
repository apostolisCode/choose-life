<?php
/**
 * Template Name: Donation
 */

get_header();

$band_words = array_values( array_filter( wp_list_pluck( get_field( 'band_words' ) ?: [], 'word' ) ) );
if ( empty( $band_words ) ) {
	$band_words = [ 'ΔΩΣΕ ΕΛΠΙΔΑ', 'ΧΑΡΙΣΕ ΖΩΗ', 'ΓΙΝΕ ΔΟΤΗΣ' ];
}
?>
    <div class="donation-page">
		<?php
		get_template_part( 'elements/sections/donation-hero', null, [
			'title' => get_field( 'hero_title' ) ?: get_the_title(),
			'text'  => get_field( 'hero_text' ),
			'image' => get_field( 'hero_image' ),
		] );

		get_template_part( 'elements/sections/words-bands', null, [
			'words' => $band_words,
		] );

		get_template_part( 'elements/sections/donation-support', null, [
			'bank_accounts' => get_field( 'bank_accounts' ) ?: [],
		] );

		get_template_part( 'elements/sections/mission', null, [
			'title' => get_field( 'mission_title' ),
			'text'  => get_field( 'mission_text' ),
			'cards' => get_field( 'mission_cards' ) ?: [],
		] );

		get_template_part( 'elements/sections/faq', null, [
			'title'     => get_field( 'faq_title' ),
			'questions' => get_field( 'faq_questions' ) ?: [],
			'link'      => get_field( 'faq_link' ),
		] );

		// content from Theme Options → Instagram feed (shared by all templates)
		get_template_part( 'elements/sections/instagram-feed' );
		?>
    </div>
    <script>
        var donation_page = <?php echo wp_json_encode( [
			'amounts'      => Inc_Donation::get_amount_options(),
			'limits'       => Inc_Donation::get_amount_limits(),
			'checkout_url' => get_field( 'checkout_page_url', 'options' ),
		] ); ?>;
    </script>
<?php
get_footer();
