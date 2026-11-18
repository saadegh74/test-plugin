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
 * WordPress transients behind the TtlCache port.
 * @since 7.2.1
 */
class TtlCache implements Ports\TtlCache {

	public function get($key, $default = false){
		$value = get_transient($key);

		return ($value === false) ? $default : $value;
	}

	public function set($key, $value, $ttl = 0){
		return set_transient($key, $value, $ttl);
	}

	public function delete($key){
		return delete_transient($key);
	}

	public function delete_by_prefix($prefixes){
		global $wpdb;

		$prefixes = array_filter((array)$prefixes, 'strlen');
		if(empty($prefixes)) return false;

		//esc_like() escapes the underscores in the transient prefix so they are literals and not LIKE
		//wildcards; value and timeout rows drop in one statement each, regardless of prefix count
		$conditions	= [];
		$args_main	= [];
		$args_time	= [];
		foreach($prefixes as $prefix){
			$conditions[]	= '`option_name` LIKE %s';
			$args_main[]	= $wpdb->esc_like('_transient_' . $prefix) . '%';
			$args_time[]	= $wpdb->esc_like('_transient_timeout_' . $prefix) . '%';
		}

		$where	= implode(' OR ', $conditions);
		$return	= $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE {$where}", $args_main));
		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE {$where}", $args_time));

		return $return;
	}

	/**
	 * One statement, so callers that arrive together each add their own event: the window is part of the row's
	 * name, which is what lets INSERT ... ON DUPLICATE KEY UPDATE count without reading first, and
	 * LAST_INSERT_ID() carries the new value back out of the same statement. option_name is unique in WordPress,
	 * so the row is the lock. The rows of a window that has passed go when the next one opens.
	 * @since 7.2.1
	 */
	public function count_in_window($key, $window){
		global $wpdb;

		$window	= max(1, (int)$window);
		$start	= intdiv(time(), $window) * $window;
		$name	= '_transient_' . (string)$key . '_' . $start;

		$done = $wpdb->query($wpdb->prepare(
			"INSERT INTO {$wpdb->options} (`option_name`, `option_value`, `autoload`) VALUES (%s, LAST_INSERT_ID(1), 'no')"
			. ' ON DUPLICATE KEY UPDATE `option_value` = LAST_INSERT_ID(`option_value` + 1)',
			$name
		));
		if($done === false) return false;

		//one row touched is the row this statement added, the window's first event: a table with an
		//AUTO_INCREMENT column hands LAST_INSERT_ID() the new row's id, not the value written into it
		$count = ((int)$done === 1) ? 1 : (int)$wpdb->get_var('SELECT LAST_INSERT_ID()');
		if($count === 1){ //the window just opened: it expires like any transient, and the one before it goes now
			$timeout = '_transient_timeout_' . (string)$key . '_' . $start;
			$wpdb->query($wpdb->prepare(
				"INSERT INTO {$wpdb->options} (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, 'no')"
				. ' ON DUPLICATE KEY UPDATE `option_value` = VALUES(`option_value`)',
				$timeout, (string)($start + $window)
			));
			$wpdb->query($wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE (`option_name` LIKE %s OR `option_name` LIKE %s) AND `option_name` NOT IN (%s, %s)",
				$wpdb->esc_like('_transient_' . (string)$key . '_') . '%',
				$wpdb->esc_like('_transient_timeout_' . (string)$key . '_') . '%',
				$name, $timeout
			));
		}

		return ['count' => max(1, $count), 'start' => $start];
	}
}
