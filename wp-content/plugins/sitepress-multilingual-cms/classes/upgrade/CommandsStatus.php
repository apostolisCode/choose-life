<?php

namespace WPML\Upgrade;

class CommandsStatus {
	const OPTION_KEY = 'wpml_update_statuses';

	const FRONT_END_SCOPE_KEY = 'front-end.scope-settled';

	private $batching = false;

	private $pending = array();

	public function beginBatch() {
		$this->batching = true;
	}

	public function endBatch() {
		$this->batching = false;

		if ( empty( $this->pending ) ) {
			return;
		}

		$update_options = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $update_options ) ) {
			$update_options = array();
		}
		foreach ( $this->pending as $id => $value ) {
			$update_options[ $id ] = $value;
		}
		$this->pending = array();

		update_option( self::OPTION_KEY, $update_options, true );
		wp_cache_flush();
	}

	public function hasBeenExecuted( $className ) {
		return (bool) $this->get_update_option_value( $this->get_command_id( $className ) );
	}

	public function markAsExecuted( $className, $flag = true ) {
		$this->set_update_status( $this->get_command_id( $className ), $flag );

		if ( ! $this->batching ) {
			wp_cache_flush();
		}
	}

	public function clearExecuted( $className ) {
		$update_options = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $update_options ) ) {
			return;
		}
		unset( $update_options[ $this->get_command_id( $className ) ] );
		update_option( self::OPTION_KEY, $update_options, true );
	}

	public function isFrontEndScopeSettled( $version ) {
		return $this->get_update_option_value( self::FRONT_END_SCOPE_KEY ) === $version;
	}

	public function markFrontEndScopeSettled( $version ) {
		$this->set_update_status( self::FRONT_END_SCOPE_KEY, $version );
	}

	private function get_command_id( $className ) {
		return str_replace( '_', '-', strtolower( $className ) );
	}

	private function set_update_status( $id, $value ) {
		if ( $this->batching ) {
			$this->pending[ $id ] = $value;
			return;
		}

		$update_options        = get_option( self::OPTION_KEY, array() );
		$update_options[ $id ] = $value;
		update_option( self::OPTION_KEY, $update_options, true );
	}

	private function get_update_option_value( $id ) {
		if ( array_key_exists( $id, $this->pending ) ) {
			return $this->pending[ $id ];
		}

		$update_options = get_option( self::OPTION_KEY, array() );

		if ( $update_options && array_key_exists( $id, $update_options ) ) {
			return $update_options[ $id ];
		}

		return null;
	}
}
