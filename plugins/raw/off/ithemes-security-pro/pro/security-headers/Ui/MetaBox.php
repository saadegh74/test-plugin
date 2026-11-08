<?php

namespace iThemesSecurity\Security_Headers\Ui;

use InvalidArgumentException;
use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Headers\ITSEC_Headers_Sanitizer;
use iThemesSecurity\Security_Headers\Repository\X_Frame_Options_Repository;
use iThemesSecurity\Security_Headers\Settings\Settings;
use ITSEC_Core;
use ITSEC_Lib;

final class MetaBox implements Runnable {

	private const META_BOX_ACTION = 'itsec_security_headers';
	private const META_BOX_NOTICES = 'itsec_security_headers_notices';

	private X_Frame_Options_Repository $x_frame_options_repository;
	private ITSEC_Headers_Sanitizer $sanitizer;
	private Settings $settings;

	/**
	 * @var list<array{type: 'error'|'warning'|'info', message: string}>
	 */
	private array $notices = [];

	public function __construct(
		X_Frame_Options_Repository $repository,
		ITSEC_Headers_Sanitizer $sanitizer,
		Settings $settings
	) {
		$this->x_frame_options_repository = $repository;
		$this->sanitizer                  = $sanitizer;
		$this->settings                   = $settings;
	}

	public function run(): void {
		add_action( 'add_meta_boxes', [ $this, 'add_security_headers_meta_box' ] );
		add_filter( 'filter_block_editor_meta_boxes', [ $this, 'remove_security_headers_meta_box' ] );
		add_action( 'save_post', [ $this, 'save_security_headers_meta_box' ] );

		add_action( 'shutdown', [ $this, 'save_notices' ] );
		add_action( 'admin_notices', [ $this, 'render_notices' ] );
	}

	/**
	 * Register the metabox.
	 *
	 * @action add_meta_boxes
	 */
	public function add_security_headers_meta_box(): void {
		if ( ! ITSEC_Core::current_user_can_manage() ) {
			return;
		}

		$post_types = get_post_types( [ 'public' => true, 'show_ui' => true ] );
		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'itsec_security_headers_meta_box',
				esc_html__( 'Security Headers', 'it-l10n-ithemes-security-pro' ),
				function () {
					ITSEC_Lib::render( dirname( __DIR__ ) . '/templates/meta-box.php', [
							'action'                         => self::META_BOX_ACTION,
							'x_frame_options_name'           => X_Frame_Options_Repository::META_KEY,
							'x_frame_options'                => $this->x_frame_options_repository->get_x_frame_options( get_the_ID() ),
							'x_frame_options_select_options' => $this->settings->get_settings_schema()['properties']['x_frame_options']['enum'],
						]
					);
				},
				$post_type,
				'normal',
			);
		}
	}

	/**
	 * Removes the security headers meta box from the editor screen for public post types.
	 *
	 * @filter filter_block_editor_meta_boxes
	 */
	public function remove_security_headers_meta_box( array $meta_boxes ): array {
		$post_types = get_post_types( [ 'public' => true, 'show_ui' => true ] );
		foreach ( $post_types as $post_type ) {
			unset( $meta_boxes[ $post_type ]['normal']['default']['itsec_security_headers_meta_box'] );
		}

		return $meta_boxes;
	}

	/**
	 * Save the metabox data.
	 *
	 * @action save_post
	 */
	public function save_security_headers_meta_box( $post_id ): void {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$nonce = $_POST[ self::META_BOX_ACTION . '_nonce' ] ?? null;
		if ( $nonce === null ) {
			return;
		}

		if ( ! wp_verify_nonce( $nonce, self::META_BOX_ACTION ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! ITSEC_Core::current_user_can_manage() ) {
			return;
		}

		$x_frame_options = $_POST[ X_Frame_Options_Repository::META_KEY ] ?? null;

		$result = rest_validate_value_from_schema( $x_frame_options, $this->settings->get_settings_schema()['properties']['x_frame_options'] );
		if ( is_wp_error( $result ) ) {
			$this->add_notice( 'error', $result->get_error_message() );

			return;
		}

		try {
			$this->sanitizer->assert_valid_header_value( $x_frame_options );
		} catch ( InvalidArgumentException $e ) {
			$this->add_notice( 'error', __( 'The header value contains invalid characters: newlines and null bytes are not allowed.', 'it-l10n-ithemes-security-pro' ) );

			return;
		}

		if ( $x_frame_options === '' ) {
			$this->x_frame_options_repository->delete_x_frame_options( $post_id );

			return;
		}

		$this->x_frame_options_repository->update_x_frame_options( $post_id, $x_frame_options );
	}

	/**
	 * Add a notice to be displayed after the post saved redirect.
	 *
	 * @param 'info'|'warning'|'error' $type
	 * @param string $message
	 *
	 */
	public function add_notice( string $type, string $message ): void {
		$this->notices[] = [
			'type'    => $type,
			'message' => $message,
		];
	}

	/**
	 * Store notices in DB transient for output after post saved redirect.
	 *
	 * @action shutdown
	 */
	public function save_notices(): void {
		if ( count( $this->notices ) === 0 ) {
			return;
		}

		set_transient( self::META_BOX_NOTICES, $this->notices, 120 );
	}

	/**
	 * Output stored notices.
	 *
	 * @action admin_notices
	 */
	public function render_notices(): void {
		$notices = get_transient( self::META_BOX_NOTICES );
		if ( ! is_array( $notices ) ) {
			return;
		}

		delete_transient( self::META_BOX_NOTICES );
		$notices = array_filter(
			$notices,
			static fn( $notice ) => isset( $notice['type'], $notice['message'] )
			                        && in_array( $notice['type'], [
					'error',
					'warning',
					'info'
				], true )
			                        && is_string( $notice['message'] ) && $notice['message'] !== ''
		);

		if ( count( $notices ) === 0 ) {
			return;
		}

		ITSEC_Lib::render( dirname( __DIR__ ) . '/templates/notices.php', [
			'notices' => $notices
		] );
	}
}
