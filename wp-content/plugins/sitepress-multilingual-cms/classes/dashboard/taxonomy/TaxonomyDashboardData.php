<?php

namespace WPML\TM\Dashboard\Taxonomy;

use WPML\Element\API\Languages;
use WPML\LanguageEditor\TranslationPause;
use WPML\TM\ATE\Review\ReviewStatus;
use WPML\Core\Component\WordsToTranslate\Domain\Calculator\Diff;
use WPML\Core\Component\WordsToTranslate\Domain\Calculator\Count;
use function WPML\Container\make;

class TaxonomyDashboardData {

	const WC_SNAPSHOT_META_PREFIX = '_wpml_tax_wc_src_';

	private $diffCalculator = null;

	private $countCalculator = null;

	public function get( string $search = '' ): array {
		global $sitepress, $wpdb, $wp_taxonomies;

		$rows                = [];
		$labelRows           = [];
		$hiddenCount         = 0;
		$secondaryLanguages  = $this->getSecondaryLanguages();
		$defaultLanguageCode = Languages::getDefaultCode();
		$labelStatus         = new TaxonomyLabelStatus();
		$isTeaActive         = (bool) \WPML\Setup\Option::shouldTranslateEverything();
		$isStActive          = $labelStatus->isStringTranslationActive();

		if ( ! is_array( $wp_taxonomies ) || ! $sitepress ) {
			return $this->shape( $rows, $labelRows, $hiddenCount, $isTeaActive, $isStActive );
		}

		foreach ( $wp_taxonomies as $slug => $object ) {
			if ( in_array( $slug, [ 'post_format', 'nav_menu', 'link_category', 'post_status' ], true ) ) {
				continue;
			}

			if ( ! $sitepress->is_translated_taxonomy( $slug ) ) {
				if ( $this->isVisibleTaxonomy( $object ) ) {
					$labelRow = $labelStatus->getRow( $slug, $isTeaActive );
					if ( null !== $labelRow ) {
						$labelRows[] = $labelRow;
					}
					if ( $this->isPublicTaxonomy( $object ) && $this->taxonomyHasTerms( $wpdb, $slug ) ) {
						++$hiddenCount;
					}
				}
				continue;
			}

			$metrics        = $this->buildTaxonomyMetrics(
				$wpdb,
				$slug,
				$defaultLanguageCode,
				$secondaryLanguages,
				$isTeaActive
			);
			$languageStates = $metrics['states'];

			$wordCounts        = [];
			$wordCountsAll     = [];
			$untranslatedTerms = [];
			foreach ( $languageStates as $state ) {
				$wordCounts[ $state['code'] ]        = $state['words'];
				$wordCountsAll[ $state['code'] ]     = $state['wordsAll'];
				$untranslatedTerms[ $state['code'] ] = $state['untranslated'];
			}

			$isFinished = ! empty( $languageStates )
				&& array_reduce(
					$languageStates,
					function ( $carry, $state ) {
						return $carry && 'complete' === $state['status'];
					},
					true
				);

			$total      = $metrics['total'];
			$translated = max( $total - ( $untranslatedTerms ? max( $untranslatedTerms ) : 0 ), 0 );

			$rows[] = [
				'taxonomy'            => $slug,
				'label'               => $this->getLabel( $object, $slug ),
				'postTypes'           => $this->getPostTypeLabels( $object ),
				'translated'          => $translated,
				'total'               => $total,
				'languages'           => $languageStates,
				'wordCounts'          => $wordCounts,
				'wordCountsAll'       => $wordCountsAll,
				'untranslatedTerms'   => $untranslatedTerms,
				'isFinished'          => $isFinished,
				'editUrl'             => $this->getEditUrl( $slug ),
				'translationPriority' => $this->getTranslationPriority( $slug ),
			];

			$labelRow = $labelStatus->getRow( $slug, $isTeaActive );
			if ( null !== $labelRow ) {
				$labelRows[] = $labelRow;
			}
		}

		if ( '' !== $search ) {
			list( $rows, $labelRows ) = $this->applySearch( $wpdb, $rows, $labelRows, $search );
		}

		return $this->shape( $rows, $labelRows, $hiddenCount, $isTeaActive, $isStActive );
	}

