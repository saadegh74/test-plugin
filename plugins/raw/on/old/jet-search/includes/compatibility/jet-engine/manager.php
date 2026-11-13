<?php
/**
 * JetEngine compatibility package manager.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Jet_Engine_Compatibility_JE class
 */
class Jet_Search_Compatibility_JE {

	/**
	 * Constructor for the class.
	 */
	function __construct() {

		if ( ! function_exists( 'jet_engine' ) ) {
			return;
		}

		// Register macros
		add_action( 'jet-engine/register-macros', array( $this, 'register_macros' ) );
	}

	/**
	 * Register JetEngine macros related to JetSearch.
	 *
	 * Loads files containing macros classes and initializes them.
	 *
	 * @return void
	 */
	public function register_macros() {

		require_once jet_search()->plugin_path( 'includes/compatibility/jet-engine/macros/current-results.php' );

		new Jet_Search_Macros_Current_Results();
	}

}
