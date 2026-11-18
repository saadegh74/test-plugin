<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

/*
 * The WordPress host bootstrap: WP-derived constants, core/boot.php, the platform adapters
 * and every WP integration and hook. The root revslider.php is only the header stub WP
 * demands; other platforms ship their own twin of this file (see standalone/host.php).
 */

if(!defined('ABSPATH')) exit();

//the duplicate-instance guard lives in revslider.php, BEFORE that stub's defines - here it would
//come too late: a second copy would warn about redefined constants before ever reaching it

define('RS_PLUGIN_PATH',		plugin_dir_path(RS_PLUGIN_FILE_PATH));
define('RS_PLUGIN_SLUG_PATH',	plugin_basename(RS_PLUGIN_FILE_PATH));
define('RS_PLUGIN_SLUG',		apply_filters('set_revslider_slug', 'revslider'));
define('RS_PLUGIN_URL',			get_sr_plugin_url());
define('RS_PLUGIN_URL_CLEAN',	str_replace(['http://', 'https://'], '//', RS_PLUGIN_URL));
define('RS_TP_TOOLS',			RS_REVISION); //tp-tools' version, the core release that built it (the build writes it into tptools.js, Essential Grid compares it)
if(!defined('RS_THUMBS_ENDPOINT')) define('RS_THUMBS_ENDPOINT', 'https://render.sliderrevolution.org/render/v1/shot'); //Cloud Thumbnails, WordPress only: the service reads the site from WP's User-Agent

//BC bridge for the WordPress ecosystem: themes, third-party plugins and addons keep calling the
//classic global names - the autoloader aliases each one onto its ThemePunch\SR class the moment
//either side is first used (extends/instanceof/statics identical)
require_once(RS_PLUGIN_PATH . 'core/autoload.php');
\ThemePunch\SR\Autoloader::register();
\ThemePunch\SR\Autoloader::aliases(require(RS_PLUGIN_PATH . 'core/compat/class-map.php'));

require_once(RS_PLUGIN_PATH . 'core/boot.php');

require_once(RS_PLUGIN_PATH . 'core/compat/output.php');


require_once(RS_PLUGIN_PATH . 'wordpress/includes/platform/option-storage.php');
require_once(RS_PLUGIN_PATH . 'wordpress/includes/platform/http-client.php');
require_once(RS_PLUGIN_PATH . 'wordpress/includes/platform/ttl-cache.php');
require_once(RS_PLUGIN_PATH . 'wordpress/includes/platform/database.php');
require_once(RS_PLUGIN_PATH . 'wordpress/includes/platform/filesystem.php');
require_once(RS_PLUGIN_PATH . 'wordpress/includes/platform/paths.php');
require_once(RS_PLUGIN_PATH . 'wordpress/includes/platform/post-content-source.php');
require_once(RS_PLUGIN_PATH . 'wordpress/includes/platform/environment.php');
require_once(RS_PLUGIN_PATH . 'wordpress/includes/platform/media-library.php');

require_once(RS_PLUGIN_PATH . 'wordpress/includes/builders.class.php'); //Page builder integrations — register via RevSliderBuilders::register()
require_once(RS_PLUGIN_PATH . 'wordpress/includes/page-effects.class.php'); //Page Effects framework — addons register via RevSliderPageEffects::register_type()
require_once(RS_PLUGIN_PATH . 'wordpress/includes/page-effect-panzoom.class.php'); //Pan & Zoom on native WP image blocks — core's own Page Effect (no addon owns Pan & Zoom)
require_once(RS_PLUGIN_PATH . 'wordpress/includes/page-effect-scrollanim.class.php'); //the layer-animation catalogue on native WP blocks — core's own Page Effect as well
require_once(RS_PLUGIN_PATH . 'wordpress/includes/page-effect-pointerix.class.php'); //Cursor Motion on native WP blocks — pointer interactions, core's own as well
require_once(RS_PLUGIN_PATH . 'wordpress/includes/slots.class.php'); //who may edit layer meta (the rows themselves are core Slots) — Quick Edit's Meta Editing mode
require_once(RS_PLUGIN_PATH . 'wordpress/admin/includes/editor-environment.class.php'); //the WordPress facts behind the editor boot values
require_once(RS_PLUGIN_PATH . 'wordpress/admin/includes/installer-wp-phases.class.php');
RevSliderWpInstallerPhases::register(); //the WP page/menu/options phases ride the installer's phase seam
require_once(RS_PLUGIN_PATH . 'wordpress/admin/includes/widget.class.php');
require_once(RS_PLUGIN_PATH . 'wordpress/includes/extension.class.php');
require_once(RS_PLUGIN_PATH . 'wordpress/includes/aq-resizer.class.php');
require_once(RS_PLUGIN_PATH . 'wordpress/includes/page-template.class.php');

