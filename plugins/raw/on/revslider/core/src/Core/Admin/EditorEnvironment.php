<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Core\Admin;

use ThemePunch\SR\Core\Functions;
use ThemePunch\SR\Core\Registry;
use ThemePunch\SR\Core\Slots;

if(!defined('ABSPATH')) exit();

/**
 * Everything host-sourced the editor header injects into SR7.E. The values every host derives the same way come
 * from the ports here; the few only the host knows are its subclass's to answer (RevSliderEditorEnvironment on
 * WordPress), which Data::editor_environment() hands out.
 */
abstract class EditorEnvironment {

	/** @return array the SR7.E values the header prints verbatim */
	public function values(){
		$env		= $this->functions()->environment();
		$meta_edit	= $env->user_can(Slots::CAP);

		return [
			'upload_url'	=> $this->upload_url(),
			'nonce'			=> $this->nonce(),
			'admin_url'		=> $this->admin_url(),
			'preview_url'	=> $env->rest_url() . 'sliderrevolution/sliders/preview/',
			'ajaxurl'		=> $env->ajax_url(),
			'resturl'		=> $env->rest_url(),
			'rtl'			=> $this->rtl(),
			'woocommerce'	=> $this->shop_active(),
			'addon_presets'	=> Functions::get_addon_presets(), //the add-ons with a <slug>.presets.js, see SR7.B.addOnAssets()
			'starts'		=> Functions::get_block_starts(), //ways to begin a module an add-on brings, see SR7.B.starts
			'wp_plugin_url'	=> $this->plugin_root_url(), //base URL the editor builds ADDON asset URLs from
			'debug'			=> $this->debug(), //dev hosts cache-bust dynamically loaded editor scripts per load
			'effects_server'=> !empty($this->functions()->option_storage()->get(Functions::OPTIONS_EFFECTS, [])), //the Tools server's tile lists are on this site; false = the library fetches them on its first opening
			'meta_edit'		=> $meta_edit, //Meta Editing (layer roles, template test status): designers, support, developers
			'meta_bug_url'	=> $meta_edit ? esc_url(apply_filters('revslider_meta_bug_url', defined('RS_META_BUG_URL') ? RS_META_BUG_URL : '')) : '', //where a tester files a template bug (a Monday board), '' = copy the context only
			'meta_monday'	=> $meta_edit && Slots::monday_enabled(), //Report bug creates the Monday item itself (RS_MONDAY_TOKEN on this site)
			'update_hint'	=> $this->update_hint(),
		];
	}

	/**
	 * Where the plugin is updated, for a host the editor cannot update it on. WordPress installs the new version
	 * from the dashboard itself, so it says nothing here and SR7.system.updateCore does what it always did; a host
	 * whose extensions are managed somewhere else answers, and the editor shows that instead of trying.
	 * @return array ['title' => string, 'text' => string, 'label' => string, 'url' => string], [] when the editor
	 *               updates the plugin itself
	 * @since 7.2.1
	 */
	public function update_hint(){
		return [];
	}

	/** @return string the base URL of the host's uploads, as the host reports it */
	public function upload_url(){
		return $this->functions()->paths()->upload_dir()['baseurl'] ?? '';
	}

	/** @return string the CSRF token every editor request carries */
	public function nonce(){
		return $this->functions()->environment()->csrf_token('revslider_actions');
	}

	/** @return string the host's Slider Revolution admin page */
	abstract public function admin_url();

	/** @return bool the host admin runs right to left */
	abstract public function rtl();

	/** @return bool a shop is installed the editor offers product sources for */
	abstract public function shop_active();

	/** @return string the URL the add-on folders sit under */
	public function plugin_root_url(){
		return $this->functions()->paths()->plugins_url();
	}

	/** @return bool a development host */
	abstract public function debug();

	/** @return Functions the ports, reached like every static-free core helper */
	protected function functions(){
		return Registry::instance()->get(Functions::class);
	}
}
