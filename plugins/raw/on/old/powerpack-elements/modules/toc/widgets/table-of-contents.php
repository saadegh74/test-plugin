<?php
namespace PowerpackElements\Modules\Toc\Widgets;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Classes\PP_Helper;
use PowerpackElements\Classes\PP_Config;
use PowerpackElements\GROUP_CONTROL_TRANSITION;
use PowerpackElements\GROUP_CONTROL_TOC;

// Elementor Classes
use Elementor\Plugin;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Color as Scheme_Color;
use Elementor\Core\Schemes\Typography as Scheme_Typography;
use Elementor\Core\Schemes;
use Elementor\Icons_Manager;
use Elementor\Modules\DynamicTags\Module as TagsModule;

/**
 * Exit if accessed directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base class for Table of Contents Widget which extends Powerpack_Widget class
 *
 * @since 1.4.15
 */

class Table_Of_Contents extends Powerpack_Widget {
	/**
	 *  Retrieve Table of Contents widget name.
	 *
	 *  @since 1.4.15
	 *  @access public
	 *
	 *  @return string Widget Name.
	 */

	public function get_name() {
		return parent::get_widget_name( 'Table_Of_Contents' );
	}

	/**
	 *  Retrieve Table of Contents widget title.
	 *
	 *  Title is displayed in the Elementor Editor, in PowerPack Settings and other places in frontend.
	 *
	 *  @since 1.4.15
	 *  @access public
	 *
	 *  @return string Widget Label.
	 */

	public function get_title() {
		return parent::get_widget_title( 'Table_Of_Contents' );
	}

	/**
	 * Retrieve Table of Contents widget icon.
	 *
	 * @since 1.4.15
	 * @access public
	 *
	 * @return string Icon Classes.
	 */

	public function get_icon() {
		return parent::get_widget_icon( 'Table_Of_Contents' );
	}

	/**
	 * Get the the keywords for the Table of Contents widget.
	 *
	 * @since 1.4.15
	 * @access public
	 *
	 * @return array Array of script identifiers.
	 */

	public function get_keywords() {
		return parent::get_widget_keywords( 'Table_Of_Contents' );
	}

	/**
	 * Retrieve the list of scripts the Table of Contents widget depended on.
	 *
	 * @since 1.4.15
	 * @access public
	 *
	 * @return array Array of script identifiers.
	 */

	public function get_script_depends() {
		return array(
			'pp-toc',
			'powerpack-frontend',
		);
	}

