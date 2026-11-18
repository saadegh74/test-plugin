<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/ 
 * @copyright 2024 ThemePunch
 */

namespace ThemePunch\SR\Core;

if(!defined('ABSPATH')) exit();

/**
 * Front end renderer - turns a slider into HTML, CSS and the JavaScript config.
 *
 * add_slider_to_stage() is the single entry point and drives the whole pass (slider wrapper -> slides ->
 * layers), echoing markup as it goes rather than building one big string. Because of that the object carries
 * the current position as state ($slide, $layer, $layer_depth …), which the many small get_html_* helpers
 * read instead of taking parameters.
 *
 * Collected assets (fonts, icons, images) are pushed into the render context so they can be emitted once per page,
 * and with the internal cache on the finished markup goes to RevSliderCache.
 */
class Output extends Functions {

	public $zIndex				= 1;
	public $caching				= false;
	/** @var array the picture the measurement mode draws from the markup: src, alt and title of the first slide */
	public $lcp_still			= [];
	public $html_id;			//holds the current html id
	public $slider;				//holds the current slider
	public $slide;				//holds the current slide
	public $slides;				//holds the current slides of the slider
	public $layers;				//holds the current layers of a slide
	public $layer;				//holds the current used layer
	public $_layer_filtered;			//memoized get_layer() result - avoids re-running the revslider_get_layer filter ~9x per layer
	public $_layer_filtered_valid = false;	//whether $_layer_filtered is current (invalidated on every write to $this->layer)
	public $stream_data			= []; //hilds the stream data, if we are a post/stream
	public $layer_depth			= '';
	public $static_slide		= [];
	public $revapi;				//holds the current JavaScript revapi
	public $slider_id			= 0; //holds the current slider id
	public $uid;				//holds the current layer unique id
	public $slide_id;			//holds the current slide id of a slide
	public $layer_id_prefix			= '';	//cached "<moduleid>-<slideid>-" prefix for layer ids (constant per slide)
	public $layer_id_prefix_slide	= null;	//the slide id the cached prefix was built for
	public $images 				= []; //holds all images
	public $gallery_ids			= [];

	public $offset 				= '';
	public $modal 				= '';
	public $usage 				= '';
	public $fullheight			= null;
	public $fullwidth			= null;
	public $ajax_loaded			= false;
	
	public $rs_module_open		= false;
	public $rs_module_closed	= false;
	
	public $enabled_sizes		= [];
	public $adv_resp_sizes		= [];
	public $icon_sets			= [];
	public $custom_order		= [];
	public $custom_settings		= [];
	public $custom_source		= [];
	public $custom_skin			= '';
	public $container_mode		= '';

	public $console_exception	= false;
	public $render_raw = ''; //raw-channel bytes collected by render_module(), echoed by the add_slider_base() shell
	public $preview_mode		= false;
	public $add_to				= []; //if set, the Slider will only be added if the current page/post meets what is into this variable

	public $global_settings     = false;

	public function __construct(){
		parent::__construct();
		$this->global_settings = $this->get_global_settings();
	}

	/** @return RevSliderSlider|false the rendered slider, false when it could not be output */
	public function add_slider_to_stage($sid){
		do_action('revslider_add_slider_to_stage_pre', $sid, $this);

		if(!$this->check_add_to()) return false;

		$locale = setlocale(LC_NUMERIC, 0);
		if($locale !== 'C') setlocale(LC_NUMERIC, 'C');

		try{
			$this->set_slider_id($sid);
			$this->add_slider_base();
		}finally{
			//LC_NUMERIC is process-wide: restore it even if add_slider_base() throws, otherwise every other
			//plugin keeps formatting numbers with the 'C' locale for the rest of the request
			if($locale !== 'C') setlocale(LC_NUMERIC, $locale);
		}
		
		do_action('revslider_add_slider_to_stage_post', $sid, $this);

		return $this->get_slider();
	}

	/**
	 * a premium-template module a lifetime or CodeCanyon key brought over from SR6 keeps showing without an SR7
	 * licence, changing it needs one (ADR-0027). Such a key installs no premium template in SR7, so each migrated
	 * one came along
	 * @since 7.2.6
	 * @return bool
	 */
	protected function kept_from_v6(){
		return $this->slider->get_param('migrated', '') === 'sr6' && in_array($this->get_options(['system', 'license_type'], ''), ['lifetime', 'cc'], true);
	}

	/**
	 * get the last slider after the output
	 * @return RevSliderSlider
	 */
	public function get_slider(){
		return apply_filters('revslider_get_slider', $this->slider, $this);
	}

	/**
	 * get the current slider_id
	 * @return int
	 */
	public function get_slider_id(){
		return apply_filters('revslider_get_slider_id', $this->slider_id, $this);
	}

	/**
	 * set the current slider_id
	 * @return void
	 */
	public function set_slider_id($sid){
		$this->slider_id = apply_filters('revslider_set_slider_id', $sid, $this);
	}

	/**
	 * set the current revapi for JavaScript
	 * @return void
	 */
	public function set_revapi($revapi){
		$this->revapi = $revapi;
	}

	/**
	 * set the slider manually
	 * @return void
	 */
	public function set_slider($slider){
		$this->slider = apply_filters('revslider_set_slider', $slider, $this);
	}
	
	/**
	 * set slide data and layers
	 * @return void
	 */
	public function set_slide($slide){
		$this->slide = apply_filters('revslider_set_slide', $slide, $this);
	}

	/**
	 * get slide data and layers
	 * @return RevSliderSlide
	 */
	public function get_slide(){
		return apply_filters('revslider_get_slide', $this->slide, $this);
	}
	
	/**
	 * set the output into ajax loaded mode
	 * so that i.e. fonts are pushed into footer
	 * @return void
	 */
	public function set_ajax_loaded(){
		//Essential Grid calls this cross-plugin (essential-grid.class.php ~3616, via the RevSliderOutput alias)
		//before add_slider_to_stage(); the flag is write-only inside v7 core, but the entry point is live API
		$this->ajax_loaded = true;
	}

	/**
	 * get the HTML ID
	 * @return string
	 */
	public function get_html_id($raw = true){
		$html_id = $this->html_id;
		$html_id = (!$raw) ? preg_replace("/[^a-zA-Z0-9]/", "", $html_id) : $html_id;
		
		return apply_filters('revslider_get_html_id', $html_id, $this, $raw);
	}

	/**
	 * set slide specific values that are needed by layers
	 * this is needed to be called before any layer is added to the stage
	 * @return void
	 **/
	public function set_slide_params_for_layers(){
		$slide = $this->get_slide();
		$this->set_slide_id($slide->get_id());
		$this->set_layers($slide->get_layers(true)); //true: scheduled off layers are dropped or marked here
	}
	
	/** @return void */
	public function set_offset($offset){
		$this->offset = wp_kses_post($offset);
	}
	
	/** @return string */
	public function get_offset(){
		return $this->offset;
	}
	
	/** @return void */
	public function set_modal($modal, $replace = true){
		if($replace){
			$this->modal = wp_kses_post($modal);
		}else{
			if(substr($this->modal, -1) !== ';') $this->modal .= ';';
			$this->modal .= wp_kses_post($modal);
		}
	}
	
	/** @return string */
	public function get_modal(){
		return $this->modal;
	}
	
	/** @return void */
	public function set_usage($usage){
		$this->usage = wp_kses_post($usage);
	}
	
	/** @return string */
	public function get_usage(){
		return $this->usage;
	}
	
	/** @return void */
	public function set_fullheight($fullheight){
		if ($fullheight === "") return;
		$this->fullheight = $this->_truefalse(wp_kses_post($fullheight));
	}

	/** @return void */
	public function set_fullwidth($fullwidth){
		if ($fullwidth === "") return;
		$this->fullwidth = $this->_truefalse(wp_kses_post($fullwidth));
	}

	/** @return string */
	public function get_fullheight(){
		return $this->fullheight;
	}

	/** @return string */
	public function get_fullwidth(){
		return $this->fullwidth;
	}

	/**
	 * set slide slide_id
	 * @return void
	 */
	public function set_slide_id($slide_id){
		$this->slide_id = apply_filters('revslider_set_slide_id', $slide_id, $this);
	}

	/**
	 * set the slides so that it can be used from anywhere
	 * @return void
	 **/
	public function set_current_slides($slides){
		$slider_id = $this->get_slider_id();
		$this->slides = apply_filters('sr_set_current_slides', $slides, $slider_id);
	}

	
	/**
	 * get the slides so that it can be used from anywhere
	 * @return array
	 **/
	public function get_current_slides(){
		return $this->slides;
	}
	
	/**
	 * set static_slide data and layers
	 * @return void
	 */
	public function set_static_slide($slide){
		$this->static_slide = apply_filters('revslider_set_static_slide', $slide, $this);
	}
	
	/**
	 * get static_slide data and layers
	 * @return array
	 */
	public function get_static_slide(){		
		return apply_filters('revslider_get_static_slide', $this->static_slide, $this);
	}
	
	/**
	 * get do_static
	 * @return bool
	 */
	public function get_do_static(){
		return apply_filters('revslider_get_do_static_layers', $this->do_static, $this);
	}

	/**
	 * get slide slide_id
	 * @return int
	 */
	public function get_slide_id(){
		return apply_filters('revslider_get_slide_id', $this->slide_id, $this);
	}
	
	/**
	 * set slide layers
	 * @return void
	 */
	public function set_layers($layers){
		$this->layers = apply_filters('revslider_set_layers', $layers, $this);
	}
	
	/**
	 * get slide layers
	 * @return array
	 */
	public function get_layers(){
		return apply_filters('revslider_get_layers', $this->layers, $this);
	}
	
	/**
	 * set slide layer
	 * @return void
	 */
	public function set_layer($layer){
		$this->layer = apply_filters('revslider_set_layer', $layer, $this);
		$this->layer['has_shortcode'] = false;
		$this->_layer_filtered_valid = false; //layer replaced - drop memoized get_layer() result
	}
	
	/**
	 * get slide layer
	 * @return array
	 */
	public function get_layer(){
		//memoize the filtered layer; the revslider_get_layer filter is re-run only after $this->layer changes
		//(set_layer() or a has_shortcode write below invalidate it). callers still get their own array copy.
		if($this->_layer_filtered_valid === false){
			$this->_layer_filtered		 = apply_filters('revslider_get_layer', $this->layer, $this);
			$this->_layer_filtered_valid = true;
		}
		return $this->_layer_filtered;
	}

	/**
	 * set the gallery ids variable
	 * @return void
	 */
	public function set_gallery_ids($ids){
		if(!empty($ids)) $ids = array_filter(array_map('intval', $ids));
		$this->gallery_ids = apply_filters('revslider_set_gallery_ids', $ids, $this);
	}
	
	/**
	 * get the gallery ids variable
	 * @return array
	 */
	public function get_gallery_ids(){
		return apply_filters('revslider_get_gallery_ids', $this->gallery_ids, $this);
	}
	
	/**
	 * get current layer depth
	 * @return string the current indentation for generated markup - purely cosmetic, keeps the emitted
	 *                HTML readable while nesting into slides and layers
	 */
	public function ld(){
		return $this->layer_depth;
	}
	
	/**
	 * increase current layer depth
	 * this is only for the HTML looks
	 * @return void
	 */
	public function increase_layer_depth(){
		$this->layer_depth .= '	';
	}
	
	/**
	 * decrease current layer depth
	 * this is only for the HTML looks
	 * @return void
	 */
	public function decrease_layer_depth(){
		if(!empty($this->layer_depth)){
			$this->layer_depth =  substr($this->layer_depth, 0, -1);
		}
	}

	/**
	 * get the preview
	 * @return bool
	 */
	public function get_preview_mode(){
		return apply_filters('revslider_get_preview_mode', $this->preview_mode, $this);
	}
	
	/**
	 * set the preview_mode
	 * @return void
	 */
	public function set_preview_mode($preview_mode){
		$this->preview_mode = apply_filters('revslider_set_preview_mode', $preview_mode, $this);
		$this->context()->set('preview_mode', $this->preview_mode);
	}

	/**
	 * set the markup_export variable
	 * @return void
	 */
	public function set_markup_export($markup_export){
		$this->context()->set('markup_export', apply_filters('revslider_set_markup_export', $markup_export, $this));
	}
	
	/**
	 * get the markup_export variable
	 * @return bool
	 */
	public function get_markup_export(){
		return apply_filters('revslider_get_markup_export', $this->context()->get('markup_export', false), $this);
	}
	
	/**
	 * set the custom settings
	 * @return void
	 */
	public function set_custom_settings($settings){
		$settings = preg_replace('/\\\\u([0-9a-fA-F]{4})/', '', $settings);
		$settings = ($settings !== '' && !is_array($settings)) ? json_decode(str_replace(['({', '})', "'"], ['[', ']', '"'], wp_kses_post($settings)), true) : $settings;
		
		$this->custom_settings = apply_filters('revslider_set_custom_settings', $settings, $this);
	}
	
