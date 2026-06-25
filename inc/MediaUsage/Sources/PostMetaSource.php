<?php
/**
 * Scans wp_postmeta for attachment references.
 *
 * Handles three cases per row:
 *  - _thumbnail_id            -> featured-image reference (id value).
 *  - _product_image_gallery   -> WooCommerce gallery references (CSV of ids).
 *  - everything else          -> raw URL matching, which covers Elementor,
 *                                Beaver Builder, Bricks, Oxygen, ACF image
 *                                fields and any meta storing an upload URL.
 *
 * The query JOINs wp_posts so it (a) fetches a human label in one query and
 * (b) naturally excludes attachments' own self-describing metadata.
 *
 * @package InfiniteUploads
 */

namespace ClikIT\InfiniteUploads\MediaUsage\Sources;

use ClikIT\InfiniteUploads\MediaUsage\Matcher;

/**
 * Class PostMetaSource
 */
class PostMetaSource extends Source {

	/**
	 * Meta keys that never contain upload references (skip the regex on them).
	 */
	const SKIP_KEYS = "'_edit_lock','_edit_last','_wp_page_template','_wp_old_slug','_wp_old_date','_pingme','_encloseme','_wp_trash_meta_status','_wp_trash_meta_time'";

	/**
	 * {@inheritDoc}
	 */
	public function get_type() {
		return 'post_meta';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_label() {
		return __( 'Checking page builder & post meta', 'infinite-uploads' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function count_rows() {
		global $wpdb;

		return (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE p.post_type != 'attachment' AND p.post_status NOT IN ('trash','auto-draft')
			AND pm.meta_key NOT IN ( " . self::SKIP_KEYS . " )"
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function scan_batch( $after_id, $limit ) {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.meta_id, pm.post_id, pm.meta_key, pm.meta_value, p.post_title, p.post_type
				FROM {$wpdb->postmeta} pm
				INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE pm.meta_id > %d AND p.post_type != 'attachment' AND p.post_status NOT IN ('trash','auto-draft')
				AND pm.meta_key NOT IN ( " . self::SKIP_KEYS . " )
				ORDER BY pm.meta_id ASC LIMIT %d",
				(int) $after_id,
				(int) $limit
			)
		);

		$last_id   = (int) $after_id;
		$processed = 0;

		// One query for every ACF companion field-key in this batch, so the
		// per-row media-field check below reads a map instead of querying per row.
		$acf_companions = $this->prefetch_acf_companions( (array) $rows );

		foreach ( (array) $rows as $row ) {
			$last_id = (int) $row->meta_id;
			$processed++;

			$label = '' !== $row->post_title ? $row->post_title : sprintf( '#%d', $row->post_id );

			if ( '_thumbnail_id' === $row->meta_key ) {
				$attachment_id = (int) $row->meta_value;
				if ( $attachment_id && Matcher::is_attachment( $attachment_id ) ) {
					$this->record_for_post( $attachment_id, $row, $label, 'featured_image', '#' . $attachment_id );
				}
				continue;
			}

			if ( '_product_image_gallery' === $row->meta_key ) {
				foreach ( array_filter( array_map( 'absint', explode( ',', (string) $row->meta_value ) ) ) as $attachment_id ) {
					if ( Matcher::is_attachment( $attachment_id ) ) {
						$this->record_for_post( $attachment_id, $row, $label, 'woocommerce_gallery', '#' . $attachment_id );
					}
				}
				continue;
			}

			// Collect references from every applicable signal, deduped per
			// attachment so a single row never records the same file twice.
			$meta_value = (string) $row->meta_value;
			$meta_key   = (string) $row->meta_key;
			$found      = array();

			// URLs in the raw value (covers most builders incl. Bricks/Elementor).
			foreach ( Matcher::find_url_references( $meta_value ) as $attachment_id => $matched ) {
				$found[ $attachment_id ] = array( self::reference_type_for_key( $meta_key ), $matched );
			}

			if ( self::is_oxygen_key( $meta_key ) ) {
				// Oxygen stores bare attachment ids inside its builder JSON.
				foreach ( Matcher::find_oxygen_references( $meta_value ) as $attachment_id => $matched ) {
					if ( ! isset( $found[ $attachment_id ] ) ) {
						$found[ $attachment_id ] = array( 'oxygen', $matched );
					}
				}
			} elseif ( self::is_bricks_key( $meta_key ) ) {
				// Bricks persists attachment ids and re-derives URLs at render, so
				// image/gallery/background refs survive only as ids in its JSON.
				foreach ( Matcher::find_bricks_references( $meta_value ) as $attachment_id => $matched ) {
					if ( ! isset( $found[ $attachment_id ] ) ) {
						$found[ $attachment_id ] = array( 'bricks', $matched );
					}
				}
			} elseif ( '_' !== substr( $meta_key, 0, 1 ) && Matcher::looks_like_id_candidate( $meta_value ) && $this->is_acf_media_field( (int) $row->post_id, $meta_key, $acf_companions ) ) {
				// ACF Image/File/Gallery fields store bare/serialized ids; the
				// `_{key}` companion field key confirms both that it's an ACF field
				// AND that its type is media (not a numeric Number/repeater value).
				foreach ( Matcher::find_serialized_id_references( $meta_value ) as $attachment_id => $matched ) {
					if ( ! isset( $found[ $attachment_id ] ) ) {
						$found[ $attachment_id ] = array( 'acf', $matched );
					}
				}
			}

			foreach ( $found as $attachment_id => $info ) {
				$this->record_for_post( $attachment_id, $row, $label, $info[0], $info[1] );
			}
		}

		return array(
			'last_id'   => $last_id,
			'processed' => $processed,
			'done'      => ( $processed < (int) $limit ),
		);
	}

	/**
	 * Record a reference attributed to the owning post.
	 *
	 * @param int    $attachment_id Attachment id.
	 * @param object $row Meta row (with post_id/post_type).
	 * @param string $label Post label.
	 * @param string $reference_type Reference type.
	 * @param string $matched Matched value.
	 *
	 * @return void
	 */
	private function record_for_post( $attachment_id, $row, $label, $reference_type, $matched ) {
		$url = get_permalink( $row->post_id );

		$this->record(
			$attachment_id,
			array(
				'source_type'    => $row->post_type,
				'source_id'      => (int) $row->post_id,
				'source_label'   => $label,
				'reference_type' => $reference_type,
				'confidence'     => Matcher::CONFIDENCE_HIGH,
				'matched_value'  => $matched,
				'source_url'     => $url ? $url : '',
			)
		);
	}

	/**
	 * Map a meta key to a friendly reference type (drives the column labels).
	 *
	 * @param string $meta_key Meta key.
	 *
	 * @return string
	 */
	public static function reference_type_for_key( $meta_key ) {
		if ( '_elementor_data' === $meta_key ) {
			return 'elementor';
		}
		if ( '_fl_builder_data' === $meta_key || '_fl_builder_draft' === $meta_key ) {
			return 'beaver_builder';
		}
		if ( self::is_bricks_key( $meta_key ) ) {
			return 'bricks';
		}
		if ( self::is_oxygen_key( $meta_key ) ) {
			return 'oxygen';
		}

		return 'postmeta';
	}

	/**
	 * Whether a meta key holds Bricks Builder page data. Covers the content,
	 * header and footer keys (each carries a builder-version suffix, e.g.
	 * `_bricks_page_content_2`).
	 *
	 * @param string $meta_key Meta key.
	 *
	 * @return bool
	 */
	public static function is_bricks_key( $meta_key ) {
		return 0 === strpos( $meta_key, '_bricks_page' );
	}

	/**
	 * Whether a meta key holds Oxygen Builder page data.
	 *
	 * @param string $meta_key Meta key.
	 *
	 * @return bool
	 */
	public static function is_oxygen_key( $meta_key ) {
		return in_array(
			$meta_key,
			array( 'ct_builder_json', '_ct_builder_json', 'ct_builder_shortcodes', '_ct_builder_shortcodes' ),
			true
		);
	}

	/**
	 * Confirm a meta key is an ACF Image/File/Gallery field on the given post,
	 * using the batch-prefetched companion map. ACF stores the field key in a
	 * `_{key}` companion meta (field_xxxx); the key is resolved to its field type
	 * so only media field types (which store attachment ids) match — numeric
	 * Number/True-False values and repeater/flexible row counts also carry a
	 * `field_*` companion and must be excluded.
	 *
	 * @param int    $post_id Post id.
	 * @param string $meta_key Field meta key (without leading underscore).
	 * @param array  $companions Prefetched "post_id\0_key" => field-key map.
	 *
	 * @return bool
	 */
	private function is_acf_media_field( $post_id, $meta_key, array $companions ) {
		$cache_key = (int) $post_id . "\0" . '_' . $meta_key;
		$companion = isset( $companions[ $cache_key ] ) ? $companions[ $cache_key ] : null;

		return Matcher::acf_key_is_media( $companion );
	}

	/**
	 * Prefetch the ACF `_{key}` companion field-keys for every candidate row in
	 * the batch (non-underscore key with an id-shaped value) in one query, so the
	 * per-row media-field check doesn't issue a query each.
	 *
	 * @param object[] $rows Meta rows for the batch.
	 *
	 * @return array Map of "post_id\0_key" => companion meta_value.
	 */
	private function prefetch_acf_companions( array $rows ) {
		$placeholders = array();
		$values       = array();
		$seen         = array();

		foreach ( $rows as $row ) {
			$meta_key = (string) $row->meta_key;
			if ( '_' === substr( $meta_key, 0, 1 ) || ! Matcher::looks_like_id_candidate( (string) $row->meta_value ) ) {
				continue;
			}

			$post_id   = (int) $row->post_id;
			$companion = '_' . $meta_key;
			$key       = $post_id . "\0" . $companion;
			if ( isset( $seen[ $key ] ) ) {
				continue;
			}
			$seen[ $key ] = true;

			$placeholders[] = '( %d, %s )';
			$values[]       = $post_id;
			$values[]       = $companion;
		}

		if ( empty( $placeholders ) ) {
			return array();
		}

		global $wpdb;

		$sql = "SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE ( post_id, meta_key ) IN ( " . implode( ', ', $placeholders ) . ' )';

		$results = $wpdb->get_results( $wpdb->prepare( $sql, $values ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- placeholders literal; values bound via prepare.

		$map = array();
		foreach ( (array) $results as $r ) {
			$map[ (int) $r->post_id . "\0" . (string) $r->meta_key ] = (string) $r->meta_value;
		}

		return $map;
	}
}
