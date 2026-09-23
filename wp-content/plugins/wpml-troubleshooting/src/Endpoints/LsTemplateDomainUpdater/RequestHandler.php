<?php

namespace WPML\Troubleshooting\Endpoints\LsTemplateDomainUpdater;

use WPML\Ajax\Authorization\Authorized;
use WPML\Ajax\IHandler;
use WPML\Collect\Support\Collection;
use WPML\FP\Either;
use WPML\Troubleshooting\Endpoints\AuthorizedForTroubleshooting;
use WPML\Troubleshooting\Engine\LsTemplateDomainUpdater;

class RequestHandler implements IHandler, Authorized {

	use AuthorizedForTroubleshooting;

	private $updater;

	public function __construct( LsTemplateDomainUpdater $updater ) {
		$this->updater = $updater;
	}

	public function run( Collection $data ) {
		try {
			$this->updater->runUpdate();

			return Either::of( [ 'success' => true ] );
		} catch ( \Exception $e ) {
			return Either::left( $e->getMessage() );
		}
	}

}
