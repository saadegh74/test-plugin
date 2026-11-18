<?php
/**
 * External Sources Facebook Class
 * @since: 5.0
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.sliderrevolution.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Facebook on WordPress: catches the OAuth redirect on the plugin's admin page and reopens the source panel.
 * Fetching, caching and storing the token are the core's (ThemePunch\SR\Core\External\Facebook).
 */
class RevSliderFacebook extends \ThemePunch\SR\Core\External\Facebook {

	/** @return void */
	public function add_actions(){
		add_action('init', [&$this, 'do_init'], 5);
		add_action('admin_footer', [&$this, 'footer_js']);
		add_action('revslider_slider_on_delete_slider', [&$this, 'on_delete_slider'], 10, 1);
	}

	/**
	 * check if we have QUERY_ARG set
	 * try to login the user
	 * @return void handles the OAuth callback and stores the received token on the slider
	 */
	public function do_init(){
		// are we on revslider page?
		if($this->get_val($_GET, 'page') != 'revslider') return;

		//fb returned error
		if(isset($_GET[self::QUERY_ERROR])) return;
		//we need token and slide ID / slider alias to proceed with saving token
		if(!isset($_GET[self::QUERY_TOKEN]) || !isset($_GET['module'])) return;
		if(!$this->environment()->user_can($this->get_user_role())){
			$_GET[self::QUERY_ERROR] = __('Bad Request', 'revslider');
			return;
		}

		$token		 = sanitize_text_field($_GET[self::QUERY_TOKEN]);
		$connectwith = isset($_GET[self::QUERY_CONNECTWITH]) ? sanitize_text_field($_GET[self::QUERY_CONNECTWITH]) : '';
		$page_id	 = isset($_GET[self::QUERY_PAGE_ID]) ? sanitize_text_field($_GET[self::QUERY_PAGE_ID]) : '';

		$error = $this->save_login($this->get_val($_GET, 'module'), $this->get_val($_GET, 'slide'), $this->get_val($_GET, 'rs_fb_nonce'), $token, $page_id, $connectwith);
		if($error !== ''){
			$_GET[self::QUERY_ERROR] = $error;
			return;
		}

		//redirect - use the site's own host (from home_url), not the client-supplied Host header, to avoid host-header injection into the redirect target
		$_site = wp_parse_url(home_url());
		$_host = $this->get_val($_site, 'host', '') . (empty($_site['port']) ? '' : ':' . $_site['port']);
		$url = set_url_scheme('http://' . $_host . $_SERVER['REQUEST_URI']);
		$url = add_query_arg([self::QUERY_TOKEN => false, self::QUERY_PAGE_ID => false, self::QUERY_CONNECTWITH => false, 'rs_fb_nonce' => false, self::QUERY_SHOW => 1], $url);
		wp_redirect($url);
		exit();
	}

	/** @return void echoes a script that reopens the source panel after the OAuth redirect */
	public function footer_js(){
		// are we on revslider page?
		if($this->get_val($_GET, 'page') != 'revslider') return;

		if(isset($_GET[self::QUERY_SHOW]) || isset($_GET[self::QUERY_ERROR])) echo '<script>SR7.openEditorView = "module.source";</script>'."\n";
		if(isset($_GET[self::QUERY_ERROR]))	echo '<script>SR7.postOpenMessage = "'. __('Facebook API error: ', 'revslider') . esc_html($_GET[self::QUERY_ERROR]) .'";</script>'."\n";
	}

}
