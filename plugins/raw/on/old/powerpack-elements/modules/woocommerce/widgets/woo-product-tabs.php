<?php
/**
 * PowerPack Product Data Tabs Widget
 *
 * PowerPack Product Data Tabs widget uses WooCommerce's product tabs template to
 * fetch the data for the tabs. *
 *
 * @package PowerPack for Elementor Pro
 */

namespace PowerpackElements\Modules\Woocommerce\Widgets;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Classes\PP_Config;
use PowerpackElements\Classes\PP_Woo_Helper;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Plugin;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Core\Schemes\Typography as Scheme_Typography;
use Elementor\Modules\DynamicTags\Module as TagsModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit;   // Exit if accessed directly.
}

/**
 * Woo - Product Stock widget
 */
class Woo_Product_Tabs extends Powerpack_Widget {

	const WIDGET = 'Woo_Product_Tabs';

	public function get_categories() {
		return parent::get_woo_categories();
	}

	/**
	 * Retrieve Woo - Product Tabs widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( self::WIDGET );
	}

	/**
	 * Retrieve Woo - Product Tabs widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( self::WIDGET );
	}

	/**
	 * Retrieve Woo - Product Tabs widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( self::WIDGET );
	}

	/**
	 * Get Woo - Product Tabs widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @since 1.3.7
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( self::WIDGET );
	}

	/**
	 * Retrieve the list of scripts the Woo - Product Tabs widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return array(
			'jquery',
			'pp-product-tabs',
			'wc-single-product',
		);
	}

	/**
	 * Retrieve the list of styles the Woo - Product Tabs depended on.
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
	 * Register Woo - Product Tabs widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @access protected
	 */
	protected function register_controls() {

		// Content Tab
		$this->register_content_tab_controls();
		$this->register_content_layout_controls();
		$this->register_content_other_controls();

		// Style Tab

		$this->register_style_controls();

	}

	/**
	 * Register Content Tab - Tabs Section controls.
	 *
	 * Registers Tabs section controls under the Content Tab.
	 *
	 * @access protected
	 */

