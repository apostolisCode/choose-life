<?php

namespace WPML\UserInterface\Web\Infrastructure\WordPress\Component\Settings;

use WPML\Core\Component\ATE\Application\Service\Dto\ActiveEngineResolutionDto;
use WPML\Core\Component\ATE\Application\Service\Dto\EngineDto;

class AiTranslationEngineCard {

  const PTC_NAME = 'Private Translation Cloud (PTC)';


  public static function render( ActiveEngineResolutionDto $resolution, bool $isTeaEnabled = false ): void {
    if ( $resolution->isUnavailable() ) {
      echo '<div id="engine" class="wpml-ai-card">';
      echo '<h2 style="font-size:13px;font-weight:600;color:#111827;margin:0 0 4px 0">'
        /* translators: Name of the card on WPML → Settings that shows which service translates the site: its heading and its entry in the settings search list. */
        . esc_html__( 'Translation engine', 'wpml' )
        . '</h2>';
      self::renderUnknownVariant();
      echo '</div>';
      return;
    }

    if ( $resolution->isNoneEnabled() ) {
      echo '<div id="engine">';
      self::renderNoEngineVariant();
      self::renderPtcConfirmation();
      echo '</div>';
      return;
    }

    $activeEngine = $resolution->getEngine();

    echo '<div id="engine">';

    echo '<div class="wpml-ai-card wpml-ai-engine-variant--ptc">';
    echo '<h2 style="font-size:13px;font-weight:600;color:#111827;margin:0 0 4px 0">'
      /* translators: Name of the card on WPML → Settings that shows which service translates the site: its heading and its entry in the settings search list. */
      . esc_html__( 'Translation engine', 'wpml' )
      . '</h2>';
    self::renderPtcVariant( $activeEngine, $isTeaEnabled );
    echo '</div>';

    self::renderLegacyVariant();

    self::renderPtcConfirmation();
    self::renderLegacyConfirmation();

    echo '</div>';
  }


  private static function renderPtcConfirmation(): void {
    echo '<div class="wpml-ai-card wpml-ai-engine-confirmation--ptc" role="status"'
      . ' style="display:none;background:rgba(39,173,149,.1);border-color:#1e8876">';
    echo '<h2 style="font-size:14px;font-weight:600;color:#373737;margin:0 0 4px 0">';
    printf(
      /* translators: %s: the PTC engine product name. */
      esc_html__( "You're now using %s", 'wpml' ),
      esc_html( self::PTC_NAME )
    );
    echo '</h2>';
    echo '<p style="font-size:13px;color:#373737;margin:4px 0 13px 0">'
      /* translators: Lead-in on WPML → Settings, followed by a bulleted list of what the translation engine adds. It ends with a colon on purpose. */
      . esc_html__( 'Your translations now come with:', 'wpml' )
      . '</p>';
    self::renderBenefits( '0' );
    echo '</div>';
  }


  private static function renderLegacyConfirmation(): void {
    echo '<div class="wpml-ai-card wpml-ai-engine-confirmation--legacy" role="status"'
      . ' style="display:none">';
    echo '<h2 style="font-size:14px;font-weight:600;color:#111827;margin:0 0 4px 0">'
      . esc_html__( 'Translation engine switched', 'wpml' )
      . '</h2>';
    echo '<p style="font-size:13px;color:#373737;margin:4px 0 13px 0">';
    printf(
      /* translators: %s: the PTC engine product name. */
      esc_html__( "You're now using a legacy translation engine. Keep in mind — %s might still be the better choice:", 'wpml' ),
      esc_html( self::PTC_NAME )
    );
    echo '</p>';
    self::renderBenefits();
    echo '<button type="button" id="wpml-ai-engine-switch-back-ptc"'
      . ' class="button-primary wpml-button base-btn">';
    printf(
      /* translators: %s: the PTC engine product name. */
      esc_html__( 'Switch back to %s', 'wpml' ),
      esc_html( self::PTC_NAME )
    );
    echo '</button>';
    echo '</div>';
  }


