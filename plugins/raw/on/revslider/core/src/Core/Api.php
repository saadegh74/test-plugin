<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

namespace ThemePunch\SR\Core;

if(!defined('ABSPATH')) exit();

/**
 * Central request dispatcher - every editor action and every front end data fetch comes through here.
 *
 * Three entry points:
 *  - do_ajax_action()       admin AJAX. Requires a logged in user, a valid revslider_actions nonce and the
 *                           configured capability, then routes on the dotted `client_action` parameter
 *                           (e.g. "slider.save"), split into module / operation / suboperation.
 *  - do_front_ajax_action() public (nopriv) AJAX. No nonce - it only exposes read-only slider data and is
 *                           protected by a per-IP rate limit instead.
 *  - init_rest_api()        REST routes under /sliderrevolution/, for the front end and the preview iframe.
 *
 * Almost every method here is one endpoint of the same shape: read the payload with get_data(), hand it to
 * the class that owns the logic, answer with ajax_response_success()/_error()/_data(). Those end the
 * request, which is why the endpoints are documented as returning void.
 */
class Api extends Functions {
	/**
	 * The REST surface, spelled once. Every host registers these; an Add-On may add its own under the
	 * namespace. The same request reaches the same operation wherever the plugin runs, and the paths the
	 * front engine builds in _tpt.transport.restRoute (core/public/js/libs/tptools.js) always exist.
	 *
	 * {name} marks a parameter and never spans a segment, so sliders/slides/{id} cannot be read as
	 * sliders/{slider}. 'gate' names what the host has to establish before the route answers, never how:
	 *  - 'public'  anonymous, the front end fetches module data without a session
	 *  - 'editor'  a logged in user who may edit. The preview renders unsaved drafts, and the font list
	 *              carries the site's own uploaded families and their URLs, so neither is anonymous.
	 * A host that does not know a gate must refuse the route, not open it.
	 * @since 7.2.1
	 */
	const REST_ROUTES = [
		'sliders'	=> ['path' => 'sliderrevolution/sliders',					'params' => [],							'method' => 'GET', 'gate' => 'public',	'call' => 'get_full_slider_object'],
		'slider'	=> ['path' => 'sliderrevolution/sliders/{slider}',			'params' => ['slider' => '[\w\-]+'],	'method' => 'GET', 'gate' => 'public',	'call' => 'get_full_slider_object'],
		'slides'	=> ['path' => 'sliderrevolution/sliders/slides/{id}',		'params' => ['id' => '\d+'],			'method' => 'GET', 'gate' => 'public',	'call' => 'get_full_slider_object'],
		'stream'	=> ['path' => 'sliderrevolution/sliders/stream/{id}',		'params' => ['id' => '[0-9,]+'],		'method' => 'GET', 'gate' => 'public',	'call' => 'get_stream_data'],
		'modal'		=> ['path' => 'sliderrevolution/sliders/modal/{slider}',		'params' => ['slider' => '[\w\-]+'],	'method' => 'GET', 'gate' => 'public',	'call' => 'get_slider_modal_data'],
		'fonts'		=> ['path' => 'sliderrevolution/fonts',						'params' => [],							'method' => 'GET', 'gate' => 'editor',	'call' => 'get_font_list'],
		'font'		=> ['path' => 'sliderrevolution/fonts/{font}',				'params' => ['font' => '[^/]+'],		'method' => 'GET', 'gate' => 'editor',	'call' => 'get_font_list'],
		'preview'	=> ['path' => 'sliderrevolution/sliders/preview/{module}',	'params' => ['module' => '[\w\-]+'],	'method' => 'GET', 'gate' => 'editor',	'call' => 'get_slider_preview'],
	];

	private $global_settings	= [];

	// The allowlist for a "low-privileged editor support"
	public $user_allowed		= ['fonts.get', 'fonts.get.google', 'library.get', 'library.load.image', 'library.load.object', 'module.load', 'page_effects.get', 'page_effects.preview', 'page_effects.save', 'plugin.modal.*', 'plugin.modals', 'plugin.panel.*', 'plugin.panels', 'plugin.get.nonce', 'plugin.get.popups', 'revslider-*-addon.all.get', 'revslider-*-addon.lang.get', 'revslider-*-addon.template.list', 'revslider-*-addon.values.get', 'slide.get', 'slide.get.by_slider_id', 'slide.get.layers', 'slide.quickedit.get', 'slide.quickedit.save', 'slide.quickedit.duplicate', 'slide.quickedit.delete', 'slider.get', 'slider.get.alias', 'slider.get.full', 'slider.get.image', 'slider.get.layout', 'slider.get.list', 'slider.get.post_templates', 'wordpress.create.image', 'platform.create.image', 'slider.get.text', 'slider.optimize' ];

	// Actions in this list does not trigger cache invalidate for the revslider object-cache namespace
	public $no_cache_invalidate	= ['template.get_short', 'slider.export', 'slider.export_html', 'slider.get_image', 'slider.get_fullheight', 'wordpress.get.object', 'platform.get.object', 'plugin.get_settings', 'slide.get.by_slider_id', 'slide.quickedit.get', 'slide.quickedit.save', 'slider.get.full', 'slider.get.full_object', 'plugin.subscribe', 'plugin.check.system', 'slide.get_layers', 'layers.export', 'wordpress.get_image', 'platform.get_image', 'library.load_image', 'plugin.get_help', 'plugin.get.tooltips', 'addon.get_sizes', 'editor.get.all', 'page_effects.get', 'page_effects.preview', 'slots.get', 'slots.save', 'slots.delete', 'slots.seed', 'slots.status', 'slots.status.save', 'slots.export', 'slots.findings', 'slots.bug', 'slots.derive' ];
	
	public $REST				= false;
	public $front_ajax			= false; //true only during do_front_ajax_action() (nopriv front-end fetch); mirrors REST for object caching so both transports are cached
	public $slider				= false;
	public $slide				= false;
	public $compress_response	= false; //when true, large ajax payloads are gzip+base64 packed to slip under server/WAF response-body limits

 	public function __construct(){
		$this->add_actions();
		$this->global_settings	= $this->get_global_settings();
		$this->slider			= new Slider();
		$this->slide			= new Slide();
	}

	/**
	 * Add all actions that the backend needs here
	 * @return void
	 **/
	public function add_actions(){
		add_action('wp_ajax_revslider_ajax_action', [$this, 'do_ajax_action']); //ajax response to save slider options.
		add_action('wp_ajax_rs_ajax_action', [$this, 'do_ajax_action']); //ajax response to save slider options.
		add_action('wp_ajax_nopriv_rs_ajax_action', [$this, 'do_ajax_action']); //for not logged in users
		add_action('wp_ajax_revslider_ajax_call_front', [$this, 'do_front_ajax_action']);
		add_action('wp_ajax_nopriv_revslider_ajax_call_front', [$this, 'do_front_ajax_action']); //for not logged in users
		add_action('rest_api_init', [$this, 'init_rest_api']);
		add_filter('rest_pre_serve_request', [$this, 'init_rest_api_return'], 10, 4);
	}

	/**
	 * the WordPress transport this API rides on; hosts replace it wholesale
	 * @since 7.2.1
	 * @return RevSliderWpTransport
	 */
	public function transport(){
		$globals = Registry::instance();
		$t = $globals->get('RevSliderWpTransport');
		if($t === null){
			$cls = apply_filters('revslider_transport_class', 'RevSliderWpTransport'); //the same host seam as revslider_content_source_class
			$t = new $cls($this);
			$globals->add('RevSliderWpTransport', $t);
		}
		return $t;
	}

	/**
	 * the same transport from a static context, where the core has no $this to ask, and only when it
	 * carries the answer: a host whose transport has no $method gets false, so the caller can answer
	 * with the core's own "none" instead of dying on an undefined method
	 * is_callable, not method_exists: the latter is true for a private method too (RevSliderWpTransport
	 * declares can_view_module_preview() private), which would hand back a transport the caller fatals on
	 * @since 7.2.1
	 * @param string $method the answer the caller needs
	 * @return object|false
	 */
	public static function host_transport($method){
		$api = Registry::instance()->get('RevSliderApi');
		if($api === null) return false;
		$t = $api->transport();

		return is_callable([$t, $method]) ? $t : false;
	}

	/**
	 * what the library, animation and system endpoints call into: the host's admin object where one was built
	 * (the library filters receive it), else the core class every one of those methods is defined on
	 * @since 7.2.1
	 * @return Admin\Functions
	 */
	private function admin_functions(){
		$globals = Registry::instance();
		return $globals->get('RevSliderAdmin') ?: $globals->get(Admin\Functions::class);
	}

	/**
	 * Init the REST API - wiring lives on the WP transport
	 * @return void
	 **/
	public function init_rest_api(){
		$this->transport()->init_rest_api();
	}
	
	/** @return bool */
	public function init_rest_api_return($served, $result, $request, $server){
		return $this->transport()->init_rest_api_return($served, $result, $request, $server);
	}

	/** @return void */
	public function set_rest_call(){
		$this->REST = true;
	}

	/** @return bool */
	public function is_rest_call(){
		return $this->REST;
	}

	/** @return bool true when nonce and capability check passed; ends the request otherwise */
	public function check_nonce(){
		$this->setup_exception_handler();
		if(!$this->environment()->verify_csrf($this->request_nonce(), 'revslider_actions')){
			$this->ajax_response_error(__('Bad Request', 'revslider'));
			exit;
		}

		if($this->current_user_can_not()){
			$this->ajax_response_error(__('Bad Request', 'revslider'));
			exit;
		}

		return true;
	}

	/**
	 * the CSRF token the request carries, under nonce or rs-nonce
	 * @since 7.2.1
	 * @return string
	 */
	public function request_nonce(){
		$nonce = $this->get_request_var('nonce');
		return (empty($nonce)) ? $this->get_request_var('rs-nonce') : $nonce;
	}

	/** @return bool */
	public function setup_exception_handler(){
		set_exception_handler([$this, 'handle_rest_exceptions']);

		return true;
	}


	/** @return bool */
	public function preview_permission_check(){
		return $this->transport()->preview_permission_check();
	}

	/** @return void */
	public function handle_rest_exceptions(\Throwable $exception){
		$this->transport()->handle_rest_exceptions($exception);
	}

	/**
	 * The Ajax Action part for backend actions only - the WP transport carries the preamble
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 **/
	public function do_ajax_action(){
		$this->transport()->do_ajax_action();
	}

	/**
	 * one admin operation of a logged-in caller, from the gates to dispatch(): the limited-role allowlist, the CSRF
	 * check, the free modules limit, cache invalidation and preview mode. Every path answers and ends the request.
	 * @since 7.2.1
	 * @param string $module_full dotted op code
	 * @param string $nonce the request's CSRF token
	 * @param mixed $data decoded request payload, '' or 'undefined' for none
	 * @return void
	 */
	public function handle_admin_request($module_full, $nonce, $data){
		$module	= explode('.', $module_full)[0];
		$data	= ($data == '' || $data == 'undefined') ? [] : $data;

		try{
			if($this->current_user_can_not()){
				// apply restrictions to $user_allowed list similar to shortcode_generator
				$can_use_limited_ajax = $this->environment()->user_can('edit_posts') || $this->environment()->user_can('edit_pages');
				if(!$can_use_limited_ajax || (!in_array($module_full, $this->user_allowed) && !$this->ajax_check_allowed($module_full, $this->user_allowed))){
					$return = apply_filters('revslider_admin_onAjaxAction_user_restriction', false, $module, $data, $this->slider, $this->slide);
					if($return === false){
						$this->ajax_response_error(__('Function only available for administrators', 'revslider'));
						exit;
					}
				}
			}

			//plugin.get.nonce hands out a fresh token, so it cannot demand a valid one
			if(!$this->environment()->verify_csrf($nonce, 'revslider_actions') && 'plugin.get.nonce' !== $module_full){
				$this->ajax_response_error(__('Bad Request', 'revslider'));
			}

			//only the plugin's object-cache group; markup transients clear per module in clear_cache()
			if(!in_array($module_full, $this->no_cache_invalidate)) $this->invalidate_group_cache();

			//set preview mode
			if($this->_truefalse($this->get_val($data, 'preview', false)) === true) $this->context()->set('preview_mode', true);
			$this->slider->set_special_table_mode();
			$this->slide->set_special_table_mode();

			$this->dispatch($module_full, $data);
		}catch(\Throwable $e){ //TypeError and ValueError are Errors, not Exceptions
			$this->ajax_response_error($e->getMessage());
		}

		//it's an ajax action, so exit
		$this->ajax_response_error(__('No response on action', 'revslider'));
	}

	/**
	 * the platform-neutral dispatch table: route one dotted op code to its operation.
	 * Transport, nonce and capability checks are the calling adapter's job (do_ajax_action
	 * keeps them as its preamble); operations send their own response and end the request.
	 * @param string $action dotted op code, e.g. 'slider.duplicate' or 'slide.set.startslide'
	 * @param mixed $data decoded request payload
	 * @return void falls through silently on an unknown op code
	 * @since 7.2.1
	 */
	public function dispatch($action, $data){
		$module			 = $action;
		$operation		 = '';
		$suboperation	 = '';
		$subsuboperation = '';
		if(strpos($module, '.') !== false){
			$parts		= explode('.', $module);
			$module		= $this->get_val($parts, 0);
			$operation	= $this->get_val($parts, 1);
			$suboperation = $this->get_val($parts, 2);
			$subsuboperation = $this->get_val($parts, 3);
		}

		switch($module){
			case 'module':
				switch($operation){
					case 'load': //load_module
						$this->load_module($data);
					break;
				}
			break;
			case 'slider':
				switch($operation){
					case 'create': //create_slider
						$this->create_slider();
					break;
					case 'save':
						if(empty($suboperation)) $this->save_slider($data); //save_slider_v7

						switch($suboperation){
							case 'advanced': //save_slider_advanced
								$this->save_slider_advanced($data);
							break;
							case 'tag': //update_slider_tags
								$this->save_slider_tags($data);
							break;
							case 'favorite': //set_favorite
								$this->save_slider_favorite($data);
							break;
							case 'modal_ids': //adjust_modal_ids
								$this->save_slider_modal_ids($data);
							break;
							case 'js_css_ids': //adjust_js_css_ids
								$this->save_slider_js_css_ids($data);
							break;
							case 'name': //update_slider_name
								$this->save_slider_name($data);
							break;	
						}
					break;
						case 'delete': //delete_slider, a module goes to the trash
						$this->delete_slider($data);
					break;
						case 'restore': //restore_slider
							$this->restore_slider($data);
						break;
						case 'destroy': //destroy_slider, out of the trash for good
							$this->destroy_slider($data);
						break;
						case 'emptytrash': //empty_trash
							$this->empty_trash($data);
						break;
						case 'trashlist': //get_trash_list
							$this->get_trash_list($data);
						break;
					case 'get': //get_module
						if(empty($suboperation)) $this->get_module($data);

						switch($suboperation){
							case 'full': //get_full_slider_object_v7
								$this->get_full_slider_object(false, false);
							break;
							case 'full_object': //get_full_slider_object v6 call only????
								//$this->get_full_slider_object();
								$this->get_full_slider_object_v6($data = false);
							break;
							case 'alias': //check_alias
								$this->get_slider_alias($data);
							break;
							case 'image': //getSliderImage
								$this->get_slider_image($data);
							break;
							case 'layout': //getSliderSizeLayout
								$this->get_slider_layout($data);
							break;
							case 'post_templates':
								$this->ajax_response_data(['templates' => $this->slider->get_sliders_with_slides_short('template')]);
							break;
								case 'text': //what an SEO analysis reads of a module (Core\Seo; Yoast / Rank Math on WordPress)
									$this->ajax_response_data(['modules' => Registry::instance()->get(Seo::class)->modules_text($this->get_val($data, 'aliases', []))]);
								break;
							case 'overview':
								$this->slider_get_overview_data($data);
							break;
							//the JS twin of BuilderApi::modules(), so a client-side integration gets the
							//identical array rather than a second listing that drifts from it (CONTRACT B8.1)
							case 'list':
								$this->builder_module_list($data);
							break;
						}
					break;
					case 'upgrade': //silent_slider_update
						$this->slider_upgrade();
					break;
					case 'upgrade_v6': //silent_slider_update
						$this->slider_upgrade_v6($data);
					break;
					case 'migrate': //saves the slide as v7 slide and sets in v6 db a parameter to the slider that upgrade to v7 started
						$this->migrate_slider($data);
					break;
					case 'duplicate': //duplicate_slider
						$this->slider_duplicate($data);
					break;
					case 'optimize': 
						$this->slider_optimize($data);
					break;
					case 'thumb':
						if($suboperation === 'auto') $this->slider_thumb_auto($data);
					break;
					case 'import': //import_slider
						$this->slider_import($data);
					break;
					case 'export': 
						if(empty($suboperation)) $this->slider_export(); //export_slider

						switch($suboperation){
							case 'html': //export_slider_html
								$this->slider_export_html();
							break;
						}
					break;
				}
			break;
			case 'slide':
				switch($operation){
					case 'create': //create_slide
						$this->slide_create($data);
					break;
					case 'save':
						if(empty($suboperation)) $this->save_slide($data); //save_slide_v7

						switch($suboperation){
							case 'advanced': //save_slide_advanced
								$this->save_slide_advanced($data);
							break;
							case 'order': //update_slide_order
								$this->save_slide_order($data);
							break;
						}
					break;
					case 'migrate': //saves the slide as v7 slide and sets in v6 db a parameter to the slider that upgrade to v7 started
						$this->migrate_slide($data);
					break;
					case 'get':
						if(empty($suboperation)) $this->get_slides($data); //get_slides

						switch($suboperation){
							case 'by_slider_id': //get_slides_by_slider_id
								$this->get_slide_by_slider_id($data);
							break;
							case 'layers': //get_layers_by_slide
								$this->get_slide_layers($data);
							break;
						}
					break;
					case 'delete': //delete_slide
						$this->delete_slide($data);
					break;
					case 'duplicate': //duplicate_slide
						$this->duplicate_slide($data);
					break;
					case 'quickedit':
						switch($suboperation){
							case 'get':
								$this->quickedit_get($data);
							break;
							case 'save':
								$this->quickedit_save($data);
							break;
							case 'duplicate':
								$this->quickedit_duplicate_slide($data);
							break;
							case 'delete':
								$this->quickedit_delete_slide($data);
							break;
						}
					break;
				}
			break;
			case 'layers':
				switch($operation){
					case 'get': //get_layers
						$this->get_layers($data);
					break;
					case 'export': //export_layer_group developer function only :)
						$this->export_layers($data);
					break;
				}
			break;
			case 'folder':
				switch($operation){
					case 'create': //create_slider_folder
						$this->create_slider_folder($data);
					break;
					case 'save_name': //update_folder_name
						$this->save_slider_name($data);
					break;
					case 'delete': //delete_slider
						$this->delete_slider($data);
					break;
					case 'save': //save_slider_folder
						$this->save_slider_folder($data);
					break;
				}
			break;
			case 'fonts':
				switch($operation){
					case 'get':
						if(empty($suboperation)) $this->get_font_list(); //get_font_list

						switch($suboperation){
							case 'google': //load_google_font
								$this->download_collected_fonts($data);
							break;
						}
					break;
					case 'collect':
						switch($suboperation){
							case 'google': //collect_google_fonts
								$this->collect_google_fonts($data);
							break;
						}
					break;
						case 'upload': //upload_custom_font
							$this->upload_custom_font();
						break;
					case 'delete':
						switch($suboperation){
							case 'cache': //delete_full_fonts_cache
								$this->delete_google_fonts();
							break;
							case 'custom': //delete_custom_font_files
								$this->delete_custom_font_files($data);
							break;
						}
					break;
				}
			break;
			case 'addon':
				switch($operation){
					case 'get':
						$this->get_addon($data);
					break;
					case 'activate': //activate_addon
						$this->activate_addon($data);
					break;
					case 'deactivate': //deactivate_addon
						$this->deactivate_addon($data);
					break;
					case 'moved': //SR7 builds of the add-ons that moved from SR6, without a licence
						$this->update_moved_addons();
					break;
						case 'effects': //refresh_effects_catalogue
							$this->refresh_effects_catalogue();
						break;
				}
			break;
			case 'template':
				switch($operation){
					case 'get':
						switch($suboperation){
							case 'short': //get_template_information_short
								$this->get_template_short();
							break;
						}
					break;
					case 'import':
						switch($suboperation){
							case 'slider': //import_template_slider
								$this->import_template_slider($data);
							break;
							case 'slide': //install_template_slide || most likely obsolete
								$this->import_template_slide($data);
							break;
							case 'media':
								$this->import_template_media($data);
							break;
						}
					break;
				}
			break;
			//Template Packages v2 — a package that ships whole pages. See docs/template-packages-v2.md.
			case 'package':
				switch($operation){
					case 'inspect': //download the package zip, parse its manifest, report what installing would do
						$this->inspect_package($data);
					break;
					case 'install':
						switch($suboperation){
							case 'begin': //open an install and get the phase list
								$this->begin_package_install($data);
							break;
							case 'step': //run the next phase
								$this->step_package_install($data);
							break;
						}
					break;
					case 'installed': //every install this site has a receipt for
						$this->get_package_installs();
					break;
					case 'uninstall':
						switch($suboperation){
							case 'report': //what removing it would take away
								$this->get_package_uninstall_report($data);
							break;
							default: //remove it
								$this->uninstall_package($data);
							break;
						}
					break;
				}
			break;
			case 'stream':
				switch($operation){
					case 'facebook':
						switch($suboperation){
							case 'login-url':
								$this->facebook_loginurl($data);
							break;
							case 'photosets':
								$this->facebook_photosets($data);
							break;
						}
						$this->ajax_response_error(__('Missing get parameter', 'revslider'));
					break;
					case 'instagram':
						switch($suboperation){
							case 'login-url':
								$this->instagram_loginurl($data);
							break;
						}
						$this->ajax_response_error(__('Missing get parameter', 'revslider'));
					break;
					case 'youtube':
						switch($suboperation){
							case 'playlists':
								$this->youtube_playlists($data);
							break;
						}
						$this->ajax_response_error(__('Missing get parameter', 'revslider'));
					break;
					case 'flickr':
						switch($suboperation){
							case 'photosets':
								$this->flickr_photosets($data);
							break;
						}
					break;
				}
			break;
			case 'plugin':
				switch($operation){
					case 'cleanup_v6':
						if(!\ThemePunch\SR\Core\Admin\UpgradeV6::do_v6_tables_exist()) $this->ajax_response_success(__('R6 data and unmigrated modules have been successfully removed', 'revslider'));
						
						$return = \ThemePunch\SR\Core\Admin\UpgradeV6::delete_v6_tables();
						if($return === true) $this->ajax_response_success(__('SR6 data and unmigrated modules have been successfully removed', 'revslider'));

						$this->ajax_response_error($return);
					break;
					case 'get':
						switch($suboperation){
							case 'tooltips':
								$handle = $this->get_val($data, 'handle', false);
								if($handle !== false){
									$tooltip = $this->get_tooltip_by_handle($handle);
								}else{
									$search = $this->get_val($data, 'search');
									$tooltip = $this->get_tooltips_by_string($search);
								}

								if($tooltip === false) $this->ajax_response_error(__('No tooltip found', 'revslider'));

								$this->ajax_response_data(['tooltips' => $tooltip]);
							break;
							case 'settings': //get_global_settings
								$this->get_plugin_settings();
							break;
							case 'sliders':
								$this->get_plugin_sliders();
							break;
							case 'animations':
								$animations = $this->get_layer_animations();
								$this->ajax_response_data(['animations' => $animations]);
							break;
							case 'nonce':
								$this->ajax_response_data(['nonce' => $this->environment()->csrf_token('revslider_actions')]);
							break;
							case 'popups':
								$this->ajax_response_data(['html' => $this->get_popups_markup($data)]);
							break;
						}
					break;
					case 'save':
						switch($suboperation){
							case 'settings': //update_global_settings
								$this->save_plugin_settings($data);
							break;
							case 'tooltip': //set_tooltip_preference
								$this->save_plugin_tooltip();
							break;
						}
					break;
					case 'dismiss':
						switch($suboperation){
							case 'deregister': //close_deregister_popup
								$this->dismiss_plugin_deregister();
							break;
							case 'trustpilot': //deactivate_trustpilot
								$this->dismiss_plugin_trustpilot();
							break;
							case 'notice': //dismiss_dynamic_notice
								$this->dismiss_plugin_notice($data);
							break;
						}
					break;
					case 'check':
						switch($suboperation){
							case 'system': //check_system
								$this->check_plugin_system();
							break;
							case 'upgrade': //check_for_updates
								$this->upgrade_check_plugin();
							break;
						}
					break;
					case 'database':
						switch($suboperation){
							case 'check':
								$this->plugin_database_check();
							break;
							case 'force':
								$this->plugin_database_force();
							break;
						}
					break;
					case 'delete':
						switch($suboperation){
							case 'cache': //clear_internal_cache
								$this->delete_plugin_cache();
							break;
						}
					break;
					case 'activate': //activate_plugin
						$this->activate_plugin($data);
					break;
					case 'deactivate': //deactivate_plugin
						$this->deactivate_plugin();
					break;
					case 'subscribe': //subscribe_to_newsletter
						$this->subscribe_plugin($data);
					break;
					case 'panel':
						$result = $this->get_plugin_panel_html($suboperation);
						if ($result['error']){
							$this->ajax_response_error($result['message']);
						}
						$this->ajax_response_data(['html' => $result['html']]);
					break;
					case 'panels':
						$panels = $data['panels'] ?? [];
						$html   = [];
						foreach($panels as $p){
							$result = $this->get_plugin_panel_html($p);
							if ($result['error']){
								$this->ajax_response_error($result['message']);
							}
							$html[$p] = $result['html'];
						}
						$this->ajax_response_data(['panels' => $html]);
					break;
					case 'modal':
						$result = $this->get_plugin_modal_html($suboperation);
						if ($result['error']){
							$this->ajax_response_error($result['message']);
						}
						$this->ajax_response_data(['html' => $result['html']]);
					break;
					case 'modals':
						$modals = $data['modals'] ?? [];
						$html   = [];
						foreach($modals as $p){
							$result = $this->get_plugin_modal_html($p);
							if ($result['error']){
								$this->ajax_response_error($result['message']);
							}
							$html[$p] = $result['html'];
						}
						$this->ajax_response_data(['modals' => $html]);
						break;
				}
			break;
			case 'ai':
				switch($operation){
					case 'getslide':
						$this->get_ai_wrapper($data, 'get_slide');
					break;
					case 'gettext':
						$this->get_ai_wrapper($data, 'get_text');
					break;
					case 'translate':
						$this->get_ai_wrapper($data, 'translate');
					break;
					case 'create':
						$this->create_ai_element($data);
					break;
					case 'status':
						$this->get_ai_element_status($data);
					break;
					case 'bgjobs':
						switch($suboperation){
							case 'clear':
								$ai = Registry::instance()->get('RevSliderAI');
								$urls = $this->get_val($data, 'urls', []);
								$ai->clear_background_jobs($urls);
								$this->ajax_response_data('');
							break;
							case 'start':
								$ai = Registry::instance()->get('RevSliderAI');
								$ai->check_open_event_ids(true, true);
								$return = [
									'pending' => ! empty( $ai->get_open_events() ),
									'new'     => $ai->get_finished_background_jobs()
								];
								$this->ajax_response_data(['data' => $return]);
							break;
						}
						break;
					case 'credits':
						$this->get_ai_wrapper($data, 'get_credits');
					break;
					case 'assist':
						if($suboperation === 'plan') $this->get_ai_wrapper($data, 'assist_plan');
						if($suboperation === 'texts') $this->get_ai_wrapper($data, 'assist_texts');
					break;
				}
			break;
			case 'library':
				switch($operation){
					case 'preload':
						$this->library_preload();
						break;
					case 'get':
						$this->get_elements_library_all_new($data);
					break;
					case 'load':
						switch($suboperation){
							case 'object': //load_library_object
								$this->load_library_object($data);
							break;
							case 'image': //load_library_image
								$this->load_library_image($data);
							break;
						}
					break;
					case 'create':
						switch($suboperation){
							case 'tag': //create_customlibrary_tags
								$this->create_library_tag($data);
							break;
							case 'item': //upload_customlibrary_item
								$this->create_library_item($data);
							break;
						}
					break;
					case 'save':
						switch($suboperation){
							case 'tag': //edit_customlibrary_tags
								$this->save_library_tag($data);
							break;
							case 'item': //edit_customlibrary_item
								$this->save_library_item($data);
							break;
						}
					break;
					case 'delete':
						switch($suboperation){
							case 'tag': //delete_customlibrary_tags
								$this->delete_library_tag($data);
							break;
							case 'item': //delete_customlibrary_item
								$this->delete_library_item($data);
							break;
						}
					break;
				}
			break;
			case 'editor':
				switch($operation){
					case 'save':
						switch($suboperation){
							case 'color': //save_color_preset
								$this->save_editor_color($data);
							break;
							case 'slidetransitions': //save_custom_templates_slidetransitions
								$this->save_editor_slidetransitions($data);
							break;
							case 'navigation':
								switch($subsuboperation){
									case 'preset': //create_navigation_preset
										$this->create_navigation_preset($data);
									break;
									default:
										$this->save_navigation($data); //save_navigation
									break;
								}
							break;
							case 'animation': //save_animation
								$this->save_animation($data);
							break;
							case 'pbar': //save_pbar_preset
								$this->save_pbar_preset($data);
							break;
						}
					break;
					case 'delete':
						switch($suboperation){
							case 'slidetransitions': //delete_custom_templates_slidetransitions
								$this->delete_editor_slidetransitions($data);
							break;
							case 'navigation':
								switch($subsuboperation){
									case 'preset': //delete_navigation_preset
										$this->delete_navigation_preset($data);
									break;
									default:
										$this->delete_navigation($data);
									break;
								}
							break;
							case 'animation': //delete_animation
								$this->delete_animation($data);
							break;
							case 'pbar': //delete_pbar_preset
								$this->delete_pbar_preset($data);
							break;
						}
					break;
					case 'get':
						switch($suboperation){
							case 'all':
								$this->editor_get_all($data);
								break;
							case 'pbar': //the Progress Bar preset browser, read once when the panel opens
								$this->ajax_response_data(['presets' => $this->get_options(['presets', 'pbar'], [])]);
								break;
							case 'animations': //the user's own layer animations, My Presets in the animation browser
								$this->ajax_response_data(['animations' => $this->get_own_layer_animations()]);
								break;
							case 'navigation':
								switch($subsuboperation){
									case 'arrows':
									case 'thumbs':
									case 'bullets':
									case 'tabs':
									case 'scrubber':
										if(intval($this->get_val($data, 'id', 0)) > 0){
											$nav_skins = $this->get_navigation_skin_by_id($this->get_val($data, 'id', 0), $subsuboperation);
										}elseif($this->get_val($data, 'handle', false) !== false){
											$nav_skins = $this->get_navigation_skin_by_handle($this->get_val($data, 'handle', false), $subsuboperation);
										}elseif($this->get_val($data, 'full', false) !== false){ //the skin browser draws every tile from the real css+markup, so it needs them all at once instead of one request per skin
											$nav_skins = $this->get_val($this->get_navigation_skins(), $subsuboperation, []);
										}else{
											$nav_skins = $this->get_navigation_skins_short($subsuboperation);
										}
										$this->ajax_response_data(['skins' => $nav_skins]);
									break;
									default:
										$nav_skins = $this->get_navigation_skins();
										$this->ajax_response_data(['skins' => $nav_skins]);
									break;
								}
							break;
						}
					break;
				}
			break;
			case 'platform': //host-neutral alias; the editor keeps sending wordpress.*, both route identically
			case 'wordpress':
				switch($operation){
					case 'get':
						switch($suboperation){
							case 'object': //load_wordpress_object
								$this->get_wordpress_object($data);
							break;
							case 'image': //load_wordpress_image
								$this->get_wordpress_image($data);
							break;
							case 'image_id':
								$this->get_wordpress_image_id($data);
							break;
							case 'same_aspect_ratio': //get_same_aspect_ratio
								$this->get_wordpress_same_aspect_ratio($data);
							break;
							case 'pages':
								$this->get_wordpress_pages();
							break;
							case 'post-types':
								$this->get_wordpress_post_types();
							break;
							case 'post-data':
								$this->get_wordpress_post_data();
							break;
							case 'post-popular':
								$this->get_wordpress_post_popular();
							break;
							case 'post-latest':
								$this->get_wordpress_post_latest();
							break;
						}
					break;
					case 'create':
						switch($suboperation){
							case 'media': //add_to_media_library
								$this->create_wordpress_media();
							break;
							case 'draft_page': //create_draft_page
								$this->create_wordpress_draft_page($data);
							break;
							case 'metadata': //generate_attachment_metadata
								$this->create_wordpress_metadata();
							break;
							case 'image': //create_image_from_raw
								$this->create_wordpress_image($data);
							break;
							case 'image_from_url':
								$this->create_wordpress_image_from_url($data);
							break;
						}
					break;
				}
			break;
			case 'preset':
				switch($operation){
					case 'get':
						$this->get_presets($data);
					break;
					case 'save':
						$this->save_preset($data);
					break;
					case 'rename':
						$this->rename_preset($data);
					break;
					case 'delete':
						$this->delete_preset($data);
					break;
				}
			break;
			case 'page_effects':
				switch($operation){
					case 'save': //save a page effect instance to its host post meta
						$this->save_page_effect($data);
					break;
					case 'get': //load a stored page effect instance
						$this->get_page_effect($data);
					break;
					case 'preview': //stage unsaved draft for Preview Live (transient, not post meta)
						$this->preview_page_effect($data);
					break;
				}
			break;
				case 'slots': //layer meta, Meta Editing mode of Quick Edit (RevSliderSlots::CAP holders only)
					switch($operation){
						case 'get':
							$this->slots_get($data);
						break;
						case 'save':
							$this->slots_save($data);
						break;
						case 'delete':
							$this->slots_delete($data);
						break;
						case 'seed':
							$this->slots_seed($data);
						break;
						case 'status': //the tester's verdict on a template
							if($suboperation === 'save') $this->slots_status_save($data);
							else $this->slots_status($data);
						break;
						case 'export': //the whole meta table, seed shape
							$this->slots_export($data);
						break;
						case 'bug': //one click, one Monday item
							$this->slots_bug($data);
						break;
						case 'derive': //copy the twins' confirmed rows onto the derived templates
							$this->slots_derive($data);
						break;
						case 'findings': //per-template triage report
							$this->slots_findings($data);
						break;
					}
				break;
			default:
				$return = ''; //''is not allowed to be added directly in apply_filters(), so its needed like this
				$return = apply_filters('revslider_do_ajax', $return, $module, $operation, $suboperation, $data);
				if($return){
					if(is_array($return)){
						if(isset($return['error'])) $this->ajax_response_error($return['error']);
						if(isset($return['message'])) $this->ajax_response_data(['message' => $return['message'], 'data' => $return['data']]);
		
						$this->ajax_response_data(['data' => $return['data']]);
					}
		
					$this->ajax_response_success($return);
				}
			break;
		}
	}

