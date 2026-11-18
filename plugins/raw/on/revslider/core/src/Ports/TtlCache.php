<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Ports;

if(!defined('ABSPATH')) exit();

/**
 * Expiring key-value entries (WordPress: transients). Entries may disappear
 * before their ttl runs out, callers treat every get as a cache lookup.
 * @since 7.2.1
 */
interface TtlCache {

	/**
	 * @param string $key
	 * @param mixed  $default returned when the key does not exist or expired
	 * @return mixed
	 */
	public function get($key, $default = false);

	/**
	 * @param string $key
	 * @param mixed  $value
	 * @param int    $ttl lifetime in seconds, 0 = no expiration
	 * @return bool
	 */
	public function set($key, $value, $ttl = 0);

	/**
	 * @param string $key
	 * @return bool
	 */
	public function delete($key);

	/**
	 * drop every entry whose key starts with one of the prefixes, in as few operations as the
	 * store allows (the WP options table does it in two statements regardless of count)
	 * @param string|string[] $prefixes
	 * @return int|false removed entries, false on a store error or empty input
	 */
	public function delete_by_prefix($prefixes);

	/**
	 * count one event inside a fixed window, in ONE operation.
	 *
	 * A counter read into PHP, raised and written back loses every increment that arrives while it is
	 * out of the store, which is exactly the caller a rate limit exists for. A host answers this where
	 * its store can add without reading first; where it cannot, it says so with false and the caller
	 * falls back to its own read and write.
	 *
	 * @param string $key
	 * @param int    $window seconds the window lasts; a count that is older starts a new one
	 * @return array|false ['count' => int after this event, 'start' => int when the window opened], false
	 *                     when this store cannot count in one operation
	 * @since 7.2.1
	 */
	public function count_in_window($key, $window);
}
