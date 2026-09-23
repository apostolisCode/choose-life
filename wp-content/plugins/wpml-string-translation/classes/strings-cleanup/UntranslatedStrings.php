<?php

namespace WPML\ST\StringsCleanup;

class UntranslatedStrings {

	private $wpdb;

	public function __construct( \wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	public function getCountInDomains( $domains ) {
		if ( ! $domains ) {
			return 0;
		}
		$wpdb = $this->wpdb;

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT count(id) FROM {$wpdb->prefix}icl_strings WHERE status = %d AND context IN ("
				. implode( ', ', array_fill( 0, count( $domains ), '%s' ) ) . ')',
				...array_merge( [ ICL_TM_NOT_TRANSLATED ], array_values( $domains ) )
			)
		);
	}

	public function getFromDomains( $domains, $batchSize ) {
		if ( ! $domains ) {
			return [];
		}
		$wpdb = $this->wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id FROM {$wpdb->prefix}icl_strings WHERE status = %d AND context IN ("
				. implode( ', ', array_fill( 0, count( $domains ), '%s' ) ) . ') LIMIT 0, %d',
				...array_merge( [ ICL_TM_NOT_TRANSLATED ], array_values( $domains ), [ $batchSize ] )
			)
		);
	}

	public function remove( $stringIds ) {
		if ( $stringIds ) {
			wpml_unregister_string_multi( $stringIds );
		}

		return count( $stringIds );
	}
}
