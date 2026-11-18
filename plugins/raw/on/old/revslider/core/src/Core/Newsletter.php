<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

namespace ThemePunch\SR\Core;

if(!defined('ABSPATH')) exit();

/**
 * Opt-in/opt-out against the ThemePunch newsletter service. Namespaced, so it never meets Essential Grid's global
 * ThemePunch_Newsletter; class-map.php aliases that name to this class only when nothing declared it first
 */
class Newsletter {

	protected static $remote_url	= 'http://newsletter.themepunch.com/';
	protected static $subscribe		= 'subscribe.php';
	protected static $unsubscribe	= 'unsubscribe.php';

	/**
	 * Subscribe to the ThemePunch Newsletter
	 * @return array|false the decoded server response, false on a transport error or a bad response
	 **/
	public static function subscribe($email){
		//through the http port; the host identifies itself with the port's default user agent now
		$result = Registry::instance()->get('RevSliderFunctions')->http_client()->post(self::$remote_url.self::$subscribe, ['email' => urlencode($email)], ['timeout' => 15]);

		if($result->ok !== true) return false;

		if($response = json_decode($result->body, true)){
			return (!is_array($response)) ? false : $response;
		}

		return false;
	}


	/**
	 * Unsubscribe to the ThemePunch Newsletter
	 * @return array|false the decoded server response, false on a transport error or a bad response
	 **/
	public static function unsubscribe($email){
		//through the http port; the host identifies itself with the port's default user agent now
		$result = Registry::instance()->get('RevSliderFunctions')->http_client()->post(self::$remote_url.self::$unsubscribe, ['email' => urlencode($email)], ['timeout' => 15]);

		if($result->ok !== true) return false;

		if($response = json_decode($result->body, true)){
			return (!is_array($response)) ? false : $response;
		}

		return false;
	}

}
