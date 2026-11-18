<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Front end bootstrap.
 *
 * Registers the front end scripts, the admin bar entry and the integrations with consent, SEO and caching
 * plugins. The table definitions live in the core (ThemePunch\SR\Core\Schema); WordPress runs them through
 * create_tables() on activation and on admin_init.
 */
class RevSliderFront extends RevSliderFunctions {
	//the TABLE_* constants are inherited from the neutral core (ThemePunch\SR\Core\Data)

	//the table versions live with the definitions in the core; the names stay readable here
	const CURRENT_TABLE_VERSION	 = \ThemePunch\SR\Core\Schema::CURRENT_TABLE_VERSION;
	const SLOTS_TABLE_VERSION	 = \ThemePunch\SR\Core\Schema::SLOTS_TABLE_VERSION;
	
	public function __construct(){
		parent::__construct();
		add_action('wp_enqueue_scripts', [$this, 'add_actions']);
		add_action('plugins_loaded', [$this, 'add_cookie_actions']);
	}

	/** @return void */
	public static function welcome_screen_activate(){
		set_transient('_revslider_welcome_screen_activation_redirect', true, 60);
	}

	/**
	 * Add Meta Generator Tag in FrontEnd
	 * @return void
	 */
	public static function add_meta_generator(){
		echo apply_filters('revslider_meta_generator', '<meta name="generator" content="Powered by Slider Revolution ' . RS_REVISION . ' - responsive, Mobile-Friendly Slider Plugin for WordPress with comfortable drag and drop interface." />' . "\n");
	}

	/**
	 * tp-tools, the runtime Essential Grid ships too, under the one handle both plugins use. A copy registered with an
	 * older RS_TP_TOOLS and not printed yet gets our file and version in place: its deps and inline data belong to the
	 * other plugin's scripts and stay. An equal or newer one is kept. The ver is RS_TP_TOOLS plus the file's time, so a
	 * browser never keeps an old copy; version_compare orders it by the first three parts
	 * @since 7.2.9
	 * @param array|bool $args wp_register_script's last argument (strategy, in_footer)
	 * @return string the handle, for wp_enqueue_script or a dependency list
	 */
	public static function register_tp_tools($args = ['strategy' => 'async']){
		$src	= RS_CORE_URL_CLEAN . 'public/js/libs/tptools.js';
		$ver	= RS_TP_TOOLS . '.' . self::asset_time('core/public/js/libs/tptools.js');
		$known	= wp_scripts()->registered['tp-tools'] ?? null;
		if($known === null){
			wp_register_script('tp-tools', $src, [], $ver, $args);
		}elseif(version_compare((string)$known->ver, RS_TP_TOOLS, '<') && !wp_script_is('tp-tools', 'done')){
			$known->src = $src;
			$known->ver = $ver;
		}
		if(!wp_script_is('_tpt', 'registered')) wp_register_script('_tpt', false, ['tp-tools'], $ver); //the admin's and the builders' name before 7.2.9
		return 'tp-tools';
	}

	/**
	 * Add all actions that the frontend needs here
	 * @return void registers the front end scripts and styles
	 **/
	public function add_scripts(){
		wp_enqueue_script(self::register_tp_tools());
		foreach(\ThemePunch\SR\Render\Page::FRONT_SCRIPTS as $handle => $src) if($handle !== 'tp-tools') wp_enqueue_script($handle, RS_CORE_URL_CLEAN . $src, [], $this->asset_time('core/' . $src), ['strategy' => 'async']);
		foreach(\ThemePunch\SR\Render\Page::FRONT_STYLES as $handle => $src) wp_enqueue_style($handle, RS_CORE_URL_CLEAN . $src, [], $this->asset_time('core/' . $src));
		
		do_action('sr_front_add_scripts', $this);
	}

