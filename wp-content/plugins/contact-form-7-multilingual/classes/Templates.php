<?php

namespace WPML\CF7;

use WPML\LIB\WP\Hooks;

use function WPML\FP\spreadArgs;

class Templates implements \IWPML_Backend_Action {

	public function add_hooks() {
		Hooks::onFilter( 'wpcf7_default_template', 10, 2 )
			->then( spreadArgs( [ $this, 'translateDefaultMessages' ] ) );
	}

	public function translateDefaultMessages( $messages, $prop ) {
		if ( 'messages' === $prop ) {
			$language = apply_filters( 'wpml_current_language', false );
			foreach ( $messages as $code => $message ) {
				$messages[ $code ] = apply_filters( 'wpml_translate_single_string', $message, WPCF7_TEXT_DOMAIN, $message, $language );
			}
		}

		return $messages;
	}

}
