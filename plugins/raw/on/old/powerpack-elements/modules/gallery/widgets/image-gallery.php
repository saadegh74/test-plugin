<?php
namespace PowerpackElements\Modules\Gallery\Widgets;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Modules\Gallery\Module;
use PowerpackElements\Classes\PP_Config;

// Elementor Classes.
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Control_Media;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Core\Schemes\Typography as Scheme_Typography;
use Elementor\Core\Schemes\Color as Scheme_Color;
use Elementor\Modules\DynamicTags\Module as TagsModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Image Gallery Widget
 */
class Image_Gallery extends Powerpack_Widget {

	/**
	 * Retrieve image gallery widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Image_Gallery' );
	}

	/**
	 * Retrieve image gallery widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Image_Gallery' );
	}

	/**
	 * Retrieve image gallery widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Image_Gallery' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the Image Gallery widget belongs to.
	 *
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'Image_Gallery' );
	}

	/**
	 * Retrieve the list of scripts the image gallery widget depended on.
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
				'isotope',
				'imagesloaded',
				'jquery-fancybox',
				'tilt',
				'pp-justified-gallery',
				'powerpack-frontend',
			];
		}

		$settings = $this->get_settings_for_display();
		$scripts = [];

		if ( 'masonry' === $settings['layout'] || 'yes' === $settings['filter_enable'] || 'yes' === $settings['pagination'] ) {
			array_push( $scripts, 'isotope', 'imagesloaded', 'powerpack-frontend' );
		}

		if ( 'file' === $settings['link_to'] && 'no' !== $settings['open_lightbox'] && 'fancybox' === $settings['lightbox_library'] ) {
			array_push( $scripts, 'jquery-fancybox', 'powerpack-frontend' );
		}

		if ( 'yes' === $settings['tilt'] ) {
			array_push( $scripts, 'tilt', 'powerpack-frontend' );
		}

		if ( 'justified' === $settings['layout'] ) {
			array_push( $scripts, 'pp-justified-gallery', 'imagesloaded', 'powerpack-frontend' );
		}

		return $scripts;
	}

	/**
	 * Retrieve the list of styles the image gallery widget depended on.
	 *
	 * Used to set styles dependencies required to run the widget.
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_style_depends() {
		return array(
			'fancybox',
		);
	}

	/**
	 * Image filters.
	 *
	 * @access public
	 * @param boolean $inherit if inherit option required.
	 * @return array Filters.
	 */
	protected function image_filters( $inherit = false ) {

		$inherit_opt = array();

		if ( $inherit ) {
			$inherit_opt = array(
				'' => __( 'Inherit', 'powerpack' ),
			);
		}

		$image_filters = array(
			'normal'           => __( 'Normal', 'powerpack' ),
			'filter-1977'      => __( '1977', 'powerpack' ),
			'filter-aden'      => __( 'Aden', 'powerpack' ),
			'filter-amaro'     => __( 'Amaro', 'powerpack' ),
			'filter-ashby'     => __( 'Ashby', 'powerpack' ),
			'filter-brannan'   => __( 'Brannan', 'powerpack' ),
			'filter-brooklyn'  => __( 'Brooklyn', 'powerpack' ),
			'filter-charmes'   => __( 'Charmes', 'powerpack' ),
			'filter-clarendon' => __( 'Clarendon', 'powerpack' ),
			'filter-crema'     => __( 'Crema', 'powerpack' ),
			'filter-dogpatch'  => __( 'Dogpatch', 'powerpack' ),
			'filter-earlybird' => __( 'Earlybird', 'powerpack' ),
			'filter-gingham'   => __( 'Gingham', 'powerpack' ),
			'filter-ginza'     => __( 'Ginza', 'powerpack' ),
			'filter-hefe'      => __( 'Hefe', 'powerpack' ),
			'filter-helena'    => __( 'Helena', 'powerpack' ),
			'filter-hudson'    => __( 'Hudson', 'powerpack' ),
			'filter-inkwell'   => __( 'Inkwell', 'powerpack' ),
			'filter-juno'      => __( 'Juno', 'powerpack' ),
			'filter-kelvin'    => __( 'Kelvin', 'powerpack' ),
			'filter-lark'      => __( 'Lark', 'powerpack' ),
			'filter-lofi'      => __( 'Lofi', 'powerpack' ),
			'filter-ludwig'    => __( 'Ludwig', 'powerpack' ),
			'filter-maven'     => __( 'Maven', 'powerpack' ),
			'filter-mayfair'   => __( 'Mayfair', 'powerpack' ),
			'filter-moon'      => __( 'Moon', 'powerpack' ),
		);

		return array_merge( $inherit_opt, $image_filters );
	}

	/**
	 * Register image gallery widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 2.0.3
	 * @access protected
	 */
	protected function register_controls() {
		/* Content Tab */
		$this->register_content_gallery_controls();
		$this->register_content_filter_controls();
		$this->register_content_settings_controls();
		$this->register_content_pagination_controls();
		$this->register_content_help_docs_controls();

		/* Style Tab */
		$this->register_style_layout_controls();
		$this->register_style_thumbnails_controls();
		$this->register_style_caption_controls();
		$this->register_style_link_icon_controls();
		$this->register_style_overlay_controls();
		$this->register_style_lightbox_controls();
		$this->register_style_filter_controls();
		$this->register_style_pagination_controls();
	}

