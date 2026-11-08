<?php declare( strict_types=1 );

namespace iThemesSecurity\Restrict_Admin_Access\Access\Checks;

use Closure;
use ITSEC_Lib_IP_Tools;

/**
 * If the admin's IP address is authorized, bypass the remaining checks.
 */
final class Authorized_Ip_Bypass {

	/**
	 * @var string[]
	 */
	private array $authorized_ips;

	/**
	 * @param string[] $authorized_ips List of authorized IP addresses.
	 */
	public function __construct( array $authorized_ips = [] ) {
		$this->authorized_ips = $authorized_ips;
	}

	public function handle( string $remote_ip, Closure $next ) {
		if ( ! $remote_ip ) {
			return $next( $remote_ip );
		}

		// Skip any further checks if the IP is in the allowed list.
		foreach ( $this->authorized_ips as $authorized_ip ) {
			if ( ITSEC_Lib_IP_Tools::in_range( $remote_ip, $authorized_ip ) ) {
				return $remote_ip;
			}
		}

		return $next( $remote_ip );
	}
}
