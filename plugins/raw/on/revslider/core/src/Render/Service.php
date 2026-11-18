<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Render;

if(!defined('ABSPATH')) exit();

/**
 * The platform-neutral render entry: hosts hand in an alias and options and get strings
 * and a manifest back; enqueue and placement stay host decisions.
 * @since 7.2.1
 */
class Service {

	/**
	 * run one module through the markup pass and hand back its html: the platform-neutral
	 * middle of rev_slider_shortcode(); option keys mirror the shortcode attributes
	 * @since 7.2.1
	 * @param string|int $alias_or_id
	 * @param array $options order, settings, skin, post_ids, category, taxonomy, gallery_ids, usage, fullheight, fullwidth, offset, modal
	 * @param \RevSlider7Output|null $output a prepared output instance, or null for a fresh one
	 * @return array ['html' => string, 'slider' => \RevSliderSlider|false, 'output' => \RevSlider7Output]
	 */
	public function render_module_html($alias_or_id, $options = [], $output = null){
		$output = ($output === null) ? new \RevSlider7Output() : $output;

		$output->set_custom_order($options['order'] ?? '');
		$output->set_custom_settings($options['settings'] ?? '');
		$output->set_custom_skin($options['skin'] ?? '');
		$source = array_intersect_key((array)$options, ['post_ids' => 1, 'category' => 1, 'taxonomy' => 1]);
		if(!empty($source)) $output->set_custom_source($source); //none given keeps a source the caller set on $output
		if(($options['gallery_ids'] ?? false) !== false) $output->set_gallery_ids($options['gallery_ids']);

		$slider  = false;
		$content = '';
		ob_start();
		try{
			$output->set_usage($options['usage'] ?? '');
			$output->set_fullheight($options['fullheight'] ?? '');
			$output->set_fullwidth($options['fullwidth'] ?? '');
			$output->set_offset($options['offset'] ?? '');
			$modal = (empty($options['modal'] ?? '')) ? 'true' : $options['modal'];
			if(($options['usage'] ?? '') === 'modal') $output->set_modal($modal);
			$slider	 = $output->add_slider_to_stage($alias_or_id);
			$content = ob_get_contents();
		}finally{
			ob_end_clean(); //close the buffer even if rendering throws, otherwise the partial markup leaks into the page
		}

		return ['html' => $content, 'slider' => $slider, 'output' => $output];
	}

	/**
	 * a whole page's worth of one module, in page order: head block, module markup, footer
	 * script, and the asset manifest the host needs for its enqueue and placement decisions.
	 * The manifest is built last, after the markup pass collected fonts and icon flags.
	 * @since 7.2.1
	 * @param string|int $alias_or_id
	 * @param array $options see render_module_html()
	 * @param \RevSlider7Output|null $output a prepared output instance, or null for a fresh one
	 * @return Result
	 */
	public function render($alias_or_id, $options = [], $output = null){
		$page	= \RevSliderGlobals::instance()->get('RevSliderFunctions')->render_page();
		$result	= new Result();

		$result->head_html	= $page->get_header_html();
		$rendered			= $this->render_module_html($alias_or_id, $options, $output);
		$result->body_html	= $rendered['html'];
		$result->footer_js	= $rendered['output']->get_footer_js();
		$result->assets		= $page->build_asset_manifest();

		return $result;
	}
}
