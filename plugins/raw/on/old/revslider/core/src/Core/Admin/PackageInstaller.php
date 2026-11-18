<?php
/**
 * Template Package installer — turns a manifest into pages, modules, media, menus and effects.
 *
 * Shaped deliberately like RevSliderPageEffects: core owns the framework and the phases, and anything that
 * knows something core does not registers a HANDLER for its own node type, rather than patching this class.
 *
 * THE RESOLUTION MAP is the whole trick. A manifest names nothing real: it says "the module `hero`", "the
 * page `about`", "the image `media/x.jpg`". Every phase writes what it created into $map and later phases
 * read it back, which is what lets the same package install onto a site that already has 40 sliders.
 *
 * Runs one phase per request (see step()): a package with five pages and forty images will not finish
 * inside one PHP timeout.
 *
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Core\Admin;

if(!defined('ABSPATH')) exit();

/**
 * Installs a parsed package manifest, one phase at a time.
 */
class PackageInstaller extends \ThemePunch\SR\Core\Functions {

	/**
	 * The page template a Slider Revolution page gets: the theme steps aside and the modules are the
	 * page. Exactly what create_slider_page() has always set, so a package builds the same page the
	 * "create blank page" tickbox next to a template does.
	 */
	/** The phases that make modules. What is left when a package is installed without its pages. */
	const MODULE_PHASES = ['modules', 'fixids'];

	const BLANK_TEMPLATE = 'revslider-page-template.php';


	/** transient holding the install state between phase requests */
	const STATE_PREFIX	= 'rs_pkg_install_';
	const STATE_TTL		= 3600;

	/** option holding one receipt per installed package uid */
	const RECEIPTS		= 'revslider_package_receipts';

	/** attachment meta: the sha1 of the packaged file, so a reinstall reuses it instead of duplicating */
	const ASSET_SHA1	= '_sr7_pkg_sha1';

	/** tokens the installer substitutes inside markup it cannot walk (raw nodes, block html) */
	const TOKEN_MEDIA	= '#%%SR7_MEDIA:([a-zA-Z0-9._/-]+)%%#';
	const TOKEN_MODULE	= '#%%SR7_MODULE:([a-zA-Z0-9_-]+)%%#';
	const TOKEN_PAGE	= '#%%SR7_PAGE:([a-zA-Z0-9_-]+)%%#';

	/** @var array<string,array> registered node handlers */
	private static $handlers = [];

	/** @var array<string,callable> phase-level overrides: a platform owns whole phases here */
	private static $phase_handlers = [];

	/**
	 * let a platform (or addon) own one install phase outright - the WP pages/menus/options
	 * phases register here; a phase without a handler falls back to the core phase_<name> method
	 * @since 7.2.1
	 * @param string $phase
	 * @param callable $handler callable(&$state): void
	 * @return bool
	 */
	public static function register_phase_handler($phase, $handler){
		if(!is_callable($handler)) return false;
		self::$phase_handlers[$phase] = $handler;
		return true;
	}

	/** @return callable|false @since 7.2.1 */
	public static function get_phase_handler($phase){
		return isset(self::$phase_handlers[$phase]) ? self::$phase_handlers[$phase] : false;
	}


	/**
	 * Register a handler for a manifest node type.
	 *
	 * Core registers the built-in types on first use. An addon registers under its own key — for a page
	 * effect that is 'provider:<slug>', which the providers phase looks up before falling back to the
	 * generic Page Effects path. Registering a handler for a type core already owns REPLACES it, which is
	 * how an addon takes over a node core only half understands.
	 *
	 * @param string $type
	 * @param array  $args validate|install|remap|uninstall callables, plus an optional 'label'
	 * @return bool
	 */
	public static function register_handler($type, $args){
		$type = sanitize_key(str_replace(':', '_', $type));
		if($type === '' || !is_array($args)) return false;

		self::$handlers[$type] = array_merge([
			'label'		=> $type,
			'validate'	=> null,	// callable($node, $state): true|WP_Error — dry-run, must not write
			'install'	=> null,	// callable($node, $state): array  — returns refs to merge into the map
			'uninstall'	=> null		// callable($receipt_entry): void — v1.1
		], $args);

		return true;
	}

	/** @return array|false */
	public static function get_handler($type){
		$type = sanitize_key(str_replace(':', '_', $type));

		return isset(self::$handlers[$type]) ? self::$handlers[$type] : false;
	}

	/** @return array<string,array> */
	public static function get_handlers(){ return self::$handlers; }


	/*******************
	 * DRY RUN         *
	 *******************/

