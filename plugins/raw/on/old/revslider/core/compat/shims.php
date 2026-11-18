<?php
/*
 * The shim layer a non-WordPress host ships: SHIMMABLE and ADDON_SHIMMABLE from wp-contract.php,
 * same names, same behavior, plus the few current-page answers at the bottom that the render path
 * still asks the host for. The hook bus is real (addons and core filters must work), the
 * i18n/escaping shims are deliberately minimal - good enough for the smoke, hardened later.
 * Never loaded on WordPress: a host requires it before core/autoload.php, and only where no WordPress runs.
 */

if(!defined('ABSPATH')) exit();

$GLOBALS['__shim_filters'] = [];

//the Database port kept wpdb's result-shape vocabulary on purpose (frozen contract)
define('OBJECT', 'OBJECT');
define('OBJECT_K', 'OBJECT_K');
define('ARRAY_A', 'ARRAY_A');
define('ARRAY_N', 'ARRAY_N');

//WP's time constants, plain numbers everywhere
define('MINUTE_IN_SECONDS', 60);
define('HOUR_IN_SECONDS', 3600);
define('DAY_IN_SECONDS', 86400);
define('WEEK_IN_SECONDS', 604800);
define('MONTH_IN_SECONDS', 2592000);
define('YEAR_IN_SECONDS', 31536000);

function add_filter($tag, $cb, $prio = 10, $args = 1){
	$GLOBALS['__shim_filters'][$tag][$prio][] = ['cb' => $cb, 'args' => $args];
	return true;
}
function add_action($tag, $cb, $prio = 10, $args = 1){ return add_filter($tag, $cb, $prio, $args); }
$GLOBALS['__shim_hook'] = []; //the running hooks, innermost last: Cache::print_addition() finds its queued html by name
function apply_filters($tag, $value, ...$extra){
	if(empty($GLOBALS['__shim_filters'][$tag])) return $value;
	$hooks = $GLOBALS['__shim_filters'][$tag];
	ksort($hooks);
	$GLOBALS['__shim_hook'][] = $tag;
	try{
		foreach($hooks as $prio => $cbs){
			foreach($cbs as $h){
				$params = array_merge([$value], array_slice($extra, 0, max(0, $h['args'] - 1)));
				$value = call_user_func_array($h['cb'], $params);
			}
		}
	}finally{
		array_pop($GLOBALS['__shim_hook']);
	}
	return $value;
}
function do_action($tag, ...$args){
	if(empty($GLOBALS['__shim_filters'][$tag])) return;
	$hooks = $GLOBALS['__shim_filters'][$tag];
	ksort($hooks);
	$GLOBALS['__shim_hook'][] = $tag;
	try{
		foreach($hooks as $prio => $cbs){
			foreach($cbs as $h) call_user_func_array($h['cb'], array_slice($args, 0, $h['args']));
		}
	}finally{
		array_pop($GLOBALS['__shim_hook']);
	}
}
/** WordPress tells callbacks apart by object identity plus method, not by equal properties */
function shim_same_callback($a, $b){
	if(is_array($a) && is_array($b)) return count($a) === 2 && count($b) === 2 && $a[0] === $b[0] && $a[1] === $b[1];
	return $a === $b;
}
function remove_filter($tag, $cb, $prio = 10){
	foreach($GLOBALS['__shim_filters'][$tag][$prio] ?? [] as $i => $h){
		if(!shim_same_callback($h['cb'], $cb)) continue;
		unset($GLOBALS['__shim_filters'][$tag][$prio][$i]);
		if(empty($GLOBALS['__shim_filters'][$tag][$prio])) unset($GLOBALS['__shim_filters'][$tag][$prio]);
		return true;
	}
	return false;
}
function remove_action($tag, $cb, $prio = 10){ return remove_filter($tag, $cb, $prio); }
function has_filter($tag, $cb = false){
	if($cb === false) return !empty($GLOBALS['__shim_filters'][$tag]);
	foreach($GLOBALS['__shim_filters'][$tag] ?? [] as $prio => $cbs){
		foreach($cbs as $h) if(shim_same_callback($h['cb'], $cb)) return $prio;
	}
	return false;
}
function has_action($tag, $cb = false){ return has_filter($tag, $cb); }
function did_action($tag){ return 0; }
function current_filter(){ return end($GLOBALS['__shim_hook']) ?: ''; }
function doing_filter($f = null){ return ($f === null) ? ($GLOBALS['__shim_hook'] !== []) : in_array($f, $GLOBALS['__shim_hook'], true); }
function doing_action($a = null){ return doing_filter($a); }

