<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Core;

if(!defined('ABSPATH')) exit();

/**
 * Pictures of a module's resting state, taken by the ThemePunch render service for licensed sites that switched
 * Cloud Thumbnails on. A save marks the module stale, the module overview asks for the new picture.
 * @since 7.2.3
 */
class Thumbs extends Functions {

	//per module id: s stale, src the picture, h the hash of what it shows, t failed tries; _day the 429 lock, _wait the
	//time before which nothing is asked after a 503 or no answer
	const OPTION	= 'revslider-thumbs';
	const MAX_W		= 960;
	const MAX_MEDIA	= 8388608;
	const TRIES		= 3;
	const WAIT		= 600;

	/** @return bool the switch is on, the site is licensed and a service is configured */
	public function enabled(){
		return $this->_truefalse($this->get_val($this->get_global_settings(), 'autoThumbs', false)) === true
			&& $this->_truefalse($this->get_options(['system', 'valid'], 'false')) === true
			&& $this->endpoint() !== '';
	}

	/** @return string the render service's shot route, RS_THUMBS_ENDPOINT; '' on a host that sets none */
	public function endpoint(){
		return defined('RS_THUMBS_ENDPOINT') ? (string)RS_THUMBS_ENDPOINT : '';
	}

	/** a new install starts with the switch on, a site that ran Slider Revolution before keeps it off until switched on */
	public function seed_new_install(){
		if($this->endpoint() === '' || $this->get_val($this->get_global_settings(), 'autoThumbs', null) !== null) return;
		$this->set_global_settings(['autoThumbs' => true], true);
	}

	protected function state(){
		$s = $this->option_storage()->get(self::OPTION, []);
		$s = is_string($s) ? json_decode($s, true) : $s;
		return is_array($s) ? $s : [];
	}

	protected function save_state($s){
		$this->option_storage()->set(self::OPTION, json_encode($s), false);
	}

	public function stale($slider_id){
		if($this->context()->get('preview_mode')) return;
		$id = intval($slider_id);
		if($id <= 0) return;
		$s = $this->state();
		//a build saves a hundred times: only the first save after a picture writes, nothing is sent until the overview asks
		if(!empty($s[$id]['s']) && empty($s[$id]['t'])) return;
		$s[$id] = array_merge($s[$id] ?? [], ['s' => 1, 't' => 0]);
		$this->save_state($s);
	}

	public function stale_after_save($data, $slider_id){
		$this->stale($slider_id);
	}

	public function stale_after_slide($return, $slider_id){
		$this->stale($slider_id);
	}

	public function forget($slider_id){
		$s = $this->state();
		$id = intval($slider_id);
		if(!isset($s[$id])) return;
		$this->delete_file($s[$id]['src'] ?? '');
		unset($s[$id]);
		$this->save_state($s);
	}

	/**
	 * @param int $slider_id
	 * @param bool $has_picture a module saved before the switch went on keeps its picture until its next save
	 * @return bool the overview should ask for a new picture
	 */
	public function is_stale($slider_id, $has_picture = true){
		if(!$this->enabled()) return false;
		$s = $this->state();
		if(($s['_day'] ?? '') === gmdate('Y-m-d') || ($s['_wait'] ?? 0) > time()) return false;
		$m = $s[intval($slider_id)] ?? null;
		return $m === null ? !$has_picture : !empty($m['s']);
	}

	/**
	 * the automatic picture for the overview, unless slide 1 carries an Admin Thumbnail its owner picked
	 * @param Slider $slider
	 * @param array|false $slides the slides the caller already loaded
	 * @return string|false
	 */
	public function src_for($slider, $slides = false){
		$src = $this->state()[intval($slider->get_id())]['src'] ?? '';
		if($src === '') return false;
		$first = $this->first_slide($slides !== false ? $slides : $slider->get_slides());
		if($first !== null && $this->_truefalse($first->get_param(['thumb', 'manual'], false)) === true) return false;
		return $src;
	}

	protected function first_slide($slides){
		foreach($slides ?? [] as $slide){
			if($this->_truefalse($slide->get_param('global', false)) === true) continue;
			return $slide;
		}
		return null;
	}

