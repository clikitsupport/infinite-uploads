<?php
/**
 * Scan orchestration for the Media Library Usage Scanner.
 *
 * Runs an "inverted sweep": every content/meta/option blob is scanned ONCE and
 * the references found in it are resolved back to attachment ids (cheap and
 * scalable), instead of searching all content per attachment. After the sweep,
 * a finalize pass walks the attachments and computes each verdict.
 *
 * All work is batched and the cursor is persisted to an option, so the exact
 * same process_batch() drives both the on-demand AJAX runner and the
 * Action Scheduler background job.
 *
 * @package InfiniteUploads
 */

namespace ClikIT\InfiniteUploads\MediaUsage;

use ClikIT\InfiniteUploads\MediaUsage\Sources\PostContentSource;
use ClikIT\InfiniteUploads\MediaUsage\Sources\PostMetaSource;
use ClikIT\InfiniteUploads\MediaUsage\Sources\OptionSource;
use ClikIT\InfiniteUploads\MediaUsage\Sources\TermMetaSource;
use ClikIT\InfiniteUploads\MediaUsage\Sources\UserMetaSource;
use ClikIT\InfiniteUploads\MediaUsage\Sources\FrontEndCrawlSource;

/**
 * Class Engine
 */
class Engine {

	/**
	 * Per-site option holding the resumable scan cursor/state.
	 */
	const STATE_OPTION = 'iup_media_usage_scan_state';

	/**
	 * Rows scanned per sweep batch.
	 */
	const SWEEP_BATCH = 100;

	/**
	 * Attachments finalized per batch.
	 */
	const FINALIZE_BATCH = 100;

	/**
	 * Short-lived lock so the background worker and a browser-driven step never
	 * process a batch at the same time.
	 */
	const LOCK_OPTION = 'iup_media_usage_lock';

	/**
	 * Lock lifetime (seconds). A batch is fast; this only needs to exceed one
	 * batch so a crashed run auto-releases the lock and can be resumed.
	 */
	const LOCK_TTL = 120;

	/**
	 * Build the ordered list of reference sources.
	 *
	 * @return Sources\Source[]
	 */
	public static function get_sources() {
		return array(
			new PostContentSource(),
			new PostMetaSource(),
			new OptionSource(),
			new TermMetaSource(),
			new UserMetaSource(),
			// Front-end crawl runs last: it catches dynamically-output media that
			// no database source can see (theme templates, archives, etc.).
			new FrontEndCrawlSource(),
		);
	}

	/**
	 * Begin a new scan run: create the run row, clear prior references, and
	 * initialize the resumable state.
	 *
	 * @param int $user_id User starting the run.
	 *
	 * @return int Run id (0 on failure).
	 */
	public static function start_run( $user_id ) {
		if ( ! Schema::tables_exist() ) {
			return 0;
		}

		global $wpdb;
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'" );

		// Fresh sweep: clear references and drop verdicts for attachments that
		// have since been deleted, so counts can't drift.
		Store::clear_references();
		Store::delete_orphaned_items();

		$run_id = Store::create_run( $user_id, $total );
		if ( ! $run_id ) {
			return 0;
		}

		update_option(
			self::STATE_OPTION,
			array(
				'run_id'       => $run_id,
				'phase'        => 'sweep',
				'source_index' => 0,
				'cursor'       => 0,
				'total'        => $total,
				'scanned'      => 0,
			),
			false
		);

		return $run_id;
	}

	/**
	 * Process the next batch of the active run.
	 *
	 * @return array Progress descriptor: status (running|complete|idle), phase,
	 *               message, total, scanned, percent, run_id.
	 */
	public static function process_batch() {
		$state = get_option( self::STATE_OPTION );
		if ( ! is_array( $state ) || empty( $state['run_id'] ) ) {
			return array( 'status' => 'idle' );
		}

		$run = Store::get_run( $state['run_id'] );
		if ( ! $run || Store::RUN_RUNNING !== $run->status ) {
			// Run was cancelled/paused elsewhere; stop touching it.
			return array( 'status' => 'idle' );
		}

		if ( 'sweep' === $state['phase'] ) {
			return self::process_sweep( $state );
		}

		if ( 'finalize' === $state['phase'] ) {
			return self::process_finalize( $state );
		}

		return self::finish( $state );
	}

