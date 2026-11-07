<?php
namespace PowerpackElements\Modules\AdvancedTabs\Widgets;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Classes\PP_Config;

// Elementor Classes
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography as Scheme_Typography;
use Elementor\Core\Schemes\Color as Scheme_Color;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Advanced Tabs Widget
 */
class Advanced_Tabs extends Powerpack_Widget {

	/**
	 * Retrieve Advanced Tabs widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Advanced_Tabs' );
	}

	/**
	 * Retrieve Advanced Tabs widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Advanced_Tabs' );
	}

	/**
	 * Retrieve Advanced Tabs widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Advanced_Tabs' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the Advanced Tabs widget belongs to.
	 *
	 * @since 1.4.13.1
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'Advanced_Tabs' );
	}

	/**
	 * Retrieve the list of scripts the Advanced Tabs widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return array(
			'pp-advanced-tabs',
		);
	}

	/**
	 * Register Advanced Tabs widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 2.0.3
	 * @access protected
	 */
	protected function register_controls() {
		/* Content Tab */
		$this->register_content_tabs_controls();
		$this->register_content_layout_controls();
		$this->register_content_help_docs_controls();

		/* Style Tab */
		$this->register_style_tabs_controls();
		$this->register_style_title_controls();
		$this->register_style_content_controls();
	}

