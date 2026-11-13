<?php
/**
 * Class: Jet_Elements_Brands
 * Name: Logo Showcase
 * Slug: jet-brands
 */

namespace Elementor;

use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Jet_Elements_Brands extends Jet_Elements_Base {

	public function get_name() {
		return 'jet-brands';
	}

	public function get_title() {
		return esc_html__( 'Logo Showcase', 'jet-elements' );
	}

	public function get_icon() {
		return 'jet-elements-icon-brands';
	}

	public function get_keywords() {
		return array( 'brands' );
	}

	public function get_jet_help_url() {
		return 'https://crocoblock.com/knowledge-base/articles/how-to-showcase-brands-in-most-stylish-ways-with-jetelements-brands-widget/';
	}

	public function get_categories() {
		return array( 'cherry' );
	}

	protected function is_dynamic_content(): bool {
		return false;
	}

	public function get_style_depends() { 
		return array( 'jet-brands', 'jet-brands-skin' ); 
	}

	public function get_script_depends() {
		return array( 'jet-slick', 'jet-brands' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_brands',
			array(
				'label' => esc_html__( 'Brands', 'jet-elements' ),
			)
		);

		$repeater = new \Elementor\Jet_Elements_Repeater();

		$repeater->add_control(
			'item_image',
			array(
				'label'   => esc_html__( 'Company Logo', 'jet-elements' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array(
					'url' => Utils::get_placeholder_image_src(),
				),
				'dynamic' => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'item_name',
			array(
				'label'   => esc_html__( 'Company Name', 'jet-elements' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'item_desc',
			array(
				'label'   => esc_html__( 'Company Description', 'jet-elements' ),
				'type'    => Controls_Manager::TEXTAREA,
				'dynamic' => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'item_url',
			array(
				'label'   => esc_html__( 'Company URL', 'jet-elements' ),
				'type'    => Controls_Manager::URL,
				'default' => array(
					'url' => '#',
				),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'brands_list',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'item_image' => array(
							'url' => Utils::get_placeholder_image_src(),
						),
						'item_name'  => esc_html__( 'Company #1', 'jet-elements' ),
						'item_url'   => array(
							'url' => '#',
						),
					),
					array(
						'item_image' => array(
							'url' => Utils::get_placeholder_image_src(),
						),
						'item_name'  => esc_html__( 'Company #2', 'jet-elements' ),
						'item_url'   => array(
							'url' => '#',
						),
					),
				),
				'title_field' => '{{{ item_name }}}',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_general',
			array(
				'label' => esc_html__( 'Settings', 'jet-elements' ),
			)
		);

		$this->add_control(
			'display_type',
			array(
				'label'        => esc_html__( 'Display', 'jet-elements' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'grid',
				'options'      => array(
					'grid'   => esc_html__( 'Grid', 'jet-elements' ),
					'slider' => esc_html__( 'Slider', 'jet-elements' ),
				),
				'render_type'  => 'template',
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'   => esc_html__( 'Columns', 'jet-elements' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 4,
				'options' => jet_elements_tools()->get_select_range( 6 ),
				'selectors' => array(
					'{{WRAPPER}} .brands-list--grid .brands-list__item' => 'max-width: calc( 100% / {{VALUE}} );flex: 0 0 calc( 100% / {{VALUE}} ); -webkit-box-flex: 0;
					-ms-flex: 0 0 calc( 100% / {{VALUE}} );',
				),
				'condition' => array(
					'display_type' => 'grid',
				),
			)
		);

		$this->add_responsive_control(
			'row_gap',
			array(
				'label'      => esc_html__( 'Row Gap', 'jet-elements' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
				'selectors'  => array(
					'{{WRAPPER}} .brands-list--grid' => '--jet-brands-row-gap: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'display_type' => 'grid',
				),
			)
		);

		$this->add_responsive_control(
			'slides_to_show',
			array(
				'label'              => esc_html__( 'Slides to Show', 'jet-elements' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => '4',
				'options'            => jet_elements_tools()->get_select_range( 10 ),
				'frontend_available' => true,
				'render_type'        => 'template',
				'condition'          => array(
					'display_type' => 'slider',
				),
			)
		);

		$this->add_responsive_control(
			'slides_to_scroll',
			array(
				'label'              => esc_html__( 'Slides to Scroll', 'jet-elements' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => '1',
				'options'            => jet_elements_tools()->get_select_range( 10 ),
				'frontend_available' => true,
				'render_type'        => 'template',
				'condition'          => array(
					'display_type' => 'slider',
				),
			)
		);

		$this->add_control(
			'autoplay',
			array(
				'label'        => esc_html__( 'Autoplay', 'jet-elements' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jet-elements' ),
				'label_off'    => esc_html__( 'No', 'jet-elements' ),
				'return_value' => 'true',
				'default'      => '',
				'condition'    => array(
					'display_type' => 'slider',
				),
			)
		);

		$this->add_control(
			'autoplay_speed',
			array(
				'label'     => esc_html__( 'Autoplay Speed', 'jet-elements' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 5000,
				'condition' => array(
					'display_type' => 'slider',
					'autoplay'     => 'true',
				),
			)
		);

		$this->add_control(
			'arrows',
			array(
				'label'        => esc_html__( 'Show Arrows Navigation', 'jet-elements' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jet-elements' ),
				'label_off'    => esc_html__( 'No', 'jet-elements' ),
				'return_value' => 'true',
				'default'      => 'true',
				'condition'    => array(
					'display_type' => 'slider',
				),
			)
		);

		$this->_add_advanced_icon_control(
			'prev_arrow',
			array(
				'label'       => esc_html__( 'Prev Arrow Icon', 'jet-elements' ),
				'type'        => Controls_Manager::ICON,
				'label_block' => true,
				'file'        => '',
				'default'     => 'fa fa-angle-left',
				'fa5_default' => array(
					'value'   => 'fas fa-angle-left',
					'library' => 'fa-solid',
				),
				'condition'   => array(
					'display_type' => 'slider',
					'arrows'       => 'true',
				),
			)
		);

		$this->_add_advanced_icon_control(
			'next_arrow',
			array(
				'label'       => esc_html__( 'Next Arrow Icon', 'jet-elements' ),
				'type'        => Controls_Manager::ICON,
				'label_block' => true,
				'file'        => '',
				'default'     => 'fa fa-angle-right',
				'fa5_default' => array(
					'value'   => 'fas fa-angle-right',
					'library' => 'fa-solid',
				),
				'condition'   => array(
					'display_type' => 'slider',
					'arrows'       => 'true',
				),
			)
		);

		$this->add_control(
			'dots',
			array(
				'label'        => esc_html__( 'Show Dots Navigation', 'jet-elements' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jet-elements' ),
				'label_off'    => esc_html__( 'No', 'jet-elements' ),
				'return_value' => 'true',
				'default'      => '',
				'condition'    => array(
					'display_type' => 'slider',
				),
			)
		);

		$this->add_control(
			'infinite',
			array(
				'label'        => esc_html__( 'Infinite Loop', 'jet-elements' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jet-elements' ),
				'label_off'    => esc_html__( 'No', 'jet-elements' ),
				'return_value' => 'true',
				'default'      => 'true',
				'condition'    => array(
					'display_type' => 'slider',
				),
			)
		);

		$this->add_control(
			'company_name_html_tag',
			array(
				'label'   => esc_html__( 'Company Name HTML Tag', 'jet-elements' ),
				'type'    => Controls_Manager::SELECT,
				'options' => jet_elements_tools()->get_available_title_html_tags(),
				'default' => 'h5',
			)
		);

		$this->end_controls_section();

		$css_scheme = apply_filters(
			'jet-elements/brands/css-scheme',
			array(
				'list'      => '.brands-list',
				'logo'      => '.brands-list .brands-list__item-img',
				'logo_wrap' => '.brands-list .brands-list__item-img-wrap',
				'name'      => '.brands-list .brands-list__item-name',
				'desc'      => '.brands-list .brands-list__item-desc',
				'arrow'     => '.brands-wrap .jet-arrow',
				'dots'      => '.brands-wrap .jet-slick-dots',
			)
		);

		$this->_start_controls_section(
			'section_brand_item_style',
			array(
				'label'      => esc_html__( 'Company Item', 'jet-elements' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			),
			100
		);

		$this->_add_responsive_control(
			'vertical_brands_alignment',
			array(
				'label'       => esc_html__( 'Vertical Alignment', 'jet-elements' ),
				'type'        => Controls_Manager::CHOOSE,
				'options' => array(
					'flex-start' => array(
						'title' => esc_html__( 'Top', 'jet-elements' ),
						'icon' => 'eicon-v-align-top',
					),
					'center' => array(
						'title' => esc_html__( 'Middle', 'jet-elements' ),
						'icon' => 'eicon-v-align-middle',
					),
					'flex-end' => array(
						'title' => esc_html__( 'Bottom', 'jet-elements' ),
						'icon' => 'eicon-v-align-bottom',
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .brands-list--grid' => 'align-items: {{VALUE}}',
				),
			),
			100
		);

		$this->_end_controls_section(100);

		$this->_start_controls_section(
			'section_brand_logo_style',
			array(
				'label'      => esc_html__( 'Company Logo', 'jet-elements' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->_add_responsive_control(
			'logo_alignment',
			array(
				'label'   => esc_html__( 'Alignment', 'jet-elements' ),
				'type'    => Controls_Manager::CHOOSE,
				'default' => 'left',
				'options' => array(
					'left'    => array(
						'title' => esc_html__( 'Left', 'jet-elements' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'jet-elements' ),
						'icon'  => 'eicon-h-align-center',
					),
					'right' => array(
						'title' => esc_html__( 'Right', 'jet-elements' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['logo_wrap'] => 'text-align: {{VALUE}};',
				),
				'classes' => 'jet-elements-text-align-control',
			)
		);

		$this->_add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'logo_border',
				'label'       => esc_html__( 'Border', 'jet-elements' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['logo'],
			)
		);

		$this->_add_responsive_control(
			'logo_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'jet-elements' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'custom' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['logo'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->_add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'logo_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['logo'],
			),
			100
		);

		$this->_add_control(
			'logo_wrap_style',
			array(
				'label'     => esc_html__( 'Logo Wrapper', 'jet-elements' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			),
			25
		);

		$this->_add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'logo_wrap_bg',
				'selector' => '{{WRAPPER}} ' . $css_scheme['logo_wrap'],
			),
			25
		);

		$this->_add_responsive_control(
			'logo_wrap_padding',
			array(
				'label'      => esc_html__( 'Padding', 'jet-elements' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'custom' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['logo_wrap'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			),
			50
		);

		$this->_add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'logo_wrap_border',
				'label'       => esc_html__( 'Border', 'jet-elements' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['logo_wrap'],
			),
			50
		);

		$this->_add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'logo_wrap_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['logo_wrap'],
			),
			100
		);

		$this->_end_controls_section();

		$this->_start_controls_section(
			'section_brand_title_style',
			array(
				'label'      => esc_html__( 'Company Name', 'jet-elements' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->_add_responsive_control(
			'title_alignment',
			array(
				'label'   => esc_html__( 'Alignment', 'jet-elements' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => array(
					'left'    => array(
						'title' => esc_html__( 'Left', 'jet-elements' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'jet-elements' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right' => array(
						'title' => esc_html__( 'Right', 'jet-elements' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['name'] => 'text-align: {{VALUE}};',
				),
				'classes' => 'jet-elements-text-align-control',
			),
			25
		);

		$this->_add_control(
			'title_color',
			array(
				'label'  => esc_html__( 'Title Color', 'jet-elements' ),
				'type'   => Controls_Manager::COLOR,
				'global' => array(
					'default' => Global_Colors::COLOR_PRIMARY,
				),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['name'] => 'color: {{VALUE}}',
				),
			),
			25
		);

		$this->_add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} ' . $css_scheme['name'],
				'global' => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
			),
			50
		);

		$this->_add_responsive_control(
			'title_margin',
			array(
				'label'      => esc_html__( 'Margin', 'jet-elements' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'custom' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['name'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			),
			50
		);

		$this->_end_controls_section();

		$this->_start_controls_section(
			'section_brand_desc_style',
			array(
				'label'      => esc_html__( 'Company Description', 'jet-elements' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->_add_responsive_control(
			'desc_alignment',
			array(
				'label'   => esc_html__( 'Alignment', 'jet-elements' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => array(
					'left'    => array(
						'title' => esc_html__( 'Left', 'jet-elements' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'jet-elements' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right' => array(
						'title' => esc_html__( 'Right', 'jet-elements' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['desc'] => 'text-align: {{VALUE}};',
				),
				'classes' => 'jet-elements-text-align-control',
			),
			25
		);

		$this->_add_control(
			'desc_color',
			array(
				'label'  => esc_html__( 'Title Color', 'jet-elements' ),
				'type'   => Controls_Manager::COLOR,
				'global' => array(
					'default' => Global_Colors::COLOR_TEXT,
				),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['desc'] => 'color: {{VALUE}}',
				),
			),
			25
		);

		$this->_add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'desc_typography',
				'selector' => '{{WRAPPER}} ' . $css_scheme['desc'],
				'global' => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
			),
			50
		);

		$this->_add_responsive_control(
			'desc_margin',
			array(
				'label'      => esc_html__( 'Margin', 'jet-elements' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'custom' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['desc'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			),
			50
		);

		$this->_end_controls_section();

		$this->_start_controls_section(
			'section_arrows_style',
			array(
				'label'      => esc_html__( 'Arrows', 'jet-elements' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
				'condition'  => array(
					'display_type' => 'slider',
					'arrows'       => 'true',
				),
			)
		);

		$this->_start_controls_tabs( 'tabs_arrows_style' );

		$this->_start_controls_tab(
			'tab_arrows_normal',
			array(
				'label' => esc_html__( 'Normal', 'jet-elements' ),
			)
		);

		$this->_add_group_control(
			\Jet_Group_Control_Box_Style::get_type(),
			array(
				'name'           => 'arrows_style',
				'label'          => esc_html__( 'Arrows Style', 'jet-elements' ),
				'selector'       => '{{WRAPPER}} ' . $css_scheme['arrow'],
				'fields_options' => array(
					'color' => array(
						'global' => array(
							'default' => Global_Colors::COLOR_PRIMARY,
						),
					),
				),
			),
			25
		);

		$this->_end_controls_tab();

		$this->_start_controls_tab(
			'tab_arrows_hover',
			array(
				'label' => esc_html__( 'Hover', 'jet-elements' ),
			)
		);

		$this->_add_group_control(
			\Jet_Group_Control_Box_Style::get_type(),
			array(
				'name'           => 'arrows_hover_style',
				'label'          => esc_html__( 'Arrows Style', 'jet-elements' ),
				'selector'       => '{{WRAPPER}} ' . $css_scheme['arrow'] . ':hover',
				'fields_options' => array(
					'color' => array(
						'global' => array(
							'default' => Global_Colors::COLOR_ACCENT,
						),
					),
				),
			),
			25
		);

		$this->_end_controls_tab();

		$this->_end_controls_tabs();

		$this->_add_control(
			'prev_arrow_position',
			array(
				'label'     => esc_html__( 'Prev Arrow Position', 'jet-elements' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			),
			75
		);

		$this->_add_control(
			'prev_vert_position',
			array(
				'label'   => esc_html__( 'Vertical Position by', 'jet-elements' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'top',
				'options' => array(
					'top'    => esc_html__( 'Top', 'jet-elements' ),
					'bottom' => esc_html__( 'Bottom', 'jet-elements' ),
				),
			),
			75
		);

		$this->_add_responsive_control(
			'prev_top_position',
			array(
				'label'      => esc_html__( 'Top Indent', 'jet-elements' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'custom' ),
				'range'      => array(
					'px' => array( 'min' => -400, 'max' => 400 ),
					'%'  => array( 'min' => -100, 'max' => 100 ),
					'em' => array( 'min' => -50, 'max' => 50 ),
				),
				'condition' => array(
					'prev_vert_position' => 'top',
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['arrow'] . '.prev-arrow' => 'top: {{SIZE}}{{UNIT}}; bottom: auto;',
				),
			),
			75
		);

		$this->_add_responsive_control(
			'prev_bottom_position',
			array(
				'label'      => esc_html__( 'Bottom Indent', 'jet-elements' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'custom' ),
				'range'      => array(
					'px' => array( 'min' => -400, 'max' => 400 ),
					'%'  => array( 'min' => -100, 'max' => 100 ),
					'em' => array( 'min' => -50, 'max' => 50 ),
				),
				'condition' => array(
					'prev_vert_position' => 'bottom',
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['arrow'] . '.prev-arrow' => 'bottom: {{SIZE}}{{UNIT}}; top: auto;',
				),
			),
			75
		);

		$this->_add_control(
			'prev_hor_position',
			array(
				'label'   => esc_html__( 'Horizontal Position by', 'jet-elements' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'left',
				'options' => array(
					'left'  => esc_html__( 'Left', 'jet-elements' ),
					'right' => esc_html__( 'Right', 'jet-elements' ),
				),
			),
			75
		);

		$this->_add_responsive_control(
			'prev_left_position',
			array(
				'label'      => esc_html__( 'Left Indent', 'jet-elements' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'custom' ),
				'range'      => array(
					'px' => array( 'min' => -400, 'max' => 400 ),
					'%'  => array( 'min' => -100, 'max' => 100 ),
					'em' => array( 'min' => -50, 'max' => 50 ),
				),
				'condition' => array(
					'prev_hor_position' => 'left',
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['arrow'] . '.prev-arrow' => 'left: {{SIZE}}{{UNIT}}; right: auto;',
				),
			),
			75
		);

		$this->_add_responsive_control(
			'prev_right_position',
			array(
				'label'      => esc_html__( 'Right Indent', 'jet-elements' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'custom' ),
				'range'      => array(
					'px' => array( 'min' => -400, 'max' => 400 ),
					'%'  => array( 'min' => -100, 'max' => 100 ),
					'em' => array( 'min' => -50, 'max' => 50 ),
				),
				'condition' => array(
					'prev_hor_position' => 'right',
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['arrow'] . '.prev-arrow' => 'right: {{SIZE}}{{UNIT}}; left: auto;',
				),
			),
			75
		);

		$this->_add_control(
			'next_arrow_position',
			array(
				'label'     => esc_html__( 'Next Arrow Position', 'jet-elements' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			),
			75
		);

		$this->_add_control(
			'next_vert_position',
			array(
				'label'   => esc_html__( 'Vertical Position by', 'jet-elements' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'top',
				'options' => array(
					'top'    => esc_html__( 'Top', 'jet-elements' ),
					'bottom' => esc_html__( 'Bottom', 'jet-elements' ),
				),
			),
			75
		);

		$this->_add_responsive_control(
			'next_top_position',
			array(
				'label'      => esc_html__( 'Top Indent', 'jet-elements' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'custom' ),
				'range'      => array(
					'px' => array( 'min' => -400, 'max' => 400 ),
					'%'  => array( 'min' => -100, 'max' => 100 ),
					'em' => array( 'min' => -50, 'max' => 50 ),
				),
				'condition' => array(
					'next_vert_position' => 'top',
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['arrow'] . '.next-arrow' => 'top: {{SIZE}}{{UNIT}}; bottom: auto;',
				),
			),
			75
		);

		$this->_add_responsive_control(
			'next_bottom_position',
			array(
				'label'      => esc_html__( 'Bottom Indent', 'jet-elements' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'custom' ),
				'range'      => array(
					'px' => array( 'min' => -400, 'max' => 400 ),
					'%'  => array( 'min' => -100, 'max' => 100 ),
					'em' => array( 'min' => -50, 'max' => 50 ),
				),
				'condition' => array(
					'next_vert_position' => 'bottom',
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['arrow'] . '.next-arrow' => 'bottom: {{SIZE}}{{UNIT}}; top: auto;',
				),
			),
			75
		);

		$this->_add_control(
			'next_hor_position',
			array(
				'label'   => esc_html__( 'Horizontal Position by', 'jet-elements' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'right',
				'options' => array(
					'left'  => esc_html__( 'Left', 'jet-elements' ),
					'right' => esc_html__( 'Right', 'jet-elements' ),
				),
			),
			75
		);

		$this->_add_responsive_control(
			'next_left_position',
			array(
				'label'      => esc_html__( 'Left Indent', 'jet-elements' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'custom' ),
				'range'      => array(
					'px' => array( 'min' => -400, 'max' => 400 ),
					'%'  => array( 'min' => -100, 'max' => 100 ),
					'em' => array( 'min' => -50, 'max' => 50 ),
				),
				'condition' => array(
					'next_hor_position' => 'left',
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['arrow'] . '.next-arrow' => 'left: {{SIZE}}{{UNIT}}; right: auto;',
				),
			),
			75
		);

		$this->_add_responsive_control(
			'next_right_position',
			array(
				'label'      => esc_html__( 'Right Indent', 'jet-elements' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'custom' ),
				'range'      => array(
					'px' => array( 'min' => -400, 'max' => 400 ),
					'%'  => array( 'min' => -100, 'max' => 100 ),
					'em' => array( 'min' => -50, 'max' => 50 ),
				),
				'condition' => array(
					'next_hor_position' => 'right',
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['arrow'] . '.next-arrow' => 'right: {{SIZE}}{{UNIT}}; left: auto;',
				),
			),
			75
		);

		$this->_end_controls_section();

		$this->_start_controls_section(
			'section_dots_style',
			array(
				'label'      => esc_html__( 'Dots', 'jet-elements' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
				'condition'  => array(
					'display_type' => 'slider',
					'dots'         => 'true',
				),
			)
		);

		$this->_add_responsive_control(
			'dots_alignment',
			array(
				'label'   => esc_html__( 'Alignment', 'jet-elements' ),
				'type'    => Controls_Manager::CHOOSE,
				'default' => 'center',
				'options' => array(
					'flex-start' => array(
						'title' => esc_html__( 'Start', 'jet-elements' ),
						'icon'  => ! is_rtl() ? 'eicon-h-align-left' : 'eicon-h-align-right',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'jet-elements' ),
						'icon'  => 'eicon-h-align-center',
					),
					'flex-end' => array(
						'title' => esc_html__( 'End', 'jet-elements' ),
						'icon'  => ! is_rtl() ? 'eicon-h-align-right' : 'eicon-h-align-left',
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['dots'] => 'justify-content: {{VALUE}};',
				),
			),
			25
		);

		$this->_add_control(
			'dots_gap',
			array(
				'label' => esc_html__( 'Gap', 'jet-elements' ),
				'type'  => Controls_Manager::SLIDER,
				'default' => array(
					'size' => 5,
					'unit' => 'px',
				),
				'range' => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['dots'] . ' li' => 'padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}}',
				),
			),
			25
		);

		$this->_add_control(
			'dots_margin',
			array(
				'label'      => esc_html__( 'Dots Box Margin', 'jet-elements' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'custom' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['dots'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			),
			50
		);

		$this->_start_controls_tabs( 'tabs_dots_style' );

		$this->_start_controls_tab(
			'tab_dots_normal',
			array(
				'label' => esc_html__( 'Normal', 'jet-elements' ),
			)
		);

		$this->_add_group_control(
			\Jet_Group_Control_Box_Style::get_type(),
			array(
				'name'           => 'dots_style',
				'label'          => esc_html__( 'Dots Style', 'jet-elements' ),
				'selector'       => '{{WRAPPER}} ' . $css_scheme['dots'] . ' li span',
				'fields_options' => array(
					'color' => array(
						'global' => array(
							'default' => Global_Colors::COLOR_TEXT,
						),
					),
				),
				'exclude' => array(
					'box_font_color',
					'box_font_size',
				),
			),
			25
		);

		$this->_end_controls_tab();

		$this->_start_controls_tab(
			'tab_dots_hover',
			array(
				'label' => esc_html__( 'Hover', 'jet-elements' ),
			)
		);

		$this->_add_group_control(
			\Jet_Group_Control_Box_Style::get_type(),
			array(
				'name'           => 'dots_style_hover',
				'label'          => esc_html__( 'Dots Style', 'jet-elements' ),
				'selector'       => '{{WRAPPER}} ' . $css_scheme['dots'] . ' li span:hover',
				'fields_options' => array(
					'color' => array(
						'global' => array(
							'default' => Global_Colors::COLOR_PRIMARY,
						),
					),
				),
				'exclude' => array(
					'box_font_color',
					'box_font_size',
				),
			),
			25
		);

		$this->_end_controls_tab();

		$this->_start_controls_tab(
			'tab_dots_active',
			array(
				'label' => esc_html__( 'Active', 'jet-elements' ),
			)
		);

		$this->_add_group_control(
			\Jet_Group_Control_Box_Style::get_type(),
			array(
				'name'           => 'dots_style_active',
				'label'          => esc_html__( 'Dots Style', 'jet-elements' ),
				'selector'       => '{{WRAPPER}} ' . $css_scheme['dots'] . ' li.slick-active span',
				'fields_options' => array(
					'color' => array(
						'global' => array(
							'default' => Global_Colors::COLOR_ACCENT,
						),
					),
				),
				'exclude' => array(
					'box_font_color',
					'box_font_size',
				),
			),
			25
		);

		$this->_end_controls_tab();

		$this->_end_controls_tabs();

		$this->_end_controls_section();

	}

	protected function render() {

		$this->_context = 'render';

		$this->_open_wrap();
		include $this->_get_global_template( 'index' );
		$this->_close_wrap();
	}

	// Use Elementor's server-rendered preview so editor content follows the PHP KSES/URL policy.
	protected function content_template() {}

	public function _open_brand_link( $url_key ) {
		call_user_func( array( $this, sprintf( '_open_brand_link_%s', $this->_context ) ), $url_key );
	}

	public function _open_brand_link_render( $url_key ) {

		$item = $this->_processed_item;

		if ( empty( $item[ $url_key ]['url'] ) ) {
			return;
		}

		$id = $item['_id'];

		$this->add_render_attribute( 'link-' . $id, 'class', 'brands-list__item-link' );

		if ( method_exists( $this, 'add_link_attributes' ) ) {
			$this->add_link_attributes( 'link-' . $id, $this->sanitize_link_control( $item[ $url_key ] ) );
		} else {
				$this->add_render_attribute( 'link-' . $id, 'href', esc_url( $item[ $url_key ]['url'] ) );

			if ( ! empty( $item[ $url_key ]['is_external'] ) ) {
				$this->add_render_attribute( 'link-' . $id, 'target', '_blank' );
			}

			if ( ! empty( $item[ $url_key ]['nofollow'] ) ) {
				$this->add_render_attribute( 'link-' . $id, 'rel', 'nofollow' );
			}
		}

		printf(
			'<a %s>',
			$this->get_render_attribute_string( 'link-' . $id ) // phpcs:ignore
		);

	}

	public function _open_brand_link_edit( $url_key ) {

		$format = '<a href="%1$s" class="brands-list__item-link"%2$s%3$s>';

		echo '<# if ( item.' . $url_key . '.url ) { #>'; // phpcs:ignore
		printf(
			$format,
			$this->get_template_render_value( 'item.' . $url_key . '.url', $format, 1 ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'<# if ( item.' . $url_key . '.is_external ) { #> target="_blank"<# } #>', // phpcs:ignore
			'<# if ( item.' . $url_key . '.nofollow ) { #> rel="nofollow"<# } #>' // phpcs:ignore
		);
		echo '<# } #>'; // phpcs:ignore

	}

	public function _close_brand_link( $url_key ) {
		call_user_func( array( $this, sprintf( '_close_brand_link_%s', $this->_context ) ), $url_key );
	}

	public function _close_brand_link_render( $url_key ) {

		$item = $this->_processed_item;

		if ( empty( $item[ $url_key ]['url'] ) ) {
			return;
		}

		echo '</a>';

	}

	public function _close_brand_link_edit( $url_key ) {

		echo '<# if ( item.' . $url_key . '.url ) { #>'; // phpcs:ignore
		echo '</a>';
		echo '<# } #>'; // phpcs:ignore

	}

	public function _get_brand_image( $img_key ) {
		call_user_func( array( $this, sprintf( '_get_brand_image_%s', $this->_context ) ), $img_key );
	}

	public function _get_brand_image_render( $img_key ) {
		$image_item = $this->_processed_item[ $img_key ];

		if ( empty( $image_item['url'] ) ) {
			return;
		}

		printf( '<div class="brands-list__item-img-wrap"><img src="%1$s" alt="%2$s" class="brands-list__item-img" loading="lazy"></div>',
			esc_url( $image_item['url'] ),
			esc_attr( Control_Media::get_image_alt( $image_item ) )
		);
	}

	public function _get_brand_image_edit( $img_key ) {
		echo $this->_loop_item( array( $img_key, 'url' ), '<div class="brands-list__item-img-wrap"><img src="%s" alt="" class="brands-list__item-img"></div>' ); // phpcs:ignore
	}

	public function generate_slider_settings_json() {
		$settings  = 'render' === $this->_context ? $this->get_settings_for_display() : $this->get_settings();
		$widget_id = sanitize_key( $this->get_id() );

		$slider_settings = array(
			'autoplaySpeed'  => ! empty( $settings['autoplay_speed'] ) ? absint( $settings['autoplay_speed'] ) : 5000,
			'autoplay'       => ! empty( $settings['autoplay'] ) && filter_var( $settings['autoplay'], FILTER_VALIDATE_BOOLEAN ),
			'infinite'       => ! empty( $settings['infinite'] ) && filter_var( $settings['infinite'], FILTER_VALIDATE_BOOLEAN ),
			'arrows'         => ! empty( $settings['arrows'] ) && filter_var( $settings['arrows'], FILTER_VALIDATE_BOOLEAN ),
			'dots'           => ! empty( $settings['dots'] ) && filter_var( $settings['dots'], FILTER_VALIDATE_BOOLEAN ),
			'slidesToScroll' => 1 < absint( $settings['slides_to_show'] ) ? absint( $settings['slides_to_scroll'] ) : 1,
			'prevArrow'      => '.jet-brands__prev-arrow-' . $widget_id,
			'nextArrow'      => '.jet-brands__next-arrow-' . $widget_id,
			'rtl'            => is_rtl(),
		);

		$slider_settings = apply_filters( 'jet-elements/jet-brands/slider-options', $slider_settings, $settings, $widget_id );

		return sprintf( 'data-slider_options="%s"', esc_attr( wp_json_encode( $slider_settings ) ) );
	}
}
