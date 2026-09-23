<?php

namespace WPML\Infrastructure\WordPress\Component\Item\Application\Query\SearchQuery\QueryBuilder\ManyLanguagesStrategy;

use WPML\Core\Component\Post\Application\Query\Criteria\SearchCriteria;
use WPML\Core\Port\Persistence\QueryPrepareInterface;
use WPML\Core\SharedKernel\Component\Post\Domain\TranslationEditorPreference;
use WPML\Infrastructure\WordPress\Component\Item\Application\Query\HeldOutTranslationCondition;
use WPML\Infrastructure\WordPress\Component\Item\Application\Query\SearchQuery\QueryBuilder\SearchCriteriaQueryBuilder\SortingCriteriaQueryBuilder;
use WPML\Infrastructure\WordPress\Component\Item\Application\Query\SearchQuery\QueryBuilder\SearchQueryBuilderInterface;

class SearchQueryBuilder implements SearchQueryBuilderInterface {
  use SearchQueryBuilderTrait;

  const TRANSLATOR_NOTE_META_KEY = '_icl_translator_note';

  private $queryPrepare;

  private $sortingQueryBuilder;

  private $heldOutCondition;

  const POST_COLUMNS = "
    p.ID,
    p.post_title,
    p.post_status,
    p.post_date,
    p.post_type
  ";


  public function __construct(
    QueryPrepareInterface $queryPrepare,
    SortingCriteriaQueryBuilder $sortingQueryBuilder,
    HeldOutTranslationCondition $heldOutCondition
  ) {
    $this->queryPrepare        = $queryPrepare;
    $this->sortingQueryBuilder = $sortingQueryBuilder;
    $this->heldOutCondition    = $heldOutCondition;
  }


  public function build( SearchCriteria $criteria ): string {
    return $this->buildQueryWithFields( $criteria );
  }


  public function buildCount( SearchCriteria $criteria ): string {
    $sql = $this->buildQueryWithFields( $criteria, false );

    $sql = "
      SELECT COUNT(t.ID)
      FROM (
        $sql
      ) t
    ";

    return $sql;
  }


  private function getFields(): string {
    $postColumns  = self::POST_COLUMNS;
    $editorNative = TranslationEditorPreference::EDITOR_NATIVE;

    return "
        {$postColumns},
        meta_tn.meta_value AS translator_note,
        CASE
          WHEN meta_ec.meta_value = '{$editorNative}' THEN 'yes'
          WHEN meta_ec.meta_value IS NOT NULL THEN 'no'
          WHEN meta_ne.meta_value = 'yes' AND meta_we.meta_value IS NULL THEN 'yes'
          WHEN meta_ne.meta_value = 'no' THEN 'no'
          ELSE NULL
        END AS use_native_editor
		";
  }


