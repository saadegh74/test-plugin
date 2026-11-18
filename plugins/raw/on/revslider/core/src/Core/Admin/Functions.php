<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */
 
namespace ThemePunch\SR\Core\Admin;

if(!defined('ABSPATH')) exit();

/**
 * Admin-only helpers on top of RevSliderFunctions: the data the editor's library and overview screens
 * need, media uploads, custom animations, and the system requirements check.
 */
class Functions extends \ThemePunch\SR\Core\Functions {

	/** the admin views a request may open */
	const ADMIN_VIEWS = ['markups', 'editor', 'sliders', 'slider', 'slide', 'update'];

	/**
	 * get the full object of: 
	 * +- Slider Templates
	 * +- Created Slider
	 * +- Object Library Images
	 * - Object Library Videos
	 * +- SVG
	 * +- Font Icons
	 * - layers
	 * @return array everything the editor's library dialog shows: templates, modules, objects, icons
	 **/
	public function get_full_library($include = ['all'], $tmp_slide_uid = [], $refresh_from_server = false, $get_static_slide = false, $page = false){
		$include	= (array)$include;
		$template	= new \ThemePunch\SR\Core\Template();
		/* @var RevSliderObjectLibrary $library */
		$library	= \ThemePunch\SR\Core\Registry::instance()->get('RevSliderObjectLibrary');
		$slide		= new \RevSliderSlide();
		$object		= [];
		$tmp_slide_uid = ($tmp_slide_uid !== false) ? (array)$tmp_slide_uid : [];

		if($refresh_from_server === true){ //refresh list from server
			if(in_array('all', $include) || in_array('moduletemplates', $include)){ 
				$template->_get_template_list(true);
				$object['moduletemplates']['tags'] = $object['moduletemplates']['tags'] ?? $template->get_template_categories();
				asort($object['moduletemplates']['tags']);
			}
			if(in_array('all', $include) || in_array('layers', $include) || in_array('videos', $include) || in_array('images', $include) || in_array('objects', $include)){
				$library->_get_list(true);
			}
			if(in_array('all', $include) || in_array('layers', $include)){
				$object['layers']['tags'] = $object['layers']['tags'] ?? $library->get_objects_categories('4');
				asort($object['layers']['tags']);
			}
			if(in_array('all', $include) || in_array('videos', $include)){
				$object['videos']['tags'] = $object['videos']['tags'] ?? $library->get_objects_categories('3');
				asort($object['videos']['tags']);
			}
			if(in_array('all', $include) || in_array('images', $include)){
				$object['images']['tags'] = $object['images']['tags'] ?? $library->get_objects_categories('2');
				asort($object['images']['tags']);
			}
			if(in_array('all', $include) || in_array('objects', $include)){
				$object['objects']['tags'] = $object['objects']['tags'] ?? $library->get_objects_categories('1');
				asort($object['objects']['tags']);
			}
			$object = apply_filters('revslider_get_full_library_refresh', $object, $include, $tmp_slide_uid, $refresh_from_server, $get_static_slide, $this);
		}

		if(in_array('moduletemplates', $include) || in_array('all', $include))		$object['moduletemplates']['items']		 = $object['moduletemplates']['items'] ?? $template->get_tp_template_sliders_for_library($refresh_from_server, $page);
		if(in_array('moduletemplateslides', $include) || in_array('all', $include))	$object['moduletemplateslides']['items'] = $object['moduletemplateslides']['items'] ?? $template->get_tp_template_slides_for_library($tmp_slide_uid);
		if(in_array('modules', $include) || in_array('all', $include))				$object['modules']['items']				 = $object['modules']['items'] ?? $this->get_slider_overview();
		if(in_array('moduleslides', $include) || in_array('all', $include))			$object['moduleslides']['items']		 = $object['moduleslides']['items'] ?? $slide->get_slides_for_library($tmp_slide_uid, $get_static_slide);
		if(in_array('svgs', $include) || in_array('all', $include))					$object['svgs']['items']				 = $object['svgs']['items'] ?? $library->get_svg_sets_full();
		if(in_array('svgcustom', $include) || in_array('all', $include))			$object['svgcustom']['items']			 = $object['svgcustom']['items'] ?? $library->get_custom_svgs();
		if(in_array('icons', $include) || in_array('all', $include))				$object['icons']['items']				 = $object['icons']['items'] ?? $library->get_font_icons();
		if(in_array('layers', $include) || in_array('all', $include))				$object['layers']['items']				 = $object['layers']['items'] ?? $library->load_objects('4');
		if(in_array('videos', $include) || in_array('all', $include))				$object['videos']['items']				 = $object['videos']['items'] ?? $library->load_objects('3');
		if(in_array('images', $include) || in_array('all', $include))				$object['images']['items']				 = $object['images']['items'] ?? $library->load_objects('2');
		if(in_array('objects', $include) || in_array('all', $include))				$object['objects']['items']				 = $object['objects']['items'] ?? $library->load_objects('1');

		return apply_filters('revslider_get_full_library', $object, $include, $tmp_slide_uid, $refresh_from_server, $get_static_slide, $this);
	}
	
	
	/**
	 * get the short library with categories and how many elements exist
	 * @return array only the category lists of the library, for the filter bar
	 **/
	public function get_short_library($sliders = false){
		$template = new \ThemePunch\SR\Core\Template();
		/* @var RevSliderObjectLibrary $library */
		$library = \ThemePunch\SR\Core\Registry::instance()->get('RevSliderObjectLibrary');
		$sliders = ($sliders === false) ? $this->get_slider_overview() : $sliders;
		
		$slider_cat = [];
		foreach($sliders ?? [] as $slider){
			$tags = $this->get_val($slider, 'tags', []);
			foreach($tags ?? [] as $tag){
				if(trim($tag) !== '' && !isset($slider_cat[$tag])) $slider_cat[$tag] = ucwords($tag);
			}
		}

		$m_templates = $template->get_template_categories();
		$svgs		 = $library->get_svg_categories();
		$icons		 = $library->get_font_tags();
		//walk the objects array once (bucketed per type) instead of calling get_objects_categories() 4x
		$obj_cats	 = $library->get_all_objects_categories();
		$objects	 = $this->get_val($obj_cats, '1', []);
		asort($m_templates);
		asort($slider_cat);
		asort($svgs);
		asort($icons);
		asort($objects);
		$tags		 = [
			'moduletemplates' => ['tags' => $m_templates],
			'modules'	=> ['tags' => $slider_cat],
			'svgs'		=> ['tags' => $svgs],
			'icons'		=> ['tags' => $icons],
			'layers'	=> ['tags' => $this->get_val($obj_cats, '4', [])],
			'videos'	=> ['tags' => $this->get_val($obj_cats, '3', [])],
			'images'	=> ['tags' => $this->get_val($obj_cats, '2', [])],
			'objects'	=> ['tags' => $objects]
		];

		$custom = $library->get_custom_tags();
		foreach($custom ?? [] as $tag_name => $tag_value){
			$tags[$tag_name] = ['tags' => $tag_value];
		}
		
		return apply_filters('revslider_get_short_library', $tags, $library, $this);
	}
	
