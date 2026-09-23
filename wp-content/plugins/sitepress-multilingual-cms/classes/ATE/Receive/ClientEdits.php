<?php

namespace WPML\TM\ATE\Receive;

use WPML\FP\Obj;
use WPML\TM\ATE\Review\ReviewStatus;

class ClientEdits {

	const REASON = 'wpml_translation_edited';

	public static function areInTheWay( $wpmlJob ) {
		return ReviewStatus::EDITING === Obj::prop( 'review_status', $wpmlJob );
	}
}
