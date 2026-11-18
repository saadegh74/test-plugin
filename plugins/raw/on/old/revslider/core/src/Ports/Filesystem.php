<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Ports;

if(!defined('ABSPATH')) exit();

/**
 * File access behind one port. On WordPress this wraps the WP_Filesystem
 * abstraction (FTP credentials and all); a plain host implements it with
 * direct PHP file functions and ZipArchive.
 * @since 7.2.1
 */
interface Filesystem {

	/**
	 * @param string $path
	 * @return bool
	 */
	public function exists($path);

	/**
	 * @param string $path
	 * @return string|false the file contents, false on failure
	 */
	public function read($path);

	/**
	 * @param string $path
	 * @param string $contents
	 * @return bool
	 */
	public function write($path, $contents);

	/**
	 * @param string $path
	 * @param bool   $recursive delete a directory with everything in it
	 * @return bool
	 */
	public function delete($path, $recursive = false);

	/**
	 * unpack a zip archive into a directory
	 * @param string $zip  path to the archive
	 * @param string $dest existing destination directory
	 * @return true|mixed true on success, the platform-native error otherwise (WordPress: WP_Error)
	 */
	public function extract_zip($zip, $dest);

	/**
	 * @param string $hint filename or URL the temp name is derived from
	 * @return string|false path of a created temporary file
	 * @since 7.2.1
	 */
	public function temp_file($hint);

	/**
	 * @param string $dir
	 * @return array|false all file paths under $dir, recursively
	 * @since 7.2.1
	 */
	public function list_files($dir);

	/**
	 * copy a directory tree into an existing destination directory, subfolders included
	 * @param string $from
	 * @param string $to
	 * @return bool
	 * @since 7.2.1
	 */
	public function copy_dir($from, $to);
}
