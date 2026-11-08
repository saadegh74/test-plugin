<?php
namespace PowerpackElements\Modules\ModalPopup\Widgets;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Classes\PP_Config;

// Elementor Classes
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Icons_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography as Scheme_Typography;
use Elementor\Core\Schemes\Color as Scheme_Color;
use Elementor\Modules\DynamicTags\Module as TagsModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Popup Box Widget
 */
class Modal_Popup extends Powerpack_Widget {

	/**
	 * Retrieve popup box widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Popup_Box' );
	}

	/**
	 * Retrieve popup box widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Popup_Box' );
	}

	/**
	 * Retrieve popup box widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Popup_Box' );
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
		return parent::get_widget_keywords( 'Popup_Box' );
	}

	/**
	 * Retrieve the list of scripts the popup box widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
			return [
				'jquery-cookie',
				'pp-magnific-popup',
				'powerpack-frontend',
			];
		}

		$settings = $this->get_settings_for_display();
		$scripts = [
			'pp-magnific-popup',
			'powerpack-frontend',
		];

		if ( 'page-load' === $settings['trigger'] || 'exit-intent' === $settings['trigger'] ) {
			array_push( $scripts, 'jquery-cookie' );
		}

		return $scripts;
	}

	/**
	 * Retrieve the list of scripts the popup box widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_style_depends() {
		return [
			'pp-magnific-popup',
		];
	}

	/**
	 * Register popup box widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 2.0.3
	 * @access protected
	 */
	protected function register_controls() {
		/* Content Tab */
		$this->register_content_content_controls();
		$this->register_content_layout_controls();
		$this->register_content_trigger_controls();
		$this->register_content_settings_controls();
		$this->register_content_help_docs_controls();

		/* Style Tab */
		$this->register_style_popup_controls();
		$this->register_style_overlay_controls();
		$this->register_style_title_controls();
		$this->register_style_content_controls();
		$this->register_style_trigger_icon_controls();
		$this->register_style_trigger_button_controls();
		$this->register_style_close_button_controls();
	}

	/*-----------------------------------------------------------------------------------*/
	/*	CONTENT TAB
	/*-----------------------------------------------------------------------------------*/

