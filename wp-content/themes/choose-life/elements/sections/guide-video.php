<?php
/**
 * Dark section with a title and a self-hosted video (Media Library file).
 *
 * @var array $args {
 * @type string $title
 * @type array|null $video ACF file (array)
 * @type array|null $poster ACF image (array)
 * }
 */

$video = $args['video'] ?? null;
if ( ! $video || empty( $video['url'] ) ) {
	return;
}

$poster = $args['poster'] ?? null;
?>
<section class="guide-video">
    <img class="guide-video__rainbow" src="<?php echo esc_url( get_template_directory_uri() . '/assets/svg/volunteer/rainbow.svg' ); ?>"
         width="197" height="123" alt="" aria-hidden="true"/>
    <div class="guide-video__inner">
		<?php if ( ! empty( $args['title'] ) ) : ?>
            <h2 class="guide-video__title" data-reveal><?php echo esc_html( $args['title'] ); ?></h2>
		<?php endif; ?>
        <div class="guide-video__frame" data-reveal>
            <video class="guide-video__player" controls playsinline preload="<?php echo $poster ? 'none' : 'metadata'; ?>"
				<?php echo $poster ? ' poster="' . esc_url( $poster['sizes']['large'] ?? $poster['url'] ) . '"' : ''; ?>
                   width="<?php echo (int) ( $video['width'] ?? 1920 ); ?>" height="<?php echo (int) ( $video['height'] ?? 1080 ); ?>">
                <source src="<?php echo esc_url( $video['url'] ); ?>" type="<?php echo esc_attr( $video['mime_type'] ?? 'video/mp4' ); ?>"/>
            </video>
        </div>
    </div>
</section>
