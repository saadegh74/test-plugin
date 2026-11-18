<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Platform\WordPress;

use ThemePunch\SR\Ports;

if(!defined('ABSPATH')) exit();

/**
 * WordPress attachments behind the MediaLibrary port.
 * @since 7.2.1
 */
class MediaLibrary implements Ports\MediaLibrary {

	public function image_url($id, $size = 'full'){
		//safety net for edge cases where WP core media functions are not loaded yet;
		//the function_exists guard avoids the redundant require_once stat on every call
		if(!function_exists('wp_get_attachment_image_src')){
			require_once(ABSPATH . 'wp-load.php');
			require_once(ABSPATH . 'wp-includes/pluggable.php');
		}
		$image	= wp_get_attachment_image_src($id, $size);
		$url	= (empty($image)) ? false : $image[0];

		return ($url === false) ? wp_get_attachment_url($id) : $url;
	}

	public function image_tag($id, $size = 'full'){
		return (string)wp_get_attachment_image($id, $size);
	}

	public function id_by_url($url){
		$url = (string)$url;
		if($url === '') return false;

		//memoize per request - attachment_url_to_postid() runs an uncached postmeta query each call
		static $cache = [];
		if(isset($cache[$url])) return $cache[$url];

		//a "-800x600" rendition suffix resolves to its original attachment
		$clean = preg_replace('/-\d+x\d+(?=\.\w{3,4}$)/', '', $url);
		if(strpos($clean, '//') === 0) $clean = 'https:' . $clean;

		$id = attachment_url_to_postid($clean);
		if(empty($id)){
			if(preg_match('~^https?://~i', $clean)){
				//sites that index their media under the other scheme
				$id = attachment_url_to_postid(set_url_scheme($clean, 'https'));
				if(empty($id)) $id = attachment_url_to_postid(set_url_scheme($clean, 'http'));
			}else{
				//raw lookup above matched uploads-relative spellings ("revslider/x/img.jpg");
				//site-relative ones ("/wp-content/uploads/...") need the site URL in front
				$id = attachment_url_to_postid(home_url($clean));
			}
		}
		$id = (empty($id)) ? false : $id; //fix for B-5855627275

		//only memoize positive hits - a "not found" must stay live so an attachment created later
		//in the same request (e.g. during import) is detected on the next lookup
		if($id !== false) $cache[$url] = $id;
		return $id;
	}

	public function alt_text($id){
		if(intval($id) === 0) return '';
		return (string)get_post_meta(intval($id), '_wp_attachment_image_alt', true);
	}

	public function title($id){
		//get_the_title() falls back to the global post on an empty id and would leak the page title
		if(intval($id) === 0) return '';
		return (string)get_the_title(intval($id));
	}

	public function file_path($id){
		$path = get_attached_file($id);
		return (empty($path)) ? false : $path;
	}

	public function file_url($id){
		return wp_get_attachment_url($id);
	}

	public function id_by_basename($basename){
		global $wpdb;

		$var = $wpdb->get_var($wpdb->prepare("SELECT `post_id` FROM `".$wpdb->postmeta."` WHERE `meta_value` LIKE %s LIMIT 0,1", '%/'.$basename));

		return intval($var) ?: false;
	}

