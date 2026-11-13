<?php
/**
 * Hierarchy request validation helper.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Smart_Filters_Hierarchy_Request_Validator' ) ) {
	/**
	 * Normalizes and validates public hierarchy request data.
	 */
	class Jet_Smart_Filters_Hierarchy_Request_Validator {

		/**
		 * Normalize hierarchy layout arguments before signing or rendering them.
		 *
		 * @param array $args Hierarchy layout arguments.
		 *
		 * @return array|false
		 */
		public function prepare_args( $args = array() ) {

			if ( ! is_array( $args ) ) {
				return false;
			}

			if ( isset( $args['layout_options'] ) ) {
				if ( ! is_array( $args['layout_options'] ) ) {
					return false;
				}

				$layout_options = $args['layout_options'];
				unset( $args['layout_options'] );
				$args = array_merge( $args, $layout_options );
			}

			$prepared_args = array(
				'show_label'      => ! empty( $args['show_label'] ) && filter_var( $args['show_label'], FILTER_VALIDATE_BOOLEAN ),
				'display_options' => array(),
			);

			if ( ! empty( $args['display_options'] ) ) {
				if ( ! is_array( $args['display_options'] ) ) {
					return false;
				}

				foreach ( array( 'counter_prefix', 'counter_suffix' ) as $key ) {
					if ( ! isset( $args['display_options'][ $key ] ) ) {
						continue;
					}

					$value = $args['display_options'][ $key ];

					if ( ! is_scalar( $value ) || strlen( (string) $value ) > 500 ) {
						return false;
					}

					$prepared_args['display_options'][ $key ] = (string) $value;
				}
			}

			return $prepared_args;
		}

		/**
		 * Prepare and validate a hierarchical filter map.
		 *
		 * @param int $filter_id Filter ID.
		 *
		 * @return array
		 */
		public function prepare_map( $filter_id ) {

			$hierarchy = get_post_meta( $filter_id, '_ih_source_map', true );

			if ( ! is_array( $hierarchy ) || empty( $hierarchy ) || count( $hierarchy ) > 20 ) {
				return array();
			}

			$prepared_hierarchy = array();

			foreach ( array_values( $hierarchy ) as $level ) {
				if ( ! is_array( $level ) || empty( $level['tax'] ) || ! is_scalar( $level['tax'] ) ) {
					return array();
				}

				$taxonomy = sanitize_key( $level['tax'] );

				if ( ! taxonomy_exists( $taxonomy ) ) {
					return array();
				}

				$level['tax']         = $taxonomy;
				$prepared_hierarchy[] = $level;
			}

			return $prepared_hierarchy;
		}

		/**
		 * Prepare hierarchy depth from the request.
		 *
		 * @param array $request   Request data.
		 * @param int   $max_depth Maximum accepted depth.
		 *
		 * @return int|false|null
		 */
		public function prepare_depth( $request, $max_depth ) {

			// A missing depth refreshes all hierarchy levels. Keep false as the
			// legacy marker expected by Jet_Smart_Filters_Hierarchy.
			if ( ! array_key_exists( 'depth', $request ) ) {
				return false;
			}

			if (
				! is_scalar( $request['depth'] )
				|| ! preg_match( '/^\d+$/', (string) $request['depth'] )
			) {
				return null;
			}

			$depth = absint( $request['depth'] );

			return $depth <= $max_depth ? $depth : null;
		}

		/**
		 * Prepare selected hierarchy values from the request.
		 *
		 * @param array $request   Request data.
		 * @param array $hierarchy Validated hierarchy map.
		 *
		 * @return array|false
		 */
		public function prepare_values( $request, $hierarchy ) {

			if ( empty( $request['values'] ) ) {
				return array();
			}

			if ( ! is_array( $request['values'] ) || count( $request['values'] ) > count( $hierarchy ) ) {
				return false;
			}

			$values = array_values( wp_unslash( $request['values'] ) );

			foreach ( $values as $index => &$value ) {
				if (
					! is_array( $value )
					|| ! isset( $value['tax'], $value['value'] )
					|| ! is_scalar( $value['tax'] )
					|| ! is_scalar( $value['value'] )
				) {
					return false;
				}

				$taxonomy   = sanitize_key( $value['tax'] );
				$term_value = sanitize_text_field( (string) $value['value'] );

				if ( $taxonomy !== $hierarchy[ $index ]['tax'] || strlen( $term_value ) > 200 ) {
					return false;
				}

				$value = array(
					'tax'   => $taxonomy,
					'value' => $term_value,
				);
			}
			unset( $value );

			return $values;
		}
	}
}
