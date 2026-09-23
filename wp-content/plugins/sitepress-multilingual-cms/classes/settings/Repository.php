<?php

namespace WPML\TM\Settings;

use WPML\Core\Component\CustomFieldPreferences\Domain\ElementType;

class Repository {

	public static function getCustomFieldsToTranslate() {
		return array_values( array_filter(
			PreferenceResolver::namesByMode( ElementType::POST, WPML_TRANSLATE_CUSTOM_FIELD )
		) );
	}

	public static function getCustomFields() {
		return \wpml_collect( PreferenceResolver::map( ElementType::POST ) )
			->filter( function ( $value, $key ) {
				return (bool) $key;
			} )->toArray();
	}
}