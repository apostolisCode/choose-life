<?php

namespace WPML\WPSEO\YoastSEO\Terms\Meta;

use SitePress;
use WPML\WPSEO\YoastSEO\Utils;
use WPSEO_Taxonomy_Meta;
use WPML\LIB\WP\Hooks as WPHooks;

use function WPML\FP\spreadArgs;

class Hooks implements \IWPML_Frontend_Action, \IWPML_Backend_Action, \IWPML_DIC_Action {

	const PACKAGE = [
		'kind'      => 'Yoast SEO',
		'kind_slug' => 'yoast-seo',
		'name'      => 'term-meta',
		'title'     => 'Term Meta',
	];

	const FIELDS = [
		Utils::KEY_META_TITLE => 'SEO Title',
		Utils::KEY_META_DESC  => 'Meta Description',
		'wpseo_bctitle'       => 'Breadcrumb Title',
		'wpseo_focuskw'       => 'Focus Keyword',
	];

	private $wpSeoOptionName;

	private $sitepress;

	private $hasCachedTermMetaValue = false;

	private $cachedTermMetaValue;

	private static $forceTranslateStrings = false;

	private static $translateOnAdminScreen = false;

	public function __construct( SitePress $sitepress ) {
		$this->sitepress = $sitepress;
	}

	public static function withForcedTranslation( callable $callback ) {
		self::$forceTranslateStrings = true;
		try {
			return $callback();
		} finally {
			self::$forceTranslateStrings = false;
		}
	}

	public static function enableAdminScreenTranslation() {
		self::$translateOnAdminScreen = true;
	}

	public function add_hooks() {
		WPHooks::onFilter( 'wpml_active_string_package_kinds' )
			->then( spreadArgs( [ $this, 'addPackageKind' ] ) );

		WPHooks::onFilter( 'option_' . $this->getWpSeoOptionName() )
			->then( spreadArgs( [ $this, 'maybeTranslateStrings' ] ) );

		if ( is_admin() ) {
			WPHooks::onAction( 'add_option', 10, 2 )
				->then( spreadArgs( [ $this, 'registerStrings' ] ) );
			WPHooks::onAction( 'update_option', 10, 3 )
				->then( spreadArgs( [ $this, 'registerUpdatedStrings' ] ) );
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_hook(
				'before_invoke:yoast',
				function () {
					add_filter( 'wpml_disable_term_adjust_id', '__return_true' );
				}
			);
		}
	}

	public function maybeTranslateStrings( $value ) {
		if ( self::$forceTranslateStrings ) {
			return $this->translateStrings( $value );
		}

		$shouldTranslateInContext = ! is_admin() || self::$translateOnAdminScreen || $this->isYoastBuildingTermIndexable();
		$isSitemapRequest         = Utils::isSitemapRequest();

		if ( $shouldTranslateInContext && ! $isSitemapRequest ) {
			if ( ! $this->hasCachedTermMetaValue ) {
				$this->cachedTermMetaValue    = $this->translateStrings( $value );
				$this->hasCachedTermMetaValue = true;
			}

			return $this->cachedTermMetaValue;
		}

		return $value;
	}

	private function isYoastBuildingTermIndexable() {
		return $this->isYoastTermBuilderOnTheStack();
	}

	private function isYoastTermBuilderOnTheStack() {
		foreach ( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) as $frame ) {
			if ( 'build' === $frame['function'] && \Yoast\WP\SEO\Builders\Indexable_Term_Builder::class === ( $frame['class'] ?? '' ) ) {
				return true;
			}
		}