	/**
	 * Add all filters to block removal of SR7 modules from cookie banners
	 * @return void registers the consent plugin filters
	 **/
	public function add_cookie_actions(){
		/**
		 * Real Cookie Banner
		 * Let Real Cookie Banner IGNORE Slider Revolution inline scripts
		 * so SR7 can initialize even without consent.
		 */
		add_filter('RCB/Blocker/InlineScript/IsBlocked', function($isBlocked, $script){
			//SR7 JSON always contains "SR7.JSON["
			if(strpos($script, 'SR7.JSON[') === false) return $isBlocked;
		
			//Prefer official API: disable blocking for this script
			if(method_exists($isBlocked, 'disableBlocking')){
				$isBlocked->disableBlocking();
				return $isBlocked;
			}

			//Fallback: clear all block data (also deactivates blocking)
			if(method_exists($isBlocked, 'setBlocked')) $isBlocked->setBlocked([]);
			if(method_exists($isBlocked, 'setBlockedExpressions')) $isBlocked->setBlockedExpressions([]);

			return $isBlocked;
		}, 10, 2);

		/**
		 * Complianz
		 * Treat the SR7 inline JSON script as functional in Complianz
		 * so it will NOT be blocked / converted to type="text/plain".
		 * $total_match contains the full <script> ... </script> match
		 * $found = true if Complianz thinks this is a 3rd-party script
		 */
		add_filter('cmplz_service_category', function($class, $total_match, $found){
			//Only touch SR7 inline scripts
			if($found && strpos($total_match, 'SR7.JSON[') !== false) return 'functional'; // Mark as "functional" so Complianz doesn't prior-block it

			return $class;
		}, 10, 3);

		/**
		 * CookieYes
		 * Add data-wcc="necessary" to ALL <script> tags inside Slider Revolution output
		 **/
		//if(is_plugin_active('cookie-law-info/cookie-law-info.php')){
		
		add_filter('revslider_html_output', function ($content, $slider_obj){
			if(!defined('CLI_VERSION')) return $content;

			return preg_replace(
				'/<script\b(?![^>]*data-wcc)/i',
				'<script data-wcc="necessary"',
				$content
			);
		}, 10, 2);

		/**
		 * Cookiebot
		 **/
		add_filter('revslider_html_output', function($content, $slider_obj){

			if(!defined('CYBOT_COOKIEBOT_PLUGIN_URL')) return $content;

			// Only touch SR7 scripts, not everything
			if(false === strpos($content, 'SR7.JSON[')) return $content;

			// For inline SR7 script tags, add data-cookieconsent="ignore"
			// so Cookiebot’s *auto* blocker will not touch them.
			// (This is the official way to tell Cookiebot to ignore a script.) :contentReference[oaicite:0]{index=0}
			$content = preg_replace(
				'/<script\b(?![^>]*data-cookieconsent)/i',
				'<script data-cookieconsent="ignore"',
				$content
			);

			return $content;
		}, 10, 2 );

		/**
		 * Cookiebot
		 **/
		add_filter('revslider_html_output', function($content, $slider_obj){

			if(!defined('BORLABS_COOKIE_CACHE_PATH')) return $content;

			// Only touch SR7 scripts, not everything
			if(false === strpos($content, 'SR7.JSON[')) return $content;

			// For inline SR7 script tags, add data-cookieconsent="ignore"
			// so Cookiebot’s *auto* blocker will not touch them.
			// (This is the official way to tell Cookiebot to ignore a script.) :contentReference[oaicite:0]{index=0}
			 $content = preg_replace(
				'/<script\b(?![^>]*data-sr7-essential)/i',
				'<script data-sr7-essential="1"',
				$content
			);

			return $content;
		}, 10, 2 );

		/**
		 * Borlabs Cookie
		 */
		add_filter('revslider_js_add_header_scripts_js', function($content){
			if(!defined('BORLABS_COOKIE_VERSION')) return $content;

			return str_replace(
				'<script',
				'<script data-borlabs-cookie-script-blocker-ignore',
				$content
			);
		});
		
	}

