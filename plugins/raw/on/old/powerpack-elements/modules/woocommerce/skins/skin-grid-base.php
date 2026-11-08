<?php
/**
 * PowerPack WooCommerce Skin Grid - Default.
 *
 * @package PowerPack
 */

namespace PowerpackElements\Modules\Woocommerce\Skins;

use Elementor\Controls_Manager;
use Elementor\Skin_Base;
use Elementor\Widget_Base;
use Elementor\Icons_Manager;
use Elementor\Core\Schemes\Color as Scheme_Color;
use Elementor\Core\Schemes\Typography as Scheme_Typography;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Skin_Grid_Base
 *
 * @property Products $parent
 */
abstract class Skin_Grid_Base extends Skin_Base {

	/**
	 * Holds add to cart button text for simple products.
	 *
	 * @since 2.9.7
	 */
	public static $add_to_cart_text;

	/**
	 * Holds add to cart button text for variable products.
	 *
	 * @since 2.9.7
	 */
	public static $select_options_text;

	/**
	 * Holds add to cart button text for grouped products.
	 *
	 * @since 2.9.7
	 */
	public static $view_products_text;

	/**
	 * Holds read more button text for simple and variable products.
	 *
	 * @since 2.9.7
	 */
	public static $read_more_text;

	private static function get_network_icon_data( $network_name ) {
		$prefix = 'fa ';
		$library = '';

		if ( Icons_Manager::is_migration_allowed() ) {
			$prefix = 'fas ';
			$library = 'fa-solid';
		}

		return [
			'value' => $prefix . 'fa-' . $network_name,
			'library' => $library,
		];
	}

	/**
	 * Query object
	 *
	 * @since 2.4.0
	 * @var object $query
	 */
	public static $query;

	/**
	 * Settings
	 *
	 * @since 2.4.0
	 * @var object $settings
	 */
	public static $settings;

	/**
	 * Change pagination arguments based on settings.
	 *
	 * @since 1.3.3
	 * @access protected
	 * @param string $located location.
	 * @param string $template_name template name.
	 * @param array  $args arguments.
	 * @param string $template_path path.
	 * @param string $default_path default path.
	 * @return string template location
	 */
	public function woo_pagination_template( $located, $template_name, $args, $template_path, $default_path ) {

		if ( 'loop/pagination.php' === $template_name ) {
			$located = POWERPACK_ELEMENTS_PATH . 'modules/woocommerce/templates/loop/pagination.php';
		}

		return $located;
	}

	/**
	 * Change pagination arguments based on settings.
	 *
	 * @since 1.3.3
	 * @access protected
	 * @param array $args pagination args.
	 * @return array
	 */
	public function woo_pagination_options( $args ) {

		if ( ! empty( self::$settings ) ) {
			$settings = self::$settings;
		} else {
			$settings = $this->parent->get_settings();
		}

		$pagination_arrow = false;

		if ( 'numbers_arrow' === $settings['pagination_type'] ) {
			$pagination_arrow = true;
		}

		$args['prev_next'] = $pagination_arrow;

		if ( '' !== $settings['pagination_prev_label'] ) {
			$args['prev_text'] = $settings['pagination_prev_label'];
		}

		if ( '' !== $settings['pagination_next_label'] ) {
			$args['next_text'] = $settings['pagination_next_label'];
		}

		return $args;
	}

