<?php
namespace PowerpackElements\Modules\Gallery;

use PowerpackElements\Base\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Module extends Module_Base {

	/**
	 * Module is active or not.
	 *
	 * @since 1.3.3
	 *
	 * @access public
	 *
	 * @return bool true|false.
	 */
	public static function is_active() {
		return true;
	}

	/**
	 * Get Module Name.
	 *
	 * @since 1.3.3
	 *
	 * @access public
	 *
	 * @return string Module name.
	 */
	public function get_name() {
		return 'pp-gallery';
	}

	/**
	 * Get Widgets.
	 *
	 * @since 1.3.3
	 *
	 * @access public
	 *
	 * @return array Widgets.
	 */
	public function get_widgets() {
		return array(
			'Image_Gallery',
			'Image_Slider',
		);
	}

	/**
	 * Get Image Caption.
	 *
	 * @since 1.3.3
	 *
	 * @access public
	 *
	 * @return string image caption.
	 */
	public static function get_image_caption( $id, $caption_type = 'caption' ) {

		$attachment = get_post( $id );

		$attachment_caption = '';

		if ( 'title' === $caption_type ) {
			$attachment_caption = $attachment->post_title;
		} elseif ( 'caption' === $caption_type ) {
			$attachment_caption = wp_get_attachment_caption( $id );
		} elseif ( 'description' === $caption_type ) {
			$attachment_caption = $attachment->post_content;
		}

		return $attachment_caption;

	}

	/**
	 * Get Image Filters.
	 *
	 * @since 1.3.3
	 *
	 * @access public
	 *
	 * @return array image filters.
	 */
	public static function get_image_filters() {

		$pp_image_filters = array(
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

		return $pp_image_filters;
	}

	/**
	 * Constructor function that initializes required actions and hooks
	 */
	public function __construct() {
		parent::__construct();

		// Gallery module - load more componenet.
		add_action( 'wp', array( $this, 'gallery_get_images' ) );
	}

	/**
	 * Get gallery images
	 *
	 * @access public
	 */
	public function gallery_get_images() {
		if ( ! isset( $_POST['pp_action'] ) || 'pp_gallery_get_images' !== $_POST['pp_action'] ) {
			return;
		}

		if ( ! isset( $_POST['settings'] ) || empty( $_POST['settings'] ) ) {
			return;
		}

		// Tell WordPress this is an AJAX request.
		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		$settings    = $_POST['settings'];
		$gallery_id  = $settings['widget_id'];
		$post_id     = $settings['post_id'];
		$template_id = $settings['template_id'];
		if ( $template_id !== $post_id ) {
			$post_id = $template_id;
		}

		$elementor = \Elementor\Plugin::$instance;
		$meta      = $elementor->documents->get( $post_id )->get_elements_data();
		$gallery   = $this->find_element_recursive( $meta, $gallery_id );

		if ( isset( $gallery['templateID'] ) ) {
			$template_data = \Elementor\Plugin::$instance->templates_manager->get_template_data( [
				'source'        => 'local',
				'template_id'   => $gallery['templateID'],
			] );

			if ( is_array( $template_data ) && isset( $template_data['content'] ) ) {
				$gallery = $template_data['content'][0];
			}
		}

		if ( ! $gallery ) {
			wp_send_json_error();
		}

		// Restore default values.
		$widget = $elementor->elements_manager->create_element_instance( $gallery );
		$photos = $widget->ajax_get_images();

		wp_send_json_success( array( 'items' => $photos ) );
	}

	/**
	 * Get Widget Setting data.
	 *
	 * @since 1.3.3
	 * @access public
	 * @param array  $elements Element array.
	 * @param string $widget_id Element ID.
	 * @return Boolean True/False.
	 */
	public function find_element_recursive( $elements, $widget_id ) {
		foreach ( $elements as $element ) {
			if ( $widget_id === $element['id'] ) {
				return $element;
			}

			if ( ! empty( $element['elements'] ) ) {
				$element = $this->find_element_recursive( $element['elements'], $widget_id );

				if ( $element ) {
					return $element;
				}
			}
		}

		return false;
	}
}
