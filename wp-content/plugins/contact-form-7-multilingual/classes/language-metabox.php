<?php

namespace WPML\CF7;

use WPML\API\Sanitize;

class Language_Metabox implements \IWPML_Backend_Action, \IWPML_DIC_Action {
	private $sitepress;

	private $wpml_post_translations;

	public function __construct( \SitePress $sitepress, \WPML_Post_Translation $wpml_post_translations ) {
		$this->sitepress              = $sitepress;
		$this->wpml_post_translations = $wpml_post_translations;
	}

	public function add_hooks() {
		add_action( 'wpcf7_admin_misc_pub_section', [ $this, 'add_language_meta_box' ] );
		add_filter( 'wpml_link_to_translation', [ $this, 'link_to_translation' ], 10, 4 );
		add_filter( 'wpml_admin_language_switcher_items', [ $this, 'admin_language_switcher_items' ] );
		add_filter( 'wpml_enable_language_meta_box', [ $this, 'wpml_enable_language_meta_box_filter' ] );
	}

	public function wpml_enable_language_meta_box_filter( $enable ) {
		if ( $this->enableScript() ) {
			return true;
		}

		return $enable;
	}

	public function add_language_meta_box( $post ) {
		$post = get_post( $post );
		$trid = filter_input( INPUT_GET, 'trid', FILTER_SANITIZE_NUMBER_INT );

		if ( $post ) {
			$isGT6   = version_compare( constant( 'WPCF7_VERSION' ), '6', '>=' );
			$rootTag = $isGT6 ? 'section' : 'div';

			add_filter( 'wpml_post_edit_can_translate', '__return_true' );
			?>
			</div>
		</div>
	</div>
</<?php echo $rootTag;  ?>>

<<?php echo $rootTag;  ?> class="postbox">
	<h3><?php echo esc_html( __( 'Language', 'sitepress' ) ); ?></h3>
	<div>
		<div>
			<div id="icl_div">
				<div class="inside"><?php $this->sitepress->meta_box( $post ); ?></div>
			<?php
		} elseif ( $trid ) {
			echo '<input type="hidden" name="icl_trid" value="' . esc_attr( $trid ) . '" />';
		}
	}

	public function link_to_translation( $link, $post_id, $lang, $trid ) {
		if ( Constants::POST_TYPE === get_post_type( $post_id ) ) {
			$link = $this->get_link_to_translation( $post_id, $lang );
		}

		return $link;
	}

	public function admin_language_switcher_items( $links ) {
		$is_wpcf7_page = $this->is_wpcf7_page();
		$post_id       = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
		$trid          = filter_input( INPUT_GET, 'trid', FILTER_SANITIZE_NUMBER_INT );

		if ( $is_wpcf7_page && ( $trid || $post_id ) ) {
			if ( ! $post_id ) {
				$source_lang = filter_input( INPUT_GET, 'source_lang', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
				$post_id     = $this->wpml_post_translations->get_element_id( $source_lang, $trid );
				unset( $links['all'] );
				if ( ! $post_id ) {
					return $links;
				}
			}

			foreach ( $links as $lang => & $link ) {
				if ( 'all' !== $lang && ! $link['current'] ) {
					$link['url'] = $this->get_link_to_translation( $post_id, $lang );
				}
			}
		}

		return $links;
	}

	private function is_wpcf7_page() {
		global $pagenow;

		$plugin_page   = $this->get_plugin_page();
		$is_admin_page = $pagenow && ( 'admin.php' === $pagenow );
		$is_wpcf7_page = $plugin_page && in_array( $plugin_page, [ 'wpcf7', 'wpcf7-new' ], true );

		return $is_admin_page && $is_wpcf7_page;
	}

	private function get_plugin_page() {
		global $plugin_page;

		if ( $plugin_page ) {
			return $plugin_page;
		}

		return plugin_basename( (string) Sanitize::stringProp( 'page', $_GET ) );
	}

	private function enableScript() {
		return $this->is_wpcf7_page() && filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
	}

	private function get_link_to_translation( $post_id, $lang ) {
		$translated_post_id = $this->wpml_post_translations->element_id_in( $post_id, $lang );
		if ( $translated_post_id ) {
			$args = [
				'page'   => 'wpcf7',
				'lang'   => $lang,
				'post'   => $translated_post_id,
				'action' => 'edit',
			];
		} else {
			$trid                 = $this->wpml_post_translations->get_element_trid( $post_id );
			$source_language_code = $this->wpml_post_translations->get_element_lang_code( $post_id );

			$args = [
				'page'        => 'wpcf7-new',
				'lang'        => $lang,
				'trid'        => $trid,
				'source_lang' => $source_language_code,
			];
		}

		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}
}