	/**
	 * Add custom HTML to the style tags
	 * @return string
	 **/
	public function add_html_to_style_tags($html, $handle) {
		// Check if it's the stylesheet you want to modify
		if ($handle !== 'sr7pagecsslp') return $html; //frozen, unreached in v7: no style is registered under this handle, the engine loads sr7.lp.css itself

		$html = str_replace("rel='stylesheet'", "rel='preload'", $html);		
		$html = str_replace('/>', 'as="style" fetchpriority="low" onload="this.rel=\'stylesheet\'" />', $html);
		$html = str_replace("rel='stylesheet'", "rel='preload'", $html);
		$html = str_replace('/>', 'as="style" fetchpriority="high" onload="this.rel=\'stylesheet\'" />', $html);

		return $html;
	}
	  
	/** @return bool */
	public function add_actions(){
		$g = &$this->context()->root();
		
		$fonts		= RevSliderGlobals::instance()->get('RevSliderFonts');
		$widget	 	= is_active_widget(false, false, 'rev-slider-widget', true);
		
		$load = false;
		$load = apply_filters('revslider_include_libraries', $load);
		$load = ($this->context()->get('preview_mode') === true) ? true : $load;
		$load = (self::has_shortcode('rev_slider') === true || self::has_shortcode('sr7') === true) ? true : $load;
		$load = ($widget !== false) ? true : $load;
		
		$load = ((new RevSlider7Output())->libraries_allowed_on_page() === true) ? true : $load;
		//a page without a module whose own elements run add-on effects (Essential Grid cards): the engine's base and the
		//asked add-ons' files, which SR7.F.effect() loads
		$effects = (array)apply_filters('revslider_page_effects', []);
		$load = !empty($effects) ? true : $load;

		add_action('wp_before_admin_bar_render', [$this, 'add_admin_menu_nodes']);
		add_action('wp_footer', [$this, 'add_admin_bar'], 99);
		add_action('wp_footer', ['RevSliderFront', 'add_pending_migration'], 99);
		
		if($load === false) return false;

		$this->add_scripts();

		add_action('wp_head', [$this, 'js_add_header_scripts'], 99);
		if(!empty($effects)) add_action('wp_head', function() use ($effects){ \ThemePunch\SR\Core\Addon\Base::print_page_effects($effects); }, 99);
		add_action('wp_head', [$this, 'load_header_fonts']);
		add_filter('style_loader_tag', [$this, 'add_html_to_style_tags'], 10, 2);
		add_action('wp_footer', [$fonts, 'load_google_fonts']);
		add_action('wp_footer', [$this, 'add_deprecation_warnings']);
		add_action('wp_head', ['RevSliderFront', 'add_meta_generator']);
	}

	/** @return bool */
	public function js_add_header_scripts(){
		if($this->context()->get('header_js') === true) return false;

		echo $this->get_header_html();
	}

	/**
	 * the asset manifest; body lives on Render\Page, this stays as frozen API
	 * @since 7.2.1
	 * @return array
	 */
	public function build_asset_manifest(){
		return $this->render_page()->build_asset_manifest();
	}

	/**
	 * the head script block as a string; body lives on Render\Page, this stays as frozen API
	 * @since 7.2.1
	 * @return string
	 */
	public function get_header_html(){
		return $this->render_page()->get_header_html();
	}

	/** @return string */
	public function load_v7_slider(){
		return $this->render_page()->load_v7_slider();
	}

	/**
	 * print in header
	 * @return void
	 **/
	public function load_header_fonts(){
		echo $this->render_page()->get_fonts_preconnect_html();
	}

	/**
	 * adds deprecation warnings of functions that will cease to exist in v7
	 * @return void prints the collected deprecation notices as an HTML comment
	 */
	public function add_deprecation_warnings(){
		echo $this->render_page()->get_deprecation_html();
	}

	/**
	 * the V7 tables this plugin owns (unprefixed)
	 * @return array
	 **/
	public static function get_own_tables(){
		return \ThemePunch\SR\Core\Schema::get_own_tables();
	}

	/**
	 * Check V7 Tables
	 * @return array - array of tables that are missing
	 **/
	public static function check_tables(){
		return \ThemePunch\SR\Core\Schema::check_tables();
	}

