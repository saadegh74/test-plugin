<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

namespace ThemePunch\SR\Core\Admin;

if(!defined('ABSPATH')) exit();

/**
 * Migration of Slider Revolution 6 data into the SR7 tables.
 *
 * The v6 tables are left untouched; every v6 slider is read, upgraded through the 6.x steps and then
 * written as a new v7 slider, with the id mapping kept so references (modals, actions) can be rewritten.
 * set_mode() switches between reading from the database and from an import package.
 */
class UpgradeV6 extends \ThemePunch\SR\Core\Functions {

	/** imported v6 modules whose browser migration has not run yet: ids in sr-options, one payload option per id */
	const PENDING_IMPORTS = 'revslider_v6_pending_imports';
	const PENDING_KEY	  = ['system', 'v6_pending'];
	const ADDONS_AT_MOVE  = ['system', 'addons_at_move']; //slug => version installed when the site moved from SR6, taken once
	const MOVED_UNTIL	  = ['system', 'addons_moved_until']; //last day the server hands out their SR7 builds without a licence
	const MOVED_TRY		  = ['system', 'addons_moved_try']; //time of a fetch that got nothing, the next waits a day
	const MOVED_STOP	  = ['system', 'addons_moved_stop']; //core version the server said closed/version to: no more asking until the next one
	const WITHOUT		  = ['system', 'v6_without']; //id => addons left out without a licence, hash of the module as moved
	const PENDING_TRY	  = ['system', 'v6_pending_try']; //the unseen run in progress: until, ids, n = runs without progress

	public $upgrade_to	= '6.7.37';
	public $revision;
	public $import		= false;
	public $navtypes	= ['arrows', 'thumbs', 'bullets', 'tabs', 'scrubber'];
	public $mode		= 'database';
	
	/**
	 * holds variables needed for certain updates
	 * @since: 6.2.0
	 **/
	public $update = [
		/**
		 * for update to 6.2.0
		 * it holds all easing names that need to be replaced whereever easings are used
		 **/
		'620' => [
			'ease_replace_adv'	=> ['Power0' => 'power0', 'Power1' => 'power1', 'Power2' => 'power2', 'Power3' => 'power3', 'Power4' => 'power4', 'Back' => 'back', 'Bounce' => 'bounce', 'Circ' => 'circ', 'Elastic' => 'elastic', 'Expo' => 'expo', 'Sine' => 'sine'],
			'ease_adv_modifier' => ['easeIn' => 'in', 'easeOut' => 'out', 'easeInOut' => 'inOut'],
			'ease_adv_from' => ['Linear.easeNone', 'SlowMo.ease'],
			'ease_adv_to' => ['none', 'slow']
		],
		/**
		 * for update to 6.6.10
		 * it holds all file path that need to be changed inside the sliders
		 **/
		'6101' => [
			'url_from' => [
				'\/public\/assets\/assets\/dummy.png',
				'\/public\/assets\/assets\/coloredbg-old.png',
				'\/public\/assets\/assets\/coloredbg.png',
				'\/public\/assets\/assets\/gridtile_3x3_white.png',
				'\/public\/assets\/assets\/gridtile_3x3.png',
				'\/public\/assets\/assets\/gridtile_white.png',
				'\/public\/assets\/assets\/gridtile.png',
				'\/public\/assets\/assets\/loader.gif',
				'\/public\/assets\/assets\/transparent.png',
				'\/public\/assets\/assets\/svg/',
				'\/public\/assets\/assets\/sources/'
			],
			'url_to' => [
				'\/sr6\/assets\/assets\/dummy.png',
				'\/sr6\/assets\/assets\/coloredbg-old.png',
				'\/sr6\/assets\/assets\/coloredbg.png',
				'\/sr6\/assets\/assets\/gridtile_3x3_white.png',
				'\/sr6\/assets\/assets\/gridtile_3x3.png',
				'\/sr6\/assets\/assets\/gridtile_white.png',
				'\/sr6\/assets\/assets\/gridtile.png',
				'\/sr6\/assets\/assets\/loader.gif',
				'\/sr6\/assets\/assets\/transparent.png',
				'\/public\/assets\/svg\/',
				'\/public\/assets\/sources\/'
			]
		]
	];

	public function __construct(){
		//set something to set the fetching to v6 sliders and not v7
		$this->revision = $this->get_version();

	}

	/**
	 * return version of installation
	 * @return string
	 */
	public function get_version(){
		return $this->option_storage()->get('revslider_update_version', '6.0.0');
	}

	/**
	 * set version of installation
	 * @return void
	 */
	public function set_version($set_to){
		$this->option_storage()->set('revslider_update_version', $set_to);
	}

	/**
	 * set import value
	 * @return void
	 */
	public function set_import($import){
		$this->import = $import;
	}

	/**
	 * allows to switch between database und object mode
	 * this allows imports of v6 sliders in v7
	 * @return void switches between reading v6 sliders from the database and from an import package
	 **/
	public function set_mode($mode){
		$this->mode = $mode;
	}

	/** @return bool */
	public static function do_v6_tables_exist(){
		global $SR_GLOBALS;

		if($SR_GLOBALS['v6db'] === true) return true;
		if($SR_GLOBALS['v6db'] === false) return false;

		//persisted across requests: the v6 legacy tables don't appear/disappear during normal operation,
		//so avoid running 2 SHOW TABLES queries on every page load (front + admin). Stored as a bool in
		//sr-options; only re-detected when the option is absent (fresh install / after it is cleared).
		$f		= \ThemePunch\SR\Core\Registry::instance()->get('RevSliderFunctions');
		$stored	= $f->get_options(['system', 'v6db'], 'unknown');
		if($stored === true || $stored === false){
			$SR_GLOBALS['v6db'] = $stored;
			return $stored;
		}

		$exist = self::find_v6_tables($f);
		$SR_GLOBALS['v6db'] = $exist;
		$f->update_option(['system', 'v6db'], $exist);

		return $exist;
	}

	/**
	 * the add-ons installed when the site moved from SR6: without a licence they load, migrate and stay locked from
	 * playing (ADR-0027). Taken once, the first time a site with SR6 tables asks, and never widened afterwards
	 * @since 7.2.5
	 * @return array slug => version
	 */
	public static function addons_at_move(){
		$f	  = \ThemePunch\SR\Core\Registry::instance()->get('RevSliderFunctions');
		$list = $f->get_options(self::ADDONS_AT_MOVE, null);
		if(is_array($list)) return $list;
		if(self::do_v6_tables_exist() !== true) return [];

		$list = [];
		foreach((array)$f->lifecycle()->installed_extensions() as $file => $data){
			if(preg_match('#^(revslider-[a-z0-9-]+-addon)/\1\.php$#', (string)$file, $m)) $list[$m[1]] = (string)$f->get_val($data, 'Version', '');
		}
		$f->update_option(self::ADDONS_AT_MOVE, $list);

		return $list;
	}

	/**
	 * reports the add-ons installed at the move; the server keeps the first list per install, answers it back with the
	 * last day it hands out their SR7 builds without a licence, and never takes a slug more (ADR-0027)
	 * @since 7.2.5
	 * @return array|false the slugs the server keeps, false when it did not say ok (not open yet, wrong version, offline)
	 */
	public function report_moved_addons(){
		$list = self::addons_at_move();
		if(empty($list)) return false;

		$res  = \ThemePunch\SR\Core\Registry::instance()->get('RevSliderLoadBalancer')->call_url_result('revslider/addons/moved.php', [
			'version' => urlencode(RS_REVISION),
			'addons'  => implode(',', array_slice(array_keys($list), 0, 60)),
		], 'updates');
		$json = ($res->ok && intval($res->code) === 200) ? json_decode((string)$res->body, true) : null;
		if(!is_array($json) || empty($json['ok'])){
			//unknown and retry come back on a later day; closed and version end the asking for this core version
			if(in_array($this->get_val($json, 'error', ''), ['closed', 'version'], true)) $this->update_option(self::MOVED_STOP, RS_REVISION);
			return false;
		}

		$kept = array_values(array_intersect((array)$this->get_val($json, 'addons', []), array_keys($list)));
		$this->update_option(self::ADDONS_AT_MOVE, array_intersect_key($list, array_flip($kept)));
		$this->update_option(self::MOVED_UNTIL, (string)$this->get_val($json, 'until', ''));

		return $kept;
	}

	/**
	 * the moved add-ons still in their SR6 build, which the overview fetches in SR7 before it migrates; none with a
	 * licence (they update the usual way), after the server's window, or within a day of a fetch that got nothing
	 * @since 7.2.5
	 * @return array slugs
	 */
	public function moved_addons_needing_build(){
		if($this->_truefalse($this->get_options(['system', 'valid'], 'false')) === true) return [];
		$until = (string)$this->get_options(self::MOVED_UNTIL, '');
		if($until !== '' && gmdate('Y-m-d') > $until) return [];
		if(time() - intval($this->get_options(self::MOVED_TRY, 0)) < 86400) return []; //a day
		if($this->get_options(self::MOVED_STOP, '') === RS_REVISION) return [];

		$installed = (array)$this->lifecycle()->installed_extensions();
		$need	   = [];
		foreach(self::addons_at_move() as $slug => $version){
			$now = (string)$this->get_val($installed, [$slug.'/'.$slug.'.php', 'Version'], '');
			if($now !== '' && version_compare($now, '7.0', '<')) $need[] = $slug;
		}
		return $need;
	}

