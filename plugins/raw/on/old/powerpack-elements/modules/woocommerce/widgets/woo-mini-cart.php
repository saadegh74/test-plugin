<?php
/**
 * PowerPack WooCommerce Cart widget.
 *
 * @package PowerPack
 */

namespace PowerpackElements\Modules\Woocommerce\Widgets;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Classes\PP_Config;

use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Modules\DynamicTags\Module as TagsModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit;   // Exit if accessed directly.
}

/**
 * Woo - Mini Cart widget
 */
class Woo_Mini_Cart extends Powerpack_Widget {

	/**
	 * Retrieve woo mini cart widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Woo_Mini_Cart' );
	}

	/**
	 * Retrieve woo mini cart widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Woo_Mini_Cart' );
	}

	/**
	 * Retrieve woo mini cart widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Woo_Mini_Cart' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @since 1.3.7
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'Woo_Mini_Cart' );
	}

	/**
	 * Retrieve the list of scripts the woo mini cart widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return [
			'pp-mini-cart',
			'pp-woocommerce',
		];
	}

	/**
	 * Retrieve the list of styles the Woo - Mini Cart depended on.
	 *
	 * Used to set style dependencies required to run the widget.
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_style_depends() {
		return [
			'pp-woocommerce',
		];
	}

	/**
	 * Register woo mini cart widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 2.0.3
	 * @access protected
	 */
	protected function register_controls() {
		/* Button Settings */
		$this->register_content_button_controls();

		/* General Settings */
		$this->register_content_cart_controls();

		/* Help Docs */
		$this->register_content_help_docs();

		/* Style Tab: Cart Button */
		$this->register_style_cart_button_controls();

		/* Style Tab: Items Container */
		$this->register_style_items_container_controls();

		/* Style Tab: Item */
		$this->register_style_items_controls();

		/* Style Tab: Checkout Button */
		$this->register_style_buttons_controls();
	}

