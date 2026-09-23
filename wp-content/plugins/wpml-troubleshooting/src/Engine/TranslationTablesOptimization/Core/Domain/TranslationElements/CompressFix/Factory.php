<?php

namespace WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\TranslationElements\CompressFix;

use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\MigrationDataService\CompletedRecordsStorageInterface;
use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\MigrationDataService\Factory as BaseFactory;
use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\MigrationDataService\ProcessorInterface;
use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\MigrationDataService\QueryInterface;

interface Factory extends BaseFactory {


  public function createQuery(): QueryInterface;


  public function createCompletedRecordsStorage(): CompletedRecordsStorageInterface;


  public function createProcessor(): ProcessorInterface;


}
