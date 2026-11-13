<?php
/**
 * Wishlist Render Class
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'Jet_CW_Wishlist_Render' ) ) {

	class Jet_CW_Wishlist_Render {

		public function __construct() {
			add_action( 'wp_ajax_jet_update_wish_list', [ $this, 'update_wish_list' ] );
			add_action( 'wp_ajax_nopriv_jet_update_wish_list', [ $this, 'update_wish_list' ] );
		}

		/**
		 * Processes buttons actions.
		 *
		 * @since  1.0.0
		 */
		function update_wish_list() {

			check_ajax_referer( 'jet-cw-compare', 'nonce' );

			$pid     = isset( $_POST['pid'] )     ? absint( wp_unslash( $_POST['pid'] ) )     : 0;
			$context = isset( $_POST['context'] ) ? sanitize_text_field( wp_unslash( $_POST['context'] ) ) : '';

			// Hook fires before adding any data into the wishlist store.
			do_action( 'jet-cw/wishlist/render/before-add-to-wishlist', $pid, $context, $this );

			$data = jet_cw()->wishlist_data->update_data_wishlist( $pid, $context );

			wp_send_json_success( [
				'content'            => $this->render_content( $pid ),
				'wishlistItemsCount' => count( $data ),
			] );

		}

		/**
		 * Render content.
		 *
		 * @param string|int $product_id Product ID.
		 *
		 * @return array
		 */
		public function render_content( $product_id ) {

			$widgets         = jet_cw()->widgets_store->get_stored_widgets();
			$widgets_content = [];

			if ( empty( $widgets ) ) {
				return $widgets_content;
			}

			foreach ( $widgets['wishlist'] as $selector => $widget_data ) {
				$selector = str_replace( '{pid}', $product_id, urldecode( $selector ) );

				ob_start();
				$this->get_render_content_type( $widget_data['settings'], $product_id, $widget_data['type'] );
				$widgets_content[ $selector ] = ob_get_clean();
			}

			return $widgets_content;

		}

		/**
		 * Render current widget type.
		 *
		 * @param array      $widget_setting List of widget settings.
		 * @param string|int $product_id     Product ID.
		 * @param string     $widget_type    Widget type.
		 */
		public function get_render_content_type( $widget_setting, $product_id, $widget_type ) {
			switch ( $widget_type ) {
				case 'jet-wishlist-count-button' :
					jet_cw_widgets_functions()->get_wishlist_count_button( $widget_setting );
					break;
				case 'jet-wishlist-button' :
					jet_cw_widgets_functions()->get_add_to_wishlist_button( $widget_setting, $product_id );
					break;
				case 'jet-wishlist' :
					jet_cw_widgets_functions()->get_widget_wishlist( $widget_setting );
					break;
				default:
					do_action( 'jet-cw/wishlist/render/get-content/' . $widget_type, $widget_setting, $product_id );
					break;
			}
		}

		/**
		 * Render wishlist button.
		 *
		 * @param array $settings List of widget settings.
		 */
		public function render_wishlist_button( $settings ) {

			global $product;

			if ( $product ) {
				$product_id = is_a( $product, 'WC_Product' ) ? $product->get_id() : $product->ID;
				$selector   = 'a.jet-wishlist-button__link[data-product-id="{pid}"][data-widget-id="' . $settings['_widget_id'] . '"]';

				jet_cw()->widgets_store->store_widgets_types( 'jet-wishlist-button', $selector, $settings, 'wishlist' );

				echo '<div class="jet-wishlist-button__container">';
				jet_cw_widgets_functions()->get_add_to_wishlist_button( $settings, $product_id );
				echo '</div>';
			} else {
				printf( '<h5 class=jet-wishlist-button--missing">%s</h5>', esc_html__( 'Product ID not found.', 'jet-cw' ) );
			}

		}

	}

}