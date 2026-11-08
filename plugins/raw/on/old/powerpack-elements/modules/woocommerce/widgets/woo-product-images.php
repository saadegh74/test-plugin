<?php
namespace PowerpackElements\Modules\Woocommerce\Widgets;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Classes\PP_Woo_Helper;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Woo - Product Images widget
 */
class Woo_Product_Images extends Powerpack_Widget {
	public function get_categories() {
		return parent::get_woo_categories();
	}

	/**
	 * Retrieve Woo - Product Images widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Woo_Product_Images' );
	}

	/**
	 * Retrieve Woo - Product Images widget title.
	 *
	 * @access public

	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Woo_Product_Images' );
	}

	/**
	 * Retrieve Woo - Product Images widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Woo_Product_Images' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the Woo - Product Images widget belongs to.
	 *
	 * @since 1.4.13.4
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'Woo_Product_Images' );
	}

	/**
	 * Retrieve the list of styles the Woo - Product Images depended on.
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
	 * Register Woo - Product Images widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @access protected
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_product_gallery_style',
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

		$this->add_control(
			'sale_flash',
			[
				'label' => __( 'Sale Flash', 'powerpack' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'powerpack' ),
				'label_off' => __( 'Hide', 'powerpack' ),
				'render_type' => 'template',
				'return_value' => 'yes',
				'default' => 'yes',
				'prefix_class' => '',
			]
		);

		$this->add_responsive_control(
			'sale_margin',
			array(
				'label'      => __( 'Margin', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'default'    => array(
					'top'    => '10',
					'bottom' => '10',
					'left'   => '10',
					'right'  => '10',
					'unit'   => 'px',
				),
				'selectors'  => array(
					'.woocommerce {{WRAPPER}} span.onsale' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition' => array(
					'sale_flash' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'sale_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'default'    => array(
					'top'    => '0',
					'bottom' => '0',
					'left'   => '0',
					'right'  => '0',
					'unit'   => 'px',
				),
				'selectors'  => array(
					'.woocommerce {{WRAPPER}} span.onsale' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition' => array(
					'sale_flash' => 'yes',
				),
			)
		);

		$this->add_control(
			'sale_badge_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'.woocommerce {{WRAPPER}} span.onsale' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'sale_flash' => 'yes',
				),
			)
		);

		$this->add_control(
			'sale_badge_bg_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'.woocommerce {{WRAPPER}} span.onsale' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'sale_flash' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'sale_badge_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'selector'  => '.woocommerce {{WRAPPER}} span.onsale',
				'condition' => array(
					'sale_flash' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'sale_badge_size',
			array(
				'label'      => __( 'Size', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 20,
						'max' => 200,
					),
					'em' => array(
						'min' => 1,
						'max' => 10,
					),
				),
				'default'    => array(
					'size' => 2,
					'unit' => 'em',
				),
				'condition' => array(
					'sale_flash' => 'yes',
				),
				'selectors'  => array(
					'.woocommerce {{WRAPPER}} span.onsalee' => 'min-height: {{SIZE}}{{UNIT}}; min-width: {{SIZE}}{{UNIT}};line-height: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'heading_images_style',
			[
				'label' => __( 'Images', 'powerpack' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'image_border',
				'selector' => '.woocommerce {{WRAPPER}} .woocommerce-product-gallery__trigger + .woocommerce-product-gallery__wrapper,
				.woocommerce {{WRAPPER}} .flex-viewport, .woocommerce {{WRAPPER}} .flex-control-thumbs img',
			]
		);

		$this->add_responsive_control(
			'image_border_radius',
			[
				'label' => __( 'Border Radius', 'powerpack' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'.woocommerce {{WRAPPER}} .woocommerce-product-gallery__trigger + .woocommerce-product-gallery__wrapper,
					.woocommerce {{WRAPPER}} .flex-viewport' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'spacing',
			[
				'label' => __( 'Spacing', 'powerpack' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'selectors' => [
					'.woocommerce {{WRAPPER}} .flex-viewport:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'heading_thumbs_style',
			[
				'label' => __( 'Thumbnails', 'powerpack' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'thumbs_border',
				'selector' => '.woocommerce {{WRAPPER}} .flex-control-thumbs img',
			]
		);

		$this->add_responsive_control(
			'thumbs_border_radius',
			[
				'label' => __( 'Border Radius', 'powerpack' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'.woocommerce {{WRAPPER}} .flex-control-thumbs img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'spacing_thumbs',
			[
				'label' => __( 'Spacing', 'powerpack' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'selectors' => [
					'.woocommerce {{WRAPPER}} .flex-control-thumbs li' => 'padding-right: calc({{SIZE}}{{UNIT}} / 2); padding-left: calc({{SIZE}}{{UNIT}} / 2); padding-bottom: {{SIZE}}{{UNIT}}',
					'.woocommerce {{WRAPPER}} .flex-control-thumbs' => 'margin-right: calc(-{{SIZE}}{{UNIT}} / 2); margin-left: calc(-{{SIZE}}{{UNIT}} / 2)',
				],
			]
		);

		$this->end_controls_section();
	}

	public function render() {
		$settings = $this->get_settings_for_display();

		do_action( 'pp_woo_builder_widget_before_render', $this );
		global $product;

		$product = wc_get_product();

		if ( Plugin::instance()->editor->is_edit_mode() ) {
			echo wp_kses_post( PP_Woo_Helper::get_instance()->default( $this->get_name() ) );
		} else {
			if ( empty( $product ) ) {
				return;
			}

			if ( 'yes' === $settings['sale_flash'] ) {
				wc_get_template( 'loop/sale-flash.php' );
			}
			wc_get_template( 'single-product/product-image.php' );

			// On render widget from Editor - trigger the init manually.
			if ( wp_doing_ajax() ) {
				?>
				<script>
					jQuery( '.woocommerce-product-gallery' ).each( function() {
						jQuery( this ).wc_product_gallery();
					} );
				</script>
				<?php
			}
		}
		do_action( 'pp_woo_builder_widget_after_render', $this );
	}
}
