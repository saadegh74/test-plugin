<?php declare( strict_types=1 );

namespace iThemesSecurity\Restrict_Admin_Access\Settings;

use iThemesSecurity\Config_Validator;
use iThemesSecurity\Module_Config;
use iThemesSecurity\Restrict_Admin_Access\Country\Country_Collection;
use iThemesSecurity\Restrict_Admin_Access\Provider;
use ITSEC_Core;
use ITSEC_Lib;
use ITSEC_Lib_Geolocation;
use ITSEC_Lib_IP_Tools;
use ITSEC_Modules;
use WP_Error;

/**
 * Custom settings validator for Restrict Admin Access settings.
 */
final class Validator extends Config_Validator {

	private Country_Collection $collection;

	public function __construct(
		Module_Config $config,
		Country_Collection $collection
	) {
		$this->collection = $collection;

		parent::__construct( $config );
	}

	/**
	 * Validate global settings changes to prevent self-lockout.
	 *
	 * Checks if removing IPs from lockout_white_list would lock out the current
	 * admin user when restrict-admin-access is active.
	 *
	 * @filter itsec_validate_global_settings
	 *
	 * @param  WP_Error|null  $error  Existing error or null.
	 * @param  array{lockout_white_list?: string[]}  $settings  The new global settings being saved.
	 * @param  array{lockout_white_list?: string[]}  $previous_settings  The current saved global settings.
	 *
	 * @return WP_Error|null
	 */
	public function validate_global_settings(
		?WP_Error $error,
		array     $settings,
		array     $previous_settings
	): ?WP_Error {
		// Already has an error, don't add more.
		if ( is_wp_error( $error ) ) {
			return $error;
		}

		// Module not active, nothing to validate.
		if ( ! ITSEC_Modules::is_active( Provider::NAME ) ) {
			return $error;
		}

		// WP-CLI is already a privileged operation, skip self-lockout check.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return $error;
		}

		// Check if lockout_white_list is being modified.
		$new_ips      = $settings['lockout_white_list'] ?? [];
		$previous_ips = $previous_settings['lockout_white_list'] ?? [];

		// No change to IPs, nothing to validate.
		if ( $new_ips === $previous_ips ) {
			return $error;
		}

		// Return if the user wouldn't be locked out.
		if ( ! $this->would_lock_out_with_ips( $new_ips ) ) {
			return $error;
		}

		$country      = $this->get_current_user_country();
		$country_name = $country
			? $this->collection->get( $country ) ?? $country
			: __( 'Unknown Country', 'it-l10n-ithemes-security-pro' );