require_once(RS_PLUGIN_PATH . 'wordpress/includes/external/facebook.class.php');
require_once(RS_PLUGIN_PATH . 'wordpress/includes/external/instagram.class.php');

require_once(RS_PLUGIN_PATH . 'wordpress/public/revslider-front.class.php');

require_once(RS_PLUGIN_PATH . 'wordpress/includes/api-transport.class.php'); //REST wiring + preview transport the api shells delegate to
require_once(RS_PLUGIN_PATH . 'wordpress/admin/includes/addon-lifecycle.class.php'); //WP plugin machinery behind the addon policy
require_once(RS_PLUGIN_PATH . 'wordpress/admin/includes/upgrade-glue.class.php'); //WP halves of the one-time upgrade routines
require_once(RS_PLUGIN_PATH . 'wordpress/includes/addon-base/notices.class.php'); //addon admin notices - RevSliderAddonNotices::show() is frozen addon API
require_once(RS_PLUGIN_PATH . 'wordpress/includes/addon-base/updates.class.php'); //feeds WP's plugin-update transient per addon
require_once(RS_PLUGIN_PATH . 'wordpress/includes/addon-base/page.class.php'); //the page skeleton an addon takes a request over with

require_once(RS_PLUGIN_PATH . 'wordpress/includes/em-integration.class.php');
require_once(RS_PLUGIN_PATH . 'wordpress/includes/seo.class.php'); //Yoast SEO and Rank Math read a module's text
require_once(RS_PLUGIN_PATH . 'wordpress/includes/woocommerce.class.php');
require_once(RS_PLUGIN_PATH . 'wordpress/includes/backwards.php');
require_once(RS_PLUGIN_PATH . 'wordpress/includes/jetpack.class.php');
require_once(RS_PLUGIN_PATH . 'wordpress/includes/cache-plugins.class.php');

//zip fallback for hosts without ext-zip: WordPress ships PclZip, core asks for it through this seam
add_filter('revslider_zip_fallback', function($zip, $path){
	if(!class_exists('PclZip')) require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
	return new PclZip($path);
}, 10, 2);

//eager front script of a tag-detected addon: core announces, the host places. $args: footer (bool), async (false
//for a script whose inline init expects it to have run)
add_action('revslider_enqueue_front_script', function($handle, $src, $args = []){
	$where = ['in_footer' => !empty($args['footer'])];
	if(!isset($args['async']) || $args['async'] !== false) $where['strategy'] = 'async';
	wp_enqueue_script($handle, $src, [], null, $where);
}, 10, 3);

//the stylesheet an addon's front needs beside its on-demand script (weather, forms); same seam, same placement rule
add_action('revslider_enqueue_front_style', function($handle, $src){
	wp_enqueue_style($handle, $src, [], null);
}, 10, 2);

