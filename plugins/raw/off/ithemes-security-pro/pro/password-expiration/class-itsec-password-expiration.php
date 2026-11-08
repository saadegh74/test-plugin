<?php

use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Lib\Password_Requirement;

class ITSEC_Password_Expiration implements Runnable {

	/** @var Password_Requirement[] */
	private $requirements;

	/**
	 * ITSEC_Password_Expiration constructor.
	 *
	 * @param Password_Requirement ...$requirements
	 */
	public function __construct( Password_Requirement ...$requirements ) { $this->requirements = $requirements; }

	public function run() {
		add_action( 'itsec_register_password_requirements', [ $this, 'register_requirements' ] );
	}

	public function register_requirements() {
		array_walk( $this->requirements, [ ITSEC_Lib_Password_Requirements::class, 'register' ] );
	}
}
