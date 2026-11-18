<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

namespace ThemePunch\SR\Core;

if(!defined('ABSPATH')) exit();

/**
 * One slider ("module").
 *
 * Loaded from the sliders table via init_by_id()/init_by_alias() into $params (the editor's settings tree)
 * and $settings (internal flags). It also resolves the slider's *source*: slides can come from the database
 * (gallery), from posts/products, or from a social stream, and the get_slides_data_from_* / streamline_*
 * methods turn each of those into the same slide structure.
 *
 * In preview mode it transparently reads and writes the *_preview tables (set_special_table_mode()).
 */
class Slider extends Functions {
	
	public $id;
	public $title;
	public $alias;
	public $settings		= [];
	public $params			= [];
	public $metas			= [];
	public $slides;
	public $type;
	public $inited			= false;
	public $map;
	public $is_woocommerce	= false;
	public $gallery_ids		= false;
	public $is_gallery		= false;
	public $table_slider;
	public $table_slides;

	/** @var \ThemePunch\SR\Ports\ContentSource|null lazily bound by content_source() */
	private $_content_source = null;

	/**
	 * @var RevSliderSlide
	 */
	public $_static_slide;
	
	/**
	 * used to determinate if we need to init the layers of the Slides
	 * can cause heavy ram usage on slider overview page if we have 100+ Sliders
	 **/
	public $init_layer = true;

	public function __construct(){
		parent::__construct();
		$this->map = [];
		$this->set_special_table_mode();
	}

	/**
	 * limit the slider to these attachment/post ids and switch it into gallery mode
	 * @return void
	 */
	public function set_gallery_ids($gallery_ids){
		$this->gallery_ids	= $gallery_ids;
		$this->is_gallery	= true;
	}

	/** @return array|false */
	public function get_gallery_ids(){
		return $this->gallery_ids;
	}

	/**
	 * point $table_slider/$table_slides at the preview tables while the editor renders unsaved drafts,
	 * and at the legacy v6 tables while a migration is running
	 * @return void
	 */
	public function set_special_table_mode(){
		global $SR_GLOBALS;

		if($this->context()->get('preview_mode') === true){
			$this->table_slider = \ThemePunch\SR\Core\Data::TABLE_SLIDER_PREVIEW;
			$this->table_slides = \ThemePunch\SR\Core\Data::TABLE_SLIDES_PREVIEW;
			return;
		}
		if($SR_GLOBALS['v6'] === true && \ThemePunch\SR\Core\Admin\UpgradeV6::do_v6_tables_exist()){
			$this->table_slider = \ThemePunch\SR\Core\Data::TABLE_SLIDER_V6;
			$this->table_slides = \ThemePunch\SR\Core\Data::TABLE_SLIDES_V6;
			return;
		}

		$this->table_slider = \ThemePunch\SR\Core\Data::TABLE_SLIDER;
		$this->table_slides = \ThemePunch\SR\Core\Data::TABLE_SLIDES;
	}

	/**
	 * does a slider with this id exist?
	 * @return bool
	 */
	public function check_id_v7($id){
		
		$slider	= $this->database()->get_row($this->database()->prepare("SELECT * FROM ". $this->database()->prefix() . $this->table_slider ." WHERE id = %d", $id), 'ARRAY_A');
		
		return !empty($slider);
	}

	/**
	 * return the map of slide IDs
	 * old id => new id, filled while duplicating or importing so references can be rewritten
	 * @return array
	 **/
	public function get_map(){
		return $this->map;
	}
	 
	/**
	 * init by id or alias
	 * @param mixed $mixed  slider id or alias
	 * @param bool $show_error
	 * @return void
	 */
	public function init_by_mixed($mixed, $show_error = true){
		if(is_numeric($mixed)){
			$this->init_by_id($mixed, $show_error);
		}else{
			$this->init_by_alias($mixed, $show_error);
		}
	}
	
	
	/**
	 * initialize the slider data by given id
	 * @param int $sid  slider id
	 * @param bool $show_error
	 * @return void
	 * @throws Exception when the slider does not exist and $show_error is set
	 */
	public function init_by_id($sid, $show_error = true){
		$this->validate_numeric($sid, 'Slider ID');

		$slider_data = $this->database()->get_row($this->database()->prepare("SELECT * FROM ". $this->database()->prefix() . $this->table_slider ." WHERE id = %d", $sid), 'ARRAY_A');		
		if(empty($slider_data) && !$this->environment()->is_admin_area() && $show_error === true) $this->throw_error('Slider not found.');
		
		if(!empty($slider_data)) $this->init_by_data($slider_data);
	}
	
	
	/**
	 * initialize the slider data by given alias
	 * @param string $alias  slider alias
	 * @param bool $show_error
	 * @return void
	 * @throws Exception when the slider does not exist and $show_error is set
	 */
	public function init_by_alias($alias, $show_error = true){

		$_alias = str_replace(' ', '-', $alias); //make sure that no spaces are added
		$slider_data = $this->database()->get_row($this->database()->prepare("SELECT * FROM ". $this->database()->prefix() . $this->table_slider ." WHERE alias = %s", $_alias), 'ARRAY_A');
		if(empty($slider_data)){ //go back to an very old option where an slider alias could have a space
			$slider_data = $this->database()->get_row($this->database()->prepare("SELECT * FROM ". $this->database()->prefix() . $this->table_slider ." WHERE alias = %s", $alias), 'ARRAY_A');
		}
		if(empty($slider_data) && !$this->environment()->is_admin_area() && $show_error === true){
			$this->throw_error('Slider with alias '.sanitize_text_field(esc_attr($alias)).' not found.');
		}
		
		if(!empty($slider_data)) $this->init_by_data($slider_data);
	}
	
	
	/**
	 * init slider by db data
	 * decodes the params/settings JSON of a fetched row into this object
	 * @return void
	 */
	public function init_by_data($data){
		$data = apply_filters('revslider_slider_init_by_data', $data, $this);
		
		$this->id		= $this->get_val($data, 'id');
		$this->title	= $this->get_val($data, 'title');
		$this->alias	= $this->get_val($data, 'alias');
		$this->settings	= (array)json_decode($this->get_val($data, 'settings'), true);
		$this->params	= (array)json_decode($this->get_val($data, 'params'), true);
		
		$this->params['version'] = $this->get_val($this->settings, 'version');
		
		$this->type		= $this->get_val($data, 'type');
		$this->inited	= true;

		$do_action = ($this->environment()->is_admin_area()) ? false : true;
		$do_action = ($this->environment()->is_ajax_or_api_request()) ? true : $do_action;
		$do_action = ($this->context()->get('preview_mode')) ? true : $do_action;
		
		if($do_action){
			global $SR_GLOBALS;
			if($SR_GLOBALS['data_init'] === true){
				do_action('revslider_slider_init_by_data_post', $this);
			}
		}
		
		$this->modify_by_global_settings();
	}
	
	
	/**
	 * set slider params
	 * in memory only - update_params() writes them back
	 * @return void
	 */
	public function set_params($params){
		$this->params = $params;
	}
	
	
	/**
	 * return params of current initialized Slider
	 * @return array
	 */
	public function get_params(){
		return $this->params;
	}
	
	
	/**
	 * set specific slider param
	 * @param array|string $name a single key, or a path of keys
	 * @since: 5.1.1
	 * @return void
	 */
	public function set_param($name, $value){
		if(is_array($name)){
			$params = &$this->params;
			if(!empty($name)){
				foreach($name as $key){
					//a level that is not there yet is null after the reference above: without this the walk stopped
					//and the value landed one level up, ['source', 'type'] on a module without a source block
					//wrote source = "posts" instead of source.type
					if($params === null) $params = [];
					if(is_array($params)){
						$params = &$params[$key];
					}elseif(is_object($params)){
						$params = &$params->$key;
					}
				}
			}
			$params = $value;
		}else{
			$this->params[$name] = $value;
		}
	}
	
	/**
	 * return certain param of current initialized Slider
	 * @param mixed $key
	 * @param string $default
	 * @return mixed
	 */
	public function get_param($key, $default = ''){
		if(!is_array($key)){
			return $this->get_val($this->params, $key, $default);
		}else{
			$a = $this->params;
			foreach($key as $k => $v){
				$a = $this->get_val($a, $v, $default);
			}
			
			return $a;
		}
	}
	
	
	/**
	 * return settings of current initialized Slider
	 * @since: 5.0
	 * @return array
	 */
	public function get_settings(){
		return $this->settings;
	}
	
	
	/**
	 * return certain setting
	 * @since: 5.0
	 * @return mixed
	 */
	public function get_setting($handle, $default){
		return $this->get_val($this->settings, $handle, $default);
	}
	
	
	/**
	 * get the slider title
	 * @return string
	 */
	public function get_title(){
		return $this->title;
	}
	
	
	/**
	 * get the slider alias
	 * @return string
	 */
	public function get_alias(){
		return $this->alias;
	}
	
	
	/**
	 * get slider shortcode
	 * @return string the [rev_slider] shortcode to embed this slider
	 */
	public function get_shortcode(){
		return '[rev_slider alias="'.$this->alias.'"]';
	}
	
	/**
	 * get the slider tags
	 * @since: 6.0
	 * @return array
	 */
	public function get_tags(){
		return $this->get_val($this->settings, 'tags', []);
	}
	
	
	/**
	 * get the slider id
	 * @return int|string
	 */
	public function get_id(){
		return $this->id;
	}
	

	/**
	 * return if the slider source is from posts
	 * true for every post based source, including WooCommerce products
	 * @return bool
	 */
	public function is_posts(){
		$source = $this->get_param(['source', 'type'], 'gallery');
		return in_array($source, ['post', 'posts', 'specific_posts', 'specific_post', 'current_post', 'woocommerce', 'woo'], true); //, 'gallery'
	}
	
	
	/**
	 * return if the slider source is from specific posts
	 * @return bool
	 */
	public function is_specific_posts(){
		$source = $this->get_param(['source', 'type'], 'gallery');
		return in_array($source, ['specific_posts', 'specific_post'], true);
	}

	/**
	 * What an embed asks of a post based module on top of what the module saved. post_ids narrows it to those
	 * posts, in that order. category points it at other terms, written as ids, slugs or the stored
	 * "<taxonomy>_<id>" form. taxonomy names the terms' taxonomy where it is not the one the module already uses.
	 * The shortcode applies it before the markup is built and the front end's stream request carries the same
	 * array back (see get_stream_data), so both halves show the same posts. A gallery module is left alone.
	 * @since 7.2.0
	 * @param array $query post_ids, category, taxonomy
	 * @return bool whether anything changed
	 */
	public function apply_source_query($query){
		if(!is_array($query) || !$this->is_posts()) return false;

		$woo	= in_array($this->get_param(['source', 'type'], 'gallery'), ['woocommerce', 'woo'], true);
		$branch	= $woo ? 'woo' : 'post';
		$ids	= (string)$this->get_val($query, 'post_ids', '');
		$cats	= (string)$this->get_val($query, 'category', '');
		$tax	= sanitize_key((string)$this->get_val($query, 'taxonomy', ''));
		$did	= false;

		if(!$woo && $ids !== ''){
			$ids = implode(',', array_filter(array_map('intval', explode(',', $ids))));
			if($ids !== ''){
				$this->set_param(['source', 'post', 'subType'], 'specific_posts');
				$this->set_param(['source', 'post', 'list'], $ids);
				$this->set_param(['source', 'post', 'sortBy'], 'post__in'); //the order they were written in
				$did = true;
			}
		}

		if($cats !== ''){
			$terms = $this->resolve_terms($cats, $tax !== '' ? $tax : $this->source_taxonomy($branch, $woo ? 'product_cat' : 'category'));
			if(!empty($terms)){
				$this->set_param(['source', $branch, 'category'], implode(',', $terms));
				$did = true;
			}
		}

		return $did;
	}

	/**
	 * Terms as the module stores them, "<taxonomy>_<id>", from what a person writes: ids, slugs or that form.
	 * What names no term is dropped.
	 * @since 7.2.0
	 * @param string $list comma separated
	 * @param string $taxonomy for the ids and slugs
	 * @return array
	 */
	public function resolve_terms($list, $taxonomy){
		return $this->create_content_source()->term_keys($list, $taxonomy);
	}

	/**
	 * The taxonomy the module's saved terms use, read off the first "<taxonomy>_<id>" entry
	 * @since 7.2.0
	 * @param string $branch post or woo
	 * @param string $fallback when the module has no terms saved
	 * @return string
	 */
	public function source_taxonomy($branch, $fallback){
		$saved = $this->get_param(['source', $branch, 'category'], '');
		$saved = is_array($saved) ? implode(',', $saved) : (string)$saved;
		foreach(explode(',', $saved) as $item){
			$item	= trim($item);
			$pos	= strrpos($item, '_');
			if($pos !== false && strpos($item, 'option_disabled') !== 0) return substr($item, 0, $pos);
		}

		return $fallback;
	}


	/**
	 * return if the slider source is from stream
	 * true for the social sources (facebook, instagram, flickr, vimeo, youtube)
	 * @return bool
	 */
	public function is_stream(){
		$source = $this->get_param(['source', 'type'], 'gallery');
		//page: the items come with the page and the engine builds their slides (SR7.F.pageItems), the server fetches nothing
		return (!in_array($source, ['post', 'posts', 'specific_posts', 'specific_post', 'current_post', 'woocommerce', 'woo', 'gallery', 'page'], true)) ? $source : false;
	}
	

	/**
	 * return if slider source is stream or post
	 * i.e. anything that is not a plain gallery of hand-made slides
	 * @return bool
	 */
	public function is_stream_post(){
		//page items too: their template slides go out whole in the JSON, the engine clones them, the markup holds none
		return ($this->is_stream() || $this->is_posts() || $this->get_param(['source', 'type'], '') === 'page') ? true : false;
	}
	
