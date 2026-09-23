<?php
/**
 * "Become a donor from home": the registration steps and the form button.
 *
 * @var array $args {
 * @type string $title
 * @type string $subtitle
 * @type array $steps ACF repeater rows (image, title, text)
 * @type array|null $cta ACF link
 * @type string $cta_note
 * @type string $contact_text
 * @type array|null $contact_link ACF link
 * }
 */

$steps = $args['steps'] ?? [];
if ( ! $steps && empty( $args['title'] ) ) {
	return;
}

$wave         = get_template_directory_uri() . '/assets/svg/donation/card-wave.svg';
$cta          = $args['cta'] ?? null;
$contact_link = $args['contact_link'] ?? null;
?>
<section class="volunteer-steps">
    <header class="volunteer-steps__header" data-reveal>
		<?php if ( ! empty( $args['title'] ) ) : ?>
            <h2 class="volunteer-steps__title"><?php echo esc_html( $args['title'] ); ?></h2>
		<?php endif; ?>
		<?php if ( ! empty( $args['subtitle'] ) ) : ?>
            <p class="volunteer-steps__subtitle"><?php echo esc_html( $args['subtitle'] ); ?></p>
		<?php endif; ?>
    </header>

	<?php if ( $steps ) : ?>
        <ol class="volunteer-steps__list" data-reveal-stagger>
			<?php foreach ( $steps as $step ) : ?>
                <li class="volunteer-steps__item">
                    <div class="mission-card">
                        <div class="mission-card__media">
							<?php if ( ! empty( $step['image'] ) ) : ?>
								<?php echo wp_get_attachment_image( $step['image']['ID'], 'large', false, [
									'class'   => 'mission-card__image',
									'loading' => 'lazy',
									'sizes'   => '(min-width: 1200px) 348px, (min-width: 768px) 45vw, 90vw',
								] ); ?>
							<?php endif; ?>
                            <img src="<?php echo esc_url( $wave ); ?>" width="600" height="11.2066" alt="" class="mission-card__wave"/>
                        </div>
                        <div class="mission-card__body">
                            <h3 class="mission-card__title"><?php echo esc_html( $step['title'] ); ?></h3>
							<?php if ( ! empty( $step['text'] ) ) : ?>
                                <p class="mission-card__text"><?php echo esc_html( $step['text'] ); ?></p>
							<?php endif; ?>
                        </div>
                    </div>
                </li>
			<?php endforeach; ?>
        </ol>
	<?php endif; ?>

	<?php if ( $cta || ! empty( $args['contact_text'] ) ) : ?>
        <div class="volunteer-steps__actions" data-reveal>
			<?php if ( $cta ) : ?>
                <a class="cl-btn cl-btn--primary cl-btn--lg" href="<?php echo esc_url( $cta['url'] ); ?>"
					<?php echo $cta['target'] ? ' target="' . esc_attr( $cta['target'] ) . '" rel="noopener"' : ''; ?>>
					<?php echo esc_html( $cta['title'] ); ?>
                </a>
			<?php endif; ?>
			<?php if ( ! empty( $args['cta_note'] ) ) : ?>
                <p class="volunteer-steps__note"><?php echo esc_html( $args['cta_note'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $args['contact_text'] ) || $contact_link ) : ?>
                <p class="volunteer-steps__contact">
					<?php echo esc_html( $args['contact_text'] ?? '' ); ?>
					<?php if ( $contact_link ) : ?>
                        <a href="<?php echo esc_url( $contact_link['url'] ); ?>"<?php echo $contact_link['target'] ? ' target="' . esc_attr( $contact_link['target'] ) . '" rel="noopener"' : ''; ?>>
							<?php echo esc_html( $contact_link['title'] ); ?>
                        </a>
					<?php endif; ?>
                </p>
			<?php endif; ?>
        </div>
	<?php endif; ?>
</section>