	/**
	 * Process one batch under a short lock so the Action Scheduler worker and a
	 * browser-driven step can both call this safely without double-processing.
	 * If another driver holds the lock, returns the current progress snapshot
	 * without doing work.
	 *
	 * @return array Progress descriptor (same shape as process_batch()).
	 */
	public static function process_batch_guarded() {
		if ( ! self::acquire_lock() ) {
			$run = Store::get_active_run();

			return $run ? self::get_progress_snapshot( $run ) : array( 'status' => 'idle' );
		}

		try {
			return self::process_batch();
		} finally {
			self::release_lock();
		}
	}

	/**
	 * Best-effort processing lock. The consequence of the rare race (two drivers
	 * both acquiring) is only a re-processed batch, which is harmless because
	 * reference writes are idempotent upserts.
	 *
	 * @return bool True if the lock was acquired.
	 */
	private static function acquire_lock() {
		$now  = time();
		$lock = get_option( self::LOCK_OPTION );

		if ( is_array( $lock ) && isset( $lock['until'] ) && (int) $lock['until'] > $now ) {
			return false;
		}

		update_option( self::LOCK_OPTION, array( 'until' => $now + self::LOCK_TTL ), false );

		return true;
	}

	/**
	 * Release the processing lock.
	 *
	 * @return void
	 */
	private static function release_lock() {
		delete_option( self::LOCK_OPTION );
	}

	/**
	 * Advance the sweep phase by one batch.
	 *
	 * @param array $state Current state.
	 *
	 * @return array
	 */
	private static function process_sweep( array $state ) {
		$sources = self::get_sources();
		$index   = (int) $state['source_index'];

		if ( ! isset( $sources[ $index ] ) ) {
			$state['phase']  = 'finalize';
			$state['cursor'] = 0;
			update_option( self::STATE_OPTION, $state, false );

			return self::progress( $state, __( 'Comparing references', 'infinite-uploads' ) );
		}

		$source = $sources[ $index ];
		$source->set_run_id( (int) $state['run_id'] );

		$result = $source->scan_batch( (int) $state['cursor'], self::SWEEP_BATCH );

		// Flush the batch's references in one bulk insert (sources buffer them).
		$source->flush_references();

		if ( ! empty( $result['done'] ) ) {
			// Source exhausted; move to the next one.
			$state['source_index'] = $index + 1;
			$state['cursor']       = 0;
		} else {
			$state['cursor'] = (int) $result['last_id'];
		}

		update_option( self::STATE_OPTION, $state, false );

		return self::progress( $state, $source->get_label() );
	}