	/**
	 * get real slides number, from posts, social streams ect.
	 * how many slides the source is configured to deliver, which is not the number of stored slides
	 * @return int
	 */
	public function get_wanted_slides($type = 'post'){
		$ns = count((array)$this->slides); //cast: $this->slides defaults to null and count(null) is a TypeError in PHP 8
		
		switch($type){
			case 'post':
				if($this->get_param(['source', 'post', 'fetchType'], 'cat_tag') == 'next_prev'){
					$ns = 2;
				}else{
					$ns = $this->get_param(['source', 'post', 'maxPosts'], $ns);
					if(intval($ns) == 0) $ns = '∞';
				}
			break;
			case 'facebook':
			case 'instagram':
			case 'flickr':
			case 'youtube':
			case 'vimeo':
				$ns = $this->get_param(['source', $type, 'count'], $ns);
			break;
		}
		
		return $ns;
	}
	
	/**
	 * return true if slider is favorite
	 * @since: 5.0
	 * @obsolete since 6.0 as it was moved to the favorite.class.php
	 * @return bool
	 */
	public function is_favorite(){
		return $this->get_val($this->settings, 'favorite', 'false') == 'true';
	}
	
	
	/**
	 * return the number of Sliders existing
	 * @return int
	 */
	public function get_slider_count(){

		return count($this->database()->get_results("SELECT COUNT(*) FROM ".$this->database()->prefix() . $this->table_slider ." WHERE `type` = '' OR `type` IS NULL", 'ARRAY_A'));
	}
	
	
	/**
	 * get the first slide ID of the current slider
	 * @return int|false
	 */
	public function get_first_slide_id_from_gallery(){
		
		$slides = [];
		$record = $this->database()->get_row($this->database()->prepare("SELECT * FROM ". $this->database()->prefix() . $this->table_slides ." WHERE slider_id = %s ORDER BY slide_order ASC LIMIT 0,1", [$this->get_id()]), 'ARRAY_A');
		
		if(!empty($record)){
			$slide = new Slide();
			$slide->init_by_data($record);
			$sid = $slide->get_id();
			$slides[$sid] = $slide;
			
			return $slides;
		}
		
		return false;
	}
	
	
	/**
	 * get the alias of an slider by id
	 * @return string|false
	 **/
	public function get_alias_by_id($slider_id){

		$record = $this->database()->get_row($this->database()->prepare("SELECT `alias` FROM ". $this->database()->prefix() . $this->table_slider ." WHERE id = %s LIMIT 0,1", [$slider_id]), 'ARRAY_A');
		return (!empty($record)) ? $this->get_val($record, 'alias') : false;
	}
	
	
	/**
	 * get all sliders that have a certain string in the params
	 * a LIKE search over the params column - used to find e.g. every post based slider
	 * @since: 6.4.6
	 * @param array|string $string one or more needles
	 * @param bool $templates kept for callers, the needles are bracketed as one group either way now
	 * @return array matching slider rows
	 **/
	public function get_slider_by_param_string($string, $templates = false){
		
		$string = (array)$string;
		if(empty($string)) return [];

		//A thrown away module must not turn up in a search either, the caller wants something to work with.
		//🔴 The LIKEs are joined with OR, so they have to be bracketed as a group or the AND above binds to the
		//first one alone and every other match comes back whatever its type is
		$sql = "SELECT * FROM ". $this->database()->prefix() . $this->table_slider ." WHERE `type` NOT IN ('folder','trash') AND (";
		$add = '';
		
		foreach($string as $k => $v){
			$string[$k] = '%'.$v.'%';
			
			$sql .= $add. "params LIKE %s";
			if($add === '') $add = " OR ";
		}
		$sql .= ")";
		
		return $this->database()->get_results($this->database()->prepare($sql, $string), 'ARRAY_A');
	}
	
	
	/**
	 * get all images that are beeing used by the Slider
	 * walks slider background, slides and layers - the basis for export and for the image optimizer
	 * @return array
	 **/
	public function get_images(){
		$images = [];
		$ret	= [];
		$image = $this->get_val($this->params, ['layout', 'bg', 'image']);
		$a_url = $this->get_val($this->params, ['troubleshooting', 'alternateURL']);
		
		if($image != '') $images[$image] = true;
		if($a_url != '') $images[$a_url] = true;
		
		if(!empty($this->slides) && count($this->slides) > 0){
			foreach($this->slides as $key => $slide){
				$params = $slide->get_params();
				$layers = $slide->get_layers();
				$image	= $this->get_val($params, ['bg', 'image']);
				$thumb	= $this->get_val($params, ['thumb', 'src']);
				$a_thumb = $this->get_val($params, ['thumb', 'admin']);
				
				if($image != ''){
					$altOption	 = $this->get_val($params, ['attributes', 'altOption'], 'media_library');
					$titleOption = $this->get_val($params, ['attributes', 'titleOption'], 'media_library');
					$alt		 = '';
					$title		 = '';
					$id			 = ($altOption === 'media_library' || $titleOption === 'media_library') ? $this->media_library()->id_by_url($image) : false;
					switch($altOption){
						case 'media_library':
							if($id > 0) $alt = $this->media_library()->alt_text($id);
						break;
						case 'file_name':
							$alt = $image;
						break;
						case 'custom':
							$alt = $this->get_val($params, ['attributes', 'alt'], '');
						break;
					}
					switch($titleOption){
						case 'media_library':
							if($id > 0) $title = $this->media_library()->title($id);
						break;
						case 'file_name':
							$title = $image;
						break;
						case 'custom':
							$title = $this->get_val($params, ['attributes', 'title'], '');
						break;
					}
					$images[$image] = [
						'src' => $image,
						'alt' => $alt,
						'title' => $title
					];
				}
				if($thumb != '' && !isset($images[$thumb])) $images[$thumb] = true;
				if($a_thumb != '' && !isset($images[$a_thumb])) $images[$a_thumb] = true;
				
				if(!empty($layers)){
					foreach($layers as $layer){
						$type		= $this->get_val($layer, 'type', 'text');
						$image		= $this->get_val($layer, ['media', 'imageUrl']);
						$bg_image	= $this->get_val($layer, ['idle', 'backgroundImage']);
						
						if($image != '' && !isset($images[$image]))	$images[$image] = true;
						if($bg_image != '' && !isset($images[$bg_image])) $images[$bg_image] = true;
						
						if(in_array($type, ['video', 'audio'])){
							$poster = $this->get_val($layer, ['media', 'posterUrl'], '');
							if($poster != '' && !isset($images[$poster])) $images[$poster] = true;
						}
						if($type === 'video'){
							$very_big	= $this->get_val($layer, ['media', 'thumbs', 'veryBig']);
							$big		= $this->get_val($layer, ['media', 'thumbs', 'big']);
							$large		= $this->get_val($layer, ['media', 'thumbs', 'large']);
							$medium		= $this->get_val($layer, ['media', 'thumbs', 'medium']);
							$small		= $this->get_val($layer, ['media', 'thumbs', 'small']);
							
							$very_big	= (is_array($very_big) && isset($very_big['url'])) ? $very_big['url'] : $very_big;
							$big		= (is_array($big) && isset($big['url'])) ? $big['url'] : $big;
							$large		= (is_array($large) && isset($large['url'])) ? $large['url'] : $large;
							$medium		= (is_array($medium) && isset($medium['url'])) ? $medium['url'] : $medium;
							$small		= (is_array($small) && isset($small['url'])) ? $small['url'] : $small;
							
							if($very_big != '' && !isset($images[$very_big])) $images[$very_big] = true;
							if($big != '' && !isset($images[$big]))			  $images[$big] = true;
							if($large != '' && !isset($images[$large]))		  $images[$large] = true;
							if($medium != '' && !isset($images[$medium]))	  $images[$medium] = true;
							if($small != '' && !isset($images[$small]))		  $images[$small] = true;
						}
					}
				}
			}
		}
		
		if(!empty($images)){
			foreach($images as $img => $b){
				if(!is_bool($b)){
					$ret[] = $b;
				}else{
					$alt = '';
					$title = '';
					$id = $this->media_library()->id_by_url($img);
					if($id > 0){
						$alt   = $this->media_library()->alt_text($id);
						$title = $this->media_library()->title($id);
					}
					$ret[] = [
						'src' => $img,
						'alt' => $alt,
						'title' => $title
					];
				}
			}
		}
		
		return $ret;
	}
	
	
	/**
	 * check if alias already exists
	 * @param bool $return_id return the owning slider id instead of a boolean
	 * @return bool|int
	 */
	public static function alias_exists($alias, $return_id = false){
		$f  = Registry::instance()->get('RevSliderFunctions'); //static context, no $this
		$db = $f->database();
		$table = ($f->context()->get('preview_mode') === false) ? \ThemePunch\SR\Core\Data::TABLE_SLIDER : \ThemePunch\SR\Core\Data::TABLE_SLIDER_PREVIEW;
		$alias_exists = $db->get_row($db->prepare("SELECT id FROM ". $db->prefix() . $table ." WHERE alias = %s", $alias), 'ARRAY_A');
		
		if($return_id === true) return (!empty($alias_exists)) ? $alias_exists['id'] : false;
		
		return !empty($alias_exists);
	}
	
	
	/**
	 * delete slider from datatase
	 * removes the slider row, its slides and the static slide
	 * @return void
	 */
	public function delete_slider(){
		
		$this->database()->delete($this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDER, ['id' => $this->id]);
		$this->database()->delete($this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDER_PREVIEW, ['id' => $this->id]);
		//The Meta Editing rows a module keeps were left behind by every delete so far, with nothing to reach
		//them again. Rows tagged by store template are shared and stay
		$this->database()->delete($this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLOTS, ['template_uid' => '', 'slider_id' => $this->id], ['%s', '%d']);
		
		$this->delete_all_slides();

		do_action('revslider_slider_on_delete_slider', $this->id);
	}


	/**
	 * is this row a module somebody threw away
	 * @since 7.2.0
	 * @return bool
	 **/
	public function is_trashed(){
		return $this->type === 'trash';
	}


	/**
	 * Move a module to the trash. Nothing a delete tears down happens here: the five listeners on
	 * revslider_slider_on_delete_slider - the optimizer's copies, the social caches, the add-on's
	 * translations - fire when the trash is emptied, so the trash only postpones the existing path.
	 * Folders are refused, a folder holds its children by id and would take them out of sight with it.
	 * @since 7.2.0
	 * @return bool
	 **/
	public function trash_slider(){
		if($this->type === 'folder' || $this->is_trashed()) return false;

		$settings				= is_array($this->settings) ? $this->settings : [];
		$settings['trashed']	= time();
		$done					= $this->database()->update($this->database()->prefix() . $this->table_slider, ['type' => 'trash', 'settings' => json_encode($settings)], ['id' => $this->id]);

		if($done === false) return false;

		$this->type		= 'trash';
		$this->settings	= $settings;
		do_action('revslider_slider_on_trash_slider', $this->id);

		return true;
	}


	/**
	 * Put a module back where it was. The type column is what every listing reads, so emptying it is the
	 * whole restore; the date goes with it so a second trip starts its own clock.
	 * @since 7.2.0
	 * @return bool
	 **/
	public function restore_slider(){
		if(!$this->is_trashed()) return false;

		$settings = is_array($this->settings) ? $this->settings : [];
		unset($settings['trashed']);
		$done = $this->database()->update($this->database()->prefix() . $this->table_slider, ['type' => '', 'settings' => json_encode($settings)], ['id' => $this->id]);

		if($done === false) return false;

		$this->type		= '';
		$this->settings	= $settings;
		do_action('revslider_slider_on_restore_slider', $this->id);

		return true;
	}


	/**
	 * what is in the trash, newest first, as raw rows - the overview needs a title and a date, not modules
	 * @since 7.2.0
	 * @return array
	 **/
	public function get_trashed_sliders(){
		return $this->database()->get_results("SELECT id, title, alias, settings FROM ". $this->database()->prefix() . $this->table_slider ." WHERE `type` = 'trash' ORDER BY `id` DESC", 'ARRAY_A') ?: [];
	}


