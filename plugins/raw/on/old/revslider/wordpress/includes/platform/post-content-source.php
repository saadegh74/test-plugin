<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Platform\WordPress;

if(!defined('ABSPATH')) exit();

/**
 * WordPress' post and product sourcing behind the ContentSource port: everything that turns a
 * slider's source config into streamlined slide arrays through WP_Query, postmeta, taxonomies
 * and the media library. The bodies moved verbatim from RevSliderSlider; $this->slider carries
 * the shared state the block always read and mutated (params, is_woocommerce, is_gallery), so
 * the addon filters keep receiving the RevSliderSlider instance they always got.
 * @since 7.2.1
 */
class PostContentSource implements \ThemePunch\SR\Ports\ContentSource {

	/** @var \RevSliderSlider|null null when only the slide-level resolvers are used */
	protected $slider;

	/** @var array what begin_records() worked out once for the whole list */
	protected $record_excerpt_limit = ['type' => 'words', 'limit' => 55];
	protected $record_img_sizes = [];
	protected $record_ignore_taxonomies = [];

	public function __construct($slider = null){
		$this->slider = $slider;
	}

	public function get_slides_data(){
		return $this->get_slides_data_from_posts_v7();
	}

	/**
	 * post based source, v7 entry point: returns the raw post data instead of building slide objects
	 * @return array
	 */
	public function get_slides_data_from_posts_v7(){
		$posts = $this->get_post_data();
		
		return $this->streamline_posts_data($posts);
	}

	/**
	 * get slides from posts
	 * builds one slide per post by cloning the slider's template slide and filling in the post's data
	 * @return RevSliderSlide[]
	 */
	public function get_slides_data_from_posts($published = false, $lang = 'all'){
		$templates = $this->slider->get_slides($published);
		$templates = $this->slider->assoc_to_array($templates);
		if(count($templates) == 0) return [];
		$posts		= $this->get_post_data($published);
		$slides		= [];
		$key		= 0;
		$num_temp	= count($templates);
		
		foreach($posts ?? [] as $post_data){
			$found = false;
			if($lang !== 'all' && $this->slider->get_val($templates[$key], ['params', 'child', 'language'], 'all') !== $lang){
				$children = $templates[$key]->get_children();
				if(!empty($children)){
					foreach($children as $child){
						if($this->slider->get_val($child, ['params', 'child', 'language'], 'all') === $lang){
							$template = clone $child;
							$found = true;
							break;
						}
					}
				}
			}
			
			if($found === false){
				$template = clone $templates[$key];
			}
			//advance the templates
			$key++;
			if($key == $num_temp){
				$key		= 0;
				$templates	= $this->slider->get_slides($published); //reset as clone did not work properly
				$templates	= $this->slider->assoc_to_array($templates);
			}

			$slide = new \RevSliderSlide();
			$slide->init_by_post_data($post_data, $template, $this->slider->id);
			
			$slides[] = $slide;
		}

		$this->slider->slides = $slides;
		
		return $this->slider->slides;
	}

	/**
	 * run the configured post query - dispatches to the specific/category/related/popular/latest variants
	 * @return array the posts, already streamlined into the slide data structure
	 */
	public function get_post_data($published = false){
		$posts		= [];
		$gal_ids	= $this->slider->get_gallery_ids();
		if(!empty($gal_ids)) $this->slider->set_param(['source', 'type'], 'specific_posts');
		$source = $this->slider->get_param(['source', 'type'], 'gallery');

		switch($source){
			case 'posts':
			case 'post':
				$subtype = $this->slider->get_param(['source', 'post', 'subType'], 'post');

				if($subtype === 'current_post'){
					global $post;
					//if empty, check referer and get ID from that one if exists
					$post_id = (empty($post)) ? url_to_postid($this->slider->get_val($_SERVER, 'HTTP_REFERER')) : $this->slider->get_val($post, 'ID');
					if(empty($post_id) || $post_id === 0) $post_id = get_queried_object_id();
					if(empty($post_id) || $post_id === 0){
						$referer = wp_get_referer();
						if($referer){
							$referer = strtok($referer, '?'); // remove query string
							$post_id = url_to_postid($referer);
						}
					}
					$posts = $this->get_specific_posts(['', $post_id]);
				}elseif(in_array($subtype, ['specific_posts', 'specific_post'], true)){
					$posts = $this->get_specific_posts($gal_ids);
				}else{
					//check where to get posts from
					switch($this->slider->get_param(['source', 'post', 'fetchType'], 'cat_type')){
						case 'cat_tag':
						default:
							$posts = $this->get_posts_by_categories($published);
						break;
						case 'related':
							$posts = $this->get_related_posts();
						break;
						case 'popular':
							$posts = $this->get_popular_posts();
						break;
						case 'recent':
							$posts = $this->get_latest_posts();
						break;
						case 'next_prev':
							$posts = $this->get_next_previous_post();
						break;
					}
				}
			break;
			case 'specific_posts':
			case 'specific_post':
				$posts = $this->get_specific_posts($gal_ids);
			break;
			case 'woocommerce':
			case 'woo':
				$posts = $this->get_products_from_categories($published);
			break;
			default:
				$this->slider->throw_error(__('This Source Type must be from posts.', 'revslider'));
			break;
		}

		if(!empty($posts)){
			foreach($posts as $k => $p){
				$post_id = (int) $p['ID'];
				$post    = get_post( $post_id );

				if ( ! $post ||
				     ! is_post_type_viewable( get_post_type_object( $post->post_type ) ) ||
				     post_password_required( $post ) ||
				     ! $this->viewer_may_read( $post ) )
				{
					unset($posts[$k]);
				}
			}
		}

		return $posts;
	}

	/**
	 * The statuses a module may list for whoever views it. WP_Query without post_status adds drafts, pending and
	 * scheduled posts whenever is_admin(), and admin-ajax is admin for a visitor too: a front AJAX render
	 * (slider.get.html) listed unpublished posts. Someone who may edit posts keeps seeing them, as in the editor
	 * @since 7.2.2
	 * @return array|null null leaves the query as it was
	 */
	private function viewer_statuses(){
		if(current_user_can('edit_posts')) return null;
		return current_user_can('read_private_posts') ? ['publish', 'private'] : ['publish'];
	}

	/**
	 * Whether the viewer may read this post: a public status, or the right to read it. A gallery's image passes: its file is
	 * public anyway, and it would take the status of a draft it was once uploaded to
	 * @since 7.2.2
	 * @param \WP_Post $post
	 * @return bool
	 */
	private function viewer_may_read($post){
		if($post->post_type === 'attachment') return true;
		$status = get_post_status($post);
		$public = function_exists('is_post_status_viewable') ? is_post_status_viewable($status) : $status === 'publish';
		return $public || current_user_can('read_post', $post->ID);
	}

	/**
	 * get related posts from current one
	 * matches by tag first and fills up from the same categories when there are not enough
	 * @since: 5.1.1
	 * @return array
	 */
	public function get_related_posts(){
		$my_posts	= [];
		$tags		= '';
		$post_id	= get_the_ID();
		$sort_by	= $this->slider->get_param(['source', 'post', 'sortBy'], 'ID');
		$source		= $this->slider->get_param('source');
		$post		= $this->slider->get_val($source, 'post');
		$max_posts	= $this->slider->get_val($post, 'maxPosts', 30);
		$max_posts	= (empty($max_posts) || !is_numeric($max_posts)) ? -1 :  $max_posts;
		$post_tags	= get_the_tags();
		
		if($post_tags){
			foreach($post_tags as $post_tag){
				$tags .= $post_tag->slug . ',';
			}
		}
		
		$query = [
			'numberposts' => $max_posts,
			'exclude'	=> $post_id,
			'order'		=> $this->slider->get_param(['source', 'post', 'sortDirection'], 'DESC'),
			'tag'		=> $tags
		];

		$tax_query = $this->get_tax_query();
		if(!empty($tax_query)) $query['tax_query'] = $tax_query;
		
		if(strpos($sort_by, 'meta_num_') === 0){
			$query['orderby']	= 'meta_value_num';
			$query['meta_key']	= str_replace('meta_num_', '', $sort_by);
		}elseif(strpos($sort_by, 'meta_') === 0){
			$query['orderby']	= 'meta_value';
			$query['meta_key']	= str_replace('meta_', '', $sort_by);
		}else{
			$query['orderby']	= $sort_by;
		}

		$get_relateds		= apply_filters('revslider_get_related_posts', $query, $post_id);
		$tag_related_posts	= get_posts($get_relateds);
		
		if(count($tag_related_posts) < $max_posts){
			$ignore = [];
			foreach($tag_related_posts as $tag_related_post){
				$ignore[] = $tag_related_post->ID;
			}
			$article_categories = get_the_category($post_id);
			$category_string = '';
			foreach($article_categories as $category){
				$category_string .= $category->cat_ID . ',';
			}
			
			$max	= $max_posts - count($tag_related_posts);
			$excl	= implode(',', $ignore);
			$query	= [
				'exclude'		=> $excl,
				'numberposts'	=> $max,
				'category'		=> $category_string
			];
			
			if(strpos($sort_by, 'meta_num_') === 0){
				$query['orderby']	= 'meta_value_num';
				$query['meta_key']	= str_replace('meta_num_', '', $sort_by);
			}else
			if(strpos($sort_by, 'meta_') === 0){
				$query['orderby']	= 'meta_value';
				$query['meta_key']	= str_replace('meta_', '', $sort_by);
			}else{
				$query['orderby']	= $sort_by;
			}
			
			$get_relateds		= apply_filters('revslider_get_related_posts', $query, $post_id);
			$cat_related_posts	= get_posts($get_relateds);
			$tag_related_posts	= $tag_related_posts + $cat_related_posts;
		}

		foreach($tag_related_posts as $post){
			$the_post = (method_exists($post, 'to_array')) ? $post->to_array() : (array)$post;
			if($the_post['ID'] == $post_id) continue;
			$my_posts[] = $the_post;
		}
		
		return $my_posts;
	}

