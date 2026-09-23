<?php

namespace WPML\UserInterface\Web\Infrastructure\WordPress\CompositionRoot\Config\Event\ATE;

use WPML\Core\SharedKernel\Component\ATE\Application\Service\AtePingerInterface;
use WPML\DicInterface;
use WPML\UserInterface\Web\Infrastructure\WordPress\Events\ATE\TeaRecheckScopeEventListener;

class TeaRecheckScopeEvent {

  const EVENT_NAME = 'wpml_tea_recheck_scope';

  private $dic;

  private $isTmLoaded;

  private $listener;


  public function __construct( DicInterface $dic, ?callable $isTmLoaded = null ) {
    $this->dic        = $dic;
    $this->isTmLoaded = $isTmLoaded ?: [ TranslationManagementLoaded::class, 'forRequest' ];
    $this->register();
  }


  public function register() {
    add_action( self::EVENT_NAME, [ $this, 'onTeaRecheckScope' ], 10, 1 );
  }


  public function onTeaRecheckScope( string $trigger = AtePingerInterface::TRIGGER_TRANSLATABLE_SCOPE_RECHECK ) {
    if ( ! call_user_func( $this->isTmLoaded ) ) {
      return;
    }
    $this->getListener()->doActions( $trigger );
  }


  private function getListener(): TeaRecheckScopeEventListener {
    if ( $this->listener === null ) {
      $this->listener = $this->dic->make( TeaRecheckScopeEventListener::class );
    }

    return $this->listener;
  }


}
