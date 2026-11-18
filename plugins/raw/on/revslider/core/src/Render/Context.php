<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Render;

if(!defined('ABSPATH')) exit();

/**
 * Per-request render state. Wraps the $SR_GLOBALS array by REFERENCE: the array stays the
 * physical storage (the bootstrap creates it, addons and the markup cache keep their view),
 * core code stops naming the superglobal.
 * @since 7.2.1
 */
class Context {

	private $g;

	public function __construct(array &$storage){
		$this->g = &$storage;
	}

	/**
	 * @param string|array $path key or nested key path
	 * @return mixed
	 */
	public function get($path, $default = null){
		$ref = $this->g;
		foreach((array)$path as $key){
			if(!is_array($ref) || !isset($ref[$key])) return $default;
			$ref = $ref[$key];
		}
		return $ref;
	}

	/**
	 * @param string|array $path key or nested key path, intermediate levels created like a plain array write
	 * @return void
	 */
	public function set($path, $value){
		$path = (array)$path;
		$last = array_pop($path);
		$ref  = &$this->g;
		foreach($path as $key){
			if(!isset($ref[$key]) || !is_array($ref[$key])) $ref[$key] = [];
			$ref = &$ref[$key];
		}
		$ref[$last] = $value;
	}

	/**
	 * append to the list at $path, like a plain [] push
	 * @return void
	 */
	public function push($path, $value){
		$ref = &$this->g;
		foreach((array)$path as $key){
			if(!isset($ref[$key]) || !is_array($ref[$key])) $ref[$key] = [];
			$ref = &$ref[$key];
		}
		$ref[] = $value;
	}

	/** @return array the raw state, by reference - for methods that mutate deep structures in place */
	public function &root(){
		return $this->g;
	}
}
