<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

namespace ThemePunch\SR\Core;

if(!defined('ABSPATH')) exit();

/**
 * Singleton service locator. RevSliderGlobals::instance()->get('RevSliderFunctions') returns a shared
 * instance of that class, creating it on first access - so the plugin's helper objects exist once per
 * request instead of being re-instantiated everywhere. add() can also park arbitrary values under a key.
 */
class Registry {
	const SLIDER_REVISION = RS_REVISION;

	/**
	 * Second set of names for table constants that RevSliderFront already defines. Nothing inside the plugin
	 * reads any of them - they are kept only because they are public and a theme or addon may still reference
	 * them. Use the RevSliderFront constants instead; a class constant cannot raise a deprecation notice at
	 * runtime, so this docblock is the only signal available.
	 *
	 * @deprecated 7.0.0 use RevSliderFront::TABLE_* instead
	 */
	const TABLE_SLIDERS_NAME = \ThemePunch\SR\Core\Data::TABLE_SLIDER;
	/** @deprecated 7.0.0 use RevSliderFront::TABLE_SLIDES instead */
	const TABLE_SLIDES_NAME = \ThemePunch\SR\Core\Data::TABLE_SLIDES;
	/** @deprecated 7.0.0 use RevSliderFront::TABLE_STATIC_SLIDES instead */
	const TABLE_STATIC_SLIDES_NAME = \ThemePunch\SR\Core\Data::TABLE_STATIC_SLIDES;
	/** @deprecated 7.0.0 use RevSliderFront::TABLE_SETTINGS instead */
	const TABLE_SETTINGS_NAME = \ThemePunch\SR\Core\Data::TABLE_SETTINGS;
	/** @deprecated 7.0.0 use RevSliderFront::TABLE_LAYER_ANIMATIONS instead */
	const TABLE_LAYER_ANIMS_NAME = \ThemePunch\SR\Core\Data::TABLE_LAYER_ANIMATIONS;
	/** @deprecated 7.0.0 use RevSliderFront::TABLE_NAVIGATIONS instead */
	const TABLE_NAVIGATION_NAME = \ThemePunch\SR\Core\Data::TABLE_NAVIGATIONS;
	/** @var string prefixed name of the legacy v6 slider table (set in the constructor) */
	public static $table_sliders;
	/** @var string prefixed name of the legacy v6 slides table */
	public static $table_slides;
	/** @var string prefixed name of the legacy v6 static slides table */
	public static $table_static_slides;

	/**
	 * Stores the singleton instance of the class
	 * @var RevSliderGlobals
	 */
	private static $instance;

	/**
	 * store global objects
	 * @var array
	 */
	private $storage = [];

	protected function __construct(){
		//v6 table names, kept as public API; nothing inside the plugin reads them anymore.
		//The singleton can construct before any platform binds (standalone host), so no hard $wpdb
		$prefix = isset($GLOBALS['wpdb']->prefix) ? $GLOBALS['wpdb']->prefix : '';
		self::$table_sliders = $prefix.'revslider_sliders';
		self::$table_slides = $prefix.'revslider_slides';
		self::$table_static_slides = $prefix.'revslider_static_slides';
	}

	/**
	 * Instance accessor. If instance doesn't exist, we'll initialize the class.
	 *
	 * @return RevSliderGlobals
	 */
	public static function instance(){
		if(!isset(self::$instance))	self::$instance = new self();
		return self::$instance;
	}

	/**
	 * store $object under $key in $storage
	 * @param string $key
	 * @param mixed  $object
	 * @return void
	 */
	public function add($key, $object){
		$this->storage[self::canonical_key($key)] = $object;
	}

	/**
	 * one instance per class, whatever name asked for it: strips the leading backslash some callers
	 * pass ('Functions' used to mint a silent second instance) and, once a class has moved
	 * to its ThemePunch\SR home, folds its classic name onto the modern one via core/compat/class-map.php.
	 * The index holds BOTH spellings lowercased, because PHP resolves class names case-insensitively -
	 * an addon asking for 'revsliderfunctions' must reach the very same instance, not mint a second one.
	 * @since 7.2.1
	 * @return string
	 */
	private static function canonical_key($key){
		static $index = null;
		if(!is_string($key)) return $key;

		$key = ltrim($key, '\\');
		if($index === null){
			$index = [];
			$map = (is_file(RS_PLUGIN_PATH . 'core/compat/class-map.php')) ? require(RS_PLUGIN_PATH . 'core/compat/class-map.php') : [];
			foreach($map as $classic => $modern){
				$index[strtolower($classic)] = $modern;
				$index[strtolower($modern)]	= $modern;
			}
		}
		$lower = strtolower($key);

		return (isset($index[$lower]) && class_exists($index[$lower])) ? $index[$lower] : $key; //autoload on purpose: a mapped key must never be stored under its dark classic name
	}

	/**
	 * get object from storage, creating it on first access
	 *
	 * @param string $key class name, or an arbitrary key that was put in with add()
	 * @return mixed|null
	 */
	public function get($key){
		if(!is_string($key) || $key === '') return NULL;
		$key = self::canonical_key($key);
		if(array_key_exists($key, $this->storage)) return $this->storage[$key];

		if(!class_exists($key)) return NULL; //deliberately NOT cached - see below

		//that class_exists may have just AUTOLOADED the modern class, enabling the classic->modern
		//mapping that was still dark on entry - canonicalize again or we would store under the new
		//key and read back under the old one
		$key = self::canonical_key($key);
		if(array_key_exists($key, $this->storage)) return $this->storage[$key];

		//only instantiate what can actually be built without arguments. "new $key" on a class with a required
		//constructor argument is a fatal ArgumentCountError, which would take the whole page down instead of
		//degrading to a null the callers already handle.
		try{
			$reflection = new \ReflectionClass($key);
			if(!$reflection->isInstantiable()) return NULL;

			$constructor = $reflection->getConstructor();
			if($constructor !== null && $constructor->getNumberOfRequiredParameters() > 0) return NULL;
		}catch(\ReflectionException $e){
			return NULL;
		}

		$this->add($key, new $key);

		return $this->storage[$key];
	}

	/**
	 * @return array  list of revslider DB tables
	 */
	public function get_rs_tables(){
		//memoize per table-prefix (constant within a request; keyed so multisite switch_to_blog stays correct)
		static $table_cache = [];
		$db = $this->get('ThemePunch\SR\Ports\Database');
		$prefix = ($db !== null) ? $db->prefix() : $GLOBALS['wpdb']->prefix; //port when bound; raw wpdb only before the first Data construction binds it
		if(!isset($table_cache[$prefix])){
			$table_cache[$prefix] = [
				$prefix . \ThemePunch\SR\Core\Data::TABLE_SLIDER,
				$prefix . \ThemePunch\SR\Core\Data::TABLE_SLIDES,
				$prefix . \ThemePunch\SR\Core\Data::TABLE_STATIC_SLIDES,
				$prefix . \ThemePunch\SR\Core\Data::TABLE_LAYER_ANIMATIONS,
				$prefix . \ThemePunch\SR\Core\Data::TABLE_NAVIGATIONS,
				$prefix . \ThemePunch\SR\Core\Data::TABLE_SETTINGS,
				$prefix . \ThemePunch\SR\Core\Data::TABLE_SLOTS,
			];
		}

		return $table_cache[$prefix];
	}
	
}
