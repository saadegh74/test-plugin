<?php
namespace Elementor;

use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Jet_Elements_Smartslider3 extends Jet_Elements_Base {

	public function get_name() {
		return 'smartslider3';
	}

	public function get_title() {
		return esc_html__( 'Smart Slider', 'jet-elements' );
	}

	public function get_icon() {
		return 'eicon-post-slider';
	}

	public function get_jet_help_url() {
		return 'https://crocoblock.com/knowledge-base/article-category/jet-elements/';
	}

	public function get_categories() {
		return array( 'cherry' );
	}

	public function is_reload_preview_required() {
		return true;
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_slider',
			array(
				'label' => esc_html__( 'Slider', 'jet-elements' ),
			)
		);

		$this->add_control(
			'slider',
			array(
				'label'   => esc_html__( 'Select slider', 'jet-elements' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => $this->get_sliders()
			)
		);

		$this->add_control(
			'set_key',
			array(
				'type' => Controls_Manager::RAW_HTML,
				'raw'  => __( '<strong>Note:</strong> In Editor mode slider will be appended only as preview image to speed up the editing. On frontend slider will be shown as usual slider.', 'jet-elements' ),
			)
		);

		$this->end_controls_section();
	}

	protected function get_sliders() {

		global $wpdb;

		$tablename = $wpdb->prefix . 'nextend2_smartslider3_sliders';
		$results   = $wpdb->get_results( "SELECT * FROM $tablename" );

		if ( empty( $results ) ) {
			return array();
		}

		$slides = wp_list_pluck( $results, 'title', 'id' );
		array_walk( $slides, array( $this, 'map_slides' ) );

		return $slides;
	}

	public function map_slides( &$item, $key ) {
		$item = sprintf( '%s (id:%s)', $item, $key );
	}

	/**
	 * Returns slider module wrapper format
	 */
	protected function get_slider_format() {
		return apply_filters(
			'cherry-elemetor/addons/smartslider3/format',
			'<div class="elementor-smartslider">%s</div>'
		);
	}

	/** Only available, positive slider IDs may reach the shortcode or preview. */
	protected function get_selected_slider() {
		$slider = $this->get_settings( 'slider' );
		if ( ! ( is_string( $slider ) || is_int( $slider ) ) || ! preg_match( '/^[1-9][0-9]*$/', (string) $slider ) ) {
			return 0;
		}
		$sliders = $this->get_sliders();
		return array_key_exists( $slider, $sliders ) ? (int) $slider : 0;
	}

	protected function render() {

		$slider = $this->get_selected_slider();

		if ( ! jet_elements_integration()->in_elementor() ) {

			if ( ! $slider ) {
				return;
			}

			printf(
				$this->get_slider_format(), // phpcs:ignore
				do_shortcode( '[smartslider3 slider=' . $slider . ']' )
			);

			return;

		}

		global $wpdb;

		$sliderstable = $wpdb->prefix . 'nextend2_smartslider3_sliders';
		$slidestable  = $wpdb->prefix . 'nextend2_smartslider3_slides';
		$placeholder  = esc_html__( 'Please, select slider to show', 'jet-elements' );
		$format       = '<div class="elementor-smartslider">%s</div>';

		if ( ! $slider ) {
			printf( $format, $placeholder ); // phpcs:ignore
			return;
		}

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $sliderstable WHERE id=%d", $slider ) );

		if ( null === $row ) {
			printf( $format, $placeholder ); // phpcs:ignore
			return;
		}

		$params = json_decode( $row->params, ARRAY_A );

		$height_value = isset( $params['height'] ) ? $params['height'] : null;
		if ( isset( $params['responsive-mode'] ) && 'fullwidth' === $params['responsive-mode'] && isset( $params['responsiveSliderHeightMax'] ) ) {
			$height_value = $params['responsiveSliderHeightMax'];
		}
		$height = is_scalar( $height_value ) && preg_match( '/^\d+(?:\.\d+)?$/', (string) $height_value ) && is_finite( (float) $height_value )
			? (float) $height_value . 'px' : 'auto';

		$slide = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $slidestable WHERE slider = %d AND ordering = 0", $slider )
		);

		if ( null === $slide ) {
			printf( $format, $placeholder ); // phpcs:ignore
			return;
		}

		$slide_params = json_decode( $slide->params, ARRAY_A );

		$image = sprintf(
			'<div style="overflow:hidden;height:%1$s;display:flex;">
				<img style="width: 100%%;object-fit: cover;" src="%2$s" alt="">
			</div>',
			esc_attr( $height ),
			esc_url( $this->get_placeholder_image( $slide_params ) )
		);

		printf( $format, $image ); // phpcs:ignore
	}

	public function get_placeholder_image( $slide_params ) {

		$uploads = wp_upload_dir();
		$base    = $uploads['baseurl'];

		if ( ! empty( $slide_params['backgroundImage'] ) ) {
			$img = str_replace( '$upload$', $base, $slide_params['backgroundImage'] );
		} else {
			$img = Utils::get_placeholder_image_src();
		}

		return $img;
	}

	public function render_plain_content() {

		// In plain mode, render without shortcode
		$slider = $this->get_selected_slider();

		if ( ! $slider ) {
			return;
		}

		printf( '[smartslider3 slider="%d"]', $slider ); // phpcs:ignore

	}

	protected function content_template() {}
}
