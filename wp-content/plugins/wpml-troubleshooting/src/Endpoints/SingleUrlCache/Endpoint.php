<?php

namespace WPML\Troubleshooting\Endpoints\SingleUrlCache;

use WPML\Ajax\Authorization\Authorized;
use WPML\Troubleshooting\Endpoints\AuthorizedForTroubleshooting;
use WPML\FP\Either;

abstract class Endpoint implements Authorized {

	use AuthorizedForTroubleshooting;

	protected function canManage() {
		return current_user_can( 'wpml_manage_troubleshooting' );
	}

	protected function getService() {
		global $wpml_dic;

		return $wpml_dic->make( \WPML_Single_Url_Cache_Admin_Service::class );
	}

	protected function permissionDenied() {
		return Either::left( __( 'You do not have permission to manage troubleshooting tools.', 'wpml-troubleshooting' ) );
	}

	protected function failure( \Throwable $error ) {
		\WPML\PHP\Logger\error(
			static::class . ' failed: ' . get_class( $error ) . ': ' . $error->getMessage()
		);

		return Either::left( 'Internal server error.' );
	}
}
