<?php
/**
 * External Sources Facebook Class
 * @since: 5.0
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.sliderrevolution.com/
 * @copyright 2024 ThemePunch
 */

namespace ThemePunch\SR\Core\External;

if(!defined('ABSPATH')) exit();

/**
 * Facebook stream source.
 *
 * The site never talks to Facebook directly: the OAuth dance and the Graph calls run through the
 * ThemePunch servers (see LoadBalancer), which hold the app credentials. save_login() stores the token
 * that flow hands back; catching the redirect is the host's part (RevSliderFacebook on WordPress).
 *
 * Every fetch is cached twice - a short lived transient for normal operation, plus a long lived "_bk"
 * copy that is served when the API errors out, so a slider keeps rendering during an outage.
 */
class Facebook extends \ThemePunch\SR\Core\Functions {

	const TRANSIENT_PREFIX	= 'revslider_fb_';

	const URL_FB_AUTH		= 'fb/login.php';
	const URL_FB_API		= 'fb/api.php';

	const QUERY_SHOW		= 'fb_show';
	const QUERY_TOKEN		= 'fb_token';
	const QUERY_PAGE_ID		= 'fb_page_id';
	const QUERY_CONNECTWITH	= 'fb_page_name';
	const QUERY_ERROR		= 'fb_error_message';

	/**
	 * @var int  Transient time in seconds
	 */
	private $transient_sec;

	public function __construct($transient_sec = 1200){
		$this->transient_sec = 	$transient_sec;
	}

	/**
	 * @return int
	 */
	public function getTransientSec(){
		return $this->transient_sec;
	}

	/**
	 * @param int $transient_sec
	 * @return void
	 */
	public function setTransientSec($transient_sec){
		$this->transient_sec = $transient_sec;
	}

	/**
	 * the OAuth flow came back with a token: store it on the module the editor started from
	 * @param int|string $slider_id the module, '' to take it from the slide
	 * @param int|string $slide_id
	 * @param string $nonce the CSRF token the login URL carried
	 * @param string $token
	 * @param string $page_id
	 * @param string $connectwith
	 * @return string '' when stored, else the message to show
	 */
	public function save_login($slider_id, $slide_id, $nonce, $token, $page_id, $connectwith){
		$slider = new \ThemePunch\SR\Core\Slider();

		if(empty($slider_id)){
			$slide = new \ThemePunch\SR\Core\Slide();
			$slide->init_by_id($slide_id);
			$slider_id = $slide->get_slider_id();
			if(intval($slider_id) == 0) return __('Slider could not be loaded', 'revslider');
		}

		$slider->init_by_id($slider_id);
		if($slider->inited === false) return __('Slider could not be loaded', 'revslider');

		if($this->environment()->verify_csrf($nonce, self::get_nonce_name($slider_id)) == false) return __('Bad Request', 'revslider');

		$slider->set_param(['source', 'facebook', 'token_source'], 'account');
		$slider->set_param(['source', 'facebook', 'appId'], $token);
		$slider->set_param(['source', 'facebook', 'page_id'], $page_id);
		$slider->set_param(['source', 'facebook', 'connect_with'], $connectwith);
		$slider->update_params([]);

		return '';
	}

	/** @return string|false the OAuth URL the editor sends the user to */
	public static function get_login_url($id, $slide_id){
		$rslb	= \ThemePunch\SR\Core\Registry::instance()->get('RevSliderLoadBalancer');

		if(empty($id)) return false;

		$back = $rslb->environment()->editor_url($id); //static: no $this, the balancer carries the ports
		$back .= ((strpos($back, '?') === false) ? '?' : '&') . 'slide='.$slide_id.'&rs_fb_nonce='.$rslb->environment()->csrf_token(self::get_nonce_name($id));

		return $rslb->get_url('updates') . '/' . self::URL_FB_AUTH . '?state=' . base64_encode($back);
	}

	/** @return array the decoded response, or ['error' => true, 'message' => ...] */
	protected function _make_api_call($args = []){
		//through the load balancer like the Instagram fetcher, gaining its retry and rate-limit handling
		$rslb	= \ThemePunch\SR\Core\Registry::instance()->get('RevSliderLoadBalancer');
		$result	= $rslb->call_url_result(self::URL_FB_API, $args, 'updates');

		if($result->ok !== true) return ['error' => true, 'message' => 'Facebook API error: ' . $result->error];

		$responseData = json_decode($result->body, true);
		return (empty($responseData)) ? ['error' => true, 'message' => 'Facebook API error: Empty response body or wrong data format'] : $responseData;
	}