	protected function register_content_tab_controls() {

		$this->start_controls_section(
			'woo-product-tabs__general-section',
			array(
				'label' => __( 'Tabs', 'powerpack' ),
			)
		);

		$repeater = new Repeater();

		$repeater->start_controls_tabs( 'tabs_at' );

		$repeater->start_controls_tab(
			'product_tabs_content',
			array(
				'label' => __( 'Content', 'powerpack' ),
			)
		);

		$repeater->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'powerpack' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Tab', 'powerpack' ),
				'description' => 'This title will appear in the Tab Title.',
			)
		);

		$repeater->add_control(
			'name',
			array(
				'label'   => __( 'Tab', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'description',
				'options' => array(
					'description'            => __( 'Description', 'powerpack' ),
					'reviews'                => __( 'Reviews', 'powerpack' ),
					'additional_information' => __( 'Additional Information', 'powerpack' ),
					'custom'                 => __( 'Custom Tab', 'powerpack' ),
				),
			)
		);

		$repeater->add_control(
			'custom_tab_type',
			array(
				'label'     => __( 'Content', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'custom',
				'options'   => array(
					'custom'         => __( 'Custom', 'powerpack' ),
					'saved_template' => __( 'Saved Template', 'powerpack' ),
				),
				'condition' => array(
					'name' => 'custom',
				),
			)
		);

		$repeater->add_control(
			'custom_content',
			array(
				'label'       => __( 'Custom Content', 'powerpack' ),
				'type'        => Controls_Manager::WYSIWYG,
				'default'     => __( 'Add custom content, shortcodes, and more.', 'powerpack' ),
				'placeholder' => __( 'Placeholder', 'powerpack' ),
				'condition'   => array(
					'custom_tab_type' => 'custom',
					'name'            => 'custom',
				),
			)
		);

		$repeater->add_control(
			'template_content',
			array(
				'label'      => __( 'Choose Template', 'powerpack' ),
				'type'       => 'pp-query',
				'multiple'   => false,
				'query_type' => 'templates',
				'condition'  => array(
					'custom_tab_type' => 'saved_template',
				),
			)
		);

		$repeater->end_controls_tab();

		$repeater->start_controls_tab(
			'product_tabs_icon',
			array(
				'label' => __( 'Icon', 'powerpack' ),
			)
		);

		$repeater->add_control(
			'product_tab_icon',
			array(
				'label'            => __( 'Icon', 'powerpack' ),
				'type'             => Controls_Manager::ICONS,
				'label_block'      => true,
				'default'          => array(
					'value'   => 'fas fa-check',
					'library' => 'fa-solid',
				),
				'fa4compatibility' => 'icon',
			)
		);

		$repeater->end_controls_tab();

		$repeater->end_controls_tab();

		$this->add_control(
			'product_tabs--repeater-section',
			array(
				'label'              => '',
				'type'               => Controls_Manager::REPEATER,
				'fields'             => $repeater->get_controls(),
				'default'            => array(
					array(
						'name'  => 'description',
						'title' => 'Description',
					),
					array(
						'name'  => 'reviews',
						'title' => 'Reviews',
					),
					array(
						'name'  => 'additional_information',
						'title' => 'Additional Information',
					),
				),
				'title_field'        => '
					<# 
						var t = "";
						if( "" !== title ){
							t = title;
						} else {
							t = name.replace(/_/g, " ").toUpperCase();
							 
						}
					
					#>{{{ t }}}
				',
				'frontend_available' => true,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Content Tab - Layout Section controls.
	 *
	 * Registers Layout section controls under the Content Tab.
	 *
	 * @access protected
	 */

	protected function register_content_layout_controls() {

		$this->start_controls_section(
			'woo-products-tabs__section-layout',
			array(
				'label' => __( 'Layout', 'powerpack' ),
			)
		);

		$this->add_control(
			'woo_product_tabs__tab_layout',
			array(
				'label'              => __( 'Tab Layout', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'options'            => array(
					'wpt-horizontal' => __( 'Horizontal', 'powerpack' ),
					'wpt-vertical'   => __( 'Vertical', 'powerpack' ),
				),
				'default'            => 'wpt-horizontal',
				'frontend_available' => true,
			)
		);

		$this->add_responsive_control(
			'woo-product-tabs__tab-position-horizontal',
			array(
				'label'     => __( 'Tab Position', 'powerpack' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'column'         => array(
						'title' => __( 'top', 'powerpack' ),
						'icon'  => 'eicon-v-align-top',
					),
					'column-reverse' => array(
						'title' => __( 'bottom', 'powerpack' ),
						'icon'  => 'eicon-v-align-bottom',
					),
				),
				'default'   => 'column',
				'selectors' => array(
					'{{WRAPPER}} div.pp-woo-product-tabs-wrapper div.woocommerce-tabs.wc-tabs-wrapper.wpt-horizontal' => 'flex-direction: {{VALUE}};',
				),
				'condition' => array(
					'woo_product_tabs__tab_layout' => 'wpt-horizontal',
				),
			)
		);

		$this->add_responsive_control(
			'woo-product-tabs__tab-position-vertical',
			array(
				'label'     => __( 'Tab Position', 'powerpack' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'row'         => array(
						'title' => __( 'left', 'powerpack' ),
						'icon'  => 'eicon-h-align-left',
					),
					'row-reverse' => array(
						'title' => __( 'right', 'powerpack' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'default'   => 'row',
				'selectors' => array(
					'{{WRAPPER}} div.pp-woo-product-tabs-wrapper div.woocommerce-tabs.wc-tabs-wrapper.wpt-vertical' => 'flex-direction: {{VALUE}};',
				),
				'condition' => array(
					'woo_product_tabs__tab_layout' => 'wpt-vertical',
				),
			)
		);

		$this->add_responsive_control(
			'woo-products-tabs__panel-width',
			array(
				'label'      => __( 'Content Panel Width', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 1000,
					),
				),
				'default'    => array(
					'unit' => '%',
					'size' => 80,
				),
				'selectors'  => array(
					'{{WRAPPER}} .wc-tabs.tabs' => 'width: {{SIZE}}{{UNIT}};',
				),
				'responsive' => true,
				'condition'  => array(
					'woo_product_tabs__tab_layout' => 'wpt-vertical',
				),
			)
		);

		$this->add_control(
			'woo-product-tabs__tab-style',
			array(
				'label'              => __( 'Tab Style', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'options'            => array(
					'title'      => __( 'Title', 'powerpack' ),
					'icon'       => __( 'Icon', 'powerpack' ),
					'icon_title' => __( 'Icon + Title', 'powerpack' ),
				),
				'default'            => 'icon_title',
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'woo-product-tabs__active-tab-indicator-section',
			array(
				'label'       => __( 'Active Tab Indicator', 'powerpack' ),
				'type'        => Controls_Manager::HEADING,
				'description' => 'Set the active and hover tab indicator for the tabs.',
				'separator'   => 'before',
			)
		);

		$this->add_responsive_control(
			'woo_products_tab__active_tab_indicator_horizontal',
			array(
				'label'              => __( 'Active Tab Indicator', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'options'            => array(
					'none'      => __( 'None', 'powerpack' ),
					'underline' => __( 'Underline', 'powerpack' ),
					'icon'      => __( 'Icon', 'powerpack' ),
					'border'    => __( 'Border', 'powerpack' ),
				),
				'default'            => 'underline',
				'frontend_available' => true,
				'condition'          => array(
					'woo_product_tabs__tab_layout' => 'wpt-horizontal',
				),
			)
		);

		$this->add_responsive_control(
			'woo_products_tab__active_tab_indicator_vertical',
			array(
				'label'              => __( 'Active Tab Indicator', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'options'            => array(
					'none'   => __( 'None', 'powerpack' ),
					'icon'   => __( 'Icon', 'powerpack' ),
					'border' => __( 'Border', 'powerpack' ),
				),
				'default'            => 'border',
				'frontend_available' => true,
				'condition'          => array(
					'woo_product_tabs__tab_layout' => 'wpt-vertical',
				),
			)
		);

		$this->add_responsive_control(
			'woo-products-tab__active-tab-indicator-position-horizontal',
			array(
				'label'              => __( 'Position', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'options'            => array(
					'indicator-top'    => __( 'Top', 'powerpack' ),
					'indicator-bottom' => __( 'Bottom', 'powerpack' ),
				),
				'default'            => 'indicator-bottom',
				'frontend_available' => true,
				'condition'          => array(
					'woo_product_tabs__tab_layout' => 'wpt-horizontal',
				),
			)
		);

		$this->add_responsive_control(
			'woo_products_tab__active_tab_indicator_position_vertical',
			array(
				'label'              => __( 'Position', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'options'            => array(
					'indicator-right' => __( 'Right', 'powerpack' ),
					'indicator-left'  => __( 'Left', 'powerpack' ),
				),
				'default'            => 'indicator-right',
				'frontend_available' => true,
				'condition'          => array(
					'woo_product_tabs__tab_layout' => 'wpt-vertical',
					'woo_products_tab__active_tab_indicator_vertical' => 'icon',
				),
			)
		);

		/**
		 * Start - Normal and Hover Tab section for Active Icon Indicator - Border
		 */

		$this->start_controls_tabs(
			'acitve_tab_indicator__border',
			array(
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'woo_products_tab__active_tab_indicator_horizontal',
									'operator' => '==',
									'value'    => 'border',
								),
								array(
									'name'     => 'woo_product_tabs__tab_layout',
									'operator' => '==',
									'value'    => 'wpt-horizontal',
								),
							),
						),
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'woo_products_tab__active_tab_indicator_vertical',
									'operator' => '==',
									'value'    => 'border',
								),
								array(
									'name'     => 'woo_product_tabs__tab_layout',
									'operator' => '==',
									'value'    => 'wpt-vertical',
								),
							),
						),
					),
				),
			)
		);

		$this->start_controls_tab(
			'active-tab-indicator__border-normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'woo-product-tabs__active-tab-indicator-border-normal',
				'label'     => __( 'Border', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .woocommerce-tabs ul.tabs.wc-tabs li a',
				'separator' => 'before',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'active-tab-indicator__border-active',
			array(
				'label' => __( 'Hover/Active', 'powerpack' ),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'woo-product-tabs__active-tab-indicator-border-active',
				'label'     => __( 'Border', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .woocommerce-tabs ul.tabs.wc-tabs li.active a,
								{{WRAPPER}} .woocommece-tabs ul.tabs.wc-tabs li.hover a',
				'separator' => 'before',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs(); // End Tab section for Active Icon Indicator Border

		$this->end_controls_section();

	}

	/**
	 * Register Content Tab - Other Section controls.
	 *
	 * Registers Other controls under the Content Tab.
	 *
	 * @access protected
	 */

	protected function register_content_other_controls() {

		$this->start_controls_section(
			'woo-product-tabs__section-other-controls',
			array(
				'label' => __( 'Other', 'powerpack' ),
			)
		);

		$this->add_control(
			'woo-product-tabs__responsive_support',
			array(
				'label'   => __( 'Responsive Support', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'tablet',
				'options' => array(
					'no'     => __( 'No', 'powerpack' ),
					'tablet' => __( 'For Tablet & Mobile', 'powerpack' ),
					'mobile' => __( 'For Mobile Only', 'powerpack' ),
				),
			)
		);

		$this->add_control(
			'woo-product-tabs__default-tab',
			array(
				'label'              => __( 'Default Active Tab Index', 'powerpack' ),
				'type'               => Controls_Manager::NUMBER,
				'label_block'        => true,
				'default'            => 1,
				'placeholder'        => __( 'Default Active Tab Index', 'powerpack' ),
				'frontend_available' => true,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Style Tab Controls.
	 *
	 * Registers controls for sections under the Style Tab.
	 *
	 * @access protected
	 */

	protected function register_style_controls() {

		$this->register_tab_style_controls();
		$this->register_indicator_style_controls();
		$this->register_title_style_controls();
		$this->register_content_style_controls();

	}

	/**
	 * Register Style Tab - Tab Style Section controls.
	 *
	 * Registers Tab Style section controls under the Style Tab.
	 *
	 * @access protected
	 */

	protected function register_tab_style_controls() {

		/**
		 * Style Tab: Tabs
		 */
		$this->start_controls_section(
			'woo-products-tab__tab-style',
			array(
				'label' => __( 'Tabs', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'product_tab_horizontal_alignment',
			array(
				'label'     => __( 'Horizontal Alignment', 'powerpack' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center'     => array(
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'eicon-h-align-center',
					),
					'flex-end'   => array(
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} div.pp-woo-product-tabs-wrapper div.woocommerce-tabs.wc-tabs-wrapper.wpt-horizontal ul.tabs.wc-tabs' => 'justify-content: {{VALUE}};',
				),
				'condition' => array(
					'woo_product_tabs__tab_layout' => 'wpt-horizontal',
				),
			)
		);

		$this->add_responsive_control(
			'product_tab_vertical_alignment',
			array(
				'label'     => __( 'Vertical Alignment', 'powerpack' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Top', 'powerpack' ),
						'icon'  => 'eicon-v-align-top',
					),
					'center'     => array(
						'title' => __( 'Middle', 'powerpack' ),
						'icon'  => 'eicon-v-align-middle',
					),
					'flex-end'   => array(
						'title' => __( 'Bottom', 'powerpack' ),
						'icon'  => 'eicon-v-align-bottom',
					),
				),
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} div.pp-woo-product-tabs-wrapper div.woocommerce-tabs.wc-tabs-wrapper.wpt-vertical ul.tabs.wc-tabs' => 'justify-content: {{VALUE}};',
				),
				'condition' => array(
					'woo_product_tabs__tab_layout' => 'wpt-vertical',
				),
			)
		);

		$this->add_responsive_control(
			'woo-product-tabs_tab-spacing',
			array(
				'label'      => __( 'Space Between Tabs', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array(
					'size' => 0,
				),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 500,
						'step' => 1,
					),
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce-tabs.wpt-horizontal .wc-tabs li:not(:last-child)' => 'margin-right: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .woocommerce-tabs.wpt-vertical .wc-tabs li:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .at-horizontal-content .pp-advanced-tabs-title:not(:first-child)' => 'margin-top: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .at-vertical .pp-advanced-tabs-title:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}}',
					'(tablet){{WRAPPER}} .pp-tabs-responsive-tablet .pp-tabs-panel:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}}',
					'(mobile){{WRAPPER}} .pp-tabs-responsive-mobile .pp-tabs-panel:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'woo-product-tabs__tab-panel-space-top',
			array(
				'label'      => __( 'Space Between Tabs & Panel', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array(
					'size' => 0,
				),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce-tabs.wpt-horizontal .tabs.wc-tabs' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				),
				'separator'  => 'after',
				'condition'  => array(
					'woo-product-tabs__tab-position-horizontal' => 'column',
					'woo_product_tabs__tab_layout' => 'wpt-horizontal',
				),
			)
		);

		$this->add_responsive_control(
			'woo-product-tabs__tab-panel-space-bottom',
			array(
				'label'      => __( 'Space Between Tabs & Panel', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array(
					'size' => 0,
				),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce-tabs.wpt-horizontal .tabs.wc-tabs' => 'margin-top: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'woo-product-tabs__tab-position-horizontal' => 'column-reverse',
					'woo_product_tabs__tab_layout' => 'wpt-horizontal',
				),
			)
		);

		$this->add_responsive_control(
			'woo-product-tabs__tab-panel-space-left',
			array(
				'label'      => __( 'Space Between Tabs & Panel', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array(
					'size' => 0,
				),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce-tabs.wpt-vertical .tabs.wc-tabs' => 'margin-right: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'woo-product-tabs__tab-position-vertical' => 'row',
					'woo_product_tabs__tab_layout' => 'wpt-vertical',
				),
			)
		);

		$this->add_responsive_control(
			'woo-product-tabs__tab-panel-space-right',
			array(
				'label'      => __( 'Space Between Tabs & Panel', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array(
					'size' => 0,
				),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce-tabs.wpt-vertical .tabs.wc-tabs' => 'margin-left: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'woo-product-tabs__tab-position-vertical' => 'row-reverse',
					'woo_product_tabs__tab_layout' => 'wpt-vertical',
				),
			)
		);

		$this->add_control(
			'woo_product_tabs_tab_size_toggle',
			array(
				'label'        => __( 'Size', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'separator'    => 'before',
			)
		);

		$this->add_responsive_control(
			'woo_product_tabs_tab_width',
			array(
				'label'      => __( 'Width', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array(
					'size' => 200,
				),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 300,
						'step' => 20,
					),
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce-tabs.wpt-horizontal .tabs.wc-tabs li a' => 'width: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'woo_product_tabs_tab_size_toggle' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'woo_product_tabs_tab_height',
			array(
				'label'      => __( 'Height', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array(
					'size' => 60,
				),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 300,
						'step' => 20,
					),
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce-tabs.wpt-horizontal .tabs.wc-tabs li a' => 'height: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'woo_product_tabs_tab_size_toggle' => 'yes',
				),
			)
		);

		$this->add_control(
			'woo-product-tabs__content-alignment-heading',
			array(
				'label'     => __( 'Content Alignment', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'woo-product-tabs__tab-content-alignment-top-bottom',
			array(
				'label'     => __( 'Horizontal', 'powerpack' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center'     => array(
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'eicon-h-align-center',
					),
					'flex-end'   => array(
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .woocommerce-tabs .tabs.wc-tabs li a' => 'align-items: {{VALUE}};', // column
					'{{WRAPPER}} .woocommerce-tabs .tabs.wc-tabs li a' => 'justify-content: {{VALUE}};', // row
				),
			)
		);

		$this->add_responsive_control(
			'woo-product-tabs__tab-content-alignment-left-right',
			array(
				'label'     => __( 'Vertical', 'powerpack' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Top', 'powerpack' ),
						'icon'  => 'eicon-v-align-top',
					),
					'center'     => array(
						'title' => __( 'Middle', 'powerpack' ),
						'icon'  => 'eicon-v-align-middle',
					),
					'flex-end'   => array(
						'title' => __( 'Bottom', 'powerpack' ),
						'icon'  => 'eicon-v-align-bottom',
					),
				),
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .woocommerce-tabs .tabs.wc-tabs li a' => 'justify-content: {{VALUE}};', // column
					'{{WRAPPER}} .woocommerce-tabs .tabs.wc-tabs li a' => 'align-items: {{VALUE}};', // row
				),
				'separator' => 'after',
			)
		);

		$this->end_controls_section();

	}

	/**
	 * Register Style Tab - Active Tab Indicator Style Section controls.
	 *
	 * Registers Active Tab Indicator Style section controls under the Style Tab.
	 *
	 * @access protected
	 */

	protected function register_indicator_style_controls() {

		$this->start_controls_section(
			'woo-products-tab__indicator-style-section',
			array(
				'label' => __( 'Indicator', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->start_controls_tabs( 'woo-product-tabs__indicator-style' );

		$this->start_controls_tab(
			'indicator-style-normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'woo-products-tab_indicator-size',
			array(
				'label'      => __( 'Size', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.icon li.active::after' => 'font-size: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.icon li.active::before' => 'font-size: {{SIZE}}{{UNIT}}',
				),
				'responsive' => true,
				'condition'  => array(
					'woo-products-tab__active-tab-indicator' => 'icon',
				),
			)
		);

		$this->add_control(
			'woo-products-tab_indicator-height',
			array(
				'label'      => __( 'Height', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.underline li.active::before' => 'height: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.underline li.active::after' => 'height: {{SIZE}}{{UNIT}}',
				),
				'responsive' => true,
				'condition'  => array(
					'woo-products-tab__active-tab-indicator' => 'underline',
				),
			)
		);

		$this->add_control(
			'woo-products-tab_indicator-width',
			array(
				'label'      => __( 'Width', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
					'vw' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => '%',
					'size' => 100,
				),
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.underline li.active::before' => 'width: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.underline li.active::after' => 'width: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'woo-products-tab__active-tab-indicator' => 'underline',
				),
			)
		);

		$this->add_control(
			'woo-product-tabs__indicator-spacing',
			array(
				'label'     => __( 'Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'selectors' => array(
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.icon.indicator-bottom li.active::after' => 'bottom: -{{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.icon.indicator-top li.active::before' => 'top: -{{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.underline li.active::after' => 'bottom: -{{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.underline li.active::before' => 'top: -{{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.icon.indicator-left li.active::before' => 'left: -{{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.icon.indicator-right li.active::after' => 'right: -{{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_control(
			'woo-products-tab_indicator-color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#808080',
				'selectors' => array(
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.icon li::before' => 'color: {{VALUE}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.icon li::after' => 'color: {{VALUE}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.underline li::before' => 'background-color: {{VALUE}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.underline li::after' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab(); // End normal indicator style

		$this->start_controls_tab(
			'indicator-style-active',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'woo-products-tab_indicator-size-hover',
			array(
				'label'      => __( 'Size', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.icon li.hover::after' => 'font-size: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.icon li.hover::before' => 'font-size: {{SIZE}}{{UNIT}}',
				),
				'responsive' => true,
				'condition'  => array(
					'woo-products-tab__active-tab-indicator' => 'icon',
				),
			)
		);

		$this->add_control(
			'woo-products-tab_indicator-height-hover',
			array(
				'label'      => __( 'Height', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.underline li.hover::before' => 'height: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.underline li.hover::after' => 'height: {{SIZE}}{{UNIT}}',
				),
				'responsive' => true,
				'condition'  => array(
					'woo-products-tab__active-tab-indicator' => 'underline',
				),
			)
		);

		$this->add_control(
			'woo-products-tab_indicator-width-hover',
			array(
				'label'      => __( 'Width', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
					'vw' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => '%',
					'size' => 100,
				),
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.underline li.hover::before' => 'width: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.underline li.hover::after' => 'width: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'woo-products-tab__active-tab-indicator' => 'underline',
				),
			)
		);

		$this->add_control(
			'woo-product-tabs__indicator-spacing-hover',
			array(
				'label'     => __( 'Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'selectors' => array(
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.icon.indicator-bottom li.hover::after' => 'bottom: -{{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.icon.indicator-top li.hover::before' => 'top: -{{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.underline li.hover::after' => 'bottom: -{{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.underline li.hover::before' => 'top: -{{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.icon.indicator-left li.hover::before' => 'left: -{{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.icon.indicator-right li.hover::after' => 'right: -{{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_control(
			'woo-products-tab_indicator-color-hover',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#808080',
				'selectors' => array(
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.icon li.hover::before' => 'color: {{VALUE}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.icon li.hover::after' => 'color: {{VALUE}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.underline li.hover::before' => 'background-color: {{VALUE}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.underline li.hover::after' => 'background-color: {{VALUE}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.icon li.active::before' => 'color: {{VALUE}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.icon li.active::after' => 'color: {{VALUE}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.underline li.active::before' => 'background-color: {{VALUE}}',
					'{{WRAPPER}} .woocommerce-tabs ul.tabs.underline li.active::after' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab(); // End Hover styles

		$this->end_controls_tabs(); // End Indicator style tabs

		$this->end_controls_section();

	}

	/**
	 * Register Style Tab - Title Section controls.
	 *
	 * Registers Title Style section controls under the Style Tab.
	 *
	 * @access protected
	 */

	protected function register_title_style_controls() {

		$this->start_controls_section(
			'woo-products-tab__title-style',
			array(
				'label' => __( 'Title', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'woo-products-tab__title-typography',
				'selector' => '
					{{WRAPPER}} .wc-tabs li a',
			)
		);

		$this->add_control(
			'woo-product-tabs__icon-position',
			array(
				'label'              => __( 'Icon Position', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'options'            => array(
					'column'         => __( 'Top', 'powerpack' ),
					'column-reverse' => __( 'Bottom', 'powerpack' ),
					'row'            => __( 'Left', 'powerpack' ),
					'row-reverse'    => __( 'Right', 'powerpack' ),
				),
				'default'            => 'row',
				'frontend_available' => true,
			)
		);

		$this->add_responsive_control(
			'woo-product-tabs__icon-size',
			array(
				'label'      => __( 'Icon Size', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array(
					'size' => 15,
				),
				'range'      => array(
					'px' => array(
						'min'  => 1,
						'max'  => 100,
						'step' => 1,
					),
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .wc-tabs li a .pp-icon' => 'font-size: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'woo-product-tabs__icon-spacing',
			array(
				'label'      => __( 'Spacing', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array(
					'size' => 10,
				),
				'range'      => array(
					'px' => array(
						'min'  => 1,
						'max'  => 100,
						'step' => 1,
					),
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .wc-tabs.row li a .pp-icon' => 'margin-right: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .wc-tabs.column li a .pp-icon' => 'margin-bottom: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .wc-tabs.row-reverse li a .pp-icon' => 'margin-left: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .wc-tabs.column-reverse li a .pp-icon' => 'margin-top: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->start_controls_tabs( 'woo-product-tabs__title-style' );

		$this->start_controls_tab(
			'tab_title-normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'woo-products-tab__title-color',
			array(
				'label'     => __( 'Title Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#808080',
				'selectors' => array(
					'{{WRAPPER}} .woocommerce-tabs .tabs.wc-tabs li a' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'woo-products-tab__title-bg-color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .woocommerce-tabs .wc-tabs li a' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'woo-products-tab__icon-color',
			array(
				'label'     => __( 'Icon Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#808080',
				'selectors' => array(
					'{{WRAPPER}} .wc-tabs li a .pp-icon' => 'color: {{VALUE}}',
					'{{WRAPPER}} .wc-tabs li a svg'      => 'fill: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'woo-product-tabs__tab-box-shadow',
				'label'    => __( 'Box Shadow', 'powerpack' ),
				'selector' => '{{WRAPPER}} .wc-tabs li a',
			)
		);

		$this->end_controls_tab(); // End Normal Tab

		$this->start_controls_tab(
			'tab_title-active',
			array(
				'label' => __( 'Hover / Active', 'powerpack' ),
			)
		);

		$this->add_control(
			'woo-products-tab__title-color-active',
			array(
				'label'     => __( 'Title Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .woocommerce-tabs .tabs.wc-tabs li.active a' => 'color: {{VALUE}}',
					'{{WRAPPER}} .woocommerce-tabs .tabs.wc-tabs li a:hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'woo-products-tab__title-bg-color-active',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#000000',
				'selectors' => array(
					'{{WRAPPER}} .woocommerce-tabs .wc-tabs li.active a' => 'background-color: {{VALUE}}',
					'{{WRAPPER}} .woocommerce-tabs .wc-tabs li a:hover' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'woo-products-tab__icon-color-active',
			array(
				'label'     => __( 'Icon Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#000000',
				'selectors' => array(
					'{{WRAPPER}} .wc-tabs li.active a .pp-icon' => 'color: {{VALUE}}',
					'{{WRAPPER}} .wc-tabs li.active a svg' => 'fill: {{VALUE}}',
					'{{WRAPPER}} .wc-tabs li a:hover .pp-icon' => 'color: {{VALUE}}',
					'{{WRAPPER}} .wc-tabs li a:hover svg'  => 'fill: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'woo-products-tab__tab-border-color-active',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#000000',
				'selectors' => array(
					'{{WRAPPER}} div.woocommerce-tabs ul.wc-tabs li.active a' => 'border-color: {{VALUE}}',
					'{{WRAPPER}} div.woocommerce-tabs ul.wc-tabs li.hover a:hover' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'woo-product-tabs__tab-box-shadow-active',
				'label'    => __( 'Box Shadow', 'powerpack' ),
				'selector' => '{{WRAPPER}} .wc-tabs li a:hover,
							   {{WRAPPER}} .wc-tabs li a:active',
			)
		);

		$this->end_controls_tab(); // End Hover Tab

		$this->end_controls_tabs(); // End Controls Tab

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'woo-product-tabs__tab-border',
				'label'     => esc_html__( 'Border', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .woocommerce-tabs ul.tabs.wc-tabs li a',
				'separator' => 'before',
			)
		);

		$this->add_control(
			'woo-product-tabs__tab-border-radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'default'    => array(
					'top'    => 0,
					'bottom' => 0,
					'left'   => 0,
					'right'  => 0,
				),
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce-tabs ul.wc-tabs li a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'woo-products-tab__tab-padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'default'    => array(
					'top'    => 10,
					'bottom' => 10,
					'left'   => 10,
					'right'  => 10,
				),
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce-tabs .wc-tabs li a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->end_controls_section();

	}

	/**
	 * Register Style Tab - Tab Content Section controls.
	 *
	 * Registers Content Style section controls under the Style Tab.
	 *
	 * @access protected
	 */

	protected function register_content_style_controls() {

		$this->start_controls_section(
			'woo-products-tab__content-style',
			array(
				'label' => __( 'Content', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'woo-product-tabs__content-align',
			array(
				'label'     => __( 'Alignment', 'powerpack' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'start'  => array(
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'eicon-text-align-center',
					),
					'end'    => array(
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => 'center',
				'separator' => 'after',
				'selectors' => array(
					'{{WRAPPER}} div.pp-woo-product-tabs-wrapper div.woocommerce-tabs div.woocommerce-Tabs-panel.panel.wc-tab' => 'text-align: {{VALUE}};',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'woo-product-tabs__content-background',
				'label'    => __( 'Background', 'powerpack' ),
				'types'    => array( 'none', 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} div.pp-woo-product-tabs-wrapper div.woocommerce-tabs div.woocommerce-Tabs-panel.panel.wc-tab',
			)
		);
		$this->add_control(
			'woo-product-tabs__text-color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#808080',
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}} div.pp-woo-product-tabs-wrapper div.woocommerce-tabs div.woocommerce-Tabs-panel.panel.wc-tab,
					{{WRAPPER}} div.pp-woo-product-tabs-wrapper div.woocommerce-tabs div.woocommerce-Tabs-panel.panel.wc-tab h2,
					{{WRAPPER}} div.pp-woo-product-tabs-wrapper div.woocommerce-tabs div.woocommerce-Tabs-panel.panel.wc-tab h3,
					{{WRAPPER}} div.pp-woo-product-tabs-wrapper div.woocommerce-tabs div.woocommerce-Tabs-panel.panel.wc-tab h4,
					{{WRAPPER}} div.pp-woo-product-tabs-wrapper div.woocommerce-tabs div.woocommerce-Tabs-panel.panel.wc-tab h5,
					{{WRAPPER}} div.pp-woo-product-tabs-wrapper div.woocommerce-tabs div.woocommerce-Tabs-panel.panel.wc-tab span,
					{{WRAPPER}} div.pp-woo-product-tabs-wrapper div.woocommerce-tabs div.woocommerce-Tabs-panel.panel.wc-tab p' => 'color: {{VALUE}}',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'woo-product-tabs__content-typography',
				'label'    => __( 'Text Typography', 'powerpack' ),
				'scheme'   => Scheme_Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} div.pp-woo-product-tabs-wrapper div.woocommerce-tabs div.woocommerce-Tabs-panel.panel.wc-tab',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'woo-product-tabs__tab-content-border',
				'label'    => esc_html__( 'Border', 'powerpack' ),
				'selector' => '{{WRAPPER}} .woocommerce-tabs .woocommerce-Tabs-panel.panel.wc-tab',
			)
		);

		$this->add_control(
			'woo-product-tabs__content-border-radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'separator'  => 'before',
				'default'    => array(
					'top'    => 0,
					'bottom' => 0,
					'left'   => 0,
					'right'  => 0,
				),
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce-tabs .woocommerce-Tabs-panel.panel.wc-tab' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'woo-product-tabs__content-tab-padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'default'    => array(
					'top'    => 10,
					'bottom' => 10,
					'left'   => 10,
					'right'  => 10,
				),
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce-tabs .woocommerce-Tabs-panel.panel.wc-tab' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'woo-product-tabs__content-box-shadow',
				'label'    => __( 'Box Shadow', 'powerpack' ),
				'selector' => '{{WRAPPER}} .woocommerce-tabs .panel.woocommerce-Tabs-panel',
			)
		);

		$this->end_controls_section();

	}

	/**
	 * Adds content to the custom tabs
	 *
	 * Add content to the custom tab based on the type of tab custom or saved_template
	 *
	 * @since 2.1.0
	 *
	 * @param string $id Slug of the tab.
	 *
	 * @return void
	 */

	public function fill_content( $id ) {

		// Get settings for display
			$settings = $this->get_settings_for_display( 'product_tabs--repeater-section' );

		// Process & Display the data

		foreach ( $settings as $i => $item ) {

			if ( $item['_id'] === $id && 'custom' === $item['custom_tab_type'] ) {

				echo $this->parse_text_editor( $item['custom_content'] ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			} elseif ( $item['_id'] === $id && 'saved_template' === $item['custom_tab_type'] ) {

				if ( ! empty( $item['template_content'] ) ) {

					echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $item['template_content'] ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

				} else {

					echo esc_attr__( 'Please select a template.', 'powerpack' );
				}
			}
		}
	}

	/**
	 * Fetch the Tab's Title
	 *
	 * @since 2.1.0
	 *
	 * @param string $tab slug of the tab.
	 *
	 * @return string $title Title of the Tab.
	 */

	protected function get_tab_title( $tab ) {

		global $product;
		$review_count = $product->get_review_count();

		$title = '';

		switch ( $tab ) {

			case 'description':
				$title = 'Description';
				break;
			case 'additional_information':
				$title = 'Additional Information';
				break;
			case 'reviews':
				$title = sprintf( __( 'Reviews (%d)', 'powerpack' ), $product->get_review_count() );
				break;
			default:
				$title = 'Custom Tab';
				break;
		}

		return $title;

	}

	/**
	 * Fetch the Tab's from repeater field
	 *
	 * @since 2.1.0
	 *
	 * @return array $new_tabs Array of Tabs.
	 */

	protected function get_tabs() {

		// Store Tabs
		$new_tabs = array();

		// Get Settings
		$settings = $this->get_settings_for_display();

		// Set a base priority to 10.
		// Priority will increment with loop in order so that tabs are arranged automatically.

		$priority = 10;

		// Get tabs from repeater fields

		foreach ( $settings['product_tabs--repeater-section'] as $index => $item ) {

			// print_r( $item );

			$new_tabs[ $item['_id'] ] = array(

				'title'    => ! empty( $item['title'] ) ? $item['title'] : $this->get_tab_title( $item['name'] ),
				'priority' => $priority,
				'callback' => '',
			);

			$priority += 10;
		}

		// Return the updated tab list
		return $new_tabs;
	}

	/**
	 * Fetch the Tab's slug name
	 *
	 * @since 2.1.0
	 *
	 * @param string $id slug of the id.
	 *
	 * @return string Slug of the Tab.
	 */

	protected function get_tab_name( $id ) {

		$settings = $this->get_settings_for_display();

		// get settings of tabs
		foreach ( $settings['product_tabs--repeater-section'] as $index => $item ) {

			if ( $item['_id'] === $id ) {
				return $item['name'];
			}
		}
	}

	/**
	 * Fetch the correct callback function for the tab
	 *
	 * @since 2.1.0
	 *
	 * @param string $id slug of the id.
	 *
	 * @return string $callback Name of the callback function for the Tab.
	 */

	protected function get_callback_function( $id ) {

		$tab = $this->get_tab_name( $id );

		// Callback function
		$callback = '';
		switch ( $tab ) {

			case 'description':
				$callback = 'woocommerce_product_description_tab';
				break;
			case 'additional_information':
				$callback = 'woocommerce_product_additional_information_tab';
				break;
			case 'reviews':
				$callback = 'comments_template';
				break;
			case 'custom':
				$callback = array( $this, 'fill_content' );
				break;
			default:
				echo 'Please select a valid option.';
				break;
		}

		return $callback;

	}

	/**
	 * Add the tabs to the WooCommerce global $tabs variable.
	 *
	 * @since 2.1.0
	 *
	 * @param array $tabs Array of tabs.
	 *
	 * @return array $tabs Modified array of tabs.
	 */

	public function add_tabs( $tabs ) {

		// Get the array of tabs from repeater field

		$new_tabs = $this->get_tabs();

		if ( empty( $new_tabs ) ) {
			return;
		}

		// Reset $tabs
		$tabs = array();

		// Add callback to each tab

		foreach ( $new_tabs as $id => $item ) {

			$new_tabs[ $id ]['callback'] = $this->get_callback_function( $id );

		}

		// Add new tabs to the list
		$tabs = $new_tabs;

		return $tabs;
	}

	/**
	 * Render the data on frontend.
	 *
	 * @since 2.1.0
	 *
	 * @return void
	 */

	protected function render() {
		$settings = $this->get_settings_for_display();

		do_action( 'pp_woo_builder_widget_before_render', $this );

		global $product;

		$product = wc_get_product();

		if ( Plugin::instance()->editor->is_edit_mode() ) {
			echo wp_kses_post( PP_Woo_Helper::get_instance()->default( $this->get_name(), $settings, $this ) );
		} else {
			if ( empty( $product ) ) {
				return;
			}

			// setup_postdata( $product->get_id() );

			add_filter( 'woocommerce_product_tabs', array( $this, 'add_tabs' ) );

			echo "<div class='pp-woo-product-tabs-wrapper'>";

			wc_get_template( 'single-product/tabs/tabs.php' );

			// On render widget from Editor - trigger the init manually.
			if ( wp_doing_ajax() ) {
				?>
				<script>
					jQuery( '.wc-tabs-wrapper, .woocommerce-tabs, #rating' ).trigger( 'init' );
				</script>
				<?php
			}

			// Remove filter.
			remove_filter( 'woocommerce_product_tabs', array( $this, 'add_tabs' ) );
		}

		do_action( 'pp_woo_builder_widget_after_render', $this );
	}
}
