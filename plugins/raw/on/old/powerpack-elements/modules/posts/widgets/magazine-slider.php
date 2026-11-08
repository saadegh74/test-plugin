<?php
/**
 * Magazine Slider Widget.
 *
 * @package PPE
 */

namespace PowerpackElements\Modules\Posts\Widgets;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Modules\Posts\Widgets\Posts_Base;
use PowerpackElements\Classes\PP_Helper;
use PowerpackElements\Classes\PP_Posts_Helper;

// Elementor Classes.
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Icons_Manager;
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
 * Magazine Slider Widget
 */
class Magazine_Slider extends Posts_Base {

	/**
	 * Retrieve magazine slider widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Magazine_Slider' );
	}

	/**
	 * Retrieve magazine slider widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Magazine_Slider' );
	}

	/**
	 * Retrieve magazine slider widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Magazine_Slider' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @since 1.4.13.1
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'Magazine_Slider' );
	}

	/**
	 * Retrieve the list of scripts the magazine slider widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return array(
			'swiper',
			'powerpack-frontend',
		);
	}

	/**
	 * Register magazine slider widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 2.0.3
	 * @access protected
	 */
	protected function register_controls() {

		/* Content Tab: Layout */
		$this->register_content_layout_controls();

		/* Content Tab: Query */
		$this->register_query_section_controls( '', 'magazine_slider', 'yes' );

		/* Content Tab: Post Meta */
		$this->register_content_post_meta_controls();

		/* Content Tab: Slider Option */
		$this->register_content_slider_controls();

		/* Style Tab: Layout */
		$this->register_style_layout_controls();

		/* Style Tab: Content */
		$this->register_style_content_controls();

		/* Style Tab: Title */
		$this->register_style_title_controls();

		/* Style Tab: Post Category */
		$this->register_style_post_category_controls();

		/* Style Tab: Post Meta */
		$this->register_style_post_meta_controls();

		/* Style Tab: Post Excerpt */
		$this->register_style_post_excerpt_controls();

		/* Style Tab: Post Overlay */
		$this->register_style_overlay_controls();

		/* Style Tab: Slider Arrows */
		$this->register_style_arrows_controls();

		/* Style Tab: Slider Dots */
		$this->register_style_dots_controls();
	}

