<?php

namespace ClikIT\InfiniteUploads;

/**
 * Breadth-first scanner over the local uploads directory. Iterates within
 * a per-request time budget and pauses cleanly at a directory boundary
 * when the budget is exhausted, stashing the remaining paths in
 * $paths_left so a subsequent request can resume without re-walking.
 * Flushes discovered files into `{prefix}infinite_uploads_files` in
 * batches of $insert_rows.
 *
 * Two modes (see MODE_SCAN / MODE_RECONCILE):
 *   - Scan wipes the sync table on start and resets the iup_files_scanned
 *     progress bookkeeping. Owned by the manual "Scan" button flow.
 *   - Reconcile leaves both intact — used by the periodic self-heal cron
 *     that picks up files written outside the plugin's attachment hooks
 *     (page builders, direct disk writes) without destroying sync state.
 */
class InfiniteUploadsFilelist {

	public $is_done = false;
	public $paths_left = [];
	public $file_count = 0;
	public $file_list = [];
	public $exclusions = [
		'/et-cache/',
		'/et_temp/',
		'/imports/',
		'/cache/',
		'/wp-defender/',
		'.DS_Store',
		'/wc-logs/',
		'/php-errors/',
		'/error_log',
		'debug.log',
		'.log',
		'/logs/',
		'/tmp/',
		'/temp/',
	];
	protected $root_path;
	protected $timeout;
	protected $start_time;
	protected $instance;
	protected $insert_rows = 500;

	/**
	 * Mode: full scan — wipes the sync table on start, resets iup_files_scanned.
	 * Used by the manual "Scan" button and the initial-scan flow.
	 */
	const MODE_SCAN = 'scan';

	/**
	 * Mode: reconcile — INSERT-only pass over local uploads that leaves the
	 * sync table (and progress bookkeeping) intact. Used by the periodic
	 * cron that heals stragglers written outside our attachment hooks
	 * (page builders, direct disk writes) without destroying sync state.
	 * The existing `ON DUPLICATE KEY UPDATE` in flush_to_db() makes the
	 * pass idempotent.
	 */
	const MODE_RECONCILE = 'reconcile';

	protected $mode;

	/**
	 * InfiniteUploadsFilelist constructor.
	 *
	 * @param  string  $root_path   The full path of the directory to iterate.
	 * @param  float   $timeout     Timeout in seconds.
	 * @param  array   $paths_left  Provide as returned if continuing the filelist after a timeout.
	 * @param  string  $mode        MODE_SCAN (default) or MODE_RECONCILE.
	 */
	public function __construct( $root_path, $timeout = 25.0, $paths_left = [], $mode = self::MODE_SCAN ) {
		$this->root_path  = rtrim( $root_path, '/' ); //expected no trailing slash.
		$this->timeout    = $timeout;
		$this->paths_left = $paths_left;
		$this->instance   = InfiniteUploads::get_instance();
		$this->mode       = ( self::MODE_RECONCILE === $mode ) ? self::MODE_RECONCILE : self::MODE_SCAN;
	}

	/**
	 * Runs over the site's files.
	 */
	public function start() {
		global $wpdb;
		$this->start_time = microtime( true );
		$this->file_count = 0;
		$this->file_list  = [];

		// If just starting reset the local DB list storage — SCAN mode only.
		// Reconcile mode must never truncate: it runs periodically as a
		// self-healing cron and would otherwise destroy sync progress.
		if ( empty( $this->paths_left ) && self::MODE_SCAN === $this->mode ) {
			//TRUNCATE is fastest, try it first
			$result = $wpdb->query( "TRUNCATE TABLE {$wpdb->base_prefix}infinite_uploads_files" );
			//Sometimes hosts don't give the DB user TRUNCATE permissions, so DELETE all if we have to.
			if ( false === $result ) {
				$wpdb->query( "DELETE FROM {$wpdb->base_prefix}infinite_uploads_files WHERE 1" );
			}

			update_site_option( 'iup_files_scanned', [
				'files_started'     => time(),
				'files_finished'    => false,
				'compare_started'   => false,
				'compare_finished'  => false,
				'sync_started'      => false,
				'sync_finished'     => false,
				'download_started'  => false,
				'download_finished' => false,
			] );
		}

		$this->get_files();

		$this->flush_to_db();

		if ( empty( $this->paths_left ) ) {
			// So we are done. Say so.
			$this->is_done = true;

			// Only touch iup_files_scanned in SCAN mode — reconcile is a
			// background heal, not a user-visible scan pass.
			if ( self::MODE_SCAN === $this->mode ) {
				$progress                   = get_site_option( 'iup_files_scanned' );
				$progress['files_finished'] = time();
				update_site_option( 'iup_files_scanned', $progress );
			}
		}
	}

