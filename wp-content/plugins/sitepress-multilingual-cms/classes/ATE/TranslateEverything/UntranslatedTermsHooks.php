<?php

namespace WPML\TM\ATE\TranslateEverything;

use WPML\LIB\WP\Hooks;
use WPML\Setup\Option;
use function WPML\Container\make;
use function WPML\FP\spreadArgs;

class UntranslatedTermsHooks implements \IWPML_Backend_Action, \IWPML_REST_Action, \IWPML_AJAX_Action {

	public function add_hooks() {
		Hooks::onFilter( 'wpml_translate_everything_untranslated_elements_strategies' )
			->then( spreadArgs( [ $this, 'registerStrategy' ] ) );

		Hooks::onAction( 'wpml_taxonomy_made_translatable', 10, 1 )
			->then( spreadArgs( [ $this, 'onTaxonomyMadeTranslatable' ] ) );

		Hooks::onAction( 'wpml_taxonomy_made_not_translatable', 10, 1 )
			->then( spreadArgs( [ $this, 'onTaxonomyMadeNotTranslatable' ] ) );

		Hooks::onAction( 'created_term', 10, 3 )
			->then( spreadArgs( [ $this, 'onTermCreated' ] ) );

		Hooks::onAction( 'edited_term', 10, 3 )
			->then( spreadArgs( [ $this, 'onTermEdited' ] ) );

		Hooks::onAction( 'added_term_meta', 10, 3 )
			->then( spreadArgs( [ $this, 'onTermMetaChanged' ] ) );

		Hooks::onAction( 'updated_term_meta', 10, 3 )
			->then( spreadArgs( [ $this, 'onTermMetaChanged' ] ) );

		Hooks::onAction( 'deleted_term_meta', 10, 3 )
			->then( spreadArgs( [ $this, 'onTermMetaChanged' ] ) );

		Hooks::onAction( 'pre_delete_term', 10, 2 )
			->then( spreadArgs( [ $this, 'onPreDeleteTerm' ] ) );

		Hooks::onAction( 'wpml_taxonomy_term_content_changed', 10, 1 )
			->then( spreadArgs( [ $this, 'onTermContentChanged' ] ) );
	}

	public function registerStrategy( array $strategies ): array {
		$strategies[] = make( UntranslatedTerms::class );

		return $strategies;
	}

	public function onTaxonomyMadeTranslatable( string $taxonomy ) {
		if ( ! Option::shouldTranslateEverything() ) {
			return;
		}

		make( UntranslatedTerms::class )->markTaxonomyAsUncompleted( $taxonomy );
	}

	public function onTaxonomyMadeNotTranslatable( string $taxonomy ) {
		global $wpdb;

		( new \WPML\TM\Taxonomy\Job\TermJobRowFactory( $wpdb ) )
			->cancelInFlightForTaxonomy( $taxonomy );
	}

	public function onTermCreated( $term_id, $tt_id, $taxonomy ) {
		if ( ! Option::shouldTranslateEverything() ) {
			return;
		}

		global $sitepress;

		if ( ! $sitepress || ! $sitepress->is_translated_taxonomy( $taxonomy ) ) {
			return;
		}

		make( UntranslatedTerms::class )->markTaxonomyAsUncompleted( $taxonomy );
	}

	public function onTermEdited( $term_id, $tt_id, $taxonomy ) {
		global $sitepress;

		if ( ! $sitepress || ! $sitepress->is_translated_taxonomy( $taxonomy ) ) {
			return;
		}

		$this->flagTranslationsNeedUpdate( (int) $tt_id, (string) $taxonomy );
	}

	public function onTermMetaChanged( $meta_id, $term_id, $meta_key ) {
		global $sitepress, $wpdb;

		if ( ! $sitepress || ! $wpdb ) {
			return;
		}

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT term_taxonomy_id, taxonomy FROM {$wpdb->term_taxonomy} WHERE term_id = %d",
				(int) $term_id
			)
		);

		if ( ! $row || ! $sitepress->is_translated_taxonomy( $row->taxonomy ) ) {
			return;
		}

		if ( ! in_array( (string) $meta_key, \WPML\TM\Taxonomy\TranslatableTermMeta::keys( (string) $row->taxonomy ), true ) ) {
			return;
		}

		$this->flagTranslationsNeedUpdate( (int) $row->term_taxonomy_id, (string) $row->taxonomy );
	}

	public function onTermContentChanged( $term_id ) {
		global $sitepress, $wpdb;

		if ( ! $sitepress || ! $wpdb || ! $term_id ) {
			return;
		}

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT term_taxonomy_id, taxonomy FROM {$wpdb->term_taxonomy} WHERE term_id = %d",
				(int) $term_id
			)
		);

		if ( ! $row || ! $sitepress->is_translated_taxonomy( $row->taxonomy ) ) {
			return;
		}

		$this->flagTranslationsNeedUpdate( (int) $row->term_taxonomy_id, (string) $row->taxonomy );
	}

	private function flagTranslationsNeedUpdate( $tt_id, $taxonomy ) {
		global $wpdb;

		$elementType = UntranslatedTerms::ELEMENT_TYPE_PREFIX . $taxonomy;

		$source = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT trid, language_code, source_language_code
					FROM {$wpdb->prefix}icl_translations
					WHERE element_type = %s AND element_id = %d",
				$elementType,
				(int) $tt_id
			)
		);

		if ( ! $source || null !== $source->source_language_code ) {
			return;
		}

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}icl_translation_status ts
					INNER JOIN {$wpdb->prefix}icl_translations t
						ON t.translation_id = ts.translation_id
					SET ts.needs_update = 1
					WHERE t.trid = %d
					AND t.language_code <> %s",
				(int) $source->trid,
				(string) $source->language_code
			)
		);

		make( UntranslatedTerms::class )->markTaxonomyAsUncompleted( $taxonomy );
	}

	public function onPreDeleteTerm( $term_id, $taxonomy ) {
		global $sitepress, $wpdb;

		if ( ! $sitepress || ! $sitepress->is_translated_taxonomy( $taxonomy ) ) {
			return;
		}

		$elementType = UntranslatedTerms::ELEMENT_TYPE_PREFIX . $taxonomy;

		$ttId = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE term_id = %d AND taxonomy = %s",
				(int) $term_id,
				$taxonomy
			)
		);
		if ( ! $ttId ) {
			return;
		}

		$source = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT trid, source_language_code
					FROM {$wpdb->prefix}icl_translations
					WHERE element_type = %s AND element_id = %d",
				$elementType,
				$ttId
			)
		);

		if ( ! $source || null !== $source->source_language_code ) {
			return;
		}

		$this->deleteOrphanInProgressTranslations( (int) $source->trid );
	}

	private function deleteOrphanInProgressTranslations( int $trid ) {
		global $wpdb;

		$translationIds = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT translation_id FROM {$wpdb->prefix}icl_translations
					WHERE trid = %d AND element_id IS NULL",
				$trid
			)
		);

		if ( empty( $translationIds ) ) {
			return;
		}

		$translationIds = array_map( 'intval', $translationIds );

		\WPML_Translation_Records_Delete::jobs_by_translation_ids( $translationIds );
		\WPML_Translation_Records_Delete::translations_by_ids( $translationIds );
	}
}
