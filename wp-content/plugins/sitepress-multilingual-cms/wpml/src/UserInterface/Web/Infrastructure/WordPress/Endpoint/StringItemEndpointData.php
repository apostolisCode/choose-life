<?php

namespace WPML\UserInterface\Web\Infrastructure\WordPress\Endpoint;

class StringItemEndpointData {


  public function getEndpointData(): array {
    return [
      'url'   => $this->getRestUrl( '/wpml/st/v1/strings' ),
    ];
  }


  public function getStringPackagesEndpointData(): array {
    return [
      'url'   => $this->getRestUrl( '/wpml/st/v1/string-packages' ),
    ];
  }


  public function isStPluginActive(): bool {
    return class_exists( 'WPML_String_Translation' );
  }


  private function getRestUrl( string $path ): string {
    $restUrl = get_rest_url( null, $path );
    if ( get_option( 'permalink_structure' ) === '' ) {
        $home    = explode( '?', home_url( '/' ), 2 );
        $restUrl = add_query_arg( 'rest_route', '/' . ltrim( $path, '/' ), trailingslashit( $home[0] ) . ( isset( $home[1] ) ? '?' . $home[1] : '' ) );
    }
    return $restUrl;
  }


}
