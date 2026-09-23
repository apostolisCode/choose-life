<?php

namespace WPML\Troubleshooting\Tool;

use WPML\UserInterface\Web\Core\SharedKernel\Config\PageRenderInterface;


class ATESyncController implements PageRenderInterface {


  public function render() {
    $nonces = array(
      'source_id_migration' => wp_create_nonce( 'wpml-tm-ate-source-id-migration' ),
      'translators'         => wp_create_nonce( 'wpml_support_ate_sync' ),
    );
    ?>
      <h1 class="wpml:text-2xl wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
        <?php echo \wpml_bold_names( __( 'Synchronize with the <b>Advanced Translation Editor</b>', 'wpml-troubleshooting' ) ); ?>
      </h1>
      <p class="wpml:text-sm wpml:text-gray-500 wpml:mb-6">
        <?php echo \wpml_bold_names( __( "Re-align local records with the state on the <b>Advanced Translation Editor</b> server.", 'wpml-troubleshooting' ) ); ?>
      </p>

      <div class="wpml-danger-box wpml:p-4 wpml:mb-6 wpml:flex wpml:items-start wpml:gap-2">
        <svg class="wpml:w-5 wpml:h-5 wpml:text-red-600 wpml:shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.93 4.93a10 10 0 1114.14 14.14 10 10 0 01-14.14-14.14z"/>
        </svg>
        <div>
          <p class="wpml:text-sm wpml:font-semibold wpml:text-red-900 wpml:mb-0.5">
            <?php /* translators: Warning above a group of tools on WPML → Support. "These" are the tools below it. */ esc_html_e( 'Only run these when WPML Support asks you to', 'wpml-troubleshooting' ); ?>
          </p>
          <p class="wpml:text-xs wpml:text-red-900/80">
            <?php esc_html_e( "These tools push local data to overwrite the ATE server's view. Running them at the wrong time can unassign translators, reset job ownership, or duplicate jobs.", 'wpml-troubleshooting' ); ?>
          </p>
        </div>
      </div>

      <!-- Sync job IDs -->
      <section id="sync-jobs" class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5 wpml:mb-5 wpml:scroll-mt-8">
        <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
          <?php esc_html_e( 'Synchronize local job IDs with ATE', 'wpml-troubleshooting' ); ?>
        </h2>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-4">
          <?php esc_html_e( "Reconciles the job IDs in your local database with the IDs on the ATE server when the two have drifted apart. Use to unblock jobs that appear stuck or can't be opened in the editor.", 'wpml-troubleshooting' ); ?>
        </p>

        <div class="wpml-danger-box wpml:p-3 wpml:mb-3">
          <label class="wpml:flex wpml:items-center wpml:gap-2 wpml:text-sm wpml:text-red-900 wpml:cursor-pointer">
            <input type="checkbox" class="wpml-support-arm wpml-checkbox-native" data-arm="btn-sync-jobs"/>
            <?php esc_html_e( 'I understand and want to proceed', 'wpml-troubleshooting' ); ?>
          </label>
        </div>

        <button id="btn-sync-jobs" type="button" disabled
          data-ajax-action="wpml-tm-ate-source-id-migration"
          data-nonce-key="source_id_migration"
          class="wpml-button base-btn wpml-button--danger wpml:text-sm">
          <?php esc_html_e( 'Synchronize job IDs', 'wpml-troubleshooting' ); ?>
        </button>
        <span id="status-sync-jobs" class="wpml:text-xs wpml:text-gray-500 wpml:ml-3" aria-live="polite"></span>
      </section>

      <!-- Sync translators and managers -->
      <section id="sync-users" class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5 wpml:mb-5 wpml:scroll-mt-8">
        <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
          <?php esc_html_e( 'Synchronize translators and translation managers with ATE', 'wpml-troubleshooting' ); ?>
        </h2>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-4">
          <?php esc_html_e( 'Re-pushes your current translator and translation-manager assignments to the ATE server. Use when translators are missing from the ATE-side assignment list, or are assigned to jobs they should not see.', 'wpml-troubleshooting' ); ?>
        </p>

        <div class="wpml-danger-box wpml:p-3 wpml:mb-3">
          <label class="wpml:flex wpml:items-center wpml:gap-2 wpml:text-sm wpml:text-red-900 wpml:cursor-pointer">
            <input type="checkbox" class="wpml-support-arm wpml-checkbox-native" data-arm="btn-sync-users"/>
            <?php esc_html_e( 'I understand and want to proceed', 'wpml-troubleshooting' ); ?>
          </label>
        </div>

        <button id="btn-sync-users" type="button" disabled
          data-ajax-action="wpml_support_ate_sync_translators,wpml_support_ate_sync_managers"
          data-nonce-key="translators"
          class="wpml-button base-btn wpml-button--danger wpml:text-sm">
          <?php esc_html_e( 'Synchronize translators and managers', 'wpml-troubleshooting' ); ?>
        </button>
        <span id="status-sync-users" class="wpml:text-xs wpml:text-gray-500 wpml:ml-3" aria-live="polite"></span>
      </section>

      <script>
      (function () {
        var nonces = <?php echo wp_json_encode( $nonces ); ?>;
        // Built server-side: a root-relative '/wp-admin/…' would resolve against the
        // domain, not the WordPress root, and 404 on subdirectory installs (wpmldev-7682).
        var ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;

        document.querySelectorAll('.wpml-support-arm').forEach(function (cb) {
          cb.addEventListener('change', function () {
            var btn = document.getElementById(cb.getAttribute('data-arm'));
            if (btn) { btn.disabled = !cb.checked; }
          });
        });

        function postWpAjax(action, nonce) {
          var body = new URLSearchParams();
          body.append('action', action);
          body.append('nonce', nonce);
          return fetch(ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
          });
        }

        document.querySelectorAll('button[data-ajax-action]').forEach(function (btn) {
          btn.addEventListener('click', function () {
            var actions = btn.getAttribute('data-ajax-action').split(',');
            var nonceKey = btn.getAttribute('data-nonce-key');
            var nonce = nonces[nonceKey];
            var statusId = 'status-' + btn.id.replace(/^btn-/, '');
            var status = document.getElementById(statusId);
            btn.disabled = true;
            if (status) { status.textContent = '<?php echo esc_js( /* translators: Status shown next to a button on WPML → Support while its action is running. */ __( 'Working…', 'wpml-troubleshooting' ) ); ?>'; }

            Promise.all(actions.map(function (a) { return postWpAjax(a.trim(), nonce); }))
              .then(function (responses) {
                var allOk = responses.every(function (r) { return r.ok; });
                if (status) {
                  status.textContent = allOk
                    ? '<?php echo esc_js( /* translators: Status shown next to a button on WPML → Support once its action has finished. */ __( 'Done.', 'wpml-troubleshooting' ) ); ?>'
                    : '<?php echo esc_js( __( 'One of the syncs failed. Please retry.', 'wpml-troubleshooting' ) ); ?>';
                }
                if (!allOk) { btn.disabled = false; }
              })
              .catch(function () {
                if (status) { status.textContent = '<?php echo esc_js( __( 'Something went wrong. Please retry.', 'wpml-troubleshooting' ) ); ?>'; }
                btn.disabled = false;
              });
          });
        });
      })();
      </script>
      <?php
  }


}
