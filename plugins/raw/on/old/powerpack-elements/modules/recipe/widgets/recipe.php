<?php
namespace PowerpackElements\Modules\Recipe\Widgets;

use PowerpackElements\Base\Powerpack_Widget;

// Elementor Classes
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Icons_Manager;
use Elementor\Control_Media;
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
 * Recipe Widget
 */
class Recipe extends Powerpack_Widget {

	/**
	 * Retrieve recipe widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Recipe' );
	}

	/**
	 * Retrieve recipe widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Recipe' );
	}

	/**
	 * Retrieve recipe widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Recipe' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @since 1.3.4
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'Recipe' );
	}

	/**
	 * Register recipe widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 2.0.3
	 * @access protected
	 */
	protected function register_controls() {
		/* Content Tab */
		$this->register_content_recipe_info_controls();
		$this->register_content_recipe_meta_controls();
		$this->register_content_recipe_details_controls();
		$this->register_content_ingredients_controls();
		$this->register_content_instructions_controls();
		$this->register_content_notes_controls();
		$this->register_content_schema_controls();

		/* Style Tab */
		$this->register_style_box_controls();
		$this->register_style_recipe_info_controls();
		$this->register_style_recipe_meta_controls();
		$this->register_style_recipe_details_controls();
		$this->register_style_ingredients_controls();
		$this->register_style_instructions_controls();
		$this->register_style_notes_controls();
	}

