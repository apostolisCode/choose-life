<?php
/**
 * Page hero: title + text, photo on a tilted red frame (donation, institute)
 *
 * @var array $args title, text (blank lines split paragraphs), image (attachment id),
 *                  image_position (left|center|right), modifier (e.g. 'institute')
 */
$args = wp_parse_args( $args ?? [], [ 'title' => '', 'text' => '', 'image' => 0, 'image_position' => 'center', 'modifier' => '' ] );

$classes = 'donation-hero' . ( $args['modifier'] ? ' donation-hero--' . sanitize_html_class( $args['modifier'] ) : '' );
$image   = [ 'class' => 'donation-hero__image', 'loading' => 'eager' ];
if ( in_array( $args['image_position'], [ 'left', 'right' ], true ) ) {
	$image['style'] = 'object-position: ' . $args['image_position'] . ' center';
}
?>
<section class="<?php echo esc_attr( $classes ); ?>">
    <div class="donation-hero__inner">
        <div class="donation-hero__content">
			<?php if ( $args['title'] ) : ?>
                <h1 class="donation-hero__title"><?php echo esc_html( $args['title'] ); ?></h1>
			<?php endif; ?>
			<?php if ( $args['text'] ) : ?>
                <div class="donation-hero__text"><?php echo wpautop( esc_html( $args['text'] ) ); ?></div>
			<?php endif; ?>
        </div>
		<?php if ( $args['image'] ) : ?>
            <div class="donation-hero__media">
                <span class="donation-hero__frame" aria-hidden="true"></span>
				<?php echo wp_get_attachment_image( $args['image'], 'large', false, $image ); ?>
            </div>
		<?php endif; ?>
    </div>
</section>
