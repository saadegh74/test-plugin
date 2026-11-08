<?php
namespace PowerpackElements\Modules\Faq\Widgets;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Classes\PP_Posts_Helper;
use PowerpackElements\Classes\PP_Config;
use PowerpackElements\Classes\PP_Helper;

// Elementor Classes
use Elementor\Controls_Manager;
use Elementor\Control_Media;
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
use Elementor\Core\Schemes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * FAQ Widget
 */
class Faq extends Powerpack_Widget {

	/**
	 * Retrieve FAQ widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Faq' );
	}

	/**
	 * Retrieve FAQ widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Faq' );
	}

	/**
	 * Retrieve FAQ widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Faq' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the FAQ widget belongs to.
	 *
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'Faq' );
	}

	/**
	 * Retrieve the list of scripts the FAQ widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return [
			'powerpack-frontend',
		];
	}

	/**
	 * Register FAQ widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 2.0.3
	 * @access protected
	 */
	protected function register_controls() {
		/* Content Tab */
		$this->register_content_faqs_controls();
		$this->register_content_query_controls();
		$this->register_content_settings_controls();
		$this->register_content_toggle_icon_controls();
		$this->register_content_help_docs_controls();

		/* Style Tab */
		$this->register_style_faq_items_controls();
		$this->register_style_questions_controls();
		$this->register_style_answers_controls();
		$this->register_style_toggle_icon_controls();
	}