	/**
	 * get popular posts
	 * ordered by comment count
	 * @return array
	 */
	public function get_popular_posts($max_posts = false){
		$post_id	= get_the_ID();
		$my_posts	= [];
		$source		= $this->slider->get_param('source');
		$post		= $this->slider->get_val($source, 'post');
		$types		= $this->slider->get_val($post, 'types', 'post'); //[post, page]
		$sortBy		= $this->slider->get_val($post, 'sortBy', 'ID'); //ID
		$sortDirection = $this->slider->get_val($post, 'sortDirection', 'DESC'); //DESC, ASC
		$subType	= $this->slider->get_val($post, 'subType', 'post'); //post
		if(!is_array($types)){
			if(strpos($types, ',') !== false){
				$types = explode(',', $types);
				$types = (array_search('any', $types) !== false) ? 'any' : $types;
			}
		}
		$types	= (empty($types)) ? 'any' : $types;
		
		if($max_posts == false){
			$max_posts	= $this->slider->get_val($post, 'maxPosts', 30);
			$max_posts = (empty($max_posts) || !is_numeric($max_posts)) ? -1 : $max_posts;
		}else{
			$max_posts = intval($max_posts);
		}

		$args = [
			'suppress_filters' => 0,
			'posts_per_page' => $max_posts,
			'post_type'	=> $types,
			'meta_key'  => '_thumbnail_id',
			'orderby'   => 'comment_count',
			'order'     => $sortDirection
		];
		
		//add sort by (could be by meta)
		if(strpos($sortBy, 'meta_num_') === 0){
			$args['orderby']	= 'meta_value_num';
			$args['meta_key']	= str_replace('meta_num_', '', $sortBy);
		}elseif(strpos($sortBy, 'meta_') === 0){
			$args['orderby']	= 'meta_value';
			$args['meta_key']	= str_replace('meta_', '', $sortBy);
		}else{
			$args['orderby']	= $sortBy;
		}
		if($args['meta_key'] === 'rating') $args['meta_key'] = '_wc_average_rating';

		$tax_query = $this->get_tax_query();
		if(!empty($tax_query)) $args['tax_query'] = $tax_query;
		
		$args	= apply_filters('revslider_get_popular_posts', $args, $post_id);
		$posts	= get_posts($args);
		
		foreach($posts ?? [] as $post){
			$my_posts[] = (method_exists($post, 'to_array')) ? $post->to_array() : (array)$post;
		}
		
		return $my_posts;
	}

	/**
	 * get recent posts
	 * @return array
	 */
	public function get_latest_posts($max_posts = false){
		$post_id	= get_the_ID();
		$my_posts	= [];
		$args		= [
			'post_type' => 'any',
			'suppress_filters' => 0,
			'meta_key'	=> '_thumbnail_id',
			'orderby'	=> 'date',
			'order'		=> 'DESC'
		];
		
		if($max_posts == false){
			$source		= $this->slider->get_val($this->slider->params, 'source');
			$post		= $this->slider->get_val($source, 'post');
			$max_posts	= $this->slider->get_val($post, 'maxPosts', 30);
			$max_posts	= (empty($max_posts) || !is_numeric($max_posts)) ? -1 : $max_posts;
		}else{
			$max_posts = intval($max_posts);
		}
		
		$args['posts_per_page']	= $max_posts;
		
		$tax_query = $this->get_tax_query();
		if(!empty($tax_query)) $args['tax_query'] = $tax_query;

		$args	= apply_filters('revslider_get_latest_posts', $args, $post_id);
		$posts	= get_posts($args);
		
		foreach($posts ?? [] as $post){
			$my_posts[] = (method_exists($post, 'to_array')) ? $post->to_array() : (array)$post;
		}
		
		return $my_posts;
	}

	/**
	 * the post before and/or after the one currently being viewed
	 * @return array
	 */
	public function get_next_previous_post(){
		$my_posts = [];
		
		$startup_next_post = get_next_post();
		if(!empty($startup_next_post)){
			$my_posts[] = (method_exists($startup_next_post, 'to_array')) ? $startup_next_post->to_array() : (array)$startup_next_post;
		}    
		$startup_previous_post = get_previous_post();
		if(!empty($startup_previous_post)){
			$my_posts[] = (method_exists($startup_previous_post, 'to_array')) ? $startup_previous_post->to_array() : (array)$startup_previous_post;
		}
		
		return $my_posts;
	}

	/**
	 * build the tax_query from the taxonomies and terms the slider selected
	 * @return array
	 */
	public function get_tax_query(){
		$cat_ids	= $this->slider->get_param(['source', 'post', 'category']);
		$data		= $this->get_tax_by_cat_id($cat_ids);
		$tax_query	= false;
		if(isset($data['tax']) && isset($data['tax']) && !empty($data['tax']) && !empty($data['cats'])){
			$cat_id = (strpos($data['cats'], ',') !== false) ? explode(',', $data['cats']) : [$data['cats']];
			$tax_query = ['relation' => 'OR'];

			//add taxomonies to the query
			$taxonomies = (strpos($data['tax'], ',') !== false) ? explode(',', $data['tax']) : [$data['tax']];
			foreach($taxonomies as $taxomony){
				$tax_query[] = [
					'taxonomy'	=> $taxomony,
					'field'		=> 'id',
					'terms'		=> $cat_id
				];
			}
		}

		return $tax_query;
	}

	/**
	 * get posts from categories (by the slider params).
	 * @return array
	 */
	private function get_posts_by_categories($published = false){
		$cat_ids	= $this->slider->get_param(['source', 'post', 'category']);
		$data		= $this->get_tax_by_cat_id($cat_ids);
		$post_types = $this->slider->get_param(['source', 'post', 'types'], 'post');
		$sort_by	= $this->slider->get_param(['source', 'post', 'sortBy'], 'ID');
		$sort_dir	= $this->slider->get_param(['source', 'post', 'sortDirection'], 'DESC');
		$sort_dir	= ($sort_by == 'menu_order') ? 'ASC' : $sort_dir;
		$source		= $this->slider->get_param('source');
		$post		= $this->slider->get_val($source, 'post');
		$max_posts	= $this->slider->get_val($post, 'maxPosts', 30);
		$max_posts	= (empty($max_posts) || !is_numeric($max_posts)) ? -1 : $max_posts;
		$addition	= [];
		
		if($published == true){
			$addition['post_status'] = 'publish';
		}
		
		$slider_id	= $this->slider->get_id();
		$post		= $this->get_posts_by_category($slider_id, $data['cats'], $sort_by, $sort_dir, $max_posts, $post_types, $data['tax'], $addition, 'post');
		
		return apply_filters('revslider_get_posts_by_categories', $post, $this->slider);
	}