	/**
	 * without a licence, the server's window lets the download of a moved add-on through
	 * @since 7.2.5
	 * @return bool
	 */
	public function moved_delivery_open($slug){
		$until = (string)$this->get_options(self::MOVED_UNTIL, '');
		return $until !== '' && gmdate('Y-m-d') <= $until && array_key_exists($slug, self::addons_at_move());
	}

	/** @return void */
	public function set_moved_try(){
		$this->update_option(self::MOVED_TRY, time());
	}

	/**
	 * a stored false outlives a plugin deleted by FTP or replaced by zip, and SR6 may have come in meanwhile
	 * @return bool
	 */
	public static function recheck_v6_tables(){
		global $SR_GLOBALS;
		if(self::do_v6_tables_exist() === true) return true;

		$f = \ThemePunch\SR\Core\Registry::instance()->get('RevSliderFunctions');
		if(!self::find_v6_tables($f)) return false;

		$SR_GLOBALS['v6db'] = true;
		$f->update_option(['system', 'v6db'], true);

		return true;
	}

	/** @return bool */
	private static function find_v6_tables($f){
		$slider	= $f->database()->get_row("SHOW TABLES LIKE '". $f->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDER_V6 ."';");
		$slide	= $f->database()->get_row("SHOW TABLES LIKE '". $f->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDES_V6 ."';");

		return !(empty($slider) || empty($slide));
	}
	

	/**
	 * return ALL v6 sliders (if table exists at all) if:
	 * - get all v6 then check:
	 * 	- if not has an v7 entry
	 * 	- if has, check version v7 < 6.7.37 -> add to list
	 * @return array ids of v6 sliders that still need migrating
	 **/
	public function slider_need_update_checks_v6(){
		if(self::do_v6_tables_exist() === false) return [];

		global $SR_GLOBALS;
		$SR_GLOBALS['v6'] = false;

		$sr		 = new \ThemePunch\SR\Core\Slider();
		$sliders_v7 = $sr->get_sliders_short_list();

		$SR_GLOBALS['v6'] = true;
		$sr		 = new \ThemePunch\SR\Core\Slider();
		$sliders_v6 = $sr->get_sliders_short_list();
		$SR_GLOBALS['v6'] = false;

		$sliders_to_upgrade = [];

		foreach($sliders_v6 ?? [] as $s6id => $slider_v6){
			$v6_settings = json_decode($this->get_val($slider_v6, 'settings'), true);
			$migrated = $this->_truefalse($this->get_val($v6_settings, 'migrated', false));
			
			if($migrated === true) continue;

			if(!isset($sliders_v7[$s6id])){
				$sliders_to_upgrade[] = $s6id;
				continue;
			}

			$settings = json_decode($this->get_val($sliders_v7, [$s6id, 'settings']), true);
			if(version_compare($this->get_val($settings, 'version', '1.0.0'), $this->upgrade_to, '<')) $sliders_to_upgrade[] = $s6id;
		}

		return $sliders_to_upgrade;
	}

	/** @return array v6 sliders that have no v7 counterpart yet */
	public function slider_v6_has_no_v7(){
		if(self::do_v6_tables_exist() === false) return [];

		global $SR_GLOBALS;
		$SR_GLOBALS['v6'] = false;

		$sr		 = new \ThemePunch\SR\Core\Slider();
		$sliders_v7 = $sr->get_sliders_short_list();

		$SR_GLOBALS['v6'] = true;
		$sr		 = new \ThemePunch\SR\Core\Slider();
		$sliders_v6 = $sr->get_sliders_short_list();

		$SR_GLOBALS['v6'] = false;

		$sliders_has_no_v7 = [];

		foreach($sliders_v6 ?? [] as $s6id => $slider_v6){
			$v6_settings = json_decode($this->get_val($slider_v6, 'settings'), true);
			$migrated = $this->_truefalse($this->get_val($v6_settings, 'migrated', false));
			
			if($migrated === true) continue;

			if(!isset($sliders_v7[$s6id])) $sliders_has_no_v7[] = $s6id;
		}

		return $sliders_has_no_v7;
	}

	/**
	 * get the next slider that is not on the latest version and update it to the latest
	 * @return array|false migrates one v6 slider and returns its new data
	 **/
	public function upgrade_next_slider_v6($slider_id){
		$pending = $this->get_pending_import($slider_id);
		if($pending !== false) return $pending;

		//switch to V6 table
		global $SR_GLOBALS;
		$SR_GLOBALS['v6'] = true;

		$slider	 = new \ThemePunch\SR\Core\Slider();
		$slider->init_by_id($slider_id);

		$version = $this->get_val($slider, ['settings', 'version']);
		
		if(version_compare($version, $this->revision, '<')){
			$state = $this->upgrade_slider_to_latest_v6($slider);
			if($state === false) return false;
		}
		//$SR_GLOBALS['v6'] = false;

		return $this->prepare_upgrade_return_data($slider);
	}

	/**
	 * the skins a v6 slider references, type => [id => skin], read by the browser migration as SR7.NAV
	 * only skins that are switched on: the full builder table is ~190 KB per module
	 * @param RevSliderSlider $slider
	 * @return array
	 */
	public function get_used_navigations($slider){
		$rs_nav	= new \ThemePunch\SR\Core\Navigation();
		$all	= $rs_nav->get_all_navigations_builder();
		$navs	= [];
		foreach(['arrows', 'bullets', 'thumbs', 'tabs', 'scrubber'] as $type){
			if($this->_truefalse($slider->get_param(['nav', $type, 'set'], false)) !== true) continue;
			$id = $slider->get_param(['nav', $type, 'style'], false);
			if($id === false || !isset($all[$type][$id])) continue;
			$navs[$type] = [$id => $all[$type][$id]];
		}

		return $navs;
	}

	/** @return mixed */
	public function prepare_upgrade_return_data($slider){
		global $SR_GLOBALS;

		$id				= $slider->get_id();
		$_slide			= new \RevSliderSlide();
		if($this->mode === 'database'){
			$_slider = new \ThemePunch\SR\Core\Slider();
			$_slider->init_by_id($id);
		}else{
			$_slider = $slider;
		}

		$slides			= ($this->mode === 'database') ? $_slider->get_slides() : $slider->slides;
		$_slides		= [];
		$_static_slide	= [];

		foreach($slides ?? [] as $s){
			$slide_id = $s->get_id();
			$_slides[] = [
				'order'	 => $s->get_order(),
				'params' => $s->get_params(),
				'layers' => $s->get_layers(),
				'id'	 => $slide_id
			];
		}

		if($this->mode === 'database'){
			$static_slide_id = $_slide->get_static_slide_id($id);
			$static_slide = false;
			if(intval($static_slide_id) > 0){
				$static_slide = new \RevSliderSlide();
				$static_slide->init_by_static_id($static_slide_id);
			}
		}else{
			$static_slide = $slider->_static_slide;
		}

		if(!empty($static_slide)){
			$slide_id = $static_slide->get_id();
			$_static_slide = [
				'params' => $static_slide->get_params(),
				'layers' => $static_slide->get_layers(),
				'id'	 => $slide_id,
			];
		}
		
		$obj = [
			'id'				=> $id,
			'alias'				=> $_slider->get_alias(),
			'title'				=> $_slider->get_title(),
			'slider_params'		=> $_slider->get_params(true),
			'slider_settings'	=> $_slider->get_settings(),
			'slides'			=> $_slides,
			'static_slide'		=> $_static_slide,
		];

		$rs7output	= new \ThemePunch\SR\Core\Output();
		$rs7output->slider = $_slider;
		$rs7output->set_slider_id($id);
		$rs7output->slides = $slides;
		$rs7output->set_javascript_variables();
		$obj['navs']	= (object)$this->get_used_navigations($_slider);
		$obj['trans']	= (object)array_filter($this->get_val($SR_GLOBALS, ['collections', 'trans'], []), function($value) { return !empty($value); });
		if($this->mode === 'database') $obj['v6v7ids']	= (object)$this->get_v7_slider_map($id);
		$obj['addOns']	= [];

		$obj = apply_filters('sr_get_full_slider_JSON', $obj, $_slider);

		$SR_GLOBALS['v6'] = false;
		
		return $obj;
	}

