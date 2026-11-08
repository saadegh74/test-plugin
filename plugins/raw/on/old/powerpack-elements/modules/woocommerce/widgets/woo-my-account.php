<?php
/**
 * PowerPack WooCommerce My Account widget.
 *
 * @package PowerPack
 */

namespace PowerpackElements\Modules\Woocommerce\Widgets;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Classes\PP_Config;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Core\Schemes\Typography as Scheme_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit;   // Exit if accessed directly.
}

/**
 * Class Woo_My_Account.
 */
class Woo_My_Account extends Powerpack_Widget {

	/**
	 * Retrieve woo my account widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Woo_My_Account' );
	}

	/**
	 * Retrieve woo my account widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Woo_My_Account' );
	}

	/**
	 * Retrieve woo my account widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Woo_My_Account' );
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
		return parent::get_widget_keywords( 'Woo_My_Account' );
	}

	/**
	 * Retrieve the list of styles the Woo - My Account depended on.
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

	public static function get_endpoints() {
		$endpoints = array(
			''             => __( 'Dashboard', 'powerpack' ),
			'orders'       => __( 'Orders', 'powerpack' ),
			// 'view-order'  => 'view-order',
			'downloads'    => __( 'Downloads', 'powerpack' ),
			'edit-address' => __( 'Addresses', 'powerpack' ),
			// 'payment-methods' => 'payment-methods',
			// 'add-payment-method'  => 'add-payment-method',
			'edit-account' => __( 'Account Details', 'powerpack' ),
		);

		return $endpoints;
	}

	/**
	 * Register Woo - My Account widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 2.0.3
	 * @access protected
	 */
	protected function register_controls() {
		/* Content: General */
		$this->register_content_controls_general();

		/* Content: Tabs */
		$this->register_content_controls_tabs();

		/* Help Docs */
		$this->register_content_help_docs();

		/* Style: Tabs */
		$this->register_style_controls_tabs();

		/* Style: Tables */
		$this->register_style_controls_tables();

		/* Style: Buttons */
		$this->register_style_controls_buttons();

		/* Style: Forms */
		$this->register_style_controls_forms();

		/* Style: Errors */
		$this->register_style_controls_errors();

	}

