<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Core\PageEffects;

if(!defined('ABSPATH')) exit();

/**
 * Scroll Animation's catalogue side: the curated tiles, the leaf a stored key points at, and the payload
 * core/public/js/scrollanim.pe.js and animpreset.js play. Which blocks carry it and how it reaches the page are the
 * host's (RevSliderPageEffectScrollAnim on WordPress).
 *
 * ⭐ THE PRESET IS A REFERENCE, NOT A COPY. A block stores the preset KEY ("cinematic.reveal" + a variant) and this
 * class resolves the body out of the catalogue (Data::get_layer_animations) at RENDER time, so a retune reaches every
 * page that uses the preset and tools/anim-player/fidelity.mjs stays meaningful.
 * @since 7.2.1
 */
class ScrollAnim {

	/** @var array|null the layer animation catalogue, decoded once per request; a property, not a method static, so a subclass shares it on every PHP */
	private static $catalog = null;

	/**
	 * The presets the picker offers. NOT a copy of any preset data - a key, a name and the still the editor's own
	 * animation browser uses for that tile (presets.js _IMGLAYER). The variants are read from the catalogue at
	 * runtime (tile_list), so an axis added there shows up here without an edit.
	 *
	 * Curated rather than complete: the catalogue's 124 presets include families that need a module stage, a mask
	 * wrapper or an SVG path, and a tile that quietly does nothing is worse than no tile. Every entry below is
	 * measured by tools/anim-player/fidelity.mjs. 'x' marks a text preset, offered only where there is text.
	 * @return array
	 */
	public static function tiles($scene = 'in'){
		if($scene === 'out') return [
			// Leaving again. Fewer, on purpose: an out preset is over in a moment and nobody browses them the
			// way they browse an entrance. Mask families are left out for the same reason as above — no wrapper.
			['k' => 'basic.fade',			't' => __('Fade Out', 'revslider'),		'a' => 'layer-presets/basic/fade.jpg'],
			['k' => 'slide.shortdistance',	't' => __('Slide Away', 'revslider'),	'a' => 'layer-presets/movement/slde.jpg'],
			['k' => 'zoom.zoomout',			't' => __('Zoom Out', 'revslider'),		'a' => 'layer-presets/movement/zoom.jpg'],
			['k' => 'zoom.zoomin',			't' => __('Zoom In', 'revslider'),		'a' => 'layer-presets/movement/zoom.jpg'],
			['k' => 'rotate.turn',			't' => __('Turn', 'revslider'),			'a' => 'layer-presets/rotate/turn.jpg'],
			['k' => 'rotate.flip',			't' => __('Flip', 'revslider'),			'a' => 'layer-presets/rotate/flip.jpg'],
			['k' => 'lines.fade',			't' => __('Lines Fade', 'revslider'),	'x' => 'lines'],
			['k' => 'words.fade',			't' => __('Words Fade', 'revslider'),	'x' => 'words'],
			['k' => 'letter.fade',			't' => __('Letters Fade', 'revslider'),	'x' => 'chars']
		];
		return [
			// Motion — plays on the block as a whole, so it fits any of them.
			['k' => 'basic.fade',			't' => __('Fade', 'revslider'),			'a' => 'layer-presets/basic/fade.jpg'],
			['k' => 'cinematic.reveal',		't' => __('Reveal', 'revslider'),		'a' => 'layer-presets/cinematic/reveal.jpg'],
			['k' => 'cinematic.rise',		't' => __('Rise', 'revslider'),			'a' => 'layer-presets/cinematic/rise.jpg'],
			['k' => 'cinematic.glide',		't' => __('Glide', 'revslider'),		'a' => 'layer-presets/cinematic/glide.jpg'],
			['k' => 'cinematic.bounce',		't' => __('Bounce', 'revslider'),		'a' => 'layer-presets/cinematic/bounce.jpg'],
			['k' => 'cinematic.punch',		't' => __('Punch', 'revslider'),		'a' => 'layer-presets/cinematic/punch.jpg'],
			['k' => 'cinematic.fold',		't' => __('Fold', 'revslider'),			'a' => 'layer-presets/cinematic/fold.jpg'],
			['k' => 'cinematic.tilt',		't' => __('Tilt', 'revslider'),			'a' => 'layer-presets/cinematic/tilt.jpg'],
			['k' => 'cinematic.swing',		't' => __('Swing', 'revslider'),		'a' => 'layer-presets/cinematic/swing.jpg'],
			['k' => 'cinematic.overshoot',	't' => __('Overshoot', 'revslider'),	'a' => 'layer-presets/cinematic/overshoot.jpg'],
			['k' => 'cinematic.skewslide',	't' => __('Skew Slide', 'revslider'),	'a' => 'layer-presets/cinematic/skewslide.jpg'],
			['k' => 'cinematic.zoomblast',	't' => __('Zoom Blast', 'revslider'),	'a' => 'layer-presets/cinematic/zoomblast.jpg'],
			['k' => 'cinematic.vortex',		't' => __('Vortex', 'revslider'),		'a' => 'layer-presets/cinematic/vortex.jpg'],
			['k' => 'curve.arcin',			't' => __('Arc', 'revslider'),			'a' => 'layer-presets/curved-motion/arcin.jpg'],
			['k' => 'curve.swoop',			't' => __('Swoop', 'revslider'),		'a' => 'layer-presets/curved-motion/swoop.jpg'],
			['k' => 'curve.whip',			't' => __('Whip', 'revslider'),			'a' => 'layer-presets/curved-motion/whip.jpg'],
			['k' => 'curve.hook',			't' => __('Hook', 'revslider'),			'a' => 'layer-presets/curved-motion/hook.jpg'],
			['k' => 'curve.drift',			't' => __('Drift', 'revslider'),		'a' => 'layer-presets/curved-motion/drift.jpg'],
			['k' => 'rotate.flip',			't' => __('Flip', 'revslider'),			'a' => 'layer-presets/rotate/flip.jpg'],
			['k' => 'rotate.turn',			't' => __('Turn', 'revslider'),			'a' => 'layer-presets/rotate/turn.jpg'],
			['k' => 'rotate.smash',			't' => __('Smash', 'revslider'),		'a' => 'layer-presets/rotate/smash.jpg'],
			['k' => 'zoom.zoomin',			't' => __('Zoom In', 'revslider'),		'a' => 'layer-presets/movement/zoom.jpg'],
			['k' => 'slide.shortdistance',	't' => __('Slide', 'revslider'),		'a' => 'layer-presets/movement/slde.jpg'],
			// Text — the block's own words, lines or characters carry the animation. No artwork for these in
			// the editor either: its browser puts SAMPLE TEXT on the tile (presets.js _SAMPLE), and so do we.
			['k' => 'lines.fade',			't' => __('Lines Fade', 'revslider'),	'x' => 'lines'],
			['k' => 'words.fade',			't' => __('Words Fade', 'revslider'),	'x' => 'words'],
			['k' => 'words.popin',			't' => __('Words Pop In', 'revslider'),	'x' => 'words'],
			['k' => 'letter.fade',			't' => __('Letters Fade', 'revslider'),	'x' => 'chars'],
			['k' => 'letter.zoomchars',		't' => __('Letters Zoom', 'revslider'),	'x' => 'chars'],
			['k' => 'kinetic.headline',		't' => __('Kinetic Headline', 'revslider'), 'x' => 'words']
		];
	}

