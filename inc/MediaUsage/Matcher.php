<?php
/**
 * Matching logic for the Media Library Usage Scanner.
 *
 * Given a blob of stored content (post_content, postmeta, options, etc.) this
 * class extracts references to Media Library attachments two ways:
 *
 *  1. By URL: any Infinite Uploads / local upload URL (in absolute, protocol-
 *     relative, root-relative or CDN form) is captured and resolved back to an
 *     attachment id via the _wp_attached_file meta, after stripping size
 *     variants (-300x200) and next-gen sidecars (.webp/.avif).
 *  2. By id: high-signal id patterns in post content (the wp-image-{id} class
 *     and gallery shortcode ids="...").
 *
 * Blobs are scanned as RAW STRINGS (never unserialized / json_decoded), so
 * malformed page-builder data can never throw or fatal a scan batch. Because
 * every supported builder stores either the attachment URL or one of these id
 * forms, raw URL matching covers Elementor, Divi, Beaver, Bricks, Oxygen,
 * Avada and Gutenberg without builder-specific parsers.
 *
 * @package InfiniteUploads
 */

namespace ClikIT\InfiniteUploads\MediaUsage;

use ClikIT\InfiniteUploads\InfiniteUploadsHelper;

/**
 * Class Matcher
 */
class Matcher {

	/**
	 * Confidence levels for references.
	 */
	const CONFIDENCE_HIGH   = 'high';
	const CONFIDENCE_MEDIUM = 'medium';
	const CONFIDENCE_LOW    = 'low';

	/**
	 * Skip URL matching on values larger than this (bytes) to avoid memory/CPU
	 * spikes on pathological option/meta blobs. Real builder data is far smaller.
	 */
	const MAX_BLOB_BYTES = 4194304; // 4 MB.

	/**
	 * Don't build the in-memory _wp_attached_file map above this many attachments
	 * (bounds memory; transient peak at the cap is ~25-30 MB during the build);
	 * larger libraries fall back to the per-path query. Tunable via the
	 * `infinite_uploads_media_usage_path_map_max` filter for constrained hosts.
	 */
	const PATH_MAP_MAX = 50000;

	/**
	 * Request cache of relative-path => attachment id (false when unresolved).
	 *
	 * @var array<string,int|false>
	 */
	private static $path_cache = array();

	/**
	 * Per-request bulk map of stored _wp_attached_file => attachment id. Null
	 * until built; false when the library is too large (per-path fallback).
	 *
	 * @var array<string,int>|false|null
	 */
	private static $path_map = null;

	/**
	 * Whether resolve_path_to_id() may use the bulk map. Disabled for single-
	 * attachment rescans, which resolve only a few paths and shouldn't pay the
	 * whole-library map build.
	 *
	 * @var bool
	 */
	private static $path_map_enabled = true;

	/**
	 * Compiled regex used to find upload URLs in a blob, or null if not built.
	 *
	 * @var string|null
	 */
	private static $url_regex = null;

	/**
	 * Find every attachment referenced in a value by URL.
	 *
	 * Works on any raw string (serialized PHP, JSON, HTML, plain text).
	 *
	 * @param string $blob The raw stored value.
	 *
	 * @return array<int,string> Map of attachment_id => first matched URL/path.
	 */
	public static function find_url_references( $blob ) {
		$found = array();

		if ( ! is_string( $blob ) || '' === $blob || strlen( $blob ) > self::MAX_BLOB_BYTES ) {
			return $found;
		}

		$regex = self::get_url_regex();
		if ( ! $regex ) {
			return $found;
		}

		// Page builders and Gutenberg store URLs as JSON, where forward slashes
		// are escaped (https:\/\/site\/wp-content\/uploads\/...). Unescape them
		// so the path-based regex matches Elementor/Bricks/Oxygen/ACF JSON data.
		if ( false !== strpos( $blob, '\\/' ) ) {
			$blob = str_replace( '\\/', '/', $blob );
		}

		if ( ! preg_match_all( $regex, $blob, $matches, PREG_SET_ORDER ) ) {
			return $found;
		}

		foreach ( $matches as $match ) {
			$relative = isset( $match[1] ) ? $match[1] : '';
			if ( '' === $relative ) {
				continue;
			}

			$attachment_id = self::resolve_path_to_id( $relative );
			if ( $attachment_id && ! isset( $found[ $attachment_id ] ) ) {
				$found[ $attachment_id ] = $match[0];
			}
		}

		return $found;
	}

