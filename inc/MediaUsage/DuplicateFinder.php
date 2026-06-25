<?php
/**
 * Lightweight possible-duplicate detection for the Media Library.
 *
 * Uses only cheap signals already in WordPress attachment records/metadata:
 * a normalized filename, MIME type, dimensions, and (when available) file
 * size. It never opens image files, never hashes on its own, and never scans
 * the uploads directory or generated thumbnail sizes. Exact verification is a
 * separate, on-demand step (see Duplicates::ajax_verify()).
 *
 * @package InfiniteUploads
 */

namespace ClikIT\InfiniteUploads\MediaUsage;

/**
 * Class DuplicateFinder
 */
class DuplicateFinder {

	/**
	 * Confidence labels.
	 */
	const LABEL_LIKELY       = 'likely';
	const LABEL_POSSIBLE     = 'possible';
	const LABEL_NEEDS_REVIEW = 'needs_review';
	const LABEL_WEAK         = 'weak';

	/**
	 * Normalize a filename for duplicate grouping: drop the extension, lower-
	 * case, and strip common duplicate suffixes (-1, -copy, (1), etc.). Numeric
	 * suffixes are limited to 1-2 digits so years like "photo-2024" aren't
	 * collapsed into "photo".
	 *
	 * @param string $filename A file name or path.
	 *
	 * @return string
	 */
	public static function normalize_filename( $filename ) {
		$name = wp_basename( (string) $filename );
		$name = preg_replace( '/\.[A-Za-z0-9]{1,5}$/', '', $name ); // Drop extension.
		$name = strtolower( trim( $name ) );
		$name = preg_replace( '/\s+/', ' ', $name ); // Collapse runs of whitespace so group keys stay stable.

		$patterns = array(
			'/[\s_-]*\(\d{1,3}\)\s*$/',                 // " (1)", "(2)"
			'/[\s_-]*copy(?:[\s_-]*\d{1,3})?\s*$/',     // "-copy", " copy", "-copy-1"
			'/-\d{1,2}$/',                              // "-1", "-2" (WP unique suffix)
		);

		// Strip repeatedly so "logo-copy-1" / "logo (1)-2" fully reduce.
		$previous = null;
		while ( $previous !== $name ) {
			$previous = $name;
			foreach ( $patterns as $pattern ) {
				$name = preg_replace( $pattern, '', $name );
			}
			$name = trim( $name );
		}

		return '' === $name ? strtolower( wp_basename( (string) $filename ) ) : $name;
	}

