<?php

use WPML\API\Sanitize;

class WPML_TM_Troubleshooting_Clear_TS extends WPML_TM_AJAX_Factory_Obsolete {
	private $script_handle = 'wpml_clear_ts';

	public function __construct( &$wpml_wp_api ) {
		parent::__construct( $wpml_wp_api );

		$this->add_ajax_action(
			'wpml_clear_ts',
			array( $this, 'clear_ts_action' ),
			\WPML\Request\Policy\Policy::capability(
				array( 'wpml_manage_troubleshooting', 'manage_translations' ),
				\WPML\Request\Policy\Authenticity::actionNonce( 'wpml_clear_ts', 'nonce' )
			)
		);
		$this->init();
	}

	public function clear_ts_action() {
		if ( \WPML\Setup\Initializer::rejectSettingsMutationAjax() ) {
			return;
		}

		$action              = Sanitize::stringProp( 'action', $_POST );
		$wpml_clear_ts_nonce = Sanitize::stringProp( 'nonce', $_POST );
		if ( $action && $wpml_clear_ts_nonce && wp_verify_nonce( $wpml_clear_ts_nonce, $action ) ) {
			$this->clear_tp_default_suid();
			/* translators: Answer returned when a job on the Troubleshooting screen went through. */
			return $this->wpml_wp_api->wp_send_json_success( __( 'Ok!', 'wpml-troubleshooting' ) );
		} else {
			return $this->wpml_wp_api->wp_send_json_error( __( "You can't do that!", 'wpml-troubleshooting' ) );
		}
	}

	protected function clear_tp_default_suid() {
		TranslationProxy::clear_preferred_translation_service();
	}

	public function enqueue_resources( $hook_suffix ) {
		if ( $this->wpml_wp_api->is_troubleshooting_page() ) {
			$this->register_resources();
			$strings = array(
				'placeHolder' => $this->script_handle,
				'action'      => $this->script_handle,
				'nonce'       => wp_create_nonce( $this->script_handle ),
			);
			wp_localize_script( $this->script_handle, $this->script_handle . '_strings', $strings );
			wp_enqueue_script( $this->script_handle );
		}
	}

	public function register_resources() {
		wp_register_script( $this->script_handle, WPML_TROUBLESHOOTING_URL . '/res/js/clear-preferred-ts.js', array( 'jquery', 'jquery-ui-dialog' ), ICL_SITEPRESS_SCRIPT_VERSION, true );
	}

}
