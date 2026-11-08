<?php

namespace iThemesSecurity\Security_Headers\Notifications;

use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Security_Headers\Provider;
use ITSEC_Core;
use ITSEC_Notification_Center;

final class Registry implements Runnable {

	public function run(): void {
		add_filter( 'itsec_notifications', [ $this, 'register_notification' ] );
		add_filter( sprintf( 'itsec_%s_notification_strings', Sender::ID ), [ $this, 'notification_strings' ] );
	}

	/**
	 * Register the Security Headers Check notification.
	 *
	 * @param array $notifications
	 *
	 * @return array
	 */
	public function register_notification( array $notifications ): array {
		$notifications[ Sender::ID ] = [
			'recipient' => ITSEC_Notification_Center::R_USER_LIST,
			'optional'  => true,
			'module'    => Provider::NAME,
		];

		return $notifications;
	}

	/**
	 * Register the strings for the Security Headers Check notification.
	 *
	 * @return array
	 */
	public function notification_strings(): array {
		return [
			'label'       => __( 'Security Headers Check', 'it-l10n-ithemes-security-pro' ),
			'description' => sprintf(
				__( 'The %1$sSecurity Headers%2$s module sends an email if it detects that the server headers are misconfigured.', 'it-l10n-ithemes-security-pro' ),
				ITSEC_Core::get_link_for_settings_route( ITSEC_Core::get_settings_module_route( Provider::NAME ) ),
				'</a>'
			),
		];
	}
}
