<?php
/**
 * Hand-drawn heart used in the words bands, assembled from the design's six
 * vector pieces at their original offsets.
 */
$heart_url = get_template_directory_uri() . '/assets/svg/donation/heart/';
$pieces    = [
	// [ file, left, top, width, height ]
	[ 'g0', 17.05, 0.88, 15.7809, 48.9648 ],
	[ 'g1', 1.92, 14.86, 17.5214, 34.2049 ],
	[ 'g2', 15.44, 0, 15.945, 49.4604 ],
	[ 'g3', 1.49, 9.77, 18.023, 39.7028 ],
	[ 'g4', 13.9, 4.39, 20.819, 46.4036 ],
	[ 'g5', 0, 8.28, 17.673, 41.8175 ],
];
?>
<span class="band-heart" aria-hidden="true">
	<span class="band-heart__shape">
		<?php foreach ( $pieces as [ $file, $left, $top, $width, $height ] ) : ?>
			<img src="<?php echo esc_url( $heart_url . $file . '.svg' ); ?>" width="<?php echo $width; ?>" height="<?php echo $height; ?>" alt="" style="left: <?php echo $left; ?>px; top: <?php echo $top; ?>px;"/>
		<?php endforeach; ?>
	</span>
</span>