	/**
	 * get products from categories (by the slider params).
	 * WooCommerce variant of get_posts_by_categories(), adding the product meta query
	 * @return array
	 */
	private function get_products_from_categories($published = false){
		$slider_id	= $this->slider->get_id();
		$cat_ids	= $this->slider->get_param(['source', 'woo', 'category']);
		$data		= $this->get_tax_by_cat_id($cat_ids);
		$cat_ids	= $data['cats'];
		$taxonomies	= $data['tax'];
		$sort_by	= $this->slider->get_param(['source', 'woo', 'sortBy'], 'ID');
		$sort_dir	= $this->slider->get_param(['source', 'woo', 'sortDirection'], 'DESC');
		$sort_dir	= ($sort_by == 'menu_order') ? 'ASC' : $sort_dir;
		$max_posts	= $this->slider->get_param(['source', 'woo', 'maxProducts'], 30);
		$max_posts	= (empty($max_posts) || !is_numeric($max_posts)) ? -1 : $max_posts;
		$post_types	= $this->slider->get_param(['source', 'woo', 'types'], 'any');
		$addition	= [];
		$this->slider->is_woocommerce = true;
		
		if($published == true){ //Events integration
			$addition['post_status'] = 'publish';
		}
		
		$addition = array_merge($addition, \RevSliderWooCommerce::get_meta_query($this->slider->get_params()));

		return $this->get_posts_by_category($slider_id, $cat_ids, $sort_by, $sort_dir, $max_posts, $post_types, $taxonomies, $addition);
	}

	/**
	 * get posts from specific posts list
	 * @return array
	 */
	public function get_specific_posts($gal_ids = []){
		$additional	= [];
		$slider_id	= $this->slider->get_id();
		
		if(!empty($gal_ids) && $gal_ids[0] !== ''){
			$posts	= $gal_ids;
			$posts	= apply_filters('revslider_set_posts_list_gal', $posts, $this->slider->get_id());
		}else{
			if(isset($gal_ids[0])){
				unset($gal_ids[0]);
				$posts					= implode(',', $gal_ids);
				$additional['order']	= 'none';
				$additional['orderby']	= 'post__in';
			}else{
				$posts = $this->slider->get_param(['source', 'post', 'list'], '');
				$additional['order'] = $this->slider->get_param(['source', 'post', 'sortDirection'], 'DESC');
				$additional['orderby'] = $this->slider->get_param(['source', 'post', 'sortBy'], '');
			}
			$posts = apply_filters('revslider_set_posts_list', $posts, $this->slider->get_id());
		}
		
		return $this->get_posts_by_id($posts, $slider_id, $this->slider->is_gallery, $additional);
	}

	/**
	 * get posts by coma saparated posts
	 * @return array
	 */
	public function get_posts_by_id($ids, $slider_id, $is_gal, $additional = []){
		$arr = (is_string($ids)) ? explode(',', $ids) : $ids;

		$query = [
			'ignore_sticky_posts' => 1,
			'post_type'	=> 'any',
			'post__in'	=> $arr
		];
		if($is_gal){
			$query['post_status']	= 'inherit';
			$query['orderby']		= 'post__in';
		}
		
		$query	= array_merge($query, $additional);
		$statuses = $this->viewer_statuses();
		if(!isset($query['post_status']) && $statuses !== null) $query['post_status'] = $statuses;
		$query	= apply_filters('revslider_get_posts', $query, $slider_id);
		if(!isset($query['no_found_rows'])) $query['no_found_rows'] = true; //front-end sliders never paginate - skip the SQL_CALC_FOUND_ROWS count
		$object	= new \WP_Query($query);
		$posts	= $object->posts;

		//check if we used the [gallery] shortcode, but added posts instead if images
		if(empty($posts) && $is_gal){
			unset($query['post_status']);
			if($statuses !== null) $query['post_status'] = $statuses;
			$object	= new \WP_Query($query);
			$posts	= $object->posts;
			if(!empty($posts)) $this->slider->is_gallery = false; //setting this will make sure to fetch images from the posts instead of the image id directly
		}

		foreach($posts ?? [] as $key => $post){
			$posts[$key] = (method_exists($post, 'to_array')) ? $post->to_array() : (array)$post;
		}

		return $posts;
	}

	/**
	 * get posts by some category
	 * could be multiple
	 * the main post query: applies the slider's post types, taxonomies, sorting and limit
	 * @return array
	 */
	public function get_posts_by_category($slider_id, $cat_id, $sort_by = 'ID', $direction = 'DESC', $max_posts = -1, $post_types = 'any', $taxonomies = 'category', $addition = [], $type = ''){
		$a = apply_filters('revslider_get_posts_by_category', ['slider_id' => $slider_id, 'cat_id' => $cat_id, 'sort_by' => $sort_by, 'direction' => $direction, 'max_posts' => $max_posts, 'post_types' => $post_types, 'taxonomies' => $taxonomies, 'addition' => $addition, 'type' => $type], $this->slider);
		$slider_id	= $this->slider->get_val($a, 'slider_id');
		$cat_id		= $this->slider->get_val($a, 'cat_id');
		$sort_by	= $this->slider->get_val($a, 'sort_by');
		$direction	= $this->slider->get_val($a, 'direction');
		$max_posts	= $this->slider->get_val($a, 'max_posts');
		$post_types	= $this->slider->get_val($a, 'post_types');
		$taxonomies	= $this->slider->get_val($a, 'taxonomies');
		$addition	= $this->slider->get_val($a, 'addition');
		$type		= $this->slider->get_val($a, 'type');
		$tax		= (!empty($taxonomies)) ? explode(',', $taxonomies) : []; //get taxonomies array
		
		if(!is_array($post_types)){
			if(strpos($post_types, ',') !== false){
				$post_types = explode(',', $post_types);
				$post_types = (array_search('any', $post_types) !== false) ? 'any' : $post_types;
			}
		}
		$post_types	= (empty($post_types)) ? 'any' : $post_types;
		$cat_id		= (strpos($cat_id, ',') !== false) ? explode(',', $cat_id) : [$cat_id];
		
		$query		= [
			'order'					=> $direction,
			'ignore_sticky_posts'	=> 1,
			'posts_per_page'		=> $max_posts,
			'showposts'				=> $max_posts,
			'post_type'				=> $post_types
		];

		//add sort by (could be by meta)
		if(strpos($sort_by, 'meta_num_') === 0){
			$query['orderby']	= 'meta_value_num';
			$query['meta_key']	= str_replace('meta_num_', '', $sort_by);
		}elseif(strpos($sort_by, 'meta_') === 0){
			$query['orderby']	= 'meta_value';
			$query['meta_key']	= str_replace('meta_', '', $sort_by);
		}else{
			$query['orderby']	= $sort_by;
		}
		
		if(!empty($taxonomies)){
			$tax_query = ['relation' => 'OR'];
		
			//add taxomonies to the query
			$taxonomies = (strpos($taxonomies, ',') !== false) ? explode(',', $taxonomies) : [$taxonomies];
			foreach($taxonomies as $taxomony){
				$tax_query[] = ['taxonomy' => $taxomony, 'field' => 'id', 'terms' => $cat_id];
			}

			$query['tax_query'] = $tax_query;
		}
		
		if(!empty($addition)){
			$tax_query = $this->slider->get_val($addition, 'tax_query', []);
			if(!empty($tax_query)){
				if(!isset($query['tax_query'])) $query['tax_query'] = [];
				if(is_array($tax_query)){
					foreach($tax_query as $tk => $tv){
						if(is_numeric($tk)){
							$query['tax_query'][] = $tv;
						}else{
							$query['tax_query'][$tk] = $tv;
						}
					}
				}
				unset($addition['tax_query']);
			}
			$query = array_merge($query, $addition);
		}
		$statuses = $this->viewer_statuses();
		if(!isset($query['post_status']) && $statuses !== null) $query['post_status'] = $statuses;
		
		$query		= apply_filters('revslider_get_posts', $query, $slider_id);
		if(!isset($query['no_found_rows'])) $query['no_found_rows'] = true; //front-end sliders never paginate - skip the SQL_CALC_FOUND_ROWS count
		$full_posts	= new \WP_Query($query);
		$posts		= $full_posts->posts;
		
		if($this->slider->is_woocommerce){
			$params	= $this->slider->get_params();
			$max	= intval($max_posts);
			$ranged	= false;
			foreach(['regPriceFrom', 'regPriceTo', 'salePriceFrom', 'salePriceTo'] as $k) $ranged = $ranged || !empty($this->slider->get_val($params, ['source', 'woo', $k]));
			if(!$ranged || $max <= 0){
				$posts = \RevSliderWooCommerce::filter_products_by_price($posts, $params);
			}else{
				//The price range drops products after WordPress applied the limit, so pages are scanned until the limit is
				//met, and an offset counts products that pass, which is what a list asking for its next page means by it
				$skip	= max(0, intval($query['offset'] ?? 0));
				$scan	= $query;
				unset($scan['offset']);
				$posts	= [];
				for($page = 1; $page <= 20; $page++){
					$raw	= ($page === 1 && $skip === 0) ? $full_posts->posts : (new \WP_Query(array_merge($scan, ['paged' => $page])))->posts;
					$posts	= array_merge($posts, \RevSliderWooCommerce::filter_products_by_price($raw, $params));
					if(count($posts) >= $skip + $max || count($raw) < $max) break;
				}
				$posts = array_slice($posts, $skip, $max);
			}
		}

		if(!empty($posts)){
			foreach($posts as $key => $post){
				$arr_post = (method_exists($post, 'to_array')) ? $post->to_array() : (array)$post;
				$arr_post['categories'] = $this->get_post_categories($post, $tax);
				
				$posts[$key] = $arr_post;
			}
		}
		
		return $posts;
	}