	/**
	 * Find attachment references by id in HTML/post content.
	 *
	 * Only high-signal id forms are used: the core wp-image-{id} class and the
	 * gallery shortcode ids/include attributes. Each id is verified to be an
	 * actual attachment before being returned.
	 *
	 * @param string $content Post content (HTML + shortcodes + block markup).
	 *
	 * @return array<int,array{reference_type:string,matched_value:string}>
	 */
	public static function find_id_references( $content ) {
		$found = array();

		if ( ! is_string( $content ) || '' === $content ) {
			return $found;
		}

		// Core image class, e.g. class="wp-image-123".
		if ( preg_match_all( '/wp-image-(\d+)/', $content, $m ) ) {
			foreach ( $m[1] as $i => $id ) {
				$id = (int) $id;
				if ( $id && self::is_attachment( $id ) && ! isset( $found[ $id ] ) ) {
					$found[ $id ] = array(
						'reference_type' => 'block_image',
						'matched_value'  => $m[0][ $i ],
					);
				}
			}
		}

		// Gallery shortcodes: [gallery ids="1,2,3"] or include="1,2,3".
		if ( preg_match_all( '/(?:ids|include)=["\']([0-9,\s]+)["\']/', $content, $g ) ) {
			foreach ( $g[1] as $list ) {
				foreach ( array_filter( array_map( 'absint', explode( ',', $list ) ) ) as $id ) {
					if ( self::is_attachment( $id ) && ! isset( $found[ $id ] ) ) {
						$found[ $id ] = array(
							'reference_type' => 'gallery',
							'matched_value'  => 'ids="' . $id . '"',
						);
					}
				}
			}
		}

		return $found;
	}

	/**
	 * Cheap pre-filter: could this value hold a bare attachment id or a
	 * serialized array of ids? Used to gate the (slightly heavier) ACF
	 * companion lookup + unserialize so they never run on normal blobs.
	 *
	 * @param string $value Stored meta/option value.
	 *
	 * @return bool
	 */
	public static function looks_like_id_candidate( $value ) {
		if ( ! is_string( $value ) || '' === $value || strlen( $value ) > self::MAX_BLOB_BYTES ) {
			return false;
		}

		if ( preg_match( '/^\d{1,12}$/', trim( $value ) ) ) {
			return true;
		}

		return 0 === strpos( ltrim( $value ), 'a:' );
	}

	/**
	 * Extract attachment ids stored as a bare integer or a flat serialized
	 * array of integers (how ACF stores Image/File/Gallery values). Every id
	 * is verified to be a real attachment, so non-attachment id arrays (post
	 * ids, prices, etc.) are rejected.
	 *
	 * @param string $value Stored value.
	 *
	 * @return array<int,string> attachment_id => matched value.
	 */
	public static function find_serialized_id_references( $value ) {
		$found = array();
		if ( ! is_string( $value ) || '' === $value ) {
			return $found;
		}

		$trimmed = trim( $value );

		// Bare single id.
		if ( preg_match( '/^\d{1,12}$/', $trimmed ) ) {
			$id = (int) $trimmed;
			if ( $id && self::is_attachment( $id ) ) {
				$found[ $id ] = '#' . $id;
			}

			return $found;
		}

		// Flat serialized array of ids (only attempt when the prefix matches).
		if ( 0 !== strpos( ltrim( $value ), 'a:' ) ) {
			return $found;
		}

		$data = maybe_unserialize( $value );
		if ( ! is_array( $data ) ) {
			return $found;
		}

		foreach ( $data as $item ) {
			if ( ! is_scalar( $item ) || ! is_numeric( $item ) ) {
				continue;
			}
			$id = (int) $item;
			if ( $id > 0 && ! isset( $found[ $id ] ) && self::is_attachment( $id ) ) {
				$found[ $id ] = '#' . $id;
			}
		}

		return $found;
	}

