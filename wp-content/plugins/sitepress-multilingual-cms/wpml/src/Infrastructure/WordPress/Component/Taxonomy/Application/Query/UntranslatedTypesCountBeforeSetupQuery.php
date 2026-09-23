<?php

namespace WPML\Infrastructure\WordPress\Component\Taxonomy\Application\Query;

use WPML\Core\SharedKernel\Component\Item\Application\Query\Dto\UntranslatedTypeCountDto;
use WPML\Core\SharedKernel\Component\Item\Application\Query\UntranslatedTypesCountQueryInterface;
use WPML\Core\SharedKernel\Component\Taxonomy\Application\Query\Dto\TaxonomyDto;
use WPML\Core\SharedKernel\Component\Taxonomy\Application\Query\TranslatableTaxonomiesQueryInterface;

class UntranslatedTypesCountBeforeSetupQuery implements UntranslatedTypesCountQueryInterface {

  private $taxonomiesQuery;

  private $wpdb;


  public function __construct( TranslatableTaxonomiesQueryInterface $taxonomiesQuery, $wpdb = null ) {
    $this->taxonomiesQuery = $taxonomiesQuery;
    $this->wpdb            = $wpdb ?: $GLOBALS['wpdb'];
  }


  public function forKind() {
    return UntranslatedTypesCountQueryInterface::KIND_TAXONOMY;
  }


  public function get( array $queryData = [] ): array {
    $taxonomies = $this->taxonomiesQuery->getTranslatable();
    if ( ! $taxonomies ) {
      return [];
    }

    $counts = $this->countTermsPerTaxonomy( $taxonomies );

    return array_map(
      function ( TaxonomyDto $taxonomy ) use ( $counts ) {
        return new UntranslatedTypeCountDto(
          $taxonomy->getPlural(),
          $taxonomy->getSingular(),
          $counts[ $taxonomy->getId() ] ?? 0,
          UntranslatedTypesCountQueryInterface::KIND_TAXONOMY,
          'tax_' . $taxonomy->getId()
        );
      },
      $taxonomies
    );
  }


  public function getSomeIds( $numberOfIdsToFetch, $offset, $type = '' ) {
    $taxonomiesIn = $this->taxonomiesIn();
    if ( $taxonomiesIn === '' ) {
      return [];
    }

    $limit  = absint( $numberOfIdsToFetch );
    $offset = absint( $offset );

    $ids = $this->wpdb->get_col(
      "SELECT tt.term_taxonomy_id
       FROM {$this->wpdb->term_taxonomy} AS tt
       WHERE tt.taxonomy IN ({$taxonomiesIn})
       ORDER BY tt.term_taxonomy_id ASC
       LIMIT {$limit} OFFSET {$offset}"
    );

    return array_map( 'intval', $ids );
  }


  private function countTermsPerTaxonomy( array $taxonomies ): array {
    $taxonomiesIn = $this->taxonomiesIn( $taxonomies );
    if ( $taxonomiesIn === '' ) {
      return [];
    }

    $rows = $this->wpdb->get_results(
      "SELECT tt.taxonomy, COUNT(*) AS total
       FROM {$this->wpdb->term_taxonomy} AS tt
       WHERE tt.taxonomy IN ({$taxonomiesIn})
       GROUP BY tt.taxonomy",
      ARRAY_A
    );

    $counts = [];
    foreach ( (array) $rows as $row ) {
      $counts[ (string) $row['taxonomy'] ] = (int) $row['total'];
    }

    return $counts;
  }


  private function taxonomiesIn( $taxonomies = null ): string {
    if ( $taxonomies === null ) {
      $taxonomies = $this->taxonomiesQuery->getTranslatable();
    }

    if ( ! $taxonomies ) {
      return '';
    }

    $slugs = array_map(
      function ( TaxonomyDto $taxonomy ) {
        return esc_sql( $taxonomy->getId() );
      },
      $taxonomies
    );

    return "'" . implode( "','", $slugs ) . "'";
  }


}
