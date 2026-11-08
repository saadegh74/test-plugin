<?php declare( strict_types=1 );

namespace iThemesSecurity\Restrict_Admin_Access;

use iThemesSecurity\Contracts\Runnable;
use ITSEC_Modules;

/**
 * Runs when the module is deactivated.
 */
final class Deactivator implements Runnable {

	/**
	 * Runs when the module is deactivated.
	 *
	 * @return void
	 */
	public function run() {
		// Clear authorized countries to prevent any potential lockout issues.
		ITSEC_Modules::set_setting( Provider::NAME, 'authorized_countries', [] );
	}

}