	/**
	 * Create V7 Tables - the activation hook and admin_init call this name
	 * @return void
	 **/
	public static function create_tables(){
		\ThemePunch\SR\Core\Schema::create_tables();
	}

	/**
	 * Add functionality to gutenberg, elementor, visual composer and so on
	 * @return void wires up the Gutenberg/Elementor/WPBakery integrations
	 **/
	public static function add_post_editor(){
		/**
		 * Page Editor Extensions
		 **/
		if(function_exists('is_user_logged_in') && is_user_logged_in()){
			//only include gutenberg for production
			if(is_admin() && defined('ABSPATH')){
				include_once(ABSPATH . 'wp-admin/includes/plugin.php');
				if(function_exists('is_plugin_active') && !is_plugin_active('revslider-gutenberg/plugin.php')){
					require_once(RS_PLUGIN_PATH . 'wordpress/admin/includes/shortcode_generator/gutenberg/gutenberg-block.php');
					new RevSliderGutenberg('gutenberg/');
				}
			}
			
			require_once(RS_PLUGIN_PATH . 'wordpress/admin/includes/shortcode_generator/shortcode_generator.class.php');

			//Shortcode Wizard Includes
			//WPB Functionality
			require_once(RS_PLUGIN_PATH . 'wordpress/admin/includes/shortcode_generator/wpbakery/wpbakery.class.php');
			add_action('vc_before_init', ['RevSliderWpbakeryShortcode', 'visual_composer_include']);
			add_action('vc_before_init', ['RevSliderShortcodeWizard', 'enqueue_wpbakery_styles']);
			add_action('admin_enqueue_scripts', ['RevSliderShortcodeWizard', 'enqueue_scripts']);

			// BeBuilder Functionality
			require_once(RS_PLUGIN_PATH . 'wordpress/admin/includes/shortcode_generator/bebuilder/bebuilder.class.php');
			RevSliderBeBuilder::init();

			// Avada Builder Functionality
			require_once(RS_PLUGIN_PATH . 'wordpress/admin/includes/shortcode_generator/avada/avada.class.php');
			RevSliderAvada::init(RevSliderShortcodeWizard::is_registered());
		}

		//Elementor Global
		require_once(RS_PLUGIN_PATH . 'wordpress/admin/includes/shortcode_generator/elementor/elementor.class.php');
		add_action('init', ['RevSliderElementor', 'init']);
	}

	/**
	 * sets the post saving value to true, so that the output echo will not be done
	 * @return void
	 **/
	public static function set_post_saving(){
		RevSliderGlobals::instance()->get('RevSliderFunctions')->context()->set('save_post', true); //static context, no $this
	}
	
	/**
	 * get the images from posts/pages for yoast seo
	 * @return string adds the slider images to the Yoast sitemap entry
	 **/
	public static function get_images_for_seo($url, $type, $user){
		if(in_array($type, ['user', 'term'], true)) return $url;
		if(!is_object($user) || !isset($user->ID)) return $url;
		
		$post = get_post($user->ID);
		if(is_a($post, 'WP_Post') && (has_shortcode($post->post_content, 'rev_slider') || has_shortcode($post->post_content, 'sr7'))){
			preg_match_all('/\[rev_slider.*alias=.(.*)"\]/', $post->post_content, $shortcodesold);
			preg_match_all('/\[sr7.*alias=.(.*)"\]/', $post->post_content, $shortcodes);
			$sc = [];
			if(isset($shortcodes[1]) && $shortcodes[1] !== '')		 $sc = array_merge($sc, $shortcodes[1]);
			if(isset($shortcodesold[1]) && $shortcodesold[1] !== '') $sc = array_merge($sc, $shortcodesold[1]);

			foreach($sc ?? [] as $s){
				if(strpos($s, '"') !== false){
					$s = explode('"', $s);
					$s = (isset($s[0])) ? $s[0] : '';
				}
				if(!RevSliderSlider::alias_exists($s)) continue;
				
				$sldr = new RevSliderSlider();
				$sldr->init_by_alias($s);
				$sldr->get_slides();
				$imgs = $sldr->get_images();
				if(!empty($imgs)){
					if(!isset($url['images'])) $url['images'] = [];
					foreach($imgs as $v){
						$url['images'][] = $v;
					}
				}
			}
		}
		
		return $url;
	}

