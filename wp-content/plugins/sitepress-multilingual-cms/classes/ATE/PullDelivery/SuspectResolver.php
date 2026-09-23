<?php

namespace WPML\TM\ATE\PullDelivery;

use WPML\TM\Jobs\JobLog;

class SuspectResolver {

	private $reachability;

	private $collector;

	public function __construct( Reachability $reachability, Collector $collector ) {
		$this->reachability = $reachability;
		$this->collector    = $collector;
	}

	public function resolve() {
		if ( Modes::SUSPECT !== State::mode() ) {
			return;
		}

		$reachable = $this->reachability->recheck();

		$this->collector->run();

		$state = State::settleSuspect( $reachable );

		JobLog::maybeInitRequest();
		JobLog::createNewGroup( JobLog::GROUP_ID_DOWNLOAD_JOBS, 'ATE pull delivery suspicion settled' );
		JobLog::add(
			'pull_suspect_settled',
			[
				'reachable' => $reachable,
				'mode'      => $state['mode'],
			]
		);
		JobLog::finishCurrentGroup();
	}
}
