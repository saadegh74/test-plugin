<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Ports;

if(!defined('ABSPATH')) exit();

/**
 * The host's content model behind one port: turns a slider's source config (posts,
 * products, current entry) into streamlined slide arrays - the platform-neutral shape
 * streamline_posts_data() established (id, title, content, link, media, thumb, meta,
 * img_urls, ...). Gallery and social-stream sources never touch this port, they are
 * platform-free core. A platform binds its implementation class via the
 * revslider_content_source_class filter; WordPress' PostContentSource is the default.
 * @since 7.2.1
 */
interface ContentSource {

	/** @return array streamlined slide arrays for the slider's source config */
	public function get_slides_data();

	/**
	 * one list of entries is about to be turned into records: the moment to work out what every record shares
	 * and to warm whatever the host would otherwise look up once per entry
	 * @param array $rows the entries the host's own query returned
	 * @return void
	 * @since 7.2.1
	 */
	public function begin_records($rows);

	/**
	 * one entry as a streamlined record: the flat fields a layer's placeholders read (title, excerpt, link, media,
	 * mediatag, img_urls, meta and the rest of the shape entry() documents)
	 * @param mixed $entry one entry, as the host's own list carries it
	 * @param mixed $template the module's template slide this record is built on, null when the module has none
	 * @param array $metas the placeholders the module's slides use, so only those are resolved
	 * @return array
	 * @since 7.2.1
	 */
	public function record($entry, $template, $metas);

	/**
	 * editor picker: entries with the most comments
	 * @param int|false $max_posts
	 * @return array
	 */
	public function get_popular_posts($max_posts = false);

	/**
	 * editor picker: newest entries
	 * @param int|false $max_posts
	 * @return array
	 */
	public function get_latest_posts($max_posts = false);

	/** @return array custom (non-builtin) content type names, name => name */
	public function custom_content_types();

	/**
	 * @param string $type
	 * @return string the singular label of the content type, or the raw name when unknown
	 */
	public function content_type_label($type);

	/**
	 * @param string $type
	 * @return array taxonomy name => label for one content type
	 */
	public function content_type_taxonomies($type);

	/**
	 * the taxonomy's terms, flat and unlimited
	 * @param string $taxonomy
	 * @return object[] each carrying ->cat_ID, ->name, ->count
	 */
	public function terms($taxonomy);

	/**
	 * terms as a module stores them, "<taxonomy>_<id>", from what a person writes: ids, slugs or that
	 * form. What names no term is dropped.
	 * @param string $list comma separated
	 * @param string $taxonomy the taxonomy of the ids and slugs
	 * @return array
	 * @since 7.2.1
	 */
	public function term_keys($list, $taxonomy);

	/**
	 * the host's static pages in the editor wire shape: id => ['slug', 'title'],
	 * password-protected ones excluded
	 * @return array
	 */
	public function page_catalog();

	/**
	 * public content types in the editor wire shape: slug => ['slug', 'title', 'tax' => name => label];
	 * the keys follow the host's public URL vocabulary
	 * @return array
	 */
	public function content_type_catalog();

	/**
	 * ids of host content whose body contains the given needle (any status a site owner still sees)
	 * @param string $needle plain substring, no wildcards
	 * @return array content ids
	 * @since 7.2.1
	 */
	public function find_content_ids_containing($needle);

	/**
	 * taxonomy terms by id list in the caller's array shape
	 * @param object $caller the RevSliderFunctions-derived object asking (shape helpers live on it)
	 * @since 7.2.1
	 */
	public function get_categories_by_id($caller, $ids, $tax = null);

	/**
	 * module aliases referenced by the given host content ids ([rev_slider]/[sr7] on WordPress)
	 * @since 7.2.1
	 */
	public function get_shortcode_from_page($caller, $ids);

	/**
	 * linked category list markup for one piece of host content
	 * @since 7.2.1
	 */
	public function get_categories_html($caller, $cat_ids, $tax = null, $post_id = '', $full = false);

	/**
	 * ids of host content matching a small, CLOSED set of criteria.
	 *
	 * Deliberately not a query language: it is the handful of questions the Add-Ons actually ask, so
	 * that a host which is not WordPress can answer all of them. An adapter ignores keys it does not
	 * know rather than passing them on, which is what keeps a caller from smuggling host specifics in.
	 *
	 *   type      string  content type name, default 'post'
	 *   relation  string  'any' (default) or 'same_terms' - entries sharing a term with 'of'
	 *   of        int     the entry 'same_terms' is about
	 *   taxonomy  string  which taxonomy 'same_terms' looks at
	 *   exclude   array   ids to leave out
	 *   limit     int     how many at most
	 *   order     string  'recent' (default), 'popular', 'random' or 'menu'
	 *
	 * @param array $query
	 * @return int[] ids, in the requested order
	 * @since 7.2.1
	 */
	public function find_ids($query);

	/**
	 * the entries either side of one entry, in the host's own running order.
	 *
	 * Its own method rather than a find_ids() call the caller indexes into, because the caller would
	 * have to fetch the whole catalogue to find two neighbours - which is what the Add-On did - while
	 * a host that can answer "the one before this" answers it directly.
	 *
	 *   type      string  content type name, default 'post'
	 *   taxonomy  string  when set, only entries sharing a term with $of in that taxonomy count
	 *
	 * @param int   $of    the entry to look around
	 * @param array $query
	 * @return array ['prev' => int, 'next' => int], 0 where there is no neighbour
	 * @since 7.2.1
	 */
	public function adjacent_ids($of, $query = []);

	/**
	 * one entry as the streamlined record, the shape get_slides_data() gives every stream slide.
	 *
	 * The Add-Ons that share, feature or annotate a single entry each rebuilt a slice of this
	 * record by hand from the host's post functions. One method and one shape, so a host that is
	 * not WordPress answers all of them at once, and a placeholder means the same thing on a
	 * slide and in an Add-On.
	 *
	 *   id, type, alias, title, excerpt (as stored, untrimmed), content['content'], link, author,
	 *   publish, modified (site date format, local time), published (bool), protected (bool: a password guards the text and this visitor has not given it), numcomments,
	 *   taglist (linked), catlist (linked), catlistraw, media, mediatag, thumb, thumbtag,
	 *   img_urls[size] => ['url', 'tag'] for every rendition the host generates plus 'full',
	 *   meta[key] for each 'meta:<key>' in $wanted, author_website / author_posts when asked for
	 *
	 * @param int   $id
	 * @param array $wanted extra keys in the placeholder vocabulary: 'meta:<key>', 'author_website', 'author_posts'
	 * @return array|false false when no entry has that id; status does not matter, published says which
	 * @since 7.2.1
	 */
	public function entry($id, $wanted = []);

	/**
	 * the entry behind one of the host's own public URLs (a share link carries the permalink, the
	 * resolver needs the entry)
	 * @param string $url
	 * @return int the entry id, 0 when the URL is not one of the host's entries
	 * @since 7.2.1
	 */
	public function id_by_url($url);

	/**
	 * everything the host stores as text for one entry, joined: its body and every meta value. A page builder keeps
	 * its markup in meta, so this is what "does this entry embed that module" has to read
	 * @param int $id
	 * @return string '' when no entry has that id
	 * @since 7.2.1
	 */
	public function entry_text($id);
}
