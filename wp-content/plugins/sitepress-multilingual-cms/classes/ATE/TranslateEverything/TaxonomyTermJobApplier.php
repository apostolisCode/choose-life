<?php

namespace WPML\TM\ATE\TranslateEverything;

use WPML\TM\ATE\Review\TermJob;
use WPML\TM\Taxonomy\TranslatableTermMeta;
use WPML\TM\XLIFF\TaxonomyTermXliffReader;

class TaxonomyTermJobApplier {

	private $wpdb;

	private $sitepress;

	private $reader;

	public function __construct( \wpdb $wpdb, \SitePress $sitepress, ?TaxonomyTermXliffReader $reader = null ) {
		$this->wpdb      = $wpdb;
		$this->sitepress = $sitepress;
		$this->reader    = $reader ?: new TaxonomyTermXliffReader();
	}

	public function apply( $xliff, $expectedJobId = null ) {
		$data = $this->reader->read( $xliff );
		if ( is_wp_error( $data ) ) {
			return $this->fail( $data->get_error_message() );
		}

		$sourceTermId = (int) $data['termId'];
		$disable      = '__return_true';
		add_filter( 'wpml_disable_term_adjust_id', $disable, 999 );
		try {
			$term = get_term( $sourceTermId );
		} finally {
			remove_filter( 'wpml_disable_term_adjust_id', $disable, 999 );
		}
		if ( ! $term instanceof \WP_Term || (int) $term->term_id !== $sourceTermId ) {
			return $this->fail( 'source term not found for id ' . $data['termId'] );
		}

		$taxonomy    = $term->taxonomy;
		$elementType = 'tax_' . $taxonomy;
		$targetLang  = $data['targetLang'];

		$trid = (int) $this->sitepress->get_element_trid( (int) $term->term_taxonomy_id, $elementType );
		if ( ! $trid ) {
			return $this->fail( 'no trid for source term_taxonomy_id ' . $term->term_taxonomy_id );
		}

		$jobId = $this->findJobId( $trid, $targetLang );

		if ( null !== $expectedJobId && (int) $jobId !== (int) $expectedJobId ) {
			return $this->fail(
				sprintf(
					'the delivered term XLIFF resolves to job %d but the delivery was bound to job %d',
					(int) $jobId,
					(int) $expectedJobId
				)
			);
		}

		if ( ! $this->sitepress->is_translated_taxonomy( $taxonomy ) ) {
			$this->cancelRefusedDelivery( $trid, $targetLang );

			return $this->fail(
				"taxonomy '{$taxonomy}' is no longer translatable - delivery refused, job cancelled (wpmldev-8066)"
			);
		}

		try {
			$action  = new \WPML_Update_Term_Action(
				$this->wpdb,
				$this->sitepress,
				[
					'term'        => $data['fields']['term-name'],
					'description' => isset( $data['fields']['term-description'] ) ? $data['fields']['term-description'] : '',
					'slug'        => $this->resolveTargetSlug( $trid, $taxonomy, $targetLang, $data['fields']['term-name'] ),
					'lang_code'   => $targetLang,
					'trid'        => (string) $trid,
					'taxonomy'    => $taxonomy,
				]
			);
			$newTerm = $action->execute();
		} catch ( \Throwable $e ) {
			return $this->fail( 'term write threw: ' . $e->getMessage() );
		}

		if ( empty( $newTerm['term_taxonomy_id'] ) ) {
			return $this->fail( 'term write produced no term_taxonomy_id' );
		}

		$this->finalize( $trid, $targetLang, (int) $newTerm['term_taxonomy_id'], $jobId );

		$this->applyTranslatedTermMeta( $newTerm, $data['fields'], $term, $targetLang );

		$this->syncTermParent( $taxonomy );

		return $jobId ?: true;
	}

