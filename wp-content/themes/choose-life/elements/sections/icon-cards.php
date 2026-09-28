<?php
/**
 * Title (+ optional text beside it) and cards with an icon on a dark square,
 * a title and a text; a card with a link is clickable as a whole.
 *
 * @var array $args title, text, cards (ACF repeater rows: icon, title, text, link)
 */
$args  = wp_parse_args( $args ?? [], [ 'title' => '', 'text' => '', 'cards' => [] ] );
$cards = array_filter( $args['cards'], function ( $card ) {
	return ! empty( $card['title'] ) || ! empty( $card['text'] );
} );
if ( ! $cards ) {
	return;
}
?>
<section class="icon-cards<?php echo $args['text'] ? ' icon-cards--split' : ''; ?>">
	<?php if ( $args['title'] || $args['text'] ) : ?>
        <header class="icon-cards__header" data-reveal>
			<?php if ( $args['title'] ) : ?>
                <h2 class="icon-cards__title"><?php echo esc_html( $args['title'] ); ?></h2>
			<?php endif; ?>
			<?php if ( $args['text'] ) : ?>
                <p class="icon-cards__text"><?php echo esc_html( $args['text'] ); ?></p>
			<?php endif; ?>
        </header>
	<?php endif; ?>
    <ul class="icon-cards__list" data-reveal-stagger>
		<?php foreach ( $cards as $card ) :
			$link = ! empty( $card['link']['url'] ) ? $card['link'] : null;
			$tag  = $link ? 'a' : 'div';
			?>
            <li class="icon-cards__item">
                <<?php echo $tag; ?> class="icon-card"<?php if ( $link ) : ?> href="<?php echo esc_url( $link['url'] ); ?>"<?php echo $link['target'] ? ' target="' . esc_attr( $link['target'] ) . '" rel="noopener"' : ''; ?><?php endif; ?>>
				<?php if ( ! empty( $card['icon'] ) ) : ?>
                    <span class="icon-card__icon">
						<?php echo wp_get_attachment_image( $card['icon'], 'thumbnail', false, [ 'loading' => 'lazy', 'alt' => '' ] ); ?>
                    </span>
				<?php endif; ?>
				<?php if ( ! empty( $card['title'] ) ) : ?>
                    <h3 class="icon-card__title"><?php echo esc_html( $card['title'] ); ?></h3>
				<?php endif; ?>
				<?php if ( ! empty( $card['text'] ) ) : ?>
                    <p class="icon-card__text"><?php echo esc_html( $card['text'] ); ?></p>
				<?php endif; ?>
                </<?php echo $tag; ?>>
            </li>
		<?php endforeach; ?>
    </ul>
</section>
