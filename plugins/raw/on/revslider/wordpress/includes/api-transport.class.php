<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * The WordPress transport of the API: REST route wiring, the preview short-circuit that
 * serves HTML through the REST server, the cookie-auth preview gate, and the ops that
 * exist only to drive WordPress admin services. RevSliderApi keeps every frozen public
 * as a delegation shell; the dispatch tables stay core.
 */
class RevSliderWpTransport extends RevSliderFunctions {

	/** @var RevSliderApi */
	private $api;

	public function __construct($api){
		parent::__construct();
		$this->api = $api;
	}

	/**
	 * Init the REST API
	 * used only for frontend functionality
	 * the routes themselves are RevSliderApi::REST_ROUTES, so a second host answers the same paths
	 * @return void
	 **/
	public function init_rest_api(){
		//what the table's gates mean here: 'public' only installs the core's exception handler, 'editor'
		//checks the auth cookie first. An unknown gate falls to the strictest one, it never opens a route.
		$gates = ['public' => 'setup_exception_handler', 'editor' => 'preview_permission_check'];

		foreach(RevSliderApi::REST_ROUTES as $route){
			$parts	= explode('/', $route['path'], 2); //WordPress wants the namespace apart from the route
			$path	= $this->get_val($parts, 1);
			foreach($route['params'] as $name => $pattern){
				$path = str_replace('{'.$name.'}', '(?P<'.$name.'>'.$pattern.')', $path);
			}

			register_rest_route($this->get_val($parts, 0), '/'.$path, [
				'methods'				=> $route['method'],
				'callback'				=> [$this->api, $route['call']],
				'permission_callback'	=> [$this->api, $this->get_val($gates, $route['gate'], 'preview_permission_check')]
			]);
		}
	}

	/** @return bool */
	public function init_rest_api_return($served, $result, $request, $server){
		$route	= $request->get_route();
		$path	= RevSliderApi::REST_ROUTES['preview']['path'];
		$prefix	= '/' . (strstr($path, '{', true) ?: $path); //the table owns the path; recognising it here must not spell it again

		if(strpos($route, $prefix) !== 0) return $served;
	
		// Output as real HTML so the browser renders the page
		$charset = get_option('blog_charset') ?: 'UTF-8';
		header('Content-Type: text/html; charset=' . $charset);

		// $result is WP_REST_Response here
		$data = ($result instanceof WP_REST_Response) ? $result->get_data() : (string)$result;
		if(is_array($data)) $data = json_encode($data);
		echo (string)$data;

		return true; // short-circuit: we've sent the response ourselves
	}

	/**
	 * true when the calling browser session belongs to a user who may see unsaved module drafts.
	 *
	 * The preview route is opened as an iframe from the editor, so it carries the WP auth cookies but no REST
	 * nonce - and WP forces the current user to 0 for nonce-less REST requests (rest_cookie_check_errors), so
	 * current_user_can() would report "not allowed" even for a logged in editor. The route is a read-only GET,
	 * so the missing nonce is not a CSRF concern: an attacker cannot read the cross-origin response.
	 * @return bool
	 */
	private static function can_view_module_preview(){
		$user_id = wp_validate_auth_cookie('', 'logged_in');

		return ($user_id && user_can($user_id, 'edit_posts')) ? true : false;
	}

	/**
	 * the 'editor' gate of RevSliderApi::REST_ROUTES: a logged in user who may edit.
	 * the preview renders the module from the *preview* (unsaved editor draft) tables and its URL is only ever
	 * handed out inside the admin editor (SR7.E.preview_url in core/admin/views/header.php); the font list
	 * carries the site's own uploaded families and their URLs. The public routes stay public on purpose:
	 * the front-end fetches slider data anonymously.
	 * @return bool
	 */
	public function preview_permission_check(){
		$this->api->setup_exception_handler();

		return self::can_view_module_preview();
	}

	/** @return void */
	public function handle_rest_exceptions(Throwable $exception){
		wp_send_json(['success'	=> false, 'message'	=> $exception->getMessage()]);
	}

