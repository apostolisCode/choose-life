<?php

namespace WPML\WPSEO\RankMathSEO\Sitemap;

use WPML\Element\API\PostTranslations;
use WPML\FP\Fns;
use WPML\FP\Lst;
use WPML\FP\Maybe;
use WPML\FP\Obj;
use WPML\FP\Relation;

class Hooks implements \IWPML_Frontend_Action, \IWPML_DIC_Action {

	private $urlConverter;

	private $sitepress;

	private $wpdb;

	private $secondaryHomesById;

	public function __construct( \WPML_URL_Converter $urlConverter, \SitePress $sitepress, \wpdb $wpdb ) {
		$this->urlConverter = $urlConverter;
		$this->sitepress    = $sitepress;
		$this->wpdb         = $wpdb;
	}

	public function add_hooks() {
		add_filter( 'rank_math/sitemap/entry', [ $this, 'filterEntry' ], 10, 3 );
		add_filter( 'rank_math/html_sitemap/get_posts/join', [ $this, 'byLanguageJoin' ], 10, 2 );
		add_filter( 'rank_math/html_sitemap/get_posts/where', [ $this, 'byLanguageWhere' ], 10, 2 );
	}

	public function filterEntry( $url, $type, $obj ) {
		if ( $url && 'post' === $type ) {
			return $this->replaceHomePageInSecondaryLanguages( $url, $obj );
		}

		return $url;
	}

	private function replaceHomePageInSecondaryLanguages( $url, $obj ) {
		if ( null === $this->secondaryHomesById ) {
			$isInDefaultLang = Relation::propEq( 'language_code', $this->sitepress->get_default_language() );

			$getIdAndUrl = function ( $translation ) {
				return [
					(int) $translation->element_id,
					$this->urlConverter->convert_url( home_url(), $translation->language_code ),
				];
			};

			$this->secondaryHomesById = Maybe::fromNullable( get_option( 'page_on_front' ) )
				->map( PostTranslations::get() )
				->map( Fns::reject( $isInDefaultLang ) )
				->map( Fns::map( $getIdAndUrl ) )
				->map( Lst::fromPairs() )
				->getOrElse( [] );
		}

		return Obj::assoc(
			'loc',
			Obj::propOr(
				$url['loc'],
				(int) Obj::prop( 'ID', $obj ),
				$this->secondaryHomesById
			),
			$url
		);
	}

	public function byLanguageJoin( $sql, $postType ) {
		if ( $this->sitepress->is_translated_post_type( $postType ) ) {
			$sql .= "
				JOIN {$this->wpdb->prefix}icl_translations AS wpml_translations
					ON p.ID = wpml_translations.element_id
					AND wpml_translations.element_type = CONCAT('post_', p.post_type)
			";
		}

		return $sql;
	}

	public function byLanguageWhere( $sql, $postType ) {
		$default_language = $this->sitepress->get_default_language();
		$current_language = $this->sitepress->get_current_language();

		if ( $this->sitepress->is_translated_post_type( $postType ) ) {
			$sql .= $this->wpdb->prepare(
				' AND ( wpml_translations.language_code = %s',
				$current_language
			);

			if ( $this->sitepress->is_display_as_translated_post_type( $postType ) ) {
				$sql .= $this->wpdb->prepare(
					"
					OR (
						wpml_translations.language_code = %s
						AND NOT EXISTS (
							SELECT 1
							FROM {$this->wpdb->posts} p1
							JOIN {$this->wpdb->prefix}icl_translations t1 ON p1.ID = t1.element_id
							WHERE t1.trid = wpml_translations.trid
							  AND t1.language_code = %s
							  AND p1.post_status = 'publish'
						)
					)
					",
					$default_language,
					$current_language
				);
			}

			$sql .= ' )';
		}

		return $sql;
	}
}