		return false;
	}

	public function addPackageKind( $kinds ) {
		$kinds[ self::PACKAGE['kind_slug'] ] = [
			'title'  => self::PACKAGE['kind'],
			'slug'   => self::PACKAGE['kind_slug'],
			'plural' => self::PACKAGE['kind'],
		];

		return $kinds;
	}

	private function getPackage() {
		return [
			'kind'      => self::PACKAGE['kind'],
			'kind_slug' => self::PACKAGE['kind_slug'],
			'name'      => self::PACKAGE['name'],
			'title'     => self::PACKAGE['title'],
		];
	}

	public function registerStrings( $option, $value ) {
		if ( $option !== $this->getWpSeoOptionName() || ! is_array( $value ) ) {
			return;
		}

		$package = $this->getPackage();

		do_action( 'wpml_start_string_package_registration', $package );

		foreach ( $value as $taxonomy => $terms ) {
			if ( ! is_array( $terms ) ) {
				continue;
			}

			if ( ! $this->sitepress->is_translated_taxonomy( $taxonomy ) ) {
				continue;
			}

			foreach ( $terms as $termId => $termMeta ) {
				if ( ! is_array( $termMeta ) ) {
					continue;
				}

				$term = $this->getUnadjustedTerm( $termId, $taxonomy );
				if ( ! $term ) {
					continue;
				}

				if ( ! $this->sitepress->is_original_content_filter( false, (int) $term->term_taxonomy_id, 'tax_' . $taxonomy ) ) {
					continue;
				}

				foreach ( self::FIELDS as $field => $fieldTitle ) {
					if ( ! empty( $termMeta[ $field ] ) ) {
						$stringName  = self::buildStringName( $taxonomy, $termId, $field );
						$stringTitle = $fieldTitle . ': ' . $term->name;

						do_action(
							'wpml_register_string',
							$termMeta[ $field ],
							$stringName,
							$package,
							$stringTitle,
							'LINE'
						);
					}
				}
			}
		}

		do_action( 'wpml_delete_unused_package_strings', $package );
	}

	public function registerUpdatedStrings( $option, $oldValue, $value ) {
		if ( $oldValue !== $value ) {
			$this->registerStrings( $option, $value );
		}
	}

	private function getWpSeoOptionName() {
		if ( null === $this->wpSeoOptionName ) {
			$this->wpSeoOptionName = WPSEO_Taxonomy_Meta::get_instance()->option_name;
		}

		return $this->wpSeoOptionName;
	}

	public function translateStrings( $value ) {
		if ( ! is_array( $value ) ) {
			return $value;
		}

		$package = $this->getPackage();

		foreach ( $value as $taxonomy => $terms ) {
			if ( ! is_array( $terms ) ) {
				continue;
			}

			if ( ! $this->sitepress->is_translated_taxonomy( $taxonomy ) ) {
				continue;
			}

			foreach ( $terms as $termId => $termMeta ) {
				if ( ! is_array( $termMeta ) ) {
					continue;
				}

				$term = $this->getUnadjustedTerm( $termId, $taxonomy );
				if ( ! $term ) {
					continue;
				}

				$ttId = (int) $term->term_taxonomy_id;

				if ( ! $this->sitepress->is_original_content_filter( false, $ttId, 'tax_' . $taxonomy ) ) {
					continue;
				}

				$trid         = $this->sitepress->get_element_trid( $ttId, 'tax_' . $taxonomy );
				$translations = $this->sitepress->get_element_translations( $trid, 'tax_' . $taxonomy );
				foreach ( $translations as $translation ) {
					if ( $ttId !== (int) $translation->element_id ) {
						$targetTermId = $this->getTermIdOfTranslation( $translation, $taxonomy );
						if ( ! $targetTermId ) {
							continue;
						}

						$this->sitepress->switch_lang( $translation->language_code );
						try {
							foreach ( self::FIELDS as $field => $fieldTitle ) {
								if ( ! empty( $value[ $taxonomy ][ $targetTermId ][ $field ] ) ) {
									continue;
								}

								if ( ! empty( $termMeta[ $field ] ) ) {
									$stringName = self::buildStringName( $taxonomy, $termId, $field );

									$translatedValue = self::$forceTranslateStrings
										? $this->getStoredStringTranslation( $stringName, $translation->language_code )
										: null;

									if ( null === $translatedValue ) {
										$translatedValue = apply_filters(
											'wpml_translate_string',
											$termMeta[ $field ],
											$stringName,
											$package
										);
									}

									if ( $translatedValue ) {
										$value[ $taxonomy ][ $targetTermId ][ $field ] = $translatedValue;
									}
								}
							}
						} finally {
							$this->sitepress->switch_lang();
						}
					}
				}
			}
		}

		return $value;
	}

	private function getUnadjustedTerm( $termId, $taxonomy ) {
		add_filter( 'wpml_disable_term_adjust_id', '__return_true' );
		$term = get_term( (int) $termId, $taxonomy );
		remove_filter( 'wpml_disable_term_adjust_id', '__return_true' );

		return ( $term instanceof \WP_Term ) ? $term : null;
	}

	private function getTermIdOfTranslation( $translation, $taxonomy ) {
		if ( isset( $translation->term_id ) ) {
			return (int) $translation->term_id;
		}

		add_filter( 'wpml_disable_term_adjust_id', '__return_true' );
		$term = get_term_by( 'term_taxonomy_id', (int) $translation->element_id, $taxonomy );
		remove_filter( 'wpml_disable_term_adjust_id', '__return_true' );

		return ( $term instanceof \WP_Term ) ? (int) $term->term_id : 0;
	}

	private function getStoredStringTranslation( $stringName, $languageCode ) {
		global $wpdb;

		if ( ! is_object( $wpdb ) || ! defined( 'ICL_TM_COMPLETE' ) ) {
			return null;
		}

		$value = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT st.value
					FROM {$wpdb->prefix}icl_strings s
					INNER JOIN {$wpdb->prefix}icl_string_translations st ON st.string_id = s.id
					WHERE s.name = %s
						AND s.context = %s
						AND st.language = %s
						AND st.status = %d
						AND st.value <> ''",
				$stringName,
				self::stringContext(),
				$languageCode,
				ICL_TM_COMPLETE
			)
		);

		return is_string( $value ) && '' !== $value ? $value : null;
	}

	public static function buildStringName( $taxonomy, $termId, $field ) {
		return $taxonomy . '-' . $termId . '-' . $field;
	}

	public static function stringContext() {
		return self::PACKAGE['kind_slug'] . '-' . self::PACKAGE['name'];
	}

	public function getServedValues( $taxonomy, $sourceTermId, $field, $languageCode ) {
		$option      = get_option( $this->getWpSeoOptionName() );
		$sourceValue = $option[ $taxonomy ][ $sourceTermId ][ $field ] ?? '';

		if ( '' === $sourceValue || ! is_string( $sourceValue ) ) {
			return [];
		}

		$stringName = self::buildStringName( $taxonomy, $sourceTermId, $field );

		$this->sitepress->switch_lang( $languageCode );
		try {
			$dictionaryValue = apply_filters( 'wpml_translate_string', $sourceValue, $stringName, $this->getPackage() );
		} finally {
			$this->sitepress->switch_lang();
		}

		$values      = [];
		$storedValue = $this->getStoredStringTranslation( $stringName, $languageCode );
		if ( null !== $storedValue ) {
			$values[] = $storedValue;
		}
		$values[] = is_string( $dictionaryValue ) && '' !== $dictionaryValue ? $dictionaryValue : $sourceValue;

		return array_values( array_unique( $values ) );
	}
}
