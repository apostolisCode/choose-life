<?php
/**
 * Two tilted, scrolling bands of words (pink and white)
 *
 * @var array $args words (string[])
 */
$words = $args['words'] ?? [];
if ( ! $words ) {
	return;
}
$bands = [
	'pink'  => $words,
	'light' => array_reverse( $words ),
];
?>
<div class="words-bands" aria-hidden="true">
	<?php foreach ( $bands as $variant => $band_words ) : ?>
        <div class="words-band words-band--<?php echo esc_attr( $variant ); ?>">
            <div class="words-band__track">
				<?php // two identical halves, so the scroll animation loops seamlessly
				for ( $half = 0; $half < 2; $half ++ ) : ?>
                    <div class="words-band__group">
						<?php for ( $repeat = 0; $repeat < 3; $repeat ++ ) :
							foreach ( $band_words as $word ) : ?>
                                <span class="words-band__word"><?php echo esc_html( $word ); ?></span>
								<?php get_template_part( 'elements/parts/band-heart' ); ?>
							<?php endforeach;
						endfor; ?>
                    </div>
				<?php endfor; ?>
            </div>
        </div>
	<?php endforeach; ?>
</div>