	/**
	 * Report what installing this package would do, and everything that would go wrong, WITHOUT writing
	 * anything. Nobody should discover a slug collision or a silently changed front page by finding it in
	 * their live site afterwards.
	 *
	 * @param array $manifest a manifest that already came through RevSliderPackageManifest::parse()
	 * @return array
	 */
	public function dry_run($manifest, $modules_only = false){
		$report = [
			'ok'		=> true,
			'errors'	=> [],
			'warnings'	=> [],
			'creates'	=> [
				//Modules only: the pages, their pictures and their menus are never made, so they are not
				//counted or warned about either. Promising them and then not making them is worse than
				//either one on its own.
				'pages'		=> $modules_only ? 0 : count($this->get_val($manifest, 'pages', [])),
				'modules'	=> count($this->get_val($manifest, 'modules', [])),
				'media'		=> $modules_only ? 0 : count($this->get_val($manifest, 'assets', [])),
				'menus'		=> $modules_only ? 0 : count($this->get_val($manifest, 'menus', []))
			],
			//Add-On slugs this package needs that are not active yet. The INSTALLER does not fetch them —
			//the library JS already has a working path for that (SR7.B.tlibItems.installAddons →
			//SR7.B.addons.fix), and it runs before begin(). This list is what it feeds that path.
			'addons'	=> [],
			'references'=> $this->get_val($manifest, 'references', [])
		];

		$this->check_requirements($manifest, $report);
		$this->check_modules($manifest, $report);

		if(!$modules_only){
			$this->check_pages($manifest, $report);
			$this->check_menus($manifest, $report);
			$this->check_providers($manifest, $report);
			$this->check_options($manifest, $report);
		}

		$this->check_reinstall($manifest, $report);

		$report['ok'] = empty($report['errors']);

		return $report;
	}

	/** @return void */
	private function check_requirements($manifest, &$report){
		$need = $this->get_val($manifest, ['requires', 'revslider'], '');
		if($need !== '' && version_compare(RS_REVISION, $need, '<')){
			$report['errors'][] = sprintf(__('This package needs Slider Revolution %1$s — this site runs %2$s.', 'revslider'), $need, RS_REVISION);
		}

		foreach($this->get_val($manifest, ['requires', 'addons'], []) as $slug){
			//A missing Add-On is a warning, not an error: the library installs it before it calls begin(),
			//through the same path the "Install Package & Addons" button has always used.
			if($this->environment()->extension_active($slug . '/' . $slug . '.php')) continue;

			$report['addons'][]   = $slug;
			$report['warnings'][] = sprintf(__('Add-On "%s" will be installed and activated.', 'revslider'), $this->addon_label($slug));
		}

		foreach($this->get_val($manifest, ['requires', 'plugins'], []) as $p){
			$slug = $this->get_val($p, 'slug');
			$name = $this->get_val($p, 'name', $slug);
			if($this->plugin_present($slug)) continue;

			//Third-party plugins are never installed for the user — declared and detected only.
			if($this->get_val($p, 'required', false)){
				$report['errors'][] = sprintf(__('This package needs the "%s" plugin, which is not installed.', 'revslider'), $name);
			}else{
				$report['warnings'][] = sprintf(__('Some content needs the "%s" plugin, which is not installed.', 'revslider'), $name);
			}
		}
	}

	/** @return void */
	private function check_modules($manifest, &$report){
		foreach($this->get_val($manifest, 'modules', []) as $mod){
			//A module authored for the package but never bound to a catalogue row: legal in a manifest,
			//not installable. This is the check that catches a half-finished package before it ships.
			if($this->get_val($mod, 'uid', '') === ''){
				$report['errors'][] = sprintf(__('The package refers to a module ("%s") that has no template attached yet.', 'revslider'), $this->get_val($mod, 'ref'));
			}
		}
	}

	/** @return void the host's page checks ride the phase-handler registry (see RevSliderWpInstallerPhases) */
	private function check_pages($manifest, &$report){
		$h = self::get_phase_handler('check_pages');
		if($h !== false) $report = $h($manifest, $report);
	}

	/** @return void */
	private function check_menus($manifest, &$report){
		$h = self::get_phase_handler('check_menus');
		if($h !== false) $report = $h($manifest, $report);
	}

	/** @return void */
	private function check_providers($manifest, &$report){
		$types = class_exists('RevSliderPageEffects') ? \RevSliderPageEffects::get_types() : [];

		foreach($this->collect_providers($manifest) as $slug){
			if(isset($types[$slug]) || self::get_handler('provider_' . $slug) !== false) continue;

			//The addon that owns this effect is missing. The page still installs; the effect does not.
			$report['warnings'][] = sprintf(__('The "%s" effect is not available here — that part of the design will be skipped.', 'revslider'), $slug);
		}
	}

	/** @return void */
	private function check_options($manifest, &$report){
		$h = self::get_phase_handler('check_options');
		if($h !== false) $report = $h($manifest, $report);
	}

	/** @return void */
	private function check_reinstall($manifest, &$report){
		$receipt = $this->get_receipt($this->get_val($manifest, 'uid'));
		if(empty($receipt)) return;

		$report['warnings'][] = sprintf(
			__('This package is already installed (version %s). Installing again creates a second copy of its pages and modules.', 'revslider'),
			$this->get_val($receipt, 'version', '?')
		);
	}


	/*******************
	 * PHASED INSTALL  *
	 *******************/

