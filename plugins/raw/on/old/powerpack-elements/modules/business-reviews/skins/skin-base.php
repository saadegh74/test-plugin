<?php
namespace PowerpackElements\Modules\BusinessReviews\Skins;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Classes\PP_Config;
use PowerpackElements\Classes\PP_Helper;

// Elementor Classes
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Icons_Manager;
use Elementor\Skin_Base as Elementor_Skin_Base;
use Elementor\Widget_Base;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Css_Filter;
use Elementor\Core\Schemes\Typography as Scheme_Typography;
use Elementor\Core\Schemes\Color as Scheme_Color;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Skin Base
 */
abstract class Skin_Base extends Elementor_Skin_Base {

	private static $networks_class_dictionary = [
		'star-solid'   => [
			'value' => 'fa fa-star',
		],
		'star-outline' => [
			'value' => 'fa fa-star',
		],
	];

	private static $networks_icon_mapping = [
		'star-solid'   => [
			'value'   => 'fas fa-star',
			'library' => 'fa-solid',
		],
		'star-outline' => [
			'value'   => 'far fa-star',
			'library' => 'fa-regular',
		],
	];

	private static function get_network_icon_data( $network_name ) {
		$prefix = 'fa ';
		$library = '';

		if ( Icons_Manager::is_migration_allowed() ) {
			if ( isset( self::$networks_icon_mapping[ $network_name ] ) ) {
				return self::$networks_icon_mapping[ $network_name ];
			}
			$prefix = 'fab ';
			$library = 'fa-brands';
		}
		if ( isset( self::$networks_class_dictionary[ $network_name ] ) ) {
			return self::$networks_class_dictionary[ $network_name ];
		}

		return [
			'value' => $prefix . 'fa-' . $network_name,
			'library' => $library,
		];
	}

	protected function _register_controls_actions() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore 
		add_action( 'elementor/element/pp-business-reviews/section_filters_controls/after_section_end', array( $this, 'register_controls' ), 20 );