	/**
	 * Get Wrapper Classes.
	 *
	 * @since 1.3.3
	 * @access public
	 */
	public function set_slider_attr() {

		if ( ! empty( self::$settings ) ) {
			$settings = self::$settings;
		} else {
			$settings = $this->parent->get_settings();
		}

		if ( 'slider' !== $settings['products_layout_type'] ) {
			return;
		}

		$desktop_show = ( isset( $settings['slides_to_show'] ) && '' !== $settings['slides_to_show'] ) ? absint( $settings['slides_to_show'] ) : 4;
		$tablet_show  = ( isset( $settings['slides_to_show_tablet'] ) && '' !== $settings['slides_to_show_tablet'] ) ? absint( $settings['slides_to_show_tablet'] ) : 3;
		$mobile_show  = ( isset( $settings['slides_to_show_mobile'] ) && '' !== $settings['slides_to_show_mobile'] ) ? absint( $settings['slides_to_show_mobile'] ) : 1;

		$desktop_scroll = ( isset( $settings['slides_to_scroll'] ) && $settings['slides_to_scroll'] ) ? absint( $settings['slides_to_scroll'] ) : 1;
		$tablet_scroll  = ( isset( $settings['slides_to_scroll_tablet'] ) && $settings['slides_to_scroll_tablet'] ) ? absint( $settings['slides_to_scroll_tablet'] ) : 1;
		$mobile_scroll  = ( isset( $settings['slides_to_scroll_mobile'] ) && $settings['slides_to_scroll_mobile'] ) ? absint( $settings['slides_to_scroll_mobile'] ) : 1;

		$desktop_space = ( isset( $settings['column_gap']['size'] ) && $settings['column_gap']['size'] ) ? absint( $settings['column_gap']['size'] ) : 10;
		$tablet_space  = ( isset( $settings['column_gap_tablet']['size'] ) && $settings['column_gap_tablet']['size'] ) ? absint( $settings['column_gap_tablet']['size'] ) : 10;
		$mobile_space  = ( isset( $settings['column_gap_mobile']['size'] ) && $settings['column_gap_mobile']['size'] ) ? absint( $settings['column_gap_mobile']['size'] ) : 10;

		$slider_options = [
			'direction'             => 'horizontal',
			'slidesPerView'         => $desktop_show,
			'slidesPerGroup'        => $desktop_scroll,
			'speed'                 => ( $settings['transition_speed'] ) ? absint( $settings['transition_speed'] ) : 600,
			'spaceBetween'          => ( $settings['column_gap']['size'] ) ? $settings['column_gap']['size'] : 10,
			'loop'                  => ( 'yes' === $settings['infinite'] ),
			'disableOnInteraction'  => ( 'yes' === $settings['pause_on_hover'] ),
			'autoHeight'            => true,
			'watchSlidesVisibility' => true,
			'observer'              => true,
			'observeParents'        => true,
		];

		if ( 'yes' === $settings['autoplay'] && ! empty( $settings['autoplay_speed'] ) ) {
			$autoplay_speed = $settings['autoplay_speed'];
		} else {
			$autoplay_speed = 999999;
		}

		$slider_options['autoplay'] = array(
			'delay' => $autoplay_speed,
		);

		if ( 'yes' === $settings['arrows'] ) {
			$slider_options['navigation'] = array(
				'nextEl' => '.swiper-button-next-' . esc_attr( $this->parent->get_id() ),
				'prevEl' => '.swiper-button-prev-' . esc_attr( $this->parent->get_id() ),
			);
		}

		if ( 'yes' === $settings['carousel_pagination'] ) {
			$slider_options['pagination'] = array(
				'el'        => '.swiper-pagination-' . esc_attr( $this->parent->get_id() ),
				'clickable' => true,
			);
		}

		$elementor_bp_lg = get_option( 'elementor_viewport_lg' );
		$elementor_bp_md = get_option( 'elementor_viewport_md' );
		$bp_desktop      = ! empty( $elementor_bp_lg ) ? $elementor_bp_lg : 1025;
		$bp_tablet       = ! empty( $elementor_bp_md ) ? $elementor_bp_md : 768;
		$bp_mobile       = 320;

		$slider_options['breakpoints'] = array(
			$bp_desktop => array(
				'slidesPerView'  => $desktop_show,
				'slidesPerGroup' => $desktop_scroll,
				'spaceBetween'   => $desktop_space,
			),
			$bp_tablet  => array(
				'slidesPerView'  => $tablet_show,
				'slidesPerGroup' => $tablet_scroll,
				'spaceBetween'   => $tablet_space,
			),
			$bp_mobile  => array(
				'slidesPerView'  => $mobile_show,
				'slidesPerGroup' => $mobile_scroll,
				'spaceBetween'   => $mobile_space,
			),
		);

		$this->parent->add_render_attribute(
			'wrapper', [
				'data-woo_slider' => wp_json_encode( $slider_options ),
			]
		);
	}

