<?php

namespace ACFML\Upgrade;

use ACFML\Upgrade\Commands\CollapseSubfieldSettings;
use ACFML\Upgrade\Commands\MigrateToV2;
use ACFML\Upgrade\Commands\MigrateToV2_1;
use ACFML\Upgrade\Commands\MigrateToV2_2;
use ACFML\Upgrade\Commands\RegisterMissingStrings;

class CommandsProvider {

	public static function get() {
		return wpml_collect( [
			MigrateToV2::class,
			MigrateToV2_1::class,
			MigrateToV2_2::class,
			CollapseSubfieldSettings::class,
			RegisterMissingStrings::class,
		] );
	}

	public static function getHash() {
		return md5( self::get()->implode( ',' ) );
	}
}