	/**
	 * get the elements library
	 * @return array
	 **/
	public function get_elements_library(){
		/* @var RevSliderObjectLibrary $library */
		$library = \ThemePunch\SR\Core\Registry::instance()->get('RevSliderObjectLibrary');
		$icons	 = $library->get_font_tags();
		asort($icons);
		$tags = [
			'icons'	    => ['tags' => $icons],
			'layers'	=> ['tags' => $library->get_objects_categories('4')],
			'videos'	=> ['tags' => $library->get_objects_categories('3')],
			'images'	=> ['tags' => $library->get_objects_categories('2')]
		];
		return $tags;
	}
	
	/**
	 * Get Sliders data for the overview page
	 * @return array
	 **/
	public function get_slider_overview(){
		global $SR_GLOBALS;
		$SR_GLOBALS['data_init'] = false;

		$rs_folder	= new \ThemePunch\SR\Core\Folder();
		$rs_slider	= new \ThemePunch\SR\Core\Slider();
		
		$_sliders	= $rs_slider->get_sliders(false);
		$folders	= $rs_folder->get_folders();		
		$_sliders 	= array_merge($_sliders, $folders);
		$data		= [];
		
		$updv6		= new UpgradeV6();
		$_slider_ids_v6 = $updv6->slider_v6_has_no_v7(); //check if v6 sliders are not migrated properly
		
		$_sliders_v6 = [];
		if(!empty($_slider_ids_v6)){
			$SR_GLOBALS['v6'] = true;
			foreach($_slider_ids_v6 as $v6_slider_id){
				$rs_slider	= new \ThemePunch\SR\Core\Slider();
				$rs_slider->init_by_id($v6_slider_id);
				$_sliders_v6[]	= $rs_slider;
			}
			$SR_GLOBALS['v6'] = false;
		}

		$slider_combined = [
			['sliders' => $_sliders, 'type' => 'v7', 'ids' => 'all'],
			['sliders' => $_sliders_v6, 'type' => 'v6', 'ids' => $_slider_ids_v6]
		];

		foreach($slider_combined as $sliders){
			if(empty($sliders['sliders'])) continue;
			
			$SR_GLOBALS['v6'] = ($sliders['type'] === 'v6') ? true : false;

			$rs_slide	= new \RevSliderSlide();
			$_slides_raw = $rs_slide->get_all_slides_raw($sliders['ids']);
			$slides_raw = $this->get_val($_slides_raw, 'first_slides', []);
			$slides_ids = $this->get_val($_slides_raw, 'slide_ids', []);
			
			foreach($sliders['sliders'] ?? [] as $k => $slider){
				$slide_ids	= [];
				$slides		= [];
				$sid		= $slider->get_id();
				foreach($slides_raw ?? [] as $s => $r){
					if($r->get_slider_id() !== $sid) continue;
					
					foreach($slides_ids as $_s => $_sv){
						if($this->get_val($_sv, 'slider_id') === $sid){
							$slide_ids[] = $this->get_val($_sv, 'id');
							unset($slides_ids[$_s]);
						}
					}
					$slides[] = $r;
					unset($slides_raw[$s]);
				}
				if(empty($slide_ids)) $slide_ids = false;
				
				$slides = (empty($slides)) ? false : $slides;
				
				$slider->init_layer = false;
				if($SR_GLOBALS['v6']){
					$_slider = $slider->get_overview_data_v6(false, $slides, $slide_ids); 
				}else{
					$_slider = $slider->get_overview_data(false, $slides, $slide_ids); 
				}
				$data[] = $_slider;
				unset($sliders[$k]);
			}
		}

		$SR_GLOBALS['v6'] = false;
		$SR_GLOBALS['data_init'] = true;

		//the one list both the Dashboard's printed page and the library's module.load read, so an add-on's
		//labels (drawn on the card from 'labels') reach every place a module card is shown
		return apply_filters('revslider_slider_overview', $data);
	}
	
	
	/**
	 * insert custom animations
	 * @return int|mixed the new animation id
	 */
	public function insert_animation($animation, $type){
		$handle = $this->get_val($animation, 'name', false);
		$result = false;
		
		if($handle !== false && trim($handle) !== ''){
			
			//check if handle exists
			$arr = [
				'handle'	=> $this->get_val($animation, 'name'),
				'params'	=> json_encode($animation),
				'settings'	=> $type
			];
			
			$result = $this->database()->insert($this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_LAYER_ANIMATIONS, $arr);
		}

		return ($result) ? $this->database()->insert_id() : $result;
	}
	
	
	/**
	 * update custom animations
	 * @return int|mixed
	 */
	public function update_animation($animation_id, $animation, $type){
		
		$arr = [
			'handle'	=> $this->get_val($animation, 'name'),
			'params'	=> json_encode($animation),
			'settings'	=> $type
		];
		
		$result = $this->database()->update($this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_LAYER_ANIMATIONS, $arr, ['id' => $animation_id]);
		
		return ($result) ? $animation_id : $result;
	}
	
	
	/**
	 * delete custom animations
	 * @param int $animation_id
	 * @return int|false
	 */
	public function delete_animation($animation_id){
		
		return $this->database()->delete($this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_LAYER_ANIMATIONS, ['id' => $animation_id]);
	}
	
	
	/**
	 * @since: 5.3.0
	 * create a page with revslider shortcodes included
	 * @return int the id of the created page
	 **/
	public static function create_slider_page($added, $modals = [], $additions = []){
		$t = \ThemePunch\SR\Core\Api::host_transport('create_slider_page'); //page creation lives with the host adapter

		return $t ? $t->create_slider_page($added, $modals, $additions) : 0; //a host that builds no pages answers 0, the same "none created" the adapter returns on bad input
	}
	
