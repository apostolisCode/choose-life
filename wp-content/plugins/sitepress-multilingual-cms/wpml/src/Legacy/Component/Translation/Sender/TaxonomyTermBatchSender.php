<?php

namespace WPML\Legacy\Component\Translation\Sender;

use WPML\Core\Component\Translation\Application\Query\TranslationQueryInterface;
use WPML\Core\Component\Translation\Domain\TranslationBatch\Element;
use WPML\Core\Component\Translation\Domain\TranslationBatch\TranslationBatch;
use WPML\Core\Component\Translation\Domain\TranslationBatch\Validator\IgnoredElement;
use WPML\Core\Component\Translation\Domain\TranslationType;

class TaxonomyTermBatchSender {

  private $wpdb;

  private $translationQuery;

  private $dispatcherFactory;


  public function __construct(
    \wpdb $wpdb,
    TranslationQueryInterface $translationQuery,
    $dispatcherFactory = null
  ) {
    $this->wpdb              = $wpdb;
    $this->translationQuery  = $translationQuery;
    $this->dispatcherFactory = $dispatcherFactory;
  }


  public function send( TranslationBatch $batch ): array {
    $jobs    = [];
    $jobIds  = [];
    $ignored = [];

    foreach ( $batch->getTargetLanguages() as $targetLanguage ) {
      foreach ( $targetLanguage->getElements() as $element ) {
        if ( ! $this->isTerm( $element ) ) {
          continue;
        }

        $taxonomy = $this->taxonomyOf( $element->getElementId() );

        if ( null === $taxonomy ) {
          $ignored[] = new IgnoredElement(
            $element->getType(),
            $element->getElementId(),
            $targetLanguage->getLanguageCode(),
            $targetLanguage->getMethod(),
            'The term no longer exists.'
          );
          continue;
        }

        $job = $this->dispatcher()->prepareTermJob(
          $element->getElementId(),
          $taxonomy,
          $element->getOriginalLanguageCode(),
          $targetLanguage->getLanguageCode(),
          true
        );

        if ( null === $job ) {
          $ignored[] = new IgnoredElement(
            $element->getType(),
            $element->getElementId(),
            $targetLanguage->getLanguageCode(),
            $targetLanguage->getMethod(),
            'The term could not be prepared for translation.'
          );
          continue;
        }

        $jobs[]   = $job;
        $jobIds[] = $job->id;
      }
    }

    if ( ! $jobs ) {
      return [ [], $ignored ];
    }

    $error    = null;
    $accepted = $this->dispatcher()->sendToAte( $jobs, $error );

    if ( $accepted < count( $jobs ) ) {
      $ignored[] = new IgnoredElement(
        TranslationType::taxonomy(),
        0,
        $batch->getSourceLanguageCode(),
        $batch->getTargetLanguages()[0]->getMethod(),
        rtrim(
          sprintf(
            'Only %d of %d term jobs were accepted by the translation service. %s',
            $accepted,
            count( $jobs ),
            (string) $error
          )
        )
      );
    }

    return [ $this->translationQuery->getManyByJobIds( $jobIds ), $ignored ];
  }


  private function isTerm( Element $element ): bool {
    return $element->getType()->get() === TranslationType::TAXONOMY;
  }


  private function taxonomyOf( int $termTaxonomyId ) {
    $wpdb = $this->wpdb;

    $sql = $wpdb->prepare(
      "SELECT taxonomy FROM {$wpdb->prefix}term_taxonomy WHERE term_taxonomy_id = %d",
      $termTaxonomyId
    );

    if ( ! is_string( $sql ) ) {
      return null;
    }

    $taxonomy = $wpdb->get_var( $sql );

    return is_string( $taxonomy ) && '' !== $taxonomy ? $taxonomy : null;
  }


  private function dispatcher() {
    if ( null !== $this->dispatcherFactory ) {
      return call_user_func( $this->dispatcherFactory );
    }

    return new \WPML\TM\ATE\TranslateEverything\TaxonomyTermJobDispatcher( $this->wpdb );
  }

}
