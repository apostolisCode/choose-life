<?php

namespace WPML\Troubleshooting\Integration\ICLToATEMigration\Endpoints\Translators;

use WPML\Ajax\IHandler;
use WPML\Ajax\Authorization\Authorized;
use WPML\Troubleshooting\Integration\ICLToATEMigration\Endpoints\AuthorizedForTranslationManagers;
use WPML\Collect\Support\Collection;
use WPML\FP\Either;
use WPML\FP\Fns;
use WPML_TM_ATE_API;

class GetFromICL implements IHandler, Authorized {

	use AuthorizedForTranslationManagers;


	private $apiClient;

	public function __construct( WPML_TM_ATE_API $apiClient ) {
		$this->apiClient = $apiClient;
	}

	public function run( Collection $data ) {
		global $sitepress_settings;
		$translationServiceData = current( $sitepress_settings['icl_translation_projects'] );

		$result = $this->apiClient->import_icl_translators( $translationServiceData['ts_id'], $translationServiceData['ts_access_key'] );

		if ( Fns::isLeft( $result ) ) {
			return Either::left( __( 'Error happened! Please try again.', 'wpml-troubleshooting' ) );
		}

		return GetFromICLResponseMapper::map( $result->get()->records );
	}
}
