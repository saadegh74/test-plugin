<?php
/**
 * PowerPack WooCommerce Checkout widget.
 *
 * @package PowerPack
 */

namespace PowerpackElements\Modules\Woocommerce\Widgets;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Classes\PP_Config;
use PowerpackElements\Classes\PP_Helper;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Core\Schemes\Typography as Scheme_Typography;
use Elementor\Core\Schemes\Color as Scheme_Color;

if ( ! defined( 'ABSPATH' ) ) {
	exit;   // Exit if accessed directly.
}

/**
 * Woo - Checkout widget
 */
class Woo_Checkout extends Woo_Base_Widget {

	private $reformatted_form_fields;

	/**
	 * Retrieve Woo - Checkout widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Woo_Checkout' );
	}

	/**
	 * Retrieve Woo - Checkout widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Woo_Checkout' );
	}

	/**
	 * Retrieve Woo - Checkout widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Woo_Checkout' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the Woo - Checkout widget belongs to.
	 *
	 * @since 1.4.13.1
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'Woo_Checkout' );
	}

	/**
	 * Retrieve the list of styles the Woo - Checkout depended on.
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
	 * Register Woo - Checkout widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 2.0.3
	 * @access protected
	 */
	protected function register_controls() {
		/* Product Control */
		$this->register_content_general_controls();

		if ( $this->is_wc_feature_active( 'checkout_login_reminder' ) ) {
			$this->register_content_checkout_login_reminder_controls();
		}

		/* Billing Details */
		$this->register_content_billing_details();

		if ( $this->is_wc_feature_active( 'shipping' ) && ! $this->is_wc_feature_active( 'ship_to_billing_address_only' ) ) {
			$this->register_content_shipping_details();
		}

		/* Additional Information */
		$this->register_content_additional_information();

		if ( $this->is_wc_feature_active( 'signup_and_login_from_checkout' ) ) {
			$this->register_content_signup_and_login_from_checkout_controls();
		}

		/* Your Order */
		$this->register_content_order_summary();

		if ( $this->is_wc_feature_active( 'coupons' ) ) {
			$this->register_content_coupon_controls();
		}

		/* Payment */
		$this->register_content_payment();

		/* Help Docs */
		$this->register_content_help_docs();

		/* Style: Sections */
		$this->register_style_controls_sections();

		/* Style: Columns */
		$this->register_style_controls_columns();

		/* Style: Inputs */
		$this->register_style_controls_inputs();

		/* Style: Returning Customer */
		if ( $this->is_wc_feature_active( 'checkout_login_reminder' ) ) {
			$this->register_style_controls_returning_customer();
		}

		/* Style: Coupon Bar */
		$this->register_style_controls_coupon_bar();

		/* Style: Headings */
		$this->register_style_controls_headings();

		/* Style: Billing Details */
		$this->register_style_controls_billing_details();
	}

