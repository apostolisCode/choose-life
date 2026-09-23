<?php
/**
 * Instagram feed (Theme Options → Instagram feed): title, a row of photo cards
 * and a "follow" badge. Include it on any template with
 * get_template_part( 'elements/sections/instagram-feed' ).
 */
$title        = get_field( 'instagram_title', 'options' );
$title_accent = get_field( 'instagram_title_accent', 'options' );
$link         = get_field( 'instagram_link', 'options' );
$link         = ! empty( $link['url'] ) ? $link : null;
$cards        = array_filter( get_field( 'instagram_cards', 'options' ) ?: [], function ( $card ) {
	return ! empty( $card['image'] );
} );
if ( ! $cards ) {
	return;
}
?>
<section class="instagram-feed">
	<?php if ( $title || $title_accent ) : ?>
        <h2 class="instagram-feed__title" data-reveal>
			<?php echo esc_html( $title ); ?>
			<?php if ( $title_accent ) : ?>
                <span><?php echo esc_html( $title_accent ); ?></span>
			<?php endif; ?>
        </h2>
	<?php endif; ?>
    <div class="instagram-feed__viewport">
        <div class="swiper instagram-feed__swiper" data-instagram-feed>
            <div class="swiper-wrapper">
			<?php foreach ( array_values( $cards ) as $i => $card ) :
				$url = ! empty( $card['url'] ) ? $card['url'] : ( $link['url'] ?? '' );
				?>
                <div class="swiper-slide instagram-feed__item<?php echo $i % 3 === 1 ? ' is-raised' : ''; ?>">
					<?php if ( $url ) : ?><a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php endif; ?>
					<?php echo wp_get_attachment_image( $card['image'], 'large', false, [ 'class' => 'instagram-feed__image', 'loading' => 'lazy' ] ); ?>
					<?php if ( $url ) : ?></a><?php endif; ?>
                </div>
			<?php endforeach; ?>
            </div>
        </div>
		<?php if ( $link ) : ?>
            <a href="<?php echo esc_url( $link['url'] ); ?>" class="instagram-feed__badge"<?php echo $link['target'] ? ' target="' . esc_attr( $link['target'] ) . '" rel="noopener"' : ''; ?>><?php echo esc_html( $link['title'] ); ?></a>
		<?php endif; ?>
    </div>
</section>