  private static function renderPtcVariant( $activeEngine, bool $isTeaEnabled = false ): void {
    if ( $isTeaEnabled ) {
      self::renderTeaLockedVariant();
      return;
    }

    $switchLink = '<button type="button" id="wpml-ai-engine-switch-legacy"'
      . ' style="background:none;border:0;padding:0;margin:0;font:inherit;'
      . 'font-size:12px;color:#666;text-decoration:underline;cursor:pointer">'
      . esc_html__( 'Switch to a different engine', 'wpml' )
      . '</button>';

    echo '<p style="font-size:13px;color:#1f2937;margin:0">';
    printf(
      /* translators: Message on WPML → Settings about the translation engine in use. %1$s: the name of that engine, in bold, %2$s: a link reading "Switch to a different engine". */
      esc_html__( 'Currently using %1$s. %2$s if you prefer — no quality guarantee provided with other engines.', 'wpml' ),
      '<strong>' . esc_html( self::PTC_NAME ) . '</strong>',
      $switchLink
    );
    echo '</p>';

    self::renderQualityBar( $activeEngine );
  }


  private static function renderTeaLockedVariant(): void {
    $dashboardLink = '<a href="' . esc_url( admin_url( 'admin.php?page=tm/menu/main.php' ) ) . '"'
      . ' style="font-size:inherit">'
      /* translators: Link text on WPML → Settings naming a menu path: the Dashboard tab of the WPML Translations screen. Translate both names the way they are translated in the menu itself. */
      . esc_html__( 'Translations → Dashboard', 'wpml' )
      . '</a>';

    echo '<p style="font-size:13px;color:#1f2937;margin:0">';
    echo \wpml_bold_names(
      sprintf(
        /* translators: Message on WPML → Settings. %1$s: the name of the engine in use, in bold, %2$s: a link reading "Translations → Dashboard". Keep the <b> tags around the setting name. */
        __( 'Currently using %1$s. <b>Translate Everything Automatically</b> is on, go to %2$s and disable it first to switch to a legacy engine.', 'wpml' ),
        '<strong>' . esc_html( self::PTC_NAME ) . '</strong>',
        $dashboardLink
      ),
      [
        'strong' => [],
        'a'      => [
          'href'  => [],
          'style' => [],
        ],
      ]
    );
    echo '</p>';
  }


  private static function renderLegacyVariant(): void {
    echo '<div class="wpml-ai-card wpml-ai-engine-variant--legacy"'
      . ' style="background:rgba(39,173,149,.1);border-color:#1e8876">';
    echo '<h2 style="font-size:14px;font-weight:600;color:#373737;margin:0 0 4px 0">'
      . esc_html__( "Switch to PTC — WPML's own AI translation engine", 'wpml' )
      . '</h2>';

    echo '<p style="font-size:13px;color:#373737;margin:4px 0 13px 0">'
      . esc_html__( 'Get exclusive benefits not available with legacy engines:', 'wpml' )
      . '</p>';
    self::renderBenefits();
    echo '<button type="button" id="wpml-ai-engine-switch-ptc" class="button-primary wpml-button base-btn">';
    printf(
      /* translators: %s: the PTC engine product name. */
      esc_html__( 'Switch to %s', 'wpml' ),
      esc_html( self::PTC_NAME )
    );
    echo '</button>';
    echo '</div>';
  }


  private static function renderNoEngineVariant(): void {
    echo '<div class="wpml-ai-card wpml-ai-engine-variant--none"'
      . ' style="background:rgba(39,173,149,.1);border-color:#1e8876">';
    echo '<h2 style="font-size:14px;font-weight:600;color:#373737;margin:0 0 4px 0">'
      . esc_html__( 'No translation engine is active', 'wpml' )
      . '</h2>';

    echo '<p style="font-size:13px;color:#373737;margin:4px 0 13px 0">';
    printf(
      /* translators: %s: the PTC engine product name. */
      esc_html__( 'Automatic translation is off until you activate an engine. Activate %s to get:', 'wpml' ),
      esc_html( self::PTC_NAME )
    );
    echo '</p>';
    self::renderBenefits();
    echo '<button type="button" id="wpml-ai-engine-activate-ptc" class="button-primary wpml-button base-btn">';
    printf(
      /* translators: Button label on WPML → Settings, and the title of the dialog it opens, for switching the site to a translation engine. Verb, imperative. %s: the name of the translation engine, for example PTC. */
      esc_html__( 'Activate %s', 'wpml' ),
      esc_html( self::PTC_NAME )
    );
    echo '</button>';
    echo '</div>';
  }


