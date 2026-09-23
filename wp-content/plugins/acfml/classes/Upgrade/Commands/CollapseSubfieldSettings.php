<?php

namespace ACFML\Upgrade\Commands;

use ACFML\FieldPreferences\SubfieldRules;

class CollapseSubfieldSettings implements Command {

	const DONE_OPTION = 'acfml_subfield_settings_collapsed';

	const MAP_INDEXES = [
		'post' => 'custom_fields_translation',
		'term' => 'custom_term_fields_translation',
	];

	public static function run() {
		if ( get_option( self::DONE_OPTION ) ) {
			return;
		}

		if ( ! SubfieldRules::isCoreResolutionAvailable() || ! function_exists( 'wpml_delete_custom_field_preferences' ) ) {
			return;
		}

		$rules = SubfieldRules::buildRules();
		if ( null === $rules ) {
			return;
		}

		$translationManagement = wpml_load_core_tm();

		$settings = $translationManagement->get_settings();
		if ( ! is_array( $settings ) || ! $settings ) {
			return;
		}

		$definitionNames = SubfieldRules::definitionNames();

		$removable = [];
		foreach ( self::MAP_INDEXES as $type => $index ) {
			if ( empty( $translationManagement->settings[ $index ] ) || ! is_array( $translationManagement->settings[ $index ] ) ) {
				continue;
			}

			$candidates = self::filterKeysMatchingAnyRule( array_keys( $translationManagement->settings[ $index ] ), $rules );

			foreach ( $candidates as $metaKey ) {
				if ( self::isDefinitionEntry( $metaKey, $definitionNames ) ) {
					continue;
				}

				$ruleMode = SubfieldRules::resolveWithRules( $rules, $metaKey, $type );
				if ( null !== $ruleMode && (int) $translationManagement->settings[ $index ][ $metaKey ] === $ruleMode ) {
					$removable[ $type ][] = $metaKey;
				}
			}
		}

		foreach ( $removable as $type => $names ) {
			wpml_delete_custom_field_preferences( $names, $type );
		}

		if ( self::entriesRemain( $removable ) ) {
			return;
		}

		update_option( self::DONE_OPTION, 1, false );
	}

	private static function entriesRemain( $removable ) {
		if ( ! $removable ) {
			return false;
		}

		$settings = get_option( 'icl_sitepress_settings' );
		if ( ! is_array( $settings ) ) {
			return true;
		}
		$tm = isset( $settings['translation-management'] ) && is_array( $settings['translation-management'] )
			? $settings['translation-management']
			: [];

		foreach ( $removable as $type => $names ) {
			$index = self::MAP_INDEXES[ $type ];
			$map   = isset( $tm[ $index ] ) && is_array( $tm[ $index ] ) ? $tm[ $index ] : [];
			foreach ( $names as $name ) {
				if ( array_key_exists( $name, $map ) ) {
					return true;
				}
			}
		}

		return false;
	}

	private static function isDefinitionEntry( $metaKey, $definitionNames ) {
		$ownName = 0 === strpos( $metaKey, '_' ) ? substr( $metaKey, 1 ) : $metaKey;

		return isset( $definitionNames[ $metaKey ] ) || isset( $definitionNames[ $ownName ] );
	}

	private static function filterKeysMatchingAnyRule( $metaKeys, $rules ) {
		$matched = [];
		foreach ( array_chunk( array_column( $rules, 'pattern' ), 50 ) as $patterns ) {
			$combined = '#^_?(?:' . implode( '|', $patterns ) . ')$#';
			$grepped  = preg_grep( $combined, $metaKeys );
			$matched  = array_merge( $matched, false === $grepped ? $metaKeys : $grepped );
		}

		return array_unique( $matched );
	}
}
