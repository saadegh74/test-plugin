<?php
namespace PowerpackElements\Modules\Coupons\Widgets;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Classes\PP_Helper;
use PowerpackElements\Classes\PP_Posts_Helper;
use PowerpackElements\Classes\PP_Config;

// Elementor Classes
use Elementor\Controls_Manager;
use Elementor\Control_Media;
use Elementor\Utils;
use Elementor\Repeater;
use Elementor\Icons_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Image_Size;
use Elementor\Core\Schemes\Typography as Scheme_Typography;
use Elementor\Core\Schemes\Color as Scheme_Color;
use Elementor\Modules\DynamicTags\Module as TagsModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Coupons Widget
 */
class Coupons extends Powerpack_Widget {

	/**
	 * Retrieve coupons widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Coupons' );
	}

	/**
	 * Retrieve coupons widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Coupons' );
	}

	/**
	 * Retrieve coupons widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Coupons' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the Coupons widget belongs to.
	 *
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'Coupons' );
	}

	/**
	 * Retrieve the list of scripts the coupons widget depended on.
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
			'powerpack-pp-posts',
			'powerpack-frontend',
		];
	}

	protected $query         = null;
	protected $query_filters = null;

	/**
	 * Register coupons widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 2.0.3
	 * @access protected
	 */
	protected function register_controls() {
		/* Content Tab */
		$this->register_content_general_controls();
		$this->register_content_coupons_controls();
		$this->register_content_post_query_controls();
		$this->register_filter_section_controls();
		$this->register_content_link_controls();
		$this->register_pagination_controls();
		$this->register_content_carousel_settings_controls();
		$this->register_content_help_docs_controls();

		/* Style Tab */
		$this->register_style_coupon_box_controls();
		$this->register_style_discount_controls();
		$this->register_style_coupon_code_controls();
		$this->register_style_content_controls();
		$this->register_style_button_controls();
		$this->register_style_filter_controls();
		$this->register_style_pagination_controls();
		$this->register_style_arrows_controls();
		$this->register_style_dots_controls();
		$this->register_style_fraction_controls();
	}

	/*-----------------------------------------------------------------------------------*/
	/*	CONTENT TAB
	/*-----------------------------------------------------------------------------------*/