	/** @return array */
	public static function do_update_checks_v6(){
		if(self::do_v6_tables_exist() === false) return [];

		global $SR_GLOBALS;
		$SR_GLOBALS['v6'] = true;

		$upd	 = new self();
		$version = $upd->get_version();

		if(version_compare($version, '6.6.10', '<')){
			//remove templates from the database
			$upd->remove_template_sliders();
			$upd->set_version('6.6.10');
		}

		if(version_compare($version, '6.7.24', '<')){
			$upd->update_post_slide_template_v7();
			$upd->set_version('6.7.24');
		}

		if(version_compare($version, '6.7.37', '<')){
			$upd->upgrade_sliders_to_6_7_37();
			$upd->set_version('6.7.37');
		}

		global $SR_GLOBALS;
		$SR_GLOBALS['v6'] = false;
	}

	/**
	 * check to convert the given Slider to latest versions
	 * it needs to be ensured, that upgrade_slider_to_version() is called at the end
	 * @return RevSliderSlider|false
	 **/
	public function upgrade_slider_to_latest_v6($slider){
		$version = $slider->get_setting('version', '1.0.0');
		
		if(version_compare($version, '6.0', '<')){
			return false;
		}

		if(version_compare($version, '6.1.4', '<')){
			$this->upgrade_slider_to_6_1_4($slider);
		}
		
		if(version_compare($version, '6.1.6', '<')){
			$this->upgrade_slider_to_6_1_6($slider);
		}
		
		if(version_compare($version, '6.2.0', '<')){
			$this->upgrade_slider_to_6_2_0($slider);
		}
		
		if(version_compare($version, '6.4.0', '<')){
			$this->upgrade_slider_to_6_4_0($slider);
		}
		
		if(version_compare($version, '6.4.10', '<')){
			$this->upgrade_slider_to_6_4_10($slider);
		}

		if(version_compare($version, '6.5.12', '<')){
			$this->upgrade_slider_to_6_5_12($slider);
		}

		if($this->import === false){
			if(version_compare($version, '6.5.26', '<')){
				$this->upgrade_slider_to_6_5_26($slider);
			}
		}
		
		if(version_compare($version, '6.6.0', '<')){
			$this->upgrade_slider_to_6_6_0($slider);
		}
		
		if(version_compare($version, '6.6.10', '<')){
			$this->upgrade_slider_to_6_6_10($slider);
		}
		
		if(version_compare($version, '6.6.20', '<')){
			$this->upgrade_slider_to_6_6_20($slider);
		}
		
		if(version_compare($version, '6.7.21', '<')){
			$this->upgrade_slider_to_6_7_21($slider);
		}
		
		if(version_compare($version, '6.7.24', '<')){
			$this->upgrade_slider_to_6_7_24($slider);
		}

		$this->upgrade_slider_to_version($slider, $this->revision);

		//every step above takes $slider by reference and wraps it in a list, so an import that ran any step got a list back
		return is_array($slider) ? reset($slider) : $slider;
	}


	/**
	 * remove all sliders that have type => template
	 * @return bool
	 **/
	public function remove_template_sliders(){
		$slider_data = $this->database()->get_results("SELECT id FROM ". $this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDER_V6 ." WHERE `type` = 'template' ORDER BY id ASC", 'ARRAY_A');
		if(empty($slider_data)) return true;
		
		$ids = [];
		foreach($slider_data as $data){
			$ids[] = (int)$data['id'];
		}

		//the ids come straight out of the query above, but these are DELETEs - build them through prepare() so
		//their safety does not rest on where the array happened to be filled
		$in = implode(',', array_fill(0, count($ids), '%d'));

		$this->database()->query("DELETE FROM ". $this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDER_V6 ." WHERE `type` = 'template'");
		$this->database()->query($this->database()->prepare("DELETE FROM ". $this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDES_V6 ." WHERE `slider_id` IN ({$in})", $ids));
		$this->database()->query($this->database()->prepare("DELETE FROM ". $this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_STATIC_SLIDES ." WHERE `slider_id` IN ({$in})", $ids));
	}


	/**
	 * get in all posts the slide_template variable and migrate to slide_template_v7
	 * $slide_id needs to be a v6 slide id
	 * @return void
	 **/
	public function update_post_slide_template_v7($slide_id = false){
		\RevSliderWpUpgradeGlue::update_post_slide_template_v7($this, $slide_id); //WP content domain - the postmeta work lives with the adapter
	}


	/**
	 * return 'id' = [fields] from the sliders table
	 *
	 * @param array  $fields
	 * @param string $table
	 * @return array
	 */
	private function get_sliders_array($fields, $table){
		$sliders = [];

		$slider_data = $this->database()->get_results("SELECT " . implode(',', array_map('sanitize_key', $fields)) . " FROM ". $this->database()->prefix() . $table, 'ARRAY_A');
		if(empty($slider_data)) return $sliders;

		foreach($slider_data as $data){
			$sliders[ $data['id'] ] = $data;
		}

		return $sliders;
	}


	/**
	 * if module older than 6.7.37 I remove it from the SR7 table
	 * add a flag "migrated:true" on the SR6 table, if SR7 Table 6.7.37+ version exists to that module
	 * @return void
	 */
	public function upgrade_sliders_to_6_7_37(){
		if( ! self::do_v6_tables_exist() ) return;

		$sliders_v6 = $this->get_sliders_array(['id', 'alias', 'settings'], \ThemePunch\SR\Core\Data::TABLE_SLIDER_V6);
		$sliders_v7 = $this->get_sliders_array(['id', 'alias', 'settings'], \ThemePunch\SR\Core\Data::TABLE_SLIDER);

		$remove = [];
		foreach($sliders_v7 as $id => $s7){
			if (!isset($sliders_v6[ $id ]) || $sliders_v6[ $id ]['alias'] != $s7['alias']) continue;

			$settings = json_decode($s7['settings'], true);
			if (json_last_error() !== JSON_ERROR_NONE || empty($settings['version'])) continue;

			if (version_compare($settings['version'], $this->upgrade_to, '>=')) {
				// version is 6.7.37+, add a flag "migrated:true" on the SR6 table
				$settings_v6 = json_decode($sliders_v6[ $id ]['settings'], true);
				if (json_last_error() !== JSON_ERROR_NONE){
					$settings_v6 = [];
				}
				$settings_v6['migrated'] = true;
				$this->database()->update($this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDER_V6, ['settings' => wp_json_encode($settings_v6)], ['id' => $id]);
			} else {
				// remove module from SR7 table
				$remove[] = $id;
			}
		}
		if (empty($remove)) return;

		$placeholders = implode(',', array_fill(0, count($remove), '%d'));
		$this->database()->query( $this->database()->prepare("DELETE FROM " . $this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDER . " WHERE id IN ($placeholders)", $remove) );
	}


	/**
	 * upgrade slider settings to a higher version, to be on par with revision
	 * @return void
	 */
	public function upgrade_slider_to_version($sliders = false, $version = false){
		$version = ($version === false) ? $this->revision : $version;
		$this->raise_runtime_limits(300);

		$sr		 = new \ThemePunch\SR\Core\Slider();
		if($sliders === false){
			$sr = new \ThemePunch\SR\Core\Slider();
			$sliders = $sr->get_sliders();
		}elseif(!is_array($sliders) || !isset($sliders[0])){
			$sliders = [$sliders];
		}

		foreach($sliders ?? [] as $slider){
			$slider->settings['version'] = $version;
			if($this->mode === 'database') $slider->update_settings(['version' => $version]);
		}
	}


	/**
	 * check to convert the given Slider to latest versions
	 * @since: 6.1.4
	 * reverse the carousel.scaleDown value. If it was 85, change it to 15 and vice versa
	 * @return void
	 **/
	public function upgrade_slider_to_6_1_4(&$sliders = false){
		
		if($sliders === false){
			$sr = new \ThemePunch\SR\Core\Slider();
			$sliders = $sr->get_sliders();
		}elseif(!is_array($sliders) || !isset($sliders[0])){
			$sliders = [$sliders];
		}

		foreach($sliders ?? [] as $slider){
			$carousel = $slider->get_param('carousel', []);
			$scale_down = $this->get_val($carousel, 'scaleDown');
			
			if($scale_down !== false){
				$carousel['scaleDown'] = 100 - intval($scale_down);
				$slider->set_param('carousel', $carousel);

				if($this->mode === 'database') $slider->save_params();
			}
			
			$slider->settings['version'] = '6.1.4';
			if($this->mode === 'database') $slider->update_settings(['version' => '6.1.4']);
		}
	}
	