	/**
	 * get the custom settings
	 * @return array
	 */
	public function get_custom_settings(){
		return apply_filters('revslider_get_custom_settings', $this->custom_settings, $this);
	}

	/**
	 * register the alias for the JSON later to be printed
	 * @return void
	 */
	public function register_module(){
		$this->context()->push('sliders', $this->slider->get_alias());
	}
	
	/**
	 * check the add_to
	 * return true / false if the put in string match the current page.
	 * @before isPutIn()
	 * @return bool
	 */
	public function check_add_to($empty_is_false = false){
		$add_to = $this->get_add_to();

		if($empty_is_false && (empty($add_to) || count($add_to)===1 && empty(reset($add_to)))) return false;
		
		if(count($add_to) === 1 && in_array('homepage', $add_to, true)){ //only add if we are the homepage
			if($this->get_current_page_id() !== 'homepage') return false;
		}elseif(!(empty($add_to) || count($add_to)===1 && empty(reset($add_to)))){
			$add_to_pages = [];
			foreach($add_to ?? [] as $page){
				$page = trim(strtolower($page));
				if(is_numeric($page) || $page == 'homepage') $add_to_pages[] = $page;
			}
			
			//check if current page is in list
			if(empty($add_to_pages)) return false;
		
			$cp_id = $this->get_current_page_id();
			if(array_search($cp_id, $add_to_pages) === false) return false;
		}
		
		return true;
	}
	
	/**
	 * do the Global Settings load the SR7 libraries on this page: "Load JS Libraries Globally", or the page is one of
	 * the "Pages to Load SR JS Libraries" (page ids or homepage). Leaves this instance's add_to as it was.
	 * @since 7.2.1
	 * @return bool
	 */
	public function libraries_allowed_on_page(){
		$global	= $this->get_global_settings();
		$all	= $this->_truefalse($this->get_val($global, 'inclAll', true));
		if($all !== false) return ($all === true); //a value that reads as neither loads nowhere, as the front always treated it

		$add_to		= $this->add_to;
		$this->set_add_to($this->get_val($global, 'incl', []));
		$allowed	= ($this->check_add_to(true) === true);
		$this->add_to	= $add_to;

		return $allowed;
	}

	/**
	 * set the add_to variable
	 * @return void
	 */
	public function set_add_to($add_to) {
		$this->add_to = apply_filters('revslider_set_add_to', $add_to, $this);
	}
	
	/**
	 * get the add_to variable
	 * @return array
	 */
	public function get_add_to(){
		return (array)apply_filters('revslider_get_add_to', $this->add_to, $this);
	}
	
	/**
	 * set the custom settings
	 * @return void
	 */
	public function set_custom_skin($skin){
		$this->custom_skin = apply_filters('revslider_set_custom_skin', $skin, $this);
	}
	
	/**
	 * set the custom order
	 * @return void
	 */
	public function set_custom_order($order){
		$order = ($order !== '' && !is_array($order)) ? explode(',', $order) : $order;
		$order = is_array($order) ? array_values(array_filter(array_map('trim', $order), 'strlen')) : [];
		
		$this->custom_order = apply_filters('revslider_set_custom_order', $order, $this);
	}

	/**
	 * what the shortcode asks of a post based module: post_ids, category, taxonomy. Applied to the module before
	 * its slides are fetched, and written to the markup as data-query so the front end's stream request asks the same
	 * @since 7.2.0
	 * @param array $source
	 * @return void
	 */
	public function set_custom_source($source){
		$source = array_filter(array_map('trim', array_map('strval', (array)$source)), 'strlen');

		$this->custom_source = apply_filters('revslider_set_custom_source', $source, $this);
	}

	/**
	 * @since 7.2.0
	 * @return array
	 */
	public function get_custom_source(){
		return apply_filters('revslider_get_custom_source', $this->custom_source, $this);
	}
	
	/**
	 * set the current layer unique id
	 * @return void
	 **/
	public function set_layer_unique_id(){
		$layer	= $this->get_layer();
		$uid	= $this->get_val($layer, 'id');

		if(!is_numeric($uid)) $uid = $this->zIndex;
		
		$this->uid = apply_filters('revslider_set_layer_unique_id', $uid, $layer, $this);
	}

	/**
	 * set usage specific parameters to the output
	 * @return void
	 **/
	public function set_usage_values(){
		$usage = $this->get_usage();

		//modal uses cover color and cover speed
		if($usage === 'modal'){
			$modal = '';
			$c = ($this->slider->get_param(['modal', 'cover'], true) === true) ? ($this->slider->get_param(['modal', 'bg'], 'rgba(0,0,0,0.5)')) : "transparent";
			$s = $this->slider->get_param(['modal', 'sp'], 1000);
			$scr = $this->slider->get_param(['modal', 'pS'],false) === true ? 'true' : 'false';
			$h = $this->slider->get_param(['modal', 'h'], "center");
			$v = $this->slider->get_param(['modal', 'v'], "middle");
			
			if(!empty($c)) $modal .= 'bg:'.esc_attr($c).';';
			if(!empty($s)) $modal .= 'sp:'.esc_attr($s).';';
			if(!empty($scr)) $modal .= 'pS:'.esc_attr($scr).';';
			if(!empty($h)) $modal .= 'h:'.esc_attr($h).';';
			if(!empty($v)) $modal .= 'v:'.esc_attr($v).';';

			if(!empty($modal)) $this->set_modal($modal, false);
		}
	}
	
	/**
	 * get the current layer unique id
	 * @return string
	 **/
	public function get_layer_unique_id(){		
		return apply_filters('revslider_get_layer_unique_id', $this->uid, $this);
	}

	/**
	 * get the simple link that can be inside the actions of a layer
	 * @return string
	 **/
	public function get_action_link(){
		$link	= '';
		$layer	= $this->get_layer();
		$action	= $this->get_val($layer, ['actions', 'action'], []);

		if(!empty($action)){
			foreach($action as $act){
				// these are needed for the Social Share Addon
				$action_type = apply_filters('rs_action_type', $this->get_val($act, 'action'));
				$link_type = apply_filters('rs_action_link_type', $this->get_val($act, 'link_type', ''));
				if(in_array($action_type, ['menu', 'link'], true)){
					if($action_type === 'link' && $link_type === 'jquery') break;

					$http			= $this->get_val($act, 'link_help_in', 'keep');
					$_link			= ($action_type === 'menu') ? $this->remove_http($this->get_val($act, 'menu_link', ''), $http) : $this->remove_http($this->get_val($act, 'image_link', ''), $http);
					if($this->has_any_shortcode($_link)){
						$this->layer['has_shortcode'] = true;
						$this->_layer_filtered_valid = false;
					}
					$_link			= $this->environment()->expand_content($_link);
					$_link			= $this->resolve_basic_metas($_link);
					$link_open_in	= $this->get_val($act, 'link_open_in', '');
					$link			= 'href="'.esc_url($_link).'"';
					$link			.= ($link_open_in !== '') ? ' target="'.esc_attr($link_open_in).'"' : '';
					if($this->get_val($act, 'link_follow', '') === 'nofollow'){
						$link .= ' rel="nofollow';
						$link .= ($link_open_in === '_blank') ? ' noopener' : '';
						$link .= '"';
					}else{
						$link .= ($link_open_in === '_blank') ? ' rel="noopener"' : '';
					}
					break;
				}
			}
		}
		
		return $link;
	}

	/**
	 * add elements depending on v7 data
	 * @return string
	 **/
	public function get_action_link_v7(){
		$html	= '';
		$layer	= $this->get_layer();
		$rel	= $this->get_val($layer, 'rel', '');

		if($this->get_val($layer, 'tag', 'sr7-txt') === 'a'){
			$html 	.= ' href="'.esc_url($this->resolve_basic_metas($this->get_val($layer, 'href', ''))).'"';
			$target	= $this->get_val($layer, 'target', '');
			if(!empty($target)) $html .= ' target="'. esc_attr($target) .'"';
		}

		if(!empty($rel)) $html .= ' rel="'. esc_attr($rel) .'"';

		return $html;
	}


	/**
	 * get the layer tag as it can change through settings and others
	 * @return string
	 **/
	public function get_layer_tag($html_simple_link, $special_type = false){
		$layer	= $this->get_layer();
		$layer	= $this->filter_single_layer_tags($layer, 'tag', 'sr7-txt');
		$tag	= $this->get_val($layer, 'tag', 'sr7-txt');
		
		if($html_simple_link !== '' && empty($this->slider)) $tag = 'a';
		if($special_type !== false)	 $tag = 'sr7-'.$special_type; //if we are special type, only allow div to be the structure, as we will close with a div outside of this function

		return ($tag !== 'div') ? esc_attr($tag) : 'sr7-txt';
	}

	/**
	 * returns the HTML layer type
	 * @return string
	 */
	public function get_html_layer_type(){
		return 'data-type="'.esc_attr($this->get_layer_type()).'"';
	}

	/**
	 * return the layer Type for further needs	 
	 * @return string
	 */
	public function get_layer_type(){
		$layer = $this->get_layer();
		return $this->get_val($layer, 'type', 'text');
	}

	/**
	 * get all direct layer attr
	 * @return string
	 */
	public function get_layer_attr(){
		$layer = $this->get_layer();
		
		return ($this->get_val($layer, 'has_shortcode', false) === true) ? 'srscsrc' : '';
	}

	/**
	 * get the html class for a layer
	 * @return string
	 **/
	public function get_html_class($class, $layer_tag){
		$html	= '';
		$class	= trim($class);
		$c		= [];

		if($class !== '') $c = explode(' ', $class);
		if(!in_array($layer_tag, ['rs-row', 'rs-column', 'sr7-layer', 'rs-group', 'rs-bgvideo'], true)) $c[] = 'sr7-layer';
		if($this->get_val($this->layer, 'schOff', false) === true) $c[] = 'sr7-sch-off';
		
		$c		= apply_filters('revslider_add_layer_classes', $c, $this->layer, $this->slide, $this->slider);
		
		return (!empty($c)) ? 'class="'.implode(' ', $this->filter_class_name($c)).'"' : '';
	}

	/**
	 * retrieves the current layer attribute id by given target
	 * @return string
	 **/
	public function get_layer_attribute_id($target){
		$layer_attribute_id = $this->slide->get_layer_id_by_uid($target, $this->static_slide);
		
		$id = $this->slider->get_id();
		if($target == 'backgroundvideo' || $target == 'firstvideo'){
			$layer_attribute_id = $target;
		}elseif(trim($layer_attribute_id) == ''){
			if(strpos($target, 'static-') !== false){
				$ss = $this->get_static_slide();
				$layer_attribute_id = 'slider-'.preg_replace("/[^\w]+/", "", $id).'-slide-'.$ss->get_id().'-layer-'.str_replace('static-', '', $target);
			}elseif($this->static_slide){
				$layer_attribute_id = 'slider-'.preg_replace("/[^\w]+/", "", $id).'-slide-'.preg_replace("/[^\w]+/", "", $this->get_slide_id()).'-layer-'.str_replace('static-', '', $target);
			}else{
				$layer_attribute_id = 'slide-'.preg_replace("/[^\w]+/", "", $this->get_slide_id()).'-layer-'.$target;
			}
		}
		
		return $layer_attribute_id;
	}

	/**
	 * get the layer ids as HTML
	 * @return string
	 **/
	public function get_html_layer_ids($raw = false){
		$layer	= $this->get_layer();
		$ids	= $this->get_val($layer, ['attr', 'id']);
		if(trim($ids) == ''){
			//the "<moduleid>-<slideid>-" prefix is constant for every layer in a slide, so build it once
			//per slide instead of re-running get_html_id()/get_slide_id()/preg_replace on every layer
			$slide_id = $this->get_slide_id();
			if($this->layer_id_prefix_slide !== $slide_id){
				$this->layer_id_prefix			= $this->get_html_id().'-'.preg_replace("/[^\w]+/", "", $slide_id).'-';
				$this->layer_id_prefix_slide	= $slide_id;
			}
			$ids = $this->layer_id_prefix.$this->get_layer_unique_id();
		}

		if($raw === false) $ids = ($ids != '') ? 'id="'.$this->filter_class_name($ids).'"' : '';

		return $ids;
	}