	/**
	 * take the picture now
	 * @param int $slider_id
	 * @return array src on success, error and kind otherwise
	 */
	public function shot($slider_id){
		if(!$this->enabled()) return ['error' => 'Cloud Thumbnails are off', 'kind' => 'off'];
		$id		= intval($slider_id);
		$slider	= new Slider();
		$slider->init_by_id($id);
		if($slider->inited === false) return ['error' => 'Module could not be loaded', 'kind' => 'invalid'];

		$s		= $this->state();
		if(($s['_day'] ?? '') === gmdate('Y-m-d')) return ['error' => 'The daily limit is reached', 'kind' => 'cap'];
		if(($s['_wait'] ?? 0) > time()) return ['error' => 'The render service asked to wait', 'kind' => 'wait'];
		$m		= $s[$id] ?? [];
		$body	= $this->payload($slider, false);
		//the page's serial in the html id counts renders, not changes
		$hash	= md5(preg_replace('/SR7_' . $id . '_\d+/', 'SR7_' . $id, $body['body'] . $body['script']));
		//a save that changed nothing the page shows keeps the picture it has
		if(($m['h'] ?? '') === $hash && !empty($m['src'])){
			$s[$id] = array_merge($m, ['s' => 0, 't' => 0]);
			$this->save_state($s);
			return ['src' => $m['src']];
		}
		//the pictures only now: a save that changed nothing reads no file and encodes nothing
		$body['media'] = (object)$this->media($body['head'] . $body['body'] . $body['script']);

		$res = $this->http_client()->post_body($this->endpoint(), json_encode($body), ['timeout' => 60, 'headers' => ['Content-Type' => 'application/json']]);
		$type = strtolower($res->header('content-type'));
		if(!$res->ok || $res->code !== 200 || strpos($type, 'image/') !== 0 || $res->body === ''){
			$answer	= json_decode((string)$res->body, true);
			//503, or no answer short of a timeout (refused, unknown host, reset): the service never tried, so it is no failed
			//try; a timeout counts, the service may hang on this very module
			$down	= $res->code === 503 || ($res->code === 0 && stripos((string)$res->error, 'timed out') === false);
			$kind	= $down ? 'wait' : ($res->code === 429 ? 'cap' : ($res->code === 403 ? 'licence' : ($answer['kind'] ?? 'failed')));
			if($kind === 'cap') $s['_day'] = gmdate('Y-m-d');
			//no payload is built or sent before Retry-After (seconds) has passed, nor for ten minutes without one
			if($down){
				$after		= trim((string)$res->header('retry-after'));
				$after		= ctype_digit($after) ? intval($after) : 0;
				$s['_wait']	= time() + min($after > 0 ? $after : self::WAIT, DAY_IN_SECONDS);
			}
			$tries	= intval($m['t'] ?? 0) + ($down ? 0 : 1);
			//three tries, then the module waits for its next save instead of asking on every visit
			$s[$id]	= array_merge($m, ['t' => $tries, 's' => ($tries < self::TRIES && !in_array($kind, ['licence', 'invalid'])) ? 1 : 0]);
			$this->save_state($s);
			return ['error' => $answer['message'] ?? ($res->error ?: 'The render service answered ' . $res->code), 'kind' => $kind];
		}

		$ud		= $this->paths()->upload_dir();
		$name	= 'revslider/thumbs/' . $id . '-' . substr($hash, 0, 10) . ($type === 'image/jpeg' ? '.jpg' : '.webp');
		$dir	= $ud['basedir'] . '/revslider/thumbs';
		if((!is_dir($dir) && !@mkdir($dir, 0755, true)) || !$this->filesystem()->write($ud['basedir'] . '/' . $name, $res->body)){
			return ['error' => 'The picture could not be written to the uploads folder', 'kind' => 'failed'];
		}
		$src = $ud['baseurl'] . '/' . $name;
		if(!empty($m['src']) && $m['src'] !== $src) $this->delete_file($m['src']);
		$s[$id] = ['s' => 0, 't' => 0, 'h' => $hash, 'src' => $src];
		$this->save_state($s);

		return ['src' => $src];
	}

