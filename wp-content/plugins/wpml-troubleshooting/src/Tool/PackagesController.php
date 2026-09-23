<?php

namespace WPML\Troubleshooting\Tool;

use WPML\UserInterface\Web\Core\SharedKernel\Config\PageRenderInterface;
use WPML_Package_Translation_HTML_Packages;


class PackagesController implements PageRenderInterface {

  const WIDTH_CLASS = 'wpml:max-w-5xl';


  public function render() {
    ?>
      <h1 class="wpml:text-2xl wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
        <?php /* translators: Name of a Support tool: its entry in the tool list on WPML → Support and the heading of its own screen. A package is a group of texts from one source, such as a form or a block. */ esc_html_e( 'Package management', 'wpml-troubleshooting' ); ?>
      </h1>
      <p class="wpml:text-sm wpml:text-gray-500 wpml:mb-6">
        <?php esc_html_e( "A \"package\" is a bundle of strings that another plugin hands to WPML so they can be translated — a Gravity Form's field labels, a Gutenberg block's preset text, a Toolset custom-post-type's admin labels, a page builder's shortcode strings. Packages are created and kept in sync automatically by the plugins that own them; you normally don't need to open this page.", 'wpml-troubleshooting' ); ?>
      </p>
      <p class="wpml:text-sm wpml:text-gray-500 wpml:mb-6">
        <?php esc_html_e( 'Come here only when a supporter is investigating a specific string that is not showing up for translation, or asks you to remove an orphaned package left over after a plugin was uninstalled. Deleting or re-registering a package here is a recovery action, not configuration.', 'wpml-troubleshooting' ); ?>
      </p>

      <div class="wpml-danger-box wpml:p-4 wpml:mb-6 wpml:flex wpml:items-start wpml:gap-2">
        <svg class="wpml:w-5 wpml:h-5 wpml:text-red-600 wpml:shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.93 4.93a10 10 0 1114.14 14.14 10 10 0 01-14.14-14.14z"/>
        </svg>
        <div>
          <p class="wpml:text-sm wpml:font-semibold wpml:text-red-900 wpml:mb-0.5">
            <?php esc_html_e( 'Deleting or editing packages here can break translations on your site', 'wpml-troubleshooting' ); ?>
          </p>
          <p class="wpml:text-xs wpml:text-red-900/80">
            <?php esc_html_e( 'These packages are created and maintained by the plugins they belong to. Manual changes here can orphan strings and remove translations from forms, blocks, and templates. Only make changes when WPML Support asks you to.', 'wpml-troubleshooting' ); ?>
          </p>
        </div>
      </div>

      <div class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5">
        <?php $this->renderLegacyPackagesView(); ?>
      </div>
      <?php
  }


  private function renderLegacyPackagesView(): void {
    if ( ! class_exists( WPML_Package_Translation_HTML_Packages::class ) || ! method_exists( WPML_Package_Translation_HTML_Packages::class, 'package_translation_menu' ) ) {
      /* translators: Message on WPML → Support. A package is a group of texts from one source, such as a form or a block. Keep the <b> tags around the plugin name. */
      echo '<p class="wpml:text-sm wpml:text-gray-500 wpml:italic">' . \wpml_bold_names( __( 'Package management requires the WPML String Translation plugin to be active.', 'wpml-troubleshooting' ) ) . '</p>';
      return;
    }

    ob_start();
    WPML_Package_Translation_HTML_Packages::package_translation_menu();
    $body = (string) ob_get_clean();
    echo $body;
  }


}
