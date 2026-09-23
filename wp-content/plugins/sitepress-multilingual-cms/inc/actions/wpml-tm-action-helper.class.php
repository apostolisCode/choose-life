<?php

use WPML\TM\Settings\LegacyChecksumOrders;

class WPML_TM_Action_Helper {

	private $legacy_checksum_orders;

	public function __construct( ?LegacyChecksumOrders $legacy_checksum_orders = null ) {
		$this->legacy_checksum_orders = $legacy_checksum_orders;
	}

	public function get_tm_instance() {

		return wpml_load_core_tm();
	}

	public function create_translation_package( $post ) {
		$package_helper = new WPML_Element_Translation_Package();

		return $package_helper->create_translation_package( $post );
	}

	public function add_translation_job( $rid, $translator_id, $translation_package, $batch_options = array(), $sendFrom = null, $addJobLogs = false, $notify = true ) {

		return $this->get_update_translation_action( $translation_package )
					->add_translation_job( $rid, $translator_id, $translation_package, $batch_options, $sendFrom, $addJobLogs, $notify );
	}

	public function post_md5( $post ) {
		$post = $this->resolve_post( $post );

		if ( $this->is_external_element( $post ) ) {
			return md5( $this->filter_post_key( $this->external_element_key( $post ), $post ) );
		}

		$custom_fields_values = $this->get_post_custom_fields( $post );

		ksort( $custom_fields_values, SORT_STRING );

		return md5(
			$this->filter_post_key(
				$this->build_post_key( $this->post_key_parts( $post ), $custom_fields_values ),
				$post
			)
		);
	}

	public function post_md5_matches( $post, $md5 ) {
		if ( ! is_string( $md5 ) || '' === $md5 ) {
			return false;
		}

		$post = $this->resolve_post( $post );
		if ( ! $post ) {
			return false;
		}

		if ( $this->post_md5( $post ) === $md5 ) {
			return true;
		}

		if ( $this->is_external_element( $post ) ) {
			return false;
		}

		$filled = array_filter(
			$this->get_post_custom_fields( $post ),
			function ( $value ) {
				return '' !== (string) $value;
			}
		);
		if ( count( $filled ) < 2 ) {
			return false;
		}

		$parts = $this->post_key_parts( $post );
		$names = array_keys( $this->translatable_custom_fields() );
		foreach ( $this->legacy_checksum_orders()->forNames( $names ) as $order ) {
			$legacy_key = $this->build_post_key( $parts, $this->get_post_custom_fields( $post, $order ) );
			if ( md5( $this->filter_post_key( $legacy_key, $post ) ) === $md5 ) {
				return true;
			}
		}

		return false;
	}

	private function resolve_post( $post ) {
		return is_numeric( $post ) ? get_post( $post ) : $post;
	}

	private function is_external_element( $post ) {
		return isset( $post->external_type ) && $post->external_type;
	}

	private function external_element_key( $post ) {
		$post_key = '';
		foreach ( $post->string_data as $key => $value ) {
			$post_key .= $key . $value;
		}

		return $post_key;
	}

	private function post_key_parts( $post ) {
		$post_tags       = $this->get_post_terms( $post, 'post_tag' );
		$post_categories = $this->get_post_terms( $post, 'category' );
		$post_taxonomies = $this->get_post_taxonomies( $post );

		$content = $post->post_content;
		$content = apply_filters( 'wpml_pb_shortcode_content_for_translation', $content, $post->ID );

		$content = apply_filters( 'wpml_tm_post_md5_content', $content, $post );

		$before = $post->post_title . ';' . $content . ';' . $post->post_excerpt . ';' . implode( ',', $post_tags ) . ';' . implode( ',', $post_categories ) . ';';

		$after = '';
		if ( ! empty( $post_taxonomies ) ) {
			$after .= ';' . implode( ';', $post_taxonomies );
		}
		if ( wpml_get_setting_filter( false, 'translated_document_page_url' ) === 'translate' ) {
			$after .= $post->post_name . ';';
		}

		return array( $before, $after );
	}