	/**
	 * get post categories by post ID and taxonomies
	 * the post ID can be post object or array too
	 * @return array
	 */
	public function get_post_categories($post_id, $tax){
		if(!is_numeric($post_id)){
			$post_id = (array)$post_id;
			$post_id = $post_id['ID'];
		}
		$cats = wp_get_post_terms($post_id, $tax);
		
		return $this->slider->class_to_array($cats);
	}

	/** @return array the core's reading of the stored category keys */
	public function get_tax_by_cat_id($cat_ids){
		return $this->slider->get_tax_by_cat_id($cat_ids);
	}

	/** @return array delegates to the core loop, which asks this source for every record @since 7.2.1 */
	public function streamline_posts_data($data){
		return $this->slider->streamline_posts_data($data);
	}

	/**
	 * one list, one pass: the excerpt limit, the image sizes and the taxonomies to skip, worked out once for the whole list
	 * @param array $rows
	 * @return void
	 * @since 7.2.1
	 */
	public function begin_records($rows){
		$this->record_ignore_taxonomies	= apply_filters('revslider_slide_ignore_taxonomies', ['post_tag', 'translation_priority', 'language', 'post_translations'], $this->slider);
		$this->record_excerpt_limit		= $this->slider->excerpt_limit($this->slider->get_val($this->slider->params, ['source', 'post', 'excerptLimit'], 55));
		$this->record_img_sizes			= $this->slider->get_all_image_sizes();

		//bulk-prime author + featured-image caches so record()'s per-post get_user_by() / wp_get_attachment_image_src()
		//calls hit the object cache instead of querying once per post (avoids N+1)
		if(!is_admin()){
			$prime_authors = [];
			$prime_thumbs  = [];
			foreach($rows as $entry){
				$aid = (int)$this->slider->get_val($entry, 'post_author');
				if($aid > 0) $prime_authors[$aid] = true;
				$pid = (int)$this->slider->get_val($entry, 'ID');
				if($pid > 0){
					$tid = ($this->slider->is_gallery) ? $pid : (int)get_post_thumbnail_id($pid);
					if($tid > 0) $prime_thumbs[$tid] = true;
				}
			}
			if(!empty($prime_authors) && function_exists('cache_users')) cache_users(array_keys($prime_authors));
			if(!empty($prime_thumbs) && function_exists('_prime_post_caches')) _prime_post_caches(array_keys($prime_thumbs), false, true);
		}
	}

	/**
	 * one streamlined record: a WP_Post plus its meta, taxonomy and image data, flattened into the structure the
	 * layers read for their %title%, %excerpt%, %featured_image% … placeholders
	 * @param array $entry one post row
	 * @param \RevSliderSlide|null $template the template slide this record is built on
	 * @param array $metas the placeholders the module's slides use
	 * @return array
	 * @since 7.2.1
	 */
	public function record($entry, $template, $metas){
		$author						= get_user_by('ID', $this->slider->get_val($entry, 'post_author'));
		$post_id					= $this->slider->get_val($entry, 'ID');
		$cats						= $this->slider->get_val($entry, ['source', 'post', 'category']);
		$full						= false;
		//$post_image_id				= ($source_type === 'specific_posts') ? $post_id : get_post_thumbnail_id($post_id);
		$post_image_id				= ($this->slider->is_gallery) ? $post_id : get_post_thumbnail_id($post_id);
		$featured_image_url			= wp_get_attachment_image_src($post_image_id, 'full'); //get full and thumbnail
		$featured_image_url_thumb	= wp_get_attachment_image_src($post_image_id, 'thumbnail'); //get full and thumbnail
		
		if(empty($cats)){
			$cats		= [];
			$taxonomies = get_object_taxonomies($this->slider->get_val($entry, 'post_type'));
			
			foreach($taxonomies ?? [] as $ptt){
				if(in_array($ptt, $this->record_ignore_taxonomies, true)) continue;
				$temp_cats = get_the_terms($post_id, $ptt);
				if(!empty($temp_cats)){
					$cats = array_merge($cats, $temp_cats);
					$full = true;
				}
			}
		}
		$title	 = $this->slider->get_val($entry, ['post_title']);
		$excerpt = $this->slider->shorten_excerpt($this->slider->get_val($entry, ['post_excerpt']), $this->record_excerpt_limit);

		$raw_data = [
			'id'			=> $post_id,
			'author'		=> $this->slider->get_val($author, ['data', 'display_name']),
			'content'		=> ['content' => $this->slider->get_val($entry, ['post_content'])],
			'excerpt'		=> $excerpt,
			'link'			=> get_permalink($this->slider->get_val($entry, 'ID')),
			'media'			=> ($featured_image_url !== false) ? $featured_image_url[0] : '',//$this->slider->get_val($entry, ['full_picture']),
			'mediatag'		=> ($featured_image_url !== false) ? $this->slider->stream_img_tag($featured_image_url[0], $title, [$featured_image_url[1], $featured_image_url[2]]) : '',
			'meta'			=> [],
			'modified'		=> $this->slider->convert_post_date($this->slider->get_val($entry, 'post_modified')),
			'numcomments'	=> $this->slider->get_val($entry, ['comment_count']),
			'publish'		=> $this->slider->convert_post_date($this->slider->get_val($entry, 'post_date_gmt')),
			'navthumb'		=> ($template !== null) ? $this->slider->get_thumb_url($template, $post_image_id) : '',
			'thumb'			=> ($featured_image_url_thumb !== false) ? $featured_image_url_thumb[0] : '', //$this->slider->get_val($entry, ['picture']),
			'thumbtag'		=> ($featured_image_url_thumb !== false) ? $this->slider->stream_img_tag($featured_image_url_thumb[0], $title, [$featured_image_url_thumb[1], $featured_image_url_thumb[2]]) : '',
			'taglist'		=> get_the_tag_list('', ',', '', $post_id),
			'title'			=> $title,
			'img_urls'		=> [],
		];	
		

		$temp_id_v7 = intval(get_post_meta($post_id, 'slide_template_v7', true));
		if($temp_id_v7 > 0){
			$local = new \RevSliderSlide();
			$local->init_by_id($temp_id_v7);
			$temp_sid = $local->get_slider_id();
			if(intval($temp_sid) > 0){
				$raw_data['sourceSlideId'] = $temp_id_v7;
				$raw_data['sourceModuleId'] = $temp_sid;
			}
		}

		if(!empty($metas)){
			foreach($metas as $meta){
				switch($meta){
					case 'id':
						$raw_data['id'] = $this->slider->get_val($entry, 'ID');
					break;
					case 'alias':
						$raw_data['alias'] = $this->slider->get_val($entry, 'post_name');
					break;
					case 'catlist':
						$raw_data['catlist'] = $this->slider->get_categories_html($cats, null, $post_id, $full);
					break;
					case 'catlist_raw':
						$raw_data['catlistraw'] = strip_tags($this->slider->get_categories_html($cats, null, $post_id, $full));
					break;
					case 'taglist':
						$raw_data['taglist'] = get_the_tag_list('', ',', '', $post_id);
					break;
					case 'author_website':
						$raw_data['author_website'] = $this->slider->get_val($author, 'user_url');
					break;
					case 'author_posts':
						$raw_data['author_posts'] = get_author_posts_url($this->slider->get_val($entry, 'post_author'));
					break;
				}

				$_meta = (strpos($meta, ':') !== false) ? explode(':', $meta) : false;
				if($_meta === false) continue;
				if($_meta[0] === 'meta'){
					$raw_data['meta'][$_meta[1]] = get_post_meta($post_id, $_meta[1], true);
				}elseif($_meta[0] === 'author_avatar'){
					if(count($_meta) !== 2) continue;
					$_meta[1] = intval($_meta[1]);
					if($_meta[1] === 0 || $_meta[1] < 0) continue;
					
					$raw_data['author_avatar'] = get_avatar_url($this->slider->get_val($entry, 'post_author'), ['size'=> $_meta[1]]);
				}else{
					$raw_data = $this->slider->expand_text_meta($raw_data, $raw_data, $meta);
				}
			}
		}

		if(!empty($this->record_img_sizes)){
			$ptid = ($this->slider->is_gallery) ? get_post_thumbnail_id($post_id) : $post_image_id; //reuse $post_image_id (already the thumbnail id when not a gallery) to avoid a second lookup
			foreach($this->record_img_sizes as $img_handle => $img_name){
				$featured_image_url = wp_get_attachment_image_src($ptid, $img_handle);
				if($featured_image_url === false) continue;
				$raw_data['img_urls'][$img_handle] = [
					'url' => $featured_image_url[0],
					'tag' => $this->slider->stream_img_tag($featured_image_url[0], $title, [$featured_image_url[1], $featured_image_url[2]])
				];
			}
		}
		
		return $raw_data;
	}

