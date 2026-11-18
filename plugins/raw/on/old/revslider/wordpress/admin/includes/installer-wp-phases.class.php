<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * The WordPress phases of a template-package install: pages, menus and the reading
 * settings, plus the Gutenberg serializers and the menu-item builder they lean on.
 * Registered on the installer's phase-handler seam; the phase engine, media and module
 * phases stay core. A host without pages installs packages modules-only, which the
 * engine already knows as a mode.
 */
class RevSliderWpInstallerPhases extends RevSliderFunctions {

	/** @var self|null built on first phase run - constructing at bootstrap is too early for the port chain */
	private static $inst = null;

	/** @return self */
	private static function instance(){
		if(self::$inst === null) self::$inst = new self();
		return self::$inst;
	}

	/** @return void wires the WP phases, dry-run checks and content primitives onto the installer seam, lazily */
	public static function register(){
		RevSliderPackageInstaller::register_phase_handler('pages',   ['RevSliderWpInstallerPhases', '_phase_pages']);
		RevSliderPackageInstaller::register_phase_handler('menus',   ['RevSliderWpInstallerPhases', '_phase_menus']);
		RevSliderPackageInstaller::register_phase_handler('options', ['RevSliderWpInstallerPhases', '_phase_options']);
		RevSliderPackageInstaller::register_phase_handler('media',     ['RevSliderWpInstallerPhases', '_phase_media']);
		RevSliderPackageInstaller::register_phase_handler('providers', ['RevSliderWpInstallerPhases', '_phase_providers']);
		//the same registry carries the non-phase host work: dry-run checks and the uninstall/report primitives
		RevSliderPackageInstaller::register_phase_handler('check_pages',   ['RevSliderWpInstallerPhases', '_check_pages']);
		RevSliderPackageInstaller::register_phase_handler('check_menus',   ['RevSliderWpInstallerPhases', '_check_menus']);
		RevSliderPackageInstaller::register_phase_handler('check_options', ['RevSliderWpInstallerPhases', '_check_options']);
		RevSliderPackageInstaller::register_phase_handler('uninstall_host_content', ['RevSliderWpInstallerPhases', '_uninstall_host_content']);
		RevSliderPackageInstaller::register_phase_handler('host_page_titles', ['RevSliderWpInstallerPhases', '_host_page_titles']);
		RevSliderPackageInstaller::register_phase_handler('host_page_links',  ['RevSliderWpInstallerPhases', '_host_page_links']);
	}

	/** @return void */
	public static function _phase_pages(&$state){ self::instance()->phase_pages($state); }
	/** @return void */
	public static function _phase_menus(&$state){ self::instance()->phase_menus($state); }
	/** @return void */
	public static function _phase_options(&$state){ self::instance()->phase_options($state); }
	/** @return void */
	public static function _phase_media(&$state){ self::instance()->phase_media($state); }
	/** @return void */
	public static function _phase_providers(&$state){ self::instance()->phase_providers($state); }
	/** @return array the report with this host's page findings added */
	public static function _check_pages($manifest, $report){ return self::instance()->check_pages($manifest, $report); }
	/** @return array */
	public static function _check_menus($manifest, $report){ return self::instance()->check_menus($manifest, $report); }
	/** @return array */
	public static function _check_options($manifest, $report){ return self::instance()->check_options($manifest, $report); }
	/** @return array removal counts for pages/menus/media */
	public static function _uninstall_host_content($prev, $menus, $posts, $safe_media){ return self::instance()->uninstall_host_content($prev, $menus, $posts, $safe_media); }
	/** @return array [['id','title'], ...] for the ids that still exist */
	public static function _host_page_titles($ids){ return self::instance()->host_page_titles($ids); }
	/** @return array [['id','title','view','edit'], ...] */
	public static function _host_page_links($ids){ return self::instance()->host_page_links($ids); }

