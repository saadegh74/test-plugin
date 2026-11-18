<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Render;

if(!defined('ABSPATH')) exit();

/**
 * The page-level render builders: head block, asset manifest, module JSON footer, fonts
 * preconnect and deprecation notices. Moved here from RevSliderFront, whose frozen publics
 * delegate; the WP adapter keeps hooks, enqueues and placement.
 * @since 7.2.1
 */
class Page extends \ThemePunch\SR\Core\Functions {

	/** the front scripts every page with a module loads (async), by handle, below core/ */
	const FRONT_SCRIPTS = ['tp-tools' => 'public/js/libs/tptools.js', 'sr7' => 'public/js/sr7.js'];

	/** the front stylesheets, by handle, below core/ */
	const FRONT_STYLES = ['sr7css' => 'public/css/sr7.css'];

	/**
	 * the engine libraries a page registers, the ones this build ships: an add-on asks for them by name (checkResources),
	 * and one nobody registered resolves without loading. Anywhere reads the same list from the bake (build/bake.php)
	 * @since 7.2.9
	 * @return array
	 */
	public static function engine_libs(){
		$libs = [];
		foreach(['THREE' => 'three', 'WEBGL' => 'webgl', 'EFFECTS' => 'effects', 'BACKDROP' => 'backdrop'] as $lib => $file){
			if(file_exists(RS_CORE_ASSETS_PATH . 'public/js/libs/' . $file . '.js')) $libs[] = $lib;
		}
		if(self::has_single_gsap()) $libs[] = 'tpgsap';
		return $libs;
	}

	/**
	 * everything a host must put on a page for the collected modules to run: the enqueue
	 * handles add_scripts() registers, the SR7.E name lists and resources, the fonts queue and
	 * the yt-api flag. The head builder prints exactly these lists; a non-WP adapter reads its enqueue
	 * and placement decisions from here instead.
	 * @since 7.2.1
	 * @return array
	 */
	public function build_asset_manifest(){
		$g = &$this->context()->root();

		$modules = $this->get_val($g, 'modules', []);
		if($g['markup_export'] === false) $modules[] = 'migration';

		$libs = self::engine_libs();
		$css  = [];
		if(file_exists(RS_CORE_ASSETS_PATH . 'public/css/sr7.lp.css'))		$css[] = 'csslp';
		if(file_exists(RS_CORE_ASSETS_PATH . 'public/css/sr7.btns.css'))		$css[] = 'cssbtns';
		if(file_exists(RS_CORE_ASSETS_PATH . 'public/css/sr7.filters.css'))	$css[] = 'cssfilters';
		if(file_exists(RS_CORE_ASSETS_PATH . 'public/css/sr7.nav.css'))		$css[] = 'cssnav';
		if(file_exists(RS_CORE_ASSETS_PATH . 'public/css/sr7.media.css'))	$css[] = 'cssmedia';

		return [
			'scripts'	=> array_keys(self::FRONT_SCRIPTS),
			'styles'	=> array_keys(self::FRONT_STYLES),
			'modules'	=> $modules,
			'libs'		=> $libs,
			'css'		=> $css,
			'resources'	=> self::bundled_module_urls($this->get_val($g, 'modules', [])),
			'fonts'		=> $this->get_val($g, 'fonts', []),
			'yt_api'	=> ($this->get_val($g, 'yt_api_loaded', false) === true),
		];
	}

