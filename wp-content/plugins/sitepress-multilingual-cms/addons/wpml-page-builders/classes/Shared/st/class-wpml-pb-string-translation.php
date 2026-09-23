<?php

class WPML_PB_String_Translation {

	protected $wpdb;

	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	public function get_package_strings( array $package_data ) {
		$strings    = [];
		$package_id = $this->get_package_id( $package_data );
		if ( $package_id ) {
			$sql_to_get_strings_with_package_id = $this->wpdb->prepare(
				"SELECT * FROM {$this->wpdb->prefix}icl_strings s WHERE s.string_package_id=%d",
				$package_id
			);

			$package_strings = $this->wpdb->get_results( $sql_to_get_strings_with_package_id );

			if ( ! empty( $package_strings ) ) {
				foreach ( $package_strings as $string ) {
					$strings[ $this->get_string_hash( $string->value ) ] = [
						'value'      => $string->value,
						'context'    => $string->context,
						'name'       => $string->name,
						'id'         => $string->id,
						'package_id' => $package_id,
						'location'   => $string->location,
					];
				}
			}
		}
		return $strings;
	}

	public function getStringsInContext( string $context, array $columns = [ '*' ], string $conditions = '' ) {
		$columns = array_intersect(
			$columns,
			[ '*', 'id', 'context', 'name', 'value', 'string_package_id', 'location', 'type', 'status' ]
		);

		if ( in_array( '*', $columns, true ) ) {
			$columns = [ '*' ];
		}

		if ( empty( $columns ) ) {
			return [];
		}

		$sql_columns = join( ', ', $columns );
		return $this->wpdb->get_results(
			$this->wpdb->prepare(
				"
				SELECT {$sql_columns}
				FROM {$this->wpdb->prefix}icl_strings
				WHERE context = %s
				{$conditions}
				",
				$context
			)
		);
	}

	public function remove_string( array $string_data ) {
		icl_unregister_string( $string_data['context'], $string_data['name'] );

		$field_type = 'package-string-' . $string_data['package_id'] . '-' . $string_data['id'];

		$this->wpdb->query(
			$this->wpdb->prepare(
				"DELETE FROM {$this->wpdb->prefix}icl_translate
				WHERE field_type = %s
				AND job_id NOT IN (
					SELECT job_id FROM {$this->wpdb->prefix}icl_translate_job WHERE translated = 0
				)",
				$field_type
			)
		);
	}

	private function get_package_id( array $package_data ) {
		$package_id            = false;
		$sql_to_get_package_id = $this->wpdb->prepare(
			"
				SELECT s.ID
				FROM {$this->wpdb->prefix}icl_string_packages s
				WHERE s.kind=%s AND s.name=%s AND s.title=%s AND s.post_id=%s
			",
			$package_data['kind'],
			$package_data['name'],
			$package_data['title'],
			$package_data['post_id']
		);

		$result = $this->wpdb->get_row( $sql_to_get_package_id );

		if ( isset( $result->ID ) ) {
			$package_id = $result->ID;
		}

		return $package_id;
	}

	public function get_string_hash( $string_value ) {
		$string_value = is_null( $string_value ) ? '' : $string_value;

		return md5( $string_value );
	}
}