	/**
	 * check to convert the given Slider to latest versions
	 * @since: 6.1.6
	 * check in the slide transitions, if we have a transition with a ","
	 * if this is the case, split it up
	 * @return void
	 **/
	public function upgrade_slider_to_6_1_6(&$sliders = false){
		$sl = new \RevSliderSlide();
		
		if($sliders === false){
			$sr = new \ThemePunch\SR\Core\Slider();
			$sliders = $sr->get_sliders();
		}elseif(!is_array($sliders) || !isset($sliders[0])){
			$sliders = [$sliders];
		}

		foreach($sliders ?? [] as $slider){
			$slides = $slider->get_slides(false, true);
			if($this->mode === 'database'){
				$static_id = $sl->get_static_slide_id($slider->get_id());
				if($static_id !== false){
					$msl = new \RevSliderSlide();
					if(strpos($static_id, 'static_') === false) $static_id = 'static_'. $static_id; //$slider->get_id();

					$msl->init_by_id($static_id);
					if($msl->get_id() !== '') $slides = array_merge($slides, [$msl]);
				}
			}else{
				$static = $slider->get_static_slide();
				if($static instanceof \RevSliderSlide) $slides['static'] = $static;
			}
			
			foreach($slides ?? [] as $key => $slide){
				$settings = $slide->get_settings();
				if(version_compare($this->get_val($settings, 'version', '1.0.0'), '6.1.6', '>=')) continue;
			
				$params = $slide->get_params();
				$transitions = $this->get_val($params, ['timeline', 'transition'], []);
				$new_transitions = [];
				if(!empty($transitions) && is_array($transitions)){
					foreach($transitions ?? [] as $t => $v){
						if(strpos($v, ',') !== false){
							$save = true;
							$_v = explode(',', $v);
							if(!empty($_v)){
								foreach($_v ?? [] as $k => $__v){
									$new_transitions[] = $__v;
								}
							}
						}else{
							$new_transitions[] = $v;
						}
					}

					$this->set_val($params, ['timeline', 'transition'], $new_transitions);
				}
				
				$slide->set_params($params);
				if($this->mode === 'database') $slide->save_params();
				$slide->settings['version'] = '6.1.6';
				$slides[$key] = $slide;

				if($this->mode === 'database') $slide->save_settings();
			}
			
			if($this->mode === 'object'){
				if(isset($slides['static'])){
					$slider->_static_slide = $slides['static'];
					unset($slides['static']);
				}
				$slider->slides = $slides;
			}

			$slider->settings['version'] = '6.1.6';
			if($this->mode === 'database') $slider->update_settings(['version' => '6.1.6']);
		}
	}
	
	
	/** check to convert the given Slider to latest versions
	 * @since: 6.2.0
	 * check in all layers, if we have a ease in it and convert it
	 * @return void
	 **/
	public function upgrade_slider_to_6_2_0(&$sliders = false){
		$sl = new \RevSliderSlide();
		
		if($sliders === false){
			$sr = new \ThemePunch\SR\Core\Slider();
			$sliders = $sr->get_sliders();
		}elseif(!is_array($sliders) || !isset($sliders[0])){
			$sliders = [$sliders];
		}

		foreach($sliders ?? [] as $slider){
			//on slider params
			if(version_compare($slider->get_setting('version', '1.0.0'), '6.2.0', '<')){
				$params = $slider->get_params();
				$json_params	= $_json_params = json_encode($params);
				$_json_params	= str_replace($this->update['620']['ease_adv_from'], $this->update['620']['ease_adv_to'], $_json_params);
				
				if($_json_params !== $json_params){
					$params = (array)json_decode($_json_params, true);
					$params['version'] = '6.2.0';
					$slider->set_params($params);
					if($this->mode === 'database') $slider->save_params();
				}
			}
			
			$slides = $slider->get_slides(false, true);

			if($this->mode === 'database'){
				$static_id = $sl->get_static_slide_id($slider->get_id());
				if($static_id !== false){
					$msl = new \RevSliderSlide();
					if(strpos($static_id, 'static_') === false) $static_id = 'static_'. $static_id; //$slider->get_id();

					$msl->init_by_id($static_id);
					if($msl->get_id() !== '') $slides = array_merge($slides, [$msl]);
				}
			}else{
				$static = $slider->get_static_slide();
				if($static instanceof \RevSliderSlide) $slides['static'] = $static;
			}
		
			foreach($slides ?? [] as $key => $slide){
				$settings = $slide->get_settings();
				//on slides
				if(version_compare($this->get_val($settings, 'version', '1.0.0'), '6.2.0', '<')){
					$params			= $slide->get_params();
					$json_params	= $_json_params = json_encode($params);
					$_json_params	= str_replace($this->update['620']['ease_adv_from'], $this->update['620']['ease_adv_to'], $_json_params);
					$params			= ($_json_params !== $json_params) ? (array)json_decode($_json_params, true) : $params;
					$params['version'] = '6.2.0';
					$slide->set_params($params);
					if($this->mode === 'database') $slide->save_params();
					
					$slide->settings['version'] = '6.2.0';
					if($this->mode === 'database') $slide->save_settings();
				}
				
				//on layers
				$layers = $slide->get_layers();
				foreach($layers ?? [] as $lk => $layer){
					$version = $this->get_val($layer, 'version', '1.0.0');
					
					if(version_compare($version, '6.2.0', '>=')) continue;
				
					$json_layer	 = $_json_layer = json_encode($layer);
					$_json_layer = str_replace($this->update['620']['ease_adv_from'], $this->update['620']['ease_adv_to'], $_json_layer);
					if($_json_layer !== $json_layer){
						$layers[$lk] = (array)json_decode($_json_layer, true);
					}
					$layers[$lk]['version'] = '6.2.0';
				}
				
				$slide->set_layers_raw($layers);
				$slides[$key] = $slide;
				if($this->mode === 'database') $slide->save_layers();
			}
			
			if($this->mode === 'object'){
				if(isset($slides['static'])){
					$slider->_static_slide = $slides['static'];
					unset($slides['static']);
				}
				$slider->slides = $slides;
			}

			$slider->settings['version'] = '6.2.0';
			if($this->mode === 'database') $slider->update_settings(['version' => '6.2.0']);
		}
	}
	
	
	/** check to convert the given Slider to latest versions
	 * @since: 6.4.0
	 * check in all layers, if we have an gradient in idle and if we need to push it to the hover animation
	 * @return void
	 **/
	public function upgrade_slider_to_6_4_0(&$sliders = false){
		$sl = new \RevSliderSlide();
		
		if($sliders === false){
			$sr = new \ThemePunch\SR\Core\Slider();
			$sliders = $sr->get_sliders();
		}elseif(!is_array($sliders) || !isset($sliders[0])){
			$sliders = [$sliders];
		}
		
		foreach($sliders ?? [] as $slider){
			if(version_compare($slider->get_setting('version', '1.0.0'), '6.4.0', '<')){
				$params = $slider->get_params();
				$params['version'] = '6.4.0';
				
				if($this->get_val($params, ['layout', 'bg'], false) !== false){
					$do = strtolower($this->get_val($params, ['layout', 'bg', 'dottedOverlay'], ''));
					if(strpos($do, 'white') !== false)		 $this->set_val($params, ['layout', 'bg', 'dottedColorB'], '#FFFFFF');
					if(strpos($do, 'twoxtwo') !== false)	 $this->set_val($params, ['layout', 'bg', 'dottedOverlay'], '1');
					if(strpos($do, 'threexthree') !== false) $this->set_val($params, ['layout', 'bg', 'dottedOverlay'], '2');
				}
				
				$slider->set_params($params);
				if($this->mode === 'database') $slider->save_params();
			}
			
			$slides = $slider->get_slides(false, true);
			if($this->mode === 'database'){
				$static_id = $sl->get_static_slide_id($slider->get_id());
				if($static_id !== false){
					$msl = new \RevSliderSlide();
					if(strpos($static_id, 'static_') === false) $static_id = 'static_'. $static_id; //$slider->get_id();

					$msl->init_by_id($static_id);
					if($msl->get_id() !== '') $slides = array_merge($slides, [$msl]);
				}
			}else{
				$static = $slider->get_static_slide();
				if($static instanceof \RevSliderSlide) $slides['static'] = $static;
			}
			
			foreach($slides ?? [] as $key => $slide){
				$settings = $slide->get_settings();
				//on slides
				if(version_compare($this->get_val($settings, 'version', '1.0.0'), '6.4.0', '<')){
					$params = $slide->get_params();
					$params['version'] = '6.4.0';
					
					$do	= $this->get_val($params, ['bg', 'video', 'dottedOverlay'], 'none');
					if(strpos($do, 'white') !== false)		 $this->set_val($params, ['bg', 'video', 'dottedColorB'], '#FFFFFF');
					if(strpos($do, 'twoxtwo') !== false)	 $this->set_val($params, ['bg', 'video', 'dottedOverlay'], '1');
					if(strpos($do, 'threexthree') !== false) $this->set_val($params, ['bg', 'video', 'dottedOverlay'], '2');
					
					$slide->set_params($params);
					if($this->mode === 'database') $slide->save_params();
					
					$slide->settings['version'] = '6.4.0';
					if($this->mode === 'database') $slide->save_settings();
				}
				
				//on layers
				$layers = $slide->get_layers();
				foreach($layers ?? [] as $lk => $layer){
					$version = $this->get_val($layer, 'version', '1.0.0');
					
					if(version_compare($version, '6.4.0', '<')){
						$save = true;
						$layers[$lk]['version'] = '6.4.0';
						
						if($this->get_val($layer, 'type', 'text') === 'video'){
							$do = $this->get_val($layer, ['media', 'dotted']);
							if(strpos($do, 'white') !== false)		 $this->set_val($layers, [$lk, 'media', 'dottedColorB'], '#FFFFFF');
							if(strpos($do, 'twoxtwo') !== false)	 $this->set_val($layers, [$lk, 'media', 'dotted'], '1');
							if(strpos($do, 'threexthree') !== false) $this->set_val($layers, [$lk, 'media', 'dotted'], '2');
						}
						
						if($this->get_val($layer, 'type', 'text') === 'shape') continue;
						$idle_bg = $this->get_val($layer, ['idle', 'backgroundColor'], '');
						if(
							strpos($idle_bg, 'gradient') === false &&
							strpos($idle_bg, 'radial') === false && 
							strpos($idle_bg, 'linear') === false && 
							strpos($idle_bg, '&type') === false
						) continue;
						if($this->get_val($layer, ['hover', 'usehover'], false) === false) continue;
						
						$hover_bg = $this->get_val($layer, ['hover', 'backgroundColor'], '');
						if(
							strpos($hover_bg, 'gradient') !== false ||
							strpos($hover_bg, 'radial') !== false || 
							strpos($hover_bg, 'linear') !== false || 
							strpos($hover_bg, '&type') !== false
						) continue;
						
						$layers[$lk]['hover']['backgroundColor'] = $idle_bg;
					}
				}
				
				$slide->set_layers_raw($layers);
				$slides[$key] = $slide;
				if($this->mode === 'database') $slide->save_layers();
			}

			if($this->mode === 'object'){
				if(isset($slides['static'])){
					$slider->_static_slide = $slides['static'];
					unset($slides['static']);
				}
				$slider->slides = $slides;
			}
			
			$slider->settings['version'] = '6.4.0';
			if($this->mode === 'database') $slider->update_settings(['version' => '6.4.0']);
		}
	}
	
