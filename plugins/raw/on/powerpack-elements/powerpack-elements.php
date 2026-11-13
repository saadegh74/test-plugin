<?php
/**
 * Plugin Name: PowerPack Pro for Elementor
 * Plugin URI: https://powerpackelements.com
 * Description: Extend Elementor Page Builder with 70+ Creative Widgets and exciting extensions.
 * Version: 2.9.19
 * Author: Team IdeaBox - PowerPack Elements
 * Author URI: http://powerpackelements.com
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: powerpack
 * Domain Path: /languages
 * Elementor tested up to: 3.12.1
 * Elementor Pro tested up to: 3.12.2
 *
 * @package PPE
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'POWERPACK_ELEMENTS_VER', '2.9.19' );
define( 'POWERPACK_ELEMENTS_PATH', plugin_dir_path( __FILE__ ) );
define( 'POWERPACK_ELEMENTS_BASE', plugin_basename( __FILE__ ) );
define( 'POWERPACK_ELEMENTS_URL', plugins_url( '/', __FILE__ ) );
define( 'POWERPACK_ELEMENTS_ELEMENTOR_VERSION_REQUIRED', '3.5.0' );
define( 'POWERPACK_ELEMENTS_PHP_VERSION_REQUIRED', '5.6' );

require_once POWERPACK_ELEMENTS_PATH . 'includes/helper-functions.php';
require_once POWERPACK_ELEMENTS_PATH . 'plugin.php';
require_once POWERPACK_ELEMENTS_PATH . 'classes/class-pp-admin-settings.php';
require_once POWERPACK_ELEMENTS_PATH . 'classes/class-pp-login-register.php';
require_once POWERPACK_ELEMENTS_PATH . 'classes/class-pp-header-footer.php';
require_once POWERPACK_ELEMENTS_PATH . 'classes/class-pp-config.php';
require_once POWERPACK_ELEMENTS_PATH . 'classes/class-pp-helper.php';
require_once POWERPACK_ELEMENTS_PATH . 'classes/class-pp-taxonomy-thumbnail.php';
require_once POWERPACK_ELEMENTS_PATH . 'classes/class-pp-posts-helper.php';
require_once POWERPACK_ELEMENTS_PATH . 'classes/class-pp-wpml.php';
require_once POWERPACK_ELEMENTS_PATH . 'classes/class-pp-attachment.php';
require_once POWERPACK_ELEMENTS_PATH . 'classes/class-pp-recaptcha.php';
require_once POWERPACK_ELEMENTS_PATH . 'includes/updater/update-config.php';
if ( is_pp_woo_builder() ) {
	require_once POWERPACK_ELEMENTS_PATH . 'classes/class-pp-woo-builder.php';
	require_once POWERPACK_ELEMENTS_PATH . 'classes/class-pp-woo-builder-preview.php';
	require_once POWERPACK_ELEMENTS_PATH . 'classes/class-pp-woo-helper.php';
}
if ( did_action( 'elementor/loaded' ) ) {
	require_once POWERPACK_ELEMENTS_PATH . 'classes/class-pp-templates-lib.php';
}

/**
 * Check if Elementor is installed
 *
 * @since 1.0
 */
if ( ! function_exists( '_is_elementor_installed' ) ) {
	function _is_elementor_installed() {
		$file_path         = 'elementor/elementor.php';
		$installed_plugins = get_plugins();
		return isset( $installed_plugins[ $file_path ] );
	}
}

/**
 * Shows notice to user if Elementor plugin
 * is not installed or activated or both
 *
 * @since 1.0
 **/
function pp_fail_load() {
	$plugin = 'elementor/elementor.php';

	if ( _is_elementor_installed() ) {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );
		$message        = sprintf( __( '%1$sPowerPack%2$s requires %1$sElementor%2$s plugin to be active. Please activate Elementor to continue.', 'powerpack' ), '<strong>', '</strong>' );
		$button_text    = __( 'Activate Elementor', 'powerpack' );

	} else {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		$activation_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' );
		$message        = sprintf( __( '%1$sPowerPack%2$s requires %1$sElementor%2$s plugin to be installed and activated. Please install Elementor to continue.', 'powerpack' ), '<strong>', '</strong>' );
		$button_text    = __( 'Install Elementor', 'powerpack' );
	}

	$button = '<p><a href="' . $activation_url . '" class="button-primary">' . $button_text . '</a></p>';

	printf( '<div class="error"><p>%1$s</p>%2$s</div>', wp_kses_post( $message ), wp_kses_post( $button ) );
}

/**
 * Shows notice to user if
 * Elementor version if outdated
 *
 * @since 1.0
 */
function pp_fail_load_out_of_date() {
	if ( ! current_user_can( 'update_plugins' ) ) {
		return;
	}

	$message = __( 'PowerPack requires Elementor version at least ' . POWERPACK_ELEMENTS_ELEMENTOR_VERSION_REQUIRED . '. Please update Elementor to continue.', 'powerpack' );

	printf( '<div class="error"><p>%1$s</p></div>', esc_html( $message ) );
}

