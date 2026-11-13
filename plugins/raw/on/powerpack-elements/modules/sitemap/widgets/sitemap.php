<?php
/**
 * PowerPack Sitemap Widget
 *
 * @package PPE
 */

namespace PowerpackElements\Modules\Sitemap\Widgets;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Classes\PP_Posts_Helper;

use Elementor\Controls_Manager;
use Elementor\Core\Schemes;
use ElementorPro\Modules\QueryControl\Module as Query_Module;
use Elementor\Repeater;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Sitemap Widget.
 *
 * Elementor widget that displays an HTML sitemap.
 */
class Sitemap extends Powerpack_Widget {

	/**
	 * Retrieve sitemap widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Sitemap' );
	}

	/**
	 * Retrieve sitemap widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Sitemap' );
	}

	/**
	 * Retrieve sitemap widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Sitemap' );
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
		return parent::get_widget_keywords( 'Sitemap' );
	}

	/**
	 * Retrieve the list of scripts the sitemap widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return array(
			'pp-treeview',
			'powerpack-frontend',
		);
	}

	/**
	 * Register sitemap widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 2.0.3
	 * @access protected
	 */
	protected function register_controls() {

		$this->register_sitemap_tab();
		$this->register_style_tab();

	}

	private function register_sitemap_tab() {
		$this->start_controls_section(
			'sitemap_section',
			array(
				'label' => __( 'Sitemap', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->register_post_type_controls();

		$this->add_control(
			'sitemap_layout_divider',
			array(
				'type' => Controls_Manager::DIVIDER,
			)
		);

		$this->register_layout_controls();

		$this->end_controls_section();

		$this->register_tree_controls();

	}

	private function register_layout_controls() {
		$this->add_responsive_control(
			'sitemap_columns',
			array(
				'label'          => __( 'Columns', 'powerpack' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '4',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				),
				'selectors'      => array(
					'{{WRAPPER}} .pp-sitemap-section' => 'flex-basis: calc( 1 / {{VALUE}} * 100% );',
				),
			)
		);

		$this->add_control(
			'sitemap_title_tag',
			array(
				'label'   => __( 'Title HTML Tag', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				),
				'default' => 'h2',
			)
		);

		$this->add_control(
			'sitemap_add_nofollow',
			array(
				'label' => __( 'Add nofollow', 'powerpack' ),
				'type'  => Controls_Manager::SWITCHER,
			)
		);

		$this->add_control(
			'sitemap_link_target',
			array(
				'label' => __( 'Open in a new Window', 'powerpack' ),
				'type'  => Controls_Manager::SWITCHER,
			)
		);
	}

	private function register_tree_controls() {
		$this->start_controls_section(
			'sitemap_tree_section',
			array(
				'label'       => __( 'Tree Structure', 'powerpack' ),
				'description' => __( 'Works only when hierarchical view is enabled', 'powerpack' ),
				'tab'         => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'sitemap_tree',
			array(
				'label'              => __( 'Tree Layout', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => '',
				'label_on'           => 'Yes',
				'label_off'          => 'No',
				'return_value'       => 'yes',
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'sitemap_tree_note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( '<b>Note:</b> Hierarchical View option must be enabled for Post Type or Taxonomy for Tree Layout to work.', 'powerpack' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				'condition'       => array(
					'sitemap_tree' => 'yes',
				),
			)
		);

		$this->add_control(
			'sitemap_tree_style',
			array(
				'label'              => __( 'Style', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'plus_circle',
				'options'            => array(
					'caret'       => __( 'Caret', 'powerpack' ),
					'plus_circle' => __( 'Circle ( Plus & Minus )', 'powerpack' ),
					'plus'        => __( 'Plus & Minus', 'powerpack' ),
					'folder'      => __( 'Folder', 'powerpack' ),
				),
				'condition'          => array(
					'sitemap_tree' => 'yes',
				),
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'sitemap_tree_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-sitemap-section ul.pp-tree li' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'sitemap_tree' => 'yes',
				),
			)
		);

		$this->end_controls_section();

	}

	private function register_post_type_controls() {
		$supported_taxonomies = array();

		$public_types = PP_Posts_Helper::get_post_types();

		foreach ( $public_types as $type => $title ) {
			$taxonomies = get_object_taxonomies( $type, 'objects' );
			foreach ( $taxonomies as $key => $tax ) {
				if ( ! in_array( $tax->name, $supported_taxonomies ) ) {
					$label                              = $tax->label . ' (' . $tax->name . ')';
					$supported_taxonomies[ $tax->name ] = $label;
				}
			}
		}

		$repeater = new Repeater();

		$repeater->add_control(
			'sitemap_type_selector',
			array(
				'label'   => __( 'Type', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'post_type',
				'options' => array(
					'post_type' => __( 'Post Type', 'powerpack' ),
					'taxonomy'  => __( 'Taxonomy', 'powerpack' ),
				),
			)
		);

		$repeater->add_control(
			'sitemap_source_post_type',
			array(
				'label'     => __( 'Source', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'page',
				'options'   => $public_types,
				'condition' => array(
					'sitemap_type_selector' => 'post_type',
				),
			)
		);

		$repeater->add_control(
			'sitemap_source_taxonomy',
			array(
				'label'     => __( 'Source', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'category',
				'options'   => $supported_taxonomies,
				'condition' => array(
					'sitemap_type_selector' => 'taxonomy',
				),
			)
		);

		$post_types = PP_Posts_Helper::get_post_types();
		$tax_array = array();

		foreach ( $post_types as $post_type_slug => $post_type_label ) {
			$exclude_post_control_key = 'sitemap_exclude_' . $post_type_slug;
			$repeater->add_control(
				$exclude_post_control_key,
				array(
					'label'        => __( 'Exclude', 'powerpack' ) . ' ' . $post_type_label,
					'type'         => 'pp-query',
					'post_type'    => '',
					'options'      => array(),
					'label_block'  => false,
					'multiple'     => true,
					'query_type'   => 'posts',
					'object_type'  => $post_type_slug,
					'include_type' => true,
					'condition'    => array(
						'sitemap_type_selector'    => 'post_type',
						'sitemap_source_post_type' => $post_type_slug,
					),
				)
			);

			$taxonomy = PP_Posts_Helper::get_post_taxonomies( $post_type_slug );

			if ( ! empty( $taxonomy ) ) {

				foreach ( $taxonomy as $index => $tax ) {

					$terms = PP_Posts_Helper::get_tax_terms( $index );

					if ( in_array( $index, $tax_array, true ) ) {
						continue;
					}
					$tax_array[] = $index;

					if ( ! empty( $terms ) ) {

						$exclude_tax_control_key = 'sitemap_exclude_' . $index;

						$repeater->add_control(
							$exclude_tax_control_key,
							array(
								'label'        => __( 'Exclude', 'powerpack' ) . ' ' . $tax->label,
								'type'         => 'pp-query',
								'post_type'    => $post_type_slug,
								'options'      => array(),
								'label_block'  => false,
								'multiple'     => true,
								'query_type'   => 'terms',
								'object_type'  => $index,
								'include_type' => true,
								'condition'    => array(
									'sitemap_type_selector' => 'taxonomy',
									'sitemap_source_taxonomy' => $index,
								),
							)
						);
					}
				}
			}
		}

		$repeater->add_control(
			'sitemap_title',
			array(
				'label' => __( 'Title', 'powerpack' ),
				'type'  => Controls_Manager::TEXT,
			)
		);

		$repeater->add_control(
			'sitemap_orderby_post_type',
			array(
				'label'     => __( 'Order By', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'post_date',
				'options'   => array(
					'post_date'  => __( 'Date', 'powerpack' ),
					'post_title' => __( 'Title', 'powerpack' ),
					'menu_order' => __( 'Menu Order', 'powerpack' ),
					'rand'       => __( 'Random', 'powerpack' ),
				),
				'condition' => array(
					'sitemap_type_selector' => 'post_type',
				),
			)
		);

		$repeater->add_control(
			'sitemap_orderby_taxonomy',
			array(
				'label'     => __( 'Order By', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'name',
				'options'   => array(
					'id'   => __( 'ID', 'powerpack' ),
					'name' => __( 'Name', 'powerpack' ),
				),
				'condition' => array(
					'sitemap_type_selector' => 'taxonomy',
				),
			)
		);

		$repeater->add_control(
			'sitemap_order',
			array(
				'label'   => __( 'Order', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'desc',
				'options' => array(
					'asc'  => __( 'ASC', 'powerpack' ),
					'desc' => __( 'DESC', 'powerpack' ),
				),
			)
		);

		$repeater->add_control(
			'sitemap_hide_empty',
			array(
				'label'     => __( 'Hide Empty', 'powerpack' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array(
					'sitemap_type_selector' => 'taxonomy',
				),
			)
		);

		$repeater->add_control(
			'sitemap_hierarchical',
			array(
				'label'   => __( 'Hierarchical View', 'powerpack' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'no',
			)
		);

		$repeater->add_control(
			'sitemap_depth',
			array(
				'label'     => __( 'Depth', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '0',
				'options'   => array(
					'0' => __( 'All', 'powerpack' ),
					'1' => 1,
					'2' => 2,
					'3' => 3,
					'4' => 4,
					'5' => 5,
					'6' => 6,
				),
				'condition' => array(
					'sitemap_hierarchical' => 'yes',
				),
			)
		);

		$this->add_control(
			'sitemap_items',
			array(
				'label'       => '',
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'sitemap_type_selector'    => 'post_type',
						'sitemap_title'            => __( 'Pages', 'powerpack' ),
						'sitemap_source_post_type' => 'page',
					),
					array(
						'sitemap_type_selector'   => 'taxonomy',
						'sitemap_title'           => __( 'Categories', 'powerpack' ),
						'sitemap_source_taxonomy' => 'category',
					),
				),
				'title_field' => '{{{ sitemap_title }}}',
			)
		);
	}

	private function register_style_tab() {
		$this->start_controls_section(
			'section_sitemap_style',
			array(
				'label' => __( 'List', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'sitemap_list_indent',
			array(
				'label'     => __( 'Indent', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'body:not(.rtl) {{WRAPPER}} .pp-sitemap-section > ul' => 'margin-left: {{SIZE}}{{UNIT}};',
					'body.rtl {{WRAPPER}} .pp-sitemap-section > ul' => 'margin-right: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'sitemap_list_bg',
				'label'    => __( 'Background Color', 'powerpack' ),
				'types'    => array( 'classic', 'gradient' ),
				'exclude'  => array( 'image' ),
				'selector' => '{{WRAPPER}} .pp-sitemap-section',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'sitemap_list_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-sitemap-section',
			)
		);

		$this->add_responsive_control(
			'sitemap_list_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-sitemap-section' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'sitemap_list_shadow',
				'selector' => '{{WRAPPER}} .pp-sitemap-section',
			)
		);

		$this->add_responsive_control(
			'sitemap_section_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-sitemap-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_sitemap_title_style',
			array(
				'label' => __( 'Title', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'sitemap_title_align',
			array(
				'label'     => __( 'Alignment', 'powerpack' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'left',
				'options'   => array(
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
				'selectors' => array(
					'{{WRAPPER}} .pp-sitemap-title' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'sitemap_title_bg_color',
				'label'    => __( 'Background Color', 'powerpack' ),
				'types'    => array( 'classic', 'gradient' ),
				'exclude'  => array( 'image' ),
				'selector' => '{{WRAPPER}} .pp-sitemap-title',
			)
		);

		$this->add_control(
			'sitemap_title_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-sitemap-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'sitemap_title_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-sitemap-title',
			)
		);

		$this->add_responsive_control(
			'sitemap_title_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-sitemap-title' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'sitemap_title_shadow',
				'selector' => '{{WRAPPER}} .pp-sitemap-title',
			)
		);

		$this->add_control(
			'sitemap_title_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-sitemap-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'sitemap_title_margin',
			array(
				'label'      => __( 'Margin', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-sitemap-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'sitemap_title_typography',
				'selector' => '{{WRAPPER}} .pp-sitemap-title',
				'scheme'   => Schemes\Typography::TYPOGRAPHY_1,
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_sitemap_list_item_style',
			array(
				'label' => __( 'List Items', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'sitemap_list_item_bg_color',
				'label'    => __( 'Background Color', 'powerpack' ),
				'types'    => array( 'classic', 'gradient' ),
				'exclude'  => array( 'image' ),
				'selector' => '{{WRAPPER}} .pp-sitemap-list',
			)
		);

		$this->add_control(
			'sitemap_list_item_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-sitemap-item, {{WRAPPER}} span.pp-sitemap-list, {{WRAPPER}} .pp-sitemap-item a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'sitemap_list_item_hover',
			array(
				'label'     => __( 'Hover Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-sitemap-item a:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'sitemap_list_item_typography',
				'selector' => '{{WRAPPER}} .pp-sitemap-item, {{WRAPPER}} span.pp-sitemap-list, {{WRAPPER}} .pp-sitemap-item a',
				'scheme'   => Schemes\Typography::TYPOGRAPHY_3,
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'sitemap_list_item_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '',
				'default'     => '',
				'selector'    => '{{WRAPPER}} .pp-sitemap-list > li',
			)
		);

		$this->add_control(
			'sitemap_list_item_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-sitemap-list > li' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'sitemap_bullet_style',
			array(
				'label'     => __( 'Bullet', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'sitemap_tree!' => 'yes',
				),
			)
		);

		$this->add_control(
			'sitemap_bullet_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-sitemap-item' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'sitemap_tree!' => 'yes',
				),
			)
		);

		$this->add_control(
			'sitemap_list_item_bullet_style',
			array(
				'label'       => __( 'Style', 'powerpack' ),
				'type'        => Controls_Manager::CHOOSE,
				'default'     => 'disc',
				'label_block' => true,
				'options'     => array(
					'disc'   => array(
						'title' => __( 'Disc', 'powerpack' ),
						'icon'  => 'eicon-circle',
					),
					'circle' => array(
						'title' => __( 'Circle', 'powerpack' ),
						'icon'  => 'eicon-circle-o',
					),
					'square' => array(
						'title' => __( 'Square', 'powerpack' ),
						'icon'  => 'eicon-square',
					),
					'none'   => array(
						'title' => __( 'None', 'powerpack' ),
						'icon'  => 'eicon-ban',
					),
				),
				'selectors'   => array(
					'{{WRAPPER}} .pp-sitemap-list, {{WRAPPER}} .pp-sitemap-list .children' => 'list-style-type: {{VALUE}};',
				),
				'condition'   => array(
					'sitemap_tree!' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['sitemap_items'] ) ) {
			return;
		}

		$title_tag = $settings['sitemap_title_tag'];

		$posts_query = array();

		$this->add_render_attribute(
			array(
				'category_link' => array(
					'class' => 'pp-sitemap-category-title',
				),
				'wrapper'       => array(
					'class' => 'pp-sitemap-wrap',
				),
			)
		);

		if ( 'yes' === $settings['sitemap_add_nofollow'] ) {
			$this->add_render_attribute( 'a', 'rel', 'nofollow' );
		}

		if ( 'yes' === $settings['sitemap_link_target'] ) {
			$this->add_render_attribute( 'a', 'target', '_blank' );
		}

		echo '<div ' . wp_kses_post( $this->get_render_attribute_string( 'wrapper' ) ) . '>';
		foreach ( $settings['sitemap_items'] as $sitemap_item ) {
			echo $this->render_sitemap_item( $sitemap_item, $title_tag, $posts_query ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</div>';
	}

	protected function get_list_title( $current_title, $item_type, $is_taxonomy ) {
		if ( '' !== $current_title ) {
			return $current_title;
		}

		if ( $is_taxonomy ) {
			$obj = get_taxonomy( $item_type );
			if ( false === $obj ) {
				return '';
			}
			return $obj->label;
		}

		$obj = get_post_type_object( $item_type );
		if ( null === $obj ) {
			return '';
		}
		if ( '' === $obj->labels->name ) {
			return $obj->labels->singular_name;
		}

		return $obj->labels->name;
	}

	protected function render_sitemap_item( $sitemap_item, $title_tag, $query_args ) {
		$hierarchical          = 'yes' === $sitemap_item['sitemap_hierarchical'];
		$max_depth             = $sitemap_item['sitemap_depth'];
		$query_args['orderby'] = $sitemap_item['sitemap_orderby_post_type'];
		$query_args['order']   = $sitemap_item['sitemap_order'];
		$is_taxonomy           = 'taxonomy' === $sitemap_item['sitemap_type_selector'];
		$is_posts              = 'post_type' === $sitemap_item['sitemap_type_selector'];
		$item_type             = $is_taxonomy ? $sitemap_item['sitemap_source_taxonomy'] : $sitemap_item['sitemap_source_post_type'];
		if ( $is_posts ) {
			$query_args['post__not_in'] = $sitemap_item[ 'sitemap_exclude_' . $item_type ];
		}
		if ( $is_taxonomy ) {
			$query_args['exclude'] = $sitemap_item[ 'sitemap_exclude_' . $item_type ];
		}
		$title = $this->get_list_title( $sitemap_item['sitemap_title'], $item_type, $is_taxonomy );

		$this->add_render_attribute(
			array(
				'section' . $item_type  => array(
					'class' => array(
						'pp-sitemap-section',
					),
				),
				'list' . $item_type     => array(
					'class' => array(
						'pp-sitemap-list',
						'pp-sitemap-' . $item_type . '-list',
					),
				),
				$title_tag . $item_type => array(
					'class' => array(
						'pp-sitemap-title',
						'pp-sitemap-' . $item_type . '-title',
					),
				),
				'item' . $item_type     => array(
					'class' => array(
						'pp-sitemap-item',
						'pp-sitemap-item-' . $item_type,
					),
				),
			)
		);

		$items_html = '';

		if ( $is_taxonomy ) {
			$items_html .= $this->sitemap_html_taxonomies( $item_type, $hierarchical, $max_depth, $sitemap_item, $query_args );
		} else {
			$items_html .= $this->sitemap_html_post_types( $item_type, $hierarchical, $max_depth, $query_args );
		}

		$title = empty( $title ) ? '' : sprintf( '<%s %s>%s</%1$s>', $title_tag, $this->get_render_attribute_string( $title_tag . $item_type ), $title );

		$html = sprintf( '<div %s>%s', $this->get_render_attribute_string( 'section' . $item_type ), $title );
		if ( empty( $items_html ) ) {
			$html .= sprintf( '<span %s>%s</span>', $this->get_render_attribute_string( 'list' . $item_type ), __( 'None', 'powerpack' ) );
		} else {
			$html .= sprintf( '<ul %s>%s</ul>', $this->get_render_attribute_string( 'list' . $item_type ), $items_html );
		}
		$html .= '</div>';

		return $html;
	}

	protected function sitemap_html_taxonomies( $taxonomy, $hierarchical, $max_depth, $item_settings, $query_args ) {

		$query_args['hide_empty']       = 'yes' === $item_settings['sitemap_hide_empty'];
		$query_args['show_option_none'] = '';
		$query_args['taxonomy']         = $taxonomy;
		$query_args['title_li']         = '';
		$query_args['echo']             = false;
		$query_args['depth']            = $max_depth;
		$query_args['hierarchical']     = $hierarchical;
		$query_args['orderby']          = $item_settings['sitemap_orderby_taxonomy'];

		$taxonomy_list = wp_list_categories( $query_args );
		$taxonomy_list = $this->add_sitemap_item_classes( 'item' . $taxonomy, $taxonomy_list );

		return $taxonomy_list;
	}

	/**
	 * Post Query by Post Type
	 *
	 * @param string $post_type post type.
	 * @param array  $query_args post query arguments.
	 *
	 * @return \WP_Query
	 */
	protected function query_by_post_type( $post_type, $query_args ) {
		$args = array(
			'posts_per_page'         => -1,
			'update_post_meta_cache' => false,
			'post_type'              => $post_type,
			'filter'                 => 'ids',
			'post_status'            => 'publish',
		);

		$args = array_merge( $query_args, $args );

		$query = new \WP_Query( $args );

		return $query;
	}

	/**
	 * Sitemap HTML Post Types
	 *
	 * @param string $post_type post type.
	 * @param bool   $hierarchical hierarchical or not.
	 * @param int    $depth depth.
	 * @param array  $query_args query arguments.
	 *
	 * @return string
	 */
	protected function sitemap_html_post_types( $post_type, $hierarchical, $depth, $query_args ) {
		$html = '';

		$query_result = $this->query_by_post_type( $post_type, $query_args );

		if ( empty( $query_result ) ) {
			return '';
		}

		if ( $query_result->have_posts() ) {
			if ( ! $hierarchical ) {
				$depth = -1;
			}
			$walker            = new \Walker_Page();
			$walker->tree_type = $post_type;
			$walker_str        = $walker->walk( $query_result->posts, $depth );
			$html             .= $this->add_sitemap_item_classes( 'item' . $post_type, $walker_str );
		}

		return $html;
	}

	protected function add_sitemap_item_classes( $element, $str ) {
		$element_str = $this->get_render_attribute_string( $element );
		/**  remove trailing " */
		$element_str = substr_replace( $element_str, ' ', -1, 1 );
		$source      = array(
			'class="',
		);
		$replace     = array(
			$element_str,
		);

		if ( 'yes' === $this->get_settings_for_display( 'sitemap_add_nofollow' ) ) {
			$source[]  = 'href=';
			$replace[] = 'rel="nofollow" href=';
		}

		if ( 'yes' === $this->get_settings_for_display( 'sitemap_link_target' ) ) {
			$source[]  = 'href=';
			$replace[] = 'target="_blank" href=';
		}

		return str_replace( $source, $replace, $str );
	}
}
