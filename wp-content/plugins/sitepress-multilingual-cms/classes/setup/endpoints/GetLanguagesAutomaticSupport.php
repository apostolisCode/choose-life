<?php

namespace WPML\Setup\Endpoint;

use WPML\Ajax\IHandler;
use WPML\Collect\Support\Collection;
use WPML\Element\API\Languages;
use WPML\FP\Either;
use WPML\FP\Obj;
use WPML\TM\API\ATE\LanguageMappings;
use WPML\TM\ATE\AutomaticTranslationCapabilities;

class GetLanguagesAutomaticSupport implements IHandler {

	const REASON_MANUAL_ONLY     = 'manual-only';
	const REASON_SAME_AS_DEFAULT = 'same-as-default';

	public function run( Collection $data ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return Either::left( 'forbidden' );
		}

		$input = array_map(
			function ( $lang ) {
				$lang = (array) $lang;

				return [ 'code' => isset( $lang['code'] ) ? (string) $lang['code'] : '' ];
			},
			array_values( (array) Languages::getActive() )
		);

		$capabilities = AutomaticTranslationCapabilities::withCapabilityInfo( $input );
		$defaultCode  = (string) Languages::getDefaultCode();

		$support = [];
		foreach ( (array) $capabilities as $lang ) {
			$code = (string) Obj::propOr( '', 'code', $lang );
			if ( '' === $code ) {
				continue;
			}

			$supported = (bool) Obj::propOr( false, 'can_be_translated_automatically', $lang );

			$support[ $code ] = [
				'supported' => $supported,
				'reason'    => $supported
					? null
					: (
						LanguageMappings::resolvesToSameAteLanguageAsDefault( $code, $defaultCode )
							? self::REASON_SAME_AS_DEFAULT
							: self::REASON_MANUAL_ONLY
					),
			];
		}

		return Either::right( $support );
	}
}
