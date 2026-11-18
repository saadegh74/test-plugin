<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Shared foundation for the page builder integrations.
 *
 * Provides the slider picker dialog, the classic editor button and the JavaScript that inserts a
 * [rev_slider] shortcode - reused by the builder specific classes (Elementor, Avada, BeBuilder, WPBakery,
 * Divi, Gutenberg) instead of each implementing its own.
 */
class RevSliderShortcodeWizard extends RevSliderFunctions {

	/** @return void */
	public static function enqueue_scripts(){
		global $pagenow;

		$f = RevSliderGlobals::instance()->get('RevSliderFunctions');
		$action = $f->get_val($_GET, 'action');
		if($action === 'elementor') return;

		// only add scripts if native WordPress editor, Gutenberg or Visual Composer
		// Elementor has its own hooks for adding scripts
		
		if($action === 'edit' || in_array($pagenow, ['post-new.php', 'site-editor.php', 'widgets.php']) || $f->get_val($_GET, 'vc_action', '') === 'vc_inline'){
			self::add_scripts();
		}
	}

	/** @return void */
	public static function add_styles(){}

	/** @return void */
	public static function add_scripts($elementor = false, $divi = false) {
		global $SR_GLOBALS;
		$f = RevSliderGlobals::instance()->get('RevSliderFunctions');
		$action = $f->get_val($_GET, 'action');
		if($elementor && $action !== 'elementor') return;

		require_once(RS_PLUGIN_PATH . 'wordpress/public/revslider-front.class.php');

		if(!RevSliderBuilders::user_may_edit()) return;

		// checks for built-in gutenberg version
		$current_screen = function_exists('get_current_screen') ? get_current_screen() : '';
		$is_gutenberg = !empty($current_screen) && method_exists($current_screen, 'is_block_editor') && $current_screen->is_block_editor();

		if(!$elementor && !$divi){
			//verify the post type
			global $typenow, $pagenow;

			if($pagenow !== 'site-editor.php'){
				$post_types = get_post_types();
				if(empty($post_types) || !is_array($post_types)) $post_types = ['post', 'page'];
				if(!in_array($typenow, $post_types) && $pagenow !== 'widgets.php') return;
			}
			
			// checks for old plugin version
			if(!$is_gutenberg) $is_gutenberg = function_exists('is_gutenberg_page') && is_gutenberg_page();

			if($pagenow !== 'site-editor.php') self::add_styles(); //the styles need to be added through the block editor filter in site editor
		}
		
		//add v7 scripts/css
		$rs_front	= RevSliderGlobals::instance()->get('RevSliderFront');
		$rs_fonts	= RevSliderGlobals::instance()->get('RevSliderFonts');
		$rs_output	= RevSliderGlobals::instance()->get('RevSlider7Output');
		wp_enqueue_script('sr7', RS_PLUGIN_URL_CLEAN . 'core/public/js/sr7.js', [], self::asset_time('core/public/js/sr7.js'), ['strategy' => 'async']);	
		wp_enqueue_script('sr7page', RS_PLUGIN_URL_CLEAN . 'core/public/js/page.js', [], self::asset_time('core/public/js/page.js'), ['strategy' => 'async']);

		if (!$is_gutenberg) wp_enqueue_style('sr7css', RS_PLUGIN_URL_CLEAN . 'core/public/css/sr7.css', [], self::asset_time('core/public/css/sr7.css'));


		wp_enqueue_script(RevSliderFront::register_tp_tools());
		add_action('wp_footer', [$rs_fonts, 'load_google_fonts']);
		add_action('wp_footer', [$rs_output, 'add_js'], 100);

		$rsaf = new RevSliderFunctionsAdmin();
		$rsa = $rsaf->get_short_library();
		if(!empty($rsa)) $obj = $rsaf->json_encode_client_side($rsa);

		$favs = $rsaf->get_options(['favorites', 'favorites'], []);
		$favs = !empty($favs) ? $rsaf->json_encode_client_side($favs) : false;
		
		$rs_color_picker_presets = RSColorpicker::get_color_presets();
		
		$global = $rs_front->get_global_settings();
		echo $rs_front->js_add_header_scripts();

		echo self::get_shortcode_javascript();
		?>
		<script>
			var ajaxurl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
		</script>
		<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
		<?php
	}

