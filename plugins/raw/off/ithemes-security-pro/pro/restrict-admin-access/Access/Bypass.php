<?php declare( strict_types=1 );

namespace iThemesSecurity\Restrict_Admin_Access\Access;

class Bypass {

	/**
	 * Whether restrictions are bypassed.
	 *
	 * Emergency bypass: define( 'ITSEC_DISABLE_COUNTRY_RESTRICTION', true ) in wp-config.php
	 *
	 * @return bool
	 */
	public function is_bypassed(): bool {
		return defined( 'ITSEC_DISABLE_COUNTRY_RESTRICTION' ) && ITSEC_DISABLE_COUNTRY_RESTRICTION === true;
	}

}