	/**
	 * init slide by post data
	  * @removed in 6.2.18 -> @change 6.2.16: $template_id will not be written if the current post id is not the post_id to prevent malfunctioning
	 * @return void
	 */
	public function init_by_post_data($slide, $data, \RevSliderSlide $template, $slider_id){
		$post_id	 = $slide->get_val($data, 'ID');
		$template_id = get_post_meta($post_id, 'slide_template_v7', true);
		//only change the template if we are in the post itself, not if we are in another revslider that is post based!
		$template_id = ($template_id == '') ? 'default' : $template_id;
	
		$slide->post_data = apply_filters('revslider_slide_initByPostData', $data, $template, $slider_id, $slide);
		
		if(!empty($template_id) && is_numeric($template_id)){ //init by local template, if this fails, init by global (slider) template
			try{
				/*
				we have to add this slide for the static slide to be available in certain cases
				check if slide exists
				*/
				$local = new \RevSliderSlide();
				if($local->exist_by_id($template_id)){
					$local->init_by_id($template_id);
					$slide->init_by_slide($local);
				}else{
					$slide->init_by_slide($template);
				}
			}catch(Exception $e){
				$slide->init_by_slide($template);
			}
			
		}else{
			//init by global template
			$slide->init_by_slide($template);
		}
		
		//set some slide params
		$slide->set_id($post_id);
		$slide->params['title']	= $slide->get_val($data, 'post_title');
		
		if($slide->get_val($slide->params, ['seo', 'set'], false) == true && $slide->get_val($slide->params, ['seo', 'type'], 'regular') == 'regular'){
			$link = get_permalink($post_id);
			$slide->params['seo']['link'] = str_replace(['%link%', '{{link}}'], $link, $slide->params['seo']['link']);
			$slide->params['seo']['link'] = str_replace('-', '_REVSLIDER_', $slide->params['seo']['link']);
			
			//process meta tags:
			$matches = [];
			preg_match('/%meta:\w+%/', $slide->params['seo']['link'], $matches);
			if(!empty($matches)){
				foreach($matches as $match){
					$meta = str_replace('%meta:', '', $match);
					$meta = str_replace('%', '', $meta);
					$meta = str_replace('_REVSLIDER_', '-', $meta);
					$meta_val = get_post_meta($post_id, $meta, true);
					$slide->params['seo']['link'] = str_replace($match, $meta_val, $slide->params['seo']['link']);
				}
			}
			
			$matches = [];
			preg_match('/{{meta:\w+}}/', $slide->params['seo']['link'], $matches);
			if(!empty($matches)){
				foreach($matches as $match){
					$meta = str_replace('{{meta:', '', $match);
					$meta = str_replace('}}', '',$meta);
					$meta = str_replace('_REVSLIDER_', '-', $meta);
					$meta_val = get_post_meta($post_id, $meta, true);
					$slide->params['seo']['link'] = str_replace($match, $meta_val, $slide->params['seo']['link']);
				}
			}
			$slide->params['seo']['link'] = str_replace('_REVSLIDER_', '-', $slide->params['seo']['link']);
		}
		$slide->params['publish']['state'] = ($data['post_status'] == 'publish') ? 'published' : $slide->params['publish']['state'] = 'unpublished';
		
		if(!in_array($slide->get_val($slide->params, ['bg', 'type'], 'trans'), ['trans', 'solid'], true)){
			if($slide->get_val($slide->params, ['bg', 'imageFromStream'], false) === true){ //if image is choosen, use featured image as background // && $slide->get_val($slide->params, ['bg', 'type']) == 'image'
				$tid = get_post_thumbnail_id($post_id);
				
				if(!empty($tid)){
					$slide->set_image_by_image_id($tid);
					
					//set the thumbnail image
					$thumbnail_url = wp_get_attachment_image_src($tid, 'thumbnail');
					if($thumbnail_url !== false){
						if(!isset($slide->params['thumb'])) $slide->params['thumb'] = [];
						$slide->params['thumb']['src'] = $slide->get_val($thumbnail_url, 0);
					}
				}
			}
		}
		//replace placeholders in layers:
		$this->set_layers_by_post($slide, $data, $slider_id);
	}

	/**
	 * replace layer placeholders by post data
	 * @return void
	 */
	public function set_layers_by_post($slide, $post, $slider_id){
		$post = apply_filters('revslider_slide_setLayersByPostData_pre', $post, $slider_id, $slide);
		$ignore_taxonomies = apply_filters('revslider_slide_ignore_taxonomies', ['post_tag', 'translation_priority', 'language', 'post_translations'], $slide);

		//check if we are woocommerce or not
		$post_id		= $slide->get_val($post, 'ID');
		$slider_source	= $slide->get_slider_param($slider_id, 'source', []);
		$source_type	= $slide->get_slider_param($slider_id, 'sourcetype', 'gallery');
		$lazyload		= ($slide->get_slider_param($slider_id, ['general', 'lazyLoad'], false) != 'none') ? true : false;
		$class			= 'tp-rs-img' . (($lazyload === true) ? ' rs-lazyload' : '');
		$excerpt_limit	= $slide->excerpt_limit(($source_type == 'woocommerce' || $source_type == 'woo') ? $slide->get_val($slider_source, ['woo', 'excerptLimit'], 55) : $slide->get_val($slider_source, ['post', 'excerptLimit'], 55));
		$excerpt_limit	= $this->get_excerpt_by_id($slide, $post_id, $excerpt_limit['limit'], $excerpt_limit['type']);
		
		$date		= $slide->get_val($post, 'post_date_gmt');
		$date_mod	= $slide->get_val($post, 'post_modified');
		$author		= $slide->get_val($post, 'post_author');
		$curauth	= get_user_by('ID', $author);
		
		$cats		= $slide->get_val($post, ['source', 'post', 'category']);
		$full		= false;
		if(empty($cats)){
			$cats = [];
			$post_type =  $slide->get_val($post, 'post_type');
			$taxonomies = get_object_taxonomies($post_type);
			
			if(!empty($taxonomies)){
				foreach($taxonomies as $ptt){
					if(in_array($ptt, $ignore_taxonomies, true)) continue;
					$temp_cats = get_the_terms($post_id, $ptt);
					if(!empty($temp_cats)){
						$cats = array_merge($cats, $temp_cats);
						$full = true;
					}
				}
			}
		}
		$img_sizes	= $slide->get_all_image_sizes();
		$ptid		= get_post_thumbnail_id($post_id);
		
		$attr		= [
			'title'			=> $slide->get_val($post, 'post_title'),
			'alias'			=> $slide->get_val($post, 'post_name'),
			'content'		=> $slide->get_val($post, 'post_content'),
			'link'			=> get_permalink($post_id),
			'excerpt'		=> $excerpt_limit,
			'postDate'		=> $slide->convert_post_date($date),
			'dateModified'	=> $slide->convert_post_date($date_mod),
			'authorName'	=> get_the_author_meta('display_name', $author),
			'authorID'		=> $author,
			'authorPage'	=> $curauth->user_url,
			'authorPostsPage' => get_author_posts_url($author),
			'catlist'		=> $slide->get_categories_html($cats, null, $post_id, $full),
			'catlist_raw'	=> strip_tags($slide->get_categories_html($cats, null, $post_id, $full)),
			'taglist'		=> get_the_tag_list('', ', ', '', $post_id),
			'numComments'	=> $slide->get_val($post, 'comment_count'),
			'img_urls'		=> []
		];
		
		foreach($img_sizes as $img_handle => $img_name){
			$featured_image_url = wp_get_attachment_image_src($ptid, $img_handle);
			if($featured_image_url !== false){
				$attr['img_urls'][$img_handle] = [
					'url' => $featured_image_url[0],
					'tag' => $slide->stream_img_tag($featured_image_url[0], $slide->get_val($attr, 'title'), [$featured_image_url[1], $featured_image_url[2]], $class)
				];
			}
		}
		
		$attr = apply_filters('revslider_slide_setLayersByPostData_post', $attr, $post, $slider_id, $slide);
		
		if(!empty($slide->layers)){
			foreach($slide->layers as $key => $layer){
				$text = $slide->get_val($layer, 'text');
				$text = apply_filters('revslider_mod_meta', $text, $post['ID'], $post); //option to add your own filter here to modify meta to your likings
				$text = $this->set_post_data($slide, $text, $attr, $post['ID']);

				$layer['text'] = $text;
				
				$actions = $slide->get_val($layer, ['actions', 'action'], []);
				if(!empty($actions)){
					foreach($actions as $a_k => $action){
						$ilink = $slide->get_val($action, 'image_link');
						if(!empty($ilink)){
							$ilink = $this->set_post_data($slide, $ilink, $attr, $post['ID']);
							$slide->set_val($layer, ['actions', 'action', $a_k, 'image_link'], $ilink);
						}
					}
				}
				
				/**
				 * check if we should add the featured image
				 * as the image, as the layer is image
				 * and has set to use the stream image
				 **/
				if($slide->get_val($layer, 'type', 'text') === 'image' && $slide->get_val($layer, ['media', 'imageFromStream'], false) === true){
					$featured_image_url = wp_get_attachment_image_src($ptid, 'full');
					if(!empty($featured_image_url)){
						$slide->set_val($layer, ['media', 'imageUrl'], $slide->get_val($featured_image_url, 0));
					}
				}
				
				//addon option values that may carry metas: declared per addon, resolved in Core\Addon\Metas
				$layer = apply_filters('revslider_slide_setLayersByPostData_layer', $layer, $attr, $post['ID'], $slide);
				
				$slide->layers[$key] = $layer;
			}
		}
		
		for($mi = 0; $mi < 10; $mi++){ //set params to the post data
			$pa = $slide->get_param(['info', 'params', $mi, 'v'], '');
			$pa = $this->set_post_data($slide, $pa, $attr, $post['ID']);
			$slide->set_param(['info', 'params', $mi, 'v'], $pa);
		}
		
		$param_list = [['attributes', 'alt'], ['attributes', 'class'], ['attributes', 'data']];
		foreach($param_list as $p){ //set params to the stream data
			$pa = $slide->get_param($p, '');
			$pa = $this->set_post_data($slide, $pa, $attr, $post['ID']);
			$slide->set_param($p, $pa);
		}
	}