	/**
	 * Create each page, with its block tree serialised and every ref resolved.
	 * @return void
	 */
	public function phase_pages(&$state){
		$pages = $this->get_val($state['manifest'], 'pages', []);

		//PASS 1 — every page gets its id before any content is built. A home page that links to the about
		//page (%%SR7_PAGE:about%%) is the ordinary case, and serialising in list order would resolve that
		//token against a page that does not exist yet and quietly produce an empty href.
		foreach($pages as $page){
			$post_id = wp_insert_post([
				'post_title'	=> $this->get_val($page, 'title'),
				'post_name'		=> $this->get_val($page, 'slug'),
				'post_content'	=> '',
				'post_type'		=> $this->get_val($page, 'post_type', 'page'),
				'post_status'	=> $this->get_val($page, 'status', 'draft')
			], true);

			if(is_wp_error($post_id)){
				$state['warnings'][] = sprintf(__('The page "%s" could not be created.', 'revslider'), $this->get_val($page, 'title'));
				continue;
			}

			$state['map']['pages'][$this->get_val($page, 'ref')] = $post_id;
			$state['receipt']['posts'][] = $post_id;
		}

		//PASS 2 — content, now that every page ref resolves.
		foreach($pages as $page){
			$ref		= $this->get_val($page, 'ref');
			$post_id	= intval($this->get_val($state['map']['pages'], $ref, 0));
			if($post_id <= 0) continue;

			//Providers are collected while serialising (they need the block they hang on) and installed in
			//the providers phase, which is the only point where every id they could reference exists.
			$pending = [];
			$content = $this->serialize_nodes($this->get_val($page, 'blocks', []), $state, $pending);

			wp_update_post(['ID' => $post_id, 'post_content' => $content]);
			$state['pending'][$ref] = $pending;

			// A package that names no template gets ours. The page exists to carry the modules, and the
			// tickbox beside a template in the library has always produced exactly this - a package should
			// not arrive looking different from the same modules installed one at a time.
			$tpl = $this->get_val($page, 'template');
			if($tpl === RevSliderPageTemplate::LEGACY_KEY) $tpl = RevSliderPackageInstaller::BLANK_TEMPLATE; //packages exported before 7.2.1
			if($tpl === '' || !$this->template_allowed($tpl)) $tpl = RevSliderPackageInstaller::BLANK_TEMPLATE;

			update_post_meta($post_id, '_wp_page_template', $tpl);

			// The block editor's Slider Revolution panel reads this, not the template. Setting only the
			// template leaves the toggle saying "off" on a page that plainly uses it.
			if($tpl === RevSliderPackageInstaller::BLANK_TEMPLATE) update_post_meta($post_id, 'rs_blank_template', 'on');

			foreach($this->get_val($page, 'meta', []) as $k => $v){
				if($k === '_wp_page_template' || $k === '_thumbnail_id') continue; //handled explicitly
				update_post_meta($post_id, $k, $v);
			}

			$thumb = $this->get_val($page, 'thumbnail');
			if($thumb !== '' && isset($state['map']['media'][$thumb])) set_post_thumbnail($post_id, $state['map']['media'][$thumb]);
		}
	}

	/**
	 * Create the package's menus and hang them in the theme's positions.
	 * @return void
	 */
	public function phase_menus(&$state){
		$locations = get_theme_mod('nav_menu_locations', []);
		if(!is_array($locations)) $locations = [];

		$registered = get_registered_nav_menus();
		$touched	= false;

		foreach($this->get_val($state['manifest'], 'menus', []) as $menu){
			$name = $this->get_val($menu, 'name');

			//A menu of this name may already exist — from a previous install, or the user's own. Make our
			//own rather than appending package items into a menu somebody else owns.
			$menu_name = $name;
			$i = 2;
			while(wp_get_nav_menu_object($menu_name)) $menu_name = $name . ' ' . $i++;

			$menu_id = wp_create_nav_menu($menu_name);
			if(is_wp_error($menu_id)){
				$state['warnings'][] = sprintf(__('The menu "%s" could not be created.', 'revslider'), $name);
				continue;
			}

			$state['receipt']['menus'][] = $menu_id;
			$this->add_menu_items($menu_id, $this->get_val($menu, 'items', []), 0, $state);

			$loc = $this->get_val($menu, 'location');
			if($loc !== '' && isset($registered[$loc])){
				$locations[$loc] = $menu_id;
				$touched = true;
			}
		}

		if($touched) set_theme_mod('nav_menu_locations', $locations);
	}

