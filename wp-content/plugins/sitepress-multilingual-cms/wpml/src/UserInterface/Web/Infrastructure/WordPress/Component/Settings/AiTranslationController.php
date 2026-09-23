<?php

namespace WPML\UserInterface\Web\Infrastructure\WordPress\Component\Settings;

use WPML\Core\Component\ATE\Application\Service\ActiveEngineQueryInterface;
use WPML\Core\Component\ATE\Application\Service\Dto\ActiveEngineResolutionDto;
use WPML\Core\SharedKernel\Component\ATE\Application\Query\SiteIDQueryInterface;
use WPML\Core\SharedKernel\Component\Installer\Application\Query\WpmlSiteKeyQueryInterface;
use WPML\Core\Component\ATE\Application\Service\PtcEngineStatus;
use WPML\Core\Component\Translation\Application\Repository\SettingsRepository;
use WPML\Core\SharedKernel\Component\Language\Application\Query\Dto\LanguageDto;
use WPML\Core\SharedKernel\Component\Language\Application\Query\LanguagesQueryInterface;
use WPML\UserInterface\Web\Core\Component\Notices\TeaUpgrade\Application\TeaUpgradeFunnelProps;
use WPML\UserInterface\Web\Core\SharedKernel\Config\PageRenderInterface;
use WPML\UserInterface\Web\Infrastructure\WordPress\Component\Ate\AmsWidgetEmbed;
use WPML\UserInterface\Web\Infrastructure\WordPress\Component\Ate\AteAutoRegister;
use WPML\TM\ATE\ClonedSites\ReconnectNotice;
use WPML\UserInterface\Web\Infrastructure\WordPress\Component\Notices\TeaUpgrade\TeaUpgradeCompactNoticeRenderer;

class AiTranslationController implements PageRenderInterface {

  const SAVE_ROUTE = '/wpml/v1/save-automatic-translation-settings';

  private $languagesQuery;

  private $settingsRepository;

  private $activeEngineQuery;

  private $engineResolution;

  private $teaEnabled;

  private $teaUpgradeCompactNoticeRenderer;

  private $siteKeyQuery;

  private $siteIdQuery;


  public function __construct(
    LanguagesQueryInterface $languagesQuery,
    SettingsRepository $settingsRepository,
    ActiveEngineQueryInterface $activeEngineQuery,
    TeaUpgradeCompactNoticeRenderer $teaUpgradeCompactNoticeRenderer,
    WpmlSiteKeyQueryInterface $siteKeyQuery,
    SiteIDQueryInterface $siteIdQuery
  ) {
    $this->languagesQuery               = $languagesQuery;
    $this->settingsRepository           = $settingsRepository;
    $this->activeEngineQuery            = $activeEngineQuery;
    $this->teaUpgradeCompactNoticeRenderer = $teaUpgradeCompactNoticeRenderer;
    $this->siteKeyQuery                 = $siteKeyQuery;
    $this->siteIdQuery                  = $siteIdQuery;

    $section = isset( $_GET['section'] ) && is_string( $_GET['section'] ) ? $_GET['section'] : '';
    if ( $section !== 'ai-translation' ) {
      return;
    }

    AteAutoRegister::attempt();

    if ( self::ateEnabled() ) {
      add_action( 'admin_enqueue_scripts', array( AmsWidgetEmbed::class, 'enqueue' ) );
      add_action( 'admin_enqueue_scripts', array( $this, 'enqueueEngineSwitchScript' ) );
    } else {
      add_action( 'admin_enqueue_scripts', array( ClassicEditorActiveCallout::class, 'enqueueScript' ) );
    }
    add_action( 'admin_enqueue_scripts', array( $this, 'enqueueSaveScript' ) );
  }


