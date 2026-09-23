<?php

namespace WPML\Core\Component\SettingsStorage\Application\Service;

use WPML\Core\Component\SettingsStorage\Application\Repository\StorageQueryInterface;
use WPML\Core\Port\Persistence\OptionsInterface;

class VirtualOptionService {

  private $options;

  private $query;

  private $diff;

  private $envelope;

  private $servedSnapshot = [];

  private $assembled = [];

  private $registryKeys = [];


  public function __construct(
    OptionsInterface $options,
    StorageQueryInterface $query,
    BlobDiffService $diff,
    RowEnvelope $envelope
  ) {
    $this->options  = $options;
    $this->query    = $query;
    $this->diff     = $diff;
    $this->envelope = $envelope;
  }


  public function read( $storedValue, int $context ) {
    $keys = $this->registryKeys( $context );
    if ( $keys === null ) {
      return $storedValue;
    }

    if ( ! isset( $this->assembled[ $context ] ) ) {
      $this->assembled[ $context ] = $this->assemble( $keys );
    }

    return $this->assembled[ $context ];
  }


  public function recordSnapshot( $value, int $context ) {
    if ( ! isset( $this->servedSnapshot[ $context ] )
      && is_array( $value )
      && $this->registryKeys( $context ) !== null
    ) {
      $detached                         = unserialize( serialize( $value ) );
      $this->servedSnapshot[ $context ] = $detached;
    }

    return $value;
  }


  public function write( $incoming, int $context, $oldValue = null ) {
    $keys = $this->registryKeys( $context );
    if ( $keys === null ) {
      return $incoming;
    }

    if ( ! is_array( $incoming ) ) {
      return $incoming;
    }

    $base = $this->servedSnapshot[ $context ] ?? $this->freshAssembly( $context );

    $diff = $this->diff->diff( $base, $incoming );
    $noop = $diff['changed'] === [] && $diff['deleted'] === [];

    if ( ! $noop && is_array( $oldValue ) ) {
      $assembly     = $this->assembled[ $context ] ?? $this->freshAssembly( $context );
      $assemblyDiff = $this->diff->diff( $assembly, $incoming );
      $noop         = $assemblyDiff['changed'] === [] && $assemblyDiff['deleted'] === [];
    }

    if ( $noop ) {
      if ( is_array( $oldValue ) ) {
        return $oldValue;
      }

      return $this->assembled[ $context ] ?? $this->freshAssembly( $context );
    }

    $scanned = $this->query->scanRowKeys();
    if ( $scanned !== [] ) {
      $this->registryKeys[ $context ] = $scanned;
      $keys                           = $scanned;
    }

    $newState = $this->diff->apply( $this->freshAssembly( $context ), $diff );

    foreach ( $diff['changed'] as $key => $keyValue ) {
      $this->writeRow( $key, $keyValue );
    }
    foreach ( $diff['deleted'] as $key ) {
      $this->options->delete( StorageQueryInterface::ROW_PREFIX . $key );
    }

    $newKeys = array_keys( $newState );
    if ( $newKeys !== $keys ) {
      $this->storeRegistry( $newKeys );
    }
    $this->registryKeys[ $context ] = $newKeys;
    $this->assembled[ $context ]    = $newState;

    return $newState;
  }


  public function writeRow( string $key, $value ) {
    $this->options->save( StorageQueryInterface::ROW_PREFIX . $key, $this->envelope->wrap( $value ), true );
  }


  public function delete( int $context ) {
    $keys = $this->registryKeys( $context );
    foreach ( (array) $keys as $key ) {
      $this->options->delete( StorageQueryInterface::ROW_PREFIX . $key );
    }
    foreach ( $this->query->scanRowKeys() as $key ) {
      $this->options->delete( StorageQueryInterface::ROW_PREFIX . $key );
    }
    $this->options->delete( StorageQueryInterface::REGISTRY_ROW );
    $this->reset();
  }


  public function registryVanishedUnderneath( int $context ): bool {
    if ( ( $this->registryKeys[ $context ] ?? null ) === null ) {
      return false;
    }

    if ( $this->query->registryRowExistsInDatabase() ) {
      return false;
    }

    unset(
      $this->servedSnapshot[ $context ],
      $this->assembled[ $context ],
      $this->registryKeys[ $context ]
    );

    return true;
  }


  public function assembleFromRowsRaw(): ?array {
    $keys = $this->registryKeysFrom( $this->options->get( StorageQueryInterface::REGISTRY_ROW ) );
    if ( $keys === null ) {
      return null;
    }

    $state = [];
    foreach ( $keys as $key ) {
      $row = $this->options->get( StorageQueryInterface::ROW_PREFIX . $key );
      if ( is_array( $row ) && $this->envelope->isValidRow( $row ) ) {
        $state[ $key ] = $this->envelope->value( $row );
      }
    }

    return $state;
  }


  public function registryKeysFrom( $stored ): ?array {
    if ( ! is_array( $stored ) ) {
      return null;
    }
    $keys = $stored['keys'] ?? null;

    return is_array( $keys ) ? array_map( 'strval', array_values( $keys ) ) : null;
  }


  public function storeRegistry( array $keys, ?string $version = null ) {
    if ( $version === null ) {
      $stored  = $this->options->get( StorageQueryInterface::REGISTRY_ROW );
      $version = is_array( $stored ) && isset( $stored['version'] )
        ? (string) $stored['version']
        : ( defined( 'ICL_SITEPRESS_VERSION' ) ? ICL_SITEPRESS_VERSION : '' );
    }
    $this->options->save(
      StorageQueryInterface::REGISTRY_ROW,
      [ 'keys' => $keys, 'version' => $version ],
      true
    );
  }


  public function reset() {
    $this->servedSnapshot = [];
    $this->assembled      = [];
    $this->registryKeys   = [];
  }


  public function refreshFromPersistence() {
    $this->assembled    = [];
    $this->registryKeys = [];
  }


  private function registryKeys( int $context ): ?array {
    if ( array_key_exists( $context, $this->registryKeys ) ) {
      return $this->registryKeys[ $context ];
    }

    $stored = $this->options->get( StorageQueryInterface::REGISTRY_ROW );
    $keys   = $this->registryKeysFrom( $stored );
    if ( $keys !== null ) {
      return $this->registryKeys[ $context ] = $keys;
    }

    if ( $stored === false && ! $this->query->optionRowExists( StorageQueryInterface::REGISTRY_ROW ) ) {
      return $this->registryKeys[ $context ] = null;
    }

    $scanned = $this->query->scanRowKeys();

    return $this->registryKeys[ $context ] = ( $scanned === [] ? null : $scanned );
  }


  private function assemble( array $keys ): array {
    $state = [];
    foreach ( $keys as $key ) {
      $row = $this->options->get( StorageQueryInterface::ROW_PREFIX . $key );
      if ( is_array( $row ) && $this->envelope->isValidRow( $row ) ) {
        $state[ $key ] = $this->envelope->value( $row );
      }
    }

    return $state;
  }


  private function freshAssembly( int $context ): array {
    $keys = $this->registryKeys( $context );

    return $this->assemble( (array) $keys );
  }


}
