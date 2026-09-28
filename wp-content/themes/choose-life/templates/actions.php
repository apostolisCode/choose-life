<?php
/**
 * Template Name: Actions
 *
 * The actions (posts): title + intro, a carousel of the featured ones and the
 * others in a paged grid of cards.
 */

get_header();

$paged    = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$featured = get_field( 'featured_actions' ) ?: [];
$actions  = new WP_Query( [
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 8,
	'paged'          => $paged,
	'post__not_in'   => wp_list_pluck( $featured, 'ID' ),
	'ignore_sticky_posts' => true,
] );
?>
    <div class="actions-page">
        <header class="actions-page__header">
            <h1 class="actions-page__title"><?php the_title(); ?></h1>
			<?php if ( $intro = get_field( 'intro' ) ) : ?>
                <p class="actions-page__intro"><?php echo esc_html( $intro ); ?></p>
			<?php endif; ?>
        </header>

        <div class="actions-page__body">
			<?php
			// the carousel on the first page only
			if ( $paged === 1 ) {
				get_template_part( 'elements/sections/actions-featured', null, [ 'posts' => $featured ] );
			}
			?>

			<?php if ( $actions->have_posts() ) : ?>
                <ul class="actions-grid" data-reveal-stagger>
					<?php foreach ( $actions->posts as $post ) : ?>
                        <li class="actions-grid__item"><?php get_template_part( 'elements/parts/action-card', null, [ 'post' => $post ] ); ?></li>
					<?php endforeach; ?>
                </ul>
				<?php get_template_part( 'elements/parts/pagination', null, [ 'query' => $actions, 'paged' => $paged ] ); ?>
			<?php endif; ?>
        </div>

		<?php
		// content from Theme Options → Instagram feed / Newsletter (shared by all templates)
		get_template_part( 'elements/sections/instagram-feed' );
		get_template_part( 'elements/sections/newsletter' );
		?>
    </div>
<?php
wp_reset_postdata();
get_footer();
