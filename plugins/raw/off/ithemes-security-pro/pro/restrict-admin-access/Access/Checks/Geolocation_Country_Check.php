<?php declare( strict_types=1 );

namespace iThemesSecurity\Restrict_Admin_Access\Access\Checks;

use Closure;
use iThemesSecurity\Geolocation\Contracts\Geolocatable;
use iThemesSecurity\Restrict_Admin_Access\Access\Exceptions\CountryNotAuthorizedException;
use WP_Error;

/**
 * Uses geolocation API to verify the user's country.
 */
final class Geolocation_Country_Check {

	private Geolocatable $geolocator;

	/**
	 * @var string[]
	 */
	private array $authorized_countries;

	/**
	 * @param Geolocatable $geolocator The Geolocator.
	 * @param string[] $authorized_countries List of authorized country codes.
	 */
	public function __construct(
		Geolocatable $geolocator,
		array $authorized_countries = []
	) {
		$this->geolocator           = $geolocator;
		$this->authorized_countries = $authorized_countries;
	}

	/**
	 * @throws CountryNotAuthorizedException
	 */
	public function handle( string $remote_ip, Closure $next ) {
		if ( ! $remote_ip ) {
			return $next( $remote_ip );
		}

		// No countries configured, allow all.
		if ( empty( $this->authorized_countries ) ) {
			return $next( $remote_ip );
		}

		$location = $this->geolocator->geolocate( $remote_ip );

		// Can't determine location, deny access.
		if ( $location instanceof WP_Error || empty( $location['country_code'] ) ) {
			throw new CountryNotAuthorizedException( 'XX' );
		}

		$country_code = strtoupper( $location['country_code'] );

		if ( in_array( $country_code, $this->authorized_countries, true ) ) {
			return $next( $remote_ip );
		}

		throw new CountryNotAuthorizedException( $country_code );
	}

}