	/*-----------------------------------------------------------------------------------*/
	/*	CONTENT TAB
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Content Tab: FAQs
	 */
	protected function register_content_faqs_controls() {
		$this->start_controls_section(
			'section_accordion_faqs',
			[
				'label'                 => esc_html__( 'FAQs', 'powerpack' ),
			]
		);

		$source_options = array(
			'custom'     => __( 'Custom', 'powerpack' ),
			'posts'      => __( 'Posts', 'powerpack' ),
		);

		if ( class_exists( 'acf' ) ) {
			$source_options['acf'] = __( 'ACF Repeater Field', 'powerpack' );
		}

		$this->add_control(
			'source',
			[
				'label'                 => __( 'Source', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'options'               => $source_options,
				'default'               => 'custom',
			]
		);

		$this->add_control(
			'source_divider',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$repeater = new Repeater();

		$repeater->start_controls_tabs( 'accordion_tabs_content_tabs' );

			$repeater->start_controls_tab(
				'accordion_tabs_content_tab',
				[
					'label' => __( 'Content', 'powerpack' ),
				]
			);

				$repeater->add_control(
					'tab_title',
					[
						'label'                 => __( 'Question', 'powerpack' ),
						'type'                  => Controls_Manager::TEXT,
						'default'               => __( 'Accordion Title', 'powerpack' ),
						'dynamic'               => [
							'active'   => true,
						],
					]
				);

				$repeater->add_control(
					'faq_answer',
					[
						'label'                 => esc_html__( 'Answer', 'powerpack' ),
						'type'                  => Controls_Manager::WYSIWYG,
						'default'               => esc_html__( 'Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'powerpack' ),
						'dynamic'               => [ 'active' => true ],
					]
				);

				$repeater->add_control(
					'accordion_tab_default_active',
					[
						'label'                 => esc_html__( 'Active as Default', 'powerpack' ),
						'type'                  => Controls_Manager::SWITCHER,
						'default'               => 'no',
						'return_value'          => 'yes',
					]
				);

			$repeater->end_controls_tab();

			$repeater->start_controls_tab(
				'accordion_tabs_icon_tab',
				[
					'label' => __( 'Icon', 'powerpack' ),
				]
			);

				$repeater->add_control(
					'question_icon',
					[
						'label'                 => __( 'Icon', 'powerpack' ),
						'type'                  => Controls_Manager::ICONS,
						'label_block'           => true,
						'fa4compatibility'      => 'accordion_question_icon',
					]
				);

			$repeater->end_controls_tab();

			$repeater->start_controls_tab(
				'accordion_tabs_advanced_tab',
				[
					'label' => __( 'Advanced', 'powerpack' ),
				]
			);

				$repeater->add_control(
					'accordion_tab_id',
					[
						'label'                 => __( 'Custom CSS ID', 'powerpack' ),
						'description'           => __( 'This CSS ID will be applied to ID attribute of this tab in HTML. It should only contain dashes, underscores, letters or numbers. No spaces. Also make sure to use different ID for each tab.', 'powerpack' ),
						'type'                  => Controls_Manager::TEXT,
						'label_block'           => true,
						'default'               => '',
					]
				);

			$repeater->end_controls_tab();

		$repeater->end_controls_tabs();

		$this->add_control(
			'tabs',
			[
				'label'                 => esc_html__( 'Add FAQs', 'powerpack' ),
				'type'                  => Controls_Manager::REPEATER,
				'default'               => [
					[ 'tab_title' => esc_html__( 'FAQ Question 1', 'powerpack' ) ],
					[ 'tab_title' => esc_html__( 'FAQ Question 2', 'powerpack' ) ],
					[ 'tab_title' => esc_html__( 'FAQ Question 3', 'powerpack' ) ],
				],
				'fields'                => $repeater->get_controls(),
				'title_field'           => '{{tab_title}}',
				'condition'             => [
					'source'    => 'custom',
				],
			]
		);

		$this->add_control(
			'acf_repeater_name',
			[
				'label'                 => esc_html__( 'ACF Repeater Field Name', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'label_block'           => true,
				'default'               => '',
				'condition'             => [
					'source'    => 'acf',
				],
			]
		);

		$this->add_control(
			'acf_repeater_question',
			[
				'label'                 => esc_html__( 'ACF Repeater Sub Field Name (Question)', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'label_block'           => true,
				'default'               => '',
				'condition'             => [
					'source'    => 'acf',
				],
			]
		);

		$this->add_control(
			'acf_repeater_answer',
			[
				'label'                 => esc_html__( 'ACF Repeater Sub Field Name (Answer)', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'label_block'           => true,
				'default'               => '',
				'condition'             => [
					'source'    => 'acf',
				],
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label'                 => __( 'Posts Count', 'powerpack' ),
				'type'                  => Controls_Manager::NUMBER,
				'default'               => 4,
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
			'preview_excerpt_length',
			[
				'label'                 => __( 'Excerpt Length', 'powerpack' ),
				'type'                  => Controls_Manager::NUMBER,
				'default'               => 50,
				'min'                   => 0,
				'max'                   => 58,
				'step'                  => 1,
				'condition'             => [
					'source'                => 'posts',
					'posts_content_type'    => 'excerpt',
				],
			]
		);

		$this->add_control(
			'enable_schema',
			[
				'label'                 => esc_html__( 'Enable Schema', 'powerpack' ),
				'type'                  => Controls_Manager::SWITCHER,
				'default'               => 'yes',
				'return_value'          => 'yes',
				'separator'             => 'before',
			]
		);

		$this->add_control(
			'default_active_tabs',
			[
				'label'                 => __( 'Default Active Tabs', 'powerpack' ),
				'description'           => __( 'Add comma separated list of tab numbers.', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'default'               => '',
				'dynamic'               => [
					'active'   => true,
				],
				'condition'             => [
					'source'            => [ 'posts', 'acf' ],
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_query_controls() {
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
					'DESC'      => __( 'Descending', 'powerpack' ),
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

		$this->end_controls_section();
	}

	protected function register_content_settings_controls() {
		/**
		 * Content Tab: Settings
		 */
		$this->start_controls_section(
			'section_accordion_settings',
			[
				'label'                 => esc_html__( 'Settings', 'powerpack' ),
			]
		);

		$this->add_control(
			'faq_layout',
			[
				'label'                 => esc_html__( 'Layout', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'default'               => 'accordion',
				'label_block'           => false,
				'options'               => [
					'accordion'     => esc_html__( 'Accordion', 'powerpack' ),
					'grid'          => esc_html__( 'Grid', 'powerpack' ),
				],
				'frontend_available'    => true,
			]
		);

		$this->add_control(
			'accordion_type',
			[
				'label'                 => esc_html__( 'Accordion Type', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'default'               => 'accordion',
				'label_block'           => false,
				'options'               => [
					'accordion'     => esc_html__( 'Accordion', 'powerpack' ),
					'toggle'        => esc_html__( 'Toggle', 'powerpack' ),
				],
				'frontend_available'    => true,
				'condition'             => [
					'faq_layout'    => 'accordion',
				],
			]
		);

		$this->add_control(
			'toggle_speed',
			[
				'label'                 => esc_html__( 'Toggle Speed (ms)', 'powerpack' ),
				'type'                  => Controls_Manager::NUMBER,
				'label_block'           => false,
				'default'               => 300,
				'frontend_available'    => true,
				'condition'             => [
					'faq_layout'    => 'accordion',
				],
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label'                 => __( 'Columns', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'default'               => '2',
				'tablet_default'        => '2',
				'mobile_default'        => '1',
				'options'               => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				],
				'prefix_class'          => 'elementor-grid%s-',
				'frontend_available'    => true,
				'condition'             => [
					'faq_layout'    => 'grid',
				],
			]
		);

		$this->add_control(
			'question_html_tag',
			[
				'label'                 => __( 'Question HTML Tag', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'default'               => 'div',
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

		$this->add_control(
			'custom_id_prefix',
			[
				'label'       => __( 'Custom ID Prefix', 'powerpack' ),
				'description' => __( 'A prefix that will be applied to ID attribute of tabs in HTML. For example, prefix "mytab" will be applied as "mytab-1", "mytab-2" in ID attribute of Tab 1 and Tab 2 respectively. It should only contain dashes, underscores, letters or numbers. No spaces.', 'powerpack' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => '',
				'placeholder' => __( 'mytab', 'powerpack' ),
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_toggle_icon_controls() {
		/**
		 * Content Tab: Toggle Icon
		 */
		$this->start_controls_section(
			'section_accordion_toggle_icon',
			[
				'label'                 => esc_html__( 'Toggle Icon', 'powerpack' ),
				'condition'             => [
					'faq_layout'    => 'accordion',
				],
			]
		);

		$this->add_control(
			'toggle_icon_show',
			[
				'label'                 => esc_html__( 'Toggle Icon', 'powerpack' ),
				'type'                  => Controls_Manager::SWITCHER,
				'default'               => 'yes',
				'label_on'              => __( 'Show', 'powerpack' ),
				'label_off'             => __( 'Hide', 'powerpack' ),
				'return_value'          => 'yes',
				'condition'             => [
					'faq_layout'    => 'accordion',
				],
			]
		);

		$this->add_control(
			'select_toggle_icon',
			[
				'label'                 => __( 'Close Icon', 'powerpack' ),
				'type'                  => Controls_Manager::ICONS,
				'label_block'           => false,
				'skin'                  => 'inline',
				'fa4compatibility'      => 'toggle_icon_normal',
				'default'               => [
					'value' => 'fas fa-plus',
					'library' => 'fa-solid',
				],
				'condition'             => [
					'faq_layout'    => 'accordion',
					'toggle_icon_show' => 'yes',
				],
			]
		);

		$this->add_control(
			'select_toggle_icon_active',
			[
				'label'                 => __( 'Open Icon', 'powerpack' ),
				'type'                  => Controls_Manager::ICONS,
				'label_block'           => false,
				'skin'                  => 'inline',
				'fa4compatibility'      => 'toggle_icon_active',
				'default'               => [
					'value' => 'fas fa-minus',
					'library' => 'fa-solid',
				],
				'condition'             => [
					'faq_layout'    => 'accordion',
					'toggle_icon_show' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_help_docs_controls() {

		$help_docs = PP_Config::get_widget_help_links( 'Faq' );

		if ( ! empty( $help_docs ) ) {

			/**
			 * Content Tab: Help Docs
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

	protected function register_style_faq_items_controls() {
		/**
		 * Style Tab: Items
		 */
		$this->start_controls_section(
			'section_faq_items_style',
			[
				'label'                 => esc_html__( 'Items', 'powerpack' ),
				'tab'                   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'faqs_items_horizontal_spacing',
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
					'size'  => 10,
				],
				'selectors'             => [
					'{{WRAPPER}}' => '--grid-column-gap: {{SIZE}}{{UNIT}}',
				],
				'condition'             => [
					'faq_layout'    => 'grid',
				],
			]
		);

		$this->add_responsive_control(
			'faqs_items_bottom_spacing',
			[
				'label'                 => __( 'Bottom Spacing', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'range'                 => [
					'px'    => [
						'min' => 0,
						'max' => 200,
					],
				],
				'default'               => [
					'size'  => 10,
				],
				'selectors'             => [
					'{{WRAPPER}}' => '--grid-row-gap: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .pp-advanced-accordion .pp-faq-item:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'faq_tabs_style' );

		$this->start_controls_tab(
			'faq_tab_normal',
			[
				'label'                 => __( 'Normal', 'powerpack' ),
			]
		);

		$this->add_control(
			'faq_items_border_border',
			[
				'label'                 => esc_html__( 'Border Type', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'default'               => 'solid',
				'options'               => [
					''          => __( 'None', 'powerpack' ),
					'solid'     => __( 'Solid', 'powerpack' ),
					'double'    => __( 'Double', 'powerpack' ),
					'dotted'    => __( 'Dotted', 'powerpack' ),
				],
				'selectors'             => [
					'{{WRAPPER}} .pp-faq-item' => 'border-style: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'faq_items_border_width',
			[
				'label'                 => esc_html__( 'Border Width', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px' ],
				'default'               => [
					'top'       => 1,
					'right'     => 1,
					'bottom'    => 1,
					'left'      => 1,
					'isLinked'  => true,
				],
				'selectors'             => [
					'{{WRAPPER}} .pp-faq-item' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'             => [
					'faq_items_border_border!' => '',
				],
			]
		);

		$this->add_control(
			'faq_items_border_color',
			[
				'label'                 => esc_html__( 'Border Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'scheme'                => [
					'type' => Schemes\Color::get_type(),
					'value' => Schemes\Color::COLOR_3,
				],
				'default'               => '#d4d4d4',
				'selectors'             => [
					'{{WRAPPER}} .pp-faq-item' => 'border-color: {{VALUE}};',
				],
				'condition'             => [
					'faq_items_border_border!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'faq_items_border_radius',
			[
				'label'                 => esc_html__( 'Border Radius', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px', 'em', '%' ],
				'selectors'             => [
					'{{WRAPPER}} .pp-faq-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'                  => 'faq_items_box_shadow',
				'selector'              => '{{WRAPPER}} .pp-faq-item',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'faq_tab_hover',
			[
				'label'                 => __( 'Hover', 'powerpack' ),
			]
		);

		$this->add_control(
			'faq_items_border_color_hover',
			[
				'label'                 => esc_html__( 'Border Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-faq-item:hover' => 'border-color: {{VALUE}};',
				],
				'condition'             => [
					'faq_items_border_border!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'                  => 'faq_items_box_shadow_hover',
				'selector'              => '{{WRAPPER}} .pp-faq-item:hover',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'faq_tab_active',
			[
				'label'                 => __( 'Active', 'powerpack' ),
			]
		);

		$this->add_control(
			'faq_items_border_color_active',
			[
				'label'                 => esc_html__( 'Border Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-faq-item.pp-accordion-item-active' => 'border-color: {{VALUE}};',
				],
				'condition'             => [
					'faq_items_border_border!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'                  => 'faq_items_box_shadow_active',
				'selector'              => '{{WRAPPER}} .pp-faq-item.pp-accordion-item-active',
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control(
			'faq_items__padding',
			[
				'label'                 => esc_html__( 'Padding', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px', 'em', '%' ],
				'separator'             => 'before',
				'selectors'             => [
					'{{WRAPPER}} .pp-faq-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_questions_controls() {
		/**
		 * Style Tab: Questions
		 */
		$this->start_controls_section(
			'section_questions_style',
			[
				'label'                 => esc_html__( 'Questions', 'powerpack' ),
				'tab'                   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'faqs_question_bottom_spacing',
			[
				'label'                 => __( 'Bottom Spacing', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'range'                 => [
					'px'    => [
						'min' => 0,
						'max' => 200,
					],
				],
				'selectors'             => [
					'{{WRAPPER}} .pp-faqs .pp-faq-question' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'faq_question_tabs_style' );

		$this->start_controls_tab(
			'faq_question_tab_normal',
			[
				'label'                 => __( 'Normal', 'powerpack' ),
			]
		);

		$this->add_control(
			'question_text_color',
			[
				'label'                 => esc_html__( 'Text Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'scheme'                => [
					'type' => Schemes\Color::get_type(),
					'value' => Schemes\Color::COLOR_1,
				],
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-faqs .pp-faq-question' => 'color: {{VALUE}};',
					'{{WRAPPER}} .pp-faqs .pp-faq-question svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'question_bg_color',
			[
				'label'                 => esc_html__( 'Background Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-faqs .pp-faq-question' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'                  => 'question_typography',
				'selector'              => '{{WRAPPER}} .pp-faqs .pp-faq-question',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'                  => 'question_border',
				'label'                 => esc_html__( 'Border', 'powerpack' ),
				'selector'              => '{{WRAPPER}} .pp-faqs .pp-faq-question',
			]
		);

		$this->add_responsive_control(
			'faq_question_border_radius',
			[
				'label'                 => esc_html__( 'Border Radius', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px', 'em', '%' ],
				'selectors'             => [
					'{{WRAPPER}} .pp-faqs .pp-faq-question' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'faq_question_tab_hover',
			[
				'label'                 => __( 'Hover', 'powerpack' ),
			]
		);

		$this->add_control(
			'question_text_color_hover',
			[
				'label'                 => esc_html__( 'Text Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-faqs .pp-faq-question:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .pp-faqs .pp-faq-question:hover svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'question_bg_color_hover',
			[
				'label'                 => esc_html__( 'Background Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-faqs .pp-faq-question:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'question_border_color_hover',
			[
				'label'                 => esc_html__( 'Border Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-faqs .pp-faq-question:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'faq_question_tab_active',
			[
				'label'                 => __( 'Active', 'powerpack' ),
				'condition'             => [
					'faq_layout'    => 'accordion',
				],
			]
		);

		$this->add_control(
			'question_text_color_active',
			[
				'label'                 => esc_html__( 'Text Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'scheme'                => [
					'type' => Schemes\Color::get_type(),
					'value' => Schemes\Color::COLOR_4,
				],
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-faqs .pp-faq-question.pp-accordion-tab-active' => 'color: {{VALUE}};',
					'{{WRAPPER}} .pp-faqs .pp-faq-question.pp-accordion-tab-active svg' => 'fill: {{VALUE}};',
				],
				'condition'             => [
					'faq_layout'    => 'accordion',
				],
			]
		);

		$this->add_control(
			'question_bg_color_active',
			[
				'label'                 => esc_html__( 'Background Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-faqs .pp-faq-question.pp-accordion-tab-active' => 'background-color: {{VALUE}};',
				],
				'condition'             => [
					'faq_layout'    => 'accordion',
				],
			]
		);

		$this->add_control(
			'question_border_color_active',
			[
				'label'                 => esc_html__( 'Border Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-faqs .pp-faq-question.pp-accordion-tab-active' => 'border-color: {{VALUE}};',
				],
				'condition'             => [
					'faq_layout'    => 'accordion',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control(
			'question_padding',
			[
				'label'                 => esc_html__( 'Padding', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px', 'em', '%' ],
				'separator'             => 'before',
				'selectors'             => [
					'{{WRAPPER}} .pp-faqs .pp-faq-question' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'tab_icon_heading',
			[
				'label'                 => __( 'Icon', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
				'separator'             => 'before',
			]
		);

		$this->add_responsive_control(
			'tab_icon_size',
			[
				'label'                 => __( 'Icon Size', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'default'               => [
					'size'  => 16,
					'unit'  => 'px',
				],
				'size_units'            => [ 'px' ],
				'range'                 => [
					'px'        => [
						'min'   => 0,
						'max'   => 100,
						'step'  => 1,
					],
				],
				'selectors'             => [
					'{{WRAPPER}} .pp-faqs .pp-faq-question .pp-accordion-tab-icon' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'tab_icon_spacing',
			[
				'label'                 => __( 'Icon Spacing', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'default'               => [
					'size'  => 10,
					'unit'  => 'px',
				],
				'size_units'            => [ 'px' ],
				'range'                 => [
					'px'    => [
						'min'   => 0,
						'max'   => 100,
						'step'  => 1,
					],
				],
				'selectors'             => [
					'{{WRAPPER}} .pp-faqs .pp-faq-question .pp-accordion-tab-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_answers_controls() {
		/**
		 * Style Tab: Answers
		 */
		$this->start_controls_section(
			'section_answers_style',
			[
				'label'                 => esc_html__( 'Answers', 'powerpack' ),
				'tab'                   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'faq_tabs_answers_style' );

		$this->start_controls_tab(
			'faq_tab_answers_normal',
			[
				'label'                 => __( 'Normal', 'powerpack' ),
			]
		);

		$this->add_control(
			'answer_text_color',
			[
				'label'                 => esc_html__( 'Text Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '#333',
				'selectors'             => [
					'{{WRAPPER}} .pp-faqs .pp-faq-answer' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'answer_bg_color',
			[
				'label'                 => esc_html__( 'Background Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-faqs .pp-faq-answer' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'                  => 'answer_typography',
				'selector'              => '{{WRAPPER}} .pp-faqs .pp-faq-answer',
			]
		);

		$this->add_responsive_control(
			'faq_answer_border_radius',
			[
				'label'                 => esc_html__( 'Border Radius', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px', 'em', '%' ],
				'selectors'             => [
					'{{WRAPPER}} .pp-faqs .pp-faq-answer' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'faq_tab_answers_hover',
			[
				'label'                 => __( 'Hover', 'powerpack' ),
			]
		);

		$this->add_control(
			'answer_text_color_hover',
			[
				'label'                 => esc_html__( 'Text Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-faqs .pp-accordion-item:hover .pp-faq-answer' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'answer_bg_color_hover',
			[
				'label'                 => esc_html__( 'Background Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-faqs .pp-accordion-item:hover .pp-faq-answer' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'faq_tab_answers_active',
			[
				'label'                 => __( 'Active', 'powerpack' ),
			]
		);

		$this->add_control(
			'answer_text_color_active',
			[
				'label'                 => esc_html__( 'Text Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-faqs .pp-accordion-item-active .pp-faq-answer' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'answer_bg_color_active',
			[
				'label'                 => esc_html__( 'Background Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-faqs .pp-accordion-item-active .pp-faq-answer' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control(
			'answer_padding',
			[
				'label'                 => esc_html__( 'Padding', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px', 'em', '%' ],
				'separator'             => 'before',
				'selectors'             => [
					'{{WRAPPER}} .pp-faqs .pp-faq-answer' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_toggle_icon_controls() {
		/**
		 * Style tab: Toggle Icon
		 */
		$this->start_controls_section(
			'section_toggle_icon_style',
			[
				'label'                 => esc_html__( 'Toggle icon', 'powerpack' ),
				'tab'                   => Controls_Manager::TAB_STYLE,
				'condition'             => [
					'faq_layout'        => 'accordion',
					'toggle_icon_show'  => 'yes',
				],
			]
		);

		$this->add_control(
			'toggle_icon_align',
			[
				'label'   => __( 'Alignment', 'powerpack' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left'  => [
						'title' => __( 'Start', 'powerpack' ),
						'icon' => 'eicon-h-align-left',
					],
					'right' => [
						'title' => __( 'End', 'powerpack' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'default' => is_rtl() ? 'left' : 'right',
				'toggle'  => false,
			]
		);

		$this->add_control(
			'toggle_icon_color',
			[
				'label'                 => esc_html__( 'Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'scheme'                => [
					'type' => Schemes\Color::get_type(),
					'value' => Schemes\Color::COLOR_1,
				],
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-faqs .pp-faq-question .pp-accordion-toggle-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .pp-faqs .pp-faq-question .pp-accordion-toggle-icon svg' => 'fill: {{VALUE}};',
				],
				'condition'             => [
					'faq_layout'        => 'accordion',
					'toggle_icon_show'  => 'yes',
				],
			]
		);

		$this->add_control(
			'toggle_icon_hover_color',
			[
				'label'                 => esc_html__( 'Hover Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'scheme'                => [
					'type' => Schemes\Color::get_type(),
					'value' => Schemes\Color::COLOR_4,
				],
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-advanced-accordion .pp-accordion-item:hover .pp-accordion-tab-title .pp-accordion-toggle-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .pp-advanced-accordion .pp-accordion-item:hover .pp-accordion-tab-title .pp-accordion-toggle-icon svg' => 'fill: {{VALUE}};',
				],
				'condition'             => [
					'faq_layout'        => 'accordion',
					'toggle_icon_show'  => 'yes',
				],
			]
		);

		$this->add_control(
			'toggle_icon_active_color',
			[
				'label'                 => esc_html__( 'Active Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'scheme'                => [
					'type' => Schemes\Color::get_type(),
					'value' => Schemes\Color::COLOR_4,
				],
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-faqs .pp-faq-question.pp-accordion-tab-active .pp-accordion-toggle-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .pp-faqs .pp-faq-question.pp-accordion-tab-active .pp-accordion-toggle-icon svg' => 'fill: {{VALUE}};',
				],
				'condition'             => [
					'faq_layout'        => 'accordion',
					'toggle_icon_show'  => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'toggle_icon_size',
			[
				'label'                 => __( 'Size', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'default'               => [
					'size'  => 16,
					'unit'  => 'px',
				],
				'size_units'            => [ 'px' ],
				'range' => [
					'px'    => [
						'min'   => 0,
						'max'   => 100,
						'step'  => 1,
					],
				],
				'selectors'             => [
					'{{WRAPPER}} .pp-faqs .pp-faq-question .pp-accordion-toggle-icon' => 'font-size: {{SIZE}}{{UNIT}};',
				],
				'condition'             => [
					'faq_layout'        => 'accordion',
					'toggle_icon_show'  => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'toggle_icon_spacing',
			[
				'label'     => __( 'Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .pp-toggle-icon-align-left .pp-accordion-toggle-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .pp-toggle-icon-align-right .pp-accordion-toggle-icon' => 'margin-left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Get post query arguments.
	 *
	 * @access protected
	 */
	protected function get_posts_query_arguments() {
		$settings = $this->get_settings();
		$posts_count = absint( $settings['posts_per_page'] );

		// Query Arguments
		$query_args = array(
			'post_status'           => array( 'publish' ),
			'post_type'             => $settings['post_type'],
			'orderby'               => $settings['orderby'],
			'order'                 => $settings['order'],
			'offset'                => $settings['offset'],
			'ignore_sticky_posts'   => ( 'yes' === $settings['sticky_posts'] ) ? 0 : 1,
			'showposts'             => $posts_count,
		);

		// Author Filter
		if ( ! empty( $settings['authors'] ) ) {
			$query_args[ $settings['author_filter_type'] ] = $settings['authors'];
		}

		// Posts Filter
		$post_type = $settings['post_type'];

		if ( 'post' === $post_type ) {
			$posts_control_key = 'exclude_posts';
		} else {
			$posts_control_key = $post_type . '_filter';
		}

		if ( ! empty( $settings[ $posts_control_key ] ) ) {
			$query_args[ $settings[ $post_type . '_filter_type' ] ] = $settings[ $posts_control_key ];
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

					$query_args['tax_query'][] = [
						'taxonomy' => $index,
						'field'    => 'term_id',
						'terms'    => $settings[ $tax_control_key ],
						'operator' => $operator,
					];
				}
			}
		}

		if ( 'anytime' !== $settings['select_date'] ) {
			$select_date = $settings['select_date'];
			if ( ! empty( $select_date ) ) {
				$date_query = [];
				if ( 'today' === $select_date ) {
					$date_query['after'] = '-1 day';
				} elseif ( 'week' === $select_date ) {
					$date_query['after'] = '-1 week';
				} elseif ( 'month' === $select_date ) {
					$date_query['after'] = '-1 month';
				} elseif ( 'quarter' === $select_date ) {
					$date_query['after'] = '-3 month';
				} elseif ( 'year' === $select_date ) {
					$date_query['after'] = '-1 year';
				} elseif ( 'exact' === $select_date ) {
					$after_date = $settings['date_after'];
					if ( ! empty( $after_date ) ) {
						$date_query['after'] = $after_date;
					}
					$before_date = $settings['date_before'];
					if ( ! empty( $before_date ) ) {
						$date_query['before'] = $before_date;
					}
					$date_query['inclusive'] = true;
				}

				$query_args['date_query'] = $date_query;
			}
		}

		// Sticky Posts Filter
		if ( 'yes' === $settings['sticky_posts'] && 'yes' === $settings['all_sticky_posts'] ) {
			$post__in = get_option( 'sticky_posts' );

			$query_args['post__in'] = $post__in;
		}

		return apply_filters( 'ppe_faqs_query_args', $query_args, $settings );
	}

	/**
	 * Get custom post excerpt.
	 *
	 * @access protected
	 */
	protected function get_faq_post_answer( $limit = '' ) {
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
	 * Render posts output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function get_faqs_posts() {
		$settings = $this->get_settings();

		$i = 1;
		$faqs = array();
		$active_tabs = array();

		// Query Arguments
		$args = $this->get_posts_query_arguments();
		$posts_query = new \WP_Query( $args );

		if ( $settings['default_active_tabs'] ) {
			$active_tabs = PP_Helper::comma_list_to_array( $settings['default_active_tabs'] );
		}

		if ( $posts_query->have_posts() ) :
			while ( $posts_query->have_posts() ) :
				$posts_query->the_post();

				$limit = $settings['preview_excerpt_length'];
				$faqs[ $i ]['question_icon'] = '';
				$faqs[ $i ]['question'] = get_the_title();
				$faqs[ $i ]['answer'] = $this->get_faq_post_answer( $limit );
				$faqs[ $i ]['accordion_tab_id'] = '';
				$faqs[ $i ]['active_tab'] = '';
				if ( in_array( $i, $active_tabs, true ) ) {
					$faqs[ $i ]['active_tab'] = 'yes';
				}

				$i++;
			endwhile;
		endif;
		wp_reset_postdata();

		return $faqs;
	}

	protected function get_faqs_custom() {
		global $wp_embed;

		$settings = $this->get_settings_for_display();

		$faqs = array();

		foreach ( $settings['tabs'] as $index => $item ) {
			$faqs[ $index ]['question_icon'] = $item['question_icon'];
			$faqs[ $index ]['question'] = $item['tab_title'];
			$faqs[ $index ]['answer'] = wpautop( $wp_embed->autoembed( $item['faq_answer'] ) );
			$faqs[ $index ]['accordion_tab_id'] = $item['accordion_tab_id'];
			$faqs[ $index ]['active_tab'] = $item['accordion_tab_default_active'];
		}

		return $faqs;
	}

	protected function get_faqs_acf( $post_id = false ) {
		$settings = $this->get_settings_for_display();

		if ( ! isset( $settings['acf_repeater_name'] ) || empty( $settings['acf_repeater_name'] ) ) {
			return;
		}

		$i = 1;
		$faqs    = array();
		$active_tabs = array();
		if ( class_exists( 'acf' ) ) {
			if ( is_tax() ) {
				$post_id = get_queried_object();
			}

			$post_id = apply_filters( 'pp_faq_acf_post_id', $post_id );

			if ( $settings['default_active_tabs'] ) {
				$active_tabs = PP_Helper::comma_list_to_array( $settings['default_active_tabs'] );
			}

			$repeater_name = $settings['acf_repeater_name'];
			$question_name = $settings['acf_repeater_question'];
			$answer_name   = $settings['acf_repeater_answer'];

			$repeater_rows = get_field( $repeater_name, $post_id );

			if ( ! $repeater_rows ) {
				return;
			}

			foreach ( $repeater_rows as $index => $item ) {
				$faqs[ $index ]['question_icon'] = '';
				$faqs[ $index ]['question'] = isset( $item[ $question_name ] ) ? $item[ $question_name ] : '';
				$faqs[ $index ]['answer'] = isset( $item[ $answer_name ] ) ? $item[ $answer_name ] : '';
				$faqs[ $index ]['accordion_tab_id'] = '';
				$faqs[ $index ]['active_tab'] = '';
				if ( in_array( $i, $active_tabs, true ) ) {
					$faqs[ $index ]['active_tab'] = 'yes';
				}
				$i++;
			}
		}

		return $faqs;
	}

	public function pp_get_settings() {
		return $this->get_settings_for_display();
	}

	public function get_faq_items() {
		$settings = $this->pp_get_settings();

		if ( 'posts' === $settings['source'] ) {
			return $this->get_faqs_posts();
		}

		if ( 'custom' === $settings['source'] ) {
			return $this->get_faqs_custom();
		}

		if ( 'acf' === $settings['source'] ) {
			return $this->get_faqs_acf();
		}
	}

	protected function render_faqs() {
		$settings = $this->get_settings_for_display();
		$id_int   = substr( $this->get_id_int(), 0, 3 );

		$faqs = $this->get_faq_items();

		if ( empty( $faqs ) ) {
			return;
		}

		foreach ( $faqs as $index => $tab ) :

			$tab_count = $index + 1;
			$question_setting_key = $this->get_repeater_setting_key( 'tab_title', 'tabs', $index );
			$tab_content_setting_key = $this->get_repeater_setting_key( 'faq_answer', 'tabs', $index );
			$item_key = $this->get_repeater_setting_key( 'faq_item', 'tabs', $index );

			$question_class   = [ 'pp-faq-question' ];
			$faq_answer_class = [ 'pp-faq-answer' ];

			if ( 'accordion' === $settings['faq_layout'] ) {
				$question_class[]   = 'pp-accordion-tab-title';
				$faq_answer_class[] = 'pp-accordion-tab-content';

				if ( 'yes' === $tab['active_tab'] ) {
					$question_class[]  = 'pp-accordion-tab-active-default';
					$faq_answer_class[] = 'pp-accordion-tab-active-default';
				}
			}

			$this->add_render_attribute( [
				$item_key => [
					'class' => [ 'pp-faq-item', 'elementor-grid-item' ],
				],
			] );

			if ( 'accordion' === $settings['faq_layout'] ) {
				$this->add_render_attribute( $item_key, 'class', 'pp-accordion-item' );
			} else {
				$this->add_render_attribute( [
					$item_key => [
						'class' => 'pp-grid-item',
					],
				] );
			}

			if ( 'yes' === $tab['active_tab'] ) {
				$this->add_render_attribute( $item_key, 'class', 'pp-accordion-item-active' );
			}

			if ( $tab['accordion_tab_id'] ) {
				$tab_id = $tab['accordion_tab_id'];
			} elseif ( $settings['custom_id_prefix'] ) {
				$tab_id = $settings['custom_id_prefix'] . '-' . $tab_count;
			} else {
				$tab_id = 'pp-accordion-tab-title-' . $id_int . $tab_count;
			}

			$this->add_render_attribute( [
				$question_setting_key => [
					'id'            => $tab_id,
					'class'         => $question_class,
					'tabindex'      => '0',
					'data-tab'      => $tab_count,
					'aria-controls' => 'pp-accordion-tab-content-' . $id_int . $tab_count,
					'aria-expanded' => ( 'yes' === $tab['active_tab'] ) ? 'true' : 'false',
				],
				$tab_content_setting_key => [
					'id'              => 'pp-accordion-tab-content-' . $id_int . $tab_count,
					'class'           => $faq_answer_class,
					'data-tab'        => $tab_count,
					'aria-labelledby' => $tab_id,
				],
			] );

			$this->add_inline_editing_attributes( $tab_content_setting_key, 'advanced' );
			?>
			<div <?php echo wp_kses_post( $this->get_render_attribute_string( $item_key ) ); ?>>
				<div <?php echo wp_kses_post( $this->get_render_attribute_string( $question_setting_key ) ); ?>>
					<div class="pp-accordion-title-icon">
						<?php if ( ! empty( $tab['question_icon']['value'] ) ) { ?>
							<span class="pp-accordion-tab-icon pp-icon">
							<?php
								Icons_Manager::render_icon( $tab['question_icon'], [ 'aria-hidden' => 'true' ] );
							?>
							</span>
						<?php } ?>
						<?php $question_html_tag = PP_Helper::validate_html_tag( $settings['question_html_tag'] ); ?>
						<<?php echo esc_html( $question_html_tag ); ?> class="pp-accordion-title-text">
							<?php echo wp_kses_post( $tab['question'] ); ?>
						</<?php echo esc_html( $question_html_tag ); ?>>
					</div>
					<?php if ( 'yes' === $settings['toggle_icon_show'] ) { ?>
						<div class="pp-accordion-toggle-icon">
							<?php if ( $settings['select_toggle_icon']['value'] ) { ?>
								<span class='pp-accordion-toggle-icon-close pp-icon'>
									<?php
										Icons_Manager::render_icon( $settings['select_toggle_icon'], [ 'aria-hidden' => 'true' ] );
									?>
								</span>
							<?php } ?>
							<?php if ( $settings['select_toggle_icon_active']['value'] ) { ?>
								<span class='pp-accordion-toggle-icon-open pp-icon'>
									<?php
										Icons_Manager::render_icon( $settings['select_toggle_icon_active'], [ 'aria-hidden' => 'true' ] );
									?>
								</span>
							<?php } ?>
						</div>
					<?php } ?>
				</div>

				<div <?php echo wp_kses_post( $this->get_render_attribute_string( $tab_content_setting_key ) ); ?>>
					<?php echo $this->parse_text_editor( $tab['answer'] ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
			<?php
		endforeach;
	}

	protected function render() {
		$settings   = $this->get_settings_for_display();
		$is_editor  = \Elementor\Plugin::instance()->editor->is_edit_mode();
		$icon_align = is_rtl() ? 'right' : $settings['toggle_icon_align'];

		$this->add_render_attribute( 'container', [
			'class' => 'pp-faqs',
		] );

		if ( 'accordion' === $settings['faq_layout'] ) {
			$this->add_render_attribute( 'container', [
				'class'             => [ 'pp-advanced-accordion', 'pp-toggle-icon-align-' . $icon_align ],
				'id'                => 'pp-advanced-accordion-' . esc_attr( $this->get_id() ),
				'data-accordion-id' => esc_attr( $this->get_id() ),
			] );
		} else {
			$this->add_render_attribute( 'container', 'class', [ 'pp-faq-grid', 'elementor-grid', 'clearfix' ] );
		}
		?>
		<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'container' ) ); ?>>
			<?php $this->render_faqs(); ?>
		</div>
		<?php
	}
}
