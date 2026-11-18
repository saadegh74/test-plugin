<?php
/**
 * CURSOR MOTION PAGE EFFECT — the layer panel's Interaction section, on a native WordPress block.
 *
 * A cursor icon and a pointer motion in one namespaced attribute (sr7PointerIX). The front hands the resolved
 * `ix` object to SR7.F.layerIX; no module, no slide, no canvas, so the page loads tptools + GSAP +
 * core/public/js/layerix.js and nothing else.
 *
 * ⭐ EXTENSION ONLY. The framework insists on a block name, so this type carries one, registered with
 * `inserter: false`: a "Cursor Motion" block would be a wrapper around content you already have, which says
 * less than switching the effect on for the block itself.
 *
 * ⚠ The preset CATALOGUE is not mirrored here. It lives in core/admin/assets/js/pointer.presets.js and the editor
 * writes the RESOLVED numbers into the block; a second copy in PHP goes stale the moment a preset is added.
 * What is checked is the shape and the ranges (the core's ThemePunch\SR\Core\PageEffects\PointerIX, which this
 * extends) - the cursor keyword is an allowlist because CSS keywords do not go stale.
 *
 * @author    ThemePunch <info@themepunch.com>
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

class RevSliderPageEffectPointerIX extends \ThemePunch\SR\Core\PageEffects\PointerIX {

	const TYPE	= 'pointerix';
	const BLOCK	= 'themepunch/sr7-cursorix';
	// The blocks the effect can be switched on for — the same list Scroll Animation offers, because "does
	// this block react to the pointer" is as true of a paragraph as it is of an image.
	const HOSTS	= ['core/paragraph', 'core/heading', 'core/list', 'core/quote', 'core/image', 'core/group',
					'core/cover', 'core/columns', 'core/buttons', 'core/media-text', 'core/gallery', 'core/video'];

	/** wire the hooks (called once on plugins_loaded) @return void */
	public static function init(){
		static $done = false;
		if($done) return;
		$done = true;

		// 'init' priority 5, before the framework's register_blocks at init:10, so __() is safe.
		add_action('init', [self::class, 'register'], 5);
		// Mark a host block that has the effect switched on so the runtime finds it by [data-sr7pe].
		add_filter('render_block', [self::class, 'wrap_host'], 20, 2);
		// The catalogue for the block editor's panel (priority 20 — the framework enqueues its shell at 10).
		add_action('enqueue_block_editor_assets', [self::class, 'editor_assets'], 20);
		// Front assets + per-block config (priority 20: after core's own add_scripts at 10).
		add_action('wp_enqueue_scripts', [self::class, 'front_enqueue'], 20);
	}

	/** Register Cursor Motion as a Page Effect TYPE with the framework. @return void */
	public static function register(){
		if(!class_exists('RevSliderPageEffects')) return;
		RevSliderPageEffects::register_type(self::TYPE, [
			'block'			=> self::BLOCK,
			'title'			=> __('Cursor Motion', 'revslider'),
			'icon'			=> 'move',
			'description'	=> __('Let a block answer the pointer — lean toward it, pop, tilt or shy away.', 'revslider'),
			'self_block'	=> true,	// nothing generic renders it; see `inserter` below
			// 🔴 `takes: 'pointer'` and NOT 'box'. The box claim would rule out Scroll Animation on the same
			// block, and the two genuinely coexist: layerIX writes style.translate/scale/rotate (the
			// INDEPENDENT css transform properties) while a layer animation writes `transform`.
			'claim'			=> ['attr' => 'sr7PointerIX', 'scope' => 'self', 'takes' => 'pointer'],
			// The block exists because register_type wants one, and is kept out of the inserter because an
			// author never wants to ADD it — they want to switch it on for a block they already have.
			'supports'		=> ['inserter' => false],
			'editor'		=> RS_PLUGIN_URL . 'core/admin/assets/js/pointer.editor.js?mt=' . RevSliderFunctions::asset_time('core/admin/assets/js/pointer.editor.js'),
			'runtime'		=> RS_PLUGIN_URL . 'core/public/js/pointerix.pe.js?mt=' . RevSliderFunctions::asset_time('core/public/js/pointerix.pe.js'),
			'storage'		=> 'attrs',			// config lives in the block attributes, not in post meta — see front_enqueue()
			'sanitize'		=> [self::class, 'sanitize']
		]);
	}

	/**
	 * Block editor: the catalogue the panel reads its titles, families and drawn tiles from. ADMIN, not
	 * core/public/js — the front never renders any of it, it gets a resolved ix object.
	 * @return void
	 */
	public static function editor_assets(){
		if(!wp_script_is(RevSliderPageEffects::SHELL_HANDLE, 'enqueued')) return;   // no page effects on this screen
		// The ENGINE, in the block editor: hovering a tile runs the real interaction on it rather than showing
		// a mock-up of one. Same file the front loads — see the note at the top of core/public/js/layerix.js. Both are
		// urls now: only a block with a cursor motion on it reads them (pointer.editor.js, SR7.PE.useAsset).
		wp_localize_script(RevSliderPageEffects::SHELL_HANDLE . '-' . self::TYPE, 'SR7PointerEd', [
			'engine'	=> RS_PLUGIN_URL . 'core/public/js/layerix.js?ver=' . RevSliderFunctions::asset_time('core/public/js/layerix.js'),
			'presets'	=> RS_PLUGIN_URL . 'core/admin/assets/js/pointer.presets.js?ver=' . RevSliderFunctions::asset_time('core/admin/assets/js/pointer.presets.js')
		]);
	}

	/**
	 * Front: the runtime bootstrap + this adapter, and each block's config as
	 * SR7.E.pageEffects[effectId] = { type:'pointerix', data:{ cursor, ix } }.
	 * @return void
	 */
	public static function front_enqueue(){
		if(is_admin() || !is_singular()) return;
		$post = RevSliderPageEffects::queried_post();   // NOT get_post($id) — that misses a preview, see there
		if(!$post) return;

		$blocks = self::collect_effects(parse_blocks($post->post_content));
		if(empty($blocks)) return;

		$payloads = [];
		foreach($blocks as $eid => $a){
			$data = self::sanitize($a);
			if(!empty($data)) $payloads[$eid] = $data;
		}
		if(empty($payloads)) return;

		$ver		= RS_REVISION;
		$rtHandle	= RevSliderPageEffects::RT_HANDLE;

		// tptools carries the resource loader the adapter drives; the same handle core's own front uses, so a
		// page that ALSO holds a module does not load it twice.
		wp_enqueue_script(RevSliderFront::register_tp_tools(true));
		wp_enqueue_script($rtHandle, RS_PLUGIN_URL . 'core/public/js/page-effects.js', [], $ver, true);
		wp_enqueue_script('sr7-pointerix-pe', RS_PLUGIN_URL . 'core/public/js/pointerix.pe.js', [$rtHandle, 'tp-tools'], RevSliderFunctions::asset_time('core/public/js/pointerix.pe.js'), true);
		// The two files the adapter pulls through SR7's own loader once it is running. layerix.js is both
		// concatenated into sr7.js and shipped standalone, so this url resolves in either build.
		wp_localize_script('sr7-pointerix-pe', 'SR7PointerPE', [
			'engine'	=> RS_PLUGIN_URL . 'core/public/js/layerix.js?ver=' . RevSliderFunctions::asset_time('core/public/js/layerix.js'),
			'gsap'		=> RS_PLUGIN_URL . 'core/public/js/libs/tpgsap.js?ver=' . $ver
		]);

		$js = 'window.SR7=window.SR7||{};SR7.E=SR7.E||{};SR7.E.pageEffects=SR7.E.pageEffects||{};';
		foreach($payloads as $eid => $data){
			$js .= 'SR7.E.pageEffects[' . wp_json_encode($eid) . ']=' . wp_json_encode(['type' => self::TYPE, 'data' => $data]) . ';';
		}
		wp_add_inline_script('sr7-pointerix-pe', $js, 'before');
	}

	/**
	 * Recursively collect [effectId => attrs] for every host block with the effect switched on.
	 * @return array
	 */
	private static function collect_effects($blocks){
		$out = [];
		foreach((array)$blocks as $b){
			$name = isset($b['blockName']) ? $b['blockName'] : '';
			if(in_array($name, self::HOSTS, true) && !empty($b['attrs']['sr7PointerIX'])){
				$pi = $b['attrs']['sr7PointerIX'];
				if(is_array($pi) && !empty($pi['enabled']) && !empty($pi['effectId'])){
					$eid = sanitize_key($pi['effectId']);
					if($eid !== '') $out[$eid] = $pi;
				}
			}
			if(!empty($b['innerBlocks'])) $out += self::collect_effects($b['innerBlocks']);
		}
		return $out;
	}

	/**
	 * Mark a host block that has the effect switched on so the runtime can find it by [data-sr7pe]. Pre-JS
	 * the block renders exactly as it always did — nothing here hides or moves anything.
	 * @return string
	 */
	public static function wrap_host($html, $block){
		if(empty($block['blockName']) || !in_array($block['blockName'], self::HOSTS, true)) return $html;
		$pi = isset($block['attrs']['sr7PointerIX']) ? $block['attrs']['sr7PointerIX'] : null;
		if(!is_array($pi) || empty($pi['enabled']) || empty($pi['effectId'])) return $html;
		return RevSliderPageEffects::tag_host($html, $pi['effectId'], 'sr7-ptr sr7-pointerix-host');
	}
}