//the markup cache replays what a render queued: core asks which handles are queued, and queues a hit's again.
//A module renders before wp_enqueue_scripts, so its WooCommerce scripts still sit in the private list of
//handles waiting for their plugin to register them, which is asked for on the class it is private to
add_filter('revslider_cache_queued_assets', function($queued){
	foreach(['js' => wp_scripts(), 'css' => wp_styles()] as $type => $dep){
		$handles = (array)$dep->queue;
		if(property_exists('WP_Dependencies', 'queued_before_register')){
			$read = Closure::bind(function(){ return array_keys($this->queued_before_register); }, $dep, 'WP_Dependencies');
			if($read instanceof Closure) $handles = array_merge($handles, $read());
		}
		$queued[$type] = $handles;
	}
	return $queued;
});

add_action('revslider_cache_queue_assets', function($js, $css){
	foreach((array)$js as $handle) wp_enqueue_script($handle);
	foreach((array)$css as $handle) wp_enqueue_style($handle);
}, 10, 2);

//an addon's block-editor scripts ('block_editor' in its descriptor: handle, script, deps, style, style_handle,
//footer, not_in_site_editor): a WordPress surface, so the loader places them and the addon names them only
add_action('revslider_addon_registered', function($addon, $args){
	if(empty($args['block_editor'])) return;
	add_action('enqueue_block_editor_assets', function() use ($addon, $args){
		global $pagenow;
		foreach($args['block_editor'] as $entry){
			if(!empty($entry['not_in_site_editor']) && $pagenow === 'site-editor.php') continue;
			$ver = function($file) use ($addon){ return file_exists($addon->path . $file) ? filemtime($addon->path . $file) : null; };
			if(!empty($entry['script'])) wp_enqueue_script($entry['handle'], $addon->url . $entry['script'], $entry['deps'] ?? [], $ver($entry['script']), !empty($entry['footer']));
			if(!empty($entry['style'])) wp_enqueue_style($entry['style_handle'] ?? $entry['handle'], $addon->url . $entry['style'], [], $ver($entry['style']));
		}
	});
}, 10, 2);

//divi
require_once(RS_PLUGIN_PATH . 'wordpress/admin/includes/shortcode_generator/shortcode_generator.class.php');
require_once(RS_PLUGIN_PATH . 'wordpress/admin/includes/shortcode_generator/divi/revslider-divi.php');