	/** check to convert the given Slider to latest versions
	 * @since: 6.4.10
	 * @return void
	 **/
	public function upgrade_slider_to_6_4_10(&$sliders = false){
		
		if($sliders === false){
			$sr = new \ThemePunch\SR\Core\Slider();
			$sliders = $sr->get_sliders();
		}elseif(!is_array($sliders) || !isset($sliders[0])){
			$sliders = [$sliders];
		}
		
		foreach($sliders ?? [] as $slider){
			if(version_compare($slider->get_setting('version', '1.0.0'), '6.4.10', '<')){
				$params = $slider->get_params();
				$params['version'] = '6.4.10';
				$slider->set_params($params);
				if($this->mode === 'database') $slider->save_params();
				
				$slider->settings['version'] = '6.7.24';
				if($this->mode === 'database') $slider->update_settings(['version' => '6.7.24']);
			}
		}
	}

	/** check to convert the given Slider to latest versions
	 * @since: 6.4.10
	 * @return void
	 **/
	public function upgrade_slider_to_6_5_26(&$sliders = false){
		$sl = new \RevSliderSlide();
		
		if($sliders === false){
			$sr = new \ThemePunch\SR\Core\Slider();
			$sliders = $sr->get_sliders();
		}elseif(!is_array($sliders) || !isset($sliders[0])){
			$sliders = [$sliders];
		}
		
		if(!empty($sliders) && is_array($sliders)){
			foreach($sliders as $slider){
				if(version_compare($slider->get_setting('version', '1.0.0'), '6.5.26', '<')){
					$params = $slider->get_params();
					$params['version'] = '6.5.26';
					$slider->set_params($params);
					if($this->mode === 'database') $slider->save_params();
					
					$slider->settings['version'] = '6.5.26';
					if($this->mode === 'database') $slider->update_settings(['version' => '6.5.26']);
				}

				$slides = $slider->get_slides(false, true);
				if($this->mode === 'database'){
					$static_id = $sl->get_static_slide_id($slider->get_id());
					if($static_id !== false){
						$msl = new \RevSliderSlide();
						if(strpos($static_id, 'static_') === false) $static_id = 'static_'. $static_id; //$slider->get_id();

						$msl->init_by_id($static_id);
						if($msl->get_id() !== '') $slides = array_merge($slides, [$msl]);
					}
				}else{
					$static = $slider->get_static_slide();
					if($static instanceof \RevSliderSlide) $slides['static'] = $static;
				}
				
				foreach($slides ?? [] as $key => $slide){
					$settings = $slide->get_settings();
					//on slides
					if(version_compare($this->get_val($settings, 'version', '1.0.0'), '6.5.26', '<')){
						$params = $slide->get_params();
						$params['version'] = '6.5.26';
						
						if($this->get_val($params, ['slideChange', 'adpr'], false) === false){
							$this->set_val($params, ['slideChange', 'adpr'], false);
						}

						$slide->set_params($params);
						$slide->save_params();
						
						$slide->settings['version'] = '6.5.26';
						$slide->save_settings();
					}
					
					//on layers
					$layers = $slide->get_layers();
					
					foreach($layers as $lk => $layer){
						$version = $this->get_val($layer, 'version', '1.0.0');
						if(version_compare($version, '6.5.26', '>=')) continue;
						$layers[$lk]['version'] = '6.5.26';
					}

					$slide->set_layers_raw($layers);
					$slides[$key] = $slide;
					if($this->mode === 'database') $slide->save_layers();
				}

				if($this->mode === 'object'){
					if(isset($slides['static'])){
						$slider->_static_slide = $slides['static'];
						unset($slides['static']);
					}
					$slider->slides = $slides;
				}
			}
		}
	}


	/** check to convert the given Slider to latest versions
	 * @since: 6.5.12
	 * @return void
	 **/
	public function upgrade_slider_to_6_5_12(&$sliders = false){
		$sl = new \RevSliderSlide();
		
		if($sliders === false){
			$sr = new \ThemePunch\SR\Core\Slider();
			$sliders = $sr->get_sliders();
		}elseif(!is_array($sliders) || !isset($sliders[0])){
			$sliders = [$sliders];
		}
		
		if(!empty($sliders) && is_array($sliders)){
			foreach($sliders as $slider){
				if(version_compare($slider->get_setting('version', '1.0.0'), '6.5.12', '<')){
					$params = $slider->get_params();
					$params['version'] = '6.5.12';
					$slider->set_params($params);
					if($this->mode === 'database') $slider->save_params();

					$slider->settings['version'] = '6.5.12';
					if($this->mode === 'database') $slider->update_settings(['version' => '6.5.12']);
				}

				$slides = $slider->get_slides(false, true);
				if($this->mode === 'database'){
					$static_id = $sl->get_static_slide_id($slider->get_id());
					if($static_id !== false){
						$msl = new \RevSliderSlide();
						if(strpos($static_id, 'static_') === false) $static_id = 'static_'. $static_id; //$slider->get_id();

						$msl->init_by_id($static_id);
						if($msl->get_id() !== '') $slides = array_merge($slides, [$msl]);
					}
				}else{
					$static = $slider->get_static_slide();
					if($static instanceof \RevSliderSlide) $slides['static'] = $static;
				}
				
				foreach($slides ?? [] as $key => $slide){
					$settings = $slide->get_settings();
					//on slides
					if(version_compare($this->get_val($settings, 'version', '1.0.0'), '6.5.12', '<')){
						$params	= $slide->get_params();
						$params['version'] = '6.5.12';

						$slide->set_params($params);
						$slide->save_params();
						
						$slide->settings['version'] = '6.5.12';
						$slide->save_settings();
					}
					
					//on layers
					$layers = $slide->get_layers();
					
					foreach($layers ?? [] as $lk => $layer){
						$version = $this->get_val($layer, 'version', '1.0.0');
						
						if(version_compare($version, '6.5.12', '>=')) continue;
						$layers[$lk]['version'] = '6.5.12';
						
						//check if parent layer is from type column 
						$puid = $this->get_val($layer, ['group', 'puid'], -1);
						if($puid !== -1 && $this->get_val($layers, [$puid, 'type']) === 'column'){
							$this->set_val($layers, [$lk, 'position', 'position'], 'relative');
						}
					}
					
					$slide->set_layers_raw($layers);
					$slides[$key] = $slide;
					if($this->mode === 'database') $slide->save_layers();
				}

				if($this->mode === 'object'){
					if(isset($slides['static'])){
						$slider->_static_slide = $slides['static'];
						unset($slides['static']);
					}
					$slider->slides = $slides;
				}
			}
		}
	}


