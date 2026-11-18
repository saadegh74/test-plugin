<?php
/**
 * @author	ThemePunch <info@themepunch.com>
 * @link	  https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Core\Addon;

if(!defined('ABSPATH')) exit();

/**
 * Meta placeholder handling for one AddOn: resolves {{...}} inside the option paths the AddOn declares
 * below addOns.<title>. Only wired for AddOns that declare a 'metas' config, with the same path shape
 * RevSliderAddonMedia uses ('layers'|'slides'|'slider', '__ARRAY__' for a numbered list).
 *
 * PHP resolves before the module JSON is written into the page, so the value is in the markup for crawlers
 * even when the JS never runs. Runtime only placeholders ({{slide_index}}, stream data of an ajax loaded
 * module) stay for SR7.F.updateDynamicMetas on the client.
 **/
class Metas extends \ThemePunch\SR\Core\Functions {

	private $title;
	private $metas	= false;	//['layers' => [path arrays below addOns.<title>], 'slides' => [...], 'slider' => [...]]

	public function __construct($base, $args){
		$this->title	= $base->title;
		$this->metas	= $this->get_val($args, 'metas', false);

		if($this->metas === false) return;

		add_filter('sr_load_slider_json', [$this, 'resolve_basics'], 10, 1);
		add_filter('revslider_slide_setLayersByPostData_layer', [$this, 'resolve_post_metas'], 10, 4);
	}

	/**
	 * Site wide basic metas, on the two paths that reach a visitor page: the module JSON of the front and
	 * the html export. The editor never passes here, so it keeps showing the placeholder
	 * @return array the slider data with the declared addon paths resolved
	 **/
	public function resolve_basics($data){
		if(empty($data) || !is_array($data)) return $data;

		$cb = function($value){
			return $this->resolve_basic_metas($value);
		};

		foreach($this->get_val($this->metas, 'slider', []) as $path){
			if(isset($data['settings'])) $this->walk($data['settings'], $this->full_path($path), $cb);
		}
		foreach($this->get_val($data, 'slides', []) as $sid => $slide){
			foreach($this->get_val($this->metas, 'slides', []) as $path){
				if(isset($data['slides'][$sid]['slide'])) $this->walk($data['slides'][$sid]['slide'], $this->full_path($path), $cb);
			}
			foreach($this->get_val($this->metas, 'layers', []) as $path){
				foreach($this->get_val($slide, 'layers', []) as $lid => $layer){
					$this->walk($data['slides'][$sid]['layers'][$lid], $this->full_path($path), $cb);
				}
			}
		}

		return $data;
	}

	/**
	 * Post and stream metas ({{title}}, {{meta:key}}, {{featured_image_*}}) for the declared layer paths.
	 * Runs inside the same walk over the post data the layer text already goes through
	 * @return array the layer with the declared addon paths resolved
	 **/
	public function resolve_post_metas($layer, $attr, $post_id, $slide){
		if(empty($this->get_val($this->metas, 'layers', []))) return $layer;

		$cb = function($value) use ($slide, $attr, $post_id){
			return $slide->set_post_data($value, $attr, $post_id);
		};

		foreach($this->get_val($this->metas, 'layers', []) as $path){
			$this->walk($layer, $this->full_path($path), $cb);
		}

		return $layer;
	}

	/** @return array the declared path prefixed with the addon node it lives in */
	private function full_path($path){
		return array_merge(['addOns', $this->title], (array)$path);
	}

	/** @return void */
	private function walk(&$node, $path, $cb){
		if(!is_array($node) || empty($path)) return;

		$key = array_shift($path);
		if($key === '__ARRAY__'){
			foreach($node as $k => $sub){
				if(empty($path)) $this->resolve_value($node[$k], $cb);
				else $this->walk($node[$k], $path, $cb);
			}
			return;
		}

		if(!isset($node[$key])) return;
		if(empty($path)){
			$this->resolve_value($node[$key], $cb);
			return;
		}

		$this->walk($node[$key], $path, $cb);
	}

	/** @return void */
	private function resolve_value(&$value, $cb){
		if(!is_string($value) || strpos($value, '{{') === false) return; //no placeholder, no work
		$value = $cb($value);
	}
}
