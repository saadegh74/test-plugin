<?php
/**
 * @author	ThemePunch <info@themepunch.com>
 * @link	  https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Core\Addon;

if(!defined('ABSPATH')) exit();

/**
 * AddOn media export/import. Only wired for AddOns that declare a 'media' config.
 * Collects the configured media paths below addOns.<title> into the slider export list and
 * registers the same paths for import, plus raw v6 legacy entries.
 **/
/**
 * Media handling for one AddOn: contributes the image/video files the AddOn references to a slider export,
 * and maps their paths back when such a package is imported again.
 */
class Media extends \ThemePunch\SR\Core\Functions {

	private $title;
	private $media	= false;	//['layers' => [path arrays below addOns.<title>], 'slides' => [...], 'slider' => [...], 'v6' => raw legacy import entries, 'live' => callable(block): bool]
	private $mimes	= [];		//extension => mime of the files the AddOn ships beyond images and videos (a 3D model), for the upload and the import

	public function __construct($base, $args){
		$this->title	= $base->title;
		$this->media	= $this->get_val($args, 'media', false);

		//not only in the admin: an import or export can run from a REST route, a cron job or WP-CLI, and without these
		//paths it packs none of the AddOn's files and leaves their src relative (a gallery of 404s after a REST import)
		if($this->media !== false){
			add_filter('sr_exportSlider_usedMedia', [$this, 'export_addon_media'], 10, 3);
			add_filter('revslider_import_image_path', [$this, 'add_import_paths']);
			add_filter('revslider_export_owned_media', [$this, 'own_media']);
			if(!empty($this->media['v6'])) add_filter('revslider_import_image_path_v6', [$this, 'add_import_paths_v6']);
		}

		$this->mimes	= (array)$this->get_val($args, 'mimes', []);
		if(!empty($this->mimes)) add_filter('revslider_import_media_allowed_mimes', [$this, 'allow_mimes']);
		if($this->environment()->is_admin_area() && !empty($this->mimes)){
			add_filter('upload_mimes', [$this, 'allow_mimes']);
			add_filter('wp_check_filetype_and_ext', [$this, 'check_filetype'], 10, 4);
		}
	}

	/**
	 * the import paths above also reach the export's own walk: this AddOn packs its files itself, so a switched off block stays out
	 * @return array the AddOn titles whose addOns.<title> paths the export leaves to them
	 */
	public function own_media($titles){
		$titles[] = $this->title;
		return $titles;
	}

	/** @return array the allowed mimes with the AddOn's own types added */
	public function allow_mimes($mimes){
		foreach($this->mimes as $ext => $mime) $mimes[$ext] = $mime;
		return $mimes;
	}

	/**
	 * the content sniff reports octet-stream for a binary model and would refuse the upload: the extension decides for the AddOn's own types
	 * @return array
	 */
	public function check_filetype($data, $file, $filename, $mimes){
		if(!empty($data['ext']) && !empty($data['type'])) return $data;
		$ext = strtolower(pathinfo((string)$filename, PATHINFO_EXTENSION));
		if(isset($this->mimes[$ext])){ $data['ext'] = $ext; $data['type'] = $this->mimes[$ext]; }
		return $data;
	}

	/**
	 * Collect the configured Media Paths into the Export List. The old copied export_adddon_images never added the src urls ($url/$src Copy Paste), this walk does
	 * @return array the media files this addon references, added to the export package
	 **/
	public function export_addon_media($used_media, $slides, $sliderParams){
		foreach($this->get_val($this->media, 'layers', []) as $path){
			foreach($slides ?? [] as $slide){
				foreach($this->get_val($slide, 'layers', []) as $layer){
					$this->collect_media($this->live($this->get_val($layer, ['addOns', $this->title], [])), $path, $used_media);
				}
			}
		}
		foreach($this->get_val($this->media, 'slides', []) as $path){
			foreach($slides ?? [] as $slide){
				$node = $this->get_val($slide, ['params', 'addOns', $this->title], $this->get_val($slide, ['addOns', $this->title], []));
				$this->collect_media($this->live($node), $path, $used_media);
			}
		}
		//the module level, where an AddOn keeps what belongs to the whole module rather than to one layer
		foreach($this->get_val($this->media, 'slider', []) as $path){
			$this->collect_media($this->live($this->get_val($sliderParams, ['addOns', $this->title], [])), $path, $used_media);
		}
		return $used_media;
	}

	/**
	 * A block switched off (u false) draws nothing, so its files stay out of the package. No u at all is not
	 * "off": older AddOns carry no switch, and _truefalse(null) would read as false. The AddOn's 'live' callable
	 * decides the rest, a 3D model that is switched off inside a switched on block for one
	 * @return array the node, or [] when switched off
	 */
	private function live($node){
		$u = $this->get_val($node, 'u', null);
		if($u !== null && $this->_truefalse($u) === false) return [];
		$live = $this->get_val($this->media, 'live', null);
		return (is_callable($live) && !empty($node) && !$live($node)) ? [] : $node;
	}

	/** @return void */
	private function collect_media($node, $path, &$used_media){
		if(empty($node)) return;
		$key = array_shift($path);
		if($key === '__ARRAY__'){
			if(!is_array($node)) return;
			foreach($node as $sub) $this->collect_media($sub, $path, $used_media);
			return;
		}
		$next = $this->get_val($node, $key, false);
		if(empty($path)){
			//an inlined image carries its own bytes: there is no file to pack, and the export list is keyed by url
			if(is_string($next) && $next !== '' && strpos($next, 'data:') !== 0) $used_media[$next] = true;
			return;
		}
		$this->collect_media($next, $path, $used_media);
	}

	/** @return array */
	public function add_import_paths($image_path){
		foreach($this->get_val($this->media, 'layers', []) as $path) $image_path['layers'][] = array_merge(['addOns', $this->title], $path);
		foreach($this->get_val($this->media, 'slides', []) as $path) $image_path['slides'][] = array_merge(['addOns', $this->title], $path);
		foreach($this->get_val($this->media, 'slider', []) as $path) $image_path['slider'][] = array_merge(['addOns', $this->title], $path);
		return $image_path;
	}

	/**
	 * v6 Entries are raw full Paths per Environment, the legacy Schemas differ too much per AddOn for a
	 * Prefix Convention
	 * @return array
	 */
	public function add_import_paths_v6($image_path){
		foreach($this->get_val($this->media, 'v6', []) as $env => $paths){
			foreach($paths ?? [] as $path) $image_path[$env][] = $path;
		}
		return $image_path;
	}
}
