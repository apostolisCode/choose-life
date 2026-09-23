<?php

namespace WPML\Troubleshooting\Endpoints\SingleUrlCache;

use WPML\Ajax\IHandler;
use WPML\Collect\Support\Collection;
use WPML\FP\Either;

class Rebuild extends Endpoint implements IHandler {

	public function run( Collection $data ) {
		if ( ! $this->canManage() ) {
			return $this->permissionDenied();
		}

		try {
			$service = $this->getService();
			$service->rebuild();

			return Either::of( $service->get_status() );
		} catch ( \Throwable $error ) {
			return $this->failure( $error );
		}
	}
}
