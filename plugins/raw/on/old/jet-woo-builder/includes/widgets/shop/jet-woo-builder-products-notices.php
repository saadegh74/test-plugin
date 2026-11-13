<?php
/**
 * Class: Jet_Woo_Builder_Products_Notices
 * Name: Products Notices
 * Slug: jet-woo-builder-products-notices
 */

namespace Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Jet_Woo_Builder_Products_Notices extends Jet_Woo_Builder_Base {

	public function get_name() {
		return 'jet-woo-builder-products-notices';
	}

	public function get_title() {
		return __( 'Products Notices', 'jet-woo-builder' );
	}

	public function get_icon() {
		return 'jet-woo-builder-icon-shop-notice';
	}

	public function get_jet_help_url() {
		return 'https://crocoblock.com/knowledge-base/articles/jetwoobuilder-how-to-create-and-set-a-shop-page-template/';
	}

	public function show_in_panel() {
		return $this->is_widget_visible( 'shop' );
	}

	protected function register_controls() {

		$css_scheme = apply_filters(
			'jet-woo-builder/products-notices/css-scheme',
			[
				'success' => '.woocommerce-message',
				'error'   => '.woocommerce-error',
				'info'    => '.woocommerce-info',
				'button'  => '.button.wc-forward',
			]
		);

		$notice_selector = '{{WRAPPER}} ' . $css_scheme['success'] . ', {{WRAPPER}} ' . $css_scheme['error'] . ', {{WRAPPER}} ' . $css_scheme['info'];
		$button_selector = '{{WRAPPER}} ' . $css_scheme['success'] . ' ' . $css_scheme['button'] . ', {{WRAPPER}} ' . $css_scheme['error'] . ' ' . $css_scheme['button'] . ', {{WRAPPER}} ' . $css_scheme['info'] . ' ' . $css_scheme['button'];
		$button_hover_selector = '{{WRAPPER}} ' . $css_scheme['success'] . ' ' . $css_scheme['button'] . ':hover, {{WRAPPER}} ' . $css_scheme['error'] . ' ' . $css_scheme['button'] . ':hover, {{WRAPPER}} ' . $css_scheme['info'] . ' ' . $css_scheme['button'] . ':hover';

		$this->start_controls_section(
			'products_notices_style_section',
			[
				'label' => __( 'Notices', 'jet-woo-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'products_notices_typography',
				'selector' => $notice_selector,
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'products_notices_border',
				'selector' => $notice_selector,
			]
		);

		$this->add_responsive_control(
			'products_notices_border_radius',
			[
				'label'      => __( 'Border Radius', 'jet-woo-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					$notice_selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'products_notices_box_shadow',
				'selector' => $notice_selector,
			]
		);

		$this->add_responsive_control(
			'products_notices_padding',
			[
				'label'      => __( 'Padding', 'jet-woo-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					$notice_selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'products_notices_spacing',
			[
				'label'      => __( 'Spacing', 'jet-woo-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em' ] ),
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min'  => 0,
						'max'  => 10,
						'step' => 0.1,
					],
				],
				'selectors'  => [
					$notice_selector => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'products_notices_alignment',
			[
				'label'     => __( 'Alignment', 'jet-woo-builder' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => jet_woo_builder_tools()->get_available_h_align_types(),
				'selectors' => [
					$notice_selector => 'text-align: {{VALUE}};',
				],
				'classes'   => 'elementor-control-align',
			]
		);

		$this->end_controls_section();

		$notice_types = [
			'success' => __( 'Success', 'jet-woo-builder' ),
			'error'   => __( 'Error', 'jet-woo-builder' ),
			'info'    => __( 'Info', 'jet-woo-builder' ),
		];

		foreach ( $notice_types as $type => $label ) {
			$selector = '{{WRAPPER}} ' . $css_scheme[ $type ];

			$this->start_controls_section(
				'products_notices_' . $type . '_style_section',
				[
					'label' => $label,
					'tab'   => Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_control(
				'products_notices_' . $type . '_text_color',
				[
					'label'     => __( 'Text Color', 'jet-woo-builder' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						$selector => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'products_notices_' . $type . '_background_color',
				[
					'label'     => __( 'Background Color', 'jet-woo-builder' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						$selector => 'background-color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'products_notices_' . $type . '_accent_color',
				[
					'label'     => __( 'Accent Color', 'jet-woo-builder' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						$selector             => 'border-top-color: {{VALUE}};',
						$selector . '::before' => 'color: {{VALUE}};',
					],
				]
			);

			$this->end_controls_section();
		}

		$this->start_controls_section(
			'products_notices_button_style_section',
			[
				'label' => __( 'Button', 'jet-woo-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'products_notices_button_typography',
				'selector' => $button_selector,
			]
		);

		$this->start_controls_tabs( 'products_notices_button_style_tabs' );

		$this->start_controls_tab(
			'products_notices_button_normal_style_tab',
			[
				'label' => __( 'Normal', 'jet-woo-builder' ),
			]
		);

		$this->add_control(
			'products_notices_button_color',
			[
				'label'     => __( 'Text Color', 'jet-woo-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					$button_selector => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'products_notices_button_background_color',
			[
				'label'     => __( 'Background Color', 'jet-woo-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					$button_selector => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'products_notices_button_hover_style_tab',
			[
				'label' => __( 'Hover', 'jet-woo-builder' ),
			]
		);

		$this->add_control(
			'products_notices_button_hover_color',
			[
				'label'     => __( 'Text Color', 'jet-woo-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					$button_hover_selector => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'products_notices_button_hover_background_color',
			[
				'label'     => __( 'Background Color', 'jet-woo-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					$button_hover_selector => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'products_notices_button_hover_border_color',
			[
				'label'     => __( 'Border Color', 'jet-woo-builder' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					'products_notices_button_border_border!' => '',
				],
				'selectors' => [
					$button_hover_selector => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'products_notices_button_hover_box_shadow',
				'selector' => $button_hover_selector,
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'products_notices_button_border',
				'selector' => $button_selector,
			]
		);

		$this->add_responsive_control(
			'products_notices_button_border_radius',
			[
				'label'      => __( 'Border Radius', 'jet-woo-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					$button_selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'products_notices_button_box_shadow',
				'selector' => $button_selector,
			]
		);

		$this->add_responsive_control(
			'products_notices_button_padding',
			[
				'label'      => __( 'Padding', 'jet-woo-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					$button_selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'products_notices_button_transition_duration',
			[
				'label'     => __( 'Transition Duration', 'jet-woo-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 0,
						'max'  => 3,
						'step' => 0.1,
					],
				],
				'selectors' => [
					$button_selector => 'transition-duration: {{SIZE}}s;',
				],
			]
		);

		$this->end_controls_section();

	}

	protected function render() {

		$this->__open_wrap();

		if ( jet_woo_builder()->elementor_views->in_elementor() ) {
			$this->render_editor_preview();
		} else {
			wc_print_notices();
		}

		$this->__close_wrap();

	}

	/**
	 * Render notices preview in the Elementor editor.
	 *
	 * @return void
	 */
	protected function render_editor_preview() {
		?>
		<div class="woocommerce-message" role="status">
			<a href="#" class="button wc-forward"><?php esc_html_e( 'View cart', 'jet-woo-builder' ); ?></a>
			<?php esc_html_e( 'This is a success message.', 'jet-woo-builder' ); ?>
		</div>
		<ul class="woocommerce-error" role="alert">
			<li><?php esc_html_e( 'This is an error message.', 'jet-woo-builder' ); ?></li>
		</ul>
		<div class="woocommerce-info" role="status">
			<?php esc_html_e( 'This is an informational message.', 'jet-woo-builder' ); ?>
		</div>
		<?php
	}
}
