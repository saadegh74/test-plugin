<?php declare( strict_types=1 );

namespace iThemesSecurity\Geolocation\Contracts;

use WP_Error;

interface Geolocatable {

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
	public function geolocate( string $ip );

}