	/**
	 * Register gallery controls for content tab
	 */
	protected function register_content_gallery_controls() {
		/**
		 * Content Tab: Gallery
		 */
		$this->start_controls_section(
			'section_gallery',
			array(
				'label' => __( 'Gallery', 'powerpack' ),
			)
		);

		$this->add_control(
			'gallery_type',
			array(
				'label'   => __( 'Gallery Type', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'filterable',
				'options' => array(
					'standard'   => __( 'Standard', 'powerpack' ),
					'filterable' => __( 'Filterable', 'powerpack' ),
				),
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'image_group',
			array(
				'label'   => __( 'Add Images', 'powerpack' ),
				'type'    => Controls_Manager::GALLERY,
				'dynamic' => array(
					'active' => true,
				),
			)
		);

		$repeater->add_control(
			'filter_label',
			array(
				'label'       => __( 'Filter Label', 'powerpack' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => '',
				'dynamic'     => array(
					'active' => true,
				),
			)
		);

		$repeater->add_control(
			'filter_id',
			array(
				'label'       => __( 'Filter ID', 'powerpack' ),
				'description' => __( 'To filter the gallery using URL parameters, specify an ID here', 'powerpack' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => '',
			)
		);

		$this->add_control(
			'gallery_images',
			array(
				'label'       => __( 'Gallery Images', 'powerpack' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '',
				'separator'   => 'before',
				'condition'   => array(
					'gallery_type' => 'filterable',
				),
			)
		);

		$this->add_control(
			'image_group_standard',
			array(
				'label'     => __( 'Add Images', 'powerpack' ),
				'type'      => Controls_Manager::GALLERY,
				'dynamic'   => array(
					'active' => true,
				),
				'separator' => 'before',
				'condition' => array(
					'gallery_type' => 'standard',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register filter controls for content tab
	 */
	protected function register_content_filter_controls() {
		/**
		 * Content Tab: Filter
		 */
		$this->start_controls_section(
			'section_filter',
			array(
				'label'     => __( 'Filter', 'powerpack' ),
				'condition' => array(
					'gallery_type' => 'filterable',
				),
			)
		);

		$this->add_control(
			'filter_enable',
			array(
				'label'     => __( 'Enable Filter', 'powerpack' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => '',
				'condition' => array(
					'gallery_type' => 'filterable',
				),
			)
		);

		$this->add_control(
			'filter_all_label',
			array(
				'label'     => __( '"All" Filter Label', 'powerpack' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'All', 'powerpack' ),
				'condition' => array(
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
				),
			)
		);

		$this->add_control(
			'default_filter_select',
			array(
				'label'   => __( 'Default Filter', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'first',
				'options' => array(
					'first'  => __( 'First', 'powerpack' ),
					'custom' => __( 'Custom', 'powerpack' ),
				),
				'condition'    => array(
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
				),
			)
		);

		$this->add_control(
			'default_filter',
			array(
				'label'     => __( 'Default Filter Name', 'powerpack' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '',
				'condition' => array(
					'gallery_type'          => 'filterable',
					'filter_enable'         => 'yes',
					'default_filter_select' => 'custom',
				),
			)
		);

		$this->add_control(
			'responsive_support',
			array(
				'label'       => __( 'Responsive Support', 'powerpack' ),
				'description' => __( 'Enable this option to display filters in a dropdown on responsive devices.', 'powerpack' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'none',
				'options'     => array(
					'none'   => __( 'None', 'powerpack' ),
					'tablet' => __( 'Tablet & Mobile', 'powerpack' ),
					'mobile' => __( 'Mobile', 'powerpack' ),
				),
				'condition'   => array(
					'gallery_type'          => 'filterable',
					'filter_enable'         => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'filter_alignment',
			array(
				'label'       => __( 'Align', 'powerpack' ),
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
					'{{WRAPPER}} .pp-gallery-filters' => 'text-align: {{VALUE}};',
				),
				'condition'   => array(
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
				),
			)
		);

		$this->add_control(
			'pointer',
			[
				'label'          => __( 'Pointer', 'powerpack' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => 'underline',
				'options'        => [
					'none'        => __( 'None', 'powerpack' ),
					'underline'   => __( 'Underline', 'powerpack' ),
					'overline'    => __( 'Overline', 'powerpack' ),
					'double-line' => __( 'Double Line', 'powerpack' ),
					'framed'      => __( 'Framed', 'powerpack' ),
					'background'  => __( 'Background', 'powerpack' ),
					'text'        => __( 'Text', 'powerpack' ),
				],
				'style_transfer' => true,
				'condition'      => array(
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
				),
			]
		);

		$this->add_control(
			'animation_line',
			[
				'label'     => __( 'Animation', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'fade',
				'options'   => [
					'fade'     => 'Fade',
					'slide'    => 'Slide',
					'grow'     => 'Grow',
					'drop-in'  => 'Drop In',
					'drop-out' => 'Drop Out',
					'none'     => 'None',
				],
				'condition' => [
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
					'pointer'       => [ 'underline', 'overline', 'double-line' ],
				],
			]
		);

		$this->add_control(
			'animation_framed',
			[
				'label'     => __( 'Animation', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'fade',
				'options'   => [
					'fade'    => 'Fade',
					'grow'    => 'Grow',
					'shrink'  => 'Shrink',
					'draw'    => 'Draw',
					'corners' => 'Corners',
					'none'    => 'None',
				],
				'condition' => [
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
					'pointer'       => 'framed',
				],
			]
		);

		$this->add_control(
			'animation_background',
			[
				'label'     => __( 'Animation', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'fade',
				'options'   => [
					'fade' => 'Fade',
					'grow' => 'Grow',
					'shrink' => 'Shrink',
					'sweep-left' => 'Sweep Left',
					'sweep-right' => 'Sweep Right',
					'sweep-up' => 'Sweep Up',
					'sweep-down' => 'Sweep Down',
					'shutter-in-vertical' => 'Shutter In Vertical',
					'shutter-out-vertical' => 'Shutter Out Vertical',
					'shutter-in-horizontal' => 'Shutter In Horizontal',
					'shutter-out-horizontal' => 'Shutter Out Horizontal',
					'none' => 'None',
				],
				'condition' => [
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
					'pointer'       => 'background',
				],
			]
		);

		$this->add_control(
			'animation_text',
			[
				'label'     => __( 'Animation', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'grow',
				'options'   => [
					'grow'   => 'Grow',
					'shrink' => 'Shrink',
					'sink'   => 'Sink',
					'float'  => 'Float',
					'skew'   => 'Skew',
					'rotate' => 'Rotate',
					'none'   => 'None',
				],
				'condition' => [
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
					'pointer'       => 'text',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_settings_controls() {
		/**
		 * Content Tab: Settings
		 */
		$this->start_controls_section(
			'section_settings',
			array(
				'label' => __( 'Settings', 'powerpack' ),
			)
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'    => 'image',
				'label'   => __( 'Image Size', 'powerpack' ),
				'default' => 'full',
				'exclude' => array( 'custom' ),
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => array(
					'grid'      => __( 'Grid', 'powerpack' ),
					'masonry'   => __( 'Masonry', 'powerpack' ),
					'justified' => __( 'Justified', 'powerpack' ),
				),
			)
		);

		$this->add_control(
			'row_height',
			array(
				'label'     => __( 'Row Height', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 50,
						'max' => 500,
					),
				),
				'default'   => array(
					'size' => 120,
				),
				'condition' => array(
					'layout' => 'justified',
				),
			)
		);

		$this->add_control(
			'last_row',
			array(
				'label'     => __( 'Last Row', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'justify',
				'options'   => array(
					'justify'   => __( 'Justify', 'powerpack' ),
					'nojustify' => __( 'No Justify', 'powerpack' ),
					'hide'      => __( 'Hide', 'powerpack' ),
				),
				'condition' => array(
					'layout' => 'justified',
				),
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'              => __( 'Columns', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => '3',
				'tablet_default'     => '2',
				'mobile_default'     => '1',
				'options'            => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
					'7' => '7',
					'8' => '8',
				),
				'prefix_class'       => 'elementor-grid%s-',
				'frontend_available' => true,
				'condition'          => array(
					'layout!' => 'justified',
				),
			)
		);

		$this->add_control(
			'caption',
			array(
				'label'   => __( 'Caption', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'show',
				'options' => array(
					'show' => __( 'Show', 'powerpack' ),
					'hide' => __( 'Hide', 'powerpack' ),
				),
			)
		);

		$this->add_control(
			'caption_type',
			array(
				'label'     => __( 'Caption Type', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'caption',
				'options'   => array(
					'title'       => __( 'Title', 'powerpack' ),
					'caption'     => __( 'Caption', 'powerpack' ),
					'description' => __( 'Description', 'powerpack' ),
				),
				'condition' => array(
					'caption' => 'show',
				),
			)
		);

		$this->add_control(
			'caption_position',
			array(
				'label'     => __( 'Caption Position', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'over_image',
				'options'   => array(
					'over_image'  => __( 'Over Image', 'powerpack' ),
					'below_image' => __( 'Below Image', 'powerpack' ),
				),
				'condition' => array(
					'layout!' => 'justified',
					'caption' => 'show',
				),
			)
		);

		$this->add_control(
			'ordering',
			array(
				'label'   => __( 'Ordering', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''       => __( 'Default', 'powerpack' ),
					'date'   => __( 'Date', 'powerpack' ),
					'random' => __( 'Random', 'powerpack' ),
				),
			)
		);

		$this->add_control(
			'link_to',
			array(
				'label'   => __( 'Link to', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'file',
				'options' => array(
					'none'       => __( 'None', 'powerpack' ),
					'file'       => __( 'Media File', 'powerpack' ),
					'custom'     => __( 'Custom URL', 'powerpack' ),
					'attachment' => __( 'Attachment Page', 'powerpack' ),
				),
			)
		);

		$this->add_control(
			'important_note',
			array(
				'label'           => '',
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'Add custom link in media uploader.', 'powerpack' ),
				'content_classes' => 'pp-editor-info',
				'condition'       => array(
					'link_to' => 'custom',
				),
			)
		);

		$this->add_control(
			'link_target',
			array(
				'label'      => __( 'Link Target', 'powerpack' ),
				'type'       => Controls_Manager::SELECT,
				'default'    => '_blank',
				'options'    => array(
					'_self'  => __( 'Same Window', 'powerpack' ),
					'_blank' => __( 'New Window', 'powerpack' ),
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'link_to',
							'operator' => '==',
							'value'    => 'custom',
						),
						array(
							'name'     => 'link_to',
							'operator' => '==',
							'value'    => 'attachment',
						),
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'link_to',
									'operator' => '==',
									'value'    => 'file',
								),
								array(
									'name'     => 'open_lightbox',
									'operator' => '==',
									'value'    => 'no',
								),
							),
						),
					),
				),
			)
		);

		$this->add_control(
			'select_link_icon',
			array(
				'label'            => __( 'Link Icon', 'powerpack' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'link_icon',
				'condition'        => array(
					'link_to!' => 'none',
				),
			)
		);

		$this->add_control(
			'open_lightbox',
			array(
				'label'     => __( 'Lightbox', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'default',
				'options'   => array(
					'default' => __( 'Default', 'powerpack' ),
					'yes'     => __( 'Yes', 'powerpack' ),
					'no'      => __( 'No', 'powerpack' ),
				),
				'separator' => 'before',
				'condition' => array(
					'link_to' => 'file',
				),
			)
		);

		$this->add_control(
			'lightbox_library',
			array(
				'label'              => __( 'Lightbox Library', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => '',
				'options'            => array(
					''         => __( 'Elementor', 'powerpack' ),
					'fancybox' => __( 'Fancybox', 'powerpack' ),
				),
				'frontend_available' => true,
				'condition'          => array(
					'link_to'        => 'file',
					'open_lightbox!' => 'no',
				),
			)
		);

		$this->add_control(
			'lightbox_caption',
			array(
				'type'      => Controls_Manager::SELECT,
				'label'     => __( 'Lightbox Caption', 'powerpack' ),
				'default'   => '',
				'options'   => array(
					''            => __( 'None', 'powerpack' ),
					'caption'     => __( 'Caption', 'powerpack' ),
					'title'       => __( 'Title', 'powerpack' ),
					'description' => __( 'Description', 'powerpack' ),
				),
				'condition' => array(
					'link_to'        => 'file',
					'open_lightbox!' => 'no',
				),
			)
		);

		$this->add_control(
			'loop',
			array(
				'label'              => __( 'Loop', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'label_on'           => __( 'Yes', 'powerpack' ),
				'label_off'          => __( 'No', 'powerpack' ),
				'return_value'       => 'yes',
				'condition'          => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'arrows',
			array(
				'label'              => __( 'Arrows', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'label_on'           => __( 'Yes', 'powerpack' ),
				'label_off'          => __( 'No', 'powerpack' ),
				'return_value'       => 'yes',
				'condition'          => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'slides_counter',
			array(
				'label'              => __( 'Slides Counter', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'label_on'           => __( 'Yes', 'powerpack' ),
				'label_off'          => __( 'No', 'powerpack' ),
				'return_value'       => 'yes',
				'condition'          => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'keyboard',
			array(
				'label'              => __( 'Keyboard Navigation', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'label_on'           => __( 'Yes', 'powerpack' ),
				'label_off'          => __( 'No', 'powerpack' ),
				'return_value'       => 'yes',
				'condition'          => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'toolbar',
			array(
				'label'              => __( 'Toolbar', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'label_on'           => __( 'Yes', 'powerpack' ),
				'label_off'          => __( 'No', 'powerpack' ),
				'return_value'       => 'yes',
				'condition'          => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'toolbar_buttons',
			array(
				'label'              => __( 'Toolbar Buttons', 'powerpack' ),
				'type'               => Controls_Manager::SELECT2,
				'default'            => array( 'zoom', 'slideShow', 'thumbs', 'close' ),
				'options'            => array(
					'zoom'       => __( 'Zoom', 'powerpack' ),
					'share'      => __( 'Share', 'powerpack' ),
					'slideShow'  => __( 'SlideShow', 'powerpack' ),
					'fullScreen' => __( 'Full Screen', 'powerpack' ),
					'download'   => __( 'Download', 'powerpack' ),
					'thumbs'     => __( 'Thumbs', 'powerpack' ),
					'close'      => __( 'Close', 'powerpack' ),
				),
				'multiple'           => true,
				'condition'          => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
					'toolbar'          => 'yes',
				),
			)
		);

		$this->add_control(
			'thumbs_auto_start',
			array(
				'label'              => __( 'Thumbs Auto Start', 'powerpack' ),
				'description'        => __( 'Display thumbnails on lightbox opening', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => '',
				'label_on'           => __( 'Yes', 'powerpack' ),
				'label_off'          => __( 'No', 'powerpack' ),
				'return_value'       => 'yes',
				'condition'          => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'thumbs_position',
			array(
				'label'              => __( 'Thumbs Position', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => '',
				'options'            => array(
					''       => __( 'Default', 'powerpack' ),
					'bottom' => __( 'Bottom', 'powerpack' ),
				),
				'condition'          => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'lightbox_animation',
			array(
				'label'              => __( 'Animation', 'powerpack' ),
				'description'        => __( 'Open/Close animation', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'zoom',
				'options'            => array(
					''            => __( 'None', 'powerpack' ),
					'fade'        => __( 'Fade', 'powerpack' ),
					'zoom'        => __( 'Zoom', 'powerpack' ),
					'zoom-in-out' => __( 'Zoom in Out', 'powerpack' ),
				),
				'condition'          => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'transition_effect',
			array(
				'label'              => __( 'Transition Effect', 'powerpack' ),
				'description'        => __( 'Transition effect between slides', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'fade',
				'options'            => array(
					''            => __( 'None', 'powerpack' ),
					'fade'        => __( 'Fade', 'powerpack' ),
					'slide'       => __( 'Slide', 'powerpack' ),
					'circular'    => __( 'Circular', 'powerpack' ),
					'tube'        => __( 'Tube', 'powerpack' ),
					'zoom-in-out' => __( 'Zoom in Out', 'powerpack' ),
					'rotate'      => __( 'Rotate', 'powerpack' ),
				),
				'condition'          => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'global_lightbox',
			array(
				'type'        => Controls_Manager::SELECT,
				'label'       => __( 'Global Lightbox', 'powerpack' ),
				'description' => __( 'Enabling this option will show images from all image gallery widgets in lightbox', 'powerpack' ),
				'default'     => 'no',
				'options'     => array(
					'yes' => __( 'Yes', 'powerpack' ),
					'no'  => __( 'No', 'powerpack' ),
				),
				'condition'   => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'tilt',
			array(
				'label'     => __( 'Tilt', 'powerpack' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => '',
				'separator' => 'before',
			)
		);

		$this->add_control(
			'tilt_axis',
			array(
				'label'     => __( 'Axis', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array(
					''  => __( 'Both', 'powerpack' ),
					'x' => __( 'X Axis', 'powerpack' ),
					'y' => __( 'Y Axis', 'powerpack' ),
				),
				'condition' => array(
					'tilt' => 'yes',
				),
			)
		);

		$this->add_control(
			'tilt_amount',
			array(
				'label'     => __( 'Amount', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 10,
						'max' => 50,
					),
				),
				'default'   => array(
					'size' => 20,
				),
				'condition' => array(
					'tilt' => 'yes',
				),
			)
		);

		$this->add_control(
			'tilt_scale',
			array(
				'label'     => __( 'Scale', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 1,
						'max'  => 1.4,
						'step' => 0.01,
					),
				),
				'default'   => array(
					'size' => 1.06,
				),
				'condition' => array(
					'tilt' => 'yes',
				),
			)
		);

		$this->add_control(
			'tilt_caption_depth',
			array(
				'label'     => __( 'Depth', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'   => array(
					'size' => 20,
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-grid-item .pp-gallery-image-content' => 'transform: translateZ({{SIZE}}px);',
				),
				'condition' => array(
					'tilt' => 'yes',
				),
			)
		);

		$this->add_control(
			'tilt_speed',
			array(
				'label'     => __( 'Speed', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 100,
						'max'  => 1000,
						'step' => 20,
					),
				),
				'default'   => array(
					'size' => 800,
				),
				'condition' => array(
					'tilt' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function register_content_pagination_controls() {
		/**
		 * Content Tab: Load More Button
		 */
		$this->start_controls_section(
			'section_pagination',
			array(
				'label' => __( 'Load More Button', 'powerpack' ),
			)
		);

		$this->add_control(
			'pagination',
			array(
				'label'              => __( 'Load More Button', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => '',
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'images_per_page',
			array(
				'label'     => __( 'Images Per Page', 'powerpack' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 6,
				'condition' => array(
					'pagination' => 'yes',
				),
			)
		);

		$this->add_control(
			'load_more_text',
			array(
				'label'     => __( 'Button Text', 'powerpack' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Load More', 'powerpack' ),
				'condition' => array(
					'pagination' => 'yes',
				),
			)
		);

		$this->add_control(
			'select_load_more_icon',
			array(
				'label'            => __( 'Button Icon', 'powerpack' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'load_more_icon',
				'condition'        => array(
					'pagination' => 'yes',
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
					'pagination' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'load_more_align',
			array(
				'label'     => __( 'Alignment', 'powerpack' ),
				'type'      => Controls_Manager::CHOOSE,
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
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .pp-gallery-pagination' => 'text-align: {{VALUE}};',
				),
				'condition' => array(
					'pagination' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function register_content_help_docs_controls() {

		$help_docs = PP_Config::get_widget_help_links( 'Image_Gallery' );

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

	/**
	 * Register layout controls for style tab
	 */
	protected function register_style_layout_controls() {
		/**
		 * Style Tab: Layout
		 */
		$this->start_controls_section(
			'section_layout_style',
			array(
				'label' => __( 'Layout', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'columns_gap',
			array(
				'label'          => __( 'Columns Gap', 'powerpack' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => array(
					'size' => 20,
					'unit' => 'px',
				),
				'size_units'     => array( 'px', '%' ),
				'range'          => array(
					'px' => array(
						'max' => 100,
					),
				),
				'tablet_default' => array(
					'unit' => 'px',
				),
				'mobile_default' => array(
					'unit' => 'px',
				),
				'selectors'      => array(
					'{{WRAPPER}} .pp-image-gallery .pp-grid-item-wrap' => 'padding-left: calc({{SIZE}}{{UNIT}}/2); padding-right: calc({{SIZE}}{{UNIT}}/2);',
					'{{WRAPPER}} .pp-image-gallery' => 'margin-left: calc(-{{SIZE}}{{UNIT}}/2); margin-right: calc(-{{SIZE}}{{UNIT}}/2);',
				),
				'condition'      => array(
					'layout!' => 'justified',
				),
			)
		);

		$this->add_responsive_control(
			'rows_gap',
			array(
				'label'          => __( 'Rows Gap', 'powerpack' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => array(
					'size' => 20,
					'unit' => 'px',
				),
				'size_units'     => array( 'px', '%' ),
				'range'          => array(
					'px' => array(
						'max' => 100,
					),
				),
				'tablet_default' => array(
					'unit' => 'px',
				),
				'mobile_default' => array(
					'unit' => 'px',
				),
				'selectors'      => array(
					'{{WRAPPER}} .pp-image-gallery .pp-grid-item' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
				'condition'      => array(
					'layout!' => 'justified',
				),
			)
		);

		$this->add_control(
			'image_spacing',
			array(
				'label'          => __( 'Image Spacing', 'powerpack' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => array(
					'size' => 10,
				),
				'range'          => array(
					'px' => array(
						'max' => 100,
					),
				),
				'condition'      => array(
					'layout' => 'justified',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function register_style_thumbnails_controls() {
		/**
		 * Style Tab: Thumbnails
		 */
		$this->start_controls_section(
			'section_thumbnails_style',
			array(
				'label' => __( 'Thumbnails', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'image_align',
			array(
				'label'       => __( 'Alignment', 'powerpack' ),
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
				),
				'default'     => '',
				'selectors'   => array(
					'{{WRAPPER}} .pp-image-gallery-thumbnail' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_image_filter_style' );

		$this->start_controls_tab(
			'tab_image_filter_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'image_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-image-gallery-thumbnail-wrap',
			)
		);

		$this->add_control(
			'image_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-image-gallery-thumbnail-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'image_scale',
			array(
				'label'     => __( 'Scale', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 1,
						'max'  => 2,
						'step' => 0.01,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-image-gallery-thumbnail img' => 'transform: scale({{SIZE}});',
				),
			)
		);

		$this->add_control(
			'image_opacity',
			array(
				'label'     => __( 'Opacity', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max'  => 1,
						'min'  => 0,
						'step' => 0.01,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-image-gallery-thumbnail img' => 'opacity: {{SIZE}}',
				),
			)
		);

		$this->add_control(
			'thumbnail_filter',
			array(
				'label'        => __( 'Image Filter', 'powerpack' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'normal',
				'options'      => $this->image_filters(),
				'prefix_class' => 'pp-ins-',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_image_filter_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'image_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-grid-item:hover .pp-image-gallery-thumbnail-wrap' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'image_hover_scale',
			array(
				'label'     => __( 'Scale', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 1,
						'max'  => 2,
						'step' => 0.01,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-image-gallery-thumbnail-wrap:hover .pp-image-gallery-thumbnail img' => 'transform: scale({{SIZE}});',
				),
			)
		);

		$this->add_control(
			'image_hover_opacity',
			array(
				'label'     => __( 'Opacity', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max'  => 1,
						'min'  => 0,
						'step' => 0.01,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-image-gallery-thumbnail-wrap:hover .pp-image-gallery-thumbnail img' => 'opacity: {{SIZE}}',
				),
			)
		);

		$this->add_control(
			'thumbnail_hover_filter',
			array(
				'label'        => __( 'Image Filter', 'powerpack' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => '',
				'options'      => $this->image_filters( true ),
				'prefix_class' => 'pp-ins-hover-',
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function register_style_caption_controls() {
		/**
		 * Style Tab: Caption
		 */
		$this->start_controls_section(
			'section_caption_style',
			array(
				'label'     => __( 'Caption', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'caption' => 'show',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'caption_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-gallery-image-caption',
				'condition' => array(
					'caption' => 'show',
				),
			)
		);

		$this->add_control(
			'caption_vertical_align',
			array(
				'label'                => __( 'Vertical Align', 'powerpack' ),
				'type'                 => Controls_Manager::CHOOSE,
				'label_block'          => false,
				'toggle'               => false,
				'default'              => 'bottom',
				'options'              => array(
					'top'    => array(
						'title' => __( 'Top', 'powerpack' ),
						'icon'  => 'eicon-v-align-top',
					),
					'middle' => array(
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'eicon-v-align-middle',
					),
					'bottom' => array(
						'title' => __( 'Bottom', 'powerpack' ),
						'icon'  => 'eicon-v-align-bottom',
					),
				),
				'selectors_dictionary' => array(
					'top'    => 'flex-start',
					'middle' => 'center',
					'bottom' => 'flex-end',
				),
				'selectors'            => array(
					'{{WRAPPER}} .pp-gallery-image-content'   => 'justify-content: {{VALUE}};',
				),
				'condition'            => array(
					'caption' => 'show',
				),
			)
		);

		$this->add_control(
			'caption_horizontal_align',
			array(
				'label'                => __( 'Horizontal Align', 'powerpack' ),
				'type'                 => Controls_Manager::CHOOSE,
				'label_block'          => false,
				'toggle'               => false,
				'options'              => array(
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
						'title' => __( 'Justify', 'powerpack' ),
						'icon'  => 'eicon-h-align-stretch',
					),
				),
				'default'              => 'left',
				'selectors_dictionary' => array(
					'left'    => 'flex-start',
					'center'  => 'center',
					'right'   => 'flex-end',
					'justify' => 'stretch',
				),
				'selectors'            => array(
					'{{WRAPPER}} .pp-gallery-image-content' => 'align-items: {{VALUE}};',
				),
				'condition'            => array(
					'caption' => 'show',
				),
			)
		);

		$this->add_control(
			'caption_text_align',
			array(
				'label'       => __( 'Text Align', 'powerpack' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => array(
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
				'default'     => 'center',
				'selectors'   => array(
					'{{WRAPPER}} .pp-gallery-image-caption' => 'text-align: {{VALUE}};',
				),
				'condition'   => array(
					'caption'                  => 'show',
					'caption_horizontal_align' => 'justify',
				),
			)
		);

		$this->add_responsive_control(
			'caption_margin',
			array(
				'label'      => __( 'Margin', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-gallery-image-caption' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'caption' => 'show',
				),
			)
		);

		$this->add_responsive_control(
			'caption_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-gallery-image-caption' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'caption' => 'show',
				),
			)
		);

		$this->add_control(
			'caption_hover_effect',
			array(
				'label'        => __( 'Hover Effect', 'powerpack' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => '',
				'options'      => array(
					''                  => __( 'None', 'powerpack' ),
					'fade-in'           => __( 'Fade In', 'powerpack' ),
					'fade-out'          => __( 'Fade Out', 'powerpack' ),
					'fade-from-top'     => __( 'Fade From Top', 'powerpack' ),
					'fade-from-bottom'  => __( 'Fade From Bottom', 'powerpack' ),
					'fade-from-left'    => __( 'Fade From Left', 'powerpack' ),
					'fade-from-right'   => __( 'Fade From Right', 'powerpack' ),
					'slide-from-top'    => __( 'Slide From Top', 'powerpack' ),
					'slide-from-bottom' => __( 'Slide From Bottom', 'powerpack' ),
					'slide-from-left'   => __( 'Slide From Left', 'powerpack' ),
					'slide-from-right'  => __( 'Slide From Right', 'powerpack' ),
					'fade-to-top'       => __( 'Fade To Top', 'powerpack' ),
					'fade-to-bottom'    => __( 'Fade To Bottom', 'powerpack' ),
					'fade-to-left'      => __( 'Fade To Left', 'powerpack' ),
					'fade-to-right'     => __( 'Fade To Right', 'powerpack' ),
					'slide-to-top'      => __( 'Slide To Top', 'powerpack' ),
					'slide-to-bottom'   => __( 'Slide To Bottom', 'powerpack' ),
					'slide-to-left'     => __( 'Slide To Left', 'powerpack' ),
					'slide-to-right'    => __( 'Slide To Right', 'powerpack' ),
				),
				'prefix_class' => 'pp-caption-hover-effect-',
				'condition'    => array(
					'caption'          => 'show',
					'caption_position' => 'over_image',
					'tilt!'            => 'yes',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_caption_style' );

		$this->start_controls_tab(
			'tab_caption_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'caption' => 'show',
				),
			)
		);

		$this->add_control(
			'caption_bg_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-gallery-image-caption' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'caption' => 'show',
				),
			)
		);

		$this->add_control(
			'caption_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-gallery-image-caption' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'caption' => 'show',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'caption_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-gallery-image-caption',
				'condition'   => array(
					'caption' => 'show',
				),
			)
		);

		$this->add_control(
			'caption_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-gallery-image-caption' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'caption' => 'show',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'      => 'caption_text_shadow',
				'label'     => __( 'Text Shadow', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-gallery-image-caption',
				'condition' => array(
					'caption' => 'show',
				),
			)
		);

		$this->add_control(
			'caption_opacity_normal',
			array(
				'label'     => __( 'Opacity', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.01,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-gallery-image-caption' => 'opacity: {{SIZE}};',
				),
				'condition' => array(
					'caption' => 'show',
					'tilt'    => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_caption_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'caption' => 'show',
				),
			)
		);

		$this->add_control(
			'caption_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-grid-item:hover .pp-gallery-image-caption' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'caption' => 'show',
				),
			)
		);

		$this->add_control(
			'caption_color_hover',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-grid-item:hover .pp-gallery-image-caption' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'caption' => 'show',
				),
			)
		);

		$this->add_control(
			'caption_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-grid-item:hover .pp-gallery-image-caption' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'caption' => 'show',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'      => 'caption_text_shadow_hover',
				'label'     => __( 'Text Shadow', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-grid-item:hover .pp-gallery-image-caption',
				'condition' => array(
					'caption' => 'show',
				),
			)
		);

		$this->add_control(
			'caption_opacity_hover',
			array(
				'label'     => __( 'Opacity', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.01,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-grid-item:hover .pp-gallery-image-caption' => 'opacity: {{SIZE}};',
				),
				'condition' => array(
					'caption' => 'show',
					'tilt'    => 'yes',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function register_style_link_icon_controls() {
		/**
		 * Style Tab: Link Icon
		 */
		$this->start_controls_section(
			'section_link_icon_style',
			array(
				'label'     => __( 'Link Icon', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'select_link_icon[value]!' => '',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_link_icon_style' );

		$this->start_controls_tab(
			'tab_link_icon_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'select_link_icon[value]!' => '',
				),
			)
		);

		$this->add_control(
			'link_icon_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-grid-item .pp-gallery-image-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .pp-grid-item .pp-gallery-image-icon svg' => 'fill: {{VALUE}};',
				),
				'condition' => array(
					'select_link_icon[value]!' => '',
				),
			)
		);

		$this->add_control(
			'link_icon_bg_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-grid-item .pp-gallery-image-icon' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'select_link_icon[value]!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'link_icon_border_normal',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-grid-item .pp-gallery-image-icon',
				'condition'   => array(
					'select_link_icon[value]!' => '',
				),
			)
		);

		$this->add_control(
			'link_icon_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-grid-item .pp-gallery-image-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'select_link_icon[value]!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'link_icon_size',
			array(
				'label'      => __( 'Icon Size', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min'  => 5,
						'max'  => 100,
						'step' => 1,
					),
				),
				'size_units' => array( 'px' ),
				'condition'  => array(
					'icon_type' => 'icon',
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-grid-item .pp-gallery-image-icon' => 'font-size: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'select_link_icon[value]!' => '',
				),
			)
		);

		$this->add_control(
			'link_icon_opacity_normal',
			array(
				'label'     => __( 'Opacity', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.01,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-grid-item .pp-gallery-image-icon' => 'opacity: {{SIZE}};',
				),
				'condition' => array(
					'select_link_icon[value]!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'link_icon_padding',
			array(
				'label'       => __( 'Padding', 'powerpack' ),
				'type'        => Controls_Manager::DIMENSIONS,
				'size_units'  => array( 'px' ),
				'placeholder' => array(
					'top'    => '',
					'right'  => '',
					'bottom' => '',
					'left'   => '',
				),
				'selectors'   => array(
					'{{WRAPPER}} .pp-grid-item .pp-gallery-image-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'   => array(
					'select_link_icon[value]!' => '',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_link_icon_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'select_link_icon[value]!' => '',
				),
			)
		);

		$this->add_control(
			'link_icon_color_hover',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-grid-item:hover .pp-gallery-image-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .pp-grid-item:hover .pp-gallery-image-icon svg' => 'fill: {{VALUE}};',
				),
				'condition' => array(
					'select_link_icon[value]!' => '',
				),
			)
		);

		$this->add_control(
			'link_icon_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-grid-item:hover .pp-gallery-image-icon' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'select_link_icon[value]!' => '',
				),
			)
		);

		$this->add_control(
			'link_icon_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-grid-item:hover .pp-gallery-image-icon' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'select_link_icon[value]!' => '',
				),
			)
		);

		$this->add_control(
			'link_icon_opacity_hover',
			array(
				'label'     => __( 'Opacity', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.01,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-grid-item:hover .pp-gallery-image-icon' => 'opacity: {{SIZE}};',
				),
				'condition' => array(
					'select_link_icon[value]!' => '',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function register_style_overlay_controls() {
		/**
		 * Style Tab: Overlay
		 */
		$this->start_controls_section(
			'section_overlay_style',
			array(
				'label' => __( 'Overlay', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'overlay_blend_mode',
			array(
				'label'     => __( 'Blend Mode', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'normal',
				'options'   => array(
					'normal'      => __( 'Normal', 'powerpack' ),
					'multiply'    => __( 'Multiply', 'powerpack' ),
					'screen'      => __( 'Screen', 'powerpack' ),
					'overlay'     => __( 'Overlay', 'powerpack' ),
					'darken'      => __( 'Darken', 'powerpack' ),
					'lighten'     => __( 'Lighten', 'powerpack' ),
					'color-dodge' => __( 'Color Dodge', 'powerpack' ),
					'color'       => __( 'Color', 'powerpack' ),
					'hue'         => __( 'Hue', 'powerpack' ),
					'hard-light'  => __( 'Hard Light', 'powerpack' ),
					'soft-light'  => __( 'Soft Light', 'powerpack' ),
					'difference'  => __( 'Difference', 'powerpack' ),
					'exclusion'   => __( 'Exclusion', 'powerpack' ),
					'saturation'  => __( 'Saturation', 'powerpack' ),
					'luminosity'  => __( 'Luminosity', 'powerpack' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-image-overlay' => 'mix-blend-mode: {{VALUE}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_overlay_style' );

		$this->start_controls_tab(
			'tab_overlay_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'overlay_background_color_normal',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .pp-image-overlay',
				'exclude'  => array(
					'image',
				),
			)
		);

		$this->add_control(
			'overlay_margin_normal',
			array(
				'label'     => __( 'Margin', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-image-overlay' => 'top: {{SIZE}}px; bottom: {{SIZE}}px; left: {{SIZE}}px; right: {{SIZE}}px;',
				),
			)
		);

		$this->add_control(
			'overlay_opacity_normal',
			array(
				'label'     => __( 'Opacity', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.01,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-image-overlay' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_overlay_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'overlay_background_color_hover',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .pp-grid-item:hover .pp-image-overlay',
				'exclude'  => array(
					'image',
				),
			)
		);

		$this->add_control(
			'overlay_margin_hover',
			array(
				'label'     => __( 'Margin', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-grid-item:hover .pp-image-overlay' => 'top: {{SIZE}}px; bottom: {{SIZE}}px; left: {{SIZE}}px; right: {{SIZE}}px;',
				),
			)
		);

		$this->add_control(
			'overlay_opacity_hover',
			array(
				'label'     => __( 'Opacity', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.01,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-grid-item:hover .pp-image-overlay' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Register Lightbox Controls in Style Tab
	 *
	 * @access protected
	 */
	protected function register_style_lightbox_controls() {
		/**
		 * Style Tab: Lightbox
		 */
		$this->start_controls_section(
			'section_lightbox_style',
			array(
				'label'     => __( 'Lightbox', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'lightbox_background',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '.pp-gallery-fancybox-{{ID}} .fancybox-bg',
				'exclude'   => array(
					'image',
				),
				'condition' => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'lightbox_opacity',
			array(
				'label'     => __( 'Opacity', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max'  => 1,
						'min'  => 0,
						'step' => 0.01,
					),
				),
				'selectors' => array(
					'.pp-gallery-fancybox-{{ID}} .fancybox-bg' => 'opacity: {{SIZE}}',
				),
				'condition' => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'lightbox_toolbar_buttons_heading',
			array(
				'label'     => __( 'Toolbar Buttons', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'lightbox_toolbar_icons_color',
			array(
				'label'     => __( 'Icons Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'.pp-gallery-fancybox-{{ID}} .fancybox-toolbar .fancybox-button' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'lightbox_toolbar_buttons_background',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '.pp-gallery-fancybox-{{ID}} .fancybox-toolbar .fancybox-button',
				'exclude'   => array(
					'image',
				),
				'condition' => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'lightbox_toolbar_buttons_size',
			array(
				'label'     => __( 'Size', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max'  => 70,
						'min'  => 30,
						'step' => 1,
					),
				),
				'selectors' => array(
					'.pp-gallery-fancybox-{{ID}} .fancybox-toolbar .fancybox-button' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'lightbox_arrows_heading',
			array(
				'label'     => __( 'Arrows', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
					'arrows'           => 'yes',
				),
			)
		);

		$this->add_control(
			'lightbox_arrows_icons_color',
			array(
				'label'     => __( 'Icons Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'.pp-gallery-fancybox-{{ID}} .fancybox-navigation .fancybox-button' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
					'arrows'           => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'lightbox_arrows_icons_background',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '.pp-gallery-fancybox-{{ID}} .fancybox-navigation .fancybox-button',
				'exclude'   => array(
					'image',
				),
				'condition' => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
					'arrows'           => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function register_style_filter_controls() {
		/**
		 * Style Tab: Filter
		 */
		$this->start_controls_section(
			'section_filter_style',
			array(
				'label'     => __( 'Filter', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'filter_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-gallery-filters',
				'condition' => array(
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_filter_style' );

		$this->start_controls_tab(
			'tab_filter_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
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
					'{{WRAPPER}} .pp-gallery-filters .pp-gallery-filter' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
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
					'{{WRAPPER}} .pp-gallery-filters .pp-gallery-filter' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
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
				'selector'    => '{{WRAPPER}} .pp-gallery-filters .pp-gallery-filter',
				'condition'   => array(
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
				),
			)
		);

		$this->add_control(
			'filter_border_radius_normal',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-gallery-filters .pp-gallery-filter' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
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
					'{{WRAPPER}} .pp-gallery-filters .pp-gallery-filter' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'   => array(
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'filter_box_shadow',
				'selector'  => '{{WRAPPER}} .pp-gallery-filters .pp-gallery-filter',
				'condition' => array(
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_filters_active',
			array(
				'label'     => __( 'Active', 'powerpack' ),
				'condition' => array(
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
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
					'{{WRAPPER}} .pp-gallery-filters .pp-gallery-filter.pp-active' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
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
					'{{WRAPPER}} .pp-gallery-filters .pp-gallery-filter.pp-active' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
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
					'{{WRAPPER}} .pp-gallery-filters .pp-gallery-filter.pp-active' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'filter_box_shadow_active',
				'selector'  => '{{WRAPPER}} .pp-gallery-filters .pp-gallery-filter.pp-active',
				'condition' => array(
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
				),
			)
		);

		$this->add_control(
			'galleries_pointer_color_active',
			[
				'label'     => __( 'Pointer Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => [
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_4,
				],
				'selectors' => [
					'{{WRAPPER}}' => '--filters-pointer-bg-color-active: {{VALUE}}',
				],
				'condition' => [
					'pointer!' => [ 'none', 'text' ],
				],

			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_filters_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
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
					'{{WRAPPER}} .pp-gallery-filters .pp-gallery-filter:hover' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
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
					'{{WRAPPER}} .pp-gallery-filters .pp-gallery-filter:hover' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
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
					'{{WRAPPER}} .pp-gallery-filters .pp-gallery-filter:hover' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'filter_box_shadow_hover',
				'selector'  => '{{WRAPPER}} .pp-gallery-filters .pp-gallery-filter:hover',
				'condition' => array(
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
				),
			)
		);

		$this->add_control(
			'galleries_pointer_color_hover',
			[
				'label'     => __( 'Pointer Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => [
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_4,
				],
				'selectors' => [
					'{{WRAPPER}}' => '--filters-pointer-bg-color-hover: {{VALUE}}',
				],
				'condition' => [
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
					'pointer!'      => [ 'none', 'text' ],
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'pointer_width',
			[
				'label' => __( 'Pointer Width', 'powerpack' ),
				'type' => Controls_Manager::SLIDER,
				'devices' => [ self::RESPONSIVE_DESKTOP, self::RESPONSIVE_TABLET ],
				'range' => [
					'px' => [
						'max' => 30,
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--filters-pointer-border-width: {{SIZE}}{{UNIT}}',
				],
				'separator' => 'before',
				'condition' => [
					'pointer' => [ 'underline', 'overline', 'double-line', 'framed' ],
				],
			]
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
					'{{WRAPPER}} .pp-gallery-filters .pp-gallery-filter, {{WRAPPER}} .pp-filters-dropdown' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'gallery_type'  => 'filterable',
					'filter_enable' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function register_style_pagination_controls() {
		/**
		 * Style Tab: Load More Button
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_load_more_button_style',
			array(
				'label'     => __( 'Load More Button', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'pagination'      => 'yes',
					'load_more_text!' => '',
				),
			)
		);

		$this->add_control(
			'button_size',
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
					'pagination'      => 'yes',
					'load_more_text!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'button_margin_top',
			array(
				'label'      => __( 'Top Spacing', 'powerpack' ),
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
					'{{WRAPPER}} .pp-gallery-pagination' => 'margin-top: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_load_more_button_style' );

		$this->start_controls_tab(
			'tab_load_more_button_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'pagination'      => 'yes',
					'load_more_text!' => '',
				),
			)
		);

		$this->add_control(
			'load_more_button_text_color_normal',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-gallery-load-more' => 'color: {{VALUE}}',
					'{{WRAPPER}} .pp-gallery-load-more .pp-icon svg' => 'fill: {{VALUE}}',
				),
				'condition' => array(
					'pagination'      => 'yes',
					'load_more_text!' => '',
				),
			)
		);

		$this->add_control(
			'load_more_button_bg_color_normal',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => [
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_4,
				],
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-gallery-load-more' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'pagination'      => 'yes',
					'load_more_text!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'load_more_button_border_normal',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-gallery-load-more',
				'condition'   => array(
					'pagination'      => 'yes',
					'load_more_text!' => '',
				),
			)
		);

		$this->add_control(
			'load_more_button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-gallery-load-more' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'pagination'      => 'yes',
					'load_more_text!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'load_more_button_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'scheme'    => Scheme_Typography::TYPOGRAPHY_4,
				'selector'  => '{{WRAPPER}} .pp-gallery-load-more',
				'condition' => array(
					'pagination'      => 'yes',
					'load_more_text!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'load_more_button_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-gallery-load-more' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'pagination'      => 'yes',
					'load_more_text!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'load_more_button_box_shadow',
				'selector'  => '{{WRAPPER}} .pp-gallery-load-more',
				'condition' => array(
					'pagination'      => 'yes',
					'load_more_text!' => '',
				),
			)
		);

		$this->add_control(
			'load_more_button_icon_heading',
			array(
				'label'     => __( 'Button Icon', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'pagination'                    => 'yes',
					'select_load_more_icon[value]!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'load_more_button_icon_margin',
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
					'{{WRAPPER}} .pp-gallery-pagination .pp-gallery-load-more-icon' => 'margin-top: {{TOP}}{{UNIT}}; margin-left: {{LEFT}}{{UNIT}}; margin-right: {{RIGHT}}{{UNIT}}; margin-bottom: {{BOTTOM}}{{UNIT}};',
				),
				'condition'   => array(
					'pagination'                    => 'yes',
					'select_load_more_icon[value]!' => '',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'pagination'      => 'yes',
					'load_more_text!' => '',
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
					'{{WRAPPER}} .pp-gallery-load-more:hover' => 'color: {{VALUE}}',
					'{{WRAPPER}} .pp-gallery-load-more:hover .pp-icon svg' => 'fill: {{VALUE}}',
				),
				'condition' => array(
					'pagination'      => 'yes',
					'load_more_text!' => '',
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
					'{{WRAPPER}} .pp-gallery-load-more:hover' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'pagination'      => 'yes',
					'load_more_text!' => '',
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
					'{{WRAPPER}} .pp-gallery-load-more:hover' => 'border-color: {{VALUE}}',
				),
				'condition' => array(
					'pagination'      => 'yes',
					'load_more_text!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'button_box_shadow_hover',
				'selector'  => '{{WRAPPER}} .pp-gallery-load-more:hover',
				'condition' => array(
					'pagination'      => 'yes',
					'load_more_text!' => '',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'load_more_button_animation_heading',
			array(
				'label'     => __( 'Loading Animation', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'pagination'      => 'yes',
					'load_more_text!' => '',
				),
			)
		);

		$this->add_control(
			'load_more_button_animation_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-gallery-load-more.pp-loading .pp-button-loader:after' => 'border-top-color: {{VALUE}}; border-bottom-color: {{VALUE}};',
				),
				'condition' => array(
					'pagination'      => 'yes',
					'load_more_text!' => '',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Fancybox Settings.
	 *
	 * @access public
	 */
	public function fancybox_settings() {
		$settings = $this->get_settings();

		$base_class = 'pp-gallery-fancybox pp-gallery-fancybox-' . esc_attr( $this->get_id() ) . ' ';

		if ( 'bottom' === $settings['thumbs_position'] ) {
			$base_class    .= ' pp-fancybox-thumbs-x';
			$fancybox_axis = 'x';
		} else {
			$base_class    .= ' pp-fancybox-thumbs-y';
			$fancybox_axis = 'y';
		}

		$fancybox_options = array(
			'loop'             => ( 'yes' === $settings['loop'] ),
			'arrows'           => ( 'yes' === $settings['arrows'] ),
			'infobar'          => ( 'yes' === $settings['slides_counter'] ),
			'keyboard'         => ( 'yes' === $settings['keyboard'] ),
			'toolbar'          => ( 'yes' === $settings['toolbar'] ),
			'buttons'          => $settings['toolbar_buttons'],
			'animationEffect'  => $settings['lightbox_animation'],
			'transitionEffect' => $settings['transition_effect'],
			'baseClass'        => $base_class,
			'thumbs'           => array(
				'autoStart' => ( 'yes' === $settings['thumbs_auto_start'] ),
				'axis'      => $fancybox_axis,
			),
		);

		return $fancybox_options;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$classes = array(
			'pp-image-gallery',
			'pp-elementor-grid',
		);

		if ( 'masonry' === $settings['layout'] ) {
			$classes[] = 'pp-image-gallery-masonry';
		}

		if ( 'justified' === $settings['layout'] ) {
			$classes[] = 'pp-image-gallery-justified';
		}

		if ( 'yes' === $settings['filter_enable'] ) {
			$classes[] = 'pp-image-gallery-filter-enabled';
		}

		$this->add_render_attribute(
			'gallery',
			array(
				'class' => $classes,
				'id'    => 'pp-image-gallery-' . $this->get_id(),
			)
		);

		if ( 'fancybox' === $settings['lightbox_library'] ) {
			$fancybox_settings = $this->fancybox_settings();

			$this->add_render_attribute(
				'gallery',
				array(
					'data-fancybox-settings' => wp_json_encode( $fancybox_settings ),
				)
			);
		}

		$gallery_settings = array();

		if ( 'yes' === $settings['tilt'] ) {
			$tilt_options = array(
				'tilt_enable' => 'yes',
				'tilt_axis'   => ! empty( $settings['tilt_axis'] ) ? $settings['tilt_axis'] : '',
				'tilt_amount' => ! empty( $settings['tilt_amount']['size'] ) ? $settings['tilt_amount']['size'] : '20',
				'tilt_scale'  => ! empty( $settings['tilt_scale']['size'] ) ? $settings['tilt_scale']['size'] : '1.06',
				'tilt_speed'  => ! empty( $settings['tilt_speed']['size'] ) ? $settings['tilt_speed']['size'] : '800',
			);
		} else {
			$tilt_options = array(
				'tilt_enable' => 'no',
			);
		}

		$gallery_settings = array_merge( $gallery_settings, $tilt_options );

		$gallery_settings['layout'] = $settings['layout'];

		if ( 'justified' === $settings['layout'] ) {
			$gallery_settings['image_spacing'] = ( $settings['image_spacing']['size'] ) ? $settings['image_spacing']['size'] : 0;
			$gallery_settings['row_height'] = $settings['row_height']['size'];
			$gallery_settings['last_row']   = $settings['last_row'];
		}

		if ( 'yes' === $settings['pagination'] ) {
			$gallery_settings['pagination'] = $settings['pagination'];
			$gallery_settings['per_page']   = $settings['images_per_page'];
		}

		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			$gallery_settings['post_id'] = \Elementor\Plugin::$instance->editor->get_post_id();
		} else {
			$gallery_settings['post_id'] = get_the_ID();
		}

		if ( null !== \Elementor\Plugin::$instance->documents->get_current() ) {
			$gallery_settings['template_id'] = \Elementor\Plugin::$instance->documents->get_current()->get_main_id();
		} else {
			$gallery_settings['template_id'] = '';
		}

		$gallery_settings['widget_id'] = $this->get_id();

		$this->add_render_attribute( 'gallery-container', [
			'class'         => 'pp-image-gallery-container',
			'data-settings' => wp_json_encode( $gallery_settings ),
		] );

		$image_gallery = $this->get_photos();
		?>
		<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'gallery-container' ) ); ?>>
			<?php if ( ! empty( $image_gallery ) ) { ?>
			<div class="pp-image-gallery-wrapper">
				<?php $this->render_filters(); ?>
				<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'gallery' ) ); ?>>
					<?php $this->render_gallery_items(); ?>
				</div>
				<?php $this->render_pagination(); ?>
			</div>
				<?php
			} else {
				$placeholder = sprintf( 'Click here to edit the "%1$s" settings and choose some images.', esc_attr( $this->get_title() ) );

				echo wp_kses_post( $this->render_editor_placeholder(
					array(
						'title' => __( 'Gallery is empty!', 'powerpack' ),
						'body'  => $placeholder,
					)
				) );
			}
			?>
		</div>
		<?php
		if ( \Elementor\Plugin::instance()->editor->is_edit_mode() ) {

			if ( ( 'grid' === $settings['layout'] && 'yes' === $settings['filter_enable'] ) || 'masonry' === $settings['layout'] ) {
				$this->render_editor_script();
			}
		}
	}

	/**
	 * Render filters
	 */
	protected function render_filters() {
		$settings = $this->get_settings_for_display();

		if ( 'yes' === $settings['filter_enable'] ) {
			$all_text = ( '' !== $settings['filter_all_label'] ) ? $settings['filter_all_label'] : esc_html__( 'All', 'powerpack' );
			$gallery  = $settings['gallery_images'];
			$default_filter_select = $settings['default_filter_select'];
			$default_filter = $settings['default_filter'];

			$this->add_render_attribute( 'filters-wrapper', 'class', 'pp-filters-wrapper' );
			$this->add_render_attribute( 'filters-container', 'class', 'pp-gallery-filters' );
			if ( $settings['pointer'] ) {
				if ( 'underline' === $settings['pointer'] || 'overline' === $settings['pointer'] || 'double-line' === $settings['pointer'] ) {
					$this->add_render_attribute( 'filters-container', 'class', 'pp-pointer-line' );
				}

				$this->add_render_attribute( 'filters-container', 'class', 'pp-pointer-' . $settings['pointer'] );

				foreach ( $settings as $key => $value ) {
					if ( 0 === strpos( $key, 'animation' ) && $value ) {
						$this->add_render_attribute( 'filters-container', 'class', 'pp-animation-' . $value );
						break;
					}
				}
			}

			if ( 'tablet' === $settings['responsive_support'] || 'mobile' === $settings['responsive_support'] ) {
				$this->add_render_attribute( 'filters-wrapper', 'class', 'pp-filters-wrapper-' . $settings['responsive_support'] );
			}
			?>
			<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'filters-wrapper' ) ); ?>>
				<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'filters-container' ) ); ?>>
					<?php
					$this->add_render_attribute( 'all-filter', [
						'class'              => 'pp-gallery-filter',
						'data-filter'        => '*',
						'data-gallery-index' => 'all',
					]);

					if ( 'first' === $default_filter_select || '' === $default_filter ) {
						$this->add_render_attribute( 'all-filter', 'class', 'pp-active' );
					}
					?>
					<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'all-filter' ) ); ?>>
						<?php echo wp_kses_post( $all_text ); ?>
					</div>
					<?php
					foreach ( $gallery as $index => $item ) {
						$filter_label = $item['filter_label'];
						$filter_id = $item['filter_id'];

						if ( empty( $filter_label ) ) {
							$filter_label  = __( 'Group ', 'powerpack' );
							$filter_label .= ( $index + 1 );
						}

						$filter_key = $this->get_repeater_setting_key( 'filter', 'gallery', $index );

						$this->add_render_attribute( $filter_key, [
							'class'              => 'pp-gallery-filter',
							'data-filter'        => '.pp-group-' . ( $index + 1 ),
							'data-gallery-index' => 'pp-group-' . ( $index + 1 ),
						]);

						if ( '' !== $filter_id ) {
							$this->add_render_attribute( $filter_key, 'id', $filter_id );
						}

						if ( 'custom' === $default_filter_select && $filter_label === $default_filter ) {
							$this->add_render_attribute( $filter_key, [
								'class'        => 'pp-active',
								'data-default' => '.pp-group-' . ( $index + 1 ),
							]);
						}
						?>
						<div <?php echo wp_kses_post( $this->get_render_attribute_string( $filter_key ) ); ?>><?php echo wp_kses_post( $filter_label ); ?></div>
					<?php } ?>
				</div>

				<?php if ( 'tablet' === $settings['responsive_support'] || 'mobile' === $settings['responsive_support'] ) { ?>
					<select class="pp-gallery-filters pp-filters-dropdown">
						<option value="*"><?php echo wp_kses_post( $all_text ); ?></option>
						<?php
						foreach ( $gallery as $index => $item ) {
							$filter_label = $item['filter_label'];
							if ( empty( $filter_label ) ) {
								$filter_label  = __( 'Group ', 'powerpack' );
								$filter_label .= ( $index + 1 );
							}

							$responsive_filter_key = $this->get_repeater_setting_key( 'mobile_filter', 'gallery', $index );

							$this->add_render_attribute( $responsive_filter_key, [
								'value'              => '.pp-group-' . ( $index + 1 ),
								'data-gallery-index' => 'pp-group-' . ( $index + 1 ),
							]);

							if ( 'custom' === $default_filter_select && $filter_label === $default_filter ) {
								$this->add_render_attribute( $responsive_filter_key, [
									'selected' => 'selected',
								]);
							}
							?>
							<option <?php echo wp_kses_post( $this->get_render_attribute_string( $responsive_filter_key ) ); ?>><?php echo wp_kses_post( $filter_label ); ?></option>
						<?php } ?>
					</select>
				<?php } ?>
			</div>
			<?php
		}
	}

	/**
	 * Render gallery items
	 */
	protected function render_gallery_items() {
		$settings = $this->get_settings_for_display();
		$photos   = $this->get_photos();
		$count    = 0;

		foreach ( $photos as $photo ) {
			$grid_item_key = $this->get_repeater_setting_key( 'gallery_item', 'gallery_images', $count );
			$thumb_wrap_key   = $this->get_repeater_setting_key( 'thumb-wrap', 'gallery_images', $count );

			$this->add_render_attribute( $grid_item_key, 'class', [ 'pp-grid-item', 'pp-image' ] );
			$this->add_render_attribute( $thumb_wrap_key, 'class', array( 'pp-image-gallery-thumbnail-wrap', 'pp-ins-filter-hover' ) );

			if ( 'yes' === $settings['tilt'] ) {
				$this->add_render_attribute( $thumb_wrap_key, 'class', 'pp-gallery-tilt' );
			}

			if ( 'filterable' === $settings['gallery_type'] ) {
				$filter_labels      = $this->get_filter_ids( $settings['gallery_images'], true );
				$filter_label       = $filter_labels[ $photo->id ];
				$final_filter_label = preg_replace( '/[^\sA-Za-z0-9]/', '-', $filter_label );
			} else {
				$final_filter_label = '';
			}
			?>
			<div class="pp-grid-item-wrap <?php echo wp_kses_post( $final_filter_label ); ?>" data-item-id="<?php echo esc_attr( $photo->id ); ?>">
				<div <?php echo wp_kses_post( $this->get_render_attribute_string( $grid_item_key ) ); ?>>
					<div <?php echo wp_kses_post( $this->get_render_attribute_string( $thumb_wrap_key ) ); ?>>
						<?php
						$srcset = apply_filters( 'pp_gallery_output_image_srcset', false ) ? esc_attr( $photo->srcset ) : '';
						$img_attrs = array(
							'class'        => 'pp-gallery-slide-image',
							'src'          => wp_get_attachment_image_url( $photo->id, $settings['image_size'] ),
							'alt'          => $photo->alt,
							'data-no-lazy' => 1,
						);
			
						if ( ! empty( $srcset ) ) {
							$img_attrs['srcset'] = $srcset;
						}
			
						$img_attrs = apply_filters( 'pp_gallery_image_html_attrs', $img_attrs, $photo, $settings );
			
						$img_attrs_str = '';
			
						foreach ( $img_attrs as $key => $value ) {
							$img_attrs_str .= ' ' . $key . '=' . '"' . $value . '"';
						}

						$image_html = '<div class="pp-ins-filter-target pp-image-gallery-thumbnail">';
						$image_html .= '<img ' . trim( $img_attrs_str ) . '/>';
						$image_html .= '</div>';

						$image_html .= $this->render_image_overlay( $count );

						$image_html .= '<div class="pp-gallery-image-content pp-media-content">';

						// Link Icon.
						$image_html .= $this->render_link_icon();

						if ( 'over_image' === $settings['caption_position'] || 'justified' === $settings['layout'] ) {
							// Image Caption.
							$image_html .= $this->render_image_caption( $photo->id );
						}

						$image_html .= '</div>';

						if ( 'none' !== $settings['link_to'] ) {

							$link      = '';
							$link_attr = '';
							$link_key  = $this->get_repeater_setting_key( 'link', 'gallery_images', $count );

							if ( 'file' === $settings['link_to'] ) {

								$lightbox_library = $settings['lightbox_library'];
								$lightbox_caption = $settings['lightbox_caption'];

								$link = wp_get_attachment_url( $photo->id );

								if ( 'fancybox' === $lightbox_library ) {
									$this->add_render_attribute(
										$link_key,
										array(
											'data-elementor-open-lightbox' => 'no',
										)
									);

									if ( 'yes' === $settings['global_lightbox'] ) {
										$this->add_render_attribute(
											$link_key,
											array(
												'data-fancybox' => 'pp-image-gallery',
											)
										);
									} else {
										$this->add_render_attribute(
											$link_key,
											array(
												'data-fancybox' => 'pp-image-gallery-' . $this->get_id(),
											)
										);
									}

									if ( '' !== $lightbox_caption ) {
										$caption = Module::get_image_caption( $photo->id, $settings['lightbox_caption'] );

										$this->add_render_attribute(
											$link_key,
											array(
												'data-caption' => $caption,
											)
										);
									}

									$link_attr = 'href';

								} else {
									$this->add_render_attribute(
										$link_key,
										array(
											'data-elementor-open-lightbox'      => $settings['open_lightbox'],
											'data-elementor-lightbox-slideshow' => $this->get_id(),
										)
									);

									if ( '' !== $lightbox_caption ) {
										$caption = Module::get_image_caption( $photo->id, $settings['lightbox_caption'] );

										$this->add_render_attribute(
											$link_key,
											array(
												'data-elementor-lightbox-title' => $caption,
											)
										);
									}

									$link_attr = 'href';

									$this->add_render_attribute(
										$link_key,
										array(
											'class' => 'elementor-clickable',
										)
									);
								}
							} elseif ( 'custom' === $settings['link_to'] ) {

								$link = get_post_meta( $photo->id, 'pp-custom-link', true );

								if ( '' !== $link ) {
									$link_attr = 'href';
								}
							} elseif ( 'attachment' === $settings['link_to'] ) {

								$link      = get_attachment_link( $photo->id );
								$link_attr = 'href';

							}

							if ( 'attachment' === $settings['link_to'] || ( 'custom' === $settings['link_to'] && '' !== $link ) || ( 'file' === $settings['link_to'] && 'no' === $settings['open_lightbox'] ) ) {

								$link_target = $settings['link_target'];

								$this->add_render_attribute(
									$link_key,
									array(
										'target' => $link_target,
									)
								);
							}

							if ( '' !== $link && '' !== $link_attr ) {

								$this->add_render_attribute(
									$link_key,
									array(
										$link_attr => $link,
										'class'    => 'pp-image-gallery-item-link',
									)
								);

								$image_html = '<a ' . $this->get_render_attribute_string( $link_key ) . '></a>' . $image_html;
							}
						}

						echo wp_kses_post( $image_html );

						if ( 'below_image' === $settings['caption_position'] && 'justified' !== $settings['layout'] ) {
							?>
							<div class="pp-gallery-image-content">
								<?php
									// Image Caption.
									echo wp_kses_post( $this->render_image_caption( $photo->id ) );
								?>
							</div>
							<?php
						}
						?>
					</div>
				</div>
			</div>
			<?php
			$count++;
		}
	}

	/**
	 * Render pagination
	 */
	protected function render_pagination() {
		$settings = $this->get_settings_for_display();

		$photos      = $this->get_wordpress_photos();
		$image_count = count( $photos );
		$per_page    = $settings['images_per_page'];

		if ( 'yes' === $settings['pagination'] && $image_count > $per_page ) {

			if ( ! isset( $settings['load_more_icon'] ) && ! Icons_Manager::is_migration_allowed() ) {
				// add old default.
				$settings['load_more_icon'] = '';
			}

			$has_icon = ! empty( $settings['load_more_icon'] );

			if ( $has_icon ) {
				$this->add_render_attribute( 'i', 'class', $settings['load_more_icon'] );
				$this->add_render_attribute( 'i', 'aria-hidden', 'true' );
			}

			if ( ! $has_icon && ! empty( $settings['select_load_more_icon']['value'] ) ) {
				$has_icon = true;
			}
			$migrated = isset( $settings['__fa4_migrated']['select_load_more_icon'] );
			$is_new   = ! isset( $settings['load_more_icon'] ) && Icons_Manager::is_migration_allowed();

			$this->add_render_attribute(
				'load-more-button',
				'class',
				array(
					'pp-gallery-load-more',
					'elementor-button',
					'elementor-size-' . $settings['button_size'],
				)
			);
			?>
		<div class="pp-gallery-pagination">
			<a href="#" <?php echo wp_kses_post( $this->get_render_attribute_string( 'load-more-button' ) ); ?>>
				<span class="pp-button-loader"></span>
				<?php if ( $has_icon && 'before' === $settings['button_icon_position'] ) { ?>
					<span class="pp-gallery-load-more-icon pp-icon pp-no-trans">
						<?php
						if ( $is_new || $migrated ) {
							Icons_Manager::render_icon( $settings['select_load_more_icon'], array( 'aria-hidden' => 'true' ) );
						} elseif ( ! empty( $settings['load_more_icon'] ) ) {
							?>
							<i <?php echo wp_kses_post( $this->get_render_attribute_string( 'i' ) ); ?>></i>
							<?php
						}
						?>
					</span>
				<?php } ?>
				<span class="pp-gallery-load-more-text">
					<?php echo wp_kses_post( $settings['load_more_text'] ); ?>
				</span>
				<?php if ( $has_icon && 'after' === $settings['button_icon_position'] ) { ?>
					<span class="pp-gallery-load-more-icon pp-icon pp-no-trans">
						<?php
						if ( $is_new || $migrated ) {
							Icons_Manager::render_icon( $settings['select_load_more_icon'], array( 'aria-hidden' => 'true' ) );
						} elseif ( ! empty( $settings['load_more_icon'] ) ) {
							?>
							<i <?php echo wp_kses_post( $this->get_render_attribute_string( 'i' ) ); ?>></i>
							<?php
						}
						?>
					</span>
				<?php } ?>
			</a>
		</div>
			<?php
		}
	}

	/**
	 * Render image caption
	 *
	 * @param  int $id image ID.
	 * @return $html
	 */
	protected function render_image_caption( $id ) {
		$settings = $this->get_settings_for_display();

		if ( 'hide' === $settings['caption'] ) {
			return '';
		}

		$caption_type = $this->get_settings( 'caption_type' );

		$caption = Module::get_image_caption( $id, $caption_type );

		if ( '' === $caption ) {
			return '';
		}

		ob_start();
		?>
		<div class="pp-gallery-image-caption">
			<?php echo wp_kses_post( $caption ); ?>
		</div>
		<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	/**
	 * Render link icon
	 *
	 * @return $html
	 */
	protected function render_link_icon() {
		$settings = $this->get_settings_for_display();

		if ( ! isset( $settings['link_icon'] ) && ! Icons_Manager::is_migration_allowed() ) {
			// add old default
			$settings['link_icon'] = '';
		}

		$has_link_icon = ! empty( $settings['link_icon'] );

		if ( $has_link_icon ) {
			$this->add_render_attribute( 'i', 'class', $settings['link_icon'] );
			$this->add_render_attribute( 'i', 'aria-hidden', 'true' );
		}

		if ( ! $has_link_icon && ! empty( $settings['select_link_icon']['value'] ) ) {
			$has_link_icon = true;
		}
		$migrated_link_icon = isset( $settings['__fa4_migrated']['select_link_icon'] );
		$is_new_link_icon   = ! isset( $settings['link_icon'] ) && Icons_Manager::is_migration_allowed();

		if ( ! $has_link_icon ) {
			return '';
		}

		ob_start();
		?>
		<?php if ( $has_link_icon ) { ?>
		<div class="pp-gallery-image-icon-wrap pp-media-content">
			<span class="pp-gallery-image-icon pp-icon">
				<?php
				if ( $is_new_link_icon || $migrated_link_icon ) {
					Icons_Manager::render_icon( $settings['select_link_icon'], array( 'aria-hidden' => 'true' ) );
				} elseif ( ! empty( $settings['link_icon'] ) ) {
					?>
					<i <?php echo wp_kses_post( $this->get_render_attribute_string( 'i' ) ); ?>></i>
					<?php
				}
				?>
			</span>
		</div>
			<?php
		}
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	/**
	 * Render image overlay
	 *
	 * @param int $count image count
	 */
	protected function render_image_overlay( $count ) {
		$overlay_key = $this->get_repeater_setting_key( 'overlay', 'gallery_images', $count );

		$this->add_render_attribute(
			$overlay_key,
			'class',
			array(
				'pp-image-overlay',
				'pp-media-overlay',
			)
		);

		return '<div ' . $this->get_render_attribute_string( $overlay_key ) . '></div>';
	}

	/**
	 * Get filter ids
	 *
	 * @param  array $items gallery items array.
	 * @param  bool $get_labels get labels or not.
	 * @return $unique_ids
	 */
	protected function get_filter_ids( $items = array(), $get_labels = false ) {
		$ids    = array();
		$labels = array();

		if ( ! count( $items ) ) {
			return $ids;
		}

		foreach ( $items as $index => $item ) {
			$image_group  = $item['image_group'];
			$filter_ids   = array();
			$filter_label = '';

			foreach ( $image_group as $group ) {
				$ids[]        = $group['id'];
				$filter_ids[] = $group['id'];
				$filter_label = 'pp-group-' . ( $index + 1 );
			}

			$labels[ $filter_label ] = $filter_ids;
		}

		if ( ! count( $ids ) ) {
			return $ids;
		}

		$unique_ids = array_unique( $ids );

		if ( $get_labels ) {
			$filter_labels = array();

			foreach ( $unique_ids as $unique_id ) {
				if ( empty( $unique_id ) ) {
					continue;
				}

				foreach ( $labels as $key => $filter_ids ) {
					if ( in_array( $unique_id, $filter_ids ) ) {
						if ( isset( $filter_labels[ $unique_id ] ) ) {
							$filter_labels[ $unique_id ] = $filter_labels[ $unique_id ] . ' ' . str_replace( ' ', '-', strtolower( $key ) );
						} else {
							$filter_labels[ $unique_id ] = str_replace( ' ', '-', strtolower( $key ) );
						}
					}
				}
			}

			return $filter_labels;
		}

		return $unique_ids;
	}

	/**
	 * Get WordPress photos
	 *
	 * @return $photos
	 */
	protected function get_wordpress_photos() {
		$settings   = $this->get_settings_for_display();
		$image_size = $settings['image_size'];
		$photos     = array();
		$ids        = array();
		$photo_ids  = array();

		if ( 'standard' === $settings['gallery_type'] ) {
			if ( ! count( $settings['image_group_standard'] ) ) {
				return $photos;
			}

			$photos_arr = $settings['image_group_standard'];

			foreach ( $photos_arr as $ids ) {
				$photo_ids[] = $ids['id'];
			}
		} else {

			if ( ! count( $settings['gallery_images'] ) ) {
				return $photos;
			}

			$photo_ids = $this->get_filter_ids( $settings['gallery_images'] );
		}

		if ( 'date' === $settings['ordering'] ) {
			$photo_ids_by_date = array();

			foreach ( $photo_ids as $id ) {
				$date = get_post_time( 'U', '', $id );
				$photo_ids_by_date[ $date ] = $id;
			}

			$photo_ids = $photo_ids_by_date;

			krsort( $photo_ids );
		}

		foreach ( $photo_ids as $id ) {
			if ( empty( $id ) ) {
				continue;
			}

			$photo = $this->get_attachment_data( $id );

			if ( ! $photo ) {
				continue;
			}

			// Only use photos who have the sizes object.
			if ( isset( $photo->sizes ) ) {
				$data = new \stdClass();

				// Photo data object.
				$data->id          = $id;
				$data->alt         = $photo->alt;
				$data->caption     = $photo->caption;
				$data->description = $photo->description;
				$data->title       = $photo->title;

				// Photo src.
				if ( 'thumbnail' === $image_size && isset( $photo->sizes->thumbnail ) ) {
					$data->src = $photo->sizes->thumbnail->url;
				} elseif ( 'medium' === $image_size && isset( $photo->sizes->medium ) ) {
					$data->src = $photo->sizes->medium->url;
				} elseif ( isset( $photo->sizes->{$image_size} ) ) {
					$data->src = $photo->sizes->{$image_size}->url;
				} else {
					$data->src = $photo->sizes->full->url;
				}

				// Photo Link.
				if ( isset( $photo->sizes->large ) ) {
					$data->link = $photo->sizes->large->url;
				} else {
					$data->link = $photo->sizes->full->url;
				}

				$photos[ $id ] = $data;
			}
		}

		return $photos;
	}

	/**
	 * Get photos
	 *
	 * @return $ordered
	 */
	protected function get_photos() {
		$settings = $this->get_settings_for_display();
		$photos   = $this->get_wordpress_photos();
		$order    = $settings['ordering'];
		$ordered  = array();

		if ( is_array( $photos ) && 'random' === $order ) {
			$keys = array_keys( $photos );
			shuffle( $keys );

			foreach ( $keys as $key ) {
				$ordered[ $key ] = $photos[ $key ];
			}
		} else {
			$ordered = $photos;
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return $ordered;
		}

		if ( 'yes' === $settings['pagination'] ) {
			$per_page = $settings['images_per_page'];

			if ( empty( $per_page ) ) {
				return $ordered;
			}

			if ( $per_page > count( $ordered ) ) {
				return $ordered;
			}

			$count          = 0;
			$current_photos = array();

			foreach ( $ordered as $photo_id => $photo ) {
				if ( $count == $per_page ) {
					break;
				} else {
					$current_photos[ $photo_id ] = $photo;
					$count++;
				}
			}

			return $current_photos;
		}

		return $ordered;
	}

	/**
	 * Get ajax images
	 *
	 * @return $html
	 */
	public function ajax_get_images() {
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
			return;
		}

		ob_start();
		$this->render_gallery_items();
		$html = ob_get_contents();
		ob_end_clean();
		return trim( $html );
	}

	/**
	 * Get attachment data
	 *
	 * @param  int $id attachment id.
	 * @return $data
	 */
	protected function get_attachment_data( $id ) {
		$data = wp_prepare_attachment_for_js( $id );

		if ( gettype( $data ) == 'array' ) {
			return json_decode( json_encode( $data ) );
		}

		return $data;
	}

	public function ajax_get_gallery_images() {
		if ( ! isset( $_POST['pp_action'] ) || 'pp_gallery_get_images' != $_POST['pp_action'] ) {
			return;
		}

		// Tell WordPress this is an AJAX request.
		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		$response = array(
			'error' => false,
			'data'  => '',
		);

		$photos = $this->get_photos();

		echo wp_json_encode( $photos );
		die;
	}

	/**
	 * Render masonry script
	 *
	 * @access protected
	 */
	protected function render_editor_script() {
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function( $ ) {
				$( '.pp-image-gallery' ).each( function() {

					var $node_id 	= '<?php echo esc_attr( $this->get_id() ); ?>',
						$scope 		= $( '[data-id="' + $node_id + '"]' ),
						$gallery 	= $(this);

					if ( $gallery.closest( $scope ).length < 1 ) {
						return;
					}

					var $layout_mode = 'fitRows';

					if ( $gallery.hasClass('pp-image-gallery-masonry') ) {
						$layout_mode = 'masonry';
					}

					var filterItems = $scope.find( '.pp-gallery-filters .pp-gallery-filter' ),
						defaultFilter = '';

					$(filterItems).each(function() {
						if ( defaultFilter === '' || defaultFilter === undefined ) {
							defaultFilter = $(this).attr('data-default');
						}
					});

					var $isotope_args = {
							itemSelector:   '.pp-grid-item-wrap',
							layoutMode		: $layout_mode,
							percentPosition : true,
							filter          : defaultFilter,
						},
						$isotope_gallery = {};

					$gallery.imagesLoaded( function(e) {
						$isotope_gallery = $gallery.isotope( $isotope_args );

						$gallery.find('.pp-grid-item-wrap').resize( function() {
							$gallery.isotope( 'layout' );
						});
					});

					$('.pp-gallery-filters').on( 'click', '.pp-gallery-filter', function() {
						var $this = $(this),
							filterValue = $this.attr('data-filter');

						$this.siblings().removeClass('pp-active');
						$this.addClass('pp-active');

						$isotope_gallery.isotope({ filter: filterValue });
					});
				});
			});
		</script>
		<?php
	}
}
