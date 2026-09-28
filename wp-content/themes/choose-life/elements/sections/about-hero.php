<?php
/**
 * About page hero: title + text on blue (running up behind the header),
 * then a full-width photo
 *
 * @var array $args title, text, image (attachment id)
 */
$args = wp_parse_args( $args ?? [], [ 'title' => '', 'text' => '', 'image' => 0 ] );
?>
<section class="about-hero">
    <div class="about-hero__intro">
        <div class="about-hero__inner">
			<?php if ( $args['title'] ) : ?>
                <h1 class="about-hero__title"><?php echo esc_html( $args['title'] ); ?></h1>
			<?php endif; ?>
			<?php if ( $args['text'] ) : ?>
                <p class="about-hero__text"><?php echo nl2br( esc_html( $args['text'] ) ); ?></p>
			<?php endif; ?>
        </div>
    </div>
	<?php if ( $args['image'] ) : ?>
		<?php echo wp_get_attachment_image( $args['image'], 'full', false, [ 'class' => 'about-hero__image', 'loading' => 'eager', 'sizes' => '100vw' ] ); ?>
	<?php endif; ?>
</section>
