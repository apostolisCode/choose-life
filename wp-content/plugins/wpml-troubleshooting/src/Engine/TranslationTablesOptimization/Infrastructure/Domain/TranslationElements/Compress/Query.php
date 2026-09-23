<?php

namespace WPML\Troubleshooting\Engine\TranslationTablesOptimization\Infrastructure\Domain\TranslationElements\Compress;

use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\MigrationDataService\QueryInterface;

class Query implements QueryInterface {

  private $wpdb;


  public function __construct( $wpdb ) {
    $this->wpdb = $wpdb;
  }


  public function countRemaining(): int {
    $wpdb = $this->wpdb;

    $count = $wpdb->get_var(
      $wpdb->prepare(
        'SELECT COUNT(*)
        FROM `' . esc_sql( $wpdb->prefix . 'icl_translate' ) . '` t
        LEFT JOIN `' . esc_sql( $wpdb->prefix . CompletedRecordsStorage::TMP_TABLE_NAME ) . '` tc ON t.tid = tc.tid
        WHERE t.field_format = %s
        AND (tc.tid IS NULL OR tc.compressed = 0)',
        'base64'
      )
    );

    return (int) $count;
  }


  public function getRemaining( int $limit ): array {
    $wpdb = $this->wpdb;

    $results = $wpdb->get_results(
      $wpdb->prepare(
        'SELECT t.tid, t.field_data as fieldData, t.field_data_translated as fieldDataTranslated
        FROM `' . esc_sql( $wpdb->prefix . 'icl_translate' ) . '` t
        LEFT JOIN `' . esc_sql( $wpdb->prefix . CompletedRecordsStorage::TMP_TABLE_NAME ) . '` tc ON t.tid = tc.tid
        WHERE t.field_format = %s
        AND (tc.tid IS NULL OR tc.compressed = 0)
        LIMIT %d',
        'base64',
        $limit
      )
    );

    return $results ?: [];
  }


}
