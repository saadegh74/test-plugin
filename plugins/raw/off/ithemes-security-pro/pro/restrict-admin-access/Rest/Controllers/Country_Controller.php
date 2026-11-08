<?php declare( strict_types=1 );

namespace iThemesSecurity\Restrict_Admin_Access\Rest\Controllers;

use iThemesSecurity\Restrict_Admin_Access\Country\Country_Collection;
use iThemesSecurity\Restrict_Admin_Access\Provider;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * REST Country Controller.
 *
 * @route ithemes-security/v1/restrict-admin-access/countries
 */
final class Country_Controller extends WP_REST_Controller {

	private Country_Collection $collection;

	public function __construct( Country_Collection $collection ) {
		$this->collection = $collection;
		$this->namespace  = 'ithemes-security/v1';
		$this->rest_base  = sprintf( '%s/countries', Provider::NAME );
	}

	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			$this->rest_base,
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => 'ITSEC_Core::current_user_can_manage',
				],
			]
		);
	}

	/**
	 * Get the list of countries indexed by their ISO-3166 country code.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_items( $request ): WP_REST_Response {
		return new WP_REST_Response( $this->collection->all() );
	}

}