	/**
	 * Register Table of Contents widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.4.15
	 * @access protected
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'table_of_contents',
			[
				'label' => __( 'Table of Contents', 'powerpack' ),
			]
		);

		$this->add_control(
			'title',
			[
				'label' => __( 'Title', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'label_block' => true,
				'default' => __( 'Table of Contents', 'powerpack' ),
			]
		);

		$this->add_control(
			'html_tag',
			[
				'label' => __( 'HTML Tag', 'powerpack' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
					'div' => 'div',
				],
				'default' => 'h4',
			]
		);

		$this->start_controls_tabs( 'include_exclude_tags', [ 'separator' => 'before' ] );

		$this->start_controls_tab(
			'include',
			[
				'label' => __( 'Include', 'powerpack' ),
			]
		);

		$this->add_control(
			'headings_by_tags',
			[
				'label' => __( 'Anchors By Tags', 'powerpack' ),
				'type' => Controls_Manager::SELECT2,
				'multiple' => true,
				'default' => [ 'h2', 'h3', 'h4', 'h5', 'h6' ],
				'options' => [
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
				],
				'label_block' => true,
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'container',
			[
				'label' => __( 'Container', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'description' => __( 'This control confines the Table of Contents to heading elements under the provided CSS selector.', 'powerpack' ),
				'frontend_available' => true,
			]
		);

		$this->end_controls_tab(); // include

		$this->start_controls_tab(
			'exclude',
			[
				'label' => __( 'Exclude', 'powerpack' ),
			]
		);

		$this->add_control(
			'exclude_headings_by_selector',
			[
				'label' => __( 'Anchors By Selector', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
				'description' => __( 'CSS selectors, in a comma-separated list', 'powerpack' ),
				'default' => [],
				'label_block' => true,
				'frontend_available' => true,
			]
		);

		$this->end_controls_tab(); // exclude

		$this->end_controls_tabs(); // include_exclude_tags

		$this->add_control(
			'marker_view',
			[
				'label' => __( 'List Style', 'powerpack' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'numbers',
				'options' => [
					'none'    => __( 'None', 'powerpack' ),
					'numbers' => __( 'Numbers', 'powerpack' ),
					'bullets' => __( 'Bullets', 'powerpack' ),
				],
				'separator' => 'before',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'icon',
			[
				'label' => __( 'Icon', 'powerpack' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-circle',
					'library' => 'fa-solid',
				],
				'recommended' => [
					'fa-solid' => [
						'circle',
						'dot-circle',
						'square-full',
					],
					'fa-regular' => [
						'circle',
						'dot-circle',
						'square-full',
					],
				],
				'condition' => [
					'marker_view' => 'bullets',
				],
				'skin' => 'inline',
				'label_block' => false,
				'exclude_inline_options' => [ 'svg' ],
				'frontend_available' => true,
			]
		);

		$this->end_controls_section(); // table_of_contents

		$this->start_controls_section(
			'additional_options',
			[
				'label' => __( 'Additional Options', 'powerpack' ),
			]
		);

		$this->add_control(
			'word_wrap',
			[
				'label' => __( 'Word Wrap', 'powerpack' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'ellipsis',
				'prefix_class' => 'pp-toc--content-',
			]
		);

		$this->add_control(
			'minimize_box',
			[
				'label' => __( 'Collapsable TOC', 'powerpack' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'description' => __( 'Enable to make TOC collapsble on click.', 'powerpack' ),
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'expand_icon',
			[
				'label' => __( 'Icon', 'powerpack' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-chevron-down',
					'library' => 'fa-solid',
				],
				'recommended' => [
					'fa-solid' => [
						'chevron-down',
						'angle-down',
						'angle-double-down',
						'caret-down',
						'caret-square-down',
					],
					'fa-regular' => [
						'caret-square-down',
					],
				],
				'skin' => 'inline',
				'label_block' => false,
				'condition' => [
					'minimize_box' => 'yes',
				],
			]
		);

		$this->add_control(
			'collapse_icon',
			[
				'label' => __( 'Minimize Icon', 'powerpack' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-chevron-up',
					'library' => 'fa-solid',
				],
				'recommended' => [
					'fa-solid' => [
						'chevron-up',
						'angle-up',
						'angle-double-up',
						'caret-up',
						'caret-square-up',
					],
					'fa-regular' => [
						'caret-square-up',
					],
				],
				'skin' => 'inline',
				'label_block' => false,
				'condition' => [
					'minimize_box' => 'yes',
				],
			]
		);

		$breakpoints = pp_get_elementor()->breakpoints->get_active_breakpoints();

		$minimized_on_options = [];

		foreach ( $breakpoints as $breakpoint_key => $breakpoint ) {
			// This feature is meant for mobile screens.
			if ( 'widescreen' === $breakpoint_key ) {
				continue;
			}

			$minimized_on_options[ $breakpoint_key ] = sprintf(
				/* translators: 1: `<` character, 2: Breakpoint value. */
				esc_html__( '%1$s (%2$s %3$dpx)', 'powerpack' ),
				$breakpoint->get_label(),
				'<',
				$breakpoint->get_value()
			);
		}

		$minimized_on_options['desktop'] = esc_html__( 'Desktop (or smaller)', 'powerpack' );

		$this->add_control(
			'minimized_on',
			[
				'label' => __( 'Collapse On', 'powerpack' ),
				'type' => Controls_Manager::SELECT2,
				'description' => __( 'Collapse TOC on the selected devices on page load.', 'powerpack' ),
				'multiple' => true,
				'default' => 'tablet',
				'options' => $minimized_on_options,
				//'prefix_class' => 'pp-toc--minimized-on-',
				'condition' => [
					'minimize_box!' => '',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'hierarchical_view',
			[
				'label' => __( 'Hierarchical View', 'powerpack' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'collapse_subitems',
			[
				'label' => __( 'Collapse Sub Headings', 'powerpack' ),
				'type' => Controls_Manager::SWITCHER,
				'description' => __( 'The "Collapse" option should only be used if the Table of Contents is made sticky', 'powerpack' ),
				'condition' => [
					'hierarchical_view' => 'yes',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'sticky_toc_toggle',
			[
				'label' => __( 'Sticky TOC on Scroll', 'powerpack' ),
				'type' => Controls_Manager::SWITCHER,
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'scroll_to_top_toggle',
			[
				'label' => __( 'Scroll to Top', 'powerpack' ),
				'type' => Controls_Manager::SWITCHER,
				'description' => __( 'Add scroll to top button.', 'powerpack' ),
				'frontend_available' => true,
			]
		);

		$this->add_responsive_control(
			'scroll_offset',
			[
				'label' => __( 'Scroll Offset', 'powerpack' ),
				'type' => Controls_Manager::SLIDER,
				'frontend_available' => true,
				'responsive' => true,
			]
		);

		$this->end_controls_section(); // settings

		/**
		 *  Section - Sticky ToC
		 *  Tab - Content
		 *  Condition - Content > Sticky TOC is Enabled
		 */

		$this->start_controls_section(
			'sticky_toc',
			[
				'label' => __( 'Sticky TOC', 'powerpack' ),
				'description'   => __( 'Scroll the page a bit to see the Sticky Toc in order to adjust its position.', 'powerpack' ),
				'condition' => [
					'sticky_toc_toggle' => 'yes',
				],
			]
		);

		$this->add_control(
			'sticky_toc_disable_on',
			[
				'label' => __( 'Disabled On', 'powerpack' ),
				'type' => Controls_Manager::SELECT2,
				'default' => 'none',
				'multiple' => true,
				'options' => $minimized_on_options,
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'sticky_toc_type',
			[
				'label' => __( 'Sticky Type', 'powerpack' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'custom-position',
				'options' => [
					'in-place' => __( 'Sticky In Place', 'powerpack' ),
					'custom-position' => __( 'Custom Position', 'powerpack' ),
				],
				'prefix_class' => 'sticky-',
				'frontend_available' => true,
			]
		);

		$this->add_responsive_control(
			'sticky_toc_position_x',
			[
				'label' => __( 'Horizontal Position', 'powerpack' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 5,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => '%',
					'size' => 50,
				],
				'responsive'    => true,
				'selectors' => [
					'{{WRAPPER}}' => '--toc-position-x: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'sticky_toc_type' => 'custom-position',
				],
			]
		);

		$this->add_responsive_control(
			'sticky_toc_position_y',
			[
				'label' => __( 'Vertical Position', 'powerpack' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 5,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => '%',
					'size' => 50,
				],
				'responsive'    => true,
				'selectors' => [
					'{{WRAPPER}}' => '--toc-position-y: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'sticky_toc_type' => 'custom-position',
				],
			]
		);

		$this->add_responsive_control(
			'sticky_toc_entrance_animation',
			[
				'label' => __( 'Entrance Animation', 'powerpack' ),
				'type' => Controls_Manager::ANIMATION,
				'default' => 'fadeIn',
				'frontend_available' => true,
			]
		);

		$this->add_responsive_control(
			'sticky_toc_exit_animation',
			[
				'label' => __( 'Exit Animation', 'powerpack' ),
				'default' => 'fadeIn',
				'type' => Controls_Manager::EXIT_ANIMATION,
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'sticky_toc_animation_duration',
			[
				'label' => __( 'Animation Duration', 'powerpack' ),
				'type' => Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'slow' => __( 'Slow', 'powerpack' ),
					'' => __( 'Normal', 'powerpack' ),
					'fast' => __( 'Fast', 'powerpack' ),
				],
				'prefix_class' => 'animated-',
				'conditions'    => [
					'relation'  => 'or',
					'terms' => [
						[
							'name'  => 'sticky_toc_entrance_animation',
							'operator'  => '!==',
							'value' => '',
						],
						[
							'name'  => 'sticky_toc_exit_animation',
							'operator'  => '!==',
							'value' => '',
						],
					],
				],
			]
		);

		$this->add_responsive_control(
			'sticky_toc_top_offset',
			[
				'label' => __( 'Offset', 'powerpack' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 5,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => '%',
					'size' => 50,
				],
				'selectors' => [
					'{{WRAPPER}} .pp-toc.floating-toc' => 'top: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'sticky_toc_type' => 'in-place',
				],
			]
		);

		$this->add_responsive_control(
			'sticky_toc_z_index',
			[
				'label' => __( 'Z-Index', 'powerpack' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 5,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 999,
				],
				'selectors' => [
					'{{WRAPPER}} .pp-toc.floating-toc' => 'z-index: {{SIZE}}',
				],
			]
		);

		$this->end_controls_section();

		/**
		 *  Section - Scroll to Top
		 *  Tab - Content
		 *  Toggle - Additional Options > Scroll to Top
		 */

		$this->start_controls_section(
			'scroll_to_top_section',
			[
				'label' => __( 'Scroll to Top', 'powerpack' ),
				'condition' => [
					'scroll_to_top_toggle' => 'yes',
				],
			]
		);

		$this->add_control(
			'scroll_to_top_option',
			[
				'label'     => __( 'Scroll To', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'window_top',
				'options'   => [
					'window_top'    => __( 'Window Top', 'powerpack' ),
					'toc'           => __( 'Table of Contents', 'powerpack' ),
				],
				'frontend_available'    => true,
			]
		);

		$this->add_control(
			'scroll_to_top_icon',
			[
				'label' => __( 'Icon', 'powerpack' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-chevron-up',
					'library' => 'fa-solid',
				],
				'recommended' => [
					'fa-solid' => [
						'chevron-up',
						'angle-up',
						'arrow-alt-circle-up',
						'arrow-circle-up',
						'caret-up',
						'chevron-circle-up',
						'hand-point-up',
					],
					'fa-regular' => [
						'arrow-alt-circle-up',
						'caret-square-up',
						'square-full',
					],
				],
				'condition' => [
					'scroll_to_top_toggle' => 'yes',
				],
				'skin' => 'inline',
				'label_block' => false,
				'exclude_inline_options' => [ 'svg' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'scroll_to_top_align',
			[
				'label' => __( 'Alignment', 'powerpack' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'pp-toc__scroll-to-top--align-left' => [
						'title' => __( 'Left', 'powerpack' ),
						'icon' => 'eicon-h-align-left',
					],
					'pp-toc__scroll-to-top--align-right' => [
						'title' => __( 'Right', 'powerpack' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'default' => 'pp-toc__scroll-to-top--align-right',
				'toggle' => true,
			]
		);

		$this->add_responsive_control(
			'scroll_to_top_position_x',
			[
				'label' => __( 'Horizontal Position', 'powerpack' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 5,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 50,
				],
				'responsive'    => true,
				'selectors' => [
					'{{WRAPPER}} .pp-toc__scroll-to-top--container' => '--toc-scroll-top-position-x: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_responsive_control(
			'scroll_to_top_position_y',
			[
				'label' => __( 'Vertical Position', 'powerpack' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 5,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 50,
				],
				'responsive'    => true,
				'selectors' => [
					'{{WRAPPER}}' => '--toc-scroll-top-position-y: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_responsive_control(
			'scroll_to_top_z_index',
			[
				'label' => __( 'Z-Index', 'powerpack' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 5,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 999,
				],
				'selectors' => [
					'{{WRAPPER}} .pp-toc__scroll-to-top--container' => 'z-index: {{SIZE}}',
				],
			]
		);

		$this->end_controls_section(); // Scroll Top

		$help_docs = PP_Config::get_widget_help_links( 'Table_Of_Contents' );
		if ( ! empty( $help_docs ) ) {
			/**
			 * Content Tab: Docs Links
			 *
			 * @since 1.4.15
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

		/**
		 * Section - Box Style
		 * Tab - Style
		 */

		$this->start_controls_section(
			'box_style',
			[
				'label' => __( 'Box', 'powerpack' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'background_color',
			[
				'label' => __( 'Background Color', 'powerpack' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}}' => '--box-background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'  => 'border',
				'label' => __( 'Border', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-toc',
			]
		);

		$this->add_control(
			'border_radius',
			[
				'label' => __( 'Border Radius', 'powerpack' ),
				'type' => Controls_Manager::DIMENSIONS,
				'selectors' => [
					'{{WRAPPER}}' => '--box-border-radius: {{TOP}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} {{RIGHT}}{{UNIT}}',
				],
			]
		);

		$this->add_responsive_control(
			'min_height',
			[
				'label' => __( 'Min Height', 'powerpack' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--box-min-height: {{SIZE}}{{UNIT}}',
				],
				'frontend_available' => true,
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'box_shadow',
				'selector' => '{{WRAPPER}} .pp-toc',
			]
		);

		$this->end_controls_section(); // box_style

		$this->start_controls_section(
			'header_style',
			[
				'label' => __( 'Header', 'powerpack' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'header_align',
			[
				'label' => __( 'Alignment', 'powerpack' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __( 'Left', 'powerpack' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'powerpack' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'powerpack' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--toc-header-title-align: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'header_padding',
			[
				'label' => __( 'Padding', 'powerpack' ),
				'type' => Controls_Manager::DIMENSIONS,
				'selectors' => [
					'{{WRAPPER}}' => '--toc-header-box-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'header_background_color',
			[
				'label' => __( 'Background Color', 'powerpack' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--header-background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'header_text_color',
			[
				'label' => __( 'Text Color', 'powerpack' ),
				'type' => Controls_Manager::COLOR,
				'scheme' => [
					'type' => Schemes\Color::get_type(),
					'value' => Schemes\Color::COLOR_2,
				],
				'selectors' => [
					'{{WRAPPER}}' => '--header-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'header_typography',
				'selector' => '{{WRAPPER}} .pp-toc__header, {{WRAPPER}} .pp-toc__header-title',
				'scheme' => Schemes\Typography::TYPOGRAPHY_1,
			]
		);

		$this->add_control(
			'toggle_button_color',
			[
				'label' => __( 'Icon Color', 'powerpack' ),
				'type' => Controls_Manager::COLOR,
				'condition' => [
					'minimize_box' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--toggle-button-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'header_separator_width',
			[
				'label' => __( 'Separator Width', 'powerpack' ),
				'type' => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}}' => '--separator-width: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'header_separator_color',
			[
				'label' => __( 'Separator Color', 'powerpack' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--separator-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section(); // header_style

		$this->start_controls_section(
			'list_style',
			[
				'label' => __( 'List', 'powerpack' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'list_padding',
			[
				'label' => __( 'Padding', 'powerpack' ),
				'type' => Controls_Manager::DIMENSIONS,
				'selectors' => [
					'{{WRAPPER}}' => '--toc-list-box-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'list_typography',
				'selector' => '{{WRAPPER}} .pp-toc__list-item',
				'scheme' => Schemes\Typography::TYPOGRAPHY_3,
			]
		);

		$this->add_group_control(
			Group_Control_Toc::get_type(),
			[
				'name' => 'heading_level_font_size',
				'selector' => '{{WRAPPER}}',
			]
		);

		$this->add_control(
			'list_indent',
			[
				'label' => __( 'Indent', 'powerpack' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'default' => [
					'unit' => 'em',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--nested-list-indent: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->start_controls_tabs( 'item_text_style' );

		$this->start_controls_tab(
			'normal',
			[
				'label' => __( 'Normal', 'powerpack' ),
			]
		);

		$this->add_control(
			'item_text_color_normal',
			[
				'label' => __( 'Text Color', 'powerpack' ),
				'type' => Controls_Manager::COLOR,
				'scheme' => [
					'type' => Schemes\Color::get_type(),
					'value' => Schemes\Color::COLOR_3,
				],
				'selectors' => [
					'{{WRAPPER}}' => '--item-text-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'item_text_underline_normal',
			[
				'label' => __( 'Underline', 'powerpack' ),
				'type' => Controls_Manager::SWITCHER,
				'selectors' => [
					'{{WRAPPER}}' => '--item-text-decoration: underline',
				],
			]
		);

		$this->end_controls_tab(); // normal

		$this->start_controls_tab(
			'hover',
			[
				'label' => __( 'Hover', 'powerpack' ),
			]
		);

		$this->add_control(
			'item_text_color_hover',
			[
				'label' => __( 'Text Color', 'powerpack' ),
				'type' => Controls_Manager::COLOR,
				'scheme' => [
					'type' => Schemes\Color::get_type(),
					'value' => Schemes\Color::COLOR_4,
				],
				'selectors' => [
					'{{WRAPPER}}' => '--item-text-hover-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'item_text_underline_hover',
			[
				'label' => __( 'Underline', 'powerpack' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'selectors' => [
					'{{WRAPPER}}' => '--item-text-hover-decoration: underline',
				],
			]
		);

		$this->end_controls_tab(); // hover

		$this->start_controls_tab(
			'active',
			[
				'label' => __( 'Active', 'powerpack' ),
			]
		);

		$this->add_control(
			'item_text_color_active',
			[
				'label' => __( 'Text Color', 'powerpack' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--item-text-active-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'item_text_underline_active',
			[
				'label' => __( 'Underline', 'powerpack' ),
				'type' => Controls_Manager::SWITCHER,
				'selectors' => [
					'{{WRAPPER}}' => '--item-text-active-decoration: underline',
				],
			]
		);

		$this->end_controls_tab(); // active

		$this->end_controls_tabs(); // item_text_style

		$this->add_control(
			'heading_marker',
			[
				'label' => __( 'Marker', 'powerpack' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'marker_color',
			[
				'label' => __( 'Color', 'powerpack' ),
				'type' => Controls_Manager::COLOR,
				'scheme' => [
					'type' => Schemes\Color::get_type(),
					'value' => Schemes\Color::COLOR_3,
				],
				'selectors' => [
					'{{WRAPPER}}' => '--marker-color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'marker_size',
			[
				'label' => __( 'Size', 'powerpack' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'selectors' => [
					'{{WRAPPER}}' => '--marker-size: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->end_controls_section(); // list_style

		/**
		 * Section - Sticky ToC
		 * Tab - Style
		 * Condition - Content > Sticky ToC Toggle - Enabled
		 */

		$this->start_controls_section(
			'sticky_toc_style_section',
			[
				'label' => __( 'Sticky TOC', 'powerpack' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'sticky_toc_toggle' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'sticky_toc_box_width',
			[
				'label' => __( 'Width', 'powerpack' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 600,
				],
				'selectors' => [
					'{{WRAPPER}} .pp-toc.floating-toc' => 'width: {{SIZE}}{{UNIT}};',
				],
				'responsive' => true,
				'condition' => [
					'sticky_toc_type' => 'custom-position',
				],
			]
		);

		$this->add_control(
			'sticky_toc_box_background_color_opacity',
			[
				'label' => __( 'Background Opacity', 'powerpack' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px'    => [
						'min'   => 0,
						'max'   => 1,
						'step'  => 0.10,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 0.5,
				],
				'selectors' => [
					'{{WRAPPER}} .pp-toc.floating-toc' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'sticky_toc_box_shadow',
				'label' => __( 'Box Shadow', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-toc.floating-toc',
			]
		);

		$this->end_controls_section(); //Sticky ToC

		/**
		 * Section - Scroll to Top
		 * Tab - Style
		 * Condition - Content > Scroll to Top Toggle - Enabled
		 */

		$this->start_controls_section(
			'scroll_to_top_style_section',
			[
				'label' => __( 'Scroll to Top', 'powerpack' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'scroll_to_top_toggle' => 'yes',
				],
			]
		);

		$this->add_control(
			'scroll_to_top_icon_size',
			[
				'label' => __( 'Icon Size', 'powerpack' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 5,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 20,
				],
				'selectors' => [
					'{{WRAPPER}} .pp-toc__scroll-to-top--icon i' => 'font-size: {{SIZE}}{{UNIT}};',
				],
				'responsive' => true,
			]
		);

		$this->add_control(
			'scroll_to_top_box_padding',
			[
				'label' => __( 'Padding', 'powerpack' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 5,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 20,
				],
				'selectors' => [
					'{{WRAPPER}} .pp-toc__scroll-to-top--container' => 'padding: {{SIZE}}{{UNIT}};',
				],
				'responsive' => true,
			]
		);

		$this->add_control(
			'scroll_to_top_icon_color',
			[
				'label' => __( 'Color', 'powerpack' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pp-toc__scroll-to-top--icon i' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'scroll_to_top_box_background_color',
			[
				'label' => __( 'Background Color', 'powerpack' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pp-toc__scroll-to-top--container' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'                  => 'scroll_to_top_box_border',
				'label'                 => __( 'Border', 'powerpack' ),
				'selector'              => '{{WRAPPER}} .pp-toc__scroll-to-top--container',
			]
		);

		$this->add_control(
			'scroll_to_top_box_border_radius',
			[
				'label' => __( 'Border Radius', 'powerpack' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 5,
					],
					'%' => [
						'min'   => 0,
						'max'   => 100,
						'step'  => 5,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 20,
				],
				'selectors' => [
					'{{WRAPPER}} .pp-toc__scroll-to-top--container' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
				'responsive' => true,
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'scroll_to_top_box_shadow',
				'label' => __( 'Box Shadow', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-toc__scroll-to-top--container',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render Table of Contents widget template
	 *
	 * @since 1.4.15
	 * @access protected
	 */

	protected function render() {
		$types = Plugin::$instance->elements_manager->get_categories();
		$widgets = Plugin::$instance->widgets_manager->get_widget_types();

		$settings = $this->get_settings_for_display();

		if ( $settings['minimized_on'] ) {

			$minimized_on = $settings['minimized_on'];

			if ( ! is_array( $minimized_on ) ) {
				$minimized_on = [ $settings['minimized_on'] ];
			}

			foreach ( $minimized_on as $m ) {

				$this->add_render_attribute( '_wrapper', 'class', 'pp-toc--minimized-on-' . $m );
			}
		}

		$this->add_render_attribute(
			'header',
			[
				'class' => 'pp-toc__header',
				'aria-controls' => 'pp-toc__body',
			]
		);

		$this->add_render_attribute(
			'body',
			[
				'class' => 'pp-toc__body',
				'aria-expanded' => 'true',
			]
		);

		if ( $settings['collapse_subitems'] ) {
			$this->add_render_attribute( 'body', 'class', 'pp-toc__list-items--collapsible' );
		}

		if ( 'yes' === $settings['minimize_box'] ) {
			$this->add_render_attribute(
				'expand-button',
				[
					'class' => 'pp-toc__toggle-button pp-toc__toggle-button--expand',
					'role' => 'button',
					'tabindex' => '0',
					'aria-label' => esc_html__( 'Open table of contents', 'powerpack' ),
				]
			);
			$this->add_render_attribute(
				'collapse-button',
				[
					'class' => 'pp-toc__toggle-button pp-toc__toggle-button--collapse',
					'role' => 'button',
					'tabindex' => '0',
					'aria-label' => esc_html__( 'Close table of contents', 'powerpack' ),
				]
			);
		}

		$html_tag = PP_Helper::validate_html_tag( $settings['html_tag'] );
		?>
		<div id="<?php echo 'pp-toc-' . esc_attr( $this->get_id() ); ?>" class="pp-toc">
			<div <?php $this->print_render_attribute_string( 'header' ); ?>>
				<div class="pp-toc__header-title-wrapper">
					<<?php PP_Helper::print_validated_html_tag( $html_tag ); ?> class="pp-toc__header-title">
						<?php $this->print_unescaped_setting( 'title' ); ?>
					</<?php PP_Helper::print_validated_html_tag( $html_tag ); ?>>
				</div>

				<?php if ( 'yes' === $settings['minimize_box'] ) : ?>
					<div <?php $this->print_render_attribute_string( 'expand-button' ); ?>><?php Icons_Manager::render_icon( $settings['expand_icon'], [ 'aria-hidden' => 'true' ] ); ?></div>
					<div <?php $this->print_render_attribute_string( 'collapse-button' ); ?>><?php Icons_Manager::render_icon( $settings['collapse_icon'], [ 'aria-hidden' => 'true' ] ); ?></div>
				<?php endif; ?>
			</div>
			<div <?php $this->print_render_attribute_string( 'body' ); ?>>
				<div class="pp-toc__spinner-container">
					<i class="pp-toc__spinner eicon-loading eicon-animation-spin" aria-hidden="true"></i>
				</div>
			</div>
		</div>

		<?php if ( $settings['scroll_to_top_toggle'] ) { ?>

			<div class="pp-toc__scroll-to-top--container <?php echo esc_attr( $settings['scroll_to_top_align'] ); ?>">
				<div class="pp-toc__scroll-to-top--icon"><?php Icons_Manager::render_icon( $settings['scroll_to_top_icon'] ); ?></div>
			</div>

			<?php
		}
	}
}
