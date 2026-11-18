<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 * @since	  6.2.0
 */

namespace ThemePunch\SR\Core;

if(!defined('ABSPATH')) exit();

/**
 * License activation against the ThemePunch servers. The result is stored in the options as
 * system.valid ('true'/'false') and system.license (the key); everything else in the plugin reads the
 * premium state from there.
 */
class License extends Functions {
	/**
	 * Activate the Plugin through the ThemePunch Servers
	 * @param string $code the license key
	 * @return true|string|false true when activated, 'exist' when the key is already in use elsewhere,
	 *                           'banned' when it was revoked, 'legacy' for a lifetime or CodeCanyon key, false on any error
	 **/
	public function activate_plugin($code){
		//only build the tracker when tracking is actually switched on - its constructor loads the global
		//settings and registers filters, all of which _run() would immediately discard again
		$this->run_tracking(true);

		$rslb = Registry::instance()->get('RevSliderLoadBalancer');
		$data = [
			'code'		=> urlencode($code),
			'version'	=> urlencode(RS_REVISION),
			'product'	=> urlencode(RS_PLUGIN_SLUG),
			'addition'	=> apply_filters('revslider_activate_plugin_info_addition', [])
		];
		
		$response = $rslb->call_url_result('activate.php', $data, 'updates');

		//check the transport, not the body: a failed transport (network error, rate-limit lock) can
		//carry no verdict, while any HTTP answer does
		if($response->ok !== true) return false;

		$version_info = $response->body;

		if($version_info == 'valid'){
			$this->update_option(['system', 'valid'], 'true');
			$this->update_option(['system', 'license'], $code);
			$this->update_option(['system', 'deregister'], 'false');
			$this->delete_option(['system', 'license_type']);

			return true;
		}
		if($version_info == 'exist') return 'exist';
		if($version_info == 'banned') return 'banned';
		if($version_info == 'legacy') return 'legacy';
		
		return false;
	}
	
	
	/**
	 * Deactivate the Plugin through the ThemePunch Servers
	 * clears the stored key on success, so the site drops back to the free feature set
	 * @return bool false when the server refuses or cannot be reached
	 **/
	public function deactivate_plugin(){
		$this->run_tracking(false);

		$rslb = Registry::instance()->get('RevSliderLoadBalancer');
		$code = $this->get_options(['system', 'license'], '');
		$data = [
			'code'		=> urlencode($code),
			'product'	=> urlencode(RS_PLUGIN_SLUG),
			'addition'	=> apply_filters('revslider_deactivate_plugin_info_addition', [])
		];

		$res = $rslb->call_url_result('deactivate.php', $data, 'updates');

		//returning false for transport failure AND a non-valid body is intentional: the caller
		//(RevSliderApi::deactivate_plugin) asks the load balancer for the last request and reports
		//the connection error from there, so the distinction is not lost.
		if($res->ok !== true) return false;

		$vi = $res->body;
		if($vi != 'valid') return false;

		$this->update_option(['system', 'valid'], 'false');
		$this->update_option(['system', 'license'], '');
		$this->delete_option(['system', 'license_type']);
		//$this->update_option(['system', 'deregister'], 'true');

		return true;
	}


	/**
	 * run the tracking payload, but only build the tracker when tracking is enabled at all.
	 *
	 * @param bool $licensed passed through to RevSliderTracking::_run()
	 * @return void
	 */
	private function run_tracking($licensed){
		$rstrack = new \ThemePunch\SR\Core\Admin\Tracking();
		if(!$rstrack->is_enabled()) return;

		$rstrack->_run($licensed);
	}
}