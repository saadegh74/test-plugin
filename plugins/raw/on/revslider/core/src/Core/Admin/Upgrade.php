<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

namespace ThemePunch\SR\Core\Admin;

if(!defined('ABSPATH')) exit();

/**
 * Data migrations *within* SR7 - not plugin updates (that is RevSliderUpdate).
 *
 * do_update_checks() compares the stored data version against the code and runs the pending upgrade steps
 * in order, bumping the version after each one. The upgrade_* methods are therefore append-only history:
 * each runs exactly once per installation and must stay working against the data shape of its time.
 */
class Upgrade extends \ThemePunch\SR\Core\Functions {

	public $revision;
	public $import		= false;
	public $navtypes	= ['arrows', 'thumbs', 'bullets', 'tabs', 'scrubber'];

	public function __construct(){
		$this->revision = $this->get_version();
	}

	/**
	 * return version of installation
	 * @return string the version the stored data was last upgraded to
	 */
	public function get_version(){
		return $this->get_options(['update', 'latest-version'], $this->option_storage()->get('revslider_update_version', '6.0.0'));
	}

	/**
	 * set version of installation
	 * @return void
	 */
	public function set_version($set_to){
		$this->update_option(['update', 'latest-version'], $set_to);
	}

	/**
	 * set import value
	 * @return void
	 */
	public function set_import($import){
		$this->import = $import;
	}

	/**
	 * check for updates and proceed if needed
	 * using set_version() requires, that ALL slider need to be upgraded to that set version
	 * so in upgrade_slider_to_latest(), the same version needs to be set!
	 * @return void runs the pending option/data upgrades for the installed version
	 */
	public static function do_update_checks(){
		//fast path: read the stored version via the shared singleton and bail before constructing the
		//updater once we are already current - do_update_checks runs on plugins_loaded for every request (front + admin)
		$f		 = \ThemePunch\SR\Core\Registry::instance()->get('RevSliderFunctions');
		$version = $f->get_options(['update', 'latest-version'], $f->option_storage()->get('revslider_update_version', '6.0.0'));
		//the asset URL step has a marker of its own, see below: the version alone does not say it ran
		if(version_compare($version, '7.2.1', '>=') && $f->get_options(['update', 'asset-urls'], '') === 'core') return;

		$upd	 = new self();
		//nothing stored by 6.x or 7.x yet: a new install
		$fresh	 = $f->get_options(['update', 'latest-version'], '') === '' && $f->option_storage()->get('revslider_update_version', '') === '' && !UpgradeV6::do_v6_tables_exist();

		//add this so that sliders will be updated if under 6.4.10
		if(version_compare($version, '6.4.10', '<')){
			$upd->change_navigation_settings_to_6_4_10();
			$upd->set_version('6.4.10');
		}

		if(version_compare($version, '7.0.0.1', '<')){
			//update all options into smaller options
			$upd->upgrade_options_to_7_0_0();
			$upd->fix_folders();
			$upd->set_version('7.0.0.1');
		}

		if(version_compare($version, '7.0.0.2', '<')){
			//update all options into smaller options
			$upd->upgrade_globals_to_7_0_0();
			$upd->set_version('7.0.0.2');
		}

		if(version_compare($version, '7.0.0.3', '<')){
			//update all options into smaller options
			$upd->upgrade_options_to_7_0_3();
			$upd->set_version('7.0.0.3');
		}

		if(version_compare($version, '7.0.0.4', '<')){
			//update all options into smaller options
			$upd->upgrade_template_library_to_7_0_4();
			$upd->set_version('7.0.0.4');
		}

		if(version_compare($version, '7.0.1', '<')){
			$upd->upgrade_options_to_7_0_1();
			$upd->set_version('7.0.1');
		}
		
		if(version_compare($version, '7.0.7', '<')){
			$upd->upgrade_options_to_7_0_7();
			$upd->set_version('7.0.7');
		}
		
		if(version_compare($version, '7.0.9', '<')){
			$upd->set_version('7.0.9');
		}

		if(version_compare($version, '7.1.1', '<')){
			$upd->move_fonts_to_own_option();
			$upd->set_version('7.1.1');
		}

		//Keyed to a marker, not to the version: test builds called themselves 7.2.1 before this tree existed, so the
		//number says nothing about whether a site's stored URLs have moved. The step is idempotent
		if($upd->get_options(['update', 'asset-urls'], '') !== 'core'){
			$upd->upgrade_asset_urls_to_core();
			$upd->update_option(['update', 'asset-urls'], 'core');
		}
		if(version_compare($version, '7.2.1', '<')) $upd->set_version('7.2.1');
		//after the chain, upgrade_globals_to_7_0_0 rebuilds the settings from a fixed list
		if($fresh) \ThemePunch\SR\Core\Registry::instance()->get('ThemePunch\SR\Core\Thumbs')->seed_new_install();
	}

	/**
	 * move the fonts branch out of the autoloaded 'sr-options' into its own, non autoloaded option.
	 * the branch holds the downloaded google font css + the collected font list and made up ~97% of
	 * 'sr-options', which WordPress loads and unserializes on *every* request (front, admin, ajax, rest, cron).
	 * the key path stays identical ('fonts' => ['fonts' => ..., 'collected' => ...]), only the option it lives in
	 * changes, so all readers keep working by passing self::OPTIONS_FONTS as the field.
	 * @return void
	 */
	public function move_fonts_to_own_option(){
		$options = $this->get_options([], '', true); //true = read fresh from the db, not the request cache
		if(!is_array($options) || !isset($options['fonts'])) return;

		$fonts = $options['fonts'];
		unset($options['fonts']);

		//write the new option first: if the second write ever failed we would rather have the data twice than lost
		$this->update_all_options(['fonts' => $fonts], self::OPTIONS_FONTS);
		$this->update_all_options($options);
	}

