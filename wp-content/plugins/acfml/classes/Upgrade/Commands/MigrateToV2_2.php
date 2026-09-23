<?php

namespace ACFML\Upgrade\Commands;

use ACFML\Options;
use ACFML\FieldGroup\FieldNamePatterns;
use ACFML\Strings\Factory;
use ACFML\Strings\Package;
use WPML\FP\Obj;
use WPML\FP\Relation;
use WPML\LIB\WP\Hooks;
use WPML\LIB\WP\Cache;

class MigrateToV2_2 implements Command {

	const KEY = 'migrate-to-v2_2';

	const STATUS_DONE = 'done';

	const INIT_PRIORITY = 2;

	public static function run() {
		Hooks::onAction( 'acf/init', self::INIT_PRIORITY )
			->then( function () {
				if ( self::isStActivated() && null === Options::get( self::KEY ) ) {
					$isLocalEnabled = acf_is_local_enabled();
					if ( $isLocalEnabled ) {
						acf_disable_local();
					}
					wpml_collect( acf_get_field_groups() )
						->filter( function ( $fieldGroup ) {
							return ! Relation::propEq( 'ID', 0, $fieldGroup );
						} )
						->map( function ( $fieldGroup ) {
							$fieldGroupId  = Obj::prop( 'ID', $fieldGroup );
							$fieldGroupKey = Obj::prop( 'key', $fieldGroup );
							$package       = Factory::createWpmlPackage( [
								'kind'      => Package::FIELD_GROUP_PACKAGE_KIND,
								'kind_slug' => Package::FIELD_GROUP_PACKAGE_KIND_SLUG,
								'name'      => $fieldGroupId,
							] );

							$packageId = $package->get_package_id();
							if ( ! $packageId ) {
								return;
							}

							MigrateToV2_2::updatePackage( $package, $fieldGroupKey );
							MigrateToV2_2::updatePackageStrings( $packageId, $fieldGroupKey, $fieldGroupId );
							MigrateToV2_2::updateNamePatterns( $fieldGroup );
							$package->flush_cache();

							Cache::flushGroup( 'WPML_ST_CACHE' );

							do_action( 'wpml_st_refresh_domain', self::stringsDomain( $fieldGroupId ) );
							do_action( 'wpml_st_refresh_domain', self::stringsDomain( $fieldGroupKey ) );
						} );

					do_action( 'wpml_st_string_updated' );
					do_action( 'wpml_st_translation_files_process_queue' );
					if ( $isLocalEnabled ) {
						acf_enable_local();
					}
					Options::set( self::KEY, self::STATUS_DONE );
				}
			} );
	}

	public static function isStActivated() {
		return defined( 'WPML_ST_VERSION' );
	}

	public static function fieldIdsToKeys( $fields, $idsToKeys = [] ) {
		foreach ( $fields as $field ) {
			if ( isset( $field['ID'], $field['key'] ) ) {
				$idsToKeys[ $field['ID'] ] = $field['key'];
			}

			if ( isset( $field['sub_fields'] ) ) {
				$idsToKeys = self::fieldIdsToKeys( $field['sub_fields'], $idsToKeys );
			}

			if ( isset( $field['layouts'] ) ) {
				foreach ( $field['layouts'] as $layout ) {
					if ( isset( $layout['sub_fields'] ) ) {
						$idsToKeys = self::fieldIdsToKeys( $layout['sub_fields'], $idsToKeys );
					}
				}
			}
		}

		return $idsToKeys;
	}

	private static function updatePackage( $package, $fieldGroupKey ) {
		$package->name  = $fieldGroupKey;
		$package->title = sprintf( Package::FIELD_GROUP_PACKAGE_TITLE, $fieldGroupKey );
		$package->update_package_record();
	}

	private static function updatePackageStrings( $packageId, $fieldGroupKey, $fieldGroupId ) {
		global $wpdb;
		$packageStrings = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT id, name
				FROM {$wpdb->prefix}icl_strings
				WHERE string_package_id = %d
				",
				$packageId
			)
		);
		if ( empty( $packageStrings ) ) {
			return;
		}

		$newContext = self::stringsDomain( $fieldGroupKey );
		$wpdb->query(
			$wpdb->prepare(
				"
				UPDATE {$wpdb->prefix}icl_strings
				SET context = %s
				WHERE string_package_id = %d
				LIMIT %d
				",
				$newContext,
				$packageId,
				count( $packageStrings )
			)
		);

		$fieldsInFieldGroup = acf_get_fields( $fieldGroupId );
		if ( empty( $fieldsInFieldGroup ) ) {
			return;
		}

		$fieldIdsToKeys  = self::fieldIdsToKeys( $fieldsInFieldGroup );
		$pattern         = '/^(group-|field-)([0-9]+)(-.*?)$/';
		$entriesToUpdate = wpml_collect( $packageStrings )
			->map( function ( $string ) use ( $pattern, $fieldGroupKey, $fieldIdsToKeys ) {
				$hasMatch = preg_match( $pattern, $string->name, $matches );
				if ( ! $hasMatch ) {
					return null;
				}

				$itemKey = ( 'group-' === $matches[1] )
					? $fieldGroupKey
					: Obj::prop( $matches[2], $fieldIdsToKeys );


				return (bool) $itemKey
					? [
						'id'   => $string->id,
						'name' => $matches[1] . $itemKey . $matches[3],
					]
					: null;
			} )
			->filter()
			->toArray();

		if ( empty( $entriesToUpdate ) ) {
			return;
		}

		$buildStringRow = function ( $entryToUpdate ) use ( $wpdb ) {
			return $wpdb->prepare(
				'( %d, "", %s, "", "", "", 0, "", "", 0, 0 )',
				$entryToUpdate['id'],
				$entryToUpdate['name']
			);
		};

		$updateStringNameQuery = "
			INSERT IGNORE INTO {$wpdb->prefix}icl_strings "
			. '(`id`, `language`, `name`, `value`, `wrap_tag`, `type`, `status`, `gettext_context`, `translation_priority`, `string_type`, `component_type`) VALUES '
			. implode( ',', array_map( $buildStringRow, $entriesToUpdate ) )
			. ' ON DUPLICATE KEY UPDATE `name` = VALUES(`name`)';

		$previousSuppressErrors = $wpdb->suppress_errors;
		$wpdb->suppress_errors  = true;
		$wpdb->query( $updateStringNameQuery );
		$wpdb->suppress_errors = $previousSuppressErrors;
	}

	private static function stringsDomain( $fieldGroupIdentifier ) {
		return Package::FIELD_GROUP_PACKAGE_KIND_SLUG . '-' . $fieldGroupIdentifier;
	}

	private static function updateNamePatterns( $fieldGroup ) {
		$fieldNamePatterns = new FieldNamePatterns();
		$fieldGroupId      = Obj::prop( 'ID', $fieldGroup );
		$fieldNamePatterns->updateGroup( $fieldGroupId, [] );
		$fieldNamePatterns->updateFieldNamePatterns( $fieldGroup );
	}

}
