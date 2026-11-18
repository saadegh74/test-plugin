<?php
/**
 * @author	ThemePunch <info@themepunch.com>
 * @link	  https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Core\Addon;

if(!defined('ABSPATH')) exit();

/**
 * Files an AddOn computed for itself: a depth map, a mask, a baked gradient. They go to
 * uploads/revslider/addons/<title>/ and deliberately NOT into the Media Library - they are machine output keyed
 * by content, not something a user picked, and the optimizer's recompression would destroy data packed into the
 * channels. Keeping them out also spares the customer's library one entry plus five thumbnails per file.
 * The classic name RevSliderAddonStore is AddOn API; a platform-neutral AddOn half calls it.
 **/
class Store extends \ThemePunch\SR\Core\Functions {

	const FOLDER	= 'revslider/addons/';
	const MAX_BYTES	= 8388608;

	//written from the verified signature, never from what the caller claimed
	private static $types = ['image/png' => 'png', 'image/webp' => 'webp'];

	/**
	 * Store raw bytes under a registered AddOn's folder.
	 * @param string $title  the AddOn title ('layerdepth'); its plugin must be registered with the core Base
	 * @param string $name   base name WITHOUT extension, the caller's content hash - a-z 0-9 - _ only, never a path
	 * @param string $bitmap data: URL carrying the bytes
	 * @return array ['success' => bool, 'url' => string, 'message' => string]
	 */
	public static function save($title, $name, $bitmap){
		$title	= preg_replace('/[^a-z0-9]/', '', strtolower((string) $title));
		$name	= preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $name);

		if($title === '' || Base::get_addon('revslider-'.$title.'-addon') === false){
			return self::fail(__('Unknown Add-On', 'revslider'));
		}
		if($name === '' || strlen($name) > 64) return self::fail(__('Invalid file name', 'revslider'));

		$bytes = self::decode($bitmap);
		if($bytes === false) return self::fail(__('Image has an invalid data', 'revslider'));

		$ext = self::signature($bytes);
		if($ext === false) return self::fail(__('Image has an invalid type', 'revslider'));

		$upload = \ThemePunch\SR\Core\Registry::instance()->get('RevSliderFunctions')->paths()->upload_dir(); //static: no $this to ask
		if(!empty($upload['error'])) return self::fail($upload['error']);

		$dir = trailingslashit($upload['basedir']).self::FOLDER.$title.'/';
		if(!wp_mkdir_p($dir)) return self::fail(sprintf(__('Could not create destination directory: %s', 'revslider'), $dir));

		//always rewritten: the name is the caller's key, not a hash of these bytes, so a regenerated file
		//under the same name must win
		$file = $dir.$name.'.'.$ext;
		if(file_put_contents($file, $bytes) === false){
			return self::fail(sprintf(__('Could not create destination file: %s', 'revslider'), $file));
		}
		self::protect($dir);

		return ['success' => true, 'url' => trailingslashit($upload['baseurl']).self::FOLDER.$title.'/'.$name.'.'.$ext, 'message' => ''];
	}

	/** @return string|false the decoded bytes, false when the data URL is malformed or oversized */
	private static function decode($bitmap){
		if(!is_string($bitmap) || !preg_match('~^data:image/(png|webp);base64,~i', $bitmap)) return false;

		$raw = substr($bitmap, strpos($bitmap, ',') + 1);
		if(strlen($raw) > self::MAX_BYTES * 2) return false;

		$bytes = base64_decode(str_replace(' ', '+', $raw), true);
		if($bytes === false || $bytes === '' || strlen($bytes) > self::MAX_BYTES) return false;

		return $bytes;
	}

	/**
	 * The extension the BYTES earn, so a mislabelled data URL cannot land as something executable
	 * @return string|false
	 */
	private static function signature($bytes){
		if(strncmp($bytes, "\x89PNG\r\n\x1a\n", 8) === 0) return self::$types['image/png'];
		if(strncmp($bytes, 'RIFF', 4) === 0 && substr($bytes, 8, 4) === 'WEBP') return self::$types['image/webp'];

		return false;
	}

	/** @return void keeps the folder from being listed, as every other generated SR folder does */
	private static function protect($dir){
		if(!is_file($dir.'index.php')) @file_put_contents($dir.'index.php', "<?php\n// Silence is golden.\n");
	}

	/** @return array */
	private static function fail($message){
		return ['success' => false, 'url' => '', 'message' => $message];
	}
}
