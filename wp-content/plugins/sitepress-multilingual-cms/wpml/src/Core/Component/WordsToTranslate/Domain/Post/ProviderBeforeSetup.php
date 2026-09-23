<?php

namespace WPML\Core\Component\WordsToTranslate\Domain\Post;

use WPML\Core\Component\WordsToTranslate\Domain\Calculator\WordsToTranslate;
use WPML\Core\Component\WordsToTranslate\Domain\Post\Query\JobQueryInterface;
use WPML\Core\Component\WordsToTranslate\Domain\Post\Query\PostQueryInterface;
use WPML\Core\Component\WordsToTranslate\Domain\ProviderInterface;
use WPML\Core\Component\WordsToTranslate\Domain\TranslatableDTO;
use WPML\PHP\Exception\InvalidItemIdException;
use WPML\PHP\Exception\RuntimeException;

class ProviderBeforeSetup implements ProviderInterface{
  const TYPE = 'post';

  private $wordsToTranslate;

  private $postQuery;

  private $jobQuery;


  public function __construct(
    PostQueryInterface $postQuery,
    WordsToTranslate $wordsToTranslate,
    JobQueryInterface $jobQuery
  ) {
    $this->postQuery = $postQuery;
    $this->wordsToTranslate = $wordsToTranslate;
    $this->jobQuery = $jobQuery;
  }


  public function getByIdAndTypeForLangs( $id, $type, $langs, $freshTranslation = false ) {
    if ( $type !== self::TYPE ) {
      return false;
    }

    if ( empty( $langs ) ) {
      return false;
    }

    try {
      $post = $this->postQuery->getById( $id );

      $job = $this->jobQuery->getContentToTranslateForLang( $post, '' );

      $post->setPreWpmlSetupWordsToTranslate(
        $this->wordsToTranslate->forContent( [ $job->getContent() ], $langs[0] )
      );
    } catch ( \Throwable $e ) {
      return false;
    }

    return $post;
  }


  public function useThisContentForItem( $id, $type, $content ) {
  }


}
