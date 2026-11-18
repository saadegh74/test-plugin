<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Core\PageEffects;

if(!defined('ABSPATH')) exit();

/**
 * What every Page Effect type and every host shares: the data-sr7pe marker the front runtime mounts on, and the
 * Template Package media rewrite of a type's own config. Registering types, storing instances and emitting the
 * payload are the host's (RevSliderPageEffects on WordPress, around Gutenberg blocks).
 * @since 7.2.1
 */
class Common {

	/**
	 * Stamp an effect instance onto the root element of rendered markup, so the runtime finds it.
	 *
	 * SEVERAL effects may share a block, so the id is APPENDED to whatever is there: data-sr7pe="id1 id2".
	 * 🔴 A second attribute would not work: HTML keeps only the FIRST duplicate, and the loser's querySelector
	 * then finds nothing - it never mounts, silently. Hence the runtimes match with [data-sr7pe~="id"].
	 *
	 * @param string $html       the block's rendered markup
	 * @param string $effect_id  the effect instance id
	 * @param string $classes    space separated classes to add to the root element
	 * @return string
	 */
	public static function tag_host($html, $effect_id, $classes = ''){
		$eid = sanitize_key($effect_id);
		if($eid === '' || !preg_match('/^(\s*)<([a-zA-Z0-9]+)\b([^>]*)>/', $html, $m)) return $html;
		$attrs = $m[3];
		// already tagged by another effect → add this id to the list instead of turning it away
		if(preg_match('/\sdata-sr7pe\s*=\s*(["\'])(.*?)\1/', $attrs, $d)){
			$have = preg_split('/\s+/', trim($d[2]), -1, PREG_SPLIT_NO_EMPTY);
			if(in_array($eid, $have, true)) return $html;
			$have[] = $eid;
			$attrs = str_replace($d[0], ' data-sr7pe="' . esc_attr(implode(' ', $have)) . '"', $attrs);
			if($classes !== ''){
				if(preg_match('/\sclass\s*=\s*["\']/', $attrs)) $attrs = preg_replace('/(\sclass\s*=\s*["\'])/', '$1' . $classes . ' ', $attrs, 1);
				else $attrs = ' class="' . esc_attr($classes) . '"' . $attrs;
			}
			return substr_replace($html, '<' . $m[2] . $attrs . '>', strlen($m[1]), strlen($m[0]) - strlen($m[1]));
		}
		if($classes !== ''){
			if(preg_match('/\sclass\s*=\s*["\']/', $attrs)) $attrs = preg_replace('/(\sclass\s*=\s*["\'])/', '$1' . $classes . ' ', $attrs, 1);
			else $attrs = ' class="' . esc_attr($classes) . '"' . $attrs;
		}
		$open = '<' . $m[2] . $attrs . ' data-sr7pe="' . esc_attr($eid) . '">';
		return substr_replace($html, $open, strlen($m[1]), strlen($m[0]) - strlen($m[1]));
	}

	/**
	 * Resolve the media references in one of a type's own fields.
	 *
	 * The exporter turns every uploads URL into a %%SR7_MEDIA:…%% reference and banks the file with the package;
	 * the installer resolves those again in MARKUP. A type's own config never passes through markup, and only the
	 * type knows which of its fields hold a picture, which is why it calls this rather than the other way round.
	 * The id is rewritten along with the url: it names a different picture on the site the package lands on.
	 *
	 * @param mixed $value a url, or a picked image ['id' => int, 'url' => string, …]
	 * @param array $map   the installer's resolution map
	 * @return mixed the same shape, pointing at this site's copy
	 */
	public static function remap_media($value, $map){
		if(is_array($value)){
			if(!isset($value['url'])) return $value;

			$id = self::media_id($value['url'], $map);
			if($id <= 0) return $value;

			$value['url'] = self::media()->file_url($id);
			$value['id']  = $id;

			return $value;
		}

		if(!is_string($value) || strpos($value, '%%SR7_MEDIA:') === false) return $value;

		return preg_replace_callback(\ThemePunch\SR\Core\Admin\PackageInstaller::TOKEN_MEDIA, function($m) use ($map){
			$id = self::mapped_media($m[1], $map);

			return $id ? self::media()->file_url($id) : '';
		}, $value);
	}

	/**
	 * remap_media() over every field a type names as a path below its data; '__ARRAY__' walks a list.
	 * The type declares which fields hold a picture, the host resolves them - the split the Add-On
	 * descriptor's 'media' paths already use, so a type's neutral half never calls a host class.
	 *
	 * @param array $data   the type's config
	 * @param array $fields paths, e.g. [['images', '__ARRAY__'], ['settings', 'revealImage']]
	 * @param array $map    the installer's resolution map
	 * @return array
	 */
	public static function remap_fields($data, $fields, $map){
		if(!is_array($data)) return $data;
		foreach($fields as $path) $data = self::remap_path($data, $path, $map);

		return $data;
	}

	private static function remap_path($node, $path, $map){
		if(empty($path)) return self::remap_media($node, $map);
		if(!is_array($node)) return $node;

		$key = array_shift($path);
		if($key === '__ARRAY__'){
			foreach($node as $i => $item) $node[$i] = self::remap_path($item, $path, $map);
			return $node;
		}
		if(isset($node[$key])) $node[$key] = self::remap_path($node[$key], $path, $map);

		return $node;
	}

	/** @return int the library item this url resolves to, or 0 if it is not a package reference */
	private static function media_id($url, $map){
		if(!is_string($url)) return 0;
		if(!preg_match(\ThemePunch\SR\Core\Admin\PackageInstaller::TOKEN_MEDIA, $url, $m)) return 0;

		return self::mapped_media($m[1], $map);
	}

	/** @return int */
	private static function mapped_media($ref, $map){
		$media = (isset($map['media']) && is_array($map['media'])) ? $map['media'] : [];

		return isset($media[$ref]) ? intval($media[$ref]) : 0;
	}

	/** @return \ThemePunch\SR\Ports\MediaLibrary static: no $this to ask */
	private static function media(){
		return \ThemePunch\SR\Core\Registry::instance()->get('RevSliderFunctions')->media_library();
	}
}
