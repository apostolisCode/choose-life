<?php

namespace WPML\TM\Settings;

use WPML\Core\Component\CustomFieldPreferences\Domain\ElementType;
use WPML\Infrastructure\WordPress\Component\CustomFieldPreferences\ContainerFreeServices;

class LegacyChecksumOrders {

	private static $storageOrder = [];

	public function forNames( array $names ) {
		$names = array_values( array_map( 'strval', $names ) );
		if ( count( $names ) < 2 ) {
			return [];
		}

		$byHash = $names;
		usort(
			$byHash,
			function ( $a, $b ) {
				return strcmp( sha1( $a ), sha1( $b ) );
			}
		);

		$byName = $names;
		sort( $byName, SORT_STRING );

		$distinct = [];
		foreach ( [ $names, $this->inStorageOrder( $names ), $byHash, $byName ] as $order ) {
			$distinct[ implode( "\0", $order ) ] = $order;
		}

		return array_values( $distinct );
	}

	private function inStorageOrder( array $names ) {
		$remaining = $names;
		$ordered   = [];
		foreach ( $this->storageOrder() as $name ) {
			$index = array_search( $name, $remaining, true );
			if ( false !== $index ) {
				$ordered[] = $name;
				unset( $remaining[ $index ] );
			}
		}

		return array_merge( $ordered, array_values( $remaining ) );
	}

	protected function storageOrder() {
		$blog = get_current_blog_id();
		if ( ! isset( self::$storageOrder[ $blog ] ) ) {
			self::$storageOrder[ $blog ] = $this->readStorageOrder();
		}

		return self::$storageOrder[ $blog ];
	}

	private function readStorageOrder() {
		if ( ! ContainerFreeServices::state()->isMigrated() ) {
			return [];
		}

		global $wpdb;

		$names = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT name FROM {$wpdb->prefix}icl_meta_settings WHERE element_type = %s ORDER BY id",
				ElementType::POST
			)
		);

		return is_array( $names ) ? array_map( 'strval', $names ) : [];
	}

	public static function reset() {
		self::$storageOrder = [];
	}
}
