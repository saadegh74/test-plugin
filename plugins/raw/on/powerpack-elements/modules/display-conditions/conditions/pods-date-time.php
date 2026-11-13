<?php
namespace PowerpackElements\Modules\DisplayConditions\Conditions;

// Powerpack Elements Classes
use PowerpackElements\Base\Condition;

// Elementor Classes
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Pods_Date_Time extends Pods_Base {

	/**
	 * Get Name
	 *
	 * Get the name of the module
	 *
	 * @since  2.3.2
	 * @return string
	 */
	public function get_name() {
		return 'pods_date_time';
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
		return __( 'Pods Date / Time', 'powerpack' );
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
			'description'   => __( 'Search Pods "Date" and "Date Time" fields by label.', 'powerpack' ),
			'placeholder'   => __( 'Search Fields', 'powerpack' ),
			'query_options' => [
				'field_type'    => [
					'date',
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
		$default_date_start = date( 'Y-m-d', strtotime( '-3 day' ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );

		return [
			'label'     => __( 'Before', 'powerpack' ),
			'type'      => \Elementor\Controls_Manager::DATE_TIME,
			'picker_options' => [
				'enableTime' => true,
			],
			'label_block'   => true,
			'default'       => $default_date_start,
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
					// Convert to timestamps
					$field_value_ts = strtotime( $_value ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
					$value_ts       = strtotime( $value ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );

					// Set display condition
					$show = $field_value_ts < $value_ts;
				}
			} else {
				// Convert to timestamps
				$field_value_ts = strtotime( $field_value ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
				$value_ts       = strtotime( $value ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );

				// Set display condition
				$show = $field_value_ts < $value_ts;
			}
		}

		return $this->compare( $show, true, $operator );
	}
}
