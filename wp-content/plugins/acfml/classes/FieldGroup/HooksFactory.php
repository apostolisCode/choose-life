<?php

namespace ACFML\FieldGroup;

class HooksFactory implements \IWPML_Backend_Action_Loader {

	public function create() {
		$fieldNamePatterns = new FieldNamePatterns();

		return [
			new UIHooks(),
			new SaveHooks(
				$fieldNamePatterns,
				new DetectNonTranslatableLocations(),
				new \ACFML\Notice\FieldNameCollisions( new NameCollisions( $fieldNamePatterns ) )
			),
			new CptLockHooks(),
			new SettingsLockHooks( $fieldNamePatterns ),
			new TranslationModeColumnHooks(),
			new TranslationEditorHooks(),
		];
	}
}
