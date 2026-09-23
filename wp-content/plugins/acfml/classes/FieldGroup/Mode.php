<?php

namespace ACFML\FieldGroup;

use WPML\API\Sanitize;
use WPML\FP\Obj;
use WPML\FP\Relation;

class Mode {

	const KEY = 'acfml_field_group_mode';

	const TRANSLATION  = 'translation';
	const LOCALIZATION = 'localization';
	const ADVANCED     = 'advanced';

	const MIXED = 'mixed';

	const ENTITY_POST     = 'post';
	const ENTITY_TAXONOMY = 'taxonomy';
	const ENTITY_OPTION   = 'option';

	public static function getMode( $fieldGroup ) {
		return Obj::prop( self::KEY, $fieldGroup );
	}

	public static function isConfigured( $fieldGroup ) {
		$mode = self::getMode( $fieldGroup );

		return is_string( $mode ) && '' !== $mode;
	}

	public static function getLabels() {
		return [
			/* translators: Name of the field group translation mode where each field's preference is set by hand. Noun used as a mode name. */
			self::ADVANCED     => __( 'Expert', 'acfml' ),
			/* translators: Name of the field group translation mode where the fields are sent for translation. */
			self::TRANSLATION  => __( 'Same content in every language, translated', 'acfml' ),
			/* translators: Name of the field group translation mode where every language keeps its own field values. */
			self::LOCALIZATION => __( 'Each language has its own content', 'acfml' ),
		];
	}

	public static function getLabel( $mode ) {
		return Obj::propOr( '', $mode ?: self::ADVANCED, self::getLabels() );
	}

	private static function is( $mode, $fieldGroup ) {
		return Relation::equals( $mode, self::getMode( $fieldGroup ) );
	}

	public static function isAdvanced( $fieldGroup ) {
		return self::is( self::ADVANCED, $fieldGroup )
			|| self::is( null, $fieldGroup );
	}

	public static function getForFieldableEntity( $entityType = null, $id = null ) {
		$filter = wpml_collect( [
			self::ENTITY_POST     => [
				'post_id' => $id ?: Sanitize::stringProp( 'post', $_REQUEST )
			],
			self::ENTITY_TAXONOMY => [
				'taxonomy' => Sanitize::stringProp( 'taxonomy', $_REQUEST )
			],
			self::ENTITY_OPTION   => [
				'options_page' => Sanitize::stringProp( 'page', $_REQUEST )
			],
		] )->get( $entityType, [] );

		if ( $filter ) {
			return self::getForFieldGroups( acf_get_field_groups( $filter ) );
		}

		return null;
	}

	public static function getForFieldGroups( $fieldGroups ) {
		if ( ! $fieldGroups ) {
			return null;
		}

		return wpml_collect( $fieldGroups )
			->map( Obj::propOr( self::ADVANCED, self::KEY ) )
			->reduce( function( $carry, $value ) {
				if ( ! $carry || $carry === $value ) {
					return $value;
				}

				return self::MIXED;
			} );
	}
}