	/**
	 * Open an install. Extracts the package zip, stores the state, and hands back the phase list the
	 * client then drives one step() at a time.
	 *
	 * @param array  $manifest parsed manifest
	 * @param string $zip_path the downloaded package zip
	 * @param bool   $modules_only install the modules and their folder, nothing else
	 * @return array|WP_Error ['install_id' => string, 'phases' => array]
	 */
	public function begin($manifest, $zip_path, $modules_only = false){
		if(!$this->environment()->user_can('edit_theme_options') || !$this->environment()->user_can('publish_pages')){
			return new \WP_Error('forbidden', __('You do not have permission to install a template package.', 'revslider'));
		}

		$dry = $this->dry_run($manifest, $modules_only);
		if(!$dry['ok']) return new \WP_Error('package_invalid', implode(' ', $dry['errors']));

		$dir = $this->extract($zip_path);
		if(is_wp_error($dir)) return $dir;

		$install_id = 'i' . substr(str_replace('-', '', wp_generate_uuid4()), 0, 16);

		$state = [
			'install_id'	=> $install_id,
			'manifest'		=> $manifest,
			'dir'			=> $dir,
			'phases'		=> $this->install_phases($manifest, $modules_only),
			'done'			=> [],
			'map'			=> ['media' => [], 'modules' => [], 'pages' => [], 'effects' => [], 'slides' => []],
			//effects found while serialising each page, keyed by page ref, installed once the posts exist
			'pending'		=> [],
			//the accumulated old→new id map from every module import, for the fixids phase
			'import_map'	=> [],
			'receipt'		=> [
				'uid'		=> $this->get_val($manifest, 'uid'),
				'title'		=> $this->get_val($manifest, 'title'),
				'version'	=> $this->get_val($manifest, 'version'),
				'date'		=> gmdate('Y-m-d H:i:s', $this->environment()->local_timestamp()),
				'posts'		=> [],
				'modules'	=> [],
				'media'		=> [],		// created by this install — candidates for removal
				'media_used'=> [],		// …plus the ones it deduped onto, which it must NOT remove
				'menus'		=> [],
				//Credits travel with the receipt, not just the install dialog. Some image licences want
				//attribution for as long as the picture is on the site, and by then the package zip is
				//long gone — this is the only place left that still knows.
				'references'=> $this->get_val($manifest, 'references', [])
			],
			'warnings'		=> $dry['warnings']
		];

		$this->save_state($state);

		return ['install_id' => $install_id, 'phases' => $state['phases'], 'warnings' => $dry['warnings']];
	}

	/**
	 * Run the next outstanding phase.
	 *
	 * @param string $install_id
	 * @return array|WP_Error ['phase' => string, 'done' => bool, 'progress' => float, 'warnings' => array]
	 */
	public function step($install_id){
		$state = $this->load_state($install_id);
		if(is_wp_error($state)) return $state;

		$phase = '';
		foreach($state['phases'] as $p){
			if(!in_array($p, $state['done'], true)){ $phase = $p; break; }
		}

		if($phase === ''){
			$this->finish($state);

			return [
				'phase'		=> '',
				'done'		=> true,
				'progress'	=> 1.0,
				'warnings'	=> $state['warnings'],
				'receipt'	=> $state['receipt'],
				//Computed now rather than stored: a receipt outlives the permalinks in it.
				'pages'		=> $this->page_links($state['receipt']),
				//What the overview needs to show the folder and its modules without being reloaded.
				'library'	=> $this->library_items($state['receipt'])
			];
		}

		$handler = self::get_phase_handler($phase);
		if($handler !== false){
			call_user_func_array($handler, [&$state]);
		}else{
			$method = 'phase_' . $phase;
			if(method_exists($this, $method)) $this->{$method}($state);
		}

		$state['done'][] = $phase;
		$this->save_state($state);

		return [
			'phase'		=> $phase,
			'done'		=> false,
			'progress'	=> count($state['done']) / max(1, count($state['phases'])),
			'warnings'	=> $state['warnings']
		];
	}

	/**
	 * Which phases this install runs.
	 *
	 * Modules only keeps the two that make a module: `modules` imports them and files them into the
	 * folder, `fixids` rewrites the slide ids their own actions point at. Everything else builds the site
	 * around them and is left out.
	 *
	 * @return array
	 */
	private function install_phases($manifest, $modules_only){
		$phases = $this->get_val($manifest, 'order', \ThemePunch\SR\Core\PackageManifest::PHASES);
		if(!$modules_only) return $phases;

		return array_values(array_intersect($phases, self::MODULE_PHASES));
	}

	/**
	 * Write the receipt, drop the state and clean up the extracted zip. The receipt is what makes a
	 * reinstall detectable and an uninstall possible; without it the package's 58 new rows are
	 * indistinguishable from anything else the user made.
	 * @return void
	 */
	private function finish(&$state){
		$receipts = $this->option_storage()->get(self::RECEIPTS, []);
		if(!is_array($receipts)) $receipts = [];

		$uid = $this->get_val($state['receipt'], 'uid');
		if($uid !== ''){
			//Keep every install, not just the last: two installs of one package made two sets of pages, and
			//an uninstall has to be able to remove either one.
			if(!isset($receipts[$uid]) || !is_array($receipts[$uid])) $receipts[$uid] = [];
			$receipts[$uid][] = $state['receipt'];
			$this->option_storage()->set(self::RECEIPTS, $receipts, false);
		}

		$this->cleanup($state['dir']);
		$this->ttl_cache()->delete(self::STATE_PREFIX . $state['install_id']);
	}


