<?php

namespace WPML\TM\Settings;

use WPML\Collect\Support\Collection;
use WPML\Core\BackgroundTask\Command\UpdateBackgroundTask;
use WPML\Core\BackgroundTask\Model\BackgroundTask;
use WPML\BackgroundTask\AbstractTaskEndpoint;
use WPML\Core\BackgroundTask\Service\BackgroundTaskService;
use WPML\Element\API\PostTranslations;
use WPML\FP\Obj;
use WPML\Setup\Option;
use WPML\TM\AutomaticTranslation\Actions\Actions as AutotranslateActions;

class ProcessNewTranslatableFields extends AbstractTaskEndpoint {
	const LOCK_TIME              = 5;
	const MAX_RETRIES            = 10;
	const DESCRIPTION            = 'Updating affected posts for changes in translatable fields %s.';
	const POSTS_PER_REQUEST      = 10;
	const UNKNOWN_TOTAL_SENTINEL = 1;

	private $wpdb;

	private $postActions;

	private $autotranslateActions;

	public function __construct(
		\wpdb $wpdb,
		\WPML_TM_Post_Actions $postActions,
		AutotranslateActions $autotranslateActions,
		UpdateBackgroundTask $updateBackgroundTask,
		BackgroundTaskService $backgroundTaskService
	) {
		$this->wpdb                      = $wpdb;
		$this->postActions               = $postActions;
		$this->autotranslateActions      = $autotranslateActions;

		parent::__construct( $updateBackgroundTask, $backgroundTaskService );
	}

	public function runBackgroundTask( BackgroundTask $task ) {
		$payload         = $task->getPayload();
		$fieldsToProcess = Obj::propOr( [], 'newFields', $payload );
		$lastPostId      = (int) Obj::propOr( 0, 'lastPostId', $payload );
		$postIds         = $this->getPosts( $fieldsToProcess, $lastPostId );
		$task->setTotalCount( 0 );

		if ( count( $postIds ) > 0 ) {
			$this->updateNeedsUpdate( $postIds );
			$payload['lastPostId'] = (int) max( $postIds );
			$task->setPayload( $payload );
			$task->addCompletedCount( count( $postIds ) );
			$task->setRetryCount( 0 );
		} else {
			$task->setTotalCount( $task->getCompletedCount() );
			$task->finish();
		}

		return $task;
	}

	public function getDescription( Collection $data ) {
		return sprintf(
			__( self::DESCRIPTION, 'sitepress' ),
			implode( ', ', $data->get( 'newFields', [] ) )
		);
	}

	public function getTotalRecords( Collection $data ) {
		return self::UNKNOWN_TOTAL_SENTINEL;
	}

	private function getPosts( array $fields, $lastPostId ) {
		if ( empty( $fields ) ) {
			return [];
		}
		$wpdb = $this->wpdb;

		return $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT pm.post_id
						FROM {$wpdb->postmeta} AS pm
						INNER JOIN {$wpdb->posts} AS p
							ON p.ID = pm.post_id
						WHERE pm.meta_key IN (" . implode( ', ', array_fill( 0, count( $fields ), '%s' ) ) . ")
							AND pm.post_id > %d
						ORDER BY pm.post_id ASC
						LIMIT %d",
				array_merge( $fields, [ $lastPostId, self::POSTS_PER_REQUEST ] )
			)
		);
	}

	private function isTranslateEverythingActive() {
		return \WPML_TM_ATE_Status::is_enabled_and_activated()
			&& Option::shouldTranslateEverything();
	}

	public function updateNeedsUpdate( array $postIds ) {
		foreach ( $postIds as $postId ) {
			$translations = PostTranslations::getIfOriginal( $postId );
			$updater      = $this->postActions->get_translation_statuses_updater( $postId, $translations );
			$needsUpdate  = $updater();
			if ( $needsUpdate && $this->isTranslateEverythingActive() ) {
				$this->autotranslateActions->sendToTranslation( $postId );
			}
		}
	}
}
