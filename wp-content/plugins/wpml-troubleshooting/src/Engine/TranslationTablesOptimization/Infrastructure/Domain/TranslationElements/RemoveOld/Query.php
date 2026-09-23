<?php

namespace WPML\Troubleshooting\Engine\TranslationTablesOptimization\Infrastructure\Domain\TranslationElements\RemoveOld;

use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\MigrationDataService\QueryInterface;

class Query implements QueryInterface {

  private $wpdb;


  public function __construct( $wpdb ) {
    $this->wpdb = $wpdb;
  }


  public function countRemaining(): int {
    $wpdb = $this->wpdb;

    $count = $wpdb->get_var(
      'SELECT COUNT(DISTINCT j.rid)
      FROM `' . esc_sql( $wpdb->prefix . 'icl_translate_job' ) . '` j
      LEFT JOIN `' . esc_sql( $wpdb->prefix . CompletedRecordsStorage::TMP_TABLE_NAME ) . '` tp ON j.rid = tp.rid
      WHERE j.translated = 1
      AND (tp.rid IS NULL OR tp.processed = 0)'
    );

    return (int) $count;
  }


  public function getRemaining( int $limit ): array {
    $wpdb = $this->wpdb;

    $results = $wpdb->get_results(
      $wpdb->prepare(
        'SELECT DISTINCT j.rid
        FROM `' . esc_sql( $wpdb->prefix . 'icl_translate_job' ) . '` j
        LEFT JOIN `' . esc_sql( $wpdb->prefix . CompletedRecordsStorage::TMP_TABLE_NAME ) . '` tp ON j.rid = tp.rid
        WHERE j.translated = 1
        AND (tp.rid IS NULL OR tp.processed = 0)
        LIMIT %d',
        $limit
      ),
      ARRAY_A
    );

    return $results ?: [];
  }


}
