<?php
/**
 * Class: Jet_Wishlist_Count_Button
 * Name: Wishlist Count Button
 * Slug: jet-wishlist-count-button
 */

namespace Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Color as Scheme_Color;
use Elementor\Core\Schemes\Typography as Scheme_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Jet_Wishlist_Count_Button extends Jet_CW_Base {

	public function get_name() {
		return 'jet-wishlist-count-button';
	}

	public function get_title() {
		return esc_html__( 'Wishlist Count Button', 'jet-cw' );
	}

	public function get_icon() {
		return 'jet-cw-icon-wishlist-count';
	}

	public function get_jet_help_url() {
		return 'https://crocoblock.com/knowledge-base/articles/how-to-adjust-the-wishlist-settings-for-woocommerce-shop-using-jetcomparewishlist/';
	}

	public function get_categories() {
		return array( 'jet-cw' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_button_content',
			[
				'label' => __( 'Button', 'jet-cw' ),
			]
		);

		$this->add_control(
			'button_label',
			[
				'label'   => __( 'Label', 'jet-cw' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Wishlist', 'jet-cw' ),
			]
		);

		$this->add_control(
			'use_button_icon',
			[
				'label'     => __( 'Icon', 'jet-cw' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => __( 'Show', 'jet-cw' ),
				'label_off' => __( 'Hide', 'jet-cw' ),
				'default'   => 'yes',
			]
		);

		$this->add_control(
			'button_icon_position',
			[
				'label'       => __( 'Position', 'jet-cw' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => [
					'left'   => __( 'Left', 'jet-cw' ),
					'top'    => __( 'Top', 'jet-cw' ),
					'right'  => __( 'Right', 'jet-cw' ),
					'bottom' => __( 'Bottom', 'jet-cw' ),
				],
				'default'     => 'left',
				'render_type' => 'template',
				'condition'   => [
					'use_button_icon' => 'yes',
				],
			]
		);

		$this->__add_advanced_icon_control(
			'button_icon',
			[
				'label'       => __( 'Icon', 'jet-cw' ),
				'type'        => Controls_Manager::ICON,
				'label_block' => true,
				'file'        => '',
				'default'     => 'fa fa-heart-o',
				'fa5_default' => [
					'value'   => 'far fa-heart',
					'library' => 'fa-regular',
				],
				'separator'   => 'after',
				'condition'   => [
					'use_button_icon' => 'yes',
				],
			]
		);

		$this->add_control(
			'show_count',
			[
				'label'     => __( 'Count', 'jet-cw' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => __( 'Show', 'jet-cw' ),
				'label_off' => __( 'Hide', 'jet-cw' ),
				'default'   => 'yes',
			]
		);

		$this->add_control(
			'hide_empty_count',
			[
				'label'     => __( 'Hide Empty', 'jet-cw' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => [
					'show_count' => 'yes',
				],
			]
		);

		$this->add_control(
			'count_format',
			[
				'label'       => __( 'Format', 'jet-cw' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '%s',
				'description' => __( 'Display format for count items that added to wishlist.', 'jet-cw' ),
				'condition'   => [
					'show_count' => 'yes',
				],
			]
		);

		$this->add_control(
			'count_position',
			[
				'label'     => __( 'Position', 'jet-cw' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'top-right',
				'options'   => [
					'top-right'     => __( 'Top Right', 'jet-cw' ),
					'center-right'  => __( 'Center Right', 'jet-cw' ),
					'bottom-right'  => __( 'Bottom Right', 'jet-cw' ),
					'bottom-center' => __( 'Bottom Center', 'jet-cw' ),
					'bottom-left'   => __( 'Bottom Left', 'jet-cw' ),
					'center-left'   => __( 'Center Left', 'jet-cw' ),
					'top-left'      => __( 'Top Left', 'jet-cw' ),
					'top-center'    => __( 'Top Center', 'jet-cw' ),
					'center'        => __( 'Center', 'jet-cw' ),
				],
				'condition' => [
					'show_count' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		$css_scheme = apply_filters(
			'jet-wishlist-button/wishlist-count-button/css-scheme',
			array(
				'button'    => '.jet-wishlist-count-button__link',
				'container' => '.jet-wishlist-count-button__wrapper',
				'icon'      => '.jet-wishlist-count-button__icon',
				'count'     => '.jet-wishlist-count-button__count',
			)
		);

		$this->start_controls_section(
			'section_button_style',
			[
				'label' => __( 'Button', 'jet-cw' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'custom_size',
			[
				'label' => __( 'Custom Size', 'jet-cw' ),
				'type'  => Controls_Manager::SWITCHER,
			]
		);

		$this->add_responsive_control(
			'button_custom_width',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Width', 'jet-cw' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
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
					'custom_size' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'button_custom_height',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Height', 'jet-cw' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
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
					'custom_size' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'button_typography',
				'selector' => '{{WRAPPER}}  ' . $css_scheme['button'],
			]
		);

		$this->start_controls_tabs( 'button_style_tabs' );

		$this->start_controls_tab(
			'button_normal_styles',
			array(
				'label' => esc_html__( 'Normal', 'jet-cw' ),
			)
		);

		$this->add_control(
			'button_normal_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['button'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'wishlist_button_normal_background',
			[
				'label'     => __( 'Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['button'] => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'button_hover_styles',
			array(
				'label' => esc_html__( 'Hover', 'jet-cw' ),
			)
		);

		$this->add_control(
			'button_hover_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['button'] . ':hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'wishlist_button_hover_background',
			[
				'label'     => __( 'Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['button'] . ':hover' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'button_border_hover_color',
			array(
				'label'     => esc_html__( 'Border Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['button'] . ':hover ' => 'border-color: {{VALUE}}',
				),
				'condition' => array(
					'button_border_border!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'button_box_shadow_hover',
				'selector' => '{{WRAPPER}} ' . $css_scheme['button'] . ':hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'button_border',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} ' . $css_scheme['button'],
			]
		);

		$this->add_control(
			'button_border_radius',
			[
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Border Radius', 'jet-cw' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['button'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'button_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['button'],
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Padding', 'jet-cw' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['button'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_margin',
			[
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Margin', 'jet-cw' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['button'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_alignment',
			[
				'label'     => __( 'Alignment', 'jet-cw' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'left',
				'options'   => jet_cw_tools()->get_available_horizontal_alignment(),
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['container'] => 'text-align: {{VALUE}};',
				],
				'classes'   => 'elementor-control-align',
			]
		);

		$this->add_control(
			'button_icon_heading',
			[
				'label'     => __( 'Icon', 'jet-cw' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'use_button_icon!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'icon_font_size',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Size', 'jet-cw' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', 'rem' ] ),
				'range'      => [
					'px' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['icon'] => 'font-size: {{SIZE}}{{UNIT}}',
				],
				'condition'  => [
					'use_button_icon!' => '',
				],
			]
		);

		$this->start_controls_tabs(
			'tabs_icon_styles',
			[
				'condition' => [
					'use_button_icon!' => '',
				],
			]
		);

		$this->start_controls_tab(
			'tab_icon_normal',
			array(
				'label' => esc_html__( 'Normal', 'jet-cw' ),
			)
		);

		$this->add_control(
			'normal_icon_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['icon'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_icon_hover',
			array(
				'label' => esc_html__( 'Hover', 'jet-cw' ),
			)
		);

		$this->add_control(
			'hover_icon_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['button'] . ':hover ' . $css_scheme['icon'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'icon_margin',
			[
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Margin', 'jet-cw' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'separator'  => 'before',
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['icon'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'use_button_icon!' => '',
				],
			]
		);

		$this->add_control(
			'count_style_heading',
			[
				'label'     => __( 'Count', 'jet-cw' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'show_count!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'count_size',
			[
				'label'      => __( 'Size', 'jet-cw' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 1,
						'max' => 50,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['count'] => 'font-size: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'show_count!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'count_width',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Width', 'jet-cw' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'range'      => [
					'px' => [
						'min' => 10,
						'max' => 200,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['count'] => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'show_count!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'count_height',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Height', 'jet-cw' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'range'      => [
					'px' => [
						'min' => 10,
						'max' => 200,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['count'] => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'show_count!' => '',
				],
			]
		);

		$this->start_controls_tabs(
			'tabs_count_styles',
			[
				'condition' => [
					'show_count!' => '',
				],
			]
		);

		$this->start_controls_tab(
			'tab_count_normal',
			array(
				'label' => esc_html__( 'Normal', 'jet-cw' ),
			)
		);

		$this->add_control(
			'normal_count_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['count'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'normal_count_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['count'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_count_hover',
			array(
				'label' => esc_html__( 'Hover', 'jet-cw' ),
			)
		);

		$this->add_control(
			'hover_count_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['button'] . ':hover ' . $css_scheme['count'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'hover_count_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['button'] . ':hover ' . $css_scheme['count'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'hover_count_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['button'] . ':hover ' . $css_scheme['count'] => 'border-color: {{VALUE}}',
				),
				'condition' => array(
					'count_border_border!' => '',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'count_border',
				'selector'  => '{{WRAPPER}} ' . $css_scheme['count'],
				'separator' => 'before',
				'condition' => [
					'show_count!' => '',
				],
			]
		);

		$this->add_control(
			'count_border_radius',
			[
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Border Radius', 'jet-cw' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['count'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'show_count!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'count_margin',
			[
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Margin', 'jet-cw' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['count'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'show_count!' => '',
				],
			]
		);

		$this->end_controls_section();
	}

	public static function render_callback( $settings = array() ) {

		$selector = 'a.jet-wishlist-count-button__link[data-widget-id="' . $settings['_widget_id'] . '"]';

		jet_cw()->widgets_store->store_widgets_types( 'jet-wishlist-count-button', $selector, $settings, 'wishlist' );

		echo '<div class="jet-wishlist-count-button__wrapper">';

		jet_cw_widgets_functions()->get_wishlist_count_button( $settings );

		echo '</div>';

	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		$widget_settings = [
			'button_icon_position' => $settings['button_icon_position'],
			'use_button_icon'      => $settings['use_button_icon'],
			'button_icon'          => htmlspecialchars( $this->__render_icon( 'button_icon', '%s', '', false ) ),
			'button_label'         => esc_html__( $settings['button_label'], 'jet-cw' ),
			'show_count'           => $settings['show_count'],
			'hide_empty_count'     => $settings['hide_empty_count'],
			'count_format'         => wp_kses_post( $settings['count_format'] ),
			'count_position'       => $settings['count_position'],
			'_widget_id'           => $this->get_id(),
		];

		$this->__open_wrap();

		echo self::render_callback( $widget_settings );

		$this->__close_wrap();

	}

}