	/**
	 * 7.2.1 moves the engine assets from public/ to core/public/. Modules store absolute URLs into
	 * public/assets (library SVGs, stream placeholders), so the stored data follows the files.
	 * The needle is anchored on the plugin's own URL path - foreign URLs merely containing 'public/'
	 * stay untouched, and the replaced form cannot match again, so the step is idempotent.
	 * @since 7.2.1
	 * @return void
	 */
	public function upgrade_asset_urls_to_core(){
		$db		= $this->database();
		$tables	= [
			\ThemePunch\SR\Core\Data::TABLE_SLIDER			=> ['params'],
			\ThemePunch\SR\Core\Data::TABLE_SLIDES			=> ['params', 'layers'],
			\ThemePunch\SR\Core\Data::TABLE_SLIDER_PREVIEW	=> ['params'],
			\ThemePunch\SR\Core\Data::TABLE_SLIDES_PREVIEW	=> ['params', 'layers'],
		];
		foreach($this->get_asset_url_needles() as $pair){
			foreach($tables as $table => $cols){
				foreach($cols as $col){
					$db->query($db->prepare('UPDATE '.$db->prefix().$table.' SET `'.$col.'` = REPLACE(`'.$col.'`, %s, %s)', $pair[0], $pair[1]));
				}
			}
		}
	}

	/**
	 * the plugin-path anchored 'public/' prefix in plain and JSON-escaped form (the DB stores both)
	 * @since 7.2.1
	 * @return array [old, new] pairs, empty when no URL path is resolvable
	 */
	public function get_asset_url_needles(){
		$path = (string)parse_url(RS_PLUGIN_URL, PHP_URL_PATH);
		if($path === '') return [];

		return [
			[$path.'public/', $path.'core/public/'],
			[str_replace('/', '\/', $path.'public/'), str_replace('/', '\/', $path.'core/public/')],
		];
	}
	
	/** @return void delegates to the WP glue - the plugin machinery lives in wordpress/admin/includes/upgrade-glue.class.php */
	public static function do_remove_addon_checks(){
		\RevSliderWpUpgradeGlue::do_remove_addon_checks();
	}

	/** @return void delegates to the WP glue */
	public static function do_update_addon_checks(){
		\RevSliderWpUpgradeGlue::do_update_addon_checks();
	}

	/** @return void delegates to the WP glue */
	public static function do_wpml_addon_check(){
		\RevSliderWpUpgradeGlue::do_wpml_addon_check();
	}

	/**
	 * check to convert the given Slider to latest versions
	 * it needs to be ensured, that upgrade_slider_to_version() is called at the end
	 * @return void
	 **/
	public function upgrade_slider_to_latest($slider){
		$version = $slider->get_setting('version', '1.0.0');

		if(version_compare($version, '7.0.0', '<')){
			$this->upgrade_slider_to_7_0_0($slider);
		}

		if(version_compare($version, '7.0.9', '<')){
			$this->upgrade_slider_to_7_0_9($slider);
		}

		if(version_compare($version, '7.2.1', '<')){
			$this->upgrade_slider_asset_urls($slider);
		}

		$this->upgrade_slider_to_version($slider, $this->revision);
	}

	/**
	 * per-module twin of upgrade_asset_urls_to_core() for data arriving after the global step
	 * already ran (v6 render-time migrations, old imports) - rewrites this module's rows only.
	 * The in-memory objects keep the old URLs for the running request; the next load is clean.
	 * @since 7.2.1
	 * @return void
	 */
	public function upgrade_slider_asset_urls($slider){
		$id = intval($slider->get_id());
		if($id < 1) return;

		$db = $this->database();
		foreach($this->get_asset_url_needles() as $pair){
			$db->query($db->prepare('UPDATE '.$db->prefix().\ThemePunch\SR\Core\Data::TABLE_SLIDER.' SET `params` = REPLACE(`params`, %s, %s) WHERE `id` = %d', $pair[0], $pair[1], $id));
			foreach(['params', 'layers'] as $col){
				$db->query($db->prepare('UPDATE '.$db->prefix().\ThemePunch\SR\Core\Data::TABLE_SLIDES.' SET `'.$col.'` = REPLACE(`'.$col.'`, %s, %s) WHERE `slider_id` = %d', $pair[0], $pair[1], $id));
			}
		}
	}

	
	/**
	 * check if there are still Slider below latest version, if yes then add JavaScript to the header
	 * @return bool is any slider still on an older data version?
	 **/
	public function slider_need_update_checks(){
		$finished = $this->get_options(['update', 'revision-version'], '1.0.0');

		return (version_compare($finished, $this->revision, '<')) ? true : false;
	}

	/**
	 * get the next slider that is not on the latest version and update it to the latest
	 * @return array status of the upgrade step
	 **/
	public function upgrade_next_slider(){
		$slr	 = new \ThemePunch\SR\Core\Slider();
		$sliders = $slr->get_sliders();

		foreach($sliders ?? [] as $slider){
			$version = $this->get_val($slider, ['settings', 'version']);
			
			if(version_compare($version, $this->revision, '>=')) continue;

			$this->upgrade_slider_to_latest($slider);

			//check if slider is now on $this->revision
			$id			= $slider->get_id();
			$_slider	= new \ThemePunch\SR\Core\Slider();
			$_slider->init_by_id($id);
			$_version	= $this->get_val($_slider, ['settings', 'version']);

			return (version_compare($_version, $this->revision, '<')) ? ['status' => 'error'] : ['status' => 'next'];
		}

		//we can only get to this point, after all Sliders have been updated to the latest revision
		$this->update_option(['update', 'revision-version'], $this->revision);

		return ['status' => 'finished'];
	}

