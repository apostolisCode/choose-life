<?php

namespace WPML\Core\Component\Translation\Domain\Sender;

use WPML\Core\Component\Translation\Domain\Translation;
use WPML\PHP\Exception\Exception;

class SendBatchException extends Exception {

  private $createdTranslations;


  public function __construct(
    string $message = '',
    array $createdTranslations = [],
    int $code = 0
  ) {
    parent::__construct( $message, $code );

    $this->createdTranslations = $createdTranslations;
  }


  public function getCreatedTranslations(): array {
    return $this->createdTranslations;
  }

}
