<?php
/**
 * Data-access layer for the Media Library Usage Scanner.
 *
 * All reads and writes for the three scanner tables go through this class so
 * SQL is centralized, prepared, and easy to audit. No output/escaping happens
 * here; callers are responsible for escaping on display.
 *
 * @package InfiniteUploads
 */

namespace ClikIT\InfiniteUploads\MediaUsage;

/**
 * Class Store
 */
class Store {

	/**
	 * Usage status values (the verdict shown to the user).
	 */
	const STATUS_REFERENCED      = 'referenced';
	const STATUS_POSSIBLY_UNUSED = 'possibly_unused';
	const STATUS_UNKNOWN         = 'unknown';
	const STATUS_BROKEN          = 'broken';

	/**
	 * Scan-run lifecycle statuses.
	 */
	const RUN_PENDING   = 'pending';
	const RUN_RUNNING   = 'running';
	const RUN_PAUSED    = 'paused';
	const RUN_COMPLETED = 'completed';
	const RUN_CANCELLED = 'cancelled';
	const RUN_FAILED    = 'failed';

	/**
	 * Create a new scan-run row and return its id.
	 *
	 * @param int $created_by User id that started the run.
	 * @param int $total_items Number of attachments to scan.
	 *
	 * @return int Insert id, or 0 on failure.
	 */
	public static function create_run( $created_by, $total_items = 0 ) {
		if ( ! Schema::tables_exist() ) {
			return 0;
		}

		global $wpdb;

		$wpdb->insert(
			Schema::runs_table(),
			array(
				'status'      => self::RUN_RUNNING,
				'total_items' => (int) $total_items,
				'created_by'  => (int) $created_by,
				'started_at'  => current_time( 'mysql', true ),
			),
			array( '%s', '%d', '%d', '%s' )
		);

		return (int) $wpdb->insert_id;
	}

	/**
	 * Update mutable fields on a scan run.
	 *
	 * @param int   $run_id Run id.
	 * @param array $fields Column => value pairs (whitelisted internally).
	 *
	 * @return void
	 */
	public static function update_run( $run_id, array $fields ) {
		if ( ! Schema::tables_exist() || ! $run_id ) {
			return;
		}

		$allowed = array(
			'status'                => '%s',
			'total_items'           => '%d',
			'scanned_items'         => '%d',
			'referenced_count'      => '%d',
			'possibly_unused_count' => '%d',
			'unknown_count'         => '%d',
			'broken_count'          => '%d',
			'completed_at'          => '%s',
		);

		$data    = array();
		$formats = array();
		foreach ( $fields as $key => $value ) {
			if ( isset( $allowed[ $key ] ) ) {
				$data[ $key ]= $value;
				$formats[]   = $allowed[ $key ];
			}
		}

		if ( empty( $data ) ) {
			return;
		}

		global $wpdb;
		$wpdb->update( Schema::runs_table(), $data, array( 'id' => (int) $run_id ), $formats, array( '%d' ) );
	}

