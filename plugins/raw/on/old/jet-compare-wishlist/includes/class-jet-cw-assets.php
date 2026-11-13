<?php
/**
 * Compare & Wishlist Assets class
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'Jet_CW_Assets' ) ) {

	class Jet_CW_Assets {

		public function __construct() {
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		}

		/**
		 * Enqueue styles.
		 *
		 * Enqueue public-facing stylesheets.
		 *
		 * @since  1.0.0
		 * @since  1.5.8 Updated stylesheets registration.
		 *
		 * @return void
		 */
		public function enqueue_styles() {

			wp_register_style(
				'jet-cw-frontend',
				jet_cw()->plugin_url( 'assets/css/frontend.css' ),
				false,
				jet_cw()->get_version()
			);

			$widgets_with_styles = jet_cw()->integration->widgets_with_styles();

			foreach ( $widgets_with_styles as $widget_name ) {
				wp_register_style(
					"jet-cw-widget-{$widget_name}",
					jet_cw()->plugin_url( "assets/css/widgets/{$widget_name}.css" ),
					[ 'jet-cw-frontend' ],
					jet_cw()->get_version()
				);
			}

			wp_register_style(
				'jet-cw-frontend-font',
				jet_cw()->plugin_url( 'assets/lib/jet-cw-frontend-font/frontend-font.css' ),
				false,
				jet_cw()->get_version()
			);

		}

	}

}