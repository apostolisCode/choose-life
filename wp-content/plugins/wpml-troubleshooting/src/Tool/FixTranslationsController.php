<?php

namespace WPML\Troubleshooting\Tool;

use WPML\UserInterface\Web\Core\SharedKernel\Config\PageRenderInterface;


class FixTranslationsController implements PageRenderInterface {


  public function render() {
    $nonces = array(
      'cache_clear'                             => wp_create_nonce( 'cache_clear' ),
      'assign_translation_status_to_duplicates' => wp_create_nonce( 'assign_translation_status_to_duplicates' ),
      'wpml_clear_ts'                                     => wp_create_nonce( 'wpml_clear_ts' ),
      'wpml_tm_refresh_services'                          => wp_create_nonce( 'wpml_tm_refresh_services' ),
      'wpml-tm-reset-preferred-translation-service'       => wp_create_nonce( 'wpml-tm-reset-preferred-translation-service' ),
      'wpml_reset_pro_trans_config'                       => wp_create_nonce( 'wpml_reset_pro_trans_config' ),
      'lsTemplatesUpdateDomain'                           => wp_create_nonce( 'WPML\\Troubleshooting\\Endpoints\\LsTemplateDomainUpdater\\RequestHandler' ),
      'retryStuckAutomaticJobs'                           => wp_create_nonce( 'WPML\\Troubleshooting\\Endpoints\\RetryStuckAutomaticJobs\\RequestHandler' ),
    );
    $i18n = array(
      /* translators: Status shown next to a button on WPML → Support once its action has finished. */
      'done'         => __( 'Done.', 'wpml-troubleshooting' ),
      /* translators: Status shown next to a button on WPML → Support while its action is running. */
      'workingTitle' => __( 'Working…', 'wpml-troubleshooting' ),
      'errorTitle'   => __( 'Something went wrong. Please retry.', 'wpml-troubleshooting' ),
    );
    ?>
      <h1 class="wpml:text-2xl wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
        <?php /* translators: Name of a Support tool: its entry in the tool list on WPML → Support and the heading of its own screen. Verb phrase, imperative. */ esc_html_e( 'Fix translations', 'wpml-troubleshooting' ); ?>
      </h1>
      <p class="wpml:text-sm wpml:text-gray-500 wpml:mb-6">
        <?php esc_html_e( 'Resolve common translation-state problems — stuck statuses, missing categories, stale caches.', 'wpml-troubleshooting' ); ?>
      </p>

      <!-- Clear cache -->
      <section id="clear-cache" class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5 wpml:mb-5 wpml:scroll-mt-8">
        <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
          <?php esc_html_e( 'Clear the cache in WPML', 'wpml-troubleshooting' ); ?>
        </h2>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-4">
          <?php esc_html_e( "Flushes WPML's internal cache. Often the first thing to try after migrations, plugin updates, or when something looks out of sync.", 'wpml-troubleshooting' ); ?>
        </p>
        <button type="button"
          data-wpml-support-action="cache_clear"
          data-wpml-support-done="<?php /* translators: Status shown next to the Clear cache button on WPML → Support once the cache has been cleared. */ echo esc_attr__( "WPML's cache was cleared.", 'wpml-troubleshooting' ); ?>"
          data-wpml-support-failed="<?php /* translators: Status shown next to the Clear cache button on WPML → Support when the cache could not be cleared. */ echo esc_attr__( 'Could not clear the cache — please retry.', 'wpml-troubleshooting' ); ?>"
          class="wpml-button base-btn wpml:text-sm wpml:disabled:opacity-50 wpml:disabled:cursor-not-allowed">
          <?php /* translators: Button label on WPML → Support. Verb phrase, imperative. */ esc_html_e( 'Clear cache', 'wpml-troubleshooting' ); ?>
        </button>
        <span id="status-clear-cache" class="wpml:text-xs wpml:text-gray-500 wpml:ml-3" aria-live="polite"></span>
      </section>

      <!-- Retry stuck automatic translations -->
      <section id="retry-stuck" class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5 wpml:mb-5 wpml:scroll-mt-8">
        <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
          <?php esc_html_e( 'Retry stuck automatic translations', 'wpml-troubleshooting' ); ?>
        </h2>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-4">
          <?php esc_html_e( "Re-queues automatic translations that got stuck mid-process so they can finish. Use when an automatic translation has been pending for an unusually long time and hasn't completed.", 'wpml-troubleshooting' ); ?>
        </p>
        <button type="button"
          data-wpml-support-wpml-action="WPML\Troubleshooting\Endpoints\RetryStuckAutomaticJobs\RequestHandler"
          data-wpml-support-nonce-key="retryStuckAutomaticJobs"
          class="wpml-button base-btn wpml:text-sm wpml:disabled:opacity-50 wpml:disabled:cursor-not-allowed">
          <?php esc_html_e( 'Retry stuck translations', 'wpml-troubleshooting' ); ?>
        </button>
        <span id="status-retry-stuck" class="wpml:text-xs wpml:text-gray-500 wpml:ml-3" aria-live="polite"></span>
      </section>

      <!-- Refresh Translation Services (disabled — TBD handler) -->
      <section id="refresh-services" class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5 wpml:mb-5 wpml:scroll-mt-8">
        <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
          <?php esc_html_e( 'Refresh Translation Services', 'wpml-troubleshooting' ); ?>
        </h2>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-4">
          <?php
          printf(
            /* translators: %1$s and %2$s wrap a link to Settings → Translation Services. */
            esc_html__( "Re-fetches the list of available translation services from WPML. Run this if a service you signed up for isn't appearing in %1\$sSettings → Translation Services%2\$s, or after enabling a new service in your WPML account.", 'wpml-troubleshooting' ),
            '<a href="' . esc_url( admin_url( 'admin.php?page=tm/menu/settings&section=ai-translation' ) ) . '" class="wpml:text-blue wpml:hover:underline">',
            '</a>'
          );
          ?>
        </p>
        <button type="button"
          data-wpml-support-ajax="wpml_tm_refresh_services"
          class="wpml-button base-btn wpml:text-sm wpml:disabled:opacity-50 wpml:disabled:cursor-not-allowed">
          <?php /* translators: Button label on WPML → Support that fetches the list of translation services again. Verb phrase, imperative. */ esc_html_e( 'Refresh services', 'wpml-troubleshooting' ); ?>
        </button>
        <span id="status-refresh-services" class="wpml:text-xs wpml:text-gray-500 wpml:ml-3" aria-live="polite"></span>
      </section>

      <!-- Update domain name in language switcher (disabled — TBD handler) -->
      <section id="update-domain" class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5 wpml:mb-5 wpml:scroll-mt-8">
        <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
          <?php esc_html_e( 'Update domain name in language switcher', 'wpml-troubleshooting' ); ?>
        </h2>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-4">
          <?php esc_html_e( "Rewrites the domain stored against each language in the language switcher to match the site's current home URL. Run this after a domain change or migration if the language switcher still points to the old domain.", 'wpml-troubleshooting' ); ?>
        </p>
        <button type="button"
          data-wpml-support-wpml-action="WPML\Troubleshooting\Endpoints\LsTemplateDomainUpdater\RequestHandler"
          data-wpml-support-nonce-key="lsTemplatesUpdateDomain"
          class="wpml-button base-btn wpml:text-sm wpml:disabled:opacity-50 wpml:disabled:cursor-not-allowed">
          <?php /* translators: Button label on WPML → Support that writes the site's current web address into the language switcher. Verb phrase, imperative. */ esc_html_e( 'Update domain', 'wpml-troubleshooting' ); ?>
        </button>
        <span id="status-update-domain" class="wpml:text-xs wpml:text-gray-500 wpml:ml-3" aria-live="polite"></span>
      </section>

      <!-- Assign translation status to duplicates -->
      <section id="assign-status" class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5 wpml:mb-5 wpml:scroll-mt-8">
        <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
          <?php /* translators: Name of a Support tool section: its entry in the Support search list and its heading. Verb phrase, imperative. */ esc_html_e( 'Assign translation status to duplicates', 'wpml-troubleshooting' ); ?>
        </h2>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-4">
          <?php esc_html_e( 'Marks WPML duplicates with a proper translation status so they appear to translation-management and automatic-translation tools. Run after bulk duplication.', 'wpml-troubleshooting' ); ?>
        </p>
        <button type="button"
          data-wpml-support-action="assign_translation_status_to_duplicates"
          class="wpml-button base-btn wpml:text-sm wpml:disabled:opacity-50 wpml:disabled:cursor-not-allowed">
          <?php /* translators: Button label on WPML → Support that gives duplicated content a translation status. Verb phrase, imperative. */ esc_html_e( 'Assign status', 'wpml-troubleshooting' ); ?>
        </button>
        <span id="status-assign-status" class="wpml:text-xs wpml:text-gray-500 wpml:ml-3" aria-live="polite"></span>
      </section>

      <!-- Temporarily clear the account's preferred translation service -->
      <section id="enable-other-services" class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5 wpml:mb-5 wpml:scroll-mt-8">
        <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
          <?php esc_html_e( 'Enable other translation services', 'wpml-troubleshooting' ); ?>
        </h2>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-4">
          <?php esc_html_e( 'Temporarily ignores the preferred translation service configured in your WPML.org account so you can choose another provider. You can refresh the account preference below.', 'wpml-troubleshooting' ); ?>
        </p>
        <button type="button"
          data-wpml-support-ajax="wpml_clear_ts"
          class="wpml-button base-btn wpml:text-sm wpml:disabled:opacity-50 wpml:disabled:cursor-not-allowed">
          <?php esc_html_e( 'Enable other services', 'wpml-troubleshooting' ); ?>
        </button>
        <span id="status-enable-other-services" class="wpml:text-xs wpml:text-gray-500 wpml:ml-3" aria-live="polite"></span>
      </section>

      <!-- Refresh preferred translation service -->
      <section id="reset-service" class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5 wpml:mb-5 wpml:scroll-mt-8">
        <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
          <?php esc_html_e( 'Refresh preferred translation service', 'wpml-troubleshooting' ); ?>
        </h2>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-4">
          <?php esc_html_e( 'Fetches the preferred translation service configured in your WPML.org account and applies it to this site again.', 'wpml-troubleshooting' ); ?>
        </p>
        <button type="button"
          data-wpml-support-ajax="wpml-tm-reset-preferred-translation-service"
          class="wpml-button base-btn wpml:text-sm wpml:disabled:opacity-50 wpml:disabled:cursor-not-allowed">
          <?php esc_html_e( 'Refresh preferred service', 'wpml-troubleshooting' ); ?>
        </button>
        <span id="status-reset-service" class="wpml:text-xs wpml:text-gray-500 wpml:ml-3" aria-live="polite"></span>
      </section>

      <!-- Reset professional translation state (armed; disabled) -->
      <section id="reset-professional" class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5 wpml:mb-5 wpml:scroll-mt-8">
        <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
          <?php /* translators: Name of a Support tool section: its entry in the Support search list, its heading and its button label. Verb phrase, imperative. */ esc_html_e( 'Reset professional translation state', 'wpml-troubleshooting' ); ?>
        </h2>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-4">
          <?php esc_html_e( 'Clears ongoing professional-translation jobs on this site and re-enables sending content to translation. Use this if your translation service stopped working or you want to cancel every in-flight job at once.', 'wpml-troubleshooting' ); ?>
        </p>

        <div class="wpml-danger-box wpml:p-4 wpml:mb-3">
          <div class="wpml:flex wpml:items-start wpml:gap-2 wpml:mb-2">
            <svg class="wpml:w-5 wpml:h-5 wpml:text-red-600 wpml:shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.93 4.93a10 10 0 1114.14 14.14 10 10 0 01-14.14-14.14z"/>
            </svg>
            <div>
              <p class="wpml:text-sm wpml:font-semibold wpml:text-red-900 wpml:mb-0.5">
                <?php esc_html_e( 'This stops every translation job currently in progress', 'wpml-troubleshooting' ); ?>
              </p>
              <p class="wpml:text-xs wpml:text-red-900/80">
                <?php esc_html_e( "Work already completed and delivered is kept. Jobs that are in-flight with a translator or service will be cancelled and won't be charged. You'll need to resend them if you still want those translations.", 'wpml-troubleshooting' ); ?>
              </p>
            </div>
          </div>
          <label class="wpml:flex wpml:items-center wpml:gap-2 wpml:text-sm wpml:text-red-900 wpml:cursor-pointer">
            <input type="checkbox" class="wpml-support-arm wpml-checkbox-native" data-arm="btn-reset-professional"/>
            <?php esc_html_e( 'I understand and want to proceed', 'wpml-troubleshooting' ); ?>
          </label>
        </div>

        <button id="btn-reset-professional" type="button" disabled
          data-wpml-support-ajax="wpml_reset_pro_trans_config"
          class="wpml-button base-btn wpml-button--danger wpml:text-sm">
          <?php /* translators: Name of a Support tool section: its entry in the Support search list, its heading and its button label. Verb phrase, imperative. */ esc_html_e( 'Reset professional translation state', 'wpml-troubleshooting' ); ?>
        </button>
        <span id="status-reset-professional" class="wpml:text-xs wpml:text-gray-500 wpml:ml-3" aria-live="polite"></span>
      </section>

      <script>
      (function () {
        var nonces = <?php echo wp_json_encode( $nonces ); ?>;
        var i18n   = <?php echo wp_json_encode( $i18n ); ?>;

        // Built server-side: a root-relative '/wp-admin/…' would resolve against the
        // domain, not the WordPress root, and 404 on subdirectory installs (wpmldev-7682).
        var ajaxUrl  = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
        var adminUrl = <?php echo wp_json_encode( admin_url( 'admin.php' ) ); ?>;

        // Phase 1 dispatcher: POST admin.php?debug_action=…&nonce=…
        function postDispatcher(action) {
          var url = adminUrl + '?debug_action=' + encodeURIComponent(action) +
                    '&nonce=' + encodeURIComponent(nonces[action] || '');
          return fetch(url, { method: 'POST', credentials: 'same-origin' });
        }

        // Simple wp-ajax single-shot (action == nonce action).
        function postWpAjax(action) {
          var body = new URLSearchParams();
          body.append('action', action);
          body.append('nonce', nonces[action] || '');
          return fetch(ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
          });
        }

        // `wpml_action` central dispatcher: POST action=wpml_action with
        // endpoint=<FQCN>, nonce=<created against the FQCN>, data=<json>.
        function postWpmlAction(endpoint, nonceKey) {
          var body = new URLSearchParams();
          body.append('action', 'wpml_action');
          body.append('endpoint', endpoint);
          body.append('nonce', nonces[nonceKey] || '');
          body.append('data', '{}');
          return fetch(ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
          });
        }

        // wpmldev-8466: HTTP 200 on its own is not proof the server did the work.
        // All three dispatch paths answer JSON: the `debug_action` dispatcher and
        // wp-ajax send `{"success":true|false}`, and a refusal now carries 400/403.
        // A legacy action that prints its own body (`assign_translation_status_to_duplicates`
        // answers `{"updated":N}`) carries no `success` key at all — that still counts as
        // done. Anything else — an empty body, an HTML admin page, `success:false` —
        // is a refusal and must not read "Done".
        function isSuccessBody(text) {
          var payload;
          try {
            payload = JSON.parse(text);
          } catch (e) {
            return false;
          }
          if (!payload || typeof payload !== 'object') { return false; }
          if (typeof payload.success !== 'undefined') { return payload.success === true; }
          return true;
        }

        // wpmldev-8558: the outcome goes on the tool's own status line — the
        // `<span id="status-…" aria-live="polite">` beside the button, which is
        // how every other Support tool reports (Database & strings maintenance,
        // the address-cache block, the usage-reporting card). It used to be
        // written over the button's own label and put back after 1.5s, so a
        // tool that finished instantly — "Clear cache" — confirmed nothing a
        // reader could catch, and a screen reader was never told at all.
        // A tool with a sentence of its own carries it on the button as
        // `data-wpml-support-done` / `-failed`; the rest use these two.
        function statusLineFor(btn) {
          var section = btn.closest('section');
          return section ? section.querySelector('[id^="status-"]') : null;
        }

        function withFeedback(btn, fn) {
          var status = statusLineFor(btn);
          var doneText   = btn.getAttribute('data-wpml-support-done') || i18n.done;
          var failedText = btn.getAttribute('data-wpml-support-failed') || i18n.errorTitle;

          btn.disabled = true;
          if (status) { status.textContent = i18n.workingTitle; }

          // wpmldev-8588: a repair endpoint can cap its own batch size and
          // answer `hasMore:true` while rows are still waiting
          // (RetryStuckAutomaticJobs\RequestHandler, LIMIT=100 per request).
          // Keep firing the same request until it reports `hasMore:false` (or
          // carries no such field at all, like every other tool on this page)
          // — one click used to read Done after repairing only the first
          // hundred of however many were stuck (wpmldev-8103 F26).
          //
          // The `wpml_action` dispatcher answers through `wp_send_json_success()`
          // (Factory.php), which wraps the handler's own array as
          // `{"success":true,"data":{…}}` — so `hasMore` lives at
          // `payload.data.hasMore`, not at the envelope's own top level (only
          // `success` is there). Read whichever of the two actually carries it,
          // so a differently-shaped answer (unwrapped, like the other two
          // dispatch paths) still works.
          function runUntilDone() {
            return fn().then(function (r) {
              if (!r.ok) { throw new Error('http ' + r.status); }
              return r.text();
            }).then(function (text) {
              if (!isSuccessBody(text)) { throw new Error('refused'); }
              var payload;
              try { payload = JSON.parse(text); } catch (e) { payload = null; }
              var body = payload && payload.data && typeof payload.data === 'object' ? payload.data : payload;
              if (body && body.hasMore === true) { return runUntilDone(); }
            });
          }

          runUntilDone().then(function () {
            if (status) { status.textContent = doneText; }
            btn.disabled = false;
          }).catch(function () {
            if (status) { status.textContent = failedText; }
            btn.disabled = false;
          });
        }

        // Armed "I understand" checkboxes: flip the target button's disabled
        // state based on the checkbox. The buttons themselves carry their own
        // URL/nonce data-attrs and run through the matching dispatch path on
        // click, just like un-armed buttons. Only the "is dangerous" gate is
        // the checkbox; the click path is identical.
        document.querySelectorAll('.wpml-support-arm').forEach(function (cb) {
          cb.addEventListener('change', function () {
            var btn = document.getElementById(cb.getAttribute('data-arm'));
            if (!btn) { return; }
            btn.disabled = !cb.checked;
          });
        });

        document.querySelectorAll('button[data-wpml-support-action]').forEach(function (btn) {
          btn.addEventListener('click', function () {
            var action = btn.getAttribute('data-wpml-support-action');
            if (!action) { return; }
            withFeedback(btn, function () { return postDispatcher(action); });
          });
        });

        document.querySelectorAll('button[data-wpml-support-ajax]').forEach(function (btn) {
          btn.addEventListener('click', function () {
            var action = btn.getAttribute('data-wpml-support-ajax');
            if (!action) { return; }
            withFeedback(btn, function () { return postWpAjax(action); });
          });
        });

        document.querySelectorAll('button[data-wpml-support-wpml-action]').forEach(function (btn) {
          btn.addEventListener('click', function () {
            var endpoint = btn.getAttribute('data-wpml-support-wpml-action');
            var nonceKey = btn.getAttribute('data-wpml-support-nonce-key');
            if (!endpoint) { return; }
            withFeedback(btn, function () { return postWpmlAction(endpoint, nonceKey); });
          });
        });
      })();
      </script>
      <?php
  }


}