	/**
	 * Ajax handling for frontend, no privileges here - the WP transport carries the preamble
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function do_front_ajax_action(){
		$this->transport()->do_front_ajax_action();
	}

	/**
	 * one anonymous front-end operation, answered and cached like its REST twin
	 * @since 7.2.1
	 * @param string $action dotted op code
	 * @return void
	 */
	public function handle_front_request($action){
		$this->front_ajax = true; //front-end (nopriv) transport - lets get_full_slider_object() use the object cache here too

		$this->dispatch_front($action);
	}

	/**
	 * Throttle for the anonymous front-end fetch.
	 *
	 * These endpoints are unauthenticated and return full slider objects, so a per-IP fixed-window limiter blunts
	 * scraping and denial-of-service abuse. Logged-in users are never throttled. Tune or disable it through the
	 * `revslider_front_ajax_rate_limit` filter ($cfg['limit'], $cfg['window'], $cfg['enabled']). Counts the request;
	 * the host sends the 429 when this answers more than 0.
	 *
	 * @since 7.2.1
	 * @param bool|null $logged_in the host's own answer, null to go by Environment::user_id()
	 * @return int 0 when the request may run, else the seconds for a Retry-After
	 */
	public function front_rate_limit_retry($logged_in = null){
		$cfg = apply_filters('revslider_front_ajax_rate_limit', [
			'enabled'	=> true,
			'limit'		=> 120, //max requests per window, per IP
			'window'	=> 60,	//window length in seconds
		]);

		//only throttle anonymous callers; skip when disabled or for logged-in users
		if(empty($cfg['enabled']) || ($logged_in ?? $this->environment()->user_id() > 0)) return 0;

		return $this->rate_window_retry('rs_frl_', max(1, (int)$this->get_val($cfg, 'limit', 120)), max(1, (int)$this->get_val($cfg, 'window', 60)));
	}

	/**
	 * one fixed window per IP, counted under its own key prefix
	 * @since 7.2.9
	 * @param string $prefix the key prefix, one per limiter
	 * @param int    $limit  requests allowed per window
	 * @param int    $window seconds
	 * @return int 0 when the request may run, else the seconds for a Retry-After
	 */
	protected function rate_window_retry($prefix, $limit, $window){
		$ip = $this->get_client_ip();
		if($ip === '') return 0; //fail open when the caller cannot be identified

		$key	= $prefix . md5($ip . '|' . $window);
		$now	= time();

		//a store that can count in one operation is asked first: a read, an increment and a write loses every
		//event that arrives while the value is out of the store, which is exactly the caller this exists for
		$counted = $this->ttl_cache()->count_in_window($key, $window);
		if(is_array($counted)){
			$count = (int)$this->get_val($counted, 'count', 1);
			$start = (int)$this->get_val($counted, 'start', $now);

			return ($count > $limit) ? max(1, $window - ($now - $start)) : 0;
		}

		$bucket	= $this->ttl_cache()->get($key);

		//start a fresh fixed window when none exists or the previous one has elapsed
		if(!is_array($bucket) || ($now - (int)$this->get_val($bucket, 'start', 0)) >= $window){
			$this->ttl_cache()->set($key, ['start' => $now, 'count' => 1], $window);
			return 0;
		}

		$bucket['count'] = (int)$this->get_val($bucket, 'count', 0) + 1;
		$this->ttl_cache()->set($key, $bucket, $window);

		return ($bucket['count'] > $limit) ? max(1, $window - ($now - (int)$this->get_val($bucket, 'start', $now))) : 0;
	}

	/**
	 * the platform-neutral dispatch table for the anonymous front-end ops; the operations
	 * read their own request vars and send their own response
	 * @param string $action dotted op code, e.g. 'slider.get.full_object'
	 * @return void falls through silently on an unknown op code
	 * @since 7.2.1
	 */
	public function dispatch_front($action){
		$module		 = $action;
		$operation	 = '';
		$suboperation = '';
		if(strpos($module, '.') !== false){
			$parts		= explode('.', $module);
			$module		= $this->get_val($parts, 0);
			$operation	= $this->get_val($parts, 1);
			$suboperation = $this->get_val($parts, 2);
		}

		switch($module){
			case 'slider':
				switch($operation){
					case 'get':
						switch($suboperation){
							case 'full_object': //get_full_slider_object
								$this->get_full_slider_object();
							break;
							case 'stream': //get_stream_data
								$this->get_stream_data();
							break;
							case 'modal': //get_modal_data
								$this->get_slider_modal_data();
							break;
							case 'html': //get_slider_html
								$this->get_slider_html();
							break;
						}
					break;
				}
			break;
			case 'form':
				if($operation === 'submit') $this->form_submit();
			break;
			case 'plugin':
				switch($operation){
					case 'get':
						switch($suboperation){
							case 'transitions':
								$transitions = $this->get_base_transitions();
								$transitions['advanced'] = $this->get_advanced_transitions(); //the Transition Pack's tiles stand whether or not the pack is here
								$this->ajax_response_data(['transitions' => $transitions]);
							break;
						}
					break;
				}
			break;
		}
	}



	/**
	 * Submit Form with a Send to URL: the fields go from the server to the URL saved at the action, found by module, slide and
	 * action id. A URL in the request is never read. Anonymous, so every answer is the same, whether anything was sent or not.
	 * Filters: `revslider_form_submit_rate_limit` (limit, window; per IP), `revslider_form_submit` (the payload, false stops it),
	 * action `revslider_form_submitted` once it went out.
	 * @since 7.2.9
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function form_submit($data = false){
		$cfg	= apply_filters('revslider_form_submit_rate_limit', ['limit' => 10, 'window' => 60]);
		$retry	= $this->rate_window_retry('rs_ffl_', max(1, (int)$this->get_val($cfg, 'limit', 10)), max(1, (int)$this->get_val($cfg, 'window', 60)));
		if($retry > 0){
			if(!headers_sent()){
				http_response_code(429);
				header('Retry-After: ' . (int)$retry);
				header('Content-Type: application/json; charset=utf-8');
			}
			echo json_encode(['success' => false, 'message' => __('Too many requests. Please slow down and try again in a moment.', 'revslider')]);
			exit;
		}

		$data		= $this->get_data($data);
		$module		= (string)$this->get_val($data, 'module', '');
		$slide_id	= (string)$this->get_val($data, 'slide', '');
		$action_id	= (string)$this->get_val($data, 'action', '');
		$fields		= $this->get_val($data, 'fields', []);
		if($module === '' || !ctype_digit($slide_id) || $action_id === '' || !is_array($fields)) $this->ajax_response_success('');

		if(ctype_digit($module)) $this->slider->init_by_id($module, false); else $this->slider->init_by_alias(sanitize_text_field($module), false);
		if($this->slider->inited === false) $this->ajax_response_success('');

		//only a slide of this module: its own slides or its static one
		$slide = null;
		foreach($this->slider->get_slides(false, true) ?: [] as $s){
			if((string)$s->get_id() === $slide_id) $slide = $s;
		}
		if($slide === null && (string)$this->slide->get_static_slide_id($this->slider->get_id()) === $slide_id){
			$this->slide->init_by_static_id($slide_id);
			$slide = $this->slide;
		}
		if($slide === null) $this->ajax_response_success('');

		$url = '';
		foreach($slide->get_param('actions', []) ?: [] as $action){
			if(!is_array($action) || (string)$this->get_val($action, 'id', '') !== $action_id || $this->get_val($action, 'a') !== 'submit') continue;
			$url = $this->get_val($action, 'hook', '');
			break;
		}
		//https only; the host's HTTP client refuses internal addresses (HttpClient::post_body 'safe')
		if(!is_string($url) || strtolower((string)parse_url($url, PHP_URL_SCHEME)) !== 'https') $this->ajax_response_success('');

		$clean = [];
		foreach($fields as $name => $value){
			if(count($clean) >= 20) break;
			if(!is_string($name) || !preg_match('/^[a-z0-9_\-]{1,64}$/i', $name) || !is_scalar($value)) continue;
			$clean[$name] = mb_substr(sanitize_text_field(wp_unslash((string)$value)), 0, 2000);
		}
		if(empty($clean)) $this->ajax_response_success('');

		$page	= (string)$this->get_val($_SERVER, 'HTTP_REFERER', '');
		$host	= strtolower((string)parse_url($page, PHP_URL_HOST));
		$payload = [
			'module'	=> $this->slider->get_alias(),
			'page'		=> ($host !== '' && in_array($host, $this->environment()->site_host_list(), true)) ? esc_url_raw($page) : '',
			'fields'	=> $clean,
			'time'		=> gmdate('c'),
		];

		$context = ['module' => $this->slider->get_id(), 'slide' => (int)$slide_id, 'action' => $action_id, 'url' => $url];
		$payload = apply_filters('revslider_form_submit', $payload, $context);
		if(!is_array($payload)) $this->ajax_response_success('');

		//blocking: a webhook answers in well under the 5 s, and a request that does not wait is often cut before TLS is done
		$result = $this->http_client()->post_body($url, wp_json_encode($payload), ['safe' => true, 'timeout' => 5, 'max_redirects' => 0, 'headers' => ['Content-Type' => 'application/json']]);
		do_action('revslider_form_submitted', $payload, $context, $result);

		$this->ajax_response_success('');
	}

	/**
	 * the free modules limit is gone since 7.2.4; kept for hosts and add-ons that still ask before dispatch()
	 * @param string $module_full module.op[.subop] of the request
	 * @return bool always true
	 */
	public function check_modules_limit($module_full){
		return true;
	}

	/**
	 * Best-effort client IP used for rate limiting.
	 *
	 * Defaults to the host's client address (on WordPress REMOTE_ADDR, which cannot be spoofed at the TCP level).
	 * Sites behind a trusted reverse proxy / CDN can supply the real visitor IP (e.g. CF-Connecting-IP or
	 * a validated X-Forwarded-For entry) through the `revslider_client_ip` filter, which the WordPress host applies.
	 * Returns '' when no valid IP can be determined.
	 *
	 * @return string
	 */
	public function get_client_ip(){
		$ip = trim($this->environment()->client_address());

		return (filter_var($ip, FILTER_VALIDATE_IP) !== false) ? $ip : '';
	}

