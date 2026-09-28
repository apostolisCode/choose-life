<?php
/**
 * Featured actions: a swipeable carousel of wide cards (photo on the left,
 * "date · location", title, excerpt, "more"); scripts/actions-carousel.js
 *
 * @var array $args posts (WP_Post[])
 */
$posts = array_filter( $args['posts'] ?? [], function ( $post ) {
	return $post instanceof WP_Post && $post->post_status === 'publish';
} );
if ( ! $posts ) {
	return;
}
?>
<section class="actions-featured" aria-label="<?php esc_attr_e( 'Featured actions', 'choose-life' ); ?>" data-reveal>
    <div class="swiper actions-featured__swiper" data-actions-carousel>
        <div class="swiper-wrapper">
			<?php foreach ( $posts as $post ) :
				$meta    = theme_action_meta( $post );
				$excerpt = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 30, ' […]' );
				?>
                <article class="swiper-slide actions-featured__slide">
                    <a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="actions-featured__card">
                        <span class="actions-featured__media">
							<?php if ( has_post_thumbnail( $post ) ) {
								echo get_the_post_thumbnail( $post, 'large', [ 'class' => 'actions-featured__image', 'loading' => 'eager', 'sizes' => '(min-width: 1200px) 855px, 100vw' ] );
							} ?>
                        </span>
                        <span class="actions-featured__body">
							<?php if ( $meta ) : ?>
                                <span class="actions-featured__meta"><?php echo esc_html( $meta ); ?></span>
							<?php endif; ?>
                            <h2 class="actions-featured__title"><?php echo esc_html( get_the_title( $post ) ); ?></h2>
							<?php if ( $excerpt ) : ?>
                                <span class="actions-featured__excerpt"><?php echo esc_html( $excerpt ); ?></span>
							<?php endif; ?>
                            <span class="actions-featured__more"><?php esc_html_e( 'More', 'choose-life' ); ?> →</span>
                        </span>
                    </a>
                </article>
			<?php endforeach; ?>
        </div>
		<?php if ( count( $posts ) > 1 ) : ?>
            <div class="actions-featured__dots" data-actions-carousel-dots></div>
		<?php endif; ?>
    </div>
</section>
