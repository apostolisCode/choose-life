<?php

namespace WPML\Troubleshooting\Engine\TranslationTablesOptimization\Infrastructure\Domain\TranslationElements\Compress;

use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Application\Service\MigrationStatus\MigrationStatusService;
use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\MigrationDataService\CompletedRecordsStorageInterface;
use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\MigrationDataService\ProcessorInterface;
use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\MigrationDataService\QueryInterface;
use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\TranslationElements\Compress\Factory as CompressFactory;
use WPML\Core\Port\Persistence\DatabaseSchemaInfoInterface;

class Factory implements CompressFactory {

  private $databaseSchemaInfo;

  private $wpdb;

  private $migrationStatusService;


  public function __construct(
    DatabaseSchemaInfoInterface $databaseSchemaInfo,
    $wpdb,
    MigrationStatusService $migrationStatusService
  ) {
    $this->databaseSchemaInfo = $databaseSchemaInfo;
    $this->wpdb               = $wpdb;
    $this->migrationStatusService = $migrationStatusService;
  }


  public function createQuery(): QueryInterface {
    return new Query(
      $this->wpdb
    );
  }


  public function createCompletedRecordsStorage(): CompletedRecordsStorageInterface {
    return new CompletedRecordsStorage(
      $this->wpdb,
      $this->databaseSchemaInfo
    );
  }


  public function createProcessor(): ProcessorInterface {
    return new Processor(
      $this->wpdb
    );
  }


  public function createMarkAsCompletedFunction(): callable {
    return [$this->migrationStatusService, 'markTranslationElementsCompressionCompleted'];
  }


}
