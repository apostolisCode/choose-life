<?php

namespace WPML\WPSEO\YoastSEO\Meta;

use WPML\FP\Obj;
use WPML\WPSEO\YoastSEO\Utils;

class SocialHooks implements \IWPML_Frontend_Action {

	const OPTION_KEY = 'wpseo_social';

	public function add_hooks() {
		add_action( 'wp', [ $this, 'init' ] );
	}

	public function init() {
		if ( Utils::isFrontPageWithPosts() ) {
			add_filter( 'wpseo_opengraph_title', [ $this, 'translateTitle' ] );
			add_filter( 'wpseo_opengraph_desc', [ $this, 'translateDescription' ] );
		}
	}

	public function translateTitle( $title ) {
		return self::translate( 'title', $title );
	}

	public function translateDescription( $description ) {
		return self::translate( 'desc', $description );
	}

	private static function translate( $type, $originalText ) {
		return Obj::prop( 'og_frontpage_' . $type, get_option( self::OPTION_KEY ) )
			?: Obj::prop( 'open_graph_frontpage_' . $type, get_option( \WPML\WPSEO\YoastSEO\Presentation\Hooks::OPTION_KEY ) )
			?: $originalText;
	}
}