	/**
	 * Delete what has been in the trash longer than the retention time, and answer how many went.
	 * Called when the module overview is opened: the plugin runs no cron, and a visitor's page load is the
	 * last place that should be deleting anything.
	 * @since 7.2.0
	 * @param int $days 0 keeps the trash for good
	 * @return int
	 **/
	public function purge_trash($days){
		$days = intval($days);
		if($days < 1) return 0;

		$cutoff	= time() - ($days * DAY_IN_SECONDS);
		$gone	= 0;

		foreach($this->get_trashed_sliders() as $row){
			$settings	= json_decode($this->get_val($row, 'settings'), true);
			$trashed	= intval($this->get_val($settings, 'trashed', 0));
			//A row with no date is one this version has not seen thrown away. It gets today's, so it leaves
			//after the full retention time rather than immediately
			if($trashed < 1){
				$this->init_by_id($this->get_val($row, 'id'), false);
				if($this->inited) $this->update_settings(['trashed' => time()]);
				continue;
			}
			if($trashed > $cutoff) continue;

			$this->init_by_id($this->get_val($row, 'id'), false);
			if(!$this->inited || !$this->is_trashed()) continue;
			$this->delete_slider();
			$gone++;
		}

		return $gone;
	}
	
	
	/**
	 * delete all slides
	 * @return void
	 */
	public function delete_all_slides(){
		
		$this->database()->delete($this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDES, ['slider_id' => $this->id]);
		$this->database()->delete($this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDES_PREVIEW, ['slider_id' => $this->id]);
		
		do_action('revslider_slider_delete_all_slides', $this->id);
		do_action('revslider_slider_deleteAllSlides', $this->id);
	}
	
	
	/**
	 * duplicate a whole slider including its slides
	 * @param bool $is_template the copy is created from a template, which changes the title handling
	 * @return int|false the new slider id
	 */
	public function duplicate_slider_by_id($id, $is_template = false){
		$this->validate_not_empty($id, 'Slider ID');
		$this->init_by_id($id);
		
		$title = $this->get_title();
		if($is_template){
			$title = str_replace(' Template', '', $title); //remove the added Template from the title in copy process
			$talias	= $title;
		}else{
			$talias	= $this->get_alias();
		}
		
		$ti = 1;
		while($this->alias_exists($talias)){ //set a new alias and title if its existing in database
			$talias = $title. ' ' .$ti;
			$ti++;
		}
		
		return $this->duplicate_slider($talias);
	}
	
	
	/**
	 * update the Slider title
	 * @param bool $change_alias also regenerate the alias from the new title
	 * @return true|string true on success, an error message otherwise
	 */
	public function update_title($new_title, $change_alias = false){

		$return = [];
		$new_title = stripslashes(esc_html($new_title));
		if(!empty($new_title)){
			$new_alias = $this->alias;
			if($change_alias === true){
				$new_alias = sanitize_title($new_title);
				$slider_counter = 1;
				while( ($aid = $this->alias_exists($new_alias, true)) !== false){
					if($aid === $this->id) break;
					$new_alias = sanitize_title($new_title) . $slider_counter;
					$slider_counter++;
				}
			}

			$this->database()->update($this->database()->prefix() . $this->table_slider, ['title' => $new_title, 'alias' => $new_alias], ['id' => $this->id]);

			$this->title = $this->params['title'] = $new_title;
			$this->alias = $this->params['alias'] = $new_alias;
			$this->save_params();

			$this->invalidate_group_cache();

			$return = [
				'title' => $this->title,
				'alias' => $this->alias,
			];
		}else{
			$return = [
				'title' => $this->title,
				'alias' => $this->alias,
			];
		}
		
		if(!empty($return)){
			return $return;
		}

		return false;
	}
	
	
	/**
	 * update the Slider Tags
	 * @since: 6.0
	 * @return bool
	 */
	public function update_slider_tags($slider_id, $tags){
		
		$this->validate_not_empty($slider_id, 'Slider ID');
		
		$record	  = $this->database()->get_row($this->database()->prepare("SELECT `settings` FROM ". $this->database()->prefix() . $this->table_slider ." WHERE id = %s", $slider_id), 'ARRAY_A');
		$cur_tags = [];
		
		if(!empty($tags)){	
			foreach($tags as $tag){
				$tag		= preg_replace('/ /', '-', $tag);
				$tag		= preg_replace('/[^-0-9a-zA-Z_-]/', '', $tag);
				$cur_tags[] = $tag;
			}
		}
			
		if(!isset($record['settings'])){
			$record['settings'] = [];
		}else{
			$record['settings'] = json_decode($record['settings'], true);
		}
		
		if(!isset($record['settings']['tags'])) $record['settings']['tags'] = [];
		
		$record['settings']['tags'] = $cur_tags;
		$settings					= json_encode($record['settings']);
		
		return $this->database()->update($this->database()->prefix() . $this->table_slider, ['settings' => $settings], ['id' => $slider_id]);
	}
	
	
	/**
	 * get the last Slider ID
	 * highest id in the table - used right after an insert
	 * @since: 6.0
	 * @return int
	 */
	public function get_last_slider_id(){
		
		$record = $this->database()->get_row("SELECT `id` FROM ". $this->database()->prefix() . $this->table_slider ." ORDER BY `id` DESC LIMIT 0,1", 'ARRAY_A');
		
		return (!empty($record)) ? $this->get_val($record, 'id') : -1;
	}
	
	
	/**
	 * get all slide children
	 * child slides are the extra slides a "carousel"/child layout attaches to a parent slide
	 * @return array
	 */
	public function get_slide_children($slide_id){
		$slides = $this->get_slides();
		
		if(!isset($slides[$slide_id])){
			$this->throw_error(__('Slide not found in the main slides of the slider. Maybe it', 'revslider'));
		}
		
		$slide		= $slides[$slide_id];
		$children	= $slide->get_children();
		
		return $children;
	}
	
	
	/**
	 * get array of slide names
	 * @return array slide id => title
	 */
	public function get_slide_names(){
		if(empty($this->slides)){
			$this->get_slides();
		}
		
		$names = [];
		if(!empty($this->slides)){
			foreach($this->slides as $slide){
				$id		 = $slide->get_id();
				$file	 = $slide->image_filename;	
				$title	 = $slide->get_title();
				$name	 = $title;
				$name 	.= (!empty($file)) ? ' ('. $file .')' : '';
				
				$childs	 = $slide->get_child_ids();
				
				$names[$id] = [
					'name'			 => $name,
					'arrChildrenIDs' => $childs,
					'title'			 => $title
				];
			}
		}
		
		return $names;
	}
	
	
	/**
	 * duplicate slider in datatase
	 * copies the slider row only; the caller duplicates the slides and fills $this->map with the id mapping
	 * @param string|false $title  title of the copy, false derives it from the original
	 * @param string|false $prefix prefix for the generated alias
	 * @return int|false the new slider id
	 */
	private function duplicate_slider($title = false, $prefix = false){
		
		$old_slider_id = $this->id;
		
		$select = $this->database()->prepare("SELECT title, alias, params, type, settings FROM ". $this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDER ." WHERE id = %s", [$this->id]);
		$this->database()->query("INSERT INTO ". $this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDER ." (title, alias, params, type, settings) (".$select.")");
		
		//update the slider title and alias to a new one
		$slider_last_id	= $this->database()->insert_id();
		$params			= $this->params;
		$this->validate_not_empty($slider_last_id, 'Slider ID');
		$slider_counter = $this->get_slider_count(); //get last slider number
		
		if($title === false){
			$slider_counter++;
			$new_title = 'Slider'.$slider_counter;
			$new_alias = 'slider'.$slider_counter;
		}else{
			$new_title = ($prefix !== false) ? sanitize_text_field($title.' '.$this->get_val($params, 'title')) : sanitize_text_field($title);
			$new_alias = ($prefix !== false) ? sanitize_title($title.' '.$this->get_val($params, 'title')) : sanitize_title($title);
			
			//check if alias exists
			$c_title = $new_title;
			$c_alias = $new_alias;
			while($this->alias_exists($c_alias)){
				$c_title = $new_title . $slider_counter;
				$c_alias = $new_alias . $slider_counter;
				$slider_counter++;
			}
			$new_title = $c_title;
			$new_alias = $c_alias;
		}
		
		$params['title']	 = $new_title;
		$params['alias']	 = $new_alias;
		$params['shortcode'] = '[rev_slider alias="'. $new_alias .'"]';

		$this->database()->update(
			$this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDER,
			[
				'title'	 => $new_title,
				'alias'	 => $new_alias,
				'params' => json_encode($params),
				'type'	 => ''
			],
			['id' => $slider_last_id]
		);
		
		
		//duplicate slides and add them to the new Slider
		$slides = $this->database()->get_results($this->database()->prepare("SELECT * FROM ". $this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDES ." WHERE slider_id = %s", $this->id), 'ARRAY_A');
		foreach($slides ?? [] as $slide){
			$slide['slider_id'] = $slider_last_id;
			$slide_id = $slide['id'];
			unset($slide['id']);
			$this->database()->insert($this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDES, $slide);
			
			$this->map[$slide_id] = $this->database()->insert_id();
		}
		
		//update actions
		$slides = $this->database()->get_results($this->database()->prepare("SELECT * FROM ". $this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDES ." WHERE slider_id = %s", $slider_last_id), 'ARRAY_A');
	
		foreach($slides ?? [] as $slide){
			$c_slide	= new Slide();
			$c_slide->init_by_data($slide);
			$layers		= $c_slide->get_layers();
			
			$did_change	= false;
			foreach($layers ?? [] as $key => $value){
				$actions = $this->get_val($value, ['actions', 'action']);
				
				if(!empty($actions)){
					foreach($actions as $a_k => $action){
						$jtsval = $this->get_val($action, 'jump_to_slide');
						if(isset($this->map[$jtsval])){
							$this->set_val($layers, [$key, 'actions', 'action', $a_k, 'jump_to_slide'], $this->map[$jtsval]);
							$did_change = true;
						}
					}
				}
			}

			if($did_change === true){
				$my_layers	= json_encode($layers);
				$data		= ['layers' => (empty($my_layers)) ? stripslashes(json_encode($layers)) : $my_layers];
				$this->database()->update($this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDES, $data, ['id' => $slide['id']]);
			}
		}
		
		//change the javascript api ID to the correct one
		$c_slider = new \ThemePunch\SR\Core\Admin\Import();
		$c_slider->init_by_id($slider_last_id);
		$c_slider->update_modal_ids([$this->id => $slider_last_id], $this->map, [], [$this->alias => $new_alias]);
		$c_slider->update_css_and_javascript_ids($old_slider_id, $slider_last_id, $this->map);
		$c_slider->update_color_ids($this->map);
		
		do_action('revslider_duplicate_slider', $slider_last_id, $old_slider_id, $slides, $this);
		
		return $slider_last_id;
	}
	
	
	/**
	 * update the modal id and the alias in the layer actions
	 * after a duplicate/import the ids changed, so every layer action pointing at a slider or slide
	 * (open_modal, callSlide, playScene, video controls, …) has to be rewritten to the new ids
	 * @return array the slides with their rewritten actions
	 **/
	public function update_modal_ids($slider_ids, $slide_ids, $slides = [], $slider_aliases = []){
		if(empty($slides)) $slides = $this->get_slides();
		if(empty($slides)) return;
		$static_slide	= $this->get_static_slide(); 
		if($static_slide !== false) $slides[] = $static_slide; //add static slide here
		
		foreach($slides ?? [] as $slide){
			if(version_compare($slide->get_param('version', '1.0.0'), '6.0.0', '<')) continue;
			
			$params	 = $slide->get_params();
			$actions = $this->get_val($params, 'actions', []);
			/**
			 * open_modal
			 * 	- target = slider alias
			 * 	- msl = #slideid
			 * close_modal
			 * 	- target = slider alias (if it is unset, empty , current or all , than you leave it as it is)
			 * callSlide:
			 * 	- target slide ID or keyword
			 * playScene, toggleScenes 
			 * 	- target layer ID or slide ID:layer ID
			 * 	- action.sid slide ID
			 * toggleClass
			 * 	- target layer ID SO IGNORE!
			 * simulate
			 * 	- target layer ID SO IGNORE!
			 * mute_video, unmute_video, toggle_mute_video
			 * 	- target layer ID or slide ID:layer ID
			 * toggle_video, start_video, stop_video
			 * 	- target layer ID or slide ID:layer ID
			 **/
			foreach($actions ?? [] as $ak => $a){
				$check = ['target', 'sid', 'msl', 'targetid']; //'src', <- src is never a slide ID
				foreach($check as $c){
					$values = $this->get_val($a, $c, []);
					if(empty($values)) continue;

					$string = (!is_array($values)) ? true : false;
					$action = $this->get_val($a, 'a');
					if($string) $values = (array)$values;

					foreach($values ?? [] as $vk => $v){
						//special values start
						if($action === 'callSlide' && $c === 'target'){ //target is slideid here, not layerid
							$_v = intval($v);
							if(!isset($slide_ids[$_v])) continue;
							$values[$vk] = $slide_ids[$_v];
							continue;
						}elseif($action === 'open_modal' && $c === 'target'){ //in target is the old slider alias
							$_v = $this->get_val($a, 'target', []);
							if(empty($_v) || !isset($slider_aliases[$_v])) continue;
							$values[$vk] = $slider_aliases[$_v];
						}elseif($action === 'open_modal' && $c === 'targetid'){ //in target_id is the old slideid
							if(!isset($slider_ids[$v])) continue;
							$values[$vk] = $slider_ids[$v];
						}elseif($action === 'open_modal' && $c === 'msl'){
							//msl has slideid with a pre # or with a pre rs-
							if (strpos($v, '#') === 0){
								$_sid = str_replace('#', '', $v);
								if(!isset($slide_ids[$_sid])) continue;
								$values[$vk] = '#'.$slide_ids[$_sid];
							}
							if (strpos($v, 'rs-') === 0){
								$_sid = str_replace('rs-', '', $v);
								if(!isset($slide_ids[$_sid])) continue;
								$values[$vk] = 'rs-'.$slide_ids[$_sid];
							}
						}elseif($action === 'close_modal' && $c === 'target'){ //in target is the old slider alias
							$_v = $this->get_val($a, 'target', []);
							if(empty($_v) || !isset($slider_aliases[$_v])) continue;
							$values[$vk] = $slider_aliases[$_v];
						}elseif( ($action === 'toggleScenes' || $action === 'playScene') && $c === 'sid'){
							$_v = intval($v);
							if(!isset($slide_ids[$_v])) continue;
							$values[$vk] = $slide_ids[$_v];
						}else{
						//special values end
							if(empty($v)||strpos($v, ':') === false) continue;
							$ids = explode(':', $v);
							$sid = intval($ids[0]);
							if($sid === 0) continue; //not a number
							if(!isset($slide_ids[$sid])) continue;
							$ids[0] = $slide_ids[$sid];

							$values[$vk] = implode(':', $ids);
						}
					}

					if($string) $values = $values[0];

					$this->set_val($actions[$ak], $c, $values);
				}
			}

			$parent_id = $this->get_val($params, 'parentId', $this->get_val($params, 'parentID', false));
			if(!in_array($parent_id, [false, ''], true) && isset($slide_ids[$parent_id])){
				$this->set_val($params, 'parentId', $slide_ids[$parent_id]);
			}

			$params['actions'] = $actions;
			$slide->set_params($params);
			$slide->save_params();
			
			$layers = $slide->get_layers();
			
			if(empty($layers)) continue;
			$change = false;
			foreach($layers ?? [] as $lk => $layer){
				foreach($layer['addOns'] ?? [] as $slug => $addon){
					if(empty($addon['slideStates']) || !is_array($addon['slideStates'])) continue;
					$bindings = [];
					foreach($addon['slideStates'] as $sid => $state) $bindings[$slide_ids[$sid] ?? $sid] = $state;
					if($bindings !== $addon['slideStates']){
						$layers[$lk]['addOns'][$slug]['slideStates'] = $bindings;
						$change = true;
					}
				}
				$actions = $this->get_val($layer, ['actions', 'action'], []);
			
				if(empty($actions)) continue;
				
				foreach($actions ?? [] as $ak => $a){
					if($this->get_val($a, 'action', '') !== 'open_modal') continue;

					$v = intval($this->get_val($a, 'openmodalId', 0)); //only openmodal is set (alias), openmodalId is not set!
					
					if(!isset($slider_ids[$v])) continue;
				
					$slider_alias = $this->get_alias_by_id($slider_ids[$v]);
					$change = true;
					$this->set_val($layers, [$lk, 'actions', 'action', $ak, 'openmodalId'], $slider_ids[$v]);
					$this->set_val($layers, [$lk, 'actions', 'action', $ak, 'openmodal'], $slider_alias);
					
					$sv = $this->get_val($a, 'modalslide', 0);
					if($sv !== 0){
						$_sv = intval(str_replace('rs-', '', $sv));
						if($_sv > 0 && isset($slide_ids[$_sv])){
							$this->set_val($layers, [$lk, 'actions', 'action', $ak, 'modalslide'], 'rs-'.$slide_ids[$_sv]);
						}
					}
				}
			}
			
			if($change){
				$slide->set_layers_raw($layers);
				$slide->save_layers();
			}
		}

		return $slides;
	}
	
