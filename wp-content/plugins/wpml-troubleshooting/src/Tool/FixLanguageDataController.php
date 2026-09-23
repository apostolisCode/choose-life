<?php

namespace WPML\Troubleshooting\Tool;

use WPML\UserInterface\Web\Core\SharedKernel\Config\PageRenderInterface;


class FixLanguageDataController implements PageRenderInterface {

    private $urlResolutionCache;


  public function __construct( UrlResolutionCacheSection $urlResolutionCache ) {
    $this->urlResolutionCache = $urlResolutionCache;
  }


  public function render() {
    $nonces = array(
      'icl_ts_add_missing_language'    => wp_create_nonce( 'icl_ts_add_missing_language' ),
      'icl_fix_terms_count'            => wp_create_nonce( 'icl_fix_terms_count' ),
      'broken_type_nonce'              => wp_create_nonce( 'broken_type_nonce' ),
      'synchronize_posts_taxonomies'   => wp_create_nonce( 'synchronize_posts_taxonomies' ),
    );
    $i18n            = array(
      /* translators: Status shown next to a button on WPML → Support once its action has finished. Adjective, not the verb "to do". */
      'done'         => __( 'Done', 'wpml-troubleshooting' ),
      /* translators: Status shown next to a button on WPML → Support while its action is running. */
      'workingTitle' => __( 'Working…', 'wpml-troubleshooting' ),
      'errorTitle'   => __( 'Something went wrong. Please retry.', 'wpml-troubleshooting' ),
      /* translators: Status shown next to a button on WPML → Support while its action is running. */
      'syncRunning'  => __( 'Running…', 'wpml-troubleshooting' ),
    );
    $translatable_post_types = $this->collectTranslatablePostTypes();
    ?>
      <h1 class="wpml:text-2xl wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
        <?php esc_html_e( 'Fix language data', 'wpml-troubleshooting' ); ?>
      </h1>
      <p class="wpml:text-sm wpml:text-gray-500 wpml:mb-6">
        <?php esc_html_e( "Repair language-related fields on content. These tools are safe to run — they don't delete anything, they only fill in or align missing information.", 'wpml-troubleshooting' ); ?>
      </p>

      <?php $this->renderUnclassifiedLanguages(); ?>

      <?php $this->renderCountryIdentityDrift(); ?>

      <!-- Set language information -->
      <section id="set-language" class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5 wpml:mb-5 wpml:scroll-mt-8">
        <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
          <?php /* translators: Name of a Support tool section: its entry in the Support search list, its heading and its button label. Verb phrase, imperative. */ esc_html_e( 'Set language information', 'wpml-troubleshooting' ); ?>
        </h2>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-4">
          <?php esc_html_e( "Assigns the site's default language to any content that was created without language metadata — typically content imported from another tool or added before WPML was active. Existing language assignments are preserved.", 'wpml-troubleshooting' ); ?>
        </p>
        <button type="button"
          data-wpml-support-action="icl_ts_add_missing_language"
          class="wpml-button base-btn wpml:text-sm wpml:disabled:opacity-50 wpml:disabled:cursor-not-allowed">
          <?php /* translators: Name of a Support tool section: its entry in the Support search list, its heading and its button label. Verb phrase, imperative. */ esc_html_e( 'Set language information', 'wpml-troubleshooting' ); ?>
        </button>
      </section>

      <!-- Synchronize posts taxonomies -->
      <section id="sync-taxonomies" class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5 wpml:mb-5 wpml:scroll-mt-8">
        <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
          <?php esc_html_e( "Synchronize posts' taxonomies", 'wpml-troubleshooting' ); ?>
        </h2>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-4">
          <?php esc_html_e( "Re-applies the original post's category and tag assignments to its translations. Fixes translations that are missing categories or tags after a migration.", 'wpml-troubleshooting' ); ?>
        </p>
        <div class="wpml:flex wpml:flex-wrap wpml:items-center wpml:gap-3">
          <?php
          ?>
          <select id="wpml-support-sync-post-type"
            class="wpml:border wpml:border-gray-300 wpml:rounded wpml:ps-3 wpml:pe-6 wpml:py-1.5 wpml:min-w-48 wpml:text-sm wpml:focus:outline-none wpml:focus:ring-2 wpml:focus:ring-blue/20"
            <?php disabled( empty( $translatable_post_types ) ); ?>>
            <?php foreach ( $translatable_post_types as $slug => $label ) : ?>
              <option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></option>
            <?php endforeach; ?>
            <?php if ( empty( $translatable_post_types ) ) : ?>
              <option value=""><?php esc_html_e( 'No translatable post types', 'wpml-troubleshooting' ); ?></option>
            <?php endif; ?>
          </select>
          <button type="button" id="wpml-support-sync-taxonomies"
            <?php disabled( empty( $translatable_post_types ) ); ?>
            class="wpml-button base-btn wpml:text-sm wpml:disabled:opacity-50 wpml:disabled:cursor-not-allowed">
            <?php /* translators: Button label on WPML → Support. Verb phrase, imperative. */ esc_html_e( 'Synchronize taxonomies', 'wpml-troubleshooting' ); ?>
          </button>
          <span id="wpml-support-sync-taxonomies-status" class="wpml:text-xs wpml:text-gray-500" aria-live="polite"></span>
        </div>
      </section>

      <!-- Fix terms count -->
      <section id="fix-terms-count" class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5 wpml:mb-5 wpml:scroll-mt-8">
        <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
          <?php esc_html_e( 'Fix terms count', 'wpml-troubleshooting' ); ?>
        </h2>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-4">
          <?php esc_html_e( 'Recalculates the post counts shown next to categories, tags, and other taxonomy terms. Run this if the numbers in your taxonomy lists look wrong — for example, a category showing 0 posts when there are translated posts assigned to it.', 'wpml-troubleshooting' ); ?>
        </p>
        <button type="button"
          data-wpml-support-action="icl_fix_terms_count"
          class="wpml-button base-btn wpml:text-sm wpml:disabled:opacity-50 wpml:disabled:cursor-not-allowed">
          <?php /* translators: Button label on WPML → Support that counts the items in each category and tag again. Verb phrase, imperative. */ esc_html_e( 'Recalculate counts', 'wpml-troubleshooting' ); ?>
        </button>
      </section>

      <!-- Fix post type assignment -->
      <section id="fix-post-type" class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5 wpml:mb-5 wpml:scroll-mt-8">
        <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
          <?php esc_html_e( 'Fix post type assignment', 'wpml-troubleshooting' ); ?>
        </h2>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-4">
          <?php esc_html_e( 'Repairs translations whose post type doesn\'t match the original — for instance, a translated product saved as a regular post. Usually happens after importing content or switching how custom post types are registered.', 'wpml-troubleshooting' ); ?>
        </p>
        <button type="button"
          data-wpml-support-action="icl_repair_broken_type_and_language_assignments"
          class="wpml-button base-btn wpml:text-sm wpml:disabled:opacity-50 wpml:disabled:cursor-not-allowed">
          <?php esc_html_e( 'Fix post types', 'wpml-troubleshooting' ); ?>
        </button>
      </section>

      <?php
      if ( UrlResolutionCacheSection::isAvailable() ) {
        $this->urlResolutionCache->render();
      }
      ?>

      <script>
      (function () {
        var nonces = <?php echo wp_json_encode( $nonces ); ?>;
        var i18n   = <?php echo wp_json_encode( $i18n ); ?>;

        // Built server-side: a root-relative '/wp-admin/…' would resolve against the
        // domain, not the WordPress root, and 404 on subdirectory installs (wpmldev-7682).
        var ajaxUrl  = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
        var adminUrl = <?php echo wp_json_encode( admin_url( 'admin.php' ) ); ?>;

        function postDispatcher(action) {
          var url = adminUrl + '?debug_action=' + encodeURIComponent(action) +
                    '&nonce=' + encodeURIComponent(nonces[action] || '');
          return fetch(url, { method: 'POST', credentials: 'same-origin' });
        }

        function postWpAjax(action, nonceKey) {
          var url = ajaxUrl + '?action=' + encodeURIComponent(action) +
                    '&icl_nonce=' + encodeURIComponent(nonces[nonceKey] || '');
          return fetch(url, { method: 'GET', credentials: 'same-origin' });
        }

        document.querySelectorAll('button[data-wpml-support-action]').forEach(function (btn) {
          btn.addEventListener('click', function () {
            var action = btn.getAttribute('data-wpml-support-action');
            if (!action) { return; }
            btn.disabled = true;
            var originalLabel = btn.textContent;
            btn.textContent = i18n.workingTitle;

            var p;
            if (action === 'icl_repair_broken_type_and_language_assignments') {
              p = postWpAjax(action, 'broken_type_nonce');
            } else {
              p = postDispatcher(action);
            }

            p.then(function (r) {
              if (!r.ok) { throw new Error('http ' + r.status); }
              btn.textContent = i18n.done;
              setTimeout(function () { btn.textContent = originalLabel; btn.disabled = false; }, 1500);
            }).catch(function () {
              btn.textContent = i18n.errorTitle;
              setTimeout(function () { btn.textContent = originalLabel; btn.disabled = false; }, 2500);
            });
          });
        });

        // Synchronize posts' taxonomies — batched POST loop.
        // `synchronize_posts_taxonomies` is dispatched on admin_init by the
        // `wpml_troubleshoot_action_load()` hook (sitepress.php:462) — it
        // reads $_POST['post_type'] + $_POST['batch_number'] and returns
        // wp_send_json_success({ batch_number, message, completed? }).
        // We loop until `completed===true`.
        var syncBtn     = document.getElementById('wpml-support-sync-taxonomies');
        var syncSelect  = document.getElementById('wpml-support-sync-post-type');
        var syncStatus  = document.getElementById('wpml-support-sync-taxonomies-status');

        function syncBatch(postType, batchNumber) {
          var body = new URLSearchParams();
          body.append('debug_action', 'synchronize_posts_taxonomies');
          body.append('nonce', nonces.synchronize_posts_taxonomies);
          body.append('post_type', postType);
          body.append('batch_number', String(batchNumber));

          return fetch(adminUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
          }).then(function (r) {
            if (!r.ok) { throw new Error('http ' + r.status); }
            return r.json();
          });
        }

        function runSyncLoop(postType) {
          syncBtn.disabled = true;
          syncSelect.disabled = true;
          var batch = 0;

          function step() {
            syncStatus.textContent = i18n.syncRunning + ' (#' + (batch + 1) + ')';
            syncBatch(postType, batch).then(function (payload) {
              if (!payload || !payload.success) {
                throw new Error('bad payload');
              }
              if (payload.data && payload.data.completed) {
                syncStatus.textContent = payload.data.message || i18n.done;
                syncBtn.disabled = false;
                syncSelect.disabled = false;
                return;
              }
              batch++;
              step();
            }).catch(function () {
              syncStatus.textContent = i18n.errorTitle;
              syncBtn.disabled = false;
              syncSelect.disabled = false;
            });
          }

          step();
        }

        if (syncBtn && syncSelect) {
          syncBtn.addEventListener('click', function () {
            var postType = syncSelect.value;
            if (!postType) { return; }
            runSyncLoop(postType);
          });
        }

      })();
      </script>
      <?php
  }


  private function renderUnclassifiedLanguages(): void {
    if ( ! class_exists( '\WPML\Upgrade\Commands\MigrateLanguagesToCountryModel' ) ) {
        return;
    }

      $names = \WPML\Upgrade\Commands\MigrateLanguagesToCountryModel::undeterminedLanguageNames();

    if ( $names === [] ) {
        return;
    }
    ?>
      <section id="unclassified-languages" class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5 wpml:mb-5 wpml:scroll-mt-8">
        <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
          <?php esc_html_e( 'Languages without a country', 'wpml-troubleshooting' ); ?>
        </h2>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-3">
          <?php
          echo esc_html(
            sprintf(
              /* translators: %d: number of languages with no country set. */
              _n(
                '%d language is set up for a specific country but has none chosen.',
                '%d languages are set up for a specific country but have none chosen.',
                count( $names ),
                'wpml-troubleshooting'
              ),
              count( $names )
            )
          );
          ?>
        </p>
        <ul class="wpml:text-sm wpml:text-gray-700 wpml:mb-3 wpml:space-y-1">
          <?php foreach ( $names as $name ) : ?>
            <li><?php echo esc_html( (string) $name ); ?></li>
          <?php endforeach; ?>
        </ul>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-0">
          <?php
          printf(
            /* translators: Note on WPML → Support. %1$s: opening link tag, %2$s: closing link tag; together they turn "Settings → Languages" into a link. hreflang is an HTML attribute name and stays as it is. */
            esc_html__(
              'Nothing is broken and nothing needs doing: these languages work as they are. Choosing a country for one on the %1$sSettings → Languages%2$s page sets its hreflang tag to match, and offers to move its locale, flag and names with it.',
              'wpml-troubleshooting'
            ),
            '<a href="' . esc_url( self::languagesPageUrl() ) . '">',
            '</a>'
          );
          ?>
        </p>
      </section>
      <?php
  }


  private function renderCountryIdentityDrift(): void {
    if ( ! class_exists( '\WPML\LanguageEditor\CountryIdentityDrift' ) ) {
        return;
    }

      $rows = \WPML\LanguageEditor\CountryIdentityDrift::divergedLanguages();

    if ( $rows === [] ) {
        return;
    }
    ?>
      <section id="country-identity-drift" class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-5 wpml:mb-5 wpml:scroll-mt-8">
        <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
          <?php esc_html_e( 'Languages set up for a different country', 'wpml-troubleshooting' ); ?>
        </h2>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-3">
          <?php
          echo esc_html(
            sprintf(
              /* translators: %d: number of languages whose WordPress locale does not match their country. */
              _n(
                "%d language's WordPress locale does not match the country it is set to.",
                "%d languages' WordPress locales do not match the countries they are set to.",
                count( $rows ),
                'wpml-troubleshooting'
              ),
              count( $rows )
            )
          );
          ?>
        </p>
        <ul class="wpml:text-sm wpml:text-gray-700 wpml:mb-3 wpml:space-y-1">
          <?php foreach ( $rows as $row ) : ?>
            <li>
              <?php
              echo esc_html( (string) $row['label'] );
              if ( (string) $row['name'] !== (string) $row['label'] ) {
                echo ' ';
                echo esc_html(
                  sprintf(
                    /* translators: Follows a language's name on WPML → Support when the name stored in the database differs from the one the Languages page shows. %s: the name stored in the database. */
                    __( '(saved as %s)', 'wpml-troubleshooting' ),
                    (string) $row['name']
                  )
                );
              }
              ?>
              &mdash;
              <?php
              echo esc_html(
                sprintf(
                  /* translators: End of the sentence "<language name> is set to <locale>, its country implies <locale>" on WPML → Support. "Its" is the language. %1$s: the WordPress locale saved for that language, %2$s: the locale its country would give. */
                  __( 'set to %1$s, its country implies %2$s', 'wpml-troubleshooting' ),
                  '' === (string) $row['locale'] ? '—' : (string) $row['locale'],
                  (string) $row['expected']
                )
              );
              ?>
            </li>
          <?php endforeach; ?>
        </ul>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-0">
          <?php
          printf(
            /* translators: Note on WPML → Support. %1$s: opening link tag, %2$s: closing link tag; together they turn "Settings → Languages" into a link. */
            esc_html__(
              'Nothing is broken: WordPress keeps loading each language in the locale that is stored. You can change the locale on its own on the %1$sSettings → Languages%2$s page, without choosing the country again.',
              'wpml-troubleshooting'
            ),
            '<a href="' . esc_url( self::languagesPageUrl() ) . '">',
            '</a>'
          );
          ?>
        </p>
      </section>
      <?php
  }


  private static function languagesPageUrl(): string {
    return admin_url( 'admin.php?page=tm/menu/settings&section=languages' );
  }


  private function collectTranslatablePostTypes(): array {
    $sitepress = $GLOBALS['sitepress'] ?? null;
    if ( ! is_object( $sitepress ) || ! method_exists( $sitepress, 'get_translatable_documents' ) ) {
      return array();
    }

    $list = array();
    foreach ( $sitepress->get_translatable_documents() as $slug => $doc ) {
      $label                  = isset( $doc->label ) ? (string) $doc->label : (string) $slug;
      $list[ (string) $slug ] = $label;
    }
    return $list;
  }


}
