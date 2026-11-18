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
 * WordPress' upload and content locations behind the Paths port.
 * @since 7.2.1
 */
class Paths implements Ports\Paths {

	public function upload_dir(){
		return wp_upload_dir();
	}

	public function content_dir(){
		return WP_CONTENT_DIR;
	}

	public function content_url(){
		return content_url();
	}

	public function plugins_url(){
		return \plugins_url(); //not the raw constant: this applies set_url_scheme() and the 'plugins_url' filter
	}
}
