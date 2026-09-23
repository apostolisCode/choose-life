<?php

namespace WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\MigrationStatus;

interface PreliminaryConditionQueryInterface {


  public function hasNonNullTranslationPackages(): bool;


}