	/**
	 * Fetch a single run row.
	 *
	 * @param int $run_id Run id.
	 *
	 * @return object|null
	 */
	public static function get_run( $run_id ) {
		if ( ! Schema::tables_exist() || ! $run_id ) {
			return null;
		}

		global $wpdb;

		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . Schema::runs_table() . ' WHERE id = %d', (int) $run_id ) );
	}

	/**
	 * Most recent run (any status), or null.
	 *
	 * @return object|null
	 */
	public static function get_latest_run() {
		if ( ! Schema::tables_exist() ) {
			return null;
		}

		global $wpdb;

		return $wpdb->get_row( 'SELECT * FROM ' . Schema::runs_table() . ' ORDER BY id DESC LIMIT 1' );
	}

	/**
	 * The currently active (running or paused) run, or null.
	 *
	 * @return object|null
	 */
	public static function get_active_run() {
		if ( ! Schema::tables_exist() ) {
			return null;
		}

		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . Schema::runs_table() . ' WHERE status IN ( %s, %s ) ORDER BY id DESC LIMIT 1',
				self::RUN_RUNNING,
				self::RUN_PAUSED
			)
		);
	}

	/**
	 * Insert or update the per-attachment verdict row (keyed on attachment_id).
	 *
	 * @param int   $attachment_id Attachment post id.
	 * @param array $data Verdict fields.
	 *
	 * @return void
	 */
	public static function upsert_item( $attachment_id, array $data ) {
		if ( ! Schema::tables_exist() || ! $attachment_id ) {
			return;
		}

		global $wpdb;

		$now   = current_time( 'mysql', true );
		$table = Schema::items_table();

		// Single round-trip upsert keyed on the UNIQUE attachment_id. The
		// user-controlled `ignored` flag and `created_at` are intentionally
		// omitted from the UPDATE list so they are preserved across rescans.
		// VALUES() is used (rather than the newer alias syntax) for broad
		// MySQL/MariaDB compatibility.
		$sql = $wpdb->prepare(
			"INSERT INTO {$table}
			( attachment_id, scan_run_id, file_path, file_url, file_type, file_size, usage_status, confidence, reference_count, local_status, cloud_status, last_scanned_at, created_at, updated_at )
			VALUES ( %d, %d, %s, %s, %s, %d, %s, %s, %d, %s, %s, %s, %s, %s )
			ON DUPLICATE KEY UPDATE
				scan_run_id = VALUES( scan_run_id ),
				file_path = VALUES( file_path ),
				file_url = VALUES( file_url ),
				file_type = VALUES( file_type ),
				file_size = VALUES( file_size ),
				usage_status = VALUES( usage_status ),
				confidence = VALUES( confidence ),
				reference_count = VALUES( reference_count ),
				local_status = VALUES( local_status ),
				cloud_status = VALUES( cloud_status ),
				last_scanned_at = VALUES( last_scanned_at ),
				updated_at = VALUES( updated_at )",
			(int) $attachment_id,
			isset( $data['scan_run_id'] ) ? (int) $data['scan_run_id'] : 0,
			isset( $data['file_path'] ) ? (string) $data['file_path'] : '',
			isset( $data['file_url'] ) ? (string) $data['file_url'] : '',
			isset( $data['file_type'] ) ? (string) $data['file_type'] : '',
			isset( $data['file_size'] ) ? (int) $data['file_size'] : 0,
			isset( $data['usage_status'] ) ? (string) $data['usage_status'] : self::STATUS_UNKNOWN,
			isset( $data['confidence'] ) ? (string) $data['confidence'] : '',
			isset( $data['reference_count'] ) ? (int) $data['reference_count'] : 0,
			isset( $data['local_status'] ) ? (string) $data['local_status'] : '',
			isset( $data['cloud_status'] ) ? (string) $data['cloud_status'] : '',
			$now,
			$now,
			$now
		);

		$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql is prepared above.
	}

	/**
	 * Upsert many verdict rows in batched multi-row statements (one query per
	 * chunk instead of one per attachment). Columns and the ON DUPLICATE KEY
	 * UPDATE list mirror upsert_item() exactly — `ignored` and `created_at` are
	 * omitted from the UPDATE so they survive rescans. Each item array uses the
	 * same keys as upsert_item()'s $data plus `attachment_id`.
	 *
	 * @param array[] $items Verdict field arrays (incl. attachment_id).
	 *
	 * @return void
	 */
	public static function upsert_items_bulk( array $items ) {
		if ( ! Schema::tables_exist() || empty( $items ) ) {
			return;
		}

		global $wpdb;

		$now   = current_time( 'mysql', true );
		$table = Schema::items_table();

		foreach ( array_chunk( $items, 100 ) as $chunk ) {
			$placeholders = array();
			$values       = array();

			foreach ( $chunk as $data ) {
				if ( empty( $data['attachment_id'] ) ) {
					continue;
				}

				$placeholders[] = '( %d, %d, %s, %s, %s, %d, %s, %s, %d, %s, %s, %s, %s, %s )';
				$values[]       = (int) $data['attachment_id'];
				$values[]       = isset( $data['scan_run_id'] ) ? (int) $data['scan_run_id'] : 0;
				$values[]       = isset( $data['file_path'] ) ? (string) $data['file_path'] : '';
				$values[]       = isset( $data['file_url'] ) ? (string) $data['file_url'] : '';
				$values[]       = isset( $data['file_type'] ) ? (string) $data['file_type'] : '';
				$values[]       = isset( $data['file_size'] ) ? (int) $data['file_size'] : 0;
				$values[]       = isset( $data['usage_status'] ) ? (string) $data['usage_status'] : self::STATUS_UNKNOWN;
				$values[]       = isset( $data['confidence'] ) ? (string) $data['confidence'] : '';
				$values[]       = isset( $data['reference_count'] ) ? (int) $data['reference_count'] : 0;
				$values[]       = isset( $data['local_status'] ) ? (string) $data['local_status'] : '';
				$values[]       = isset( $data['cloud_status'] ) ? (string) $data['cloud_status'] : '';
				$values[]       = $now; // last_scanned_at.
				$values[]       = $now; // created_at (INSERT only; preserved on UPDATE).
				$values[]       = $now; // updated_at.
			}

			if ( empty( $placeholders ) ) {
				continue;
			}

			$sql = "INSERT INTO {$table}
				( attachment_id, scan_run_id, file_path, file_url, file_type, file_size, usage_status, confidence, reference_count, local_status, cloud_status, last_scanned_at, created_at, updated_at )
				VALUES " . implode( ', ', $placeholders ) . '
				ON DUPLICATE KEY UPDATE
					scan_run_id = VALUES( scan_run_id ),
					file_path = VALUES( file_path ),
					file_url = VALUES( file_url ),
					file_type = VALUES( file_type ),
					file_size = VALUES( file_size ),
					usage_status = VALUES( usage_status ),
					confidence = VALUES( confidence ),
					reference_count = VALUES( reference_count ),
					local_status = VALUES( local_status ),
					cloud_status = VALUES( cloud_status ),
					last_scanned_at = VALUES( last_scanned_at ),
					updated_at = VALUES( updated_at )';

			$wpdb->query( $wpdb->prepare( $sql, $values ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- placeholders literal; values bound via prepare.
		}
	}

	/**
	 * Fetch the verdict row for an attachment.
	 *
	 * @param int $attachment_id Attachment id.
	 *
	 * @return object|null
	 */
	public static function get_item( $attachment_id ) {
		if ( ! Schema::tables_exist() || ! $attachment_id ) {
			return null;
		}

		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM ' . Schema::items_table() . ' WHERE attachment_id = %d', (int) $attachment_id )
		);
	}

	/**
	 * Fetch verdict rows for many attachments in one query. Used to batch-prime
	 * the Media Library "Usage" column so it does not query once per row.
	 *
	 * @param int[] $ids Attachment ids.
	 *
	 * @return object[] Verdict rows.
	 */
	public static function get_items_for_ids( array $ids ) {
		if ( ! Schema::tables_exist() ) {
			return array();
		}

		$ids = array_values( array_unique( array_filter( array_map( 'intval', $ids ) ) ) );
		if ( empty( $ids ) ) {
			return array();
		}

		global $wpdb;

		// $ids are cast to int, so the IN list is safe to interpolate.
		$in = implode( ',', $ids );

		return $wpdb->get_results( 'SELECT * FROM ' . Schema::items_table() . " WHERE attachment_id IN ( {$in} )" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- ids cast to int above.
	}

	/**
	 * Insert a single reference row.
	 *
	 * @param array $ref Reference fields.
	 *
	 * @return void
	 */
	public static function add_reference( array $ref ) {
		if ( ! Schema::tables_exist() || empty( $ref['attachment_id'] ) ) {
			return;
		}

		global $wpdb;

		$wpdb->insert(
			Schema::references_table(),
			array(
				'attachment_id'  => (int) $ref['attachment_id'],
				'scan_run_id'    => isset( $ref['scan_run_id'] ) ? (int) $ref['scan_run_id'] : 0,
				'source_type'    => isset( $ref['source_type'] ) ? substr( (string) $ref['source_type'], 0, 50 ) : '',
				'source_id'      => isset( $ref['source_id'] ) ? (int) $ref['source_id'] : 0,
				'source_label'   => isset( $ref['source_label'] ) ? substr( (string) $ref['source_label'], 0, 255 ) : '',
				'reference_type' => isset( $ref['reference_type'] ) ? substr( (string) $ref['reference_type'], 0, 50 ) : '',
				'confidence'     => isset( $ref['confidence'] ) ? substr( (string) $ref['confidence'], 0, 10 ) : '',
				'matched_value'  => isset( $ref['matched_value'] ) ? substr( (string) $ref['matched_value'], 0, 255 ) : '',
				'source_url'     => isset( $ref['source_url'] ) ? substr( (string) $ref['source_url'], 0, 512 ) : '',
				'last_seen_at'   => current_time( 'mysql', true ),
			),
			array( '%d', '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
	}

	/**
	 * Insert many reference rows in batched multi-row INSERTs (one query per
	 * chunk instead of one per reference). Mirrors add_reference()'s column
	 * handling exactly. Chunked to stay well within max_allowed_packet and the
	 * prepared-placeholder limit.
	 *
	 * @param array[] $refs Reference field arrays (same shape as add_reference()).
	 *
	 * @return void
	 */
	public static function add_references_bulk( array $refs ) {
		if ( ! Schema::tables_exist() || empty( $refs ) ) {
			return;
		}

		global $wpdb;

		$table = Schema::references_table();
		$now   = current_time( 'mysql', true );

		foreach ( array_chunk( $refs, 100 ) as $chunk ) {
			$placeholders = array();
			$values       = array();

			foreach ( $chunk as $ref ) {
				if ( empty( $ref['attachment_id'] ) ) {
					continue;
				}

				$placeholders[] = '( %d, %d, %s, %d, %s, %s, %s, %s, %s, %s )';
				$values[]       = (int) $ref['attachment_id'];
				$values[]       = isset( $ref['scan_run_id'] ) ? (int) $ref['scan_run_id'] : 0;
				$values[]       = isset( $ref['source_type'] ) ? substr( (string) $ref['source_type'], 0, 50 ) : '';
				$values[]       = isset( $ref['source_id'] ) ? (int) $ref['source_id'] : 0;
				$values[]       = isset( $ref['source_label'] ) ? substr( (string) $ref['source_label'], 0, 255 ) : '';
				$values[]       = isset( $ref['reference_type'] ) ? substr( (string) $ref['reference_type'], 0, 50 ) : '';
				$values[]       = isset( $ref['confidence'] ) ? substr( (string) $ref['confidence'], 0, 10 ) : '';
				$values[]       = isset( $ref['matched_value'] ) ? substr( (string) $ref['matched_value'], 0, 255 ) : '';
				$values[]       = isset( $ref['source_url'] ) ? substr( (string) $ref['source_url'], 0, 512 ) : '';
				$values[]       = $now;
			}

			if ( empty( $placeholders ) ) {
				continue;
			}

			$sql = "INSERT INTO {$table}
				( attachment_id, scan_run_id, source_type, source_id, source_label, reference_type, confidence, matched_value, source_url, last_seen_at )
				VALUES " . implode( ', ', $placeholders );

			$wpdb->query( $wpdb->prepare( $sql, $values ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- placeholders are literal; values bound via prepare.
		}
	}

	/**
	 * Delete all reference rows for one attachment (used before a rescan).
	 *
	 * @param int $attachment_id Attachment id.
	 *
	 * @return void
	 */
	public static function delete_references_for_attachment( $attachment_id ) {
		if ( ! Schema::tables_exist() || ! $attachment_id ) {
			return;
		}

		global $wpdb;
		$wpdb->delete( Schema::references_table(), array( 'attachment_id' => (int) $attachment_id ), array( '%d' ) );
	}

	/**
	 * All references for an attachment, newest first.
	 *
	 * @param int $attachment_id Attachment id.
	 *
	 * @return array
	 */
	public static function get_references( $attachment_id ) {
		if ( ! Schema::tables_exist() || ! $attachment_id ) {
			return array();
		}

		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare( 'SELECT * FROM ' . Schema::references_table() . ' WHERE attachment_id = %d ORDER BY id ASC', (int) $attachment_id )
		);
	}

	/**
	 * Count of references for an attachment.
	 *
	 * @param int $attachment_id Attachment id.
	 *
	 * @return int
	 */
	public static function count_references( $attachment_id ) {
		if ( ! Schema::tables_exist() || ! $attachment_id ) {
			return 0;
		}

		global $wpdb;

		return (int) $wpdb->get_var(
			$wpdb->prepare( 'SELECT COUNT(*) FROM ' . Schema::references_table() . ' WHERE attachment_id = %d', (int) $attachment_id )
		);
	}

	/**
	 * Reference counts for a batch of attachment ids in a single query.
	 *
	 * @param int[] $ids Attachment ids.
	 *
	 * @return array<int,int> Map of attachment_id => count (missing ids = 0).
	 */
	public static function count_references_for_ids( array $ids ) {
		$map = array();
		if ( ! Schema::tables_exist() || empty( $ids ) ) {
			return $map;
		}

		global $wpdb;

		$ids          = array_map( 'intval', $ids );
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT attachment_id, COUNT(*) AS c FROM ' . Schema::references_table() . " WHERE attachment_id IN ( {$placeholders} ) GROUP BY attachment_id",
				$ids
			)
		);

		foreach ( (array) $rows as $row ) {
			$map[ (int) $row->attachment_id ] = (int) $row->c;
		}

		return $map;
	}

	/**
	 * Remove verdict + reference rows for attachments that no longer exist
	 * (deleted from the Media Library since the last scan), so summary counts
	 * and the list table do not drift.
	 *
	 * @return void
	 */
	public static function delete_orphaned_items() {
		if ( ! Schema::tables_exist() ) {
			return;
		}

		global $wpdb;
		$items = Schema::items_table();
		$refs  = Schema::references_table();

		// Table names are plugin-controlled (prefix + constant), not user input.
		$wpdb->query( "DELETE r FROM {$refs} r LEFT JOIN {$wpdb->posts} p ON p.ID = r.attachment_id AND p.post_type = 'attachment' WHERE p.ID IS NULL" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DELETE i FROM {$items} i LEFT JOIN {$wpdb->posts} p ON p.ID = i.attachment_id AND p.post_type = 'attachment' WHERE p.ID IS NULL" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
	}

	/**
	 * Toggle the user-controlled "ignored" flag for an attachment.
	 *
	 * @param int  $attachment_id Attachment id.
	 * @param bool $ignored Whether to ignore.
	 *
	 * @return void
	 */
	public static function set_ignored( $attachment_id, $ignored ) {
		if ( ! Schema::tables_exist() || ! $attachment_id ) {
			return;
		}

		global $wpdb;
		$wpdb->update(
			Schema::items_table(),
			array(
				'ignored'    => $ignored ? 1 : 0,
				'updated_at' => current_time( 'mysql', true ),
			),
			array( 'attachment_id' => (int) $attachment_id ),
			array( '%d', '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Summary counts for the dashboard.
	 *
	 * @return array{total:int,referenced:int,possibly_unused:int,unknown:int,broken:int}
	 */
	public static function get_summary() {
		$summary = array(
			'total'           => 0,
			'referenced'      => 0,
			'possibly_unused' => 0,
			'unknown'         => 0,
			'broken'          => 0,
		);

		if ( ! Schema::tables_exist() ) {
			return $summary;
		}

		global $wpdb;

		$rows = $wpdb->get_results( 'SELECT usage_status, COUNT(*) AS c FROM ' . Schema::items_table() . ' GROUP BY usage_status' );
		foreach ( (array) $rows as $row ) {
			$summary['total'] += (int) $row->c;
			switch ( $row->usage_status ) {
				case self::STATUS_REFERENCED:
					$summary['referenced'] = (int) $row->c;
					break;
				case self::STATUS_POSSIBLY_UNUSED:
					$summary['possibly_unused'] = (int) $row->c;
					break;
				case self::STATUS_BROKEN:
					$summary['broken'] = (int) $row->c;
					break;
				default:
					$summary['unknown'] += (int) $row->c;
					break;
			}
		}

		return $summary;
	}

	/**
	 * Empty the references table only (used at the start of a fresh full scan).
	 *
	 * @return void
	 */
	public static function clear_references() {
		if ( ! Schema::tables_exist() ) {
			return;
		}

		global $wpdb;
		// Table name is plugin-controlled (prefix + constant), not user input.
		$wpdb->query( 'TRUNCATE TABLE ' . Schema::references_table() ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
	}

	/**
	 * Remove every scanner row for the current site (the "clear" action).
	 *
	 * @return void
	 */
	public static function clear_all() {
		if ( ! Schema::tables_exist() ) {
			return;
		}

		global $wpdb;
		// Table names are plugin-controlled (prefix + constant), not user input.
		$wpdb->query( 'TRUNCATE TABLE ' . Schema::references_table() ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
		$wpdb->query( 'TRUNCATE TABLE ' . Schema::items_table() ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
		$wpdb->query( 'TRUNCATE TABLE ' . Schema::runs_table() ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
	}
}
