<?php
/**
 * External Sources Instagram Class
 * @since: 5.0
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.sliderrevolution.com/
 * @copyright 2024 ThemePunch
 */

namespace ThemePunch\SR\Core\External;

if(!defined('ABSPATH')) exit();

/**
 * Instagram stream source.
 *
 * Like Facebook, the OAuth flow and the API calls go through the ThemePunch servers. The
 * resulting token lives on the slider, and every fetch keeps a short lived cache plus a long lived "_bk"
 * fallback used when the API errors out. Instagram tokens expire, so refresh_token() renews them.
 * Catching the OAuth redirect is the host's part (RevSliderInstagram on WordPress).
 */
class Instagram extends \ThemePunch\SR\Core\Functions {

	const TRANSIENT_PREFIX = 'revslider_ig_';

	const URL_IG_AUTH = 'https://updates.themepunch.tools/ig/login.php';
	const URL_IG_API = 'https://updates.themepunch.tools/ig/api.php';

	const QUERY_SHOW = 'ig_show';
	const QUERY_TOKEN = 'ig_token';
	const QUERY_CONNECTWITH = 'ig_user';
	const QUERY_ERROR = 'ig_error_message';

	/**
	 * Stream Array
	 * @access   private
	 * @var      array    $stream    Stream Data Array
	 */
	private $stream;

	/**
	 * instagram data transient expiration time in seconds
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var int  $transient_sec
	 */
	private $transient_sec;

	/**
	 * instagram token transient expiration time
	 * trigger token_refresh on expire
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var int  $transient_token_sec
	 */
	private $transient_token_sec;

