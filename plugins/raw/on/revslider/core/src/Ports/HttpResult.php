<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Ports;

if(!defined('ABSPATH')) exit();

/**
 * Platform-neutral outcome of one HttpClient call.
 * @since 7.2.1
 */
class HttpResult {

	/** @var bool the transport succeeded (any HTTP status); false on a network/system error */
	public $ok = false;

	/** @var int HTTP status code, 0 when the transport failed */
	public $code = 0;

	/** @var string response body ('' for streamed downloads) */
	public $body = '';

	/** @var array response headers, keys lowercased */
	public $headers = [];

	/** @var string|null downloaded file path (download/post_to_file only) */
	public $file = null;

	/** @var string|null machine readable error code when ok is false */
	public $error_code = null;

	/** @var string|null human readable error message when ok is false */
	public $error = null;

	/** @var mixed the platform-native response (WordPress: array|WP_Error) for frozen public APIs */
	public $raw = null;

	/**
	 * @param string $name case-insensitive header name
	 * @return string '' when the header is not present
	 */
	public function header($name){
		return $this->headers[strtolower($name)] ?? '';
	}

	/**
	 * What the frozen public APIs hand out: the platform-native response, or WordPress' shape built from the
	 * fields above on a host that has none - their callers test is_wp_error() and read ['body'], and a null
	 * passed the first test as a success.
	 * @return mixed
	 */
	public function native(){
		if($this->raw !== null) return $this->raw;
		if(!$this->ok) return new \WP_Error($this->error_code ?? 'http_request_failed', (string)$this->error);

		return ['headers' => $this->headers, 'body' => $this->body, 'response' => ['code' => $this->code, 'message' => ''], 'cookies' => [], 'filename' => $this->file];
	}
}