	/**
	 * adds the Slider Basis
	 * the main render pass: wrapper div, slides, layers, then the JavaScript config
	 * @return bool
	 */
	public function add_slider_base(){
		$start_ob_level = ob_get_level();
		try{
			$r = $this->render_module();
			if($r === false) return false;
			echo $r['raw'];
			$this->render_raw = ''; //sent; a later throw must not repeat it in the catch below
			if($r['ok'] === false) return false;
			if($r['cached'] === true) return true;
			echo $r['filtered'];
			$this->add_fonts_v7();

			//fonts are queued after the echo, so the delta is only complete here
			$ctx = $this->context();
			if($r['globals'] !== false && $this->ajax_loaded !== true && $ctx->get('markup_export') === false && $ctx->get('loaded_by_editor') === false){
				$cache = Registry::instance()->get('RevSliderCache');
				$cache->add_addition('special', 'globals', $cache->diff_globals($r['globals']));
				$cache->set_full_transient($this->get_transient_alias(), $this->get_slider_id(), $r['filtered']);
			}
		}catch(\Throwable $e){ //Throwable: a TypeError/Error skipped this handler entirely, leaving the output buffer open and killing the page
			$message = $e->getMessage();
			//the page shows the message alone; where it came from is only in the log
			if(defined('WP_DEBUG') && WP_DEBUG) error_log('Slider Revolution output: ' . $message . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString());
			if(ob_get_level() > $start_ob_level){
				ob_clean();
				ob_end_clean();
			}

			echo $this->render_raw; //the pre-split code had already sent these bytes (the YouTube api tag) before the throw, so they stay on the page

			// Do not output error markup in REST API requests
			if (defined('REST_REQUEST') && REST_REQUEST) $this->console_exception = true;
			if($this->console_exception){
				$this->print_error_message_console($message);
			}else{
				$this->print_error_message($message);
			}
		}
	}

	/**
	 * bring a module saved by an older plugin version up to the current data format before it
	 * renders. Writes to the database, so it is a host-side side effect, not part of the pure
	 * markup pass; it stays at its call position in render_module() until the adapter owns it
	 * as a pre-render step.
	 * @since 7.2.1
	 * @return void
	 */
	public function upgrade_stale_module_data(){
		if($this->get_preview_mode() !== false) return;
		if(version_compare($this->slider->get_param(['settings', 'version']), $this->get_options(['update', 'latest-version'], '6.0.0'), '<')){
			$upd	= new \ThemePunch\SR\Core\Admin\Upgrade();
			$slider_id = $this->slider->get_id();
			$upd->upgrade_slider_to_latest($this->slider);
			$this->slider->init_by_id($slider_id);
		}
	}

	/**
	 * build one module without sending a byte: the markup pass behind add_slider_base().
	 * raw carries what must never pass the revslider_html_output filter (the YouTube api
	 * tag, a cache-hit module); filtered is the module markup after that filter.
	 * @since 7.2.1
	 * @return array|false false when there is no module to render; ['raw' => string, 'filtered' => string, 'cached' => bool, 'ok' => bool],
	 *                     a built module adds 'globals' => array|false, snapshot_globals() from before the build, false with the cache off
	 */
	public function render_module(){
		$this->render_raw = '';

		$g = &$this->context()->root();

		$g['serial']++; //set the serial +1, so that if we have the slider two times, it has different ID's for sure

		if(empty($this->slider)){
			$this->slider = new Slider(); 
			$this->slider->init_by_mixed($this->get_slider_id(), false);
			if($this->slider->inited === false) $this->slider = null;
		}

		if(empty($this->slider)) return false;
		
		$this->slider = apply_filters('revslider_add_slider_base', $this->slider);
		
		$this->render_raw .= $this->add_youtube_api_html();

		$this->upgrade_stale_module_data();

		$this->slider->set_params($this->apply_brand_kit($this->slider->get_params())); //bg.color, pLoader.color and modal.bg go straight into the markup

		//A module in the trash renders nothing, the same as one that was deleted. It is reached by id and by
		//alias, from a shortcode, a block and every page builder, and this is the one place they all pass
		if($this->slider->is_trashed()) return ['raw' => $this->render_raw, 'filtered' => '', 'cached' => false, 'ok' => false];

		//the initial id can be an alias, so reset the id now
		$sid = $this->slider->get_id();
		$this->set_slider_id($sid);
		$this->set_usage_values();
		//current post: the stream request found its post by the referer, and a page cache keeps that GET per URL,
		//so every page got the first page's post. Naming the post puts it into the URL
		$entry = $this->environment()->current_entry_id();
		if($entry > 0 && empty($this->custom_source['post_ids']) && in_array($this->slider->get_param(['source', 'type'], 'gallery'), ['post', 'posts'], true) && $this->slider->get_param(['source', 'post', 'subType'], 'post') === 'current_post'){
			$this->custom_source['post_ids'] = (string)$entry;
		}
		if(!empty($this->custom_source)) $this->slider->apply_source_query($this->custom_source);

		if($this->_truefalse($this->slider->get_param(['general', 'disableOnMobile'], false)) === true && $this->environment()->is_mobile_request()) return ['raw' => $this->render_raw, 'filtered' => '', 'cached' => false, 'ok' => false];	//_truefalse: the editor stores this as a string, and a strict === let the module through while the front end's own guard blanked it
		
		if($this->_truefalse($this->slider->get_param('prem', false)) === true && $this->_truefalse($this->get_options(['system', 'valid'], 'false')) === false && $g['preview_mode'] === false && !$this->kept_from_v6()){
			$this->console_exception = true;
			$this->throw_error(__('Please register the Slider Revolution plugin to use premium templates.', 'revslider'));
		}

		$this->register_module();

		ob_start(); //the cached module echoes on its own; capture it onto the raw channel
		$cached = $this->get_from_caching();
		$this->render_raw .= ob_get_clean();
		if($cached) return ['raw' => $this->render_raw, 'filtered' => '', 'cached' => true, 'ok' => true];

		//a module is its markup plus what it leaves in SR_GLOBALS; the shell stores the difference once the fonts are queued
		$cache_globals = ($this->caching === true) ? Registry::instance()->get('RevSliderCache')->snapshot_globals() : false;

		$slider_id	= $this->slider->get_param('id', '');
		$html_id	= (trim($slider_id) !== '') ? $slider_id : 'SR7_'.$sid.'_'.$g['serial'];
		$revapi		= (in_array('sr7'.$sid, $g['collections']['js']['revapi'], true)) ? 'revapi'.$sid.'_'.$g['serial'] : 'revapi'.$sid; //frozen, unreached in v7: nothing writes collections.js.revapi, this always yields 'revapi'.$sid
		$this->set_html_id($html_id);
		$this->set_revapi($revapi);
		
		ob_start();
		
		echo $this->get_slider_div();

		/**
		 * stream feed =  JSON -> it should gather the feeds, prepare slides and push in the SR7.M[sliderid].stream
		 * stream feed =  REST -> we get the data and prepare the slides, so that content is beeing written for SEO, we do not need to push it into SR_GLOBALS, as it gets fetched later on 
		 */
		$this->stream_data = $this->slider->get_stream_data();
		if($this->get_val($this->global_settings, ['getTec', 'feed'], 'REST') === 'JSON'){
			if(!empty($this->stream_data)) $g['collections']['js']['stream'][$html_id] = $this->stream_data;
		}

		echo $this->get_slides();

		echo $this->close_slider_div();
		echo $this->js_get_start_size();
		//echo $this->js_get_custom_settings();
		
		if($g['loaded_by_editor'] === true){ //for elementor
			$this->add_js();
		}else{
			add_action('wp_print_footer_scripts', [$this, 'add_js'], 99); //host-fired event on the shimmable hook bus - a non-WP host fires or maps its own footer moment
		}

		$this->set_javascript_variables();

		do_action('revslider_add_slider_base_post', $this);
		
		$content = ob_get_contents();
		ob_clean();
		ob_end_clean();
		
		return ['raw' => $this->render_raw, 'filtered' => apply_filters('revslider_html_output', $content, $this), 'cached' => false, 'ok' => true, 'globals' => $cache_globals];
	}

	/**
	 * set the HTML ID
	 * @since 6.1.6: added option to check for duplications
	 * @return void
	 */
	public function set_html_id($html_id, $check_for_duplication = true){
		$html_id = $this->set_html_id_v7($html_id, $check_for_duplication);
		
		$this->html_id = apply_filters('revslider_set_html_id', $html_id, $this);
	}

	/**
	 * return the responsive sizes
	 * @since: 5.0
	 * @return array
	 **/
	public function get_responsive_size_v7($slider){
		$uSize = $slider->slider->get_param('uSize');
		$sizes = [
			'height'	=> $slider->slider->get_param(['size', 'height'], []),
			'width'		=> $slider->slider->get_param(['size', 'width'], []),
			'cacheSize'	=> $slider->slider->get_param(['size', 'cachedHeight'], [])
		];

		foreach($sizes as $type => $size){
			$default = $this->get_biggest_device_setting_v7($size, $this->enabled_sizes);
			if(empty($size)){
				$sizes[$type] = array_fill(0, 5, $default);
				continue;
			}

			foreach($size as $l => $v){										
				if(!empty($uSize) && isset($uSize[$l]) && $uSize[$l] === false){
					$sizes[$type][$l] = $default;
				}else{
					$default = $v;
				}
			}
		}
		foreach($sizes as $type => $size){
			$sizes[$type] = str_replace('px', '', implode(',', $sizes[$type]));
		}

		return $sizes;
	}


	/**
	 * creates the div container for Sliders
	 * @return string
	 **/
	public function get_slider_div(){
		$class	= $this->slider->get_param('class', '');
		$class	= (!is_array($class) && !empty($class)) ? explode(' ', $class) : $class;
		$class	= (empty($class)) ? [] : (array)$class;
		$modal	= $this->get_modal();
		$id		= $this->slider->get_id();
		$alias	= $this->slider->get_alias();
		$gallery = $this->get_gallery_ids();
		$this->rs_module_open = true;
		/*if($this->slider->get_param(['size', 'MPOU'], true) == true) {
			if($this->slider->get_param(['size', 'overflow'], true) == true) $class[] = 'sr-ov-hidden';
		}*/
		
		$r = "\n".RS_T4.'<p class="rs-p-wp-fix"></p>'."\n";
		$r .= RS_T4.'<sr7-module data-alias="'. esc_attr($alias) .'" data-id="'. esc_attr($id) .'" id="'. esc_attr($this->get_html_id()) .'"';
		$r .= (!empty($class)) ? ' class="'. implode(' ', $this->filter_class_name($class)) .'"' : '';
		$r .= ' data-version="'. RS_REVISION .'"';
		$r .= (!empty($modal)) ? ' data-style="display:none" data-modal="'.esc_attr($modal).'"' : '';
		$r .= (!empty($gallery)) ? ' data-source="wp-gallery" data-sourceids="'.implode(',', $gallery).'"' : '';
		//what the shortcode asked for, carried to the front end: the JSON it loads still holds every slide and the
		//stream it asks for is built from the saved module, so both have to be told (module.js reads them as settings.embed)
		$r .= (!empty($this->custom_order)) ? ' data-order="'.esc_attr(implode(',', $this->custom_order)).'"' : '';
		$r .= (!empty($this->custom_source)) ? ' data-query="'.esc_attr(wp_json_encode($this->custom_source)).'"' : '';
		$r .= '>'."\n";
		$r .= RS_T5.'<sr7-adjuster></sr7-adjuster>'."\n";
		$r .= RS_T5.'<sr7-content>'."\n";

		if($this->slider->get_param(['type'], 'slider') == 'carousel') $r .= RS_T6.'<sr7-carousel>'."\n";

		// Backward compatibility
		if (has_filter('revslider_get_slider_wrapper_div')) {
			$this->add_deprecation_message('revslider_get_slider_wrapper_div', 'revslider_get_slider_div');
			$r = apply_filters_deprecated(
				'revslider_get_slider_wrapper_div',
				[$r, $this],
				'7.0',
				'revslider_get_slider_div',
				'This filter was renamed. Please update your integration.'
			);
		}

		return apply_filters('revslider_get_slider_div', $r, $this);
	}

	/**
	 * the sample items of admin/includes/page-items.json, handed in as a page would (the editor's stage reads the same
	 * file); their pictures are named relative to the core
	 * @since 7.2.9
	 * @return string
	 */
	public function page_item_samples(){
		$doc = json_decode((string)@file_get_contents(RS_CORE_PATH . 'admin/includes/page-items.json'), true);
		if(!is_array($doc['records'] ?? null)) return '';
		foreach($doc['records'] as &$item){
			foreach(['media', 'thumb'] as $k) if(!empty($item[$k]['src']) && !preg_match('#^(https?:)?//#', $item[$k]['src'])) $item[$k]['src'] = RS_CORE_URL_CLEAN . $item[$k]['src'];
		}
		unset($item);
		return RS_T5.'<script type="application/json" data-sr-items>'.json_encode($doc, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES).'</script>'."\n";
	}

