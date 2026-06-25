<?php
/**
 * Scans wp_posts.post_content for attachment references.
 *
 * Covers raw URLs, Gutenberg blocks (wp-image-{id}), gallery shortcodes, and
 * any builder that stores its markup in post_content (Divi, Avada/Fusion, and
 * any shortcode/HTML output) via the shared URL matcher.
 *
 * @package InfiniteUploads
 */

namespace ClikIT\InfiniteUploads\MediaUsage\Sources;

use ClikIT\InfiniteUploads\MediaUsage\Matcher;

/**
 * Class PostContentSource
 */
class PostContentSource extends Source {

	/**
	 * Post statuses worth scanning (excludes trash/auto-draft and attachments).
	 */
	const STATUSES = "'publish','future','draft','pending','private'";

	/**
	 * {@inheritDoc}
	 */
	public function get_type() {
		return 'post_content';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_label() {
		return __( 'Checking post content', 'infinite-uploads' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function count_rows() {
		global $wpdb;

		return (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type NOT IN ('attachment','revision') AND post_status IN ( " . self::STATUSES . " )"
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function scan_batch( $after_id, $limit ) {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title, post_type, post_content FROM {$wpdb->posts}
				WHERE ID > %d AND post_type NOT IN ('attachment','revision') AND post_status IN ( " . self::STATUSES . " )
				ORDER BY ID ASC LIMIT %d",
				(int) $after_id,
				(int) $limit
			)
		);

		$last_id   = (int) $after_id;
		$processed = 0;

		foreach ( (array) $rows as $row ) {
			$last_id = (int) $row->ID;
			$processed++;

			$content = (string) $row->post_content;
			if ( '' === $content ) {
				continue;
			}

			// id-based matches take precedence (more specific) over plain URL hits.
			$best = array();

			foreach ( Matcher::find_id_references( $content ) as $attachment_id => $info ) {
				$best[ $attachment_id ] = array(
					'reference_type' => $info['reference_type'],
					'matched_value'  => $info['matched_value'],
				);
			}

			foreach ( Matcher::find_url_references( $content ) as $attachment_id => $matched ) {
				if ( ! isset( $best[ $attachment_id ] ) ) {
					$best[ $attachment_id ] = array(
						'reference_type' => 'content_url',
						'matched_value'  => $matched,
					);
				}
			}

			if ( empty( $best ) ) {
				continue;
			}

			$label = '' !== $row->post_title ? $row->post_title : sprintf( '#%d', $row->ID );
			$url   = get_permalink( $row->ID );

			foreach ( $best as $attachment_id => $ref ) {
				$this->record(
					$attachment_id,
					array(
						'source_type'    => $row->post_type,
						'source_id'      => (int) $row->ID,
						'source_label'   => $label,
						'reference_type' => $ref['reference_type'],
						'confidence'     => Matcher::CONFIDENCE_HIGH,
						'matched_value'  => $ref['matched_value'],
						'source_url'     => $url ? $url : '',
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
}