  private static function renderQualityBar( $activeEngine ): void {
    if ( $activeEngine === null ) {
      return;
    }

    echo '<div class="wpml-ai-quality-bar">';
    echo '<span class="wpml-ai-quality-bar__label">'
      /* translators: Label in front of the translation-quality bar on WPML → Settings. It ends with a colon on purpose. */
      . esc_html__( 'Translation quality:', 'wpml' )
      . '</span>';
    echo '<div class="wpml-ai-quality-bar-mount"'
      . ' data-wpml-quality-bar'
      . ' data-engine-slug="' . esc_attr( $activeEngine->getCodeName() ) . '"'
      . ' data-engine-name="' . esc_attr( $activeEngine->getFormalName() ) . '"'
      . '></div>';
    echo '</div>';

    echo '<div class="wpml-ai-quality-disclaimer-mount"'
      . ' data-wpml-quality-disclaimer></div>';
  }


  private static function renderBenefits( string $bottomMargin = '13px' ): void {
    $benefits = array(
      sprintf(
        /* translators: Bullet point about the translation engine on WPML → Settings. %1$s: opening bold tag, %2$s: closing bold tag. */
        esc_html__( "%1\$sContextual translation%2\$s — understands your site's audience, tone and topic.", 'wpml' ),
        '<b>',
        '</b>'
      ),
      sprintf(
        /* translators: Bullet point about the translation engine on WPML → Settings. %1$s: opening bold tag, %2$s: closing bold tag. */
        esc_html__( '%1$sBest for SEO and branding%2$s — translations that fit your voice.', 'wpml' ),
        '<b>',
        '</b>'
      ),
      '<b>' . esc_html__( 'Backed by our translation quality guarantee', 'wpml' ) . '</b>',
    );

    echo '<ul style="list-style:none;margin:0 0 ' . esc_attr( $bottomMargin ) . ' 0;padding:0">';
    foreach ( $benefits as $benefit ) {
      echo '<li style="display:flex;align-items:center;gap:6px;'
        . 'font-size:13px;color:#373737;margin:0 0 6px 0">';
      echo '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 14 14"'
        . ' fill="none" aria-hidden="true" style="flex-shrink:0">'
        . '<path d="M13.8416 1.72946C13.6597 1.5475 13.3646 1.5475 13.1827 1.72946L5.23717'
        . ' 9.67498L0.99155 5.42936C0.809305 5.24712 0.513025 5.24792 0.330781 5.43016C0.148536'
        . ' 5.61241 0.147733 5.90869 0.329978 6.09093L4.89314 10.6541C4.95161 10.7126 5.0218'
        . ' 10.7522 5.09621 10.773C5.25766 10.8257 5.44217 10.7878 5.57047 10.6595L13.8416'
        . ' 2.38838C14.0236 2.20643 14.0236 1.91141 13.8416 1.72946Z" fill="#27ad95"></path>'
        . '</svg>';
      echo '<span>' . $benefit . '</span>';
      echo '</li>';
    }
    echo '</ul>';
  }


  private static function renderUnknownVariant(): void {
    echo '<p style="font-size:13px;color:#6b7280;margin:0">'
      . esc_html__( "Couldn't load translation engine information.", 'wpml' )
      . ' <a href="">'
      . esc_html__( 'Reload the page to try again.', 'wpml' )
      . '</a></p>';
  }


