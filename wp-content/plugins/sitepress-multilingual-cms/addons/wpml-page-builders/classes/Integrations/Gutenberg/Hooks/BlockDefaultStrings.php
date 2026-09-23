<?php

namespace WPML\PB\Gutenberg\Hooks;

use WPML\FP\Str;

class BlockDefaultStrings implements \WPML\PB\Gutenberg\Integration {

	const CORE_NAMESPACE    = 'core';
	const ACF_NAMESPACE     = 'acf';
	const SYSTEM_KEY_PREFIX = '_';

	private $registered = [];

	public function add_hooks() {
		add_filter( 'block_type_metadata_settings', [ $this, 'translateDefaultStrings' ] );
	}

	public function translateDefaultStrings( array $settings ): array {
		if (
			empty( $settings['name'] )
			|| ! is_string( $settings['name'] )
			|| empty( $settings['attributes'] )
			|| ! is_array( $settings['attributes'] )
			|| ! $this->isEligibleBlock( $settings['name'] )
		) {
			return $settings;
		}

		$blockName = $settings['name'];

		foreach ( $settings['attributes'] as $key => &$attribute ) {
			$stringName = (string) $key;

			if ( '' === $stringName || $this->isSystemKey( $key ) || ! $this->isTranslatableDefault( $attribute ) ) {
				continue;
			}

			$default = $attribute['default'];

			$this->registerString( $blockName, $stringName, $default );

			$attribute['default'] = apply_filters( 'wpml_translate_single_string', $default, $blockName, $stringName );
		}
		unset( $attribute );

		return $settings;
	}

	private function isEligibleBlock( string $blockName ): bool {
		return ! Str::startsWith( self::CORE_NAMESPACE . '/', $blockName )
			&& ! Str::startsWith( self::ACF_NAMESPACE . '/', $blockName )
			&& ! ( function_exists( 'acf_has_block_type' ) && acf_has_block_type( $blockName ) );
	}

	private function isSystemKey( $key ): bool {
		return is_string( $key ) && Str::startsWith( self::SYSTEM_KEY_PREFIX, $key );
	}

	private function isTranslatableDefault( $attribute ): bool {
		return is_array( $attribute )
			&& isset( $attribute['type'], $attribute['default'] )
			&& 'string' === $attribute['type']
			&& is_string( $attribute['default'] )
			&& '' !== trim( $attribute['default'] )
			&& ! is_numeric( $attribute['default'] );
	}

	private function registerString( string $blockName, string $stringName, string $value ): void {
		if ( ! is_admin() ) {
			return;
		}

		$key = $blockName . '/' . $stringName;
		if ( isset( $this->registered[ $key ] ) ) {
			return;
		}
		$this->registered[ $key ] = true;

		do_action( 'wpml_register_single_string', $blockName, $stringName, $value );
	}
}
