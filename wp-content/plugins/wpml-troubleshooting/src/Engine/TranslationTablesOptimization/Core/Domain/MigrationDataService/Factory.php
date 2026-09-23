<?php

namespace WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\MigrationDataService;

interface Factory {


  public function createQuery(): QueryInterface;


  public function createCompletedRecordsStorage(): CompletedRecordsStorageInterface;


  public function createProcessor(): ProcessorInterface;


  public function createMarkAsCompletedFunction(): callable;


}
