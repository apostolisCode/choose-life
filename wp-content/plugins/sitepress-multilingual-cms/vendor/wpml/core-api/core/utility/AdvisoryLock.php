<?php

namespace WPML\Utilities;

class AdvisoryLock {

	private $wpdb;

	private $name;

	public function __construct( \wpdb $wpdb, string $name ) {
		$this->wpdb = $wpdb;
		$this->name = 'wpml_' . md5( $wpdb->dbname . '|' . $wpdb->prefix . '|' . $name );
	}

	public function acquire( int $timeoutSeconds ): bool {
		return '1' === $this->wpdb->get_var(
			$this->wpdb->prepare( 'SELECT GET_LOCK(%s, %d)', $this->name, $timeoutSeconds )
		);
	}

	public function release(): void {
		$this->wpdb->query( $this->wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $this->name ) );
	}
}
