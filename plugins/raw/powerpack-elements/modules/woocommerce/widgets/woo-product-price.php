<?php
namespace PowerpackElements\Modules\Woocommerce\Widgets;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Classes\PP_Woo_Helper;

use Elementor\Controls_Manager;
use Elementor\Core\Schemes;
use Elementor\Group_Control_Typography;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Woo - Product Price widget
 */
class Woo_Product_Price extends Powerpack_Widget {
	public function get_categories() {
		return parent::get_woo_categories();
	}

	/**
	 * Retrieve Woo - Product Price widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Woo_Product_Price' );
	}

	/**
	 * Retrieve Woo - Product Price widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Woo_Product_Price' );
	}

	/**
	 * Retrieve Woo - Product Price widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Woo_Product_Price' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the Woo - Product Price widget belongs to.
	 *
	 * @since 1.4.13.4
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'Woo_Product_Price' );
	}

	/**
	 * Retrieve the list of styles the Woo - Product Price depended on.
	 *
	 * Used to set style dependencies required to run the widget.
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_style_depends() {
		return array(
			'pp-woocommerce',
		);
	}

	/**
	 * Register Woo - Product Price widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @access protected
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_price_style',
			[
				'label' => __( 'Price', 'powerpack' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wc_style_warning',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => __( 'The style of this widget is often affected by your theme and plugins. If you experience any such issue, try to switch to a basic theme and deactivate related plugins.', 'powerpack' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		$this->add_responsive_control(
			'text_align',
			[
				'label' => __( 'Alignment', 'powerpack' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __( 'Left', 'powerpack' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'powerpack' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'powerpack' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => 'text-align: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'price_color',
			[
				'label' => __( 'Color', 'powerpack' ),
				'type' => Controls_Manager::COLOR,
				'scheme' => [
					'type' => Schemes\Color::get_type(),
					'value' => Schemes\Color::COLOR_1,
				],
				'selectors' => [
					'.woocommerce {{WRAPPER}} .price' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'scheme' => Schemes\Typography::TYPOGRAPHY_1,
				'selector' => '.woocommerce {{WRAPPER}} .price',
			]
		);

		$this->add_control(
			'sale_heading',
			[
				'label' => __( 'Sale Price', 'powerpack' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'sale_price_color',
			[
				'label' => __( 'Color', 'powerpack' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.woocommerce {{WRAPPER}} .price ins' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'sale_price_typography',
				'selector' => '.woocommerce {{WRAPPER}} .price ins',
			]
		);

		$this->add_control(
			'price_block',
			[
				'label' => __( 'Stacked', 'powerpack' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'prefix_class' => 'pp-product-price-block-',
			]
		);

		$this->add_responsive_control(
			'sale_price_spacing',
			[
				'label' => __( 'Spacing', 'powerpack' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'em' => [
						'min' => 0,
						'max' => 5,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'body:not(.rtl) {{WRAPPER}}:not(.pp-product-price-block-yes) del' => 'margin-right: {{SIZE}}{{UNIT}}',
					'body.rtl {{WRAPPER}}:not(.pp-product-price-block-yes) del' => 'margin-left: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}}.pp-product-price-block-yes del' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		do_action( 'pp_woo_builder_widget_before_render', $this );
		global $product;
		$product = wc_get_product();

		if ( Plugin::instance()->editor->is_edit_mode() ) {
			echo wp_kses_post( PP_Woo_Helper::get_instance()->default( $this->get_name() ) );
		} else {
			if ( empty( $product ) ) {
				return;
			}

			wc_get_template( '/single-product/price.php' );
		}
		do_action( 'pp_woo_builder_widget_after_render', $this );
	}

	public function render_plain_content() {}
}
