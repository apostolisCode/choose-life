<?php

class WPML_TM_Upgrade_Translation_Priorities_For_Posts implements IWPML_Upgrade_Command {

	private $result = true;

	const TRANSLATION_PRIORITY_TAXONOMY = 'translation_priority';

	private function run() {
		$lock = \WPML\Container\make( \WPML\Utilities\AdvisoryLockFactory::class )->create( 'tm_prio_upgrade' );
		if ( ! $lock->acquire( 0 ) ) {
			return false;
		}

		try {
			$translation_priorities_factory = new WPML_TM_Translation_Priorities_Factory();
			$translation_priorities_actions = $translation_priorities_factory->create();
			$translation_priorities_actions->register_translation_priority_taxonomy();

			WPML_TM_Translation_Priorities::insert_missing_default_terms();
			WPML_TM_Translation_Priorities::insert_missing_term_relationship();
		} finally {
			$lock->release();
		}

		return $this->result;
	}


	public function run_admin() {
		return $this->run();
	}

	public function run_ajax() {
		return false;
	}

	public function run_frontend() {
		return false;
	}

	public function get_results() {
		return $this->result;
	}
}
