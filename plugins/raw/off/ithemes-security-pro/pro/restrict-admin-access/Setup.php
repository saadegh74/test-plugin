<?php declare( strict_types=1 );

namespace iThemesSecurity\Restrict_Admin_Access;

use ITSEC_Modules;

final class Setup {

	/**
	 * Perform an upgrade for this module.
	 *
	 * @action itsec_modules_do_plugin_upgrade
	 *
	 * @param int $old_version The old version of the plugin.
	 *
	 * @return void
	 */
	public function upgrade( int $old_version ): void {
		// Merge authorized_administrator_ips into the global lockout_white_list ips and remove the setting.
		if ( $old_version < 4130 ) {
			$settings = ITSEC_Modules::get_settings( Provider::NAME );

			if ( isset( $settings['authorized_administrator_ips'] ) ) {
				$global_ips = ITSEC_Modules::get_setting( 'global', 'lockout_white_list', [] );
				$ips        = (array) $settings['authorized_administrator_ips'];

				if ( $global_ips ) {
					$merged_ips = array_merge( $global_ips, $ips );

					ITSEC_Modules::set_setting( 'global', 'lockout_white_list', $merged_ips );
				}

				unset( $settings['authorized_administrator_ips'] );

				ITSEC_Modules::set_settings( Provider::NAME, $settings );
			}
		}
	}
}
