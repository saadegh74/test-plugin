<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Core;

if(!defined('ABSPATH')) exit();

/**
 * The builder-facing module API: listing, labels and the shortcode grammar (CONTRACT B8.1).
 * Platform-neutral by construction - database and permissions come over the ports; the
 * WP-facing RevSliderBuilders keeps its frozen publics as delegation shells on top.
 */
class BuilderApi {

	/**
	 * "Slider with 5 Slides" - composed once, from parts translated once.
	 *
	 * The card in every builder writes the same sentence in JavaScript (SR7.Block.typeLabel). Both have to
	 * read the same, or a dropdown and the card it fills disagree about the module they describe.
	 *
	 * @param string $type   module type as stored, e.g. 'carousel'
	 * @param int    $slides number of slides
	 *
	 * @return string
	 */
	public static function type_label($type, $slides){
		$kind	= ($type === '' || $type === null) ? __('Slider', 'revslider') : ucfirst((string)$type);
		$slides	= (int)$slides;
		$noun	= ($slides === 1) ? __('Slide', 'revslider') : __('Slides', 'revslider');

		return $kind . ' ' . __('with', 'revslider') . ' ' . $slides . ' ' . $noun;
	}

	/**
	 * Every module on the site, as plain arrays.
	 *
	 * This is the call themes and builders actually want, and the one they have been reaching into
	 * RevSliderSlider for. Nothing but arrays crosses this boundary, which is what lets the classes behind
	 * it keep changing (CONTRACT B8.1).
	 *
	 *   RevSliderBuilders::modules(['include' => ['slides'], 'filter' => 'carousel']);
	 *   // [ ['id'=>12,'alias'=>'hero','title'=>'Hero','type'=>'carousel','premium'=>false,
	 *   //    'cover'=>['image'=>'...','color'=>'...'], 'slides'=>5, 'label'=>'Carousel with 5 Slides'], ... ]
	 *
	 * Cost: one query for the list. Asking for 'slides' adds one more for the whole list at once - never one
	 * per module, which is the trap the older listing helpers fall into.
	 *
	 * @param array $args include (slides), filter (all or a module type), search, orderby (title|id)
	 *
	 * @return array the modules, or nothing at all for a user who may not edit
	 */
	public static function modules($args = []){
		if(!self::user_may_edit()) return [];

		$args = array_merge([
			'include'	=> [],
			'filter'	=> 'all',
			'search'	=> '',
			'orderby'	=> 'title'
		], is_array($args) ? $args : []);

		$db = Registry::instance()->get('RevSliderFunctions')->database(); //static context, no $this

		//One read, and no slider object built out of any of it. Everything the default shape needs - the
		//type, whether it is premium, its cover - is in this row's params. (Not in `settings`, which holds
		//the version and little else; that is worth knowing before trying to make this cheaper.)
		$rows = $db->get_results(
			'SELECT id, title, alias, params FROM ' . $db->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDER . " WHERE `type` NOT IN ('folder','trash')",
			ARRAY_A
		);

		$include		= (array)$args['include'];
		$want_slides	= in_array('slides', $include, true);
		$counts			= $want_slides ? self::slide_counts() : [];
		$search			= trim((string)$args['search']);
		$modules		= [];

		foreach((array)$rows as $row){
			$params	= json_decode((string)($row['params'] ?? ''), true);
			$params	= is_array($params) ? $params : [];
			$type	= isset($params['type']) ? (string)$params['type'] : '';

			if($args['filter'] !== 'all' && $type !== $args['filter']) continue;

			$title	= (string)($row['title'] ?? '');
			$alias	= (string)($row['alias'] ?? '');

			if($search !== '' && stripos($title . ' ' . $alias, $search) === false) continue;

			$id		= (int)($row['id'] ?? 0);
			$module	= [
				'id'		=> $id,
				'alias'		=> $alias,
				'title'		=> $title,
				'type'		=> $type,
				'premium'	=> !empty($params['prem']),
				'cover'		=> self::cover($params)
			];

			if($want_slides){
				$module['slides']	= isset($counts[$id]) ? (int)$counts[$id] : 0;
				$module['label']	= self::type_label($type, $module['slides']);
			}

			$modules[] = $module;
		}

		$by_id = ($args['orderby'] === 'id');
		usort($modules, static function($a, $b) use ($by_id){
			return $by_id ? ($a['id'] <=> $b['id']) : strcasecmp($a['title'], $b['title']);
		});

		/**
		 * The module list handed to a builder or theme integration.
		 *
		 * @param array $modules plain arrays, never slider objects
		 * @param array $args    what the caller asked for
		 */
		return apply_filters('revslider_builder_modules', $modules, $args);
	}