	/**
	 * does a slider with this id exist?
	 * @return bool
	 */
	public function check_slider_id($id){
		
		$slider	= $this->database()->get_row($this->database()->prepare("SELECT * FROM ". $this->database()->prefix() . $this->table_slider ." WHERE id = %d", $id), 'ARRAY_A');
		
		return !empty($slider);
	}
	
	/**
	 * Check if an alias exists in database
	 * @return bool
	 */
	public function check_alias($alias){
		
		$add	= (!empty($this->id)) ? $this->database()->prepare(" AND id != %s", [$this->id]) : '';
		$slider	= $this->database()->get_row($this->database()->prepare("SELECT * FROM ". $this->database()->prefix() . $this->table_slider ." WHERE alias = %s", $alias).$add, 'ARRAY_A');
		
		return !empty($slider);
	}
	
	
	/**
	 * Create a blank Slider
	 **/
	public function create_blank_slider(){
		
		$title		= 'Slider ';
		$alias		= 'slider-';
		$counter	= 1;
		$new_alias	= $alias.$counter;
		
		while($this->alias_exists($new_alias)){
			$counter++;
			$new_alias = $alias.$counter;
		}
		
		$title .= $counter;
		
		//insert slider to database
		$slider_data = [
			'title'		=> $title,
			'alias'		=> $new_alias,
			'params'	=> json_encode([], JSON_FORCE_OBJECT),
			'settings'	=> json_encode(['version' => RS_REVISION], JSON_FORCE_OBJECT),
			'type'		=> ''
		];
		
		$result		= $this->database()->insert($this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDER, $slider_data);
		$slider_id	= ($result) ? ['id' => $this->database()->insert_id(), 'alias' => $new_alias] : false;
		
		return $slider_id;
	}
	
	/**
	 * write the editor's payload back: title, alias, settings and params of one slider
	 * @return int|string the slider id, or an error message
	 */
	public function save_slider_v7($slider_id, $settings, $title, $alias, $version = \RS_REVISION){
		$settings	= $this->json_decode_slashes($settings);
		$title		= (empty($title)) ? $this->get_val($settings, 'title') : $title;
		$alias		= (empty($alias)) ? $this->get_val($settings, 'alias') : $alias;
		$exists		= $this->check_id_v7($slider_id);
		
		//dont allow css and javascript from users other than administrator
		if($this->current_user_can_not('administrator')){
			if(isset($settings['codes']) && isset($settings['codes']['css'])) unset($settings['codes']['css']);
			if(isset($settings['codes']) && isset($settings['codes']['js'])) unset($settings['codes']['js']);
		}
		
		if($exists){
			$this->init_by_id($slider_id);

			if($this->current_user_can_not('administrator')){
				//check for js and css, add it to $params
				$settings['codes'] = [
					'css' => $this->get_param(['codes', 'css'], ''),
					'js' => $this->get_param(['codes', 'js'], '')
				];
			}
		}

		//insert slider to database
		$slider_data = [
			'title'		=> $title,
			'alias'		=> $alias,
			'params'	=> json_encode($settings),
			'settings'	=> json_encode(['version' => $version], JSON_FORCE_OBJECT),
			'type'		=> ''
		];

		if(!$exists){ //create slider
			$slider_data['id'] = $slider_id;
			$result		= $this->database()->insert($this->database()->prefix() . $this->table_slider , $slider_data);
			$slider_id	= ($result) ? $this->database()->insert_id() : false;
		}else{ //update slider
			$this->database()->update($this->database()->prefix() . $this->table_slider, $slider_data, ['id' => $slider_id]);
		}
		
		if($this->context()->get('preview_mode') === false){ //delete preview mode data
			$this->database()->delete($this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDES_PREVIEW, ['slider_id' => $slider_id]);
			$this->database()->delete($this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDER_PREVIEW, ['id' => $slider_id]);
		}

		do_action('revslider_save_slider_v7', $slider_id);

		return $slider_id;
	}

	/**
	 * partial save used by the editor's advanced panels - merges $data into the stored params
	 * @return int|string the slider id, or an error message
	 */
	public function save_slider_advanced($slider_id, $data){
		$this->init_by_id($slider_id);
		if($this->inited === false) return false;
		
		$params = $this->get_val($data, 'params', []);
		$params = $this->json_decode_slashes($params);
		if(!empty($params)) $this->update_params($params);
		
		$settings = $this->get_val($data, 'settings', []);
		$settings = $this->json_decode_slashes($settings);
		if(!empty($settings)) $this->update_settings($settings);
		
		return true;
	}
	
	/**
	 * save all current params to database
	 * @return void
	 **/
	public function save_params(){

		$this->database()->update($this->database()->prefix() . $this->table_slider, ['params' => json_encode($this->params)], ['id' => $this->id]);
		$this->invalidate_group_cache();
	}

	/**
	 * update some params in the slider
	 * @param bool $replace true overwrites the params, false merges recursively
	 * @return void
	 */
	public function update_params($update, $replace = false){

		$this->params = ($replace) ? $update : array_replace_recursive($this->params, $update);

		$this->database()->update($this->database()->prefix() . $this->table_slider, ['params' => json_encode($this->params)], ['id' => $this->id]);
	}
	
	
	/**
	 * update some settings in the slider
	 * @return void
	 */
	public function update_settings($update){

		$this->settings = array_replace_recursive($this->settings, $update);

		$this->database()->update($this->database()->prefix() . $this->table_slider, ['settings' => json_encode($this->settings)], ['id' => $this->id]);
	}
	
	
	/**
	 * get array of slides numbers by id's
	 * RevSliderSlider::getSlidesNumbersByIDs();
	 * @param bool $published count only published slides
	 * @return array slider id => number of slides
	 */
	public function get_slide_numbers_by_id($published = false){
		$numbers = [];
		$counter = 0;
		
		if(empty($this->slide)){
			$this->get_slides($published);
		}
		
		if(empty($this->arr_slides)){
			foreach($this->slides as $slide){
				$counter++;
				$id				= $slide->get_id();
				$numbers[$id]	= $counter;
			}
		}
		
		return $numbers;
	}
	
	
	/**
	 * get sliders array - function don't belong to the object!
	 * every slider as an initialized object; folders and templates are excluded unless asked for
	 * @param bool $templates include template sliders
	 * @param int  $page      0 for all, otherwise the page to return
	 * @return RevSliderSlider[]
	 */
	public function get_sliders($templates = false, $page = 0){
		global $SR_GLOBALS;
		
		$SR_GLOBALS['data_init'] = false;
		$sliders	= [];
		$page		= intval($page);
		$limit		= '';

		if($page > 0){
			$start	= 50 * ($page - 1);
			$limit	= $this->database()->prepare(' LIMIT %d, 50', $start);
		}

		$slider_data = $this->database()->get_results("SELECT * FROM ".$this->database()->prefix() . $this->table_slider ." WHERE `type` NOT IN ('folder','trash') ORDER BY `id` ASC".$limit, 'ARRAY_A'); //WHERE `type` = '' OR `type` IS NULL
		if(!empty($slider_data)){
			foreach($slider_data ?? [] as $data){
				$slider = new self();
				$slider->init_by_data($data);
				$sliders[] = $slider;
			}
		}
		
		$SR_GLOBALS['data_init'] = true;
		
		return $sliders;
	}

	/**
	 * get sliders shortlist array 
	 * the raw rows without params - cheap enough to load for every slider on an overview page
	 * @return array slider id => row
	 */
	public function get_sliders_short_list(){
		
		$sliders = $this->database()->get_results("SELECT id, title, alias, settings FROM " . $this->database()->prefix() . $this->table_slider . " WHERE `type` NOT IN ('folder','trash') ORDER BY `id` ASC", 'ARRAY_A');
		$sliders = apply_filters('sr_get_sliders_short_list', $sliders);
		$_sliders = [];

		foreach($sliders ?? [] as $slider){
			$_sliders[$slider['id']] = $slider;
		}

		return $_sliders; 
	}
	
	
	/**
	 * the sliders shown in the WordPress admin menu
	 * @return array
	 */
	public function get_slider_for_admin_menu(){
		global $SR_GLOBALS;
		
		$SR_GLOBALS['data_init'] = false;
		$sliders = $this->get_sliders();
		$SR_GLOBALS['data_init'] = true;
		
		$short = [];
		if(!empty($sliders)){
			foreach($sliders as $slider){
				$id = $slider->get_id();
				
				$short[$id] = ['title' => $slider->get_title(), 'alias' => $slider->get_alias()];
			}
		}
		
		return $short;
	}
	
	/**
	 * attach already loaded slide objects to this slider instead of querying them
	 * @return void
	 */
	public function set_slides($slides){
		$this->slides = [];
		foreach($slides ?? [] as $slide){
			$rslide = new Slide();
			$rslide->init_by_data($slide);
			$this->slides[] = $rslide;
		}
	}
	
	/**
	 * the slide rows straight from the database, without building RevSliderSlide objects
	 * @param bool $layers also select the (large) layers column
	 * @return array
	 */
	public function get_slides_raw($layers = false){
		$slide = new Slide();
		
		return $slide->get_slides_raw($this->id, $layers);
	}
	
	/**
	 * same for a given list of slide ids
	 * @return array
	 */
	public function get_slides_by_slide_ids_raw($slide_ids, $layers = false){
		$slide = new Slide();
		
		return $slide->get_slides_by_slide_ids_raw($slide_ids, $layers);
	}

	/**
	 * get slides from gallery
	 * force from gallery - get the slide from the gallery only
	 * the stored slides of this slider, regardless of its source setting
	 * @param bool $published skip unpublished slides
	 * @param bool $allwpml   ignore the WPML language filter
	 * @param bool $first     stop after the first slide
	 * @return RevSliderSlide[]
	 */
	public function get_slides($published = false, $allwpml = false, $first = false){
		$cache_key = $this->get_wp_cache_key('get_slides_by_slider_id', [$this->id, $published, $allwpml, $first, $this->init_layer]);
		$this->slides = wp_cache_get($cache_key, self::CACHE_GROUP);

		if(!empty($this->slides)) return apply_filters('revslider_get_slides', $this->slides, $published, $allwpml, $first);

		$slide			= new Slide();
		$this->slides	= $slide->get_slides_by_slider_id($this->id, $published, $allwpml, $first, $this->init_layer);
		wp_cache_set($cache_key, $this->slides, self::CACHE_GROUP, self::CACHE_TTL);

		return apply_filters('revslider_get_slides', $this->slides, $published, $allwpml, $first);
	}

	/**
	 * same as get_slides(), but allows for temporary modifications, i.e. changing customThumbUrl
	 * should only be used at frontend stage, so that the data saved is not modified inproperly
	 * @param bool $do_shortcodes run the layer texts through do_shortcode()
	 * @return RevSliderSlide[]
	 */
	public function get_slides_modified($published = false, $allwpml = false, $first = false, $do_shortcodes = true){
		$slides = $this->get_slides($published, $allwpml, $first);

		foreach($slides ?? [] as $key => $slide){
			$thumb_url = $this->get_thumb_url($slide);
			if(!empty($thumb_url)) $slide->set_param(['thumb', 'src'], $thumb_url);

			if($do_shortcodes){
				//parse all slide and layers shortcodes
				if($slide->get_param(['seo', 'set'], false) == true){
					if($slide->get_param(['seo', 'type'], 'regular') !== 'slide'){
						$_link = $slide->get_param(['seo', 'link'], '');
						$link = $this->resolve_basic_metas($this->environment()->expand_content($_link));
						if($_link !== $link) $slide->set_param(['seo', 'link'], $link);
					}
				}

				$layers		= $slide->get_layers();
				foreach($layers ?? [] as $_key => $layer){
					if(!in_array($this->get_val($layer, 'type', 'text'), ['image', 'svg', 'column', 'shape'], true)){
						//parse text shortcodes
						$text = $this->get_val($layer, ['content', 'text']);
						$_text = $this->resolve_basic_metas($this->environment()->expand_content(stripslashes($text)));
						if($text !== $_text) $_text = '#srfshcd#'.$_text;
						$this->set_val($layers[$_key], ['content', 'text'], $_text);

						//parse toggle text shortcodes
						$text_toggle = $this->get_val($layer, ['toggle', 'text']);
						$_text_toggle = $this->resolve_basic_metas($this->environment()->expand_content(stripslashes($text_toggle)));
						if($text_toggle !== $_text_toggle) $_text_toggle = '#srfshcd#'.$_text_toggle;
						$this->set_val($layers[$_key], ['toggle', 'text'], $_text_toggle);

						//parse actions shortcodes
						$action	= $this->get_val($layer, ['actions', 'action'], []);
						foreach($action ?? [] as $a_k => $act){
							$action_type = apply_filters('rs_action_type', $this->get_val($act, 'action'));
							$link_type = apply_filters('rs_action_link_type', $this->get_val($act, 'link_type', ''));

							if(in_array($action_type, ['menu', 'link'], true)){
								if($action_type === 'link' && $link_type === 'jquery') continue;
								$_link = ($action_type === 'menu') ? 'menu_link' : 'image_link';
								$link = $this->get_val($act, $action_type, '');
								$link = $this->resolve_basic_metas($this->environment()->expand_content($link));
								$this->set_val($layers[$_key], ['actions', 'action', $a_k, $_link], $link);
							}
						}
					}
					$slide->set_layers_raw($layers);
				}
			}
			
			$slides[$key] = $slide;
		}
		
		return $slides;
	}
	
