<?php

namespace WPML\Troubleshooting\Integration\ICLToATEMigration\Endpoints;

use WPML\Ajax\IHandler;
use WPML\Ajax\Authorization\Authorized;
use WPML\Collect\Support\Collection;
use WPML\FP\Either;
use WPML\Troubleshooting\Integration\ICLToATEMigration\Data;

class DeactivateICL implements IHandler, Authorized {

	use AuthorizedForTranslationManagers;


	public function run( Collection $data ) {
		if ( \TranslationProxy::is_current_service_active_and_authenticated() ) {
			\TranslationProxy::deselect_active_service();

			global $sitepress_settings;
			$translationServiceData = current( $sitepress_settings['icl_translation_projects'] );

			Data::setICLDeactivated( true );
			Data::saveICLCredentials( $translationServiceData );
		}

		return Either::of( true );
	}
}