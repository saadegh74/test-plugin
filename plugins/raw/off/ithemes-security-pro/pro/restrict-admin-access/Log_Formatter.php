<?php declare( strict_types=1 );

namespace iThemesSecurity\Restrict_Admin_Access;

final class Log_Formatter {

	public const ADMIN_RESTRICTED = 'admin-restricted';

	/**
	 * Format the log display when viewing details.
	 *
	 * @filter itsec_logs_prepare_restrict-admin-access_entry_for_details_display
	 *
	 * @param array<string, mixed> $details The log details.
	 * @param array<string, mixed> $entry The log entry.
	 * @param string $code The log code.
	 * @param array<int, mixed> $code_data The log data.
	 *
	 * @return array<string, mixed>
	 */
	public function filter_entry_for_details_display( array $details, array $entry, string $code, array $code_data ): array {
		$entry = $this->filter_entry_for_list_display( $entry, $code, $code_data );

		$details['module']['content']      = $entry['module_display'];
		$details['description']['content'] = $entry['description'];

		return $details;
	}

	/**
	 * Format the log display for the logs table.
	 *
	 * @filter itsec_logs_prepare_restrict-admin-access_entry_for_list_display
	 *
	 * @param array<string, mixed> $entry The log entry.
	 * @param string $code The log code.
	 * @param array<int, mixed> $data The log data.
	 *
	 * @return array<string, mixed>
	 */
	public function filter_entry_for_list_display( array $entry, string $code, array $data ): array {
		$entry['module_display'] = esc_html__( 'Restrict Admin Access', 'it-l10n-ithemes-security-pro' );

		if ( $code === self::ADMIN_RESTRICTED ) {
			[ $user_id, $error ] = $data + [ null, null ];

			$user          = get_userdata( $user_id );
			$username      = $user ? $user->user_login : esc_html__( 'Unknown User', 'it-l10n-ithemes-security-pro' );
			$error_message = $error ?? esc_html__( 'Error not found', 'it-l10n-ithemes-security-pro' );

			/* translators: 1: Username, 2: Error Message */
			$entry['description'] = sprintf(
				esc_html__( '%s Restricted: %s', 'it-l10n-ithemes-security-pro' ),
				$username,
				$error_message
			);
		}

		return $entry;
	}

}
