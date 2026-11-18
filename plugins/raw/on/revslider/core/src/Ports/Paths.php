<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Ports;

if(!defined('ABSPATH')) exit();

/**
 * Where the host keeps public files. The /revslider/* layout underneath these
 * roots is core policy; only the roots themselves are platform facts.
 * @since 7.2.1
 */
interface Paths {

	/**
	 * the managed-uploads root
	 * @return array basedir, baseurl (both without trailing slash), path, url, error - the shape
	 *               WordPress' wp_upload_dir() established; every platform fills the same keys
	 */
	public function upload_dir();

	/** @return string absolute path of the content directory (WordPress: WP_CONTENT_DIR) */
	public function content_dir();

	/** @return string public URL of the content directory */
	public function content_url();

	/** @return string public base URL under which extensions keep their assets, no trailing slash */
	public function plugins_url();
}