	/** @return void */
	public function phase_options(&$state){
		$options = $this->get_val($state['manifest'], 'options', []);
		if(empty($options)) return;

		$front = $this->get_val($options, 'front_page');
		$posts = $this->get_val($options, 'posts_page');

		if($front !== ''){
			$id = intval($this->get_val($state['map']['pages'], $front, 0));
			if($id > 0){
				//Remember what we replaced, so an uninstall can hand the site its own front page back.
				$state['receipt']['prev_options'] = [
					'show_on_front'	=> get_option('show_on_front'),
					'page_on_front'	=> get_option('page_on_front')
				];
				update_option('page_on_front', $id);
				update_option('show_on_front', 'page');
			}
		}

		if($posts !== ''){
			$id = intval($this->get_val($state['map']['pages'], $posts, 0));
			if($id > 0) update_option('page_for_posts', $id);
		}
	}

	/** @return void */
	public function add_menu_items($menu_id, $items, $parent_id, &$state){
		foreach($items as $item){
			$page_ref	= $this->get_val($item, 'page');
			$post_id	= intval($this->get_val($state['map']['pages'], $page_ref, 0));
			$url		= $this->get_val($item, 'url');

			if($page_ref !== '' && $post_id <= 0) continue; //its page failed to install; a dead link is worse than a gap

			$args = [
				'menu-item-title'	=> $this->get_val($item, 'title'),
				'menu-item-status'	=> 'publish',
				'menu-item-parent-id' => $parent_id
			];

			if($post_id > 0){
				$args['menu-item-type']		= 'post_type';
				$args['menu-item-object']	= get_post_type($post_id);
				$args['menu-item-object-id']= $post_id;
			}else{
				$args['menu-item-type']	= 'custom';
				$args['menu-item-url']	= $url;
			}

			$item_id = wp_update_nav_menu_item($menu_id, 0, $args);
			if(is_wp_error($item_id)) continue;

			$children = $this->get_val($item, 'children', []);
			if(!empty($children)) $this->add_menu_items($menu_id, $children, $item_id, $state);
		}
	}

	/**
	 * Turn manifest nodes into Gutenberg markup, resolving every ref as it goes.
	 *
	 * Effects are NOT installed here — the post does not exist yet. Each one leaves a
	 * %%SR7_EFFECT:<ref>%% placeholder in the markup and is appended to $pending, which phase_providers()
	 * picks up once the page has an id.
	 *
	 * @param array $nodes
	 * @param array $state
	 * @param array $pending  collected provider nodes (by reference)
	 * @return string
	 */
	public function serialize_nodes($nodes, &$state, &$pending){
		$out = '';

		foreach($nodes as $node){
			$type = $this->get_val($node, 'type');

			switch($type){
				case 'raw':
					$out .= $this->substitute($this->get_val($node, 'html'), $state) . "\n";
					break;

				case 'module':
					$out .= $this->serialize_module($node, $state);
					break;

				case 'provider':
					$out .= $this->serialize_provider($node, $state, $pending);
					break;

				case 'block':
				case 'group':
					$out .= $this->serialize_block_node($node, $state, $pending);
					break;
			}
		}

		return $out;
	}

