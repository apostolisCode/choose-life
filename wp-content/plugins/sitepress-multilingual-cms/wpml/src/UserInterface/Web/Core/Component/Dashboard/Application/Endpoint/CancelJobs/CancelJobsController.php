<?php

namespace WPML\UserInterface\Web\Core\Component\Dashboard\Application\Endpoint\CancelJobs;

use WPML\Core\Component\Translation\Application\Service\AutomaticJobsCancellation\ReleaseLedger;
use WPML\Core\Component\Translation\Application\Service\CancelJobsService;
use WPML\Core\Port\Endpoint\EndpointInterface;
use WPML\PHP\Exception\InvalidArgumentException;

class CancelJobsController implements EndpointInterface {

    private $cancelJobsService;


  public function __construct( CancelJobsService $cancelJobsService ) {
      $this->cancelJobsService = $cancelJobsService;
  }


  public function handle( $requestData = null ): array {
      $requestData = $requestData ?: [];

      $jobIds = $requestData['jobIds'] ?? null;

    if ( $jobIds === null ) {
        throw new InvalidArgumentException( 'jobIds array is required.' );
    }

    if ( ! is_array( $jobIds ) ) {
        throw new InvalidArgumentException( 'jobIds must be an array.' );
    }

      $releaseMark = ReleaseLedger::instance()->mark();

      $result = $this->cancelJobsService->cancelJobs( $jobIds );

      $result['release'] = ReleaseLedger::instance()->summaryFrom( $releaseMark )->toArray();

      return [
          'success' => true,
          'data'    => $result,
      ];
  }


}
