<?php

namespace WPML\Troubleshooting\Endpoints\OptimizeDbTables;

use WPML\Ajax\Authorization\Authorized;
use WPML\Ajax\IHandler;
use WPML\Collect\Support\Collection;
use WPML\FP\Either;
use WPML\Troubleshooting\Endpoints\AuthorizedForTroubleshooting;
use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Application\Service\EntryPointService;

class Endpoint implements IHandler, Authorized {

	use AuthorizedForTroubleshooting;


	public function run( Collection $data ) {
		global $wpml_dic;

		$service   = $wpml_dic->make( EntryPointService::class );
		$isInitialRequest = $data->get( 'isInitialRequest', false );
		$remaining = $service->run( $data->get( 'migrationType' ), $isInitialRequest );

		return Either::of( $remaining );
	}


}