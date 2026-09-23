<?php

class WPML_TM_Translation_Priorities {

	const DEFAULT_TRANSLATION_PRIORITY_VALUE_SLUG = 'optional';

	const TAXONOMY = 'translation_priority';

	public function get_values() {
		return get_terms(
			array(
				'taxonomy'   => self::TAXONOMY,
				'hide_empty' => false,
			)
		);
	}

	public function get_default_value_id() {
		return (int) self::get_default_term()->term_id;
	}

	public static function get_default_term() {
		$term = get_term_by( 'slug', self::DEFAULT_TRANSLATION_PRIORITY_VALUE_SLUG, self::TAXONOMY );
		if ( ! $term ) {
			$term = new WP_Term( (object) [ 'term_id' => 0 ] );
		}

		return $term;
	}


	public static function insert_missing_translation( $term_taxonomy_id, $original_name, $target_language ) {
		global $sitepress;

		$trid              = (int) $sitepress->get_element_trid( $term_taxonomy_id, 'tax_' . self::TAXONOMY );
		$term_translations = $sitepress->get_element_translations( $trid, 'tax_' . self::TAXONOMY );

		if ( ! isset( $term_translations[ $target_language ] ) ) {

			$sitepress->switch_locale( $target_language );

			$name            = __( $original_name, 'sitepress' );
			$suffix          = '';
			if ( $name === $original_name ) {
				$suffix = ' ' . $target_language;
			}

			$slug            = WPML_Terms_Translations::term_unique_slug( sanitize_title( $name . $suffix ), self::TAXONOMY, $target_language );
			$translated_term = wp_insert_term( $name, self::TAXONOMY, array( 'slug' => $slug ) );

			if ( $translated_term && ! is_wp_error( $translated_term ) ) {
				WPML_Set_Language::run_exempt_core_flow(
					function () use ( $sitepress, $translated_term, $trid, $target_language ) {
						$sitepress->set_element_language_details( $translated_term['term_taxonomy_id'], 'tax_' . self::TAXONOMY, $trid, $target_language );
					}
				);

				return $translated_term['term_taxonomy_id'];
			}
		}

		return false;
	}

	public static function insert_missing_default_terms() {
		global $sitepress;

		$terms = array(
			array(
				/* translators: Option that says how important a translation is: it may be left out. Adjective. */
				'default' => __( 'Optional', 'sitepress' ),
				'en_name' => 'Optional',
			),
			array(
				/* translators: Option that says how important a translation is: it has to be done. Adjective. */
				'default' => __( 'Required', 'sitepress' ),
				'en_name' => 'Required',
			),
			array(
				/* translators: Option that says how important a translation is: it should not be done at all. Adjective phrase. */
				'default' => __( 'Not needed', 'sitepress' ),
				'en_name' => 'Not needed',
			),
		);

		$default_language = $sitepress->get_default_language();
		$active_languages = $sitepress->get_active_languages();
		$current_language = $sitepress->get_current_language();
		unset( $active_languages[ $default_language ] );

		foreach ( $terms as $term ) {
			$sitepress->switch_locale( $default_language );
			$original_term = get_term_by( 'name', $term['default'], self::TAXONOMY, ARRAY_A );

			if ( ! $original_term ) {
				$original_term = wp_insert_term( $term['default'], self::TAXONOMY );
				WPML_Set_Language::run_exempt_core_flow(
					function () use ( $sitepress, $original_term, $default_language ) {
						$sitepress->set_element_language_details( $original_term['term_taxonomy_id'], 'tax_' . self::TAXONOMY, null, $default_language );
					}
				);
			}

			foreach ( $active_languages as $language ) {
				self::insert_missing_translation( $original_term['term_taxonomy_id'], $term['en_name'], $language['code'] );
			}
		}

		$sitepress->switch_locale( $current_language );
	}

	public static function insert_missing_term_relationship() {
		global $wpdb;
		$term = get_term_by( 'slug', self::DEFAULT_TRANSLATION_PRIORITY_VALUE_SLUG, self::TAXONOMY );
		if ( ! $term ) {
			return;
		}

		$inserted = $wpdb->query(
			$wpdb->prepare(
				"INSERT IGNORE INTO {$wpdb->term_relationships} (object_id, term_taxonomy_id, term_order)
				SELECT p.ID, %d, 0
				FROM {$wpdb->posts} p
				WHERE p.post_type IN ('post', 'page')
				AND p.post_status NOT IN ('inherit', 'auto-draft', 'trash')
				AND NOT EXISTS (
					SELECT 1
					FROM {$wpdb->term_relationships} tr
					WHERE tr.object_id = p.ID
					AND tr.term_taxonomy_id = %d
				)",
				$term->term_taxonomy_id,
				$term->term_taxonomy_id
			)
		);

		if ( $inserted ) {
			wp_update_term_count( (int) $term->term_taxonomy_id, self::TAXONOMY );
		}
	}
}
