<?php

namespace WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\MigrationDataService;

interface QueryInterface {


  public function countRemaining(): int;


  public function getRemaining( int $limit ): array;


}
