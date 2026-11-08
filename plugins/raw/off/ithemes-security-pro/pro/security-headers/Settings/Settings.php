<?php

namespace iThemesSecurity\Security_Headers\Settings;

use iThemesSecurity\Config_Settings;
use iThemesSecurity\Contracts\Runnable;
use ITSEC_Modules;

final class Settings extends Config_Settings implements Runnable {

	public function run(): void {
		ITSEC_Modules::register_settings( $this );
	}

	protected function handle_settings_changes( $old_settings ): void {
		parent::handle_settings_changes( $old_settings );

		if ( array_diff_assoc( $this->settings, $old_settings ) ) {
			do_action( 'itsec_security_headers_settings_changed' );
		}
	}
}
