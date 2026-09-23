<?php

namespace WPML\TM\ATE\TranslateEverything;

use WPML\LIB\WP\User;
use WPML\Setup\Option;
use WPML\TM\API\Job\Map;
use WPML\TM\ATE\JobRecords;
use WPML\TM\ATE\Review\TermJob;
use WPML\TM\ATE\Review\ReviewStatus;
use WPML\FP\Obj;
use WPML\TM\Taxonomy\Job\BuildsTaxonomyTermJobModel;
use WPML\TM\XLIFF\TaxonomyTermXliffBuilder;
use WPML_TM_ATE;
use WPML_TM_ATE_API;
use WPML_TM_ATE_Models_Job_Create;
use WPML_TM_Editors;

class TaxonomyTermJobDispatcher {

	use BuildsTaxonomyTermJobModel;

	private $wpdb;

	private $xliff_builder;

	private $user_id;

	public function __construct( \wpdb $wpdb, ?TaxonomyTermXliffBuilder $xliffBuilder = null, ?int $userId = null ) {
		$this->wpdb          = $wpdb;
		$this->xliff_builder = $xliffBuilder ?: new TaxonomyTermXliffBuilder();
		$this->user_id       = null === $userId ? (int) User::getCurrentId() : $userId;
	}

	public function prepareTermJob(
		int $termTaxonomyId,
		string $taxonomy,
		string $sourceLang,
		string $targetLang,
		bool $overwrite
	) {
		$wpdb = $this->wpdb;

		$source = $this->resolveTaxonomyTermSource( $wpdb, $termTaxonomyId, $taxonomy );
		if ( null === $source ) {
			return null;
		}
		list( $term, $trid ) = $source;

		$elementType = 'tax_' . $taxonomy;

		$targetTranslationId = $this->ensureTargetTranslationRow( $trid, $elementType, $targetLang, $sourceLang );
		if ( ! $targetTranslationId ) {
			return null;
		}

		$rid = $this->ensureTranslationStatusRow( $targetTranslationId, $overwrite );
		if ( ! $rid ) {
			return null;
		}

		$jobId = $this->ensureTranslateJobRow( $rid, $term->name );
		if ( ! $jobId ) {
			return null;
		}

		$this->storeSourceSnapshot( $term, $targetLang );

		try {
			$xliff = $this->xliff_builder->build( $term, $sourceLang, $targetLang );
		} catch ( \Throwable $e ) {
			return null;
		}

		return $this->buildTaxonomyJobModel( $jobId, $rid, $termTaxonomyId, $term, $sourceLang, $targetLang, $xliff );
	}

	public function sendToAte( array $jobs, ?string &$error = null ): int {
		if ( ! $jobs ) {
			return 0;
		}

		try {
			$ateApi = \WPML\Container\make( WPML_TM_ATE_API::class );
		} catch ( \Throwable $e ) {
			$error = 'ATE API container resolution failed: ' . $e->getMessage();
			return 0;
		}

		$encodedPayload = wp_json_encode(
			[
				'jobs'           => $jobs,
				'existing_jobs'  => [],
				'auto_translate' => true,
				'preview'        => (bool) Option::shouldBeReviewed(),
				'job_type'       => 'auto',
			]
		);
		if ( false === $encodedPayload ) {
			$error = 'Failed to encode ATE jobs payload.';
			return 0;
		}
		$payload = json_decode( $encodedPayload, true );

		$response = $ateApi->create_jobs( $payload );

		if ( is_wp_error( $response ) ) {
			$error = $response->get_error_message();
			return 0;
		}

		$encodedResponse = wp_json_encode( $response );
		$normalised      = false === $encodedResponse ? null : json_decode( $encodedResponse, true );
		if ( ! is_array( $normalised ) || empty( $normalised['jobs'] ) || ! is_array( $normalised['jobs'] ) ) {
			return 0;
		}

		$jobRecords  = \WPML\Container\make( JobRecords::class );
		$accepted    = 0;
		$acceptedIds = [];

		foreach ( $normalised['jobs'] as $rid => $ateJobId ) {
			$wpmlJobId = (int) Map::fromRid( (int) $rid );
			if ( ! $wpmlJobId ) {
				continue;
			}

			$jobRecords->store( $wpmlJobId, [ JobRecords::FIELD_ATE_JOB_ID => (int) $ateJobId ] );
			$acceptedIds[] = $wpmlJobId;
			++$accepted;
		}

		TermJob::flagAcceptedJobs( $acceptedIds );

		return $accepted;
	}

