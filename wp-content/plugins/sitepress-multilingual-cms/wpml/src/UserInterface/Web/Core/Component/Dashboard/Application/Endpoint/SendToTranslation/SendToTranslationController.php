<?php

namespace WPML\UserInterface\Web\Core\Component\Dashboard\Application\Endpoint\SendToTranslation;

use WPML\Core\Component\Translation\Application\Service\AutomaticJobsCancellation\ReleaseLedger;
use WPML\Core\Component\Translation\Application\Service\Dto\SendToTranslationDto;
use WPML\Core\Component\Translation\Application\Service\TranslationService;
use WPML\Core\Port\Endpoint\EndpointInterface;
use WPML\Legacy\Component\TranslationProxy\Application\Service\SendLock;
use WPML\PHP\Exception\Exception;
use WPML\PHP\Exception\InvalidArgumentException;

class SendToTranslationController implements EndpointInterface {

  private $translationService;

  private $sendLock;


  public function __construct( TranslationService $translationService, SendLock $sendLock ) {
    $this->translationService = $translationService;
    $this->sendLock           = $sendLock;
  }


  public function handle( $requestData = null ): array {
    $requestData = $requestData ?: [];

    if ( ! $this->sendLock->acquire() ) {
      \WPML\TM\Jobs\JobLog::maybeInitRequest();
      \WPML\TM\Jobs\JobLog::createNewGroup( \WPML\TM\Jobs\JobLog::GROUP_ID_SEND_JOBS, 'TP send refused: lock busy' );
      \WPML\TM\Jobs\JobLog::addError( 'tp_send_lock_busy', [] );
      \WPML\TM\Jobs\JobLog::finishCurrentGroup();

      return [
        'success' => false,
        'data'    => __( 'Another translation send is already in progress. Please try again in a moment.', 'wpml' ),
      ];
    }

    try {
      $sendToTranslationDto = SendToTranslationDto::fromArray( $requestData );

      $releaseMark = ReleaseLedger::instance()->mark();

      $result = $this->translationService->send( $sendToTranslationDto, true );

      return [
        'success' => true,
        'data'    => $result->toArray(),
        'release' => ReleaseLedger::instance()->summaryFrom( $releaseMark )->toArray(),
      ];
    } catch ( InvalidArgumentException $e ) {
      return [
        'success' => false,
        'data' => sprintf(
          /* translators: %s is the validation error returned for the request. */
          __( 'The request data for SendToTranslation is not valid: %s', 'wpml' ),
          $e->getMessage()
        )
      ];
    } catch ( TranslationService\TranslationServiceException $e ) {
      $response = [
        'success' => false,
        'data'    => 'TranslationServiceException: ' . $e->getMessage()
      ];

      $partialResult = $e->getPartialResult();
      if ( $partialResult ) {
        $response['partialData'] = $partialResult->toArray();
      }

      return $response;
    } catch ( Exception $e ) {
      return [
        'success' => false,
        'data'    => $e->getMessage()
      ];
    } finally {
      $this->sendLock->release();
    }
  }


}
