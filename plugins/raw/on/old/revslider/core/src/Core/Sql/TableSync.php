<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Core\Sql;

use ThemePunch\SR\Ports\Database;

if(!defined('ABSPATH')) exit();

/**
 * WordPress' dbDelta for one CREATE TABLE statement, spoken through the Database port, so a host without
 * WordPress brings its tables to Core\Schema the same way: same comparisons, same ALTER statements, same
 * messages under the same keys. Like dbDelta it never drops a column or an index, never shrinks a text or
 * blob column, treats an index as present whatever its prefix lengths, and on MySQL 8.0.17+ ignores a
 * difference in integer display width only.
 *
 * Left out of dbDelta: several statements in one call, INSERT, UPDATE and CREATE DATABASE statements, the
 * dbdelta_* filters, the $execute = false dry run, the multisite global-table guard and the keywords that
 * read wp_get_db_schema(). A TEMPORARY table of the same name is not seen, the base table is created beside it.
 * Parsed as dbDelta parses, save where dbDelta breaks on valid input: a ';' inside a quoted literal, a column
 * name with regex characters (price$). An index line dbDelta's index regex rejects (a blank line, USING BTREE
 * after the columns) is skipped without a message, dbDelta turns it into a broken ADD.
 * @since 7.2.1
 */
class TableSync {

	const TEXT_TYPES = ['tinytext', 'text', 'mediumtext', 'longtext'];
	const BLOB_TYPES = ['tinyblob', 'blob', 'mediumblob', 'longblob'];
	const INT_TYPES	 = ['tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint'];

	/**
	 * bring one table to a CREATE TABLE definition
	 * @since 7.2.1
	 * @param Database $db
	 * @param string   $sql one CREATE TABLE statement, a trailing ';' is allowed
	 * @return array dbDelta's return value: 'Created table' under the table name, column changes under table.column, added indexes numbered
	 */
	public static function sync(Database $db, $sql){
		$sql = rtrim(trim($sql), ';');
		if(!preg_match('|CREATE TABLE ([^ ]*)|', $sql, $matches)) return [];

		$table = trim($matches[1], '`');
		//dbDelta suppresses the error DESCRIBE raises on a missing table, the port cannot, so ask first
		$name	= explode('.', $table, 2);
		$from	= (isset($name[1])) ? " FROM {$name[0]}" : '';
		$exists = ($db->get_var($db->prepare("SHOW TABLES{$from} LIKE %s", $db->esc_like(end($name)))) !== null);
		$fields = ($exists) ? $db->get_results("DESCRIBE {$table}") : [];
		if(empty($fields)){
			$db->query($sql);
			return [$table => 'Created table ' . $matches[1]];
		}

		list($cfields, $indices, $indices_bare) = self::parse($sql);
		$queries  = [];
		$messages = [];
		$server	  = null;

		foreach($fields as $field){
			$name = strtolower($field->Field);
			if(!isset($cfields[$name])) continue;

			$def = $cfields[$name];
			preg_match('|`?' . preg_quote($field->Field, '|') . '`? ([^ ]*( unsigned)?)|i', $def, $m);
			$wanted = $m[1] ?? '';

			if($field->Type !== strtolower($wanted) && self::type_differs(strtolower($wanted), strtolower($field->Type), $db, $server)){
				$queries[] = "ALTER TABLE {$table} CHANGE COLUMN `{$field->Field}` " . $def;
				$messages[$table . '.' . $field->Field] = "Changed type of {$table}.{$field->Field} from {$field->Type} to {$wanted}";
			}

			//only a quoted default is compared, DEFAULT 0 and DEFAULT NULL are left alone; a type message of the same column is overwritten
			if(preg_match("| DEFAULT '(.*?)'|i", $def, $m) && $field->Default !== $m[1]){
				$queries[] = "ALTER TABLE {$table} ALTER COLUMN `{$field->Field}` SET DEFAULT '{$m[1]}'";
				$messages[$table . '.' . $field->Field] = "Changed default value of {$table}.{$field->Field} from {$field->Default} to {$m[1]}";
			}

			unset($cfields[$name]);
		}

		foreach($cfields as $name => $def){
			$queries[] = "ALTER TABLE {$table} ADD COLUMN $def";
			$messages[$table . '.' . $name] = 'Added column ' . $table . '.' . $name;
		}

		foreach(self::table_indices($db, $table) as $index){
			$found = array_search($index, $indices_bare, true);
			if($found !== false) unset($indices_bare[$found], $indices[$found]);
		}

		foreach($indices as $index){
			$queries[]	= "ALTER TABLE {$table} ADD $index";
			$messages[] = 'Added index ' . $table . ' ' . $index;
		}

		foreach($queries as $query) $db->query($query);

		return $messages;
	}