	/**
	 * Render Query.
	 *
	 * @since 1.1.0
	 */
	public function render_query() {

		if ( ! empty( self::$settings ) ) {
			$settings = self::$settings;
		} else {
			$settings = $this->parent->get_settings();
		}

		$this->parent->query_posts( $settings );

		self::$query = $this->parent->get_query();
	}

	/**
	 * Render loop required arguments.
	 *
	 * @since 1.1.0
	 */
	public function render_loop_args() {

		$query = $this->parent->get_query();

		global $woocommerce_loop;

		if ( ! empty( self::$settings ) ) {
			$settings = self::$settings;
		} else {
			$settings = $this->parent->get_settings();
		}

		if ( 'grid' === $settings['products_layout_type'] ) {
			$woocommerce_loop['columns'] = (int) $settings['products_columns'];

			if ( 'main' !== $settings['source'] ) {
				if ( 0 < $settings['products_per_page'] && '' !== $settings['pagination_type'] ) {
					/* Pagination */
					$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

					if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'pp-product-nonce' ) ) {
						if ( isset( $_POST['page_number'] ) && '' !== $_POST['page_number'] ) {
							$paged = $_POST['page_number'];
						}
					}
					$woocommerce_loop['paged']        = $paged;
					$woocommerce_loop['total']        = $query->found_posts;
					$woocommerce_loop['post_count']   = $query->post_count;
					$woocommerce_loop['per_page']     = $settings['products_per_page'];
					$woocommerce_loop['total_pages']  = ceil( $query->found_posts / $settings['products_per_page'] );
					$woocommerce_loop['current_page'] = $paged;
				}
			}

