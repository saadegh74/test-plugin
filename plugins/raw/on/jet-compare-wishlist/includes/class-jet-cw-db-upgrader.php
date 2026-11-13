<?php
/**
 * Jet Compare & Wishlist DB Upgrader Сlass
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_CW_DB_Upgrader' ) ) {

	/**
	 * Define Jet_CW_DB_Upgrader class
	 */
	class Jet_CW_DB_Upgrader {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private static $instance = null;

		/**
		 * Constructor for the class
		 */
		public function init() {
			/**
			 * Plugin initialized on new Jet_CW_DB_Upgrader call.
			 * Please ensure, that it called only on admin context
			 */
			$this->init_upgrader();
		}

		/**
		 * Initialize upgrader module
		 *
		 * @return void
		 */
		public function init_upgrader() {
			new CX_Db_Updater(
				[
					'slug'      => 'jet-cw',
					'version'   => '1.5.5',
					'callbacks' => [
						'1.3.0' => [
							[ $this, 'clear_elementor_cache' ],
						],
						'1.3.1' => [
							[ $this, 'clear_elementor_cache' ],
						],
						'1.3.2' => [
							[ $this, 'clear_elementor_cache' ],
						],
						'1.4.0' => [
							[ $this, 'clear_elementor_cache' ],
						],
						'1.4.4' => [
							[ $this, 'clear_elementor_cache' ],
						],
						'1.5.0' => [
							[ $this, 'clear_elementor_cache' ],
						],
						'1.5.2' => [
							[ $this, 'update_db_1_5_0' ],
						],
						'1.5.5' => [
							[ $this, 'clear_elementor_cache' ],
						],
					],
				]
			);
		}

		/**
		 * Clear elementor cache.
		 *
		 * Clear Elementor plugin files cache.
		 *
		 * @since  1.3.0
		 * @access public
		 */
		public function clear_elementor_cache() {
			if ( class_exists( 'Elementor\Plugin' ) ) {
				jet_cw()->elementor()->files_manager->clear_cache();
			}
		}

		/**
		 * Update db 1.5.0.
		 *
		 * Update database according to new version changes.
		 *
		 * @since  1.5.2
		 * @access public
		 */
		public function update_db_1_5_0() {

			$settings = get_option( jet_cw()->settings->key, false );

			if ( $settings && ! isset( $settings['compare_message_max_items'] ) ) {
				$settings['compare_message_max_items'] = 'You can`t add more product in compare';
			}

			update_option( jet_cw()->settings->key, $settings );

		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @return object
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

	}

}

/**
 * Returns instance of Jet_CW_DB_Upgrader
 *
 * @return object
 */
function jet_cw_db_upgrader() {
	return Jet_CW_DB_Upgrader::get_instance();
}