<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Core\PageEffects;

if(!defined('ABSPATH')) exit();

/**
 * Cursor Motion's payload: a cursor keyword and a pointer motion, checked against what SR7.F.layerIX
 * (core/public/js/layerix.js) knows. Where the effect is switched on is the host's (RevSliderPageEffectPointerIX on
 * WordPress, on native blocks).
 *
 * ⚠ The preset CATALOGUE is not mirrored here. It lives in core/admin/assets/js/pointer.presets.js and the editor
 * writes the RESOLVED numbers; a second copy in PHP goes stale the moment a preset is added. What this class checks
 * is the shape and the ranges - the cursor keyword is an allowlist because CSS keywords do not go stale.
 * @since 7.2.1
 */
class PointerIX {

	// The eight modes SR7.F.layerIX knows. An unknown one is dropped rather than passed on: the engine would
	// register an entry that can never move, and the block would carry a pointer listener for nothing.
	const MODES	= ['magnetic', 'attract', 'repel', 'squeeze', 'pop', 'stretch', 'tilt', 'rotate'];
	const EASES	= ['smooth', 'back', 'elastic', 'bounce'];
	const AXES	= ['both', 'x', 'y'];
	// `cursor` is SR7's own default and resolves to no CSS at all — the runtime writes nothing for it.
	const CURSORS = ['cursor', 'auto', 'default', 'pointer', 'text', 'move', 'crosshair', 'help', 'wait',
					'zoom-in', 'zoom-out', 'none'];

	/**
	 * Whitelist what the runtime is allowed to act on. The editor resolved the preset already, so what
	 * arrives here is a plain ix object; every number is clamped to the range the layer panel offers.
	 * @return array
	 */
	public static function sanitize($a){
		if(!is_array($a)) $a = [];
		$out = [];

		$cursor = is_string($a['cursor'] ?? null) ? $a['cursor'] : '';
		if(in_array($cursor, self::CURSORS, true) && $cursor !== 'cursor') $out['cursor'] = $cursor;

		$ix = (isset($a['ix']) && is_array($a['ix'])) ? $a['ix'] : [];
		$type = is_string($ix['type'] ?? null) ? $ix['type'] : '';
		if(in_array($type, self::MODES, true)){
			$num = function($v, $def, $min, $max){ $v = is_numeric($v) ? floatval($v) : $def; return max($min, min($max, intval(round($v)))); };
			$m = [
				'type'			=> $type,
				'strength'		=> $num($ix['strength'] ?? null, 40, 0, 150),
				'radius'		=> $num($ix['radius'] ?? null, 200, 20, 800),
				'speed'			=> $num($ix['speed'] ?? null, 12, 2, 40),
				'returnEase'	=> in_array($ix['returnEase'] ?? '', self::EASES, true) ? $ix['returnEase'] : 'smooth'
			];
			// axis only clamps a translate, perspective only means anything to tilt — carrying either any
			// further would put a key in the payload that the engine reads and ignores
			if(in_array($type, ['magnetic', 'attract', 'repel'], true))
				$m['axis'] = in_array($ix['axis'] ?? '', self::AXES, true) ? $ix['axis'] : 'both';
			if($type === 'tilt') $m['perspective'] = $num($ix['perspective'] ?? null, 800, 200, 3000);
			$out['ix'] = $m;
		}

		// neither a cursor nor a motion → nothing to mount
		return empty($out) ? [] : $out;
	}
}