	/**
	 * close the div container for Sliders
	 * @return string
	 **/
	public function close_slider_div(){
		$this->set_image_lists();

		$r = '';
		if($this->slider->get_param(['type'], 'slider') == 'carousel') $r .= RS_T6.'</sr7-carousel>'."\n";
		
		$r .= RS_T5.'</sr7-content>'."\n";
		$r .= $this->get_lcp_still();
		if($this->get_markup_export() === false) $r .= $this->add_image_lists();
		if($this->context()->get('preview_page') && $this->slider->get_param(['source', 'type'], '') === 'page') $r .= $this->page_item_samples(); //a preview stands in for the page
		$r .= RS_T4.'</sr7-module>'."\n";

		$this->rs_module_closed = true;

		// Backward compatibility
		if (has_filter('revslider_close_slider_wrapper_div')) {
			$this->add_deprecation_message('revslider_close_slider_wrapper_div', 'revslider_close_slider_div_and_call_prepare');
			$r = apply_filters_deprecated(
				'revslider_close_slider_wrapper_div',
				[$r, $this],
				'7.0',
				'revslider_close_slider_div_and_call_prepare',
				'This filter was renamed. Please update your integration.'
			);
		}
		
		return apply_filters('revslider_close_slider_div_and_call_prepare', $r, $this);
	}

	/** @return array */
	public function get_images_list(){
		return apply_filters('sr_get_image_lists', $this->images, $this);
	}

	/** @return void */
	public function set_image_lists(){
		foreach($this->stream_data ?? [] as $stream){
			$media = $this->get_val($stream, 'media');
			if(empty($media)) $media = $this->get_val($stream, 'thumb');
			if(empty($media)) continue;
			$this->images[] = ['src' => $media, 'orig' => $media];
		}

		$flat = [];
		foreach($this->slider->get_param('imgs', []) ?? [] as $image){
			if(!is_array($image)) $image = ['src' => $image];
			$src = $this->get_val($image, 'src');
			//per device the src is [ld, d, n, t, m] with '#a' for inherit: every real picture in there is an image of the module
			$srcs = is_array($src) ? array_unique(array_filter($src, function($s){ return is_string($s) && $s !== '' && $s !== '#a'; })) : [$src];
			foreach($srcs as $s){
				$one = $image;
				$one['src'] = $s;
				$one['orig'] = $s;
				$flat[] = $one;
			}
		}
		$this->images = array_merge($flat, $this->images);
	}

	/**
	 * prepares list of images found in the slider
	 * in case of a stream/post based slider, images will be added here, too
	 * @return string
	 **/
	public function add_image_lists(){
		$used_images = $this->get_images_list();
		$images = [];
		foreach($used_images ?? [] as $image){
			$imgsrc	 = $this->remove_http($this->get_val($image, 'src'));
			$imgorig = $this->remove_http($this->get_val($image, 'orig'));
			if(empty($imgsrc) || empty($imgorig) || strpos(wp_check_filetype($image['src'])['type'], 'image/') === false) continue;

			$lib_id	 = $this->get_val($image, 'lib_id');
			$info	 = false;
			$attr	 = [
				'alt' => $this->get_val($image, 'a', false),
				'title' => $this->get_val($image, 't', false),
			];
			foreach($attr as $key => $value){
				if($value === '#fn'){
					$info	= ($info === false) ? pathinfo($imgsrc) : $info;
					$value	= $this->get_val($info, 'filename', '');
				}elseif($value === false || $value === ''){	//only an image WITHOUT a stored alt/title falls back to the Media Library, a custom one is the value itself
					$value	= (intval($lib_id) < 1) ? '' : (($key === 'title') ? $this->media_library()->title($lib_id) : $this->media_library()->alt_text($lib_id));
				}
				$attr[$key]	= $value;
			}

			$additions = (!empty($lib_id)) ? ' data-libid="'. esc_attr($lib_id) .'"' : '';
			$additions .= (!empty($this->get_val($image, 'lib'))) ? ' data-lib="'. esc_attr($this->get_val($image, 'lib')) .'"' : '';
			$additions .= (!empty($this->get_val($image, 'src2'))) ? ' data-src2="'. esc_attr($this->remove_http($this->get_val($image, 'src2'))) .'"' : '';
			foreach($attr as $key => $value){
				$additions .= (!empty($value)) ? ' '.$key.'="'. esc_attr(strip_tags($value)) .'"' : '';
			}

			$images[] = RS_T6.'<img data-src="'. esc_attr($imgsrc) .'"'.$additions.' width="0" height="0" data-dbsrc="'. base64_encode($imgorig) .'"/>';
		}

		$images = apply_filters('sr_add_image_lists', $images, $this);

		return (empty($images)) ? '' : RS_T5.'<image_lists style="display:none">'."\n".
				implode("\n", $images) . "\n" .
				RS_T5.'</image_lists>'."\n";
	}

	/**
	 * add custom settings for modules
	 * @return string
	 */
	public function js_get_custom_settings(){ //frozen, unreached in v7: its only call site in render_module() is commented out
		if(empty($this->revapi)) return '';
		$html_id = esc_attr($this->get_html_id());
		if(empty($html_id)) return '';
		$settings = $this->get_custom_settings();
		return (empty($settings) || !is_array($settings)) ? '' : RS_T4.'<script>'."\n". RS_T5.'document.addEventListener("sr.module.ready", function (e, id) {'."\n".RS_T6.'if(e.id !== "'.$html_id.'") return;'."\n".RS_T6.$this->revapi.'.settings('.json_encode($settings).');'."\n". RS_T5.'});'."\n". RS_T4.'</script>'."\n";
	}

	/**
	 * get the start size
	 * @return string
	 **/
	public function js_get_start_size(){
		return (!empty($this->modal)) ? '' : RS_T4.self::script_open()."\n". RS_T5.$this->get_html_js_start_size()."\n". RS_T4.'</script>'."\n";
	}

	/**
	 * set the start size of the slider through javascript
	 * @return string
	 **/
	public function get_html_js_start_size(){
		$len = false;
		$sbt = false;
		$onh = 0;
		$onw = 0;
		$cpt = 0;
		$cpb = 0;

		$slides		= $this->get_current_slides();
		$csizes		= $this->get_responsive_size_v7($this);
		// Convert CSV strings like "#a,900,#a,#a,#a" into typed arrays for safe JSON output
		$to_js_array = function($csv){
			if ($csv === false) return false;
			$parts = array_map('trim', explode(',', (string)$csv));
			$out = [];
			foreach ($parts as $p) {
				if ($p === '')                 { $out[] = '#a'; continue; }
				if ($p === '#a' || $p === "'#a'" || $p === '"#a"') { $out[] = '#a'; continue; }
				if (strcasecmp($p, 'auto') === 0){ $out[] = 'auto'; continue; }
				if (is_numeric($p))             { $out[] = (int)$p; continue; }
				$out[] = $p;
			}
			return $out;
		};
		$ghArr = $to_js_array($csizes['height']);
		$gwArr = $to_js_array($csizes['width']);
		$elArr = ($csizes['cacheSize'] !== false) ? $to_js_array($csizes['cacheSize']) : false;
		$mtype		= $this->slider->get_param(['type'], 'standard');
		//one entry per level for a module with its own levels, as gh/gw: printed as one quoted string, esc_attr turned the
		//separators into &#039; and page.js read the first value on every level. An empty or inherited level becomes the
		//'#' fillArray inherits, and a value keeps only what a length can hold
		$vport		= ($this->adv_resp_sizes == true) ? (array)$this->slider->get_param(['vPort'], '100px') : [$this->get_biggest_device_setting_v7($this->slider->get_param(['vPort'], '100px'), $this->enabled_sizes, '100px')];
		$vport		= array_map(function($v){ $v = preg_replace('/[^0-9a-z.%#\-]/i', '', (string)$v); return ($v === '' || $v === '#a') ? '#' : $v; }, $vport);
		if($mtype == 'carousel'){
			$cpt = intval($this->slider->get_param(['carousel', 'pT'], '0'));
			$cpb = intval($this->slider->get_param(['carousel', 'pB'], '0'));
		}
		$plType		= $this->slider->get_param(['pLoader', 'type'], 'off');
		$plColor	= ($plType !== 'off') ? $this->slider->get_param(['pLoader', 'color'], '#FFFFFF') : false;
		$shdw 		= $this->slider->get_param(['shdw'], false);

		//outer thumbs and tabs, reserved as SR7.F.getONavs() does at runtime: io defaults to inner, d may be a plain
		//string and anything not vertical is horizontal, two on one side take the larger size. `io ?? '' == 'o'` read
		//as `io ?? false`, so an inner nav that stored its io reserved its size too
		$elements	= ['thumbs', 'tabs'];
		foreach($elements as $element){
			$navElementParams = $this->slider->get_param(['nav', $element], []);
			if(!($navElementParams['set'] ?? false) || ($navElementParams['io'] ?? 'i') === 'i') continue;
			$direction = $navElementParams['d'] ?? 'horizontal';
			$vertical = (is_array($direction) ? ($direction['1'] ?? '') : $direction) === 'vertical';
			$tempSize = $this->slider->get_param(['nav', $element, 'size', $vertical ? 'w' : 'h', '1'], 0);
			$tempPadding = $this->slider->get_param(['nav', $element, 'wr', 'p', '1'], 0);

			if($vertical){
				$onw = max($onw, intval($tempSize) + intval($tempPadding) * 2);
			}else{
				$onh = max($onh, intval($tempSize) + intval($tempPadding) * 2);
			}
		}
		
		//get_current_slides() carries the static slide appended at the end. It is shown for the module's whole life
		//and owns no segment of the story (mirrors the global-slide skip in SR7.F.sbt.story.map), so it must not
		//count towards the scroll distance either.
		$static		= $this->slider->get_static_slide();
		$static_id	= (!empty($static) && is_object($static)) ? $static->get_id() : false;
		$story_slides = [];
		foreach($slides ?? [] as $slide){
			if($static_id !== false && $slide->get_id() === $static_id) continue;
			$story_slides[] = $slide;
		}
		//A story is a module TYPE, not a combination of settings. It used to be a mode plus "not a carousel" plus a
		//slide count, rebuilt here and in three more places; the type says it once. A single slide is still no
		//story, and that is the only part the type cannot carry.
		$sbt_story	= ($mtype === 'story' && count($story_slides) > 1);
		if($this->slider->get_param(['sbt', 'use'], false) === true && ($this->slider->get_param(['sbt', 'f'], false) === true || $sbt_story)){
			$sbt = true;
			$mlen = $this->slider->get_param(['default', 'len'], 'default');
			if(!is_numeric($mlen) || intval($mlen) <= 0) $mlen = 9000; //stored as text, may carry 'default', '' or units
			//A story spends the sum of ALL slide durations on scroll distance, so the pre-JS height has to be the sum
			//too — otherwise the module resizes under the visitor the moment the engine works the real one out.
			$len = 0;
			foreach($story_slides as $slide){
				$slen = $slide->get_param(['slideshow', 'len'], 'default');
				$len += ($slen === 'default') ? $mlen : intval($slen);
				if(!$sbt_story) break;
			}
			if($len <= 0) $len = $mlen;
		}
		$sticky = $this->slider->get_param('sticky', '');
		if($sticky !== 'top' && $sticky !== 'bottom'){
			$sticky = ($this->slider->get_param('fixed', false) === true) ? 'top' : 'none';
		}
		$fixed		= ($sticky === 'top') ? true : false;
		$slider_type= $this->slider->get_param('type', 'standard');
		$fullwidth 	= ($this->slider->get_param(['size', 'fullWidth'], true) === true) ? true : false;
		$fullheight	= ($this->slider->get_param(['size', 'fullHeight']) === true) ? true : false;
		$useFHO		= ($this->_truefalse($this->slider->get_param('size', 'FFOU', true)) !== false) ? true : false;
		$useMPO		= ($this->_truefalse($this->slider->get_param('size', 'MPOU', true)) !== false) ? true : false;
		$useMSO		= ($this->_truefalse($this->slider->get_param('size', 'MSOU', true)) !== false) ? true : false;
		$useMMO		= ($this->_truefalse($this->slider->get_param('size', 'MMOU', true)) !== false) ? true : false;
		$full_height_container	= $this->slider->get_param(['size', 'fullHeightOffset'], '');
		$bgcolor	= $this->slider->get_param(['bg', 'color']);
		$usebgimage = ($this->slider->get_param(['bg', 'image','src'],'') !== '') && ($this->slider->get_param(['bg', 'image','u'], true) === true) ? true : false;
		
		if($usebgimage){
			$bgimage = $this->slider->get_param(['bg', 'image', 'src']);
			$bgpos = $this->slider->get_param(['bg', 'image', 'pos','x'],'center') . ' ' . $this->slider->get_param(['bg', 'image', 'pos','y'],'center');
			$bgrep = $this->slider->get_param(['bg', 'image', 'repeat']);
			$bgrepx = $this->slider->get_param(['bg', 'image', 'rx']);
			$bgrepy = $this->slider->get_param(['bg', 'image', 'ry']);
			$bgfit = $this->slider->get_param(['bg', 'image', 'size']);
		}

		$minH		= $this->slider->get_param(['size', 'minHeight'], 0);

		if (!is_null($this->get_fullwidth())) $fullwidth = $this->get_fullwidth();
		if (!is_null($this->get_fullheight())) $fullheight = $this->get_fullheight();

		$html = '';
		if($elArr !== false){
			$allNeutral = true; // only zeros/#a/auto? then skip
			foreach($elArr as $v){ if($v !== 0 && $v !== 'auto' && $v !== '#a'){ $allNeutral = false; break; } }
			if(!$allNeutral) $html .= 'el:'.json_encode($elArr, JSON_UNESCAPED_SLASHES).',';
		}
		if($useMPO){
			if($sticky !== 'none') $html .= "sticky:'".esc_attr($sticky)."',";
			$html .= ($fixed !== false) ? "fixed:true," : '';
			if($this->_truefalse($this->slider->get_param(['size', 'stickyReserve'], false))) $html .= "stickyReserve:true,";
		}

		$html .= "type:'".esc_attr($mtype)."',";
		if($this->_truefalse($shdw) !== false) $html .= "shdw:'".esc_attr($shdw)."',";
		$html .= ($len !== false) ? "slideduration:'".esc_attr($len)."'," : '';
		
		$html .= 'gh:'.json_encode($ghArr, JSON_UNESCAPED_SLASHES).',';
		$html .= 'gw:'.json_encode($gwArr, JSON_UNESCAPED_SLASHES).',';
		$html .= 'vpt:'.json_encode($vport).',';
		$html .= "size:{fullWidth:".($fullwidth ? "true" : "false").", fullHeight:".($fullheight ? "true" : "false")."},";
		
		if($slider_type !== 'hero'){
			$check = ['tab' => 'tabs', 'thumb' => 'thumbs'];
			$wpd = ['tabs' => 2, 'thumbs' => 10];
			foreach($check as $nk => $nav){
				$nav_set		= $this->slider->get_param(['nav', $nav, 'set'], false);
				$nav_io			= $this->slider->get_param(['nav', $nav, 'io'], 'inner');
				$nav_widht_min	= json_encode($this->slider->get_param(['nav', $nav, 'size', 'w'], ['#a', 100, '#a', '#a', '#a']));
				$nav_padding	= json_encode($this->slider->get_param(['nav', $nav, 'wr', 'p'], ['#a', $wpd[$nav], '#a', '#a', '#a']));
				$nav_height		= json_encode($this->slider->get_param(['nav', $nav, 'size', 'h'], ['#a', 50, '#a', '#a', '#a']));
				
				if($nav_set !== true) continue;
				$do = false;
				if($nav_io === 'outer-vertical'){
					$html .= $nk.'w:"'.esc_attr($nav_widht_min).'",';
					$do = true;
				}elseif($nav_io === 'outer-horizontal'){
					$nav_height = ($nav_padding > 0) ? $nav_height + $nav_padding * 2 : $nav_height;
					$html .= $nk.'h:"'.esc_attr($nav_height).'",';
					$do = true;
				}
				
				if($do === false) continue;

				$nav_hul = json_encode($this->slider->get_param(['nav', $nav, 'show'], 0));

				$html .= $nk.'hide:"'.$nav_hul.'",';
			}
		}

		$fullscreen = ($fullwidth === true && $fullheight === true) ? true : false;
		
		if($useMSO){
			$offset = $this->translate_shortcode_offset();
			if(!empty($offset)) $html .= "off:".json_encode($offset).",";
		}

		if($fullscreen === true && $useFHO === true){
			$html .= "fho:'". esc_attr($full_height_container) ."',";
		}

		$mheight = 0;
		if($useMMO == true){
			$mheight = ($fullscreen === false) ? $minH : $this->slider->get_param(['size', 'minHeight'], '0');
			$mheight = ($mheight == '' || $mheight == 'none') ? 0 : $mheight;
			if(is_array($mheight)) $mheight = implode(',', $mheight);
		}
				
		$html .= "mh:'".esc_attr($mheight)."',";
		
		//sbt.use travels on for a story too: defaults.js only builds the sbt bag when it is set, and the engine's
		//scroll listener lives in there. The MODE is gone — the type in the same config already carries it.
		if($sbt) $html .= "sbt:{use:true},";
		
		if(!is_string($bgcolor)) $bgcolor = json_encode($bgcolor, JSON_HEX_APOS); else $bgcolor = esc_attr($bgcolor);
		$html .= "onh:".esc_attr($onh).",";
		$html .= "onw:".esc_attr($onw).",";
		$html .= "bg:{color:'".$bgcolor."'";
			
		if($usebgimage){
			if(!is_string($bgimage)) $bgimage = json_encode($bgimage, JSON_HEX_APOS); else $bgimage = esc_attr($bgimage);
			$html .= ",image:{src:'".$bgimage."'";
			$html .= ",size:'".esc_attr($bgfit)."'";
			$html .= ",position:'".esc_attr($bgpos)."'";
			$html .= ",repeat:'".esc_attr($bgrep)."'";
			$html .= ",rx:'".esc_attr($bgrepx)."'";
			$html .= ",ry:'".esc_attr($bgrepy)."'";
			$html .= "}";
		}
		$html .= "}";
		if(($this->get_val($this->global_settings, ['fonts', 'download'], 'off')) == 'disable') $html .=',googleFont:"ignore"';
		if($mtype == 'carousel') $html .= ",carousel:{pt:'".esc_attr($cpt)."',pb:'".esc_attr($cpb)."'}";
		if($plType !== 'off'){
			$html .= ",plType:'".esc_attr($plType)."',";
			$html .= "plColor:'".esc_attr($plColor)."'";
		}
		$html_id = esc_attr($this->get_html_id());
		$ret = 'window.SR7??={}; '. 
			'SR7.PMH ??={}; '.
			'SR7.PMH["'. $html_id .'"] = {' .
			'cn:100,'.
			'state:false,'.
			'fn: function() {'.
				' if (window._tpt!==undefined && window._tpt.prepareModuleHeight !== undefined) {'.
				'  _tpt.prepareModuleHeight({id:"'. $html_id .'",'. $html .'});'.
				'   SR7.PMH["'. $html_id .'"].state=true;'.
				'} else if(SR7.PMH["'. $html_id .'"].cn-->0)'.
				'	setTimeout( SR7.PMH["'. $html_id .'"].fn,19);'.
			'}'.
			'};'.
				'SR7.PMH["'. $html_id .'" ].fn();';
		
		ob_start();
		try{
			do_action('revslider_fe_javascript_output', $this->slider, $html_id);
			$js_action = ob_get_contents();
		}finally{
			ob_end_clean(); //close the buffer even if a hooked callback throws
		}
		$ret .= (!empty($js_action)) ? $js_action : '';

		return $ret;
	}

