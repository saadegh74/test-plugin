<?php
/**
 * Compare Integration Class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_CW_Compare_Integration' ) ) {

	/**
	 * Define Jet_CW_Compare_Integration class
	 */
	class Jet_CW_Compare_Integration {

		public function __construct() {

			// Add compare buttons html to Products Grid/List widgets from JetWooBuilder
			add_action( 'jet-woo-builder/templates/jet-woo-products/compare-button', [ $this, 'add_compare_button' ], 10, 1 );
			add_action( 'jet-woo-builder/templates/jet-woo-products-list/compare-button', [ $this, 'add_compare_button' ], 10, 1 );

			// Add compare button content controls to Products Grid/List widgets from JetWooBuilder.
			add_action( 'elementor/element/jet-woo-products/section_general/after_section_end', [ $this, 'register_compare_button_content_controls' ], 10, 2 );
			add_action( 'elementor/element/jet-woo-products-list/section_general/after_section_end', [ $this, 'register_compare_button_content_controls' ], 10, 2 );

			// Add compare buttons style controls to Products Grid/List widgets from JetWooBuilder
			add_action( 'elementor/element/jet-woo-products/section_button_style/after_section_end', [ $this, 'register_compare_button_style_controls' ], 10, 2 );
			add_action( 'elementor/element/jet-woo-products-list/section_button_style/after_section_end', [ $this, 'register_compare_button_style_controls' ], 10, 2 );

			if ( filter_var( jet_cw()->settings->get( 'add_default_compare_button' ), FILTER_VALIDATE_BOOLEAN ) ) {
				// Add compare buttons style controls to Archive Products widget from ElementorPro
				add_action( 'elementor/element/woocommerce-archive-products/section_design_box/after_section_end', array( $this, 'register_compare_button_style_controls' ), 10, 2 );

				// Add compare button html to default WooCommerce content product template
				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'add_compare_button_default' ), 11 );
				add_action( 'woocommerce_single_product_summary', array( $this, 'add_compare_button_default' ), 31 );
			}

			// Processing compare button icons
			add_filter( 'jet-woo-builder/jet-woo-products-grid/settings', array( $this, 'compare_button_icon' ), 10, 2 );
			add_filter( 'jet-woo-builder/jet-woo-products-list/settings', array( $this, 'compare_button_icon' ), 10, 2 );

		}

		/**
		 * Add compare button.
		 *
		 * Renders the widget in JetWooBuilder plugin widgets.
		 *
		 * @since 1.0.0
		 * @since 1.5.8.1 Added style enqueue.
		 *
		 * @param array $settings List of widget settings.
		 *
		 * @return void
		 */
		public function add_compare_button( $settings = [] ) {

			$widget_settings = [
				'button_icon_position' => $settings['compare_button_icon_position'],
				'use_button_icon'      => $settings['compare_use_button_icon'],
				'button_icon_normal'   => $settings['selected_compare_button_icon_normal'],
				'button_label_normal'  => $settings['compare_button_label_normal'],
				'use_as_remove_button' => $settings['compare_use_as_remove_button'],
				'button_icon_added'    => $settings['selected_compare_button_icon_added'],
				'button_label_added'   => $settings['compare_button_label_added'],
				'_widget_id'           => $settings['_widget_id'],
			];

			jet_cw()->compare_render->render_compare_button( $widget_settings );

			wp_enqueue_style( 'jet-cw-widget-cw-button' );

		}

		/**
		 * Returns wishlist button icon settings
		 *
		 * @param $settings
		 * @param $widget
		 *
		 * @return mixed
		 */
		public function compare_button_icon( $settings, $widget ) {

			if ( isset( $settings['selected_compare_button_icon_normal'] ) || isset( $settings['compare_button_icon_normal'] ) ) {
				$settings['selected_compare_button_icon_normal'] = htmlspecialchars( $widget->__render_icon( 'compare_button_icon_normal', '%s', '', false ) );
			}

			if ( isset( $settings['selected_compare_button_icon_added'] ) || isset( $settings['compare_button_icon_added'] ) ) {
				$settings['selected_compare_button_icon_added'] = htmlspecialchars( $widget->__render_icon( 'compare_button_icon_added', '%s', '', false ) );
			}

			return $settings;
		}

		/**
		 * Add compare button default.
		 *
		 * Renders the widget in default WooCommerce templates.
		 *
		 * @since 1.0.0
		 * @since 1.5.8 Added style enqueue.
		 *
		 * @return void
		 */
		public function add_compare_button_default() {

			$widget_settings = [
				'button_icon_position' => 'left',
				'use_button_icon'      => false,
				'button_icon_normal'   => '',
				'button_label_normal'  => __( 'Add To Compare', 'jet-cw' ),
				'use_as_remove_button' => false,
				'button_icon_added'    => '',
				'button_label_added'   => __( 'View Compare', 'jet-cw' ),
				'_widget_id'           => 'default',
			];

			jet_cw()->compare_render->render_compare_button( $widget_settings );

			wp_enqueue_style( 'jet-cw-widget-cw-button' );

		}

		/**
		 * Register compare button content controls.
		 *
		 * Register compare button content controls in Elementor editor.
		 *
		 * @since  1.5.0
		 * @access public
		 *
		 * @param object $obj  Widget instance.
		 * @param array  $args Specific widget arguments list.
		 */
		public function register_compare_button_content_controls( $obj = null, $args = [] ) {

			$obj->start_controls_section(
				'section_compare_content',
				[
					'label' => __( 'Compare', 'jet-cw' ),
				]
			);

			if ( 'jet-woo-products' === $obj->get_name() || 'jet-woo-products-list' === $obj->get_name() ) {
				$obj->add_control(
					'show_compare',
					[
						'label'     => __( 'Compare Button', 'jet-cw' ),
						'type'      => Elementor\Controls_Manager::SWITCHER,
						'label_on'  => __( 'Show', 'jet-cw' ),
						'label_off' => __( 'Hide', 'jet-cw' ),
					]
				);
			} else {
				$obj->add_control(
					'show_compare',
					[
						'label'   => __( 'Compare Button', 'jet-cw' ),
						'type'    => \Elementor\Controls_Manager::HIDDEN,
						'default' => 'yes',
					]
				);
			}

			$obj->add_control(
				'compare_use_button_icon',
				[
					'label'     => __( 'Icon', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::SWITCHER,
					'label_on'  => __( 'Show', 'jet-cw' ),
					'label_off' => __( 'Hide', 'jet-cw' ),
					'default'   => 'yes',
					'condition' => [
						'show_compare!' => '',
					],
				]
			);

			$obj->add_control(
				'compare_button_icon_position',
				[
					'label'       => __( 'Icon Position', 'jet-cw' ),
					'type'        => Elementor\Controls_Manager::SELECT,
					'options'     => [
						'left'   => __( 'Left', 'jet-cw' ),
						'top'    => __( 'Top', 'jet-cw' ),
						'right'  => __( 'Right', 'jet-cw' ),
						'bottom' => __( 'Bottom', 'jet-cw' ),
					],
					'default'     => 'left',
					'render_type' => 'template',
					'condition'   => [
						'compare_use_button_icon!' => '',
						'show_compare!'            => '',
					],
				]
			);

			$obj->start_controls_tabs(
				'tabs_compare_button_content',
				[
					'condition' => [
						'show_compare!' => '',
					],
				]
			);

			$obj->start_controls_tab(
				'tab_compare_button_content_normal',
				[
					'label' => __( 'Normal', 'jet-cw' ),
				]
			);

			$obj->__add_advanced_icon_control(
				'compare_button_icon_normal',
				[
					'label'       => __( 'Icon', 'jet-cw' ),
					'type'        => Elementor\Controls_Manager::ICON,
					'label_block' => true,
					'file'        => '',
					'default'     => 'fa fa-compress',
					'fa5_default' => [
						'value'   => 'fas fa-compress',
						'library' => 'fa-solid',
					],
					'condition'   => [
						'compare_use_button_icon!' => '',
					],
				]
			);

			$obj->add_control(
				'compare_button_label_normal',
				[
					'label'   => __( 'Label', 'jet-cw' ),
					'type'    => Elementor\Controls_Manager::TEXT,
					'default' => __( 'Add To Compare', 'jet-cw' ),
				]
			);

			$obj->end_controls_tab();

			$obj->start_controls_tab(
				'tab_compare_button_content_added',
				[
					'label' => __( 'Added', 'jet-cw' ),
				]
			);

			$obj->add_control(
				'compare_use_as_remove_button',
				[
					'label' => __( 'Use as Remove Button', 'jet-cw' ),
					'type'  => Elementor\Controls_Manager::SWITCHER,
				]
			);

			$obj->__add_advanced_icon_control(
				'compare_button_icon_added',
				[
					'label'       => __( 'Icon', 'jet-cw' ),
					'type'        => Elementor\Controls_Manager::ICON,
					'label_block' => true,
					'file'        => '',
					'default'     => 'fa fa-check',
					'fa5_default' => [
						'value'   => 'fas fa-check',
						'library' => 'fa-solid',
					],
					'condition'   => [
						'compare_use_button_icon!' => '',
					],
				]
			);

			$obj->add_control(
				'compare_button_label_added',
				[
					'label'   => __( 'Label', 'jet-cw' ),
					'type'    => Elementor\Controls_Manager::TEXT,
					'default' => __( 'View Compare', 'jet-cw' ),
				]
			);

			$obj->end_controls_tab();

			$obj->end_controls_tabs();

			if ( 'jet-woo-products' === $obj->get_name() || 'jet-woo-products-list' === $obj->get_name() ) {
				$obj->add_responsive_control(
					'compare_button_order',
					[
						'type'      => Elementor\Controls_Manager::NUMBER,
						'label'     => __( 'Order', 'jet-cw' ),
						'default'   => 1,
						'min'       => 1,
						'max'       => 10,
						'step'      => 1,
						'separator' => 'before',
						'selectors' => [
							'{{WRAPPER}} .jet-compare-button__container' => 'order: {{VALUE}}',
						],
						'condition' => [
							'show_compare!' => '',
						],
					]
				);
			}

			$obj->end_controls_section();

		}

		/**
		 * Register compare button style controls.
		 *
		 * Register compare button style controls in Elementor editor.
		 *
		 * @since  1.5.0
		 * @access public
		 *
		 * @param object $obj  Widget instance.
		 * @param array  $args Specific widget arguments list.
		 */
		public function register_compare_button_style_controls( $obj = null, $args = [] ) {

			$css_scheme = apply_filters(
				'jet-compare-button/compare-button/css-scheme',
				[
					'added'        => '.added-to-compare',
					'container'    => '.jet-compare-button__container',
					'button'       => '.jet-compare-button__link',
					'plane_normal' => '.jet-compare-button__plane-normal',
					'plane_added'  => '.jet-compare-button__plane-added',
					'state_normal' => '.jet-compare-button__state-normal',
					'state_added'  => '.jet-compare-button__state-added',
					'icon_normal'  => '.jet-compare-button__state-normal .jet-compare-button__icon',
					'label_normal' => '.jet-compare-button__state-normal .jet-compare-button__label',
					'icon_added'   => '.jet-compare-button__state-added .jet-compare-button__icon',
					'label_added'  => '.jet-compare-button__state-added .jet-compare-button__label',
				]
			);

			$obj->start_controls_section(
				'section_button_compare_general_style',
				[
					'label'     => __( 'Compare', 'jet-cw' ),
					'tab'       => Elementor\Controls_Manager::TAB_STYLE,
					'condition' => [
						'show_compare!' => '',
					],
				]
			);

			$obj->add_control(
				'compare_custom_size',
				[
					'label' => __( 'Custom Size', 'jet-cw' ),
					'type'  => Elementor\Controls_Manager::SWITCHER,
				]
			);

			$obj->add_responsive_control(
				'compare_button_custom_width',
				[
					'type'       => Elementor\Controls_Manager::SLIDER,
					'label'      => __( 'Width', 'jet-cw' ),
					'size_units' => is_callable( [ $obj, 'set_custom_size_unit' ] ) ? $obj->set_custom_size_unit( [ 'px', 'em', '%' ] ) : [ 'px', 'em', '%' ],
					'range'      => [
						'px' => [
							'min' => 40,
							'max' => 1000,
						],
						'%'  => [
							'min' => 0,
							'max' => 100,
						],
					],
					'selectors'  => [
						'{{WRAPPER}} ' . $css_scheme['button'] => 'width: {{SIZE}}{{UNIT}};',
					],
					'condition'  => [
						'compare_custom_size' => 'yes',
					],
				]
			);

			$obj->add_responsive_control(
				'compare_button_custom_height',
				[
					'type'       => Elementor\Controls_Manager::SLIDER,
					'label'      => __( 'Height', 'jet-cw' ),
					'size_units' => is_callable( [ $obj, 'set_custom_size_unit' ] ) ? $obj->set_custom_size_unit( [ 'px', 'em', '%' ] ) : [ 'px', 'em', '%' ],
					'range'      => [
						'px' => [
							'min' => 10,
							'max' => 1000,
						],
						'%'  => [
							'min' => 0,
							'max' => 100,
						],
					],
					'selectors'  => [
						'{{WRAPPER}} ' . $css_scheme['button'] => 'height: {{SIZE}}{{UNIT}};',
					],
					'condition'  => [
						'compare_custom_size' => 'yes',
					],
				]
			);

			$obj->add_group_control(
				Elementor\Group_Control_Typography::get_type(),
				[
					'name'     => 'compare_button_typography',
					'selector' => '{{WRAPPER}} ' . $css_scheme['button'] . ',{{WRAPPER}} ' . $css_scheme['label_normal'] . ',{{WRAPPER}} ' . $css_scheme['label_added'],
				]
			);

			$obj->start_controls_tabs( 'compare_button_style_tabs' );

			$obj->start_controls_tab(
				'compare_button_normal_styles',
				[
					'label' => __( 'Normal', 'jet-cw' ),
				]
			);

			$obj->add_control(
				'compare_button_normal_color',
				[
					'label'     => __( 'Color', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['label_normal'] => 'color: {{VALUE}}',
						'{{WRAPPER}} ' . $css_scheme['icon_normal']  => 'color: {{VALUE}}',
					],
				]
			);

			$obj->add_control(
				'compare_button_normal_background',
				[
					'label'     => __( 'Background Color', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['button'] . ' ' . $css_scheme['plane_normal'] => 'background-color: {{VALUE}}',
					],
				]
			);

			$obj->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name'     => 'compare_button_normal_box_shadow',
					'selector' => '{{WRAPPER}} ' . $css_scheme['button'],
				]
			);

			$obj->end_controls_tab();

			$obj->start_controls_tab(
				'compare_button_hover_styles',
				[
					'label' => __( 'Hover', 'jet-cw' ),
				]
			);

			$obj->add_control(
				'compare_button_hover_color',
				[
					'label'     => __( 'Color', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['button'] . ':hover ' . $css_scheme['label_normal'] => 'color: {{VALUE}}',
						'{{WRAPPER}} ' . $css_scheme['button'] . ':hover ' . $css_scheme['icon_normal']  => 'color: {{VALUE}}',
					],
				]
			);

			$obj->add_control(
				'compare_button_hover_background',
				[
					'label'     => __( 'Background Color', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['button'] . ':hover ' . $css_scheme['plane_normal'] => 'background-color: {{VALUE}}',
					],
				]
			);

			$obj->add_control(
				'compare_button_border_hover_color',
				[
					'label'     => __( 'Border Color', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['button'] . ':hover ' . $css_scheme['plane_normal'] => 'border-color: {{VALUE}}',
					],
					'condition' => [
						'compare_button_border_border!' => '',
					],
				]
			);

			$obj->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name'     => 'compare_button_hover_box_shadow',
					'selector' => '{{WRAPPER}} ' . $css_scheme['button'] . ':hover',
				]
			);

			$obj->end_controls_tab();

			$obj->start_controls_tab(
				'compare_button_added_styles',
				[
					'label' => __( 'Added', 'jet-cw' ),
				]
			);

			$obj->add_control(
				'compare_button_added_color',
				[
					'label'     => __( 'Color', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['added'] . $css_scheme['button']                                    => 'color: {{VALUE}}',
						'{{WRAPPER}} ' . $css_scheme['added'] . $css_scheme['button'] . ' ' . $css_scheme['label_added'] => 'color: {{VALUE}}',
						'{{WRAPPER}} ' . $css_scheme['added'] . ' ' . $css_scheme['icon_added']                          => 'color: {{VALUE}}',
					],
				]
			);

			$obj->add_control(
				'compare_button_added_background',
				[
					'label'     => __( 'Background Color', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['added'] . ' ' . $css_scheme['plane_added'] => 'background-color: {{VALUE}}',
					],
				]
			);

			$obj->add_control(
				'compare_button_added_border_color',
				[
					'label'     => __( 'Border Color', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['added'] . ' ' . $css_scheme['plane_added'] => 'border-color: {{VALUE}}',
					],
					'condition' => [
						'compare_button_border_border!' => '',
					],
				]
			);

			$obj->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name'     => 'compare_button_added_box_shadow',
					'selector' => '{{WRAPPER}} ' . $css_scheme['added'],
				]
			);

			$obj->end_controls_tab();

			$obj->end_controls_tabs();

			$obj->add_group_control(
				Elementor\Group_Control_Border::get_type(),
				[
					'name'      => 'compare_button_border',
					'label'     => __( 'Border', 'jet-cw' ),
					'separator' => 'before',
					'selector'  => '{{WRAPPER}} ' . $css_scheme['plane_normal'] . ', ' . '{{WRAPPER}} ' . $css_scheme['plane_added'],
				]
			);

			$obj->add_control(
				'compare_button_border_radius',
				[
					'type'       => Elementor\Controls_Manager::DIMENSIONS,
					'label'      => __( 'Border Radius', 'jet-cw' ),
					'size_units' => is_callable( [ $obj, 'set_custom_size_unit' ] ) ? $obj->set_custom_size_unit( [ 'px', 'em', '%' ] ) : [ 'px', 'em', '%' ],
					'selectors'  => [
						'{{WRAPPER}} ' . $css_scheme['button']       => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						'{{WRAPPER}} ' . $css_scheme['plane_normal'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						'{{WRAPPER}} ' . $css_scheme['plane_added']  => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$obj->add_responsive_control(
				'compare_button_margin',
				[
					'type'       => Elementor\Controls_Manager::DIMENSIONS,
					'label'      => __( 'Margin', 'jet-cw' ),
					'size_units' => is_callable( [ $obj, 'set_custom_size_unit' ] ) ? $obj->set_custom_size_unit( [ 'px', 'em', '%' ] ) : [ 'px', 'em', '%' ],
					'selectors'  => [
						'{{WRAPPER}} ' . $css_scheme['container'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$obj->add_responsive_control(
				'compare_button_padding',
				[
					'type'       => Elementor\Controls_Manager::DIMENSIONS,
					'label'      => __( 'Padding', 'jet-cw' ),
					'size_units' => is_callable( [ $obj, 'set_custom_size_unit' ] ) ? $obj->set_custom_size_unit( [ 'px', 'em', '%' ] ) : [ 'px', 'em', '%' ],
					'selectors'  => [
						'{{WRAPPER}} ' . $css_scheme['button'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$obj->add_responsive_control(
				'compare_button_alignment',
				[
					'label'     => __( 'Alignment', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::CHOOSE,
					'default'   => 'center',
					'options'   => jet_cw_tools()->get_available_flex_horizontal_alignment(),
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['container'] => 'justify-content: {{VALUE}};',
					],
				]
			);

			$obj->add_control(
				'compare_button_icon_heading',
				[
					'label'     => __( 'Icon', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$obj->start_controls_tabs( 'tabs_compare_icon_styles' );

			$obj->start_controls_tab(
				'tab_compare_icon_normal',
				[
					'label' => __( 'Normal', 'jet-cw' ),
				]
			);

			$obj->add_control(
				'normal_compare_icon_color',
				[
					'label'     => __( 'Color', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['icon_normal'] => 'color: {{VALUE}}',
					],
				]
			);

			$obj->add_responsive_control(
				'normal_compare_icon_font_size',
				[
					'type'       => Elementor\Controls_Manager::SLIDER,
					'label'      => __( 'Font Size', 'jet-cw' ),
					'size_units' => is_callable( [ $obj, 'set_custom_size_unit' ] ) ? $obj->set_custom_size_unit( [ 'px', '%', 'em', 'rem' ] ) : [ 'px', '%', 'em', 'rem' ],
					'range'      => [
						'px' => [
							'min' => 1,
							'max' => 100,
						],
					],
					'selectors'  => [
						'{{WRAPPER}} ' . $css_scheme['icon_normal'] => 'font-size: {{SIZE}}{{UNIT}}',
					],
				]
			);

			$obj->add_responsive_control(
				'normal_compare_icon_margin',
				[
					'type'       => Elementor\Controls_Manager::DIMENSIONS,
					'label'      => __( 'Margin', 'jet-cw' ),
					'size_units' => is_callable( [ $obj, 'set_custom_size_unit' ] ) ? $obj->set_custom_size_unit( [ 'px', 'em', '%' ] ) : [ 'px', 'em', '%' ],
					'selectors'  => [
						'{{WRAPPER}} ' . $css_scheme['icon_normal'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$obj->end_controls_tab();

			$obj->start_controls_tab(
				'tab_compare_icon_hover',
				[
					'label' => __( 'Hover', 'jet-cw' ),
				]
			);

			$obj->add_control(
				'compare_icon_color_hover',
				[
					'label'     => __( 'Color', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['button'] . ':hover ' . $css_scheme['icon_normal'] => 'color: {{VALUE}}',
					],
				]
			);

			$obj->end_controls_tab();

			$obj->start_controls_tab(
				'tab_compare_icon_added',
				[
					'label' => __( 'Added', 'jet-cw' ),
				]
			);

			$obj->add_control(
				'compare_icon_color_added',
				[
					'label'     => __( 'Color', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['added'] . $css_scheme['button'] . ' ' . $css_scheme['icon_added'] => 'color: {{VALUE}}',
					],
				]
			);

			$obj->add_responsive_control(
				'compare_icon_font_size_added',
				[
					'type'       => Elementor\Controls_Manager::SLIDER,
					'label'      => __( 'Font Size', 'jet-cw' ),
					'size_units' => is_callable( [ $obj, 'set_custom_size_unit' ] ) ? $obj->set_custom_size_unit( [ 'px', '%', 'em', 'rem' ] ) : [ 'px', '%', 'em', 'rem' ],
					'range'      => [
						'px' => [
							'min' => 1,
							'max' => 100,
						],
					],
					'selectors'  => [
						'{{WRAPPER}} ' . $css_scheme['icon_added'] => 'font-size: {{SIZE}}{{UNIT}}',
					],
				]
			);

			$obj->add_responsive_control(
				'compare_icon_margin_added',
				[
					'type'       => Elementor\Controls_Manager::DIMENSIONS,
					'label'      => __( 'Margin', 'jet-cw' ),
					'size_units' => is_callable( [ $obj, 'set_custom_size_unit' ] ) ? $obj->set_custom_size_unit( [ 'px', 'em', '%' ] ) : [ 'px', 'em', '%' ],
					'selectors'  => [
						'{{WRAPPER}} ' . $css_scheme['icon_added'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$obj->end_controls_tab();

			$obj->end_controls_tabs();

			$obj->end_controls_section();

		}

	}

}