<?php
/**
 * PowerPack Image Slider Widget.
 *
 * @package PPE
 */

namespace PowerpackElements\Modules\Gallery\Widgets;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Modules\Gallery\Module;
use PowerpackElements\Classes\PP_Config;
use PowerpackElements\Classes\PP_Helper;

// Elementor Classes.
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Repeater;
use Elementor\Control_Media;
use Elementor\Icons_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Core\Schemes\Typography as Scheme_Typography;
use Elementor\Modules\DynamicTags\Module as TagsModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Image Slider Widget
 */
class Image_Slider extends Powerpack_Widget {

	/**
	 * Retrieve image slider widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Image_Slider' );
	}

	/**
	 * Retrieve image slider widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Image_Slider' );
	}

	/**
	 * Retrieve image slider widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Image_Slider' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the image slider widget belongs to.
	 *
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'Image_Slider' );
	}

	/**
	 * Retrieve the list of scripts the image slider widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return array(
			'jquery-fancybox',
			'jquery-resize',
			'swiper',
			'powerpack-frontend',
		);
	}

	/**
	 * Retrieve the list of styles the image slider widget depended on.
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
	 * Register image slider widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 2.0.3
	 * @access protected
	 */
	protected function register_controls() {
		/* Content Tab */
		$this->register_content_gallery_controls();
		$this->register_content_thumbnails_controls();
		$this->register_content_feature_image_controls();
		$this->register_content_additional_options_controls();
		$this->register_content_help_docs_controls();

		/* Style Tab */
		$this->register_style_feature_image_controls();
		$this->register_style_image_captions_controls();
		$this->register_style_thumbnails_controls();
		$this->register_style_thumbnails_captions_controls();
		$this->register_style_lightbox_controls();
		$this->register_style_arrows_controls();
		$this->register_style_dots_controls();
		$this->register_style_fraction_controls();
	}

	/**
	 * Register Gallery Controls
	 *
	 * @access protected
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
			'gallery_images',
			array(
				'label'   => __( 'Add Images', 'powerpack' ),
				'type'    => Controls_Manager::GALLERY,
				'dynamic' => array(
					'active' => true,
				),
			)
		);

		$this->add_control(
			'effect',
			array(
				'type'      => Controls_Manager::SELECT,
				'label'     => __( 'Effect', 'powerpack' ),
				'default'   => 'slide',
				'options'   => array(
					'slide' => __( 'Slide', 'powerpack' ),
					'fade'  => __( 'Fade', 'powerpack' ),
				),
				'separator' => 'before',
			)
		);

		$this->add_control(
			'skin',
			array(
				'label'              => __( 'Layout', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'slideshow',
				'options'            => array(
					'slideshow' => __( 'Slideshow', 'powerpack' ),
					'carousel'  => __( 'Carousel', 'powerpack' ),
				),
				'prefix_class'       => 'pp-image-slider-',
				'render_type'        => 'template',
				'frontend_available' => true,
			)
		);

		$slides_per_view = range( 1, 10 );
		$slides_per_view = array_combine( $slides_per_view, $slides_per_view );

		$this->add_responsive_control(
			'slides_per_view',
			array(
				'type'           => Controls_Manager::SELECT,
				'label'          => __( 'Slides Per View', 'powerpack' ),
				'options'        => $slides_per_view,
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '2',
				'condition'      => array(
					'effect' => 'slide',
					'skin!'  => 'slideshow',
				),
			)
		);

		$this->add_responsive_control(
			'slides_to_scroll',
			array(
				'type'           => Controls_Manager::SELECT,
				'label'          => __( 'Slides to Scroll', 'powerpack' ),
				'description'    => __( 'Set how many slides are scrolled per swipe.', 'powerpack' ),
				'options'        => $slides_per_view,
				'default'        => 1,
				'tablet_default' => 1,
				'mobile_default' => 1,
				'condition'      => array(
					'effect' => 'slide',
					'skin!'  => 'slideshow',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Thumbnails Controls
	 *
	 * @return void
	 */
	protected function register_content_thumbnails_controls() {
		/**
		 * Content Tab: Thumbnails
		 */
		$this->start_controls_section(
			'section_thumbnails_settings',
			array(
				'label' => __( 'Thumbnails', 'powerpack' ),
			)
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'    => 'thumbnail',
				'label'   => __( 'Image Size', 'powerpack' ),
				'default' => 'thumbnail',
				'exclude' => array( 'custom' ),
			)
		);

		$this->add_control(
			'equal_height',
			[
				'label'                 => __( 'Equal Height', 'powerpack' ),
				'type'                  => Controls_Manager::SELECT,
				'default'               => 'no',
				'options'               => [
					'yes'   => __( 'Yes', 'powerpack' ),
					'no'    => __( 'No', 'powerpack' ),
				],
			]
		);

		$this->add_responsive_control(
			'custom_height',
			[
				'label'                 => __( 'Custom Height', 'powerpack' ),
				'type'                  => Controls_Manager::SLIDER,
				'default'               => [
					'size' => 300,
					'unit' => 'px',
				],
				'size_units'            => [ 'px' ],
				'range'                 => [
					'px' => [
						'step' => 1,
						'min'  => 100,
						'max' => 800,
					],
				],
				'tablet_default'        => [
					'unit' => 'px',
				],
				'mobile_default'        => [
					'unit' => 'px',
				],
				'selectors'             => [
					'{{WRAPPER}} .pp-thumbs-equal-height .pp-image-slider-thumb-item' => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition'             => [
					'equal_height'  => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => __( 'Columns', 'powerpack' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '3',
				'tablet_default' => '6',
				'mobile_default' => '4',
				'options'        => array(
					'1'  => '1',
					'2'  => '2',
					'3'  => '3',
					'4'  => '4',
					'5'  => '5',
					'6'  => '6',
					'7'  => '7',
					'8'  => '8',
					'9'  => '9',
					'10' => '10',
					'11' => '11',
					'12' => '12',
				),
				'prefix_class'   => 'elementor-grid%s-',
				'condition'      => array(
					'skin' => 'slideshow',
				),
			)
		);

		$this->add_control(
			'thumbnails_caption',
			array(
				'type'    => Controls_Manager::SELECT,
				'label'   => __( 'Caption', 'powerpack' ),
				'default' => '',
				'options' => array(
					''            => __( 'None', 'powerpack' ),
					'caption'     => __( 'Caption', 'powerpack' ),
					'title'       => __( 'Title', 'powerpack' ),
					'description' => __( 'Description', 'powerpack' ),
				),
			)
		);

		$this->add_control(
			'carousel_link_to',
			array(
				'label'     => __( 'Link to', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'none',
				'options'   => array(
					'none'   => __( 'None', 'powerpack' ),
					'file'   => __( 'Media File', 'powerpack' ),
					'custom' => __( 'Custom URL', 'powerpack' ),
				),
				'condition' => array(
					'skin' => 'carousel',
				),
			)
		);

		$this->add_control(
			'carousel_link_important_note',
			array(
				'label'           => '',
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'Add custom link in media uploader.', 'powerpack' ),
				'content_classes' => 'pp-editor-info',
				'condition'       => array(
					'skin'             => 'carousel',
					'carousel_link_to' => 'custom',
				),
			)
		);

		$this->add_control(
			'carousel_link_target',
			array(
				'label'      => __( 'Link Target', 'powerpack' ),
				'type'       => Controls_Manager::SELECT,
				'default'    => '_blank',
				'options'    => array(
					'_self'  => __( 'Same Window', 'powerpack' ),
					'_blank' => __( 'New Window', 'powerpack' ),
				),
				'conditions' => array(
					'relation' => 'and',
					'terms'    => array(
						array(
							'name'     => 'skin',
							'operator' => '==',
							'value'    => 'carousel',
						),
						array(
							'relation' => 'or',
							'terms'    => array(
								array(
									'name'     => 'carousel_link_to',
									'operator' => '==',
									'value'    => 'custom',
								),
								array(
									'relation' => 'and',
									'terms'    => array(
										array(
											'name'     => 'carousel_link_to',
											'operator' => '==',
											'value'    => 'file',
										),
										array(
											'name'     => 'carousel_open_lightbox',
											'operator' => '==',
											'value'    => 'no',
										),
									),
								),
							),
						),
					),
				),
			)
		);

		$this->add_control(
			'carousel_open_lightbox',
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
					'skin'             => 'carousel',
					'carousel_link_to' => 'file',
				),
			)
		);

