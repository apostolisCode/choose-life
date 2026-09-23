<?php

namespace WPML\UserInterface\Web\Infrastructure\WordPress\CompositionRoot\Config\Event\TeaUpgrade;

use WPML\DicInterface;
use WPML\UserInterface\Web\Infrastructure\WordPress\Component\Notices\TeaUpgrade\TeaUpgradeCompactNoticeRenderer;

class DisplayedTrackingEvent {


  public function __construct( DicInterface $dic ) {
    $renderer = $dic->make( TeaUpgradeCompactNoticeRenderer::class );
    $renderer->registerWpDashboardHook();
  }


}
