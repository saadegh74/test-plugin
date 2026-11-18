<?php
/**
 * Layer meta ("slots"): what a template's layers ARE and what a customer may do with them.
 *
 * Authored by ThemePunch designers, support and developers inside Quick Edit's Meta Editing mode, never
 * guessed. One row per tagged layer. The 561 catalogue templates are tagged by template uid, so every
 * installed copy of a template reads the same rows; a module that is not a template copy is keyed by its
 * slider id instead. A layer without a row is decoration: hidden from Quick Edit and from the AI.
 *
 * Vocabulary (roles, weights, edit actions) and the alias lexicon live in core/admin/includes/alias-lexicon.json,
 * the one file sr-connect's derive.mjs reads as well.
 *
 * The classic name RevSliderSlots comes through the class map. Who holds the capability and the ?rs_meta switch are
 * the host's (RevSliderWpSlots on WordPress).
 *
 * @author    ThemePunch <info@themepunch.com>
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Core;

if(!defined('ABSPATH')) exit();

class Slots extends Functions {

	const CAP		= 'revslider_meta_edit';	//granted through RS_META_EDITORS / the revslider_meta_editors filter, no stock role has it
	const LEXICON	= 'core/admin/includes/alias-lexicon.json';
	const BUNDLE	= 'core/admin/includes/template-metas.json.gz';	//the delivered metas, written by tools/template-metas/bundle.mjs from a Meta export
	const KIND_TEMPLATE = 'template';			//one row per template (slide_index -1, lkey '') carrying the test status, not a layer
	const VIEW_FLAG		= 'revslider_meta_customer_view';	//?rs_meta=off: the cap is withheld for this user until ?rs_meta=on, so an editor sees what a customer sees
	//Bug reports go straight into Monday when the site carries a token (RS_MONDAY_TOKEN); board and columns
	//are the "SR7 Template QA" board, overridable through the two filters below.
	const MONDAY_API	= 'https://api.monday.com/v2';
	const MONDAY_BOARD	= '18429337587';
	const MONDAY_GROUP	= 'group_mm6tze38';	//Open bugs
	const MONDAY_URL	= 'https://themepunch.monday.com/boards/';

	/** @var array|null decoded lexicon, read once per request */
	private static $lexicon = null;

	/**
	 * a module just imported (store template, package, zip upload) gets the delivered metas of its template
	 * @param int $slider_id
	 * @return void
	 */
	public static function on_imported($slider_id){
		try {
			(new self())->seed_installed($slider_id);
		} catch(\Throwable $e) {} //the metas are a bonus, an import never fails over them
	}

	/**
	 * seed the bundled rows of the module's template, human rows on this site stay
	 * @param int $slider_id
	 * @return array written / kept / skipped
	 */
	public function seed_installed($slider_id){
		$none	= ['written' => 0, 'kept' => 0, 'skipped' => 0];
		$slider = new Slider();
		$slider->init_by_id(intval($slider_id));
		$uid = $this->template_uid_of($slider);
		if($uid === '') return $none;
		//the bundle is what the plugin carries; the filter lets the template server or an AddOn hand over fresher rows
		$rows = apply_filters('revslider_template_metas', $this->bundle_rows($uid), $uid, intval($slider_id));

		return is_array($rows) && !empty($rows) ? $this->seed($rows) : $none;
	}

	/**
	 * the delivered rows of one template, the bundle is read once per request
	 * @param string $template_uid
	 * @return array rows in the seed() shape
	 */
	public function bundle_rows($template_uid){
		static $templates = null;
		if($templates === null){
			$templates = [];
			$file = RS_PLUGIN_PATH . self::BUNDLE;
			if(is_file($file) && function_exists('gzdecode')){
				$json = gzdecode((string)file_get_contents($file));
				$data = is_string($json) ? json_decode($json, true) : null;
				$templates = is_array($data) ? (array)$this->get_val($data, 'templates', []) : [];
			}
		}
		$uid  = strtolower((string)$template_uid);
		$rows = $this->get_val($templates, $uid, []);
		$out  = [];
		foreach(is_array($rows) ? $rows : [] as $row){
			if(!is_array($row)) continue;
			$row['template_uid'] = $uid;
			$out[] = $row;
		}

		return $out;
	}

	/**
	 * @return bool the internal build flavour (Meta Editing on for every plugin admin, ThemePunch servers only)
	 */
	public static function is_internal(){
		return defined('RS_INTERNAL') && RS_INTERNAL === true;
	}

	/**
	 * @return bool
	 */
	public static function monday_enabled(){
		return defined('RS_MONDAY_TOKEN') && is_string(RS_MONDAY_TOKEN) && RS_MONDAY_TOKEN !== '';
	}

	/**
	 * @return bool the uncompiled dev tree (the release build never carries _DIST)
	 */
	public static function is_dev_tree(){
		static $dev = null;
		return $dev ??= file_exists(RS_PLUGIN_PATH . '_DIST');
	}

	/**
	 * @return bool
	 */
	public function user_can_edit(){
		return $this->environment()->user_can(self::CAP);
	}

	/**
	 * @return bool the current user chose to see the plugin as a customer (?rs_meta=off)
	 */
	public static function is_customer_view(){
		$env = Registry::instance()->get('RevSliderFunctions')->environment(); //static: no $this to ask

		return $env->user_id() > 0 && (bool)$env->user_value(self::VIEW_FLAG, '');
	}

	/**
	 * @return array the vocabulary + alias rules, [] when the file is missing
	 */
	public function get_lexicon(){
		if(self::$lexicon !== null) return self::$lexicon;

		$file = RS_PLUGIN_PATH . self::LEXICON;
		$data = file_exists($file) ? json_decode(file_get_contents($file), true) : null;
		self::$lexicon = is_array($data) ? $data : [];

		return self::$lexicon;
	}

	/**
	 * the store uid a slider was installed from ('' for a module built on the site)
	 * @param Slider $slider
	 * @return string
	 */
	public function template_uid_of($slider){
		$uid = $slider->get_param('uid', '');

		return (is_string($uid) && preg_match('/^[a-f0-9]{32}$/i', $uid)) ? strtolower($uid) : '';
	}

	/**
	 * position of every slide in the module, global slide = -1. Slide ids are renumbered by every import,
	 * the order is not, so rows are located by this index; a slide pinned by stamp_slide_indexes() keeps its own.
	 * @param array $slides Slider::get_slides_raw() output (ordered by slide_order)
	 * @return array slide id => index
	 */
	public function slide_index_map($slides){
		$map = [];
		$i	 = 0;
		foreach($slides ?? [] as $id => $slide){
			if($this->_truefalse($this->get_val($slide, 'global', false)) === true){
				$map[$id] = -1;
				continue;
			}
			$pos = $i++;
			$pin = $slide['settings']['slotIndex'] ?? '';
			$map[$id] = is_numeric($pin) ? intval($pin) : $pos;
		}

		return $map;
	}

	/**
	 * pin every slide of a template copy to the index its rows know it by (params slotIndex), before a slide is
	 * added or deleted: by position, a deleted slide moved every tag behind it onto the next slide, and a copy at
	 * the end found none. The global slide keeps the number seeded rows give it.
	 * @since 7.2.2
	 * @param RevSliderSlider $slider
	 * @return void
	 */
	public function stamp_slide_indexes($slider){
		if($this->template_uid_of($slider) === '') return;

		$slides = $slider->get_slides_raw(false);
		$map	= $this->slide_index_map($slides);
		$after	= $this->seeded_global_index($slides, $map);
		$db		= $this->database();
		$table	= $db->prefix() . Data::TABLE_SLIDES;
		foreach($map as $id => $index){
			if(is_numeric($slides[$id]['settings']['slotIndex'] ?? '')) continue;
			$params = json_decode((string)$db->get_var($db->prepare("SELECT params FROM " . $table . " WHERE id = %d", $id)), true);
			if(!is_array($params)) continue;
			$params['slotIndex'] = ($index === -1) ? $after : $index;
			$db->update($table, ['params' => json_encode($params)], ['id' => intval($id)]);
		}
	}

	/**
	 * the number seeded rows give the global slide: the one it was pinned to, else the one after the last slide
	 * @since 7.2.2
	 * @param array $slides Slider::get_slides_raw() output
	 * @param array $index_map slide_index_map() output
	 * @return int|null null when the module has no global slide
	 */
	public function seeded_global_index($slides, $index_map){
		if(!in_array(-1, $index_map, true)) return null;
		foreach($index_map as $id => $index){
			if($index === -1 && is_numeric($slides[$id]['settings']['slotIndex'] ?? '')) return intval($slides[$id]['settings']['slotIndex']);
		}

		return count($index_map) - 1;
	}

	/**
	 * seeded rows (sr-connect manifests, the template bundle) number the global slide after the last slide, while
	 * Quick Edit saves it as -1: read the seeded number as -1, a row stored at -1 wins
	 * @param array $rows get_rows() output
	 * @param array $index_map slide_index_map() output
	 * @return array
	 */
	public function global_rows_as_minus_one($rows, $index_map){
		return $this->global_rows_at($rows, in_array(-1, $index_map, true) ? count($index_map) - 1 : null);
	}

	/**
	 * the same, with the number the seeded rows use for the global slide given (seeded_global_index())
	 * @since 7.2.2
	 * @param array $rows get_rows() output
	 * @param int|null $after null: the module has no global slide
	 * @return array
	 */
	public function global_rows_at($rows, $after){
		if($after === null) return $rows;

		$own   = [];
		foreach($rows as $row) if($row['slide_index'] === -1) $own[$row['lkey']] = true;

		$out = [];
		foreach($rows as $row){
			if($row['slide_index'] === $after){
				if(isset($own[$row['lkey']])) continue;
				$row['slide_index'] = -1;
			}
			$out[] = $row;
		}

		return $out;
	}

	/**
	 * @return string prefixed table name
	 */
	private function table(){
		return $this->database()->prefix() . Data::TABLE_SLOTS;
	}

	/**
	 * rows of a module: by template uid when it is a template copy, by slider id otherwise
	 * @return array
	 */
	public function get_rows($template_uid, $slider_id = 0){
		$db = $this->database();

		$template_uid = (string)$template_uid;
		$slider_id	  = intval($slider_id);
		if($template_uid === '' && $slider_id <= 0) return [];

		$sql  = ($template_uid !== '')
			? $db->prepare("SELECT * FROM " . $this->table() . " WHERE template_uid = %s AND slider_id = 0 AND kind <> %s ORDER BY slide_index, id", $template_uid, self::KIND_TEMPLATE)
			: $db->prepare("SELECT * FROM " . $this->table() . " WHERE template_uid = '' AND slider_id = %d AND kind <> %s ORDER BY slide_index, id", $slider_id, self::KIND_TEMPLATE);
		$rows = $db->get_results($sql, 'ARRAY_A');

		return array_map([$this, 'row_out'], $rows ?: []);
	}

	/**
	 * @return array|null
	 */
	public function get_row($template_uid, $slider_id, $slide_index, $lkey){
		$db = $this->database();

		$row = $db->get_row($db->prepare("SELECT * FROM " . $this->table() . " WHERE template_uid = %s AND slider_id = %d AND slide_index = %d AND lkey = %s",
			(string)$template_uid, intval($slider_id), intval($slide_index), (string)$lkey), 'ARRAY_A');

		return $row ? $this->row_out($row) : null;
	}

	/**
	 * insert or update one layer's tag. A template copy writes the template's row, so every copy sees it.
	 * @param array $tag role, weight, edit[], txt{}, img{}, addons[], group, alias, uid, match_key, kind
	 * @return array|false the stored row
	 */
	public function save_row($template_uid, $slider_id, $slide_index, $lkey, $tag, $user_id = 0){
		$db = $this->database();

		$template_uid = (string)$template_uid;
		$slider_id	  = ($template_uid !== '') ? 0 : intval($slider_id);
		$lkey		  = (string)$lkey;
		if(($template_uid === '' && $slider_id <= 0) || !preg_match('/^[a-zA-Z0-9_\-]{1,64}$/', $lkey)) return false;

		$clean = $this->sanitize_tag($tag);
		$data  = [
			'template_uid'	=> $template_uid,
			'slider_id'		=> $slider_id,
			'slide_index'	=> intval($slide_index),
			'lkey'			=> $lkey,
			'uid'			=> $clean['uid'],
			'match_key'		=> $clean['match_key'],
			'kind'			=> $clean['kind'],
			'role'			=> $clean['role'],
			'weight'		=> $clean['weight'],
			'grp'			=> $clean['group'],
			'params'		=> wp_json_encode(['edit' => $clean['edit'], 'txt' => $clean['txt'], 'img' => $clean['img'], 'addons' => $clean['addons'], 'alias' => $clean['alias']]),
			'updated'		=> gmdate('Y-m-d H:i:s', $this->environment()->local_timestamp()),
			'updated_by'	=> intval($user_id),
		];
		$format = ['%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d'];

		$existing = $this->get_row($template_uid, $slider_id, $slide_index, $lkey);
		if($existing){
			$db->update($this->table(), $data, ['id' => intval($existing['id'])], $format, ['%d']);
		}else{
			$db->insert($this->table(), $data, $format);
		}

		return $this->get_row($template_uid, $slider_id, $slide_index, $lkey);
	}

	/**
	 * untag a layer, it falls back to the default (decoration)
	 * @return bool
	 */
	public function delete_row($template_uid, $slider_id, $slide_index, $lkey){
		$db = $this->database();

		$template_uid = (string)$template_uid;
		$slider_id	  = ($template_uid !== '') ? 0 : intval($slider_id);

		return (bool)$db->delete($this->table(), ['template_uid' => $template_uid, 'slider_id' => $slider_id, 'slide_index' => intval($slide_index), 'lkey' => (string)$lkey], ['%s', '%d', '%d', '%s']);
	}

	/**
	 * bulk upsert, the source for the 561 catalogue templates (sr-connect manifests + skeleton roles) and the
	 * primitive a template install injects rows through. A row a human saved is kept unless $force.
	 * @param array $rows each: template_uid|alias, slide_index, lkey + tag fields
	 * @return array counts
	 */
	public function seed($rows, $force = false){
		$out = ['written' => 0, 'kept' => 0, 'skipped' => 0];
		if(!is_array($rows)) return $out;

		$by_alias = [];
		foreach($rows as $row){
			if(!is_array($row)) { $out['skipped']++; continue; }

			$template_uid = strtolower((string)$this->get_val($row, 'template_uid', ''));
			$alias		  = (string)$this->get_val($row, 'alias_template', $this->get_val($row, 'template', ''));
			if($template_uid === '' && $alias !== ''){
				if(!isset($by_alias[$alias])){
					$tpl = new Template();
					$by_alias[$alias] = (string)$tpl->get_uid_by_alias($alias);
				}
				$template_uid = $by_alias[$alias];
			}
			$slider_id = intval($this->get_val($row, 'slider_id', 0));
			if(($template_uid === '' && $slider_id <= 0) || !preg_match('/^[a-f0-9]{32}$|^$/', $template_uid)) { $out['skipped']++; continue; }

			$slide_index = intval($this->get_val($row, 'slide_index', 0));
			$lkey		 = (string)$this->get_val($row, 'lkey', '');
			$existing	 = $this->get_row($template_uid, $slider_id, $slide_index, $lkey);
			if($existing && intval($existing['updated_by']) > 0 && !$force) { $out['kept']++; continue; }

			$saved = $this->save_row($template_uid, $slider_id, $slide_index, $lkey, $row, 0);
			if($saved) $out['written']++; else $out['skipped']++;
		}

		return $out;
	}

	/**
	 * test status of every template that has one: uid => status
	 * @return array
	 */
	public function get_status_map(){
		$db = $this->database();

		$rows = $db->get_results($db->prepare("SELECT * FROM " . $this->table() . " WHERE kind = %s", self::KIND_TEMPLATE), 'ARRAY_A');
		$map  = [];
		foreach($rows ?: [] as $row) $map[self::status_key($this->get_val($row, 'template_uid'), $this->get_val($row, 'slider_id'))] = $this->status_out($row);

		return $map;
	}

	/**
	 * the key a status is filed under: the store uid of a template copy, else "m<module id>"
	 * @return string
	 */
	public static function status_key($template_uid, $slider_id = 0){
		return (string)$template_uid !== '' ? (string)$template_uid : 'm' . intval($slider_id);
	}

	/**
	 * @return array|null
	 */
	public function get_status($template_uid, $slider_id = 0){
		$db = $this->database();

		$template_uid = (string)$template_uid;
		$slider_id	  = $template_uid !== '' ? 0 : intval($slider_id);
		$row = $db->get_row($db->prepare("SELECT * FROM " . $this->table() . " WHERE template_uid = %s AND slider_id = %d AND kind = %s", $template_uid, $slider_id, self::KIND_TEMPLATE), 'ARRAY_A');

		return $row ? $this->status_out($row) : null;
	}

	/**
	 * the tester's verdict on a template: status (lexicon.statuses), who claimed it, a note, the Monday item
	 * @param array $data status, claimed_by (-1 = the current user, 0 = nobody), note, monday_url
	 * @return array|false
	 */
	public function save_status($template_uid, $data, $user_id = 0, $slider_id = 0){
		$db = $this->database();

		$template_uid = strtolower((string)$template_uid);
		if(!preg_match('/^[a-f0-9]{32}$/', $template_uid)) $template_uid = '';
		$slider_id = $template_uid !== '' ? 0 : intval($slider_id);
		if($template_uid === '' && $slider_id <= 0) return false;

		$lex	  = $this->get_lexicon();
		$statuses = (array)$this->get_val($lex, 'statuses', []);
		$status	  = (string)$this->get_val($data, 'status', 'todo');
		if(!in_array($status, $statuses, true)) $status = (string)$this->get_val($statuses, 0, 'todo');

		$claimed_by = intval($this->get_val($data, 'claimed_by', 0));
		if($claimed_by === -1) $claimed_by = intval($user_id);
		if($claimed_by < 0) $claimed_by = 0;
		$claimed = $claimed_by > 0 ? $this->environment()->user_display_name($claimed_by) : '';

		$url = esc_url_raw((string)$this->get_val($data, 'monday_url', ''));
		if($url !== '' && strpos($url, 'https://') !== 0) $url = '';

		//derived = structurally identical to another template (same slides, layer keys, kinds, aliases): its twin is
		//the one that gets checked, its rows are copied from the twin (derive_rows), testers skip it
		$from = strtolower((string)$this->get_val($data, 'derived_from', ''));
		if(!preg_match('/^[a-f0-9]{32}$/', $from)) $from = '';
		$params = [
			'status'	 => $status,
			'claimed_by' => $claimed_by,
			'claimed'	 => $claimed,
			'note'		 => mb_substr(sanitize_textarea_field((string)$this->get_val($data, 'note', '')), 0, 500),
			'monday_url' => $url,
			'derived_from' => $status === 'derived' ? $from : '',
		];
		$row = [
			'template_uid'	=> $template_uid,
			'slider_id'		=> $slider_id,
			'slide_index'	=> -1,
			'lkey'			=> '',
			'kind'			=> self::KIND_TEMPLATE,
			'role'			=> $status,	//typed copy of the status so the dashboard can filter without decoding params
			'params'		=> wp_json_encode($params),
			'updated'		=> gmdate('Y-m-d H:i:s', $this->environment()->local_timestamp()),
			'updated_by'	=> intval($user_id),
		];
		$format = ['%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d'];

		$existing = $db->get_var($db->prepare("SELECT id FROM " . $this->table() . " WHERE template_uid = %s AND slider_id = %d AND kind = %s", $template_uid, $slider_id, self::KIND_TEMPLATE));
		if($existing) $db->update($this->table(), $row, ['id' => intval($existing)], $format, ['%d']);
		else $db->insert($this->table(), $row, $format);

		return $this->get_status($template_uid, $slider_id);
	}

	/**
	 * one click, one Monday item: the context becomes the item's columns, the status flips to bug, the item URL
	 * is filed on the status row. Without a token the caller falls back to the clipboard flow.
	 * @param array $ctx title, template, uid, module, slide, device, site, text, note
	 * @return array status + monday_url, or error
	 */
	public function report_bug($template_uid, $slider_id, $ctx, $user_id = 0){
		if(!self::monday_enabled()) return ['error' => 'no token'];
		$ctx = is_array($ctx) ? $ctx : [];
		//also the item's first update: that is where the conversation continues in Monday
		$desc = mb_substr(trim(sanitize_textarea_field((string)$this->get_val($ctx, 'desc', ''))), 0, 4000);
		if($desc === '') return ['error' => 'Describe what went wrong before you send the report'];
		$txt = static function($v, $len){ return mb_substr(sanitize_text_field((string)$v), 0, $len); };

		$cols  = apply_filters('revslider_monday_bug_columns', ['template' => 'text_mm6t907p', 'uid' => 'text_mm6t8qdx', 'module' => 'numeric_mm6t9zac', 'slide' => 'text_mm6t7yf5', 'device' => 'text_mm6t5szx', 'site' => 'link_mm6twda4', 'context' => 'long_text_mm6tvn6q', 'status' => 'color_mm6t3g07', 'notes' => 'text_mm6trj17']);
		//the item is created with the site's token, so Monday shows the token's owner: the real reporter goes into the columns
		$reporter = ($user_id > 0) ? $this->environment()->user_display_name($user_id) : '';
		$board = (string)apply_filters('revslider_monday_bug_board', defined('RS_MONDAY_BOARD') ? RS_MONDAY_BOARD : self::MONDAY_BOARD);
		$group = (string)apply_filters('revslider_monday_bug_group', self::MONDAY_GROUP);
		$site  = esc_url_raw((string)$this->get_val($ctx, 'site', ''));
		$values = [
			$cols['template']	=> $txt($this->get_val($ctx, 'template', ''), 200),
			$cols['uid']		=> $txt($template_uid, 32),
			$cols['module']		=> (string)intval($slider_id),
			$cols['slide']		=> $txt($this->get_val($ctx, 'slide', ''), 120),
			$cols['device']		=> $txt($this->get_val($ctx, 'device', ''), 200),
			$cols['context']	=> ['text' => mb_substr(($reporter !== '' ? 'Reported by ' . $reporter . "\n" : '') . sanitize_textarea_field((string)$this->get_val($ctx, 'text', '')), 0, 4000)],
			$cols['status']		=> ['label' => (string)apply_filters('revslider_monday_bug_open_label', 'Open')],	//the board's status labels: Open · In Progress · Fixed · Won't fix
		];
		if($reporter !== '' && !empty($cols['notes'])) $values[$cols['notes']] = $txt('Reported by ' . $reporter . ' · ' . gmdate('Y-m-d H:i', $this->environment()->local_timestamp()), 200);
		if($site !== '') $values[$cols['site']] = ['url' => $site, 'text' => 'Open in Quick Edit'];
		$name = $txt($this->get_val($ctx, 'title', ''), 120);
		if($name === '') $name = $txt($this->get_val($ctx, 'template', 'Template'), 100) . ': bug';

		$query = 'mutation ($board: ID!, $group: String!, $name: String!, $vals: JSON!) { create_item (board_id: $board, group_id: $group, item_name: $name, column_values: $vals) { id } }';
		$res = $this->http_client()->post_body(self::MONDAY_API, wp_json_encode(['query' => $query, 'variables' => ['board' => $board, 'group' => $group, 'name' => $name, 'vals' => wp_json_encode($values)]]), [
			'timeout' => 20,
			'headers' => ['Authorization' => RS_MONDAY_TOKEN, 'Content-Type' => 'application/json', 'API-Version' => '2024-10'],
		]);
		if($res->ok !== true) return ['error' => $res->error];
		$body = json_decode((string)$res->body, true);
		$id   = (string)$this->get_val($body, ['data', 'create_item', 'id'], '');
		if($id === ''){
			$err = $this->get_val($body, ['errors', 0, 'message'], $this->get_val($body, 'error_message', 'Monday did not create the item'));
			return ['error' => (string)$err];
		}
		$url = apply_filters('revslider_monday_item_url', self::MONDAY_URL . $board . '/pulses/' . $id, $id, $board);
		$this->http_client()->post_body(self::MONDAY_API, wp_json_encode(['query' => 'mutation ($item: ID!, $body: String!) { create_update (item_id: $item, body: $body) { id } }', 'variables' => ['item' => $id, 'body' => ($reporter !== '' ? $reporter . ': ' : '') . $desc]]), [
			'timeout' => 15,
			'headers' => ['Authorization' => RS_MONDAY_TOKEN, 'Content-Type' => 'application/json', 'API-Version' => '2024-10'],
		]);

		$cur = $this->get_status($template_uid, $slider_id) ?: [];
		$status = $this->save_status($template_uid, [
			'status'	 => 'bug',
			'claimed_by' => intval($this->get_val($cur, 'claimed_by', 0)),
			'note'		 => (string)$this->get_val($ctx, 'note', $this->get_val($cur, 'note', '')),
			'monday_url' => $url,
		], $user_id, $slider_id);

		return ['status' => $status, 'monday_url' => $url, 'item_id' => $id];
	}

	/**
	 * @return array
	 */
	private function status_out($row){
		$params = json_decode((string)$this->get_val($row, 'params', ''), true);
		$params = is_array($params) ? $params : [];

		return [
			'status'	 => (string)$this->get_val($params, 'status', 'todo'),
			'claimed_by' => intval($this->get_val($params, 'claimed_by', 0)),
			'claimed'	 => (string)$this->get_val($params, 'claimed', ''),
			'note'		 => (string)$this->get_val($params, 'note', ''),
			'monday_url' => (string)$this->get_val($params, 'monday_url', ''),
			'derived_from' => (string)$this->get_val($params, 'derived_from', ''),
			'updated'	 => (string)$this->get_val($row, 'updated', ''),
			'updated_by' => intval($this->get_val($row, 'updated_by', 0)),
		];
	}

	/**
	 * copy the twin's confirmed layer rows onto every derived template, matched by (slide_index, lkey)
	 * @return array counts
	 */
	public function derive_rows(){
		$out = ['templates' => 0, 'rows' => 0, 'no_twin_rows' => 0];
		foreach($this->get_status_map() as $key => $st){
			if($this->get_val($st, 'status') !== 'derived' || $this->get_val($st, 'derived_from', '') === '' || strlen($key) !== 32) continue;
			$twin = array_filter($this->get_rows($st['derived_from'], 0), function($r){ return intval($r['updated_by']) > 0; });
			if(empty($twin)) { $out['no_twin_rows']++; continue; }
			$out['templates']++;
			foreach($twin as $r){
				$this->save_row($key, 0, $r['slide_index'], $r['lkey'], $r, intval($r['updated_by']));
				$out['rows']++;
			}
		}

		return $out;
	}

	/**
	 * every layer row, in the shape slots.seed reads back (the product artifact), plus the template statuses
	 * @return array
	 */
	public function export_rows(){
		$db = $this->database();

		$rows = $db->get_results($db->prepare("SELECT * FROM " . $this->table() . " WHERE kind <> %s ORDER BY template_uid, slider_id, slide_index, id", self::KIND_TEMPLATE), 'ARRAY_A');
		$out  = [];
		foreach($rows ?: [] as $row){
			$r = $this->row_out($row);
			unset($r['id']);
			$out[] = $r;
		}

		return ['generated' => gmdate('Y-m-d H:i:s', $this->environment()->local_timestamp()), 'site' => untrailingslashit($this->environment()->site_url()), 'rows' => $out, 'status' => $this->get_status_map()];
	}

	/**
	 * one line per template for the triage report: status, who, counts, and whether slide 0 has a headline
	 * @return array
	 */
	public function findings(){
		$db = $this->database();

		$titles = [];
		$tpl = new Template();
		foreach($tpl->get_tp_template_sliders() ?: [] as $t) $titles[$this->get_val($t, 'uid')] = ['title' => $this->get_val($t, 'title'), 'alias' => $this->get_val($t, 'alias')];
		$installed = [];
		foreach($db->get_results("SELECT id, params FROM " . $db->prefix() . Data::TABLE_SLIDER, 'ARRAY_A') ?: [] as $s){
			$p = json_decode((string)$this->get_val($s, 'params', ''), true);
			$uid = is_array($p) ? (string)$this->get_val($p, 'uid', '') : '';
			if($uid !== '') $installed[$uid][] = intval($s['id']);
		}

		$agg = $db->get_results($db->prepare("SELECT template_uid, COUNT(*) rows_all, SUM(updated_by > 0) confirmed, SUM(slide_index = 0 AND role = 'headline') headline0 FROM " . $this->table() . " WHERE kind <> %s AND template_uid <> '' GROUP BY template_uid", self::KIND_TEMPLATE), 'ARRAY_A');
		$status = $this->get_status_map();
		$out	= [];
		$counts = ['templates' => 0, 'todo' => 0, 'checked' => 0, 'metas' => 0, 'bug' => 0, 'ignore' => 0, 'derived' => 0, 'claimed' => 0, 'no_headline' => 0];
		foreach($agg ?: [] as $a){
			$uid = $a['template_uid'];
			$st  = $this->get_val($status, $uid, ['status' => 'todo', 'claimed' => '', 'note' => '', 'monday_url' => '', 'updated' => '']);
			$line = [
				'template_uid'	=> $uid,
				'title'			=> $this->get_val($titles, [$uid, 'title'], ''),
				'alias'			=> $this->get_val($titles, [$uid, 'alias'], ''),
				'installed'		=> $this->get_val($installed, $uid, []),
				'status'		=> $st['status'],
				'claimed'		=> $st['claimed'],
				'note'			=> $st['note'],
				'monday_url'	=> $st['monday_url'],
				'derived_from'	=> (string)$this->get_val($st, 'derived_from', ''),
				'updated'		=> $st['updated'],
				'rows'			=> intval($a['rows_all']),
				'confirmed'		=> intval($a['confirmed']),
				'headline_slide0' => intval($a['headline0']) > 0,
			];
			$out[] = $line;
			$counts['templates']++;
			if(isset($counts[$line['status']])) $counts[$line['status']]++;
			if($line['claimed'] !== '') $counts['claimed']++;
			if(!$line['headline_slide0']) $counts['no_headline']++;
		}

		return ['generated' => gmdate('Y-m-d H:i:s', $this->environment()->local_timestamp()), 'site' => untrailingslashit($this->environment()->site_url()), 'counts' => $counts, 'templates' => $out];
	}

	/**
	 * whitelist every tag field against the lexicon vocabulary
	 * @return array
	 */
	public function sanitize_tag($tag){
		$tag = is_array($tag) ? $tag : [];
		$lex = $this->get_lexicon();
		$roles	 = (array)$this->get_val($lex, 'roles', []);
		$weights = (array)$this->get_val($lex, 'weights', []);
		$actions = (array)$this->get_val($lex, 'actions', []);

		$pick = static function($v, $allowed){
			$v = is_string($v) ? $v : '';
			return in_array($v, $allowed, true) ? $v : '';
		};
		$word = static function($v, $len){
			$v = is_scalar($v) ? (string)$v : '';
			return preg_match('/^[a-zA-Z0-9_\-\.\/#]{1,' . $len . '}$/', $v) ? $v : '';
		};
		$intv = static function($v){
			return (is_numeric($v) && intval($v) > 0) ? intval($v) : 0;
		};

		$edit = [];
		foreach((array)$this->get_val($tag, 'edit', []) as $a) if(is_string($a) && in_array($a, $actions, true) && !in_array($a, $edit, true)) $edit[] = $a;

		$txt = $this->get_val($tag, 'txt', []);
		//maxAlone: the measured max when only this slot changes (0 = same as max); now: the designed length (0 = unknown)
		$txt = is_array($txt) ? ['min' => $intv($this->get_val($txt, 'min', 0)), 'max' => $intv($this->get_val($txt, 'max', 0)), 'wrap' => $this->_truefalse($this->get_val($txt, 'wrap', false)) === true, 'maxAlone' => $intv($this->get_val($txt, 'maxAlone', 0)), 'now' => $intv($this->get_val($txt, 'now', 0))] : ['min' => 0, 'max' => 0, 'wrap' => false, 'maxAlone' => 0, 'now' => 0];

		$img = $this->get_val($tag, 'img', []);
		$img = is_array($img) ? [
			'w'		=> $intv($this->get_val($img, 'w', 0)),
			'h'		=> $intv($this->get_val($img, 'h', 0)),
			'alpha'	=> $this->_truefalse($this->get_val($img, 'alpha', false)) === true,
			'fit'	=> $pick($this->get_val($img, 'fit', ''), ['cover', 'contain', 'stretch', 'auto']),
		] : ['w' => 0, 'h' => 0, 'alpha' => false, 'fit' => ''];

		$addons = [];
		foreach((array)$this->get_val($tag, 'addons', []) as $a){
			if(!is_array($a)) continue;
			$slug = $word($this->get_val($a, 'slug', ''), 40);
			if($slug === '') continue;
			$addons[] = ['slug' => $slug, 'toggle' => $this->_truefalse($this->get_val($a, 'toggle', false)) === true, 'preset' => $this->_truefalse($this->get_val($a, 'preset', false)) === true];
		}

		return [
			'uid'		=> $word($this->get_val($tag, 'uid', ''), 16),
			'match_key'	=> $word($this->get_val($tag, 'match_key', ''), 64),
			'kind'		=> $pick($this->get_val($tag, 'kind', ''), ['text', 'image', 'video', 'slidebg', 'addon', 'shape', 'choice']),
			'role'		=> $pick($this->get_val($tag, 'role', ''), $roles),
			'weight'	=> $pick($this->get_val($tag, 'weight', ''), $weights),
			'group'		=> $word($this->get_val($tag, 'group', ''), 64),
			'edit'		=> $edit,
			'txt'		=> $txt,
			'img'		=> $img,
			'addons'	=> $addons,
			'alias'		=> sanitize_text_field((string)$this->get_val($tag, 'alias', '')),
		];
	}

	/**
	 * DB row → the shape the Quick Editor and the seed exchange
	 * @return array
	 */
	private function row_out($row){
		$params = json_decode((string)$this->get_val($row, 'params', ''), true);
		$params = is_array($params) ? $params : [];

		return [
			'id'			=> intval($this->get_val($row, 'id')),
			'template_uid'	=> (string)$this->get_val($row, 'template_uid', ''),
			'slider_id'		=> intval($this->get_val($row, 'slider_id', 0)),
			'slide_index'	=> intval($this->get_val($row, 'slide_index', 0)),
			'lkey'			=> (string)$this->get_val($row, 'lkey', ''),
			'uid'			=> (string)$this->get_val($row, 'uid', ''),
			'match_key'		=> (string)$this->get_val($row, 'match_key', ''),
			'kind'			=> (string)$this->get_val($row, 'kind', ''),
			'role'			=> (string)$this->get_val($row, 'role', ''),
			'weight'		=> (string)$this->get_val($row, 'weight', ''),
			'group'			=> (string)$this->get_val($row, 'grp', ''),
			'edit'			=> (array)$this->get_val($params, 'edit', []),
			'txt'			=> (array)$this->get_val($params, 'txt', []),
			'img'			=> (array)$this->get_val($params, 'img', []),
			'addons'		=> (array)$this->get_val($params, 'addons', []),
			'alias'			=> (string)$this->get_val($params, 'alias', ''),
			'updated'		=> (string)$this->get_val($row, 'updated', ''),
			'updated_by'	=> intval($this->get_val($row, 'updated_by', 0)),
		];
	}
}
