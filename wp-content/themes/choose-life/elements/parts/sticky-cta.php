<?php
/**
 * The footer's "support our work" bar (Theme Options → footer CTA), floating
 * at the bottom of the screen until the footer's own bar comes into view
 * (scripts/sticky-cta.js).
 */
$title = get_field( 'footer_cta_title', 'options' );
$link  = get_field( 'footer_cta_link', 'options' );
if ( ! $title || empty( $link['url'] ) ) {
	return;
}
?>
<div class="sticky-cta" data-sticky-cta>
    <p class="sticky-cta__title"><?php echo esc_html( $title ); ?></p>
    <a href="<?php echo esc_url( $link['url'] ); ?>" class="cl-btn cl-btn--primary cl-btn--lg"<?php echo $link['target'] ? ' target="' . esc_attr( $link['target'] ) . '" rel="noopener"' : ''; ?>><?php echo esc_html( $link['title'] ); ?></a>
</div>
