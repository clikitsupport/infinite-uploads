<?php
/**
 * Scans wp_termmeta for attachment references (URL matching).
 *
 * @package InfiniteUploads
 */

namespace ClikIT\InfiniteUploads\MediaUsage\Sources;

use ClikIT\InfiniteUploads\MediaUsage\Matcher;

/**
 * Class TermMetaSource
 */
class TermMetaSource extends Source {

	/**
	 * {@inheritDoc}
	 */
	public function get_type() {
		return 'term_meta';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_label() {
		return __( 'Checking term meta', 'infinite-uploads' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function count_rows() {
		global $wpdb;

		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->termmeta}" );
	}

	/**
	 * {@inheritDoc}
	 */
	public function scan_batch( $after_id, $limit ) {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT tm.meta_id, tm.term_id, tm.meta_key, tm.meta_value, t.name
				FROM {$wpdb->termmeta} tm
				LEFT JOIN {$wpdb->terms} t ON t.term_id = tm.term_id
				WHERE tm.meta_id > %d
				ORDER BY tm.meta_id ASC LIMIT %d",
				(int) $after_id,
				(int) $limit
			)
		);

		$last_id   = (int) $after_id;
		$processed = 0;

		foreach ( (array) $rows as $row ) {
			$last_id = (int) $row->meta_id;
			$processed++;

			$meta_value = (string) $row->meta_value;
			$meta_key   = (string) $row->meta_key;
			$found      = array();

			foreach ( Matcher::find_url_references( $meta_value ) as $attachment_id => $matched ) {
				$found[ $attachment_id ] = array( 'term_meta', $matched );
			}

			// ACF media fields on terms store bare/serialized ids with no URL;
			// the `_{key}` companion field key confirms a media field type.
			if ( '_' !== substr( $meta_key, 0, 1 ) && Matcher::looks_like_id_candidate( $meta_value ) && $this->is_acf_media_field( (int) $row->term_id, $meta_key ) ) {
				foreach ( Matcher::find_serialized_id_references( $meta_value ) as $attachment_id => $matched ) {
					if ( ! isset( $found[ $attachment_id ] ) ) {
						$found[ $attachment_id ] = array( 'acf', $matched );
					}
				}
			}

			if ( empty( $found ) ) {
				continue;
			}

			$label = ! empty( $row->name ) ? $row->name : sprintf( 'term #%d', $row->term_id );
			foreach ( $found as $attachment_id => $info ) {
				$this->record(
					$attachment_id,
					array(
						'source_type'    => 'term',
						'source_id'      => (int) $row->term_id,
						'source_label'   => $label,
						'reference_type' => $info[0],
						'confidence'     => Matcher::CONFIDENCE_HIGH,
						'matched_value'  => $info[1],
					)
				);
			}
		}

		return array(
			'last_id'   => $last_id,
			'processed' => $processed,
			'done'      => ( $processed < (int) $limit ),
		);
	}

	/**
	 * Confirm a term meta key is an ACF media field by resolving its `_{key}`
	 * companion (field_xxxx) to a media field type. Mirrors PostMetaSource.
	 *
	 * @param int    $term_id Term id.
	 * @param string $meta_key Field meta key (without leading underscore).
	 *
	 * @return bool
	 */
	private function is_acf_media_field( $term_id, $meta_key ) {
		global $wpdb;

		$companion = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_value FROM {$wpdb->termmeta} WHERE term_id = %d AND meta_key = %s LIMIT 1",
				$term_id,
				'_' . $meta_key
			)
		);

		return Matcher::acf_key_is_media( $companion );
	}
}
