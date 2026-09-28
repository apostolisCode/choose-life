<?php
/**
 * Template Name: Institute
 *
 * Intro with a photo, a 360° tour (iframe), video cards and the Instagram feed.
 */

get_header();
?>
    <div class="institute-page">
		<?php
		get_template_part( 'elements/sections/donation-hero', null, [
			'title'          => get_field( 'intro_title' ) ?: get_the_title(),
			'text'           => get_field( 'intro_text' ),
			'image'          => get_field( 'intro_image' ),
			'image_position' => get_field( 'intro_image_position' ),
			'modifier'       => 'institute',
		] );

		get_template_part( 'elements/sections/tour-360', null, [
			'title' => get_field( 'tour_title' ),
			'url'   => get_field( 'tour_url' ),
			'image' => get_field( 'tour_image' ),
		] );

		get_template_part( 'elements/sections/video-cards', null, [
			'title'  => get_field( 'videos_title' ),
			'videos' => get_field( 'videos' ) ?: [],
		] );

		// content from Theme Options → Instagram feed (shared by all templates)
		get_template_part( 'elements/sections/instagram-feed' );
		?>
    </div>
<?php
get_footer();
