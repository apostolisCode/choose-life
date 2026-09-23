<?php

namespace WPML\Troubleshooting\Integration\StringTranslation;

class StringTroubleshootingMenuAlias implements \IWPML_Backend_Action {


  const STRING_TRANSLATION_SLUG = 'wpml-string-translation/menu/string-translation.php';
  const SUPPORT_SLUG            = 'sitepress-multilingual-cms/menu/support.php';
  const WPML_PARENT_SLUG        = 'tm/menu/main.php';
  const HIDDEN_LEGACY_PARENT    = 'wpml-hidden-legacy-menu';
  const FROM_PARAM_VALUE        = 'support';


  public function add_hooks() {
    if ( ! $this->isAliasedRequest() ) {
      return;
    }
    add_filter( 'parent_file', array( $this, 'highlight_parent' ) );
    add_filter( 'submenu_file', array( $this, 'highlight_submenu' ) );
    add_action( 'admin_notices', array( $this, 'render_back_link' ) );
  }


  public function highlight_parent( $parent_file ) {
    if ( ! isset( $GLOBALS['_wp_real_parent_file'] ) || ! is_array( $GLOBALS['_wp_real_parent_file'] ) ) {
      $GLOBALS['_wp_real_parent_file'] = array();
    }
    $GLOBALS['_wp_real_parent_file'][ self::HIDDEN_LEGACY_PARENT ] = self::WPML_PARENT_SLUG;

    return self::WPML_PARENT_SLUG;
  }


  public function highlight_submenu( $submenu_file ) {
    return self::SUPPORT_SLUG;
  }


  public function render_back_link(): void {
    $href = esc_url(
      admin_url( 'admin.php?page=' . self::SUPPORT_SLUG . '&tool=db-strings&flash=check-string-issues' )
    ) . '#check-string-issues';

    $linkStyle = 'display:inline-flex;align-items:center;gap:6px;font-size:13px;'
      . 'color:#2563eb;text-decoration:none;';
    $svgStyle  = 'width:16px;height:16px;flex-shrink:0;';
    echo '<p class="wpml-support-back" style="margin:1em 0 0 0;">';
    echo '<a href="' . $href . '" style="' . esc_attr( $linkStyle ) . '">';
    echo '<svg style="' . esc_attr( $svgStyle ) . '" fill="none" stroke="currentColor"'
      . ' viewBox="0 0 24 24" aria-hidden="true">';
    echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"'
      . ' d="M15 19l-7-7 7-7"/>';
    echo '</svg>';
    /* translators: Name of the WPML Support screen: the admin menu item, the page heading, and the back-link that returns to it. Noun (help from the WPML support team), not the verb "to support". */
    echo esc_html__( 'Support', 'wpml-troubleshooting' );
    echo '</a></p>';
  }


  private function isAliasedRequest(): bool {
    $page = isset( $_GET['page'] ) && is_string( $_GET['page'] ) ? wp_unslash( $_GET['page'] ) : '';
    $from = isset( $_GET['from'] ) && is_string( $_GET['from'] ) ? wp_unslash( $_GET['from'] ) : '';

    return $page === self::STRING_TRANSLATION_SLUG && $from === self::FROM_PARAM_VALUE;
  }


}
