<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

namespace ThemePunch\SR\Core;

if(!defined('ABSPATH')) exit();

/**
 * Internal slider output cache.
 *
 * Rendered slider HTML is stored in transients (revslider_slider_<id>_...). Everything that cannot live
 * inside that cached HTML - hook callbacks, footer scripts, global CSS/font collections - is recorded as an
 * "addition" while rendering and stored alongside it; on a cache hit do_additions() replays those, so the
 * page behaves as if the slider had been rendered live.
 */
class Cache extends Functions {

	/** @var bool the "internal cache" switch from the global settings */
	public $cache_enabled = false;

	/** @var int how long a cached module stays valid, in seconds */
	public $cache_lifetime = WEEK_IN_SECONDS;

	/**
	 * the SR_GLOBALS branches a render contributes to: the footer JS, the fonts and the nav definitions of a
	 * module live there, not in its markup, so a cache hit has to put them back
	 **/
	private $global_branches = ['collections', 'fonts', 'icon_sets'];

	/** key the enqueued script/style handles travel under, next to those branches */
	const ENQUEUED = '#enqueued';

	/**
	 * additions recorded during rendering, keyed by kind:
	 * 'action'/'filter' => [hook name => html chunks], 'html' => plain chunks, 'special' => global state
	 **/
	private $cache_additions = ['action' => [], 'filter' => [], 'html' => [], 'special' => []];

	/** @var array html chunks queued per hook name, printed by print_addition() */
	private $output_html = [];

	public function __construct(){
		$globals = $this->get_global_settings();
		$this->cache_enabled = ($this->_truefalse($this->get_val($globals, ['opt', 'intcache'])) === true) ? true : false;

		//a 0 or a junk value would make set_transient() keep the entry forever; the week is the pre-7.2.0 behaviour
		$lifetime = intval($this->get_val($globals, ['opt', 'icachetime'], WEEK_IN_SECONDS));
		$this->cache_lifetime = ($lifetime > 0) ? min($lifetime, YEAR_IN_SECONDS) : WEEK_IN_SECONDS;
	}


	/**
	 * current state of the branches a render contributes to
	 * @since: 7.2.0
	 * @return array
	 **/
	public function snapshot_globals(){
		global $SR_GLOBALS;

		$snapshot = [];
		foreach($this->global_branches as $branch) $snapshot[$branch] = $this->get_val($SR_GLOBALS, $branch, []);

		//an add-on asks for its front script while the module renders, so a hit has to ask for the same ones again
		//which handles are queued is host business, the WP loader answers; a host without a queue keeps both lists empty
		$snapshot[self::ENQUEUED] = (array)apply_filters('revslider_cache_queued_assets', ['js' => [], 'css' => []]);

		return $snapshot;
	}


	/**
	 * what this render added to those branches, measured against a snapshot taken before it
	 * @since: 7.2.0
	 * @param array $before snapshot_globals() from before the render
	 * @return array
	 **/
	public function diff_globals($before){
		$diff = [];
		foreach($this->snapshot_globals() as $branch => $after){
			if($branch === self::ENQUEUED){
				$was	= $this->get_val($before, $branch, ['js' => [], 'css' => []]);
				$delta	= [
					'js'	=> array_values(array_diff($after['js'], $this->get_val($was, 'js', []))),
					'css'	=> array_values(array_diff($after['css'], $this->get_val($was, 'css', [])))
				];
				if(!empty($delta['js']) || !empty($delta['css'])) $diff[$branch] = $delta;
				continue;
			}

			$delta = $this->diff_recursive($after, $this->get_val($before, $branch, []));
			if(!empty($delta)) $diff[$branch] = $delta;
		}

		return $diff;
	}


	/**
	 * keys $after holds that $before did not, recursively
	 * @return array
	 **/
	private function diff_recursive($after, $before){
		if(!is_array($after)) return [];
		$before	= (is_array($before)) ? $before : [];
		$diff	= [];

		foreach($after as $key => $value){
			if(!array_key_exists($key, $before)){
				$diff[$key] = $value;
			}elseif(is_array($value) && is_array($before[$key])){
				$sub = $this->diff_recursive($value, $before[$key]);
				if(!empty($sub)) $diff[$key] = $sub;
			}elseif($value !== $before[$key]){
				$diff[$key] = $value;
			}
		}

		return $diff;
	}