	/**
	 * Register woo mini cart widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @access protected
	 */
	protected function register_content_cart_controls() {

		$this->start_controls_section(
			'section_settings',
			array(
				'label' => __( 'Cart', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_cart_on',
			array(
				'label'   => __( 'Show Cart on', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'on-click',
				'options' => array(
					'on-click' => __( 'Click', 'powerpack' ),
					'on-hover' => __( 'Hover', 'powerpack' ),
					'none'     => __( 'None', 'powerpack' ),
				),
			)
		);

		$this->add_control(
			'show_preview',
			array(
				'label'        => __( 'Preview Cart', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'default'      => '',
				'condition'    => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_title',
			array(
				'label'       => __( 'Cart Title', 'powerpack' ),
				'description' => __( 'Cart title is displayed on top of mini cart.', 'powerpack' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'PowerPack Mini Cart', 'powerpack' ),
				'separator'   => 'before',
				'condition'   => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_message',
			array(
				'label'       => __( 'Cart Message', 'powerpack' ),
				'description' => __( 'Cart message is displayed on bottom of mini cart.', 'powerpack' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => __( '100% Secure Checkout!', 'powerpack' ),
				'condition'   => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'link',
			array(
				'label'       => __( 'Link', 'powerpack' ),
				'type'        => Controls_Manager::URL,
				'dynamic'     => array(
					'active'     => true,
					'categories' => array(
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY,
					),
				),
				'placeholder' => 'https://www.your-link.com',
				'default'     => array(
					'url' => '#',
				),
				'condition'   => array(
					'show_cart_on' => 'none',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register woo mini cart widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @access protected
	 */
	protected function register_content_button_controls() {

		$this->start_controls_section(
			'section_button_settings',
			array(
				'label' => __( 'Cart Button', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'icon_style',
			array(
				'label'   => __( 'Style', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'icon',
				'options' => array(
					'icon'      => __( 'Icon only', 'powerpack' ),
					'icon_text' => __( 'Icon + Text', 'powerpack' ),
					'text'      => __( 'Text only', 'powerpack' ),
				),
			)
		);

		$this->add_control(
			'cart_text',
			array(
				'label'     => __( 'Text', 'powerpack' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Cart', 'powerpack' ),
				'condition' => array(
					'icon_style' => array( 'icon_text', 'text' ),
				),
			)
		);

		$this->add_control(
			'icon_type',
			array(
				'label'       => esc_html__( 'Icon Type', 'powerpack' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'toggle'      => false,
				'options'     => array(
					'icon'  => array(
						'title' => esc_html__( 'Icon', 'powerpack' ),
						'icon'  => 'eicon-star',
					),
					'image' => array(
						'title' => esc_html__( 'Image', 'powerpack' ),
						'icon'  => 'eicon-image-bold',
					),
				),
				'default'     => 'icon',
				'condition'   => array(
					'icon_style' => array( 'icon_text', 'icon' ),
				),
			)
		);

		$this->add_control(
			'icon',
			array(
				'label'     => __( 'Icon', 'powerpack' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => array(
					'value'   => 'fas fa-shopping-bag',
					'library' => 'fa-solid',
				),
				'condition' => array(
					'icon_style' => array( 'icon_text', 'icon' ),
					'icon_type'  => 'icon',
				),
			)
		);

		$this->add_control(
			'icon_image',
			array(
				'label'     => __( 'Image Icon', 'powerpack' ),
				'type'      => Controls_Manager::MEDIA,
				'dynamic'   => array(
					'active' => true,
				),
				'default'   => array(
					'url' => Utils::get_placeholder_image_src(),
				),
				'condition' => array(
					'icon_style' => array( 'icon_text', 'icon' ),
					'icon_type'  => 'image',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'      => 'icon_image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `image_size` and `image_custom_dimension`.
				'default'   => 'full',
				'separator' => 'none',
				'condition' => array(
					'icon_style' => array( 'icon_text', 'icon' ),
					'icon_type'  => 'image',
				),
			)
		);

		$this->add_control(
			'counter_position',
			array(
				'label'   => __( 'Counter', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'top',
				'options' => array(
					'none'  => __( 'None', 'powerpack' ),
					'top'   => __( 'Bubble', 'powerpack' ),
					'after' => __( 'After Button', 'powerpack' ),
				),
			)
		);

		$this->add_control(
			'show_subtotal',
			array(
				'label'        => __( 'Subtotal', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'powerpack' ),
				'label_off'    => __( 'Hide', 'powerpack' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'button_position',
			array(
				'label'   => __( 'Position', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'inline',
				'options' => array(
					'inline'   => __( 'Inline', 'powerpack' ),
					'floating' => __( 'Floating', 'powerpack' ),
				),
			)
		);

		$this->add_control(
			'floating_button_placement',
			array(
				'label'        => __( 'Placement', 'powerpack' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'top-left',
				'options'      => array(
					'top-left'      => __( 'Top Left', 'powerpack' ),
					'top-center'    => __( 'Top Center', 'powerpack' ),
					'top-right'     => __( 'Top Right', 'powerpack' ),
					'middle-right'  => __( 'Middle Right', 'powerpack' ),
					'bottom-right'  => __( 'Bottom Right', 'powerpack' ),
					'bottom-center' => __( 'Bottom Center', 'powerpack' ),
					'bottom-left'   => __( 'Bottom Left', 'powerpack' ),
					'middle-left'   => __( 'Middle Left', 'powerpack' ),
				),
				'prefix_class' => 'pp-floating-element-align-',
				'condition'    => array(
					'button_position' => 'floating',
				),
			)
		);

		$this->add_responsive_control(
			'button_align',
			array(
				'label'        => __( 'Alignment', 'powerpack' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => array(
					'left'   => array(
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'      => '',
				'prefix_class' => 'pp-woo-menu-cart%s-align-',
				'selectors'    => array(
					'{{WRAPPER}} .pp-woo-cart-button' => 'text-align: {{VALUE}};',
				),
				'condition'    => array(
					'button_position!' => 'floating',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function register_content_help_docs() {

		$help_docs = PP_Config::get_widget_help_links( 'Woo_Mini_Cart' );

		if ( ! empty( $help_docs ) ) {

			/**
			 * Content Tab: Help Docs
			 *
			 * @since 1.4.8
			 * @access protected
			 */
			$this->start_controls_section(
				'section_help_docs',
				array(
					'label' => __( 'Help Docs', 'powerpack' ),
				)
			);

			$hd_counter = 1;
			foreach ( $help_docs as $hd_title => $hd_link ) {
				$this->add_control(
					'help_doc_' . $hd_counter,
					array(
						'type'            => Controls_Manager::RAW_HTML,
						'raw'             => sprintf( '%1$s ' . $hd_title . ' %2$s', '<a href="' . $hd_link . '" target="_blank" rel="noopener">', '</a>' ),
						'content_classes' => 'pp-editor-doc-links',
					)
				);

				$hd_counter++;
			}

			$this->end_controls_section();
		}
	}

	/**
	 * Style Tab
	 */
	/**
	 * Register Layout Controls.
	 *
	 * @access protected
	 */

	/**
	 * Style Tab: Items Container
	 * -------------------------------------------------
	 */
	protected function register_style_items_container_controls() {
		$this->start_controls_section(
			'section_items_container_style',
			array(
				'label'     => __( 'Items Container', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'items_container_background',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .pp-woo-mini-cart',
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'items_container_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-mini-cart',
				'condition'   => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'items_container_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-mini-cart' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'items_container_width',
			array(
				'label'      => __( 'Width', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'default'    => array(
					'size' => '',
				),
				'range'      => array(
					'px' => array(
						'min' => 150,
						'max' => 500,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-mini-cart-wrap' => 'width: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'items_container_margin_top',
			array(
				'label'     => __( 'Margin Top', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => '',
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart' => 'margin-top: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'items_container_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-mini-cart' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'default'    => array(
					'top'      => '10',
					'right'    => '10',
					'bottom'   => '10',
					'left'     => '10',
					'unit'     => 'px',
					'isLinked' => true,
				),
				'condition'  => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'                  => 'items_container_box_shadow',
				'separator'             => 'before',
				'selector'              => '{{WRAPPER}} .pp-woo-mini-cart',
				'condition'             => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_title_heading',
			array(
				'label'     => __( 'Cart Title', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'show_cart_on!' => 'none',
					'cart_title!'   => '',
				),
			)
		);

		$this->add_control(
			'cart_title_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .pp-woo-mini-cart-title' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
					'cart_title!'   => '',
				),
			)
		);

		$this->add_control(
			'cart_title_bg_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .pp-woo-mini-cart-title' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
					'cart_title!'   => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'cart_title_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-woo-mini-cart .pp-woo-mini-cart-title',
				'condition' => array(
					'show_cart_on!' => 'none',
					'cart_title!'   => '',
				),
			)
		);

		$this->add_responsive_control(
			'cart_title_text_align',
			array(
				'label'     => __( 'Alignment', 'powerpack' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .pp-woo-mini-cart-title'   => 'text-align: {{VALUE}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
					'cart_title!'   => '',
				),
			)
		);

		$this->add_responsive_control(
			'cart_title_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-mini-cart .pp-woo-mini-cart-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'show_cart_on!' => 'none',
					'cart_title!'   => '',
				),
			)
		);

		$this->add_control(
			'subtotal_heading',
			array(
				'label'     => __( 'Subtotal', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'subtotal_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .woocommerce-mini-cart__total' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'subtotal_bg_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .woocommerce-mini-cart__total' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'subtotal_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-mini-cart .woocommerce-mini-cart__total',
				'condition'   => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'subtotal_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-woo-mini-cart .woocommerce-mini-cart__total',
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'subtotal_text_align',
			array(
				'label'     => __( 'Alignment', 'powerpack' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .woocommerce-mini-cart__total'   => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'subtotal_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-mini-cart .woocommerce-mini-cart__total' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_message_heading',
			array(
				'label'     => __( 'Cart Message', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'show_cart_on!' => 'none',
					'cart_message!' => '',
				),
			)
		);

		$this->add_control(
			'cart_message_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .pp-woo-mini-cart-message' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
					'cart_message!' => '',
				),
			)
		);

		$this->add_control(
			'cart_message_bg_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .pp-woo-mini-cart-message' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
					'cart_message!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'cart_message_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-woo-mini-cart .pp-woo-mini-cart-message',
				'condition' => array(
					'show_cart_on!' => 'none',
					'cart_message!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'cart_message_text_align',
			array(
				'label'     => __( 'Alignment', 'powerpack' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .pp-woo-mini-cart-message'   => 'text-align: {{VALUE}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
					'cart_message!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'cart_message_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'default'    => array(
					'top'      => '10',
					'right'    => '0',
					'bottom'   => '0',
					'left'     => '0',
					'unit'     => 'px',
					'isLinked' => false,
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-mini-cart .pp-woo-mini-cart-message' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'show_cart_on!' => 'none',
					'cart_message!' => '',
				),
			)
		);

		$this->add_control(
			'empty_cart_message_heading',
			array(
				'label'     => __( 'Empty Cart Message', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'empty_cart_message_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .woocommerce-mini-cart__empty-message' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'empty_cart_message_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-woo-mini-cart .woocommerce-mini-cart__empty-message',
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'empty_cart_message_text_align',
			array(
				'label'     => __( 'Alignment', 'powerpack' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .woocommerce-mini-cart__empty-message'   => 'text-align: {{VALUE}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Cart Table
	 * -------------------------------------------------
	 */
	protected function register_style_items_controls() {
		$this->start_controls_section(
			'section_items_style',
			array(
				'label'     => __( 'Item', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_items_row_separator_type',
			array(
				'label'     => __( 'Separator Type', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'solid',
				'options'   => array(
					'none'   => __( 'None', 'powerpack' ),
					'solid'  => __( 'Solid', 'powerpack' ),
					'dotted' => __( 'Dotted', 'powerpack' ),
					'dashed' => __( 'Dashed', 'powerpack' ),
					'double' => __( 'Double', 'powerpack' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart ul.product_list_widget li:not(:last-child)' => 'border-bottom-style: {{VALUE}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_items_row_separator_color',
			array(
				'label'     => __( 'Separator Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart ul.product_list_widget li:not(:last-child)' => 'border-bottom-color: {{VALUE}};',
				),
				'condition' => array(
					'show_cart_on!'                  => 'none',
					'cart_items_row_separator_type!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'cart_items_row_separator_size',
			array(
				'label'     => __( 'Separator Size', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => '',
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 20,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart ul.product_list_widget li:not(:last-child)' => 'border-bottom-width: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'show_cart_on!'                  => 'none',
					'cart_items_row_separator_type!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'cart_items_spacing',
			array(
				'label'     => __( 'Items Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => '',
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 20,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart ul.product_list_widget li' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'cart_items_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-mini-cart ul.product_list_widget li' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->start_controls_tabs( 'cart_items_rows_tabs_style' );

		$this->start_controls_tab(
			'cart_items_even_row',
			array(
				'label'     => __( 'Even Row', 'powerpack' ),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_items_even_row_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .mini_cart_item:nth-child(2n)' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_items_even_row_links_color',
			array(
				'label'     => __( 'Links Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .mini_cart_item:nth-child(2n) a' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_items_even_row_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .mini_cart_item:nth-child(2n)' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'cart_items_odd_row',
			array(
				'label'     => __( 'Odd Row', 'powerpack' ),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_items_odd_row_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .mini_cart_item:nth-child(2n+1)' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_items_odd_row_links_color',
			array(
				'label'     => __( 'Links Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .mini_cart_item:nth-child(2n+1) a' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_items_odd_row_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .mini_cart_item:nth-child(2n+1)' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'item_name_heading',
			array(
				'label'     => __( 'Item Name', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'item_name_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-woo-mini-cart .mini_cart_item a:not(.remove)',
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'item_name_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .mini_cart_item a:not(.remove)' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'item_name_bottom_spacing',
			array(
				'label'     => __( 'Bottom Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => '',
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 20,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .mini_cart_item a:not(.remove)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'show_cart_on!'                  => 'none',
					'cart_items_row_separator_type!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_items_image_heading',
			array(
				'label'     => __( 'Image', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'cart_items_image_position',
			array(
				'label'       => __( 'Position', 'powerpack' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => array(
					'left'  => array(
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-h-align-left',
					),
					'right' => array(
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'default'     => 'left',
				'selectors'   => array(
					'{{WRAPPER}} .pp-woo-mini-cart ul li.woocommerce-mini-cart-item a img' => 'float: {{VALUE}};',
				),
				'condition'   => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'cart_items_image_spacing',
			array(
				'label'      => __( 'Spacing', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'default'    => array(
					'size' => '',
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-mini-cart ul li.woocommerce-mini-cart-item a img' => 'margin-right: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'cart_items_image_width',
			array(
				'label'      => __( 'Width', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'default'    => array(
					'size' => '',
				),
				'range'      => array(
					'px' => array(
						'min' => 10,
						'max' => 250,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-mini-cart ul li.woocommerce-mini-cart-item a img' => 'width: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_items_price_heading',
			array(
				'label'     => __( 'Item Quantity & Price', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'cart_items_price_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-woo-mini-cart .cart_list .quantity',
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_items_price_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .cart_list .quantity' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_items_remove_icon_heading',
			array(
				'label'     => __( 'Remove Item Icon', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'cart_items_remove_icon_size',
			array(
				'label'      => __( 'Size', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'default'    => array(
					'size' => '',
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-mini-cart ul.cart_list li a.remove' => 'font-size: {{SIZE}}{{UNIT}}; width: calc({{SIZE}}{{UNIT}} + 6px); height: calc({{SIZE}}{{UNIT}} + 6px);',
				),
				'condition'  => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_cart_items_remove_icon_style' );

		$this->start_controls_tab(
			'tab_cart_items_remove_icon_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_items_remove_icon_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart ul.cart_list li a.remove' => 'color: {{VALUE}} !important;',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_items_remove_icon_bg_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart ul.cart_list li a.remove' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_items_remove_icon_border_color',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart ul.cart_list li a.remove' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_cart_items_remove_icon_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_items_remove_icon_color_hover',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart ul.cart_list li a.remove:hover' => 'color: {{VALUE}} !important;',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_items_remove_icon_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart ul.cart_list li a.remove:hover' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_items_remove_icon_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart ul.cart_list li a.remove:hover' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Buttons
	 * -------------------------------------------------
	 */
	protected function register_style_buttons_controls() {

		$this->start_controls_section(
			'section_buttons_style',
			array(
				'label'     => __( 'Buttons', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'buttons_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-woo-mini-cart .buttons .button',
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'buttons_layout',
			array(
				'label'        => __( 'Layout', 'powerpack' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'inline',
				'options'      => array(
					'inline'  => __( 'Inline', 'powerpack' ),
					'stacked' => __( 'Stacked', 'powerpack' ),
				),
				'prefix_class' => 'pp-woo-cart-buttons-',
				'condition'    => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'buttons_align',
			array(
				'label'        => __( 'Alignment', 'powerpack' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => array(
					'left'   => array(
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'      => '',
				'prefix_class' => 'pp-woo-menu-cart-align-',
				'selectors'    => array(
					'{{WRAPPER}}.pp-woo-cart-buttons-inline .buttons'   => 'text-align: {{VALUE}};',
				),
				'condition'    => array(
					'show_cart_on!'  => 'none',
					'buttons_layout' => 'inline',
				),
			)
		);

		$this->add_responsive_control(
			'buttons_gap',
			array(
				'label'     => __( 'Space Between', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => '',
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 60,
					),
				),
				'selectors' => array(
					'{{WRAPPER}}.pp-woo-cart-buttons-inline .buttons .button.checkout.checkout' => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.pp-woo-cart-buttons-stacked .buttons .button.checkout.checkout' => 'margin-top: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'buttons_margin_top',
			array(
				'label'     => __( 'Margin Top', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => '',
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 60,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart-items .buttons' => 'margin-top: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'buttons_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-mini-cart .buttons .button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'view_cart_button_heading',
			array(
				'label'     => __( 'View Cart Button', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_view_cart_button_style' );

		$this->start_controls_tab(
			'tab_view_cart_button_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'view_cart_button_bg_color_normal',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .buttons .button:not(.checkout)' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'view_cart_button_text_color_normal',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .buttons .button:not(.checkout)' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'view_cart_button_border_normal',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-mini-cart .buttons .button:not(.checkout)',
				'condition'   => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'view_cart_button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-mini-cart .buttons .button:not(.checkout)' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'view_cart_button_box_shadow',
				'selector'  => '{{WRAPPER}} .pp-woo-mini-cart .buttons .button:not(.checkout)',
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_view_cart_button_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'view_cart_button_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .buttons .button:not(.checkout):hover' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'view_cart_button_text_color_hover',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .buttons .button:not(.checkout):hover' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'view_cart_button_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .buttons .button:not(.checkout):hover' => 'border-color: {{VALUE}}',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'view_cart_button_box_shadow_hover',
				'selector'  => '{{WRAPPER}} .pp-woo-mini-cart .buttons .button:not(.checkout):hover',
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'checkout_button_heading',
			array(
				'label'     => __( 'Checkout Button', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_checkout_button_style' );

		$this->start_controls_tab(
			'tab_checkout_button_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'checkout_button_bg_color_normal',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .buttons .button.checkout' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'checkout_button_text_color_normal',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .buttons .button.checkout' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'checkout_button_border_normal',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-mini-cart .buttons .button.checkout',
				'condition'   => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'checkout_button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-mini-cart .buttons .button.checkout' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'checkout_button_box_shadow',
				'selector'  => '{{WRAPPER}} .pp-woo-mini-cart .buttons .button.checkout',
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_checkout_button_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'checkout_button_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .buttons .button.checkout:hover' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'checkout_button_text_color_hover',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .buttons .button.checkout:hover' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_control(
			'checkout_button_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart .buttons .button.checkout:hover' => 'border-color: {{VALUE}}',
				),
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'checkout_button_box_shadow_hover',
				'selector'  => '{{WRAPPER}} .pp-woo-mini-cart .buttons .button.checkout:hover',
				'condition' => array(
					'show_cart_on!' => 'none',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Cart Button
	 * -------------------------------------------------
	 */
	protected function register_style_cart_button_controls() {

		$this->start_controls_section(
			'section_cart_button_style',
			array(
				'label' => __( 'Cart Button', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'cart_button_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-mini-cart-container .pp-woo-cart-contents',
			)
		);

		$this->start_controls_tabs( 'tabs_cart_button' );

		$this->start_controls_tab(
			'tab_cart_button_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'cart_button_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart-container .pp-woo-cart-contents' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'cart_button_bg_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart-container .pp-woo-cart-contents' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'cart_button_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-mini-cart-container .pp-woo-cart-contents',
			)
		);

		$this->add_control(
			'cart_button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-mini-cart-container .pp-woo-cart-contents' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'cart_button_margin',
			array(
				'label'      => __( 'Margin', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-mini-cart-container .pp-woo-cart-contents' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'cart_button_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-mini-cart-container .pp-woo-cart-contents' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_cart_button_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'cart_button_color_hover',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart-container .pp-woo-cart-contents:hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'cart_button_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart-container .pp-woo-cart-contents:hover' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'cart_button_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-mini-cart-container .pp-woo-cart-contents:hover' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'cart_button_icon_heading',
			array(
				'label'     => __( 'Button Icon', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'icon_style' => array( 'icon_text', 'icon' ),
				),
			)
		);

		$this->add_responsive_control(
			'cart_button_icon_size',
			array(
				'label'      => __( 'Icon Size', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'default'    => array(
					'size' => '',
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 40,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-cart-button .pp-icon' => 'font-size: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'icon_style' => array( 'icon_text', 'icon' ),
					'icon_type'  => 'icon',
				),
			)
		);

		$this->add_responsive_control(
			'cart_button_icon_img_size',
			array(
				'label'     => __( 'Icon Size', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => '',
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 60,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart-button .pp-cart-contents-icon-image img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'icon_style' => array( 'icon_text', 'icon' ),
					'icon_type'  => 'image',
				),
			)
		);

		$this->add_responsive_control(
			'cart_button_icon_spacing',
			array(
				'label'     => __( 'Icon Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => '',
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 40,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart-button .pp-icon' => 'margin-left: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'icon_style' => array( 'icon_text', 'icon' ),
				),
			)
		);

		$this->start_controls_tabs( 'tabs_cart_button_icon' );

		$this->start_controls_tab(
			'tab_cart_button_icon_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'cart_button_icon_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart-button .pp-icon' => 'color: {{VALUE}}',
					'{{WRAPPER}} .pp-woo-cart-button .pp-icon svg' => 'fill: {{VALUE}}',
				),
				'condition' => array(
					'icon_style' => array( 'icon_text', 'icon' ),
					'icon_type'  => 'icon',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_cart_button_icon_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'cart_button_icon_color_hover',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart-button:hover .pp-icon' => 'color: {{VALUE}}',
					'{{WRAPPER}} .pp-woo-cart-button:hover .pp-icon svg' => 'fill: {{VALUE}}',
				),
				'condition' => array(
					'icon_style' => array( 'icon_text', 'icon' ),
					'icon_type'  => 'icon',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'cart_button_counter_heading',
			array(
				'label'     => __( 'Counter', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'counter_position!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_button_counter_gap',
			array(
				'label'     => __( 'Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'unit' => 'px',
				),
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 20,
						'step' => 1,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-menu-cart-counter-after .pp-cart-contents-count-after' => 'margin-left: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'counter_position' => 'after',
				),
			)
		);

		$this->add_control(
			'cart_button_counter_distance',
			array(
				'label'     => __( 'Distance', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'unit' => 'em',
				),
				'range'     => array(
					'em' => array(
						'min'  => 0,
						'max'  => 4,
						'step' => 0.1,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-cart-counter' => 'right: -{{SIZE}}{{UNIT}}; top: -{{SIZE}}{{UNIT}}',
				),
				'condition' => array(
					'counter_position' => 'top',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_cart_counter' );

		$this->start_controls_tab(
			'tab_cart_counter_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'cart_button_counter_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-cart-counter' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'counter_position!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_button_counter_bg_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-cart-counter' => 'background-color: {{VALUE}}',
					'{{WRAPPER}} .pp-woo-menu-cart-counter-after .pp-cart-counter:before' => 'border-right-color: {{VALUE}}',
				),
				'condition' => array(
					'counter_position!' => 'none',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_cart_counter_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'cart_button_counter_color_hover',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart-button:hover .pp-cart-counter' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'counter_position!' => 'none',
				),
			)
		);

		$this->add_control(
			'cart_button_counter_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart-button:hover .pp-cart-counter' => 'background-color: {{VALUE}}',
					'{{WRAPPER}} .pp-woo-cart-button:hover .pp-woo-menu-cart-counter-after .pp-cart-counter:before' => 'border-right-color: {{VALUE}}',
				),
				'condition' => array(
					'counter_position!' => 'none',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Render output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_counter() {
		?>
		<span class="pp-cart-counter"><?php echo esc_html( WC()->cart->get_cart_contents_count() ); ?></span>
		<?php
	}

	/**
	 * Render output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_cart_icon() {
		$settings = $this->get_settings();

		if ( 'icon' === $settings['icon_type'] ) {
			?>
			<span class="pp-mini-cart-button-icon pp-icon">
				<?php
					\Elementor\Icons_Manager::render_icon(
						$settings['icon'],
						array(
							'class'       => 'pp-cart-contents-icon',
							'aria-hidden' => 'true',
						)
					);
				?>
			</span>
			<?php
		} elseif ( 'image' === $settings['icon_type'] && $settings['icon_image']['url'] ) {
			?>
			<span class="pp-cart-contents-icon-image pp-icon">
				<?php
					echo wp_kses_post( Group_Control_Image_Size::get_attachment_image_html( $settings, 'icon_image', 'icon_image' ) );
				?>
			</span>
			<?php
		}
	}

	/**
	 * Render output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_text() {
		$settings = $this->get_settings();
		?>
		<span class="pp-cart-contents-text"><?php echo wp_kses_post( $settings['cart_text'] ); ?></span>
		<?php
	}

	/**
	 * Render output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_subtotal() {
		$settings = $this->get_settings();

		$sub_total = WC()->cart->get_cart_subtotal();

		if ( 'yes' === $settings['show_subtotal'] ) {
			?>
			<span class="pp-cart-subtotal"><?php echo wp_kses_post( $sub_total ); ?></span>
			<?php
		}
	}

	/**
	 * Render output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render() {
		if ( null === WC()->cart ) {
			return;
		}

		$settings  = $this->get_settings_for_display();
		$is_editor = \Elementor\Plugin::instance()->editor->is_edit_mode();

		$this->add_render_attribute( 'container', [
			'class' => [
				'pp-woocommerce',
				'pp-woo-mini-cart-container',
				'pp-woo-menu-cart-counter-' . $settings['counter_position'],
			],
			'data-target' => $settings['show_cart_on'],
		] );

		$this->add_render_attribute( 'button', [
			'class' => [
				'pp-woo-cart-contents',
				'pp-woo-cart-' . $settings['icon_style'],
			],
			'title' => __( 'View your shopping cart', 'powerpack' ),
		] );

		if ( $is_editor && 'yes' === $settings['show_preview'] ) {
			$this->add_render_attribute( 'container', 'class', 'pp-woo-mini-cart-preview' );
		}

		if ( 'none' === $settings['show_cart_on'] ) {
			if ( ! empty( $settings['link']['url'] ) ) {
				$this->add_link_attributes( 'button', $settings['link'] );
			}
		} else {
			$this->add_render_attribute( 'button', 'href', '#' );
		}
		?>
		<?php do_action( 'pp_woo_before_mini_cart_wrap' ); ?>

		<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'container' ) ); ?>>
			<?php
			if ( 'floating' === $settings['button_position'] ) {
				$placeholder = sprintf( __( 'Mini Cart button is floating. Click here to edit the "%1$s" settings. This placeholder will not be shown on the live page.', 'powerpack' ), esc_attr( $this->get_title() ) );

				echo wp_kses_post( $this->render_editor_placeholder(
					array(
						'body' => $placeholder,
					)
				) );
			}
			?>

			<div class="pp-woo-cart-button">
				<div class="pp-woo-cart-button-inner">
					<a <?php echo wp_kses_post( $this->get_render_attribute_string( 'button' ) ); ?>>
						<span class="pp-cart-button-wrap">
							<?php
							if ( 'icon' === $settings['icon_style'] ) {

								$this->render_subtotal();
								$this->render_cart_icon();

							} elseif ( 'icon_text' === $settings['icon_style'] ) {

								$this->render_text();
								$this->render_subtotal();
								$this->render_cart_icon();

							} else {

								$this->render_text();
								$this->render_subtotal();

							}
							?>
						</span>

						<?php if ( 'top' === $settings['counter_position'] ) { ?>
							<span class="pp-cart-contents-count">
								<?php $this->render_counter(); ?>
							</span>
						<?php } ?>
					</a>

					<?php if ( 'after' === $settings['counter_position'] ) { ?>
						<span class="pp-cart-contents-count-after">
							<?php $this->render_counter(); ?>
						</span>
					<?php } ?>
				</div>
			</div>

			<?php if ( 'none' !== $settings['show_cart_on'] ) { ?>
				<div class="pp-woo-mini-cart-wrap pp-v-hidden pp-pos-abs">
					<div class="pp-woo-mini-cart pp-woo-menu-cart">
						<?php if ( $settings['cart_title'] ) { ?>
							<h3 class="pp-woo-mini-cart-title">
								<?php echo wp_kses_post( $settings['cart_title'] ); ?>
							</h3>
						<?php } ?>

						<div class="pp-woo-mini-cart-items">
							<div class="widget_shopping_cart_content"><?php woocommerce_mini_cart(); ?></div>
						</div>

						<?php if ( $settings['cart_message'] ) { ?>
							<div class="pp-woo-mini-cart-message">
								<?php echo wp_kses_post( $this->parse_text_editor( $settings['cart_message'] ) ); ?>
							</div>
						<?php } ?>
					</div>
				</div>
			<?php } ?>
		</div>

		<?php do_action( 'pp_woo_after_mini_cart_wrap' ); ?>
		<?php
	}
}
