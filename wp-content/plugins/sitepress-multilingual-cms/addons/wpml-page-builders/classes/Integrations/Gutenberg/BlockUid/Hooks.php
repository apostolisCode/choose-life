<?php

namespace WPML\PB\Gutenberg\BlockUid;

use WPML\PB\ElementUid\Hash;
use WPML\PB\ElementUid\NameMap;
use WPML\PB\ElementUid\Registry;

class Hooks implements \WPML\PB\Gutenberg\Integration {

	const ATTRIBUTE = 'wpmlUid';

	const SOURCE = 'block';

	const UID_PATTERN = '/^[A-Za-z0-9]{1,32}$/';

	private $parsers;

	private $registry;

	private $sitepress;

	public function __construct( \WPML\PB\Gutenberg\StringsInBlock\Collection $parsers, ?Registry $registry = null, $sitepress = null ) {
		$this->parsers   = $parsers;
		$this->registry  = null === $registry ? new Registry() : $registry;
		$this->sitepress = $sitepress;
	}

	public function add_hooks() {
		add_action( 'save_post', [ $this, 'update_registry' ], 20, 2 );
		add_filter( 'wpml_pb_string_uid_map', [ $this, 'add_uid_map' ], 10, 2 );
		add_filter( 'wpml_pb_block_uids', [ $this, 'add_block_uids' ], 10, 3 );
	}

	private function get_skipped_blocks() {
		$skipped = apply_filters( 'wpml_pb_block_uid_skipped_blocks', [ 'divi/placeholder' ] );

		return array_values( array_filter( (array) $skipped, 'is_string' ) );
	}

	private static function uid_of( array $block ) {
		$uid = isset( $block['attrs'][ self::ATTRIBUTE ] ) ? $block['attrs'][ self::ATTRIBUTE ] : '';

		return is_string( $uid ) && preg_match( self::UID_PATTERN, $uid ) ? $uid : '';
	}

	private static function hash_block( array $block ) {
		unset( $block['attrs'][ self::ATTRIBUTE ] );
		$block['innerBlocks'] = [];

		return Hash::of( $block );
	}

	public function update_registry( $post_id, $post ) {
		if ( 'revision' === $post->post_type || ! $this->registry->isAvailable() ) {
			return;
		}

		$post_id   = (int) $post_id;
		$blocks    = $this->tracked_blocks( $post->post_content );
		$hashes    = [];
		$addresses = [];

		foreach ( $this->assign( $post_id, $blocks, true ) as $index => $uid ) {
			$hashes[ $uid ]    = $blocks[ $index ]['hash'];
			$addresses[ $uid ] = $blocks[ $index ]['address'];
		}

		$this->registry->update( $post_id, $hashes, self::SOURCE, null, $addresses );
	}

	public function add_uid_map( $map, $post_id ) {
		$map = is_array( $map ) ? $map : [];

		if ( ! $this->registry->isAvailable() ) {
			return $map;
		}

		$post = get_post( (int) $post_id );

		if ( ! $post instanceof \WP_Post ) {
			return $map;
		}

		$post_id = (int) $post_id;
		$blocks  = $this->tracked_blocks( $post->post_content );
		$uidMap  = [];

		if ( ! $blocks ) {
			return $map;
		}

		foreach ( $this->assign( $post_id, $blocks, false ) as $index => $uid ) {
			$this->collect_string_names( $blocks[ $index ]['block'], $uid, $uidMap );
		}

		return array_replace( $map, $uidMap );
	}

	public function add_block_uids( $uids, $post_id, $content ) {
		$uids = is_array( $uids ) ? $uids : [];

		if ( ! $this->registry->isAvailable() ) {
			return $uids;
		}

		$blocks = $this->tracked_blocks( (string) $content );

		if ( ! $blocks ) {
			return $uids;
		}

		$positions = [];

		foreach ( $this->assign( (int) $post_id, $blocks, false ) as $index => $uid ) {
			$positions[ $blocks[ $index ]['path'] ] = $uid;
		}

		return array_replace( $uids, $positions );
	}

	private function tracked_blocks( $content ) {
		if ( ! has_blocks( $content ) ) {
			return [];
		}

		$skipped = $this->get_skipped_blocks();
		$tracked = [];
		$order   = 0;

		foreach ( \WPML_Gutenberg_Integration::parse_blocks( $content ) as $index => $block ) {
			$this->collect_tracked( (array) $block, $skipped, $tracked, $order, (int) $index, 0, (string) $index );
		}

		return $tracked;
	}

	private function collect_tracked( array $block, array $skipped, array &$tracked, int &$order, int $index, int $depth, string $path ): void {
		$name = empty( $block['blockName'] ) ? '' : (string) $block['blockName'];

		if ( '' !== $name && in_array( $name, $skipped, true ) ) {
			++$order;

			return;
		}

		if ( '' !== $name ) {
			$tracked[] = [
				'block'   => $block,
				'name'    => $name,
				'hash'    => self::hash_block( $block ),
				'legacy'  => self::uid_of( $block ),
				'index'   => $index,
				'depth'   => $depth,
				'path'    => $path,
				'address' => $order . '/' . $name,
			];
		}

		++$order;

		if ( ! empty( $block['innerBlocks'] ) ) {
			foreach ( $block['innerBlocks'] as $child_position => $inner_block ) {
				$this->collect_tracked( (array) $inner_block, $skipped, $tracked, $order, $index, $depth + 1, $path . '.' . $child_position );
			}
		}
	}