	/**
	 * the page a render service needs: what the front prints for this module, and every picture and font file of
	 * this site it uses as data, since local, staging and logged-in sites cannot be fetched
	 * @param Slider $slider
	 * @param bool $with_media false leaves media empty, for a caller that first checks whether the page changed
	 * @return array
	 */
	public function payload($slider, $with_media = true){
		$service	= new \ThemePunch\SR\Render\Service();
		$output		= new \RevSlider7Output();
		//the service loads nothing from this site, so a stream module's slides go into the page, not through the REST route
		$output->global_settings = array_replace_recursive((array)$output->global_settings, ['getTec' => ['feed' => 'JSON']]);
		$page		= $service->render($slider->get_id(), [], $output);

		return [
			'licence'	=> $this->get_options(['system', 'license'], ''),
			'site'		=> md5((string)$this->paths()->content_url()),
			'module'	=> ['id' => intval($slider->get_id()), 'alias' => $slider->get_alias()],
			'engine'	=> RS_REVISION,
			'addons'	=> array_values((array)$slider->get_param('aU', [])),
			//the handles a host enqueues (Render\Page::FRONT_SCRIPTS, FRONT_STYLES); the service puts its engine's files there
			'assets'	=> $page->assets,
			'head'		=> $page->head_html,
			'body'		=> $page->body_html,
			'script'	=> $page->footer_js,
			//an object even when empty: a module without pictures sent [], and the service refused it as invalid
			'media'		=> (object)($with_media ? $this->media($page->head_html . $page->body_html . $page->footer_js) : []),
		];
	}

	/** @return array url => data URL, pictures over MAX_W wide shrunk to it */
	protected function media($text){
		//JSON-escaped once in the module's settings, twice in a stream's JSON.parse('...')
		$text	= str_replace(['\\\\/', '\\/'], '/', $text);
		$ud		= $this->paths()->upload_dir();
		$roots	= [$ud['baseurl'] => $ud['basedir'], rtrim($this->paths()->plugins_url(), '/') => dirname(rtrim(RS_PLUGIN_PATH, '/'))];
		$found	= [];
		preg_match_all('#(?:https?:)?//[^\s"\'()<>\\\\]+?\.(?:jpe?g|png|webp|gif|svg|avif|woff2?|ttf|otf)(?=[\s"\'()<>?\\\\])#i', $text, $found);
		$media	= [];
		$total	= 0;
		foreach(array_unique($found[0] ?? []) as $url){
			$path = $this->local_path($url, $roots);
			if($path === '') continue;
			$data = $this->data_url($path);
			if($data === '') continue;
			$total += strlen($data);
			if($total > self::MAX_MEDIA) break;
			$media[$url] = $data;
		}

		return $media;
	}

	protected function local_path($url, $roots){
		$plain = preg_replace('#^(?:https?:)?//#', '', $url);
		foreach($roots as $base => $dir){
			$base = preg_replace('#^(?:https?:)?//#', '', $base);
			if(strpos($plain, $base . '/') !== 0) continue;
			$rel	= rawurldecode(substr($plain, strlen($base) + 1));
			$path	= $dir . '/' . $rel;
			if(strpos($rel, '..') !== false || !is_file($path)) return '';
			return $path;
		}
		return '';
	}

	protected function data_url($path){
		$ext	= strtolower(pathinfo($path, PATHINFO_EXTENSION));
		$mime	= ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif', 'svg' => 'image/svg+xml', 'avif' => 'image/avif', 'woff2' => 'font/woff2', 'woff' => 'font/woff', 'ttf' => 'font/ttf', 'otf' => 'font/otf'][$ext] ?? '';
		if($mime === '') return '';
		$file = $path;
		$tmp  = '';
		if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])){
			$size = @getimagesize($path);
			if(!empty($size[0]) && $size[0] > self::MAX_W){
				$o	 = \RevSliderGlobals::instance()->get('RevSliderOptimizer');
				$ud	 = $this->paths()->upload_dir();
				$tmp = $ud['basedir'] . '/revslider/thumbs/.shrink-' . md5($path) . '.webp';
				if($o->verify_webp() && $o->save($path, $tmp, ['w' => self::MAX_W, 'h' => (int)round($size[1] * self::MAX_W / $size[0])]) !== false && is_file($tmp)){
					$file = $tmp;
					$mime = 'image/webp';
				}
			}
		}
		$bytes = (string)$this->filesystem()->read($file);
		if($tmp !== '') $this->filesystem()->delete($tmp);

		return $bytes === '' ? '' : 'data:' . $mime . ';base64,' . base64_encode($bytes);
	}

	protected function delete_file($src){
		if($src === '') return;
		$ud = $this->paths()->upload_dir();
		if(strpos($src, $ud['baseurl'] . '/revslider/thumbs/') !== 0) return;
		$this->filesystem()->delete($ud['basedir'] . substr($src, strlen($ud['baseurl'])));
	}
}
