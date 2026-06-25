<?php
/**
 * Front-end crawl source.
 *
 * Fetches public URLs and scans the RENDERED HTML for attachment references.
 * This catches media output dynamically by themes/templates/archives that no
 * database source can see. Crawling is done in small, network-bound batches
 * and tolerates fetch failures so a blocked or slow URL never fails the scan.
 *
 * Because rendered HTML contains the upload URLs (img src/srcset, picture
 * source, a href, video src/poster, source src, iframe src, inline style
 * background-image, CSS url(), data-src/-srcset/-bg, and JSON blobs), the
 * shared URL matcher resolves all of these from the page body in one pass.
 *
 * @package InfiniteUploads
 */

namespace ClikIT\InfiniteUploads\MediaUsage\Sources;

use ClikIT\InfiniteUploads\MediaUsage\Matcher;

/**
 * Class FrontEndCrawlSource
 */
class FrontEndCrawlSource extends Source {

	/**
	 * URLs fetched per batch (kept small: each is a remote HTTP request).
	 */
	const CRAWL_BATCH = 5;

	/**
	 * Per-request fetch timeout (seconds).
	 */
	const TIMEOUT = 10;

	/**
	 * {@inheritDoc}
	 */
	public function get_type() {
		return 'frontend';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_label() {
		return __( 'Crawling public pages', 'infinite-uploads' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function count_rows() {
		global $wpdb;

		$types        = $this->public_post_types();
		$placeholders = implode( ',', array_fill( 0, count( $types ), '%s' ) );

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type IN ( {$placeholders} )",
				$types
			)
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function scan_batch( $after_id, $limit ) {
		global $wpdb;

		// The home page (and posts page) are not always represented by a single
		// crawlable post, so crawl the site root once at the start.
		if ( 0 === (int) $after_id ) {
			$this->crawl_url( home_url( '/' ), 0, get_bloginfo( 'name' ) );
		}

		$types        = $this->public_post_types();
		$placeholders = implode( ',', array_fill( 0, count( $types ), '%s' ) );

		$params = $types;
		$params[] = (int) $after_id;
		$params[] = self::CRAWL_BATCH;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title FROM {$wpdb->posts}
				WHERE post_status = 'publish' AND post_type IN ( {$placeholders} ) AND ID > %d
				ORDER BY ID ASC LIMIT %d",
				$params
			)
		);

		$last_id   = (int) $after_id;
		$processed = 0;

		foreach ( (array) $rows as $row ) {
			$last_id = (int) $row->ID;
			$processed++;

			$url = get_permalink( $row->ID );
			if ( $url ) {
				$label = '' !== $row->post_title ? $row->post_title : sprintf( '#%d', $row->ID );
				$this->crawl_url( $url, (int) $row->ID, $label );
			}
		}

		return array(
			'last_id'   => $last_id,
			'processed' => $processed,
			'done'      => ( $processed < self::CRAWL_BATCH ),
		);
	}

	/**
	 * Fetch one URL and record any attachment references in the rendered HTML.
	 *
	 * @param string $url Public URL.
	 * @param int    $source_id Originating post id (0 for the home page).
	 * @param string $label Human label.
	 *
	 * @return void
	 */
	private function crawl_url( $url, $source_id, $label ) {
		// Confine the crawler to this site's own host. get_permalink() is
		// filterable (e.g. "Page Links To"), so a permalink could point off-site;
		// only ever fetch same-origin http(s) URLs.
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( ! $host || strtolower( $host ) !== strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) ) ) {
			return;
		}

		$response = wp_remote_get(
			$url,
			array(
				'timeout'             => self::TIMEOUT,
				'redirection'         => 2,
				'limit_response_size' => Matcher::MAX_BLOB_BYTES,
				'user-agent'          => 'InfiniteUploads-MediaUsageScanner/1.0; ' . home_url( '/' ),
			)
		);

		// Tolerate failures: a blocked/slow/erroring URL is skipped, not fatal.
		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return;
		}

		$body = (string) wp_remote_retrieve_body( $response );
		if ( '' === $body || strlen( $body ) > Matcher::MAX_BLOB_BYTES ) {
			return;
		}

		$best = array();
		foreach ( Matcher::find_id_references( $body ) as $attachment_id => $info ) {
			$best[ $attachment_id ] = $info['matched_value'];
		}
		foreach ( Matcher::find_url_references( $body ) as $attachment_id => $matched ) {
			if ( ! isset( $best[ $attachment_id ] ) ) {
				$best[ $attachment_id ] = $matched;
			}
		}

		foreach ( $best as $attachment_id => $matched ) {
			$this->record(
				$attachment_id,
				array(
					'source_type'    => 'frontend',
					'source_id'      => $source_id,
					'source_label'   => $label,
					'reference_type' => 'rendered_html',
					'confidence'     => Matcher::CONFIDENCE_HIGH,
					'matched_value'  => $matched,
					'source_url'     => $url,
				)
			);
		}
	}

	/**
	 * Publicly-queryable post types worth crawling (excludes attachments).
	 *
	 * @return string[]
	 */
	private function public_post_types() {
		// public => true covers everything with a viewable front end (including
		// 'page', which is public but not publicly_queryable).
		$types = get_post_types( array( 'public' => true ) );
		unset( $types['attachment'] );

		return array_values( $types );
	}
}