	private function applySearch( $wpdb, array $rows, array $labelRows, string $search ) {
		$matchesNeedle = function ( $haystack ) use ( $search ) {
			return false !== stripos( (string) $haystack, $search );
		};

		$termMatches = $this->findTermNameMatches(
			$wpdb,
			array_map(
				function ( $row ) {
					return (string) $row['taxonomy'];
				},
				$rows
			),
			$search
		);

		$filteredRows = [];
		foreach ( $rows as $row ) {
			$row['matchedTerms'] = isset( $termMatches[ $row['taxonomy'] ] ) ? $termMatches[ $row['taxonomy'] ] : [];
			if ( $matchesNeedle( $row['label'] ) || $matchesNeedle( $row['taxonomy'] ) || $row['matchedTerms'] ) {
				$filteredRows[] = $row;
			}
		}

		$filteredLabelRows = [];
		foreach ( $labelRows as $labelRow ) {
			if (
				$matchesNeedle( isset( $labelRow['plural'] ) ? $labelRow['plural'] : '' )
				|| $matchesNeedle( isset( $labelRow['singular'] ) ? $labelRow['singular'] : '' )
				|| $matchesNeedle( isset( $labelRow['taxonomy'] ) ? $labelRow['taxonomy'] : '' )
			) {
				$filteredLabelRows[] = $labelRow;
			}
		}

		return [ $filteredRows, $filteredLabelRows ];
	}

	private function findTermNameMatches( $wpdb, array $taxonomies, string $search ): array {
		if ( ! $taxonomies ) {
			return [];
		}

		$placeholders = implode( ',', array_fill( 0, count( $taxonomies ), '%s' ) );

		$sql = "SELECT tt.taxonomy, t.name
			FROM {$wpdb->prefix}terms t
			INNER JOIN {$wpdb->prefix}term_taxonomy tt ON tt.term_id = t.term_id
			WHERE tt.taxonomy IN ( {$placeholders} )
			AND t.name LIKE %s
			ORDER BY t.name
			LIMIT 100";

		$params = array_merge( $taxonomies, [ '%' . $wpdb->esc_like( $search ) . '%' ] );
		$found = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

		$byTaxonomy = [];
		foreach ( $found ?: [] as $row ) {
			$byTaxonomy[ (string) $row['taxonomy'] ][] = (string) $row['name'];
		}

		return $byTaxonomy;
	}

