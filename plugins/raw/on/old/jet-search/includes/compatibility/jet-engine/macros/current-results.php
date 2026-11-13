<?php
/**
 * Macros: Current JetSearch Results
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Jet_Search_Macros_Current_Results extends Jet_Engine_Base_Macros {

	/**
	 * Returns the macros tag.
	 *
	 * @return string
	 */
	public function macros_tag() {

		return 'jet_search_current_results';
	}

	/**
	 * Returns the macros name.
	 *
	 * @return string
	 */
	public function macros_name() {

		return __( 'Current JetSearch Results', 'jet-search' );
	}

	/**
	 * Returns the list of macros arguments.
	 *
	 * @return array
	 */
	public function macros_args() {

		return array();
	}

	/**
	 * Main macros callback.
	 *
	 * This method returns an array of post IDs that match the current
	 * JetSearch Ajax results, based on the latest executed search query.
	 *
	 * @param array $args Macros arguments (not used).
	 *
	 * @return array An array of post IDs.
	 */
	public function macros_callback( $args = array() ) {

		$ids = jet_search_ajax_handlers()->get_current_results_ids();

		if ( empty( $ids ) || ! is_array( $ids ) ) {
			return '';
		}

		$ids = array_map( 'intval', $ids );
		$ids = array_values( array_unique( $ids ) );

		return implode( ',', $ids );
	}

}