	private function assign( $post_id, array $blocks, $mint ) {
		$all      = $this->registry->getEntries( $post_id );
		$rows     = self::in_document_order( self::of_source( $all ) );
		$assigned = [];
		$claimed  = [];

		foreach ( $blocks as $index => $block ) {
			$uid = $block['legacy'];

			if ( '' !== $uid && ! isset( $claimed[ $uid ] ) ) {
				$assigned[ $index ] = $uid;
				$claimed[ $uid ]    = true;
				unset( $rows[ $uid ] );
			}
		}

		foreach ( $blocks as $index => $block ) {
			if ( ! isset( $assigned[ $index ] ) ) {
				$this->take( $rows, $assigned, $claimed, $index, self::byHash( $block['hash'] ) );
			}
		}

		foreach ( $blocks as $index => $block ) {
			if ( ! isset( $assigned[ $index ] ) ) {
				$this->take( $rows, $assigned, $claimed, $index, self::byName( $block['name'] ) );
			}
		}

		$this->inherit( $post_id, $blocks, $assigned, $claimed );

		if ( $mint ) {
			$reserved = array_replace( $claimed, array_fill_keys( array_keys( $all ), true ) );

			foreach ( $blocks as $index => $block ) {
				if ( ! isset( $assigned[ $index ] ) ) {
					$uid                = self::mint( $reserved );
					$assigned[ $index ] = $uid;
					$reserved[ $uid ]   = true;
				}
			}
		}

		ksort( $assigned );

		return $assigned;
	}

	private function inherit( $post_id, array $blocks, array &$assigned, array &$claimed ) {
		$unplaced = [];

		foreach ( $blocks as $index => $block ) {
			if ( ! isset( $assigned[ $index ] ) ) {
				$unplaced[ $index ] = $block;
			}
		}

		if ( ! $unplaced ) {
			return;
		}

		$original_id = $this->original_of( $post_id );

		if ( ! $original_id ) {
			return;
		}

		$rows = self::in_document_order( self::of_source( $this->registry->getEntries( $original_id ) ) );

		foreach ( array_keys( $claimed ) as $uid ) {
			unset( $rows[ $uid ] );
		}

		foreach ( $unplaced as $index => $block ) {
			$this->take( $rows, $assigned, $claimed, $index, self::byName( $block['name'] ) );
		}
	}

	private function original_of( $post_id ) {
		if ( null === $this->sitepress ) {
			return 0;
		}

		$post = get_post( (int) $post_id );

		if ( ! $post instanceof \WP_Post ) {
			return 0;
		}

		$original_id = (int) $this->sitepress->get_original_element_id(
			(int) $post_id,
			'post_' . $post->post_type,
			false,
			true,
			false,
			true
		);

		return $original_id === (int) $post_id ? 0 : $original_id;
	}

	private function take( array &$rows, array &$assigned, array &$claimed, $index, $matches ) {
		foreach ( $rows as $uid => $entry ) {
			if ( $matches( $entry ) ) {
				$assigned[ $index ] = (string) $uid;
				$claimed[ $uid ]    = true;

				unset( $rows[ $uid ] );

				return;
			}
		}
	}

	private static function byHash( $hash ) {
		return function ( array $entry ) use ( $hash ) {
			return isset( $entry['hash'] ) && $entry['hash'] === $hash;
		};
	}

	private static function byName( $name ) {
		return function ( array $entry ) use ( $name ) {
			return self::name_of( $entry ) === $name;
		};
	}

	private static function of_source( array $entries ) {
		$ofSource = [];

		foreach ( $entries as $uid => $entry ) {
			if ( isset( $entry['source'] ) && self::SOURCE === $entry['source'] ) {
				$ofSource[ $uid ] = $entry;
			}
		}

		return $ofSource;
	}

	private static function in_document_order( array $rows ) {
		$decorated = [];
		$sequence  = 0;

		foreach ( $rows as $uid => $entry ) {
			$decorated[] = [ self::position_of( $entry ), $sequence, $uid, $entry ];

			++$sequence;
		}

		usort(
			$decorated,
			function ( array $a, array $b ) {
				return $a[0] === $b[0] ? $a[1] - $b[1] : $a[0] - $b[0];
			}
		);

		$ordered = [];

		foreach ( $decorated as $row ) {
			$ordered[ $row[2] ] = $row[3];
		}

		return $ordered;
	}

	private static function position_of( array $entry ) {
		$address = isset( $entry['address'] ) ? (string) $entry['address'] : '';

		return preg_match( '#^(\d+)/#', $address, $matches ) ? (int) $matches[1] : PHP_INT_MAX;
	}

	private static function name_of( array $entry ) {
		$address   = isset( $entry['address'] ) ? (string) $entry['address'] : '';
		$separator = strpos( $address, '/' );

		return false === $separator ? '' : substr( $address, $separator + 1 );
	}

	private static function mint( array $reserved ) {
		do {
			$uid = substr( md5( wp_generate_uuid4() ), 0, 8 );
		} while ( isset( $reserved[ $uid ] ) );

		return $uid;
	}

	private function collect_string_names( $block, $uid, &$map ) {
		$wp_block = \WPML_Gutenberg_Integration::sanitize_block( $block );

		foreach ( $this->parsers->find( $wp_block ) as $string ) {
			NameMap::add( $map, $string->id, $uid );
		}

	}
}