	/**
	 * add notices from ThemePunch
	 * @since: 4.6.8
	 * @return array
	 */
	public function get_notices(){
		$_n = [];
		$notices = (array)$this->get_options(['overview', 'notices'], []);
		$rs_valid = $this->_truefalse($this->get_options(['system', 'valid'], 'false'));
		if(empty($notices)) return $_n;

		$n_discarted = $this->get_options(['overview', 'notices-dc'], []);
		foreach($notices ?? [] as $notice){
			//the option is stored as json and read back associative, so a notice arrives as an array, never as the object it was saved as
			$code		= $this->get_val($notice, 'code');
			$version	= $this->get_val($notice, 'version', false);
			$registered	= $this->get_val($notice, 'registered', null);
			$until		= $this->get_val($notice, 'show_until', '');

			if(in_array($code, $n_discarted)) continue;
			if($version !== false && version_compare($version, RS_REVISION, '<=')) continue;
			if($registered !== null && $this->_truefalse($registered) !== $rs_valid) continue; //only while the plugin state is the one the notice is meant for
			if(!empty($until) && $until !== '0000-00-00 00:00:00' && strtotime($until) < time()) continue;

			$_n[] = $notice;
		}
		return $_n;
	}

	/**
	 * the note a lifetime/CodeCanyon or a lapsed key gets instead of the Deactivated popup, only while the free core is
	 * open (ADR-0027). A key whose type the server never named (deactivated before 7.2.5, not asked yet) gets none
	 * @since 7.2.5
	 * @return array|false code (the dismissal's), state (lifetime|lapsed), dismissed, without (module id => add-on keys
	 *                     left out at the move), premium (add-on key => title)
	 */
	public function licence_note(){
		if($this->_truefalse($this->get_options(['system', 'valid'], 'false')) === true) return false;
		if($this->get_options(['system', 'license'], '') === '' || !$this->free_core_open()) return false;

		$type  = $this->get_options(['system', 'license_type'], '');
		$state = in_array($type, ['lifetime', 'cc'], true) ? 'lifetime' : (in_array($type, ['subscription', 'extended'], true) ? 'lapsed' : '');
		if($state === '') return false;

		$premium = [];
		foreach((array)\ThemePunch\SR\Core\Registry::instance()->get('RevSliderAddons')->get_addon_list() as $slug => $addon){
			if($this->get_val($addon, 'premium') === 'premium') $premium[str_replace(['revslider-', '-addon'], '', $slug)] = (string)$this->get_val($addon, 'title', $slug);
		}
		$without = [];
		foreach((array)$this->get_options(['system', 'v6_without'], []) as $id => $moved) $without[$id] = (array)$this->get_val($moved, 'addons', []);
		$code = 'sr7-licence-'.$state;

		return [
			'code'		=> $code,
			'state'		=> $state,
			'dismissed'	=> in_array($code, (array)$this->get_options(['overview', 'notices-dc'], []), true),
			'without'	=> (object)$without,
			'premium'	=> (object)$premium,
		];
	}

