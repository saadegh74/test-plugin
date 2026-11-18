<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */
 
if(!defined('ABSPATH')) exit();

/**
 * Jetpack Boost compatibility.
 *
 * Boost defers and concatenates scripts, which breaks the slider: our JS has to run in order and partly
 * before the DOM is ready. Marking our tags with data-jetpack-boost="ignore" takes them out of that
 * optimization. All filters no-op when Jetpack is not active.
 * Kept for the frozen public API (tools/platform-split/api-baseline.json); since 7.2.3 RevSliderCachePlugins marks
 * our scripts for every optimizer, Boost included, and this class hooks nothing.
 */
class RevSliderJetPack extends RevSliderFunctions {

	public function __construct(){
	}

	/**
	 * script_loader_tag filter: mark our own and our addons' enqueued scripts as ignore-by-Boost
	 * @return string the (possibly rewritten) script tag
	 */
	public function exclude_scripts_from_defer($tag, $handle, $src = ''){
		if(!class_exists('Jetpack')) return $tag;
		$process = false;

		if(in_array($handle, ['tp-tools', 'sr7', 'sr7migration', 'revmin', 'revmin-actions', 'revmin-carousel', 'revmin-layeranimation', 'revmin-navigation', 'revmin-panzoom', 'revmin-parallax', 'revmin-slideanims', 'revmin-video'])){
			$process = true;
		}

		//add addons to ignore list - match against the actual script URL only, never the full tag: inline scripts (e.g. the block editor settings JSON) are concatenated into $tag and rewriting them corrupts the JSON
		if($src !== '' && (strpos($src, 'rbtools.min.js') !== false || strpos($src, 'revolution.addon.') !== false || strpos($src, 'liquideffect') !== false || strpos($src, 'distortion') !== false || strpos($src, 'pixi.min.js') !== false || strpos($src, 'rslottie-js') !== false)){
			$process = true;
		}

		return ($process && $src !== '') ? str_replace(' src', ' data-jetpack-boost="ignore" src', $tag) : $tag;
	}

	/**
	 * same marker for the inline script blocks we generate ourselves (header scripts, slider html,
	 * setREVStartSize) - these never pass through script_loader_tag
	 * @return string
	 */
	public function add_defer_to_script_tags($html){
		if(!class_exists('Jetpack')) return $html;

		return str_replace('<script', '<script data-jetpack-boost="ignore"', $html);
	}
}