		add_action( 'elementor/element/pp-business-reviews/section_filters_controls/after_section_end', array( $this, 'register_style_controls' ), 20 );
	}

	public function register_controls( Widget_Base $widget ) {
		$this->parent = $widget;

		$this->register_content_review_details_controls();
		$this->register_content_help_docs();
	}

	public function register_style_controls( Widget_Base $widget ) {
		$this->parent = $widget;

		$this->register_style_layout_controls();
		$this->register_style_box_controls();
		$this->register_style_image_controls();
		$this->register_style_name_controls();
		$this->register_style_review_date_controls();
		$this->register_style_rating_controls();
		$this->register_style_review_text_controls();
		$this->register_style_arrows_controls();
		$this->register_style_pagination_controls();
	}

	/**
	 * Content Tab: Review Details controls
	 *
	 * @since 2.8.0
	 * @access protected
	 */
	protected function register_content_review_details_controls() {

		$this->start_controls_section(
			'section_review_details',
			array(
				'label' => __( 'Review Details', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

			$this->add_control(
				'heading_content_reviewer_image',
				array(
					'label'     => __( 'Reviewer Image', 'powerpack' ),
					'type'      => Controls_Manager::HEADING,
				)
			);

			$this->add_control(
				'reviewer_image',
				array(
					'label'        => __( 'Show Image', 'powerpack' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => __( 'Yes', 'powerpack' ),
					'label_off'    => __( 'Now', 'powerpack' ),
					'return_value' => 'yes',
					'default'      => 'yes',
					'prefix_class' => 'pp-review-image-enable-',
					'render_type'  => 'template',
				)
			);

			$this->add_control(
				'image_align',
				array(
					'label'     => __( 'Image Position', 'powerpack' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'left',
					'options'   => array(
						'top'      => __( 'Above Name', 'powerpack' ),
						'left'     => __( 'Left of Name', 'powerpack' ),
						'all_left' => __( 'Left of all content', 'powerpack' ),
					),
					'condition' => array(
						$this->get_control_id( 'reviewer_image' ) => 'yes',
					),
				)
			);

			// This Overall alignment control in case of image top alignment condition.
			$this->add_control(
				'overall_align',
				array(
					'label'        => __( 'Overall Alignment', 'powerpack' ),
					'type'         => Controls_Manager::CHOOSE,
					'options'      => array(
						'left'   => array(
							'title' => __( 'Left', 'powerpack' ),
							'icon'  => 'fa fa-align-left',
						),
						'center' => array(
							'title' => __( 'Center', 'powerpack' ),
							'icon'  => 'fa fa-align-center',
						),
						'right'  => array(
							'title' => __( 'Right', 'powerpack' ),
							'icon'  => 'fa fa-align-right',
						),
					),
					'default'      => 'center',
					'toggle'       => false,
					'condition'    => array(
						$this->get_control_id( 'image_align' )    => 'top',
					),
					'prefix_class' => 'pp-reviews-align-',
				)
			);

			// This Overall alignment control in case of image left and all left alignment condition.
			$this->add_control(
				'overall_alignment_left',
				array(
					'label'        => __( 'Overall Alignment', 'powerpack' ),
					'type'         => Controls_Manager::CHOOSE,
					'options'      => array(
						'left'   => array(
							'title' => __( 'Left', 'powerpack' ),
							'icon'  => 'fa fa-align-left',
						),
						'center' => array(
							'title' => __( 'Center', 'powerpack' ),
							'icon'  => 'fa fa-align-center',
						),
						'right'  => array(
							'title' => __( 'Right', 'powerpack' ),
							'icon'  => 'fa fa-align-right',
						),
					),
					'default'      => 'center',
					'toggle'       => false,
					'condition'    => array(
						$this->get_control_id( 'reviewer_image' ) . '!' => 'yes',
						$this->get_control_id( 'image_align' ) . '!'    => 'top',
					),
					'prefix_class' => 'pp-reviews-align-',
				)
			);

			$this->add_control(
				'heading_content_reviewer_name',
				array(
					'label'     => __( 'Reviewer Name', 'powerpack' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_control(
				'reviewer_name',
				array(
					'label'        => __( 'Show Name', 'powerpack' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => __( 'Yes', 'powerpack' ),
					'label_off'    => __( 'Now', 'powerpack' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_control(
				'reviewer_name_link',
				array(
					'label'        => __( 'Link Name', 'powerpack' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => __( 'Yes', 'powerpack' ),
					'label_off'    => __( 'No', 'powerpack' ),
					'return_value' => 'yes',
					'default'      => 'no',
					'condition'    => array(
						$this->get_control_id( 'reviewer_name' ) => 'yes',
					),
				)
			);

			$this->add_control(
				'heading_content_star_rating',
				array(
					'label'     => __( 'Star Rating', 'powerpack' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_control(
				'star_rating',
				array(
					'label'        => __( 'Show Star Rating', 'powerpack' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_control(
				'heading_content_review_source_icon',
				array(
					'label'     => __( 'Review Source Icon', 'powerpack' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_control(
				'review_source_icon',
				array(
					'label'        => __( 'Show Review Source Icon', 'powerpack' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default'      => 'yes',
					'render_type'  => 'template',
				)
			);

			$this->add_control(
				'heading_content_review_date',
				array(
					'label'     => __( 'Review Date', 'powerpack' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_control(
				'review_date',
				array(
					'label'        => __( 'Show Review Date', 'powerpack' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_control(
				'review_date_type',
				array(
					'label'     => __( 'Select Type', 'powerpack' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'relative',
					'options'   => array(
						'default'  => __( 'Numeric', 'powerpack' ),
						'relative' => __( 'Relative', 'powerpack' ),
					),
					'condition' => array(
						$this->get_control_id( 'review_date' ) => 'yes',
						'reviews_source' => 'google',
					),
				)
			);

			$this->add_control(
				'heading_content_review_text_date',
				array(
					'label'     => __( 'Review Text', 'powerpack' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_control(
				'review_content',
				array(
					'label'        => __( 'Show Review Text', 'powerpack' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_control(
				'review_content_length',
				array(
					'label'     => __( 'Text Length', 'powerpack' ),
					'type'      => Controls_Manager::NUMBER,
					'default'   => 25,
					'condition' => array(
						$this->get_control_id( 'review_content' ) => 'yes',
					),
				)
			);

			$this->add_control(
				'yelp_review_length_doc',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => __( 'Yelp API allows fetching maximum 160 characters from a review.', 'powerpack' ),
					'content_classes' => 'pp-editor-info',
					'condition'       => array(
						$this->get_control_id( 'review_content' ) => 'yes',
						'reviews_source!' => 'google',
					),
				)
			);

			$this->add_control(
				'read_more',
				array(
					'label'     => __( 'Read More Text', 'powerpack' ),
					'default'   => __( 'Read More »', 'powerpack' ),
					'type'      => Controls_Manager::TEXT,
					'dynamic'   => array(
						'active' => true,
					),
					'condition' => array(
						$this->get_control_id( 'review_content' ) => 'yes',
					),
				)
			);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Layout Controls
	 *
	 * @since 2.8.0
	 * @access protected
	 */
	protected function register_style_layout_controls() {

		$this->start_controls_section(
			'section_layout',
			array(
				'label' => __( 'Layout', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_responsive_control(
				'column_gap',
				array(
					'label'     => __( 'Columns Gap', 'powerpack' ),
					'type'      => Controls_Manager::SLIDER,
					'default'   => array(
						'size' => 25,
					),
					'range'     => array(
						'px' => array(
							'min' => 0,
							'max' => 100,
						),
					),
					'selectors' => array(
						'{{WRAPPER}}' => '--grid-column-gap: {{SIZE}}{{UNIT}}',
					),
					'render_type'  => 'template',
				)
			);

			$this->add_responsive_control(
				'row_gap',
				array(
					'label'     => __( 'Rows Gap', 'powerpack' ),
					'type'      => Controls_Manager::SLIDER,
					'default'   => array(
						'size' => 25,
					),
					'range'     => array(
						'px' => array(
							'min' => 0,
							'max' => 100,
						),
					),
					'selectors' => array(
						'{{WRAPPER}}' => '--grid-row-gap: {{SIZE}}{{UNIT}}',
					),
					'condition' => array(
						'layout!' => 'carousel',
					),
				)
			);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Box Style
	 *
	 * @since 2.8.0
	 * @access protected
	 */
	protected function register_style_box_controls() {

		$this->start_controls_section(
			'section_styling',
			array(
				'label' => __( 'Box', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_control(
				'box_bg_color',
				array(
					'label'     => __( 'Background Color', 'powerpack' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .pp-review' => 'background-color: {{VALUE}};',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'           => 'box_border',
					'label'          => __( 'Border', 'powerpack' ),
					'fields_options' => array(
						'border' => array(
							'default' => 'solid',
						),
						'width'  => array(
							'default' => array(
								'top'    => '1',
								'right'  => '1',
								'bottom' => '1',
								'left'   => '1',
							),
						),
						'color'  => array(
							'default' => '#e1e8ed',
						),
					),
					'selector'       => '{{WRAPPER}} .pp-review',
				)
			);

			$this->add_control(
				'box_border_radius',
				array(
					'label'      => __( 'Border Radius', 'powerpack' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'default'    => array(
						'top'    => '5',
						'bottom' => '5',
						'right'  => '5',
						'left'   => '5',
						'unit'   => 'px',
					),
					'selectors'  => array(
						'{{WRAPPER}} .pp-review' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);
	
			$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				array(
					'name'     => 'box_box_shadow',
					'selector' => '{{WRAPPER}} .pp-review',
				)
			);

			$this->add_control(
				'box_padding',
				array(
					'label'      => __( 'Padding', 'powerpack' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', 'em', '%' ),
					'default'    => array(
						'top'    => '20',
						'bottom' => '20',
						'right'  => '20',
						'left'   => '20',
						'unit'   => 'px',
					),
					'selectors'  => array(
						'{{WRAPPER}} .pp-review' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$this->add_control(
				'show_separator',
				[
					'label'        => esc_html__( 'Separator', 'powerpack' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_off'    => esc_html__( 'Hide', 'powerpack' ),
					'label_on'     => esc_html__( 'Show', 'powerpack' ),
					'default'      => 'has-separator',
					'return_value' => 'has-separator',
					'prefix_class' => 'pp-review--',
					'separator'    => 'before',
				]
			);
	
			$this->add_control(
				'separator_color',
				[
					'label'     => esc_html__( 'Color', 'powerpack' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .pp-review-header' => 'border-bottom-color: {{VALUE}}',
					],
					'condition' => [
						$this->get_control_id( 'show_separator!' ) => '',
					],
				]
			);
	
			$this->add_control(
				'separator_size',
				[
					'label'     => esc_html__( 'Size', 'powerpack' ),
					'type'      => Controls_Manager::SLIDER,
					'range'     => [
						'px' => [
							'min' => 0,
							'max' => 20,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .pp-review-header' => 'border-bottom-width: {{SIZE}}{{UNIT}}',
					],
					'condition' => [
						$this->get_control_id( 'show_separator!' ) => '',
					],
				]
			);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Image Controls
	 *
	 * @since 2.8.0
	 * @access protected
	 */
	protected function register_style_image_controls() {

		$this->start_controls_section(
			'section_image_style',
			array(
				'label'     => __( 'Image', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					$this->get_control_id( 'reviewer_image' ) => 'yes',
				),
			)
		);

			$this->add_responsive_control(
				'image_size',
				array(
					'label'     => __( 'Image Size', 'powerpack' ),
					'type'      => Controls_Manager::SLIDER,
					'default'   => array(
						'size' => 60,
					),
					'range'     => array(
						'px' => array(
							'min' => 0,
							'max' => 130,
						),
					),
					'selectors' => array(
						'{{WRAPPER}} .pp-review-image' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					),
					'condition' => array(
						$this->get_control_id( 'reviewer_image' ) => 'yes',
					),
				)
			);

			$this->add_responsive_control(
				'image_gap',
				[
					'label'     => __( 'Gap', 'powerpack' ),
					'type'      => Controls_Manager::SLIDER,
					'range'     => [
						'px' => [
							'max' => 100,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .pp-review-image' => 'margin-bottom: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .pp-review-image-left .pp-review-image, {{WRAPPER}} .pp-review-image-all_left .pp-review-image' => 'margin-right: {{SIZE}}{{UNIT}}; margin-bottom: 0px;',
					],
					'condition' => [
						$this->get_control_id( 'reviewer_image' ) => 'yes',
					],
				]
			);

			$this->add_group_control(
				Group_Control_Border::get_type(),
				[
					'name'        => 'image_border',
					'label'       => __( 'Border', 'powerpack' ),
					'placeholder' => '',
					'default'     => '',
					'selector'    => '{{WRAPPER}} .pp-review-image',
					'condition' => [
						$this->get_control_id( 'reviewer_image' ) => 'yes',
					],
				]
			);

			$this->add_control(
				'image_border_radius',
				[
					'label'      => __( 'Border Radius', 'powerpack' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'selectors'  => [
						'{{WRAPPER}} .pp-review-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'condition'  => [
						$this->get_control_id( 'reviewer_image' ) => 'yes',
					],
				]
			);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Name Controls
	 *
	 * @since 2.8.0
	 * @access protected
	 */
	protected function register_style_name_controls() {

		$this->start_controls_section(
			'section_names_style',
			array(
				'label'     => __( 'Name', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					$this->get_control_id( 'reviewer_name' ) => 'yes',
				),
			)
		);

			$this->add_control(
				'name_text_color',
				array(
					'label'     => __( 'Color', 'powerpack' ),
					'type'      => Controls_Manager::COLOR,
					'scheme'    => array(
						'type'  => Scheme_Color::get_type(),
						'value' => Scheme_Color::COLOR_1,
					),
					'selectors' => array(
						'{{WRAPPER}} .pp-reviewer-name a, {{WRAPPER}} .pp-reviewer-name' => 'color: {{VALUE}}',
					),
					'condition' => array(
						$this->get_control_id( 'reviewer_name' ) => 'yes',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'name_typography',
					'label'    => __( 'Typography', 'powerpack' ),
					'scheme'   => Scheme_Typography::TYPOGRAPHY_1,
					'selector' => '{{WRAPPER}} .pp-reviewer-name a, {{WRAPPER}} .pp-reviewer-name',
					'condition' => array(
						$this->get_control_id( 'reviewer_name' ) => 'yes',
					),
				)
			);

			$this->add_responsive_control(
				'name_gap',
				array(
					'label'     => __( 'Bottom Spacing', 'powerpack' ),
					'type'      => Controls_Manager::SLIDER,
					'range'     => array(
						'px' => array(
							'min' => 0,
							'max' => 50,
						),
					),
					'selectors' => array(
						'{{WRAPPER}} .pp-reviewer-name' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					),
					'condition' => array(
						$this->get_control_id( 'reviewer_name' ) => 'yes',
					),
				)
			);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Review Date Controls
	 *
	 * @since 2.8.0
	 * @access protected
	 */
	protected function register_style_review_date_controls() {

		$this->start_controls_section(
			'section_review_date_style',
			array(
				'label'     => __( 'Review Date', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					$this->get_control_id( 'review_date' ) => 'yes',
				),
			)
		);

			$this->add_control(
				'review_date_color',
				array(
					'label'     => __( 'Color', 'powerpack' ),
					'type'      => Controls_Manager::COLOR,
					'scheme'    => array(
						'type'  => Scheme_Color::get_type(),
						'value' => Scheme_Color::COLOR_2,
					),
					'default'   => '#afafaf',
					'selectors' => array(
						'{{WRAPPER}} .pp-review-time' => 'color: {{VALUE}}',
					),
					'condition' => array(
						$this->get_control_id( 'review_date' ) => 'yes',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'review_date_typography',
					'label'    => __( 'Typography', 'powerpack' ),
					'scheme'   => Scheme_Typography::TYPOGRAPHY_3,
					'selector' => '{{WRAPPER}} .pp-review-time',
				)
			);

			$this->add_responsive_control(
				'review_date_spacing',
				array(
					'label'     => __( 'Bottom Spacing', 'powerpack' ),
					'type'      => Controls_Manager::SLIDER,
					'range'     => array(
						'px' => array(
							'min' => 0,
							'max' => 50,
						),
					),
					'selectors' => array(
						'{{WRAPPER}} .pp-review-time' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					),
					'condition' => array(
						$this->get_control_id( 'review_date' ) => 'yes',
					),
				)
			);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Rating controls
	 *
	 * @since 2.8.0
	 * @access protected
	 */
	protected function register_style_rating_controls() {
		$this->start_controls_section(
			'section_rating_style',
			[
				'label'     => __( 'Rating', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					$this->get_control_id( 'star_rating' ) => 'yes',
				),
			]
		);

			$this->add_control(
				'star_style',
				array(
					'label'     => __( 'Star Icon Style', 'powerpack' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'custom',
					'options'   => array(
						'default' => __( 'Default', 'powerpack' ),
						'custom'  => __( 'Custom', 'powerpack' ),
					),
					'condition' => array(
						$this->get_control_id( 'star_rating' ) => 'yes',
					),
				)
			);

			$this->add_control(
				'unmarked_star_style',
				[
					'label'       => __( 'Unmarked Style', 'powerpack' ),
					'type'        => Controls_Manager::CHOOSE,
					'label_block' => false,
					'options'     => [
						'solid' => [
							'title' => __( 'Solid', 'powerpack' ),
							'icon'  => 'eicon-star',
						],
						'outline' => [
							'title' => __( 'Outline', 'powerpack' ),
							'icon'  => 'eicon-star-o',
						],
					],
					'default'     => 'solid',
					'condition'   => array(
						$this->get_control_id( 'star_rating' ) => 'yes',
						$this->get_control_id( 'star_style' )  => 'custom',
					),
				]
			);

			$this->add_responsive_control(
				'star_spacing',
				array(
					'label'     => __( 'Bottom Spacing', 'powerpack' ),
					'type'      => Controls_Manager::SLIDER,
					'range'     => array(
						'px' => array(
							'min' => 0,
							'max' => 50,
						),
					),
					'selectors' => array(
						'{{WRAPPER}} .elementor-star-rating__wrapper' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					),
					'condition' => array(
						$this->get_control_id( 'star_rating' ) => 'yes',
					),
				)
			);

			$this->add_control(
				'star_size',
				[
					'label'     => __( 'Size', 'powerpack' ),
					'type'      => Controls_Manager::SLIDER,
					'range'     => [
						'px' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .pp-star-rating .pp-star-full, {{WRAPPER}} .pp-star-rating .pp-star-empty' => 'font-size: {{SIZE}}{{UNIT}}',
					],
					'separator' => 'before',
					'condition' => array(
						$this->get_control_id( 'star_rating' ) => 'yes',
						$this->get_control_id( 'star_style' )  => 'custom',
					),
				]
			);

			$this->add_control(
				'star_space',
				[
					'label'     => __( 'Spacing', 'powerpack' ),
					'type'      => Controls_Manager::SLIDER,
					'range'     => [
						'px' => [
							'min' => 0,
							'max' => 50,
						],
					],
					'selectors' => [
						'body:not(.rtl) {{WRAPPER}} .pp-star-rating i:not(:last-of-type), body:not(.rtl) {{WRAPPER}} .pp-star-rating svg:not(:last-of-type)' => 'margin-right: {{SIZE}}{{UNIT}}',
						'body.rtl {{WRAPPER}} .pp-star-rating i:not(:last-of-type), body.rtl {{WRAPPER}} .pp-star-rating svg:not(:last-of-type)' => 'margin-left: {{SIZE}}{{UNIT}}',
					],
					'condition' => array(
						$this->get_control_id( 'star_rating' ) => 'yes',
						$this->get_control_id( 'star_style' )  => 'custom',
					),
				]
			);

			$this->add_control(
				'stars_color',
				[
					'label'     => __( 'Color', 'powerpack' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .pp-star-rating .pp-star-full' => 'color: {{VALUE}}; fill: {{VALUE}}',
					],
					'separator' => 'before',
					'condition' => array(
						$this->get_control_id( 'star_rating' ) => 'yes',
						$this->get_control_id( 'star_style' )  => 'custom',
					),
				]
			);

			$this->add_control(
				'stars_unmarked_color',
				[
					'label'     => __( 'Unmarked Color', 'powerpack' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .pp-star-rating .pp-star-empty' => 'color: {{VALUE}}; fill: {{VALUE}}',
					],
					'condition' => array(
						$this->get_control_id( 'star_rating' ) => 'yes',
						$this->get_control_id( 'star_style' )  => 'custom',
					),
				]
			);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Review Text Controls
	 *
	 * @since 2.8.0
	 * @access protected
	 */
	protected function register_style_review_text_controls() {

		$this->start_controls_section(
			'section_review_text_style',
			array(
				'label'     => __( 'Review Text', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					$this->get_control_id( 'review_content' ) => 'yes',
				),
			)
		);

			$this->add_control(
				'heading_style_review_text',
				array(
					'label'     => __( 'Review Text', 'powerpack' ),
					'type'      => Controls_Manager::HEADING,
					'condition' => array(
						$this->get_control_id( 'review_content' ) => 'yes',
					),
				)
			);

			$this->add_control(
				'review_text_color',
				array(
					'label'     => __( 'Text Color', 'powerpack' ),
					'type'      => Controls_Manager::COLOR,
					'scheme'    => array(
						'type'  => Scheme_Color::get_type(),
						'value' => Scheme_Color::COLOR_3,
					),
					'selectors' => array(
						'{{WRAPPER}} .pp-review-content' => 'color: {{VALUE}}',
					),
					'condition' => array(
						$this->get_control_id( 'review_content' ) => 'yes',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'      => 'content_typography',
					'label'     => __( 'Typography', 'powerpack' ),
					'scheme'    => Scheme_Typography::TYPOGRAPHY_3,
					'selector'  => '{{WRAPPER}} .pp-review-content',
					'condition' => array(
						$this->get_control_id( 'review_content' ) => 'yes',
					),
				)
			);

			$this->add_control(
				'heading_style_readmore_text',
				array(
					'label'     => __( 'Read More Text', 'powerpack' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array(
						$this->get_control_id( 'review_content' ) => 'yes',
						$this->get_control_id( 'read_more' ) . '!' => '',
					),
				)
			);

			$this->add_control(
				'reviewer_readmore_color',
				array(
					'label'     => __( 'Text Color', 'powerpack' ),
					'type'      => Controls_Manager::COLOR,
					'scheme'    => array(
						'type'  => Scheme_Color::get_type(),
						'value' => Scheme_Color::COLOR_4,
					),
					'selectors' => array(
						'{{WRAPPER}} a.pp-reviews-read-more' => 'color: {{VALUE}};',
					),
					'condition' => array(
						$this->get_control_id( 'review_content' ) => 'yes',
						$this->get_control_id( 'read_more' ) . '!' => '',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'      => 'readmore_typography',
					'label'     => __( 'Typography', 'powerpack' ),
					'scheme'    => Scheme_Typography::TYPOGRAPHY_4,
					'selector'  => '{{WRAPPER}} .pp-reviews-read-more',
					'condition' => array(
						$this->get_control_id( 'review_content' ) => 'yes',
						$this->get_control_id( 'read_more' ) . '!' => '',
					),
				)
			);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Arrows Controls
	 *
	 * @since 2.8.0
	 * @access protected
	 */
	protected function register_style_arrows_controls() {

		$this->start_controls_section(
			'section_arrows_style',
			array(
				'label'     => __( 'Arrows', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'layout' => 'carousel',
					'arrows' => 'yes',
				),
			)
		);

			$this->add_control(
				'arrow',
				array(
					'label'                  => __( 'Choose Arrow', 'powerpack' ),
					'type'                   => Controls_Manager::ICONS,
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
					'condition'              => array(
						'layout' => 'carousel',
						'arrows' => 'yes',
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
						'{{WRAPPER}} .swiper-container-wrap .pp-slider-arrow' => 'font-size: {{SIZE}}{{UNIT}};',
					),
					'condition' => array(
						'layout' => 'carousel',
						'arrows' => 'yes',
					),
				)
			);

			$this->add_responsive_control(
				'left_arrow_position',
				array(
					'label'      => __( 'Align Left Arrow', 'powerpack' ),
					'type'       => Controls_Manager::SLIDER,
					'range'      => array(
						'px' => array(
							'min'  => -100,
							'max'  => 40,
							'step' => 1,
						),
					),
					'size_units' => array( 'px' ),
					'selectors'  => array(
						'{{WRAPPER}} .swiper-container-wrap .elementor-swiper-button-prev' => 'left: {{SIZE}}{{UNIT}};',
					),
					'condition'  => array(
						'layout' => 'carousel',
						'arrows' => 'yes',
					),
				)
			);

			$this->add_responsive_control(
				'right_arrow_position',
				array(
					'label'      => __( 'Align Right Arrow', 'powerpack' ),
					'type'       => Controls_Manager::SLIDER,
					'range'      => array(
						'px' => array(
							'min'  => -100,
							'max'  => 40,
							'step' => 1,
						),
					),
					'size_units' => array( 'px' ),
					'selectors'  => array(
						'{{WRAPPER}} .swiper-container-wrap .elementor-swiper-button-next' => 'right: {{SIZE}}{{UNIT}};',
					),
					'condition'  => array(
						'layout' => 'carousel',
						'arrows' => 'yes',
					),
				)
			);

			$this->start_controls_tabs( 'tabs_arrows_style' );

				$this->start_controls_tab(
					'tab_arrows_normal',
					array(
						'label'      => __( 'Normal', 'powerpack' ),
						'condition'  => array(
							'layout' => 'carousel',
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
								'{{WRAPPER}} .swiper-container-wrap .pp-slider-arrow' => 'background-color: {{VALUE}};',
							),
							'condition' => array(
								'layout' => 'carousel',
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
								'{{WRAPPER}} .swiper-container-wrap .pp-slider-arrow' => 'color: {{VALUE}};',
							),
							'condition' => array(
								'layout' => 'carousel',
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
							'selector'    => '{{WRAPPER}} .swiper-container-wrap .pp-slider-arrow',
							'separator'   => 'before',
							'condition' => array(
								'layout' => 'carousel',
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
								'{{WRAPPER}} .swiper-container-wrap .pp-slider-arrow' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							),
							'condition' => array(
								'layout' => 'carousel',
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
							'layout' => 'carousel',
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
								'{{WRAPPER}} .swiper-container-wrap .pp-slider-arrow:hover' => 'background-color: {{VALUE}};',
							),
							'condition' => array(
								'layout' => 'carousel',
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
								'{{WRAPPER}} .swiper-container-wrap .pp-slider-arrow:hover' => 'color: {{VALUE}};',
							),
							'condition' => array(
								'layout' => 'carousel',
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
								'{{WRAPPER}} .swiper-container-wrap .pp-slider-arrow:hover' => 'border-color: {{VALUE}};',
							),
							'condition' => array(
								'layout' => 'carousel',
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
						'{{WRAPPER}} .swiper-container-wrap .pp-slider-arrow' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'separator'  => 'before',
					'condition' => array(
						'layout' => 'carousel',
						'arrows' => 'yes',
					),
				)
			);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Pagination Controls
	 *
	 * @since 2.8.0
	 * @access protected
	 */
	protected function register_style_pagination_controls() {
		$this->start_controls_section(
			'section_dots_style',
			array(
				'label'     => __( 'Pagination: Dots', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'layout'          => 'carousel',
					'pagination'      => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

			$this->add_control(
				'dots_position',
				array(
					'label'     => __( 'Position', 'powerpack' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => array(
						'inside'  => __( 'Inside', 'powerpack' ),
						'outside' => __( 'Outside', 'powerpack' ),
					),
					'default'   => 'outside',
					'condition' => array(
						'layout'          => 'carousel',
						'pagination'      => 'yes',
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
						'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}',
					),
					'condition'  => array(
						'layout'          => 'carousel',
						'pagination'      => 'yes',
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
						'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: {{SIZE}}{{UNIT}}',
					),
					'condition'  => array(
						'layout'          => 'carousel',
						'pagination'      => 'yes',
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
							'layout'          => 'carousel',
							'pagination'      => 'yes',
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
								'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet' => 'background: {{VALUE}};',
							),
							'condition' => array(
								'layout'          => 'carousel',
								'pagination'      => 'yes',
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
								'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet-active' => 'background: {{VALUE}};',
							),
							'condition' => array(
								'layout'          => 'carousel',
								'pagination'      => 'yes',
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
							'selector'    => '{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet',
							'condition'   => array(
								'layout'          => 'carousel',
								'pagination'      => 'yes',
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
								'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							),
							'condition'  => array(
								'layout'          => 'carousel',
								'pagination'      => 'yes',
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
								'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullets' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							),
							'condition'          => array(
								'layout'          => 'carousel',
								'pagination'      => 'yes',
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
							'layout'          => 'carousel',
							'pagination'      => 'yes',
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
								'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet:hover' => 'background: {{VALUE}};',
							),
							'condition' => array(
								'layout'          => 'carousel',
								'pagination'      => 'yes',
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
								'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet:hover' => 'border-color: {{VALUE}};',
							),
							'condition' => array(
								'layout'          => 'carousel',
								'pagination'      => 'yes',
								'pagination_type' => 'bullets',
							),
						)
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();

		/**
		 * Style Tab: Pagination: Fraction
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_fraction_style',
			array(
				'label'     => __( 'Pagination: Fraction', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'layout'          => 'carousel',
					'pagination'      => 'yes',
					'pagination_type' => 'fraction',
				),
			)
		);

			$this->add_control(
				'fraction_text_color',
				array(
					'label'     => __( 'Text Color', 'powerpack' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .swiper-pagination-fraction' => 'color: {{VALUE}};',
					),
					'condition' => array(
						'layout'          => 'carousel',
						'pagination'      => 'yes',
						'pagination_type' => 'fraction',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'      => 'fraction_typography',
					'label'     => __( 'Typography', 'powerpack' ),
					'scheme'    => Scheme_Typography::TYPOGRAPHY_4,
					'selector'  => '{{WRAPPER}} .swiper-pagination-fraction',
					'condition' => array(
						'layout'          => 'carousel',
						'pagination'      => 'yes',
						'pagination_type' => 'fraction',
					),
				)
			);

		$this->end_controls_section();
	}

	/**
	 * Content Tab: Help Docs
	 *
	 * @since 2.8.0
	 * @access protected
	 */
	protected function register_content_help_docs() {

		$help_docs = PP_Config::get_widget_help_links( 'Business_Reviews' );

		if ( ! empty( $help_docs ) ) {

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
	 * Get Wrapper Classes.
	 *
	 * @since 2.8.0
	 * @access public
	 */
	public function get_slider_settings() {
		$settings = $this->parent->get_settings_for_display();

		if ( 'carousel' !== $settings['layout'] ) {
			return;
		}

		$column_gap = $this->get_instance_value( 'column_gap' );
		$column_gap_tablet = $this->get_instance_value( 'column_gap_tablet' );
		$column_gap_mobile = $this->get_instance_value( 'column_gap_mobile' );

		$slider_options = [
			'direction'     => 'horizontal',
			'speed'         => ( '' !== $settings['slider_speed'] ) ? $settings['slider_speed'] : 500,
			'slidesPerView' => ( '' !== $settings['columns'] ) ? absint( $settings['columns'] ) : 3,
			'spaceBetween'  => ( '' !== $column_gap['size'] ) ? absint( $column_gap['size'] ) : 10,
			'autoHeight'    => true,
			'loop'          => ( 'yes' === $settings['infinite_loop'] ),
		];

		$autoplay_speed = 999999;

		if ( 'yes' === $settings['autoplay'] && '' !== $settings['autoplay_speed'] ) {
			$autoplay_speed = $settings['autoplay_speed'];
		}

		$slider_options['autoplay'] = [
			'delay'                  => $autoplay_speed,
			'disableOnInteraction'   => ( 'yes' === $settings['pause_on_interaction'] ),
		];

		if ( 'yes' === $settings['pagination'] ) {
			$slider_options['pagination'] = [
				'el'                 => '.swiper-pagination-' . esc_attr( $this->parent->get_id() ),
				'type'               => $settings['pagination_type'],
				'clickable'          => true,
			];
		}

		if ( 'yes' === $settings['arrows'] ) {
			$slider_options['navigation'] = [
				'nextEl'             => '.swiper-button-next-' . esc_attr( $this->parent->get_id() ),
				'prevEl'             => '.swiper-button-prev-' . esc_attr( $this->parent->get_id() ),
			];
		}

		$elementor_bp_lg = get_option( 'elementor_viewport_lg' );
		$elementor_bp_md = get_option( 'elementor_viewport_md' );
		$bp_desktop      = ! empty( $elementor_bp_lg ) ? $elementor_bp_lg : 1025;
		$bp_tablet       = ! empty( $elementor_bp_md ) ? $elementor_bp_md : 768;
		$bp_mobile       = 320;

		$items        = ( isset( $settings['columns'] ) && '' !== $settings['columns'] ) ? absint( $settings['columns'] ) : 3;
		$items_tablet = ( isset( $settings['columns_tablet'] ) && '' !== $settings['columns_tablet'] ) ? absint( $settings['columns_tablet'] ) : 2;
		$items_mobile = ( isset( $settings['columns_mobile'] ) && '' !== $settings['columns_mobile'] ) ? absint( $settings['columns_mobile'] ) : 1;

		$margin        = ( '' !== $column_gap['size'] ) ? absint( $column_gap['size'] ) : 25;
		$margin_tablet = ( isset( $column_gap_tablet['size'] ) && '' !== $column_gap_tablet['size'] ) ? absint( $column_gap_tablet['size'] ) : 20;
		$margin_mobile = ( isset( $column_gap_mobile['size'] ) && '' !== $column_gap_mobile['size'] ) ? absint( $column_gap_mobile['size'] ) : 10;

		$slider_options['breakpoints'] = [
			$bp_desktop => [
				'slidesPerView' => $items,
				'spaceBetween'  => $margin,
			],
			$bp_tablet  => [
				'slidesPerView' => $items_tablet,
				'spaceBetween'  => $margin_tablet,
			],
			$bp_mobile  => [
				'slidesPerView' => $items_mobile,
				'spaceBetween'  => $margin_mobile,
			],
		];

		$slider_options = apply_filters( 'pp_reviews_carousel_options', $slider_options );

		return $slider_options;
	}

	/**
	 * Render team member carousel dots output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_dots() {
		$settings = $this->parent->get_settings_for_display();

		if ( 'yes' === $settings['pagination'] ) {
			?>
			<div class="swiper-pagination swiper-pagination-<?php echo esc_attr( $this->parent->get_id() ); ?>"></div>
			<?php
		}
	}

	/**
	 * Render team member carousel arrows output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_arrows() {
		$settings = $this->parent->get_settings_for_display();

		if ( 'yes' === $settings['arrows'] ) {
			$arrow = $this->get_instance_value( 'arrow' );

			$next_arrow = $arrow;
			$prev_arrow = str_replace( 'right', 'left', $arrow );

			if ( ! empty( $arrow['value'] ) ) { ?>
				<div class="pp-slider-arrow elementor-swiper-button-prev swiper-button-prev-<?php echo esc_attr( $this->parent->get_id() ); ?>">
					<?php
						Icons_Manager::render_icon( $prev_arrow, [ 'aria-hidden' => 'true' ] );
					?>
				</div>
				<div class="pp-slider-arrow elementor-swiper-button-next swiper-button-next-<?php echo esc_attr( $this->parent->get_id() ); ?>">
					<?php
						Icons_Manager::render_icon( $next_arrow, [ 'aria-hidden' => 'true' ] );
					?>
				</div>
			<?php }
		}
	}

	private static function render_star_icon( $network_name, $icon_class = '' ) {
		$network_icon_data = self::get_network_icon_data( $network_name );

		if ( \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_font_icon_svg' ) ) {
			$icon = Icons_Manager::render_font_icon( $network_icon_data, [ 'class' => 'pp-rating-star ' . $icon_class ] );
		} else {
			$icon = sprintf( '<i class="pp-rating-star %s %s" aria-hidden="true"></i>', $icon_class, $network_icon_data['value'] );
		}

		return $icon;
	}

	/**
	 * Gets the layout of five star.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param array $total_rating total_rating.
	 * @param array $review data of single review.
	 * @param array $settings The settings array.
	 * @return the layout of Google reviews star rating.
	 */
	public function render_stars( $total_rating, $review, $settings ) {
		$rating     = $total_rating;
		$stars_html = '';
		$flag       = 0;

		if ( 'default' === $this->get_instance_value( 'star_style' ) ) {

			if ( 'google' === $review['source'] ) {
				$marked_icon_html   = self::render_star_icon('star-solid', 'pp-star-full pp-star-default');
				$unmarked_icon_html = self::render_star_icon('star-solid', 'pp-star-empty pp-star-default');
				$flag               = 1;
			} else {
				$stars_html = '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="100px" height="18px" viewBox="-1 0.054 32 5.642" enable-background="new -1 0.054 32 5.642" xml:space="preserve" class="pp-yelp-rating-svg-' . $rating . '">
<g>
	<path fill="#CECECE" d="M4.075,0.055h-4.511C-0.744,0.055-1,0.307-1,0.626v4.497c0,0.314,0.256,0.572,0.564,0.572h4.511
		c0.308,0,0.557-0.258,0.557-0.572V0.626C4.632,0.307,4.383,0.055,4.075,0.055z M3.973,2.486L2.889,3.434l0.322,1.396
		C3.241,4.927,3.13,5.004,3.05,4.945L1.82,4.214L0.59,4.945C0.501,5,0.399,4.926,0.42,4.829l0.33-1.396l-1.086-0.947
		c-0.08-0.061-0.041-0.187,0.062-0.19l1.432-0.123l0.56-1.327c0.03-0.088,0.161-0.088,0.205,0L2.48,2.173l1.433,0.123
		C4.003,2.302,4.046,2.428,3.973,2.486z" class="pp-yelp-rating-1"/>
	<path fill="#CECECE" d="M10.663,0.055H6.159c-0.311,0-0.571,0.252-0.571,0.571v4.497c0,0.314,0.26,0.572,0.571,0.572h4.504
		c0.315,0,0.564-0.258,0.564-0.572V0.626C11.227,0.307,10.978,0.055,10.663,0.055z M10.567,2.486L9.483,3.434l0.322,1.396
		C9.83,4.927,9.717,5.004,9.64,4.945L8.414,4.214l-1.23,0.731C7.096,5,6.994,4.925,7.008,4.829l0.329-1.396L6.25,2.486
		C6.172,2.426,6.216,2.3,6.319,2.296l1.425-0.123l0.565-1.327c0.032-0.088,0.164-0.088,0.208,0l0.557,1.327L10.5,2.296
		C10.597,2.302,10.641,2.428,10.567,2.486z" class="pp-yelp-rating-2"/>
	<path fill="#CECECE" d="M17.246,0.055h-4.497c-0.318,0-0.571,0.252-0.571,0.571v4.497c0,0.314,0.253,0.572,0.571,0.572h4.497
		c0.32,0,0.572-0.258,0.572-0.572V0.626C17.818,0.307,17.566,0.055,17.246,0.055z M17.158,2.486l-1.084,0.947l0.322,1.396
		c0.018,0.098-0.088,0.175-0.172,0.116l-1.228-0.73l-1.225,0.732c-0.086,0.054-0.191-0.021-0.174-0.117l0.322-1.396l-1.084-0.944
		c-0.073-0.062-0.029-0.188,0.073-0.191l1.421-0.123l0.562-1.325c0.039-0.09,0.172-0.09,0.211,0l0.561,1.325l1.422,0.123
		C17.188,2.302,17.232,2.428,17.158,2.486z" class="pp-yelp-rating-3"/>
	<path fill="#CECECE" d="M23.838,0.055h-4.503c-0.315,0-0.565,0.252-0.565,0.571v4.497c0,0.314,0.25,0.572,0.565,0.572h4.503
		c0.314,0,0.572-0.258,0.572-0.572V0.626C24.41,0.307,24.152,0.055,23.838,0.055z M23.742,2.486l-1.083,0.947l0.323,1.396
		c0.026,0.098-0.082,0.175-0.17,0.116l-1.229-0.731l-1.226,0.731C20.279,5,20.168,4.925,20.191,4.829l0.322-1.396L19.43,2.486
		C19.355,2.426,19.4,2.3,19.496,2.296l1.426-0.123l0.563-1.327c0.037-0.088,0.17-0.088,0.205,0l0.559,1.327l1.438,0.123
		C23.773,2.302,23.824,2.428,23.742,2.486z" class="pp-yelp-rating-4"/>
	<path fill="#CECECE" d="M30.43,0.055h-4.505c-0.3,0-0.563,0.252-0.563,0.571v4.497c0,0.314,0.266,0.572,0.563,0.572h4.505
		c0.321,0,0.57-0.258,0.57-0.572V0.626C31,0.307,30.751,0.055,30.43,0.055z M30.34,2.486l-1.083,0.947l0.323,1.396
		c0.027,0.098-0.09,0.175-0.176,0.116l-1.229-0.731l-1.229,0.731C26.868,5,26.764,4.925,26.791,4.829l0.326-1.396l-1.086-0.945
		c-0.088-0.062-0.035-0.188,0.059-0.191l1.438-0.123l0.557-1.326c0.031-0.089,0.169-0.089,0.207,0l0.557,1.326l1.436,0.123
		C30.371,2.302,30.416,2.428,30.34,2.486z" class="pp-yelp-rating-5"/>
</g>
</svg>';
			}
		} else {
			$unmarked_star_style = $this->get_instance_value( 'unmarked_star_style' );

			if ( 'outline' === $unmarked_star_style ) {
				$star_icon = 'star-outline';
			} else {
				$star_icon = 'star-solid';
			}

			$marked_icon_html   = self::render_star_icon( 'star-solid', 'pp-star-full pp-star-custom' );
			$unmarked_icon_html = self::render_star_icon( $star_icon, 'pp-star-empty pp-star-custom' );
			$flag               = 1;
		}

		if ( $flag ) {
			for ( $stars = 1; $stars <= 5; $stars++ ) {
				if ( $stars <= $rating ) {
					$stars_html .= $marked_icon_html;
				} else {
					$stars_html .= $unmarked_icon_html;
				}
			}
		}

		return $stars_html;
	}

	/**
	 * Get API data.
	 * 
	 * Handles review source remote API calls.
	 *
	 * @since 2.8.0
	 * @param string 	$source		Review source.
	 *
	 * @return array	$response	API response.
	 */
	public function get_api_data( $source ) {
		$settings = $this->parent->get_settings_for_display();

		$api_args = array(
			'method'      => 'POST',
			'timeout'     => 60,
			'httpversion' => '1.0',
			'sslverify'   => false,
		);

		if ( 'google' === $source ) {
			$api_key = $this->parent->get_google_places_api();
			$place_id = $settings['google_place_id'];

			if ( empty( $api_key ) ) {
				return new \WP_Error( 'missing_api_key', __( 'To display Google Reviews, you need to setup API key.', 'powerpack' ) );
			}
			if ( empty( $place_id ) ) {
				return new \WP_Error( 'missing_place_id', __( 'To display Google Reviews, you need to provide valid Place ID.', 'powerpack' ) );
			}

			$url = add_query_arg(
				array(
					'key'      => $api_key,
					'placeid'  => $settings['google_place_id'],
					'language' => ( $settings['language_id'] ) ? $settings['language_id'] : get_locale(),
				),
				'https://maps.googleapis.com/maps/api/place/details/json'
			);
		}

		if ( 'yelp' === $source ) {
			$business_id = $settings['yelp_business_id'];

			if ( empty( $business_id ) ) {
				return new \WP_Error( 'missing_business_id', __( 'To display Yelp Reviews, you need to provide valid Business ID.', 'powerpack' ) );
			}

			$url = 'https://api.yelp.com/v3/businesses/' . $business_id . '/reviews';

			$yelp_api_key = $this->parent->get_yelp_api();
			
			$api_args['method'] = 'GET';
			$api_args['user-agent'] = '';
			$api_args['headers'] = array(
				'Authorization' => 'Bearer ' . $yelp_api_key,
			);
		}

		$response = wp_remote_post(
			esc_url_raw( $url ),
			$api_args
		);

		if ( ! is_wp_error( $response ) ) {
			$body = json_decode( wp_remote_retrieve_body( $response ) );
			if ( isset( $body->error_message ) && ! empty( $body->error_message ) ) {
				$status = isset( $body->status ) ? $body->status : $source . '_api_error';
				return new \WP_Error( $status, $body->error_message );
			}
		}

		return $response;
	}

	/**
	 * Get google reviews.
	 * 
	 * Get reviews from Google Place API and store it in transient.
	 *
	 * @since 2.8.0
	 *
	 * @return array $response Reviews data.
	 */
	private function get_google_reviews( $settings ) {

		$response = array(
			'data'	=> array(),
			'error' => false,
		);

		$transient_name = 'pp_reviews_' . $settings['google_place_id'] . '&language=' . $settings['language_id'];

		$response['data'] = get_transient( $transient_name );

		if ( empty( $response['data'] ) ) {
			$api_data = $this->get_api_data( 'google' );

			if ( is_wp_error( $api_data ) ) {
				
				$response['error'] = $api_data;

			} else {
				if ( 200 === wp_remote_retrieve_response_code( $api_data ) ) {
					
					$data = json_decode( wp_remote_retrieve_body( $api_data ) );
					
					if ( 'OK' !== $data->status ) {
						$response['error'] = isset( $data->error_message ) ? $data->error_message : __( 'No reviews found.', 'powerpack' );
					} else {
						if ( isset( $data->result ) && isset( $data->result->reviews ) ) {
							$response['data'] = array(
								'reviews'  => $data->result->reviews,
								'location' => array(),
							);

							if ( isset( $data->result->geometry->location ) ) {
								$response['data']['location'] = $data->result->geometry->location;
							}

							set_transient( $transient_name, $response['data'], $this->get_transient_expire( $settings ) );

							$response['error'] = false;
						} else {
							$response['error'] = __( 'This place doesn\'t have any reviews.', 'powerpack' );
						}
					}
				}
			}	
		}

		return $response;
	}

	/**
	 * Get yelp reviews.
	 * 
	 * Get reviews from Yelp Business API and store it in transient.
	 *
	 * @since 2.8.0
	 *
	 * @return array $response Reviews data.
	 */
	private function get_yelp_reviews( $settings ) {

		$response = array(
			'data'	=> array(),
			'error' => false,
		);

		$transient_name = 'pp_reviews_' . $settings['yelp_business_id'];

		$response['data'] = get_transient( $transient_name );

		if ( empty( $response['data'] ) ) {
			$api_data = $this->get_api_data( 'yelp' );

			if ( is_wp_error( $api_data ) ) {
				
				$response['error'] = $api_data;

			} else {
				if ( 200 !== wp_remote_retrieve_response_code( $api_data ) ) {
					$data = json_decode( wp_remote_retrieve_body( $api_data ) );

					if ( isset( $data->error ) ) {
						if ( 'VALIDATION_ERROR' === $data->error->code ) {
							$response['error'] = __( 'Yelp Reviews Error: Invalid or empty API key.', 'powerpack' );
						}
						if ( 'BUSINESS_NOT_FOUND' === $data->error->code ) {
							$response['error'] = __( 'Yelp Reviews Error: Incorrect or empty Business ID.', 'powerpack' );
						}
						if ( 'INTERNAL_SERVER_ERROR' === $data->error->code ) {
							$response['error'] = __( 'Yelp Reviews Error: Something is wrong with Yelp.', 'powerpack' );
						}
					} else {
						$response['error'] = __( 'Yelp Reviews Error: Unknown error occurred.', 'powerpack' );
					}
				} else {
					$data = json_decode( wp_remote_retrieve_body( $api_data ) );

					if ( empty( $data ) || ! isset( $data->reviews ) || empty( $data->reviews ) ) {
						$response['error'] = __( 'This business doesn\'t have any reviews.', 'powerpack' );
					} else {
						$response['data'] = $data->reviews;

						set_transient( $transient_name, $response['data'], $this->get_transient_expire( $settings ) );

						$response['error'] = false;
					}
				}
			}	
		}

		return $response;
	}

	/**
	 * Gets expire time of transient.
	 *
	 * @since 2.8.0
	 * @param array $settings The settings array.
	 * @return the reviews transient expire time.
	 * @access public
	 */
	public function get_transient_expire( $settings ) {

		$expire_value = $settings['reviews_refresh_time'];
		$expire_time  = 24 * HOUR_IN_SECONDS;

		if ( 'hour' === $expire_value ) {
			$expire_time = 60 * MINUTE_IN_SECONDS;
		} elseif ( 'week' === $expire_value ) {
			$expire_time = 7 * DAY_IN_SECONDS;
		} elseif ( 'month' === $expire_value ) {
			$expire_time = 30 * DAY_IN_SECONDS;
		} elseif ( 'year' === $expire_value ) {
			$expire_time = 365 * DAY_IN_SECONDS;
		}

		return $expire_time;
	}

	/**
	 * Get Reviews array with the same key for Google & Yelp.
	 *
	 * @since 2.8.0
	 * @param string $type The reviews source.
	 * @param array  $reviews The reviews array.
	 * @param array  $settings The settings array.
	 * @return the merged array of Google & Yelp reviews.
	 * @access public
	 */
	public function parse_reviews( $type, $reviews, $settings ) {
		if ( is_wp_error( $reviews['error'] ) ) {
			return $reviews['error'];
		}

		if ( empty( $reviews['data'] ) ) {
			return;
		}

		$parsed_reviews       = array();
		$filter_by_min_rating = false;

		if ( 'no' !== $settings['reviews_min_rating'] ) {
			$filter_by_min_rating = true;
		}

		$data = $reviews['data'];

		if ( 'google' === $type ) {
			$data = $data['reviews'];
		}

		foreach ( $data as $review ) {
			$_review = array();

			if ( 'google' === $type ) {
				$user_review_url = explode( '/reviews', $review->author_url );
				array_pop( $user_review_url );
				$review_url = $user_review_url[0] . '/place/' . $settings['google_place_id'];

				$_review['source']                    = 'google';
				$_review['author_name']               = $review->author_name;
				$_review['author_url']                = $review->author_url;
				$_review['profile_photo_url']         = $review->profile_photo_url;
				$_review['rating']                    = $review->rating;
				$_review['relative_time_description'] = $review->relative_time_description;
				$_review['text']                      = $review->text;
				$_review['time']                      = $review->time;
				$_review['review_url']                = $review_url;
			}
			if ( 'yelp' === $type ) {
				$_review['source']                    = 'yelp';
				$_review['author_name']               = $review->user->name;
				$_review['author_url']                = $review->user->profile_url;
				$_review['profile_photo_url']         = $review->user->image_url;
				$_review['rating']                    = $review->rating;
				$_review['relative_time_description'] = '';
				$_review['text']                      = $review->text;
				$_review['time']                      = $review->time_created;
				$_review['review_url']                = $review->url;
			}

			if ( $filter_by_min_rating ) {
				if ( $review->rating >= $settings['reviews_min_rating'] ) {
					array_push( $parsed_reviews, $_review );
				}
			} else {
				array_push( $parsed_reviews, $_review );
			}
		}

		return $parsed_reviews;

	}
	/**
	 * Get Reviews array with the same key for Google & Yelp.
	 *
	 * @since 2.8.0
	 * @param array $settings The settings array.
	 * @return the layout of Google reviews.
	 * @access public
	 */
	public function get_reviews( $settings ) {
		$reviews = array();

		if ( 'google' === $settings['reviews_source'] ) {

			$reviews = $this->get_google_reviews( $settings );
			$reviews = $this->parse_reviews( 'google', $reviews, $settings );

		} elseif ( 'yelp' === $settings['reviews_source'] ) {

			$reviews = $this->get_yelp_reviews( $settings );
			$reviews = $this->parse_reviews( 'yelp', $reviews, $settings );

		} elseif ( 'all' === $settings['reviews_source'] ) {

			$google_reviews = $this->get_google_reviews( $settings );
			$yelp_reviews = $this->get_yelp_reviews( $settings );

			$google_reviews = $this->parse_reviews( 'google', $google_reviews, $settings );
			$yelp_reviews   = $this->parse_reviews( 'yelp', $yelp_reviews, $settings );

			if ( empty( $google_reviews ) || empty( $yelp_reviews ) ) {
				return;
			}

			$count = count( $google_reviews );

			/* Merge reviews array elements inalternative order */
			for ( $i = 0; $i < $count; $i++ ) {
				$reviews[] = $google_reviews[ $i ];
				if ( $i < count( $yelp_reviews ) ) {
					$reviews[] = $yelp_reviews[ $i ];
				}
			}
			$reviews = array_filter( $reviews );
		}

		return $reviews;
	}

	/**
	 * Get sorted array of reviews by rating.
	 *
	 * @since 2.8.0
	 * @access public
	 * @param string $review1 represents review1 to compare.
	 * @param string $review2 represents review2 to compare.
	 * @return string of compared reviews.
	 */
	public function filter_by_rating( $review1, $review2 ) {
		return strcmp( $review2['rating'], $review1['rating'] );
	}

	/**
	 * Get sorted array of reviews by date.
	 *
	 * @since 2.8.0
	 * @access public
	 * @param string $review1 represents review1 to compare.
	 * @param string $review2 represents review2 to compare.
	 * @return string of compared reviews.
	 */
	public function filter_by_date( $review1, $review2 ) {
		return strcmp( $review2['time'], $review1['time'] );
	}

	/**
	 * Get reviewer name section.
	 *
	 * @since 2.8.0
	 * @access public
	 * @param string $review represents single review.
	 * @param array  $settings represents settings array.
	 */
	public function get_reviewer_name( $review, $settings ) {
		if ( 'yes' === $this->get_instance_value( 'reviewer_name' ) ) {
			?>
			<?php if ( 'yes' === $this->get_instance_value( 'reviewer_name_link' ) ) { ?>
				<span class="pp-reviewer-name"><?php echo wp_kses_post( "<a href={$review['author_url']} target='_blank'>{$review['author_name']}</a>" ); ?></span>
			<?php } else { ?>
				<span class="pp-reviewer-name"><?php echo wp_kses_post( "{$review['author_name']}" ); ?></span>
				<?php
			}
		}
	}

	/**
	 * Get review header.
	 *
	 * @since 2.8.0
	 * @access public
	 * @param string $review represents single review.
	 * @param string $photolink represents reviewer image link.
	 * @param array  $settings represents settings array.
	 */
	public function get_reviews_header( $review, $photolink, $settings ) {
		$total_rating = $review['rating'];
		$timestamp    = ( 'google' === $review['source'] ) ? $review['time'] : strtotime( $review['time'] );
		$date         = gmdate( 'd-m-Y', $timestamp );

		$google_svg = '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="18px" height="18px" viewBox="0 0 100 100" enable-background="new 0 0 100 100" xml:space="preserve">
		<g>
			<path id="XMLID_5_" fill="#FFFFFF" d="M34.963,3.686C23.018,7.777,12.846,16.712,7.206,28.002
				c-1.963,3.891-3.397,8.045-4.258,12.315C0.78,50.961,2.289,62.307,7.2,72.002c3.19,6.328,7.762,11.951,13.311,16.361
				c5.236,4.175,11.336,7.256,17.806,8.979c8.163,2.188,16.854,2.14,25.068,0.268c7.426-1.709,14.452-5.256,20.061-10.436
				c5.929-5.449,10.158-12.63,12.399-20.342c2.441-8.415,2.779-17.397,1.249-26.011c-15.373-0.009-30.744-0.004-46.113-0.002
				c0.003,6.375-0.007,12.749,0.006,19.122c8.9-0.003,17.802-0.006,26.703,0c-1.034,6.107-4.665,11.696-9.813,15.135
				c-3.236,2.176-6.954,3.587-10.787,4.26c-3.861,0.661-7.846,0.746-11.696-0.035c-3.914-0.781-7.649-2.412-10.909-4.711
				c-5.212-3.662-9.189-9.018-11.23-15.048c-2.088-6.132-2.103-12.954,0.009-19.08c1.466-4.316,3.907-8.305,7.112-11.551
				c3.955-4.048,9.095-6.941,14.633-8.128c4.742-1.013,9.745-0.819,14.389,0.586c3.947,1.198,7.584,3.359,10.563,6.206
				c3.012-2.996,6.011-6.008,9.014-9.008c1.579-1.615,3.236-3.161,4.763-4.819C79.172,9.52,73.819,6.123,67.97,3.976
				C57.438,0.1,45.564,0.018,34.963,3.686z"/>
			<g>
				<path id="XMLID_4_" fill="#EA4335" d="M34.963,3.686C45.564,0.018,57.438,0.1,67.97,3.976c5.85,2.147,11.202,5.544,15.769,9.771
					c-1.526,1.659-3.184,3.205-4.763,4.819c-3.003,3-6.002,6.012-9.014,9.008c-2.979-2.846-6.616-5.008-10.563-6.206
					c-4.645-1.405-9.647-1.599-14.389-0.586c-5.539,1.187-10.679,4.08-14.633,8.128c-3.206,3.246-5.646,7.235-7.112,11.551
					c-5.353-4.152-10.703-8.307-16.058-12.458C12.846,16.712,23.018,7.777,34.963,3.686z"/>
			</g>
			<g>
				<path id="XMLID_3_" fill="#FBBC05" d="M2.947,40.317c0.861-4.27,2.295-8.424,4.258-12.315c5.355,4.151,10.706,8.306,16.058,12.458
					c-2.112,6.126-2.097,12.948-0.009,19.08C17.903,63.695,12.557,67.856,7.2,72.002C2.289,62.307,0.78,50.961,2.947,40.317z"/>
			</g>
			<g>
				<path id="XMLID_2_" fill="#4285F4" d="M50.981,40.818c15.369-0.002,30.74-0.006,46.113,0.002
					c1.53,8.614,1.192,17.596-1.249,26.011c-2.241,7.712-6.471,14.893-12.399,20.342c-5.18-4.039-10.386-8.057-15.568-12.099
					c5.147-3.438,8.778-9.027,9.813-15.135c-8.9-0.006-17.803-0.003-26.703,0C50.974,53.567,50.984,47.194,50.981,40.818z"/>
			</g>
			<g>
				<path id="XMLID_1_" fill="#34A853" d="M7.2,72.002c5.356-4.146,10.703-8.307,16.055-12.461c2.041,6.03,6.018,11.386,11.23,15.048
					c3.26,2.299,6.995,3.93,10.909,4.711c3.851,0.781,7.835,0.696,11.696,0.035c3.833-0.673,7.551-2.084,10.787-4.26
					c5.183,4.042,10.389,8.06,15.568,12.099c-5.608,5.18-12.635,8.727-20.061,10.436c-8.215,1.872-16.906,1.921-25.068-0.268
					c-6.469-1.723-12.57-4.804-17.806-8.979C14.962,83.953,10.39,78.33,7.2,72.002z"/>
			</g>
		</g>
		</svg>';

		if ( 'yes' === $this->get_instance_value( 'review_date' ) ) {
			if ( 'google' === $settings['reviews_source'] ) {
				$date_value = ( 'default' === $this->get_instance_value( 'review_date_type' ) ) ? $date : $review['relative_time_description'];
			} else {
				$date_value = $date;
			}
		}
		?>
		<div class="pp-review-header">
			<?php if ( 'yes' === $this->get_instance_value( 'reviewer_image' ) && 'all_left' !== $this->get_instance_value( 'image_align' ) ) { ?>
				<div class="pp-review-image" style="background-image:url( <?php echo wp_kses_post( $photolink ); ?> );"></div>
			<?php } ?>
			<div class="pp-review-details">
				<?php
				if ( 'classic' === $settings['_skin'] ) {
					$this->get_reviewer_name( $review, $settings );
				}
				?>
				<?php if ( 'yes' === $this->get_instance_value( 'star_rating' ) ) { ?>
					<?php
					$star_rating_wrap_class = '';

					if ( 'yelp' !== $review['source'] || 'custom' === $this->get_instance_value( 'star_style' ) ) {
						$star_rating_wrap_class = 'pp-review-stars';
					}
					?>
					<span class="elementor-star-rating__wrapper <?php echo esc_attr( $star_rating_wrap_class ); ?>">
						<span class="pp-star-rating"><?php echo $this->render_stars( $total_rating, $review, $settings ); ?></span>
					</span>
				<?php } ?>
				<?php
				if ( 'yes' === $this->get_instance_value( 'review_date' ) ) {
					$review_source = ( 'google' === $review['source'] ) ? 'Google' : 'Yelp';
					$via_source    = ' via ' . $review_source;
					if ( 'yes' === $this->get_instance_value( 'review_source_icon' ) ) {
						$via_source = '';
					}
					?>
					<span class="pp-review-time"><?php echo esc_attr( $date_value ) . esc_attr( $via_source ); ?></span>
				<?php } ?>
				<?php
				if ( 'card' === $settings['_skin'] ) {
					$this->get_reviewer_name( $review, $settings );
				}
				?>
			</div>
			<?php if ( 'yes' === $this->get_instance_value( 'review_source_icon' ) && ( 'all_left' === $this->get_instance_value( 'image_align' ) || 'left' === $this->get_instance_value( 'image_align' ) ) ) { ?>
				<div class="pp-review-icon-wrap">
					<?php if ( 'yelp' === $review['source'] ) {
						self::render_network_icon('fa fa-yelp');
					} else {
						echo $google_svg; // phpcs:ignore
					}
					?>
				</div>
			<?php } ?>
		</div>
		<?php
	}

	private static function render_network_icon( $network_name ) {
		$network_icon_data = self::get_network_icon_data( $network_name );

		if ( \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_font_icon_svg' ) ) {
			$icon = Icons_Manager::render_font_icon( $network_icon_data );
		} else {
			$icon = sprintf( '<i class="%s" aria-hidden="true"></i>', $network_icon_data['value'] );
		}

		$icon = '<span class="pp-icon">' . $icon . '</span>';

		\Elementor\Utils::print_unescaped_internal_string( $icon );
	}

	/**
	 * Render classic skin output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access public
	 */
	public function render_skin_classic( $review ) {
		$settings  = $this->parent->get_settings_for_display();
		$photolink = ( null !== $review['profile_photo_url'] ) ? $review['profile_photo_url'] : ( POWERPACK_ELEMENTS_URL . 'assets/images/user.png' );
		?>
		<?php if ( 'yes' === $this->get_instance_value( 'reviewer_image' ) && 'all_left' === $this->get_instance_value( 'image_align' ) ) { ?>
			<div class="pp-review-image" style="background-image:url( <?php echo esc_url( $photolink ); ?> );"></div>
		<?php } ?>
		<div class="pp-review-inner-wrap">
			<?php $this->get_reviews_header( $review, $photolink, $settings ); ?>
			<?php if ( 'yes' === $this->get_instance_value( 'review_content' ) ) { ?>
				<?php
				$the_content = $review['text'];
				if ( '' !== $this->get_instance_value( 'review_content_length' ) ) {
					$the_content    = wp_strip_all_tags( $review['text'] ); // Strips tags.
					$content_length = $this->get_instance_value( 'review_content_length' ); // Sets content length by word count.
					$words          = explode( ' ', $the_content, $content_length + 1 );
					if ( count( $words ) > $content_length ) {
						array_pop( $words );
						$the_content  = implode( ' ', $words ); // put in content only the number of word that is set in $content_length.
						$the_content .= '...';
						if ( '' !== $this->get_instance_value( 'read_more' ) ) {
							$the_content .= '<a href="' . apply_filters( 'pp_business_reviews_read_more', $review['review_url'] ) . '"  target="_blank" rel="noopener noreferrer" class="pp-reviews-read-more">' . $this->get_instance_value( 'read_more' ) . '</a>';
						}
					}
				}
				?>
				<div class="pp-review-content"><?php echo wp_kses_post( $the_content ); ?></div>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Render card skin output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access public
	 */
	public function render_skin_card( $review ) {
		$settings  = $this->parent->get_settings_for_display();
		$photolink = ( null !== $review['profile_photo_url'] ) ? $review['profile_photo_url'] : ( POWERPACK_ELEMENTS_URL . 'assets/images/user.png' );
		?>
		<?php if ( 'yes' === $this->get_instance_value( 'reviewer_image' ) && 'all_left' === $this->get_instance_value( 'image_align' ) ) { ?>
			<div class="pp-review-image" style="background-image:url( <?php echo esc_url( $photolink ); ?> );"></div>
		<?php } ?>
		<div class="pp-review-inner-wrap">
			<?php if ( 'yes' === $this->get_instance_value( 'review_content' ) ) { ?>
				<?php
				$the_content = $review['text'];
				if ( '' !== $this->get_instance_value( 'review_content_length' ) ) {
					$the_content    = wp_strip_all_tags( $review['text'] ); // Strips tags.
					$content_length = $this->get_instance_value( 'review_content_length' ); // Sets content length by word count.
					$words          = explode( ' ', $the_content, $content_length + 1 );
					if ( count( $words ) > $content_length ) {
						array_pop( $words );
						$the_content  = implode( ' ', $words ); // put in content only the number of word that is set in $content_length.
						$the_content .= '...';
						if ( '' !== $this->get_instance_value( 'read_more' ) ) {
							$the_content .= '<a href="' . apply_filters( 'pp_business_reviews_read_more', $review['review_url'] ) . '"  target="_blank" rel="noopener noreferrer" class="pp-reviews-read-more">' . $this->get_instance_value( 'read_more' ) . '</a>';
						}
					}
				}
				?>
				<div class="pp-review-content"><?php echo wp_kses_post( $the_content ); ?></div>
				<?php $this->get_reviews_header( $review, $photolink, $settings ); ?>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Render business reviews widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access public
	 */
	public function render() {
		$settings = $this->parent->get_settings_for_display();
		$is_editor = \Elementor\Plugin::instance()->editor->is_edit_mode();
		$skin     = str_replace( '-', '_', $this->get_id() );

		$reviews            = '';
		$reviews_max        = 8;
		$disply_num_reviews = 8;

		$reviews = $this->get_reviews( $settings );

		if ( is_wp_error( $reviews ) ) {
			$error_message = $reviews->get_error_message();

			if ( $is_editor ) {
				echo esc_attr( $this->parent->render_editor_placeholder(
					[
						'title' => __( 'Business Reviews', 'powerpack' ),
						'body' => $error_message,
					]
				) );
			}
			return;
		}

		if ( ! is_array( $reviews ) || empty( $reviews ) ) {
			return;
		}

		$layout_class = ( 'carousel' === $settings['layout'] ) ? 'pp-reviews-layout-carousel pp-swiper-slider swiper-container' : 'elementor-grid pp-reviews-layout-grid';
		$image_align  = ( 'yes' === $this->get_instance_value( 'reviewer_image' ) ) ? 'pp-review-image-' . $this->get_instance_value( 'image_align' ) : '';

		$this->parent->add_render_attribute( 'container-wrap', 'class', 'pp-business-reviews-widget' );

		$this->parent->add_render_attribute(
			'reviews-wrap',
			array(
				'class'            => array(
					'pp-reviews-wrapper',
					'pp-reviews-' . $settings['_skin'],
					$image_align,
					$layout_class
				),
				'data-review-skin' => $settings['_skin'],
				'data-layout'      => $settings['layout'],
			)
		);

		$this->parent->add_render_attribute( 'review-wrap', 'class', 'pp-review-wrap pp-grid-item-wrap elementor-grid-item' );

		if ( ( 'auto' === $settings['direction'] && is_rtl() ) || 'right' === $settings['direction'] ) {
			$this->parent->add_render_attribute( 'reviews-wrap', 'dir', 'rtl' );
		}

		if ( 'carousel' === $settings['layout'] ) {
			$slider_options = $this->get_slider_settings();

			$this->parent->add_render_attribute( 'container-wrap', 'class', [ 'swiper-container-wrap', 'swiper' ] );

			$this->parent->add_render_attribute( 'review-wrap', 'class', 'swiper-slide' );

			$this->parent->add_render_attribute(
				'reviews-wrap',
				array(
					'data-equal-height'    => $settings['equal_height'],
					'data-slider-settings' => wp_json_encode( $slider_options ),
				)
			);
		}
		?>
		<div <?php $this->parent->print_render_attribute_string( 'container-wrap' ); ?>>
			<div <?php $this->parent->print_render_attribute_string( 'reviews-wrap' ); ?>>
				<?php if ( 'carousel' === $settings['layout'] ) { ?><div class="swiper-wrapper"><?php } ?>
					<?php
					if ( 'rating' === $settings['reviews_filter_by'] ) {
						usort( $reviews, array( $this, 'filter_by_rating' ) );
					} elseif ( 'date' === $settings['reviews_filter_by'] ) {
						usort( $reviews, array( $this, 'filter_by_date' ) );
					}

					if ( 'google' === $settings['reviews_source'] ) {
						$reviews_max        = 5;
						$disply_num_reviews = $settings['google_reviews_count'];
					} elseif ( 'yelp' === $settings['reviews_source'] ) {
						$reviews_max        = 3;
						$disply_num_reviews = $settings['yelp_reviews_count'];
					} elseif ( 'all' === $settings['reviews_source'] ) {
						$reviews_max        = 8;
						$disply_num_reviews = $settings['reviews_count'];
					}

					$disply_num_reviews = ( '' !== $disply_num_reviews ) ? $disply_num_reviews : $reviews_max;

					if ( $reviews_max !== $disply_num_reviews ) {
						$display_number = (int) $disply_num_reviews;
						$reviews        = array_slice( $reviews, 0, $display_number );
					}

					foreach ( $reviews as $key => $review ) {
						?>
						<div <?php $this->parent->print_render_attribute_string( 'review-wrap' ); ?>>
							<div class="pp-review pp-review-type-<?php echo esc_attr( $review['source'] ); ?>" >
								<?php
								if ( 'card' === $skin ) {
									$this->render_skin_card( $review );
								} else {
									$this->render_skin_classic( $review );
								}
								?>
							</div>
						</div>
						<?php
					}
					?>
				<?php if ( 'carousel' === $settings['layout'] ) { ?></div><?php } ?>
			</div>
			<?php
			if ( 'carousel' === $settings['layout'] ) {
				if ( 'yes' === $settings['arrows'] || 'yes' === $settings['pagination'] ) {
					$this->render_dots();

					$this->render_arrows();
				}
			}
			?>
		</div>
		<?php
	}
}
