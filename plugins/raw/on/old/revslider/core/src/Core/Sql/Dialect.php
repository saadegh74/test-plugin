<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Core\Sql;

if(!defined('ABSPATH')) exit();

/**
 * wpdb's statement building over any SQL driver, so a host's Database port keeps wpdb's semantics:
 * prepare() and the statements wpdb::insert / update / delete send, byte for byte (WordPress 7.1.1).
 * The driver comes in as $quote: a PHP string in, a quoted SQL string literal out, quotes included
 * (PDO::quote, Doctrine DBAL Connection::quote). Anything but a plain '...' literal throws.
 * As in wpdb every % of a prepared string is hidden until the host runs the statement through unescape().
 * @since 7.2.1
 */
class Dialect {

	//sign, argnum, padding, alignment and precision a placeholder may carry, as wpdb allows them
	const FORMAT = '(?:[1-9][0-9]*[$])?[-+0-9]*(?: |0|\'.)?[-+0-9]*(?:\.[0-9]+)?';

	/**
	 * the string wpdb::prepare returns, its % hidden behind the stand-in unescape() removes, so a prepared
	 * string prepared again or put through sprintf stays inert
	 *
	 * %s %d %f %F %i, numbered (%1$s) and formatted (%5s, %.2f) placeholders, %% for a literal %, '%s' and "%s"
	 * quoted afresh, all values in one array argument. As in wpdb a numbered or formatted %s, and a %s right
	 * after a %, are escaped but not quoted, and %f is locale-unaware.
	 * Where wpdb bails this returns what wpdb returns: null for a null query, for an argument used as both %i
	 * and a value, and for one placeholder handed an array of another length; '' for fewer arguments than
	 * placeholders, unless there is a numbered one and none points past the arguments. Surplus arguments are
	 * ignored, a value that is no scalar is taken as '' (an identifier is cast as wpdb casts it), a query without
	 * placeholders only has its %% turned into %. Where vsprintf still runs out of arguments it throws
	 * ValueError, as it does in wpdb.
	 * @param callable    $quote   string => quoted SQL string literal
	 * @param string|null $query
	 * @param mixed       ...$args the values, or one array holding them all
	 * @return string|null
	 * @since 7.2.1
	 */
	public static function prepare(callable $quote, $query, ...$args){
		if($query === null) return null;

		$query = str_replace(["'%s'", '"%s"'], '%s', $query);
		$query = preg_replace('/%(?:%|$|(?!(?:' . self::FORMAT . ')?[sdfFi]))/', '%%', $query);
		$split = preg_split('/(^|[^%]|(?:%%)+)(%(?:' . self::FORMAT . ')?[sdfFi])/', $query, -1, PREG_SPLIT_DELIM_CAPTURE);
		$parts = count($split);
		$placeholders = ($parts - 1) / 3; //one text part, then lead, placeholder and text per placeholder

		$as_array = isset($args[0]) && is_array($args[0]) && count($args) === 1;
		if($as_array) $args = $args[0];

		$sql = '';
		$identifiers = [];
		$strings = [];
		for($key = 2, $arg = 0; $key < $parts; $key += 3, $arg++){
			$placeholder = $split[$key];
			$spec = substr($placeholder, 1, -1);
			$type = substr($placeholder, -1);
			$after_percent = substr($split[$key - 1], -1) === '%';
			$pos = strpos($spec, '$');
			$index = ($pos === false) ? $arg : (int)substr($spec, 0, $pos) - 1;

			if($type === 'f' && $after_percent){
				//"%%%f" is no placeholder, as before WP 6.2: it comes out as the text "%%f"
				$run = $split[$key - 2] . $split[$key - 1];
				$len = strlen($run);
				$k = 1;
				while($k <= $len && $run[$len - $k] === '%') $k++;
				$placeholder = '%' . ($k % 2 ? '%' : '') . $spec . $type;
				$placeholders--;
			}elseif($type === 'f'){
				$placeholder = '%' . $spec . 'F';
			}elseif($type === 'i'){
				$placeholder = '`%' . $spec . 's`';
				$identifiers[] = $index;
			}elseif($type === 's'){
				$strings[] = $index;
				if($spec === '' && !$after_percent) $placeholder = "'%s'";
			}
			$sql .= $split[$key - 2] . $split[$key - 1] . $placeholder;
		}
		$sql .= $split[$key - 2];

		if(array_intersect($identifiers, $strings)) return null;

		$count = count($args);
		if($count !== $placeholders){
			if($placeholders === 1 && $as_array) return null;
			if($count < $placeholders){
				$numbered = 0;
				for($i = 2; $i < $parts; $i += 3) $numbered = max($numbered, (int)substr($split[$i], 1));
				if(!$numbered || $count < $numbered) return '';
			}
		}

		$values = [];
		foreach($args as $i => $value){
			if(in_array($i, $identifiers, true)){
				$values[] = str_replace('`', '``', $value ?? '');
			}elseif(is_int($value) || is_float($value)){
				$values[] = $value;
			}else{
				$values[] = self::escape($quote, $value);
			}
		}

		return str_replace('%', self::percent(), vsprintf($sql, $values));
	}

