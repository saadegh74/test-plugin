<?php
namespace PowerpackElements\Modules\Breadcrumbs\Widgets;

use PowerpackElements\Base\Powerpack_Widget;

// Elementor Classes
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Icons_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography as Scheme_Typography;
use Elementor\Core\Schemes\Color as Scheme_Color;
use Elementor\Modules\DynamicTags\Module as TagsModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Breadcrumbs Widget
 */
class Breadcrumbs extends Powerpack_Widget {

	/**
	 * Retrieve Breadcrumbs widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Breadcrumbs' );
	}

	/**
	 * Retrieve Breadcrumbs widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Breadcrumbs' );
	}

	/**
	 * Retrieve Breadcrumbs widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Breadcrumbs' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the Breadcrumbs widget belongs to.
	 *
	 * @since 1.4.13.1
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'Breadcrumbs' );
	}

	/**
	 * Retrieve the list of scripts the Breadcrumbs widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return array(
			'powerpack-frontend',
		);
	}

	/**
	 * Register Breadcrumbs widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 2.0.3
	 * @access protected
	 */
	protected function register_controls() {

		/**
		 * Content Tab: Breadcrumbs
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_breadcrumbs',
			array(
				'label' => __( 'Breadcrumbs', 'powerpack' ),
			)
		);

		$this->add_control(
			'breadcrumbs_type',
			array(
				'label'              => __( 'Select Type', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'powerpack',
				'frontend_available' => true,
				'options'            => array(
					'powerpack' => __( 'PowerPack', 'powerpack' ),
					'yoast'     => __( 'Yoast', 'powerpack' ),
					'rankmath'  => __( 'Rank Math SEO', 'powerpack' ),
					'navxt'     => __( 'Breadcrumb NavXT', 'powerpack' ),
					'seopress'  => __( 'SEOPress', 'powerpack' ),
				),
			)
		);

		$this->add_control(
			'show_home',
			array(
				'label'        => __( 'Show Home', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'On', 'powerpack' ),
				'label_off'    => __( 'Off', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'breadcrumbs_type' => 'powerpack',
				),
			)
		);

		$this->add_control(
			'home_text',
			array(
				'label'     => __( 'Home Text', 'powerpack' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Home', 'powerpack' ),
				'dynamic'   => array(
					'active'     => true,
					'categories' => array( TagsModule::POST_META_CATEGORY ),
				),
				'condition' => array(
					'breadcrumbs_type' => 'powerpack',
					'show_home'        => 'yes',
				),
			)
		);

		$this->add_control(
			'select_home_icon',
			array(
				'label'            => __( 'Home Icon', 'powerpack' ),
				'type'             => Controls_Manager::ICONS,
				'label_block'      => false,
				'skin'             => 'inline',
				'fa4compatibility' => 'home_icon',
				'default'          => array(
					'value'   => 'fas fa-home',
					'library' => 'fa-solid',
				),
				'condition'        => array(
					'breadcrumbs_type' => 'powerpack',
					'show_home'        => 'yes',
				),
			)
		);

		$this->add_control(
			'blog_text',
			array(
				'label'     => __( 'Blog Text', 'powerpack' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Blog', 'powerpack' ),
				'dynamic'   => array(
					'active'     => true,
					'categories' => array( TagsModule::POST_META_CATEGORY ),
				),
				'condition' => array(
					'breadcrumbs_type' => 'powerpack',
				),
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'                => __( 'Alignment', 'powerpack' ),
				'type'                 => Controls_Manager::CHOOSE,
				'default'              => '',
				'options'              => array(
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
				'selectors_dictionary' => array(
					'left'   => 'flex-start',
					'center' => 'center',
					'right'  => 'flex-end',
				),
				'separator'            => 'before',
				'selectors'            => array(
					'{{WRAPPER}} .pp-breadcrumbs' => 'justify-content: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		/**
		 * Content Tab: Separator
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_separator',
			array(
				'label'     => __( 'Separator', 'powerpack' ),
				'condition' => array(
					'breadcrumbs_type' => 'powerpack',
				),
			)
		);

		$this->add_control(
			'separator_type',
			array(
				'label'     => __( 'Separator Type', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'icon',
				'options'   => array(
					'text' => __( 'Text', 'powerpack' ),
					'icon' => __( 'Icon', 'powerpack' ),
				),
				'condition' => array(
					'breadcrumbs_type' => 'powerpack',
				),
			)
		);

		$this->add_control(
			'separator_text',
			array(
				'label'     => __( 'Separator', 'powerpack' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( '>', 'powerpack' ),
				'condition' => array(
					'breadcrumbs_type' => 'powerpack',
					'separator_type'   => 'text',
				),
			)
		);

		$this->add_control(
			'select_separator_icon',
			array(
				'label'            => __( 'Separator', 'powerpack' ),
				'type'             => Controls_Manager::ICONS,
				'label_block'      => false,
				'skin'             => 'inline',
				'fa4compatibility' => 'separator_icon',
				'default'          => array(
					'value'   => 'fas fa-angle-right',
					'library' => 'fa-solid',
				),
				'recommended'      => array(
					'fa-regular' => array(
						'circle',
						'square',
						'window-minimize',
					),
					'fa-solid'   => array(
						'angle-right',
						'angle-double-right',
						'caret-right',
						'chevron-right',
						'bullseye',
						'circle',
						'dot-circle',
						'genderless',
						'greater-than',
						'grip-lines',
						'grip-lines-vertical',
						'minus',
					),
				),
				'condition'        => array(
					'breadcrumbs_type' => 'powerpack',
					'separator_type'   => 'icon',
				),
			)
		);

		$this->end_controls_section();

		/**
		 * Style Tab: Items
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_breadcrumbs_style',
			array(
				'label' => __( 'Items', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'breadcrumbs_items_spacing',
			array(
				'label'     => __( 'Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 10,
				),
				'range'     => array(
					'px' => array(
						'max' => 50,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-breadcrumbs' => 'margin-left: -{{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .pp-breadcrumbs.pp-breadcrumbs-powerpack > li' => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .pp-breadcrumbs:not(.pp-breadcrumbs-powerpack) a, {{WRAPPER}} .pp-breadcrumbs:not(.pp-breadcrumbs-powerpack) span:not(.separator)' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_breadcrumbs_style' );

		$this->start_controls_tab(
			'tab_breadcrumbs_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'breadcrumbs_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-breadcrumbs-crumb, {{WRAPPER}} .pp-breadcrumbs:not(.pp-breadcrumbs-powerpack) a, {{WRAPPER}} .pp-breadcrumbs:not(.pp-breadcrumbs-powerpack) span:not(.separator)' => 'color: {{VALUE}}',
					'{{WRAPPER}} .pp-breadcrumbs-crumb .pp-icon svg' => 'fill: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'breadcrumbs_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-breadcrumbs-crumb, {{WRAPPER}} .pp-breadcrumbs:not(.pp-breadcrumbs-powerpack) a, {{WRAPPER}} .pp-breadcrumbs:not(.pp-breadcrumbs-powerpack) span:not(.separator)' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'breadcrumbs_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-breadcrumbs-crumb, {{WRAPPER}} .pp-breadcrumbs:not(.pp-breadcrumbs-powerpack) a, {{WRAPPER}} .pp-breadcrumbs:not(.pp-breadcrumbs-powerpack) span:not(.separator)',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'breadcrumbs_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-breadcrumbs-crumb, {{WRAPPER}} .pp-breadcrumbs:not(.pp-breadcrumbs-powerpack) a, {{WRAPPER}} .pp-breadcrumbs:not(.pp-breadcrumbs-powerpack) span:not(.separator)',
			)
		);

		$this->add_control(
			'breadcrumbs_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-breadcrumbs-crumb, {{WRAPPER}} .pp-breadcrumbs:not(.pp-breadcrumbs-powerpack) a, {{WRAPPER}} .pp-breadcrumbs:not(.pp-breadcrumbs-powerpack) span:not(.separator)' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_breadcrumbs_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'breadcrumbs_color_hover',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-breadcrumbs-crumb-link:hover, {{WRAPPER}} .pp-breadcrumbs:not(.pp-breadcrumbs-powerpack) a:hover' => 'color: {{VALUE}}',
					'{{WRAPPER}} .pp-breadcrumbs-crumb-link:hover .pp-icon svg' => 'fill: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'breadcrumbs_background_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-breadcrumbs-crumb-link:hover, {{WRAPPER}} .pp-breadcrumbs:not(.pp-breadcrumbs-powerpack) a:hover' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'breadcrumbs_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-breadcrumbs-crumb-link:hover, {{WRAPPER}} .pp-breadcrumbs:not(.pp-breadcrumbs-powerpack) a:hover' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'breadcrumbs_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'separator'  => 'before',
				'selectors'  => array(
					'{{WRAPPER}} .pp-breadcrumbs-crumb, {{WRAPPER}} .pp-breadcrumbs:not(.pp-breadcrumbs-powerpack) a, {{WRAPPER}} .pp-breadcrumbs:not(.pp-breadcrumbs-powerpack) span:not(.separator)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		/**
		 * Style Tab: Separators
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_separators_style',
			array(
				'label'     => __( 'Separators', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'breadcrumbs_type' => array( 'powerpack', 'rankmath' ),
				),
			)
		);

		$this->add_control(
			'separators_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-breadcrumbs-separator, {{WRAPPER}} .pp-breadcrumbs .separator' => 'color: {{VALUE}}',
					'{{WRAPPER}} .pp-breadcrumbs-separator svg' => 'fill: {{VALUE}}',
				),
				'condition' => array(
					'breadcrumbs_type' => array( 'powerpack', 'rankmath' ),
				),
			)
		);

		$this->add_control(
			'separators_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-breadcrumbs-separator, {{WRAPPER}} .pp-breadcrumbs .separator' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'breadcrumbs_type' => array( 'powerpack', 'rankmath' ),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'separators_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-breadcrumbs-separator, {{WRAPPER}} .pp-breadcrumbs .separator',
				'condition' => array(
					'breadcrumbs_type' => array( 'powerpack', 'rankmath' ),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'separators_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-breadcrumbs-separator, {{WRAPPER}} .pp-breadcrumbs .separator',
				'condition'   => array(
					'breadcrumbs_type' => array( 'powerpack', 'rankmath' ),
				),
			)
		);

		$this->add_control(
			'separators_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-breadcrumbs-separator, {{WRAPPER}} .pp-breadcrumbs .separator' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'breadcrumbs_type' => array( 'powerpack', 'rankmath' ),
				),
			)
		);

		$this->add_responsive_control(
			'separators_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-breadcrumbs-separator, {{WRAPPER}} .pp-breadcrumbs .separator' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'breadcrumbs_type' => array( 'powerpack', 'rankmath' ),
				),
			)
		);

		$this->end_controls_section();

		/**
		 * Style Tab: Current
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_current_style',
			array(
				'label'     => __( 'Current', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'breadcrumbs_type' => 'powerpack',
				),
			)
		);

		$this->add_control(
			'current_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-breadcrumbs-crumb-current' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'breadcrumbs_type' => 'powerpack',
				),
			)
		);

		$this->add_control(
			'current_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-breadcrumbs-crumb-current' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'breadcrumbs_type' => 'powerpack',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'current_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-breadcrumbs-crumb-current',
				'condition' => array(
					'breadcrumbs_type' => 'powerpack',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'current_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-breadcrumbs-crumb-current',
				'condition'   => array(
					'breadcrumbs_type' => 'powerpack',
				),
			)
		);

		$this->add_control(
			'current_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-breadcrumbs-crumb-current' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'breadcrumbs_type' => 'powerpack',
				),
			)
		);

		$this->end_controls_section();

	}

	/**
	 * Render Breadcrumbs widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( 'powerpack' === $settings['breadcrumbs_type'] ) {
			$query = $this->get_query();

			if ( $query ) {
				if ( $query->have_posts() ) {

					$this->render_breadcrumbs( $query );

					wp_reset_postdata();
				}
			} else {
				$this->render_breadcrumbs();
			}
		} else {
			if (
				( 'yoast' === $settings['breadcrumbs_type'] && function_exists( 'yoast_breadcrumb' ) ) ||
				( 'rankmath' === $settings['breadcrumbs_type'] && function_exists( 'rank_math_the_breadcrumbs' ) ) ||
				( 'navxt' === $settings['breadcrumbs_type'] && function_exists( 'bcn_display' ) ) ||
				( 'seopress' === $settings['breadcrumbs_type'] && function_exists( 'seopress_display_breadcrumbs' ) ) ) { ?>
				<div class="pp-breadcrumbs pp-breadcrumbs-<?php echo esc_attr( $settings['breadcrumbs_type'] ); ?>">
					<?php
					if ( 'yoast' === $settings['breadcrumbs_type'] && function_exists( 'yoast_breadcrumb' ) ) {
						yoast_breadcrumb( '<p id="breadcrumbs">', '</p>' );
					} elseif ( 'rankmath' === $settings['breadcrumbs_type'] && function_exists( 'rank_math_the_breadcrumbs' ) ) {
						rank_math_the_breadcrumbs();
					} elseif ( 'navxt' === $settings['breadcrumbs_type'] && function_exists( 'bcn_display' ) ) {
						bcn_display();
					} elseif ( 'seopress' === $settings['breadcrumbs_type'] && function_exists( 'seopress_display_breadcrumbs' ) ) {
						seopress_display_breadcrumbs();
					}
					?>
				</div>
				<?php
			}
		}
	}

	protected function get_query() {
		$settings = $this->get_settings_for_display();

		global $post;

		$post_type = 'any';

		$args = array(
			'post_type' => $post_type,
		);

		// Posts Query.
		$post_query = new \WP_Query( $args );

		// return $post_query;

		return false;
	}

	protected function render_breadcrumbs( $query = false ) {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'breadcrumbs', 'class', array( 'pp-breadcrumbs', 'pp-breadcrumbs-powerpack' ) );
		$this->add_render_attribute( 'breadcrumbs-item', 'class', 'pp-breadcrumbs-item' );

		// If you have any custom post types with custom taxonomies, put the taxonomy name below (e.g. product_cat)
		$custom_taxonomy = 'product_cat';

		// Get the query & post information
		global $post, $wp_query;

		if ( false === $query ) {
			// Reset post data to parent query
			$wp_query->reset_postdata();

			// Set active query to native query
			$query = $wp_query;
		}

		// Do not display on the homepage
		if ( ! $query->is_front_page() ) {

			// Build the breadcrums
			echo '<ul ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs' ) ) . '>';

			// Home page
			if ( 'yes' === $settings['show_home'] ) {
				$this->render_home_link();
			}

			if ( $query->is_archive() && ! $query->is_tax() && ! $query->is_category() && ! $query->is_tag() ) {

				$this->add_render_attribute(
					'breadcrumbs-item-archive',
					'class',
					array(
						'pp-breadcrumbs-item',
						'pp-breadcrumbs-item-current',
						'pp-breadcrumbs-item-archive',
					)
				);

				echo '<li ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-archive' ) ) . '><strong class="bread-current bread-archive">' . post_type_archive_title( '', false ) . '</strong></li>';

			} elseif ( $query->is_archive() && $query->is_tax() && ! $query->is_category() && ! $query->is_tag() ) {

				// If post is a custom post type
				$post_type = get_post_type();

				// If it is a custom post type display name and link
				if ( 'post' !== $post_type ) {

					$post_type_object  = get_post_type_object( $post_type );
					$post_type_archive = get_post_type_archive_link( $post_type );

					$this->add_render_attribute(
						array(
							'breadcrumbs-item-cpt'       => array(
								'class' => array(
									'pp-breadcrumbs-item',
									'pp-breadcrumbs-item-cat',
									'pp-breadcrumbs-item-custom-post-type-' . $post_type,
								),
							),
							'breadcrumbs-item-cpt-crumb' => array(
								'class' => array(
									'pp-breadcrumbs-crumb',
									'pp-breadcrumbs-crumb-link',
									'pp-breadcrumbs-crumb-cat',
									'pp-breadcrumbs-crumb-custom-post-type-' . $post_type,
								),
								'href'  => $post_type_archive,
								'title' => $post_type_object->labels->name,
							),
						)
					);

					echo '<li ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-cpt' ) ) . '><a ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-cpt-crumb' ) ) . '>' . esc_attr( $post_type_object->labels->name ) . '</a></li>';

					$this->render_separator();

				}

				$this->add_render_attribute(
					array(
						'breadcrumbs-item-tax'       => array(
							'class' => array(
								'pp-breadcrumbs-item',
								'pp-breadcrumbs-item-current',
								'pp-breadcrumbs-item-archive',
							),
						),
						'breadcrumbs-item-tax-crumb' => array(
							'class' => array(
								'pp-breadcrumbs-crumb',
								'pp-breadcrumbs-crumb-current',
							),
						),
					)
				);

				$custom_tax_name = get_queried_object()->name;

				echo '<li ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-tax' ) ) . '><strong ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-tax-crumb' ) ) . '>' . esc_attr( $custom_tax_name ) . '</strong></li>';

			} elseif ( $query->is_single() ) {

				// If post is a custom post type
				$post_type = get_post_type();

				// If it is a custom post type display name and link
				if ( 'post' !== $post_type ) {

					$post_type_object  = get_post_type_object( $post_type );
					$post_type_archive = get_post_type_archive_link( $post_type );

					$this->add_render_attribute(
						array(
							'breadcrumbs-item-cpt'       => array(
								'class' => array(
									'pp-breadcrumbs-item',
									'pp-breadcrumbs-item-cat',
									'pp-breadcrumbs-item-custom-post-type-' . $post_type,
								),
							),
							'breadcrumbs-item-cpt-crumb' => array(
								'class' => array(
									'pp-breadcrumbs-crumb',
									'pp-breadcrumbs-crumb-link',
									'pp-breadcrumbs-crumb-cat',
									'pp-breadcrumbs-crumb-custom-post-type-' . $post_type,
								),
								'href'  => $post_type_archive,
								'title' => $post_type_object->labels->name,
							),
						)
					);

					echo '<li ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-cpt' ) ) . '><a ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-cpt-crumb' ) ) . '>' . esc_attr( $post_type_object->labels->name ) . '</a></li>';

					$this->render_separator();

				}

				// Get post category info
				$category = get_the_category();

				if ( ! empty( $category ) ) {

					// Get last category post is in
					$values = array_values( $category );

					$last_category = reset( $values );

					$categories      = array();
					$get_cat_parents = rtrim( get_category_parents( $last_category->term_id, true, ',' ), ',' );
					$cat_parents     = explode( ',', $get_cat_parents );
					foreach ( $cat_parents as $parent ) {
						$categories[] = get_term_by( 'name', $parent, 'category' );
					}

					// Loop through parent categories and store in variable $cat_display
					$cat_display = '';

					foreach ( $categories as $parent ) {
						if ( ! is_wp_error( get_term_link( $parent ) ) ) {
							$cat_display .= '<li class="pp-breadcrumbs-item pp-breadcrumbs-item-cat"><a class="pp-breadcrumbs-crumb pp-breadcrumbs-crumb-link pp-breadcrumbs-crumb-cat" href="' . get_term_link( $parent ) . '">' . $parent->name . '</a></li>';
							//$cat_display .= $this->render_separator( false );
						}
					}
				}

				// If it's a custom post type within a custom taxonomy
				$taxonomy_exists = taxonomy_exists( $custom_taxonomy );
				$taxonomy_terms = array();

				if ( empty( $last_category ) && ! empty( $custom_taxonomy ) && $taxonomy_exists ) {
					$taxonomy_terms = get_the_terms( $post->ID, $custom_taxonomy );
				}

				// Check if the post is in a category
				if ( ! empty( $last_category ) ) {
					echo wp_kses_post( $cat_display );
					$this->render_separator();

					$this->add_render_attribute(
						array(
							'breadcrumbs-item-post-cat' => array(
								'class' => array(
									'pp-breadcrumbs-item',
									'pp-breadcrumbs-item-current',
									'pp-breadcrumbs-item-' . $post->ID,
								),
							),
							'breadcrumbs-item-post-cat-bread' => array(
								'class' => array(
									'pp-breadcrumbs-crumb',
									'pp-breadcrumbs-crumb-current',
									'pp-breadcrumbs-crumb-' . $post->ID,
								),
								'title' => get_the_title(),
							),
						)
					);

					echo '<li ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-post-cat' ) ) . '"><strong ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-post-cat-bread' ) ) . '">' . wp_kses_post( get_the_title() ) . '</strong></li>';

					// Else if post is in a custom taxonomy
				} elseif ( ! empty( $taxonomy_terms ) ) {

					foreach ( $taxonomy_terms as $index => $taxonomy ) {
						$cat_id       = $taxonomy->term_id;
						$cat_nicename = $taxonomy->slug;
						$cat_link     = get_term_link( $taxonomy->term_id, $custom_taxonomy );
						$cat_name     = $taxonomy->name;

						$this->add_render_attribute(
							array(
								'breadcrumbs-item-post-cpt-' . $index => array(
									'class' => array(
										'pp-breadcrumbs-item',
										'pp-breadcrumbs-item-cat',
										'pp-breadcrumbs-item-cat-' . $cat_id,
										'pp-breadcrumbs-item-cat-' . $cat_nicename,
									),
								),
								'breadcrumbs-item-post-cpt-crumb-' . $index => array(
									'class' => array(
										'pp-breadcrumbs-crumb',
										'pp-breadcrumbs-crumb-link',
										'pp-breadcrumbs-crumb-cat',
										'pp-breadcrumbs-crumb-cat-' . $cat_id,
										'pp-breadcrumbs-crumb-cat-' . $cat_nicename,
									),
									'href'  => $cat_link,
									'title' => $cat_name,
								),
							)
						);

						echo '<li ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-post-cpt-' . $index ) ) . '"><a ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-post-cpt-crumb-' . $index ) ) . '>' . esc_attr( $cat_name ) . '</a></li>';

						$this->render_separator();
					}

					$this->add_render_attribute(
						array(
							'breadcrumbs-item-post'       => array(
								'class' => array(
									'pp-breadcrumbs-item',
									'pp-breadcrumbs-item-current',
									'pp-breadcrumbs-item-' . $post->ID,
								),
							),
							'breadcrumbs-item-post-crumb' => array(
								'class' => array(
									'pp-breadcrumbs-crumb',
									'pp-breadcrumbs-crumb-current',
									'pp-breadcrumbs-crumb-' . $post->ID,
								),
								'title' => get_the_title(),
							),
						)
					);

					echo '<li ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-post' ) ) . '"><strong ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-post-crumb' ) ) . '">' . wp_kses_post( get_the_title() ) . '</strong></li>';

				} else {

					$this->add_render_attribute(
						array(
							'breadcrumbs-item-post'       => array(
								'class' => array(
									'pp-breadcrumbs-item',
									'pp-breadcrumbs-item-current',
									'pp-breadcrumbs-item-' . $post->ID,
								),
							),
							'breadcrumbs-item-post-crumb' => array(
								'class' => array(
									'pp-breadcrumbs-crumb',
									'pp-breadcrumbs-crumb-current',
									'pp-breadcrumbs-crumb-' . $post->ID,
								),
								'title' => get_the_title(),
							),
						)
					);

					echo '<li ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-post' ) ) . '"><strong ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-post-crumb' ) ) . '">' . wp_kses_post( get_the_title() ) . '</strong></li>';

				}
			} elseif ( $query->is_category() ) {

					$this->add_render_attribute(
						array(
							'breadcrumbs-item-cat'       => array(
								'class' => array(
									'pp-breadcrumbs-item',
									'pp-breadcrumbs-item-current',
									'pp-breadcrumbs-item-cat',
								),
							),
							'breadcrumbs-item-cat-bread' => array(
								'class' => array(
									'pp-breadcrumbs-crumb',
									'pp-breadcrumbs-crumb-current',
									'pp-breadcrumbs-crumb-cat',
								),
							),
						)
					);

				// Category page
				echo '<li ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-cat' ) ) . '><strong ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-cat-bread' ) ) . '>' . single_cat_title( '', false ) . '</strong></li>';

			} elseif ( $query->is_page() ) {

				// Standard page
				if ( $post->post_parent ) {

					// If child page, get parents
					$anc = get_post_ancestors( $post->ID );

					// Get parents in the right order
					$anc = array_reverse( $anc );

					// Parent page loop
					if ( ! isset( $parents ) ) {
						$parents = null;
					}
					foreach ( $anc as $ancestor ) {
						$parents .= '<li class="pp-breadcrumbs-item pp-breadcrumbs-item-parent pp-breadcrumbs-item-parent-' . $ancestor . '"><a class="pp-breadcrumbs-crumb pp-breadcrumbs-crumb-link pp-breadcrumbs-crumb-parent pp-breadcrumbs-crumb-parent-' . $ancestor . '" href="' . get_permalink( $ancestor ) . '" title="' . get_the_title( $ancestor ) . '">' . get_the_title( $ancestor ) . '</a></li>';

						echo wp_kses_post( $parents );

						//$parents .= $this->render_separator( false );

						$separator_html  = '<li class="pp-breadcrumbs-separator">';
						$separator_html .= $this->get_separator();
						$separator_html .= '</li>';

						\Elementor\Utils::print_unescaped_internal_string( $separator_html );
					}

					// Display parent pages
					//echo wp_kses_post( $parents );

				}

				$this->add_render_attribute(
					array(
						'breadcrumbs-item-page'       => array(
							'class' => array(
								'pp-breadcrumbs-item',
								'pp-breadcrumbs-item-current',
								'pp-breadcrumbs-item-' . $post->ID,
							),
						),
						'breadcrumbs-item-page-crumb' => array(
							'class' => array(
								'pp-breadcrumbs-crumb',
								'pp-breadcrumbs-crumb-current',
								'pp-breadcrumbs-crumb-' . $post->ID,
							),
							'title' => get_the_title(),
						),
					)
				);

				// Just display current page if not parents
				echo '<li ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-page' ) ) . '><strong ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-page-crumb' ) ) . '>' . wp_kses_post( get_the_title() ) . '</strong></li>';

			} elseif ( $query->is_tag() ) {

				// Tag page

				// Get tag information
				$term_id       = get_query_var( 'tag_id' );
				$taxonomy      = 'post_tag';
				$args          = 'include=' . $term_id;
				$terms         = get_terms( $taxonomy, $args );
				$get_term_id   = $terms[0]->term_id;
				$get_term_slug = $terms[0]->slug;
				$get_term_name = $terms[0]->name;

				$this->add_render_attribute(
					array(
						'breadcrumbs-item-tag'       => array(
							'class' => array(
								'pp-breadcrumbs-item',
								'pp-breadcrumbs-item-current',
								'pp-breadcrumbs-item-tag-' . $get_term_id,
								'pp-breadcrumbs-item-tag-' . $get_term_slug,
							),
						),
						'breadcrumbs-item-tag-bread' => array(
							'class' => array(
								'pp-breadcrumbs-crumb',
								'pp-breadcrumbs-crumb-current',
								'pp-breadcrumbs-crumb-tag-' . $get_term_id,
								'pp-breadcrumbs-crumb-tag-' . $get_term_slug,
							),
							'title' => get_the_title(),
						),
					)
				);

				// Display the tag name
				echo '<li ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-tag' ) ) . '><strong ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-tag-bread' ) ) . '>' . wp_kses_post( $get_term_name ) . '</strong></li>';

			} elseif ( $query->is_day() ) {

				$this->add_render_attribute(
					array(
						'breadcrumbs-item-year'        => array(
							'class' => array(
								'pp-breadcrumbs-item',
								'pp-breadcrumbs-item-year',
								'pp-breadcrumbs-item-year-' . get_the_time( 'Y' ),
							),
						),
						'breadcrumbs-item-year-crumb'  => array(
							'class' => array(
								'pp-breadcrumbs-crumb',
								'pp-breadcrumbs-crumb-link',
								'pp-breadcrumbs-crumb-year',
								'pp-breadcrumbs-crumb-year-' . get_the_time( 'Y' ),
							),
							'href'  => get_year_link( get_the_time( 'Y' ) ),
							'title' => get_the_time( 'Y' ),
						),
						'breadcrumbs-item-month'       => array(
							'class' => array(
								'pp-breadcrumbs-item',
								'pp-breadcrumbs-item-month',
								'pp-breadcrumbs-item-month-' . get_the_time( 'm' ),
							),
						),
						'breadcrumbs-item-month-crumb' => array(
							'class' => array(
								'pp-breadcrumbs-crumb',
								'pp-breadcrumbs-crumb-link',
								'pp-breadcrumbs-crumb-month',
								'pp-breadcrumbs-crumb-month-' . get_the_time( 'm' ),
							),
							'href'  => get_month_link( get_the_time( 'Y' ), get_the_time( 'm' ) ),
							'title' => get_the_time( 'M' ),
						),
						'breadcrumbs-item-day'         => array(
							'class' => array(
								'pp-breadcrumbs-item',
								'pp-breadcrumbs-item-current',
								'pp-breadcrumbs-item-' . get_the_time( 'j' ),
							),
						),
						'breadcrumbs-item-day-crumb'   => array(
							'class' => array(
								'pp-breadcrumbs-crumb',
								'pp-breadcrumbs-crumb-current',
								'pp-breadcrumbs-crumb-' . get_the_time( 'j' ),
							),
						),
					)
				);

				// Year link
				echo '<li ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-year' ) ) . '><a ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-year-crumb' ) ) . '>' . wp_kses_post( get_the_time( 'Y' ) ) . ' ' . esc_attr__( 'Archives', 'powerpack' ) . '</a></li>';

				$this->render_separator();

				// Month link
				echo '<li ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-month' ) ) . '><a ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-month-crumb' ) ) . '>' . wp_kses_post( get_the_time( 'M' ) ) . ' ' . esc_attr__( 'Archives', 'powerpack' ) . '</a></li>';

				$this->render_separator();

				// Day display
				echo '<li ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-day' ) ) . '><strong ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-day-crumb' ) ) . '> ' . wp_kses_post( get_the_time( 'jS' ) ) . ' ' . wp_kses_post( get_the_time( 'M' ) ) . ' ' . esc_attr__( 'Archives', 'powerpack' ) . '</strong></li>';

			} elseif ( $query->is_month() ) {

				$this->add_render_attribute(
					array(
						'breadcrumbs-item-year'        => array(
							'class' => array(
								'pp-breadcrumbs-item',
								'pp-breadcrumbs-item-year',
								'pp-breadcrumbs-item-year-' . get_the_time( 'Y' ),
							),
						),
						'breadcrumbs-item-year-crumb'  => array(
							'class' => array(
								'pp-breadcrumbs-crumb',
								'pp-breadcrumbs-crumb-year',
								'pp-breadcrumbs-crumb-year-' . get_the_time( 'Y' ),
							),
							'href'  => get_year_link( get_the_time( 'Y' ) ),
							'title' => get_the_time( 'Y' ),
						),
						'breadcrumbs-item-month'       => array(
							'class' => array(
								'pp-breadcrumbs-item',
								'pp-breadcrumbs-item-month',
								'pp-breadcrumbs-item-month-' . get_the_time( 'm' ),
							),
						),
						'breadcrumbs-item-month-crumb' => array(
							'class' => array(
								'pp-breadcrumbs-crumb',
								'pp-breadcrumbs-crumb-month',
								'pp-breadcrumbs-crumb-month-' . get_the_time( 'm' ),
							),
							'title' => get_the_time( 'M' ),
						),
					)
				);

				// Year link
				echo '<li ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-year' ) ) . '><strong ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-year-crumb' ) ) . '>' . wp_kses_post( get_the_time( 'Y' ) ) . ' ' . esc_attr__( 'Archives', 'powerpack' ) . '</strong></li>';

				$this->render_separator();

				// Month display
				echo '<li ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-month' ) ) . '><strong ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-month-crumb' ) ) . '>' . wp_kses_post( get_the_time( 'M' ) ) . ' ' . esc_attr__( 'Archives', 'powerpack' ) . '</strong></li>';

			} elseif ( $query->is_year() ) {

				$this->add_render_attribute(
					array(
						'breadcrumbs-item-year'       => array(
							'class' => array(
								'pp-breadcrumbs-item',
								'pp-breadcrumbs-item-current',
								'pp-breadcrumbs-item-current-' . get_the_time( 'Y' ),
							),
						),
						'breadcrumbs-item-year-crumb' => array(
							'class' => array(
								'pp-breadcrumbs-crumb',
								'pp-breadcrumbs-crumb-current',
								'pp-breadcrumbs-crumb-current-' . get_the_time( 'Y' ),
							),
							'title' => get_the_time( 'Y' ),
						),
					)
				);

				// Display year archive
				echo '<li ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-year' ) ) . '><strong ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-year-crumb' ) ) . '>' . wp_kses_post( get_the_time( 'Y' ) ) . ' ' . esc_attr__( 'Archives', 'powerpack' ) . '</strong></li>';

			} elseif ( $query->is_author() ) {

				// Get the author information
				global $author;
				$userdata = get_userdata( $author );

				$this->add_render_attribute(
					array(
						'breadcrumbs-item-author'       => array(
							'class' => array(
								'pp-breadcrumbs-item',
								'pp-breadcrumbs-item-current',
								'pp-breadcrumbs-item-current-' . $userdata->user_nicename,
							),
						),
						'breadcrumbs-item-author-bread' => array(
							'class' => array(
								'pp-breadcrumbs-crumb',
								'pp-breadcrumbs-crumb-current',
								'pp-breadcrumbs-crumb-current-' . $userdata->user_nicename,
							),
							'title' => $userdata->display_name,
						),
					)
				);

				// Display author name
				echo '<li ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-author' ) ) . '><strong ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-author-bread' ) ) . '>' . esc_attr__( 'Author:', 'powerpack' ) . ' ' . esc_attr( $userdata->display_name ) . '</strong></li>';

			} elseif ( get_query_var( 'paged' ) ) {

				$this->add_render_attribute(
					array(
						'breadcrumbs-item-paged'       => array(
							'class' => array(
								'pp-breadcrumbs-item',
								'pp-breadcrumbs-item-current',
								'pp-breadcrumbs-item-current-' . get_query_var( 'paged' ),
							),
						),
						'breadcrumbs-item-paged-bread' => array(
							'class' => array(
								'pp-breadcrumbs-crumb',
								'pp-breadcrumbs-crumb-current',
								'pp-breadcrumbs-crumb-current-' . get_query_var( 'paged' ),
							),
							'title' => __( 'Page', 'powerpack' ) . ' ' . get_query_var( 'paged' ),
						),
					)
				);

				// Paginated archives
				echo '<li ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-paged' ) ) . '><strong ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-paged-bread' ) ) . '>' . esc_attr__( 'Page', 'powerpack' ) . ' ' . wp_kses_post( get_query_var( 'paged' ) ) . '</strong></li>';

			} elseif ( $query->is_search() ) {

				// Search results page
				$this->add_render_attribute(
					array(
						'breadcrumbs-item-search'       => array(
							'class' => array(
								'pp-breadcrumbs-item',
								'pp-breadcrumbs-item-current',
								'pp-breadcrumbs-item-current-' . get_search_query(),
							),
						),
						'breadcrumbs-item-search-crumb' => array(
							'class' => array(
								'pp-breadcrumbs-crumb',
								'pp-breadcrumbs-crumb-current',
								'pp-breadcrumbs-crumb-current-' . get_search_query(),
							),
							'title' => __( 'Search results for:', 'powerpack' ) . ' ' . get_search_query(),
						),
					)
				);

				// Search results page
				echo '<li ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-search' ) ) . '><strong ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-search-crumb' ) ) . '>' . esc_attr__( 'Search results for:', 'powerpack' ) . ' ' . get_search_query() . '</strong></li>';

			} elseif ( $query->is_home() ) {

				$blog_label = $settings['blog_text'];

				if ( $blog_label ) {
					$this->add_render_attribute(
						array(
							'breadcrumbs-item-blog'       => array(
								'class' => array(
									'pp-breadcrumbs-item',
									'pp-breadcrumbs-item-current',
								),
							),
							'breadcrumbs-item-blog-crumb' => array(
								'class' => array(
									'pp-breadcrumbs-crumb',
									'pp-breadcrumbs-crumb-current',
								),
								'title' => $blog_label,
							),
						)
					);

					// Just display current page if not parents
					echo '<li ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-blog' ) ) . '><strong ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-blog-crumb' ) ) . '>' . esc_attr( $blog_label ) . '</strong></li>';
				}
			} elseif ( $query->is_404() ) {
				$this->add_render_attribute(
					array(
						'breadcrumbs-item-error' => array(
							'class' => array(
								'pp-breadcrumbs-item',
								'pp-breadcrumbs-item-current',
							),
						),
					)
				);

				// 404 page
				echo '<li ' . wp_kses_post( $this->get_render_attribute_string( 'breadcrumbs-item-error' ) ) . '>' . esc_attr__( 'Error 404', 'powerpack' ) . '</li>';
			}

			echo '</ul>';

		}

	}

	protected function get_separator() {
		$settings = $this->get_settings_for_display();

		ob_start();
		if ( 'icon' === $settings['separator_type'] ) {

			if ( ! isset( $settings['separator_icon'] ) && ! Icons_Manager::is_migration_allowed() ) {
				// add old default
				$settings['separator_icon'] = 'fa fa-angle-right';
			}

			$has_icon = ! empty( $settings['separator_icon'] );

			if ( $has_icon ) {
				$this->add_render_attribute( 'separator-icon', 'class', $settings['separator_icon'] );
				$this->add_render_attribute( 'separator-icon', 'aria-hidden', 'true' );
			}

			if ( ! $has_icon && ! empty( $settings['select_separator_icon']['value'] ) ) {
				$has_icon = true;
			}
			$migrated = isset( $settings['__fa4_migrated']['select_separator_icon'] );
			$is_new   = ! isset( $settings['separator_icon'] ) && Icons_Manager::is_migration_allowed();

			if ( $has_icon ) {
				?>
				<span class='pp-separator-icon pp-icon'>
					<?php
					if ( $is_new || $migrated ) {
						Icons_Manager::render_icon( $settings['select_separator_icon'], array( 'aria-hidden' => 'true' ) );
					} elseif ( ! empty( $settings['separator_icon'] ) ) {
						?>
						<i <?php $this->print_render_attribute_string( 'separator-icon' ); ?>></i>
						<?php
					}
					?>
				</span>
				<?php
			}
		} else {

			$this->add_inline_editing_attributes( 'separator_text' );
			$this->add_render_attribute( 'separator_text', 'class', 'pp-breadcrumbs-separator-text' );

			echo '<span ' . wp_kses_post( $this->get_render_attribute_string( 'separator_text' ) ) . '>' . esc_attr( $settings['separator_text'] ) . '</span>';

		}
		$separator = ob_get_contents();
		ob_end_clean();

		return $separator;
	}

	protected function render_separator( $output = true ) {
		$settings = $this->get_settings_for_display();

		$html  = '<li class="pp-breadcrumbs-separator">';
		$html .= $this->get_separator();
		$html .= '</li>';

		if ( true === $output ) {
			\Elementor\Utils::print_unescaped_internal_string( $html );
			return;
		}

		return $html;
	}

	protected function render_home_link() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute(
			array(
				'home_item' => array(
					'class' => array(
						'pp-breadcrumbs-item',
						'pp-breadcrumbs-item-home',
					),
				),
				'home_link' => array(
					'class' => array(
						'pp-breadcrumbs-crumb',
						'pp-breadcrumbs-crumb-link',
						'pp-breadcrumbs-crumb-home',
					),
					'href'  => get_home_url(),
					'title' => $settings['home_text'],
				),
				'home_text' => array(
					'class' => array(
						'pp-breadcrumbs-text',
					),
				),
			)
		);

		if ( ! isset( $settings['home_icon'] ) && ! Icons_Manager::is_migration_allowed() ) {
			// add old default
			$settings['home_icon'] = 'fa fa-home';
		}

		$has_home_icon = ! empty( $settings['home_icon'] );

		if ( $has_home_icon ) {
			$this->add_render_attribute( 'i', 'class', $settings['home_icon'] );
			$this->add_render_attribute( 'i', 'aria-hidden', 'true' );
		}

		if ( ! $has_home_icon && ! empty( $settings['select_home_icon']['value'] ) ) {
			$has_home_icon = true;
		}
		$migrated_home_icon = isset( $settings['__fa4_migrated']['select_home_icon'] );
		$is_new_home_icon   = ! isset( $settings['home_icon'] ) && Icons_Manager::is_migration_allowed();
		?>
		<li <?php $this->print_render_attribute_string( 'home_item' ); ?>>
			<a <?php $this->print_render_attribute_string( 'home_link' ); ?>>
				<span <?php $this->print_render_attribute_string( 'home_text' ); ?>>
					<?php if ( ! empty( $settings['home_icon'] ) || ( ! empty( $settings['select_home_icon']['value'] ) && $is_new_home_icon ) ) { ?>
						<span class="pp-icon">
							<?php
							if ( $is_new_home_icon || $migrated_home_icon ) {
								Icons_Manager::render_icon( $settings['select_home_icon'], array( 'aria-hidden' => 'true' ) );
							} elseif ( ! empty( $settings['home_icon'] ) ) {
								?>
								<i <?php $this->print_render_attribute_string( 'i' ); ?>></i>
								<?php
							}
							?>
						</span>
					<?php } ?>
					<?php echo esc_attr( $settings['home_text'] ); ?>
				</span>
			</a>
		</li>
		<?php

		$this->render_separator();
	}
}