	/**
	 * returns an object of current system values
	 * @return array each requirement with its current and required value
	 **/
	public function get_system_requirements(){
		$dir	= $this->paths()->upload_dir();
		$basedir = $this->get_val($dir, 'basedir').'/';
		$ml		= ini_get('memory_limit');
		$mlb	= wp_convert_hr_to_bytes($ml);
		$umf	= ini_get('upload_max_filesize');
		$umfb	= wp_convert_hr_to_bytes($umf);
		$pms	= ini_get('post_max_size');
		$pmsb	= wp_convert_hr_to_bytes($pms);
		$map	= $this->database()->get_row("SHOW VARIABLES LIKE 'max_allowed_packet';");
		$map	= $this->get_val($map, 'Value', 0);
		

		$mlg  = ($mlb >= 268435456) ? true : false;
		$umfg = ($umfb >= 33554432) ? true : false;
		$pmsg = ($pmsb >= 33554432) ? true : false;
		$mapg = ($map >= 16777216) ? true : false;
		
		return [
			'memory_limit' => [
				'has' => size_format($mlb),
				'min' => size_format(268435456),
				'good'=> $mlg
			],
			'upload_max_filesize' => [
				'has' => size_format($umfb),
				'min' => size_format(33554432),
				'good'=> $umfg
			],
			'post_max_size' => [
				'has' => size_format($pmsb),
				'min' => size_format(33554432),
				'good'=> $pmsg
			],
			'max_allowed_packet' => [
				'has' => size_format($map),
				'min' => size_format(16777216),
				'good'=> $mapg
			],
			'upload_folder_writable'	=> wp_is_writable($basedir),
			'zlib_enabled'				=> function_exists('gzcompress') && function_exists('gzuncompress'),
			'object_library_writable'	=> $this->media_library()->supports_image_editing(),
			'server_connect'			=> $this->get_options(['system', 'connect'], false),
		];
	}
	
