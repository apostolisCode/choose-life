<?php
/**
 * About page "Our team": a grid of photo cards with name and role
 *
 * @var array $args title, members (ACF repeater rows: photo, name, role)
 */
$args    = wp_parse_args( $args ?? [], [ 'title' => '', 'members' => [] ] );
$wave    = get_template_directory_uri() . '/assets/svg/layout/card-wave-white.svg';
$members = array_filter( $args['members'], function ( $member ) {
	return ! empty( $member['photo'] );
} );
if ( ! $members ) {
	return;
}
?>
<section class="about-team">
	<?php if ( $args['title'] ) : ?>
        <h2 class="about-team__title" data-reveal><?php echo esc_html( $args['title'] ); ?></h2>
	<?php endif; ?>
    <ul class="about-team__grid" data-reveal-stagger>
		<?php foreach ( $members as $member ) : ?>
            <li class="team-card">
				<?php echo wp_get_attachment_image( $member['photo'], 'large', false, [ 'class' => 'team-card__photo', 'loading' => 'lazy', 'sizes' => '352px' ] ); ?>
				<?php if ( ! empty( $member['name'] ) || ! empty( $member['role'] ) ) : ?>
                    <div class="team-card__info">
                        <img src="<?php echo esc_url( $wave ); ?>" width="600" height="11.2066" alt="" class="team-card__wave"/>
						<?php if ( ! empty( $member['name'] ) ) : ?>
                            <h3 class="team-card__name"><?php echo esc_html( $member['name'] ); ?></h3>
						<?php endif; ?>
						<?php if ( ! empty( $member['role'] ) ) : ?>
                            <p class="team-card__role"><?php echo esc_html( $member['role'] ); ?></p>
						<?php endif; ?>
                    </div>
				<?php endif; ?>
            </li>
		<?php endforeach; ?>
    </ul>
</section>
