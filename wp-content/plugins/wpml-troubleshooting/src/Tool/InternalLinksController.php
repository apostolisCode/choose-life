<?php

namespace WPML\Troubleshooting\Tool;

use WPML\UserInterface\Web\Core\SharedKernel\Config\PageRenderInterface;
use WPML_Translate_Link_Target_Global_State;


class InternalLinksController implements PageRenderInterface {

  const BATCH_SIZE = 50;


  public static function isApplicable(): bool {
    if ( ! class_exists( WPML_Translate_Link_Target_Global_State::class ) ) {
      return false;
    }
    $sitepress = isset( $GLOBALS['sitepress'] ) ? $GLOBALS['sitepress'] : null;
    if ( ! $sitepress ) {
      return false;
    }
    $state = new WPML_Translate_Link_Target_Global_State( $sitepress );
    return (bool) $state->is_rescan_required();
  }


  public function render() {
    $applicable = self::isApplicable();
    $nonce      = wp_create_nonce( 'WPML_Ajax_Update_Link_Targets' );
    $i18n       = array(
      /* translators: Status shown on WPML → Support while the tool reads the site's pages and posts. */
      'scanningPosts'    => __( 'Scanning posts…', 'wpml-troubleshooting' ),
      /* translators: Status shown on WPML → Support while the tool reads the site's texts. */
      'scanningStrings'  => __( 'Scanning strings…', 'wpml-troubleshooting' ),
      /* translators: %s is the number of links that were rewired. */
      'doneWithCount'    => __( 'Done — %s legacy links rewired. Your internal links are now in sync.', 'wpml-troubleshooting' ),
      'doneWithoutCount' => __( 'Done. Your internal links are now in sync.', 'wpml-troubleshooting' ),
      'error'            => __( 'Something went wrong. Please retry.', 'wpml-troubleshooting' ),
    );
    ?>
      <h1 class="wpml:text-2xl wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
        <?php esc_html_e( 'Update internal links', 'wpml-troubleshooting' ); ?>
      </h1>
      <p class="wpml:text-sm wpml:text-gray-500 wpml:mb-6">
        <?php esc_html_e( 'Some posts created before WPML 4.7 still need their internal links rewired to their translated versions. Run once to scan and fix those legacy posts. Newer posts have their links adjusted automatically — this action only applies to sites that carried content over from older WPML versions.', 'wpml-troubleshooting' ); ?>
      </p>

      <?php if ( ! $applicable ) : ?>
        <section class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5 wpml:mb-5">
          <div class="wpml:flex wpml:items-start wpml:gap-3">
            <svg class="wpml:w-5 wpml:h-5 wpml:text-green-600 wpml:shrink-0 wpml:mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <div>
              <p class="wpml:text-sm wpml:font-medium wpml:text-gray-900 wpml:mb-1">
                <?php esc_html_e( "Your internal links are already in sync.", 'wpml-troubleshooting' ); ?>
              </p>
              <p class="wpml:text-xs wpml:text-gray-500">
                <?php
                printf(
                  /* translators: %s is the URL of the Sticky Links plugin documentation. */
                  esc_html__( 'WPML keeps internal links updated automatically as you translate content. If you also want to switch language URL formats without breaking links, the %s add-on handles that case.', 'wpml-troubleshooting' ),
                  /* translators: Link text on WPML → Support naming the Sticky Links add-on. Product name: keep it as it is. */
                  '<a href="' . esc_url( self::stickyLinksDocUrl() ) . '" target="_blank" class="wpml:text-blue wpml:hover:underline">' . esc_html__( 'Sticky Links', 'wpml-troubleshooting' ) . '</a>'
                );
                ?>
              </p>
            </div>
          </div>
        </section>
      <?php else : ?>
        <section id="fix-empty-codes" class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5 wpml:mb-5">
          <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
            <?php esc_html_e( 'Adjust legacy internal links', 'wpml-troubleshooting' ); ?>
          </h2>
          <p class="wpml:text-sm wpml:text-gray-500 wpml:mb-6">
            <?php esc_html_e( "Scans posts that still need link adjustments from before the automatic-tracking change in WPML 4.7. After this completes, the section disappears — it only runs once per site.", 'wpml-troubleshooting' ); ?>
          </p>
          <div class="wpml:flex wpml:flex-wrap wpml:items-center wpml:gap-3">
            <button id="wpml-support-internal-links" type="button"
              data-nonce="<?php echo esc_attr( $nonce ); ?>"
              class="wpml-button base-btn wpml:text-sm wpml:disabled:opacity-50 wpml:disabled:cursor-not-allowed">
              <?php esc_html_e( 'Scan now and adjust links', 'wpml-troubleshooting' ); ?>
            </button>
            <span id="wpml-support-internal-links-status" class="wpml:text-xs wpml:text-gray-500" aria-live="polite"></span>
          </div>
        </section>

        <script>
        (function () {
          var btn    = document.getElementById('wpml-support-internal-links');
          var status = document.getElementById('wpml-support-internal-links-status');
          if (!btn) { return; }
          var i18n  = <?php echo wp_json_encode( $i18n ); ?>;
          var batch = <?php echo self::BATCH_SIZE; ?>;
          // Built server-side: a root-relative '/wp-admin/…' would resolve against the
          // domain, not the WordPress root, and 404 on subdirectory installs (wpmldev-7682).
          var ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;

          function postBatch(action, lastProcessed) {
            var body = new URLSearchParams();
            body.append('action', action);
            body.append('nonce', btn.getAttribute('data-nonce'));
            body.append('last_processed', String(lastProcessed));
            body.append('number_to_process', String(batch));

            return fetch(ajaxUrl, {
              method: 'POST',
              credentials: 'same-origin',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: body.toString()
            }).then(function (r) {
              if (!r.ok) { throw new Error('http ' + r.status); }
              return r.json();
            });
          }

          function runLoop(action, statusLabel) {
            return new Promise(function (resolve, reject) {
              var lastProcessed = 0;
              var totalFixed    = 0;
              function step() {
                status.textContent = statusLabel;
                postBatch(action, lastProcessed).then(function (json) {
                  if (!json || !json.success) { reject(new Error('bad payload')); return; }
                  var d = json.data || {};
                  // The handler returns these as strings — coerce with
                  // Number() so the loop-exit and lastProcessed advance
                  // arithmetic. Without the coercion `!"0"` is `false`
                  // (truthy string) and the loop runs forever.
                  totalFixed    = d.links_fixed != null ? Number(d.links_fixed) : totalFixed;
                  lastProcessed = d.last_processed != null ? Number(d.last_processed) : lastProcessed;
                  var numberLeft = Number(d.number_left || 0);
                  if (!numberLeft) { resolve(totalFixed); return; }
                  step();
                }).catch(reject);
              }
              step();
            });
          }

          function renderSuccess(total) {
            // Swap the action card for the same green-check empty state
            // a fresh page load would show. The legacy is_rescan_required
            // flag clears server-side as posts get processed, so a reload
            // would render the same shape — this just avoids the reload.
            var section = document.getElementById('fix-empty-codes');
            if (!section) { return; }
            var message = total > 0
              ? i18n.doneWithCount.replace('%s', total)
              : i18n.doneWithoutCount;
            section.innerHTML =
              '<div class="wpml:flex wpml:items-start wpml:gap-3">' +
                '<svg class="wpml:w-5 wpml:h-5 wpml:text-green-600 wpml:shrink-0 wpml:mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">' +
                  '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>' +
                '</svg>' +
                '<div>' +
                  '<p class="wpml:text-sm wpml:font-medium wpml:text-gray-900 wpml:mb-1"></p>' +
                '</div>' +
              '</div>';
            section.querySelector('p').textContent = message;
          }

          btn.addEventListener('click', function () {
            btn.disabled = true;
            runLoop('WPML_Ajax_Update_Link_Targets_In_Posts', i18n.scanningPosts)
              .then(function (fixedPosts) {
                return runLoop('WPML_Ajax_Update_Link_Targets_In_Strings', i18n.scanningStrings)
                  .then(function (fixedStrings) {
                    return fixedPosts + fixedStrings;
                  });
              })
              .then(function (total) {
                renderSuccess(total);
              })
              .catch(function () {
                status.textContent = i18n.error;
                btn.disabled = false;
              });
          });
        })();
        </script>
      <?php endif; ?>
      <?php
  }

  private static function stickyLinksDocUrl(): string {
    $url = 'https://wpml.org/documentation/getting-started-guide/sticky-links/';

    if ( class_exists( '\WPML\OutboundLinks\OutboundLinks' ) ) {
      return \WPML\OutboundLinks\OutboundLinks::to(
        $url,
        [ 'medium' => 'support', 'campaign' => 'getting-started' ]
      );
    }

    return $url;
  }


}
