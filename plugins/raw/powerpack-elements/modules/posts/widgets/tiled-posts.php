<?php
namespace PowerpackElements\Modules\Posts\Widgets;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Modules\Posts\Widgets\Posts_Base;
use PowerpackElements\Classes\PP_Posts_Helper;
use PowerpackElements\Classes\PP_Config;

// Elementor Classes.
use Elementor\Controls_Manager;
use Elementor\Utils;
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
 * Tiled Posts Widget
 */
class Tiled_Posts extends Posts_Base {

	/**
	 * Retrieve tiled posts widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Tiled_Posts' );
	}

	/**
	 * Retrieve tiled posts widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Tiled_Posts' );
	}

	/**
	 * Retrieve tiled posts widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Tiled_Posts' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'Tiled_Posts' );
	}

	/**
	 * Register tiled posts widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 2.0.3
	 * @access protected
	 */
	protected function register_controls() {

		/* Content Tab: Layout */
		$this->register_content_layout_controls();

		/* Content Tab: Other Posts */
		$this->register_content_other_posts_controls();

		/* Content Tab: Query */
		$this->register_query_section_controls( '', 'tiled_posts', 'yes' );

		/* Content Tab: Post Meta */
		$this->register_content_post_meta_controls();

		/* Content Tab: Help Docs */
		$this->register_content_help_docs();

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

		/* Style Tab: Button */
		$this->register_style_button_controls();

		/* Style Tab: Post Overlay */
		$this->register_style_overlay_controls();
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
			'read_more_button',
			array(
				'label'        => __( 'Read More Button', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'read_more_button_text',
			array(
				'label'       => __( 'Button Text', 'powerpack' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Read More', 'powerpack' ),
				'placeholder' => __( 'Read More', 'powerpack' ),
				'condition'   => array(
					'read_more_button' => 'yes',
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
	 * Content Tab: Other Posts
	 */
	protected function register_content_other_posts_controls() {
		$this->start_controls_section(
			'section_other_posts',
			array(
				'label' => __( 'Other Posts', 'powerpack' ),
			)
		);

		$this->add_control(
			'other_posts',
			array(
				'label'        => __( 'Show Other Posts', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'no',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'other_posts_count',
			array(
				'label'       => __( 'Posts Count', 'powerpack' ),
				'description' => __( 'Leave blank to show all posts', 'powerpack' ),
				'type'        => Controls_Manager::NUMBER,
				'step'        => 1,
				'default'     => 4,
				'condition'   => array(
					'other_posts' => 'yes',
				),
			)
		);

		$this->add_control(
			'other_posts_columns',
			array(
				'label'     => __( 'Columns', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'1' => __( '1', 'powerpack' ),
					'2' => __( '2', 'powerpack' ),
					'3' => __( '3', 'powerpack' ),
					'4' => __( '4', 'powerpack' ),
				),
				'default'   => '2',
				'condition' => array(
					'other_posts' => 'yes',
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
	 * Content Tab: Help Docs
	 *
	 * @since 1.4.8
	 * @access protected
	 */
	protected function register_content_help_docs() {

		$help_docs = PP_Config::get_widget_help_links( 'Tiled_Posts' );

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

		$this->add_responsive_control(
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
					'{{WRAPPER}} .pp-tiled-post-medium, {{WRAPPER}} .pp-tiled-post-small, {{WRAPPER}} .pp-tiled-post-xs, {{WRAPPER}} .pp-tiled-post-large' => 'height: calc( ({{SIZE}}{{UNIT}} - {{vertical_spacing.SIZE}}px)/2 );',
					'(mobile){{WRAPPER}} .pp-tiled-post' => 'height: calc( ({{SIZE}}{{UNIT}} - {{vertical_spacing.SIZE}}px)/2 );',
				),
			)
		);

		$this->add_responsive_control(
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
					'{{WRAPPER}} .pp-tiled-post-xs'     => 'width: calc( 25% - {{SIZE}}{{UNIT}} );',
				),
			)
		);

		$this->add_responsive_control(
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

		$this->add_control(
			'tiles_style_heading',
			array(
				'label'     => __( 'Tiles', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'fallback_img_bg_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
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

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'tiles_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-tiled-post',
			)
		);

		$this->add_responsive_control(
			'tiles_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-tiled-post' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'tiles_box_shadow',
				'selector' => '{{WRAPPER}} .pp-tiled-post',
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

		$this->add_responsive_control(
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
				'label'     => __( 'Color', 'powerpack' ),
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

		$this->add_control(
			'title_text_color_hover',
			array(
				'label'     => __( 'Hover Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-tiled-post:hover .pp-tiled-post-title' => 'color: {{VALUE}}',
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
					'value' => Scheme_Color::COLOR_1,
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
				'label'     => __( 'Color', 'powerpack' ),
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

		$this->add_control(
			'cat_text_color_hover',
			array(
				'label'     => __( 'Hover Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-tiled-post:hover .pp-post-categories' => 'color: {{VALUE}}',
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
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'size_units' => array( 'px' ),
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
				'label'     => __( 'Color', 'powerpack' ),
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

		$this->add_control(
			'meta_text_color_hover',
			array(
				'label'     => __( 'Hover Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-tiled-post:hover .pp-tiled-posts-meta' => 'color: {{VALUE}}',
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
				'label'      => __( 'Color', 'powerpack' ),
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

		$this->add_control(
			'excerpt_text_color_hover',
			array(
				'label'      => __( 'Hover Color', 'powerpack' ),
				'type'       => Controls_Manager::COLOR,
				'default'    => '',
				'selectors'  => array(
					'{{WRAPPER}} .pp-tiled-post:hover .pp-tiled-post-excerpt' => 'color: {{VALUE}}',
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
	 * Style Tab: Button
	 * -------------------------------------------------
	 */
	protected function register_style_button_controls() {
		$this->start_controls_section(
			'section_button_style',
			array(
				'label'     => __( 'Read More Button', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'read_more_button' => 'yes',
				),
			)
		);

		$this->add_control(
			'button_spacing',
			array(
				'label'     => __( 'Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 20,
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 60,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-tiled-post-button' => 'margin-top: {{SIZE}}px;',
				),
				'condition' => array(
					'read_more_button' => 'yes',
				),
			)
		);

		$this->add_control(
			'button_size',
			array(
				'label'     => __( 'Size', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'md',
				'options'   => array(
					'xs' => __( 'Extra Small', 'powerpack' ),
					'sm' => __( 'Small', 'powerpack' ),
					'md' => __( 'Medium', 'powerpack' ),
					'lg' => __( 'Large', 'powerpack' ),
					'xl' => __( 'Extra Large', 'powerpack' ),
				),
				'condition' => array(
					'read_more_button' => 'yes',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'read_more_button' => 'yes',
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
					'{{WRAPPER}} .pp-tiled-post-button' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'read_more_button' => 'yes',
				),
			)
		);

		$this->add_control(
			'button_bg_color_normal',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => array(
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_4,
				),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-tiled-post-button' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'read_more_button' => 'yes',
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
				'selector'    => '{{WRAPPER}} .pp-tiled-post-button',
				'condition'   => array(
					'read_more_button' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-tiled-post-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'read_more_button' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'button_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'scheme'    => Scheme_Typography::TYPOGRAPHY_4,
				'selector'  => '{{WRAPPER}} .pp-tiled-post-button',
				'condition' => array(
					'read_more_button' => 'yes',
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
					'{{WRAPPER}} .pp-tiled-post-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'read_more_button' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'button_box_shadow',
				'selector'  => '{{WRAPPER}} .pp-tiled-post-button',
				'condition' => array(
					'read_more_button' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'read_more_button' => 'yes',
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
					'{{WRAPPER}} .pp-tiled-post:hover .pp-tiled-post-button' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'read_more_button' => 'yes',
				),
			)
		);

		$this->add_control(
			'button_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-tiled-post:hover .pp-tiled-post-button' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'read_more_button' => 'yes',
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
					'{{WRAPPER}} .pp-tiled-post:hover .pp-tiled-post-button' => 'border-color: {{VALUE}}',
				),
				'condition' => array(
					'read_more_button' => 'yes',
				),
			)
		);

		$this->add_control(
			'button_animation',
			array(
				'label'     => __( 'Animation', 'powerpack' ),
				'type'      => Controls_Manager::HOVER_ANIMATION,
				'condition' => array(
					'read_more_button' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'button_box_shadow_hover',
				'selector'  => '{{WRAPPER}} .pp-tiled-post:hover .pp-tiled-post-button',
				'condition' => array(
					'read_more_button' => 'yes',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Render tiled posts widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings();

		$this->add_render_attribute(
			array(
				'tiled-posts'     => array(
					'class' => array(
						'pp-tiled-posts',
						'pp-tiled-posts-' . $settings['layout'],
						'clearfix',
					),
				),
				'post-content'    => array(
					'class' => array(
						'pp-tiled-post-content',
						'pp-tiled-post-content-' . $settings['content_vertical_position'],
					),
				),
				'post-categories' => array(
					'class' => array(
						'pp-post-categories',
						'pp-post-categories-' . $settings['category_style'],
					),
				),
			)
		);
		?>
		<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'tiled-posts' ) ); ?>>
			<?php
			$count = 1;

			$layout = $settings['layout'];

			switch ( $layout ) {
				case 'layout-1':
					$posts_count = 4;
					break;

				case 'layout-2':
				case 'layout-3':
					$posts_count = 3;
					break;

				case 'layout-4':
				case 'layout-5':
				case 'layout-6':
					$posts_count = 5;
					break;

				default:
					$posts_count = 3;
					break;
			}

			if ( 'yes' === $settings['other_posts'] ) {
				if ( ! empty( $settings['other_posts_count'] ) && is_numeric( $settings['other_posts_count'] ) ) {
					$number_of_posts = absint( $settings['other_posts_count'] );
					$posts_count    += $number_of_posts;
				} else {
					$posts_count = '-1';
				}
			}

			/* $args = $this->query_posts_args( '', '', '', '', '', 'tiled_posts', 'yes', '', $posts_count );

			$posts_query = new \WP_Query( $args ); */
			$this->query_posts( '', '', '', '', '', 'tiled_posts', 'yes', '', $posts_count );
			$posts_query = $this->get_query();

			if ( 'yes' === $settings['other_posts'] ) {
				if ( ( ! empty( $settings['other_posts_count'] ) && is_numeric( $settings['other_posts_count'] ) )
					&& ( $posts_count > $posts_query->found_posts ) ) {
						$posts_count = $posts_query->found_posts;
				}
			}

			if ( $posts_query->have_posts() ) :
				while ( $posts_query->have_posts() ) :
					$posts_query->the_post();
					if ( 1 === $count && 'layout-5' !== $layout ) {
						echo '<div class="pp-tiles-posts-left">';
					}

					if ( 3 === $count && 'layout-6' === $layout ) {
						echo '<div class="pp-tiles-posts-center">';
					}

					if (
						( 2 === $count && ( 'layout-1' === $layout || 'layout-2' === $layout || 'layout-3' === $layout || 'layout-4' === $layout ) ) ||
						( 4 === $count && 'layout-6' === $layout ) ) {
						echo '<div class="pp-tiles-posts-right">';
					}

					if ( 'yes' === $settings['other_posts'] && (
						( 5 === $count && 'layout-1' === $layout ) ||
						( 4 === $count && ( 'layout-2' === $layout || 'layout-3' === $layout ) ) ||
						( 6 === $count && ( 'layout-4' === $layout || 'layout-5' === $layout || 'layout-6' === $layout ) )
						) ) {
						echo '<div class="pp-tiled-post-group pp-tiled-post-col-' . esc_attr( $settings['other_posts_columns'] ) . '">';
					}

					$this->render_post_body( $count, $layout );

					if (
						( 1 === $count && ( 'layout-1' === $layout || 'layout-2' === $layout || 'layout-3' === $layout || 'layout-4' === $layout ) ) ||
						( 2 === $count && 'layout-6' === $layout ) ||
						( 3 === $count && 'layout-6' === $layout ) ) {
						echo '</div>';
					}

					if ( 'yes' === $settings['other_posts'] && $count === $posts_count ) {
						echo '</div>';
					}

					if ( 'layout-1' === $layout ) {
						if ( 4 === $count ) {
							echo '</div>';
						}
					} elseif ( 'layout-2' === $layout || 'layout-3' === $layout ) {
						if ( 3 === $count ) {
							echo '</div>';
						}
					} elseif ( 'layout-4' === $layout ) {
						if ( 5 === $count ) {
							echo '</div>';
						}
					} elseif ( 'layout-6' === $layout ) {
						if ( 5 === $count ) {
							echo '</div>';
						}
					}

					$count++;
				endwhile;
			endif;
			wp_reset_postdata();
			?>
		</div>
		<?php
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
	 * @param  mixed  $count   Post count.
	 * @param  string $layout  Posts layout.
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
			if (
				( 1 === $count && ( 'layout-1' === $layout || 'layout-2' === $layout || 'layout-3' === $layout || 'layout-4' === $layout ) ) ||
				( 3 === $count && 'layout-6' === $layout ) ) {
				$thumb_url = Group_Control_Image_Size::get_attachment_image_src( $image_id, 'image_size', $settings );
			} else {
				$thumb_url = Group_Control_Image_Size::get_attachment_image_src( $image_id, 'image_size_small', $settings );
			}
		} else {
			if ( 'placeholder' === $settings['fallback_image'] ) {
				$thumb_url = Utils::get_placeholder_image_src();
			} elseif ( 'custom' === $settings['fallback_image'] && ! empty( $settings['fallback_image_custom']['url'] ) ) {
				$custom_image_id = $settings['fallback_image_custom']['id'];
				if ( 1 === $count && 'layout-5' !== $layout ) {
					$thumb_url = Group_Control_Image_Size::get_attachment_image_src( $custom_image_id, 'image_size', $settings );
				} else {
					$thumb_url = Group_Control_Image_Size::get_attachment_image_src( $custom_image_id, 'image_size_small', $settings );
				}
			} else {
				$thumb_url = '';
			}
		}
		?>
		<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'post-' . $count ) ); ?>>
			<div class="pp-tiled-post-bg pp-media-background" 
			<?php
			if ( $thumb_url ) {
				echo "style='background-image:url(" . esc_url( $thumb_url ) . ")'"; }
			?>
			>
			</div>
			<?php $posts_link = apply_filters( 'ppe_tiled_posts_link', get_the_permalink(), get_the_ID(), $settings ); ?>
			<div class="pp-media-overlay pp-tiled-post-overlay"><a href="<?php echo $posts_link; ?>" title="<?php the_title_attribute(); ?>"></a></div>
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
						<?php if ( 'yes' === $settings['post_date'] ) { ?>
							<?php
								printf(
									'<span class="pp-post-date"><span class="screen-reader-text">%1$s </span>%2$s</span>',
									esc_html__( 'Posted on', 'powerpack' ),
									wp_kses_post( $this->get_post_date() )
								);
							?>
						<?php } ?>
					</div>
				<?php } ?>

				<?php $this->render_post_excerpt( $count, $layout ); ?>

				<?php if ( 'yes' === $settings['read_more_button'] ) { ?>
					<?php
					$this->add_render_attribute(
						'button',
						'class',
						array(
							'pp-tiled-post-button',
							'elementor-button',
							'elementor-size-' . $settings['button_size'],
						)
					);
					?>
					<a <?php echo wp_kses_post( $this->get_render_attribute_string( 'button' ) ); ?> href="<?php esc_url( the_permalink() ); ?>">
						<span class="pp-tiled-post-button-text">
							<?php echo esc_attr( $settings['read_more_button_text'] ); ?>
						</span>
					</a>
				<?php } ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render posts body output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @param  mixed  $count   Post count.
	 * @param  string $layout  Posts layout.
	 *
	 * @access protected
	 */
	protected function render_post_excerpt( $count, $layout ) {
		$settings = $this->get_settings();

		if (
			( 1 === $count && ( 'layout-1' === $layout || 'layout-2' === $layout || 'layout-3' === $layout || 'layout-4' === $layout ) ) ||
			( 3 === $count && 'layout-6' === $layout ) ) {
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
	 * @param  mixed  $count   Post count.
	 * @param  string $layout  Posts layout.
	 *
	 * @access protected
	 */
	protected function get_post_class( $count, $layout ) {
		$settings = $this->get_settings();

		$class = '';

		if (
			( 1 === $count && ( 'layout-1' === $layout || 'layout-2' === $layout || 'layout-3' === $layout || 'layout-4' === $layout ) ) ||
			( 3 === $count && 'layout-6' === $layout ) ) {
			$class = 'pp-tiled-post-featured';
		} elseif (
			( 2 === $count && 'layout-1' === $layout ) ||
			( ( 2 === $count || 3 === $count ) && ( 'layout-2' === $layout || 'layout-3' === $layout ) ) ) {
			$class = 'pp-tiled-post-large';
		} elseif (
			( ( 3 === $count || 4 === $count ) && 'layout-1' === $layout ) ||
			( ( 1 === $count || 2 === $count ) && 'layout-5' === $layout ) ||
			( ( 1 === $count || 2 === $count || 4 === $count || 5 === $count ) && 'layout-6' === $layout ) ) {
			$class = 'pp-tiled-post-medium';
		} elseif ( $count > 1 && $count < 6 && 'layout-4' === $layout ) {
			$class = 'pp-tiled-post-medium';
		} elseif ( ( 3 === $count || 4 === $count || 5 === $count ) && 'layout-5' === $layout ) {
			$class = 'pp-tiled-post-small';
		}

		if ( $this->check_other_posts( $count, $layout ) ) {
			switch ( $settings['other_posts_columns'] ) {
				case '4':
					$class = 'pp-tiled-post-xs';
					break;

				case '3':
					$class = 'pp-tiled-post-small';
					break;

				case '2':
					$class = 'pp-tiled-post-medium';
					break;

				case '1':
					$class = 'pp-tiled-post-large';
					break;
			}
		}

		return $class;
	}

	/**
	 * Check other posts.
	 *
	 * @param  mixed  $count   Post count.
	 * @param  string $layout  Posts layout.
	 *
	 * @access protected
	 */
	protected function check_other_posts( $count, $layout ) {
		$settings = $this->get_settings();

		if ( 'yes' === $settings['other_posts'] && (
			( $count >= 5 && 'layout-1' === $layout ) ||
			( $count >= 4 && ( 'layout-2' === $layout || 'layout-3' === $layout ) ) ||
			( $count >= 6 && ( 'layout-4' === $layout || 'layout-5' === $layout ) ||
			( $count >= 6 && 'layout-6' === $layout ) ) ) ) {
			return true;
		}
	}

	/**
	 * Get post title length.
	 *
	 * @param  string $title Post title.
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
	 * @param  int $limit Excerpt limit.
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
