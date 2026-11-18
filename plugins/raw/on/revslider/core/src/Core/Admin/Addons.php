<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

namespace ThemePunch\SR\Core\Admin;

if(!defined('ABSPATH')) exit();

/**
 * Add-On management.
 *
 * Add-Ons are ordinary WordPress plugins named revslider-<name>-addon, installed straight from the
 * ThemePunch servers instead of wp.org: download_addon() fetches the zip and unpacks it into the plugins
 * folder, activate_addon() switches it on. The catalogue lives in the rs-addons option; $addon_version_required
 * declares the minimum add-on version the current core needs.
 */
class Addons extends \ThemePunch\SR\Core\Functions {
	//private $addon_version_required = '2.0.0'; //this holds the globally needed addon version for the current RS version
	private $addons_path			= '/revslider/addons/';
	private $addons_basedir			= '';
	private $addons_baseurl			= '';
	private $addons_list			= [];
	private $addons_list_short		= [];

	//Minimum add-on version required by the CURRENT core version (keys = official add-on slugs from the rs-addons list).
	//Since 7.2.0 the family ships and updates together: one number for every add-on, raised with the core.
	private $addon_version_required = [
		'revslider-404-addon'                 => '7.2.1',
		'revslider-aitranslate-addon'         => '7.2.6',
		'revslider-backup-addon'              => '7.2.2',
		'revslider-beforeafter-addon'         => '7.2.9',
		'revslider-bubblemorph-addon'         => '7.2.5',
		'revslider-carouselx-addon'           => '7.2.7',
		'revslider-chalkline-addon'           => '7.2.3',
		'revslider-charts-addon'              => '7.2.5',
		'revslider-commerce-addon'            => '7.2.6',
		'revslider-crystalglass-addon'        => '7.2.9',
		'revslider-cymatic-addon'             => '7.2.3',
		'revslider-darkmode-addon'            => '7.2.3',
		'revslider-depthforge-addon'          => '7.2.4',
		'revslider-distortion-addon'          => '7.2.8',
		'revslider-domainswitch-addon'        => '7.2.1',
		'revslider-duotonefilters-addon'      => '7.2.5',
		'revslider-explodinglayers-addon'     => '7.2.6',
		'revslider-featured-addon'            => '7.2.2',
		'revslider-filmstrip-addon'           => '7.2.6',
		'revslider-floatingobjects-addon'     => '7.2.3',
		'revslider-fluiddynamics-addon'       => '7.2.3',
		'revslider-gallery-addon'             => '7.2.1',
		'revslider-glowcards-addon'           => '7.2.3',
		'revslider-hovermorph-addon'          => '7.2.9',
		'revslider-insights-addon'            => '7.2.5',
		'revslider-kinetictext-addon'         => '7.2.3',
		'revslider-layerdepth-addon'          => '7.2.5',
		'revslider-liquidglass-addon'         => '7.2.6',
		'revslider-login-addon'               => '7.2.1',
		'revslider-lottie-addon'              => '7.2.6',
		'revslider-maintenance-addon'         => '7.2.2',
		'revslider-mapandplaces-addon'        => '7.2.9',
		'revslider-mousetrap-addon'           => '7.2.5',
		'revslider-paintbrush-addon'          => '7.2.5',
		'revslider-panorama-addon'            => '7.2.5',
		'revslider-particles-addon'           => '7.2.5',
		'revslider-particlewave-addon'        => '7.2.5',
		'revslider-polyfold-addon'            => '7.2.5',
		'revslider-prevnextposts-addon'       => '7.2.1',
		'revslider-refresh-addon'             => '7.2.1',
		'revslider-relposts-addon'            => '7.2.1',
		'revslider-revealer-addon'            => '7.2.3',
		'revslider-scrollvideo-addon'         => '7.2.5',
		'revslider-shapeburst-addon'          => '7.2.5',
		'revslider-sharing-addon'             => '7.2.1',
		'revslider-slicey-addon'              => '7.2.5',
		'revslider-snow-addon'                => '7.2.3',
		'revslider-spatialscenes-addon'       => '7.2.3',
		'revslider-spectrumlines-addon'       => '7.2.3',
		'revslider-sunbeam-addon'             => '7.2.6',
		'revslider-thecluster-addon'          => '7.2.5',
		'revslider-transitionpack-addon'      => '7.2.9',
		'revslider-typewriter-addon'          => '7.2.5',
		'revslider-videofx-addon'             => '7.2.9',
		'revslider-weather-addon'             => '7.2.5',
		'revslider-whiteboard-addon'          => '7.2.4',
	];