	private function taxonomyHasTerms( $wpdb, string $taxonomy ): bool {
		return (bool) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT 1 FROM {$wpdb->prefix}term_taxonomy WHERE taxonomy = %s LIMIT 1",
				$taxonomy
			)
		);
	}

	public function getUntranslatedTermIds( string $taxonomy, string $targetLang, bool $includeCompleted = false ): array {
		return array_keys( $this->getUntranslatedTermsWithSource( $taxonomy, $targetLang, $includeCompleted ) );
	}

	public function getUntranslatedTermsWithSource( string $taxonomy, string $targetLang, bool $includeCompleted = false, bool $excludeLiveJobs = false ): array {
		global $wpdb;

		$elementType = 'tax_' . $taxonomy;

		$statusFilter = $includeCompleted
			? ''
			: 'AND ( translations.translation_id IS NULL OR ts.status != %d OR ts.needs_update = 1 )';

		$liveJobFilter = $excludeLiveJobs
			? 'AND NOT EXISTS (
					SELECT 1 FROM ' . $wpdb->prefix . 'icl_translate_job live
					WHERE live.rid = ts.rid
					AND live.revision IS NULL
					AND COALESCE( live.editor_job_id, 0 ) <> 0
					AND ts.status = %d
				)'
			: '';

		$sql = "SELECT original_element.element_id, original_element.language_code
				FROM {$wpdb->prefix}icl_translations original_element
				LEFT JOIN {$wpdb->prefix}icl_translations translations
					ON translations.trid = original_element.trid
					AND translations.language_code = %s
				LEFT JOIN {$wpdb->prefix}icl_translation_status ts
					ON ts.translation_id = translations.translation_id
				WHERE original_element.element_type = %s
				AND original_element.source_language_code IS NULL
				AND original_element.language_code != %s
				{$statusFilter}
				{$liveJobFilter}
				-- wpmldev-8099: a review OPEN IN THE EDITOR is somebody's work in
				-- progress, not a gap to fill. Its row is IN_PROGRESS, which is
				-- also what a sent-but-undelivered row looks like, so without this
				-- the send picked the term up again, superseded the job the
				-- reviewer had open and orphaned their editor tab. Re-sending an
				-- in-flight term job is deliberate (wpmldev-8013); re-sending one
				-- under review is not. Holds for `overwrite` too - that redoes
				-- FINISHED work, and an open review is not finished.
				AND ( ts.review_status IS NULL OR ts.review_status != %s )";

		$params = [ $targetLang, $elementType, $targetLang ];
		if ( ! $includeCompleted ) {
			$params[] = ICL_TM_COMPLETE;
		}
		if ( $excludeLiveJobs ) {
			$params[] = ICL_TM_IN_PROGRESS;
		}
		$params[] = ReviewStatus::EDITING;

		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

		$terms = [];
		foreach ( $rows ?: [] as $row ) {
			$terms[ (int) $row['element_id'] ] = (string) $row['language_code'];
		}

		return $terms;
	}

	private function shape( array $rows, array $labelRows, int $hiddenCount, bool $isTeaActive, bool $isStActive ): array {
		return [
			'rows'                       => $rows,
			'labelRows'                  => $labelRows,
			'hiddenNotTranslatableCount' => $hiddenCount,
			'isTeaActive'                => $isTeaActive,
			'isStActive'                 => $isStActive,
			'settingsUrl'                => $this->getSettingsUrl(),
		];
	}

	private function getEditUrl( string $taxonomy ): string {
		if ( ! function_exists( 'admin_url' ) ) {
			return '';
		}

		$folder = defined( 'WPML_PLUGIN_FOLDER' ) ? WPML_PLUGIN_FOLDER : 'sitepress-multilingual-cms';

		return admin_url(
			'admin.php?page=' . $folder . '/menu/taxonomy-translation.php&taxonomy=' . rawurlencode( $taxonomy )
		);
	}

	private function getTranslationPriority( string $slug ): string {
		if ( ! function_exists( 'apply_filters' ) ) {
			return '';
		}

		$priority = apply_filters( 'wpml_element_translation_priority', '', 'tax_' . $slug );

		return is_string( $priority ) ? $priority : '';
	}

	private function getSecondaryLanguages(): array {
		return TranslationPause::filterTranslatable( Languages::getSecondaryCodes() );
	}

	private function getLanguageColumns( string $defaultLang, array $secondaryLanguages ): array {
		return array_values( array_unique( array_merge( $secondaryLanguages, [ $defaultLang ] ) ) );
	}

	private function isPublicTaxonomy( $taxonomyObject ): bool {
		return ! empty( $taxonomyObject->public );
	}

	private function isVisibleTaxonomy( $taxonomyObject ): bool {
		return ! empty( $taxonomyObject->public ) || ! empty( $taxonomyObject->show_ui );
	}

	private function getLabel( $taxonomyObject, string $slug ): string {
		if ( isset( $taxonomyObject->labels->name ) && $taxonomyObject->labels->name ) {
			$label = (string) $taxonomyObject->labels->name;

			return $this->localizeAdminString( $label, 'taxonomy general name: ' . $label );
		}

		return $slug;
	}

	private function localizeAdminString( string $label, string $stringName ): string {
		if ( '' === $label ) {
			return $label;
		}

		$language = apply_filters( 'wpml_current_language', null );
		if ( ! $language ) {
			return $label;
		}

		$translated = apply_filters( 'wpml_translate_single_string', $label, 'WordPress', $stringName, $language );

		return is_string( $translated ) && '' !== $translated ? $translated : $label;
	}

	private function getPostTypeLabels( $taxonomyObject ): array {
		global $wp_post_types;

		$labels = [];
		$types  = isset( $taxonomyObject->object_type ) && is_array( $taxonomyObject->object_type )
			? $taxonomyObject->object_type
			: [];

		foreach ( $types as $type ) {
			if ( isset( $wp_post_types[ $type ]->labels->name ) ) {
				$name     = (string) $wp_post_types[ $type ]->labels->name;
				$labels[] = $this->localizeAdminString( $name, 'post type general name: ' . $name );
			} else {
				$labels[] = $type;
			}
		}

		return $labels;
	}

	private function getSourceTermRows( $wpdb, string $taxonomy ): array {
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT original_element.element_id,
					original_element.language_code AS source_lang,
					t.term_id,
					t.name,
					COALESCE( tt.description, '' ) AS description
				FROM {$wpdb->prefix}icl_translations original_element
				INNER JOIN {$wpdb->prefix}term_taxonomy tt
					ON tt.term_taxonomy_id = original_element.element_id
				INNER JOIN {$wpdb->prefix}terms t
					ON t.term_id = tt.term_id
				WHERE original_element.element_type = %s
				AND original_element.source_language_code IS NULL",
				'tax_' . $taxonomy
			),
			ARRAY_A
		);

		return $rows ?: [];
	}

	private function getTranslationStatusMap( $wpdb, string $taxonomy, array $languages ): array {
		if ( empty( $languages ) ) {
			return [];
		}

		$placeholders = implode( ',', array_fill( 0, count( $languages ), '%s' ) );

		$sql = "SELECT original_element.element_id,
				translations.language_code,
				translations.translation_id,
				ts.status,
				ts.needs_update,
				job.editor_job_id
			FROM {$wpdb->prefix}icl_translations original_element
			INNER JOIN {$wpdb->prefix}icl_translations translations
				ON translations.trid = original_element.trid
				AND translations.language_code IN ( {$placeholders} )
			LEFT JOIN {$wpdb->prefix}icl_translation_status ts
				ON ts.translation_id = translations.translation_id
			LEFT JOIN (
				SELECT rid, MAX( job_id ) AS job_id
				FROM {$wpdb->prefix}icl_translate_job
				GROUP BY rid
			) latest_job ON latest_job.rid = ts.rid
			LEFT JOIN {$wpdb->prefix}icl_translate_job job
				ON job.job_id = latest_job.job_id
			WHERE original_element.element_type = %s
			AND original_element.source_language_code IS NULL";

		$params = array_merge( $languages, [ 'tax_' . $taxonomy ] );
		$rows   = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

		$map = [];
		foreach ( $rows ?: [] as $row ) {
			$map[ (string) $row['language_code'] ][ (int) $row['element_id'] ] = $row;
		}

		return $map;
	}

	private function buildTaxonomyMetrics(
		$wpdb,
		string $taxonomy,
		string $defaultLang,
		array $secondaryLanguages,
		bool $isTeaActive = false
	): array {
		$sourceRows = $this->getSourceTermRows( $wpdb, $taxonomy );
		$languages  = $this->getLanguageColumns( $defaultLang, $secondaryLanguages );
		$statusMap  = $this->getTranslationStatusMap( $wpdb, $taxonomy, $languages );
		$total      = count( $sourceRows );

		$termIds = array_map(
			static function ( $row ) {
				return (int) $row['term_id'];
			},
			$sourceRows
		);
		if ( $termIds && function_exists( 'update_termmeta_cache' ) ) {
			update_termmeta_cache( $termIds );
		}

		$metaKeys   = \WPML\TM\Taxonomy\TranslatableTermMeta::keys( $taxonomy );
		$termTexts  = [];
		$termWords  = [];
		foreach ( $sourceRows as $row ) {
			$current = (string) ( $row['name'] ?? '' ) . "\n" . (string) ( $row['description'] ?? '' );

			$metaText = \WPML\TM\Taxonomy\TranslatableTermMeta::metaTextById( (int) ( $row['term_id'] ?? 0 ), $metaKeys, $taxonomy );
			if ( '' !== $metaText ) {
				$current .= "\n" . $metaText;
			}

			$elementId               = (int) $row['element_id'];
			$termTexts[ $elementId ] = $current;
			$termWords[ $elementId ] = $this->wordCount( $current );
		}

		$states = [];

		foreach ( $languages as $lang ) {
			$langMap     = isset( $statusMap[ $lang ] ) ? $statusMap[ $lang ] : [];
			$translated  = 0;
			$inProgress  = 0;
			$stalled     = 0;
			$needsUpdate = 0;
			$words       = 0;
			$langTotal = 0;
			$wordsAll  = 0;

			foreach ( $sourceRows as $row ) {
				if ( (string) ( $row['source_lang'] ?? $defaultLang ) === $lang ) {
					continue;
				}

				$elementId = (int) $row['element_id'];
				$wordsAll += $termWords[ $elementId ];
				++$langTotal;

				$statusRow = isset( $langMap[ $elementId ] ) ? $langMap[ $elementId ] : null;
				$rowStatus = ( $statusRow && null !== $statusRow['status'] ) ? (int) $statusRow['status'] : null;
				$stale     = $statusRow && 1 === (int) ( $statusRow['needs_update'] ?? 0 );

				if ( $statusRow && ( null === $rowStatus || ICL_TM_COMPLETE === $rowStatus ) && ! $stale ) {
					++$translated;
					continue;
				}

				if ( ICL_TM_IN_PROGRESS === $rowStatus ) {
					++$inProgress;
					if ( empty( $statusRow['editor_job_id'] ) ) {
						++$stalled;
					}
				}

				if ( ICL_TM_COMPLETE === $rowStatus && $stale ) {
					++$needsUpdate;
					$baseline = (string) get_term_meta(
						(int) $row['term_id'],
						self::WC_SNAPSHOT_META_PREFIX . $lang,
						true
					);
					$words   += $this->countChangedWords( $baseline, $termTexts[ $elementId ] );
				} else {
					$words += $termWords[ $elementId ];
				}
			}

			$untranslated = max( $langTotal - $translated, 0 );

			if ( 0 === $langTotal ) {
				$status = 'complete';
			} elseif ( $translated === $langTotal ) {
				$status = 'complete';
			} elseif ( $inProgress > 0 ) {
				$status = $stalled === $inProgress ? 'stalled' : 'in_progress';
			} elseif ( $needsUpdate > 0 ) {
				$status = $isTeaActive ? 'preparing' : 'needs_update';
			} elseif ( 0 === $translated ) {
				$status = 'missing';
			} else {
				$status = 'partial';
			}

			$states[] = [
				'code'         => $lang,
				'status'       => $status,
				'untranslated' => $untranslated,
				'inProgress'   => $inProgress,
				'stalled'      => $stalled,
				'words'        => $words,
				'wordsAll'     => $wordsAll,
			];
		}

		return [
			'states' => $states,
			'total'  => $total,
		];
	}

	private function countChangedWords( string $previous, string $current ): int {
		$current = trim( wp_strip_all_tags( $current ) );
		if ( '' === $current ) {
			return 0;
		}

		$previous = trim( wp_strip_all_tags( $previous ) );
		if ( '' === $previous ) {
			return $this->wordCount( $current );
		}

		return (int) $this->countCalculator()->wordsToTranslate(
			$this->diffCalculator()->diffStrings( $previous, $current )
		);
	}

	private function diffCalculator(): Diff {
		if ( null === $this->diffCalculator ) {
			$this->diffCalculator = make( Diff::class );
		}

		return $this->diffCalculator;
	}

	private function countCalculator(): Count {
		if ( null === $this->countCalculator ) {
			$this->countCalculator = make( Count::class );
		}

		return $this->countCalculator;
	}

	private function wordCount( string $text ): int {
		$clean = trim( wp_strip_all_tags( $text ) );
		if ( '' === $clean ) {
			return 0;
		}

		return count( preg_split( '/\s+/u', $clean ) ?: [] );
	}

	private function getSettingsUrl(): string {
		if ( ! function_exists( 'admin_url' ) ) {
			return '';
		}

		return admin_url( 'admin.php?page=tm/menu/settings&section=taxonomies' );
	}
}