  private function buildQueryWithFields( SearchCriteria $criteria, bool $withPagination = true ): string {
    $preparedSourceLanguage = $this->queryPrepare->prepare( '%s', $criteria->getSourceLanguageCode() );
    $preparedType           = $this->queryPrepare->prepare( '%s', $criteria->getType() );

    $escapedLanguageCodes      = $this->getEscapedLanguageCodes( $criteria );
    $gluedEscapedLanguageCodes = implode( ',', $escapedLanguageCodes );

    $fields = $this->getFields();

    $sql = "
      SELECT
          {$fields}
      FROM {$this->queryPrepare->prefix()}posts p
      INNER JOIN {$this->queryPrepare->prefix()}icl_translations source_t
      ON source_t.element_id = p.ID
        AND source_t.element_type = CONCAT('post_', p.post_type)
        AND source_t.language_code = {$preparedSourceLanguage}
            
            
      LEFT JOIN {$this->queryPrepare->prefix()}icl_translations target_t
         ON target_t.trid = source_t.trid
             AND target_t.language_code IN ({$gluedEscapedLanguageCodes})
       LEFT JOIN {$this->queryPrepare->prefix()}icl_translation_status target_ts
         ON target_ts.translation_id = target_t.translation_id
      
      LEFT JOIN {$this->queryPrepare->prefix()}postmeta meta_tn
        ON meta_tn.post_id = p.ID
        AND meta_tn.meta_key = '" . self::TRANSLATOR_NOTE_META_KEY . "'
      LEFT JOIN {$this->queryPrepare->prefix()}postmeta meta_ec
        ON meta_ec.post_id = p.ID
        AND meta_ec.meta_key = '" . TranslationEditorPreference::POST_META_KEY_EDITOR . "'
      LEFT JOIN {$this->queryPrepare->prefix()}postmeta meta_ne
        ON meta_ne.post_id = p.ID
        AND meta_ne.meta_key = '" . TranslationEditorPreference::POST_META_KEY_USE_NATIVE . "'
      LEFT JOIN {$this->queryPrepare->prefix()}postmeta meta_we
        ON meta_we.post_id = p.ID
        AND meta_we.meta_key = '" . TranslationEditorPreference::POST_META_KEY_USE_WPML . "'
      WHERE p.post_type = {$preparedType}
          {$this->buildPostStatusCondition( $criteria->getPublicationStatus() )}
          {$this->buildPostTitleCondition( $criteria )}
          {$this->buildTaxonomyTermCondition( $criteria )}
          {$this->buildParentCondition( $criteria )}
          {$this->buildHeldOutCondition( $criteria )}
          {$this->buildTranslationStatusConditionWrapper( $criteria, $escapedLanguageCodes )}
      
      GROUP BY p.ID  
      {$this->buildSortingQueryPart( $criteria )}
        
    ";

    if ( $withPagination ) {
      $sql .= ' ' . $this->buildPagination( $criteria );
    }

    return $sql;
  }


  private function buildSortingQueryPart( SearchCriteria $criteria ): string {
    return $this->sortingQueryBuilder->build( $criteria->getSortingCriteria() );
  }


  private function buildPostTitleCondition(
    SearchCriteria $criteria
  ): string {
    $searchKeyword = $this->queryPrepare->escLike( $criteria->getTitle() );
    if ( $searchKeyword !== '' ) {
      return $this->queryPrepare->prepare(
        'AND p.post_title LIKE %s',
        '%' . $searchKeyword . '%'
      );
    }

    return '';
  }


  private function buildTranslationStatusConditionWrapper(
    SearchCriteria $criteria,
    array $targetLanguageCodes
  ): string {
    return 'AND ' . $this->buildTranslationStatusCondition( $criteria, $targetLanguageCodes );
  }


  private function buildTaxonomyTermCondition( SearchCriteria $criteria ): string {
    if ( ! $criteria->getTaxonomyId() || ! $criteria->getTermId() ) {
      return '';
    }

    $where = $this->queryPrepare->prepare(
      "WHERE tax.taxonomy = %s AND tax.term_id = %d",
      $criteria->getTaxonomyId(),
      $criteria->getTermId()
    );

    return " AND p.ID IN (
      SELECT object_id
      FROM {$this->queryPrepare->prefix()}term_relationships rel
      JOIN {$this->queryPrepare->prefix()}term_taxonomy tax " .
           "ON rel.term_taxonomy_id = tax.term_taxonomy_id
      $where
      ) ";
  }


  private function buildParentCondition(
    SearchCriteria $criteria
  ): string {
    if ( $criteria->getParentId() ) {
      return $this->queryPrepare->prepare(
        'AND p.post_parent = %d',
        $criteria->getParentId()
      );
    }

    return '';
  }


  private function buildHeldOutCondition( SearchCriteria $criteria ): string {
    if ( ! $criteria->isHeldOut() ) {
      return '';
    }

    $exists = $this->heldOutCondition->existsSql( 'source_t.trid' );

    return $exists === '' ? ' AND 1 = 0' : " AND $exists";
  }


  private function buildPagination( SearchCriteria $criteria ): string {
    return $this->queryPrepare->prepare(
      'LIMIT %d OFFSET %d',
      $criteria->getLimit(),
      $criteria->getOffset()
    );
  }


  private function getEscapedLanguageCodes( SearchCriteria $criteria ): array {
    $languageCodes = $criteria->getTargetLanguageCodes();

    $escapedLanguageCodes = array_map(
      function ( $languageCode ) {
        return $this->queryPrepare->prepare( '%s', $languageCode );
      },
      $languageCodes
    );

    return $escapedLanguageCodes;
  }


}
