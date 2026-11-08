<?php

namespace iThemesSecurity\Security_Headers\Headers_Check;

use iThemesSecurity\Security_Headers\Provider;
use iThemesSecurity\Security_Headers\Repository\X_Frame_Options_Repository;
use ITSEC_Modules;
use WP_Error;

/**
 * Service to check if security headers are properly configured and sent.
 */
final class Checker {

	public const ERROR_REQUEST_FAILED = 'request_failed';
	public const ERROR_HEADERS_MISCONFIGURED = 'headers_misconfigured';

	private X_Frame_Options_Repository $x_frame_options_repository;

	public function __construct(
		X_Frame_Options_Repository $x_frame_options_repository
	) {
		$this->x_frame_options_repository = $x_frame_options_repository;
	}

	/**
	 * Check if security headers are properly configured and sent.
	 *
	 * Makes a request to the home page, retrieves headers, and compares them
	 * with expected security headers from module settings.
	 *
	 * @return true|WP_Error True if headers are correct, WP_Error if there are issues.
	 */
	public function check_security_headers() {
		$response = wp_remote_get( home_url( '/' ) );

		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				self::ERROR_REQUEST_FAILED,
				sprintf(
					__( 'Failed to retrieve headers: %s', 'it-l10n-ithemes-security-pro' ),
					$response->get_error_message()
				)
			);
		}

		$actual_headers = wp_remote_retrieve_headers( $response );
		$error          = new WP_Error();

		$expected_headers = $this->get_expected_headers();
		foreach ( $expected_headers as $header_name => $expected_value ) {
			$actual_value = $actual_headers[ $header_name ] ?? null;

			if ( null === $actual_value ) {
				$error->add(
					'missing_header',
					sprintf(
						__( 'Missing header: %s (expected: %s)', 'it-l10n-ithemes-security-pro' ),
						$header_name,
						$expected_value
					)
				);
				continue;
			}

			$values = is_array( $actual_value ) ? $actual_value : [ $actual_value ];

			if ( count( $values ) > 1 ) {
				$error->add( 'duplicate_header', sprintf(
					__( 'Duplicate header: %s (found %d occurrences: %s)', 'it-l10n-ithemes-security-pro' ),
					$header_name,
					count( $values ),
					implode( ', ', $values )
				) );
				continue;
			}

			if ( $actual_value !== $expected_value ) {
				$error->add(
					'header_mismatch',
					sprintf(
						__( 'Header mismatch: %s (expected: "%s", actual: "%s")', 'it-l10n-ithemes-security-pro' ),
						$header_name,
						$expected_value,
						$actual_value
					)
				);
			}
		}

		if ( $error->has_errors() ) {
			$result = new WP_Error(
				self::ERROR_HEADERS_MISCONFIGURED,
				__( 'Security headers configuration issues detected.', 'it-l10n-ithemes-security-pro' ),
			);
			$result->merge_from( $error );

			return $result;
		}

		return true;
	}

	/**
	 * Get expected headers based on module settings.
	 *
	 * @return array<string, string> Expected headers.
	 */
	private function get_expected_headers(): array {
		$settings      = ITSEC_Modules::get_settings( Provider::NAME );
		$front_page_id = (int) get_option( 'page_on_front' );

		$expected                    = [];
		$expected['X-Frame-Options'] = $settings['x_frame_options'];
		$page_x_frame_options        = $front_page_id
			? $this->x_frame_options_repository->get_x_frame_options( $front_page_id )
			: null;
		if ( is_string( $page_x_frame_options ) && $page_x_frame_options !== '' ) {
			$expected['X-Frame-Options'] = $page_x_frame_options;
		}

		$expected['X-Content-Type-Options']  = $settings['x_content_type_options'];
		$expected['Referrer-Policy']         = $settings['referrer_policy'];
		$expected['Content-Security-Policy'] = $settings['content_security_policy'];

		return array_filter( $expected );
	}
}