	/**
	 * Change navigation css that needs to be used since 6.4.9
	 * @since: 6.4.9
	 * @return void
	 **/
	public function change_navigation_settings_to_6_4_10($navs = false, $return = false){
		/**
		 * some customers had an version inbetween, where $find was wrongly translated into this here
		 * so we need to replace $find2 also with $replace and this has to happen first!
		 **/
		$find2 = [
			'.tp-bullets:hover.rs.touchhover',
			'.tp-bullet.rs.touchhover',
			'.tp-tab.rs.touchhover',
			'.tp-tabs.rs.touchhover',
			'.tp-thumb.rs.touchhover',
			'.tp-thumbs.rs.touchhover',
			'.tparrows.rs-touchhover',
			'.tp-rightarrow.rs.touchhover',
			'.tp-leftarrow.rs.touchhover'
		];
		$find = [
			'.tp-bullets:hover',
			'.tp-bullet:hover',
			'.tp-tab:hover',
			'.tp-tabs:hover',
			'.tp-thumb:hover',
			'.tp-thumbs:hover',
			'.tparrows:hover',
			'.tp-rightarrow:hover',
			'.tp-leftarrow:hover'
		];
		$replace = [
			'.tp-bullets.rs-touchhover',
			'.tp-bullet.rs-touchhover',
			'.tp-tab.rs-touchhover',
			'.tp-tabs.rs-touchhover',
			'.tp-thumb.rs-touchhover',
			'.tp-thumbs.rs-touchhover',
			'.tparrows.rs-touchhover',
			'.tp-rightarrow.rs-touchhover',
			'.tp-leftarrow.rs-touchhover'
		];
		
		$rs_nav = new \ThemePunch\SR\Core\Navigation();
		//do on all navigations ?
		$navs = ($navs === false) ? $rs_nav->get_all_navigations(false, false, true) : (array) $navs;
		
		if(!empty($navs)){
			//now push all again back in with new IDs
			foreach($navs as $id => $nav){
				$css = $this->get_val($nav, 'css');
				$css = str_replace($find2, $replace, $css);
				$css = str_replace($find, $replace, $css);
				if($css !== $this->get_val($nav, 'css')){
					//update the css
					$response = $this->database()->update(
						$this->database()->prefix().\ThemePunch\SR\Core\Data::TABLE_NAVIGATIONS,
						['css' => $css],
						['id' => $this->get_val($nav, 'id')]
					);
				}
			}
		}
	}

