<?php
/**
 * Page builder integrations — shared registry.
 *
 * A builder integration is the code that lets a page builder place a Slider Revolution module: pick one,
 * configure it, preview it. Seven of them ship with the plugin and each used to arrange its own assets,
 * repeat the same localization and re-declare the same options. This class is the half they share; what
 * stays with each integration is only what is genuinely its own (see SR7.Builders in
 * core/admin/assets/js/tools/shortcode.js for the JavaScript half).
 *
 * Contract (see docs/builder-harmonization/CONTRACT.md, B5):
 *   PHP : RevSliderBuilders::register($slug, $args)
 *   JS  : SR7.Builders.register(slug, adapter)
 *
 * Modelled on RevSliderPageEffects::register_type(): declarative config, hooks booted once on the first
 * registration, nothing loaded for a builder that is not present.
 *
 * @author    ThemePunch <info@themepunch.com>
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

class RevSliderBuilders {

	/**
	 * The version of this API. What is documented on RevSliderBuilders and SR7.Block is stable within a
	 * major version; what is not documented is not part of it, whatever its visibility.
	 */
	const API_VERSION = 1;

	/** @var array<string,array> registered builders */
	private static $builders = [];
	private static $booted = false;

	/**
	 * Register a builder integration. Called on plugins_loaded, before 'init'.
	 *
	 * @param string $slug  e.g. 'elementor'
	 * @param array  $args  title, detect, screens, editor, style, caps, boot, surfaces
	 * @return bool
	 */
	public static function register($slug, $args = []){
		$slug = sanitize_key($slug);
		if($slug === '') return false;

		self::$builders[$slug] = array_merge([
			'slug'		=> $slug,
			'title'		=> ucfirst($slug),
			// callable():bool - is this builder present at all. Without one the builder never loads.
			'detect'	=> null,
			// callable():bool - is the current screen one this builder edits on. Defaults to any admin screen
			// the shortcode wizard already answers for.
			'screens'	=> null,
			'editor'	=> '',		// plugin relative path to the integration's editor script
			'style'		=> '',		// plugin relative path to its editor stylesheet
			// what the builder can do natively, so core does not offer what it cannot honour. 'live-preview'
			// is the only one so far: it means the builder renders the page as it will look (CONTRACT B7).
			'caps'		=> [],
			// surfaces of the same builder we do NOT support - see the surface rule in CONTRACT D4. The JS
			// half decides what to draw; this is here so the decision is declared in one place.
			'unsupported'	=> [],
			'boot'		=> null		// callable() run once when the builder is present, for its own hooks
		], $args);

		self::boot();

		/**
		 * A builder integration has registered.
		 *
		 * The moment to hang anything of your own off a builder you do not own - its assets, its screens,
		 * an extra capability. Fires on plugins_loaded, before 'init', whether or not the builder turns out
		 * to be present on this screen.
		 *
		 * @param string $slug    e.g. 'elementor'
		 * @param array  $builder the registration, with the defaults filled in
		 */
		do_action('revslider_builder_registered', $slug, self::$builders[$slug]);

		return true;
	}

	/** @return array<string,array> every registered builder */
	public static function get_builders(){
		return self::$builders;
	}

	/**
	 * The builders actually present on this site.
	 * @return array<string,array>
	 */
	public static function active(){
		$active = [];
		foreach(self::$builders as $slug => $builder){
			if(self::is_present($builder)) $active[$slug] = $builder;
		}

		return $active;
	}

	/**
	 * Does a builder declare a capability.
	 * @return bool
	 */
	public static function supports($slug, $cap){
		$slug = sanitize_key($slug);

		return isset(self::$builders[$slug]) && in_array($cap, (array)self::$builders[$slug]['caps'], true);
	}

	/**
	 * register the shared hooks once, on the first register() call
	 * @return void
	 */
	private static function boot(){
		if(self::$booted) return;
		self::$booted = true;

		//An integration that registers on plugins_loaded at 20 or later - the priority the documentation's
		//own example uses - would otherwise never be booted: adding a callback to the priority currently
		//running is not something to rely on. 'init' at 0 still comes before everything a builder needs to
		//be registered for, so nothing is lost by falling back to it.
		if(did_action('plugins_loaded')){
			add_action('init', [self::class, 'boot_builders'], 0);
		}else{
			add_action('plugins_loaded', [self::class, 'boot_builders'], 20);
		}

		add_action('admin_enqueue_scripts', [self::class, 'enqueue'], 20);
	}

	/**
	 * Let each present builder wire up whatever is genuinely its own.
	 *
	 * Each builder is booted once, however often this runs - a builder registered after the first pass
	 * still gets its turn, and one that has already had it does not get a second.
	 *
	 * @return void
	 */
	public static function boot_builders(){
		static $booted = [];

		foreach(self::active() as $slug => $builder){
			if(isset($booted[$slug])) continue;
			$booted[$slug] = true;

			if(is_callable($builder['boot'])) call_user_func($builder['boot'], $builder);
		}
	}

	/** @return bool */
	private static function is_present($builder){
		return is_callable($builder['detect']) ? (bool)call_user_func($builder['detect']) : false;
	}

	/** @return bool */
	private static function is_on_screen($builder){
		if(is_callable($builder['screens'])) return (bool)call_user_func($builder['screens']);

		// the screens the shortcode wizard already loads on: editing a post, or a screen that embeds one
		global $pagenow, $typenow;
		if(in_array($pagenow, ['post-new.php', 'site-editor.php', 'widgets.php'], true)) return true;
		if($pagenow === 'post.php') return true;

		unset($typenow);

		return false;
	}

	/**
	 * The assets every integration needs before its own script can do anything: the SR7 front runtime, the
	 * tools bundle, the block model, and the handful of values the JavaScript reads out of SR7.E.
	 *
	 * Idempotent - WordPress dedupes by handle, so a builder may call this itself if it loads on a screen
	 * the registry does not know about.
	 *
	 * @return void
	 */
	public static function enqueue_shared(){
		static $done = false;
		if($done) return;
		$done = true;

		if(!self::user_may_edit()) return;

		if(RevSliderFunctions::has_single_gsap()) wp_enqueue_script('tpgsap', RS_PLUGIN_URL_CLEAN . 'core/public/js/libs/tpgsap.js', [], RevSliderFunctions::asset_time('core/public/js/libs/tpgsap.js'), ['strategy' => 'async']);
		wp_enqueue_script(RevSliderFront::register_tp_tools());
		wp_enqueue_script('revbuilder-backend', RS_PLUGIN_URL_CLEAN . 'core/admin/assets/js/tools/tools.js', [], RevSliderFunctions::asset_time('core/admin/assets/js/tools/tools.js'), false);
		wp_enqueue_script('sr7-tools-shortcode', RS_PLUGIN_URL_CLEAN . 'core/admin/assets/js/tools/shortcode.js', ['revbuilder-backend'], RevSliderFunctions::asset_time('core/admin/assets/js/tools/shortcode.js'), true);

		// what the JavaScript reads: where to call, where the plugin lives, what it may do. Every integration
		// localized some subset of this by hand, and one of them under a different handle each time.
		wp_localize_script('sr7-tools-shortcode', 'SR7ShortcodeData', self::client_config());
	}

	/**
	 * The values the JavaScript half needs. Kept in one place so an integration cannot ship a subset of them
	 * and then wonder why the picker cannot reach the server.
	 *
	 * @return array
	 */
	public static function client_config(){
		$uploads = wp_upload_dir();

		return [
			'ajaxurl'		=> admin_url('admin-ajax.php'),
			'plugin_url'	=> RS_PLUGIN_URL_CLEAN,
			'core_url'		=> RS_CORE_URL_CLEAN,
			'block_nonce'	=> wp_create_nonce('revslider_actions'),
			'wp_upload_url'	=> rtrim($uploads['baseurl'] ?? '', '/') . '/',
			'builders'		=> array_map(static function($builder){
				return [
					'slug'			=> $builder['slug'],
					'title'			=> $builder['title'],
					'caps'			=> array_values((array)$builder['caps']),
					'unsupported'	=> array_values((array)$builder['unsupported'])
				];
			}, self::active())
		];
	}

	/**
	 * Load the shared assets plus each present builder's own, on the screens it edits on.
	 * @return void
	 */
	public static function enqueue(){
		if(!self::user_may_edit()) return;

		foreach(self::active() as $slug => $builder){
			if(!self::is_on_screen($builder)) continue;

			self::enqueue_shared();

			if($builder['style'] !== ''){
				wp_enqueue_style('sr7-builder-' . $slug, RS_PLUGIN_URL_CLEAN . $builder['style'], [], RevSliderFunctions::asset_time($builder['style']));
			}
			if($builder['editor'] !== ''){
				wp_enqueue_script('sr7-builder-' . $slug, RS_PLUGIN_URL_CLEAN . $builder['editor'], ['sr7-tools-shortcode'], RevSliderFunctions::asset_time($builder['editor']), true);
			}
		}
	}

	/** delegates to the platform-neutral RevSliderBuilderApi - frozen public @since 7.2.1 */
	public static function type_label($type, $slides){ return RevSliderBuilderApi::type_label($type, $slides); }


	/** delegates to the platform-neutral RevSliderBuilderApi - frozen public @since 7.2.1 */
	public static function modules($args = []){ return RevSliderBuilderApi::modules($args); }


	/** delegates to the platform-neutral RevSliderBuilderApi - frozen public @since 7.2.1 */
	public static function module($which){ return RevSliderBuilderApi::module($which); }



	/** delegates to the platform-neutral RevSliderBuilderApi - frozen public @since 7.2.1 */
	public static function dropdown($args = []){ return RevSliderBuilderApi::dropdown($args); }


	/** delegates to the platform-neutral RevSliderBuilderApi - frozen public @since 7.2.1 */
	public static function shortcode($atts = []){ return RevSliderBuilderApi::shortcode($atts); }


	/** delegates to the platform-neutral RevSliderBuilderApi - frozen public @since 7.2.1 */
	public static function parse_shortcode($shortcode){ return RevSliderBuilderApi::parse_shortcode($shortcode); }


	/** delegates to the platform-neutral RevSliderBuilderApi - frozen public @since 7.2.1 */
	public static function is_registered(){ return RevSliderBuilderApi::is_registered(); }

	/** delegates to the platform-neutral RevSliderBuilderApi - frozen public @since 7.2.1 */
	public static function user_may_edit(){ return RevSliderBuilderApi::user_may_edit(); }

}