	/** @return string the rendered preview page */
	public static function get_slider_preview(WP_REST_Request $req){
		global $SR_api;
		$key         = isset($req['module']) ? intval($req['module']) : '';
		$singleslide = isset($req['singleslide']) ? intval($req['singleslide']) : '';
		$preview     = isset($_GET['preview']) ? $SR_api->_truefalse($_GET['preview']) : false;

		//defense in depth: the route's permission callback already restricts this to editors, but never let
		//?preview=1 switch the reads to the unsaved draft tables for a session that may not see them
		if($preview === true && !self::can_view_module_preview()) $preview = false;
		
		// Optional headers
		nocache_headers(); // or send Cache-Control public if you want caching

		if (!defined('SHOW_CT_BUILDER')) define('SHOW_CT_BUILDER', false);

		if (!$key) self::load_404();

		// Render inside your template (same as before)
		if (extension_loaded('newrelic') && function_exists('newrelic_disable_autorum')) newrelic_disable_autorum();

		$module = $SR_api->prepare_preview($key, $singleslide, $preview);
		if($module === false) self::load_404();

		$boxed	 = ($module['fullwidth'] === false && $module['fullheight'] === false);
		$_pre	 = $boxed ? '<div style="max-width:1240px; text-align:center;width:auto; height:auto; margin:auto; display:block;position:relative">'."\n" : '';
		$_post	 = $boxed ? '</div>'."\n" : '';
		$content = $_pre.'[sr7 alias="' . esc_attr($module['alias']) . '"][/sr7]'.$_post;
		$title	 = __('Slider Revolution Preview', 'revslider');
		
		$rev_slider_front = new RevSliderFront(); //needed to be called, to load header properly
		//$admin = new RevSliderAdmin();
		$post = $rev_slider_front->create_fake_post($content, $title);
		$_rs_orig_query = $_rs_orig_the_query = $_rs_orig_post = null;
		if($post instanceof WP_Post){
			global $wp_query, $wp_the_query;

			//remember the real globals: this endpoint deliberately fakes the main query for the template render,
			//and without restoring them rest_post_dispatch filters / shutdown hooks would still see the fake page
			$_rs_orig_query		= $wp_query;
			$_rs_orig_the_query	= $wp_the_query;
			$_rs_orig_post		= $GLOBALS['post'] ?? null;

			$wp_query = new WP_Query([
				'post__in'				 => [$post->ID],
				'post_type'				 => 'any',
				'posts_per_page'		 => 1,
				'ignore_sticky_posts'	 => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'no_found_rows'			 => true,
				'fields'				 => 'all',
			]);

			$wp_the_query					= $wp_query;
			$wp_query->post					= $post;
			$wp_query->posts				= [$post];
			$wp_query->post_count			= 1;
			$wp_query->is_singular			= true;
			$wp_query->is_page				= true;
			$wp_query->queried_object		= $post;
			$wp_query->queried_object_id	= $post->ID;
			
			setup_postdata($post);
		}else{
			self::load_404();
		}

		ob_start();
		try{
			include RS_PLUGIN_PATH . 'wordpress/public/views/revslider-page-template.php';
		}finally{
			$html = ob_get_clean(); //always close the buffer, even if the template throws

			//hand the real main query back before returning (wp_reset_postdata() alone would only reset to our fake one)
			if($_rs_orig_query !== null){
				$GLOBALS['wp_query']	 = $_rs_orig_query;
				$GLOBALS['wp_the_query'] = $_rs_orig_the_query;
				$GLOBALS['post']		 = $_rs_orig_post;
				wp_reset_postdata();
			}
		}

		return new WP_REST_Response($html, 200, ['Content-Type'=>'text/html; charset=UTF-8']);
	}

