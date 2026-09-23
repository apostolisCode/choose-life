<?php

namespace WPML\Utilities;

class AdvisoryLockFactory {

	public function create( string $name ): AdvisoryLock {
		global $wpdb;

		return new AdvisoryLock( $wpdb, $name );
	}
}
