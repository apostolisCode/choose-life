<?php

namespace WPML\Infrastructure\WordPress\Component\String\Application\Query;

use WPML\Core\Port\Persistence\Exception\DatabaseErrorException;
use WPML\Core\Port\Persistence\QueryHandlerInterface;
use WPML\Core\Port\Persistence\QueryPrepareInterface;
use WPML\Core\SharedKernel\Component\Item\Application\Query\Dto\UntranslatedTypeCountDto;
use WPML\Core\SharedKernel\Component\Item\Application\Query\UntranslatedTypesCountQueryInterface;
use WPML\Core\SharedKernel\Component\Language\Application\Query\Dto\LanguageDto;
use WPML\Core\SharedKernel\Component\Language\Application\Query\LanguagesQueryInterface;
use WPML\Core\SharedKernel\Component\Language\Domain\LanguageCode;

class UntranslatedTypesCountQuery implements UntranslatedTypesCountQueryInterface {

  const SITE_IDENTITY_STRING_DOMAIN   = 'WP';
  const SITE_IDENTITY_STRING_BLOGNAME = 'Blog Title';
  const SITE_IDENTITY_STRING_TAGLINE  = 'Tagline';

  private $queryHandler;

  private $queryPrepare;

  private $languagesQuery;

  private $englishSourceCode;


  public function __construct(
    QueryHandlerInterface $queryHandler,
    QueryPrepareInterface $queryPrepare,
    LanguagesQueryInterface $languagesQuery
  ) {
    $this->queryHandler   = $queryHandler;
    $this->queryPrepare   = $queryPrepare;
    $this->languagesQuery = $languagesQuery;
  }


  public function forKind() {
    return UntranslatedTypesCountQueryInterface::KIND_STRING;
  }


  public function get( array $queryData = [] ): array {
    $languageCrossJoin = $this->buildLanguageCrossJoin();

    $sql = "
      SELECT
        COUNT( DISTINCT strings.id ) as count
			FROM {$this->queryPrepare->prefix()}icl_strings strings
			{$languageCrossJoin}
			LEFT JOIN {$this->queryPrepare->prefix()}icl_string_translations translations
				ON strings.id = translations.string_id AND translations.language = langs.code
			WHERE {$this->buildInScopeCondition()}
				AND ( translations.status IS NULL OR translations.status = 0 )
				AND strings.language = %s
			ORDER BY langs.code, strings.id ASC
		";

    $sql = $this->queryPrepare->prepare(
      $sql,
      6,
      self::SITE_IDENTITY_STRING_DOMAIN,
      self::SITE_IDENTITY_STRING_BLOGNAME,
      self::SITE_IDENTITY_STRING_TAGLINE,
      $this->englishSourceCode()
    );

    try {
      $count = (int) $this->queryHandler->querySingle( $sql );
    } catch ( DatabaseErrorException $e ) {
      $count = 0;
    }

    if ( $count === 0 ) {
      return [];
    }

    return [
      new UntranslatedTypeCountDto(
        /* translators: Plural name of a kind of content on the Translate Everything word-count list: texts that come from the theme, the plugins and the widgets rather than from pages. */
        __( 'Frontend Strings', 'wpml' ),
        /* translators: Singular of the same name on the Translate Everything word-count list: one text that comes from the theme, a plugin or a widget. */
        __( 'Frontend String', 'wpml' ),
        $count,
        'string',
        ''
      )
    ];
  }


  public function getSomeIds( $numberOfIdsToFetch, $offset, $type = '' ) {
    $languageCrossJoin = $this->buildLanguageCrossJoin();

    $sql = "
      SELECT DISTINCT
        strings.id
			FROM {$this->queryPrepare->prefix()}icl_strings strings
			{$languageCrossJoin}
			LEFT JOIN {$this->queryPrepare->prefix()}icl_string_translations translations
				ON strings.id = translations.string_id AND translations.language = langs.code
			WHERE {$this->buildInScopeCondition()}
				AND ( translations.status IS NULL OR translations.status = 0 )
				AND strings.language = %s
			ORDER BY strings.id ASC
      LIMIT %d OFFSET %d
    ";

    try {
      $ids = $this->queryHandler->queryColumn(
        $this->queryPrepare->prepare(
          $sql,
          6,
          self::SITE_IDENTITY_STRING_DOMAIN,
          self::SITE_IDENTITY_STRING_BLOGNAME,
          self::SITE_IDENTITY_STRING_TAGLINE,
          $this->englishSourceCode(),
          $numberOfIdsToFetch,
          $offset
        )
      );
      return $ids;
    } catch ( DatabaseErrorException $e ) {
      return [];
    }
  }


  private function englishSourceCode(): string {
    if ( null === $this->englishSourceCode ) {
      $activeCodes = array_map(
        function ( LanguageDto $language ) {
          return $language->getCode();
        },
        $this->languagesQuery->getActive()
      );

      $this->englishSourceCode = LanguageCode::englishSourceCode( $activeCodes, $this->languagesQuery->getDefaultCode() );
    }

    return $this->englishSourceCode;
  }


  private function buildInScopeCondition(): string {
    return "(
					(
						strings.string_type = 1
						AND EXISTS (
							SELECT 1
							FROM {$this->queryPrepare->prefix()}icl_string_positions positions
							WHERE positions.string_id = strings.id
								AND positions.kind = %d
						)
					)
					OR (
						strings.context = %s
						AND strings.name IN ( %s, %s )
					)
				)";
  }


  private function buildLanguageCrossJoin(): string {
    $secondary = array_map(
      function ( LanguageDto $language ) {
        return $language->getCode();
      },
      $this->languagesQuery->getSecondary()
    );

    $secondaryWithoutEnglish = array_filter(
      $secondary,
      function ( string $language ) {
        return ! LanguageCode::isEnglish( $language );
      }
    );

    $languageSelect    = array_map(
      function ( $languageCode ) {
        return $this->queryPrepare->prepare( 'SELECT %s AS code', $languageCode );
      },
      $secondaryWithoutEnglish
    );
    $languageSelect    = implode( ' UNION ALL ', $languageSelect );
    $languageCrossJoin = "
			CROSS JOIN (
				$languageSelect
			) as langs
		";

    return $languageCrossJoin;
  }


}
