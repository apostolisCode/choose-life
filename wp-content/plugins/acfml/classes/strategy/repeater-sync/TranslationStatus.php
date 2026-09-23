<?php

namespace ACFML\Repeater\Sync;

interface TranslationStatus {

	public function isLevelWithOriginal( $translation );

	public function markNeedsUpdate( $translation );
}
