<?php

namespace WPML\TM\ATE\TranslateEverything;

use WPML\Element\API\Languages;
use WPML\FP\Cast;
use WPML\FP\Fns;
use WPML\FP\Lst;
use WPML\FP\Obj;
use WPML\Setup\Option;
use WPML\TM\API\ATE\CachedLanguageMappings;
use WPML\TM\API\ATE\LanguageMappings;
use WPML\TM\AutomaticTranslation\Actions\Actions;
use function WPML\Container\make;
use function WPML\FP\pipe;

class UntranslatedTerms extends AbstractUntranslatedElements {

	const ELEMENT_TYPE_PREFIX = 'tax_';

	protected function getElementTypePrefix(): string {
		return self::ELEMENT_TYPE_PREFIX;
	}

	public function getEligibleLanguageCodes( bool $cached = false ): array {
		$codes  = parent::getEligibleLanguageCodes( $cached );
		$mapper = $cached ? CachedLanguageMappings::class : LanguageMappings::class;

		foreach ( $this->getSecondaryOriginLanguages() as $originLang ) {
			$codes = array_merge( $codes, $mapper::geCodesEligibleForAutomaticTranslations( $originLang ) );
		}

		return array_values( array_unique( $codes ) );
	}

	private function getSecondaryOriginLanguages(): array {
		$elementTypes = array_map(
			function ( $taxonomy ) {
				return self::ELEMENT_TYPE_PREFIX . $taxonomy;
			},
			$this->getTypes()
		);

		if ( empty( $elementTypes ) ) {
			return [];
		}

		$wpdb         = $this->wpdb;
		$placeholders = Lst::join( ',', array_fill( 0, count( $elementTypes ), '%s' ) );

		$sql = "SELECT DISTINCT language_code FROM {$wpdb->prefix}icl_translations
			WHERE element_type IN ( {$placeholders} )
			AND source_language_code IS NULL
			AND language_code != %s";

		$codes = $wpdb->get_col(
			$wpdb->prepare(
				$sql,
				array_merge( $elementTypes, [ Languages::getDefaultCode() ] )
			)
		);

		return array_map( 'strval', $codes ?: [] );
	}

	protected function getCompletableLanguageCodes(): array {
		return array_values( array_unique( array_keys( Languages::getActive() ) ) );
	}

	public function getTypeWithLanguagesToProcess() {
		$taxonomies = $this->getTaxonomiesToTranslate(
			$this->getTypes(),
			$this->getEligibleLanguageCodes( true )
		);

		return wpml_collect( $taxonomies )->first();
	}

	private function getTaxonomiesToTranslate( array $taxonomies, array $targetLanguages ) {
		$completed                           = $this->getCompleted();
		$getLanguageCodesNotCompletedForType = pipe( Obj::propOr( [], Fns::__, $completed ), Lst::diff( $targetLanguages ) );

		$mapTaxonomy = function ( $taxonomy ) use ( $getLanguageCodesNotCompletedForType ) {
			return [ $taxonomy, $getLanguageCodesNotCompletedForType( $taxonomy ) ];
		};

		$pipeTaxonomies = pipe(
			Fns::map( $mapTaxonomy ),
			Fns::filter( pipe( Obj::prop( 1 ), Lst::length() ) )
		);

		return $pipeTaxonomies( $taxonomies );
	}

	public function getElementsToProcess( $languages, $type, $queueSize ) {
		if ( empty( $languages ) ) {
			return [];
		}

		$languages_part      = $this->buildLanguagesUnion( $languages );
		$acceptable_statuses = ICL_TM_NOT_TRANSLATED . ', ' . ICL_TM_ATE_CANCELLED;
		$element_type        = self::ELEMENT_TYPE_PREFIX . $type;

		$sql = "
			SELECT original_element.element_id, languages.code
			FROM {$this->wpdb->prefix}icl_translations original_element
			INNER JOIN ( {$languages_part} ) as languages
			LEFT JOIN {$this->wpdb->prefix}icl_translations translations
				ON translations.trid = original_element.trid
				AND translations.language_code = languages.code
			LEFT JOIN {$this->wpdb->prefix}icl_translation_status translation_status
				ON translation_status.translation_id = translations.translation_id

			WHERE original_element.element_type = %s
				AND original_element.source_language_code IS NULL
				-- No `AND original_element.language_code = <default>` here, on
				-- purpose (wpmldev-8068). `source_language_code IS NULL` already
				-- identifies the ORIGINAL of a translation group, whatever
				-- language it was authored in - which is exactly how
				-- UntranslatedPosts keys its inventory. Pinning the original to
				-- the site default made every term authored directly in a
				-- secondary language invisible to this sweep: never queued,
				-- never dispatched, never counted. The `languages.code <>
				-- original_element.language_code` line below is what keeps a
				-- term from being queued into its own language, and it works for
				-- any origin language.
				AND languages.code <> original_element.language_code
				AND (
					translations.translation_id IS NULL
					OR translation_status.status IN ({$acceptable_statuses})
					OR translation_status.needs_update = 1
				)
			ORDER BY original_element.element_id, languages.code
			LIMIT %d
		";

		$result = $this->wpdb->get_results(
			$this->wpdb->prepare(
				$sql,
				$element_type,
				$queueSize
			),
			ARRAY_N
		);

		return Fns::map( Obj::evolve( [ 0 => Cast::toInt() ] ), $result );
	}

