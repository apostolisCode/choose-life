<?php

namespace WPML\Troubleshooting\Tool;

use WPML\UserInterface\Web\Core\SharedKernel\Config\PageRenderInterface;
use WPML\UserInterface\Web\Infrastructure\WordPress\Component\Wcml\WcmlEmbed;

class WcmlTranslationMaintenanceController implements PageRenderInterface {


  public function render() {
    ?>
      <h1 class="wpml:text-2xl wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
        <?php esc_html_e( 'WCML translation maintenance', 'wpml-troubleshooting' ); ?>
      </h1>
      <p class="wpml:text-sm wpml:text-gray-500 wpml:mb-6">
        <?php
        esc_html_e( 'Advanced repair tools for the WCML translation relationships. Only run these when WPML Support has asked you to — they rewrite the variation-translation link tables and delete unused custom fields from product translations.', 'wpml-troubleshooting' );
        ?>
      </p>

      <div class="wpml-danger-box wpml:p-4 wpml:mb-6 wpml:flex wpml:items-start wpml:gap-2">
        <svg class="wpml:w-5 wpml:h-5 wpml:text-red-600 wpml:shrink-0" fill="none"
          stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 9v2m0 4h.01M4.93 4.93a10 10 0 1114.14 14.14 10 10 0 01-14.14-14.14z"/>
        </svg>
        <div>
          <p class="wpml:text-sm wpml:font-semibold wpml:text-red-900 wpml:mb-0.5">
            <?php esc_html_e( 'These tools rewrite translation data and can hide existing variations', 'wpml-troubleshooting' ); ?>
          </p>
          <p class="wpml:text-xs wpml:text-red-900/80">
            <?php
            esc_html_e( 'Make a full database backup before running them. Only check the boxes WPML Support has named, then press "Run the selected tools".', 'wpml-troubleshooting' );
            ?>
          </p>
        </div>
      </div>

      <div class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5">
        <?php WcmlEmbed::embed( 'wcml-translation-maintenance' ); ?>
      </div>
      <?php
  }


}
