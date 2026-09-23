<?php

namespace WPML\Troubleshooting\Tool;


class UrlResolutionCacheSection {

  const STATUS_ENDPOINT  = 'WPML\\Troubleshooting\\Endpoints\\SingleUrlCache\\GetStatus';
  const REBUILD_ENDPOINT = 'WPML\\Troubleshooting\\Endpoints\\SingleUrlCache\\Rebuild';
  const PROCESS_ENDPOINT = 'WPML\\Troubleshooting\\Endpoints\\SingleUrlCache\\ProcessBatch';


  public static function isAvailable(): bool {
    return class_exists( self::STATUS_ENDPOINT );
  }


  public function render(): void {
    $nonces = array(
      'status'  => wp_create_nonce( self::STATUS_ENDPOINT ),
      'rebuild' => wp_create_nonce( self::REBUILD_ENDPOINT ),
      'process' => wp_create_nonce( self::PROCESS_ENDPOINT ),
    );

    $endpoints = array(
      'status'  => self::STATUS_ENDPOINT,
      'rebuild' => self::REBUILD_ENDPOINT,
      'process' => self::PROCESS_ENDPOINT,
    );

    $i18n = array(
      /* translators: Shown while a dialog or a panel is still fetching what it has to show. */
      'loading'      => __( 'Loading…', 'wpml-troubleshooting' ),
      /* translators: Status shown next to a button on WPML → Support while its action is running. */
      'working'      => __( 'Working…', 'wpml-troubleshooting' ),
      /* translators: Status shown on WPML → Support while the address cache is being built again. */
      'rebuilding'   => __( 'Rebuilding…', 'wpml-troubleshooting' ),
      'rebuild'      => __( 'Clear and rebuild cache', 'wpml-troubleshooting' ),
      'confirm'      => __( 'Clear and rebuild the URL resolution cache for this site? Links may temporarily stay in their source language while the cache rebuilds.', 'wpml-troubleshooting' ),
      'failed'       => __( 'The URL resolution cache request failed.', 'wpml-troubleshooting' ),
      'complete'     => __( 'The URL resolution cache rebuild is complete.', 'wpml-troubleshooting' ),
      'preparing'    => __( 'Preparing the cache rebuild…', 'wpml-troubleshooting' ),
      /* translators: Row label on the address-cache block of WPML → Support: which round of the cache the numbers below belong to. */
      'generation'   => __( 'Generation', 'wpml-troubleshooting' ),
      /* translators: Heading of a count on the address-cache block of WPML → Support: the addresses the cache has worked out. Adjective describing them, not a verb. */
      'resolved'     => __( 'Resolved', 'wpml-troubleshooting' ),
      /* translators: Heading of a count on the address-cache block of WPML → Support: the addresses the cache has not worked out yet. Adjective, not a verb. */
      'unresolved'   => __( 'Unresolved', 'wpml-troubleshooting' ),
      /* translators: Heading of a count on the address-cache block of WPML → Support: the addresses still waiting to be processed. Adjective, not a verb. */
      'pending'      => __( 'Pending', 'wpml-troubleshooting' ),
      /* translators: Heading of a count on the address-cache block of WPML → Support: the addresses the cache could not process. Adjective, not a verb. */
      'failedCount'  => __( 'Failed', 'wpml-troubleshooting' ),
      'oldest'       => __( 'Oldest pending item', 'wpml-troubleshooting' ),
      'lastRun'      => __( 'Last worker run', 'wpml-troubleshooting' ),
      'cronDisabled' => __( 'WP-Cron is disabled. Keep this page open to process cache batches, or configure a system cron runner.', 'wpml-troubleshooting' ),
      'stalled'      => __( 'URL resolution cache processing appears to be stalled.', 'wpml-troubleshooting' ),
      /* translators: Message on WPML → Support. "It" is the database table. */
      'tableMissing' => __( 'The URL resolution cache table is missing. It is created automatically on the next admin or front-end request.', 'wpml-troubleshooting' ),
      'lastError'    => __( 'Last worker error', 'wpml-troubleshooting' ),
      /* translators: An em dash shown in place of a value on WPML → Support when there is nothing to show. Leave it as it is. */
      'none'         => __( '—', 'wpml-troubleshooting' ),
    );
    ?>
      <section id="url-resolution-cache" class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5 wpml:mb-5 wpml:scroll-mt-8">
        <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
          <?php esc_html_e( 'Clear URL translations cache', 'wpml-troubleshooting' ); ?>
        </h2>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-2">
          <?php esc_html_e( 'WPML caches how individual front-end URLs resolve to translated content. Rebuild it after routing or translation changes if links still use an older result.', 'wpml-troubleshooting' ); ?>
        </p>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-4">
          <?php
          printf(
            /* translators: %s is the site name followed by its numeric ID. */
            esc_html__( 'Showing %s. Each site in a multisite network keeps its own cache.', 'wpml-troubleshooting' ),
            '<strong>' . esc_html( get_bloginfo( 'name' ) ) . ' (#' . esc_html( (string) get_current_blog_id() ) . ')</strong>'
          );
          ?>
        </p>

        <div id="wpml-url-cache-alerts"></div>

        <div id="wpml-url-cache-status" class="wpml:text-sm wpml:text-gray-500 wpml:mb-4">
          <?php echo esc_html( $i18n['loading'] ); ?>
        </div>

        <div id="wpml-url-cache-progress" class="wpml:mb-4" hidden>
          <div class="wpml:w-full wpml:bg-gray-200 wpml:rounded wpml:h-2">
            <div id="wpml-url-cache-bar" class="wpml:bg-blue wpml:h-2 wpml:rounded" style="width:0%"></div>
          </div>
          <div id="wpml-url-cache-progress-label" class="wpml:text-xs wpml:text-gray-500 wpml:mt-1"></div>
        </div>

        <button type="button"
          id="wpml-url-cache-rebuild"
          class="wpml-button base-btn wpml:text-sm wpml:disabled:opacity-50 wpml:disabled:cursor-not-allowed">
          <?php echo esc_html( $i18n['rebuild'] ); ?>
        </button>
      </section>

      <script>
      (function () {
        var ajaxUrl   = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
        var nonces    = <?php echo wp_json_encode( $nonces ); ?>;
        var endpoints = <?php echo wp_json_encode( $endpoints ); ?>;
        var i18n      = <?php echo wp_json_encode( $i18n ); ?>;

        var statusEl   = document.getElementById('wpml-url-cache-status');
        var alertsEl   = document.getElementById('wpml-url-cache-alerts');
        var progressEl = document.getElementById('wpml-url-cache-progress');
        var barEl      = document.getElementById('wpml-url-cache-bar');
        var labelEl    = document.getElementById('wpml-url-cache-progress-label');
        var rebuildBtn = document.getElementById('wpml-url-cache-rebuild');

        var busy           = false;
        var rebuildStarted = false;
        var timer          = null;

        // `wpml_action` central dispatcher: the nonce is created against the
        // endpoint's own class name.
        function call(which) {
          var body = new URLSearchParams();
          body.append('action', 'wpml_action');
          body.append('endpoint', endpoints[which]);
          body.append('nonce', nonces[which] || '');
          body.append('data', '{}');

          return fetch(ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
          }).then(function (r) {
            if (!r.ok) { throw new Error('http ' + r.status); }
            return r.json();
          }).then(function (payload) {
            if (!payload || !payload.success) {
              throw new Error(typeof payload?.data === 'string' && payload.data ? payload.data : i18n.failed);
            }
            return payload.data || {};
          });
        }

        function num(value) {
          var parsed = Number(value);
          return isFinite(parsed) ? parsed : 0;
        }

        function text(value) {
          return value === null || value === undefined || value === '' ? i18n.none : String(value);
        }

        function escapeHtml(value) {
          var div = document.createElement('div');
          div.textContent = String(value);
          return div.innerHTML;
        }

        function alertBox(kind, message) {
          var tone = kind === 'error'
            ? 'wpml:bg-red-50 wpml:border-red-200 wpml:text-red-800'
            : (kind === 'success'
              ? 'wpml:bg-green-50 wpml:border-green-200 wpml:text-green-800'
              : 'wpml:bg-amber-50 wpml:border-amber-200 wpml:text-amber-800');

          return '<div class="wpml:border wpml:rounded wpml:px-3 wpml:py-2 wpml:text-xs wpml:mb-3 ' + tone + '">' +
            escapeHtml(message) + '</div>';
        }

        function item(label, value) {
          return '<div class="wpml:mr-8 wpml:mb-3 wpml:min-w-[130px]">' +
            '<div class="wpml:text-xs wpml:text-gray-500">' + escapeHtml(label) + '</div>' +
            '<div class="wpml:text-gray-900">' + escapeHtml(value) + '</div>' +
            '</div>';
        }

        function render(status) {
          var counts   = status.counts || {};
          var rebuild  = status.rebuild || {};
          var warnings = status.warnings || {};

          statusEl.innerHTML = '<div class="wpml:flex wpml:flex-wrap">' +
            item(i18n.generation, text(status.generation)) +
            item(i18n.resolved, num(counts.positive)) +
            item(i18n.unresolved, num(counts.negative)) +
            item(i18n.pending, num(counts.pending)) +
            item(i18n.failedCount, num(counts.failed)) +
            item(i18n.oldest, text(status.oldest_pending)) +
            item(i18n.lastRun, text(status.last_run)) +
            '</div>';

          var alerts = '';
          if (warnings.table_missing) { alerts += alertBox('warning', i18n.tableMissing); }
          if (warnings.cron_disabled) { alerts += alertBox('warning', i18n.cronDisabled); }
          if (warnings.stalled) { alerts += alertBox('warning', i18n.stalled); }
          if (status.last_error) { alerts += alertBox('error', i18n.lastError + ': ' + status.last_error); }
          if (rebuildStarted && !rebuild.active) { alerts += alertBox('success', i18n.complete); }
          alertsEl.innerHTML = alerts;

          if (rebuild.active) {
            progressEl.hidden = false;
            barEl.style.width = Math.max(0, Math.min(100, num(rebuild.percent))) + '%';
            labelEl.textContent = num(rebuild.total) > 0
              ? num(rebuild.processed) + ' / ' + num(rebuild.total)
              : i18n.preparing;
          } else {
            progressEl.hidden = true;
          }

          rebuildBtn.disabled = !!rebuild.active || busy;
          rebuildBtn.textContent = rebuild.active ? i18n.rebuilding : i18n.rebuild;

          schedule(status);
        }

        // Keep draining batches while the page is open. WP-Cron does this on a
        // healthy site; this is the fallback when it is disabled or blocked.
        function schedule(status) {
          if (timer) { clearTimeout(timer); timer = null; }

          var counts  = status.counts || {};
          var rebuild = status.rebuild || {};
          var more    = !!rebuild.active || num(counts.pending) > 0;

          if (!more || busy) { return; }

          timer = setTimeout(function () {
            run('process');
          }, 1000);
        }

        function fail(message) {
          alertsEl.innerHTML = alertBox('error', message || i18n.failed);
          statusEl.textContent = '';
          rebuildBtn.disabled = false;
          rebuildBtn.textContent = i18n.rebuild;
        }

        function run(which) {
          if (busy) { return; }
          busy = true;

          call(which).then(function (status) {
            busy = false;
            render(status);
          }).catch(function (error) {
            busy = false;
            fail(error && error.message);
          });
        }

        rebuildBtn.addEventListener('click', function () {
          if (!window.confirm(i18n.confirm)) { return; }
          rebuildStarted = true;
          rebuildBtn.disabled = true;
          rebuildBtn.textContent = i18n.working;
          run('rebuild');
        });

        run('status');
      })();
      </script>
    <?php
  }


}