	protected function register_content_content_controls() {
		/**
		 * Content Tab: Content
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_content',
			array(
				'label' => __( 'Content', 'powerpack' ),
			)
		);

		$this->add_control(
			'preview_popup',
			array(
				'label'        => __( 'Preview Popup', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'popup_title',
			array(
				'label'        => __( 'Enable Title', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'title',
			array(
				'label'     => __( 'Title', 'powerpack' ),
				'type'      => Controls_Manager::TEXT,
				'dynamic'   => array(
					'active' => true,
				),
				'default'   => __( 'Modal Title', 'powerpack' ),
				'condition' => array(
					'popup_title' => 'yes',
				),
			)
		);

		$this->add_control(
			'popup_type',
			array(
				'label'   => __( 'Type', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'image'       => __( 'Image', 'powerpack' ),
					'link'        => __( 'Link (Video/Map/Page)', 'powerpack' ),
					'content'     => __( 'Content', 'powerpack' ),
					'template'    => __( 'Saved Templates', 'powerpack' ),
					'custom-html' => __( 'Custom HTML', 'powerpack' ),
				),
				'default' => 'image',
			)
		);

		$this->add_control(
			'image',
			array(
				'label'     => __( 'Choose Image', 'powerpack' ),
				'type'      => Controls_Manager::MEDIA,
				'dynamic'   => array(
					'active' => true,
				),
				'default'   => array(
					'url' => Utils::get_placeholder_image_src(),
				),
				'condition' => array(
					'popup_type' => 'image',
				),
			)
		);

		$this->add_control(
			'popup_image_link',
			array(
				'label'         => __( 'Image Link', 'powerpack' ),
				'type'          => Controls_Manager::URL,
				'dynamic'       => array(
					'active' => true,
				),
				'show_external' => false, // Show the 'open in new tab' button.
				'condition'     => array(
					'popup_type' => 'image',
				),
			)
		);

		$this->add_control(
			'popup_link',
			array(
				'label'         => __( 'Enter URL', 'powerpack' ),
				'type'          => Controls_Manager::URL,
				'dynamic'       => array(
					'active' => true,
				),
				'show_external' => false, // Show the 'open in new tab' button.
				'condition'     => array(
					'popup_type' => 'link',
				),
			)
		);

		$this->add_control(
			'content',
			array(
				'label'     => __( 'Content', 'powerpack' ),
				'type'      => Controls_Manager::WYSIWYG,
				'default'   => __( 'I am the popup Content', 'powerpack' ),
				'dynamic'   => array(
					'active' => true,
				),
				'condition' => array(
					'popup_type' => 'content',
				),
			)
		);

		$this->add_control(
			'templates',
			array(
				'label'       => __( 'Choose Template', 'powerpack' ),
				'type'        => 'pp-query',
				'label_block' => false,
				'multiple'    => false,
				'query_type'  => 'templates-all',
				'condition'   => array(
					'popup_type' => 'template',
				),
			)
		);

		$this->add_control(
			'custom_html',
			array(
				'label'     => __( 'Custom HTML', 'powerpack' ),
				'type'      => Controls_Manager::CODE,
				'language'  => 'html',
				'condition' => array(
					'popup_type' => 'custom-html',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function register_content_layout_controls() {
		/**
		 * Content Tab: Layout
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => __( 'Layout', 'powerpack' ),
			)
		);

		$this->add_control(
			'layout_type',
			array(
				'label'              => __( 'Layout', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'options'            => array(
					'standard'   => __( 'Standard', 'powerpack' ),
					'fullscreen' => __( 'Fullscreen', 'powerpack' ),
				),
				'default'            => 'standard',
				'frontend_available' => true,
			)
		);

		$this->add_responsive_control(
			'popup_width',
			array(
				'label'      => __( 'Width', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array(
					'size' => '550',
					'unit' => 'px',
				),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1920,
						'step' => 1,
					),
				),
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'.pp-modal-popup-window.pp-modal-popup-window-{{ID}}' => 'width: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'layout_type' => 'standard',
				),
			)
		);

		$this->add_control(
			'auto_height',
			array(
				'label'        => __( 'Auto Height', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'layout_type' => 'standard',
				),
			)
		);

		$this->add_responsive_control(
			'popup_height',
			array(
				'label'      => __( 'Height', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array(
					'size' => '450',
					'unit' => 'px',
				),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1000,
						'step' => 1,
					),
				),
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'.pp-modal-popup-window-{{ID}}' => 'height: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'auto_height!' => 'yes',
					'layout_type'  => 'standard',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function register_content_trigger_controls() {
		/**
		 * Content Tab: Trigger
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_trigger',
			array(
				'label' => __( 'Trigger', 'powerpack' ),
			)
		);

		$this->add_control(
			'trigger',
			array(
				'label'              => __( 'Trigger', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'on-click',
				'options'            => array(
					'on-click'    => __( 'On Click', 'powerpack' ),
					'page-load'   => __( 'Time Delayed', 'powerpack' ),
					'exit-intent' => __( 'Exit Intent', 'powerpack' ),
					'other'       => __( 'Element Class/ID', 'powerpack' ),
				),
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'trigger_type',
			array(
				'label'     => __( 'Type', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'button',
				'options'   => array(
					'button' => __( 'Button', 'powerpack' ),
					'icon'   => __( 'Icon', 'powerpack' ),
					'image'  => __( 'Image', 'powerpack' ),
				),
				'condition' => array(
					'trigger' => 'on-click',
				),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'     => __( 'Button Text', 'powerpack' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Click Here', 'powerpack' ),
				'condition' => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'button',
				),
			)
		);

		$this->add_control(
			'select_button_icon',
			array(
				'label'            => __( 'Button Icon', 'powerpack' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'button_icon',
				'condition'        => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'button',
				),
			)
		);

		$this->add_control(
			'button_icon_position',
			array(
				'label'     => __( 'Icon Position', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'after',
				'options'   => array(
					'after'  => __( 'After', 'powerpack' ),
					'before' => __( 'Before', 'powerpack' ),
				),
				'condition' => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'button',
				),
			)
		);

		$this->add_control(
			'select_trigger_icon',
			array(
				'label'            => __( 'Icon', 'powerpack' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'trigger_icon',
				'condition'        => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'icon',
				),
			)
		);

		$this->add_control(
			'trigger_image',
			array(
				'label'     => __( 'Choose Image', 'powerpack' ),
				'type'      => Controls_Manager::MEDIA,
				'dynamic'   => array(
					'active' => true,
				),
				'default'   => array(
					'url' => Utils::get_placeholder_image_src(),
				),
				'condition' => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'image',
				),
			)
		);

		$this->add_control(
			'delay',
			array(
				'label'     => __( 'Delay', 'powerpack' ),
				'title'     => __( 'seconds', 'powerpack' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 1,
				'min'       => 1,
				'step'      => 1,
				'condition' => array(
					'trigger' => 'page-load',
				),
			)
		);

		$this->add_control(
			'display_after_page_load',
			array(
				'label'       => __( 'Display After', 'powerpack' ),
				'title'       => __( 'day(s)', 'powerpack' ),
				'description' => __( 'If a user closes the modal box, it will be displayed only after the defined day(s)', 'powerpack' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 1,
				'min'         => 0,
				'step'        => 1,
				'condition'   => array(
					'trigger' => 'page-load',
				),
			)
		);

		$this->add_control(
			'display_after_exit_intent',
			array(
				'label'       => __( 'Display After', 'powerpack' ),
				'title'       => __( 'day(s)', 'powerpack' ),
				'description' => __( 'If a user closes the modal box, it will be displayed only after the defined day(s)', 'powerpack' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 1,
				'min'         => 0,
				'step'        => 1,
				'condition'   => array(
					'trigger' => 'exit-intent',
				),
			)
		);

		$this->add_control(
			'element_identifier',
			array(
				'label'       => __( 'Element CSS Class/ID', 'powerpack' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => '.pp-modal-popup-link',
				'dynamic'     => array(
					'active' => true,
				),
				'condition'   => array(
					'trigger' => 'other',
				),
			)
		);

		$this->add_control(
			'enable_url_trigger',
			array(
				'label'              => __( 'Enable URL Trigger', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => '',
				'label_on'           => __( 'Yes', 'powerpack' ),
				'label_off'          => __( 'No', 'powerpack' ),
				'return_value'       => 'yes',
				'separator'          => 'before',
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'element_url_identifier',
			array(
				'label'       => __( 'Element ID', 'powerpack' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'condition'   => array(
					'enable_url_trigger' => 'yes',
				),
				'frontend_available' => true,
			)
		);

		$this->end_controls_section();
	}

	protected function register_content_settings_controls() {
		/**
		 * Content Tab: Settings
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_settings',
			array(
				'label' => __( 'Settings', 'powerpack' ),
			)
		);

		$this->add_control(
			'prevent_scroll',
			array(
				'label'              => __( 'Prevent Page Scroll', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'label_on'           => __( 'Yes', 'powerpack' ),
				'label_off'          => __( 'No', 'powerpack' ),
				'return_value'       => 'yes',
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'close_button',
			array(
				'label'              => __( 'Show Close Button', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'label_on'           => __( 'Yes', 'powerpack' ),
				'label_off'          => __( 'No', 'powerpack' ),
				'return_value'       => 'yes',
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'esc_exit',
			array(
				'label'              => __( 'Close on Esc Keypress', 'powerpack' ),
				'description'        => __( 'Close the popup when user presses the Esc key', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'label_on'           => __( 'Yes', 'powerpack' ),
				'label_off'          => __( 'No', 'powerpack' ),
				'return_value'       => 'yes',
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'click_exit',
			array(
				'label'              => __( 'Close on Overlay Click', 'powerpack' ),
				'description'        => __( 'Close the popup when user clicks on the overlay', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'label_on'           => __( 'Yes', 'powerpack' ),
				'label_off'          => __( 'No', 'powerpack' ),
				'return_value'       => 'yes',
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'content_close',
			array(
				'label'              => __( 'Close on Content Click', 'powerpack' ),
				'description'        => __( 'Close the popup when user clicks on content of it', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => '',
				'label_on'           => __( 'Yes', 'powerpack' ),
				'label_off'          => __( 'No', 'powerpack' ),
				'return_value'       => 'yes',
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'popup_disable_on',
			array(
				'label'     => __( 'Disable On', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array(
					''       => __( 'None', 'powerpack' ),
					'tablet' => __( 'Mobile & Tablet', 'powerpack' ),
					'mobile' => __( 'Mobile', 'powerpack' ),
				),
				'separator' => 'before',
			)
		);

		$this->add_control(
			'popup_animation_in',
			array(
				'label'              => __( 'Animation', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'mfp-zoom-in',
				'options'            => array(
					''                  => __( 'None', 'powerpack' ),
					'mfp-zoom-in'       => __( 'Zoom In', 'powerpack' ),
					'mfp-zoom-out'      => __( 'Zoom Out', 'powerpack' ),
					'mfp-3d-unfold'     => __( '3D Unfold', 'powerpack' ),
					'mfp-newspaper'     => __( 'Newspaper', 'powerpack' ),
					'mfp-move-from-top' => __( 'Move From Top', 'powerpack' ),
					'mfp-move-left'     => __( 'Move Left', 'powerpack' ),
					'mfp-move-right'    => __( 'Move Right', 'powerpack' ),
				),
				'frontend_available' => true,
			)
		);

		$this->end_controls_section();
	}

	protected function register_content_help_docs_controls() {

		$help_docs = PP_Config::get_widget_help_links( 'Popup_Box' );

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

	protected function register_style_popup_controls() {
		/**
		 * Style Tab: Popup
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_popup_window_style',
			array(
				'label' => __( 'Popup', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'popup_bg',
				'label'    => __( 'Background', 'powerpack' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '#pp-modal-popup-window-{{ID}}',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'popup_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '#pp-modal-popup-window-{{ID}}',
			)
		);

		$this->add_control(
			'popup_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'#pp-modal-popup-window-{{ID}}' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'popup_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'#pp-modal-popup-window-{{ID}}' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'popup_box_shadow',
				'selector'  => '#pp-modal-popup-window-{{ID}}',
				'separator' => 'before',
			)
		);

		$this->end_controls_section();
	}

	protected function register_style_overlay_controls() {
		/**
		 * Style Tab: Overlay
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_popup_overlay_style',
			array(
				'label' => __( 'Overlay', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'overlay_switch',
			array(
				'label'              => __( 'Overlay', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'label_on'           => __( 'Show', 'powerpack' ),
				'label_off'          => __( 'Hide', 'powerpack' ),
				'return_value'       => 'yes',
				'frontend_available' => true,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'overlay_bg',
				'label'     => __( 'Background', 'powerpack' ),
				'types'     => array( 'classic', 'gradient' ),
				'exclude'   => array( 'image' ),
				'selector'  => '.pp-modal-popup-{{ID}}',
				'condition' => array(
					'overlay_switch' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function register_style_title_controls() {
		/**
		 * Style Tab: Title
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_title_style',
			array(
				'label'     => __( 'Title', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'popup_title' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'title_align',
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
				'default'   => '',
				'selectors' => array(
					'.pp-modal-popup-window-{{ID}} .pp-popup-header .pp-popup-title' => 'text-align: {{VALUE}};',
				),
				'condition' => array(
					'popup_title' => 'yes',
				),
			)
		);

		$this->add_control(
			'title_bg',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'.pp-modal-popup-window-{{ID}} .pp-popup-header .pp-popup-title' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'popup_title' => 'yes',
				),
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'.pp-modal-popup-window-{{ID}} .pp-popup-header .pp-popup-title' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'popup_title' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'title_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '.pp-modal-popup-window-{{ID}} .pp-popup-header .pp-popup-title',
				'condition'   => array(
					'popup_title' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'title_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'.pp-modal-popup-window-{{ID}} .pp-popup-header .pp-popup-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'popup_title' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'title_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'scheme'    => Scheme_Typography::TYPOGRAPHY_1,
				'selector'  => '.pp-modal-popup-window-{{ID}} .pp-popup-header .pp-popup-title',
				'condition' => array(
					'popup_title' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function register_style_content_controls() {
		/**
		 * Style Tab: Content
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_popup_content_style',
			array(
				'label'     => __( 'Content', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'popup_type' => 'content',
				),
			)
		);

		$this->add_responsive_control(
			'content_align',
			array(
				'label'     => __( 'Alignment', 'powerpack' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'    => array(
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'  => array(
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'   => array(
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-text-align-right',
					),
					'justify' => array(
						'title' => __( 'Justified', 'powerpack' ),
						'icon'  => 'eicon-text-align-justify',
					),
				),
				'default'   => '',
				'selectors' => array(
					'.pp-modal-popup-window-{{ID}} .pp-popup-content'   => 'text-align: {{VALUE}};',
				),
				'condition' => array(
					'popup_type' => 'content',
				),
			)
		);

		$this->add_control(
			'content_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'.pp-modal-popup-window-{{ID}} .pp-popup-content' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'popup_type' => 'content',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'content_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'scheme'    => Scheme_Typography::TYPOGRAPHY_4,
				'selector'  => '.pp-modal-popup-window-{{ID}} .pp-popup-content',
				'condition' => array(
					'popup_type' => 'content',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function register_style_trigger_icon_controls() {
		/**
		 * Style Tab: Trigger Icon
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_icon_style',
			array(
				'label'     => __( 'Trigger Icon/Image', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'trigger'       => 'on-click',
					'trigger_type!' => 'button',
				),
			)
		);

		$this->add_responsive_control(
			'icon_align',
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
					'{{WRAPPER}} .pp-modal-popup-wrap .pp-modal-popup'   => 'text-align: {{VALUE}};',
				),
				'condition' => array(
					'trigger'      => 'on-click',
					'trigger_type' => array( 'icon', 'image' ),
				),
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-trigger-icon'     => 'color: {{VALUE}}',
					'{{WRAPPER}} .pp-trigger-icon svg' => 'fill: {{VALUE}}',
				),
				'condition' => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'icon',
				),
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'      => __( 'Size', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array(
					'size' => '28',
					'unit' => 'px',
				),
				'range'      => array(
					'px' => array(
						'min'  => 10,
						'max'  => 80,
						'step' => 1,
					),
				),
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-trigger-icon' => 'font-size: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'icon',
				),
			)
		);

		$this->add_responsive_control(
			'icon_image_width',
			array(
				'label'      => __( 'Width', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min'  => 10,
						'max'  => 1200,
						'step' => 1,
					),
				),
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-trigger-image' => 'width: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'image',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function register_style_trigger_button_controls() {
		/**
		 * Style Tab: Trigger Button
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_modal_button_style',
			array(
				'label'     => __( 'Trigger Button', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'button',
					'button_text!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'button_align',
			array(
				'label'     => __( 'Alignment', 'powerpack' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'left',
				'options'   => array(
					'left'    => [
						'title' => esc_html__( 'Left', 'powerpack' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'powerpack' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'powerpack' ),
						'icon' => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => esc_html__( 'Justified', 'powerpack' ),
						'icon' => 'eicon-text-align-justify',
					],
				),
				'condition' => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'button',
					'button_text!' => '',
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
					'trigger'      => 'on-click',
					'trigger_type' => 'button',
					'button_text!' => '',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'button',
					'button_text!' => '',
				),
			)
		);

		$this->add_control(
			'button_bg_color_normal',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => [
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_4,
				],
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-modal-popup-button' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'button',
					'button_text!' => '',
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
					'{{WRAPPER}} .pp-modal-popup-button' => 'color: {{VALUE}}',
					'{{WRAPPER}} .pp-modal-popup-button svg' => 'fill: {{VALUE}}',
				),
				'condition' => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'button',
					'button_text!' => '',
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
				'selector'    => '{{WRAPPER}} .pp-modal-popup-button',
				'condition'   => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'button',
					'button_text!' => '',
				),
			)
		);

		$this->add_control(
			'button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-modal-popup-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'button',
					'button_text!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'button_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'scheme'    => Scheme_Typography::TYPOGRAPHY_4,
				'selector'  => '{{WRAPPER}} .pp-modal-popup-button',
				'condition' => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'button',
					'button_text!' => '',
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
					'{{WRAPPER}} .pp-modal-popup-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'button',
					'button_text!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'button_box_shadow',
				'selector'  => '{{WRAPPER}} .pp-modal-popup-button',
				'condition' => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'button',
					'button_text!' => '',
				),
			)
		);

		$this->add_control(
			'button_icon_heading',
			array(
				'label'     => __( 'Button Icon', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'button',
				),
			)
		);

		$this->add_responsive_control(
			'button_icon_margin',
			array(
				'label'       => __( 'Margin', 'powerpack' ),
				'type'        => Controls_Manager::DIMENSIONS,
				'size_units'  => array( 'px', '%' ),
				'placeholder' => array(
					'top'    => '',
					'right'  => '',
					'bottom' => '',
					'left'   => '',
				),
				'selectors'   => array(
					'{{WRAPPER}} .pp-modal-popup-button .pp-button-icon' => 'margin-top: {{TOP}}{{UNIT}}; margin-left: {{LEFT}}{{UNIT}}; margin-right: {{RIGHT}}{{UNIT}}; margin-bottom: {{BOTTOM}}{{UNIT}};',
				),
				'condition'   => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'button',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'button',
					'button_text!' => '',
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
					'{{WRAPPER}} .pp-modal-popup-button:hover' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'button',
					'button_text!' => '',
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
					'{{WRAPPER}} .pp-modal-popup-button:hover' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'button',
					'button_text!' => '',
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
					'{{WRAPPER}} .pp-modal-popup-button:hover' => 'border-color: {{VALUE}}',
				),
				'condition' => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'button',
					'button_text!' => '',
				),
			)
		);

		$this->add_control(
			'button_animation',
			array(
				'label'     => __( 'Animation', 'powerpack' ),
				'type'      => Controls_Manager::HOVER_ANIMATION,
				'condition' => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'button',
					'button_text!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'button_box_shadow_hover',
				'selector'  => '{{WRAPPER}} .pp-modal-popup-button:hover',
				'condition' => array(
					'trigger'      => 'on-click',
					'trigger_type' => 'button',
					'button_text!' => '',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function register_style_close_button_controls() {
		/**
		 * Style Tab: Close Button
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_close_button_style',
			array(
				'label'     => __( 'Close Button', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'close_button' => 'yes',
				),
			)
		);

		$this->add_control(
			'close_button_position',
			array(
				'label'              => __( 'Position', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'win-top-right',
				'options'            => array(
					'box-top-right' => __( 'Box - Top Right', 'powerpack' ),
					'box-top-left'  => __( 'Box - Top Left', 'powerpack' ),
					'win-top-right' => __( 'Window - Top Right', 'powerpack' ),
					'win-top-left'  => __( 'Window - Top Left', 'powerpack' ),
				),
				'frontend_available' => true,
				'condition'          => array(
					'close_button' => 'yes',
				),
			)
		);

		$this->add_control(
			'close_button_weight',
			array(
				'label'     => __( 'Weight', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'normal',
				'options'   => array(
					'normal' => __( 'Normal', 'powerpack' ),
					'bold'   => __( 'Bold', 'powerpack' ),
				),
				'condition' => array(
					'close_button' => 'yes',
				),
				'selectors' => array(
					'.pp-modal-popup-{{ID}} .pp-modal-popup-window .mfp-close' => 'font-weight: {{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'close_button_size',
			array(
				'label'      => __( 'Size', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array(
					'size' => '28',
					'unit' => 'px',
				),
				'range'      => array(
					'px' => array(
						'min'  => 10,
						'max'  => 80,
						'step' => 1,
					),
				),
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'.pp-modal-popup-{{ID}} .pp-modal-popup-window .mfp-close' => 'font-size: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'close_button' => 'yes',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_close_button_style' );

			$this->start_controls_tab(
				'tab_close_button_normal',
				array(
					'label'     => __( 'Normal', 'powerpack' ),
					'condition' => array(
						'close_button' => 'yes',
					),
				)
			);

				$this->add_control(
					'close_button_color_normal',
					array(
						'label'     => __( 'Icon Color', 'powerpack' ),
						'type'      => Controls_Manager::COLOR,
						'default'   => '',
						'selectors' => array(
							'.pp-modal-popup-{{ID}} .pp-modal-popup-window .mfp-close' => 'color: {{VALUE}}',
						),
						'condition' => array(
							'close_button' => 'yes',
						),
					)
				);

				$this->add_group_control(
					Group_Control_Background::get_type(),
					array(
						'name'      => 'close_button_bg',
						'label'     => __( 'Background', 'powerpack' ),
						'types'     => array( 'classic', 'gradient' ),
						'exclude'   => array( 'image' ),
						'selector'  => '.pp-modal-popup-{{ID}} .pp-modal-popup-window .mfp-close',
						'condition' => array(
							'close_button' => 'yes',
						),
					)
				);

				$this->add_group_control(
					Group_Control_Border::get_type(),
					array(
						'name'        => 'close_button_border_normal',
						'label'       => __( 'Border', 'powerpack' ),
						'placeholder' => '1px',
						'default'     => '1px',
						'selector'    => '.pp-modal-popup-{{ID}} .pp-modal-popup-window .mfp-close',
						'condition'   => array(
							'close_button' => 'yes',
						),
					)
				);

				$this->add_control(
					'close_button_border_radius',
					array(
						'label'      => __( 'Border Radius', 'powerpack' ),
						'type'       => Controls_Manager::DIMENSIONS,
						'size_units' => array( 'px', '%', 'em' ),
						'selectors'  => array(
							'.pp-modal-popup-{{ID}} .pp-modal-popup-window .mfp-close' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						),
						'condition'  => array(
							'close_button' => 'yes',
						),
					)
				);

				$this->add_responsive_control(
					'close_button_margin',
					array(
						'label'       => __( 'Margin', 'powerpack' ),
						'type'        => Controls_Manager::DIMENSIONS,
						'size_units'  => array( 'px', '%' ),
						'placeholder' => array(
							'top'    => '',
							'right'  => '',
							'bottom' => '',
							'left'   => '',
						),
						'selectors'   => array(
							'.pp-modal-popup-{{ID}} .pp-modal-popup-window .mfp-close' => 'margin-top: {{TOP}}{{UNIT}}; margin-left: {{LEFT}}{{UNIT}}; margin-right: {{RIGHT}}{{UNIT}}; margin-bottom: {{BOTTOM}}{{UNIT}};',
						),
						'condition'   => array(
							'close_button' => 'yes',
						),
					)
				);

				$this->add_responsive_control(
					'close_button_padding',
					array(
						'label'       => __( 'Padding', 'powerpack' ),
						'type'        => Controls_Manager::DIMENSIONS,
						'size_units'  => array( 'px', '%' ),
						'placeholder' => array(
							'top'    => '',
							'right'  => '',
							'bottom' => '',
							'left'   => '',
						),
						'selectors'   => array(
							'.pp-modal-popup-{{ID}} .pp-modal-popup-window .mfp-close' => 'padding-top: {{TOP}}{{UNIT}}; padding-left: {{LEFT}}{{UNIT}}; padding-right: {{RIGHT}}{{UNIT}}; padding-bottom: {{BOTTOM}}{{UNIT}};',
						),
						'condition'   => array(
							'close_button' => 'yes',
						),
					)
				);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'tab_close_button_hover',
				array(
					'label'     => __( 'Hover', 'powerpack' ),
					'condition' => array(
						'close_button' => 'yes',
					),
				)
			);

				$this->add_control(
					'close_button_color_hover',
					array(
						'label'     => __( 'Icon Color', 'powerpack' ),
						'type'      => Controls_Manager::COLOR,
						'default'   => '',
						'selectors' => array(
							'.pp-modal-popup-{{ID}} .pp-modal-popup-window .mfp-close:hover' => 'color: {{VALUE}}',
						),
						'condition' => array(
							'close_button' => 'yes',
						),
					)
				);

				$this->add_group_control(
					Group_Control_Background::get_type(),
					array(
						'name'      => 'close_button_bg_hover',
						'label'     => __( 'Background', 'powerpack' ),
						'types'     => array( 'classic', 'gradient' ),
						'selector'  => '.pp-modal-popup-{{ID}} .pp-modal-popup-window .mfp-close:hover',
						'condition' => array(
							'close_button' => 'yes',
						),
					)
				);

				$this->add_control(
					'close_button_border_hover',
					array(
						'label'     => __( 'Border Color', 'powerpack' ),
						'type'      => Controls_Manager::COLOR,
						'default'   => '',
						'selectors' => array(
							'.pp-modal-popup-{{ID}} .pp-modal-popup-window .mfp-close:hover' => 'border-color: {{VALUE}}',
						),
						'condition' => array(
							'close_button' => 'yes',
						),
					)
				);

				$this->add_control(
					'close_button_border_radius_hover',
					array(
						'label'      => __( 'Border Radius', 'powerpack' ),
						'type'       => Controls_Manager::DIMENSIONS,
						'size_units' => array( 'px', '%', 'em' ),
						'selectors'  => array(
							'.pp-modal-popup-{{ID}} .pp-modal-popup-window .mfp-close:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						),
						'condition'  => array(
							'close_button' => 'yes',
						),
					)
				);

			$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

	}

	/**
	 * Render popup box button icon output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_button_icon() {
		$settings = $this->get_settings_for_display();

		// Trigger Button Icon
		if ( ! isset( $settings['button_icon'] ) && ! Icons_Manager::is_migration_allowed() ) {
			// add old default
			$settings['button_icon'] = '';
		}

		$has_icon = ! empty( $settings['button_icon'] );

		if ( $has_icon ) {
			$this->add_render_attribute( 'i', 'class', $settings['button_icon'] );
			$this->add_render_attribute( 'i', 'aria-hidden', 'true' );
		}

		if ( ! $has_icon && ! empty( $settings['select_button_icon']['value'] ) ) {
			$has_icon = true;
		}
		$migrated = isset( $settings['__fa4_migrated']['select_button_icon'] );
		$is_new   = ! isset( $settings['button_icon'] ) && Icons_Manager::is_migration_allowed();

		if ( $has_icon ) {
			?>
			<span class="pp-button-icon pp-icon">
				<?php
				if ( $is_new || $migrated ) {
					Icons_Manager::render_icon( $settings['select_button_icon'], array( 'aria-hidden' => 'true' ) );
				} elseif ( ! empty( $settings['button_icon'] ) ) {
					?>
					<i <?php echo wp_kses_post( $this->get_render_attribute_string( 'i' ) ); ?>></i>
					<?php
				}
				?>
			</span>
			<?php
		}
	}

	/**
	 * Render popup box button output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_trigger_button() {
		$settings  = $this->get_settings_for_display();
		$elementor = \Elementor\Plugin::instance();

		$this->add_render_attribute( 'button-wrap', 'class', 'pp-modal-popup-button-wrap' );

		if ( ! empty( $settings['button_align'] ) ) {
			$this->add_render_attribute( 'button-wrap', 'class', 'elementor-align-' . $settings['button_align'] );
		}

		if ( pp_has_custom_breakpoints() ) {
			$breakpoints = pp_get_breakpoints_config();

			foreach ( $breakpoints as $breakpoint_name => $breakpoint ) :

				if ( isset( $breakpoints[$breakpoint_name]['value'] ) && true === $breakpoints[$breakpoint_name]['is_enabled'] ) {

					if ( isset( $settings['button_align_' . $breakpoint_name] ) && ! empty( $settings['button_align_' . $breakpoint_name] ) ) {
						$alignment = $settings['button_align_' . $breakpoint_name];

						$this->add_render_attribute( 'button-wrap', 'class', 'elementor-' . $breakpoint_name .'-align-' . $alignment );
					}
				}
			endforeach;
		} else {
			if ( ! empty( $settings['button_align_tablet'] ) ) {
				$this->add_render_attribute( 'button-wrap', 'class', 'elementor-tablet-align-' . $settings['button_align_tablet'] );
			}

			if ( ! empty( $settings['button_align_mobile'] ) ) {
				$this->add_render_attribute( 'button-wrap', 'class', 'elementor-mobile-align-' . $settings['button_align_mobile'] );
			}
		}

		$this->add_render_attribute(
			'button',
			'class',
			array(
				'pp-modal-popup-button',
				'pp-modal-popup-link',
				'pp-modal-popup-link-' . esc_attr( $this->get_id() ),
				'elementor-button',
				'elementor-size-' . $settings['button_size'],
			)
		);

		if ( $settings['button_animation'] ) {
			$this->add_render_attribute( 'button', 'class', 'elementor-animation-' . $settings['button_animation'] );
		}

		echo '<div ' . wp_kses_post( $this->get_render_attribute_string( 'button-wrap' ) ) . '>';
			echo '<span ' . wp_kses_post( $this->get_render_attribute_string( 'button' ) ) . '>';

			if ( 'before' === $settings['button_icon_position'] ) {
				$this->render_button_icon();
			}

			if ( ! empty( $settings['button_text'] ) ) {
				printf( '<span %1$s>', wp_kses_post( $this->get_render_attribute_string( 'button_text' ) ) );
					echo esc_attr( $settings['button_text'] );
				printf( '</span>' );
			}

			if ( 'after' === $settings['button_icon_position'] ) {
				$this->render_button_icon();
			}

			echo '</span>';
		echo '</div>';
	}

	/**
	 * Render popup box button icon output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_trigger_icon() {
		$settings = $this->get_settings_for_display();

		// Trigger Button Icon
		if ( ! isset( $settings['trigger_icon'] ) && ! Icons_Manager::is_migration_allowed() ) {
			// add old default
			$settings['trigger_icon'] = '';
		}

		$has_icon = ! empty( $settings['trigger_icon'] );

		if ( $has_icon ) {
			$this->add_render_attribute( 'trigger-i', 'class', $settings['trigger_icon'] );
			$this->add_render_attribute( 'trigger-i', 'aria-hidden', 'true' );
		}

		if ( ! $has_icon && ! empty( $settings['select_trigger_icon']['value'] ) ) {
			$has_icon = true;
		}
		$migrated = isset( $settings['__fa4_migrated']['select_trigger_icon'] );
		$is_new   = ! isset( $settings['trigger_icon'] ) && Icons_Manager::is_migration_allowed();

		if ( $has_icon ) {
			?>
			<span class="pp-trigger-icon pp-icon pp-modal-popup-link pp-modal-popup-link-<?php echo esc_attr( $this->get_id() ); ?>">
				<?php
				if ( $is_new || $migrated ) {
					Icons_Manager::render_icon( $settings['select_trigger_icon'], array( 'aria-hidden' => 'true' ) );
				} elseif ( ! empty( $settings['trigger_icon'] ) ) {
					?>
					<i <?php echo wp_kses_post( $this->get_render_attribute_string( 'trigger-i' ) ); ?>></i>
					<?php
				}
				?>
			</span>
			<?php
		}
	}

	/**
	 * Render popup box widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'modal-popup', 'class', 'pp-modal-popup' );

		$is_editor = \Elementor\Plugin::instance()->editor->is_edit_mode();

		if ( 'template' === $settings['popup_type'] || 'image' === $settings['popup_type'] || 'content' === $settings['popup_type'] || 'custom-html' === $settings['popup_type'] ) {
			$this->add_render_attribute( 'modal-popup', 'data-type', 'inline' );
		} elseif ( 'link' === $settings['popup_type'] ) {
			$this->add_render_attribute( 'modal-popup', 'data-type', 'iframe' );
		} else {
			$this->add_render_attribute( 'modal-popup', 'data-type', $settings['popup_type'] );
		}

		if ( 'link' === $settings['popup_type'] ) {
			$this->add_render_attribute( 'modal-popup', 'data-src', $settings['popup_link']['url'] );
			$this->add_render_attribute( 'modal-popup', 'data-iframe-class', 'pp-modal-popup-window pp-modal-popup-window-' . esc_attr( $this->get_id() ) );
		} else {
			$this->add_render_attribute( 'modal-popup', 'data-src', '#pp-modal-popup-window-' . esc_attr( $this->get_id() ) );
		}

		// Trigger
		if ( 'other' !== $settings['trigger'] ) {
			$this->add_render_attribute( 'modal-popup', 'data-trigger-element', '.pp-modal-popup-link-' . esc_attr( $this->get_id() ) );
		}

		if ( 'page-load' === $settings['trigger'] ) {
			$delay = 1000;
			if ( '' !== $settings['delay'] ) {
				$delay = $settings['delay'] * 1000;
			}
			$this->add_render_attribute( 'modal-popup', 'data-delay', $delay );

			if ( '' !== $settings['display_after_page_load'] ) {
				$this->add_render_attribute( 'modal-popup', 'data-display-after', $settings['display_after_page_load'] );
			}
		} elseif ( 'exit-intent' === $settings['trigger'] ) {
			if ( '' !== $settings['display_after_exit_intent'] ) {
				$this->add_render_attribute( 'modal-popup', 'data-display-after', $settings['display_after_exit_intent'] );
			}
		} elseif ( 'other' === $settings['trigger'] ) {
			if ( '' !== $settings['element_identifier'] ) {
				$this->add_render_attribute( 'modal-popup', 'data-trigger-element', $settings['element_identifier'] );
			}
		}

		if ( 'yes' === $settings['enable_url_trigger'] ) {
			if ( '' !== $settings['element_url_identifier'] ) {
				$this->add_render_attribute( 'modal-popup', 'data-url-identifier', esc_attr( $settings['element_url_identifier'] ) );
			}
		}

		$elementor_bp_tablet = get_option( 'elementor_viewport_lg' );
		$elementor_bp_mobile = get_option( 'elementor_viewport_md' );
		$bp_tablet           = ! empty( $elementor_bp_tablet ) ? $elementor_bp_tablet : 1025;
		$bp_mobile           = ! empty( $elementor_bp_mobile ) ? $elementor_bp_mobile : 768;

		if ( 'tablet' === $settings['popup_disable_on'] ) {
			$popup_disable_on = $bp_tablet;
		} elseif ( 'mobile' === $settings['popup_disable_on'] ) {
			$popup_disable_on = $bp_mobile;
		} else {
			$popup_disable_on = '';
		}

		if ( $popup_disable_on ) {
			$this->add_render_attribute( 'modal-popup', 'data-disable-on', $popup_disable_on );
		}

		// Popup Window
		$this->add_render_attribute(
			'modal-popup-window',
			array(
				'class' => array( 'pp-modal-popup-window pp-modal-popup-window-' . esc_attr( $this->get_id() ) ),
				'id'    => 'pp-modal-popup-window-' . esc_attr( $this->get_id() ),
			)
		);

		$this->add_render_attribute(
			'popup-wrap',
			array(
				'class' => 'pp-modal-popup-wrap',
				'id'    => 'pp-modal-popup-wrap-' . esc_attr( $this->get_id() ),
			)
		);

		if ( $is_editor && 'yes' === $settings['preview_popup'] ) {
			$this->add_render_attribute( 'popup-wrap', 'class', 'pp-popup-preview' );
		}
		?>
		<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'popup-wrap' ) ); ?>>
			<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'modal-popup' ) ); ?>>
				<?php
				if ( 'on-click' === $settings['trigger'] ) {
					if ( 'button' === $settings['trigger_type'] ) {

						$this->render_trigger_button();

					} elseif ( 'icon' === $settings['trigger_type'] ) {

						$this->render_trigger_icon();

					} elseif ( 'image' === $settings['trigger_type'] ) {

						$trigger_image = $this->get_settings_for_display( 'trigger_image' );
						if ( ! empty( $trigger_image['url'] ) ) {
							printf( '<img class="pp-trigger-image pp-modal-popup-link %1$s" src="%2$s">', 'pp-modal-popup-link-' . esc_attr( $this->get_id() ), esc_url( $trigger_image['url'] ) );
						}

					}
				} else {
					if ( $is_editor ) {
						?>
						<div class="pp-editor-message" style="text-align: center;">
							<h5>
							<?php printf( 'Modal Popup ID - %1$s', esc_attr( $this->get_id() ) ); ?>
							</h5>
							<p>
							<?php esc_attr_e( 'Click here to edit the "Popup Box" settings. This text will not be visible on frontend.', 'powerpack' ); ?>
							</p>
						</div>
						<?php
					}
				}
				?>
			</div>
		</div>
		<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'modal-popup-window' ) ); ?>>
				<?php if ( 'yes' === $settings['popup_title'] && '' !== $settings['title'] ) { ?>
					<div class="pp-popup-header">
						<h2 class="pp-popup-title">
							<?php echo wp_kses_post( $settings['title'] ); ?>
						</h2>
					</div>
					<?php
				}
				echo '<div class="pp-popup-content" id="pp-popup-content">';
				if ( 'image' === $settings['popup_type'] ) {
					$image = $this->get_settings_for_display( 'image' );

					$this->add_link_attributes( 'image-link', $settings['popup_image_link'] );

					if ( $settings['popup_image_link']['url'] ) {
						echo '<a ' . wp_kses_post( $this->get_render_attribute_string( 'image-link' ) ) . '><img src="' . esc_url( $image['url'] ) . '"></a>';
					} else {
						echo '<img src="' . esc_url( $image['url'] ) . '">';
					}

				} elseif ( 'content' === $settings['popup_type'] ) {
					global $wp_embed;

					$content = wpautop( $wp_embed->autoembed( $settings['content'] ) ); // Get content HTML
					echo do_shortcode( $content ); // Process code for shortcode and then output it
				} elseif ( 'template' === $settings['popup_type'] ) {
					if ( ! empty( $settings['templates'] ) ) {
						$template_id = $settings['templates'];

						echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
				} elseif ( 'custom-html' === $settings['popup_type'] ) {
					echo wp_kses_post( $settings['custom_html'] );
				} else {
					echo '';
				}
				echo '</div>';
				?>
		</div>
		<?php
	}
}