	/**
	 * get slides for export
	 * @return array
	 */
	public function get_slides_for_export(){
		$export			= [];
		$slides			= $this->get_slides(false, true);
		$static_slide	= $this->get_static_slide(); 
		if($static_slide !== false) $slides[] = $static_slide; //add static slide here
		
		foreach($slides ?? [] as $slide){
			$export[] = [
				'id'			=> $slide->get_id(),
				'params'		=> $slide->get_params_for_export(),
				'slide_order'	=> $slide->get_order(),
				'layers'		=> $slide->get_layers_for_export(),
				'settings'		=> $slide->get_settings(),
				'static'		=> $slide->is_static_slide()
			];
		}
		
		return apply_filters('revslider_get_slides_for_export', apply_filters('revslider_getSlidesForExport', $export));
	}
	
	/**
	 * get array of sliders with slides, short, assoc.
	 * @param string $filter 'all', or a slider type to limit the result to
	 * @return array
	 */
	public function get_sliders_with_slides_short($filter = 'all'){
		$output	 = [];
		$sliders = $this->get_sliders_short(null, $filter);
		
		if(!empty($sliders)){
			foreach($sliders as $sid => $slider_name){
				$slider = new self();
				$slider->init_by_id($sid);
				$is_posts = $slider->is_posts();
				
				if($filter == 'posts' && $is_posts == false) continue; //filter by gallery only
				if($filter == 'gallery' && $is_posts == true) continue;
				if($filter == 'template' && $is_posts == false)	continue; //filter by template type
				
				$slides = $slider->get_slides_from_gallery_short();
				foreach($slides ?? [] as $slide_id => $slide_name){
					$output[$slide_id] = $slider_name.', '.$slide_name;
				}
			}
		}
		
		return $output;
	}
	
	
	/**
	 * get slide id and slide title from gallery
	 * @return array
	 */
	public function get_slides_from_gallery_short(){
		$counter = 0;
		$output	 = [];
		$slides	 = $this->get_slides();
		
		foreach($slides ?? [] as $slide){
			$id			 = $slide->get_id();
			$name		 = 'Slide '.$counter;
			$title		 = $slide->get_param('title', '');
			$output[$id] = (!empty($title)) ? $name.' - ('.$title.')' : $name;
			
			$counter++;
		}
		
		return $output;
	}
	
	
	/**
	 * get slides for output
	 * one level only without children
	 * @return RevSliderSlide[]
	 */
	public function get_slides_for_output($published = false, $lang = 'all'){
		$parent_slides = $this->get_parent_slides($published, $lang);
		
		return $parent_slides;
	}

	
	/**
	 * get the parent Slides if the Slide has any
	 * @return RevSliderSlide[]
	 **/
	public function get_parent_slides($published, $lang){
		apply_filters('revslider_get_parent_slides_pre', $lang, $published, [], $this);
		
		$parent_slides = $this->get_slides($published);
		
		return apply_filters('revslider_get_parent_slides_post', $parent_slides, $published, [], $this);
	}

	/**
	 * the platform's content source for post/product sourced sliders. WordPress' implementation
	 * is the default; another platform binds its class via the revslider_content_source_class
	 * filter before the first slider renders
	 * @since 7.2.1
	 * @return \ThemePunch\SR\Ports\ContentSource
	 */
	public function content_source(){
		if($this->_content_source === null) $this->_content_source = $this->create_content_source($this);
		return $this->_content_source;
	}

	/**
	 * get all stream/post data
	 * resolves the configured source (posts, products or a social stream) into slide data
	 * @return array
	 **/
	public function get_stream_data(){
		$stream_data = [];
		
		if($this->is_posts()){
			$stream_data = $this->get_slides_data_from_posts_v7();
		}elseif($this->is_stream()){
			$stream_data = $this->get_slides_data_from_stream_v7();
		}
		
		return $stream_data;
	}
	
	
	/**
	 * get array of slider id -> title
	 * @param int|null $exclude_id leave this slider out
	 * @param string   $filter     'all', or a slider type
	 * @return array
	 */		
	public function get_sliders_short($exclude_id = null, $filter = 'all'){
		$sliders	= $this->get_sliders();
		$short		= [];
		if(!empty($sliders)){
			foreach($sliders as $slider){
				$id			= $slider->get_id();
				$from_post	= $slider->is_posts();
				
				//filter by gallery only
				if($filter == 'posts' && $from_post == false) 	 continue;
				if($filter == 'gallery' && $from_post == true) 	 continue;
				if($filter == 'template' && $from_post == false) continue; //filter by template type
				if(!empty($exclude_id) && $exclude_id == $id)	 continue; //filter by except
				
				$short[$id] = $slider->get_title();
			}
		}
		
		return $short;
	}
	
	
	/**
	 * get the maximum order
	 * highest slide_order of this slider, so a new slide can be appended
	 * @return int
	 */
	public function get_max_order(){
		
		$record = $this->database()->get_row($this->database()->prepare("SELECT slide_order FROM ". $this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDES ." WHERE slider_id = %d ORDER BY slide_order DESC LIMIT 0,1", $this->id), 'ARRAY_A');
		
		return (empty($record)) ? 0 : $this->get_val($record, 'slide_order');
	}
	
	
	/**
	 * get the slider type
	 */
	public function get_type(){
		$type		= 'gallery';
		$is_stream	= $this->is_stream();
		
		if($this->is_posts() == true){
			$type = (in_array($this->get_param('sourcetype', 'gallery'), ['woocommerce', 'woo'], true)) ? 'woocommerce' : 'posts';
			if($this->is_specific_posts()) $type = 'specific_posts';
		}elseif($is_stream !== false){
			$type = (in_array($is_stream, ['facebook', 'twitter', 'instagram', 'flickr', 'youtube', 'vimeo'])) ? $is_stream : $type;
		}
		
		return $type;
	}
	
	
	/**
	 * copy slide from one Slider to the given Slider ID
	 * @since: 5.0
	 * @return int|false the new slide id
	 */
	public function copy_slide_to_slider($data){

		$slider_id		= intval($this->get_val($data, 'slider_id'));
		$slide_id		= intval($this->get_val($data, 'slide_id'));
		$add_to_slider	= $this->database()->get_row($this->database()->prepare("SELECT * FROM ". $this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDER ." WHERE id = %s", $slider_id), 'ARRAY_A'); //check if ID exists
		
		if(empty($add_to_slider)) return __('Slide could not be duplicated', 'revslider');
		
		//get last slide in slider for the order
		$slide_order	= $this->database()->get_row($this->database()->prepare("SELECT * FROM ". $this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDES ." WHERE slider_id = %s ORDER BY slide_order DESC", $slider_id), 'ARRAY_A');
		$slide_to_copy	= $this->database()->get_row($this->database()->prepare("SELECT * FROM ". $this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDES ." WHERE id = %s", $slide_id), 'ARRAY_A');
		
		if(empty($slide_to_copy)) return __('Slide could not be duplicated', 'revslider');
		
		unset($slide_to_copy['id']); //remove the ID of the Slide, as it will be a new Slide
		$slide_to_copy['slider_id']		= $slider_id; //set the new Slider ID to the Slide
		$slide_to_copy['slide_order']	= (empty($slide_order)) ? 1 : $this->get_val($slide_order, 'slide_order') + 1; //set the next slide order, to set slide to the end
		
		$response = $this->database()->insert($this->database()->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDES, $slide_to_copy);
		
		if(isset($slide_id) && $response !== false){
			$this->map[$slide_id] = $this->database()->insert_id();
		}
		
		return ($response === false) ? __('Slide could not be duplicated', 'revslider') : true;
	}

	/**
	 * get slider' static slide
	 * 
	 * @since: 6.4.6
	 * @return false | RevSliderSlide
	 */
	public function get_static_slide(){
		$slider_id = $this->get_id();
		if (empty($slider_id)) return false;
		
		if ($this->_static_slide instanceof Slide && $this->_static_slide->get_slider_id() == $slider_id) 
			return $this->_static_slide;

		$slide = new Slide();
		$is_init = $slide->init_static_slide_by_slider_id($slider_id);
		if (!$is_init) return false;
		
		$this->_static_slide = $slide;
		return $this->_static_slide;
	}
	
	
	/**
	 * get all used fonts in the current Slider
	 * @param bool $full also return the variants and subsets, not just the family names
	 * @since: 5.1.0
	 * @return array
	 */
	public function get_used_fonts($full = false){
		$gf			= [];
		$sl			= new Slide();
		$mslides	= $this->get_slides(true);
		
		$static_slide = $this->get_static_slide();
		if($static_slide !== false){
			$mslides = array_merge($mslides, [$static_slide]);
		}
		
		if(!empty($mslides)){
			foreach($mslides as $ms){
				$mf = $ms->get_used_fonts($full);
				
				if(!empty($mf)){
					foreach($mf as $mfk => $mfv){
						if(!isset($gf[$mfk])){
							$gf[$mfk] = $mfv;
						}else{
							foreach($mfv['variants'] as $mfvk => $mfvv){
								$gf[$mfk]['variants'][$mfvk] = true;
							}
						}
						$gf[$mfk]['slide'][] = ['id' => $ms->get_id(), 'title' => $ms->get_title()];
					}
				}
			}
		}
		
		return apply_filters('revslider_getUsedFonts', $gf);
	}
	
	/** delegates to the platform's content source (WordPress: post/product sourcing) - body moved to wordpress/includes/platform/post-content-source.php @since 7.2.1 */
	public function get_slides_data_from_posts_v7(){
		return $this->content_source()->get_slides_data();
	}

	/** delegates to the platform's content source (WordPress: post/product sourcing), empty where a host does not source posts this way @since 7.2.1 */
	public function get_slides_data_from_posts($published = false, $lang = 'all'){
		return $this->legacy_source_call($this->content_source(), 'get_slides_data_from_posts', [$published, $lang], []);
	}
	
	/** delegates to the platform's content source (WordPress: post/product sourcing), empty where a host does not source posts this way @since 7.2.1 */
	public function get_post_data($published = false){
		return $this->legacy_source_call($this->content_source(), 'get_post_data', [$published], []);
	}
	
	/** delegates to the platform's content source (WordPress: post/product sourcing), empty where a host does not source posts this way @since 7.2.1 */
	public function get_related_posts(){
		return $this->legacy_source_call($this->content_source(), 'get_related_posts', [], []);
	}
	
	
	/** delegates to the platform's content source (WordPress: post/product sourcing) - body moved to wordpress/includes/platform/post-content-source.php @since 7.2.1 */
	public function get_popular_posts($max_posts = false){
		return $this->content_source()->get_popular_posts($max_posts);
	}
	
	
	/** delegates to the platform's content source (WordPress: post/product sourcing) - body moved to wordpress/includes/platform/post-content-source.php @since 7.2.1 */
	public function get_latest_posts($max_posts = false){
		return $this->content_source()->get_latest_posts($max_posts);
	}
	
	
	/** delegates to the platform's content source (WordPress: post/product sourcing), empty where a host does not source posts this way @since 7.2.1 */
	public function get_next_previous_post(){
		return $this->legacy_source_call($this->content_source(), 'get_next_previous_post', [], []);
	}


	/** delegates to the platform's content source (WordPress: post/product sourcing), empty where a host does not source posts this way @since 7.2.1 */
	public function get_tax_query(){
		return $this->legacy_source_call($this->content_source(), 'get_tax_query', [], false);
	}
	
