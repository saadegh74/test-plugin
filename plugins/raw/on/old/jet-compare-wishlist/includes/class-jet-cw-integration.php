<?php
/**
 * Class Compare & Wishlist Integration
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'Jet_CW_Integration' ) ) {

	class Jet_CW_Integration {

		/**
		 * Check if processing elementor widget
		 *
		 * @var boolean
		 */
		private $is_elementor_ajax = false;

		public function __construct() {

			add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ] );
			add_action( 'wp_ajax_elementor_render_widget', [ $this, 'set_elementor_ajax' ], 10, - 1 );

			if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' ) ) {
				add_action( 'elementor/widgets/register', [ $this, 'register_cw_widgets' ], 10 );
			} else {
				add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_cw_widgets' ], 10 );
			}

			add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'enqueue_icons_styles' ] );
			add_action( 'elementor/preview/enqueue_styles', [ $this, 'enqueue_icons_styles' ] );

		}

		/**
		 * Set $this->is_elementor_ajax to true on Elementor AJAX processing.
		 */
		public function set_elementor_ajax() {
			$this->is_elementor_ajax = true;
		}

		/**
		 * Check if we currently in Elementor mode.
		 *
		 * @return mixed|void
		 */
		public function in_elementor() {

			$result = false;

			if ( wp_doing_ajax() ) {
				$result = $this->is_elementor_ajax;
			} elseif ( Elementor\Plugin::instance()->editor->is_edit_mode() || Elementor\Plugin::instance()->preview->is_preview_mode() ) {
				$result = true;
			}

			// Allow to filter result before return
			return apply_filters( 'jet-cw/in-elementor', $result );

		}


		/**
		 * Register addon by file name
		 *
		 * @param string                     $file            File name.
		 * @param \Elementor\Widgets_Manager $widgets_manager Widgets manager instance.
		 */
		public function register_widgets( $file, $widgets_manager ) {

			$base  = basename( str_replace( '.php', '', $file ) );
			$class = ucwords( str_replace( '-', ' ', $base ) );
			$class = str_replace( ' ', '_', $class );
			$class = sprintf( 'Elementor\%s', $class );

			require $file;

			if ( class_exists( $class ) ) {
				if ( method_exists( $widgets_manager, 'register' ) ) {
					$widgets_manager->register( new $class );
				} else {
					$widgets_manager->register_widget_type( new $class );
				}
			}

		}

		/**
		 * Register cherry category for elementor if not exists.
		 */
		public function register_category() {
			\Elementor\Plugin::instance()->elements_manager->add_category(
				'jet-cw',
				[
					'title' => __( 'Jet Compare Wishlist', 'jet-cw' ),
					'icon'  => 'font',
				]
			);
		}

		/**
		 * Widgets with styles.
		 *
		 * This method returns the list of all the widgets that have styles.
		 *
		 * @since 1.5.8
		 *
		 * @return array The names of the widgets that have styles.
		 */
		public function widgets_with_styles(): array {
			return [
				'compare',
				'cw-button',
				'cw-count-button',
				'wishlist',
			];
		}

		/**
		 * Enqueue icons styles.
		 *
		 * @since 1.5.7
		 */
		public function enqueue_icons_styles() {
			wp_enqueue_style(
				'jet-cw-icons',
				jet_cw()->plugin_url( 'assets/lib/jet-cw-icons/icons.css' ),
				false,
				jet_cw()->get_version()
			);
		}

		/**
		 * Register plugin widgets.
		 *
		 * @since 1.0.0
		 * @since 1.5.6 Added theme fix and fixed typos.
		 *
		 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager instance.
		 */
		public function register_cw_widgets( $widgets_manager ) {

			// Fix WooCommerce hooks for Kava theme.
			if ( function_exists( 'kava_theme' ) ) {
				remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_rating', 20 );
			}

			$available_widgets = jet_cw()->settings->get( 'avaliable_widgets' );

			require jet_cw()->plugin_path( 'includes/base/class-jet-cw-base.php' );

			if ( filter_var( jet_cw()->compare_enabled, FILTER_VALIDATE_BOOLEAN ) ) {
				foreach ( glob( jet_cw()->plugin_path( 'includes/widgets/compare/' ) . '*.php' ) as $file ) {
					$slug = basename( $file, '.php' );

					if ( filter_var( $available_widgets[ $slug ], FILTER_VALIDATE_BOOLEAN ) || ! $available_widgets ) {
						$this->register_widgets( $file, $widgets_manager );
					}
				}
			}

			if ( filter_var( jet_cw()->wishlist_enabled, FILTER_VALIDATE_BOOLEAN ) ) {
				foreach ( glob( jet_cw()->plugin_path( 'includes/widgets/wishlist/' ) . '*.php' ) as $file ) {
					$slug = basename( $file, '.php' );

					if ( filter_var( $available_widgets[ $slug ], FILTER_VALIDATE_BOOLEAN ) || ! $available_widgets ) {
						$this->register_widgets( $file, $widgets_manager );
					}
				}
			}

		}

	}

}