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
 * wpdb behind the Database port.
 * @since 7.2.1
 */
class Database implements Ports\Database {

	/** @return \wpdb */
	protected function wpdb(){
		global $wpdb;
		return $wpdb;
	}

	public function prefix(){
		return $this->wpdb()->prefix;
	}

	public function prepare($query, ...$args){
		return $this->wpdb()->prepare($query, ...$args);
	}

	public function get_row($query, $output = 'OBJECT'){
		return $this->wpdb()->get_row($query, $output);
	}

	public function get_results($query, $output = 'OBJECT'){
		return $this->wpdb()->get_results($query, $output);
	}

	public function get_var($query){
		return $this->wpdb()->get_var($query);
	}

	public function insert($table, $data, $format = null){
		return $this->wpdb()->insert($table, $data, $format);
	}

	public function insert_id(){
		return $this->wpdb()->insert_id;
	}

	public function update($table, $data, $where, $format = null, $where_format = null){
		return $this->wpdb()->update($table, $data, $where, $format, $where_format);
	}

	public function delete($table, $where, $where_format = null){
		return $this->wpdb()->delete($table, $where, $where_format);
	}

	public function query($query){
		return $this->wpdb()->query($query);
	}

	public function esc_like($text){
		return $this->wpdb()->esc_like($text);
	}

	public function last_error(){
		return $this->wpdb()->last_error;
	}

	public function charset_collate(){
		return $this->wpdb()->get_charset_collate();
	}

	public function sync_table($sql){
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		return dbDelta($sql);
	}
}
