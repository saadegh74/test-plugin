<?php
/**
 * PowerPack WooCommerce Add To Cart Button.
 *
 * @package PowerPack
 */

namespace PowerpackElements\Modules\Woocommerce\Widgets;

use PowerpackElements\Base\Powerpack_Widget;

use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Core\Schemes\Typography as Scheme_Typography;
use Elementor\Core\Schemes\Color as Scheme_Color;

if ( ! defined( 'ABSPATH' ) ) {
	exit;   // Exit if accessed directly.
}

/**
 * Woo - Categories Widget
 */
class Woo_Categories extends Powerpack_Widget {

	/**
	 * Retrieve Woo - Categories widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Woo_Categories' );
	}

	/**
	 * Retrieve Woo - Categories widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Woo_Categories' );
	}

	/**
	 * Retrieve Woo - Categories widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Woo_Categories' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the Woo - Categories widget belongs to.
	 *
	 * @since 1.4.11.0
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'Woo_Categories' );
	}

	/**
	 * Retrieve the list of scripts the Woo - Categories widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return [
			'swiper',
			'imagesloaded',
			'pp-woocommerce',
		];
	}

	/**
	 * Retrieve the list of styles the Woo - Categories depended on.
	 *
	 * Used to set style dependencies required to run the widget.
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_style_depends() {
		return [
			'pp-woocommerce',
		];
	}

	/**
	 * Register Woo - Categories widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 2.0.3
	 * @access protected
	 */
	protected function register_controls() {
		/* Product Control */
		$this->register_content_general_controls();
		$this->register_content_grid_controls();
		$this->register_content_carousel_controls();
		$this->register_content_filter_controls();

		/* Style */
		$this->register_style_layout_controls();
		$this->register_style_category_controls();
		/* Style Tab: Arrows */
		$this->register_style_arrows_controls();
		/* Style Tab: Dots */
		$this->register_style_dots_controls();
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
			[
				'label'                 => __( 'Layout', 'powerpack' ),
				'tab'                   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'layout',
			[
				'label'                 => __( 'Layout', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'default'               => 'grid',
				'options'               => [
					'grid'      => __( 'Grid', 'powerpack' ),
					'carousel'  => __( 'Carousel', 'powerpack' ),
					'tiles'     => __( 'Tiles', 'powerpack' ),
				],
			]
		);

		$this->add_control(
			'tiles_style',
			[
				'label'                 => __( 'Tiles Style', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'default'               => '1',
				'options'               => [
					'1'     => __( 'Style 1', 'powerpack' ),
					'2'     => __( 'Style 2', 'powerpack' ),
				],
				'condition'             => [
					'layout'    => 'tiles',
				],
			]
		);

		$this->add_control(
			'cats_count',
			[
				'label'                 => __( 'Categories Count', 'powerpack' ),
				'type'                  => Controls_Manager::NUMBER,
				'default'               => '4',
			]
		);

		$this->add_control(
			'content_position',
			[
				'label'                 => __( 'Content Position', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'default'               => 'overlay',
				'options'               => [
					'default'   => __( 'Below Image', 'powerpack' ),
					'overlay'   => __( 'Over Image', 'powerpack' ),
				],
				'condition'             => [
					'layout!'   => 'tiles',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'    => 'image_size',
				'label'   => __( 'Image Size', 'powerpack' ),
				'default' => 'woocommerce_thumbnail',
			)
		);

		$this->add_control(
			'cat_title',
			[
				'label'                 => __( 'Category Title', 'powerpack' ),
				'type'                  => Controls_Manager::SWITCHER,
				'default'               => 'yes',
				'label_on'              => __( 'Show', 'powerpack' ),
				'label_off'             => __( 'Hide', 'powerpack' ),
				'return_value'          => 'yes',
			]
		);

		$this->add_control(
			'product_count',
			[
				'label'                 => __( 'Product Count', 'powerpack' ),
				'type'                  => Controls_Manager::SWITCHER,
				'default'               => 'yes',
				'label_on'              => __( 'Show', 'powerpack' ),
				'label_off'             => __( 'Hide', 'powerpack' ),
				'return_value'          => 'yes',
			]
		);

		$this->add_control(
			'cat_desc',
			[
				'label'                 => __( 'Category Description', 'powerpack' ),
				'type'                  => Controls_Manager::SWITCHER,
				'default'               => '',
				'label_on'              => __( 'Show', 'powerpack' ),
				'label_off'             => __( 'Hide', 'powerpack' ),
				'return_value'          => 'yes',
			]
		);

		$this->add_control(
			'category_desc_limit',
			[
				'label'                 => __( 'Words Limit', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'default'               => '',
				'condition'             => [
					'cat_desc' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Register Grid Controls.
	 *
	 * @access protected
	 */
	protected function register_content_grid_controls() {

		$this->start_controls_section(
			'section_grid_settings',
			[
				'label'                 => __( 'Grid Options', 'powerpack' ),
				'tab'                   => Controls_Manager::TAB_CONTENT,
				'condition'             => [
					'layout!' => 'carousel',
				],
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label'                 => __( 'Columns', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'default'               => '3',
				'tablet_default'        => '2',
				'mobile_default'        => '1',
				'options'               => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
					'7' => '7',
					'8' => '8',
				],
				'prefix_class'          => 'elementor-grid%s-',
				'frontend_available'    => true,
				'condition'             => [
					'layout!' => 'carousel',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Register Carousel Controls.
	 *
	 * @access protected
	 */
	protected function register_content_carousel_controls() {
		$this->start_controls_section(
			'section_carousel_options',
			[
				'label'                 => __( 'Carousel Options', 'powerpack' ),
				'type'                  => Controls_Manager::SECTION,
				'condition'             => [
					'layout'   => 'carousel',
				],
			]
		);

		$this->add_responsive_control(
			'slides_to_show',
			[
				'label'                 => __( 'Categories to Show', 'powerpack' ),
				'type'                  => Controls_Manager::NUMBER,
				'default'               => 4,
				'tablet_default'        => 3,
				'mobile_default'        => 1,
				'condition'             => [
					'layout'   => 'carousel',
				],
			]
		);

		$this->add_responsive_control(
			'slides_to_scroll',
			[
				'label'                 => __( 'Categories to Scroll', 'powerpack' ),
				'type'                  => Controls_Manager::NUMBER,
				'default'               => 1,
				'tablet_default'        => 1,
				'mobile_default'        => 1,
				'condition'             => [
					'layout'   => 'carousel',
				],
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label'                 => __( 'Autoplay', 'powerpack' ),
				'type'                  => Controls_Manager::SWITCHER,
				'return_value'          => 'yes',
				'default'               => '',
				'condition'             => [
					'layout'   => 'carousel',
				],
			]
		);
		$this->add_control(
			'autoplay_speed',
			[
				'label'                 => __( 'Autoplay Speed', 'powerpack' ),
				'type'                  => Controls_Manager::NUMBER,
				'default'               => 3000,
				'condition'             => [
					'layout'   => 'carousel',
					'autoplay' => 'yes',
				],
			]
		);
		$this->add_control(
			'pause_on_hover',
			[
				'label'                 => __( 'Pause on Hover', 'powerpack' ),
				'type'                  => Controls_Manager::SWITCHER,
				'return_value'          => 'yes',
				'default'               => 'yes',
				'condition'             => [
					'layout'   => 'carousel',
					'autoplay' => 'yes',
				],
			]
		);

		$this->add_control(
			'infinite',
			[
				'label'                 => __( 'Infinite Loop', 'powerpack' ),
				'type'                  => Controls_Manager::SWITCHER,
				'return_value'          => 'yes',
				'default'               => 'yes',
				'condition'             => [
					'layout'   => 'carousel',
				],
			]
		);

		$this->add_control(
			'transition_speed',
			[
				'label'                 => __( 'Transition Speed (ms)', 'powerpack' ),
				'type'                  => Controls_Manager::NUMBER,
				'default'               => 600,
				'condition'             => [
					'layout'   => 'carousel',
				],
			]
		);

		$this->add_control(
			'navigation',
			[
				'label'                 => __( 'Navigation', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
				'separator'             => 'before',
				'condition'             => [
					'layout'   => 'carousel',
				],
			]
		);

		$this->add_control(
			'arrows',
			[
				'label'                 => __( 'Arrows', 'powerpack' ),
				'type'                  => Controls_Manager::SWITCHER,
				'return_value'          => 'yes',
				'default'               => 'yes',
				'condition'             => [
					'layout'   => 'carousel',
				],
			]
		);

		$this->add_control(
			'dots',
			[
				'label'                 => __( 'Dots', 'powerpack' ),
				'type'                  => Controls_Manager::SWITCHER,
				'return_value'          => 'yes',
				'default'               => 'yes',
				'condition'             => [
					'layout'   => 'carousel',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Register Woo Products Filter Controls.
	 *
	 * @access protected
	 */
	protected function register_content_filter_controls() {

		$this->start_controls_section(
			'section_filter_field',
			[
				'label'                 => __( 'Filters', 'powerpack' ),
				'tab'                   => Controls_Manager::TAB_CONTENT,
			]
		);

			$this->add_control(
				'category_filter_rule',
				[
					'label'   => __( 'Category Filter Rule', 'powerpack' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'all',
					'options' => [
						'all'     => __( 'Show All', 'powerpack' ),
						'top'     => __( 'Only Top Level', 'powerpack' ),
						'include' => __( 'Match These Categories', 'powerpack' ),
						'exclude' => __( 'Exclude These Categories', 'powerpack' ),
					],
				]
			);
			$this->add_control(
				'category_filter',
				[
					'label'                 => __( 'Category Filter', 'powerpack' ),
					'type'                  => Controls_Manager::SELECT2,
					'multiple'  => true,
					'default'               => '',
					'options'   => $this->get_product_categories(),
					'condition'             => [
						'category_filter_rule' => [ 'include', 'exclude' ],
					],
				]
			);
			$this->add_control(
				'display_empty_cat',
				[
					'label'                 => __( 'Display Empty Categories', 'powerpack' ),
					'type'                  => Controls_Manager::SWITCHER,
					'default'               => '',
					'label_on'     => 'Yes',
					'label_off'    => 'No',
					'return_value'          => 'yes',
				]
			);
			$this->add_control(
				'orderby',
				[
					'label'   => __( 'Order by', 'powerpack' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'name',
					'options' => [
						'name'  => __( 'Name', 'powerpack' ),
						'slug'  => __( 'Slug', 'powerpack' ),
						'desc'  => __( 'Description', 'powerpack' ),
						'count' => __( 'Count', 'powerpack' ),
						'menu_order' => __( 'Menu Order', 'powerpack' ),
					],
				]
			);

			$this->add_control(
				'order',
				[
					'label'   => __( 'Order', 'powerpack' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'desc',
					'options' => [
						'desc' => __( 'Descending', 'powerpack' ),
						'asc'  => __( 'Ascending', 'powerpack' ),
					],
				]
			);
		$this->end_controls_section();
	}

	/**
	 * Style Tab
	 */
	/**
	 * Register Layout Controls.
	 *
	 * @since 1.3.3
	 * @access protected
	 */
	protected function register_style_layout_controls() {
		$this->start_controls_section(
			'section_design_layout',
			[
				'label'                 => __( 'Layout', 'powerpack' ),
				'tab'                   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'column_gap',
			[
				'label'                 => __( 'Columns Gap', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'default'               => [
					'size' => 20,
				],
				'range'                => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'render_type'           => 'template',
				'selectors'             => [
					'{{WRAPPER}}' => '--grid-column-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'row_gap',
			[
				'label'                 => __( 'Rows Gap', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'default'               => [
					'size' => 35,
				],
				'range'                => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'             => [
					'{{WRAPPER}}' => '--grid-row-gap: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .pp-woo-categories-tiles .product' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .pp-woo-cat-tiles-1 .pp-woo-cat-tiles-center .product, {{WRAPPER}} .pp-woo-cat-tiles-2 .pp-woo-cat-tiles-right .product' => 'height: calc((550px - {{SIZE}}{{UNIT}}) / 2);',
				],
				'condition'             => [
					'layout' => [ 'grid', 'tiles' ],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'                  => 'column_border',
				'label'                 => __( 'Border', 'powerpack' ),
				'placeholder'           => '1px',
				'default'               => '1px',
				'separator'             => 'before',
				'selector'              => '{{WRAPPER}} .pp-woo-categories .product-category .pp-grid-item',
			]
		);

			$this->add_control(
				'column_border_color_hover',
				[
					'label'                 => __( 'Border Hover Color', 'powerpack' ),
					'type'                  => Controls_Manager::COLOR,
					'selectors'             => [
						'{{WRAPPER}} .pp-woo-categories .product-category .pp-grid-item:hover' => 'border-color: {{VALUE}};',
					],
				]
			);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'column_box_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-categories .product-category .pp-grid-item',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Register Category Content Controls.
	 *
	 * @since 1.3.3
	 * @access protected
	 */
	protected function register_style_category_controls() {
		$this->start_controls_section(
			'section_design_cat_content',
			[
				'label'                 => __( 'Category Content', 'powerpack' ),
				'tab'                   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'cat_content_vertical_align',
			[
				'label'                 => __( 'Vertical Align', 'powerpack' ),
				'type'                  => Controls_Manager::CHOOSE,
				'label_block'           => false,
				'default'               => 'middle',
				'options'               => [
					'top'          => [
						'title'    => __( 'Top', 'powerpack' ),
						'icon'     => 'eicon-v-align-top',
					],
					'middle'       => [
						'title'    => __( 'Center', 'powerpack' ),
						'icon'     => 'eicon-v-align-middle',
					],
					'bottom'       => [
						'title'    => __( 'Bottom', 'powerpack' ),
						'icon'     => 'eicon-v-align-bottom',
					],
				],
				'selectors'             => [
					'{{WRAPPER}} .pp-woo-categories-overlay .product .pp-product-cat-content-wrap'   => 'justify-content: {{VALUE}};',
				],
				'selectors_dictionary'  => [
					'top'          => 'flex-start',
					'middle'       => 'center',
					'bottom'       => 'flex-end',
				],
				'conditions' => [
					'relation' => 'or',
					'terms' => [
						[
							'relation' => 'and',
							'terms' => [
								[
									'relation' => 'or',
									'terms' => [
										[
											'name' => 'layout',
											'operator' => '==',
											'value' => 'grid',
										],
										[
											'name' => 'layout',
											'operator' => '==',
											'value' => 'carousel',
										],
									],
								],
								[
									'name' => 'content_position',
									'operator' => '==',
									'value' => 'overlay',
								],
							],
						],
						[
							'name' => 'layout',
							'operator' => '==',
							'value' => 'tiles',
						],
					],
				],
			]
		);

		$this->add_control(
			'cat_content_horizontal_align',
			[
				'label'                 => __( 'Horizontal Align', 'powerpack' ),
				'type'                  => Controls_Manager::CHOOSE,
				'label_block'           => false,
				'options'               => [
					'left'      => [
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-h-align-left',
					],
					'center'           => [
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'eicon-h-align-center',
					],
					'right'            => [
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-h-align-right',
					],
					'justify'   => [
						'title'    => __( 'Stretch', 'powerpack' ),
						'icon'     => 'eicon-h-align-stretch',
					],
				],
				'default'               => 'center',
				'selectors_dictionary'  => [
					'left'     => 'flex-start',
					'center'   => 'center',
					'right'    => 'flex-end',
					'justify'  => 'stretch',
				],
				'selectors'             => [
					'{{WRAPPER}} .pp-woo-categories-overlay .product .pp-product-cat-content-wrap' => 'align-items: {{VALUE}};',
				],
				'conditions' => [
					'relation' => 'or',
					'terms' => [
						[
							'relation' => 'and',
							'terms' => [
								[
									'relation' => 'or',
									'terms' => [
										[
											'name' => 'layout',
											'operator' => '==',
											'value' => 'grid',
										],
										[
											'name' => 'layout',
											'operator' => '==',
											'value' => 'carousel',
										],
									],
								],
								[
									'name' => 'content_position',
									'operator' => '==',
									'value' => 'overlay',
								],
							],
						],
						[
							'name' => 'layout',
							'operator' => '==',
							'value' => 'tiles',
						],
					],
				],
			]
		);
			$this->add_control(
				'cat_content_text_align',
				[
					'label'                 => __( 'Text Alignment', 'powerpack' ),
					'type'                  => Controls_Manager::CHOOSE,
					'label_block'  => false,
					'options'      => [
						'left'   => [
							'title' => __( 'Left', 'powerpack' ),
							'icon'  => 'eicon-text-align-left',
						],
						'center' => [
							'title' => __( 'Center', 'powerpack' ),
							'icon'  => 'eicon-text-align-center',
						],
						'right'  => [
							'title' => __( 'Right', 'powerpack' ),
							'icon'  => 'eicon-text-align-right',
						],
					],
					'default'               => 'center',
					'selectors'             => [
						'{{WRAPPER}} .pp-woo-categories .product .pp-product-cat-content' => 'text-align: {{VALUE}};',
					],
					'separator'             => 'after',
				]
			);

			$this->start_controls_tabs( 'cat_content_tabs_style' );

			$this->start_controls_tab(
				'cat_content_normal',
				[
					'label'                 => __( 'Normal', 'powerpack' ),
				]
			);

			$this->add_group_control(
				Group_Control_Background::get_type(),
				[
					'name'                  => 'cat_content_background',
					'types'                 => [ 'classic', 'gradient' ],
					'selector'              => '{{WRAPPER}} .pp-woo-categories .product .pp-product-cat-content',
				]
			);

			$this->add_control(
				'cat_content_margin',
				[
					'label'                 => __( 'Margin', 'powerpack' ),
					'type'                  => Controls_Manager::DIMENSIONS,
					'size_units'            => [ 'px', 'em', '%' ],
					'selectors'             => [
						'{{WRAPPER}} .pp-woo-categories .product .pp-product-cat-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'separator'             => 'before',
				]
			);

			$this->add_control(
				'cat_content_padding',
				[
					'label'                 => __( 'Padding', 'powerpack' ),
					'type'                  => Controls_Manager::DIMENSIONS,
					'size_units'            => [ 'px', 'em', '%' ],
					'default'               => [
						'top'      => '10',
						'right'    => '10',
						'bottom'   => '10',
						'left'     => '10',
						'isLinked' => true,
					],
					'selectors'             => [
						'{{WRAPPER}} .pp-woo-categories .product .pp-product-cat-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'cat_content_title_heading',
				[
					'label'                 => __( 'Title', 'powerpack' ),
					'type'                  => Controls_Manager::HEADING,
					'separator'             => 'before',
					'condition'             => [
						'cat_title' => 'yes',
					],
				]
			);

			$this->add_control(
				'cat_title_color',
				[
					'label'                 => __( 'Color', 'powerpack' ),
					'type'                  => Controls_Manager::COLOR,
					'selectors'             => [
						'{{WRAPPER}} .pp-woo-categories .product .woocommerce-loop-category__title' => 'color: {{VALUE}};',
					],
					'condition'             => [
						'cat_title' => 'yes',
					],
				]
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name'                 => 'cat_content_title_typography',
					'label'                => __( 'Typography', 'powerpack' ),
					'scheme'               => Scheme_Typography::TYPOGRAPHY_1,
					'selector'             => '{{WRAPPER}} .pp-woo-categories .product .woocommerce-loop-category__title',
					'condition'            => [
						'cat_title' => 'yes',
					],
				]
			);

			$this->add_control(
				'cat_content_count_heading',
				[
					'label'                 => __( 'Product Count', 'powerpack' ),
					'type'                  => Controls_Manager::HEADING,
					'separator'             => 'before',
					'condition'             => [
						'product_count' => 'yes',
					],
				]
			);

			$this->add_control(
				'cat_count_color',
				[
					'label'                 => __( 'Color', 'powerpack' ),
					'type'                  => Controls_Manager::COLOR,
					'selectors'             => [
						'{{WRAPPER}} .pp-woo-categories .product .pp-product-cat-content .pp-count' => 'color: {{VALUE}};',
					],
					'condition'             => [
						'product_count' => 'yes',
					],
				]
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name'                 => 'cat_content_count_typography',
					'label'                => __( 'Typography', 'powerpack' ),
					'scheme'               => Scheme_Typography::TYPOGRAPHY_4,
					'selector'             => '{{WRAPPER}} .pp-woo-categories .product .pp-product-cat-content .pp-count',
					'condition'            => [
						'product_count' => 'yes',
					],
				]
			);

			$this->add_control(
				'cat_desc_heading',
				[
					'label'                 => __( 'Category Description', 'powerpack' ),
					'type'                  => Controls_Manager::HEADING,
					'separator'             => 'before',
					'condition'             => [
						'cat_desc'  => 'yes',
					],
				]
			);

			$this->add_control(
				'cat_desc_color',
				[
					'label'                 => __( 'Color', 'powerpack' ),
					'type'                  => Controls_Manager::COLOR,
					'selectors'             => [
						'{{WRAPPER}} .pp-woo-categories .product .pp-product-cat-content .pp-product-cat-desc' => 'color: {{VALUE}};',
					],
					'condition'             => [
						'cat_desc'  => 'yes',
					],
				]
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name'                 => 'cat_desc_typography',
					'label'                => __( 'Typography', 'powerpack' ),
					'selector'             => '{{WRAPPER}} .pp-woo-categories .product .pp-product-cat-content .pp-product-cat-desc',
					'condition'             => [
						'cat_desc'  => 'yes',
					],
				]
			);

			$this->add_control(
				'cat_content_opacity',
				[
					'label'                 => __( 'Opacity', 'powerpack' ),
					'type'                  => Controls_Manager::SLIDER,
					'default'               => [
						'size' => 1,
					],
					'range'                 => [
						'px' => [
							'min'   => 0,
							'max'   => 1,
							'step'  => 0.01,
						],
					],
					'selectors'             => [
						'{{WRAPPER}} .pp-woo-categories .product .pp-product-cat-content' => 'opacity: {{SIZE}};',
					],
					'separator'             => 'before',
					'condition'             => [
						'content_position' => 'overlay',
					],
				]
			);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'cat_content_hover',
				[
					'label'                 => __( 'Hover', 'powerpack' ),
				]
			);

			$this->add_group_control(
				Group_Control_Background::get_type(),
				[
					'name'                  => 'cat_content_background_hover',
					'types'                 => [ 'classic', 'gradient' ],
					'selector'              => '{{WRAPPER}} .pp-woo-categories .product-category .pp-grid-item:hover .pp-product-cat-content',
					'separator'             => 'after',
				]
			);

			$this->add_control(
				'cat_content_hover_title_color',
				[
					'label'                 => __( 'Title Color', 'powerpack' ),
					'type'                  => Controls_Manager::COLOR,
					'selectors'             => [
						'{{WRAPPER}} .pp-woo-categories .product-category .pp-grid-item:hover .woocommerce-loop-category__title' => 'color: {{VALUE}};',
					],
					'condition'             => [
						'cat_title' => 'yes',
					],
				]
			);

			$this->add_control(
				'cat_content_hover_count_color',
				[
					'label'                 => __( 'Product Count Color', 'powerpack' ),
					'type'                  => Controls_Manager::COLOR,
					'selectors'             => [
						'{{WRAPPER}} .pp-woo-categories .product-category .pp-grid-item:hover .pp-product-cat-content .pp-count' => 'color: {{VALUE}};',
					],
					'condition'             => [
						'product_count' => 'yes',
					],
				]
			);

			$this->add_control(
				'cat_hover_desc_color',
				[
					'label'                 => __( 'Description Color', 'powerpack' ),
					'type'                  => Controls_Manager::COLOR,
					'selectors'             => [
						'{{WRAPPER}} .pp-woo-categories .product-category .pp-grid-item:hover .pp-product-cat-content .pp-product-cat-desc' => 'color: {{VALUE}};',
					],
					'condition'             => [
						'cat_desc'  => 'yes',
					],
				]
			);

			$this->add_control(
				'cat_content_opacity_hover',
				[
					'label'                 => __( 'Opacity', 'powerpack' ),
					'type'                  => Controls_Manager::SLIDER,
					'default'               => [
						'size' => 1,
					],
					'range'                 => [
						'px' => [
							'min'   => 0,
							'max'   => 1,
							'step'  => 0.01,
						],
					],
					'selectors'             => [
						'{{WRAPPER}} .pp-woo-categories .product .pp-grid-item:hover .pp-product-cat-content' => 'opacity: {{SIZE}};',
					],
					'separator'             => 'before',
					'condition'             => [
						'content_position' => 'overlay',
					],
				]
			);

			$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();
	}

	public function register_style_arrows_controls() {
		$this->start_controls_section(
			'section_arrows_style',
			array(
				'label'     => __( 'Arrows', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'layout'   => 'carousel',
					'arrows'   => 'yes',
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
				'condition'          => array(
					'layout'   => 'carousel',
					'arrows'   => 'yes',
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
					'layout'   => 'carousel',
					'arrows'   => 'yes',
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
					'layout'   => 'carousel',
					'arrows'   => 'yes',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_arrows_style' );

		$this->start_controls_tab(
			'tab_arrows_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'layout'   => 'carousel',
					'arrows'   => 'yes',
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
					'layout'   => 'carousel',
					'arrows'   => 'yes',
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
					'layout'   => 'carousel',
					'arrows'   => 'yes',
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
					'layout'   => 'carousel',
					'arrows'   => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'arrows_border_radius_normal',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-slider-arrow' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'layout'   => 'carousel',
					'arrows'   => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_arrows_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'layout'   => 'carousel',
					'arrows'   => 'yes',
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
					'layout'   => 'carousel',
					'arrows'   => 'yes',
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
					'layout'   => 'carousel',
					'arrows'   => 'yes',
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
					'{{WRAPPER}} .pp-slider-arrow:hover' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'layout'   => 'carousel',
					'arrows'   => 'yes',
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
					'layout'   => 'carousel',
					'arrows'   => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	public function register_style_dots_controls() {
		$this->start_controls_section(
			'section_dots_style',
			array(
				'label'     => __( 'Dots', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'layout'    => 'carousel',
					'dots'      => 'yes',
				),
			)
		);

		$this->add_control(
			'dots_position',
			[
				'label'                 => __( 'Position', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'options'               => [
					'inside'     => __( 'Inside', 'powerpack' ),
					'outside'    => __( 'Outside', 'powerpack' ),
				],
				'default'               => 'outside',
				'condition' => array(
					'layout'    => 'carousel',
					'dots'      => 'yes',
				),
			]
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
					'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'layout'    => 'carousel',
					'dots'      => 'yes',
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
					'layout'    => 'carousel',
					'dots'      => 'yes',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_dots_style' );

		$this->start_controls_tab(
			'tab_dots_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'layout'    => 'carousel',
					'dots'      => 'yes',
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
					'layout'    => 'carousel',
					'dots'      => 'yes',
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
					'layout'    => 'carousel',
					'dots'      => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'dots_border_radius_normal',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'layout'    => 'carousel',
					'dots'      => 'yes',
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
					'layout'    => 'carousel',
					'dots'      => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_dots_active',
			array(
				'label'     => __( 'Active', 'powerpack' ),
				'condition' => array(
					'layout'    => 'carousel',
					'dots'      => 'yes',
				),
			)
		);

		$this->add_control(
			'dots_color_active',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'background: {{VALUE}};',
				),
				'condition' => array(
					'layout'    => 'carousel',
					'dots'      => 'yes',
				),
			)
		);

		$this->add_control(
			'dots_border_color_active',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'layout'    => 'carousel',
					'dots'      => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_dots_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'layout'    => 'carousel',
					'dots'      => 'yes',
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
					'layout'    => 'carousel',
					'dots'      => 'yes',
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
					'layout'    => 'carousel',
					'dots'      => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Get WooCommerce Product Categories.
	 *
	 * @since 1.3.3
	 * @access protected
	 */
	protected function get_product_categories() {

		$product_cat = array();

		$cat_args = array(
			'orderby'    => 'name',
			'order'      => 'asc',
			'hide_empty' => false,
		);

		$product_categories = get_terms( 'product_cat', $cat_args );

		if ( ! empty( $product_categories ) ) {

			foreach ( $product_categories as $key => $category ) {

				$product_cat[ $category->term_id ] = $category->name;
			}
		}

		return $product_cat;
	}

	/**
	 * List all product categories.
	 *
	 * @return string
	 */
	public function query_product_categories() {

		$settings    = $this->get_settings();
		$include_ids = array();
		$exclude_ids = array();

		$atts = array(
			'limit'   => ( $settings['cats_count'] ) ? $settings['cats_count'] : '-1',
			'columns' => ( $settings['columns'] ) ? $settings['columns'] : '4',
			'parent'  => '',
		);

		if ( 'top' === $settings['category_filter_rule'] ) {
			$atts['parent'] = 0;
		} elseif ( 'include' === $settings['category_filter_rule'] && is_array( $settings['category_filter'] ) ) {
			$include_ids = array_filter( array_map( 'trim', $settings['category_filter'] ) );
		} elseif ( 'exclude' === $settings['category_filter_rule'] && is_array( $settings['category_filter'] ) ) {
			$exclude_ids = array_filter( array_map( 'trim', $settings['category_filter'] ) );
		}

		$hide_empty = ( 'yes' === $settings['display_empty_cat'] ) ? 0 : 1;

		// Get terms and workaround WP bug with parents/pad counts.
		$args = array(
			'orderby'    => ( $settings['orderby'] ) ? $settings['orderby'] : 'name',
			'order'      => ( $settings['order'] ) ? $settings['order'] : 'ASC',
			'hide_empty' => $hide_empty,
			'pad_counts' => true,
			'child_of'   => $atts['parent'],
			'include'    => $include_ids,
			'exclude'    => $exclude_ids,
		);

		$args = apply_filters( 'ppe_woo_categories_query_args', $args, $settings );

		$product_categories = get_terms( 'product_cat', $args );

		if ( '' !== $atts['parent'] ) {
			$product_categories = wp_list_filter(
				$product_categories, array(
					'parent' => $atts['parent'],
				)
			);
		}

		if ( $hide_empty ) {
			foreach ( $product_categories as $key => $category ) {
				if ( 0 === $category->count ) {
					unset( $product_categories[ $key ] );
				}
			}
		}

		$atts['limit'] = intval( $atts['limit'] );

		if ( $atts['limit'] > 0 ) {
			$product_categories = array_slice( $product_categories, 0, $atts['limit'] );
		}

		$columns = absint( $atts['columns'] );

		wc_set_loop_prop( 'columns', $columns );

		/* Category Image */
		remove_action( 'woocommerce_before_subcategory_title', 'woocommerce_subcategory_thumbnail', 10 );
		add_action( 'woocommerce_before_subcategory_title', array( $this, 'woocommerce_category_thumbnail_size' ), 10 );

		/* Category Link */
		remove_action( 'woocommerce_before_subcategory', 'woocommerce_template_loop_category_link_open', 10 );
		add_action( 'woocommerce_before_subcategory', array( $this, 'template_loop_category_link_open' ), 10 );

		/* Category Wrapper */
		add_action( 'woocommerce_before_subcategory', array( $this, 'category_wrap_start' ), 15 );
		add_action( 'woocommerce_after_subcategory', array( $this, 'category_wrap_end' ), 8 );

		/* Content Wrapper */
		add_action( 'woocommerce_before_subcategory_title', array( $this, 'category_content_start' ), 15 );
		add_action( 'woocommerce_after_subcategory_title', array( $this, 'category_content_end' ), 8 );

		if ( 'yes' === $settings['cat_desc'] ) {
			add_action( 'woocommerce_shop_loop_subcategory_title', array( $this, 'category_description' ), 12 );
		}

		/* Category Title */
		remove_action( 'woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title', 10 );
		add_action( 'woocommerce_shop_loop_subcategory_title', array( $this, 'template_loop_category_title' ), 10 );

		ob_start();

		if ( $product_categories ) {
			$i = 1;
			$products_count = count( $product_categories );

			if ( 'tiles' === $settings['layout'] ) {
				echo '<div class="products">';

				$tiles_template = ( 'tiles' === $settings['layout'] && $settings['tiles_style'] ) ? $settings['tiles_style'] : '1';

				foreach ( $product_categories as $category ) {

					include POWERPACK_ELEMENTS_PATH . 'modules/woocommerce/templates/content-product-cat-tiles-' . $tiles_template . '.php';
					$i++;
				}

				echo '</div>';

				if ( '1' === $tiles_template ) {
					if ( 4 > $products_count ) {
						echo '</div>';
					}
				} elseif ( '2' === $tiles_template ) {
					if ( 3 > $products_count ) {
						echo '</div>';
					}
				}
			} elseif ( 'carousel' === $settings['layout'] ) {
				echo '<div class="products swiper-wrapper">';

				foreach ( $product_categories as $category ) {

					include POWERPACK_ELEMENTS_PATH . 'modules/woocommerce/templates/content-product-cat-carousel.php';
					$i++;
				}

				echo '</div>';
			} else {
				echo '<ul class="elementor-grid columns-' . esc_attr( $settings['columns'] ) . '">';

				foreach ( $product_categories as $category ) {

					include POWERPACK_ELEMENTS_PATH . 'modules/woocommerce/templates/content-product-cat.php';
					$i++;
				}

				echo '</ul>';
			}
		}

		woocommerce_reset_loop();

		$this->add_render_attribute( 'categories-inner', 'class', [
			'pp-woo-categories-inner',
		] );

		if ( 'carousel' === $settings['layout'] ) {
			$this->add_render_attribute(
				'categories-inner', [
					'class' => [
						'pp-swiper-slider',
						'swiper-container',
					],
				]
			);

			if ( is_rtl() ) {
				$this->add_render_attribute( 'categories-inner', 'dir', 'rtl' );
			}
		}

		$inner_content = ob_get_clean();

		/* Category Image */
		remove_action( 'woocommerce_before_subcategory_title', array( $this, 'woocommerce_category_thumbnail_size' ), 10 );
		add_action( 'woocommerce_before_subcategory_title', 'woocommerce_subcategory_thumbnail', 10 );

		/* Category Link */
		add_action( 'woocommerce_before_subcategory', 'woocommerce_template_loop_category_link_open', 10 );
		remove_action( 'woocommerce_before_subcategory', array( $this, 'template_loop_category_link_open' ), 10 );

		/* Category Wrapper */
		remove_action( 'woocommerce_before_subcategory', array( $this, 'category_wrap_start' ), 15 );
		remove_action( 'woocommerce_after_subcategory', array( $this, 'category_wrap_end' ), 8 );

		if ( 'yes' === $settings['cat_desc'] ) {
			remove_action( 'woocommerce_after_subcategory', array( $this, 'category_description' ), 8 );
		}

		/* Category Title */
		remove_action( 'woocommerce_shop_loop_subcategory_title', array( $this, 'template_loop_category_title' ), 10 );
		add_action( 'woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title', 10 );

		return '<div ' . $this->get_render_attribute_string( 'categories-inner' ) . '>' . $inner_content . '</div>';
	}

	/**
	 * Get Product Image size.
	 *
	 * @param object $size Category Image Size.
	 */
	public function woocommerce_category_thumbnail_size( $category ) {
		$settings = $this->get_settings();
		$thumbnail_id = get_term_meta( $category->term_id, 'thumbnail_id', true );
		if ( empty( $thumbnail_id ) ) {
			woocommerce_subcategory_thumbnail( $category );
		} else {
			$post_thumb_url = Group_Control_Image_Size::get_attachment_image_src( $thumbnail_id, 'image_size', $settings );
			printf(
				'<img src="%1$s" alt="%2$s">',
				esc_url( $post_thumb_url ),
				esc_attr( $category->name )
			);
		}
	}

	/**
	 * Wrapper Start.
	 *
	 * @param object $category Category object.
	 */
	public function template_loop_category_link_open( $category ) {
		$link = apply_filters( 'pp_woo_category_link', esc_url( get_term_link( $category, 'product_cat' ) ) );

		echo '<a href="' . esc_url( $link ) . '">';
	}

	/**
	 * Wrapper Start.
	 *
	 * @param object $category Category object.
	 */
	public function category_wrap_start( $category ) {
		echo '<div class="pp-product-cat-inner">';
	}


	/**
	 * Wrapper End.
	 *
	 * @param object $category Category object.
	 */
	public function category_wrap_end( $category ) {
		echo '</div>';
	}

	/**
	 * Content Start.
	 *
	 * @param object $category Category object.
	 */
	public function category_content_start( $category ) {
		echo '<div class="pp-product-cat-content-wrap">';
		echo '<div class="pp-product-cat-content">';
	}


	/**
	 * Content End.
	 *
	 * @param object $category Category object.
	 */
	public function category_content_end( $category ) {
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Category Description.
	 *
	 * @param object $category Category object.
	 */
	public function category_description( $category ) {

		$settings = $this->get_settings();

		if ( $category && ! empty( $category->description ) ) {

			echo '<div class="pp-product-cat-desc">';
			if ( $settings['category_desc_limit'] ) {
				echo '<div class="pp-term-description">' . wp_trim_words( wc_format_content( $category->description ), $settings['category_desc_limit'], '' ) . '</div>';//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				echo '<div class="pp-term-description">' . wc_format_content( $category->description ) . '</div>';//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo '</div>';
		}
	}


	/**
	 * Show the subcategory title in the product loop.
	 *
	 * @param object $category Category object.
	 */
	public function template_loop_category_title( $category ) {

		$settings = $this->get_settings();

		$output         = '<div class="pp-category__title-wrap">';
		$output         .= '<div class="pp-category__title-inner">';
		if ( 'yes' === $settings['cat_title'] ) {
			$output     .= '<h2 class="woocommerce-loop-category__title">';
				$output .= esc_html( $category->name );
			$output     .= '</h2>';
		}

		if ( 'yes' === $settings['product_count'] ) {
			if ( $category->count > 0 ) {
					$output .= sprintf( // WPCS: XSS OK.
						/* translators: 1: number of products */
						_nx( '<mark class="pp-count">%1$s Product</mark>', '<mark class="pp-count">%1$s Products</mark>', $category->count, 'product categories', 'powerpack' ),
						number_format_i18n( $category->count )
					);
			}
		}
		$output .= '</div>';
		$output .= '</div>';

		echo wp_kses_post( $output );
	}

	/**
	 * Set slider attributes.
	 *
	 * @access public
	 */
	public function set_slider_attr() {

		$settings = $this->get_settings();

		if ( 'carousel' !== $settings['layout'] ) {
			return;
		}

		$desktop_show = ( isset( $settings['slides_to_show'] ) && '' !== $settings['slides_to_show'] ) ? absint( $settings['slides_to_show'] ) : 4;
		$tablet_show  = ( isset( $settings['slides_to_show_tablet'] ) && '' !== $settings['slides_to_show_tablet'] ) ? absint( $settings['slides_to_show_tablet'] ) : 3;
		$mobile_show  = ( isset( $settings['slides_to_show_mobile'] ) && '' !== $settings['slides_to_show_mobile'] ) ? absint( $settings['slides_to_show_mobile'] ) : 1;

		$desktop_scroll = ( isset( $settings['slides_to_scroll'] ) && $settings['slides_to_scroll'] ) ? absint( $settings['slides_to_scroll'] ) : 1;
		$tablet_scroll  = ( isset( $settings['slides_to_scroll_tablet'] ) && $settings['slides_to_scroll_tablet'] ) ? absint( $settings['slides_to_scroll_tablet'] ) : 1;
		$mobile_scroll  = ( isset( $settings['slides_to_scroll_mobile'] ) && $settings['slides_to_scroll_mobile'] ) ? absint( $settings['slides_to_scroll_mobile'] ) : 1;

		$desktop_space = ( isset( $settings['column_gap']['size'] ) && $settings['column_gap']['size'] ) ? absint( $settings['column_gap']['size'] ) : 10;
		$tablet_space  = ( isset( $settings['column_gap_tablet']['size'] ) && $settings['column_gap_tablet']['size'] ) ? absint( $settings['column_gap_tablet']['size'] ) : 10;
		$mobile_space  = ( isset( $settings['column_gap_mobile']['size'] ) && $settings['column_gap_mobile']['size'] ) ? absint( $settings['column_gap_mobile']['size'] ) : 10;

		$slider_options = [
			'direction'             => 'horizontal',
			'slidesPerView'         => $desktop_show,
			'slidesPerGroup'        => $desktop_scroll,
			'speed'                 => ( $settings['transition_speed'] ) ? absint( $settings['transition_speed'] ) : 600,
			'spaceBetween'          => ( $settings['column_gap']['size'] ) ? $settings['column_gap']['size'] : 10,
			'loop'                  => ( 'yes' === $settings['infinite'] ),
			'disableOnInteraction'  => ( 'yes' === $settings['pause_on_hover'] ),
			'autoHeight'            => true,
			'watchSlidesVisibility' => true,
			'observer'              => true,
			'observeParents'        => true,
		];

		if ( 'yes' === $settings['autoplay'] && ! empty( $settings['autoplay_speed'] ) ) {
			$autoplay_speed = absint( $settings['autoplay_speed'] );
		} else {
			$autoplay_speed = 999999;
		}

		$slider_options['autoplay'] = array(
			'delay' => $autoplay_speed,
		);

		if ( 'yes' === $settings['arrows'] ) {
			$slider_options['navigation'] = array(
				'nextEl' => '.swiper-button-next-' . esc_attr( $this->get_id() ),
				'prevEl' => '.swiper-button-prev-' . esc_attr( $this->get_id() ),
			);
		}

		if ( 'yes' === $settings['dots'] ) {
			$slider_options['pagination'] = array(
				'el'        => '.swiper-pagination-' . esc_attr( $this->get_id() ),
				'clickable' => true,
			);
		}

		$elementor_bp_lg = get_option( 'elementor_viewport_lg' );
		$elementor_bp_md = get_option( 'elementor_viewport_md' );
		$bp_desktop      = ! empty( $elementor_bp_lg ) ? $elementor_bp_lg : 1025;
		$bp_tablet       = ! empty( $elementor_bp_md ) ? $elementor_bp_md : 768;
		$bp_mobile       = 320;

		$slider_options['breakpoints'] = array(
			$bp_desktop => array(
				'slidesPerView'  => $desktop_show,
				'slidesPerGroup' => $desktop_scroll,
				'spaceBetween'   => $desktop_space,
			),
			$bp_tablet  => array(
				'slidesPerView'  => $tablet_show,
				'slidesPerGroup' => $tablet_scroll,
				'spaceBetween'   => $tablet_space,
			),
			$bp_mobile  => array(
				'slidesPerView'  => $mobile_show,
				'slidesPerGroup' => $mobile_scroll,
				'spaceBetween'   => $mobile_space,
			),
		);

		$this->add_render_attribute(
			'container', [
				'data-cat-carousel-options' => wp_json_encode( $slider_options ),
			]
		);
	}

			/**
	 * Render team member carousel dots output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_dots() {
		$settings = $this->get_settings();

		if ( 'carousel' !== $settings['layout'] ) {
			return;
		}

		if ( 'yes' === $settings['dots'] ) {
			?>
			<!-- Add Pagination -->
			<div class="swiper-pagination swiper-pagination-<?php echo esc_attr( $this->get_id() ); ?>"></div>
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
		$settings = $this->get_settings();

		if ( 'carousel' !== $settings['layout'] ) {
			return;
		}

		$migration_allowed = Icons_Manager::is_migration_allowed();

		if ( ! isset( $settings['arrow'] ) && ! Icons_Manager::is_migration_allowed() ) {
			// add old default.
			$settings['arrow'] = 'fa fa-angle-right';
		}

		$has_icon = ! empty( $settings['arrow'] );

		if ( ! $has_icon && ! empty( $settings['select_arrow']['value'] ) ) {
			$has_icon = true;
		}

		if ( ! empty( $settings['arrow'] ) ) {
			$this->add_render_attribute( 'arrow-icon', 'class', $settings['arrow'] );
			$this->add_render_attribute( 'arrow-icon', 'aria-hidden', 'true' );
		}

		$migrated = isset( $settings['__fa4_migrated']['select_arrow'] );
		$is_new = ! isset( $settings['arrow'] ) && $migration_allowed;

		if ( 'yes' === $settings['arrows'] ) {
			if ( $has_icon ) {
				if ( $is_new || $migrated ) {
					$next_arrow = $settings['select_arrow'];
					$prev_arrow = str_replace( 'right', 'left', $settings['select_arrow'] );
				} else {
					$next_arrow = $settings['arrow'];
					$prev_arrow = str_replace( 'right', 'left', $settings['arrow'] );
				}
			} else {
				$next_arrow = 'fa fa-angle-right';
				$prev_arrow = 'fa fa-angle-left';
			}

			if ( ! empty( $settings['arrow'] ) || ( ! empty( $settings['select_arrow']['value'] ) && $is_new ) ) { ?>
				<div class="pp-slider-arrow elementor-swiper-button-prev swiper-button-prev-<?php echo esc_attr( $this->get_id() ); ?>">
					<?php if ( $is_new || $migrated ) :
						Icons_Manager::render_icon( $prev_arrow, [ 'aria-hidden' => 'true' ] );
					else : ?>
						<i <?php $this->print_render_attribute_string( 'arrow-icon' ); ?>></i>
					<?php endif; ?>
				</div>
				<div class="pp-slider-arrow elementor-swiper-button-next swiper-button-next-<?php echo esc_attr( $this->get_id() ); ?>">
					<?php if ( $is_new || $migrated ) :
						Icons_Manager::render_icon( $next_arrow, [ 'aria-hidden' => 'true' ] );
					else : ?>
						<i <?php $this->print_render_attribute_string( 'arrow-icon' ); ?>></i>
					<?php endif; ?>
				</div>
			<?php }
		}
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

		$this->add_render_attribute( 'container', 'class', [
			'pp-woocommerce',
			'pp-woo-categories',
			'pp-woo-categories-' . $settings['layout'],
		] );

		if ( 'tiles' === $settings['layout'] ) {
			$this->add_render_attribute( 'container', 'class', [
				'pp-woo-categories-overlay',
			] );
		} else {
			$this->add_render_attribute( 'container', 'class', [
				'pp-woo-categories-' . $settings['content_position'],
			] );
		}

		if ( 'carousel' === $settings['layout'] ) {
			$this->add_render_attribute( 'container', 'class', [
				'swiper-container-wrap',
				'swiper',
			] );

			if ( $settings['dots_position'] ) {
				$this->add_render_attribute( 'container', 'class', 'swiper-container-wrap-dots-' . $settings['dots_position'] );
			}
		}

		$this->set_slider_attr();
		?>
		<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'container' ) ); ?>>
			<?php echo wp_kses_post( $this->query_product_categories() ); ?>
			<?php
			if ( 'carousel' === $settings['layout'] ) {
				$this->render_dots();
				$this->render_arrows();
			}
			?>
		</div>
		<?php
	}
}
