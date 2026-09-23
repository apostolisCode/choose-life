<?php

use ACFML\FieldState;

class WPML_ACF_Custom_Fields_Sync implements \IWPML_Backend_Action {

	private $field_state;

	public function __construct( FieldState $field_state ) {
		$this->field_state = $field_state;
	}

	public function add_hooks() {
		add_filter( 'acf/update_value', [ $this, 'clean_empty_values_for_copy_once_field' ], 10, 3 );
	}

	public function clean_empty_values_for_copy_once_field( $value, $post_id, $field ) {
		if ( '' === $value
			&& ! $this->value_has_been_emptied( $field )
			&& isset( $field['wpml_cf_preferences'] )
			&& WPML_COPY_ONCE_CUSTOM_FIELD === $field['wpml_cf_preferences']
			&& ! $this->isFieldType( $field, 'group' )
			&& ! $this->isFieldType( $field, 'clone' )
		) {
			$value = null;
		}
		return $value;
	}

	private function value_has_been_emptied( $field ) {
		$state_before = $this->field_state->getStateBefore();
		return ! empty( $state_before[ $field['name'] ] );
	}

	private function isFieldType( $field, $type ) {
		return isset( $field['type'] ) && $type === $field['type'];
	}
}
