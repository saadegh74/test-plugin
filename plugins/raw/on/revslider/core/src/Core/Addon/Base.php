<?php
/**
 * @author	ThemePunch <info@themepunch.com>
 * @link	  https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Core\Addon;

if(!defined('ABSPATH')) exit();

//notices + updates (WP admin surfaces) and the store (WP uploads) live in wordpress/includes/ - the WP loader ships them
//Assets/Media/Metas/Templates/Ajax load through the autoloader on first construction

/**
 * Shared base for all SR7 AddOns (Harmonization Contract C9) - the AddOns Manager.
 * Its job is to register and validate one AddOn, then orchestrate the single shared services
 * (updates, assets, media, metas, ajax, notices) instead of every AddOn copying a Base/SliderFront/Update trio.
 *
 * Registration/validation, in use detection and the panel/modal maps live here; everything else is
 * delegated to core/includes/addon-base/*.class.php so each concern is one Single Responsibility class.
 *
 * AddOns call RevSliderAddonBase::register([...]) on plugins_loaded and keep their old
 * bundled Base only as Fallback for Cores without this class.
 **/
class Base extends \ThemePunch\SR\Core\Functions {

	private static $addons = [];
	private static $last_error = '';
	private static $migration = [];	//title => script urls of add-ons that moved from SR6, unregistered, for the overview's migration only	//notice code of the last failed register() ('add_notice_version' | 'add_notice_activation'), so the AddOn can show the matching notice

	public $slug;		//revslider-snow-addon
	public $title;		//snow
	public $path;		//absolute plugin path, trailing slash
	public $url;		//plugin url, trailing slash
	public $version;

	private $modals	= [];	//suboperation => file relative to path, validated on registration
	private $panels	= [];

	private $updates;		//RevSliderAddonUpdates
	private $assets;		//RevSliderAddonAssets
	private $media;			//RevSliderAddonMedia
	private $metas;			//RevSliderAddonMetas
	private $ajax;			//RevSliderAddonAjax

	/**
	 * Register an AddOn with the core Base. Returns the instance, or false if Requirements fail (AddOn falls back to its own Notice handling)
	 *
	 * The descriptor: slug, title, path, url, version, min_core, modals, panels, lang, scripts / eager /
	 * base_url_entry / resources / fonts (Assets), media (Media), legacy_options (old option name => new,
	 * renamed on registration) and block_editor (the host's block-editor scripts, placed by the WP loader).
	 *
	 * @return RevSliderAddonBase|false the addon instance, false when registration failed
	 **/
	public static function register($args){
		self::$last_error = '';
		$slug = isset($args['slug']) ? $args['slug'] : '';
		if($slug === '') return false;
		if(isset(self::$addons[$slug])) return self::$addons[$slug];

		//same Gates the copied Base classes had: core version + license. On failure record the reason so the AddOn can show the matching notice (false return kept for back compat)
		$min_core = isset($args['min_core']) ? $args['min_core'] : false;
		if($min_core !== false && !version_compare(\ThemePunch\SR\Core\Registry::SLIDER_REVISION, $min_core, '>=')){
			self::$last_error = 'add_notice_version';
			return false;
		}

		//register() is static (no instance yet - the gate decides whether to build one), so reach the shared RevSliderFunctions singleton for the license check
		$f = \ThemePunch\SR\Core\Registry::instance()->get('RevSliderFunctions');
		if($f->_truefalse($f->get_options(['system', 'valid'], 'false')) === false){
			self::keep_for_migration($f, $slug, $args);
			self::$last_error = 'add_notice_activation';
			return false;
		}

		self::$addons[$slug] = new self($args);
		do_action('revslider_addon_registered', self::$addons[$slug], $args); //the host wires its own surfaces off the same descriptor

		return self::$addons[$slug];
	}

