<?php declare( strict_types=1 );

namespace iThemesSecurity\Geolocation;

use iThemesSecurity\Geolocation\Contracts\Geolocatable;
use ITSEC_Lib_Geolocation;
use WP_Error;

/**
 * Geolocation wrapper for the static implementation.
 */
final class Geolocator implements Geolocatable {

	/**
	 * Geolocate an IP address.
	 *
	 * @param string $ip
	 *
	 * @return array{
	 *     label: string,
	 *     credit: string,
	 *     lat?: float,
	 *     long?: float,
	 *     country?: string,
	 *     country_code?: string,
	 * }|WP_Error Label and credit are sanitized.
	 */
	public function geolocate( string $ip ) {
		return ITSEC_Lib_Geolocation::geolocate( $ip );
	}

}
