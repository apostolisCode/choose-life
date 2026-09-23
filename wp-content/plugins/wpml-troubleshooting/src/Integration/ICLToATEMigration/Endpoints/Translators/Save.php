<?php

namespace WPML\Troubleshooting\Integration\ICLToATEMigration\Endpoints\Translators;

use WPML\Ajax\IHandler;
use WPML\Ajax\Authorization\Authorized;
use WPML\Troubleshooting\Integration\ICLToATEMigration\Endpoints\AuthorizedForTranslationManagers;
use WPML\Collect\Support\Collection;
use WPML\FP\Either;

use WPML\FP\Fns;
use WPML\FP\Left;
use WPML\FP\Lst;
use WPML\FP\Obj;
use WPML\FP\Logic;
use WPML\Element\API\Languages;

use WPML\FP\Right;
use function WPML\Container\make;
use function WPML\FP\invoke;
use function WPML\FP\partialRight;
use WPML\TranslationRoles\SaveUser;
use WPML_Language_Pair_Records;

use function WPML\FP\pipe;

class Save extends SaveUser implements Authorized {

	use AuthorizedForTranslationManagers;


	public function run( Collection $data ) {
		return Either::of( $data )
			->map( Fns::map( $this->createTranslator() ) )
			->chain( $this->syncAteIfRequired() )
			->chain( $this->handleErrors() );
	}

	private function createTranslator() {
		return function( $translator ) {
			$handleError = function($error) use ($translator) {
				return [ 'translator' => $translator, 'error' => $error ];
			};
			$translator = \wpml_collect( $translator );

			return self::getUser( \wpml_collect( $translator ) )
				->map( Fns::tap( invoke( 'add_cap' )->with( \WPML\LIB\WP\User::CAP_TRANSLATE ) ) )
				->map( Obj::prop( 'ID' ) )
				->map( Fns::tap( partialRight( [ make( WPML_Language_Pair_Records::class ), 'store_active' ], $translator->get( 'languagePairs' ) ) ) )
				->bimap( $handleError, Fns::always( $translator->get( 'user' )['email'] ) );
		};
	}

	private function syncAteIfRequired() {
		return function( $translatorResults ) {
			foreach( $translatorResults as $translatorResult ) {
				if ( $translatorResult instanceof Right ) {
					do_action( 'wpml_update_translator' );
					break;
				}
			}
			return $translatorResults;
		};
	}

	private function handleErrors() {
		return function( $translatorResults ) {
			$getErrorMsg     = pipe( invoke( 'coalesce' )->with( Fns::identity(), Fns::identity() ), invoke( 'get' ) );
			$getErrorDetails = pipe( Fns::filter( Fns::isLeft() ), Fns::map( $getErrorMsg ) );
			$errorDetails    = $getErrorDetails( $translatorResults );

			if ( count( $errorDetails ) ) {
				return Either::left( [
					'message' => __( 'There was an error when saving the following translators:', 'wpml-troubleshooting' ),
					'details' => $errorDetails
				] );
			}
			return Either::right( __( 'The translators were saved successfully.', 'wpml-troubleshooting' ) );
		};
	}

}