	/**
	 * fetch the configured social stream (facebook, instagram, flickr, vimeo, youtube) and normalize it
	 * into the common slide data structure
	 * @return array
	 */
	public function _get_stream_data(){
		$posts		 = [];
		$max_allowed = 999999;
		$sourcetype	 = $this->get_param(['source', 'type'], 'gallery');
		$additions	 = [];
		$max_posts	 = 0;
		
		ob_start();
		switch($sourcetype){
			case 'facebook':
				$facebook = Registry::instance()->get(External\Facebook::class);
				$facebook->setTransientSec($this->get_param(['source', 'facebook', 'transient'], '1200'));
				if($this->get_param(['source', 'facebook', 'typeSource'], 'timeline') == 'album'){
					$posts = $facebook->get_photo_set_photos(
						$this->id,
						$this->get_param(['source', 'facebook', 'appId']),
						$this->get_param(['source', 'facebook', 'album']),
						$this->get_param(['source', 'facebook', 'count'], 8)
					);
					$additions['fb_type']	 = 'album';
				}else{
					$posts = $facebook->get_photo_feed(
						$this->id,
						$this->get_param(['source', 'facebook', 'appId']),
						$this->get_param(['source', 'facebook', 'page_id']),
						$this->get_param(['source', 'facebook', 'count'], 8)
					);
					$additions['fb_type'] = 'timeline';
				}
				
				$max_posts	 = $this->get_param(['source', 'facebook', 'count'], '25');
				$max_allowed = 25;
			break;
			case 'twitter':
				$this->throw_error(__('Twitter Stream is no longer available, for further information, please check https://www.sliderrevolution.com/faq/why-are-we-dropping-twitter-api-integration/', 'revslider'));
			break;
			case 'instagram':
				$instagram	= Registry::instance()->get(External\Instagram::class);
				$instagram->setTransientSec($this->get_param(['source', 'instagram', 'transient'], '1200'));
				$posts		= $instagram->get_public_photos($this->get_id(), $this->get_param(['source', 'instagram', 'token']), $this->get_param(['source', 'instagram', 'count'], '33'));
				$max_posts	= $this->get_param(['source', 'instagram', 'count'], '33');
				$profile	= $instagram->get_user_profile($this->get_param(['source', 'instagram', 'token']));
				$additions['instagram_user'] = isset($profile['username']) ? $profile['username'] : '';
				$max_allowed = 33;
			break;
			case 'flickr':
				$flickr = new \ThemePunch\SR\Core\External\Flickr($this->get_param(['source', 'flickr', 'apiKey']), $this->get_param(['source', 'flickr', 'transient'], '1200'));
				switch($this->get_param(['source', 'flickr', 'type'])){
					case 'publicphotos':
						$user_id = $flickr->get_user_from_url($this->get_param(['source', 'flickr', 'userURL']));
						$posts	 = $flickr->get_public_photos($user_id, $this->get_param(['source', 'flickr', 'count']));
					break;
					case 'gallery':
						$gallery_id	= $flickr->get_gallery_from_url($this->get_param(['source', 'flickr', 'galleryURL']));
						$posts		= $flickr->get_gallery_photos($gallery_id, $this->get_param(['source', 'flickr', 'count']));
					break;
					case 'group':
						$group_id	= $flickr->get_group_from_url($this->get_param(['source', 'flickr', 'groupURL']));
						$posts		= $flickr->get_group_photos($group_id, $this->get_param(['source', 'flickr', 'count']));
					break;
					case 'photosets':
						$posts = $flickr->get_photo_set_photos($this->get_param(['source', 'flickr', 'photoSet']), $this->get_param(['source', 'flickr', 'count']));
					break;
				}
				$max_posts = $this->get_param(['source', 'flickr', 'count'], '99');
			break;
			case 'youtube':
				$channel_id	 = $this->get_param(['source', 'youtube', 'channelId']);
				$youtube	 = new \ThemePunch\SR\Core\External\Youtube($this->get_param(['source', 'youtube', 'api']), $channel_id, $this->get_param(['source', 'youtube', 'transient'], '1200'));
				if($this->get_param(['source', 'youtube', 'typeSource']) == 'playlist'){
					$posts = $youtube->show_playlist_videos($this->get_param(['source', 'youtube', 'playList']), $this->get_param(['source', 'youtube', 'count']));
				}else{
					$posts = $youtube->show_channel_videos($this->get_param(['source', 'youtube', 'count']));
				}
				
				$additions['yt_type'] = $this->get_param(['source', 'youtube', 'typeSource'], 'channel');
				$max_posts	 = $this->get_param(['source', 'youtube', 'count'], '25');
				$max_allowed = 50;
			break;
			case 'vimeo':
				$vimeo		= new \ThemePunch\SR\Core\External\Vimeo($this->get_param(['source', 'vimeo', 'transient'], '1200'));
				$vimeo_type	= $this->get_param(['source', 'vimeo', 'typeSource']);
				$max_posts	= $this->get_param(['source', 'vimeo', 'count'], '25');
				$max_allowed = 60;
				if(intval($max_posts) > $max_allowed) $max_posts = $max_allowed;

				switch($vimeo_type){
					case 'user':
						$posts = $vimeo->get_vimeo_videos($vimeo_type, $this->get_param(['source', 'vimeo', 'userName']), $max_posts);
					break;
					case 'channel':
						$posts = $vimeo->get_vimeo_videos($vimeo_type, $this->get_param(['source', 'vimeo', 'channelName']), $max_posts);
					break;
					case 'group':
						$posts = $vimeo->get_vimeo_videos($vimeo_type, $this->get_param(['source', 'vimeo', 'groupName']), $max_posts);
					break;
					case 'album':
						$posts = $vimeo->get_vimeo_videos($vimeo_type, $this->get_param(['source', 'vimeo', 'albumId']), $max_posts);
					break;
					default:
					break;
				}
				
				$additions['vim_type'] = $this->get_param(['source', 'vimeo', 'typeSource'], 'user');
			break;
			default:
				//an add-on's own stream type answers with its items, already in the shape streamline_stream_data() returns
				$posts = apply_filters('revslider_get_stream_data', false, $sourcetype, $this);
				if(is_array($posts)){
					$max_posts = count($posts);
					break;
				}
				if($this->context()->get('preview_mode') && $this->environment()->is_admin_area()){ //the editor's ajax call answers in JSON, the REST preview page renders on and logs it
					Registry::instance()->get(Api::class)->ajax_response_error(__('Make sure that the stream settings are properly selected in "Module General Options -> Content -> Stream Settings".', 'revslider'));
				}else{
					$this->throw_error(__('Make sure that the stream settings are properly selected in "Module General Options -> Content -> Stream Settings".', 'revslider'));
				}
			break;
		}
		$content = ob_get_contents();
		ob_clean();
		ob_end_clean();

		if($posts === false) $this->throw_error($content);

		return ['posts' => $posts, 'additions' => $additions, 'max_allowed' => $max_allowed, 'max_posts' => $max_posts, 'sourcetype' => $sourcetype];
	}

	/**
	 * social stream source, v7 entry point: returns the raw stream items instead of slide objects
	 * @return array
	 */
	public function get_slides_data_from_stream_v7(){
		$_posts = $this->_get_stream_data();
		$posts	= $this->get_val($_posts, 'posts', []);
		$sourcetype	= $this->get_val($_posts, 'sourcetype', []);
		$additions	= $this->get_val($_posts, 'additions', []);

		return $this->streamline_stream_data($posts, $sourcetype, $additions);
	}

	/**
	 * build one slide per stream item, by cloning the slider's template slide
	 * @return RevSliderSlide[]
	 */
	public function get_slides_data_from_stream($published = false){
		$templates = $this->get_slides($published);
		$templates = $this->assoc_to_array($templates);
		
		if(count($templates) == 0) return [];
		
		$_slides	 = [];
		$_posts 	 = $this->_get_stream_data();
		$posts		 = $this->get_val($_posts, 'posts', []);
		$sourcetype	 = $this->get_val($_posts, 'sourcetype', []);
		$additions	 = $this->get_val($_posts, 'additions', []);
		$max_posts	 = $this->get_val($_posts, 'max_posts', []);
		$max_allowed = $this->get_val($_posts, 'max_allowed', []);

		$max_posts = intval($max_posts);
		if($max_posts < 0) $max_posts *= -1;
		
		$posts = apply_filters('revslider_pre_mod_stream_data', $posts, $sourcetype, $this->id);
		$posts = (is_string($posts) || is_bool($posts)) ? [] : $posts;
		
		while(count($posts) > $max_posts || count($posts) > $max_allowed){
			array_pop($posts);
		}
		
		$posts = apply_filters('revslider_post_mod_stream_data', $posts, $sourcetype, $this->id);
		
		if(empty($posts)){
			if($this->context()->get('preview_mode') && $this->environment()->is_admin_area()){ //the editor's ajax call answers in JSON, the REST preview page renders on and logs it
				Registry::instance()->get(Api::class)->ajax_response_error(__('Make sure that the stream settings are properly selected in "Module General Options -> Content -> Stream Settings".', 'revslider'));
			}else{
				$this->throw_error(__('Make sure that the stream settings are properly selected in "Module General Options -> Content -> Stream Settings".', 'revslider'));
			}
		}

		if(!empty($posts)){
			$i = 0;
			$tk = 0;
			foreach($posts as $data){
				if(empty($data)) continue; //ignore empty entries, like from instagram
				
				$slide_template = $templates[$tk];
				
				//advance the templates
				$tk++;
				$tk = ($tk == count($templates)) ? 0 : $tk;
				$_slides[$i] = new Slide();

				$_slides[$i]->init_by_stream_data($data, $slide_template, $this->id, $sourcetype, $additions);
				
				$i++;
			}
		}
		
		$this->slides = $_slides;
		
		return $this->slides;
	}
	
	
	
	
	
	
	/**
	 * get setting - start with slide
	 * @return int zero based index of the slide the slider should start on
	 */
	public function get_start_with_slide_setting(){
		$slide = $this->get_param(['general', 'firstSlide', 'alternativeFirstSlide'], 1);
		if(is_numeric($slide)){
			$slide = (int)$slide - 1;
			if($slide < 0 || $slide >= count($this->slides)) $slide = 0;
		}else{
			$slide = 0;
		}
		
		return $slide;
	}
	
	
	/**
	 * get the Slider Overview Structure for V6 data
	 * @since: 6.0
	 */
	public function get_overview_data_v6($slider = false, $slides = false, $slide_ids = false){
		$slider		= ($slider == false || $slider instanceof Folder) ? $this : $slider;
		$id			= 0;
		$slides		= ($slides !== false) ? $slides :  $slider->get_slides();
		$type		= $slider->get_type();
		$image		= '';
		$sid		= $slider->get_id();
		$do_ids		= ($slide_ids !== false) ? false : true;
		$addons_used = [];

		if(!empty($slides)){
			foreach($slides as $slide){
				$id		= $slide->get_id();
				$image	= $slide->get_overview_image_attributes_v6($type);
				break;
			}
			if($do_ids) $slide_ids = [];
			
			foreach($slides as $slide){
				if($do_ids) $slide_ids[] = $slide->get_id();

				$addons = $slide->get_param('addOns');
				if(!empty($addons)){
					foreach($addons as $addon => $values){
						if($this->_truefalse($this->get_val($values, 'enable', false)) === true){
							if(!in_array($addon, $addons_used)) $addons_used[] = $addon;
						}
					}
				}
			}
		}

		$addons = $slider->get_param('addOns');
		if(!empty($addons)){
			foreach($addons as $addon => $values){
				if($this->_truefalse($this->get_val($values, 'enable', false)) === true){
					if(!in_array($addon, $addons_used)) $addons_used[] = $addon;
				}
			}
		}

		$favorite = Registry::instance()->get('RevSliderFavorite');

		return [
			'id'		=> $sid,
			'slide_id'	=> $id,
			'slide_ids'	=> $slide_ids,
			'title'		=> esc_html($slider->get_title()),
			'alias'		=> $slider->get_alias(),
			'source'	=> esc_html($type),
			'type'		=> $slider->get_param('type', 'standard'),
			'size'		=> $slider->get_param('layouttype'),
			'bg'		=> $image,
			'addons'	=> $addons_used,
			'notmigrated' => true,
			'favorite'	=> ($slider instanceof Folder) ? false : $favorite->is_favorite('modules', $sid),
			'premium'	=> $slider->get_param('pakps', false),
			'tags'		=> $this->get_tags(),
			'children'	=> ($slider instanceof Folder) ? $slider->get_children() : [],
			'function'	=> ($slider instanceof Folder) ? 'folder' : 'module',
		];
	}

	/**
	 * get the Slider Overview Structure
	 * the trimmed record the module overview grid needs: title, alias, type, thumbnail, tags
	 * @since: 6.0
	 * @return array
	 */
	public function get_overview_data($slider = false, $slides = false, $slide_ids = false){
		$slider		= ($slider == false || $slider instanceof Folder) ? $this : $slider;
		$id			= 0;
		$type		= $slider->get_type();
		$sid		= $slider->get_id();
		$bg			= $slider->get_param('thumb');
		$do_ids		= ($slide_ids !== false) ? false : true;
		$addons_used = [];

		if($do_ids){
			$slides		= ($slides !== false) ? $slides :  $slider->get_slides();
			$slide_ids	= [];
			
			foreach($slides ?? [] as $slide){
				if($do_ids) $slide_ids[] = $slide->get_id();
			}
		}

		$thumb = $slider->get_param('thumb', '');
		if(empty($thumb) && in_array($type, ['posts', 'specific_posts', 'specific_post', 'woocommerce', 'facebook', 'twitter', 'instagram', 'flickr', 'youtube', 'vimeo'])){
			$scr = RS_PLUGIN_URL_CLEAN.'core/public/assets/sources/';
			$bg = $scr. $type .'.png';
			$bg = (in_array($type, ['posts', 'specific_posts', 'specific_post'])) ? $scr.'post.png' : $bg;
			$bg = ($type === 'woocommerce') ? $scr.'woo.png' : $bg;
		}

		$favorite = Registry::instance()->get('RevSliderFavorite');
		$thumbs	  = ($slider instanceof Folder) ? null : Registry::instance()->get('ThemePunch\SR\Core\Thumbs');
		$auto	  = $thumbs !== null ? $thumbs->src_for($slider, $do_ids ? $slides : false) : false;

		return ($slider instanceof Folder) ? 
			[
				'id'		=> $sid,
				'alias'		=> $slider->get_alias(),
				'children'	=> ($slider instanceof Folder) ? $slider->get_children() : [],
				'function'	=> 'folder',
				'title'		=> esc_html($slider->get_title()),
				//'parent'	=> '',
			]
			:
			[
				'id'		=> $sid,
				'addons'	=> $slider->get_param('aU', []),
				'alias'		=> $slider->get_alias(),
				'bg'		=> $auto !== false ? $auto : $bg,
				'thumbStale' => $thumbs !== null && $thumbs->is_stale($sid, !empty($thumb)),
				'children'	=> $slide_ids,
				'function'	=> 'module',
				//'parent'	=> '',
				'favorite'	=> $favorite->is_favorite('modules', $sid),
				'premium'	=> $slider->get_param('prem', false),
				'tags'		=> $this->get_tags(),
				'title'		=> esc_html($slider->get_title()),
				'type'		=> $slider->get_param('type', 'standard'),
				'template_uid' => ($slots = \RevSliderGlobals::instance()->get('RevSliderSlots')) ? $slots->template_uid_of($slider) : '', //the store template this module was installed from, '' when built here or on a host without layer meta
				'sourcetype' => $type,
				'qg'		=> $slider->get_param('qg', false),
				'subType'	=> $slider->get_param('source', 'post', 'subType'),
			];
	}
	
	
	/** delegates to the platform's content source (WordPress: post/product sourcing), empty where a host does not source posts this way @since 7.2.1 */
	public function get_specific_posts($gal_ids = []){
		return $this->legacy_source_call($this->content_source(), 'get_specific_posts', [$gal_ids], []);
	}
	
	
	/** delegates to the platform's content source (WordPress: post/product sourcing), empty where a host does not source posts this way @since 7.2.1 */
	public function get_posts_by_id($ids, $slider_id, $is_gal, $additional = []){
		return $this->legacy_source_call($this->content_source(), 'get_posts_by_id', [$ids, $slider_id, $is_gal, $additional], []);
	}
	
	
	/** delegates to the platform's content source (WordPress: post/product sourcing), empty where a host does not source posts this way @since 7.2.1 */
	public function get_posts_by_category($slider_id, $cat_id, $sort_by = 'ID', $direction = 'DESC', $max_posts = -1, $post_types = 'any', $taxonomies = 'category', $addition = [], $type = ''){
		return $this->legacy_source_call($this->content_source(), 'get_posts_by_category', [$slider_id, $cat_id, $sort_by, $direction, $max_posts, $post_types, $taxonomies, $addition, $type], []);
	}
	
	
	/** delegates to the platform's content source (WordPress: post/product sourcing), empty where a host does not source posts this way @since 7.2.1 */
	public function get_post_categories($post_id, $tax){
		return $this->legacy_source_call($this->content_source(), 'get_post_categories', [$post_id, $tax], []);
	}
	
	
	/**
	 * get cats and taxanomies data from the category id's
	 * reads the <taxonomy>_<id> keys a module stores its terms under
	 * @return array tax and cats, each a comma list
	 */
	public function get_tax_by_cat_id($cat_ids){
		$ret	= ['tax' => '', 'cats' => ''];
		$tax	= [];
		$cats	= '';
		$taxs	= '';

		if(is_string($cat_ids)){
			$cat_ids = trim($cat_ids);
			$cat_ids = (empty($cat_ids)) ? [] : explode(',', $cat_ids);
		}

		if(!empty($cat_ids)){
			foreach($cat_ids as $cat){
				//a category drop cleared to nothing stores [""]
				$cat = trim((string)$cat);
				if($cat === '' || strpos($cat, 'option_disabled') === 0) continue;

				$pos = strrpos($cat, '_');
				if($pos === false) $this->throw_error(__('Wrong category format', 'revslider'));

				$tax_name		= substr($cat, 0, $pos);
				$tax[$tax_name]	= $tax_name;
				$cats			.= (!empty($cats)) ? ',' : '';
				$cats			.= substr($cat, $pos + 1, strlen($cat) - $pos - 1); //category id
			}

			$ret['cats'] = $cats;
		}

		if(!empty($tax)){
			foreach($tax as $tax_name){
				$taxs .= (!empty($taxs)) ? ','.$tax_name : $tax_name;
			}
		}
		$ret['tax'] = $taxs;

		return $ret;
	}
	
