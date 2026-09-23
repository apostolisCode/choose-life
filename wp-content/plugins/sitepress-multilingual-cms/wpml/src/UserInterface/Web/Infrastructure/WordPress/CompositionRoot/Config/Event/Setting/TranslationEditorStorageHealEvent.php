<?php

namespace WPML\UserInterface\Web\Infrastructure\WordPress\CompositionRoot\Config\Event\Setting;

use WPML\DicInterface;
use WPML\Infrastructure\WordPress\Component\Setting\Application\Service\TranslationEditorStorageHeal;

class TranslationEditorStorageHealEvent {

  const HOOK     = 'init';
  const PRIORITY = 0;

  private $dic;


  public function __construct( DicInterface $dic ) {
    $this->dic = $dic;
    add_action( self::HOOK, [ $this, 'onInit' ], self::PRIORITY );
  }


  public function onInit() {
    $heal = $this->dic->make( TranslationEditorStorageHeal::class );
    $heal->run();
  }


}