	/**
	 * add admin nodes
	 * @since: 5.0.5
	 * @return void
	 */
	public function add_admin_menu_nodes(){
		if(!is_super_admin() || !is_admin_bar_showing()){
			return;
		}

		self::_add_node('<span class="rs-label">Slider Revolution</span>', false, admin_url('admin.php?page=revslider'), ['class' => 'revslider-menu'], 'revslider'); //<span class="wp-menu-image dashicons-before dashicons-update"></span>

		//add all nodes of all Slider
		$sl = new RevSliderSlider();
		$sliders = $sl->get_slider_for_admin_menu();

		if(!empty($sliders)){
			foreach ($sliders as $id => $slider){
				self::_add_node('<span class="rs-label" data-alias="' . esc_attr($slider['alias']) . '">' . esc_html($slider['title']) . '</span>', 'revslider', admin_url('admin.php?page=revslider&view=editor&module='.$id), ['class' => 'revslider-sub-menu'], esc_attr($slider['alias'])); //<span class="wp-menu-image dashicons-before dashicons-update"></span>
			}
		}
	}

	/**
	 * add admin node
	 * @since: 5.0.5
	 * @return void
	 */
	public static function _add_node($title, $parent = false, $href = '', $custom_meta = [], $id = ''){
		if(!is_super_admin() || !is_admin_bar_showing()){
			return;
		}

		$id = ($id == '') ? strtolower(str_replace(' ', '-', $title)) : $id;
		
		//links from the current host will open in the current window
		$meta = (strpos($href, site_url()) !== false) ? [] : ['target' => '_blank']; //external links open in new tab/window
		$meta = array_merge($meta, $custom_meta);
		
		global $wp_admin_bar;
		$wp_admin_bar->add_node(['parent'=> $parent, 'id' => $id, 'title' => $title, 'href' => $href, 'meta' => $meta]);
	}

	/**
	 * a theme's demo import leaves SR6 modules that only the overview's browser migration finishes; while one waits,
	 * a page an editor of modules opens runs that overview unseen, and the front reloads once it is saved.
	 * Not display:none, a hidden frame gets no animation frames
	 * @since 7.2.5
	 * @return void
	 */
	public static function add_pending_migration(){
		if(!is_user_logged_in()) return;

		$f = RevSliderGlobals::instance()->get('RevSliderFunctions');
		if(!current_user_can($f->resolve_user_role())) return;
		if(!RevSliderGlobals::instance()->get('RevSliderPluginUpdateV6')->pending_migration_due()) return;

		$src = esc_url(admin_url('admin.php?page=revslider&sr_migrate=1'));
		$css = 'position:fixed;left:0;bottom:0;width:1px;height:1px;border:0;opacity:0;pointer-events:none';
		if(is_admin()){
			echo '<iframe id="sr7-pending-migration" src="'.$src.'" title="Slider Revolution" aria-hidden="true" tabindex="-1" style="'.$css.'"></iframe>'."\n";
			return;
		}

		//the waiting modules this page shows go first, with a note, and the page reloads when they are saved
		$pending = RevSliderGlobals::instance()->get('RevSliderPluginUpdateV6')->get_pending_ids();
		$json	 = fn($v) => wp_json_encode($v, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES);
		?>
<script>
(() => {
	const pending = <?php echo $json(array_values($pending)); ?>;
	const first = [...new Set([...document.querySelectorAll('sr7-module[data-id]')].map(m => parseInt(m.dataset.id)))].filter(id => pending.includes(id));
	const f = document.createElement('iframe');
	Object.assign(f, {id:'sr7-pending-migration', title:'Slider Revolution', tabIndex:-1, src:<?php echo $json(admin_url('admin.php?page=revslider&sr_migrate=1')); ?> + (first.length ? '&sr_first=' + first.join(',') : '')});
	f.setAttribute('aria-hidden', 'true');
	f.style.cssText = '<?php echo $css; ?>';
	document.body.append(f);
	if (!first.length) return;
	const n = document.createElement('div');
	Object.assign(n, {id:'sr7-pending-migration-note', role:'status', textContent:<?php echo $json(__('Slider Revolution: preparing imported sliders...', 'revslider')); ?>});
	n.style.cssText = 'position:fixed;right:16px;bottom:16px;z-index:2147483000;padding:10px 14px;border-radius:6px;background:rgba(17,17,17,.9);color:#fff;font:13px/1.4 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;box-shadow:0 4px 16px rgba(0,0,0,.25)';
	document.body.append(n);
	window.addEventListener('message', e => { if (e.data === 'sr7-migrated' && e.source === f.contentWindow) location.reload(); });
	//a run that cannot finish takes the note away instead of leaving it up
	setTimeout(() => n.remove(), 60000);
})();
</script>
<?php
	}