	/**
	 * Extract attachment ids from Oxygen Builder data, which stores them as
	 * "attachment_id":"123" (single) and "image_ids":"1,2,3" (gallery) inside
	 * its JSON/shortcode tree. Tolerant of addslashes()-escaped quotes.
	 *
	 * @param string $blob Stored Oxygen value.
	 *
	 * @return array<int,string> attachment_id => matched value.
	 */
	public static function find_oxygen_references( $blob ) {
		$found = array();
		if ( ! is_string( $blob ) || '' === $blob || strlen( $blob ) > self::MAX_BLOB_BYTES ) {
			return $found;
		}

		// Oxygen stores ct_builder_json with addslashes(), so normalize escaped
		// quotes/slashes before matching the JSON keys.
		$normalized = str_replace( array( '\\"', '\\/' ), array( '"', '/' ), $blob );

		if ( preg_match_all( '/"attachment_id"\s*:\s*"?(\d{1,12})/', $normalized, $m ) ) {
			foreach ( $m[1] as $raw ) {
				$id = (int) $raw;
				if ( $id && ! isset( $found[ $id ] ) && self::is_attachment( $id ) ) {
					$found[ $id ] = 'attachment_id:' . $id;
				}
			}
		}

		if ( preg_match_all( '/"image_ids"\s*:\s*"([0-9,\s]+)"/', $normalized, $g ) ) {
			foreach ( $g[1] as $list ) {
				foreach ( array_filter( array_map( 'absint', preg_split( '/[,\s]+/', $list ) ) ) as $id ) {
					if ( ! isset( $found[ $id ] ) && self::is_attachment( $id ) ) {
						$found[ $id ] = 'image_ids:' . $id;
					}
				}
			}
		}

		return $found;
	}

	/**
	 * Extract attachment ids from Bricks Builder data.
	 *
	 * Bricks saves builder data as PHP-serialized arrays (update_post_meta /
	 * update_option with an array value), so an image's attachment id is stored
	 * as `s:2:"id";i:123;` — never as JSON `"id":123`. Element ids are random
	 * alphanumeric STRINGS (`s:2:"id";s:6:"abc123";`), which the integer-valued
	 * pattern (`i:`) cleanly excludes.
	 *
	 * Bricks persists the upload URL alongside the id (image/gallery/background
	 * objects keep `url`/`full`), so find_url_references already catches the
	 * common case. This id-path is a backstop for references whose stored URL
	 * cannot be resolved (e.g. a third-party CDN rewrote it). Every candidate is
	 * still verified via is_attachment() to reject stray numeric settings.
	 *
	 * @param string $blob Stored Bricks value (serialized postmeta or option).
	 *
	 * @return array<int,string> attachment_id => matched value.
	 */
	public static function find_bricks_references( $blob ) {
		$found = array();
		if ( ! is_string( $blob ) || '' === $blob || strlen( $blob ) > self::MAX_BLOB_BYTES ) {
			return $found;
		}

		if ( preg_match_all( '/s:2:"id";i:(\d{1,12});/', $blob, $m ) ) {
			foreach ( $m[1] as $raw ) {
				$id = (int) $raw;
				if ( $id && ! isset( $found[ $id ] ) && self::is_attachment( $id ) ) {
					$found[ $id ] = '#' . $id;
				}
			}
		}

		return $found;
	}

	/**
	 * Cache of ACF field-key => is-a-media-field (image/file/gallery) lookups.
	 *
	 * @var array<string,bool>
	 */
	private static $acf_media_field_cache = array();

