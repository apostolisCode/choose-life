<?php
/**
 * Violet band: title, key figures (counting up when they come into view,
 * animations/count-up.js) and a button, with hand-drawn doodles on the sides;
 * a red band behind it tilts out on scroll.
 *
 * @var array $args title (line breaks kept), stats (ACF repeater rows: value, label), link
 */
$args  = wp_parse_args( $args ?? [], [ 'title' => '', 'stats' => [], 'link' => null ] );
$svg   = get_template_directory_uri() . '/assets/svg/home/';
$link  = ! empty( $args['link']['url'] ) ? $args['link'] : null;
$stats = array_values( array_filter( $args['stats'], function ( $stat ) {
	return isset( $stat['value'] ) && $stat['value'] !== '';
} ) );
if ( ! $args['title'] && ! $stats ) {
	return;
}
?>
<section class="impact" data-tilt-card data-tilt="-1.5">
    <span class="impact__back" data-tilt-card-back aria-hidden="true"></span>

    <img src="<?php echo esc_url( $svg . 'doodle-hand-heart.svg' ); ?>" width="368.471" height="200" alt="" class="impact__doodle impact__doodle--start" aria-hidden="true" loading="lazy"/>
    <div class="impact__doodle impact__doodle--end" aria-hidden="true">
        <img src="<?php echo esc_url( $svg . 'doodle-heart.svg' ); ?>" width="77.5177" height="83.2467" alt="" class="impact__doodle-heart" loading="lazy"/>
        <img src="<?php echo esc_url( $svg . 'doodle-hand-box.svg' ); ?>" width="362.6" height="200.105" alt="" class="impact__doodle-box" loading="lazy"/>
    </div>

    <div class="impact__inner" data-reveal>
		<?php if ( $args['title'] ) : ?>
            <h2 class="impact__title"><?php echo nl2br( esc_html( $args['title'] ) ); ?></h2>
		<?php endif; ?>

		<?php if ( $stats ) : ?>
            <div class="impact__stats">
				<?php foreach ( $stats as $i => $stat ) : ?>
					<?php if ( $i ) : ?>
                        <img src="<?php echo esc_url( $svg . 'kpi-divider.svg' ); ?>" width="152.5" height="3.49982" alt="" class="impact__divider" aria-hidden="true"/>
					<?php endif; ?>
                    <div class="impact__stat">
                        <p class="impact__value" data-count-up><?php echo esc_html( $stat['value'] ); ?></p>
						<?php if ( ! empty( $stat['label'] ) ) : ?>
                            <p class="impact__label"><?php echo esc_html( $stat['label'] ); ?></p>
						<?php endif; ?>
                    </div>
				<?php endforeach; ?>
            </div>
		<?php endif; ?>

		<?php if ( $link ) : ?>
            <a href="<?php echo esc_url( $link['url'] ); ?>" class="cl-btn cl-btn--dark cl-btn--lg impact__button"<?php echo $link['target'] ? ' target="' . esc_attr( $link['target'] ) . '" rel="noopener"' : ''; ?>><?php echo esc_html( $link['title'] ); ?></a>
		<?php endif; ?>
    </div>
</section>