	/**
	 * @var RevSliderLoadBalancer
	 */
	private $rslb;
	
	public function __construct(){
		$upload_dir = $this->paths()->upload_dir();
		$this->addons_basedir = $upload_dir['basedir'] . $this->addons_path;
		$this->addons_baseurl = $upload_dir['baseurl'] . $this->addons_path;

		$this->rslb = \ThemePunch\SR\Core\Registry::instance()->get('RevSliderLoadBalancer');

		add_action('revslider_update_all_options', array($this, 'on_update_all_options'), 10, 2);
	}


	/**
	 * clear the addon list
	 * drops the per-request memo so the next read reflects a fresh install/activation
	 * @return void
	 */
	public function clear_addon_list(){
		$this->addons_list = [];
		$this->addons_list_short = [];
	}

	/**
	 * clear the addon list on rs-addons option change
	 *
	 * @param array $options
	 * @param string $field
	 * @return void
	 */
	public function on_update_all_options($options, $field){
		if('rs-addons' === $field){
			$this->clear_addon_list();
		}
	}
	
	/**
	 * get all the addons with information
	 *
	 * @param bool $short return only the short list
	 * @return array
	 **/
	public function get_addon_list($short = false){
		
		if(!$short && !empty($this->addons_list)){
			return $this->addons_list;
		}
		if($short && !empty($this->addons_list_short)){
			return $this->addons_list_short;
		}

		$addons	= $this->get_options(['addons'], [], false, 'rs-addons');
		$addons	= (array)$addons;
		$addons = array_reverse($addons, true);

		$plugins = $this->lifecycle()->installed_extensions();
		//an active add-on the update server does not list yet (a new one before its release, a test build) still counts,
		//or the editor calls it "not available" and its resources never arrive through sr_get_addon_data
		foreach($plugins as $file => $plugin){
			if(!preg_match('#^(revslider-[a-z0-9]+-addon)/\1\.php$#', $file, $m) || isset($addons[$m[1]]) || !$this->lifecycle()->is_active($file)) continue;
			$addons[$m[1]] = $this->unlisted_addon($m[1], $plugin);
		}
		if(empty($addons)) return $addons;

		foreach($addons as $k => $addon){
			if(!is_object($addon)) continue;

			if(array_key_exists($addon->slug.'/'.$addon->slug.'.php', $plugins)){
				$addons[$k]->full_title	= $plugins[$addon->slug.'/'.$addon->slug.'.php']['Name'];
				$addons[$k]->active		= $this->lifecycle()->is_active($addon->slug.'/'.$addon->slug.'.php');
				$addons[$k]->installed	= $plugins[$addon->slug.'/'.$addon->slug.'.php']['Version'];
			}else{
				$addons[$k]->active	 = false;
				$addons[$k]->installed = false;
			}
		}

		if(!$short) {
			$this->addons_list = $addons;
			return $addons;
		}

		$_addons = [];
		foreach($addons as $k => $addon){
			if(!is_object($addon)) continue;

			$k = str_replace(['revslider-', '-addon'], '', $k);
			$addon		 = apply_filters('sr_get_addon_data', $addon, $addon->slug);
			$addon->slug = str_replace(['revslider-', '-addon'], '', $k);
			$_addons[$k] = $addon;
		}

		$this->addons_list_short = $_addons;

		return $_addons;
	}

	/**
	 * a catalogue entry built from the add-on's own plugin header, for an active add-on the update server does not list
	 * @since 7.2.1
	 * @return object
	 */
	private function unlisted_addon($slug, $plugin){
		$version = (string)$this->get_val($plugin, 'Version', '');
		$title	 = trim(preg_replace(['/^Slider Revolution\s+/i', '/\s+Add-?on$/i'], '', (string)$this->get_val($plugin, 'Name', $slug)));
		return (object)[
			'id' => 'local-' . $slug, 'releaseid' => 'local', 'slug' => $slug, 'version_from' => $version, 'version_to' => '9.9.9',
			'title' => $title, 'line_1' => (string)$this->get_val($plugin, 'Description', ''), 'line_2' => '', 'available' => $version,
			'background' => '', 'background_file' => '', 'background_md5' => '', 'button' => '', 'sort' => '0', 'last_update' => '',
			'global' => false, 'premium' => 'premium', 'logo' => (object)['img' => '', 'img_file' => '', 'img_md5' => '', 'color' => '', 'text' => ''],
			'banner' => '', 'banner_file' => '', 'banner_md5' => '', 'tags' => [], 'card_url' => ''
		];
	}