	/**
	 * get excerpt from post id
	 * falls back to a shortened post content when the post has no excerpt of its own
	 * @return string
	 */
	public function get_excerpt_by_id($slide, $id, $limit = 55, $type = 'words'){
		$post	 = get_post($id);
		$excerpt = trim($post->post_excerpt);
		$excerpt = (empty($excerpt)) ? $post->post_content : $excerpt;
		$excerpt = $slide->shorten_excerpt($excerpt, ['type' => $type, 'limit' => $limit]);

		return apply_filters('revslider_getExcerptById', $excerpt, $post, $limit);
	}

	/**
	 * replace placeholders with post data
	 * resolves %title%, %excerpt%, %meta:key%, %featured_image_*% and friends in one layer's text
	 * @return string
	 **/
	public function set_post_data($slide, $text, $attr, $post_id){
		$img_sizes = $slide->get_all_image_sizes();
		
		//remove rev_slider shortcodes from content ( no inception ;)
		$content = $slide->get_val($attr, 'content');
		$content = preg_replace('/\\[rev_slider.*?\\]/', '', $content, -1);
		$content = str_replace('[/rev_slider]', '', $content);
		
		//add filter for addon metas
		$text = apply_filters('rev_slider_insert_meta', $text, $post_id);

		$text = $slide->resolve_basic_metas($text);
		$text = str_replace(['%title%', '{{title}}'], $slide->get_val($attr, 'title'), $text);
		$text = str_replace(['%excerpt%', '{{excerpt}}'], $slide->get_val($attr, 'excerpt'), $text);
		$text = str_replace(['%alias%', '{{alias}}'], $slide->get_val($attr, 'alias'), $text);
		$text = str_replace(['%content%', '{{content}}'], $content, $text);
		$text = str_replace(['%link%', '{{link}}'], $slide->get_val($attr, 'link'), $text);
		$text = str_replace(['%date%', '{{date}}'], $slide->get_val($attr, 'postDate'), $text);
		$text = str_replace(['%date_modified%', '{{date_modified}}'], $slide->get_val($attr, 'dateModified'), $text);
		$text = str_replace(['%author_name%', '{{author_name}}'], $slide->get_val($attr, 'authorName'), $text);
		$text = str_replace(['%author_posts%', '{{author_posts}}'], $slide->get_val($attr, 'authorPostsPage'), $text);
		$text = str_replace(['%author_website%', '{{author_website}}'], $slide->get_val($attr, 'authorPage'), $text);
		$text = str_replace(['%num_comments%', '{{num_comments}}'], $slide->get_val($attr, 'numComments'), $text);
		$text = str_replace(['%catlist%', '{{catlist}}'], $slide->get_val($attr, 'catlist'), $text);
		$text = str_replace(['%catlist_raw%', '{{catlist_raw}}'], $slide->get_val($attr, 'catlist_raw'), $text);
		$text = str_replace(['%taglist%', '{{taglist}}'], $slide->get_val($attr, 'taglist'), $text);
		$text = str_replace(['%id%', '{{id}}'], $post_id, $text);
		
		if(!empty($img_sizes)){
			foreach($img_sizes as $img_handle => $img_name){
				$text = str_replace(['%featured_image_url_'.$img_handle.'%', '{{featured_image_url_'.$img_handle.'}}'],  $slide->get_val($attr, ['img_urls', $img_handle, 'url'], ''), $text);
				$text = str_replace(['%featured_image_'.$img_handle.'%', '{{featured_image_'.$img_handle.'}}'], $slide->get_val($attr, ['img_urls', $img_handle, 'tag'], ''), $text);
				
				//fix for using the lowercase name instead of the handle
				$img_name = strtolower($img_name);
				$img_name = str_replace(' ', '_', $img_name);
				$text = str_replace(['%featured_image_url_'.$img_name.'%', '{{featured_image_url_'.$img_name.'}}'],  $slide->get_val($attr, ['img_urls', $img_handle, 'url'], ''), $text);
				$text = str_replace(['%featured_image_'.$img_name.'%', '{{featured_image_'.$img_name.'}}'], $slide->get_val($attr, ['img_urls', $img_handle, 'tag'], ''), $text);
			}
		}

		//process meta tags:
		$text = str_replace('-', '_REVSLIDER_', $text);
		
		$arrMatches = [];
		preg_match_all('/%meta:\w+%/', $text, $arrMatches);
		
		if(!empty($arrMatches)){
			foreach($arrMatches as $matched){
				foreach($matched as $match){
					$meta = str_replace('%meta:', '', $match);
					$meta = str_replace('%', '',$meta);
					$meta = str_replace('_REVSLIDER_', '-', $meta);
					$metaValue = get_post_meta($post_id, $meta, true);
					
					$text = str_replace($match, $metaValue, $text);	
				}
			}
		}
		
		$arrMatches = [];
		preg_match_all('/{{meta:\w+}}/', $text, $arrMatches);

		if(!empty($arrMatches)){
			foreach($arrMatches as $matched){
				foreach($matched as $match) {
					$meta = str_replace('{{meta:', '', $match);
					$meta = str_replace('}}', '',$meta);
					$meta = str_replace('_REVSLIDER_', '-', $meta);
					$metaValue = get_post_meta($post_id,$meta,true);
					
					$text = str_replace($match,$metaValue,$text);	
				}
			}
		}
		
		$search_keys = ['content' => $content, 'title' => $slide->get_val($attr, 'title'), 'excerpt' => $slide->get_val($attr, 'excerpt')];
		foreach($search_keys as $sk => $svalue){
			$arrMatches = [];
			preg_match_all("/{{".$sk.":\w+[\:]\w+}}/", $text, $arrMatches);
			if(!empty($arrMatches)){
				foreach($arrMatches as $matched){
					foreach($matched as $match) {
						//now check length and type
						
						$meta = str_replace('{{'.$sk.':', '', $match);
						$meta = str_replace('}}', '',$meta);
						$meta = str_replace('_REVSLIDER_', '-', $meta);
						$vals = explode(':', $meta);
						
						if(count($vals) !== 2) continue; //not correct values
						$vals[1] = intval($vals[1]); //get real number
						if($vals[1] === 0 || $vals[1] < 0) continue; //needs to be at least 1 
						
						$metaValue = $slide->cut_text($svalue, $vals[0], $vals[1]);
						if($metaValue === false) continue;
						
						$text = str_replace($match, $metaValue, $text);	
					}
				}
			}
		}

		$arrMatches = [];
		preg_match_all("/{{author_avatar:\w+}}/", $text, $arrMatches);
		if(!empty($arrMatches)){
			foreach($arrMatches as $matched){
				foreach($matched as $match) {
					//now check length and type
					
					$meta = str_replace('{{author_avatar:', '', $match);
					$meta = str_replace('}}', '', $meta);
					$meta = str_replace('_REVSLIDER_', '-', $meta);
					$vals = explode(':', $meta);
					
					if(count($vals) !== 1) continue; //not correct values
					$vals[0] = intval($vals[0]); //get real number
					if($vals[0] === 0 || $vals[0] < 0) continue; //needs to be at least 1 
					
					$avatar = get_avatar_url($slide->get_val($attr, 'authorID'), ['size'=> $vals[0]]);
					
					$text = str_replace($match, $avatar, $text);	
				}
			}
		}
		
		$text = str_replace('_REVSLIDER_','-',$text);
		
		//replace event's template (skip the EM_Event build when the layer has no event_ placeholder)
		if(strpos($text, 'event_') !== false && \RevSliderEventsManager::isEventsExists()){
			$ed = \RevSliderEventsManager::get_event_post_data($post_id);
			if(!empty($ed)){
				foreach($ed as $ek => $ev){
					if($ek == 'start_date' || $ek == 'end_date') $ev = $slide->convert_post_date($ev);
					$text = str_replace(['%event_'.$ek.'%', '{{event_'.$ek.'}}'], $ev, $text);
				}
			}
		}
		
		$text = apply_filters('sr_modify_layer_text', $text, $post_id, $slide);
		
		return $text;
	}
	public function custom_content_types(){
		return get_post_types(['_builtin' => false]);
	}