	protected function register_content_general_controls() {
		/**
		 * Content Tab: General
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_general',
			[
				'label'                 => __( 'General', 'powerpack' ),
			]
		);

		$this->add_control(
			'source',
			[
				'label'                 => __( 'Source', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'options'               => [
					'custom'     => __( 'Custom', 'powerpack' ),
					'posts'      => __( 'Posts', 'powerpack' ),
				],
				'default'               => 'custom',
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
				],
				'frontend_available'    => true,
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
				],
				'prefix_class'          => 'elementor-grid%s-',
				'render_type'           => 'template',
				'frontend_available'    => true,
				'conditions'            => [
					'relation' => 'or',
					'terms' => [
						[
							'name' => 'layout',
							'operator' => '==',
							'value' => 'grid',
						],
						[
							'relation' => 'and',
							'terms' => [
								[
									'name' => 'layout',
									'operator' => '==',
									'value' => 'carousel',
								],
								[
									'relation' => 'or',
									'terms' => [
										[
											'name' => 'carousel_effect',
											'operator' => '==',
											'value' => 'slide',
										],
										[
											'name' => 'carousel_effect',
											'operator' => '==',
											'value' => 'coverflow',
										],
									],
								],
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'coupon_style',
			[
				'label'                 => __( 'Coupon Style', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'default'               => 'copy',
				'options'               => [
					'copy'          => __( 'Click to Copy Code', 'powerpack' ),
					'reveal'        => __( 'Click to Reveal Code and Copy', 'powerpack' ),
					'no-code'       => __( 'No Code Needed', 'powerpack' ),
				],
				'frontend_available'    => true,
			]
		);

		$this->add_control(
			'coupon_reveal',
			[
				'label'                 => __( 'Reveal Text', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( 'Click to Reveal Coupon Code', 'powerpack' ),
				'condition'             => [
					'coupon_style'      => 'reveal',
				],
			]
		);

		$this->add_control(
			'no_code_need',
			[
				'label'                 => __( 'No Code Text', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( 'No Code Needed', 'powerpack' ),
				'condition'             => [
					'coupon_style'      => 'no-code',
				],
			]
		);

		$this->add_control(
			'icon_type',
			[
				'label'                 => esc_html__( 'Coupon Icon', 'powerpack' ),
				'type'                  => Controls_Manager::CHOOSE,
				'label_block'           => false,
				'toggle'                => false,
				'options'               => [
					'none' => [
						'title' => esc_html__( 'None', 'powerpack' ),
						'icon' => 'eicon-ban',
					],
					'icon' => [
						'title' => esc_html__( 'Icon', 'powerpack' ),
						'icon' => 'eicon-star',
					],
					'image' => [
						'title' => esc_html__( 'Image', 'powerpack' ),
						'icon' => 'eicon-image-bold',
					],
				],
				'default'               => 'icon',
			]
		);

		$this->add_control(
			'icon',
			[
				'label'                 => __( 'Choose Icon', 'powerpack' ),
				'type'                  => Controls_Manager::ICONS,
				'label_block'           => true,
				'default'               => [
					'value'     => 'fas fa-check',
					'library'   => 'fa-solid',
				],
				'condition'             => [
					'icon_type'     => 'icon',
				],
			]
		);

		$this->add_control(
			'icon_image',
			[
				'label'                 => __( 'Choose Image', 'powerpack' ),
				'type'                  => Controls_Manager::MEDIA,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => [
					'url' => Utils::get_placeholder_image_src(),
				],
				'condition'             => [
					'icon_type' => 'image',
				],
			]
		);

		$this->add_control(
			'show_discount',
			[
				'label'                 => __( 'Show Discount', 'powerpack' ),
				'type'                  => Controls_Manager::SWITCHER,
				'default'               => 'yes',
				'label_on'              => __( 'Yes', 'powerpack' ),
				'label_off'             => __( 'No', 'powerpack' ),
				'return_value'          => 'yes',
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'                  => 'image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `thumbnail_size` and `thumbnail_custom_dimension`.,
				'label'                 => __( 'Image Size', 'powerpack' ),
				'default'               => 'full',
				'separator'             => 'before',
			]
		);

		$this->add_control(
			'title_html_tag',
			[
				'label'                 => __( 'Title HTML Tag', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'default'               => 'h4',
				'options'               => [
					'h1'     => __( 'H1', 'powerpack' ),
					'h2'     => __( 'H2', 'powerpack' ),
					'h3'     => __( 'H3', 'powerpack' ),
					'h4'     => __( 'H4', 'powerpack' ),
					'h5'     => __( 'H5', 'powerpack' ),
					'h6'     => __( 'H6', 'powerpack' ),
					'div'    => __( 'div', 'powerpack' ),
					'span'   => __( 'span', 'powerpack' ),
					'p'      => __( 'p', 'powerpack' ),
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_coupons_controls() {
		/**
		 * Content Tab: Coupons
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_coupons',
			[
				'label'                     => __( 'Coupons', 'powerpack' ),
			]
		);

		$repeater = new Repeater();

		$repeater->start_controls_tabs( 'items_repeater' );

		$repeater->start_controls_tab( 'tab_icon', [ 'label' => __( 'General', 'powerpack' ) ] );

			$repeater->add_control(
				'image',
				[
					'label'                 => __( 'Image', 'powerpack' ),
					'type'                  => Controls_Manager::MEDIA,
					'dynamic'               => [
						'active'   => true,
					],
					'default'               => [
						'url' => Utils::get_placeholder_image_src(),
					],
				]
			);

			$repeater->add_control(
				'discount',
				[
					'label'                 => __( 'Discount', 'powerpack' ),
					'type'                  => Controls_Manager::TEXT,
					'dynamic'               => [
						'active'   => true,
					],
					'default'               => '10% OFF',
				]
			);

			$repeater->add_control(
				'coupon_code',
				[
					'label'                 => __( 'Coupon Code', 'powerpack' ),
					'type'                  => Controls_Manager::TEXT,
					'dynamic'               => [
						'active'   => true,
					],
					'default'               => 'ABCDEF',
				]
			);

		$repeater->end_controls_tab();

		$repeater->start_controls_tab( 'tab_content', [ 'label' => __( 'Content', 'powerpack' ) ] );

			$repeater->add_control(
				'title',
				[
					'label'                 => __( 'Title', 'powerpack' ),
					'type'                  => Controls_Manager::TEXT,
					'dynamic'               => [
						'active'   => true,
					],
					'default'               => __( 'Title', 'powerpack' ),
				]
			);

			$repeater->add_control(
				'description',
				[
					'label'                 => __( 'Description', 'powerpack' ),
					'type'                  => Controls_Manager::WYSIWYG,
					'dynamic'               => [
						'active'   => true,
					],
					'default'               => __( 'Enter coupons description', 'powerpack' ),
				]
			);

		$repeater->end_controls_tab();

		$repeater->start_controls_tab( 'tab_link', [ 'label' => __( 'Link', 'powerpack' ) ] );

		$repeater->add_control(
			'link',
			[
				'label'                 => __( 'Link', 'powerpack' ),
				'type'                  => Controls_Manager::URL,
				'dynamic'               => [
					'active'   => true,
				],
				'placeholder'           => 'https://www.your-link.com',
				'default'               => [
					'url' => '#',
				],
			]
		);

		$repeater->end_controls_tab();

		$repeater->end_controls_tabs();

		$this->add_control(
			'pp_coupons',
			[
				'label'     => '',
				'type'      => Controls_Manager::REPEATER,
				'default'   => [
					[
						'title' => __( 'Coupon 1', 'powerpack' ),
					],
					[
						'title' => __( 'Coupon 2', 'powerpack' ),
					],
					[
						'title' => __( 'Coupon 3', 'powerpack' ),
					],
				],
				'fields'        => $repeater->get_controls(),
				'title_field'   => '{{{ title }}}',
				'condition'             => [
					'source'    => 'custom',
				],
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label'                 => __( 'Coupons Count', 'powerpack' ),
				'type'                  => Controls_Manager::NUMBER,
				'default'               => 6,
				'condition'             => [
					'source'    => 'posts',
				],
			]
		);

		$this->add_control(
			'posts_content_type',
			[
				'label'                 => __( 'Content Type', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'default'               => 'excerpt',
				'label_block'           => true,
				'options'               => [
					'excerpt'       => __( 'Excerpt', 'powerpack' ),
					'full_content'  => __( 'Full Content', 'powerpack' ),
				],
				'condition'             => [
					'source'    => 'posts',
				],
			]
		);

		$this->add_control(
			'excerpt_length',
			[
				'label'             => __( 'Excerpt Length', 'powerpack' ),
				'type'              => Controls_Manager::NUMBER,
				'default'           => 50,
				'min'               => 0,
				'max'               => 58,
				'step'              => 1,
				'condition'         => [
					'source'                => 'posts',
					'posts_content_type'    => 'excerpt',
				],
			]
		);

		$this->add_control(
			'coupon_custom_field',
			[
				'label'                 => __( 'Coupon Custom Field', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'label_block'           => true,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => '',
				'condition'             => [
					'source'    => 'posts',
				],
			]
		);

		$this->add_control(
			'discount_custom_field',
			[
				'label'                 => __( 'Discount Custom Field', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'label_block'           => true,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => '',
				'condition'             => [
					'source'    => 'posts',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_post_query_controls() {
		/**
		 * Content Tab: Query
		 */
		$this->start_controls_section(
			'section_post_query',
			[
				'label'                 => __( 'Query', 'powerpack' ),
				'condition'             => [
					'source'    => 'posts',
				],
			]
		);

		$this->add_control(
			'post_type',
			[
				'label'                 => __( 'Post Type', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'options'               => PP_Posts_Helper::get_post_types(),
				'default'               => 'post',

			]
		);

		$post_types = PP_Posts_Helper::get_post_types();

		foreach ( $post_types as $post_type_slug => $post_type_label ) {

			$taxonomy = PP_Posts_Helper::get_post_taxonomies( $post_type_slug );

			if ( ! empty( $taxonomy ) ) {

				foreach ( $taxonomy as $index => $tax ) {

					$terms = PP_Posts_Helper::get_tax_terms( $index );

					$tax_terms = array();

					if ( ! empty( $terms ) ) {

						foreach ( $terms as $term_index => $term_obj ) {

							$tax_terms[ $term_obj->term_id ] = $term_obj->name;
						}

						if ( 'post' === $post_type_slug ) {
							if ( 'post_tag' === $index ) {
								$tax_control_key = 'tags';
							} elseif ( 'category' === $index ) {
								$tax_control_key = 'categories';
							} else {
								$tax_control_key = $index . '_' . $post_type_slug;
							}
						} else {
							$tax_control_key = $index . '_' . $post_type_slug;
						}

						// Taxonomy filter type
						$this->add_control(
							$index . '_' . $post_type_slug . '_filter_type',
							[
								/* translators: %s Label */
								'label'       => sprintf( __( '%s Filter Type', 'powerpack' ), $tax->label ),
								'type'        => Controls_Manager::SELECT,
								'default'     => 'IN',
								'label_block' => true,
								'options'     => [
									/* translators: %s label */
									'IN'     => sprintf( __( 'Include %s', 'powerpack' ), $tax->label ),
									/* translators: %s label */
									'NOT IN' => sprintf( __( 'Exclude %s', 'powerpack' ), $tax->label ),
								],
								'separator'         => 'before',
								'condition'   => [
									'post_type' => $post_type_slug,
								],
							]
						);

						$this->add_control(
							$tax_control_key,
							[
								'label'         => $tax->label,
								'type'          => 'pp-query',
								'post_type'     => $post_type_slug,
								'options'       => [],
								'label_block'   => true,
								'multiple'      => true,
								'query_type'    => 'terms',
								'object_type'   => $index,
								'include_type'  => true,
								'condition'   => [
									'post_type' => $post_type_slug,
								],
							]
						);

					}
				}
			}
		}

		$this->add_control(
			'author_filter_type',
			[
				'label'       => __( 'Authors Filter Type', 'powerpack' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'author__in',
				'label_block' => true,
				'separator'         => 'before',
				'options'     => [
					'author__in'     => __( 'Include Authors', 'powerpack' ),
					'author__not_in' => __( 'Exclude Authors', 'powerpack' ),
				],
			]
		);

		$this->add_control(
			'authors',
			[
				'label'                 => __( 'Authors', 'powerpack' ),
				'type'                  => 'pp-query',
				'label_block'           => true,
				'multiple'              => true,
				'query_type'            => 'authors',
				'condition'         => [
					'post_type!' => 'related',
				],
			]
		);

		foreach ( $post_types as $post_type_slug => $post_type_label ) {

			if ( 'post' === $post_type_slug ) {
				$posts_control_key = 'exclude_posts';
			} else {
				$posts_control_key = $post_type_slug . '_filter';
			}

			$this->add_control(
				$post_type_slug . '_filter_type',
				[
					'label'             => sprintf( __( '%s Filter Type', 'powerpack' ), $post_type_label ),
					'type'              => Controls_Manager::SELECT,
					'default'           => 'post__not_in',
					'label_block'       => true,
					'separator'         => 'before',
					'options'           => [
						'post__in'     => sprintf( __( 'Include %s', 'powerpack' ), $post_type_label ),
						'post__not_in' => sprintf( __( 'Exclude %s', 'powerpack' ), $post_type_label ),
					],
					'condition'   => [
						'post_type' => $post_type_slug,
					],
				]
			);

			$this->add_control(
				$posts_control_key,
				[
					/* translators: %s Label */
					'label'             => $post_type_label,
					'type'              => 'pp-query',
					'default'           => '',
					'multiple'          => true,
					'label_block'       => true,
					'query_type'        => 'posts',
					'object_type'       => $post_type_slug,
					'condition'         => [
						'post_type' => $post_type_slug,
					],
				]
			);
		}

		$this->add_control(
			'select_date',
			[
				'label'             => __( 'Date', 'powerpack' ),
				'type'              => Controls_Manager::SELECT,
				'options'           => [
					'anytime'   => __( 'All', 'powerpack' ),
					'today'     => __( 'Past Day', 'powerpack' ),
					'week'      => __( 'Past Week', 'powerpack' ),
					'month'     => __( 'Past Month', 'powerpack' ),
					'quarter'   => __( 'Past Quarter', 'powerpack' ),
					'year'      => __( 'Past Year', 'powerpack' ),
					'exact'     => __( 'Custom', 'powerpack' ),
				],
				'default'           => 'anytime',
				'label_block'       => false,
				'multiple'          => false,
				'separator'         => 'before',
			]
		);

		$this->add_control(
			'date_before',
			[
				'label'             => __( 'Before', 'powerpack' ),
				'description'       => __( 'Setting a ‘Before’ date will show all the posts published until the chosen date (inclusive).', 'powerpack' ),
				'type'              => Controls_Manager::DATE_TIME,
				'label_block'       => false,
				'multiple'          => false,
				'placeholder'       => __( 'Choose', 'powerpack' ),
				'condition'         => [
					'select_date' => 'exact',
				],
			]
		);

		$this->add_control(
			'date_after',
			[
				'label'             => __( 'After', 'powerpack' ),
				'description'       => __( 'Setting an ‘After’ date will show all the posts published since the chosen date (inclusive).', 'powerpack' ),
				'type'              => Controls_Manager::DATE_TIME,
				'label_block'       => false,
				'multiple'          => false,
				'placeholder'       => __( 'Choose', 'powerpack' ),
				'condition'         => [
					'select_date' => 'exact',
				],
			]
		);

		$this->add_control(
			'order',
			[
				'label'             => __( 'Order', 'powerpack' ),
				'type'              => Controls_Manager::SELECT,
				'options'           => [
					'DESC'           => __( 'Descending', 'powerpack' ),
					'ASC'       => __( 'Ascending', 'powerpack' ),
				],
				'default'           => 'DESC',
				'separator'         => 'before',
			]
		);

		$this->add_control(
			'orderby',
			[
				'label'             => __( 'Order By', 'powerpack' ),
				'type'              => Controls_Manager::SELECT,
				'options'           => [
					'date'           => __( 'Date', 'powerpack' ),
					'modified'       => __( 'Last Modified Date', 'powerpack' ),
					'rand'           => __( 'Random', 'powerpack' ),
					'comment_count'  => __( 'Comment Count', 'powerpack' ),
					'title'          => __( 'Title', 'powerpack' ),
					'ID'             => __( 'Post ID', 'powerpack' ),
					'author'         => __( 'Post Author', 'powerpack' ),
				],
				'default'           => 'date',
			]
		);

		$this->add_control(
			'sticky_posts',
			[
				'label'             => __( 'Sticky Posts', 'powerpack' ),
				'type'              => Controls_Manager::SWITCHER,
				'default'           => '',
				'label_on'          => __( 'Yes', 'powerpack' ),
				'label_off'         => __( 'No', 'powerpack' ),
				'return_value'      => 'yes',
				'separator'         => 'before',
			]
		);

		$this->add_control(
			'all_sticky_posts',
			[
				'label'             => __( 'Show Only Sticky Posts', 'powerpack' ),
				'type'              => Controls_Manager::SWITCHER,
				'default'           => '',
				'label_on'          => __( 'Yes', 'powerpack' ),
				'label_off'         => __( 'No', 'powerpack' ),
				'return_value'      => 'yes',
				'condition'         => [
					'sticky_posts' => 'yes',
				],
			]
		);

		$this->add_control(
			'offset',
			[
				'label'             => __( 'Offset', 'powerpack' ),
				'description'       => __( 'Use this setting to skip this number of initial posts', 'powerpack' ),
				'type'              => Controls_Manager::NUMBER,
				'default'           => '',
				'separator'         => 'before',
			]
		);

		$this->add_control(
			'query_id',
			array(
				'label'       => __( 'Query ID', 'powerpack' ),
				'description' => __( 'Give your Query a custom unique id to allow server side filtering', 'powerpack' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'separator'   => 'before',
			)
		);

		$this->end_controls_section();
	}

	public function register_filter_section_controls() {

		$this->start_controls_section(
			'section_filters',
			array(
				'label'      => __( 'Filters', 'powerpack' ),
				'tab'        => Controls_Manager::TAB_CONTENT,
				'condition'  => array(
					'source'     => 'posts',
					'layout!'    => 'carousel',
					'post_type!' => 'related',
				),
			)
		);

		$this->add_control(
			'show_filters',
			array(
				'label'        => __( 'Show Filters', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => array(
					'source'     => 'posts',
					'layout!'    => 'carousel',
					'post_type!' => 'related',
				),
			)
		);

		$post_types = PP_Posts_Helper::get_post_types();

		foreach ( $post_types as $post_type_slug => $post_type_label ) {

			$taxonomy = PP_Posts_Helper::get_post_taxonomies( $post_type_slug );

			if ( ! empty( $taxonomy ) ) {

				$related_tax = array();

				// Get all taxonomy values under the taxonomy.
				foreach ( $taxonomy as $index => $tax ) {

					$terms = get_terms( $index );

					$related_tax[ $index ] = $tax->label;
				}

				// Add control for all taxonomies.
				$this->add_control(
					'tax_' . $post_type_slug . '_filter',
					array(
						'label'       => __( 'Filter By', 'powerpack' ),
						'type'        => Controls_Manager::SELECT2,
						'options'     => $related_tax,
						'multiple'    => true,
						'label_block' => true,
						'default'     => array_keys( $related_tax )[0],
						'condition'   => array(
							'source'       => 'posts',
							'show_filters' => 'yes',
							'layout!'      => 'carousel',
							'post_type'    => $post_type_slug,
						),
					)
				);
			}
		}

		$this->add_control(
			'filters_orderby',
			array(
				'label'      => __( 'Order By', 'powerpack' ),
				'type'       => Controls_Manager::SELECT,
				'default'    => 'name',
				'options'    => array(
					'name'        => __( 'Name', 'powerpack' ),
					'term_id'     => __( 'Term ID', 'powerpack' ),
					'count'       => __( 'Post Count', 'powerpack' ),
					'slug'        => __( 'Slug', 'powerpack' ),
					'description' => __( 'Description', 'powerpack' ),
					'parent'      => __( 'Term Parent', 'powerpack' ),
					'menu_order'  => __( 'Menu Order', 'powerpack' ),
				),
				'condition'  => array(
					'source'       => 'posts',
					'show_filters' => 'yes',
					'layout!'      => 'carousel',
				),
			)
		);

		$this->add_control(
			'filters_order',
			array(
				'label'      => __( 'Order', 'powerpack' ),
				'type'       => Controls_Manager::SELECT,
				'default'    => 'ASC',
				'options'    => array(
					'ASC'  => __( 'Ascending', 'powerpack' ),
					'DESC' => __( 'Descending', 'powerpack' ),
				),
				'condition'  => array(
					'source'       => 'posts',
					'show_filters' => 'yes',
					'layout!'      => 'carousel',
				),
			)
		);

		$this->add_control(
			'filter_all_label',
			array(
				'label'      => __( '"All" Filter Label', 'powerpack' ),
				'type'       => Controls_Manager::TEXT,
				'default'    => __( 'All', 'powerpack' ),
				'condition'  => array(
					'source'       => 'posts',
					'show_filters' => 'yes',
					'layout!'      => 'carousel',
				),
			)
		);

		$this->add_control(
			'enable_active_filter',
			array(
				'label'        => __( 'Default Active Filter', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => array(
					'source'       => 'posts',
					'show_filters' => 'yes',
					'layout!'      => 'carousel',
				),
			)
		);

		// Active filter
		$this->add_control(
			'filter_active',
			array(
				'label'        => __( 'Active Filter', 'powerpack' ),
				'type'         => 'pp-query',
				'post_type'    => '',
				'options'      => array(),
				'label_block'  => true,
				'multiple'     => false,
				'query_type'   => 'terms',
				'object_type'  => '',
				'include_type' => true,
				'condition'   => array(
					'source'               => 'posts',
					'show_filters'         => 'yes',
					'enable_active_filter' => 'yes',
					'layout!'              => 'carousel',
				),
			)
		);

		$this->add_control(
			'show_filters_count',
			array(
				'label'        => __( 'Show Post Count', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => array(
					'source'       => 'posts',
					'show_filters' => 'yes',
					'layout!'      => 'carousel',
				),
			)
		);

		$this->add_control(
			'responsive_support',
			array(
				'label'   => __( 'Dropdown Filters', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'no',
				'options' => array(
					'no'      => __( 'No', 'powerpack' ),
					'desktop' => __( 'For All Devices', 'powerpack' ),
					'tablet'  => __( 'For Tablet & Mobile', 'powerpack' ),
					'mobile'  => __( 'For Mobile Only', 'powerpack' ),
				),
				'condition'   => array(
					'source'               => 'posts',
					'show_filters'         => 'yes',
					'layout!'              => 'carousel',
				),
			)
		);

		$this->add_responsive_control(
			'filter_alignment',
			array(
				'label'       => __( 'Alignment', 'powerpack' ),
				'label_block' => false,
				'type'        => Controls_Manager::CHOOSE,
				'default'     => 'left',
				'options'     => array(
					'left'   => array(
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'eicon-h-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'selectors'   => array(
					'{{WRAPPER}} .pp-post-filters, {{WRAPPER}} .pp-post-filters-dropdown' => 'text-align: {{VALUE}};',
				),
				'condition'    => array(
					'source'       => 'posts',
					'show_filters' => 'yes',
					'layout!'      => 'carousel',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function register_content_link_controls() {
		/**
		 * Content Tab: Links
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_link',
			[
				'label'                 => __( 'Link', 'powerpack' ),
			]
		);

		$this->add_control(
			'link_type',
			[
				'label'                 => __( 'Link Type', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'default'               => 'button',
				'options'               => [
					'none'      => __( 'None', 'powerpack' ),
					'box'       => __( 'Box', 'powerpack' ),
					'title'     => __( 'Title', 'powerpack' ),
					'button'    => __( 'Button', 'powerpack' ),
				],
			]
		);

		$this->add_control(
			'button_text',
			[
				'label'                 => __( 'Button Text', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( 'View This Deal', 'powerpack' ),
				'condition'             => [
					'link_type'   => 'button',
				],
			]
		);

		$this->add_control(
			'button_icon',
			[
				'label'                 => __( 'Button Icon', 'powerpack' ),
				'type'                  => Controls_Manager::ICONS,
				'label_block'           => true,
				'default'               => [
					'value'     => 'fas fa-long-arrow-alt-right',
					'library'   => 'fa-solid',
				],
				'condition'             => [
					'link_type'   => 'button',
				],
			]
		);

		$this->add_control(
			'button_icon_position',
			[
				'label'                 => __( 'Icon Position', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'default'               => 'after',
				'options'               => [
					'before'    => __( 'Before', 'powerpack' ),
					'after'     => __( 'After', 'powerpack' ),
				],
				'prefix_class'          => 'pp-coupon-button-icon-',
				'render_type'           => 'template',
				'condition'             => [
					'link_type'     => 'button',
				],
			]
		);

		$this->add_control(
			'button_separator',
			[
				'label'                 => __( 'Separator', 'powerpack' ),
				'type'                  => Controls_Manager::SWITCHER,
				'default'               => 'yes',
				'label_on'              => __( 'Yes', 'powerpack' ),
				'label_off'             => __( 'No', 'powerpack' ),
				'return_value'          => 'yes',
				'condition'             => [
					'link_type'     => 'button',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_pagination_controls() {
		$this->start_controls_section(
			'section_pagination',
			array(
				'label'     => __( 'Pagination', 'powerpack' ),
				'condition' => array(
					'source'  => 'posts',
					'layout!' => 'carousel',
				),
			)
		);

		$this->add_control(
			'coupons_pagination_type',
			array(
				'label'     => __( 'Pagination', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'none',
				'options'   => array(
					'none'                  => __( 'None', 'powerpack' ),
					'numbers'               => __( 'Numbers', 'powerpack' ),
					'numbers_and_prev_next' => __( 'Numbers', 'powerpack' ) . ' + ' . __( 'Previous/Next', 'powerpack' ),
					'load_more'             => __( 'Load More Button', 'powerpack' ),
					'infinite'              => __( 'Infinite', 'powerpack' ),
				),
				'condition' => array(
					'source'  => 'posts',
					'layout!' => 'carousel',
				),
			)
		);

		$this->add_control(
			'pagination_note',
			array(
				'label'             => '',
				'type'              => Controls_Manager::RAW_HTML,
				'raw'               => __( 'Load more and Infinite pagination does not work with Main Query.', 'powerpack' ),
				'content_classes'   => 'pp-editor-info',
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => [ 'load_more', 'infinite' ],
					'query_type'      => 'main',
				),
			)
		);

		$this->add_control(
			'pagination_ajax',
			array(
				'label'     => __( 'Ajax Pagination', 'powerpack' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array(
					'show_filters!'   => 'yes',
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array(
						'numbers',
						'numbers_and_prev_next',
					),
				),
			)
		);

		$this->add_control(
			'pagination_page_limit',
			array(
				'label'     => __( 'Page Limit', 'powerpack' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 5,
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array(
						'numbers',
						'numbers_and_prev_next',
					),
				),
			)
		);

		$this->add_control(
			'pagination_numbers_shorten',
			array(
				'label'     => __( 'Shorten', 'powerpack' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => '',
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array(
						'numbers',
						'numbers_and_prev_next',
					),
				),
			)
		);

		$this->add_control(
			'pagination_load_more_label',
			array(
				'label'     => __( 'Button Label', 'powerpack' ),
				'default'   => __( 'Load More', 'powerpack' ),
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => 'load_more',
				),
			)
		);

		$this->add_control(
			'pagination_load_more_button_icon',
			array(
				'label'       => __( 'Button Icon', 'powerpack' ),
				'type'        => Controls_Manager::ICONS,
				'label_block' => false,
				'skin'        => 'inline',
				'condition'   => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => 'load_more',
				),
			)
		);

		$this->add_control(
			'pagination_load_more_button_icon_position',
			array(
				'label'     => __( 'Icon Position', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'after',
				'options'   => array(
					'after'  => __( 'After', 'powerpack' ),
					'before' => __( 'Before', 'powerpack' ),
				),
				'condition' => array(
					'source'                            => 'posts',
					'layout!'                           => 'carousel',
					'coupons_pagination_type'           => 'load_more',
					'pagination_load_more_button_icon[value]!' => '',
				),
			)
		);

		$this->add_control(
			'pagination_prev_label',
			array(
				'label'     => __( 'Previous Label', 'powerpack' ),
				'default'   => __( '&laquo; Previous', 'powerpack' ),
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => 'numbers_and_prev_next',
				),
			)
		);

		$this->add_control(
			'pagination_next_label',
			array(
				'label'     => __( 'Next Label', 'powerpack' ),
				'default'   => __( 'Next &raquo;', 'powerpack' ),
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => 'numbers_and_prev_next',
				),
			)
		);

		$this->add_control(
			'pagination_align',
			array(
				'label'     => __( 'Alignment', 'powerpack' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
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
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .pp-posts-pagination' => 'text-align: {{VALUE}};',
				),
				'condition' => array(
					'source'           => 'posts',
					'layout!'          => 'carousel',
					'pagination_type!' => 'none',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function register_content_carousel_settings_controls() {
		/**
		 * Content Tab: Carousel Settings
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_carousel_settings',
			[
				'label'                 => __( 'Carousel Settings', 'powerpack' ),
				'condition'             => [
					'layout'    => 'carousel',
				],
			]
		);

		$this->add_control(
			'carousel_effect',
			[
				'label'                 => __( 'Effect', 'powerpack' ),
				'description'           => '',
				'type'                  => Controls_Manager::SELECT,
				'default'               => 'slide',
				'options'               => [
					'slide'     => __( 'Slide', 'powerpack' ),
					'cube'      => __( 'Cube', 'powerpack' ),
					'coverflow' => __( 'Coverflow', 'powerpack' ),
					'flip'      => __( 'Flip', 'powerpack' ),
				],
				'condition'             => [
					'layout'    => 'carousel',
				],
			]
		);

		$this->add_control(
			'slider_speed',
			[
				'label'                 => __( 'Slider Speed', 'powerpack' ),
				'description'           => __( 'Duration of transition between slides (in ms)', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'default'               => [ 'size' => 600 ],
				'range'                 => [
					'px' => [
						'min'   => 100,
						'max'   => 3000,
						'step'  => 1,
					],
				],
				'size_units'            => '',
				'separator'             => 'before',
				'condition'             => [
					'layout'    => 'carousel',
				],
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label'                 => __( 'Autoplay', 'powerpack' ),
				'type'                  => Controls_Manager::SWITCHER,
				'default'               => 'yes',
				'label_on'          => __( 'Yes', 'powerpack' ),
				'label_off'         => __( 'No', 'powerpack' ),
				'return_value'      => 'yes',
				'separator'             => 'before',
				'condition'             => [
					'layout'    => 'carousel',
				],
			]
		);

		$this->add_control(
			'autoplay_speed',
			[
				'label'                 => __( 'Autoplay Speed', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'default'               => [ 'size' => 2400 ],
				'range'                 => [
					'px' => [
						'min'   => 500,
						'max'   => 5000,
						'step'  => 1,
					],
				],
				'size_units'            => '',
				'condition'             => [
					'layout'    => 'carousel',
					'autoplay'  => 'yes',
				],
			]
		);

		$this->add_control(
			'infinite_loop',
			[
				'label'                 => __( 'Infinite Loop', 'powerpack' ),
				'description'           => '',
				'type'                  => Controls_Manager::SWITCHER,
				'default'               => 'yes',
				'label_on'          => __( 'Yes', 'powerpack' ),
				'label_off'         => __( 'No', 'powerpack' ),
				'return_value'      => 'yes',
				'condition'             => [
					'layout'    => 'carousel',
				],
			]
		);

		$this->add_control(
			'pause_on_hover',
			[
				'label'                 => __( 'Pause on Hover', 'powerpack' ),
				'description'           => '',
				'type'                  => Controls_Manager::SWITCHER,
				'default'               => 'yes',
				'label_on'              => __( 'Yes', 'powerpack' ),
				'label_off'             => __( 'No', 'powerpack' ),
				'return_value'          => 'yes',
				'frontend_available'    => true,
				'condition'             => [
					'layout'    => 'carousel',
					'autoplay'  => 'yes',
				],
			]
		);

		$this->add_control(
			'grab_cursor',
			[
				'label'                 => __( 'Grab Cursor', 'powerpack' ),
				'description'           => __( 'Shows grab cursor when you hover over the slider', 'powerpack' ),
				'type'                  => Controls_Manager::SWITCHER,
				'default'               => '',
				'label_on'              => __( 'Show', 'powerpack' ),
				'label_off'             => __( 'Hide', 'powerpack' ),
				'return_value'          => 'yes',
				'separator'             => 'before',
				'condition'             => [
					'layout'    => 'carousel',
				],
			]
		);

		$this->add_control(
			'navigation_heading',
			[
				'label'                 => __( 'Navigation', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
				'separator'             => 'before',
				'condition'             => [
					'layout'    => 'carousel',
				],
			]
		);

		$this->add_control(
			'arrows',
			[
				'label'                 => __( 'Arrows', 'powerpack' ),
				'type'                  => Controls_Manager::SWITCHER,
				'default'               => 'yes',
				'label_on'          => __( 'Yes', 'powerpack' ),
				'label_off'         => __( 'No', 'powerpack' ),
				'return_value'      => 'yes',
				'condition'             => [
					'layout'    => 'carousel',
				],
			]
		);

		$this->add_control(
			'dots',
			[
				'label'                 => __( 'Pagination', 'powerpack' ),
				'type'                  => Controls_Manager::SWITCHER,
				'default'               => 'yes',
				'label_on'          => __( 'Yes', 'powerpack' ),
				'label_off'         => __( 'No', 'powerpack' ),
				'return_value'      => 'yes',
				'condition'             => [
					'layout'    => 'carousel',
				],
			]
		);

		$this->add_control(
			'pagination_type',
			[
				'label'                 => __( 'Pagination Type', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'default'               => 'bullets',
				'options'               => [
					'bullets'       => __( 'Dots', 'powerpack' ),
					'fraction'      => __( 'Fraction', 'powerpack' ),
				],
				'condition'             => [
					'layout'    => 'carousel',
					'dots'      => 'yes',
				],
			]
		);

		$this->add_control(
			'direction',
			[
				'label'                 => __( 'Direction', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'default'               => 'left',
				'options'               => [
					'auto'       => __( 'Auto', 'powerpack' ),
					'left'       => __( 'Left', 'powerpack' ),
					'right'      => __( 'Right', 'powerpack' ),
				],
				'separator'             => 'before',
				'condition'             => [
					'layout'    => 'carousel',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_help_docs_controls() {

		$help_docs = PP_Config::get_widget_help_links( 'Coupons' );

		if ( ! empty( $help_docs ) ) {

			/**
			 * Content Tab: Docs Links
			 *
			 * @since 1.4.8
			 * @access protected
			 */
			$this->start_controls_section(
				'section_help_docs',
				[
					'label' => __( 'Help Docs', 'powerpack' ),
				]
			);

			$hd_counter = 1;
			foreach ( $help_docs as $hd_title => $hd_link ) {
				$this->add_control(
					'help_doc_' . $hd_counter,
					[
						'type'            => Controls_Manager::RAW_HTML,
						'raw'             => sprintf( '%1$s ' . $hd_title . ' %2$s', '<a href="' . $hd_link . '" target="_blank" rel="noopener">', '</a>' ),
						'content_classes' => 'pp-editor-doc-links',
					]
				);

				$hd_counter++;
			}

			$this->end_controls_section();
		}
	}

	/*-----------------------------------------------------------------------------------*/
	/*	STYLE TAB
	/*-----------------------------------------------------------------------------------*/

	protected function register_style_coupon_box_controls() {
		/**
		 * Style Tab: Coupon Boxes
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_coupon_box_style',
			[
				'label'                 => __( 'Coupon Boxes', 'powerpack' ),
				'tab'                   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label'                 => __( 'Alignment', 'powerpack' ),
				'type'                  => Controls_Manager::CHOOSE,
				'options'               => [
					'left'      => [
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center'    => [
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'     => [
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-text-align-right',
					],
					'justify'   => [
						'title' => __( 'Justified', 'powerpack' ),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'default'               => 'left',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupons .pp-coupon'   => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'column_spacing',
			[
				'label'                 => __( 'Column Spacing', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'range'                 => [
					'px'    => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'               => [
					'size'  => 25,
				],
				'selectors'             => [
					'{{WRAPPER}}' => '--grid-column-gap: {{SIZE}}{{UNIT}}',
				],
				'render_type'           => 'template',
				'separator'             => 'before',
			]
		);

		$this->add_responsive_control(
			'row_spacing',
			[
				'label'                 => __( 'Row Spacing', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'range'                 => [
					'px'    => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'               => [
					'size'  => 25,
				],
				'selectors'             => [
					'{{WRAPPER}}' => '--grid-row-gap: {{SIZE}}{{UNIT}}',
				],
				'condition'             => [
					'layout'    => 'grid',
				],
			]
		);

		$this->add_control(
			'coupon_bg_color',
			[
				'label'                 => __( 'Background Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupons .pp-coupon' => 'background-color: {{VALUE}}',
				],
				'separator'             => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'                  => 'coupon_border',
				'label'                 => __( 'Border', 'powerpack' ),
				'placeholder'           => '1px',
				'default'               => '1px',
				'selector'              => '{{WRAPPER}} .pp-coupons .pp-coupon',
			]
		);

		$this->add_control(
			'coupon_border_radius',
			[
				'label'                 => __( 'Border Radius', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px', '%', 'em' ],
				'selectors'             => [
					'{{WRAPPER}} .pp-coupons .pp-coupon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'                  => 'coupon_box_shadow',
				'selector'              => '{{WRAPPER}} .pp-coupons .pp-coupon',
				'condition'             => [
					'layout'    => 'grid',
				],
			]
		);

		$this->add_responsive_control(
			'coupon_padding',
			[
				'label'                 => __( 'Padding', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px', '%' ],
				'placeholder'           => [
					'top'      => '',
					'right'    => '',
					'bottom'   => '',
					'left'     => '',
				],
				'selectors'             => [
					'{{WRAPPER}} .pp-coupons .pp-coupon' => 'padding-top: {{TOP}}{{UNIT}}; padding-left: {{LEFT}}{{UNIT}}; padding-right: {{RIGHT}}{{UNIT}}; padding-bottom: {{BOTTOM}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_discount_controls() {
		/**
		 * Style Tab: Discount
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_discount_style',
			[
				'label'                 => __( 'Discount', 'powerpack' ),
				'tab'                   => Controls_Manager::TAB_STYLE,
				'condition'             => [
					'show_discount' => 'yes',
				],
			]
		);

		$this->add_control(
			'discount_position',
			[
				'label'                 => __( 'Position', 'powerpack' ),
				'type'                  => Controls_Manager::CHOOSE,
				'options'               => [
					'left'         => [
						'title'    => __( 'Left', 'powerpack' ),
						'icon'     => 'eicon-h-align-left',
					],
					'right'        => [
						'title'    => __( 'Right', 'powerpack' ),
						'icon'     => 'eicon-h-align-right',
					],
				],
				'default'               => 'left',
				'prefix_class'          => 'pp-coupon-discount-',
				'condition'             => [
					'show_discount' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'                  => 'discount_typography',
				'label'                 => __( 'Typography', 'powerpack' ),
				'scheme'                => Scheme_Typography::TYPOGRAPHY_4,
				'selector'              => '{{WRAPPER}} .pp-coupon-discount',
			]
		);

		$this->start_controls_tabs( 'tabs_discount_style' );

		$this->start_controls_tab(
			'tab_discount_normal',
			[
				'label'                 => __( 'Normal', 'powerpack' ),
				'condition'             => [
					'show_discount' => 'yes',
				],
			]
		);

		$this->add_control(
			'discount_color_normal',
			[
				'label'                 => __( 'Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-discount' => 'color: {{VALUE}}',
				],
				'condition'             => [
					'show_discount' => 'yes',
				],
			]
		);

		$this->add_control(
			'discount_bg_color_normal',
			[
				'label'                 => __( 'Background Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'scheme'                => [
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_1,
				],
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-discount' => 'background-color: {{VALUE}}',
				],
				'condition'             => [
					'show_discount' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'                  => 'discount_border',
				'label'                 => __( 'Border', 'powerpack' ),
				'placeholder'           => '1px',
				'default'               => '1px',
				'selector'              => '{{WRAPPER}} .pp-coupon-discount',
				'condition'             => [
					'show_discount' => 'yes',
				],
			]
		);

		$this->add_control(
			'discount_border_radius',
			[
				'label'                 => __( 'Border Radius', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px', '%', 'em' ],
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-discount' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'             => [
					'show_discount' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'                  => 'discount_box_shadow',
				'selector'              => '{{WRAPPER}} .pp-coupon-discount',
				'condition'             => [
					'show_discount' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'discount_padding',
			[
				'label'                 => __( 'Padding', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px', '%' ],
				'placeholder'           => [
					'top'      => '',
					'right'    => '',
					'bottom'   => '',
					'left'     => '',
				],
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-discount' => 'padding-top: {{TOP}}{{UNIT}}; padding-left: {{LEFT}}{{UNIT}}; padding-right: {{RIGHT}}{{UNIT}}; padding-bottom: {{BOTTOM}}{{UNIT}};',
				],
				'condition'             => [
					'show_discount' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'discount_margin',
			[
				'label'                 => __( 'Margin', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px', '%' ],
				'placeholder'           => [
					'top'      => '',
					'right'    => '',
					'bottom'   => '',
					'left'     => '',
				],
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-discount' => 'margin-top: {{TOP}}{{UNIT}}; margin-left: {{LEFT}}{{UNIT}}; margin-right: {{RIGHT}}{{UNIT}}; margin-bottom: {{BOTTOM}}{{UNIT}};',
				],
				'condition'             => [
					'show_discount' => 'yes',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_discount_hover',
			[
				'label'                 => __( 'Hover', 'powerpack' ),
				'condition'             => [
					'show_discount' => 'yes',
				],
			]
		);

		$this->add_control(
			'discount_color_hover',
			[
				'label'                 => __( 'Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-discount:hover' => 'color: {{VALUE}}',
				],
				'condition'             => [
					'show_discount' => 'yes',
				],
			]
		);

		$this->add_control(
			'discount_bg_color_hover',
			[
				'label'                 => __( 'Background Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-discount:hover' => 'background-color: {{VALUE}}',
				],
				'condition'             => [
					'show_discount' => 'yes',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function register_style_coupon_code_controls() {
		/**
		 * Style Tab: Coupon Code
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_coupon_code_style',
			[
				'label'                 => __( 'Coupon Code', 'powerpack' ),
				'tab'                   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'coupon_code_position',
			[
				'label'                 => __( 'Position', 'powerpack' ),
				'type'                  => Controls_Manager::CHOOSE,
				'options'               => [
					'left'         => [
						'title'    => __( 'Left', 'powerpack' ),
						'icon'     => 'eicon-h-align-left',
					],
					'right'        => [
						'title'    => __( 'Right', 'powerpack' ),
						'icon'     => 'eicon-h-align-right',
					],
				],
				'default'               => 'left',
				'prefix_class'          => 'pp-coupon-code-',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'                  => 'coupon_code_typography',
				'label'                 => __( 'Coupon Code Typography', 'powerpack' ),
				'scheme'                => Scheme_Typography::TYPOGRAPHY_4,
				'selector'              => '{{WRAPPER}} .pp-coupon-code',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'                  => 'coupon_code_copy_text_typography',
				'label'                 => __( 'Copy Text Typography', 'powerpack' ),
				'selector'              => '{{WRAPPER}} .pp-coupon-code .pp-coupon-copy-text',
			]
		);

		$this->start_controls_tabs( 'tabs_coupon_style' );

		$this->start_controls_tab(
			'tab_coupon_normal',
			[
				'label'                 => __( 'Normal', 'powerpack' ),
			]
		);

		$this->add_control(
			'coupon_code_color_normal',
			[
				'label'                 => __( 'Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-code-text' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'coupon_code_bg_color_normal',
			[
				'label'                 => __( 'Background Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-code' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'coupon_code_reveal_text_color_normal',
			[
				'label'                 => __( 'Reveal Text Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-reveal-wrap' => 'color: {{VALUE}}',
				],
				'condition'             => [
					'coupon_style'  => 'reveal',
				],
			]
		);

		$this->add_control(
			'coupon_code_reveal_text_bg_color',
			[
				'label'                 => __( 'Reveal Text Background Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '#ff0000',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-reveal-wrap' => 'background-color: {{VALUE}}; box-shadow: 0px 0px 0px 20px {{VALUE}};',
				],
				'condition'             => [
					'coupon_style'  => 'reveal',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'                  => 'coupon_code_border',
				'label'                 => __( 'Border', 'powerpack' ),
				'placeholder'           => '1px',
				'default'               => '1px',
				'selector'              => '{{WRAPPER}} .pp-coupon-code',
			]
		);

		$this->add_control(
			'coupon_code_border_radius',
			[
				'label'                 => __( 'Border Radius', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px', '%', 'em' ],
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-code' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'                  => 'coupon_code_box_shadow',
				'selector'              => '{{WRAPPER}} .pp-coupon-code',
			]
		);

		$this->add_responsive_control(
			'coupon_code_padding',
			[
				'label'                 => __( 'Padding', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px', '%' ],
				'placeholder'           => [
					'top'      => '',
					'right'    => '',
					'bottom'   => '',
					'left'     => '',
				],
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-code' => 'padding-top: {{TOP}}{{UNIT}}; padding-left: {{LEFT}}{{UNIT}}; padding-right: {{RIGHT}}{{UNIT}}; padding-bottom: {{BOTTOM}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'coupon_code_margin',
			[
				'label'                 => __( 'Margin', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px', '%' ],
				'placeholder'           => [
					'top'      => '',
					'right'    => '',
					'bottom'   => '',
					'left'     => '',
				],
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-code' => 'margin-top: {{TOP}}{{UNIT}}; margin-left: {{LEFT}}{{UNIT}}; margin-right: {{RIGHT}}{{UNIT}}; margin-bottom: {{BOTTOM}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_coupon_hover',
			[
				'label'                 => __( 'Hover', 'powerpack' ),
			]
		);

		$this->add_control(
			'coupon_code_color_hover',
			[
				'label'                 => __( 'Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-code:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'coupon_code_bg_color_hover',
			[
				'label'                 => __( 'Background Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-code:hover' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'coupon_code_reveal_text_color_hover',
			[
				'label'                 => __( 'Reveal Text Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-code:hover .pp-coupon-reveal-wrap' => 'color: {{VALUE}}',
				],
				'condition'             => [
					'coupon_style'  => 'reveal',
				],
			]
		);

		$this->add_control(
			'coupon_code_reveal_text_bg_color_hover',
			[
				'label'                 => __( 'Reveal Text Background Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '#ff0000',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-code:hover .pp-coupon-reveal-wrap' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .pp-coupon-code.pp-coupon-style-reveal:hover .pp-coupon-reveal-wrap' => 'box-shadow: 0px 0px 0px 3px {{VALUE}};',
				],
				'condition'             => [
					'coupon_style'  => 'reveal',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'coupon_icon_heading',
			[
				'label'                 => __( 'Coupon Icon', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
				'separator'             => 'before',
			]
		);

		$this->add_control(
			'coupon_icon_color',
			[
				'label'                 => __( 'Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '#FFFFFF',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon .pp-coupon-code-icon' => 'color: {{VALUE}}; fill: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'coupon_icon_size',
			[
				'label'                 => __( 'Size', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'range'                 => [
					'px' => [
						'min'   => 1,
						'max'   => 60,
						'step'  => 1,
					],
				],
				'size_units'            => [ 'px' ],
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon .pp-coupon-code-icon' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'coupon_icon_spacing',
			[
				'label'                 => __( 'Spacing', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'range'                 => [
					'px' => [
						'min'   => 0,
						'max'   => 60,
						'step'  => 1,
					],
				],
				'size_units'            => [ 'px' ],
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon .pp-coupon-code-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_content_controls() {
		/**
		 * Style Tab: Content
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_content_style',
			[
				'label'                 => __( 'Content', 'powerpack' ),
				'tab'                   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'conent_padding',
			[
				'label'                 => __( 'Padding', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px', '%' ],
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'content_title_heading',
			[
				'label'                 => __( 'Title', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
				'separator'             => 'before',
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'                 => __( 'Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'scheme'                => [
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_2,
				],
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-title' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'                  => 'title_typography',
				'label'                 => __( 'Typography', 'powerpack' ),
				'scheme'                => Scheme_Typography::TYPOGRAPHY_1,
				'selector'              => '{{WRAPPER}} .pp-coupon-title',
			]
		);

		$this->add_responsive_control(
			'title_margin',
			[
				'label'                 => __( 'Margin Bottom', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'default'               => [
					'size'  => 10,
				],
				'range'                 => [
					'px' => [
						'min'   => 0,
						'max'   => 100,
						'step'  => 1,
					],
					'%' => [
						'min'   => 0,
						'max'   => 30,
						'step'  => 1,
					],
				],
				'size_units'            => [ 'px', '%' ],
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-title' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'content_description_heading',
			[
				'label'                 => __( 'Description', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
				'separator'             => 'before',
			]
		);

		$this->add_control(
			'description_color',
			[
				'label'                 => __( 'Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'scheme'                => [
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_3,
				],
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-description' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'                  => 'description_typography',
				'label'                 => __( 'Typography', 'powerpack' ),
				'scheme'                => Scheme_Typography::TYPOGRAPHY_3,
				'selector'              => '{{WRAPPER}} .pp-coupon-description',
			]
		);

		$this->add_responsive_control(
			'description_margin',
			[
				'label'                 => __( 'Margin Bottom', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'default'               => [
					'size'  => 20,
				],
				'range'                 => [
					'px' => [
						'min'   => 0,
						'max'   => 100,
						'step'  => 1,
					],
					'%' => [
						'min'   => 0,
						'max'   => 30,
						'step'  => 1,
					],
				],
				'size_units'            => [ 'px', '%' ],
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-description' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_button_controls() {
		/**
		 * Style Tab: Button
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_coupons_button_style',
			[
				'label'                 => __( 'Button', 'powerpack' ),
				'tab'                   => Controls_Manager::TAB_STYLE,
				'condition'             => [
					'link_type'  => 'button',
				],
			]
		);

		$this->add_control(
			'button_size',
			[
				'label'                 => __( 'Size', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'default'               => 'md',
				'options'               => [
					'xs' => __( 'Extra Small', 'powerpack' ),
					'sm' => __( 'Small', 'powerpack' ),
					'md' => __( 'Medium', 'powerpack' ),
					'lg' => __( 'Large', 'powerpack' ),
					'xl' => __( 'Extra Large', 'powerpack' ),
				],
				'condition'             => [
					'link_type'  => 'button',
				],
			]
		);

		$this->add_responsive_control(
			'button_spacing',
			[
				'label'                 => __( 'Button Spacing', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'default'               => [
					'size'  => 20,
				],
				'range'                 => [
					'px' => [
						'min'   => 1,
						'max'   => 30,
						'step'  => 1,
					],
				],
				'size_units'            => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-button-wrap' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
				'condition'             => [
					'link_type'  => 'button',
				],
			]
		);

		$this->add_responsive_control(
			'button_align',
			[
				'label'                 => __( 'Alignment', 'powerpack' ),
				'type'                  => Controls_Manager::CHOOSE,
				'options'               => [
					'left'      => [
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center'    => [
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'     => [
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-text-align-right',
					],
					'justify'   => [
						'title' => __( 'Justified', 'powerpack' ),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupons .pp-coupon-button-wrap'   => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label'                 => __( 'Normal', 'powerpack' ),
			]
		);

		$this->add_control(
			'button_text_color_normal',
			[
				'label'                 => __( 'Text Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-button' => 'color: {{VALUE}}',
					'{{WRAPPER}} .pp-coupon-button .pp-icon' => 'fill: {{VALUE}}',
				],
				'condition'             => [
					'link_type'  => 'button',
				],
			]
		);

		$this->add_control(
			'button_bg_color_normal',
			[
				'label'                 => __( 'Background Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'scheme'                => [
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_4,
				],
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-button' => 'background-color: {{VALUE}}',
				],
				'condition'             => [
					'link_type'  => 'button',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'                  => 'button_border_normal',
				'label'                 => __( 'Border', 'powerpack' ),
				'placeholder'           => '1px',
				'default'               => '1px',
				'selector'              => '{{WRAPPER}} .pp-coupon-button',
				'condition'             => [
					'link_type'  => 'button',
				],
			]
		);

		$this->add_control(
			'button_border_radius',
			[
				'label'                 => __( 'Border Radius', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px', '%', 'em' ],
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'             => [
					'link_type'  => 'button',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'                  => 'button_typography',
				'label'                 => __( 'Typography', 'powerpack' ),
				'scheme'                => Scheme_Typography::TYPOGRAPHY_4,
				'selector'              => '{{WRAPPER}} .pp-coupon-button',
				'condition'             => [
					'link_type'  => 'button',
				],
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label'                 => __( 'Padding', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px', 'em', '%' ],
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'             => [
					'link_type'  => 'button',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'                  => 'button_box_shadow',
				'selector'              => '{{WRAPPER}} .pp-coupon-button',
				'condition'             => [
					'link_type'  => 'button',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label'                 => __( 'Hover', 'powerpack' ),
				'condition'             => [
					'link_type'  => 'button',
				],
			]
		);

		$this->add_control(
			'button_text_color_hover',
			[
				'label'                 => __( 'Text Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-button:hover' => 'color: {{VALUE}}',
					'{{WRAPPER}} .pp-coupon-button:hover .pp-icon' => 'fill: {{VALUE}}',
				],
				'condition'             => [
					'link_type'  => 'button',
				],
			]
		);

		$this->add_control(
			'button_bg_color_hover',
			[
				'label'                 => __( 'Background Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-button:hover' => 'background-color: {{VALUE}}',
				],
				'condition'             => [
					'link_type'  => 'button',
				],
			]
		);

		$this->add_control(
			'button_border_color_hover',
			[
				'label'                 => __( 'Border Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-button:hover' => 'border-color: {{VALUE}}',
				],
				'condition'             => [
					'link_type'  => 'button',
				],
			]
		);

		$this->add_control(
			'button_hover_animation',
			[
				'label'                 => __( 'Animation', 'powerpack' ),
				'type'                  => Controls_Manager::HOVER_ANIMATION,
				'condition'             => [
					'link_type'  => 'button',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'                  => 'button_box_shadow_hover',
				'selector'              => '{{WRAPPER}} .pp-coupon-button:hover',
				'condition'             => [
					'link_type'  => 'button',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'button_icon_heading',
			[
				'label'                 => __( 'Button Icon', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
				'separator'             => 'before',
			]
		);

		$this->add_control(
			'button_icon_color',
			[
				'label'                 => __( 'Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupons .pp-button-icon' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'button_icon_size',
			[
				'label'                 => __( 'Size', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'range'                 => [
					'px' => [
						'min'   => 1,
						'max'   => 60,
						'step'  => 1,
					],
				],
				'size_units'            => [ 'px' ],
				'selectors'             => [
					'{{WRAPPER}} .pp-coupons .pp-button-icon' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_icon_spacing',
			[
				'label'                 => __( 'Spacing', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'range'                 => [
					'px' => [
						'min'   => 0,
						'max'   => 60,
						'step'  => 1,
					],
				],
				'size_units'            => [ 'px' ],
				'selectors'             => [
					'{{WRAPPER}}.pp-coupon-button-icon-before .pp-coupons .pp-button-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.pp-coupon-button-icon-after .pp-coupons .pp-button-icon' => 'margin-left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'button_separator_heading',
			[
				'label'                 => __( 'Separator', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
				'separator'             => 'before',
				'condition'             => [
					'button_separator'  => 'yes',
				],
			]
		);

		$this->add_control(
			'button_separator_color',
			[
				'label'                 => __( 'Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-separator' => 'border-top-color: {{VALUE}}',
				],
				'condition'             => [
					'button_separator'  => 'yes',
				],
			]
		);

		$this->add_control(
			'button_separator_style',
			[
				'label'                 => __( 'Style', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'options'               => [
					'solid'     => __( 'Solid', 'powerpack' ),
					'dotted'    => __( 'Dotted', 'powerpack' ),
					'dashed'    => __( 'Dashed', 'powerpack' ),
				],
				'default'               => 'solid',
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-separator' => 'border-top-style: {{VALUE}}',
				],
				'condition'             => [
					'button_separator'  => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'button_separator_size',
			[
				'label'                 => __( 'Width', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'range'                 => [
					'px' => [
						'min'   => 0,
						'max'   => 60,
						'step'  => 1,
					],
				],
				'size_units'            => [ 'px' ],
				'selectors'             => [
					'{{WRAPPER}} .pp-coupon-separator' => 'border-top-width: {{SIZE}}{{UNIT}};',
				],
				'condition'             => [
					'button_separator'  => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	public function register_style_filter_controls() {
		/**
		 * Style Tab: Filters
		 */
		$this->start_controls_section(
			'section_filter_style',
			array(
				'label'     => __( 'Filters', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters'  => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'filter_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-post-filters',
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'filters_gap',
			array(
				'label'     => __( 'Horizontal Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 5,
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'body:not(.rtl) {{WRAPPER}} .pp-post-filters .pp-post-filter' => 'margin-right: {{SIZE}}{{UNIT}};',
					'body.rtl {{WRAPPER}} .pp-post-filters .pp-post-filter' => 'margin-left: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'filters_gap_vertical',
			array(
				'label'     => __( 'Vertical Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 5,
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-post-filters .pp-post-filter' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'filters_margin_bottom',
			array(
				'label'      => __( 'Filters Bottom Spacing', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 80,
						'step' => 1,
					),
				),
				'size_units' => '',
				'selectors'  => array(
					'{{WRAPPER}} .pp-post-filters' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'layout!' => 'carousel',
					'show_filters'  => 'yes',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_filter_style' );

		$this->start_controls_tab(
			'tab_filter_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
				),
			)
		);

		$this->add_control(
			'filter_color_normal',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-post-filter, {{WRAPPER}} .pp-post-filters-dropdown-button, {{WRAPPER}} .pp-post-filters-dropdown-item' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
				),
			)
		);

		$this->add_control(
			'filter_background_color_normal',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-post-filter, {{WRAPPER}} .pp-post-filters-dropdown-button, {{WRAPPER}} .pp-post-filters-dropdown-item' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'filter_border_normal',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-post-filter, {{WRAPPER}} .pp-post-filters-dropdown-button',
				'condition'   => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'filter_border_radius_normal',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-post-filter, {{WRAPPER}} .pp-post-filters-dropdown-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'filter_padding',
			array(
				'label'       => __( 'Padding', 'powerpack' ),
				'type'        => Controls_Manager::DIMENSIONS,
				'size_units'  => array( 'px', 'em', '%' ),
				'placeholder' => array(
					'top'    => '',
					'right'  => '',
					'bottom' => '',
					'left'   => '',
				),
				'selectors'   => array(
					'{{WRAPPER}} .pp-post-filter, {{WRAPPER}} .pp-post-filters-dropdown-button, {{WRAPPER}} .pp-post-filters-dropdown-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'   => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'filter_box_shadow',
				'selector'  => '{{WRAPPER}} .pp-post-filter, {{WRAPPER}} .pp-post-filters-dropdown-button, {{WRAPPER}} .pp-post-filters-dropdown-item',
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_filter_active',
			array(
				'label'     => __( 'Active', 'powerpack' ),
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
				),
			)
		);

		$this->add_control(
			'filter_color_active',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-post-filter.pp-filter-current, {{WRAPPER}} .pp-post-filters-dropdown-item.pp-filter-current' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
				),
			)
		);

		$this->add_control(
			'filter_background_color_active',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-post-filter.pp-filter-current, {{WRAPPER}} .pp-post-filters-dropdown-item.pp-filter-current' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
				),
			)
		);

		$this->add_control(
			'filter_border_color_active',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-post-filter.pp-filter-current, {{WRAPPER}} .pp-post-filters-dropdown-item.pp-filter-current' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'filter_box_shadow_active',
				'selector'  => '{{WRAPPER}} .pp-post-filter.pp-filter-current, {{WRAPPER}} .pp-post-filters-dropdown-item.pp-filter-current',
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_filter_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
				),
			)
		);

		$this->add_control(
			'filter_color_hover',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-post-filter:hover, {{WRAPPER}} .pp-post-filters-dropdown-item:hover' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
				),
			)
		);

		$this->add_control(
			'filter_background_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-post-filter:hover, {{WRAPPER}} .pp-post-filters-dropdown-item:hover' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
				),
			)
		);

		$this->add_control(
			'filter_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-post-filter:hover, {{WRAPPER}} .pp-post-filters-dropdown-item:hover' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'filter_box_shadow_hover',
				'selector'  => '{{WRAPPER}} .pp-post-filter:hover, {{WRAPPER}} .pp-post-filters-dropdown-item:hover',
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'filters_count_style_heading',
			array(
				'label'     => __( 'Post Count', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
					'show_filters_count' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'filters_count_typography',
				'selector'  => '{{WRAPPER}} .pp-post-filter-count',
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
					'show_filters_count' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'filters_count_spacing',
			array(
				'label'     => __( 'Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 40,
					),
				),
				'default'   => array(
					'size' => 5,
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-post-filter-count' => 'margin-left: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
					'show_filters_count' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'filters_count_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-post-filter-count' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
					'show_filters_count' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'filters_count_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-post-filter-count' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
					'show_filters_count' => 'yes',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_filter_count_style' );

		$this->start_controls_tab(
			'tab_filter_count_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
					'show_filters_count' => 'yes',
				),
			)
		);

		$this->add_control(
			'filter_count_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-post-filter-count' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
					'show_filters_count' => 'yes',
				),
			)
		);

		$this->add_control(
			'filter_count_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-post-filter-count' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
					'show_filters_count' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_filter_count_active',
			array(
				'label'     => __( 'Active', 'powerpack' ),
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
					'show_filters_count' => 'yes',
				),
			)
		);

		$this->add_control(
			'filter_count_color_active',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-post-filter.pp-filter-current .pp-post-filter-count' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
					'show_filters_count' => 'yes',
				),
			)
		);

		$this->add_control(
			'filter_count_background_color_active',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-post-filter.pp-filter-current .pp-post-filter-count' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
					'show_filters_count' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_filter_count_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
					'show_filters_count' => 'yes',
				),
			)
		);

		$this->add_control(
			'filter_count_color_hover',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-post-filter:hover .pp-post-filter-count' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
					'show_filters_count' => 'yes',
				),
			)
		);

