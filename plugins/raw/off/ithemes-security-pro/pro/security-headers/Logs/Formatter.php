<?php

namespace iThemesSecurity\Security_Headers\Logs;

use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Security_Headers\Headers_Check\Checker;

final class Formatter implements Runnable {

	public function run(): void {
		add_filter( 'itsec_logs_prepare_security-headers_entry_for_list_display', [
			$this,
			'filter_entry_for_list_display'
		], 10, 2 );
		add_filter( 'itsec_logs_prepare_security-headers_entry_for_details_display', [
			$this,
			'filter_entry_for_details_display'
		], 10, 3 );
	}

	/**
	 * Format the log display for the logs table.
	 *
	 * @filter itsec_logs_prepare_security-headers_entry_for_list_display
	 *
	 * @param array<string, mixed> $entry The log entry.
	 * @param string $code The log code.
	 *
	 * @return array<string, mixed>
	 */
	public function filter_entry_for_list_display( array $entry, string $code ): array {
		$entry['module_display'] = esc_html__( 'Security Headers', 'it-l10n-ithemes-security-pro' );

		switch ( $code ) {
			case Checker::ERROR_REQUEST_FAILED:
				$entry['description'] = esc_html__( 'Check Request Failed', 'it-l10n-ithemes-security-pro' );
				break;
			case Checker::ERROR_HEADERS_MISCONFIGURED:
				$entry['description'] = esc_html__( 'Headers Misconfigured', 'it-l10n-ithemes-security-pro' );
				break;
		}

		return $entry;
	}

	/**
	 * Format the log display when viewing details.
	 *
	 * @filter itsec_logs_prepare_security-headers_entry_for_details_display
	 *
	 * @param array<string, mixed> $details The log details.
	 * @param array<string, mixed> $entry The log entry.
	 * @param string $code The log code.
	 *
	 * @return array<string, mixed>
	 */
	public function filter_entry_for_details_display( array $details, array $entry, string $code ): array {
		$entry = $this->filter_entry_for_list_display( $entry, $code );

		$details['module']['content']      = $entry['module_display'];
		$details['description']['content'] = $entry['description'];

		if ( ! isset( $entry['data'] ) || ! is_array( $entry['data'] ) ) {
			return $details;
		}

		$messages                = array_merge( ...array_values( $entry['data'] ) );
		$details['found_issues'] = [
			'header'  => esc_html__( 'Found Issues', 'it-l10n-ithemes-security-pro' ),
			'content' => implode( '<br>', $messages ),
		];

		return $details;
	}
}