  public function render() {
    $this->teaUpgradeCompactNoticeRenderer->renderIfVisible(
      TeaUpgradeFunnelProps::SURFACE_AI_SETTINGS,
      self::ateEnabled()
    );

    wp_enqueue_script( 'wpml-settings-flash' );

    if ( wp_style_is( 'wpml-settings', 'registered' ) ) {
      wp_enqueue_style( 'wpml-settings' );
    }

    SettingsPageChrome::printTitle(
      /* translators: Name of the AI Translation section of WPML → Settings: its entry in the settings search list and its heading. */
      __( 'AI Translation', 'wpml' ),
      __( 'Tell the AI about your site and choose how automatic translation behaves.', 'wpml' )
    );

    if ( self::ateEnabled() ) {
      $codes = array_map(
        function ( LanguageDto $language ) {
          return $language->getCode();
        },
        $this->languagesQuery->getActive()
      );

      $languages       = implode( ',', $codes );
      $defaultLanguage = $this->languagesQuery->getDefaultCode();

      echo '<style>#wpml-ai-settings{display:none}'
        . '.wpml-ai-loading{display:flex;align-items:center;gap:10px;padding:24px 0;color:#50575e;font-size:13px}'
        . '.wpml-ai-card{background:#fff;border:1px solid #e5e7eb;'
        . 'border-radius:6px;padding:1.25rem;margin-bottom:1.25rem}'
        . '.wpml-ai-collapsible>summary{list-style:none;cursor:pointer}'
        . '.wpml-ai-collapsible>summary::-webkit-details-marker{display:none}'
        . '.wpml-ai-collapsible[open]>summary .wpml-ai-chevron{transform:rotate(180deg)}'
        . '.wpml-ai-chevron{transition:transform .2s ease}'
        . '.wpml-wpml__AiTranslationTitle{display:none!important}'
        . '</style>';
      echo '<noscript><style>#wpml-ai-settings{display:block!important}'
        . '#wpml-ai-loading{display:none!important}</style></noscript>';
      echo '<div id="wpml-ai-loading" class="wpml-ai-loading">'
        . '<span class="spinner is-active" style="float:none;margin:0"></span>'
        . esc_html__( 'Loading automatic translation settings…', 'wpml' )
        . '</div>';

      echo '<div id="wpml-ai-settings-reconnecting" style="display:none">'
        . wp_kses( ReconnectNotice::renderInlineFallback(), ReconnectNotice::allowedTags() )
        . '</div>';

      $resolution   = $this->engineResolution();
      $activeEngine = $resolution->getEngine();

      do_action( 'wpml_ai_translation_settings_rendered', $activeEngine !== null );
      $isPtc        = $activeEngine !== null
        && $activeEngine->getCodeName() === PtcEngineStatus::PTC_ENGINE_SLUG;
      $engineClass  = '';
      if ( $activeEngine !== null ) {
        $engineClass = $isPtc ? 'wpml-ai-settings--engine-ptc' : 'wpml-ai-settings--engine-other';
      } elseif ( $resolution->isNoneEnabled() ) {
        $engineClass = 'wpml-ai-settings--engine-none';
      }
      echo '<div id="wpml-ai-settings" class="' . esc_attr( $engineClass ) . '">';

      $engineCardFirst = ( $activeEngine !== null && ! $isPtc )
        || $resolution->isNoneEnabled();
      if ( $engineCardFirst ) {
        $this->renderEngineCard( $resolution );
      }


      echo '<div id="context" class="wpml-ai-card">';
      AmsWidgetEmbed::renderSettings( 'context', $languages, $defaultLanguage );
      $this->renderSaveButton();
      echo '</div>';

      echo '<div id="wpml-ai-engine-probe" style="display:none" aria-hidden="true">';
      AmsWidgetEmbed::renderSettings( 'engine-selector', $languages, $defaultLanguage );
      echo '</div>';

      echo '<div id="auto-translate" class="wpml-ai-card">';
      AmsWidgetEmbed::renderSettings( 'translation', $languages, $defaultLanguage );
      $this->renderDraftsCheckbox();
      echo '</div>';

      echo '<div id="after-translation" class="wpml-ai-card">';
      $this->renderReviewModeRadios();
      echo '</div>';

      if ( ! $engineCardFirst ) {
        $this->renderEngineCard( $resolution );
      }

      echo '<details id="who-can-use" class="wpml-ai-card wpml-ai-collapsible">';
      echo '<summary style="display:flex;align-items:center;justify-content:space-between;gap:10px">';
      echo '<span>';
      echo '<span style="display:block;font-size:13px;font-weight:600;color:#111827">'
        . esc_html__( 'Who can use Automatic Translation', 'wpml' )
        . '</span>';
      echo '<span style="display:block;font-size:11px;color:#6b7280;margin-top:2px">'
        . esc_html__( 'Disable automatic translation for specific translators.', 'wpml' )
        . '</span>';
      echo '</span>';
      echo '<svg class="wpml-ai-chevron" width="16" height="16" fill="none"'
        . ' stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"'
        . ' style="color:#9ca3af;flex-shrink:0">'
        . '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>'
        . '</svg>';
      echo '</summary>';
      echo '<div style="margin-top:1rem;border-top:1px solid #f3f4f6;padding-top:1rem">';
      AmsWidgetEmbed::renderElement( 'who-can-ta' );
      echo '</div>';
      echo '</details>';

      echo '<div id="automation-preferences" class="wpml-ai-card">';
      AmsWidgetEmbed::renderSettings( 'automation-preferences', $languages, $defaultLanguage );
      echo '</div>';

      echo '</div>';

      AiTranslationEngineCard::renderModal();

      $this->printSettingsRevealScript();
    } else {
      ClassicEditorActiveCallout::render();
    }
  }


