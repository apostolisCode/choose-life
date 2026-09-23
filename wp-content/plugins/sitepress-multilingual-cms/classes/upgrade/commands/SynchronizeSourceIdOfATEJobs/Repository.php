<?php

namespace WPML\TM\Upgrade\Commands\SynchronizeSourceIdOfATEJobs;


class Repository {
	private $wpdb;

	public function __construct( \wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}


	public function getPairs() {
		$wpdb   = $this->wpdb;
		$rowset = $wpdb->get_results(
			"SELECT MAX(editor_job_id) as editor_job_id, rid
			FROM {$wpdb->prefix}icl_translate_job
			WHERE editor = 'ate' AND editor_job_id IS NOT NULL
			GROUP BY rid",
			ARRAY_A
		);
		$rowset = \wpml_collect( is_array( $rowset ) ? $rowset : [] );

		return $rowset->pluck( 'rid', 'editor_job_id' );
	}
}
