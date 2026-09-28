<?php
/**
 * Action (post) card: square photo, "date · location", title, excerpt and a
 * "more" link; the whole card is clickable.
 * Used by: templates/actions.php, single.php (more actions)
 *
 * @var array $args post (WP_Post)
 */
$post = $args['post'] ?? null;
if ( ! $post instanceof WP_Post ) {
	return;
}
$meta    = theme_action_meta( $post );
$excerpt = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 20, ' […]' );
?>
<article class="action-card">
    <a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="action-card__link">
        <span class="action-card__media">
			<?php if ( has_post_thumbnail( $post ) ) {
				echo get_the_post_thumbnail( $post, 'large', [ 'class' => 'action-card__image', 'loading' => 'lazy', 'sizes' => '(min-width: 1200px) 414px, (min-width: 768px) 45vw, 100vw' ] );
			} ?>
        </span>
        <span class="action-card__body">
			<?php if ( $meta ) : ?>
                <span class="action-card__meta"><?php echo esc_html( $meta ); ?></span>
			<?php endif; ?>
            <h3 class="action-card__title"><?php echo esc_html( get_the_title( $post ) ); ?></h3>
			<?php if ( $excerpt ) : ?>
                <span class="action-card__excerpt"><?php echo esc_html( $excerpt ); ?></span>
			<?php endif; ?>
            <span class="action-card__more"><?php esc_html_e( 'More', 'choose-life' ); ?> →</span>
        </span>
    </a>
</article>