	/**
	 * get the HTML layer
	 * @return string
	 **/
	public function get_html_layer(){
		$layer = $this->get_layer();
		$html = '';
		$type = $this->get_val($layer, 'type', 'text');
		$text = $this->get_val($layer, ['content', 'text']);
		
		switch($type){
			case 'shape':
				//Custom Shape: output the pre-baked parametric SVG stored in the layer
				if($this->get_val($layer, 'subtype') === 'custom'){
					$html = (string)$this->get_val($layer, ['shape', 'svg'], '');
					//stored markup, so it goes out only through the SVG allow list (no script, handler or script url)
					//its output is indented, and the nl2br below would put a <br /> between the tags
					if($html !== '') $html = preg_replace('/>\s+</', '><', (string)(new SvgSanitizer())->sanitize($html));
				}
			break;
			case 'svg':
			case 'column':
			case 'image':
			break;
			case 'video':
				//for v6 fetching of rs_revicons
				if(empty($this->slider_7)){
					if(
						in_array(trim($this->get_val($layer, ['media', 'mediaType'])), ['streaminstagram', 'streaminstagramboth', 'html5'], true)
						&& $this->get_val($layer, ['media', 'largeControls'], true) === true
					){
						$g = &$this->context()->root();
						if(!isset($g['icon_sets']['RevIcon'])) $g['icon_sets']['RevIcon'] = ['css' => false, 'parsed' => false];
						$g['icon_sets']['RevIcon']['css'] = true;
					}
				}
			break;
			default:
			case 'text':
			case 'button':
				// this filter is needed for the weather Addon
				$html = apply_filters('revslider_modify_layer_text', $text, $layer);
	
				$check_icons = [
					$html,
					$this->get_val($layer, ['toggle', 'text']) //this part is needed for v6 data, to push icon sets //also check toggle data for v6
				];

				$g = &$this->context()->root();
				$cache = null; //resolved lazily on first icon match, then reused for the rest of this layer
				foreach($this->icon_sets as $font_handle => $is){
					if($is === '' || $is === null) continue; //an empty needle would make strpos() match every layer
					foreach($check_icons as $_html){
						if($_html === '' || strpos($_html, $is) === false) continue;

						//include default Icon Sets if used
						if(!isset($g['icon_sets'][$font_handle])) $g['icon_sets'][$font_handle] = ['css' => false, 'parsed' => false];
						$g['icon_sets'][$font_handle]['css'] = true;

						if($cache === null) $cache = Registry::instance()->get('RevSliderCache');
						$cache->add_addition('special', 'font_var', $font_handle);
					}
				}
			break;
		}
		
		$ws = ($this->adv_resp_sizes == true) ? implode(',', (array)$this->get_val($layer, 'ws', 'full')) : $this->get_biggest_device_setting_v7($this->get_val($layer, 'ws', 'full'), $this->enabled_sizes, 'full');
				
		//replace new lines with <br />
		$html = (strpos($ws, 'content') !== false || strpos($ws, 'full') !== false) ? nl2br($html) : $html;
		//do shortcodes here, so that nl2br is not done within the shortcode content
		
		if($this->has_any_shortcode($html)){
			$this->layer['has_shortcode'] = true;
			$this->_layer_filtered_valid = false;
		}

		return (!in_array($type, ['image', 'svg', 'column', 'shape'], true)) ? $this->resolve_basic_metas($this->environment()->expand_content(stripslashes($html))) : $html;
	}

	/**
	 * get the layer ids as HTML
	 * @return string
	 **/
	public function get_html_title(){
		$layer = $this->get_layer();
		$title	= $this->get_val($layer, ['attr', 'title']);
		
		return ($title != '') ? 'title="'.esc_attr($title).'"' : '';
	}

	/**
	 * get the HTML rel
	 * @return string
	 **/
	public function get_html_rel(){
		$layer	= $this->get_layer();
		$rel	= $this->get_val($layer, ['attr', 'rel']);
		
		return ($rel != '') ? 'rel="'.esc_attr($rel).'"' : '';
	}

	/**
	 * get the Slides HTML of the Slider
	 * @return void
	 **/
	public function get_slides(){
		$type	= $this->slider->get_param('type', 'standard');
		$hero	= ($type === 'hero') ? true : false;
		$order	= $this->custom_order;
		//fetch all slides connected to the Slider (no static slide). An order lists its slides itself, published or not
		$slides = $this->slider->get_slides(empty($order), false, $hero && empty($order));
		if(!empty($order)) $slides = $this->order_slides($slides, $order);
		
		/**
		 * if we are now at 0 slides, there will be no more chances to add them
		 * so return back with no slides markup
		 **/
		if(empty($slides)) throw new \Exception('No active slides found');

		/**
		 * if we are a stream
		 * duplicate slides as templates to the corresponding stream amount
		 * check if post, if the post has a specific slide template set
		 **/

		/**
		 * stream feed =  JSON
		 * - it should gather the feeds, prepare slides and push in the SR7.M[sliderid].stream
		 *
		 * stream feed =  REST
		 * - we gather feed, and print slides, layers and images that are SEO relevant
		 */
		$is_stream = $this->slider->is_stream_post();
		if($is_stream) $slides = $this->multiply_slides($slides);

		$static_slide = $this->slider->get_static_slide();

		if(!empty($static_slide)){
			$slides[] = $static_slide;
			$this->set_static_slide($static_slide);
		}

		$this->set_general_params_for_layers();

		$this->set_current_slides($slides);

		$preview_mode = $this->get_preview_mode(); //constant for the whole render - hoisted out of the per-slide loop
		$first = true;
		foreach($slides ?? [] as $slide){
			$this->set_slide($slide);
			if($preview_mode === false && !$slide->check_use_slide()) continue;
			$this->add_slide_li_pre();
			if($first === true) $this->set_lcp_still($slide);
			$this->add_slide_background_image();
			$this->set_slide_params_for_layers();
			$this->add_creative_layer();
			$this->add_slide_li_post();
			$first = false;
		}
	}

	/**
	 * The slides the shortcode's order attribute lists, in that order and only those. Version 6 had this,
	 * version 7 read the attribute and never acted on it. A listed slide shows even when unpublished, as before,
	 * and a slide can be named by its id or by its DeepLink ID.
	 * @since 7.2.0
	 * @param RevSliderSlide[] $slides
	 * @param array $order
	 * @return RevSliderSlide[]
	 */
	public function order_slides($slides, $order){
		$by_key = [];
		foreach($slides as $slide){
			$by_key[(string)$slide->get_id()] = $slide;
			$link = (string)$slide->get_param(['attr', 'deepLink'], '');
			if($link !== '') $by_key[$link] = $slide;
		}

		$ordered = [];
		foreach($order as $key){
			$key = (string)trim($key);
			if(!isset($by_key[$key]) || in_array($by_key[$key], $ordered, true)) continue;
			$by_key[$key]->set_param(['publish', 'state'], 'published');
			$ordered[] = $by_key[$key];
		}

		return apply_filters('revslider_order_slides', $ordered, $slides, $order, $this);
	}

