<?php

namespace iThemesSecurity\Security_Headers\Headers_Check;

use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Security_Headers\Provider;
use ITSEC_Log;

final class Scheduler implements Runnable {

	private Checker $checker;

	public function __construct( Checker $checker ) {
		$this->checker = $checker;
	}

	public function run(): void {
		add_action( 'itsec_scheduled_security-headers-check', [ $this, 'check_security_headers' ] );
	}

	public function check_security_headers(): void {
		$result = $this->checker->check_security_headers();

		if ( $result === true ) {
			return;
		}

		if ( $result->get_error_code() === Checker::ERROR_REQUEST_FAILED ) {
			ITSEC_Log::add_error( Provider::NAME, $result->get_error_code(), $result->errors );

			return;
		}

		ITSEC_Log::add_critical_issue( Provider::NAME, $result->get_error_code(), $result->errors );

		do_action( 'itsec_security_headers_check_failed', $result );
	}
}
