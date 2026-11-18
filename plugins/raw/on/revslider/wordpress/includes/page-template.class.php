<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if( !defined( 'ABSPATH') ) exit();

/**
 * Registers the "Slider Revolution Blank Template" page template.
 *
 * The template file lives in this plugin, not in the theme, so WordPress would not list it. The filters
 * below inject it into the theme's template cache and list, and template_include then resolves the stored
 * meta value back to the file inside the plugin.
 */
class RevSliderPageTemplate {

	/** the _wp_page_template value. No '..' in it: hoster guards for CVE-2026-87902 answer 403 to any request touching one */
	const KEY		= 'revslider-page-template.php';

	/** the value stored before 7.2.1, still resolved; migrate_legacy_key() rewrites it once per site */
	const LEGACY_KEY	= '../public/views/revslider-page-template.php';

	/**
	 * A reference to an instance of this class.
	 * @var RevSliderPageTemplate
	 */
	private static $instance;

	/**
	 * The array of templates that this plugin tracks.
	 * @var array file path (relative to this directory) => display name
	 */
	protected $templates;

	/**
	 * Returns an instance of this class.
	 * @return RevSliderPageTemplate
	 */
	public static function get_instance(){

		if(null == self::$instance) self::$instance = new RevSliderPageTemplate();
		
		return self::$instance;
	} 

	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	private function __construct(){

		$this->templates = [];

		// Add a filter to the attributes metabox to inject template into the cache.
		add_filter('page_attributes_dropdown_pages_args', [$this, 'register_project_templates']);

		// Add a filter to the save post to inject out template into the page cache
		add_filter('wp_insert_post_data', [$this, 'register_project_templates']);

		// Add a filter to the template include to determine if the page has our 
		// template assigned and return it's path
		add_filter('template_include', [$this, 'view_project_template']);

		// Add your templates to this array.
		$this->templates = [self::KEY => 'Slider Revolution Blank Template'];

		$f = \ThemePunch\SR\Core\Registry::instance()->get('RevSliderFunctions');
		if($f->get_options(['update', 'page-template'], '') !== self::KEY){
			self::migrate_legacy_key();
			$f->update_option(['update', 'page-template'], self::KEY);
		}
		
		// Fix for WP 4.7
		add_filter('theme_page_templates', [$this, 'register_project_templates_new']);

		// Add filters to the attributes metabox to inject templates to all posts
		$types = get_post_types([], 'objects');
		foreach($types as $type => $values){
			if(isset($type)) add_filter('theme_' . $type . '_templates', [$this, 'add_post_templates']);
		}
	} 


	/**
	 * Adds our template to the new post templates setting (WP >= 4.7)
	 * @return array
	 */
	public function register_project_templates_new($post_templates){
	    return array_merge($post_templates, $this->templates);
	}


	/**
	 * same for every other post type - hooked as theme_<type>_templates in the constructor
	 * @return array
	 */
	public function  add_post_templates($templates){
		return array_merge($templates, $this->templates);
	}


	/**
	 * rewrites every stored LEGACY_KEY to KEY; update_post_meta keeps the meta caches right
	 * @since 7.2.1
	 * @return void
	 */
	public static function migrate_legacy_key(){
		global $wpdb;

		$ids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_page_template' AND meta_value = %s", self::LEGACY_KEY));
		foreach($ids as $id) update_post_meta((int)$id, '_wp_page_template', self::KEY, self::LEGACY_KEY);
	}


	/**
	 * Adds our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doens't really exist.
	 *
	 * @return mixed $atts unchanged - this is a filter used purely for its side effect
	 */

	public function register_project_templates($atts){

		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5(get_theme_root() . '/' . get_stylesheet());

		// Retrieve the cache list. 
		// If it doesn't exist, or it's empty prepare an array
		$templates = wp_get_theme()->get_page_templates();

		if(empty($templates)) $templates = [];

		// New cache, therefore remove the old one
		wp_cache_delete($cache_key , 'themes');

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge($templates, $this->templates);

		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add($cache_key, $templates, 'themes', 1800);

		return $atts;
	} 

	/**
	 * Checks if the template is assigned to the page
	 * @return string path to our template file, or the theme's $template when it does not apply
	 */
	public function view_project_template($template){

		if(is_search()) return $template; //fix for search issue (B-5798177839)

		global $post;
		
		if(!isset($post->ID)) return $template;
		if(!in_array(get_post_meta($post->ID, '_wp_page_template', true), [self::KEY, self::LEGACY_KEY], true)) return $template;

		$file = RS_PLUGIN_PATH . 'wordpress/public/views/revslider-page-template.php';

		if(file_exists($file)) return $file; // Just to be safe, we check if the file exist first

		//the missing file used to be echoed here, which printed the absolute server path into the page for
		if(defined('WP_DEBUG') && WP_DEBUG) error_log('RevSlider: page template not found: ' . $file);

		return $template;
	}
}