	/*******************
	 * PHASES          *
	 *******************/

	/**
	 * Sideload the package's bundled media. Deduped by sha1 so reinstalling does not leave the media
	 * library with two of every image.
	 * @return void
	 */
	//the media phase is host work (attachments, sha1 dedup, sideload) and lives on the phase-handler
	//seam - see RevSliderWpInstallerPhases::phase_media()

	/**
	 * Install each module through the ordinary template path, so a packaged module goes through exactly
	 * the same download, licence check and import as one installed by hand.
	 * @return void
	 */
	private function phase_modules(&$state){
		$modules = $this->get_val($state['manifest'], 'modules', []);
		if(empty($modules)) return; //a package can be pages only — don't pull the import stack in for nothing

		$this->need_admin_classes();
		$templates = new \ThemePunch\SR\Core\Template();

		foreach($modules as $mod){
			$uid = $this->get_val($mod, 'uid');
			$ref = $this->get_val($mod, 'ref');
			if($uid === '') continue;

			$filepath = $templates->_download_template($uid);
			if($filepath === false || (is_array($filepath) && isset($filepath['error']))){
				$state['warnings'][] = sprintf(__('The module "%s" could not be downloaded.', 'revslider'), $this->get_val($mod, 'title', $ref));
				continue;
			}

			$import = new Import();
			$result = $import->import_slider(false, $filepath, $uid);
			$templates->_delete_template($uid);

			$slider_id = intval($this->get_val($result, 'sliderID', 0));
			if($this->get_val($result, 'success') !== true || $slider_id <= 0){
				$state['warnings'][] = sprintf(__('The module "%s" could not be installed.', 'revslider'), $this->get_val($mod, 'title', $ref));
				continue;
			}

			$slider = new \ThemePunch\SR\Core\Slider();
			$slider->init_by_id($slider_id);

			//'from' rides along so a provider's remap() can turn what its data still says into what this
			//site actually calls the module.
			$state['map']['modules'][$ref] = [
				'id'	=> $slider_id,
				'alias'	=> $slider->get_alias(),
				'from'	=> $this->get_val($mod, 'from', [])
			];
			$state['receipt']['modules'][] = $slider_id;

			foreach($this->pair_slides($this->get_val($mod, ['from', 'slides'], []), $slider) as $old_slide => $new_slide){
				$state['map']['slides'][$old_slide] = $new_slide;
			}

			//Accumulate every import's own old→new map into ONE, exactly as the library JS does with
			//SR7.importMap (a deep merge, so zip_to_template/aliases/slides gather rather than overwrite).
			//The fixids phase then runs over the whole set — see phase_fixids().
			$state['import_map'] = $this->merge_map($state['import_map'], $this->get_val($result, 'map', []));
		}

		$this->file_into_folder($state);
	}

	/**
	 * Remove the folder an install created, but ONLY once nothing else is in it.
	 *
	 * A folder is shared ground: somebody may well have dragged their own modules into it since. Those
	 * are not ours to lose, so a folder that still has something in it keeps standing with the rest.
	 *
	 * @return void
	 */
	private function drop_folders($receipt){
		$folders = $this->get_val($receipt, 'folders', []);
		if(empty($folders)) return;

		$this->need_admin_classes();
		if(!class_exists('RevSliderFolder')) return;

		$ours = array_map('intval', (array)$this->get_val($receipt, 'modules', []));

		foreach($folders as $folder_id){
			$folder = new \ThemePunch\SR\Core\Folder();
			$folder->init_folder_by_id($folder_id);
			if(intval($folder->get_id()) <= 0) continue;

			$left = array_diff(array_map('intval', (array)$folder->get_children()), $ours);
			if(!empty($left)){
				//true = replace the list. set_children() only changes the object, and update_settings()
				//merges recursively, which cannot shrink a list - it would leave the removed ids in place.
				$folder->add_slider_to_folder(array_values($left), $folder_id, true);
				continue;
			}

			$folder->delete_slider();
		}
	}

	/**
	 * The folder and the modules as the library draws them.
	 *
	 * Installing ONE template hands its overview data straight back, and the library puts the item on
	 * screen from that - no reload anywhere. A package installs on the server across several requests,
	 * so the same data has to travel with the last one, or the overview keeps showing what it read when
	 * the page was opened.
	 *
	 * @return array ['folder' => data|null, 'modules' => [data, …]]
	 */
	private function library_items($receipt){
		$this->need_admin_classes();

		$out = ['folder' => null, 'modules' => []];

		foreach($this->get_val($receipt, 'modules', []) as $id){
			$slider = new \ThemePunch\SR\Core\Slider();
			$slider->init_by_id($id, false);
			if(intval($slider->get_id()) > 0) $out['modules'][] = $slider->get_overview_data();
		}

		$folders = $this->get_val($receipt, 'folders', []);
		if(!empty($folders) && class_exists('RevSliderFolder')){
			$folder = new \ThemePunch\SR\Core\Folder();
			$folder->init_folder_by_id(reset($folders));
			if(intval($folder->get_id()) > 0){
				$data = $folder->get_overview_data();
				//the overview keys everything off parent; a package folder sits at the top level
				$data['parent'] = -1;
				$out['folder'] = $data;
			}
		}

		return $out;
	}

