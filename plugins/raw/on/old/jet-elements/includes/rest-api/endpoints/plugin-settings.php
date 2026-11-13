<?php
namespace Jet_Elements\Endpoints;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Define Posts class
 */
class Plugin_Settings extends Base {

	/**
	 * [get_method description]
	 * @return [type] [description]
	 */
	public function get_method() {
		return 'POST';
	}

	/**
	 * Returns route name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'plugin-settings';
	}

	/**
	 * [callback description]
	 * @param  [type]   $request [description]
	 * @return function          [description]
	 */
	public function callback( $request ) {

		$data = $request->get_params();

		$current = get_option( jet_elements_settings()->key, array() );

		if ( is_wp_error( $current ) ) {
			return rest_ensure_response( [
				'status'  => 'error',
				'message' => __( 'Server Error', 'jet-elements' )
			] );
		}

		$settings = jet_elements_settings();
		$current = array_replace( $settings->sanitize( $current ), $settings->sanitize( $data ) );
		update_option( $settings->key, $current );
		$settings->settings = $current;

		return rest_ensure_response( [
			'status'  => 'success',
			'message' => __( 'Settings have been saved', 'jet-elements' )
		] );
	}

}
