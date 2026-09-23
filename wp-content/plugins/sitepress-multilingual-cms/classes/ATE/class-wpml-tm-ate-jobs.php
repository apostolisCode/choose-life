<?php

use WPML\FP\Cast;
use WPML\FP\Maybe;
use WPML\TM\API\Jobs;
use WPML\TM\ATE\JobRecords;
use WPML\TM\ATE\API\RequestException;
use WPML\TM\Jobs\JobLog;
use function WPML\FP\pipe;
use function WPML\FP\partialRight;
use WPML\FP\Obj;
use WPML\FP\Logic;
use WPML\FP\Fns;
use function \WPML\FP\invoke;

class WPML_TM_ATE_Jobs {

	const SKIPPED_NOT_AWAITING_DELIVERY = null;

	private $records;

	public function __construct( JobRecords $records ) {
		$this->records = $records;
	}

	public function get_ate_job_id( $wpml_job_id ) {
		$wpml_job_id = (int) $wpml_job_id;

		return $this->records->get_ate_job_id( $wpml_job_id );
	}

	public function get_wpml_job_id( $ate_job_id ) {
		return Maybe::fromNullable( $ate_job_id )
		            ->map( Cast::toInt() )
		            ->map( [ $this->records, 'get_data_from_ate_job_id' ] )
		            ->map( Obj::prop( 'wpml_job_id' ) )
		            ->map( Cast::toInt() )
		            ->getOrElse( null );
	}

	public function store( $wpml_job_id, $ate_job_data ) {
		$this->records->store( (int) $wpml_job_id, $ate_job_data );
	}

	public function apply( $xliff, $expectedJobId = null ) {
		if ( \WPML\TM\XLIFF\TaxonomyTermXliffReader::isTermXliff( $xliff ) ) {
			global $wpdb, $sitepress;

			return ( new \WPML\TM\ATE\TranslateEverything\TaxonomyTermJobApplier( $wpdb, $sitepress ) )->apply( $xliff, $expectedJobId );
		}

		$factory       = wpml_tm_load_job_factory();
		$xliff_factory = new WPML_TM_Xliff_Reader_Factory( $factory );
		$xliff_reader  = $xliff_factory->general_xliff_reader();
		$job_data      = $xliff_reader->get_data( $xliff );
		if ( is_wp_error( $job_data ) ) {
			throw new RequestException(
				$job_data->get_error_message(),
				$job_data->get_error_code()
			);
		}

		kses_remove_filters();
		$job_data    = $this->filterJobData( $job_data );
		$wpml_job_id = $job_data['job_id'];

		if ( null !== $expectedJobId && (int) $wpml_job_id !== (int) $expectedJobId ) {
			kses_init();

			throw new \Exception(
				sprintf(
					'The delivered XLIFF declares job %d but the delivery was bound to job %d; nothing was applied.',
					(int) $wpml_job_id,
					(int) $expectedJobId
				)
			);
		}

		$blocking_status = $this->getStatusBlockingDelivery( $wpml_job_id );
		if ( null !== $blocking_status ) {
			kses_init();

			JobLog::add(
				'apply_skipped_not_awaiting_delivery',
				[
					'job_id' => (int) $wpml_job_id,
					'status' => $blocking_status,
				]
			);

			return self::SKIPPED_NOT_AWAITING_DELIVERY;
		}

		try {
			$is_saved = wpml_tm_save_data( $job_data, false );
		} catch ( Exception $e ) {
			throw new Exception(
				'The XLIFF file could not be applied to the content of the job ID: ' . $wpml_job_id,
				$e->getCode(),
				$e
			);
		}

		kses_init();

		return $is_saved ? $wpml_job_id : false;
	}

	private function getStatusBlockingDelivery( $wpml_job_id ) {
		$status = Jobs::getStatus( (int) $wpml_job_id );

		if ( null === $status ) {
			return null;
		}

		$not_awaiting_delivery = [ ICL_TM_NOT_TRANSLATED, ICL_TM_ATE_CANCELLED ];

		return in_array( $status, $not_awaiting_delivery, true ) ? $status : null;
	}

	private function filterJobData( $jobData ) {
		$filteredJobData = apply_filters(
			'wpml_tm_ate_job_data_from_xliff',
			$jobData,
			$this->getJobTargetLanguage()
		);

		if ( array_key_exists( 'job_id', $filteredJobData ) && array_key_exists( 'fields', $filteredJobData ) ) {
			$jobData = $filteredJobData;
		}

		return $jobData;
	}

	private function getJobTargetLanguage() {
		$getJobEntityById = partialRight( [
			wpml_tm_get_jobs_repository(),
			'get_job'
		], \WPML_TM_Job_Entity::POST_TYPE );
		$getTargetLangIfEntityExists = Logic::ifElse( Fns::identity(), invoke( 'get_target_language' ), Fns::always( null ) );

		return pipe( Obj::prop( 'rid' ), $getJobEntityById, $getTargetLangIfEntityExists );
	}

	public function is_editing_job( $wpml_job_id ) {
		return $this->records->is_editing_job( $wpml_job_id );
	}

	public function warm_cache( array $wpml_job_ids ) {
		$this->records->warmCache( $wpml_job_ids );
	}
}
