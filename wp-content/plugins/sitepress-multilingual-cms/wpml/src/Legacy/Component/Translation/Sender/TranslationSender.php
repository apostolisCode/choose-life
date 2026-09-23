<?php

namespace WPML\Legacy\Component\Translation\Sender;

use WPML\Core\Component\Translation\Application\Query\TranslationQueryInterface;
use WPML\Core\Component\Translation\Application\Service\Dto\SendToTranslationExtraInformationDto;
use WPML\Core\Component\Translation\Domain\Sender\SendBatchException;
use WPML\Core\Component\Translation\Domain\Sender\TranslationSenderInterface;
use WPML\Core\Component\Translation\Domain\Translation;
use WPML\Core\Component\Translation\Domain\TranslationBatch\TargetLanguage;
use WPML\Core\Component\Translation\Domain\TranslationBatch\TranslationBatch;
use WPML\Core\Component\Translation\Domain\TranslationType;
use WPML\Core\Component\Translation\Domain\TranslationMethod\TranslationServiceMethod;
use WPML\Legacy\Component\Translation\Sender\ErrorMapper\ErrorMapper;

class TranslationSender implements TranslationSenderInterface {

  const SEND_VIA_DASHBOARD = 6;

  private $legacyTranslationManagement;

  private $translationBatchMapper;

  private $translationQuery;

  private $errorMapper;


  private $ignoredElements = [];

  private $taxonomyTermBatchSender = null;


  public function __construct(
    TranslationBatchMapper $translationBatchMapper,
    TranslationQueryInterface $translationQuery,
    ErrorMapper $errorMapper
  ) {
    $this->legacyTranslationManagement = \wpml_load_core_tm();
    $this->translationBatchMapper      = $translationBatchMapper;
    $this->translationQuery            = $translationQuery;
    $this->errorMapper                 = $errorMapper;
  }


  public function getFailedElements(): array {
    if ( method_exists( $this->legacyTranslationManagement, 'get_failed_send_elements' ) ) {
      return (array) $this->legacyTranslationManagement->get_failed_send_elements();
    }

    return [];
  }


  public function wasTruncated(): bool {
    if ( method_exists( $this->legacyTranslationManagement, 'send_was_truncated' ) ) {
      return (bool) $this->legacyTranslationManagement->send_was_truncated();
    }

    return false;
  }


  public function send( TranslationBatch $batch, bool $mayTruncate = false ): array {

    $termTranslations = [];

    if ( $this->hasTaxonomyElements( $batch ) ) {
      list( $termTranslations, $this->ignoredElements ) = $this->taxonomyTermBatchSender()->send( $batch );

      $batch = $this->withoutTaxonomyElements( $batch );

      if ( ! $this->hasAnyElement( $batch ) ) {
        return $termTranslations;
      }
    }

    $this->setTargetLanguagesInTranslationProxy( $batch );

    $translationProxyBatchInfo = null;

    $batchHasJobsForTranslationProxy = $this->getTargetLanguagesForTranslationProxy( $batch );

    if ( $batchHasJobsForTranslationProxy ) {
      $translationProxyBatchInfo = [
        'batchName'   => $batch->getBatchName(),
        'deadline'    => $batch->getDeadline(),
        'extraFields' => $batch->getTranslationServiceExtraFields()
      ];
    }

    $legacyBatches = $this->translationBatchMapper->map( $batch, $translationProxyBatchInfo );

    $jobIds = [];

    if ( $mayTruncate
         && $batchHasJobsForTranslationProxy
         && method_exists( $this->legacyTranslationManagement, 'start_send_budget' ) ) {
      $this->legacyTranslationManagement->start_send_budget();
    }

    foreach ( $legacyBatches as $legacyBatch ) {
      foreach ( $this->getElementTypes() as $type ) {
        do_action(
          'wpml_tm_send_' . $type . '_jobs',
          $legacyBatch,
          $type,
          self::SEND_VIA_DASHBOARD
        );
      }

      $jobIdsOfLegacy = $this->legacyTranslationManagement->get_sent_job_ids();
      if ( is_array( $jobIdsOfLegacy ) ) {
        $jobIds = array_merge( $jobIds, $jobIdsOfLegacy );
      }

      $errors = $this->legacyTranslationManagement->messages_by_type( 'error' );
      if ( is_array( $errors ) ) {
        $errorMessage = $this->errorMapper->map( $errors );
        $translations = $jobIds ? $this->translationQuery->getManyByJobIds( $jobIds ) : [];

        throw new SendBatchException( $errorMessage, $translations );
      }

      do_action( 'wpml_tm_jobs_notification' );
    }

    if ( $jobIds ) {
      return array_merge( $termTranslations, $this->translationQuery->getManyByJobIds( $jobIds ) );
    }

    return $termTranslations;
  }


