<?php
/**
 * Page numbers of a query: previous / numbers (current in a red circle, gaps as
 * "…") / next; the arrows stay, faded, on the first and last page.
 *
 * @var array $args query (WP_Query), paged (int, current page)
 */
$query = $args['query'] ?? null;
$paged = max( 1, (int) ( $args['paged'] ?? 1 ) );
if ( ! $query instanceof WP_Query || $query->max_num_pages < 2 ) {
	return;
}
$pages = paginate_links( [
	'total'     => $query->max_num_pages,
	'current'   => $paged,
	'mid_size'  => 1,
	'end_size'  => 1,
	'prev_next' => false,
	'type'      => 'array',
] );
$arrow = function ( $direction, $page ) use ( $query ) {
	$label = $direction === 'prev' ? __( 'Previous page', 'choose-life' ) : __( 'Next page', 'choose-life' );
	$class = 'pagination__arrow pagination__arrow--' . $direction;
	if ( $page < 1 || $page > $query->max_num_pages ) {
		return sprintf( '<span class="%s is-disabled" aria-hidden="true"></span>', $class );
	}
	return sprintf( '<a class="%s" href="%s" aria-label="%s"></a>', $class, esc_url( get_pagenum_link( $page ) ), esc_attr( $label ) );
};
?>
<nav class="pagination" aria-label="<?php esc_attr_e( 'Pages', 'choose-life' ); ?>">
	<?php echo $arrow( 'prev', $paged - 1 ); ?>
	<?php foreach ( $pages as $page ) : ?>
		<?php echo str_replace( 'page-numbers', 'pagination__page page-numbers', $page ); ?>
	<?php endforeach; ?>
	<?php echo $arrow( 'next', $paged + 1 ); ?>
</nav>