	/**
	 * check for global settings and modify slider settings
	 * only do these changes on outputting the slider
	 * applies global overrides (e.g. a forced font or lazy load mode) that are not stored per slider
	 * @since: 6.4.12
	 * @return bool
	 **/
	public function modify_by_global_settings(){
		if($this->environment()->is_admin_area() && !$this->context()->get('preview_mode')) return true;

		//unused for now, can come in handy later
	}

	/**
	 * one record per entry, each built on the next of the module's template slides.
	 *
	 * The loop is the module's: the templates cycle, every record is handed the one it belongs to, and the
	 * finished list passes sr_streamline_post_data_post. What a record holds is the host's, since only it knows
	 * where a title, an excerpt or an image comes from.
	 * @param array $data the entries the host's own query returned
	 * @return array
	 * @since 7.2.1
	 */
	public function streamline_posts_data($data){
		if(empty($data) || !is_array($data)) return $data;

		$source = $this->content_source();
		if(!method_exists($source, 'record')) return $this->legacy_source_call($source, 'streamline_posts_data', [$data], []);

		$templates	= $this->assoc_to_array($this->get_slides(false));
		$metas		= $this->get_used_metas();
		$num_temp	= count($templates);
		$key		= 0;
		$template	= null;
		$post_data	= [];

		$source->begin_records($data);

		foreach($data as $k => $entry){
			if(!empty($templates)){
				$template = clone $templates[$key];
				$key++;
				if($key == $num_temp){
					$key		= 0;
					$templates	= $this->assoc_to_array($this->get_slides(false)); //reset as clone did not work properly
				}
			}

			$post_data[] = $source->record($entry, $template, $metas);
		}

		return apply_filters('sr_streamline_post_data_post', $post_data, $data, $metas, $this);
	}

	/**
	 * streamline the stream data to an universal array
	 * dispatches to the streamline_by_* method of the configured network, so every stream ends up with
	 * the same keys
	 * @return array
	 */
	public function streamline_stream_data($data, $sourcetype, $additions){
		$_data = [];
		switch($sourcetype){
			case 'facebook':
				$_data = $this->streamline_by_facebook($data, $additions);
			break;
			case 'flickr':
				$_data = $this->streamline_by_flickr($data, $additions);
			break;
			case 'instagram':
				$_data = $this->streamline_by_instagram($data, $additions);
			break;
			case 'vimeo':
				$_data = $this->streamline_by_vimeo($data, $additions);
			break;
			case 'youtube':
				$_data = $this->streamline_by_youtube($data, $additions);
			break;
			default:
				//revslider_get_stream_data delivers its items streamlined; the filter may still reshape them
				$_data = apply_filters('revslider_streamline_stream_data', is_array($data) ? $data : false, $data, $sourcetype, $additions, $this);

				if($_data === false) $this->throw_error(__('Source must be from a stream', 'revslider'));
			break;
		}

		$_data = $this->add_stream_metas($_data, $data, $sourcetype, $additions);
		
		return $_data;
	}


	/**
	 * normalize a Facebook stream into the common slide data structure
	 * @return array
	 */
	public function streamline_by_facebook($data, $additions){
		$fb_data	= [];
		
		if(empty($data) || !is_array($data)) return $fb_data;

		foreach($data as $k => $entry){
			$likes			= $this->get_val($entry, ['likestream', 'summary', 'can_like']);
			$num_comments	= $this->get_val($entry, ['commentstream', 'summary', 'can_comment']);
			if(empty($likes) || $likes === false)				$likes = $this->get_val($entry, ['likestream', 'summary', 'total_count']);
			if(empty($num_comments) || $num_comments === false)	$num_comments = $this->get_val($entry, ['commentstream', 'summary', 'total_count']);

			if($this->get_val($additions, 'fb_type') == 'album'){
				$image_array = $this->get_val($entry, 'images');
				$image_url	= $image_array[0]['source'] ?? $this->get_val( $entry, 'picture' );
			}else{
				$image_url	= $this->get_val($entry, 'full_picture');
			}
			$image_url = (empty($image_url)) ? RS_PLUGIN_URL_CLEAN.'core/public/assets/sources/facebook.png' : $image_url;
			$image_url = (is_ssl()) ? str_replace('http://', 'https://', $image_url) : $image_url;
			$image_thumb = $this->get_val($entry, 'picture');

			$fb_data[] = [
				'author'		=> $this->get_val($entry, ['from', 'name']),
				'customMetas'	=> [],
				'content'		=> ['content' => nl2br($this->get_val($entry, ['message']))],
				'likes'			=> $likes,
				'link'			=> $this->get_val($entry, ['permalink_url']),
				'media'			=> $image_url,
				'modified'		=> $this->convert_post_date($this->get_val($entry, ['updated_time'])),
				'num_comments'	=> $num_comments,
				'publish'		=> $this->convert_post_date($this->get_val($entry, ['created_time'])),
				'thumb'			=> $image_thumb,
				'title'			=> $this->get_val($entry, ['message'])
			];
		}

		return $fb_data;
	}

	/**
	 * encode a Flickr photo id into its short URL form (flic.kr/p/...)
	 * @return string
	 */
	private function flickr_base_encode($num, $alphabet = '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ')
	{
		$base_count = strlen($alphabet);
		$encoded = '';
		while ($num >= $base_count) {
			$div = $num / $base_count;
			$mod = ($num - ($base_count * intval($div)));

			/* 2.1.5 */
			$mod = intval($mod);
			$encoded = $alphabet[$mod] . $encoded;

			$num = intval($div);
		}
		if ($num) $encoded = $alphabet[$num] . $encoded;
		return $encoded;
	}

	/**
	 * normalize a Flickr stream into the common slide data structure
	 * @return array
	 */
	public function streamline_by_flickr($data, $additions){
		$fl_data = [];
		if(empty($data) || !is_array($data)) return $fl_data;
		
		foreach($data as $k => $entry){
			$fl_data[] = [
				'author'		=> $this->get_val($entry, ['ownername']),
				'content'		=> ['content' => nl2br($this->get_val($entry, ['description', '_content']))],
				'link'			=> '//flic.kr/p/'.$this->flickr_base_encode($this->get_val($entry, ['id'])),
				'media'			=> $this->remove_http($this->get_val($entry, ['url_o'])),
				'modified'		=> $this->convert_post_date($this->get_val($entry, ['datetaken'])),
				'publish'		=> $this->convert_post_date($this->get_val($entry, ['datetaken'])),
				'thumb'			=> $this->remove_http($this->get_val($entry, ['url_t'])),
				'title'			=> $this->get_val($entry, ['title']),
				'views'			=> $this->get_val($entry, ['views'])
			];
		}

		return $fl_data;
	}

	/**
	 * normalize an Instagram stream into the common slide data structure
	 * @return array
	 */
	public function streamline_by_instagram($data, $additions){
		$ig_data = [];
		if(empty($data) || !is_array($data)) return $ig_data;

		foreach($data as $k => $entry){
			$image = $this->get_val($entry, ['thumbnail_url']);
			$video = $this->get_val($entry, ['media_url']);
			$ig_data[] = [
				'author'		=> $this->get_val($entry, ['username']),
				'content'		=> ['content' => nl2br($this->get_val($entry, ['caption']))],
				'link'			=> $this->get_val($entry, ['link']),
				'media'			=> (empty($image)) ? '' : $video,
				'num_comments'	=> $this->get_val($entry, ['comments_count']),
				'likes'			=> $this->get_val($entry, ['like_count']),
				'publish'		=> $this->convert_post_date($this->get_val($entry, ['taken_at_timestamp'])),
				'title'			=> $this->get_val($entry, ['caption']),
				'thumb'			=> (empty($image)) ? $video : $image,
				'views'			=> $this->get_val($entry, ['stats_number_of_plays']),
			];
		}

		return $ig_data;
	}

	/**
	 * normalize a Vimeo stream into the common slide data structure
	 * @return array
	 */
	public function streamline_by_vimeo($data, $additions){
		$vm_data = [];
		if(empty($data) || !is_array($data)) return $vm_data;

		//TODO: check $additions['vim_type'];
		foreach($data as $k => $entry){
			$vm_data[] = [
				'author'		=> $this->get_val($entry, ['user', 'user_name']),
				'content'		=> ['content' => nl2br($this->get_val($entry, ['description']))],
				'likes'			=> $this->get_val($entry, ['stats_number_of_likes']),
				'link'			=> $this->get_val($entry, ['url']),
				'media'			=> $this->get_val($entry, ['id']),
				'num_comments'	=> $this->get_val($entry, ['stats_number_of_comments']),
				'publish'		=> $this->convert_post_date($this->get_val($entry, ['upload_date'])),
				'title'			=> $this->get_val($entry, ['title']),
				'thumb'			=> $this->get_val($entry, ['thumbnail_large']),
				'views'			=> $this->get_val($entry, ['stats_number_of_plays'])
			];
		}

		return $vm_data;
	}

	/**
	 * normalize a YouTube stream into the common slide data structure
	 * @return array
	 */
	public function streamline_by_youtube($data, $additions){
		$yt_data = [];
		
		if(empty($data) || !is_array($data)) return $yt_data;

		$channel = ($additions['yt_type'] === 'channel') ? true : false;
		foreach($data as $k => $entry){
			$id			= ($channel) ? $this->get_val($entry, ['id', 'videoId']) : $this->get_val($entry, ['snippet', 'resourceId', 'videoId']);
			$thumb		= $this->get_val($entry, ['snippet', 'thumbnails', 'maxres', 'url']);
			if(empty($thumb)) $thumb = $this->get_val($entry, ['snippet', 'thumbnails', 'high', 'url']);
			$yt_data[]	= [
				'author'	=> ($channel) ? '' : $this->get_val($entry, ['snippet', 'author']),
				'content'	=> nl2br($this->get_val($entry, ['snippet', 'description'])),
				'link'		=> '//youtube.com/watch?v=' .  $id,
				'publish'	=> ($channel) ? '' : $this->convert_post_date($this->get_val($entry, ['snippet', 'publishedAt'])),
				'media'		=> $id,
				'thumb'		=> $this->remove_http($thumb),
				'title'		=> $this->get_val($entry, ['snippet', 'title'])
			];
		}
		
		return $yt_data;
	}
	
	/**
	 * attach the raw source fields the slider's layers actually reference as extra meta on each stream item
	 * @return array
	 */
	public function add_stream_metas($streams, $stream_data, $source_type, $additions = [], $metas = null){
		$metas = $metas ?? $this->get_used_metas();

		if(empty($metas)) return $streams;
		if(empty($streams)) return $streams;

		foreach($streams as $k => $data){
			foreach($metas as $meta){
				switch($meta){
					case 'alias':
						$streams[$k]['alias'] = $this->get_val($stream_data, [$k, 'alias']);
					break;
				}

				$streams[$k] = $this->expand_text_meta($streams[$k], $data, $meta);
			}
		}

		return $streams;
	}

	/**
	 * one {{field:words:N}} or {{field:chars:N}} placeholder resolved into a record: the content under
	 * content[words|chars][N], every other field under meta[field][words|chars][N]
	 * @since 7.2.1
	 * @param array $record the record to write into
	 * @param array $source the record the field is read from, the same one or a snapshot of it
	 * @param string $meta the placeholder without braces, e.g. 'title:words:10'
	 * @return array the record
	 */
	public function expand_text_meta($record, $source, $meta){
		$_meta = (strpos($meta, ':') !== false) ? explode(':', $meta) : false;
		if($_meta === false) return $record;

		//this works only for metas that are already defined in the record
		if(!isset($source[$_meta[0]])) return $record;
		if(count($_meta) !== 3) return $record;
		$_meta[2] = intval($_meta[2]);
		if($_meta[2] === 0) return $record;

		$text = $source[$_meta[0]];
		if(is_array($text)) $text = $text[$_meta[0]] ?? '';
		if(!is_scalar($text)) $text = ''; //an image list or a null resolves empty, without a notice
		$value = $this->cut_text($text, $_meta[1], $_meta[2]);
		if($value === false) return $record;

		if($_meta[0] === 'content'){
			if(!isset($record['content'][$_meta[1]])) $record['content'][$_meta[1]] = [];
			$record['content'][$_meta[1]][$_meta[2]] = $value;
		}else{
			if(!isset($record['meta'])) $record['meta'] = [];
			if(!isset($record['meta'][$_meta[0]])) $record['meta'][$_meta[0]] = [];
			if(!isset($record['meta'][$_meta[0]][$_meta[1]])) $record['meta'][$_meta[0]][$_meta[1]] = [];
			$record['meta'][$_meta[0]][$_meta[1]][$_meta[2]] = $value;
		}

		return $record;
	}

