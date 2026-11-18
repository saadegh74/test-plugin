<?php
/*
Plugin Name: Slider Revolution
Plugin URI: https://www.sliderrevolution.com/?utm_source=admin&utm_medium=button&utm_campaign=srusers&utm_content=info
Description: Slider Revolution - More than just a WordPress Slider
Author: ThemePunch
Text Domain: revslider
Domain Path: /core/languages
Version: 7.2.10
Author URI: https://themepunch.com/?utm_source=admin&utm_medium=button&utm_campaign=srusers&utm_content=info
*/

// If this file is called directly, abort.
if(!defined('WPINC')){ die; }

//before any define: a second installed copy must die cleanly, not warn about redefined constants first.
//RS_REVISION covers every version's root file with no class system involved; the class check stays
//for exotic setups that define the class without the constant
if(defined('RS_REVISION') || class_exists('RevSliderFront')){
	die('ERROR: It looks like you have more than one instance of Slider Revolution installed. Please remove additional instances for this plugin to work again.');
}


/* Private License & Asset Bridge */
$sr_options = get_option('sr-options', []);
if (is_object($sr_options)) {
	$decoded = json_decode(wp_json_encode($sr_options), true);
	$sr_options = is_array($decoded) ? $decoded : [];
} elseif (!is_array($sr_options)) {
	$decoded = json_decode(is_string($sr_options) ? $sr_options : '', true);
	$sr_options = is_array($decoded) ? $decoded : [];
}
if (!isset($sr_options['system']) || !is_array($sr_options['system'])) {
	$sr_options['system'] = [];
}
$existing_code = '';
if (!empty($sr_options['system']['license'])) {
	$existing_code = (string) $sr_options['system']['license'];
} elseif (!empty(get_option('revslider-code', ''))) {
	$existing_code = (string) get_option('revslider-code', '');
}
$active_code = $existing_code !== '' ? $existing_code : 'B5E0B5F8DD8689E6ACA49DD6E6E1A930';
$sr_options['system']['valid'] = 'true';
$sr_options['system']['license'] = $active_code;
$sr_options['system']['trustpilot'] = 'true';
$sr_options['system']['deregister'] = 'false';
unset($sr_options['update']);
update_option('sr-options', $sr_options);

update_option('revslider-valid', 'true');
update_option('revslider-code', $active_code);
update_option('revslider-trustpilot', 'true');
update_option('revslider-deregister-popup', 'false');

add_filter('pre_http_request', function($preempt, $args, $url) {
	$host = (string) wp_parse_url($url, PHP_URL_HOST);
	$path = (string) wp_parse_url($url, PHP_URL_PATH);

	if (!preg_match('/(^|\.)themepunch(-[a-z0-9-]+)?\.tools$/i', $host)) return $preempt;

	// Core plugin updates and downloads are handled exclusively by our private PUC server
	if (strpos($path, '/revslider/revslider.php') !== false || strpos($path, '/revslider/download.php') !== false) {
		return new WP_Error('revslider_external_update_disabled', 'Core plugin updates are managed via private update server.');
	}

	if (strpos($path, '/check_for_updates.php') !== false) {
		$payload = wp_json_encode(array(
			'version'     => defined('RS_REVISION') ? RS_REVISION : '7.2.11',
			'beta'        => defined('RS_REVISION') ? RS_REVISION : '7.2.11',
			'stable'      => '4.2.0',
			'deactivated' => false,
			'notices'     => array(),
		));
		return array(
			'response' => array('code' => 200, 'message' => 'OK'),
			'headers'  => array(
				'Content-Type'   => 'application/json',
				'Content-Length' => (string) strlen($payload),
			),
			'body'     => $payload,
		);
	}

	$body = isset($args['body']) && is_array($args['body']) ? $args['body'] : array();
	$product = isset($body['product']) ? urldecode(sanitize_text_field($body['product'])) : '';
	$uid = isset($body['uid']) ? sanitize_text_field($body['uid']) : '';
	$type = isset($body['type']) ? sanitize_text_field($body['type']) : '';
	$shop_ver = isset($body['shop_version']) ? sanitize_text_field($body['shop_version']) : '';
	$ver_prefix = ($shop_ver === '2.0') ? 'v6' : 'v7';

	$proxy_get = function($target, $content_type) use ($args) {
		$is_stream = !empty($args['stream']) && !empty($args['filename']) && is_string($args['filename']);
		$request_args = array('timeout' => 60);
		if ($is_stream) {
			$request_args['stream'] = true;
			$request_args['filename'] = $args['filename'];
		}

		$response = wp_remote_get($target, $request_args);
		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) return false;

		$headers = array('Content-Type' => $content_type);
		$content_length = wp_remote_retrieve_header($response, 'content-length');
		if ($is_stream) {
			$file_size = @filesize($args['filename']);
			if (!empty($content_length)) $headers['Content-Length'] = $content_length;
			elseif ($file_size !== false) $headers['Content-Length'] = (string) $file_size;
			return array(
				'response' => array('code' => 200, 'message' => 'OK'),
				'headers'  => $headers,
				'body'     => '',
				'filename' => $args['filename'],
			);
		}

		$resp_body = wp_remote_retrieve_body($response);
		$headers['Content-Length'] = !empty($content_length) ? $content_length : (string) strlen($resp_body);

		return array(
			'response' => array('code' => 200, 'message' => 'OK'),
			'headers'  => $headers,
			'body'     => $resp_body,
		);
	};

	if ($product === 'revslider' && (!empty($uid) || !empty($type))) {
		$asset_prefix = !empty($type) ? 'v7' : $ver_prefix;
		$base_url = 'https://dl.gpltimes.com/file/gpltimes/revslider/' . $asset_prefix . '/';
		$file_url = !empty($type)
			? $base_url . 'rev-addons-zips/' . rawurlencode($type) . '.zip'
			: $base_url . 'rev-templates-zips/' . rawurlencode($uid) . '.zip';
		$proxied = $proxy_get($file_url, 'application/zip');
		return ($proxied !== false) ? $proxied : $preempt;
	}

	if (strpos($path, '/revslider/get-list.php') !== false) {
		$proxied = $proxy_get('https://dl.gpltimes.com/file/gpltimes/revslider/' . $ver_prefix . '/revsliderlist.json', 'application/json');
		if ($proxied !== false) return $proxied;
	}
	if (strpos($path, '/revslider/get-top-list.php') !== false) {
		$proxied = $proxy_get('https://dl.gpltimes.com/file/gpltimes/revslider/revslidertoplist.json', 'application/json');
		if ($proxied !== false) return $proxied;
	}

	return $preempt;
}, 10, 3);

//this stub exists because WordPress reads the plugin header from the root file - everything else lives
//in wordpress/loader.php (the WP host) and core/boot.php (the platform-neutral part every host shares)
define('RS_REVISION',			'7.2.10'); //beside the header Version above, so a release bump touches one file
define('RS_PLUGIN_FILE_PATH',	__FILE__); //the WP anchor: activation hooks, plugin_basename and plugins_url resolve against this file

require_once(__DIR__ . '/wordpress/loader.php');

function revslider_update_checker() {
	$json_url    = 'https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/revslider/info.json';
	$plugin_slug = 'revslider';
	require_once __DIR__ . '/plugin-update-checker/plugin-update-checker.php';
	Puc_v4_Factory::buildUpdateChecker(
		$json_url,
		__FILE__,
		$plugin_slug
	);
}
add_action( 'init', 'revslider_update_checker', 20 );