	/**
	 * one catalogue entry; the handle may be given with or without the revslider-/-addon wrapper
	 * @return object|false
	 */
	public function get_addon_data($handle, $short = false){
		$list		= $this->get_addon_list($short);
		$_handle	= str_replace(['revslider-', '-addon'], '', $handle);
		$addon		= $this->get_val($list, $_handle, false);

		return $addon;
	}
	
	/**
	 * get a specific addon version
	 * @return string|false the installed version, false when the add-on is not installed
	 **/
	public function get_addon_version($handle){
		$list = $this->get_addon_list();
		return $this->get_val($list, [$handle, 'installed'], false);
	}

	/**
	 * check if any addon is below version x (for RS6.0 this is version 2.0)
	 * if yes give a message that tells to update
	 * compares every active add-on against $addon_version_required
	 * @return array handle => version the user should update to (empty when everything is current)
	 **/
	public function check_addon_version(){
		$rs_addons	= $this->get_addon_list();
		$update		= [];
		
		if(empty($rs_addons)) return $update;
	
		foreach($rs_addons ?? [] as $handle => $addon){
			$installed = $this->get_val($addon, 'installed');
			if(trim($installed) === '') continue;
			if($this->get_val($addon, 'active', false) === false) continue;
			
			$version = $this->get_val($this->addon_version_required, $handle, false);
			if($version !== false && version_compare($installed, $version, '<')){
				$handle = str_replace(['revslider-', '-addon'], '', $handle);
				$update[$handle] = (version_compare($version, $this->get_val($addon, 'available'), '>')) ? $version : $this->get_val($addon, 'available');
			}
		}
		
		return $update;
	}
	
	/**
	 * Install Add-On/Plugin
	 * downloads the zip when needed and activates the add-on afterwards
	 *
	 * @since 6.0
	 * @param bool $force re-download even when the plugin folder already exists
	 * @return true|string true on success, an error message otherwise
	 */

	public function install_addon($addon, $force = false){
		if(empty($addon) || 0 !== strpos($addon, 'revslider-')) return false;
		
		
		if($this->_truefalse($this->get_options(['system', 'valid'], 'false')) !== true) return __('Please activate Slider Revolution', 'revslider');
		
		//check if downloaded already
		$plugins	= $this->lifecycle()->installed_extensions();
		$addon_path = $addon.'/'.$addon.'.php';

		if(!array_key_exists($addon_path, $plugins) || $this->_truefalse($force) === true || !$this->lifecycle()->extension_file_exists($addon_path)){
			//download if nessecary
			$downloaded = $this->download_addon($addon);
			if($downloaded !== true) return $downloaded;
		}

		//activate
		return $this->activate_addon($addon_path);
	}
	
	
	/**
	 * Download Add-On/Plugin
	 *
	 * @since    1.0.0
	 */
	/**
	 * can the given file be opened as a ZIP archive and does it contain anything?
	 *
	 * Used as a gate before the installed add-on folder is deleted, so a truncated download or an error page
	 * that arrived with a 200 cannot destroy a working installation.
	 *
	 * @param string $file absolute path
	 * @return true|string true when usable, otherwise a human readable reason
	 */
	private function is_readable_zip($file){
		$size = (is_file($file)) ? filesize($file) : 0;
		if($size < 22) return __('the downloaded file is empty or incomplete', 'revslider'); //22 = smallest possible zip

		if(class_exists('ZipArchive')){
			$zip	= new \ZipArchive();
			$opened	= $zip->open($file, \ZipArchive::CHECKCONS);
			//CHECKCONS is stricter than the extractor behind this gate: WP's unzip_file() falls back to
			//PclZip on archives that fail the consistency check but still extract. Retry with a plain open
			//so the gate never rejects something unzip_file() would have installed - a truncated file fails
			//the plain open too (the central directory is gone either way).
			if($opened !== true) $opened = $zip->open($file);
			if($opened !== true) return __('the downloaded file is not a valid ZIP archive', 'revslider');

			$entries = $zip->numFiles;
			$zip->close();

			return ($entries > 0) ? true : __('the downloaded archive is empty', 'revslider');
		}

		//no ZipArchive: WP's unzip_file() will use PclZip, and PclZip locates the End of Central Directory
		//record at the file's tail - it never gates on the leading local header. So check for exactly that:
		//a download truncated mid-body keeps its leading "PK\x03\x04" but loses the EOCD, which is the case
		//this gate exists to catch. The EOCD ("PK\x05\x06") sits in the last 22 bytes plus at most 64KB of
		//archive comment.
		$handle = @fopen($file, 'rb');
		if($handle === false) return __('the downloaded file could not be read', 'revslider');

		$signature = fread($handle, 4);

		$tail_len = (int)min($size, 65557); //22 byte EOCD + 65535 byte max comment
		fseek($handle, -$tail_len, SEEK_END);
		$tail = fread($handle, $tail_len);
		fclose($handle);

		if($signature !== "PK\x03\x04") return __('the downloaded file is not a valid ZIP archive', 'revslider');
		if(!is_string($tail) || strpos($tail, "PK\x05\x06") === false) return __('the downloaded file is incomplete (no end of archive record)', 'revslider');

		return true;
	}

