<?php

namespace WPML\Troubleshooting\Endpoints;

use WPML\Collect\Support\Collection;

trait AuthorizedForTroubleshooting {

	public function authorize( Collection $data ) {
		return current_user_can( 'manage_options' ) || current_user_can( 'wpml_manage_troubleshooting' );
	}
}
