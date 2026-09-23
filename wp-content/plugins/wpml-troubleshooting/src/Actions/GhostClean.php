<?php

namespace WPML\Troubleshooting\Actions;

class GhostClean {

	const CHUNK_SIZE = 100;

	public function run() {
		global $wpdb, $wp_taxonomies;

		$this->clean_post_orphans( $wpdb );
		$this->clean_comment_orphans( $wpdb );
		$this->clean_tax_orphans( $wpdb );

		if ( is_array( $wp_taxonomies ) ) {
			foreach ( $wp_taxonomies as $taxonomy => $object ) {
				$this->clean_mismatched_taxonomy_rows( $wpdb, $taxonomy );
			}
		}

		$this->clean_unlinked_translation_status( $wpdb );
		$this->dedupe_post_translations( $wpdb );
	}

	private function clean_post_orphans( $wpdb ) {
		do {
			$orphans = $wpdb->get_col(
				"
				SELECT t.translation_id, t.element_type
				FROM {$wpdb->prefix}icl_translations t
				LEFT JOIN {$wpdb->posts} p ON t.element_id = p.ID
				WHERE t.element_id IS NOT NULL AND t.element_type LIKE 'post\\_%' AND p.ID IS NULL
				LIMIT " . self::CHUNK_SIZE
			);
			$fetched = count( (array) $orphans );
			$deleted = $this->delete_orphans( $wpdb, $orphans, 'post' );
		} while ( $deleted > 0 && $fetched >= self::CHUNK_SIZE );
	}

	private function clean_comment_orphans( $wpdb ) {
		do {
			$orphans = $wpdb->get_col(
				"
				SELECT t.translation_id
				FROM {$wpdb->prefix}icl_translations t
				LEFT JOIN {$wpdb->comments} c ON t.element_id = c.comment_ID
				WHERE t.element_type = 'comment' AND c.comment_ID IS NULL
				LIMIT " . self::CHUNK_SIZE
			);
			if ( false === $orphans ) {
				echo $wpdb->last_result;
			}
			$fetched = count( (array) $orphans );
			$deleted = $this->delete_orphans( $wpdb, $orphans, 'comment' );
		} while ( $deleted > 0 && $fetched >= self::CHUNK_SIZE );
	}

	private function clean_tax_orphans( $wpdb ) {
		do {
			$orphans = $wpdb->get_col(
				"
				SELECT t.translation_id
				FROM {$wpdb->prefix}icl_translations t
				LEFT JOIN {$wpdb->term_taxonomy} p ON t.element_id = p.term_taxonomy_id
				WHERE t.element_id IS NOT NULL AND t.element_type LIKE 'tax\\_%' AND p.term_taxonomy_id IS NULL
				LIMIT " . self::CHUNK_SIZE
			);
			$fetched = count( (array) $orphans );
			$deleted = $this->delete_orphans_tax( $wpdb, $orphans );
		} while ( $deleted > 0 && $fetched >= self::CHUNK_SIZE );
	}

	private function clean_mismatched_taxonomy_rows( $wpdb, $taxonomy ) {
		$taxonomy = sanitize_key( $taxonomy );
		do {
			$orphans = $wpdb->get_col(
				"
				SELECT t.translation_id
				FROM {$wpdb->prefix}icl_translations t
				LEFT JOIN {$wpdb->term_taxonomy} p
				ON t.element_id = p.term_taxonomy_id
				WHERE t.element_type = 'tax_{$taxonomy}'
				AND p.taxonomy <> '{$taxonomy}'
				LIMIT " . self::CHUNK_SIZE
			);
			$fetched = count( (array) $orphans );
			$deleted = $this->delete_orphans_tax( $wpdb, $orphans );
		} while ( $deleted > 0 && $fetched >= self::CHUNK_SIZE );
	}

	private function delete_orphans( $wpdb, $orphans, $context ) {
		if ( empty( $orphans ) ) {
			return 0;
		}

		$upgrade_args_set = array();
		foreach ( $orphans as $orphan ) {
			$upgrade_args       = array(
				'translation_id' => $orphan,
				'context'        => $context,
			);
			$upgrade_args_set[] = $upgrade_args;

			do_action( 'wpml_translation_update', array_merge( $upgrade_args, array( 'type' => 'before_delete' ) ) );
		}

		$deleted = $this->delete_translation_rows( $wpdb, $orphans );

		foreach ( $upgrade_args_set as $upgrade_args ) {
			do_action( 'wpml_translation_update', array_merge( $upgrade_args, array( 'type' => 'after_delete' ) ) );
		}

		return $deleted;
	}

	private function delete_orphans_tax( $wpdb, $orphans ) {
		if ( empty( $orphans ) ) {
			return 0;
		}

		$upgrade_args_set = array();
		foreach ( $orphans as $orphan ) {
			$upgrade_args       = array(
				'translation_id' => $orphan,
				'context'        => 'tax',
			);
			$upgrade_args_set[] = $upgrade_args;
			do_action( 'wpml_translation_update', $upgrade_args );
		}

		$deleted = $this->delete_translation_rows( $wpdb, $orphans );

		foreach ( $upgrade_args_set as $upgrade_args ) {
			do_action( 'wpml_translation_update', array_merge( $upgrade_args, array( 'type' => 'after_delete' ) ) );
		}

		return $deleted;
	}

	private function delete_translation_rows( $wpdb, $translation_ids ) {
		$deleted = 0;

		foreach ( array_chunk( (array) $translation_ids, self::CHUNK_SIZE ) as $batch ) {
			$deleted += (int) $wpdb->query(
				"DELETE FROM {$wpdb->prefix}icl_translations
				WHERE translation_id IN (" . wpml_prepare_in( $batch, '%d' ) . ')'
			);
		}

		return $deleted;
	}

	private function clean_unlinked_translation_status( $wpdb ) {
		\WPML_Unlinked_Translation_Records::sweep_all( $wpdb );
	}

	private function dedupe_post_translations( $wpdb ) {
		do {
			$trs = $wpdb->get_results(
				"SELECT element_id, GROUP_CONCAT(translation_id) AS tids FROM {$wpdb->prefix}icl_translations
				WHERE element_id > 0 AND element_type LIKE 'post\\_%'
				GROUP BY element_id
				HAVING COUNT(translation_id) > 1
				LIMIT " . self::CHUNK_SIZE
			);

			$to_delete = array();
			foreach ( (array) $trs as $r ) {
				$exp = explode( ',', $r->tids );
				if ( count( $exp ) <= 1 ) {
					continue;
				}

				$maxtid = max( $exp );
				foreach ( $exp as $e ) {
					if ( (int) $e === (int) $maxtid ) {
						continue;
					}
					$to_delete[] = $e;
				}
			}

			$fetched = count( (array) $trs );
			$deleted = $this->delete_orphans( $wpdb, $to_delete, 'post' );
		} while ( $deleted > 0 && $fetched >= self::CHUNK_SIZE );
	}
}
