<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * The WordPress halves of the one-time upgrade routines: legacy addon plugins are deactivated and
 * deleted through WP's plugin machinery, and the v6 slide_template postmeta migration acts on WP
 * posts. RevSliderPluginUpdate/RevSliderPluginUpdateV6 keep delegating shells for their frozen API.
 */
class RevSliderWpUpgradeGlue {

	/** @return void */
	public static function do_remove_addon_checks(){
		// execute only in admin for a user that has permissions to activate/delete plugins
		// skip ajax requests
		if ( wp_doing_ajax() || ! is_admin() ) return;
		if ( wp_doing_cron() || (defined('WP_CLI') && WP_CLI) ) return;
		if ( ! current_user_can('delete_plugins') || ! current_user_can('install_plugins') ) return;

		/* @var $rsa RevSliderAddons */
		$rsa = RevSliderGlobals::instance()->get('RevSliderAddons');

		//bail before loading wp-admin/includes/file.php once the one-time addon removal has already run
		if ( $rsa->get_options(['system', 'addons_remove'], false) ) return;

		if (!function_exists('request_filesystem_credentials')) {
			require_once(ABSPATH . 'wp-admin/includes/file.php');
		}

		$flush     = false;
		$to_remove = $rsa->get_addons_to_remove();
		foreach ($to_remove as $old ){
			$old_full = $old.'/'.$old.'.php';
			if ( is_wp_error( validate_plugin( $old_full ) ) ) continue;
			deactivate_plugins( $old_full );
			delete_plugins( [ $old_full ] );
			$flush = true;
		}

		$rsa->update_option(['system', 'addons_remove'], true);

		if ( $flush ) {
			// at least one addon was migrated, so flush the WP cache
			$rsa->flush_wp_cache();
		}
	}

	/**
	 * A number of addons have updated slug.
	 * Check and update them if needed.
	 * @return void
	 */
	public static function do_update_addon_checks(){
		// execute only in admin for a user that has permissions to activate/delete plugins
		// skip ajax requests
		if ( wp_doing_ajax() || ! is_admin() ) return;
		if ( wp_doing_cron() || (defined('WP_CLI') && WP_CLI) ) return;
		if ( ! current_user_can('delete_plugins') || ! current_user_can('install_plugins') ) return;

		/* @var $rsa RevSliderAddons */
		$rsa = RevSliderGlobals::instance()->get('RevSliderAddons');
		if ( $rsa->get_options(['system', 'addons_migration'], false) ) return;

		// SR is not activated, addons install fail
		if($rsa->_truefalse($rsa->get_options(['system', 'valid'], 'false')) !== true) return;

		if (!function_exists('request_filesystem_credentials')) {
			require_once(ABSPATH . 'wp-admin/includes/file.php');
		}

		$al  = $rsa->get_addon_list();
		if (empty($al) || empty($al['revslider-domainswitch-addon'])){
			// no addons or old addons list, force an update
			$update = new RevSliderUpdate(RS_REVISION);
			$update->force = true;
			$update->_retrieve_version_info();
			$al = $rsa->get_addon_list();
			if (empty($al) || empty($al['revslider-domainswitch-addon'])){
				// server error?
				return;
			}
		}

		$flush = false;
		$a2m   = $rsa->get_addons_to_migrate();
		foreach ($a2m as $old => $new ){
			$old_full = $old.'/'.$old.'.php';
			if ( is_wp_error( validate_plugin( $old_full ) ) ) continue;

			deactivate_plugins( $old_full );
			if ( true === delete_plugins( [ $old_full ] ) ) {
				$rsa->install_addon($new);
				$flush = true;
			}
		}

		$rsa->update_option(['system', 'addons_migration'], true);

		if ( $flush ) {
			// at least one addon was migrated, so flush the WP cache
			$rsa->flush_wp_cache();
			wp_safe_redirect( admin_url('admin.php?page=revslider') );
			exit;
		}
	}

	/**
	 * WPML integration: when the WPML plugin is active, make sure the
	 * AI Translate add-on is installed and activated. Runs only once.
	 * @return void
	 */
	public static function do_wpml_addon_check(){
		// execute only in admin for a user that has permissions to activate plugins, skip ajax/cron
		if ( wp_doing_ajax() || ! is_admin() ) return;
		if ( wp_doing_cron() || (defined('WP_CLI') && WP_CLI) ) return;
		if ( ! current_user_can('activate_plugins') || ! current_user_can('install_plugins') ) return;

		// WPML not active, nothing to do
		$rsfa = new RevSliderFunctionsAdmin();
		if ( ! $rsfa->is_wpml_active() ) return;

		/* @var $rsa RevSliderAddons */
		$rsa = RevSliderGlobals::instance()->get('RevSliderAddons');

		// only run the auto-activation once
		if ( $rsa->_truefalse($rsa->get_options(['system', 'wpml_addon_init'], 'false')) === true ) return;

		// SR is not activated, addon install would fail
		if ( $rsa->_truefalse($rsa->get_options(['system', 'valid'], 'false')) !== true ) return;

		if ( ! function_exists('request_filesystem_credentials') ) {
			require_once(ABSPATH . 'wp-admin/includes/file.php');
		}

		$addon = $rsa->get_addon_data('revslider-aitranslate-addon');
		if ( $addon === false || $rsa->_truefalse($rsa->get_val($addon, 'active', false)) !== true ) {
			$rsa->install_addon('revslider-aitranslate-addon');
			$rsa->clear_addon_list();
		}

		// mark as done so we never auto-activate again (respect manual deactivation afterwards)
		$rsa->update_option(['system', 'wpml_addon_init'], true);
	}

	/**
	 * force-inject this plugin's update into WP's update_plugins site transient
	 * @param RevSliderUpdate $update
	 * @return void
	 */
	public static function force_update_transient($update){
		$transient = get_site_transient('update_plugins');
		$rs_t = $update->set_update_transient($transient);
		if(!empty($rs_t)) set_site_transient('update_plugins', $rs_t);
	}

	/**
	 * get in all posts the slide_template variable and migrate to slide_template_v7
	 * $slide_id needs to be a v6 slide id
	 * @param RevSliderPluginUpdateV6 $upd owns the v6-to-v7 slide map
	 * @return void
	 **/
	public static function update_post_slide_template_v7($upd, $slide_id = false){
		global $wpdb;

		if($slide_id !== false){
			$results = $wpdb->get_results($wpdb->prepare("SELECT post_id, meta_value FROM ".$wpdb->postmeta." WHERE meta_key = 'slide_template' AND meta_value = %s", $slide_id), 'ARRAY_A');
		}else{
			$results = $wpdb->get_results("SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = 'slide_template'", 'ARRAY_A');
		}

		foreach($results ?? [] as $row){
			$slide_template_v7 = $upd->get_v7_slide_map($row['meta_value']);
			if($slide_template_v7 !== false){
				update_post_meta($row['post_id'], 'slide_template_v7', $slide_template_v7);
			}else{
				delete_post_meta($row['post_id'], 'slide_template_v7');
			}
		}
	}
}
