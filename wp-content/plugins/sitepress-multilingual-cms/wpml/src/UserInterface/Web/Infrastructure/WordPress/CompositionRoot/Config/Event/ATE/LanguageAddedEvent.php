<?php

namespace WPML\UserInterface\Web\Infrastructure\WordPress\CompositionRoot\Config\Event\ATE;

use WPML\DicInterface;
use WPML\UserInterface\Web\Infrastructure\WordPress\Events\ATE\LanguageAddedEventListener;

class LanguageAddedEvent {

  const EVENT_NAME = 'wpml_update_active_languages';

  private $dic;

  private $isTmLoaded;

  private $listener;


  public function __construct( DicInterface $dic, ?callable $isTmLoaded = null ) {
    $this->dic        = $dic;
    $this->isTmLoaded = $isTmLoaded ?: [ TranslationManagementLoaded::class, 'forRequest' ];
    $this->register();
  }


  public function register() {
    add_action( self::EVENT_NAME, [ $this, 'onActiveLanguagesUpdated' ], 10, 1 );
  }


  /**
   * Both guards run before the getListener() below (which builds the listener
   * through the DIC), never inside doActions(): without tm.php (Blog license,
   * wpmldev-8391) and mid-setup that dependency graph cannot be
   * resolved, and the failure is the make() itself.
   *
   * @param array<string, mixed>|null $oldActiveLanguages
   *
   * @return void
   */
  public function onActiveLanguagesUpdated( $oldActiveLanguages = null ) {
    if ( ! call_user_func( $this->isTmLoaded ) ) {
      return;
    }
    if ( ! LanguageAddedEventListener::isSetupComplete() ) {
      return;
    }
    $this->getListener()->doActions( $oldActiveLanguages );
  }


  private function getListener(): LanguageAddedEventListener {
    if ( $this->listener === null ) {
      $this->listener = $this->dic->make( LanguageAddedEventListener::class );
    }

    return $this->listener;
  }


}
