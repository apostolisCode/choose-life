<?php

namespace WPML\WPSEO\Shared\Upgrade;

class CommandsProvider {

	public static function get() {
		return wpml_collect(
			[
				Commands\DisableHeadLangs::class,
				Commands\TranslateExistingTermMeta::class,
				Commands\InvalidateTermIndexables::class,
				Commands\RepairTermMeta::class,
			]
		);
	}

	public static function getHash() {
		return md5( self::get()->implode( ',' ) );
	}
}