	/** @return void */
	public function upgrade_options_to_7_0_0(){
		$_options = $this->get_options();

		$options = [
			'system' => [
				'valid'				=> $this->option_storage()->get('revslider-valid',					$this->get_val($_options, ['system', 'valid'], 'false')),
				'version'			=> $this->option_storage()->get('revslider-latest-version',			$this->get_val($_options, ['system', 'version'], RS_REVISION)),
				'stable'			=> $this->option_storage()->get('revslider-stable-version',			$this->get_val($_options, ['system', 'stable'], '4.2')),
				'license'			=> $this->option_storage()->get('revslider-code',						$this->get_val($_options, ['system', 'license'], '')),
				'connect'			=> $this->option_storage()->get('revslider-connection',				$this->get_val($_options, ['system', 'connect'], false)),
				'servers'			=> $this->option_storage()->get('revslider_servers',					$this->get_val($_options, ['system', 'servers'], [])),
				'uid'				=> $this->option_storage()->get('revslider-uid',						$this->get_val($_options, ['system', 'uid'], '')),
				'overlay'			=> $this->option_storage()->get('rs_cache_overlay',					$this->get_val($_options, ['system', 've'], '1.0.0')),
				'deregister'		=> $this->option_storage()->get('revslider-deregister-popup',			$this->get_val($_options, ['system', 'deregister'], 'false')),
				'deregister-msg'	=> $this->option_storage()->get('revslider-deregister-message',		$this->get_val($_options, ['system', 'deregister-msg'], '')),
				'table'				=> $this->option_storage()->get('revslider_table_version',			$this->get_val($_options, ['system', 'table'], '1.0.0')),
				'tooltips'			=> $this->option_storage()->get('revslider_hide_tooltips',			$this->get_val($_options, ['system', 'tooltips'], 'false')),
				'settings'			=> $this->option_storage()->get('revslider-global-settings',			$this->get_val($_options, ['system', 'settings'], '')),
				'additions'			=> $this->option_storage()->get('revslider-additions',				$this->get_val($_options, ['system', 'additions'], [])),
				'trustpilot'		=> $this->option_storage()->get('revslider-trustpilot',				$this->get_val($_options, ['system', 'trustpilot'], 'false')),
			],
			'update' => [
				'information'	 	=> $this->option_storage()->get('revslider_update_info',				$this->get_val($_options, ['update', 'information'], false)),
				'latest-version' 	=> $this->option_storage()->get('revslider_update_version',			$this->get_val($_options, ['update', 'latest-version'], '6.0.0')),
				'revision-version'	=> $this->option_storage()->get('revslider_update_revision_current',	$this->get_val($_options, ['update', 'revision-version'], '1.0.0')),
			],
			'overview' => [
				'notices'			=> $this->option_storage()->get('revslider-notices',					$this->get_val($_options, ['overview', 'notices'], [])),
				'notices-dc'		=> $this->option_storage()->get('revslider-notices-dc',				$this->get_val($_options, ['overview', 'notices-dc'], [])),
			],
			'timestamps' => [
				'servers'			=> $this->option_storage()->get('revslider_server_refresh',			$this->get_val($_options, ['timestamps', 'servers'], false)),
				'templates'			=> $this->option_storage()->get('revslider-templates-check',			$this->get_val($_options, ['timestamps', 'templates'], false)),
				'library'			=> $this->option_storage()->get('revslider-library-check',			$this->get_val($_options, ['timestamps', 'library'], false)),
				//not SR6's: a lifetime key it carried over counted as valid for up to two days, until the next check
				'update'			=> false,
				'update-short'		=> false,
				'google-fonts'		=> $this->option_storage()->get('tp_google_font',						$this->get_val($_options, ['timestamps', 'google-fonts'], 0)),
			],
			'hashes'	=> [
				'templates'			=> $this->option_storage()->get('revslider-templates-hash',			$this->get_val($_options, ['hashes', 'templates'], '')),
				'templates-top'		=> $this->option_storage()->get('revslider-templates-top-hash',		$this->get_val($_options, ['hashes', 'templates-top'], '')),
				'library'			=> $this->option_storage()->get('revslider-library-hash',				$this->get_val($_options, ['hashes', 'valid'], '')),
				'update'			=> $this->option_storage()->get('revslider-update-hash',				$this->get_val($_options, ['hashes', 'update'], '')),
			],
			'tracking'	=> [
				'tracking'			=> $this->option_storage()->get('rs-tracking-data',					$this->get_val($_options, ['tracking', 'tracking'], [])),
			],
			'fonts'	=> [
				'collected'			=> $this->option_storage()->get('tp-google-fonts-collect',			$this->get_val($_options, ['fonts', 'collected'], [])),
				'fonts'				=> $this->option_storage()->get('tp_font_css',						$this->get_val($_options, ['fonts', 'fonts'], [])),
			],
			'presets' => [
				'colorpicker'		=> $this->option_storage()->get('tp_colorpicker_presets',				$this->get_val($_options, ['presets', 'colorpicker'], [])),
				'navigation'		=> $this->option_storage()->get('revslider-nav-preset-default',		$this->get_val($_options, ['presets', 'navigation'], [])),
			],
			'favorites' => [
				'favorites'			=> $this->option_storage()->get('rs_favorite',						$this->get_val($_options, ['favorites', 'favorites'], [])),
				'object'			=> $this->option_storage()->get('rs_obj_favorites',					$this->get_val($_options, ['favorites', 'object'], [])),
			],
			'tags' => [
				'custom-library'	=> $this->option_storage()->get('rs-custom-library-tags',				$this->get_val($_options, ['tags', 'custom-library'], [])),
			],
			'other'	=> [
				'page-id'			=> $this->option_storage()->get('rs_import_page_id',					$this->get_val($_options, ['other', 'page-id'], 1)),
				'image-meta'		=> $this->option_storage()->get('rs_image_meta_todo',					$this->get_val($_options, ['other', 'image-meta'], [])),
				'static-css'		=> $this->option_storage()->get('revslider-static-css',				$this->get_val($_options, ['other', 'static-css'], '')),
				'slide-transitions' => $this->option_storage()->get('revslider_template_slidetransitions', $this->get_val($_options, ['other', 'slide-transitions'], [])),
			]
		];

		$library = [
			'library'	=> $this->option_storage()->get('rs-library', []),
			'custom'	=> $this->option_storage()->get('rs-custom-library', []),
		];
		$addons = [
			'addons'	=> $this->option_storage()->get('revslider-addons', []),
			'counter'	=> $this->option_storage()->get('rs-addons-counter', false),
		];

		$this->update_all_options($options);
		$this->update_all_options($library, 'rs-library');
		$this->update_all_options($addons, 'rs-addons');

		//delete_option('revslider-valid'); //used by our addons, so leave it for now to make sure addon updates do work
		//delete_option('revslider-code'); //used by our addons, so leave it for now to make sure addon updates do work
		//delete_option('revslider_update_revision_current');
		/*delete_option('revslider-latest-version');
		delete_option('revslider-stable-version');
		delete_option('revslider-connection');
		delete_option('revslider_servers');
		delete_option('revslider-uid');
		delete_option('rs_cache_overlay');
		delete_option('revslider-deregister-popup');
		delete_option('revslider_table_version');
		delete_option('revslider_hide_tooltips');
		delete_option('revslider-global-settings');
		delete_option('revslider-additions');
		delete_option('revslider-trustpilot');
		delete_option('revslider_update_info');
		delete_option('revslider-notices');
		delete_option('revslider-notices-dc');
		delete_option('revslider_server_refresh');
		delete_option('revslider-templates-check');
		delete_option('revslider-library-check');
		delete_option('revslider-update-check');
		delete_option('revslider-update-check-short');
		delete_option('tp_google_font');
		delete_option('revslider-templates-hash');
		delete_option('revslider-templates-top-hash');
		delete_option('revslider-library-hash');
		delete_option('revslider-update-hash');
		delete_option('rs-templates-top');
		delete_option('rs-templates-new');
		delete_option('rs-templates');
		delete_option('rs-templates-counter');
		delete_option('rs-library');
		delete_option('rs-custom-library');
		delete_option('revslider-addons');
		delete_option('rs-addons-counter');
		delete_option('rs-tracking-data');
		delete_option('tp-google-fonts-collect');
		delete_option('tp_font_css');
		delete_option('tp_colorpicker_presets');
		delete_option('revslider-nav-preset-default');
		delete_option('rs_favorite');
		delete_option('rs_obj_favorites');
		delete_option('rs-custom-library-tags');
		delete_option('rs_import_page_id');
		delete_option('rs_image_meta_todo');
		delete_option('revslider-static-css');
		delete_option('revslider_template_slidetransitions');*/
	}

	/** @return bool */
	public function fix_folders(){
		if(UpgradeV6::do_v6_tables_exist() === false) return false;

		$folders = $this->database()->get_results("SELECT * FROM ". $this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDER_V6 ." WHERE `type` = 'folder' ORDER BY id ASC", 'ARRAY_A');

		foreach($folders ?? [] as $folder){
			$add = [
				'id'	=> $this->get_val($folder, 'id'),
				'title'	=> $this->get_val($folder, 'title'),
				'alias'	=> $this->get_val($folder, 'alias'),
				'settings'	=> $this->get_val($folder, 'settings'),
				'type'	=> $this->get_val($folder, 'type'),
			];

			$exists = $this->database()->get_var($this->database()->prepare("SELECT COUNT(*) FROM ".$this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDER." WHERE id = %d AND type = 'folder'", $add['id']));
			if($exists){
				$this->database()->update($this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDER, $add, ['id' => $add['id']]);
			}else{
				$this->database()->insert($this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDER, $add);
			}
		}
	}

