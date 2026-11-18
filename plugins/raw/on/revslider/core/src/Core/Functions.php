<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */
 
namespace ThemePunch\SR\Core;

if(!defined('ABSPATH')) exit();

/**
 * Shared helper base class - almost every class in the plugin extends it. Option access (with the fonts
 * branch split into its own non-autoloaded option), the get_val()/set_val() array accessors used instead of
 * isset() chains throughout the codebase, request variable readers, and image/post type/string utilities.
 */
class Functions extends Data {

	/**
	 * separate options field for the fonts branch. it holds the downloaded google font css + the collected font
	 * list (~650KB) and is only needed while rendering or managing fonts - keeping it inside the autoloaded
	 * 'sr-options' meant WordPress loaded and unserialized all of it on *every* request.
	 */
	const OPTIONS_FONTS = 'sr-options-fonts';
	const OPTIONS_EFFECTS = 'rs-addons-effects';	//the effects[] lists off the add-on catalogue, ~150 KB nobody needs on a front-end request: own row, never autoloaded

	/** @var string|null the capability that gates the plugin, see resolve_user_role(); a property, not a method static, so every subclass shares it */
	private static $user_capability = null;

	public function __construct(){
		parent::__construct();
	}

	/**
	 * is the given file extension on the import blocklist ($SR_GLOBALS['bad_extensions'])?
	 *
	 * called once per file inside an imported zip, where in_array() over the ~70 entry list is a linear scan.
	 * The flipped map is rebuilt only when the list itself changed, so a runtime addition still takes effect.
	 *
	 * @param string $extension without the dot, case insensitive
	 * @return bool
	 */
	public function is_bad_extension($extension){
		global $SR_GLOBALS;

		static $map	  = null;
		static $count = -1;

		$list = (isset($SR_GLOBALS['bad_extensions']) && is_array($SR_GLOBALS['bad_extensions'])) ? $SR_GLOBALS['bad_extensions'] : [];
		if($map === null || $count !== count($list)){
			$map   = array_flip(array_map('strtolower', $list));
			$count = count($list);
		}

		return isset($map[strtolower((string)$extension)]);
	}

	/**
	 * raise the PHP runtime limits for a long running job (import, version upgrade, image processing).
	 *
	 * ini_set()/set_time_limit() are regularly listed in disable_functions on hardened hosting, where the bare
	 * call emits a PHP warning that lands in the middle of an AJAX/JSON body and breaks JSON.parse(). So this
	 * checks the function is callable, never lowers a limit the host set higher, and leaves an unlimited limit
	 * (0 for max_execution_time, -1 for memory_limit) alone.
	 *
	 * @param int    $seconds seconds of runtime that should be available from now on, 0 to skip
	 * @param string $memory  memory_limit to request, e.g. '1G', '' to skip
	 * @return bool
	 */
	public function raise_runtime_limits($seconds = 0, $memory = ''){
		if(!function_exists('ini_get')) return false;

		if($seconds > 0){
			$cur = (int)ini_get('max_execution_time');
			if($cur > 0){ //0 means "no limit" (usually CLI) - there is nothing to raise
				//max() so the timer is reset without ever shortening a longer host limit
				$want = max((int)$seconds, $cur);
				if(function_exists('set_time_limit')){
					@set_time_limit($want);
				}elseif(function_exists('ini_set')){
					@ini_set('max_execution_time', (string)$want);
				}
			}
		}

		if($memory !== '' && function_exists('ini_set')){
			$cur  = wp_convert_hr_to_bytes((string)ini_get('memory_limit'));
			$want = wp_convert_hr_to_bytes($memory);
			if($cur > 0 && $want > $cur) @ini_set('memory_limit', $memory); //<= 0 means unlimited
		}

		return true;
	}

	/**
	 * is current user can NOT perform action
	 *
	 * @param string $role
	 * @return bool
	 */
	public function current_user_can_not($role = ''){
		//the plugin's permissions only exist in the admin area (on WordPress: where RevSliderAdmin is loaded)
		if(!$this->environment()->is_admin_area()) return true;

		$role = $role ?: $this->get_user_role();
		return !$this->environment()->user_can($role) && apply_filters('revslider_restrict_role', true);
	}

	/**
	 * the capability that gates the plugin; resolved once per request, as the admin screen always did
	 * @return string
	 */
	public function get_user_role(){
		return self::$user_capability ?? $this->resolve_user_role();
	}

	/**
	 * map the 'permission' global setting to that capability and keep it for the request. WordPress resolves it
	 * when RevSliderAdmin loads, so a filter added later or a setting saved mid-request takes effect next request.
	 * @return string
	 */
	public function resolve_user_role(){
		$role = $this->get_val($this->get_global_settings(), 'permission', 'administrator');
		if($role === 'admin') $role = 'administrator';
		if(!in_array($role, ['author', 'editor', 'administrator'])) $role = 'administrator';

		switch($role){
			case 'author':
				return self::$user_capability = 'edit_published_posts';
			case 'editor':
				return self::$user_capability = 'edit_pages';
			default:
				return self::$user_capability = 'manage_options';
		}
	}

	/**
	 * attempt to load cache for _get_global_settings
	 * @return mixed
	 */
	public function get_global_settings(){
		return $this->get_wp_cache('_get_global_settings');
	}

	/**
	 * Get Global Settings
	 **/
	protected function _get_global_settings(){
		$gs = $this->get_options(['system', 'settings'], '');

		if(!is_array($gs)){
			//settings are stored as a JSON string and get_global_settings() is called ~20+ times per editor
			//request, where get_wp_cache is is_admin-gated and would re-decode the full blob every call.
			//Memoize the decode against the last raw value so it auto-busts when settings are saved.
			static $last_raw = null, $last_decoded = null;
			if($gs !== $last_raw){
				$last_raw	  = $gs;
				$last_decoded = json_decode($gs, true);
			}
			$gs = $last_decoded;
		}

		//uploaded custom fonts stored an absolute url, which an http -> https switch or a moved site invalidates
		if(!empty($gs['fonts']['list'])){
			$rsf = Registry::instance()->get('RevSliderFonts');
			if($rsf !== null) $gs['fonts']['list'] = $rsf->heal_uploaded_font_urls($gs['fonts']['list']);
		}

		return apply_filters('rs_get_global_settings', $gs);
	}

	/**
	 * update general settings
	 * stored JSON encoded in the system.settings option
	 * @param bool $merge true merges recursively into the existing settings instead of replacing them
	 * @return bool
	 */
	public function set_global_settings($global, $merge = false){
		$this->delete_wp_cache('_get_global_settings');
		if($this->_truefalse($merge) === true){
			$_global = $this->get_global_settings();
			if(!is_array($_global)) $_global = [];
			if(!is_array($global)) $global = [];
			$global = array_replace_recursive($_global, $global);
		}
		
		$global = json_encode($global);
		
		$this->update_option(['system', 'settings'], $global);
		
		return true;
	}


	/**
	 * get all additions from the update checks
	 * extra payload the update server sends along, e.g. notices and feature flags
	 * @since: 6.2.0
	 * @param string $key optional single key instead of the whole array
	 * @return mixed
	 **/
	public function get_addition($key = ''){
		$additions = (array)$this->get_options(['system', 'additions'], []);
		$additions = (!is_array($additions)) ? json_decode($additions, true) : $additions;
		
		return (empty($key)) ? $additions : $this->get_val($additions, $key);
	}


	/**
	 * throw an error
	 * @return void
	 * @throws Exception always
	 **/
	public function throw_error($message, $code = null){
		if(!empty($code)) throw new \Exception($message, $code);

		throw new \Exception($message);
	}


	/**
	 * get value from array. if not - return alternative
	 * 
	 * @param mixed $arr  could be array | object | scalar 
	 * @param mixed $key  could be array | string
	 * @param mixed $default  value to return if key not found
	 * @return mixed  
	 */
	public function get_val($arr, $key, $default = ''){
		//scalar =  int, float, string и bool
		if(is_scalar($arr)) return $default;
		//convert obj to array 
		if(is_object($arr)) $arr = (array)$arr;
		//if key is string, check immediately 
		if(!is_array($key)) return (isset($arr[$key])) ? $arr[$key] : $default;
		//loop thru keys
		foreach($key as $v){
			if(is_object($arr)) $arr = (array)$arr;
			if(isset($arr[$v])) {
				$arr = $arr[$v];
			} else {
				return $default;
			}
		}
		return $arr;
	}

	
	/**
	 * set parameter
	 * counterpart to get_val(): writes into a nested array/object, creating missing levels on the way
	 * @param mixed        $base  modified by reference
	 * @param array|string $name  a single key, or a path of keys
	 * @since: 6.0
	 * @return void
	 */
	public function set_val(&$base, $name, $value){
		if(is_array($name)){
			foreach($name as $key){
				if(is_array($base)){
					if(!isset($base[$key])) $base[$key] = [];
					$base = &$base[$key];
				}elseif(is_object($base)){
					if(!isset($base->$key)) $base->$key = new \stdClass();
					$base = &$base->$key;
				}
			}
			$base = $value;
		}else{
			$base[$name] = $value;
		}
	}

	/**
	 * Brand Kit: a field linked to a brand colour or font takes what the kit holds now
	 * the field keeps a real value, the link sits beside it in 'brand': {"color.1": {i: id, a: opacity, v: value written last}}
	 * a link only applies while its field still holds v, anything that wrote there since has won
	 * module settings carry the font list the page loads: an entry saved from a linked layer holds brand (id) and plain
	 * when an unlinked layer uses the same family, so the old family is only dropped once nothing else needs it
	 * @param array $obj module settings, slide params or a layer
	 * @param bool $strip drop the links, the page does not need them
	 * @since 7.2.0
	 * @return array
	 */
	public function apply_brand_kit($obj, $strip = false){
		if(!is_array($obj)) return $obj;
		$links = (is_array($obj['brand'] ?? null)) ? $obj['brand'] : [];
		$fonts = (is_array($obj['fonts'] ?? null)) ? array_filter($obj['fonts'], function($f){ return !empty($f['brand']); }) : [];
		if(!empty($links) || !empty($fonts)){
			$kit = [];
			foreach(['colors' => 'c', 'fonts' => 'f'] as $list => $field){
				foreach($this->get_val($this->get_global_settings(), ['brand', $list], []) as $entry){
					$kit[$this->get_val($entry, 'i')] = [$field, $this->get_val($entry, $field)];
				}
			}
			foreach($links as $path => $link){
				$id = $this->get_val($link, 'i');
				if(empty($kit[$id][1])) continue; //a deleted brand entry leaves the field on its last value
				$keys = explode('.', $path);
				$cur = $this->get_val($obj, $keys, null);
				if(!is_string($cur) || strtolower($cur) !== strtolower($this->get_val($link, 'v'))) continue;

				$a	 = intval($this->get_val($link, 'a', 100));
				$val = $kit[$id][1];
				if($kit[$id][0] === 'c' && $a < 100){
					$rgba = Colorpicker::rgbValues((strpos($val, '(') === false) ? Colorpicker::processRgba(Colorpicker::sanitizeHex($val), 1) : $val, 4);
					$val  = Colorpicker::rgbaString($rgba[0], $rgba[1], $rgba[2], round(floatval($rgba[3]) * $a / 100, 2));
				}
				$this->set_val($obj, $keys, $val);
				$obj['brand'][$path]['v'] = $val;
			}
			foreach($fonts as $key => $font){
				$family = $kit[$font['brand']][1] ?? '';
				$nkey	= str_replace(' ', '+', explode(',', $family)[0]); //the key _tpt.fontFamily gives it
				if($family === '' || $nkey === $key) continue;

				$entry = $this->get_val($obj['fonts'], $nkey, []);
				foreach(['normal', 'italic', 'subset'] as $part){
					if(is_array($font[$part] ?? null)) $entry[$part] = ($entry[$part] ?? []) + $font[$part];
				}
				$entry['name']	= $family;
				$entry['brand']	= $font['brand'];
				unset($entry['custom'], $entry['url']);
				foreach($this->get_custom_font_list() as $cfont){
					if($this->get_val($cfont, 'family') !== str_replace(['"', "'"], '', $family)) continue;
					$entry['custom']	= true;
					$entry['url']		= $this->get_val($cfont, 'url');
					break;
				}
				if(empty($font['plain'])) unset($obj['fonts'][$key]);
				$obj['fonts'][$nkey] = $entry;
			}
		}
		if($strip) unset($obj['brand']);

		return $obj;
	}

