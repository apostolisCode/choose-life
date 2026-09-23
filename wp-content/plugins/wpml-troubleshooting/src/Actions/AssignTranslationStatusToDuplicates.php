<?php

namespace WPML\Troubleshooting\Actions;

use WPML\Troubleshooting\Engine\AssignTranslationStatusToDuplicates as Service;

class AssignTranslationStatusToDuplicates {

	public function run() {
		$updated_items = Service::run();
		echo wp_json_encode( array( 'updated' => $updated_items ) );
	}
}
