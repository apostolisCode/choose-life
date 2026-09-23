<?php
/**
 * Template Name: Volunteer donor
 *
 * The journeys of hope on a globe, the registration steps and the guide video.
 */

get_header();

$journeys = Inc_Journeys::get_all();
$intro    = str_replace( '%count%', number_format_i18n( count( $journeys ) ), (string) get_field( 'intro' ) );
?>
    <div class="volunteer-page">
        <header class="volunteer-page__header">
            <h1 class="volunteer-page__title"><?php the_title(); ?></h1>
			<?php if ( $intro ) : ?>
                <p class="volunteer-page__intro"><?php echo esc_html( $intro ); ?></p>
			<?php endif; ?>
        </header>

		<?php
		get_template_part( 'elements/sections/journeys-globe', null, [
			'journeys' => $journeys,
		] );

		get_template_part( 'elements/sections/volunteer-steps', null, [
			'title'        => get_field( 'steps_title' ),
			'subtitle'     => get_field( 'steps_subtitle' ),
			'steps'        => get_field( 'steps' ) ?: [],
			'cta'          => get_field( 'steps_cta' ),
			'cta_note'     => get_field( 'steps_cta_note' ),
			'contact_text' => get_field( 'steps_contact_text' ),
			'contact_link' => get_field( 'steps_contact_link' ),
		] );

		get_template_part( 'elements/sections/guide-video', null, [
			'title'  => get_field( 'video_title' ),
			'video'  => get_field( 'video_file' ),
			'poster' => get_field( 'video_poster' ),
		] );

		// content from Theme Options → Instagram feed (shared by all templates)
		get_template_part( 'elements/sections/instagram-feed' );
		?>
    </div>
<?php
get_footer();