	private function ensureTargetTranslationRow( int $trid, string $elementType, string $targetLang, string $sourceLang ): int {
		$wpdb = $this->wpdb;

		$existing = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT translation_id FROM {$wpdb->prefix}icl_translations
				WHERE trid = %d AND language_code = %s",
				$trid,
				$targetLang
			),
			ARRAY_A
		);
		if ( $existing ) {
			return (int) $existing['translation_id'];
		}

		$wpdb->insert(
			$wpdb->prefix . 'icl_translations',
			[
				'element_type'         => $elementType,
				'trid'                 => $trid,
				'language_code'        => $targetLang,
				'source_language_code' => $sourceLang,
			],
			[ '%s', '%d', '%s', '%s' ]
		);

		return (int) $wpdb->insert_id;
	}

	private function storeSourceSnapshot( \WP_Term $term, string $targetLang ) {
		if ( ! function_exists( 'update_term_meta' ) ) {
			return;
		}

		$prefix = class_exists( \WPML\TM\Dashboard\Taxonomy\TaxonomyDashboardData::class )
			? \WPML\TM\Dashboard\Taxonomy\TaxonomyDashboardData::WC_SNAPSHOT_META_PREFIX
			: '_wpml_tax_wc_src_';

		$metaText = \WPML\TM\Taxonomy\TranslatableTermMeta::metaText( $term );
		$source   = trim(
			wp_strip_all_tags(
				$term->name . "\n" . ( $term->description ?? '' ) . ( '' !== $metaText ? "\n" . $metaText : '' )
			)
		);

		update_term_meta( (int) $term->term_id, $prefix . $targetLang, $source );
	}

	private function ensureTranslationStatusRow( int $translationId, bool $overwrite ): int {
		$wpdb = $this->wpdb;

		$existing = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT rid, status, review_status FROM {$wpdb->prefix}icl_translation_status WHERE translation_id = %d",
				$translationId
			),
			ARRAY_A
		);

		if ( $existing ) {
			$currentStatus = (int) $existing['status'];

			if ( ReviewStatus::EDITING === Obj::prop( 'review_status', $existing ) ) {
				return 0;
			}

			if ( (int) ICL_TM_COMPLETE === $currentStatus && ! $overwrite ) {
				return 0;
			}
			$wpdb->update(
				$wpdb->prefix . 'icl_translation_status',
				[
					'status'              => ICL_TM_IN_PROGRESS,
					'translation_service' => 'local',
					'translator_id'       => 0,
					'needs_update'        => 0,
				],
				[ 'translation_id' => $translationId ],
				[ '%d', '%s', '%d', '%d' ],
				[ '%d' ]
			);
			return (int) $existing['rid'];
		}

		$wpdb->insert(
			$wpdb->prefix . 'icl_translation_status',
			[
				'translation_id'      => $translationId,
				'status'              => ICL_TM_IN_PROGRESS,
				'translation_service' => 'local',
				'translator_id'       => 0,
				'batch_id'            => 0,
				'needs_update'        => 0,
				'md5'                 => '',
				'translation_package' => '',
			],
			[ '%d', '%d', '%s', '%d', '%d', '%d', '%s', '%s' ]
		);

		return (int) $wpdb->insert_id;
	}

	private function ensureTranslateJobRow( int $rid, string $title ): int {
		return ( new \WPML\TM\Taxonomy\Job\TermJobRowFactory( $this->wpdb, $this->user_id ) )
			->resolveJobRow( $rid, $title );
	}
}
