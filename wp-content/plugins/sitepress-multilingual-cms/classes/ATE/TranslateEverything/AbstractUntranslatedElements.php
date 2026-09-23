<?php

namespace WPML\TM\ATE\TranslateEverything;

use WPML\API\PostTypes;
use WPML\Element\API\Languages;
use WPML\FP\Lst;
use WPML\LanguageEditor\TranslationPause;
use WPML\Setup\Option;
use WPML\TM\API\ATE\CachedLanguageMappings;
use WPML\TM\API\ATE\LanguageMappings;
use WPML\TM\AutomaticTranslation\Actions\Actions;

abstract class AbstractUntranslatedElements implements UntranslatedElementsInterface {

	protected $wpdb;

	private $oldJobsEditor;

	public function __construct( \wpdb $wpdb, ?\WPML_TM_Old_Jobs_Editor $oldJobsEditor = null ) {
		$this->wpdb          = $wpdb;
		$this->oldJobsEditor = $oldJobsEditor;
	}

	/**
	 * Resolved on first use, never in the constructor (wpmldev-8194).
	 *
	 * Auryn cannot autowire the nullable argument, so every make() of a
	 * strategy hands in null. The editor is read in exactly one place,
	 * {@see self::buildOldEditorCondition()}, on the Translate Everything query
	 * path. The strategies are also built on every licence by the bookkeeping
	 * hooks (edited_term, wpml_st_package_created, ...), and on a Blog licence
	 * tm.php is not loaded, so the loader function does not exist: resolving it
	 * eagerly turned "Update category" into a fatal error.
	 *
	 * @return \WPML_TM_Old_Jobs_Editor|null Null when Translation Management is not loaded for this request.
	 */
	private function getOldJobsEditor() {
		if ( ! $this->oldJobsEditor && function_exists( 'wpml_tm_load_old_jobs_editor' ) ) {
			$this->oldJobsEditor = \wpml_tm_load_old_jobs_editor();
		}

		return $this->oldJobsEditor;
	}

	public function getQueueSize(): int {
		return 15;
	}

	public function getEligibleLanguageCodes( bool $cached = false ): array {
		$mapper = $cached ? CachedLanguageMappings::class : LanguageMappings::class;

		return TranslationPause::filterTranslatable( $mapper::geCodesEligibleForAutomaticTranslations() );
	}

	public function isEverythingProcessed( $cached = false ) {
		$completed = $this->getCompleted();
		$languages = $this->getEligibleLanguageCodes( $cached );

		foreach ( $this->getTypes() as $type ) {
			$completedLanguages = $completed[ $type ] ?? [];
			$remainingLanguages = Lst::diff( $languages, $completedLanguages );
			if ( count( $remainingLanguages ) > 0 ) {
				return false;
			}
		}

		return true;
	}

	public function markTypeAsCompleted( string $type ) {
		$completed = $this->getCompleted();
		$languages          = $this->getCompletableLanguageCodes();
		$completed[ $type ] = array_merge( $completed[ $type ] ?? [], $languages );

		$this->setCompleted( $completed );
	}

	protected function getCompletableLanguageCodes(): array {
		return Languages::getSecondaryCodes();
	}

	public function markEverythingAsCompleted() {
		$types     = $this->getTypes();
		$languages = $this->getCompletableLanguageCodes();
		$completed = $this->getCompleted();

		foreach ( $types as $type ) {
			$completed[ $type ] = array_merge( $completed[ $type ] ?? [], $languages );
		}

		$this->setCompleted( $completed );
	}


	public function markEverythingAsUncompleted() {
		$this->setCompleted( [] );
	}

	public function markLanguagesAsCompleted( array $languages ) {
		$types     = $this->getTypes();
		$completed = $this->getCompleted();

		foreach ( $types as $type ) {
			$completed[ $type ] = array_merge( $completed[ $type ] ?? [], $languages );
		}

		$this->setCompleted( $completed );
	}

	public function markLanguagesAsUncompleted( array $languages ) {
		$types     = $this->getTypes();
		$completed = $this->getCompleted();

		foreach ( $types as $type ) {
			$typeValues         = Lst::diff( $completed[ $type ] ?? [], $languages );
			$completed[ $type ] = is_array( $typeValues ) ? array_values( $typeValues ) : $typeValues;
		}

		$this->setCompleted( $completed );
	}

	abstract protected function getCompleted(): array;

	abstract protected function setCompleted( array $completed );

	abstract protected function getTypes(): array;

	protected function buildOldEditorCondition(): string {
		$oldEditorCondition = '';
		$oldJobsEditor      = $this->getOldJobsEditor();

		if ( $oldJobsEditor && $oldJobsEditor->editorForTranslationsPreviouslyCreatedUsingCTE() === \WPML_TM_Editors::WPML ) {
			$editor = \WPML_TM_Editors::WPML;
			$oldEditorCondition = "AND (
				translation_status.needs_update = 0 OR IFNULL(
					(
					  SELECT jobs.editor FROM {$this->wpdb->prefix}icl_translate_job jobs
					  WHERE jobs.rid = translation_status.rid
					  ORDER BY jobs.job_id DESC
					  LIMIT 1
					),
					'ate'
				) != '{$editor}'
			)";
		}

		return $oldEditorCondition;
	}

	protected function buildLanguagesUnion( array $languages ): string {
		$wpdb = $this->wpdb;

		$languages = array_values( array_unique( array_map( 'strval', $languages ) ) );

		return implode(
			' UNION ALL ',
			array_map(
				function ( $code ) use ( $wpdb ) {
					return $wpdb->prepare( 'SELECT %s AS code', $code );
				},
				$languages
			)
		);
	}

	abstract protected function getElementTypePrefix(): string;

	public function createTranslationJobsFromAllLanguages( Actions $actions, array $elements, $type ) {
		$sourceLanguageAndElements = [];
		foreach ( $elements as $element ) {
			$id = $element[0];

			$sourceLang = count( $element ) === 3 ? $element[1] : Languages::getDefaultCode();
			$targetLang = count( $element ) === 3 ? $element[2] : $element[1];

			if ( ! isset( $sourceLanguageAndElements[ $sourceLang ] ) ) {
				$sourceLanguageAndElements[ $sourceLang ] = [];
			}
			$sourceLanguageAndElements[ $sourceLang ][] = [ $id, $targetLang ];
		}

		$result       = [];
		$prefixedType = $this->getElementTypePrefix() . $type;
		foreach ( $sourceLanguageAndElements as $sourceLang => $elements ) {
			$jobs   = $actions->createNewTranslationJobs( $sourceLang, $elements, $prefixedType );
			$result = array_merge( $result, $jobs );
		}

		return $result;
	}
}
