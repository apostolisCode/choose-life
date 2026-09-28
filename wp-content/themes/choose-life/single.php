<?php
/**
 * A single action (post): breadcrumb + title, the event details and share
 * buttons in a sticky side column, the content with a button, and more actions.
 */

get_header();
the_post();

$svg       = get_template_directory_uri() . '/assets/svg/';
$page_id   = theme_actions_page_id();
$date      = theme_action_date();
$location  = get_field( 'event_location' );
$cta       = get_field( 'cta' );
$cta       = ! empty( $cta['url'] ) ? $cta : null;
$permalink = get_permalink();
// sent with the link by the share-sheet / copy button (Facebook and LinkedIn use the page's Open Graph tags)
$share_text = get_field( 'share_text' ) ?: get_field( 'share_text', 'options' );
$more      = get_posts( [
	'post_type'           => 'post',
	'posts_per_page'      => 4,
	'post__not_in'        => [ get_the_ID() ],
	'ignore_sticky_posts' => true,
] );
$share      = [
	'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $permalink ),
	'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $permalink ),
];
?>
    <div class="action-page">
        <header class="action-page__header">
            <nav class="breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'choose-life' ); ?>">
                <ol class="breadcrumb__list">
                    <li class="breadcrumb__item">
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="breadcrumb__link"><?php esc_html_e( 'Home', 'choose-life' ); ?></a>
                        <img src="<?php echo esc_url( $svg . 'blog/breadcrumb-star.svg' ); ?>" width="36" height="12" alt="" class="breadcrumb__separator"/>
                    </li>
					<?php if ( $page_id ) : ?>
                        <li class="breadcrumb__item">
                            <a href="<?php echo esc_url( get_permalink( $page_id ) ); ?>" class="breadcrumb__link breadcrumb__link--current"><?php echo esc_html( get_the_title( $page_id ) ); ?></a>
                        </li>
					<?php endif; ?>
                </ol>
            </nav>
            <h1 class="action-page__title"><?php the_title(); ?></h1>
        </header>

        <div class="action-page__article">
            <aside class="action-page__aside">
                <div class="action-page__sticky">
					<?php if ( $date || $location ) : ?>
                        <dl class="action-page__details">
							<?php if ( $date ) : ?>
                                <div class="action-page__detail">
                                    <dt><?php esc_html_e( 'Date', 'choose-life' ); ?></dt>
                                    <dd><?php echo esc_html( $date ); ?></dd>
                                </div>
							<?php endif; ?>
							<?php if ( $location ) : ?>
                                <div class="action-page__detail">
                                    <dt><?php esc_html_e( 'Location', 'choose-life' ); ?></dt>
                                    <dd><?php echo esc_html( $location ); ?></dd>
                                </div>
							<?php endif; ?>
                        </dl>
					<?php endif; ?>

                    <div class="share">
                        <p class="share__label"><?php esc_html_e( 'Share', 'choose-life' ); ?></p>
                        <div class="share__buttons">
                            <a href="<?php echo esc_url( $share['facebook'] ); ?>" class="share__button" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( sprintf( __( 'Share on %s', 'choose-life' ), 'Facebook' ) ); ?>">
                                <img src="<?php echo esc_url( $svg . 'blog/share-facebook.svg' ); ?>" width="9.56554" height="20" alt=""/>
                            </a>
							<?php // Instagram has no share link: the device's share sheet (it lists Instagram on phones), else copy the link ?>
                            <button type="button" class="share__button" data-share="<?php echo esc_url( $permalink ); ?>" data-share-title="<?php echo esc_attr( get_the_title() ); ?>" data-share-text="<?php echo esc_attr( $share_text ); ?>" aria-label="<?php esc_attr_e( 'Share or copy the link', 'choose-life' ); ?>">
                                <img src="<?php echo esc_url( $svg . 'blog/share-instagram.svg' ); ?>" width="20.341" height="20" alt=""/>
                                <span class="share__copied" data-share-copied hidden><?php esc_html_e( 'Link copied', 'choose-life' ); ?></span>
                            </button>
                            <a href="<?php echo esc_url( $share['linkedin'] ); ?>" class="share__button" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( sprintf( __( 'Share on %s', 'choose-life' ), 'LinkedIn' ) ); ?>">
                                <img src="<?php echo esc_url( $svg . 'blog/share-linkedin.svg' ); ?>" width="19.8401" height="20" alt=""/>
                            </a>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="action-page__content">
                <div class="post-content">
					<?php the_content(); ?>
                </div>
				<?php if ( $cta ) : ?>
                    <a href="<?php echo esc_url( $cta['url'] ); ?>" class="cl-btn cl-btn--primary cl-btn--lg action-page__cta"<?php echo $cta['target'] ? ' target="' . esc_attr( $cta['target'] ) . '" rel="noopener"' : ''; ?>><?php echo esc_html( $cta['title'] ); ?></a>
				<?php endif; ?>
            </div>
        </div>

		<?php if ( $more ) : ?>
            <section class="action-page__more">
                <h2 class="action-page__more-title" data-reveal><?php esc_html_e( 'More actions', 'choose-life' ); ?></h2>
                <ul class="actions-grid" data-reveal-stagger>
					<?php foreach ( $more as $post_item ) : ?>
                        <li class="actions-grid__item"><?php get_template_part( 'elements/parts/action-card', null, [ 'post' => $post_item ] ); ?></li>
					<?php endforeach; ?>
                </ul>
            </section>
		<?php endif; ?>

		<?php
		// content from Theme Options → Newsletter (shared by all templates)
		get_template_part( 'elements/sections/newsletter' );
		?>
    </div>
<?php
get_footer();
