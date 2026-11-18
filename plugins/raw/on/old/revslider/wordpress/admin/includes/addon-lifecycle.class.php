<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * The WordPress answers to "what extensions live on this host": add-ons ship as WP plugins,
 * so listing, activation state and the (de)activation calls wrap WP's plugin machinery.
 * RevSliderAddons keeps the portable policy (catalogue merge, slug rules, download flow)
 * and asks this class for every host fact.
 */
class RevSliderAddonLifecycle {

	/** @return array installed extensions in the host's raw shape: file => ['Name', 'Version', ...] */
	public function installed_extensions(){
		if(!function_exists('get_plugins')) require_once ABSPATH . 'wp-admin/includes/plugin.php';
		return get_plugins();
	}

	/** @return bool */
	public function is_active($file){
		if(!function_exists('is_plugin_active')) require_once ABSPATH . 'wp-admin/includes/plugin.php';
		return is_plugin_active($file);
	}

	/** @return bool */
	public function is_network_active($file){
		if(!function_exists('is_plugin_active_for_network')) require_once ABSPATH . 'wp-admin/includes/plugin.php';
		return is_plugin_active_for_network($file);
	}

	/** @return bool is this request administering the whole network, not one site? */
	public function is_network_admin_context(){
		return is_network_admin();
	}

	/** @return true|WP_Error */
	public function activate($file, $network_wide = false){
		if(!function_exists('activate_plugins')) require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$result = activate_plugins($file, false, (bool)$network_wide);
		return is_wp_error($result) ? $result : true;
	}

	/** @return void */
	public function deactivate($file, $network_wide = false){
		if(!function_exists('deactivate_plugins')) require_once ABSPATH . 'wp-admin/includes/plugin.php';
		deactivate_plugins($file, false, (bool)$network_wide);
	}

	/** @return bool does the extension's main file exist on disk? */
	public function extension_file_exists($file){
		return file_exists(WP_PLUGIN_DIR . '/' . $file);
	}

	/** @return string the directory the host installs extensions into */
	public function extension_dir(){
		return WP_PLUGIN_DIR;
	}

	/** drop the host's cached extension list after an install or removal */
	public function flush_extension_cache(){
		wp_clean_plugins_cache(true);
	}
}
