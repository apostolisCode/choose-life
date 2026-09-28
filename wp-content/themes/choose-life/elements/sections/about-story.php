<?php
/**
 * About page story (title + text) and "the dream" on a tilted violet card
 *
 * @var array $args title, text (html), dream_label, dream_text (html)
 */
$args    = wp_parse_args( $args ?? [], [ 'title' => '', 'text' => '', 'dream_label' => '', 'dream_text' => '' ] );
$rainbow = get_template_directory_uri() . '/assets/svg/about/rainbow.svg';
if ( ! $args['title'] && ! $args['text'] && ! $args['dream_text'] ) {
	return;
}
?>
<section class="about-story">
	<?php if ( $args['title'] || $args['text'] ) : ?>
        <div class="about-story__inner" data-reveal>
			<?php if ( $args['title'] ) : ?>
                <h2 class="about-story__title"><?php echo esc_html( $args['title'] ); ?></h2>
			<?php endif; ?>
			<?php if ( $args['text'] ) : ?>
                <div class="about-story__text"><?php echo wp_kses_post( $args['text'] ); ?></div>
			<?php endif; ?>
        </div>
	<?php endif; ?>

	<?php if ( $args['dream_text'] ) : ?>
        <div class="about-dream" data-reveal>
            <span class="about-dream__bg" data-tilt-card data-tilt="-4" aria-hidden="true"><span class="about-dream__bg-back" data-tilt-card-back></span></span>
            <span class="about-dream__rainbow" aria-hidden="true">
                <span><img src="<?php echo esc_url( $rainbow ); ?>" width="218.245" height="128.441" alt="" loading="lazy"/></span>
            </span>
			<?php if ( $args['dream_label'] ) : ?>
                <h2 class="about-dream__label"><?php echo esc_html( $args['dream_label'] ); ?></h2>
			<?php endif; ?>
            <div class="about-dream__text"><?php echo wp_kses_post( $args['dream_text'] ); ?></div>
        </div>
	<?php endif; ?>
</section>