	/** @return void ends the request */
	public static function load_404(){
		RevSliderApi::load_404(); //the answer itself is the core's
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function create_wordpress_media(){
		$return = $this->import_upload_media();
		
		if($this->get_val($return, 'error', false) !== false) $this->api->ajax_response_error($this->get_val($return, 'error', false));
		
		$this->api->ajax_response_data($return);
	}

	/** @return void the response is sent by ajax_response_*(), which ends the request */
	public function create_wordpress_draft_page($data){
		$data		= $this->api->get_data($data);
		$response	= ['open' => false, 'edit' => false];
		$sliders	= $this->get_val($data, 'sliders');
		$modals		= $this->get_val($data, 'modals', []);
		$additions	= $this->get_val($data, 'additions', []);
		$page_id	= self::create_slider_page($sliders, $modals, $additions, sanitize_text_field((string)$this->get_val($data, 'title', '')));
		
		if($page_id > 0){
			$response['open'] = str_replace('&amp;', '&', get_permalink($page_id));
			$response['edit'] = str_replace('&amp;', '&', get_edit_post_link($page_id));
		}
		$this->api->ajax_response_data($response);
	}
	/**
	 * a WP_Post that exists only in memory (id 999999999), so the_content filters can be run
	 * against arbitrary content - used by the preview and the HTML export
	 * @return WP_Post
	 **/
	public static function fake_post($content, $title = 'Slider Revolution'){
		$fake_id				= 999999999;
		$post					= new stdClass();
		$post->ID				= $fake_id;
		$post->post_author		= get_current_user_id();
		$post->post_date		= current_time('mysql');
		$post->post_date_gmt	= current_time('mysql', 1);
		$post->post_title		= $title;
		$post->post_content		= $content;
		$post->post_status		= 'publish';
		$post->comment_status	= 'closed';
		$post->ping_status		= 'closed';
		$post->post_name		= 'rs-fake-page-' . rand(1, 99999);
		$post->post_type		= 'page';
		$post->filter			= 'raw';

		$wp_post = new WP_Post($post);

		wp_cache_add($wp_post->ID, $wp_post, 'posts');

		return $wp_post;
	}

	/**
	 * The Ajax Action part for backend actions only
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 **/
	public function do_ajax_action(){
		if(!is_user_logged_in()){
			wp_send_json( ['success' => false, 'message' => __('Please login to continue', 'revslider')], 401 );
			exit;
		}

		$this->api->handle_admin_request($this->get_request_var('client_action'), $this->api->request_nonce(), $this->get_request_var('data', '', false));
		wp_die();
	}

	/**
	 * Ajax handling for frontend, no privileges here
	 * @return void the response is sent by ajax_response_*(), which ends the request
	 */
	public function do_front_ajax_action(){
		//throttle anonymous (nopriv) callers before doing any work; sends 429 and exits if exceeded
		$retry = $this->api->front_rate_limit_retry(is_user_logged_in()); //the pluggable answer, as a site may have replaced it
		if($retry > 0){
			if(!headers_sent()) header('Retry-After: ' . (int)$retry);
			wp_send_json(['success' => false, 'message' => __('Too many requests. Please slow down and try again in a moment.', 'revslider')], 429);
		}

		$this->api->handle_front_request($this->get_request_var('client_action'));

		exit;
	}

	/**
	 * @since: 5.3.0
	 * create a page with revslider shortcodes included
	 * @param string $title the page's title, "RevSlider Page n" when empty
	 * @return int the id of the created page
	 **/
	public static function create_slider_page($added, $modals = [], $additions = [], $title = ''){
		$new_page_id = 0;

		if(!is_array($added)) return apply_filters('revslider_create_slider_page', $new_page_id, $added);

		$f = RevSliderGlobals::instance()->get('RevSliderFunctions');
		$content = '';

		foreach($added ?? [] as $sid){
			//a template's own shortcode attributes, such as usage="modal" or zindex="5"
			$sc = isset($additions[$sid]) ? shortcode_parse_atts(stripslashes((string)$additions[$sid])) : [];
			$sc = is_array($sc) ? array_filter($sc, 'is_string', ARRAY_FILTER_USE_KEY) : [];
			unset($sc['alias']);
			if(isset($modals[$sid])) $sc = ['usage' => 'modal'] + $sc;
			$content .= self::module_block($sid, $sc, isset($sc['zindex']) ? ['zindex' => (string)$sc['zindex']] : []);
		}

		if($content !== ''){
			$page_id = $f->get_options(['other', 'page-id'], 1);
			$new_page_id = wp_insert_post(
				[
					'post_title'    => wp_strip_all_tags($title !== '' ? $title : 'RevSlider Page '.$page_id),
					'post_content'  => $content,
					'post_type'   	=> 'page',
					'post_status'   => 'draft',
					'page_template' => RevSliderPageTemplate::KEY
				]
			);

			if(is_wp_error($new_page_id)) $new_page_id = 0; //fallback to 0

			$page_id++;
			$f->update_option(['other', 'page-id'], $page_id);
		}

		return apply_filters('revslider_create_slider_page', $new_page_id, $added);
	}

	/**
	 * One module on a page as the Slider Revolution block: character for character what the block's save() writes, or
	 * WordPress reads it as an older block and drops the attributes, around the [sr7] shortcode its adapter writes
	 * (SR7.Block.toShortcode). Every page the plugin builds places its modules through here.
	 * @since 7.2.9
	 * @param int|string $module the module's id or alias
	 * @param array $sc shortcode attributes after the alias, in their order: usage, modal, zindex
	 * @param array $attrs block attributes beside the ones read off the module
	 * @return string '' when the module does not exist
	 */
	public static function module_block($module, $sc = [], $attrs = []){
		$slider = new RevSliderSlider();
		if(is_numeric($module)) $slider->init_by_id(intval($module), false); else $slider->init_by_alias((string)$module, false);
		$alias = $slider->get_alias();
		if(empty($alias)) return '';
		$sid = $slider->get_id();

		$ov		= $slider->get_overview_data();
		$title	= (string)$slider->get_val($ov, 'title', '');
		//bg used to be an array and is a plain url now
		$img	= $slider->get_val($ov, 'bg', '');
		if(!is_string($img) || $img === '') $img = (string)$slider->get_val($ov, ['bg', 'src'], '');

		//the block only ever sees this comment: its card fetches nothing without alias and takes its cover from image
		$attrs = array_merge(array_filter([
			'alias'		=> $alias,
			'moduleId'	=> (string)$sid,
			'slides'	=> count((array)$slider->get_val($ov, 'children', [])),
			'image'		=> $img,
			'type'		=> (string)$slider->get_val($ov, 'type', ''),
			'title'		=> $title
		], function($v){ return $v !== ''; }), $attrs);
		$zindex = (string)$slider->get_val($attrs, 'zindex', '');

		$code = '[sr7 alias="'.esc_attr($alias).'"';
		foreach($sc as $k => $v) $code .= ' '.$k.'="'.esc_attr($v).'"';
		$code .= '][/sr7]';

		$inner = '<div class="wp-block-themepunch-revslider revslider" id="" data-slidertitle="'.esc_attr($title).'" style="'.($zindex !== '' ? 'z-index:'.esc_attr($zindex) : '').'">'.$code.'</div>';

		return serialize_block(['blockName' => 'themepunch/revslider', 'attrs' => $attrs, 'innerBlocks' => [], 'innerHTML' => $inner, 'innerContent' => [$inner]])."\n";
	}

	/**
	 * import a media file uploaded through the browser to the media library
	 * @return array ['id' => attachment id] or ['error' => message]
	 **/
	public function import_upload_media(){
		require_once(ABSPATH . 'wp-admin/includes/file.php');

		$checked = $this->api->check_upload($this->get_val($_FILES, 'import_file'));
		if(isset($checked['error'])) return $checked;

		$file_name	= $checked['name'];
		$file_mime	= $checked['mime'];
		$new_path	= $checked['target'];

		if(!move_uploaded_file($checked['path'], $new_path)) return ['error' => __('File not found', 'revslider')];

		$upload_id = wp_insert_attachment(
			[
				'guid'			 => $new_path,
				'post_mime_type' => $file_mime,
				'post_title'	 => preg_replace( '/\.[^.]+$/', '', $file_name),
				'post_name'		 => sanitize_title_with_dashes(str_replace('_', '-', $file_name)),
				'post_content'	 => '',
				'post_status'    => 'inherit'
			],
			$new_path
		);

		require_once(ABSPATH . 'wp-admin/includes/image.php');

		@wp_update_attachment_metadata($upload_id, wp_generate_attachment_metadata($upload_id, $new_path));

		$img_dim = @wp_get_attachment_image_src($upload_id, 'full');
		$width	 = ($img_dim !== false) ? $this->get_val($img_dim, 1, '') : '';
		$height	 = ($img_dim !== false) ? $this->get_val($img_dim, 2, '') : '';

		return ['error' => false, 'id' => $upload_id, 'path' => wp_get_attachment_url($upload_id), 'width' => $width, 'height' => $height]; //$new_path
	}

	/**
	 * @return bool are we on a post/widget edit screen?
	 */
	public function is_edit_page(){
		if(!$this->environment()->is_admin_area()) return false;
		global $pagenow;

		return in_array($pagenow, ['post.php', 'post-new.php', 'widgets.php']);
	}
}
