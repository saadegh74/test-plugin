<?php
namespace PowerpackElements\Modules\PresetsStyle;

use PowerpackElements\Base\Module_Base;
use PowerpackElements\Classes\PP_Helper;

use PowerpackElements\Modules\PresetsStyle\Controls\Presets_Style;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Module.
 */
class Module extends Module_Base {

	//const QUERY_CONTROL_ID = 'pp-presets-style';

	/**
	 * Module should load or not.
	 *
	 * @since 2.9.0
	 * @access public
	 *
	 * @return bool true|false.
	 */
	public static function is_active() {
		return true;
	}

	/**
	 * Constructer.
	 *
	 * @since 2.9.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();
		if ( is_extension_enabled( 'presets-style' ) ) {
			$this->add_actions();
		}
	}

	/**
	 * Get Module Name.
	 *
	 * @since 2.9.0
	 * @access public
	 *
	 * @return string Module name.
	 */
	public function get_name() {
		return 'presets-style';
	}

	/**
	 * Fetch the presets.
	 *
	 * @param string $preset_name Widget preset.
	 * @since 2.9.0
	 */
	public static function get_presets( $preset_name ) {
		$design = POWERPACK_ELEMENTS_PATH . 'assets/presets-json/' . $preset_name . '.json';
		if ( ! is_readable( $design ) ) {
			return false;
		}
		return file_get_contents( $design ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}

	/**
	 * Apply the presets.
	 *
	 * @since 2.9.0
	 */
	public static function apply_preset() {
		check_ajax_referer( 'pp-presets-nonce', 'nonce' );
		$presets = self::get_presets( substr( $_POST['widget'], 3 ) );
		wp_send_json_success( $presets, 200 );
	}

	/**
	 * Register Control
	 *
	 * @since 2.9.0
	 */
	public function register_controls() {
		$controls_manager = \Elementor\Plugin::$instance->controls_manager;
		//$controls_manager->register_control( self::QUERY_CONTROL_ID, new Presets_Style() );
		$controls_manager->register( new Presets_Style() );
	}

	/**
	 * Add actions
	 *
	 * @since 2.9.0
	 */
	protected function add_actions() {
		add_action( 'wp_ajax_pp_widget_presets', array( $this, 'apply_preset' ) );
		//add_action( 'elementor/controls/controls_registered', array( $this, 'register_controls' ) );
		add_action( 'elementor/controls/register', array( $this, 'register_controls' ) );
	}
}