	protected function register_content_tabs_controls() {
		/**
		 * Content Tab: Advanced Tabs
		 */
		$this->start_controls_section(
			'section_advanced_tabs',
			array(
				'label' => __( 'Advanced Tabs', 'powerpack' ),
			)
		);

		$repeater = new Repeater();

		$repeater->start_controls_tabs( 'tabs_at' );

		$repeater->start_controls_tab(
			'tab_content',
			array(
				'label' => __( 'Content', 'powerpack' ),
			)
		);

		$repeater->add_control(
			'tab_title',
			array(
				'label'       => __( 'Title', 'powerpack' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array(
					'active' => true,
				),
				'label_block' => true,
				'placeholder' => __( 'Title', 'powerpack' ),
				'default'     => __( 'Title', 'powerpack' ),
			)
		);

		$repeater->add_control(
			'content_type',
			array(
				'label'   => __( 'Content Type', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'tab_content',
				'options' => array(
					'tab_content' => __( 'Content', 'powerpack' ),
					'tab_photo'   => __( 'Image', 'powerpack' ),
					'tab_video'   => __( 'Link (Video/Image)', 'powerpack' ),
					'section'     => __( 'Saved Section', 'powerpack' ),
					'widget'      => __( 'Saved Widget', 'powerpack' ),
					'template'    => __( 'Saved Page Template', 'powerpack' ),
				),
			)
		);

		$repeater->add_control(
			'content',
			array(
				'label'     => __( 'Content', 'powerpack' ),
				'type'      => Controls_Manager::WYSIWYG,
				'dynamic'   => array(
					'active' => true,
				),
				'default'   => __( 'I am tab content. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'powerpack' ),
				'condition' => array(
					'content_type' => 'tab_content',
				),
			)
		);

		$repeater->add_control(
			'image',
			array(
				'label'     => __( 'Image', 'powerpack' ),
				'type'      => Controls_Manager::MEDIA,
				'dynamic'   => array(
					'active' => true,
				),
				'default'   => array(
					'url' => Utils::get_placeholder_image_src(),
				),
				'condition' => array(
					'content_type' => 'tab_photo',
				),
			)
		);

		$repeater->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'      => 'image',
				'label'     => __( 'Image Size', 'powerpack' ),
				'default'   => 'large',
				'exclude'   => array( 'custom' ),
				'condition' => array(
					'content_type' => 'tab_photo',
				),
			)
		);

		$repeater->add_control(
			'link_video',
			array(
				'label'       => __( 'Link', 'powerpack' ),
				'description' => sprintf( __( 'Check list of supported embeds <a href="%1$s" target="_blank">here</a>.', 'powerpack' ), 'https://wordpress.org/support/article/embeds/' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array(
					'active' => true,
				),
				'ai'          => array(
					'active' => false,
				),
				'placeholder' => __( 'Enter your Video link', 'powerpack' ),
				'label_block' => true,
				'condition'   => array(
					'content_type' => 'tab_video',
				),
			)
		);

		$repeater->add_control(
			'saved_widget',
			array(
				'label'       => __( 'Choose Widget', 'powerpack' ),
				'type'        => 'pp-query',
				'label_block' => false,
				'multiple'    => false,
				'query_type'  => 'templates-widget',
				'conditions'  => array(
					'terms' => array(
						array(
							'name'     => 'content_type',
							'operator' => '==',
							'value'    => 'widget',
						),
					),
				),
			)
		);

		$repeater->add_control(
			'saved_section',
			array(
				'label'       => __( 'Choose Section', 'powerpack' ),
				'type'        => 'pp-query',
				'label_block' => false,
				'multiple'    => false,
				'query_type'  => 'templates-section',
				'conditions'  => array(
					'terms' => array(
						array(
							'name'     => 'content_type',
							'operator' => '==',
							'value'    => 'section',
						),
					),
				),
			)
		);

		$repeater->add_control(
			'templates',
			array(
				'label'       => __( 'Choose Template', 'powerpack' ),
				'type'        => 'pp-query',
				'label_block' => false,
				'multiple'    => false,
				'query_type'  => 'templates-page',
				'conditions'  => array(
					'terms' => array(
						array(
							'name'     => 'content_type',
							'operator' => '==',
							'value'    => 'template',
						),
					),
				),
			)
		);

		$repeater->end_controls_tab();

		$repeater->start_controls_tab(
			'tab_icon',
			array(
				'label' => __( 'Icon', 'powerpack' ),
			)
		);

		$repeater->add_control(
			'pp_icon_type',
			[
				'label'             => __( 'Icon Type', 'powerpack' ),
				'type'              => Controls_Manager::CHOOSE,
				'label_block'       => false,
				'toggle'            => false,
				'default'           => 'icon',
				'options'           => [
					'none'  => [
						'title' => esc_html__( 'None', 'powerpack' ),
						'icon'  => 'eicon-ban',
					],
					'icon'  => [
						'title' => esc_html__( 'Icon', 'powerpack' ),
						'icon'  => 'eicon-star',
					],
					'image' => [
						'title' => esc_html__( 'Image', 'powerpack' ),
						'icon'  => 'eicon-image-bold',
					],
				],
			]
		);

		$repeater->add_control(
			'selected_icon',
			array(
				'label'            => __( 'Icon', 'powerpack' ),
				'type'             => Controls_Manager::ICONS,
				'label_block'      => true,
				'default'          => array(
					'value'   => 'fas fa-check',
					'library' => 'fa-solid',
				),
				'fa4compatibility' => 'icon',
				'condition'        => [
					'pp_icon_type' => 'icon',
				],
			)
		);

		$repeater->add_control(
			'icon_img',
			[
				'label'             => __( 'Image', 'powerpack' ),
				'label_block'       => true,
				'type'              => Controls_Manager::MEDIA,
				'default'           => [
					'url' => Utils::get_placeholder_image_src(),
				],
				'dynamic'           => [
					'active'  => true,
				],
				'condition'         => [
					'pp_icon_type' => 'image',
				],
			]
		);

		$repeater->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'      => 'icon_img',
				'label'     => __( 'Image Size', 'powerpack' ),
				'default'   => 'full',
				'condition' => array(
					'pp_icon_type' => 'image',
				),
			)
		);

		$repeater->end_controls_tab();

		$repeater->end_controls_tabs();

		$this->add_control(
			'tab_features',
			array(
				'label'       => '',
				'type'        => Controls_Manager::REPEATER,
				'default'     => array(
					array(
						'tab_title' => __( 'Tab #1', 'powerpack' ),
						'content'   => __( 'I am tab 1 content. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'powerpack' ),
					),
					array(
						'tab_title' => __( 'Tab #2', 'powerpack' ),
						'content'   => __( 'I am tab 2 content. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'powerpack' ),
					),
					array(
						'tab_title' => __( 'Tab #3', 'powerpack' ),
						'content'   => __( 'I am tab 3 content. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'powerpack' ),
					),
				),
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ tab_title }}}',
			)
		);

		$this->end_controls_section();
	}

