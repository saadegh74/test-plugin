<?php
/**
 * Compare Render Class
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'Jet_CW_Compare_Render' ) ) {

	class Jet_CW_Compare_Render {

		public function __construct() {
			add_action( 'wp_ajax_jet_update_compare_list', [ $this, 'update_compare_list' ] );
			add_action( 'wp_ajax_nopriv_jet_update_compare_list', [ $this, 'update_compare_list' ] );
			add_action( 'wp_footer', [ $this, 'render_compare_messages' ] );
		}

		/**
		 * Processes compare buttons actions.
		 *
		 * @since  1.0.0
		 */
		function update_compare_list() {

			check_ajax_referer( 'jet-cw-compare', 'nonce' );

			$pid     = isset( $_POST['pid'] )     ? absint( wp_unslash( $_POST['pid'] ) )     : 0;
			$context = isset( $_POST['context'] ) ? sanitize_text_field( wp_unslash( $_POST['context'] ) ) : '';

			// Hook fires before adding any data into the wishlist store.
			do_action( 'jet-cw/compare/render/before-add-to-compare', $pid, $context, $this );

			$data = jet_cw()->compare_data->update_data_compare( $pid, $context );

			wp_send_json_success( [
				'content'           => $this->render_content( $pid ),
				'compareItemsCount' => count( $data ),
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

			foreach ( $widgets['compare'] as $selector => $widget_data ) {
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
				case 'jet-compare-count-button' :
					jet_cw_widgets_functions()->get_compare_count_button( $widget_setting );
					break;
				case 'jet-compare-button' :
					jet_cw_widgets_functions()->get_add_to_compare_button( $widget_setting, $product_id );
					break;
				case 'jet-compare' :
					jet_cw_widgets_functions()->get_widget_compare_table( $widget_setting );
					break;
				default:
					do_action( 'jet-cw/compare/render/get-content/' . $widget_type, $widget_setting, $product_id );
					break;
			}
		}

		/**
		 * Render compare button.
		 *
		 * @param array $settings List of widget settings.
		 */
		public function render_compare_button( $settings ) {

			global $product;

			if ( $product ) {
				$product_id = is_a( $product, 'WC_Product' ) ? $product->get_id() : $product->ID;
				$selector   = 'a.jet-compare-button__link[data-product-id="{pid}"][data-widget-id="' . $settings['_widget_id'] . '"]';

				jet_cw()->widgets_store->store_widgets_types( 'jet-compare-button', $selector, $settings, 'compare' );

				echo '<div class="jet-compare-button__container">';
				jet_cw_widgets_functions()->get_add_to_compare_button( $settings, $product_id );
				echo '</div>';
			} else {
				printf( '<h5 class=jet-compare-button--missing">%s</h5>', esc_html__( 'Product ID not found.', 'jet-cw' ) );
			}

		}

		/**
		 * Render compare message.
		 *
		 * Render maximum items in compare list message.
		 *
		 * @since 1.0.0
		 * @since 1.5.2 Added option value for message.
		 */
		public function render_compare_messages() {
			printf(
				'<div class="jet-compare-message jet-compare-message--max-items" style="display: none">%s</div>',
				esc_html__( jet_cw()->settings->get( 'compare_message_max_items' ), 'jet-cw' )
			);
		}

	}

}