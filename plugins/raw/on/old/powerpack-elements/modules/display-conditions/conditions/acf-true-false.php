<?php
namespace PowerpackElements\Modules\DisplayConditions\Conditions;

// Powerpack Elements Classes
use PowerpackElements\Base\Condition;

// Elementor Classes
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Acf_True_False extends Acf_Base {

	/**
	 * Get Name
	 *
	 * Get the name of the module
	 *
	 * @since  1.4.15
	 * @return string
	 */
	public function get_name() {
		return 'acf_true_false';
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
		return __( 'ACF True / False', 'powerpack' );
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
			'description'   => __( 'Search ACF True / False field by label.', 'powerpack' ),
			'placeholder'   => __( 'Search Fields', 'powerpack' ),
			'query_options' => [
				'field_type'    => [
					'boolean',
				],
				'show_field_type' => false,
			],
		], $this->name_control_defaults );
	}

	/**
	 * Get Value Control
	 *
	 * Get the settings for the value control
	 *
	 * @since  1.4.15
	 * @return string
	 */
	public function get_value_control() {
		return [
			'type'          => Controls_Manager::SELECT,
			'default'       => 'true',
			'label_block'   => true,
			'options'       => [
				'true'      => __( 'True', 'powerpack' ),
				'false'     => __( 'False', 'powerpack' ),
			],
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

		// Handle string value for correct comparison
		$value = ( 'true' === $value ) ? true : false;

		global $post;

		$field_value = get_field( $key );

		if ( is_archive() ) {
			$term = get_queried_object();

			if ( get_class( $term ) === 'WP_Term' ) {
				$field_value = get_field( $key, $term );
			}
		}

		// Check condition for user acf key.
		$curr_user = get_current_user_id();
		if ( empty( $field_value ) && $curr_user && true === get_field( $key, 'user_' . $curr_user ) ) {
			$field_value = true;
		}

		if ( isset( $field_value ) ) {

			// Compare setting with value
			$show = $value === $field_value;
		}

		return $this->compare( $show, true, $operator );
	}
}
