<?php

namespace WPML\TM\ATE\TranslateEverything;

use WPML\API\PostTypes;

class OfferedTypeDefaults {

	public function persist() {
		PostTypesSinceRepositoryFactory::create()
			->fillMissingSinceDates( PostTypes::getAutomaticTranslatable() );
	}
}