	private function build_post_key( array $parts, array $custom_fields_values ) {
		return $parts[0] . implode( ',', $custom_fields_values ) . $parts[1];
	}

	private function filter_post_key( $post_key, $post ) {
		return apply_filters( 'wpml_post_md5_key', $post_key, $post );
	}

	private function legacy_checksum_orders() {
		if ( ! $this->legacy_checksum_orders ) {
			$this->legacy_checksum_orders = new LegacyChecksumOrders();
		}

		return $this->legacy_checksum_orders;
	}

	private function get_post_terms( $post, $taxonomy, $sort = false ) {
		global $sitepress;

		$terms = array();
		$hasFilter = remove_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1 );

		$post_taxonomy_terms = wp_get_object_terms( $post->ID, $taxonomy );
		if ( ! is_wp_error( $post_taxonomy_terms ) ) {
			foreach ( $post_taxonomy_terms as $trm ) {
				$terms[] = $trm->name;
			}
		}

		if ( $terms ) {
			sort( $terms, SORT_STRING );
		}

		if ( $hasFilter ) {
			add_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1, 1 );
		}

		return $terms;
	}

	private function get_post_taxonomies( $post ) {
		global $wpdb, $sitepress_settings;

		$post_taxonomies = array();

		$taxonomies = $wpdb->get_col(
			$wpdb->prepare(
				"
				SELECT DISTINCT tx.taxonomy
				FROM {$wpdb->term_taxonomy} tx JOIN {$wpdb->term_relationships} tr ON tx.term_taxonomy_id = tr.term_taxonomy_id
				WHERE tr.object_id =%d ",
				$post->ID
			)
		);
		sort( $taxonomies, SORT_STRING );
		if ( isset( $sitepress_settings['taxonomies_sync_option'] ) ) {
			foreach ( $taxonomies as $t ) {
				if ( taxonomy_exists( $t ) && isset( $sitepress_settings['taxonomies_sync_option'][ $t ] ) && $sitepress_settings['taxonomies_sync_option'][ $t ] == 1 ) {
					$taxs = $this->get_post_terms( $post, $t );

					if ( $taxs ) {
						$post_taxonomies[] = '[' . $t . ']:' . implode( ',', $taxs );
					}
				}
			}
		}

		return $post_taxonomies;
	}

	private function get_post_custom_fields( $post, ?array $order = null ) {
		$custom_fields_values = array();

		foreach ( $this->translatable_custom_fields( $order ) as $cf => $op ) {
			$value = get_post_meta( $post->ID, $cf, true );
			if ( is_scalar( $value ) ) {
				$custom_fields_values[ $cf ] = $value;
			} else {
				$custom_fields_values[ $cf ] = wp_json_encode( $value );
			}
		}

		$custom_fields_values = apply_filters( 'wpml_custom_field_values_for_post_signature', $custom_fields_values, $post->ID );
		return $custom_fields_values;
	}

	private function translatable_custom_fields( ?array $order = null ) {
		$fields = array();
		foreach ( \WPML\TM\Settings\Repository::getCustomFields() as $cf => $op ) {
			if ( in_array( (int) $op, array( WPML_TRANSLATE_CUSTOM_FIELD, WPML_COPY_ONCE_CUSTOM_FIELD ), true ) ) {
				$fields[ $cf ] = $op;
			}
		}

		if ( null === $order ) {
			return $fields;
		}

		$ordered = array();
		foreach ( $order as $cf ) {
			if ( array_key_exists( $cf, $fields ) ) {
				$ordered[ $cf ] = $fields[ $cf ];
			}
		}

		return $ordered + $fields;
	}

	private function get_update_translation_action( $translation_package ) {
		require_once WPML_TM_PATH . '/inc/translation-jobs/helpers/wpml-update-external-translation-data-action.class.php';
		require_once WPML_TM_PATH . '/inc/translation-jobs/helpers/wpml-update-post-translation-data-action.class.php';

		return array_key_exists( 'type', $translation_package ) && $translation_package['type'] === 'post'
			? new WPML_TM_Update_Post_Translation_Data_Action() : new WPML_TM_Update_External_Translation_Data_Action();
	}
}
