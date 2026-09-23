<?php
/**
 * Template Name: FAQ
 *
 * Questions from the FAQ post type, one tab per FAQ category.
 */

get_header();

// questions shown per tab before "view all"
$visible_per_tab = 5;

$categories = get_terms( [
	'taxonomy'   => 'faq_category',
	'hide_empty' => true,
] );
$categories = is_wp_error( $categories ) ? [] : $categories;

$tabs = [];
foreach ( $categories as $category ) {
	$questions = get_posts( [
		'post_type'      => 'faq',
		'posts_per_page' => - 1,
		'orderby'        => [ 'menu_order' => 'ASC', 'title' => 'ASC' ],
		'tax_query'      => [ [ 'taxonomy' => 'faq_category', 'terms' => $category->term_id ] ],
	] );
	if ( $questions ) {
		$tabs[] = [ 'term' => $category, 'questions' => $questions ];
	}
}

// no categories yet: a single list with every question
if ( ! $tabs ) {
	$questions = get_posts( [
		'post_type'      => 'faq',
		'posts_per_page' => - 1,
		'orderby'        => [ 'menu_order' => 'ASC', 'title' => 'ASC' ],
	] );
	if ( $questions ) {
		$tabs[] = [ 'term' => null, 'questions' => $questions ];
	}
}

$arrow = get_template_directory_uri() . '/assets/svg/donation/arrow-right.svg';
?>
    <div class="faq-page">
        <header class="faq-page__header">
            <h1 class="faq-page__title"><?php the_title(); ?></h1>
        </header>

		<?php if ( $tabs ) : ?>
            <section class="faq-page__body" data-faq-tabs>
				<?php if ( count( $tabs ) > 1 ) : ?>
                    <div class="faq-tabs" role="tablist" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
						<?php foreach ( $tabs as $i => $tab ) : ?>
                            <button type="button" class="faq-tabs__tab<?php echo $i === 0 ? ' is-active' : ''; ?>" role="tab"
                                    id="faq-tab-<?php echo $i; ?>" aria-controls="faq-panel-<?php echo $i; ?>"
                                    aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"<?php echo $i === 0 ? '' : ' tabindex="-1"'; ?>>
								<?php echo esc_html( $tab['term']->name ); ?>
                            </button>
						<?php endforeach; ?>
                    </div>
				<?php endif; ?>

				<?php foreach ( $tabs as $i => $tab ) : ?>
                    <div class="faq-page__panel" id="faq-panel-<?php echo $i; ?>"<?php if ( count( $tabs ) > 1 ) : ?> role="tabpanel" aria-labelledby="faq-tab-<?php echo $i; ?>"<?php endif; ?> data-faq-panel>
						<?php get_template_part( 'elements/parts/faq-list', null, [
							'questions' => $tab['questions'],
							'visible'   => $visible_per_tab,
						] ); ?>
						<?php if ( count( $tab['questions'] ) > $visible_per_tab ) : ?>
                            <button type="button" class="faq__more" data-faq-more>
								<?php esc_html_e( 'View all', 'choose-life' ); ?>
                                <img src="<?php echo esc_url( $arrow ); ?>" width="24" height="24" alt=""/>
                            </button>
						<?php endif; ?>
                    </div>
				<?php endforeach; ?>
            </section>
		<?php endif; ?>

		<?php
		// content from Theme Options → Instagram feed (shared by all templates)
		get_template_part( 'elements/sections/instagram-feed' );
		?>
    </div>
<?php
get_footer();
