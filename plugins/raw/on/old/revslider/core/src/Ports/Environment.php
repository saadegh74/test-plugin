<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Ports;

if(!defined('ABSPATH')) exit();

/**
 * Who is asking, and from where. Capability names stay WordPress vocabulary
 * ('edit_posts', 'manage_options'); a non-WP host maps them onto its own roles.
 * @since 7.2.1
 */
interface Environment {

	/**
	 * does the acting user hold the capability, optionally on one specific object?
	 * @param string $capability
	 * @param int|null $object_id
	 * @return bool
	 */
	public function user_can($capability, $object_id = null);

	/** @return bool is this request rendering the host's admin area, not a public page? */
	public function is_admin_area();

	/** @return bool does the host serve multiple sites from one installation? */
	public function is_multisite();

	/**
	 * format a moment the way the site owner configured; the host's format string never crosses this boundary
	 * @param int|false $timestamp unix stamp, false = now
	 * @param bool $with_time append the site's time format
	 * @return string
	 */
	public function format_date($timestamp, $with_time = false);

	/**
	 * 'now' as a unix stamp shifted by the site's UTC offset. Slide schedules compare this against
	 * wall-time strings the user typed in site-local time, so plain time() would be off by the offset.
	 * @return int
	 */
	public function local_timestamp();

	/**
	 * opaque value that changes whenever the set of active extensions changes; only ever hashed into
	 * cache keys, never parsed
	 * @return string
	 */
	public function extension_fingerprint();

	/**
	 * a token the host's endpoints demand back to accept a request from this page
	 * @param string $scope
	 * @return string
	 */
	public function csrf_token($scope);

	/** @return string URL the page posts its ajax operations to */
	public function ajax_url();

	/**
	 * the host's deep link into the module editor
	 * @param int $module_id
	 * @return string
	 * @since 7.2.1
	 */
	public function editor_url($module_id);

	/** @return string base URL of the host's REST endpoints */
	public function rest_url();

	/** @return bool does the requesting device identify as mobile? */
	public function is_mobile_request();

	/**
	 * @return string the address the request came from, '' when there is no request; only ever hashed
	 *                into a rate-limit key, never shown
	 * @since 7.2.1
	 */
	public function client_address();

	/** @return string public URL of the site's front, with trailing slash */
	public function site_url();

	/**
	 * @return string the site's display name, '' when the host has none
	 * @since 7.2.1
	 */
	public function site_name();

	/** @return string URL of the page being rendered right now; '' when there is none */
	public function current_page_url();

	/**
	 * is this token one csrf_token($scope) handed out and still fresh?
	 * @param string $token
	 * @param string $scope
	 * @return bool
	 */
	public function verify_csrf($token, $scope);

	/**
	 * run host content expansion over a text (WordPress: shortcodes); hosts without such a
	 * mechanism return the text unchanged
	 * @param string $text
	 * @return string
	 */
	public function expand_content($text);

	/**
	 * every hostname this installation serves content under (multisite: all sites)
	 * @return string[]
	 */
	public function site_host_list();

	/**
	 * @return string the host locale code (e.g. 'de_DE'), '' when the host has none
	 * @since 7.2.1
	 */
	public function locale();

	/**
	 * @return string|int 'homepage' on the front page, else the current content id ('' when none)
	 * @since 7.2.1
	 */
	public function current_page_id();

	/**
	 * the id of the entry being viewed, or 0 when the request is not about one.
	 *
	 * Deliberately not current_page_id(): that one answers the core's own question and says 'homepage'
	 * on the front page, which is a label, not an id. A caller that wants to look the entry up needs
	 * a number or nothing.
	 *
	 * @return int
	 * @since 7.2.1
	 */
	public function current_entry_id();

	/**
	 * the weekday names in the host's language, indexed the way date('w') counts: 0 = Sunday.
	 * @return array seven entries of ['name' => 'Sunday', 'abbrev' => 'Sun']
	 * @since 7.2.1
	 */
	public function weekdays();

	/**
	 * @return bool the running request is an ajax or REST/API call
	 * @since 7.2.1
	 */
	public function is_ajax_or_api_request();

	/**
	 * @return int upload size limit in bytes the host enforces
	 * @since 7.2.1
	 */
	public function max_upload_size();

	/**
	 * @param string $file extension identifier as the host names installed extensions (WordPress: plugin basename)
	 * @return bool the extension is installed and active
	 * @since 7.2.1
	 */
	public function extension_active($file);

	/**
	 * @param string $type a content type name from a package manifest
	 * @return bool the host knows this content type (WordPress: post type)
	 * @since 7.2.1
	 */
	public function content_type_exists($type);

	/**
	 * @param int $user_id 0 for the acting user
	 * @return string display name of that user, '' when the host has none
	 * @since 7.2.1
	 */
	public function user_display_name($user_id = 0);

	/**
	 * @return bool the running request renders inside the host's block/page editor (WordPress: Gutenberg)
	 * @since 7.2.1
	 */
	public function is_block_editor_request();

	/**
	 * send the browser to $url and end the request; hosts without a request to end may no-op
	 * @param string $url
	 * @return void
	 * @since 7.2.1
	 */
	public function redirect($url);

	/**
	 * @return int the acting user's id, 0 for a visitor the host does not know
	 * @since 7.2.1
	 */
	public function user_id();

	/**
	 * a value the host keeps for the acting user (WordPress: user meta), what the editor remembers per person
	 * @param string $key
	 * @param mixed  $default when the user has none, or there is no user
	 * @return mixed
	 * @since 7.2.1
	 */
	public function user_value($key, $default = '');

	/**
	 * @param string $key
	 * @param mixed  $value
	 * @return bool false when there is no user to keep it for
	 * @since 7.2.1
	 */
	public function set_user_value($key, $value);
}