		$this->add_control(
			'carousel_lightbox_library',
			array(
				'label'     => __( 'Lightbox Library', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array(
					''         => __( 'Elementor', 'powerpack' ),
					'fancybox' => __( 'Fancybox', 'powerpack' ),
				),
				'condition' => array(
					'skin'                    => 'carousel',
					'carousel_link_to'        => 'file',
					'carousel_open_lightbox!' => 'no',
				),
			)
		);

		$this->add_control(
			'thumbnails_lightbox_caption',
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
					'skin'                      => 'carousel',
					'carousel_link_to'          => 'file',
					'carousel_open_lightbox!'   => 'no',
					'carousel_lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'carousel_lightbox_loop',
			array(
				'label'        => __( 'Loop', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'skin'                      => 'carousel',
					'carousel_link_to'          => 'file',
					'carousel_open_lightbox!'   => 'no',
					'carousel_lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'carousel_lightbox_arrows',
			array(
				'label'        => __( 'Arrows', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'skin'                      => 'carousel',
					'carousel_link_to'          => 'file',
					'carousel_open_lightbox!'   => 'no',
					'carousel_lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'carousel_lightbox_slides_counter',
			array(
				'label'        => __( 'Slides Counter', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'skin'                      => 'carousel',
					'carousel_link_to'          => 'file',
					'carousel_open_lightbox!'   => 'no',
					'carousel_lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'carousel_lightbox_keyboard',
			array(
				'label'        => __( 'Keyboard Navigation', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'skin'                      => 'carousel',
					'carousel_link_to'          => 'file',
					'carousel_open_lightbox!'   => 'no',
					'carousel_lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'carousel_lightbox_toolbar',
			array(
				'label'        => __( 'Toolbar', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'skin'                      => 'carousel',
					'carousel_link_to'          => 'file',
					'carousel_open_lightbox!'   => 'no',
					'carousel_lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'carousel_lightbox_toolbar_buttons',
			array(
				'label'     => __( 'Toolbar Buttons', 'powerpack' ),
				'type'      => Controls_Manager::SELECT2,
				'default'   => array( 'zoom', 'slideShow', 'thumbs', 'close' ),
				'options'   => array(
					'zoom'       => __( 'Zoom', 'powerpack' ),
					'share'      => __( 'Share', 'powerpack' ),
					'slideShow'  => __( 'SlideShow', 'powerpack' ),
					'fullScreen' => __( 'Full Screen', 'powerpack' ),
					'download'   => __( 'Download', 'powerpack' ),
					'thumbs'     => __( 'Thumbs', 'powerpack' ),
					'close'      => __( 'Close', 'powerpack' ),
				),
				'multiple'  => true,
				'condition' => array(
					'skin'                      => 'carousel',
					'carousel_link_to'          => 'file',
					'carousel_open_lightbox!'   => 'no',
					'carousel_lightbox_library' => 'fancybox',
					'toolbar'                   => 'yes',
				),
			)
		);

		$this->add_control(
			'carousel_lightbox_thumbs_auto_start',
			array(
				'label'        => __( 'Thumbs Auto Start', 'powerpack' ),
				'description'  => __( 'Display thumbnails on lightbox opening', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'skin'                      => 'carousel',
					'carousel_link_to'          => 'file',
					'carousel_open_lightbox!'   => 'no',
					'carousel_lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'carousel_lightbox_thumbs_position',
			array(
				'label'     => __( 'Thumbs Position', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array(
					''       => __( 'Default', 'powerpack' ),
					'bottom' => __( 'Bottom', 'powerpack' ),
				),
				'condition' => array(
					'skin'                      => 'carousel',
					'carousel_link_to'          => 'file',
					'carousel_open_lightbox!'   => 'no',
					'carousel_lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'carousel_lightbox_animation',
			array(
				'label'       => __( 'Animation', 'powerpack' ),
				'description' => __( 'Open/Close animation', 'powerpack' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'zoom',
				'options'     => array(
					''            => __( 'None', 'powerpack' ),
					'fade'        => __( 'Fade', 'powerpack' ),
					'zoom'        => __( 'Zoom', 'powerpack' ),
					'zoom-in-out' => __( 'Zoom in Out', 'powerpack' ),
				),
				'condition'   => array(
					'skin'                      => 'carousel',
					'carousel_link_to'          => 'file',
					'carousel_open_lightbox!'   => 'no',
					'carousel_lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'carousel_lightbox_transition_effect',
			array(
				'label'       => __( 'Transition Effect', 'powerpack' ),
				'description' => __( 'Transition effect between slides', 'powerpack' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'fade',
				'options'     => array(
					''            => __( 'None', 'powerpack' ),
					'fade'        => __( 'Fade', 'powerpack' ),
					'slide'       => __( 'Slide', 'powerpack' ),
					'circular'    => __( 'Circular', 'powerpack' ),
					'tube'        => __( 'Tube', 'powerpack' ),
					'zoom-in-out' => __( 'Zoom in Out', 'powerpack' ),
					'rotate'      => __( 'Rotate', 'powerpack' ),
				),
				'condition'   => array(
					'skin'                      => 'carousel',
					'carousel_link_to'          => 'file',
					'carousel_open_lightbox!'   => 'no',
					'carousel_lightbox_library' => 'fancybox',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Feature Image Controls
	 *
	 * @return void
	 */
	protected function register_content_feature_image_controls() {
		/**
		 * Content Tab: Feature Image
		 */
		$this->start_controls_section(
			'section_feature_image',
			array(
				'label'     => __( 'Feature Image', 'powerpack' ),
				'condition' => array(
					'skin' => 'slideshow',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'      => 'image',
				'label'     => __( 'Image Size', 'powerpack' ),
				'default'   => 'full',
				'exclude'   => array( 'custom' ),
				'condition' => array(
					'skin' => 'slideshow',
				),
			)
		);

		$this->add_control(
			'feature_image_caption',
			array(
				'type'      => Controls_Manager::SELECT,
				'label'     => __( 'Caption', 'powerpack' ),
				'default'   => '',
				'options'   => array(
					''            => __( 'None', 'powerpack' ),
					'caption'     => __( 'Caption', 'powerpack' ),
					'title'       => __( 'Title', 'powerpack' ),
					'description' => __( 'Description', 'powerpack' ),
				),
				'condition' => array(
					'skin' => 'slideshow',
				),
			)
		);

		$this->add_control(
			'link_to',
			array(
				'label'     => __( 'Link to', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'none',
				'options'   => array(
					'none'   => __( 'None', 'powerpack' ),
					'file'   => __( 'Media File', 'powerpack' ),
					'custom' => __( 'Custom URL', 'powerpack' ),
				),
				'condition' => array(
					'skin' => 'slideshow',
				),
			)
		);

		$this->add_control(
			'link_important_note',
			array(
				'label'           => '',
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'Add custom link in media uploader.', 'powerpack' ),
				'content_classes' => 'pp-editor-info',
				'condition'       => array(
					'skin'    => 'slideshow',
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
					'relation' => 'and',
					'terms'    => array(
						array(
							'name'     => 'skin',
							'operator' => '==',
							'value'    => 'slideshow',
						),
						array(
							'relation' => 'or',
							'terms'    => array(
								array(
									'name'     => 'link_to',
									'operator' => '==',
									'value'    => 'custom',
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
					),
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
					'skin'    => 'slideshow',
					'link_to' => 'file',
				),
			)
		);

		$this->add_control(
			'lightbox_library',
			array(
				'label'     => __( 'Lightbox Library', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array(
					''         => __( 'Elementor', 'powerpack' ),
					'fancybox' => __( 'Fancybox', 'powerpack' ),
				),
				'condition' => array(
					'skin'           => 'slideshow',
					'link_to'        => 'file',
					'open_lightbox!' => 'no',
				),
			)
		);

		$this->add_control(
			'feature_image_lightbox_caption',
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
					'skin'             => 'slideshow',
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'loop',
			array(
				'label'        => __( 'Loop', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'lightbox_arrows',
			array(
				'label'        => __( 'Arrows', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'slides_counter',
			array(
				'label'        => __( 'Slides Counter', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'keyboard',
			array(
				'label'        => __( 'Keyboard Navigation', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'toolbar',
			array(
				'label'        => __( 'Toolbar', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'toolbar_buttons',
			array(
				'label'     => __( 'Toolbar Buttons', 'powerpack' ),
				'type'      => Controls_Manager::SELECT2,
				'default'   => array( 'zoom', 'slideShow', 'thumbs', 'close' ),
				'options'   => array(
					'zoom'       => __( 'Zoom', 'powerpack' ),
					'share'      => __( 'Share', 'powerpack' ),
					'slideShow'  => __( 'SlideShow', 'powerpack' ),
					'fullScreen' => __( 'Full Screen', 'powerpack' ),
					'download'   => __( 'Download', 'powerpack' ),
					'thumbs'     => __( 'Thumbs', 'powerpack' ),
					'close'      => __( 'Close', 'powerpack' ),
				),
				'multiple'  => true,
				'condition' => array(
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
				'label'        => __( 'Thumbs Auto Start', 'powerpack' ),
				'description'  => __( 'Display thumbnails on lightbox opening', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'thumbs_position',
			array(
				'label'     => __( 'Thumbs Position', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array(
					''       => __( 'Default', 'powerpack' ),
					'bottom' => __( 'Bottom', 'powerpack' ),
				),
				'condition' => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'lightbox_animation',
			array(
				'label'       => __( 'Animation', 'powerpack' ),
				'description' => __( 'Open/Close animation', 'powerpack' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'zoom',
				'options'     => array(
					''            => __( 'None', 'powerpack' ),
					'fade'        => __( 'Fade', 'powerpack' ),
					'zoom'        => __( 'Zoom', 'powerpack' ),
					'zoom-in-out' => __( 'Zoom in Out', 'powerpack' ),
				),
				'condition'   => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->add_control(
			'transition_effect',
			array(
				'label'       => __( 'Transition Effect', 'powerpack' ),
				'description' => __( 'Transition effect between slides', 'powerpack' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'fade',
				'options'     => array(
					''            => __( 'None', 'powerpack' ),
					'fade'        => __( 'Fade', 'powerpack' ),
					'slide'       => __( 'Slide', 'powerpack' ),
					'circular'    => __( 'Circular', 'powerpack' ),
					'tube'        => __( 'Tube', 'powerpack' ),
					'zoom-in-out' => __( 'Zoom in Out', 'powerpack' ),
					'rotate'      => __( 'Rotate', 'powerpack' ),
				),
				'condition'   => array(
					'link_to'          => 'file',
					'open_lightbox!'   => 'no',
					'lightbox_library' => 'fancybox',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Additional Options Controls
	 *
	 * @return void
	 */
	protected function register_content_additional_options_controls() {
		/**
		 * Content Tab: Additional Options
		 */
		$this->start_controls_section(
			'section_additional_options',
			array(
				'label' => __( 'Additional Options', 'powerpack' ),
			)
		);

		$this->add_control(
			'animation_speed',
			array(
				'label'   => __( 'Animation Speed', 'powerpack' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 600,
			)
		);

		$this->add_control(
			'autoplay',
			array(
				'label'        => __( 'Autoplay', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'autoplay_speed',
			array(
				'label'     => __( 'Autoplay Speed', 'powerpack' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 3000,
				'condition' => array(
					'autoplay' => 'yes',
				),
			)
		);

		$this->add_control(
			'pause_on_hover',
			array(
				'label'              => __( 'Pause on Hover', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'label_on'           => __( 'Yes', 'powerpack' ),
				'label_off'          => __( 'No', 'powerpack' ),
				'return_value'       => 'yes',
				'frontend_available' => true,
				'condition'          => array(
					'autoplay' => 'yes',
				),
			)
		);

		$this->add_control(
			'infinite_loop',
			array(
				'label'              => __( 'Infinite Loop', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'label_on'           => __( 'Yes', 'powerpack' ),
				'label_off'          => __( 'No', 'powerpack' ),
				'return_value'       => 'yes',
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'adaptive_height',
			array(
				'label'        => __( 'Adaptive Height', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'navigation_heading',
			array(
				'label'     => __( 'Navigation', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'arrows',
			array(
				'label'        => __( 'Arrows', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'dots',
			array(
				'label'        => __( 'Pagination', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'no',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'pagination_type',
			array(
				'label'     => __( 'Pagination Type', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'bullets',
				'options'   => array(
					'bullets'  => __( 'Dots', 'powerpack' ),
					'fraction' => __( 'Fraction', 'powerpack' ),
				),
				'condition' => array(
					'dots' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Help Docs Controls
	 *
	 * @return void
	 */
	protected function register_content_help_docs_controls() {

		$help_docs = PP_Config::get_widget_help_links( 'Image_Slider' );

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
	 * Register Feature Image Controls in Style Tab
	 *
	 * @access protected
	 */
	protected function register_style_feature_image_controls() {
		/**
		 * Style Tab: Feature Image
		 */
		$this->start_controls_section(
			'section_feature_image_style',
			array(
				'label'     => __( 'Feature Image', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'skin' => 'slideshow',
				),
			)
		);

		$this->add_control(
			'feature_image_align',
			array(
				'label'        => __( 'Align', 'powerpack' ),
				'type'         => Controls_Manager::CHOOSE,
				'label_block'  => false,
				'toggle'       => false,
				'default'      => 'left',
				'options'      => array(
					'left'  => array(
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-h-align-left',
					),
					'top'   => array(
						'title' => __( 'Top', 'powerpack' ),
						'icon'  => 'eicon-v-align-top',
					),
					'right' => array(
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'prefix_class' => 'pp-image-slider-align-',
				'condition'    => array(
					'skin' => 'slideshow',
				),
			)
		);

		$this->add_control(
			'feature_image_stack',
			array(
				'label'        => __( 'Stack On', 'powerpack' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'tablet',
				'options'      => array(
					'tablet' => __( 'Tablet', 'powerpack' ),
					'mobile' => __( 'Mobile', 'powerpack' ),
				),
				'prefix_class' => 'pp-image-slider-stack-',
				'condition'    => array(
					'skin'                 => 'slideshow',
					'feature_image_align!' => 'top',
				),
			)
		);

		$this->add_responsive_control(
			'feature_image_width',
			array(
				'label'      => __( 'Width', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%' ),
				'range'      => array(
					'%' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'size' => 70,
				),
				'selectors'  => array(
					'{{WRAPPER}}.pp-image-slider-align-left .pp-image-slider-wrap' => 'width: {{SIZE}}%',
					'{{WRAPPER}}.pp-image-slider-align-right .pp-image-slider-wrap' => 'width: {{SIZE}}%',
					'{{WRAPPER}}.pp-image-slider-align-right .pp-image-slider-thumb-pagination' => 'width: calc(100% - {{SIZE}}%)',
					'{{WRAPPER}}.pp-image-slider-align-left .pp-image-slider-thumb-pagination' => 'width: calc(100% - {{SIZE}}%)',
				),
				'condition'  => array(
					'skin' => 'slideshow',
					'feature_image_align!' => 'top',
				),
			)
		);

		$this->add_responsive_control(
			'feature_image_spacing',
			array(
				'label'     => __( 'Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 200,
					),
				),
				'default'   => array(
					'size' => 20,
				),
				'selectors' => array(
					'{{WRAPPER}}.pp-image-slider-align-left .pp-image-slider-container,
                    {{WRAPPER}}.pp-image-slider-align-right .pp-image-slider-container' => 'margin-left: -{{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.pp-image-slider-align-left .pp-image-slider-container > *,
                    {{WRAPPER}}.pp-image-slider-align-right .pp-image-slider-container > *' => 'padding-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.pp-image-slider-align-top .pp-image-slider-wrap' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'(tablet){{WRAPPER}}.pp-image-slider-stack-tablet .pp-image-slider-wrap' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'(mobile){{WRAPPER}}.pp-image-slider-stack-mobile .pp-image-slider-wrap' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'skin' => 'slideshow',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'feature_image_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-image-slider',
				'separator'   => 'before',
				'condition'   => array(
					'skin' => 'slideshow',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'feature_image_box_shadow',
				'selector'  => '{{WRAPPER}} .pp-image-slider',
				'separator' => 'before',
				'condition' => array(
					'skin' => 'slideshow',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array(
				'name'      => 'feature_image_css_filters',
				'selector'  => '{{WRAPPER}} .pp-image-slider img',
				'condition' => array(
					'skin' => 'slideshow',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Feature Image Captions Controls in Style Tab
	 *
	 * @access protected
	 */
	protected function register_style_image_captions_controls() {
		/**
		 * Style Tab: Feature Image Captions
		 */
		$this->start_controls_section(
			'section_feature_image_captions_style',
			array(
				'label'     => __( 'Feature Image Captions', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'skin'                   => 'slideshow',
					'feature_image_caption!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'feature_image_captions_vertical_align',
			array(
				'label'                => __( 'Vertical Align', 'powerpack' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => array(
					'top'    => array(
						'title' => __( 'Top', 'powerpack' ),
						'icon'  => 'eicon-v-align-top',
					),
					'middle' => array(
						'title' => __( 'Middle', 'powerpack' ),
						'icon'  => 'eicon-v-align-middle',
					),
					'bottom' => array(
						'title' => __( 'Bottom', 'powerpack' ),
						'icon'  => 'eicon-v-align-bottom',
					),
				),
				'default'              => 'bottom',
				'selectors'            => array(
					'{{WRAPPER}} .pp-image-slider-slide .pp-image-slider-content' => 'justify-content: {{VALUE}};',
				),
				'selectors_dictionary' => array(
					'top'    => 'flex-start',
					'bottom' => 'flex-end',
					'middle' => 'center',
				),
				'condition'            => array(
					'skin'                   => 'slideshow',
					'feature_image_caption!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'feature_image_captions_horizontal_align',
			array(
				'label'                => __( 'Horizontal Align', 'powerpack' ),
				'type'                 => Controls_Manager::CHOOSE,
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
				'selectors'            => array(
					'{{WRAPPER}} .pp-image-slider-slide .pp-image-slider-content' => 'align-items: {{VALUE}};',
				),
				'selectors_dictionary' => array(
					'left'    => 'flex-start',
					'right'   => 'flex-end',
					'center'  => 'center',
					'justify' => 'stretch',
				),
				'condition'            => array(
					'skin'                   => 'slideshow',
					'feature_image_caption!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'feature_image_captions_align',
			array(
				'label'     => __( 'Text Align', 'powerpack' ),
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
					'{{WRAPPER}} .pp-image-slider-slide .pp-image-slider-caption' => 'text-align: {{VALUE}};',
				),
				'condition' => array(
					'skin'                   => 'slideshow',
					'feature_image_captions_horizontal_align' => 'justify',
					'feature_image_caption!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'feature_image_captions_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-image-slider-slide .pp-image-slider-caption',
				'condition' => array(
					'skin'                   => 'slideshow',
					'feature_image_caption!' => '',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_feature_image_captions_style' );

		$this->start_controls_tab(
			'tab_feature_image_captions_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'skin'                   => 'slideshow',
					'feature_image_caption!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'feature_image_captions_background',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .pp-image-slider-slide .pp-image-slider-caption',
				'exclude'   => array(
					'image',
				),
				'condition' => array(
					'skin'                   => 'slideshow',
					'feature_image_caption!' => '',
				),
			)
		);

		$this->add_control(
			'feature_image_captions_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-image-slider-slide .pp-image-slider-caption' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'skin'                   => 'slideshow',
					'feature_image_caption!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'feature_image_captions_border_normal',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-image-slider-slide .pp-image-slider-caption',
				'condition'   => array(
					'skin'                   => 'slideshow',
					'feature_image_caption!' => '',
				),
			)
		);

		$this->add_control(
			'feature_image_captions_border_radius_normal',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-image-slider-slide .pp-image-slider-caption' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'skin'                   => 'slideshow',
					'feature_image_caption!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'feature_image_captions_margin',
			array(
				'label'      => __( 'Margin', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-image-slider-slide .pp-image-slider-caption' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'skin'                   => 'slideshow',
					'feature_image_caption!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'feature_image_captions_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-image-slider-slide .pp-image-slider-caption' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'skin'                   => 'slideshow',
					'feature_image_caption!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'      => 'feature_image_text_shadow',
				'selector'  => '{{WRAPPER}} .pp-image-slider-slide .pp-image-slider-caption',
				'condition' => array(
					'skin'                   => 'slideshow',
					'feature_image_caption!' => '',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_feature_image_captions_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'skin'                   => 'slideshow',
					'feature_image_caption!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'feature_image_captions_background_hover',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .pp-image-slider-slide:hover .pp-image-slider-caption',
				'exclude'   => array(
					'image',
				),
				'condition' => array(
					'skin'                   => 'slideshow',
					'feature_image_caption!' => '',
				),
			)
		);

		$this->add_control(
			'feature_image_captions_text_color_hover',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-image-slider-slide:hover .pp-image-slider-caption' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'skin'                   => 'slideshow',
					'feature_image_caption!' => '',
				),
			)
		);

		$this->add_control(
			'feature_image_captions_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-image-slider-slide:hover .pp-image-slider-caption' => 'border-color: {{VALUE}}',
				),
				'condition' => array(
					'skin'                   => 'slideshow',
					'feature_image_caption!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'      => 'feature_image_text_shadow_hover',
				'selector'  => '{{WRAPPER}} .pp-image-slider-slide:hover .pp-image-slider-caption',
				'condition' => array(
					'skin'                   => 'slideshow',
					'feature_image_caption!' => '',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'feature_image_captions_blend_mode',
			array(
				'label'     => __( 'Blend Mode', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					''            => __( 'Normal', 'powerpack' ),
					'multiply'    => 'Multiply',
					'screen'      => 'Screen',
					'overlay'     => 'Overlay',
					'darken'      => 'Darken',
					'lighten'     => 'Lighten',
					'color-dodge' => 'Color Dodge',
					'saturation'  => 'Saturation',
					'color'       => 'Color',
					'difference'  => 'Difference',
					'exclusion'   => 'Exclusion',
					'hue'         => 'Hue',
					'luminosity'  => 'Luminosity',
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-image-slider-slide .pp-image-slider-caption' => 'mix-blend-mode: {{VALUE}}',
				),
				'separator' => 'before',
				'condition' => array(
					'skin'                   => 'slideshow',
					'feature_image_caption!' => '',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Thumbnails Controls in Style Tab
	 *
	 * @access protected
	 */
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
			'thumbnails_alignment',
			array(
				'label'                => __( 'Alignment', 'powerpack' ),
				'type'                 => Controls_Manager::CHOOSE,
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
				'default'              => 'left',
				'selectors'            => array(
					'{{WRAPPER}} .pp-image-slider-thumb-pagination' => 'justify-content: {{VALUE}};',
				),
				'selectors_dictionary' => array(
					'left'   => 'flex-start',
					'right'  => 'flex-end',
					'center' => 'center',
				),
				'condition'            => array(
					'skin' => 'slideshow',
				),
			)
		);

		$this->add_control(
			'thumbnail_images_divider',
			array(
				'type'      => Controls_Manager::DIVIDER,
				'condition' => array(
					'skin' => 'slideshow',
				),
			)
		);

		$this->add_control(
			'thumbnail_images_heading',
			array(
				'label' => __( 'Images', 'powerpack' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_responsive_control(
			'thumbnails_horizontal_spacing',
			array(
				'label'       => __( 'Column Spacing', 'powerpack' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array(
					'px' => array(
						'min' => 0,
						'max' => 200,
					),
				),
				'default'     => array(
					'size' => '',
				),
				'render_type' => 'template',
				'selectors'   => array(
					'{{WRAPPER}} .pp-image-slider-thumb-pagination' => '--grid-column-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'thumbnails_vertical_spacing',
			array(
				'label'     => __( 'Row Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 200,
					),
				),
				'default'   => array(
					'size' => '',
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-image-slider-thumb-pagination' => '--grid-row-gap: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'skin' => 'slideshow',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_thumbnails_style' );

		$this->start_controls_tab(
			'tab_thumbnails_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'thumbnails_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-image-slider-thumb-item',
			)
		);

		$this->add_control(
			'thumbnails_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-image-slider-thumb-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'thumbnails_scale',
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
					'{{WRAPPER}} .pp-image-slider-thumb-image img' => 'transform: scale({{SIZE}});',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'thumbnails_box_shadow',
				'selector'  => '{{WRAPPER}} .pp-image-slider-thumb-item',
				'condition' => array(
					'skin' => 'slideshow',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'thumbnails_css_filters',
				'selector' => '{{WRAPPER}} .pp-image-slider-thumb-image img',
			)
		);

		$this->add_control(
			'thumbnails_image_filter',
			array(
				'label'        => __( 'Image Filter', 'powerpack' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'normal',
				'options'      => Module::get_image_filters(),
				'prefix_class' => 'pp-ins-',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_thumbnails_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'thumbnails_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-image-slider-thumb-item:hover' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'thumbnails_scale_hover',
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
					'{{WRAPPER}} .pp-image-slider-thumb-item:hover .pp-image-slider-thumb-image img' => 'transform: scale({{SIZE}});',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'thumbnails_box_shadow_hover',
				'selector'  => '{{WRAPPER}} .pp-image-slider-thumb-item:hover',
				'condition' => array(
					'skin' => 'slideshow',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'thumbnails_css_filters_hover',
				'selector' => '{{WRAPPER}} .pp-image-slider-thumb-item:hover .pp-image-slider-thumb-image img',
			)
		);

		$this->add_control(
			'thumbnails_image_filter_hover',
			array(
				'label'        => __( 'Image Filter', 'powerpack' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'normal',
				'options'      => Module::get_image_filters(),
				'prefix_class' => 'pp-ins-hover-',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_thumbnails_active',
			array(
				'label' => __( 'Active', 'powerpack' ),
			)
		);

		$this->add_control(
			'thumbnails_border_color_active',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-active-slide .pp-image-slider-thumb-item' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'thumbnails_scale_active',
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
					'{{WRAPPER}} .pp-active-slide .pp-image-slider-thumb-image img' => 'transform: scale({{SIZE}});',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'thumbnails_box_shadow_active',
				'selector'  => '{{WRAPPER}} .pp-active-slide .pp-image-slider-thumb-item',
				'condition' => array(
					'skin' => 'slideshow',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'thumbnails_css_filters_active',
				'selector' => '{{WRAPPER}} .pp-active-slide .pp-image-slider-thumb-image img',
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'thumbnail_overlay_heading',
			array(
				'label'     => __( 'Overlay', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->start_controls_tabs( 'tabs_thumbnails_overlay_style' );

		$this->start_controls_tab(
			'tab_thumbnails_overlay_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'thumbnails_overlay_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .pp-image-slider-thumb-overlay',
				'exclude'  => array(
					'image',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_thumbnails_overlay_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'thumbnails_overlay_background_hover',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .pp-image-slider-thumb-item:hover .pp-image-slider-thumb-overlay',
				'exclude'  => array(
					'image',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_thumbnails_overlay_active',
			array(
				'label'     => __( 'Active', 'powerpack' ),
				'condition' => array(
					'skin' => 'slideshow',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'thumbnails_overlay_background_active',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .pp-active-slide .pp-image-slider-thumb-overlay',
				'exclude'   => array(
					'image',
				),
				'condition' => array(
					'skin' => 'slideshow',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'feature_image_overlay_blend_mode',
			array(
				'label'     => __( 'Blend Mode', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					''            => __( 'Normal', 'powerpack' ),
					'multiply'    => 'Multiply',
					'screen'      => 'Screen',
					'overlay'     => 'Overlay',
					'darken'      => 'Darken',
					'lighten'     => 'Lighten',
					'color-dodge' => 'Color Dodge',
					'saturation'  => 'Saturation',
					'color'       => 'Color',
					'difference'  => 'Difference',
					'exclusion'   => 'Exclusion',
					'hue'         => 'Hue',
					'luminosity'  => 'Luminosity',
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-image-slider-thumb-overlay' => 'mix-blend-mode: {{VALUE}}',
				),
				'separator' => 'before',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Thumbnails Captions Controls in Style Tab
	 *
	 * @access protected
	 */
	protected function register_style_thumbnails_captions_controls() {
		/**
		 * Style Tab: Thumbnails Captions
		 */
		$this->start_controls_section(
			'section_thumbnails_captions_style',
			array(
				'label'     => __( 'Thumbnails Captions', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'thumbnails_caption!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'thumbnails_captions_vertical_align',
			array(
				'label'                => __( 'Vertical Align', 'powerpack' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => array(
					'top'    => array(
						'title' => __( 'Top', 'powerpack' ),
						'icon'  => 'eicon-v-align-top',
					),
					'middle' => array(
						'title' => __( 'Middle', 'powerpack' ),
						'icon'  => 'eicon-v-align-middle',
					),
					'bottom' => array(
						'title' => __( 'Bottom', 'powerpack' ),
						'icon'  => 'eicon-v-align-bottom',
					),
				),
				'default'              => 'bottom',
				'selectors'            => array(
					'{{WRAPPER}} .pp-image-slider-thumb-item-wrap .pp-image-slider-content' => 'justify-content: {{VALUE}};',
				),
				'selectors_dictionary' => array(
					'top'    => 'flex-start',
					'bottom' => 'flex-end',
					'middle' => 'center',
				),
				'condition'            => array(
					'thumbnails_caption!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'thumbnails_captions_horizontal_align',
			array(
				'label'                => __( 'Horizontal Align', 'powerpack' ),
				'type'                 => Controls_Manager::CHOOSE,
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
				'selectors'            => array(
					'{{WRAPPER}} .pp-image-slider-thumb-item-wrap .pp-image-slider-content' => 'align-items: {{VALUE}};',
				),
				'selectors_dictionary' => array(
					'left'    => 'flex-start',
					'right'   => 'flex-end',
					'center'  => 'center',
					'justify' => 'stretch',
				),
				'condition'            => array(
					'thumbnails_caption!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'thumbnails_captions_align',
			array(
				'label'     => __( 'Text Align', 'powerpack' ),
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
					'{{WRAPPER}} .pp-image-slider-thumb-item-wrap .pp-image-slider-caption' => 'text-align: {{VALUE}};',
				),
				'condition' => array(
					'thumbnails_captions_horizontal_align' => 'justify',
					'thumbnails_caption!'                  => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'thumbnails_captions_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-image-slider-thumb-item-wrap .pp-image-slider-caption',
				'condition' => array(
					'thumbnails_caption!' => '',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_thumbnails_captions_style' );

		$this->start_controls_tab(
			'tab_thumbnails_captions_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'thumbnails_caption!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'thumbnails_captions_background',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .pp-image-slider-thumb-item-wrap .pp-image-slider-caption',
				'exclude'   => array(
					'image',
				),
				'condition' => array(
					'thumbnails_caption!' => '',
				),
			)
		);

		$this->add_control(
			'thumbnails_captions_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-image-slider-thumb-item-wrap .pp-image-slider-caption' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'thumbnails_caption!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'thumbnails_captions_border_normal',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-image-slider-thumb-item-wrap .pp-image-slider-caption',
				'condition'   => array(
					'thumbnails_caption!' => '',
				),
			)
		);

		$this->add_control(
			'thumbnails_captions_border_radius_normal',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-image-slider-thumb-item-wrap .pp-image-slider-caption' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'thumbnails_caption!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'thumbnails_captions_margin',
			array(
				'label'      => __( 'Margin', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-image-slider-thumb-item-wrap .pp-image-slider-caption' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'thumbnails_caption!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'thumbnails_captions_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-image-slider-thumb-item-wrap .pp-image-slider-caption' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'thumbnails_caption!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'      => 'thumbnails_text_shadow',
				'selector'  => '{{WRAPPER}} .pp-image-slider-thumb-item-wrap .pp-image-slider-caption',
				'condition' => array(
					'thumbnails_caption!' => '',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_thumbnails_captions_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'thumbnails_caption!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'thumbnails_captions_background_hover',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .pp-image-slider-thumb-item-wrap:hover .pp-image-slider-caption',
				'exclude'   => array(
					'image',
				),
				'condition' => array(
					'thumbnails_caption!' => '',
				),
			)
		);

		$this->add_control(
			'thumbnails_captions_text_color_hover',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-image-slider-thumb-item-wrap:hover .pp-image-slider-caption' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'thumbnails_caption!' => '',
				),
			)
		);

		$this->add_control(
			'thumbnails_captions_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-image-slider-thumb-item-wrap:hover .pp-image-slider-caption' => 'border-color: {{VALUE}}',
				),
				'condition' => array(
					'thumbnails_caption!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'      => 'thumbnails_text_shadow_hover',
				'selector'  => '{{WRAPPER}} .pp-image-slider-thumb-item-wrap:hover .pp-image-slider-caption',
				'condition' => array(
					'thumbnails_caption!' => '',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'thumbnails_captions_blend_mode',
			array(
				'label'     => __( 'Blend Mode', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					''            => __( 'Normal', 'powerpack' ),
					'multiply'    => 'Multiply',
					'screen'      => 'Screen',
					'overlay'     => 'Overlay',
					'darken'      => 'Darken',
					'lighten'     => 'Lighten',
					'color-dodge' => 'Color Dodge',
					'saturation'  => 'Saturation',
					'color'       => 'Color',
					'difference'  => 'Difference',
					'exclusion'   => 'Exclusion',
					'hue'         => 'Hue',
					'luminosity'  => 'Luminosity',
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-image-slider-thumb-item-wrap .pp-image-slider-caption' => 'mix-blend-mode: {{VALUE}}',
				),
				'separator' => 'before',
				'condition' => array(
					'thumbnails_caption!' => '',
				),
			)
		);

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
				'label'      => __( 'Lightbox', 'powerpack' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'slideshow',
								),
								array(
									'name'     => 'link_to',
									'operator' => '==',
									'value'    => 'file',
								),
								array(
									'name'     => 'open_lightbox',
									'operator' => '!=',
									'value'    => 'no',
								),
								array(
									'name'     => 'lightbox_library',
									'operator' => '==',
									'value'    => 'fancybox',
								),
							),
						),
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'carousel',
								),
								array(
									'name'     => 'carousel_link_to',
									'operator' => '==',
									'value'    => 'file',
								),
								array(
									'name'     => 'carousel_open_lightbox',
									'operator' => '!=',
									'value'    => 'no',
								),
								array(
									'name'     => 'carousel_lightbox_library',
									'operator' => '==',
									'value'    => 'fancybox',
								),
							),
						),
					),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'       => 'lightbox_background',
				'types'      => array( 'classic', 'gradient' ),
				'selector'   => '.pp-gallery-fancybox-{{ID}} .fancybox-bg',
				'exclude'    => array(
					'image',
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'slideshow',
								),
								array(
									'name'     => 'link_to',
									'operator' => '==',
									'value'    => 'file',
								),
								array(
									'name'     => 'open_lightbox',
									'operator' => '!=',
									'value'    => 'no',
								),
								array(
									'name'     => 'lightbox_library',
									'operator' => '==',
									'value'    => 'fancybox',
								),
							),
						),
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'carousel',
								),
								array(
									'name'     => 'carousel_link_to',
									'operator' => '==',
									'value'    => 'file',
								),
								array(
									'name'     => 'carousel_open_lightbox',
									'operator' => '!=',
									'value'    => 'no',
								),
								array(
									'name'     => 'carousel_lightbox_library',
									'operator' => '==',
									'value'    => 'fancybox',
								),
							),
						),
					),
				),
			)
		);

		$this->add_control(
			'lightbox_opacity',
			array(
				'label'      => __( 'Opacity', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'max'  => 1,
						'min'  => 0,
						'step' => 0.01,
					),
				),
				'selectors'  => array(
					'.pp-gallery-fancybox-{{ID}} .fancybox-bg' => 'opacity: {{SIZE}}',
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'slideshow',
								),
								array(
									'name'     => 'link_to',
									'operator' => '==',
									'value'    => 'file',
								),
								array(
									'name'     => 'open_lightbox',
									'operator' => '!=',
									'value'    => 'no',
								),
								array(
									'name'     => 'lightbox_library',
									'operator' => '==',
									'value'    => 'fancybox',
								),
							),
						),
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'carousel',
								),
								array(
									'name'     => 'carousel_link_to',
									'operator' => '==',
									'value'    => 'file',
								),
								array(
									'name'     => 'carousel_open_lightbox',
									'operator' => '!=',
									'value'    => 'no',
								),
								array(
									'name'     => 'carousel_lightbox_library',
									'operator' => '==',
									'value'    => 'fancybox',
								),
							),
						),
					),
				),
			)
		);

		$this->add_control(
			'lightbox_toolbar_buttons_heading',
			array(
				'label'      => __( 'Toolbar Buttons', 'powerpack' ),
				'type'       => Controls_Manager::HEADING,
				'separator'  => 'before',
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'slideshow',
								),
								array(
									'name'     => 'link_to',
									'operator' => '==',
									'value'    => 'file',
								),
								array(
									'name'     => 'open_lightbox',
									'operator' => '!=',
									'value'    => 'no',
								),
								array(
									'name'     => 'lightbox_library',
									'operator' => '==',
									'value'    => 'fancybox',
								),
							),
						),
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'carousel',
								),
								array(
									'name'     => 'carousel_link_to',
									'operator' => '==',
									'value'    => 'file',
								),
								array(
									'name'     => 'carousel_open_lightbox',
									'operator' => '!=',
									'value'    => 'no',
								),
								array(
									'name'     => 'carousel_lightbox_library',
									'operator' => '==',
									'value'    => 'fancybox',
								),
							),
						),
					),
				),
			)
		);

		$this->add_control(
			'lightbox_toolbar_icons_color',
			array(
				'label'      => __( 'Icons Color', 'powerpack' ),
				'type'       => Controls_Manager::COLOR,
				'default'    => '',
				'selectors'  => array(
					'.pp-gallery-fancybox-{{ID}} .fancybox-toolbar .fancybox-button' => 'color: {{VALUE}};',
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'slideshow',
								),
								array(
									'name'     => 'link_to',
									'operator' => '==',
									'value'    => 'file',
								),
								array(
									'name'     => 'open_lightbox',
									'operator' => '!=',
									'value'    => 'no',
								),
								array(
									'name'     => 'lightbox_library',
									'operator' => '==',
									'value'    => 'fancybox',
								),
							),
						),
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'carousel',
								),
								array(
									'name'     => 'carousel_link_to',
									'operator' => '==',
									'value'    => 'file',
								),
								array(
									'name'     => 'carousel_open_lightbox',
									'operator' => '!=',
									'value'    => 'no',
								),
								array(
									'name'     => 'carousel_lightbox_library',
									'operator' => '==',
									'value'    => 'fancybox',
								),
							),
						),
					),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'       => 'lightbox_toolbar_buttons_background',
				'types'      => array( 'classic', 'gradient' ),
				'selector'   => '.pp-gallery-fancybox-{{ID}} .fancybox-toolbar .fancybox-button',
				'exclude'    => array(
					'image',
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'slideshow',
								),
								array(
									'name'     => 'link_to',
									'operator' => '==',
									'value'    => 'file',
								),
								array(
									'name'     => 'open_lightbox',
									'operator' => '!=',
									'value'    => 'no',
								),
								array(
									'name'     => 'lightbox_library',
									'operator' => '==',
									'value'    => 'fancybox',
								),
							),
						),
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'carousel',
								),
								array(
									'name'     => 'carousel_link_to',
									'operator' => '==',
									'value'    => 'file',
								),
								array(
									'name'     => 'carousel_open_lightbox',
									'operator' => '!=',
									'value'    => 'no',
								),
								array(
									'name'     => 'carousel_lightbox_library',
									'operator' => '==',
									'value'    => 'fancybox',
								),
							),
						),
					),
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
				'label'      => __( 'Arrows', 'powerpack' ),
				'type'       => Controls_Manager::HEADING,
				'separator'  => 'before',
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'slideshow',
								),
								array(
									'name'     => 'link_to',
									'operator' => '==',
									'value'    => 'file',
								),
								array(
									'name'     => 'open_lightbox',
									'operator' => '!=',
									'value'    => 'no',
								),
								array(
									'name'     => 'lightbox_library',
									'operator' => '==',
									'value'    => 'fancybox',
								),
								array(
									'name'     => 'lightbox_arrows',
									'operator' => '==',
									'value'    => 'yes',
								),
							),
						),
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'carousel',
								),
								array(
									'name'     => 'carousel_link_to',
									'operator' => '==',
									'value'    => 'file',
								),
								array(
									'name'     => 'carousel_open_lightbox',
									'operator' => '!=',
									'value'    => 'no',
								),
								array(
									'name'     => 'carousel_lightbox_library',
									'operator' => '==',
									'value'    => 'fancybox',
								),
								array(
									'name'     => 'carousel_lightbox_arrows',
									'operator' => '==',
									'value'    => 'yes',
								),
							),
						),
					),
				),
			)
		);

		$this->add_control(
			'lightbox_arrows_icons_color',
			array(
				'label'      => __( 'Icons Color', 'powerpack' ),
				'type'       => Controls_Manager::COLOR,
				'default'    => '',
				'selectors'  => array(
					'.pp-gallery-fancybox-{{ID}} .fancybox-navigation .fancybox-button' => 'color: {{VALUE}};',
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'slideshow',
								),
								array(
									'name'     => 'link_to',
									'operator' => '==',
									'value'    => 'file',
								),
								array(
									'name'     => 'open_lightbox',
									'operator' => '!=',
									'value'    => 'no',
								),
								array(
									'name'     => 'lightbox_library',
									'operator' => '==',
									'value'    => 'fancybox',
								),
								array(
									'name'     => 'lightbox_arrows',
									'operator' => '==',
									'value'    => 'yes',
								),
							),
						),
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'carousel',
								),
								array(
									'name'     => 'carousel_link_to',
									'operator' => '==',
									'value'    => 'file',
								),
								array(
									'name'     => 'carousel_open_lightbox',
									'operator' => '!=',
									'value'    => 'no',
								),
								array(
									'name'     => 'carousel_lightbox_library',
									'operator' => '==',
									'value'    => 'fancybox',
								),
								array(
									'name'     => 'carousel_lightbox_arrows',
									'operator' => '==',
									'value'    => 'yes',
								),
							),
						),
					),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'       => 'lightbox_arrows_icons_background',
				'types'      => array( 'classic', 'gradient' ),
				'selector'   => '.pp-gallery-fancybox-{{ID}} .fancybox-navigation .fancybox-button',
				'exclude'    => array(
					'image',
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'slideshow',
								),
								array(
									'name'     => 'link_to',
									'operator' => '==',
									'value'    => 'file',
								),
								array(
									'name'     => 'open_lightbox',
									'operator' => '!=',
									'value'    => 'no',
								),
								array(
									'name'     => 'lightbox_library',
									'operator' => '==',
									'value'    => 'fancybox',
								),
								array(
									'name'     => 'lightbox_arrows',
									'operator' => '==',
									'value'    => 'yes',
								),
							),
						),
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'carousel',
								),
								array(
									'name'     => 'carousel_link_to',
									'operator' => '==',
									'value'    => 'file',
								),
								array(
									'name'     => 'carousel_open_lightbox',
									'operator' => '!=',
									'value'    => 'no',
								),
								array(
									'name'     => 'carousel_lightbox_library',
									'operator' => '==',
									'value'    => 'fancybox',
								),
								array(
									'name'     => 'carousel_lightbox_arrows',
									'operator' => '==',
									'value'    => 'yes',
								),
							),
						),
					),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Arrows Controls in Style Tab
	 *
	 * @access protected
	 */
	protected function register_style_arrows_controls() {
		/**
		 * Style Tab: Arrows
		 */
		$this->start_controls_section(
			'section_arrows_style',
			array(
				'label'     => __( 'Arrows', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'arrows' => 'yes',
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
					'arrows' => 'yes',
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
					'{{WRAPPER}} .swiper-button-next' => 'right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .swiper-button-prev' => 'left: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'arrows' => 'yes',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_arrows_style' );

		$this->start_controls_tab(
			'tab_arrows_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'arrows' => 'yes',
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
					'arrows' => 'yes',
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
					'arrows' => 'yes',
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
					'arrows' => 'yes',
				),
			)
		);

		$this->add_control(
			'arrows_border_radius_normal',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-slider-arrow' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'arrows' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_arrows_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'arrows' => 'yes',
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
					'arrows' => 'yes',
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
					'arrows' => 'yes',
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
					'{{WRAPPER}} .pp-slider-arrow:hover',
				),
				'condition' => array(
					'arrows' => 'yes',
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
					'arrows' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Dots Controls in Style Tab
	 *
	 * @access protected
	 */
	protected function register_style_dots_controls() {
		/**
		 * Style Tab: Dots
		 */
		$this->start_controls_section(
			'section_dots_style',
			array(
				'label'     => __( 'Pagination: Dots', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->add_control(
			'dots_position',
			array(
				'label'        => __( 'Position', 'powerpack' ),
				'type'         => Controls_Manager::SELECT,
				'options'      => array(
					'inside'  => __( 'Inside', 'powerpack' ),
					'outside' => __( 'Outside', 'powerpack' ),
				),
				'default'      => 'outside',
				'prefix_class' => 'pp-swiper-slider-pagination-',
				'condition'    => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
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
					'{{WRAPPER}} .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
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
					'{{WRAPPER}} .swiper-pagination-bullet' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_dots_style' );

		$this->start_controls_tab(
			'tab_dots_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
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
					'{{WRAPPER}} .swiper-pagination-bullet' => 'background: {{VALUE}};',
				),
				'condition' => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->add_control(
			'active_dot_color_normal',
			array(
				'label'     => __( 'Active Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'background: {{VALUE}};',
				),
				'condition' => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
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
				'selector'    => '{{WRAPPER}} .swiper-pagination-bullet',
				'condition'   => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->add_control(
			'dots_border_radius_normal',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .swiper-pagination-bullet' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
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
					'{{WRAPPER}} .swiper-pagination' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'          => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_dots_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
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
					'{{WRAPPER}} .swiper-pagination-bullet:hover' => 'background: {{VALUE}};',
				),
				'condition' => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
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
					'{{WRAPPER}} .swiper-pagination-bullet:hover' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Register Fraction Pagination Controls in Style Tab
	 *
	 * @access protected
	 */
	protected function register_style_fraction_controls() {
		/**
		 * Style Tab: Pagination: Dots
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_fraction_style',
			array(
				'label'     => __( 'Pagination: Fraction', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'dots'            => 'yes',
					'pagination_type' => 'fraction',
				),
			)
		);

		$this->add_control(
			'fraction_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .swiper-pagination-fraction' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'dots'            => 'yes',
					'pagination_type' => 'fraction',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'fraction_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'scheme'    => Scheme_Typography::TYPOGRAPHY_4,
				'selector'  => '{{WRAPPER}} .swiper-pagination-fraction',
				'condition' => array(
					'dots'            => 'yes',
					'pagination_type' => 'fraction',
				),
			)
		);

		$this->add_control(
			'fraction_position',
			array(
				'label'        => __( 'Position', 'powerpack' ),
				'type'         => Controls_Manager::SELECT,
				'options'      => array(
					'inside'  => __( 'Inside', 'powerpack' ),
					'outside' => __( 'Outside', 'powerpack' ),
				),
				'default'      => 'outside',
				'prefix_class' => 'pp-swiper-slider-pagination-',
				'condition'    => array(
					'dots'            => 'yes',
					'pagination_type' => 'fraction',
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

		if ( 'carousel' === $settings['skin'] ) {
			if ( 'bottom' === $settings['carousel_lightbox_thumbs_position'] ) {
				$base_class    .= ' pp-fancybox-thumbs-x';
				$fancybox_axis = 'x';
			} else {
				$base_class    .= ' pp-fancybox-thumbs-y';
				$fancybox_axis = 'y';
			}

			$fancybox_options = array(
				'loop'             => ( 'yes' === $settings['carousel_lightbox_loop'] ),
				'arrows'           => ( 'yes' === $settings['carousel_lightbox_arrows'] ),
				'infobar'          => ( 'yes' === $settings['carousel_lightbox_slides_counter'] ),
				'keyboard'         => ( 'yes' === $settings['carousel_lightbox_keyboard'] ),
				'toolbar'          => ( 'yes' === $settings['carousel_lightbox_toolbar'] ),
				'buttons'          => $settings['carousel_lightbox_toolbar_buttons'],
				'animationEffect'  => $settings['carousel_lightbox_animation'],
				'transitionEffect' => $settings['carousel_lightbox_transition_effect'],
				'baseClass'        => $base_class,
				'thumbs'           => array(
					'autoStart' => ( 'yes' === $settings['carousel_lightbox_thumbs_auto_start'] ),
					'axis'      => $fancybox_axis,
				),
			);
		} else {
			if ( 'bottom' === $settings['thumbs_position'] ) {
				$base_class    .= ' pp-fancybox-thumbs-x';
				$fancybox_axis = 'x';
			} else {
				$base_class    .= ' pp-fancybox-thumbs-y';
				$fancybox_axis = 'y';
			}

			$fancybox_options = array(
				'loop'             => ( 'yes' === $settings['loop'] ),
				'arrows'           => ( 'yes' === $settings['lightbox_arrows'] ),
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
		}

		return $fancybox_options;
	}

	/**
	 * Carousel Settings.
	 *
	 * @access public
	 */
	public function slider_settings() {
		$settings = $this->get_settings();

		if ( 'slide' === $settings['effect'] && 'slideshow' !== $settings['skin'] ) {
			$slides_to_show          = ( isset( $settings['slides_per_view'] ) && '' !== $settings['slides_per_view'] ) ? absint( $settings['slides_per_view'] ) : 3;
			$slides_to_show_tablet   = ( isset( $settings['slides_per_view_tablet'] ) && '' !== $settings['slides_per_view_tablet'] ) ? absint( $settings['slides_per_view_tablet'] ) : 2;
			$slides_to_show_mobile   = ( isset( $settings['slides_per_view_mobile'] ) && '' !== $settings['slides_per_view_mobile'] ) ? absint( $settings['slides_per_view_mobile'] ) : 2;
			$slides_to_scroll        = ( isset( $settings['slides_to_scroll'] ) && '' !== $settings['slides_to_scroll'] ) ? absint( $settings['slides_to_scroll'] ) : 1;
			$slides_to_scroll_tablet = ( isset( $settings['slides_to_scroll_tablet'] ) && '' !== $settings['slides_to_scroll_tablet'] ) ? absint( $settings['slides_to_scroll_tablet'] ) : 1;
			$slides_to_scroll_mobile = ( isset( $settings['slides_to_scroll_mobile'] ) && '' !== $settings['slides_to_scroll_mobile'] ) ? absint( $settings['slides_to_scroll_mobile'] ) : 1;
		} else {
			$slides_to_show          = 1;
			$slides_to_show_tablet   = 1;
			$slides_to_show_mobile   = 1;
			$slides_to_scroll        = 1;
			$slides_to_scroll_tablet = 1;
			$slides_to_scroll_mobile = 1;
		}

		$slider_options = array(
			'direction'      => 'horizontal',
			'speed'          => ( '' !== $settings['animation_speed'] ) ? $settings['animation_speed'] : 600,
			'effect'         => $settings['effect'],
			'slidesPerView'  => $slides_to_show,
			'slidesPerGroup' => $slides_to_scroll,
			'spaceBetween'   => ( isset( $settings['thumbnails_horizontal_spacing'] ) && '' !== $settings['thumbnails_horizontal_spacing']['size'] ) ? $settings['thumbnails_horizontal_spacing']['size'] : 10,
			'autoHeight'     => ( 'yes' === $settings['adaptive_height'] ),
			'loop'           => ( 'yes' === $settings['infinite_loop'] ),
		);

		if ( 'yes' === $settings['autoplay'] && ! empty( $settings['autoplay_speed'] ) ) {
			$autoplay_speed = $settings['autoplay_speed'];
		} else {
			$autoplay_speed = 999999;
		}

		$slider_options['autoplay'] = array(
			'delay' => $autoplay_speed,
		);

		if ( 'yes' === $settings['dots'] ) {
			$slider_options['pagination'] = array(
				'el'        => '.swiper-pagination-' . esc_attr( $this->get_id() ),
				'type'      => $settings['pagination_type'],
				'clickable' => true,
			);
		}

		if ( 'yes' === $settings['arrows'] ) {
			$slider_options['navigation'] = array(
				'nextEl' => '.swiper-button-next-' . esc_attr( $this->get_id() ),
				'prevEl' => '.swiper-button-prev-' . esc_attr( $this->get_id() ),
			);
		}

		$elementor_bp_lg = get_option( 'elementor_viewport_lg' );
		$elementor_bp_md = get_option( 'elementor_viewport_md' );
		$bp_desktop      = ! empty( $elementor_bp_lg ) ? $elementor_bp_lg : 1025;
		$bp_tablet       = ! empty( $elementor_bp_md ) ? $elementor_bp_md : 768;
		$bp_mobile       = 320;

		$slider_options['breakpoints'] = array(
			$bp_desktop => array(
				'slidesPerView'  => ( $slides_to_show ) ? absint( $slides_to_show ) : 3,
				'slidesPerGroup' => ( $slides_to_scroll ) ? absint( $slides_to_scroll ) : 3,
				'spaceBetween'   => ( isset( $settings['thumbnails_horizontal_spacing'] ) && '' !== $settings['thumbnails_horizontal_spacing']['size'] ) ? $settings['thumbnails_horizontal_spacing']['size'] : 10,
			),
			$bp_tablet  => array(
				'slidesPerView'  => ( $slides_to_show_tablet ) ? absint( $slides_to_show_tablet ) : 2,
				'slidesPerGroup' => ( $slides_to_scroll_tablet ) ? absint( $slides_to_scroll_tablet ) : 2,
				'spaceBetween'   => ( isset( $settings['thumbnails_horizontal_spacing_tablet'] ) && '' !== $settings['thumbnails_horizontal_spacing_tablet']['size'] ) ? $settings['thumbnails_horizontal_spacing_tablet']['size'] : 10,
			),
			$bp_mobile  => array(
				'slidesPerView'  => ( $slides_to_show_mobile ) ? absint( $slides_to_show_mobile ) : 1,
				'slidesPerGroup' => ( $slides_to_scroll_mobile ) ? absint( $slides_to_scroll_mobile ) : 1,
				'spaceBetween'   => ( isset( $settings['thumbnails_horizontal_spacing_mobile'] ) && '' !== $settings['thumbnails_horizontal_spacing_mobile']['size'] ) ? $settings['thumbnails_horizontal_spacing_mobile']['size'] : 10,
			),
		);

		$this->add_render_attribute(
			'slider',
			array(
				'data-slider-settings' => wp_json_encode( $slider_options ),
			)
		);
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
			<?php
		}
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

	/**
	 * Render Image Slider output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$gallery  = $settings['gallery_images'];

		if ( empty( $gallery ) ) {
			return;
		}

		$this->add_render_attribute(
			array(
				'slider-container' => array(
					'class' => 'pp-image-slider-container'
				),
				'slider-wrap' => array(
					'class' => array(
						'pp-image-slider-wrap',
						'swiper-container-wrap',
						'swiper'
					)
				),
				'slider' => array(
					'class' => array(
						'pp-image-slider',
						'pp-swiper-slider',
						'swiper-container'
					),
					'id'    => 'pp-image-slider-' . esc_attr( $this->get_id() ),
				)
			)
		);

		if ( 'yes' === $settings['equal_height'] ) {
			$this->add_render_attribute( 'slider-container', 'class', 'pp-thumbs-equal-height' );
		}

		if ( 'bottom' === $settings['thumbs_position'] ) {
			$this->add_render_attribute(
				'slider-wrap',
				array(
					'data-fancybox-class' => 'pp-fancybox-thumbs-x',
					'data-fancybox-axis'  => 'x',
				)
			);
		} else {
			$this->add_render_attribute(
				'slider-wrap ',
				array(
					'data-fancybox-class' => 'pp-fancybox-thumbs-y',
					'data-fancybox-axis'  => 'y',
				)
			);
		}

		if ( 'fancybox' === $settings['lightbox_library'] || 'fancybox' === $settings['carousel_lightbox_library'] ) {
			$fancybox_settings = $this->fancybox_settings();

			$this->add_render_attribute(
				'slider',
				array(
					'data-fancybox-settings' => wp_json_encode( $fancybox_settings ),
				)
			);
		}

		if ( is_rtl() ) {
			$this->add_render_attribute( 'slider', 'dir', 'rtl' );
		}

		$this->slider_settings();
		?>
		<?php if ( ! empty( $gallery ) ) { ?>
		<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'slider-container' ) ); ?>>
			<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'slider-wrap' ) ); ?>>
				<div class="pp-image-slider-box">
					<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'slider' ) ); ?>>
						<div class="swiper-wrapper">
							<?php
							if ( 'slideshow' === $settings['skin'] ) {
								$this->render_slideshow();
							} else {
								$this->render_carousel();
							}
							?>
						</div>
					</div>
					<?php
						$this->render_dots();
						$this->render_arrows();
					?>
				</div>
			</div>
			<?php
			if ( 'slideshow' === $settings['skin'] ) {
				// Slideshow Thumbnails.
				$this->render_thumbnails();
			}
			?>
		</div>
			<?php
		} else {
			$placeholder = sprintf( 'Click here to edit the "%1$s" settings and choose some images.', esc_attr( $this->get_title() ) );

			echo $this->render_editor_placeholder( // phpcs:ignore
				array(
					'title' => __( 'Gallery is empty!', 'powerpack' ),
					'body'  => $placeholder, // phpcs:ignore
				)
			);
		}
	}

	/**
	 * Render Slideshow
	 */
	protected function render_slideshow() {
		$settings = $this->get_settings_for_display();
		$gallery  = $settings['gallery_images'];

		foreach ( $gallery as $index => $item ) {
			?>
			<div class="pp-image-slider-slide pp-swiper-slide swiper-slide">
				<?php
				$image_url   = Group_Control_Image_Size::get_attachment_image_src( $item['id'], 'image', $settings );
				$image_html  = '<div class="pp-image-slider-image-wrap">';
				$image_html .= '<img class="pp-image-slider-image" src="' . esc_url( $image_url ) . '" alt="' . esc_attr( Control_Media::get_image_alt( $item ) ) . '" />';
				$image_html .= '</div>';

				$caption          = '';
				$caption_rendered = '';

				if ( '' !== $settings['feature_image_caption'] ) {
					$caption_rendered = $this->render_image_caption( $item['id'], $settings['feature_image_caption'] );
					$image_html      .= '<div class="pp-image-slider-content pp-media-content">';
						$image_html  .= $caption_rendered;
					$image_html      .= '</div>';
				}

				if ( '' !== $settings['feature_image_lightbox_caption'] ) {
					$caption = Module::get_image_caption( $item['id'], $settings['feature_image_lightbox_caption'] );
				}

				if ( 'none' !== $settings['link_to'] ) {

					$image_html = $this->get_slide_link_atts( 'slideshow', $index, $item, $image_html, $caption );

				}

				echo wp_kses_post( $image_html );
				?>
			</div>
			<?php
		}
	}

	/**
	 * Render Thumbnails
	 */
	protected function render_thumbnails() {
		$settings = $this->get_settings_for_display();
		$gallery  = $settings['gallery_images'];
		?>
		<div class="pp-image-slider-thumb-pagination elementor-grid <?php echo 'pp-' . esc_attr( $settings['thumbnails_image_filter'] ); ?>">
			<?php
			foreach ( $gallery as $index => $item ) {
				$image_url = Group_Control_Image_Size::get_attachment_image_src( $item['id'], 'thumbnail', $settings );
				?>
				<div class="pp-image-slider-thumb-item-wrap pp-grid-item-wrap elementor-grid-item">
					<div class="pp-image-slider-thumb-item">
						<div class="pp-grid-item pp-image-slider-thumb-item pp-ins-filter-hover">
						<?php if ( 'yes' === $settings['equal_height'] ) { ?>
							<div class="pp-image-slider-thumb-image pp-ins-filter-target" style="background-image:url('<?php echo esc_attr( $image_url ); ?> ')"></div>
						<?php } else { ?>
							<div class="pp-image-slider-thumb-image pp-ins-filter-target">
								<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( Control_Media::get_image_alt( $item ) ); ?>" />
							</div>
						<?php } ?>
						<?php echo wp_kses_post( $this->render_image_overlay() ); ?>
						<?php if ( '' !== $settings['thumbnails_caption'] ) { ?>
								<div class="pp-image-slider-content pp-media-content">
									<?php
										echo wp_kses_post( $this->render_image_caption( $item['id'], $settings['thumbnails_caption'] ) );
									?>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
				<?php
			}
			?>
		</div>
		<?php
	}

	/**
	 * Render Carousel
	 */
	protected function render_carousel() {
		$settings = $this->get_settings_for_display();
		$gallery  = $settings['gallery_images'];

		foreach ( $gallery as $index => $item ) {
			$image_url = Group_Control_Image_Size::get_attachment_image_src( $item['id'], 'thumbnail', $settings );
			?>
			<div class="pp-image-slider-thumb-item-wrap pp-swiper-slide swiper-slide">
				<?php
					$image_html = '<div class="pp-image-slider-thumb-item pp-ins-filter-hover">';

					if ( 'yes' === $settings['equal_height'] ) {
						if ( 'file' === $settings['carousel_link_to'] && 'fancybox' === $settings['carousel_lightbox_library'] ) {
							$image_html .= '<div class="pp-image-slider-thumb-image pp-ins-filter-target" style="background-image:url(' . esc_url( $image_url ) . ')"><img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( Control_Media::get_image_alt( $item ) ) . '" /></div>';
						} else {
							$image_html .= '<div class="pp-image-slider-thumb-image pp-ins-filter-target" style="background-image:url(' . esc_url( $image_url ) . ')"></div>';
						}
					} else {
						$image_html .= '<div class="pp-image-slider-thumb-image pp-ins-filter-target"><img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( Control_Media::get_image_alt( $item ) ) . '" /></div>';
					}

					$image_html .= $this->render_image_overlay();

					$caption          = '';
					$caption_rendered = '';

					if ( '' !== $settings['thumbnails_caption'] ) {
						$caption_rendered = $this->render_image_caption( $item['id'], $settings['thumbnails_caption'] );
						$image_html      .= '<div class="pp-image-slider-content pp-media-content">';
						$image_html      .= $caption_rendered;
						$image_html      .= '</div>';
					}

					if ( '' !== $settings['thumbnails_lightbox_caption'] ) {
						$caption = Module::get_image_caption( $item['id'], $settings['thumbnails_lightbox_caption'] );
					}

					$image_html .= '</div>';

					if ( 'none' !== $settings['carousel_link_to'] ) {
						$image_html = $this->get_slide_link_atts( 'carousel', $index, $item, $image_html, $caption );
					}

					echo $image_html; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</div>
			<?php
		}
	}

	/**
	 * Get link attributes for slides
	 *
	 * @param  string $layout      layout type.
	 * @param  mixed  $index       slide index.
	 * @param  array  $item        item array.
	 * @param  string $image_html  image html.
	 * @param  string $caption     image caption.
	 */
	protected function get_slide_link_atts( $layout, $index, $item, $image_html, $caption = '' ) {
		$settings = $this->get_settings_for_display();

		if ( 'slideshow' === $layout ) {
			$link_to          = $settings['link_to'];
			$custom_link      = get_post_meta( $item['id'], 'pp-custom-link', true );
			$link_target      = $settings['link_target'];
			$lightbox_library = $settings['lightbox_library'];
			$lightbox_caption = $settings['feature_image_lightbox_caption'];
			$link_key         = $this->get_repeater_setting_key( 'link', 'gallery_images', $index );
		} elseif ( 'carousel' === $layout ) {
			$link_to          = $settings['carousel_link_to'];
			$custom_link      = get_post_meta( $item['id'], 'pp-custom-link', true );
			$link_target      = $settings['carousel_link_target'];
			$lightbox_library = $settings['carousel_lightbox_library'];
			$lightbox_caption = $settings['thumbnails_lightbox_caption'];
			$link_key         = $this->get_repeater_setting_key( 'carousel_link', 'gallery_images', $index );
		}

		if ( 'file' === $link_to ) {
			$link = wp_get_attachment_url( $item['id'] );

			if ( 'fancybox' === $lightbox_library ) {
				$this->add_render_attribute(
					$link_key,
					array(
						'data-elementor-open-lightbox' => 'no',
						'data-fancybox'                => 'pp-image-slider-' . $this->get_id(),
					)
				);

				if ( '' !== $lightbox_caption ) {
					$this->add_render_attribute(
						$link_key,
						array(
							'data-caption' => $caption,
						)
					);
				}

				$this->add_render_attribute(
					$link_key,
					array(
						'data-src' => $link,
					)
				);
			} else {
				$this->add_lightbox_data_attributes( $link_key, $item['id'], $settings['open_lightbox'], $this->get_id() );

				$this->add_render_attribute(
					$link_key,
					array(
						'href'  => $link,
						'class' => 'elementor-clickable',
					)
				);
			}
		} elseif ( 'custom' === $link_to && '' !== $custom_link ) {
			$link = $custom_link;

			$this->add_render_attribute( $link_key, 'target', $link_target );

			$this->add_render_attribute(
				$link_key,
				array(
					'href' => $link,
				)
			);
		}

		$this->add_render_attribute(
			$link_key,
			array(
				'class' => 'pp-image-slider-slide-link',
			)
		);

		return '<a ' . $this->get_render_attribute_string( $link_key ) . '>' . $image_html . '</a>';
	}

	/**
	 * Render Image Overlay
	 *
	 * @return string overlay markup
	 */
	protected function render_image_overlay() {
		return '<div class="pp-image-slider-thumb-overlay pp-media-overlay"></div>';
	}

	/**
	 * Render Image Caption
	 *
	 * @param  int    $id ID of image.
	 * @param  string $caption_type image caption type.
	 * @return $html
	 */
	protected function render_image_caption( $id, $caption_type = 'caption' ) {
		$settings = $this->get_settings_for_display();

		$caption = Module::get_image_caption( $id, $caption_type );

		if ( '' === $caption ) {
			return '';
		}

		ob_start();
		?>
		<div class="pp-image-slider-caption">
			<?php echo wp_kses_post( $caption ); ?>
		</div>
		<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
}
