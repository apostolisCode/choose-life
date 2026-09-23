<?php

namespace WPML\TM\ATE\Hooks;

use WPML\Core\Security\ExecutionContext\ExecutionContext;
use WPML\Core\Security\ExecutionContext\ExecutionContextHolder;
use WPML\Core\Security\ExecutionContext\MachineCallbackProof;
use WPML\Request\Adapter\AdminPost;
use WPML\Request\Policy\Authenticity;
use WPML\Request\Policy\Policy;
use WPML\TM\ATE\ReturnToken;
use WPML\TM\ATE\ReturnUrl;
use WPML\TM\Jobs\Authorization\AuthorizedJobResolver;

class ReturnCommand implements \IWPML_Action {

	const ACTION    = 'wpml_ate_return';
	const RETURN_TO = 'wpml_return_to';

	const CAPABILITIES = [ 'translate', 'manage_translations' ];

	const RETURN_VERIFIER = 'ate-return-token';

	const ATE_PARAMS = [ 'ate_original_id', 'complete', 'complete_no_changes', 'ate_status', 'back', 'ate_job_id', 'message' ];

	private $removeTranslationDuplicateStatus;

	private $resolver;

	private $accessDenied;

	private $resignTranslator;

	private $redirect;

	public function __construct(
		callable $removeTranslationDuplicateStatus,
		AuthorizedJobResolver $resolver,
		\WPML_TM_AMS_Synchronize_Users_On_Access_Denied $accessDenied,
		callable $resignTranslator,
		?callable $redirect = null
	) {
		$this->removeTranslationDuplicateStatus = $removeTranslationDuplicateStatus;
		$this->resolver                         = $resolver;
		$this->accessDenied                     = $accessDenied;
		$this->resignTranslator                 = $resignTranslator;
		$this->redirect                         = $redirect ?: function ( $url ) {
			wp_safe_redirect( $url, 302, 'WPML' );
			exit;
		};
	}

	public function add_hooks() {
		AdminPost::register( self::ACTION, $this->policy(), [ $this, 'handle' ] );
	}

	public function policy() {
		return Policy::capability(
			self::CAPABILITIES,
			Authenticity::verifier(
				[ $this, 'verifyReturnToken' ],
				'ATE return token: HMAC-SHA256 over the WPML job id and the user id, keyed with wp_salt(\'auth\'), in ?' . ReturnToken::PARAM . '; the job is the one the ATE ids in the request resolve to for the acting principal (for the editor\'s bare back: the job the ATE id maps to, whatever its current state)'
			)
		);
	}

	public function verifyReturnToken() {
		$wpmlJobId = $this->signedJobId();

		return $wpmlJobId > 0 && ReturnToken::verifyRequest( $wpmlJobId );
	}

	public static function url( $destination, $token ) {
		return add_query_arg(
			[
				'action'           => self::ACTION,
				self::RETURN_TO    => rawurlencode( (string) $destination ),
				ReturnToken::PARAM => (string) $token,
			],
			admin_url( 'admin-post.php' )
		);
	}

	public function handle() {
		$destination   = $this->destination();
		$ateOriginalId = isset( $_GET['ate_original_id'] ) ? (int) $_GET['ate_original_id'] : 0;
		$ateJobId      = isset( $_GET['ate_job_id'] ) ? (int) $_GET['ate_job_id'] : 0;
		$completed     = ! empty( $_GET['complete'] );
		$message       = isset( $_GET['message'] ) && is_string( $_GET['message'] ) ? sanitize_text_field( wp_unslash( $_GET['message'] ) ) : '';

		if ( $completed && $ateOriginalId > 0 ) {
			call_user_func( $this->removeTranslationDuplicateStatus, $ateOriginalId );

			$context = $this->verifiedReturnContext();

			if ( $context ) {
				ExecutionContextHolder::coverDeferredWork( $context );
			}

			do_action( 'wpml_on_back_from_ate_manual_translation', $ateOriginalId, $context );
		}

		if ( '' !== $message ) {
			if ( $this->accessDenied->is_access_denied_message( $message ) ) {
				$destination = $this->accessDenied->handle( $ateJobId, $destination );
			} elseif ( $ateJobId > 0 ) {
				$ownJobId = $this->localJobId( $ateJobId, AuthorizedJobResolver::ACCESS_OWN );
				if ( $ownJobId > 0 ) {
					call_user_func( $this->resignTranslator, $ownJobId );
				}
			}

			$destination = add_query_arg( 'message', rawurlencode( $message ), $destination );
		}

		call_user_func( $this->redirect, $destination );
	}

	private function verifiedReturnContext() {
		$wpmlJobId = $this->signedJobId();

		if ( $wpmlJobId < 1 ) {
			return null;
		}

		return ExecutionContext::trusted( MachineCallbackProof::forVerifiedCallback( $wpmlJobId, self::RETURN_VERIFIER ) );
	}

	private function signedJobId() {
		$signed = $this->signedReturnJobId();
		if ( $signed > 0 ) {
			return $signed;
		}

		$ateId = isset( $_GET['ate_original_id'] ) ? (int) $_GET['ate_original_id'] : 0;
		if ( $ateId <= 0 ) {
			$ateId = isset( $_GET['ate_job_id'] ) ? (int) $_GET['ate_job_id'] : 0;
		}

		return $this->resolver->localIdOfAteId( $ateId );
	}

	private function signedReturnJobId() {
		$query = (string) wp_parse_url( $this->destination(), PHP_URL_QUERY );
		if ( '' === $query || false === strpos( $query, 'ate-return-job' ) ) {
			return 0;
		}

		return preg_match( '/(?:^|&)ate-return-job=(\\d+)(?:&|$)/', $query, $matches )
			? (int) $matches[1]
			: 0;
	}

	private function localJobId( $ateId, $access ) {
		if ( $ateId <= 0 ) {
			return 0;
		}

		$job = $this->resolver->byAteId( ExecutionContextHolder::current(), $ateId, $access );

		return $job ? (int) $job->localId() : 0;
	}

	private function destination() {
		$returnTo = isset( $_GET[ self::RETURN_TO ] ) && is_string( $_GET[ self::RETURN_TO ] ) ? esc_url_raw( wp_unslash( $_GET[ self::RETURN_TO ] ) ) : '';

		return ReturnUrl::sanitize( $returnTo );
	}
}
