<?php

namespace WPML\Troubleshooting\Engine\TranslationTablesOptimization\Infrastructure\Domain\TranslationElements\Compress;

use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\MigrationDataService\CompletedRecordsStorageInterface;
use WPML\Core\Port\Persistence\DatabaseSchemaInfoInterface;
use WPML\Core\Port\Persistence\Exception\DatabaseErrorException;
use WPML\PHP\Exception\InvalidArgumentException;

class CompletedRecordsStorage implements CompletedRecordsStorageInterface {

  private $wpdb;

  const TMP_TABLE_NAME = 'icl_translate_compressed';

  private $databaseSchemaInfo;


  public function __construct( $wpdb, DatabaseSchemaInfoInterface $databaseSchemaInfo ) {
    $this->wpdb               = $wpdb;
    $this->databaseSchemaInfo = $databaseSchemaInfo;
  }


  public function create() {
    try {
      if ( ! $this->databaseSchemaInfo->doesTableExist( self::TMP_TABLE_NAME ) ) {
        $wpdb = $this->wpdb;

        $this->wpdb->query(
          "CREATE TABLE {$wpdb->prefix}icl_translate_compressed (
            tid BIGINT UNSIGNED NOT NULL,
            compressed TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (tid)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
      }
    } catch ( DatabaseErrorException $e ) {
      return;
    } catch ( InvalidArgumentException $e ) {
      return;
    }
  }


  public function delete() {
    $wpdb = $this->wpdb;

    $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}icl_translate_compressed" );
  }


  public function markAsCompleted( array $recordIds ) {
    if ( empty( $recordIds ) ) {
      return;
    }

    $wpdb = $this->wpdb;
    $ids  = array_map( 'intval', $recordIds );

    $wpdb->query(
      $wpdb->prepare(
        "INSERT INTO {$wpdb->prefix}icl_translate_compressed (tid, compressed)
        VALUES " . implode( ', ', array_fill( 0, count( $ids ), '(%d, 1)' ) ) . "
        ON DUPLICATE KEY UPDATE compressed = 1",
        $ids
      )
    );
  }


}
