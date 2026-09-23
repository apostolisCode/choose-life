<?php

use WPML\Upgrade\CommandsStatus;

class WPML_Upgrade_Loader_Factory implements IWPML_Backend_Action_Loader, IWPML_Frontend_Action_Loader, IWPML_CLI_Action_Loader {

	public function create() {
		global $sitepress;

		if ( $this->is_settled_front_end_request() ) {
			return null;
		}

		return new WPML_Upgrade_Loader(
			$sitepress,
			wpml_get_upgrade_schema(),
			wpml_load_settings_helper(),
			wpml_get_admin_notices(),
			wpml_get_upgrade_command_factory()
		);
	}

	private function is_settled_front_end_request() {
		if ( is_admin() || wpml_is_cli() ) {
			return false;
		}

		return ( new CommandsStatus() )->isFrontEndScopeSettled( ICL_SITEPRESS_VERSION );
	}
}