	/**
	 * Style Tab: Section
	 * -------------------------------------------------
	 */
	protected function register_content_controls_general() {

		$this->start_controls_section(
			'section_content_general',
			array(
				'label' => __( 'Preview', 'powerpack' ),
			)
		);

		$this->add_control(
			'endpoint',
			array(
				'label'   => __( 'Select Endpoint', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => self::get_endpoints(),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Section
	 * -------------------------------------------------
	 */
	protected function register_content_controls_tabs() {

		$this->start_controls_section(
			'section_content_tabs',
			array(
				'label' => __( 'Tabs', 'powerpack' ),
			)
		);

		$this->add_control(
			'show_dashboard',
			array(
				'label'        => __( 'Show Dashboard', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'dashboard_tab_name',
			[
				'label' => esc_html__( 'Tab Name', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Dashboard', 'powerpack' ),
				'dynamic' => [
					'active' => true,
				],
				'condition'   => array(
					'show_dashboard' => 'yes',
				),
			]
		);

		$this->add_control(
			'show_orders',
			array(
				'label'        => __( 'Show Orders', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'separator'    => 'before',
			)
		);

		$this->add_control(
			'orders_tab_name',
			[
				'label'     => esc_html__( 'Tab Name', 'powerpack' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Orders', 'powerpack' ),
				'dynamic'   => [
					'active' => true,
				],
				'condition' => array(
					'show_orders' => 'yes',
				),
			]
		);

		$this->add_control(
			'show_downloads',
			array(
				'label'        => __( 'Show Downloads', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'separator'    => 'before',
			)
		);

		$this->add_control(
			'downloads_tab_name',
			[
				'label'     => esc_html__( 'Tab Name', 'powerpack' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Downloads', 'powerpack' ),
				'dynamic'   => [
					'active' => true,
				],
				'condition' => array(
					'show_downloads' => 'yes',
				),
			]
		);

		$this->add_control(
			'show_addresses',
			array(
				'label'        => __( 'Show Addresses', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'separator'    => 'before',
			)
		);

		$this->add_control(
			'addresses_tab_name',
			[
				'label'     => esc_html__( 'Tab Name', 'powerpack' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Addresses', 'powerpack' ),
				'dynamic'   => [
					'active' => true,
				],
				'condition' => array(
					'show_addresses' => 'yes',
				),
			]
		);

		$this->add_control(
			'show_account_details',
			array(
				'label'        => __( 'Show Account Details', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'separator'    => 'before',
			)
		);

		$this->add_control(
			'account_details_tab_name',
			[
				'label'     => esc_html__( 'Tab Name', 'powerpack' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Account Details', 'powerpack' ),
				'dynamic'   => [
					'active' => true,
				],
				'condition' => array(
					'show_account_details' => 'yes',
				),
			]
		);

		$this->add_control(
			'show_logout_link',
			array(
				'label'        => __( 'Show Logout Link', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'separator'    => 'before',
			)
		);

		$this->add_control(
			'logout_link_tab_name',
			[
				'label'     => esc_html__( 'Tab Name', 'powerpack' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Logout', 'powerpack' ),
				'dynamic'   => [
					'active' => true,
				],
				'condition' => array(
					'show_logout_link' => 'yes',
				),
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Help Docs: Section
	 * -------------------------------------------------
	 */
	protected function register_content_help_docs() {

		$help_docs = PP_Config::get_widget_help_links( 'Woo_My_Account' );

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
		}

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Tabs
	 * -------------------------------------------------
	 */
	protected function register_style_controls_tabs() {

		$this->start_controls_section(
			'section_tabs_style',
			array(
				'label' => __( 'Tabs', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'tab_position',
			array(
				'label'        => __( 'Tab Position', 'powerpack' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'left',
				'options'      => array(
					'left'  => __( 'Left', 'powerpack' ),
					'right' => __( 'Right', 'powerpack' ),
					'top'   => __( 'Top', 'powerpack' ),
				),
				'prefix_class' => 'pp-woo-tab-position%s-',
			)
		);

		$this->add_control(
			'tabs_alignment',
			array(
				'label'       => __( 'Alignment', 'powerpack' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => array(
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
				'default'     => 'left',
				'selectors'   => array(
					'{{WRAPPER}}.pp-woo-tab-position-top .woocommerce .woocommerce-MyAccount-navigation ul,
					{{WRAPPER}}.pp-woo-tab-position-tablet-top .woocommerce .woocommerce-MyAccount-navigation ul,
					{{WRAPPER}}.pp-woo-tab-position-mobile-top .woocommerce .woocommerce-MyAccount-navigation ul' => 'align-items: {{VALUE}}; justify-content: {{VALUE}};',
				),
				'condition'   => array(
					'tab_position'        => 'top',
					'tab_position_tablet' => 'top',
					'tab_position_mobile' => 'top',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'tab_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'scheme'   => Scheme_Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-navigation ul li a',
			)
		);

		$this->add_responsive_control(
			'tabs_spacing',
			array(
				'label'      => __( 'Spacing between Tabs & Content', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'size' => 30,
				),
				'selectors'  => array(
					'{{WRAPPER}}.pp-woo-tab-position-top .pp-woo-my-account .woocommerce-MyAccount-navigation' => 'margin-bottom: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}}.pp-woo-tab-position-left .pp-woo-my-account .woocommerce-MyAccount-content,
					{{WRAPPER}}.pp-woo-tab-position-right .pp-woo-my-account .woocommerce-MyAccount-content' => 'width: calc( 70% - {{SIZE}}{{UNIT}} )',
					'(mobile){{WRAPPER}}.pp-woo-tab-position-left .pp-woo-my-account .woocommerce-MyAccount-content,
					{{WRAPPER}}.pp-woo-tab-position-right .pp-woo-my-account .woocommerce-MyAccount-content' => 'width: calc( 100% - {{SIZE}}{{UNIT}} )',
				),
			)
		);

		$this->add_responsive_control(
			'tab_margin',
			array(
				'label'      => __( 'Spacing between Tabs', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}}.pp-woo-tab-position-top .woocommerce .woocommerce-MyAccount-navigation ul li' => 'margin-right: {{SIZE}}{{UNIT}}',
					'(tablet) {{WRAPPER}}.pp-woo-tab-position-tablet-top .woocommerce .woocommerce-MyAccount-navigation ul li' => 'margin-right: {{SIZE}}{{UNIT}}',
					'(mobile) {{WRAPPER}}.pp-woo-tab-position-mobile-top .woocommerce .woocommerce-MyAccount-navigation ul li' => 'margin-right: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}}.pp-woo-tab-position-left .woocommerce .woocommerce-MyAccount-navigation ul li'  => 'margin-bottom: {{SIZE}}{{UNIT}}; margin-right:0;',
					'(tablet) {{WRAPPER}}.pp-woo-tab-position-tablet-left .woocommerce .woocommerce-MyAccount-navigation ul li'  => 'margin-bottom: {{SIZE}}{{UNIT}}; margin-right:0;',
					'(mobile) {{WRAPPER}}.pp-woo-tab-position-mobile-left .woocommerce .woocommerce-MyAccount-navigation ul li' => 'margin-bottom: {{SIZE}}{{UNIT}}; margin-right:0;',
					'{{WRAPPER}}.pp-woo-tab-position-right .woocommerce .woocommerce-MyAccount-navigation ul li' => 'margin-bottom: {{SIZE}}{{UNIT}}; margin-right:0;',
					'(tablet) {{WRAPPER}}.pp-woo-tab-position-tablet-right .woocommerce .woocommerce-MyAccount-navigation ul li' => 'margin-bottom: {{SIZE}}{{UNIT}}; margin-right:0;',
					'(mobile) {{WRAPPER}}.pp-woo-tab-position-mobile-right .woocommerce .woocommerce-MyAccount-navigation ul li' => 'margin-bottom: {{SIZE}}{{UNIT}}; margin-right:0;',
				),
				// 'selectors'             => [
				// '{{WRAPPER}}.pp-woo-tab-position-top .woocommerce .woocommerce-MyAccount-navigation ul li,
				// {{WRAPPER}}.pp-woo-tab-position-tablet-top .woocommerce .woocommerce-MyAccount-navigation ul li,
				// {{WRAPPER}}.pp-woo-tab-position-mobile-top .woocommerce .woocommerce-MyAccount-navigation ul li' => 'margin-right: {{SIZE}}{{UNIT}}; margin-bottom: {{SIZE}}{{UNIT}};',
				// '{{WRAPPER}}.pp-woo-tab-position-left .woocommerce .woocommerce-MyAccount-navigation ul li,
				// {{WRAPPER}}.pp-woo-tab-position-tablet-left .woocommerce .woocommerce-MyAccount-navigation ul li,
				// {{WRAPPER}}.pp-woo-tab-position-mobile-left .woocommerce .woocommerce-MyAccount-navigation ul li' => 'margin-bottom: {{SIZE}}{{UNIT}}; margin-right: 0;',
				// '{{WRAPPER}}.pp-woo-tab-position-right .woocommerce .woocommerce-MyAccount-navigation ul li,
				// {{WRAPPER}}.pp-woo-tab-position-tablet-right .woocommerce .woocommerce-MyAccount-navigation ul li,
				// {{WRAPPER}}.pp-woo-tab-position-mobile-right .woocommerce .woocommerce-MyAccount-navigation ul li' => 'margin-bottom: {{SIZE}}{{UNIT}}; margin-right: 0;',
				// ],
			)
		);

		// $this->add_control(
		// 'tab_margin',
		// [
		// 'label'         => __( 'Margin', 'powerpack' ),
		// 'type'          => Controls_Manager::DIMENSIONS,
		// 'size_units'    => [ 'px', 'em', '%' ],
		// 'selectors'     => [
		// '{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-navigation ul li' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		// ],
		// ]
		// );

		$this->add_responsive_control(
			'tab_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-navigation ul li a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_style' );

		$this->start_controls_tab(
			'tabs_normal',
			array(
				'label' => __( 'Default', 'powerpack' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'tab_bg_color',
				'label'    => __( 'Background', 'powerpack' ),
				'types'    => array( 'classic', 'gradient' ),
				'exclude'  => array( 'image' ),
				'selector' => '{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-navigation ul li a',
			)
		);

		$this->add_control(
			'tab_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-navigation ul li a' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'tab_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-navigation ul li a',
			)
		);

		$this->add_responsive_control(
			'tab_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-navigation ul li a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'tab_box_shadow',
				'selector' => '{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-navigation ul li a',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tabs_style_active',
			array(
				'label' => __( 'Active', 'powerpack' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'active_tab_bg_color',
				'label'    => __( 'Background', 'powerpack' ),
				'types'    => array( 'classic', 'gradient' ),
				'exclude'  => array( 'image' ),
				'selector' => '{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-navigation ul li.is-active a, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-navigation ul li:hover a',
			)
		);

		$this->add_control(
			'active_tab_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-navigation ul li.is-active a, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-navigation ul li:hover a' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'active_tab_border_color',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-navigation ul li.is-active a, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-navigation ul li:hover a' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'active_tab_box_shadow',
				'selector' => '{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-navigation ul li.is-active a, {{WRAPPER}} .pp-woo-my-account .woocommerce-MyAccount-navigation ul li:hover a',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'tabs_content_heading',
			array(
				'label'     => __( 'Tab Content', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'tab_content_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-navigation' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'tab_content_link_color',
			array(
				'label'     => __( 'Link Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content a' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'tab_content_link_hover',
			array(
				'label'     => __( 'Link Hover Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content a:hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'tab_content_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'scheme'   => Scheme_Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Tables
	 * -------------------------------------------------
	 */
	protected function register_style_controls_tables() {

		$this->start_controls_section(
			'section_tables_style',
			array(
				'label' => __( 'Tables', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'table_heading',
			array(
				'label' => __( 'Table', 'powerpack' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'table_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'scheme'   => Scheme_Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .pp-woo-my-account .woocommerce table thead th, {{WRAPPER}} .pp-woo-my-account .woocommerce table tbody td, {{WRAPPER}} .pp-woo-my-account .woocommerce table tfoot td',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'table_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '',
				'default'     => '',
				'selector'    => '{{WRAPPER}} .pp-woo-my-account .woocommerce table',
			)
		);

		$this->add_responsive_control(
			'table_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce table' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'table_margin',
			array(
				'label'      => __( 'Margin', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce table' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'table_header_heading',
			array(
				'label'     => __( 'Header', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'table_header_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'scheme'   => Scheme_Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .pp-woo-my-account .woocommerce table thead th',
			)
		);

		$this->add_control(
			'table_header_bg_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce table thead th' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'table_header_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce table thead th' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'table_header_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '',
				'default'     => '',
				'selector'    => '{{WRAPPER}} .pp-woo-my-account .woocommerce table thead th',
			)
		);

		$this->add_responsive_control(
			'table_header_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce table thead th' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'table_rows_heading',
			array(
				'label'     => __( 'Rows', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'table_rows_bg_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce table tbody tr td' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'table_rows_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce table tbody tr td' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'table_even_rows_bg_color',
			array(
				'label'     => __( 'Even Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce table tbody tr:nth-child(even) td' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'table_even_rows_text_color',
			array(
				'label'     => __( 'Even Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce table tbody tr:nth-child(even) td' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'table_row_vertical_align',
			array(
				'label'       => __( 'Vertical Align', 'powerpack' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => array(
					'top'    => array(
						'title' => __( 'Top', 'powerpack' ),
						'icon'  => 'eicon-v-align-top',
					),
					'middle' => array(
						'title' => __( 'Middle', 'powerpack' ),
						'icon'  => 'eicon-v-align-middle',
					),
					'bottom' => array(
						'title' => __( 'Bottom', 'powerpack' ),
						'icon'  => 'eicon-v-align-bottom',
					),
				),
				'selectors'   => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce table tbody tr td'   => 'vertical-align: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'table_row_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '',
				'default'     => '',
				'selector'    => '{{WRAPPER}} .pp-woo-my-account .woocommerce table tbody tr th, {{WRAPPER}} .pp-woo-my-account .woocommerce table tbody tr td,
								{{WRAPPER}} .pp-woo-my-account .woocommerce table tfoot tr th, {{WRAPPER}} .pp-woo-my-account .woocommerce table tfoot tr td',
			)
		);

		$this->add_control(
			'table_cells_heading',
			array(
				'label'     => __( 'Cell', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'table_cell_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '',
				'default'     => '',
				'selector'    => '{{WRAPPER}} .pp-woo-my-account .woocommerce table tbody tr td',
			)
		);

		$this->add_responsive_control(
			'table_cell_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce table tbody tr td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'table_footer_heading',
			array(
				'label'     => __( 'Footer', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'table_footer_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'scheme'   => Scheme_Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .pp-woo-my-account .woocommerce table tfoot td',
			)
		);

		$this->add_control(
			'table_footer_bg_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce table tfoot td' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'table_footer_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce table tfoot td' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'table_footer_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '',
				'default'     => '',
				'selector'    => '{{WRAPPER}} .pp-woo-my-account .woocommerce table tfoot td',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Button
	 * -------------------------------------------------
	 */
	protected function register_style_controls_buttons() {

		$this->start_controls_section(
			'buttons_style',
			array(
				'label' => __( 'Buttons', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'scheme'   => Scheme_Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content .button',
			)
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'button_bg_color_normal',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content .button' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'button_text_color_normal',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content .button' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'button_border_normal',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content .button',
			)
		);

		$this->add_responsive_control(
			'button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content .button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'button_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content .button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content .button',
			)
		);

		$this->add_responsive_control(
			'order_section_button_settings',
			array(
				'label'     => __( 'Orders Section', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'order_section_button_spacing',
			array(
				'label'       => __( 'Button Spacing', 'powerpack' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'default'     => array(
					'unit' => 'px',
					'size' => 0,
				),
				'description' => 'Use the spacing setting when there are more than 1 action button under the Order Table action column.',
				'selectors'   => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce td.woocommerce-orders-table__cell.woocommerce-orders-table__cell-order-actions .woocommerce-button' => 'margin-right: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'button_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content .button:hover' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'button_text_color_hover',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content .button:hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'button_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content .button:hover' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_box_shadow_hover',
				'selector' => '{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content .button:hover',
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();

	}

	/**
	 * Style Tab: Section
	 * -------------------------------------------------
	 */
	protected function register_style_controls_forms() {

		$this->start_controls_section(
			'section_form_style',
			array(
				'label' => __( 'Forms', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'form_heading',
			array(
				'label' => __( 'Form', 'powerpack' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'form_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce form' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'form_link_color',
			array(
				'label'     => __( 'Link Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce form a' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'form_link_hover_color',
			array(
				'label'     => __( 'Link Hover Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce form a:hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'form_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .pp-woo-my-account .woocommerce form',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'form_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-my-account .woocommerce form',
			)
		);

		$this->add_responsive_control(
			'form_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce form' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'form_box_shadow',
				'selector' => '{{WRAPPER}} .pp-woo-my-account .woocommerce form',
			)
		);

		$this->add_responsive_control(
			'form_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce form' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'form_title_heading',
			array(
				'label'     => __( 'Headings', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'form_title_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'scheme'   => Scheme_Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .pp-woo-my-account .woocommerce h2, {{WRAPPER}} .pp-woo-my-account .woocommerce h3',
			)
		);

		$this->add_control(
			'form_title_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce h2, {{WRAPPER}} .pp-woo-my-account .woocommerce h3' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'form_title_margin',
			array(
				'label'              => __( 'Margin', 'powerpack' ),
				'type'               => Controls_Manager::DIMENSIONS,
				'size_units'         => array( 'px', 'em', '%' ),
				'allowed_dimensions' => 'vertical',
				'placeholder'        => array(
					'top'    => '',
					'right'  => 'auto',
					'bottom' => '',
					'left'   => 'auto',
				),
				'selectors'          => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce h2, {{WRAPPER}} .pp-woo-my-account .woocommerce h3' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		/** Inputs */
		$this->add_control(
			'form_inputs',
			array(
				'label'     => __( 'Inputs', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'inputs_text_align',
			array(
				'label'       => __( 'Text Alignment', 'powerpack' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => array(
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
				'default'     => 'left',
				'selectors'   => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .form-row .input-text' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'input_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'scheme'   => Scheme_Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .pp-woo-my-account .woocommerce .form-row .input-text',
			)
		);

		$this->add_control(
			'input_text_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .form-row .input-text' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'input_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .form-row .input-text' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'input_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-my-account .woocommerce .form-row .input-text',
			)
		);

		$this->add_responsive_control(
			'input_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .form-row .input-text' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'input_box_shadow',
				'selector' => '{{WRAPPER}} .pp-woo-my-account .woocommerce .form-row .input-text',
			)
		);

		$this->add_responsive_control(
			'input_gap',
			array(
				'label'     => __( 'Spacing', 'powerpack' ),
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
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .form-row' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'input_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .form-row .input-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'input_height',
			array(
				'label'     => __( 'Input Height', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 35,
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .form-row .input-text' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		/** Form Labels */
		$this->add_control(
			'form_labels',
			array(
				'label'     => __( 'Labels', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'form_label_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'scheme'   => Scheme_Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .pp-woo-my-account .woocommerce .form-row label',
			)
		);

		$this->add_control(
			'form_label_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .form-row label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'form_label_margin',
			array(
				'label'     => __( 'Margin', 'powerpack' ),
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
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .form-row label' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		/** Form Buttons */
		$this->add_control(
			'form_buttons',
			array(
				'label'     => __( 'Buttons', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'form_button_align',
			array(
				'label'       => __( 'Alignment', 'powerpack' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'default'     => 'left',
				'options'     => array(
					'left'   => array(
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'eicon-h-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'selectors'   => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-form-register .form-row:last-child,
					{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-form-login .form-row:last-child,
					{{WRAPPER}} .pp-woo-my-account .woocommerce form .pp-my-account-button' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'form_button_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'scheme'   => Scheme_Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .pp-woo-my-account .woocommerce form .form-row button, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content form .button',
			)
		);

		$this->add_control(
			'form_button_width',
			array(
				'label'        => __( 'Width', 'powerpack' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'auto',
				'options'      => array(
					'auto'   => __( 'Auto', 'powerpack' ),
					'full'   => __( 'Full Width', 'powerpack' ),
					'custom' => __( 'Custom', 'powerpack' ),
				),
				'prefix_class' => 'pp-woo-my-account-button-',
			)
		);

		$this->add_responsive_control(
			'form_button_custom_width',
			array(
				'label'      => __( 'Custom Width', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'default'    => array(
					'size' => '',
				),
				'range'      => array(
					'px' => array(
						'min' => 50,
						'max' => 500,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce form .form-row button, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content form .button' => 'width: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'form_button_width' => 'custom',
				),
			)
		);

		$this->add_responsive_control(
			'form_button_margin',
			array(
				'label'              => __( 'Margin', 'powerpack' ),
				'type'               => Controls_Manager::DIMENSIONS,
				'size_units'         => array( 'px', 'em', '%' ),
				'allowed_dimensions' => 'vertical',
				'placeholder'        => array(
					'top'    => '',
					'right'  => 'auto',
					'bottom' => '',
					'left'   => 'auto',
				),
				'selectors'          => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce form .form-row button, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content form .button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_form_button_style' );

		$this->start_controls_tab(
			'tab_form_button_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'form_button_bg_color_normal',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce form .form-row button, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content form .button' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'form_button_text_color_normal',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce form .form-row button, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content form .button' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'form_button_border_normal',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-my-account .woocommerce form .form-row button, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content form .button',
			)
		);

		$this->add_responsive_control(
			'form_button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce form .form-row button, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content form .button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'form_button_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce form .form-row button, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content form .button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'form_button_box_shadow',
				'selector' => '{{WRAPPER}} .pp-woo-my-account .woocommerce form .form-row button, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content form .button',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_form_button_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'form_button_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce form .form-row button:hover, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content form .button:hover' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'form_button_text_color_hover',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce form .form-row button:hover, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content form .button:hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'form_button_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce form .form-row button:hover, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content form .button:hover' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'form_button_box_shadow_hover',
				'selector' => '{{WRAPPER}} .pp-woo-my-account .woocommerce form .form-row button:hover, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-MyAccount-content form .button:hover',
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Errors
	 * -------------------------------------------------
	 */
	protected function register_style_controls_errors() {

		$this->start_controls_section(
			'section_notices_style',
			array(
				'label' => __( 'Notices', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		/** Form Errors */
		$this->add_control(
			'form_errors',
			array(
				'label' => __( 'Errors', 'powerpack' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'error_message_bg_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-error' => 'background: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'error_message_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-error' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'error_message_icon_color',
			array(
				'label'     => __( 'Icon Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-error:before' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'error_message_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-error',
			)
		);

		$this->add_responsive_control(
			'error_message_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-error' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'error_message_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-error' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'error_message_box_shadow',
				'selector' => '{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-error',
			)
		);

		/** Notices */
		$this->add_control(
			'form_notices',
			array(
				'label'     => __( 'General Notices', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'notice_bg_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-info, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-message' => 'background: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'notice_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-info, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-message' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'notice_icon_color',
			array(
				'label'     => __( 'Icon Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-info:before, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-message:before' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'notice_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-info, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-message',
			)
		);

		$this->add_responsive_control(
			'notice_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-info, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-message' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'notice_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-info, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-message' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'notice_box_shadow',
				'selector' => '{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-info, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-message',
			)
		);

		/** Notices Typography */

		$this->add_control(
			'form_notices_typography',
			array(
				'label'     => __( 'Typography', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'notice_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'scheme'   => Scheme_Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-error, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-info, {{WRAPPER}} .pp-woo-my-account .woocommerce .woocommerce-message',
			)
		);

		$this->end_controls_section();

	}

	public function modify_menu_items( $items, $endpoints ) {
		$settings = $this->get_settings_for_display();

		$menu_links = array(
			'dashboard' => [
				'field_key' => 'dashboard',
				'tab_name'  => esc_html__( 'Dashboard', 'powerpack' ),
			],
			'orders' => [
				'field_key' => 'orders',
				'tab_name'  => esc_html__( 'Orders', 'powerpack' ),
			],
			'downloads' => [
				'field_key' => 'downloads',
				'tab_name'  => esc_html__( 'Downloads', 'powerpack' ),
			],
			'addresses' => [
				'field_key' => 'edit-address',
				'tab_name'  => esc_html__( 'Addresses', 'powerpack' ),
			],
			'account_details' => [
				'field_key' => 'edit-account',
				'tab_name'  => esc_html__( 'Account Details', 'powerpack' ),
			],
			'logout_link' => [
				'field_key' => 'customer-logout',
				'tab_name'  => esc_html__( 'Logout', 'powerpack' ),
			],
		);

		foreach ( $menu_links as $tab_key => $tab ) {
			if ( isset( $settings[ 'show_' . $tab_key ] ) ) {
				if ( 'yes' !== $settings[ 'show_' . $tab_key ] ) {
					unset( $items[ $tab['field_key'] ] ); // Remove tab
				} else {
					$items[ $tab['field_key'] ] = $settings[ $tab_key . '_tab_name' ];
				}
			}
		}

		return $items;
	}

	private function get_shortcode() {

		$shortcode = sprintf( '[%s %s]', 'woocommerce_my_account', $this->get_render_attribute_string( 'shortcode' ) );

		return $shortcode;
	}

	/**
	 * Render output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute(
			'container',
			'class',
			array(
				'pp-woocommerce',
				'pp-woo-my-account',
				'clearfix',
			)
		);

		//add_filter( 'woocommerce_account_menu_items', array( $this, 'customize_my_account_tabs' ) );
		?>
		<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'container' ) ); ?>>
			<?php do_action( 'pp_woo_before_my_account_wrap' ); ?>

			<div class="pp-woo-my-account">
				<?php do_action( 'pp_woo_before_my_account_content', $settings ); ?>
					<?php
					if ( ! empty( $settings['endpoint'] ) && \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
						global $wp;
						$wp->query_vars[ $settings['endpoint'] ] = 1;
					}
					//add_filter( 'woocommerce_account_menu_items', array( $this, 'customize_my_account_tabs' ) );
					add_filter( 'woocommerce_account_menu_items', [ $this, 'modify_menu_items' ], 10, 2 );
					echo do_shortcode( '[woocommerce_my_account]' );
					//remove_filter( 'woocommerce_account_menu_items', array( $this, 'customize_my_account_tabs' ) );
					remove_filter( 'woocommerce_account_menu_items', [ $this, 'modify_menu_items' ], 10 );
					?>
				<?php do_action( 'pp_woo_after_my_account_content', $settings ); ?>
			</div>

			<?php do_action( 'pp_woo_after_my_account_wrap' ); ?>
		</div>
		<?php
	}

	public function customize_my_account_tabs( $menu_links ) {

		$settings = $this->get_settings();

		if ( 'yes' !== $settings['show_dashboard'] ) {
			unset( $menu_links['dashboard'] ); // Remove Dashboard tab
		}
		if ( 'yes' !== $settings['show_orders'] ) {
			unset( $menu_links['orders'] ); // Remove Orders tab
		}
		if ( 'yes' !== $settings['show_downloads'] ) {
			unset( $menu_links['downloads'] ); // Remove Downloads tab
		}
		if ( 'yes' !== $settings['show_addresses'] ) {
			unset( $menu_links['edit-address'] ); // Removed Addresses tab
		}
		if ( 'yes' !== $settings['show_account_details'] ) {
			unset( $menu_links['edit-account'] ); // Remove Account details tab
		}
		if ( 'yes' !== $settings['show_logout_link'] ) {
			unset( $menu_links['customer-logout'] ); // Remove Logout Link
		}

		return $menu_links;

	}

	public function render_plain_content() {
		echo $this->get_shortcode(); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