	/**
	 * What a module card shows behind its title: the editor saves the first slide's thumbnail as an image URL when it has
	 * one, and the colour it stands on when it has not - as a string, or as the {color} object the editor writes for a
	 * colour thumbnail. Reading that object as a URL is what drew "Array" over the card.
	 * @since 7.2.0
	 * @param array $params the module's stored params
	 * @return array image and color, either of them ''
	 */
	public static function cover($params){
		//no $this and no registry: this class answers a builder that may have loaded nothing else of ours yet
		$thumb	= isset($params['thumb']) ? $params['thumb'] : '';
		$image	= '';
		$color	= '';

		if(is_array($thumb)){
			$image = isset($thumb['src']) ? (string)$thumb['src'] : '';
			$color = isset($thumb['color']) ? (string)$thumb['color'] : (isset($thumb['string']) ? (string)$thumb['string'] : '');
		}elseif(is_string($thumb) && $thumb !== ''){
			//a value that starts like a colour is never a URL
			if(preg_match('/^(#|rgba?\(|transparent)/i', $thumb)) $color = $thumb;
			else $image = $thumb;
		}

		//an unfinished colour, the bare '#' an older save could store, says nothing: the module's background answers instead
		if(!preg_match('/^(#[0-9a-f]{3,8}|rgba?\(.+\)|transparent)$/i', $color)) $color = '';
		if($color === '' && isset($params['bg']['color']['string'])) $color = (string)$params['bg']['color']['string'];

		return ['image' => $image, 'color' => $color];
	}

	/**
	 * One module by alias or id, in the shape modules() returns.
	 *
	 * @param string|int $which alias or id
	 *
	 * @return array|false
	 */
	public static function module($which){
		foreach(self::modules(['include' => ['slides']]) as $module){
			if((string)$module['alias'] === (string)$which) return $module;
			if((string)$module['id'] === (string)$which) return $module;
		}

		return false;
	}

	/**
	 * How many slides each module has, for the whole list in one query.
	 *
	 * @return array id => count
	 */
	private static function slide_counts(){
		$db = Registry::instance()->get('RevSliderFunctions')->database(); //static context, no $this

		$rows = $db->get_results(
			'SELECT slider_id, COUNT(*) AS slides FROM ' . $db->prefix() . \ThemePunch\SR\Core\Data::TABLE_SLIDES . ' WHERE static != 1 GROUP BY slider_id',
			ARRAY_A
		);

		$counts = [];
		foreach((array)$rows as $row){
			$counts[(int)$row['slider_id']] = (int)$row['slides'];
		}

		return $counts;
	}

	/**
	 * A ready-made module picker, for an integration that only wants a select box.
	 *
	 * A thin wrapper over modules() - one data path - and the escaping happens here so an integrator cannot
	 * get it wrong. Anyone wanting their own markup calls modules() instead.
	 *
	 *   echo RevSliderBuilders::dropdown(['name' => 'my_slider', 'selected' => $current, 'show' => ['type']]);
	 *
	 * @param array $args name, value (alias|id), selected, placeholder, show (type), attrs, echo, and
	 *                    anything modules() accepts
	 *
	 * @return string
	 */
	public static function dropdown($args = []){
		$args = array_merge([
			'name'			=> 'revslider_module',
			'value'			=> 'alias',
			'selected'		=> '',
			'placeholder'	=> __('Select a Module...', 'revslider'),
			'show'			=> [],
			'attrs'			=> [],
			'echo'			=> false
		], is_array($args) ? $args : []);

		$show		= (array)$args['show'];
		$with_type	= in_array('type', $show, true);
		$listing	= self::modules(array_merge($args, ['include' => $with_type ? ['slides'] : []]));

		$attributes = ' name="' . esc_attr($args['name']) . '"';
		foreach((array)$args['attrs'] as $attribute => $value){
			$attributes .= ' ' . esc_attr($attribute) . '="' . esc_attr($value) . '"';
		}

		$html  = '<select' . $attributes . '>';
		$html .= '<option value="">' . esc_html($args['placeholder']) . '</option>';

		foreach($listing as $module){
			$value	= ($args['value'] === 'id') ? (string)$module['id'] : $module['alias'];
			$label	= $module['title'];

			if($with_type && isset($module['label'])) $label .= ' - ' . $module['label'];

			$html .= '<option value="' . esc_attr($value) . '"' . selected($args['selected'], $value, false) . '>'
				. esc_html($label) . '</option>';
		}

		$html .= '</select>';

		if(!empty($args['echo'])){
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- every part escaped above
			return '';
		}

		return $html;
	}