	/**
	 * fetch an add-on zip from the ThemePunch servers and unpack it into the plugins folder
	 * @return true|string true on success, an error message otherwise
	 */
	public function download_addon($addon){
		$plugin_slug	= basename($addon);
		//without a licence only an add-on of the SR6 move, while the server's window is open (ADR-0027)
		if($this->_truefalse($this->get_options(['system', 'valid'], 'false')) !== true && !\ThemePunch\SR\Core\Registry::instance()->get('RevSliderPluginUpdateV6')->moved_delivery_open($plugin_slug)) return __('Please activate Slider Revolution', 'revslider');
		
		if(0 !== strpos($plugin_slug, 'revslider-')) die( '-1' );

		$code	= $this->get_options(['system', 'license'], '');
		$rattr	= [
			'code'		=> urlencode($code),
			'version'	=> urlencode(RS_REVISION),
			'product'	=> urlencode(RS_PLUGIN_SLUG),
			'type'		=> urlencode($plugin_slug)
		];
		$res = $this->rslb->call_url_result('addons/'.$plugin_slug.'/download.php', $rattr);
		if(!$res->ok) return sprintf(__('Add-On could not be downloaded: %s', 'revslider'), $res->error);

		if($res->body != 'invalid' && $res->code == 200){
			$upload_dir	= $this->paths()->upload_dir();
			$file		= $upload_dir['basedir']. '/revslider/templates/' . $plugin_slug . '.zip';
			@mkdir(dirname($file), 0777, true);
			$ret		= @file_put_contents($file, $res->body);
			if($ret === false) return sprintf(__('Add-On package could not be written to %s. Please check the folder permissions.', 'revslider'), dirname($file));

			$upload_dir	= $this->paths()->upload_dir();
			$d_path		= $this->lifecycle()->extension_dir();

			//verify the download is a usable archive BEFORE the installed add-on is removed. A truncated or
			//rejected download used to wipe a working add-on and leave nothing in its place.
			$readable = $this->is_readable_zip($file);
			if($readable !== true){
				wp_delete_file($file);
				return sprintf(__('Add-On package could not be extracted: %s', 'revslider'), $readable);
			}

			$this->filesystem()->delete($d_path . '/' . $plugin_slug, true); //remove the addon folder if exists

			$unzipfile = $this->filesystem()->extract_zip($file, $d_path); //retries with FS_METHOD 'direct' on its own

			wp_delete_file($file);

			$this->flush_wp_cache();
			$this->clear_addon_list();

			if(is_wp_error($unzipfile)) return sprintf(__('Add-On package could not be extracted: %s', 'revslider'), $unzipfile->get_error_message());

			return true;
		}

		if($res->body == 'invalid') return __('The Slider Revolution server rejected the Add-On download. Please check your license.', 'revslider');

		return sprintf(__('Add-On could not be downloaded, the server responded with status %s.', 'revslider'), $res->code);
	}
	