	/**
	 * Put everything the package installed into one folder, named after the package.
	 *
	 * Installing a package from the library has always done this: the nine modules of a website do not
	 * belong loose in the overview between everything else. It happened in the library's javascript,
	 * which is why the server driven install did not inherit it.
	 *
	 * @return void
	 */
	private function file_into_folder(&$state){
		$children = $this->get_val($state['receipt'], 'modules', []);
		if(empty($children)) return;

		$this->need_admin_classes();
		if(!class_exists('RevSliderFolder')) return;

		$folder	= new \ThemePunch\SR\Core\Folder();
		$title	= $this->get_val($state['manifest'], 'title', __('Template Package', 'revslider'));
		//create_folder() hands back the folder OBJECT, or false when the row could not be written.
		$made	= $folder->create_folder($title);
		$id		= is_object($made) ? intval($made->get_id()) : 0;

		if($id <= 0){
			$state['warnings'][] = __('The modules could not be put into a folder.', 'revslider');
			return;
		}

		//false: add to what is there rather than replace it, which for a folder just made is nothing
		$folder->add_slider_to_folder($children, $id, false);

		//in the receipt, so uninstalling the package takes its folder with it
		$state['receipt']['folders'][] = $id;
	}

	/**
	 * Re-point every id that only became wrong once ALL the modules were in.
	 *
	 * A module is imported knowing only its own old→new mapping, so a button in module A that opens module B as
	 * a modal still points at B's id on the site the package was built from. Core solves this with a second pass
	 * over the ACCUMULATED map after every template has landed (in the library: SR7.B.tlibItems.fixIds() →
	 * 'slider.save.modal_ids'). This is that pass for the server-side install, deliberately the same operation
	 * as RevSliderApi::save_slider_modal_ids() against the same map shape.
	 *
	 * NOT replicated: the 'slider.save.js_css_ids' half. With the map shape the library actually sends, that
	 * handler's inner loop never runs - it reads the map as a LIST of {slider:…} entries, an older shape.
	 * Per-slider inline JS/CSS ids are already rewritten inside import_slider().
	 * @return void
	 */
	private function phase_fixids(&$state){
		$map = $state['import_map'];
		if(empty($map)) return;

		$ztt		= $this->get_val($map, ['slider', 'zip_to_template'], []);
		$aliases	= $this->get_val($map, ['slider', 'aliases'], []);
		$slides_ids	= $this->get_val($map, 'slides', []);
		$sliders	= $this->get_val($map, 'slider', []);

		if(empty($ztt)) return;

		// STEP ONE, the same call save_slider_modal_ids() makes: a button in one module that opens
		// another as a modal still names the id it had on the site the package came from.
		foreach($ztt as $new){
			$rsi = new Import();
			$rsi->init_by_id($new);
			$rsi->update_modal_ids($ztt, $slides_ids, [], $aliases);
			$this->clear_module_cache($new);
		}

		// STEP TWO, which this phase was missing entirely: the ids written into a module's own CSS and
		// javascript. Installing a template runs both - modal ids, then inline scripts - so a package
		// that ran only the first left every custom script and style rule pointing at the old module.
		foreach($ztt as $new){
			$rsi = new Import();
			$rsi->init_by_id($new);

			foreach($sliders as $old => $made){
				if($old === 'zip_to_template' || $old === 'aliases' || !is_scalar($made)) continue;

				$rsi->update_css_and_javascript_ids($old, $made, $slides_ids);
			}

			$this->clear_module_cache($new);
		}
	}

	/**
	 * Drop a module's cached object and markup after its ids were rewritten. Both api.class.php calls do
	 * this; without it the module keeps serving the markup built from the ids it had a moment ago.
	 * @return void
	 */
	private function clear_module_cache($slider_id){
		$api = class_exists('RevSliderApi') ? new \ThemePunch\SR\Core\Api() : null;
		if($api === null || !method_exists($api, 'clear_cache')) return;

		$api->clear_cache($slider_id);
	}

	/**
	 * AUTHORING slide id → the one this install just created, paired by position.
	 *
	 * A layer's element id is "<moduleHtmlId>-<slideId>-<layerIndex>", so an effect bound to a layer carries a
	 * slide id from the site the package was BUILT on. The import's own map is no help: it runs from the ZIP's
	 * internal ids, which the authoring site never saw either. Both modules come from the same template, so
	 * their slides line up in order, and position is the only thing that connects the two.
	 *
	 * Stops at the first position the installed module cannot fill: a short pairing leaves one anchor
	 * unresolved, a WRONG pairing looks like the effect works and quietly draws in the wrong place.
	 *
	 * @param array  $authored slide ids as recorded by the exporter, in order
	 * @param object $slider   the module this install created
	 * @return array [authoringSlideId => newSlideId]
	 */
	private function pair_slides($authored, $slider){
		$out = [];
		if(empty($authored) || !is_array($authored)) return $out;

		$made = [];
		foreach((array)$slider->get_slides() as $sl){
			$sl_id = intval($sl->get_id());
			if($sl_id > 0) $made[] = $sl_id;
		}

		foreach(array_values($authored) as $i => $old_slide){
			if(!isset($made[$i])) break;
			$old_slide = intval($old_slide);
			if($old_slide > 0) $out[$old_slide] = $made[$i];
		}

		return $out;
	}

