<?php

namespace WPML\Troubleshooting\Engine\TranslationTablesOptimization\Infrastructure\Domain\TranslationElements\RemoveOld;

use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\MigrationDataService\ProcessorInterface;

class Processor implements ProcessorInterface {

  private $wpdb;


  public function __construct( $wpdb ) {
    $this->wpdb = $wpdb;
  }


  public function process( array $records ): array {
    $processed = [];
    $wpdb      = $this->wpdb;

    if ( 1 !== preg_match( '/^[A-Za-z0-9_]+$/D', $wpdb->prefix ) ) {
      return $processed;
    }

    foreach ( $records as $record ) {
      $wpdb->query(
        $wpdb->prepare(
          "DELETE t
			FROM {$wpdb->prefix}icl_translate t
			INNER JOIN (
				SELECT job_id
				FROM {$wpdb->prefix}icl_translate_job
				WHERE rid = %d
					AND job_id < (
						SELECT MAX(job_id)
						FROM {$wpdb->prefix}icl_translate_job
						WHERE rid = %d AND translated = 1
					)
			) to_delete ON t.job_id = to_delete.job_id",
          $record['rid'],
          $record['rid']
        )
      );
      $processed[] = $record['rid'];
    }

    return $processed;
  }


}
