<?php

namespace WPML\Troubleshooting\Tool;

use WPML\UserInterface\Web\Core\SharedKernel\Config\PageRenderInterface;
use WPML\UserInterface\Web\Infrastructure\WordPress\Component\Wcml\WcmlEmbed;

class WcmlFixTranslationsController implements PageRenderInterface {


  public function render() {
    ?>
      <h1 class="wpml:text-2xl wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
        <?php esc_html_e( 'Fix WCML translations', 'wpml-troubleshooting' ); ?>
      </h1>
      <p class="wpml:text-sm wpml:text-gray-500 wpml:mb-6">
        <?php
        esc_html_e( 'Bulk repair tools for WooCommerce Multilingual that fill in missing data on translations or register missing items for translation. Tick the boxes you want to run, then submit once.', 'wpml-troubleshooting' );
        ?>
      </p>

      <div class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5">
        <?php WcmlEmbed::embed( 'wcml-fix-translations' ); ?>
      </div>
      <?php
  }


}