	/**
	 * Runs a breadth-first iteration on all files and gathers the relevant info for each one.
	 *
	 * @todo test what happens if some files have no read permissions.
	 */
	protected function get_files() {
		$paths = ( empty( $this->paths_left ) ) ? [ $this->root_path ] : $this->paths_left;

		while ( ! empty( $paths ) ) {
			$path = array_pop( $paths );

			if ( preg_match( '/\.\.([\/\\\\]|$)/', $path ) ) {
				continue;
			}

			if ( 0 !== strpos( $path, $this->root_path ) ) {
				// Resume paths_left entries come back as relative — rebuild absolute.
				$path = rtrim( $this->root_path, '/' ) . $path;
			}

			if ( $this->is_excluded( $path ) ) {
				continue;
			}

			$contents = defined( 'GLOB_BRACE' )
				? glob( trailingslashit( $path ) . '{,.}[!.,!..]*', GLOB_BRACE )
				: glob( trailingslashit( $path ) . '[!.,!..]*' );

			foreach ( $contents as $item ) {
				$file = [];
				if ( is_link( $item ) || $this->is_excluded( $item ) ) {
					continue;
				} elseif ( is_file( $item ) ) {
					if ( is_readable( $item ) ) {
						$file = $this->get_file_info( $item );
					} else {
						$file = null;
						error_log( sprintf( '[INFINITE_UPLOADS Filelist Error] %s could not be read for syncing', $item ) );
					}

					$file['name'] = $this->relative_path( $item );

					$this->add_file( $file );
				} elseif ( is_dir( $item ) ) {
					if ( ! in_array( $item, $paths, true ) ) {
						$paths[] = $this->relative_path( $item );
					}
				}
			}
			$this->paths_left = $paths;

			if ( $this->has_exceeded_timelimit() ) {
				break;
			}
		}

		$this->is_done = false;
	}

	/**
	 * Register the files listed in $paths_left as sync candidates.
	 * Used by the un-exclude flow (see
	 * process_added_removed_excluded_files) where individual FILE paths
	 * — not directory paths — are handed in for re-queuing. The glob()
	 * branch below treats every path as a directory pattern, so files
	 * are handled up-front before reaching it, otherwise glob() on a
	 * file returns [] and the entry would be silently dropped.
	 */
	public function add_files_to_sync() {
		$paths = ( empty( $this->paths_left ) ) ? [ $this->root_path ] : $this->paths_left;

		while ( ! empty( $paths ) ) {
			$path = array_pop( $paths );

			if ( preg_match( '/\.\.([\/\\\\]|$)/', $path ) ) {
				continue;
			}

			if ( 0 !== strpos( $path, $this->root_path ) ) {
				$path = rtrim( $this->root_path, '/' ) . $path;
			}

			if ( is_file( $path ) ) {
				if ( ! is_link( $path ) ) {
					if ( is_readable( $path ) ) {
						$file         = $this->get_file_info( $path );
						$file['name'] = $this->relative_path( $path );
						$this->add_file( $file );
					} else {
						error_log( sprintf( '[INFINITE_UPLOADS Filelist Error] %s could not be read for syncing', $path ) );
					}
				}
				$this->paths_left = $paths;
				continue;
			}

			$contents = defined( 'GLOB_BRACE' )
				? glob( trailingslashit( $path ) . '{,.}[!.,!..]*', GLOB_BRACE )
				: glob( trailingslashit( $path ) . '[!.,!..]*' );

			foreach ( $contents as $item ) {
				$file = [];
				if ( is_link( $item ) ) {
					continue;
				} elseif ( is_file( $item ) ) {
					if ( is_readable( $item ) ) {
						$file = $this->get_file_info( $item );
					} else {
						$file = null;
						error_log( sprintf( '[INFINITE_UPLOADS Filelist Error] %s could not be read for syncing', $item ) );
					}

					$file['name'] = $this->relative_path( $item );

					$this->add_file( $file );
				}
				// is_dir branch intentionally omitted here — this method
				// operates on individual files enumerated up-front, so
				// re-descending directories would double-queue.
			}

			$this->paths_left = $paths;
		}

		$this->flush_to_db();
	}

