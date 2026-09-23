<?php

namespace WPML\Language\Detection;

use WPML\FP\Maybe;
use WPML\FP\Obj;
use WPML\FP\Lst;
use WPML\FP\Str;
use WPML\FP\Fns;
use \WPML_Request;

class Ajax extends WPML_Request {

	public function get_requested_lang() {
		return Maybe::of( $_REQUEST )
					->map( Obj::prop( 'lang' ) )
					->filter( Lst::includes( Fns::__, $this->active_languages ) )
					->map( 'sanitize_text_field' )
					->getOrElse(
						function () {
							return $this->get_media_grid_all_languages() ?: $this->get_cookie_lang();
						}
					);
	}

	private function get_media_grid_all_languages() {
		if ( 'query-attachments' !== $this->get_ajax_action() ) {
			return null;
		}

		$referer = wp_get_raw_referer();
		if ( ! $referer ) {
			return null;
		}

		$query = [];
		parse_str( (string) wpml_parse_url( $referer, PHP_URL_QUERY ), $query );

		return isset( $query['lang'] ) && 'all' === $query['lang'] ? 'all' : null;
	}

	protected function get_ajax_action() {
		return (string) filter_input( INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	}

	protected function get_cookie_name() {
		return $this->cookieLanguage->getAjaxCookieName( $this->is_admin_action_from_referer() );
	}

	private function is_admin_action_from_referer() {
		$adminSlug = basename( trim( admin_url(), '/' ) );

		return (bool) Maybe::of( $_SERVER )
						   ->map( Obj::prop( 'HTTP_REFERER' ) )
						   ->map( Str::pos( '/' . $adminSlug . '/' ) )
						   ->getOrElse( false );
	}
}
