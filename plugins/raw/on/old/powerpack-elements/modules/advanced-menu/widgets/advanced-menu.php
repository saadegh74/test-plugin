<?php
namespace PowerpackElements\Modules\AdvancedMenu\Widgets;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Classes\PP_Config;

use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography as Scheme_Typography;
use Elementor\Core\Schemes\Color as Scheme_Color;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Advanced Menu Widget
 */
class Advanced_Menu extends Powerpack_Widget {

	/**
	 * Menu Index
	 *
	 * @var $nav_menu_index
	 */
	protected $nav_menu_index = 1;

	/**
	 * Retrieve Advanced Menu widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Advanced_Menu' );
	}

	/**
	 * Retrieve Advanced Menu widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Advanced_Menu' );
	}

	/**
	 * Retrieve Advanced Menu widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Advanced_Menu' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the Advanced Menu widget belongs to.
	 *
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'Advanced_Menu' );
	}

	/**
	 * Retrieve the list of scripts the Advanced Menu widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return array(
			'jquery-smartmenu',
			'pp-advanced-menu',
		);
	}

	public function on_export( $element ) {
		unset( $element['settings']['menu'] );

		return $element;
	}

	/**
	 * Get menu index
	 *
	 * @return $nav_menu_index
	 */
	protected function get_nav_menu_index() {
		return $this->nav_menu_index++;
	}

	/**
	 * Get available menus
	 *
	 * @return array $options
	 */
	private function get_available_menus() {
		$menus = wp_get_nav_menus();

		$options = array();

		foreach ( $menus as $menu ) {
			$options[ $menu->slug ] = $menu->name;
		}

		return $options;
	}

	/**
	 * Register Advanced Menu widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 2.0.3
	 * @access protected
	 */
	protected function register_controls() {
		/* Content Tab */
		$this->register_content_layout_controls();
		$this->register_content_help_docs_controls();

		/* Style Tab */
		$this->register_style_menu_controls();
	}

	/**
	 * Register widget layout controls
	 *
	 * @return void
	 */
	protected function register_content_layout_controls() {

		$this->start_controls_section(
			'section_layout',
			array(
				'label' => __( 'Layout', 'powerpack' ),
			)
		);

		$menus = $this->get_available_menus();

		if ( ! empty( $menus ) ) {
			$this->add_control(
				'menu',
				array(
					'label'       => __( 'Menu', 'powerpack' ),
					'type'        => Controls_Manager::SELECT,
					'options'     => $menus,
					'default'     => array_keys( $menus )[0],
					'separator'   => 'after',
					/* translators: %s: menu page link */
					'description' => sprintf( __( 'Go to the <a href="%s" target="_blank">Menus screen</a> to manage your menus.', 'powerpack' ), admin_url( 'nav-menus.php' ) ),
				)
			);
		} else {
			$this->add_control(
				'menu',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %s: create menu page link */
					'raw'             => sprintf( __( '<strong>There are no menus in your site.</strong><br>Go to the <a href="%s" target="_blank">Menus screen</a> to create one.', 'powerpack' ), admin_url( 'nav-menus.php?action=edit&menu=0' ) ),
					'separator'       => 'after',
					'content_classes' => 'pp-editor-info',
				)
			);
		}

