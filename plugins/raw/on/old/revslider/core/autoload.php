<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR;

if(!defined('ABSPATH')) exit();

/*
 * The core autoloader: PSR-4 for ThemePunch\SR\* out of core/src/, loading a class the moment
 * a request first uses it instead of the old forty-line require chain in boot.php.
 *
 * A host that must keep the classic global names (WordPress, standalone) hands the
 * core/compat/class-map.php catalogue to aliases(): every classic name then aliases its modern
 * class the moment either side is first used - including before type hints and instanceof
 * checks, which never trigger autoloading on their own. A greenfield host simply never calls
 * aliases() and ships without the legacy names.
 *
 * ThemePunch\SR\Platform\* is deliberately NOT resolved here: platform adapters are host
 * property and the host bootstrap requires its own.
 */
class Autoloader {

	/** @var array lowercased classic name => modern class, set by the host via aliases() - PHP resolves
	 *  class names case-insensitively, so 'new revsliderslider' must find the catalogue entry too */
	private static $aliases = [];

	/** @var array modern class => classic name, derived */
	private static $classic = [];

	/** @return void idempotent */
	public static function register(){
		static $done = false;
		if($done) return;
		$done = true;

		spl_autoload_register([self::class, 'load']);
	}

	/**
	 * hand over the classic-name catalogue; already-loaded modern classes are aliased right away
	 * @param array $map classic => modern (core/compat/class-map.php)
	 * @return void
	 */
	public static function aliases($map){
		self::$aliases = array_change_key_case($map, CASE_LOWER);
		self::$classic = array_flip($map);

		foreach($map as $classic => $modern){
			if(class_exists($modern, false) && !class_exists($classic, false)) class_alias($modern, $classic);
		}
	}

	/** @return void */
	public static function load($name){
		//a classic name: make sure its modern class is loaded, then alias - the alias must be made
		//HERE too, because a direct require of the modern file bypasses the branch below entirely
		$lower = strtolower($name);
		if(isset(self::$aliases[$lower])){
			$modern = self::$aliases[$lower];
			if(class_exists($modern) && !class_exists($name, false)) class_alias($modern, $name);
			return;
		}

		if(strpos($name, 'ThemePunch\\SR\\') !== 0) return;
		if(strpos($name, 'ThemePunch\\SR\\Platform\\') === 0) return; //host property

		$file = RS_PLUGIN_PATH . 'core/src/' . str_replace('\\', '/', substr($name, strlen('ThemePunch\\SR\\'))) . '.php';
		if(!is_file($file)) return;

		require $file;

		//the instant a modern class exists its classic name must too: type hints and instanceof
		//checks never autoload, so waiting for a lazy request would leave them failing
		if(isset(self::$classic[$name]) && !class_exists(self::$classic[$name], false)) class_alias($name, self::$classic[$name]);
	}
}
