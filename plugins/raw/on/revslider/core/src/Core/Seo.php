<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Core;

if(!defined('ABSPATH')) exit();

/**
 * What a module says, as the HTML an SEO analysis reads: text layers under their heading tags, every picture
 * with its alt. An analysis sees a module only as a shortcode or a block, so nothing it says reaches the checks
 * otherwise. WordPress hands this to Yoast SEO and Rank Math (RevSliderSEO, core/admin/assets/js/tools/seo.js).
 * @since 7.2.1
 */
class Seo extends Functions {

	/**
	 * The text of each module, keyed by alias. An alias nobody has answers with ''
	 * @param array $aliases
	 * @return array
	 */
	public function modules_text($aliases){
		$out = [];
		foreach((array)$aliases as $alias){
			$alias = sanitize_text_field((string)$alias);
			if($alias === '' || isset($out[$alias])) continue;
			$out[$alias] = $this->module_text($alias);
		}

		return $out;
	}

	/**
	 * One module as the HTML the analysis reads: text layers as headings or paragraphs, pictures as img
	 * with alt and title, in slide order, the global layers last
	 * @param string $alias
	 * @return string
	 */
	public function module_text($alias){
		$slider = new Slider();
		$slider->init_by_alias($alias, false);
		$id = $slider->get_id();
		if(empty($id)) return '';

		$slide	= new Slide();
		$slides	= $slide->get_slides_by_slider_id($id, true);
		$static	= new Slide();
		if($static->init_static_slide_by_slider_id($id)) $slides[] = $static;

		$html = '';
		foreach($slides as $s){
			foreach($s->get_layers() ?? [] as $layer){
				$html .= $this->layer_text($layer);
			}
		}

		return ($html === '') ? '' : '<div class="sr7-seo-content" data-alias="' . esc_attr($alias) . '">' . "\n" . $html . '</div>';
	}

	/**
	 * @param array $layer
	 * @return string one line of HTML, '' for a layer with nothing to read
	 */
	public function layer_text($layer){
		$type = $this->get_val($layer, 'type', 'text');

		if(in_array($type, ['text', 'button'], true)){
			//a post based module carries its metas as {{title}} placeholders, no words for the analysis
			$text = trim(preg_replace('/\{\{[^}]*\}\}/', '', $this->strip_icons((string)$this->get_val($layer, ['content', 'text']))));
			//an arrow, a dot between two arrows, a bare icon: nothing to read
			if(!preg_match('/[\p{L}\p{N}]/u', strip_tags($text))) return '';
			$tag = $this->get_val($layer, 'tag', '');
			$tag = in_array($tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true) ? $tag : 'p';

			return '<' . $tag . '>' . wp_kses_post($text) . '</' . $tag . '>' . "\n";
		}

		//a picture is any layer drawn from bg.image: an image shape, a slide background, a zone with a photo
		if($this->get_val($layer, ['bg', 'image', 'u'], true) !== false && in_array($this->get_val($layer, ['bg', 'type'], ''), ['image', ''], true)){
			$img = $this->get_biggest_device_setting_v7($this->get_val($layer, ['bg', 'image', 'src']), ['ld' => true, 'd' => true, 'n' => true, 't' => true, 'm' => true], '');
			if(empty($img) || !is_string($img)) return '';
			$attr = $this->get_media_alt_title($layer, $img);

			return '<img src="' . esc_url($img) . '" alt="' . esc_attr(strip_tags((string)$attr['alt'])) . '" title="' . esc_attr(strip_tags((string)$attr['title'])) . '">' . "\n";
		}

		return '';
	}

	/**
	 * An icon font's <i> holds a ligature name (material-icons: arrow_forward) or nothing, never words. The
	 * needles are the same the front output looks for
	 * @param string $text
	 * @return string
	 */
	public function strip_icons($text){
		static $rx = null;
		if($rx === null){
			$needles = array_values($this->set_icon_sets(['material-icons']));
			$rx = '/<i\b[^>]*class="[^"]*(?:' . implode('|', array_map(static function($n){ return preg_quote($n, '/'); }, $needles)) . ')[^"]*"[^>]*>.*?<\/i>/is';
		}

		return preg_replace($rx, '', $text);
	}
}
