<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Ports;

if(!defined('ABSPATH')) exit();

/**
 * Outgoing HTTP behind one port: all ThemePunch server traffic (license, updates,
 * templates, object library) runs through this. The WordPress implementation wraps
 * wp_safe_remote_post/download_url; every method returns an HttpResult whose ->raw
 * carries the platform-native response for frozen public APIs.
 * @since 7.2.1
 */
interface HttpClient {

	/**
	 * POST form fields
	 * @param string $url
	 * @param array  $fields
	 * @param array  $args timeout (seconds), user_agent
	 * @return HttpResult
	 */
	public function post($url, $fields = [], $args = []);

	/**
	 * plain GET
	 * @param string $url
	 * @param array $args timeout, user_agent, max_redirects (how many to follow), max_bytes (cap on the
	 *                    body a proxy is willing to relay); a host without caps ignores them
	 * @return HttpResult
	 */
	public function get($url, $args = []);

	/**
	 * HEAD request - existence/status probe without a body
	 * @param string $url
	 * @param array $args timeout, user_agent
	 * @return HttpResult
	 */
	public function head($url, $args = []);

	/**
	 * GET a file straight to a temporary path (HttpResult->file)
	 * @param string $url
	 * @param array  $args timeout (seconds)
	 * @return HttpResult
	 */
	public function download($url, $args = []);

	/**
	 * POST form fields and stream the response body into $file
	 * @param string $url
	 * @param array  $fields
	 * @param string $file existing writable path the body is streamed into
	 * @param array  $args timeout (seconds), user_agent
	 * @return HttpResult
	 */
	public function post_to_file($url, $fields, $file, $args = []);

	/**
	 * POST a ready body (a JSON payload, say) with the headers the API asks for
	 * @param string $url
	 * @param string $body
	 * @param array  $args timeout (seconds), headers, max_redirects; safe (@since 7.2.9) for a URL a user typed: the
	 *                     wp_safe_remote_* checks (valid http(s) URL, no internal host, safe port) on every hop
	 * @return HttpResult
	 */
	public function post_body($url, $body, $args = []);
}
