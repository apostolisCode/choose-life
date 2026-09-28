<?php
/**
 * Template Name: About
 *
 * Hero, the story and the dream of Choose Life, the mission and the team.
 */

get_header();
?>
    <div class="about-page">
		<?php
		get_template_part( 'elements/sections/about-hero', null, [
			'title' => get_field( 'hero_title' ) ?: get_the_title(),
			'text'  => get_field( 'hero_text' ),
			'image' => get_field( 'hero_image' ),
		] );

		get_template_part( 'elements/sections/about-story', null, [
			'title'       => get_field( 'story_title' ),
			'text'        => get_field( 'story_text' ),
			'dream_label' => get_field( 'dream_label' ),
			'dream_text'  => get_field( 'dream_text' ),
		] );

		get_template_part( 'elements/sections/icon-cards', null, [
			'title' => get_field( 'mission_title' ),
			'cards' => get_field( 'mission_cards' ) ?: [],
		] );

		get_template_part( 'elements/sections/about-team', null, [
			'title'   => get_field( 'team_title' ),
			'members' => get_field( 'team_members' ) ?: [],
		] );

		// content from Theme Options → Instagram feed (shared by all templates)
		get_template_part( 'elements/sections/instagram-feed' );
		?>
    </div>
<?php
get_footer();
