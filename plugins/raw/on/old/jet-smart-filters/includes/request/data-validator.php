<?php
/**
 * Public request data validation helper.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Smart_Filters_Request_Data_Validator' ) ) {
	/**
	 * Validates generic untrusted request structures.
	 */
	class Jet_Smart_Filters_Request_Data_Validator {

		/**
		 * Validate nested taxonomy or meta query clauses against allowed keys.
		 *
		 * @param array  $clauses      Query clauses.
		 * @param array  $allowed_keys Allowed taxonomy or meta keys.
		 * @param string $key_name     Clause key name.
		 *
		 * @return array|false
		 */
		public function validate_query_clauses( $clauses, $allowed_keys, $key_name ) {

			if ( ! is_array( $clauses ) || empty( $allowed_keys ) || count( $clauses ) > 50 ) {
				return false;
			}

			foreach ( $clauses as $key => $clause ) {
				if ( 'relation' === $key ) {
					if ( ! is_string( $clause ) || ! in_array( strtoupper( $clause ), array( 'AND', 'OR' ), true ) ) {
						return false;
					}

					continue;
				}

				if ( ! is_array( $clause ) ) {
					return false;
				}

				if ( isset( $clause[ $key_name ] ) ) {
					if ( ! is_string( $clause[ $key_name ] ) || ! in_array( $clause[ $key_name ], $allowed_keys, true ) ) {
						return false;
					}
				} elseif ( false === $this->validate_query_clauses( $clause, $allowed_keys, $key_name ) ) {
					return false;
				}
			}

			return $clauses;
		}

		/**
		 * Limit the size and nesting of untrusted request data.
		 *
		 * @param mixed $value      Request data.
		 * @param int   $depth      Current nesting level.
		 * @param int   $max_depth  Maximum nesting level.
		 * @param int   $max_items  Maximum items in one array.
		 * @param int   $max_length Maximum string length.
		 *
		 * @return bool
		 */
		public function is_safe_request_data( $value, $depth = 0, $max_depth = 5, $max_items = 50, $max_length = 500 ) {

			if ( $depth > $max_depth ) {
				return false;
			}

			if ( is_scalar( $value ) || null === $value ) {
				return ! is_string( $value ) || strlen( $value ) <= $max_length;
			}

			if ( ! is_array( $value ) || count( $value ) > $max_items ) {
				return false;
			}

			foreach ( $value as $key => $item ) {
				if ( ! is_int( $key ) && ! is_string( $key ) ) {
					return false;
				}

				if ( is_string( $key ) && strlen( $key ) > $max_length ) {
					return false;
				}

				if ( ! $this->is_safe_request_data( $item, $depth + 1, $max_depth, $max_items, $max_length ) ) {
					return false;
				}
			}

			return true;
		}
	}
}
