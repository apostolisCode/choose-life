<?php

namespace WPML\TM\XLIFF;

use WPML\TM\Taxonomy\TranslatableTermMeta;

class TaxonomyTermXliffBuilder {

	public function build( \WP_Term $term, $sourceLang, $targetLang, array $options = [] ) {
		$ancestors = $this->buildAncestorNames( $term );
		$note      = $this->buildContextNote( $ancestors, $term );

		$fileAttributes = [
			'original'        => 'term-' . $term->term_id,
			'source-language' => $sourceLang,
			'target-language' => $targetLang,
			'datatype'        => 'plaintext',
		];

		$bodyGroups = [
			[
				'id'    => 'term-' . $term->term_id,
				'units' => $this->buildUnits( $term, $note, $options ),
			],
		];

		$xliff = new \WPML_TM_XLIFF( '1.2' );
		$xliff->setFileAttributes( $fileAttributes );
		$xliff->setBodyGroups( $bodyGroups );

		return $xliff->toString();
	}

	private function buildContextNote( array $ancestors, \WP_Term $term ) {
		$taxonomy = [];
		foreach ( $ancestors as $name ) {
			$taxonomy[] = [ 'name' => (string) $name ];
		}

		$leaf = [ 'name' => (string) $term->name ];
		if ( ! empty( $term->description ) ) {
			$leaf['description'] = (string) $term->description;
		}
		$taxonomy[] = $leaf;

		return (string) wp_json_encode(
			[ 'taxonomy' => $taxonomy ],
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
		);
	}

	private function buildAncestorNames( \WP_Term $term ) {
		if ( ! $term->parent ) {
			return [];
		}

		$chain    = [];
		$parentId = $term->parent;

		$disable = '__return_true';
		add_filter( 'wpml_disable_term_adjust_id', $disable, 999 );
		try {
			while ( $parentId ) {
				$parent = get_term( $parentId, $term->taxonomy );
				if ( ! $parent || is_wp_error( $parent ) ) {
					break;
				}
				array_unshift( $chain, $parent->name );
				$parentId = $parent->parent;
			}
		} finally {
			remove_filter( 'wpml_disable_term_adjust_id', $disable, 999 );
		}

		return $chain;
	}

	private function buildUnits( \WP_Term $term, $note, array $options ) {
		$units = [
			[
				'id'     => 'term-name',
				'source' => $term->name,
				'note'   => $note,
			],
		];

		if ( ! empty( $options['translate_slug'] ) ) {
			$units[] = [
				'id'     => 'term-slug',
				'source' => $term->slug,
				'note'   => $note,
			];
		}

		if ( ! empty( $term->description ) ) {
			$units[] = [
				'id'     => 'term-description',
				'source' => $term->description,
				'note'   => $note,
			];
		}

		foreach ( TranslatableTermMeta::values( $term ) as $key => $value ) {
			$units[] = [
				'id'     => TranslatableTermMeta::UNIT_ID_PREFIX . $key,
				'source' => $value,
				'note'   => $note,
			];
		}

		return $units;
	}
}
