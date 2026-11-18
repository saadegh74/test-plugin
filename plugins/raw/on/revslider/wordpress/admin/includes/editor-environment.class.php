<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * The editor boot values only WordPress can answer: its admin page, text direction, the shop probe, the
 * raw plugins URL and WP_DEBUG. Everything else SR7.E carries is the core's (Core\Admin\EditorEnvironment).
 */
class RevSliderEditorEnvironment extends \ThemePunch\SR\Core\Admin\EditorEnvironment {

	/** @return string */
	public function admin_url(){
		return admin_url('admin.php?page=revslider');
	}

	/** @return bool */
	public function rtl(){
		return is_rtl();
	}

	/** @return bool */
	public function shop_active(){
		return RevSliderWooCommerce::woo_exists();
	}

	/** @return string the raw constant, not the filtered plugins_url(): SR7.E.wp_plugin_url stays what sites had */
	public function plugin_root_url(){
		return WP_PLUGIN_URL;
	}

	/** @return bool */
	public function debug(){
		return defined('WP_DEBUG') && WP_DEBUG;
	}
}
