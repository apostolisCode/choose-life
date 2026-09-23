<?php

namespace WPML\Troubleshooting\Endpoints\RetryStuckAutomaticJobs;

class RepairJobsQuery {

	private $wpdb;

	public function __construct( \wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	public function repair( array $data ): bool {
		$result = $this->repairJobs( array_column( $data, 'job_id' ) );

		return $result && $this->repairJobsStatus( array_column( $data, 'rid' ) );
	}

	private function repairJobs( array $jobIds ): bool {
		$wpdb   = $this->wpdb;
		$result = (bool) $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}icl_translate_job
				 SET editor = %s, automatic = 1
				 WHERE job_id IN (" . implode( ', ', array_fill( 0, count( $jobIds ), '%d' ) ) . ')',
				array_merge( array( \WPML_TM_Editors::ATE ), $jobIds )
			)
		);

		if ( $this->wpdb->last_error ) {
			\WPML\PHP\Logger\error( __METHOD__ . ' failed: ' . $this->wpdb->last_error );

			throw new \Exception( 'Database query failed.' );
		}

		return $result;
	}

	private function repairJobsStatus( array $rids ): bool {
		$wpdb   = $this->wpdb;
		$result = (bool) $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}icl_translation_status
				 SET status = %d
				 WHERE rid IN (" . implode( ', ', array_fill( 0, count( $rids ), '%d' ) ) . ')',
				array_merge( array( ICL_TM_ATE_NEEDS_RETRY ), $rids )
			)
		);

		if ( $this->wpdb->last_error ) {
			\WPML\PHP\Logger\error( __METHOD__ . ' failed: ' . $this->wpdb->last_error );

			throw new \Exception( 'Database query failed.' );
		}

		return $result;
	}

}
