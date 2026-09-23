<?php

namespace WPML\TM\Settings;

use WPML\BackgroundTask\AbstractTaskEndpoint;
use WPML\Collect\Support\Collection;
use WPML\Core\BackgroundTask\Command\UpdateBackgroundTask;
use WPML\Core\BackgroundTask\Model\BackgroundTask;
use WPML\Core\BackgroundTask\Service\BackgroundTaskService;
use WPML\FP\Obj;

class ReapplyPreferenceChanges extends AbstractTaskEndpoint {

	const LOCK_TIME         = 5;
	const MAX_RETRIES       = 10;
	const POSTS_PER_REQUEST = 10;

	private $affectedPosts;

	private $processNewTranslatableFields;

	private $elementFactory;

	public function __construct(
		AffectedPostsQuery $affectedPosts,
		ProcessNewTranslatableFields $processNewTranslatableFields,
		\WPML_Translation_Element_Factory $elementFactory,
		UpdateBackgroundTask $updateBackgroundTask,
		BackgroundTaskService $backgroundTaskService
	) {
		$this->affectedPosts                = $affectedPosts;
		$this->processNewTranslatableFields = $processNewTranslatableFields;
		$this->elementFactory               = $elementFactory;

		parent::__construct( $updateBackgroundTask, $backgroundTaskService );
	}

	public function runBackgroundTask( BackgroundTask $task ) {
		$payload   = (array) $task->getPayload();
		$changeSet = PreferenceChangeSet::fromArray( (array) Obj::propOr( [], 'changes', $payload ) );
		$processed = (array) Obj::propOr( [], 'processed', $payload );
		$postIds   = $this->nextPage(
			$changeSet,
			$this->extraPostIds( $payload ),
			(int) Obj::propOr( 0, 'lastPostId', $payload )
		);

		if ( $postIds ) {
			$payload['processed']  = $this->applyToPosts( $postIds, $changeSet, $processed );
			$payload['lastPostId'] = max( $postIds );
			$task->setPayload( $payload );
			$task->addCompletedCount( count( $postIds ) );
			$task->setRetryCount( 0 );
		} else {
			$payload['processed'] = $processed;
			$task->setPayload( $payload );
			$task->setTotalCount( $task->getCompletedCount() );
			$task->finish();
		}

		do_action(
			'wpml_preference_reapply_batch_completed',
			$postIds,
			$changeSet->toArray(),
			self::summaryOf( $task )
		);

		return $task;
	}

	public function getDescription( Collection $data ) {
		return sprintf(
			/* translators: %s is a comma separated list of custom field names. */
			__( 'Updating existing content for the changed field preferences: %s.', 'sitepress' ),
			implode( ', ', array_keys( (array) $data->get( 'changes', [] ) ) )
		);
	}

	public function getTotalRecords( Collection $data ) {
		$changeSet = PreferenceChangeSet::fromArray( (array) $data->get( 'changes', [] ) );

		return $this->affectedPosts->countAffected( $changeSet->fieldNames() )
			+ count( $this->extraPostIds( [ 'extraPostIds' => $data->get( 'extraPostIds', [] ) ] ) );
	}

	public static function summaryOf( BackgroundTask $task ) {
		$processed = (array) Obj::propOr( [], 'processed', (array) $task->getPayload() );

		return [
			'taskId'      => $task->getTaskId(),
			'isCompleted' => (bool) $task->isStatusCompleted(),
			'total'       => (int) $task->getTotalCount(),
			'completed'   => (int) $task->getCompletedCount(),
			'processed'   => [
				PreferenceChangeSet::TRANSLATE => (int) Obj::propOr( 0, PreferenceChangeSet::TRANSLATE, $processed ),
				PreferenceChangeSet::COPY      => (int) Obj::propOr( 0, PreferenceChangeSet::COPY, $processed ),
				PreferenceChangeSet::COPY_ONCE => (int) Obj::propOr( 0, PreferenceChangeSet::COPY_ONCE, $processed ),
				PreferenceChangeSet::IGNORE    => (int) Obj::propOr( 0, PreferenceChangeSet::IGNORE, $processed ),
			],
		];
	}

