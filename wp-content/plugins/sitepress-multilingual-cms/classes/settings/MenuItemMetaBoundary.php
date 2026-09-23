<?php

namespace WPML\TM\Settings;

use WPML\Core\Component\CustomFieldPreferences\Domain\ElementType;

class MenuItemMetaBoundary {

	const SYNC_OWNED_META_PREFIX = '_menu_item_';

	public static function withoutSyncOwnedKeys( array $metaKeys ) {
		return array_values( array_filter( $metaKeys, [ self::class, 'mayCopy' ] ) );
	}

	public static function mayCopy( $metaKey ) {
		$metaKey = (string) $metaKey;

		if ( 0 !== strpos( $metaKey, self::SYNC_OWNED_META_PREFIX ) ) {
			return true;
		}

		return null !== PreferenceSource::getSource( $metaKey, ElementType::POST );
	}
}