	/**
	 * the head script block as a string: SR7.E environment, breakpoints, JSON bootstrap and the
	 * page handler. Marks the head as emitted; the js_add_header_scripts() shell guards repeats.
	 * @since 7.2.1
	 * @return string
	 */
	public function get_header_html(){
		$g = &$this->context()->root();

		$global = $this->get_global_settings();

		//the front list starts at the desktop breakpoint - wide desktop is "everything above it" and needs no entry, so the
		//editors five slot breakPoints list is read from slot 1 on. size.* is the pre 7.0 storage and stays as the per slot
		//fallback for installs that never re-saved their Global Settings
		$bpoints		= $this->get_val($global, 'breakPoints', []);
		$breakpoints	= [];
		$lev			= 1;
		foreach(['desktop' => 1240, 'notebook' => 1024, 'tablet' => 778, 'mobile' => 480] as $device => $default){
			$width = intval($this->get_val($bpoints, $lev++, 0));
			if($width < 1) $width = intval($this->get_val($global, ['size', $device], $default));
			$breakpoints[] = ($width > 0) ? $width : $default;
		}
		//escape hatch for a site whose layout was tuned while the front still ignored its custom breakpoints:
		//add_filter('revslider_front_breakpoints', function(){ return [1240, 1024, 778, 480]; });
		$breakpoints = apply_filters('revslider_front_breakpoints', $breakpoints, $global);
		//7.0 moved these under gdpr./fonts.; a site that never saved its Global Settings since still has the old name
		$ytnc	 = $this->_truefalse($this->get_val($global, ['gdpr', 'ytnc'], $this->get_val($global, 'ytnc', false)));
		$fSUVW	 = $this->_truefalse($this->get_val($global, 'fSUVW', false));		
		$assets	 = $this->build_asset_manifest();
		$modules = $assets['modules'];
		//Dev by default; the release build rewrites this line to 'false' (compiler gulp-frontend.js "silent-update-devMode").
		//It must NOT be inferred from three.js any more: three.js used to be concatenated into webgl.js and deleted, so "three.js is
		//missing" happened to mean "this is a release build". THREE now ships as its own file (2D pages skip ~200KB), so that signal
		//is gone — three.js exists in BOTH dev and release, and keying devMode off it would make every release run in dev mode.
		$devMode = 'false';

		$script = self::script_open() . "\n"; // data-type="SR7-content"
		$script .= "	window._tpt			??= {};" . "\n";
		$script .= "	window.SR7			??= {};" . "\n";
		$script .= "	_tpt.R				??= {};" . "\n";
		$script .= "	_tpt.R.fonts		??= {};" . "\n";
		$script .= "	_tpt.R.fonts.customFonts??= {};" . "\n";
		$script .= "	SR7.devMode			=  ".$devMode.";" . "\n";
		$script .= "	SR7.F 				??= {};" . "\n";
		$script .= "	SR7.G				??= {};" . "\n";
		$script .= "	SR7.LIB				??= {};" . "\n";
		$script .= "	SR7.E				??= {};" . "\n";
		$script .= "	SR7.E.gAddons		??= {};" . "\n";
		$script .= "	SR7.E.php 			??= {};" . "\n";
		$basic_metas = $this->get_basic_metas();
		foreach($basic_metas as $meta => $meta_val){
			$script .= "	SR7.E.php[". json_encode($meta) ."] = ". json_encode($meta_val) .";" . "\n";
		}
		//names which of the SR7.E.php keys are basic metas, so addon added ones resolve client side too
		$script .= "	SR7.E.basicMetas	= ". json_encode(array_keys($basic_metas)) .";" . "\n";
		$script .= "	SR7.E.nonce			= '". $this->environment()->csrf_token('RevSlider_Front') ."';" . "\n";
		$script .= "	SR7.E.ajaxurl		= '". $this->environment()->ajax_url() ."';" . "\n";
		$script .= "	SR7.E.resturl		= '". $this->environment()->rest_url() ."';" . "\n";
		$script .= "	SR7.E.slug_path		= '". str_replace(["\n", "\r"], '', RS_PLUGIN_SLUG_PATH) ."';" . "\n";
		$script .= "	SR7.E.slug			= '". str_replace(["\n", "\r"], '', RS_PLUGIN_SLUG) ."';" . "\n";
		$script .= "	SR7.E.plugin_url	= '". str_replace(["\n", "\r"], '', RS_PLUGIN_URL) ."';" . "\n";
		$script .= "	SR7.E.core_url		= '". str_replace(["\n", "\r"], '', RS_CORE_URL) ."';" . "\n";
		$script .= "	SR7.E.wp_plugin_url = '". str_replace(["\n", "\r"], '', $this->paths()->plugins_url()) . "/" ."';" . "\n";
		$script .= "	SR7.E.revision		= '". RS_REVISION ."';" . "\n";
		//DEV-only cache-bust: with devMode on, the loader (tptools.js ~21: ver = assetver ?? revision) re-fetches the individual
		//runtime JS every reload so source edits appear without a build. Release / devMode=false leaves assetver unset -> static revision.
		if($devMode === 'true') $script .= "	SR7.E.assetver		= '". time() ."';" . "\n";
		$script .= "	SR7.E.latest_revision = '". $this->get_options(['system', 'version'], RS_REVISION) ."';" . "\n";
		$script .= "	SR7.E.fontBaseUrl	= '". ($this->get_val($global, ['fonts', 'download'], $this->get_val($global, 'fontdownload', 'off')) === 'off' ? $this->modify_fonts_url('https://fonts.googleapis.com/css2?family=') : '') ."';" . "\n";
		$script .= "	SR7.G.breakPoints 	= [".implode(',', $breakpoints)."];" . "\n";
		$script .= "	SR7.G.fSUVW 		= ".(($fSUVW === true) ? 'true' : 'false').";" . "\n";
		if($this->_truefalse($this->get_val($global, ['opt', 'dpr1mobile'], false)) === true) $script .= "	SR7.G.dpr1mobile	= true;" . "\n";
		if($this->_truefalse($this->get_val($global, 'reducedMotion', false)) === true) $script .= "	SR7.G.reducedMotion	= true;" . "\n";
		if($this->get_val($global, ['gdpr', 'filter'], 'none') !== 'none'){
			$script .= "	SR7.G.gdprfilter	= '".$this->get_val($global, ['gdpr', 'filter'], 'none')."';". "\n";
			$script .= "	SR7.G.gdprcategory	= '". $this->get_val($global, ['gdpr', 'category'], 'marketing')."';". "\n";
		}
		$script .= "	SR7.E.modules 		= ['".implode("','", $modules)."'];" . "\n";
		if(!empty($assets['libs']))	$script .= '	SR7.E.libs 			= [\'' . implode("','", $assets['libs']) . '\'];' . "\n";
		if(!empty($assets['css']))	$script .= '	SR7.E.css 			= [\'' . implode("','", $assets['css']) . '\'];' . "\n";
		$script .= "	SR7.E.resources		??= {};" . "\n";
		foreach($assets['resources'] as $module => $url){
			$script .= "	SR7.E.resources[". json_encode($module) ."] ??= ". json_encode($url) .";" . "\n";
		}
		$script .= "	SR7.E.ytnc			= ".(($ytnc === false) ? 'false' : 'true').";" . "\n";

		$script = apply_filters('revslider_js_add_header_scripts_js', $script);

		// Add Page Handler Inline Script
		if($this->get_val($global, ['getTec', 'core'], 'MIX') !== 'REST' || $g['markup_export'] === true){
			$script .= '	SR7.JSON			??= {};' ."\n";
		}
		if($g['markup_export'] === false) $script .= (file_get_contents(RS_CORE_ASSETS_PATH . 'public/js/page.js'));

		$script .= '</script>' . "\n";
		$script = apply_filters('revslider_js_add_header_scripts', $script);
		$g['header_js'] = true;

		return $script;
	}

