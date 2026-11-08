<?php
namespace PowerpackElements\Modules\DisplayConditions\Conditions;

// Powerpack Elements Classes
use PowerpackElements\Base\Condition;

// Elementor Classes
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Pods_Base extends Condition {

	public $name_control_defaults = [
		'type'          => 'pp-query',
		'post_type'     => '',
		'options'       => [],
		'query_type'    => 'pods',
		'label_block'   => true,
		'multiple'      => false,
		'query_options' => [
			'show_type' => false,
			'show_field_type' => true,
		],
	];

	/**
	 * Checks if current condition is supported
	 *
	 * @since  2.3.2
	 * @return bool
	 */
	public static function is_supported() {
		//return class_exists( '\Pods' );
		return function_exists( 'pods' );
	}

	/**
	 * Get Group
	 *
	 * Get the group of the condition
	 *
	 * @since  2.3.2
	 * @return string
	 */
	public function get_group() {
		return 'pods';
	}

	/**
	 * Get Field Post
	 *
	 * Retrieve the ACF field post object by id
	 *
	 * @since  2.3.2
	 * @return string
	 */
	public function get_field_post( $post_id ) {
		global $post;

		$field_post = get_posts( [
			'post__in'      => [ $post_id ],
			'post_type'     => '_pods_field',
			'post_status'   => 'publish',
			'numberposts'   => 1,
		] );

		if ( $field_post[0] ) {
			return $field_post;
		}

		return false;
	}

	/**
	 * Get Name Control Defaults
	 *
	 * Get the settings for the name control
	 *
	 * @since  2.3.2
	 * @return array
	 */
	public function get_name_control_defaults() {
		return;
	}
}