//i18n
function __($t, $d = null){ return $t; }
function _e($t, $d = null){ echo $t; }
function _x($t, $c = null, $d = null){ return $t; }
function _n($s, $p, $n, $d = null){ return ($n == 1) ? $s : $p; }
function _nx($s, $p, $n, $c = null, $d = null){ return ($n == 1) ? $s : $p; }
function esc_html__($t, $d = null){ return esc_html($t); }
function esc_html_e($t, $d = null){ echo esc_html($t); }
function esc_attr__($t, $d = null){ return esc_attr($t); }
function esc_attr_e($t, $d = null){ echo esc_attr($t); }

//escaping and sanitizing
function esc_attr($t){ return htmlspecialchars((string)$t, ENT_QUOTES, 'UTF-8', false); }
function esc_html($t){ return htmlspecialchars((string)$t, ENT_QUOTES, 'UTF-8', false); }
function esc_url($url){ return str_replace(['"', "'", '<', '>'], '', (string)$url); }
function esc_url_raw($url){ return esc_url($url); }
function esc_js($t){ return str_replace(["\r", "\n", '"', "'"], ['', '\n', '\"', "\'"], (string)$t); }
function esc_textarea($t){ return htmlspecialchars((string)$t, ENT_QUOTES, 'UTF-8'); }
function sanitize_text_field($t){ return trim(preg_replace('/[\r\n\t ]+/', ' ', strip_tags((string)$t))); }
function sanitize_textarea_field($t){ return trim(preg_replace('/[ \t]+/', ' ', strip_tags(str_replace("\r", '', (string)$t)))); }
function sanitize_key($k){ return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string)$k)); }
function sanitize_title($t){ return preg_replace('/[^a-z0-9\-]+/', '-', strtolower(trim((string)$t))); }
function sanitize_html_class($c, $fb = ''){ $s = preg_replace('/[^A-Za-z0-9_\-]/', '', (string)$c); return ($s === '') ? $fb : $s; }
function sanitize_file_name($n){ return preg_replace('/[^A-Za-z0-9._\-]/', '', (string)$n); }
function sanitize_email($e){ return filter_var($e, FILTER_SANITIZE_EMAIL); }
function wp_kses($t, $allowed = [], $protocols = []){ return $t; }
function wp_kses_post($t){ return $t; }
function wp_strip_all_tags($t, $break = false){
	$t = strip_tags(preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', (string)$t)); //the code inside is not text
	return trim($break ? preg_replace('/[\r\n\t ]+/', ' ', $t) : $t);
}

//small pure helpers
function wp_parse_args($args, $defaults = []){
	if(is_object($args)) $args = get_object_vars($args);
	if(is_string($args)) parse_str($args, $args);
	return array_merge($defaults, (array)$args);
}
function wp_json_encode($v, $flags = 0, $depth = 512){ return json_encode($v, $flags, $depth); }
function trailingslashit($s){ return rtrim((string)$s, '/\\') . '/'; }
function untrailingslashit($s){ return rtrim((string)$s, '/\\'); }
function wp_normalize_path($p){ return str_replace('\\', '/', (string)$p); }
function absint($n){ return abs(intval($n)); }
function maybe_unserialize($d){ return is_serialized($d) ? @unserialize(trim($d)) : $d; }
function maybe_serialize($d){ return (is_array($d) || is_object($d) || is_serialized($d, false)) ? serialize($d) : $d; } //a serialized string again, or reading it back would unserialize it
function wp_unslash($v){ return is_array($v) ? array_map('wp_unslash', $v) : (is_string($v) ? stripslashes($v) : $v); }
function wp_slash($v){ return is_array($v) ? array_map('wp_slash', $v) : (is_string($v) ? addslashes($v) : $v); }
function stripslashes_deep($v){ return wp_unslash($v); }
function wp_parse_url($u, $c = -1){ return parse_url($u, $c); }
function wp_basename($p, $s = ''){ return urldecode(basename(str_replace(['%2F', '%5C'], '/', urlencode($p)), $s)); }
function set_url_scheme($url, $scheme = null){
	if($scheme === null) return $url;
	return preg_replace('#^\w+://#', $scheme . '://', (string)$url);
}

//file helpers
function wp_mkdir_p($dir){ return is_dir($dir) ? true : @mkdir($dir, 0777, true); }
function wp_delete_file($f){ @unlink($f); }
function wp_tempnam($f = '', $dir = ''){ return tempnam(($dir !== '') ? $dir : sys_get_temp_dir(), ($f !== '') ? basename($f) : 'tmp'); }
function list_files($dir, $levels = 100, $exclusions = []){ return glob(rtrim($dir, '/') . '/*') ?: []; }
function get_temp_dir(){ return trailingslashit(sys_get_temp_dir()); }
function wp_check_filetype($file, $mimes = null){
	$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
	$map = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp', 'svg' => 'image/svg+xml', 'mp4' => 'video/mp4', 'json' => 'application/json', 'zip' => 'application/zip'];
	return ['ext' => isset($map[$ext]) ? $ext : false, 'type' => $map[$ext] ?? false];
}

//value semantics
class WP_Error {
	public $errors = [];
	public $error_data = [];
	public function __construct($code = '', $message = '', $data = ''){
		if($code !== ''){ $this->errors[$code][] = $message; if($data !== '') $this->error_data[$code] = $data; }
	}
	public function get_error_code(){ $codes = array_keys($this->errors); return $codes[0] ?? ''; }
	public function get_error_message($code = ''){ if($code === '') $code = $this->get_error_code(); return $this->errors[$code][0] ?? ''; }
	public function get_error_messages($code = ''){ if($code === '') return array_merge(...array_values($this->errors) ?: [[]]); return $this->errors[$code] ?? []; }
	public function get_error_data($code = ''){ if($code === '') $code = $this->get_error_code(); return $this->error_data[$code] ?? null; }
	public function add($code, $message, $data = ''){ $this->errors[$code][] = $message; if($data !== '') $this->error_data[$code] = $data; }
}
function is_wp_error($v){ return $v instanceof WP_Error; }
function size_format($bytes, $decimals = 0){
	foreach(['TB' => 1099511627776, 'GB' => 1073741824, 'MB' => 1048576, 'KB' => 1024] as $unit => $size){
		if($bytes >= $size) return number_format($bytes / $size, $decimals) . ' ' . $unit;
	}
	return $bytes . ' B';
}
function human_time_diff($from, $to = 0){ if($to === 0) $to = time(); $d = abs($to - $from); return ($d < 3600) ? round($d / 60) . ' mins' : round($d / 3600) . ' hours'; }
function _deprecated_function($fn, $version, $replacement = ''){}
function wp_convert_hr_to_bytes($v){ $v = strtolower(trim($v)); $n = intval($v); if(strpos($v, 'g')) $n *= 1073741824; elseif(strpos($v, 'm')) $n *= 1048576; elseif(strpos($v, 'k')) $n *= 1024; return $n; }
function is_ssl(){ return strpos(shim_environment()->current_page_url(), 'https://') === 0; } //the host's request knows the scheme, proxies included
function wp_getimagesize($f){ return @getimagesize($f); }
function wp_get_image_mime($f){ $i = @getimagesize($f); return (is_array($i) && !empty($i['mime'])) ? $i['mime'] : false; }
function wp_is_writable($p){ return is_writable($p); }

//host-side request-cache (WP object cache): plain in-memory here
$GLOBALS['__shim_cache'] = [];
function wp_cache_get($key, $group = ''){ return $GLOBALS['__shim_cache'][$group][$key] ?? false; }
function wp_cache_set($key, $val, $group = '', $ttl = 0){ $GLOBALS['__shim_cache'][$group][$key] = $val; return true; }
function wp_cache_delete($key, $group = ''){ unset($GLOBALS['__shim_cache'][$group][$key]); return true; }
function wp_cache_add($key, $val, $group = '', $ttl = 0){ return isset($GLOBALS['__shim_cache'][$group][$key]) ? false : wp_cache_set($key, $val, $group, $ttl); }
function wp_cache_incr($key, $offset = 1, $group = ''){
	if(!isset($GLOBALS['__shim_cache'][$group][$key])) return false;
	$GLOBALS['__shim_cache'][$group][$key] = max(0, (int)$GLOBALS['__shim_cache'][$group][$key] + $offset); //WordPress floors at zero
	return $GLOBALS['__shim_cache'][$group][$key];
}

//transport questions the host answers; false = plain page request
function wp_doing_ajax(){ return shim_environment()->is_ajax_or_api_request(); }
function wp_is_json_request(){ return shim_environment()->is_ajax_or_api_request(); }

function selected($selected, $current = true, $display = true){
	$result = ((string)$selected === (string)$current) ? " selected='selected'" : '';
	if($display) echo $result;
	return $result;
}
function wp_cache_flush(){ $GLOBALS['__shim_cache'] = []; return true; }
function validate_file($file, $allowed = []){
	if(strpos($file, '..') !== false) return 1;
	if(strpos($file, './') !== false) return 1;
	if(!empty($allowed) && in_array($file, $allowed, true)) return 0;
	if(strpos($file, ':') !== false && preg_match('#^[a-zA-Z]:#', $file) !== 1) return 2;
	return 0;
}
function wp_rand($min = 0, $max = 0){ return ($max > $min) ? random_int($min, $max) : random_int(0, PHP_INT_MAX); }
function is_serialized($data, $strict = true){ //WordPress' test of the whole format: 'i:love you' is a plain string
	if(!is_string($data)) return false;
	$data = trim($data);
	if($data === 'N;') return true;
	if(strlen($data) < 4 || $data[1] !== ':') return false;
	if($strict && !in_array(substr($data, -1), [';', '}'], true)) return false;
	if(!$strict){
		$semicolon = strpos($data, ';');
		$brace = strpos($data, '}');
		if(($semicolon === false && $brace === false) || ($semicolon !== false && $semicolon < 3) || ($brace !== false && $brace < 4)) return false;
	}
	$token = $data[0];
	if($token === 's' && ($strict ? substr($data, -2, 1) !== '"' : strpos($data, '"') === false)) return false;
	if(in_array($token, ['s', 'a', 'O', 'E'], true)) return preg_match('/^' . $token . ':[0-9]+:/s', $data) === 1;
	if(in_array($token, ['b', 'i', 'd'], true)) return preg_match('/^' . $token . ':[0-9.E+-]+;' . ($strict ? '$' : '') . '/', $data) === 1;
	return false;
}
function is_rtl(){ return false; } //a non-WP host wires its own locale direction here
function wp_generate_uuid4(){ $b = random_bytes(16); $b[6] = chr(ord($b[6]) & 0x0f | 0x40); $b[8] = chr(ord($b[8]) & 0x3f | 0x80); return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($b), 4)); }
function wp_unique_filename($dir, $filename){
	$name = pathinfo($filename, PATHINFO_FILENAME); $ext = pathinfo($filename, PATHINFO_EXTENSION);
	$ext = ($ext !== '') ? '.'.$ext : ''; $n = 1;
	while(file_exists($dir.'/'.$filename)){ $filename = $name.'-'.(++$n).$ext; }
	return $filename;
}
function apply_filters_deprecated($tag, $args, $version, $replacement = '', $message = ''){ return apply_filters($tag, ...$args); }
function nocache_headers(){ if(!headers_sent()){ header('Cache-Control: no-cache, must-revalidate, max-age=0'); header('Expires: Wed, 11 Jan 1984 05:00:00 GMT'); } }
function wp_raise_memory_limit($context = 'admin'){ return ini_get('memory_limit'); }
function load_plugin_textdomain($domain, $deprecated = false, $rel_path = false){ return true; } //gettext is WP's mechanism, other hosts translate elsewhere
function shortcode_parse_atts($text){
	$atts = [];
	$pattern = '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|\'([^\']*)\'(?:\s|$)|(\S+)(?:\s|$)/';
	$text = preg_replace("/[\x{00a0}\x{200b}]+/u", ' ', (string)$text);
	if(preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)){
		foreach($matches as $m){
			if(!empty($m[1])) $atts[strtolower($m[1])] = stripcslashes($m[2]);
			elseif(!empty($m[3])) $atts[strtolower($m[3])] = stripcslashes($m[4]);
			elseif(!empty($m[5])) $atts[strtolower($m[5])] = stripcslashes($m[6]);
			elseif(isset($m[7]) && $m[7] !== '') $atts[] = stripcslashes($m[7]);
			elseif(isset($m[8]) && $m[8] !== '') $atts[] = stripcslashes($m[8]);
			elseif(isset($m[9])) $atts[] = stripcslashes($m[9]);
		}
	}else{
		$atts = ltrim($text);
	}
	return $atts;
}