	public function content_type_label($type){
		$obj_type = get_post_type_object($type);

		return (empty($obj_type)) ? ($type) : $obj_type->labels->singular_name;
	}

	public function content_type_taxonomies($type){
		$tax = get_object_taxonomies(['post_type' => $type], 'objects');

		if(empty($tax)) return [];

		$names	= [];
		foreach($tax ?? [] as $obj_tax){
			if($type === 'product' && !in_array($obj_tax->name, ['product_cat', 'product_tag'])) continue; //WooCommerce ships attribute taxonomies per product that would flood the picker
			$names[$obj_tax->name] = $obj_tax->labels->name;
		}

		return $names;
	}

	public function terms($taxonomy){
		return get_categories(['taxonomy' => $taxonomy, 'number' => 10000, 'hide_empty' => false]);
	}

	public function page_catalog(){
		$pages = get_pages([]);
		$return = [];
		foreach($pages ?? [] as $page){
			if(!$page->post_password) $return[$page->ID] = ['slug' => $page->post_name, 'title' => $page->post_title ? $page->post_title : __('(no title)', 'revslider')];
		}
		return $return;
	}

	public function content_type_catalog(){
		$post_types		= get_post_types(['public' => true, '_builtin' => false], 'objects', 'and');
		$return['post']	= ['slug' => 'post', 'title' => __('Posts', 'revslider')];

		foreach($post_types ?? [] as $post_type){
			$return[$post_type->rewrite['slug']] = ['slug' => $post_type->rewrite['slug'], 'title' => $post_type->labels->name];
			if(in_array($post_type->name, ['post', 'page', 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'custom_changeset', 'user_request'])) continue;

			$taxonomy_objects = get_object_taxonomies($post_type->name, 'objects');
			$return[$post_type->rewrite['slug']]['tax'] = [];
			foreach($taxonomy_objects ?? [] as $name => $tax){
				$return[$post_type->rewrite['slug']]['tax'][$name] = $tax->label;
			}
		}

		return $return;
	}

	public function get_categories_by_id($caller, $ids, $tax = null){
		if(empty($ids)) [];

		$string_ids = (is_string($ids)) ? $ids : implode(',', $ids);
		$args		= ['include' => $string_ids, 'number' => 10000];
		if(!empty($tax)) $args['taxonomy'] = (is_string($tax)) ? explode(',', $tax) : $tax;
		$cats		= get_categories($args);

		return (!empty($cats)) ? $caller->class_to_array($cats) : $cats;
	}

	public function get_shortcode_from_page($caller, $ids){
		$_shortcodes = [];
		$ids		 = (!is_array($ids)) ? (array)$ids : $ids;

		foreach($ids as $id){
			$post = get_post($id);
			$sc = [];
			if(is_a($post, 'WP_Post') && (has_shortcode($post->post_content, 'rev_slider') || has_shortcode($post->post_content, 'sr7'))){
				preg_match_all('/\[sr7.*alias=.(.*)"\]/', $post->post_content, $shortcodes);
				preg_match_all('/\[rev_slider.*alias=.(.*)"\]/', $post->post_content, $shortcodesold);
				if(isset($shortcodes[1]) && $shortcodes[1] !== '') $sc = $shortcodes[1];
				if(isset($shortcodesold[1]) && $shortcodesold[1] !== '') $sc = array_merge($sc, $shortcodesold[1]);

				if(!empty($sc)){
					foreach($sc as $k => $s){
						if(strpos($s, '"') !== false) $s = $caller->get_val(explode('"', $s), 0);
						if(!in_array($s, $_shortcodes)) $_shortcodes[] = $s;
					}
				}
			}
		}

		return $_shortcodes;
	}

	public function get_categories_html($caller, $cat_ids, $tax = null, $post_id = '', $full = false){
		global $wp_rewrite;

		if(!empty($post_id) && $full === false) return get_the_category_list(', ', null, $post_id);

		$categories	= ($full === true && !empty($cat_ids)) ? $cat_ids :  $caller->get_categories_by_id($cat_ids, $tax);
		$errors		= $caller->get_val($categories, 'errors');
		$list		= [];
		$err		= '';
		$rel 		= (is_object($wp_rewrite) && $wp_rewrite->using_permalinks()) ? 'rel="category tag"' : 'rel="category"';

		if(!empty($errors)){
			foreach($errors as $error){
				$err .= implode(',', $error);
			}
			$caller->throw_error(__('retrieving categories error: '.esc_html($err)));
		}

		foreach($categories as $category){
			$link = get_category_link($caller->get_val($category, 'term_id'));
			$name = $caller->get_val($category, 'name');

			$list[] = (!empty($link)) ? '<a href="' . esc_url($link) . '" title="' . esc_attr(sprintf(__('View all posts in %s', 'revslider'), $name)) .'" '. $rel .'>'. $name .'</a>' : $name;
		}

		return (!empty($list)) ? implode(', ', $list) : '';
	}

