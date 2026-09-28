<?php
/**
 * Plugin Name: Choose Life transfer
 * Description: Moves all donations and subscriptions, with the users they belong to and every Subscriber, — and optionally the Cardlink settings — from one Choose Life site to another: Tools → Choose Life transfer. Export on the source, import on the target (dry run first). Remove the plugin when done: the export file holds password hashes and the Cardlink secret.
 * Version: 1.0.0
 * Author: Choose Life
 * Text Domain: choose-life-transfer
 */

defined( 'ABSPATH' ) || exit;

final class CL_Transfer {

	const FORMAT = 'choose-life-transfer/1';
	const PAGE   = 'choose-life-transfer';
	const TYPES  = [ 'donations', 'subscriptions' ];

	/** Cardlink settings (ACF options, every language copy) */
	const SETTINGS_PATTERN = '^_?options_([a-z]{2}_)?(payment_mid|payment_secret|enable_test_environment)$';

	/** post meta the transfer writes itself or that only means something on the source */
	const SKIP_POST_META = [ '_edit_lock', '_edit_last', '_wp_old_slug', '_cl_source_id', '_cl_source_site' ];

	private $report = [];

	public static function init() {
		$self = new self();
		add_action( 'admin_menu', [ $self, 'menu' ] );
		add_action( 'admin_post_cl_transfer_export', [ $self, 'export' ] );
	}

	public function menu() {
		add_management_page( 'Choose Life transfer', 'Choose Life transfer', 'manage_options', self::PAGE, [ $this, 'page' ] );
	}

	/* ------------------------------------------------------------------
	 * Admin page
	 * ------------------------------------------------------------------ */

	public function page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$counts = $this->counts();
		$result = null;
		if ( isset( $_POST['cl_transfer_import'] ) ) {
			check_admin_referer( 'cl_transfer_import' );
			$result = $this->import_request();
		}
		?>
        <div class="wrap">
            <h1>Choose Life transfer</h1>
            <p>Moves all the donations and subscriptions from one site to another, together with every user with the <strong>Subscriber</strong> role and any other user (e.g. an admin) who made a donation or has a subscription, keeping their links (donor ↔ donations ↔ subscription) and, where possible, their ids — Cardlink finds the donation of every recurring charge by the id it had on the first payment.</p>