  public static function renderModal(): void {
    ?>
    <style>
      /* The dialog fills the viewport and is the scroll container, so a
         dialog taller than the window scrolls at the viewport edge (like a
         page scroll) instead of a scrollbar inside the box. The visible card
         is the inner __box, centered with a top/bottom margin. */
      .wpml-ai-switch-modal{width:100%;max-width:none;height:100%;
        max-height:none;margin:0;padding:0;border:0;background:transparent;
        overflow-y:auto}
      .wpml-ai-switch-modal__box{position:relative;width:620px;max-width:92vw;
        margin:5vh auto;padding:1.25rem;background:#fff;border:1px solid #e5e7eb;
        border-radius:6px;box-shadow:0 10px 30px rgba(0,0,0,.18)}
      .wpml-ai-switch-modal::backdrop{background:rgba(17,24,39,.5)}
      /* The dialog itself receives initial focus (tabindex="-1" +
         dialog.focus() on open) so showModal() doesn't land on the
         Cancel button and paint its dark :focus state. */
      .wpml-ai-switch-modal:focus{outline:none}
      /* Close X — same markup + look as the Dashboard React Modal's
         close button (app.scss `.modal-close`: absolute 16/16, icon in
         40% $wpml-blue fading to full on hover). That bundle isn't
         enqueued on Settings pages, so the rules are re-emitted here
         scoped to this dialog. */
      .wpml-ai-switch-modal .modal-close{position:absolute;top:16px;
        right:16px;padding:0;background:none;border:0;cursor:pointer;
        font-size:14px;line-height:1}
      .wpml-ai-switch-modal .modal-close .otgs-ico::before{
        color:rgba(47,125,146,.4);transition:color .3s ease-out}
      .wpml-ai-switch-modal .modal-close:hover .otgs-ico::before,
      .wpml-ai-switch-modal .modal-close:focus .otgs-ico::before{
        color:#2f7d92}
      /* Tailwind preflight overrides the .otgs-ico font on this page
         (same cascade fight as the Recommended badge glyph) — re-pin
         the icon font so the cancel glyph renders as the X. */
      .wpml-ai-switch-modal .modal-close .otgs-ico{font-family:'otgs-icons'!important}
      /* Centered action row + 24px gap — same as the Dashboard's TEA
         enable-modal `.actions` variant. */
      .wpml-ai-switch-modal-footer{display:flex;justify-content:center;
        align-items:center;gap:24px;margin-top:1rem;border-top:1px solid #f3f4f6;
        padding-top:1rem}
      .wpml-ai-switch-modal-error{color:#b91c1c;font-size:12px}
      /* Validation message above the action — red box, same look as the
         dashboard modal's `.wpml-engine-switch__error`. */
      .wpml-ai-switch-context-error{margin:10px 0 12px;padding:10px 12px;
        border-radius:4px;background:#fcf0f1;color:#8a1f11;font-size:13px}
      /* Engine-card variant gating — same wrapper-class mechanism as
         the Recommended badges. After a successful switch the TS asset
         hides both state variants (inline display:none beats these
         rules) and shows the matching confirmation card instead. */
      .wpml-ai-engine-variant--ptc,
      .wpml-ai-engine-variant--legacy,
      .wpml-ai-engine-variant--none{display:none}
      #wpml-ai-settings.wpml-ai-settings--engine-ptc .wpml-ai-engine-variant--ptc,
      #wpml-ai-settings.wpml-ai-settings--engine-other .wpml-ai-engine-variant--legacy,
      #wpml-ai-settings.wpml-ai-settings--engine-none .wpml-ai-engine-variant--none{
        display:block}

      /* Label left of the track, aligned to the track itself rather than to
         the bar box (the box carries 24px of bottom padding for the marker
         labels, so plain centring would ride high). */
      .wpml-ai-quality-bar{display:flex;align-items:flex-start;gap:12px;margin:12px 0 0}
      .wpml-ai-quality-bar__label{flex:0 0 auto;margin-top:22px;font-size:13px;
        font-weight:600;color:#111827;white-space:nowrap}
      .wpml-ai-quality-bar-mount{flex:1 1 auto;min-width:0}

      /* wpmldev-7554 — sitepress's own admin CSS sets
         `body.wpml_page_tm-menu-settings a{text-decoration:none}` and
         `... p{font-size:14px}`, both of which out-specify the shared
         component's `.wpml-quality-disclaimer` rules and would leave the
         attribution at 14px with the link indistinguishable from plain text.
         Re-assert them page-scoped so the link still reads as a link — the
         same quiet 12px/#666 underline the "Switch to a different engine"
         control above uses. */
      body.wpml_page_tm-menu-settings .wpml-quality-disclaimer{font-size:12px}
      /* font-size is re-stated on the <a> too: the same rule targets links
         directly, so inheriting 12px from the <p> is not enough. */
      body.wpml_page_tm-menu-settings .wpml-quality-disclaimer a{
        font-size:12px;color:inherit;text-decoration:underline}
      body.wpml_page_tm-menu-settings .wpml-quality-disclaimer a:hover,
      body.wpml_page_tm-menu-settings .wpml-quality-disclaimer a:focus{
        color:#373737}

      /* wpmldev-7326 — PTC-active presentation of the shared Translation
         Quality bar (mockup `translation_quality.png`).

         The component (SharedKernel/.../QualityBar) draws two markers: the
         `--cluster` one, driven entirely by the props we pass and carrying
         the "Your engine" tag, plus a FIXED `--ptc` reference marker at 90.
         With PTC active we pass PTC as the active engine, so the cluster
         marker already renders "PTC · 90 · Your engine" at the right spot
         and the fixed `--ptc` marker becomes an exact duplicate sitting on
         top of it.

         So: drop the duplicate, and recolour the cluster's active look from
         amber to green — values taken from the `--ptc.is-active` rules in
         the component's own SCSS. No component change needed. */
      [data-wpml-quality-bar][data-engine-slug="llm"] .wpml-quality-bar__marker--ptc{
        display:none}
      [data-wpml-quality-bar][data-engine-slug="llm"]
        .wpml-quality-bar__marker--cluster.is-active .wpml-quality-bar__pin{
        border-color:#2f9a55;
        box-shadow:0 0 0 4px rgba(47,154,85,.2),0 1px 3px rgba(0,0,0,.3);
        width: 16px;
        height: 16px;
      }
      [data-wpml-quality-bar][data-engine-slug="llm"]
        .wpml-quality-bar__marker--cluster.is-active .wpml-quality-bar__score{
        color:#2f9a55}
      [data-wpml-quality-bar][data-engine-slug="llm"]
        .wpml-quality-bar__marker--cluster.is-active .wpml-quality-bar__tag{
        background:#e6f4ec;color:#2f7a4a}
    </style>
    <dialog id="wpml-ai-switch-modal" class="wpml-ai-switch-modal"
      tabindex="-1" aria-labelledby="wpml-ai-switch-modal-title">
      <div class="wpml-ai-switch-modal__box">
      <button type="button" id="wpml-ai-switch-close"
        class="wpml-button base-btn text-button modal-close" data-testid="modal-close"
        aria-label="<?php echo /* translators: Accessible label (screen readers) of the button that closes a dialog on WPML → Settings. Verb, imperative, not the adjective "near". */ esc_attr__( 'Close', 'wpml' ); ?>">
        <i class="otgs-ico otgs-ico-cancel" aria-hidden="true"></i>
      </button>
      <?php  ?>
      <h2 id="wpml-ai-switch-modal-title"
        style="font-size:18px;font-weight:600;color:#111827;margin:0 0 12px 0"></h2>
      <div id="wpml-ai-switch-modal-loading" class="wpml-ai-loading">
        <span class="spinner is-active" style="float:none;margin:0"></span>
        <?php echo /* translators: Shown while a dialog or a panel is still fetching what it has to show. */ esc_html__( 'Loading…', 'wpml' ); ?>
      </div>
      <div id="wpml-ai-switch-modal-body"></div>
      <p id="wpml-ai-switch-modal-error" class="wpml-ai-switch-modal-error" hidden>
        <span id="wpml-ai-switch-modal-error-text"></span>
        <button type="button" id="wpml-ai-switch-retry" class="button">
          <?php echo /* translators: Button label shown after something failed, to repeat it. Verb phrase, imperative. */ esc_html__( 'Try again', 'wpml' ); ?>
        </button>
      </p>
      <?php  ?>
      <?php  ?>
      <p id="wpml-ai-switch-context-error" class="wpml-ai-switch-context-error"
        role="alert" hidden></p>
      <?php  ?>
      <?php  ?>
      <div class="wpml-ai-switch-modal-footer">
        <span id="wpml-ai-switch-status" class="wpml-ai-save-status" aria-live="polite"></span>
        <button type="button" id="wpml-ai-switch-save"
          class="button-primary wpml-button base-btn" disabled></button>
      </div>
      </div>
    </dialog>
    <?php
  }


}
