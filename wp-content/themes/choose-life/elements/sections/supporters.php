<?php
/**
 * Supporters: a title and a strip of logos scrolling endlessly (CSS animation,
 * paused on hover; the list is printed twice so the loop is seamless).
 *
 * @var array $args title, logos (ACF repeater rows: logo, name, url)
 */
$args  = wp_parse_args( $args ?? [], [ 'title' => '', 'logos' => [] ] );
$logos = array_filter( $args['logos'], function ( $row ) {
	return ! empty( $row['logo'] );
} );
if ( ! $logos ) {
	return;
}
?>
<section class="supporters">
	<?php if ( $args['title'] ) : ?>
        <h2 class="supporters__title"><?php echo esc_html( $args['title'] ); ?></h2>
	<?php endif; ?>
    <div class="supporters__strip">
        <div class="supporters__track" style="--supporters-count: <?php echo count( $logos ); ?>">
			<?php for ( $copy = 0; $copy < 2; $copy ++ ) : ?>
                <ul class="supporters__list"<?php echo $copy ? ' aria-hidden="true"' : ''; ?>>
					<?php foreach ( $logos as $row ) :
						$name  = $row['name'] ?? '';
						$image = wp_get_attachment_image( $row['logo'], 'medium', false, [ 'class' => 'supporters__logo', 'loading' => 'lazy', 'alt' => $copy ? '' : $name ] );
						?>
                        <li class="supporters__item">
							<?php if ( ! empty( $row['url'] ) ) : ?>
                                <a href="<?php echo esc_url( $row['url'] ); ?>" target="_blank" rel="noopener"<?php echo $copy ? ' tabindex="-1"' : ''; ?>><?php echo $image; ?></a>
							<?php else : ?>
								<?php echo $image; ?>
							<?php endif; ?>
                        </li>
					<?php endforeach; ?>
                </ul>
			<?php endfor; ?>
        </div>
    </div>
</section>
