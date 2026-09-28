<?php
/**
 * Template Name: Homepage
 *
 * Hero (+ who we are), mission, how to help, impact figures, supporters,
 * donation steps, FAQ, Instagram feed and newsletter.
 */

get_header();

$band_words = array_values( array_filter( wp_list_pluck( get_field( 'band_words' ) ?: [], 'word' ) ) );
?>
    <div class="home-page">
		<?php
		get_template_part( 'elements/sections/home-hero', null, [
			'title' => get_field( 'hero_title' ) ?: get_the_title(),
			'text'  => get_field( 'hero_text' ),
			'image' => get_field( 'hero_image' ),
			'about' => [
				'text'         => get_field( 'about_text' ),
				'title'        => get_field( 'about_title' ),
				'title_accent' => get_field( 'about_title_accent' ),
				'link'         => get_field( 'about_link' ),
				'image'        => get_field( 'about_image' ),
			],
		] );

		get_template_part( 'elements/sections/pillars', null, [
			'title' => get_field( 'pillars_title' ),
			'text'  => get_field( 'pillars_text' ),
			'items' => get_field( 'pillars' ) ?: [],
		] );

		get_template_part( 'elements/sections/icon-cards', null, [
			'title' => get_field( 'help_title' ),
			'text'  => get_field( 'help_text' ),
			'cards' => get_field( 'help_cards' ) ?: [],
		] );

		get_template_part( 'elements/sections/words-bands', null, [
			'words'    => $band_words,
			'modifier' => 'apart',
		] );

		get_template_part( 'elements/sections/impact', null, [
			'title' => get_field( 'impact_title' ),
			'stats' => get_field( 'impact_stats' ) ?: [],
			'link'  => get_field( 'impact_link' ),
		] );

		get_template_part( 'elements/sections/supporters', null, [
			'title' => get_field( 'supporters_title' ),
			'logos' => get_field( 'supporters' ) ?: [],
		] );

		get_template_part( 'elements/sections/mission', null, [
			'title'    => get_field( 'steps_title' ),
			'text'     => get_field( 'steps_text' ),
			'cards'    => get_field( 'steps' ) ?: [],
			'modifier' => 'steps',
		] );

		get_template_part( 'elements/sections/faq', null, [
			'title'     => get_field( 'faq_title' ),
			'questions' => get_field( 'faq_questions' ) ?: [],
			'link'      => get_field( 'faq_link' ),
		] );

		// content from Theme Options → Instagram feed / Newsletter (shared by all templates)
		get_template_part( 'elements/sections/instagram-feed' );
		get_template_part( 'elements/sections/newsletter' );

		// the footer's "support our work" bar, floating at the bottom of the screen
		get_template_part( 'elements/parts/sticky-cta' );
		?>
    </div>
<?php
get_footer();