	/**
	 * Initialize the class and set its properties.
	 * @param int $transient_sec  Transient time in seconds
	 */
	public function __construct($transient_sec = \DAY_IN_SECONDS){
		$this->transient_sec = $transient_sec;
		$this->transient_token_sec = MONTH_IN_SECONDS;
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
	 * @param int $transient_token_sec
	 * @return void
	 */
	public function setTransientTokenSec($transient_token_sec)
	{
		$this->transient_token_sec = $transient_token_sec;
	}

	/**
	 * the OAuth flow came back with a token: store it on the module the editor started from
	 * @param int|string $slider_id the module, '' to take it from the slide
	 * @param int|string $slide_id
	 * @param string $nonce the CSRF token the login URL carried
	 * @param string $token
	 * @param string $connectwith
	 * @return string '' when stored, else the message to show
	 */
	public function save_login($slider_id, $slide_id, $nonce, $token, $connectwith){
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

		// save token transient to trigger refresh on expire
		$this->ttl_cache()->set( $this->get_transient_name('token', $token), $token, $this->transient_token_sec);

		$slider->set_param(['source', 'instagram', 'token_source'], 'account');
		$slider->set_param(['source', 'instagram', 'token'], $token);
		$slider->set_param(['source', 'instagram', 'connect_with'], $connectwith);
		$slider->update_params([]);

		return '';
	}

	/** @return string|false the OAuth URL the editor sends the user to */
	public static function get_login_url($id, $slide_id){
		$env = \ThemePunch\SR\Core\Registry::instance()->get('RevSliderFunctions')->environment(); //static: no $this to ask

		if(empty($id)) return false;

		$back = $env->editor_url($id); //a host's editor URL need not carry a query yet
		$back .= ((strpos($back, '?') === false) ? '?' : '&') . 'slide='.$slide_id.'&rs_ig_nonce='.$env->csrf_token(self::get_nonce_name($id));

		return self::URL_IG_AUTH . '?state=' . base64_encode($back);
	}

	/**
	 * get grid transient name
	 *
	 * @param mixed  $id  transient id (grid id or string)
	 * @param string $token
	 * @param int    $count
	 * @return string
	 */
	public function get_transient_name($id, $token, $count = 0){
		return self::TRANSIENT_PREFIX . $id . '_' . md5(json_encode($token . '_' . $count));
	}

	/**
	 * refresh Instagram token if needed
	 *
	 * @param  string $token
	 * @return string
	 */
	public function refresh_token($token){
		if(empty($token)){
			return $token;
		}

		$transient_token = $this->get_transient_name('token', $token);
		if($this->transient_token_sec > 0 && false !== ($data = $this->ttl_cache()->get($transient_token))){
			return $token;
		}

		//$request contain new token, however old token expiry date also updated, so we could still use it
		$request = $this->_callAPI([
			'action' => 'refresh_token',
			'token' => $token,
		]);
		if(isset($request['data']['access_token'])){
			$this->ttl_cache()->set($transient_token, $token, $this->transient_token_sec);
			return $token;
		}

		return '';
	}

	/**
	 * Get Instagram User Profile
	 *
	 * @param string $token Instagram Access Token
	 * @return mixed
	 */
	public function get_user_profile($token){
		$this->refresh_token($token);

		$transient_name = 'revslider_'. md5('instagram-profile-' . $token);
		//the profile changes rarely - cache it for at least a day regardless of the (short) stream TTL
		$profile_ttl = max((int)$this->transient_sec, DAY_IN_SECONDS);
		if(false !== ($data = $this->ttl_cache()->get($transient_name))){
			return $data;
		}

		$profile = $this->_callAPI([
			'action' => 'profile',
			'token' => $token,
		]);
		if(isset($profile['data'])){
			$profile = $profile['data'];
			$this->ttl_cache()->set($transient_name, $profile, $profile_ttl);
			$this->ttl_cache()->set($transient_name.'_bk', $profile, WEEK_IN_SECONDS);
			return $profile;
		}
		//stale-on-error: serve the last good profile instead of null when the API call fails
		$backup = $this->ttl_cache()->get($transient_name.'_bk');
		return ($backup !== false) ? $backup : null;
	}


	/**
	 * Get Instagram User Pictures
	 *
	 * @param int $slider_id slider ID
	 * @param string $token Instagram Access Token
	 * @param string $count media count
	 * @param string $orig_image
	 * @return mixed
	 */
	public function get_public_photos($slider_id, $token, $count, $orig_image = ''){
		$this->refresh_token($token);

		$transient_name = $this->get_transient_name($slider_id, $token, $count);
		if($this->transient_sec > 0 && false !== ($data = $this->ttl_cache()->get($transient_name))){
			$this->stream = $data;
			return $this->stream;
		}else{
			$this->ttl_cache()->delete($transient_name);
		}

		//Getting instagram images
		$medias = $this->_callAPI([
			'action' => 'public_photos',
			'token' => $token,
			'count' => $count,
		]);
		if(isset($medias['data']['data'])){
			$this->instagram_output_array($medias['data']['data'], $count);
		}
		if(!empty($this->stream)){
			$this->ttl_cache()->set($transient_name, $this->stream, $this->transient_sec);
			$this->ttl_cache()->set($transient_name.'_bk', $this->stream, WEEK_IN_SECONDS);
			return $this->stream;
		}else{
			//stale-on-error: serve the last good media set instead of an error when the API call fails
			$backup = $this->ttl_cache()->get($transient_name.'_bk');
			if($backup !== false){
				$this->stream = $backup;
				return $this->stream;
			}

			$err = (isset($medias['error']) && $medias['error'] === true) ? $this->get_val($medias, 'message') : __('Instagram reports: Please check the settings','revslider');
			$_err = $this->get_val($medias, ['data', 'error', 'message']);
			$err = (!empty($_err)) ? $_err : $err;

			echo $err;
			return false;
		}
	}

	/**
	 * @param array $args
	 * @return array
	 */
	protected function _callAPI($args = []){
		//the transport result instead of the host-native response, as the Facebook fetcher does
		$rslb	= \ThemePunch\SR\Core\Registry::instance()->get('RevSliderLoadBalancer');
		$result	= $rslb->call_url_result(self::URL_IG_API, $args);
		if($result->ok !== true) return ['error' => true, 'message' => 'Instagram API error: ' . $result->error];

		$responseData = json_decode($result->body, true);
		return (empty($responseData)) ? ['error' => true, 'message' => 'Instagram API error: Empty response body or wrong data format'] : $responseData;
	}

	/**
	 * Prepare output array $stream
	 *
	 * @param    array $photos Instagram Output Data
	 * @param    int $count resulting number of items
	 * @return int
	 */
	private function instagram_output_array($photos, $count){
		$this->stream = [];

		foreach($photos ?? [] as $photo){
			if($count <= 0) break;

			$count--;
			$shortcode = '';

			preg_match('/.+\/p\/(.+)?\//m', $photo['permalink'], $matches);
			if(isset($matches[1])) $shortcode = $matches[1];

			$photo['display_url'] = isset($photo['media_url']) ? $photo['media_url'] : '';
			if($photo['media_type'] == 'VIDEO'){
				$photo['display_url'] = isset($photo['thumbnail_url']) ? $photo['thumbnail_url'] : '';
				$photo['thumbnail_src'] = $photo['display_url'];
				$photo['videos']['standard_resolution']['url'] = isset($photo['media_url']) ? $photo['media_url'] : '';
			}
			$photo['link'] = isset($photo['permalink']) ? $photo['permalink'] : '';
			$photo['shortcode'] = $shortcode;
			$photo['taken_at_timestamp'] = isset($photo['timestamp']) ? $photo['timestamp'] : '';
			$photo['edge_media_to_caption']['edges'][0]['node']['text'] = isset($photo['caption']) ? $photo['caption'] : '';
			$this->stream[] = $photo;
		}

		return $count;
	}

	/**
	 * delete slider ig transients upon deletion
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