  private function engineResolution(): ActiveEngineResolutionDto {
    if ( $this->engineResolution === null ) {
      $this->engineResolution = $this->activeEngineQuery->resolve();
    }

    return $this->engineResolution;
  }


  private function isTeaEnabled(): bool {
    if ( $this->teaEnabled === null ) {
      $this->teaEnabled = $this->settingsRepository
          ->getSettings()
          ->getTranslateEverything()
          ->isEnabled();
    }

    return $this->teaEnabled;
  }


  private function renderEngineCard( ActiveEngineResolutionDto $resolution ): void {
    AiTranslationEngineCard::render( $resolution, $this->isTeaEnabled() );
  }


  public function enqueueEngineSwitchScript(): void {
    wp_enqueue_script( 'wpml-ai-engine-switch' );

    $activeEngine = $this->engineResolution()->getEngine();
    $isPtc        = $activeEngine !== null
      && $activeEngine->getCodeName() === PtcEngineStatus::PTC_ENGINE_SLUG;

    $codes = array_map(
      function ( LanguageDto $language ) {
        return $language->getCode();
      },
      $this->languagesQuery->getActive()
    );

    $origin   = EngineSwitchOrigin::fromQuery(
      array(
        EngineSwitchOrigin::PARAM_SOURCE  => filter_input( INPUT_GET, EngineSwitchOrigin::PARAM_SOURCE ),
        EngineSwitchOrigin::PARAM_SURFACE => filter_input( INPUT_GET, EngineSwitchOrigin::PARAM_SURFACE ),
      )
    );
    $tracking = array(
      'fromEngine' => $activeEngine === null
        ? ''
        : ( $isPtc ? 'ptc' : $activeEngine->getCodeName() ),
      'siteKey'    => $this->siteKeyQuery->get() ?: '',
      'ateSiteId'  => $this->siteIdQuery->get() ?: '',
      'source'     => $origin['source'],
      'surface'    => $origin['surface'],
    );

    $config = array(
      'languages'       => implode( ',', $codes ),
      'defaultLanguage' => $this->languagesQuery->getDefaultCode(),
      'saveRoute'       => self::SAVE_ROUTE,
      'tracking'        => $tracking,
      'i18n'            => array(
        'titleToPtc'    => sprintf(
          /* translators: %s: the PTC engine product name. */
          __( 'Switch to %s', 'wpml' ),
          AiTranslationEngineCard::PTC_NAME
        ),
        'titleToLegacy' => __( 'Switch to a different translation engine', 'wpml' ),
        'saveToPtc'     => __( 'Switch to PTC', 'wpml' ),
        'saveToLegacy'  => __( 'Save and switch engine', 'wpml' ),
        'titleActivatePtc' => sprintf(
          /* translators: Button label on WPML → Settings, and the title of the dialog it opens, for switching the site to a translation engine. Verb, imperative. %s: the name of the translation engine, for example PTC. */
          __( 'Activate %s', 'wpml' ),
          AiTranslationEngineCard::PTC_NAME
        ),
        /* translators: Button label on WPML → Settings that switches the site to the PTC translation engine. Verb, imperative. PTC is a product name and stays as it is. */
        'saveActivatePtc'  => __( 'Activate PTC', 'wpml' ),
        /* translators: Status shown next to the Save button on WPML → Settings while the settings are being stored. */
        'saving'        => __( 'Saving…', 'wpml' ),
        /* translators: Status shown next to the Save button on WPML → Settings once the settings have been stored. Past participle (they have been saved), not an instruction. */
        'saved'         => __( 'Saved', 'wpml' ),
        'errorLoad'     => __( "Couldn't load the translation engine settings — check your connection.", 'wpml' ),
        'errorSave'     => __( "Couldn't save — try again", 'wpml' ),
        'contextRequired' => __( 'Fill in your site context above before switching to PTC.', 'wpml' ),
      ),
    );

    $encoded = wp_json_encode( $config );
    if ( $encoded === false ) {
      return;
    }

    wp_add_inline_script(
      'wpml-ai-engine-switch',
      'window.wpmlAiEngineSwitch = ' . $encoded . ';'
      . 'window.wpmlAiInitialPtc = ' . ( $isPtc ? 'true' : 'false' ) . ';',
      'before'
    );
  }


