<?php
/**
 * FAQ card: title, a selection of questions (FAQ post type) and a "view all" link
 *
 * @var array $args title, questions (WP_Post[]), link
 */
$args    = wp_parse_args( $args ?? [], [ 'title' => '', 'questions' => [], 'link' => null ] );
$svg_url = get_template_directory_uri() . '/assets/svg/donation/';
if ( ! $args['questions'] ) {
	return;
}
?>
<section class="faq">
    <div class="faq__card" data-reveal>
        <img src="<?php echo esc_url( $svg_url . 'faq-rainbow.svg' ); ?>" width="197.454" height="122.541" alt="" class="faq__rainbow" loading="lazy"/>
        <div class="faq__inner">
			<?php if ( $args['title'] ) : ?>
                <h2 class="faq__title"><?php echo esc_html( $args['title'] ); ?></h2>
			<?php endif; ?>
			<?php get_template_part( 'elements/parts/faq-list', null, [ 'questions' => $args['questions'] ] ); ?>
			<?php if ( ! empty( $args['link']['url'] ) ) : ?>
                <a href="<?php echo esc_url( $args['link']['url'] ); ?>" class="faq__more"<?php echo $args['link']['target'] ? ' target="' . esc_attr( $args['link']['target'] ) . '" rel="noopener"' : ''; ?>>
					<?php echo esc_html( $args['link']['title'] ); ?>
                    <img src="<?php echo esc_url( $svg_url . 'arrow-right.svg' ); ?>" width="24" height="24" alt=""/>
                </a>
			<?php endif; ?>
        </div>
    </div>
</section>