	/**
	 * Activates Installed Add-On/Plugin
	 * @param string    $candidate    plugin path, e.g. revslider-lottie-addon/revslider-lottie-addon.php
	 * @param bool|null $network_wide null decides from the current context
	 * @return bool true when the add-on is active afterwards
	 */
	public function activate_addon($candidate, $network_wide = null){
		if(empty($candidate) || 0 !== strpos($candidate, 'revslider-')) return false;
		
		// Determine network_wide default
		if($network_wide === null) $network_wide = $this->environment()->is_multisite() && $this->lifecycle()->is_network_admin_context();
		
		// If already inactive, report cleanly
		$isActive = true;
		if(!$this->lifecycle()->is_active($candidate) || ($this->environment()->is_multisite() && !$this->lifecycle()->is_network_active($candidate))){
			// Activate
			$result = $this->lifecycle()->activate($candidate, $network_wide);
			if (is_wp_error($result)) return false;
			
			// Check result
			$isActive = $this->lifecycle()->is_active($candidate) || ($this->environment()->is_multisite() && $this->lifecycle()->is_network_active($candidate));
		}
		
		$this->clear_addon_list();

		return $isActive;
	}

	/**
	 * Deactivate an addon by handle or plugin path.
	 *
	 * @param string      $addon         Handle like 'revslider-404-addon' OR full plugin path 'revslider-404-addon/index.php'
	 * @param null|bool   $network_wide  Pass true/false to force network-wide deactivation on multisite. NULL = auto.
	 * @return bool
	 */
	public function deactivate_addon($addon, $network_wide = null){
		if(empty($addon) || 0 !== strpos($addon, 'revslider-')) return false;
		
		$candidate = $this->find_addon_path($addon);
		if($candidate === null) return false;

		// Determine network_wide default
		if($network_wide === null) $network_wide = $this->environment()->is_multisite() && $this->lifecycle()->is_network_admin_context();

		// If already inactive, report cleanly
		if(!$this->lifecycle()->is_active($candidate) && !($this->environment()->is_multisite() && $this->lifecycle()->is_network_active($candidate))) return true;

		// Deactivate
		$this->lifecycle()->deactivate($candidate, $network_wide);

		// Check result
		$stillActive = $this->lifecycle()->is_active($candidate) || ($this->environment()->is_multisite() && $this->lifecycle()->is_network_active($candidate));

		$this->clear_addon_list();

		return ! $stillActive;
	}


	/**
	 * resolve an add-on slug to its actual plugin file, since the folder name is not always the slug
	 * @param string $status only used for the error message
	 * @return string|null plugin path relative to the plugins folder
	 */
	public function find_addon_path($addon, $status = 'activated'){

		// If it already looks like a plugin path and the file exists, use it directly
		$candidate = null;
		if(strpos($addon, '/') !== false && substr(strtolower($addon), -4) === '.php'){ //str_ends_with is PHP 8.0, the plugin runs from 7.4
			if($this->lifecycle()->extension_file_exists($addon)) $candidate = $addon; // already a valid plugin basename
		}

		// Otherwise, treat input as "handle" (e.g., 'revslider-404-addon') and try to resolve
		if(!$candidate){
			$handle = strtolower(trim($addon));

			// Helper to slugify strings similar to WP-style slugs
			$slugify = function ($s) {
				$s = strtolower($s);
				$s = preg_replace('~[^\pL\d]+~u', '-', $s);
				$s = trim($s, '-');
				$s = preg_replace('~[^-\w]+~', '', $s);
				return $s;
			};

			$plugins   = $this->lifecycle()->installed_extensions();
			$found = 0;

			foreach($plugins ?? [] as $base => $data){
				// $base e.g. "revslider-404-addon/index.php"
				$dir  = strtolower(dirname($base));                 // "revslider-xxx-addon"
				$file = strtolower(basename($base, '.php'));        // "index" (or "revslider-xxx-addon")

				// Scoring heuristics (prefer exact dir match, then file, then TextDomain, then Name slug, then "contains")
				if($dir === $handle)                      $found = max($found, 100);
				if($file === $handle)                     $found = max($found, 90);

				if($found > 0 && $this->lifecycle()->is_active($base)){
					$plugin_status = $this->lifecycle()->is_active($base);

					if($plugin_status !== true && $status === 'activated') continue;
					if($plugin_status === true && $status === 'deactivated') continue;
					
					$candidate = $base;
					break;
				}
			}
		}

		return $candidate;

	}

