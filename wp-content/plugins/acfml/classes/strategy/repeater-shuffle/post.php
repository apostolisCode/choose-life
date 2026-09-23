<?php

namespace ACFML\Repeater\Shuffle;

class Post extends Strategy {
	protected $id_prefix = '';

	public function getEntityType() {
		return 'post';
	}

	public function isValidId( $id ) {
		return is_numeric( $id ) && $id > 0;
	}

	protected function getElement( $id ) {
		if ( $this->isValidId( $id ) ) {
			return (object) [
				'id'   => $id,
				'type' => get_post_type( $id ),
			];
		}
	}

	public function getAllMeta( $id ) {
		return get_post_meta( $id );
	}

	public function getOneMeta( $id, $key, $single = true ) {
		return get_post_meta( $id, $key, $single );
	}

	public function deleteOneMeta( $id, $key ) {
		delete_post_meta( $id, $key );
	}

	public function updateOneMeta( $id, $key, $val ) {
		update_post_meta( $id, $key, $val );
	}

	protected function get_element_type( $id ) {
		return apply_filters( 'wpml_element_type', get_post_type( $id ) );
	}
}