<?php
/**
 * Homepage hero, on orange running up behind the header: title + text,
 * a full-width photo and the "who we are" card
 *
 * @var array $args title, text, image (attachment id), about (feature-card args)
 */
$args = wp_parse_args( $args ?? [], [ 'title' => '', 'text' => '', 'image' => 0, 'about' => [] ] );
?>
<section class="home-hero">
    <div class="home-hero__intro">
		<?php if ( $args['title'] ) : ?>
            <h1 class="home-hero__title"><?php echo esc_html( $args['title'] ); ?></h1>
		<?php endif; ?>
		<?php if ( $args['text'] ) : ?>
            <p class="home-hero__text"><?php echo nl2br( esc_html( $args['text'] ) ); ?></p>
		<?php endif; ?>
    </div>
	<?php if ( $args['image'] ) : ?>
		<?php echo wp_get_attachment_image( $args['image'], 'full', false, [ 'class' => 'home-hero__image', 'loading' => 'eager', 'sizes' => '100vw' ] ); ?>
	<?php endif; ?>
	<?php get_template_part( 'elements/sections/feature-card', null, array_merge( $args['about'], [ 'modifier' => 'blue' ] ) ); ?>
</section>