	/**
	 * get POST variable
	 * @param bool $esc run the value through esc_html()
	 * @return mixed
	 */
	public function get_post_var($key, $default = '', $esc = true){
		$val = $this->get_var($_POST, $key, $default);
		return ($esc) ? esc_html($val) : $val;
	}
	
	/**
	 * get GET variable
	 * @param bool $esc run the value through esc_html()
	 * @return mixed
	 */
	public function get_get_var($key, $default = '', $esc = true){
		$val = $this->get_var($_GET, $key, $default);
		return ($esc) ? esc_html($val) : $val;
	}
	
	/**
	 * get POST or GET variable in this order
	 * @param bool $esc run the value through esc_html()
	 * @return mixed
	 */
	public function get_request_var($key, $default = '', $esc = true){
		$val = (array_key_exists($key, $_POST)) ? $this->get_var($_POST, $key, $default) : $this->get_var($_GET, $key, $default);
		return ($esc) ? esc_html($val) : $val;
	}
	
	/**
	 * get a variable from an array,
	 * @return mixed
	 */
	public function get_var($arr, $key, $default = ''){
		return (isset($arr[$key])) ? $arr[$key] : $default;
	}
	
	/**
	 * check for true and false in all possible ways
	 * settings arrive as 'true'/'on'/1/'1' from very different places, so comparisons go through here
	 * @since: 6.0
	 * @return mixed bool when recognised, otherwise the input unchanged
	 **/
	public function _truefalse($v){
		//fast path: already a bool (the most common case) - true => true, false => false
		if(is_bool($v)) return $v;
		//static so the lookup arrays are built once, not on every call
		static $false_vals = ['false', 'off', NULL, 0, -1, "0"];
		static $true_vals  = ['true', 'on', 1, "1"];
		if(in_array($v, $false_vals, true)) return false;
		if(in_array($v, $true_vals, true)) return true;

		return $v;
	}
	
	/**
	 * validate that some value is numeric
	 * @param string $fn field name for the error message
	 * @return void
	 * @throws Exception when the value is empty or not numeric
	 */
	public function validate_numeric($val, $fn = 'Field'){
		$this->validate_not_empty($val, $fn);
		
		if(!is_numeric($val)) $this->throw_error($fn.__(' needs to be numeric', 'revslider'));
	}
	
	/**
	 * validate that some variable not empty
	 * @param string $fn field name for the error message
	 * @return void
	 * @throws Exception when the value is empty (0 counts as filled)
	 */
	public function validate_not_empty($val, $fn = 'Field'){
		if(empty($val) && is_numeric($val) == false) $this->throw_error($fn.__(' needs to not be empty', 'revslider'));
	}
	
	/**
	 * encode array into json for client side
	 * returns the JSON already quoted and slash escaped, ready to be printed into a JS literal
	 * @return string the encoded value, '{}' when there is nothing to encode
	 */
	public function json_encode_client_side($arr, $options = 0){
		if(empty($arr)) return '{}';

		if(defined('JSON_INVALID_UTF8_IGNORE')) $options |= JSON_INVALID_UTF8_IGNORE;
		$json = json_encode($arr, $options);
		$json = (!empty($json)) ? addslashes($json) : $json;
		
		return (empty($json)) ? '{}' : "'".$json."'";
	}
	
	
	/**
	 * turn a string into an array, check also for slashes!
	 * tries the unslashed string first, since values reach us slashed from different layers
	 * @since: 6.0
	 * @return mixed the decoded value, or the input unchanged when it is not a string
	 */
	public function json_decode_slashes($data){
		if(gettype($data) !== 'string') return $data;

		$data_decoded = json_decode(stripslashes($data), true);
		return (empty($data_decoded)) ? json_decode($data, true) : $data_decoded;
	}

	/**
	 * maybe_unserialize() for *untrusted* payloads such as remote HTTP response bodies. Keeps WordPress'
	 * pass-through behaviour for plain data, but never lets the payload instantiate arbitrary classes: an
	 * unrestricted unserialize() on a remote body is a PHP object injection surface.
	 *
	 * @param mixed       $data     raw body
	 * @param array|false $allowed  classes the payload may instantiate, false = none at all
	 * @return mixed
	 */
	public function maybe_unserialize_safe($data, $allowed = false){
		if(!is_string($data) || !is_serialized($data)) return $data;

		return @unserialize(trim($data), ['allowed_classes' => $allowed]);
	}

	
	/**
	 * Convert std class to array, with all sons
	 * @return array|null
	 */
	public function class_to_array($arr){
		return json_decode(json_encode($arr), true);
	}
	
	/**
	 * Convert std class to array, single
	 * @return array
	 */
	public function class_to_array_single($arr){
		return (array)$arr;
	}
	
	/**
	 * Check Array for Value Recursive
	 */
	public function in_array_r($needle, $haystack, $strict = false){
		if(!is_array($haystack) || empty($haystack)) return false;

		foreach($haystack ?? [] as $item){
			if(($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict)))	return true;
		}
	
