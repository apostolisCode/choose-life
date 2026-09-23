<?php

namespace WPML\Core\Component\SettingsStorage\Application\Service;

class BlobDiffService {


  public function diff( array $base, array $incoming ): array {
    $changed = [];
    foreach ( $incoming as $key => $value ) {
      if (
        ! array_key_exists( $key, $base )
        || serialize( $base[ $key ] ) !== serialize( $value )
      ) {
        $changed[ $key ] = $value;
      }
    }

    $deleted = array_keys( array_diff_key( $base, $incoming ) );

    return [
      'changed' => $changed,
      'deleted' => $deleted,
    ];
  }


  public function apply( array $fresh, array $diff ): array {
    foreach ( $diff['changed'] as $key => $value ) {
      $fresh[ $key ] = $value;
    }
    foreach ( $diff['deleted'] as $key ) {
      unset( $fresh[ $key ] );
    }

    return $fresh;
  }


}
