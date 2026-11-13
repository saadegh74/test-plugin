<?php
namespace PowerpackElements\Modules\DisplayConditions\Conditions;

// Powerpack Elements Classes
use PowerpackElements\Base\Condition;

// Elementor Classes
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Pods_Text extends Pods_Base {

	/**
	 * Get Name
	 *
	 * Get the name of the module
	 *
	 * @since  2.3.2
	 * @return string
	 */
	public function get_name() {
		return 'pods_text';
	}

	/**
	 * Get Title
	 *
	 * Get the title of the module
	 *
	 * @since  2.3.2
	 * @return string
	 */
	public function get_title() {
		return __( 'Pods Textual', 'powerpack' );
	}

	/**
	 * Get Name Control
	 *
	 * Get the settings for the name control
	 *
	 * @since  2.3.2
	 * @return array
	 */
	public function get_name_control() {
		return wp_parse_args( [
			'description'   => __( 'Search Pods Textual ( text, textarea, number, range, email, url and password ) fields by label.', 'powerpack' ),
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
	 * @since  2.3.2
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
	 * @since 2.3.2
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

		$key = explode( ':', $key );

		$field_value = pods_field( $key[2] );

		if ( $field_value ) {
			if ( is_array( $field_value ) && ! empty( $field_value ) ) {
				foreach ( $field_value as $_key => $_value ) {
					if ( $value === $_value ) {
						$show = true;
						break;
					}
				}
			} else {
				$show = $value === $field_value;
			}
		}

		return $this->compare( $show, true, $operator );
	}
}
