<?php

use WPML\API\Sanitize;

class WPML_TM_Troubleshooting_Reset_Pro_Trans_Config extends WPML_TM_AJAX_Factory_Obsolete {

	const SCRIPT_HANDLE = 'wpml_reset_pro_trans_config';

	private $wpdb;

	private $sitepress;
	private $translation_proxy;

	public function __construct( &$sitepress, &$translation_proxy, &$wpml_wp_api, &$wpdb ) {
		parent::__construct( $wpml_wp_api );

		$this->sitepress         = &$sitepress;
		$this->translation_proxy = &$translation_proxy;
		$this->wpdb              = &$wpdb;

		$this->add_ajax_action(
			'wpml_reset_pro_trans_config',
			array( $this, 'reset_pro_translation_configuration_action' ),
			\WPML\Request\Policy\Policy::capability(
				'wpml_manage_troubleshooting',
				\WPML\Request\Policy\Authenticity::actionNonce( 'wpml_reset_pro_trans_config', 'nonce' )
			)
		);
		$this->init();
	}


	public function register_resources() {
		wp_register_script( self::SCRIPT_HANDLE, WPML_TROUBLESHOOTING_URL . '/res/js/reset-pro-trans-config.js', array( 'jquery', 'jquery-ui-dialog' ), ICL_SITEPRESS_SCRIPT_VERSION, true );
	}

	public function enqueue_resources( $hook_suffix ) {
		if ( $this->wpml_wp_api->is_troubleshooting_page() ) {
			$this->register_resources();
			$translation_service_name = $this->translation_proxy->get_current_service_name();
			$strings                  = array(
				'placeHolder'  => 'icl_reset_pro',
				'reset'        => wp_create_nonce( 'reset_pro_translation_configuration' ),
				/* translators: Question asked before the work sent to a translation service is dropped. %1$s: the name of that service. */
				'confirmation' => sprintf( __( 'Are you sure you want to reset the %1$s translation process?', 'wpml-troubleshooting' ), $translation_service_name ),
				'action'       => self::SCRIPT_HANDLE,
				'nonce'        => wp_create_nonce( self::SCRIPT_HANDLE ),
			);
			wp_localize_script( self::SCRIPT_HANDLE, self::SCRIPT_HANDLE . '_strings', $strings );
			wp_enqueue_script( self::SCRIPT_HANDLE );
		}
	}

	public function reset_pro_translation_configuration_action() {
		if ( \WPML\Setup\Initializer::rejectSettingsMutationAjax() ) {
			return;
		}

		if ( ! current_user_can( 'wpml_manage_troubleshooting' ) ) {
			return $this->wpml_wp_api->wp_send_json_error( __( "You can't do that!", 'wpml-troubleshooting' ) );
		}

		$action = Sanitize::stringProp( 'action', $_POST );
		$nonce  = Sanitize::stringProp( 'nonce', $_POST );

		$valid_nonce = $nonce && $action && wp_verify_nonce( $nonce, $action );
		if ( $valid_nonce ) {
			return $this->wpml_wp_api->wp_send_json_success( $this->reset_pro_translation_configuration() );
		} else {
			return $this->wpml_wp_api->wp_send_json_error( __( "You can't do that!", 'wpml-troubleshooting' ) );
		}
	}

	public function reset_pro_translation_configuration() {
		$translation_service_name = $this->translation_proxy->get_current_service_name();

		$this->sitepress->set_setting( 'content_translation_languages_setup', false );
		$this->sitepress->set_setting( 'content_translation_setup_complete', false );
		$this->sitepress->set_setting( 'content_translation_setup_wizard_step', false );
		$this->sitepress->set_setting( 'translator_choice', false );
		$this->sitepress->set_setting( 'icl_lang_status', false );
		$this->sitepress->set_setting( 'icl_balance', false );
		$this->sitepress->set_setting( 'icl_support_ticket_id', false );
		$this->sitepress->set_setting( 'icl_current_session', false );
		$this->sitepress->set_setting( 'last_get_translator_status_call', false );
		$this->sitepress->set_setting( 'last_icl_reminder_fetch', false );
		$this->sitepress->set_setting( 'icl_account_email', false );
		$this->sitepress->set_setting( 'translators_management_info', false );
		$this->sitepress->set_setting( 'site_id', false );
		$this->sitepress->set_setting( 'access_key', false );
		$this->sitepress->set_setting( 'ts_site_id', false );
		$this->sitepress->set_setting( 'ts_access_key', false );

		global $wpdb;

		$sql_for_remote_rids = $wpdb->prepare(
			"FROM {$wpdb->prefix}icl_translation_status
            WHERE translation_service != 'local'
                AND translation_service != 0
				AND status IN ( %d, %d )",
			ICL_TM_WAITING_FOR_TRANSLATOR,
			ICL_TM_IN_PROGRESS
		);

		$remote_rids = $wpdb->get_col( "SELECT rid {$sql_for_remote_rids}" );

		WPML_Translation_Records_Delete::jobs_by_rids( (array) $remote_rids );

		$wpdb->query( "DELETE {$sql_for_remote_rids}" );

		\WPML\TM\TranslationProxy\TpBatchState::clear();
		$this->sitepress->set_setting( 'icl_html_status', false );
		$this->sitepress->set_setting( 'language_pairs', false );

		if ( ! $this->translation_proxy->has_preferred_translation_service() ) {
			$this->sitepress->set_setting( 'translation_service', false );
			$this->sitepress->set_setting( 'icl_translation_projects', false );
		}

		$this->sitepress->save_settings();

		$this->wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}icl_core_status" );
		$this->wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}icl_content_status" );
		$this->wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}icl_string_status" );
		$this->wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}icl_node" );
		$this->wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}icl_reminders" );

		if ( $this->translation_proxy->has_preferred_translation_service() && $translation_service_name ) {
			/* translators: Message shown after the work sent to a translation service was dropped. %1$s: the name of that service. */
			$confirm_message = sprintf( __( 'The translation process with %1$s was reset.', 'wpml-troubleshooting' ), $translation_service_name );
		} elseif ( $translation_service_name ) {
			/* translators: Message shown after the site was disconnected from a translation service. %1$s: the name of that service, in both places. "the translators tab" is a tab of the Translation Management screen. */
			$confirm_message = sprintf( __( 'Your site was successfully disconnected from %1$s. Go to the translators tab to connect a new %1$s account or use a different translation service.', 'wpml-troubleshooting' ), $translation_service_name );
		} else {
			$confirm_message = __( 'PRO translation has been reset.', 'wpml-troubleshooting' );
		}

		return $confirm_message;
	}
}
