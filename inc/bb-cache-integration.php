<?php
/**
 * Beaver Builder cache integration — extracted from inc/InfiniteUploads.php
 * for clarity and so the backfill + photo-cropped + cron-push handlers can be
 * unit-tested in isolation.
 *
 * Three pieces live here:
 *   1. infinite_uploads_bb_photo_cropped  — fl_builder_photo_cropped action.
 *      Records each freshly-cropped image in infinite_uploads_files with
 *      synced=0, invalidates the rewriter cache, and schedules the async
 *      cloud-push cron event.
 *   2. infinite_uploads_bb_cache_push     — iu_bb_cache_push cron handler.
 *      Picks up synced=0 BB cache rows in batches of 100, uploads via the
 *      existing Transfer pipeline, marks synced=1 on success, re-schedules
 *      itself while there's pending work.
 *   3. infinite_uploads_bb_carveout_backfill — iu_bb_carveout_backfill cron
 *      handler. One-time walk of /bb-plugin/cache/ on plugin upgrade. Bounded
 *      per-run by file count (500) and elapsed time (20s); resumes on next
 *      tick if cut off; flags completion only when iteration walks past the
 *      last entry. Uses the DB itself as the cursor (skips rows that are
 *      already known) so it stays idempotent across runs.
 *
 * Why anchored LIKE patterns (no leading %): paths in infinite_uploads_files
 * always start with the relative-to-uploads leading slash, so
 * '/bb-plugin/cache/...' is a literal prefix match. Anchored LIKE lets MySQL
 * use the PRIMARY KEY range scan on the `file` column instead of doing a
 * full table scan.
 *
 * @package ClikIT\InfiniteUploads
 */

namespace ClikIT\InfiniteUploads;

if ( ! function_exists( __NAMESPACE__ . '\\infinite_uploads_bb_photo_cropped' ) ) {

	/**
	 * BB writes cropped images directly to disk (bypassing the stream
	 * wrapper) and doesn't go through wp_handle_upload. This hook fires
	 * immediately after FLBuilderPhoto::crop() saves the file, giving us a
	 * reliable point to record it and queue an async cloud push.
	 *
	 * @param  array  $cropped_path  Array with 'path' and 'url' keys.
	 * @param  mixed  $editor        Unused (WP_Image_Editor instance).
	 */
	function infinite_uploads_bb_photo_cropped( $cropped_path, $editor = null ) {
		if ( ! \is_array( $cropped_path ) || empty( $cropped_path['path'] ) ) {
			return;
		}
		$file_path = $cropped_path['path'];
		if ( ! \file_exists( $file_path ) ) {
			return;
		}
		if ( ! InfiniteUploadsHelper::is_offloadable_bb_cache_image( $file_path ) ) {
			return;
		}
		if ( ! \infinite_uploads_enabled() ) {
			return;
		}

		// Convert absolute path → filelist-style relative path (leading slash).
		$root_dirs = InfiniteUploadsHelper::get_original_upload_dir_root();
		$base_dir  = \untrailingslashit( $root_dirs['basedir'] );
		if ( \strpos( $file_path, $base_dir ) !== 0 ) {
			return;
		}
		$rel = \substr( $file_path, \strlen( $base_dir ) );

		global $wpdb;
		$size     = (int) \filesize( $file_path );
		$modified = (int) \filemtime( $file_path );
		$type     = \wp_check_filetype( $file_path );
		$mime     = ! empty( $type['type'] ) ? $type['type'] : 'application/octet-stream';

		// Idempotent insert — ON DUPLICATE KEY UPDATE handles the regen case
		// where BB overwrites the same crop with new contents. Reset synced=0
		// so the next cron pass re-uploads, and clear error counters so it
		// isn't auto-skipped.
		$wpdb->query( $wpdb->prepare(
			"INSERT INTO {$wpdb->base_prefix}infinite_uploads_files
			 (file, size, modified, type, errors, synced, deleted, transferred)
			 VALUES (%s, %d, %d, %s, 0, 0, 0, 0)
			 ON DUPLICATE KEY UPDATE size = VALUES(size), modified = VALUES(modified), synced = 0, deleted = 0, transferred = 0, errors = 0",
			$rel,
			$size,
			$modified,
			$mime
		) );

		// Invalidate the rewriter's synced-paths cache so it reloads on next
		// URL match (otherwise a stale view could mark a freshly-overwritten
		// crop as synced=1).
		\wp_cache_delete( 'iu_bb_cache_synced_paths', 'infinite_uploads' );

		// Schedule async push ~30s out, idempotent across multiple BB photo
		// writes in one request.
		if ( ! \wp_next_scheduled( 'iu_bb_cache_push' ) ) {
			\wp_schedule_single_event( \time() + 30, 'iu_bb_cache_push' );
		}
	}
}

