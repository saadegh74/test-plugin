<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Core;

if(!defined('ABSPATH')) exit();

/**
 * The plugin's own tables: their definitions, which of them exist, and bringing a database up to them.
 *
 * Every host keeps the same tables. How a CREATE TABLE statement is applied is the Database port's part
 * (WordPress runs dbDelta), when it runs is the host's (WordPress: activation and admin_init). The stored
 * table versions live in sr-options like every other setting.
 * @since 7.2.1
 */
class Schema {

	//deliberately NOT bumped for the charset/index/alias improvements in create_tables(): create_tables() only
	//runs while the stored table version is lower than this one, so leaving it at 1.0.17 means installations
	//that are already on 1.0.17 never touch their schema again. they have been running fine for years and an
	//ALTER TABLE on a live LONGTEXT table is not worth the risk. New installs start at 1.0.0 and therefore get
	//the improved definitions right away.
	const CURRENT_TABLE_VERSION	 = '1.0.17';
	//the slots table has its own version so adding it never re-runs the legacy CREATE statements above
	const SLOTS_TABLE_VERSION	 = '1.0.0';

	/**
	 * the V7 tables this plugin owns (unprefixed)
	 * @return array
	 **/
	public static function get_own_tables(){
		return [
			Data::TABLE_SLIDER,
			Data::TABLE_SLIDES,
			Data::TABLE_LAYER_ANIMATIONS,
			Data::TABLE_NAVIGATIONS,
			Data::TABLE_SLIDER_PREVIEW,
			Data::TABLE_SLIDES_PREVIEW,
		];
	}

	/**
	 * Check V7 Tables
	 * @return array - array of tables that are missing
	 **/
	public static function check_tables(){
		$db		= self::functions()->database();
		$return = [];

		foreach(self::get_own_tables() as $table){
			$table_name = $db->prefix() . $table;
			if($db->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) $return[] = $table;
		}

		return $return;
	}

