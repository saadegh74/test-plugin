<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Ports;

if(!defined('ABSPATH')) exit();

/**
 * The host's media library: media items with ids, URLs, renditions, alt text and labels.
 * Rendition names stay host vocabulary ('full', 'medium', ...); 'full' always means the original.
 * @since 7.2.1
 */
interface MediaLibrary {

	/**
	 * public URL of the item, at the named rendition for images; falls back to the original
	 * file's URL when the rendition does not exist or the item is not an image
	 * @param int|string $id
	 * @param string $size rendition name, 'full' = original
	 * @return string|false false when the id is unknown to the library
	 */
	public function image_url($id, $size = 'full');

	/**
	 * the item as the host's own <img> markup at a rendition (dimensions, srcset, alt, lazy loading as the
	 * host writes them); '' when the id is unknown or not an image
	 * @param int|string $id
	 * @param string $size rendition name, 'full' = original
	 * @return string
	 * @since 7.2.1
	 */
	public function image_tag($id, $size = 'full');

	/**
	 * resolve a media URL back to its library id. Must also match rendition/thumbnail URLs,
	 * host-relative and protocol-relative spellings and either scheme. Positive hits are
	 * memoized per request; a miss stays live so an item created later in the same request
	 * (e.g. during import) is found on retry
	 * @param string $url
	 * @return int|false false when the URL is not a library item
	 */
	public function id_by_url($url);

	/**
	 * alt text stored for the item; '' when none or the id is empty/unknown
	 * @param int|string $id
	 * @return string
	 */
	public function alt_text($id);

	/**
	 * title/label stored for the item; '' when none or the id is empty/unknown
	 * @param int|string $id
	 * @return string
	 */
	public function title($id);

	/**
	 * absolute local filesystem path of the item's original file; false when the id is unknown
	 * or the host keeps no local file (CDN-only) - callers treat false as 'cannot compare'
	 * @param int|string $id
	 * @return string|false
	 */
	public function file_path($id);

	/**
	 * public URL of the item's original file, whatever its type (the size-less counterpart of image_url)
	 * @param int|string $id
	 * @return string|false false when the id is unknown
	 * @since 7.2.1
	 */
	public function file_url($id);

	/**
	 * the library item whose stored file ends in this basename; false when none does.
	 * The domain-change fallback of the import dedupe - URL lookups fail after a move,
	 * the file name still matches
	 * @param string $basename
	 * @return int|string|false
	 */
	public function id_by_basename($basename);

	/**
	 * the newest image whose stored file path contains the fragment, preferring originals
	 * over resized renditions; false when none matches
	 * @param string $fragment
	 * @return int|string|false
	 */
	public function id_by_file_fragment($fragment);

	/**
	 * register a finished file as a library item and hand back its id. The caller already wrote
	 * the file to $path (library-relative); renditions and thumbnails are the host's business
	 * (WordPress defers them through its image-meta-todo pass)
	 * @param string $path library-relative file location
	 * @param array $meta filename, url, absolute, mime_type, excerpt, content
	 * @return int|string the new item's id
	 */
	public function import($path, $meta = []);

	/**
	 * the item's rendition catalogue: ['orig' => rendition, 'sizes' => [name => rendition]],
	 * each rendition ['url' => string, 'path' => string|null, 'width' => int, 'height' => int];
	 * 'path' is null on hosts without local files. false when the id is unknown or not an image
	 * @param int|string $id
	 * @return array|false
	 */
	public function image_renditions($id);

	/**
	 * @return string[] the size names the host generates for library images
	 * @since 7.2.1
	 */
	public function image_sizes();

	/**
	 * @return bool the host can resize and save images
	 * @since 7.2.1
	 */
	public function supports_image_editing();

	/**
	 * warm the host's item cache for the given ids before a burst of lookups; may be a no-op
	 * @param array $ids
	 * @return void
	 * @since 7.2.1
	 */
	public function prime_cache($ids);

	/**
	 * generate the host's renditions/metadata for one library item from its source file;
	 * a no-op on hosts without renditions
	 * @param int|string $id
	 * @param string     $file
	 * @return void
	 * @since 7.2.1
	 */
	public function generate_renditions($id, $file);

	/**
	 * write scaled copies of one image: [$dest_path => [width, height]] in the given order,
	 * sequentially on one editor instance (the shrink-chain the object library has always produced)
	 * @param string $src
	 * @param array  $variants
	 * @return bool
	 * @since 7.2.1
	 */
	public function create_scaled_copies($src, $variants);

	/**
	 * the URL of a cropped copy of this image at exactly this size, made on first use and kept for the next
	 * caller. A host without an image editor answers with the source URL, which the caller renders instead.
	 * @param string $url
	 * @param int $width
	 * @param int $height
	 * @return string '' when the host cannot serve this image at all
	 * @since 7.2.1
	 */
	public function scaled_image_url($url, $width, $height);
}