if ( ! function_exists( __NAMESPACE__ . '\\infinite_uploads_bb_cache_push' ) ) {

	/**
	 * Cron handler: push queued BB cache images (synced=0) to the cloud.
	 *
	 * Runs ~30s after BB writes a new crop. Uses the existing Transfer
	 * pipeline scoped to BB cache files only, updates synced=1 on success,
	 * and invalidates the rewriter cache so visitor requests after this
	 * point get CDN URLs.
	 */
	function infinite_uploads_bb_cache_push() {
		global $wpdb;

		if ( ! \infinite_uploads_enabled() ) {
			return;
		}

		$instance = InfiniteUploads::get_instance();
		if ( empty( $instance->bucket ) ) {
			return;
		}

		// Anchored LIKE — see file-level docblock.
		$rows = $wpdb->get_results(
			"SELECT file, size FROM `{$wpdb->base_prefix}infinite_uploads_files`
			 WHERE synced = 0 AND errors < 3 AND deleted = 0
			 AND file LIKE '/bb-plugin/cache/%'
			 LIMIT 100"
		);
		if ( ! $rows ) {
			return;
		}

		$path = $instance->get_original_upload_dir_root();
		$s3   = $instance->s3();

		$to_sync_full = [];
		foreach ( $rows as $r ) {
			$local = $path['basedir'] . $r->file;
			if ( \file_exists( $local ) ) {
				$to_sync_full[] = $local;
			} else {
				// File vanished locally before push — mark deleted so it's skipped next round.
				$wpdb->update(
					"{$wpdb->base_prefix}infinite_uploads_files",
					[ 'deleted' => 1 ],
					[ 'file' => $r->file ],
					[ '%d' ],
					[ '%s' ]
				);
			}
		}
		if ( ! $to_sync_full ) {
			return;
		}

		$obj  = new \ArrayObject( $to_sync_full );
		$from = $obj->getIterator();

		$concurrency = \defined( 'INFINITE_UPLOADS_SYNC_CONCURRENCY' ) ? INFINITE_UPLOADS_SYNC_CONCURRENCY : 5;

		$transfer_args = [
			'concurrency' => $concurrency,
			'base_dir'    => $path['basedir'],
			'before'      => function ( \ClikIT\Infinite_Uploads\Aws\Command $command ) use ( $wpdb, $instance ) {
				if ( \in_array( $command->getName(), [ 'PutObject', 'CreateMultipartUpload' ], true ) ) {
					if ( \defined( 'INFINITE_UPLOADS_HTTP_EXPIRES' ) ) {
						$command['Expires'] = INFINITE_UPLOADS_HTTP_EXPIRES;
					}
					if ( \defined( 'INFINITE_UPLOADS_HTTP_CACHE_CONTROL' ) ) {
						$command['CacheControl'] = \is_numeric( INFINITE_UPLOADS_HTTP_CACHE_CONTROL )
							? 'max-age=' . INFINITE_UPLOADS_HTTP_CACHE_CONTROL
							: INFINITE_UPLOADS_HTTP_CACHE_CONTROL;
					}
				}
				if ( \in_array( $command->getName(), [ 'PutObject', 'CompleteMultipartUpload' ], true ) ) {
					$command->getHandlerList()->appendSign(
						\ClikIT\Infinite_Uploads\Aws\Middleware::mapResult( function ( \ClikIT\Infinite_Uploads\Aws\ResultInterface $result ) use ( $wpdb, $instance ) {
							$file = $instance->get_file_from_result( $result );
							$wpdb->query( $wpdb->prepare(
								"UPDATE `{$wpdb->base_prefix}infinite_uploads_files`
								 SET transferred = size, synced = 1, errors = 0, transfer_status = null
								 WHERE file = %s",
								$file
							) );

							return $result;
						} )
					);
				}
			},
		];

		try {
			$manager = new \ClikIT\Infinite_Uploads\Aws\S3\Transfer( $s3, $from, 's3://' . $instance->bucket . '/', $transfer_args );
			$manager->transfer();

			// Make freshly synced=1 rows visible to the rewriter on the next page load.
			\wp_cache_delete( 'iu_bb_cache_synced_paths', 'infinite_uploads' );

			// Re-schedule if there's still pending BB cache work. The query
			// mirrors the SELECT above so we only re-run when there's actual
			// work; errors >= 3 are skipped, breaking any runaway loop on
			// persistent failures.
			$pending = (int) $wpdb->get_var(
				"SELECT COUNT(*) FROM `{$wpdb->base_prefix}infinite_uploads_files`
				 WHERE synced = 0 AND errors < 3 AND deleted = 0
				 AND file LIKE '/bb-plugin/cache/%'"
			);
			if ( $pending > 0 && ! \wp_next_scheduled( 'iu_bb_cache_push' ) ) {
				\wp_schedule_single_event( \time() + 30, 'iu_bb_cache_push' );
			}
		} catch ( \Exception $e ) {
			// Bump the error counter on the rows we attempted so do_sync's retry logic kicks in.
			$rel_paths = \array_map( function ( $r ) {
				return \esc_sql( $r->file );
			}, $rows );
			$wpdb->query(
				"UPDATE `{$wpdb->base_prefix}infinite_uploads_files`
				 SET errors = errors + 1
				 WHERE file IN ('" . \implode( "','", $rel_paths ) . "')"
			);
			\error_log( '[INFINITE_UPLOADS BB Cache Push] ' . $e->getMessage() );
		}
	}
}