	/** check to convert the given Slider to latest versions
	 * changing the position.position attribute
	 * set it to absolute as default
	 * if layer is in column, default is relative
	 * @since: 6.6.0
	 * @return void
	 **/
	public function upgrade_slider_to_6_6_0(&$sliders = false){
		$sl = new \RevSliderSlide();
		
		if($sliders === false){
			$sr = new \ThemePunch\SR\Core\Slider();
			$sliders = $sr->get_sliders();
		}elseif(!is_array($sliders) || !isset($sliders[0])){
			$sliders = [$sliders];
		}
		
		foreach($sliders ?? [] as $slider){
			if(version_compare($slider->get_setting('version', '1.0.0'), '6.6.0', '<')){
				$params = $slider->get_params();
				$params['version'] = '6.6.0';
				$slider->set_params($params);
				if($this->mode === 'database') $slider->save_params();

				$slider->settings['version'] = '6.6.0';
				if($this->mode === 'database') $slider->update_settings(['version' => '6.6.0']);
			}

			$slides = $slider->get_slides(false, true);
			if($this->mode === 'database'){
				$static_id = $sl->get_static_slide_id($slider->get_id());
				if($static_id !== false){
					$msl = new \RevSliderSlide();
					if(strpos($static_id, 'static_') === false) $static_id = 'static_'. $static_id; //$slider->get_id();

					$msl->init_by_id($static_id);
					if($msl->get_id() !== '') $slides = array_merge($slides, [$msl]);
				}
			}else{
				$static = $slider->get_static_slide();
				if($static instanceof \RevSliderSlide) $slides['static'] = $static;
			}
			
			foreach($slides ?? [] as $key => $slide){
				$settings = $slide->get_settings();
				//on slides
				if(version_compare($this->get_val($settings, 'version', '1.0.0'), '6.6.0', '<')){
					$params			= $slide->get_params();
					$params['version'] = '6.6.0';

					$slide->set_params($params);
					if($this->mode === 'database') $slide->save_params();
					
					$slide->settings['version'] = '6.6.0';
					if($this->mode === 'database') $slide->save_settings();
				}
				
				//on layers
				$layers = $slide->get_layers();
				
				$group_uids = [];
				foreach($layers as $lk => $layer){
					if($this->get_val($layer, 'type', 'text') === 'column') $group_uids[] = (string)$this->get_val($layer, 'uid', -1);
				}

				foreach($layers as $lk => $layer){
					$version = $this->get_val($layer, 'version', '1.0.0');
					
					if(version_compare($version, '6.6.0', '>=')) continue;

					$layers[$lk]['version'] = '6.6.0';

					if(in_array($this->get_val($layer, 'type', 'text'), ['column', 'row'], true)) continue; //column and row do not have these values

					$puid = (string)$this->get_val($layer, ['group', 'puid'], -1);
					
					$pos_default = 'absolute';
					//if layer is in a row/column, default is relative
					if($puid !== '-1' && in_array($this->get_val($layers, [$puid, 'type']), ['column', 'row'])){
						$pos_default = 'relative';
					}
					
					$this->set_val($layers, [$lk, 'position', 'position'], $pos_default);
				}

				$slide->set_layers_raw($layers);
				$slides[$key] = $slide;
				if($this->mode === 'database') $slide->save_layers();
			}

			if($this->mode === 'object'){
				if(isset($slides['static'])){
					$slider->_static_slide = $slides['static'];
					unset($slides['static']);
				}
				$slider->slides = $slides;
			}
		}
	}


	/** check to convert the given Slider to latest versions
	 * @since: 6.6.10
	 * @return void
	 **/
	public function upgrade_slider_to_6_6_10(&$sliders = false){
		
		if($sliders === false){
			$sr = new \ThemePunch\SR\Core\Slider();
			$sliders = $sr->get_sliders();
		}elseif(!is_array($sliders) || !isset($sliders[0])){
			$sliders = [$sliders];
		}
		
		foreach($sliders ?? [] as $slider){
			if(version_compare($slider->get_setting('version', '1.0.0'), '6.6.10', '<')){
				$params = $slider->get_params();
				$params['version'] = '6.6.10';
				$slider->set_params($params);
				if($this->mode === 'database') $slider->save_params();
				
				$slider->settings['version'] = '6.6.10';
				if($this->mode === 'database') $slider->update_settings(['version' => '6.6.10']);
			}
		}
	}


	/** check to convert the given Slider to latest versions
	 * @since: 6.6.10
	 * @return void
	 **/
	public function upgrade_slider_to_6_6_20(&$sliders = false){
		$sl = new \RevSliderSlide();
		
		if($sliders === false){
			$sr = new \ThemePunch\SR\Core\Slider();
			$sliders = $sr->get_sliders();
		}elseif(!is_array($sliders) || !isset($sliders[0])){
			$sliders = [$sliders];
		}
		
		foreach($sliders ?? [] as $slider){
			if(version_compare($slider->get_setting('version', '1.0.0'), '6.6.20', '<')){
				$params			= $slider->get_params();
				$json_params	= $_json_params = json_encode($params);
				$_json_params	= str_replace($this->update['6101']['url_from'], $this->update['6101']['url_to'], $_json_params);
				
				if($_json_params !== $json_params){
					$params = (array)json_decode($_json_params, true);
					$params['version'] = '6.6.20';
					$slider->set_params($params);
					if($this->mode === 'database') $slider->save_params();
				}
			}

			$slides = $slider->get_slides(false, true);
			if($this->mode === 'database'){
				$static_id = $sl->get_static_slide_id($slider->get_id());
				if($static_id !== false){
					$msl = new \RevSliderSlide();
					if(strpos($static_id, 'static_') === false) $static_id = 'static_'. $static_id; //$slider->get_id();

					$msl->init_by_id($static_id);
					if($msl->get_id() !== '') $slides = array_merge($slides, [$msl]);
				}
			}else{
				$static = $slider->get_static_slide();
				if($static instanceof \RevSliderSlide) $slides['static'] = $static;
			}

			foreach($slides ?? [] as $key => $slide){
				$settings = $slide->get_settings();
				//on slides
				if(version_compare($this->get_val($settings, 'version', '1.0.0'), '6.6.20', '<')){
					$params			= $slide->get_params();
					$json_params	= $_json_params = json_encode($params);
					$_json_params	= str_replace($this->update['6101']['url_from'], $this->update['6101']['url_from'], $_json_params);
					$params			= ($_json_params !== $json_params) ? (array)json_decode($_json_params, true) : $params;
					$params['version'] = '6.6.20';
					
					$slide->set_params($params);
					if($this->mode === 'database') $slide->save_params();
					
					$slide->settings['version'] = '6.6.20';
					if($this->mode === 'database') $slide->save_settings();
				}
				
				//on layers
				$layers = $slide->get_layers();
				
				foreach($layers ?? [] as $lk => $layer){
					$version = $this->get_val($layer, 'version', '1.0.0');
					
					if(version_compare($version, '6.6.20', '<')){
						$json_layer	 = $_json_layer = json_encode($layer);
						$_json_layer = str_replace($this->update['6101']['url_from'], $this->update['6101']['url_from'], $_json_layer);
						if($_json_layer !== $json_layer){
							$layers[$lk] = (array)json_decode($_json_layer, true);
						}
						$layers[$lk]['version'] = '6.6.20';
					}
				}
				
				$slide->set_layers_raw($layers);
				$slides[$key] = $slide;
				if($this->mode === 'database') $slide->save_layers();
			}

			if($this->mode === 'object'){
				if(isset($slides['static'])){
					$slider->_static_slide = $slides['static'];
					unset($slides['static']);
				}
				$slider->slides = $slides;
			}

			$slider->settings['version'] = '6.6.20';
			if($this->mode === 'database') $slider->update_settings(['version' => '6.6.20']);
		}

		if($this->mode === 'object'){
			if(isset($slides['static'])){
				$slider->_static_slide = $slides['static'];
				unset($slides['static']);
			}
			$slider->slides = $slides;
		}
	}


	/**
	 * change svg path of all layers from the upload folder if 5.2.5.3+ was installed
	 * @since 6.7.21
	 * @return void
	 */
	public function upgrade_slider_to_6_7_21(&$sliders = false){
		$sl = new \RevSliderSlide();
		$path = $this->remove_http(RS_PLUGIN_URL.'public/assets/assets/svg/'); //historic needle: v6 data stored the pre-7.2.1 layout
		$new_path = $this->remove_http(RS_PLUGIN_URL .'core/public/assets/svg/');
		
		if($sliders === false){
			$sr = new \ThemePunch\SR\Core\Slider();
			$sliders = $sr->get_sliders();
		}elseif(!is_array($sliders) || !isset($sliders[0])){
			$sliders = [$sliders];
		}
		
		foreach($sliders ?? [] as $slider){
			$slides = $slider->get_slides(false, true);

			if($this->mode === 'database'){
				$static_id = $sl->get_static_slide_id($slider->get_id());
				if($static_id !== false){
					$msl = new \RevSliderSlide();
					if(strpos($static_id, 'static_') === false) $static_id = 'static_'. $static_id; //$slider->get_id();

					$msl->init_by_id($static_id);
					if($msl->get_id() !== '') $slides = array_merge($slides, [$msl]);
				}
			}else{
				$static = $slider->get_static_slide();
				if($static instanceof \RevSliderSlide) $slides['static'] = $static;
			}

			foreach($slides as $key => $slide){
				$layers = $slide->get_layers();
				foreach($layers ?? [] as $lk => $layer){
					if(isset($layer['type']) && $layer['type'] == 'svg'){
						if(isset($layer['svg']) && isset($layer['svg']['source'])){
							//change newer path to older path
							if(strpos($layers[$lk]['svg']['source'], $path) !== false){
								$layers[$lk]['svg']['source'] = str_replace($path, $new_path, $layers[$lk]['svg']['source']);
							}
						}
					}
					
					$layers[$lk]['version'] = '6.6.21';
				}
				
				$slide->set_layers_raw($layers);
				if($this->mode === 'database') $slide->save_layers();

				$slide->settings['version'] = '6.6.21';
				if($this->mode === 'database') $slide->save_settings();

				$slide->set_layers_raw($layers);
				$slides[$key] = $slide;
				if($this->mode === 'database') $slide->save_layers();
			}

			if($this->mode === 'object'){
				if(isset($slides['static'])){
					$slider->_static_slide = $slides['static'];
					unset($slides['static']);
				}
				$slider->slides = $slides;
			}
			
			$slider->settings['version'] = '6.6.21';
			if($this->mode === 'database') $slider->update_settings(['version' => '6.6.21']);
		}
	}