	/** @return array the cached stream, falling back to the long lived backup copy on an API error */
	protected function _get_transient_fb_data($requestData){
		$transient_name = self::TRANSIENT_PREFIX . $requestData['slider_id'] . '_' . md5(json_encode($requestData));
		if($this->transient_sec > 0 && false !== ($data = $this->ttl_cache()->get($transient_name))) return $data;

		$responseData = $this->_make_api_call($requestData);
		//stale-on-error: serve the last good copy instead of nothing when the API call fails
		if(!empty($responseData['error']) || !isset($responseData['data'])){
			$backup = $this->ttl_cache()->get($transient_name.'_bk');
			return ($backup !== false) ? $backup : [];
		}

		$this->ttl_cache()->set($transient_name, $responseData['data'], $this->transient_sec);
		$this->ttl_cache()->set($transient_name.'_bk', $responseData['data'], WEEK_IN_SECONDS);
		return $responseData['data'];
	}

	/**
	 * Get Photosets List from User
	 *
	 * @param	string	$access_token 	page access token
	 * @param	string	$page_id 	page id
	 * @return	mixed
	 */
	public function get_photo_sets($access_token, $page_id){
		return $this->_make_api_call([
			'token'		=> $access_token,
			'page_id'	=> $page_id,
			'action'	=> 'albums',
		]);
	}

	/**
	 * Get Photosets List from User as Options for Selectbox
	 *
	 * @param	string	$access_token 	page access token
	 * @param	string	$page_id 	page id
	 * @return	mixed	options html string | array('error' => true, 'message' => '...');
	 */
	public function get_photo_set_photos_options($access_token, $page_id){
		$photo_sets = $this->get_photo_sets($access_token, $page_id);

		if($photo_sets['error']) return $photo_sets;

		$return = [];
		if(!is_array($photo_sets['data'])) return $return;

		foreach($photo_sets['data'] ?? [] as $photo_set){
			$return[] = [
				'value'	=> $this->get_val($photo_set, ['id']),
				'text'	=> $this->get_val($photo_set, ['name']),
			];
		}

		return $return;
	}

	/**
	 * Get Photoset Photos
	 *
	 * @param	mixed	$slider_id 	slider id
	 * @param	string	$access_token 	page access token
	 * @param	string|array	$album_id 	Album ID, or the list the SR7 album multiselect stores
	 * @param	int 	$item_count 	items count
	 * @return	array
	 */
	public function get_photo_set_photos($slider_id, $access_token, $album_id, $item_count = 8){
		//the API takes one album_id string: an array reached it as album_id[0] and answered "Album ID is missing!"
		$albums = array_values(array_filter(array_map('strval', (array)$album_id), 'strlen'));
		$photos = [];
		foreach($albums as $album){
			$requestData = [
				'slider_id'	=> $slider_id,
				'token'		=> $access_token,
				'action'	=> 'photos',
				'album_id'	=> $album,
				'limit'		=> $item_count,
			];
			$photos = array_merge($photos, (array)$this->_get_transient_fb_data($requestData));
		}
		if(count($albums) > 1){
			usort($photos, function($a, $b){ return strcmp($this->get_val($b, 'created_time', ''), $this->get_val($a, 'created_time', '')); });
			$photos = array_slice($photos, 0, intval($item_count));
		}

		return $photos;
	}

	/**
	 * Get Feed
	 *
	 * @param	mixed	$slider_id 	slider id
	 * @param	string	$access_token 	page access token
	 * @param	string	$page_id 	page id
	 * @param	int 	$item_count 	items count
	 * @return	array
	 */
	public function get_photo_feed($slider_id, $access_token, $page_id, $item_count = 8){
		$requestData = [
			'slider_id'	=> $slider_id,
			'token'		=> $access_token,
			'page_id'	=> $page_id,
			'action'	=> 'feed',
			'limit'		=> $item_count,
		];
		return $this->_get_transient_fb_data($requestData);
	}

	/**
	 * delete slider fb transients upon deletion
	 *
	 * @param	$id		slider id
	 * @return	void
	 */
	public function on_delete_slider($id){
		if(empty($id)) return;

		$this->ttl_cache()->delete_by_prefix(self::TRANSIENT_PREFIX . $id); //prefix sweep replaces the old infix LIKE, same rows for the real key shapes
	}

	/**
	 * @param int|string $p
	 * @return string
	 */
	public static function get_nonce_name($p){
		return self::TRANSIENT_PREFIX . 'nonce_' . $p;
	}

}
