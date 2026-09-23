<?php

namespace WPML\Troubleshooting\Endpoints\RetryStuckAutomaticJobs;

class GetJobsQuery {

	private $wpdb;

	public function __construct( \wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	public function get( int $limit ) {
		$wpdb  = $this->wpdb;
		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					wpml_tj.job_id,
					wpml_tj.rid
				FROM {$wpdb->prefix}icl_translate_job wpml_tj
				INNER JOIN {$wpdb->prefix}icl_translation_status wpml_ts ON wpml_tj.rid = wpml_ts.rid
				INNER JOIN {$wpdb->prefix}icl_translation_batches wpml_tb ON wpml_ts.batch_id = wpml_tb.id
				WHERE wpml_tj.editor = %s AND wpml_tj.revision IS NULL
					AND wpml_ts.status = %d AND wpml_ts.translation_service = 'local'
					AND wpml_tb.tp_id IS NULL AND wpml_tb.batch_name LIKE %s
				LIMIT %d",
				\WPML_TM_Editors::NONE,
				ICL_TM_WAITING_FOR_TRANSLATOR,
				'Automatic Translations from%',
				$limit
			),
			ARRAY_A
		);

		if ( $wpdb->last_error ) {
			\WPML\PHP\Logger\error( __METHOD__ . ' failed: ' . $wpdb->last_error );

			throw new \Exception( 'Database query failed.' );
		}

		return $result;
	}

}
