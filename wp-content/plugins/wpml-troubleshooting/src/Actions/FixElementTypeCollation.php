<?php

namespace WPML\Troubleshooting\Actions;

class FixElementTypeCollation {

	public function run() {
		repair_el_type_collate();
	}
}
