<?php
/**
 * Dark section with a title and a 360° tour shown in an iframe. With a cover
 * image the iframe loads only when the "360°" button is pressed
 * (scripts/tour-360.js); without one it is embedded straight away.
 *
 * @var array $args title, url (embed url of the tour), image (attachment id)
 */
$args = wp_parse_args( $args ?? [], [ 'title' => '', 'url' => '', 'image' => 0 ] );
if ( ! $args['url'] ) {
	return;
}

$svg   = get_template_directory_uri() . '/assets/svg/';
$title = $args['title'] ?: __( '360° tour', 'choose-life' );
?>
<section class="tour-360">
    <img class="tour-360__rainbow" src="<?php echo esc_url( $svg . 'volunteer/rainbow.svg' ); ?>" width="197.454" height="122.541" alt="" aria-hidden="true" loading="lazy"/>
    <div class="tour-360__inner">
		<?php if ( $args['title'] ) : ?>
            <h2 class="tour-360__title" data-reveal><?php echo esc_html( $args['title'] ); ?></h2>
		<?php endif; ?>
        <div class="tour-360__frame" data-reveal data-tour-360="<?php echo esc_url( $args['url'] ); ?>" data-tour-title="<?php echo esc_attr( $title ); ?>">
			<?php if ( $args['image'] ) : ?>
				<?php echo wp_get_attachment_image( $args['image'], 'full', false, [ 'class' => 'tour-360__image', 'loading' => 'lazy', 'sizes' => '(min-width: 1540px) 1418px, 100vw' ] ); ?>
                <button type="button" class="tour-360__play" data-tour-360-play>
                    <img src="<?php echo esc_url( $svg . 'institute/360-arrow-top.svg' ); ?>" width="101.001" height="24.338" alt="" class="tour-360__arrow tour-360__arrow--top"/>
                    <span class="tour-360__label">360°</span>
                    <img src="<?php echo esc_url( $svg . 'institute/360-arrow-bottom.svg' ); ?>" width="101.001" height="24.0005" alt="" class="tour-360__arrow tour-360__arrow--bottom"/>
                    <span class="visually-hidden"><?php esc_html_e( 'Start the 360° tour', 'choose-life' ); ?></span>
                </button>
			<?php else : ?>
                <iframe class="tour-360__iframe" src="<?php echo esc_url( $args['url'] ); ?>" title="<?php echo esc_attr( $title ); ?>"
                        loading="lazy" allow="fullscreen; accelerometer; gyroscope; magnetometer; xr-spatial-tracking" allowfullscreen></iframe>
			<?php endif; ?>
        </div>
    </div>
</section>