	private function applyTranslatedTermMeta( array $newTerm, array $fields, \WP_Term $sourceTerm, $targetLang ) {
		if ( ! function_exists( 'update_term_meta' ) ) {
			return;
		}

		$termId = isset( $newTerm['term_id'] ) ? (int) $newTerm['term_id'] : 0;
		if ( ! $termId && ! empty( $newTerm['term_taxonomy_id'] ) ) {
			$termId = (int) $this->wpdb->get_var(
				$this->wpdb->prepare(
					"SELECT term_id FROM {$this->wpdb->term_taxonomy} WHERE term_taxonomy_id = %d",
					(int) $newTerm['term_taxonomy_id']
				)
			);
		}
		if ( ! $termId ) {
			return;
		}

		$prefix  = TranslatableTermMeta::UNIT_ID_PREFIX;
		$context = [
			'sourceTermId' => (int) $sourceTerm->term_id,
			'taxonomy'     => (string) $sourceTerm->taxonomy,
			'targetLang'   => (string) $targetLang,
		];

		foreach ( $fields as $unitId => $value ) {
			if ( 0 !== strpos( (string) $unitId, $prefix ) ) {
				continue;
			}
			$key = substr( (string) $unitId, strlen( $prefix ) );
			if ( '' === $key || '' === (string) $value ) {
				continue;
			}

			$handled = apply_filters( 'wpml_apply_translated_term_meta', false, $termId, $key, (string) $value, $context );
			if ( true === $handled ) {
				continue;
			}

			update_term_meta( $termId, $key, (string) $value );
		}
	}

	private function syncTermParent( $taxonomy ) {
		if ( ! is_taxonomy_hierarchical( $taxonomy ) ) {
			return;
		}

		$sync = wpml_get_hierarchy_sync_helper( 'term' );
		if ( ! $sync ) {
			return;
		}

		\WPML_Non_Persistent_Cache::flush_group( \WPML_Hierarchy_Sync::CACHE_GROUP );

		try {
			if ( $sync->is_need_sync( $taxonomy ) ) {
				$sync->sync_element_hierarchy( $taxonomy );
			}
		} catch ( \Throwable $e ) {
			$this->fail( 'term parent sync threw: ' . $e->getMessage() );
		}
	}

	private function resolveTargetSlug( $trid, $taxonomy, $targetLang, $translatedName ) {
		$currentSlug = $this->getTargetTermSlug( $trid, $taxonomy, $targetLang );

		if ( '' !== $currentSlug && ! $this->slugSharedWithSibling( $currentSlug, $taxonomy, $targetLang, $trid ) ) {
			return '';
		}

		return $this->uniqueSiteWideSlug( sanitize_title( $translatedName ), $taxonomy, $targetLang, $trid );
	}

