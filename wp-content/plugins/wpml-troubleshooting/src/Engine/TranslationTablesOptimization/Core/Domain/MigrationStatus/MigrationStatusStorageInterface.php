<?php

namespace WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\MigrationStatus;

interface MigrationStatusStorageInterface {


  public function read(): MigrationStatus;


  public function write( MigrationStatus $migrationStatus );


}