	/**
	 * An embedded module, as its shortcode.
	 *
	 * The PHP twin of SR7.Block.toShortcode, and the reason it exists: an integration whose host gives it
	 * no place to run our JavaScript - Cornerstone's inspector is one - can still only reach the grammar by
	 * composing the string by hand. Five places in this plugin were already doing exactly that.
	 *
	 *   RevSliderBuilders::shortcode(['alias' => 'hero', 'usage' => 'modal']);
	 *   // [sr7 alias="hero" usage="modal"][/sr7]
	 *
	 * Takes the published grammar - the fifteen attributes, flat - not the JavaScript model's nesting. Empty
	 * values are dropped rather than written out empty, and anything not in the grammar is ignored.
	 *
	 * @param array $atts alias, class, fullheight, fullwidth, modal, offset, order, settings, skin, usage,
	 *                    wrapperid, zindex, and for a post based module post_ids, category, taxonomy
	 *
	 * @return string '' without an alias, since a shortcode without one renders nothing
	 */
	public static function shortcode($atts = []){
		$atts	= is_array($atts) ? $atts : [];
		$alias	= isset($atts['alias']) ? trim((string)$atts['alias']) : '';

		if($alias === '') return '';

		//The JavaScript twin drops the premium attributes on an unregistered site rather than writing them
		//out to be ignored later. Same here, or the same module reads differently depending on which half of
		//the API wrote it.
		$premium	= self::is_registered();
		$modal		= isset($atts['usage']) && $atts['usage'] === 'modal';
		$order		= $modal
			? ['alias', 'usage', 'modal', 'order', 'settings', 'skin', 'post_ids', 'category', 'taxonomy']
			: ['alias', 'zindex', 'wrapperid', 'class', 'fullwidth', 'fullheight', 'offset', 'order', 'settings', 'skin', 'post_ids', 'category', 'taxonomy'];
		$gated		= ['zindex', 'wrapperid', 'class', 'fullwidth', 'fullheight', 'offset'];
		$parts		= [];

		foreach($order as $key){
			if(!isset($atts[$key])) continue;
			if(!$premium && in_array($key, $gated, true)) continue;

			$value = is_bool($atts[$key]) ? ($atts[$key] ? 'true' : '') : trim((string)$atts[$key]);
			if($value === '') continue;

			$parts[] = $key . '="' . esc_attr($value) . '"';
		}

		return '[sr7 ' . implode(' ', $parts) . '][/sr7]';
	}

	/**
	 * A shortcode back to its attributes - the twin of SR7.Block.parseShortcode.
	 *
	 * Reads [sr7] and [rev_slider] alike, and the encodings a builder may have stored it under: Divi keeps
	 * the brackets as entities in its builder data and as percent escapes in saved content, so a module
	 * placed there is unreadable to a plain parse.
	 *
	 * @param string $shortcode
	 *
	 * @return array the attributes found, [] if there is no module shortcode in the string
	 */
	public static function parse_shortcode($shortcode){
		$shortcode = (string)$shortcode;

		$shortcode = str_replace(['&#91;', '&#093;', '&#93;', '&quot;', '&#39;', '&apos;', '&amp;'], ['[', ']', ']', '"', "'", "'", '&'], $shortcode);
		if(strpos($shortcode, '%91') !== false || strpos($shortcode, '%93') !== false){
			$shortcode = str_replace(['%91', '%93', '%22'], ['[', ']', '"'], $shortcode);
		}

		//The name must end where the attributes begin, or [sr7_something_else] is read as one of ours.
		if(!preg_match('/\[(?:sr7|rev_slider)(\s[^\]]*)?\]/i', $shortcode, $found)) return [];

		$atts = shortcode_parse_atts(isset($found[1]) ? $found[1] : '');

		return is_array($atts) ? $atts : [];
	}

