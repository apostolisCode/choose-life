<?php

namespace WPML\Upgrade\Commands;

use WPML\LanguageEditor\Presets\CatalogueVersionStore;

class RefreshLanguageCatalogueForLanguageColumn implements \IWPML_Upgrade_Command, \IWPML_Pre_Setup_Upgrade_Command {

	private $schema;

	public function __construct( array $args ) {
		$this->schema = $args[0];
	}

	const PRESETS_TABLE = 'icl_language_presets';

	private function run() {
		if ( true !== $this->seedCatalogue() ) {
			return false;
		}

		if ( ! $this->languageColumnIsFilled() ) {
			return false;
		}

		$store = new CatalogueVersionStore();
		foreach (
			[
				CatalogueVersionStore::SECTION_LANGUAGES,
				CatalogueVersionStore::SECTION_LANGUAGE_TRANSLATIONS,
				CatalogueVersionStore::SECTION_COUNTRY_TRANSLATIONS,
				CatalogueVersionStore::SECTION_FLAGS,
			] as $section
		) {
			if ( null !== $store->getVersion( $section ) ) {
				$store->clearVersion( $section );
			}
		}

		return true;
	}

	protected function seedCatalogue() {
		return ( new SeedLanguageCatalogue( [ $this->schema ] ) )->run_admin();
	}

	private function languageColumnIsFilled() {
		$wpdb  = $this->schema->get_wpdb();
		$table = $wpdb->prefix . self::PRESETS_TABLE;

		$hasColumn = (bool) $wpdb->get_var(
			$wpdb->prepare( "SHOW COLUMNS FROM `{$table}` LIKE %s", 'language' )
		);
		if ( ! $hasColumn ) {
			return false;
		}

		$unseeded = $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}` WHERE `language` = ''" );

		return 0 === (int) $unseeded;
	}

	public function run_admin() {
		return $this->run();
	}

	public function run_ajax() {
		return $this->run();
	}

	public function run_frontend() {
		return $this->run();
	}

	public function get_results() {
		return true;
	}
}
