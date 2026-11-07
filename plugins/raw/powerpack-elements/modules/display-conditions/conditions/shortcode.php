<?php
namespace PowerpackElements\Modules\DisplayConditions\Conditions;

// Powerpack Elements Classes
use PowerpackElements\Base\Condition;

// Elementor Classes
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * \Modules\DisplayConditions\Conditions\Condition
 *
 * @since  2.3.2
 */
class Shortcode extends Condition {

	/**
	 * Get Group
	 *
	 * Get the group of the condition
	 *
	 * @since  2.3.2
	 * @return string
	 */
	public function get_group() {
		return 'misc';
	}

	/**
	 * Get Name
	 *
	 * Get the name of the module
	 *
	 * @since  2.3.2
	 * @return string
	 */
	public function get_name() {
		return 'shortcode';
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
		return __( 'Shortcode', 'powerpack' );
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
		return [
			'type'          => Controls_Manager::TEXT,
			'default'       => '',
			'placeholder'   => __( '[shortcode attribute="value"]', 'powerpack' ),
			'label_block'   => true,
		];
	}

	/**
	 * Get Value Control
	 *
	 * Get the settings for the value control
	 *
	 * @since  2.3.2
	 * @return string
	 */
	public function get_value_control() {
		return [
			'type'          => Controls_Manager::TEXTAREA,
			'default'       => '',
			'description'   => __( 'Enter the string that the shortcode needs to return in order for the condition to apply.', 'powerpack' ),
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
	public function check( $name, $operator, $value ) {
		$show = false;
		$output = do_shortcode( $name );

		if ( is_string( $output ) && $value === $output ) {
			$show = true;
		}

		return $this->compare( $show, true, $operator );
	}
}
