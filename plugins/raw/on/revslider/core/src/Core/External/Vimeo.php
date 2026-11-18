<?php
/**
 * External Sources Vimeo Class
 * @since: 5.0
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.sliderrevolution.com/
 * @copyright 2024 ThemePunch
 */

namespace ThemePunch\SR\Core\External;

if(!defined('ABSPATH')) exit();

/**
 * Vimeo
 *
 * with help of the API this class delivers all kind of Images/Videos from Vimeo
 *
 * @package    socialstreams
 * @subpackage socialstreams/vimeo
 * @author     ThemePunch <info@themepunch.com>
 */

/**
 * Vimeo stream source: fetches a user's, album's or channel's videos, cached like the other streams with a
 * long lived "_bk" copy as fallback.
 */
class Vimeo extends \ThemePunch\SR\Core\Functions {
	/**
	 * Stream Array
	 *
	 * @access   private
	 * @var      array    $stream    Stream Data Array
	 */
	private $stream;

	/**
	 * Transient seconds
	 *
	 * @access   private
	 * @var      number    $transient Transient time in seconds
	 */
	private $transient_sec;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param      string    $api_key	Youtube API key.
	 */
	public function __construct($transient_sec = 1200){
		$this->transient_sec = $transient_sec;
	}

	/**
	 * Get Vimeo User Videos
	 *
	 * @return array the cached videos, falling back to the long lived backup copy on an API error
	 */
	public function get_vimeo_videos($type, $value, $elements = 20){
		//call the API and decode the response
		$url = 'https://vimeo.com/api/v2/';
		$url .= ($type == 'user') ? $value.'/videos.json' : $type.'/'.$value.'/videos.json';

		$transient_name = 'revslider_' . md5($url.$elements);
		if($this->transient_sec > 0 && false !== ($data = $this->ttl_cache()->get($transient_name))) return ($data);

		$timeout  = (int)apply_filters('revslider_stream_http_timeout', 8); //cap the render-path fetch
		$elements = intval($elements);
		$page = 1;
		$rsp = [];
		do {
			$body     = $this->http_client()->get($url.'?page='.$page, ['timeout' => $timeout])->body;
			$_rsp     = ($body !== '') ? json_decode($body) : null;
			$count    = (!empty($_rsp) && is_array($_rsp)) ? count($_rsp) : 0;
			if($count > 0) $rsp = array_merge($rsp, $_rsp);
			$page++;
			$elements -= 20;
			if($count < 20) break; //short page = last page, stop fetching further (empty) pages
		} while($elements > 0);

		//stale-on-error: keep serving the last good set instead of going blank on a failed fetch
		if(empty($rsp)){
			$backup = $this->ttl_cache()->get($transient_name.'_bk');
			if($backup !== false) return $backup;
		}

		$this->ttl_cache()->set($transient_name, $rsp, $this->transient_sec);
		if(!empty($rsp)) $this->ttl_cache()->set($transient_name.'_bk', $rsp, WEEK_IN_SECONDS);

		return $rsp;
	}
}	// End Class