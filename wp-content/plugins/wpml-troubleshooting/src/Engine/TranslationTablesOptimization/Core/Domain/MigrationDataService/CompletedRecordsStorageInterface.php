<?php

namespace WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\MigrationDataService;

interface CompletedRecordsStorageInterface {


  public function create();


  public function delete();


  public function markAsCompleted( array $recordIds );


}
