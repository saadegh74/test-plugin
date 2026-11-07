<?php
namespace PowerpackElements\Modules\Woocommerce\Widgets;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Classes\PP_Woo_Helper;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Woo - Product Short Description widget
 */
class Woo_Product_Short_Description extends Powerpack_Widget {
	public function get_categories() {
		return parent::get_woo_categories();
	}

	/**
	 * Retrieve Woo - Product Short Description widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Woo_Product_Short_Description' );
	}

	/**
	 * Retrieve Woo - Product Short Description widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Woo_Product_Short_Description' );
	}

	/**
	 * Retrieve Woo - Product Short Description widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Woo_Product_Short_Description' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @since 1.4.13.4
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'Woo_Product_Short_Description' );
	}

	/**
	 * Retrieve the list of styles the Woo - Product Short Description depended on.
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
	 * Register Woo - Product Short Description widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @access protected
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_product_description_style',
			[
				'label' => __( 'Style', 'powerpack' ),
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
					'justify' => [
						'title' => __( 'Justified', 'powerpack' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => 'text-align: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => __( 'Text Color', 'powerpack' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.woocommerce {{WRAPPER}} .woocommerce-product-details__short-description' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'text_typography',
				'label' => __( 'Typography', 'powerpack' ),
				'selector' => '.woocommerce {{WRAPPER}} .woocommerce-product-details__short-description',
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

			wc_get_template( 'single-product/short-description.php' );
		}
		do_action( 'pp_woo_builder_widget_after_render', $this );
	}

	public function render_plain_content() {}
}
