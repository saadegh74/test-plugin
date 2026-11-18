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
 * WP_Filesystem behind the Filesystem port. extract_zip carries the
 * FS_METHOD 'direct' retry exactly once, instead of the three copies the
 * import, add-on and object-library paths used to hold.
 * @since 7.2.1
 */
class Filesystem implements Ports\Filesystem {

	/** @return \WP_Filesystem_Base */
	protected function fs(){
		global $wp_filesystem;
		if(!is_object($wp_filesystem)){
			if(!function_exists('WP_Filesystem')) require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}
		return $wp_filesystem;
	}

	public function exists($path){
		return $this->fs()->exists($path);
	}

	public function read($path){
		return $this->fs()->get_contents($path);
	}

	public function write($path, $contents){
		return $this->fs()->put_contents($path, $contents); //chmods to FS_CHMOD_FILE by default
	}

	public function delete($path, $recursive = false){
		$done = $this->fs()->delete($path, $recursive);
		if($done === false && $this->fs()->exists($path)){
			//an FTP/ssh filesystem that cannot write here: force direct file access and try again, as extract_zip does
			if(!defined('FS_METHOD')) define('FS_METHOD', 'direct');
			WP_Filesystem();
			$done = $this->fs()->delete($path, $recursive);
		}
		return $done;
	}

	public function extract_zip($zip, $dest){
		$this->fs(); //loads file.php and initializes the filesystem for unzip_file()

		$result = unzip_file($zip, $dest);
		if(is_wp_error($result)){
			//an FTP/ssh filesystem that cannot write here: force direct file access and try again
			if(!defined('FS_METHOD')) define('FS_METHOD', 'direct');
			WP_Filesystem();
			$result = unzip_file($zip, $dest);
		}

		return $result;
	}

	public function temp_file($hint){
		if(!function_exists('wp_tempnam')) require_once(ABSPATH . 'wp-admin/includes/file.php');
		return wp_tempnam($hint);
	}

	public function list_files($dir){
		if(!function_exists('list_files')) require_once(ABSPATH . 'wp-admin/includes/file.php');
		return list_files($dir);
	}

	public function copy_dir($from, $to){
		$this->fs(); //copy_dir() works through the global WP_Filesystem, which fs() sets up
		return copy_dir($from, $to) === true;
	}
}