		$this->add_control(
			'layout',
			array(
				'label'              => __( 'Layout', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'horizontal',
				'options'            => array(
					'horizontal' => __( 'Horizontal', 'powerpack' ),
					'vertical'   => __( 'Vertical', 'powerpack' ),
				),
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'align_items',
			array(
				'label'       => __( 'Align', 'powerpack' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => array(
					'left'    => array(
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center'  => array(
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'eicon-h-align-center',
					),
					'right'   => array(
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-h-align-right',
					),
					'justify' => array(
						'title' => __( 'Stretch', 'powerpack' ),
						'icon'  => 'eicon-h-align-stretch',
					),
				),
				'condition'   => array(
					'layout!' => 'dropdown',
				),
			)
		);

		$this->add_control(
			'pointer',
			array(
				'label'     => __( 'Pointer', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'underline',
				'options'   => array(
					'none'               => __( 'None', 'powerpack' ),
					'underline'          => __( 'Underline', 'powerpack' ),
					'overline'           => __( 'Overline', 'powerpack' ),
					'double-line'        => __( 'Double Line', 'powerpack' ),
					'framed'             => __( 'Framed', 'powerpack' ),
					'background'         => __( 'Background', 'powerpack' ),
					'brackets'           => __( 'Brackets', 'powerpack' ),
					'right-angle-slides' => __( 'Right Angle Slides Down Over Title', 'powerpack' ),
					'text'               => __( 'Text', 'powerpack' ),
				),
				'condition' => array(
					'layout!' => 'dropdown',
				),
			)
		);

		$this->add_control(
			'animation_line',
			array(
				'label'     => __( 'Animation', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'fade',
				'options'   => array(
					'fade'     => 'Fade',
					'slide'    => 'Slide',
					'grow'     => 'Grow',
					'drop-in'  => 'Drop In',
					'drop-out' => 'Drop Out',
					'none'     => 'None',
				),
				'condition' => array(
					'layout!' => 'dropdown',
					'pointer' => array( 'underline', 'overline', 'double-line' ),
				),
			)
		);

		$this->add_control(
			'animation_framed',
			array(
				'label'     => __( 'Animation', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'fade',
				'options'   => array(
					'fade'    => 'Fade',
					'grow'    => 'Grow',
					'shrink'  => 'Shrink',
					'draw'    => 'Draw',
					'corners' => 'Corners',
					'none'    => 'None',
				),
				'condition' => array(
					'layout!' => 'dropdown',
					'pointer' => 'framed',
				),
			)
		);

		$this->add_control(
			'animation_background',
			array(
				'label'     => __( 'Animation', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'fade',
				'options'   => array(
					'fade'                   => 'Fade',
					'grow'                   => 'Grow',
					'shrink'                 => 'Shrink',
					'sweep-left'             => 'Sweep Left',
					'sweep-right'            => 'Sweep Right',
					'sweep-up'               => 'Sweep Up',
					'sweep-down'             => 'Sweep Down',
					'shutter-in-vertical'    => 'Shutter In Vertical',
					'shutter-out-vertical'   => 'Shutter Out Vertical',
					'shutter-in-horizontal'  => 'Shutter In Horizontal',
					'shutter-out-horizontal' => 'Shutter Out Horizontal',
					'none'                   => 'None',
				),
				'condition' => array(
					'layout!' => 'dropdown',
					'pointer' => 'background',
				),
			)
		);

		$this->add_control(
			'animation_text',
			array(
				'label'     => __( 'Animation', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'grow',
				'options'   => array(
					'grow'   => 'Grow',
					'shrink' => 'Shrink',
					'sink'   => 'Sink',
					'float'  => 'Float',
					'skew'   => 'Skew',
					'rotate' => 'Rotate',
					'none'   => 'None',
				),
				'condition' => array(
					'layout!' => 'dropdown',
					'pointer' => 'text',
				),
			)
		);

		$this->add_control(
			'expanded_submenu',
			array(
				'label'        => __( 'Expanded Submenu', 'powerpack' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'no',
				'options'      => array(
					'yes' => __( 'Yes', 'powerpack' ),
					'no'  => __( 'No', 'powerpack' ),
				),
				'frontend_available' => true,
				'condition'    => array(
					'layout' => 'vertical',
				),
			)
		);

		$this->add_control(
			'show_submenu_on',
			array(
				'label'        => __( 'Show Submenu On', 'powerpack' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'hover',
				'options'      => array(
					'hover'  => __( 'Hover', 'powerpack' ),
					'click'  => __( 'Click', 'powerpack' ),
				),
				'frontend_available' => true,
				'conditions'         => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'layout',
							'operator' => '===',
							'value'    => 'horizontal',
						),
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'layout',
									'operator' => '===',
									'value'    => 'vertical',
								),
								array(
									'name'     => 'expanded_submenu',
									'operator' => '!==',
									'value'    => 'yes',
								),
							),
						),
					),
				),
			)
		);

		$this->add_control(
			'submenu_icon',
			[
				'label' => esc_html__( 'Submenu Indicator', 'powerpack' ),
				'type' => Controls_Manager::ICONS,
				'separator' => 'before',
				'default' => [
					'value' => 'fas fa-caret-down',
					'library' => 'fa-solid',
				],
				'recommended' => [
					'fa-solid' => [
						'chevron-down',
						'angle-down',
						'caret-down',
						'plus',
					],
				],
				'label_block' => false,
				'skin' => 'inline',
				'exclude_inline_options' => [ 'svg' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'heading_mobile_dropdown',
			array(
				'label'     => __( 'Responsive', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'layout!' => 'dropdown',
				),
			)
		);

		$this->add_control(
			'dropdown',
			array(
				'label'   => __( 'Breakpoint', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'tablet',
				'options' => array(
					'all'    => __( 'Always', 'powerpack' ),
					'mobile' => __( 'Mobile (767px >)', 'powerpack' ),
					'tablet' => __( 'Tablet (1023px >)', 'powerpack' ),
					'none'   => __( 'None', 'powerpack' ),
				),
			)
		);

		$this->add_control(
			'menu_type',
			array(
				'label'     => __( 'Menu Type', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'default',
				'options'   => array(
					'default'     => __( 'Default', 'powerpack' ),
					'off-canvas'  => __( 'Off Canvas', 'powerpack' ),
					'full-screen' => __( 'Full Screen', 'powerpack' ),
				),
				'frontend_available' => true,
				'condition' => array(
					'toggle!'   => '',
					'dropdown!' => 'none',
				),
			)
		);

		$this->add_control(
			'onepage_menu',
			array(
				'label'       => __( 'One Page Menu', 'powerpack' ),
				'description' => __( 'Set this option to \'Yes\' to close menu when user clicks on same page links.', 'powerpack' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'no',
				'options'     => array(
					'yes' => __( 'Yes', 'powerpack' ),
					'no'  => __( 'No', 'powerpack' ),
				),
				'frontend_available' => true,
				'condition'   => array(
					'dropdown!'  => 'none',
					'menu_type!' => 'default',
				),
			)
		);

		$this->add_control(
			'full_width',
			array(
				'label'              => __( 'Full Width', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'description'        => __( 'Stretch the dropdown of the menu to full width.', 'powerpack' ),
				'prefix_class'       => 'pp-advanced-menu--',
				'return_value'       => 'stretch',
				'render_type'        => 'template',
				'frontend_available' => true,
				'condition'          => array(
					'dropdown!' => 'none',
					'menu_type' => 'default',
				),
			)
		);

		$this->add_control(
			'toggle',
			array(
				'label'              => __( 'Toggle Button', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'icon',
				'options'            => array(
					'icon'       => __( 'Icon', 'powerpack' ),
					'icon-label' => __( 'Icon + Label', 'powerpack' ),
					'button'     => __( 'Label', 'powerpack' ),
				),
				'render_type'        => 'template',
				'frontend_available' => true,
				'condition'          => array(
					'dropdown!' => 'none',
				),
			)
		);

		$this->add_control(
			'toggle_icon_type',
			array(
				'label'              => __( 'Toggle Icon Type', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'hamburger',
				'options'            => array(
					'hamburger' => __( 'Hamburger', 'powerpack' ),
					'custom'    => __( 'Custom Icon', 'powerpack' ),
				),
				'frontend_available' => true,
				'condition'          => array(
					'dropdown!' => 'none',
					'toggle'    => array( 'icon', 'icon-label' ),
				),
			)
		);

		$this->add_control(
			'toggle_icon',
			array(
				'label'       => __( 'Toggle Icon', 'powerpack' ),
				'type'        => Controls_Manager::ICONS,
				'label_block' => false,
				'skin'        => 'inline',
				'default'     => array(
					'value'   => 'fas fa-bars',
					'library' => 'fa-solid',
				),
				'recommended' => array(
					'fa-solid' => array(
						'bars',
						'stream',
					),
				),
				'condition'   => array(
					'dropdown!'        => 'none',
					'toggle'           => array( 'icon', 'icon-label' ),
					'toggle_icon_type' => 'custom',
				),
			)
		);

		$this->add_control(
			'toggle_label',
			array(
				'label'     => __( 'Toggle Label', 'powerpack' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Menu', 'powerpack' ),
				'condition' => array(
					'toggle'    => array( 'icon-label', 'button' ),
					'dropdown!' => 'none',
				),
			)
		);

		$this->add_control(
			'label_align',
			array(
				'label'       => __( 'Label Align', 'powerpack' ),
				'type'        => Controls_Manager::CHOOSE,
				'default'     => 'right',
				'options'     => array(
					'left'  => array(
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-h-align-left',
					),
					'right' => array(
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'condition'   => array(
					'toggle'    => array( 'icon-label' ),
					'dropdown!' => 'none',
				),
				'label_block' => false,
				'toggle'      => false,
			)
		);

		$this->add_control(
			'toggle_align',
			array(
				'label'                => __( 'Toggle Align', 'powerpack' ),
				'type'                 => Controls_Manager::CHOOSE,
				'default'              => 'center',
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
					'left'   => 'margin-right: auto',
					'center' => 'margin: 0 auto',
					'right'  => 'margin-left: auto',
				),
				'selectors'            => array(
					'{{WRAPPER}} .pp-menu-toggle' => '{{VALUE}}',
				),
				'condition'            => array(
					'toggle!'   => '',
					'dropdown!' => 'none',
				),
				'label_block'          => false,
			)
		);

		$this->add_control(
			'text_align',
			array(
				'label'     => __( 'Align', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'aside',
				'options'   => array(
					'aside'  => __( 'Aside', 'powerpack' ),
					'center' => __( 'Center', 'powerpack' ),
				),
				'condition' => array(
					'dropdown!'  => 'none',
					'menu_type!' => array( 'off-canvas', 'full-screen' ),
				),
			)
		);

		$this->end_controls_section();

	}

	/**
	 * Register help docs controls
	 *
	 * @access protected
	 */
	protected function register_content_help_docs_controls() {

		$help_docs = PP_Config::get_widget_help_links( 'Advanced_Menu' );

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

	/*-----------------------------------------------------------------------------------*/
	/*	STYLE TAB
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Register menu style controls
	 *
	 * @access protected
	 */
	protected function register_style_menu_controls() {
		$this->start_controls_section(
			'section_style_main_menu',
			array(
				'label'     => __( 'Main Menu', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'dropdown!' => 'all',
				),

			)
		);

		$this->add_control(
			'heading_menu_item',
			array(
				'type'  => Controls_Manager::HEADING,
				'label' => __( 'Menu Item', 'powerpack' ),
			)
		);

		$this->start_controls_tabs( 'tabs_menu_item_style' );

		$this->start_controls_tab(
			'tab_menu_item_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'color_menu_item',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => array(
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_3,
				),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--main .pp-menu-item' => 'color: {{VALUE}}; fill: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_menu_item_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'color_menu_item_hover',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => array(
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_4,
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--main .pp-menu-item:hover,
					{{WRAPPER}} .pp-advanced-menu--main .pp-menu-item.pp-menu-item-active,
					{{WRAPPER}} .pp-advanced-menu--main .pp-menu-item.highlighted,
					{{WRAPPER}} .pp-advanced-menu--main .pp-menu-item:focus' => 'color: {{VALUE}}; fill: {{VALUE}}',
				),
				'condition' => array(
					'pointer!' => 'background',
				),
			)
		);

		$this->add_control(
			'color_menu_item_hover_pointer_bg',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#fff',
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--main .pp-menu-item:hover,
					{{WRAPPER}} .pp-advanced-menu--main .pp-menu-item.pp-menu-item-active,
					{{WRAPPER}} .pp-advanced-menu--main .pp-menu-item.highlighted,
					{{WRAPPER}} .pp-advanced-menu--main .pp-menu-item:focus' => 'color: {{VALUE}}; fill: {{VALUE}}',
				),
				'condition' => array(
					'pointer' => 'background',
				),
			)
		);

		$this->add_control(
			'pointer_color_menu_item_hover',
			array(
				'label'     => __( 'Pointer Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => array(
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_4,
				),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--main:not(.pp--pointer-framed) .pp-menu-item:before,
					{{WRAPPER}} .pp-advanced-menu--main:not(.pp--pointer-framed) .pp-menu-item:after' => 'background-color: {{VALUE}}',
					'{{WRAPPER}} .pp--pointer-framed .pp-menu-item:before,
					{{WRAPPER}} .pp--pointer-framed .pp-menu-item:after' => 'border-color: {{VALUE}}',
					'{{WRAPPER}} .pp--pointer-brackets .pp-menu-item:before,
					{{WRAPPER}} .pp--pointer-brackets .pp-menu-item:after' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'pointer!' => array( 'none', 'text' ),
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_menu_item_active',
			array(
				'label' => __( 'Active', 'powerpack' ),
			)
		);

		$this->add_control(
			'color_menu_item_active',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--main .pp-menu-item.pp-menu-item-active, {{WRAPPER}} .pp-advanced-menu--main .menu-item.current-menu-ancestor .pp-menu-item' => 'color: {{VALUE}}; fill: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'pointer_color_menu_item_active',
			array(
				'label'     => __( 'Pointer Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--main:not(.pp--pointer-framed) .pp-menu-item.pp-menu-item-active:before,
					{{WRAPPER}} .pp-advanced-menu--main:not(.pp--pointer-framed) .pp-menu-item.pp-menu-item-active:after,
					{{WRAPPER}} .pp-advanced-menu--main:not(.pp--pointer-framed) .menu-item.current-menu-ancestor .pp-menu-item:before,
					{{WRAPPER}} .pp-advanced-menu--main:not(.pp--pointer-framed) .menu-item.current-menu-ancestor .pp-menu-item:after' => 'background-color: {{VALUE}}',
					'{{WRAPPER}} .pp--pointer-framed .pp-menu-item.pp-menu-item-active:before,
					{{WRAPPER}} .pp--pointer-framed .pp-menu-item.pp-menu-item-active:after,
					{{WRAPPER}} .pp--pointer-framed .menu-item.current-menu-ancestor .pp-menu-item:before,
					{{WRAPPER}} .pp--pointer-framed .menu-item.current-menu-ancestor .pp-menu-item:after' => 'border-color: {{VALUE}}',
					'{{WRAPPER}} .pp--pointer-brackets .pp-menu-item.pp-menu-item-active:before,
					{{WRAPPER}} .pp--pointer-brackets .pp-menu-item.pp-menu-item-active:after,
					{{WRAPPER}} .pp--pointer-brackets .menu-item.current-menu-ancestor .pp-menu-item:before,
					{{WRAPPER}} .pp--pointer-brackets .menu-item.current-menu-ancestor .pp-menu-item:after' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'pointer!' => array( 'none', 'text' ),
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$divider_condition = [
			'nav_menu_divider' => 'yes',
			'layout' => 'horizontal',
		];

		$this->add_control(
			'nav_menu_divider',
			[
				'label'     => esc_html__( 'Divider', 'powerpack' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_off' => esc_html__( 'Off', 'powerpack' ),
				'label_on'  => esc_html__( 'On', 'powerpack' ),
				'condition' => [
					'layout' => 'horizontal',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--pp-nav-menu-divider-content: "";',
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'nav_menu_divider_style',
			[
				'label'     => esc_html__( 'Style', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'solid' => esc_html__( 'Solid', 'powerpack' ),
					'double' => esc_html__( 'Double', 'powerpack' ),
					'dotted' => esc_html__( 'Dotted', 'powerpack' ),
					'dashed' => esc_html__( 'Dashed', 'powerpack' ),
				],
				'default'   => 'solid',
				'condition' => $divider_condition,
				'selectors' => [
					'{{WRAPPER}}' => '--pp-nav-menu-divider-style: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'nav_menu_divider_weight',
			[
				'label'     => esc_html__( 'Width', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 20,
					],
				],
				'condition' => $divider_condition,
				'selectors' => [
					'{{WRAPPER}}' => '--pp-nav-menu-divider-width: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'nav_menu_divider_height',
			[
				'label'      => esc_html__( 'Height', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px' ],
				'range'      => [
					'px' => [
						'min' => 1,
						'max' => 100,
					],
					'%' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'condition'  => $divider_condition,
				'selectors'  => [
					'{{WRAPPER}}' => '--pp-nav-menu-divider-height: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'nav_menu_divider_color',
			[
				'label'     => esc_html__( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => array(
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_3,
				),
				'condition' => $divider_condition,
				'selectors' => [
					'{{WRAPPER}}' => '--pp-nav-menu-divider-color: {{VALUE}}',
				],
			]
		);

		/* This control is required to handle with complicated conditions */
		$this->add_control(
			'divider_hr',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_responsive_control(
			'padding_horizontal_menu_item',
			array(
				'label'     => __( 'Horizontal Padding', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 50,
					),
				),
				'devices'   => array( 'desktop', 'tablet', 'mobile' ),
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--main .pp-menu-item' => 'padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'padding_vertical_menu_item',
			array(
				'label'     => __( 'Vertical Padding', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 50,
					),
				),
				'devices'   => array( 'desktop', 'tablet', 'mobile' ),
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--main .pp-menu-item' => 'padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'menu_space_between',
			array(
				'label'     => __( 'Space Between', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 100,
					),
				),
				'devices'   => array( 'desktop', 'tablet', 'mobile' ),
				'selectors' => array(
					'body:not(.rtl) {{WRAPPER}} .pp-advanced-menu--layout-horizontal .pp-advanced-menu > li:not(:last-child)' => 'margin-right: {{SIZE}}{{UNIT}}',
					'body.rtl {{WRAPPER}} .pp-advanced-menu--layout-horizontal .pp-advanced-menu > li:not(:last-child)' => 'margin-left: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .pp-advanced-menu--main:not(.pp-advanced-menu--layout-horizontal) .pp-advanced-menu > li:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_control(
			'pointer_width',
			array(
				'label'     => __( 'Pointer Width', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'devices'   => array( self::RESPONSIVE_DESKTOP, self::RESPONSIVE_TABLET ),
				'range'     => array(
					'px' => array(
						'max' => 30,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp--pointer-framed .pp-menu-item:before' => 'border-width: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .pp--pointer-framed.e--animation-draw .pp-menu-item:before' => 'border-width: 0 0 {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .pp--pointer-framed.e--animation-draw .pp-menu-item:after' => 'border-width: {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}} 0 0',
					'{{WRAPPER}} .pp--pointer-framed.e--animation-corners .pp-menu-item:before' => 'border-width: {{SIZE}}{{UNIT}} 0 0 {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .pp--pointer-framed.e--animation-corners .pp-menu-item:after' => 'border-width: 0 {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}} 0',
					'{{WRAPPER}} .pp--pointer-underline .pp-menu-item:after,
					 {{WRAPPER}} .pp--pointer-overline .pp-menu-item:before,
					 {{WRAPPER}} .pp--pointer-double-line .pp-menu-item:before,
					 {{WRAPPER}} .pp--pointer-double-line .pp-menu-item:after' => 'height: {{SIZE}}{{UNIT}}',
				),
				'condition' => array(
					'pointer' => array( 'underline', 'overline', 'double-line', 'framed' ),
				),
			)
		);

		$this->add_responsive_control(
			'border_radius_menu_item',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'devices'    => array( 'desktop', 'tablet' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-menu-item:before' => 'border-radius: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .e--animation-shutter-in-horizontal .pp-menu-item:before' => 'border-radius: {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}} 0 0',
					'{{WRAPPER}} .e--animation-shutter-in-horizontal .pp-menu-item:after' => 'border-radius: 0 0 {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .e--animation-shutter-in-vertical .pp-menu-item:before' => 'border-radius: 0 {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}} 0',
					'{{WRAPPER}} .e--animation-shutter-in-vertical .pp-menu-item:after' => 'border-radius: {{SIZE}}{{UNIT}} 0 0 {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'pointer' => 'background',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_dropdown',
			array(
				'label'      => __( 'Dropdown', 'powerpack' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'dropdown',
							'operator' => '!==',
							'value'    => 'all',
						),
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'dropdown',
									'operator' => '==',
									'value'    => 'all',
								),
								array(
									'name'     => 'menu_type',
									'operator' => '==',
									'value'    => 'default',
								),
							),
						),
					),
				),
			)
		);

		$this->add_control(
			'dropdown_description',
			array(
				'raw'             => __( 'On desktop, this will affect the submenu. On mobile, this will affect the entire menu.', 'powerpack' ),
				'type'            => Controls_Manager::RAW_HTML,
				'content_classes' => 'pp-editor-info',
			)
		);

		$this->start_controls_tabs( 'tabs_dropdown_item_style' );

		$this->start_controls_tab(
			'tab_dropdown_item_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'color_dropdown_item',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--main .pp-advanced-menu--dropdown a, {{WRAPPER}} .pp-advanced-menu--type-default .pp-advanced-menu--dropdown.pp-menu-default a, {{WRAPPER}} .pp-menu-toggle' => 'color: {{VALUE}}; fill: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'background_color_dropdown_item',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--main .pp-advanced-menu--dropdown, {{WRAPPER}} .pp-advanced-menu--type-default .pp-advanced-menu--dropdown' => 'background-color: {{VALUE}}',
				),
				'separator' => 'none',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_dropdown_item_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'color_dropdown_item_hover',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--main .pp-advanced-menu--dropdown a:hover, {{WRAPPER}} .pp-advanced-menu--type-default .pp-advanced-menu--dropdown.pp-menu-default a:hover, {{WRAPPER}} .pp-menu-toggle:hover' => 'color: {{VALUE}}; fill: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'background_color_dropdown_item_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--main .pp-advanced-menu--dropdown a:hover,
					{{WRAPPER}} .pp-advanced-menu--main:not(.pp-advanced-menu--layout-expanded) .pp-advanced-menu--dropdown a.highlighted, {{WRAPPER}} .pp-advanced-menu--type-default .pp-advanced-menu--dropdown.pp-menu-default a:hover,
					{{WRAPPER}} .pp-advanced-menu--type-default .pp-advanced-menu--dropdown.pp-menu-default a.highlighted' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_dropdown_item_active',
			array(
				'label' => __( 'Active', 'powerpack' ),
			)
		);

		$this->add_control(
			'color_dropdown_item_active',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--main .pp-advanced-menu--dropdown a.pp-menu-item-active, {{WRAPPER}} .pp-advanced-menu--type-default .pp-advanced-menu--dropdown.pp-menu-default a.pp-menu-item-active' => 'color: {{VALUE}}; fill: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'background_color_dropdown_item_active',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--main .pp-advanced-menu--dropdown a.pp-menu-item-active, {{WRAPPER}} .pp-advanced-menu--type-default .pp-advanced-menu--dropdown.pp-menu-default a.pp-menu-item-active' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'dropdown_border',
				'selector'  => '{{WRAPPER}} .pp-advanced-menu--main .pp-advanced-menu--dropdown, {{WRAPPER}} .pp-advanced-menu--type-default .pp-advanced-menu--dropdown.pp-menu-default',
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'dropdown_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-advanced-menu--main .pp-advanced-menu--dropdown, {{WRAPPER}} .pp-advanced-menu--type-default .pp-advanced-menu--dropdown.pp-menu-default' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .pp-advanced-menu--main .pp-advanced-menu--dropdown li:first-child a, {{WRAPPER}} .pp-advanced-menu--type-default .pp-advanced-menu--dropdown.pp-menu-default li:first-child a' => 'border-top-left-radius: {{TOP}}{{UNIT}}; border-top-right-radius: {{RIGHT}}{{UNIT}};',
					'{{WRAPPER}} .pp-advanced-menu--main .pp-advanced-menu--dropdown li:last-child a, {{WRAPPER}} .pp-advanced-menu--type-default .pp-advanced-menu--dropdown.pp-menu-default li:last-child a' => 'border-bottom-right-radius: {{BOTTOM}}{{UNIT}}; border-bottom-left-radius: {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'dropdown_box_shadow',
				'exclude'  => array(
					'box_shadow_position',
				),
				'selector' => '{{WRAPPER}} .pp-advanced-menu--main .pp-advanced-menu--dropdown, {{WRAPPER}} .pp-advanced-menu--type-default .pp-advanced-menu__container.pp-advanced-menu--dropdown',
			)
		);

		$this->add_responsive_control(
			'dropdown_min_width',
			array(
				'label'     => __( 'Minimum Width', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 200,
				),
				'range'     => array(
					'px' => array(
						'min' => 50,
						'max' => 400,
					),
				),
				'devices'   => array( 'desktop', 'tablet', 'mobile' ),
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--main .pp-advanced-menu--dropdown' => 'min-width: {{SIZE}}{{UNIT}};',
				),
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'padding_horizontal_dropdown_item',
			array(
				'label'     => __( 'Horizontal Padding', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--main .pp-advanced-menu--dropdown a, {{WRAPPER}} .pp-advanced-menu--type-default .pp-advanced-menu--dropdown.pp-menu-default a' => 'padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}}',
				),

			)
		);

		$this->add_responsive_control(
			'padding_vertical_dropdown_item',
			array(
				'label'     => __( 'Vertical Padding', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 50,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--main .pp-advanced-menu--dropdown a, {{WRAPPER}} .pp-advanced-menu--type-default .pp-advanced-menu--dropdown.pp-menu-default a' => 'padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_control(
			'heading_dropdown_divider',
			array(
				'label'     => __( 'Divider', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'dropdown_divider',
				'selector' => '{{WRAPPER}} .pp-advanced-menu--main .pp-advanced-menu--dropdown li:not(:last-child), {{WRAPPER}} .pp-advanced-menu--type-default .pp-advanced-menu--dropdown.pp-menu-default li:not(:last-child)',
				'exclude'  => array( 'width' ),
			)
		);

		$this->add_control(
			'dropdown_divider_width',
			array(
				'label'     => __( 'Border Width', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 50,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--main .pp-advanced-menu--dropdown li:not(:last-child), {{WRAPPER}} .pp-advanced-menu--type-default .pp-advanced-menu--dropdown.pp-menu-default li:not(:last-child)' => 'border-bottom-width: {{SIZE}}{{UNIT}}',
				),
				'condition' => array(
					'dropdown_divider_border!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'dropdown_top_distance',
			array(
				'label'     => __( 'Distance', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => -100,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--main > .pp-advanced-menu > li > .pp-advanced-menu--dropdown, {{WRAPPER}} .pp-advanced-menu--type-default .pp-advanced-menu__container.pp-advanced-menu--dropdown' => 'margin-top: {{SIZE}}{{UNIT}} !important',
				),
				'separator' => 'before',
			)
		);

		$this->end_controls_section();

		/**
		 * Content Tab: Toggle Button
		 */
		$this->start_controls_section(
			'style_toggle',
			array(
				'label'     => __( 'Toggle Button', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'dropdown!' => 'none',
					'toggle!'   => '',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_toggle_style' );

		$this->start_controls_tab(
			'tab_toggle_style_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'toggle_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-menu-toggle .pp-hamburger .pp-hamburger-box .pp-hamburger-inner,
					{{WRAPPER}} .pp-menu-toggle .pp-hamburger .pp-hamburger-box .pp-hamburger-inner:before,
					{{WRAPPER}} .pp-menu-toggle .pp-hamburger .pp-hamburger-box .pp-hamburger-inner:after' => 'background-color: {{VALUE}}',
					'{{WRAPPER}} .pp-menu-toggle .pp-icon, {{WRAPPER}} .pp-menu-toggle .pp-menu-toggle-label' => 'color: {{VALUE}}',
					'{{WRAPPER}} .pp-menu-toggle .pp-icon svg' => 'fill: {{VALUE}}',
				),
				'condition' => array(
					'dropdown!' => 'none',
				),
			)
		);

		$this->add_control(
			'toggle_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-menu-toggle' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'dropdown!' => 'none',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'toggle_border',
				'label'     => __( 'Border', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-menu-toggle',
				'condition' => array(
					'dropdown!' => 'none',
				),
			)
		);

		$this->add_control(
			'toggle_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-menu-toggle' => 'border-radius: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'dropdown!' => 'none',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'toggle_box_shadow',
				'selector'  => '{{WRAPPER}} .pp-menu-toggle',
				'condition' => array(
					'dropdown!' => 'none',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_toggle_style_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'toggle_color_hover',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-menu-toggle:hover .pp-hamburger .pp-hamburger-box .pp-hamburger-inner,
					{{WRAPPER}} .pp-menu-toggle:hover .pp-hamburger .pp-hamburger-box .pp-hamburger-inner:before,
					{{WRAPPER}} .pp-menu-toggle:hover .pp-hamburger .pp-hamburger-box .pp-hamburger-inner:after' => 'background-color: {{VALUE}}',
					'{{WRAPPER}} .pp-menu-toggle:hover .pp-icon, {{WRAPPER}} .pp-menu-toggle:hover .pp-menu-toggle-label'   => 'color: {{VALUE}}',
					'{{WRAPPER}} .pp-menu-toggle:hover .pp-icon svg' => 'fill: {{VALUE}}',
				),
				'condition' => array(
					'dropdown!' => 'none',
				),
			)
		);

		$this->add_control(
			'toggle_background_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-menu-toggle:hover' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'dropdown!' => 'none',
				),
			)
		);

		$this->add_control(
			'toggle_baorder_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-menu-toggle:hover' => 'border-color: {{VALUE}}',
				),
				'condition' => array(
					'dropdown!' => 'none',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'toggle_box_shadow_hover',
				'selector'  => '{{WRAPPER}} .pp-menu-toggle:hover',
				'condition' => array(
					'dropdown!' => 'none',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'toggle_size',
			array(
				'label'     => __( 'Size', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 15,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-menu-toggle .pp-hamburger .pp-hamburger-box' => 'font-size: {{SIZE}}{{UNIT}}',
				),
				'condition' => array(
					'dropdown!' => 'none',
					'toggle'    => array( 'icon', 'icon-label' ),
				),
				'separator' => 'before',
			)
		);

		$this->add_control(
			'toggle_thickness',
			array(
				'label'     => __( 'Thickness', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 1,
						'max' => 16,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-menu-toggle .pp-hamburger .pp-hamburger-box .pp-hamburger-inner,
					{{WRAPPER}} .pp-menu-toggle .pp-hamburger .pp-hamburger-box .pp-hamburger-inner:before,
					{{WRAPPER}} .pp-menu-toggle .pp-hamburger .pp-hamburger-box .pp-hamburger-inner:after' => 'height: {{SIZE}}{{UNIT}}',
				),
				'condition' => array(
					'dropdown!'        => 'none',
					'toggle'           => array( 'icon', 'icon-label' ),
					'toggle_icon_type' => array( 'hamburger' ),
				),
			)
		);

		$this->add_responsive_control(
			'toggle_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-menu-toggle' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'heading_toggle_label_style',
			array(
				'label'     => __( 'Label', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'dropdown!' => 'none',
					'toggle'    => array( 'icon-label', 'button' ),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'toggle_label_typography',
				'scheme'    => Scheme_Typography::TYPOGRAPHY_1,
				'selector'  => '{{WRAPPER}} .pp-menu-toggle .pp-menu-toggle-label',
				'condition' => array(
					'dropdown!' => 'none',
					'toggle'    => array( 'icon-label', 'button' ),
				),
			)
		);

		$this->end_controls_section();

		/**
		 * Content Tab: Off Canvas/Full Screen
		 */
		$this->start_controls_section(
			'style_responsive',
			array(
				'label'     => __( 'Off Canvas/Full Screen', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'dropdown!' => 'none',
					'menu_type' => array( 'off-canvas', 'full-screen' ),
				),
			)
		);

		$this->add_control(
			'offcanvas_position',
			array(
				'label'     => __( 'Position', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'left',
				'options'   => array(
					'left'  => __( 'Left', 'powerpack' ),
					'right' => __( 'Right', 'powerpack' ),
				),
				'condition' => array(
					'dropdown!' => 'none',
					'menu_type' => 'off-canvas',
				),
			)
		);

		$this->add_control(
			'responsive_menu_alignment',
			array(
				'label'     => __( 'Alignment', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'space-between',
				'options'   => array(
					'space-between' => __( 'Left', 'powerpack' ),
					'center'        => __( 'Center', 'powerpack' ),
					'flex-end'      => __( 'Right', 'powerpack' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--dropdown a, .pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}} a'  => 'justify-content: {{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'offcanvas_menu_width',
			array(
				'label'      => __( 'Off Canvas Width', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw' ),
				'range'      => array(
					'px' => array(
						'min' => 100,
						'max' => 1000,
					),
				),
				'selectors'  => array(
					'body.pp-menu--off-canvas .pp-menu-off-canvas.pp-menu-{{ID}}' => 'width: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'menu_type' => 'off-canvas',
				),

			)
		);

		$this->add_control(
			'overlay_bg_color',
			array(
				'label'     => __( 'Menu Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(0,0,0,0.8)',
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--dropdown.pp-advanced-menu__container,
					.pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}}'  => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_responsive_style' );

		$this->start_controls_tab(
			'tab_responsive_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'mobile_link_color',
			array(
				'label'     => __( 'Link Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--dropdown.pp-advanced-menu__container .pp-menu-item,
					.pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}} .pp-menu-item' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'mobile_sub_link_bg_color',
			array(
				'label'     => __( 'Sub Menu Link Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--dropdown.pp-advanced-menu__container a.pp-sub-item,
					.pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}} a.pp-sub-item, .pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}} .sub-menu' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'mobile_sub_link_color',
			array(
				'label'     => __( 'Sub Menu Link Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--dropdown.pp-advanced-menu__container a.pp-sub-item, .pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}} a.pp-sub-item' => 'color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_responsive_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'mobile_link_hover',
			array(
				'label'     => __( 'Link Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--dropdown.pp-advanced-menu__container .pp-menu-item:hover,
					{{WRAPPER}} .pp-advanced-menu--dropdown.pp-advanced-menu__container .pp-menu-item:focus,
					{{WRAPPER}} .pp-advanced-menu--dropdown.pp-advanced-menu__container .pp-menu-item.pp-menu-item-active,
					.pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}} .pp-menu-item:hover,
					.pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}} .pp-menu-item:focus,
					.pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}} .pp-menu-item.pp-menu-item-active' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'mobile_link_bg_hover',
			array(
				'label'     => __( 'Link Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--dropdown.pp-advanced-menu__container .pp-menu-item:hover,
					{{WRAPPER}} .pp-advanced-menu--dropdown.pp-advanced-menu__container .pp-menu-item:focus,
					{{WRAPPER}} .pp-advanced-menu--dropdown.pp-advanced-menu__container .pp-menu-item.pp-menu-item-active,
					.pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}} .pp-menu-item:hover,
					.pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}} .pp-menu-item:focus,
					.pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}} .pp-menu-item.pp-menu-item-active' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'mobile_sub_link_bg_hover',
			array(
				'label'     => __( 'Sub Menu Link Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--dropdown.pp-advanced-menu__container a.pp-sub-item:hover,
					{{WRAPPER}} .pp-advanced-menu--dropdown.pp-advanced-menu__container a.pp-sub-item:focus,
					{{WRAPPER}} .pp-advanced-menu--dropdown.pp-advanced-menu__container a.pp-sub-item:active,
					.pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}} a.pp-sub-item:hover,
					.pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}} a.pp-sub-item:focus,
					.pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}} a.pp-sub-item:active' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'mobile_sub_link_hover',
			array(
				'label'     => __( 'Sub Menu Link Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--dropdown.pp-advanced-menu__container a.pp-sub-item:hover, .pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}} a.pp-sub-item:hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'mobile_submenu_indent',
			array(
				'label'     => __( 'Submenu Indent', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'selectors' => array(
					'.pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}} .sub-menu' => 'padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}}',
				),
				'separator' => 'before',

			)
		);

		$this->add_control(
			'padding_horizontal_mobile_link_item',
			array(
				'label'     => __( 'Horizontal Padding', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--dropdown.pp-advanced-menu__container .pp-menu-item, {{WRAPPER}} .pp-advanced-menu--dropdown.pp-advanced-menu__container a.pp-sub-item, .pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}} .pp-menu-item, .pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}} a.pp-sub-item' => 'padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}}',
				),
				'separator' => 'before',

			)
		);

		$this->add_control(
			'padding_vertical_mobile_link_item',
			array(
				'label'     => __( 'Vertical Padding', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 50,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu--dropdown.pp-advanced-menu__container .pp-menu-item, {{WRAPPER}} .pp-advanced-menu--dropdown.pp-advanced-menu__container a.pp-sub-item, .pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}} .pp-menu-item, .pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}} a.pp-sub-item' => 'padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'mobile_menu_border',
				'selector'  => '{{WRAPPER}} .pp-advanced-menu--dropdown li:not(:last-child), .pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}} li:not(:last-child)',
				'separator' => 'before',
			)
		);

		$this->add_control(
			'hr',
			array(
				'type'      => Controls_Manager::DIVIDER,
				'style'     => 'thick',
				'condition' => array(
					'dropdown!' => 'none',
					'menu_type' => 'off-canvas',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'mobile_menu_box_shadow',
				'selector'  => '{{WRAPPER}} .pp-advanced-menu--dropdown.pp-advanced-menu__container, .pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}}',
				'condition' => array(
					'dropdown!' => 'none',
					'menu_type' => 'off-canvas',
				),
				'separator' => 'before',
			)
		);

		$this->add_control(
			'heading_close_icon_style',
			array(
				'label'     => __( 'Close Icon', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'dropdown!' => 'none',
					'menu_type' => array( 'off-canvas', 'full-screen' ),
				),
			)
		);

		$this->add_control(
			'close_icon_size',
			array(
				'label'     => __( 'Close Icon Size', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 15,
					),
				),
				'selectors' => array(
					'body.pp-menu--off-canvas .pp-advanced-menu--dropdown.pp-menu-{{ID}} .pp-menu-close, {{WRAPPER}} .pp-advanced-menu--type-full-screen .pp-advanced-menu--dropdown.pp-advanced-menu__container .pp-menu-close' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}',
					'body.pp-menu--off-canvas .pp-advanced-menu--dropdown.pp-menu-{{ID}} .pp-menu-close:before, {{WRAPPER}} .pp-advanced-menu--type-full-screen .pp-advanced-menu--dropdown.pp-advanced-menu__container .pp-menu-close:before,
					body.pp-menu--off-canvas .pp-advanced-menu--dropdown.pp-menu-{{ID}} .pp-menu-close:after, {{WRAPPER}} .pp-advanced-menu--type-full-screen .pp-advanced-menu--dropdown.pp-advanced-menu__container .pp-menu-close:after' => 'height: {{SIZE}}{{UNIT}}',
				),
				'condition' => array(
					'dropdown!' => 'none',
					'menu_type' => array( 'off-canvas', 'full-screen' ),
				),
			)
		);

		$this->add_control(
			'close_icon_horizontal_position',
			array(
				'label'     => __( 'Horizontal Position', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'selectors' => array(
					'body.pp-menu--off-canvas .pp-advanced-menu--dropdown.pp-menu-off-canvas-left.pp-menu-{{ID}} .pp-menu-close-wrap, {{WRAPPER}} .pp-advanced-menu--type-full-screen .pp-advanced-menu--dropdown.pp-advanced-menu__container .pp-menu-close-wrap' => 'right: {{SIZE}}{{UNIT}};',
					'body.pp-menu--off-canvas .pp-advanced-menu--dropdown.pp-menu-off-canvas-right.pp-menu-{{ID}} .pp-menu-close-wrap' => 'left: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'close_icon_vertical_position',
			array(
				'label'     => __( 'Vertical Position', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 80,
					),
				),
				'selectors' => array(
					'body.pp-menu--off-canvas .pp-advanced-menu--dropdown.pp-menu-{{ID}} .pp-menu-close-wrap, {{WRAPPER}} .pp-advanced-menu--type-full-screen .pp-advanced-menu--dropdown.pp-advanced-menu__container .pp-menu-close-wrap' => 'top: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'close_icon_padding',
			array(
				'label'     => __( 'Padding', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 30,
					),
				),
				'selectors' => array(
					'body.pp-menu--off-canvas .pp-advanced-menu--dropdown.pp-menu-{{ID}} .pp-menu-close-wrap, {{WRAPPER}} .pp-advanced-menu--type-full-screen .pp-advanced-menu--dropdown.pp-advanced-menu__container .pp-menu-close-wrap' => 'padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}}; padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_close_icon_style' );

		$this->start_controls_tab(
			'tab_close_icon_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'close_icon_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'body.pp-menu--off-canvas .pp-advanced-menu--dropdown.pp-menu-{{ID}} .pp-menu-close:before, {{WRAPPER}} .pp-advanced-menu--type-full-screen .pp-advanced-menu--dropdown.pp-advanced-menu__container .pp-menu-close:before,
					body.pp-menu--off-canvas .pp-advanced-menu--dropdown.pp-menu-{{ID}} .pp-menu-close:after, {{WRAPPER}} .pp-advanced-menu--type-full-screen .pp-advanced-menu--dropdown.pp-advanced-menu__container .pp-menu-close:after' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'dropdown!' => 'none',
					'menu_type' => array( 'off-canvas', 'full-screen' ),
				),
			)
		);

		$this->add_control(
			'close_icon_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'body.pp-menu--off-canvas .pp-advanced-menu--dropdown.pp-menu-{{ID}} .pp-menu-close-wrap, {{WRAPPER}} .pp-advanced-menu--type-full-screen .pp-advanced-menu--dropdown.pp-advanced-menu__container .pp-menu-close-wrap' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'dropdown!' => 'none',
					'menu_type' => array( 'off-canvas', 'full-screen' ),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'close_icon_border',
				'selector'  => 'body.pp-menu--off-canvas .pp-advanced-menu--dropdown.pp-menu-{{ID}} .pp-menu-close-wrap, {{WRAPPER}} .pp-advanced-menu--type-full-screen .pp-advanced-menu--dropdown.pp-advanced-menu__container .pp-menu-close-wrap',
			)
		);

		$this->add_responsive_control(
			'close_icon_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'body.pp-menu--off-canvas .pp-advanced-menu--dropdown.pp-menu-{{ID}} .pp-menu-close-wrap, {{WRAPPER}} .pp-advanced-menu--type-full-screen .pp-advanced-menu--dropdown.pp-advanced-menu__container .pp-menu-close-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_close_icon_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'close_icon_color_hover',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'body.pp-menu--off-canvas .pp-advanced-menu--dropdown.pp-menu-{{ID}} .pp-menu-close-wrap:hover .pp-menu-close:before, {{WRAPPER}} .pp-advanced-menu--type-full-screen .pp-advanced-menu--dropdown.pp-advanced-menu__container .pp-menu-close-wrap:hover .pp-menu-close:before,
					body.pp-menu--off-canvas .pp-advanced-menu--dropdown.pp-menu-{{ID}} .pp-menu-close-wrap:hover .pp-menu-close:after, {{WRAPPER}} .pp-advanced-menu--type-full-screen .pp-advanced-menu--dropdown.pp-advanced-menu__container .pp-menu-close-wrap:hover .pp-menu-close:after' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'dropdown!' => 'none',
					'menu_type' => array( 'off-canvas', 'full-screen' ),
				),
			)
		);

		$this->add_control(
			'close_icon_background_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'body.pp-menu--off-canvas .pp-advanced-menu--dropdown.pp-menu-{{ID}} .pp-menu-close-wrap:hover, {{WRAPPER}} .pp-advanced-menu--type-full-screen .pp-advanced-menu--dropdown.pp-advanced-menu__container .pp-menu-close-wrap:hover' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'dropdown!' => 'none',
					'menu_type' => array( 'off-canvas', 'full-screen' ),
				),
			)
		);

		$this->add_control(
			'close_icon_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'body.pp-menu--off-canvas .pp-advanced-menu--dropdown.pp-menu-{{ID}} .pp-menu-close-wrap:hover, {{WRAPPER}} .pp-advanced-menu--type-full-screen .pp-advanced-menu--dropdown.pp-advanced-menu__container .pp-menu-close-wrap:hover' => 'border-color: {{VALUE}}',
				),
				'condition' => array(
					'dropdown!' => 'none',
					'menu_type' => array( 'off-canvas', 'full-screen' ),
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		/**
		 * Content Tab: Typography
		 */
		$this->start_controls_section(
			'style_typography',
			array(
				'label' => __( 'Typography', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'main_typography_heading',
			array(
				'label' => __( 'Main Menu/Off Canvas/Full Screen', 'powerpack' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'menu_typography',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-advanced-menu .pp-menu-item, {{WRAPPER}} .pp-advanced-menu-main-wrapper.pp-advanced-menu--type-full-screen .pp-advanced-menu--dropdown .pp-menu-item, .pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}} .pp-menu-item',
			)
		);

		$this->add_control(
			'dropdown_typography_heading',
			array(
				'label'     => __( 'Dropdown/Submenu', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'dropdown_typography',
				'scheme'    => Scheme_Typography::TYPOGRAPHY_4,
				'selector'  => '{{WRAPPER}} .pp-advanced-menu--dropdown .pp-menu-item, {{WRAPPER}} .pp-advanced-menu--dropdown .pp-sub-item, .pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}} .sub-menu .pp-menu-item, .pp-advanced-menu--dropdown.pp-advanced-menu__container.pp-menu-{{ID}} .sub-menu .pp-sub-item',
				'separator' => 'before',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_indicator',
			array(
				'label'     => __( 'Submenu Indicator', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'submenu_icon[value]!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'indicator_size',
			array(
				'label'     => __( 'Size', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'selectors' => array(
					'{{WRAPPER}} .pp-advanced-menu .sub-arrow, .pp-advanced-menu__container.pp-menu-{{ID}} .sub-arrow' => 'font-size: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'submenu_icon[value]!' => '',
				),
			)
		);

		$this->end_controls_section();
	}

	public function get_frontend_settings() {
		$frontend_settings = parent::get_frontend_settings();

		// If the saved value is FA4, but the user has upgraded to FA5, the value needs to be converted to FA5.
		if ( 'fa ' === substr( $frontend_settings['submenu_icon']['value'], 0, 3 ) && Icons_Manager::is_migration_allowed() ) {
			$frontend_settings['submenu_icon']['value'] = str_replace( 'fa ', 'fas ', $frontend_settings['submenu_icon']['value'] );
		}

		// Determine the submenu icon markup.
		if ( \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_font_icon_svg' ) ) {
			$icon_classes = [];

			if ( false !== strpos( $frontend_settings['submenu_icon']['value'], 'chevron-down' ) ) {
				$icon_classes['class'] = 'fa-svg-chevron-down';
			}

			$icon_content = Icons_Manager::render_font_icon( $frontend_settings['submenu_icon'], $icon_classes );
		} else {
			$icon_content = sprintf( '<i class="%s"></i>', $frontend_settings['submenu_icon']['value'] );
		}

		// Passing the entire icon markup to the frontend settings because it can be either <i> or <svg> tag.
		$frontend_settings['submenu_icon']['value'] = $icon_content;

		return $frontend_settings;
	}

	/**
	 * Render widget output
	 *
	 * @access protected
	 */
	protected function render() {
		$available_menus = $this->get_available_menus();

		if ( ! $available_menus ) {
			return;
		}

		$settings = $this->get_active_settings();

		$settings_attr = array(
			'menu_id'    => esc_attr( $this->get_id() ),
			'breakpoint' => $settings['dropdown'],
		);

		if ( 'none' !== $settings['dropdown'] ) {
			$settings_attr['full_width'] = ( ! $settings['full_width'] || empty( $settings['full_width'] ) ) ? false : true;
		}

		$args = array(
			'echo'        => false,
			'menu'        => $settings['menu'],
			'menu_class'  => 'pp-advanced-menu',
			'fallback_cb' => '__return_empty_string',
			'container'   => '',
		);

		if ( 'vertical' === $settings['layout'] ) {
			$args['menu_class'] .= ' sm-vertical';
		}

		// Add custom filter to handle Nav Menu HTML output.
		add_filter( 'nav_menu_link_attributes', array( $this, 'handle_link_classes' ), 10, 4 );
		add_filter( 'nav_menu_submenu_css_class', array( $this, 'handle_sub_menu_classes' ) );
		add_filter( 'nav_menu_item_id', '__return_empty_string' );

		// General Menu.
		$menu_html = wp_nav_menu( $args );

		// Dropdown Menu.
		$dropdown_menu_html = wp_nav_menu( $args );

		// Remove all our custom filters.
		remove_filter( 'nav_menu_link_attributes', array( $this, 'handle_link_classes' ) );
		remove_filter( 'nav_menu_submenu_css_class', array( $this, 'handle_sub_menu_classes' ) );
		remove_filter( 'nav_menu_item_id', '__return_empty_string' );

		if ( empty( $menu_html ) ) {
			return;
		}

		$menu_toggle_classes = array(
			'pp-menu-toggle',
		);

		if ( 'dropdown' !== $settings['layout'] ) {
			$menu_toggle_classes[] = 'pp-menu-toggle-on-' . $settings['dropdown'];
		} else {
			$menu_toggle_classes[] = 'pp-menu-toggle-on-all';
		}

		if ( 'icon-label' === $settings['toggle'] ) {
			$menu_toggle_classes[] = 'pp-menu-toggle-label-' . $settings['label_align'];
		}

		$this->add_render_attribute( 'menu-toggle', 'class', $menu_toggle_classes );

		// if ( Plugin::elementor()->editor->is_edit_mode() ) {
		// $this->add_render_attribute( 'menu-toggle', [
		// 'class' => 'pp-clickable',
		// ] );
		// }

		$menu_wrapper_classes  = 'pp-advanced-menu__align-' . $settings['align_items'];
		$menu_wrapper_classes .= ' pp-advanced-menu--dropdown-' . $settings['dropdown'];
		$menu_wrapper_classes .= ' pp-advanced-menu--type-' . $settings['menu_type'];
		$menu_wrapper_classes .= ' pp-advanced-menu__text-align-' . $settings['text_align'];
		$menu_wrapper_classes .= ' pp-advanced-menu--toggle pp-advanced-menu--' . $settings['toggle'];

		if ( 'vertical' === $settings['layout'] && 'yes' === $settings['expanded_submenu'] ) {
			$menu_wrapper_classes .= ' pp-advanced-menu__submenu-visible-always';
		}
		?>

		<?php do_action( 'ppe_before_advanced_menu_wrapper' ); ?>
		<div class="pp-advanced-menu-main-wrapper <?php echo esc_attr( $menu_wrapper_classes ); ?>">
		<?php
		if ( 'all' !== $settings['dropdown'] ) :
			$this->add_render_attribute(
				'main-menu',
				'class',
				array(
					'pp-advanced-menu--main',
					'pp-advanced-menu__container',
					'pp-advanced-menu--layout-' . $settings['layout'],
				)
			);

			if ( $settings['pointer'] ) :
				$this->add_render_attribute( 'main-menu', 'class', 'pp--pointer-' . $settings['pointer'] );

				foreach ( $settings as $key => $value ) :
					if ( 0 === strpos( $key, 'animation' ) && $value ) :
						$this->add_render_attribute( 'main-menu', 'class', 'e--animation-' . $value );

						break;
					endif;
				endforeach;
			endif;
			?>
			<?php do_action( 'ppe_before_advanced_menu' ); ?>
			<nav id="pp-menu-<?php echo esc_attr( $this->get_id() ); ?>" <?php echo wp_kses_post( $this->get_render_attribute_string( 'main-menu' ) ); ?> data-settings="<?php echo htmlspecialchars( json_encode( $settings_attr ) ); ?>"><?php echo wp_kses_post( $menu_html ); ?></nav>
			<?php do_action( 'ppe_after_advanced_menu' ); ?>
			<?php
		endif;
		?>
		<?php if ( 'none' !== $settings['dropdown'] ) { ?>
			<?php if ( '' !== $settings['toggle'] ) { ?>
				<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'menu-toggle' ) ); ?>>
					<?php if ( 'icon-label' === $settings['toggle'] || 'icon' === $settings['toggle'] ) { ?>
						<div class="pp-hamburger">
							<div class="pp-hamburger-box">
								<?php if ( 'custom' === $settings['toggle_icon_type'] ) { ?>
									<?php if ( '' !== $settings['toggle_icon']['value'] ) { ?>
										<span class="pp-hamburger-icon pp-icon">
											<?php Icons_Manager::render_icon( $settings['toggle_icon'], array( 'aria-hidden' => 'true' ) ); ?>
										</span>
									<?php } ?>
								<?php } else { ?>
									<div class="pp-hamburger-inner"></div>
								<?php } ?>
							</div>
						</div>
					<?php } ?>
					<?php if ( 'icon-label' === $settings['toggle'] || 'button' === $settings['toggle'] ) { ?>
						<?php if ( '' !== $settings['toggle_label'] ) { ?>
							<span class="pp-menu-toggle-label">
								<?php echo wp_kses_post( $settings['toggle_label'] ); ?>
							</span>
						<?php } ?>
					<?php } ?>
				</div>
			<?php } ?>
			<?php
				$offcanvas_pos = '';
			if ( 'off-canvas' === $settings['menu_type'] ) {
				$offcanvas_pos = ' pp-menu-off-canvas-' . $settings['offcanvas_position'];
			}
			?>
			<?php do_action( 'ppe_before_advanced_menu_responsive' ); ?>
			<nav class="pp-advanced-menu--dropdown pp-menu-style-toggle pp-advanced-menu__container pp-menu-<?php echo esc_attr( $this->get_id() ); ?> pp-menu-<?php echo esc_attr( $settings['menu_type'] ); ?><?php echo esc_attr( $offcanvas_pos ); ?>" data-settings="<?php echo htmlspecialchars( json_encode( $settings_attr ) ); ?>">
				<?php if ( 'full-screen' === $settings['menu_type'] || 'off-canvas' === $settings['menu_type'] ) { ?>
					<div class="pp-menu-close-wrap">
						<div class="pp-menu-close"></div>
					</div>
				<?php } ?>
				<?php do_action( 'ppe_before_advanced_menu_responsive_inner' ); ?>
				<?php echo wp_kses_post( $dropdown_menu_html ); ?>
				<?php do_action( 'ppe_after_advanced_menu_responsive_inner' ); ?>
			</nav>
			<?php do_action( 'ppe_after_advanced_menu_responsive' ); ?>
		<?php } ?>
		</div>
		<?php do_action( 'ppe_after_advanced_menu_wrapper' ); ?>
		<?php
	}

	/**
	 * Menu item link classes
	 *
	 * @param  mixed $atts   attributes.
	 * @param  mixed $item   item.
	 * @param  mixed $args   arguments.
	 * @param  mixed $depth  depth.
	 * @return $atts
	 */
	public function handle_link_classes( $atts, $item, $args, $depth ) {
		$settings = $this->get_settings();
		$is_anchor = false !== strpos( $atts['href'], '#' );

		$classes = $depth ? 'pp-sub-item' : 'pp-menu-item';

		if ( ! $is_anchor && in_array( 'current-menu-item', $item->classes ) ) {
			$classes .= ' pp-menu-item-active';
		}

		if ( $is_anchor ) {
			$classes .= ' pp-menu-item-anchor';
		}

		if ( empty( $atts['class'] ) ) {
			$atts['class'] = $classes;
		} else {
			$atts['class'] .= ' ' . $classes;
		}

		return $atts;
	}

	/**
	 * Sub menu classes
	 *
	 * @param  mixed $classes menu item classes.
	 * @return array $classes
	 */
	public function handle_sub_menu_classes( $classes ) {
		$classes[] = 'pp-advanced-menu--dropdown';

		return $classes;
	}

	public function render_plain_content() {}
}
