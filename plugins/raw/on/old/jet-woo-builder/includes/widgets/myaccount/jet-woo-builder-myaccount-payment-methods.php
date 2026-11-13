<?php
/**
 * Class: Jet_Woo_Builder_MyAccount_Payment_Methods
 * Name: Account Payment Methods
 * Slug: jet-myaccount-payment-methods
 */

namespace Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Jet_Woo_Builder_MyAccount_Payment_Methods extends Jet_Woo_Builder_Base {

	public function get_name() {
		return 'jet-myaccount-payment-methods';
	}

	public function get_title() {
		return __( 'Account Payment Methods', 'jet-woo-builder' );
	}

	public function get_icon() {
		return 'jet-woo-builder-icon-my-account-payment-methods';
	}

	public function get_jet_help_url() {
		return 'https://crocoblock.com/knowledge-base/articles/jetwoobuilder-how-to-create-my-account-page-template/';
	}

	public function show_in_panel() {
		return $this->is_widget_visible( 'myaccount' );
	}

	protected function register_controls() {

		$css_scheme = apply_filters(
			'jet-woo-builder/jet-woo-builder-myaccount-payment-methods/css-scheme',
			[
				'table_heading' => 'table.account-payment-methods-table thead tr th',
				'table_cells'   => 'table.account-payment-methods-table tbody tr td',
			]
		);

		$this->start_controls_section(
			'myaccount_payment_methods_table_heading_styles',
			[
				'label' => __( 'Table Heading', 'jet-woo-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'myaccount_payment_methods_table_heading_typography',
				'label'    => esc_html__( 'Typography', 'jet-woo-builder' ),
				'selector' => '{{WRAPPER}} ' . $css_scheme['table_heading'],
			)
		);

		$this->add_control(
			'myaccount_payment_methods_table_heading_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-woo-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['table_heading'] => 'color: {{VALUE}}',
				),
			)
		);

		jet_woo_builder_common_controls()->register_table_cell_style_controls( $this, 'myaccount_payment_methods_table_heading', $css_scheme['table_heading'] );

		$this->end_controls_section();

		$this->start_controls_section(
			'myaccount_payment_methods_table_cells_styles',
			[
				'label' => __( 'Table Cells', 'jet-woo-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'myaccount_payment_methods_cells_typography',
				'label'    => esc_html__( 'Typography', 'jet-woo-builder' ),
				'selector' => '{{WRAPPER}} ' . $css_scheme['table_cells'],
			)
		);

		$this->add_control(
			'myaccount_payment_methods_table_cells_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-woo-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['table_cells'] => 'color: {{VALUE}}',
				),
			)
		);

		jet_woo_builder_common_controls()->register_table_cell_style_controls( $this, 'myaccount_payment_methods_table_cells', $css_scheme['table_cells'] );

		$this->end_controls_section();

		$this->start_controls_section(
			'myaccount_payment_methods_button_styles',
			array(
				'label' => esc_html__( 'Buttons', 'jet-woo-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		jet_woo_builder_common_controls()->register_button_style_controls( $this, 'myaccount_payment_methods', '.button' );

		$this->end_controls_section();

	}

	protected function render() {

		$this->__open_wrap();

		include $this->get_template( 'myaccount/payment-methods.php' );

		$this->__close_wrap();

	}
}
