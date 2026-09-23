<?php

class WPML_Single_Url_Cache_Scheduler {

	const CRON_HOOK       = 'wpml_process_single_url_resolution_queue';
	const AJAX_ACTION     = 'wpml_single_url_resolution_worker';
	const DISPATCH_LOCK   = 'wpml_single_url_resolution_dispatch_lock';
	const SIGNATURE_TTL   = 300;
	const CRON_DELAY      = 60;
	const LOOPBACK_ACTION = 'single-url-cache-worker';

	private $shutdown_dispatches = [];

	private $shutdown_registered = false;

	private $scheduled_in_request = [];

	public function schedule( $blog_id = null ) {
		$blog_id = $blog_id ? (int) $blog_id : get_current_blog_id();
		if ( isset( $this->scheduled_in_request[ $blog_id ] ) ) {
			return;
		}
		$this->scheduled_in_request[ $blog_id ] = true;

		$switched = false;

		if ( get_current_blog_id() !== $blog_id ) {
			switch_to_blog( $blog_id );
			$switched = true;
		}

		try {
			if ( ! wp_next_scheduled( self::CRON_HOOK, [ $blog_id ] ) ) {
				wp_schedule_single_event( time() + self::CRON_DELAY, self::CRON_HOOK, [ $blog_id ] );
			}

			if ( false === get_transient( self::DISPATCH_LOCK ) ) {
				set_transient( self::DISPATCH_LOCK, 1, 30 );
				$this->shutdown_dispatches[ $blog_id ] = $blog_id;
			}
		} finally {
			if ( $switched ) {
				restore_current_blog();
			}
		}

		if ( ! $this->shutdown_registered ) {
			$this->shutdown_registered = true;
			add_action( 'shutdown', [ $this, 'dispatch_loopbacks' ], PHP_INT_MAX - 10 );
		}
	}

	public function schedule_recovery( $blog_id, $timestamp ) {
		$blog_id   = (int) $blog_id;
		$timestamp = max( time() + 1, (int) $timestamp );
		$switched  = false;

		if ( get_current_blog_id() !== $blog_id ) {
			switch_to_blog( $blog_id );
			$switched = true;
		}

		try {
			$scheduled = wp_next_scheduled( self::CRON_HOOK, [ $blog_id ] );
			if ( ! $scheduled || $scheduled > $timestamp ) {
				wp_schedule_single_event( $timestamp, self::CRON_HOOK, [ $blog_id ] );
			}
		} finally {
			if ( $switched ) {
				restore_current_blog();
			}
		}
	}

	public function dispatch_loopbacks() {
		foreach ( $this->shutdown_dispatches as $blog_id ) {
			$this->dispatch_loopback( $blog_id );
		}

		$this->shutdown_dispatches = [];
	}

	private function dispatch_loopback( $blog_id ) {
		$timestamp = time();
		$signature = $this->signature( $blog_id, $timestamp );

		if ( ! $signature ) {
			return;
		}

		wp_remote_post(
			get_admin_url( $blog_id, 'admin-ajax.php' ),
			[
				'blocking'    => false,
				'timeout'     => 0.01,
				'redirection' => 0,
				'body'        => [
					'action'    => self::AJAX_ACTION,
					'blog_id'   => $blog_id,
					'timestamp' => $timestamp,
					'signature' => $signature,
				],
			]
		);
	}

	public function signature( $blog_id, $timestamp ) {
		$salt = wp_salt( 'auth' );
		if ( ! is_string( $salt ) || strlen( $salt ) < 32 ) {
			return '';
		}

		return hash_hmac(
			'sha256',
			implode( '|', [ self::LOOPBACK_ACTION, (int) $blog_id, (int) $timestamp ] ),
			$salt
		);
	}

	public function verify( $blog_id, $timestamp, $signature ) {
		if ( abs( time() - (int) $timestamp ) > self::SIGNATURE_TTL ) {
			return false;
		}

		$expected = $this->signature( $blog_id, $timestamp );

		return '' !== $expected
			&& is_string( $signature )
			&& hash_equals( $expected, $signature );
	}

	public function allow_redispatch( $blog_id ) {
		$switched = false;
		if ( get_current_blog_id() !== (int) $blog_id ) {
			switch_to_blog( (int) $blog_id );
			$switched = true;
		}

		try {
			delete_transient( self::DISPATCH_LOCK );
		} finally {
			if ( $switched ) {
				restore_current_blog();
			}
		}
	}
}