	/**
	 * add the slide li with data attributes and so on
	 * @return void
	 **/
	public function add_slide_li_pre(){
		echo RS_T6.'<sr7-slide';
		echo $this->get_html_slide_class();
		echo $this->get_html_slide_id();
		echo $this->get_html_slide_key();
		echo $this->get_html_slide_attrs();
		echo '>'."\n";
	}

	/**
	 * add the slide closing li 
	 * @return void
	 **/
	public function add_slide_li_post(){
		echo RS_T6.'</sr7-slide>'."\n";
	}

	/** @return string */
	public function get_html_slide_class(){
		$class = $this->get_slide()->get_param(['attr', 'class']);
		
		return (!empty(trim($class))) ? ' class="'.esc_attr($class).'"' : '';
	}

	/** @return string */
	public function get_html_slide_id(){
		$id = $this->get_slide()->get_param(['attr', 'id']);
		$id = (empty($id)) ? $this->get_html_id().'-'.$this->get_slide()->get_id() : $id;

		return ' id="'.esc_attr($id).'"';
	}

	/** @return string */
	public function get_html_slide_key(){
		return ' data-key="'.esc_attr($this->get_slide()->get_id()).'"';
	}

	/**
	 * the slide's DeepLink ID as data-deeplink and the name="value" pairs of its HTML Data (data-* only, as SR6
	 * printed them) and Attribute fields. Never an event handler or a name the slide carries already; the front's
	 * twin is SR7.F.slideAttrs (slide.js), for the slides the engine builds itself
	 * @since 7.2.7
	 * @return string
	 */
	public function get_html_slide_attrs(){
		$slide	= $this->get_slide();
		$link	= trim((string)$slide->get_param(['attr', 'deepLink'], ''));
		$list	= ($link !== '') ? ['data-deeplink' => $link] : [];
		foreach(['data' => '/^data-/', 'attr' => '/^/'] as $field => $allow){
			preg_match_all('/(?:^|\s)([a-z_:][-\w:.]*)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'>]+))/i', (string)$slide->get_param(['attr', $field], ''), $pairs, PREG_SET_ORDER);
			foreach($pairs as $p){
				$name = strtolower($p[1]);
				if(!preg_match($allow, $name) || isset($list[$name]) || preg_match('/^(on|(id|class|style|data-key)$)/', $name)) continue;
				$list[$name] = ($p[2] ?? '').($p[3] ?? '').($p[4] ?? '');
			}
		}

		$html = '';
		foreach($list as $name => $value) $html .= ' '.$name.'="'.esc_attr($value).'"';
		return $html;
	}

	/**
	 * measurement mode: the first slide's picture, drawn from the markup. It sits in the module itself, because
	 * both the slide and the content box are sized by the engine and have no width before that, so nothing in
	 * them can paint. In the module it has a box from the first paint on, and no late pop when the engine sizes
	 * @since 7.2.0
	 * @return string
	 */
	public function get_lcp_still(){
		global $SR_GLOBALS;
		if(empty($this->lcp_still) || $SR_GLOBALS['lcp_still'] === true) return '';

		//the module is sized by a script, so without a height of its own the picture has nothing to paint into.
		//These are the module's own start heights, per device, and the engine clears them the moment it takes over
		$sizes		= $this->get_responsive_size_v7($this);
		$heights	= explode(',', (string)$this->get_val($sizes, 'height', ''));
		$widths		= explode(',', (string)$this->get_val($sizes, 'width', ''));
		$points		= $this->get_val($this->global_settings, 'breakPoints', [1920, 1240, 1024, 778, 480]);
		$full		= ($this->_truefalse($this->slider->get_param(['size', 'fullWidth'], true)) === true); //same default as the start size script
		$html_id	= esc_attr($this->get_html_id());
		$css		= '';

		foreach($heights as $level => $height){
			$height = intval($height);
			$width	= intval($this->get_val($widths, $level, 0));
			if($height < 1) continue;

			//a full width module scales its design height down with the window, exactly as updatePMHeight() does,
			//so the box the picture paints into is the one the engine settles on and nothing jumps at the handover
			$size	= ($full === true && $width > 0) ? 'min('.$height.'px, calc(100vw * '.$height.' / '.$width.'))' : $height.'px';
			$rule	= '#'.$html_id.'{min-height:'.$size.'}';
			$css   .= ($level === 0) ? $rule : '@media (max-width:'.intval($this->get_val($points, $level, 0)).'px){'.$rule.'}';
		}

		$r  = ($css !== '') ? RS_T5.'<style>'.$css.'</style>'."\n" : '';
		$r .= RS_T5.'<img class="sr7-lcpimg" fetchpriority="high" decoding="async" src="'.esc_attr($this->lcp_still['src']).'" alt="'.esc_attr(strip_tags($this->lcp_still['alt'])).'" title="'.esc_attr(strip_tags($this->lcp_still['title'])).'">'."\n";

		$this->lcp_still			= [];
		$SR_GLOBALS['lcp_still']	= true; //one per page

		return $r;
	}

	/**
	 * measurement mode: remember the picture the first slide opens with, for get_lcp_still() to draw. That is
	 * the slide background, and where a slide has none, the one picture layer that covers the whole stage
	 * @since 7.2.0
	 * @return void
	 */
	public function set_lcp_still($slide){
		global $SR_GLOBALS;

		if($SR_GLOBALS['lcp_still'] === true || $SR_GLOBALS['serial'] > 3) return;
		if($this->_truefalse($this->get_val($this->global_settings, ['opt', 'lcp'], false)) !== true) return;

		$devices	= ['ld' => true, 'd' => true, 'n' => true, 't' => true, 'm' => true];
		$layer		= $slide->get_bg_layer();
		$img		= (!empty($layer)) ? $this->get_biggest_device_setting_v7($this->get_val($layer, ['bg', 'image', 'src']), $devices, '') : '';

		//a background video cannot paint before its script runs, its poster can
		if((!is_string($img) || $img === '') && !empty($layer)){
			$img = $this->get_biggest_device_setting_v7($this->get_val($layer, ['bg', 'video', 'poster', 'src']), $devices, '');
		}

		if(!is_string($img) || $img === ''){
			foreach($slide->get_layers(true) ?? [] as $cover){
				if($this->get_val($cover, ['bg', 'type']) !== 'image') continue;
				if(intval($this->get_val($cover, 'pid', -1)) !== -1) continue; //only a layer that stands on the slide itself
				if($this->get_biggest_device_setting_v7($this->get_val($cover, ['size', 'w']), $devices, '') !== '100%') continue;
				if($this->get_biggest_device_setting_v7($this->get_val($cover, ['size', 'h']), $devices, '') !== '100%') continue;

				$src = $this->get_biggest_device_setting_v7($this->get_val($cover, ['bg', 'image', 'src']), $devices, '');
				if(!is_string($src) || $src === '') continue;

				$layer	= $cover;
				$img	= $src;
				break;
			}
		}

		if(!is_string($img) || $img === '') return;

		$attr			 = $this->get_media_alt_title($layer, $img);
		$this->lcp_still = ['src' => $img, 'alt' => $this->get_val($attr, 'alt', ''), 'title' => $this->get_val($attr, 'title', '')];
	}

	/** @return void */
	public function add_slide_background_image(){
		$slide	= $this->get_slide();
		
		$layer = $slide->get_bg_layer();
		$type	= '';
		
		if(!empty($layer)){
			$type			= 'image';
			//per device the src is [ld, d, n, t, m] with '#a' for inherit; the preload and the alt lookup want the one picture the biggest device shows
			$img			= $this->get_biggest_device_setting_v7($this->get_val($layer, ['bg', 'image', 'src']), ['ld' => true, 'd' => true, 'n' => true, 't' => true, 'm' => true], '');
			if(empty($img) || !is_string($img)) return;

			$attr			= $this->get_media_alt_title($layer, $img);

			$this->set_layer($layer);
			$this->set_layer_unique_id();	
			$this->set_slide_id($slide->get_id());
			$ids			= $this->get_html_layer_ids();
		}
	
		if($type !== 'image') return;
		
		$alt	= $attr['alt'];
		$title	= $attr['title'];

		$img = apply_filters('sr_add_slide_background_image_url', $img, $slide, $this);

		echo $this->ld().RS_T7.'<sr7-bg '.$ids.' class="sr7-layer"><noscript>';
		echo '<img src="'.esc_attr($img).'" alt="'.esc_attr(strip_tags($alt)).'" title="'.esc_attr(strip_tags($title)).'">';
		echo '</noscript></sr7-bg>'."\n";
	}

	/**
	 * put creative layer
	 * @return false|void false when the slide has no layers
	 */
	private function add_creative_layer(){
		$layers = $this->get_layers();
		if(empty($layers)) return false;
		
		$this->container_mode = '';
		foreach($layers as $layer){
			if(!in_array($this->get_val($layer, 'type', 'text'), ['text', 'button'], true)) continue; //only do text layer, if we are v6 data, then allow buttons also
			if(!in_array($this->get_val($layer, 'subtype', false), ['button', false, ""], true)) continue; //only do text layer
			
			$this->set_layer($layer);
			$this->add_layer(false);
		}
	}

	/**
	 * Adds a Layer to the stage
	 * Moved most code part from putCreativeLayer into putLayer
	 * @since: 5.3.0
	 * @return string
	 */
	public function add_layer($row_group_uid = false, $special_type = false){
		$layer = apply_filters('revslider_putLayer_pre', $this->get_layer(), $this, $row_group_uid, '', $special_type);
		$this->set_layer($layer);
		$this->set_layer_unique_id();
		
		/**
		 * top middle and bottom are placeholder layers, do not write them
		  **/
		if(in_array($this->get_layer_unique_id(), ['top', 'middle', 'bottom'], true)) return '';
		
		$slider_id			= $this->slider->get_id();
		$class				= '';
		$html_simple_link	= trim($this->get_action_link_v7());
		$ids				= $this->get_html_layer_ids();
		$layer_tag			= $this->get_layer_tag($html_simple_link, $special_type);
		$html_class			= $this->get_html_class($class, $layer_tag);
		$html_title			= $this->get_html_title();
		$html_rel			= $this->get_html_rel();
		$html_layer			= $this->get_html_layer();
		$layertype 			= $this->get_layer_type();
		$layerattr			= $this->get_layer_attr();
		echo $this->ld().RS_T7.'<'.$layer_tag;
		echo ($ids != '')				? ' '.$ids : '';
		echo ($html_class !== '')		? ' '.$html_class : '';
		echo ($html_simple_link !== '')	? ' '.$html_simple_link : '';
		echo ($html_rel !== '')			? ' '.$html_rel : '';
		echo ($html_title !== '')		? ' '.$html_title : '';
		echo ($layerattr !== '')		? ' '.$layerattr : '';
		echo '>';//."\n";
		$content = ($special_type === false && $layertype !== 'video') ? apply_filters('revslider_layer_content', $html_layer, $html_layer, $slider_id, $this->slide, $layer) : '';
		//an <a> inside a link layer makes the parser close the layer and put it beside it, top left in the slide ({{wc_add_to_cart_button}})
		if($layer_tag === 'a' && stripos($content, '<a') !== false) $content = preg_replace(['/<a(?=[\s>])/i', '/<\/a\s*>/i'], ['<span', '</span>'], $content);
		echo $content;
		if($special_type === false){
			echo '</'.$layer_tag.'>'."\n";
		} //the closing will be written later, after all layers/columns are added
		
		$this->zIndex++;
	}
	
	/** @return array */
	public function get_custom_transitions(){
		$found	= [];
		$slides	= $this->get_current_slides();
		
		if(empty($slides)) return $found;

		$base_transitions = $this->get_base_transitions();

		$_transitions = [];
		if($this->slider->get_param(['fs', 'as'], false) === true && $this->slider->get_param('type') !== 'hero'){
			if($this->slider->get_param(['fs', 'a', 'rnd'], false) === true) $_transitions['rndany'] = 'rndany';
		}

		foreach($slides ?? [] as $slide){
			$layers = $slide->get_layers();
			if(empty($layers)) continue;
		
			foreach($layers as $layer){
				if($this->get_val($layer, 'subtype') !== 'slidebg') continue;
				if($this->get_val($layer, ['tl', 'in', 'bg', 'all', '1', 'rnd'], false) === false) continue;
				$rnd_trans = $this->get_val($layer, ['tl', 'in', 'bg', 'all', '1', 'rndmain'], 'all');
				$rnd_trans = ($rnd_trans === 'all') ? 'rndany' : $rnd_trans;

				$_transitions[$rnd_trans] = $rnd_trans;
			}
		}

		if(empty($_transitions)) return $found;

		foreach($_transitions as $transition){
			if(strpos($transition, 'rnd') !== false) continue;

			foreach($base_transitions as $name => $keys){
				if(!is_array($keys) || empty($keys)) continue;

				foreach($keys as $k => $t){
					if(!is_array($t) || empty($t)) continue;
					
					if(!isset($t[$transition])) continue;

					if(!isset($found[$name])){
						$found[$name] = [];
						foreach($base_transitions[$name] as $key => $value){ //add strings like icon, noSubLevel
							if(is_array($value)) continue;

							$found[$name][$key] = $value;
						}
					}
					$found[$name][$transition] = $t[$transition];
					
					break;
				}
			}
		}
		foreach($_transitions as $transition){
			if(strpos($transition, 'rnd') === false) continue;
		
			$cat = array_keys($base_transitions);
			if(isset($cat[array_search('random', $cat)])) unset($cat[array_search('random', $cat)]);

			//push 10 random transitions from $base_transitions! make sure not to use the 'random' branch
			$max = 0;
			for($i = 0; $i < 10; $i++){
				$use_cat = $cat[array_rand($cat)];
				$sub_cat = array_keys($base_transitions[$use_cat]);

				//remove strings from the list to use
				foreach($base_transitions[$use_cat] as $check => $array){
					if(!is_array($array)) unset($sub_cat[array_search($check, $sub_cat)]);
				}
				
				$sub_index	= $sub_cat[array_rand($sub_cat)];
				$push_index = array_keys($base_transitions[$use_cat][$sub_index]);
				$push_key	= $push_index[array_rand($push_index)];
				
				if(isset($found[$use_cat]) && isset($found[$use_cat][$sub_index])){
					$i--; //already existing, get another one
				}else{
					if(!isset($found[$use_cat])) $found[$use_cat] = [];
					if(!isset($found[$use_cat][$sub_index])) $found[$use_cat][$sub_index] = [];
					$found[$use_cat][$sub_index][$push_key] = $base_transitions[$use_cat][$sub_index][$push_key];
				}
				$max++;
				if($max >= 100) $i = 10; //make sure to not fall into a loop here if all transitions are already added
			}

			break;
		}

		return $found;
	}

	/**
	 * Set variables that are later printed like in the add_js() function
	 * @return void
	 **/
	public function set_javascript_variables(){
		$g = &$this->context()->root();
		$trans	= $this->get_custom_transitions();

		foreach($trans ?? [] as $type => $_trans){
			if(empty($_trans)) continue;

			foreach($_trans as $key => $tran){
				if(isset($g['collections']['trans'][$type][$key])) continue;
				if(!isset($g['collections']['trans'][$type])) $g['collections']['trans'][$type] = [];

				$g['collections']['trans'][$type][$key] = $tran;
			}
		}
	}


	/**
	 * add JavaScript
	 * @return void
	 **/
	public function add_js(){
		$g = &$this->context()->root();

		if($g['js_init'] === true && $g['loaded_by_editor'] !== true) return;

		echo $this->get_footer_js();
	}

	/**
	 * the footer script block as a string: init calls, transition table, navigation and stream
	 * payloads. Marks the footer as emitted; the add_js() shell guards repeat emission.
	 * @since 7.2.1
	 * @return string
	 */
	public function get_footer_js(){
		$g = &$this->context()->root();
		ob_start();

		echo self::script_open()."\n"; // data-type="SR7-content"
		do_action('revslider_pre_add_js', $this);
		if($g['loaded_by_editor'] === true){
			echo RS_T.'SR7.E.elementorBackend  = true;'."\n";
		}
		
		if($this->get_val($this->global_settings, ['getTec', 'core'], 'MIX') !== 'REST' || $g['markup_export'] === true){
			//get all sliders that have been loaded and write their JSON
			echo $this->render_page()->load_v7_slider();
		}

		echo RS_T."if (SR7.F.init) SR7.F.init(); // DOUBLE CALL NOT A PROBLEM, MANAGED IN INIT"."\n";
		echo RS_T."document.addEventListener('DOMContentLoaded', function() {if (SR7.F.init) SR7.F.init(); else SR7.shouldBeInited = true;});"."\n";
		echo RS_T."window.addEventListener('load', function() {if (SR7.F.init) SR7.F.init(); else SR7.shouldBeInited = true; });"."\n";

		if(!empty($g['collections']['trans'])){
			echo RS_T.'SR7.E.transtable ??={};'."\n";
			echo RS_T.'SR7.E.transtable = JSON.parse('. $this->json_encode_client_side($g['collections']['trans'], JSON_INVALID_UTF8_IGNORE) .');'."\n";
		}
		if(!empty($g['collections']['nav'])){ //always true, the bootstrap seeds five empty lists
			foreach($g['collections']['nav'] as $nav){
				if(!empty($nav)){ //frozen, unreached in v7: only the removed v6 renderer wrote collections.nav
					echo RS_T.'SR7.NAV ??={};'."\n";
					echo RS_T.'SR7.NAV = JSON.parse('. $this->json_encode_client_side($g['collections']['nav'], JSON_INVALID_UTF8_IGNORE) .');'."\n";
					break;
				}
			}
		}
		if(!empty($g['collections']['js']) && !empty($g['collections']['js']['stream'])){
			echo RS_T.'SR7.M ??={};'."\n";
			foreach($g['collections']['js']['stream'] as $id => $stream){
				echo RS_T."SR7.M['".$id."'] ??={};"."\n";
				echo RS_T."SR7.M['".$id."'].stream ??={};"."\n";
				echo RS_T."SR7.M['".$id."'].stream = JSON.parse(". $this->json_encode_client_side($stream, JSON_INVALID_UTF8_IGNORE) .");"."\n";
			}
		}

		do_action('revslider_post_add_js', $this);
		echo '</script>'."\n";

		$g['js_init'] = true;

		return ob_get_clean();
	}

	/**
	 * translates the offset that can be added to the shortcodes in a JS format for the specific slider
	 * @return string
	 **/
	public function translate_shortcode_offset(){
		$offset = $this->offset;
		if(empty($offset)) return '';
		$pairs = explode(';', $offset);
		
		if(empty($pairs)) return '';
		
		$result = [];
		foreach($pairs as $pair){
			$parts	= explode(':', $pair);
			if(count($parts) !== 2) continue;

			$value	= $parts[1];
			$values = explode(',', $value);
			if(empty($values)) continue;
			
			if(count($values) < 5){
				while(count($values) < 5){
					array_unshift($values, $values[0]);
				}
			}
			$result[$parts[0]] = $values;
		}

		return $result;
	}

	/**
	 * if we are a stream, we use the slides as slide templates
	 * multiply them and add content depending on how mamy post/streams we have
	 * @return array
	 */
	public function multiply_slides($slides){
		$custom		= [];
		$templates	= $slides;
		$slides		= [];
		$templates	= $this->assoc_to_array($templates);
		$count		= count($templates);
		$key		= 0;
		$post_slider= $this->slider->is_posts();
		$stream_slider= $this->slider->is_stream();
		$slider_id	= $this->slider->get_id();

		foreach($this->stream_data ?? [] as $k => $post){
			if($post_slider === true || $stream_slider !== false){
				$slide = new Slide();
				$slide->init_by_post_data_v7($post, $templates[$key], $slider_id);
			}else{
				$slide	= clone $templates[$key];
			}
			$k	+= 1;
			$slide->set_id($slide->get_id().'STR'.$k);
			$key++;
			$slides[] = $slide;
			if($key == $count) $key = 0;
		}

		return $slides;
	}

	/**
	 * modify slider settings through the shortcode directly
	 * @return void
	 */
	private function modify_settings(){
		$settings = $this->get_custom_settings();
		$settings = apply_filters('revslider_modify_slider_settings', $settings, $this->get_slider_id());
		
		if(empty($settings) || !is_array($settings)) return;
		
		$params = $this->slider->get_params();
		foreach($settings as $handle => $setting){
			$params[$handle] = $setting;
		}
		
		if(!empty($this->slider_v7)){
			$this->slider_v7->set_params($params);
		}else{
			$this->slider->set_params($params);
		}
	}

	/**
	 * add error message into the console
	 * @return void
	 */
	public function print_error_message_console($message){

		$message = $this->slider->get_title().': '.$message;
		echo self::script_open();
		echo 'console.log("'.esc_html($message).'")';
		echo '</script>'."\n";
	}

	/**
	 * put inline error message in a box.
	 * @return void
	 */
	public function print_error_message($message, $open_page = false){
		$html_id = 'SR7_ERROR_'.$this->context()->get('serial');
		$data_id = 'ERROR';
		$html = "\n".RS_T3.'<sr7-module data-alias="Error-Module" data-id="'.$data_id.'" id="'.$html_id.'" class="" data-version="'.RS_REVISION.'">'."\n";
		$html .= RS_T4.'<sr7-adjuster></sr7-adjuster>'."\n";
		$html .= RS_T4.'<sr7-content>'."\n";
		$html .= RS_T5.'<sr7-slide id="'.$html_id.'-1" data-key="1">'."\n";
		$html .= RS_T6.'<sr7-txt id="'.$html_id.'-1-1" class="sr7-layer">'.esc_html($message).'</sr7-txt>'."\n";
		$html .= RS_T6.'<sr7-txt id="'.$html_id.'-1-2" class="sr7-layer">There is nothing to show here!</sr7-txt>'."\n";
		$html .= RS_T6.'<sr7-txt id="'.$html_id.'-1-3" class="sr7-layer"><i class="fa-warning"></i></sr7-txt>'."\n";
		$html .= RS_T5.'</sr7-slide>'."\n";		
		$html .= RS_T4.'</sr7-content>'."\n";
		$html .= RS_T3.'</sr7-module>'."\n";
		$html .= RS_T3.self::script_open()."\n";
		$html .= RS_T4.'window.SR7 ??= {};'."\n";
		$html .= RS_T4.'SR7.JSON ??= {};'."\n";
		$html .= RS_T4.'SR7.JSON["'.$html_id.'"] = {id:"'.$data_id.'", settings:{"title":"Error Module","alias":"Error Module","type":"hero", "size":{"width": [1240,1240,1024,778,480],"height": [600,600,600,600,480]}},"slides":{"1":{"slide":{"id":"1","version":"'.RS_REVISION.'","order":1},"layers":{"1":{"fluid":{"tx":true,"tr":true,"sp":true},"id":1,"alias":"Subtext","size":{"w":["850px","800px","600px","450px","450px"]}, "content":{"text":"'.esc_html($message).'"},"pos":{"y":["30px","30px","20px","16px","13px"],"h":["center","center","center","center","center"],"v":["middle","middle","middle","middle","middle"],"pos":"absolute"},"zIndex":6,"order":6,"display":["block","block","block","block","block"],"tl":{"in":{"content":{"all":[{"t":0,"d":0,"f":0,"e":"power3.inOut","pE":"d","sX":0.9,"sY":0.9,"o":0},{"t":370,"d":850,"f":850,"e":"power3.inOut","pE":"d","sX":1,"sY":1,"o":1}]}},"out":{"content":{"all":[{"t":0,"d":300,"f":300,"e":"power3.inOut","pE":"n","o":0}]}}},"tA":["center","center","center","center","center"],"color":["#000000","#000000","#000000","#000000","#000000"],"font":{"family":\'Arial, Helvetica, sans-serif\',"size":["#a",20,16,12,7],"weight":["200","200","200","200","200"],"ls":[0,0,0,0,0]},"lh":["#a","24px","20px","16px","7px"],"type":"text"},"2":{"fluid":{"tx":true,"tr":true,"sp":true},"id":2,"alias":"Title","content":{"text":"There is nothing to show here!"},"pos":{"y":["-20px","-20px","-16px","-12px","-7px"],"h":["center","center","center","center","center"],"v":["middle","middle","middle","middle","middle"],"pos":"absolute"},"zIndex":7,"order":7,"display":["block","block","block","block","block"],"tl":{"in":{"content":{"all":[{"t":0,"d":0,"f":0,"e":"power3.inOut","pE":"d","sX":0.9,"sY":0.9,"o":0},{"t":200,"d":1000,"f":1000,"e":"power3.inOut","pE":"d","sX":1,"sY":1,"o":1}]}},"out":{"content":{"all":[{"t":0,"d":300,"f":300,"e":"power3.inOut","pE":"n","o":0}]}}},"tA":["center","center","center","center","center"],"color":["#000000","#000000","#000000","#000000","#000000"],"font":{"family":"\'Arial, Helvetica, sans-serif\'","size":["30px","30px","24px","18px","11px"],"weight":["200","200","200","200","200"],"ls":[0,0,0,0,0]},"lh":["30px","30px","24px","18px","11px"],"type":"text"},"3":{"fluid":{"tx":true,"tr":true,"sp":true},"id":3,"alias":"Title","content":{"text":"<i class=\\"fa-warning\\"></i>"},"pos":{"y":["-70px","-70px","-57px","-43px","-26px"],"h":["center","center","center","center","center"],"v":["middle","middle","middle","middle","middle"],"pos":"absolute"},"zIndex":8,"order":8,"display":["block","block","block","block","block"],"tl":{"in":{"content":{"all":[{"t":0,"d":0,"f":0,"e":"power3.inOut","pE":"d","sX":0.9,"sY":0.9,"o":0},{"t":0,"d":1040,"f":1040,"e":"power3.inOut","pE":"d","sX":1,"sY":1,"o":1}]}},"out":{"content":{"all":[{"t":0,"d":300,"f":300,"e":"power3.inOut","pE":"n","o":0}]}}},"tA":["center","center","center","center","center"],"color":["#ff3a2d","#ff3a2d","#ff3a2d","#ff3a2d","#ff3a2d"],"font":{"family":"\'Arial, Helvetica, sans-serif\'","size":["50px","50px","41px","31px","19px"],"weight":[400,400,400,400,400],"ls":[0,0,0,0,0]},"lh":["50px","50px","41px","31px","19px"],"type":"text"},"32":{"rTo":"slide","id":32,"subtype":"slidebg","size":{"cMode":"cover"},"bg":{"color":"#ffffff"},"tl":{"in":{"bg":{"ms":1000,"rnd":false,"temp":{"t":"*opacity* Fade In","p":"fade","m":"basic","g":"fade"},"in":{"o":0},"out":{"a":false}}}},"type":"shape"}}}}}'."\n";
		$html .= RS_T4.'SR7.PMH ??={}; SR7.PMH["'. $html_id .'"] = {cn:0,state:false,fn: function() { if (_tpt!==undefined && _tpt.prepareModuleHeight !== undefined) {_tpt.prepareModuleHeight({id:"'. $html_id .'","size":{"width":[1240,1240,1024,778,480],"height":[900,900,768,960,720]}}); SR7.PMH["'. $html_id .'"].state=true;} else if((SR7.PMH["'. $html_id .'"].cn++)<100) setTimeout( SR7.PMH["'. $html_id .'"].fn,19);}}; SR7.PMH["'. $html_id .'" ].fn();';
		$html .= RS_T3.'</script>'."\n";

		add_action('wp_print_footer_scripts', [$this, 'add_js'], 100);
		
		echo $html;
	}

	/**
	 * check if the youtube api needs to be added, this should only be done once for all sliders
	 * @since: 6.5.7
	 * @return string
	 **/
	public function add_youtube_api_html(){
		$r = '';

		if($this->context()->get('yt_api_loaded', false) === true) return $r; //already loaded
		if($this->slider->get_param('hasYT', false) === false) return $r;

		//check global option if enabled
		$gs = $this->get_global_settings();
		if($this->_truefalse($this->get_val($gs, ['script', 'ytapi'], false)) === true){
			$r = RS_T.'<script src="https://www.youtube.com/iframe_api"></script>'."\n";
			$this->context()->set('yt_api_loaded', true);
		}
		
		return apply_filters('revslider_add_youtube_api_html', $r, $this);
	}

	/**
	 * set general values that are needed by layers
	 * this is needed to be called before any layer is added to the stage
	 * @return void
	 **/
	public function set_general_params_for_layers(){
		$this->enabled_sizes	= $this->slider->get_responsive_sizes();
		$this->adv_resp_sizes	= $this->enabled_sizes['ld'] == true || $this->enabled_sizes['n'] == true || $this->enabled_sizes['t'] == true || $this->enabled_sizes['m'] == true;
		$this->icon_sets		= $this->set_icon_sets(['material-icons']);
	}

	/** @return array */
	public function get_used_icons(){ //frozen, unreached in v7: no caller anywhere, kept as frozen public API
		$ctx = $this->context();
		$icon_sets = [];

		if($this->_truefalse($this->get_val($this->global_settings, ['fonts', 'awesome'], false)) === false){
			if($ctx->get(['icon_sets', 'FontAwesome', 'css'], false) === true)		$icon_sets['FontAwesome'] = true;
			if($ctx->get(['icon_sets', 'FontAwesomeIcon', 'css'], false) === true)	$icon_sets['FontAwesome'] = true;
		}
		if($ctx->get(['icon_sets', 'PeIcon', 'css'], false) === true)				$icon_sets['PeIcon'] = true;
		if($ctx->get(['icon_sets', 'Materialicons', 'css'], false) === true)		$icon_sets['Materialicons'] = true;
		if($ctx->get(['icon_sets', 'RevIcon', 'css'], false) === true)				$icon_sets['RevIcon'] = true;

		return $icon_sets;
	}

	/** @return bool */
	public function add_fonts_v7(){
		$g = &$this->context()->root();
		$gf		= $this->slider->get_param('fonts', []);
		if(empty($gf)) return true;
		
		$custom_fonts = $this->get_custom_font_list($this->global_settings);
		$keys = ['normal', 'italic'];
		
		foreach($gf as $font => $values){
			$branch = 'queue';
			if(in_array($font, ['Materialicons', 'PeIcon', 'FontAwesome', 'RevIcon'], true)){
				if(!isset($g['fonts'])) $g['fonts'] = ['queue' => [], 'loaded' => [], 'custom' => []];
				if($values === true) $g['fonts']['queue'][$font] = true;
			}
			if(!is_array($values)) continue;

			$variants	= ['normal' => [], 'italic' => []];
			$subsets	= ['normal' => [], 'italic' => []];
			foreach($keys as $key){
				if(!isset($values[$key]) || !is_array($values[$key])) continue;
				if(!isset($variants[$key])) $variants[$key] = [];
				
				foreach($values[$key] as $weight => $ign){
					$variants[$key][$weight] = $weight;
				}
			}

			if(empty($variants['normal']) && empty($variants['italic'])) $variants['normal'][400] = 400;

			if($this->get_val($values, 'custom', false) === true){
				//add url
				foreach($custom_fonts ?? [] as $cfont){
					if($this->get_val($cfont, 'family') !== str_replace(['"', "'"], '', $values['name'])) continue;
					//the module stored this url at edit time, so an empty one here is what keeps a font the
					//global list limits to the editor out of the frontend markup
					$values['url'] = ($this->custom_font_loads_in($cfont, 'live') === true) ? $this->get_val($cfont, 'url') : '';
					break;
				}
			}else{ //only allow google fonts, no system fonts
				if(!isset($googlefonts)) $googlefonts = self::get_google_fonts();
				$_font = str_replace(['+', '"', "'"], [' ', '', ''], $font);
				if(!isset($googlefonts[$_font])) $branch = 'custom';
			}

			$url = (isset($values['url'])) ? $values['url'] : '';
			
			$this->set_clean_font_import_v7($font, $url, $variants, $subsets, $branch);
		}
	}

	/**
	 * set the font clean for import
	 * @return void
	 */
	public function set_clean_font_import_v7($font, $url = '', $variants = [], $subsets = [], $branch = 'queue'){
		$g = &$this->context()->root();
		
		if(!isset($g['fonts'])) $g['fonts'] = ['queue' => [], 'loaded' => [], 'custom' => []]; //if this is called without revslider.php beeing loaded
		
		if(!empty($variants) || !empty($subsets)){
			if(!isset($g['fonts'][$branch][$font])) $g['fonts'][$branch][$font] = [];
			if(!isset($g['fonts'][$branch][$font]['variants'])) $g['fonts'][$branch][$font]['variants'] = [];
			if(!isset($g['fonts'][$branch][$font]['subsets'])) $g['fonts'][$branch][$font]['subsets'] = [];
			
			if(!empty($variants)){
				foreach($variants as $type => $variant){
					if(empty($variant)) continue;
					if(!isset($g['fonts'][$branch][$font]['variants'][$type])) $g['fonts'][$branch][$font]['variants'][$type] = [];
					foreach($variant as $k => $v){
						//check if the variant is already in loaded
						if(!in_array($v, $g['fonts'][$branch][$font]['variants'][$type], true)){
							$g['fonts'][$branch][$font]['variants'][$type][] = $v;
						}else{ //already included somewhere, so do not call it anymore
							unset($variants[$type][$k]);
						}
					}
				}
			}

			foreach($subsets ?? [] as $k => $v){
				if(!in_array($v, $g['fonts'][$branch][$font]['subsets'], true)){
					$g['fonts'][$branch][$font]['subsets'][] = $v;
				}else{ //already included somewhere, so do not call it anymore
					unset($subsets[$k]);
				}
			}

			if($url !== ''){
				//the url of an uploaded font is stored inside the module, frozen to the origin it was saved on
				$rsf = Registry::instance()->get('RevSliderFonts');
				$g['fonts'][$branch][$font]['url'] = ($rsf !== null) ? $rsf->heal_custom_font_url($url) : $url;
				$g['fonts'][$branch][$font]['load'] = true;
			}
		}
	}
	
	/**
	 * Check if shortcodes exists in the content
	 * @since: 5.0
	 * @return array|false
	 */  
	public static function check_for_shortcodes($mid_content){
		if($mid_content === null) return false;
		if(!preg_match('/\[gallery[\s\]]/', $mid_content)) return false; //tag-boundary probe: [gallery-slider] and friends must not match; the regex below still demands the full [gallery ids=..] form

		preg_match('/\[gallery.*ids=.(.*).\]/', $mid_content, $img_ids);
		
		return (isset($img_ids[1]) && $img_ids[1] !== '') ? explode(',', $img_ids[1]) : false;
	}

	/**
	 * add all options that change the slider here, for the cache to properly work
	 * @since: 6.4.6
	 * @return string
	 **/
	public function get_transient_alias(){
		$transient = 'revslider_slider';
		$transient .= '_'.$this->get_slider_id();

		//a post based module can read the page it sits on (current post, related posts, the current product),
		//so those get one entry per page; a gallery module is the same everywhere and keeps a single entry.
		//current_entry_id() is the post the content source reads; the queried object is one id for a whole loop
		$source_type	= $this->slider->get_param(['source', 'type'], 'gallery');
		$per_page		= (in_array($source_type, ['post', 'posts', 'specific_posts', 'specific_post', 'current_post', 'woocommerce', 'woo'], true)) ? $this->environment()->current_entry_id() : 0;

		$args = [
			'fontdownload' => $this->get_val($this->global_settings, ['fonts', 'download'], 'off'),
			'page'		=> $per_page,
			'lang'		=> apply_filters('wpml_current_language', $this->environment()->locale()),
			'serial'	=> $this->context()->get('serial'),
			'admin'		=> $this->environment()->is_admin_area(),
			'settings'	=> $this->custom_settings,
			'order'		=> $this->custom_order,
			'source'	=> $this->custom_source,
			'usage'		=> $this->usage,
			'modal'		=> $this->modal,
			'fullwidth'	=> $this->fullwidth,
			'fullheight' => $this->fullheight,
			'skin'		=> $this->custom_skin,
			'offset'	=> $this->offset,
			'mid_content' => $this->gallery_ids,
			//'export'	=> $this->markup_export,
			'preview'	=> $this->preview_mode,
			//'published'	=> $this->only_published
		];
		
		$transient .= '_'.md5(json_encode($args));
		
		return $transient;
	}

	/**
	 * print data from caching if caching is on and data is existing
	 * on a hit the stored markup is echoed and the recorded additions are replayed through RevSliderCache
	 * @return bool true when the slider was served from cache
	 **/
	public function get_from_caching(){
		//check if caching should be active or not
		$cache			= Registry::instance()->get('RevSliderCache');
		$source_type	= $this->slider->get_param(['source', 'type'], 'gallery');
		$can_do_cache	= ($this->get_preview_mode() === false && $cache->is_supported_type($source_type)) ? true : false;
		//same Question as in RevSliderApi::get_slider_object(), same Answer: see the Note there
		$can_do_cache	= apply_filters('sr_slider_can_cache', $can_do_cache, $this->slider);
		$this->caching	= ($cache->is_enabled() && $can_do_cache) ? true : false;
		$do_cache		= $this->slider->get_param(['general', 'icache'], 'default');
		$this->caching	= ($do_cache === 'on' && $can_do_cache) ? true : $this->caching;
		$this->caching	= ($do_cache === 'off') ? false : $this->caching;
		
		//add caching if its enabled
		if($this->caching === false) return false;
		$transient	= $this->get_transient_alias();
		$content	= $this->ttl_cache()->get($transient);
		if($content === false) return false;
	
		$content = json_decode($content, true);
		if(!isset($content['html'])) return false;

		echo $cache->do_html_changes($content['html']);
		
		$cache->do_additions($this->get_val($content, 'addition', []), $this);

		global $SR_GLOBALS;
		if($SR_GLOBALS['loaded_by_editor'] === true){ //for elementor
			$this->add_js();
		}else{
			add_action('wp_print_footer_scripts', [$this, 'add_js'], 99);
		}

		return true;
	}
}

//the deprecated RevSliderOutput subclass shim lives with the host bridges now (see wordpress/loader.php)