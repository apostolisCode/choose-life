<?php

namespace WPML\TM\Settings;

use WPML\Core\BackgroundTask\Repository\BackgroundTaskRepository;
use WPML\Core\BackgroundTask\Service\BackgroundTaskService;
use function WPML\Container\make;

class PreferenceReapply {

	private $affectedPosts;

	private $backgroundTaskService;

	private $backgroundTaskRepository;

	public function __construct(
		AffectedPostsQuery $affectedPosts,
		BackgroundTaskService $backgroundTaskService,
		BackgroundTaskRepository $backgroundTaskRepository
	) {
		$this->affectedPosts            = $affectedPosts;
		$this->backgroundTaskService    = $backgroundTaskService;
		$this->backgroundTaskRepository = $backgroundTaskRepository;
	}

	public function countAffected( array $fieldNames ): int {
		return $this->affectedPosts->countAffected( $fieldNames );
	}

	public function countAffectedForChanges( array $changes ): int {
		$changeSet = PreferenceChangeSet::fromArray( $changes );

		return $this->countAffected( $changeSet->fieldNames() )
			+ count( self::extraPostIds( $changeSet ) );
	}

	public function schedule( array $changes, $applyToExisting ) {
		$changeSet = PreferenceChangeSet::fromArray( $changes );
		if ( $changeSet->isEmpty() ) {
			return null;
		}

		PreferenceReapplyConsent::record( $changeSet->namesFor( PreferenceChangeSet::TRANSLATE ) );

		if ( ! $applyToExisting ) {
			return null;
		}

		$extraPostIds = self::extraPostIds( $changeSet );
		if ( ! $this->countAffected( $changeSet->fieldNames() ) && ! $extraPostIds ) {
			return null;
		}

		$endpoint = make( ReapplyPreferenceChanges::class );

		$task = $this->backgroundTaskService->add(
			$endpoint,
			wpml_collect(
				[
					'changes'      => $changeSet->toArray(),
					'extraPostIds' => $extraPostIds,
					'lastPostId'   => 0,
					'processed'    => [],
				]
			)
		);

		return $task ? $task->getTaskId() : null;
	}

	public function getSummary( $taskId ) {
		$task = $this->backgroundTaskRepository->getByTaskId( $taskId );

		return $task ? ReapplyPreferenceChanges::summaryOf( $task ) : null;
	}

	private static function extraPostIds( PreferenceChangeSet $changeSet ) {
		$postIds = apply_filters( 'wpml_preference_reapply_affected_posts', [], $changeSet->toArray() );

		return array_values( array_unique( array_filter( array_map( 'intval', (array) $postIds ) ) ) );
	}
}
