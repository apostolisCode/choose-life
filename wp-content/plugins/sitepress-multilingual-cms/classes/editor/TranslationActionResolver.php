<?php

namespace WPML\TM\Editor;

use WPML\FP\Obj;
use WPML\LIB\WP\User;
use WPML\TM\API\Jobs;
use WPML\TM\ATE\AutoTranslate\Eligibility;
use WPML\TranslationRoles\SelfTranslator;

class TranslationActionResolver {

	const ACTION_AUTO_TRANSLATE = 'auto-translate';
	const ACTION_NAVIGATE       = 'navigate';
	const ACTION_BLOCKED        = 'blocked';

	const VERB_TRANSLATE = 'translate';
	const VERB_EDIT      = 'edit';
	const VERB_REVIEW    = 'review';

	const VERBS = [ self::VERB_TRANSLATE, self::VERB_EDIT, self::VERB_REVIEW ];

	private $editor;

	private $eligibility;

	private $selfTranslator;

	public function __construct( Editor $editor, Eligibility $eligibility, SelfTranslator $selfTranslator ) {
		$this->editor         = $editor;
		$this->eligibility    = $eligibility;
		$this->selfTranslator = $selfTranslator;
	}

	public function resolve( array $intent ) {
		$trid           = (int) Obj::prop( 'trid', $intent );
		$targetLanguage = (string) Obj::prop( 'targetLanguage', $intent );
		$verb           = Obj::propOr( self::VERB_TRANSLATE, 'verb', $intent );

		if ( ! $trid || ! $targetLanguage || ! in_array( $verb, self::VERBS, true ) ) {
			return $this->blocked( 'invalid-intent', __( 'The translation request is malformed.', 'sitepress' ) );
		}

		$postId = (int) \SitePress::get_original_element_id_by_trid( $trid );
		if ( ! $postId ) {
			return $this->blocked( 'post-not-found', __( 'Post cannot be found by trid', 'sitepress' ) );
		}

		$sourceLanguage = $this->getSourceLanguage( $postId );

		if ( ! $this->canTranslatePair( $sourceLanguage, $targetLanguage, $postId ) ) {
			return $this->blocked( 'not-allowed', __( 'You are not allowed to translate this language pair.', 'sitepress' ) );
		}

		if ( self::VERB_TRANSLATE === $verb && $this->eligibility->shouldAutoTranslate( $trid, $postId, $targetLanguage ) ) {
			return $this->translateAutomatically( $trid, $postId, $targetLanguage, Obj::prop( 'currentUrl', $intent ) );
		}

		return $this->mapEditorResponse(
			$this->editor->open( $this->buildOpenParams( $intent, $trid, $targetLanguage, $sourceLanguage, $verb ) ),
			Obj::prop( 'currentUrl', $intent )
		);
	}

	private function canTranslatePair( $langFrom, $langTo, $postId ) {
		$allowed = (bool) apply_filters( 'wpml_is_translator', false, User::getCurrentId(), [
			'lang_from'      => $langFrom,
			'lang_to'        => $langTo,
			'admin_override' => User::isAdministrator(),
			'post_id'        => $postId,
		] );

		if ( ! $allowed ) {
			$allowed = $this->selfTranslator->can_translate_pair( $langFrom, $langTo );
		}

		return $allowed;
	}

	private function translateAutomatically( $trid, $postId, $targetLanguage, $currentUrl ) {
		global $wpml_translation_job_factory;

		$lock = ( new OpenLock() )->acquire( [ 'trid' => $trid, 'language_code' => $targetLanguage ] );

		try {
			$existing = Jobs::getTridJob( $trid, $targetLanguage );

			if ( $existing
			     && (int) Obj::propOr( 0, 'automatic', $existing )
			     && ! (int) Obj::propOr( 0, 'translated', $existing ) ) {
				return [
					'action' => self::ACTION_AUTO_TRANSLATE,
					'jobId'  => (int) Obj::prop( 'job_id', $existing ),
				];
			}

			$jobId = $wpml_translation_job_factory->create_local_post_job( $postId, $targetLanguage );
		} finally {
			if ( $lock ) {
				$lock->release();
			}
		}

		$job = Jobs::get( (int) $jobId );

		if ( ! $job ) {
			/* translators: Message shown when WPML could not make the translation job for a piece of content. */
			return $this->blocked( 'job-not-created', __( 'Job could not be created', 'sitepress' ) );
		}

		if ( Obj::prop( 'automatic', $job ) ) {
			return [
				'action' => self::ACTION_AUTO_TRANSLATE,
				'jobId'  => $jobId,
			];
		}

		return [
			'action' => self::ACTION_NAVIGATE,
			'editor' => 'wpml',
			'url'    => Jobs::getEditUrl( $currentUrl, $jobId ),
			'jobId'  => $jobId,
		];
	}

	private function buildOpenParams( $intent, $trid, $targetLanguage, $sourceLanguage, $verb ) {
		$params = [
			'trid'                 => $trid,
			'language_code'        => $targetLanguage,
			'source_language_code' => $sourceLanguage,
		];

		if ( self::VERB_REVIEW === $verb ) {
			$params['preview'] = 1;
		}

		$currentUrl = Obj::prop( 'currentUrl', $intent );
		if ( $currentUrl ) {
			$params['return_url'] = $currentUrl;
		}

		return $params;
	}

	private function mapEditorResponse( $response, $currentUrl ) {
		$jobObject = Obj::prop( 'jobObject', $response );
		$jobId     = $jobObject ? (int) $jobObject->get_id() : null;

		switch ( Obj::prop( 'editor', $response ) ) {
			case \WPML_TM_Editors::ATE:
				$answer = [ 'action' => self::ACTION_NAVIGATE, 'editor' => 'ate', 'url' => Obj::prop( 'url', $response ) ];
				break;
			case \WPML_TM_Editors::WP:
				$answer = [ 'action' => self::ACTION_NAVIGATE, 'editor' => 'wp', 'url' => Obj::prop( 'url', $response ) ];
				break;
			case \WPML_TM_Editors::WPML:
				$answer = [ 'action' => self::ACTION_NAVIGATE, 'editor' => 'wpml', 'url' => Jobs::getEditUrl( $currentUrl, $jobId ) ];
				break;
			default:
				return $this->blocked( 'editor-unavailable', __( 'The translation cannot be opened right now.', 'sitepress' ) );
		}

		if ( $jobId ) {
			$answer['jobId'] = $jobId;
		}

		return $answer;
	}

	private function getSourceLanguage( $postId ) {
		global $sitepress;

		return $sitepress->post_translations()->get_element_lang_code( $postId );
	}

	private function blocked( $reason, $message ) {
		return [
			'action'  => self::ACTION_BLOCKED,
			'reason'  => $reason,
			'message' => $message,
		];
	}
}
