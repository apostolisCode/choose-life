<?php

namespace WPML\Troubleshooting\Tool;

use WPML\UserInterface\Web\Core\SharedKernel\Config\PageRenderInterface;


class DatabaseStringsController implements PageRenderInterface {


  public function render() {
    $nonces = array(
      'ghost_clean'                 => wp_create_nonce( 'ghost_clean' ),
      'icl_fix_collation'           => wp_create_nonce( 'icl_fix_collation' ),
      'optimize_db_tables_endpoint' => wp_create_nonce( 'WPML\Troubleshooting\Endpoints\OptimizeDbTables\Endpoint' ),
      'wpml_st_troubleshooting'     => wp_create_nonce( 'wpml-st-troubleshooting' ),
      'fix_tables_collation'        => wp_create_nonce( 'fix_tables_collation' ),
      'update_term_names'           => wp_create_nonce( 'update_term_names_nonce' ),
      'fix_tp_id'                   => wp_create_nonce( 'wpml-fix-translation-jobs-tp-id' ),
    );
    $check_string_url = admin_url( 'admin.php?page=wpml-string-translation/menu/string-translation.php&troubleshooting=1&from=support' );
    $suffixed_terms   = $this->collectSuffixedTerms();
    ?>
      <h1 class="wpml:text-2xl wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
        <?php esc_html_e( 'Database & strings maintenance', 'wpml-troubleshooting' ); ?>
      </h1>
      <p class="wpml:text-sm wpml:text-gray-500 wpml:mb-6">
        <?php esc_html_e( "Direct operations on WPML's database tables and string-translation storage.", 'wpml-troubleshooting' ); ?>
      </p>

      <div class="wpml-danger-box wpml:p-4 wpml:mb-6 wpml:flex wpml:items-start wpml:gap-2">
        <svg class="wpml:w-5 wpml:h-5 wpml:text-red-600 wpml:shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.93 4.93a10 10 0 1114.14 14.14 10 10 0 01-14.14-14.14z"/>
        </svg>
        <div>
          <p class="wpml:text-sm wpml:font-semibold wpml:text-red-900 wpml:mb-0.5">
            <?php esc_html_e( 'These tools can permanently delete or corrupt translation data', 'wpml-troubleshooting' ); ?>
          </p>
          <p class="wpml:text-xs wpml:text-red-900/80">
            <?php
            printf(
              /* translators: %s renders the literal "Make a full database backup first" in bold. */
              esc_html__( 'Only run when WPML Support has reviewed your situation and asked you to. %s WPML will not be able to recover translations lost here.', 'wpml-troubleshooting' ),
              '<strong>' . esc_html__( 'Make a full database backup first.', 'wpml-troubleshooting' ) . '</strong>'
            );
            ?>
          </p>
        </div>
      </div>

      <?php
      if ( \SitePress_Setup::setup_complete() ) {
        do_action( 'wpml_troubleshooting_after_setup_complete_cleanup_begin' );
      }

      $this->renderArmedSection(
        'db-optimize',
        __( 'Database Tables Optimization', 'wpml-troubleshooting' ),
        sprintf(
          /* translators: Description of a Support tool on WPML → Support. %1$s, %2$s and %3$s: the names of three database tables, each shown in a code box. %4$s: a link, already wrapped in its tags, to the documentation for this tool. */
          esc_html__( 'Compresses WPML\'s translation tables (%1$s, %2$s, %3$s). Applies only to sites created before WPML 4.8, and only while the tables still hold redundant data. %4$s', 'wpml-troubleshooting' ),
          '<code class="wpml:text-[11px] wpml:bg-gray-100 wpml:px-1 wpml:rounded">wp_icl_translate</code>',
          '<code class="wpml:text-[11px] wpml:bg-gray-100 wpml:px-1 wpml:rounded">wp_icl_translate_job</code>',
          '<code class="wpml:text-[11px] wpml:bg-gray-100 wpml:px-1 wpml:rounded">wp_icl_translation_status</code>',
          '<a href="' . esc_url( self::optimizationDocUrl() ) . '" target="_blank" class="wpml:hover:underline wpml:font-medium">'
            /* translators: Link on the Database Tables Optimization tool, opening the page that explains what the tool does. */
            . esc_html__( 'Learn how this works', 'wpml-troubleshooting' ) . ' ↗</a>'
        ),
        __( 'Optimize translation tables', 'wpml-troubleshooting' ),
        array()
      );

      $this->renderArmedSection(
        'strings-cleanup',
        __( 'Cleanup and optimize string tables', 'wpml-troubleshooting' ),
        \wpml_bold_names( sprintf(
          /* translators: %s renders the literal ".mo" file extension. */
          __( 'Drops the <b>String Translation</b> cache and re-indexes the strings tables. Applies only to sites originally translated before WPML 4.3, and only when every custom %s file is already generated.', 'wpml-troubleshooting' ),
          '<code class="wpml:text-[11px] wpml:bg-gray-100 wpml:px-1 wpml:rounded">.mo</code>'
        ), [
          'code' => [ 'class' => [] ],
        ] ),
        __( 'Clean up and optimize strings', 'wpml-troubleshooting' ),
        array(
          'data-wpml-mode'      => 'wp_ajax',
          'data-wpml-action'    => 'wpml_st_troubleshooting_cleanup',
          'data-wpml-nonce-key' => 'wpml_st_troubleshooting',
        )
      );

      $this->renderArmedSection(
        'ghosts',
        __( 'Remove ghost entries from translation tables', 'wpml-troubleshooting' ),
        sprintf(
          /* translators: %s renders the literal `wp_icl_translations`. */
          esc_html__( 'Deletes rows in %s that point to posts or terms that no longer exist. Only use when support has confirmed that orphaned rows are your actual problem.', 'wpml-troubleshooting' ),
          '<code class="wpml:text-[11px] wpml:bg-gray-100 wpml:px-1 wpml:rounded">wp_icl_translations</code>'
        ),
        __( 'Remove ghost entries', 'wpml-troubleshooting' ),
        array(
          'data-wpml-mode'      => 'dispatcher',
          'data-wpml-action'    => 'ghost_clean',
          'data-wpml-nonce-key' => 'ghost_clean',
        )
      );
      ?>

      <!-- Show MO files dialog (toggle / one-shot, non-armed) -->
      <section id="mo-dialog" class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5 wpml:mb-5 wpml:scroll-mt-8">
        <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
          <?php esc_html_e( 'Show custom MO files pre-generation dialog', 'wpml-troubleshooting' ); ?>
        </h2>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-4">
          <?php
          printf(
            /* translators: Message on WPML → Support. %1$s: the menu path "WPML → Translations → Strings", in bold, %2$s: the file extension ".mo" in a code box; it appears twice. */
            esc_html__( 'This action prepares the custom %2$s files dialog to appear the next time you open %1$s. Use that dialog to choose which custom %2$s files to regenerate.', 'wpml-troubleshooting' ),
            '<strong>' . esc_html__( 'WPML → Translations → Strings', 'wpml-troubleshooting' ) . '</strong>',
            '<code class="wpml:text-[11px] wpml:bg-gray-100 wpml:px-1 wpml:rounded">.mo</code>'
          );
          ?>
        </p>
        <button id="btn-mo-dialog" type="button"
          data-wpml-mode="wp_ajax"
          data-wpml-action="wpml_st_mo_generate_show_dialog"
          data-wpml-nonce-key="wpml_st_troubleshooting"
          class="wpml-button base-btn wpml:text-sm wpml:disabled:opacity-50 wpml:disabled:cursor-not-allowed">
          <?php esc_html_e( 'Show the dialog on the Strings tab', 'wpml-troubleshooting' ); ?>
        </button>
        <span id="status-mo-dialog" class="wpml:text-xs wpml:text-gray-500 wpml:ml-3" aria-live="polite"></span>
      </section>

      <!-- Remove language suffixes -->
      <section id="lang-suffixes" class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5 wpml:mb-5 wpml:scroll-mt-8">
        <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
          <?php esc_html_e( 'Remove language suffixes from taxonomy names', 'wpml-troubleshooting' ); ?>
        </h2>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-4">
          <?php esc_html_e( 'Strips language-code suffixes from taxonomy term names. Appears after certain legacy migrations where WPML created taxonomy duplicates with language-coded names.', 'wpml-troubleshooting' ); ?>
        </p>

        <?php if ( empty( $suffixed_terms ) ) : ?>
          <div class="wpml:rounded wpml:border wpml:border-gray-200 wpml:bg-gray-50 wpml:p-3 wpml:text-xs wpml:text-gray-600">
            <?php esc_html_e( 'No taxonomy terms with language suffixes were detected.', 'wpml-troubleshooting' ); ?>
          </div>
        <?php else : ?>
          <div class="wpml:border wpml:border-gray-200 wpml:rounded-md wpml:overflow-hidden wpml:mb-4">
            <table id="wpml-lang-suffixes-table" class="wpml:w-full wpml:text-xs">
              <thead class="wpml:bg-gray-50 wpml:border-b wpml:border-gray-200 wpml:text-left wpml:text-gray-500">
                <tr>
                  <th class="wpml:w-8 wpml:px-3 wpml:py-2"></th>
                  <th class="wpml:px-3 wpml:py-2"><?php /* translators: Column heading in the renamed-terms table on WPML → Support: the name a category or tag had before. Noun phrase, not a verb. */ esc_html_e( 'Old name', 'wpml-troubleshooting' ); ?></th>
                  <th class="wpml:px-3 wpml:py-2"><?php /* translators: Column heading in the renamed-terms table on WPML → Support: the name that category or tag has now. Noun phrase, not a verb. */ esc_html_e( 'Updated name', 'wpml-troubleshooting' ); ?></th>
                  <th class="wpml:px-3 wpml:py-2"><?php /* translators: Column heading in the renamed-terms table on WPML → Support: which categories or tags the change touches. */ esc_html_e( 'Affected taxonomies', 'wpml-troubleshooting' ); ?></th>
                </tr>
              </thead>
              <tbody class="wpml:divide-y wpml:divide-gray-100">
                <?php
                foreach ( $suffixed_terms as $term_id => $term ) :
                  $stripped = \WPML_Troubleshooting_Terms_Menu::strip_language_suffix( $term['name'] );
                  ?>
                  <tr class="lang-suffix-row">
                    <td class="wpml:px-3 wpml:py-2">
                      <input type="checkbox" class="lang-suffix-cb wpml-checkbox-native"
                        data-term-id="<?php echo esc_attr( (string) $term_id ); ?>"
                        data-new-name="<?php echo esc_attr( $stripped ); ?>"
                        checked/>
                    </td>
                    <td class="wpml:px-3 wpml:py-2 wpml:text-gray-700"><?php echo esc_html( $term['name'] ); ?></td>
                    <td class="wpml:px-3 wpml:py-2 wpml:text-gray-900"><?php echo esc_html( $stripped ); ?></td>
                    <td class="wpml:px-3 wpml:py-2 wpml:text-gray-500"><?php echo esc_html( implode( ', ', $term['taxonomies'] ) ); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <button id="btn-lang-suffixes" type="button"
            class="wpml-button base-btn wpml-button--danger wpml:text-sm">
            <?php esc_html_e( 'Update term names', 'wpml-troubleshooting' ); ?>
          </button>
          <span id="status-lang-suffixes" class="wpml:text-xs wpml:text-gray-500 wpml:ml-3" aria-live="polite"></span>
        <?php endif; ?>
      </section>

      <!-- Check for string issues (navigate-only) -->
      <section id="check-string-issues" class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5 wpml:mb-5 wpml:scroll-mt-8">
        <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
          <?php esc_html_e( 'Check for string issues', 'wpml-troubleshooting' ); ?>
        </h2>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-4">
          <?php esc_html_e( "Scans WPML's string-translation tables for inconsistencies — duplicate string IDs, broken context links, mismatched languages — and reports what it finds. Read-only: nothing is changed until you act on the report.", 'wpml-troubleshooting' ); ?>
        </p>
        <a href="<?php echo esc_url( $check_string_url ); ?>" class="wpml-button base-btn wpml:inline-block wpml:text-sm">
          <?php /* translators: Link text on WPML → Support that starts a database check. Verb phrase, imperative. */ esc_html_e( 'Run check', 'wpml-troubleshooting' ); ?>
        </a>
      </section>

      <?php
      $this->renderArmedSection(
        'fix-element-type-collation',
        __( 'Fix element_type collation', 'wpml-troubleshooting' ),
        sprintf(
          /* translators: Description of a Support tool on WPML → Support. %1$s: the name of a database column, in a code box, %2$s: the name of a database table, in a code box. "Illegal mix of collations" is a database error message and stays in English. */
          esc_html__( 'Aligns the collation of the %1$s column in %2$s with the rest of the database. Mismatched collation here causes "Illegal mix of collations" errors when WPML joins translation rows with WordPress posts or terms.', 'wpml-troubleshooting' ),
          '<code class="wpml:text-[11px] wpml:bg-gray-100 wpml:px-1 wpml:rounded">element_type</code>',
          '<code class="wpml:text-[11px] wpml:bg-gray-100 wpml:px-1 wpml:rounded">wp_icl_translations</code>'
        ),
        __( 'Fix element_type collation', 'wpml-troubleshooting' ),
        array(
          'data-wpml-mode'      => 'dispatcher',
          'data-wpml-action'    => 'icl_fix_collation',
          'data-wpml-nonce-key' => 'icl_fix_collation',
        )
      );

      $this->renderArmedSection(
        'fix-tables-collation',
        __( 'Fix WPML tables collation', 'wpml-troubleshooting' ),
        esc_html__( "Converts every WPML table to match the collation of WordPress's core tables. Use when string searches return wrong results, or when joins between WPML tables and WordPress tables raise collation errors. Broader than \"Fix element_type collation\" — touches all WPML tables, not just one column.", 'wpml-troubleshooting' ),
        __( 'Fix WPML tables collation', 'wpml-troubleshooting' ),
        array(
          'data-wpml-mode'      => 'wp_ajax',
          'data-wpml-action'    => 'fix_tables_collation',
          'data-wpml-nonce-key' => 'fix_tables_collation',
        )
      );
      ?>

      <!-- Fix tp_id field -->
      <section id="fix-tp-id" class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5 wpml:mb-5 wpml:scroll-mt-8">
        <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
          <?php esc_html_e( 'Fix tp_id field', 'wpml-troubleshooting' ); ?>
        </h2>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-4">
          <?php
          printf(
            /* translators: %s is the literal `tp_id`. */
            esc_html__( "Repairs the %s column on the listed translation jobs and resets each job's status to in-progress. After running it, manually re-sync the translation status and download the translations for the affected jobs.", 'wpml-troubleshooting' ),
            '<code class="wpml:text-[11px] wpml:bg-gray-100 wpml:px-1 wpml:rounded">tp_id</code>'
          );
          ?>
        </p>
        <div class="wpml:space-y-3">
          <label class="wpml:block wpml:text-xs wpml:font-medium wpml:text-gray-700">
            <?php esc_html_e( 'Translation job IDs (rid)', 'wpml-troubleshooting' ); ?>
            <input id="fix-tp-id-jobs" type="text" placeholder="e.g. 12, 47, 113"
              class="wpml:mt-1 wpml:w-full wpml:border wpml:border-gray-300 wpml:rounded wpml:px-3 wpml:py-1.5 wpml:text-sm wpml:focus:outline-none wpml:focus:ring-2 wpml:focus:ring-blue/30 wpml:focus:border-blue"/>
            <span class="wpml:block wpml:mt-1 wpml:text-[11px] wpml:text-gray-500">
              <?php esc_html_e( 'Comma-separated list of remote job IDs.', 'wpml-troubleshooting' ); ?>
            </span>
          </label>
          <div class="wpml-danger-box wpml:p-3">
            <label class="wpml:flex wpml:items-center wpml:gap-2 wpml:text-sm wpml:text-red-900 wpml:cursor-pointer">
              <input type="checkbox" class="wpml-support-arm wpml-checkbox-native" data-arm="btn-fix-tp-id"/>
              <?php esc_html_e( 'I understand and want to proceed', 'wpml-troubleshooting' ); ?>
            </label>
          </div>
          <button id="btn-fix-tp-id" type="button" disabled
            class="wpml-button base-btn wpml-button--danger wpml:text-sm">
            <?php esc_html_e( 'Fix tp_id field', 'wpml-troubleshooting' ); ?>
          </button>
          <span id="status-fix-tp-id" class="wpml:text-xs wpml:text-gray-500 wpml:ml-3" aria-live="polite"></span>
        </div>
      </section>

      <?php
      if ( \SitePress_Setup::setup_complete() ) {
        do_action( 'wpml_troubleshooting_after_setup_complete_cleanup_end' );
      }
      ?>

      <script>
      (function () {
        var nonces = <?php echo wp_json_encode( $nonces ); ?>;
        // Built server-side: a root-relative '/wp-admin/…' would resolve against the
        // domain, not the WordPress root, and 404 on subdirectory installs (wpmldev-7682).
        var ajaxUrl  = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
        var adminUrl = <?php echo wp_json_encode( admin_url( 'admin.php' ) ); ?>;

        document.querySelectorAll('.wpml-support-arm').forEach(function (cb) {
          cb.addEventListener('change', function () {
            var btn = document.getElementById(cb.getAttribute('data-arm'));
            if (btn) { btn.disabled = !cb.checked; }
          });
        });

        function postDispatcher(action, nonce) {
          var url = adminUrl + '?debug_action=' + encodeURIComponent(action) +
                    '&nonce=' + encodeURIComponent(nonce);
          return fetch(url, { method: 'POST', credentials: 'same-origin' });
        }

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

        function postWpmlAction(endpoint, nonce, dataJson) {
          var body = new URLSearchParams();
          body.append('action', 'wpml_action');
          body.append('endpoint', endpoint);
          body.append('nonce', nonce);
          body.append('data', dataJson || '{}');
          return fetch(ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
          });
        }

        function withFeedback(btn, fn) {
          var section = btn.closest('section');
          var status  = section ? section.querySelector('[id^="status-"]') : null;
          btn.disabled = true;
          if (status) { status.textContent = '<?php echo esc_js( /* translators: Status shown next to a button on WPML → Support while its action is running. */ __( 'Working…', 'wpml-troubleshooting' ) ); ?>'; }

          fn().then(function (r) {
            if (!r.ok) { throw new Error('http ' + r.status); }
            if (status) { status.textContent = '<?php echo esc_js( /* translators: Status shown next to a button on WPML → Support once its action has finished. */ __( 'Done.', 'wpml-troubleshooting' ) ); ?>'; }
            // Leave the button disabled after a successful run so the user
            // doesn't repeat the destructive action accidentally.
          }).catch(function () {
            if (status) { status.textContent = '<?php echo esc_js( __( 'Something went wrong. Please retry.', 'wpml-troubleshooting' ) ); ?>'; }
            btn.disabled = false;
          });
        }

        document.querySelectorAll('button[data-wpml-mode]').forEach(function (btn) {
          btn.addEventListener('click', function () {
            var mode      = btn.getAttribute('data-wpml-mode');
            var nonceKey  = btn.getAttribute('data-wpml-nonce-key');
            var nonce     = nonces[nonceKey];

            if (mode === 'dispatcher') {
              var action = btn.getAttribute('data-wpml-action');
              withFeedback(btn, function () { return postDispatcher(action, nonce); });
            } else if (mode === 'wp_ajax') {
              var ajaxAction = btn.getAttribute('data-wpml-action');
              withFeedback(btn, function () { return postWpAjax(ajaxAction, nonce); });
            } else if (mode === 'wpml_action') {
              var endpoint = btn.getAttribute('data-wpml-endpoint');
              var dataJson = btn.getAttribute('data-wpml-data-json') || '{}';
              withFeedback(btn, function () { return postWpmlAction(endpoint, nonce, dataJson); });
            }
          });
        });

        // Fix tp_id — bespoke contract: comma-separated job ids text input.
        var fixTpIdBtn = document.getElementById('btn-fix-tp-id');
        if (fixTpIdBtn) {
          var fixTpIdInput  = document.getElementById('fix-tp-id-jobs');
          var fixTpIdStatus = document.getElementById('status-fix-tp-id');
          fixTpIdBtn.addEventListener('click', function () {
            var jobIds = (fixTpIdInput.value || '').trim();
            if (jobIds === '') {
              if (fixTpIdStatus) { fixTpIdStatus.textContent = '<?php echo esc_js( __( 'Enter at least one job ID.', 'wpml-troubleshooting' ) ); ?>'; }
              return;
            }
            fixTpIdBtn.disabled = true;
            if (fixTpIdStatus) { fixTpIdStatus.textContent = '<?php echo esc_js( /* translators: Status shown next to a button on WPML → Support while its action is running. */ __( 'Working…', 'wpml-troubleshooting' ) ); ?>'; }

            var body = new URLSearchParams();
            body.append('action', 'wpml-fix-translation-jobs-tp-id');
            body.append('nonce', nonces.fix_tp_id);
            body.append('job_ids', jobIds);

            fetch(ajaxUrl, {
              method: 'POST',
              credentials: 'same-origin',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: body.toString()
            }).then(function (r) {
              if (!r.ok) { throw new Error('http ' + r.status); }
              return r.json();
            }).then(function (json) {
              if (!json || !json.success) { throw new Error('rejected'); }
              if (fixTpIdStatus) { fixTpIdStatus.textContent = '<?php echo esc_js( __( 'Done. Re-sync the affected jobs from Translation Management.', 'wpml-troubleshooting' ) ); ?>'; }
            }).catch(function () {
              if (fixTpIdStatus) { fixTpIdStatus.textContent = '<?php echo esc_js( __( 'Something went wrong. Please retry.', 'wpml-troubleshooting' ) ); ?>'; }
              fixTpIdBtn.disabled = false;
            });
          });
        }

        // Optimize translation tables — bespoke contract (wpmldev-8508). The
        // generic single-shot `data-wpml-mode="wpml_action"` dispatch used to
        // send only the fixed {"migrationType":"previous-state",
        // "isInitialRequest":true} payload and call it "Done." after sizing
        // the phase and draining nothing — the retired React client
        // (useOptimizeDbTables.js, pre-wpmldev-8508 move) drove the phase to
        // completion instead: an isInitialRequest:true call returns the total
        // to process, then isInitialRequest:false calls repeat while the
        // response is still positive. This mirrors that loop for both phases
        // the button advertises (previous-state, translation-elements), then
        // sends the same finishing truncate-translation-package call, and
        // reports the rows actually drained — or that there was nothing to do.
        var optimizeBtn = document.getElementById('btn-db-optimize');
        if (optimizeBtn) {
          var optimizeStatus       = document.getElementById('status-db-optimize');
          var optimizeEndpoint     = 'WPML\\Troubleshooting\\Endpoints\\OptimizeDbTables\\Endpoint';
          var optimizeNonce        = nonces.optimize_db_tables_endpoint;
          var optimizeNothingToDo  = <?php echo wp_json_encode( __( 'Nothing to do — the tables were already optimized.', 'wpml-troubleshooting' ) ); ?>;
          var optimizeDoneTemplate = <?php echo wp_json_encode( /* translators: %d is replaced client-side with the number of rows the Optimize translation tables button drained. */ __( 'Optimized %d row(s).', 'wpml-troubleshooting' ) ); ?>;

          var optimizeRequest = function (payload) {
            return postWpmlAction(optimizeEndpoint, optimizeNonce, JSON.stringify(payload)).then(function (r) {
              if (!r.ok) { throw new Error('http ' + r.status); }
              return r.json();
            }).then(function (json) {
              if (!json || json.success !== true) { throw new Error('refused'); }
              return Number(json.data) || 0;
            });
          };

          // Drives one migration phase to completion. Resolves with the
          // number of rows the phase sized initially (0 = nothing to do for
          // this phase), mirroring the retired client's per-phase counter.
          var runPhaseToCompletion = function (migrationType) {
            return optimizeRequest({ migrationType: migrationType, isInitialRequest: true }).then(function (total) {
              if (total <= 0) { return 0; }

              var drainNext = function () {
                return optimizeRequest({ migrationType: migrationType, isInitialRequest: false }).then(function (remaining) {
                  return remaining > 0 ? drainNext() : total;
                });
              };

              return drainNext();
            });
          };

          optimizeBtn.addEventListener('click', function () {
            optimizeBtn.disabled = true;
            if (optimizeStatus) { optimizeStatus.textContent = '<?php echo esc_js( __( 'Working…', 'wpml-troubleshooting' ) ); ?>'; }

            runPhaseToCompletion('previous-state').then(function (previousStateTotal) {
              return runPhaseToCompletion('translation-elements').then(function (translationElementsTotal) {
                return previousStateTotal + translationElementsTotal;
              });
            }).then(function (totalProcessed) {
              // The finishing call the old client always sent once both phases
              // were drained; its own result carries nothing this button reports.
              return optimizeRequest({ migrationType: 'truncate-translation-package' })
                .catch(function () { return 0; })
                .then(function () { return totalProcessed; });
            }).then(function (totalProcessed) {
              if (optimizeStatus) {
                optimizeStatus.textContent = totalProcessed > 0
                  ? optimizeDoneTemplate.replace('%d', String(totalProcessed))
                  : optimizeNothingToDo;
              }
            }).catch(function () {
              if (optimizeStatus) { optimizeStatus.textContent = '<?php echo esc_js( __( 'Something went wrong. Please retry.', 'wpml-troubleshooting' ) ); ?>'; }
              optimizeBtn.disabled = false;
            });
          });
        }

        // Language-suffix table — bespoke contract: collect
        // {term_id: new_name} from checked rows, POST as JSON.
        var langSuffixBtn = document.getElementById('btn-lang-suffixes');
        if (langSuffixBtn) {
          var langSuffixStatus = document.getElementById('status-lang-suffixes');
          langSuffixBtn.addEventListener('click', function () {
            var selected = {};
            document.querySelectorAll('.lang-suffix-cb:checked').forEach(function (cb) {
              var id = cb.getAttribute('data-term-id');
              var newName = cb.getAttribute('data-new-name');
              if (id && newName) { selected[id] = newName; }
            });
            if (Object.keys(selected).length === 0) {
              if (langSuffixStatus) { langSuffixStatus.textContent = '<?php echo esc_js( __( 'Select at least one term.', 'wpml-troubleshooting' ) ); ?>'; }
              return;
            }
            langSuffixBtn.disabled = true;
            if (langSuffixStatus) { langSuffixStatus.textContent = '<?php echo esc_js( /* translators: Status shown next to a button on WPML → Support while the change is being written. */ __( 'Updating…', 'wpml-troubleshooting' ) ); ?>'; }

            var body = new URLSearchParams();
            body.append('action', 'wpml_update_term_names_troubleshoot');
            body.append('_icl_nonce', nonces.update_term_names);
            body.append('terms', JSON.stringify(selected));

            fetch(ajaxUrl, {
              method: 'POST',
              credentials: 'same-origin',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: body.toString()
            }).then(function (r) {
              if (!r.ok) { throw new Error('http ' + r.status); }
              return r.json();
            }).then(function (json) {
              if (!json || !json.success) { throw new Error('rejected'); }
              (json.data || []).forEach(function (id) {
                var cb = document.querySelector('.lang-suffix-cb[data-term-id="' + id + '"]');
                if (cb) { cb.closest('tr').remove(); }
              });
              if (document.querySelectorAll('.lang-suffix-row').length === 0) {
                var tableWrap = document.getElementById('wpml-lang-suffixes-table');
                if (tableWrap) { tableWrap.parentNode.remove(); }
                langSuffixBtn.remove();
                if (langSuffixStatus) { langSuffixStatus.textContent = '<?php echo esc_js( __( 'All term names updated.', 'wpml-troubleshooting' ) ); ?>'; }
              } else {
                if (langSuffixStatus) { langSuffixStatus.textContent = '<?php echo esc_js( /* translators: Status shown next to a button on WPML → Support once its action has finished. */ __( 'Done.', 'wpml-troubleshooting' ) ); ?>'; }
                langSuffixBtn.disabled = false;
              }
            }).catch(function () {
              if (langSuffixStatus) { langSuffixStatus.textContent = '<?php echo esc_js( __( 'Something went wrong. Please retry.', 'wpml-troubleshooting' ) ); ?>'; }
              langSuffixBtn.disabled = false;
            });
          });
        }
      })();
      </script>
      <?php
  }


  private function renderArmedSection(
    string $id,
    string $title,
    string $descriptionHtml,
    string $buttonLabel,
    array $buttonAttrs
  ): void {
    $button_id = 'btn-' . $id;
    ?>
    <section id="<?php echo esc_attr( $id ); ?>" class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5 wpml:mb-5 wpml:scroll-mt-8">
      <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1"><?php echo esc_html( $title ); ?></h2>
      <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-4"><?php echo $descriptionHtml;  ?></p>
      <div class="wpml-danger-box wpml:p-3 wpml:mb-3">
        <label class="wpml:flex wpml:items-center wpml:gap-2 wpml:text-sm wpml:text-red-900 wpml:cursor-pointer">
          <input type="checkbox" class="wpml-support-arm wpml-checkbox-native" data-arm="<?php echo esc_attr( $button_id ); ?>"/>
          <?php esc_html_e( 'I understand and want to proceed', 'wpml-troubleshooting' ); ?>
        </label>
      </div>
      <button id="<?php echo esc_attr( $button_id ); ?>" type="button" disabled
        <?php foreach ( $buttonAttrs as $attr => $value ) : ?>
          <?php echo esc_attr( $attr ); ?>="<?php echo esc_attr( $value ); ?>"
        <?php endforeach; ?>
        class="wpml-button base-btn wpml-button--danger wpml:text-sm">
        <?php echo esc_html( $buttonLabel ); ?>
      </button>
      <span id="status-<?php echo esc_attr( $id ); ?>" class="wpml:text-xs wpml:text-gray-500 wpml:ml-3" aria-live="polite"></span>
    </section>
    <?php
  }


  private function collectSuffixedTerms(): array {
    if (
      ! class_exists( '\WPML_Terms_Translations' )
      || ! method_exists( '\WPML_Terms_Translations', 'get_all_terms_with_language_suffix' )
      || ! class_exists( '\WPML_Troubleshooting_Terms_Menu' )
    ) {
      return array();
    }
    $terms = \WPML_Terms_Translations::get_all_terms_with_language_suffix();
    return $terms;
  }


  private static function optimizationDocUrl() {
    $url = 'https://wpml.org/documentation/support/wpml-tables/optimizing-wpml-database-tables/';

    if ( class_exists( '\WPML\OutboundLinks\OutboundLinks' ) ) {
      return \WPML\OutboundLinks\OutboundLinks::to(
        $url,
        [ 'medium' => 'support', 'campaign' => 'troubleshooting' ]
      );
    }

    return $url;
  }

}
