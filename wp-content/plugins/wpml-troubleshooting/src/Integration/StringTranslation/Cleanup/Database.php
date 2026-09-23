<?php

namespace WPML\Troubleshooting\Integration\StringTranslation\Cleanup;

use wpdb;
use WPML_ST_Translations_File_Dictionary;

class Database {

	private $wpdb;

	private $dictionary;

	public function __construct(
		wpdb $wpdb,
		WPML_ST_Translations_File_Dictionary $dictionary
	) {
		$this->wpdb       = $wpdb;
		$this->dictionary = $dictionary;
	}

	public function deleteStringsFromImportedMoFiles() {
		$moDomains = $this->dictionary->get_domains( 'mo' );

		if ( ! $moDomains ) {
			return;
		}

		$this->deleteOnlyNativeMoStringTranslations( $moDomains );
		$this->deleteMoStringsWithNoTranslation( $moDomains );
		icl_update_string_status_all();
		$this->optimizeStringTables();
	}

	private function deleteOnlyNativeMoStringTranslations( array $moDomains ) {
		$wpdb = $this->wpdb;

		$wpdb->query(
			$wpdb->prepare(
				"
			DELETE st FROM {$wpdb->prefix}icl_string_translations AS st
			LEFT JOIN {$wpdb->prefix}icl_strings AS s
				ON st.string_id = s.id
			WHERE st.value IS NULL AND s.context IN(" . implode( ', ', array_fill( 0, count( $moDomains ), '%s' ) ) . ')',
				$moDomains
			)
		);
	}

	private function deleteMoStringsWithNoTranslation( array $moDomains ) {
		$wpdb = $this->wpdb;

		$wpdb->query(
			$wpdb->prepare(
				"
			DELETE s FROM {$wpdb->prefix}icl_strings AS s
			LEFT JOIN {$wpdb->prefix}icl_string_translations AS st
				ON st.string_id = s.id
			WHERE st.string_id IS NULL AND s.context IN(" . implode( ', ', array_fill( 0, count( $moDomains ), '%s' ) ) . ')',
				$moDomains
			)
		);
	}

	private function optimizeStringTables() {
		$wpdb = $this->wpdb;

			$this->wpdb->query( "OPTIMIZE TABLE {$wpdb->prefix}icl_strings, {$wpdb->prefix}icl_string_translations" );
	}

	public function truncatePagesAndUrls() {
		$wpdb = $this->wpdb;
		foreach ( [ 'icl_string_pages', 'icl_string_urls' ] as $table_suffix ) {
			$table = $this->wpdb->prefix . $table_suffix;

			if ( $this->tableExists( $table ) ) {
				if ( 'icl_string_pages' === $table_suffix ) {
					$this->wpdb->query( "TRUNCATE {$wpdb->prefix}icl_string_pages" );
				} else {
					$this->wpdb->query( "TRUNCATE {$wpdb->prefix}icl_string_urls" );
				}
			}
		}
	}

	private function tableExists( $table ) {
		$wpdb = $this->wpdb;

		return (bool) $wpdb->get_var(
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $table )
		);
	}
}