		return new WP_Error(
			'itsec-restrict-admin-access-global-self-lockout',
			sprintf(
				/* translators: %s: The user's detected country name */
				__( 'Potential Lockout Detected: Removing your IP from the Authorized IPs list would lock you out because your country (%s) is not in the Authorized Countries list for Restrict Admin Access. Add your country to the allowed list first, or keep your IP in the Authorized IPs list.',
					'it-l10n-ithemes-security-pro' ),
				$country_name
			)
		);
	}

	/**
	 * Try to prevent the user from locking themselves out of their own admin
	 * on module settings save.
	 *
	 * @return void
	 */
	protected function validate_settings() {
		parent::validate_settings();

		if ( ! $this->can_save() ) {
			return;
		}

		// WP-CLI is already a privileged operation, skip self-lockout check.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return;
		}

		// Check for self-lockout.
		if ( $this->would_lock_out_current_user() ) {
			$country = $this->get_current_user_country();

			if ( $country === null ) {
				$geolocation_link = sprintf(
					'<a href="%s">%s</a>',
					esc_url( ITSEC_Core::get_settings_module_url( 'geolocation' ) ),
					__( 'configure geolocation', 'it-l10n-ithemes-security-pro' )
				);

				$this->add_error( new WP_Error(
					'itsec-restrict-admin-access-self-lockout',
					sprintf(
						/* translators: %s: Link to configure geolocation settings */
						__( 'Potential Lockout Detected: Your current country cannot be identified. To save these settings, you must first %s or add your IP to the Authorized IPs list.', 'it-l10n-ithemes-security-pro' ),
						$geolocation_link
					)
				) );
			} else {
				$country_name = $this->collection->get( $country ) ?? $country;

				$authorized_ips_link = sprintf(
					'<a href="%s">%s</a>',
					esc_url( ITSEC_Core::get_url_for_settings_route( '/settings/global#lockout_white_list' ) ),
					__( 'Authorized IPs', 'it-l10n-ithemes-security-pro' )
				);

				$this->add_error( new WP_Error(
					'itsec-restrict-admin-access-self-lockout',
					sprintf(
						/* translators: 1: The user's detected country name, 2: Link to Authorized IPs settings */
						__( 'Potential Lockout Detected: Your current country is detected as %1$s, which is not on your allowed list. To save these settings, add %1$s to the Authorized Countries or add your IP to the Authorized IPs list.', 'it-l10n-ithemes-security-pro' ),
						$country_name,
						$authorized_ips_link
					)
				) );
			}

			$this->set_can_save( false );
		}
	}

	/**
	 * Get the current user's country code based on their IP.
	 *
	 * @return string|null The country code or null if unable to determine.
	 */
	private function get_current_user_country(): ?string {
		$ip       = ITSEC_Lib::get_ip();
		$location = ITSEC_Lib_Geolocation::geolocate( $ip );

		if ( $location instanceof WP_Error || empty( $location['country_code'] ) ) {
			return null;
		}

		return strtoupper( $location['country_code'] );
	}

	/**
	 * Check if the current user would be locked out with the current settings.
	 *
	 * @return bool True if the user would be locked out.
	 */
	private function would_lock_out_current_user(): bool {
		$authorized_ips       = ITSEC_Modules::get_setting( 'global', 'lockout_white_list', [] );
		$authorized_countries = $this->settings['authorized_countries'] ?? [];

		return $this->would_lock_out( $authorized_ips, $authorized_countries );
	}

	/**
	 * Check if the current user would be locked out with the given authorized IPs.
	 *
	 * @param array $authorized_ips The authorized IPs list to check against.
	 *
	 * @return bool True if the user would be locked out.
	 */
	private function would_lock_out_with_ips( array $authorized_ips ): bool {
		$authorized_countries = ITSEC_Modules::get_setting( Provider::NAME, 'authorized_countries', [] );

		return $this->would_lock_out( $authorized_ips, $authorized_countries );
	}

	/**
	 * Check if the current user would be locked out with the given restrictions.
	 *
	 * @param array $authorized_ips      The authorized IPs list to check against.
	 * @param array $authorized_countries The authorized countries list to check against.
	 *
	 * @return bool True if the user would be locked out.
	 */
	private function would_lock_out( array $authorized_ips, array $authorized_countries ): bool {
		$has_ip_restrictions      = ! empty( $authorized_ips );
		$has_country_restrictions = ! empty( $authorized_countries );

		// No restrictions configured, no one is locked out.
		if ( ! $has_ip_restrictions && ! $has_country_restrictions ) {
			return false;
		}

		// If countries list is empty, all countries are allowed.
		if ( ! $has_country_restrictions ) {
			return false;
		}

		$current_ip    = ITSEC_Lib::get_ip();
		$ip_authorized = $has_ip_restrictions && $this->is_ip_authorized( $authorized_ips, $current_ip );

		// If IP is authorized, user bypasses all checks (not locked out).
		if ( $ip_authorized ) {
			return false;
		}

		/*
		 * At this point:
		 * - We have country restrictions.
		 * - IP is NOT authorized.
		 * - We need to check country authorization.
		 */
		$country            = $this->get_current_user_country();
		$country_authorized = $this->is_country_authorized( $country, $authorized_countries );

		// User is locked out if country is not authorized.
		return ! $country_authorized;
	}

	/**
	 * Check if an IP address is in the authorized IPs list.
	 *
	 * @param array       $authorized_ips The authorized IPs list.
	 * @param string|null $ip             The IP address to check. If null, uses current user's IP.
	 *
	 * @return bool True if the IP is authorized.
	 */
	private function is_ip_authorized( array $authorized_ips, ?string $ip = null ): bool {
		if ( $ip === null ) {
			$ip = ITSEC_Lib::get_ip();
		}

		foreach ( $authorized_ips as $authorized_ip ) {
			if ( ITSEC_Lib_IP_Tools::in_range( $ip, $authorized_ip ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a country is in the authorized countries list. If we can't detect the user's country, they
	 * are automatically blocked from logging in.
	 *
	 * @param string|null $country             The country code to check.
	 * @param array       $authorized_countries The authorized countries list.
	 *
	 * @return bool True if the country is authorized.
	 */
	private function is_country_authorized( ?string $country, array $authorized_countries ): bool {
		if ( $country === null ) {
			return false;
		}

		return in_array( $country, $authorized_countries, true );
	}

}