	/**
	 * Deep-merge two import maps, matching _tpt.extend() in tptools.js: nested arrays gather instead of
	 * replacing, so zip_to_template ends up holding every module's mapping rather than the last one's.
	 * @return array
	 */
	private function merge_map($into, $from){
		if(!is_array($from)) return $into;
		if(!is_array($into)) $into = [];

		foreach($from as $k => $v){
			if(is_array($v) && isset($into[$k]) && is_array($into[$k])) $into[$k] = $this->merge_map($into[$k], $v);
			else $into[$k] = $v;
		}

		return $into;
	}


	/**
	 * Install the effects collected while the pages were serialised: mint each an effectId, let its type
	 * rewrite its own references against the map, write the meta, then stamp the id back into the page's
	 * markup where serialize_nodes() left a placeholder.
	 *
	 * Runs last of the content phases because an effect needs its host post to exist AND needs the module
	 * map to be complete — a ChalkLine anchor can bind to a selector inside a module.
	 * @return void
	 */
	//the providers phase rewrites the created posts' content and lives on the phase-handler seam -
	//see RevSliderWpInstallerPhases::phase_providers()





	/*******************
	 * BLOCK TREE      *
	 *******************/









	/*******************
	 * HELPERS         *
	 *******************/

	//find_asset_by_sha1() and sideload() travelled with the media phase to RevSliderWpInstallerPhases


	/**
	 * The template and import classes are required behind is_admin() in the bootstrap, while this class is
	 * declared outside it (api.class.php reaches for it from either side). An install is always an admin
	 * request, so they are normally already there — this just refuses to depend on that.
	 * @return void
	 */
	private function need_admin_classes(){
		class_exists('ThemePunch\SR\Core\Template');		//autoload warm-up: an install is not necessarily an is_admin request
		class_exists('ThemePunch\SR\Core\Admin\Import');
	}

	/** @return bool */
	private function plugin_present($slug){
		foreach(array_keys($this->lifecycle()->installed_extensions()) as $file){
			if(dirname($file) === $slug) return true;
		}

		return false;
	}

	/** @return string */
	private function addon_label($slug){
		return ucwords(str_replace(['revslider-', '-addon', '-'], ['', '', ' '], $slug));
	}

	/** @return array every provider slug used anywhere in the manifest */
	private function collect_providers($manifest){
		$found = [];

		$walk = function($nodes) use (&$walk, &$found){
			foreach($nodes as $n){
				if($this->get_val($n, 'type') === 'provider') $found[$this->get_val($n, 'provider')] = true;
				foreach($this->get_val($n, 'providers', []) as $p) $found[$this->get_val($p, 'provider')] = true;
				if(!empty($n['inner'])) $walk($n['inner']);
			}
		};

		foreach($this->get_val($manifest, 'pages', []) as $page) $walk($this->get_val($page, 'blocks', []));

		return array_keys($found);
	}

	/*******************
	 * UNINSTALL       *
	 *******************/

	/**
	 * Every install this site has a receipt for, newest last, with the index uninstall() takes.
	 * @return array
	 */
	public function get_installs($uid = ''){
		$receipts = $this->option_storage()->get(self::RECEIPTS, []);
		if(!is_array($receipts)) return [];

		$out = [];
		foreach($receipts as $key => $list){
			if($uid !== '' && $key !== $uid) continue;
			if(!is_array($list)) continue;

			foreach($list as $i => $receipt){
				if(!is_array($receipt)) continue;
				$out[] = array_merge($receipt, ['uid' => $key, 'index' => $i]);
			}
		}

		return $out;
	}

	/**
	 * What removing an install would take away. Same idea as the dry-run, pointing the other way: a
	 * package's pages are somebody's site by the time they remove it, and they should see the list first.
	 * @return array|WP_Error
	 */
	public function uninstall_report($uid, $index = null){
		$receipt = $this->find_receipt($uid, $index);
		if(is_wp_error($receipt)) return $receipt;

		$h = self::get_phase_handler('host_page_titles');
		$pages = ($h !== false) ? $h($this->get_val($receipt, 'posts', [])) : [];

		$modules = 0;
		foreach($this->get_val($receipt, 'modules', []) as $id){
			//$show_error = false: a module the user has since deleted by hand is not an error here, it is
			//simply one less thing to remove. With the default, init_by_id() THROWS on a missing slider and
			//the guard below never runs — reporting on a part-dismantled package would take the page down.
			$slider = new \ThemePunch\SR\Core\Slider();
			$slider->init_by_id($id, false);
			if(intval($slider->get_id()) > 0) $modules++;
		}

		return [
			'uid'		=> $this->get_val($receipt, 'uid', $uid),
			'title'		=> $this->get_val($receipt, 'title'),
			'version'	=> $this->get_val($receipt, 'version'),
			'date'		=> $this->get_val($receipt, 'date'),
			'pages'		=> $pages,
			'modules'	=> $modules,
			'media'		=> count($this->shared_safe_media($receipt, $uid, $index)),
			'menus'		=> count($this->get_val($receipt, 'menus', [])),
			//Pages go to the trash, not the shredder: by the time a package is removed its pages may have
			//been edited for months, and "undo" has to mean something.
			'pages_trashed' => true
		];
	}