	private function getTargetTermSlug( $trid, $taxonomy, $targetLang ) {
		$wpdb = $this->wpdb;

		return (string) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT t.slug
				FROM {$wpdb->terms} t
				INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id
				INNER JOIN {$wpdb->prefix}icl_translations tr ON tr.element_id = tt.term_taxonomy_id
				WHERE tr.trid = %d
					AND tr.element_type = %s
					AND tr.language_code = %s",
				$trid,
				'tax_' . $taxonomy,
				$targetLang
			)
		);
	}

	private function slugSharedWithSibling( $slug, $taxonomy, $targetLang, $trid = 0 ) {
		$wpdb = $this->wpdb;

		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->terms} t
				INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id
				LEFT JOIN {$wpdb->prefix}icl_translations tr
					ON tr.element_id = tt.term_taxonomy_id AND tr.element_type = %s
				WHERE t.slug = %s
					AND tt.taxonomy = %s
					AND ( tr.trid IS NULL OR NOT ( tr.trid = %d AND tr.language_code = %s ) )",
				'tax_' . $taxonomy,
				$slug,
				$taxonomy,
				(int) $trid,
				$targetLang
			)
		);

		return $count > 0;
	}

	private function uniqueSiteWideSlug( $slug, $taxonomy, $targetLang, $trid = 0 ) {
		if ( '' === $slug ) {
			$slug = 'term';
		}

		$base    = $slug;
		$suffix  = 2;
		$current = $slug;

		while ( $this->slugSharedWithSibling( $current, $taxonomy, $targetLang, $trid ) ) {
			$current = $base . '-' . $suffix;
			++$suffix;
		}

		return $current;
	}

	private function findJobId( $trid, $targetLang ) {
		$wpdb = $this->wpdb;

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT MAX( tj.job_id )
				FROM {$wpdb->prefix}icl_translate_job tj
				INNER JOIN {$wpdb->prefix}icl_translation_status ts ON ts.rid = tj.rid
				INNER JOIN {$wpdb->prefix}icl_translations t ON t.translation_id = ts.translation_id
				WHERE t.trid = %d AND t.language_code = %s",
				$trid,
				$targetLang
			)
		);
	}

	private function cancelRefusedDelivery( $trid, $targetLang ) {
		$wpdb = $this->wpdb;

		$translationId = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT translation_id FROM {$wpdb->prefix}icl_translations
				WHERE trid = %d AND language_code = %s ORDER BY ( element_id IS NULL ), translation_id DESC LIMIT 1",
				$trid,
				$targetLang
			)
		);

		if ( ! $translationId ) {
			return;
		}

		$wpdb->update(
			$wpdb->prefix . 'icl_translation_status',
			[ 'status' => ICL_TM_ATE_CANCELLED, 'needs_update' => 0, 'review_status' => null ],
			[ 'translation_id' => $translationId ],
			[ '%d', '%d', '%s' ],
			[ '%d' ]
		);
	}

	private function finalize( $trid, $targetLang, $newTermTaxonomyId, $jobId ) {
		$wpdb = $this->wpdb;

		$translationId = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT translation_id FROM {$wpdb->prefix}icl_translations
				WHERE trid = %d AND language_code = %s AND element_id = %d",
				$trid,
				$targetLang,
				$newTermTaxonomyId
			)
		);

		if ( ! $translationId ) {
			$translationId = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT translation_id FROM {$wpdb->prefix}icl_translations
					WHERE trid = %d AND language_code = %s ORDER BY ( element_id IS NULL ), translation_id DESC LIMIT 1",
					$trid,
					$targetLang
				)
			);
			if ( $translationId ) {
				$wpdb->update(
					$wpdb->prefix . 'icl_translations',
					[ 'element_id' => $newTermTaxonomyId ],
					[ 'translation_id' => $translationId ],
					[ '%d' ],
					[ '%d' ]
				);
			}
		}

		if ( ! $translationId ) {
			return;
		}

		\WPML_Translation_Records_Delete::translations_where(
			'trid = %d AND language_code = %s AND translation_id <> %d',
			[ $trid, $targetLang, $translationId ]
		);

		if ( ! $jobId ) {
			return;
		}

		$rid = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT rid FROM {$wpdb->prefix}icl_translate_job WHERE job_id = %d", $jobId )
		);
		if ( ! $rid ) {
			return;
		}

		$wpdb->update(
			$wpdb->prefix . 'icl_translation_status',
			[
				'translation_id' => $translationId,
				'status'         => ICL_TM_COMPLETE,
				'needs_update'   => 0,
			],
			[ 'rid' => $rid ],
			[ '%d', '%d', '%d' ],
			[ '%d' ]
		);

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}icl_translate_job
				SET translated = 1, completed_date = COALESCE( completed_date, %s )
				WHERE job_id = %d",
				gmdate( 'Y-m-d H:i:s' ),
				$jobId
			)
		);

		TermJob::onDelivered( $jobId );
	}

	private function fail( $reason ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WPML taxonomy term translation could not be applied: ' . $reason );
		}

		return false;
	}
}
