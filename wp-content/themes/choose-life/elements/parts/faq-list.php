<?php
/**
 * List of expandable questions (FAQ post type: title = question, content = answer)
 *
 * @var array $args questions (WP_Post[]), visible (int, 0 = all; the rest are
 *                  marked data-faq-extra and hidden by scripts/faq-tabs.js
 *                  until "view all" is used)
 */
$questions = $args['questions'] ?? [];
$visible   = (int) ( $args['visible'] ?? 0 );
$icon      = get_template_directory_uri() . '/assets/svg/donation/faq-plus.svg';
if ( ! $questions ) {
	return;
}
?>
<div class="faq__list">
	<?php foreach ( array_values( $questions ) as $i => $question ) :
		$answer = apply_filters( 'the_content', $question->post_content );
		?>
        <details class="faq__item"<?php echo $visible && $i >= $visible ? ' data-faq-extra' : ''; ?>>
            <summary class="faq__question">
                <span><?php echo esc_html( get_the_title( $question ) ); ?></span>
                <img src="<?php echo esc_url( $icon ); ?>" width="24" height="24" alt="" class="faq__icon"/>
            </summary>
			<?php if ( trim( wp_strip_all_tags( $answer ) ) ) : ?>
                <div class="faq__answer"><?php echo $answer; ?></div>
			<?php endif; ?>
        </details>
	<?php endforeach; ?>
</div>