	/**
	 * The SR7.E and SR7.LANG boot a builder screen needs before the module picker runs, from the same host values
	 * the editor header prints.
	 *
	 * @param bool $from_api true when the markup travels inside an API answer, which carries no nonce of its own
	 *
	 * @return string '' for a user who may not edit
	 */
	public static function shortcode_javascript($from_api = false){
		if(!self::user_may_edit()) return '';

		$sr_env		= Registry::instance()->get('RevSliderFunctions')->editor_environment(); //static context, no $this
		$upload_url	= $sr_env->upload_url();
		$sr_upload_url = !empty($upload_url) ? rtrim($upload_url, '/') . '/' : '';

		ob_start();
		?>
		<script>
			window.SR7			??= {};
			SR7.LIB				??= {};
			SR7.E				??= {gAddons:{}};
			SR7.E.registered	= <?php echo (self::is_registered() !== true) ? 'false' : 'true'; ?>;
			SR7.E.resources		??= {};
			SR7.E.modules		??= [];
			SR7.E.wp_upload_url	= "<?php echo esc_js($sr_upload_url); ?>";
			// Quick Edit loads a module's AddOns the way the editor does (SR7.B.addOnAssets by convention), which
			// needs the plugin root and the list of AddOns that ship a presets catalogue. Same two values as header.php.
			SR7.E.wp_plugin_url	??= "<?php echo esc_js(str_replace(["\n", "\r"], '', $sr_env->plugin_root_url()) . '/'); ?>";
			SR7.E.addon_presets	??= <?php echo wp_json_encode(Functions::get_addon_presets()); ?>;
			// What an AddOn offers to begin a module with, on the card beside Import Template (Functions::get_block_starts)
			SR7.E.starts		??= <?php echo wp_json_encode(Functions::get_block_starts()); ?>;
			// New Popup offers its shop popup only where a shop runs, as in the dashboard (EditorEnvironment)
			SR7.E.woocommerce	??= <?php echo $sr_env->shop_active() ? 'true' : 'false'; ?>;
			<?php if (!$from_api) { ?>
				SR7.E.block_nonce 	= "<?php echo $sr_env->nonce(); ?>";
			<?php } ?>
			SR7.LANG ??= {};
			SR7.LANG = Object.assign(SR7.LANG, <?php echo wp_json_encode([
				'Please wait...' => __('Please wait...', 'revslider'),
				'Premium'        => __('Premium', 'revslider'),
				'Slider'         => __('Slider', 'revslider'),
				'Hero'           => __('Hero', 'revslider'),
				'Carousel'       => __('Carousel', 'revslider'),
				'Per Pages'      => __('Per Page', 'revslider'),
				'All Items'      => __('All Items', 'revslider'),
				'Show all items' => __('Show all items', 'revslider'),
			]); ?>);
		</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * The sr-sp spacer element the module picker lays its cards out with.
	 * @return string
	 */
	public static function shortcode_extended_markup(){
		ob_start();
		?>
		<script>
			window.SrSp = window.SrSp || class SrSp extends HTMLElement {
				static get observedAttributes() {return ['h', 'w'];}
				constructor() {super();}
				connectedCallback() {this.updateDimensions();}
				attributeChangedCallback(name, oldValue, newValue) {this.updateDimensions();}
				updateDimensions() {
					const height = this.getAttribute('h');
					const width = this.getAttribute('w');
					if (width !== null) {
					this.style.display = 'inline-block';
					this.style.width = `${width}px`;
					this.style.height = height ? `${height}px` : 'auto';
					} else {
					this.style.display = 'block';
					this.style.height = height ? `${height}px` : 'auto';
					}
				}
			}
			if (!customElements.get('sr-sp')) customElements.define('sr-sp', SrSp);
		</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * Is this install registered. Premium attributes are not written out without it.
	 * @return bool
	 */
	public static function is_registered(){
		$f = Registry::instance()->get('RevSliderFunctions'); //static context, no $this
		return $f->_truefalse($f->get_options(['system', 'valid'], 'false')) === true;
	}

	/**
	 * Every integration checked this before touching anything, in the same words.
	 * @return bool
	 */
	public static function user_may_edit(){
		$env = Registry::instance()->get('RevSliderFunctions')->environment(); //static context, no $this
		return $env->user_can('edit_posts') || $env->user_can('edit_pages');
	}
}