	/** @return void */
	public function upgrade_globals_to_7_0_0(){
		$gs		= $this->get_global_settings();
		$incl	= $this->get_val($gs, 'includeids', 'all');
		$incl	= (strpos($incl, ',') !== false) ? explode(',', $incl) : (array)$incl;
		$fonts	= $this->get_val($gs, 'customFontList', []);
		foreach($fonts ?? [] as $k => $font){
			if(isset($font['in'])) continue;
			$frontend		 = $this->_truefalse($this->get_val($font, 'frontend'));
			$backend		 = $this->_truefalse($this->get_val($font, 'backend'));
			//a front-only 6.x font used to end up as 'none' here, which is why get_custom_font_list()
			//repairs the rows that already went through this
			if($frontend === true){
				$fonts[$k]['in'] = ($backend === true) ? 'both' : 'live';
			}else{
				$fonts[$k]['in'] = ($backend === true) ? 'editor' : 'none';
			}
		}

		$_gs	= [
			'permission'	=> $this->get_val($gs, 'permission', 'admin'),
			'lang'			=> $this->get_val($gs, 'lang', 'default'),
			'guide'			=> [
				'template'	=> $this->get_val($gs, 'templateGuide', true),
				'module'	=> $this->get_val($gs, 'moduleGuide', true),
			],
			'inclAll'		=> $this->get_val($gs, 'allinclude', true),
			'incl'			=> $incl,
			'script'		=> [
				'footer' => $this->get_val($gs, ['script', 'footer'], true),
				'defer'	 => $this->get_val($gs, ['script', 'defer'], true),
				'async'	 => $this->get_val($gs, ['script', 'async'], true),
				'full'	 => $this->get_val($gs, ['script', 'full'], false),
				'ytapi'	 => $this->get_val($gs, ['script', 'ytapi'], true),
			],
			'xOrig'			=> $this->get_val($gs, 'imgcrossOrigin', 'unset'),
			'fonts'			=> [
				'download'	=> $this->get_val($gs, 'fontdownload', 'off'),
				'awesome'	=> $this->get_val($gs, 'fontawesomedisable', false),
				'url'		=> $this->get_val($gs, 'fonturl', 'off'),
				'list'		=> $fonts,
			],
			'getTec'			=> [
				'feed'	=> $this->get_val($gs, ['getTec', 'feed'], 'REST'),
				'core'	=> $this->get_val($gs, ['getTec', 'core'], 'MIX'),
			],
			'opt'			=> [
				'dprmobile'	=> $this->get_val($gs, 'dprmobile', true),
				'intcache'	=> $this->get_val($gs, 'internalcaching', false),
			],
			'gdpr' 			=> [				
				'ytnc'		=> $this->get_val($gs, 'ytnc', false),
			],
			'track'			=> $this->get_val($gs, 'tracking', '1999-01-01'),
			'trackOnOff'	=> ($this->get_val($gs, 'tracking', '1999-01-01') === 'enabled') ? true : false,
			'breakPoints'	=> [
				1920,
				intval($this->get_val($gs, ['size', 'desktop'], 1240)),
				intval($this->get_val($gs, ['size', 'notebook'], 1024)),
				intval($this->get_val($gs, ['size', 'tablet'], 778)),
				intval($this->get_val($gs, ['size', 'mobile'], 480)),
			],
		];
		
		$this->set_global_settings($_gs);
	}

	/** @return void */
	public function upgrade_options_to_7_0_3(){
		$options = $this->get_options();
		if(empty($options)) return;

		if(isset($options['templates'])){
			$templates = [
				'top'		=> $this->get_val($options, ['templates', 'top']),
				'new'		=> $this->get_val($options, ['templates', 'new']),
				'templates'	=> $this->get_val($options, ['templates', 'templates']),
				'counter'	=> $this->get_val($options, ['templates', 'counter'])
			];
			$this->update_all_options($templates, 'rs-templates');

			$this->delete_option('templates');
		}
		if(isset($options['library'])){
			$library = [
				'library'	=> $this->get_val($options, ['library', 'library']),
				'custom'	=> $this->get_val($options, ['library', 'custom']),
			];
			$this->update_all_options($library, 'rs-library');

			$this->delete_option('library');
		}
		if(isset($options['addons'])){
			$addons = [
				'addons'	=> $this->get_val($options, ['addons', 'addons']),
				'counter'	=> $this->get_val($options, ['addons', 'counter']),
			];
			$this->update_all_options($addons, 'rs-addons');

			$this->delete_option('addons');
		}
	}

	/**
	 * Split template slides data and template slider data into two separate options
	 * to ensure server limitations are not reached
	 * @return void
	 */
	public function upgrade_options_to_7_0_1(){
		$options = $this->get_options();
		if(empty($options)) return;
		
		$t	= $this->get_options([], false, false, 'rs-templates');
		$td	= $this->get_val($t, 'templates-data', []);
		if(isset($t['templates-data'])){
			unset($t['templates-data']);
			$this->update_all_options($t, 'rs-templates');
		}

		$this->update_all_options($td, 'rs-templates-slides');
	}

	/**
	 * Fix templates and template slides options naming to be consistent with the other options
	 * all options are now prefixed with "rs-"
	 * 'rs-templates'
	 * 'rs-library'
	 * 'rs-addons'
	 * @return void
	 */
	public function upgrade_options_to_7_0_7(){
		$data = $this->get_options([], false, false, 'sr-templates');
		if (!empty($data)) {
			$this->update_all_options($data, 'rs-templates');
			$this->option_storage()->delete( 'sr-templates' );
		}

		$data = $this->get_options([], false, false, 'sr-templates-slides');
		if (!empty($data)) {
			$this->update_all_options($data, 'rs-templates-slides');
			$this->option_storage()->delete( 'sr-templates-slides' );
		}
	}

