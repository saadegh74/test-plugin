<?php

namespace iThemesSecurity\Security_Headers\Notifications;

use iThemesSecurity\Security_Headers\Provider;
use ITSEC_Core;
use ITSEC_Lib_Utility;
use ITSEC_Mail;
use WP_Error;

final class Mail_Factory {

	public function make( WP_Error $error ): ITSEC_Mail {
		$nc   = ITSEC_Core::get_notification_center();
		$mail = $nc->mail();

		$mail->set_subject( __( 'Security Headers Misconfigured', 'it-l10n-ithemes-security-pro' ) );

		$messages = $error->get_error_messages();
		$header   = array_shift( $messages );

		$mail->add_header(
			$header,
			''
		);
		$mail->add_list( $messages, false, true );

		$mail->add_info_box( sprintf(
				ITSEC_Lib_Utility::is_nginx()
					? __( 'Try to reload Nginx or <a href="%s">disable the "Use server config" setting</a>.', 'it-l10n-ithemes-security-pro' )
					: __( 'Check server configuration or <a href="%s">disable the "Use server config" setting</a>.', 'it-l10n-ithemes-security-pro' ),
				esc_url( ITSEC_Core::get_settings_module_url( Provider::NAME ) )
			)
		);

		$mail->add_footer( false );

		return $mail;
	}
}
