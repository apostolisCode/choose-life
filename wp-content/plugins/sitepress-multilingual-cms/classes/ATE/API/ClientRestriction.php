<?php

namespace WPML\TM\ATE\API;

use WPML\FP\Obj;
use WP_Error;

class ClientRestriction {

	const ERROR_CODE  = 'CLIENT_RESTRICTED';
	const HTTP_STATUS = 428;

	private function __construct() {
	}

	public static function fromAteError( $error ) {
		if ( ! $error instanceof WP_Error ) {
			return null;
		}

		if ( (int) $error->get_error_code() !== self::HTTP_STATUS ) {
			return null;
		}

		$errors = $error->get_error_data( $error->get_error_code() );
		if ( ! is_array( $errors ) ) {
			return null;
		}

		foreach ( $errors as $entry ) {
			if ( self::ERROR_CODE === Obj::propOr( '', 'error_code', (array) $entry ) ) {
				return new self();
			}
		}

		return null;
	}

	public static function fromArray( array $data ) {
		return new self();
	}

	public function toArray() {
		return [ 'restricted' => true ];
	}
}
