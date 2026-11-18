<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Ports;

if(!defined('ABSPATH')) exit();

/**
 * Where the plugin's option blobs live. Core code talks to the host platform's key-value
 * store only through this port. WordPress' implementation (wordpress/includes/platform/)
 * is the fallback; another platform binds its own before first use via
 * RevSliderGlobals::instance()->add('ThemePunch\SR\Ports\OptionStorage', $impl).
 * @since 7.2.1
 */
interface OptionStorage {

	/**
	 * @param string $key
	 * @param mixed  $default returned when the key does not exist
	 * @return mixed
	 */
	public function get($key, $default = false);

	/**
	 * @param string    $key
	 * @param mixed     $value
	 * @param bool|null $autoload null keeps the store's existing/default autoload behavior
	 * @return bool
	 */
	public function set($key, $value, $autoload = null);

	/**
	 * @param string $key
	 * @return bool
	 */
	public function delete($key);
}