	/**
	 * A page template a package may ask for: one of the templates this plugin ships, or one the active
	 * theme actually offers. Anything else would be a path of the package's choosing.
	 * @return bool
	 */
	public function template_allowed($template){
		// OUR OWN first: the pre-7.2.1 key starts with ../ and the traversal guard below would throw it out
		if(in_array($template, [RevSliderPackageInstaller::BLANK_TEMPLATE, RevSliderPageTemplate::LEGACY_KEY], true)) return true;

		if(strpos($template, '..') !== false) return false;

		$theme = wp_get_theme();
		$page_templates = $theme->get_page_templates();

		return isset($page_templates[$template]);
	}

	/**
	 * An ordinary block, plus any effects riding on it. A Scroll Animation on a core/group is stored as an
	 * sr7Anim attribute on that group — the attribute name comes from what the TYPE claims, never from
	 * the manifest, so a package cannot write an arbitrary attribute onto a block.
	 * @return string
	 */
	private function serialize_block_node($node, &$state, &$pending){
		$attrs = $this->get_val($node, 'attrs', []);

		foreach($this->get_val($node, 'providers', []) as $p){
			$slug = $this->get_val($p, 'provider');
			$attr = class_exists('RevSliderPageEffects') ? RevSliderPageEffects::claimed_attr($slug) : '';
			if($attr === '') continue;

			$pending[] = $p;

			$own = $this->get_val($p, 'attrs', []);
			if(class_exists('RevSliderPageEffects')) $own = RevSliderPageEffects::remap_data($slug, $own, $state['map']);

			$own['enabled']  = true;
			$own['effectId'] = '%%SR7_EFFECT:' . $this->get_val($p, 'ref') . '%%';
			$attrs[$attr]    = $own;
		}

		$inner_blocks	= [];
		$inner_markup	= '';
		$children		= $this->get_val($node, 'inner', []);

		if(!empty($children)) $inner_markup = $this->serialize_nodes($children, $state, $pending);

		$open	= $this->substitute($this->get_val($node, 'html_open'), $state);
		$close	= $this->substitute($this->get_val($node, 'html_close'), $state);
		$html	= $this->substitute($this->get_val($node, 'html'), $state);

		//A container splits its markup around its children; a leaf just carries its own.
		if($inner_markup !== '' || $open !== '' || $close !== ''){
			$content = $open . "\n" . $inner_markup . $close;
		}else{
			$content = $html;
		}

		return $this->serialize_one([
			'blockName'		=> $this->get_val($node, 'name'),
			'attrs'			=> $attrs,
			'innerBlocks'	=> $inner_blocks,
			'innerHTML'		=> $content,
			'innerContent'	=> [$content]
		]) . "\n";
	}

	/**
	 * A module placement, the same markup as a page made with "create blank page" (RevSliderWpTransport::module_block).
	 * @return string
	 */
	private function serialize_module($node, &$state){
		$module	= $this->get_val($state['map']['modules'], $this->get_val($node, 'ref'), false);

		//The module failed to install. Emitting the block anyway would leave a shortcode pointing at
		//nothing, which renders as raw text on the front end.
		if($module === false) return '';

		$sc = ($this->get_val($node, 'usage') === 'modal') ? ['usage' => 'modal'] : [];
		return RevSliderWpTransport::module_block(intval($this->get_val($module, 'id')), $sc, (array)$this->get_val($node, 'attrs', []));
	}

	/**
	 * Serialise one block. Uses WordPress' own serializer wherever it exists: block attributes have
	 * escaping rules of their own ("--" and "<" inside the JSON break the comment delimiter), and
	 * hand-rolling json_encode here gets them wrong.
	 * @return string
	 */
	private function serialize_one($block){
		if(function_exists('serialize_block')) return serialize_block($block);

		$attrs = empty($block['attrs']) ? '' : ' ' . wp_json_encode($block['attrs']);
		if($block['innerHTML'] === '') return '<!-- wp:' . $block['blockName'] . $attrs . ' /-->';

		return '<!-- wp:' . $block['blockName'] . $attrs . ' -->' . $block['innerHTML'] . '<!-- /wp:' . $block['blockName'] . ' -->';
	}