	/** check to convert the given Slider to latest versions
	 * @since: 6.6.10
	 * @return void
	 **/
	public function upgrade_slider_to_6_7_24(&$sliders = false){
		
		if($sliders === false){
			$sr = new \ThemePunch\SR\Core\Slider();
			$sliders = $sr->get_sliders();
		}elseif(!is_array($sliders) || !isset($sliders[0])){
			$sliders = [$sliders];
		}
		
		foreach($sliders ?? [] as $slider){
			if(version_compare($slider->get_setting('version', '1.0.0'), '6.7.24', '<')){
				$params = $slider->get_params();
				$params['version'] = '6.7.24';
				$slider->set_params($params);
				if($this->mode === 'database') $slider->save_params();
				
				$slider->settings['version'] = '6.7.24';
				if($this->mode === 'database') $slider->update_settings(['version' => '6.7.24']);
			}
		}
	}
	
	/**
	 * check all folders, and move them to the v7 tables once
	 * @return bool
	 */
	public function move_folder(){
		if(self::do_v6_tables_exist() === false) return false;

		$folders = $this->database()->get_results("SELECT * FROM ". $this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDER_V6 ." WHERE `type` = 'folder' ORDER BY id ASC", 'ARRAY_A');
		foreach($folders ?? [] as $folder){
			$add = [
				'id'	=> $this->get_val($folder, 'id'),
				'title'	=> $this->get_val($folder, 'title'),
				'alias'	=> $this->get_val($folder, 'alias'),
				'settings'	=> $this->get_val($folder, 'settings'),
				'type'	=> $this->get_val($folder, 'type'),
			];
			$this->database()->insert($this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDER, $add);
		}
	}

	/**
	 * get a map of slide ids for v7 slides
	 * we need this in the process to migrate v7 slides
	 * as we merge normal and static slides here
	 * @return array|false maps a v6 slider id to the v7 one created from it
	 **/
	public function get_v7_slider_map($v6_slider_id = false, $v6_slide_id = false){
		if(self::do_v6_tables_exist() === false) return false;

		$slide_map 	= $this->option_storage()->get('sliderrevolution-v7-slide-map', []);
		$update		= false;

		if(empty($slide_map)) $update = true;
		//if($v6_slider_id !== false && intval($v6_slider_id) === 0) return;
		if($v6_slider_id !== false && !isset($slide_map[$v6_slider_id])) $update = true;
		if($v6_slide_id !== false){
			if($this->get_v7_slide_map($v6_slide_id) === false) $update = true;
		}

		if($update === true) $slide_map = $this->update_v7_slide_id_map();

		if($v6_slider_id === false && $v6_slide_id === false) return $slide_map;
		if(empty($slide_map)) return false;

		if($v6_slider_id !== false){
			return (!isset($slide_map[$v6_slider_id])) ? false : $slide_map[$v6_slider_id];
		}else{
			$v7_slide_id = $this->get_v7_slide_map($v6_slide_id);
			if($v7_slide_id !== false) return $v7_slide_id;
		}

		return false;
	}

	/**
	 * retrieves the v7 slide id by a v6 slide id from the slide map
	 * @return array|false
	 **/
	public function get_v7_slide_map($v6_slide_id){
		if(self::do_v6_tables_exist() === false) return false;

		$slide_map 	= $this->option_storage()->get('sliderrevolution-v7-slide-map', []);
		if(empty($slide_map)) return false;

		$_type = (strpos($v6_slide_id, 'static_') !== false) ? 's' : 'n';
		$v6_slide_id = str_replace('static_', '', $v6_slide_id);
		foreach($slide_map as $v6_sid => $type){
			foreach($type as $t => $v){
				if($t !== $_type) continue;
				if(isset($v[$v6_slide_id])) return $v[$v6_slide_id];
			}
		}

		return false;
	}

	/**
	 * retrieves the v6 slide id by a v7 slide id from the slide map
	 * @return array|false
	 **/
	public function get_v6_slide_by_v7_id($v7_slide_id){
		if(self::do_v6_tables_exist() === false) return false;

		$slide_map 	= $this->option_storage()->get('sliderrevolution-v7-slide-map', []);
		if(empty($slide_map)) return false;

		foreach($slide_map as $module => $slides){
			$_slides = $this->get_val($slides, 'n', []);
			if(empty($_slides)) continue;
			if(!in_array($v7_slide_id, $_slides)) continue;
			foreach($_slides as $v6 => $v7){
				if($v7 == $v7_slide_id) return $v6;
			}
			return false;
		}

		return false;
	}

