<?php

namespace WPML\TM\ATE\ClonedSites\AutoMigration;

use WPML\API\Settings;
use WPML\TM\ATE\API\AmsCredentialsStorage;
use WPML\TM\ATE\API\AmsRequestSigner;
use WPML\TM\ATE\API\CachedAMSAPI;
use WPML\TM\ATE\ClonedSites\IdentitySnapshot;
use WPML\TM\ATE\ClonedSites\InProgressJobsCanceller;
use WPML\TM\ATE\ClonedSites\MigrationLogger;
use WPML\TM\ATE\ClonedSites\AliasDomainResetFlag;
use WPML\TM\ATE\ClonedSites\SetupMigration\Resetter\SiteKeyCleaner;
use WPML\TM\ATE\ClonedSites\SetupMigration\Resetter\SiteKeyRegistrar;
use WPML\TM\ATE\SiteName\Sender as SiteNameSender;

use function WPML\Container\make;

class Handler {

	const TRANSIENT_KEY = 'wpml_ate_auto_migration_succeeded';
	const OPTION_MIGRATION_DATA = 'wpml_ate_auto_migration_data';

	private $signer;

	private $endpoints;

	private $credentialsStorage;

	private $auth;

	private $siteKeyCleaner;

	private $siteKeyRegistrar;

	private static $processing = false;

	private $lastFailureReason = '';

	public function __construct(
		AmsRequestSigner $signer,
		\WPML_TM_ATE_AMS_Endpoints $endpoints,
		\WPML_TM_ATE_Authentication $auth,
		AmsCredentialsStorage $credentialsStorage,
		SiteKeyCleaner $siteKeyCleaner,
		SiteKeyRegistrar $siteKeyRegistrar
	) {
		$this->signer             = $signer;
		$this->endpoints          = $endpoints;
		$this->auth               = $auth;
		$this->credentialsStorage = $credentialsStorage;
		$this->siteKeyCleaner     = $siteKeyCleaner;
		$this->siteKeyRegistrar   = $siteKeyRegistrar;
	}

	public function tryMigrate( string $oldUrl = '', string $newUrl = '' ): bool {
		$this->lastFailureReason = '';

		if ( ! $oldUrl || ! $newUrl ) {
			$existing = self::getMigrationData();
			if ( is_array( $existing ) ) {
				$oldUrl = $oldUrl ?: ( $existing['old_url'] ?? '' );
				$newUrl = $newUrl ?: ( $existing['new_url'] ?? '' );
			}
		}

		if ( ! $this->ensureInstallerAvailable() ) {
			$this->lastFailureReason = 'installer_unavailable';

			return false;
		}

		if ( ! \SitePress_Setup::setup_complete() ) {
			$this->lastFailureReason = 'setup_incomplete';

			return false;
		}

		if ( self::$processing ) {
			$this->lastFailureReason = 'already_running';

			return false;
		}

		if ( $this->alreadyMigratedForCurrentUrls( $oldUrl, $newUrl ) ) {
			return true;
		}

		self::$processing = true;

		MigrationLogger::begin();

		try {
			$result = $this->doMigrate( $oldUrl, $newUrl );

			if ( $result ) {
				MigrationLogger::siteUnlocked();
			} else {
				MigrationLogger::migrationFailed();
			}

			return $result;
		} finally {
			MigrationLogger::end();
			self::$processing = false;
		}
	}

