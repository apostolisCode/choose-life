<?php

namespace WPML\TM\Editor;

use WPML\FP\Cast;
use WPML\FP\Fns;
use WPML\FP\Logic;
use WPML\FP\Obj;
use WPML\FP\Relation;
use WPML\LIB\WP\Hooks;
use WPML\TM\API\Jobs;
use WPML\UIPage;
use function WPML\Container\make;
use function WPML\FP\pipe;

class ManualJobCreationErrorNotice implements \IWPML_Backend_Action {

	const RETRY_LIMIT = 3;

	const ROUTE = 'ate-job-creation-error-notice';

	public function add_hooks() {
		if ( \WPML_TM_ATE_Status::is_enabled() ) {

			Hooks::onAction( 'wp_loaded' )
			     ->then( function () {
				     $notices = make( \WPML_Notices::class );

				     if ( isset( $_GET['ateJobCreationError'] ) ) {
					     if ( ! self::policy()->permits() ) {
						     return;
					     }
					     $notice = $notices->create_notice( __CLASS__, $this->getContent( $_GET ) );

					     $notice->set_css_class_types( 'error' );
					     $notice->set_dismissible( false );

					     $notices->add_notice( $notice );
				     } else {
					     $notices->remove_notice( 'default', __CLASS__ );
				     }
			     } );
		}
	}

	public static function policy() {
		return \WPML\Request\Policy\Registry::declare(
			\WPML\Request\Policy\Registry::PSEUDO_ROUTE,
			self::ROUTE,
			\WPML\Request\Policy\Policy::capability(
				[ 'translate', 'manage_translations' ],
				\WPML\Request\Policy\Authenticity::none( 'WPML\'s own redirect after a failed ATE job creation; the notice text comes from fixed templates, the job id only selects the retry link' )
			)
		);
	}

	private function getContent( array $params ) {
		$isATENotActiveError  = pipe( Obj::prop( 'ateJobCreationError' ), Cast::toInt(), Relation::equals( Editor::ATE_IS_NOT_ACTIVE ) );
		$isRetryLimitExceeded = pipe( Obj::prop( 'jobId' ), [ ATERetry::class, 'getCount' ], Relation::gt( self::RETRY_LIMIT ) );

		return Logic::cond( [
			[ $isATENotActiveError, [ self::class, 'ateNotActiveMessage' ] ],
			[ $isRetryLimitExceeded, [ self::class, 'retryMessage' ] ],
			[ Fns::always( true ), [ self::class, 'retryFailedMessage' ] ]
		], $params );
	}

	public static function retryMessage( array $params ) {
		$returnUrl  = \remove_query_arg( [ 'ateJobCreationError', 'jobId' ], Jobs::getCurrentUrl() );
		$jobId      = Obj::prop( 'jobId', $params );

		if ( ! is_numeric( $jobId ) ) {
			return sprintf(
				'<div class="wpml-display-flex wpml-display-flex-center">%1$s</div>',
				__( "WPML didn't manage to translate this page.", 'sitepress' )
			);
		}

		$jobEditUrl = Jobs::getEditUrl( $returnUrl, (int) $jobId );

		$fallbackErrorMessage = sprintf(
			'<div class="wpml-display-flex wpml-display-flex-center">%1$s <a class="button wpml-margin-left-sm" href="%2$s">%3$s</a></div>',
			__( "WPML didn't manage to translate this page.", 'sitepress' ),
			$jobEditUrl,
			/* translators: Button label in an error notice: do the same thing once more. Verb phrase, imperative. */
			__( 'Try again', 'sitepress' )
		);

		$tryAgainTextLink = sprintf( '<a href="%1$s">%2$s</a>',
			$jobEditUrl,
			/* translators: Button label in an error notice: do the same thing once more. Verb phrase, imperative. */
			__( 'Try again', 'sitepress' ) );

		$ateApiErrorMessage = ATEDetailedErrorMessage::readDetailedError( $tryAgainTextLink );

		return $ateApiErrorMessage ?: $fallbackErrorMessage;
	}

	public static function retryFailedMessage() {
		$support_url          = \WPML\OutboundLinks\OutboundLinks::to(
			\WPML\UserInterface\Web\Core\SharedKernel\Domain\SupportForumUrl::URL,
			array(
				'medium'   => 'notice',
				'campaign' => 'support',
			)
		);
		$fallbackErrorMessage = '<div>' .
		                        sprintf(
			                        /* translators: %s: link to WPML support. */
			                        __( 'WPML tried to translate this page three times and failed. To get it fixed, contact %s', 'sitepress' ),
			                        /* translators: Link text inside a sentence about a problem with automatic translation; it opens the WPML support pages. It is the name of the support team, a noun. */
			                        '<a target=\'_blank\' href="' . esc_url( $support_url ) . '">' . __( 'WPML support', 'sitepress' ) . '</a>'
		                        ) . '</div>';

		$ateApiErrorMessage = ATEDetailedErrorMessage::readDetailedError();

		return $ateApiErrorMessage ?: $fallbackErrorMessage;

	}

	public static function ateNotActiveMessage() {
		return '<div>' .
		       sprintf(
			       /* translators: %s: link to the Translation Management Dashboard. */
			       __( 'WPML’s <b>Advanced Translation Editor</b> is enabled but not activated. Go to %s to resolve the issue.', 'sitepress' ),
			       '<a href="' . UIPage::getTMDashboard() . '">' . __( 'WPML Translation Management Dashboard', 'sitepress' ) . '</a>'
		       )
		       . '</div>';
	}
}
