<?php

namespace WPML\Troubleshooting\Engine;

class WPML_Troubleshoot_Action {

	const SYNC_POSTS_TAXONOMIES_SLUG = 'synchronize_posts_taxonomies';

	public function is_valid_request() {
		$response = false;

		if ( array_key_exists( 'nonce', $_POST ) && array_key_exists( 'debug_action', $_POST )
			 && self::SYNC_POSTS_TAXONOMIES_SLUG === $_POST['debug_action']
		) {
			if ( \WPML\Setup\Initializer::rejectSettingsMutationAjax() ) {
				return false;
			}

			$response = wp_verify_nonce( $_POST['nonce'], $_POST['debug_action'] );

			if ( ! $response ) {
				/* translators: Error message returned when a request from the browser cannot be trusted and is turned away. */
				wp_send_json_error( array( 'message' => esc_html__( 'Invalid nonce.', 'wpml-troubleshooting' ) ) );
				return $response;
			}
		}

		return $response;
	}
}