	/**
	 * Collect lightweight records for one batch of image attachments.
	 *
	 * @param int $after_id Cursor: only attachments with ID greater than this.
	 * @param int $limit Batch size.
	 *
	 * @return array{records:array,last_id:int,processed:int,done:bool}
	 */
	public static function scan_batch( $after_id, $limit ) {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_mime_type, post_date FROM {$wpdb->posts}
				WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%' AND ID > %d
				ORDER BY ID ASC LIMIT %d",
				(int) $after_id,
				(int) $limit
			)
		);

		// Distinguish a real query failure (null) from a legitimately empty
		// final batch ([]), so the caller doesn't treat an error as "done".
		if ( null === $rows ) {
			return array(
				'records'   => array(),
				'last_id'   => (int) $after_id,
				'processed' => 0,
				'done'      => false,
				'error'     => true,
			);
		}

		$records   = array();
		$last_id   = (int) $after_id;
		$processed = 0;

		foreach ( (array) $rows as $row ) {
			$last_id = (int) $row->ID;
			$processed++;

			$meta = wp_get_attachment_metadata( (int) $row->ID );
			$meta = is_array( $meta ) ? $meta : array();

			$file = isset( $meta['file'] ) && '' !== $meta['file'] ? $meta['file'] : (string) get_attached_file( (int) $row->ID );
			$name = wp_basename( $file );

			$records[] = array(
				'id'         => (int) $row->ID,
				'name'       => $name,
				'normalized' => self::normalize_filename( $name ),
				'mime'       => (string) $row->post_mime_type,
				'width'      => isset( $meta['width'] ) ? (int) $meta['width'] : 0,
				'height'     => isset( $meta['height'] ) ? (int) $meta['height'] : 0,
				'size'       => isset( $meta['filesize'] ) ? (int) $meta['filesize'] : 0,
				'date'       => substr( (string) $row->post_date, 0, 10 ),
			);
		}

		return array(
			'records'   => $records,
			'last_id'   => $last_id,
			'processed' => $processed,
			'done'      => ( $processed < (int) $limit ),
			'error'     => false,
		);
	}

	/**
	 * Group collected records into possible-duplicate groups.
	 *
	 * Groups share a normalized filename AND MIME type (>= 2 members). Each
	 * group's label/reason is derived from how closely dimensions and sizes
	 * match. Weak groups (different dimensions and very different sizes) are
	 * omitted unless $show_weak is true.
	 *
	 * @param array $records   All collected records.
	 * @param bool  $show_weak Include weak groups.
	 *
	 * @return array List of group descriptors.
	 */
	public static function group( array $records, $show_weak = false ) {
		$buckets = array();
		foreach ( $records as $rec ) {
			$key                = $rec['normalized'] . '|' . $rec['mime'];
			$buckets[ $key ][] = $rec;
		}

		$groups = array();
		foreach ( $buckets as $key => $members ) {
			if ( count( $members ) < 2 ) {
				continue;
			}

			list( $label, $reason ) = self::evaluate( $members );

			if ( self::LABEL_WEAK === $label && ! $show_weak ) {
				continue;
			}

			$groups[] = array(
				'key'     => $key,
				'label'   => $label,
				'reason'  => $reason,
				'members' => $members,
			);
		}

		// Strongest first.
		$order = array(
			self::LABEL_LIKELY       => 0,
			self::LABEL_POSSIBLE     => 1,
			self::LABEL_NEEDS_REVIEW => 2,
			self::LABEL_WEAK         => 3,
		);
		usort(
			$groups,
			function ( $a, $b ) use ( $order ) {
				return $order[ $a['label'] ] - $order[ $b['label'] ];
			}
		);

		return $groups;
	}

	/**
	 * Determine a group's label + human reason from its members.
	 *
	 * @param array $members Group members.
	 *
	 * @return array{0:string,1:string} [ label, reason ]
	 */
	private static function evaluate( array $members ) {
		// Dimensions: classify as all-known-and-same, all-known-and-different,
		// or unknown (at least one member missing width/height).
		$known_dims = array();
		$all_known  = true;
		foreach ( $members as $m ) {
			if ( $m['width'] && $m['height'] ) {
				$known_dims[ $m['width'] . 'x' . $m['height'] ] = true;
			} else {
				$all_known = false;
			}
		}
		$same_dims = ( $all_known && 1 === count( $known_dims ) );
		$diff_dims = ( $all_known && count( $known_dims ) > 1 );

		// Sizes: spread between the largest and smallest known file size.
		$sizes = array();
		foreach ( $members as $m ) {
			if ( $m['size'] > 0 ) {
				$sizes[] = (int) $m['size'];
			}
		}
		$size_spread = null;
		if ( count( $sizes ) >= 2 ) {
			$max         = max( $sizes );
			$min         = min( $sizes );
			$size_spread = $max > 0 ? ( ( $max - $min ) / $max ) * 100 : 0;
		}
		$size_close   = ( null !== $size_spread && $size_spread <= 5 );
		$size_similar = ( null !== $size_spread && $size_spread <= 15 );

		// Whole, translatable sentences (no glued fragments) per situation.
		if ( $same_dims && $size_close ) {
			return array( self::LABEL_LIKELY, __( 'Same filename, same dimensions, same file type and near-identical file size.', 'infinite-uploads' ) );
		}
		if ( $same_dims ) {
			return array( self::LABEL_POSSIBLE, __( 'Same filename, same dimensions and same file type.', 'infinite-uploads' ) );
		}
		if ( $diff_dims && $size_similar ) {
			return array( self::LABEL_NEEDS_REVIEW, __( 'Same filename and file type, but different dimensions with a similar file size.', 'infinite-uploads' ) );
		}
		if ( $diff_dims ) {
			return array( self::LABEL_WEAK, __( 'Same filename and file type, but different dimensions and file size.', 'infinite-uploads' ) );
		}
		// Dimensions unknown for at least one member — judge on size alone.
		if ( $size_close ) {
			return array( self::LABEL_POSSIBLE, __( 'Same filename and file type with near-identical file size (dimensions unknown).', 'infinite-uploads' ) );
		}
		if ( $size_similar ) {
			return array( self::LABEL_NEEDS_REVIEW, __( 'Same filename and file type with a similar file size (dimensions unknown).', 'infinite-uploads' ) );
		}

		return array( self::LABEL_WEAK, __( 'Same filename and file type only (dimensions and file size could not be compared).', 'infinite-uploads' ) );
	}
}