	/**
	 * without a licence no add-on registers, so nothing of it draws or plays anywhere. One installed when the site moved
	 * from SR6 (SR7 build) hands only its scripts to the module overview, for a user of the plugin: its migration
	 * converts the module's add-on data with them (ADR-0027). The core never runs it there (SR7.E.lockedAddons)
	 * @return void
	 */
	private static function keep_for_migration($f, $slug, $args){
		$env = $f->environment();
		if(!$env->is_admin_area() || $env->is_ajax_or_api_request()) return;
		if($f->get_val($_GET, 'page', '') !== 'revslider' || !in_array($f->get_val($_GET, 'view', 'sliders'), ['', 'sliders'], true)) return;
		if(version_compare((string)$f->get_val($args, 'version', '0'), '7.0', '<')) return;
		if(!$env->user_can($f->resolve_user_role())) return;
		if(!array_key_exists($slug, \ThemePunch\SR\Core\Admin\UpgradeV6::addons_at_move())) return;

		$title = $f->get_val($args, 'title', str_replace(['revslider-', '-addon'], '', $slug));
		$list  = Assets::script_urls(trailingslashit($f->get_val($args, 'path')), trailingslashit($f->get_val($args, 'url')), $f->get_val($args, 'scripts', [$title => 'public/js/'.$title.'.js']));
		if(empty($list)) return;

		if(empty(self::$migration)) add_action('admin_footer', [__CLASS__, 'print_migration_scripts']);
		self::$migration[$title] = $list;
	}

	/** @return void */
	public static function print_migration_scripts(){
		echo Assets::script_open()."\nwindow.SR7 ??= {}; SR7.E ??= {}; SR7.E.resources ??= {}; SR7.E.lockedAddons ??= [];\n";
		foreach(self::$migration as $title => $list){
			foreach($list as $handle => $url) echo 'SR7.E.resources.'.$handle.' = "'.$url.'";'."\n"; //as Assets prints them
			echo 'SR7.E.lockedAddons.push("'.$title.'");'."\n";
		}
		echo '</script>'."\n";
	}

	/**
	 * a page without a module that asks for add-on engines (an Essential Grid card, a Page FX block) gets their files'
	 * addresses for SR7.F.effect(); only a registered add-on has any, so the licence decides here as everywhere
	 * @param array $titles add-on titles, "particles"
	 * @return void
	 */
	public static function print_page_effects($titles){
		$html = '';
		foreach(array_unique((array)$titles) as $title){
			$addon = self::get_addon('revslider-'.preg_replace('/[^a-z0-9]/', '', strtolower((string)$title)).'-addon');
			if($addon !== false) $html .= $addon->assets->add_header_scripts_return(false);
		}
		if($html !== '') echo Assets::script_open()."\nwindow.SR7 ??= {}; SR7.E ??= {}; SR7.E.resources ??= {};\n".$html.'</script>'."\n";
	}

	/** @return RevSliderAddonBase|false */
	public static function get_addon($slug){
		return isset(self::$addons[$slug]) ? self::$addons[$slug] : false;
	}

	/**
	 * notice code of the last failed register() ('' on success) - the AddOn reads it right after its own
	 * register() call to show the matching RevSliderAddonNotices
	 * @return string the reason the last register() call failed
	 */
	public static function get_register_error(){
		return self::$last_error;
	}

	private function __construct($args){
		$this->slug		= $args['slug'];
		$this->title	= $this->get_val($args, 'title', str_replace(['revslider-', '-addon'], '', $this->slug));
		$this->path		= trailingslashit($this->get_val($args, 'path'));
		$this->url		= trailingslashit($this->get_val($args, 'url'));
		$this->version	= $this->get_val($args, 'version', '1.0.0');
		$this->modals	= $this->validate_map($this->get_val($args, 'modals', []));
		$this->panels	= $this->validate_map($this->get_val($args, 'panels', []));

		//shared services - one Single Responsibility class each, configured from the same $args
		$this->updates	= (class_exists('RevSliderAddonUpdates')) ? new \RevSliderAddonUpdates($this) : null; //WP-only surface, absent on other hosts
		$this->assets	= new Assets($this, $args);
		$this->media	= new Media($this, $args);
		$this->metas	= new Metas($this, $args);
		$this->ajax		= new Ajax($this, $args);

		$this->migrate_options($this->get_val($args, 'legacy_options', []));

		add_action('after_setup_theme', [$this, 'load_textdomain']);

		if($this->environment()->is_admin_area()){
			add_filter('revslider_modify_modal_map', [$this, 'check_modal'], 10, 2);
			add_filter('revslider_modify_panel_map', [$this, 'check_panel'], 10, 2);
			//NOTE: addon translation strings (generated i18n-strings.php + 'lang' hand-list) reach
			//SR7.LANG via the existing lazy path — get_lang() (addon-base/ajax.class.php) feeds both
			//revslider_api_get_addon_lang and the 'lang.get' ajax, which tptools merges with
			//SR7.LANG = _tpt.extend(SR7.LANG, data). No separate page-load hook needed here.
		}
	}