	/**
	 * import a media file uploaded through the browser to the media library
	 * @return array ['id' => attachment id] or ['error' => message]
	 **/
	public function import_upload_media(){
		$t = \ThemePunch\SR\Core\Api::host_transport('import_upload_media'); //media-library registration lives with the host adapter

		return $t ? $t->import_upload_media() : ['error' => __('No media library on this host', 'revslider')];
	}
	
	
	/**
	 * Create Multilanguage for JavaScript
	 * @return array the translation strings handed to the editor JS
	 */
	public function get_javascript_multilanguage(){
		//All SR7.LANG strings live in one place now: core/editor/i18n-strings.php ($manual + $generated).
		$lang = (array) include RS_PLUGIN_PATH . 'core/editor/i18n-strings.php';
		return apply_filters('revslider_get_javascript_multilanguage', $lang);
	}
	
	/**
	 * returns all image sizes that have the same aspect ratio, rounded on the second
	 * @since: 6.1.4
	 * @return array
	 **/
	public function get_same_aspect_ratio_images($images){
		$return = [];
		$images = (array)$images;
		/* @var RevSliderObjectLibrary $objlib */
		$objlib = \ThemePunch\SR\Core\Registry::instance()->get('RevSliderObjectLibrary');
		$upload_dir = $this->paths()->upload_dir();
		
		foreach($images ?? [] as $key => $image){
			//check if we are from object library
			if($objlib->_is_object($image)){
				$_img = $image;
				$image = $objlib->get_correct_size_url($image, 100, true);
				$objlib->_check_object_exist($image); //check to redownload if not downloaded yet
				
				$sizes = $objlib->get_sizes();
				$return[$key] = [];
				
				if(empty($sizes)) continue;
					
				foreach($sizes ?? [] as $size){
					$url = $objlib->get_correct_size_url($image, $size);
					$file = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $url);
					$_size = getimagesize($file);
					$return[$key][$size] = [
						'url'	=> $url,
						'width'	=> $this->get_val($_size, 0),
						'height'=> $this->get_val($_size, 1),
						'size'	=> filesize($file)
					];
					
					if($_img === $url) $return[$key][$size]['default'] = true;
				}
				
				//$image = $objlib->get_correct_size_url($image, 100, true);
				$file = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $image);
				$_size = getimagesize($file);
				$return[$key][100] = [
					'url'	=> $image,
					'width'	=> $this->get_val($_size, 0),
					'height'=> $this->get_val($_size, 1),
					'size'	=> filesize($file)
				];
				if($_img === $return[$key][100]['url']) $return[$key][100]['default'] = true;
			}else{
				$_img  = (intval($image) === 0) ? $this->media_library()->id_by_url($image) : $image;
				$rends = $this->media_library()->image_renditions($_img);

				if($rends === false) continue;
				$orig = $rends['orig'];
				if(intval($orig['width']) === 0 || intval($orig['height']) === 0) continue;
				$return[$key] = [];
				$ratio = round($orig['width'] / $orig['height'], 2);
				$return[$key]['orig'] = [
					'url'	=> $orig['url'],
					'width'	=> $orig['width'],
					'height'=> $orig['height'],
					'size'	=> ($orig['path'] !== null) ? filesize($orig['path']) : 0
				];
				if($image === $return[$key]['orig']['url']) $return[$key]['orig']['default'] = true;

				foreach($rends['sizes'] ?? [] as $sn => $sv){
					if(intval($sv['width']) === 0 || intval($sv['height']) === 0) continue;
					if(round($sv['width'] / $sv['height'], 2) !== $ratio) continue;
					$return[$key][$sn] = [
						'url'	=> $sv['url'],
						'width'	=> $sv['width'],
						'height'=> $sv['height'],
						'size'	=> ($sv['path'] !== null) ? filesize($sv['path']) : 0
					];
					if($image === $return[$key][$sn]['url']) $return[$key][$sn]['default'] = true;
				}
			}
		}
		
		return $return;
	}
	
	/**
	 * get all available languages from Slider Revolution
	 * @return array
	 **/
	public function get_available_languages(){
		$lang_codes = [
			'de_DE' => __('German', 'revslider'),
			'en_US' => __('English', 'revslider'),
			'fr_FR' => __('French', 'revslider'),
			'zh_CN' => __('Chinese', 'revslider')
		];
		
		$lang = [];
		foreach(glob(RS_PLUGIN_PATH.'core/languages/*.mo') ?: [] as $mo) $lang[] = basename($mo, '.mo'); //what get_available_languages() returned, without wp-admin
		$lang = apply_filters('get_available_languages', $lang, RS_PLUGIN_PATH.'core/languages/'); //the WP filter multilingual plugins hook into; the hook bus is host-shimmable
		$_lang = [];
		foreach($lang ?? [] as $k => $v){
			if(strpos($v, 'revsliderhelp-') !== false) continue;
			
			$_lc = str_replace('revslider-', '', $v);
			$_lang[$_lc] = (isset($lang_codes[$_lc])) ? $lang_codes[$_lc] : $_lc;
		}
		
		return $_lang;
	}

	/**
	 * the language the editor speaks: its own setting, the host's locale while that is empty or default
	 * @since 7.2.1
	 * @param array|null $settings the global settings, when the caller holds them already
	 * @return string
	 */
	public function editor_locale($settings = null){
		$locale = $this->get_val($settings ?? $this->get_global_settings(), 'lang', 'default');

		return (!$locale || $locale === 'default') ? $this->environment()->locale() : $locale;
	}

	/**
	 * the editor's translation files for its language that exist, by text domain
	 * @since 7.2.1
	 * @param array|null $settings the global settings, when the caller holds them already
	 * @return array domain => absolute path
	 */
	public function editor_language_files($settings = null){
		$locale = $this->editor_locale($settings);
		$files	= [];
		foreach(['revslider', 'revsliderhelp'] as $domain){
			$file = RS_PLUGIN_PATH . 'core/languages/' . $domain . '-' . $locale . '.mo';
			if(file_exists($file)) $files[$domain] = $file;
		}

		return $files;
	}

	/**
	 * the template an admin view is drawn with, below core/admin/views/; 'sliders', 'slider' and 'update' show the dashboard
	 * @since 7.2.1
	 * @param string $view
	 * @param array $allowed the views a request may open
	 * @return string
	 */
	public function admin_view_template($view, $allowed = self::ADMIN_VIEWS){
		if(!in_array($view, $allowed)) $this->throw_error(__('Bad Request', 'revslider'));

		switch($view){
			case 'markups':
				return 'markups';
			case 'slide':
			case 'editor':
				return '/editor/editor';
		}

		return 'dashboard';
	}

	/**
	 * The whole order of the admin document a view draws: its stylesheets, then everything that goes into its
	 * script block, so a second host prints the same page without working that order out again.
	 *
	 * The style order is the cascade. Timeline, preset browser and library only draw on the plugin's own page;
	 * base (the --sr-* palette Page Effects read), editor (the dialogs on builder screens), forms and colorpicker
	 * also serve the other screens, where the pickers and Quick Edit ask for anything else themselves.
	 *
	 * A script is [handle, path below core/, slot]. The slot names what only the host can fill: 'lang' the
	 * SRLANG / SR7.LANG translations, printed right before the script that reads them; 'media' the media library
	 * facade, the file named being WordPress', a host with another library names its own. _tpt follows the view's
	 * own scripts on the plugin page, compatibility.js closes every screen that carries the UI.
	 *
	 * @since 7.2.1
	 * @param string $view the view the request asked for: empty for the dashboard and on every other screen with SR7 UI
	 * @param bool $plugin_page the plugin's own admin page
	 * @param bool $rtl the admin runs right to left
	 * @return array styles and scripts, each a list of [handle, path below core/], a script also carrying its slot
	 */
	public function admin_asset_manifest($view, $plugin_page, $rtl){
		$css		= 'admin/assets/css/';
		$styles		= [['revslider-base-css', $css . 'base.css'], ['revslider-editor-css', $css . 'editor.css']];
		if($plugin_page){
			$styles[] = ['revslider-timeline-css', $css . 'timeline.css'];
			$styles[] = ['revslider-preset-browser-css', $css . 'preset-browser.css'];
			$styles[] = ['revslider-library-css', $css . 'library.css'];
		}
		$styles[]	= ['revslider-toolbars-css', $css . 'forms.css'];
		$styles[]	= ['revslider-colorpicker-css', $css . 'colorpicker.css'];
		if($rtl){
			$styles[] = ['revslider-base-rtl-css', $css . 'base-rtl.css'];
			$styles[] = ['revslider-editor-rtl-css', $css . 'editor-rtl.css'];
			if($plugin_page) $styles[] = ['revslider-library-rtl-css', $css . 'library-rtl.css'];
			$styles[] = ['revslider-forms-rtl-css', $css . 'forms-rtl.css'];
		}

		$tools		= [['revbuilder-backend', 'admin/assets/js/tools/tools.js', 'lang'], ['revbuilder-wpmedia', 'admin/assets/js/tools/wpmedia.js', 'media']];
		$scripts	= [];
		if(empty($view)){
			$scripts = $tools;
			if($plugin_page) $scripts[] = ['revbuilder-dashboard', 'admin/assets/js/dashboard.js'];
		}
		if('markups' === $view){
			$scripts = $tools;
			$scripts[] = ['revbuilder-colorpicker', 'admin/assets/js/tools/colorpicker.js'];
		}
		if(in_array($view, ['editor', 'slide'])){
			$scripts[] = ['sr7', 'public/js/sr7.js'];
			$scripts = array_merge($scripts, $tools);
			$scripts[] = ['revbuilder-editor', 'admin/assets/js/editor/editor.js'];
		}
		if($plugin_page && in_array($view, ['', 'markups', 'slide', 'editor'])) $scripts[] = ['tp-tools', 'public/js/libs/tptools.js'];
		$scripts[]	= ['revbuilder-admin', 'admin/assets/js/compatibility.js'];

		return ['styles' => $styles, 'scripts' => $scripts];
	}

	/**
	 * refresh what the admin reads from the ThemePunch servers: the object library, the template list, then the
	 * version info (notices, add-on and effects catalogues, licence state) - last, since it can revoke the licence
	 * the catalogue requests send
	 * @since 7.2.1
	 * @param bool $force_library refetch the object library while its cached list is still fresh
	 * @param bool $force_templates refetch the template list while it is still fresh
	 * @param bool $force_version a real true, Update checks it with ===: forces the version check and resets the update transient
	 * @return \ThemePunch\SR\Core\Update the version check, for a host that hooks its plugin updater to it
	 */
	public function refresh_remote_data($force_library, $force_templates, $force_version){
		\ThemePunch\SR\Core\Registry::instance()->get('RevSliderObjectLibrary')->_get_list($force_library);

		$template = new \ThemePunch\SR\Core\Template();
		$template->_get_template_list($force_templates);

		$update = new \ThemePunch\SR\Core\Update(RS_REVISION);
		$update->force = $force_version;
		$update->_retrieve_version_info();

		return $update;
	}

	/**
	 * function to check if the current page is a post/page in edit mode
	 * The same four screens the shortcode wizard and the builder registry load on: a block, a metabox or a
	 * Page Effect panel can appear on any of them, the site editor included.
	 * @return bool are we on a post/widget edit screen?
	 */
	public function is_edit_page(){
		$t = \ThemePunch\SR\Core\Api::host_transport('is_edit_page'); //screen probing lives with the host adapter

		return $t ? $t->is_edit_page() : false; //a host with no post editor is never on one
	}

	/**
	 * check if the WPML plugin is currently active
	 * @return bool
	 */
	public function is_wpml_active(){
		return defined('ICL_SITEPRESS_VERSION') || did_action('wpml_loaded');
	}

	/**
	 * get the active WPML languages for the editor, keyed by language code.
	 * returns an empty array when WPML is not active, so callers can skip output.
	 * used by the AI Translate add-on to auto-detect WPML and read its language list.
	 * @return array
	 */
	public function get_wpml_editor_languages(){
		if(!$this->is_wpml_active()) return [];

		$languages = apply_filters('wpml_active_languages', null, []);
		if(empty($languages) || !is_array($languages)) return [];

		$list = [];
		foreach($languages as $code => $data){
			if($code === 'all') continue;
			$list[$code] = [
				'title'  => $this->get_val($data, 'native_name', $this->get_val($data, 'translated_name', $code)),
				'active' => (bool)$this->get_val($data, 'active', false),
			];
		}

		return $list;
	}

}
