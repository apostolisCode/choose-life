<?php

namespace WPML\TM\ATE\PullDelivery;

use WPML\TM\ATE\API\ClientRestrictionState;

class LiveSignals {

	public function refresh( array $state ) {
		$restricted = $this->isClientRestricted();
		$spendCap   = $this->spendCap();

		$sameRestriction = $restricted === (bool) $state['client_restricted'];
		$sameCap         = $spendCap === (array) ( isset( $state['spend_cap'] ) ? $state['spend_cap'] : [] );

		if ( $sameRestriction && $sameCap ) {
			return $state;
		}

		return State::update(
			[
				'client_restricted' => $restricted,
				'spend_cap'         => $spendCap,
			]
		);
	}

	protected function isClientRestricted() {
		return ClientRestrictionState::isRestricted();
	}

	protected function spendCap() {
		return Collector::spendCapForState();
	}
}
