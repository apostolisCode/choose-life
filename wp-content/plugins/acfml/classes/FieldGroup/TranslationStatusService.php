<?php

namespace ACFML\FieldGroup;

class TranslationStatusService {

	const CLASS_NAME = '\WPML\TM\Settings\ProcessNewTranslatableFields';

	public static function get() {
		if ( ! class_exists( self::CLASS_NAME ) ) {
			return null;
		}

		try {
			return \WPML\Container\make( self::CLASS_NAME );
		} catch ( \Throwable $e ) {
			return null;
		}
	}
}
