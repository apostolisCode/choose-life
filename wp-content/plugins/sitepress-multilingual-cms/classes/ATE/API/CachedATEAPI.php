<?php

namespace WPML\TM\ATE\API;

use WPML\Element\API\Languages;
use WPML\FP\Fns;
use WPML\FP\Lst;
use WPML\FP\Maybe;
use WPML\FP\Obj;
use WPML\LanguageEditor\LanguageCodeResolution;
use WPML\TM\API\ATE\LanguageMappings;
use WPML\TM\ATE\API\CacheStorage\StaticVariable;
use WPML\TM\ATE\API\CacheStorage\Storage;
use WPML\TM\ATE\API\CacheStorage\Transient;
use function WPML\FP\curryN;

class CachedATEAPI {

	const CACHE_OPTION = 'wpml-tm-ate-api-cache';

	const REQUEST_MEMO_OPTION = 'wpml-tm-ate-api-cache-request';

	const NOTHING_MARKER = '__wpml_ate_nothing__';

	private $ateAPI;

	private $storage;

	private $cachedFns = [ 'get_languages_supported_by_automatic_translations', 'get_language_details', 'get_language_mapping' ];

	public function __construct( \WPML_TM_ATE_API $ateAPI, Storage $storage ) {
		$this->ateAPI = $ateAPI;
		$this->storage = $storage;
	}

	public function __call( $name, $args ) {
		return Lst::includes( $name, $this->cachedFns ) ? $this->callWithCache( $name, $args ) : call_user_func_array( [ $this->ateAPI, $name ], $args );
	}

	private function callWithCache( $fnName, $args ) {
		$data = $this->storage->get( self::CACHE_OPTION, [] );
		$key  = $this->getKey( $args );

		if ( is_array( $data ) && array_key_exists( $fnName, $data ) && array_key_exists( $key, $data[ $fnName ] ) ) {
			return Maybe::of( $data[ $fnName ][ $key ] );
		}

		$memo = StaticVariable::getInstance()->get( self::REQUEST_MEMO_OPTION, [] );
		if ( is_array( $memo ) && array_key_exists( $fnName, $memo ) && array_key_exists( $key, $memo[ $fnName ] ) ) {
			return self::NOTHING_MARKER === $memo[ $fnName ][ $key ]
				? Maybe::nothing()
				: Maybe::of( $memo[ $fnName ][ $key ] );
		}

		if ( \WPML_TM_ATE_API::isUnreachableWindowLive() ) {
			$this->memoValue( $fnName, $key, self::NOTHING_MARKER );

			return Maybe::nothing();
		}

		$answer = call_user_func_array( [ $this->ateAPI, $fnName ], $args );

		$this->memoValue( $fnName, $key, Fns::isNothing( $answer ) ? self::NOTHING_MARKER : $answer->get() );

		return $answer->map(
			function ( $result ) use ( $fnName, $args ) {
				if ( $this->isResultCacheable( $fnName, $args, $result ) ) {
					$this->cacheValue( $fnName, $args, $result );
				}

				return $result;
			}
		);
	}

	private function memoValue( $fnName, $key, $value ) {
		$store = StaticVariable::getInstance();
		$memo  = $store->get( self::REQUEST_MEMO_OPTION, [] );

		if ( ! is_array( $memo ) ) {
			$memo = [];
		}
		$memo[ $fnName ][ $key ] = $value;
		$store->save( self::REQUEST_MEMO_OPTION, $memo );
	}

	public function isResultCacheable( $fnName, $args, $result ) {
		if ( 'get_languages_supported_by_automatic_translations' !== $fnName ) {
			return true;
		}

		$requestedCodes = isset( $args[0] ) && is_array( $args[0] ) ? $args[0] : [];
		$supportMap     = is_object( $result ) ? get_object_vars( $result ) : ( is_array( $result ) ? $result : [] );

		$sourceFolds = $this->getSourceFolds( $args );

		foreach ( $requestedCodes as $code ) {
			if ( in_array( strtolower( (string) $code ), $sourceFolds, true ) ) {
				continue;
			}
			if ( ! $this->isCodeSupported( (string) $code, $supportMap ) ) {
				return false;
			}
		}

		return true;
	}

	private function getSourceFolds( array $args ) {
		$source = isset( $args[1] ) && $args[1] ? (string) $args[1] : (string) Languages::getDefaultCode();

		if ( '' === $source ) {
			return [];
		}

		return array_map( 'strtolower', LanguageCodeResolution::ateLookupChain( $source ) );
	}

	private function isCodeSupported( $code, array $supportMap ) {
		foreach ( LanguageCodeResolution::ateLookupChain( $code ) as $variant ) {
			$matchedKey = LanguageMappings::resolveVariantKey( $variant, $supportMap );
			if ( null !== $matchedKey && null !== $supportMap[ $matchedKey ] ) {
				return true;
			}
		}

		return false;
	}

	public function cacheValue( $fnName = null, $args = null, $result = null ) {
		$fn = curryN( 3, function ( $fnName, $args, $result ) {
			$data                                      = $this->storage->get( self::CACHE_OPTION, [] );
			$data[ $fnName ][ $this->getKey( $args ) ] = $result;
			$this->storage->save( self::CACHE_OPTION, $data );

			return $result;
		} );

		return call_user_func_array( $fn, func_get_args() );
	}

	public static function clearAllCaches() {
		StaticVariable::getInstance()->delete( self::CACHE_OPTION );
		StaticVariable::getInstance()->delete( self::REQUEST_MEMO_OPTION );
		( new Transient() )->delete( self::CACHE_OPTION );
	}

	private function getKey( $args ) {
		return \serialize( $args );
	}
}
