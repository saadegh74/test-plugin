<?php declare( strict_types=1 );

namespace iThemesSecurity\Restrict_Admin_Access\Access\Exceptions;

use Exception;

/**
 * Thrown when a user's country is not authorized to access wp-admin.
 */
final class CountryNotAuthorizedException extends Exception {

	/**
	 * The ISO-3166 2 digit country code.
	 *
	 * @var string
	 */
	private string $country_code;

	public function __construct( string $country_code, string $message = '' ) {
		$this->country_code = $country_code;

		if ( ! $message ) {
			$message = sprintf( 'Country %s is not authorized to access the admin area.', $country_code );
		}

		parent::__construct( $message );
	}

	public function get_country_code(): string {
		return $this->country_code;
	}
}

