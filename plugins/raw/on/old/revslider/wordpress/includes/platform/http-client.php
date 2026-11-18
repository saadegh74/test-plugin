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
 * WordPress' HTTP API behind the HttpClient port. ->raw keeps the untouched
 * array|WP_Error so frozen public APIs return exactly what they always did.
 * @since 7.2.1
 */
class HttpClient implements Ports\HttpClient {

	public function get($url, $args = []){
		$request = [
			'user-agent' => $args['user_agent'] ?? $this->default_user_agent(),
			'timeout'	 => $args['timeout'] ?? 45
		];
		if(isset($args['max_redirects'])) $request['redirection'] = (int)$args['max_redirects'];
		if(isset($args['max_bytes'])) $request['limit_response_size'] = (int)$args['max_bytes'];

		return $this->result(wp_safe_remote_get($url, $request));
	}

	public function head($url, $args = []){
		$response = wp_safe_remote_head($url, [
			'user-agent' => $args['user_agent'] ?? $this->default_user_agent(),
			'timeout'	 => $args['timeout'] ?? 45
		]);

		return $this->result($response);
	}

	public function post($url, $fields = [], $args = []){
		$response = wp_safe_remote_post($url, [
			'user-agent' => $args['user_agent'] ?? $this->default_user_agent(),
			'body'		 => $fields,
			'timeout'	 => $args['timeout'] ?? 45
		]);

		return $this->result($response);
	}

	public function download($url, $args = []){
		if(!function_exists('download_url')) require_once ABSPATH . 'wp-admin/includes/file.php';

		$tmp	= download_url($url, $args['timeout'] ?? 45);
		$result	= $this->result($tmp);
		if($result->ok === true){
			$result->code = 200; //download_url only hands back a path on HTTP 200
			$result->file = $tmp;
		}

		return $result;
	}

	public function post_to_file($url, $fields, $file, $args = []){
		$response = wp_safe_remote_post($url, [
			'user-agent' => $args['user_agent'] ?? $this->default_user_agent(),
			'body'		 => $fields,
			'timeout'	 => $args['timeout'] ?? 45,
			'stream'	 => true,
			'filename'	 => $file
		]);

		$result = $this->result($response);
		if($result->ok === true) $result->file = $file;

		return $result;
	}

	public function post_body($url, $body, $args = []){
		$request = [
			'timeout' => $args['timeout'] ?? 45,
			'headers' => $args['headers'] ?? [],
			'body'	  => $body,
		];
		if(isset($args['max_redirects'])) $request['redirection'] = (int)$args['max_redirects'];
		//wp_remote_post with WordPress' own defaults, as the Monday calls always went out; the caller fixes the URL
		if(empty($args['safe'])) return $this->result(wp_remote_post($url, $request));

		if(wp_http_validate_url($url) === false) return $this->result(new \WP_Error('http_request_failed', __('A valid URL was not provided.', 'revslider')));

		return $this->result(wp_safe_remote_post($url, $request));
	}

	/** @return string the user agent WordPress traditionally sends for plugin calls */
	protected function default_user_agent(){
		global $wp_version;

		return 'WordPress/' . $wp_version . '; ' . get_bloginfo('url');
	}

	/**
	 * @param mixed $response array|WP_Error from the WP HTTP API, or string|WP_Error from download_url()
	 * @return Ports\HttpResult
	 */
	protected function result($response){
		$result		 = new Ports\HttpResult();
		$result->raw = $response;

		if(is_wp_error($response)){
			$result->error_code	= $response->get_error_code();
			$result->error		= $response->get_error_message();
			return $result;
		}

		$result->ok = true;
		if(is_array($response)){
			$result->code = (int)wp_remote_retrieve_response_code($response);
			$result->body = (string)wp_remote_retrieve_body($response);
			foreach(wp_remote_retrieve_headers($response) as $name => $value){
				$result->headers[strtolower($name)] = is_array($value) ? implode(', ', $value) : $value;
			}
		}

		return $result;
	}
}
