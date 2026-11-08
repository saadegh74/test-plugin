<?php

namespace iThemesSecurity\Security_Headers\Configuration;

use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Security_Headers\Repository\X_Frame_Options_Repository;
use ITSEC_Lib_Utility;
use ITSEC_Response;
use WP_Post;

final class Server_Config implements Runnable {

	private X_Frame_Options_Repository $x_frame_options_repository;

	public function __construct(
		X_Frame_Options_Repository $x_frame_options_repository
	) {
		$this->x_frame_options_repository = $x_frame_options_repository;
	}

	public function run(): void {
		add_action( 'itsec_security_headers_settings_changed', [ $this->x_frame_options_repository, 'clear_cache' ], 5 );
		add_action( 'itsec_security_headers_settings_changed', [ $this, 'regenerate_server_config' ] );
		add_action( 'added_post_meta', [ $this, 'handle_post_meta_change' ], 10, 3 );
		add_action( 'updated_post_meta', [ $this, 'handle_post_meta_change' ], 10, 3 );
		add_action( 'deleted_post_meta', [ $this, 'handle_post_meta_change' ], 10, 3 );
		add_action( 'transition_post_status', [ $this, 'handle_post_status_change' ], 10, 3 );
	}

	/**
	 * Regenerate the server config file.
	 *
	 * @action itsec_security_headers_settings_changed
	 */
	public function regenerate_server_config(): void {
		ITSEC_Response::regenerate_server_config();

		if ( ITSEC_Lib_Utility::is_nginx() ) {
			ITSEC_Response::add_info(
				__( 'You must restart your NGINX server for the changes to take effect.', 'it-l10n-ithemes-security-pro' )
			);
		}
	}

	/**
	 * @action added_post_meta
	 * @action updated_post_meta
	 * @action deleted_post_meta
	 *
	 * @param int|list<numeric-string> $meta_ids Array of meta IDs for deleted_post_meta action.
	 *                                             Scalar value for added_post_meta and updated_post_meta actions.
	 * @param int $object_id
	 * @param string $meta_key
	 */
	public function handle_post_meta_change( $meta_ids, $object_id, $meta_key ): void {
		$status = get_post_status( $object_id );
		if ( $status !== 'publish' ) {
			return;
		}

		if ( $meta_key === X_Frame_Options_Repository::META_KEY ) {
			do_action( 'itsec_security_headers_settings_changed' );
		}
	}

	/**
	 * The status of the post impacts server config generation,
	 * so we fire settings changed action when a post published or unpublished.
	 *
	 * @action transition_post_status
	 */
	public function handle_post_status_change( string $new_status, string $old_status, WP_Post $post ): void {
		$x_frame_options = (string) get_post_meta( $post->ID, X_Frame_Options_Repository::META_KEY, true );
		if ( $x_frame_options === '' ) {
			return;
		}

		if ( $new_status === 'publish' || $old_status === 'publish' ) {
			do_action( 'itsec_security_headers_settings_changed' );
		}
	}
}