	/**
	 * this will remove a v6 slider in total or a v6 slide id from the map
	 * @return bool
	 **/
	public function remove_v7_slider_from_map($v6_slider_id = false, $v6_slide_id = false){
		if(self::do_v6_tables_exist() === false) return false;

		$slide_map = $this->option_storage()->get('sliderrevolution-v7-slide-map', []);
		if(empty($slide_map)) return true;

		if($v6_slider_id !== false){
			if(!isset($slide_map[$v6_slider_id])) return true;

			unset($slide_map[$v6_slider_id]);
			$this->option_storage()->set('sliderrevolution-v7-slide-map', $slide_map);

			return true;
		}else{
			$_type = (strpos('static_', $v6_slide_id) !== false) ? 's' : 'n';
			$v6_slide_id = str_replace('static_', '', $v6_slide_id);
			foreach($slide_map as $v6_sid => $type){
				foreach($type as $t => $v){
					if($t !== $_type) continue;
					if(!isset($v[$v6_slide_id])) continue;

					unset($slide_map[$v6_sid][$_type][$v6_slide_id]);
					$this->option_storage()->set('sliderrevolution-v7-slide-map', $slide_map);

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * create/update the map of slide ids for v7 slides
	 * @return array
	 **/
	public function update_v7_slide_id_map(){
		if(self::do_v6_tables_exist() === false) return [];

		$slide_map = $this->option_storage()->get('sliderrevolution-v7-slide-map', []);

		$v6 = [];
		$v6[] = ['static' => false, 'slides' => $this->database()->get_results("SELECT `id`, `slider_id` FROM ".$this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDES_V6." ORDER BY id, slider_id, slide_order ASC", 'ARRAY_A')];
		if(empty($v6[0]['slides'])) return $slide_map;
		$v6[] = ['static' => true, 'slides' => $this->database()->get_results("SELECT `id`, `slider_id` FROM ".$this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_STATIC_SLIDES." ORDER BY id, slider_id ASC", 'ARRAY_A')];

		if(empty($slide_map)){
			//$this->truncate_v7();
			$result = $this->database()->get_row("SHOW TABLE STATUS LIKE '".$this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDES."'");
			$v7_next_increment = intval($this->get_val($result, 'Auto_increment', 1));
		}else{
			$v7_next_increment = 0;
			foreach($slide_map as $sid => $slides){
				foreach($slides as $slide){
					if(empty($slide)) continue;
					foreach($slide as $v7id){
						if($v7id > $v7_next_increment) $v7_next_increment = $v7id;
					}
				}
			}
			$v7_next_increment++;
		}

		foreach($v6 as $v6_slides){
			foreach($v6_slides['slides'] as $v6_slide){
				$found = false;
				//search if $v6_slide['id'] exists in oid already
				foreach($slide_map ?? [] as $map){
					foreach($map as $type => $slides){
						if(empty($slides)) continue;
						if(!isset($slides[$v6_slide['id']])) continue;
						if($v6_slides['static'] === true && $type !== 's') continue;
						if($v6_slides['static'] === false && $type !== 'n') continue;
						$found = true;
						break;
					}
					if($found === true) break;
				}

				if($found !== false) continue; //already registered
				
				if(!isset($slide_map[$v6_slide['slider_id']])) $slide_map[$v6_slide['slider_id']] = ['s' => [], 'n' => []];

				$k = ($v6_slides['static'] === true) ? 's' : 'n';
				$slide_map[$v6_slide['slider_id']][$k][$v6_slide['id']] = $v7_next_increment;

				$v7_next_increment++;
			}
		}
		
		//create/update a mapping of v7
		$this->option_storage()->set('sliderrevolution-v7-slide-map', $slide_map);

		return $slide_map;
	}

	/** @return bool */
	public function set_v6_migration_started($slider_id){
		global $SR_GLOBALS;
		if(self::do_v6_tables_exist() === false) return false;

		$SR_GLOBALS['v6'] = true;
		$slider = new \ThemePunch\SR\Core\Slider();
		$slider->init_by_id($slider_id);

		$slider->update_settings(['migrated' => false]);
		$SR_GLOBALS['v6'] = false;
	}

	
	/**
	 * a v6 package becomes a v7 module only in the browser; an import with no browser behind it (a theme's demo
	 * import) leaves an empty shell, so its payload waits here for the module overview to migrate it
	 * @since 7.2.5
	 * @return void
	 */
	public function set_pending_import($slider_id, $obj){
		$json = json_encode($obj);
		if($json === false) return;

		$slider_id = intval($slider_id);
		$ids	   = $this->get_pending_ids();
		$ids[]	   = $slider_id;

		$this->option_storage()->set(self::PENDING_IMPORTS.'_'.$slider_id, $json, false);
		$this->update_option(self::PENDING_KEY, array_values(array_unique($ids)));
	}

	/**
	 * the ids live in sr-options, which every admin page reads anyway, so asking costs no query
	 * @return array
	 */
	public function get_pending_ids(){
		$ids = $this->get_options(self::PENDING_KEY, []);
		return is_array($ids) ? array_map('intval', $ids) : [];
	}

	/**
	 * @return array|false the payload slider_upgrade_v6 hands the browser migration, false when none waits
	 */
	public function get_pending_import($slider_id){
		$json = $this->option_storage()->get(self::PENDING_IMPORTS.'_'.intval($slider_id), '');
		//decoded as objects so every {} and [] goes back to the browser the way the import sent it
		$obj  = (is_string($json) && $json !== '') ? json_decode($json) : null;

		return is_object($obj) ? (array)$obj : false;
	}

	/**
	 * @return array ids of imported v6 modules still waiting for their migration; deleted modules are dropped
	 */
	public function get_pending_imports(){
		$ids = $this->get_pending_ids();
		if(empty($ids)) return [];

		//a payload holds for the row it was made for: SR6 6.7 empties and refills the v7 tables with ids of its own
		$db	  = $this->database();
		$rows = $db->get_results('SELECT id, alias FROM '.$db->prefix().\ThemePunch\SR\Core\Data::TABLE_SLIDER.' WHERE id IN ('.implode(',', $ids).')', 'ARRAY_A');
		$live = [];
		foreach(is_array($rows) ? $rows : [] as $row){
			$p = $this->get_pending_import($row['id']);
			if($p === false || $this->get_val($p, 'alias') !== $row['alias']) continue;

			$sids = [];
			foreach((array)$this->get_val($p, 'slides', []) as $s) $sids[] = intval($s->id ?? 0);
			$sids[] = intval($this->get_val($p, ['static_slide', 'id'], 0));
			$sids	= array_filter($sids);
			if(!empty($sids) && intval($db->get_var('SELECT COUNT(*) FROM '.$db->prefix().\ThemePunch\SR\Core\Data::TABLE_SLIDES.' WHERE id IN ('.implode(',', $sids).') AND slider_id != '.intval($row['id']))) > 0) continue;

			$live[] = intval($row['id']);
		}

		foreach(array_diff($ids, $live) as $gone) $this->clear_pending_import($gone);

		return array_values(array_intersect($ids, $live));
	}

	/**
	 * whether a page should run the overview's migration unseen: imports wait, no run holds the lock (it lasts
	 * while its own modules wait, at most its time budget, since a page left early kills a run), and five runs
	 * that saved nothing leave it to the overview
	 * @return bool
	 */
	public function pending_migration_due(){
		$ids = $this->get_pending_ids();
		if(empty($ids)) return false;

		$try = $this->get_options(self::PENDING_TRY, []);
		if(!is_array($try) || !isset($try['until'])) return true; //none yet, or the [time, count] shape of beta 0.3 to 0.5
		if(intval($try['n'] ?? 0) >= 5) return false;

		return intval($try['until']) < time() || empty(array_intersect(array_map('intval', (array)($try['ids'] ?? [])), $ids));
	}

	/**
	 * takes the lock for the run that migrates these ids
	 * @return void
	 */
	public function set_pending_try($ids){
		if(empty($ids)) return; //a bare sr_migrate=1 link must not use up the runs
		$try = $this->get_options(self::PENDING_TRY, []);
		$n	 = (is_array($try) && isset($try['until'])) ? intval($try['n'] ?? 0) : 0;
		$this->update_option(self::PENDING_TRY, ['until' => time() + min(300, 20 + 15 * count($ids)), 'ids' => array_values($ids), 'n' => $n + 1]);
	}

	/** @return void */
	public function clear_pending_import($slider_id){
		$slider_id = intval($slider_id);
		$ids	   = $this->get_pending_ids();
		if(!in_array($slider_id, $ids, true)) return;

		$this->option_storage()->delete(self::PENDING_IMPORTS.'_'.$slider_id);
		$ids = array_values(array_diff($ids, [$slider_id]));
		if(empty($ids)){
			$this->delete_option(self::PENDING_KEY);
			$this->delete_option(self::PENDING_TRY);
			return;
		}
		$this->update_option(self::PENDING_KEY, $ids);

		//the run saved all of its modules: the lock goes, and the count of runs without progress starts again
		$try = $this->get_options(self::PENDING_TRY, []);
		if(is_array($try) && isset($try['ids']) && empty(array_intersect(array_map('intval', (array)$try['ids']), $ids))) $this->delete_option(self::PENDING_TRY);
	}

	/**
	 * a module migrated without a licence leaves its add-on parts out; it is marked, with a hash of how it moved so a
	 * licence later migrates it again only while nobody changed it, and an imported payload is kept as its SR6 source
	 * @since 7.2.5
	 * @return void
	 */
	public function set_migrated_without($slider_id, $addons){
		$slider_id = intval($slider_id);
		$addons	   = array_values(array_filter(array_map(fn($a) => preg_replace('/[^a-z0-9]/', '', strtolower((string)$a)), (array)$addons)));
		if($slider_id <= 0 || empty($addons)) return;

		$payload = $this->option_storage()->get(self::PENDING_IMPORTS.'_'.$slider_id, '');
		if(is_string($payload) && $payload !== '') $this->option_storage()->set(self::PENDING_IMPORTS.'_source_'.$slider_id, $payload, false);

		$this->update_option(array_merge(self::WITHOUT, [$slider_id]), ['addons' => $addons, 'hash' => $this->module_hash($slider_id)]);
	}

	/**
	 * @return string md5 over the module's params and its slides as stored, '' when it does not exist
	 */
	public function module_hash($slider_id){
		$db	 = $this->database();
		$p	 = $db->prefix();
		$sl	 = $db->get_var($db->prepare('SELECT params FROM '.$p.\ThemePunch\SR\Core\Data::TABLE_SLIDER.' WHERE id = %d', intval($slider_id)));
		if($sl === null) return '';
		$rows = $db->get_results($db->prepare('SELECT id, params, layers FROM '.$p.\ThemePunch\SR\Core\Data::TABLE_SLIDES.' WHERE slider_id = %d ORDER BY id', intval($slider_id)), 'ARRAY_A');

		return md5($sl.json_encode($rows));
	}

	/** @return bool */
	public function set_v6_migration_finished($slider_id){
		global $SR_GLOBALS;
		$this->clear_pending_import($slider_id);
		if(self::do_v6_tables_exist() === false) return false;

		$SR_GLOBALS['v6'] = true;
		$slider = new \ThemePunch\SR\Core\Slider();
		$slider->init_by_id($slider_id);

		$slider->update_settings(['migrated' => true]);
		$SR_GLOBALS['v6'] = false;
	}


	/** @return true|string true on success, the error message otherwise */
	public static function delete_v6_tables(){
		$db = \ThemePunch\SR\Core\Registry::instance()->get('RevSliderFunctions')->database(); //static context, no $this

		try{
			$tables = [
				\ThemePunch\SR\Core\Data::TABLE_SLIDER_V6, //old Slider table
				\ThemePunch\SR\Core\Data::TABLE_SLIDES_V6, //old Slide table
				\ThemePunch\SR\Core\Data::TABLE_STATIC_SLIDES, //old Static Slide table
				\ThemePunch\SR\Core\Data::TABLE_CSS_V6 //old CSS table
			];
			foreach($tables as $table){
				$exists	= $db->get_row("SHOW TABLES LIKE '". $db->prefix() . $table ."';");

				if($exists) $db->query("DROP TABLE IF EXISTS `".$db->prefix() . $table ."`");
			}
		}catch(\Exception $e){
			return $e->getMessage();
		}

		return true;
	}
}