		$this->add_control(
			'filter_count_background_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-post-filter:hover .pp-post-filter-count' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'layout!' => 'carousel',
					'show_filters' => 'yes',
					'show_filters_count' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function register_style_pagination_controls() {

		$this->start_controls_section(
			'section_pagination_style',
			array(
				'label'     => __( 'Pagination', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'source'           => 'posts',
					'layout!'          => 'carousel',
					'pagination_type!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'pagination_margin_top',
			array(
				'label'     => __( 'Gap between Posts & Pagination', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => '',
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-posts-pagination-top .pp-posts-pagination' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .pp-posts-pagination-bottom .pp-posts-pagination' => 'margin-top: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array( 'numbers', 'numbers_and_prev_next', 'load_more' ),
				),
			)
		);

		$this->add_control(
			'load_more_button_size',
			array(
				'label'     => __( 'Size', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'sm',
				'options'   => array(
					'xs' => __( 'Extra Small', 'powerpack' ),
					'sm' => __( 'Small', 'powerpack' ),
					'md' => __( 'Medium', 'powerpack' ),
					'lg' => __( 'Large', 'powerpack' ),
					'xl' => __( 'Extra Large', 'powerpack' ),
				),
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => 'load_more',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'pagination_typography',
				'selector'  => '{{WRAPPER}} .pp-posts-pagination .page-numbers, {{WRAPPER}} .pp-posts-pagination a',
				'scheme'    => Scheme_Typography::TYPOGRAPHY_2,
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array( 'numbers', 'numbers_and_prev_next', 'load_more' ),
				),
			)
		);

		$this->start_controls_tabs( 'tabs_pagination' );

		$this->start_controls_tab(
			'tab_pagination_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array( 'numbers', 'numbers_and_prev_next', 'load_more' ),
				),
			)
		);

		$this->add_control(
			'pagination_link_bg_color_normal',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => [
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_4,
				],
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-posts-pagination .page-numbers, {{WRAPPER}} .pp-posts-pagination a' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array( 'numbers', 'numbers_and_prev_next', 'load_more' ),
				),
			)
		);

