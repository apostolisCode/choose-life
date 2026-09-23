<?php

namespace WPML\CF7;

use WPML\FP\Obj;
use WPML\LIB\WP\Hooks;
use function WPML\FP\spreadArgs;

class TranslationReview implements \IWPML_Frontend_Action {

	public function add_hooks() {
		Hooks::onAction( 'wpml_tm_handle_translation_review', 10, 2 )
			->then( spreadArgs( [ $this, 'handleTranslationReview' ] ) );
	}

	public function handleTranslationReview( $jobId, $post ) {
		if ( Constants::POST_TYPE === Obj::prop( 'post_type', $post ) ) {
			Hooks::onFilter( 'template_include' )
				->then( $this->previewFormTranslation( $post ) );
		}
	}

	public function previewFormTranslation( $post ) {
		return function() use ( $post ) {
			get_header();
			echo do_shortcode( '[contact-form-7 id="' . $post->ID . '"]' );
			get_footer();
			return null;
		};
	}
}
