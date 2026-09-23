<?php
/**
 * "Our mission": title + intro, and staggered cards (image, title, text)
 *
 * @var array $args title, text, cards (ACF repeater rows: image, title, text, link)
 */
$args  = wp_parse_args( $args ?? [], [ 'title' => '', 'text' => '', 'cards' => [] ] );
$wave  = get_template_directory_uri() . '/assets/svg/donation/card-wave.svg';
$cards = array_filter( $args['cards'], function ( $card ) {
	return ! empty( $card['title'] ) || ! empty( $card['image'] );
} );
if ( ! $args['title'] && ! $cards ) {
	return;
}
?>
<section class="mission">
    <div class="mission__inner">
        <header class="mission__header" data-reveal>
			<?php if ( $args['title'] ) : ?>
                <h2 class="mission__title"><?php echo esc_html( $args['title'] ); ?></h2>
			<?php endif; ?>
			<?php if ( $args['text'] ) : ?>
                <p class="mission__text"><?php echo esc_html( $args['text'] ); ?></p>
			<?php endif; ?>
        </header>

		<?php if ( $cards ) : ?>
            <ul class="mission__cards" data-reveal-stagger>
				<?php foreach ( $cards as $card ) :
					$link = ! empty( $card['link']['url'] ) ? $card['link'] : null;
					$tag  = $link ? 'a' : 'div';
					?>
                    <li class="mission__item">
                        <<?php echo $tag; ?> class="mission-card"<?php if ( $link ) : ?> href="<?php echo esc_url( $link['url'] ); ?>"<?php echo $link['target'] ? ' target="' . esc_attr( $link['target'] ) . '" rel="noopener"' : ''; ?><?php endif; ?>>
						<?php if ( ! empty( $card['image'] ) ) : ?>
                            <div class="mission-card__media">
								<?php echo wp_get_attachment_image( $card['image'], 'large', false, [ 'class' => 'mission-card__image', 'loading' => 'lazy' ] ); ?>
                                <img src="<?php echo esc_url( $wave ); ?>" width="600" height="11.2066" alt="" class="mission-card__wave"/>
                            </div>
						<?php endif; ?>
                        <div class="mission-card__body">
							<?php if ( ! empty( $card['title'] ) ) : ?>
                                <h3 class="mission-card__title"><?php echo esc_html( $card['title'] ); ?></h3>
							<?php endif; ?>
							<?php if ( ! empty( $card['text'] ) ) : ?>
                                <p class="mission-card__text"><?php echo esc_html( $card['text'] ); ?></p>
							<?php endif; ?>
                        </div>
                        </<?php echo $tag; ?>>
                    </li>
				<?php endforeach; ?>
            </ul>
		<?php endif; ?>
    </div>
</section>