	/**
	 * public URL of an add-on's logo/banner image, downloading it on first access
	 * @return string
	 */
	public function _get_media_url($handle, $download = true){
		$url = $this->_check_file_path($handle, true, $download);
		//a Sync can bring a new file under the same name: the md5 in the url makes the browser drop its cached copy
		$md5 = $url === $handle ? '' : $this->media_md5($handle);
		return $md5 === '' ? $url : $url . '?ver=' . substr($md5, 0, 8);
	}

	/**
	 * check if image was uploaded, if yes, return path or url
	 * re-downloads the file when its checksum no longer matches the catalogue
	 * @param bool $url      return a public URL instead of a filesystem path
	 * @param bool $download allow fetching the image when it is missing
	 * @return string the resolved path/url, or $image unchanged when it could not be fetched
	 */
	public function _check_file_path($image, $url = false, $download = true){
		$local_image = ltrim(wp_normalize_path($image), '/');
		$prefix      = trim($this->addons_path, '/') . '/';
		if(strpos($local_image, $prefix) === 0){
			$local_image = substr($local_image, strlen($prefix));
		}

		if(!wp_mkdir_p($this->addons_basedir)) return $image;
		
		$base_url = ($url) ? $this->addons_baseurl : $this->addons_basedir;
		$file     = $this->addons_basedir . $local_image;
		if(file_exists($file)){
			if(!$this->check_checksum($image, $file)) {
				$this->rslb->download_url( $image, $file, 'updates', false, $this->addons_basedir);
			}
			return $base_url . $local_image;
		}
		
		if($download !== true) return $image;
		
		$this->rslb->download_url($image, $file, 'updates', false, $this->addons_basedir);

		return (file_exists($file)) ? $base_url . $local_image : $image;
	}

	/**
	 * does the local file still match the md5 the catalogue lists for it?
	 * @return bool false also when the image is not in the catalogue at all
	 */
	public function check_checksum($image, $file){
		$md5 = $this->media_md5($image);
		return $md5 !== '' && md5_file($file) === $md5;
	}

	/**
	 * the md5 the catalogue lists for one of its files: an add-on's logo or banner, or an Effects Library still
	 * @since 7.2.0
	 * @return string '' when the catalogue does not know the file
	 */
	public function media_md5($image){
		$image = (string)$image;
		$still = $this->still_md5s();
		if(isset($still[$image])) return $still[$image];
		foreach($this->get_addon_list(true) ?? [] as $addon){
			if($this->get_val($addon, ['logo', 'img_file']) === $image) return (string)$this->get_val($addon, ['logo', 'img_md5'], '');
			if($this->get_val($addon, 'banner_file') === $image) return (string)$this->get_val($addon, 'banner_md5', '');
		}
		return '';
	}

	/**
	 * handle => md5 of every still the Tools server lists, read once per request
	 * @since 7.2.0
	 * @return array
	 */
	private function still_md5s(){
		static $map = null;
		if($map !== null) return $map;
		$map = [];
		foreach((array)$this->option_storage()->get(self::OPTIONS_EFFECTS, []) as $list){
			foreach((array)$list as $e){
				$e = (array)$e;
				$handle = $this->media_handle((string)($e['still_url'] ?? ''));
				if($handle !== null && !empty($e['md5'])) $map[$handle] = (string)$e['md5'];
			}
		}
		return $map;
	}

	/**
	 * the handle _get_media_url() takes for a file the Tools server serves under revslider/addons/
	 * @since 7.2.0
	 * @return string|null null for a url on this site or outside that folder
	 */
	private function media_handle($url){
		$host = $url === '' ? '' : (string)parse_url($url, PHP_URL_HOST);
		if($host === '' || $host === parse_url($this->environment()->site_url(), PHP_URL_HOST)) return null;
		$handle = ltrim((string)parse_url($url, PHP_URL_PATH), '/');
		return strpos($handle, trim($this->addons_path, '/') . '/') === 0 ? $handle : null;
	}

