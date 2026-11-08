<?php

namespace iThemesSecurity\Security_Headers\Configuration;

use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Security_Headers\Provider;
use iThemesSecurity\Security_Headers\Repository\X_Frame_Options_Repository;
use ITSEC_Modules;

final class Headers_Registration implements Runnable {

	private X_Frame_Options_Repository $x_frame_options_repository;

	public function __construct(
		X_Frame_Options_Repository $x_frame_options_repository
	) {
		$this->x_frame_options_repository = $x_frame_options_repository;
	}

	public function run(): void {
		add_filter( 'itsec_lib_php_headers', [ $this, 'filter_php_headers' ] );
		add_filter( 'itsec_lib_server_config_headers', [ $this, 'filter_server_config_headers' ] );
	}

	/**
	 * Provide headers that should be added to the PHP response. There are three possible conditions when we send headers:
	 * 1. The sever config is disabled. We send all global headers with potentially X-Frame-Options override for the current post.
	 * 2. The current post has a post-specific X-Frame-Options header set. We send that X-Frame-Options header.
	 * 3. The X-Frame-Options header is enabled for any of the posts. We sent the global X-Frame-Options header because it was not written to the server config.
	 *
	 * @filter itsec_lib_php_headers
	 */
	public function filter_php_headers( array $headers ): array {
		$use_server_config      = (bool) ITSEC_Modules::get_setting( Provider::NAME, 'use_server_config' );
		$global_x_frame_options = ITSEC_Modules::get_setting( Provider::NAME, 'x_frame_options' );

		if ( ! $use_server_config ) {
			$headers = array_merge(
				$headers,
				$this->get_global_headers(),
				$global_x_frame_options ? [ 'X-Frame-Options' => $global_x_frame_options ] : []
			);
		}

		$post_id = get_the_ID();
		if ( ! is_int( $post_id ) || $post_id <= 0 ) {
			return $headers;
		}

		$post_specific_x_frame_options = $this->x_frame_options_repository->get_x_frame_options( $post_id );
		if ( $post_specific_x_frame_options !== '' ) {
			$headers['X-Frame-Options'] = $post_specific_x_frame_options;

			return $headers;
		}

		if ( ! $this->x_frame_options_repository->is_enabled_for_any_post() ) {
			return $headers;
		}

		if ( $global_x_frame_options !== '' ) {
			$headers['X-Frame-Options'] = $global_x_frame_options;
		}

		return $headers;
	}

	/**
	 * Provide headers that should be added to the server configuration.
	 *
	 * @filter itsec_lib_server_config_headers
	 */
	public function filter_server_config_headers( array $headers ): array {
		if ( ! ITSEC_Modules::get_setting( Provider::NAME, 'use_server_config' ) ) {
			return $headers;
		}

		$headers = array_merge( $headers, $this->get_global_headers() );
		if ( $this->x_frame_options_repository->is_enabled_for_any_post() ) {
			return $headers;
		}

		// If a post-specific X-Frame-Options header is not set, we can write it to the server config.
		$x_frame_options = ITSEC_Modules::get_setting( Provider::NAME, 'x_frame_options' );
		if ( $x_frame_options !== '' ) {
			$headers['X-Frame-Options'] = $x_frame_options;
		}

		return $headers;
	}

	private function get_global_headers(): array {
		$headers =
			[
				'X-Content-Type-Options'  => ITSEC_Modules::get_setting( Provider::NAME, 'x_content_type_options' ),
				'Referrer-Policy'         => ITSEC_Modules::get_setting( Provider::NAME, 'referrer_policy' ),
				'Content-Security-Policy' => ITSEC_Modules::get_setting( Provider::NAME, 'content_security_policy' ),
			];

		return array_filter( $headers );
	}
}
