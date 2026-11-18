<?php
/**
 * @package RevSliderExtension
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.sliderrevolution.com/
 * @copyright 2024 ThemePunch
 */
 
if(!defined('ABSPATH')) exit();

/**
 * Essential Grid integration.
 *
 * Lets an Essential Grid item open a slider in its ajax lightbox: the slider is offered as a media source and
 * picked per post in Essential Grid's meta box (eg_sources_revslider, which Essential Grid saves itself). Essential
 * Grid fetches and shows the slider on its own. Inert without Essential Grid.
 */
class RevSliderExtension {
	
	public function __construct() {
		$this->init_essential_grid_extensions();
	}
	
	
	/***************************
	 * Setup part for Revslider inclusion into Essential Grid
	 ***************************/
	
	/**
	 * Do all initializations for RevSlider integration
	 * @return bool|void false when Essential Grid is not installed
	 */
	public function init_essential_grid_extensions(){
		
		if(!class_exists('Essential_Grid')) return false; //only add if Essential Grid is installed
		
		add_filter('essgrid_set_ajax_source_order', [$this, 'add_slider_to_eg_ajax']);
		add_filter('essgrid_handle_ajax_content', [$this, 'set_slider_values_to_eg_ajax'], 10, 4);
		add_action('essgrid_add_meta_options', [$this, 'add_eg_additional_meta_field']);
	}
	
	
	/**
	 * Add Slider to the List of choosable media
	 * @return array $media with our own source appended
	 */
	public function add_slider_to_eg_ajax($media){
		
		$media['revslider'] = ['name' => __('Slider Revolution', 'revslider'), 'type' => 'ccw'];
		
		return $media;
	}
	
	
	/**
	 * builds the data-ajax* attributes Essential Grid needs to load the slider chosen for this post
	 * @return string|false false for other media handles or when no slider was chosen
	 */
	public function set_slider_values_to_eg_ajax($handle, $media_sources, $post, $grid_id){
		
		if($handle !== 'revslider') return false;
		if(empty($post['ID'])) return false; //an Essential Grid custom item comes as false, it has no post to read the slider from
		
		$slider_source	= '';
		$values			= get_post_custom($post['ID']);
		
		if(isset($values['eg_sources_revslider'])){
			if(isset($values['eg_sources_revslider'][0])){
				$slider_source = (isset($values['eg_sources_revslider'][0])) ? $values['eg_sources_revslider'][0] : '';
			}else{
				$slider_source = (isset($values['eg_sources_revslider'])) ? $values['eg_sources_revslider'] : '';
			}
		}
		
		return ($slider_source === '') ? false : ' data-ajaxtype="'.$handle.'" data-ajaxsource="'.esc_attr($slider_source).'"';
	}
	
	
	/**
	 * Adds custom meta field into the essential grid meta box for post/pages
	 * prints a slider picker into Essential Grid's meta box
	 * @return void
	 */
	public function add_eg_additional_meta_field($values){
		
		$sld		= new RevSliderSlider();
		$sliders	= $sld->get_sliders();
		$shortcodes = [];
		foreach($sliders ?? [] as $slider){
			$name = $slider->get_param('shortcode','false');
			if($name != 'false') $shortcodes[$slider->get_id()] = $name;
		}
		
		$selected_slider = (isset($values['eg_sources_revslider'])) ? $values['eg_sources_revslider'] : '';
		if($selected_slider == '') $selected_slider = [0 => ''];
		?>
		<p><strong style="font-size:14px"><?php _e('Choose Revolution Slider', 'revslider'); ?></strong></p>
		<p>
			<select name="eg_sources_revslider" id="eg_sources_revslider">
				<option value=""<?php selected($selected_slider[0], ''); ?>><?php _e('--- Choose Slider ---', 'revslider'); ?></option>
				<?php
				foreach($shortcodes ?? [] as $id => $name){
					?>
					<option value="<?php echo esc_attr($id); ?>"<?php selected($selected_slider[0], $id); ?>><?php echo esc_html($name); ?></option>
					<?php
				}
				?>
			</select>
		</p>
		<?php
	}
	
}

//only instantiate when Essential Grid is present - the class does nothing else, so on non-EG sites
//(the majority) this skips the object + constructor work on every page load
global $revext;
if(class_exists('Essential_Grid')) $revext = new RevSliderExtension();