	/**
	 * The whole "in" catalogue, decoded once per request. Only ever loaded on a page that actually holds the
	 * effect or in the block editor.
	 * @return array
	 */
	protected static function catalog($scene = 'in'){
		if(self::$catalog === null){
			//Functions, not its Data parent: the catalogue getters call get_val(), which the child defines
			$data = new \ThemePunch\SR\Core\Functions();
			self::$catalog = $data->get_layer_animations();
		}
		$scene = ($scene === 'out') ? 'out' : 'in';
		return (isset(self::$catalog[$scene]) && is_array(self::$catalog[$scene])) ? self::$catalog[$scene] : [];
	}

	// Keys of a multi preset that name something other than a variant.
	private static function meta_keys(){
		return ['multi', 'demo', 'icon', 'name', 'group', 'extend', 'roles'];
	}

	/**
	 * The catalogue leaf a stored key points at. A preset whose variant is gone falls back to the preset's
	 * first one — a renamed axis should still animate — but a preset that is gone resolves to null, and
	 * nothing is emitted at all (the block then renders untouched, which is the point).
	 * @return array|null
	 */
	public static function resolve($preset, $variant, $scene = 'in'){
		$parts = explode('.', (string)$preset);
		if(count($parts) !== 2) return null;

		$cat = self::catalog($scene);
		$node = isset($cat[$parts[0]][$parts[1]]) ? $cat[$parts[0]][$parts[1]] : null;
		if(!is_array($node)) return null;
		if(empty($node['multi'])) return $node;   // single-variant preset — the leaf IS the body

		if(isset($node[$variant]) && is_array($node[$variant])) return $node[$variant];
		foreach($node as $k => $v){
			if(!in_array($k, self::meta_keys(), true) && is_array($v)) return $v;
		}
		return null;
	}