	/**
	 * a Tools server file as this site's own copy: the url the browser gets and the handle that fetches it
	 * @since 7.2.0
	 * @return array|null ['url', 'file'], null when the url is not the Tools server's
	 */
	private function local_media($url){
		$handle = $this->media_handle($url);
		if($handle === null) return null;
		$md5 = $this->media_md5($handle);
		return ['url' => $this->addons_baseurl . substr($handle, strlen(trim($this->addons_path, '/') . '/')) . ($md5 === '' ? '' : '?ver=' . substr($md5, 0, 8)), 'file' => $handle];
	}

	/**
	 * one entry per Effects Library tile: installed add-ons from their admin/assets/effects.json
	 * (written by tools/effects-catalogue), the others from the effects[] the Tools server lists per add-on
	 * @since 7.2.0
	 * @return array slug => ['version' => string, 'installed' => bool, 'effects' => array]
	 */
	public function get_effects_catalogue(){
		$out = [];
		//the server's lists live in their own option (see RevSliderUpdate), keyed by the full plugin slug
		$server = (array)$this->option_storage()->get(self::OPTIONS_EFFECTS, []);
		$known	= [];
		foreach($this->get_addon_list(true) ?? [] as $slug => $addon){
			$known[$slug] = true;
			$list = [];
			foreach((array)$this->get_val($server, 'revslider-' . $slug . '-addon', $this->get_val($addon, 'effects', [])) as $e){
				$e = (array)$e;
				if(empty($e['key']) || empty($e['still_url'])) continue;
				$list[] = $e;
			}
			//the server sends the collection card beside the effects, addressed on its own side; until it does, the card
			//the core's bundle names is taken when it is one of the tiles' own stills, which the server serves already
			$card = (string)$this->get_val($this->get_val($server, 'revslider-' . $slug . '-addon-card', []), 'card_url', '');
			if($card === '' && !empty($list)){
				$named = (string)$this->get_val($this->bundled_catalogue(), [$slug, 'card'], '');
				foreach($named === '' ? [] : $list as $e) if((string)$this->get_val($e, 'still', '') === $named){ $card = (string)$e['still_url']; break; }
			}
			if(!empty($list)) $out[$slug] = ['version' => '', 'installed' => false, 'card_url' => $card, 'effects' => $list];
		}
		//an add-on the server lists without its effects (the Tools server sends none so far) takes the copy the core
		//carries: the same tiles without stills, the editor shows the add-on's own picture for them
		foreach($this->bundled_catalogue() as $slug => $entry){
			if(!isset($out[$slug]) && isset($known[$slug])) $out[$slug] = $entry;
		}
		//the installed add-on's own list wins over the server's, it matches the version that is actually there
		foreach(glob($this->lifecycle()->extension_dir() . '/revslider-*-addon/admin/assets/effects.json') ?: [] as $file){
			$data = json_decode((string)file_get_contents($file), true);
			$this->add_sidecar($out, (string)$this->get_val($data, 'slug', ''), $data);
		}
		//🔴 An add-on that is not installed has the Tools server's stills and card. The browser gets this site's copy,
		//still_file / card_file is what the editor hands _get_media_url() to fetch a missing one before a tile shows
		foreach($out as &$entry){
			if(!empty($entry['installed']) || !is_array($entry['effects'] ?? null)) continue;
			foreach($entry['effects'] as &$e){
				$local = $this->local_media((string)$this->get_val($e, 'still_url', ''));
				if($local !== null){ $e['still_url'] = $local['url']; $e['still_file'] = $local['file']; }
			}
			unset($e);
			$local = $this->local_media((string)$this->get_val($entry, 'card_url', ''));
			if($local !== null){ $entry['card_url'] = $local['url']; $entry['card_file'] = $local['file']; }
		}
		unset($entry);
		//🔴 Core's own effects belong to no add-on, so neither loop above can reach them: the first walks the
		//add-on list, the second only fills in add-ons that list already knows. They ship with the plugin and
		//are always there, so they are added here, whatever the server sent
		$bundled = $this->bundled_catalogue();
		if(!empty($bundled['core'])) $out['core'] = $bundled['core'];
		//Overview posters are frames of the library films, including for add-ons that are not installed.
		foreach($out as $slug => &$entry){
			$poster = 'admin/assets/images/presets/addon-posters/' . $slug . '.webp';
			$file = RS_CORE_ASSETS_PATH . $poster;
			if(is_file($file)) $entry['card_url'] = RS_CORE_URL_CLEAN . $poster . '?ver=' . substr(md5_file($file), 0, 8);
		}
		unset($entry);
		return $out;
	}

