<?php
/**
 * Template Name: Contact
 *
 * Intro + Contact Form 7 form (fields in elements/cf7-contact.php).
 */

get_header();

$intro   = get_field( 'contact_intro' );
$form_id = get_field( 'contact_form' );
?>
    <div class="contact-page">
        <header class="contact-page__header">
            <h1 class="contact-page__title"><?php the_title(); ?></h1>
			<?php if ( $intro ) : ?>
                <p class="contact-page__intro"><?php echo nl2br( esc_html( $intro ) ); ?></p>
			<?php endif; ?>
        </header>

		<?php if ( $form_id && function_exists( 'wpcf7_contact_form' ) ) : ?>
            <section class="contact-page__card" data-contact-card>
                <span class="contact-page__card-back" aria-hidden="true"></span>
                <div class="contact-page__form">
					<?php echo do_shortcode( sprintf( '[contact-form-7 id="%d"]', (int) $form_id ) ); ?>
                </div>
            </section>
		<?php endif; ?>

		<?php
		// content from Theme Options → Instagram feed (shared by all templates)
		get_template_part( 'elements/sections/instagram-feed' );
		?>
    </div>
<?php
get_footer();