	/**
	 * Content Tab: Layout
	 */
	protected function register_content_layout_controls() {
		$this->start_controls_section(
			'section_post_settings',
			array(
				'label' => __( 'Layout', 'powerpack' ),
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'       => __( 'Layout', 'powerpack' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => true,
				'toggle'      => false,
				'options'     => array(
					'layout-1' => array(
						'title' => __( 'Layout 1', 'powerpack' ),
						'icon'  => 'ppicon-layout-1',
					),
					'layout-2' => array(
						'title' => __( 'Layout 2', 'powerpack' ),
						'icon'  => 'ppicon-layout-2',
					),
					'layout-3' => array(
						'title' => __( 'Layout 3', 'powerpack' ),
						'icon'  => 'ppicon-layout-3',
					),
					'layout-4' => array(
						'title' => __( 'Layout 4', 'powerpack' ),
						'icon'  => 'ppicon-layout-4',
					),
					'layout-5' => array(
						'title' => __( 'Layout 5', 'powerpack' ),
						'icon'  => 'ppicon-layout-5',
					),
					'layout-6' => array(
						'title' => __( 'Layout 6', 'powerpack' ),
						'icon'  => 'ppicon-layout-6',
					),
				),
				'separator'   => 'none',
				'default'     => 'layout-1',
			)
		);

		$this->add_control(
			'content_vertical_position',
			array(
				'label'       => __( 'Content Position', 'powerpack' ),
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
				'separator'   => 'before',
				'default'     => 'bottom',
			)
		);

		$this->add_control(
			'content_text_alignment',
			array(
				'label'       => __( 'Text Alignment', 'powerpack' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'default'     => 'left',
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
				'selectors'   => array(
					'{{WRAPPER}} .pp-tiled-post-content' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'post_title',
			array(
				'label'        => __( 'Post Title', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'post_title_length',
			array(
				'label'       => __( 'Post Title Length', 'powerpack' ),
				'title'       => __( 'In characters', 'powerpack' ),
				'description' => __( 'Leave blank to show full title', 'powerpack' ),
				'type'        => Controls_Manager::NUMBER,
				'step'        => 1,
				'condition'   => array(
					'post_title' => 'yes',
				),
			)
		);

		$this->add_control(
			'image_position',
			array(
				'label'     => __( 'Image Position', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					''              => _x( 'Default', 'Background Image Position', 'powerpack' ),
					'center center' => _x( 'Center Center', 'Background Image Position', 'powerpack' ),
					'center left'   => _x( 'Center Left', 'Background Image Position', 'powerpack' ),
					'center right'  => _x( 'Center Right', 'Background Image Position', 'powerpack' ),
					'top center'    => _x( 'Top Center', 'Background Image Position', 'powerpack' ),
					'top left'      => _x( 'Top Left', 'Background Image Position', 'powerpack' ),
					'top right'     => _x( 'Top Right', 'Background Image Position', 'powerpack' ),
					'bottom center' => _x( 'Bottom Center', 'Background Image Position', 'powerpack' ),
					'bottom left'   => _x( 'Bottom Left', 'Background Image Position', 'powerpack' ),
					'bottom right'  => _x( 'Bottom Right', 'Background Image Position', 'powerpack' ),
				),
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .pp-media-background' => 'background-position: {{VALUE}};',
				],
			)
		);

		$this->add_control(
			'fallback_image',
			array(
				'label'     => __( 'Fallback Image', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					''            => __( 'None', 'powerpack' ),
					'placeholder' => __( 'Placeholder', 'powerpack' ),
					'custom'      => __( 'Custom', 'powerpack' ),
				),
				'default'   => '',
				'separator' => 'before',
			)
		);

		$this->add_control(
			'fallback_image_custom',
			array(
				'label'     => __( 'Fallback Image Custom', 'powerpack' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array(
					'fallback_image' => 'custom',
				),
			)
		);

		$this->add_control(
			'large_tile_heading',
			array(
				'label'     => __( 'Large Tile', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'layout!' => 'layout-5',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'      => 'image_size',
				'label'     => __( 'Image Size', 'powerpack' ),
				'default'   => 'medium_large',
				'condition' => array(
					'layout!' => 'layout-5',
				),
			)
		);

		$this->add_control(
			'post_excerpt',
			array(
				'label'        => __( 'Post Excerpt', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'no',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'layout!' => 'layout-5',
				),
			)
		);

		$this->add_control(
			'excerpt_length',
			array(
				'label'     => __( 'Excerpt Length', 'powerpack' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 20,
				'min'       => 0,
				'max'       => 58,
				'step'      => 1,
				'condition' => array(
					'layout!'      => 'layout-5',
					'post_excerpt' => 'yes',
				),
			)
		);

		$this->add_control(
			'small_tiles_heading',
			array(
				'label'     => __( 'Small Tiles', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'    => 'image_size_small',
				'label'   => __( 'Image Size', 'powerpack' ),
				'default' => 'medium_large',
			)
		);

		$this->add_control(
			'post_excerpt_small',
			array(
				'label'        => __( 'Post Excerpt', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'no',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'excerpt_length_small',
			array(
				'label'     => __( 'Excerpt Length', 'powerpack' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 20,
				'min'       => 0,
				'max'       => 58,
				'step'      => 1,
				'condition' => array(
					'post_excerpt_small' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Content Tab: Post Meta
	 */
	protected function register_content_post_meta_controls() {
		$this->start_controls_section(
			'section_post_meta',
			array(
				'label' => __( 'Post Meta', 'powerpack' ),
			)
		);

		$this->add_control(
			'post_meta',
			array(
				'label'        => __( 'Post Meta', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'post_meta_divider',
			array(
				'label'     => __( 'Post Meta Divider', 'powerpack' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '-',
				'selectors' => array(
					'{{WRAPPER}} .pp-tiled-posts-meta > span:not(:last-child):after' => 'content: "{{UNIT}}";',
				),
				'condition' => array(
					'post_meta' => 'yes',
				),
			)
		);

		$this->add_control(
			'post_author',
			array(
				'label'        => __( 'Post Author', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'post_meta' => 'yes',
				),
			)
		);

		$this->add_control(
			'post_category',
			array(
				'label'        => __( 'Post Category', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'post_meta' => 'yes',
				),
			)
		);

		$this->add_control(
			'post_date',
			array(
				'label'        => __( 'Post Date', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'post_meta' => 'yes',
				),
			)
		);

		$this->add_control(
			'date_type',
			array(
				'label'     => __( 'Date Type', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					''         => __( 'Published Date', 'powerpack' ),
					'modified' => __( 'Last Modified Date', 'powerpack' ),
					'ago'      => __( 'Time Ago', 'powerpack' ),
					'key'      => __( 'Custom Meta Key', 'powerpack' ),
				),
				'default'   => '',
				'condition' => array(
					'post_meta' => 'yes',
					'post_date' => 'yes',
				),
			)
		);

		$this->add_control(
			'date_format',
			array(
				'label'     => __( 'Date Format', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					''       => __( 'Default', 'powerpack' ),
					'F j, Y' => gmdate( 'F j, Y' ),
					'Y-m-d'  => gmdate( 'Y-m-d' ),
					'm/d/Y'  => gmdate( 'm/d/Y' ),
					'd/m/Y'  => gmdate( 'd/m/Y' ),
					'custom' => __( 'Custom', 'powerpack' ),
				),
				'default'   => '',
				'condition' => array(
					'post_meta' => 'yes',
					'post_date' => 'yes',
					'date_type' => [ '', 'modified' ],
				),
			)
		);

		$this->add_control(
			'date_custom_format',
			array(
				'label'       => __( 'Custom Format', 'powerpack' ),
				'description' => sprintf( __( 'Refer to PHP date formats <a href="%s">here</a>', 'powerpack' ), 'https://wordpress.org/support/article/formatting-date-and-time/' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => false,
				'default'     => 'F j, Y',
				'condition'   => array(
					'post_meta'   => 'yes',
					'post_date'   => 'yes',
					'date_type'   => [ '', 'modified' ],
					'date_format' => 'custom',
				),
			)
		);

		$this->add_control(
			'date_meta_key',
			array(
				'label'       => __( 'Custom Meta Key', 'powerpack' ),
				'description' => __( 'Display the post date stored in custom meta key.', 'powerpack' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => false,
				'default'     => '',
				'condition'   => array(
					'post_meta' => 'yes',
					'post_date' => 'yes',
					'date_type' => 'key',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Content Tab: Slider Options
	 */
	protected function register_content_slider_controls() {
		$this->start_controls_section(
			'section_slider_options',
			array(
				'label' => __( 'Slider Options', 'powerpack' ),
			)
		);

		$slides_per_view = range( 1, 10 );
		$slides_per_view = array_combine( $slides_per_view, $slides_per_view );

		$this->add_control(
			'slides_count',
			array(
				'type'    => Controls_Manager::SELECT,
				'label'   => __( 'Slides', 'powerpack' ),
				'options' => $slides_per_view,
				'default' => '3',
			)
		);

		$this->add_control(
			'animation_speed',
			array(
				'label'   => __( 'Animation Speed', 'powerpack' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 600,
			)
		);

		$this->add_control(
			'autoplay',
			array(
				'label'        => __( 'Autoplay', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'autoplay_speed',
			array(
				'label'     => __( 'Autoplay Speed', 'powerpack' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 3000,
				'condition' => array(
					'autoplay' => 'yes',
				),
			)
		);

		$this->add_control(
			'pause_on_hover',
			array(
				'label'              => __( 'Pause on Hover', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'label_on'           => __( 'Yes', 'powerpack' ),
				'label_off'          => __( 'No', 'powerpack' ),
				'return_value'       => 'yes',
				'frontend_available' => true,
				'condition'          => array(
					'autoplay' => 'yes',
				),
			)
		);

		$this->add_control(
			'infinite_loop',
			array(
				'label'        => __( 'Infinite Loop', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'adaptive_height',
			array(
				'label'        => __( 'Adaptive Height', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'navigation_heading',
			array(
				'label'     => __( 'Navigation', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'arrows',
			array(
				'label'        => __( 'Arrows', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'dots',
			array(
				'label'        => __( 'Pagination', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'no',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'pagination_type',
			array(
				'label'     => __( 'Pagination Type', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'bullets',
				'options'   => array(
					'bullets'  => __( 'Dots', 'powerpack' ),
					'fraction' => __( 'Fraction', 'powerpack' ),
				),
				'condition' => array(
					'dots' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Layout
	 */
	protected function register_style_layout_controls() {
		$this->start_controls_section(
			'section_layout_style',
			array(
				'label' => __( 'Layout', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'fallback_img_bg_color',
			array(
				'label'     => __( 'Tiles Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-tiled-post-bg' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'fallback_image' => '',
				),
			)
		);

		$this->add_control(
			'height',
			array(
				'label'      => __( 'Height', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 200,
						'max'  => 1000,
						'step' => 1,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 535,
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-tiled-post' => 'height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .pp-tiled-post-medium, {{WRAPPER}} .pp-tiled-post-small, {{WRAPPER}} .pp-tiled-post-large' => 'height: calc( ({{SIZE}}{{UNIT}} - {{vertical_spacing.SIZE}}px)/2 );',
				),
			)
		);

		$this->add_control(
			'horizontal_spacing',
			array(
				'label'      => __( 'Horizontal Spacing', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 5,
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-tiled-posts'       => 'margin-left: -{{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .pp-tiled-post, {{WRAPPER}} .pp-tiled-posts-layout-6 .pp-tiles-posts-left .pp-tiled-post, {{WRAPPER}} .pp-tiled-posts-layout-6 .pp-tiles-posts-right .pp-tiled-post' => 'margin-left: {{SIZE}}{{UNIT}}; width: calc( 100% - {{SIZE}}{{UNIT}} );',
					'{{WRAPPER}} .pp-tiled-post-medium' => 'width: calc( 50% - {{SIZE}}{{UNIT}} );',
					'{{WRAPPER}} .pp-tiled-post-small'  => 'width: calc( 33.333% - {{SIZE}}{{UNIT}} );',
				),
			)
		);

		$this->add_control(
			'vertical_spacing',
			array(
				'label'      => __( 'Vertical Spacing', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 5,
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-tiled-post' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Content
	 */
	protected function register_style_content_controls() {
		$this->start_controls_section(
			'section_post_content_style',
			array(
				'label' => __( 'Content', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'post_content_bg',
				'label'    => __( 'Post Content Background', 'powerpack' ),
				'types'    => array( 'classic', 'gradient' ),
				'exclude'  => array( 'image' ),
				'selector' => '{{WRAPPER}} .pp-tiled-post-content',
			)
		);

		$this->add_control(
			'post_content_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-tiled-post-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Title
	 */
	protected function register_style_title_controls() {
		$this->start_controls_section(
			'section_title_style',
			array(
				'label'     => __( 'Title', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'post_title' => 'yes',
				),
			)
		);

		$this->add_control(
			'title_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-tiled-post-title' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'post_title' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'title_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'scheme'    => Scheme_Typography::TYPOGRAPHY_1,
				'selector'  => '{{WRAPPER}} .pp-tiled-post-title',
				'condition' => array(
					'post_title' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'title_margin_bottom',
			array(
				'label'      => __( 'Margin Bottom', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-tiled-post-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'post_title' => 'yes',
				),
			)
		);

		$this->add_control(
			'large_tile_title_heading',
			array(
				'label'     => __( 'Large Tile', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'layout!'    => 'layout-5',
					'post_title' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'large_title_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'scheme'    => Scheme_Typography::TYPOGRAPHY_1,
				'selector'  => '{{WRAPPER}} .pp-tiled-post-featured .pp-tiled-post-title',
				'condition' => array(
					'layout!'    => 'layout-5',
					'post_title' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Post Category
	 */
	protected function register_style_post_category_controls() {
		$this->start_controls_section(
			'section_cat_style',
			array(
				'label'     => __( 'Post Category', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'post_category' => 'yes',
				),
			)
		);

		$this->add_control(
			'category_style',
			array(
				'label'     => __( 'Category Style', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'style-1' => __( 'Style 1', 'powerpack' ),
					'style-2' => __( 'Style 2', 'powerpack' ),
				),
				'default'   => 'style-1',
				'condition' => array(
					'post_category' => 'yes',
				),
			)
		);

		$this->add_control(
			'cat_bg_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => array(
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_4,
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-post-categories-style-2 span' => 'background: {{VALUE}}',
				),
				'condition' => array(
					'post_category'  => 'yes',
					'category_style' => 'style-2',
				),
			)
		);

		$this->add_control(
			'cat_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#fff',
				'selectors' => array(
					'{{WRAPPER}} .pp-post-categories' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'post_category' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'cat_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'scheme'    => Scheme_Typography::TYPOGRAPHY_2,
				'selector'  => '{{WRAPPER}} .pp-post-categories',
				'condition' => array(
					'post_category' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'cat_margin_bottom',
			array(
				'label'      => __( 'Margin Bottom', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'default'    => [
					'unit' => 'px',
					'size' => 10,
				],
				'selectors'  => array(
					'{{WRAPPER}} .pp-post-categories' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'post_category' => 'yes',
				),
			)
		);

		$this->add_control(
			'cat_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'default'    => [
					'top'      => 5,
					'right'    => 10,
					'bottom'   => 5,
					'left'     => 10,
					'isLinked' => true,
				],
				'selectors'  => array(
					'{{WRAPPER}} .pp-post-categories-style-2 span' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'post_category'  => 'yes',
					'category_style' => 'style-2',
				),
			)
		);

		$this->add_control(
			'large_tile_cat_heading',
			array(
				'label'     => __( 'Large Tile', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'layout!'       => 'layout-5',
					'post_category' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'large_cat_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'scheme'    => Scheme_Typography::TYPOGRAPHY_2,
				'selector'  => '{{WRAPPER}} .pp-tiled-post-featured .pp-post-categories',
				'condition' => array(
					'layout!'       => 'layout-5',
					'post_category' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Post Meta
	 */
	protected function register_style_post_meta_controls() {
		$this->start_controls_section(
			'section_meta_style',
			array(
				'label'     => __( 'Post Meta', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'post_meta' => 'yes',
				),
			)
		);

		$this->add_control(
			'meta_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#fff',
				'selectors' => array(
					'{{WRAPPER}} .pp-tiled-posts-meta' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'post_meta' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'meta_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'scheme'    => Scheme_Typography::TYPOGRAPHY_2,
				'selector'  => '{{WRAPPER}} .pp-tiled-posts-meta',
				'condition' => array(
					'post_meta' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'meta_items_spacing',
			array(
				'label'      => __( 'Items Spacing', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-tiled-posts-meta > span:not(:last-child):after' => 'margin-left: calc({{SIZE}}{{UNIT}}/2); margin-right: calc({{SIZE}}{{UNIT}}/2);',
				),
				'condition'  => array(
					'post_meta' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'meta_margin_bottom',
			array(
				'label'      => __( 'Margin Bottom', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-tiled-posts-meta' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'post_meta' => 'yes',
				),
			)
		);

		$this->add_control(
			'large_tile_meta_heading',
			array(
				'label'     => __( 'Large Tile', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'layout!'   => 'layout-5',
					'post_meta' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'large_meta_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'scheme'    => Scheme_Typography::TYPOGRAPHY_2,
				'selector'  => '{{WRAPPER}} .pp-tiled-post-featured .pp-tiled-posts-meta',
				'condition' => array(
					'layout!'   => 'layout-5',
					'post_meta' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Post Excerpt
	 */
	protected function register_style_post_excerpt_controls() {
		$this->start_controls_section(
			'section_excerpt_style',
			array(
				'label'      => __( 'Post Excerpt', 'powerpack' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => [
					'relation' => 'or',
					'terms' => [
						[
							'name'  => 'post_excerpt',
							'operator'  => '==',
							'value' => 'yes',
						],
						[
							'name'  => 'post_excerpt_small',
							'operator'  => '==',
							'value' => 'yes',
						],
					],
				],
			)
		);

		$this->add_control(
			'excerpt_text_color',
			array(
				'label'      => __( 'Text Color', 'powerpack' ),
				'type'       => Controls_Manager::COLOR,
				'default'    => '#fff',
				'selectors'  => array(
					'{{WRAPPER}} .pp-tiled-post-excerpt' => 'color: {{VALUE}}',
				),
				'conditions' => [
					'relation' => 'or',
					'terms' => [
						[
							'name'  => 'post_excerpt',
							'operator'  => '==',
							'value' => 'yes',
						],
						[
							'name'  => 'post_excerpt_small',
							'operator'  => '==',
							'value' => 'yes',
						],
					],
				],
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'       => 'excerpt_typography',
				'label'      => __( 'Typography', 'powerpack' ),
				'scheme'     => Scheme_Typography::TYPOGRAPHY_3,
				'selector'   => '{{WRAPPER}} .pp-tiled-post-excerpt',
				'conditions' => [
					'relation' => 'or',
					'terms' => [
						[
							'name'  => 'post_excerpt',
							'operator'  => '==',
							'value' => 'yes',
						],
						[
							'name'  => 'post_excerpt_small',
							'operator'  => '==',
							'value' => 'yes',
						],
					],
				],
			)
		);

		$this->add_control(
			'large_tile_excerpt_heading',
			array(
				'label'      => __( 'Large Tile', 'powerpack' ),
				'type'       => Controls_Manager::HEADING,
				'separator'  => 'before',
				'conditions' => [
					'relation' => 'or',
					'terms' => [
						[
							'relation' => 'and',
							'terms' => [
								[
									'name'  => 'post_excerpt',
									'operator'  => '==',
									'value' => 'yes',
								],
								[
									'name'  => 'layout',
									'operator'  => '!==',
									'value' => 'layout-5',
								],
							],
						],
						[
							'relation' => 'and',
							'terms' => [
								[
									'name'  => 'post_excerpt_small',
									'operator'  => '==',
									'value' => 'yes',
								],
								[
									'name'  => 'layout',
									'operator'  => '!==',
									'value' => 'layout-5',
								],
							],
						],
					],
				],
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'       => 'large_excerpt_typography',
				'label'      => __( 'Typography', 'powerpack' ),
				'scheme'     => Scheme_Typography::TYPOGRAPHY_3,
				'selector'   => '{{WRAPPER}} .pp-tiled-post-featured .pp-tiled-post-excerpt',
				'conditions' => [
					'relation' => 'or',
					'terms' => [
						[
							'relation' => 'and',
							'terms' => [
								[
									'name'  => 'post_excerpt',
									'operator'  => '==',
									'value' => 'yes',
								],
								[
									'name'  => 'layout',
									'operator'  => '!==',
									'value' => 'layout-5',
								],
							],
						],
						[
							'relation' => 'and',
							'terms' => [
								[
									'name'  => 'post_excerpt_small',
									'operator'  => '==',
									'value' => 'yes',
								],
								[
									'name'  => 'layout',
									'operator'  => '!==',
									'value' => 'layout-5',
								],
							],
						],
					],
				],
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Overlay
	 */
	protected function register_style_overlay_controls() {
		$this->start_controls_section(
			'section_overlay_style',
			array(
				'label' => __( 'Overlay', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->start_controls_tabs( 'tabs_overlay_style' );

		$this->start_controls_tab(
			'tab_overlay_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'post_overlay_bg',
				'label'    => __( 'Overlay Background', 'powerpack' ),
				'types'    => array( 'classic', 'gradient' ),
				'exclude'  => array( 'image' ),
				'selector' => '{{WRAPPER}} .pp-tiled-post-overlay',
			)
		);

		$this->add_control(
			'post_overlay_opacity',
			array(
				'label'     => __( 'Opacity', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.1,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-tiled-post-overlay' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_overlay_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'post_overlay_bg_hover',
				'label'    => __( 'Overlay Background', 'powerpack' ),
				'types'    => array( 'classic', 'gradient' ),
				'exclude'  => array( 'image' ),
				'selector' => '{{WRAPPER}} .pp-tiled-post:hover .pp-tiled-post-overlay',
			)
		);

		$this->add_control(
			'post_overlay_opacity_hover',
			array(
				'label'     => __( 'Opacity', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.1,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-tiled-post:hover .pp-tiled-post-overlay' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Arrows
	 */
	protected function register_style_arrows_controls() {
		$this->start_controls_section(
			'section_arrows_style',
			array(
				'label'     => __( 'Arrows', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'arrows' => 'yes',
				),
			)
		);

		$this->add_control(
			'select_arrow',
			array(
				'label'                  => __( 'Choose Arrow', 'powerpack' ),
				'type'                   => Controls_Manager::ICONS,
				'fa4compatibility'       => 'arrow',
				'label_block'            => false,
				'default'                => array(
					'value'   => 'fas fa-angle-right',
					'library' => 'fa-solid',
				),
				'skin'                   => 'inline',
				'exclude_inline_options' => 'svg',
				'recommended'            => array(
					'fa-regular' => array(
						'arrow-alt-circle-right',
						'caret-square-right',
						'hand-point-right',
					),
					'fa-solid'   => array(
						'angle-right',
						'angle-double-right',
						'chevron-right',
						'chevron-circle-right',
						'arrow-right',
						'long-arrow-alt-right',
						'caret-right',
						'caret-square-right',
						'arrow-circle-right',
						'arrow-alt-circle-right',
						'toggle-right',
						'hand-point-right',
					),
				),
			)
		);

		$this->add_responsive_control(
			'arrows_size',
			array(
				'label'      => __( 'Arrows Size', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array( 'size' => '22' ),
				'range'      => array(
					'px' => array(
						'min'  => 15,
						'max'  => 100,
						'step' => 1,
					),
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-slider-arrow' => 'font-size: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'arrows' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'arrows_position',
			array(
				'label'      => __( 'Align Arrows', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min'  => -100,
						'max'  => 50,
						'step' => 1,
					),
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-arrow-next' => 'right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .pp-arrow-prev' => 'left: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'arrows' => 'yes',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_arrows_style' );

		$this->start_controls_tab(
			'tab_arrows_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'arrows' => 'yes',
				),
			)
		);

		$this->add_control(
			'arrows_bg_color_normal',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-slider-arrow' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'arrows' => 'yes',
				),
			)
		);

		$this->add_control(
			'arrows_color_normal',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-slider-arrow' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'arrows' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'arrows_border_normal',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-slider-arrow',
				'condition'   => array(
					'arrows' => 'yes',
				),
			)
		);

		$this->add_control(
			'arrows_border_radius_normal',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-slider-arrow' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'arrows' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_arrows_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'arrows' => 'yes',
				),
			)
		);

		$this->add_control(
			'arrows_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-slider-arrow:hover' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'arrows' => 'yes',
				),
			)
		);

		$this->add_control(
			'arrows_color_hover',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-slider-arrow:hover' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'arrows' => 'yes',
				),
			)
		);

		$this->add_control(
			'arrows_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-slider-arrow:hover',
				),
				'condition' => array(
					'arrows' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'arrows_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-slider-arrow' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
				'condition'  => array(
					'arrows' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Dots
	 */
	protected function register_style_dots_controls() {
		$this->start_controls_section(
			'section_dots_style',
			array(
				'label'     => __( 'Pagination: Dots', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->add_control(
			'dots_position',
			array(
				'label'        => __( 'Position', 'powerpack' ),
				'type'         => Controls_Manager::SELECT,
				'options'      => array(
					'inside'  => __( 'Inside', 'powerpack' ),
					'outside' => __( 'Outside', 'powerpack' ),
				),
				'default'      => 'outside',
				'prefix_class' => 'pp-swiper-slider-pagination-',
				'condition'    => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->add_responsive_control(
			'dots_size',
			array(
				'label'      => __( 'Size', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min'  => 2,
						'max'  => 40,
						'step' => 1,
					),
				),
				'size_units' => '',
				'selectors'  => array(
					'{{WRAPPER}} .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->add_responsive_control(
			'dots_spacing',
			array(
				'label'      => __( 'Spacing', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min'  => 1,
						'max'  => 30,
						'step' => 1,
					),
				),
				'size_units' => '',
				'selectors'  => array(
					'{{WRAPPER}} .swiper-pagination-bullet' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_dots_style' );

		$this->start_controls_tab(
			'tab_dots_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->add_control(
			'dots_color_normal',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .swiper-pagination-bullet' => 'background: {{VALUE}};',
				),
				'condition' => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->add_control(
			'active_dot_color_normal',
			array(
				'label'     => __( 'Active Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'background: {{VALUE}};',
				),
				'condition' => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'dots_border_normal',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .swiper-pagination-bullet',
				'condition'   => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->add_control(
			'dots_border_radius_normal',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .swiper-pagination-bullet' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->add_responsive_control(
			'dots_margin',
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
					'{{WRAPPER}} .swiper-pagination' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'          => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_dots_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->add_control(
			'dots_color_hover',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .swiper-pagination-bullet:hover' => 'background: {{VALUE}};',
				),
				'condition' => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->add_control(
			'dots_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .swiper-pagination-bullet:hover' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Carousel Settings.
	 *
	 * @access public
	 */
	public function slider_settings() {
		$settings = $this->get_settings();

		$slider_options = array(
			'direction'      => 'horizontal',
			'speed'          => ( '' !== $settings['animation_speed'] ) ? $settings['animation_speed'] : 600,
			'slidesPerView'  => 1,
			'slidesPerGroup' => 1,
			'spaceBetween'   => ( '' !== $settings['horizontal_spacing']['size'] ) ? $settings['horizontal_spacing']['size'] : 10,
			'autoHeight'     => ( 'yes' === $settings['adaptive_height'] ),
			'loop'           => ( 'yes' === $settings['infinite_loop'] ),
		);

		if ( 'yes' === $settings['autoplay'] && ! empty( $settings['autoplay_speed'] ) ) {
			$autoplay_speed = $settings['autoplay_speed'];
		} else {
			$autoplay_speed = 999999;
		}

		$slider_options['autoplay'] = array(
			'delay' => $autoplay_speed,
		);

		if ( 'yes' === $settings['dots'] ) {
			$slider_options['pagination'] = array(
				'el'        => '.swiper-pagination-' . esc_attr( $this->get_id() ),
				'type'      => $settings['pagination_type'],
				'clickable' => true,
			);
		}

		if ( 'yes' === $settings['arrows'] ) {
			$slider_options['navigation'] = array(
				'nextEl' => '.swiper-button-next-' . esc_attr( $this->get_id() ),
				'prevEl' => '.swiper-button-prev-' . esc_attr( $this->get_id() ),
			);
		}

		/*
		 if ( is_rtl() ) {
			$slider_options['rtl'] = true;
		} */

		return $slider_options;
	}

	/**
	 * Render coupons carousel dots output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_dots() {
		$settings = $this->get_settings_for_display();

		if ( 'yes' === $settings['dots'] ) { ?>
			<!-- Add Pagination -->
			<div class="swiper-pagination swiper-pagination-<?php echo esc_attr( $this->get_id() ); ?>"></div>
			<?php
		}
	}

	/**
	 * Render coupons carousel arrows output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_arrows() {
		PP_Helper::render_arrows( $this );
	}

	/**
	 * Render magazine slider widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings();

		$slider_options = $this->slider_settings();

		$this->add_render_attribute(
			array(
				'tiled-posts'  => array(
					'class'                => array(
						'pp-magazine-slider',
						'pp-swiper-slider',
						'pp-tiled-posts',
						'pp-tiled-posts-' . $settings['layout'],
						'swiper-container',
						'clearfix',
					),
					'data-slider-settings' => wp_json_encode( $slider_options ),
				),
				'post-content' => array(
					'class' => 'pp-tiled-post-content',
				),
			)
		);

		if ( $settings['content_vertical_position'] ) {
			$this->add_render_attribute( 'post-content', 'class', 'pp-tiled-post-content-' . $settings['content_vertical_position'] );
		}

		$this->add_render_attribute( 'post-categories', 'class', 'pp-post-categories' );

		if ( $settings['category_style'] ) {
			$this->add_render_attribute( 'post-categories', 'class', 'pp-post-categories-' . $settings['category_style'] );
		}
		?>
		<div class="swiper-container-wrap swiper">
			<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'tiled-posts' ) ); ?>>
				<div class="swiper-wrapper">
					<?php
					$count = 1;

					$layout = $settings['layout'];

					if ( 'layout-1' === $layout ) {
						$posts_count = absint( $settings['slides_count'] * 4 );
					} elseif ( 'layout-2' === $layout || 'layout-3' === $layout ) {
						$posts_count = $settings['slides_count'] * 3;
					} elseif ( 'layout-4' === $layout || 'layout-5' === $layout ) {
						$posts_count = $settings['slides_count'] * 5;
					} elseif ( 'layout-4' === $layout || 'layout-6' === $layout ) {
						$posts_count = $settings['slides_count'] * 5;
					} else {
						$posts_count = absint( $settings['slides_count'] * 4 );
					}

					/* $args = $this->query_posts_args( '', '', '', '', '', 'magazine_slider', 'yes', '', $posts_count );
					$posts_query = new \WP_Query( $args ); */
					$this->query_posts( '', '', '', '', '', 'magazine_slider', 'yes', '', $posts_count );
					$posts_query = $this->get_query();
					$total_posts = $posts_query->found_posts;

					if ( $posts_query->have_posts() ) :
						while ( $posts_query->have_posts() ) :
							$posts_query->the_post();

							switch ( $layout ) {
								case 'layout-1':
									$this->render_layout_1( $count, $layout );
									break;
								case 'layout-2':
									$this->render_layout_2( $count, $layout );
									break;
								case 'layout-3':
									$this->render_layout_2( $count, $layout );
									break;
								case 'layout-4':
									$this->render_layout_4( $count, $layout );
									break;
								case 'layout-5':
									$this->render_layout_5( $count, $layout );
									break;
								case 'layout-6':
									$this->render_layout_6( $count, $layout );
									break;
								default:
									$this->render_layout_1( $count, $layout );
							}

							$count++;
						endwhile;
					endif;
					wp_reset_postdata();
					?>
				</div>
			</div>
			<?php
				$this->render_dots();
				$this->render_arrows();
			?>
		</div>
		<?php
	}

	/**
	 * Render posts body output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @param  int    $count   post count.
	 * @param  string $layout  posts layout.
	 *
	 * @access protected
	 */
	protected function render_layout_1( $count, $layout ) {
		$settings = $this->get_settings();

		$open_array_left   = array();
		$open_array_right  = array();
		$close_array_right = array();

		for ( $x = 0; $x <= 100; $x++ ) {
			$open_array_left[] = 4 * $x + 1;
		}

		for ( $x = 0; $x <= 100; $x++ ) {
			$open_array_right[] = 4 * $x + 2;
		}

		for ( $x = 0; $x <= 100; $x++ ) {
			$close_array_right[] = 4 * $x;
		}

		if ( in_array( $count, $open_array_left, true ) ) {
			?>
			<div class="pp-tiled-posts-slide swiper-slide">
			<div class="pp-tiles-posts-left">
			<?php
		}

		if ( in_array( $count, $open_array_right, true ) ) {
			?>
			<div class="pp-tiles-posts-right">
			<?php
		}

		$this->render_post_body( $count, $layout );

		if ( in_array( $count, $open_array_left, true ) ) {
			?>
			</div>
			<?php
		}

		if ( in_array( $count, $close_array_right, true ) ) {
			?>
			</div>
			</div>
			<?php
		}
	}

	/**
	 * Render posts body output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @param  int    $count   post count.
	 * @param  string $layout  posts layout.
	 *
	 * @access protected
	 */
	protected function render_layout_2( $count, $layout ) {
		$settings = $this->get_settings();

		$open_array_left   = array();
		$open_array_right  = array();
		$close_array_right = array();

		for ( $x = 0; $x <= 100; $x++ ) {
			$open_array_left[] = 3 * $x + 1;
		}

		for ( $x = 0; $x <= 100; $x++ ) {
			$open_array_right[] = 3 * $x + 2;
		}

		for ( $x = 0; $x <= 100; $x++ ) {
			$close_array_right[] = 3 * $x;
		}

		if ( in_array( $count, $open_array_left, true ) ) {
			?>
			<div class="pp-tiled-posts-slide swiper-slide">
			<div class="pp-tiles-posts-left">
			<?php
		}

		if ( in_array( $count, $open_array_right, true ) ) {
			?>
			<div class="pp-tiles-posts-right">
			<?php
		}

		$this->render_post_body( $count, $layout );

		if ( in_array( $count, $open_array_left, true ) ) {
			?>
			</div>
			<?php
		}

		if ( in_array( $count, $close_array_right, true ) ) {
			?>
			</div>
			</div>
			<?php
		}
	}

	/**
	 * Render posts body output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @param  int    $count   post count.
	 * @param  string $layout  posts layout.
	 *
	 * @access protected
	 */
	protected function render_layout_4( $count, $layout ) {
		$settings = $this->get_settings();

		$open_array_left   = array();
		$open_array_right  = array();
		$close_array_right = array();

		for ( $x = 0; $x <= 100; $x++ ) {
			$open_array_left[] = 5 * $x + 1;
		}

		for ( $x = 0; $x <= 100; $x++ ) {
			$open_array_right[] = 5 * $x + 2;
		}

		for ( $x = 0; $x <= 100; $x++ ) {
			$close_array_right[] = 5 * $x;
		}

		if ( in_array( $count, $open_array_left, true ) ) {
			?>
			<div class="pp-tiled-posts-slide swiper-slide">
			<div class="pp-tiles-posts-left">
			<?php
		}

		if ( in_array( $count, $open_array_right, true ) ) {
			?>
			<div class="pp-tiles-posts-right">
			<?php
		}

		$this->render_post_body( $count, $layout );

		if ( in_array( $count, $open_array_left, true ) ) {
			?>
			</div>
			<?php
		}

		if ( in_array( $count, $close_array_right, true ) ) {
			?>
			</div>
			</div>
			<?php
		}
	}

	/**
	 * Render posts body output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @param  int    $count   post count.
	 * @param  string $layout  posts layout.
	 *
	 * @access protected
	 */
	protected function render_layout_5( $count, $layout ) {
		$settings = $this->get_settings();

		$open_array_slide  = array();
		$close_array_slide = array();

		for ( $x = 0; $x <= 100; $x++ ) {
			$open_array_slide[] = 5 * $x + 1;
		}

		for ( $x = 0; $x <= 100; $x++ ) {
			$close_array_slide[] = 5 * $x;
		}

		if ( in_array( $count, $open_array_slide, true ) ) {
			?>
			<div class="pp-tiled-posts-slide swiper-slide">
			<?php
		}

		$this->render_post_body( $count, $layout );

		if ( in_array( $count, $close_array_slide, true ) ) {
			?>
			</div>
			<?php
		}
	}

	/**
	 * Render posts body output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @param  int    $count   post count.
	 * @param  string $layout  posts layout.
	 *
	 * @access protected
	 */
	protected function render_layout_6( $count, $layout ) {
		$settings = $this->get_settings();

		$open_array_left   = array();
		$open_array_center = array();
		$open_array_right  = array();
		$close_array_right = array();

		for ( $x = 0; $x <= 100; $x++ ) {
			$open_array_left[] = 5 * $x + 1;
		}

		for ( $x = 0; $x <= 100; $x++ ) {
			$open_array_center[] = 5 * $x + 3;
		}

		for ( $x = 0; $x <= 100; $x++ ) {
			$open_array_right[] = 5 * $x + 4;
		}

		for ( $x = 0; $x <= 100; $x++ ) {
			$close_array_right[] = 5 * $x;
		}

		for ( $x = 0; $x <= 100; $x++ ) {
			$close_array_left[] = 5 * $x + 2;
		}

		if ( in_array( $count, $open_array_left, true ) ) {
			?>
			<div class="pp-tiled-posts-slide swiper-slide">
			<div class="pp-tiles-posts-left">
			<?php
		}

		if ( in_array( $count, $open_array_center, true ) ) {
			?>
			<div class="pp-tiles-posts-center">
			<?php
		}

		if ( in_array( $count, $open_array_right, true ) ) {
			?>
			<div class="pp-tiles-posts-right">
			<?php
		}

		$this->render_post_body( $count, $layout );

		if ( in_array( $count, $close_array_left, true ) ) {
			?>
			</div>
			<?php
		}

		if ( in_array( $count, $open_array_center, true ) ) {
			?>
			</div>
			<?php
		}

		if ( in_array( $count, $close_array_right, true ) ) {
			?>
			</div>
			</div>
			<?php
		}
	}

	/**
	 * Get post date
	 *
	 * @since 2.3.7
	 * @access protected
	 */
	protected function get_post_date() {
		$settings = $this->get_settings_for_display();
		$date_type = $settings['date_type'];
		$date_format = $settings['date_format'];
		$date_custom_format = $settings['date_custom_format'];
		$date = '';

		if ( 'custom' === $date_format && $date_custom_format ) {
			$date_format = $date_custom_format;
		}

		if ( 'ago' === $date_type ) {
			$date = sprintf( _x( '%s ago', '%s = human-readable time difference', 'powerpack' ), human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) );
		} elseif ( 'modified' === $date_type ) {
			$date = get_the_modified_date( $date_format, get_the_ID() );
		} elseif ( 'key' === $date_type ) {
			$date_meta_key = $settings['date_meta_key'];
			if ( $date_meta_key ) {
				$date = get_post_meta( get_the_ID(), $date_meta_key, 'true' );
			}
		} else {
			$date = get_the_date( $date_format );
		}

		if ( '' === $date ) {
			$date = get_the_date( $date_format );
		}

		return apply_filters( 'ppe_tiled_posts_date', $date, get_the_ID() );
	}

	/**
	 * Render posts body output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @param  int    $count   post count.
	 * @param  string $layout  posts layout.
	 *
	 * @access protected
	 */
	protected function render_post_body( $count, $layout ) {
		$settings = $this->get_settings();

		$this->add_render_attribute(
			'post-' . $count,
			'class',
			array(
				'pp-tiled-post',
				'pp-tiled-post-' . intval( $count ),
				$this->get_post_class( $count, $layout ),
			)
		);

		$post_type_name = $settings['post_type'];
		if ( has_post_thumbnail() || 'attachment' === $post_type_name ) {
			if ( 'attachment' === $post_type_name ) {
				$image_id = get_the_ID();
			} else {
				$image_id = get_post_thumbnail_id( get_the_ID() );
			}
			if ( 1 === $count && 'layout-5' !== $layout ) {
				$post_thumb_url = Group_Control_Image_Size::get_attachment_image_src( $image_id, 'image_size', $settings );
			} else {
				$post_thumb_url = Group_Control_Image_Size::get_attachment_image_src( $image_id, 'image_size_small', $settings );
			}
		} else {
			if ( 'placeholder' === $settings['fallback_image'] ) {
				$post_thumb_url = Utils::get_placeholder_image_src();
			} elseif ( 'custom' === $settings['fallback_image'] && ! empty( $settings['fallback_image_custom']['url'] ) ) {
				$custom_image_id = $settings['fallback_image_custom']['id'];
				if ( 1 === $count && 'layout-5' !== $layout ) {
					$post_thumb_url = Group_Control_Image_Size::get_attachment_image_src( $custom_image_id, 'image_size', $settings );
				} else {
					$post_thumb_url = Group_Control_Image_Size::get_attachment_image_src( $custom_image_id, 'image_size_small', $settings );
				}
			} else {
				$post_thumb_url = '';
			}
		}
		?>
		<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'post-' . $count ) ); ?>>
			<div class="pp-tiled-post-bg pp-media-background" 
			<?php
			if ( $post_thumb_url ) {
				echo "style='background-image:url(" . esc_url( $post_thumb_url ) . ")'"; }
			?>
			>
			</div>
			<div class="pp-tiled-post-overlay pp-media-overlay"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"></a></div>
			<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'post-content' ) ); ?>>
				<?php if ( 'yes' === $settings['post_meta'] ) { ?>
					<?php if ( 'yes' === $settings['post_category'] ) { ?>
						<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'post-categories' ) ); ?>>
							<span>
								<?php
									$category = get_the_category();
								if ( $category ) {
									echo esc_attr( $category[0]->name );
								}
								?>
							</span>
						</div>
					<?php } ?>
				<?php } ?>
				<?php if ( 'yes' === $settings['post_title'] ) { ?>
					<header>
						<h2 class="pp-tiled-post-title">
							<?php echo wp_kses_post( $this->get_post_title_length( get_the_title() ) ); ?>
						</h2>
					</header>
				<?php } ?>
				<?php if ( 'yes' === $settings['post_meta'] ) { ?>
					<div class="pp-tiled-posts-meta">
						<?php if ( 'yes' === $settings['post_author'] ) { ?>
							<span class="pp-post-author">
								<?php echo get_the_author(); ?>
							</span>
						<?php } ?>
						<?php
						if ( 'yes' === $settings['post_date'] ) {
							printf(
								'<span class="pp-post-date"><span class="screen-reader-text">%1$s </span>%2$s</span>',
								esc_html__( 'Posted on', 'powerpack' ),
								wp_kses_post( $this->get_post_date() )
							);
						}
						?>
					</div>
				<?php } ?>

				<?php $this->render_post_excerpt( $count, $layout ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render posts body output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @param  int    $count   post count.
	 * @param  string $layout  posts layout.
	 *
	 * @access protected
	 */
	protected function render_post_excerpt( $count, $layout ) {
		$settings = $this->get_settings();

		if ( in_array( $count, $this->featured_tiles_positions( $layout ), true ) && ( 'layout-5' !== $layout ) ) {
			$post_excerpt = $settings['post_excerpt'];
			$limit        = $settings['excerpt_length'];
		} else {
			$post_excerpt = $settings['post_excerpt_small'];
			$limit        = $settings['excerpt_length_small'];
		}

		if ( 'yes' === $post_excerpt ) {
			?>
			<div class="pp-tiled-post-excerpt">
				<?php echo wp_kses_post( $this->get_custom_post_excerpt( $limit ) ); ?>
			</div>
			<?php
		}
	}

	/**
	 * Get post class.
	 *
	 * @param  string $layout  posts layout.
	 *
	 * @access protected
	 */
	protected function featured_tiles_positions( $layout = 'layout-1' ) {
		$number = array();

		for ( $x = 0; $x <= 100; $x++ ) {
			switch ( $layout ) {
				case 'layout-1':
					$number[] = 4 * $x + 1;
					break;

				case 'layout-2':
					$number[] = 3 * $x + 1;
					break;

				case 'layout-3':
					$number[] = 3 * $x + 1;
					break;

				case 'layout-4':
					$number[] = 5 * $x + 1;
					break;

				case 'layout-6':
					$number[] = 5 * $x + 3;
					break;
			}
		}

		return $number;
	}

	/**
	 * Get post class.
	 *
	 * @param  string $layout  posts layout.
	 *
	 * @access protected
	 */
	protected function large_tiles_positions( $layout = 'layout-1' ) {
		$number = array();

		for ( $x = 0; $x <= 100; $x++ ) {
			switch ( $layout ) {
				case 'layout-1':
					$number[] = 4 * $x + 2;
					break;

				case 'layout-2':
					$number[] = 3 * $x + 2;
					$number[] = 3 * $x + 3;
					break;

				case 'layout-3':
					$number[] = 3 * $x + 2;
					$number[] = 3 * $x + 3;
					break;
			}
		}

		return $number;
	}

	/**
	 * Get post class.
	 *
	 * @param  string $layout  posts layout.
	 *
	 * @access protected
	 */
	protected function medium_tiles_positions( $layout = 'layout-1' ) {
		$number = array();

		for ( $x = 0; $x <= 100; $x++ ) {
			switch ( $layout ) {
				case 'layout-1':
					$number[] = 4 * $x + 3;
					$number[] = 4 * $x + 4;
					break;

				case 'layout-4':
					$number[] = 5 * $x + 2;
					$number[] = 5 * $x + 3;
					$number[] = 5 * $x + 4;
					$number[] = 5 * $x + 5;
					break;

				case 'layout-5':
					$number[] = 5 * $x + 1;
					$number[] = 5 * $x + 2;
					break;

				case 'layout-6':
					$number[] = 5 * $x + 1;
					$number[] = 5 * $x + 2;
					$number[] = 5 * $x + 4;
					$number[] = 5 * $x + 5;
					break;
			}
		}

		return $number;
	}

	/**
	 * Get post class.
	 *
	 * @param  string $layout  posts layout.
	 *
	 * @access protected
	 */
	protected function small_tiles_positions( $layout = 'layout-1' ) {
		$number = array();

		for ( $x = 0; $x <= 100; $x++ ) {
			if ( 'layout-5' === $layout ) {
				$number[] = 5 * $x + 3;
				$number[] = 5 * $x + 4;
				$number[] = 5 * $x + 5;
			}
		}

		return $number;
	}

	/**
	 * Get post class.
	 *
	 * @param  int    $count   post count.
	 * @param  string $layout  posts layout.
	 *
	 * @access protected
	 */
	protected function get_post_class( $count, $layout ) {
		$settings = $this->get_settings();

		$class = '';

		if ( in_array( $count, $this->featured_tiles_positions( $layout ), true ) && ( 'layout-5' !== $layout ) ) {
			$class = 'pp-tiled-post-featured';
		}
		if (
			( in_array( $count, $this->large_tiles_positions( $layout ), true ) && 'layout-1' === $layout ) ||
			( in_array( $count, $this->large_tiles_positions( $layout ), true ) && ( 'layout-2' === $layout || 'layout-3' === $layout ) )
			) {
			$class = 'pp-tiled-post-large';
		}
		if (
			( in_array( $count, $this->medium_tiles_positions( $layout ), true ) && 'layout-1' === $layout ) ||
			( in_array( $count, $this->medium_tiles_positions( $layout ), true ) && 'layout-5' === $layout ) ||
			( in_array( $count, $this->medium_tiles_positions( $layout ), true ) && 'layout-6' === $layout ) ) {
			$class = 'pp-tiled-post-medium';
		}
		if ( in_array( $count, $this->medium_tiles_positions( $layout ), true ) && 'layout-4' === $layout ) {
			$class = 'pp-tiled-post-medium';
		}
		if ( in_array( $count, $this->small_tiles_positions( $layout ), true ) && 'layout-5' === $layout ) {
			$class = 'pp-tiled-post-small';
		}

		return $class;
	}

	/**
	 * Get post title length.
	 *
	 * @param  string $title post title.
	 *
	 * @access protected
	 */
	protected function get_post_title_length( $title ) {
		$settings = $this->get_settings();

		$length = absint( $settings['post_title_length'] );

		if ( $length ) {
			if ( strlen( $title ) > $length ) {
				$title = substr( $title, 0, $length ) . '&hellip;';
			}
		}

		return $title;
	}

	/**
	 * Get custom post excerpt.
	 *
	 * @param  int $limit   post excerpt length.
	 *
	 * @access protected
	 */
	protected function get_custom_post_excerpt( $limit ) {
		$excerpt = explode( ' ', get_the_excerpt(), $limit );

		if ( count( $excerpt ) >= $limit ) {
			array_pop( $excerpt );
			$excerpt = implode( ' ', $excerpt ) . '...';
		} else {
			$excerpt = implode( ' ', $excerpt );
		}

		$excerpt = preg_replace( '`[[^]]*]`', '', $excerpt );

		return $excerpt;
	}
}
