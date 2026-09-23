<?php

namespace WPML\Core\Component\Translation\Application\Service;

use WPML\Core\Component\Translation\Application\Service\AutomaticJobsCancellation\BatchResult;
use WPML\Core\Component\Translation\Application\Service\Dto\SendToTranslationDto;
use WPML\Core\Component\Translation\Application\Service\Event\CancelAllAutomaticJobsEvent;
use WPML\Core\Component\Translation\Application\Service\Event\TranslationsSentEvent;
use WPML\Core\Component\Translation\Application\Service\TranslationService\BatchBuilder\BatchBuilderInterface;
use WPML\Core\Component\Translation\Application\Service\TranslationService\Dto\ResultDto;
use WPML\Core\Component\Translation\Application\Service\TranslationService\ResultBuilder;
use WPML\Core\Component\Translation\Application\Service\TranslationService\TranslationServiceException;
use WPML\Core\Component\Translation\Application\String\StringBatchToStringsTranslationsMapper;
use WPML\Core\Component\Translation\Domain\Sender\DuplicationSenderInterface;
use WPML\Core\Component\Translation\Domain\Sender\SendBatchException;
use WPML\Core\Component\Translation\Domain\Sender\TranslationSenderInterface;
use WPML\Core\Component\Translation\Domain\Translation;
use WPML\Core\Component\Translation\Domain\TranslationBatch\DuplicationBatch;
use WPML\Core\Component\Translation\Domain\TranslationBatch\TranslationBatch;
use WPML\Core\Port\Event\DispatcherInterface;
use WPML\PHP\Exception\InvalidArgumentException;
use WPML\PHP\Exception\RuntimeException;


class TranslationService {

  private $batchBuilder;

  private $translationSender;

  private $duplicationSender;

  private $stringBatchToStringsTranslationsMapper;

  private $resultBuilder;

  private $eventDispatcher;


  public function __construct(
    BatchBuilderInterface $batchBuilder,
    TranslationSenderInterface $translationSender,
    DuplicationSenderInterface $duplicationSender,
    StringBatchToStringsTranslationsMapper $stringBatchToStringsTranslationsMapper,
    ResultBuilder $resultBuilder,
    DispatcherInterface $eventDispatcher
  ) {
    $this->batchBuilder                           = $batchBuilder;
    $this->translationSender                      = $translationSender;
    $this->duplicationSender                      = $duplicationSender;
    $this->stringBatchToStringsTranslationsMapper = $stringBatchToStringsTranslationsMapper;
    $this->resultBuilder                          = $resultBuilder;
    $this->eventDispatcher                        = $eventDispatcher;
  }


  public function send(
    SendToTranslationDto $sendToTranslationDto,
    bool $mayTruncate = false
  ): ResultDto {
    list( $translationBatch, $duplicationBatch, $ignoredElements ) =
      $this->batchBuilder->build( $sendToTranslationDto );

    $duplicatedTranslations = $this->byDuplicate( $duplicationBatch );

    try {
      $regularTranslations = $this->byTranslation( $translationBatch, $mayTruncate );
    } catch ( SendBatchException $e ) {
      if ( $translationBatch ) {
        $this->translationSender->cleanupAfterFailedSend( $translationBatch );
      }

      $createdTranslations = array_merge(
        $duplicatedTranslations,
        $this->stringBatchToStringsTranslationsMapper->map( $e->getCreatedTranslations() )
      );
      $partialResult       = $this->resultBuilder->build( $createdTranslations, $ignoredElements );

      throw new TranslationServiceException(
        $e->getMessage(),
        $partialResult,
        $e->getCode()
      );
    }

    $translations = array_merge( $duplicatedTranslations, $regularTranslations );

    $ignoredElements = array_merge( $ignoredElements, $this->translationSender->getIgnoredElements() );

    $completed = ! $this->translationSender->wasTruncated();

    $result = $this->resultBuilder->build(
      $translations,
      $ignoredElements,
      $completed,
      $this->translationSender->getFailedElements()
    );

    if ( $completed ) {
      $this->eventDispatcher->dispatch( new TranslationsSentEvent( $result ) );
    }

    return $result;
  }


  public function cancelAllAutomaticJobs(): BatchResult {
    $result = new BatchResult();
    $this->eventDispatcher->dispatch( new CancelAllAutomaticJobsEvent( $result ) );

    if ( $result->hasFailures() ) {
      throw new RuntimeException(
        'Some automatic translation jobs could not be cancelled in ATE. Please retry.'
      );
    }

    return $result;
  }


  private function byDuplicate( ?DuplicationBatch $duplicationBatch = null ): array {
    if ( $duplicationBatch ) {
      return $this->duplicationSender->send( $duplicationBatch );
    }

    return [];
  }


  private function byTranslation(
    ?TranslationBatch $translationBatch = null,
    bool $mayTruncate = false
  ): array {
    if ( $translationBatch ) {
      $translations = $this->translationSender->send( $translationBatch, $mayTruncate );

      return $this->stringBatchToStringsTranslationsMapper->map( $translations );
    }

    return [];
  }


}
