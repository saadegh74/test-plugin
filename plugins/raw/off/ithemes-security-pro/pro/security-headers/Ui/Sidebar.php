<?php

namespace iThemesSecurity\Security_Headers\Ui;

use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Security_Headers\Repository\X_Frame_Options_Repository;
use iThemesSecurity\Security_Headers\Settings\Settings;
use ITSEC_Core;

final class Sidebar implements Runnable {

	private Settings $settings;

	public function __construct(
		Settings $settings
	) {
		$this->settings          = $settings;
	}

	/**
	 * @inheritDoc
	 */
	public function run(): void {
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );
		add_action( 'init', [ $this, 'register_post_meta' ] );
	}

	/**
	 * @action enqueue_block_editor_assets
	 */
	public function enqueue_block_editor_assets(): void {
		if ( ! ITSEC_Core::current_user_can_manage() ) {
			return;
		}

		wp_enqueue_script( 'itsec-security-headers-sidebar' );
	}

	/**
	 * @action init
	 */
	public function register_post_meta(): void {
		register_post_meta( '', X_Frame_Options_Repository::META_KEY, [
			'show_in_rest'      => [
				'schema' => $this->settings->get_settings_schema()['properties']['x_frame_options'],
			],
			'single'            => true,
			'type'              => 'string',
			'default'           => '',
			'auth_callback'     => [ ITSEC_Core::class, 'current_user_can_manage' ],
		] );
	}
}
