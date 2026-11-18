<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Platform\WordPress;

use ThemePunch\SR\Ports;

if(!defined('ABSPATH')) exit();

/**
 * WordPress' options table behind the OptionStorage port.
 * @since 7.2.1
 */
class OptionStorage implements Ports\OptionStorage {

	public function get($key, $default = false){
		return get_option($key, $default);
	}

	public function set($key, $value, $autoload = null){
		return update_option($key, $value, $autoload);
	}

	public function delete($key){
		return delete_option($key);
	}
}
