<?php
/**
 * Scans wp_usermeta for attachment references (URL matching).
 *
 * @package InfiniteUploads
 */

namespace ClikIT\InfiniteUploads\MediaUsage\Sources;

use ClikIT\InfiniteUploads\MediaUsage\Matcher;

/**
 * Class UserMetaSource
 */
class UserMetaSource extends Source {

	/**
	 * {@inheritDoc}
	 */
	public function get_type() {
		return 'user_meta';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_label() {
		return __( 'Checking user meta', 'infinite-uploads' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function count_rows() {
		// wp_usermeta is a single network-global table shared by every site, so
		// scanning it per-subsite would attribute other sites' data to this one.
		if ( is_multisite() ) {
			return 0;
		}

		global $wpdb;

		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->usermeta}" );
	}

	/**
	 * {@inheritDoc}
	 */
	public function scan_batch( $after_id, $limit ) {
		// Skipped on multisite (global table — see count_rows()).
		if ( is_multisite() ) {
			return array(
				'last_id'   => (int) $after_id,
				'processed' => 0,
				'done'      => true,
			);
		}

		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT umeta_id, user_id, meta_key, meta_value FROM {$wpdb->usermeta}
				WHERE umeta_id > %d
				ORDER BY umeta_id ASC LIMIT %d",
				(int) $after_id,
				(int) $limit
			)
		);

		$last_id   = (int) $after_id;
		$processed = 0;

		foreach ( (array) $rows as $row ) {
			$last_id = (int) $row->umeta_id;
			$processed++;

			$meta_value = (string) $row->meta_value;
			$meta_key   = (string) $row->meta_key;
			$found      = array();

			foreach ( Matcher::find_url_references( $meta_value ) as $attachment_id => $matched ) {
				$found[ $attachment_id ] = array( 'user_meta', $matched );
			}

			// ACF media fields on users store bare/serialized ids with no URL;
			// the `_{key}` companion field key confirms a media field type.
			if ( '_' !== substr( $meta_key, 0, 1 ) && Matcher::looks_like_id_candidate( $meta_value ) && $this->is_acf_media_field( (int) $row->user_id, $meta_key ) ) {
				foreach ( Matcher::find_serialized_id_references( $meta_value ) as $attachment_id => $matched ) {
					if ( ! isset( $found[ $attachment_id ] ) ) {
						$found[ $attachment_id ] = array( 'acf', $matched );
					}
				}
			}

			if ( empty( $found ) ) {
				continue;
			}

			foreach ( $found as $attachment_id => $info ) {
				$this->record(
					$attachment_id,
					array(
						'source_type'    => 'user',
						'source_id'      => (int) $row->user_id,
						'source_label'   => sprintf( 'user #%d', $row->user_id ),
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
	 * Confirm a user meta key is an ACF media field by resolving its `_{key}`
	 * companion (field_xxxx) to a media field type. Mirrors PostMetaSource.
	 *
	 * @param int    $user_id User id.
	 * @param string $meta_key Field meta key (without leading underscore).
	 *
	 * @return bool
	 */
	private function is_acf_media_field( $user_id, $meta_key ) {
		global $wpdb;

		$companion = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = %s LIMIT 1",
				$user_id,
				'_' . $meta_key
			)
		);

		return Matcher::acf_key_is_media( $companion );
	}
}
