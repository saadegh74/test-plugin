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


/* GPLTIMES_PATCH_START */
$sr_options = get_option('sr-options', []);
if(is_object($sr_options)){
	$decoded = json_decode(wp_json_encode($sr_options), true);
	$sr_options = is_array($decoded) ? $decoded : [];
}elseif(!is_array($sr_options)){
	$decoded = json_decode(is_string($sr_options) ? $sr_options : '', true);
	$sr_options = is_array($decoded) ? $decoded : [];
}
if(!isset($sr_options['system']) || !is_array($sr_options['system'])){
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
update_option('sr-options', $sr_options);

update_option('revslider-valid', 'true');
update_option('revslider-code', $active_code);
update_option('revslider-trustpilot', 'true');
update_option('revslider-deregister-popup', 'false');

add_filter('pre_http_request', function($preempt, $args, $url) {
	$host = (string) wp_parse_url($url, PHP_URL_HOST);
	$path = (string) wp_parse_url($url, PHP_URL_PATH);

	// every ThemePunch host, also ones their server list adds later (themepunch.tools, themepunch-ext-a.tools, ...)
	if (!preg_match('/(^|\.)themepunch(-[a-z0-9-]+)?\.tools$/i', $host)) return $preempt;

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
				'headers' => $headers,
				'body' => '',
				'filename' => $args['filename']
			);
		}

		$body = wp_remote_retrieve_body($response);
		if (!empty($content_length)) $headers['Content-Length'] = $content_length;
		else $headers['Content-Length'] = (string) strlen($body);

		return array(
			'response' => array('code' => 200, 'message' => 'OK'),
			'headers' => $headers,
			'body' => $body
		);
	};

	if ($product === 'revslider' && (!empty($uid) || !empty($type))) {
		$asset_prefix = !empty($type) ? 'v7' : $ver_prefix;
		$base_url = 'https://dl.gpltimes.com/file/gpltimes/revslider/' . $asset_prefix . '/';
		$file_url = !empty($type)
			? $base_url . 'rev-addons-zips/' . rawurlencode($type) . '.zip'
			: $base_url . 'rev-templates-zips/' . rawurlencode($uid) . '.zip';
		$proxied = $proxy_get($file_url, 'application/zip');
		// not on our storage: let ThemePunch answer ("invalid", which the plugin reports). Falling through would
		// match the revslider/download.php branch below and hand back the plugin zip as the template.
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
	if (strpos($path, '/revslider/revslider.php') !== false) {
		// the update WordPress offers: our snapshot, so a version is offered only once its zip is on our storage
		$proxied = $proxy_get('https://dl.gpltimes.com/file/gpltimes/revslider/v7/revslider-info.txt', 'text/html; charset=UTF-8');
		if ($proxied !== false) return $proxied;
	}
	if (strpos($path, '/revslider/download.php') !== false) {
		$query_args = array();
		$query_string = (string) wp_parse_url($url, PHP_URL_QUERY);
		if ($query_string !== '') parse_str($query_string, $query_args);

		$requested_version = '';
		if (isset($body['version'])) {
			$requested_version = sanitize_text_field($body['version']);
		} elseif (isset($query_args['version'])) {
			$requested_version = sanitize_text_field(wp_unslash($query_args['version']));
		}

		$primary_track = (strpos($requested_version, '6.') === 0) ? 'v6' : 'v7';
		$tracks = array($primary_track, $primary_track === 'v7' ? 'v6' : 'v7');

		foreach ($tracks as $track) {
			$plugin_zip_url = 'https://dl.gpltimes.com/file/gpltimes/revslider/' . $track . '/revslider-plugin.zip';
			$proxied = $proxy_get($plugin_zip_url, 'application/zip');
			if ($proxied !== false) return $proxied;
		}
	}

	if (strpos($path, '/check_for_updates.php') !== false) {
		$query_args = array();
		$query_string = (string) wp_parse_url($url, PHP_URL_QUERY);
		if ($query_string !== '') parse_str($query_string, $query_args);

		$requested_version = '';
		if (isset($body['version'])) {
			$requested_version = sanitize_text_field($body['version']);
		} elseif (isset($query_args['version'])) {
			$requested_version = sanitize_text_field(wp_unslash($query_args['version']));
		}

		$primary_track = (strpos($requested_version, '6.') === 0) ? 'v6' : 'v7';
		$tracks = array($primary_track, $primary_track === 'v7' ? 'v6' : 'v7');

		$snapshot_name = 'check_for_updates.json';
		$content_type = 'application/json';

		foreach ($tracks as $track) {
			$snapshot_url = 'https://dl.gpltimes.com/file/gpltimes/revslider/' . $track . '/' . $snapshot_name;
			$proxied = $proxy_get($snapshot_url, $content_type);
			if ($proxied !== false) return $proxied;
		}
	}

	return $preempt;
}, 10, 3);
/* GPLTIMES_PATCH_END */

//this stub exists because WordPress reads the plugin header from the root file - everything else lives
//in wordpress/loader.php (the WP host) and core/boot.php (the platform-neutral part every host shares)
define('RS_REVISION',			'7.2.10'); //beside the header Version above, so a release bump touches one file
define('RS_PLUGIN_FILE_PATH',	__FILE__); //the WP anchor: activation hooks, plugin_basename and plugins_url resolve against this file

require_once(__DIR__ . '/wordpress/loader.php');
