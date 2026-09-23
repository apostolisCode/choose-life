<?php

if ( ! function_exists( 'icl_repair_broken_type_and_language_assignments' ) ) {

	function icl_repair_broken_type_and_language_assignments() {
		( new WPML\Troubleshooting\Ajax\RepairTypeAssignmentsAjaxController() )->handle();
	}
}