	/**
	 * A standalone effect block (a ChalkLine). Its effectId is a placeholder until the page exists.
	 * @return string
	 */
	private function serialize_provider($node, &$state, &$pending){
		$slug = $this->get_val($node, 'provider');
		$ref  = $this->get_val($node, 'ref');

		$types = class_exists('RevSliderPageEffects') ? RevSliderPageEffects::get_types() : [];
		$block = isset($types[$slug]) ? $this->get_val($types[$slug], 'block') : '';
		if($block === '') return ''; //the addon that owns it is not here; the dry-run said so

		$pending[] = $node;

		$attrs = $this->get_val($node, 'attrs', []);
		//Attribute-backed types keep their whole config here, so it has to be remapped too — the meta path
		//in install_effect() never sees it.
		if(!empty($attrs) && class_exists('RevSliderPageEffects')){
			$attrs = RevSliderPageEffects::remap_data($slug, $attrs, $state['map']);
		}
		$attrs['effectId'] = '%%SR7_EFFECT:' . $ref . '%%';

		return $this->serialize_one([
			'blockName'		=> $block,
			'attrs'			=> $attrs,
			'innerBlocks'	=> [],
			'innerHTML'		=> '',
			'innerContent'	=> []
		]) . "\n";
	}

	/**
	 * Replace the tokens the exporter left inside markup the installer cannot walk into.
	 * @return string
	 */
	private function substitute($html, &$state){
		if($html === '' || strpos($html, '%%SR7_') === false) return $html;

		$map = $state['map'];

		$html = preg_replace_callback(RevSliderPackageInstaller::TOKEN_MEDIA, function($m) use ($map){
			$id = isset($map['media'][$m[1]]) ? $map['media'][$m[1]] : 0;
			return $id ? wp_get_attachment_url($id) : '';
		}, $html);

		$html = preg_replace_callback(RevSliderPackageInstaller::TOKEN_MODULE, function($m) use ($map){
			return isset($map['modules'][$m[1]]) ? $map['modules'][$m[1]]['alias'] : '';
		}, $html);

		$html = preg_replace_callback(RevSliderPackageInstaller::TOKEN_PAGE, function($m) use ($map){
			$id = isset($map['pages'][$m[1]]) ? $map['pages'][$m[1]] : 0;
			return $id ? get_permalink($id) : '';
		}, $html);

		return $html;
	}

	/**
	 * Every packaged file lands in the media library exactly once, deduped by sha1: reinstalling and
	 * shared assets point at the existing attachment instead of filling the library with two of every image.
	 * @return void
	 */
	public function phase_media(&$state){
		foreach($this->get_val($state['manifest'], 'assets', []) as $path => $meta){
			$existing = $this->find_asset_by_sha1($this->get_val($meta, 'sha1'));
			if($existing > 0){
				$state['map']['media'][$path]	= $existing;
				//RELIED ON but not created by this install. Recording it is what stops a later uninstall of
				//whichever install DID create it from deleting an image this one is still showing.
				$state['receipt']['media_used'][] = $existing;
				continue;
			}

			$id = $this->sideload($state['dir'] . RevSliderPackageManifest::ASSET_DIR . $path, $meta);
			if(is_wp_error($id)){
				$state['warnings'][] = sprintf(__('Could not add the image "%s" to your media library.', 'revslider'), $path);
				continue;
			}

			$state['map']['media'][$path]		= $id;
			$state['receipt']['media'][]		= $id;	// created here → this install may remove it
			$state['receipt']['media_used'][]	= $id;	// …and uses it
		}
	}

	/** @return int attachment id, 0 when this package file is not in the library yet */
	private function find_asset_by_sha1($sha1){
		if($sha1 === '') return 0;

		$found = get_posts([
			'post_type'		=> 'attachment',
			'post_status'	=> 'inherit',
			'numberposts'	=> 1,
			'fields'		=> 'ids',
			'meta_key'		=> RevSliderPackageInstaller::ASSET_SHA1,
			'meta_value'	=> $sha1
		]);

		return empty($found) ? 0 : intval($found[0]);
	}