	private function doMigrate( string $oldUrl, string $newUrl ): bool {
		$body = $this->callCopyWithAttachment();

		if ( ! $body ) {
			$this->lastFailureReason = 'copy_failed';

			return false;
		}

		if ( ! isset( $body['new_shared_key'], $body['new_secret_key'], $body['new_website_uuid'] ) ) {
			MigrationLogger::copyResponseInvalid( $body );
			$this->lastFailureReason = 'copy_response_incomplete';

			return false;
		}

		$snapshot = IdentitySnapshot::capture( $this->auth );

		$stored = $this->credentialsStorage->store( $body );
		MigrationLogger::credentialsStored( $stored );

		if ( ! $stored ) {
			$snapshot->restore();
			$this->lastFailureReason = 'credentials_not_stored';

			return false;
		}

		if ( ! $this->sendConfirmation() ) {
			MigrationLogger::confirmFailed();
			$snapshot->restore();
			$this->lastFailureReason = 'confirm_failed';

			return false;
		}

		if ( ! $this->handleSiteKey( $body, $snapshot->websiteUuid() ) ) {
			$snapshot->restore();
			$this->lastFailureReason = 'site_key_not_registered';

			return false;
		}

		$this->publish( $body, $oldUrl, $newUrl );

		return true;
	}

	private function publish( array $body, string $oldUrl, string $newUrl ) {
		CachedAMSAPI::clearCache();

		SiteNameSender::send();

		$organizationName      = $body['billing_group_name'] ?? $oldUrl;
		$organizationConnected = (bool) ( $body['organization_connected'] ?? true );

		$aliasDomainReset = AliasDomainResetFlag::isSet();

		update_option( self::OPTION_MIGRATION_DATA, [
			'old_url'                => $oldUrl,
			'new_url'                => $newUrl,
			'organization_name'      => $organizationName,
			'organization_connected' => $organizationConnected,
			'alias_domain_reset'     => $aliasDomainReset,
		], false );

		if ( $oldUrl && $newUrl ) {
			Settings::setAndSave( 'migrated_site', [
				'old_url' => $oldUrl,
				'new_url' => $newUrl,
			] );
		}

		set_transient( self::TRANSIENT_KEY, [
			'old_url' => $oldUrl,
			'new_url' => $newUrl,
		], HOUR_IN_SECONDS );

		$cancelledCount = make( InProgressJobsCanceller::class )->cancel();
		MigrationLogger::jobsCancelled( (int) $cancelledCount );

		do_action( 'wpml_tm_ate_synchronize_translators' );

		\WPML\Upgrade\Commands\MigrateLanguagesToCountryModel::reapplyAteCountryMappingIfDeferred();
	}

	public function getLastFailureReason(): string {
		return $this->lastFailureReason;
	}

	private function callCopyWithAttachment() {
		$registration_data = get_option( \WPML_TM_ATE_Authentication::AMS_DATA_KEY, [] );

		$url = $this->endpoints->get_ams_copy_attached();

		$params = [
			'shared_key'                  => isset( $registration_data['shared'] ) ? $registration_data['shared'] : '',
			'website_uuid'                => $this->auth->get_site_id(),
			'respect_previous_disconnect' => 'true',
		];

		MigrationLogger::copyRequestSent( $url );

		$response = $this->signer->send( $url, 'POST', $params );

		MigrationLogger::copyResponse( $response );

		if ( is_wp_error( $response ) || ! is_array( $response ) ) {
			return null;
		}

		if (
			! isset( $response['response']['code'] )
			|| $response['response']['code'] !== 200
			|| ! isset( $response['body'] )
		) {
			return null;
		}

		$body = json_decode( $response['body'], true );

		if ( ! is_array( $body ) ) {
			return null;
		}

		return $body;
	}

	private function sendConfirmation(): bool {
		$registration_data = get_option( \WPML_TM_ATE_Authentication::AMS_DATA_KEY, [] );

		MigrationLogger::confirmSent( $this->auth->get_site_id() );

		$response = $this->signer->send(
			$this->endpoints->get_ams_site_confirm(),
			'POST',
			[
				'new_shared_key'   => isset( $registration_data['shared'] ) ? $registration_data['shared'] : '',
				'new_website_uuid' => $this->auth->get_site_id(),
			]
		);

		if ( is_wp_error( $response ) || ! is_array( $response ) ) {
			MigrationLogger::confirmRequestFailed( $response );
			return false;
		}

		if ( ! isset( $response['body'] ) ) {
			MigrationLogger::confirmRequestFailed( $response );
			return false;
		}

		$body = json_decode( $response['body'], true );
		$confirmed = is_array( $body ) && ! empty( $body['confirmed'] );

		MigrationLogger::confirmResponse( $confirmed );

		return $confirmed;
	}