	/** @return string */
	public function load_v7_slider(){
		$g = &$this->context()->root();

		$used_slider	= [];
		$forced_slides	= [];
		$all_shortcodes = $g['sliders'];
		$script			= '';

		if(empty($all_shortcodes)) return $script;

		$serial		= $g['serial'];
		$collection	= $g['collections']['ids'];
		$g['collections']['ids'] = [];
		if($g['serial'] > 0){
			$g['serial'] = 0;
		}
		$global		= $this->get_global_settings();
		$mode		= $this->get_val($global, ['getTec', 'core'], 'MIX');
		foreach($all_shortcodes ?? [] as $alias){
			$slider	= new \RevSliderSlider();
			if(!$slider->check_alias($alias)) continue;
			
			$slider->init_by_alias($alias, false);
			if($slider->inited === false) continue;

			$dl = $slider->get_param('deepLinks', []);
			foreach($dl ?? [] as $slide_id){
				if(!in_array($slide_id, $forced_slides)) $forced_slides[] = $slide_id;
			}
			
			$used_slider[] = $slider;
		}

		foreach($used_slider ?? [] as $slider){
			$g['serial']++;
			$sid		= $slider->get_id();
			$slider_id	= $slider->get_param('id', '');
			$html_id	= (trim($slider_id) !== '') ? $slider_id : 'SR7_'.$sid.'_'.$g['serial'];
			$html_id	= $this->set_html_id_v7($html_id, true);
			$sticky     = $slider->get_param('sticky', '');
			if($sticky !== 'top' && $sticky !== 'bottom') $sticky = ($slider->get_param('fixed', false) !== false) ? 'top' : 'none';
			$full		= ($sticky !== 'none' || $slider->get_param('fixed', false) !== false || in_array($slider->get_param('type', ''), ['scene', 'hero', 'carousel'])) ? true : false;
			if($mode === 'MIX' && $g['serial'] > 2) $full = false; //we only print $forced_slides from now on
			$full		= ($slider->is_stream_post()) ? true : $full; //check if we are stream/post, these need to always write all layers (B-7235530610)

			if($g['markup_export'] === true){
				$script .= "	SR7.JSON['".$html_id."'] = 'assets/".$html_id.".json';"."\n";
			}else{
				$data	= $slider->get_full_slider_JSON(false, $full, [], $forced_slides);
				$data	= apply_filters('sr_load_slider_json', $data, $this);

				if($mode === 'MIX' && $g['serial'] > 2 && !empty($forced_slides)){ //we check if slides are in the forced_slides list, if not then we ignore
					$print = false;
					foreach($data['slides'] ?? [] as $slide){
						if(!in_array($this->get_val($slide, 'id'), $forced_slides)) continue;
						$print = true;
						break;
					}
					if($print === false) continue; //do not pront any information about the slider
				}

				//load dom data directly
				$script .= "	SR7.JSON['".$html_id."'] = ".json_encode($data).";"."\n";
			}

			if($mode === 'MIX' && $g['serial'] >= 2 && empty($forced_slides)) break;
		}
		$g['collections']['ids'] = $collection; //reset html ids here, so that they are later on empty when the page is parsed
		$g['serial'] = $serial; //reset back to what it was before

		return $script;
	}

	/**
	 * the fonts preconnect links, or an empty string when downloads or dpc turn them off
	 * @return string
	 **/
	public function get_fonts_preconnect_html(){
		$global = $this->get_global_settings();
		if($this->get_val($global, ['fonts', 'download'], 'off') !== 'off') return '';
		if($this->_truefalse($this->get_val($global,['fonts','dpc'], false)) === true) return '';

		return '<link rel="preconnect" href="https://fonts.googleapis.com">'."\n"
			 . '<link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>'."\n";
	}

	/**
	 * the collected deprecation notices as a script block, '' when nothing was collected
	 * @return string
	 */
	public function get_deprecation_html(){
		$g = &$this->context()->root();
		if(empty($g['deprecated'])) return '';

		$html  = self::script_open();
		$html .= 'window.SR7 ??= {};'."\n";
		$html .= 'SR7.E ??= {};'."\n";
		$html .= 'SR7.E.php ??= {};'."\n";
		$html .= 'SR7.E.php.warnings	= '.json_encode($g['deprecated']).';';
		$html .= '</script>'."\n";

		return $html;
	}
}