	/**
	 * Copy one packaged file into the media library. Goes through wp_check_filetype_and_ext against the
	 * site's allowed types, so a package cannot drop a .php into uploads by naming it .jpg.
	 * @return int|WP_Error
	 */
	private function sideload($file, $meta){
		if(!file_exists($file)) return new WP_Error('missing', __('File missing from package', 'revslider'));

		$name = basename($file);
		$type = wp_check_filetype_and_ext($file, $name);
		if(empty($type['type']) || empty($type['ext'])) return new WP_Error('filetype', __('File type not allowed', 'revslider'));

		$upload = wp_upload_bits($name, null, file_get_contents($file));
		if(!empty($upload['error'])) return new WP_Error('upload', $upload['error']);

		$id = wp_insert_attachment([
			'post_mime_type'=> $type['type'],
			'post_title'	=> sanitize_file_name(pathinfo($name, PATHINFO_FILENAME)),
			'post_content'	=> '',
			'post_status'	=> 'inherit'
		], $upload['file'], 0, true);

		if(is_wp_error($id)) return $id;

		require_once(ABSPATH . 'wp-admin/includes/image.php');
		wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $upload['file']));

		$alt = $this->get_val($meta, 'alt');
		if($alt !== '') update_post_meta($id, '_wp_attachment_image_alt', $alt);

		$sha1 = $this->get_val($meta, 'sha1');
		if($sha1 !== '') update_post_meta($id, RevSliderPackageInstaller::ASSET_SHA1, $sha1);

		return $id;
	}

	/**
	 * Resolve the %%SR7_EFFECT:...%% placeholders the pages phase left in the created posts.
	 * @return void
	 */
	public function phase_providers(&$state){
		if(empty($state['pending'])) return;

		foreach($state['pending'] as $page_ref => $providers){
			$post_id = intval($this->get_val($state['map']['pages'], $page_ref, 0));
			if($post_id <= 0 || empty($providers)) continue;

			$post = get_post($post_id);
			if(!$post) continue;

			$content = $post->post_content;
			$changed = false;

			foreach($providers as $p){
				$slug	= $this->get_val($p, 'provider');
				$ref	= $this->get_val($p, 'ref');
				$handler= RevSliderPackageInstaller::get_handler('provider_' . $slug);

				if($handler !== false && is_callable($handler['install'])){
					$result = call_user_func($handler['install'], $p, $state);
					$eid = $this->get_val($result, 'effect_id', '');
				}else{
					$eid = $this->install_page_effect($post_id, $slug, $p, $state);
				}

				//No addon and no registered type: leave the placeholder empty rather than half-wiring an
				//effect nothing can render. The dry-run already warned about this.
				$token = '%%SR7_EFFECT:' . $ref . '%%';
				if(strpos($content, $token) === false) continue;

				$content = str_replace($token, $eid, $content);
				$changed = true;

				if($eid !== '') $state['map']['effects'][$ref] = $eid;
			}

			if($changed) wp_update_post(['ID' => $post_id, 'post_content' => $content]);
		}
	}

	/** @return string the minted effectId, or '' when the type is not available here */
	private function install_page_effect($post_id, $slug, $node, &$state){
		if(!class_exists('RevSliderPageEffects')) return '';

		$types = RevSliderPageEffects::get_types();
		if(!isset($types[$slug])) return '';

		$result = RevSliderPageEffects::install_effect($post_id, $slug, $this->get_val($node, 'data', []), $state['map']);
		if(is_wp_error($result)){
			$state['warnings'][] = sprintf(__('An effect on this page could not be set up: %s', 'revslider'), $result->get_error_message());
			return '';
		}

		return $this->get_val($result, 'effect_id', '');
	}

	/** @return array the dry-run report with slug collisions and unavailable page templates added */
	public function check_pages($manifest, $report){
		foreach($this->get_val($manifest, 'pages', []) as $page){
			$slug = $this->get_val($page, 'slug');
			if($slug === '') continue;

			$existing = get_page_by_path($slug, OBJECT, $this->get_val($page, 'post_type', 'page'));
			if($existing){
				$report['warnings'][] = sprintf(__('A page with the address "%1$s" already exists — "%2$s" will be created as a separate page.', 'revslider'), $slug, $this->get_val($page, 'title'));
			}

			$tpl = $this->get_val($page, 'template');
			if($tpl !== '' && !$this->template_allowed($tpl)){
				$report['errors'][] = sprintf(__('Page "%1$s" asks for a page template that is not available here: %2$s', 'revslider'), $this->get_val($page, 'title'), $tpl);
			}
		}

		return $report;
	}

	/** @return array */
	public function check_menus($manifest, $report){
		$locations = get_registered_nav_menus();

		foreach($this->get_val($manifest, 'menus', []) as $menu){
			$loc = $this->get_val($menu, 'location');
			if($loc !== '' && !isset($locations[$loc])){
				$report['warnings'][] = sprintf(__('Your theme has no "%1$s" menu position — the "%2$s" menu will be created but not assigned.', 'revslider'), $loc, $this->get_val($menu, 'name'));
			}
		}

		return $report;
	}

	/** @return array */
	public function check_options($manifest, $report){
		$options = $this->get_val($manifest, 'options', []);
		if(empty($options['front_page'])) return $report;

		$current = get_option('show_on_front');
		if($current !== 'page' || intval(get_option('page_on_front')) > 0){
			$report['warnings'][] = __('This package sets your site\'s front page. Your current front page setting will be replaced.', 'revslider');
		}

		return $report;
	}

	/**
	 * Remove one install's host content. Order matters at the top: the site options go back BEFORE the
	 * front page is trashed, or WordPress is left pointing at a post in the bin.
	 * @return array removal counts
	 */
	public function uninstall_host_content($prev, $menus, $posts, $safe_media){
		$removed = ['pages' => 0, 'media' => 0, 'menus' => 0];

		if(!empty($prev)){
			if(isset($prev['show_on_front'])) update_option('show_on_front', $prev['show_on_front']);
			if(isset($prev['page_on_front'])) update_option('page_on_front', $prev['page_on_front']);
		}

		foreach($menus ?? [] as $menu_id){
			if(wp_get_nav_menu_object($menu_id)){ wp_delete_nav_menu($menu_id); $removed['menus']++; }
		}

		//Pages go to the trash, not the shredder: by the time a package is removed its pages may have
		//been edited for months, and "undo" has to mean something.
		foreach($posts ?? [] as $post_id){
			if(get_post($post_id)){ wp_trash_post($post_id); $removed['pages']++; }
		}

		foreach($safe_media ?? [] as $att_id){
			if(get_post($att_id)){ wp_delete_attachment($att_id, true); $removed['media']++; }
		}

		return $removed;
	}

	/** @return array [['id','title'], ...] for the receipt posts that still exist */
	public function host_page_titles($ids){
		$pages = [];

		foreach($ids ?? [] as $id){
			$post = get_post($id);
			if($post) $pages[] = ['id' => intval($id), 'title' => $post->post_title];
		}

		return $pages;
	}

	/** @return array the created pages with the links the finished dialog offers */
	public function host_page_links($ids){
		$out = [];

		foreach($ids ?? [] as $id){
			$post = get_post($id);
			if(!$post) continue;

			$out[] = [
				'id'	=> intval($id),
				'title'	=> $post->post_title,
				'view'	=> str_replace('&amp;', '&', (string)get_permalink($id)),
				'edit'	=> str_replace('&amp;', '&', (string)get_edit_post_link($id))
			];
		}

		return $out;
	}
}