	/**
	 * delete template preview images, as we have new ones in the database
	 * @return void
	 **/
	public function upgrade_template_library_to_7_0_4(){
		$upload_dir = $this->paths()->upload_dir(); // Set upload folder
		$folder = $upload_dir['basedir'] . '/revslider/templates/'; //we use direct path here, as if RevSliderTemplate::$templates_path; is empty or something goes wrong, it would kill ALL media

		// Check if folder exists (the trailing slash keeps exists() a directory-only check, like is_dir was)
		if($this->filesystem()->exists($folder)){
			// Delete folder recursively
			$this->filesystem()->delete($folder, true);
		}
	}

	/**
	 * upgrade slider settings to a higher version, to be on par with revision
	 * @return void
	 */
	public function upgrade_slider_to_version($sliders = false, $version = false){
		$version = ($version === false) ? $this->revision : $version;
		$this->raise_runtime_limits(300);

		$sr		 = new \ThemePunch\SR\Core\Slider();
		$sliders = ($sliders === false) ? $sr->get_sliders() : [$sliders]; //do it on all Sliders if false

		foreach($sliders ?? [] as $slider){
			$slider->update_settings(['version' => $version]);
		}
	}

	/**
	 * change core WPML to use the new ai translate addon
	 * @return void
	 */
	public function upgrade_slider_to_7_0_9($sliders){
		global $SR_GLOBALS;

		$sr = new \ThemePunch\SR\Core\Slider();
		$sl = new \RevSliderSlide();
		$updv6 = false;
		$sliders = ($sliders === false) ? $sr->get_sliders() : [$sliders]; //do it on all Sliders if false

		foreach($sliders ?? [] as $slider){
			if(version_compare($slider->get_setting('version', '1.0.0'), '7.0.9', '>=')) continue;

			$wpml_active = $slider->get_param('wpml', false);
			$use_wpml_active = $slider->get_param('useWPML', false);
			if($wpml_active === true || $use_wpml_active === true){
				$slides			= $slider->get_slides(false, true);
				$layers			= [];
				$delete_slides	= [];
				$main_slides	= [];
				$languages 		= [];
				$slides_data	= [];

				foreach($slides ?? [] as $sk => $slide){
					$slides_data[$sk] = ['layers' => 0, 'text' => 0];

					//$settings = $slide->get_settings();
					//if(version_compare($this->get_val($settings, 'version', '1.0.0'), '7.0.9', '>=')) continue;
					
					$lang	= $slide->get_param('language', 'all');
					$pid	= $slide->get_param('parentId', $slide->get_param('parentID', '')); //v7 //v6

					if(empty($pid)){
						//check if we have a parentId in V6 and if yes, check the map to get corresponding V7 slide id
						if($updv6 === false) $updv6	= new UpgradeV6();
						$v7_slide_id = $slide->get_id();
						$v6_slide_id = $updv6->get_v6_slide_by_v7_id($v7_slide_id);
						if($v6_slide_id !== false){
							$SR_GLOBALS['v6'] = true;
							$v6_slide = new \RevSliderSlide();
							if($v6_slide->exist_by_id($v6_slide_id)){
								$v6_slide->init_by_id($v6_slide_id);
								if($v6_slide->inited !== false){
									$v6_pid = $v6_slide->get_param(['child', 'parentId'], $v6_slide->get_param('parentID', ''));
									$v6_lang = $v6_slide->get_param(['child', 'language'], '');
									if(!empty($v6_pid)){
										$v7_pid = $updv6->get_v7_slide_map($v6_pid);
										if(!empty($v7_pid)) $pid = $v7_pid;
									}
								}
							}
							$SR_GLOBALS['v6'] = false;
						}
					}

					$slides_data[$sk]['lang'] = $lang;
					if(in_array($lang, ['all', 'default'])) continue;

					//if(!in_array($lang, ['all', 'default']) && !in_array($lang, $languages)) $languages[] = $lang;
					if(!in_array($lang, $languages)) $languages[] = $lang;


					if($pid !== '') $delete_slides[] = $slide->get_id();
					if(empty($pid)) $pid = $slide->get_id();

					if(!isset($main_slides[$pid])) $main_slides[$pid] = [];

					$layers = $slide->get_layers();
					foreach($layers ?? [] as $lk => $layer){
						$slides_data[$sk]['layers']++;
						if($this->get_val($layer, 'type', 'text') !== 'text') continue;
						
						$slides_data[$sk]['text']++;
						if(!isset($main_slides[$pid][$lk]))				$main_slides[$pid][$lk] = [];
						if(!isset($main_slides[$pid][$lk]['text']))		$main_slides[$pid][$lk]['text'] = [];
						if(!isset($main_slides[$pid][$lk]['toggle']))	$main_slides[$pid][$lk]['toggle'] = [];

						$main_slides[$pid][$lk]['text'][$lang]	 = $this->get_val($layer, ['content', 'text']);
						$main_slides[$pid][$lk]['toggle'][$lang] = $this->get_val($layer, ['toggle', 'text']);
					}
				}

				//Only proceed if we have multiple languages detected
				//Separate slides with no language set ('all', 'default') as potential parents
				//check to connect slides as it seems parentId is missing
				//and slides with specific languages as potential children
				if(empty($delete_slides) && !empty($languages)){
					$potential_parents  = [];
					$potential_children = [];
					
					foreach($slides_data as $sk => $data){
						if(in_array($data['lang'], ['all', 'default'])){
							$potential_parents[$sk] = $data;
						}else{
							$potential_children[$sk] = $data;
						}
					}

					//Build a map: for each language, how many slides exist?
					$lang_counts = [];
					foreach($potential_children as $sk => $data){
						$lang = $data['lang'];
						if(!isset($lang_counts[$lang])) $lang_counts[$lang] = 0;
						$lang_counts[$lang]++;
					}

					//Number of expected parent slides = count of slides per language
					//(assumes all languages have the same number of child slides as parents)
					$expected_parent_count = count($potential_parents);

					//Group children by language
					$children_by_lang = [];
					foreach($potential_children as $sk => $data){
						$lang = $data['lang'];
						if(!isset($children_by_lang[$lang])) $children_by_lang[$lang] = [];
						$children_by_lang[$lang][$sk] = $data;
					}

					//Try to match each parent slide to its children across languages
					//Strategy 1: Match by identical layer count
					$parent_keys	= array_keys($potential_parents);
					$matched		= [];

					foreach($parent_keys ?? [] as $psk){
						$matched[$psk] = [];
					}

					foreach($children_by_lang ?? [] as $lang => $lang_children){
						$unmatched_children = array_keys($lang_children);

						foreach($parent_keys ?? [] as $psk){
							$parent_layers = $potential_parents[$psk]['layers'];

							//Find children with matching layer count
							$layer_matches = array_filter(
								$unmatched_children,
								function($csk) use ($lang_children, $parent_layers){
									return $lang_children[$csk]['layers'] === $parent_layers;
								}
							);

							if(count($layer_matches) === 1){
								$csk = reset($layer_matches);
								$matched[$psk][$lang]	= $csk;
								$unmatched_children		= array_diff($unmatched_children, [$csk]);
							}
						}

						//Strategy 2: For still-unmatched, try matching by text layer count
						foreach($parent_keys as $psk){
							if(isset($matched[$psk][$lang])) continue;

							$parent_text = $potential_parents[$psk]['text'];

							$text_matches = array_filter(
								$unmatched_children,
								function($csk) use ($lang_children, $parent_text){
									return $lang_children[$csk]['text'] === $parent_text;
								}
							);

							if(count($text_matches) === 1){
								$csk = reset($text_matches);
								$matched[$psk][$lang]	= $csk;
								$unmatched_children		= array_diff($unmatched_children, [$csk]);
							}
						}

						//Strategy 3: For still-unmatched, try root-level group/row/column layer structure
						foreach($parent_keys as $psk){
							if(isset($matched[$psk][$lang])) continue;

							$parent_slide_obj	= $slides[$psk];
							$parent_layers_raw	= $parent_slide_obj->get_layers();
							$parent_root_struct	= $this->_count_root_structure($parent_layers_raw);

							$struct_matches = array_filter(
								$unmatched_children,
								function($csk) use ($slides, $parent_root_struct){
									$child_layers_raw	= $slides[$csk]->get_layers();
									$child_root_struct	= $this->_count_root_structure($child_layers_raw);
									return $child_root_struct === $parent_root_struct;
								}
							);

							if(count($struct_matches) === 1){
								$csk = reset($struct_matches);
								$matched[$psk][$lang]	= $csk;
								$unmatched_children		= array_diff($unmatched_children, [$csk]);
							}
						}

						//Strategy 4: Last resort — assign remaining unmatched children
						//to unmatched parents in order (positional fallback)
						foreach($parent_keys as $psk){
							if(isset($matched[$psk][$lang])) continue;
							if(empty($unmatched_children)) break;

							$csk = array_shift($unmatched_children);
							$matched[$psk][$lang] = $csk;
						}
					}

					//Now apply the matched relationships:
					// - Collect child slide IDs into $delete_slides
					// - Merge their text layers into $main_slides under the parent's slide ID
					foreach($matched as $psk => $lang_children_map){
						$parent_slide       = $slides[$psk];
						$parent_slide_id    = $parent_slide->get_id();
						$parent_layers      = $parent_slide->get_layers();

						if(!isset($main_slides[$parent_slide_id])){
							$main_slides[$parent_slide_id] = [];
						}

						foreach($lang_children_map as $lang => $csk){
							$child_slide    = $slides[$csk];
							$child_slide_id = $child_slide->get_id();
							$child_layers   = $child_slide->get_layers();

							$delete_slides[] = $child_slide_id;

							foreach($child_layers as $lk => $layer){
								if($this->get_val($layer, 'type', 'text') !== 'text') continue;

								if(!isset($main_slides[$parent_slide_id][$lk]))             $main_slides[$parent_slide_id][$lk] = [];
								if(!isset($main_slides[$parent_slide_id][$lk]['text']))     $main_slides[$parent_slide_id][$lk]['text'] = [];
								if(!isset($main_slides[$parent_slide_id][$lk]['toggle']))   $main_slides[$parent_slide_id][$lk]['toggle'] = [];

								$main_slides[$parent_slide_id][$lk]['text'][$lang]   = $this->get_val($layer, ['content', 'text']);
								$main_slides[$parent_slide_id][$lk]['toggle'][$lang] = $this->get_val($layer, ['toggle', 'text']);
							}
						}
					}
				}

				foreach($slides ?? [] as $sk => $slide){
					//$settings = $slide->get_settings();
					//if(version_compare($this->get_val($settings, 'version', '1.0.0'), '7.0.9', '>=')) continue;

					$slide_id = $slide->get_id();

					if(in_array($slide_id, $delete_slides)){
						//DELETE SLIDE
						$sl->delete_slide_by_id($slide_id);
						continue;
					}
					
					//set translation strings
					if(isset($main_slides[$slide_id])){
						$layers = $slide->get_layers();
						
						foreach($layers ?? [] as $lk => $layer){
							if($this->get_val($layer, 'type', 'text') !== 'text') continue;
							
							if(!isset($layers[$lk]['addOns'])) $layers[$lk]['addOns'] = [];
							if(!isset($layers[$lk]['addOns']['aitranslate'])) $layers[$lk]['addOns']['aitranslate'] = [];

							$layers[$lk]['addOns']['aitranslate']['translations'] = $main_slides[$slide_id][$lk]['text'];
						}

						$slide->set_layers_raw($layers);
						$slide->save_layers();

						//$slide->set_param(['addOns', 'aitranslate', 'translations'], $main_slides[$slide_id][$lk]['text']);
						//$slide->save_params();

						$slide->settings['version'] = '7.0.9';
						$slide->save_settings();
					}
				}

				//set module settings
				$slider->set_param('wpml', false);
				$saddons = $slider->get_param('addOns', []);
				$saddons = $slider->set_param('addOns', $saddons);
				$slider->set_param(['addOns', 'aitranslate'], [
					'u'			 => true,
					'languages'  => $languages,
					'default'	 => 'en',
					'transmode'  => 'all',
					'wpmlSynced' => false,
					'engine'	 => 'hwpml',
				]);
				
				$slider->save_params();
			}
			
			$slider->update_settings(['version' => '7.0.9']);
		}
	}