	private function handleSiteKey( array $body, string $previousUuid ): bool {
		$siteKey = isset( $body['site_key'] ) ? $body['site_key'] : null;

		if ( $siteKey ) {
			return (bool) $this->siteKeyRegistrar->register( $siteKey );
		}

		if ( '' !== $previousUuid && (string) $body['new_website_uuid'] === $previousUuid ) {
			return true;
		}

		if ( $this->revalidateHeldKey() ) {
			return true;
		}

		$this->siteKeyCleaner->unregister();

		return true;
	}

	private function revalidateHeldKey(): bool {
		if ( class_exists( 'WP_Installer' ) && \WP_Installer::get_repository_hardcoded_site_key( 'wpml' ) ) {
			return true;
		}

		$heldKey = $this->getHeldSiteKey();
		if ( '' === $heldKey ) {
			return false;
		}

		if ( ! $this->siteKeyRegistrar->register( $heldKey ) ) {
			return false;
		}

		do_action( 'otgs_installer_site_key_update', 'wpml' );

		return true;
	}

	private function getHeldSiteKey(): string {
		if ( ! function_exists( 'OTGS_Installer' ) ) {
			return '';
		}

		$installer = \OTGS_Installer();
		if ( ! is_object( $installer ) || ! method_exists( $installer, 'get_site_key' ) ) {
			return '';
		}

		return (string) $installer->get_site_key( 'wpml' );
	}

	private function ensureInstallerAvailable(): bool {
		if ( function_exists( 'OTGS_Installer' ) ) {
			return true;
		}

		if ( function_exists( 'wpml_installer_force_load' ) ) {
			wpml_installer_force_load();
		}

		return function_exists( 'OTGS_Installer' );
	}

	public static function hasMigrated(): bool {
		return (bool) get_transient( self::TRANSIENT_KEY );
	}

	public static function getMigrationData() {
		$data = get_option( self::OPTION_MIGRATION_DATA, null );

		return is_array( $data ) ? $data : null;
	}

	public static function clearMigrationFlag() {
		delete_transient( self::TRANSIENT_KEY );
	}

	public static function clearMigrationData() {
		delete_option( self::OPTION_MIGRATION_DATA );
	}

	public static function setOrganizationConnected( bool $connected ) {
		$data = self::getMigrationData();
		if ( ! is_array( $data ) ) {
			return;
		}
		$data['organization_connected'] = $connected;
		update_option( self::OPTION_MIGRATION_DATA, $data, false );
	}

	public static function hasSitekey(): bool {
		if ( ! function_exists( 'OTGS_Installer' ) ) {
			return false;
		}
		$installer = \OTGS_Installer();
		if ( ! $installer ) {
			return false;
		}
		return (bool) $installer->get_site_key( 'wpml' );
	}

	public static function resolveInitialState( $migrationData = null ): string {
		if ( $migrationData === null ) {
			$migrationData = self::getMigrationData();
		}

		if ( ! is_array( $migrationData ) || empty( $migrationData ) ) {
			return 'error';
		}

		if ( ! isset( $migrationData['organization_connected'] ) ) {
			return 'error';
		}

		return $migrationData['organization_connected'] ? 'success' : 'success-independent';
	}

	private function alreadyMigratedForCurrentUrls( string $oldUrl, string $newUrl ): bool {
		$cached = get_transient( self::TRANSIENT_KEY );

		return is_array( $cached )
			&& isset( $cached['old_url'], $cached['new_url'] )
			&& $cached['old_url'] === $oldUrl
			&& $cached['new_url'] === $newUrl;
	}
}
