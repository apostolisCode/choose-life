<?php
/**
 * Dark card: title + intro with a hand-drawn illustration, and a list of
 * pillars (icon, title, text); a red card behind it tilts out on scroll.
 *
 * @var array $args title, text, items (ACF repeater rows: icon, title, text)
 */
$args  = wp_parse_args( $args ?? [], [ 'title' => '', 'text' => '', 'items' => [] ] );
$svg   = get_template_directory_uri() . '/assets/svg/';
$items = array_filter( $args['items'], function ( $item ) {
	return ! empty( $item['title'] ) || ! empty( $item['text'] );
} );
if ( ! $items ) {
	return;
}
?>
<section class="pillars">
    <img class="pillars__rainbow" src="<?php echo esc_url( $svg . 'volunteer/rainbow.svg' ); ?>" width="197.454" height="122.541" alt="" aria-hidden="true" loading="lazy"/>
    <div class="pillars__card" data-tilt-card data-reveal>
        <span class="pillars__card-back" data-tilt-card-back aria-hidden="true"></span>

        <div class="pillars__intro">
			<?php if ( $args['title'] ) : ?>
                <h2 class="pillars__title"><?php echo esc_html( $args['title'] ); ?></h2>
			<?php endif; ?>
			<?php if ( $args['text'] ) : ?>
                <p class="pillars__text"><?php echo esc_html( $args['text'] ); ?></p>
			<?php endif; ?>
        </div>

        <ul class="pillars__list">
			<?php foreach ( $items as $item ) : ?>
                <li class="pillars__item">
                    <span class="pillars__icon">
						<?php if ( ! empty( $item['icon'] ) ) {
							echo wp_get_attachment_image( $item['icon'], 'thumbnail', false, [ 'loading' => 'lazy', 'alt' => '' ] );
						} ?>
                    </span>
                    <div class="pillars__body">
						<?php if ( ! empty( $item['title'] ) ) : ?>
                            <h3 class="pillars__item-title"><?php echo esc_html( $item['title'] ); ?></h3>
						<?php endif; ?>
						<?php if ( ! empty( $item['text'] ) ) : ?>
                            <p class="pillars__item-text"><?php echo esc_html( $item['text'] ); ?></p>
						<?php endif; ?>
                    </div>
                </li>
			<?php endforeach; ?>
        </ul>

        <img class="pillars__hand" src="<?php echo esc_url( $svg . 'home/mission-hand.svg' ); ?>" width="252.714" height="683" alt="" aria-hidden="true" loading="lazy"/>
    </div>
</section>
