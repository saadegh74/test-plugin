<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

/*
 * The platform-neutral bootstrap: the request state every host seeds, the ports the core
 * talks through, and the core class files. A host defines its constants (RS_PLUGIN_PATH,
 * RS_PLUGIN_URL, RS_PLUGIN_URL_CLEAN, RS_REVISION, ...) first, requires this file, then binds its platform
 * adapters and wires its own hooks - see wordpress/loader.php and standalone/host.php.
 */

if(!defined('ABSPATH')) exit();

if(!defined('RS_DEMO')) define('RS_DEMO', false);

//the product tree (admin/, public/, src/ below it): add-ons name core files from here, SR7.E.core_url in JS, and fall
//back to RS_PLUGIN_* on an older core
if(!defined('RS_CORE_PATH')) define('RS_CORE_PATH', RS_PLUGIN_PATH . 'core/');
if(!defined('RS_CORE_URL')) define('RS_CORE_URL', RS_PLUGIN_URL . 'core/');
if(!defined('RS_CORE_URL_CLEAN')) define('RS_CORE_URL_CLEAN', RS_PLUGIN_URL_CLEAN . 'core/');

//where the engine's shipped files lie for PHP to READ them: ONE folder carrying BOTH public/ and admin/assets/ and
//ending in a separator - every call site joins a relative path straight onto it. A host that publishes the two trees
//away from this PHP points the constant there and ships one copy instead of two; RS_CORE_URL stays what the browser
//loads. A value the host got wrong reads nothing and says nothing later (a missing admin/assets/ empties the admin
//sprite in silence), so it is said once here; the default cannot be wrong, so no host pays for the two is_dir()
if(!defined('RS_CORE_ASSETS_PATH')) define('RS_CORE_ASSETS_PATH', rtrim(RS_CORE_PATH, '/\\') . '/');
elseif(!preg_match('#[/\\\\]$#', RS_CORE_ASSETS_PATH) || !is_dir(RS_CORE_ASSETS_PATH . 'public') || !is_dir(RS_CORE_ASSETS_PATH . 'admin/assets'))
	error_log('Slider Revolution: RS_CORE_ASSETS_PATH must end in a separator and carry both public/ and admin/assets/, got ' . RS_CORE_ASSETS_PATH);

global $SR_GLOBALS;

$SR_GLOBALS = [
	'addon_notice_merged'	=> 0,
	'animations'			=> [],
	'collections'			=> [
		'css'	=> [],
		'ids'	=> [],
		'js'	=> ['revapi' => [], 'js' => [], 'minimal' => '', 'stream' => []], //'revapi', 'js' and 'minimal' are v6 residue nothing writes; only 'stream' is live in v7
		'trans'	=> [],
		'nav'	=> ['arrows' => [], 'thumbs' => [], 'bullets' => [], 'tabs' => [], 'scrubber' => []]
	],
	'deprecated'			=> [],
	'fonts'					=> ['queue' => [], 'loaded' => [], 'custom' => []],
	'header_js'				=> false,
	'icon_sets'				=> [
		'Materialicons' => ['css' => false, 'parsed' => false],
		'FontAwesome'	=> ['css' => false, 'parsed' => false],
		'PeIcon'		=> ['css' => false, 'parsed' => false],
		'RevIcon'		=> ['css' => false, 'parsed' => false]
	],
	'data_init'				=> true,
	'js_init'				=> false,
	'lcp_still'				=> false,
	'loaded_by_editor'		=> false,
	'preview_mode'			=> false,
	'markup_export'			=> false,
	'modules'				=> ['module','page','slide','layer','layerix','draw','animate','transitions','srtools','canvas','defaults','carousel','navigation','media','modifiers','story','bgpattern','digits','glow','surface','lightbox'],
	'save_post'				=> false,
	'serial'				=> 0,
	'sliders'				=> [],
	'v6'					=> false,
	'v6db'					=> 'unknown',
	'yt_api_loaded'			=> false,
	//file extensions that must never end up on disk through an import. deduplicated and grouped by platform -
	//the flat list before this carried 'shtml' 3x, 'jsp'/'jspx' 3x and 'asp', 'aspx', 'cgi', 'pl', 'swf',
	//'htaccess', 'phps' twice each. test against it with RevSliderFunctions::is_bad_extension(), which does an
	//O(1) hash lookup instead of walking ~70 entries for every single file inside an imported zip.
	'bad_extensions'		=> [
		//php
		'php', 'php2', 'php3', 'php4', 'php5', 'php6', 'php7', 'phps', 'pht', 'phtm', 'phtml', 'pgif', 'phar', 'hphp', 'inc', 'ctp', 'module',
		//asp / .net
		'asp', 'aspx', 'asa', 'ashx', 'asmx', 'aspq', 'axd', 'cer', 'config', 'cshtm', 'cshtml', 'rem', 'soap', 'vbhtm', 'vbhtml',
		//java / jsp
		'jsp', 'jspx', 'jspf', 'jsw', 'jsv', 'jar', 'wss', 'do', 'action',
		//coldfusion
		'cfm', 'cfml', 'cfc', 'dbm',
		//server parsed markup
		'shtml', 'xhtml', 'html', 'htm', 'yaws',
		//scripts
		'pl', 'cgi', 'sh', 'py', 'rb', 'ps1', 'psm1', 'vbs', 'js',
		//binaries
		'exe', 'dll', 'sys', 'com', 'bat', 'cmd', 'msi', 'reg', 'scr', 'pif',
		//archives (nested archives are not unpacked, so they must not be written either)
		'zip', 'rar', '7z',
		//config / plugins
		'htaccess', 'ini', 'swf'
	],
	'mime_types'			=> [
		'image'	=> ['jpg|jpeg|jpe' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'bmp' => 'image/bmp', 'webp' => 'image/webp', 'svg' => 'image/svg+xml'],
		'video'	=> ['mpeg|mpg|mpe' => 'video/mpeg', 'mp4|m4v' => 'video/mp4', 'ogv' => 'video/ogg', 'webm' => 'video/webm', 'mp3' => 'audio/mpeg']
	],
	'options' => [],
];

//every ThemePunch\SR class loads on first use out of core/src/ - the forty-line require
//chain that used to sit here died with the PSR-4 move. A host that keeps the classic
//global names calls Autoloader::aliases() with core/compat/class-map.php BEFORE this file.
require_once(RS_PLUGIN_PATH . 'core/autoload.php');
\ThemePunch\SR\Autoloader::register();