if ( ! function_exists( __NAMESPACE__ . '\\infinite_uploads_bb_carveout_backfill' ) ) {

	/**
	 * One-time backfill: walk /wp-content/uploads/bb-plugin/cache/, insert
	 * any existing cropped images into infinite_uploads_files with synced=0,
	 * then trigger the existing push handler.
	 *
	 * Bounded per run by both a file count and an elapsed-time budget so the
	 * cron tick can't exhaust PHP's max_execution_time or memory_limit on
	 * large sites. If iteration doesn't reach the end of the directory in
	 * one run, the handler re-schedules itself; the completion flag is only
	 * set once iteration walks past the last entry. Memory stays flat
	 * regardless of directory size — we use DirectoryIterator (lazy) and
	 * the DB itself as the cursor (load the set of already-known BB cache
	 * rows once at the start, skip them during iteration).
	 */
	function infinite_uploads_bb_carveout_backfill() {
		global $wpdb;

		$cache_dir = WP_CONTENT_DIR . '/uploads/bb-plugin/cache/';
		if ( ! \is_dir( $cache_dir ) ) {
			\update_site_option( 'iup_bb_carveout_backfilled', INFINITE_UPLOADS_VERSION );
			return;
		}

		// Per-run budget. Tuned so a single tick completes comfortably under
		// WP-cron's effective timeout (which is gated by PHP max_execution_time
		// when WP isn't running on real cron). 500 prepared rows ≈ 100KB of memory.
		$max_files       = 500;
		$max_seconds     = 20;
		$start_microtime = \microtime( true );

		// Cursor: use the DB as the source of truth for "already processed."
		// Anchored LIKE so the PRIMARY KEY index handles this even on huge tables.
		$existing = $wpdb->get_col(
			"SELECT file FROM {$wpdb->base_prefix}infinite_uploads_files
			 WHERE file LIKE '/bb-plugin/cache/%'"
		);
		$seen = $existing ? \array_flip( $existing ) : [];

		$root_dirs = InfiniteUploadsHelper::get_original_upload_dir_root();
		$base_dir  = \untrailingslashit( $root_dirs['basedir'] );

		try {
			$iterator = new \DirectoryIterator( $cache_dir );
		} catch ( \Exception $e ) {
			\error_log( '[INFINITE_UPLOADS BB Backfill] Failed to open ' . $cache_dir . ': ' . $e->getMessage() );
			\update_site_option( 'iup_bb_carveout_backfilled', INFINITE_UPLOADS_VERSION );
			return;
		}

		$values    = [];
		$processed = 0;
		$more_work = false;

		foreach ( $iterator as $entry ) {
			if ( $entry->isDot() || ! $entry->isFile() || $entry->isLink() ) {
				continue;
			}
			$abs = $entry->getPathname();
			if ( ! InfiniteUploadsHelper::is_offloadable_bb_cache_image( $abs ) ) {
				continue;
			}
			if ( \strpos( $abs, $base_dir ) !== 0 ) {
				continue;
			}
			$rel = \substr( $abs, \strlen( $base_dir ) );
			if ( isset( $seen[ $rel ] ) ) {
				continue; // already queued by an earlier run or by a live fl_builder_photo_cropped event
			}

			$size     = (int) $entry->getSize();
			$modified = (int) $entry->getMTime();
			$type     = \wp_check_filetype( $abs );
			$mime     = ! empty( $type['type'] ) ? $type['type'] : 'application/octet-stream';
			$values[] = $wpdb->prepare( "(%s, %d, %d, %s, 0, 0, 0, 0)", $rel, $size, $modified, $mime );
			$processed++;

			// Stop after the budget is exhausted. Mark "more_work" so we re-schedule below.
			if ( $processed >= $max_files || ( \microtime( true ) - $start_microtime ) > $max_seconds ) {
				$more_work = true;
				break;
			}
		}

		if ( $values ) {
			// Bulk insert. Up to $max_files rows at a time → single INSERT in normal case.
			// ON DUPLICATE KEY UPDATE makes the operation idempotent if a row was just
			// added by a concurrent fl_builder_photo_cropped event between our SELECT
			// and this INSERT.
			$sql = "INSERT INTO {$wpdb->base_prefix}infinite_uploads_files
					(file, size, modified, type, errors, synced, deleted, transferred)
					VALUES " . \implode( ',', $values ) . "
					ON DUPLICATE KEY UPDATE size = VALUES(size), modified = VALUES(modified), deleted = 0";
			$wpdb->query( $sql );

			// Kick the push handler if we have cloud sync enabled. Otherwise rows just sit at
			// synced=0 until the user enables; either way they're tracked.
			if ( \infinite_uploads_enabled() && ! \wp_next_scheduled( 'iu_bb_cache_push' ) ) {
				\wp_schedule_single_event( \time() + 30, 'iu_bb_cache_push' );
			}
		}

		if ( $more_work ) {
			// Iteration was cut off by the per-run budget. Re-schedule ourselves and
			// do NOT set the completion flag — the next tick picks up where we
			// logically left off (the DB cursor will now include the rows we just
			// inserted, so they'll be skipped automatically).
			if ( ! \wp_next_scheduled( 'iu_bb_carveout_backfill' ) ) {
				\wp_schedule_single_event( \time() + 60, 'iu_bb_carveout_backfill' );
			}
			return;
		}

		// Iteration walked past the last entry within budget → backfill complete.
		\update_site_option( 'iup_bb_carveout_backfilled', INFINITE_UPLOADS_VERSION );
	}
}

\add_action( 'fl_builder_photo_cropped', __NAMESPACE__ . '\\infinite_uploads_bb_photo_cropped', 10, 2 );
\add_action( 'iu_bb_cache_push', __NAMESPACE__ . '\\infinite_uploads_bb_cache_push' );
\add_action( 'iu_bb_carveout_backfill', __NAMESPACE__ . '\\infinite_uploads_bb_carveout_backfill' );
