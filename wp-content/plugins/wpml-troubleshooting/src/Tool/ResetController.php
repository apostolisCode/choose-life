<?php

namespace WPML\Troubleshooting\Tool;

use WPML\UserInterface\Web\Core\SharedKernel\Config\PageRenderInterface;


class ResetController implements PageRenderInterface {


  public function render() {
    $reset_all_url   = admin_url( 'admin-post.php' );
    $reset_all_nonce = wp_create_nonce( 'icl_reset_all' );
    ?>
      <h1 class="wpml:text-2xl wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
        <?php /* translators: Name of a Support tool: its entry in the tool list on WPML → Support, the heading of its own screen, and its button label. Verb phrase, imperative (reset WPML on this site). */ esc_html_e( 'Reset WPML', 'wpml-troubleshooting' ); ?>
      </h1>
      <p class="wpml:text-sm wpml:text-gray-500 wpml:mb-6">
        <?php esc_html_e( 'Last-resort recovery. This permanently deletes data and cannot be undone. Make a full database backup before running it.', 'wpml-troubleshooting' ); ?>
      </p>

      <!-- Reset WPML entirely -->
      <section id="reset-all" class="wpml-danger-box wpml:overflow-hidden wpml:scroll-mt-8">
        <div class="wpml:px-5 wpml:py-4 wpml:bg-white">
          <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
            <?php esc_html_e( 'Reset WPML entirely', 'wpml-troubleshooting' ); ?>
          </h2>
          <p class="wpml:text-xs wpml:text-gray-500">
            <?php esc_html_e( 'Deletes every WPML table, removes all language assignments on your content, and deactivates the plugin. Your multilingual site returns to a single-language WordPress install.', 'wpml-troubleshooting' ); ?>
          </p>
        </div>
        <div class="wpml:px-5 wpml:py-4">
          <div class="wpml:flex wpml:items-start wpml:gap-2 wpml:mb-3">
            <svg class="wpml:w-5 wpml:h-5 wpml:text-red-600 wpml:shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.93 4.93a10 10 0 1114.14 14.14 10 10 0 01-14.14-14.14z"/>
            </svg>
            <div>
              <p class="wpml:text-sm wpml:font-semibold wpml:text-red-900 wpml:mb-0.5">
                <?php esc_html_e( "This isn't a toy button", 'wpml-troubleshooting' ); ?>
              </p>
              <p class="wpml:text-xs wpml:text-red-900/80">
                <?php /* translators: Warning on WPML → Support above the reset button. "It" is the reset. */ esc_html_e( "It will completely reset WPML and permanently delete all language information from your site. Translated content stays in your WordPress database but loses its connection to other translations and to its language. You'll need to reinstall WPML and rebuild your language setup from scratch. There's no undo.", 'wpml-troubleshooting' ); ?>
              </p>
            </div>
          </div>

          <form method="post" action="<?php echo esc_url( $reset_all_url ); ?>" id="form-reset-all" class="wpml:space-y-3">
            <input type="hidden" name="action" value="wpml_reset_all"/>
            <input type="hidden" name="icl_reset_allnonce" value="<?php echo esc_attr( $reset_all_nonce ); ?>"/>
            <label class="wpml:flex wpml:items-center wpml:gap-2 wpml:text-sm wpml:text-red-900 wpml:cursor-pointer">
              <input type="checkbox" id="reset-all-arm" class="wpml-checkbox-native"/>
              <?php /* translators: Label of the confirmation checkbox on WPML → Support. "This" is resetting WPML. Written in the first person, as the user saying it. */ esc_html_e( "I understand this will permanently delete all of WPML's language data", 'wpml-troubleshooting' ); ?>
            </label>
            <div>
              <label class="wpml:block wpml:text-xs wpml:font-medium wpml:text-red-900 wpml:mb-1" for="reset-all-typed">
                <?php
                printf(
                  /* translators: %s is the literal phrase the user must type, wrapped in a span. */
                  esc_html__( 'Type %s to confirm', 'wpml-troubleshooting' ),
                  '<span class="wpml:font-mono wpml:font-semibold">RESET WPML</span>'
                );
                ?>
              </label>
              <input id="reset-all-typed" type="text" placeholder="RESET WPML"
                class="wpml:w-64 wpml:border wpml:border-red-300 wpml:bg-white wpml:rounded wpml:px-3 wpml:py-1.5 wpml:text-sm wpml:focus:outline-none wpml:focus:ring-2 wpml:focus:ring-red-400 wpml:focus:border-transparent"/>
            </div>
            <button id="btn-reset-all" type="submit" disabled
              class="wpml-button base-btn wpml-button--danger wpml:text-sm">
              <?php /* translators: Name of a Support tool: its entry in the tool list on WPML → Support, the heading of its own screen, and its button label. Verb phrase, imperative (reset WPML on this site). */ esc_html_e( 'Reset WPML', 'wpml-troubleshooting' ); ?>
            </button>
          </form>
        </div>
      </section>

      <script>
      (function () {
        // Reset WPML entirely: arm + typed confirmation gate.
        var armCb = document.getElementById('reset-all-arm');
        var typed = document.getElementById('reset-all-typed');
        var btn   = document.getElementById('btn-reset-all');
        function evalReset() {
          btn.disabled = ! ( armCb.checked && typed.value.trim() === 'RESET WPML' );
        }
        armCb.addEventListener('change', evalReset);
        typed.addEventListener('input', evalReset);
      })();
      </script>
      <?php
  }


  public function handle_reset_all() {
    check_admin_referer( 'icl_reset_all', 'icl_reset_allnonce' );

    if ( ! current_user_can( 'wpml_manage_support' ) ) {
      wp_die( esc_html__( 'You do not have permission to reset WPML.', 'wpml-troubleshooting' ) );
    }

    require_once WPML_PLUGIN_PATH . '/inc/functions-troubleshooting.php';
    icl_reset_wpml();

    wp_safe_redirect( admin_url( 'plugins.php?deactivate=true' ) );
    exit();
  }


}