	/*-----------------------------------------------------------------------------------*/
	/*	CONTENT TAB
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Content Tab: Recipe
	 */
	protected function register_content_recipe_info_controls() {
		$this->start_controls_section(
			'section_recipe_info',
			[
				'label'                 => __( 'Recipe Info', 'powerpack' ),
			]
		);

		$this->add_control(
			'recipe_name',
			[
				'label'                 => __( 'Name', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( 'Fudgy Chocolate Brownies', 'powerpack' ),
				'title'                 => __( 'Enter recipe name', 'powerpack' ),
			]
		);

		$this->add_control(
			'recipe_description',
			[
				'label'                 => __( 'Description', 'powerpack' ),
				'type'                  => Controls_Manager::WYSIWYG,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( 'These heavenly brownies are pure chocolate overload, featuring a fudgy center, slightly crusty top and layers of decadence. It doesn\'t get better than this.', 'powerpack' ),
				'title'                 => __( 'Recipe description', 'powerpack' ),
			]
		);

		$this->add_control(
			'image',
			[
				'label'                 => __( 'Image', 'powerpack' ),
				'type'                  => Controls_Manager::MEDIA,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => [
					'url'  => Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'                  => 'image',
				'default'               => 'full',
				'separator'             => 'none',
			]
		);

		$this->add_control(
			'title_separator',
			[
				'label'                 => __( 'Title Separator', 'powerpack' ),
				'type'                  => Controls_Manager::SWITCHER,
				'default'               => 'no',
				'label_on'              => __( 'Yes', 'powerpack' ),
				'label_off'             => __( 'No', 'powerpack' ),
				'return_value'          => 'yes',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Content Tab: Recipe Meta
	 */
	protected function register_content_recipe_meta_controls() {
		$this->start_controls_section(
			'section_recipe_meta',
			[
				'label'                 => __( 'Recipe Meta', 'powerpack' ),
			]
		);

		$this->add_control(
			'rating',
			[
				'label'                 => __( 'Rating', 'powerpack' ),
				'type'                  => Controls_Manager::SWITCHER,
				'default'               => '',
				'label_on'              => __( 'Yes', 'powerpack' ),
				'label_off'             => __( 'No', 'powerpack' ),
				'return_value'          => 'yes',
			]
		);

		$this->add_control(
			'rating_value',
			[
				'label'                 => __( 'Rating Value', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( '4', 'powerpack' ),
				'condition'             => [
					'rating'   => 'yes',
				],
			]
		);

		$this->add_control(
			'best_rating',
			[
				'label'                 => __( 'Best Rating', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( '4', 'powerpack' ),
				'condition'             => [
					'rating'   => 'yes',
				],
			]
		);

		$this->add_control(
			'worst_rating',
			[
				'label'                 => __( 'Worst Rating', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( '1', 'powerpack' ),
				'condition'             => [
					'rating'   => 'yes',
				],
			]
		);

		$this->add_control(
			'total_rating',
			[
				'label'                 => __( 'Total Rating', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( '5', 'powerpack' ),
				'condition'             => [
					'rating'   => 'yes',
				],
			]
		);

		$this->add_control(
			'rating_icon',
			array(
				'label'                  => __( 'Icon', 'powerpack' ),
				'type'                   => Controls_Manager::ICONS,
				'label_block'            => false,
				'default'                => array(
					'value'   => 'fas fa-star',
					'library' => 'fa-solid',
				),
				'skin'                   => 'inline',
				'condition'             => [
					'rating'   => 'yes',
				],
			)
		);

		$this->add_control(
			'author',
			[
				'label'                 => __( 'Author', 'powerpack' ),
				'type'                  => Controls_Manager::SWITCHER,
				'default'               => 'yes',
				'label_on'              => __( 'Yes', 'powerpack' ),
				'label_off'             => __( 'No', 'powerpack' ),
				'return_value'          => 'yes',
			]
		);

		$this->add_control(
			'date',
			[
				'label'                 => __( 'Date', 'powerpack' ),
				'type'                  => Controls_Manager::SWITCHER,
				'default'               => 'yes',
				'label_on'              => __( 'Yes', 'powerpack' ),
				'label_off'             => __( 'No', 'powerpack' ),
				'return_value'          => 'yes',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Content Tab: Recipe Details
	 */
	protected function register_content_recipe_details_controls() {
		$this->start_controls_section(
			'section_recipe_details',
			[
				'label'                 => __( 'Recipe Details', 'powerpack' ),
			]
		);

		$this->add_control(
			'prep_time_heading',
			[
				'label'                 => __( 'Prep Time', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'prep_time_title',
			[
				'label'                 => __( 'Title', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( 'Prep Time', 'powerpack' ),
			]
		);

		$this->add_control(
			'prep_time',
			[
				'label'                 => __( 'Time', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( '15', 'powerpack' ),
			]
		);

		$this->add_control(
			'prep_time_unit',
			[
				'label'                 => __( 'Unit', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( 'Minutes', 'powerpack' ),
			]
		);

		$this->add_control(
			'prep_time_icon',
			array(
				'label'                  => __( 'Icon', 'powerpack' ),
				'type'                   => Controls_Manager::ICONS,
				'label_block'            => false,
				'default'                => array(
					'value'   => 'fas fa-leaf',
					'library' => 'fa-solid',
				),
				'skin'                   => 'inline',
			)
		);

		$this->add_control(
			'cook_time_heading',
			[
				'label'                 => __( 'Cook Time', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
				'separator'             => 'before',
			]
		);

		$this->add_control(
			'cook_time_title',
			[
				'label'                 => __( 'Title', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( 'Cook Time', 'powerpack' ),
			]
		);

		$this->add_control(
			'cook_time',
			[
				'label'                 => __( 'Time', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( '30', 'powerpack' ),
			]
		);

		$this->add_control(
			'cook_time_unit',
			[
				'label'                 => __( 'Unit', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( 'Minutes', 'powerpack' ),
			]
		);

		$this->add_control(
			'cook_time_icon',
			array(
				'label'                  => __( 'Icon', 'powerpack' ),
				'type'                   => Controls_Manager::ICONS,
				'label_block'            => false,
				'default'                => array(
					'value'   => 'fas fa-utensils',
					'library' => 'fa-solid',
				),
				'skin'                   => 'inline',
			)
		);

		$this->add_control(
			'total_time_heading',
			[
				'label'                 => __( 'Total Time', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
				'separator'             => 'before',
			]
		);

		$this->add_control(
			'total_time_title',
			[
				'label'                 => __( 'Title', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( 'Total Time', 'powerpack' ),
			]
		);

		$this->add_control(
			'total_time',
			[
				'label'                 => __( 'Time', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( '45', 'powerpack' ),
			]
		);

		$this->add_control(
			'total_time_unit',
			[
				'label'                 => __( 'Unit', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( 'Minutes', 'powerpack' ),
			]
		);

		$this->add_control(
			'total_time_icon',
			array(
				'label'                  => __( 'Icon', 'powerpack' ),
				'type'                   => Controls_Manager::ICONS,
				'label_block'            => false,
				'default'                => array(
					'value'   => 'fas fa-clock',
					'library' => 'fa-solid',
				),
				'skin'                   => 'inline',
			)
		);

		$this->add_control(
			'servings_heading',
			[
				'label'                 => __( 'Servings', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
				'separator'             => 'before',
			]
		);

		$this->add_control(
			'servings_title',
			[
				'label'                 => __( 'Title', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( 'Serves', 'powerpack' ),
			]
		);

		$this->add_control(
			'servings',
			[
				'label'                 => __( 'Value', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( '2', 'powerpack' ),
			]
		);

		$this->add_control(
			'servings_unit',
			[
				'label'                 => __( 'Unit', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( 'People', 'powerpack' ),
			]
		);

		$this->add_control(
			'servings_icon',
			array(
				'label'                  => __( 'Icon', 'powerpack' ),
				'type'                   => Controls_Manager::ICONS,
				'label_block'            => false,
				'default'                => array(
					'value'   => 'fas fa-users',
					'library' => 'fa-solid',
				),
				'skin'                   => 'inline',
			)
		);

		$this->add_control(
			'calories_heading',
			[
				'label'                 => __( 'Calories', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
				'separator'             => 'before',
			]
		);

		$this->add_control(
			'calories_title',
			[
				'label'                 => __( 'Title', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( 'Calories', 'powerpack' ),
			]
		);

		$this->add_control(
			'calories',
			[
				'label'                 => __( 'Value', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( '200', 'powerpack' ),
			]
		);

		$this->add_control(
			'calories_unit',
			[
				'label'                 => __( 'Unit', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( 'kcal', 'powerpack' ),
			]
		);

		$this->add_control(
			'calories_icon',
			array(
				'label'                  => __( 'Icon', 'powerpack' ),
				'type'                   => Controls_Manager::ICONS,
				'label_block'            => false,
				'default'                => array(
					'value'   => 'fas fa-bolt',
					'library' => 'fa-solid',
				),
				'skin'                   => 'inline',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Content Tab: Ingredients
	 */
	protected function register_content_ingredients_controls() {
		$this->start_controls_section(
			'section_ingredients',
			[
				'label'                 => __( 'Ingredients', 'powerpack' ),
			]
		);

		$this->add_control(
			'ingredients_title',
			[
				'label'                 => __( 'Title', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( 'Ingredients', 'powerpack' ),
				'separator'             => 'after',
			]
		);

		$this->add_control(
			'recipe_ingredients',
			[
				'label'                 => __( 'Add Ingredients', 'powerpack' ),
				'type'                  => Controls_Manager::REPEATER,
				'default'               => [
					[
						'recipe_ingredient' => __( 'Ingredient #1', 'powerpack' ),
					],
					[
						'recipe_ingredient' => __( 'Ingredient #2', 'powerpack' ),
					],
					[
						'recipe_ingredient' => __( 'Ingredient #3', 'powerpack' ),
					],
				],
				'fields'                => [
					[
						'name'        => 'recipe_ingredient',
						'label'       => __( 'Text', 'powerpack' ),
						'type'        => Controls_Manager::TEXT,
						'dynamic'     => [
							'active'  => true,
						],
						'label_block' => true,
						'placeholder' => __( 'Ingredient', 'powerpack' ),
						'default'     => __( 'Ingredient #1', 'powerpack' ),
					],
				],
				'title_field'           => '{{{ recipe_ingredient }}}',
			]
		);

		$this->add_control(
			'ingredients_icon',
			[
				'label'                 => __( 'Icon', 'powerpack' ),
				'type'                  => Controls_Manager::ICONS,
				'fa4compatibility'      => 'ingredients_list_icon',
				'default'               => [
					'value'     => 'far fa-square',
					'library'   => 'fa-solid',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Content Tab: Instructions
	 */
	protected function register_content_instructions_controls() {
		$this->start_controls_section(
			'section_instructions',
			[
				'label'                 => __( 'Instructions', 'powerpack' ),
			]
		);

		$this->add_control(
			'instructions_title',
			[
				'label'                 => __( 'Title', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( 'Instructions', 'powerpack' ),
				'separator'             => 'after',
			]
		);

		$this->add_control(
			'recipe_instructions',
			[
				'label'                 => __( 'Add Instructions', 'powerpack' ),
				'type'                  => Controls_Manager::REPEATER,
				'default'               => [
					[
						'recipe_instruction' => __( 'Step #1', 'powerpack' ),
					],
					[
						'recipe_instruction' => __( 'Step #2', 'powerpack' ),
					],
					[
						'recipe_instruction' => __( 'Step #3', 'powerpack' ),
					],
				],
				'fields'                => [
					[
						'name'        => 'recipe_instruction',
						'label'       => __( 'Text', 'powerpack' ),
						'type'        => Controls_Manager::TEXT,
						'dynamic'               => [
							'active'   => true,
						],
						'label_block' => true,
						'placeholder' => __( 'Instruction', 'powerpack' ),
						'default'     => __( 'Instruction', 'powerpack' ),
					],
				],
				'title_field'           => '{{{ recipe_instruction }}}',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Content Tab: Notes
	 */
	protected function register_content_notes_controls() {
		$this->start_controls_section(
			'section_notes',
			[
				'label'                 => __( 'Notes', 'powerpack' ),
			]
		);

		$this->add_control(
			'notes_title',
			[
				'label'                 => __( 'Title', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => __( 'Notes', 'powerpack' ),
				'separator'             => 'after',
			]
		);

		$this->add_control(
			'item_notes',
			[
				'label'                 => __( 'Notes', 'powerpack' ),
				'type'                  => Controls_Manager::WYSIWYG,
				'dynamic'               => [
					'active'   => true,
				],
				'default'               => '',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Content Tab: Schema Markup
	 */
	protected function register_content_schema_controls() {
		$this->start_controls_section(
			'section_schema_markup',
			[
				'label' => esc_html__( 'Schema Markup', 'powerpack' ),
			]
		);

		$this->add_control(
			'enable_schema',
			[
				'label'        => __( 'Enable Schema Markup', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'description'  => __( 'Enable Schema Markup option if you are setting up a unique "Recipe" page on your website. The Module adds "Recipe" Page schema to the page as per Google\'s Structured Data guideline. <a target="_blank" rel="noopener" href="https://developers.google.com/search/docs/advanced/structured-data/recipe/"><b style="color: #d30c5c;">Click here</b></a> for more details. <p style="font-style: normal; padding: 10px; background: #fffbd4; color: #333; margin-top: 10px; border: 1px solid #FFEB3B; border-radius: 5px; font-size: 12px;">To use schema markup, your page must have only single instance of Recipe widget.</p>', 'powerpack' ),
			]
		);

		$this->add_control(
			'keyword',
			[
				'label'                 => __( 'Keyword', 'powerpack' ),
				'type'                  => Controls_Manager::SWITCHER,
				'default'               => '',
				'label_on'              => __( 'Yes', 'powerpack' ),
				'label_off'             => __( 'No', 'powerpack' ),
				'return_value'          => 'yes',
				'condition'             => [
					'enable_schema'   => 'yes',
				],
			]
		);

		$this->add_control(
			'keyword_list',
			[
				'label'                 => __( 'Add Keyword', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'default'               => __( 'Street food, Food Junkie', 'powerpack' ),
				'description'           => __( 'Note: Add keywords in a comma-separated list.', 'powerpack' ),
				'condition'             => [
					'keyword'       => 'yes',
					'enable_schema' => 'yes',
				],
			]
		);

		$this->add_control(
			'recipe_cuisine',
			[
				'label'                 => __( 'Recipe Cuisine', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'default'               => __( 'French', 'powerpack' ),
				'condition'             => [
					'keyword'       => 'yes',
					'enable_schema' => 'yes',
				],
			]
		);

		$this->add_control(
			'recipe_category',
			[
				'label'                 => __( 'Recipe Category', 'powerpack' ),
				'type'                  => Controls_Manager::TEXT,
				'default'               => __( 'appetizer', 'powerpack' ),
				'condition'             => [
					'keyword'       => 'yes',
					'enable_schema' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	/*-----------------------------------------------------------------------------------*/
	/*	STYLE TAB
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Style Tab: Box Style
	 */
	protected function register_style_box_controls() {
		$this->start_controls_section(
			'section_box_style',
			[
				'label'                 => __( 'Box Style', 'powerpack' ),
				'tab'                   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'                  => 'box_bg',
				'label'                 => __( 'Background', 'powerpack' ),
				'types'                 => [ 'none', 'classic', 'gradient' ],
				'selector'              => '{{WRAPPER}} .pp-recipe-container',
			]
		);

		$this->add_control(
			'border_color',
			[
				'label'                 => __( 'Border Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-recipe-container, {{WRAPPER}} .pp-recipe-section' => 'border-color: {{VALUE}};',
				],
				'separator'             => 'before',
			]
		);

		$this->add_responsive_control(
			'border_width',
			[
				'label'                 => __( 'Border Width', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'size_units'            => [ 'px' ],
				'range'                 => [
					'px' => [
						'min' => 1,
						'max' => 10,
					],
				],
				'selectors'             => [
					'{{WRAPPER}} .pp-recipe-container, {{WRAPPER}} .pp-recipe-section' => 'border-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'box_border_radius',
			[
				'label'             => __( 'Border Radius', 'powerpack' ),
				'type'              => Controls_Manager::DIMENSIONS,
				'size_units'        => [ 'px', '%', 'em' ],
				'selectors'         => [
					'{{WRAPPER}} .pp-recipe-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Box Style
	 */
	protected function register_style_recipe_info_controls() {
		$this->start_controls_section(
			'section_recipe_info_style',
			[
				'label'                 => __( 'Recipe Info', 'powerpack' ),
				'tab'                   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_style',
			[
				'label'                 => __( 'Title', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'title_text_color',
			[
				'label'                 => __( 'Text Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-recipe-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'                  => 'title_typography',
				'label'                 => __( 'Typography', 'powerpack' ),
				'selector'              => '{{WRAPPER}} .pp-recipe-title',
			]
		);

		$this->add_control(
			'title_separator_heading',
			[
				'label'                 => __( 'Title Separator', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
				'condition'             => [
					'title_separator'   => 'yes',
				],
			]
		);

		$this->add_control(
			'title_separator_border_type',
			[
				'label'                 => __( 'Border Type', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'default'               => 'solid',
				'options'               => [
					'none'      => __( 'None', 'powerpack' ),
					'solid'     => __( 'Solid', 'powerpack' ),
					'double'    => __( 'Double', 'powerpack' ),
					'dotted'    => __( 'Dotted', 'powerpack' ),
					'dashed'    => __( 'Dashed', 'powerpack' ),
				],
				'selectors'             => [
					'{{WRAPPER}} .pp-recipe-title' => 'border-bottom-style: {{VALUE}}',
				],
				'condition'             => [
					'title_separator'   => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'title_separator_border_height',
			[
				'label'                 => __( 'Border Height', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'default'               => [
					'size'  => 1,
				],
				'range'                 => [
					'px' => [
						'min'   => 1,
						'max'   => 20,
						'step'  => 1,
					],
				],
				'size_units'            => [ 'px' ],
				'selectors'             => [
					'{{WRAPPER}} .pp-recipe-title' => 'border-bottom-width: {{SIZE}}{{UNIT}}',
				],
				'condition'             => [
					'title_separator'   => 'yes',
				],
			]
		);

		$this->add_control(
			'title_separator_border_color',
			[
				'label'                 => __( 'Border Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-recipe-title' => 'border-bottom-color: {{VALUE}}',
				],
				'condition'             => [
					'title_separator'   => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'title_separator_spacing',
			[
				'label'                 => __( 'Spacing', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'range'                 => [
					'px'        => [
						'min'   => 0,
						'max'   => 100,
						'step'  => 1,
					],
				],
				'size_units'            => [ 'px', 'em', '%' ],
				'selectors'             => [
					'{{WRAPPER}} .pp-recipe-title' => 'padding-bottom: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'description_style',
			[
				'label'                 => __( 'Description', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'description_text_color',
			[
				'label'                 => __( 'Text Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-recipe-description' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'                  => 'description_typography',
				'label'                 => __( 'Typography', 'powerpack' ),
				'scheme'                => Scheme_Typography::TYPOGRAPHY_4,
				'selector'              => '{{WRAPPER}} .pp-recipe-description',
			]
		);

		$this->add_control(
			'image_style',
			[
				'label'                 => __( 'Image', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
			]
		);

		$this->add_responsive_control(
			'image_width',
			[
				'label'                 => __( 'Width', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'size_units'            => [ 'px', '%' ],
				'range'                 => [
					'px' => [
						'min' => 50,
						'max' => 500,
					],
				],
				'selectors'             => [
					'{{WRAPPER}} .pp-recipe-header-image' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Recipe Meta
	 */
	protected function register_style_recipe_meta_controls() {
		$this->start_controls_section(
			'section_meta_style',
			[
				'label'                 => __( 'Recipe Meta', 'powerpack' ),
				'tab'                   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'meta_text_color',
			[
				'label'                 => __( 'Text Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-recipe-meta' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'                  => 'meta_typography',
				'label'                 => __( 'Typography', 'powerpack' ),
				'scheme'                => Scheme_Typography::TYPOGRAPHY_4,
				'selector'              => '{{WRAPPER}} .pp-recipe-meta',
			]
		);

		$this->add_control(
			'rating_icon_color',
			[
				'label'                 => __( 'Rating Icon Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-rating-icon' => 'color: {{VALUE}}',
				],
				'condition'             => [
					'rating'   => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Recipe Details
	 */
	protected function register_style_recipe_details_controls() {
		$this->start_controls_section(
			'section_recipe_details_style',
			[
				'label'                 => __( 'Recipe Details', 'powerpack' ),
				'tab'                   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'                  => 'recipe_details_bg',
				'label'                 => __( 'Background', 'powerpack' ),
				'types'                 => [ 'none', 'classic', 'gradient' ],
				'selector'              => '{{WRAPPER}} .pp-recipe-details',
			]
		);

		$this->add_responsive_control(
			'recipe_details_padding',
			[
				'label'                 => __( 'Padding', 'powerpack' ),
				'type'                  => Controls_Manager::DIMENSIONS,
				'size_units'            => [ 'px', '%' ],
				'selectors'             => [
					'{{WRAPPER}} .pp-recipe-details' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'detail_list_title_style_heading',
			[
				'label'                 => __( 'Title', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
				'separator'             => 'before',
			]
		);

		$this->add_control(
			'details_title_text_color',
			[
				'label'                 => __( 'Text Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-recipe-detail-title' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'                  => 'details_title_typography',
				'label'                 => __( 'Typography', 'powerpack' ),
				'scheme'                => Scheme_Typography::TYPOGRAPHY_4,
				'selector'              => '{{WRAPPER}} .pp-recipe-detail-title',
			]
		);

		$this->add_control(
			'details_content_style_heading',
			[
				'label'                 => __( 'Content', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'details_text_color',
			[
				'label'                 => __( 'Text Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-recipe-detail-value' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'                  => 'details_typography',
				'label'                 => __( 'Typography', 'powerpack' ),
				'scheme'                => Scheme_Typography::TYPOGRAPHY_4,
				'selector'              => '{{WRAPPER}} .pp-recipe-detail-value',
			]
		);

		$this->add_control(
			'icon_style_heading',
			[
				'label'                 => __( 'Icons', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'meta_icon_color',
			[
				'label'                 => __( 'Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-recipe-detail-icon' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'meta_icon_size',
			[
				'label'                 => __( 'Size', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'size_units'            => [ 'px' ],
				'range'                 => [
					'px' => [
						'min' => 10,
						'max' => 40,
					],
				],
				'selectors'             => [
					'{{WRAPPER}} .pp-recipe-detail-icon' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Ingredients
	 */
	protected function register_style_ingredients_controls() {
		$this->start_controls_section(
			'section_ingredients_style',
			[
				'label'                 => __( 'Ingredients', 'powerpack' ),
				'tab'                   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'ingredients_title_style',
			[
				'label'                 => __( 'Title', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'ingredients_title_color',
			[
				'label'                 => __( 'Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-recipe-ingredients-heading' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'                  => 'ingredients_title_typography',
				'label'                 => __( 'Typography', 'powerpack' ),
				'scheme'                => Scheme_Typography::TYPOGRAPHY_4,
				'selector'              => '{{WRAPPER}} .pp-recipe-ingredients-heading',
			]
		);

		$this->add_control(
			'ingredients_content_style',
			[
				'label'                 => __( 'Content', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'ingredients_color',
			[
				'label'                 => __( 'Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-recipe-ingredients-list' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'                  => 'ingredients_typography',
				'label'                 => __( 'Typography', 'powerpack' ),
				'scheme'                => Scheme_Typography::TYPOGRAPHY_4,
				'selector'              => '{{WRAPPER}} .pp-recipe-ingredients-list',
			]
		);

		$this->add_control(
			'ingredients_list_style',
			[
				'label'                 => __( 'List', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'ingredients_list_border_type',
			[
				'label'                 => __( 'Border Type', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'default'               => 'solid',
				'options'               => [
					'none'      => __( 'None', 'powerpack' ),
					'solid'     => __( 'Solid', 'powerpack' ),
					'dotted'    => __( 'Dotted', 'powerpack' ),
					'dashed'    => __( 'Dashed', 'powerpack' ),
				],
				'selectors'             => [
					'{{WRAPPER}} .pp-recipe-container .pp-recipe-ingredients li:not(:last-child)' => 'border-bottom-style: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'ingredients_list_border_color',
			[
				'label'                 => __( 'Border Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-recipe-container .pp-recipe-ingredients li:not(:last-child)' => 'border-bottom-color: {{VALUE}};',
				],
				'condition'             => [
					'ingredients_list_border_type!'   => 'none',
				],
			]
		);

		$this->add_responsive_control(
			'ingredients_list_border_width',
			[
				'label'                 => __( 'Border Weight', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'size_units'            => [ 'px' ],
				'range'                 => [
					'px' => [
						'min' => 0,
						'max' => 10,
					],
				],
				'selectors'             => [
					'{{WRAPPER}} .pp-recipe-container .pp-recipe-ingredients li:not(:last-child)' => 'border-bottom-width: {{SIZE}}{{UNIT}};',
				],
				'condition'             => [
					'ingredients_list_border_type!'   => 'none',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Instructions
	 */
	protected function register_style_instructions_controls() {
		$this->start_controls_section(
			'section_instructions_style',
			[
				'label'                 => __( 'Instructions', 'powerpack' ),
				'tab'                   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'instructions_title_style',
			[
				'label'                 => __( 'Title', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'instructions_title_color',
			[
				'label'                 => __( 'Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-recipe-instructions-heading' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'                  => 'instructions_title_typography',
				'label'                 => __( 'Typography', 'powerpack' ),
				'scheme'                => Scheme_Typography::TYPOGRAPHY_4,
				'selector'              => '{{WRAPPER}} .pp-recipe-instructions-heading',
			]
		);

		$this->add_control(
			'instructions_content_style',
			[
				'label'                 => __( 'Content', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'instructions_color',
			[
				'label'                 => __( 'Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-recipe-instructions-list' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'                  => 'instructions_typography',
				'label'                 => __( 'Typography', 'powerpack' ),
				'scheme'                => Scheme_Typography::TYPOGRAPHY_4,
				'selector'              => '{{WRAPPER}} .pp-recipe-instructions-list',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Notes
	 */
	protected function register_style_notes_controls() {
		$this->start_controls_section(
			'section_notes_style',
			[
				'label'                 => __( 'Notes', 'powerpack' ),
				'tab'                   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'notes_title_style',
			[
				'label'                 => __( 'Title', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'notes_title_color',
			[
				'label'                 => __( 'Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-recipe-notes-heading' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'                  => 'notes_title_typography',
				'label'                 => __( 'Typography', 'powerpack' ),
				'scheme'                => Scheme_Typography::TYPOGRAPHY_4,
				'selector'              => '{{WRAPPER}} .pp-recipe-notes-heading',
			]
		);

		$this->add_control(
			'notes_content_style',
			[
				'label'                 => __( 'Content', 'powerpack' ),
				'type'                  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'notes_color',
			[
				'label'                 => __( 'Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'default'               => '',
				'selectors'             => [
					'{{WRAPPER}} .pp-recipe-notes-content' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'                  => 'notes_typography',
				'label'                 => __( 'Typography', 'powerpack' ),
				'scheme'                => Scheme_Typography::TYPOGRAPHY_4,
				'selector'              => '{{WRAPPER}} .pp-recipe-notes-content',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render recipe details icon output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_recipe_details_icon( $icon, $added_class = 'yes' ) {
		$settings = $this->get_settings_for_display();

		if ( $icon['value'] ) { ?>
			<span class="<?php echo ( 'yes' === $added_class ) ? 'pp-recipe-detail-icon pp-icon' : ''; ?>">
				<?php Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] ); ?>
			</span>
			<?php
		}
	}

	/**
	 * Render recipe widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'recipe_name', 'class', 'pp-recipe-title' );
		$this->add_render_attribute( 'recipe_name', 'itemprop', 'name' );
		$this->add_inline_editing_attributes( 'recipe_name', 'none' );

		$this->add_render_attribute( 'recipe_description', 'class', 'pp-recipe-description' );
		$this->add_render_attribute( 'recipe_description', 'itemprop', 'description' );
		$this->add_inline_editing_attributes( 'recipe_description', 'basic' );

		$this->add_render_attribute( 'item_notes', 'class', 'pp-recipe-notes-content' );
		$this->add_inline_editing_attributes( 'item_notes', 'advanced' );

		$enable_schema = ( 'yes' === $settings['enable_schema'] ) ? 'yes' : 'no';
		?>
		<div class="pp-recipe-container" <?php echo ( 'yes' === $enable_schema ) ? 'itemscope="" itemtype="https://schema.org/Recipe"' : ''; ?> >
			<div class="pp-recipe-header">
				<?php if ( ! empty( $settings['image']['url'] ) ) { ?>
					<div class="pp-recipe-header-image" <?php echo ( 'yes' === $enable_schema ) ? 'itemprop="image" itemscope="" itemtype="https://schema.org/ImageObject"' : ''; ?> >
						<?php $this->add_render_attribute( 'image-url', 'content', $settings['image']['url'] ); ?>
							<?php if ( 'yes' === $enable_schema ) { ?>
								<meta itemprop="url" <?php echo wp_kses_post( $this->get_render_attribute_string( 'image-url' ) ); ?>>
							<?php } ?>
						<?php
						$image_id = $settings['image']['id'];
						$img_url = Group_Control_Image_Size::get_attachment_image_src( $image_id, 'image', $settings );

						if ( ! $img_url ) {
							$img_url = $settings['image']['url'];
						}

						$this->add_render_attribute( 'image', 'src', $img_url );
						$this->add_render_attribute( 'image', 'itemprop', 'image' );
						$this->add_render_attribute( 'image', 'alt', Control_Media::get_image_alt( $settings['image'] ) );
						$this->add_render_attribute( 'image', 'title', Control_Media::get_image_title( $settings['image'] ) );

						echo '<img ' . wp_kses_post( $this->get_render_attribute_string( 'image' ) ) . '>';
						?>
						<meta itemprop="height" content="">
						<meta itemprop="width" content="">
					</div>
				<?php } ?>
				<div class="pp-recipe-header-content">
					<?php if ( ! empty( $settings['recipe_name'] ) ) { ?>
						<h2 <?php echo wp_kses_post( $this->get_render_attribute_string( 'recipe_name' ) ); ?>>
							<?php echo esc_html( $settings['recipe_name'] ); ?>
						</h2>
					<?php } ?>
					<div class="pp-recipe-meta">
						<?php if ( 'yes' === $settings['author'] ) { ?>
							<span class="pp-recipe-meta-item" <?php echo ( 'yes' === $enable_schema ) ? 'itemprop="author" itemscope="" itemtype="https://schema.org/Person"' : ''; ?> >
								<?php if ( 'yes' === $enable_schema ) { ?>
									<meta itemprop="name" content="<?php echo get_the_author(); ?>">
								<?php } ?>
								<?php echo get_the_author(); ?>
							</span>
						<?php } ?>
						<?php if ( 'yes' === $settings['date'] ) { ?>
							<span class="pp-recipe-meta-item" <?php echo ( 'yes' === $enable_schema ) ? 'itemprop="datePublished"' : ''; ?>>
								<?php the_time( 'F d, Y' ); ?>
							</span>
						<?php } ?>
						<?php if ( 'yes' === $settings['keyword'] && 'yes' === $enable_schema ) { ?>
							<meta itemprop="keywords" content="<?php echo esc_html( $settings['keyword_list'] ); ?>" />
							<meta itemprop="recipeCuisine" content="<?php echo esc_html( $settings['recipe_cuisine'] ); ?>" />
							<meta itemprop="recipeCategory" content="<?php echo esc_html( $settings['recipe_category'] ); ?>" />
						<?php } ?>
						<?php if ( 'yes' === $settings['rating'] ) { ?>
							<span class="pp-recipe-meta-item" <?php echo ( 'yes' === $enable_schema ) ? 'itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating"' : ''; ?> >
								<?php if ( 'yes' === $enable_schema ) { ?>
									<meta itemprop="ratingValue" content="<?php echo esc_html( $settings['rating_value'] ); ?>" />
									<meta itemprop="bestRating" content="<?php echo esc_html( $settings['best_rating'] ); ?>" />
									<meta itemprop="worstRating" content="<?php echo esc_html( $settings['worst_rating'] ); ?>" />
									<meta itemprop="ratingCount" content="<?php echo esc_html( $settings['total_rating'] ); ?>" />
								<?php } ?>
								<?php echo esc_html( $settings['rating_value'] ); ?>
								<span class="pp-rating-icon">
									<?php $this->render_recipe_details_icon( $settings['rating_icon'], '' ); ?>
								</span>
							</span>
						<?php } ?>
					</div>
					<?php if ( ! empty( $settings['recipe_description'] ) ) { ?>
						<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'recipe_description' ) ); ?>>
							<?php echo $this->parse_text_editor( $settings['recipe_description'] ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					<?php } ?>
				</div>
			</div>
			<div class="pp-recipe-details pp-recipe-section">
				<ul class="pp-recipe-detail-list">
					<?php if ( $settings['prep_time'] ) { ?>
					<li <?php echo ( 'yes' === $enable_schema ) ? 'itemprop="prepTime"' : ''; ?> content="PT<?php echo esc_attr( $settings['prep_time'] ); ?>M">
						<?php $this->render_recipe_details_icon( $settings['prep_time_icon'] ); ?>
						<span class="pp-recipe-detail-content">
							<span class="pp-recipe-detail-title">
								<?php
								$prep_time_title = ( $settings['prep_time_title'] ) ? $settings['prep_time_title'] : __( 'Prep Time', 'powerpack' );

								echo esc_html( $prep_time_title );
								?>
							</span>
							<span class="pp-recipe-detail-value">
								<?php echo esc_html( $settings['prep_time'] ) . ' ' . esc_html( $settings['prep_time_unit'] ); ?>
							</span>
						</span>
					</li>
					<?php } ?>
					<?php if ( $settings['cook_time'] ) { ?>
					<li <?php echo ( 'yes' === $enable_schema ) ? 'itemprop="cookTime"' : ''; ?> content="PT3<?php echo esc_attr( $settings['cook_time'] ); ?>M">
						<?php $this->render_recipe_details_icon( $settings['cook_time_icon'] ); ?>
						<span class="pp-recipe-detail-content">
							<span class="pp-recipe-detail-title">
								<?php
								$cook_time_title = ( $settings['cook_time_title'] ) ? $settings['cook_time_title'] : __( 'Cook Time', 'powerpack' );

								echo esc_html( $cook_time_title );
								?>
							</span>
							<span class="pp-recipe-detail-value">
								<?php echo esc_html( $settings['cook_time'] ) . ' ' . esc_html( $settings['cook_time_unit'] ); ?>
							</span>
						</span>
					</li>
					<?php } ?>
					<?php if ( $settings['total_time'] ) { ?>
					<li <?php echo ( 'yes' === $enable_schema ) ? 'itemprop="totalTime"' : ''; ?> content="PT<?php echo esc_attr( $settings['total_time'] ); ?>M">
						<?php $this->render_recipe_details_icon( $settings['total_time_icon'] ); ?>
						<span class="pp-recipe-detail-content">
							<span class="pp-recipe-detail-title">
								<?php
								$total_time_title = ( $settings['total_time_title'] ) ? $settings['total_time_title'] : __( 'Total Time', 'powerpack' );

								echo esc_html( $total_time_title );
								?>
							</span>
							<span class="pp-recipe-detail-value">
								<?php echo esc_html( $settings['total_time'] ) . ' ' . esc_html( $settings['total_time_unit'] ); ?>
							</span>
						</span>
					</li>
					<?php } ?>
					<?php if ( $settings['servings'] ) { ?>
					<li <?php echo ( 'yes' === $enable_schema ) ? 'itemprop="recipeYield"' : ''; ?> content="<?php echo esc_attr( $settings['servings'] ); ?>">
						<?php $this->render_recipe_details_icon( $settings['servings_icon'] ); ?>
						<span class="pp-recipe-detail-content">
							<span class="pp-recipe-detail-title">
								<?php
								$servings_title = ( $settings['servings_title'] ) ? $settings['servings_title'] : __( 'Serves', 'powerpack' );

								echo esc_html( $servings_title );
								?>
							</span>
							<span class="pp-recipe-detail-value">
								<?php echo esc_html( $settings['servings'] ) . ' ' . esc_html( $settings['servings_unit'] ); ?>
							</span>
						</span>
					</li>
					<?php } ?>
					<?php if ( $settings['calories'] ) { ?>
					<li <?php echo ( 'yes' === $enable_schema ) ? 'itemprop="nutrition" itemscope="" itemtype="https://schema.org/NutritionInformation"' : ''; ?> >
						<span <?php echo ( 'yes' === $enable_schema ) ? 'itemprop="calories"' : ''; ?>>
							<?php $this->render_recipe_details_icon( $settings['calories_icon'] ); ?>
							<span class="pp-recipe-detail-content">
								<span class="pp-recipe-detail-title">
									<?php
									$calories_title = ( $settings['calories_title'] ) ? $settings['calories_title'] : __( 'Calories', 'powerpack' );

									echo esc_html( $calories_title );
									?>
								</span>
								<span class="pp-recipe-detail-value">
									<?php echo esc_html( $settings['calories'] ) . ' ' . esc_html( $settings['calories_unit'] ); ?>
								</span>
							</span>
						</span>
					</li>
					<?php } ?>
				</ul>
			</div>
			<div class="pp-recipe-ingredients pp-recipe-section">
				<h3 class="pp-recipe-section-heading pp-recipe-ingredients-heading">
					<?php echo ( $settings['ingredients_title'] ) ? wp_kses_post( $settings['ingredients_title'] ) : esc_attr__( 'Ingredients', 'powerpack' ); ?>
				</h3>
				<ul class="pp-recipe-ingredients-list">
					<?php
					foreach ( $settings['recipe_ingredients'] as $index => $item ) :

						if ( ! isset( $settings['ingredients_list_icon'] ) && ! Icons_Manager::is_migration_allowed() ) {
							// add old default
							$settings['ingredients_list_icon'] = 'fa fa-square-o';
						}

						$has_icon = ! empty( $settings['ingredients_list_icon'] );

						if ( $has_icon ) {
							$this->add_render_attribute( 'i', 'class', $settings['ingredients_list_icon'] );
							$this->add_render_attribute( 'i', 'aria-hidden', 'true' );
						}

						if ( ! $has_icon && ! empty( $settings['ingredients_icon']['value'] ) ) {
							$has_icon = true;
						}
						$migrated = isset( $settings['__fa4_migrated']['ingredients_icon'] );
						$is_new = ! isset( $settings['ingredients_list_icon'] ) && Icons_Manager::is_migration_allowed();

						$ingredient_key = $this->get_repeater_setting_key( 'recipe_ingredient', 'recipe_ingredients', $index );
						$this->add_render_attribute( $ingredient_key, 'class', 'pp-recipe-ingredient-text' );
						$this->add_inline_editing_attributes( $ingredient_key, 'none' );

						if ( $item['recipe_ingredient'] ) : ?>
								<li class="pp-recipe-ingredient">
									<?php if ( $has_icon ) { ?>
										<span class="pp-icon">
										<?php
										if ( $is_new || $migrated ) {
											Icons_Manager::render_icon( $settings['ingredients_icon'], [ 'aria-hidden' => 'true' ] );
										} elseif ( ! empty( $settings['ingredients_list_icon'] ) ) {
											?><i <?php echo wp_kses_post( $this->get_render_attribute_string( 'i' ) ); ?>></i><?php
										}
										?>
										</span>
									<?php } ?>
									<span <?php echo ( 'yes' === $enable_schema ) ? 'itemprop="recipeIngredient"' : ''; ?> <?php echo wp_kses_post( $this->get_render_attribute_string( $ingredient_key ) ); ?>>
										<?php echo wp_kses_post( $item['recipe_ingredient'] ); ?>
									</span>
								</li>
								<?php
							endif;
					endforeach;
					?>
				</ul>
			</div>
			<div class="pp-recipe-instructions pp-recipe-section">
				<h3 class="pp-recipe-section-heading pp-recipe-instructions-heading">
					<?php echo ( $settings['instructions_title'] ) ? wp_kses_post( $settings['instructions_title'] ) : esc_attr__( 'Instructions', 'powerpack' ); ?>
				</h3>
				<ol class="pp-recipe-instructions-list">
					<?php
					foreach ( $settings['recipe_instructions'] as $index => $item ) :
						$instruction_key = $this->get_repeater_setting_key( 'recipe_instruction', 'recipe_instructions', $index );
						$this->add_render_attribute( $instruction_key, 'class', 'pp-recipe-instruction' );
						$this->add_inline_editing_attributes( $instruction_key, 'none' );

						if ( $item['recipe_instruction'] ) : ?>
								<li <?php echo ( 'yes' === $enable_schema ) ? 'itemprop="recipeInstructions"' : ''; ?> <?php echo wp_kses_post( $this->get_render_attribute_string( $instruction_key ) ); ?>>
									<?php echo wp_kses_post( $item['recipe_instruction'] ); ?>
								</li>
								<?php
							endif;
						endforeach;
					?>
				</ol>
			</div>
			<?php if ( $settings['item_notes'] ) { ?>
				<div class="pp-recipe-notes pp-recipe-section">
					<h3 class="pp-recipe-section-heading pp-recipe-notes-heading">
						<?php echo ( $settings['notes_title'] ) ? wp_kses_post( $settings['notes_title'] ) : esc_attr__( 'Notes', 'powerpack' ); ?>
					</h3>
					<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'item_notes' ) ); ?>>
						<?php
							$pa_allowed_html = wp_kses_allowed_html();
							echo wp_kses( $settings['item_notes'], $pa_allowed_html );
						?>
					</div>
				</div>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Render recipe widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @access protected
	 */
	protected function content_template() {
		?>
		<#
			function render_recipe_details_icon( icon ) {
				if ( '' != icon.value && icon.value ) {
					var recipeIconHTML = elementor.helpers.renderIcon( view, icon, { 'aria-hidden': true }, 'i' , 'object' );
					#>
					<span class="pp-recipe-detail-icon">
						{{{ recipeIconHTML.value }}}
					</span>
					<#
				}
			}

			function recipe_details_template() {
				#>
				<div class="pp-recipe-details pp-recipe-section">
					<ul class="pp-recipe-detail-list">
						<# if ( settings.prep_time != '' ) { #>
							<li>
								<# render_recipe_details_icon( settings.prep_time_icon ); #>
								<span class="pp-recipe-detail-content">
									<span class="pp-recipe-detail-title">
										<#
											var prep_time_title = ( settings.prep_time_title ) ? settings.prep_time_title : 'Prep Time';

											print( prep_time_title );
										#>
									</span>
									<span class="pp-recipe-detail-value">
										<#
											if ( settings.prep_time != '' ) {
												var prep_time_html = '<span' + ' ' + view.getRenderAttributeString( 'prep_time' ) + '>' + settings.prep_time + ' ' + settings.prep_time_unit + '</span>';

												print( prep_time_html );
											}
										#>
									</span>
								</span>
							</li>
						<# } #>
						<# if ( settings.cook_time != '' ) { #>
							<li>
								<# render_recipe_details_icon( settings.cook_time_icon ); #>
								<span class="pp-recipe-detail-content">
									<span class="pp-recipe-detail-title">
										<#
											var cook_time_title = ( settings.cook_time_title ) ? settings.cook_time_title : 'Cook Time';

											print( cook_time_title );
										#>
									</span>
									<span class="pp-recipe-detail-value">
										<#
											if ( settings.cook_time != '' ) {
												var cook_time_html = '<span' + ' ' + view.getRenderAttributeString( 'cook_time' ) + '>' + settings.cook_time + ' ' + settings.cook_time_unit + '</span>';

												print( cook_time_html );
											}
										#>
									</span>
								</span>
							</li>
						<# } #>
						<# if ( settings.total_time != '' ) { #>
							<li itemprop="totalTime" content="PT45MIN">
								<# render_recipe_details_icon( settings.total_time_icon ); #>
								<span class="pp-recipe-detail-content">
									<span class="pp-recipe-detail-title">
										<#
											var total_time_title = ( settings.total_time_title ) ? settings.total_time_title : 'Total Time';

											print( total_time_title );
										#>
									</span>
									<span class="pp-recipe-detail-value">
										<#
											if ( settings.total_time != '' ) {
												var total_time_html = '<span' + ' ' + view.getRenderAttributeString( 'total_time' ) + '>' + settings.total_time + ' ' + settings.total_time_unit + '</span>';

												print( total_time_html );
											}
										#>
									</span>
								</span>
							</li>
						<# } #>
						<# if ( settings.servings != '' ) { #>
							<li>
								<# render_recipe_details_icon( settings.servings_icon ); #>
								<span class="pp-recipe-detail-content">
									<span class="pp-recipe-detail-title">
										<#
											var servings_title = ( settings.servings_title ) ? settings.servings_title : 'Serves';

											print( servings_title );
										#>
									</span>
									<span class="pp-recipe-detail-value">
										<#
											if ( settings.servings != '' ) {
												var servings_html = '<span' + ' ' + view.getRenderAttributeString( 'servings' ) + '>' + settings.servings + ' ' + settings.servings_unit + '</span>';

												print( servings_html );
											}
										#>
									</span>
								</span>
							</li>
						<# } #>
						<# if ( settings.calories != '' ) { #>
							<li>
								<span itemprop="calories">
									<# render_recipe_details_icon( settings.calories_icon ); #>
									<span class="pp-recipe-detail-content">
										<span class="pp-recipe-detail-title">
											<#
												var calories_title = ( settings.calories_title ) ? settings.calories_title : 'Calories';

												print( calories_title );
											#>
										</span>
										<span class="pp-recipe-detail-value">
											<#
												if ( settings.calories != '' ) {
													var calories_html = '<span' + ' ' + view.getRenderAttributeString( 'calories' ) + '>' + settings.calories + ' ' + settings.calories_unit + '</span>';

													print( calories_html );
												}
											#>
										</span>
									</span>
								</span>
							</li>
						<# } #>
					</ul>
				</div>
				<#
			}

			var i = 1,
			iconHTML = elementor.helpers.renderIcon( view, settings.ingredients_icon, { 'aria-hidden': true }, 'i' , 'object' ),
			migrated = elementor.helpers.isIconMigrated( settings, 'ingredients_icon' );
		#>
		<div class="pp-recipe-container">
			<div class="pp-recipe-header">
				<# if ( settings.image.url != '' ) { #>
					<div class="pp-recipe-header-image">
						<#
						var image = {
							id: settings.image.id,
							url: settings.image.url,
							size: settings.image_size,
							dimension: settings.image_custom_dimension,
							model: view.getEditModel()
						};
						var image_url = elementor.imagesManager.getImageUrl( image );
						#>
						<img src="{{{ image_url }}}" />
					</div><!-- .pp-recipe-header-image -->
				<# } #>
				<div class="pp-recipe-header-content">
					<#
						if ( settings.recipe_name != '' ) {
							var name = settings.recipe_name;

							view.addRenderAttribute( 'recipe_name', 'class', 'pp-recipe-title' );

							view.addInlineEditingAttributes( 'recipe_name' );

							var recipe_name_html = '<h2' + ' ' + view.getRenderAttributeString( 'recipe_name' ) + '>' + name + '</h2>';

							print( recipe_name_html );
						}
					#>
					<div class="pp-recipe-meta">
						<# if ( settings.author == 'yes' ) { #>
							<span class="pp-recipe-meta-item" itemprop="author">
								<?php echo get_the_author(); ?>
							</span>
						<# } #>
						<# if ( settings.date == 'yes' ) { #>
							<span class="pp-recipe-meta-item" itemprop="datePublished">
								<?php the_time( 'F d, Y' ); ?>
							</span>
						<# } #>
						<# if ( settings.rating == 'yes' ) { #>
							<span class="pp-recipe-meta-item" itemprop="aggregateRating">
								<# print( settings.rating_value );  #>
								<span class="pp-rating-icon">
									<# render_recipe_details_icon( settings.rating_icon, '' ); #>
								</span>
							</span>
						<# } #>
					</div>
					<#
						if ( settings.recipe_description != '' ) {
							var description = settings.recipe_description;

							view.addRenderAttribute( 'recipe_description', 'class', 'pp-recipe-description' );

							view.addInlineEditingAttributes( 'recipe_description', 'basic' );

							var description_html = '<div' + ' ' + view.getRenderAttributeString( 'recipe_description' ) + '>' + description + '</div>';

							print( description_html );
						}
					#>
				</div>
			</div>

			<# recipe_details_template(); #>

			<div class="pp-recipe-ingredients pp-recipe-section">
				<h3 class="pp-recipe-section-heading pp-recipe-ingredients-heading">
					<# if ( settings.ingredients_title ) { #>
						{{{ settings.ingredients_title }}}
					<# } else { #>
						<?php esc_attr_e( 'Ingredients', 'powerpack' ); ?>
					<# } #>
				</h3>
				<ul class="pp-recipe-ingredients-list">
					<# _.each( settings.recipe_ingredients, function( item ) { #>
						<# if ( item.recipe_ingredient != '' ) { #>
							<li class="pp-recipe-ingredient">
								<# if ( settings.ingredients_list_icon || settings.ingredients_icon ) { #>
									<span class="pp-icon">
										<# if ( iconHTML && iconHTML.rendered && ( ! settings.ingredients_list_icon || migrated ) ) { #>
										{{{ iconHTML.value }}}
										<# } else { #>
											<i class="{{ settings.ingredients_list_icon }}" aria-hidden="true"></i>
										<# } #>
									</span>
								<# } #>

								<#
									var ingredient = item.recipe_ingredient,
										ingredient_key = 'recipe_ingredients.' + (i - 1) + '.recipe_ingredient';

									view.addRenderAttribute( ingredient_key, 'class', 'pp-recipe-ingredient-text' );
								   
									view.addRenderAttribute( ingredient_key, 'itemprop', 'recipeIngredient' );

									view.addInlineEditingAttributes( ingredient_key );

									var ingredient_html = '<span' + ' ' + view.getRenderAttributeString( ingredient_key ) + '>' + ingredient + '</span>';

									print( ingredient_html );
								#>
							</li>
						<# } #>
					<# } ); #>
				</ul>
			</div>
			<div class="pp-recipe-instructions pp-recipe-section">
				<h3 class="pp-recipe-section-heading pp-recipe-instructions-heading">
					<# if ( settings.instructions_title ) { #>
						{{{ settings.instructions_title }}}
					<# } else { #>
						<?php esc_attr_e( 'Instructions', 'powerpack' ); ?>
					<# } #>
				</h3>
				<ol class="pp-recipe-instructions-list">
					<# _.each( settings.recipe_instructions, function( item ) { #>
						<# if ( item.recipe_instruction != '' ) { #>
							<#
								var instruction = item.recipe_instruction,
									instruction_key = 'recipe_instructions.' + (i - 1) + '.recipe_instruction';

								view.addRenderAttribute( instruction_key, 'class', 'pp-recipe-instruction' );

								view.addRenderAttribute( instruction_key, 'itemprop', 'recipeInstructions' );

								view.addInlineEditingAttributes( instruction_key );

								var instruction_html = '<li' + ' ' + view.getRenderAttributeString( instruction_key ) + '>' + instruction + '</li>';

								print( instruction_html );
							#>
						<# } #>
					<# i++; } ); #>
				</ol>
			</div>
			<# if ( settings.item_notes != '' ) { #>
				<div class="pp-recipe-notes pp-recipe-section">
					<h3 class="pp-recipe-section-heading pp-recipe-notes-heading">
						<# if ( settings.notes_title ) { #>
							{{{ settings.notes_title }}}
						<# } else { #>
							<?php esc_attr_e( 'Notes', 'powerpack' ); ?>
						<# } #>
					</h3>

					<#
						var notes = settings.item_notes,
							notes_key = 'item_notes';

						view.addRenderAttribute( notes_key, 'class', 'pp-recipe-notes-content' );

						view.addInlineEditingAttributes( notes_key, 'advanced' );

						var notes_html = '<div' + ' ' + view.getRenderAttributeString( notes_key ) + '>' + notes + '</div>';

						print( notes_html );
					#>
				</div>
			<# } #>
		</div>
		<?php
	}
}
