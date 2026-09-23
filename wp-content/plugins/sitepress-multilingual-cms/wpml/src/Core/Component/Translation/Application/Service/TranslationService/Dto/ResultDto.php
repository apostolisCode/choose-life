<?php

namespace WPML\Core\Component\Translation\Application\Service\TranslationService\Dto;

class ResultDto {

  private $createdTranslations;

  private $ignoredElements;

  private $completed;

  private $failedElements;


  public function __construct(
    array $createdTranslations,
    array $ignoredElements,
    bool $completed = true,
    array $failedElements = []
  ) {
    $this->createdTranslations = $createdTranslations;
    $this->ignoredElements     = $ignoredElements;
    $this->completed           = $completed;
    $this->failedElements      = $failedElements;
  }


  public function getFailedElements(): array {
    return $this->failedElements;
  }


  public function isCompleted(): bool {
    return $this->completed;
  }


  public function getCreatedTranslations(): array {
    return $this->createdTranslations;
  }


  public function getIgnoredElements(): array {
    return $this->ignoredElements;
  }


  public function toArray(): array {
    return [
      'createdTranslations' => array_map(
        function ( CreatedTranslationDto $createdTranslationDto ) {
          return $createdTranslationDto->toArray();
        },
        $this->createdTranslations
      ),
      'ignoredElements'     => array_map(
        function ( IgnoredElementDto $ignoredElementDto ) {
          return $ignoredElementDto->toArray();
        },
        $this->ignoredElements
      ),
      'completed'           => $this->completed,
      'failedElements'      => $this->failedElements,
    ];
  }


}
