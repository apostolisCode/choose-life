<?php


function wpml_is_action_authenticated( $action ) {
	$nonce = isset( $_POST['_icl_nonce'] ) ? $_POST['_icl_nonce'] : '';
	if ( '' !== $nonce ) {
		$action = $action . '_nonce';
	} else {
		$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
	}

	return wp_verify_nonce( $nonce, $action );
}

function wpml_nonce_field( $action ) {
	return '<input name="_icl_nonce" type="hidden" value="'
		   . wp_create_nonce( $action . '_nonce' ) . '"/>';
}

if ( ! function_exists( 'uuid_v5' ) ) {
	function uuid_v5( $name, $ns_uuid = '6ba7b811-9dad-11d1-80b4-00c04fd430c8' ) {
		$wpml_uuid = new WPML_UUID();
		return $wpml_uuid->get_uuid_v5( $name, $ns_uuid );
	}
}

function wpml_uuid( $object_id, $object_type, $timestamp = null ) {
	$wpml_uuid = new WPML_UUID();
	return $wpml_uuid->get( $object_id, $object_type, $timestamp );
}