	/** @return string the JS the page builders use to insert a slider shortcode */
	public static function get_shortcode_javascript($from_api = false){
		return RevSliderBuilderApi::shortcode_javascript($from_api);
	}

	/** @return bool */
	public static function is_registered(){
		return RevSliderBuilderApi::is_registered(); //the canonical answer lives in the neutral builder API now
	}

	/** @return string the slider picker dialog shared by all builders */
	public static function get_shortcode_extended_markup(){
		return RevSliderBuilderApi::shortcode_extended_markup();
	}	

	/**
	 * add wildcards metabox variables to posts
	 * @var $post_types: null = all, post = only posts
	 * @return void
	 */
	public static function add_slider_meta_box($post_types = null){
		try {
			$post_types = [];
			register_post_meta('', 'rs_blank_template', [
				'show_in_rest'   => true,
				'single'         => true,
				'type'           => 'string'
			]);
			register_post_meta('', 'rs_page_bg_color', [
				'show_in_rest'   => true,
				'single'         => true,
				'type'           => 'string'
			]);
			register_post_meta( '', 'slide_template_v7', [
				'show_in_rest'   => true,
				'single'         => true,
				'type'           => 'string'
			]);
		} catch (Exception $e){}
	}

	/**
	 * add wildcards metabox variables to posts
	 * @var $post_types: null = all, post = only posts
	 * @return void
	 */
	public static function add_slider_meta_box_assets($post_types = null){
		try {
			//🔴 A DEPENDENCY, not a coincidence. index.js waits for _tpt in a requestAnimationFrame loop and then
			//asks slider.get.post_templates through _tpt.ajax, so without it the block spins for ever and never
			//lists a template. It used to arrive because the Page Effects framework happened to enqueue it on the
			//same screen; naming it here is what keeps it here, and printing it FIRST ends the rAF wait.
			//tools/tools.js (revbuilder-backend) reads it too, and retries the same way until it shows up.
			wp_enqueue_script(RevSliderFront::register_tp_tools(false));
			wp_enqueue_script('slider_revolution_metabox_js', RS_PLUGIN_URL_CLEAN . 'wordpress/admin/includes/meta_box/build/index.js', ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-core-data', 'wp-data', 'tp-tools'], self::asset_time('wordpress/admin/includes/meta_box/build/index.js'));
			wp_enqueue_style('slider_revolution_metabox_css', RS_PLUGIN_URL_CLEAN . 'wordpress/admin/includes/meta_box/build/index.css', self::asset_time('wordpress/admin/includes/meta_box/build/index.css'));
		} catch (Exception $e){}
	}

	/**
	 * on save post meta. Update metaboxes data from post, add it to the post meta 
	 * @return int|false
	 */
	public static function on_updated_post_meta($meta_id, $post_id, $meta_key, $meta_value){
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;
		if(empty($post_id)) return false;
		if(function_exists('is_user_logged_in') && !is_user_logged_in() || !current_user_can('edit_post', $post_id)) return $post_id;

		if ($meta_key === "slide_template_v7") {
			if (in_array($meta_value, ['', 'default'])) {
				delete_post_meta($post_id, $meta_key);
			}
		} else if ($meta_key === "rs_page_bg_color") {
			if(strtolower($meta_value) === '#ffffff'){
				delete_post_meta($post_id, $meta_key);
			}
		} else if ($meta_key === "rs_blank_template") {
			if(empty($meta_value) && in_array(get_post_meta($post_id, '_wp_page_template', true), [RevSliderPageTemplate::KEY, RevSliderPageTemplate::LEGACY_KEY], true)){
				update_post_meta($post_id, '_wp_page_template', '');
			}
			if(!empty($meta_value) &&  $meta_value == 'on'){
				update_post_meta($post_id, '_wp_page_template', RevSliderPageTemplate::KEY);
			}
		}
	}

	/**
	 * Enqueue styles for WP Bakery
	 * @return void
	 */
	public static function enqueue_wpbakery_styles() {
		wp_enqueue_style('slider_revolution_wpbakery_css', RS_PLUGIN_URL_CLEAN . 'wordpress/admin/includes/shortcode_generator/wpbakery/assets/css/sr7-wpbakery.css', [], self::asset_time('wordpress/admin/includes/shortcode_generator/wpbakery/assets/css/sr7-wpbakery.css'));
	}	

}