	/**
	 * Whether an ACF field key refers to a field type that can store an
	 * attachment id with no accompanying URL: Image/File/Gallery, plus
	 * Icon Picker (media-library mode), Post Object, Relationship and Page Link
	 * (which can hold attachment ids selected as objects). This prevents numeric
	 * Number/True-False values and repeater/flexible row counts (which also carry
	 * a `field_*` companion) from being misread as attachment references.
	 *
	 * Object-selector types (post_object/relationship/page_link) may also hold
	 * non-attachment post/page/term ids; those are harmlessly dropped because
	 * find_serialized_id_references verifies every id via is_attachment().
	 *
	 * Resolves the type via acf_get_field() when ACF is loaded, falling back to
	 * the field-definition post (post_type `acf-field`) so it still works for
	 * DB-defined fields outside the ACF runtime. Results are cached per request.
	 *
	 * @param mixed $field_key The companion field key (expected `field_xxxx`).
	 *
	 * @return bool
	 */
	public static function acf_key_is_media( $field_key ) {
		if ( ! is_string( $field_key ) || 0 !== strpos( $field_key, 'field_' ) ) {
			return false;
		}

		if ( isset( self::$acf_media_field_cache[ $field_key ] ) ) {
			return self::$acf_media_field_cache[ $field_key ];
		}

		$type = '';

		if ( function_exists( 'acf_get_field' ) ) {
			$field = acf_get_field( $field_key );
			if ( is_array( $field ) && ! empty( $field['type'] ) ) {
				$type = $field['type'];
			}
		}

		if ( '' === $type ) {
			global $wpdb;
			$content = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT post_content FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'acf-field' LIMIT 1",
					$field_key
				)
			);
			if ( is_string( $content ) && '' !== $content ) {
				$def = maybe_unserialize( $content );
				if ( is_array( $def ) && ! empty( $def['type'] ) ) {
					$type = $def['type'];
				}
			}
		}

		$is_media = in_array( $type, array( 'image', 'file', 'gallery', 'icon_picker', 'post_object', 'relationship', 'page_link' ), true );

		self::$acf_media_field_cache[ $field_key ] = $is_media;

		return $is_media;
	}

	/**
	 * Resolve an uploads-relative path to an attachment id.
	 *
	 * Strips the size variant suffix and any next-gen sidecar extension, then
	 * matches the remaining relative path against _wp_attached_file. Results
	 * (including misses) are cached for the request.
	 *
	 * @param string $relative Path relative to the uploads root, e.g. 2024/01/img-300x200.jpg.
	 *
	 * @return int Attachment id, or 0 if not resolvable.
	 */
	public static function resolve_path_to_id( $relative ) {
		$relative = self::normalize_relative_path( $relative );
		if ( '' === $relative ) {
			return 0;
		}

		if ( array_key_exists( $relative, self::$path_cache ) ) {
			return (int) self::$path_cache[ $relative ];
		}

		// Fast path: O(1) lookup in the per-request bulk map of _wp_attached_file
		// values. A hit is byte-exact, i.e. a strict subset of the SQL match
		// below, so it never resolves anything the per-path query wouldn't. A
		// miss falls through to the query, preserving exact behaviour (including
		// collation-insensitive matches and very large libraries).
		if ( self::$path_map_enabled ) {
			$map = self::path_map();
			if ( is_array( $map ) && isset( $map[ $relative ] ) ) {
				self::$path_cache[ $relative ] = (int) $map[ $relative ];

				return (int) $map[ $relative ];
			}
		}

		global $wpdb;

		$post_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value = %s LIMIT 1",
				$relative
			)
		);

		$post_id                       = $post_id ? (int) $post_id : 0;
		self::$path_cache[ $relative ] = $post_id;

		return $post_id;
	}

	/**
	 * Build (once per request) and return the bulk map of stored _wp_attached_file
	 * values => attachment id, so URL resolution is an in-memory lookup instead of
	 * an unindexed meta_value query per path. Returns false when the library is
	 * larger than the cap (resolution then uses the per-path query to bound memory).
	 *
	 * @return array<string,int>|false
	 */
	private static function path_map() {
		if ( null !== self::$path_map ) {
			return self::$path_map;
		}

		global $wpdb;

		$max = (int) apply_filters( 'infinite_uploads_media_usage_path_map_max', self::PATH_MAP_MAX );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching -- literal query, no user input.
		$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file'" );
		if ( $count > $max ) {
			self::$path_map = false;

			return false;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching -- literal query, no user input.
		$rows = $wpdb->get_results( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file'" );

		$map    = array(); // stored relpath => attachment id (first wins).
		$folds  = array(); // stored relpath => collation-folded form.
		$groups = array(); // folded form => count of distinct byte-paths.

		foreach ( (array) $rows as $row ) {
			$value = (string) $row->meta_value;
			if ( '' === $value || isset( $map[ $value ] ) ) {
				continue;
			}
			$map[ $value ]   = (int) $row->post_id;
			$fold            = self::collation_fold( $value );
			$folds[ $value ] = $fold;
			$groups[ $fold ] = isset( $groups[ $fold ] ) ? $groups[ $fold ] + 1 : 1;
		}

		unset( $rows );

		// The per-path fallback query matches with the column's (typically case/
		// accent-insensitive) collation, so byte-distinct paths that fold to the
		// same form could be served a DIFFERENT attachment by this byte-exact map
		// than the original query would return. Drop every such colliding path so
		// it defers to the unchanged collation query — exact legacy behaviour.
		// Normal libraries have zero collisions, so the map stays fully populated.
		foreach ( $folds as $value => $fold ) {
			if ( $groups[ $fold ] > 1 ) {
				unset( $map[ $value ] );
			}
		}

		self::$path_map = $map;

		return $map;
	}

	/**
	 * Fold a stored path the way a case/accent-insensitive DB collation would, to
	 * detect paths that the per-path query treats as equal. Folding at least as
	 * aggressively as the collation is safe: any extra grouping only makes more
	 * paths defer to the (correct) query.
	 *
	 * Assumes _wp_attached_file values are sanitize_file_name-normalized (ASCII
	 * after accent removal), which holds for the WP media pipeline. Width/kana/
	 * ligature collation expansions only matter for raw non-ASCII paths written
	 * directly to postmeta outside that pipeline, which is out of scope.
	 *
	 * @param string $value Stored relative path.
	 *
	 * @return string
	 */
	private static function collation_fold( $value ) {
		if ( function_exists( 'remove_accents' ) ) {
			$value = remove_accents( $value );
		}

		return function_exists( 'mb_strtolower' ) ? mb_strtolower( $value, 'UTF-8' ) : strtolower( $value );
	}

	/**
	 * Enable/disable the bulk path map for the current request. Disabled around
	 * single-attachment rescans so they don't build the whole-library map.
	 *
	 * @param bool $enabled Whether the map may be used.
	 *
	 * @return void
	 */
	public static function set_path_map_enabled( $enabled ) {
		self::$path_map_enabled = (bool) $enabled;
	}

	/**
	 * Normalize a captured upload path to its full-size relative form.
	 *
	 * @param string $relative Raw captured path.
	 *
	 * @return string
	 */
	private static function normalize_relative_path( $relative ) {
		if ( ! is_string( $relative ) ) {
			return '';
		}

		$relative = urldecode( $relative );
		$relative = ltrim( $relative, '/' );

		// Drop any trailing query/fragment that slipped into the capture.
		$relative = preg_replace( '/[?#].*$/', '', $relative );

		// Strip a next-gen sidecar extension (img.jpg.webp -> img.jpg).
		$relative = preg_replace( '/\.(webp|avif)$/i', '', $relative );

		// Strip the intermediate-size suffix (img-300x200.jpg -> img.jpg).
		$relative = preg_replace( '/-\d+x\d+(\.[A-Za-z0-9]{2,5})$/', '$1', $relative );

		return $relative;
	}

	/**
	 * Whether a post id is an existing attachment (request-cached).
	 *
	 * @param int $id Post id.
	 *
	 * @return bool
	 */
	public static function is_attachment( $id ) {
		static $cache = array();

		$id = (int) $id;
		if ( ! $id ) {
			return false;
		}

		if ( isset( $cache[ $id ] ) ) {
			return $cache[ $id ];
		}

		$cache[ $id ] = ( 'attachment' === get_post_type( $id ) );

		return $cache[ $id ];
	}

	/**
	 * Build (once per request) the regex that captures upload URLs/paths.
	 *
	 * Matches the local uploads path (which also covers absolute, protocol-
	 * relative and root-relative forms) and the Infinite Uploads CDN base.
	 * Capture group 1 is the uploads-relative path including extension.
	 *
	 * @return string|null
	 */
	private static function get_url_regex() {
		if ( null !== self::$url_regex ) {
			return self::$url_regex ? self::$url_regex : null;
		}

		$bases = array();

		// Local uploads URL -> path portion (e.g. /wp-content/uploads). Matching
		// just the path also catches https://, // and root-relative forms.
		$local_path = wp_parse_url( InfiniteUploadsHelper::get_local_upload_url(), PHP_URL_PATH );
		if ( $local_path ) {
			$bases[] = preg_quote( untrailingslashit( $local_path ), '#' );
		}

		// Infinite Uploads CDN base (scheme-stripped so // protocol-relative matches too).
		$cloud_base = InfiniteUploadsHelper::get_cloud_upload_url();
		if ( is_string( $cloud_base ) && false !== strpos( $cloud_base, '//' ) ) {
			$cloud_rel = preg_replace( '#^https?:#i', '', $cloud_base );
			$bases[]   = preg_quote( untrailingslashit( $cloud_rel ), '#' );
		}

		$bases = array_unique( array_filter( $bases ) );
		if ( empty( $bases ) ) {
			self::$url_regex = '';

			return null;
		}

		self::$url_regex = '#(?:' . implode( '|', $bases ) . ')/([A-Za-z0-9._/%-]+?\.[A-Za-z0-9]{2,5})#i';

		return self::$url_regex;
	}

	/**
	 * Reset request caches (used by long-running CLI/cron processes between sites).
	 *
	 * @return void
	 */
	public static function reset_cache() {
		self::$path_cache = array();
		self::$path_map   = null;
		self::$url_regex  = null;
	}
}
