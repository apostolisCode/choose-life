<?php

namespace WPML\Troubleshooting\Integration\TranslationManagement;

use WPML_Translation_Jobs_Migration;
use WPML_TM_Job_Entity;
use WPML_TM_Jobs_Repository;
use WPML_TM_Jobs_Search_Params;

class WPML_TM_Troubleshooting_Fix_Translation_Jobs_TP_ID {

	const AJAX_ACTION = 'wpml-fix-translation-jobs-tp-id';

	private $jobs_migration;
	private $jobs_repository;

	public function __construct( WPML_Translation_Jobs_Migration $jobs_migration, WPML_TM_Jobs_Repository $jobs_repository ) {
		$this->jobs_migration  = $jobs_migration;
		$this->jobs_repository = $jobs_repository;
	}

	public function add_hooks() {
		add_action(
			'wpml_troubleshooting_after_fix_element_type_collation',
			array(
				$this,
				'render_troubleshooting_section',
			)
		);
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		\WPML\Request\Adapter\Ajax::register( self::AJAX_ACTION, \WPML\Request\Policy\Policy::capability( [ 'wpml_manage_troubleshooting', 'manage_translations' ], \WPML\Request\Policy\Authenticity::actionNonce( self::AJAX_ACTION, 'nonce' ) ), array( $this, 'fix_tp_id_ajax' ) );

	}

	public function fix_tp_id_ajax() {
		if ( isset( $_POST['nonce'], $_POST['job_ids'] ) && wp_verify_nonce( $_POST['nonce'], self::AJAX_ACTION ) ) {
			if ( '' === trim( (string) $_POST['job_ids'] ) ) {
				wp_send_json_error( __( 'No translation job IDs were provided.', 'wpml-troubleshooting' ) );
				return;
			}

			$job_ids = array_map( 'intval', explode( ',', filter_var( $_POST['job_ids'], FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) );
			$jobs    = array();

			foreach ( $job_ids as $job_id ) {
				if ( ! $job_id ) {
					continue;
				}

				$params = new WPML_TM_Jobs_Search_Params();
				$params->set_scope( WPML_TM_Jobs_Search_Params::SCOPE_REMOTE );
				$params->set_job_types( array( WPML_TM_Job_Entity::POST_TYPE, WPML_TM_Job_Entity::PACKAGE_TYPE ) );
				$params->set_local_job_id( $job_id );

				$job = current( $this->jobs_repository->get( $params )->getIterator()->getArrayCopy() );
				if ( $job ) {
					$jobs[] = $job;
				}
			}

			if ( ! $jobs ) {
				wp_send_json_error( __( 'No matching remote translation jobs were found for the provided job IDs.', 'wpml-troubleshooting' ) );
				return;
			}

			try {
				$this->jobs_migration->migrate_jobs( $jobs, true );
			} catch ( \Exception $e ) {
				wp_send_json_error( \WPML\WordPress\ClientSafeError::message( 'Fix tp_id AJAX', $e ), 500 );
				return;
			}

			wp_send_json_success();
		} else {
			wp_send_json_error( __( 'Invalid request: missing job IDs or the security check failed.', 'wpml-troubleshooting' ) );
		}
	}

	public function enqueue_scripts( $hook ) {
		if ( WPML_PLUGIN_FOLDER . '/menu/troubleshooting.php' === $hook ) {
			wp_enqueue_script( 'wpml-fix-tp-id', WPML_TROUBLESHOOTING_URL . '/res/js/fix-tp-id.js', array( 'jquery' ), ICL_SITEPRESS_SCRIPT_VERSION );
		}
	}


	public function render_troubleshooting_section() {
		?>
		<p>
			<input id="wpml_fix_tp_id_text" type="text" value=""/><input id="wpml_fix_tp_id_btn" type="button" class="button-secondary" value="<?php esc_attr_e( 'Fix WPML Translation Jobs "tp_id" field', 'wpml-troubleshooting' ); ?>"/><br/>
			<?php wp_nonce_field( self::AJAX_ACTION, 'wpml-fix-tp-id-nonce' ); ?>
			<small style="margin-left:10px;"><?php /* translators: Explanation under a tool on the Troubleshooting screen. "It" is that tool. "tp_id" and "rid" are field names and stay as they are; "in progress" is the wording of a translation state. */ esc_attr_e( 'Fixes the "tp_id" field of WPML ranslation jobs and set the status to "in progress" (it requires manual action to re-sync translation status + download translations). It accepts comma separated values of translation job IDs (rid).', 'wpml-troubleshooting' ); ?></small>
		</p>
		<?php

	}
}
