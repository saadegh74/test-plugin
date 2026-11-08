<?php declare( strict_types=1 );

namespace iThemesSecurity\Restrict_Admin_Access\Access;

use iThemesSecurity\Lib\Pipeline\Pipeline;
use iThemesSecurity\Restrict_Admin_Access\Access\Exceptions\CountryNotAuthorizedException;
use iThemesSecurity\Restrict_Admin_Access\Log_Formatter;
use iThemesSecurity\Restrict_Admin_Access\Provider;
use ITSEC_Lib;
use ITSEC_Lib_Login;
use ITSEC_Log;
use WP_Error;
use WP_User;

/**
 * Control access to administrator login attempts based on geographic location.
 */
final class Access_Manager {

	/**
	 * The preconfigured Pipeline and its checks.
	 */
	private Pipeline $pipeline;
	private Bypass $bypass;

	/**
	 * @param Pipeline $pipeline
	 * @param Bypass $bypass
	 */
	public function __construct(
		Pipeline $pipeline,
		Bypass $bypass
	) {
		$this->pipeline = $pipeline;
		$this->bypass   = $bypass;
	}

	/**
	 * Check if a user is allowed to log in.
	 *
	 * @filter authenticate
	 *
	 * @param null|WP_User|WP_Error $user
	 * @param string $username The username used to try to log in.
	 *
	 * @return null|WP_User|WP_Error
	 */
	public function allow_login( $user, string $username ) {
		// Emergency bypass: define( 'ITSEC_DISABLE_COUNTRY_RESTRICTION', true ) in wp-config.php
		if ( $this->bypass->is_bypassed() ) {
			return $user;
		}

		// Something already went wrong or the user did not authenticate.
		if ( ! $user instanceof WP_User ) {
			return $user;
		}

		// Always allow Central IP addresses.
		if ( ITSEC_Lib::is_ip_trusted() ) {
			return $user;
		}

		// Not an admin, skip.
		if ( ! $user->has_cap( 'manage_options' ) ) {
			return $user;
		}

		$remote_ip = ITSEC_Lib::get_ip();

		try {
			$this->pipeline->send( $remote_ip )->thenReturn();
		} catch ( CountryNotAuthorizedException $e ) {
			$found_user = ITSEC_Lib_Login::get_user( $username );
			$user_id    = $found_user ? $found_user->ID : 0;
			$error      = $e->getMessage();

			ITSEC_Log::add_warning(
				Provider::NAME,
				sprintf( '%s::%d,%s', Log_Formatter::ADMIN_RESTRICTED, $user_id, $error ),
				compact( 'user_id', 'error' )
			);

			return new WP_Error(
				'itsec_login_denied',
				esc_html__( 'Access denied.', 'it-l10n-ithemes-security-pro' )
			);
		}

		return $user;
	}

}
