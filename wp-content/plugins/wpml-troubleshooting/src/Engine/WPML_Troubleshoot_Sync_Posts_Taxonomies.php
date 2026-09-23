<?php

namespace WPML\Troubleshooting\Engine;

use SitePress;
use WPML\API\Sanitize;
use WPML_Term_Translation_Utils;

class WPML_Troubleshoot_Sync_Posts_Taxonomies {

	const BATCH_SIZE = 5;

	private $sitepress;

	private $term_translation_utils;

	public function __construct( SitePress $sitePress, WPML_Term_Translation_Utils $term_translation_utils ) {
		$this->sitepress              = $sitePress;
		$this->term_translation_utils = $term_translation_utils;
	}

	public function run() {
		if ( ! array_key_exists( 'post_type', $_POST ) || ! array_key_exists( 'batch_number', $_POST ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Some parameters are missing for this request.', 'wpml-troubleshooting' ) ) );
			return;
		}

		$post_type    = Sanitize::stringProp( 'post_type', $_POST );
		$batch_number = (int) filter_var( $_POST['batch_number'], FILTER_SANITIZE_NUMBER_INT );

		$posts = $this->get_posts_batch( (string) $post_type, $batch_number );
		$this->synchronize_batch( $posts );

		$new_batch_number = $batch_number + 1;

		$response_data = array(
			'post_type'    => $post_type,
			'batch_number' => $new_batch_number,
			/* translators: Progress message on the Troubleshooting screen. %d: the number of the group of items being worked through. */
			'message'      => sprintf( esc_html__( 'Running now batch #%d', 'wpml-troubleshooting' ), $new_batch_number ),
		);

		if ( count( $posts ) < self::BATCH_SIZE ) {
			$total_posts_processed = ( $batch_number * self::BATCH_SIZE ) + count( $posts );

			$response_data['completed'] = true;
			/* translators: Message shown when a repair on the Troubleshooting screen has finished. %1$d: how many posts were worked through, %2$s: the name of the content type. */
			$response_data['message']   = sprintf( __( 'Completed: %1$d posts were processed for "%2$s".', 'wpml-troubleshooting' ), $total_posts_processed, $post_type );
		}

		wp_send_json_success( $response_data );
	}

	private function get_posts_batch( $type, $batch_number ) {
		$this->sitepress->switch_lang( $this->sitepress->get_default_language() );

		$args = array(
			'post_type'      => $type,
			'offset'         => $batch_number * self::BATCH_SIZE,
			'order_by'       => 'ID',
			'order'          => 'ASC',
			'posts_per_page' => self::BATCH_SIZE,
			'suppress_filters' => false,
		);

		$posts = get_posts( $args );

		$this->sitepress->switch_lang();

		return $posts;
	}

	private function synchronize_batch( $posts ) {
		$active_languages = $this->sitepress->get_active_languages();

		foreach ( $active_languages as $language_code => $active_language ) {

			foreach ( $posts as $post ) {
				$this->term_translation_utils->sync_terms( $post->ID, $language_code );
			}
		}
	}
}