            <h2>1. Export (on the source site)</h2>
            <p>This site: <?php echo (int) $counts['users']; ?> users (subscribers + other donors), <?php echo (int) $counts['donations']; ?> donations, <?php echo (int) $counts['subscriptions']; ?> subscriptions.</p>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'cl_transfer_export' ); ?>
                <input type="hidden" name="action" value="cl_transfer_export"/>
                <p><label><input type="checkbox" name="settings" value="1" checked/> Include the Cardlink settings (MID, secret, test environment)</label></p>
				<?php submit_button( 'Download export file', 'primary', 'submit', false ); ?>
            </form>
            <p class="description">The file holds password hashes and the Cardlink secret: keep it private and delete it after the import.</p>

            <h2>2. Import (on the target site)</h2>
            <form method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( 'cl_transfer_import' ); ?>
                <p><input type="file" name="file" accept=".json,application/json" required/></p>
                <p><label><input type="checkbox" name="dry_run" value="1" checked/> <strong>Dry run</strong>: only show what would happen</label></p>
                <p><label><input type="checkbox" name="replace" value="1"/> Delete this site's own donations and subscriptions first (e.g. test ones); already transferred ones are updated, not deleted</label></p>
                <p><label><input type="checkbox" name="settings" value="1" checked/> Import the Cardlink settings (if the file has them)</label></p>
				<?php submit_button( 'Import', 'secondary', 'cl_transfer_import', false ); ?>
            </form>
            <p class="description">Running the import again with a newer export updates what was transferred before and adds the new records — nothing is duplicated.</p>

			<?php if ( $result ) {
				$this->print_report( $result );
			} ?>
        </div>
		<?php
	}

	private function counts() {
		return [
			'users'         => count( $this->export_user_ids() ),
			'donations'     => (int) wp_count_posts( 'donations' )->publish,
			'subscriptions' => (int) wp_count_posts( 'subscriptions' )->publish,
		];
	}

	private function print_report( $result ) {
		if ( is_wp_error( $result ) ) {
			printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( $result->get_error_message() ) );

			return;
		}
		printf( '<div class="notice %s"><p><strong>%s</strong></p></div>', $result['dry_run'] ? 'notice-info' : 'notice-success', $result['dry_run'] ? 'Dry run: nothing was changed. Untick "Dry run" to import.' : 'Import done.' );
		echo '<table class="widefat striped" style="max-width:780px"><tbody>';
		foreach ( $result['lines'] as $label => $value ) {
			printf( '<tr><th style="width:55%%">%s</th><td>%s</td></tr>', esc_html( $label ), esc_html( $value ) );
		}
		echo '</tbody></table>';
		if ( $result['notes'] ) {
			echo '<h3>Notes</h3><ul style="list-style:disc;padding-left:20px">';
			foreach ( $result['notes'] as $note ) {
				printf( '<li>%s</li>', esc_html( $note ) );
			}
			echo '</ul>';
		}
	}

	/* ------------------------------------------------------------------
	 * Export
	 * ------------------------------------------------------------------ */

	public function export() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Not allowed' );
		}
		check_admin_referer( 'cl_transfer_export' );
		global $wpdb;

		$data = [
			'format'   => self::FORMAT,
			'site'     => home_url(),
			'prefix'   => $wpdb->prefix,
			'created'  => gmdate( 'c' ),
			'users'    => $this->export_users(),
			'posts'    => $this->export_posts(),
			'settings' => ! empty( $_POST['settings'] ) ? $this->export_settings() : null,
		];

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="choose-life-transfer-' . sanitize_file_name( wp_parse_url( home_url(), PHP_URL_HOST ) ) . '-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		exit;
	}

	/** every Subscriber, and any other user who owns a donation or a subscription */
	private function export_user_ids() {
		global $wpdb;
		$ids = get_users( [ 'role' => 'subscriber', 'fields' => 'ID' ] );
		$ids = array_merge( $ids, $wpdb->get_col( "SELECT DISTINCT post_author FROM $wpdb->posts WHERE post_type IN ('donations','subscriptions') AND post_status NOT IN ('trash','auto-draft','inherit') AND post_author > 0" ) );

		return array_values( array_unique( array_map( 'intval', $ids ) ) );
	}

	private function export_users() {
		global $wpdb;
		$users = [];
		foreach ( $this->export_user_ids() as $user_id ) {
			$user = get_userdata( $user_id );
			if ( ! $user ) {
				continue;
			}
			$meta = [];
			foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM $wpdb->usermeta WHERE user_id = %d", $user->ID ) ) as $row ) {
				// roles are set again on import; the rest with the site prefix and sessions mean nothing elsewhere
				if ( $row->meta_key === 'session_tokens' || strpos( $row->meta_key, $wpdb->prefix ) === 0 ) {
					continue;
				}
				$meta[] = [ $row->meta_key, $row->meta_value ];
			}
			$users[] = [
				'ID'              => (int) $user->ID,
				'user_login'      => $user->user_login,
				'user_pass'       => $user->user_pass,
				'user_nicename'   => $user->user_nicename,
				'user_email'      => $user->user_email,
				'user_url'        => $user->user_url,
				'user_registered' => $user->user_registered,
				'display_name'    => $user->display_name,
				'roles'           => array_values( (array) $user->roles ),
				'meta'            => $meta,
			];
		}

		return $users;
	}

	private function export_posts() {
		global $wpdb;
		$posts = [];
		$rows  = $wpdb->get_results(
			"SELECT * FROM $wpdb->posts WHERE post_type IN ('donations','subscriptions') AND post_status NOT IN ('trash','auto-draft','inherit') ORDER BY ID"
		);
		foreach ( $rows as $post ) {
			$meta = [];
			foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = %d ORDER BY meta_id", $post->ID ) ) as $row ) {
				if ( in_array( $row->meta_key, self::SKIP_POST_META, true ) ) {
					continue;
				}
				$meta[] = [ $row->meta_key, $row->meta_value ];
			}
			$author  = $post->post_author ? get_userdata( $post->post_author ) : false;
			$posts[] = [
				'ID'                => (int) $post->ID,
				'post_type'         => $post->post_type,
				'post_status'       => $post->post_status,
				'post_title'        => $post->post_title,
				'post_author'       => (int) $post->post_author,
				'author_email'      => $author ? $author->user_email : '',
				'post_date'         => $post->post_date,
				'post_date_gmt'     => $post->post_date_gmt,
				'post_modified'     => $post->post_modified,
				'post_modified_gmt' => $post->post_modified_gmt,
				'meta'              => $meta,
			];
		}

		return $posts;
	}

	private function export_settings() {
		global $wpdb;
		$settings = [];
		foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT option_name, option_value FROM $wpdb->options WHERE option_name REGEXP %s", self::SETTINGS_PATTERN ) ) as $row ) {
			$settings[ $row->option_name ] = $row->option_value;
		}

		return $settings;
	}

	/* ------------------------------------------------------------------
	 * Import
	 * ------------------------------------------------------------------ */

	private function import_request() {
		if ( empty( $_FILES['file']['tmp_name'] ) || ! is_uploaded_file( $_FILES['file']['tmp_name'] ) ) {
			return new WP_Error( 'file', 'Choose the export file.' );
		}
		$data = json_decode( file_get_contents( $_FILES['file']['tmp_name'] ), true );
		if ( ! is_array( $data ) || ( $data['format'] ?? '' ) !== self::FORMAT ) {
			return new WP_Error( 'format', 'This is not a Choose Life transfer export file.' );
		}
		if ( untrailingslashit( $data['site'] ) === untrailingslashit( home_url() ) ) {
			return new WP_Error( 'same', 'The file was exported from this same site.' );
		}
		@set_time_limit( 0 );

		return $this->import( $data, [
			'dry_run'  => ! empty( $_POST['dry_run'] ),
			'replace'  => ! empty( $_POST['replace'] ),
			'settings' => ! empty( $_POST['settings'] ),
		] );
	}

	/**
	 * @param array $data    the export
	 * @param array $options dry_run, replace, settings
	 *
	 * @return array report: dry_run, lines (label => value), notes
	 */
	public function import( array $data, array $options ) {
		global $wpdb;
		$dry   = $options['dry_run'];
		$site  = untrailingslashit( $data['site'] );
		$lines = [ 'Source site' => $site . ' (exported ' . $data['created'] . ')' ];
		$notes = [];

		// already transferred posts from this source: source id => local id
		$existing = [];
		foreach ( $wpdb->get_results( $wpdb->prepare(
			"SELECT s.post_id, s.meta_value AS source_id FROM $wpdb->postmeta s
			 JOIN $wpdb->postmeta site ON site.post_id = s.post_id AND site.meta_key = '_cl_source_site' AND site.meta_value = %s
			 WHERE s.meta_key = '_cl_source_id'", $site ) ) as $row ) {
			$existing[ (int) $row->source_id ] = (int) $row->post_id;
		}

		/* 1. this site's own donations / subscriptions, to delete ---------- */
		$to_delete = [];
		if ( $options['replace'] ) {
			$to_delete = $wpdb->get_col(
				"SELECT p.ID FROM $wpdb->posts p WHERE p.post_type IN ('donations','subscriptions')
				 AND NOT EXISTS (SELECT 1 FROM $wpdb->postmeta m WHERE m.post_id = p.ID AND m.meta_key = '_cl_source_id')"
			);
			$to_delete = array_map( 'intval', $to_delete );
		}
		$lines['Own donations / subscriptions deleted first'] = count( $to_delete );
		if ( ! $dry ) {
			foreach ( $to_delete as $id ) {
				wp_delete_post( $id, true );
			}
		}

		/* 2. users ----------------------------------------------------------- */
		$user_map = [];
		$u_stats  = [ 'created, same id' => 0, 'created, new id' => 0, 'updated' => 0, 'matched, left as is (not a plain subscriber)' => 0 ];
		$other    = [];
		foreach ( $data['users'] as $u ) {
			$local = get_user_by( 'email', $u['user_email'] );
			if ( $local ) {
				$user_map[ $u['ID'] ] = (int) $local->ID;
				// only plain donor accounts are overwritten; an admin / editor here keeps everything
				$source_roles = $u['roles'] ?? [ 'subscriber' ];
				if ( array_diff( (array) $local->roles, [ 'subscriber' ] ) || array_diff( $source_roles, [ 'subscriber' ] ) ) {
					$u_stats['matched, left as is (not a plain subscriber)'] ++;
					continue;
				}
				$u_stats['updated'] ++;
				if ( ! $dry ) {
					$wpdb->update( $wpdb->users, [
						'user_pass'       => $u['user_pass'],
						'display_name'    => $u['display_name'],
						'user_registered' => $u['user_registered'],
					], [ 'ID' => $local->ID ] );
					$this->write_user_meta( $local->ID, $u['meta'] );
					clean_user_cache( $local->ID );
				}
				continue;
			}

			$keep_id = ! get_userdata( $u['ID'] );
			if ( array_diff( $u['roles'] ?? [ 'subscriber' ], [ 'subscriber' ] ) ) {
				$other[] = $u['user_email'] . ' (' . implode( ', ', $u['roles'] ) . ')';
			}
			$u_stats[ $keep_id ? 'created, same id' : 'created, new id' ] ++;
			$login = $u['user_login'];
			for ( $i = 2; username_exists( $login ); $i ++ ) {
				$login = $u['user_login'] . '-' . $i;
			}
			if ( $login !== $u['user_login'] ) {
				$notes[] = sprintf( 'User %s: the username "%s" is taken here, it becomes "%s" (logging in with the email still works).', $u['user_email'], $u['user_login'], $login );
			}
			if ( $dry ) {
				$user_map[ $u['ID'] ] = $keep_id ? (int) $u['ID'] : 0;
				continue;
			}
			$row = [
				'user_login'      => $login,
				'user_pass'       => $u['user_pass'], // the hash, as is: the password keeps working
				'user_nicename'   => $u['user_nicename'],
				'user_email'      => $u['user_email'],
				'user_url'        => $u['user_url'],
				'user_registered' => $u['user_registered'],
				'display_name'    => $u['display_name'],
			];
			if ( $keep_id ) {
				$row['ID'] = (int) $u['ID'];
			}
			$wpdb->insert( $wpdb->users, $row );
			$new_id = $keep_id ? (int) $u['ID'] : (int) $wpdb->insert_id;
			$this->write_user_meta( $new_id, $u['meta'] );
			clean_user_cache( $new_id );
			// the roles it had on the source (subscriber for donors), only ones that exist here
			$roles  = array_values( array_filter( $u['roles'] ?? [ 'subscriber' ], function ( $role ) {
				return (bool) get_role( $role );
			} ) ) ?: [ 'subscriber' ];
			$wp_user = new WP_User( $new_id );
			$wp_user->set_role( array_shift( $roles ) );
			foreach ( $roles as $role ) {
				$wp_user->add_role( $role );
			}
			$user_map[ $u['ID'] ] = $new_id;
		}
		foreach ( $u_stats as $label => $n ) {
			$lines[ 'Users: ' . $label ] = $n;
		}
		if ( $other ) {
			$notes[] = 'Created with their role from the source, since they own donations: ' . implode( '; ', $other ) . '.';
		}

		/* 3. donations / subscriptions -------------------------------------- */
		$deleted  = array_flip( $to_delete );
		$post_map = [];
		$p_stats  = [];
		$create   = [];
		foreach ( $data['posts'] as $p ) {
			$stat_key = $p['post_type'];
			if ( isset( $existing[ $p['ID'] ] ) ) {
				$post_map[ $p['ID'] ] = $existing[ $p['ID'] ];
				$p_stats[ $stat_key ]['updated'] = ( $p_stats[ $stat_key ]['updated'] ?? 0 ) + 1;
				if ( ! $dry ) {
					$this->write_post( $p, $existing[ $p['ID'] ], $user_map, $site );
				}
				continue;
			}
			$create[] = $p;
		}

		// first the ones whose id is free here (they keep it), then the rest with ids above every source id
		$taken = function ( $id ) use ( $wpdb, $deleted, $dry ) {
			if ( $dry && isset( $deleted[ $id ] ) ) {
				return false;
			}

			return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE ID = %d", $id ) );
		};
		$later = [];
		foreach ( $create as $p ) {
			if ( $taken( $p['ID'] ) ) {
				$later[] = $p;
				continue;
			}
			$p_stats[ $p['post_type'] ]['created, same id'] = ( $p_stats[ $p['post_type'] ]['created, same id'] ?? 0 ) + 1;
			$post_map[ $p['ID'] ] = $dry ? $p['ID'] : $this->write_post( $p, 0, $user_map, $site, $p['ID'] );
		}
		if ( $later ) {
			$max_source = max( array_column( $data['posts'], 'ID' ) );
			if ( ! $dry ) {
				$next = max( $max_source, (int) $wpdb->get_var( "SELECT MAX(ID) FROM $wpdb->posts" ) ) + 1;
				$wpdb->query( "ALTER TABLE $wpdb->posts AUTO_INCREMENT = " . (int) $next );
			}
			foreach ( $later as $p ) {
				$p_stats[ $p['post_type'] ]['created, new id'] = ( $p_stats[ $p['post_type'] ]['created, new id'] ?? 0 ) + 1;
				$post_map[ $p['ID'] ] = $dry ? 0 : $this->write_post( $p, 0, $user_map, $site );
			}
			$notes[] = sprintf( '%d record(s) get a new id here (theirs is taken). They keep the old one in `_cl_source_id`: the donor reference (#CL-…) stays the same and Cardlink\'s recurring charges still find them.', count( $later ) );
		}
		foreach ( self::TYPES as $type ) {
			foreach ( $p_stats[ $type ] ?? [ 'nothing to import' => 0 ] as $label => $n ) {
				$lines[ ucfirst( $type ) . ': ' . $label ] = $n;
			}
		}

		/* 4. links between them ---------------------------------------------- */
		if ( ! $dry ) {
			$this->relink( $data['posts'], $post_map );
		}

		/* 5. Cardlink settings ----------------------------------------------- */
		if ( $options['settings'] && ! empty( $data['settings'] ) ) {
			$lines['Cardlink settings imported'] = implode( ', ', array_keys( array_filter( $data['settings'], function ( $k ) {
				return $k[0] !== '_';
			}, ARRAY_FILTER_USE_KEY ) ) );
			if ( ! $dry ) {
				foreach ( $data['settings'] as $name => $value ) {
					update_option( $name, maybe_unserialize( $value ), false );
				}
			}
		} else {
			$lines['Cardlink settings imported'] = 'no';
		}

		$orphans = array_filter( $data['posts'], function ( $p ) use ( $user_map ) {
			return $p['post_author'] && ! isset( $user_map[ $p['post_author'] ] ) && ! ( $p['author_email'] && get_user_by( 'email', $p['author_email'] ) );
		} );
		if ( $orphans ) {
			$notes[] = sprintf( '%d record(s) belong to a user who no longer exists on the source: they are imported without an owner (like guest donations).', count( $orphans ) );
		}

		if ( ! $dry ) {
			wp_cache_flush();
		}

		return [ 'dry_run' => $dry, 'lines' => $lines, 'notes' => $notes ];
	}

	/** User meta, raw (as stored on the source) */
	private function write_user_meta( $user_id, array $meta ) {
		global $wpdb;
		$keys = array_unique( array_column( $meta, 0 ) );
		foreach ( $keys as $key ) {
			delete_user_meta( $user_id, $key );
		}
		foreach ( $meta as [ $key, $value ] ) {
			$wpdb->insert( $wpdb->usermeta, [ 'user_id' => $user_id, 'meta_key' => $key, 'meta_value' => $value ] );
		}
	}

	/**
	 * Creates ($post_id 0) or updates a donation / subscription, with its meta as
	 * on the source and the source markers
	 *
	 * @return int local post id
	 */
	private function write_post( array $p, $post_id, array $user_map, $site, $import_id = 0 ) {
		global $wpdb;
		$author = $user_map[ $p['post_author'] ] ?? 0;
		if ( ! $author && $p['author_email'] && ( $user = get_user_by( 'email', $p['author_email'] ) ) ) {
			$author = (int) $user->ID;
		}
		$args = [
			'post_type'     => $p['post_type'],
			'post_status'   => $p['post_status'],
			'post_title'    => $p['post_title'],
			'post_author'   => $author,
			'post_date'     => $p['post_date'],
			'post_date_gmt' => $p['post_date_gmt'],
		];
		if ( $post_id ) {
			$args['ID'] = $post_id;
			wp_update_post( wp_slash( $args ), true, false );
		} else {
			if ( $import_id ) {
				$args['import_id'] = $import_id;
			}
			$post_id = wp_insert_post( wp_slash( $args ), true, false );
			if ( is_wp_error( $post_id ) ) {
				return 0;
			}
		}
		$wpdb->update( $wpdb->posts, [ 'post_modified' => $p['post_modified'], 'post_modified_gmt' => $p['post_modified_gmt'] ], [ 'ID' => $post_id ] );

		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE post_id = %d", $post_id ) );
		foreach ( $p['meta'] as [ $key, $value ] ) {
			$wpdb->insert( $wpdb->postmeta, [ 'post_id' => $post_id, 'meta_key' => $key, 'meta_value' => $value ] );
		}
		$wpdb->insert( $wpdb->postmeta, [ 'post_id' => $post_id, 'meta_key' => '_cl_source_id', 'meta_value' => (string) $p['ID'] ] );
		$wpdb->insert( $wpdb->postmeta, [ 'post_id' => $post_id, 'meta_key' => '_cl_source_site', 'meta_value' => $site ] );
		clean_post_cache( $post_id );

		return (int) $post_id;
	}

	/** donation → subscription (`subscription_id`) and subscription → donations (`payments`) with the local ids */
	private function relink( array $posts, array $map ) {
		global $wpdb;
		foreach ( $posts as $p ) {
			$local = $map[ $p['ID'] ] ?? 0;
			if ( ! $local ) {
				continue;
			}
			if ( $p['post_type'] === 'donations' ) {
				$sub = get_post_meta( $local, 'subscription_id', true );
				if ( $sub !== '' && isset( $map[ (int) $sub ] ) && (int) $map[ (int) $sub ] !== (int) $sub ) {
					$wpdb->update( $wpdb->postmeta, [ 'meta_value' => (string) $map[ (int) $sub ] ], [ 'post_id' => $local, 'meta_key' => 'subscription_id' ] );
				}
			} else {
				$raw      = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = 'payments'", $local ) );
				$payments = maybe_unserialize( $raw );
				if ( ! is_array( $payments ) ) {
					continue;
				}
				// keep each element's type (int at creation, string after ACF rewrote it)
				$payments = array_map( function ( $id ) use ( $map ) {
					if ( ! isset( $map[ (int) $id ] ) ) {
						return $id;
					}

					return is_int( $id ) ? (int) $map[ $id ] : (string) $map[ (int) $id ];
				}, $payments );
				$wpdb->update( $wpdb->postmeta, [ 'meta_value' => maybe_serialize( $payments ) ], [ 'post_id' => $local, 'meta_key' => 'payments' ] );
			}
			clean_post_cache( $local );
		}
	}
}

CL_Transfer::init();