	/**
	 * Remove one install.
	 *
	 * Order matters at the top: the site options go back BEFORE the front page is trashed, or WordPress
	 * is left pointing at a post in the bin.
	 *
	 * @param string   $uid
	 * @param int|null $index which install (see get_installs()); the most recent by default
	 * @return array|WP_Error
	 */
	public function uninstall($uid, $index = null){
		if(!$this->environment()->user_can('delete_pages') || !$this->environment()->user_can('edit_theme_options')){
			return new \WP_Error('forbidden', __('You do not have permission to remove a template package.', 'revslider'));
		}

		$receipt = $this->find_receipt($uid, $index);
		if(is_wp_error($receipt)) return $receipt;

		$removed = ['pages' => 0, 'modules' => 0, 'media' => 0, 'menus' => 0];

		//Only files this install actually added, and only where no OTHER install still points at them —
		//installing a package twice dedupes the second one onto the first one's attachments.
		$safe = $this->shared_safe_media($receipt, $uid, $index);

		//the host removes its own content (options back, menus, pages to the trash, safe attachments)
		$h = self::get_phase_handler('uninstall_host_content');
		if($h !== false){
			$counts = $h($this->get_val($receipt, 'prev_options', []), $this->get_val($receipt, 'menus', []), $this->get_val($receipt, 'posts', []), $safe);
			$removed = array_merge($removed, (array)$counts);
		}

		//Whatever is spared because something else still uses it has just lost its owner. Hand it to an
		//install that is actually using it, or the file is orphaned for good: the installs still showing
		//it deduped onto it and never listed it as theirs to remove.
		$this->hand_over_media(array_diff(array_map('intval', (array)$this->get_val($receipt, 'media', [])), $safe), $uid, $this->get_val($receipt, 'index', $index));

		foreach($this->get_val($receipt, 'modules', []) as $slider_id){
			//Same reason as in uninstall_report(): removing a package whose modules a user has already
			//deleted themselves must finish the job, not die partway through it.
			$slider = new \ThemePunch\SR\Core\Slider();
			$slider->init_by_id($slider_id, false);
			if(intval($slider->get_id()) > 0){ $slider->delete_slider(); $removed['modules']++; }
		}

		$this->drop_folders($receipt);

		foreach(self::$handlers as $handler){
			if(is_callable($handler['uninstall'])) call_user_func($handler['uninstall'], $receipt);
		}

		$this->forget_receipt($uid, $this->get_val($receipt, 'index', $index));

		return $removed;
	}

	/**
	 * The attachments this install created that nothing else is still relying on.
	 *
	 * Installing a package twice does NOT copy its images: the second install finds the first one's by
	 * sha1 and points at them. So "did I create it?" is not enough to decide whether removing it is safe
	 * — the question is whether any OTHER install is still using it, which is why every install records
	 * what it uses as well as what it made.
	 * @return array
	 */
	private function shared_safe_media($receipt, $uid, $index){
		$mine = array_map('intval', (array)$this->get_val($receipt, 'media', []));
		if(empty($mine)) return [];

		$my_index = intval($this->get_val($receipt, 'index', $index));
		$in_use   = [];

		foreach($this->get_installs() as $other){
			if($this->get_val($other, 'uid') === $uid && intval($this->get_val($other, 'index')) === $my_index) continue;

			//'media_used' is the honest answer. Receipts written before it existed only listed what they
			//created, which is still better than assuming nothing else wants the file.
			$used = $this->get_val($other, 'media_used', $this->get_val($other, 'media', []));
			$in_use = array_merge($in_use, array_map('intval', (array)$used));
		}

		$safe = [];
		foreach(array_unique($mine) as $id){
			if(!in_array($id, $in_use, true)) $safe[] = $id;
		}

		return $safe;
	}

	/**
	 * Pass ownership of shared attachments to an install that still uses them, so the LAST install
	 * standing is the one that finally cleans them up.
	 *
	 * @param array  $ids       attachments this install created but may not delete
	 * @param string $uid       the package being removed
	 * @param int    $my_index  the install being removed
	 * @return void
	 */
	private function hand_over_media($ids, $uid, $my_index){
		$ids = array_values(array_filter(array_map('intval', (array)$ids)));
		if(empty($ids)) return;

		$receipts = $this->option_storage()->get(self::RECEIPTS, []);
		if(!is_array($receipts)) return;

		$changed = false;

		foreach($ids as $id){
			foreach($receipts as $key => $list){
				if(!is_array($list)) continue;

				foreach($list as $i => $other){
					if($key === $uid && intval($i) === intval($my_index)) continue;
					if(!is_array($other)) continue;

					$used = array_map('intval', (array)$this->get_val($other, 'media_used', []));
					if(!in_array($id, $used, true)) continue;

					$owns = array_map('intval', (array)$this->get_val($other, 'media', []));
					if(!in_array($id, $owns, true)){
						$receipts[$key][$i]['media'][] = $id;
						$changed = true;
					}

					continue 3; //one new owner is enough
				}
			}
		}

		if($changed) $this->option_storage()->set(self::RECEIPTS, $receipts, false);
	}