	/**
	 * update concept
	 * @return void
	 */
	public function upgrade_slider_to_7_0_0($sliders){
		$this->raise_runtime_limits(300);

		$sr = new \ThemePunch\SR\Core\Slider();
		$sl = new \RevSliderSlide();

		$sliders = ($sliders === false) ? $sr->get_sliders() : [$sliders]; //do it on all Sliders if false

		foreach($sliders ?? [] as $slider){
			$slides = $slider->get_slides(false, true);

			foreach($slides ?? [] as $slide){
				$settings = $slide->get_settings();
				if(version_compare($this->get_val($settings, 'version', '1.0.0'), '7.0.0', '>=')) continue;
				$save	= false;
				$params = $slide->get_params();

				//$this->set_val($params, ['bg', 'video', 'dottedColorB'], '#FFFFFF');

				if($save){
					$slide->set_params($params);
					$slide->save_params();
				}
				$slide->settings['version'] = '7.0.0';
				$slide->save_settings();

				$layers = $slide->get_layers();

				$save = false;
				foreach($layers ?? [] as $lk => $layer){
					$version = $this->get_val($layer, 'version', '1.0.0');
					
					if(version_compare($version, '7.0.0', '>=')) continue;
					
					$save		 = true;
					$json_layer	 = $_json_layer = json_encode($layer);
					//$_json_layer = str_replace($this->update['620']['ease_adv_from'], $this->update['620']['ease_adv_to'], $_json_layer);
					if($_json_layer !== $json_layer){
						$layers[$lk] = (array)json_decode($_json_layer, true);
					}
					$layers[$lk]['version'] = '7.0.0';
				}

				if($save){
					$slide->set_layers_raw($layers);
					$slide->save_layers();
				}
			}

			$slider->update_settings(['version' => '7.0.0']);
		}
	}

	
	/**
	 * transform an old navigation into the 6.0.0+ version
	 * @return array
	 **/
	public function create_new_navigation_6_0($_, $t){
		$n = [
			'id'		=> $this->get_val($_, 'id'),
			'handle'	=> $this->get_val($_, 'handle'),
			'name'		=> $this->get_val($_, 'name'),
			'type'		=> $t,
			'css'		=> $this->get_val($_, ['css', $t]),
			'markup'	=> $this->get_val($_, ['markup', $t]),
			'settings'	=> [
				'dim'			=> ['width' => $this->get_val($_, ['settings', 'width', $t], 160), 'height' => $this->get_val($_, ['settings', 'height', $t], 160)],
				'placeholders'	=> new \stdClass(),
				'presets'		=> new \stdClass(),
				'version'		=> '6.0.0',
			],
		];

		$placeholders = $this->get_val($_, ['settings', 'placeholders'], []);
		foreach($placeholders ?? [] as $ph){
			if($this->get_val($ph, 'nav-type') !== $t) continue;
		
			$n['settings']['placeholders']->{$this->get_val($ph, 'handle')} = [
				'title' => $this->get_val($ph, 'title'),
				'type' => $this->get_val($ph, 'type'),
				'data' => ($this->get_val($ph, 'type') === 'font-family') ? $this->get_val($ph, ['data', 'font_family']) : $this->get_val($ph, ['data', $this->get_val($ph, 'type')]),
			];
		}

		$presets = $this->get_val($_, ['settings', 'presets'], []);
		foreach($presets ?? [] as $preset){
			if($this->get_val($preset, 'type') !== $t) continue;
		
			$n['settings']['presets']->{$this->get_val($preset, 'handle')} = [
				'name' => $this->get_val($preset, 'name'),
				'values' => [],
			];

			$values = $this->get_val($preset, 'values', []);
			foreach($values ?? [] as $j => $value){
				$handle = str_replace(['ph-'. $_['handle'] .'-'. $t .'-', '-color', '-rgba', '-custom'], '', $j);
				$n['settings']['presets']->{$this->get_val($preset, 'handle')}['values'][$handle] = $value;
			}
		}

		return $n;
	}


	/**
	 * Counts root-level layer types (group, row, column) for structural slide matching.
	 * Returns an array like ['group' => 2, 'row' => 1, 'column' => 0, 'other' => 3]
	 * @return array
	 */
	private function _count_root_structure($layers){
		$counts = ['group' => 0, 'row' => 0, 'column' => 0, 'other' => 0];

		foreach($layers ?? [] as $layer){
			// Only root-level layers (no parentId / parentId is empty)
			$parent = $this->get_val($layer, 'parentId', $this->get_val($layer, 'parentID', ''));
			if(!empty($parent)) continue;

			$type = $this->get_val($layer, 'type', 'other');
			if(isset($counts[$type])){
				$counts[$type]++;
			}else{
				$counts['other']++;
			}
		}

		return $counts;
	}
}