	/**
	 * Checks path against excluded pattern.
	 *
	 * @return bool
	 *
	 */
	protected function is_excluded( $path ) {
		// Carve-out: Beaver Builder writes cropped image files into bb-plugin/cache/
		// next to its per-layout .css/.js. The folder is excluded by '/cache/' and
		// '/bb-plugin/' rules below, but the cropped images are what BB actually
		// serves to visitors — we want them offloaded + CDN-delivered. Layout
		// .css/.js still match the path-based rules below and stay local.
		if ( InfiniteUploadsHelper::is_offloadable_bb_cache_image( $path ) ) {
			return false;
		}

		/**
		 * Filters the built in list of file/directory exclusions that should not be synced to the Infinite Uploads cloud. Be specific it's a simple strpos() search for the strings.
		 *
		 * @param  {array}  $exclusions  A list of file or directory names in the format of `/do-not-sync-this-dir/` or `somefilename.ext`.
		 *
		 * @return {array} A list of file or directory names in the format of `/do-not-sync-this-dir/` or `somefilename.ext`.
		 * @since  1.0
		 * @hook   infinite_uploads_sync_exclusions
		 *
		 */
		$exclusions = apply_filters( 'infinite_uploads_sync_exclusions', $this->exclusions );
		foreach ( $exclusions as $string ) {
			if ( false !== strpos( $path, $string ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks file health and returns as many info as it can.
	 *
	 * @param  string  $item  The file to be investigated.
	 *
	 * @return mixed File info or false for failure.
	 */
	protected function get_file_info( $item ) {
		$file          = [];
		$file['mtime'] = filemtime( $item );
		$file['size']  = filesize( $item );
		$file['type']  = $this->instance->get_file_type( $item );

		if ( empty( $file['mtime'] ) && empty( $file['size'] ) ) {
			return false;
		}

		return $file;
	}

	/**
	 * Returns rel path of file/dir, relative to site root.
	 *
	 * @param  string  $item  File's absolute path.
	 *
	 * @return string
	 */
	protected function relative_path( $item ) {
		$pos = strpos( $item, $this->root_path );
		if ( 0 === $pos ) {
			return substr_replace( $item, '', $pos, strlen( $this->root_path ) );
		}

		return $item;
	}

	/**
	 * Add file details to internal storage and the db.
	 */
	protected function add_file( $file ) {
		$this->file_list[] = $file;
		$this->file_count ++;
		if ( count( $this->file_list ) >= $this->insert_rows ) {
			$this->flush_to_db();
		}
	}

	/**
	 * Write the queued file list to DB storage.
	 */
	protected function flush_to_db() {
		global $wpdb;

		if ( count( $this->file_list ) ) {
			$values = [];
			foreach ( $this->file_list as $file ) {
				$values[] = $wpdb->prepare( "(%s,%d,%d,%s,0)", $file['name'], $file['size'], $file['mtime'], $file['type'] );
			}

			$query = "INSERT INTO {$wpdb->base_prefix}infinite_uploads_files (file, size, modified, type, errors) VALUES ";
			$query .= implode( ",\n", $values );
			$query .= " ON DUPLICATE KEY UPDATE size = VALUES(size), modified = VALUES(modified), type = VALUES(type), errors = 0";
			if ( $wpdb->query( $query ) ) {
				$this->file_list = [];
			}
		}
	}

	/**
	 * Checks if current iteration has exceeded the given time limit.
	 *
	 * @return bool True if we have exceeded the time limit, false if we haven't.
	 */
	protected function has_exceeded_timelimit() {
		$current_time = microtime( true );
		$time_diff    = number_format( $current_time - $this->start_time, 2 );

		$has_exceeded_timelimit = ! empty( $this->timeout ) && ( $time_diff > $this->timeout );

		return $has_exceeded_timelimit;
	}
}