	/**
	 * how long a cached module stays valid, in seconds
	 * @since: 7.2.0
	 * @return int
	 **/
	public function get_lifetime(){
		return $this->cache_lifetime;
	}


	/**
	 * is the internal slider cache switched on?
	 * @return bool
	 **/
	public function is_enabled(){
		return $this->cache_enabled;
	}


	/**
	 * can sliders of this source type be cached? social streams cannot
	 * @return bool
	 **/
	public function is_supported_type($type){
		return (in_array($type, ['post', 'posts', 'specific_posts', 'specific_post', 'current_post', 'woocommerce', 'woo', 'gallery'], true)) ? true : false;
	}

	/**
	 * delete the cached output of every slider (value and timeout rows)
	 * @return int|false deleted value rows, false on a database error
	 **/
	public function clear_all_transients(){
		return $this->ttl_cache()->delete_by_prefix('revslider_slider_');
	}


	/**
	 * delete the cached output of one slider
	 * @since: 6.4.7
	 * @return int|false deleted value rows, false when $sid is not a positive id
	 **/
	public function clear_transients_by_slider($sid){
		$sid = intval($sid);
		if($sid <= 0) return false;

		//no separator after the id on purpose, mirroring the old LIKE pattern: '..._5' also swept '..._55'
		return $this->ttl_cache()->delete_by_prefix('revslider_slider_' . $sid);
	}
	
	
	/**
	 * delete the cached output of multiple sliders in two queries instead of 2×N
	 * @since: 6.4.8
	 * @param int[] $ids slider ids; non-numeric entries are dropped
	 * @return int|false deleted value rows, false when no valid id remains
	 **/
	public function clear_transients_by_sliders($ids = []){
		$ids = array_unique(array_filter(array_map('intval', $ids), function($id){ return $id > 0; }));
		if(empty($ids)) return false;

		$prefixes = array_map(function($id){ return 'revslider_slider_' . $id; }, $ids);

		return $this->ttl_cache()->delete_by_prefix($prefixes);
	}


	/**
	 * the additions recorded so far (see the class docblock)
	 * @return array
	 **/
	public function get_additions(){
		return $this->cache_additions;
	}


	/**
	 * record something that must be replayed on a cache hit
	 *
	 * @param string       $type     'action' | 'filter' | 'html' | 'special'
	 * @param string|false $name     hook name (action/filter) or key (special); false appends to a flat list
	 * @param mixed        $output   html chunk, or the value to restore for 'special'
	 * @param int          $priority output order within the same hook
	 * @return void
	 **/
	public function add_addition($type, $name = false, $output = '', $priority = 10){
		if($output === '') return;
		
		if(!isset($this->cache_additions[$type])) $this->cache_additions[$type] = [];
		
		if($name === false){
			$this->cache_additions[$type][] = $output;
		}else{
			if(!isset($this->cache_additions[$type][$name])) $this->cache_additions[$type][$name] = [];
			
			if($type === 'special'){
				$this->cache_additions[$type][$name][] = $output;
			}else{
				$this->cache_additions[$type][$name][] = [
					'html' => $output,
					'priority' => $priority
				];
			}
		}
	}
	
	/**
	 * hook for replacing per-request placeholders in cached HTML before it is echoed - currently a plain
	 * pass-through, the one replacement it ever did is commented out below
	 * @return string
	 **/
	public function do_html_changes($html){
		//$html = str_replace('##NONCE##', wp_create_nonce('RevSlider_Front'), $html);

		return $html;
	}

