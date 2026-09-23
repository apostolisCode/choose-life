<?php

namespace WPML\Core\Component\WordsToTranslate\Domain\Post;

use WPML\Core\Component\WordsToTranslate\Domain\Item;
use WPML\Core\Component\WordsToTranslate\Domain\Post\Term\Term;

class Post extends Item {

  private $lastEdit;

  private $terms;

  private $preWpmlSetupWordsToTranslate;


  public function __construct(
    int $id,
    string $type,
    string $sourceLang,
    int $lastEdit
  ) {
    parent::__construct( $id, $type, $sourceLang );
    $this->lastEdit = $lastEdit;
  }


  public function getLastEdit() {
    return $this->lastEdit;
  }


  public function setTerms( $terms ) {
    $this->terms = $terms;
  }


  public function getTerms() {
    return $this->terms;
  }


  public function setPreWpmlSetupWordsToTranslate( int $words ): void {
    $this->preWpmlSetupWordsToTranslate = $words;
  }


  public function getContentCount() {
    if ( $this->preWpmlSetupWordsToTranslate !== null ) {
      return $this->preWpmlSetupWordsToTranslate;
    }

    return parent::getContentCount();
  }


  public function getWordsToTranslate( $langCode = null ) {
    if ( $this->preWpmlSetupWordsToTranslate !== null ) {
      return $this->preWpmlSetupWordsToTranslate;
    }

    $wordsToTranslate = 0;

    if ( ! $langCode ) {
      foreach ( $this->lastTranslations as $lastTranslation ) {
        $wordsToTranslate += $lastTranslation->getWordsToTranslate() ?? 0;
      }
    } elseif ( isset( $this->lastTranslations[ $langCode ] ) ) {
      $wordsToTranslate = $this->lastTranslations[ $langCode ]->getWordsToTranslate() ?? 0;
    }

    if ( ! $this->terms ) {
      return $wordsToTranslate;
    }

    foreach ( $this->terms as $term ) {
      foreach ( $term->getContents() as $content ) {
        $wordsToTranslate += $content->getWordsToTranslate( $langCode );
      }
    }

    return $wordsToTranslate;
  }


}