	/**
	 * Advance the finalize phase by one batch.
	 *
	 * @param array $state Current state.
	 *
	 * @return array
	 */
	private static function process_finalize( array $state ) {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_date_gmt, post_mime_type, post_parent FROM {$wpdb->posts}
				WHERE post_type = 'attachment' AND ID > %d ORDER BY ID ASC LIMIT %d",
				(int) $state['cursor'],
				self::FINALIZE_BATCH
			)
		);

		$processed = 0;
		if ( $rows ) {
			$ids = array_map( 'intval', wp_list_pluck( $rows, 'ID' ) );

			// Prime post + meta caches for the whole batch in two queries so the
			// per-attachment get_post_meta / wp_get_attachment_* calls below hit
			// the object cache instead of issuing one query each.
			_prime_post_caches( $ids, false, true );

			// One GROUP BY for all reference counts in the batch (no per-row COUNT).
			$counts = Store::count_references_for_ids( $ids );

			$items = array();
			foreach ( $rows as $row ) {
				$ref_count = isset( $counts[ (int) $row->ID ] ) ? (int) $counts[ (int) $row->ID ] : 0;
				$values    = self::compute_item_values( (int) $row->ID, (int) $state['run_id'], $ref_count, $row );
				if ( null !== $values ) {
					$items[] = $values;
				}
				$state['cursor'] = (int) $row->ID;
				$processed++;
			}

			// One bulk upsert for the whole batch instead of one query per item.
			Store::upsert_items_bulk( $items );
		}

		$state['scanned'] = (int) $state['scanned'] + $processed;
		Store::update_run( (int) $state['run_id'], array( 'scanned_items' => (int) $state['scanned'] ) );

		if ( $processed < self::FINALIZE_BATCH ) {
			$state['phase'] = 'done';
		}

		update_option( self::STATE_OPTION, $state, false );

		if ( 'done' === $state['phase'] ) {
			return self::finish( $state );
		}

		return self::progress( $state, __( 'Saving results', 'infinite-uploads' ) );
	}

	/**
	 * Compute and store the verdict for one attachment. Shared by the full-scan
	 * finalize phase and single-attachment rescans.
	 *
	 * @param int         $attachment_id Attachment id.
	 * @param int         $run_id Run id stamped on the verdict row.
	 * @param int|null    $ref_count Known reference count, or null to look it up.
	 * @param object|null $row Optional attachment row (ID, post_date_gmt, post_mime_type, post_parent).
	 *
	 * @return void
	 */
	public static function recompute_item( $attachment_id, $run_id, $ref_count = null, $row = null ) {
		$values = self::compute_item_values( $attachment_id, $run_id, $ref_count, $row );
		if ( null === $values ) {
			return;
		}

		$id = (int) $values['attachment_id'];
		unset( $values['attachment_id'] );
		Store::upsert_item( $id, $values );
	}

	/**
	 * Compute the verdict value-array for one attachment without writing it, so
	 * the finalize phase can collect a batch and persist them in one bulk upsert.
	 * Returns null for an invalid/non-attachment id.
	 *
	 * @param int         $attachment_id Attachment id.
	 * @param int         $run_id Run id stamped on the verdict row.
	 * @param int|null    $ref_count Known reference count, or null to look it up.
	 * @param object|null $row Optional attachment row.
	 *
	 * @return array|null Upsert value-array (incl. attachment_id), or null.
	 */
	private static function compute_item_values( $attachment_id, $run_id, $ref_count, $row ) {
		$attachment_id = (int) $attachment_id;
		if ( ! $attachment_id ) {
			return null;
		}

		if ( null === $row ) {
			$row = get_post( $attachment_id );
		}
		if ( ! $row || 'attachment' !== get_post_type( $attachment_id ) ) {
			return null;
		}

		if ( null === $ref_count ) {
			$ref_count = Store::count_references( $attachment_id );
		}
		$ref_count = (int) $ref_count;

		$file_path = (string) get_post_meta( $attachment_id, '_wp_attached_file', true );
		$file_url  = wp_get_attachment_url( $attachment_id );
		$meta      = wp_get_attachment_metadata( $attachment_id );
		$file_size = ( is_array( $meta ) && isset( $meta['filesize'] ) ) ? (int) $meta['filesize'] : 0;

		if ( $ref_count > 0 ) {
			$status     = Store::STATUS_REFERENCED;
			$confidence = Matcher::CONFIDENCE_HIGH;
		} else {
			$status     = self::no_reference_status( $row );
			$confidence = Matcher::CONFIDENCE_LOW;
		}

		// Local/cloud file status and the "broken reference" verdict are
		// intentionally not computed: the offload/local detection proved
		// unreliable, so those columns were removed from the UI. The DB
		// columns remain (empty) so it can be re-enabled later if needed.
		return array(
			'attachment_id'   => $attachment_id,
			'scan_run_id'     => (int) $run_id,
			'file_path'       => $file_path,
			'file_url'        => $file_url ? $file_url : '',
			'file_type'       => (string) $row->post_mime_type,
			'file_size'       => $file_size,
			'usage_status'    => $status,
			'confidence'      => $confidence,
			'reference_count' => $ref_count,
		);
	}

	/**
	 * Re-scan a single attachment on demand (no full content pass).
	 *
	 * Uses targeted LIKE queries on the attachment's relative path / id across
	 * the high-value sources (post content, post meta, options), confirms each
	 * candidate with the Matcher, rewrites that attachment's references, and
	 * recomputes its verdict. Term/user meta are only covered by full scans.
	 *
	 * @param int $attachment_id Attachment id.
	 *
	 * @return bool True on success.
	 */
	public static function rescan_attachment( $attachment_id ) {
		$attachment_id = (int) $attachment_id;
		if ( ! Schema::tables_exist() || ! $attachment_id || 'attachment' !== get_post_type( $attachment_id ) ) {
			return false;
		}

		Store::delete_references_for_attachment( $attachment_id );

		// A single rescan resolves only a handful of paths, so skip the bulk path
		// map (building it would scan the whole library for nothing). Restore the
		// flag in a finally so a thrown scan can't leak it into later work.
		Matcher::set_path_map_enabled( false );
		try {
			self::find_references_for_attachment( $attachment_id );
		} finally {
			Matcher::set_path_map_enabled( true );
		}

		self::recompute_item( $attachment_id, 0 );

		return true;
	}

	/**
	 * Targeted reference discovery for one attachment across content/meta/options.
	 *
	 * @param int $attachment_id Attachment id.
	 *
	 * @return void
	 */
	private static function find_references_for_attachment( $attachment_id ) {
		global $wpdb;

		$relpath = (string) get_post_meta( $attachment_id, '_wp_attached_file', true );
		$stem    = $relpath ? preg_replace( '/\.[A-Za-z0-9]{2,5}$/', '', $relpath ) : '';

		$record = function ( $source_type, $source_id, $label, $reference_type, $matched, $url = '' ) use ( $attachment_id ) {
			Store::add_reference(
				array(
					'attachment_id'  => $attachment_id,
					'scan_run_id'    => 0,
					'source_type'    => $source_type,
					'source_id'      => (int) $source_id,
					'source_label'   => $label,
					'reference_type' => $reference_type,
					'confidence'     => Matcher::CONFIDENCE_HIGH,
					'matched_value'  => $matched,
					'source_url'     => $url,
				)
			);
		};

		// 1) Post content: URL stem OR wp-image-{id} OR gallery id.
		$content_like = array();
		$content_args = array();
		if ( $stem ) {
			$content_like[] = 'post_content LIKE %s';
			$content_args[] = '%' . $wpdb->esc_like( $stem ) . '%';
		}
		$content_like[] = 'post_content LIKE %s';
		$content_args[] = '%wp-image-' . $attachment_id . '%';
		$content_args[] = 200;

		$sql  = "SELECT ID, post_title, post_type, post_content FROM {$wpdb->posts}
			WHERE post_type NOT IN ('attachment','revision')
			AND post_status IN ('publish','future','draft','pending','private')
			AND ( " . implode( ' OR ', $content_like ) . ' ) ORDER BY ID ASC LIMIT %d';
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $content_args ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- placeholders built above.

		foreach ( (array) $rows as $row ) {
			$matched = self::confirm_in_content( $attachment_id, (string) $row->post_content );
			if ( $matched ) {
				$label = '' !== $row->post_title ? $row->post_title : sprintf( '#%d', $row->ID );
				$url   = get_permalink( $row->ID );
				$record( $row->post_type, $row->ID, $label, $matched['type'], $matched['value'], $url ? $url : '' );
			}
		}

		// 2) Post meta: featured image, woo gallery, generic URL stem.
		$meta_like = $stem ? '%' . $wpdb->esc_like( $stem ) . '%' : '';
		$meta_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.post_id, pm.meta_key, pm.meta_value, p.post_title, p.post_type
				FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE p.post_type != 'attachment' AND p.post_status NOT IN ('trash','auto-draft')
				AND ( ( pm.meta_key = '_thumbnail_id' AND pm.meta_value = %s )
					OR ( pm.meta_key = '_product_image_gallery' AND ( pm.meta_value = %s OR pm.meta_value LIKE %s OR pm.meta_value LIKE %s OR pm.meta_value LIKE %s ) )
					OR ( %s != '' AND pm.meta_value LIKE %s ) )
				ORDER BY pm.meta_id ASC LIMIT 200",
				(string) $attachment_id,
				(string) $attachment_id,
				$attachment_id . ',%',
				'%,' . $attachment_id . ',%',
				'%,' . $attachment_id,
				$meta_like,
				$meta_like
			)
		);

		foreach ( (array) $meta_rows as $row ) {
			$label = '' !== $row->post_title ? $row->post_title : sprintf( '#%d', $row->post_id );
			$url   = get_permalink( $row->post_id );
			$url   = $url ? $url : '';

			if ( '_thumbnail_id' === $row->meta_key ) {
				$record( $row->post_type, $row->post_id, $label, 'featured_image', '#' . $attachment_id, $url );
				continue;
			}
			if ( '_product_image_gallery' === $row->meta_key ) {
				$record( $row->post_type, $row->post_id, $label, 'woocommerce_gallery', '#' . $attachment_id, $url );
				continue;
			}
			// Generic: confirm the stem really resolves to this attachment.
			$matches = Matcher::find_url_references( (string) $row->meta_value );
			if ( isset( $matches[ $attachment_id ] ) ) {
				$record( $row->post_type, $row->post_id, $label, PostMetaSource::reference_type_for_key( $row->meta_key ), $matches[ $attachment_id ], $url );
			}
		}

		// 3) Options: generic URL stem.
		if ( $stem ) {
			$opt_rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT option_name, option_value FROM {$wpdb->options}
					WHERE option_name NOT LIKE '\_transient\_%' AND option_name NOT LIKE '\_site\_transient\_%'
					AND option_value LIKE %s ORDER BY option_id ASC LIMIT 200",
					'%' . $wpdb->esc_like( $stem ) . '%'
				)
			);
			foreach ( (array) $opt_rows as $row ) {
				$matches = Matcher::find_url_references( (string) $row->option_value );
				if ( isset( $matches[ $attachment_id ] ) ) {
					$record( 'option', 0, $row->option_name, 'option', $matches[ $attachment_id ] );
				}
			}
		}
	}

	/**
	 * Confirm an attachment is referenced in a content blob and describe how.
	 *
	 * @param int    $attachment_id Attachment id.
	 * @param string $content Content blob.
	 *
	 * @return array{type:string,value:string}|null
	 */
	private static function confirm_in_content( $attachment_id, $content ) {
		$ids = Matcher::find_id_references( $content );
		if ( isset( $ids[ $attachment_id ] ) ) {
			return array(
				'type'  => $ids[ $attachment_id ]['reference_type'],
				'value' => $ids[ $attachment_id ]['matched_value'],
			);
		}

		$urls = Matcher::find_url_references( $content );
		if ( isset( $urls[ $attachment_id ] ) ) {
			return array(
				'type'  => 'content_url',
				'value' => $urls[ $attachment_id ],
			);
		}

		return null;
	}

	/**
	 * Decide the status for an attachment with no references found.
	 *
	 * @param object $row Attachment row.
	 *
	 * @return string
	 */
	private static function no_reference_status( $row ) {
		// Documents/archives (PDF, ZIP, office docs, etc.) are commonly linked
		// from outside WordPress, so we can't safely call them unused.
		$mime = (string) $row->post_mime_type;
		if ( 0 === strpos( $mime, 'application/' ) || 0 === strpos( $mime, 'text/' ) ) {
			return Store::STATUS_UNKNOWN;
		}

		// Attached to non-public content: it may be used privately.
		if ( $row->post_parent ) {
			$parent_status = get_post_status( (int) $row->post_parent );
			if ( $parent_status && ! in_array( $parent_status, array( 'publish', 'inherit' ), true ) ) {
				return Store::STATUS_UNKNOWN;
			}
		}

		// Everything else with no references found: a real cleanup candidate.
		// (Recently-uploaded images are intentionally included — they are simply
		// not used yet; the "review before deleting" wording covers new files.)
		return Store::STATUS_POSSIBLY_UNUSED;
	}

	/**
	 * Complete the run: write final counts and clear state.
	 *
	 * @param array $state Current state.
	 *
	 * @return array
	 */
	private static function finish( array $state ) {
		$summary = Store::get_summary();

		Store::update_run(
			(int) $state['run_id'],
			array(
				'status'                => Store::RUN_COMPLETED,
				'scanned_items'         => (int) $summary['total'],
				'referenced_count'      => (int) $summary['referenced'],
				'possibly_unused_count' => (int) $summary['possibly_unused'],
				'unknown_count'         => (int) $summary['unknown'],
				'broken_count'          => (int) $summary['broken'],
				'completed_at'          => current_time( 'mysql', true ),
			)
		);

		delete_option( self::STATE_OPTION );

		return array(
			'status'  => 'complete',
			'phase'   => 'done',
			'message' => __( 'Scan complete', 'infinite-uploads' ),
			'total'   => (int) $summary['total'],
			'scanned' => (int) $summary['total'],
			'percent' => 100,
			'run_id'  => (int) $state['run_id'],
		);
	}

	/**
	 * Build a running-progress descriptor.
	 *
	 * @param array  $state Current state.
	 * @param string $message Human status line.
	 *
	 * @return array
	 */
	private static function progress( array $state, $message ) {
		return array(
			'status'       => 'running',
			'phase'        => $state['phase'],
			'message'      => $message,
			'total'        => (int) $state['total'],
			'scanned'      => (int) $state['scanned'],
			'cursor'       => (int) $state['cursor'],
			'source_index' => (int) $state['source_index'],
			'percent'      => self::percent_for_state( $state ),
			'run_id'       => (int) $state['run_id'],
		);
	}

	/**
	 * Estimate overall percent: sweep is the first 40%, finalize the last 60%.
	 *
	 * @param array $state Current state.
	 *
	 * @return int 1-99.
	 */
	private static function percent_for_state( array $state ) {
		$total   = max( 1, (int) $state['total'] );
		$scanned = (int) $state['scanned'];

		if ( 'finalize' === $state['phase'] || 'done' === $state['phase'] ) {
			$percent = 40 + (int) round( ( $scanned / $total ) * 60 );
		} else {
			$source_count = max( 1, count( self::get_sources() ) );
			$percent      = (int) round( ( (int) $state['source_index'] / $source_count ) * 40 );
		}

		return min( 99, max( 1, $percent ) );
	}

	/**
	 * Read-only progress descriptor for an active run (no batch processing).
	 * Used by the status poll, which must not advance the scan.
	 *
	 * @param object $run Run row.
	 *
	 * @return array
	 */
	public static function get_progress_snapshot( $run ) {
		if ( Store::RUN_COMPLETED === $run->status ) {
			return array(
				'status'  => 'complete',
				'percent' => 100,
				'message' => __( 'Scan complete', 'infinite-uploads' ),
			);
		}

		$state = get_option( self::STATE_OPTION );
		if ( ! is_array( $state ) || empty( $state['run_id'] ) ) {
			return array(
				'status'  => Store::RUN_PAUSED === $run->status ? 'paused' : 'running',
				'percent' => 0,
				'message' => Store::RUN_PAUSED === $run->status ? __( 'Paused', 'infinite-uploads' ) : '',
			);
		}

		$message = ( 'finalize' === $state['phase'] )
			? __( 'Saving results', 'infinite-uploads' )
			: __( 'Scanning…', 'infinite-uploads' );

		return array(
			'status'       => Store::RUN_PAUSED === $run->status ? 'paused' : 'running',
			'phase'        => $state['phase'],
			'message'      => Store::RUN_PAUSED === $run->status ? __( 'Paused', 'infinite-uploads' ) : $message,
			'total'        => (int) $state['total'],
			'scanned'      => (int) $state['scanned'],
			'cursor'       => (int) $state['cursor'],
			'source_index' => (int) $state['source_index'],
			'percent'      => self::percent_for_state( $state ),
			'run_id'       => (int) $state['run_id'],
		);
	}

	/**
	 * Cancel the active run and clear state.
	 *
	 * @return void
	 */
	public static function cancel() {
		$state = get_option( self::STATE_OPTION );
		if ( is_array( $state ) && ! empty( $state['run_id'] ) ) {
			Store::update_run( (int) $state['run_id'], array( 'status' => Store::RUN_CANCELLED ) );
		}
		delete_option( self::STATE_OPTION );
	}
}
