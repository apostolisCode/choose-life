<?php

namespace WPML\WPSEO\YoastSEO\Indexable;

use WPML\LIB\WP\WPDB;
use WPML\WPSEO\YoastSEO\Terms\Meta\Hooks as TermMetaHooks;
use Yoast\WP\SEO\Main;
use Yoast\WP\SEO\Models\Indexable;
use Yoast\WP\SEO\Repositories\Indexable_Repository;
use Yoast\WP\SEO\Surfaces\Classes_Surface;

class Hooks implements \IWPML_Backend_Action, \IWPML_Frontend_Action, \IWPML_DIC_Action {

	private $wpdb;

	private $pendingStringTranslations = [];

	public function __construct( \wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	public function add_hooks() {
		add_action( 'icl_pro_translation_completed', [ $this, 'invalidateIndexables' ], 10, 3 );
		add_action( 'wpml_st_add_string_translation', [ $this, 'invalidateTermIndexableOnStringTranslation' ], 10, 4 );
	}

	public function invalidateTermIndexableOnStringTranslation( $stId, $translationData, $language, $stringId ) {
		if ( ! $this->pendingStringTranslations ) {
			add_action( 'shutdown', [ $this, 'invalidateTermIndexablesForSavedStrings' ] );
		}

		$this->pendingStringTranslations[] = [ (int) $stringId, (string) $language ];
	}

	public function invalidateTermIndexablesForSavedStrings() {
		$pending                         = $this->pendingStringTranslations;
		$this->pendingStringTranslations = [];

		if ( ! $pending ) {
			return;
		}

		$names = $this->getTermMetaStringNames( array_unique( array_column( $pending, 0 ) ) );

		$termIds = [];
		foreach ( $pending as $stringTranslation ) {
			list( $stringId, $language ) = $stringTranslation;

			if ( ! isset( $names[ $stringId ] ) ) {
				continue;
			}

			$parsedName = $this->parseTermMetaStringName( $names[ $stringId ] );
			if ( ! $parsedName ) {
				continue;
			}

			list( $taxonomy, $sourceTermId ) = $parsedName;

			$targetTermId = (int) apply_filters( 'wpml_object_id', $sourceTermId, $taxonomy, false, $language );

			if ( $targetTermId && $sourceTermId !== $targetTermId ) {
				$termIds[ $targetTermId ] = true;
			}
		}

		if ( $termIds ) {
			$this->invalidateTermIndexables( array_keys( $termIds ) );
		}
	}

	private function getTermMetaStringNames( array $stringIds ) {
		$in = wpml_prepare_in( $stringIds, '%d' );

		$rows = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT id, name FROM {$this->wpdb->prefix}icl_strings WHERE context = %s AND id IN ($in)",
				TermMetaHooks::stringContext()
			)
		);

		return array_column( (array) $rows, 'name', 'id' );
	}

	private function parseTermMetaStringName( $name ) {
		if ( ! preg_match( '/^(.+)-(\d+)-(wpseo_\w+)$/', $name, $matches )
			|| ! array_key_exists( $matches[3], TermMetaHooks::FIELDS )
		) {
			return null;
		}

		return [ $matches[1], (int) $matches[2], $matches[3] ];
	}

	public function invalidateIndexables( $postId, $fields, $job ) {
		if ( $postId ) {
			$this->invalidatePostIndexable( $postId );
		} elseif ( 'package_yoast-seo' === $job->original_post_type ) {
			$this->invalidateTermIndexables();
		}
	}

	private function invalidatePostIndexable( $postId ) {
		$yoastSEO = YoastSEO();

		$classes = $yoastSEO->classes;

		$indexable_repository = $classes->get( Indexable_Repository::class );

		$indexable = $indexable_repository->find_by_id_and_type( $postId, 'post', false );
		if ( $indexable ) {
			$indexable->version = 0;
			$indexable->save();
		}
	}

	public function invalidateTermIndexables( $termIds = null ) {
		WPDB::withoutError(
			function () use ( $termIds ) {
				$sql = "UPDATE {$this->wpdb->prefix}yoast_indexable SET version = 0 WHERE object_type = 'term'";

				if ( $termIds ) {
					$sql .= ' AND object_id IN (' . wpml_prepare_in( (array) $termIds, '%d' ) . ')';
				}

				$this->wpdb->query( $sql );
			}
		);
	}
}
