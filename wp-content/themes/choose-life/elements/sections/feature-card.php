<?php
/**
 * Wide card: a short text on the left, a two-line title (the second line in an
 * accent colour) with a button or a Contact Form 7 form, and a photo on the
 * right with a card behind it that tilts out on scroll.
 * Used by: home (who we are: blue, newsletter: dark)
 *
 * @var array $args text, title, title_accent, link (ACF link), form (CF7 form id),
 *                  image (attachment id), modifier (blue|dark)
 */
$args = wp_parse_args( $args ?? [], [ 'text' => '', 'title' => '', 'title_accent' => '', 'link' => null, 'form' => 0, 'image' => 0, 'modifier' => 'blue' ] );
$link = ! empty( $args['link']['url'] ) ? $args['link'] : null;
$form = $args['form'] && function_exists( 'wpcf7_contact_form' ) ? (int) $args['form'] : 0;
if ( ! $args['title'] && ! $args['text'] ) {
	return;
}
?>
<section class="feature-card feature-card--<?php echo esc_attr( $args['modifier'] ); ?>" data-reveal>
	<?php if ( $args['text'] ) : ?>
        <p class="feature-card__text"><?php echo esc_html( $args['text'] ); ?></p>
	<?php endif; ?>

    <div class="feature-card__main">
		<?php if ( $args['title'] || $args['title_accent'] ) : ?>
            <h2 class="feature-card__title">
				<?php echo esc_html( $args['title'] ); ?>
				<?php if ( $args['title_accent'] ) : ?>
                    <span class="feature-card__accent"><?php echo esc_html( $args['title_accent'] ); ?></span>
				<?php endif; ?>
            </h2>
		<?php endif; ?>

		<?php if ( $form ) : ?>
            <div class="feature-card__form">
				<?php echo do_shortcode( sprintf( '[contact-form-7 id="%d"]', $form ) ); ?>
            </div>
		<?php elseif ( $link ) : ?>
            <a href="<?php echo esc_url( $link['url'] ); ?>" class="cl-btn cl-btn--dark cl-btn--lg feature-card__button"<?php echo $link['target'] ? ' target="' . esc_attr( $link['target'] ) . '" rel="noopener"' : ''; ?>><?php echo esc_html( $link['title'] ); ?></a>
		<?php endif; ?>
    </div>

	<?php if ( $args['image'] ) : ?>
        <div class="feature-card__media" data-tilt-card data-tilt="-6">
            <span class="feature-card__media-back" data-tilt-card-back aria-hidden="true"></span>
			<?php echo wp_get_attachment_image( $args['image'], 'large', false, [ 'class' => 'feature-card__image', 'loading' => 'lazy' ] ); ?>
        </div>
	<?php endif; ?>
</section>
