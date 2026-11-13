<?php
/**
 * JetEngine compatibility package.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_CW_Engine_Package' ) ) {
	class Jet_CW_Engine_Package {

		public function __construct() {

			add_action( 'jet-engine/ajax-handlers/before-call-handler', [ $this, 'register_assets_on_ajax' ] );

			add_filter( 'jet-engine/ajax/get_listing/response', [ $this, 'define_cw_listing_lazy_load_data' ], 10, 2 );

		}

		/**
		 * Register assets on AJAX.
		 *
		 * @since 1.5.8
		 *
		 * @return void
		 */
		public function register_assets_on_ajax() {

			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- no state change, only enqueue styles.

			if ( filter_var( wp_unslash( $_POST['isEditMode'] ?? '' ), FILTER_VALIDATE_BOOLEAN ) ) {
				return;
			}

			jet_cw()->assets->enqueue_styles();

		}

		/**
		 * Define lazy loading data.
		 *
		 * Add the JetCompareWishlist widgets data to JetEngine response after lazy loading.
		 *
		 * @since 1.4.1
		 *
		 * @param array $response Response data list.
		 * @param array $settings Listing grid widget settings list.
		 *
		 * @return array
		 */
		public function define_cw_listing_lazy_load_data( $response, $settings ) {

			if ( 'yes' !== $settings['lazy_load'] ) {
				return $response;
			}

			$response['jetCompareWishlistWidgets'] = jet_cw()->widgets_store->get_widgets_types();

			return $response;

		}

	}
}

new Jet_CW_Engine_Package();
