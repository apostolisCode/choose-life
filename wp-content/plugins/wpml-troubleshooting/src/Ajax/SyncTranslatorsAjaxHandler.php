<?php

namespace WPML\Troubleshooting\Ajax;

use IWPML_Backend_Action;

class SyncTranslatorsAjaxHandler implements IWPML_Backend_Action {

	const NONCE_ACTION = 'wpml_support_ate_sync';

	public function add_hooks() {
		\WPML\Request\Adapter\Ajax::register( 'wpml_support_ate_sync_translators', \WPML\Request\Policy\Policy::capability( [ 'wpml_manage_support', 'manage_options' ], \WPML\Request\Policy\Authenticity::actionNonce( 'wpml_support_ate_sync', 'nonce' ) ), array( $this, 'handle_translators' ) );
		\WPML\Request\Adapter\Ajax::register( 'wpml_support_ate_sync_managers', \WPML\Request\Policy\Policy::capability( [ 'wpml_manage_support', 'manage_options' ], \WPML\Request\Policy\Authenticity::actionNonce( 'wpml_support_ate_sync', 'nonce' ) ), array( $this, 'handle_managers' ) );
	}

	public function handle_translators() {
		if ( \WPML\Setup\Initializer::rejectSettingsMutationAjax() ) {
			return;
		}

		if ( ! $this->is_authorized() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
		}

		do_action( 'wpml_tm_ate_synchronize_translators' );
		wp_send_json_success();
	}

	public function handle_managers() {
		if ( \WPML\Setup\Initializer::rejectSettingsMutationAjax() ) {
			return;
		}

		if ( ! $this->is_authorized() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
		}

		do_action( 'wpml_tm_ate_synchronize_managers' );
		wp_send_json_success();
	}

	private function is_authorized(): bool {
		if ( ! current_user_can( 'wpml_manage_support' ) && ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['nonce'] ) ) : '';
		return (bool) wp_verify_nonce( $nonce, self::NONCE_ACTION );
	}
}
