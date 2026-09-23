<?php

namespace WPML\TM\ATE\Receive;

use WPML\FP\Obj;
use WPML\TM\API\ATE;

class TranslationApplier {

	const APPLIED = 'applied';

	const XLIFF_NOT_READY = 'xliff_not_ready';

	const APPLY_FAILED = 'apply_failed';

	private $ateApi;

	private $grant;

	public function __construct( ATE $ateApi, ?UnfilteredHtmlGrant $grant = null ) {
		$this->ateApi = $ateApi;
		$this->grant  = $grant ?: new UnfilteredHtmlGrant();
	}

	public function applyForPost( $jobId, $postId ) {
		return $this->apply( $jobId, $postId, true );
	}

	public function applyForTerm( $jobId ) {
		return $this->apply( $jobId, 0, false );
	}

	private function apply( $jobId, $postId, $requiresPost ) {
		$xliffUrl = Obj::prop( 'translated_xliff', $this->ateApi->checkJobStatus( $jobId ) );

		if ( ! $xliffUrl ) {
			return self::XLIFF_NOT_READY;
		}

		$this->grant->arm( $jobId );

		$applied = ( ! $requiresPost || $postId ) && $this->ateApi->applyTranslation( $jobId, $postId, $xliffUrl );

		return $applied ? self::APPLIED : self::APPLY_FAILED;
	}
}
