<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Yoast SEO and Rank Math integration.
 *
 * Both analyse the post content as the editor holds it, where a module is a shortcode or a block, so nothing
 * a module says ever reached their checks. core/admin/assets/js/tools/seo.js hands them the module's text, which
 * the core builds (ThemePunch\SR\Core\Seo). Inert without one of the two plugins.
 */
class RevSliderSEO extends \ThemePunch\SR\Core\Seo {
	const HANDLE = 'sr7-seo';

	public function __construct(){
		if(!is_admin()) return;
		//late, so the Rank Math analyzer handle is registered by the time it is named as a dependency
		add_action('admin_enqueue_scripts', [$this, 'enqueue'], 20);
	}

	/**
	 * @return bool
	 */
	public static function active(){
		return defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION');
	}

	/**
	 * The bridge script, on the post editor only and only with one of the two plugins present
	 * @return void
	 */
	public function enqueue(){
		global $pagenow;
		if(!self::active() || !in_array($pagenow, ['post.php', 'post-new.php'], true) || !current_user_can('edit_posts')) return;

		$deps = ['jquery', 'wp-hooks'];
		if(defined('RANK_MATH_VERSION') && wp_script_is('rank-math-analyzer', 'registered')) $deps[] = 'rank-math-analyzer';
		wp_enqueue_script(self::HANDLE, RS_PLUGIN_URL_CLEAN . 'core/admin/assets/js/tools/seo.js', $deps, self::asset_time('core/admin/assets/js/tools/seo.js'), true);
		wp_localize_script(self::HANDLE, 'SR7SEO', [
			'ajaxurl'	=> admin_url('admin-ajax.php'),
			'nonce'		=> wp_create_nonce('revslider_actions'),
			'yoast'		=> defined('WPSEO_VERSION'),
			'rankmath'	=> defined('RANK_MATH_VERSION')
		]);
	}
}
