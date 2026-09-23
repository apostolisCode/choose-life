<?php

namespace WPML\Core\Component\Translation\Application\Service;

use WPML\Core\Component\Translation\Application\Query\TranslationQueryInterface;
use WPML\Core\Component\Translation\Application\Query\UnsolvableJobsQueryInterface;
use WPML\Core\Component\Translation\Application\Service\ResendUnsolvableJobs\SendToTranslationDtoCollectionBuilder;
use WPML\Core\Component\Translation\Application\Service\ResendUnsolvableJobs\TimestampBatchNameGenerator;
use WPML\Core\Component\Translation\Application\Service\TranslationService\TranslationServiceException;
use WPML\PHP\Exception\Exception;
use WPML\PHP\Exception\InvalidArgumentException;
use WPML\PHP\Exception\InvalidItemIdException;

class ResendUnsolvableJobsService {

  private $translationQuery;

  private $cancelJobsService;

  private $translationService;

  private $dtoCollectionBuilder;

  private $batchNameGenerator;

  private $unsolvableJobsQuery;


  public function __construct(
    TranslationQueryInterface $translationQuery,
    CancelJobsService $cancelJobsService,
    TranslationService $translationService,
    SendToTranslationDtoCollectionBuilder $dtoCollectionBuilder,
    TimestampBatchNameGenerator $batchNameGenerator,
    UnsolvableJobsQueryInterface $unsolvableJobsQuery
  ) {
    $this->translationQuery     = $translationQuery;
    $this->cancelJobsService    = $cancelJobsService;
    $this->translationService   = $translationService;
    $this->dtoCollectionBuilder = $dtoCollectionBuilder;
    $this->batchNameGenerator   = $batchNameGenerator;
    $this->unsolvableJobsQuery  = $unsolvableJobsQuery;
  }


  private function keepResendableJobIds( array $jobIds ): array {
    $resendable = [];
    foreach ( $this->unsolvableJobsQuery->getUnsolvableJobs() as $job ) {
      if ( ! empty( $job['canResend'] ) ) {
        $resendable[] = $job['jobId'];
      }
    }

    return array_values( array_intersect( array_map( 'intval', $jobIds ), $resendable ) );
  }


  public function resend( array $jobIds, $batchName = null ): array {
    $requestedCount = count( $jobIds );
    $jobIds         = $this->keepResendableJobIds( $jobIds );

    if ( empty( $jobIds ) ) {
      throw new InvalidArgumentException(
        $requestedCount > 0
          ? 'None of the requested jobs can be resent. The list they came from is out of date; reload it to see which jobs can still be recovered.'
          : 'No translations found for the provided job IDs.'
      );
    }

    $translations = $this->translationQuery->getManyByJobIds( $jobIds );
    if ( empty( $translations ) ) {
      throw new InvalidArgumentException( 'No translations found for the provided job IDs.' );
    }

    $batchName = $batchName ?: $this->batchNameGenerator->generate();
    $dtos      = $this->dtoCollectionBuilder->buildCollection( $batchName, $translations );

    $this->cancelJobsService->cancelJobs( $jobIds );

    $results = [];
    foreach ( $dtos as $sourceLanguage => $dto ) {
      $result                     = $this->translationService->send( $dto );
      $results[ $sourceLanguage ] = $result->toArray();
    }

    return [
      'batchName' => $batchName,
      'results'   => $results,
    ];
  }


}