try{
	RevSliderFunctions::set_memory_limit();

	/** @return string|false the rendered slider markup */
	function rev_slider_shortcode($args, $mid_content = null){

		//do not render in elementor preview iframe
		if(isset($_GET['elementor-preview'])) return false;

		//do not render on saving a post/page
		global $SR_GLOBALS;
		if($SR_GLOBALS['save_post']) return false;

		//skip shortcode generation if any of these functions found in backtrace
		//function can be provided as array item without key
		//or as 'class' => 'function'
		$skip_functions = apply_filters(
			'rs_shortcode_skip_functions',
			[
				'WC_Structured_Data' => 'generate_product_data', // woocommerce
				'AIOSEO\Plugin\Common\Meta\Description' => 'getDescription', // all-in-one-seo
				//'Elementor\Core\Editor\Editor' => 'print_editor_template', // elementor
			]
		);

		//only pay for debug_backtrace() when a relevant integration is actually loaded:
		//string keys are class names - if none exist (and there are no keyless entries), skip the backtrace entirely
		$check_backtrace = false;
		foreach($skip_functions as $class => $func){
			if(!is_string($class) || class_exists($class, false)){ $check_backtrace = true; break; }
		}
		if($check_backtrace){
			$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			foreach($backtrace as $trace){
				foreach($skip_functions as $class => $func){
					if($trace['function'] == $func){
						//no class was provided, func matched, return
						if(!is_string($class)) return false;
						//class provided in key, compare with trace class
						if(isset($trace['class']) && $trace['class'] == $class) return false;
					}
				}
			}
		}

		$defaults = ['alias' => '', 'class' => '', 'fullheight' => '', 'fullwidth' => '', 'modal' => '', 'offset' => '', 'order' => '', 'settings' => '', 'skin' => '', 'usage' => '', 'wrapperid' => '', 'zindex' => '', 'post_ids' => '', 'category' => '', 'taxonomy' => ''];
		$sc		= shortcode_atts($defaults, $args, 'rev_slider');
		$sc		= array_map('wp_kses_post', $sc);

		//Avada's element saves its own dialect of the grammar: yes/no where this reads true, and an id where
		//it reads wrapperid. Its integration used to rewrite the whole shortcode before it ever got here, but
		//only for logged in users - so the same page rendered one way for its editor and another for everyone
		//else. Reading the words that differ settles it for both (CONTRACT R3).
		foreach(['fullwidth', 'fullheight'] as $key){
			if($sc[$key] === 'yes')		$sc[$key] = 'true';
			elseif($sc[$key] === 'no')	$sc[$key] = '';	//"no" meant leave it to the module, not turn it off
		}
		if($sc['usage'] !== 'modal') $sc['usage'] = '';
		if($sc['wrapperid'] === '' && is_array($args) && !empty($args['id'])) $sc['wrapperid'] = wp_kses_post($args['id']);

		/**
		 * How an embedded module is about to render.
		 *
		 * The fifteen attributes after defaults, sanitizing and the dialect reading above - so a filter sees
		 * one grammar whichever builder wrote the shortcode, and does not have to know that Avada says
		 * yes/no where this reads true. Change the wrapper id a theme needs, force an offset, refuse a
		 * module in a context of your own.
		 *
		 * @param array $sc          alias, class, fullheight, fullwidth, modal, offset, order, settings,
		 *                           skin, usage, wrapperid, zindex, and for a post based module post_ids,
		 *                           category, taxonomy
		 * @param array $args        the attributes as they were written
		 * @param string $mid_content anything between the opening and closing tags
		 */
		$sc = array_merge($defaults, (array)apply_filters('revslider_builder_shortcode_atts', $sc, $args, $mid_content));

		$output = new RevSlider7Output();

		if(is_admin() && $output->_is_gutenberg_page()) return false;

		$slider_alias = ($sc['alias'] != '') ? $sc['alias'] : $output->get_val($args, 0); //backwards compatibility

		//this fixes an issue with the Visual Composer extension
		if(empty($slider_alias)){
			return (function_exists('is_user_logged_in') && is_user_logged_in()) ? '<div><img src="' . RS_PLUGIN_URL_CLEAN . 'core/admin/assets/images/sr_dark.png"></div>' : '';
		}

		$service  = new \ThemePunch\SR\Render\Service();
		$rendered = $service->render_module_html($slider_alias, [
			'order'			=> $sc['order'],
			'settings'		=> $sc['settings'],
			'skin'			=> $sc['skin'],
			'post_ids'		=> $sc['post_ids'], //a post based module's own posts; Service sets them after order, settings and skin
			'category'		=> $sc['category'],
			'taxonomy'		=> $sc['taxonomy'],
			'gallery_ids'	=> $output->check_for_shortcodes($mid_content), //check for example on gallery shortcode and do stuff
			'usage'			=> $sc['usage'],
			'fullheight'	=> $sc['fullheight'],
			'fullwidth'		=> $sc['fullwidth'],
			'offset'		=> $sc['offset'],
			'modal'			=> $sc['modal'],
		], $output);
		$slider	 = $rendered['slider'];
		$content = $rendered['html'];

		//the wrapper the builders each used to add for themselves: an id to link to, classes to style with,
		//a z-index to lift the module over its neighbours. Only emitted when something asks for it.
		$wrapper_id		= trim($sc['wrapperid']);
		$wrapper_class	= trim($sc['class']);
		$wrapper_zindex	= trim($sc['zindex']);

		if($wrapper_id !== '' || $wrapper_class !== '' || $wrapper_zindex !== ''){
			$classes = ['wp-block-themepunch-revslider'];
			foreach(preg_split('/\s+/', $wrapper_class, -1, PREG_SPLIT_NO_EMPTY) as $class){
				$class = sanitize_html_class($class);
				if($class !== '') $classes[] = $class;
			}

			$attributes = ' class="'.esc_attr(implode(' ', $classes)).'"';
			if($wrapper_id !== '')		$attributes .= ' id="'.esc_attr(sanitize_html_class($wrapper_id)).'"';
			if($wrapper_zindex !== '')	$attributes .= ' style="z-index:'.esc_attr($wrapper_zindex).';"';

			$content = '<div'.$attributes.'>'. $content .'</div>';
		}

		if(empty($slider)) return $content;
		$filter = $slider->get_param(['general', 'outPutFilter'], '');
		switch($filter){
			case 'compress':
				$content = str_replace(["\n", "\r"], '', $content);
				return $content;
			case 'echo':
				global $SR_GLOBALS;
				if($SR_GLOBALS['save_post']) return $content;
				echo $content; //bypass the filters
				return ''; //return nothing
			break;
		}

		return $content;
	}

	$optimizer  = RevSliderGlobals::instance()->get('RevSliderOptimizer');
	$sr_thumbs	= RevSliderGlobals::instance()->get('ThemePunch\SR\Core\Thumbs');
	add_action('revslider_api_save_slider_after', [$sr_thumbs, 'stale_after_save'], 10, 2);
	add_action('sr_api_save_slide', [$sr_thumbs, 'stale_after_slide'], 10, 2);
	add_action('revslider_quickedit_saved', [$sr_thumbs, 'stale']);
	add_action('revslider_slider_imported', [$sr_thumbs, 'stale']);
	add_action('revslider_duplicate_slider', [$sr_thumbs, 'stale']);
	add_action('revslider_slider_on_delete_slider', [$sr_thumbs, 'forget']);
	$SR_cache	= RevSliderGlobals::instance()->get('RevSliderCachePlugins');
	$SR_api		= RevSliderGlobals::instance()->get('RevSliderApi');
	$SR_ai		= RevSliderGlobals::instance()->get('RevSliderAI');
	$rslb		= RevSliderGlobals::instance()->get('RevSliderLoadBalancer');
	$rslb->refresh_server_list();

	add_shortcode('rev_slider', 'rev_slider_shortcode');
	add_shortcode('sr7', 'rev_slider_shortcode');
	add_action('save_post', ['RevSliderFront', 'set_post_saving']);
	add_action('widgets_init', ['RevSliderWidget', 'register_widget']);

	add_action('init', ['RevSliderShortcodeWizard', 'add_slider_meta_box']);
	add_action('added_post_meta', ['RevSliderShortcodeWizard', 'on_updated_post_meta'], 10, 4);
	add_action('updated_post_meta', ['RevSliderShortcodeWizard', 'on_updated_post_meta'], 10, 4);

	if(is_admin()){
		require_once(RS_PLUGIN_PATH . 'wordpress/admin/revslider-admin.class.php');
		$sr_track	= RevSliderGlobals::instance()->get('RevSliderTracking');
		$sr_addons	= RevSliderGlobals::instance()->get('RevSliderAddons');
		$sr_admin	= RevSliderGlobals::instance()->get('RevSliderAdmin');
		$sr_seo		= RevSliderGlobals::instance()->get('RevSliderSEO');
	}else{
		$rev_slider_front = new RevSliderFront();

		//outside the admin so wp-cron's automatic updater sees Slider Revolution in the
		//update_plugins transient and can run background auto-updates like for wp.org plugins
		RevSliderUpdate::init_auto_updates();
	}

	register_activation_hook(RS_PLUGIN_FILE_PATH, ['RevSliderFront', 'create_tables']);
	register_activation_hook(RS_PLUGIN_FILE_PATH, ['RevSliderFront', 'welcome_screen_activate']);

	//HPOS: WooCommerce moved orders out of the posts table and marks every plugin that has not said it copes
	//as incompatible, on a screen the shop owner reads. We only ever read products (wc_get_product, wc_price)
	//and never touch an order, so the declaration is unconditional. Must run on before_woocommerce_init
	add_action('before_woocommerce_init', function(){
		if(!class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) return;
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', RS_PLUGIN_FILE_PATH, true);
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', RS_PLUGIN_FILE_PATH, true);
	});

	add_action('plugins_loaded', ['RevSliderPluginUpdateV6', 'do_update_checks_v6']);
	add_action('plugins_loaded', ['RevSliderPluginUpdate', 'do_update_checks']);
	add_action('plugins_loaded', ['RevSliderPluginUpdate', 'do_remove_addon_checks']);
	add_action('plugins_loaded', ['RevSliderPluginUpdate', 'do_update_addon_checks']);
	add_action('admin_init', ['RevSliderPluginUpdate', 'do_wpml_addon_check']);
	//schema work belongs to activation and to the admin - on 'plugins_loaded' it ran an option read plus a
	//version_compare on every front-end request as well, and a plugin *update* (which does not re-fire the
	//activation hook) is picked up by 'admin_init' anyway, on the first admin page load after the update.
	add_action('admin_init', ['RevSliderFront', 'create_tables']);
	add_action('plugins_loaded', ['RevSliderPageTemplate', 'get_instance']);
	add_action('plugins_loaded', ['RevSliderPageEffectPanZoom', 'init']);
	add_action('plugins_loaded', ['RevSliderPageEffectPointerIX', 'init']);
	add_action('plugins_loaded', ['RevSliderPageEffectScrollAnim', 'init']);
	add_action('plugins_loaded', ['RevSliderWpSlots', 'init']);
	add_action('plugins_loaded', ['RevSliderFront', 'add_post_editor']);
	add_action('wp_loaded', [$SR_ai, 'check_open_event_ids']);
	add_filter('wpseo_sitemap_entry', ['RevSliderFront', 'get_images_for_seo'], 10, 3);
	add_filter('rocket_rucss_inline_atts_exclusions', ['RevSliderFront', 'wp_rocket_inline_atts_exclusions']);
}catch(Throwable $e){
	//$trace = $e->getTraceAsString();
	echo esc_html__('Revolution Slider Error:', 'revslider') .' <b>'. esc_html($e->getMessage()) .'</b>';
}

