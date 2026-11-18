<?php
/**
 * Layer meta on WordPress: who may edit it and the ?rs_meta switch. The rows, the lexicon and everything done with
 * them are the core's (ThemePunch\SR\Core\Slots, classic name RevSliderSlots).
 *
 * @author    ThemePunch <info@themepunch.com>
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

class RevSliderWpSlots {

	//The production switch: a plugin admin who opens any SR7 admin page with ?rs_meta=<token> becomes a meta editor
	//(per user, stored in user meta), ?rs_meta=off takes it away. Only the token's sha256 ships; rotate it here.
	const TOKEN_HASH	= 'ef8e77b0bd9846defd38a5e5e36104042168c62f3a5f7a4a1fa13317b489e21b';
	const USER_FLAG		= 'revslider_meta_editor';

	/**
	 * wire the capability grant
	 * @return void
	 */
	public static function init(){
		add_filter('user_has_cap', ['RevSliderWpSlots', 'grant_cap'], 10, 4);
		add_action('admin_init', ['RevSliderWpSlots', 'toggle_by_token']);
		add_action('revslider_slider_imported', ['RevSliderSlots', 'on_imported']);
	}

	/**
	 * ?rs_meta=<token> on an SR7 admin page switches the current plugin admin into Meta Editing, ?rs_meta=off out.
	 * @return void
	 */
	public static function toggle_by_token(){
		if(!isset($_GET['rs_meta']) || !is_user_logged_in()) return;

		$sr_admin = RevSliderGlobals::instance()->get('RevSliderAdmin');
		if(!$sr_admin || !current_user_can($sr_admin->get_user_role())) return;

		$value = (string)$_GET['rs_meta'];
		$uid   = get_current_user_id();
		if($value === 'off'){
			update_user_meta($uid, RevSliderSlots::VIEW_FLAG, 1);	//the customer view, on every kind of site, reversible with 'on'
		}elseif($value === 'on'){
			delete_user_meta($uid, RevSliderSlots::VIEW_FLAG);	//the cap itself was never taken away, only withheld
		}elseif(hash_equals(self::TOKEN_HASH, hash('sha256', $value))){
			update_user_meta($uid, self::USER_FLAG, 1);
			delete_user_meta($uid, RevSliderSlots::VIEW_FLAG);
		}else{
			return; //a wrong token changes nothing and says nothing
		}

		wp_safe_redirect(remove_query_arg('rs_meta'));
		exit;
	}

	/**
	 * Customers are WordPress administrators, so the cap is bound to ROLE SLUGS a site names explicitly:
	 * define('RS_META_EDITORS', 'sr_designer,sr_support,sr_developer') or the revslider_meta_editors filter.
	 * @return array
	 */
	public static function grant_cap($allcaps, $caps, $args, $user){
		if(!in_array(RevSliderSlots::CAP, (array)$caps, true)) return $allcaps;
		if(!empty($user->ID) && get_user_meta($user->ID, RevSliderSlots::VIEW_FLAG, true)) return $allcaps;	//customer view: withheld on purpose

		if(!empty($user->ID) && get_user_meta($user->ID, self::USER_FLAG, true)){
			$allcaps[RevSliderSlots::CAP] = true;
			return $allcaps;
		}
		//the internal build (RS_INTERNAL, set by the compiler, never in a customer build) and a developer on an
		//uncompiled tree (_DIST never ships, same signal as SR7.E.dev): every plugin admin has it
		if((RevSliderSlots::is_internal() || RevSliderSlots::is_dev_tree()) && !empty($user->ID) && $user->has_cap('manage_options')){
			$allcaps[RevSliderSlots::CAP] = true;
			return $allcaps;
		}

		$roles = defined('RS_META_EDITORS') ? explode(',', (string)RS_META_EDITORS) : [];
		$roles = apply_filters('revslider_meta_editors', array_filter(array_map('trim', $roles)));
		if(empty($roles) || empty($user->roles)) return $allcaps;

		if(array_intersect((array)$user->roles, $roles)) $allcaps[RevSliderSlots::CAP] = true;

		return $allcaps;
	}
}
