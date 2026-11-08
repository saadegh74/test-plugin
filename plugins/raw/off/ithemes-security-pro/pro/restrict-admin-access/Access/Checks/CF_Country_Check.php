<?php declare( strict_types=1 );

namespace iThemesSecurity\Restrict_Admin_Access\Access\Checks;

use Closure;
use iThemesSecurity\Restrict_Admin_Access\Access\Exceptions\CountryNotAuthorizedException;

/**
 * Checks if the country code from Cloudflare header is authorized.
 *
 * @note This can be forged if not behind Cloudflare, so should only be used
 *       as an early optimization, not as the sole check.
 */
final class CF_Country_Check {

	/**
	 * @var string[]
	 */
	private array $authorized_countries;

	/**
	 * @param string[] $authorized_countries List of authorized country codes.
	 */
	public function __construct( array $authorized_countries = [] ) {
		$this->authorized_countries = $authorized_countries;
	}

	/**
	 * @throws CountryNotAuthorizedException
	 */
	public function handle( string $remote_ip, Closure $next ) {
		// No countries configured, allow all.
		if ( empty( $this->authorized_countries ) ) {
			return $next( $remote_ip );
		}

		$country_code = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? null;

		// No header present.
		if ( $country_code === null ) {
			return $next( $remote_ip );
		}

		$country_code = strtoupper( $country_code );

		// Check if country is authorized, pass through so we can ensure this isn't faked.
		if ( in_array( $country_code, $this->authorized_countries, true ) ) {
			return $next( $remote_ip );
		}

		throw new CountryNotAuthorizedException( $country_code );
	}
}