	/**
	 * Create V7 Tables, while the stored table versions are behind this schema
	 * @return void
	 **/
	public static function create_tables(){
		$f	= self::functions();
		$db	= $f->database();
		$table_version = $f->get_options(['system', 'table'], '1.0.0');

		if(version_compare($table_version, self::CURRENT_TABLE_VERSION, '<')){
			//without this the tables inherit the MySQL *server* default (often latin1 or utf8mb3) instead of the
			//charset the site actually runs on. that silently mangles emoji/CJK in titles and params, and makes
			//every JOIN against wp_posts fail with "Illegal mix of collations". this only affects tables that
			//get *created* here - dbDelta never changes the charset of an existing table, and converting one
			//after the fact is intentionally out of scope (see CURRENT_TABLE_VERSION).
			$charset_collate = $db->charset_collate();

			//`alias` is the column every front-end shortcode lookup goes through, so a real VARCHAR(191) with a
			//plain index beats a TEXT column with a prefix index. only for tables that do not exist yet though:
			//on an existing table this would be an ALTER that could truncate aliases longer than 191 chars.
			list($alias_def, $alias_idx)				 = self::alias_column_definition(Data::TABLE_SLIDER);
			list($alias_def_preview, $alias_idx_preview) = self::alias_column_definition(Data::TABLE_SLIDER_PREVIEW);

			//V7 tables
			$sql = "CREATE TABLE " . $db->prefix() . Data::TABLE_SLIDER . " (
			  id int(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			  title tinytext NOT NULL,
			  $alias_def,
			  params LONGTEXT NOT NULL,
			  settings text NULL,
			  type VARCHAR(191) NOT NULL DEFAULT '',
			  INDEX `type_index` (`type`(8)),
			  $alias_idx
			) $charset_collate;";
			$db->sync_table($sql);

			$sql = "CREATE TABLE " . $db->prefix() . Data::TABLE_SLIDES . " (
			  id int(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			  slider_id int NOT NULL,
			  slide_order int not NULL,
			  params LONGTEXT NOT NULL,
			  layers LONGTEXT NOT NULL,
			  settings text NULL,
			  static VARCHAR(191) NOT NULL DEFAULT '',
			  INDEX `slider_id_index` (`slider_id`),
			  INDEX `idx_order_static_slider` (`slide_order`, `static`, `slider_id`)
			) $charset_collate;";
			$db->sync_table($sql);

			$sql = "CREATE TABLE " . $db->prefix() . Data::TABLE_LAYER_ANIMATIONS . " (
			  id int(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			  handle TEXT NOT NULL,
			  params TEXT NOT NULL,
			  settings text NULL
			) $charset_collate;";
			$db->sync_table($sql);

			//`handle` is looked up on its own (RevSliderNavigation::get_navigation_by_handle) and together with
			//`type` (import). a composite index on (handle, type) serves both through the leftmost-prefix rule.
			$sql = "CREATE TABLE " . $db->prefix() . Data::TABLE_NAVIGATIONS . " (
			  id int(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			  name VARCHAR(191) NOT NULL,
			  handle VARCHAR(191) NOT NULL,
			  type VARCHAR(191) NOT NULL,
			  css LONGTEXT NOT NULL,
			  markup LONGTEXT NOT NULL,
			  settings LONGTEXT NULL,
			  INDEX `handle_type_index` (`handle`, `type`)
			) $charset_collate;";
			$db->sync_table($sql);

			$sql = "CREATE TABLE " . $db->prefix() . Data::TABLE_SLIDER_PREVIEW . " (
			  id int(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			  title tinytext NOT NULL,
			  $alias_def_preview,
			  params LONGTEXT NOT NULL,
			  settings text NULL,
			  type VARCHAR(191) NOT NULL DEFAULT '',
			  INDEX `type_index` (`type`(8)),
			  $alias_idx_preview
			) $charset_collate;";
			$db->sync_table($sql);

			$sql = "CREATE TABLE " . $db->prefix() . Data::TABLE_SLIDES_PREVIEW . " (
			  id int(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			  slider_id int NOT NULL,
			  slide_order int not NULL,
			  params LONGTEXT NOT NULL,
			  layers LONGTEXT NOT NULL,
			  settings text NULL,
			  static VARCHAR(191) NOT NULL DEFAULT '',
			  INDEX `slider_id_preview_index` (`slider_id`),
			  INDEX `idx_order_preview_static_slider` (`slide_order`, `static`, `slider_id`)
			) $charset_collate;";
			$db->sync_table($sql);

			$f->update_option(['system', 'table'], self::CURRENT_TABLE_VERSION);
		}

		if(version_compare($f->get_options(['system', 'table_slots'], '0'), self::SLOTS_TABLE_VERSION, '<')){
			$charset_collate = $db->charset_collate();

			//one row per tagged layer. template_uid = the store uid a template copy was installed from (rows are
			//shared by every copy), slider_id only for modules built on the site. (slide_index, lkey) locates the
			//layer: an import renumbers slide ids, not the order and not the layer keys.
			$sql = "CREATE TABLE " . $db->prefix() . Data::TABLE_SLOTS . " (
			  id int(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			  template_uid VARCHAR(32) NOT NULL DEFAULT '',
			  slider_id int NOT NULL DEFAULT 0,
			  slide_index int NOT NULL DEFAULT 0,
			  lkey VARCHAR(64) NOT NULL DEFAULT '',
			  uid VARCHAR(16) NOT NULL DEFAULT '',
			  match_key VARCHAR(64) NOT NULL DEFAULT '',
			  kind VARCHAR(16) NOT NULL DEFAULT '',
			  role VARCHAR(32) NOT NULL DEFAULT '',
			  weight VARCHAR(16) NOT NULL DEFAULT '',
			  grp VARCHAR(64) NOT NULL DEFAULT '',
			  params LONGTEXT NOT NULL,
			  updated datetime NULL,
			  updated_by bigint NOT NULL DEFAULT 0,
			  UNIQUE KEY `slot_key` (`template_uid`, `slider_id`, `slide_index`, `lkey`),
			  INDEX `template_uid_index` (`template_uid`)
			) $charset_collate;";
			$db->sync_table($sql);

			$f->update_option(['system', 'table_slots'], self::SLOTS_TABLE_VERSION);
		}
	}

	/**
	 * column definition + index for the `alias` column of a slider table.
	 *
	 * a table that does not exist yet is created with a real VARCHAR(191) and a plain index. an existing table
	 * keeps the legacy TEXT column and its prefix index: changing it would make dbDelta emit an ALTER TABLE,
	 * and aliases longer than 191 characters would be silently truncated by it. Installs that have been running
	 * for years are not worth that risk - the improvement applies to new installations.
	 *
	 * @param string $table unprefixed table name
	 * @return array [column definition, index definition]
	 **/
	private static function alias_column_definition($table){
		$db		= self::functions()->database();
		$name	= $db->prefix() . $table;
		$exists	= ($db->get_var($db->prepare('SHOW TABLES LIKE %s', $name)) === $name);

		return ($exists)
			? ['alias tinytext', 'INDEX `alias_index` (`alias`(191))']
			: ['alias varchar(191) DEFAULT NULL', 'INDEX `alias_index` (`alias`)'];
	}

	/** @return Functions the shared instance that carries the ports and the options */
	private static function functions(){
		return Registry::instance()->get('RevSliderFunctions');
	}
}