	/** @return void */
	public function load_textdomain(){
		load_plugin_textdomain('rs_'.$this->title, false, $this->path.'languages/');
	}

	/** @return string the one option this addon keeps its settings under; every addon spelled it out itself, four or five times each */
	public function option_key(){
		return 'revslider_' . $this->title . '_addon';
	}

	/**
	 * one SR7.E.resources entry as the descriptor declares it, host-supplied ones included (an endpoint
	 * URL the addon's own classes must not spell out); '' when the addon declares none by that name
	 * @param string $name
	 * @return string
	 */
	public function resource($name){
		$list = $this->assets->get_resource_list();

		return isset($list[$name]) ? (string)$list[$name] : '';
	}

	/**
	 * options an older version stored under other names move to the current ones, the way the
	 * addons' host main files did it one copy each
	 * @param array $legacy old name => new name
	 * @return void
	 */
	private function migrate_options($legacy){
		if(empty($legacy)) return;

		$store = $this->option_storage();
		foreach($legacy as $old => $new){
			$value = $store->get($old, null);
			if($value === null) continue;
			$store->set($new, $value);
			$store->delete($old);
		}
	}

	/**
	 * Map entries must be namespaced with the AddOn title and point to existing files, everything else made the old shared path overwrite a latent fatal
	 * @return array the entries of $map whose files actually exist
	 **/
	private function validate_map($map){
		$valid = [];
		foreach($map ?? [] as $suboperation => $file){
			if(strpos($suboperation, $this->title) === false) continue;
			if(!file_exists($this->path.$file)) continue;
			$valid[$suboperation] = $file;
		}
		return $valid;
	}

	/** @return array */
	public function check_modal($panels, $suboperation){
		if(!isset($this->modals[$suboperation])) return $panels;
		$panels['path'] = $this->path;
		$panels['map'][$suboperation] = $this->modals[$suboperation];
		return $panels;
	}

	/** @return array */
	public function check_panel($panels, $suboperation){
		if(!isset($this->panels[$suboperation])) return $panels;
		$panels['path'] = $this->path;
		$panels['map'][$suboperation] = $this->panels[$suboperation];
		return $panels;
	}

	/**
	 * JSON aware in use Detection: explicit u flags first, then a real key walk for layer/slide level usage.
	 * Replaces the old strpos over the serialized Slider which false positive triggered on any text mentioning the AddOn.
	 * Shared authority used by the assets service for script delivery + html export.
	 * @return bool true when any slider actually uses this addon
	 **/
	public function is_in_use($slider){
		if(empty($slider)) return false;

		$u = $this->get_val($slider, ['settings', 'addOns', $this->title, 'u'], null);
		if($u === null) $u = $this->get_val($slider, ['params', 'addOns', $this->title, 'u'], null);
		if($u === true) return true;
		if($u === false) return false;

		//maybe some deeper element (slide/layer) uses the addon
		return $this->addon_key_exists(json_decode(json_encode($slider), true));
	}

	/** @return bool */
	private function addon_key_exists($node){
		if(!is_array($node)) return false;
		if(isset($node['addOns'][$this->title])) return true;
		foreach($node as $v){
			if(is_array($v) && $this->addon_key_exists($v)) return true;
		}
		return false;
	}
}
