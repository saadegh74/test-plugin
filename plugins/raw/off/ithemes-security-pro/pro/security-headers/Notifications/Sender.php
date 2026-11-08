<?php

namespace iThemesSecurity\Security_Headers\Notifications;

use iThemesSecurity\Contracts\Runnable;
use ITSEC_Core;
use WP_Error;

final class Sender implements Runnable {

	public const ID = 'security-headers-check';

	private Mail_Factory $mail_factory;

	public function __construct( Mail_Factory $mail_factory) {
		$this->mail_factory = $mail_factory;
	}

	public function run(): void {
		add_action( 'itsec_security_headers_check_failed', [ $this, 'send_notification' ] );
	}

	public function send_notification( WP_Error $error ): void {

		$nc = ITSEC_Core::get_notification_center();

		if ( ! $nc->is_notification_enabled( self::ID ) ) {
			return;
		}

		$mail = $this->mail_factory->make( $error );
		$mail->set_recipients( $nc->get_recipients( self::ID ) );

		$nc->send( self::ID, $mail );
	}
}