	/**
	 * The curated tiles with the variants each one actually has, read out of the catalogue. Dropping the
	 * do-nothing variants ("noanim") keeps a tile from offering "…and then it doesn't animate".
	 * @return array
	 */
	public static function tile_list($scene = 'in'){
		$cat = self::catalog($scene);
		$out = [];
		foreach(self::tiles($scene) as $t){
			$parts = explode('.', $t['k']);
			$node = isset($cat[$parts[0]][$parts[1]]) ? $cat[$parts[0]][$parts[1]] : null;
			if(!is_array($node)) continue;   // the catalogue moved on and this tile no longer exists

			$vars = [];
			if(!empty($node['multi'])){
				foreach($node as $k => $v){
					if(in_array($k, self::meta_keys(), true) || !is_array($v)) continue;
					if($k === 'noanim' || $k === 'none') continue;
					$vars[] = $k;
				}
				if(empty($vars)) continue;
			}
			$t['v'] = $vars;   // empty = the preset has no axis at all
			$out[] = $t;
		}
		return $out;
	}

	/**
	 * Whitelist the stored settings into the payload the runtime plays, and RESOLVE the preset key into the
	 * catalogue body while we are at it — that resolution is the whole reference model, and it belongs here
	 * (one place, on both the front and the editor preview path).
	 * @return array
	 */
	public static function sanitize($a){
		if(!is_array($a)) $a = [];

		// "cat.preset" — the catalogue's own key shape; sanitize_key would eat the dot
		$preset = preg_replace('/[^a-z0-9.]/', '', strtolower((string)($a['preset'] ?? '')));
		$variant = sanitize_key((string)($a['variant'] ?? ''));
		if($preset === '' || strlen($preset) > 48) return [];

		$body = self::resolve($preset, $variant);
		if(empty($body)) return [];   // renamed or removed → emit nothing, and the block stays as it is

		// once = the first time and never again · down/up = every time it is entered FROM that direction
		// (entered from the other one it is simply there, at rest) · both = every time.
		$mode = sanitize_key((string)($a['mode'] ?? ''));
		if(!in_array($mode, ['once', 'down', 'up', 'both'], true)){
			// blocks saved before the modes existed carried a plain "play once" switch
			$mode = (isset($a['once']) && empty($a['once'])) ? 'both' : 'once';
		}

		$inMode = (sanitize_key((string)($a['inMode'] ?? '')) === 'scroll') ? 'scroll' : 'trigger';

		$devices = sanitize_key((string)($a['devices'] ?? ''));
		if(!in_array($devices, ['all', 'nomobile', 'desktop'], true)) $devices = 'all';

		$data = [
			'body'		=> $body,
			'speed'		=> max(0.25, min(4, round(floatval($a['speed'] ?? 1), 2))),
			'delay'		=> max(0, min(5000, intval($a['delay'] ?? 0))),
			'mode'		=> $mode,
			'offset'	=> max(0, min(50, intval($a['offset'] ?? 15))),
			'inMode'	=> $inMode,
			// Under 10% the whole entrance would be over in a tenth of a screen, which reads as a jump rather
			// than a movement; over 90% it can outrun the page on the last block.
			'inSpan'	=> max(10, min(90, intval($a['inSpan'] ?? 50))),
			'devices'	=> $devices,
			'stagger'	=> max(0, min(600, intval($a['stagger'] ?? 0)))
		];

		// The exit. Only for a block that is allowed to play more than once — an out on a "once" block would
		// take the content away and never bring it back.
		$outPreset = preg_replace('/[^a-z0-9.]/', '', strtolower((string)($a['outPreset'] ?? '')));
		// A scrubbed entrance is a function of position, not an event — "plays once" cannot apply to it, so the
		// exit is on offer there whatever `mode` says.
		if(($mode !== 'once' || $inMode === 'scroll') && $outPreset !== '' && strlen($outPreset) <= 48){
			$outBody = self::resolve($outPreset, sanitize_key((string)($a['outVariant'] ?? '')), 'out');
			if(!empty($outBody)){
				$outMode = sanitize_key((string)($a['outMode'] ?? ''));
				$data['out'] = [
					'body'	=> $outBody,
					// scroll = the timeline is driven by how far the block has travelled out, so it can never be
					// finished while the block is still on screen. trigger = it plays at its own speed once the
					// mark is passed, which is snappier and can outrun a slow reader.
					'mode'	=> ($outMode === 'trigger') ? 'trigger' : 'scroll',
					'at'	=> max(0, min(90, intval($a['outAt'] ?? 20)))
				];
			}
		}

		return $data;
	}
}
