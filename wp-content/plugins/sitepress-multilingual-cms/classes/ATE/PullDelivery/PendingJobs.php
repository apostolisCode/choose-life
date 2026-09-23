<?php

namespace WPML\TM\ATE\PullDelivery;

class PendingJobs {

	public function summary() {
		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT COUNT( * ) AS pending_count,
				        MIN( waiting.sent_at ) AS oldest_at,
				        COALESCE( SUM( waiting.automatic ), 0 ) AS automatic_count,
				        MIN( CASE WHEN waiting.automatic = 1 THEN waiting.sent_at END ) AS oldest_automatic_at
				   FROM (
				        SELECT UNIX_TIMESTAMP( status.timestamp ) AS sent_at,
				               EXISTS (
				                   SELECT 1
				                     FROM {$wpdb->prefix}icl_translate_job job
				                    WHERE job.rid = status.rid
				                      AND job.editor = %s
				                      AND job.automatic = %d
				               ) AS automatic
				          FROM {$wpdb->prefix}icl_translation_status status
				         WHERE status.status IN ( %d, %d )
				           AND EXISTS (
				               SELECT 1
				                 FROM {$wpdb->prefix}icl_translate_job job
				                WHERE job.rid = status.rid
				                  AND job.editor = %s
				           )
				   ) waiting",
				\WPML_TM_Editors::ATE,
				1,
				ICL_TM_IN_PROGRESS,
				ICL_TM_WAITING_FOR_TRANSLATOR,
				\WPML_TM_Editors::ATE
			)
		);

		if ( ! is_object( $row ) ) {
			return self::nothing();
		}

		return [
			'count'               => (int) $row->pending_count,
			'oldest_at'           => (int) $row->oldest_at,
			'automatic_count'     => isset( $row->automatic_count ) ? (int) $row->automatic_count : 0,
			'oldest_automatic_at' => isset( $row->oldest_automatic_at ) ? (int) $row->oldest_automatic_at : 0,
		];
	}

	public static function nothing() {
		return [ 'count' => 0, 'oldest_at' => 0, 'automatic_count' => 0, 'oldest_automatic_at' => 0 ];
	}
}