	private function extraPostIds( array $payload ) {
		$ids = array_map( 'intval', (array) Obj::propOr( [], 'extraPostIds', $payload ) );

		return array_values( array_unique( array_filter( $ids ) ) );
	}

	private function nextPage( PreferenceChangeSet $changeSet, array $extraPostIds, $lastPostId ) {
		$postIds = $this->affectedPosts->page( $changeSet->fieldNames(), $lastPostId, self::POSTS_PER_REQUEST );

		$remaining = array_filter(
			$extraPostIds,
			function ( $id ) use ( $lastPostId ) {
				return $id > $lastPostId;
			}
		);

		if ( $remaining ) {
			$postIds = array_values( array_unique( array_merge( $postIds, $remaining ) ) );
			sort( $postIds );
			$postIds = array_slice( $postIds, 0, self::POSTS_PER_REQUEST );
		}

		return $postIds;
	}

	private function applyToPosts( array $postIds, PreferenceChangeSet $changeSet, array $processed ) {
		$namesByTransition = $changeSet->namesByTransition();
		$needsUpdate       = [];

		foreach ( $postIds as $postId ) {
			$postId     = (int) $postId;
			$sourceMeta = get_post_meta( $postId );
			$sourceMeta = is_array( $sourceMeta ) ? $sourceMeta : [];

			foreach ( $namesByTransition as $transition => $names ) {
				$present = array_values( array_intersect( $names, array_keys( $sourceMeta ) ) );
				if ( ! $present ) {
					continue;
				}

				$processed[ $transition ] = ( isset( $processed[ $transition ] ) ? (int) $processed[ $transition ] : 0 ) + 1;

				if ( PreferenceChangeSet::TRANSLATE === $transition ) {
					$needsUpdate[] = $postId;
				} elseif ( PreferenceChangeSet::COPY === $transition ) {
					$this->applyCopy( $postId, $present );
				} elseif ( PreferenceChangeSet::COPY_ONCE === $transition ) {
					$this->applyCopyOnce( $postId, $present, $sourceMeta );
				}

			}
		}

		if ( $needsUpdate ) {
			$this->processNewTranslatableFields->updateNeedsUpdate( array_values( array_unique( $needsUpdate ) ) );
		}

		return $processed;
	}

	private function applyCopy( $postId, array $fieldNames ) {
		$this->syncFor( $fieldNames )->sync_all_custom_fields( $postId );
	}

	private function applyCopyOnce( $postId, array $fieldNames, array $sourceMeta ) {
		$translationIds = $this->translationIds( $postId );
		if ( ! $translationIds ) {
			return;
		}

		$sync = $this->syncFor( $fieldNames );

		foreach ( $translationIds as $translationId ) {
			$targetMeta = get_post_meta( $translationId );
			$targetMeta = is_array( $targetMeta ) ? $targetMeta : [];

			foreach ( $fieldNames as $metaKey ) {
				if ( ! isset( $sourceMeta[ $metaKey ] ) ) {
					continue;
				}

				$values = isset( $targetMeta[ $metaKey ] ) && ! empty( $targetMeta[ $metaKey ] )
					? [ $targetMeta[ $metaKey ] ]
					: [];

				$values = apply_filters(
					'wpml_custom_field_values',
					$values,
					[
						'post_id'                   => $translationId,
						'meta_key'                  => $metaKey,
						'custom_fields_translation' => WPML_COPY_ONCE_CUSTOM_FIELD,
					]
				);

				if ( empty( $values ) ) {
					$sync->sync_custom_field( $postId, $translationId, $metaKey );
				}
			}
		}
	}

	private function translationIds( $postId ) {
		$element        = $this->elementFactory->create( $postId, 'post' );
		$translationIds = [];

		foreach ( (array) $element->get_translations() as $translation ) {
			$translationId = (int) $translation->get_element_id();
			if ( $translationId && $translationId !== (int) $postId ) {
				$translationIds[] = $translationId;
			}
		}

		return $translationIds;
	}

	protected function syncFor( array $fieldNames ) {
		return new \WPML_Sync_Custom_Fields( $this->elementFactory, array_values( $fieldNames ) );
	}
}
