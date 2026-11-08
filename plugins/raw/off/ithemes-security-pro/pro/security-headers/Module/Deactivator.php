<?php

namespace iThemesSecurity\Security_Headers\Module;

use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Security_Headers\Configuration\Headers_Registration;
use ITSEC_Response;

final class Deactivator implements Runnable {

	private Headers_Registration $headers_registration;

	public function __construct(
		Headers_Registration $headers_registration
	) {
		$this->headers_registration = $headers_registration;
	}

	public function run(): void {
		remove_filter(
			'itsec_lib_server_config_headers',
			[ $this->headers_registration, 'filter_server_config_headers' ]
		);
		ITSEC_Response::regenerate_server_config();
	}
}