	/**
	 * the effects lists the core carries for every add-on (tools/effects-catalogue/build.mjs --bundle), read once
	 * @return array slug => catalogue entry, tiles without stills
	 */
	private function bundled_catalogue(){
		static $bundle = null;
		if($bundle !== null) return $bundle;
		$bundle = [];
		$file = RS_PLUGIN_PATH . 'core/admin/includes/effects-catalogue.json';
		$data = is_file($file) ? json_decode((string)file_get_contents($file), true) : null;
		foreach(is_array($data) ? $data : [] as $slug => $d){
			$list = [];
			foreach((array)$this->get_val($d, 'effects', []) as $e){
				if(empty($e['key'])) continue;
				$e['still_url'] = '';
				$list[] = $e;
			}
			//🔴 Core's own effects need no add-on and therefore no install: their stills live in the plugin, so
			//the url can be built right here and the tile has a picture on a site that has never seen the
			//Tools server
			if((string)$slug === 'core'){
				$base = RS_CORE_URL_CLEAN . 'admin/assets/images/presets/';
				foreach($list as &$ce){
					if(!empty($ce['still'])) $ce['still_url'] = $base . rawurlencode($ce['still']) . '?ver=' . RS_REVISION;
					//a core effect may name a film beside its still: it plays on hover, the way an add-on's library video does
					if(!empty($ce['video'])) $ce['video_url'] = $base . rawurlencode($ce['video']) . '?ver=' . RS_REVISION;
				}
				unset($ce);
			}
			if(!empty($list)) $bundle[(string)$slug] = ['version' => '', 'installed' => ((string)$slug === 'core'), 'card_url' => '', 'card' => (string)$this->get_val($d, 'card', ''), 'effects' => $list];
		}
		return $bundle;
	}

	/**
	 * one add-on's effects.json into the catalogue, stills addressed inside the installed add-on;
	 * an entry without a still is kept, the editor shows the add-on's own picture for it
	 */
	private function add_sidecar(&$out, $slug, $data){
		if($slug === '' || empty($data['effects']) || !is_array($data['effects'])) return;
		$base = $this->paths()->plugins_url() . '/revslider-' . $slug . '-addon/admin/assets/images/presets/';
		$version = (string)$this->get_val($data, 'version', '');
		$list = [];
		$stamp = [];
		foreach($data['effects'] as $e){
			if(empty($e['key'])) continue;
			//a still keeps its name from one version to the next, so the url carries the picture's own md5 or the browser shows the old one
			$v = empty($e['md5']) ? $version : substr((string)$e['md5'], 0, 8);
			if(!empty($e['still'])) $stamp[$e['still']] = $v;
			$e['still_url'] = empty($e['still']) ? '' : $base . rawurlencode($e['still']) . ($v === '' ? '' : '?ver=' . $v);
			$list[] = $e;
		}
		$card = (string)$this->get_val($data, 'card', '');
		//a card that is one of the stills carries that still's stamp: the editor pairs the two by their url
		$cv = $card === '' ? '' : ($stamp[$card] ?? $version);
		if(!empty($list)) $out[$slug] = ['version' => $version, 'installed' => true,
										 'card_url' => $card === '' ? '' : $base . rawurlencode($card) . ($cv === '' ? '' : '?ver=' . $cv), 'effects' => $list];
	}

	/**
	 * get the addons that need to be migrated from old to new addon slugs during upgrade
	 * 
	 * @return array
	 */
	public function get_addons_to_migrate(){
		return [
			'revslider-domain-switch-addon' => 'revslider-domainswitch-addon',
			'revslider-prevnext-posts-addon' => 'revslider-prevnextposts-addon',
			'revslider-rel-posts-addon' => 'revslider-relposts-addon',
			'revslider-liquideffect-addon' => 'revslider-distortion-addon'
		];
	}
	
	/**
	 * get the addons that need to be removed during upgrade
	 * 
	 * @return array
	 */
	public function get_addons_to_remove(){
		return [
			'revslider-backup-addon',
		];
	}

	/**
	 * flush all cache
	 */
	public function flush_wp_cache(){
		$this->lifecycle()->flush_extension_cache();
		parent::flush_wp_cache();
	}
}
