<?php

namespace iThemesSecurity\Security_Headers\Settings;

use InvalidArgumentException;
use iThemesSecurity\Config_Validator;
use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Headers\ITSEC_Headers_Sanitizer;
use iThemesSecurity\Module_Config;
use ITSEC_Modules;
use WP_Error;

final class Validator extends Config_Validator implements Runnable {

	private ITSEC_Headers_Sanitizer $sanitizer;

	public function __construct(
		Module_Config $config,
		ITSEC_Headers_Sanitizer $sanitizer
	) {
		parent::__construct( $config );

		$this->sanitizer = $sanitizer;
	}

	public function run(): void {
		ITSEC_Modules::register_validator( $this );
	}

	protected function validate_settings(): void {
		parent::validate_settings();

		if ( ! $this->can_save() ) {
			return;
		}

		foreach ( $this->settings as $header_value ) {
			if ( $header_value === '' ) {
				continue;
			}

			try {
				$this->sanitizer->assert_valid_header_value( $header_value );
			} catch ( InvalidArgumentException $e ) {
				$this->add_error( new WP_Error(
					'itsec.security-headers.invalid-header-value',
					esc_html__(
						'The header value contains invalid characters: newlines and null bytes are not allowed.',
						'it-l10n-ithemes-security-pro'
					)
				) );
				$this->set_can_save( false );
			}
		}
	}
}
