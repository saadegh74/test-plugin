<?php declare( strict_types=1 );

namespace iThemesSecurity\Restrict_Admin_Access\Notice;

use iThemesSecurity\Restrict_Admin_Access\Provider;
use ITSEC_Admin_Notice;
use ITSEC_Admin_Notice_Action;
use ITSEC_Admin_Notice_Action_Link;
use ITSEC_Admin_Notice_Context;
use ITSEC_Core;
use ITSEC_Geolocator;
use ITSEC_Modules;

/**
 * Notice displayed when country restrictions are configured but a proper geolocation
 * service is not available.
 */
final class Geolocation_Not_Configured_Notice implements ITSEC_Admin_Notice {

	public const ID = 'restrict-admin-access-geolocation-not-configured';

	private ITSEC_Geolocator $maxmind_api;
	private ITSEC_Geolocator $maxmind_db;

	public function __construct(
		ITSEC_Geolocator $maxmind_api,
		ITSEC_Geolocator $maxmind_db
	) {
		$this->maxmind_api = $maxmind_api;
		$this->maxmind_db  = $maxmind_db;
	}

	public function get_id(): string {
		return self::ID;
	}

	public function get_title(): string {
		return __( 'Restrict Admin Access: Geolocation Required', 'it-l10n-ithemes-security-pro' );
	}

	public function get_message(): string {
		return __( 'Country blocking is inactive — geolocation is not configured. To enable these restrictions, set up a geolocation provider like MaxMind.', 'it-l10n-ithemes-security-pro' );
	}

	public function get_meta(): array {
		return [
			'module' => [
				'label'     => esc_html__( 'Module', 'it-l10n-ithemes-security-pro' ),
				'value'     => Provider::NAME,
				'formatted' => esc_html__( 'Restrict Admin Access', 'it-l10n-ithemes-security-pro' ),
			],
		];
	}

	public function get_severity(): string {
		return self::S_WARN;
	}

	public function show_for_context( ITSEC_Admin_Notice_Context $context ): bool {
		if ( ! user_can( $context->get_user()->ID, ITSEC_Core::get_required_cap() ) ) {
			return false;
		}

		if ( ! ITSEC_Modules::is_active( Provider::NAME ) ) {
			return false;
		}

		$authorized_countries = ITSEC_Modules::get_setting( Provider::NAME, 'authorized_countries', [] );

		if ( empty( $authorized_countries ) ) {
			return false;
		}

		return ! $this->is_geolocation_available();
	}

	/**
	 * Check if any reliable geolocation provider is properly configured.
	 */
	private function is_geolocation_available(): bool {
		if ( ! ITSEC_Modules::is_active( 'geolocation' ) ) {
			return false;
		}

		return $this->maxmind_api->is_available() || $this->maxmind_db->is_available();
	}

	public function get_actions(): array {
		return [
			'settings' => new ITSEC_Admin_Notice_Action_Link(
				ITSEC_Core::get_settings_module_url( 'geolocation' ),
				esc_html__( 'Configure Geolocation', 'it-l10n-ithemes-security-pro' ),
				ITSEC_Admin_Notice_Action::S_PRIMARY
			),
		];
	}

}
