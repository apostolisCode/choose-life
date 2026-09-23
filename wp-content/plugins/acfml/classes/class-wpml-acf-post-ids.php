<?php

class WPML_ACF_Post_Ids implements WPML_ACF_Convertable {

	public function convert( WPML_ACF_Field $acf_field ) {
		return $this->convertSerializationLayer( $acf_field );
	}

	private function convertSerializationLayer( WPML_ACF_Field $acf_field ) {
		$came_serialized = is_serialized( $acf_field->meta_value );

		$mixedIds = $came_serialized
			? maybe_unserialize( $acf_field->meta_value )
			: $acf_field->meta_value;

		$mixedTranslatedIds = $this->convertStringOrArrayOfStringsLayer( $mixedIds, $acf_field );

		return $came_serialized
			? serialize( $mixedTranslatedIds )
			: $mixedTranslatedIds;
	}

	private function convertStringOrArrayOfStringsLayer( $mixedIds, WPML_ACF_Field $acf_field ) {

		if ( is_array( $mixedIds ) ) {
			return array_map( function ( $originalId ) use ( $acf_field ) {
				return $this->convertOriginalIdToTranslationId( $originalId, $acf_field );
			}, $mixedIds );
		}

		return $this->convertOriginalIdToTranslationId( $mixedIds, $acf_field );
	}

	private function convertOriginalIdToTranslationId( $originalId, WPML_ACF_Field $acf_field ) {
		if( is_null( $originalId ) ) {
			return null;
		}

		if ( ! is_numeric( $originalId ) ) {
			return $originalId;
		}

		return (string) ( new WPML_ACF_Post_Id( $originalId, $acf_field ) )
			->convert()->id;
	}
}
