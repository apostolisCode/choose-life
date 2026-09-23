<?php

namespace WPML\Infrastructure\WordPress\Component\ATE\Application\Repository;

use WPML\Core\SharedKernel\Component\ATE\Application\Repository\SpendCapRepositoryInterface;
use WPML\Infrastructure\WordPress\Port\Persistence\Options;

class SpendCapRepository implements SpendCapRepositoryInterface {

  const OPTION_KEY = 'wpml_ate_spend_cap';

  private $options;


  public function __construct( Options $options ) {
    $this->options = $options;
  }


  public function get(): ?array {
    $value = $this->options->get( self::OPTION_KEY, null );

    if ( ! is_array( $value ) || count( $value ) === 0 ) {
      return null;
    }

    return [
      'capWords'   => (int) ( $value['capWords'] ?? 0 ),
      'usedWords'  => (int) ( $value['usedWords'] ?? 0 ),
      'resetsOn'   => (string) ( $value['resetsOn'] ?? '' ),
      'reached'    => ! empty( $value['reached'] ),
      'detectedAt' => isset( $value['detectedAt'] ) && is_numeric( $value['detectedAt'] )
        ? (int) $value['detectedAt']
        : null,
    ];
  }


}
