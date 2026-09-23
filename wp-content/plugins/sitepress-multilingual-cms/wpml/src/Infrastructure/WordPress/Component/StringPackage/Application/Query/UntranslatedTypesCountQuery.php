<?php

namespace WPML\Infrastructure\WordPress\Component\StringPackage\Application\Query;

use WPML\Core\Component\StringPackage\Application\Query\PackageDefinitionQueryInterface;
use WPML\Core\Port\Persistence\Exception\DatabaseErrorException;
use WPML\Core\Port\Persistence\QueryHandlerInterface;
use WPML\Core\Port\Persistence\QueryPrepareInterface;
use WPML\Core\SharedKernel\Component\Item\Application\Query\Dto\UntranslatedTypeCountDto;
use WPML\Core\SharedKernel\Component\Item\Application\Query\UntranslatedTypesCountQueryInterface;
use WPML\Core\SharedKernel\Component\Language\Application\Query\Dto\LanguageDto;
use WPML\Core\SharedKernel\Component\Language\Application\Query\LanguagesQueryInterface;
use WPML\Core\SharedKernel\Component\Translation\Domain\TranslationStatus;

class UntranslatedTypesCountQuery implements UntranslatedTypesCountQueryInterface {

  const IGNORED_IF_EMPTY = [ 'block' ];

  private $queryHandler;

  private $queryPrepare;

  private $languagesQuery;

  private $packageDefinitionRepository;


  public function __construct(
    QueryHandlerInterface $queryHandler,
    QueryPrepareInterface $queryPrepare,
    LanguagesQueryInterface $languagesQuery,
    PackageDefinitionQueryInterface $packageDefinitionRepository
  ) {
    $this->queryHandler                = $queryHandler;
    $this->queryPrepare                = $queryPrepare;
    $this->languagesQuery              = $languagesQuery;
    $this->packageDefinitionRepository = $packageDefinitionRepository;
  }


  public function forKind() {
    return UntranslatedTypesCountQueryInterface::KIND_PACKAGE;
  }


  public function get( array $queryData = [] ): array {
    $languageCrossJoin = $this->buildLanguageCrossJoin();

    $packageInfo = $this->packageDefinitionRepository->getInfoList();

    $translatablePackages = $this->buildTranslatablePackagesCondition(
      array_keys( $packageInfo )
    );

    $sql = "
      SELECT
        package.kind,
        package.kind_slug,
        COUNT( DISTINCT package.ID ) as count
      FROM {$this->queryPrepare->prefix()}icl_string_packages package
      LEFT JOIN {$this->queryPrepare->prefix()}icl_translations original_translation
        ON original_translation.element_id = package.ID AND original_translation.element_type LIKE 'package_%'
      {$languageCrossJoin}
      LEFT JOIN {$this->queryPrepare->prefix()}icl_translations translations
        ON translations.trid = original_translation.trid AND translations.language_code = langs.code
      LEFT JOIN {$this->queryPrepare->prefix()}icl_translation_status translation_status
        ON translation_status.translation_id = translations.translation_id
      WHERE package.kind_slug IN ({$translatablePackages})
            AND langs.code != original_translation.language_code
            AND ( translation_status.status IS NULL OR translation_status.status = %d )
      GROUP BY package.kind
    ";

    $sql = $this->queryPrepare->prepare(
      $sql,
      TranslationStatus::NOT_TRANSLATED
    );

    try {
      $rowResult = $this->queryHandler->query( $sql )->getResults();

      $existingSlugs = array_column( $rowResult, 'kind_slug' );

      foreach ( $packageInfo as $slug => $info ) {
        if (
          in_array( $slug, $existingSlugs, true )
          || in_array( strtolower( $slug ), self::IGNORED_IF_EMPTY, true )
        ) {
          continue;
        }

        $rowResult[] = [
          'kind'      => $info->getTitle(),
          'kind_slug' => $slug,
          'count'     => 0,
        ];
      }

      return array_map(
        function ( $row ) use ( $packageInfo ) {
          $info = $packageInfo[ $row['kind_slug'] ] ?? null;

          $pluralName   = $info ? $info->getPlural() : $row['kind'];
          $singularName = $info ? $info->getTitle() : $row['kind'];

          return new UntranslatedTypeCountDto(
            $pluralName,
            $singularName,
            $row['count'],
            UntranslatedTypesCountQueryInterface::KIND_PACKAGE,
            $row['kind_slug']
          );
        },
        $rowResult
      );
    } catch ( DatabaseErrorException $e ) {
      return [];
    }
  }


  public function getSomeIds( $numberOfIdsToFetch, $offset, $type ) {
    $languageCrossJoin = $this->buildLanguageCrossJoin();

    $sql = "
      SELECT DISTINCT
        package.ID
      FROM {$this->queryPrepare->prefix()}icl_string_packages package
      LEFT JOIN {$this->queryPrepare->prefix()}icl_translations original_translation
        ON original_translation.element_id = package.ID
        AND original_translation.element_type = CONCAT('package_', %s)
      {$languageCrossJoin}
      LEFT JOIN {$this->queryPrepare->prefix()}icl_translations translations
        ON translations.trid = original_translation.trid
        AND translations.language_code = langs.code
      LEFT JOIN {$this->queryPrepare->prefix()}icl_translation_status translation_status
        ON translation_status.translation_id = translations.translation_id
      WHERE package.kind_slug = %s
        AND ( translation_status.status IS NULL OR translation_status.status = %d )
      ORDER BY package.ID ASC
      LIMIT %d OFFSET %d
    ";

    try {
      $ids = $this->queryHandler->queryColumn(
        $this->queryPrepare->prepare(
          $sql,
          $type,
          $type,
          TranslationStatus::NOT_TRANSLATED,
          $numberOfIdsToFetch,
          $offset
        )
      );
      return $ids;
    } catch ( DatabaseErrorException $e ) {
      return [];
    }
  }


  private function buildLanguageCrossJoin(): string {
    $languageSelect = array_map(
      function ( LanguageDto $language ) {
        return $this->queryPrepare->prepare( 'SELECT %s AS code', $language->getCode() );
      },
      $this->languagesQuery->getActive()
    );

    $languageSelect    = implode( ' UNION ALL ', $languageSelect );
    $languageCrossJoin = "
			CROSS JOIN (
				$languageSelect
			) as langs
		";

    return $languageCrossJoin;
  }


  private function buildTranslatablePackagesCondition( array $slugs ): string {
    $translatablePackages = array_map(
      function ( string $packageName ): string {
        return $this->queryPrepare->prepare( '%s', $packageName );
      },
      $slugs
    );

    return implode( ',', $translatablePackages );
  }


}
