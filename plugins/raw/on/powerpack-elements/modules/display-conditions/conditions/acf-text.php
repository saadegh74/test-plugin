<?php
namespace PowerpackElements\Modules\DisplayConditions\Conditions;

// Powerpack Elements Classes
use PowerpackElements\Base\Condition;

// Elementor Classes
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Acf_Text extends Acf_Base {

	/**
	 * Get Name
	 *
	 * Get the name of the module
	 *
	 * @since  1.4.15
	 * @return string
	 */
	public function get_name() {
		return 'acf_text';
	}

	/**
	 * Get Title
	 *
	 * Get the title of the module
	 *
	 * @since  1.4.15
	 * @return string
	 */
	public function get_title() {
		return __( 'ACF Textual', 'powerpack' );
	}

	/**
	 * Get Name Control
	 *
	 * Get the settings for the name control
	 *
	 * @since  1.4.15
	 * @return array
	 */
	public function get_name_control() {
		return wp_parse_args( [
			'description'   => __( 'Search ACF Textual ( text, textarea, number, range, email, url and password ) fields by label.', 'powerpack' ),
			'placeholder'   => __( 'Search Fields', 'powerpack' ),
			'query_options' => [
				'field_type'    => [
					'textual',
				],
				'show_field_type' => true,
			],
		], $this->name_control_defaults );
	}

	/**
	 * Get Value Control
	 *
	 * Get the settings for the value control
	 *
	 * @since  1.4.15
	 * @return array
	 */
	public function get_value_control() {
		return [
			'type'          => Controls_Manager::TEXT,
			'default'       => '',
			'placeholder'   => __( 'Value', 'powerpack' ),
			'label_block'   => true,
		];
	}

	/**
	 * Check condition
	 *
	 * @since 1.4.15
	 *
	 * @access public
	 *
	 * @param string    $name       The control name to check
	 * @param string    $operator   Comparison operator
	 * @param mixed     $value      The control value to check
	 */
	public function check( $key, $operator, $value ) {
		$show = false;

		global $post;

		$field_value = get_field( $key );

		if ( is_archive() ) {
			$term = get_queried_object();

			if ( get_class( $term ) === 'WP_Term' ) {
				$field_value = get_field( $key, $term );
			}
		}

		//if ( $field_value ) {
			$field_settings = get_field_object( $key );

			switch ( $field_settings['type'] ) {
				default:
					$show = $value === $field_value;
					break;
			}
		//}

		return $this->compare( $show, true, $operator );
	}
}