	/**
	 * wpdb's priority 0 'query' filter: the host runs every statement through this right before it
	 * executes it, ahead of its own 'query' filters
	 * @param string|null $sql
	 * @return string|null
	 * @since 7.2.1
	 */
	public static function unescape($sql){
		return is_string($sql) ? str_replace(self::percent(), '%', $sql) : $sql;
	}

	/**
	 * the statement wpdb::insert sends; the host database handles charsets, wpdb's field_types are not read
	 * @param callable             $quote
	 * @param string               $table  full table name including prefix
	 * @param array                $data   column => raw value, null stores NULL
	 * @param string|string[]|null $format one format for all values or one per value, default %s
	 * @return string|null null or '' where wpdb would send nothing
	 * @since 7.2.1
	 */
	public static function insert_sql(callable $quote, $table, array $data, $format = null){
		$formats = [];
		$values = [];
		foreach(self::fields($data, $format) as [$value, $f]){
			if($value === null){
				$formats[] = 'NULL';
				continue;
			}
			$formats[] = $f;
			$values[] = $value;
		}
		$fields = '`' . implode('`, `', array_keys($data)) . '`';

		return self::unescape(self::prepare($quote, "INSERT INTO `$table` ($fields) VALUES (" . implode(', ', $formats) . ')', $values));
	}

	/**
	 * the statement wpdb::update sends
	 * @param callable             $quote
	 * @param string               $table        full table name including prefix
	 * @param array                $data         column => raw value, null stores NULL
	 * @param array                $where        column => raw value joined with AND, null tests IS NULL
	 * @param string|string[]|null $format       as insert_sql()
	 * @param string|string[]|null $where_format as insert_sql()
	 * @return string|null null or '' where wpdb would send nothing
	 * @since 7.2.1
	 */
	public static function update_sql(callable $quote, $table, array $data, array $where, $format = null, $where_format = null){
		$fields = [];
		$values = [];
		foreach(self::fields($data, $format) as $field => [$value, $f]){
			if($value === null){
				$fields[] = "`$field` = NULL";
				continue;
			}
			$fields[] = "`$field` = " . $f;
			$values[] = $value;
		}
		$conditions = self::conditions($where, $where_format, $values);

		return self::unescape(self::prepare($quote, "UPDATE `$table` SET " . implode(', ', $fields) . " WHERE $conditions", $values));
	}

	/**
	 * the statement wpdb::delete sends
	 * @param callable             $quote
	 * @param string               $table        full table name including prefix
	 * @param array                $where        column => raw value joined with AND, null tests IS NULL
	 * @param string|string[]|null $where_format as insert_sql()
	 * @return string|null null or '' where wpdb would send nothing
	 * @since 7.2.1
	 */
	public static function delete_sql(callable $quote, $table, array $where, $where_format = null){
		$values = [];
		$conditions = self::conditions($where, $where_format, $values);

		return self::unescape(self::prepare($quote, "DELETE FROM `$table` WHERE $conditions", $values));
	}

	/**
	 * wpdb::process_field_formats: a short format list repeats its first entry, an empty one means %s
	 * @param array                $data
	 * @param string|string[]|null $format
	 * @return array column => [value, format]
	 */
	private static function fields(array $data, $format){
		$formats = (array)$format;
		$first = reset($formats);
		foreach($data as $field => $value){
			$data[$field] = [$value, empty($format) ? '%s' : (array_shift($formats) ?: $first)];
		}
		return $data;
	}

	/**
	 * @param array                $where
	 * @param string|string[]|null $where_format
	 * @param array                $values the placeholders' values, the where values are appended
	 * @return string the WHERE conditions joined with AND
	 */
	private static function conditions(array $where, $where_format, array &$values){
		$conditions = [];
		foreach(self::fields($where, $where_format) as $field => [$value, $f]){
			if($value === null){
				$conditions[] = "`$field` IS NULL";
				continue;
			}
			$conditions[] = "`$field` = " . $f;
			$values[] = $value;
		}
		return implode(' AND ', $conditions);
	}

	/**
	 * a value as wpdb::_real_escape hands it to vsprintf: escaped by the driver, not quoted
	 * @param callable $quote
	 * @param mixed    $value
	 * @return string
	 */
	private static function escape(callable $quote, $value){
		$literal = $quote(is_scalar($value) ? (string)$value : '');
		//N'...' or E'...' cut to its inside would reopen the quote; false is a driver that cannot quote
		if(!is_string($literal) || strlen($literal) < 2 || $literal[0] !== "'" || substr($literal, -1) !== "'"){
			throw new \UnexpectedValueException('Dialect: $quote must return a plain \'...\' string literal');
		}
		return str_replace('%', self::percent(), substr($literal, 1, -1));
	}

	/**
	 * @return string what a % stands as until unescape(): as long as wpdb's placeholder escape,
	 *                so %5s pads a value holding a % the way wpdb pads it
	 */
	private static function percent(){
		static $stand_in;
		return $stand_in ??= '{' . bin2hex(random_bytes(32)) . '}';
	}
}
