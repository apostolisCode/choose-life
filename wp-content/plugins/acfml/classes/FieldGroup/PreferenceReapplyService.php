<?php

namespace ACFML\FieldGroup;

class PreferenceReapplyService {

	const CLASS_NAME = '\WPML\TM\Settings\PreferenceReapply';

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
