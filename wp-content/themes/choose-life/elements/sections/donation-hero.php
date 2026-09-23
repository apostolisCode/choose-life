<?php
/**
 * Donation page hero: title + text, photo on a tilted red frame
 *
 * @var array $args title, text, image (attachment id)
 */
$args = wp_parse_args( $args ?? [], [ 'title' => '', 'text' => '', 'image' => 0 ] );
?>
<section class="donation-hero">
    <div class="donation-hero__inner">
        <div class="donation-hero__content">
			<?php if ( $args['title'] ) : ?>
                <h1 class="donation-hero__title"><?php echo esc_html( $args['title'] ); ?></h1>
			<?php endif; ?>
			<?php if ( $args['text'] ) : ?>
                <p class="donation-hero__text"><?php echo nl2br( esc_html( $args['text'] ) ); ?></p>
			<?php endif; ?>
        </div>
		<?php if ( $args['image'] ) : ?>
            <div class="donation-hero__media">
                <span class="donation-hero__frame" aria-hidden="true"></span>
				<?php echo wp_get_attachment_image( $args['image'], 'large', false, [ 'class' => 'donation-hero__image', 'loading' => 'eager' ] ); ?>
            </div>
		<?php endif; ?>
    </div>
</section>