	/**
	 * central function to either fill the $_data by REST API or by ajax requests
	 *
	 * @param bool|array|WP_REST_Request $data
	 * @return array
	 **/
	public function get_data($data = false){
		if(is_a($data, 'WP_REST_Request')){ //string form on purpose: no WP class reference in core
			$_data = $data->get_params('GET');
			$this->set_rest_call();
		}else{
			$_data = $this->get_request_var('data', '', false);
		}

		return empty($_data) || !is_array($_data) ? [] : $_data;
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_stream_data($data = false){
		$data		= $this->get_data($data);
		$slider_id	= explode(',', $this->get_val($data, 'id'));
		//$lang		= $this->get_val($data, 'srlang', 'all');

		//parentheses are what PHP does anyway (&& binds tighter than ||) - spelled out so the intent is
		//readable instead of resting on operator precedence
		if(count($slider_id) > 1 || (count($slider_id) == 1 && strpos($slider_id[0], "SR7_") !== false)){
			$this->slider->set_gallery_ids($slider_id);
			$this->slider->set_param('sourcetype', 'specific_posts');
			$this->slider->set_param(['source'], []);
			$this->slider->set_param(['source', 'type'], 'specific_posts');
		}else{
			$slider_id = str_replace("SR7_", "", $slider_id[0]);
			$this->slider->init_by_id($slider_id, false);
	
			if($this->slider->inited === false){
				$this->ajax_response_error(__('Slider could not be loaded', 'revslider'));
			}

			//what the embedding shortcode asked for (post_ids, category, taxonomy): the markup was built with it,
			//and the module carries it back here as data-query, so the posts are the same ones on both ends
			$query = $this->get_val($data, 'query', '');
			if(!is_array($query)) $query = ($query === '') ? [] : (array)json_decode(stripslashes((string)$query), true);
			if(!empty($query)) $this->slider->apply_source_query($query);
		}

		$this->ajax_response_data(['data' => $this->slider->get_stream_data()]);
	}

	/** @return bool */
	public function ajax_check_allowed($call, $allowed){
		
		foreach($allowed ?? [] as $check){
			if(strpos($check, '*') === false) continue;
			if(!preg_match('/^' . str_replace('\*', '.*', preg_quote($check, '/')) . '$/', $call)) continue;

			return true;
		}

		return false;
	}

	
	/**
	 * one centralized way to clear the slider cache inside of the API
	 * @return void
	 */
	public function clear_cache($slider_id = false, $slide_id = false){
		if($slide_id !== false){
			$this->slide->init_by_id($slide_id);
			$slider_id = $this->slide->get_slider_id();
		}
		$cache = Registry::instance()->get('RevSliderCache');
		$cache->clear_transients_by_slider($slider_id);
	}

	/**
	 * get different module types and return them
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function load_module($data = false){
		$data		= $this->get_data($data);
		$sr_admin	= $this->admin_functions();
		$module		= $this->get_val($data, 'module', ['all']);
		$uid		= $this->get_val($data, 'module_uid', false);
		$refresh	= $this->_truefalse($this->get_val($data, 'refresh_from_server', false));
		$get_static	= $this->_truefalse($this->get_val($data, 'static', false));
		$page		 = $this->get_val($data, 'page', false);

		if($uid === false) $uid = $this->get_val($data, 'module_id', false);

		$this->ajax_response_data(['modules' => $sr_admin->get_full_library($module, $uid, $refresh, $get_static, $page)]);
	}

	/***********************
	 *  PAGE EFFECTS       *
	 ***********************/

	/**
	 * save a Page Effect instance to its host post meta. the effect type's own sanitize callable
	 * whitelists the data; per-post 'edit_post' capability is enforced inside RevSliderPageEffects.
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 **/
	public function save_page_effect($data = false){
		$data		= $this->get_data($data);
		$post_id	= intval($this->get_val($data, 'post_id', 0));
		$effect_id	= sanitize_key($this->get_val($data, 'effect_id', ''));
		$type		= sanitize_key($this->get_val($data, 'type', ''));
		$payload	= $this->get_val($data, 'effect', '');
		$payload	= is_string($payload) ? json_decode(wp_unslash($payload), true) : $payload;

		$return = \RevSliderPageEffects::save_effect($post_id, $effect_id, $type, $payload);
		if(is_wp_error($return)) $this->ajax_response_error($return->get_error_message());

		$this->ajax_response_data($return);
	}

	/**
	 * load a stored Page Effect instance from its host post meta.
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 **/
	public function get_page_effect($data = false){
		$data		= $this->get_data($data);
		$post_id	= intval($this->get_val($data, 'post_id', 0));
		$effect_id	= sanitize_key($this->get_val($data, 'effect_id', ''));

		$return = \RevSliderPageEffects::get_effect($post_id, $effect_id);
		if(is_wp_error($return)) $this->ajax_response_error($return->get_error_message());

		$this->ajax_response_data($return);
	}

	/**
	 * Stage unsaved Page Effect data for Preview Live (opens the real front runtime in a new tab).
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 **/
	public function preview_page_effect($data = false){
		$data		= $this->get_data($data);
		$post_id	= intval($this->get_val($data, 'post_id', 0));
		$effect_id	= sanitize_key($this->get_val($data, 'effect_id', ''));
		$type		= sanitize_key($this->get_val($data, 'type', ''));
		$payload	= $this->get_val($data, 'effect', '');
		$payload	= is_string($payload) ? json_decode(wp_unslash($payload), true) : $payload;

		$return = \RevSliderPageEffects::preview_effect($post_id, $effect_id, $type, $payload);
		if(is_wp_error($return)) $this->ajax_response_error($return->get_error_message());

		$this->ajax_response_data($return);
	}

	/***********************
	 *  SLIDER FUNCTIONS   *
	 ***********************/
	
	/** @return void */
	public function migrate_slider($data = false){
		//set v6 version to migration has started
		$data		= $this->get_data($data);
		$slider_id	= $this->get_val($data, 'id');

		$upd	 	= Registry::instance()->get('RevSliderPluginUpdateV6');
		$upd->set_v6_migration_started($slider_id);

		$this->save_slider($data, '7.0.0');
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function save_slider($data = false, $version = \RS_REVISION){
		$data		 = $this->get_data($data);
		$slider_id	 = $this->get_val($data, 'id');
		$title		 = $this->get_val($data, 'title');
		$alias		 = $this->get_val($data, 'alias');
		$slider_data = $this->get_val($data, 'settings');
		$slider_id	 = $this->slider->save_slider_v7($slider_id, $slider_data, $title, $alias, $version);

		/**
		 * @param array $data
		 * @param int   $slider_id
		 */
		do_action('revslider_api_save_slider_after', $data, $slider_id);

		$this->clear_cache($slider_id);
		
		$this->ajax_response_success(__('Slider Saved', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_slider_html(){
		$alias	= $this->get_post_var('alias');
		$usage	= $this->get_post_var('usage');
		$modal	= $this->get_post_var('modal');
		$layout = $this->get_post_var('layout');
		$offset = $this->get_post_var('offset');
		$id		= intval($this->get_post_var('id', 0));
		
		//check if $alias exists in a database, transform it to id
		if($alias !== ''){
			$sr = new Slider();
			$id = intval($sr->alias_exists($alias, true));
		}
		
		if($id <= 0) $this->ajax_response_error(__('No Data Received', 'revslider'), false);
		
		ob_start();
		try{
			$rs_output = new Output();
			$rs_output->set_ajax_loaded();
			$slider_class = $rs_output->add_slider_to_stage($id);
			$html = ob_get_contents();
		}finally{
			ob_end_clean(); //close the buffer even if rendering throws
		}
		
		$result = !empty($slider_class) && $html !== '';
		
		if(!$result) $this->ajax_response_error(__('Slider not found', 'revslider'), false);

		if($html === false) $this->ajax_response_error(__('Slider not found', 'revslider'), false);

		$return = ['data' => $html, 'waiting' => [], 'toload' => [], 'htmlid' => $rs_output->get_html_id()];
		$return = apply_filters('revslider_get_slider_html_addition', $return, $rs_output);

		$this->ajax_response_data($return);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_slider_modal_data($data = false){
		$data = $this->get_data($data);
		if($this->REST === true){
			if(isset($data['slider'])){
				if($this->_truefalse($this->get_val($data, 'alias', false)) === true){
					$data['alias'] = $data['slider'];
				}else{
					$data['id'] = $data['slider'];
				}
			}
		}

		$slider_id		= Functions::esc_attr_deep($this->get_val($data, 'id'));
		$slider_alias	= Functions::esc_attr_deep($this->get_val($data, 'alias' ));
		if($slider_alias !== ''){
			$this->slider->init_by_alias($slider_alias);
			$slider_id = $this->slider->get_id();
		}else{
			$this->slider->init_by_id($slider_id);
		}
		if($this->slider->inited === false) $this->ajax_response_error(__('Slider could not inited', 'revslider'));
		$this->slider->set_params($this->apply_brand_kit($this->slider->get_params()));

		//the same values Output::set_usage_values() prints for a modal on the page
		$obj = [
			'id'	=> $slider_id,
			'bg'	=> ($this->slider->get_param(['modal', 'cover'], true) === true) ? $this->slider->get_param(['modal', 'bg'], 'rgba(0,0,0,0.5)') : 'transparent',
			'sp'	=> $this->slider->get_param(['modal', 'sp'], 1000),
			'addons' => []
		];

		$obj = apply_filters('sr_get_slider_modal_data', $obj, $slider_id);

		$this->ajax_response_data($obj);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_full_slider_object($data = false, $modify = true){
		$g = &$this->context()->root();

		$data	= $this->get_data($data);

		if($this->REST === true){
			if(isset($data['slider'])){
				if($this->_truefalse($this->get_val($data, 'alias', false)) === true){
					$data['alias'] = $data['slider'];
				}else{
					$data['id'] = 'slider-'.$data['slider'];
				}
			}

			//the route is public, the draft tables are not: ?preview counts only for a session the preview route would admit
			if($this->_truefalse($this->get_val($data, 'preview', false)) === true && $this->preview_permission_check() === true) $g['preview_mode'] = true;
			$this->slider->set_special_table_mode();
			$this->slide->set_special_table_mode();
		}

		// Preview drafts must never be served from an HTTP/browser cache of an older response
		if(!empty($g['preview_mode'])) nocache_headers();
		
		$slide_id		= Functions::esc_attr_deep($this->get_val($data, 'id'));
		$slider_alias	= Functions::esc_attr_deep($this->get_val($data, 'alias'));
		$slide_ids		= Functions::esc_attr_deep($this->get_val($data, 'slideid', []));
		$raw			= $this->_truefalse($this->get_val($data, 'raw', false));

		$this->init_slider_by_data($slider_alias, $slide_id);
		
		if($this->slider->inited === false){
			if($g['preview_mode'] === false || $this->REST === false) $this->ajax_response_error(__('Slider init error!', 'revslider'));

			//we are in preview, do again and this time check orig
			$g['preview_mode'] = false;
			$this->slider->set_special_table_mode();
			$this->slide->set_special_table_mode();

			$this->init_slider_by_data($slider_alias, $slide_id);
			if($this->slider->inited === false) $this->ajax_response_error(__('Slider init error!', 'revslider'));
		}

		//check if an update is needed
		$upd	= new \ThemePunch\SR\Core\Admin\Upgrade();
		if(version_compare($this->slider->get_param(['settings', 'version']), $this->get_options(['update', 'latest-version'], '6.0.0'), '<')){
			$slider_id = $this->slider->get_id();
			$upd->upgrade_slider_to_latest($this->slider);
			$this->slider->init_by_id($slider_id);
		}

		if($this->current_user_can_not()){
			// update modify var if user role not enough to get full json with sources
			$modify = true;
		}

		//front-end object cache: skip the heavy get_full_slider_JSON() build on REST reads. mirrors the markup
		//cache gate in RevSlider7Output::get_from_caching() exactly - same enable flag, same supported source
		//types (social streams stay live), same per-slider general.icache on/off override, never in preview.
		//stored under the per-slider transient namespace, so the existing clear_transients_by_slider() (run on
		//every save/delete and on post changes via check_for_post_transient_deletion) busts it automatically.
		$rs_cache		= Registry::instance()->get('RevSliderCache');
		$source_type	= $this->slider->get_param(['source', 'type'], 'gallery');
		$can_cache		= ($g['preview_mode'] === false && $rs_cache->is_supported_type($source_type));
		//a module whose content follows the request itself cannot be replayed to the next one. The source type does
		//not answer that: an add-on can bind a gallery module to whatever page it is placed on
		$can_cache		= apply_filters('sr_slider_can_cache', $can_cache, $this->slider);
		$do_cache		= $this->slider->get_param(['general', 'icache'], 'default');
		$caching		= ($rs_cache->is_enabled() && $can_cache) ? true : false;
		if($do_cache === 'on' && $can_cache) $caching = true;  //per-slider force on
		if($do_cache === 'off') $caching = false;              //per-slider hard off
		$use_cache		= (($this->REST === true || $this->front_ajax === true) && $caching) ? true : false;

		$cache_key = false;
		if($use_cache){
			$cache_key	= 'revslider_slider_'.intval($this->slider->get_id()).'_object_'.md5(wp_json_encode([$slide_id, $slider_alias, $slide_ids, $raw, $modify, apply_filters('wpml_current_language', $this->environment()->locale())]));
			$cached		= $this->ttl_cache()->get($cache_key);
			if($cached !== false) $this->ajax_response_data($cached); //cache hit: identical payload, exits
		}

		$JSON = apply_filters('sr_get_full_slider_object', $this->slider->get_full_slider_JSON(false, true, $slide_ids, [], $raw, $modify), $this->slider);

		if($cache_key !== false) $this->ttl_cache()->set($cache_key, $JSON, $rs_cache->get_lifetime());

		$this->ajax_response_data($JSON);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function init_slider_by_data($slider_alias, $slide_id){
		$show_error = ! ( $this->context()->get('preview_mode') === true );

		if($slider_alias !== ''){
			$this->slider->init_by_alias($slider_alias, $show_error);
		}else{
			if(strpos($slide_id, 'slider-') !== false){
				$slider_id = str_replace('slider-', '', $slide_id);
			}else{
				$this->slide->init_by_id($slide_id);

				$slider_id = intval($this->slide->get_slider_id());
				//if(intval($slider_id) == 0) $this->ajax_response_error(__('Slider could not be found', 'revslider'));
			}
			$this->slider->init_by_id($slider_id, $show_error);
		}
	}

	/**
	 * 1. create a blank Slider
	 * 2. create a blank Slide
	 * 3. create a blank Static Slide
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 **/
	public function create_slider(){
		$slide_id		= false;
		$global_slide_id = false;
		$slider_data	= $this->slider->create_blank_slider();
		$slider_id		= $slider_data['id'];
		$slider_alias	= $slider_data['alias'];
		if($slider_id !== false){
			$slide_id = $this->slide->create_slide($slider_id); //normal slide
			$global_slide_id = $this->slide->create_slide($slider_id, '', true); //static slide
		}

		if($slide_id !== false) $this->ajax_response_data(['global_slide_id' => $global_slide_id, 'slide_id' => $slide_id, 'slider_id' => $slider_id, 'alias' => $slider_alias]);

		$this->ajax_response_error(__('Could not create Slider', 'revslider'));
	}

	/**
	 * every parameter given through $data can be used to modify slider data
	 * if data is set, it will overwrite all existing parameters
	 * allowed data:
	 * $data['params'][*]
	 * $data['settings'][*]
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 **/
	public function save_slider_advanced($data = false){
		$data		= $this->get_data($data);
		$slider_id	= $this->get_val($data, 'slider_id');
		$return		= $this->slider->save_slider_advanced($slider_id, $data);

		/**
		 * @param array $data
		 * @param int   $slider_id
		 */
		do_action('revslider_api_save_slider_advanced_after', $data, $slider_id);
		
		$this->clear_cache($slider_id);
		
		if($return) $this->ajax_response_success(__('Slider Saved', 'revslider'));
		
		$this->ajax_response_error(__('Slider not found', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function save_slider_tags($data = false){
		$data	= $this->get_data($data);
		$id		= $this->get_val($data, 'id');
		$tags	= $this->get_val($data, 'tags');

		if($this->slider->update_slider_tags($id, $tags) == true) $this->ajax_response_success(__('Tags Updated', 'revslider'));

		$this->ajax_response_error(__('Failed to Update Tags', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function save_slider_favorite($data = false){
		$data	= $this->get_data($data);
		$do		= $this->get_val($data, 'do', 'add');
		$type	= $this->get_val($data, 'type', 'slider');
		$id		= esc_attr($this->get_val($data, 'id'));

		$favorite = Registry::instance()->get('RevSliderFavorite');
		$favorite->set_favorite($do, $type, $id);

		$this->ajax_response_success(__('Favorite Changed', 'revslider'));
	}
	
	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function save_slider_modal_ids($data = false){
		$data	= $this->get_data($data);
		$map	= $this->get_val($data, 'map', []);
		if(empty($map)) $this->ajax_response_error(__('Slider Map Empty', 'revslider'));

		$slides_ids = $this->get_val($map, 'slides', []);
		$ztt        = $this->get_val($map, ['slider', 'zip_to_template'], []);
		$aliases    = $this->get_val($map, ['slider', 'aliases'], []);

		foreach($ztt ?? [] as $new){
			$rsi = new \ThemePunch\SR\Core\Admin\Import();
			$rsi->init_by_id($new);
			$rsi->update_modal_ids($ztt, $slides_ids, [], $aliases);
			$this->clear_cache($new); //slider modal ids changed - bust its cached object/markup
		}

		$this->ajax_response_data([]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function save_slider_js_css_ids($data = false){
		$data	= $this->get_data($data);
		$map	= $this->get_val($data, 'map', []);
		if(empty($map)) $this->ajax_response_data([]);

		$slider_map = [];
		foreach($map ?? [] as $m){
			$slider_ids = $this->get_val($m, 'slider', []);
			foreach($slider_ids ?? [] as $old => $new){
				$islider = new \ThemePunch\SR\Core\Admin\Import();
				$islider->init_by_id($new);
				
				$slider_map[] = $islider;
			}
		}
		
		foreach($slider_map ?? [] as $slider){
			foreach($map ?? [] as $m){
				$slider_ids = $this->get_val($m, 'slider', []);
				$slide_ids	= $this->get_val($m, 'slides', []);
				foreach($slider_ids ?? [] as $old => $new){
					$slider->update_css_and_javascript_ids($old, $new, $slide_ids);
				}
			}
			$this->clear_cache($slider->get_id()); //slider css/js ids changed - bust its cached object/markup
		}

		$this->ajax_response_data([]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function save_slider_name($data = false){
		$data		= $this->get_data($data);
		$slider_id	= $this->get_val($data, 'id');
		$new_title	= $this->get_val($data, 'title');
		$change_alias	= $this->_truefalse($this->get_val($data, 'changealias', false));

		$this->slider->init_by_id($slider_id, $new_title);
		$return = $this->slider->update_title($new_title, $change_alias);
		if($return != false){
			$this->clear_cache($slider_id); //title/alias changed - bust the slider's cached object/markup
			$this->ajax_response_success(__('Title updated', 'revslider'), $return);
		}
		
		$this->ajax_response_error(__('Failed to update Title', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function delete_slider($data = false){
		$data	= $this->get_data($data);
		$id		= (array)$this->get_val($data, 'id');
		$moved	= 0;
		//Keep Deleted Modules off means what deleting always did: gone on the spot
		$keep	= $this->_truefalse($this->get_val($this->get_global_settings(), 'trashKeep', true));
		foreach($id ?? [] as $_id){
			$_id = intval($_id);
			if($_id === 0) continue;
			$_slider = new Slider();
			$_slider->init_by_id($_id);
			//A module goes to the trash and can be fetched back. A folder is deleted outright: it holds its
			//children by id, so a folder out of sight would take modules with it that nobody threw away
			if($keep && $_slider->trash_slider()) $moved++;
			else $_slider->delete_slider();
			$this->clear_cache($_id); //drop any cached object/markup for the removed slider
		}

		$this->ajax_response_success($moved > 0 ? __('Moved to the trash', 'revslider') : __('Slider/Folder successfully deleted', 'revslider'));
	}


	/**
	 * put modules back where they were
	 * @since 7.2.0
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 **/
	public function restore_slider($data = false){
		$data = $this->get_data($data);
		foreach((array)$this->get_val($data, 'id') ?? [] as $_id){
			$_id = intval($_id);
			if($_id === 0) continue;
			$_slider = new Slider();
			$_slider->init_by_id($_id);
			$_slider->restore_slider();
			$this->clear_cache($_id);
		}

		$this->ajax_response_success(__('Restored', 'revslider'));
	}


	/**
	 * delete for good, from the trash. This is the existing delete, hooks and all
	 * @since 7.2.0
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 **/
	public function destroy_slider($data = false){
		$data = $this->get_data($data);
		foreach((array)$this->get_val($data, 'id') ?? [] as $_id){
			$_id = intval($_id);
			if($_id === 0) continue;
			$_slider = new Slider();
			$_slider->init_by_id($_id);
			//only out of the trash: the same call from the overview would delete a live module with no way back
			if(!$_slider->is_trashed()) continue;
			$_slider->delete_slider();
			$this->clear_cache($_id);
		}

		$this->ajax_response_success(__('Deleted', 'revslider'));
	}


	/**
	 * what is in the trash, for the dialog: a title, when it went, and how many days are left
	 * @since 7.2.0
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 **/
	public function get_trash_list($data = false){
		$slider	= new Slider();
		$days	= intval($this->get_val($this->get_global_settings(), 'trashDays', 30));
		$rows	= [];

		foreach($slider->get_trashed_sliders() as $row){
			$settings	= json_decode($this->get_val($row, 'settings'), true);
			$trashed	= intval($this->get_val($settings, 'trashed', 0));
			$rows[]		= [
				'id'		=> intval($this->get_val($row, 'id')),
				'title'		=> $this->get_val($row, 'title'),
				'alias'		=> $this->get_val($row, 'alias'),
				'trashed'	=> $trashed,
				//negative would read as overdue; the sweep runs when the overview opens, so it is simply due
				'left'		=> ($days < 1 || $trashed < 1) ? -1 : max(0, $days - (int)floor((time() - $trashed) / DAY_IN_SECONDS))
			];
		}

		$this->ajax_response_data(['modules' => $rows, 'days' => $days]);
	}


	/**
	 * empty the trash, whatever the retention time says
	 * @since 7.2.0
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 **/
	public function empty_trash($data = false){
		$slider = new Slider();
		$gone	= 0;
		foreach($slider->get_trashed_sliders() as $row){
			$_id = intval($this->get_val($row, 'id'));
			if($_id === 0) continue;
			$_slider = new Slider();
			$_slider->init_by_id($_id);
			if(!$_slider->is_trashed()) continue;
			$_slider->delete_slider();
			$this->clear_cache($_id);
			$gone++;
		}

		$this->ajax_response_success(sprintf(_n('%d module deleted', '%d modules deleted', $gone, 'revslider'), $gone));
	}

	/** @return array|string the module data, or an error message */
	protected function do_get_module($data = false){
		$data			= $this->get_data($data);
		$slider_id		= $this->get_val($data, 'slider_id', false);
		$slide_id		= $this->get_val($data, 'slide_id', false);
		$slider_alias	= $this->get_val($data, 'alias');
		$slider_alias	= Functions::esc_attr_deep($slider_alias);

		if($slider_id !== false){ //moved to check by slider_id first
			$this->slider->init_by_id($slider_id);
		}else{
			if($slider_alias !== ''){
				$this->slider->init_by_alias($slider_alias);
				if($this->slider->inited === false) return __('Slider could not be loaded', 'revslider');

				$slider_id = $this->slider->get_id();
			}elseif($slide_id !== false){
				$this->slide->init_by_id($slide_id);
				if(!$this->slide->inited === true){
					return __('Slide could not be found', 'revslider');
				}else{
					$slider_id = $this->slide->get_slider_id();
				}
			}
			if($slider_id !== false) $this->slider->init_by_id($slider_id);
		}

		if($this->slider->inited === false) return __('Slider could not be loaded', 'revslider');

		$slider_id = $this->slider->get_id();
		$this->slide->ensure_static_slide($slider_id);
		$slides = $this->slide->get_slide_ids_by_slider_id($slider_id);

		//check if an update is needed
		$upd	= new \ThemePunch\SR\Core\Admin\Upgrade();
		if(version_compare($this->slider->get_param(['settings', 'version']), $this->get_options(['update', 'latest-version'], '6.0.0'), '<')){
			$slider_id = $this->slider->get_id();
			$upd->upgrade_slider_to_latest($this->slider);
			$this->slider->init_by_id($slider_id);
		}

		$return = [
			'id'		=> $slider_id,
			'title'		=> $this->slider->get_title(),
			'alias'		=> $this->slider->get_alias(),
			'settings'	=> $this->slider->get_params(),
			'slides'	=> $slides,
			'auto'		=> Registry::instance()->get('ThemePunch\SR\Core\Thumbs')->src_for($this->slider) ?: '' //the overview's automatic picture, for the builders' card
		];

		if($this->current_user_can_not()){
			// modify sources if user role not enough to get full json with sources
			$return = $this->slider->modify_slider_data($return);
		}
		
		return $return;
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_module($data = false){
		$result = $this->do_get_module($data);
		if (!is_array($result)) $this->ajax_response_error($result);
		$this->ajax_response_data($result);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_full_slider_object_v6($data = false){
		$data			= $this->get_data($data);
		$slide_id		= $this->get_val($data, 'id');
		$slide_id		= Functions::esc_attr_deep($slide_id);
		$slider_alias	= $this->get_val($data, 'alias');
		$slider_alias	= Functions::esc_attr_deep($slider_alias);

		if($slider_alias !== ''){
			$this->slider->init_by_alias($slider_alias);
			$slider_id = $this->slider->get_id();
		}else{
			if(strpos($slide_id, 'slider-') !== false){
				$slider_id = str_replace('slider-', '', $slide_id);
			}else{
				$this->slide->init_by_id($slide_id);

				$slider_id = $this->slide->get_slider_id();
				if(intval($slider_id) == 0) $this->ajax_response_error(__('Slider could not be loaded', 'revslider'));
			}

			$this->slider->init_by_id($slider_id);
		}
		if($this->slider->inited === false) $this->ajax_response_error(__('Slider could not be loaded', 'revslider'));
		
		//check if an update is needed
		$upd = new \ThemePunch\SR\Core\Admin\Upgrade();
		if(version_compare($this->slider->get_param(['settings', 'version']), $this->get_options(['update', 'latest-version'], '6.0.0'), '<')){
			$slider_id = $this->slider->get_id();
			$upd->upgrade_slider_to_latest($this->slider);
			$this->slider->init_by_id($slider_id);
		}
		
		//create static Slide if the Slider not yet has one
		$static_slide_id = $this->slide->get_static_slide_id($slider_id);
		$static_slide_id = (intval($static_slide_id) === 0) ? $this->slide->create_slide($slider_id, '', true) : $static_slide_id;
		
		$static_slide = false;
		if(intval($static_slide_id) > 0){
			$static_slide = new Slide();
			$static_slide->init_by_static_id($static_slide_id);
		}
		
		$slides	 = $this->slider->get_slides(false, true);
		$_slides = [];
		$_static_slide = [];

		foreach($slides ?? [] as $s){
			$_slides[] = [
				'order' => $s->get_order(),
				'params' => $s->get_params(),
				'layers' => $s->get_layers(),
				'id' => $s->get_id(),
			];
		}

		if(!empty($static_slide)){
			$_static_slide = [
				'params' => $static_slide->get_params(),
				'layers' => $static_slide->get_layers(),
				'id' => $static_slide->get_id(),
			];
		}
		
		$obj = [
			'id'				=> $slider_id,
			'alias'				=> $this->slider->get_alias(),
			'title'				=> $this->slider->get_title(),
			'slider_params' 	=> $this->slider->get_params(),
			'slider_settings'	=> $this->slider->get_settings(),
			'slides'			=> $_slides,
			'static_slide'		=> $_static_slide,
		];
		
		$uid = $this->get_val($obj, ['slider_params', 'uid']);
		if(empty($uid)) $this->ajax_response_data($obj);

		$templates		= new Template();
		$rslb			= Registry::instance()->get('RevSliderLoadBalancer');
		$temp_url		= $rslb->get_url('templates', 0).'/'.$templates->templates_server_path;
		$defaults		= $this->get_addition(['guide']);
		$template_data	= $templates->get_tp_template_sliders($uid);
		
		foreach($template_data ?? [] as $_data){
			$title			= $this->get_val($_data, 'guide_title');
			$url			= $this->get_val($_data, 'guide_url');
			$img			= $this->get_val($_data, 'guide_img');
			$template_img	= $this->get_val($_data, 'img');
			$obj['guide'] = [
				'title'			=> (empty($title)) ? $this->get_val($defaults, 'title') : $title,
				'url'			=> (empty($url)) ? $this->get_val($defaults, 'url') : $url,
				'img'			=> (empty($img)) ? $this->get_val($defaults, 'img') : $temp_url.'/'.$img,
				'template_img'	=> (empty($template_img)) ? $this->get_val($defaults, 'img') : $template_img,
				'template_title'=> $this->get_val($_data, 'title'),
			];

			break;
		}

		$this->ajax_response_data($obj);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_slider_alias($data = false){
		$data		= $this->get_data($data);
		$id			= intval($this->get_val($data, 'id', 0));
		$_alias		= $this->get_val($data, 'alias');
		$alias  	= sanitize_title($_alias);
		$temp		= $alias;
		$modified	= false; //($alias !== $_alias) ? true : false;
		$return		= ($id > 0) ? true : false;

		if($return){
			$_id = $this->slider->alias_exists($alias, $return);
			if($_id === $id || $_id === false) $this->ajax_response_data(['alias' => $alias, 'modified' => $modified]);
		}

		$ti = 1;
		while($this->slider->alias_exists($alias)){ //set a new alias and title if its existing in database
			$modified = true;
			$alias = sanitize_title($temp . ' ' . $ti);
			$ti++;
		}

		$this->ajax_response_data(['alias' => $alias, 'modified' => $modified]);
	}
	
	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_slider_image($data = false){
		// Available Sliders
		$arrSliders = $this->slider->get_sliders();

		// Given Alias
		$alias = $this->get_val($data, 'alias');
		$return = array_search($alias, $arrSliders);

		foreach($arrSliders ?? [] as $sliderony){
			if($sliderony->get_alias() != $alias) continue;

			$sf		 = $sliderony->get_overview_data();
			$return	 = $this->get_val($sf, ['bg', 'src']);
			$title	 = $this->get_val($sf, 'title');
			$premium = $this->get_val($sf, 'premium');
			break;
		}

		if(!$return) $return = '';

		if( !empty($title) ) $this->ajax_response_data([ 'image' => $return, 'title' => $title, 'premium' => $premium]);
		
		$this->ajax_response_error(__('The Slider with the alias "' . esc_attr($alias) . '" is not available!', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function slider_get_overview_data($data){
		$data	= $this->get_data($data);
		$id		= $this->get_val($data, 'id');
		$migrated = $this->_truefalse($this->get_val($data, 'migrated', false));
		$slider = new Slider();
		$slider->init_by_id($id);
		if($migrated){
			$upd = Registry::instance()->get('RevSliderPluginUpdateV6');
			$upd->set_migrated_without($id, (array)$this->get_val($data, 'without', []));
			$upd->set_v6_migration_finished($id);
		}

		$this->ajax_response_data(['slider' => $slider->get_overview_data()]);
	}

	/**
	 * Every module as plain arrays, for a builder integration working client-side.
	 *
	 * One line of substance on purpose: the shape is BuilderApi::modules() and nothing here may
	 * add to it, or SR7.Block.modules() and its PHP twin stop agreeing.
	 *
	 * @param mixed $data include, filter, search, orderby - see BuilderApi::modules()
	 * @return void the response is sent by ajax_response_data(), which ends the request
	 */
	public function builder_module_list($data){
		$data = $this->get_data($data);

		$this->ajax_response_data(['modules' => BuilderApi::modules([
			'include'	=> (array)$this->get_val($data, 'include', []),
			'filter'	=> (string)$this->get_val($data, 'filter', 'all'),
			'search'	=> (string)$this->get_val($data, 'search', ''),
			'orderby'	=> (string)$this->get_val($data, 'orderby', 'title')
		])]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_slider_layout($data = false){
		$data		= $this->get_data($data);
		$arrSliders = $this->slider->get_sliders();
		$alias		= $this->get_val($data, 'alias');
		$return 	= array_search($alias, $arrSliders);
		$title		= '';

		foreach($arrSliders ?? [] as $sliderony){
			if($sliderony->get_alias() != $alias) continue;

			$sf		= $sliderony->get_overview_data();
			$return	= $this->get_val($sf, 'size');
			$title	= $this->get_val($sf, 'title');
			break;
		}
		
		$this->ajax_response_data(['layout' => $return, 'title' => $title]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function slider_upgrade(){
		$upd	= new \ThemePunch\SR\Core\Admin\Upgrade();
		$return = $upd->upgrade_next_slider();
		
		$this->ajax_response_data($return);
	}

	/**
	 * Upgrade old V6 sliders to latest V6 state, to then migrate them to V7
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 **/
	public function slider_upgrade_v6($data = false){
		$data	= $this->get_data($data);
		$id		= $this->get_val($data, 'id');
		//the migrated module JSON can grow past the server/WAF response-body limit (commonly 2MB) and get rejected with a 404.
		//when the client signals it can inflate, pack large payloads as gzip+base64 to stay well under that ceiling.
		$this->compress_response = (bool)$this->get_val($data, 'compress', false) && function_exists('gzencode');
		$upd	= Registry::instance()->get('RevSliderPluginUpdateV6');
		$return = $upd->upgrade_next_slider_v6($id);
		if($return === false) $this->ajax_response_error('Migration failed, module older than v6', 'revslider');

		$this->ajax_response_data($return);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function slider_duplicate($data = false){
		$data	= $this->get_data($data);
		$id		= $this->get_val($data, 'id');
		$new_id = $this->slider->duplicate_slider_by_id($id);
		if(intval($new_id) === 0) $this->ajax_response_error(__('Duplication Failed', 'revslider'));

		$new_slider = new Slider();
		$new_slider->init_by_id($new_id);
		$this->ajax_response_data(['slider' => $new_slider->get_overview_data()]);
	}
	
	/** @return void the response is sent by ajax_response_*(), which ends the request */
	/**
	 * the module overview asks for a module's picture from the render service (Cloud Thumbnails)
	 * @since 7.2.3
	 */
	public function slider_thumb_auto($data = false){
		$data	= $this->get_data($data);
		$result	= Registry::instance()->get('ThemePunch\SR\Core\Thumbs')->shot(intval($this->get_val($data, 'id', 0)));
		if(isset($result['error'])) $this->ajax_response_error($result['error'], ['kind' => $result['kind']]);
		$this->ajax_response_data(['src' => $result['src']]);
	}

	public function slider_optimize($data = false){
		$data = $this->get_data($data);
		$ref = $this->get_val($data, 'ref');
		$suffix = $this->get_val($data, 'suffix');
		$src = $this->get_val($data, 'src');
		$r = $this->get_val($data, 'r', []);
		$itemid = $this->get_val($data, 'itemid');

		$slider = new Slider();
		$slider->init_by_id( $itemid );
		if ( $slider->inited === false ) {
			$this->ajax_response_error( __( 'Module could not be loaded', 'revslider' ) );
		}
		
		$slide  = null;
		// only init slide for _sthumbs
		if ('_sthumb' == $suffix) {
			$slide = new Slide();
			$slide->init_by_id( $ref );
			if ( $slide->inited === false ) {
				$this->ajax_response_error( __( 'Slide could not be loaded', 'revslider' ) );
			}
		}

		/* @var RevSliderOptimizer $o */
		$o        = Registry::instance()->get('RevSliderOptimizer');
		$o_result = $o->optimize_webp(
			$src, 
			$itemid, 
			$r, 
			$o->get_dest_thumb_file($src, $slider->alias, $suffix)
		);
		if ( empty( $o_result['url'] ) ) {
			$this->ajax_response_error( __( 'Optimization return empty URL', 'revslider' ) );
		}

		// update module thumb
		if ('_thumb' == $suffix) {
			$save_data = [ 'params' => [ 'thumb' => $o_result['url'] ] ];
			if ( ! $slider->save_slider_advanced($itemid, $save_data) ) {
				$this->ajax_response_error( __( 'Module could not be saved', 'revslider' ) );
			}
		}

		// update slide thumb
		if ('_sthumb' == $suffix) {
			$save_data = ['params' => ['thumb' => ['admin' => $o_result['url']]]];
			if ( ! $slide->save_slide_advanced($ref, $save_data, $itemid) ) {
				$this->ajax_response_error( __( 'Slide could not be saved', 'revslider' ) );
			}
		}

		$data['src'] = $o_result['url'];
		$this->ajax_response_data($data);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function slider_import($data = false){
		$data	= $this->get_data($data);
		$import = new \ThemePunch\SR\Core\Admin\Import();
		$return = $import->import_slider();

		if($this->get_val($return, 'success') == true){
			$new_id = $this->get_val($return, 'sliderID');
			$map = $this->get_val($return, 'map',  []);

			if(intval($new_id) > 0){
				$folder = new Folder();
				$folder_id = $this->get_val($data, 'folderid', -1);
				if(intval($folder_id) > 0) $folder->add_slider_to_folder($new_id, $folder_id, false);

				if($this->get_val($return, 'v6', false) !== false){
					$return['hiddensliderid'] = $new_id;
					$this->ajax_response_data($return);
				}

				$new_slider = new Slider();
				$new_slider->init_by_id($new_id);
				$this->ajax_response_data(['slider' => $new_slider->get_overview_data(), 'map' => $map, 'hiddensliderid' => $new_id]);
			}
		}

		$error = ($this->get_val($return, 'error') !== '') ? $this->get_val($return, 'error') : __('Slider Import Failed', 'revslider');

		$this->ajax_response_error($error);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function slider_export(){
		$export = new Export();
		$data      = $this->get_request_var('data', '', []);
		$id      = $this->get_val($data, 'id');
		//$id		= intval($this->get_request_var('id'));
		$export->export_slider($id);

		//will never be called if all is good
		$this->ajax_response_error(__('Slider Export Error!!!', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function slider_export_html(){
		$export = new ExportHtml();
		$data      = $this->get_request_var('data', '', []);
		$id      = $this->get_val($data, 'id');
		//$id		= intval($this->get_request_var('id'));
		$export->export_slider_html($id);

		//will never be called if all is good
		$this->ajax_response_error(__('Slider HTML Export Error!!!', 'revslider'));
	}


	/***********************
	 *   SLIDE FUNCTIONS   *
	 ***********************/
	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function migrate_slide($data = false){
		$data	= $this->get_data($data);
		$slide	= $this->get_val($data, 'slides');
		$slide	= $this->json_decode_slashes($slide);
		$slider_id	= $this->get_val($data, 'id');
		$id = $this->get_val($slide, ['slide', 'id'], false);
		if($id === false || intval($id) === 0) $this->ajax_response_error(__('Slide could not be saved as a V7 Slide', 'revslider'));

		$return	= $this->slide->save_slide_v7($id, $slide, $slider_id, '7.0.0');

		$upd = Registry::instance()->get('RevSliderPluginUpdateV6');
		$v6_slide_id = $upd->get_v6_slide_by_v7_id($id);
		if($v6_slide_id !== false) $upd->update_post_slide_template_v7($v6_slide_id);

		do_action('sr_api_save_slide', $return, $slider_id);

		$this->clear_cache($slider_id);

		if($return) $this->ajax_response_success(__('Slide Saved', 'revslider'));

		$this->ajax_response_error(__('Slide not found', 'revslider'));
	}


	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function save_slide($data = false){
		$data		= $this->get_data($data);
		$id			= $this->get_val($data, 'id');
		$slider_id	= $this->get_val($data, 'slider_id');
		$slide		= [
			'slide' => $this->json_decode_slashes($this->get_val($data, 'settings')),
			'layers' => $this->json_decode_slashes($this->get_val($data, 'layers')),
		];

		
		$return		= $this->slide->save_slide_v7($id, $slide, $slider_id);

		// In preview mode, ensure the preview table row belongs to the current slider and has the correct static flag.
		// Some environments can have stale preview rows with the same slide id but wrong slider_id/static.
		if(!empty($this->context()->get('preview_mode'))){
			try {
				$static_id = 0;
				// get_static_slide_id exists on RevSliderSlide
				if(method_exists($this->slide, 'get_static_slide_id')){
					$static_id = (int)$this->slide->get_static_slide_id((int)$slider_id);
				}
				$is_static = ((int)$id > 0 && (int)$id === (int)$static_id) ? 1 : 0;
				$tbl = $this->database()->prefix() . $this->slide->table_slides;
				$this->database()->update($tbl, ['slider_id' => (int)$slider_id, 'static' => $is_static], ['id' => (int)$id]);
			} catch (\Throwable $e) {
				// do not break saving if this fails
			}
		}

		do_action('sr_api_save_slide', $return, $slider_id);

		$this->clear_cache($slider_id);

		if($return) $this->ajax_response_success(__('Slide Saved', 'revslider'));

		$this->ajax_response_error(__('Slide not found', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function slide_create($data = false){
		$data		= $this->get_data($data);
		$slider_id	= $this->get_val($data, 'slider_id', false);
		$amount		= intval($this->get_val($data, 'amount', 1));
		$slide_ids	= [];

		if(intval($slider_id) > 0 && ($amount > 0 && $amount < 50)){
			for($i = 0; $i < $amount; $i++){
				$slide_ids[] = $this->slide->create_slide($slider_id);
			}
			//delete again as we only create them to reserve the db id
			foreach($slide_ids as $slide_id){
				$this->clear_cache(false, $slide_id);
				$this->slide->delete_slide_by_id($slide_id);
				
			}
		}

		if(!empty($slide_ids)) $this->ajax_response_data(['slide_id' => $slide_ids]);
		
		$this->ajax_response_error(__('Could not create Slide', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function save_slide_advanced($data = false){
		$data		= $this->get_data($data);
		$slide_id	= $this->get_val($data, 'slide_id');
		$slider_id	= $this->get_val($data, 'slider_id');
		$return		= $this->slide->save_slide_advanced($slide_id, $data, $slider_id);
		
		$this->clear_cache($slider_id);
		
		if($return) $this->ajax_response_success(__('Slide Saved', 'revslider'));
		
		$this->ajax_response_error(__('Slide not found', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function save_slide_order($data = false){
		$data		= $this->get_data($data);
		$slide_ids	= $this->get_val($data, 'slide_ids', []);
		
		if(empty($slide_ids)) $this->ajax_response_error(__('Slide order could not be changed', 'revslider'));

		foreach($slide_ids ?? [] as $order => $id){
			$order++;
			$this->slide->init_by_id($id);
			if($this->slide->inited === false) continue;
			$this->slide->change_slide_order($id, $order, true);
		}
		
		$this->clear_cache($this->slide->get_slider_id());
		
		$this->ajax_response_success(__('Slide order changed', 'revslider'));
	}

	/** @return array|string the slides, or an error message */
	protected function do_get_slides($data = false){
		$data		= $this->get_data($data);
		$slider_id	= $this->get_val($data, 'slider_id', false);
		$slide_id	= (array)$this->get_val($data, 'slide_id', []);
		$layers		= $this->_truefalse($this->get_val($data, 'layers', false));
		$slides		= [];

		if($slider_id !== false){
			//skip re-initialising when the slider is already loaded for this id (editor_get_all() runs
			//do_get_module() first, which already init'd the same slider) - avoids a duplicate slider-row query
			if($this->slider->inited === false || (string)$this->slider->get_id() !== (string)$slider_id) $this->slider->init_by_id($slider_id);
			if($this->slider->inited === false) return __('Slides could not be loaded', 'revslider');
			$slides = $this->slider->get_slides_raw($layers);
		}elseif(!empty($slide_id)){
			$slides = $this->slider->get_slides_by_slide_ids_raw($slide_id, $layers);
		}else{
			return __('Slides could not be loaded', 'revslider');
		}

		return $slides;
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_slides($data = false){
		$result = $this->do_get_slides($data);
		if (!is_array($result)) $this->ajax_response_error($result);
		$this->ajax_response_data(['slides' => $result]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_slide_by_slider_id($data = false){
		$data	 = $this->get_data($data);
		$sid	 = intval($this->get_val($data, 'id'));
		$slides	 = [];
		$_slides = $this->slide->get_slides_by_slider_id($sid);
		
		foreach($_slides ?? [] as $_slide){
			$slides[] = $_slide->get_overview_data();
		}
		
		$this->ajax_response_data(['slides' => $slides]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_slide_layers($data = false){
		$data		= $this->get_data($data);
		$slide_id	= $this->get_val($data, 'slide_id');

		$this->slide->init_by_id($slide_id);
		$layers = $this->slide->get_layers();

		$this->ajax_response_data(['layers' => $layers]);
	}

	/**
	 * Limited-privilege Quick Edit: require edit_post on host post unless user has RevSlider role.
	 * @since 7.0
	 * @param bool $write a save or a slide operation; reading still follows the post's embeds
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	private function quickedit_check_limited_access($data, $write = true){
		if(!$this->current_user_can_not()) return;

		$post_id = intval($this->get_val($data, 'post_id', 0));
		if($post_id <= 0) $post_id = intval($this->get_val($data, 'owner_post_id', 0));
		$slider_id = intval($this->get_val($data, 'slider_id', 0));

		//edit_post alone let a contributor with one draft name any post_id and rewrite any module. A module is shared, so a
		//post that only names it is no ownership: writing takes the module imported for this post, or edit_others_posts
		$owned_only = $write && !$this->environment()->user_can('edit_others_posts');
		if($post_id > 0 && $this->environment()->user_can('edit_post', $post_id) && $this->post_embeds_slider($post_id, $slider_id, $owned_only)) return;

		$this->ajax_response_error(__('You do not have permission to edit this module', 'revslider'));
	}

	/**
	 * a limited user reaches only the modules of the post: the block, a shortcode, a builder's meta,
	 * or a module the block imported for this post (sr7_gb_owner_post_id)
	 * @since 7.2.0
	 * @param int $post_id
	 * @param int $slider_id
	 * @param bool $owned_only only the module imported for this post counts
	 * @return bool
	 */
	private function post_embeds_slider($post_id, $slider_id, $owned_only = false){
		if($slider_id <= 0) return false;
		$slider = new Slider();
		$slider->init_by_id($slider_id, false);
		if($slider->inited === false) return false;
		if(intval($slider->get_param('sr7_gb_owner_post_id', 0)) === $post_id) return true;
		if($owned_only) return false;

		$hay = $this->create_content_source()->entry_text($post_id);
		if($hay === '') return false;
		$hay = str_replace('\\', '', $hay); //a builder keeps its shortcode json encoded in meta, quotes escaped
		$alias = (string)$slider->get_alias();
		$needles = ['"moduleId":"'.$slider_id.'"', 'id="'.$slider_id.'"', "id='".$slider_id."'", 'id='.$slider_id.']', 'id='.$slider_id.' '];
		if($alias !== '') array_push($needles, 'alias="'.$alias.'"', "alias='".$alias."'", 'alias='.$alias.']', 'alias='.$alias.' ', '"alias":"'.$alias.'"');
		foreach($needles as $n) if(stripos($hay, $n) !== false) return true;

		return false;
	}

	/**
	 * Quick Edit writes one picture for every device: a per device set (srcs) that no longer stands for it goes, as SR7.F.setImgSrc does live
	 * @since 7.2.2
	 * @param array $img the bg.image array of a layer
	 * @param string $url the new src
	 * @return array
	 */
	private function quickedit_set_img_src($img, $url){
		if(!is_array($img)) $img = [];
		if(is_array($this->get_val($img, 'srcs', false)) && $this->get_biggest_device_setting_v7($img['srcs'], ['ld' => true, 'd' => true, 'n' => true, 't' => true, 'm' => true], '') !== $url) unset($img['srcs']);
		$img['src'] = $url;
		return $img;
	}

	/**
	 * Sanitize layer trees written via Quick Edit bulk save (slide_layers) without touching main editor saves.
	 * Only layers the slide has, and of each only the keys Quick Edit edits; type, subtype, shape and all else stay as stored
	 * @since 7.0
	 * @return array
	 */
	private function sanitize_quickedit_slide_layers($incoming, $existing = []){
		if(!is_array($incoming)) return is_array($existing) ? $existing : [];

		$existing = is_array($existing) ? $existing : [];
		$out = [];
		//what Quick Edit writes into a layer: media, text style (tag goes through filter_single_layer_tags on output), Bulk Style
		//Editor and colour replace, visibility, effects and what their presets set (tl, pos, pE, sel), AI marks, brand links.
		//A key missing here is not saved from Quick Edit
		$keys = ['bg', 'color', 'font', 'lh', 'tA', 'tag', 'm', 'p', 'border', 'radius', 'hov', 'tStroke', 'glow', 'vis', 'addOns', 'tl', 'pos', 'pE', 'sel', 'aigen', 'brand'];

		foreach($incoming as $lid => $layer){
			$lid = (string)$lid;
			//Quick Edit never adds a layer, and a new id would take its type and shape from the request
			if(!isset($existing[$lid]) || !is_array($existing[$lid]) || !is_array($layer)) continue;

			$base = $existing[$lid];
			$merged = $base;
			foreach($keys as $k){
				if(!array_key_exists($k, $layer)) continue;
				$v = $this->quickedit_clean($layer[$k], $base[$k] ?? null);
				if(is_array($base[$k] ?? null) && !is_array($v) && $v !== null) continue;
				$merged[$k] = (is_array($v) && is_array($base[$k] ?? null)) ? array_replace_recursive($base[$k], $v) : $v;
			}

			//of the content only its text, file and text style; only what this save changed is cleaned, an inline svg in an untouched text vanished on a font change
			$content = $this->get_val($layer, 'content');
			if(is_array($content)){
				$merged['content'] = is_array($base['content'] ?? null) ? $base['content'] : [];
				foreach(['trans', 'deco'] as $k) if(array_key_exists($k, $content)) $merged['content'][$k] = $this->quickedit_clean($content[$k], $merged['content'][$k] ?? null);
				$text = $this->get_val($content, 'text');
				if(is_string($text) && $text !== $this->get_val($base, ['content', 'text'])) $merged['content']['text'] = wp_kses_post($text);
				$src = $this->get_val($content, 'src');
				if(is_string($src) && $src !== $this->get_val($base, ['content', 'src'])) $merged['content']['src'] = esc_url_raw($src);
			}

			$out[$lid] = $this->sanitize_quickedit_layer_media($merged);
		}

		// Keep DB layers omitted from the client payload (defensive — QE should send full tree).
		foreach($existing as $lid => $layer){
			$lid = (string)$lid;
			if($lid === '' || isset($out[$lid]) || !is_array($layer)) continue;
			$out[$lid] = $layer;
		}

		return $out;
	}

	/**
	 * @since 7.0
	 * @return array
	 */
	private function sanitize_quickedit_layer_media($layer){
		if(!is_array($layer)) return $layer;

		if(isset($layer['bg']) && is_array($layer['bg'])){
			if(isset($layer['bg']['image']) && is_array($layer['bg']['image']) && isset($layer['bg']['image']['src']) && is_string($layer['bg']['image']['src'])){
				$layer['bg']['image']['src'] = esc_url_raw($layer['bg']['image']['src']);
			}
			//the per device sources beside it. '#a' means "same as the level above" and is not a url, so it is
			//left as it is rather than run through esc_url_raw, which would empty it
			if(isset($layer['bg']['image']['srcs']) && is_array($layer['bg']['image']['srcs'])){
				foreach($layer['bg']['image']['srcs'] as $k => $s){
					if(is_string($s) && $s !== '' && $s !== '#a') $layer['bg']['image']['srcs'][$k] = esc_url_raw($s);
				}
			}
			if(isset($layer['bg']['video']) && is_array($layer['bg']['video'])){
				if(isset($layer['bg']['video']['src']) && is_string($layer['bg']['video']['src'])){
					$layer['bg']['video']['src'] = esc_url_raw($layer['bg']['video']['src']);
				}
				if(isset($layer['bg']['video']['poster']) && is_array($layer['bg']['video']['poster']) && isset($layer['bg']['video']['poster']['src']) && is_string($layer['bg']['video']['poster']['src'])){
					$layer['bg']['video']['poster']['src'] = esc_url_raw($layer['bg']['video']['poster']['src']);
				}
				if(isset($layer['bg']['video']['type']) && is_string($layer['bg']['video']['type'])){
					$layer['bg']['video']['type'] = sanitize_key($layer['bg']['video']['type']);
				}
			}
			if(isset($layer['bg']['type']) && is_string($layer['bg']['type'])){
				$layer['bg']['type'] = sanitize_key($layer['bg']['type']);
			}
			if(isset($layer['bg']['color'])){
				if(is_string($layer['bg']['color'])){
					$layer['bg']['color'] = sanitize_text_field($layer['bg']['color']);
				}elseif(is_array($layer['bg']['color']) && isset($layer['bg']['color']['string']) && is_string($layer['bg']['color']['string'])){
					$layer['bg']['color']['string'] = sanitize_text_field($layer['bg']['color']['string']);
				}
			}
		}

		return $layer;
	}

	/**
	 * a value a Quick Edit save sent, cleaned where it changed: markup through wp_kses_post, a script url emptied, a new key
	 * that could break out of a quoted name dropped. quickedit_merge_block strips every tag, a layer's add-on texts may carry markup
	 * @since 7.2.8
	 * @param mixed $v the value sent
	 * @param mixed $was the value stored there
	 * @return mixed
	 */
	private function quickedit_clean($v, $was = null, $depth = 0){
		if(is_array($v)){
			if($depth > 8) return is_array($was) ? $was : [];
			$out = [];
			foreach($v as $k => $x){
				$old = is_array($was) && array_key_exists($k, $was);
				if(!$old && !is_int($k) && preg_match('/[<>"\'&\\\\]/', (string)$k)) continue;
				$out[$k] = $this->quickedit_clean($x, $old ? $was[$k] : null, $depth + 1);
			}
			return $out;
		}
		if(!is_string($v)) return (is_bool($v) || is_int($v) || is_float($v)) ? $v : null;
		if($v === $was) return $v;
		if(strpos($v, '<') !== false) $v = wp_kses_post($v);
		return preg_match('/^(javascript|vbscript|data:(?!image\/))/i', preg_replace('/[\x00-\x20]+/', '', $v)) ? '' : $v;
	}

	/**
	 * Gutenberg quick-editor: module slide/layer tree (raw data for client classifier).
	 * @since 7.0
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function quickedit_get($data = false){
		$data = $this->get_data($data);
		$slider_id = intval($this->get_val($data, 'slider_id'));
		if($slider_id <= 0) $this->ajax_response_error(__('Bad Request', 'revslider'));

		$this->quickedit_check_limited_access($data, false);

		$this->slider->init_by_id($slider_id);
		if($this->slider->inited === false) $this->ajax_response_error(__('Slider could not be loaded', 'revslider'));

		$slides = $this->slider->get_slides_raw(true);
		$static_id = intval($this->slide->get_static_slide_id($slider_id));

		// Per-slide DB rows can have empty `layers` when content is on the global/static slide — hydrate via slide object
		foreach($slides as $slide_id => &$slide){
			if(!is_array($slide['layers'] ?? null)) $slide['layers'] = [];
			if(!empty($slide['layers'])) continue;
			$this->slide->init_by_id($slide_id);
			if($this->slide->inited === false) continue;
			$layers = $this->slide->get_layers();
			if(is_array($layers) && !empty($layers)) $slide['layers'] = $layers;
		}
		unset($slide);

		//layer meta: rows keyed by the template a copy came from, the lexicon only for those who may tag
		$slots = Registry::instance()->get('RevSliderSlots');
		$template_uid = $slots->template_uid_of($this->slider);
		$index_map = $slots->slide_index_map($slides);
		foreach($index_map as $sid => $index) $slides[$sid]['slide_index'] = $index;
		$can_meta = $slots->user_can_edit();

		$this->ajax_response_data([
			'id' => $slider_id,
			'alias' => $this->slider->get_alias(),
			'title' => $this->slider->get_title(),
			'static_slide_id' => $static_id > 0 ? $static_id : null,
			'slides' => $slides,
			'template_uid' => $template_uid,
			//full: the plugin's own role, which an add-on may ask for before it offers what only that role may do
			'caps' => ['meta' => $can_meta, 'full' => !$this->current_user_can_not()],
			//the Brand Kit for the AI assist's brand job, Quick Edit has no global settings of its own
			'brand' => ['colors' => array_values((array)$this->get_val($this->get_global_settings(), ['brand', 'colors'], [])), 'fonts' => array_values((array)$this->get_val($this->get_global_settings(), ['brand', 'fonts'], []))],
			'slots' => $slots->global_rows_at($slots->get_rows($template_uid, $slider_id), $slots->seeded_global_index($slides, $index_map)),
			//every Quick Edit user: the AI assist guesses an untagged layer's role from it (5 KB); tagging stays behind meta
			'lexicon' => $slots->get_lexicon(),
			'status' => $can_meta ? $slots->get_status($template_uid, $slider_id) : null,
		]);
	}

	/**
	 * Meta Editing: the slider a slots op addresses, after the cap check
	 * @return array [RevSliderSlots, slider id, template uid]
	 */
	private function slots_context($data){
		$slots = Registry::instance()->get('RevSliderSlots');
		if(!$slots->user_can_edit()) $this->ajax_response_error(__('Meta Editing is limited to designers, support and developers', 'revslider'));

		$slider_id = intval($this->get_val($data, 'slider_id'));
		if($slider_id <= 0) $this->ajax_response_error(__('Bad Request', 'revslider'));

		$this->slider->init_by_id($slider_id);
		if($this->slider->inited === false) $this->ajax_response_error(__('Slider could not be loaded', 'revslider'));

		return [$slots, $slider_id, $slots->template_uid_of($this->slider)];
	}

	/**
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function slots_get($data = false){
		$data = $this->get_data($data);
		list($slots, $slider_id, $template_uid) = $this->slots_context($data);

		$this->ajax_response_data(['template_uid' => $template_uid, 'slots' => $slots->get_rows($template_uid, $slider_id)]);
	}

	/**
	 * one layer's tag; `tag` is the JSON the Quick Editor built (role, weight, edit[], txt, img, addons, group, alias)
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function slots_save($data = false){
		$data = $this->get_data($data);
		list($slots, $slider_id, $template_uid) = $this->slots_context($data);

		$tag = $this->get_val($data, 'tag', []);
		if(is_string($tag)) $tag = $this->json_decode_slashes($tag);
		$row = $slots->save_row($template_uid, $slider_id, intval($this->get_val($data, 'slide_index', 0)), (string)$this->get_val($data, 'lkey', ''), $tag, $this->environment()->user_id());
		if(!$row) $this->ajax_response_error(__('Bad Request', 'revslider'));

		$this->ajax_response_data(['slot' => $row]);
	}

	/**
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function slots_delete($data = false){
		$data = $this->get_data($data);
		list($slots, $slider_id, $template_uid) = $this->slots_context($data);

		$slots->delete_row($template_uid, $slider_id, intval($this->get_val($data, 'slide_index', 0)), (string)$this->get_val($data, 'lkey', ''));

		$this->ajax_response_success(__('Removed', 'revslider'));
	}

	/**
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function slots_status($data = false){
		$slots = Registry::instance()->get('RevSliderSlots');
		if(!$slots->user_can_edit()) $this->ajax_response_error(__('Meta Editing is limited to designers, support and developers', 'revslider'));

		$this->ajax_response_data(['status' => $slots->get_status_map()]);
	}

	/**
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function slots_status_save($data = false){
		$data  = $this->get_data($data);
		$slots = Registry::instance()->get('RevSliderSlots');
		if(!$slots->user_can_edit()) $this->ajax_response_error(__('Meta Editing is limited to designers, support and developers', 'revslider'));

		$status = $slots->save_status((string)$this->get_val($data, 'template_uid', ''), $data, $this->environment()->user_id(), intval($this->get_val($data, 'slider_id', 0)));
		if(!$status) $this->ajax_response_error(__('Bad Request', 'revslider'));

		$this->ajax_response_data(['status' => $status]);
	}

	/**
	 * Report bug: the item lands in Monday when the site carries a token
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function slots_bug($data = false){
		$data  = $this->get_data($data);
		$slots = Registry::instance()->get('RevSliderSlots');
		if(!$slots->user_can_edit()) $this->ajax_response_error(__('Meta Editing is limited to designers, support and developers', 'revslider'));

		$ctx = $this->get_val($data, 'ctx', []);
		if(is_string($ctx)) $ctx = $this->json_decode_slashes($ctx);
		$res = $slots->report_bug((string)$this->get_val($data, 'template_uid', ''), intval($this->get_val($data, 'slider_id', 0)), $ctx, $this->environment()->user_id());
		if(isset($res['error'])) $this->ajax_response_error($res['error']);

		$this->ajax_response_data($res);
	}

	/**
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function slots_export($data = false){
		$slots = Registry::instance()->get('RevSliderSlots');
		if(!$slots->user_can_edit()) $this->ajax_response_error(__('Meta Editing is limited to designers, support and developers', 'revslider'));

		$this->ajax_response_data($slots->export_rows());
	}

	/**
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function slots_findings($data = false){
		$slots = Registry::instance()->get('RevSliderSlots');
		if(!$slots->user_can_edit()) $this->ajax_response_error(__('Meta Editing is limited to designers, support and developers', 'revslider'));

		$this->ajax_response_data($slots->findings());
	}

	/**
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function slots_derive($data = false){
		$slots = Registry::instance()->get('RevSliderSlots');
		if(!$slots->user_can_edit()) $this->ajax_response_error(__('Meta Editing is limited to designers, support and developers', 'revslider'));

		$this->ajax_response_data($slots->derive_rows());
	}

	/**
	 * bulk upsert from a JSON array of rows (sr-connect manifest export); human rows stay unless `force`
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function slots_seed($data = false){
		$data = $this->get_data($data);
		$slots = Registry::instance()->get('RevSliderSlots');
		if(!$slots->user_can_edit()) $this->ajax_response_error(__('Meta Editing is limited to designers, support and developers', 'revslider'));

		$rows = $this->get_val($data, 'rows', []);
		if(is_string($rows)) $rows = $this->json_decode_slashes($rows);
		if(!is_array($rows) || empty($rows)) $this->ajax_response_error(__('Bad Request', 'revslider'));

		$this->ajax_response_data($slots->seed($rows, $this->_truefalse($this->get_val($data, 'force', false)) === true));
	}

	/**
	 * Gutenberg quick-editor: surgical content-only layer writes.
	 * @since 7.0
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function quickedit_save($data = false){
		$data = $this->get_data($data);
		$slider_id = intval($this->get_val($data, 'slider_id'));
		$changes = $this->json_decode_slashes($this->get_val($data, 'changes', '[]'));
		$fonts = $this->get_val($data, 'fonts', false);
		if(is_string($fonts)) $fonts = $this->json_decode_slashes($fonts);

		if($slider_id <= 0 || ((!is_array($changes) || empty($changes)) && !is_array($fonts))) $this->ajax_response_error(__('Bad Request', 'revslider'));

		$this->quickedit_check_limited_access($data);

		$this->slider->init_by_id($slider_id);
		if($this->slider->inited === false) $this->ajax_response_error(__('Slider could not be loaded', 'revslider'));
		//as the editor's save: a premium template changes only with a licence
		if($this->_truefalse($this->slider->get_param('prem', false)) === true && $this->_truefalse($this->get_options(['system', 'valid'], 'false')) === false) $this->ajax_response_error(__('Changes to a module built from a premium template need an SR7 licence.', 'revslider'));

		// Persist module font registry so the frontend can load Google/custom families after bulk font edits
		if(is_array($fonts)){
			//a font's name and url go into the page head
			$this->slider->set_param('fonts', $this->quickedit_clean($fonts, $this->slider->get_param('fonts', [])));
			$this->slider->save_params();
		}

		if(!is_array($changes)) $changes = [];
		$addon_changes = [];

		foreach($changes as $change){
			if(!is_array($change)) continue;

			$slide_id = intval($this->get_val($change, 'slide_id'));
			$layer_id = $this->get_val($change, 'layer_id');
			$field = sanitize_key($this->get_val($change, 'field'));
			$value = $this->get_val($change, 'value');

			if($slide_id <= 0 || $layer_id === '' || $layer_id === null) continue;
			if(!in_array($field, ['text', 'image', 'video', 'videoposter', 'slidebg', 'slide_layers', 'bgcolor', 'link', 'addon', 'slidestate', 'slideorder'], true)) continue;

			//the module's slides in their new order (the global slide stays where it is): only slides of this module move
			if($field === 'slideorder'){
				$order = 0;
				$static = intval($this->slide->get_static_slide_id($slider_id));
				foreach((array)$value as $id){
					$id = intval($id);
					if($id <= 0 || $id === $static) continue;
					$this->slide->init_by_id($id);
					if($this->slide->inited === false || intval($this->slide->get_slider_id()) !== $slider_id) continue;
					$this->slide->change_slide_order($id, ++$order, true);
				}
				continue;
			}

			//written after every other change, so a layer tree saved whole in the same request cannot overwrite it
			if($field === 'addon'){
				$addon_changes[] = $change;
				continue;
			}

			$this->slide->init_by_id($slide_id);
			if(intval($this->slide->get_slider_id()) !== $slider_id) continue;

			//a slide switched off stays in the module, no page shows it
			if($field === 'slidestate'){
				if(intval($this->slide->get_static_slide_id($slider_id)) === $slide_id) continue;
				$this->slide->set_param(['publish', 'state'], $value === 'unpublished' ? 'unpublished' : 'published');
				$this->slide->save_params();
				continue;
			}

			$layers = $this->slide->get_layers();
			if(!is_array($layers)) continue;

			if($field === 'slidebg'){
				foreach($layers as $lid => $layer){
					if(($this->get_val($layer, 'subtype') === 'slidebg')){
						$layer_id = $lid;
						break;
					}
				}
			}

			if(!isset($layers[$layer_id]) || !is_array($layers[$layer_id])) {
				if($field !== 'slide_layers') continue;
			}

			// A layer link is not stored on the layer: SR7 keeps it as a click action on the SLIDE, in the slide
			// params. Quick Edit owns exactly one link action per layer and never touches the others.
			if($field === 'link'){
				$actions = $this->slide->get_param('actions', []);
				if(!is_array($actions)) $actions = [];
				$url = is_array($value) ? esc_url_raw((string)$this->get_val($value, 'url', '')) : '';
				$target = ($this->get_val($value, 'target') === '_self') ? '_self' : '_blank';
				$slkey = (string)$layer_id;
				$found = false;

				foreach($actions as $k => $action){
					if(!is_array($action) || $this->get_val($action, 'a') !== 'link') continue;
					$src = $this->get_val($action, 'src', []);
					if(!is_array($src)) continue;
					if(!in_array($slkey, array_map('strval', $src), true)) continue;

					if($url === ''){
						unset($actions[$k]);
					}else{
						$actions[$k]['link'] = $url;
						$actions[$k]['target'] = $target;
					}
					$found = true;
					break;
				}

				if(!$found && $url !== ''){
					//replace: the layer leaves the click action it had (Scroll Below Module, a modal), dropped when it was the only one
					if($this->_truefalse($this->get_val($value, 'replace', false)) === true){
						foreach($actions as $k => $action){
							if(!is_array($action) || $this->get_val($action, 'a') === 'link' || $this->get_val($action, 'evt', 'click') !== 'click') continue;
							$src = $this->get_val($action, 'src', []);
							if(!is_array($src) || !in_array($slkey, array_map('strval', $src), true)) continue;
							$src = array_values(array_filter($src, fn($s) => (string)$s !== $slkey));
							if(empty($src)) unset($actions[$k]);
							else $actions[$k]['src'] = $src;
						}
					}
					// Same shape the editor's own Link element creates (admin/assets/js/editor/add.js)
					$actions[] = ['a' => 'link', 'd' => 0, 'evt' => 'click', 'ltype' => 'a', 'rd' => 0, 'src' => [$slkey], 'link' => $url, 'target' => $target];
				}

				$this->slide->set_param('actions', array_values($actions));
				$this->slide->save_params();
				continue;
			}

			switch($field){
				case 'slide_layers':
					if($layer_id !== '*' && $layer_id !== 'all') continue 2;
					if(!is_array($value) || empty($value)) continue 2;
					$value = $this->sanitize_quickedit_slide_layers($value, $layers);
					$this->slide->set_layers_raw($value);
					$this->slide->save_layers();
				break;
				case 'text':
					if(!is_string($value)) continue 2;
					$layers[$layer_id]['content'] ??= [];
					// Keep meta tokens like {{home_url}} intact; allow basic markup used by text layers
					$layers[$layer_id]['content']['text'] = wp_kses_post($value);
				break;
				case 'image':
					if(!is_array($value)) continue 2;
					//an SVG layer's file is its content.src and takes an SVG only; colours, size and animation stay on the layer
					if($this->get_val($value, 'media') === 'svg'){
						$src = esc_url_raw($this->get_val($value, 'src', ''));
						if($this->get_val($layers[$layer_id], 'type') !== 'svg' || !preg_match('/\.svg(\?|#|$)/i', $src)) continue 2;
						$layers[$layer_id]['content'] ??= [];
						$layers[$layer_id]['content']['src'] = $src;
						break;
					}
					$layers[$layer_id]['bg'] ??= [];
					$layers[$layer_id]['bg']['type'] = 'image';
					$layers[$layer_id]['bg']['image'] = $this->quickedit_set_img_src($this->get_val($layers[$layer_id]['bg'], 'image', []), esc_url_raw($this->get_val($value, 'src', '')));
					$layers[$layer_id]['bg']['image']['u'] = true;
					if($this->get_val($value, 'lib_id') !== null && $this->get_val($value, 'lib_id') !== ''){
						$layers[$layer_id]['bg']['image']['lib_id'] = intval($this->get_val($value, 'lib_id'));
					}
				break;
				case 'videoposter':
					if(is_array($value)) $value = $this->get_val($value, 'src', '');
					if(!is_string($value) || $value === '') continue 2;
					$layers[$layer_id]['bg'] ??= [];
					$layers[$layer_id]['bg']['video'] ??= [];
					$layers[$layer_id]['bg']['video']['poster'] ??= [];
					$layers[$layer_id]['bg']['video']['poster']['src'] = esc_url_raw($value);
				break;
				case 'video':
				case 'slidebg':
					if(!is_array($value)) continue 2;
					$layers[$layer_id]['bg'] ??= [];
					$media = sanitize_key($this->get_val($value, 'media', 'image'));
					if($media === 'video'){
						$layers[$layer_id]['bg']['type'] = 'video';
						$layers[$layer_id]['bg']['video'] ??= [];
						$layers[$layer_id]['bg']['video']['src'] = esc_url_raw($this->get_val($value, 'src', ''));
						$layers[$layer_id]['bg']['video']['type'] = sanitize_key($this->get_val($value, 'video_type', $this->get_val($layers[$layer_id]['bg']['video'], 'type', 'html5')));
						if(isset($layers[$layer_id]['bg']['image']['src'])) $layers[$layer_id]['bg']['image'] = $this->quickedit_set_img_src($layers[$layer_id]['bg']['image'], '');
					} else {
						$layers[$layer_id]['bg']['type'] = 'image';
						$layers[$layer_id]['bg']['image'] = $this->quickedit_set_img_src($this->get_val($layers[$layer_id]['bg'], 'image', []), esc_url_raw($this->get_val($value, 'src', '')));
						if($this->get_val($value, 'lib_id') !== null && $this->get_val($value, 'lib_id') !== ''){
							$layers[$layer_id]['bg']['image']['lib_id'] = intval($this->get_val($value, 'lib_id'));
						}
					}
				break;
				case 'bgcolor':
					$layers[$layer_id]['bg'] ??= [];
					$layers[$layer_id]['bg']['type'] = 'color';
					if(is_array($value)) $value = $this->get_val($value, 'string', $this->get_val($value, 'color', ''));
					if(!is_string($value) || $value === '') continue 2;
					$layers[$layer_id]['bg']['color'] = sanitize_text_field($value);
					if(isset($layers[$layer_id]['bg']['image']['src'])) $layers[$layer_id]['bg']['image'] = $this->quickedit_set_img_src($layers[$layer_id]['bg']['image'], '');
					//a mesh would paint over the picked colour; Quick Edit switches it off live the same way
					if(isset($layers[$layer_id]['bg']['mesh']['k'])) $layers[$layer_id]['bg']['mesh']['k'] = 'none';
				break;
			}

			if($field === 'slide_layers') continue;

			$this->slide->set_layers_raw($layers);
			$this->slide->save_layers();
		}

		foreach($addon_changes as $change) $this->quickedit_save_addon($slider_id, $change);
		//after the writes, so an add-on that indexes what a module holds reads the saved state
		do_action('revslider_quickedit_saved', $slider_id);

		$this->clear_cache($slider_id);
		$this->ajax_response_success(__('Saved', 'revslider'));
	}

	/**
	 * Quick Edit: duplicate a slide of the module, the copy goes to the end as in the editor
	 * @since 7.2.2
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function quickedit_duplicate_slide($data = false){
		$data		= $this->get_data($data);
		$slider_id	= intval($this->get_val($data, 'slider_id'));
		$slide_id	= $this->quickedit_slide_of($data, $slider_id);

		Registry::instance()->get('RevSliderSlots')->stamp_slide_indexes($this->slider);
		$new_slide_id = $this->slide->duplicate_slide_by_id($slide_id, $slider_id);
		if($new_slide_id === false) $this->ajax_response_error(__('Slide could not duplicated', 'revslider'));

		//named as the editor names a duplicate; written as it is, save_params() would run the role filters over the copy
		$table	= $this->database()->prefix() . Data::TABLE_SLIDES;
		$params = json_decode((string)$this->database()->get_var($this->database()->prepare("SELECT params FROM " . $table . " WHERE id = %d", $new_slide_id)), true);
		if(is_array($params)){
			$params['title'] = str_replace('Copy Copy', 'Copy', trim(($params['title'] ?? '') . ' Copy'));
			$this->database()->update($table, ['params' => json_encode($params)], ['id' => intval($new_slide_id)]);
		}

		$this->clear_cache($slider_id);
		$this->ajax_response_data(['slide_id' => intval($new_slide_id)]);
	}

	/**
	 * Quick Edit: delete a slide of the module, never its last one
	 * @since 7.2.2
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function quickedit_delete_slide($data = false){
		$data		= $this->get_data($data);
		$slider_id	= intval($this->get_val($data, 'slider_id'));
		$slide_id	= $this->quickedit_slide_of($data, $slider_id);

		$regular = array_filter($this->slider->get_slides_raw(false), function($slide){ return empty($slide['global']); });
		if(count($regular) <= 1) $this->ajax_response_error(__('The last slide cannot be deleted.', 'revslider'));

		Registry::instance()->get('RevSliderSlots')->stamp_slide_indexes($this->slider);
		$this->clear_cache(false, $slide_id);
		if($this->slide->delete_slide_by_id($slide_id) === false) $this->ajax_response_error(__('Slide could not be deleted', 'revslider'));

		$this->clear_cache($slider_id);
		$this->ajax_response_success(__('Slide deleted', 'revslider'));
	}

	/**
	 * what both slide operations check: the user may edit the module, and the slide is one of its own, not the global one
	 * @return int the slide id
	 */
	private function quickedit_slide_of($data, $slider_id){
		$slide_id = intval($this->get_val($data, 'slide_id'));
		if($slider_id <= 0 || $slide_id <= 0) $this->ajax_response_error(__('Bad Request', 'revslider'));

		$this->quickedit_check_limited_access($data);

		$this->slider->init_by_id($slider_id);
		if($this->slider->inited === false) $this->ajax_response_error(__('Slider could not be loaded', 'revslider'));

		$this->slide->init_by_id($slide_id);
		if(intval($this->slide->get_slider_id()) !== $slider_id || intval($this->slide->get_static_slide_id($slider_id)) === $slide_id) $this->ajax_response_error(__('Bad Request', 'revslider'));

		return $slide_id;
	}

	/**
	 * Quick Edit: an add-on's own block on the module, a slide or a layer. Core cannot tell what the block means, so
	 * it writes only what the add-on's filter hands back, and only under addOns.<slug> of that one target.
	 * @since 7.2.0
	 * @param int $slider_id
	 * @param array $change slug, slide_id, layer_id ('module', 'slide' or a layer key) and value
	 * @return bool whether anything was written
	 */
	private function quickedit_save_addon($slider_id, $change){
		$slug		= sanitize_key($this->get_val($change, 'slug', ''));
		$target		= (string)$this->get_val($change, 'layer_id', '');
		$slide_id	= intval($this->get_val($change, 'slide_id'));
		$value		= $this->get_val($change, 'value');
		if($slug === '' || $target === '') return false;

		if($target === 'module'){
			$old	= $this->slider->get_param(['addOns', $slug], []);
			$clean	= apply_filters('revslider_quickedit_addon_save', null, $value, $old, $slug, 'module', $slider_id, 0, '');
			//an add-on without a filter of its own: Quick Edit's options on its module block
			if($clean === null) $clean = $this->quickedit_merge_block($old, $value);
			if(!is_array($clean)) return false;
			$this->slider->set_param(['addOns', $slug], $clean);
			$this->slider->save_params();
			return true;
		}

		$this->slide->init_by_id($slide_id);
		if($this->slide->inited === false || intval($this->slide->get_slider_id()) !== $slider_id) return false;

		if($target === 'slide'){
			$clean = apply_filters('revslider_quickedit_addon_save', null, $value, $this->slide->get_param(['addOns', $slug], []), $slug, 'slide', $slider_id, $slide_id, '');
			if(!is_array($clean)) return false;
			$this->slide->set_param(['addOns', $slug], $clean);
			$this->slide->save_params();
			return true;
		}

		$layers = $this->slide->get_layers();
		if(!is_array($layers) || !isset($layers[$target]) || !is_array($layers[$target])) return false;
		$clean = apply_filters('revslider_quickedit_addon_save', null, $value, $layers[$target]['addOns'][$slug] ?? [], $slug, 'layer', $slider_id, $slide_id, $target);
		if(!is_array($clean)) return false;
		$layers[$target]['addOns'] ??= [];
		$layers[$target]['addOns'][$slug] = $clean;
		$this->slide->set_layers_raw($layers);
		$this->slide->save_layers();
		return true;
	}

	/**
	 * An add-on block merged in key by key: a stored block leaves its defaults out, so a key may be new; a list never
	 * goes where a value was, nor a value where a list was, and text is cleaned
	 * @since 7.2.3
	 * @return array
	 */
	private function quickedit_merge_block($old, $new, $depth = 0){
		$old = is_array($old) ? $old : [];
		if(!is_array($new) || $depth > 4) return $old;
		foreach($new as $k => $v){
			if(!is_int($k)) $k = preg_replace('/[^A-Za-z0-9_\-]/', '', (string)$k);
			if($k === '') continue;
			$was = $old[$k] ?? null;
			if(is_array($v)){
				if($was !== null && !is_array($was)) continue;
				$old[$k] = $this->quickedit_merge_block($was, $v, $depth + 1);
			}elseif(is_array($was)) continue;
			elseif(is_bool($v) || is_int($v) || is_float($v)) $old[$k] = $v;
			elseif(is_string($v)) $old[$k] = sanitize_text_field($v);
		}
		return $old;
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function delete_slide($data = false){
		$data		= $this->get_data($data);
		$slide_id	= intval($this->get_val($data, 'slide_id'));
		if($slide_id === 0) $this->ajax_response_error(__('Wrongly formated Slide ID', 'revslider'));
		
		$this->clear_cache(false, $slide_id);
		
		if($this->slide->delete_slide_by_id($slide_id) !== false) $this->ajax_response_success(__('Slide deleted', 'revslider'));
		
		$this->ajax_response_error(__('Slide could not be deleted', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function duplicate_slide($data = false){
		$data		= $this->get_data($data);
		$slide_id	= intval($this->get_val($data, 'slide_id'));
		$slider_id	= intval($this->get_val($data, 'slider_id'));
		
		$new_slide_id = $this->slide->duplicate_slide_by_id($slide_id, $slider_id);
		if($new_slide_id === false) $this->ajax_response_error(__('Slide could not duplicated', 'revslider'));

		$this->clear_cache($slider_id); //slide list changed - bust the slider's cached object/markup
		$this->slide->init_by_id($new_slide_id);
		$_slide = $this->slide->get_overview_data();
		
		$this->ajax_response_data(['slide' => $_slide]);
	}	

	/***********************
	 *   LAYER FUNCTIONS   *
	 ***********************/
	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_layers($data = false){
		$data		= $this->get_data($data);
		$slider_id	= $this->get_val($data, 'slider_id', false);
		$slide_id	= (array)$this->get_val($data, 'slide_id', []);
		$slides		= [];
		$layers		= [];

		if($slider_id !== false){
			$this->slider->init_by_id($slider_id);
			if($this->slider->inited === false) $this->ajax_response_error(__('Layers could not be loaded', 'revslider'));
			$slides = $this->slider->get_slides_raw(true);
		}elseif(!empty($slide_id)){
			$slides = $this->slider->get_slides_by_slide_ids_raw($slide_id, true);
		}else{
			$this->ajax_response_error(__('Layers could not be loaded', 'revslider'));
		}

		if(empty($slides)) $this->ajax_response_error(__('Layers could not be loaded', 'revslider'));

		foreach($slides ?? [] as $slide){
			$id = $this->get_val($slide, 'id');
			$layers[$id] = $this->get_val($slide, 'layers');
		}

		$this->ajax_response_data(['layers' => $layers]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function export_layers($data = false){
		$data	 = $this->get_data($data);
		$title	 = $this->get_val($data, 'title', $this->get_request_var('title'));
		$videoid = intval($this->get_val($data, 'videoid', $this->get_request_var('videoid')));
		$thumbid = intval($this->get_val($data, 'thumbid', $this->get_request_var('thumbid')));
		$layers	 = $this->get_val($data, 'layers', $this->get_request_var('layers', '', false));
		$export  = new Export($title);
		$url	 = $export->export_layer_group($videoid, $thumbid, $layers);

		$this->ajax_response_data(['url' => $url]);
	}

	/***********************
	 *  FOLDER FUNCTIONS   *
	 ***********************/
	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function create_slider_folder($data = false){
		$data	= $this->get_data($data);
		$folder	= new Folder();
		$title	= $this->get_val($data, 'title', __('New Folder', 'revslider'));
		$parent	= $this->get_val($data, 'parentFolder', 0);
		$new	= $folder->create_folder($title, $parent);

		if($new !== false) $this->ajax_response_data(['folder' => $new->get_overview_data()]);

		$this->ajax_response_error(__('Folder Creation Failed', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function save_slider_folder($data = false){
		$data		= $this->get_data($data);
		$folder		= new Folder();
		$children	= (array)$this->get_val($data, 'children', []);
		$folder_id	= $this->get_val($data, 'id');

		if($folder->add_slider_to_folder($children, $folder_id) == true) $this->ajax_response_success(__('Slider Moved to Folder', 'revslider'));
		
		$this->ajax_response_error(__('Failed to Move Slider Into Folder', 'revslider'));
	}

	/***********************
	 *   FONT FUNCTIONS    *
	 ***********************/
	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_font_list($data = false){
		$data = $this->get_data($data);
		$font = $this->get_val($data, 'font');
		$font_families = $this->get_font_familys();

		if(empty($font)) $this->ajax_response_data(['fonts' => $font_families]);

		foreach($font_families ?? [] as $ff){
			if($this->get_val($ff, 'label') !== $font) continue;
			$this->ajax_response_data(['fonts' => [$ff]]);
		}
		
		$this->ajax_response_error(__('Font not found', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function download_collected_fonts($data = false){
		$data = $this->get_data($data);
		$font = $this->get_val($data, 'font', []);
		if(empty($font)) return;
		
		$rsf = Registry::instance()->get('RevSliderFonts');
		$rsf->preload_fonts((array)$font, false, true);

		$this->ajax_response_success('', '');
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function collect_google_fonts($data = false){
		$data		= $this->get_data($data);
		$fonts_cl	= Registry::instance()->get('RevSliderFonts');
		$page		= $this->get_val($data, 'page', 1);
		$return		= $fonts_cl->collect_used_fonts(true, true, $page);

		$this->ajax_response_data($return);
	}
	
	/**
	 * set the google font to current date, so that it will be redownloaded
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function delete_google_fonts(){
		$this->update_option(['timestamps', 'google-fonts'], time());
		$this->update_option(['fonts', 'fonts'], [], self::OPTIONS_FONTS);
		$this->update_option(['fonts', 'collected'], [], self::OPTIONS_FONTS);
		
		$this->ajax_response_success(__('Successfully deleted all fonts cache', 'revslider'));
	}

	/************************
	 *  AI FUNCTIONS  *
	 ************************/

	/**
	 * @param array $data
	 * @param string $ai_method
	 * @return void
	 */
	protected function get_ai_wrapper($data, $ai_method){
		if($this->_truefalse($this->get_options(['system', 'valid'], 'false')) !== true) {
			$this->ajax_response_error(__('Please activate Slider Revolution', 'revslider'));
		}

		/* @var RevSliderAi $ai */
		$ai = Registry::instance()->get('RevSliderAI');
		$return = [
			'success' => false,
			'message' => sprintf(__('AI method not implemented (%s).', 'revslider'), $ai_method),
		];
		if(method_exists($ai, $ai_method)) $return = $ai->{ $ai_method }($data);

		if($return['success']) $this->ajax_response_data(stripslashes_deep($return));

		$this->ajax_response_error($return['message'], isset($return['code']) ? ['code' => $return['code']] : null);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function create_ai_element($data){
		if($this->_truefalse($this->get_options(['system', 'valid'], 'false')) !== true) {
			$this->ajax_response_error( __( 'Please activate Slider Revolution', 'revslider' ) );
		}

		/* @var RevSliderAi $ai */
		$ai = Registry::instance()->get('RevSliderAI');
		$result = $ai->generate_image($data);
		if(!$result['success']) $this->ajax_response_error($result['message']);
		
		$event_id = $this->get_val($result, 'event_id');
		if(!empty($event_id)) $ai->add_event_id($event_id, $result);

		$this->ajax_response_data(['data' => $result]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_ai_element_status($data){
		/* @var RevSliderAi $ai */
		$ai       = Registry::instance()->get('RevSliderAI');
		$event_id = $this->get_val($data, 'event_id');
		$result   = $ai->get_image_status($event_id, 'api');
		if (!$result['success']) {
			$this->ajax_response_error($result['message']);
		}

		$average_duration = $this->get_val($result, 'average_duration', false);
		$estimation = $this->get_val($result, 'estimation', false);
		if($result['status'] === 'done'){
			$img_stream = $this->get_val($result, 'result');
			if(empty($img_stream)) $this->ajax_response_error(__('Empty Image Data', 'revslider'));

			// reply - local|external - how to return images data - fetch to a local system or return external urls
			$reply = $this->get_val($data, 'reply', 'local');
			if ($reply === 'local') {
				// fetch_mode - url|data - images in response as urls or data string
				$fetch_mode = $this->get_val($result, 'fetch_mode', 'url');

				//get the orig prompt
				$open_event = $ai->get_open_event_by_event_id($event_id);
				$prompt		= $this->get_val($open_event, 'prompt');
				if(!empty($prompt)) $prompt = 'Created through Slider Revolution with prompt: '.esc_html($prompt);

				$result['result'] = $ai->fetch_generated_images($img_stream, $event_id, $prompt, false, $fetch_mode);
			}

			//remove event_id from a checklist
			$ai->remove_event_id($event_id);
		}

		if ($result['status'] === 'done_bg_job') {
			// images were downloaded in between status calls via a background job
			// update the status to done so js can continue
			$result['status'] = 'done';
		}

		if ($result['status'] === 'active') {
			// request still active, add empty results to suppress warning
			$result['result'] = [];
		}

		$this->ajax_response_data([
			'data' => $result['result'],
			'status' => $result['status'],
			'event_id' => $event_id,
			'average_duration' => $average_duration,
			'estimation' => $estimation
		]);
	}

	
	/************************
	 *  ELEMENTS FUNCTIONS  *
	 ************************/
	
	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function library_preload(){
		/* @var RevSliderObjectLibrary $library */
		$library = Registry::instance()->get('RevSliderObjectLibrary');
		$this->ajax_response_data( $library->_preload_list() );
	}

	/**
	 * @param array $data
	 * @param bool $return return items or send ajax response
	 *
	 * @return array|void
	 */
	public function get_elements_library_all_new($data, $return = false){
		/*type -> icons, images, videos, layers, effects
			keyword
			favorite -> (1, 0)
			order -> (title, date)
			dir -> (asc, desc)
			offset
			limit
			type -> all
			limit*/
		$rsaf	= new \ThemePunch\SR\Core\Admin\Functions();
		$type	= ['icons', 'images', 'videos', 'layers', 'svgs'];
		$type	= apply_filters('sr_get_elements_library_all_new', $type);
		$_type	= $this->get_val($data, 'type', false);
		$order	= $this->get_val($data, 'order', 'date');
		$keyword= $this->get_val($data, 'keyword');
		if($_type !== false) $type = array_merge($type, [$_type]);
		$favorite = $this->_truefalse($this->get_val($data, 'favorite', false));
		$limit	= $_type === 'all' ? 15 : intval($this->get_val($data, 'limit', 0));
		$offset	= $_type === 'all' ? 0 : intval($this->get_val($data, 'offset', 0));
		$dir	= $this->get_val($data, 'dir', 'desc');
		$result	= [];
		$elements = $rsaf->get_full_library($type);
		$order = ($order !== 'date') ? 'title' : 'added';

		foreach($elements as $name => $items){
			$result[$name] = $items['items'];
			
			if($dir === 'asc'){
				// sort only elements that have 'added' date or if sort order by 'title'
				if( in_array($name, ['layers', 'videos', 'images']) || $order === 'title' ){
					usort( $result[$name], function ($a, $b) use ($order) {
						return $order === "date" ? strtotime($a[$order]) <=> strtotime($b[$order]) : $a[$order] <=> $b[$order];
					});
				}else{
					krsort( $result[$name]);
				}
			}else{
				// sort only elements that have 'added' date or if sort order by 'title'
				if( in_array($name, ['layers', 'videos', 'images']) || $order === 'title' ){
					usort( $result[$name], function ($a, $b) use ($order) {
						return $order === "date" ? strtotime($b[$order]) <=> strtotime($a[$order]) : $b[$order] <=> $a[$order];
					});
				}
			}
			if($favorite === true){
				$result[$name] = array_filter( $result[$name], function ($item) {
					return isset($item['favorite']) && $item['favorite'] === true;
				});
			}

			if($keyword !== ''){
				$result[$name] = array_filter( $result[$name], function ($item) use ($keyword) {
					// Check if the 'name' key exists and contains the search term (case-insensitive)
					if(isset($item['title']) && stripos($item['title'], $keyword) !== false || isset($item['handle']) && stripos($item['handle'], $keyword) !== false) return true;
					if(isset($item['tags']) && !empty($item['tags'])){
						foreach($item['tags'] ?? [] as $tag){
							if(stripos($tag, $keyword) !== false) return true;
						}
					}
					return false; 
				});
			}

			if( $offset !== 0 )
				$result[$name] = array_slice( $result[$name], $offset);
			if( $limit !== 0 )
				$result[$name] = array_slice( $result[$name], 0, $limit);
		}

		if ($return) return $result;
		
		$this->ajax_response_data( $result);
	}
	
	/***********************
	 *  ADDONS FUNCTIONS   *
	 ***********************/
	/** @return void the response is sent by ajax_response_*(), which ends the request */
	/**
	 * The Effects Library's first opening on a site: the Tools server's tile lists are fetched now instead of with
	 * the next update check, so every add-on shows with its stills at once. Once an hour at most, the regular
	 * check keeps them current afterwards.
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function refresh_effects_catalogue(){
		$fresh = false;
		if(time() - intval($this->get_options(['timestamps', 'effects-catalogue'], 0)) > 3600){
			$this->update_option(['timestamps', 'effects-catalogue'], time());
			$update = Registry::instance()->get('RevSliderUpdate') ?: new Update(RS_REVISION);   //an ajax call registers no global for it
			$update->force = true;
			$update->_retrieve_version_info();
			$fresh = true;
		}
		$addon = Registry::instance()->get('RevSliderAddons');
		$this->ajax_response_data(['effects' => $addon->get_effects_catalogue(), 'fresh' => $fresh, 'server' => !empty($this->option_storage()->get(Functions::OPTIONS_EFFECTS, []))]);
	}

	public function get_addon($data = false){
		$data	= $this->get_data($data);
		$slugs	= $this->get_val($data, 'slugs', []);
		$addon	= Registry::instance()->get('RevSliderAddons');
		$addons = $addon->get_addon_list(true);
		$lang   = apply_filters( 'revslider_api_get_addon_lang', [] );
		$values = apply_filters( 'revslider_api_get_addon_values', [] );

		foreach($addons as $slug => $a){
			if(!$a->global && !in_array($slug, $slugs)) continue;
			if(isset($lang[$slug])) $addons[ $slug ]->lang = $lang[$slug];
			if(isset($values[$slug])) $addons[ $slug ]->values = $values[$slug];
		}
		
		/*
		* 🔴 The badge is for what ARRIVES later: on a first look there is nothing to compare against, so the
		* list is only remembered and nothing is marked. And only the LIBRARY marks them seen, the editor asks
		* for the same list on every module it opens
		*/
		$seen = $this->environment()->user_value('rs-addons-seen');
		$seen = (is_array($seen)) ? $seen : null;
		foreach($addons as $slug => $a){
			if(is_object($addons[$slug])) $addons[$slug]->isnew = ($seen !== null && !in_array($slug, $seen, true));
		}
		if($this->_truefalse($this->get_val($data, 'seen', false)) === true) $this->environment()->set_user_value('rs-addons-seen', array_keys($addons));

		$this->update_option(['counter'], 0, 'rs-addons');
		$this->ajax_response_data(['addons' => $addons]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	/**
	 * reports the add-ons of the SR6 move and fetches the SR7 build of those still in their SR6 one, for the
	 * overview's migration on a site without a licence (ADR-0027)
	 * @since 7.2.5
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function update_moved_addons(){
		if(!$this->environment()->user_can('activate_plugins') || !$this->environment()->user_can('install_plugins')) $this->ajax_response_error(__('Function only available for administrators', 'revslider'));

		$upd  = Registry::instance()->get('RevSliderPluginUpdateV6');
		$need = $upd->moved_addons_needing_build();
		if(empty($need) || $upd->report_moved_addons() === false){
			$upd->set_moved_try();
			$this->ajax_response_data(['updated' => [], 'failed' => []]);
		}

		$addon	 = Registry::instance()->get('RevSliderAddons');
		$updated = [];
		$failed	 = [];
		foreach($need as $slug){
			if(!$upd->moved_delivery_open($slug)) continue;
			$res = $addon->download_addon($slug);
			if($res === true) $updated[] = $slug; else $failed[$slug] = $res;
		}
		if(empty($updated)) $upd->set_moved_try();

		$this->ajax_response_data(['updated' => $updated, 'failed' => $failed]);
	}

	public function activate_addon($data = false){
		$return = false;
		
		if ( $this->environment()->user_can( 'activate_plugins' ) && $this->environment()->user_can( 'install_plugins' ) ) {
			$data   = $this->get_data( $data );
			$handle = $this->get_val( $data, 'addon' );
			$update = $this->get_val( $data, 'update', false );
			$addon  = Registry::instance()->get( 'RevSliderAddons' );
			if ( ! empty( $handle ) && strpos( $handle, 'revslider-' ) === false ) {
				$handle = 'revslider-' . $handle . '-addon';
			}

			$return = $addon->install_addon( $handle, $update );

			if ( $return === true ) {
				$addon_data = $addon->get_addon_data( $handle, true );
				//the header listed the <slug>.presets.js files at page load; an add-on installed since has to be added
				$this->ajax_response_data( [ 'addon' => $addon_data, 'presets' => Functions::get_addon_presets() ] );
			}
		}

		$error = ($return === false) ? __('Addon could not be activated', 'revslider') : $return;
		
		$this->ajax_response_error($error);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function deactivate_addon($data = false){
		if ( $this->environment()->user_can( 'activate_plugins' ) ) {
			$data   = $this->get_data( $data );
			$handle = $this->get_val( $data, 'addon' );
			$addon  = Registry::instance()->get( 'RevSliderAddons' );

			if ( $addon->deactivate_addon( $handle ) ) {
				$this->ajax_response_success( __( 'Addon deactivated', 'revslider' ) );
			}
		}
		
		$this->ajax_response_error(__('Addon could not be deactivated', 'revslider'));
	}

	/***********************
	 *  ADDONS FUNCTIONS   *
	 ***********************/
	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_template_short(){
		$templates = new Template();

		$this->ajax_response_data(['templates' => $templates->get_tp_template_sliders()]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function import_template_slider($data = false){
		$data		= $this->get_data($data);
		$uid		= $this->get_val($data, 'uid');
		$templates	= new Template();
		$filepath	= $templates->_download_template($uid);

		if($filepath === false) $this->ajax_response_error(__('Template Slider Import Failed', 'revslider'));
		if(is_array($filepath) && isset($filepath['error'])) $this->ajax_response_error($filepath['error']);

		$islider = new \ThemePunch\SR\Core\Admin\Import();
		$return = $islider->import_slider(false, $filepath, $uid);

		if($this->get_val($return, 'success') == true){
			$new_id = $this->get_val($return, 'sliderID');
			if(intval($new_id) > 0){
				$map = $this->get_val($return, 'map',  []);
				$folder_id = $this->get_val($data, 'folderid', -1);
				if(intval($folder_id) > 0){
					$folder = new Folder();
					$folder->add_slider_to_folder($new_id, $folder_id, false);
				}

				$new_slider = new Slider();
				$new_slider->init_by_id($new_id);

				$owner_post_id = intval($this->get_val($data, 'owner_post_id', 0));
				if($owner_post_id === 0) $owner_post_id = intval($this->get_val($data, 'post_id', 0));
				if($owner_post_id > 0 && $this->environment()->user_can('edit_post', $owner_post_id)){
					$new_slider->set_param('sr7_gb_owner_post_id', $owner_post_id);
					$new_slider->save_params();
				}

				$_data = $new_slider->get_overview_data();

				$templates->_delete_template($uid); //delete template file
				$this->ajax_response_data(['slider' => $_data, 'map' => $map, 'uid' => $uid]);
			}
		}

		$templates->_delete_template($uid); //delete template file
		
		$error = ($this->get_val($return, 'error') !== '') ? $this->get_val($return, 'error') : __('Slider Import Failed', 'revslider');
		$this->ajax_response_error($error);
	}

	/**
	 * Fetch a template package and report what installing it would do, without writing anything.
	 *
	 * The zip is kept where it lands: begin_package_install() reuses it rather than pulling it a second
	 * time, so looking at a package before installing it does not cost the download twice.
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function inspect_package($data = false){
		$data	= $this->get_data($data);
		$uid	= $this->get_val($data, 'uid');

		$templates	= new Template();
		$zip		= $templates->_download_package($uid);
		if(is_array($zip) && isset($zip['error'])) $this->ajax_response_error($zip['error']);

		$installer	= new \ThemePunch\SR\Core\Admin\PackageInstaller();
		$manifest	= $installer->read_manifest($zip);
		if(is_wp_error($manifest)){
			$templates->_delete_package($uid);
			$this->ajax_response_error($manifest->get_error_message());
		}

		$report = $installer->dry_run($manifest, !empty($this->get_val($data, 'modules_only', false)));

		$this->ajax_response_data([
			'uid'		=> $uid,
			'title'		=> $this->get_val($manifest, 'title'),
			'version'	=> $this->get_val($manifest, 'version'),
			'report'	=> $report
		]);
	}

	/**
	 * Open a package install. Expects any Add-Ons the package needs to be installed already — the library
	 * does that first, through the same path the "Install Package & Addons" button has always used, so
	 * the effects a page carries are registered by the time their pages are built.
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function begin_package_install($data = false){
		$data	= $this->get_data($data);
		$uid	= $this->get_val($data, 'uid');

		$templates	= new Template();
		$zip		= $templates->_download_package($uid);
		if(is_array($zip) && isset($zip['error'])) $this->ajax_response_error($zip['error']);

		$installer	= new \ThemePunch\SR\Core\Admin\PackageInstaller();
		$manifest	= $installer->read_manifest($zip);
		if(is_wp_error($manifest)) $this->ajax_response_error($manifest->get_error_message());

		$begun = $installer->begin($manifest, $zip, !empty($this->get_val($data, 'modules_only', false)));
		if(is_wp_error($begun)) $this->ajax_response_error($begun->get_error_message());

		$templates->_delete_package($uid); //begin() has unpacked it; the download is no longer needed

		$this->ajax_response_data($begun);
	}

	/**
	 * Run the next phase of an open package install. One phase per request: a package with five pages and
	 * forty images does not finish inside one PHP timeout.
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function step_package_install($data = false){
		$data		= $this->get_data($data);
		$install_id	= $this->get_val($data, 'install_id');

		$installer	= new \ThemePunch\SR\Core\Admin\PackageInstaller();
		$result		= $installer->step($install_id);

		if(is_wp_error($result)) $this->ajax_response_error($result->get_error_message());

		$this->ajax_response_data($result);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_package_installs(){
		$installer = new \ThemePunch\SR\Core\Admin\PackageInstaller();

		$this->ajax_response_data(['installs' => $installer->get_installs()]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_package_uninstall_report($data = false){
		$data	= $this->get_data($data);
		$uid	= $this->get_val($data, 'uid');
		$index	= $this->get_val($data, 'index', null);

		$installer	= new \ThemePunch\SR\Core\Admin\PackageInstaller();
		$report		= $installer->uninstall_report($uid, ($index === null || $index === '') ? null : intval($index));

		if(is_wp_error($report)) $this->ajax_response_error($report->get_error_message());

		$this->ajax_response_data(['report' => $report]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function uninstall_package($data = false){
		$data	= $this->get_data($data);
		$uid	= $this->get_val($data, 'uid');
		$index	= $this->get_val($data, 'index', null);

		$installer	= new \ThemePunch\SR\Core\Admin\PackageInstaller();
		$removed	= $installer->uninstall($uid, ($index === null || $index === '') ? null : intval($index));

		if(is_wp_error($removed)) $this->ajax_response_error($removed->get_error_message());

		$this->ajax_response_data(['removed' => $removed]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function import_template_slide($data = false){
		$data		= $this->get_data($data);
		$slider_id	= intval($this->get_val($data, 'slider_id'));
		$slide_id	= intval($this->get_val($data, 'slide_id'));

		if($slider_id == 0 || $slide_id == 0) $this->ajax_response_error(__('Slide duplication failed', 'revslider'));
	
		$new_slide_id = $this->slide->duplicate_slide_by_id($slide_id, $slider_id);

		if($new_slide_id === false) $this->ajax_response_error(__('Slide duplication failed', 'revslider'));
	
		$this->slide->init_by_id($new_slide_id);
		$_slides[] = [
			'id'	 => $this->slide->get_id(),
			'layers' => $this->slide->get_layers(),
			'params' => $this->slide->get_params(),
			'order'  => $this->slide->get_order(),
		];

		$this->ajax_response_data(['slides' => $_slides]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function import_template_media($data = false) {
		$data		= $this->get_data($data);
		$uid		= $this->get_val($data, 'uid');
		$alias		= $this->get_val($data, 'alias');
		$media		= $this->get_val($data, 'media');

		$templates	= new Template();
		$filepath	= $templates->_download_template($uid);

		if($filepath === false) $this->ajax_response_error(__('Template Slider Import Failed', 'revslider'));
		if(is_array($filepath) && isset($filepath['error'])) $this->ajax_response_error($filepath['error']);

		$islider = new \ThemePunch\SR\Core\Admin\Import();
		$exec = $islider->unzip_slider($filepath);
		if($exec !== true){
			$templates->_delete_template($uid);
			$this->ajax_response_error(__('Template Slider Import Failed', 'revslider'));
		}

		$islider->mode = 7;
		$islider->slider_data['alias'] = $alias;
		foreach ($media as $lid => $layerMedia) {
			foreach ($layerMedia as $path => $mediaFile) {
				$media[$lid][$path] = $islider->import_media_from_zip($mediaFile);
			}
		}

		$templates->_delete_template($uid);

		$this->ajax_response_data(['media' => $media]);
	}

	/***********************
	 * NAVIGATION FUNCTIONS *
	 ***********************/
	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function create_navigation_preset($data = false){
		$data	= $this->get_data($data);
		$nav	= new Navigation();
		$return = $nav->add_preset($data);

		if($return === true) $this->ajax_response_success(__('Navigation preset saved/updated', 'revslider'));
		if($return === false) $this->ajax_response_error(__('Preset could not be saved/values are the same', 'revslider'));

		$this->ajax_response_error($return);
	}

	/**
	 * Progress Bar presets. The Progress Bar has no Skin row to hang them off the way a Navigation preset does,
	 * so they live in the shared options bucket next to the ones the factory Navigation skins keep there.
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function save_pbar_preset($data = false){
		$data	= $this->get_data($data);
		$handle	= sanitize_key($this->get_val($data, 'handle', ''));
		$values	= $this->get_val($data, 'values', false);
		if($handle === '' || !is_array($values)) $this->ajax_response_error(__('Progress bar preset could not be saved', 'revslider'));

		$presets = $this->get_options(['presets', 'pbar'], []);
		if(!is_array($presets)) $presets = [];
		$old = sanitize_key($this->get_val($data, 'old_handle', ''));
		if($old !== '' && $old !== $handle) unset($presets[$old]);

		$presets[$handle] = [
			'name'	 => esc_attr($this->get_val($data, 'name', $handle)),
			'values' => $values
		];
		$this->update_option(['presets', 'pbar'], $presets);
		$this->ajax_response_success(__('Progress bar preset saved', 'revslider'), ['presets' => $presets]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function delete_pbar_preset($data = false){
		$data	 = $this->get_data($data);
		$handle	 = sanitize_key($this->get_val($data, 'handle', ''));
		$presets = $this->get_options(['presets', 'pbar'], []);
		if(!is_array($presets) || !isset($presets[$handle])) $this->ajax_response_error(__('Progress bar preset not found', 'revslider'));

		unset($presets[$handle]);
		$this->update_option(['presets', 'pbar'], $presets);
		$this->ajax_response_success(__('Progress bar preset deleted', 'revslider'), ['presets' => $presets]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function delete_navigation_preset($data = false){
		$data	= $this->get_data($data);
		$nav	= new Navigation();
		$return	= $nav->delete_preset($data);

		if($return === true) $this->ajax_response_success(__('Navigation preset deleted', 'revslider'));
		if($return === false) $this->ajax_response_error(__('Preset not found', 'revslider'));

		$this->ajax_response_error($return);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function save_navigation($data = false){
		if($this->current_user_can_not('administrator')){
			$this->ajax_response_error(__('Function only available for administrators', 'revslider'));
			exit;
		}

		$data		= $this->get_data($data);
		$_nav		= new Navigation();
		$navigation	= $this->get_val($data, 'skin', []);
		$return		= $_nav->create_update_full_navigation($navigation);

		if($return === true) $this->ajax_response_success(__('Navigation saved', 'revslider'));
		if($return === false) $this->ajax_response_error(__('Navigation could not be saved', 'revslider'));
		if(intval($return) > 0) $this->ajax_response_data(['id' => $return]);

		$this->ajax_response_error($return);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function delete_navigation($data = false){
		if($this->current_user_can_not('administrator')){
			$this->ajax_response_error(__('Function only available for administrators', 'revslider'));
			exit;
		}

		$data		 = $this->get_data($data);
		$_nav		 = new Navigation();
		$navigation	 = $this->get_val($data, 'skins', 0);
		if(empty($navigation) || $navigation === 0) $navigation = $this->get_val($data, 'skin', 0);

		$_nav->delete_navigation($navigation);

		$this->ajax_response_success(__('Navigation deleted', 'revslider'));
	}
	
	
	/***********************
	 * ANIMATION FUNCTIONS *
	 ***********************/
	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function delete_animation($data = false){
		$sr_admin	= $this->admin_functions();
		$data		= $this->get_data($data);
		$aid		= $this->get_val($data, 'id');
		$return		= $sr_admin->delete_animation($aid);
		if($return) $this->ajax_response_success(__('Animation deleted', 'revslider'));
		$this->ajax_response_error(__('Deletion failed', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function save_animation($data = false){
		$sr_admin	= $this->admin_functions();
		$data		= $this->get_data($data);
		$id			= $this->get_val($data, 'id', false);
		$type		= $this->get_val($data, 'type', 'in');
		$animation	= $this->get_val($data, 'obj');
		$return		= ($id !== false) ? $sr_admin->update_animation($id, $animation, $type) : $sr_admin->insert_animation($animation, $type);

		if(intval($return) > 0) $this->ajax_response_data(['id' => $return]);
		if($return === true) $this->ajax_response_success(__('Animation saved', 'revslider'));
		if($return == false) $this->ajax_response_error(__('Animation could not be saved', 'revslider'));
		
		$this->ajax_response_error($return);
	}

	/***********************
	 *  STREAM FUNCTIONS   *
	 ***********************/
	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function facebook_loginurl($data = false){
		$data		= $this->get_data($data);
		$id			= $this->get_val($data, 'id');
		$slide_id	= $this->get_val($data, 'slide_id');
		$url		= External\Facebook::get_login_url($id, $slide_id);
		if($url === false) $this->ajax_response_error(__('Slider ID or Slide ID unavailable', 'revslider'));

		$this->ajax_response_data(['url' => $url]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function facebook_photosets($data = false){
		$data		= $this->get_data($data);
		$app_id		= $this->get_val($data, 'app_id');
		$page_id	= $this->get_val($data, 'page_id');

		if(empty($app_id)) $this->ajax_response_error(__('Facebook API error: Empty Access Token', 'revslider'));
		if(empty($page_id)) $this->ajax_response_error(__('Facebook API error: Empty Page ID', 'revslider'));

		$facebook	= Registry::instance()->get(External\Facebook::class);
		$return		= $facebook->get_photo_set_photos_options($app_id, $page_id);

		if(empty($return)) $this->ajax_response_error(__('Could not fetch Facebook albums', 'revslider'));
		if(!empty($return['error'])) $this->ajax_response_error(__('Facebook API error: ', 'revslider') . $return['message']);

		$this->ajax_response_success(__('Successfully fetched Facebook albums', 'revslider'), ['data' => $return]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function instagram_loginurl($data = false){
		$data		= $this->get_data($data);
		$id			= $this->get_val($data, 'id');
		$slide_id	= $this->get_val($data, 'slide_id');
		$url		= External\Instagram::get_login_url($id, $slide_id);
		if($url === false) $this->ajax_response_error(__('Slider ID or Slide ID unavailable', 'revslider'));

		$this->ajax_response_data(['url' => $url]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function youtube_playlists($data = false){
		$data		= $this->get_data($data);
		$api		= trim($this->get_val($data, 'api'));
		$id			= trim($this->get_val($data, 'id'));
		if(empty($id)) $this->ajax_response_error(__('Could not fetch YouTube playlists', 'revslider'));
		$youtube	= new \ThemePunch\SR\Core\External\Youtube($api, $id);
		$return		= $youtube->get_playlist_options();

		if(!empty($this->get_val($return, 'error', []))) $this->ajax_response_error(__('Facebook API error: ', 'revslider') . $this->get_val($return, ['error', 'message'], []));

		$this->ajax_response_success(__('Successfully fetched YouTube playlists', 'revslider'), ['data' => $return]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function flickr_photosets($data = false){
		$data	= $this->get_data($data);
		$url	= $this->get_val($data, 'url');
		$key	= $this->get_val($data, 'key');
		$count	= $this->get_val($data, 'count');
		if(empty($url) && empty($key)) $this->ajax_response_success(__('Cleared Albums', 'revslider'), ['data' => []]);
		if(empty($url)) $this->ajax_response_error(__('No User URL - Could not fetch flickr albums', 'revslider'));
		if(empty($key)) $this->ajax_response_error(__('No API Key - Could not fetch flickr albums', 'revslider'));

		$flickr		= new \ThemePunch\SR\Core\External\Flickr($key);
		$user_id	= $flickr->get_user_from_url($url);
		$return		= $flickr->get_photo_sets($user_id, $count);
		if(empty($return)) $this->ajax_response_error(__('Could not fetch flickr albums', 'revslider'));

		$this->ajax_response_success(__('Successfully fetched flickr albums', 'revslider'), ['data' => $return]);
	}

	/***********************
	 * PLUGIN FUNCTIONS    *

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_plugin_settings(){
		$this->ajax_response_data(['settings' => $this->global_settings]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_plugin_sliders(){
		$sliders	= $this->slider->get_sliders();
		$return		= [];
		foreach($sliders ?? [] as $s){
			$return[$s->get_id()] = [
				'slug'		=> $s->get_alias(),
				'title'		=> $s->get_title(),
				'type'		=> $s->get_type(),
				'subtype'	=> $s->get_param(['source', 'post', 'subType'], false)
			];
		}
		$this->ajax_response_data(['sliders' => $return]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function save_plugin_settings($data = false){
		$data	= $this->get_data($data);
		$global = $this->get_val($data, 'settings', []);
		if(empty($global)) $this->ajax_response_error(__('No settings sent', 'revslider'));

		//sync uploaded custom fonts: (re)build their @font-face css + prune removed ones
		if(isset($global['fonts']['list'])){
			$rsf		= Registry::instance()->get('RevSliderFonts');
			$old_list	= $this->get_val($this->global_settings, ['fonts', 'list'], []);
			$global['fonts']['list'] = $rsf->sync_uploaded_fonts($global['fonts']['list'], $old_list);
		}

		//a shortened cache lifetime would only take hold once the old entries expired, so drop them on change;
		//the same for a changed Brand Kit, the cached markup holds the module background it resolved to
		$old_time = intval($this->get_val($this->global_settings, ['opt', 'icachetime'], WEEK_IN_SECONDS));
		$new_time = intval($this->get_val($global, ['opt', 'icachetime'], WEEK_IN_SECONDS));
		$old_brand = json_encode($this->get_val($this->global_settings, 'brand', []));
		if($old_time !== $new_time || $old_brand !== json_encode($this->get_val($global, 'brand', []))) Registry::instance()->get('RevSliderCache')->clear_all_transients();

		//the dialog only edits trackOnOff, keep the status the loader reads in step with it
		if(isset($global['trackOnOff'])){
			$global['track'] = ($this->_truefalse($global['trackOnOff']) === true) ? 'enabled' : '1999-01-01';
		}

		//never store a gappy breakpoint list: the client drops emptied/null entries on the way here, which leaves a
		//keyed object instead of a 5 slot list and made the Global Settings modal unopenable on the next load
		if(isset($global['breakPoints'])){
			$slots = [1920, 1240, 1024, 778, 480];
			foreach($slots as $i => $default){
				$sent = intval($this->get_val($global['breakPoints'], $i, 0));
				$slots[$i] = ($sent > 0) ? $sent : $default;
			}
			$global['breakPoints'] = $slots;
		}

		$update = $this->get_val($data, 'update', false);
		$return = $this->set_global_settings($global, $update);
		if($return === true) $this->ajax_response_success(__('Global Settings saved/updated', 'revslider'), ['settings' => $global]);

		$this->ajax_response_error(__('Global Settings not saved/updated', 'revslider'));
	}

	/**
	 * handle a single custom font file upload (Global Settings > Fonts > Custom Fonts)
	 * the file is stored now; the generated @font-face css is (re)built on the following settings save
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function upload_custom_font(){
		$family = $this->get_request_var('family', '');
		if(is_array($family)) $family = reset($family);
		$family = trim((string)$family);
		if($family === '') $this->ajax_response_error(__('Please enter a font family name first', 'revslider'));

		$rsf = Registry::instance()->get('RevSliderFonts');
		$res = $rsf->store_uploaded_font_file($family);
		if($this->get_val($res, 'error', false) !== false) $this->ajax_response_error($this->get_val($res, 'error'));

		$this->ajax_response_data([
			'file'		=> $this->get_val($res, 'file'),
			'url'		=> $this->get_val($res, 'url'),
			'ext'		=> $this->get_val($res, 'ext'),
			'weight'	=> $this->get_val($res, 'weight'),
			'style'		=> $this->get_val($res, 'style')
		]);
	}

	/**
	 * delete the stored files/folder of an uploaded custom font (Global Settings > Fonts > Custom Fonts)
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function delete_custom_font_files($data = false){
		$data	= $this->get_data($data);
		$family = trim((string)$this->get_val($data, 'family', ''));
		if($family === '') $this->ajax_response_error(__('No font family sent', 'revslider'));

		$rsf = Registry::instance()->get('RevSliderFonts');
		$rsf->delete_custom_font($family);
		$this->ajax_response_success(__('Custom font removed', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function save_plugin_tooltip(){
		$this->update_option(['system', 'tooltips'], true);
		$this->ajax_response_success(__('Preference Updated', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function dismiss_plugin_deregister(){
		$this->update_option(['system', 'deregister'], 'false');
		$this->ajax_response_success(__('Saved', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function dismiss_plugin_trustpilot(){
		$this->update_option(['system', 'trustpilot'], 'false');
		$this->ajax_response_success(__('Saved', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function dismiss_plugin_notice($data = false){
		$data	= $this->get_data($data);
		$ids	= $this->get_val($data, 'id', []);
		$disc	= $this->get_options(['overview', 'notices-dc'], []);
		if(empty($ids)) $this->ajax_response_success(__('Saved', 'revslider'));

		//fix if notices was pushed into notices-dc 
		if(!empty($disc)){
			$first = reset($disc);
			if($first instanceof \stdClass) $disc = [];
		}

		if(!is_array($ids)) $ids = (array)$ids;

		foreach($ids ?? [] as $_id){
			$disc[] = esc_attr(trim($_id));
		}
		
		$this->update_option(['overview', 'notices-dc'], $disc);
		$this->ajax_response_success(__('Saved', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function check_plugin_system(){
		$sr_admin = $this->admin_functions();
		$update = new Update(RS_REVISION);
		$update->force = true;
		$update->_retrieve_version_info();
		$system = $sr_admin->get_system_requirements();

		$last_request = Registry::instance()->get('RevSliderLoadBalancer')->get_last_request();
		if ( is_wp_error($last_request) ) {
			$system['server_error'] = $last_request->get_error_message();
		}
		
		$this->ajax_response_data(['system' => $system]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function plugin_database_check(){
		$missing = Schema::check_tables();
		if (empty($missing)) $this->ajax_response_success(__('Slider Revolution database structure is up to date', 'revslider'));
		
		$this->ajax_response_error(
			sprintf(
				__('Slider Revolution database structure error. Missing %s', 'revslider'),
				implode(', ', $missing)
			)
		);
	}
	
	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function plugin_database_force(){
		$this->update_option(['system', 'table'], '1.0.0');
		Schema::create_tables();
		$this->ajax_response_success(__('Slider Revolution database structure was updated', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function delete_plugin_cache(){
		$cache = Registry::instance()->get('RevSliderCache');
		$cache->clear_all_transients();
		
		$this->ajax_response_success(__('Slider Revolution internal cache was fully cleared', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function activate_plugin($data = false){
		$data	= $this->get_data($data);
		$code	= trim($this->get_val($data, 'code'));
		$lic	= new License();
		
		if(empty($code)) $this->ajax_response_error(__('The License Key needs to be set!', 'revslider'));

		$result = $lic->activate_plugin($code);

		if($result === true)	$this->ajax_response_success(__('Plugin successfully activated', 'revslider'));
		if($result == 'exist')	$this->ajax_response_error(__('License Key already registered!', 'revslider'));
		if($result == 'banned')	$this->ajax_response_error(__('License Key was locked, please contact the ThemePunch support!', 'revslider'));
		if($result === 'legacy'){
			if(!$this->free_core_open()) $this->ajax_response_error(__('License Key is invalid', 'revslider'));
			//kept unregistered, for the dashboard's note (ADR-0027); the answer does not tell lifetime from CodeCanyon, both get one note
			$this->update_option(['system', 'license'], $code);
			$this->update_option(['system', 'license_type'], 'lifetime');
			$this->ajax_response_error(__('This lifetime or CodeCanyon key keeps Slider Revolution 7 and every update free for you. Premium add-ons and templates need an SR7 licence, with 50 % off the first year.', 'revslider'), ['licence' => 'lifetime']);
		}
		if($result === false){
			$last_request = Registry::instance()->get('RevSliderLoadBalancer')->get_last_request();
			if ( is_wp_error($last_request) ) {
				$this->ajax_response_error( $last_request->get_error_message() );
			}
			$this->ajax_response_error(__('License Key is invalid', 'revslider'));
		}
		
		$this->ajax_response_error(__('License Key could not be validated', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function deactivate_plugin(){
		$lic = new License();
		if($lic->deactivate_plugin()) $this->ajax_response_success(__('Plugin deregistered', 'revslider'));

		$last_request = Registry::instance()->get('RevSliderLoadBalancer')->get_last_request();
		if ( is_wp_error($last_request) ) {
			$this->ajax_response_error( $last_request->get_error_message() );
		}
		
		$this->ajax_response_error(__('Deregistration failed!', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function upgrade_check_plugin(){
		$update = new Update(RS_REVISION);
		$update->force = true;
		
		$update->_retrieve_version_info();
		
		$last_request = Registry::instance()->get('RevSliderLoadBalancer')->get_last_request();
		if ( is_wp_error($last_request) ) {
			$this->ajax_response_error( $last_request->get_error_message() );
		}
		
		$version = $this->get_options(['system', 'version'], RS_REVISION);
		if($version !== false) $this->ajax_response_data(['version' => $version]);
		
		$this->ajax_response_error(__('Connection to Update Server Failed', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function subscribe_plugin($data = false){
		$data	= $this->get_data($data);
		$email	= $this->get_val($data, 'email');
		if(empty($email)) $this->ajax_response_error(__('No Email given', 'revslider'));
		
		$return = Newsletter::subscribe($email);

		if($return === false) $this->ajax_response_error(__('Invalid Email/Could not connect to the Newsletter server', 'revslider'));
		if(!isset($return['status']) || $return['status'] === 'error'){
			$error = $this->get_val($return, 'message', __('Invalid Email', 'revslider'));
			$this->ajax_response_error($error);
		}
		
		$this->ajax_response_success(__('Success! Please check your E-Mails to finish the subscription', 'revslider'), $return);
	}

	/** @return array */
	protected function get_plugin_panel_modal_html($handle, $map, $filter_name, $type){
		$do_filter  = apply_filters($filter_name, ['map' => $map, 'path' => RS_PLUGIN_PATH . 'core/admin/includes/modals/'], $handle);
		$map		= $this->get_val($do_filter, 'map');
		$path		= $this->get_val($do_filter, 'path');

		// Check if the handle exists in the map
		if(!isset($map[$handle])) {
			return [
				'error' => true,
				'message' => sprintf(__('%s "%s" does not exist', 'revslider'), $type, $handle),
			];
		}

		ob_start();
		try{
			require($path . $map[$handle]); //never _once: two handles share slide/settings.php and guides/editor.php, the second would render empty
		}finally{
			$html = ob_get_clean(); //always close the buffer, even if the included file throws
		}
		if(empty($html)) {
			return [
				'error' => true,
				'message' => sprintf(__('%s "%s" could not be loaded', 'revslider'), $type, $handle),
			];
		}

		return [
			'error' => false,
			'handle' => $handle,
			'html' => $html,
		];
	}

	/** @return array panel handle => partial under core/admin/includes/modals/, the core's own; add-ons join through revslider_modify_panel_map */
	public function panel_map(){
		return [
			/*'sr_add_text_heading' => 'add/text_heading.php',
			'sr_add_text_content' => 'add/text_content.php',*/
			'sr_ai_panel' => 'ai/generator.php',
			'sr_elements_text' => 'elements/text.php',
			'sr_elements_counter' => 'elements/counter.php',
			'sr_elements_field' => 'elements/field.php',
			'sr_elements_highlight' => 'elements/highlight.php',
			'sr_elements_buttonpresets' => 'elements/buttonpresets.php',
			'sr_elements_svgstyle' => 'elements/svgstyle.php',
			'sr_elements_textstyle' => 'elements/textstyle.php',
			'sr_elements_lineheight' => 'elements/lineheight.php',
			'sr_elements_slidebg' => 'elements/slidebg.php',
			'sr_elements_image' => 'elements/image.php',
			'sr_elements_svg' => 'elements/svg.php',
			'sr_elements_audio' => 'elements/audio.php',
			'sr_elements_video' => 'elements/video.php',
			'sr_elements_overlay' => 'elements/overlay.php',
			'sr_elements_customshape' => 'elements/customshape.php',
			'sr_elements_customshapestyle' => 'elements/customshapestyle.php',
			'sr_elements_background' => 'elements/background.php',
			'sr_elements_parallax' => 'elements/parallax.php',
			'sr_elements_border' => 'elements/border.php',
			'sr_elements_spacing' => 'elements/spacing.php',
			'sr_elements_position' => 'elements/position.php',
			'sr_elements_behavior' => 'elements/behavior.php',
			'sr_elements_structure' => 'elements/structure.php',
			'sr_elements_responsive' => 'elements/responsive.php',
			'sr_elements_gtransform' => 'elements/gtransform.php',
			'sr_elements_attr' => 'elements/attributes.php',
			'sr_elements_acc' => 'elements/accessibility.php',
			'sr_elements_visibility' => 'elements/visibility.php',
			'sr_elements_bshadow' => 'elements/boxshadow.php',
			'sr_elements_filters' => 'elements/filters.php',
			'sr_elements_surface' => 'elements/surface.php',
			'sr_elements_tshadow' => 'elements/textshadow.php',
			'sr_elements_sshadow' => 'elements/svgshadow.php',
			'sr_elements_xstyle' => 'elements/xstyle.php',
			'sr_elements_toggle' => 'elements/toggle.php',
			'sr_elements_hover' => 'elements/hover.php',
			'sr_elements_accordion' => 'elements/accordion.php',
			'sr_elements_frames' => 'animation/frames.php',
			'sr_elements_presets' => 'animation/presets.php',
		];
	}

	/**
	 * @param string $handle Panel handle
	 * @return array
	 */
	public function get_plugin_panel_html($handle){
		return $this->get_plugin_panel_modal_html($handle, $this->panel_map(), 'revslider_modify_panel_map', 'Panel');
	}

	/** @return array modal handle => partial under core/admin/includes/modals/, the core's own; add-ons join through revslider_modify_modal_map */
	public function modal_map(){
		return [
			'sr_embed_code'			=> 'embed.php',
			'sr_meta_editing'		=> 'meta.php',
			'sr_module_trash'		=> 'trash.php',
			'template_library'		=> 'templates.php',
			'addon_library'			=> 'addons.php',
			'effects_library'		=> 'effects.php',
			'block_settings'		=> 'block_settings.php',
			'element_library'		=> 'elements.php',
			'modules'				=> 'modules.php',
			'module_layers'			=> 'module_layers.php',
			'sr_colorpicker_modal'	=> 'colorpicker.php',
			'sr_ai_txt'				=> 'ai/text.php',
			'sr_ai_bgremoval'		=> 'ai/bgremoval.php',
			'sr_copyright'			=> 'copyright.php',
			'sr_new_scene'			=> 'animation/newscene.php',
			'sr_scene_transfer'		=> 'animation/scenetransfer.php',
			'sr_globals'			=> 'globals.php',
			'sr_new_guide'			=> 'guides/new.php',
			'sr_editor_guide'		=> 'guides/editor.php',
			'sr_editor_step99'		=> 'guides/editor.php',
			'sr_staticslidesettings'=> 'slide/settings.php',
			'sr_slidesettings'		=> 'slide/settings.php',
			'sr_actions'			=> 'actions.php',
			'sr_module_general'		=> 'module/general.php',
			'sr_module_scroll'		=> 'module/scroll.php',
			'sr_module_story'		=> 'module/story.php',
			'sr_module_style'		=> 'module/style.php',
			'sr_module_acc'			=> 'module/accessibility.php',
			'sr_module_a11y'		=> 'module/a11ycheck.php',
			'sr_module_browser'		=> 'module/browser.php',
			'sr_module_carousel'	=> 'module/carousel.php',
			'sr_module_modal'		=> 'module/modal.php',
			'sr_module_navigation'	=> 'module/navigation.php',
			'sr_module_arrows'		=> 'module/navigation/arrows.php',
			'sr_module_bullets'		=> 'module/navigation/bullets.php',
			'sr_module_scrubber'	=> 'module/navigation/scrubber.php',
			'sr_module_tabs'		=> 'module/navigation/tabs.php',
			'sr_module_thumbs'		=> 'module/navigation/thumbs.php',
			'sr_module_scripts'		=> 'module/scripts.php',
			'sr_module_shortcuts'	=> 'module/shortcuts.php',
			'sr_module_aislide'		=> 'ai/slide.php',
			'sr_module_source'		=> 'module/srcs.php',
			'sr_module_slideshow'	=> 'module/slideshow.php',
		];
	}

	/**
	 * @param string $handle Modal handle
	 * @return array
	 */
	public function get_plugin_modal_html($handle){
		return $this->get_plugin_panel_modal_html($handle, $this->modal_map(), 'revslider_modify_modal_map', 'Modal');
	}

	/**
	 * @return string
	 */
	public function get_popups_markup($data = []){
		ob_start();
		try{
			require_once(RS_PLUGIN_PATH . 'core/admin/views/popups.php');
		}finally{
			$popups = ob_get_clean(); //always close the buffer, even if the included file throws
		}
		//sr-light-theme pins the palette: these dialogs are drawn on WordPress and on other builders'
		//screens, where following the SR7 app into dark mode reads as a mistake rather than as a theme.
		$html = '<div class="sr--block--editor--popup--wrap sr-light-theme" style="display:none;">' . $popups . '</div>';
		if (!isset($data['skipIcons'])) {
			$html .= Functions::get_sprite_svg();
		}
		$html .= BuilderApi::shortcode_javascript(true);
		$html .= BuilderApi::shortcode_extended_markup();
		$html .= '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">';
		return $html;
	}

	/***********************
	 *  LIBRARY FUNCTIONS  *
	 ***********************/
	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function load_library_object($data = false){
		$data	 = $this->get_data($data);
		/* @var RevSliderObjectLibrary $library */
		$library = Registry::instance()->get('RevSliderObjectLibrary');
		$cover   = false;
		$id      = $this->get_val($data, 'id');
		$type    = $this->get_val($data, 'type');
		
		if (strpos($id, '-')) {
			// ID could come in the format of "546-videos"
			$id = explode('-', $id)[0];
		}
		
		if($type == 'thumb'){
			$thumb = $library->_get_object_thumb($id, 'thumb');
		}elseif($type == 'video'){
			$thumb = $library->_get_object_thumb($id, 'video_full', true);
			$cover = $library->_get_object_thumb($id, 'cover', true);
		}elseif($type == 'layers'){
			$thumb = $library->_get_object_layers($id);
		}else{
			$thumb = $library->_get_object_thumb($id, 'orig', true);
			if(isset($thumb['error']) && $thumb['error'] === false){
				$url = $library->get_correct_size_url($id, $type);
				if($url !== '') $thumb['url'] = $url;
			}
		}

		if(isset($thumb['error']) && $thumb['error'] !== false) $this->ajax_response_error(__('Object could not be loaded', 'revslider'));
		$return = ($type == 'layers') ? ['layers' => $this->get_val($thumb, 'data')] : ['url' => $this->get_val($thumb, 'url')];

		if($cover !== false){
			if(isset($cover['error']) && $cover['error'] !== false) $this->ajax_response_error(__('Video cover could not be loaded', 'revslider'));

			$return['cover'] = $this->get_val($cover, 'url');
		}

		$this->ajax_response_data($return);
	}

	/**
	 * @param array $data
	 * @param bool $return return items or send ajax response
	 *
	 * @return array|void
	 */
	public function load_library_image($data = false, $return = false){
		$data	= $this->get_data($data);
		$images	= (!is_array($data)) ? (array)$data : $data;
		$images	= Functions::esc_attr_deep($images);
		$images	= Functions::esc_js_deep($images);
		$img_data = [];
		
		if(empty($images)) {
			if ($return) return $img_data;
			$this->ajax_response_data(['data' => $img_data]);
		}

		/**
		 * @var RevSliderAddons $addons 
		 * @var RevSliderObjectLibrary $obj 
		 * @var RevSliderFavorite $favorite 
		 */
		$addons		= Registry::instance()->get('RevSliderAddons');
		$obj		= Registry::instance()->get('RevSliderObjectLibrary');
		$favorite	= Registry::instance()->get('RevSliderFavorite');
		$templates	= new Template();

		foreach($images ?? [] as $image){
			$type = $this->get_val($image, 'librarytype');
			$img  = $this->get_val($image, 'id');
			$id   = $this->get_val($image, 'oid');
			$mt   = $this->get_val($image, 'mediatype');
			$dims = [];
			
			if(empty($img)) continue;

			if(in_array($type, ['moduletemplates', 'moduletemplateslides'])){
				if($mt === 'video'){
					$img = $templates->_check_file_path($img, true, true, true);
				}else{
					$img = $templates->_check_file_path($img, true);
				}
			}elseif(in_array($type, ['image', 'images', 'layers', 'objects', 'elements'])){
				$get = ($mt === 'video') ? 'video_thumb' : 'thumb';
				$img = $obj->_get_object_thumb($img, $get, true);
				if ($this->get_val($img, 'error', false) !== false) continue;
				$dims = [
					'w' => $this->get_val($img, 'width'),
					'h' => $this->get_val($img, 'height')
				];
				$img = $this->get_val($img, 'url');
				
				if($this->get_val($img, 'error', false) !== false) continue;
			}elseif($type === 'videos'){
				$get = ($mt === 'img') ? 'video' : 'video_thumb';
				$img = $obj->_get_object_thumb($img, $get, true);
				if ($this->get_val($img, 'error', false) !== false) continue;
				$img = $this->get_val($img, 'url');
				if($this->get_val($img, 'error', false) !== false) continue;
			}elseif($type === 'addons'){
				$img = $addons->_get_media_url($img);
				if($this->get_val($img, 'error', false) !== false) continue;
			}else{
				continue;
			}

			$imgData = [
				'ind'		=> $this->get_val($image, 'ind'),
				'url'		=> $img,
				'mediatype'	=> $mt,
				'favorite'	=> $favorite->is_favorite($type, $id)
			];

			if($type === 'elements' && !empty($dims['w']) && !empty($dims['h'])) $imgData = array_merge($imgData, $dims);

			$img_data[] = $imgData;
		}

		if ($return) return $img_data;
		
		$this->ajax_response_data(['data' => $img_data]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function create_library_tag($data = false){
		$data	= $this->get_data($data);
		/* @var RevSliderObjectLibrary $obj */
		$obj	= Registry::instance()->get('RevSliderObjectLibrary');
		$name	= $this->get_val($data, 'name');
		$type	= $this->get_val($data, 'type');
		$return = $obj->create_custom_tag($name, $type);

		if(!is_array($return)) $this->ajax_response_error($return);	
		
		$this->ajax_response_data($return);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function create_library_item($data = false){
		$data	= $this->get_data($data);
		/* @var RevSliderObjectLibrary $obj */
		$obj	= Registry::instance()->get('RevSliderObjectLibrary');
		$return = $obj->upload_custom_item($data);
		
		if(!is_array($return)) $this->ajax_response_error($return);
		
		$return['tags'] = $this->get_val($obj->get_custom_tags(), 'svgcustom', []);
		$this->ajax_response_data($return);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function save_library_tag($data = false){
		$data	= $this->get_data($data);
		/* @var RevSliderObjectLibrary $obj */
		$obj	= Registry::instance()->get('RevSliderObjectLibrary');
		$id		= $this->get_val($data, 'id');
		$name	= $this->get_val($data, 'name');
		$type	= $this->get_val($data, 'type');
		$return = $obj->edit_custom_tag($id, $name, $type);

		if($return !== true) $this->ajax_response_error($return);	
		
		$this->ajax_response_success(__('Tag successfully saved', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function save_library_item($data = false){
		$data	= $this->get_data($data);
		/* @var RevSliderObjectLibrary $obj */
		$obj	= Registry::instance()->get('RevSliderObjectLibrary');
		$id		= $this->get_val($data, 'id');
		$type	= $this->get_val($data, 'type');
		$name	= $this->get_val($data, 'name');
		$tags	= $this->get_val($data, 'tags');
		$return = $obj->edit_custom_item($id, $type, $name, $tags);

		if($return !== true) $this->ajax_response_error(__('Item could not be changed', 'revslider'));	

		$this->ajax_response_success(__('Item successfully changed', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function delete_library_tag($data = false){
		$data	= $this->get_data($data);
		/* @var RevSliderObjectLibrary $obj */
		$obj	= Registry::instance()->get('RevSliderObjectLibrary');
		$id		= $this->get_val($data, 'id');
		$type	= $this->get_val($data, 'type');
		$return = $obj->delete_custom_tag($id, $type);

		if($return !== true) $this->ajax_response_error($return);	

		$this->ajax_response_success(__('Tag successfully deleted', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function delete_library_item($data = false){
		$data	= $this->get_data($data);
		/* @var RevSliderObjectLibrary $obj */
		$obj	= Registry::instance()->get('RevSliderObjectLibrary');
		$id		= $this->get_val($data, 'id');
		$type	= $this->get_val($data, 'type');
		$return = $obj->delete_custom_item($id, $type);

		if($return !== true) $this->ajax_response_error(__('Item could not be deleted', 'revslider'));
		
		$this->ajax_response_success(__('Item successfully deleted', 'revslider'));
	}

	/***********************
	 *	 EDITOR FUNCTIONS  *
	 ***********************/
	/**
	 * get the editor data including plugin settings, slider & slides
	 *
	 * @param array $data
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function editor_get_all($data = false){
		$module = $this->do_get_module($data);
		if (!is_array($module)) $this->ajax_response_error($module);

		$slides = $this->do_get_slides($data);
		if (!is_array($slides)) $this->ajax_response_error($slides);

		$this->ajax_response_data([
			'id'     => $module['id'],
			'global' => $this->global_settings,
			'module' => $module,
			'slides' => $slides,
		]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function save_editor_color($data = false){
		$data	= $this->get_data($data);
		$presets = $this->get_val($data, 'presets', []);

		$this->ajax_response_data(['presets' => Colorpicker::save_color_presets($presets)]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function save_editor_slidetransitions($data = false){
		$data	= $this->get_data($data);
		$return = $this->save_custom_slidetransitions($data);
		if($return === false || intval($return) === 0) $this->ajax_response_success(__('Slide transition template could not be saved', 'revslider'));
		
		$this->ajax_response_success(__('Slide transition template saved', 'revslider'), ['data' => ['id' => $return]]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function delete_editor_slidetransitions($data = false){
		$data = $this->get_data($data);
		if($this->delete_custom_slidetransitions($data)) $this->ajax_response_success(__('Slide transition template deleted', 'revslider'));
		$this->ajax_response_error(__('Slide transition template could not be deleted', 'revslider'));
	}

	/** @return array */
	public function get_navigation_skins(){
		$nav = new Navigation();

		return $nav->get_all_navigations_builder();
	}

	/** @return array|false */
	public function get_navigation_skin_by_handle($handle, $type){
		$nav = new Navigation();

		return $nav->init_by_handle($handle, $type);
	}
	
	/** @return array|false */
	public function get_navigation_skin_by_id($id, $type){
		$nav = new Navigation();

		return $nav->init_by_id($id, $type);
	}
	

	/** @return array */
	public function get_navigation_skins_short($type){
		$nav = new Navigation();

		return $nav->get_all_navigations_short($type);
	}
	
	/** @return array|false */
	public function get_tooltip_by_handle($handle){

		if(!class_exists('ThemePunch\SR\Core\Tooltips')) $this->ajax_response_error(__('Error loading RevSliderTooltips', 'revslider'));
		$tt = new \ThemePunch\SR\Core\Tooltips();

		return $tt->get_tooltip_by_handle($handle);
	}
	
	/** @return array */
	public function get_tooltips_by_string($search){
		if(empty($search)) return false;


		if(!class_exists('ThemePunch\SR\Core\Tooltips')) $this->ajax_response_error(__('Error loading RevSliderTooltips', 'revslider'));
		$tt = new \ThemePunch\SR\Core\Tooltips();

		return $tt->get_tooltips_by_string($search);
	}

	/**
	 * the rendered preview page - body on the host transport, a WP_REST_Response on WordPress'.
	 * The WP_REST_Request hint is frozen signature.
	 * @return mixed whatever the host transport answers; a host that renders no preview never returns,
	 *               load_404() ends the request the way the WP transport's own 404 paths do
	 */
	public static function get_slider_preview(\WP_REST_Request $req){
		$t = self::host_transport('get_slider_preview');
		if(!$t) self::load_404(); //echoes and exits, as it does for a bad key on the WP transport

		return $t->get_slider_preview($req);
	}

	/**
	 * which module an editor preview shows and from which tables: the unsaved draft when asked for, the published
	 * module when there is no draft of it, and one slide alone when singleslide names it
	 * @since 7.2.1
	 * @param int $module_id
	 * @param int|string $singleslide the slide to show alone, '' for all
	 * @param bool $preview read the draft tables; a host passes true only for a session that may see drafts
	 * @return array|false alias, fullwidth and fullheight of the module, false when there is none to show
	 */
	public function prepare_preview($module_id, $singleslide, $preview){
		$ctx = $this->context();

		//a preview draws a module on a page that is no page of the site, draft or saved. preview_mode only says which
		//tables are read; an add-on that stands a preview value in for the page's own (Commerce's Preview Product) asks this
		$ctx->set('preview_page', true);

		if($preview){
			$ctx->set('preview_mode', true);
			if($singleslide){
				add_filter(
					'revslider_get_slides',
					function($slides) use ($singleslide){
						$return = [];
						if(isset($slides[$singleslide])) $return[$singleslide] = $slides[$singleslide];
						return $return;
					}
				);
			}
		}

		$slider	= new Slider();
		$alias	= $slider->get_alias_by_id($module_id);
		if($alias === false){
			if($ctx->get('preview_mode') === false) return false;

			$ctx->set('preview_mode', false); //check for original

			$slider	= new Slider();
			$alias	= $slider->get_alias_by_id($module_id);
			if($alias === false) return false;
		}

		$slider->init_by_id($module_id);

		return [
			'alias'		 => $alias,
			'fullwidth'	 => $slider->get_param(['size', 'fullWidth'], true),
			'fullheight' => $slider->get_param(['size', 'fullHeight'], false),
		];
	}

	/** @return void ends the request */
	public static function load_404(){
		echo 'Not Found';
		exit;
	}


	/*********************
	 * PRESETS FUNCTIONS *
	 *********************/
	/** @return array */
	protected function get_type_presets($type){
		if (!$type) return [];
		$presets = $this->get_options(['system', 'presets', $type], []);
		return $presets ?? [];
	}

	/** @return void */
	protected function save_type_presets($type, $presets){
		if (!$type || !is_array($presets)) return;
		$this->update_option(['system', 'presets', $type], $presets);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_presets($data){
		$type = $this->get_val($data, 'type');
		$presets = $this->get_type_presets($type);
		$this->ajax_response_data(['presets' => $presets]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function save_preset($data){
		$type = $this->get_val($data, 'type');
		$handle = $this->get_val($data, 'handle');
		$preset = $this->get_val($data, 'preset', []);

		$presets = $this->get_type_presets($type);

		$saved = false;
		foreach ($presets as $key => $_preset) {
			if (isset($_preset['value']) && $_preset['value'] === $handle) {
				$presets[$key] = $preset;
				$saved = true;
			}
		}
		if (!$saved) {
			$presets[] = $preset;
		}

		$this->save_type_presets($type, $presets);
		$this->ajax_response_success(__('Palette Saved', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function rename_preset($data){
		$type = $this->get_val($data, 'type');
		$handle = $this->get_val($data, 'handle');
		$newHandle = $this->get_val($data, 'newHandle');
		$newTitle = $this->get_val($data, 'newTitle');

		$presets = $this->get_type_presets($type);

		foreach ($presets as $key => $preset) if (isset($preset['value']) && $preset['value'] === $newHandle) $this->ajax_response_error(__('Palette with this name already exists', 'revslider'));

		foreach ($presets as $key => $preset) {
			if (isset($preset['value']) && $preset['value'] === $handle) {
				$presets[$key]['value'] = $newHandle;
				$presets[$key]['title'] = $newTitle;
				$this->save_type_presets($type, $presets);
				$this->ajax_response_success(__('Palette Renamed', 'revslider'));
			}
		}

		$this->ajax_response_error(__('Failed to Rename Palette', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function delete_preset($data){
		$type = $this->get_val($data, 'type');
		$handle = $this->get_val($data, 'handle');

		$presets = $this->get_type_presets($type);

		foreach ($presets as $key => $preset) {
			if (isset($preset['value']) && $preset['value'] === $handle) {
				unset($presets[$key]);
				$this->save_type_presets($type, $presets);
				$this->ajax_response_success(__('Palette Deleted', 'revslider'));
			}
		}

		$this->ajax_response_error(__('Failed to Delete Palette', 'revslider'));
	}

	/***********************
	 * WORDPRESS FUNCTIONS *
	 ***********************/
	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_wordpress_object($data = false){
		$data	= $this->get_data($data);
		$id		= $this->get_val($data, 'id', 0);
		$type	= $this->get_val($data, 'type', 'full');
		$file	= $this->media_library()->image_url($id, $type);

		if($file !== false) $this->ajax_response_data(['url' => $file]);
		
		$this->ajax_response_error(__('File could not be loaded', 'revslider'));
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_wordpress_image($data = false){
		$data	= $this->get_data($data);
		$id		= $this->get_val($data, 'id', 0);
		$type	= $this->get_val($data, 'type', 'orig');
		$img	= $this->media_library()->image_url($id, $type);

		if(empty($img)) $this->ajax_response_error(__('Image could not be loaded', 'revslider'));
		
		$this->ajax_response_data(['url' => $img]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_wordpress_image_id($data = false){
		$data	= $this->get_data($data);
		$url	= $this->get_val($data, 'url');
		$id		= $this->get_image_id_by_url($url);

		$this->ajax_response_data(['id' => $id]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_wordpress_same_aspect_ratio($data = false){
		$data		= $this->get_data($data);
		$sr_admin	= $this->admin_functions();
		$images		= $this->get_val($data, 'images', []);
		$return		= $sr_admin->get_same_aspect_ratio_images($images);
		
		$this->ajax_response_data(['images' => $return]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_wordpress_pages(){
		$this->ajax_response_data(['pages' => $this->create_content_source()->page_catalog()]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_wordpress_post_types(){
		$this->ajax_response_data(['post-types' => $this->create_content_source()->content_type_catalog()]);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_wordpress_post_data(){
		$post_data = [
			'post_types' => $this->get_post_type_assoc(),
			'taxonomies' => $this->get_post_types_with_taxonomies(),
			'categories' => $this->get_post_types_with_categories()
		];

		$this->ajax_response_data($post_data);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_wordpress_post_popular(){
		$pop_posts = $this->slider->get_popular_posts(15);
		$this->ajax_response_data($pop_posts);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function get_wordpress_post_latest(){
		$rec_posts = $this->slider->get_latest_posts(15);
		$this->ajax_response_data($rec_posts);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function create_wordpress_media(){
		$this->transport()->create_wordpress_media();
	}

	/**
	 * the checks a file uploaded through the editor passes before the host stores it: upload error, size, name,
	 * the media type allowlist, and the SVG cleanup while the file is still the temporary upload
	 * @since 7.2.1
	 * @param array $file name, tmp_name, size and error, as PHP reports an upload
	 * @return array ['name', 'path', 'mime', 'target'] - target a free file name in the upload folder - or ['error' => message]
	 */
	public function check_upload($file){
		$message = $this->upload_error_message($this->get_val($file, 'error'));
		if($message !== null) return ['error' => $message];

		$path = $this->get_val($file, 'tmp_name');
		if(file_exists($path) == false) return ['error' => __('File not found', 'revslider')];
		if($this->get_val($file, 'size') > $this->environment()->max_upload_size()) return ['error' => __('Exceeded filesize limit', 'revslider')];

		$file_name	= sanitize_file_name(basename($this->get_val($file, 'name'))); //basename() leaves spaces, control characters and unicode
		if($file_name === '') return ['error' => __('Invalid file name', 'revslider')];

		$mime_types = $this->media_mimes($file_name);
		//without ext/fileinfo the check below would trust the extension alone, so the image header is a second source
		$file_mime	= function_exists('mime_content_type') ? mime_content_type($path) : false;
		if($file_mime === false){
			$wp_mime	= wp_get_image_mime($path);
			$file_mime	= ($wp_mime !== false) ? $wp_mime : '';
		}
		$file_type	= wp_check_filetype($file_name, $mime_types);
		if(!in_array($file_mime, $mime_types, true)){
			if(!empty($file_type['type']) && in_array($file_type['type'], $mime_types, true)) $file_mime = $file_type['type'];
			else return ['error' => __('WordPress doesn\'t allow this filetype', 'revslider')];
		}
		if($this->get_val($file_type, 'ext', false) === false || $this->get_val($file_type, 'type', false) === false) return ['error' => __('WordPress doesn\'t allow this filetype', 'revslider')];

		//while still the temporary upload: in the uploads folder the unfiltered file would be reachable over HTTP
		if($file_mime === 'image/svg+xml' && !$this->sanitize_svg_file($path)) return ['error' => __('SVG could not be sanitized', 'revslider')];

		$upload_dir = $this->paths()->upload_dir();
		$new_path	= trailingslashit($upload_dir['path']) . $file_name;
		$i			= 0;
		while(file_exists($new_path)){
			$i++;
			$new_path = trailingslashit($upload_dir['path']) . $i . '-' . $file_name;
		}

		return ['name' => $file_name, 'path' => $path, 'mime' => $file_mime, 'target' => $new_path];
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function create_wordpress_draft_page($data){
		$this->transport()->create_wordpress_draft_page($data);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function create_wordpress_metadata(){
		$this->generate_attachment_metadata();
		$this->ajax_response_success('');
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function create_wordpress_image($data = false){
		$data	 = $this->get_data($data);
		$mpeg	 = $this->get_val($data, 'mpeg');
		$mpeg	 = basename($mpeg);
		$bitmap  = $this->get_val($data, 'bitmap');

		if($this->current_user_can_not() && !$this->environment()->user_can('upload_files')){
			$this->ajax_response_error(__('You do not have permission to upload files', 'revslider'));
		}

		if(empty($mpeg)) $this->ajax_response_error(__('mpeg not set', 'revslider'));

		/* @var RevSliderOptimizer $o */
		$o  = Registry::instance()->get('RevSliderOptimizer');
		add_filter('revslider_import_media_insert_attachment_before', [$o, 'convert_import_media_webp']);

		$result = $this->import_media_raw($mpeg, $bitmap);
		if(!$result['success']){
			$this->ajax_response_error($result['message']);
		}

		$this->ajax_response_data($result);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function create_wordpress_image_from_url($data = false, $dst_folder = 'ai/'){
		$data     = $this->get_data($data);
		$prompt   = $this->get_val($data, 'prompt');
		$url      = $this->get_val($data, 'url');
		$ctype    = $this->get_val($data, 'content_type');
		$mode     = $this->get_val($data, 'mode', 'image');
		$filename = sanitize_file_name($this->get_val($data, 'filename'));

		if(empty($filename)) $this->ajax_response_error(__('Filename not set', 'revslider'));
		if (
			empty($url) || 
			!is_scalar($url) ||
			!filter_var($url, FILTER_VALIDATE_URL) ||
			!in_array(parse_url($url, PHP_URL_SCHEME), ['http','https']) 
		) {
			$this->ajax_response_error(__('URL not set or wrong format', 'revslider'));
		}
		

		/* @var RevSliderAi $ai */
		$ai = Registry::instance()->get( 'RevSliderAI' );

		if (!empty($ctype)){
			// validate by content type
			$ext = $ai->validate_mime_type($ctype);
		} else {
			//validate by extension
			$ext = $ai->validate_extension($url);
		}
		if(empty($ext)) $this->ajax_response_error(__('Not supported image format', 'revslider'));

		if ('bgremove' === $mode){
			// bgremove model return png images
			// convert to webp
			/* @var RevSliderOptimizer $o */
			$o  = Registry::instance()->get('RevSliderOptimizer');
			add_filter('revslider_import_media_insert_attachment_before', [$o, 'convert_import_media_webp']);
		}

		$result = $this->import_media($url, $dst_folder, $filename.'.'.$ext, $prompt);
		if (!$result['success']) {
			$this->ajax_response_error($result['message']);
		}

		$this->ajax_response_data(['url' => $result['url']]);
	}

	/***********************
	 * AJAX RESPONSE FUNC  *
	 ***********************/

	/**
	 * echo json ajax response as error
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function ajax_response_error($message, $data = null){
		$this->ajax_response(false, $message, $data);
	}

	/**
	 * echo ajax success response with redirect instructions
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function ajax_response_redirect($message, $url){
		$data = ['is_redirect' => true, 'redirect_url' => $url];

		$this->ajax_response(true, $message, $data);
	}

	/**
	 * echo json ajax response, without message, only data
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function ajax_response_data($data){
		$data = (gettype($data) == 'string') ? ['data' => $data] : $data;

		$this->ajax_response(true, '', $data);
	}

	/**
	 * echo ajax success response
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function ajax_response_success($message, $data = null){
		$this->ajax_response(true, $message, $data);
	}

	/**
	 * echo json ajax response
	 * @return void echoes the JSON body and ends the request
	 */
	private function ajax_response($success, $message, $data = null){
		http_response_code(200);

		$response = ['success' => $success, 'message' => $message];

		if(!empty($data)){
			$data = (gettype($data) == 'string') ? ['data' => $data] : $data;
			$response = array_merge($response, $data);
		}

		$out = json_encode($response);

		//pack oversized payloads so they slip under server/WAF response-body limits. only triggered when the
		//client opted in (compress flag) and the body is actually big enough to risk the limit. small responses
		//and non-opted-in clients keep the legacy plain-JSON contract untouched.
		if($this->compress_response && function_exists('gzencode') && strlen($out) > 1048576){
			$out = json_encode([
				'success'	 => $success,
				'compressed' => 'gzip',
				'payload'	 => base64_encode(gzencode($out, 6))
			]);
		}

		if($this->is_rest_call()){
			echo $out;
			exit;
			//return new WP_REST_Response($response);
		}else{
			echo $out;
			exit; //like the REST branch above
		}
	}
}
