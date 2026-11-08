<?php

namespace iThemesSecurity\Security_Headers\Logs;

use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Security_Headers\Provider;
use ITSEC_Core;
use ITSEC_Lib_Highlighted_Logs;
use ITSEC_Lib_Utility;

final class Highlighter implements Runnable {

	private const LOG_ID = 'security-headers-check';

	public function run(): void {
		add_action( 'itsec_register_highlighted_logs', [ $this, 'register_highlight' ] );
		add_filter( 'itsec_highlighted_log_' . self::LOG_ID . '_notice_title', [
			$this,
			'filter_highlight_title'
		] );
		add_filter( 'itsec_highlighted_log_' . self::LOG_ID . '_notice_message', [
			$this,
			'filter_highlight_message'
		], 10, 2 );
	}

	/**
	 * @action itsec_register_highlighted_logs
	 */
	public function register_highlight(): void {
		ITSEC_Lib_Highlighted_Logs::register_dynamic_highlight( self::LOG_ID, [
			'module' => Provider::NAME,
			'type'   => 'critical-issue',
		] );
	}

	/**
	 * @filter itsec_highlighted_log_security-headers-check_notice_title
	 */
	public function filter_highlight_title(): string {
		return __( 'Security Headers are misconfigured.', 'it-l10n-ithemes-security-pro' );
	}

	/**
	 * @filter itsec_highlighted_log_security-headers-check_notice_message
	 */
	public function filter_highlight_message(): string {
		return sprintf(
			ITSEC_Lib_Utility::is_nginx()
				? __( 'Try to reload Nginx or <a href="%s">disable the "Use server config" setting</a>.', 'it-l10n-ithemes-security-pro' )
				: __( 'Check server configuration or <a href="%s">disable the "Use server config" setting</a>.', 'it-l10n-ithemes-security-pro' ),
			esc_url( ITSEC_Core::get_settings_module_url( Provider::NAME ) )
		);
	}
}
