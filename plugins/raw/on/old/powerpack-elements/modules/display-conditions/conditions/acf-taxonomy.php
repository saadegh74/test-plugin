<?php
namespace PowerpackElements\Modules\DisplayConditions\Conditions;

// Powerpack Elements Classes
use PowerpackElements\Base\Condition;

// Elementor Classes
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Acf_Taxonomy extends Acf_Base {

	/**
	 * Get Name
	 *
	 * Get the name of the module
	 *
	 * @since  1.4.15
	 * @return string
	 */
	public function get_name() {
		return 'acf_taxonomy';
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
		return __( 'ACF Taxonomy', 'powerpack' );
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
			'description'   => __( 'Search ACF "Taxonomy" fields by label.', 'powerpack' ),
			'placeholder'   => __( 'Search Fields', 'powerpack' ),
			'query_options' => [
				'field_type'    => [
					'taxonomy',
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
	 * @return array
	 */
	public function get_value_control() {
		return [
			'description'   => __( 'Leave blank or select all for any term.', 'powerpack' ),
			'placeholder'   => __( 'Search Terms', 'powerpack' ),
			'type'          => 'pp-query',
			'post_type'     => '',
			'options'       => [],
			'label_block'   => true,
			'multiple'      => true,
			'query_type'    => 'terms',
			'include_type'  => true,
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
	public function check( $key, $operator, $values ) {
		$show = false;

		global $post;

		$field_value = get_field( $key );

		if ( is_archive() ) {
			$term = get_queried_object();

			if ( get_class( $term ) === 'WP_Term' ) {
				$field_value = get_field( $key, $term );
			}
		}

		if ( $field_value ) {
			$field_term_ids = $this->parse_field_values( $field_value );
			$value_term_ids = array_map( 'intval', $values );

			$show = ! empty( array_intersect( $field_term_ids, $value_term_ids ) );
		}

		return $this->compare( $show, true, $operator );
	}

	/**
	 * Parse field values
	 *
	 * Depending on the return formats and number of field values
	 * this function returns an array with the term IDs set in
	 * the field settings
	 *
	 * @since 1.4.15
	 *
	 * @access public
	 *
	 * @param   string      $posts              The posts saved in the field
	 * @return  array       $return_values      The array of post IDs
	 */
	public function parse_field_values( $terms ) {
		$return_values = [];

		if ( is_array( $terms ) ) {
			foreach ( $terms as $term ) {
				$return_values[] = ( is_a( $term, 'WP_Term' ) ) ? $term->term_id : $term;
			}
		} else {
			$return_values[] = ( is_a( $terms, 'WP_Term' ) ) ? $terms->term_id : $terms;
		}

		return $return_values;
	}
}
