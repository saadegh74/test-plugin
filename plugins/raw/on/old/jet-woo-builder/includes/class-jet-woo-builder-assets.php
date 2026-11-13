<?php
/**
 * JetWooBuilder Assets class.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Woo_Builder_Assets' ) ) {

	/**
	 * Define Jet_Woo_Builder_Assets class
	 */
	class Jet_Woo_Builder_Assets {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Widget scripts mapping.
		 *
		 * @since 2.3.0
		 * @var array
		 */
		private $widget_scripts = [
			'jet-woo-builder-archive-add-to-cart.default' => 'jet-woo-builder-archive-add-to-cart',
			'jet-cart-table.default'                      => 'jet-woo-builder-cart-table',
			'jet-woo-categories.default'                  => 'jet-woo-builder-categories',
			'jet-woo-products.default'                    => 'jet-woo-builder-products-grid',
			'jet-woo-products-list.default'               => 'jet-woo-builder-products-list',
			'jet-woo-builder-products-loop.default'       => 'jet-woo-builder-products-loop',
			'jet-single-add-to-cart.default'              => 'jet-woo-builder-single-add-to-cart',
			'jet-single-images.default'                   => 'jet-woo-builder-single-images',
			'jet-single-tabs.default'                     => 'jet-woo-builder-single-tabs',
		];

		/**
		 * Already printed widget scripts.
		 *
		 * @since 2.3.0
		 * @var array
		 */
		private $widget_scripts_printed = [];

		public function init() {
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_elementor_pro_empty_cart_notices_assets' ], 20 );
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
			add_action( 'elementor/frontend/before_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
			add_action( 'elementor/frontend/after_enqueue_scripts', [ 'WC_Frontend_Scripts', 'localize_printed_scripts' ], 5 );
		}

		private $widget_styles_registered = false;
		private $widget_style_map         = [];

		private function register_widget_styles() {

			if ( $this->widget_styles_registered ) {
				return;
			}

			$base_path = jet_woo_builder()->plugin_path( 'assets/css/widgets/' );
			$base_url  = jet_woo_builder()->plugin_url( 'assets/css/widgets/' );
			$version   = jet_woo_builder()->get_version();

			foreach ( glob( $base_path . '**/*.css' ) as $file ) {

				$rel  = str_replace( $base_path, '', $file );
				$slug = basename( $file, '.css' );

				$normalized = preg_replace( '/^jet-woo-builder-/', '', $slug );
				$handle     = 'jet-woo-builder-' . $normalized; 

				wp_register_style( $handle, $base_url . $rel, [], $version );

				$this->widget_style_map[ $slug ] = $handle;
			}

			$this->widget_styles_registered = true;
		}

		public function get_widget_style_handle( $slug ) {
			if ( ! $this->widget_styles_registered ) {
				$this->register_widget_styles();
			}

			return isset( $this->widget_style_map[ $slug ] )
				? $this->widget_style_map[ $slug ]
				: false;
		}

		/**
		 * Enqueue styles.
		 *
		 * Enqueue public-facing stylesheets.
		 *
		 * @since  1.0.0
		 * @since  2.1.6 Refactored. Changed some scripts from `wp_enqueue_style` to `wp_register_style`.
		 * @access public
		 *
		 * @return void
		 */
		public function enqueue_styles() {

			wp_register_style(
				'jet-woo-builder',
				jet_woo_builder()->plugin_url( 'assets/css/frontend.css' ),
				apply_filters( 'jet-woo-builder/frontend/styles-dependencies', [] ),
				jet_woo_builder()->get_version()
			);

			wp_register_style(
				'jet-woo-builder-frontend-font',
				jet_woo_builder()->plugin_url( 'assets/css/lib/jetwoobuilder-frontend-font/css/jetwoobuilder-frontend-font.css' ),
				false,
				jet_woo_builder()->get_version()
			);

			$font_path = WC()->plugin_url() . '/assets/fonts/';

			wp_add_inline_style( 'jet-woo-builder', '@font-face {
				font-family: "WooCommerce";
				font-weight: normal;
				font-style: normal;
				src: url("' . $font_path . 'WooCommerce.eot");
				src: url("' . $font_path . 'WooCommerce.eot?#iefix") format("embedded-opentype"),
					 url("' . $font_path . 'WooCommerce.woff") format("woff"),
					 url("' . $font_path . 'WooCommerce.ttf") format("truetype"),
					 url("' . $font_path . 'WooCommerce.svg#WooCommerce") format("svg");
			}' );

			$this->register_widget_styles();
		}

		/**
		 * Enqueue Elementor Pro notices assets if the widget can be injected by the empty cart template.
		 *
		 * WooCommerce replaces the cart markup with the empty cart template over AJAX when the last
		 * cart item is removed. If the Elementor Pro Notices widget exists only in this empty cart
		 * template, Elementor does not detect it during the initial cart page render, so its handler
		 * and widget stylesheet are not loaded until a full page refresh.
		 *
		 * @return void
		 */
		public function enqueue_elementor_pro_empty_cart_notices_assets() {

			if ( ! function_exists( 'is_cart' ) || ! is_cart() ) {
				return;
			}

			if ( ! function_exists( 'jet_woo_builder' ) || empty( jet_woo_builder()->woocommerce ) ) {
				return;
			}

			$template = jet_woo_builder()->woocommerce->get_custom_empty_cart_template();

			if ( ! $template ) {
				return;
			}

			$elementor_data = get_post_meta( $template, '_elementor_data', true );

			if ( ! is_string( $elementor_data ) || false === strpos( $elementor_data, 'woocommerce-notices' ) ) {
				return;
			}

			if ( wp_script_is( 'elementor-pro-frontend', 'registered' ) ) {
				wp_enqueue_script( 'elementor-pro-frontend' );
			}

			if (
				! wp_style_is( 'widget-woocommerce-notices', 'registered' )
				&& defined( 'ELEMENTOR_PRO_URL' )
				&& defined( 'ELEMENTOR_PRO_VERSION' )
			) {
				wp_register_style(
					'widget-woocommerce-notices',
					ELEMENTOR_PRO_URL . 'assets/css/widget-woocommerce-notices' . ( is_rtl() ? '-rtl' : '' ) . '.min.css',
					[ 'elementor-frontend' ],
					ELEMENTOR_PRO_VERSION
				);
			}

			if ( wp_style_is( 'widget-woocommerce-notices', 'registered' ) ) {
				wp_enqueue_style( 'widget-woocommerce-notices' );
			}

		}

		/**
		 * Enqueue admin assets.
		 *
		 * @return void
		 */
		public function enqueue_admin_assets() {
			wp_register_script(
				'jet-woo-builder-tippy',
				jet_woo_builder()->plugin_url( 'assets/lib/tippy/tippy.all.min.js' ),
				[],
				'2.5.4',
				true
			);
		}

		/**
		 * Enqueue scripts.
		 *
		 * Enqueue plugin scripts only with elementor scripts.
		 *
		 * @since 2.1.11 Added `jet-plugins` script.
		 */
		public function enqueue_scripts() {

			wp_register_script(
				'jet-plugins',
				jet_woo_builder()->plugin_url( 'assets/lib/jet-plugins/jet-plugins.js' ),
				[ 'jquery' ],
				'1.0.0',
				true
			);

			wp_enqueue_script(
				'jet-woo-builder',
				jet_woo_builder()->plugin_url( 'assets/js/frontend' . $this->suffix() . '.js' ),
				apply_filters( 'jet-woo-builder/frontend/script-dependencies', [
					'jquery',
					'elementor-frontend',
					'jet-plugins',
				] ),
				jet_woo_builder()->get_version(),
				true
			);

			global $wp_query;

			wp_localize_script(
				'jet-woo-builder',
				'jetWooBuilderData',
				apply_filters( 'jet-woo-builder/frontend/localize-data', [
					'ajax_url'                => esc_url( admin_url( 'admin-ajax.php' ) ),
					'products'                => json_encode( $wp_query->query_vars ),
					'single_ajax_add_to_cart' => 'yes' === jet_woo_builder_shop_settings()->get( 'use_ajax_add_to_cart' ),
				] )
			);

		}

		public function add_inline_widget_script( $widget_name ) {

			if ( empty( $this->widget_scripts[ $widget_name ] ) ) {
				return;
			}

			$handle = 'jet-woo-builder';

			// Ensure main script is registered/enqueued before adding inline scripts.
			if (
				! wp_script_is( $handle, 'registered' )
				&& ! wp_script_is( $handle, 'enqueued' )
				&& ! wp_script_is( $handle, 'done' )
			) {
				$this->enqueue_scripts();
			}

			if ( isset( $this->widget_scripts_printed[ $widget_name ] ) ) {
				return;
			}

			$file_name = $this->widget_scripts[ $widget_name ] . $this->suffix() . '.js';
			$file_path = jet_woo_builder()->plugin_path( 'assets/js/widgets/' . $file_name );

			if ( ! file_exists( $file_path ) ) {
				return;
			}

			$script = file_get_contents( $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			if ( false === $script || '' === trim( $script ) ) {
				return;
			}

			$result = wp_add_inline_script(
				$handle,
				"\n/* JetWooBuilder widget script: {$widget_name} */\n" . $script,
				'after'
			);

			if ( ! $result ) {
				return;
			}

			$this->widget_scripts_printed[ $widget_name ] = true;
		}

		/**
		 * Returns minified suffix for plugin scripts
		 *
		 * @return string
		 */
		public function suffix() {
			return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;

		}

	}

}

/**
 * Returns instance of Jet_Woo_Builder_Assets
 *
 * @return object
 */
function jet_woo_builder_assets() {
	return Jet_Woo_Builder_Assets::get_instance();
}