/**
 * add RevSlider to the page/post
 * @return void
 */
function putRevSlider($data, $put_in = ''){
	add_revslider($data, $put_in);
}

/** @return bool|void */
function add_revslider($data, $put_in = ''){
	$output = new RevSlider7Output();
	if(!$output->libraries_allowed_on_page()){
		$output->print_error_message(__('If you want to use the PHP function "add_revslider" in your code please make sure to activate ', 'revslider').__('"Load JS Libraries Globally" ', 'revslider').__('and/or add the current page to the ', 'revslider').__('"Pages to Load SR JS Libraries" option ', 'revslider').__('in the "Global Settings" of Slider Revolution.', 'revslider'));
		return false;
	}

	ob_start();
	try{
		$output->set_add_to($put_in);
		$slider	 = $output->add_slider_to_stage($data);
		$content = ob_get_contents();
	}finally{
		ob_end_clean(); //close the buffer even if rendering throws
	}

	echo $content;
}

/** @return string the plugin URL with stray line breaks removed */
function get_sr_plugin_url(){
	$url = str_replace('index.php', '', plugins_url('index.php', RS_PLUGIN_FILE_PATH));
	if(strpos($url, 'http') === false){
		$site_url	= get_site_url();
		$url		= (substr($site_url, -1) === '/') ? substr($site_url, 0, -1). $url : $site_url. $url;
	}

	return str_replace([chr(10), chr(13)], '', $url);
}