		return false;
	}
	
	/**
	 * compress an array/object/string to a string
	 * used for the big cached catalogues (templates, object library) before they go into an option
	 * @since 6.6.0
	 * @return string base64 encoded gzip, or the plain JSON when zlib is unavailable
	 **/
	public function do_compress($data, $level = 9){
		if(is_array($data) || is_object($data)) $data = json_encode($data);
		
		if(!function_exists('gzcompress') || !function_exists('gzuncompress')) return $data; //gzencode / gzdecode

		return base64_encode(gzcompress($data, $level));
	}

	/**
	 * decompress an string to an array/object/string
	 * also accepts uncompressed JSON, so old option values keep working
	 * @since 6.6.0
	 * @return mixed
	 **/
	public function do_uncompress($data){
		if($data === false || empty($data) || is_array($data) || is_object($data)) return $data;

		//memoize per request: the same large compressed blob (e.g. the rs-templates catalog) is uncompressed
		//many times per request; gzuncompress + json_decode costs far more than hashing the input key
		static $cache = [];
		$ckey = md5($data);
		if(isset($cache[$ckey])) return $cache[$ckey];

		$_data = json_decode($data, true);
		if(is_array($_data) || is_object($_data)){ $cache[$ckey] = $_data; return $_data; }
		if(!function_exists('gzcompress') || !function_exists('gzuncompress')) return $data; //gzencode / gzdecode

		$raw	= gzuncompress(base64_decode($data));
		$_data	= json_decode($raw, true);
		$result	= (!empty($_data)) ? $_data : $raw;

		$cache[$ckey] = $result;
		return $result;
	}

	/**
	 * read the (large, static) admin icon sprite once per request - it is file_get_contents()'d from several
	 * places. NOTE: this only de-duplicates the disk read; the ~264KB are still inlined into the page.
	 * @return string
	 */
	public static function get_sprite_svg(){
		static $sprite_svg = null;
		if($sprite_svg === null) $sprite_svg = (string)file_get_contents(RS_CORE_ASSETS_PATH . 'admin/assets/images/sprite.svg');
		return $sprite_svg;
	}

	/**
	 * get attachment image url
	 * @param string $size registered image size, 'full' for the original
	 * @return string|false
	 */
	public function get_url_attachment_image($id, $size = 'full'){
		return $this->media_library()->image_url($id, $size);
	}


	/**
	 * gets a temporary path where files can be stored
	 *
	 * wp_mkdir_p() instead of mkdir($dir, 0777, true): 0777 makes the directory world writable, and imported
	 * archives are unpacked into it - on shared hosting every other account could then write there.
	 * @return string absolute path with a trailing slash; the first writable candidate wins
	 **/
	public function get_temp_path($path = 'rstemp'){
		if(function_exists('sys_get_temp_dir')){
			$temp = sys_get_temp_dir();
			if(@is_dir($temp) && wp_is_writable($temp)){
				$dir = trailingslashit($temp).$path.'/';
				if(!is_dir($dir)) wp_mkdir_p($dir);
				if(is_dir($dir) && wp_is_writable($dir)) return $dir;
			}
		}

		$temp = ini_get('upload_tmp_dir');
		if(@is_dir($temp) && wp_is_writable($temp)){
			$dir = trailingslashit($temp).$path.'/';
			if(!is_dir($dir)) wp_mkdir_p($dir);
			if(is_dir($dir) && wp_is_writable($dir)) return $dir;
		}

		$temp_dir = get_temp_dir();
		if(wp_is_writable($temp_dir)){
			$dir = trailingslashit($temp_dir).$path.'/';
			if(!is_dir($dir)) wp_mkdir_p($dir);
			if(is_dir($dir) && wp_is_writable($dir)) return $dir;
		}

		$upload_dir = $this->paths()->upload_dir();
		$dir		= $upload_dir['basedir'].'/'.$path.'/';
		if(!is_dir($dir)) wp_mkdir_p($dir);

		return $dir;
	}
	
	
	/**
	 * retrieve the image id from the given image url
	 * a "-800x600" size suffix is stripped first, so a thumbnail URL resolves to its original attachment
	 * @return int|false
	 */
	public function get_image_id_by_url($url){
		return $this->media_library()->id_by_url($url);
	}
	
	/**
	 * retrieve the image id from the given image filename/basename
	 * fallback for when the URL no longer matches, e.g. after a domain change
	 * @since: 6.1.5
	 * @return int|false
	 */
	public function get_image_id_by_basename($basename){
		return $this->media_library()->id_by_basename($basename);
	}
	
	/**
	 * get image url from image path.
	 * relative paths are prefixed with the upload base url, absolute ones are left alone
	 * @return string empty when the path is not a usable image path
	 */
	public function get_image_url_from_path($path){
		if(empty($path) || substr($path, -1) === '/' || substr($path, -1) === '\\') return ''; //check if the path ends with /, if yes its not a correct image path
		
		//check if it has an extension, if not leave it as it is
		if(empty(strtolower(pathinfo($path, PATHINFO_EXTENSION)))) return $path;
		
		//protect from absolute url, a scheme-less //host/... one too: prefixed it became <content_url>/host/...
		if(strpos($path, '//') === 0) return $path;
		$lower		= strtolower($path);
		$base_url	= $this->get_base_url();
		$return		= (strpos($lower, 'http://') !== false || strpos($lower, 'https://') !== false || strpos($lower, 'www.') === 0) ? $path : $base_url.$path;
		
		return ($return !== $base_url) ? preg_replace('~(?<!:)//+~', '/', $return) : '';
	}
	
	/**
	 * Check if Path is a Valid Image File
	 * extension check only, the file is not opened
	 * @return string|false the url when the extension is an image one, false otherwise
	 **/
	public function check_valid_image($url){
		if(empty($url)) return false;

		$ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
		
		return (in_array($ext, ['gif', 'jpg', 'jpeg', 'png', 'webp'])) ? $url : false;
	}
	
	/**
	 * get the upload URL of images
	 * @return string wp-content on single sites, the uploads folder on multisite
	 */
	public static function get_base_url(){
		$f		= Registry::instance()->get('RevSliderFunctions'); //static context, no $this
		$paths	= $f->paths();
		return ($f->environment()->is_multisite() == false) ? $paths->content_url().'/' : $paths->upload_dir()['baseurl'].'/';
	}
	
	/**
	 * AddOns that ship a <slug>.presets.js beside their admin script. The editor loads AddOn assets by convention
	 * and has to be told which of those files exist, or every editor load is one 404 per AddOn. Read by the
	 * editor header and by the block editor script (Quick Edit), so it lives here once.
	 * @since 7.2.0
	 * @return array slugs
	 */
	public static function get_addon_presets(){
		$slugs = [];
		$root = Registry::instance()->get('RevSliderFunctions')->lifecycle()->extension_dir(); //static context, no $this
		foreach(glob($root . '/revslider-*-addon/admin/assets/js/*.presets.js') ?: [] as $file){
			$slugs[] = basename($file, '.presets.js');
		}
		return $slugs;
	}

	/**
	 * Ways to begin a module that an AddOn brings (revslider_block_starts): [slug, label, script, text, img]. Read by
	 * the builders' module card and by the dashboard's New dialog, so it lives here once. The script is only
	 * fetched when its entry is picked, and answers as SR7.B.starts[slug].open(done)
	 * @since 7.2.1
	 * @return array
	 */
	public static function get_block_starts(){
		return array_values((array)apply_filters('revslider_block_starts', []));
	}

	/**
	 * strip slashes recursive
	 * @since: 5.0
	 * @return mixed
	 */
	public static function stripslashes_deep($value){
		if(empty($value)) return $value;

		$value = is_array($value) ? array_map(['RevSliderFunctions', 'stripslashes_deep'], $value) : stripslashes($value);
		
		return $value;
	}
	
	/**
	 * strip addslashes() leftovers without eating CSS escapes
	 * @since: 7.2.0
	 * stripslashes() drops the backslash before ANY character, so a skin carrying its icon in the CSS
	 * (content:'\e03f') read back as the literal text "e03f"
	 * @return string
	 */
	public static function stripslashes_css($value){
		return (is_string($value) && $value !== '') ? preg_replace('/\\\\([\\\\\'"])/', '$1', $value) : $value;
	}
	
	/**
	 * esc attr recursive
	 * @since: 6.0
	 * @return mixed
	 */
	public static function esc_attr_deep($value){
		$value = is_array($value) ? array_map(['RevSliderFunctions', 'esc_attr_deep'], $value) : esc_attr($value);

		return $value;
	}

	/**
	 * esc js recursive; lived on RevSliderAdmin, a WordPress class, which inherits it from here now
	 * @since: 6.0
	 * @return mixed
	 */
	public static function esc_js_deep($value){
		return is_array($value) ? array_map(['RevSliderFunctions', 'esc_js_deep'], $value) : esc_js($value);
	}
	
	
	/**
	 * get post types with categories for client side.
	 * flat list for the editor's source picker: one entry per post type + taxonomy combination
	 * @return array
	 */
	public function get_post_types_with_categories_for_client(){
		$c = 0;
		$ret = [];
		$post_types = $this->get_post_types_with_taxonomies();
		foreach($post_types as $name => $tax){
			$cat = [];
			if(empty($tax)){
				$ret[$name] = $cat;
				continue;
			}
			
			foreach($tax as $tax_name => $tax_title){
				$cats = $this->get_categories_assoc($tax_name);
				if(empty($cats)) continue;

				$c++;
				$cat['option_disabled_'.$c] = '---- '. $tax_title .' ----';
				foreach($cats as $catID => $catTitle){
					$cat[$tax_name.'_'.$catID] = $catTitle;
				}
			}
			
			$ret[$name] = $cat;
		}

		return $ret;
	}
	
	
	/**
	 * get post types array with taxomonies
	 * @return array post type name => its taxonomies
	 */
	public function get_post_types_with_taxonomies(){
		$post_types = $this->get_post_type_assoc();
		
		foreach($post_types ?? [] as $post_type => $title){
			$post_types[$post_type]	= $this->get_post_type_taxonomies($post_type);
		}
		
		return $post_types;
	}
	
	
	/**
	 * 
	 * get array of post types with categories (the taxonomies is between).
	 * get only those taxomonies that have some categories in it.
	 * @return array post type => taxonomy => terms
	 */
	public function get_post_types_with_categories(){
		$post_types_categories	= [];
		$post_types				= $this->get_post_types_with_taxonomies();
		
		foreach($post_types as $name => $tax){
			$ptwc = [];
			foreach($tax ?? [] as $tax_name => $tax_title){
				$cats = $this->get_categories_assoc($tax_name);
				if(!empty($cats)){
					$ptwc[$tax_name] = [
						'title'	=> $tax_title,
						'cats'	=> $cats
					];
				}
			}
			$post_types_categories[$name] = $ptwc;
		}
		
		return $post_types_categories;
	}
	
	
	/**
	 * get all the post types including custom ones
	 * the put to top items will be always in top (they must be in the list)
	 * @param array $put_to_top post type names to sort to the front
	 * @return array post type name => label
	 */
	public function get_post_type_assoc($put_to_top = []){
		$build_in		= ['post' => 'post', 'page'=>'page'];
		$custom_types	= $this->create_content_source()->custom_content_types();
		
		//top items validation - add only items that in the customtypes list
		$top_updated	= [];
		foreach($put_to_top ?? [] as $top){
			if(in_array($top, $custom_types) == true){
				$top_updated[$top] = $top;
				unset($custom_types[$top]);
			}
		}
		
		$post_types = array_merge($top_updated, $build_in, $custom_types);
		
		//update label
		foreach($post_types ?? [] as $key => $type){
			$post_types[$key] = $this->get_post_type_title($type);
		}
		
		return $post_types;
	}
	
	
	/**
	 * return post type title from the post type
	 * @return string the singular label, or the raw name for unregistered types
	 */
	public static function get_post_type_title($post_type){
		return Registry::instance()->get('RevSliderFunctions')->create_content_source()->content_type_label($post_type); //static context, no $this
	}
	
	
	/**
	 * get post type taxomonies
	 * @return array taxonomy name => label
	 */
	public function get_post_type_taxonomies($post_type){
		return $this->create_content_source()->content_type_taxonomies($post_type);
	}
	
	
	/**
	 * get post categories list assoc - id / title
	 * child terms are indented with dashes so the flat select still shows the hierarchy
	 * @return array term id => title
	 */
	public function get_categories_assoc($taxonomy = 'category'){
		$categories	= [];
		if(strpos($taxonomy, ',') !== false){
			$taxes = explode(',', $taxonomy);
			foreach($taxes ?? [] as $tax){
				$cats		= $this->get_categories_assoc($tax);
				$categories	= array_merge($categories, $cats);
			}
		}else{
			$cats = $this->create_content_source()->terms($taxonomy);
			foreach($cats ?? [] as $cat){
				$num				= $cat->count;
				$id					= $cat->cat_ID;
				$name				= ($num == 1) ? 'item' : 'items';
				$title				= $cat->name . ' ('.$num.' '.$name.')';
				$categories[$id]	= $title;
			}
		}
		
		return $categories;
	}
	
	
	/**
	 * check if css string is rgb
	 * @return bool
	 **/
	public function is_rgb($rgba){
		return (strpos($rgba, 'rgb') !== false) ? true : false;
	}
	
	
	/** @return array [allowed, absolute path, entry name inside the zip] - keeps an export or an import inside $baseDir, with only $allowedExts */
	public function is_safe_export_path($relative, $baseDir, array $allowedExts){
		// Normalize & decode once
		$relative = wp_normalize_path($relative);
		$relative = rawurldecode($relative); // catch %2e%2e etc.

		// Remember if it started with a slash
		$hadLeadingSlash = ($relative !== '' && $relative[0] === '/');

		// Strip a single leading slash for internal checks
		if($hadLeadingSlash) {
			$relative = ltrim($relative, '/');
		}

		// Basic sanity: forbid empties, Windows drive letters, UNC, or schemes
		if($relative === ''
			|| preg_match('~^[A-Za-z]:[\\\\/]~', $relative)         // C:\ or C:/
			|| preg_match('~^\\\\\\\\~', $relative)                 // \\server\share
			|| preg_match('~^[A-Za-z][A-Za-z0-9+.-]*:~', $relative) // file:, php:, etc.
		){
			return [false, null, null];
		}

		// Collapse duplicate slashes and remove harmless "./" segments
		$relative = preg_replace('~/+~', '/', $relative);
		$relative = preg_replace('~(^|/)\\./~', '$1', $relative);

		// Forbid traversal & null bytes & control chars
		if(strpos($relative, "\0") !== false) return [false, null, null];
		if(preg_match('~(^|/)\.\.(?:/|$)~', $relative)) return [false, null, null];
		if(preg_match('~[[:cntrl:]]~', $relative)) return [false, null, null];

		//resolved like the file below: an uploads folder behind a symlink would refuse every file inside it
		$baseDir = realpath($baseDir);
		if($baseDir === false) return [false, null, null];
		$baseDir = rtrim(wp_normalize_path($baseDir), '/');

		// Resolve and ensure inside base
		$full = realpath($baseDir . '/' . $relative);
		if($full === false) return [false, null, null];

		$fullNorm = wp_normalize_path($full);
		if(strpos($fullNorm, $baseDir . '/') !== 0 && $fullNorm !== $baseDir){
			return [false, null, null];
		}

		// Extension allowlist
		$ext = strtolower(pathinfo($fullNorm, PATHINFO_EXTENSION));
		if(!in_array($ext, $allowedExts, true)) return [false, null, null];

		// Produce clean ZIP entry path
		$zipEntry = str_replace('\\', '/', $relative);
		$zipEntry = ltrim($zipEntry, './');

		// Always add back the leading slash for export consistency
		if($hadLeadingSlash) {
			$zipEntry = '/' . ltrim($zipEntry, '/');
		}

		return [true, $fullNorm, $zipEntry];
	}

	/**
	 * check if file is in zip
	 * during an import: look for the file inside the extracted package and, if found, move it into the
	 * media library - otherwise keep the original reference
	 * @since: 5.0
	 * $folder in v6 was images
	 * @param array $imported modified by reference: already imported files, keyed by source path
	 * @return string|false the new URL, false when the file is not part of the package
	 */
	public function check_file_in_zip($path, $file, $alias, &$imported, $add_path = false, $folder = 'media'){ 
		
		$file = (is_array($file)) ? $this->get_val($file, 'url') : $file;
		if(trim($file) === '' || strpos($file, 'http') !== false) return $file; //http -> external image, do not change

		//the package names the file, so both branches below take only a media file inside its media folder
		[$zimage, $source, $entry] = $this->is_safe_export_path($file, $path.$folder, $this->media_extensions($file));

		if($zimage !== false){
			if(!isset($imported[$folder.'/'.$file])){
				//check if we are object folder, if yes, do not import into media library but add it to the object folder
				if(strpos($entry, 'revslider/objects/') === 0){
					// we are from the object library, copy the image to the objects folder if false
					/* @var RevSliderObjectLibrary $obj */
					$obj = Registry::instance()->get('RevSliderObjectLibrary');
					$importImage = $obj->_import_object($source);
				}else{
					$importImage = $this->import_media($source, $alias.'/');
					if (!$importImage['success']) $importImage = false;
				}
				
				if($importImage !== false){
					$imported[$folder.'/'.$file] = $importImage['path'];
					
					$file = $importImage['path'];
				}
			}else{
				$file = $imported[$folder.'/'.$file];
			}
		}

		if($add_path){
			$updir = $this->paths()->upload_dir()['baseurl'];
			if(strpos($file, $updir) === false) $file = str_replace('uploads/uploads/', 'uploads/', $updir . '/' . $file);
		}
		
		return $file;
	}
	
	/**
	 * Import the Media as it is
	 *
	 * @param string $name
	 * @param string $bitmap
	 * @return array
	 **/
	public function import_media_raw($name, $bitmap){
		$data = substr($bitmap, strpos($bitmap, ',') + 1);
		$data = base64_decode(str_replace(' ', '+', $data));
		if($data === false) {
			return [
				'success' => false,
				'message' => __('Image has an invalid type', 'revslider'),
			];
		}

		if(!preg_match('/^data:image\/(\w+);base64,/', $bitmap, $type)) {
			return [
				'success' => false,
				'message' => __('Image has an invalid data', 'revslider'),
			];
		}

		$type = strtolower($type[1]); // jpg, png, gif
		if(!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
			return [
				'success' => false,
				'message' => __('Image has an invalid type', 'revslider'),
			];
		}

		if(strpos($name, '.') !== false) $name = explode('.', $name)[0];

		$path = $this->get_temp_path('rstemp');
		$name = preg_replace("/[^a-zA-Z0-9\-\.\_]/", '', $name.'.'.$type);
		
		if(file_put_contents($path.$name, $data) === false) {
			return [
				'success' => false,
				'message' => __('Image could not be saved', 'revslider'),
			];
		}

		return $this->import_media($path.$name , 'video-media/');
	}

	/**
	 * normalize slashes in file path
	 * do not use with urls
	 *
	 * @param string $path
	 * @return string
	 */
	public function normalize_slashes($path){
		return preg_replace('#/+#', '/', $path);
	}

	/**
	 * @param string $value
	 * @return boolean
	 */
	function path_or_url_exists($value){
		// Remote URL
		if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
			return $this->http_client()->head( $value )->code === 200;
		}

		// Local filesystem path
		return file_exists( $value ) && is_readable( $value );
	}
	
	/**
	 * copy a source file to its destination - the source may be a local path or a remote URL.
	 *
	 * a plain copy() on an http(s) URL goes through the PHP stream wrapper: it needs allow_url_fopen, has no
	 * timeout, does not verify the TLS certificate, follows redirects without a limit and reports nothing but
	 * false. download_url() routes it through the WP HTTP API instead. Local paths keep using copy().
	 *
	 * @param string $source
	 * @param string $destination absolute path
	 * @return bool|WP_Error true/false from copy(), WP_Error when the download failed
	 */
	public function copy_source_file($source, $destination){
		if(!preg_match('#^https?://#i', (string)$source)) return @copy($source, $destination);

		$result = $this->http_client()->download($source);
		if($result->ok !== true) return new \WP_Error('download_failed', (string)$result->error);

		$copied = @copy($result->file, $destination);
		wp_delete_file($result->file);

		return $copied;
	}

	/**
	 * the media types a file may be imported or uploaded as: images and videos, plus what an AddOn adds for its own
	 * files through revslider_import_media_allowed_mimes
	 * @since 7.2.1
	 * @param string $file_name
	 * @return array mime types
	 */
	public function media_mimes($file_name){
		global $SR_GLOBALS;

		$mimes = array_merge($this->get_val($SR_GLOBALS, ['mime_types', 'image'], []), $this->get_val($SR_GLOBALS, ['mime_types', 'video'], []));
		return apply_filters('revslider_import_media_allowed_mimes', $mimes, $file_name);
	}

	/**
	 * the file extensions media_mimes() allows, one entry each ('jpg|jpeg|jpe' gives three)
	 * @since 7.2.5
	 * @param string $file_name
	 * @return array
	 */
	public function media_extensions($file_name){
		return explode('|', implode('|', array_keys($this->media_mimes($file_name))));
	}

	/**
	 * the message for a failed PHP upload the editor names itself, null for any other error code
	 * @since 7.2.1
	 * @return string|null
	 */
	protected function upload_error_message($code){
		switch($code){
			case UPLOAD_ERR_NO_FILE:
				return __('No file sent', 'revslider');
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				return __('Exceeded filesize limit', 'revslider');
		}

		return null;
	}

	/**
	 * strip script, event handlers, foreignObject and XXE from an SVG file in place
	 * @since 7.2.1
	 * @return bool false when nothing safe was left or the file could not be written back
	 */
	public function sanitize_svg_file($path){
		$clean = (new SvgSanitizer())->sanitize($this->filesystem()->read($path));
		return !empty($clean) && $this->filesystem()->write($path, $clean);
	}

	/**
	 * Import media from url
	 *
	 * @param string $file_url    URL of the existing file from the original site
	 * @param int    $folder_name The slidername will be used as a folder name in import
	 * @return array
	 */
	public function import_media($file_url, $folder_name, $filename = '', $post_content = '', $post_excerpt = ''){
		global $SR_GLOBALS;

		if (!$this->path_or_url_exists($file_url)){
			return [
				'success' => false,
				'message' => sprintf( __( 'Source file not exists: %s', 'revslider' ), $file_url),
			];
		}

		$path_info = pathinfo($file_url);
		if(empty($path_info['extension']) || $this->is_bad_extension($path_info['extension'])){
			return [
				'success' => false,
				'message' => sprintf( __( 'Extension not allowed: %s', 'revslider' ), $path_info['extension']),
			];
		}

		// folder inside uploads/revslider/
		$folder_name = trailingslashit( ltrim( $folder_name, '/' ) );

		$ul_dir = $this->paths()->upload_dir();
		$paths = [
			'sr' => 'revslider/',
			'basedir' => trailingslashit($ul_dir['basedir']),
			'baseurl' => trailingslashit($ul_dir['baseurl']),
			'filename' => (empty($filename)) ? basename($file_url) : $filename,
		];
		// relative to the upload dir
		$paths['relative'] = $this->normalize_slashes($paths['sr'] . $folder_name . $paths['filename']);
		// absolute path
		$paths['absolute'] = $paths['basedir'] . $paths['relative'];

		if (!wp_mkdir_p(dirname($paths['absolute']))) {
			return [
				'success' => false,
				'message' => sprintf( __( 'Could not create destination directory: %s', 'revslider' ), $paths['absolute']),
			];
		}

		$atc_id	= $this->get_image_id_by_url($paths['relative']);
		if (empty($atc_id)) {
			$atc_id = $this->get_image_id_by_basename($paths['filename']);
			if (!empty($atc_id)) {
				// the image was found through get_image_id_by_basename()
				// new save = found file location, if the files are the same
				$attached_file_path = $this->media_library()->file_path($atc_id);
				if(!empty($attached_file_path) && @md5_file($attached_file_path) === @md5_file($file_url)){
					$paths['absolute'] = $attached_file_path;
					$paths['relative'] = str_replace($paths['basedir'], '', $paths['absolute']);
				}
			}
		}
		
		/**
		 * check if the files have matching md5, if not change the filename
		 * change save_dir so that the file is not
		 **/
		if (!empty($atc_id)) {
			if(!$this->filesystem()->exists($paths['absolute']) || @md5_file($file_url) !== @md5_file($paths['absolute'])){
				$fn_info = pathinfo($paths['filename']);
				$nr = 0;
				while(true){
					$nr++;
					$paths['filename'] = $fn_info['filename'] . $nr . '.' . $fn_info['extension'];
					$paths['relative'] = dirname($paths['relative']) . '/' . $paths['filename'];
					$paths['absolute'] = $paths['basedir'] . $paths['relative'];
					if(!$this->filesystem()->exists($paths['absolute'])){
						break;
					}
				}
				$atc_id = $this->get_image_id_by_url($paths['relative']);
			}
			//check if the file really exists in the filesystem, if not reset and redownload
			if(!$this->filesystem()->exists($paths['absolute'])) $atc_id = false;
		}

		if (empty($atc_id)) {
			$copied = $this->copy_source_file($file_url, $paths['absolute']);
			if(is_wp_error($copied)){
				return [
					'success' => false,
					'message' => sprintf( __( 'Could not download source file: %s', 'revslider' ), $copied->get_error_message()),
				];
			}
			if(!$this->filesystem()->exists($paths['absolute'])){
				return [
					'success' => false,
					'message' => sprintf( __( 'Could not create destination file: %s', 'revslider' ), $paths['absolute']),
				];
			}

			$paths = apply_filters('revslider_import_media_insert_attachment_before', $paths);
			//the filter can hand back a file the library holds already (the webp of a poster saved before): that row is reused
			$atc_id = $this->get_image_id_by_url($paths['relative']);
		}
		if (empty($atc_id)) {

			$file_info = wp_getimagesize($paths['absolute']);
			$mime_type = $this->get_val($file_info, 'mime');
			$allowed_mimes = $this->media_mimes($paths['filename']); //an AddOn's own type (a lottie .json) has passed the bad_extensions gate above
			if(!$file_info){
				$type_check = wp_check_filetype($paths['absolute'], $allowed_mimes);
				if(empty($type_check['type'])){
					$this->filesystem()->delete($paths['absolute']);
					return [
						'success' => false,
						'message' => sprintf( __( 'Could not validate destination file: %s', 'revslider' ), $paths['absolute']),
					];
				}
				$mime_type = $type_check['type'];
			}
			if(empty($mime_type) || !in_array($mime_type, $allowed_mimes, true)){
				$this->filesystem()->delete($paths['absolute']);
				return [
					'success' => false,
					'message' => sprintf( __( 'Mime type not allowed: %s', 'revslider' ), $mime_type),
				];
			}

			//zip and object library imports never pass Api::check_upload(), so their SVGs are cleaned here
			if($mime_type === 'image/svg+xml' || strtolower((string)$this->get_val($path_info, 'extension')) === 'svg'){
				if(!$this->sanitize_svg_file($paths['absolute'])){
					$this->filesystem()->delete($paths['absolute']);
					return [
						'success' => false,
						'message' => sprintf( __( 'SVG could not be sanitized: %s', 'revslider' ), $paths['filename']),
					];
				}
			}

			//the finished file becomes a library item; record fields, thumbnail deferral and
			//metadata are the host's business behind the port
			$attach_id = $this->media_library()->import($paths['relative'], [
				'filename'	=> $paths['filename'],
				'url'		=> $paths['baseurl'] . $paths['relative'],
				'absolute'	=> $paths['absolute'],
				'mime_type'	=> $mime_type,
				'excerpt'	=> $post_excerpt,
				'content'	=> $post_content,
			]);
		}else{
			$attach_id = $atc_id;
		}
		
		$paths['upload_relative'] = !$this->environment()->is_multisite() ? 'uploads/' . $paths['relative'] : $paths['relative'];
		
		return [
			'success' => true,
			'id'      => $attach_id,
			'path'    => $paths['upload_relative'],
			'url'     => $paths['baseurl'] . $paths['relative'],
		];
	}
	
	
	/**
	 * temporary remove image sizes so that only the needed thumb will be created
	 * intermediate_image_sizes_advanced filter, hooked only around an import so WordPress does not generate
	 * the theme's whole size set for every imported image
	 * @since: 6.0
	 * @return array
	 **/
	public static function temporary_remove_sizes($sizes, $meta = false){
		return (!empty($sizes) && isset($sizes['thumbnail'])) ? ['thumbnail' => $sizes['thumbnail']] : $sizes;
	}
	
	
	/**
	 * get wp-content path
	 * @return string absolute path, multisite aware
	 */
	public function get_upload_path(){
		if($this->environment()->is_multisite()){
			global $wpdb;
			return (!defined('BLOGUPLOADDIR')) ? ABSPATH . 'wp-content/uploads/sites/' . $wpdb->blogid : BLOGUPLOADDIR;
		}
		
		return (!empty(WP_CONTENT_DIR)) ? WP_CONTENT_DIR . '/' : ABSPATH . 'wp-content/uploads/';
	}
	
	/**
	 * get contents of the static css file
	 * the user's own CSS from the editor's global settings
	 * @return string
	 */
	public function get_static_css(){
		return $this->get_options(['other', 'static-css'], '');
	}
	
	/**
	 * store the user's own CSS, unescaping the slashes it arrives with
	 * @return string the stored CSS
	 */
	public function update_static_css($css){
		$css = str_replace(["\'", '\"', '\\\\'], ["'", '"', '\\'], trim($css));
		$this->update_option(['other', 'static-css'], $css); //write where get_static_css() reads it; the old standalone 'revslider-static-css' option was never read back (and autoloaded)
		return $css;
	}

	/**
	 * get the client browser with version
	 * parsed from the user agent; used for browser specific font format decisions
	 * @return array ['userAgent', 'name', 'version', 'platform', 'pattern']
	 **/
	public function get_browser(){
		$u_agent	= $this->get_val($_SERVER, 'HTTP_USER_AGENT');
		$bname		= 'Unknown';
		$platform	= 'Unknown';
		$version	= '';
		$ub			= '';

		// get platform
		if (preg_match('/linux/i', $u_agent)) {
			$platform = 'linux';
		} elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
			$platform = 'mac';
		} elseif (preg_match('/windows|win32/i', $u_agent)) {
			$platform = 'windows';
		}

		// get name of useragent
		if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) {
			$bname = 'Internet Explorer';
			$ub = 'MSIE';
		} elseif(preg_match('/Firefox/i',$u_agent)) {
			$bname = 'Mozilla Firefox';
			$ub = 'Firefox';
		} elseif(preg_match('/OPR/i',$u_agent))	{
			$bname = 'Opera';
			$ub = 'Opera';
		} elseif(preg_match('/Chrome/i',$u_agent) && !preg_match('/Edg/i',$u_agent)) {
			$bname = 'Google Chrome';
			$ub = 'Chrome';
		} elseif(preg_match('/Safari/i',$u_agent) && !preg_match('/Edg/i',$u_agent)) {
			$bname = 'Apple Safari';
			$ub = 'Safari';
		} elseif(preg_match('/Netscape/i',$u_agent)) {
			$bname = 'Netscape';
			$ub = 'Netscape';
		} elseif(preg_match('/Edg/i',$u_agent)) {
			$bname = 'Edge';
			$ub = 'Edg';
		} elseif(preg_match('/Trident/i',$u_agent)) {
			$bname = 'Internet Explorer';
			$ub = 'MSIE';
		}

		// get version
		$known		= ['Version', $ub, 'other'];
		$pattern	= '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if (!preg_match_all($pattern, $u_agent, $matches)){ /* */ }
		// see how many we have
		$i			= count($this->get_val($matches, 'browser', [])); //[] default: get_val returns '' otherwise, and count('') is a TypeError in PHP 8
		$version	= $this->get_val($matches, ['version', 0]);
		if ($i != 1) {
			//we will have two since we are not using the 'other' argument yet
			//see if the version is before or after the name
			$version = (strripos($u_agent, 'Version') < strripos($u_agent,$ub)) ? $version : $this->get_val($matches, ['version', 1]);
		}

		// check if we have a number
		if ($version == null || $version == '') $version = '0';

		return [
			'name'		=> $bname,
			'version'	=> $version,
			'platform'	=> $platform
		];
    }

	/**
	 * Change FontURL to new URL (added for chinese support since google is blocked there)
	 * replaces the Google Fonts host with the mirror configured in the global settings
	 * @since: 5.0
	 * @return string
	 */
	public function modify_fonts_url($url, $remove = true){
		$gs = $this->get_global_settings();
		$df = $this->get_val($gs, ['fonts', 'url'], '');
		$df = (!in_array($df, ['', 'off'])) ? $df : $url;

		//force absolute https for the font CDN: a protocol-relative //host URL gets collapsed to a page-relative ?family= by some optimizers/CDNs, which then loads the (text/html) page as a stylesheet
		return ($remove) ? $this->remove_http($df, 'https') : $df;
	}
	
	/**
	 * convert date to the date format that the user chose.
	 * @param bool $with_time append the site's time format
	 * @return string
	 */
	public function convert_post_date($date, $with_time = false){
		if(empty($date)) return $date;

		return $this->environment()->format_date(strtotime($date), $with_time);
	}
	
	
	/**
	 * return biggest value of object depending on which devices are enabled
	 * v6 device values: an object keyed d/n/t/m (desktop, notebook, tablet, mobile). Returns the value of
	 * the largest enabled device, so a setting only filled for mobile still has something to inherit from.
	 * @since: 5.0
	 * @param string $default sentinel '########' means "no default given"
	 * @return mixed
	 **/
	public function get_biggest_device_setting($obj, $enabled_devices, $default = '########'){
		$dv = $this->get_val($obj, ['d', 'v']);
		if($this->get_val($enabled_devices, 'd') === true && $dv != '') return $dv;
		if($default !== '########') return $default;
		$nv = $this->get_val($obj, ['n', 'v']);
		if($this->get_val($enabled_devices, 'n') === true && $nv != '') return $nv;
		$tv = $this->get_val($obj, ['t', 'v']);
		if($this->get_val($enabled_devices, 't') === true && $tv != '') return $tv;
		$mv = $this->get_val($obj, ['m', 'v']);
		if($this->get_val($enabled_devices, 'm') === true && $mv != '') return $mv;
		
		return '';
	}

	/**
	 * same for v7, where the value is a flat array ordered [ld, d, n, t, m] and '#a' marks "inherit"
	 * @since: 5.0
	 * @return mixed
	 **/
	public function get_biggest_device_setting_v7($obj, $enabled_devices, $default = '########'){
		if(empty($obj)) return ($default !== '########') ? $default : '';
		if(!is_array($obj)) return $obj;
		
		$devices = ['ld', 'd', 'n', 't', 'm'];
		foreach($devices as $key => $device){
			if($this->get_val($enabled_devices, $device) === true && !in_array($obj[$key], ['', '#a', 'none'])) return $obj[$key];	//'none' = the image field cleared on that device
		}

		return ($default !== '########') ? $default : $obj[1];
	}

	/**
	 * The alt and title a picture is written out with, by the layer's Alt Option and Title Option: the Media
	 * Library entry (default), the file name, or the custom text. A layer without a stored lib_id gets it
	 * looked up from the URL, and only when an option actually needs the library
	 * @param array  $layer carries attr.aO / attr.tO / attr.a / attr.t and bg.image.lib_id
	 * @param string $img   the picture's URL
	 * @return array ['alt' => string, 'title' => string]
	 */
	public function get_media_alt_title($layer, $img){
		$img_id			= $this->get_val($layer, ['bg', 'image', 'lib_id']);
		$img_filename	= basename($img);
		$alt_option		= $this->get_val($layer, ['attr', 'aO']);
		$title_option	= $this->get_val($layer, ['attr', 'tO']);

		if(empty($img_id) || intval($img_id) === 0){
			$needs_ml = in_array($alt_option, ['ml', 'media_library', '', null], true) || in_array($title_option, ['ml', 'media_library', '', null], true);
			if($needs_ml) $img_id = $this->media_library()->id_by_url($img); //protocol relative, site relative and the other scheme included
		}

		$file_name	= null; //resolved once, shared by alt and title when both use the file name
		$attr		= ['alt' => '', 'title' => ''];
		foreach(['alt' => [$alt_option, 'a'], 'title' => [$title_option, 't']] as $key => $option){
			switch($option[0]){
				case 'ml':
				case 'media_library':
				default:
					$attr[$key] = ($key === 'alt') ? $this->media_library()->alt_text($img_id) : $this->media_library()->title($img_id);
				break;
				case 'fn':
				case 'file_name':
					if($file_name === null) $file_name = pathinfo($img_filename, PATHINFO_FILENAME);
					$attr[$key] = $file_name;
				break;
				case 'c':
				case 'custom':
					$attr[$key] = $this->get_val($layer, ['attr', $option[1]]);
				break;
			}
		}

		return $attr;
	}

	/**
	 * like get_biggest_device_setting_v7(), but compares numerically and returns the largest value found
	 * instead of the first enabled one
	 * @since: 5.0
	 * @return int
	 **/
	public function get_biggest_size_setting_v7($obj, $enabled_devices, $default = '########'){
		$devices = ['ld', 'd', 'n', 't', 'm'];
		$biggest = false;

		foreach($devices ?? [] as $key => $device){
			if($this->get_val($enabled_devices, $device) === true && !in_array($obj[$key], ['', '#a'])) if($biggest === false || intval($obj[$key]) > $biggest) $biggest = $obj[$key];
		}

		return ($biggest === false && $default !== '########') ? $default : intval($biggest);
	}
	
	
	/**
	 * normalize object with device informations depending on what is enabled for the Slider
	 * fills the gaps in a device object: every disabled or empty device inherits from the next larger one,
	 * so the front end always gets a complete set
	 * @since: 5.0
	 * @param string $return 'obj' returns the array, anything else joins the values with $use
	 * @param array  $set_to_if value replacements applied before normalizing, from => to
	 * @return mixed
	 **/
	public function normalize_device_settings($obj, $enabled_devices, $return = 'obj', $default = [], $set_to_if = [], $use = ','){ //array -> from -> to
		/*d n t m*/
		$obj			= $this->fill_device_settings($obj);
		$_def			= (!empty($default)) ? reset($default) : '########';
		$inherit_size	= $this->get_biggest_device_setting($obj, $enabled_devices, $_def);
		
		if(!empty($set_to_if)){
			foreach($obj as $device => $key){
				foreach($set_to_if as $from => $to){
					if(trim($this->get_val($obj, [$device, 'v'])) == $from) $obj[$device]['v'] = $to;
				}
			}
		}

		$device_types = ['d', 'n', 't', 'm'];
		foreach($device_types as $device){
			if($enabled_devices[$device] === true){
				$value = $this->get_val($obj, [$device, 'v'], '');
				if($value === ''){
					$obj[$device]['v'] = ($_def !== '########') ? $_def : $inherit_size;
				}else{
					$inherit_size = $value;
				}
			}else{
				$obj[$device]['v'] = $inherit_size;
			}
		}
	
		switch ($return) {
			case 'obj':
				return [
					'd' => $obj['d']['v'],
					'n' => $obj['n']['v'],
					't' => $obj['t']['v'],
					'm' => $obj['m']['v']
				];
			case 'html-array':
				$html_array = ($obj['d']['v'] === $obj['n']['v'] && $obj['d']['v'] === $obj['m']['v'] && $obj['d']['v'] === $obj['t']['v']) ? $obj['d']['v'] : implode($use, array_column($obj, 'v'));

				return (!empty($default) && in_array($html_array, $default)) ? '' : $html_array;
			case 'array':
				$array = [];
				if($obj['d']['v'] === $obj['n']['v'] && $obj['d']['v'] === $obj['m']['v'] && $obj['d']['v'] === $obj['t']['v']){
					$array[$obj['d']['v']] = $obj['d']['v'];
				}else{
					$array[$obj['d']['v']] = $this->get_val($obj, ['d', 'v']);
					$array[$obj['n']['v']] = $this->get_val($obj, ['n', 'v']);
					$array[$obj['t']['v']] = $this->get_val($obj, ['t', 'v']);
					$array[$obj['m']['v']] = $this->get_val($obj, ['m', 'v']);
					if(!empty($array)){
						foreach($array as $k => $v){
							if(trim($v) === ''){
								unset($array[$k]);
							}
						}
					}
				}
				
				return $array;
		}
	
		return $obj;
	}
	
	/**
	 * fill object with default values
	 * make sure a device object has an entry for every device, so callers can index it without checks
	 * @since: 6.0
	 * @return array
	 **/
	public function fill_device_settings($obj){
		$push = ['d', 'n', 't', 'm'];
		
		if(is_string($obj)){
			$t = $obj;
			$obj = [];
			foreach($push as $p){
				$obj[$p] = ['v' => $t];
			}
			return $obj;
		}
		
		$_obj = [];
		foreach($push as $p){
			$_obj[$p] = (!isset($obj[$p])) ? [] : $obj[$p];
			if(!isset($_obj[$p]['v'])){
				$_obj[$p]['v'] = '';
				$_obj[$p]['u'] = '';
			}
		}
		
		return $_obj;
	}

	/**
	 * get the values for the given transition
	 * looks a slide transition up by name in the nested base transition catalogue
	 * @return array empty when the name is unknown
	 **/
	public function get_slide_transition_values($transition, $base_transitions = []){
		if(empty($base_transitions)) $base_transitions = $this->get_base_transitions();
		foreach($base_transitions as $t){
			if(!is_array($t)) continue;
			foreach($t as $_t){
				if(!is_array($_t)) continue;
				foreach($_t as $name => $values){
					if($name !== $transition) continue;
					
					return $values;
				}
			}
		}
		return [];
	}
	
	
	/**
	 * get a random slide transition for the given main and grp
	 * @param string       $main transition family, 'all' for any
	 * @param array|string $grp  group filter, comma separated string allowed
	 * @return string transition name, '' when nothing matches
	 **/
	public function get_random_slide_transition($main, $grp, $base_transitions = []){
		if(empty($base_transitions)) $base_transitions = $this->get_base_transitions();
		
		if(!is_array($grp) && !empty($grp)) $grp = explode(',', $grp);
		if($grp === '') $grp = [];
		
		$items = [];
		foreach($base_transitions as $m => $bt){
			if(!is_string($m) || $m === 'random' || $m === 'custom' || ($main !== 'all' && $main !== $m)) continue;
			foreach($bt as $g => $_bt){
				if(is_array($_bt) && $g !== 'icon' && (empty($grp) || in_array($g, $grp))){
					foreach($_bt as $e => $__bt){
						$items[] = $e;
					}
				}
			}
		}
		
		$num = (!empty($items)) ? array_rand($items, 1) : false;
		return ($num !== false) ? $items[$num] : '';
	}
	
	
	/**
	 * Remove http:// and https://
	 * @since: 6.0.0
	 * @param string $special 'auto' makes the URL protocol relative, 'http'/'https' force a scheme,
	 *                        'keep' leaves it untouched
	 * @return string
	 **/
	public function remove_http($url, $special = 'auto'){
		switch($special){
			case 'http':
				$url = str_replace('https://', 'http://', $url);
				if(strpos($url, 'http://') === false) $url = 'http://'.$url;
			break;
			case 'https':
				if(strpos($url, '//') === 0) $url = 'https:'.$url; //protocol-relative -> https
				$url = str_replace('http://', 'https://', $url);
				if(strpos($url, 'https://') === false) $url = 'https://'.$url;
			break;
			case 'keep': //do nothing
			break;
			case 'auto':
			default:
				$url = str_replace(['http://', 'https://'], '//' , $url);
			break;
		}
		return $url;
	}

	/**
	 * set the memory limit to at least 256MB if possible
	 * @since: 6.1.6
	 * @return void
	 **/
	public static function set_memory_limit(){
		wp_raise_memory_limit('revslider');
	}
	
	
	/**
	 * Check if page is edited in Gutenberg
	 */
	public function _is_gutenberg_page(){
		return $this->environment()->is_block_editor_request();
	}
	
	
	
	/**
	 * get custom transitions
	 * slide transitions the user built in the editor, stored in the options
	 * @return array
	 **/
	public function get_custom_slidetransitions(){
		$custom = $this->get_options(['other', 'slide-transitions'], []);
		
		return apply_filters('rs_get_custom_slidetransitions', $custom);
	}
	
	
	/**
	 * store a custom slide transition, assigning a new id when the template has none
	 * @return int the id it was stored under
	 **/
	public function save_custom_slidetransitions($template){
		$custom = $this->get_custom_slidetransitions();
		
		//empty custom templates?
		if(empty($custom)){
			$custom = [];
			$new_id = 1;
		}else{
			$id = $this->get_val($template, 'id', 0);
			//custom templates exist
			$new_id = ($id > 0) ? $id : max(array_keys($custom)) + 1;
		}
		
		//update or insert template
		$custom[$new_id]['title']	= $template['obj']['title'];
		$custom[$new_id]['preset']	= $template['obj']['preset'];
		//return the ID the template was saved with
		$this->update_option(['other', 'slide-transitions'], $custom);

		return $new_id;
	}
	
	
	/**
	 * remove a custom slide transition (identified by its id inside $template)
	 * @return bool false when no transition with that id exists
	 **/
	public function delete_custom_slidetransitions($template){
		//load templates array
		$custom = $this->get_custom_slidetransitions();
		
		$id = intval($this->get_val($template, 'id', 0));
		//custom template exist
		if($id > 0 && isset($custom[$id])){
			//delete given ID
			unset($custom[$id]);
			//save the resulting templates array again
			$this->update_option(['other', 'slide-transitions'], $custom);
			return true;	
		}
		
		return false;
	}
	/**
	 * push the matieral icons css into the global variable
	 * queues the Material Icons font once per request, either from the CDN or the bundled copy
	 * @return string the markup to print, '' when it is already queued
	 **/
	public function add_material_icons(){
		global $SR_GLOBALS;
		if($this->get_val($SR_GLOBALS, ['icon_sets', 'Materialicons', 'css'], false) !== false) return '';

		$gs = $this->get_global_settings();

		if($this->get_val($gs, ['fonts', 'download'], 'off') === 'off'){
			$font_face = "@font-face {
  font-family: 'Material Icons';
  font-style: normal;
  font-weight: 400;  
  src: url(//fonts.gstatic.com/s/materialicons/v41/flUhRq6tzZclQEJ-Vdg-IuiaDsNcIhQ8tQ.woff2) format('woff2');
}";
		}else{
			$font_face = "@font-face {
font-family: 'Material Icons';
font-style: normal;
font-weight: 400;  

src: local('Material Icons'),
local('MaterialIcons-Regular'),
  url(".RS_CORE_URL_CLEAN."public/css/fonts/material/MaterialIcons-Regular.woff2) format('woff2');
}";
		}

		$this->set_val($SR_GLOBALS, ['icon_sets', 'Materialicons', 'css'], "/* 
ICON SET 
*/
".$font_face."

rs-module .material-icons {
  font-family: 'Material Icons';
  font-weight: normal;
  font-style: normal;
	font-size: inherit;
  display: inline-block;  
  text-transform: none;
  letter-spacing: normal;
  word-wrap: normal;
  white-space: nowrap;
  direction: ltr;
  vertical-align: top;
  line-height: inherit;
  /* Support for IE. */
  font-feature-settings: 'liga';

  -webkit-font-smoothing: antialiased;
  text-rendering: optimizeLegibility;
  -moz-osx-font-smoothing: grayscale;
}");
	}
	
	/**
	 * get the current page id
	 * @since: 6.0
	 * @return int|string the post id, or 'homepage' on the front page
	 **/
	public function get_current_page_id(){
		return $this->environment()->current_page_id();
	}

	/**
	 * this will return the exact alias of the rev_slider modules on given posts/pages
	 * parses the [rev_slider] / [sr7] shortcodes out of the post content
	 * @param array|int $ids post ids
	 * @return array the slider aliases used on those posts, without duplicates
	 **/
	public function get_shortcode_from_page($ids){
		return $this->create_content_source()->get_shortcode_from_page($this, $ids); //delegates to the platform's content source @since 7.2.1
	}

	/**
	 * checks if any shortcode format is present in given string
	 * @return bool
	 */
	public function has_any_shortcode($text){
		//cheap pre-check: skip the regex when there is no '[' at all (the common case)
		if(!is_string($text) || strpos($text, '[') === false) return false;
		return (preg_match('/\[.*?\]/', $text)) ? true : false;
	}

	/**
	 * the site wide basic metas, resolved lazily as they are page context, not post loop data
	 * single source of truth for PHP, mirrored in JS by SR7.F.getSiteMeta()
	 * @since 7.1.4
	 * @return array
	 */
	public function get_basic_metas(){
		$site		= $this->environment()->site_url();
		$permalink	= $this->environment()->current_page_url();

		//addons add their own site wide placeholders here, they then resolve everywhere the core ones do
		return apply_filters('revslider_basic_metas', [
			'home_url'			=> esc_url($site),
			'current_page_link'	=> esc_url($permalink !== '' ? $permalink : $site)
		]);
	}

	/**
	 * Resolve site-wide basic meta placeholders (text, href, action URLs).
	 * @since 7.1.4
	 * @return string
	 */
	public function resolve_basic_metas($text){
		if(!is_string($text) || strpos($text, '{{') === false) return $text;
		foreach($this->get_basic_metas() as $meta => $value){
			if(strpos($text, '{{'.$meta.'}}') === false) continue;
			$text = str_replace('{{'.$meta.'}}', $value, $text);
		}
		return $text;
	}

	/**
	 * open and checks a zip file for filetypes
	 * rejects the whole import when any entry has a blocked extension
	 * @param array|false $extensions_allowed whitelist; false uses the global blocklist instead
	 * @return void
	 * @throws Exception when the zip cannot be opened or contains an illegal file
	 **/
	public function check_bad_files($zip_file, $extensions_allowed = false){
		global $SR_GLOBALS;
		if(class_exists('ZipArchive')){
			$zip = new \ZipArchive;
			$success = $zip->open($zip_file);
			
			if($success !== true) $this->throw_error(__("Can not open zip file", 'revslider'));

			for($i = 0; $i < $zip->numFiles; $i++){
				$path_info = pathinfo($zip->getNameIndex($i));
				if(!isset($path_info['extension'])) continue;
			
				$pi = strtolower($path_info['extension']);
				if($extensions_allowed !== false){
					if(!in_array($pi, $extensions_allowed)) $this->throw_error(__("zip file contains illegal files", 'revslider'));
				}else{
					if($this->is_bad_extension($pi)) $this->throw_error(__("zip file contains illegal files", 'revslider'));
				}
			}
		}else{ //fallback to pclzip
			$pclzip = apply_filters('revslider_zip_fallback', null, $zip_file); //the host's zip reader when ext-zip is missing
			if($pclzip === null) $this->throw_error(__("zip file could not be inspected", 'revslider'));

			$content = $pclzip->listContent();
			if(is_array($content) && !empty($content)){
				foreach($content as $file){
					if(!isset($file['filename'])) continue;

					$path_info = pathinfo($file['filename']);
					if(!isset($path_info['extension'])) continue;

					$pi = strtolower($path_info['extension']);
					if($extensions_allowed !== false){
						if(!in_array($pi, $extensions_allowed)) $this->throw_error(__("zip file contains illegal files", 'revslider'));
					}else{
						if($this->is_bad_extension($pi)) $this->throw_error(__("zip file contains illegal files", 'revslider'));
					}
				}
			}
		}
	}
	
	/**
	 * generate missing attachement metadata for images
	 * works through the queue an import left behind, so thumbnail generation does not block the import
	 * @since: 6.0
	 * @return void
	 **/
	public function generate_attachment_metadata(){
		$rs_meta_create = $this->get_options(['other', 'image-meta'], []);

		if(!empty($rs_meta_create)){
			foreach($rs_meta_create as $attach_id => $save_dir){
				//dequeue BEFORE generating: an image that crashes the generator must not come back forever
				unset($rs_meta_create[$attach_id]);
				$this->update_option(['other', 'image-meta'], $rs_meta_create);

				$this->media_library()->generate_renditions($attach_id, $save_dir);
			}
		}
	}
	
	/**
	 * set the font clean for import
	 * queues one font in $SR_GLOBALS['fonts'] so RevSliderFonts can emit it later
	 * @return void
	 */
	public function set_clean_font_import($font, $class = '', $url = '', $variants = [], $subsets = []){
		global $SR_GLOBALS;
		
		if(!isset($SR_GLOBALS['fonts'])) $SR_GLOBALS['fonts'] = ['queue' => [], 'loaded' => []]; //if this is called without revslider.php beeing loaded
		
		if(!empty($variants) || !empty($subsets)){
			if(!isset($SR_GLOBALS['fonts']['queue'][$font])) $SR_GLOBALS['fonts']['queue'][$font] = [];
			if(!isset($SR_GLOBALS['fonts']['queue'][$font]['variants'])) $SR_GLOBALS['fonts']['queue'][$font]['variants'] = [];
			if(!isset($SR_GLOBALS['fonts']['queue'][$font]['subsets'])) $SR_GLOBALS['fonts']['queue'][$font]['subsets'] = [];
			
			if(!empty($variants)){
				foreach($variants as $k => $v){
					//check if the variant is already in loaded
					if(!in_array($v, $SR_GLOBALS['fonts']['queue'][$font]['variants'], true)){
						$SR_GLOBALS['fonts']['queue'][$font]['variants'][] = $v;
					}else{ //already included somewhere, so do not call it anymore
						unset($variants[$k]);
					}
				}
			}
			if(!empty($subsets)){
				foreach($subsets as $k => $v){
					if(!in_array($v, $SR_GLOBALS['fonts']['queue'][$font]['subsets'], true)){
						$SR_GLOBALS['fonts']['queue'][$font]['subsets'][] = $v;
					}else{ //already included somewhere, so do not call it anymore
						unset($subsets[$k]);
					}
				}
			}
			if($url !== ''){
				$SR_GLOBALS['fonts']['queue'][$font]['url'] = $url;
			}
		}
	}

	
	/**
	 * get categories list, copy the code from default wp functions
	 * @param bool $full $cat_ids already holds term objects instead of ids
	 * @return string linked category list
	 */
	public function get_categories_html($cat_ids, $tax = null, $post_id = '', $full = false){
		return $this->create_content_source()->get_categories_html($this, $cat_ids, $tax, $post_id, $full); //delegates to the platform's content source @since 7.2.1
	}

	/**
	 * get text intro, limit by number of words
	 * appends an ellipsis when it had to cut, and strips leftover shortcodes
	 * @return string
	 */
	public function get_text_intro($text, $limit){
		$limit++;
		$array = explode(' ', $text, $limit);
		
		if(count($array) >= $limit){
			array_pop($array);
			$intro = implode(' ', $array);
			$intro = trim($intro);
			$intro .= (!empty($intro)) ? '...' : '';
		}else{
			$intro = $text;
		}
		
		return preg_replace('`\[[^\]]*\]`', '', $intro);
	}
	
	/**
	 * same limited by characters instead of words
	 * @return string
	 */
	public function get_text_intro_chars($text, $limit){
		$intro = substr($text, 0, $limit);
		return preg_replace('`\[[^\]]*\]`', '', $intro);
	}

	/**
	 * a module's excerpt limit setting read: '55' and '20words' count words, '120chars' characters
	 * @since 7.2.1
	 * @param string|int $setting
	 * @return array type (words|chars) and limit
	 */
	public function excerpt_limit($setting){
		if(strpos($setting, 'chars') !== false) return ['type' => 'chars', 'limit' => (int)str_replace('chars', '', $setting)];

		return ['type' => 'words', 'limit' => (int)str_replace(['char', 'words'], '', $setting)]; //char is a fallback from before 6.3.4
	}

	/**
	 * an excerpt as a layer shows it: simple inline markup kept, line breaks dropped, cut to the limit
	 * @since 7.2.1
	 * @param string $text
	 * @param array $limit as excerpt_limit() reads it
	 * @return string
	 */
	public function shorten_excerpt($text, $limit){
		$text = str_replace(['<br/>', '<br />'], '', strip_tags($text, '<b><br><i><strong><small>'));

		return ($limit['type'] === 'words') ? $this->get_text_intro($text, $limit['limit']) : $this->get_text_intro_chars($text, $limit['limit']);
	}

	/**
	 * the text of a {{field:words:N}} / {{field:chars:N}} placeholder: tags stripped, cut to N words or characters
	 * @since 7.2.1
	 * @return string|false false for a unit other than words and chars
	 */
	public function cut_text($text, $unit, $count){
		switch($unit){
			case 'words':
				$words = explode(' ', strip_tags($text), $count + 1);
				if(is_array($words) && count($words) > $count) array_pop($words);
				return implode(' ', $words);
			case 'chars':
				return mb_substr(strip_tags($text), 0, $count);
		}

		return false;
	}

	/**
	 * the image tag a stream or post placeholder resolves to
	 * @since 7.2.1
	 * @param string $src
	 * @param string $alt
	 * @param array|null $size width and height, written even when empty; null for a tag without them
	 * @param string $class
	 * @return string
	 */
	public function stream_img_tag($src, $alt, $size = null, $class = ''){
		$tag = '<img' . (($class !== '') ? ' class="' . $class . '"' : '') . ' src="' . $src . '"';
		if($size !== null) $tag .= ' width="' . $size[0] . '" height="' . $size[1] . '"';

		return $tag . ' alt="' . esc_attr($alt) . '" data-no-retina />';
	}

	/**
	 * a content source call the ContentSource port does not declare (the v6 post sourcing): a host without it
	 * gets the empty answer instead of a fatal
	 * @since 7.2.1
	 * @return mixed
	 */
	protected function legacy_source_call($source, $method, $args, $empty){
		return method_exists($source, $method) ? $source->$method(...$args) : $empty;
	}

	/**
	 * convert assoc array to array
	 * @return array
	 */
	public static function assoc_to_array($assoc){
		return array_values($assoc ?? []);
	}

	/**
	 * filter non-allowed chars for html classes / IDs
	 * 
	 * @param array|string $classes
	 * @return array|string
	 */
	public function filter_class_name($classes){
		$single = false;
		if(!is_array($classes)){
			$classes = [$classes];
			$single = true;
		}

		$classes = array_map(function($className) {
			return preg_replace('/[^a-zA-Z \d_-]/', '', $className);
		}, $classes);

		return $single ? $classes[0] : $classes;
	}

	public function add_deprecation_message($old_func, $new_func){
		$g = &$this->context()->root();

		if(isset($g['deprecated'][$old_func])) return;
		//_deprecated_function($old_func, '7.0', $new_func);
		$g['deprecated'][$old_func] = $new_func;
	}
	
	/**
	 * print the inline setREVStartSize() helper once per page.
	 * It reserves the slider's height before the JS engine loads, so the page does not jump - which is why
	 * it has to be inline and cannot wait for sr7.js.
	 * @return false|void echoes the script; false when it was already printed this request
	 **/
	public static function js_set_start_size(){
		global $SR_GLOBALS;

		if(isset($SR_GLOBALS['js_startsize_init']) && $SR_GLOBALS['js_startsize_init'] === true) return false;
		
		$script = self::script_open();
		$script .= 'function setREVStartSize(e){
			//window.requestAnimationFrame(function() {
				window.RSIW = window.RSIW===undefined ? window.innerWidth : window.RSIW;
				window.RSIH = window.RSIH===undefined ? window.innerHeight : window.RSIH;
				try {
					var pw = document.getElementById(e.c).parentNode.offsetWidth,
						newh;
					pw = pw===0 || isNaN(pw) || (e.l=="fullwidth" || e.layout=="fullwidth") ? window.RSIW : pw;
					e.tabw = e.tabw===undefined ? 0 : parseInt(e.tabw);
					e.thumbw = e.thumbw===undefined ? 0 : parseInt(e.thumbw);
					e.tabh = e.tabh===undefined ? 0 : parseInt(e.tabh);
					e.thumbh = e.thumbh===undefined ? 0 : parseInt(e.thumbh);
					e.tabhide = e.tabhide===undefined ? 0 : parseInt(e.tabhide);
					e.thumbhide = e.thumbhide===undefined ? 0 : parseInt(e.thumbhide);
					e.mh = e.mh===undefined || e.mh=="" || e.mh==="auto" ? 0 : parseInt(e.mh,0);
					if(e.layout==="fullscreen" || e.l==="fullscreen")
						newh = Math.max(e.mh,window.RSIH);
					else{
						e.gw = Array.isArray(e.gw) ? e.gw : [e.gw];
						for (var i in e.rl) if (e.gw[i]===undefined || e.gw[i]===0) e.gw[i] = e.gw[i-1];
						e.gh = e.el===undefined || e.el==="" || (Array.isArray(e.el) && e.el.length==0)? e.gh : e.el;
						e.gh = Array.isArray(e.gh) ? e.gh : [e.gh];
						for (var i in e.rl) if (e.gh[i]===undefined || e.gh[i]===0) e.gh[i] = e.gh[i-1];
											
						var nl = new Array(e.rl.length),
							ix = 0,
							sl;
						e.tabw = e.tabhide>=pw ? 0 : e.tabw;
						e.thumbw = e.thumbhide>=pw ? 0 : e.thumbw;
						e.tabh = e.tabhide>=pw ? 0 : e.tabh;
						e.thumbh = e.thumbhide>=pw ? 0 : e.thumbh;
						for (var i in e.rl) nl[i] = e.rl[i]<window.RSIW ? 0 : e.rl[i];
						sl = nl[0];
						for (var i in nl) if (sl>nl[i] && nl[i]>0) { sl = nl[i]; ix=i;}
						var m = pw>(e.gw[ix]+e.tabw+e.thumbw) ? 1 : (pw-(e.tabw+e.thumbw)) / (e.gw[ix]);
						newh =  (e.gh[ix] * m) + (e.tabh + e.thumbh);
					}
					var el = document.getElementById(e.c);
					if (el!==null && el) el.style.height = newh+"px";
					el = document.getElementById(e.c+"_wrapper");
					if (el!==null && el) {
						el.style.height = newh+"px";
						el.style.display = "block";
					}
				} catch(e){
					console.log("Failure at Presize of Slider:" + e)
				}
			//});
		  };';
		$script .= '</script>' . "\n";
		echo apply_filters('revslider_add_setREVStartSize', $script);
		
		$SR_GLOBALS['js_startsize_init'] = true;
	}

	/**
	 * make sure every rendered slider gets a unique DOM id, appending _1, _2 … on collisions
	 * @return string the id to use
	 **/
	public function set_html_id_v7($html_id, $check_for_duplication){
		$g = &$this->context()->root();

		if($check_for_duplication){ //check if it already exists, if yes change it and add attribute for console output
			$ids = $this->get_val($g, ['collections', 'ids']);
			if(in_array($html_id, $ids, true)){
				$i = 0;
				do{$i++; }while(in_array($html_id.'_'.$i, $ids, true));
				$html_id .= '_'.$i;
			}
		}
		if(!in_array($html_id, $g['collections']['ids'])) $g['collections']['ids'][] = $html_id;

		return $html_id;
	}

	/**
	 * compress the css
	 * strips comments and superfluous whitespace from generated stylesheets
	 * @return string
	 **/
	public function compress_css($buffer){
		/* remove comments - ?? keeps the uncompressed css if the (nested-quantifier) pattern hits the
		   backtrack limit on large input; preg_replace returns null there and would wipe the css entirely */
		$buffer = preg_replace("!/\*[^*]*\*+([^/][^*]*\*+)*/!", '', $buffer) ?? $buffer;
		/* remove tabs, spaces, newlines, etc. */
		$arr = ["\r\n", "\r", "\n", "\t", '  ', '    ', '    '];
		$rep = ['', '', '', '', ' ', ' ', ' '];
		$buffer = str_replace($arr, $rep, $buffer);
		/* remove whitespaces around {}:, */
		$buffer = preg_replace("/\s*([\{\}:,])\s*/", "$1", $buffer) ?? $buffer;
		/* remove last ; */
		$buffer = str_replace(';}', '}', $buffer);
		
		return $buffer;
	}

	/**
	 * parse css stylesheet to an array
	 * @return array selector => [property => value]
	 **/
	public function css_to_array($css){
		
		while(strpos($css, '/*') !== false){
			if(strpos($css, '*/') === false) return false;
			$start	= strpos($css, '/*');
			$end	= strpos($css, '*/') + 2;
			$css	= str_replace(substr($css, $start, $end - $start), '', $css);
		}
		
		preg_match_all('/(?ims)([a-z0-9\,\s\.\:#_\-@]+)\{([^\}]*)\}/', $css, $arr);

		$result = [];
		foreach($arr[0] as $i => $x){
			$selector = trim($arr[1][$i]);
			if(strpos($selector, '{') !== false || strpos($selector, '}') !== false) return false;
			$rules = explode(';', trim($arr[2][$i]));
			$result[$selector] = [];
			foreach($rules as $strRule){
				if(!empty($strRule)){
					$rule = explode(':', $strRule);
					
					//put back everything but not $rule[0];
					$key = trim($rule[0]);
					unset($rule[0]);
					$values = implode(':', $rule);
					
					$result[$selector][trim($key)] = trim(str_replace("'", '"', $values));
				}
			}
		}
		
		return $result;
	}



	/**
	 * read a nested array value by a path of keys
	 * @return mixed $default when any level of the path is missing
	 */
	public function array_get_path($root, array $path, $default = false) {
		if(empty($path)) return $default;

		$key = array_shift($path);

		if($key === '__ARRAY__'){
			if(!is_array($root)) return $default;
			$out = [];
			foreach ($root as $item) {
				$out[] = empty($path) ? $item : $this->array_get_path($item, $path, $default);
			}
			return $out;
		}

		if(!is_array($root) || !array_key_exists($key, $root)) return $default;
		if(empty($path)) return $root[$key];

		return $this->array_get_path($root[$key], $path, $default);
	}

	/**
	 * write a nested array value by a path of keys, creating the levels on the way
	 * @param array $root modified by reference
	 * @return void
	 */
	public function array_set_path(&$root, array $path, $value) {
		if(empty($path)) return;

		$key = array_shift($path);

		if($key === '__ARRAY__'){
			if(!is_array($root)) return;

			$isListValue = is_array($value);
			$i = 0;
			foreach ($root as $idx => &$item) {
				$valForThis = $isListValue && array_key_exists($i, $value) ? $value[$i] : $value;

				if (!empty($path)) {
					$this->array_set_path($item, $path, $valForThis);
				} else {
					// At ['...','__ARRAY__'] the element itself is the leaf; set it directly.
					$item = $valForThis;
				}
				$i++;
			}
			unset($item);
			return;
		}

		// Build shells as needed
		if(!isset($root[$key]) || !is_array($root[$key])){
			if (!isset($root[$key])) $root[$key] = [];
			elseif (!is_array($root[$key])) $root[$key] = [];
		}

		if(empty($path)){
			$root[$key] = $value;
			return;
		}

		$this->array_set_path($root[$key], $path, $value);
	}
	
	/**
	 * whether the update server gives this site the core and its updates without a licence (ADR-0027): it says so in
	 * the update check's additions while HQ's free_core.revslider switch is open, absent means closed
	 * @since 7.2.5
	 * @return bool
	 */
	public function free_core_open(){
		return $this->_truefalse($this->get_options(['system', 'additions', 'free_core'], false)) === true;
	}

	/**
	 * get options depending on given keys as array
	 * $db -> get directly from database, every time its true
	 * @param array|string $keys  path inside the option, [] for everything
	 * @param string       $field which option to read; the fonts branch lives in its own (see OPTIONS_FONTS)
	 * @return mixed
	 */
	public function get_options($keys = [], $default = '', $db = false, $field = 'sr-options'){
		global $SR_GLOBALS;
		$options_field = ($field === 'sr-options') ? 'options' : $field;

		$options = ($db === false && !empty($SR_GLOBALS[$options_field])) ? $SR_GLOBALS[$options_field] : $this->option_storage()->get($field, '');
		
		if(!is_array($options)) $options = json_decode($options, true);

		if($db === false && empty($SR_GLOBALS[$options_field])) $SR_GLOBALS[$options_field] = $options;

		return (empty($keys)) ? $options : $this->get_val($options, $keys, $default);
	}

	/**
	 * save all options into json format
	 * replaces the whole option; use update_option() to change a single path
	 * @return void
	 */
	public function update_all_options($options, $field = 'sr-options'){
		global $SR_GLOBALS;

		//never autoload the fonts blob - it is large and only read while rendering/managing fonts.
		//null = leave the store's default/existing autoload flag untouched for every other field.
		$this->option_storage()->set($field, $options, ($field === self::OPTIONS_FONTS) ? false : null);

		$options_field = ($field === 'sr-options') ? 'options' : $field;

		$SR_GLOBALS[$options_field] = $options;

		do_action('revslider_update_all_options', $options, $field);
	}

	/**
	 * update internal options by given keys of array
	 * revslider-valid and revslider-code will be double saved for the usage in older addons
	 * so that they can still be updated if they are on an older version pre v7
	 * @return void
	 */
	public function update_option($keys, $value, $field = 'sr-options'){
		$extra_save = [
			'revslider-valid' => ['system', 'valid'],
			'revslider-code' => ['system', 'license'],
		];
		$options = $this->get_options([], '', true, $field);
		if(empty($options)) $options = [];
		$was_valid = ($keys === ['system', 'valid']) ? $this->_truefalse($this->get_val($options, $keys, 'false')) : null;

		$this->set_val($options, $keys, $value);
		$this->update_all_options($options, $field);

		//a page cached under the other licence state would keep printing, or keep missing, its add-ons
		$cache = ($was_valid !== null && $was_valid !== $this->_truefalse($value)) ? Registry::instance()->get('RevSliderCache') : null;
		if($cache) $cache->clear_all_transients();

		foreach($extra_save as $handle => $v){
			if(array_diff($v, $keys)) continue;
			$this->option_storage()->set($handle, $value);
			break;
		}
	}

	/**
	 * deletes the option in our internal options by given keys of array
	 * @return bool false when the path does not exist
	 */
	public function delete_option($keys, $field = 'sr-options'){
		$options	= $this->get_options([], '', true, $field);
		$_options	= &$options;
		$keys		= (array)$keys;
		$last_key	= array_pop($keys);

		foreach($keys ?? [] as $key){
			if(!isset($_options[$key])) return false;
			$_options = &$_options[$key];
		}

		if(!isset($_options[$last_key])) return false;
		
		unset($_options[$last_key]);
		
		$this->update_all_options($options, $field);

		return true;
	}

	
	/**
	 * Create a temporary fake page/post
	 * a WP_Post that exists only in memory (id 999999999), so the_content filters can be run against
	 * arbitrary content - used by the preview and the HTML export
	 * @since: 6.0
	 * @return WP_Post|false false on a host whose transport builds no post objects
	 **/
	public function create_fake_post($content, $title = 'Slider Revolution'){
		$t = Api::host_transport('fake_post'); //post machinery, body on the host adapter

		return $t ? $t->fake_post($content, $title) : false; //a host with no post objects answers false, which the preview already reads as "nothing to render against"
	}


	/**
	 * global function to add revslider textdomain
	 * @return string
	 **/
	public function _t($text) {
		return __($text, 'revslider');
	}

	/**
	 * return filetime or SR version for cache busting
	 * @param string $path the file, relative to the plugin directory, e.g. 'core/public/js/sr7.js'
	 * @return int|string the file mtime, RS_REVISION when the file is missing
	 */
	public static function asset_time($path){
		//the two shipped trees are stamped from the copy RS_CORE_ASSETS_PATH names, which a host may keep elsewhere:
		//the same rewrite re-roots the path and answers whether it was one of them, so no offset is counted by hand
		$rel		= preg_replace('#^core/(public|admin/assets)/#', '$1/', $path, 1, $shipped);
		$fullPath	= ($shipped) ? RS_CORE_ASSETS_PATH . $rel : RS_PLUGIN_PATH . $path;
		return file_exists($fullPath) ? filemtime($fullPath) : RS_REVISION;
	}

	/**
	 * the opening tag of an inline script the front prints; a host adds through revslider_script_attributes what keeps
	 * page optimizers from deferring, delaying or bundling it (the engine reads SR7.E and SR7.JSON as the page parses)
	 * @since 7.2.3
	 * @return string
	 */
	public static function script_open(){
		static $attrs = null;
		$attrs ??= (string)apply_filters('revslider_script_attributes', '');
		return '<script' . $attrs . '>';
	}

	/**
	 * dev ships GSAP as its own file, the release build folds it into tptools.js. Enqueue the file only where it exists
	 * @since 7.2.0
	 * @return bool
	 */
	public static function has_single_gsap(){
		return file_exists(RS_CORE_ASSETS_PATH . 'public/js/libs/tpgsap.js');
	}

	/**
	 * The release build concatenates most of public/js/* into sr7.js and deletes the singles, so the loader's
	 * plugin_url + "core/public/js/<module>.js" is a 404 in every shipped build. Hand it the bundle that carries
	 * them instead: sr7.js stamps every module it holds as loaded in its first line, so this is a no-op on a
	 * screen that got sr7.js, and the only working fallback on one that did not.
	 * @since 7.2.0
	 * @return array module => url, only for the modules that have no single file
	 */
	public static function bundled_module_urls($modules){
		$urls = [];
		foreach($modules ?? [] as $module){
			if(!file_exists(RS_CORE_ASSETS_PATH . 'public/js/' . $module . '.js')) $urls[$module] = RS_CORE_URL_CLEAN . 'public/js/sr7.js';
		}

		return $urls;
	}

}