			$this->parent->add_render_attribute(
				'inner', [
					'class' => [
						' columns-' . $woocommerce_loop['columns'],
					],
				]
			);
		} else {
			if ( 'yes' === ( $settings['arrows'] || $settings['carousel_pagination'] ) ) {

				$this->parent->add_render_attribute(
					'inner', [
						'class' => [
							'pp-slick-dotted',
						],
					]
				);
			}
		}
	}

	/**
	 * Pagination Structure.
	 *
	 * @since 1.1.0
	 */
	public function render_pagination_structure() {

		if ( ! empty( self::$settings ) ) {
			$settings = self::$settings;
		} else {
			$settings = $this->parent->get_settings();
		}

		if ( 'grid' === $settings['products_layout_type'] ) {
			if ( '' !== $settings['pagination_type'] ) {
				add_filter( 'wc_get_template', [ $this, 'woo_pagination_template' ], 10, 5 );
				add_filter( 'pp_woocommerce_pagination_args', [ $this, 'woo_pagination_options' ] );
				woocommerce_pagination();
				remove_filter( 'pp_woocommerce_pagination_args', [ $this, 'woo_pagination_options' ] );
				remove_filter( 'wc_get_template', [ $this, 'woo_pagination_template' ], 10, 5 );
			}
		}
	}

	/**
	 * Render wrapper start.
	 *
	 * @since 1.1.0
	 */
	public function render_wrapper_start() {

		if ( ! empty( self::$settings ) ) {
			$settings = self::$settings;
		} else {
			$settings = $this->parent->get_settings();
		}

		$skin = $this->get_id();
		$page_id = 0;

		if ( null !== \Elementor\Plugin::$instance->documents->get_current() ) {
			$page_id = \Elementor\Plugin::$instance->documents->get_current()->get_main_id();
		}

		$this->parent->add_render_attribute(
			'wrapper', [
				'class' => [
					'pp-woocommerce',
					'pp-woo-products-' . $settings['products_layout_type'],
					'pp-woo-skin-' . $this->get_id(),
					'pp-woo-query-' . $settings['source'],
				],
				'data-page' => $page_id,
				'data-skin' => $skin,
			]
		);

		if ( 'slider' === $settings['products_layout_type'] ) {
			$this->parent->add_render_attribute( 'wrapper', 'class', [ 'swiper-container-wrap', 'swiper' ] );

			if ( $settings['dots_position'] ) {
				$this->parent->add_render_attribute( 'wrapper', 'class', 'swiper-container-wrap-dots-' . $settings['dots_position'] );
			}
		}

		$this->set_slider_attr();

		echo '<div ' . wp_kses_post( $this->parent->get_render_attribute_string( 'wrapper' ) ) . '>';
	}

	/**
	 * Render wrapper end.
	 *
	 * @since 1.1.0
	 */
	public function render_wrapper_end() {
		echo '</div>';
	}

	/**
	 * Render inner container start.
	 *
	 * @since 1.1.0
	 */
	public function render_inner_start() {
		if ( ! empty( self::$settings ) ) {
			$settings = self::$settings;
		} else {
			$settings = $this->parent->get_settings();
		}

		$products_columns        = isset( $settings['products_columns'] ) ? $settings['products_columns'] : '4';
		$products_columns_tablet = isset( $settings['products_columns_tablet'] ) ? $settings['products_columns_tablet'] : '3';
		$products_columns_mobile = isset( $settings['products_columns_mobile'] ) ? $settings['products_columns_mobile'] : '1';

		$this->parent->add_render_attribute(
			'inner', [
				'class' => [
					'pp-woo-products-inner',
					'pp-woo-product__column-' . $products_columns,
					'pp-woo-product__column-tablet-' . $products_columns_tablet,
					'pp-woo-product__column-mobile-' . $products_columns_mobile,
				],
			]
		);

		if ( '' !== $settings['products_hover_style'] ) {
			$this->parent->add_render_attribute(
				'inner', [
					'class' => [
						'pp-woo-product__hover-' . $settings['products_hover_style'],
					],
				]
			);
		}

		if ( 'slider' === $settings['products_layout_type'] ) {
			$this->parent->add_render_attribute(
				'inner', [
					'class' => [
						'pp-swiper-slider',
						'swiper-container',
					],
				]
			);

			if ( is_rtl() ) {
				$this->parent->add_render_attribute( 'inner', 'dir', 'rtl' );
			}
		}

		echo '<div ' . wp_kses_post( $this->parent->get_render_attribute_string( 'inner' ) ) . '>';
	}

	/**
	 * Render inner container end.
	 *
	 * @since 1.1.0
	 */
	public function render_inner_end() {
		echo '</div>';
	}

	/**
	 * Render woo loop start.
	 *
	 * @since 1.1.0
	 */
	public function render_woo_loop_start() {
		if ( ! empty( self::$settings ) ) {
			$settings = self::$settings;
		} else {
			$settings = $this->parent->get_settings();
		}

		include POWERPACK_ELEMENTS_PATH . 'modules/woocommerce/templates/loop/loop-start.php';
	}

	public function add_to_cart_button_text( $text, $product ) {
		if ( 'simple' === $product->get_type() ) {
			$text = $product->is_purchasable() && $product->is_in_stock() ? self::$add_to_cart_text : self::$read_more_text;
		}

		if ( 'variable' === $product->get_type() ) {
			$text = $product->is_purchasable() ? self::$select_options_text : self::$read_more_text;
		}

		if ( 'grouped' === $product->get_type() ) {
			$text = self::$view_products_text;
		}

		return $text;
	}

	/**
	 * Render woo loop.
	 *
	 * @since 1.1.0
	 */
	public function render_woo_loop() {
		if ( ! empty( self::$settings ) ) {
			$settings = self::$settings;
		} else {
			$settings = $this->parent->get_settings();
		}

		$query = $this->parent->get_query();

		if ( 'yes' === $settings['show_add_cart'] && 'yes' === $settings['change_add_to_cart_text'] ) {
			self::$add_to_cart_text = $settings['add_to_cart_simple_text'] ? $settings['add_to_cart_simple_text'] : __( 'Add to cart', 'powerpack' );
			self::$select_options_text = $settings['add_to_cart_variable_text'] ? $settings['add_to_cart_variable_text'] : __( 'Select options', 'powerpack' );
			self::$view_products_text = $settings['add_to_cart_group_text'] ? $settings['add_to_cart_group_text'] : __( 'View products', 'powerpack' );
			self::$read_more_text = $settings['add_to_cart_read_more_text'] ? $settings['add_to_cart_read_more_text'] : __( 'Read more', 'powerpack' );

			add_filter( 'woocommerce_product_add_to_cart_text', [ $this, 'add_to_cart_button_text' ], 10, 2 );
		}

		while ( $query->have_posts() ) :
			$query->the_post();
			$this->render_woo_loop_template();
		endwhile;

		if ( 'yes' === $settings['show_add_cart'] && 'yes' === $settings['change_add_to_cart_text'] ) {
			remove_filter( 'woocommerce_product_add_to_cart_text', [ $this, 'add_to_cart_button_text' ], 10, 2 );
		}
	}

	/**
	 * Render woo default template.
	 *
	 * @since 1.1.0
	 */
	public function render_woo_loop_template() {

		if ( ! empty( self::$settings ) ) {
			$settings = self::$settings;
		} else {
			$settings = $this->parent->get_settings();
		}

		include POWERPACK_ELEMENTS_PATH . 'modules/woocommerce/templates/content-product-skin-1.php';
	}
	/**
	 * Render woo loop end.
	 *
	 * @since 1.1.0
	 */
	public function render_woo_loop_end() {
		include POWERPACK_ELEMENTS_PATH . 'modules/woocommerce/templates/loop/loop-end.php';
	}

	/**
	 * Render reset loop.
	 *
	 * @since 1.1.0
	 */
	public function render_reset_loop() {

		woocommerce_reset_loop();

		wp_reset_postdata();
	}

	/**
	 * Quick View.
	 *
	 * @since 1.3.3
	 * @access public
	 */
	public function quick_view_modal() {

		if ( ! empty( self::$settings ) ) {
			$settings = self::$settings;
		} else {
			$settings = $this->parent->get_settings();
		}

		$quick_view_type = $settings['quick_view_type'];

		if ( '' !== $quick_view_type ) {
			wp_enqueue_script( 'wc-add-to-cart-variation' );
			wp_enqueue_script( 'flexslider' );

			$widget_id = $this->parent->get_id();

			include POWERPACK_ELEMENTS_PATH . 'modules/woocommerce/templates/quick-view-modal.php';
		}
	}

	/**
	 * Get Best Selling Product for Badge.
	 *
	 * @since 1.3.3
	 * @access public
	 */
	public function is_best_selling_product( $product_id ) {

		if ( ! empty( self::$settings ) ) {
			$settings = self::$settings;
		} else {
			$settings = $this->parent->get_settings();
		}
		$number_of_sales = $settings['number_of_sales'];

		if ( empty( $number_of_sales ) ) {
			return false;
		}

		$total_sales = get_post_meta( $product_id, 'total_sales', true );

		if ( ! $total_sales || empty( $total_sales ) ) {
			return false;
		}

		return $total_sales >= $number_of_sales;
	}

	/**
	 * Get Top Rated Product for Badge.
	 *
	 * @since 1.3.3
	 * @access public
	 */
	public function is_top_rated_product( $product_id ) {

		if ( ! empty( self::$settings ) ) {
			$settings = self::$settings;
		} else {
			$settings = $this->parent->get_settings();
		}
		$rating = $settings['number_of_ratings'];

		if ( empty( $rating ) ) {
			return false;
		}

		$total_rating = get_post_meta( $product_id, '_wc_average_rating', true );

		if ( ! $total_rating || empty( $total_rating ) ) {
			return false;
		}

		return $total_rating >= $rating;
	}

	/**
	 * Get Best Selling Product for Badge 1.
	 *
	 * @since 1.3.3
	 * @access public
	 */
	public function is_best_selling_product_1( $product_id ) {

		if ( ! empty( self::$settings ) ) {
			$settings = self::$settings;
		} else {
			$settings = $this->parent->get_settings();
		}
		$number_of_sales = $settings['number_of_sales_1'];

		if ( empty( $number_of_sales ) ) {
			return false;
		}

		$total_sales = get_post_meta( $product_id, 'total_sales', true );

		if ( ! $total_sales || empty( $total_sales ) ) {
			return false;
		}

		return $total_sales >= $number_of_sales;
	}

	/**
	 * Get Top Rated Product for Badge 1.
	 *
	 * @since 1.3.3
	 * @access public
	 */
	public function is_top_rated_product_1( $product_id ) {

		if ( ! empty( self::$settings ) ) {
			$settings = self::$settings;
		} else {
			$settings = $this->parent->get_settings();
		}
		$rating = $settings['number_of_rating_1'];

		if ( empty( $rating ) ) {
			return false;
		}

		$total_rating = get_post_meta( $product_id, '_wc_average_rating', true );

		if ( ! $total_rating || empty( $total_rating ) ) {
			return false;
		}

		return $total_rating >= $rating;
	}

	/**
	 * Get Best Selling Product for Badge 1.
	 *
	 * @since 1.3.3
	 * @access public
	 */
	public function is_best_selling_product_2( $product_id ) {

		if ( ! empty( self::$settings ) ) {
			$settings = self::$settings;
		} else {
			$settings = $this->parent->get_settings();
		}
		$number_of_sales = $settings['number_of_sales_2'];

		if ( empty( $number_of_sales ) ) {
			return false;
		}

		$total_sales = get_post_meta( $product_id, 'total_sales', true );

		if ( ! $total_sales || empty( $total_sales ) ) {
			return false;
		}

		return $total_sales >= $number_of_sales;
	}

	/**
	 * Get Top Rated Product for Badge 1.
	 *
	 * @since 1.3.3
	 * @access public
	 */
	public function is_top_rated_product_2( $product_id ) {

		if ( ! empty( self::$settings ) ) {
			$settings = self::$settings;
		} else {
			$settings = $this->parent->get_settings();
		}
		$rating = $settings['number_of_rating_2'];

		if ( empty( $rating ) ) {
			return false;
		}

		$total_rating = get_post_meta( $product_id, '_wc_average_rating', true );

		if ( ! $total_rating || empty( $total_rating ) ) {
			return false;
		}

		return $total_rating >= $rating;
	}

	/**
	 * Render product body output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 2.4.0
	 * @access protected
	 */
	public function render_ajax_product_body( $widget ) {
		ob_start();

		self::$settings = $widget->get_settings();

		$this->render_query();

		$this->render_loop_args();
		$this->render_woo_loop_start();
			$this->render_woo_loop();
		$this->render_woo_loop_end();

		return ob_get_clean();
	}

	/**
	 * Render product pagination HTML via AJAX call.
	 *
	 * @param array|string $widget    Widget object.
	 *
	 * @since 2.4.0
	 * @access public
	 */
	public function render_ajax_pagination( $widget ) {
		ob_start();

		self::$settings = $widget->get_settings();

		$this->render_query();

		$this->render_pagination_structure();

		return ob_get_clean();
	}

	protected static function render_product_icon( $network_name ) {
		$network_icon_data = self::get_network_icon_data( $network_name );

		if ( \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_font_icon_svg' ) ) {
			$icon = Icons_Manager::render_font_icon( $network_icon_data );
		} else {
			$icon = sprintf( '<i class="%s" aria-hidden="true"></i>', $network_icon_data['value'] );
		}

		\Elementor\Utils::print_unescaped_internal_string( $icon );
	}

	/**
	 * Render team member carousel dots output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_dots() {
		if ( ! empty( self::$settings ) ) {
			$settings = self::$settings;
		} else {
			$settings = $this->parent->get_settings();
		}

		if ( 'slider' !== $settings['products_layout_type'] ) {
			return;
		}

		if ( 'yes' === $settings['carousel_pagination'] ) {
			?>
			<!-- Add Pagination -->
			<div class="swiper-pagination swiper-pagination-<?php echo esc_attr( $this->parent->get_id() ); ?>"></div>
			<?php
		}
	}

	/**
	 * Render team member carousel arrows output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_arrows() {
		if ( ! empty( self::$settings ) ) {
			$settings = self::$settings;
		} else {
			$settings = $this->parent->get_settings();
		}

		if ( 'slider' !== $settings['products_layout_type'] ) {
			return;
		}

		$migration_allowed = Icons_Manager::is_migration_allowed();

		if ( ! isset( $settings['arrow'] ) && ! Icons_Manager::is_migration_allowed() ) {
			// add old default.
			$settings['arrow'] = 'fa fa-angle-right';
		}

		$has_icon = ! empty( $settings['arrow'] );

		if ( ! $has_icon && ! empty( $settings['select_arrow']['value'] ) ) {
			$has_icon = true;
		}

		if ( ! empty( $settings['arrow'] ) ) {
			$this->parent->add_render_attribute( 'arrow-icon', 'class', $settings['arrow'] );
			$this->parent->add_render_attribute( 'arrow-icon', 'aria-hidden', 'true' );
		}

		$migrated = isset( $settings['__fa4_migrated']['select_arrow'] );
		$is_new = ! isset( $settings['arrow'] ) && $migration_allowed;

		if ( 'yes' === $settings['arrows'] ) {
			if ( $has_icon ) {
				if ( $is_new || $migrated ) {
					$next_arrow = $settings['select_arrow'];
					$prev_arrow = str_replace( 'right', 'left', $settings['select_arrow'] );
				} else {
					$next_arrow = $settings['arrow'];
					$prev_arrow = str_replace( 'right', 'left', $settings['arrow'] );
				}
			} else {
				$next_arrow = 'fa fa-angle-right';
				$prev_arrow = 'fa fa-angle-left';
			}

			if ( ! empty( $settings['arrow'] ) || ( ! empty( $settings['select_arrow']['value'] ) && $is_new ) ) { ?>
				<div class="pp-slider-arrow elementor-swiper-button-prev swiper-button-prev-<?php echo esc_attr( $this->parent->get_id() ); ?>">
					<?php if ( $is_new || $migrated ) :
						Icons_Manager::render_icon( $prev_arrow, [ 'aria-hidden' => 'true' ] );
					else : ?>
						<i <?php $this->parent->print_render_attribute_string( 'arrow-icon' ); ?>></i>
					<?php endif; ?>
				</div>
				<div class="pp-slider-arrow elementor-swiper-button-next swiper-button-next-<?php echo esc_attr( $this->parent->get_id() ); ?>">
					<?php if ( $is_new || $migrated ) :
						Icons_Manager::render_icon( $next_arrow, [ 'aria-hidden' => 'true' ] );
					else : ?>
						<i <?php $this->parent->print_render_attribute_string( 'arrow-icon' ); ?>></i>
					<?php endif; ?>
				</div>
			<?php }
		}
	}

	/**
	 * Get no products found message.
	 *
	 * Returns the no products found message HTML.
	 *
	 * @since 2.7.7
	 * @access public
	 */
	public function render_empty() {
		if ( ! empty( self::$settings ) ) {
			$settings = self::$settings;
		} else {
			$settings = $this->parent->get_settings();
		}
		?>
		<div class="pp-wooproducts-empty">
			<p><?php echo wp_kses_post( $settings['no_products_message'] ); ?></p>
		</div>
		<?php
	}

	/**
	 * Render Content.
	 *
	 * @since 1.3.3
	 * @access protected
	 */
	public function render() {
		if ( ! empty( self::$settings ) ) {
			$settings = self::$settings;
		} else {
			$settings = $this->parent->get_settings();
		}
		$this->render_query();

		$query = $this->parent->get_query();

		if ( ! $query->have_posts() ) {
			$this->render_empty();
			return;
		}

		$this->render_loop_args();
		$this->render_wrapper_start();
			$this->render_inner_start();
				$this->render_woo_loop_start();
					$this->render_woo_loop();
				$this->render_woo_loop_end();
				$this->render_pagination_structure();
				$this->render_reset_loop();
			$this->render_inner_end();
			if ( 'slider' === $settings['products_layout_type'] ) {
				$this->render_dots();
				$this->render_arrows();
			}
		$this->render_wrapper_end();

		$this->quick_view_modal();

	}
}
