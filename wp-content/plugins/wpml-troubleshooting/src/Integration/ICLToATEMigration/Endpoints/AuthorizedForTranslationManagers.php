<?php

namespace WPML\Troubleshooting\Integration\ICLToATEMigration\Endpoints;

use WPML\Collect\Support\Collection;

trait AuthorizedForTranslationManagers {

	public function authorize( Collection $data ) {
		return current_user_can( 'manage_options' ) || current_user_can( 'manage_translations' );
	}
}
