<?php

namespace WPML\CF7;

use WPML\FP\Obj;

class Shortcodes implements \IWPML_Frontend_Action {

	public function add_hooks() {
		add_filter( 'shortcode_atts_wpcf7', [ $this, 'translate_shortcode_form_id' ] );
	}

	public function translate_shortcode_form_id( $atts ) {
		$form         = null;
		$formIdOrHash = Obj::prop('id', $atts );;
		$formTitle    = Obj::prop('title', $atts );

		if ( $formIdOrHash && function_exists( 'wpcf7_get_contact_form_by_hash' ) ) {
			$form = wpcf7_get_contact_form_by_hash( trim( $formIdOrHash ) );
		}

		if ( ! $form && $formIdOrHash ) {
			$form = wpcf7_contact_form( (int) $formIdOrHash );
		}

		if ( ! $form && $formTitle ) {
			$formTitle = trim( $formTitle );
			$form      = wpcf7_get_contact_form_by_title( $formTitle );

			if ( ! $form ) {
				$form = $this->getFormByTitleInDefaultLanguage( $formTitle );
			}
		}

		if ( $form ) {
			$atts['id']    = apply_filters( 'wpml_object_id', $form->id(), Constants::POST_TYPE, true );
			$atts['title'] = '';
		}

		return $atts;
	}

	private function getFormByTitleInDefaultLanguage( $formTitle ) {
		$currentLanguage = apply_filters( 'wpml_current_language', null );
		$defaultLanguage = apply_filters( 'wpml_default_language', null );

		if ( ! $defaultLanguage || $defaultLanguage === $currentLanguage ) {
			return null;
		}

		do_action( 'wpml_switch_language', $defaultLanguage );

		try {
			return wpcf7_get_contact_form_by_title( $formTitle );
		} finally {
			do_action( 'wpml_switch_language', null );
		}
	}
}
