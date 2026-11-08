<?php declare( strict_types=1 );

namespace iThemesSecurity\Restrict_Admin_Access\Rest;

use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Restrict_Admin_Access\Provider;
use ITSEC_Modules;
use WP_REST_Controller;

/**
 * Set up the available rest controllers for this module if it's active.
 */
final class Rest implements Runnable {

	/**
	 * @var WP_REST_Controller[]
	 */
	private array $controllers;

	public function __construct( WP_REST_Controller ...$controllers ) {
		$this->controllers = $controllers;
	}

	/**
	 * Launched for the Provider.
	 *
	 * @see Provider::__invoke()
	 *
	 * @return void
	 */
	public function run(): void {
		if ( ! ITSEC_Modules::is_active( Provider::NAME ) ) {
			return;
		}

		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register all our module's controllers.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		foreach ( $this->controllers as $controller ) {
			$controller->register_routes();
		}
	}

}