	/** @return array|WP_Error */
	private function find_receipt($uid, $index){
		$installs = $this->get_installs($uid);
		if(empty($installs)) return new \WP_Error('no_receipt', __('This package is not recorded as installed.', 'revslider'));

		if($index === null) return $installs[count($installs) - 1];

		foreach($installs as $install){
			if(intval($this->get_val($install, 'index')) === intval($index)) return $install;
		}

		return new \WP_Error('no_receipt', __('That installation could not be found.', 'revslider'));
	}

	/** @return void */
	private function forget_receipt($uid, $index){
		$receipts = $this->option_storage()->get(self::RECEIPTS, []);
		if(!is_array($receipts) || !isset($receipts[$uid]) || !is_array($receipts[$uid])) return;

		unset($receipts[$uid][$index]);

		//Reindexing would renumber every OTHER install of this package, and the numbers are what the
		//uninstall UI just handed the user.
		if(empty(array_filter($receipts[$uid]))) unset($receipts[$uid]);

		$this->option_storage()->set(self::RECEIPTS, $receipts, false);
	}

	/**
	 * The pages an install created, with the links the finished dialog offers. Built from the receipt at
	 * the moment it is asked for — permalinks change, and a receipt is kept for the life of the install.
	 * @return array
	 */
	private function page_links($receipt){
		$h = self::get_phase_handler('host_page_links');
		return ($h !== false) ? $h($this->get_val($receipt, 'posts', [])) : [];
	}

	/** @return array */
	public function get_receipt($uid){
		$receipts = $this->option_storage()->get(self::RECEIPTS, []);
		if(!is_array($receipts) || !isset($receipts[$uid]) || !is_array($receipts[$uid])) return [];

		$list = $receipts[$uid];

		return empty($list) ? [] : end($list);
	}

	/**
	 * Read and parse just the manifest out of a package zip, without unpacking the media alongside it.
	 * The dry-run needs the manifest and nothing else, and a package's assets can run to tens of MB —
	 * unpacking them to answer "what would this do?" would make the preview cost as much as the install.
	 *
	 * @param string $zip_path
	 * @param array  $errors   filled with the manifest's own parse errors
	 * @return array|WP_Error
	 */
	public function read_manifest($zip_path, &$errors = []){
		$errors = [];

		if(!file_exists($zip_path)) return new \WP_Error('missing', __('Package file not found', 'revslider'));
		if(!class_exists('ZipArchive')) return new \WP_Error('no_zip', __('Your server cannot read zip files.', 'revslider'));

		$zip = new \ZipArchive();
		if($zip->open($zip_path) !== true) return new \WP_Error('bad_zip', __('This package could not be opened.', 'revslider'));

		$json = $zip->getFromName(\ThemePunch\SR\Core\PackageManifest::FILE);
		$zip->close();

		if($json === false) return new \WP_Error('no_manifest', __('This package is missing its manifest.', 'revslider'));

		$manifest = \ThemePunch\SR\Core\PackageManifest::parse($json, $errors);
		if($manifest === false) return new \WP_Error('package_invalid', implode(' ', $errors));

		return $manifest;
	}

	/**
	 * Unpack the package zip into its own temp folder.
	 * @return string|WP_Error the folder, with a trailing slash
	 */
	private function extract($zip_path){
		if(!file_exists($zip_path)) return new \WP_Error('missing', __('Package file not found', 'revslider'));

		$dir = trailingslashit(get_temp_dir()) . 'rs-package-' . substr(md5($zip_path . microtime()), 0, 12) . '/';
		if(!wp_mkdir_p($dir)) return new \WP_Error('mkdir', __('Could not create a temporary folder for the package', 'revslider'));

		$result = $this->filesystem()->extract_zip($zip_path, $dir);
		if(is_wp_error($result)){
			$this->cleanup($dir);
			return $result;
		}

		if(!file_exists($dir . \ThemePunch\SR\Core\PackageManifest::FILE)){
			$this->cleanup($dir);
			return new \WP_Error('no_manifest', __('This package is missing its manifest.', 'revslider'));
		}

		return $dir;
	}

	/** @return void */
	private function cleanup($dir){
		if($dir === '' || strpos($dir, 'rs-package-') === false) return; //never recurse over a path we did not make

		$this->filesystem()->delete($dir, true);
	}

	/** @return void */
	private function save_state($state){
		$this->ttl_cache()->set(self::STATE_PREFIX . $state['install_id'], $state, self::STATE_TTL);
	}

	/** @return array|WP_Error */
	private function load_state($install_id){
		$install_id = sanitize_key($install_id);
		$state = $this->ttl_cache()->get(self::STATE_PREFIX . $install_id);

		if(!is_array($state)) return new \WP_Error('no_state', __('This installation timed out. Please start it again.', 'revslider'));

		return $state;
	}
}
