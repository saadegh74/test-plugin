<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Page cache and optimizer plugins. The engine has to run in order and partly while the page parses, so no
 * optimizer may defer, delay or bundle its scripts; and a module lives in no post, so no cache plugin knows that a
 * page showing it changed when the module is saved.
 * @since 7.2.3
 */
class RevSliderCachePlugins extends RevSliderFunctions {

	//LiteSpeed (no-optimize, no-defer), Autoptimize and Cloudflare Rocket Loader (cfasync), Jetpack Boost
	const MARKS		= ['data-cfasync' => 'false', 'data-no-optimize' => '1', 'data-no-defer' => '1', 'data-jetpack-boost' => 'ignore'];
	//our files, core and add-ons, by their path
	const OWN_PATH	= '/plugins/revslider';
	//SiteGround Optimizer reads none of the marks, only its own lists: a file by its path, an inline script by its text
	const SG_INLINE	= ['SR7.', '_tpt.'];
	//more posts than this, or a template that many pages show: the whole cache goes
	const MAX_POSTS	= 100;
	const TEMPLATES	= ['wp_template', 'wp_template_part', 'wp_block', 'elementor_library', 'et_template', 'et_header_layout', 'et_footer_layout', 'et_body_layout'];

	protected $purged = [];

	public function __construct(){
		add_filter('revslider_script_attributes', [$this, 'script_attributes']);
		add_filter('wp_script_attributes', [$this, 'mark_enqueued']);
		add_filter('wp_inline_script_attributes', [$this, 'mark_enqueued']);
		//scripts an add-on prints inside the module's markup
		add_filter('revslider_html_output', [$this, 'mark_markup']);

		//Autoptimize removes every revslider script for visitors from a page without SR6's <rs-slides>, which SR7 never prints
		add_filter('autoptimize_filter_compatibility_revslider_remover_active', '__return_false');
		add_filter('autoptimize_filter_js_exclude', [$this, 'ao_exclude'], 20);

		add_filter('sgo_javascript_combine_excluded_internal_paths', [$this, 'sg_paths']);
		add_filter('sgo_javascript_combine_excluded_inline_content', [$this, 'sg_inline']);
		add_filter('sgo_javascript_combine_exclude', [$this, 'sg_handles']);
		add_filter('sgo_js_async_exclude', [$this, 'sg_handles']);
		add_filter('sgo_js_minify_exclude', [$this, 'sg_handles']);

		add_action('revslider_api_save_slider_after', [$this, 'purge_after_save'], 10, 2);
		add_action('sr_api_save_slide', [$this, 'purge_after_save'], 10, 2);
		add_action('revslider_quickedit_saved', [$this, 'purge_module']);
		add_action('revslider_slider_on_delete_slider', [$this, 'purge_module']);
		add_action('activated_plugin', [$this, 'purge_on_addon']);
		add_action('deactivated_plugin', [$this, 'purge_on_addon']);
	}

	/** @return string the marks as the attributes of a script the core prints itself (Functions::script_open) */
	public function script_attributes($attrs){
		foreach(self::MARKS as $name => $value) $attrs .= ' ' . $name . '="' . $value . '"';
		return $attrs;
	}

	/**
	 * wp_script_attributes and wp_inline_script_attributes: our own and our add-ons' files, and the inline scripts
	 * printed before or after them
	 * @return array
	 */
	public function mark_enqueued($attrs){
		$src = $attrs['src'] ?? '';
		if($src === ''){
			$handle	= preg_replace('/-js-(before|after|extra|translations)$/', '', (string)($attrs['id'] ?? ''), 1, $inline);
			$src	= ($inline) ? (string)(wp_scripts()->registered[$handle]->src ?? '') : '';
		}

		return $this->ours($src) ? array_merge($attrs, self::MARKS) : $attrs;
	}