  private static function printSaveStatusCssOnce(): void {
    static $emitted = false;
    if ( $emitted ) {
      return;
    }
    $emitted = true;
    ?>
    <style>
      .wpml-ai-save-status{display:inline-block;margin-left:8px;font-size:12px;
        opacity:0;transition:opacity .15s ease;vertical-align:middle}
      .wpml-ai-save-status.is-visible{opacity:1}
      .wpml-ai-save-status[data-kind="saving"]{color:#646970}
      .wpml-ai-save-status[data-kind="saved"]{color:#16a34a}
      .wpml-ai-save-status[data-kind="error"]{color:#b91c1c}
    </style>
    <?php
  }


  private function renderDraftsCheckbox(): void {
    self::printSaveStatusCssOnce();
    $translateDrafts = $this->settingsRepository->shouldTranslateAutomaticallyDrafts();
    ?>
    <p id="translate-everything-drafts" class="wpml-settings-translate-everything-drafts" style="margin-top:1rem">
      <label for="translate-everything-drafts-checkbox">
        <input type="checkbox" id="translate-everything-drafts-checkbox"
          class="wpml-checkbox-native"
          <?php checked( $translateDrafts ); ?> />
        <?php echo esc_html__( 'Translate drafts automatically', 'wpml' ); ?>
      </label>
      <span id="wpml-ai-drafts-status" class="wpml-ai-save-status" aria-live="polite"></span>
      <span class="description" style="display:block;color:#646970">
        <?php
        echo \wpml_bold_names(
          sprintf(
            /* translators: Explanation on WPML → Settings. "This" is translating drafts as they are saved. %1$s: opening bold tag, %2$s: closing bold tag. Keep the <b> tags around the setting name. */
            __( 'WPML translates each draft %1$swhen you save it%2$s. This requires <b>Translate Everything Automatically</b> to be on.', 'wpml' ),
            '<strong>',
            '</strong>'
          ),
          [ 'strong' => [] ]
        );
        ?>
      </span>
      <span class="description" style="display:block;color:#646970;margin-top:.5rem">
        <?php
        echo \wpml_bold_names(
          sprintf(
            /* translators: Message on WPML → Settings. %1$s: opening bold tag, %2$s: closing bold tag. Keep the <b> tags around the screen name. */
            __( 'Drafts saved before you enabled this option %1$sare not automatically translated%2$s. To translate them, save them again or manually send them for translation from the <b>Translation Dashboard</b>.', 'wpml' ),
            '<strong>',
            '</strong>'
          ),
          [ 'strong' => [] ]
        );
        ?>
      </span>
    </p>
    <?php
  }


  private function renderReviewModeRadios(): void {
    self::printSaveStatusCssOnce();
    self::printRecommendedBadgeCssOnce();
    $reviewMode    = $this->settingsRepository->getSettings()->getReviewMode();
    $currentReview = $reviewMode ? $reviewMode->getValue() : 'no-review';

    $options = array(
      'no-review'          => array(
        'label'       => __( 'Publish without review', 'wpml' ),
        'description' => __( 'Translations go live immediately in the target language.', 'wpml' ),
        'recommended' => array(
          'ptc'   => array(
            /* translators: Label on the option WPML advises you to pick on WPML → Settings. Past participle used as a label (this is what we advise), not a verb. */
            'label' => __( 'Recommended', 'wpml' ),
            'tip'   => __( 'PTC provides Better Than Human Translations that rarely need improvements, so you can confidently skip the review process.', 'wpml' ),
          ),
          'other' => array(
            'label' => __( 'Recommended with PTC', 'wpml' ),
            'tip'   => __( 'PTC provides Better Than Human Translations that rarely need improvements, so you can confidently skip the review process. For other translation engines, a human review is recommended to confirm accuracy.', 'wpml' ),
          ),
          'none'  => array(
            'label' => __( 'Recommended with PTC', 'wpml' ),
            'tip'   => __( 'PTC provides Better Than Human Translations that rarely need improvements, so you can confidently skip the review process. For other translation engines, a human review is recommended to confirm accuracy.', 'wpml' ),
          ),
        ),
      ),
      'before-publish'     => array(
        'label'       => __( 'Wait for review before publishing', 'wpml' ),
        'description' => __( 'Translations stay as drafts until a translation manager approves them.', 'wpml' ),
        'recommended' => array(
          'other' => array(
            'label' => __( 'Recommended with DeepL, Google and Microsoft', 'wpml' ),
            'tip'   => __( 'For DeepL, Google, and Microsoft we recommend a human review to confirm they are correct. PTC provides Better Than Human Translations that understand context, so you can confidently skip the review process.', 'wpml' ),
          ),
        ),
      ),
      'publish-and-review' => array(
        'label'       => __( 'Publish and mark for review', 'wpml' ),
        'description' => __( "Translations go live but appear in a \"needs review\" queue.", 'wpml' ),
        'recommended' => array(),
      ),
    );
    ?>
    <h2 style="font-size:13px;font-weight:600;color:#111827;margin:0 0 4px 0">
      <?php echo esc_html__( 'After auto-translation', 'wpml' ); ?>
      <span id="wpml-ai-review-status" class="wpml-ai-save-status" aria-live="polite"></span>
    </h2>
    <p style="font-size:11px;color:#6b7280;margin:0 0 12px 0">
      <?php echo esc_html__( 'What happens to content once the AI has translated it.', 'wpml' ); ?>
    </p>
    <div class="wpml-ai-review-radios">
      <?php foreach ( $options as $value => $opt ) : ?>
        <label style="display:flex;gap:10px;align-items:flex-start;padding:6px 0;cursor:pointer">
          <input type="radio" name="wpml-ai-review-mode" value="<?php echo esc_attr( $value ); ?>"
            <?php checked( $currentReview, $value ); ?>
            class="wpml-radio-native"
            style="margin-top:3px;flex-shrink:0"/>
          <span>
            <span class="wpml-ai-review-mode-label">
              <span style="color:#1f2937"><?php echo esc_html( $opt['label'] ); ?></span>
              <?php foreach ( $opt['recommended'] as $variant => $rec ) : ?>
                <?php
                $classes = 'wpml-blue-badge with-icon wpml-ai-recommended'
                  . ' wpml-ai-recommended--' . $variant;
                ?>
                <span class="<?php echo esc_attr( $classes ); ?>" tabindex="0">
                  <?php echo esc_html( $rec['label'] ); ?>
                  <span class="wpml-ai-recommended-icon" aria-hidden="true">&#xF642;</span>
                  <span class="wpml-ai-recommended-tip" role="tooltip">
                    <?php echo esc_html( $rec['tip'] ); ?>
                  </span>
                </span>
              <?php endforeach; ?>
            </span>
            <span style="display:block;font-size:11px;color:#6b7280">
              <?php echo esc_html( $opt['description'] ); ?>
            </span>
          </span>
        </label>
      <?php endforeach; ?>
    </div>
    <?php
  }


  /**
   * Emit the `.wpml-ai-recommended` CSS once per page. Mirrors the
   * idempotent guard pattern used by `printSaveStatusCssOnce()`.
   *
   * The badge appearance — light-blue pill, `--wpml-color-blue` text,
   * `3px 12px` padding, `100px` radius, `13px / 1.5` typography, the
   * `inline-flex / 8px gap / otgs-icons ::after` chrome — comes from
   * the global `.wpml-blue-badge.with-icon` rules already loaded on
   * this page (defined in the Dashboard's compiled `app.scss` and in
   * `SharedKernel/PublicSrc/Style/_variables.scss` so the styles ship
   * with any IA page that enqueues the OTGS shared CSS, which Settings
   * does). We only add: (a) the `\f642` info glyph for `::after`
   * (TEA's `%review-option-label-wrapper-styles` placeholder sets the
   * same content; that placeholder doesn't apply outside Dashboard
   * markup so we emit it locally); (b) engine-class visibility
   * gating; (c) the CSS-only hover popover (no react-tooltip
   * dependency; native `title` has a long delay that hurts UX).
   *
   * Engine class is added to `#wpml-ai-settings` (the
   * loading-notice + atomic-reveal wrapper) by `show()` —
   * `--engine-ptc` reveals `.wpml-ai-recommended--ptc`, `--engine-other`
   * reveals `.wpml-ai-recommended--other`.
   */
  private static function printRecommendedBadgeCssOnce(): void {
    static $emitted = false;
    if ( $emitted ) {
      return;
    }
    $emitted = true;
    ?>
    <style>
      .wpml-ai-review-mode-label{display:flex;flex-wrap:wrap;
        align-items:center;gap:8px}
      #wpml-ai-settings .wpml-ai-recommended{display:none;
        position:relative;cursor:help;vertical-align:middle}
      #wpml-ai-settings.wpml-ai-settings--engine-ptc
        .wpml-ai-recommended--ptc,
      #wpml-ai-settings.wpml-ai-settings--engine-other
        .wpml-ai-recommended--other,
      #wpml-ai-settings.wpml-ai-settings--engine-none
        .wpml-ai-recommended--none{display:inline-flex}
      /* TEA's badge uses a `::after` pseudo-element for the info
         glyph. Tailwind v4 preflight (loaded here via tailwind.css)
         tangles the cascade on `::after { content }` enough that an
         override won't take effect cleanly — verified attempts:
         specificity bumps + `!important`, `@layer utilities`,
         setting `--tw-content` on the badge AND on `::after`. So we
         emit the glyph as a real `<span class="wpml-ai-recommended-
         icon">&#xF642;</span>` inside the badge instead. Visually
         identical (the badge is `inline-flex` with `gap:8px` from
         `.with-icon`); avoids the cascade fight. */
      .wpml-ai-recommended-icon{font-family:'otgs-icons';
        font-size:14px;line-height:1;color:inherit}
      .wpml-ai-recommended-tip{display:none;position:absolute;
        top:calc(100% + 6px);left:0;z-index:1000;
        width:320px;padding:8px 12px;
        background:#111827;color:#f9fafb;
        font-size:11px;font-weight:400;line-height:1.45;
        border-radius:4px;white-space:normal;
        box-shadow:0 4px 12px rgba(0,0,0,0.15)}
      .wpml-ai-recommended:hover .wpml-ai-recommended-tip,
      .wpml-ai-recommended:focus .wpml-ai-recommended-tip,
      .wpml-ai-recommended:focus-within .wpml-ai-recommended-tip{
        display:block}
    </style>
    <?php
  }


  private function renderSaveButton(): void {
    self::printSaveStatusCssOnce();
    $route = wp_json_encode( self::SAVE_ROUTE );
    ?>
    <p style="margin-top:1.5rem;text-align:right">
      <span id="wpml-ai-save-status" class="wpml-ai-save-status" aria-live="polite"
        style="margin-right:8px"></span>
      <button type="button" id="wpml-ai-save-btn" class="button-primary wpml-button base-btn">
        <?php echo /* translators: Button label on WPML → Settings. Verb phrase, imperative. */ esc_html__( 'Save settings', 'wpml' ); ?>
      </button>
    </p>
    <script>
    (function(){
      var btn = document.getElementById('wpml-ai-save-btn');
      var st  = document.getElementById('wpml-ai-save-status');
      if (!btn) return;
      var S = <?php
              echo wp_json_encode(
                array(
                  /* translators: Status shown next to the Save button on WPML → Settings while the settings are being stored. */
                  'saving' => __( 'Saving…', 'wpml' ),
                  /* translators: Status shown next to the Save button on WPML → Settings once the settings have been stored. Past participle (they have been saved), not an instruction. */
                  'saved'  => __( 'Saved', 'wpml' ),
                )
              );
              ?>;
      function setStatus(kind, msg, hideAfter) {
        st.setAttribute('data-kind', kind);
        st.textContent = msg;
        st.classList.add('is-visible');
        if (st._t) { clearTimeout(st._t); st._t = null; }
        if (hideAfter) st._t = setTimeout(function(){ st.classList.remove('is-visible'); }, hideAfter);
      }
      btn.addEventListener('click', function(){
        setStatus('saving', S.saving, 0);

        // wpmldev-7255 — the store's effective engine mode, read from
        // the hidden probe. Same selector contract as the modal's
        // `readActiveEngineMode()` (AiTranslationEngineSwitch/helpers.ts
        // FLIP_CONTROL_SELECTORS — keep in sync): each flip control
        // renders only while the store is in the mode it flips AWAY
        // from. null = inconclusive (not hydrated / AMS renamed them).
        var probe = document.getElementById('wpml-ai-engine-probe');
        var mode = null;
        if (probe) {
          if (probe.querySelector('.wpml-wpml__LegacyEngineSwitchNoticeLink')) mode = 'ptc';
          else if (probe.querySelector('.AT-btn')) mode = 'legacy';
        }
        var engineChanged = mode !== null
          && ((mode === 'ptc') !== !!window.wpmlAiInitialPtc);

        // Fire `triggerSave` on the PAGE's `context` AMS widget — its
        // POSTs (settings.json, website_contexts, website_settings)
        // are fire-and-forget; AMS surfaces its own per-widget error
        // UI if any call fails. On a reset-flipped store THIS is what
        // persists the engine change. Scoped to the `#context` card:
        // the engine-switch modal (wpmldev-7114) hosts a second
        // instance of the same scope and a bare tag selector could
        // hit it.
        var el = document.querySelector('#context wpml-settings[scope="context"]');
        if (el) el.dispatchEvent(new Event('triggerSave'));

        // ALSO POST to the WPML save endpoint — flushes the engine
        // cache unconditionally (see docblock) and re-persists the
        // WPML controls' current values. `wp.apiFetch` is enqueued
        // by `enqueueSaveScript()` so it's available on this page.
        if (window.wp && wp.apiFetch) {
          var d = document.getElementById('translate-everything-drafts-checkbox');
          var r = document.querySelector('input[name="wpml-ai-review-mode"]:checked');
          // The analytics fields for the PostHog event emitted by
          // `SaveAutomaticTranslationsSettingsController`:
          //   - `isPtcEngineSelected` — the probe's effective mode
          //     (wpmldev-7255: a "Reset to default" flip persisted by
          //     this very click must not report the stale PHP-seeded
          //     value), falling back to `wpmlAiInitialPtc` when the
          //     probe is inconclusive.
          //   - `websiteContext` — scoped to the `#context` card
          //     subtree; the AMS input ids (`#about`, …) duplicate
          //     across widget instances once the modal is open.
          //   - `autoTranslateWhenEditorOpens` — scoped to the
          //     `#auto-translate` card for the same reason.
          // Each defaults to a falsy fallback when its element isn't
          // present (the AMS widgets hydrate ~0.8-4 s after the page;
          // the explicit Save click should still POST in either case
          // so the unconditional engine-cache flush runs).
          var ctx = document.getElementById('context') || document;
          var siteTopic    = ctx.querySelector('#about');
          var sitePurpose  = ctx.querySelector('#which');
          var siteAudience = ctx.querySelector('#audience');
          var autoEditor   = document.querySelector('#auto-translate #option-translate-atbd_settings');
          var body = {
            isPtcEngineSelected:          mode !== null ? (mode === 'ptc') : !!window.wpmlAiInitialPtc,
            websiteContext: {
              site_topic:    siteTopic    ? siteTopic.value    : '',
              site_purpose:  sitePurpose  ? sitePurpose.value  : '',
              site_audience: siteAudience ? siteAudience.value : ''
            },
            autoTranslateWhenEditorOpens: !!(autoEditor && autoEditor.checked)
          };
          if (d) body.shouldTranslateAutomaticallyDrafts = d.checked;
          if (r) body.reviewMode = r.value;
          wp.apiFetch({
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode output.
            path: <?php echo $route; ?>,
            method: 'POST',
            data: body
          });
        }

        // Optimistic completion — AMS does not surface a host-level
        // success event we can listen on, but its POSTs (and ours)
        // complete in ~300-800ms in practice. 1.2s window is long
        // enough for the common case while still feeling responsive.
        //
        // wpmldev-7255: when the save just persisted an engine CHANGE
        // (reset-flipped store), sync the page IN PLACE via the modal
        // script's exposed hook — the same wrapper-class/baseline sync
        // and confirmation card a modal switch renders, no reload. The
        // engine card, badges and `wpmlAiInitialPtc` were all
        // server-rendered for the OLD engine; the hook re-renders/
        // re-baselines them client-side. Only if the hook is absent
        // (modal script failed to load) fall back to a reload with
        // `?flash=engine` — never leave a stale page.
        setTimeout(function(){
          if (engineChanged) {
            if (typeof window.wpmlAiApplyEngineSwitch === 'function') {
              window.wpmlAiApplyEngineSwitch(mode);
            } else {
              var u = new URL(window.location.href);
              u.searchParams.set('flash', 'engine');
              window.location.assign(u.toString());
              return;
            }
          }
          setStatus('saved', S.saved, 1800);
        }, 1200);
      });
    })();
    </script>
    <?php
  }


  public function enqueueSaveScript(): void {
    wp_enqueue_script( 'wp-api-fetch' );
    $strings = wp_json_encode(
      array(
      /* translators: Status shown next to the Save button on WPML → Settings while the settings are being stored. */
      'saving' => __( 'Saving…', 'wpml' ),
      /* translators: Status shown next to the Save button on WPML → Settings once the settings have been stored. Past participle (they have been saved), not an instruction. */
      'saved'  => __( 'Saved', 'wpml' ),
      'error'  => __( "Couldn't save — try again", 'wpml' ),
      )
    );
    $route = wp_json_encode( self::SAVE_ROUTE );
    $js = "(function(){"
      . "var f=window.wp&&wp.apiFetch;if(!f)return;"
      . "var S=$strings;"
      . "function setStatus(el,kind,msg,hideAfter){if(!el)return;"
      . "el.setAttribute('data-kind',kind);el.textContent=msg;"
      . "el.classList.add('is-visible');"
      . "if(el._t){clearTimeout(el._t);el._t=null;}"
      . "if(hideAfter)el._t=setTimeout(function(){"
      . "el.classList.remove('is-visible');},hideAfter);}"
      . "function save(d,st){setStatus(st,'saving',S.saving,0);"
      . "return f({path:$route,method:'POST',data:d})"
      . ".then(function(){setStatus(st,'saved',S.saved,1800);})"
      . ".catch(function(){setStatus(st,'error',S.error,0);});}"
      . "var c=document.getElementById('translate-everything-drafts-checkbox');"
      . "var cs=document.getElementById('wpml-ai-drafts-status');"
      . "if(c)c.addEventListener('change',function(){"
      . "save({shouldTranslateAutomaticallyDrafts:c.checked},cs);});"
      . "var rs=document.getElementById('wpml-ai-review-status');"
      . "var rb=document.querySelectorAll('input[name=\"wpml-ai-review-mode\"]');"
      . "rb.forEach(function(x){x.addEventListener('change',function(){"
      . "if(x.checked)save({reviewMode:x.value},rs);});});"
      . "})();";
    wp_add_inline_script( 'wp-api-fetch', $js );
  }


  private function printSettingsRevealScript() {
    $js = '(function(){'
      . "var box=document.getElementById('wpml-ai-settings');if(!box)return;"
      . "var ld=document.getElementById('wpml-ai-loading');"
      . 'var done=false,obs=null,iv=null;'
      . 'function flashDeepLink(){'
      . "var m=window.location.search.match(/[?&]flash=([\\w-]+)/);"
      . 'if(!m)return;'
      . 'var t=document.getElementById(decodeURIComponent(m[1]));'
      . 'if(!t)return;'
      . "if(t.tagName==='DETAILS')t.open=true;"
      . 'var lastH=0,stableSince=Date.now(),startedAt=Date.now();'
      . 'function settle(){'
      . 'var h=document.documentElement.scrollHeight;'
      . 'if(h!==lastH){lastH=h;stableSince=Date.now();}'
      . 'var stableFor=Date.now()-stableSince;'
      . 'var elapsed=Date.now()-startedAt;'
      . 'if(stableFor>=300||elapsed>=3000){'
      . "if(typeof window.wpmlSettingsFlash==='function'){window.wpmlSettingsFlash();return;}"
      . "t.scrollIntoView({behavior:'auto',block:'start'});"
      . "t.classList.add('wpml-flash');"
      . "setTimeout(function(){t.classList.remove('wpml-flash');},1500);"
      . 'return;}'
      . 'setTimeout(settle,100);}'
      . 'settle();}'
      . 'function show(){if(done)return;done=true;'
      . "box.style.display='block';"
      . 'if(ld&&ld.parentNode)ld.parentNode.removeChild(ld);'
      . 'flashDeepLink();}'
      . "var nt=document.getElementById('wpml-ai-settings-reconnecting');"
      . 'function stopWatch(){if(obs)obs.disconnect();if(iv)clearInterval(iv);}'
      . 'function hand(fn){var a=window.wpmlConnectionStatus;if(a){fn(a);}else{(window.__wpmlConnectionPending=window.__wpmlConnectionPending||[]).push(fn);}}'
      . 'function unavailable(){if(!nt)return;'
      . "nt.style.display='block';"
      . 'hand(function(a){a.scriptFailed(nt);});}'
      . 'function recovered(){if(nt&&nt.parentNode){var n=nt;hand(function(a){a.scriptRecovered(n);});nt.parentNode.removeChild(nt);nt=null;}}'
      . 'function onReady(){show();recovered();stopWatch();}'
      . 'function tlen(s){var e=document.querySelector(s);'
      . "return e?(e.textContent||'').trim().length:0;}"
      . 'function ready(){return tlen(\'#context wpml-settings[scope="context"]\')>20'
      . '&&tlen(\'#auto-translate wpml-settings[scope="translation"]\')>20;}'
      . 'if(ready()){onReady();return;}'
      . 'obs=new MutationObserver(function(){if(ready())onReady();});'
      . 'obs.observe(box,{childList:true,subtree:true,characterData:true});'
      . 'iv=setInterval(function(){if(ready())onReady();},300);'
      . "window.addEventListener('error',function(e){var t=e&&e.target;"
      . "if(t&&t.tagName==='SCRIPT'&&t.id==='eate_dashboard-js'){show();unavailable();}},true);"
      . 'setTimeout(function(){if(ready()){onReady();}else{show();unavailable();}},8000);'
      . '})();';
    echo '<script>' . $js . '</script>';
  }


  private static function ateEnabled(): bool {
    return \WPML_TM_ATE_Status::is_enabled();
  }


}