	/**
	 * split the lines between the outer parentheses into column definitions and normalised index definitions
	 * @since 7.2.1
	 * @param string $sql
	 * @return array [column name => definition, indexes with prefix lengths, the same indexes without]
	 */
	private static function parse($sql){
		$cfields = [];
		$indices = [];
		$bare	 = [];
		preg_match('|\((.*)\)|ms', $sql, $body);

		foreach(explode("\n", trim($body[1])) as $fld){
			$fld = trim($fld, " \t\n\r\0\x0B,");
			preg_match('|^([^ ]*)|', $fld, $fvals);
			$name = strtolower(trim($fvals[1], '`'));

			if(!in_array($name, ['', 'primary', 'index', 'fulltext', 'unique', 'key', 'spatial'], true)){
				$cfields[$name] = $fld;
				continue;
			}
			if(!preg_match('/^(?P<index_type>PRIMARY\s+KEY|(?:UNIQUE|FULLTEXT|SPATIAL)\s+(?:KEY|INDEX)|KEY|INDEX)\s+(?:`?(?P<index_name>(?:[0-9a-zA-Z$_-]|[\xC2-\xDF][\x80-\xBF])+)`?\s+)*\((?P<index_columns>.+?)\)$/im', $fld, $im)) continue;

			$type = str_replace('INDEX', 'KEY', strtoupper(preg_replace('/\s+/', ' ', trim($im['index_type']))));
			$key  = ('PRIMARY KEY' === $type) ? '' : '`' . strtolower($im['index_name']) . '`';
			$cols = [];
			$cols_bare = [];
			foreach(array_map('trim', explode(',', $im['index_columns'])) as $column){
				preg_match('/`?(?P<column_name>(?:[0-9a-zA-Z$_-]|[\xC2-\xDF][\x80-\xBF])+)`?(?:\s*\(\s*(?P<sub_part>\d+)\s*\))?/', $column, $cm);
				$cols_bare[] = '`' . $cm['column_name'] . '`';
				$cols[]		 = '`' . $cm['column_name'] . '`' . (isset($cm['sub_part']) ? '(' . $cm['sub_part'] . ')' : '');
			}
			$indices[] = "{$type} {$key} (" . implode(',', $cols) . ')';
			$bare[]	   = "{$type} {$key} (" . implode(',', $cols_bare) . ')';
		}

		return [$cfields, $indices, $bare];
	}

	/**
	 * does a column whose type string differs need a CHANGE COLUMN
	 * @since 7.2.1
	 * @param string      $wanted type from the statement, lowercased
	 * @param string      $have   type the table reports, lowercased
	 * @param Database    $db
	 * @param string|null $server VERSION(), fetched on first need
	 * @return bool
	 */
	private static function type_differs($wanted, $have, Database $db, &$server){
		foreach([self::TEXT_TYPES, self::BLOB_TYPES] as $family){
			if(in_array($wanted, $family, true) && in_array($have, $family, true) && array_search($wanted, $family, true) < array_search($have, $family, true)) return false;
		}

		$wanted_bare = preg_replace('/(.+)\(\d*\)(.*)/', '$1$2', $wanted);
		$have_bare	 = preg_replace('/(.+)\(\d*\)(.*)/', '$1$2', $have);
		if($wanted_bare !== $have_bare || !in_array(strtok($wanted_bare, ' '), self::INT_TYPES, true) || !in_array(strtok($have_bare, ' '), self::INT_TYPES, true)) return true;

		//dbDelta reads wpdb::db_server_info(), VERSION() carries the same number and the MariaDB mark
		$server ??= (string)$db->get_var('SELECT VERSION()');

		return !(version_compare(preg_replace('/[^0-9.].*/', '', $server), '8.0.17', '>=') && strpos($server, 'MariaDB') === false);
	}

	/**
	 * the table's indexes in the form parse() writes them without prefix lengths
	 * @since 7.2.1
	 * @param Database $db
	 * @param string   $table
	 * @return array
	 */
	private static function table_indices(Database $db, $table){
		$keys = [];
		foreach((array)$db->get_results("SHOW INDEX FROM {$table}") as $row){
			$name = strtolower($row->Key_name);
			$keys[$name]['columns'][] = '`' . $row->Column_name . '`';
			$keys[$name]['unique']	  = ('0' === (string)$row->Non_unique);
			$keys[$name]['type']	  = strtoupper($row->Index_type);
		}

		$indices = [];
		foreach($keys as $name => $key){
			$index = ('primary' === $name) ? 'PRIMARY ' : (($key['unique']) ? 'UNIQUE ' : '');
			if(in_array($key['type'], ['FULLTEXT', 'SPATIAL'], true)) $index .= $key['type'] . ' ';
			$indices[] = $index . 'KEY ' . (('primary' !== $name) ? '`' . $name . '`' : '') . ' (' . implode(',', $key['columns']) . ')';
		}

		return $indices;
	}
}