	public function id_by_file_fragment($fragment){
		global $wpdb;

		$like = '%' . $wpdb->esc_like($fragment) . '%';

		// Prefer: image attachments, exclude resized variants like -150x150.jpg
		$aid = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT p.ID
				FROM {$wpdb->postmeta} pm
				JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE pm.meta_key = '_wp_attached_file'
				AND pm.meta_value LIKE %s
				AND p.post_type = 'attachment'
				AND p.post_mime_type LIKE 'image/%%'
				AND pm.meta_value NOT REGEXP '-[0-9]+x[0-9]+\\.[A-Za-z0-9]+$'
				ORDER BY p.post_date_gmt DESC
				LIMIT 1",
				$like
			)
		);

		if(!$aid){
			// Fallback: take the most recent image attachment if all matches are resized
			$aid = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT p.ID
					FROM {$wpdb->postmeta} pm
					JOIN {$wpdb->posts} p ON p.ID = pm.post_id
					WHERE pm.meta_key = '_wp_attached_file'
					AND pm.meta_value LIKE %s
					AND p.post_type = 'attachment'
					AND p.post_mime_type LIKE 'image/%%'
					ORDER BY p.post_date_gmt DESC
					LIMIT 1",
					$like
				)
			);
		}

		return $aid ?: false;
	}

	public function import($path, $meta = []){
		if(!function_exists('wp_generate_attachment_metadata')){ //the wp-admin media helpers are not loaded on front/ajax requests
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			require_once(ABSPATH . 'wp-admin/includes/image.php');
		}

		$filename = $meta['filename'] ?? basename($path);
		// Create an array of attachment data to insert into wp_posts table
		$artdata = [
			'post_author'	 => 1,
			'post_date'		 => current_time('mysql'),
			'post_date_gmt'	 => current_time('mysql'),
			'post_title'	 => $filename,
			'post_status'	 => 'inherit',
			'comment_status' => 'closed',
			'ping_status'	 => 'closed',
			'post_name'		 => sanitize_title_with_dashes(str_replace('_', '-', $filename)),
			'post_modified'	 => current_time('mysql'),
			'post_modified_gmt' => current_time('mysql'),
			'post_parent'	 => '',
			'post_type'		 => 'attachment',
			'guid'			 => $meta['url'] ?? '',
			'post_mime_type' => $meta['mime_type'] ?? '',
			'post_excerpt'	 => $meta['excerpt'] ?? '',
			'post_content'	 => $meta['content'] ?? ''
		];
		//insert the database record
		$attach_id = wp_insert_attachment($artdata, $path);
		//generate metadata and thumbnails
		add_filter('intermediate_image_sizes_advanced', ['RevSliderFunctions', 'temporary_remove_sizes'], 10, 2);

		$storage = \RevSliderGlobals::instance()->get('ThemePunch\SR\Ports\OptionStorage');
		$rs_meta_create = $storage->get('rs_image_meta_todo', []);
		if(!isset($rs_meta_create[$attach_id])){
			$rs_meta_create[$attach_id] = $meta['absolute'] ?? $path;
			$storage->set('rs_image_meta_todo', $rs_meta_create);
		}
		if($attach_data = @wp_generate_attachment_metadata($attach_id, $meta['absolute'] ?? $path)){
			@wp_update_attachment_metadata($attach_id, $attach_data);
		}

		return $attach_id;
	}

	public function image_renditions($id){
		$meta = wp_get_attachment_metadata($id);
		if(empty($meta) || empty($meta['file'])) return false;

		$upload	= wp_upload_dir();
		$ret	= [
			'orig' => [
				'url'	 => trailingslashit($upload['baseurl']) . $meta['file'],
				'path'	 => trailingslashit($upload['basedir']) . $meta['file'],
				'width'	 => intval(isset($meta['width']) ? $meta['width'] : 0),
				'height' => intval(isset($meta['height']) ? $meta['height'] : 0),
			],
			'sizes' => [],
		];

		foreach($meta['sizes'] ?? [] as $name => $size){
			$src = wp_get_attachment_image_src($id, $name);
			if($src === false) continue;
			$ret['sizes'][$name] = [
				'url'	 => $src[0],
				'path'	 => str_replace($upload['baseurl'], $upload['basedir'], $src[0]),
				'width'	 => intval(isset($size['width']) ? $size['width'] : 0),
				'height' => intval(isset($size['height']) ? $size['height'] : 0),
			];
		}

		return $ret;
	}

	public function image_sizes(){
		return get_intermediate_image_sizes();
	}

	public function supports_image_editing(){
		return wp_image_editor_supports(['methods' => ['resize', 'save']]) === true;
	}

	public function prime_cache($ids){
		if(function_exists('_prime_post_caches') && !empty($ids)) _prime_post_caches($ids, false, true);
	}

	public function generate_renditions($id, $file){
		if($attach_data = @wp_generate_attachment_metadata($id, $file)){
			@wp_update_attachment_metadata($id, $attach_data);
		}
	}

	public function scaled_image_url($url, $width, $height){
		//crop to the exact box, one URL back, upscale a picture smaller than the box: what the navigation thumbnails were built on
		return rev_aq_resize($url, $width, $height, true, true, true);
	}

	public function create_scaled_copies($src, $variants){
		$image = wp_get_image_editor($src);
		if(is_wp_error($image)) return false;

		foreach($variants ?? [] as $dest => $dim){
			$image->resize($dim[0], $dim[1]);
			$image->save($dest);
		}

		return true;
	}
}
