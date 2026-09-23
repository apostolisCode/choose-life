<?php

namespace WPML\TM\Settings;

class AffectedPostsQuery {

	private $wpdb;

	private $countQuery;

	public function __construct( \wpdb $wpdb, GetNumberOfPostsForCustomField $countQuery ) {
		$this->wpdb       = $wpdb;
		$this->countQuery = $countQuery;
	}

	public function countAffected( array $fieldNames ): int {
		if ( ! $fieldNames ) {
			return 0;
		}

		return (int) $this->countQuery->run(
			wpml_collect( [ 'customFields' => array_values( $fieldNames ) ] ),
			$this->wpdb
		);
	}

	public function page( array $fieldNames, int $lastPostId, int $limit ): array {
		if ( ! $fieldNames || $limit < 1 ) {
			return [];
		}

		$fieldsIn   = wpml_prepare_in( array_values( $fieldNames ) );
		$lastPostId = (int) $lastPostId;
		$limit      = (int) $limit;

		$ids = $this->wpdb->get_col(
			"SELECT DISTINCT pm.post_id
			FROM {$this->wpdb->postmeta} pm
			INNER JOIN {$this->wpdb->prefix}icl_translations orig
				ON orig.element_id = pm.post_id
				AND orig.element_type LIKE 'post_%'
				AND orig.source_language_code IS NULL
			WHERE pm.meta_key IN ({$fieldsIn})
			  AND pm.meta_key <> ''
			  AND pm.post_id > {$lastPostId}
			  AND EXISTS (
			      SELECT 1 FROM {$this->wpdb->prefix}icl_translations tr
			      WHERE tr.trid = orig.trid
			        AND tr.source_language_code IS NOT NULL
			  )
			ORDER BY pm.post_id ASC
			LIMIT {$limit}"
		);

		return array_map( 'intval', (array) $ids );
	}
}