  public function getIgnoredElements(): array {
    return $this->ignoredElements;
  }


  private function taxonomyTermBatchSender(): TaxonomyTermBatchSender {
    if ( null === $this->taxonomyTermBatchSender ) {
      $this->taxonomyTermBatchSender = new TaxonomyTermBatchSender(
        $GLOBALS['wpdb'],
        $this->translationQuery
      );
    }

    return $this->taxonomyTermBatchSender;
  }


  private function getTargetLanguagesForTranslationProxy( TranslationBatch $batch ): array {
    $targetLanguages = [];

    foreach ( $batch->getTargetLanguages() as $targetLanguage ) {
      if ( $targetLanguage->getMethod() instanceof TranslationServiceMethod ) {
        $targetLanguages[] = $targetLanguage->getLanguageCode();
      }
    }

    return array_unique( $targetLanguages );
  }


  private function setTargetLanguagesInTranslationProxy( TranslationBatch $batch ) {
    $targetLanguages = $this->getTargetLanguagesForTranslationProxy( $batch );
    if ( $targetLanguages ) {
      \WPML\TM\TranslationProxy\TpBatchState::setRemoteTargetLanguages( $targetLanguages );
    }

  }


  private function getElementTypes(): array {
    $types = \apply_filters(
      'wpml_tm_basket_items_types',
      [
        'st-batch' => 'core',
        'post'     => 'core',
        'package'  => 'custom',
      ]
    );

    return array_keys( $types );
  }


  private function withoutTaxonomyElements( TranslationBatch $batch ): TranslationBatch {
    $targetLanguages = [];

    foreach ( $batch->getTargetLanguages() as $targetLanguage ) {
      $elements = array_values(
        array_filter(
          $targetLanguage->getElements(),
          function ( $element ) {
            return $element->getType()->get() !== TranslationType::TAXONOMY;
          }
        )
      );

      if ( $elements ) {
        $targetLanguages[] = new TargetLanguage(
          $targetLanguage->getLanguageCode(),
          $targetLanguage->getMethod(),
          $elements
        );
      }
    }

    return $batch->copyWithNewTargetLanguages( $targetLanguages );
  }


  private function hasTaxonomyElements( TranslationBatch $batch ): bool {
    foreach ( $batch->getTargetLanguages() as $targetLanguage ) {
      foreach ( $targetLanguage->getElements() as $element ) {
        if ( $element->getType()->get() === TranslationType::TAXONOMY ) {
          return true;
        }
      }
    }

    return false;
  }


  private function hasAnyElement( TranslationBatch $batch ): bool {
    foreach ( $batch->getTargetLanguages() as $targetLanguage ) {
      if ( $targetLanguage->getElements() ) {
        return true;
      }
    }

    return false;
  }


  public function cleanupAfterFailedSend( TranslationBatch $batch ) {
    if ( ! $this->getTargetLanguagesForTranslationProxy( $batch ) ) {
      return;
    }

    \WPML\TM\TranslationProxy\TpBatchState::setBatchData( null );
    icl_cache_clear_preserving_language_names();
  }


}