	/**
	 * add admin menu points in ToolBar Top
	 * @since: 5.0.5
	 * @return void
	 */
	public static function add_admin_bar(){
		if(!is_super_admin() || !is_admin_bar_showing()) return;
		?>
		<script>
			/** @return void */
			function rs_adminBarToolBarTopFunction() {
				var revSliderDefault = document.querySelector('#wp-admin-bar-revslider-default');
				var sr7Module = document.querySelectorAll('sr7-module');

				if (revSliderDefault && sr7Module.length > 0) {
					var aliases = [];

					sr7Module.forEach(function(element) {
						aliases.push(element.getAttribute('data-alias'));
					});

					if (aliases.length > 0) {
						revSliderDefault.querySelectorAll('li').forEach(function(li) {
							var rsLabel = li.querySelector('.ab-item .rs-label');
							var t = rsLabel ? rsLabel.getAttribute('data-alias') : undefined;
							t = t !== undefined && t !== null ? t.trim() : t;

							if (aliases.indexOf(t) === -1) {
								li.remove();
							}
						});
					}
				} else {
					var revSlider = document.querySelector('#wp-admin-bar-revslider');
					if (revSlider) {
						revSlider.remove();
					}
				}
			}
			
			var adminBarLoaded_once = false;

			if (document.readyState === "loading") {
				document.addEventListener('readystatechange', function() {
					if ((document.readyState === "interactive" || document.readyState === "complete") && !adminBarLoaded_once) {
						adminBarLoaded_once = true;
						rs_adminBarToolBarTopFunction();
					}
				});
			} else {
				adminBarLoaded_once = true;
				rs_adminBarToolBarTopFunction();
			}
		</script>
		<?php
	}

	/**
	 * pre 7.0 name of add_admin_bar(), themes like Impreza still hook it into wp_footer and fatal without it
	 * @return void
	 */
	public static function putAdminBarMenus(){
		rs_deprecated_shim('RevSliderFront::putAdminBarMenus()', 'RevSliderFront::add_admin_bar()');

		self::add_admin_bar();
	}

	/**
	 * prevent WP Rocket from removing our frontend css for font loading
	 * @return array keeps WP Rocket from stripping our inline script attributes
	 */
	public static function wp_rocket_inline_atts_exclusions($inline_atts_exclusions){
		$inline_atts_exclusions[] = "sr7-inline-css";	
		return $inline_atts_exclusions;
	}

	/**
	 * check the current post for the existence of a short code
	 * @return bool does this post contain the given shortcode?
	 */  
	public static function has_shortcode($shortcode = ''){ 
		if(empty($shortcode)) return false;
		if(!is_singular()) return false;
		
		$post = get_post(get_the_ID());  
		if(empty($post)) return false;
		if(!isset($post->post_content)) return false;

		return (stripos($post->post_content, '[' . $shortcode) !== false) ? true : false;
	}

}