/**
 * Shows notice to user if minimum PHP
 * version requirement is not met
 *
 * @since 1.0
 */
function pp_fail_php() {
	$message = __( 'PowerPack requires PHP version ' . POWERPACK_ELEMENTS_PHP_VERSION_REQUIRED . '+ to work properly. The plugins is deactivated for now.', 'powerpack' );

	printf( '<div class="error"><p>%1$s</p></div>', esc_html( $message ) );

	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
}

/**
 * Deactivates the plugin
 *
 * @since 1.0
 */
function pp_deactivate() {
	deactivate_plugins( plugin_basename( __FILE__ ) );
}

/**
 * Load theme textdomain
 *
 * @since 1.0
 */
function pp_load_plugin_textdomain() {
	load_plugin_textdomain( 'powerpack', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'pp_init' );

function pp_init() {
	if ( class_exists( 'Caldera_Forms' ) ) {
		add_filter( 'caldera_forms_force_enqueue_styles_early', '__return_true' );
	}

	// Notice if the Elementor is not active.
	if ( ! did_action( 'elementor/loaded' ) ) {
		add_action( 'admin_notices', 'pp_fail_load' );
		return;
	}

	// Check for required Elementor version.
	if ( ! version_compare( ELEMENTOR_VERSION, POWERPACK_ELEMENTS_ELEMENTOR_VERSION_REQUIRED, '>=' ) ) {
		add_action( 'admin_notices', 'pp_fail_load_out_of_date' );
		add_action( 'admin_init', 'pp_deactivate' );
		return;
	}

	// Check for required PHP version.
	if ( ! version_compare( PHP_VERSION, POWERPACK_ELEMENTS_PHP_VERSION_REQUIRED, '>=' ) ) {
		add_action( 'admin_notices', 'pp_fail_php' );
		add_action( 'admin_init', 'pp_deactivate' );
		return;
	}

	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	/* $lite_dirname   = 'powerpack-lite-for-elementor';
	$lite_active    = is_plugin_active( $lite_dirname . '/powerpack-lite-elementor.php' );
	$plugin_dirname = basename( dirname( dirname( __FILE__ ) ) );

	if ( defined( 'POWERPACK_ELEMENTS_LITE_VER' ) || ( $plugin_dirname != $lite_dirname && $lite_active ) ) {
		add_action( 'admin_init', 'pp_deactivate_lite', 1 );
	} */

	add_action( 'init', 'pp_load_plugin_textdomain' );

	/**
	 * Enable CSV Upload option
	 * 
	 * Enable CSV Upload option to bypass the WordPress security and upload the CSV file to the site.
	 * CSV files are used in PowerPack Table Widget for generating tables using preset data.
	 * 
	 * @since 1.5.1
	 * 
	 * @param Array $mimes Array of all the MIME types supported by WordPress.
	 * 
	 * @return Array $mimes Array of all the MIME types supported by WordPress.
	 */

	$csv_upload = get_option('pp_enable_csv_upload');

	if ( 'enabled' === $csv_upload ) {
		add_filter( 'upload_mimes', function($mimes){

			$mimes['csv'] = 'text/csv';
			return $mimes;
		} );
	}
}

/**
 * Enable white labeling setting form after re-activating the plugin
 *
 * @since 1.0.1
 * @return void
 */
function pp_plugin_activation() {
	$settings = get_option( 'pp_elementor_settings' );

	if ( is_array( $settings ) ) {
		$settings['hide_wl_settings'] = 'off';
		$settings['hide_plugin']      = 'off';
	}

	update_option( 'pp_elementor_settings', $settings );
}
register_activation_hook( __FILE__, 'pp_plugin_activation' );

/**
 * Add settings page link to plugin page
 *
 * @since 1.4.4
 */
function pp_add_plugin_page_settings_link( $links ) {
	$links[] = '<a href="' . admin_url( 'admin.php?page=powerpack-settings' ) . '">' . __( 'Settings', 'powerpack' ) . '</a>';
	return $links;
}
add_filter( 'plugin_action_links_' . POWERPACK_ELEMENTS_BASE, 'pp_add_plugin_page_settings_link' );

/**
 * Auto deactivate PowerPack Lite.
 *
 * @since 2.1.0
 */
function pp_deactivate_lite() {
	deactivate_plugins( 'powerpack-lite-for-elementor/powerpack-lite-elementor.php' );
}
function powerpack_elements_update_checker() {
	$json_url    = 'https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/powerpack-elements/info.json';
	$plugin_slug = 'powerpack-elements';
	require 'plugin-update-checker/plugin-update-checker.php';
	$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
		$json_url,
		__FILE__, //Full path to the main plugin file or functions.php.
		$plugin_slug
	);
}
add_action( 'admin_init', 'powerpack_elements_update_checker', 99 );