	/**
	 * replay the recorded additions on a cache hit: re-register the action/filter output through
	 * print_addition() and restore the 'special' entries (css collection, icon font flags) into $SR_GLOBALS
	 * @since: 6.4.7
	 * @param array             $additions the 'addition' part of the stored transient
	 * @param RevSlider7Output  $output    passed through to the revslider_do_cache_additions hook only
	 * @return void
	 **/
	public function do_additions($additions, $output){
		$t_actions = $this->get_val($additions, 'action', []);
		if(!empty($t_actions)){
			foreach($t_actions as $_action => $t_a){
				if(!empty($t_a)){
					
					foreach($t_a as $t_sa){
						if(!isset($this->output_html[$_action])) $this->output_html[$_action] = [];
						$this->output_html[$_action][] = $t_sa;
						add_action($_action, [$this, 'print_addition']);
					}
				}
			}
		}
		
		$t_filters = $this->get_val($additions, 'filter', []);
		if(!empty($t_filters)){
			foreach($t_filters as $_filter => $t_a){
				if(!empty($t_a)){
					foreach($t_a as $t_sa){
						if(!isset($this->output_html[$_filter])) $this->output_html[$_filter] = [];
						$this->output_html[$_filter][] = $t_sa;
						add_filter($_filter, [$this, 'print_addition']);
					}
				}
			}
		}
		
		$t_special = $this->get_val($additions, 'special', []);
		if(!empty($t_special)){
			$_globals = $this->get_val($t_special, 'globals', []);
			if(!empty($_globals)){
				global $SR_GLOBALS;
				foreach($_globals as $delta){
					foreach($delta as $branch => $value){
						if($branch === self::ENQUEUED){
							do_action('revslider_cache_queue_assets', $this->get_val($value, 'js', []), $this->get_val($value, 'css', [])); //the host queues them again
							continue;
						}

						$SR_GLOBALS[$branch] = array_replace_recursive($this->get_val($SR_GLOBALS, $branch, []), $value);
					}
				}
			}
			$_rs_css_collection = $this->get_val($t_special, 'rs_css_collection', []);
			if(!empty($_rs_css_collection)){
				global $SR_GLOBALS;
				$SR_GLOBALS['collections']['css'] = $_rs_css_collection;
			}
			$_font_var = $this->get_val($t_special, 'font_var', []);
			if(!empty($_font_var)){
				global $SR_GLOBALS;
				foreach($_font_var as $fw){
					if(!isset($SR_GLOBALS['icon_sets'][$fw])) $SR_GLOBALS['icon_sets'][$fw] = ['css' => false, 'parsed' => false];
					$SR_GLOBALS['icon_sets'][$fw]['css'] = true;
				}
			}
		}
		
		do_action('revslider_do_cache_additions', $additions, $output);
	}
	
	
	/**
	 * store rendered html plus the recorded additions as one transient, then reset the buffer
	 * @param string $transient transient name
	 * @param int    $sid       unused; kept for callers outside the plugin
	 * @param string $content   rendered slider html
	 * @return void
	 **/
	public function set_full_transient($transient, $sid, $content){ //written by Output::add_slider_base() once the fonts are queued
		$add = [
			'html' => $content,
			'addition' => $this->get_additions()
		];
		
		$add = json_encode($add);
		$this->ttl_cache()->set($transient, $add, $this->cache_lifetime);
		
		$this->cache_additions = [];
	}
	
	/**
	 * echo the html queued for the currently running hook, ordered by priority; the footer scripts hook
	 * gets a <script> wrapper
	 * @since: 6.4.7
	 * @return void
	 **/
	public function print_addition(){
		$html = $this->get_val($this->output_html, current_filter());
		if(is_array($html)){
			if(!empty($html)){
				usort($html, [$this, 'sort_by_priority']);
				echo (current_filter() === 'wp_print_footer_scripts') ? self::script_open()."\n" : '';
				foreach($html as $echo){
					echo $this->get_val($echo, 'html');
				}
				echo (current_filter() === 'wp_print_footer_scripts') ? RS_T.'</script>'."\n" : '';
			}
		}else{
			echo $html;
		}
	}
	
	/**
	 * runs on save_post/publish: clears the cache of every slider with a post-based source, since the
	 * changed post may appear in their output. gallery sliders are untouched
	 * @return void
	 **/
	public function check_for_post_transient_deletion(){
		if($this->cache_enabled === false) return;

		$post_types = [];
		foreach(['post', 'posts', 'specific_posts', 'specific_post', 'current_post', 'woocommerce', 'woo'] as $pt){
			$post_types[] = '"sourcetype":"'.$pt.'"';		//v6 shape
			$post_types[] = '"source":{"type":"'.$pt.'"';	//v7 shape
		}
		$_slider = Registry::instance()->get('RevSliderSlider');
		
		$slider = $_slider->get_slider_by_param_string($post_types, true);
		
		//clear cache for all of these sliders in a single bulk operation
		if(!empty($slider) && is_array($slider)){
			$_self = $this;
			$ids   = array_map(function($s) use($_self){ return $_self->get_val($s, 'id'); }, $slider);
			$this->clear_transients_by_sliders($ids);
		}
	}
	
	
	/**
	 * usort callback for print_addition()
	 * @return int
	 **/
	public function sort_by_priority($a, $b) {
		return $a['priority'] - $b['priority'];
	}
}