	/**
	 * Register toggle widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @access protected
	 */
	protected function register_content_general_controls() {

		$this->start_controls_section(
			'section_layout',
			array(
				'label' => __( 'Layout', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '1',
				'options' => array(
					'1' => __( 'One Column', 'powerpack' ),
					'2' => __( 'Two Columns', 'powerpack' ),
				),
				'prefix_class' => 'pp-checkout-layout-',
			)
		);

		$this->add_control(
			'columns_stack',
			array(
				'label'              => __( 'Stack On', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'tablet',
				'options'            => array(
					'tablet' => __( 'Tablet', 'powerpack' ),
					'mobile' => __( 'Mobile', 'powerpack' ),
				),
				'prefix_class'       => 'pp-woo-cols-stack-',
				'frontend_available' => true,
				'condition'          => array(
					'layout' => '2',
				),
			)
		);

		$this->add_responsive_control(
			'column_1_width',
			array(
				'label'      => __( 'First Column Width', 'powerpack' ) . ' (%)',
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%' ),
				'devices'    => array( 'desktop', 'tablet' ),
				'default'    => array(
					'size' => 50,
				),
				'range'      => array(
					'%' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'separator'  => 'before',
				'selectors'  => array(
					'{{WRAPPER}}' => '--first-column-width: {{SIZE}}%;',
				),
				'condition'  => array(
					'layout' => '2',
				),
			)
		);

		$this->add_responsive_control(
			'column_gap',
			array(
				'label'      => __( 'Columns Gap', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'devices'    => array( 'desktop', 'tablet' ),
				'size_units' => array( 'px' ),
				'default'    => array(
					'size' => 30,
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}}' => '--checkout-columns-gap: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'layout' => '2',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function register_content_checkout_login_reminder_controls() {
		$this->start_controls_section(
			'section_returning_customer',
			[
				'label' => esc_html__( 'Returning Customer', 'powerpack' ),
			]
		);

		$this->add_control(
			'returning_customer_section_title',
			[
				'label' => esc_html__( 'Section Title', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Returning customer?', 'powerpack' ),
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'returning_customer_link_text',
			[
				'label' => esc_html__( 'Link Text', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Click here to login', 'powerpack' ),
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_billing_details() {
		$this->start_controls_section(
			'section_billing_details',
			array(
				'label' => __( 'Billing Details', 'powerpack' ),
			)
		);

		$this->add_control(
			'billing_details_section_title',
			[
				'label' => esc_html__( 'Section Title', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => $this->is_wc_feature_active( 'ship_to_billing_address_only' ) ? esc_html__( 'Billing and Shipping Details', 'powerpack' ) : esc_html__( 'Billing Details', 'powerpack' ),
				'default' => $this->is_wc_feature_active( 'ship_to_billing_address_only' ) ? esc_html__( 'Billing and Shipping Details', 'powerpack' ) : esc_html__( 'Billing Details', 'powerpack' ),
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_responsive_control(
			'billing_details_alignment',
			[
				'label' => esc_html__( 'Alignment', 'powerpack' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'start' => [
						'title' => esc_html__( 'Start', 'powerpack' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'powerpack' ),
						'icon' => 'eicon-text-align-center',
					],
					'end' => [
						'title' => esc_html__( 'End', 'powerpack' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--billing-details-title-alignment: {{VALUE}};',
				],
			]
		);

		$repeater = new Repeater();

		$repeater->start_controls_tabs( 'tabs', [
			'condition' => [
				'repeater_state' => '',
			],
		] );

		$repeater->start_controls_tab( 'content_tab', [
			'label' => esc_html__( 'Content', 'powerpack' ),
		] );

		$repeater->add_control(
			'label',
			[
				'label' => esc_html__( 'Label', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
			]
		);

		$repeater->add_control(
			'placeholder',
			[
				'label' => esc_html__( 'Placeholder', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
			]
		);

		$repeater->end_controls_tab();

		$repeater->start_controls_tab( 'advanced_tab', [
			'label' => esc_html__( 'Advanced', 'powerpack' ),
		] );

		$repeater->add_control(
			'default',
			[
				'label' => esc_html__( 'Default Value', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->end_controls_tab();

		$repeater->end_controls_tabs();

		$repeater->add_control(
			'repeater_state',
			[
				'label' => esc_html__( 'Repeater State - hidden', 'powerpack' ),
				'type' => Controls_Manager::HIDDEN,
			]
		);

		$repeater->add_control(
			'locale_notice',
			[
				'raw' => __( 'Note: This content cannot be changed due to local regulations.', 'powerpack' ),
				'type' => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-descriptor',
				'condition' => [
					'repeater_state' => 'locale',
				],
			]
		);

		$repeater->add_control(
			'from_billing_notice',
			[
				'raw' => __( 'Note: This label and placeholder are taken from the Billing section. You can change it there.', 'powerpack' ),
				'type' => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-descriptor',
				'condition' => [
					'repeater_state' => 'from_billing',
				],
			]
		);

		$this->add_control(
			'billing_details_form_fields',
			[
				'label' => esc_html__( 'Form Items', 'powerpack' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'item_actions' => [
					'add' => false,
					'duplicate' => false,
					'remove' => false,
					'sort' => false,
				],
				'default' => $this->get_billing_field_defaults(),
				'title_field' => '{{{ label }}}',
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_shipping_details() {
		$this->start_controls_section(
			'section_shipping_details',
			[
				'label' => esc_html__( 'Shipping Details', 'powerpack' ),
			]
		);

		$this->add_control(
			'shipping_details_section_title',
			[
				'label' => esc_html__( 'Section Title', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Ship to a different address?', 'powerpack' ),
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'repeater_state',
			[
				'label' => esc_html__( 'Repeater State - hidden', 'powerpack' ),
				'type' => Controls_Manager::HIDDEN,
			]
		);

		$repeater->add_control(
			'label_placeholder_notification',
			[
				'raw' => __( 'Note: This label and placeholder are taken from the Billing section. You can change it there.', 'powerpack' ),
				'type' => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-descriptor',
				'condition' => [
					'repeater_state' => 'from_billing',
				],
			]
		);

		$repeater->start_controls_tabs( 'tabs', [
			'condition' => [
				'repeater_state' => '',
			],
		] );

		$repeater->start_controls_tab( 'content_tab', [
			'label' => esc_html__( 'Content', 'powerpack' ),
		] );

		$repeater->add_control(
			'label',
			[
				'label' => esc_html__( 'Label', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
			]
		);

		$repeater->add_control(
			'placeholder',
			[
				'label' => esc_html__( 'Placeholder', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
			]
		);

		$repeater->end_controls_tab();

		$repeater->start_controls_tab( 'advanced_tab', [
			'label' => esc_html__( 'Advanced', 'powerpack' ),
		] );

		$repeater->add_control(
			'default',
			[
				'label' => esc_html__( 'Default Value', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->end_controls_tab();

		$repeater->end_controls_tabs();

		$repeater->add_control(
			'locale_notice',
			[
				'raw' => __( 'Note: This content cannot be changed due to local regulations.', 'powerpack' ),
				'type' => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-descriptor',
				'condition' => [
					'repeater_state' => 'locale',
				],
			]
		);

		$repeater->add_control(
			'from_billing_notice',
			[
				'raw' => __( 'Note: This label and placeholder are taken from the Billing section. You can change it there.', 'powerpack' ),
				'type' => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-descriptor',
				'condition' => [
					'repeater_state' => 'from_billing',
				],
			]
		);

		$this->add_control(
			'shipping_details_form_fields',
			[
				'label' => esc_html__( 'Form Items', 'powerpack' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'item_actions' => [
					'add' => false,
					'duplicate' => false,
					'remove' => false,
					'sort' => false,
				],
				'default' => $this->get_shipping_field_defaults(),
				'title_field' => '{{{ label }}}',
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_additional_information() {
		$this->start_controls_section(
			'section_additional_information',
			array(
				'label' => __( 'Additional Information', 'powerpack' ),
			)
		);

		$this->add_control(
			'hide_additional_info',
			array(
				'label'        => __( 'Hide Additonal Information Box', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
			)
		);

		if ( $this->is_wc_feature_active( 'additional_options' ) ) {
			$this->add_control(
				'additional_information_section_title',
				[
					'label' => esc_html__( 'Section Title', 'powerpack' ),
					'type' => Controls_Manager::TEXT,
					'placeholder' => esc_html__( 'Additional Information', 'powerpack' ),
					'default' => esc_html__( 'Additional Information', 'powerpack' ),
					'dynamic' => [
						'active' => true,
					],
					'condition' => [
						'hide_additional_info!' => 'yes',
					],
				]
			);

			$this->add_responsive_control(
				'additional_information_alignment',
				[
					'label' => esc_html__( 'Alignment', 'powerpack' ),
					'type' => Controls_Manager::CHOOSE,
					'options' => [
						'start' => [
							'title' => esc_html__( 'Start', 'powerpack' ),
							'icon' => 'eicon-text-align-left',
						],
						'center' => [
							'title' => esc_html__( 'Center', 'powerpack' ),
							'icon' => 'eicon-text-align-center',
						],
						'end' => [
							'title' => esc_html__( 'End', 'powerpack' ),
							'icon' => 'eicon-text-align-right',
						],
					],
					'selectors' => [
						'{{WRAPPER}} .woocommerce .woocommerce-additional-fields' => 'text-align: {{VALUE}};',
					],
					'condition' => [
						'hide_additional_info!' => 'yes',
					],
				]
			);
		}

		$repeater = new Repeater();

		$repeater->start_controls_tabs( 'additional_information_form_fields_tabs' );

		$repeater->start_controls_tab( 'additional_information_form_fields_content_tab', [
			'label' => esc_html__( 'Content', 'powerpack' ),
		] );

		$repeater->add_control(
			'label',
			[
				'label' => esc_html__( 'Label', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
			]
		);

		$repeater->add_control(
			'placeholder',
			[
				'label' => esc_html__( 'Placeholder', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
			]
		);

		$repeater->end_controls_tab();

		$repeater->start_controls_tab( 'additional_information_form_fields_advanced_tab', [
			'label' => esc_html__( 'Advanced', 'powerpack' ),
		] );

		$repeater->add_control(
			'default',
			[
				'label' => esc_html__( 'Default Value', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->end_controls_tab();

		$repeater->end_controls_tabs();

		$this->add_control(
			'additional_information_form_fields',
			[
				'label' => esc_html__( 'Items', 'powerpack' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'item_actions' => [
					'add' => false,
					'duplicate' => false,
					'remove' => false,
					'sort' => false,
				],
				'default' => [
					[
						'field_key' => 'order_comments',
						'field_label' => esc_html__( 'Order Notes', 'powerpack' ),
						'label' => esc_html__( 'Order Notes', 'powerpack' ),
						'placeholder' => esc_html__( 'Notes about your order, e.g. special notes for delivery.', 'powerpack' ),
					],
				],
				'title_field' => '{{{ label }}}',
				'condition' => [
					'hide_additional_info!' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_signup_and_login_from_checkout_controls() {
		$this->start_controls_section(
			'section_create_account',
			[
				'label' => esc_html__( 'Create an Account', 'powerpack' ),
			]
		);

		$this->add_control(
			'create_account_text',
			[
				'label' => esc_html__( 'Section Title', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Create an account?', 'powerpack' ),
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_order_summary() {
		$this->start_controls_section(
			'section_order_summary',
			array(
				'label' => __( 'Your Order', 'powerpack' ),
			)
		);

		$this->add_control(
			'order_summary_section_title',
			[
				'label' => esc_html__( 'Section Title', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Your Order', 'powerpack' ),
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_responsive_control(
			'order_summary_alignment',
			[
				'label' => esc_html__( 'Alignment', 'powerpack' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'start' => [
						'title' => esc_html__( 'Start', 'powerpack' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'powerpack' ),
						'icon' => 'eicon-text-align-center',
					],
					'end' => [
						'title' => esc_html__( 'End', 'powerpack' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .woocommerce #order_review_heading' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_coupon_controls() {
		$this->start_controls_section(
			'section_coupon',
			[
				'label' => esc_html__( 'Coupon', 'powerpack' ),
			]
		);

		$this->add_control(
			'coupon_section_title_text',
			[
				'label' => esc_html__( 'Section Title', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Have a coupon?', 'powerpack' ),
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'coupon_section_title_link_text',
			[
				'label' => esc_html__( 'Link Text', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Click here to enter your coupon code', 'powerpack' ),
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_payment() {
		$this->start_controls_section(
			'section_payment',
			[
				'label' => esc_html__( 'Payment', 'powerpack' ),
			]
		);

		$this->add_control(
			'terms_conditions_heading',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Terms &amp; Conditions', 'powerpack' ),
			]
		);

		$this->add_control(
			'terms_conditions_message_text',
			[
				'label' => esc_html__( 'Message', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => esc_html__( 'I have read and agree to the website', 'powerpack' ),
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'terms_conditions_link_text',
			[
				'label' => esc_html__( 'Link Text', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => esc_html__( 'terms and conditions', 'powerpack' ),
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'purchase_buttom_heading',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Purchase Button', 'powerpack' ),
			]
		);

		$this->add_responsive_control(
			'purchase_button_alignment',
			[
				'label' => esc_html__( 'Alignment', 'powerpack' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'start' => [
						'title' => esc_html__( 'Start', 'powerpack' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'powerpack' ),
						'icon' => 'eicon-text-align-center',
					],
					'end' => [
						'title' => esc_html__( 'End', 'powerpack' ),
						'icon' => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => esc_html__( 'Justify', 'powerpack' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .woocommerce-checkout' => '{{VALUE}}',
				],
				'selectors_dictionary' => [
					'start' => '--place-order-title-alignment: flex-start; --purchase-button-width: fit-content;',
					'center' => '--place-order-title-alignment: center; --purchase-button-width: fit-content;',
					'end' => '--place-order-title-alignment: flex-end; --purchase-button-width: fit-content;',
					'justify' => '--place-order-title-alignment: stretch; --purchase-button-width: 100%;',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_help_docs() {

		$help_docs = PP_Config::get_widget_help_links( 'Woo_Checkout' );

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
	 * Style Tab: Section
	 * -------------------------------------------------
	 */
	protected function register_style_controls_columns() {

		$this->start_controls_section(
			'section_columns_style',
			array(
				'label' => __( 'Columns', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'column_1_style_heading',
			array(
				'label' => __( 'Column 1', 'powerpack' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'column_1_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .woocommerce #customer_details .col-1, {{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields',
			)
		);

		/* $this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'column_1_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .woocommerce #customer_details .col-1, {{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields',
			)
		); */

		$this->add_control(
			'column_1_border_border',
			[
				'label'     => esc_html__( 'Border Type', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => [
					''       => __( 'None', 'powerpack' ),
					'solid'  => __( 'Solid', 'powerpack' ),
					'double' => __( 'Double', 'powerpack' ),
					'dotted' => __( 'Dotted', 'powerpack' ),
				],
				'selectors' => [
					'{{WRAPPER}} .woocommerce .pp-checkout-column-start' => '--sections-border-type: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'column_1_border_width',
			[
				'label'      => esc_html__( 'Border Width', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'default'    => [
					'top'       => 1,
					'right'     => 1,
					'bottom'    => 1,
					'left'      => 1,
					'isLinked'  => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .woocommerce .pp-checkout-column-start' => '--sections-border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'column_1_border_border!' => '',
				],
			]
		);

		$this->add_control(
			'column_1_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .woocommerce .pp-checkout-column-start' => '--sections-border-color: {{VALUE}};',
				],
				'condition' => [
					'column_1_border_border!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'column_1_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce .pp-checkout-column-start' => '--sections-border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'column_1_box_shadow',
				'selector' => '{{WRAPPER}} .woocommerce #customer_details .col-1, {{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields',
			)
		);

		$this->add_responsive_control(
			'column_1_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce .pp-checkout-column-start' => '--sections-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'column_2_style_heading',
			array(
				'label'     => __( 'Column 2', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'layout' => '2',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'column_2_background',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .woocommerce .pp-checkout__order_review, {{WRAPPER}} .woocommerce .woocommerce-checkout #payment',
				'condition' => array(
					'layout' => '2',
				),
			)
		);

		/* $this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'column_2_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .woocommerce .pp-checkout__order_review, {{WRAPPER}} .woocommerce .woocommerce-checkout #payment',
				'condition'   => array(
					'layout' => '2',
				),
			)
		); */

		$this->add_control(
			'column_2_border_border',
			[
				'label'     => esc_html__( 'Border Type', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => [
					''       => __( 'None', 'powerpack' ),
					'solid'  => __( 'Solid', 'powerpack' ),
					'double' => __( 'Double', 'powerpack' ),
					'dotted' => __( 'Dotted', 'powerpack' ),
				],
				'selectors' => [
					'{{WRAPPER}} .woocommerce .pp-checkout-column-end' => '--sections-border-type: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'column_2_border_width',
			[
				'label'      => esc_html__( 'Border Width', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'default'    => [
					'top'       => 1,
					'right'     => 1,
					'bottom'    => 1,
					'left'      => 1,
					'isLinked'  => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .woocommerce .pp-checkout-column-end' => '--sections-border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'column_2_border_border!' => '',
				],
			]
		);

		$this->add_control(
			'column_2_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .woocommerce .pp-checkout-column-end' => '--sections-border-color: {{VALUE}};',
				],
				'condition' => [
					'column_2_border_border!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'column_2_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-checkout-column-end' => '--sections-border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'layout' => '2',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'column_2_box_shadow',
				'selector'  => '{{WRAPPER}} .woocommerce .pp-checkout__order_review, {{WRAPPER}} .woocommerce .woocommerce-checkout #payment',
				'condition' => array(
					'layout' => '2',
				),
			)
		);

		$this->add_responsive_control(
			'column_2_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-checkout-column-end' => '--sections-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'layout' => '2',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Section
	 * -------------------------------------------------
	 */
	protected function register_style_controls_sections() {

		$this->start_controls_section(
			'section_sections_style',
			array(
				'label' => __( 'Sections', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'sections_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .woocommerce .pp-woocommerce-login-section, {{WRAPPER}} .woocommerce .pp-checkout__order_review, {{WRAPPER}} .woocommerce .pp-coupon-box, {{WRAPPER}} .woocommerce .woocommerce-checkout #payment, {{WRAPPER}} .woocommerce #customer_details .col-1, {{WRAPPER}} .woocommerce .shipping_address, {{WRAPPER}} .woocommerce .woocommerce-additional-fields',
			)
		);

		/* $this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'sections_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'separator'   => 'before',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper, {{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper, {{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table, {{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-payment',
			)
		); */

		$this->add_control(
			'sections_border_border',
			[
				'label'     => esc_html__( 'Border Type', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'solid',
				'options'   => [
					''       => __( 'None', 'powerpack' ),
					'solid'  => __( 'Solid', 'powerpack' ),
					'double' => __( 'Double', 'powerpack' ),
					'dotted' => __( 'Dotted', 'powerpack' ),
				],
				'selectors' => [
					'{{WRAPPER}}' => '--sections-border-type: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'sections_border_width',
			[
				'label'      => esc_html__( 'Border Width', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'default'    => [
					'top'       => 1,
					'right'     => 1,
					'bottom'    => 1,
					'left'      => 1,
					'isLinked'  => true,
				],
				'selectors'  => [
					'{{WRAPPER}}' => '--sections-border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'sections_border_border!' => '',
				],
			]
		);

		$this->add_control(
			'sections_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}}' => '--sections-border-color: {{VALUE}};',
				],
				'condition' => [
					'sections_border_border!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'sections_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}}' => '--sections-border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'sections_box_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .woocommerce .pp-woocommerce-login-section, {{WRAPPER}} .woocommerce .pp-checkout__order_review, {{WRAPPER}} .woocommerce .pp-coupon-box, {{WRAPPER}} .woocommerce .woocommerce-checkout #payment, {{WRAPPER}} .woocommerce #customer_details .col-1, {{WRAPPER}} .woocommerce .shipping_address, {{WRAPPER}} .woocommerce .woocommerce-additional-fields',
			)
		);

		$this->add_responsive_control(
			'sections_gap',
			array(
				'label'     => __( 'Spacing', 'powerpack' ),
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
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}}' => '--sections-margin: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'sections_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}}' => '--sections-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Inputs
	 * -------------------------------------------------
	 */
	protected function register_style_controls_inputs() {

		$this->start_controls_section(
			'section_inputs_style',
			array(
				'label' => __( 'Inputs', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
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
					'{{WRAPPER}} .woocommerce form .input-text, {{WRAPPER}} .woocommerce form  select, {{WRAPPER}} .select2-container .select2-selection' => 'text-align: {{VALUE}};',
				),
				'separator'   => 'after',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'inputs_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .woocommerce form .input-text, {{WRAPPER}} .woocommerce form  select, {{WRAPPER}} .select2-container .select2-selection',
			)
		);

		$this->add_control(
			'input_text_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woocommerce form .input-text, {{WRAPPER}} .woocommerce form  select, {{WRAPPER}} .select2-container .select2-selection' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'input_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woocommerce form .input-text, {{WRAPPER}} .woocommerce form  select, {{WRAPPER}} .select2-container .select2-selection' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'inputs_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'separator'   => 'before',
				'selector'    => '{{WRAPPER}} .woocommerce form .input-text, {{WRAPPER}} .woocommerce form  select, {{WRAPPER}} .select2-container .select2-selection',
			)
		);

		$this->add_responsive_control(
			'inputs_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce form .input-text, {{WRAPPER}} .woocommerce form  select, {{WRAPPER}} .select2-container .select2-selection' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'inputs_box_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .woocommerce form .input-text, {{WRAPPER}} .woocommerce form  select, {{WRAPPER}} .select2-container .select2-selection',
			)
		);

		$this->add_responsive_control(
			'inputs_gap',
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
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}} .woocommerce form .input-text, {{WRAPPER}} .woocommerce form  select, {{WRAPPER}} .select2-container' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'inputs_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce form .input-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'inputs_height',
			array(
				'label'     => __( 'Input Height', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}} .woocommerce .form-row input.input-text, {{WRAPPER}} .woocommerce .form-row select' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'textarea_height',
			array(
				'label'     => __( 'Textarea Height', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => '',
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 200,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .woocommerce form .form-row textarea' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Returning Customer Box
	 * -------------------------------------------------
	 */
	protected function register_style_controls_returning_customer() {
		$this->start_controls_section(
			'section_returning_customer_style',
			array(
				'label' => __( 'Returning Customer Box', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'returning_customer_toggle_heading',
			array(
				'label' => __( 'Returning Customer Toggle', 'powerpack' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'returning_customer_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login-toggle .woocommerce-info' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'returning_customer_icon_color',
			array(
				'label'     => __( 'Icon Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login-toggle .woocommerce-info:before' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'returning_customer_links_color',
			array(
				'label'     => __( 'Links Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login-toggle .woocommerce-info a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'returning_customer_links_color_hover',
			array(
				'label'     => __( 'Links Hover Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login-toggle .woocommerce-info a:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'returning_customer_toggle_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login-toggle .woocommerce-info',
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'returning_customer_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login-toggle .woocommerce-info',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'returning_customer_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login-toggle .woocommerce-info',
			)
		);

		$this->add_responsive_control(
			'returning_customer_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login-toggle .woocommerce-info' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'returning_customer_box_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login-toggle .woocommerce-info',
			)
		);

		$this->add_control(
			'returning_customer_form_heading',
			array(
				'label'     => __( 'Login Form', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'returning_customer_form_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'returning_customer_form_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'returning_customer_form_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login',
			)
		);

		$this->add_responsive_control(
			'returning_customer_form_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'returning_customer_form_box_shadow',
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login',
			)
		);

		$this->add_responsive_control(
			'returning_customer_form_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'returning_customer_form_input_heading',
			array(
				'label'     => __( 'Login Form Input', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'returning_customer_form_input_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login input.input-text' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'returning_customer_form_input_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login input.input-text' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'returning_customer_form_input_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login input.input-text',
			)
		);

		$this->add_responsive_control(
			'returning_customer_form_input_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login input.input-text' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'returning_customer_form_input_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login input.input-text',
			)
		);

		$this->add_responsive_control(
			'returning_customer_form_input_height',
			array(
				'label'     => __( 'Input Height', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login input.input-text' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'returning_customer_form_input_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login input.input-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'returning_customer_form_input_label_heading',
			array(
				'label'     => __( 'Login Form Input Label', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'returning_customer_form_input_label_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'returning_customer_form_input_label_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login label',
			)
		);

		$this->add_responsive_control(
			'returning_customer_form_input_label_spacing',
			array(
				'label'     => __( 'Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 5,
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login label' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'returning_customer_form_button_heading',
			array(
				'label'     => __( 'Login Form Button', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'returning_customer_form_button_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login .button',
			)
		);

		$this->start_controls_tabs( 'tabs_returning_customer_form_button_style' );

		$this->start_controls_tab(
			'tab_returning_customer_form_button_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'returning_customer_form_button_text_color_normal',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login .button' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'returning_customer_form_button_bg_color_normal',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login .button' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'returning_customer_form_button_border_normal',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login .button',
			)
		);

		$this->add_responsive_control(
			'returning_customer_form_button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login .button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'returning_customer_form_button_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login .button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'returning_customer_form_button_box_shadow',
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login .button',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_returning_customer_form_button_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'returning_customer_form_button_text_color_hover',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login .button:hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'returning_customer_form_button_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login .button:hover' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'returning_customer_form_button_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login .button:hover' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'returning_customer_form_button_box_shadow_hover',
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login .button:hover',
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Coupon Bar
	 * -------------------------------------------------
	 */
	protected function register_style_controls_coupon_bar() {
		$this->start_controls_section(
			'section_coupon_bar_style',
			array(
				'label' => __( 'Coupon Bar', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'coupon_bar_toggle_heading',
			array(
				'label' => __( 'Coupon Toggle', 'powerpack' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'coupon_bar_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon-toggle .woocommerce-info' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'coupon_bar_icon_color',
			array(
				'label'     => __( 'Icon Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon-toggle .woocommerce-info:before' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'coupon_bar_links_color',
			array(
				'label'     => __( 'Links Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon-toggle .woocommerce-info a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'coupon_bar_links_color_hover',
			array(
				'label'     => __( 'Links Hover Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon-toggle .woocommerce-info a:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'coupon_bar_toggle_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon-toggle .woocommerce-info',
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'coupon_bar_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon-toggle .woocommerce-info',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'coupon_bar_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon-toggle .woocommerce-info',
			)
		);

		$this->add_responsive_control(
			'coupon_bar_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon-toggle .woocommerce-info' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'coupon_bar_box_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon-toggle .woocommerce-info',
			)
		);

		$this->add_control(
			'coupon_bar_form_heading',
			array(
				'label'     => __( 'Coupon Form', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'coupon_form_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'coupon_form_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'coupon_form_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon',
			)
		);

		$this->add_responsive_control(
			'coupon_form_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'coupon_form_box_shadow',
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon',
			)
		);

		$this->add_responsive_control(
			'coupon_form_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'coupon_bar_form_input_heading',
			array(
				'label'     => __( 'Coupon Form Input', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'coupon_bar_form_input_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon #coupon_code' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'coupon_bar_form_input_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon #coupon_code' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'coupon_bar_form_input_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon #coupon_code',
			)
		);

		$this->add_responsive_control(
			'coupon_bar_form_input_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon #coupon_code' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'coupon_bar_form_input_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon #coupon_code',
			)
		);

		$this->add_responsive_control(
			'coupon_bar_form_input_height',
			array(
				'label'     => __( 'Input Height', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon #coupon_code' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'coupon_bar_form_input_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon #coupon_code' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'coupon_bar_form_button_heading',
			array(
				'label'     => __( 'Coupon Form Button', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'coupon_bar_form_button_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon .button',
			)
		);

		$this->start_controls_tabs( 'tabs_coupon_form_button_style' );

		$this->start_controls_tab(
			'tab_coupon_form_button_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'coupon_form_button_text_color_normal',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon .button' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'coupon_form_button_bg_color_normal',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => [
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_4,
				],
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon .button' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'coupon_form_button_border_normal',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon .button',
			)
		);

		$this->add_responsive_control(
			'coupon_form_button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon .button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'coupon_form_button_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon .button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'coupon_form_button_box_shadow',
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon .button',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_coupon_form_button_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'coupon_form_button_text_color_hover',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon .button:hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'coupon_form_button_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon .button:hover' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'coupon_form_button_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon .button:hover' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'coupon_form_button_box_shadow_hover',
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon .button:hover',
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Headings
	 * -------------------------------------------------
	 */
	protected function register_style_controls_headings() {
		$this->start_controls_section(
			'section_headings_style',
			array(
				'label' => __( 'Headings', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'headings_text_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--sections-title-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'headings_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout #customer_details .woocommerce-billing-fields > h3, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields > h3, {{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields > h3, {{WRAPPER}} .pp-woo-checkout #order_review_heading',
			)
		);

		$this->add_responsive_control(
			'headings_spacing',
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
					'{{WRAPPER}}' => '--sections-title-spacing: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Billing Details
	 * -------------------------------------------------
	 */
	protected function register_style_controls_billing_details() {
		$this->start_controls_section(
			'section_billing_details_style',
			array(
				'label' => __( 'Billing Details', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'section_billing_details_heading',
			array(
				'label' => __( 'Section', 'powerpack' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'section_billing_details_background',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1',
			)
		);

		/* $this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'section_billing_details_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1',
			)
		); */

		$this->add_control(
			'section_billing_details_border_border',
			[
				'label'     => esc_html__( 'Border Type', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => [
					''       => __( 'None', 'powerpack' ),
					'solid'  => __( 'Solid', 'powerpack' ),
					'double' => __( 'Double', 'powerpack' ),
					'dotted' => __( 'Dotted', 'powerpack' ),
				],
				'selectors' => [
					'{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1' => '--sections-border-type: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'section_billing_details_border_width',
			[
				'label'      => esc_html__( 'Border Width', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'default'    => [
					'top'       => 1,
					'right'     => 1,
					'bottom'    => 1,
					'left'      => 1,
					'isLinked'  => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1' => '--sections-border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'section_billing_details_border_border!' => '',
				],
			]
		);

		$this->add_control(
			'section_billing_details_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1' => '--sections-border-color: {{VALUE}};',
				],
				'condition' => [
					'section_billing_details_border_border!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'section_billing_details_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1' => '--sections-border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'section_billing_details_box_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1',
			)
		);

		$this->add_control(
			'section_billing_details_inputs_heading',
			array(
				'label'     => __( 'Inputs', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'section_billing_details_inputs_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 input.input-text, {{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 select',
			)
		);

		$this->add_responsive_control(
			'section_billing_details_inputs_height',
			array(
				'label'     => __( 'Input Height', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 input.input-text, {{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 select' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'section_billing_details_inputs_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 input.input-text, {{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_billing_details_inputs_style' );

		$this->start_controls_tab(
			'tab_billing_details_inputs_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'section_billing_details_inputs_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 input.input-text, {{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 select' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_billing_details_inputs_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 input.input-text, {{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 select' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'section_billing_details_inputs_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 input.input-text, {{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 select',
			)
		);

		$this->add_responsive_control(
			'section_billing_details_inputs_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper input.input-text' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'section_billing_details_inputs_box_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 input.input-text, {{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 select',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_billing_details_inputs_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'section_billing_details_inputs_text_color_hover',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 input.input-text:hover, {{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 select:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_billing_details_inputs_background_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 input.input-text:hover, {{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 select:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_billing_details_inputs_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 input.input-text:hover, {{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 select:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'section_billing_details_inputs_box_shadow_hover',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 input.input-text:hover, {{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 select:hover',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_billing_details_inputs_focus',
			array(
				'label' => __( 'Focus', 'powerpack' ),
			)
		);

		$this->add_control(
			'section_billing_details_inputs_text_color_focus',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 input.input-text:focus, {{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 select:focus' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_billing_details_inputs_background_color_focus',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 input.input-text:focus, {{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 select:focus' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_billing_details_inputs_border_color_focus',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 input.input-text:focus, {{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 select:focus' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'section_billing_details_inputs_box_shadow_focus',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 input.input-text:focus, {{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 select:focus',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'section_billing_details_inputs_label_heading',
			array(
				'label'     => __( 'Input Label', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'section_billing_details_inputs_label_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'section_billing_details_inputs_label_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 label',
			)
		);

		$this->add_responsive_control(
			'section_billing_details_inputs_label_spacing',
			array(
				'label'     => __( 'Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 5,
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce #customer_details .col-1 label' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		/**
		 * Style Tab: Additional Information
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_additional_fields_style',
			array(
				'label' => __( 'Additional Information', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'section_additional_fields_heading',
			array(
				'label' => __( 'Section', 'powerpack' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'section_additional_fields_background',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields',
			)
		);

		/* $this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'section_additional_fields_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper',
			)
		); */

		$this->add_control(
			'section_additional_fields_border_border',
			[
				'label'     => esc_html__( 'Border Type', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => [
					''       => __( 'None', 'powerpack' ),
					'solid'  => __( 'Solid', 'powerpack' ),
					'double' => __( 'Double', 'powerpack' ),
					'dotted' => __( 'Dotted', 'powerpack' ),
				],
				'selectors' => [
					'{{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields' => '--sections-border-type: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'section_additional_fields_border_width',
			[
				'label'      => esc_html__( 'Border Width', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'default'    => [
					'top'       => 1,
					'right'     => 1,
					'bottom'    => 1,
					'left'      => 1,
					'isLinked'  => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields' => '--sections-border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'section_additional_fields_border_border!' => '',
				],
			]
		);

		$this->add_control(
			'section_additional_fields_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields' => '--sections-border-color: {{VALUE}};',
				],
				'condition' => [
					'section_additional_fields_border_border!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'section_additional_fields_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'section_additional_fields_box_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields',
			)
		);

		$this->add_control(
			'section_additional_fields_textarea_heading',
			array(
				'label'     => __( 'Textarea', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'section_additional_fields_textarea_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields textarea',
			)
		);

		$this->start_controls_tabs( 'tabs_additional_fields_textarea_style' );

		$this->start_controls_tab(
			'tab_additional_fields_textarea_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'section_additional_fields_textarea_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields textarea' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_additional_fields_textarea_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields textarea' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'section_additional_fields_textarea_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields textarea',
			)
		);

		$this->add_responsive_control(
			'section_additional_fields_textarea_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields textarea' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'section_additional_fields_textarea_box_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields textarea',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_additional_fields_textarea_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'section_additional_fields_textarea_text_color_hover',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields textarea:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_additional_fields_textarea_background_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields textarea:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_additional_fields_textarea_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields textarea:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'section_additional_fields_textarea_box_shadow_hover',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields textarea:hover',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_additional_fields_textarea_focus',
			array(
				'label' => __( 'Focus', 'powerpack' ),
			)
		);

		$this->add_control(
			'section_additional_fields_textarea_text_color_focus',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields textarea:focus' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_additional_fields_textarea_background_color_focus',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields textarea:focus' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_additional_fields_textarea_border_color_focus',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields textarea:focus' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'section_additional_fields_textarea_box_shadow_focus',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields textarea:focus',
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'section_additional_fields_textarea_label_heading',
			array(
				'label'     => __( 'Textarea Label', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'section_additional_fields_textarea_label_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'section_additional_fields_textarea_label_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields label',
			)
		);

		$this->add_responsive_control(
			'section_additional_fields_textarea_label_spacing',
			array(
				'label'     => __( 'Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 5,
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .woocommerce #customer_details .woocommerce-additional-fields label' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		/**
		 * Style Tab: Review Order
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_review_order_style',
			array(
				'label' => __( 'Review Order', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'section_review_order_heading',
			array(
				'label' => __( 'Section', 'powerpack' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'section_review_order_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce .pp-checkout__order_review',
			)
		);

		/* $this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'section_review_order_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woocommerce.pp-woo-checkout .woocommerce-checkout #order_review .shop_table, {{WRAPPER}} .pp-woocommerce.pp-woo-checkout .woocommerce-checkout #order_review .shop_table th, {{WRAPPER}} .pp-woocommerce.pp-woo-checkout .woocommerce-checkout #order_review .shop_table td',
			)
		); */

		$this->add_control(
			'section_review_order_border_border',
			[
				'label'     => esc_html__( 'Border Type', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => [
					''       => __( 'None', 'powerpack' ),
					'solid'  => __( 'Solid', 'powerpack' ),
					'double' => __( 'Double', 'powerpack' ),
					'dotted' => __( 'Dotted', 'powerpack' ),
				],
				'selectors' => [
					'{{WRAPPER}} .pp-woo-checkout .woocommerce .pp-checkout__order_review' => '--sections-border-type: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'section_review_order_border_width',
			[
				'label'      => esc_html__( 'Border Width', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'default'    => [
					'top'       => 1,
					'right'     => 1,
					'bottom'    => 1,
					'left'      => 1,
					'isLinked'  => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .pp-woo-checkout .woocommerce .pp-checkout__order_review' => '--sections-border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'section_review_order_border_border!' => '',
				],
			]
		);

		$this->add_control(
			'section_review_order_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .pp-woo-checkout .woocommerce .pp-checkout__order_review' => '--sections-border-color: {{VALUE}};',
				],
				'condition' => [
					'section_review_order_border_border!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'section_review_order_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce .pp-checkout__order_review' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'section_review_order_box_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce .pp-checkout__order_review',
			)
		);

		$this->add_responsive_control(
			'section_review_order_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce .pp-checkout__order_review' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'section_review_order_table_heading',
			array(
				'label' => __( 'Table', 'powerpack' ),
				'type'  => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'section_review_order_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce .pp-checkout__order_review',
			)
		);

		$this->add_control(
			'section_review_order_table_cell_heading',
			array(
				'label'     => __( 'Table Cell', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'section_review_order_cell_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woocommerce.pp-woo-checkout .woocommerce-checkout #order_review .shop_table th, {{WRAPPER}} .pp-woocommerce.pp-woo-checkout .woocommerce-checkout #order_review .shop_table td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'section_review_order_table_head_heading',
			array(
				'label'     => __( 'Table Head', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'section_review_order_table_head_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table thead th' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_review_order_table_head_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table thead th' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_review_order_table_foot_heading',
			array(
				'label'     => __( 'Table Footer', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'section_review_order_table_foot_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table tfoot tr' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_review_order_table_foot_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table tfoot tr' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_review_order_table_body_heading',
			array(
				'label'     => __( 'Table Body', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->start_controls_tabs( 'section_review_order_tbody_rows_tabs_style' );

		$this->start_controls_tab(
			'tab_section_review_order_even_row',
			array(
				'label' => __( 'Even Row', 'powerpack' ),
			)
		);

		$this->add_control(
			'section_review_order_even_row_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table .cart_item:nth-child(2n) td' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_review_order_even_row_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table .cart_item:nth-child(2n) td' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_section_review_order_odd_row',
			array(
				'label' => __( 'Odd Row', 'powerpack' ),
			)
		);

		$this->add_control(
			'section_review_order_odd_row_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table .cart_item:nth-child(2n+1) td' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_review_order_odd_row_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table .cart_item:nth-child(2n+1) td' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'section_review_order_row_separator_heading',
			array(
				'label'     => __( 'Row Separator', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'section_review_order_row_separator_type',
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
					'{{WRAPPER}} .pp-woo-checkout .woocommerce table.shop_table td, {{WRAPPER}} .pp-woo-checkout .woocommerce table.shop_table tfoot th' => 'border-top-style: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_review_order_row_separator_color',
			array(
				'label'     => __( 'Separator Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce table.shop_table td, {{WRAPPER}} .pp-woo-checkout .woocommerce table.shop_table tfoot th' => 'border-top-color: {{VALUE}};',
				),
				'condition' => array(
					'section_review_order_row_separator_type!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'section_review_order_row_separator_size',
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
					'{{WRAPPER}} .pp-woo-checkout .woocommerce table.shop_table td, {{WRAPPER}} .pp-woo-checkout .woocommerce table.shop_table tfoot th' => 'border-top-width: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'section_review_order_row_separator_type!' => 'none',
				),
			)
		);

		$this->end_controls_section();

		/**
		 * Style Tab: Payment Method
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_payment_method_style',
			array(
				'label' => __( 'Payment Method', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'section_payment_method_heading',
			array(
				'label' => __( 'Section', 'powerpack' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'section_payment_method_background',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce .woocommerce-checkout #payment',
			)
		);

		/* $this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'section_payment_method_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #payment',
			)
		); */

		$this->add_control(
			'section_payment_method_border_border',
			[
				'label'     => esc_html__( 'Border Type', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => [
					''       => __( 'None', 'powerpack' ),
					'solid'  => __( 'Solid', 'powerpack' ),
					'double' => __( 'Double', 'powerpack' ),
					'dotted' => __( 'Dotted', 'powerpack' ),
				],
				'selectors' => [
					'{{WRAPPER}} .pp-woo-checkout .woocommerce .woocommerce-checkout #payment' => '--sections-border-type: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'section_payment_method_border_width',
			[
				'label'      => esc_html__( 'Border Width', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'default'    => [
					'top'       => 1,
					'right'     => 1,
					'bottom'    => 1,
					'left'      => 1,
					'isLinked'  => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .pp-woo-checkout .woocommerce .woocommerce-checkout #payment' => '--sections-border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'section_payment_method_border_border!' => '',
				],
			]
		);

		$this->add_control(
			'section_payment_method_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .pp-woo-checkout .woocommerce .woocommerce-checkout #payment' => '--sections-border-color: {{VALUE}};',
				],
				'condition' => [
					'section_payment_method_border_border!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'section_payment_method_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce .woocommerce-checkout #payment' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'section_payment_method_box_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce .woocommerce-checkout #payment',
			)
		);

		$this->add_control(
			'section_payment_method_label_heading',
			array(
				'label'     => __( 'Label', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'payment_method_label_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout .payment_methods label',
			)
		);

		$this->add_control(
			'payment_method_label_text_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout .payment_methods label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_payment_method_message_heading',
			array(
				'label'     => __( 'Message', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'payment_method_message_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #payment .payment_box',
			)
		);

		$this->add_control(
			'payment_method_message_text_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #payment .payment_box' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'payment_method_message_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #payment .payment_box' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #payment .payment_box:before' => 'border-bottom-color: {{VALUE}};',
				),
			)
		);

		// Privacy Policy
		$this->add_control(
			'payment_privacy_policy',
			array(
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Privacy Policy', 'powerpack' ),
				'separator' => 'before',
			)
		);

		$this->add_control(
			'privacy_policy_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-terms-and-conditions-wrapper .woocommerce-privacy-policy-text' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'privacy_policy_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-terms-and-conditions-wrapper .woocommerce-privacy-policy-text',
			)
		);

		// Checkbox
		$this->add_control(
			'payment_checkboxes_title',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Terms & Conditions', 'powerpack' ),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'payment_checkboxes_color',
			[
				'label' => esc_html__( 'Color', 'powerpack' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce-terms-and-conditions-wrapper .woocommerce-form__label-for-checkbox span' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'payment_checkboxes_typography',
				'selector' => '{{WRAPPER}} .woocommerce-terms-and-conditions-wrapper .woocommerce-form__label-for-checkbox span',
			]
		);

		// Links
		$this->add_control(
			'sections_links_title',
			array(
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Links', 'powerpack' ),
				'separator' => 'before',
			)
		);

		$this->start_controls_tabs( 'payment_colors' );

		$this->start_controls_tab( 'payment_normal_colors', [
			'label' => esc_html__( 'Normal', 'powerpack' ),
		] );

		$this->add_control(
			'payment_normal_color',
			[
				'label' => esc_html__( 'Link Color', 'powerpack' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce-checkout-payment' => '--links-normal-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'payment_hover_colors', [
			'label' => esc_html__( 'Hover', 'powerpack' ),
		] );

		$this->add_control(
			'payment_hover_color',
			[
				'label' => esc_html__( 'Link Color', 'powerpack' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce-checkout-payment' => '--links-hover-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		/**
		 * Style Tab: Purchase Button
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_checkout_button_style',
			array(
				'label' => __( 'Purchase Button', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order',
			)
		);

		$this->add_control(
			'button_width',
			array(
				'label'        => __( 'Width', 'powerpack' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'auto',
				'options'      => array(
					'auto'   => __( 'Auto', 'powerpack' ),
					'full'   => __( 'Full Width', 'powerpack' ),
					'custom' => __( 'Custom', 'powerpack' ),
				),
				'prefix_class' => 'pp-woo-checkout-button-',
			)
		);

		$this->add_responsive_control(
			'button_custom_width',
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
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order' => 'width: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'button_width' => 'custom',
				),
			)
		);

		$this->add_responsive_control(
			'button_margin',
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
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
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
				'scheme'    => [
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_4,
				],
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order' => 'background-color: {{VALUE}}',
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
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order' => 'color: {{VALUE}}',
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
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order',
			)
		);

		$this->add_responsive_control(
			'button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order',
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
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order:hover' => 'background-color: {{VALUE}}',
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
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order:hover' => 'color: {{VALUE}}',
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
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order:hover' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_box_shadow_hover',
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order:hover',
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Get Billing Field Defaults
	 *
	 * Get defaults used for the billing details repeater control.
	 *
	 * @since 2.9.5
	 *
	 * @return array
	 */
	private function get_billing_field_defaults() {
		$fields = [
			'billing_first_name' => [
				'label' => esc_html__( 'First Name', 'powerpack' ),
				'repeater_state' => '',
			],
			'billing_last_name' => [
				'label' => esc_html__( 'Last Name', 'powerpack' ),
				'repeater_state' => '',
			],
			'billing_company' => [
				'label' => esc_html__( 'Company Name', 'powerpack' ),
				'repeater_state' => '',
			],
			'billing_country' => [
				'label' => esc_html__( 'Country / Region', 'powerpack' ),
				'repeater_state' => 'locale',
			],
			'billing_address_1' => [
				'label' => esc_html__( 'Street Address', 'powerpack' ),
				'repeater_state' => 'locale',
			],
			'billing_postcode' => [
				'label' => esc_html__( 'Post Code', 'powerpack' ),
				'repeater_state' => 'locale',
			],
			'billing_city' => [
				'label' => esc_html__( 'Town / City', 'powerpack' ),
				'repeater_state' => 'locale',
			],
			'billing_state' => [
				'label' => esc_html__( 'State', 'powerpack' ),
				'repeater_state' => 'locale',
			],
			'billing_phone' => [
				'label' => esc_html__( 'Phone', 'powerpack' ),
				'repeater_state' => '',
			],
			'billing_email' => [
				'label' => esc_html__( 'Email Address', 'powerpack' ),
				'repeater_state' => '',
			],
		];

		return $this->reformat_address_field_defaults( $fields );
	}

	/**
	 * Get Shipping Field Defaults
	 *
	 * Get defaults used for the shipping details repeater control.
	 *
	 * @since 2.9.5
	 *
	 * @return array
	 */
	private function get_shipping_field_defaults() {
		$fields = [
			'shipping_first_name' => [
				'label' => esc_html__( 'First Name', 'powerpack' ),
				'repeater_state' => '',
			],
			'shipping_last_name' => [
				'label' => esc_html__( 'Last Name', 'powerpack' ),
				'repeater_state' => '',
			],
			'shipping_company' => [
				'label' => esc_html__( 'Company Name', 'powerpack' ),
				'repeater_state' => '',
			],
			'shipping_country' => [
				'label' => esc_html__( 'Country / Region', 'powerpack' ),
				'repeater_state' => 'locale',
			],
			'shipping_address_1' => [
				'label' => esc_html__( 'Street Address', 'powerpack' ),
				'repeater_state' => 'locale',
			],
			'shipping_postcode' => [
				'label' => esc_html__( 'Post Code', 'powerpack' ),
				'repeater_state' => 'locale',
			],
			'shipping_city' => [
				'label' => esc_html__( 'Town / City', 'powerpack' ),
				'repeater_state' => 'locale',
			],
			'shipping_state' => [
				'label' => esc_html__( 'State', 'powerpack' ),
				'repeater_state' => 'locale',
			],
		];

		return $this->reformat_address_field_defaults( $fields );
	}

	/**
	 * Reformat Address Field Defaults
	 *
	 * Used with the `get_..._field_defaults()` methods.
	 * Takes the address array and converts it into the format expected by the repeater controls.
	 *
	 * @since 2.9.5
	 *
	 * @param $address
	 * @return array
	 */
	private function reformat_address_field_defaults( $address ) {
		$defaults = [];
		foreach ( $address as $key => $value ) {
			$defaults[] = [
				'field_key' => $key,
				'field_label' => $value['label'],
				'label' => $value['label'],
				'placeholder' => $value['label'],
				'repeater_state' => $value['repeater_state'],
			];
		}

		return $defaults;
	}

	/**
	 * Get Main Woocommerce Sections Selectors
	 *
	 * Get all the 'Sections' selectors. There are numerous controls that need these selectors so it was easier
	 * to consolidate them into one function. Especially when updates need to be made.
	 *
	 * @since 2.9.5
	 *
	 * @return string
	 */
	private function get_main_woocommerce_sections_selectors() {
		$selector = '{{WRAPPER}} .e-woocommerce-login-section, {{WRAPPER}} .woocommerce-checkout #customer_details .col-1, {{WRAPPER}} .woocommerce-additional-fields, {{WRAPPER}} .e-checkout__order_review, {{WRAPPER}} .e-coupon-box, {{WRAPPER}} .woocommerce-checkout #payment';
		if ( $this->is_wc_feature_active( 'shipping' ) ) {
			$selector .= ', {{WRAPPER}} .woocommerce-shipping-fields .shipping_address';
		}
		return $selector;
	}

	/**
	 * Get Main Woocommerce Sections Title Selectors
	 *
	 * Get all the 'Title' selectors. There are numerous controls that need these selectors so it was easier to
	 * consolidate them into one function. Especially when updates need to be made.
	 *
	 * @since 2.9.5
	 *
	 * @return string
	 */
	private function get_main_woocommerce_sections_title_selectors() {
		return '{{WRAPPER}} h3#order_review_heading, {{WRAPPER}} .woocommerce-billing-fields h3, {{WRAPPER}} .woocommerce-additional-fields h3';
	}

	/**
	 * Init Gettext Modifications
	 *
	 * Sets the `$gettext_modifications` property used with the `filter_gettext()` in the extended Base_Widget.
	 *
	 * @since 2.9.5
	 */
	protected function init_gettext_modifications() {
		$instance = $this->get_settings_for_display();

		$this->gettext_modifications = [
			'Billing details' => isset( $instance['billing_details_section_title'] ) ? $instance['billing_details_section_title'] : '',
			'Billing &amp; Shipping' => isset( $instance['billing_details_section_title'] ) ? $instance['billing_details_section_title'] : '',
			'Ship to a different address?' => isset( $instance['shipping_details_section_title'] ) ? $instance['shipping_details_section_title'] : '',
			'Additional information' => isset( $instance['additional_information_section_title'] ) ? $instance['additional_information_section_title'] : '',
			'Your order' => isset( $instance['order_summary_section_title'] ) ? $instance['order_summary_section_title'] : '',
			'Have a coupon?' => isset( $instance['coupon_section_title_text'] ) ? $instance['coupon_section_title_text'] : '',
			'Click here to enter your coupon code' => isset( $instance['coupon_section_title_link_text'] ) ? $instance['coupon_section_title_link_text'] : '',
			'Returning customer?' => isset( $instance['returning_customer_section_title'] ) ? $instance['returning_customer_section_title'] : '',
			'Click here to login' => isset( $instance['returning_customer_link_text'] ) ? $instance['returning_customer_link_text'] : '',
			'Create an account?' => isset( $instance['create_account_text'] ) ? $instance['create_account_text'] : '',
		];
	}

	/**
	 * WooCommerce Terms and Conditions Checkbox Text.
	 *
	 * WooCommerce filter is used to apply widget settings to Checkout Terms & Conditions text and link text.
	 *
	 * @since 2.9.5
	 *
	 * @param string $text
	 * @return string
	 */
	public function woocommerce_terms_and_conditions_checkbox_text( $text ) {
		$settings = $this->get_settings_for_display();

		if ( ! isset( $settings['terms_conditions_message_text'] ) || ! isset( $settings['terms_conditions_link_text'] ) ) {
			return $text;
		}

		$message = $settings['terms_conditions_message_text'];
		$link = $settings['terms_conditions_link_text'];

		$terms_page_id = wc_terms_and_conditions_page_id();
		if ( $terms_page_id ) {
			$message .= ' <a href="' . esc_url( get_permalink( $terms_page_id ) ) . '" class="woocommerce-terms-and-conditions-link" target="_blank">' . $link . '</a>';
		}

		return $message;
	}

	/**
	 * Modify Form Field.
	 *
	 * WooCommerce filter is used to apply widget settings to the Checkout forms address fields
	 * from the Billing and Shipping Details widget sections, e.g. label, placeholder, default.
	 *
	 * @since 2.9.5
	 *
	 * @param array $args
	 * @param string $key
	 * @param string $value
	 * @return array
	 */
	public function modify_form_field( $args, $key, $value ) {
		$reformatted_form_fields = $this->get_reformatted_form_fields();

		// Check if we need to modify the args of this form field.
		if ( isset( $reformatted_form_fields[ $key ] ) ) {
			$apply_fields = [
				'label',
				'placeholder',
				'default',
			];

			foreach ( $apply_fields as $field ) {
				if ( ! empty( $reformatted_form_fields[ $key ][ $field ] ) ) {
					$args[ $field ] = $reformatted_form_fields[ $key ][ $field ];
				}
			}
		}

		return $args;
	}

	/**
	 * Get Reformatted Form Fields.
	 *
	 * Combines the 3 relevant repeater settings arrays into a one level deep associative array
	 * with the keys that match those that WooCommerce uses for its form fields.
	 *
	 * The result is cached so the conversion only ever happens once.
	 *
	 * @since 2.9.5
	 *
	 * @return array
	 */
	private function get_reformatted_form_fields() {
		if ( ! isset( $this->reformatted_form_fields ) ) {
			$instance = $this->get_settings_for_display();

			// Reformat form repeater field into one usable array.
			$repeater_fields = [
				'billing_details_form_fields',
				'shipping_details_form_fields',
				'additional_information_form_fields',
			];

			$this->reformatted_form_fields = [];

			// Apply other modifications to inputs.
			foreach ( $repeater_fields as $repeater_field ) {
				if ( isset( $instance[ $repeater_field ] ) ) {
					foreach ( $instance[ $repeater_field ] as $item ) {
						if ( ! isset( $item['field_key'] ) ) {
							continue;
						}
						$this->reformatted_form_fields[ $item['field_key'] ] = $item;
					}
				}
			}
		}

		return $this->reformatted_form_fields;
	}

	/**
	 * Render Woocommerce Checkout Login Form
	 *
	 * A custom function to render a login form on the Checkout widget. The default WC Login form
	 * was removed in this file's render() method with:
	 * remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form' );
	 *
	 * And then we are adding this form into the widget at the
	 * 'woocommerce_checkout_before_customer_details' hook.
	 *
	 * We are doing this in order to match the placement of the Login form to the provided design.
	 * WC places these forms ABOVE the checkout form section where as we needed to place them inside the
	 * checkout form section. So we removed the default login form and added our own form.
	 *
	 * @since 3.5.0
	 */
	private function render_woocommerce_checkout_login_form() {
		$settings = $this->get_settings_for_display();
		$button_classes = [ 'woocommerce-button', 'button', 'woocommerce-form-login__submit', 'pp-woocommerce-form-login-submit' ];
		/* if ( $settings['forms_buttons_hover_animation'] ) {
			$button_classes[] = 'elementor-animation-' . $settings['forms_buttons_hover_animation'];
		} */
		$this->add_render_attribute(
			'button_login', [
				'class' => $button_classes,
				'name' => 'login',
				'type' => 'submit',
			]
		);
		?>
		<div class="pp-woocommerce-login-section">
			<div class="elementor-woocommerce-login-messages"></div>
			<div class="woocommerce-form-login-toggle pp-checkout-secondary-title">
				<?php echo esc_html__( 'Returning customer?', 'powerpack' ) . ' <a href="#" class="pp-show-login">' . esc_html__( 'Click here to login', 'powerpack' ) . '</a>'; ?>
			</div>
			<div class="pp-woocommerce-login-anchor" style="display:none;">
				<p class="pp-woocommerce-login-nudge pp-description"><?php echo esc_html__( 'If you have shopped with us before, please enter your details below. If you are a new customer, please proceed to the Billing section.', 'powerpack' ); ?></p>

				<div class="pp-login-wrap">
					<div class="pp-login-wrap-start">
						<p class="form-row form-row-first">
							<label for="username"><?php esc_html_e( 'Email', 'powerpack' ); ?> <span class="required">*</span></label>
							<input type="text" class="input-text" name="username" id="username" autocomplete="username" />
						</p>
						<p class="form-row form-row-last">
							<label for="password"><?php esc_html_e( 'Password', 'powerpack' ); ?> <span class="required">*</span></label>
							<input class="input-text" type="password" name="password" id="password" autocomplete="current-password" />
						</p>
						<div class="clear"></div>
					</div>

					<div class="pp-login-wrap-end">
						<p class="form-row">
							<label for="login" class="pp-login-label">&nbsp;</label>
							<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
							<input type="hidden" name="redirect" value="<?php echo esc_url( get_permalink() ); ?>" />
							<button <?php $this->print_render_attribute_string( 'button_login' ); ?> value="<?php esc_attr_e( 'Login', 'powerpack' ); ?>"><?php esc_html_e( 'Login', 'powerpack' ); ?></button>
						</p>
						<div class="clear"></div>
					</div>
				</div>

				<div class="pp-login-actions-wrap">
					<div class="pp-login-actions-wrap-start">
						<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
							<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span class="elementor-woocomemrce-login-rememberme"><?php esc_html_e( 'Remember me', 'powerpack' ); ?></span>
						</label>
					</div>

					<div class="pp-login-actions-wrap-end">
						<p class="lost_password">
							<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'powerpack' ); ?></a>
						</p>
					</div>
				</div>

			</div>
		</div>
		<?php
	}

	/**
	 * Render Woocommerce Checkout Coupon Form
	 *
	 * A custom function to render a coupon form on the Checkout widget. The default WC coupon form
	 * was removed in this file's render() method with:
	 * remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form' );
	 *
	 * And then we are adding this form into the widget at the
	 * 'woocommerce_checkout_order_review' hook.
	 *
	 * We are doing this in order to match the placement of the coupon form to the provided design.
	 * WC places these forms ABOVE the checkout form section where as we needed to place them inside the
	 * checkout form section. So we removed the default coupon form and added our own form.
	 *
	 * @since 2.9.5
	 */
	private function render_woocommerce_checkout_coupon_form() {
		$settings = $this->get_settings_for_display();
		$button_classes = [ 'woocommerce-button', 'button', 'pp-apply-coupon' ];
		/* if ( $settings['forms_buttons_hover_animation'] ) {
			$button_classes[] = 'elementor-animation-' . $settings['forms_buttons_hover_animation'];
		} */
		$this->add_render_attribute(
			'button_coupon', [
				'class' => $button_classes,
				'name' => 'apply_coupon',
				'type' => 'submit',
			]
		);
		?>
		<div class="pp-coupon-box">
			<p class="pp-woocommerce-coupon-nudge pp-checkout-secondary-title"><?php esc_html_e( 'Have a coupon?', 'powerpack' ); ?> <a href="#" class="pp-show-coupon-form"><?php esc_html_e( 'Click here to enter your coupon code', 'powerpack' ); ?></a></p>
			<div class="pp-coupon-anchor" style="display:none">
				<label class="pp-coupon-anchor-description"><?php esc_html_e( 'If you have a coupon code, please apply it below.', 'powerpack' ); ?></label>
				<div class="form-row">
					<div class="coupon-container-grid">
						<div class="col coupon-col-1 ">
							<input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e( 'Coupon code', 'powerpack' ); ?>" id="coupon_code" value="" />
						</div>
						<div class="col coupon-col-2">
							<button <?php $this->print_render_attribute_string( 'button_coupon' ); ?>><?php esc_html_e( 'Apply', 'powerpack' ); ?></button>
						</div>
					</div>
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Should Render Login
	 *
	 * Decide if the login form should be rendered.
	 * The login form should be rendered if:
	 * 1) The WooCommerce setting is enabled
	 * 2) AND: a logged out user is viewing the page, OR the Editor is open
	 *
	 * @since 2.9.5
	 *
	 * @return boolean
	 */
	private function should_render_login() {
		return 'no' !== get_option( 'woocommerce_enable_checkout_login_reminder' ) && ( ! is_user_logged_in() || PP_Helper::elementor()->editor->is_edit_mode() );
	}

	/**
	 * Should Render Coupon
	 *
	 * Decide if the coupon form should be rendered.
	 * The coupon form should be rendered if:
	 * 1) The WooCommerce setting is enabled
	 * 2) AND: a payment is needed, OR the Editor is open
	 *
	 * @since 2.9.5
	 *
	 * @return boolean
	 */
	private function should_render_coupon() {
		return ( WC()->cart->needs_payment() || PP_Helper::elementor()->editor->is_edit_mode() ) && wc_coupons_enabled();
	}

	/**
	 * WooCommerce Checkout Before Customer Details
	 *
	 * Callback function for the woocommerce_checkout_before_customer_details hook that outputs elements
	 *
	 * This eliminates the need for template overrides.
	 *
	 * @since 2.9.5
	 */
	public function woocommerce_checkout_before_customer_details() {
		?>
		<div class="pp-checkout-container">
			<!--open container-->
			<div class="pp-checkout-column pp-checkout-column-start">
				<!--open column-1-->
		<?php
		if ( $this->should_render_login() ) {
			//$this->render_woocommerce_checkout_login_form();
		}
	}

	/**
	 * Woocommerce Checkout After Customer Details
	 *
	 * Output containing elements. Callback function for the woocommerce_checkout_after_customer_details hook.
	 *
	 * This eliminates the need for template overrides.
	 *
	 * @since 2.9.5
	 */
	public function woocommerce_checkout_after_customer_details() {
		?>
					<!--close column-1-->
				</div>
		<?php
	}

	/**
	 * Woocommerce Checkout Before Order Review Heading 1
	 *
	 * Output containing elements. Callback function for the woocommerce_checkout_before_order_review_heading hook.
	 *
	 * This eliminates the need for template overrides.
	 *
	 * @since 2.9.5
	 */
	public function woocommerce_checkout_before_order_review_heading_1() {
		?>
				<div class="pp-checkout-column pp-checkout-column-end">
					<!--open column-2-->
						<div class="pp-checkout-column-inner pp-sticky-right-column">
							<!--open column-inner-->
		<?php
	}

	/**
	 * Woocommerce Checkout Before Order Review Heading 2
	 *
	 * Output containing elements. Callback function for the woocommerce_checkout_before_order_review_heading hook.
	 *
	 * This eliminates the need for template overrides.
	 *
	 * @since 2.9.5
	 */
	public function woocommerce_checkout_before_order_review_heading_2() {
		?>
							<div class="pp-checkout__order_review">
								<!--open order_review-->
		<?php
	}

	/**
	 * Woocommerce Checkout Order Review
	 *
	 * Output containing elements. Callback function for the woocommerce_checkout_order_review hook.
	 *
	 * This eliminates the need for template overrides.
	 *
	 * @since 2.9.5
	 */
	public function woocommerce_checkout_order_review() {
		?>
									<!--close wc_order_review-->
								</div>
								<!--close order_review-->
							</div>
		<?php
		if ( $this->should_render_coupon() ) {
			//$this->render_woocommerce_checkout_coupon_form();
		}
		?>
							<div class="pp-checkout__order_review-2">
								<!--reopen wc_order_review-2-->
		<?php
	}

	/**
	 * Woocommerce Checkout After Order Review
	 *
	 * Output containing elements. Callback function for the woocommerce_checkout_after_order_review hook.
	 *
	 * This eliminates the need for template overrides.
	 *
	 * @since 2.9.5
	 */
	public function woocommerce_checkout_after_order_review() {
		?>
										<!--close wc_order_review-2-->
						<!--close column-inner-->
					</div>
					<!--close column-2-->
				</div>
				<!--close container-->
			</div>
		<?php
	}

	/**
	 * Add Render Hooks
	 *
	 * Add actions & filters before displaying our widget.
	 *
	 * @since 2.9.5
	 */
	public function add_render_hooks() {
		add_filter( 'woocommerce_form_field_args', [ $this, 'modify_form_field' ], 70, 3 );
		add_filter( 'woocommerce_get_terms_and_conditions_checkbox_text', [ $this, 'woocommerce_terms_and_conditions_checkbox_text' ], 10, 1 );

		add_filter( 'gettext', [ $this, 'filter_gettext' ], 20, 3 );

		add_action( 'woocommerce_checkout_before_customer_details', [ $this, 'woocommerce_checkout_before_customer_details' ], 5 );
		add_action( 'woocommerce_checkout_after_customer_details', [ $this, 'woocommerce_checkout_after_customer_details' ], 95 );
		add_action( 'woocommerce_checkout_before_order_review_heading', [ $this, 'woocommerce_checkout_before_order_review_heading_1' ], 5 );
		add_action( 'woocommerce_checkout_before_order_review_heading', [ $this, 'woocommerce_checkout_before_order_review_heading_2' ], 95 );
		add_action( 'woocommerce_checkout_order_review', [ $this, 'woocommerce_checkout_order_review' ], 15 );
		add_action( 'woocommerce_checkout_after_order_review', [ $this, 'woocommerce_checkout_after_order_review' ], 95 );
	}

	/**
	 * Remove Render Hooks
	 *
	 * Remove actions & filters after displaying our widget.
	 *
	 * @since 2.9.5
	 */
	public function remove_render_hooks() {
		remove_filter( 'woocommerce_form_field_args', [ $this, 'modify_form_field' ], 70 );
		remove_filter( 'woocommerce_get_terms_and_conditions_checkbox_text', [ $this, 'woocommerce_terms_and_conditions_checkbox_text' ], 10 );

		remove_filter( 'gettext', [ $this, 'filter_gettext' ], 20 );

		remove_action( 'woocommerce_checkout_before_customer_details', [ $this, 'woocommerce_checkout_before_customer_details' ], 5 );
		remove_action( 'woocommerce_checkout_after_customer_details', [ $this, 'woocommerce_checkout_after_customer_details' ], 95 );
		remove_action( 'woocommerce_checkout_before_order_review_heading', [ $this, 'woocommerce_checkout_before_order_review_heading_1' ], 5 );
		remove_action( 'woocommerce_checkout_before_order_review_heading', [ $this, 'woocommerce_checkout_before_order_review_heading_2' ], 95 );
		remove_action( 'woocommerce_checkout_order_review', [ $this, 'woocommerce_checkout_order_review' ], 15 );
		remove_action( 'woocommerce_checkout_after_order_review', [ $this, 'woocommerce_checkout_after_order_review' ], 95 );
	}

	private function get_shortcode() {

		$shortcode = sprintf( '[%s %s]', 'woocommerce_checkout', $this->get_render_attribute_string( 'shortcode' ) );

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
		$settings = $this->get_settings();
		$is_editor = PP_Helper::elementor()->editor->is_edit_mode();

		// Simulate a logged out user so that all WooCommerce sections will render in the Editor
		if ( $is_editor ) {
			$store_current_user = wp_get_current_user()->ID;
			wp_set_current_user( 0 );
		}

		// Add actions & filters before displaying our Widget.
		$this->add_render_hooks();

		$this->add_render_attribute(
			'container',
			'class',
			array(
				'pp-woocommerce',
				'pp-woo-checkout',
				'pp-woo-checkout-col-' . $settings['layout'],
				'clearfix',
			)
		);

		if ( 'yes' === $settings['hide_additional_info'] ) {
			add_filter( 'woocommerce_enable_order_notes_field', '__return_false', 9999 );
		}
		?>
		<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'container' ) ); ?>>
			<?php do_action( 'pp_woo_before_checkout_wrap' ); ?>

			<div class="woopack-product-checkout">
				<?php do_action( 'pp_woo_before_checkout_content' ); ?>
				<?php echo do_shortcode( '[woocommerce_checkout]' ); ?>
				<?php do_action( 'pp_woo_after_checkout_content' ); ?>
			</div>

			<?php do_action( 'pp_woo_after_checkout_wrap' ); ?>
		</div>
		<?php
		// Remove actions & filters after displaying our Widget.
		$this->remove_render_hooks();

		// Return to existing logged-in user after widget is rendered.
		if ( $is_editor ) {
			wp_set_current_user( $store_current_user );
		}
	}

	public function render_plain_content() {
		echo $this->get_shortcode(); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