	/**
	 * the module's markup, every script element in it marked; only the opening tag of each, a '<script' inside a
	 * script's own code (an ad's document.write in an HTML layer) stays as it is
	 * @return string
	 */
	public function mark_markup($html){
		$parts	= preg_split('#(</script>)#i', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
		$attrs	= $this->script_attributes('');
		foreach($parts as $i => $part){
			if(!preg_match('/<script\b([^>]*)>/i', $part, $m, PREG_OFFSET_CAPTURE) || stripos($m[1][0], 'data-no-optimize') !== false) continue;
			$parts[$i] = substr_replace($part, '<script' . $attrs, $m[0][1], 7);
		}
		return implode('', $parts);
	}

	protected function ours($src){
		return $src !== '' && strpos($src, self::OWN_PATH) !== false;
	}

	/** @return string|array Autoptimize's exclusions, a comma list or path => flag, with our files: neither bundled nor deferred */
	public function ao_exclude($exclude){
		$path = ltrim(self::OWN_PATH, '/');
		if(is_array($exclude)){
			$exclude[$path] = '';
			return $exclude;
		}
		return trim((string)$exclude . ', ' . $path, ', ');
	}

	public function sg_paths($paths){
		$paths[] = self::OWN_PATH;
		return $paths;
	}

	public function sg_inline($content){
		return array_merge((array)$content, self::SG_INLINE);
	}

	/** @return array the handles of every registered script that is ours */
	public function sg_handles($handles){
		foreach(wp_scripts()->registered as $handle => $script){
			if($this->ours((string)$script->src)) $handles[] = $handle;
		}
		return $handles;
	}

	public function purge_after_save($data, $slider_id){
		$this->purge_module($slider_id);
	}

	/**
	 * empty the page caches that hold a page showing this module; once per module and request, since one editor
	 * save fires the module's and every slide's hook
	 */
	public function purge_module($slider_id){
		$id = intval($slider_id);
		if($id <= 0 || isset($this->purged[$id]) || $this->context()->get('preview_mode')) return;
		$this->purged[$id] = true;

		$posts = $this->posts_showing($id);
		if($posts === false){
			self::purge_all();
		}else{
			self::purge_posts($posts);
		}
	}

	/**
	 * the published posts whose content, block or Elementor data names the module
	 * @return int[]|false false when the module sits in no post we can see, in a template, or in too many posts
	 */
	protected function posts_showing($id){
		global $wpdb;

		$slider = new RevSliderSlider();
		//false: outside wp-admin (wp-cli) a deleted module would throw "Slider not found." and skip the later listeners
		$slider->init_by_id($id, false);
		$alias	= ($slider->inited !== false) ? (string)$slider->get_alias() : '';
		//a deleted module has no alias left to look for
		if($alias === '') return false;

		$names	= ['"moduleId":"' . $id . '"', 'alias="' . $alias . '"', 'alias=\"' . $alias . '\"', '"alias":"' . $alias . '"', '[rev_slider ' . $alias . ']'];
		$likes	= array_map(function($n) use ($wpdb){ return '%' . $wpdb->esc_like($n) . '%'; }, $names);
		$or		= implode(' OR ', array_fill(0, count($likes), '%1$s LIKE %%s'));

		$limit	= self::MAX_POSTS + 1;
		$ids	= $wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type NOT IN ('revision', 'nav_menu_item', 'attachment') AND (" . sprintf($or, 'post_content') . ") LIMIT $limit", $likes));
		$ids	= array_merge($ids, $wpdb->get_col($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_elementor_data' AND (" . sprintf($or, 'meta_value') . ") LIMIT $limit", $likes)));
		$ids	= array_values(array_unique(array_map('intval', $ids)));

		if(empty($ids) || count($ids) > self::MAX_POSTS) return false;
		foreach($ids as $post_id){
			if(in_array(get_post_type($post_id), self::TEMPLATES, true)) return false;
		}

		return $ids;
	}

	/**
	 * an add-on switched on or off changes what the pages print (Insights counts on every page with a module), and a
	 * cached page keeps printing the old state; once per request, a bulk activation is one purge
	 */
	public function purge_on_addon($plugin){
		if(!preg_match('#^revslider-[a-z0-9]+-addon/#', (string)$plugin) || isset($this->purged['addons'])) return;
		$this->purged['addons'] = true;
		self::purge_all();
	}

	/** every page cache we know, for the given posts; no-ops for a plugin that is not there */
	public static function purge_posts($posts){
		foreach((array)$posts as $post_id){
			$post_id = intval($post_id);
			if($post_id <= 0) continue;
			clean_post_cache($post_id);
			if(function_exists('rocket_clean_post')) rocket_clean_post($post_id);
			if(function_exists('w3tc_flush_post')) w3tc_flush_post($post_id);
			if(function_exists('wp_cache_post_change')) wp_cache_post_change($post_id);
			if(has_action('litespeed_purge_post')) do_action('litespeed_purge_post', $post_id);
			if(has_action('cache_enabler_clear_page_cache_by_post')) do_action('cache_enabler_clear_page_cache_by_post', $post_id);
			if(function_exists('sg_cachepress_purge_cache')) sg_cachepress_purge_cache(get_permalink($post_id));
			if(has_action('nitropack_integration_purge_single_post')) do_action('nitropack_integration_purge_single_post', $post_id);
			if(has_action('wpfc_clear_post_cache_by_id')) do_action('wpfc_clear_post_cache_by_id', false, $post_id);
		}
		do_action('revslider_purge_posts', $posts);
	}

	public static function purge_all(){
		if(function_exists('rocket_clean_domain')) rocket_clean_domain();
		if(function_exists('w3tc_flush_all')) w3tc_flush_all();
		if(function_exists('wp_cache_clear_cache')) wp_cache_clear_cache();
		if(has_action('litespeed_purge_all')) do_action('litespeed_purge_all');
		if(has_action('cache_enabler_clear_complete_cache')) do_action('cache_enabler_clear_complete_cache');
		if(function_exists('sg_cachepress_purge_everything')) sg_cachepress_purge_everything();
		if(has_action('nitropack_integration_purge_all')) do_action('nitropack_integration_purge_all');
		if(has_action('wpfc_clear_all_cache')) do_action('wpfc_clear_all_cache');
		do_action('revslider_purge_all');
	}
}