	protected function register_content_layout_controls() {
		/**
		 * Content Tab: Layout
		 */
		$this->start_controls_section(
			'section_general_layout',
			array(
				'label' => __( 'Layout', 'powerpack' ),
			)
		);
		$this->add_control(
			'type',
			array(
				'label'   => __( 'Layout', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'at-horizontal',
				'options' => array(
					'at-horizontal' => __( 'Horizontal', 'powerpack' ),
					'at-vertical'   => __( 'Vertical', 'powerpack' ),
				),
			)
		);

		$this->add_control(
			'responsive_support',
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
			'custom_style',
			array(
				'label'   => __( 'Select Style', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'style-0',
				'options' => array(
					'style-0'      => __( 'Basic', 'powerpack' ),
					'style-1'      => __( 'Style 1', 'powerpack' ),
					'style-2'      => __( 'Style 2', 'powerpack' ),
					'style-3'      => __( 'Style 3', 'powerpack' ),
					'style-4'      => __( 'Style 4', 'powerpack' ),
					'style-5'      => __( 'Style 5', 'powerpack' ),
					'style-6'      => __( 'Style 6', 'powerpack' ),
					'style-7'      => __( 'Style 7', 'powerpack' ),
					'style-8'      => __( 'Style 8', 'powerpack' ),
					'style-custom' => __( 'Custom', 'powerpack' ),
				),
			)
		);

		$this->add_control(
			'scroll_top',
			array(
				'label'       => __( 'Scroll to Top', 'powerpack' ),
				'description' => __( 'Scrolls to top of tabs contaniner when clicked on tab title.', 'powerpack' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'no',
				'options'     => array(
					'yes' => __( 'Yes', 'powerpack' ),
					'no'  => __( 'No', 'powerpack' ),
				),
				'condition'   => array(
					'type' => 'at-vertical',
				),
			)
		);

		$this->add_control(
			'default_tab',
			array(
				'label'       => __( 'Default Active Tab Index', 'powerpack' ),
				'type'        => Controls_Manager::NUMBER,
				'label_block' => false,
				'min'         => 1,
				'step'        => 1,
				'default'     => 1,
				'placeholder' => __( 'Default Active Tab Index', 'powerpack' ),
			)
		);

		$this->add_control(
			'custom_id_prefix',
			array(
				'label'       => __( 'Custom ID Prefix', 'powerpack' ),
				'description' => __( 'A prefix that will be applied to ID attribute of tabs\'s in HTML. For example, prefix "mytab" will be applied as "mytab-1", "mytab-2" in ID attribute of Tab 1 and Tab 2 respectively. It should only contain dashes, underscores, letters or numbers. No spaces.', 'powerpack' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => '',
				'ai'          => [
					'active' => false,
				],
				'placeholder' => __( 'mytab', 'powerpack' ),
			)
		);

		$this->end_controls_section();
	}

	protected function register_content_help_docs_controls() {

		$help_docs = PP_Config::get_widget_help_links( 'Advanced_Tabs' );

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

	protected function register_style_tabs_controls() {
		/**
		 * Style Tab: Tabs
		 */
		$this->start_controls_section(
			'section_tabs_style',
			array(
				'label' => __( 'Tabs', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'title_align_horizontal',
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
				'condition' => array(
					'type' => 'at-horizontal',
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-tabs-wrapper.at-horizontal, {{WRAPPER}} .pp-advanced-tabs .pp-advanced-tabs-content-wrapper .pp-tab-responsive.pp-advanced-tabs-title' => 'justify-content: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'title_margin_right',
			array(
				'label'      => __( 'Margin Right', 'powerpack' ),
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
				'condition'  => array(
					'type'                   => 'at-horizontal',
					'title_align_horizontal' => 'flex-end',
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .at-horizontal .pp-advanced-tabs-title:last-child' => 'margin-right: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'title_margin_left',
			array(
				'label'      => __( 'Margin Left', 'powerpack' ),
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
				'condition'  => array(
					'type'                   => 'at-horizontal',
					'title_align_horizontal' => 'flex-start',
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .at-horizontal .pp-advanced-tabs-title:first-child' => 'margin-left: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'title_align_vertical',
			array(
				'label'     => __( 'Vertical Alignment', 'powerpack' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Top', 'powerpack' ),
						'icon'  => 'eicon-v-align-top',
					),
					'center'     => array(
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'eicon-v-align-middle',
					),
					'flex-end'   => array(
						'title' => __( 'Bottom', 'powerpack' ),
						'icon'  => 'eicon-v-align-bottom',
					),
				),
				'default'   => 'flex-start',
				'condition' => array(
					'type' => 'at-vertical',
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-tabs-wrapper.at-vertical' => 'justify-content: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'tabs_position_vertical',
			array(
				'label'                => __( 'Position', 'powerpack' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => array(
					'left'  => array(
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-h-align-left',
					),
					'right' => array(
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'default'              => 'left',
				'selectors_dictionary' => array(
					'left'  => 'row',
					'right' => 'row-reverse',
				),
				'selectors'            => array(
					'{{WRAPPER}} .pp-advanced-tabs' => 'flex-direction: {{VALUE}};',
				),
				'condition'            => array(
					'type' => 'at-vertical',
				),
			)
		);

		$this->add_responsive_control(
			'title_margin_top',
			array(
				'label'      => __( 'Margin Top', 'powerpack' ),
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
				'condition'  => array(
					'type'                 => 'at-vertical',
					'title_align_vertical' => 'flex-start',
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .at-vertical .pp-advanced-tabs-title:first-child' => 'margin-top: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'title_margin_bottom',
			array(
				'label'      => __( 'Margin Bottom', 'powerpack' ),
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
				'condition'  => array(
					'type'                 => 'at-vertical',
					'title_align_vertical' => 'flex-end',
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .at-vertical .pp-advanced-tabs-title:last-child' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'title_space',
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
				'separator'  => 'after',
				'selectors'  => array(
					'{{WRAPPER}} .at-horizontal .pp-advanced-tabs-title:not(:last-child)' => 'margin-right: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .at-horizontal-content .pp-advanced-tabs-title:not(:first-child)' => 'margin-top: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .at-vertical .pp-advanced-tabs-title:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}}',
					'(tablet){{WRAPPER}} .pp-tabs-responsive-tablet .pp-tabs-panel:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}}',
					'(mobile){{WRAPPER}} .pp-tabs-responsive-mobile .pp-tabs-panel:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_control(
			'tabs_container_background_color',
			[
				'label'                 => __( 'Background Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-advanced-tabs-wrapper' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'tabs_container_border_radius',
			[
				'label'                 => __( 'Border Radius', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px', '%', 'em' ],
				'selectors'             => [
					'{{WRAPPER}} .pp-advanced-tabs-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'tabs_container_padding',
			[
				'label'                 => __( 'Padding', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px', 'em', '%' ],
				'selectors'             => [
					'{{WRAPPER}} .pp-advanced-tabs-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_title_controls() {
		/**
		 * Style Tab: Title
		 */
		$this->start_controls_section(
			'section_title_style',
			array(
				'label' => __( 'Title', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'icon_position',
			array(
				'label'   => __( 'Icon Position', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'top'    => __( 'Top', 'powerpack' ),
					'bottom' => __( 'Bottom', 'powerpack' ),
					'left'   => __( 'Left', 'powerpack' ),
					'right'  => __( 'Right', 'powerpack' ),
				),
				'default' => 'left',
			)
		);
		$this->add_responsive_control(
			'icon_size',
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
					'{{WRAPPER}} .pp-advanced-tabs-title .pp-icon' => 'font-size: {{SIZE}}{{UNIT}}',
				),
			)
		);
		$this->add_responsive_control(
			'icon_image_width',
			array(
				'label'      => __( 'Icon Image Width', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array(
					'size' => 30,
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
					'{{WRAPPER}} .pp-advanced-tabs-title .pp-icon-img img' => 'width: {{SIZE}}{{UNIT}}',
				),
				'separator'  => 'after',
			)
		);
		$this->add_control(
			'title_border_radius',
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
					'{{WRAPPER}} .pp-advanced-tabs-title' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'title_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'separator'  => 'after',
				'default'    => array(
					'top'    => 10,
					'bottom' => 10,
					'left'   => 10,
					'right'  => 10,
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-advanced-tabs-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'label'    => __( 'Title Typography', 'powerpack' ),
				'scheme'   => Scheme_Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .pp-advanced-tabs-title .pp-advanced-tabs-title-text',
			)
		);
		$this->start_controls_tabs( 'tabs_title_style' );

		$this->start_controls_tab(
			'tab_title_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);
		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'Icon Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#808080',
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-tabs-title .pp-icon' => 'color: {{VALUE}}',
					'{{WRAPPER}} .pp-advanced-tabs-title svg' => 'fill: {{VALUE}}',
				),
			)
		);
		$this->add_control(
			'title_text_color',
			array(
				'label'     => __( 'Title Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#808080',
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-tabs-title .pp-advanced-tabs-title-text' => 'color: {{VALUE}}',
				),
			)
		);
		$this->add_control(
			'title_bg_color',
			array(
				'label'     => __( 'Title Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-tabs-title' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'custom_style!' => 'style-6',
				),
			)
		);
		$this->add_control(
			'title_border_color',
			array(
				'label'     => __( 'Title Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#808080',
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-tabs-title' => 'border-color: {{VALUE}}',
					'{{WRAPPER}} .pp-style-6 .pp-advanced-tabs-title:after' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'custom_style' => array( 'style-6' ),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'tab_title_border',
				'label'     => esc_html__( 'Border', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-style-custom .pp-advanced-tabs-title',
				'condition' => array(
					'custom_style' => array( 'style-custom' ),
				),
			)
		);

		$this->end_controls_tab(); // End Normal Tab

		$this->start_controls_tab(
			'tab_title_active',
			array(
				'label' => __( 'Active', 'powerpack' ),
			)
		);
		$this->add_control(
			'icon_color_active',
			array(
				'label'     => __( 'Icon Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#000000',
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-tabs-title.pp-tab-active .pp-icon' => 'color: {{VALUE}}',
					'{{WRAPPER}} .pp-advanced-tabs-title.pp-tab-active svg' => 'fill: {{VALUE}}',
					'{{WRAPPER}} .at-hover .pp-advanced-tabs-title:hover .pp-icon' => 'color: {{VALUE}}',
					'{{WRAPPER}} .at-hover .pp-advanced-tabs-title:hover svg' => 'fill: {{VALUE}}',
				),
			)
		);
		$this->add_control(
			'title_text_color_active',
			array(
				'label'     => __( 'Title Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#000000',
				'selectors' => array(
					'{{WRAPPER}} .pp-tab-active .pp-advanced-tabs-title-text' => 'color: {{VALUE}}',
					'{{WRAPPER}} .at-hover .pp-advanced-tabs-title:hover .pp-advanced-tabs-title-text' => 'color: {{VALUE}}',
				),
			)
		);
		$this->add_control(
			'title_bg_color_active',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-tab-active' => 'background-color: {{VALUE}}',
					'{{WRAPPER}} .at-hover .pp-advanced-tabs-title:hover' => 'background-color: {{VALUE}}',
					'{{WRAPPER}} .pp-style-1 .at-horizontal .pp-tab-active:after' => 'border-top-color: {{VALUE}}',
					'{{WRAPPER}} .pp-style-1 .at-vertical .pp-tab-active:after' => 'border-left-color: {{VALUE}}',
					'{{WRAPPER}} .pp-style-6 .pp-advanced-tabs-title.pp-tab-active:after' => 'background-color: {{VALUE}}',
				),
			)
		);
		$this->add_control(
			'title_border_color_active',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#000000',
				'condition' => array(
					'custom_style!' => array( 'style-1', 'style-6', 'style-7', 'style-8' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-tab-active, {{WRAPPER}} .pp-style-custom .pp-advanced-tabs-title.pp-tab-active' => 'border-color: {{VALUE}}',
					'{{WRAPPER}} .at-hover .pp-advanced-tabs-title:hover' => 'border-color: {{VALUE}}',
					'{{WRAPPER}} .pp-style-2 .pp-advanced-tabs-title.pp-tab-active:before' => 'background-color: {{VALUE}}',
					'{{WRAPPER}} .pp-style-2 .at-hover .pp-advanced-tabs-title.pp-tab-active:before' => 'background-color: {{VALUE}}',
					'{{WRAPPER}} .pp-style-3 .pp-advanced-tabs-title.pp-tab-active:before' => 'background-color: {{VALUE}}',
					'{{WRAPPER}} .pp-style-3 .at-hover .pp-advanced-tabs-title.pp-tab-active:before' => 'background-color: {{VALUE}}',
				),
			)
		);
			$this->add_control(
				'title_animation_color',
				array(
					'label'     => __( 'Animation Color', 'powerpack' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '#000000',
					'condition' => array(
						'custom_style' => array( 'style-4', 'style-5', 'style-7', 'style-8' ),
					),
					'selectors' => array(
						'{{WRAPPER}} .pp-style-4 .pp-advanced-tabs-title.pp-tab-active:before' => 'background-color: {{VALUE}}',
						'{{WRAPPER}} .pp-style-4 .pp-advanced-tabs-title.pp-tab-active:after' => 'background-color: {{VALUE}}',
						'{{WRAPPER}} .pp-style-5 .pp-advanced-tabs-title.pp-tab-active:before' => 'background-color: {{VALUE}}',
						'{{WRAPPER}} .pp-style-5 .pp-advanced-tabs-title.pp-tab-active:after' => 'background-color: {{VALUE}}',
						'{{WRAPPER}} .pp-style-7 .pp-advanced-tabs-title .active-slider-span' => 'background-color: {{VALUE}}',
						'{{WRAPPER}} .pp-style-8 .pp-advanced-tabs-title .active-slider-span' => 'background-color: {{VALUE}}',
					),
				)
			);
		$this->end_controls_tab(); // End Hover Tab

		$this->end_controls_tabs(); // End Controls Tab

		$this->add_control(
			'tab_hover_effect',
			array(
				'label'     => __( 'Hover Effect', 'powerpack' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'yes' => array(
						'title' => __( 'Yes', 'powerpack' ),
						'icon'  => 'eicon-check',
					),
					'no'  => array(
						'title' => __( 'No', 'powerpack' ),
						'icon'  => 'eicon-ban',
					),
				),
				'condition' => array(
					'custom_style!' => array( 'style-6' ),
				),
				'separator' => 'before',
				'default'   => 'no',
			)
		);

		$this->end_controls_section();
	}

	protected function register_style_content_controls() {
		/**
		 * Style Tab: Content
		 */
		$this->start_controls_section(
			'section_content_style',
			array(
				'label' => __( 'Content', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'tab_align',
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
					'{{WRAPPER}} .pp-advanced-tabs-content'   => 'text-align: {{VALUE}};',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'tab_bg_style',
				'label'    => __( 'Background', 'powerpack' ),
				'types'    => array( 'none', 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .pp-advanced-tabs-content',
			)
		);
		$this->add_control(
			'text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#808080',
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-tabs-content' => 'color: {{VALUE}}',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'tab_text_typography',
				'label'    => __( 'Text Typography', 'powerpack' ),
				'scheme'   => Scheme_Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .pp-advanced-tabs-content',
			)
		);

		$this->add_control(
			'tab_border_type',
			array(
				'label'     => __( 'Border Type', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'solid',
				'options'   => array(
					'none'   => __( 'None', 'powerpack' ),
					'solid'  => __( 'Solid', 'powerpack' ),
					'double' => __( 'Double', 'powerpack' ),
					'dotted' => __( 'Dotted', 'powerpack' ),
					'dashed' => __( 'Dashed', 'powerpack' ),
					'groove' => __( 'Groove', 'powerpack' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-tabs-content' => 'border-style: {{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'tab_border_width',
			array(
				'label'      => __( 'Border Width', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'default'    => array(
					'top'    => 1,
					'bottom' => 1,
					'left'   => 1,
					'right'  => 1,
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-advanced-tabs-content' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'tab_border_type!' => 'none',
				),
			)
		);

		$this->add_control(
			'tab_border_color',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#808080',
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-tabs-content' => 'border-color: {{VALUE}}',
				),
				'condition' => array(
					'tab_border_type!' => 'none',
				),
			)
		);
		$this->add_control(
			'tab_border_radius',
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
					'{{WRAPPER}} .pp-advanced-tabs-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'tab_padding',
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
					'{{WRAPPER}} .pp-advanced-tabs-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_section();
	}

	/**
	 * Render tabs content.
	 *
	 * @since 2.3.2
	 */
	protected function get_tabs_content( $item ) {
		$settings     = $this->get_settings_for_display();
		$content_type = $item['content_type'];
		$output       = '';

		switch ( $content_type ) {
			case 'tab_content':
				$output = $this->parse_text_editor( $item['content'] );
				break;

			case 'tab_photo':
				if ( $item['image']['url'] ) {
					$output = wp_kses_post( Group_Control_Image_Size::get_attachment_image_html( $item, 'image', 'image' ) );
				}
				break;

			case 'tab_video':
				$output = wp_kses_post( $this->parse_text_editor( $item['link_video'] ) );
				break;

			case 'section':
				$output = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $item['saved_section'] );
				break;

			case 'template':
				$output = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $item['templates'] );
				break;

			case 'widget':
				$output = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $item['saved_widget'] );
				break;

			default:
				return;
		}

		return $output;
	}

	/**
	 * Render tabs widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		$settings        = $this->get_settings();
		$tabs            = $this->get_settings_for_display( 'tab_features' );
		$id_int          = substr( $this->get_id_int(), 0, 3 );
		$hover_class     = '';
		$default_tab_no  = '';
		$default_title   = '';
		$default_content = '';

		$fallback_defaults = array(
			'fa fa-check',
			'fa fa-times',
			'fa fa-dot-circle-o',
		);

		if ( 0 < $settings['default_tab'] && count( $tabs ) >= $settings['default_tab'] ) {
			$default_tab_no = $settings['default_tab'];
		} else {
			$default_tab_no = 1;
		}

		$hover_state = $settings['tab_hover_effect'];

		if ( 'yes' === $hover_state ) {
			$hover_class = ' at-hover';
		} else {
			$hover_class = ' at-no-hover';
		}

		$this->add_render_attribute(
			'container',
			array(
				'class' => array( 'pp-advanced-tabs', 'pp-' . $settings['custom_style'], 'pp-tabs-responsive-' . $settings['responsive_support'] ),
				'role'  => 'tablist',
			)
		);

		if ( 'no' !== $settings['scroll_top'] ) {
			$this->add_render_attribute( 'container', 'data-scroll-top', 'yes' );
		}

		if ( 'no' !== $settings['responsive_support'] ) {
			$this->add_render_attribute( 'container', 'class', 'pp-advabced-tabs-responsive' );
		}

		$this->add_render_attribute(
			'tabs-wrap',
			'class',
			array(
				'pp-advanced-tabs-wrapper',
				'pp-tabs-labels',
				$settings['type'],
				$hover_class,
			)
		);
		?>
		<div <?php $this->print_render_attribute_string( 'container' ); ?>>
			<div <?php $this->print_render_attribute_string( 'tabs-wrap' ); ?>>
				<?php
				foreach ( $tabs as $index => $item ) {

					$tab_count = $index + 1;

					if ( $tab_count === (int) $default_tab_no ) {
						$default_title = 'pp-tab-active';
					} else {
						$default_title = '';
					}

					if ( $settings['custom_id_prefix'] ) {
						$tab_id = $settings['custom_id_prefix'] . '-' . $tab_count;
					} else {
						$tab_id = 'pp-advanced-tabs-title-' . $id_int . $tab_count;
					}

					$title_text_setting_key = $this->get_repeater_setting_key( 'tab_title', 'tab_features', $index );

					$this->add_render_attribute(
						$title_text_setting_key,
						array(
							'id'            => $tab_id,
							'class'         => array( 'pp-advanced-tabs-title', 'pp-tabs-label', 'pp-advanced-tabs-desktop-title', $default_title ),
							'data-tab'      => $tab_count,
							'data-index'    => $id_int . $tab_count,
							'tabindex'      => '0',
							'role'          => 'tab',
							'aria-controls' => 'pp-advanced-tabs-content-' . $id_int . $tab_count,
						)
					);

					if ( 'top' === $settings['icon_position'] || 'left' === $settings['icon_position'] ) {
						?>
							<div <?php $this->print_render_attribute_string( $title_text_setting_key ); ?>>

								<?php $this->render_tab_title_icon( $item ); ?>

								<span class="pp-advanced-tabs-title-text"><?php echo wp_kses_post( $item['tab_title'] ); ?></span>
								<?php if ( 'style-7' === $settings['custom_style'] || 'style-8' === $settings['custom_style'] ) { ?>
									<span class="active-slider-span"></span>
								<?php } ?>
							</div>
						<?php } elseif ( 'bottom' === $settings['icon_position'] || 'right' === $settings['icon_position'] ) { ?>
							<div <?php $this->print_render_attribute_string( $title_text_setting_key ); ?>>
								<span class="pp-advanced-tabs-title-text"><?php echo wp_kses_post( $item['tab_title'] ); ?></span>

								<?php $this->render_tab_title_icon( $item ); ?>

								<?php if ( 'style-7' === $settings['custom_style'] || 'style-8' === $settings['custom_style'] ) { ?>
									<span class="active-slider-span"></span>
								<?php } ?>
							</div>
						<?php
						}
				}
				?>
			</div>
			<div class="pp-advanced-tabs-content-wrapper pp-tabs-panels <?php echo esc_attr( $settings['type'] ); ?>-content">
				<?php
				foreach ( $tabs as $index => $item ) :
					$tab_count = $index + 1;
					if ( $tab_count === (int) $default_tab_no ) {
						$default_content = 'pp-tab-active';
					} else {
						$default_content = '';
					}

					if ( $settings['custom_id_prefix'] ) {
						$tab_id = $settings['custom_id_prefix'] . '-' . $tab_count;
					} else {
						$tab_id = 'pp-advanced-tabs-title-' . $id_int . $tab_count;
					}

					$tab_title_setting_key = $this->get_repeater_setting_key( 'title', 'tab_features', $index );
					$tab_content_setting_key = $this->get_repeater_setting_key( 'content', 'tab_features', $index );

					$this->add_render_attribute(
						$tab_title_setting_key,
						array(
							'class'      => array(
								'pp-advanced-tabs-title',
								'pp-tabs-label',
								'pp-tab-responsive',
								esc_attr( $default_content ),
								esc_attr( $hover_class )
							),
							'data-index' => $id_int . $tab_count,
						)
					);

					$this->add_render_attribute(
						$tab_content_setting_key,
						array(
							'id'              => 'pp-advanced-tabs-content-' . $id_int . $tab_count,
							'class'           => array( 'pp-advanced-tabs-content', 'elementor-clearfix', 'pp-advanced-tabs-' . $item['content_type'], $default_content ),
							'data-tab'        => $tab_count,
							'data-index'      => $id_int . $tab_count,
							'role'            => 'tabpanel',
							'aria-labelledby' => $tab_id,
						)
					);
					?>
					<div class="pp-tabs-panel">
						<div <?php $this->print_render_attribute_string( $tab_title_setting_key ); ?>>
							<div class="pp-advanced-tabs-title-inner">
								<?php $this->render_tab_title_icon( $item ); ?>

								<span class="pp-advanced-tabs-title-text"><?php echo wp_kses_post( $item['tab_title'] ); ?></span>
								<i class="pp-toggle-icon pp-tab-open fa"></i>
							</div>
						</div>
						<div <?php $this->print_render_attribute_string( $tab_content_setting_key ); ?>>
							<?php echo $this->get_tabs_content( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 *  Get Saved Widgets
	 *
	 *  @param string $type Type.
	 *
	 *  @return string
	 */
	public function render_tab_title_icon( $item ) {
		$settings = $this->get_settings();

		if ( 'none' === $item['pp_icon_type'] ) {
			return;
		}

		$migration_allowed = Icons_Manager::is_migration_allowed();

		// add old default
		if ( ! isset( $item['icon'] ) && ! $migration_allowed ) {
			$item['icon'] = isset( $fallback_defaults[ $index ] ) ? $fallback_defaults[ $index ] : 'fa fa-check';
		}

		$migrated = isset( $item['__fa4_migrated']['selected_icon'] );
		$is_new   = ! isset( $item['icon'] ) && $migration_allowed;

		if ( 'icon' === $item['pp_icon_type'] && ( ! empty( $item['icon'] ) || ( ! empty( $item['selected_icon']['value'] ) && $is_new ) ) ) {
			?>
			<span class="pp-icon pp-advanced-tabs-icon-<?php echo esc_attr( $settings['icon_position'] ); ?>">
			<?php
			if ( $is_new || $migrated ) {
				Icons_Manager::render_icon( $item['selected_icon'], array( 'aria-hidden' => 'true' ) );
			} else {
				?>
				<i class="<?php echo esc_attr( $item['icon'] ); ?>" aria-hidden="true"></i>
			<?php } ?>
			</span>
			<?php
		} elseif ( 'image' === $item['pp_icon_type'] ) {
			?>
			<span class="pp-icon-img pp-advanced-tabs-icon-<?php echo esc_attr( $settings['icon_position'] ); ?>">
			<?php echo wp_kses_post( Group_Control_Image_Size::get_attachment_image_html( $item, 'icon_img', 'icon_img' ) ); ?>
			</span>
			<?php
		}
	}

	/**
	 *  Get Saved Widgets
	 *
	 *  @param string $type Type.
	 *
	 *  @return string
	 */
	public function get_page_template_options( $type = '' ) {

		$page_templates = pp_get_page_templates( $type );

		$options[-1] = __( 'Select', 'powerpack' );

		if ( count( $page_templates ) ) {
			foreach ( $page_templates as $id => $name ) {
				$options[ $id ] = $name;
			}
		} else {
			$options['no_template'] = __( 'No saved templates found!', 'powerpack' );
		}

		return $options;
	}
}