	public function find_content_ids_containing($needle){
		global $wpdb;

		$ids   = [];
		$pages = $wpdb->get_results($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE `post_content` LIKE %s AND post_status IN ('publish', 'private', 'draft')", '%' . $wpdb->esc_like($needle) . '%'));
		foreach($pages ?? [] as $page){
			$ids[] = $page->ID;
		}

		return $ids;
	}

	/**
	 * the port's closed query vocabulary, translated into the get_posts() call the Add-Ons wrote by
	 * hand until now. Keys outside the vocabulary are ignored rather than forwarded, so nothing
	 * WordPress specific can travel through the port.
	 *
	 * suppress_filters stays 0 on purpose: it is what lets WPML and Polylang narrow the result to the
	 * current language, which the Add-Ons relied on. The status is deliberately not set - the callers
	 * used to pass 'published', which is not a registered status and which WordPress therefore drops,
	 * leaving the default. Setting nothing reproduces that exactly.
	 *
	 * @param array $query see the port
	 * @return int[]
	 */
	public function find_ids($query){
		$get = function($key, $default = null) use ($query){
			return (is_array($query) && isset($query[$key])) ? $query[$key] : $default;
		};

		$args = [
			'post_type'			=> $get('type', 'post'),
			'numberposts'		=> $get('limit', 5),
			'suppress_filters'	=> 0
		];

		//'' from an empty exclude list would become 0, which matches no post - drop it instead
		$exclude = array_values(array_filter(array_map('intval', (array)$get('exclude', []))));
		if(!empty($exclude)) $args['post__not_in'] = $exclude;

		switch($get('order', 'recent')){
			case 'popular':
				$args['orderby'] = 'comment_count';
			break;
			case 'random':
				$args['orderby']	= 'rand';
				$args['order']		= 'ASC';
			break;
			case 'menu':
				$args['orderby'] = 'menu_order date';
			break;
		}

		if($get('relation') === 'same_terms'){
			$taxonomy	= $get('taxonomy');
			$terms		= get_the_terms($get('of'), $taxonomy);
			$slugs		= [];
			if(is_array($terms)){
				foreach($terms as $term){
					$slugs[] = $term->slug;
				}
			}
			//an empty slug list is passed on as it was: it matches nothing, which is what the callers got
			$args['tax_query'] = [['taxonomy' => $taxonomy, 'field' => 'slug', 'terms' => $slugs]];
		}

		$ids = [];
		foreach(get_posts($args) ?? [] as $post){
			$ids[] = (int)$post->ID;
		}

		return $ids;
	}

	/**
	 * WordPress has no cheap "the one before this" for an arbitrary ordering, so this still walks the
	 * list - the same list, the same order and the same taxonomy narrowing the Add-On built by hand,
	 * down to the query-var spelling ('category_name' for posts) rather than a tax_query, because the
	 * two differ on hierarchical taxonomies and the Add-On's results were the ones being kept.
	 *
	 * A host that CAN answer it directly implements this without the walk, which is the whole reason
	 * this is a port method instead of a find_ids() call the caller indexes into.
	 *
	 * @param int   $of
	 * @param array $query see the port
	 * @return array ['prev' => int, 'next' => int]
	 */
	public function adjacent_ids($of, $query = []){
		$of		= (int)$of;
		$type	= (is_array($query) && isset($query['type'])) ? $query['type'] : 'post';
		$tax	= (is_array($query) && isset($query['taxonomy'])) ? $query['taxonomy'] : '';
		$none	= ['prev' => 0, 'next' => 0];
		if($of < 1) return $none;

		$args = [
			'posts_per_page'	=> -1,
			'orderby'			=> 'menu_order date',
			'order'				=> 'ASC',
			'post_type'			=> $type,
			'post_status'		=> 'publish'
		];

		if($tax !== ''){
			$slugs = [];
			$terms = get_the_terms($of, $tax);
			if(is_array($terms)){
				foreach($terms as $term){
					$slugs[] = $term->slug;
				}
			}
			//the query var, not a tax_query: 'category_name' for posts, the taxonomy's own name otherwise
			$args[($type === 'post') ? 'category_name' : $tax] = implode(',', $slugs);
		}

		$ids = [];
		foreach(get_posts($args) ?? [] as $post){
			$ids[] = (int)$post->ID;
		}

		$at = array_search($of, $ids, true);
		if($at === false) return $none;

		return [
			'prev' => isset($ids[$at - 1]) ? $ids[$at - 1] : 0,
			'next' => isset($ids[$at + 1]) ? $ids[$at + 1] : 0
		];
	}

	/**
	 * the streamlined record of one entry, built the way streamline_posts_data() builds a stream
	 * slide's - same taxonomy walk, same image tags, same date formatting - minus what only a slider
	 * can answer (templates, navthumb). Dates are the local post_date/post_modified, which is what
	 * every caller read through get_the_date() until now.
	 *
	 * @param int   $id
	 * @param array $wanted 'meta:<key>', 'author_website', 'author_posts'
	 * @return array|false
	 */
	public function entry($id, $wanted = []){
		$post = get_post((int)$id);
		if(!($post instanceof \WP_Post)) return false;

		$id		= (int)$post->ID;
		$caller	= ($this->slider instanceof \RevSliderFunctions) ? $this->slider : new \RevSliderFunctions();
		$author	= get_user_by('ID', $post->post_author);
		$title	= $post->post_title;
		$tag	= function($src) use ($title, $caller){
			return ($src === false) ? '' : $caller->stream_img_tag($src[0], $title, [$src[1], $src[2]]);
		};

		$thumb_id	= (int)get_post_thumbnail_id($id);
		$full		= ($thumb_id > 0) ? wp_get_attachment_image_src($thumb_id, 'full') : false;
		$thumb		= ($thumb_id > 0) ? wp_get_attachment_image_src($thumb_id, 'thumbnail') : false;

		$ignore	= apply_filters('revslider_slide_ignore_taxonomies', ['post_tag', 'translation_priority', 'language', 'post_translations'], $this->slider);
		$cats	= [];
		foreach(get_object_taxonomies($post->post_type) ?? [] as $taxonomy){
			if(in_array($taxonomy, $ignore, true)) continue;
			$terms = get_the_terms($id, $taxonomy);
			if(!empty($terms) && !is_wp_error($terms)) $cats = array_merge($cats, $terms);
		}
		$catlist = $caller->get_categories_html($cats, null, $id, !empty($cats));
		//a type without post_tag still costs an uncached term query per call; the answer is '' either way
		$taglist = is_object_in_taxonomy($post->post_type, 'post_tag') ? get_the_tag_list('', ',', '', $id) : '';

		$record = [
			'id'			=> $id,
			'type'			=> $post->post_type,
			'alias'			=> $post->post_name,
			'title'			=> $title,
			'excerpt'		=> $post->post_excerpt,
			'content'		=> ['content' => $post->post_content],
			'link'			=> get_permalink($id),
			'author'		=> $caller->get_val($author, ['data', 'display_name'], ''),
			'publish'		=> $caller->convert_post_date($post->post_date),
			'modified'		=> $caller->convert_post_date($post->post_modified),
			'published'		=> ($post->post_status === 'publish'),
			'protected'		=> post_password_required($post), //cookie aware: a visitor who gave the password reads the text, as on the post itself
			'numcomments'	=> (int)$post->comment_count,
			'taglist'		=> (is_string($taglist)) ? $taglist : '',
			'catlist'		=> $catlist,
			'catlistraw'	=> strip_tags($catlist),
			'media'			=> ($full !== false) ? $full[0] : '',
			'mediatag'		=> $tag($full),
			'thumb'			=> ($thumb !== false) ? $thumb[0] : '',
			'thumbtag'		=> $tag($thumb),
			'img_urls'		=> [],
			'meta'			=> []
		];

		if($thumb_id > 0){
			foreach(array_merge(get_intermediate_image_sizes(), ['full']) as $size){
				$src = wp_get_attachment_image_src($thumb_id, $size);
				if($src === false) continue;
				$record['img_urls'][$size] = ['url' => $src[0], 'tag' => $tag($src)];
			}
		}

		//a product carries its commerce fields too, the wc_* values a product slide resolves; the Add-Ons
		//that shared or featured a product each rebuilt them from the WooCommerce API
		if(in_array($post->post_type, ['product', 'product_variation'], true) && class_exists('\RevSliderWooCommerce') && \RevSliderWooCommerce::woo_exists()){
			$wc = \RevSliderWooCommerce::get_wc_data($id);
			if(is_array($wc)) $record = array_merge($record, $wc);
		}

		foreach((array)$wanted as $want){
			if(strpos($want, 'meta:') === 0){
				$key = substr($want, 5);
				if($key !== '') $record['meta'][$key] = get_post_meta($id, $key, true);
			}elseif($want === 'author_website'){
				$record['author_website'] = $caller->get_val($author, 'user_url', '');
			}elseif($want === 'author_posts'){
				$record['author_posts'] = get_author_posts_url($post->post_author);
			}
		}

		return $record;
	}

	//url_to_postid() answers a post type's ?<query_var>=slug on plain permalinks with 0, or with the front page when there is one
	public function id_by_url($url){
		$url	= (string)$url;
		$id		= (int)url_to_postid($url);
		if($id > 0 && ($id !== (int)get_option('page_on_front') || get_permalink($id) === $url)) return $id;

		parse_str((string)parse_url($url, PHP_URL_QUERY), $query);
		foreach(get_post_types(['public' => true], 'objects') as $type){
			if(empty($type->query_var) || empty($query[$type->query_var]) || !is_string($query[$type->query_var])) continue;
			$post = get_page_by_path($query[$type->query_var], OBJECT, $type->name);
			if(!empty($post)) return (int)$post->ID;
		}
		return 0;
	}

	public function entry_text($id){
		$post = get_post($id);
		if(!is_a($post, 'WP_Post')) return '';

		$text = $post->post_content;
		foreach(get_post_meta($id) as $values){
			foreach((array)$values as $v) if(is_string($v)) $text .= "\n".$v;
		}

		return $text;
	}

	public function term_keys($list, $taxonomy){
		$out = [];
		foreach(explode(',', (string)$list) as $item){
			$item = trim($item);
			if($item === '') continue;
			if(preg_match('/^(.+)_(\d+)$/', $item, $m) && taxonomy_exists($m[1])){
				$out[] = $item;
				continue;
			}
			$term = is_numeric($item) ? get_term((int)$item, $taxonomy) : get_term_by('slug', sanitize_title($item), $taxonomy);
			if($term instanceof \WP_Term) $out[] = $term->taxonomy . '_' . $term->term_id;
		}

		return array_values(array_unique($out));
	}
}
