<?php

namespace WPML\Troubleshooting\Ajax;

use WPML_Fix_Type_Assignments;

class RepairTypeAssignmentsAjaxController {

	const NONCE_ACTION = 'broken_type_nonce';
	const NONCE_FIELD  = 'icl_nonce';

	public function handle() {
		global $sitepress;

		$nonce = isset( $_GET[ self::NONCE_FIELD ] )
			? sanitize_text_field( wp_unslash( $_GET[ self::NONCE_FIELD ] ) )
			: '';

		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			/* translators: Error message returned when a request from the browser cannot be trusted and is turned away. */
			wp_send_json_error( esc_html__( 'Invalid request!', 'wpml-troubleshooting' ) );
		}

		$lang_setter = new WPML_Fix_Type_Assignments( $sitepress );
		$rows_fixed  = $lang_setter->run();

		wp_send_json_success( $rows_fixed );
	}
}
