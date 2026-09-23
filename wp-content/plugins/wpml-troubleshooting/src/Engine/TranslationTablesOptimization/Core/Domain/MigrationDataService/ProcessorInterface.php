<?php

namespace WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\MigrationDataService;

interface ProcessorInterface {


  public function process( array $records ): array;


}
