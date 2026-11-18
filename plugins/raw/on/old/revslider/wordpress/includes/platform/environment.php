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
 * WordPress' user capabilities and request context behind the Environment port.
 * @since 7.2.1
 */
class Environment implements Ports\Environment {

	public function user_can($capability, $object_id = null){
		//pluggable.php may not be loaded yet on very early calls (loadbalancer refresh); no capability then
		if(!function_exists('current_user_can')) return false;
		return ($object_id === null) ? current_user_can($capability) : current_user_can($capability, $object_id);
	}

	public function is_admin_area(){
		return is_admin();
	}

	public function is_multisite(){
		return is_multisite();
	}

	public function format_date($timestamp, $with_time = false){
		$format = get_option('date_format');
		if($with_time) $format .= ' ' . get_option('time_format');
		return date_i18n($format, $timestamp);
	}

	public function local_timestamp(){
		return current_time('timestamp');
	}

	public function extension_fingerprint(){
		return md5(json_encode(get_option('active_plugins', [])));
	}

	public function csrf_token($scope){
		return wp_create_nonce($scope);
	}

	public function ajax_url(){
		return admin_url('admin-ajax.php');
	}

	public function editor_url($module_id){
		return admin_url('admin.php?page=revslider&view=editor&module=' . (int)$module_id);
	}

	//revslider_client_ip: a site behind a proxy or CDN names the visitor here, for every limiter of the core and its add-ons
	public function client_address(){
		return (string)apply_filters('revslider_client_ip', isset($_SERVER['REMOTE_ADDR']) ? (string)$_SERVER['REMOTE_ADDR'] : '');
	}

	public function rest_url(){
		return get_rest_url();
	}

	public function is_mobile_request(){
		return wp_is_mobile();
	}

	public function site_url(){
		return home_url('/');
	}

	public function site_name(){
		return get_bloginfo('name');
	}

	public function current_page_url(){
		$permalink = get_permalink(); //no caching, this follows the loop and can change per call
		return ($permalink === false) ? '' : $permalink;
	}

	public function verify_csrf($token, $scope){
		return (bool)wp_verify_nonce($token, $scope);
	}

	public function expand_content($text){
		return do_shortcode($text);
	}

	public function site_host_list(){
		$sites = [(object)['id' => get_current_blog_id()]];
		if(is_multisite()) $sites = get_sites();

		$hosts = [];
		foreach($sites as $site){
			$hosts[] = parse_url(get_site_url($site->id), PHP_URL_HOST);
			$hosts[] = parse_url(get_admin_url($site->id), PHP_URL_HOST);
		}

		return array_unique($hosts);
	}

	public function locale(){
		return get_locale();
	}

	public function current_page_id(){
		if(is_front_page() == true || is_home() == true) return 'homepage';

		global $post;
		return (isset($post->ID)) ? $post->ID : '';
	}

	public function current_entry_id(){
		global $post;

		return isset($post->ID) ? (int)$post->ID : 0;
	}

	public function weekdays(){
		global $wp_locale;
		$days = [];
		for($d = 0; $d < 7; $d++){
			$name = $wp_locale->get_weekday($d);
			$days[$d] = ['name' => $name, 'abbrev' => $wp_locale->get_weekday_abbrev($name)];
		}

		return $days;
	}

	public function is_ajax_or_api_request(){
		if(wp_doing_ajax() || wp_is_json_request()) return true;
		if(defined('REST_REQUEST') && REST_REQUEST) return true;

		//asked before WordPress routed the request (wp_loaded): the REST URL itself is the answer
		if(isset($_GET['rest_route'])) return true;
		$uri = isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : '';
		return $uri !== '' && strpos($uri, '/' . rest_get_url_prefix() . '/') !== false;
	}

	public function max_upload_size(){
		return wp_max_upload_size();
	}

	public function extension_active($file){
		if(!function_exists('is_plugin_active')) require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		return is_plugin_active($file);
	}

	public function content_type_exists($type){
		return post_type_exists($type);
	}

	public function user_display_name($user_id = 0){
		if((int)$user_id <= 0) return wp_get_current_user()->display_name;
		$user = get_userdata((int)$user_id);

		return $user ? $user->display_name : '';
	}

	public function user_id(){
		//pluggable.php may not be loaded yet on very early calls, like user_can()
		return function_exists('get_current_user_id') ? (int)get_current_user_id() : 0;
	}

	public function user_value($key, $default = ''){
		$uid = $this->user_id();
		if($uid === 0) return $default;
		$value = get_user_meta($uid, $key, true);

		return ($value === '') ? $default : $value;
	}

	public function set_user_value($key, $value){
		$uid = $this->user_id();

		return ($uid === 0) ? false : (update_user_meta($uid, $key, $value) !== false);
	}

	public function redirect($url){
		wp_safe_redirect($url);
		exit();
	}

	public function is_block_editor_request(){
		if(isset($_GET['action']) && $_GET['action'] == 'elementor') return false; // Elementor Page Edit
		if(isset($_GET['vc_action']) && $_GET['vc_action'] == 'vc_inline') return false; // WP Bakery Front Edit
		if(function_exists('is_gutenberg_page') && is_gutenberg_page()) return true; // Gutenberg Edit with WP < 5
		if(function_exists('get_current_screen')){
			$current_screen = get_current_screen();
			if(!empty($current_screen) && method_exists($current_screen, 'is_block_editor') && $current_screen->is_block_editor()) return true; //Gutenberg Edit with WP >= 5
		}
		return false;
	}
}
