<?php

namespace WPML\Legacy\Component\Language\Application\Query;

use WPML\Core\SharedKernel\Component\Language\Application\Query\Dto\LanguageDto;
use WPML\Core\SharedKernel\Component\Language\Application\Query\LanguagesQueryInterface;
use WPML\TM\API\ATE\CachedLanguageMappings;
use WPML\TM\API\ATE\LanguageMappings;

class AutomaticTranslationsSupportInfoDecoratorForLanguagesQuery implements LanguagesQueryInterface {

  private $languagesQuery;


  public function __construct( LanguagesQueryInterface $languagesQuery ) {
    $this->languagesQuery = $languagesQuery;
  }


  public function getDefaultCode(): string {
    return $this->languagesQuery->getDefaultCode();
  }


  public function getCurrentLanguageCode(): string {
    return $this->languagesQuery->getCurrentLanguageCode();
  }


  public function getDefault(): LanguageDto {
    $default = $this->languagesQuery->getDefault();

    return $this->addInfoAboutAutomaticTranslationsSupport( [ $default ] )[0];
  }


  public function getActive() {
    $active = $this->languagesQuery->getActive();

    return $this->addInfoAboutAutomaticTranslationsSupport( $active );
  }


  public function getSecondary( bool $withRespectToCurrentLang = false, $currentLang = null ) {
    $secondary = $this->languagesQuery->getSecondary( $withRespectToCurrentLang, $currentLang );

    return $this->addInfoAboutAutomaticTranslationsSupport( $secondary );
  }


  private function addInfoAboutAutomaticTranslationsSupport( array $languages ): array {
    $languagesData = CachedLanguageMappings::getAllLanguagesWithAutomaticSupportInfo();

    if ( ! is_array( $languagesData ) ) {
      return [];
    }

    $languagesData = array_filter(
      $languagesData,
      function ( $languageData ) {
        return is_array( $languageData ) && isset( $languageData['can_be_translated_automatically'] );
      }
    );

    $explicitlyMapped = $this->explicitlyMappedCodes();
    $defaultCode      = $this->languagesQuery->getDefaultCode();

    return array_map(
      function ( LanguageDto $language ) use ( $languagesData, $explicitlyMapped, $defaultCode ) {
        if ( in_array( $language->getCode(), $explicitlyMapped, true ) ) {
          $language->setSupportsAutomaticTranslations( true );

          return $language;
        }

        $matchedLanguage = $languagesData[ $language->getCode() ] ?? null;
        if ( ! $matchedLanguage ) {
          return $language;
        }

        $doesSupport = $matchedLanguage['can_be_translated_automatically'];
        $language->setSupportsAutomaticTranslations( $doesSupport );
        $language->setAutomaticTranslationsUnavailableReason(
          $this->unavailableReason( (bool) $doesSupport, $language->getCode(), $defaultCode )
        );

        return $language;
      },
      $languages
    );
  }


  private function unavailableReason( bool $supported, string $code, string $defaultCode ) {
    if ( $supported ) {
      return null;
    }

    return LanguageMappings::resolvesToSameAteLanguageAsDefault( $code, $defaultCode )
      ? LanguageDto::AUTOMATIC_TRANSLATION_REASON_SAME_AS_DEFAULT
      : LanguageDto::AUTOMATIC_TRANSLATION_REASON_MANUAL_ONLY;
  }


  private function explicitlyMappedCodes(): array {
    $all = array_merge(
      (array) CachedLanguageMappings::get(),
      \WPML\Setup\Option::getLanguageMappings()
    );

    $codes = [];
    foreach ( $all as $mapping ) {
      $source = is_object( $mapping ) && isset( $mapping->sourceCode ) ? (string) $mapping->sourceCode : '';
      $target = is_object( $mapping ) && isset( $mapping->targetCode ) ? (string) $mapping->targetCode : '';
      if ( $source !== '' && $target !== '' ) {
        $codes[] = $source;
      }
    }

    return $codes;
  }


}
