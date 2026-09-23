<?php

namespace WPML\Troubleshooting\Integration\TranslationManagement;

use WPML\API\Sanitize;

class ResetPreferredTranslationService implements \IWPML_Backend_Action {

	const ACTION_ID = 'wpml-tm-reset-preferred-translation-service';

	public function add_hooks() {
		add_action( 'after_setup_complete_troubleshooting_functions', [ $this, 'displayButton' ], 11 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueScripts' ] );
		\WPML\Request\Adapter\Ajax::register( self::ACTION_ID, \WPML\Request\Policy\Policy::capability( 'manage_options', \WPML\Request\Policy\Authenticity::actionNonce( self::ACTION_ID, 'nonce' ) ), [ $this, 'resetAndFetchPreferredTS' ] );
	}

	public function displayButton() {
		$resetTitle   = sprintf( __( 'Reset preferred translation service', 'wpml-troubleshooting' ) );
		$resetMessage = sprintf( __( 'Reset and fetch  local preferred translation service to use Preferred Translation Service configured on WPML.org account.', 'wpml-troubleshooting' ) );
		$resetButton  = sprintf( __( 'Reset & Fetch', 'wpml-troubleshooting' ) );

		$html = '<div class="icl_cyan_box" id="wpml_tm_reset_preferred_translation_service_btn">' .
		        wp_nonce_field( self::ACTION_ID, 'wpml_tm_reset_preferred_translation_service_nonce', true, false ) .
		        '<h3>' . $resetTitle . '</h3>
				<p>' . $resetMessage . '</p>
				<a class="button-primary" href="#">' . $resetButton . '</a><span class="spinner"></span>
				</div>';

		echo $html;
	}

	public function resetAndFetchPreferredTS() {
		if ( \WPML\Setup\Initializer::rejectSettingsMutationAjax() ) {
			return;
		}

		$action = Sanitize::stringProp( 'action', $_POST );
		$nonce  = Sanitize::stringProp( 'nonce', $_POST );

		if ( $nonce && $action && wp_verify_nonce( $nonce, $action ) && current_user_can( 'manage_options' ) ) {
			$settings = OTGS_Installer()->get_settings();

			if ( isset( $settings['repositories']['wpml']['ts_info'] ) ) {
				$settings['repositories']['wpml']['ts_info']['preferred'] = null;
				OTGS_Installer()->save_settings( $settings );
				OTGS_Installer()->refresh_subscriptions_data();
			}
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}

	public function enqueueScripts( $page ) {
		if ( WPML_PLUGIN_FOLDER . '/menu/troubleshooting.php' === $page ) {
			wp_enqueue_script(
				self::ACTION_ID,
				WPML_TROUBLESHOOTING_URL . '/res/js/reset-preferred-ts.js',
				[ 'jquery' ],
				ICL_SITEPRESS_SCRIPT_VERSION
			);
		}
	}
}
