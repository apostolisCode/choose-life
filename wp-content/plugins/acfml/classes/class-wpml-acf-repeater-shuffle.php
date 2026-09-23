<?php

use ACFML\FieldGroup\Mode;
use ACFML\Repeater\Shuffle\Post;
use ACFML\Repeater\Shuffle\RowChange;
use ACFML\Repeater\Shuffle\Rows;
use ACFML\Repeater\Shuffle\Strategy;
use ACFML\Repeater\Sync\Condition;
use ACFML\Repeater\Sync\StaleJobs;
use ACFML\Repeater\Sync\TranslationStatus;
use WPML\FP\Obj;

class WPML_ACF_Repeater_Shuffle implements \IWPML_Backend_Action {

	const PRIORITY_BEFORE_ACF_WRITES = 5;
	const PRIORITY_AFTER_ACF_WROTE   = 15;

	private $shuffled;

	private $rowsBefore = [];

	private $heldBack = [];

	private $unmoved = [];

	private $status;

	public function __construct( Strategy $shuffled, TranslationStatus $status ) {
		$this->shuffled = $shuffled;
		$this->status   = $status;
	}

	public function add_hooks() {
		if ( Mode::LOCALIZATION !== Mode::getForFieldableEntity( $this->shuffled->getEntityType() ) ) {
			add_action( 'acf/save_post', [ $this, 'store_state_before' ], self::PRIORITY_BEFORE_ACF_WRITES );
			add_action( 'acf/save_post', [ $this, 'update_translated_repeaters' ], self::PRIORITY_AFTER_ACF_WROTE );
			add_filter( 'wpml_custom_fields_to_sync_on_post_save', [ $this, 'holdBackRowsOfAnInsert' ], 10, 2 );
		}
	}

	public function store_state_before( $post_id = 0 ) {
		if ( $this->shouldSupportSync( $post_id ) ) {
			$this->rowsBefore = Rows::read( $post_id );
		}
	}

	public function shouldSupportSync( $entityId ) {
		return Condition::isActiveFor( $this->shuffled, $entityId );
	}

	public function update_translated_repeaters( $post_id = 0 ) {
		if ( ! $this->shouldSupportSync( $post_id ) ) {
			return;
		}

		$translations = $this->shuffled->getTranslations( $post_id );

		if ( ! $translations ) {
			return;
		}

		$isLevel = fn( $translation ) => $this->status->isLevelWithOriginal( $translation );
		$level   = array_filter( $translations, $isLevel );
		$behind  = array_diff_key( $translations, $level );

		$jobsOutdated = false;
		$needsUpdate  = false;
		$unmoved      = [];

		foreach ( Rows::read( $post_id ) as $field => $after ) {
			$change = RowChange::between(
				(array) Obj::pathOr( [], [ $field, 'rows' ], $this->rowsBefore ),
				$after['rows']
			);

			$jobsOutdated = $jobsOutdated || $change->outdatesJob();
			$needsUpdate  = $needsUpdate || $change->needsTranslating();

			if ( ! $change->movesRows() ) {
				if ( $change->needsTranslating() ) {
					$this->heldBack[] = $field;
				}

				continue;
			}

			foreach ( $level as $translation ) {
				$this->followRows( $post_id, $translation->element_id, $field, $after['type'], $change->getMap() );
			}

			foreach ( $behind as $translation ) {
				$unmoved[ (int) $translation->element_id ][] = $field;
			}
		}

		$this->holdBackCopiesFor( $unmoved );

		if ( $needsUpdate ) {
			foreach ( $translations as $translation ) {
				$this->status->markNeedsUpdate( $translation );
			}

			return;
		}

		if ( $jobsOutdated && $level ) {
			StaleJobs::mark( $this->shuffled->getEntityType(), $post_id, array_keys( $level ) );
		}
	}

	public function holdBackRowsOfAnInsert( $fields, $postId = 0 ) {
		if ( ! $this->heldBack || ! is_array( $fields ) ) {
			return $fields;
		}

		$kept = fn( $metaKey ) => ! self::belongsToAnyOf( $metaKey, $this->heldBack );

		return array_values( array_filter( $fields, $kept ) );
	}

	private function holdBackCopiesFor( array $unmoved ) {
		if ( $unmoved && $this->shuffled instanceof Post ) {
			$this->unmoved = $unmoved;

			add_filter( 'wpml_sync_custom_field_copied_value', [ $this, 'holdBackCopiedValue' ], 10, 4 );
			add_action( 'wpml_after_save_post', [ $this, 'releaseHeldBackCopies' ] );
		}
	}

	public function holdBackCopiedValue( $value, $originalId = 0, $translatedId = 0, $metaKey = '' ) {
		$unmoved = $this->unmoved[ (int) $translatedId ] ?? [];

		return self::belongsToAnyOf( $metaKey, $unmoved )
			? $this->shuffled->getOneMeta( $translatedId, (string) $metaKey, true )
			: $value;
	}

	public function releaseHeldBackCopies() {
		$this->unmoved = [];

		remove_filter( 'wpml_sync_custom_field_copied_value', [ $this, 'holdBackCopiedValue' ], 10 );
		remove_action( 'wpml_after_save_post', [ $this, 'releaseHeldBackCopies' ] );
	}

	private function followRows( $originalId, $translatedId, $field, $type, array $map ) {
		$rowMeta = $this->readRowMeta( $translatedId, $field );

		foreach ( $rowMeta as $keys ) {
			foreach ( array_keys( $keys ) as $key ) {
				$this->shuffled->deleteOneMeta( $translatedId, $key );
			}
		}

		foreach ( $map as $newIndex => $oldIndex ) {
			if ( null === $oldIndex || ! isset( $rowMeta[ $oldIndex ] ) ) {
				continue;
			}

			foreach ( $rowMeta[ $oldIndex ] as $key => $value ) {
				$this->shuffled->updateOneMeta( $translatedId, $this->reIndex( $key, $field, $newIndex ), $value );
			}
		}

		$this->followWrapper( $originalId, $translatedId, $field, $type, count( $map ) );
	}

	private function readRowMeta( $translatedId, $field ) {
		$pattern = self::rowKeyPattern( $field );
		$rows    = [];

		foreach ( (array) $this->shuffled->getAllMeta( $translatedId ) as $key => $value ) {
			if ( preg_match( $pattern, (string) $key, $matched ) ) {
				$rows[ (int) $matched[2] ][ $key ] = is_array( $value ) ? end( $value ) : $value;
			}
		}

		return $rows;
	}

	private function followWrapper( $originalId, $translatedId, $field, $type, $rowCount ) {
		$value = 'flexible_content' === $type
			? $this->shuffled->getOneMeta( $originalId, $field, true )
			: $rowCount;

		$this->shuffled->updateOneMeta( $translatedId, $field, $value );
	}

	private function reIndex( $key, $field, $newIndex ) {
		$rewrite = fn( array $matched ) => $matched[1] . $field . '_' . $newIndex . '_';

		return preg_replace_callback( self::rowKeyPattern( $field ), $rewrite, (string) $key, 1 );
	}

	private static function belongsToAnyOf( $metaKey, array $fields ) {
		foreach ( $fields as $field ) {
			if ( $metaKey === $field || preg_match( self::rowKeyPattern( $field ), (string) $metaKey ) ) {
				return true;
			}
		}

		return false;
	}

	private static function rowKeyPattern( $field ) {
		return '/^(_?)' . preg_quote( $field, '/' ) . '_(\d+)_/';
	}
}