		$this->add_control(
			'pagination_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-posts-pagination .page-numbers, {{WRAPPER}} .pp-posts-pagination a' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array( 'numbers', 'numbers_and_prev_next', 'load_more' ),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'pagination_link_border_normal',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-posts-pagination .page-numbers, {{WRAPPER}} .pp-posts-pagination a',
				'condition'   => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array( 'numbers', 'numbers_and_prev_next', 'load_more' ),
				),
			)
		);

		$this->add_responsive_control(
			'pagination_link_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-posts-pagination .page-numbers, {{WRAPPER}} .pp-posts-pagination a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array( 'numbers', 'numbers_and_prev_next', 'load_more' ),
				),
			)
		);

		$this->add_responsive_control(
			'pagination_link_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-posts-pagination .page-numbers, {{WRAPPER}} .pp-posts-pagination a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array( 'numbers', 'numbers_and_prev_next', 'load_more' ),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'pagination_link_box_shadow',
				'selector'  => '{{WRAPPER}} .pp-posts-pagination .page-numbers, {{WRAPPER}} .pp-posts-pagination a',
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array( 'numbers', 'numbers_and_prev_next', 'load_more' ),
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_pagination_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array( 'numbers', 'numbers_and_prev_next', 'load_more' ),
				),
			)
		);

		$this->add_control(
			'pagination_link_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-posts-pagination a:hover' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array( 'numbers', 'numbers_and_prev_next', 'load_more' ),
				),
			)
		);

		$this->add_control(
			'pagination_color_hover',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-posts-pagination a:hover' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array( 'numbers', 'numbers_and_prev_next', 'load_more' ),
				),
			)
		);

		$this->add_control(
			'pagination_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-posts-pagination a:hover' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array( 'numbers', 'numbers_and_prev_next', 'load_more' ),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'pagination_link_box_shadow_hover',
				'selector'  => '{{WRAPPER}} .pp-posts-pagination a:hover',
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array( 'numbers', 'numbers_and_prev_next', 'load_more' ),
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_pagination_active',
			array(
				'label'     => __( 'Active', 'powerpack' ),
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array( 'numbers', 'numbers_and_prev_next' ),
				),
			)
		);

		$this->add_control(
			'pagination_link_bg_color_active',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-posts-pagination .page-numbers.current' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array( 'numbers', 'numbers_and_prev_next' ),
				),
			)
		);

		$this->add_control(
			'pagination_color_active',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-posts-pagination .page-numbers.current' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array( 'numbers', 'numbers_and_prev_next' ),
				),
			)
		);

		$this->add_control(
			'pagination_border_color_active',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-posts-pagination .page-numbers.current' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array( 'numbers', 'numbers_and_prev_next' ),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'pagination_link_box_shadow_active',
				'selector'  => '{{WRAPPER}} .pp-posts-pagination .page-numbers.current',
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array( 'numbers', 'numbers_and_prev_next' ),
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'pagination_spacing',
			array(
				'label'     => __( 'Space Between', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'separator' => 'before',
				'default'   => array(
					'size' => 10,
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'body:not(.rtl) {{WRAPPER}} .pp-posts-pagination .page-numbers:not(:first-child)' => 'margin-left: calc( {{SIZE}}{{UNIT}}/2 );',
					'body:not(.rtl) {{WRAPPER}} .pp-posts-pagination .page-numbers:not(:last-child)' => 'margin-right: calc( {{SIZE}}{{UNIT}}/2 );',
					'body.rtl {{WRAPPER}} .pp-posts-pagination .page-numbers:not(:first-child)' => 'margin-right: calc( {{SIZE}}{{UNIT}}/2 );',
					'body.rtl {{WRAPPER}} .pp-posts-pagination .page-numbers:not(:last-child)' => 'margin-left: calc( {{SIZE}}{{UNIT}}/2 );',
				),
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array( 'numbers', 'numbers_and_prev_next' ),
				),
			)
		);

		$this->add_control(
			'heading_loader',
			array(
				'label'     => __( 'Loader', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array( 'load_more', 'infinite' ),
				),
			)
		);

		$this->add_control(
			'loader_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-loader:after, {{WRAPPER}} .pp-posts-loader:after' => 'border-bottom-color: {{VALUE}}; border-top-color: {{VALUE}};',
				),
				'condition' => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array( 'load_more', 'infinite' ),
				),
			)
		);

		$this->add_responsive_control(
			'loader_size',
			array(
				'label'      => __( 'Size', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min'  => 10,
						'max'  => 80,
						'step' => 1,
					),
				),
				'default'    => array(
					'size' => 46,
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-posts-loader' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'source'          => 'posts',
					'layout!'         => 'carousel',
					'coupons_pagination_type' => array( 'load_more', 'infinite' ),
				),
			)
		);

		$this->end_controls_section();
	}

	protected function register_style_arrows_controls() {
		/**
		 * Style Tab: Arrows
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_arrows_style',
			[
				'label'                 => __( 'Arrows', 'powerpack' ),
				'tab'                   => Controls_Manager::TAB_STYLE,
				'condition'             => [
					'layout'    => 'carousel',
					'arrows'    => 'yes',
				],
			]
		);

		$this->add_control(
			'select_arrow',
			[
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
				'condition'             => [
					'layout'    => 'carousel',
					'arrows'    => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'arrows_size',
			[
				'label'                 => __( 'Arrows Size', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'default'               => [ 'size' => '22' ],
				'range'                 => [
					'px' => [
						'min'   => 15,
						'max'   => 100,
						'step'  => 1,
					],
				],
				'size_units'            => [ 'px' ],
				'selectors'             => [
					'{{WRAPPER}} .swiper-container-wrap .swiper-button-next, {{WRAPPER}} .swiper-container-wrap .swiper-button-prev' => 'font-size: {{SIZE}}{{UNIT}};',
				],
				'condition'             => [
					'layout'    => 'carousel',
					'arrows'    => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'align_arrows',
			[
				'label'                 => __( 'Align Arrows', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'range'                 => [
					'px' => [
						'min'   => -100,
						'max'   => 40,
						'step'  => 1,
					],
				],
				'size_units'            => [ 'px' ],
				'selectors'             => [
					'{{WRAPPER}} .swiper-container-wrap .swiper-button-prev' => 'left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .swiper-container-wrap .swiper-button-next' => 'right: {{SIZE}}{{UNIT}};',
				],
				'condition'             => [
					'layout'    => 'carousel',
					'arrows'    => 'yes',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_arrows_style' );

		$this->start_controls_tab(
			'tab_arrows_normal',
			[
				'label'                 => __( 'Normal', 'powerpack' ),
				'condition'             => [
					'layout'    => 'carousel',
					'arrows'    => 'yes',
				],
			]
		);

		$this->add_control(
			'arrows_bg_color_normal',
			[
				'label'                 => __( 'Background Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .swiper-container-wrap .swiper-button-next, {{WRAPPER}} .swiper-container-wrap .swiper-button-prev' => 'background-color: {{VALUE}};',
				],
				'condition'             => [
					'layout'    => 'carousel',
					'arrows'    => 'yes',
				],
			]
		);

		$this->add_control(
			'arrows_color_normal',
			[
				'label'                 => __( 'Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .swiper-container-wrap .swiper-button-next, {{WRAPPER}} .swiper-container-wrap .swiper-button-prev' => 'color: {{VALUE}};',
				],
				'condition'             => [
					'layout'    => 'carousel',
					'arrows'    => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'                  => 'arrows_border_normal',
				'label'                 => __( 'Border', 'powerpack' ),
				'placeholder'           => '1px',
				'default'               => '1px',
				'selector'              => '{{WRAPPER}} .swiper-container-wrap .swiper-button-next, {{WRAPPER}} .swiper-container-wrap .swiper-button-prev',
				'condition'             => [
					'layout'    => 'carousel',
					'arrows'    => 'yes',
				],
			]
		);

		$this->add_control(
			'arrows_border_radius_normal',
			[
				'label'                 => __( 'Border Radius', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px', '%', 'em' ],
				'selectors'             => [
					'{{WRAPPER}} .swiper-container-wrap .swiper-button-next, {{WRAPPER}} .swiper-container-wrap .swiper-button-prev' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'             => [
					'layout'    => 'carousel',
					'arrows'    => 'yes',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_arrows_hover',
			[
				'label'                 => __( 'Hover', 'powerpack' ),
				'condition'             => [
					'layout'    => 'carousel',
					'arrows'    => 'yes',
				],
			]
		);

		$this->add_control(
			'arrows_bg_color_hover',
			[
				'label'                 => __( 'Background Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .swiper-container-wrap .swiper-button-next:hover, {{WRAPPER}} .swiper-container-wrap .swiper-button-prev:hover' => 'background-color: {{VALUE}};',
				],
				'condition'             => [
					'layout'    => 'carousel',
					'arrows'    => 'yes',
				],
			]
		);

		$this->add_control(
			'arrows_color_hover',
			[
				'label'                 => __( 'Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .swiper-container-wrap .swiper-button-next:hover, {{WRAPPER}} .swiper-container-wrap .swiper-button-prev:hover' => 'color: {{VALUE}};',
				],
				'condition'             => [
					'layout'    => 'carousel',
					'arrows'    => 'yes',
				],
			]
		);

		$this->add_control(
			'arrows_border_color_hover',
			[
				'label'                 => __( 'Border Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .swiper-container-wrap .swiper-button-next:hover, {{WRAPPER}} .swiper-container-wrap .swiper-button-prev:hover' => 'border-color: {{VALUE}};',
				],
				'condition'             => [
					'layout'    => 'carousel',
					'arrows'    => 'yes',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'arrows_padding',
			[
				'label'                 => __( 'Padding', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px', '%' ],
				'selectors'             => [
					'{{WRAPPER}} .swiper-container-wrap .swiper-button-next, {{WRAPPER}} .swiper-container-wrap .swiper-button-prev' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator'             => 'before',
				'condition'             => [
					'layout'    => 'carousel',
					'arrows'    => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_dots_controls() {
		/**
		 * Style Tab: Pagination: Dots
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_dots_style',
			[
				'label'                 => __( 'Pagination: Dots', 'powerpack' ),
				'tab'                   => Controls_Manager::TAB_STYLE,
				'condition'             => [
					'layout'            => 'carousel',
					'dots'              => 'yes',
					'pagination_type'   => 'bullets',
				],
			]
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
				'condition'             => [
					'layout'            => 'carousel',
					'dots'              => 'yes',
					'pagination_type'   => 'bullets',
				],
			]
		);

		$this->add_responsive_control(
			'dots_size',
			[
				'label'                 => __( 'Size', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'range'                 => [
					'px' => [
						'min'   => 2,
						'max'   => 40,
						'step'  => 1,
					],
				],
				'size_units'            => '',
				'selectors'             => [
					'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}',
				],
				'condition'             => [
					'layout'            => 'carousel',
					'dots'              => 'yes',
					'pagination_type'   => 'bullets',
				],
			]
		);

		$this->add_responsive_control(
			'dots_spacing',
			[
				'label'                 => __( 'Spacing', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'range'                 => [
					'px' => [
						'min'   => 1,
						'max'   => 30,
						'step'  => 1,
					],
				],
				'size_units'            => '',
				'selectors'             => [
					'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: {{SIZE}}{{UNIT}}',
				],
				'condition'             => [
					'layout'            => 'carousel',
					'dots'              => 'yes',
					'pagination_type'   => 'bullets',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_dots_style' );

		$this->start_controls_tab(
			'tab_dots_normal',
			[
				'label'                 => __( 'Normal', 'powerpack' ),
				'condition'             => [
					'layout'            => 'carousel',
					'dots'              => 'yes',
					'pagination_type'   => 'bullets',
				],
			]
		);

		$this->add_control(
			'dots_color_normal',
			[
				'label'                 => __( 'Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet' => 'background: {{VALUE}};',
				],
				'condition'             => [
					'layout'            => 'carousel',
					'dots'              => 'yes',
					'pagination_type'   => 'bullets',
				],
			]
		);

		$this->add_control(
			'active_dot_color_normal',
			[
				'label'                 => __( 'Active Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet-active' => 'background: {{VALUE}};',
				],
				'condition'             => [
					'layout'            => 'carousel',
					'dots'              => 'yes',
					'pagination_type'   => 'bullets',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'                  => 'dots_border_normal',
				'label'                 => __( 'Border', 'powerpack' ),
				'placeholder'           => '1px',
				'default'               => '1px',
				'selector'              => '{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet',
				'condition'             => [
					'layout'            => 'carousel',
					'dots'              => 'yes',
					'pagination_type'   => 'bullets',
				],
			]
		);

		$this->add_control(
			'dots_border_radius_normal',
			[
				'label'                 => __( 'Border Radius', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px', '%', 'em' ],
				'selectors'             => [
					'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'             => [
					'layout'            => 'carousel',
					'dots'              => 'yes',
					'pagination_type'   => 'bullets',
				],
			]
		);

		$this->add_responsive_control(
			'dots_margin',
			[
				'label'                 => __( 'Margin', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px', 'em', '%' ],
				'allowed_dimensions'    => 'vertical',
				'placeholder'           => [
					'top'      => '',
					'right'    => 'auto',
					'bottom'   => '',
					'left'     => 'auto',
				],
				'selectors'             => [
					'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullets' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'             => [
					'layout'            => 'carousel',
					'dots'              => 'yes',
					'pagination_type'   => 'bullets',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_dots_hover',
			[
				'label'                 => __( 'Hover', 'powerpack' ),
				'condition'             => [
					'layout'            => 'carousel',
					'dots'              => 'yes',
					'pagination_type'   => 'bullets',
				],
			]
		);

		$this->add_control(
			'dots_color_hover',
			[
				'label'                 => __( 'Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet:hover' => 'background: {{VALUE}};',
				],
				'condition'             => [
					'layout'            => 'carousel',
					'dots'              => 'yes',
					'pagination_type'   => 'bullets',
				],
			]
		);

		$this->add_control(
			'dots_border_color_hover',
			[
				'label'                 => __( 'Border Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet:hover' => 'border-color: {{VALUE}};',
				],
				'condition'             => [
					'layout'            => 'carousel',
					'dots'              => 'yes',
					'pagination_type'   => 'bullets',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function register_style_fraction_controls() {
		/**
		 * Style Tab: Pagination: Fraction
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_fraction_style',
			[
				'label'                 => __( 'Pagination: Fraction', 'powerpack' ),
				'tab'                   => Controls_Manager::TAB_STYLE,
				'condition'             => [
					'layout'            => 'carousel',
					'dots'              => 'yes',
					'pagination_type'   => 'fraction',
				],
			]
		);

		$this->add_control(
			'fraction_text_color',
			[
				'label'                 => __( 'Text Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .swiper-pagination-fraction' => 'color: {{VALUE}};',
				],
				'condition'             => [
					'layout'            => 'carousel',
					'dots'              => 'yes',
					'pagination_type'   => 'fraction',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'                  => 'fraction_typography',
				'label'                 => __( 'Typography', 'powerpack' ),
				'scheme'                => Scheme_Typography::TYPOGRAPHY_4,
				'selector'              => '{{WRAPPER}} .swiper-pagination-fraction',
				'condition'             => [
					'layout'            => 'carousel',
					'dots'              => 'yes',
					'pagination_type'   => 'fraction',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Slider Settings.
	 *
	 * @access public
	 */
	public function slider_settings() {
		$settings = $this->get_settings();

		$slider_options = [
			'direction'              => 'horizontal',
			'speed'                  => ( $settings['slider_speed']['size'] ) ? $settings['slider_speed']['size'] : 400,
			'effect'                 => ( $settings['carousel_effect'] ) ? $settings['carousel_effect'] : 'slide',
			'slidesPerView'          => 1,
			'spaceBetween'           => 10,
			'grabCursor'             => ( 'yes' === $settings['grab_cursor'] ),
			'autoHeight'             => true,
			'loop'                   => ( 'yes' === $settings['infinite_loop'] ),
		];

		if ( 'yes' === $settings['autoplay'] && ! empty( $settings['autoplay_speed']['size'] ) ) {
			$autoplay_speed = $settings['autoplay_speed']['size'];
		} else {
			$autoplay_speed = 999999;
		}

		$slider_options['autoplay'] = [
			'delay'                  => $autoplay_speed,
		];

		if ( 'yes' === $settings['dots'] ) {
			$slider_options['pagination'] = [
				'el'                 => '.swiper-pagination-' . esc_attr( $this->get_id() ),
				'type'               => $settings['pagination_type'],
				'clickable'          => true,
			];
		}

		if ( 'yes' === $settings['arrows'] ) {
			$slider_options['navigation'] = [
				'nextEl'             => '.swiper-button-next-' . esc_attr( $this->get_id() ),
				'prevEl'             => '.swiper-button-prev-' . esc_attr( $this->get_id() ),
			];
		}

		$elementor_bp_lg        = get_option( 'elementor_viewport_lg' );
		$elementor_bp_md        = get_option( 'elementor_viewport_md' );
		$bp_desktop             = ! empty( $elementor_bp_lg ) ? $elementor_bp_lg : 1025;
		$bp_tablet              = ! empty( $elementor_bp_md ) ? $elementor_bp_md : 768;
		$bp_mobile              = 320;

		$slider_options['breakpoints'] = [
			$bp_desktop   => [
				'slidesPerView'      => ( isset( $settings['columns'] ) && $settings['columns'] ) ? absint( $settings['columns'] ) : 3,
				'spaceBetween'       => ( isset( $settings['column_spacing']['size'] ) && $settings['column_spacing']['size'] ) ? $settings['column_spacing']['size'] : 25,
			],
			$bp_tablet   => [
				'slidesPerView'      => ( isset( $settings['columns_tablet'] ) && $settings['columns_tablet'] ) ? absint( $settings['columns_tablet'] ) : 2,
				'spaceBetween'       => ( isset( $settings['column_spacing_tablet']['size'] ) && $settings['column_spacing_tablet']['size'] ) ? $settings['column_spacing_tablet']['size'] : 10,
			],
			$bp_mobile   => [
				'slidesPerView'      => ( isset( $settings['columns_mobile'] ) && $settings['columns_mobile'] ) ? absint( $settings['columns_mobile'] ) : 1,
				'spaceBetween'       => ( isset( $settings['column_spacing_mobile']['size'] ) && $settings['column_spacing_mobile']['size'] ) ? $settings['column_spacing_mobile']['size'] : 10,
			],
		];

		$this->add_render_attribute(
			'container',
			[
				'data-slider-settings' => wp_json_encode( $slider_options ),
			]
		);
	}

	/**
	 * Returns the paged number for the query.
	 *
	 * @since 2.3.7
	 * @return int
	 */
	public function get_paged() {
		$settings = $this->get_settings_for_display();

		global $wp_the_query, $paged;

		$pagination_ajax = ( 'yes' === $settings['show_filters'] ) ? 'yes' : $settings['pagination_ajax'];
		$pagination_type = $settings['coupons_pagination_type'];

		if ( 'yes' === $pagination_ajax || 'load_more' === $pagination_type || 'infinite' === $pagination_type ) {
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'pp-posts-widget-nonce' ) ) {
				if ( isset( $_POST['page_number'] ) && '' !== $_POST['page_number'] ) {
					return $_POST['page_number'];
				}
			}

			// Check the 'paged' query var.
			$paged_qv = $wp_the_query->get( 'paged' );

			if ( is_numeric( $paged_qv ) ) {
				return $paged_qv;
			}

			// Check the 'page' query var.
			$page_qv = $wp_the_query->get( 'page' );

			if ( is_numeric( $page_qv ) ) {
				return $page_qv;
			}

			// Check the $paged global?
			if ( is_numeric( $paged ) ) {
				return $paged;
			}

			return 0;
		} else {
			return max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) );
		}
	}

	/**
	 * Render post body output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 2.3.7
	 * @access public
	 */
	public function render_ajax_pagination( $filter = '', $taxonomy = '', $search = '' ) {
		ob_start();
		$this->render_pagination( $filter, $taxonomy, $search );
		return ob_get_clean();
	}

	/**
	 * Get Pagination.
	 *
	 * Returns the Pagination HTML.
	 *
	 * @since 2.3.7
	 * @access public
	 */
	public function render_pagination( $filter = '', $taxonomy = '', $search = '' ) {
		$settings  = $this->get_settings_for_display();

		$pagination_type    = $settings['coupons_pagination_type'];
		$page_limit         = $settings['pagination_page_limit'];
		$pagination_shorten = $settings['pagination_numbers_shorten'];

		if ( 'none' === $pagination_type ) {
			return;
		}

		// Get current page number.
		$paged = $this->get_paged();

		$query       = $this->get_query( $filter, $taxonomy, $search, '', 'yes' );
		$total_pages = $query->max_num_pages;
		$total_pages_pagination = $query->max_num_pages;

		if ( 'load_more' !== $pagination_type || 'infinite' !== $pagination_type ) {

			if ( '' !== $page_limit && null !== $page_limit ) {
				$total_pages = min( $page_limit, $total_pages );
			}
		}

		if ( 2 > $total_pages ) {
			return;
		}

		$has_numbers   = in_array( $pagination_type, array( 'numbers', 'numbers_and_prev_next' ) );
		$has_prev_next = ( 'numbers_and_prev_next' === $pagination_type );
		$is_load_more  = ( 'load_more' === $pagination_type );
		$is_infinite   = ( 'infinite' === $pagination_type );

		$links = array();

		if ( $has_numbers || $is_infinite ) {

			$current_page = $paged;
			if ( ! $current_page ) {
				$current_page = 1;
			}

			$paginate_args = array(
				'type'      => 'array',
				'current'   => $current_page,
				'total'     => $total_pages,
				'prev_next' => false,
				'show_all'  => 'yes' !== $pagination_shorten,
			);
		}

		if ( $has_prev_next ) {
			$prev_label = $settings['pagination_prev_label'];
			$next_label = $settings['pagination_next_label'];

			$paginate_args['prev_next'] = true;

			if ( $prev_label ) {
				$paginate_args['prev_text'] = $prev_label;
			}
			if ( $next_label ) {
				$paginate_args['next_text'] = $next_label;
			}
		}

		if ( $has_numbers || $has_prev_next || $is_infinite ) {

			if ( is_singular() && ! is_front_page() && ! is_singular( 'page' ) ) {
				global $wp_rewrite;
				if ( $wp_rewrite->using_permalinks() ) {
					$paginate_args['base']   = trailingslashit( get_permalink() ) . '%_%';
					$paginate_args['format'] = user_trailingslashit( '%#%', 'single_paged' );
				} else {
					$paginate_args['format'] = '?page=%#%';
				}
			}

			$links = paginate_links( $paginate_args );

		}

		if ( ! $is_load_more ) {
			$pagination_ajax = ( 'yes' === $settings['show_filters'] ) ? 'yes' : $settings['pagination_ajax'];
			$pagination_type = 'standard';

			if ( 'yes' === $pagination_ajax ) {
				$pagination_type = 'ajax';
			}
			?>
			<nav class="pp-posts-pagination pp-posts-pagination-<?php echo esc_attr( $pagination_type ); ?> elementor-pagination" role="navigation" aria-label="<?php esc_attr_e( 'Pagination', 'powerpack' ); ?>" data-total="<?php echo esc_html( $total_pages_pagination ); ?>">
				<?php echo implode( PHP_EOL, $links ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</nav>
			<?php
		}

		if ( $is_load_more ) {
			$load_more_label                = $settings['pagination_load_more_label'];
			$load_more_button_icon          = $settings['pagination_load_more_button_icon'];
			$load_more_button_icon_position = $settings['pagination_load_more_button_icon_position'];
			$load_more_button_size          = $settings['load_more_button_size'];
			?>
			<div class="pp-post-load-more-wrap pp-posts-pagination" data-total="<?php echo esc_html( $total_pages_pagination ); ?>">
				<a class="pp-post-load-more elementor-button elementor-size-<?php echo esc_attr( $load_more_button_size ); ?>" href="javascript:void(0);">
					<?php if ( $load_more_button_icon['value'] && 'before' === $load_more_button_icon_position ) { ?>
						<span class="pp-button-icon pp-icon">
							<?php \Elementor\Icons_Manager::render_icon( $settings['pagination_load_more_button_icon'], [ 'aria-hidden' => 'true' ] ); ?>
						</span>
					<?php } ?>
					<?php if ( $load_more_label ) { ?>
						<span class="pp-button-text">
							<?php echo esc_html( $load_more_label ); ?>
						</span>
					<?php } ?>
					<?php if ( $load_more_button_icon['value'] && 'after' === $load_more_button_icon_position ) { ?>
						<span class="pp-button-icon pp-icon">
							<?php \Elementor\Icons_Manager::render_icon( $settings['pagination_load_more_button_icon'], [ 'aria-hidden' => 'true' ] ); ?>
						</span>
					<?php } ?>
				</a>
			</div>
			<?php
		}
	}

	/**
	 * Get post query arguments.
	 *
	 * @access protected
	 */
	protected function get_posts_query_arguments( $filter = '', $taxonomy_filter = '', $search = '', $all_posts = '', $paged_args = '', $posts_count_var = '', $posts_count = '' ) {
		$settings    = $this->get_settings();
		$paged       = ( 'yes' === $paged_args ) ? $this->get_paged() : '';
		$tax_count   = 0;

		// Query Arguments
		$args = array(
			'post_status'           => array( 'publish' ),
			'post_type'             => $settings['post_type'],
			'orderby'               => $settings['orderby'],
			'order'                 => $settings['order'],
			'offset'                => $settings['offset'],
			'ignore_sticky_posts'   => ( 'yes' === $settings['sticky_posts'] ) ? 0 : 1,
			'posts_per_page'        => -1,
		);

		if ( ! $posts_count ) {
			$posts_per_page = ( $posts_count_var ) ? $settings[ $posts_count_var ] : ( isset( $settings['posts_per_page'] ) ? $settings['posts_per_page'] : '' );
		} else {
			$posts_per_page = $posts_count;
		}

		if ( '' === $all_posts ) {
			$args['posts_per_page'] = $posts_per_page;
		}

		$args['paged'] = $paged;

		// Author Filter
		if ( ! empty( $settings['authors'] ) ) {
			$args[ $settings['author_filter_type'] ] = $settings['authors'];
		}

		// Posts Filter
		$post_type = $settings['post_type'];

		if ( 'post' === $post_type ) {
			$posts_control_key = 'exclude_posts';
		} else {
			$posts_control_key = $post_type . '_filter';
		}

		if ( ! empty( $settings[ $posts_control_key ] ) ) {
			$args[ $settings[ $post_type . '_filter_type' ] ] = $settings[ $posts_control_key ];
		}

		// Taxonomy Filter
		$taxonomy = PP_Posts_Helper::get_post_taxonomies( $post_type );

		if ( ! empty( $taxonomy ) && ! is_wp_error( $taxonomy ) ) {

			foreach ( $taxonomy as $index => $tax ) {

				if ( 'post' === $post_type ) {
					if ( 'post_tag' === $index ) {
						$tax_control_key = 'tags';
					} elseif ( 'category' === $index ) {
						$tax_control_key = 'categories';
					} else {
						$tax_control_key = $index . '_' . $post_type;
					}
				} else {
					$tax_control_key = $index . '_' . $post_type;
				}

				if ( ! empty( $settings[ $tax_control_key ] ) ) {

					$operator = $settings[ $index . '_' . $post_type . '_filter_type' ];

					$args['tax_query'][] = [
						'taxonomy' => $index,
						'field'    => 'term_id',
						'terms'    => $settings[ $tax_control_key ],
						'operator' => $operator,
					];
				}
			}
		}

		if ( '' !== $filter && '*' !== $filter ) {
			// Taxonomy Filter.
			$taxonomy = PP_Posts_Helper::get_post_taxonomies( $post_type );

			$tax_cat_in     = '';
			$tax_cat_not_in = '';
			$tax_tag_in     = '';
			$tax_tag_not_in = '';

			if ( ! empty( $taxonomy ) && ! is_wp_error( $taxonomy ) ) {

				foreach ( $taxonomy as $index => $tax ) {

					$tax_control_key = $index . '_' . $post_type;

					if ( 'yes' === $old_code ) {
						if ( 'post' === $post_type ) {
							if ( 'post_tag' === $index ) {
								$tax_control_key = 'tags';
							} elseif ( 'category' === $index ) {
								$tax_control_key = 'categories';
							}
						}
					}

					if ( ! empty( $settings[ $tax_control_key ] ) ) {

						$operator = $settings[ $index . '_' . $post_type . '_filter_type' ];

						$args['tax_query'][] = array(
							'taxonomy' => $index,
							'field'    => 'term_id',
							'terms'    => $settings[ $tax_control_key ],
							'operator' => $operator,
						);

						switch ( $index ) {
							case 'category':
								if ( 'IN' === $operator ) {
									$tax_cat_in = $settings[ $tax_control_key ];
								} elseif ( 'NOT IN' === $operator ) {
									$tax_cat_not_in = $settings[ $tax_control_key ];
								}
								break;

							case 'post_tag':
								if ( 'IN' === $operator ) {
									$tax_tag_in = $settings[ $tax_control_key ];
								} elseif ( 'NOT IN' === $operator ) {
									$tax_tag_not_in = $settings[ $tax_control_key ];
								}
								break;
						}
					}
				}
			}

			$args['tax_query'][ $tax_count ]['taxonomy'] = $taxonomy_filter;
			$args['tax_query'][ $tax_count ]['field']    = 'slug';
			$args['tax_query'][ $tax_count ]['terms']    = $filter;
			$args['tax_query'][ $tax_count ]['operator'] = 'IN';

			/* if ( ! empty( $tax_cat_in ) ) {
				$args['category__in'] = $tax_cat_in;
			}

			if ( ! empty( $tax_cat_not_in ) ) {
				$args['category__not_in'] = $tax_cat_not_in;
			}

			if ( ! empty( $tax_tag_in ) ) {
				$args['tag__in'] = $tax_tag_in;
			}

			if ( ! empty( $tax_tag_not_in ) ) {
				$args['tag__not_in'] = $tax_tag_not_in;
			} */
		}

		if ( 'anytime' !== $settings['select_date'] ) {
			$select_date = $settings['select_date'];
			if ( ! empty( $select_date ) ) {
				$date_query = [];
				switch ( $select_date ) {
					case 'today':
						$date_query['after'] = '-1 day';
						break;

					case 'week':
						$date_query['after'] = '-1 week';
						break;

					case 'month':
						$date_query['after'] = '-1 month';
						break;

					case 'quarter':
						$date_query['after'] = '-3 month';
						break;

					case 'year':
						$date_query['after'] = '-1 year';
						break;

					case 'exact':
						$after_date = $settings['date_after'];
						if ( ! empty( $after_date ) ) {
							$date_query['after'] = $after_date;
						}
						$before_date = $settings['date_before'];
						if ( ! empty( $before_date ) ) {
							$date_query['before'] = $before_date;
						}
						$date_query['inclusive'] = true;
						break;
				}

				$args['date_query'] = $date_query;
			}
		}

		if ( '' !== $search ) {
			$args['s'] = $search;
		}

		// Sticky Posts Filter
		if ( 'yes' === $settings['sticky_posts'] && 'yes' === $settings['all_sticky_posts'] ) {
			$post__in = get_option( 'sticky_posts' );

			$args['post__in'] = $post__in;
		}

		return $args;
	}

	/**
	 * Get custom post excerpt.
	 *
	 * @access protected
	 */
	protected function get_coupons_post_content( $limit = '' ) {
		$settings = $this->get_settings();

		if ( 'excerpt' === $settings['posts_content_type'] ) {
			$content = explode( ' ', get_the_excerpt(), $limit );

			if ( count( $content ) >= $limit ) {
				array_pop( $content );
				$content = implode( ' ', $content ) . '...';
			} else {
				$content = implode( ' ', $content );
			}

			$content = preg_replace( '`[[^]]*]`', '', $content );
		} else {
			$content = get_the_content();
		}

		return $content;
	}

	/**
	 * Render current query.
	 *
	 * @since 2.3.7
	 * @access protected
	 */
	public function get_query( $filter = '', $taxonomy_filter = '', $search = '', $all_posts = '', $paged_args = '' ) {

		$args = $this->get_posts_query_arguments( $filter, $taxonomy_filter, $search, $all_posts, $paged_args );
		$query = new \WP_Query( $args );

		return $query;
	}

	/**
	 * Render posts output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function get_coupons_posts( $filter = '', $taxonomy = '', $search = '', $all_posts = '', $paged_args = '' ) {
		$settings = $this->get_settings();

		$i = 0;
		$coupons = array();

		// Query Arguments
		$posts_query = $this->get_query( $filter, $taxonomy, $search, '', 'yes' );

		if ( $posts_query->have_posts() ) :
			while ( $posts_query->have_posts() ) :
				$posts_query->the_post();

				$limit = $settings['excerpt_length'];
				$coupon = ( $settings['coupon_custom_field'] ) ? get_post_meta( get_the_ID(), $settings['coupon_custom_field'], true ) : '';
				$discount_code = ( $settings['discount_custom_field'] ) ? get_post_meta( get_the_ID(), $settings['discount_custom_field'], true ) : '';

				$coupons[ $i ]['coupon_code'] = ( $coupon ) ? $coupon : '';
				$coupons[ $i ]['discount'] = ( $discount_code ) ? $discount_code : '';
				$coupons[ $i ]['title'] = get_the_title();
				$coupons[ $i ]['description'] = $this->get_coupons_post_content( $limit );
				$coupons[ $i ]['image']['id'] = get_post_thumbnail_id();
				$coupons[ $i ]['image']['url'] = get_the_post_thumbnail_url();
				$coupons[ $i ]['icon_type'] = $settings['icon_type'];
				$coupons[ $i ]['link_type'] = $settings['link_type'];
				$coupons[ $i ]['link']['url'] = get_permalink();
				$coupons[ $i ]['link']['is_external'] = '';
				$coupons[ $i ]['link']['nofollow'] = '';
				$coupons[ $i ]['link']['custom_attributes'] = '';

				$i++;
			endwhile;
		endif;
		wp_reset_postdata();

		return $coupons;
	}

	/**
	* Render custom coupons output on the frontend.
	*
	* Written in PHP and used to generate the final HTML.
	*
	* @access protected
	*/
	protected function get_coupons_custom() {
		$settings = $this->get_settings_for_display();

		$coupons = array();

		foreach ( $settings['pp_coupons'] as $index => $item ) {
			$coupons[ $index ]['coupon_code'] = $item['coupon_code'];
			$coupons[ $index ]['discount'] = $item['discount'];
			$coupons[ $index ]['title'] = $item['title'];
			$coupons[ $index ]['description'] = $item['description'];
			$coupons[ $index ]['image']['id'] = $item['image']['id'];
			$coupons[ $index ]['image']['url'] = $item['image']['url'];
			$coupons[ $index ]['icon_type'] = $settings['icon_type'];
			$coupons[ $index ]['link_type'] = $settings['link_type'];
			$coupons[ $index ]['link']['url'] = $item['link']['url'];
			$coupons[ $index ]['link']['is_external'] = $item['link']['is_external'];
			$coupons[ $index ]['link']['nofollow'] = $item['link']['nofollow'];
			$coupons[ $index ]['link']['custom_attributes'] = $item['link']['custom_attributes'];
		}

		return $coupons;
	}

	protected function get_coupons( $filter = '', $taxonomy = '', $search = '' ) {
		$settings = $this->get_settings_for_display();

		if ( 'posts' === $settings['source'] ) {

			return $this->get_coupons_posts( $filter, $taxonomy, $search );

		} elseif ( 'custom' === $settings['source'] ) {

			return $this->get_coupons_custom();

		}
	}

	protected function render_coupons( $filter = '', $taxonomy = '', $search = '' ) {
		$settings = $this->get_settings_for_display();

		$coupons = $this->get_coupons( $filter, $taxonomy, $search );

		if ( empty( $coupons ) ) {
			return;
		}

		$title_html_tag = 'div';
		$button_html_tag = 'div';

		foreach ( $coupons as $index => $item ) :

			if ( 'none' !== $settings['link_type'] ) {
				if ( ! empty( $item['link']['url'] ) ) {

					$this->add_link_attributes( 'link' . $index, $item['link'] );

					if ( 'title' === $settings['link_type'] ) {
						$title_html_tag = 'a';
					} elseif ( 'button' === $settings['link_type'] ) {
						$button_html_tag = 'a';
					}
				}
			}

			$this->add_render_attribute( 'title-container' . $index, 'class', 'pp-coupon-title-container' );

			$this->add_render_attribute(
				'coupon-code-' . $index,
				[
					'class' => [ 'pp-coupon-code', 'pp-coupon-style-' . $settings['coupon_style'] ],
					'data-coupon-code' => $item['coupon_code'],
				]
			);
			?>
			<div <?php $this->print_render_attribute_string( 'coupon' ); ?>>
				<?php
				if ( $item['image']['url'] ) {
					$image_url = Group_Control_Image_Size::get_attachment_image_src( $item['image']['id'], 'image', $settings );

					if ( ! $image_url ) {
						$image_url = $item['image']['url'];
					}
					?>
					<div class="pp-coupon-image-wrapper">
						<?php if ( 'yes' === $settings['show_discount'] && $item['discount'] ) { ?>
							<div class="pp-coupon-discount">
								<?php echo wp_kses_post( $item['discount'] ); ?>
							</div>
						<?php } ?>

						<?php if ( $item['coupon_code'] ) { ?>
							<div <?php $this->print_render_attribute_string( 'coupon-code-' . $index ); ?>>
								<?php $this->render_coupon( $item ); ?>
							</div>
						<?php } ?>

						<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( Control_Media::get_image_alt( $item['image'] ) ); ?>">
					</div>
					<?php
				}
				?>
				
				<?php if ( 'box' === $settings['link_type'] ) { ?>
					<a <?php $this->print_render_attribute_string( 'link' . $index ); ?>>
				<?php } ?>
				<div class="pp-coupon-content">
					<div class="pp-coupon-title-wrap">
						<?php
						if ( ! empty( $item['title'] ) ) {
							$title_tag = PP_Helper::validate_html_tag( $settings['title_html_tag'] );

							printf( '<%1$s %2$s %3$s>', esc_html( $title_html_tag ), wp_kses_post( $this->get_render_attribute_string( 'title-container' . $index ) ), wp_kses_post( $this->get_render_attribute_string( 'link' . $index ) ) );
							printf( '<%1$s class="pp-coupon-title">', esc_html( $title_tag ) );
							echo wp_kses_post( $item['title'] );
							printf( '</%1$s>', esc_html( $title_tag ) );
							printf( '</%1$s>', esc_html( $title_html_tag ) );
						}
						?>
					</div>

					<?php if ( ! empty( $item['description'] ) ) { ?>
						<div class="pp-coupon-description">
							<?php echo $this->parse_text_editor( nl2br( $item['description'] ) ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					<?php } ?>

					<?php if ( 'yes' === $settings['button_separator'] ) { ?>
						<hr class="pp-coupon-separator">
					<?php } ?>

					<?php if ( 'button' === $settings['link_type'] ) { ?>
						<div class="pp-coupon-button-wrap">
							<a <?php $this->print_render_attribute_string( 'coupon-button' . $index ) ?> <?php $this->print_render_attribute_string( 'link' . $index ); ?>>
								<div <?php $this->print_render_attribute_string( 'coupon-button' ); ?>>
									<?php
									if ( 'before' === $settings['button_icon_position'] ) {
										$this->render_coupon_button_icon();
									}
									?>
									<?php if ( ! empty( $settings['button_text'] ) ) { ?>
										<span class="pp-button-text">
											<?php echo esc_attr( $settings['button_text'] ); ?>
										</span>
									<?php } ?>
									<?php
									if ( 'after' === $settings['button_icon_position'] ) {
										$this->render_coupon_button_icon();
									}
									?>
								</div>
							</a>
						</div>
					<?php } ?>
					<?php if ( 'box' === $settings['link_type'] ) { ?>
						</a>
					<?php } ?>
				</div>
			</div>
			<?php
		endforeach;
	}

	/**
	 * Render coupons widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$pagination_type     = $settings['coupons_pagination_type'];
		$query_type          = 'custom';
		$layout              = $settings['layout'];
		$skin                = $this->get_id();
		$page_id             = '';
		if ( null !== \Elementor\Plugin::$instance->documents->get_current() ) {
			$page_id = \Elementor\Plugin::$instance->documents->get_current()->get_main_id();
		}

		$this->add_render_attribute( 'container', 'class', 'pp-coupons' );

		$query = $this->get_query( '', '', '', '', 'yes' );

		// Filters
		if ( 'related' !== $settings['post_type'] && $query->found_posts ) {
			$this->render_filters();
		}

		if ( 'carousel' === $settings['layout'] ) {
			$this->slider_settings();
		}

		$this->add_render_attribute( 'coupon', 'class', 'pp-coupon' );

		if ( 'carousel' === $settings['layout'] ) {
			$this->add_render_attribute(
				[
					'container' => [
						'class' => [ 'swiper-container', 'pp-swiper-slider', 'pp-coupons-carousel', 'swiper' ],
					],
					'wrapper' => [
						'class' => 'swiper-wrapper'
					],
					'coupon' => [
						'class' => 'swiper-slide'
					]
				]
			);

			if ( $settings['dots_position'] ) {
				$this->add_render_attribute( 'container', 'class', 'swiper-container-wrap-dots-' . $settings['dots_position'] );
			} elseif ( 'fraction' === $settings['pagination_type'] ) {
				$this->add_render_attribute( 'container', 'class', 'swiper-container-wrap-dots-outside' );
			}

		} else {
			$this->add_render_attribute( 'container', 'class', 'pp-coupons-grid' );

			if ( 'infinite' === $pagination_type ) {
				$this->add_render_attribute( 'container', 'class', 'pp-coupons-infinite-scroll' );
			}

			$this->add_render_attribute(
				[
					'wrapper' => [
						'class'           => [ 'elementor-grid', 'pp-coupons-grid-wrapper' ],
						'data-query-type' => $query_type,
						'data-layout'     => $layout,
						'data-page'       => $page_id,
						'data-skin'       => $skin,
					],
					'coupon' => [
						'class' => 'elementor-grid-item'
					]
				]
			);
		}

		if ( is_rtl() ) {
			$this->add_render_attribute( 'container', 'dir', 'rtl' );
		}

		$this->add_render_attribute( 'coupon-button', 'class', [
			'pp-coupon-button',
			'elementor-button',
			'elementor-size-' . $settings['button_size'],
		] );

		if ( $settings['button_hover_animation'] ) {
			$this->add_render_attribute( 'coupon-button', 'class', 'elementor-animation-' . $settings['button_hover_animation'] );
		}

		$this->add_render_attribute( 'icon', 'class', [ 'pp-coupon-code-icon', 'pp-icon' ] );
		?>
		<div <?php $this->print_render_attribute_string( 'container' ); ?>>
			<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
				<?php $coupons = $this->render_coupons(); ?>
			</div>
			<?php
			if ( 'posts' === $settings['source'] ) {
				?>
				<div class="pp-posts-pagination-wrap pp-posts-pagination-bottom">
					<?php
						$coupons = $this->render_pagination();
					?>
				</div>
				<?php
				if ( 'load_more' === $pagination_type || 'infinite' === $pagination_type ) { 
					?>
					<div class="pp-posts-loader"></div>
					<?php
				}
			}

			if ( 'carousel' === $settings['layout'] ) {
				$this->render_dots();

				$this->render_arrows();
			}
			?>
		</div>
		<?php
	}

	/**
	 * Render caoupon icon output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_coupon( $item ) {
		$settings = $this->get_settings_for_display();
		?>
		<?php if ( 'copy' === $settings['coupon_style'] ) { ?>
			<span class="pp-coupon-code-text">
				<?php
				if ( 'none' !== $settings['icon_type'] ) {
					$this->render_coupon_icon();
				}

				echo wp_kses_post( $item['coupon_code'] );
				?>
			</span>
			<span class="pp-coupon-copy-text">
				<?php echo esc_attr__( 'Copy', 'powerpack' ); ?>
			</span>
		<?php } elseif ( 'reveal' === $settings['coupon_style'] ) { ?>
			<?php
				// Trim coupon code for Reveal style
				$str    = $item['coupon_code'];
				$strlth = strlen( $item['coupon_code'] );
			if ( 1 === $strlth ) {
				$str = $item['coupon_code'];
			} elseif ( 3 >= $strlth ) {
				$str = substr( $str, 1 );
			} else {
				$strcut = $strlth - 3;
				$str = substr( $str, $strcut );
			}
			?>
			<div class='pp-coupon-reveal-wrap'>
				<span class='pp-coupon-reveal'>
					<?php
					if ( 'none' !== $settings['icon_type'] ) {
						$this->render_coupon_icon();
					}

					echo wp_kses_post( $settings['coupon_reveal'] );
					?>
				</span>
			</div>
			<div class='pp-coupon-code-text-wrap pp-unreavel'>
				<span class='pp-coupon-code-text' id='pp-coupon-code-<?php echo esc_attr( $this->get_id() ); ?>'><?php echo esc_attr( $str ); ?></span>
				<span class='pp-coupon-copy-text'style='display: none;'></span>
			</div>
		<?php } else { ?>
			<span class='pp-coupon-code-no-code' id='pp-coupon-code-<?php echo esc_attr( $this->get_id() ); ?>'>
				<?php
				if ( 'none' !== $settings['icon_type'] ) {
					$this->render_coupon_icon();
				}

				echo wp_kses_post( $settings['no_code_need'] );
				?>
			</span>
		<?php }
	}

	/**
	 * Render caoupon icon output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_coupon_icon() {
		$settings = $this->get_settings_for_display();
		?>
		<span <?php $this->print_render_attribute_string( 'icon' ); ?>>
			<?php if ( 'icon' === $settings['icon_type'] ) { ?>
				<?php
				if ( ! empty( $settings['icon']['value'] ) ) {
					Icons_Manager::render_icon( $settings['icon'], [ 'aria-hidden' => 'true' ] );
				}
				?>
			<?php } elseif ( 'image' === $settings['icon_type'] ) { ?>
				<?php
				if ( ! empty( $settings['icon_image']['url'] ) ) {
					$image_url = Group_Control_Image_Size::get_attachment_image_src( $settings['icon_image']['id'], 'thumbnail', $settings );

					if ( $image_url ) {
						echo '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( Control_Media::get_image_alt( $settings['icon_image'] ) ) . '">';
					} else {
						echo '<img src="' . esc_url( $settings['icon_image']['url'] ) . '">';
					}
				}
				?>
			<?php } ?>
		</span>
		<?php
	}

	/**
	 * Render coupon button icon output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_coupon_button_icon() {
		$settings = $this->get_settings_for_display();

		if ( ! empty( $settings['button_icon']['value'] ) ) {
			?>
			<span class="pp-button-icon pp-icon">
				<?php
					Icons_Manager::render_icon( $settings['button_icon'], [ 'aria-hidden' => 'true' ] );
				?>
			</span>
			<?php
		}
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
		<?php }
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

	public function query_posts( $filter = '', $taxonomy = '', $search = '', $all_posts = '', $paged_args = '' ) {
		$settings = $this->get_settings_for_display();
		$query_id = $settings['query_id'];

		if ( ! empty( $query_id ) ) {
			add_action( 'pre_get_posts', array( $this, 'pre_get_posts_query_filter' ) );
		}

		$query_args  = $this->get_posts_query_arguments( $filter, $taxonomy, $search, '', 'yes' );

		$this->query = new \WP_Query( $query_args );
		remove_action( 'pre_get_posts', array( $this, 'pre_get_posts_query_filter' ) );
	}

	/**
	 * Render post body output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	public function render_ajax_post_body( $filter = '', $taxonomy = '', $search = '' ) {
		$settings   = $this->get_settings_for_display();
		$query_type = 'custom';
		$layout     = $settings['layout'];
		$skin       = $this->get_id();
		$page_id    = '';

		ob_start();
		$this->query_posts( $filter, $taxonomy, $search );
		$this->add_render_attribute( 'container', 'class', 'pp-coupons-grid' );

		if ( 'infinite' === $settings['coupons_pagination_type'] ) {
			$this->add_render_attribute( 'container', 'class', 'pp-coupons-infinite-scroll' );
		}

		$this->add_render_attribute(
			'wrapper',
			array(
				'class'           => [ 'elementor-grid', 'pp-coupons-grid-wrapper' ],
				'data-query-type' => $query_type,
				'data-layout'     => $layout,
				'data-page'       => $page_id,
				'data-skin'       => $skin,
			)
		);

		$this->add_render_attribute( 'coupon', 'class', [ 'pp-coupon', 'elementor-grid-item' ] );

		if ( is_rtl() ) {
			$this->add_render_attribute( 'container', 'dir', 'rtl' );
		}

		$this->add_render_attribute( 'coupon-button', 'class', [
			'pp-coupon-button',
			'elementor-button',
			'elementor-size-' . $settings['button_size'],
		] );

		if ( $settings['button_hover_animation'] ) {
			$this->add_render_attribute( 'coupon-button', 'class', 'elementor-animation-' . $settings['button_hover_animation'] );
		}

		$this->add_render_attribute( 'icon', 'class', [ 'pp-coupon-code-icon', 'pp-icon' ] );
		$this->render_coupons( $filter, $taxonomy, $search );

		return ob_get_clean();
	}

	/**
	 * pre_get_posts_query_filter
	 *
	 * @param  mixed $wp_query
	 */
	public function pre_get_posts_query_filter( $wp_query ) {
		$settings = $this->get_settings_for_display();

		$query_id = $settings['query_id'];
		/**
		 * Query args.
		 *
		 * It allows developers to alter individual posts widget queries.
		 *
		 * The dynamic portion of the hook name '$query_id', refers to the Query ID.
		 *
		 * @since 1.4.11.3
		 *
		 * @param \WP_Query     $wp_query
		 */
		do_action_deprecated( "pp_query_{$query_id}", [ $wp_query ], '2.6.1', "powerpack/query/{$query_id}" );
		do_action( "powerpack/query/{$query_id}", $wp_query, $this );

	}

	public function query_filters_posts( $filter = '', $taxonomy = '', $search = '' ) {
		$settings = $this->get_settings();
		$query_id = $settings['query_id'];

		if ( ! empty( $query_id ) ) {
			add_action( 'pre_get_posts', array( $this, 'pre_get_posts_query_filter' ) );
		}
		$query_filter_args   = $this->get_posts_query_arguments( $filter, $taxonomy, $search, 'yes', 'yes' );
		$this->query_filters = new \WP_Query( $query_filter_args );
		remove_action( 'pre_get_posts', array( $this, 'pre_get_posts_query_filter' ) );
	}

	/**
	 * Render current query.
	 *
	 * @since 1.7.0
	 * @access protected
	 */
	public function get_query_filters() {
		return $this->query_filters;
	}

	/**
	 * Get Filter taxonomy array.
	 *
	 * Returns the Filter array of objects.
	 *
	 * @since 1.7.0
	 * @access public
	 */
	public function get_filter_taxonomies() {

		$settings = $this->get_settings();

		$post_type = $settings['post_type'];

		$filter_by = $settings['tax_' . $post_type . '_filter'];

		return $filter_by;
	}

	/**
	 * Get Filter taxonomy array.
	 *
	 * Returns the Filter array of objects.
	 *
	 * @since 1.7.0
	 * @access public
	 */
	public function get_filter_values() {
		$settings = $this->get_settings_for_display();

		$post_type       = $settings['post_type'];
		$filter_by       = $settings['tax_' . $post_type . '_filter'];
		$filters_orderby = $settings['filters_orderby'];
		$filters_order   = $settings['filters_order'];
		$taxonomy        = $this->get_filter_taxonomies();
		$filters         = array();
		$terms_ids       = array();

		$this->query_filters_posts( $filter = '', $taxonomy, $search = '' );

		$query = $this->get_query_filters();

		foreach ( $query->posts as $post ) {
			$post_terms = wp_get_post_terms(
				$post->ID,
				$taxonomy
			);

			foreach ( $post_terms as $post_term ) {
				$terms_ids[] = $post_term->term_id;
			}
		}

		if ( ! empty( $terms_ids ) ) {
			$terms_ids = array_unique( $terms_ids );

			$post_all_terms = get_terms(
				array(
					'include' => $terms_ids,
					'orderby' => $filters_orderby,
					'order'   => $filters_order,
				)
			);

			foreach ( $post_all_terms as $post_term ) {
				$filters[ $post_term->slug ] = $post_term;
			}
		}
		return apply_filters( 'ppe_posts_filters', $filters, $filters );
	}

	/**
	 * Render Filters.
	 *
	 * Returns the Filter HTML.
	 *
	 * @since 1.7.0
	 * @access public
	 */
	public function render_filters() {
		$settings = $this->get_settings_for_display();

		$layout                = $settings['layout'];
		$show_filters          = $settings['show_filters'];
		$show_filters_count    = $settings['show_filters_count'];
		// $show_ajax_search_form = $settings['show_ajax_search_form'];
		// $search_form_action    = $settings['search_form_action'];

		if ( 'carousel' === $layout ) {
			return;
		}

		// if ( 'yes' !== $show_filters && 'yes' !== $show_ajax_search_form ) {
		if ( 'yes' !== $show_filters ) {
			return;
		}

		$filters   = $this->get_filter_values();
		$all_label = $settings['filter_all_label'];

		$this->add_render_attribute( 'filters-container', 'class', 'pp-post-filters-container' );

		/* if ( 'yes' === $show_ajax_search_form ) {
			$this->add_render_attribute(
				'filters-container',
				array(
					'data-search-form'   => 'show',
					'data-search-action' => $search_form_action,
				)
			);
		} */

		$enable_active_filter = $settings['enable_active_filter'];
		if ( 'yes' === $enable_active_filter ) {
			$filter_active = $settings['filter_active'];
		}
		?>
		<div <?php $this->print_render_attribute_string( 'filters-container' ); ?>>
			<?php
			if ( 'yes' === $show_filters ) {
				$this->add_render_attribute( 'pp-post-filters-wrap', 'class', [ 'pp-post-filters-wrap', 'pp-post-filters-dropdown-' . $settings['responsive_support'] ] );
			?>
			<div <?php $this->print_render_attribute_string( 'pp-post-filters-wrap' ); ?>>
				<ul class="pp-post-filters <?php echo ( 'no' !== $settings['responsive_support'] ) ? 'pp-has-post-filters-dropdown' : '' ; ?>">
					<li class="pp-post-filter <?php echo ( 'yes' === $enable_active_filter ) ? '' : 'pp-filter-current'; ?>" data-filter="*" data-taxonomy=""><?php echo ( 'All' === $all_label || '' === $all_label ) ? esc_attr__( 'All', 'powerpack' ) : esc_attr( $all_label ); ?></li>
					<?php foreach ( $filters as $key => $value ) { ?>
						<?php
						if ( 'yes' === $show_filters_count ) {
							$filter_value = $value->name . '<span class="pp-post-filter-count">' . $value->count . '</span>';
						} else {
							$filter_value = $value->name;
						}
						?>
						<?php if ( 'yes' === $enable_active_filter && ( $key === $filter_active ) ) { ?>
							<li class="pp-post-filter pp-filter-current" data-filter="<?php echo '.' . esc_attr( $value->slug ); ?>" data-taxonomy="<?php echo '.' . esc_attr( $value->taxonomy ); ?>"><?php echo wp_kses_post( $filter_value ); ?></li>
						<?php } else { ?>
							<li class="pp-post-filter" data-filter="<?php echo '.' . esc_attr( $value->slug ); ?>" data-taxonomy="<?php echo '.' . esc_attr( $value->taxonomy ); ?>"><?php echo wp_kses_post( $filter_value ); ?></li>
							<?php
						}
					}
					?>
				</ul>

				<?php
				if ( 'no' !== $settings['responsive_support'] ) {
					$this->add_render_attribute( 'pp-post-filters-dropdown', 'class', 'pp-post-filters-dropdown' ); ?>
					<div <?php $this->print_render_attribute_string( 'pp-post-filters-dropdown' ); ?>>
						<div class="pp-post-filters-dropdown-button"><?php echo ( 'All' === $all_label || '' === $all_label ) ? esc_attr__( 'All', 'powerpack' ) : esc_attr( $all_label ); ?></div>
						<ul class="pp-post-filters-dropdown-list">
							<li class="pp-post-filters-dropdown-item <?php echo ( 'yes' === $enable_active_filter ) ? '' : 'pp-filter-current'; ?>" data-filter="*" data-taxonomy=""><?php echo ( 'All' === $all_label || '' === $all_label ) ? esc_attr__( 'All', 'powerpack' ) : esc_attr( $all_label ); ?></li>
							<?php foreach ( $filters as $key => $value ) { ?>
								<?php
								if ( 'yes' === $show_filters_count ) {
									$filter_value = $value->name . '<span class="pp-post-filter-count">' . $value->count . '</span>';
								} else {
									$filter_value = $value->name;
								}
								?>
								<?php if ( 'yes' === $enable_active_filter && ( $key === $filter_active ) ) { ?>
									<li class="pp-post-filters-dropdown-item pp-filter-current" data-filter="<?php echo '.' . esc_attr( $value->slug ); ?>" data-taxonomy="<?php echo '.' . esc_attr( $value->taxonomy ); ?>"><?php echo wp_kses_post( $filter_value ); ?></li>
								<?php } else { ?>
									<li class="pp-post-filters-dropdown-item " data-filter="<?php echo '.' . esc_attr( $value->slug ); ?>" data-taxonomy="<?php echo '.' . esc_attr( $value->taxonomy ); ?>"><?php echo wp_kses_post( $filter_value ); ?></li>
									<?php
								}
							}
							?>
						</ul>
					</div>
				<?php } ?>
			</div>
			<?php } ?>
		</div>
		<?php
	}
}
