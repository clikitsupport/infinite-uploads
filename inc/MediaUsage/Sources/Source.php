<?php
/**
 * Base class for Media Library Usage Scanner reference sources.
 *
 * A "source" is one location that may reference an attachment (post content,
 * post meta, options, term meta, user meta, the front-end crawl, ...). Each
 * source iterates its rows with keyset pagination (cursor by row id) so deep
 * scans never pay the OFFSET penalty on large tables.
 *
 * @package InfiniteUploads
 */

namespace ClikIT\InfiniteUploads\MediaUsage\Sources;

use ClikIT\InfiniteUploads\MediaUsage\Store;

/**
 * Class Source
 */
abstract class Source {

	/**
	 * Current scan-run id (for stamping recorded references).
	 *
	 * @var int
	 */
	protected $run_id = 0;

	/**
	 * References collected during the current batch, flushed in one bulk insert.
	 *
	 * @var array[]
	 */
	private $reference_buffer = array();

	/**
	 * Set the active scan-run id.
	 *
	 * @param int $run_id Run id.
	 *
	 * @return void
	 */
	public function set_run_id( $run_id ) {
		$this->run_id = (int) $run_id;
	}

	/**
	 * Stable machine identifier for this source.
	 *
	 * @return string
	 */
	abstract public function get_type();

	/**
	 * Human-readable label shown in scan progress.
	 *
	 * @return string
	 */
	abstract public function get_label();

	/**
	 * Approximate number of rows to scan (for progress display).
	 *
	 * @return int
	 */
	abstract public function count_rows();

	/**
	 * Scan one batch of rows whose id is greater than $after_id.
	 *
	 * @param int $after_id Cursor: only rows with id greater than this.
	 * @param int $limit Max rows to process.
	 *
	 * @return array{last_id:int,processed:int,done:bool} New cursor, rows
	 *         processed, and whether this source has no further rows.
	 */
	abstract public function scan_batch( $after_id, $limit );

	/**
	 * Persist one reference row, stamped with the current run id.
	 *
	 * @param int   $attachment_id Attachment id.
	 * @param array $args Reference fields (source_type, source_id, source_label,
	 *                    reference_type, confidence, matched_value, source_url).
	 *
	 * @return void
	 */
	protected function record( $attachment_id, array $args ) {
		$args['attachment_id']    = (int) $attachment_id;
		$args['scan_run_id']      = $this->run_id;
		$this->reference_buffer[] = $args;
	}

	/**
	 * Persist all references buffered during the current batch in one bulk
	 * insert, then clear the buffer. Called by the engine after each scan_batch().
	 *
	 * @return void
	 */
	public function flush_references() {
		if ( empty( $this->reference_buffer ) ) {
			return;
		}

		Store::add_references_bulk( $this->reference_buffer );
		$this->reference_buffer = array();
	}
}
