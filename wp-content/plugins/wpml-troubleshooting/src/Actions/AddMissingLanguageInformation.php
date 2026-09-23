<?php

namespace WPML\Troubleshooting\Actions;

class AddMissingLanguageInformation {

	public function run() {
		global $iclTranslationManagement;
		$iclTranslationManagement->add_missing_language_information();
	}
}
