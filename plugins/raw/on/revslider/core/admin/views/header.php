<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

global $SR_GLOBALS;
$sr_af		= new RevSliderFunctionsAdmin();
$sr_addon	= RevSliderGlobals::instance()->get('RevSliderAddons');
$sr_slider	= new RevSliderSlider();
$sr_track	= new RevSliderTracking();
$sr_upd		= new RevSliderPluginUpdate();
$sr_updv6	= new RevSliderPluginUpdateV6();
$sr_ai		= RevSliderGlobals::instance()->get('RevSliderAI');
$sr_o		= RevSliderGlobals::instance()->get('RevSliderOptimizer');

$sr_valid				 = $sr_af->_truefalse($sr_af->get_options(['system', 'valid'], 'false'));
$sr_latest_version		 = $sr_af->get_options(['system', 'version'], RS_REVISION);
$sr_stable_version		 = $sr_af->get_options(['system', 'stable'], '4.2');
$sr_library_preloaded	 = $sr_af->get_options(['system', 'library_preloaded'], false);
$sr_emergency_update	 = ($sr_valid !== true && version_compare($sr_latest_version, $sr_stable_version, '<') === true) ? true : false;
$sr_latest_version		 = ($sr_valid !== true && version_compare($sr_latest_version, $sr_stable_version, '<') === true) ? $sr_stable_version : $sr_latest_version;
$sr_added_image_sizes	 = $sr_af->get_all_image_sizes();
$sr_image_meta_todo		 = $sr_af->get_options(['other', 'image-meta'], []);
$sr_color_picker_presets = RSColorpicker::get_color_presets();
$sr_backend_fonts		 = $sr_af->get_font_familys();
$sr_env					 = $sr_af->editor_environment()->values(); //the host values the header injects
$sr_upload_url			 = $sr_env['upload_url'];
$sr_v6_exists			 = RevSliderPluginUpdateV6::recheck_v6_tables();
$sr_migrate_only		 = isset($_GET['sr_migrate']); //the unseen run a page starts for imports waiting
$sr_pending_imports		 = $sr_updv6->get_pending_imports();
if($sr_migrate_only){
	//sr_first: the waiting modules the page shows, migrated alone so it can reload; the rest on a later run
	$sr_first			 = array_values(array_intersect($sr_pending_imports, array_map('intval', explode(',', (string)$sr_af->get_val($_GET, 'sr_first', '')))));
	$sr_pending_imports	 = empty($sr_first) ? $sr_pending_imports : $sr_first;
	$sr_updv6->set_pending_try($sr_pending_imports);
}
//pending imports first: their slide ids are v7 already, so they must run before a v6 module fills SR7.E.v6v7ids
$sr_v6_upgrade_needed	 = array_merge($sr_pending_imports, ($sr_v6_exists === true && !$sr_migrate_only) ? $sr_updv6->slider_need_update_checks_v6() : []);
$sr_slider_update_needed = $sr_upd->slider_need_update_checks();
$sr_show_updated		 = $sr_af->get_options(['system', 'overlay'], '1.0.0');
$sr_js_modules			 = $this->get_val($SR_GLOBALS, 'modules', []);
$sr_bundled_modules		 = RevSliderFunctions::bundled_module_urls($sr_js_modules);
$sr_js_modules[]		 = 'migration';
$sr_ai_new				 = $sr_ai->get_finished_background_jobs();
$sr_ai_pending			 = ! empty( $sr_ai->get_open_events() );
$sr_addon_min_ver		 = $sr_addon->check_addon_version();
$sr_wpml_languages		 = $sr_af->get_wpml_editor_languages();

if(version_compare(RS_REVISION, $sr_show_updated, '>')) $sr_af->update_option(['system', 'overlay'], RS_REVISION);

$sr_free_core			= $sr_af->free_core_open(); //texts that sold updates as paid change only while it is open (ADR-0027)
$sr_licence_note		= $sr_af->licence_note();
//the note takes the Deactivated popup's place, dismissed or not (ADR-0027)
$sr_show_deregister		= $sr_af->_truefalse($sr_af->get_options(['system', 'deregister'], 'false')) && empty($sr_licence_note);
$sr_show_deregister_msg	= $sr_af->get_options(['system', 'deregister-msg']);