	private function getOriginalLanguage( int $termTaxonomyId, string $elementType ): string {
		$wpdb = $this->wpdb;

		$lang = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT language_code FROM {$wpdb->prefix}icl_translations
				WHERE element_id = %d AND element_type = %s AND source_language_code IS NULL",
				$termTaxonomyId,
				$elementType
			)
		);

		return $lang ? (string) $lang : Languages::getDefaultCode();
	}


	public function createTranslationJobs( Actions $actions, array $elements, $type ) {
		unset( $actions );

		$elementType = self::ELEMENT_TYPE_PREFIX . $type;
		$dispatcher  = make( TaxonomyTermJobDispatcher::class );

		$models      = [];
		$descriptors = [];
		$seenPairs   = [];

		foreach ( $elements as $element ) {
			$termTaxonomyId = (int) $element[0];
			$targetLang     = (string) $element[1];

			$pairKey = $termTaxonomyId . '|' . $targetLang;
			if ( isset( $seenPairs[ $pairKey ] ) ) {
				continue;
			}
			$seenPairs[ $pairKey ] = true;

			$sourceLang = $this->getOriginalLanguage( $termTaxonomyId, $elementType );

			if ( $targetLang === $sourceLang ) {
				continue;
			}

			$model = $dispatcher->prepareTermJob( $termTaxonomyId, (string) $type, $sourceLang, $targetLang, true );
			if ( $model ) {
				$models[]      = $model;
				$descriptors[] = [
					'elementId'   => $termTaxonomyId,
					'lang'        => $targetLang,
					'elementType' => $elementType,
					'jobId'       => (int) $model->id,
				];
			}
		}

		if ( $models ) {
			$error    = null;
			$accepted = $dispatcher->sendToAte( $models, $error );

			if ( $accepted < count( $models ) ) {
				$this->releaseUndispatchedJobs( $descriptors, (string) $error, (string) $type );
			}
		}

		return $descriptors;
	}

	private function releaseUndispatchedJobs( array $descriptors, string $error, string $type ) {
		global $wpdb;

		$jobIds = array_values( array_filter( array_map( static function ( $d ) {
			return isset( $d['jobId'] ) ? (int) $d['jobId'] : 0;
		}, $descriptors ) ) );

		if ( ! $jobIds ) {
			return;
		}

		$this->markTaxonomyAsUncompleted( $type );

		$in = wpml_prepare_in( $jobIds, '%d' );

		$strandedRids = $wpdb->get_col(
			"SELECT rid FROM {$wpdb->prefix}icl_translate_job
			 WHERE job_id IN ({$in}) AND ( editor_job_id IS NULL OR editor_job_id = 0 )"
		);

		if ( ! $strandedRids ) {
			return;
		}

		$ridIn = wpml_prepare_in( array_map( 'intval', $strandedRids ), '%d' );

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}icl_translation_status
				 SET status = %d
				 WHERE rid IN ({$ridIn}) AND status = %d",
				ICL_TM_NOT_TRANSLATED,
				ICL_TM_IN_PROGRESS
			)
		);

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log(
				'WPML taxonomy term jobs were not accepted by ATE and have been returned to the queue: '
				. count( $strandedRids ) . ' job(s)' . ( '' !== $error ? ' — ' . $error : '' )
			);
		}
	}

	protected function getCompleted(): array {
		return Option::getTranslateEverythingCompletedTaxonomies();
	}

	protected function setCompleted( array $completed ) {
		Option::setTranslateEverythingCompletedTaxonomies( $completed );
	}

	protected function getTypes(): array {
		return TranslatableTaxonomies::getEligibleForTea();
	}

	public function markTaxonomyAsUncompleted( string $taxonomy ) {
		$completed              = $this->getCompleted();
		$completed[ $taxonomy ] = [];

		$this->setCompleted( $completed );
	}
}