//what the Add-Ons' neutral halves call on top of the core's list. An Add-On owns its shortcode tags;
//whether content runs them is the host's expand_content(), and this host has none
$GLOBALS['__shim_shortcodes'] = [];
function add_shortcode($tag, $cb){ $GLOBALS['__shim_shortcodes'][$tag] = $cb; }
function remove_shortcode($tag){ unset($GLOBALS['__shim_shortcodes'][$tag]); }
function shortcode_exists($tag){ return isset($GLOBALS['__shim_shortcodes'][$tag]); }
function shortcode_atts($pairs, $atts, $shortcode = ''){
	$atts = (array)$atts;
	$out = [];
	foreach($pairs as $name => $default) $out[$name] = array_key_exists($name, $atts) ? $atts[$name] : $default;
	return ($shortcode !== '') ? apply_filters('shortcode_atts_' . $shortcode, $out, $pairs, $atts, $shortcode) : $out;
}
function wp_trim_words($text, $num_words = 55, $more = null){
	$original	= $text;
	$num_words	= (int)$num_words; //a user-typed {{content:words:}} token arrives as ''
	$more		= ($more === null) ? '&hellip;' : $more;
	$words		= preg_split('/[\n\r\t ]+/', wp_strip_all_tags($text), $num_words + 1, PREG_SPLIT_NO_EMPTY);
	if(count($words) > $num_words){
		array_pop($words);
		$text = implode(' ', $words) . $more;
	}else{
		$text = implode(' ', $words);
	}
	return apply_filters('wp_trim_words', $text, $num_words, $more, $original);
}
function add_query_arg(...$args){
	$uri = is_array($args[0]) ? ($args[1] ?? false) : ($args[2] ?? false);
	$uri = (string)(($uri === false) ? ($_SERVER['REQUEST_URI'] ?? '') : $uri);
	$frag = '';
	if(($p = strpos($uri, '#')) !== false){ $frag = substr($uri, $p); $uri = substr($uri, 0, $p); }
	$protocol = '';
	foreach(['http://', 'https://'] as $scheme){
		if(stripos($uri, $scheme) === 0){ $protocol = $scheme; $uri = substr($uri, strlen($scheme)); break; }
	}
	if(strpos($uri, '?') !== false){ [$base, $query] = explode('?', $uri, 2); $base .= '?'; }
	elseif($protocol !== '' || strpos($uri, '=') === false){ $base = $uri . '?'; $query = ''; }
	else{ $base = ''; $query = $uri; } //a bare 'a=b' is the query itself
	parse_str($query, $qs);
	array_walk_recursive($qs, function(&$v){ $v = urlencode($v); }); //what the URL carried is encoded again; the new values are the caller's to encode
	foreach((is_array($args[0]) ? $args[0] : [$args[0] => $args[1] ?? null]) as $k => $v) $qs[$k] = $v;
	foreach($qs as $k => $v){ if($v === false) unset($qs[$k]); }
	$ret = preg_replace('#=(&|$)#', '$1', trim(shim_build_query($qs), '?'));
	return str_replace('?#', '#', rtrim($protocol . $base . $ret . $frag, '?'));
}
/** WordPress' build_query(): nulls dropped, false as '0', arrays as key[sub], nothing encoded */
function shim_build_query($data, $key = ''){
	$ret = [];
	foreach((array)$data as $k => $v){
		if($key !== '') $k = $key . '%5B' . $k . '%5D';
		if($v === null) continue;
		if($v === false) $v = '0';
		$ret[] = (is_array($v) || is_object($v)) ? shim_build_query($v, $k) : $k . '=' . $v;
	}
	return implode('&', $ret);
}

//current-page questions: the core asks its Environment port directly, code written for WordPress asks these
function shim_environment(){ return \ThemePunch\SR\Core\Registry::instance()->get('ThemePunch\SR\Ports\Environment'); }
function get_permalink($post = 0){ return shim_environment()->current_page_url(); }
function get_the_ID(){ return shim_environment()->current_entry_id(); }
function get_the_title($post = 0){ return ''; }
function get_bloginfo($show = ''){ return shim_environment()->site_name(); }
function home_url($path = ''){ return rtrim(shim_environment()->site_url(), '/') . (($path !== '') ? '/' . ltrim($path, '/') : ''); }
