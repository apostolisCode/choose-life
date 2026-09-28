<?php
/**
 * Title + two-column video cards (title, subtitle, self-hosted video);
 * the cards alternate between blush and white.
 *
 * @var array $args title, videos (ACF repeater rows: title, subtitle, video (file array), poster (attachment id))
 */
$args   = wp_parse_args( $args ?? [], [ 'title' => '', 'videos' => [] ] );
$videos = array_filter( $args['videos'], function ( $row ) {
	return ! empty( $row['video']['url'] );
} );
if ( ! $videos ) {
	return;
}
?>
<section class="video-cards">
	<?php if ( $args['title'] ) : ?>
        <h2 class="video-cards__title" data-reveal><?php echo esc_html( $args['title'] ); ?></h2>
	<?php endif; ?>
    <ul class="video-cards__list" data-reveal-stagger>
		<?php foreach ( array_values( $videos ) as $i => $row ) :
			$video  = $row['video'];
			$poster = ! empty( $row['poster'] ) ? wp_get_attachment_image_url( $row['poster'], 'large' ) : '';
			?>
            <li class="video-card<?php echo $i % 2 ? '' : ' video-card--blush'; ?>">
				<?php if ( ! empty( $row['title'] ) || ! empty( $row['subtitle'] ) ) : ?>
                    <div class="video-card__header">
						<?php if ( ! empty( $row['title'] ) ) : ?>
                            <h3 class="video-card__title"><?php echo esc_html( $row['title'] ); ?></h3>
						<?php endif; ?>
						<?php if ( ! empty( $row['subtitle'] ) ) : ?>
                            <p class="video-card__subtitle"><?php echo esc_html( $row['subtitle'] ); ?></p>
						<?php endif; ?>
                    </div>
				<?php endif; ?>
                <video class="video-card__player" controls playsinline preload="<?php echo $poster ? 'none' : 'metadata'; ?>"<?php echo $poster ? ' poster="' . esc_url( $poster ) . '"' : ''; ?>
                       width="<?php echo (int) ( $video['width'] ?? 1920 ); ?>" height="<?php echo (int) ( $video['height'] ?? 1080 ); ?>">
                    <source src="<?php echo esc_url( $video['url'] ); ?>" type="<?php echo esc_attr( $video['mime_type'] ?? 'video/mp4' ); ?>"/>
                </video>
            </li>
		<?php endforeach; ?>
    </ul>
</section>
