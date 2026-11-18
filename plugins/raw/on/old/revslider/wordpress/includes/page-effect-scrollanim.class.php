<?php
/**
 * SCROLL ANIMATION PAGE EFFECT — the SR7 layer-animation catalogue, on ordinary page content.
 *
 * INSPECTOR-style, self_block page effect built on the Pan & Zoom page effect's shape and, like it, CORE's
 * own. Two ways in: a dedicated block themepunch/sr7-scrollanim, or a block EXTENSION on the common content
 * blocks, where the front animates the markup the block already rendered. Like Pan & Zoom it does NOT pull
 * the SR7 module engine: core/public/js/animpreset.js plays a preset body on a plain element with only GSAP.
 *
 * ⭐ THE PRESET IS A REFERENCE, NOT A COPY. The block stores the preset KEY ("cinematic.reveal" + a variant)
 * and the core resolves the body out of the catalogue (ThemePunch\SR\Core\PageEffects\ScrollAnim) at RENDER time,
 * so a retune reaches every page that uses the preset and tools/anim-player/fidelity.mjs stays meaningful.
 * Baking could not keep its promise anyway: the ~10 rules in animpreset.js interpret the data, so a change
 * there moves a frozen body just as much as a live one. The price is that a renamed preset stops resolving,
 * hence the rule below: the start state is set by JS ALONE, never by PHP or inline CSS.
 *
 * @author    ThemePunch <info@themepunch.com>
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

class RevSliderPageEffectScrollAnim extends \ThemePunch\SR\Core\PageEffects\ScrollAnim {

	const TYPE	= 'scrollanim';
	const BLOCK	= 'themepunch/sr7-scrollanim';
	// Blocks the effect can be switched on for (block extension — scrollanim.editor.js).
	const HOSTS	= ['core/paragraph', 'core/heading', 'core/list', 'core/quote', 'core/image', 'core/group',
					'core/cover', 'core/columns', 'core/buttons', 'core/media-text', 'core/gallery', 'core/video'];
	// Of those, the ones that carry text: only there is a split preset (lines / words / characters) offered,
	// because SplitText needs something to cut. Our own block counts as one — the author puts text inside it.
	const TEXT_HOSTS = ['core/paragraph', 'core/heading', 'core/list', 'core/quote'];

	/** wire the hooks (called once on plugins_loaded) @return void */
	public static function init(){
		static $done = false;
		if($done) return;
		$done = true;

		// Register on 'init' priority 5, before the framework's register_blocks at init:10, so __() is safe.
		add_action('init', [self::class, 'register'], 5);
		// Mark a host block that has the effect switched on so the runtime finds it by [data-sr7pe].
		add_filter('render_block', [self::class, 'wrap_host'], 20, 2);
		// Editor CANVAS styles (the block list is iframed since WP 6.3 → they must go through editor settings).
		add_filter('block_editor_settings_all', [self::class, 'editor_canvas_styles']);
		// The player + GSAP + the curated presets for the block editor: the panel's tiles play the real
		// animation (priority 20 — the framework enqueues its shell, and tptools, at the default 10).
		add_action('enqueue_block_editor_assets', [self::class, 'editor_assets'], 20);
		// Front assets + per-block config (priority 20: after core's own add_scripts at 10).
		add_action('wp_enqueue_scripts', [self::class, 'front_enqueue'], 20);
	}

	/** Register Scroll Animation as a Page Effect TYPE with the framework. @return void */
	public static function register(){
		if(!class_exists('RevSliderPageEffects')) return;
		RevSliderPageEffects::register_type(self::TYPE, [
			'block'			=> self::BLOCK,
			'title'			=> __('Scroll Animation', 'revslider'),
			'icon'			=> 'controls-play',
			'spriteIcon'	=> 'MoveIn',
			'description'	=> __('Give any block a Slider Revolution layer animation — it plays when the block scrolls into view.', 'revslider'),
			'self_block'	=> true,			// the editor JS registers the block (inspector edit), not the generic card
			'claim'			=> ['attr' => 'sr7Anim', 'scope' => 'self', 'takes' => 'box'],	// the block's own box, not the picture in it — so an image effect may run alongside	// we animate the block itself — see SR7.PE.claimedBy
			'attributes'	=> self::attributes(),
			'supports'		=> ['align' => ['wide', 'full']],
			'storage'		=> 'attrs',			// config lives in the block attributes, not in post meta — see front_enqueue()
			'render'		=> [self::class, 'render'],
			'editor'		=> RS_PLUGIN_URL . 'core/admin/assets/js/scrollanim.editor.js?mt=' . RevSliderFunctions::asset_time('core/admin/assets/js/scrollanim.editor.js'),
			'runtime'		=> RS_PLUGIN_URL . 'core/public/js/scrollanim.pe.js?mt=' . RevSliderFunctions::asset_time('core/public/js/scrollanim.pe.js'),
			'sanitize'		=> [self::class, 'sanitize']
		]);
	}

	/** Block attribute schema (mirrored in scrollanim.editor.js so the dynamic block round-trips). @return array */
	public static function attributes(){
		return [
			'preset'	=> ['type' => 'string',	'default' => ''],		// "cat.preset" — a leaf of the "in" catalogue
			'variant'	=> ['type' => 'string',	'default' => ''],		// the preset's own axis value ("up", "strong", …)
			'speed'		=> ['type' => 'number',	'default' => 1],		// timeScale — the body carries the real durations
			'delay'		=> ['type' => 'number',	'default' => 0],		// ms after it comes into view
			'mode'		=> ['type' => 'string',	'default' => 'once'],	// once | down | up | both — see sanitize()
			'offset'	=> ['type' => 'number',	'default' => 15],		// % of the viewport it has to come in by
			// The entrance's CLOCK. 'trigger' plays it at its own speed once the block is far enough in;
			// 'scroll' hands the clock to the scroll position — the reveal runs under the reader's finger and
			// winds back when they go up. With that, speed / delay / mode have nothing left to say.
			'inMode'	=> ['type' => 'string',	'default' => 'trigger'],	// trigger | scroll
			'inSpan'	=> ['type' => 'number',	'default' => 50],		// % of the screen the scrubbed entrance runs over
			// Leaving again. Only ever emitted when 'mode' is not 'once' — a block that plays a single time
			// has no second act. See the out scene's own rules in animpreset.js (build → opt.scene).
			'outPreset'	=> ['type' => 'string',	'default' => ''],		// "cat.preset" — a leaf of the "out" catalogue
			'outVariant'=> ['type' => 'string',	'default' => ''],
			'outMode'	=> ['type' => 'string',	'default' => 'scroll'],	// scroll = along the leaving stretch | trigger = fire and forget
			'outAt'		=> ['type' => 'number',	'default' => 20],		// % of the viewport where the leaving stretch starts
			'devices'	=> ['type' => 'string',	'default' => 'all'],	// all | nomobile | desktop
			'stagger'	=> ['type' => 'number',	'default' => 0]			// ms between the block's own children, 0 = the block moves as one
		];
	}

	// Blocks that hold children worth staggering. Anywhere else the setting is not offered, because a
	// paragraph's "children" are its words — which is what the Text presets are for.
	const STAGGER_HOSTS = ['core/group', 'core/columns', 'core/buttons', 'core/gallery', 'core/list'];

	/**
	 * Block editor: GSAP + the player, so a tile plays the very animation the front will run, plus the curated
	 * list and the BODIES behind it — the panel resolves nothing itself, it is handed what PHP resolved.
	 * @return void
	 */
	public static function editor_assets(){
		if(!wp_script_is(RevSliderPageEffects::SHELL_HANDLE, 'enqueued')) return;   // no page effects on this screen
		// the player and GSAP are urls now: only a screen holding a scroll animation reads them (SR7.PE.useAsset)

		$tiles = $bodies = [];
		foreach(['in', 'out'] as $scene){
			$tiles[$scene] = self::tile_list($scene);
			$cat = self::catalog($scene);
			foreach($tiles[$scene] as $t){
				$parts = explode('.', $t['k']);
				$node = $cat[$parts[0]][$parts[1]] ?? null;
				if(!is_array($node)) continue;
				if(empty($t['v'])){ $bodies[$scene][$t['k']] = ['' => $node]; continue; }
				foreach($t['v'] as $v) if(isset($node[$v])) $bodies[$scene][$t['k']][$v] = $node[$v];
			}
		}

		wp_localize_script(RevSliderPageEffects::SHELL_HANDLE . '-' . self::TYPE, 'SR7ScrollAnimEd', [
			'url'			=> RS_PLUGIN_URL . 'core/admin/assets/images/presets/',
			'player'		=> RS_PLUGIN_URL . 'core/public/js/animpreset.js?ver=' . RevSliderFunctions::asset_time('core/public/js/animpreset.js'),
			'tiles'			=> $tiles,
			'bodies'		=> $bodies,
			'textHosts'		=> self::TEXT_HOSTS,
			'staggerHosts'	=> self::STAGGER_HOSTS,
			'block'			=> self::BLOCK
		]);
	}

	/** The block card inside the editor iframe (the SR7 Page Effect family look). The badge on a host block is
	 *  the shell's — see RevSliderPageEffects::badge_canvas_styles(). @return array */
	public static function editor_canvas_styles($settings){
		$css = '.sr7-scrollanim-block{position:relative}'
			. '.sr7-sa-edit{border:1px dashed #d9ccff;border-radius:0;padding:6px}'
			. '.sr7-sa-edit-head{display:flex;align-items:center;gap:10px;margin-bottom:8px;font-size:13px}'
			. '.sr7-sa-edit-meta{color:#646970;flex:1;font-size:13px}';
		if(!isset($settings['styles']) || !is_array($settings['styles'])) $settings['styles'] = [];
		$settings['styles'][] = ['css' => $css];
		return $settings;
	}

	/**
	 * Front: enqueue the runtime bootstrap + the player + the adapter, and emit each block's RESOLVED config as
	 * x.
	 * @return void
	 */
	public static function front_enqueue(){
		if(is_admin() || !is_singular()) return;
		$post = RevSliderPageEffects::queried_post();   // NOT get_post($id) — that misses a preview, see there
		if(!$post) return;

		$blocks = self::collect_effects(parse_blocks($post->post_content));
		if(empty($blocks)) return;

		$payloads = [];
		$split = false;
		foreach($blocks as $eid => $a){
			$data = self::sanitize($a);
			if(empty($data)) continue;
			$payloads[$eid] = $data;
			foreach(['lines', 'words', 'chars'] as $s){
				if(isset($data['body'][$s]) || isset($data['out']['body'][$s])) $split = true;
			}
		}
		if(empty($payloads)) return;

		$ver		= RS_REVISION;
		$rtHandle	= RevSliderPageEffects::RT_HANDLE;

		// tptools carries the resource loader the adapter drives to pull GSAP; the same handle core's own
		// front uses, so a page that ALSO holds a module does not load it twice.
		wp_enqueue_script(RevSliderFront::register_tp_tools(true));
		wp_enqueue_script($rtHandle, RS_PLUGIN_URL . 'core/public/js/page-effects.js', [], $ver, true);
		wp_enqueue_script('sr7-scrollanim-pe', RS_PLUGIN_URL . 'core/public/js/scrollanim.pe.js', [$rtHandle, 'tp-tools'], RevSliderFunctions::asset_time('core/public/js/scrollanim.pe.js'), true);
		// The two files the adapter loads through SR7's own loader once it is running.
		wp_localize_script('sr7-scrollanim-pe', 'SR7ScrollAnimPE', [
			'player'	=> RS_PLUGIN_URL . 'core/public/js/animpreset.js?ver=' . RevSliderFunctions::asset_time('core/public/js/animpreset.js'),
			'gsap'		=> RS_PLUGIN_URL . 'core/public/js/libs/tpgsap.js?ver=' . $ver
		]);

		// A split unit has to be a block-level box or a transform does nothing to it. SplitText writes the
		// display inline itself, but the engine's own pages carry these four rules (core/public/css/sr7.lp.css) and
		// the classes it hands out are the same — so a page effect brings them along rather than relying on
		// whichever the split library happens to set. Only emitted when a split preset is actually on the page.
		if($split){
			wp_register_style('sr7-scrollanim', false);
			wp_enqueue_style('sr7-scrollanim');
			wp_add_inline_style('sr7-scrollanim',
				'.sr7_splitted_lines{display:block;color:inherit;font-size:inherit;font-weight:inherit;line-height:inherit}'
				. '.sr7_splitted_words,.sr7_splitted_words_noanim,.sr7_splitted_chars{display:inline-block;color:inherit;font-size:inherit;font-weight:inherit;line-height:inherit}');
		}

		$js = 'window.SR7=window.SR7||{};SR7.E=SR7.E||{};SR7.E.pageEffects=SR7.E.pageEffects||{};';
		foreach($payloads as $eid => $data){
			$js .= 'SR7.E.pageEffects[' . wp_json_encode($eid) . ']=' . wp_json_encode(['type' => self::TYPE, 'data' => $data]) . ';';
		}
		wp_add_inline_script('sr7-scrollanim-pe', $js, 'before');
	}

	/**
	 * Recursively collect [effectId => attrs] for every Scroll Animation on the page:
	 *  - the dedicated themepunch/sr7-scrollanim block → its own attributes
	 *  - a host block with the effect switched on → its sr7Anim attributes
	 * @return array
	 */
	private static function collect_effects($blocks){
		$out = [];
		foreach((array)$blocks as $b){
			$name = isset($b['blockName']) ? $b['blockName'] : '';
			if($name === self::BLOCK && !empty($b['attrs']['effectId'])){
				$eid = sanitize_key($b['attrs']['effectId']);
				if($eid !== '') $out[$eid] = $b['attrs'];
			}else if(in_array($name, self::HOSTS, true) && !empty($b['attrs']['sr7Anim'])){
				$sa = $b['attrs']['sr7Anim'];
				if(is_array($sa) && !empty($sa['enabled']) && !empty($sa['effectId'])){
					$eid = sanitize_key($sa['effectId']);
					if($eid !== '') $out[$eid] = $sa;
				}
			}
			if(!empty($b['innerBlocks'])) $out += self::collect_effects($b['innerBlocks']);
		}
		return $out;
	}

	/**
	 * Mark a host block that has the effect switched on so the runtime can find it by [data-sr7pe] and animate
	 * the markup it already rendered. Pre-JS the block renders normally — and that is also what a visitor sees
	 * if the scripts never arrive, because nothing here hides anything.
	 * @return string
	 */
	public static function wrap_host($html, $block){
		if(empty($block['blockName']) || !in_array($block['blockName'], self::HOSTS, true)) return $html;

		// A rich-text block IS its own editable element, so an editor badge portalled into it became part of the TEXT
		// and was saved with the post. The editor no longer does that; this takes the leftovers back out of content
		// saved before the fix. Both names: this effect's own badge, and the shell's that replaced it.
		if(strpos($html, 'sr7-sa-hostbadge') !== false || strpos($html, 'sr7pe-badge') !== false){
			$html = preg_replace('#<button[^>]*(?:sr7-sa-hostbadge|sr7pe-badge)[^>]*>.*?</button>#is', '', $html);
		}

		$sa = isset($block['attrs']['sr7Anim']) ? $block['attrs']['sr7Anim'] : null;
		if(!is_array($sa) || empty($sa['enabled']) || empty($sa['effectId'])) return $html;
		return RevSliderPageEffects::tag_host($html, $sa['effectId'], 'sr7-sa sr7-scrollanim-host');
	}

	/**
	 * Front markup for the dedicated block: its inner blocks in a box the effect owns. Pre-JS (and with JS
	 * off) that is the content, plain and visible.
	 * @return string
	 */
	public static function render($attributes, $content = '', $block = null){
		$a	 = is_array($attributes) ? $attributes : [];
		$eid = isset($a['effectId']) ? sanitize_key($a['effectId']) : '';
		if($eid === '') return $content;

		$align = (isset($a['align']) && in_array($a['align'], ['wide', 'full'], true)) ? ' align' . $a['align'] : '';

		return '<div class="sr7-sa sr7-scrollanim' . $align . '" id="sr7sa-' . esc_attr($eid) . '" data-sr7pe="' . esc_attr($eid) . '">'
			. $content
			. '</div>';
	}
}