	/**
	 * which %meta:...% placeholders the slides of this slider use - so only those get resolved
	 * @return array
	 */
	public function get_used_metas(){
		if(!empty($this->metas)) return $this->metas;
		if(empty($this->slides)) $this->get_slides_for_output();
		
		$text = serialize($this->slides);
		preg_match_all('/{{(.*?)}}/', $text, $matches, PREG_PATTERN_ORDER);
		
		$placeholders = $this->get_val($matches, 1, []);
		
		if(empty($placeholders)) return $this->metas;
		
		$this->metas = $placeholders;

		return $this->metas;
	}

	/**
	 * sensitive data needs to be removed: source as it contains stream login data
	 * @return array the slider data, safe to hand to the client
	 */
	public function modify_slider_data($obj){
		if(!isset($obj['settings']['source'])) return $obj;

		$modify_allow	= ['excerptLimit', 'maxPosts', 'maxProducts', 'count'];
		$modify_sources	= ['post', 'specific_posts', 'specific_post', 'woo', 'woocommerce', 'instagram', 'facebook', 'flickr', 'twitter', 'vimeo', 'youtube'];

		$source = $obj['settings']['source'];
		$obj['settings']['source'] = ['type' => $this->get_val($obj, ['settings', 'source', 'type'])];
		foreach($modify_sources as $m_source){
			if(!isset($source[$m_source])) continue;
			foreach($modify_allow as $allow){
				if(!isset($source[$m_source][$allow])) continue;

				$obj['settings']['source'][$m_source][$allow] = $source[$m_source][$allow];
			}
		}
		
		return $obj;
	}

	/**
	 * a Submit Form's Send to URL stays on the server, Api::form_submit() sends to it; the page only learns that there is one,
	 * or anyone could post to the webhook straight from the page source
	 * @since 7.2.9
	 * @param array $obj the object get_full_slider_JSON() builds
	 * @return array
	 */
	public function hide_form_hooks($obj){
		foreach($obj['slides'] ?? [] as $sid => $slide){
			foreach($this->get_val($slide, ['slide', 'actions'], []) ?: [] as $k => $action){
				if(is_array($action) && !empty($action['hook'])) $obj['slides'][$sid]['slide']['actions'][$k]['hook'] = true;
			}
		}

		return $obj;
	}

	/**
	 * returns a slider object with all slides and static slide in v6 or v7 format for JSON/REST
	 * @return array
	 */
	public function get_full_slider_JSON($slider = false, $full = true, $slide_ids = [], $forced_ids = [], $raw = false, $modify = true){
		if($slider === false) $slider = $this;
		if(!empty($slide_ids) && !is_array($slide_ids)) $slide_ids = (array)$slide_ids;

		$slider_id		= $slider->get_id();
		$type			= $slider->get_param('type', 'standard');
		$hero			= ($type === 'hero') ? true : false;
		$do_shortcodes	= ($slider->is_stream_post()) ? false : true;
		$obj			= (empty($slide_ids)) ? ['settings' => $slider->get_params(), 'slides' => [], 'id' => $slider_id] : ['slides' => []];
		if(isset($obj['settings'])) $obj['settings'] = $this->apply_brand_kit($obj['settings'], $modify);
		$slides			= ($raw) ? $slider->get_slides(false, true) : $slider->get_slides_modified(false, true, $hero, $do_shortcodes);

		if(empty($slides)){
			if($modify === true && isset($obj['settings']['source'])) unset($obj['settings']['source']);
			return $obj;
		}

		$first = ($full === false) ? true : false;
		foreach($slides as $slide_id => $slide){
			if(!empty($slide_ids) && !in_array($slide_id, $slide_ids)) continue;
			if($modify === true && !$slide->check_use_slide()) continue;
			if($this->context()->get('serial') > 2 && !empty($forced_ids) && !in_array($slide_id, $forced_ids)) continue;

			$obj['slides'][$slide_id] = [
				'id'	 => $slide_id,
				'slide'	 => $slide->get_params(),
				'layers' => ($full === true || $full === false && $first === true) ? $slide->get_layers(true) : []
			];
			$first = false;
		}

		$static_slide_id = $slide->get_static_slide_id($slider_id);
		if(!empty($slide_ids) && !in_array($static_slide_id, $slide_ids, true)) $static_slide_id = 0;

		if(intval($static_slide_id) > 0){
			$static_slide = new Slide();
			$static_slide->init_by_static_id($static_slide_id);
			if(!empty($static_slide)){
				$obj['slides'][$static_slide_id] = [ //'static_'.
					'id'		=> $static_slide_id,
					'slide'		=> $static_slide->get_params(),
					'layers'	=> $static_slide->get_layers()
				];
			}
		}
		
		foreach($obj['slides'] as $sid => $_slide){
			$obj['slides'][$sid]['slide'] = $this->apply_brand_kit($_slide['slide'], $modify);
			foreach($_slide['layers'] ?? [] as $lid => $layer) $obj['slides'][$sid]['layers'][$lid] = $this->apply_brand_kit($layer, $modify);
		}

		if($modify === true){ //make sure to have the source overwritten for streams, if we are not in backend
			$obj = $this->modify_slider_data($obj);
			$obj = $this->hide_form_hooks($obj);
		}

		if(isset($obj['slider_params']['source'])) $obj['slider_params']['source'] = ['type' => $this->get_val($obj, ['slider_params', 'source', 'type'])];
		
		$obj['addOns'] = [];

		return apply_filters('sr_get_full_slider_JSON', $obj, $slider, $raw, $modify);
	}

	/**
	 * get the thumb url for the slide (navigation may need it)
	 * only resolved when a navigation type that shows thumbnails is actually enabled
	 * @return string
	 **/
	public function get_thumb_url($slide, $post_id = false){
		$bullet_set		= $this->get_param(['nav', 'bullets', 'set'], false);
		$thumbs_set		= $this->get_param(['nav', 'thumbs', 'set'], false);
		$arrows_set		= $this->get_param(['nav', 'arrows', 'set'], false);
		$tabs_set		= $this->get_param(['nav', 'tabs', 'set'], false);
		$scrubber_set	= $this->get_param(['nav', 'scrubber', 'set'], false);
		$arrows_style	= $this->get_param(['nav', 'arrows', 't'], 'round');
		$bullets_style	= $this->get_param(['nav', 'bullets', 't'], 'round');
		$bglayer		= $slide->get_bg_layer();
		$is_posts		= $this->is_posts();
		$active			= ($bullet_set == true || $thumbs_set == true || $arrows_set == true || $tabs_set == true || $scrubber_set == true) ? true : false;
		$special		= (
			in_array($arrows_style, ['preview1', 'preview2', 'preview3', 'preview4', 'custom'], true) ||
			in_array($bullets_style, ['preview1', 'preview2', 'preview3', 'preview4', 'custom'], true)
		) ? true : false;

		if($active === false && $special === false) return '';
		
		if($is_posts && $post_id !== false){
			$thumb_src = $this->media_library()->image_url($post_id, 'full');
			$slide->image_url = ($thumb_src !== false) ? $thumb_src : '';
		}else{
			$thumb_src	= $slide->get_param(['thumb', 'src'], '');
			$slide->image_url = (!empty($thumb_src)) ? $thumb_src : '';
		}
		if(empty($slide->image_url)){
			//per device the src is [ld, d, n, t, m] with '#a' for inherit; the overview shows the biggest device's picture
			$slide->image_url = $this->get_biggest_device_setting_v7($this->get_val($bglayer, ['bg', 'image', 'src'], ''), ['ld' => true, 'd' => true, 'n' => true, 't' => true, 'm' => true], '');
		}
		$slide->image_url = (empty($slide->image_url)) ? $this->get_val($bglayer, ['bg', 'video', 'poster', 'src'], '') : $slide->image_url;

		if($arrows_set === true && in_array(intval($arrows_style), [1012])) return $slide->image_url; //check if we are dione and if, then return full image // or custom

		$bg_stream	 = $slide->get_param(['bg', 'image', 'fromStream'], false);
		$source_type = $this->get_param(['source', 'type'], 'gallery');
		$dimension	 = $slide->get_param(['thumb', 'dimension'], 'slider');
		$url		 = ($is_posts && $bg_stream === true) ? '' : $slide->image_url;
		$slide_bg	 = 'trans';
		if(!empty($this->get_val($bglayer, ['bg', 'image'], []))) $slide_bg = 'image';
		if(!empty($this->get_val($bglayer, ['bg', 'video'], []))) $slide_bg = 'video';
	

		if(
			$dimension == 'slider' &&
			($is_posts ||
			in_array($source_type, ['youtube', 'vimeo'], true) ||
			in_array($slide_bg, ['image', 'video', 'vimeo', 'youtube', 'html5', 'streamvimeo', 'streamyoutube', 'streaminstagram', 'streamvimeoboth', 'streamyoutubeboth', 'streaminstagramboth'], true))
		){ //use the slider settings for width / height
			$w = intval(str_replace('px', '', $this->get_param(['nav', 'p', 'w'], 100)));
			$h = intval(str_replace('px', '', $this->get_param(['nav', 'p', 'h'], 50)));

			$w = ($w == 0) ? 100 : $w;
			$h = ($h == 0) ? 50 : $h;

			if(empty($url)){ //try to get resized thumb
				$url = $this->media_library()->scaled_image_url($slide->image_url, $w, $h);
			}else{
				$url = $this->media_library()->scaled_image_url($url, $w, $h);
				if(empty($url)){
					$url = $slide->image_url;
					$url = $this->media_library()->scaled_image_url($url, $w, $h);
				}
			}
		}
		
		$url = (empty($url)) ? $slide->image_url : $url; //if empty - put regular image

		return ($this->check_valid_image($url)) ? $url : '';
	}

	/**
	 * return the responsive sizes depending on v6 or v7
	 * @return array
	 */
	public function get_responsive_sizes(){
		$sizes = $this->get_param('uSize');
		$enabled_sizes = [
			'ld' => $this->get_val($sizes, 0, false),
			'd' => $this->get_val($sizes, 1, false),
			'n' => $this->get_val($sizes, 2, false),
			't' => $this->get_val($sizes, 3, false),
			'm' => $this->get_val($sizes, 4, false)
		];
		if(in_array(true, $enabled_sizes) === false) $enabled_sizes['d'] = true;

		return $enabled_sizes;
	}

	/**
	 * @deprecated old version of get_sliders();
	 * added for compatibility with Avada theme builder
	 **/
	public function getArrSliders($templates = false){
		$this->add_deprecation_message('getArrSliders', 'get_sliders');
		_deprecated_function( __FUNCTION__, '7.0.0', 'get_sliders');
		return $this->get_sliders($templates);
	}

	/**
	 * @deprecated old version of get_sliders_short();
	 * added for compatibility with Jupiter theme and WPBakery builder
	 * @return array
	 */		
	public function getArrSlidersShort($exclude_id = null, $filter = 'all'){
		$this->add_deprecation_message('getArrSlidersShort', 'get_sliders_short');
		_deprecated_function( __FUNCTION__, '7.0.0', 'get_sliders_short');
		return $this->get_sliders_short($exclude_id, $filter);
	}

	/**
	 * @deprecated old version of get_alias();
	 * added for compatibility with Avada theme builder
	 * @return string
	 */
	public function getAlias(){
		$this->add_deprecation_message('getAlias', 'get_alias');
		_deprecated_function( __FUNCTION__, '7.0.0', 'get_alias');
		return $this->get_alias();
	}	

	/**
	 * @return string
	 * @deprecated old version of get_title();
	 * added for compatibility with Avada theme builder
	 */
	public function getTitle(){
		$this->add_deprecation_message('getTitle', 'get_title');
		_deprecated_function( __FUNCTION__, '7.0.0', 'get_title');
		return $this->get_title();
	}	

	/**
	 * @return int|string
	 * @deprecated old version of get_id();
	 * added for compatibility with Avada theme builder
	 **/
	public function getID(){
		$this->add_deprecation_message('getID', 'get_id');
		_deprecated_function( __FUNCTION__, '7.0.0', 'get_id');
		return $this->get_id();
	}	

	/**
	 * @return bool
	 * @deprecated old version of check_alias();
	 * added for compatibility with Avada theme builder
	 **/
	public function isAliasExistsInDB($alias){
		$this->add_deprecation_message('isAliasExistsInDB', 'check_alias');
		_deprecated_function( __FUNCTION__, '7.0.0', 'check_alias');
		return $this->check_alias($alias);
	}
	
	/**
	 * @return void
	 * @deprecated old version of initByAlias();
	 * added for compatibility with Avada theme builder
	 */
	public function initByAlias($alias){
		$this->add_deprecation_message('initByAlias', 'init_by_alias');
		_deprecated_function( __FUNCTION__, '7.0.0', 'init_by_alias');
		$this->init_by_alias($alias);
	}

	/**
	 * @return RevSliderSlide[]
	 * @deprecated old version of get_slides();
	 * added for compatibility with Avada theme builder
	 */
	public function getSlidesFromGallery($published = false, $allwpml = false, $first = false){
		$this->add_deprecation_message('getSlidesFromGallery', 'get_slides');
		_deprecated_function( __FUNCTION__, '7.0.0', 'get_slides');
		return $this->get_slides($published, $allwpml, $first);
	}

	/**
	 * @deprecated
	 * this function does not exist anymore, only added for backwards compatibility,
	 * as a theme author, please use different functionality to recreate this
	 * @return array
	 */
	public function getAllSliderAliases(){
		$this->add_deprecation_message('getAllSliderAliases', false);
		return array();
	}

	/**
	 * @deprecated old version of RevSliderSliderImport::import_slider();
	 * added for compatibility with theme demo importers written against 6.x
	 * @return array
	 */
	public function importSliderFromPost($update_animation = true, $updateStatic = true, $exact_filepath = false, $is_template = false, $single_slide = false, $update_navigation = true, $install = true){
		$this->add_deprecation_message('importSliderFromPost', 'import_slider');
		_deprecated_function( __FUNCTION__, '7.0.0', 'RevSliderSliderImport::import_slider');
		$import = new \ThemePunch\SR\Core\Admin\Import();
		return $import->import_slider($update_animation, $exact_filepath, $is_template, $single_slide, $update_navigation);
	}
}