//SR7.E, the editor's environment: one array, emitted once below. A build bakes the same array as the JSON a host without
//PHP fills in (build/bake.php), so a key belongs here and nowhere else
$sr_e = [
	'php'				=> (object)$sr_af->get_basic_metas(),
	'nonce'				=> $sr_env['nonce'],
	'plugin_dir'		=> 'revslider',
	'allow_update'		=> $sr_emergency_update === true,
	'slug_path'			=> str_replace(["\n", "\r"], '', RS_PLUGIN_SLUG_PATH),
	'slug'				=> str_replace(["\n", "\r"], '', RS_PLUGIN_SLUG),
	'admin_url'			=> $sr_env['admin_url'],
	'plugin_url'		=> str_replace(["\n", "\r"], '', RS_PLUGIN_URL),
	'core_url'			=> str_replace(["\n", "\r"], '', RS_CORE_URL),
	'preview_url'		=> $sr_env['preview_url'],
	'wp_plugin_url'		=> str_replace(["\n", "\r"], '', $sr_env['wp_plugin_url']) . '/',
	'wp_upload_url'		=> str_replace(["\n", "\r"], '', $sr_upload_url) . '/',
	'revision'			=> RS_REVISION,
	'beta'				=> defined('RS_BETA') ? RS_BETA : '', //test build label, compare SR7.E.revision only
	'addon_presets'		=> $sr_env['addon_presets'],
	'starts'			=> $sr_env['starts'],
	'effects'			=> $sr_addon->get_effects_catalogue(), //Effects Library tiles, installed and not installed add-ons alike
	'effectsServer'		=> (bool)$sr_env['effects_server'],
	'metaEdit'			=> (bool)$sr_env['meta_edit'],
	'metaBugUrl'		=> $sr_env['meta_bug_url'],
	'metaMonday'		=> (bool)$sr_env['meta_monday'],
	//cache-bust token for dynamically loaded editor scripts, apart from revision (version gating): per load in debug, RS_REVISION in production
	'assetver'			=> ($sr_env['debug']) ? RS_REVISION . '.' . time() : RS_REVISION,
	//an uncompiled dev tree carries _DIST; gates in-progress editor features so they are off in every release
	'dev'				=> file_exists(RS_PLUGIN_PATH . '_DIST'),
	'rtl'				=> (bool)(method_exists($this, 'use_rtl_assets') ? $this->use_rtl_assets() : $sr_env['rtl']),
	'ajaxurl'			=> $sr_env['ajaxurl'],
	'resturl'			=> $sr_env['resturl'],
	'latest_revision'	=> $sr_latest_version,
	'update_hint'		=> $sr_env['update_hint'], //a host that does not update the plugin from here says where it is done, see SR7.system.updateCore
	'registered'		=> $sr_valid === true,
	'deregister'		=> $sr_show_deregister === true,
	'deregister_msg'	=> ($sr_show_deregister === true) ? $sr_show_deregister_msg : '',
	'freeCore'			=> $sr_free_core, //the update works without a licence
	'licenceNote'		=> empty($sr_licence_note) ? null : $sr_licence_note,
	'updated'			=> version_compare(RS_REVISION, $sr_show_updated, '>'),
	'woocommerce'		=> (bool)$sr_env['woocommerce'],
	'backend'			=> true,
	'libs'				=> ['tpgsap'],
	'css'				=> ['csslp', 'cssbtns', 'cssfilters', 'cssnav', 'cssmedia'],
	'modules'			=> $sr_js_modules,
	'resources'			=> (object)$sr_bundled_modules,
	'ai'				=> ['new' => (empty($sr_ai_new)) ? [] : $sr_ai_new, 'pending' => $sr_ai_pending === true],
	'addonsMinVer'		=> (empty($sr_addon_min_ver)) ? new stdClass() : $sr_addon_min_ver,
	'library_preload'	=> !$sr_library_preloaded,
	'migrate_only'		=> $sr_migrate_only,
	'movedUpdate'		=> !empty($sr_v6_upgrade_needed) ? $sr_updv6->moved_addons_needing_build() : [],
	'optimizer'			=> $sr_o->is_enabled() && $sr_o->verify_webp(),
];
if(!empty($sr_wpml_languages)) $sr_e['wpml'] = ['languages' => $sr_wpml_languages];

?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

<!-- GLOBAL VARIABLES -->
<script>	
	window.SrSp = window.SrSp || class SrSp extends HTMLElement {
		static get observedAttributes() {return ['h', 'w'];}

		constructor() {super();}

		connectedCallback() {this.updateDimensions();}

		attributeChangedCallback(name, oldValue, newValue) {this.updateDimensions();}

		updateDimensions() {
			const height = this.getAttribute('h');
			const width = this.getAttribute('w');
			//the 5px rhythm reads --sr-space-N from base.css, a step without one keeps its pixels
			const len = v => (v > 0 && v % 5 === 0) ? `var(--sr-space-${v / 5}, ${v}px)` : `${v}px`;

			if (width !== null) {
			this.style.display = 'inline-block';
			this.style.width = len(width);
			this.style.height = height ? len(height) : 'auto';
			} else {
			this.style.display = 'block';
			this.style.height = height ? len(height) : 'auto';
			}
		}
	}
	if (!customElements.get('sr-sp')) {
		customElements.define('sr-sp', SrSp);
	}

	window.SR7			  ??= {};
	SR7.F				  ??= {};
	SR7.D				  ??= {};
	SR7.E				  ??= {gAddons:{}};
	SR7.E.php			  ??= {};
	SR7.LIB				  ??= {};
	SR7.LIB.V6			  = <?php echo (empty($sr_v6_upgrade_needed)) ? '[]' : json_encode($sr_v6_upgrade_needed); ?>;
	{ const e = <?php echo wp_json_encode($sr_e); ?>; Object.assign(SR7.E.php, e.php); delete e.php; Object.assign(SR7.E, e); }
	<?php if($sr_env['rtl'] && method_exists($this, 'use_rtl_assets') && !$this->use_rtl_assets()){ ?>
	//"Use Editor in LTR Mode" global setting: pin the SR7 UI to LTR inside the RTL admin
	document.body.classList.add('sr--force--ltr');
	<?php } ?>
	<?php if($sr_slider_update_needed == true){ ?>
	if (window.SR7?.B?.silentUpdate) SR7.B.silentUpdate(); else document.addEventListener('tools_all_ready',SR7.B.silentUpdate);
	<?php } ?>
</script>

<?php
do_action('revslider_header_content', $sr_af);
?>

<?php
//add custom fonts that have backend set to true

foreach($sr_backend_fonts ?? [] as $sr_bf){
	if($sr_bf['type'] === 'custom' && isset($sr_bf['url']) && isset($sr_bf['backend']) && $sr_bf['backend'] === true){
		echo '<link href="'.esc_html($sr_bf['url']).'" rel="stylesheet" property="stylesheet" media="all" type="text/css" >'."\n";
	}
}
?>
