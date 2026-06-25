<?php
/**
 * Database schema for the Media Library Usage Scanner.
 *
 * Owns the three per-site custom tables used by the scanner and the
 * create/upgrade lifecycle for them. Kept separate from the rest of the
 * plugin so the scanner's storage is self-contained and easy to reason about.
 *
 * @package InfiniteUploads
 */

namespace ClikIT\InfiniteUploads\MediaUsage;

/**
 * Class Schema
 *
 * Static helper that creates, upgrades, locates and drops the scanner tables.
 * All three tables are PER-SITE ( $wpdb->prefix ) because the data they hold
 * (attachments, post content, builder meta) is per-subsite on multisite.
 */
class Schema {

	/**
	 * Bump this whenever a table definition changes so existing installs
	 * re-run dbDelta on the next request. Independent of the plugin version.
	 */
	const DB_VERSION = 1;

	/**
	 * Per-site option that records the installed schema version.
	 */
	const VERSION_OPTION = 'iup_media_usage_db_version';

	/**
	 * Scan-run / progress log table (unprefixed base name).
	 */
	const RUNS = 'infinite_uploads_media_scan_runs';

	/**
	 * Per-attachment verdict table (unprefixed base name).
	 */
	const ITEMS = 'infinite_uploads_media_scan_items';

	/**
	 * Per-reference detail table (unprefixed base name).
	 */
	const REFERENCES = 'infinite_uploads_media_references';

	/**
	 * Request-level cache of "does this table exist" lookups, keyed by the
	 * full (prefixed) table name so it stays correct across switch_to_blog().
	 *
	 * @var array<string,bool>
	 */
	private static $exists_cache = array();

	/**
	 * Full, prefixed name of the scan-runs table for the current site.
	 *
	 * @return string
	 */
	public static function runs_table() {
		global $wpdb;

		return $wpdb->prefix . self::RUNS;
	}

	/**
	 * Full, prefixed name of the scan-items table for the current site.
	 *
	 * @return string
	 */
	public static function items_table() {
		global $wpdb;

		return $wpdb->prefix . self::ITEMS;
	}

	/**
	 * Full, prefixed name of the references table for the current site.
	 *
	 * @return string
	 */
	public static function references_table() {
		global $wpdb;

		return $wpdb->prefix . self::REFERENCES;
	}

	/**
	 * Create the scanner tables for the current site if they are missing or
	 * out of date. Cheap to call on every request: it only compares an
	 * autoloaded option and short-circuits when the schema is current.
	 *
	 * @return void
	 */
	public static function maybe_upgrade() {
		if ( (int) get_option( self::VERSION_OPTION ) === self::DB_VERSION ) {
			return;
		}

		self::install();
	}

	/**
	 * Run dbDelta to create or update the three per-site tables.
	 *
	 * The version option is written BEFORE the (potentially slow) dbDelta call
	 * to guard against concurrent requests racing to run CREATE TABLE, matching
	 * the pattern used by infinite_uploads_install_folders().
	 *
	 * @return void
	 */
	public static function install() {
		global $wpdb;

		// Mark done first to prevent a race during the slow dbDelta below.
		update_option( self::VERSION_OPTION, self::DB_VERSION, true );

		$charset_collate = $wpdb->get_charset_collate();
		$runs_table      = self::runs_table();
		$items_table     = self::items_table();
		$references_table = self::references_table();

		// Scan-run / progress log: one row per scan run.
		$sql = "CREATE TABLE {$runs_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            total_items BIGINT UNSIGNED NOT NULL DEFAULT 0,
            scanned_items BIGINT UNSIGNED NOT NULL DEFAULT 0,
            referenced_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
            possibly_unused_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
            unknown_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
            broken_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
            created_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
            started_at DATETIME NULL DEFAULT NULL,
            completed_at DATETIME NULL DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY status (status)
        ) {$charset_collate};";

		// Per-attachment verdict: one row per attachment (UNIQUE on attachment_id).
		$sql .= "\nCREATE TABLE {$items_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            attachment_id BIGINT UNSIGNED NOT NULL,
            scan_run_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            file_path VARCHAR(255) NOT NULL DEFAULT '',
            file_url VARCHAR(512) NOT NULL DEFAULT '',
            file_type VARCHAR(50) NOT NULL DEFAULT '',
            file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
            usage_status VARCHAR(20) NOT NULL DEFAULT 'unknown',
            confidence VARCHAR(10) NOT NULL DEFAULT '',
            reference_count INT UNSIGNED NOT NULL DEFAULT 0,
            local_status VARCHAR(20) NOT NULL DEFAULT '',
            cloud_status VARCHAR(20) NOT NULL DEFAULT '',
            ignored BOOLEAN NOT NULL DEFAULT 0,
            last_scanned_at DATETIME NULL DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY attachment_id (attachment_id),
            KEY usage_status (usage_status),
            KEY ignored (ignored),
            KEY scan_run_id (scan_run_id),
            KEY local_status (local_status),
            KEY cloud_status (cloud_status)
        ) {$charset_collate};";

		// Per-reference detail: one row per place an attachment was found.
		$sql .= "\nCREATE TABLE {$references_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            attachment_id BIGINT UNSIGNED NOT NULL,
            scan_run_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            source_type VARCHAR(50) NOT NULL DEFAULT '',
            source_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            source_label VARCHAR(255) NOT NULL DEFAULT '',
            reference_type VARCHAR(50) NOT NULL DEFAULT '',
            confidence VARCHAR(10) NOT NULL DEFAULT '',
            matched_value VARCHAR(255) NOT NULL DEFAULT '',
            source_url VARCHAR(512) NOT NULL DEFAULT '',
            last_seen_at DATETIME NULL DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY attachment_id (attachment_id),
            KEY source_type (source_type),
            KEY scan_run_id (scan_run_id)
        ) {$charset_collate};";

		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		dbDelta( $sql );

		// Refresh the existence cache for this site after (re)creating tables.
		self::$exists_cache = array();
	}

	/**
	 * Whether all three scanner tables exist for the current site.
	 *
	 * Used as a defensive guard by read/write callers so a missing-table
	 * edge case (e.g. a partial upgrade) degrades gracefully instead of
	 * throwing a database error.
	 *
	 * @return bool
	 */
	public static function tables_exist() {
		foreach ( array( self::runs_table(), self::items_table(), self::references_table() ) as $table ) {
			if ( ! self::table_exists( $table ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Whether a single fully-qualified table exists (request-cached).
	 *
	 * @param string $table Full prefixed table name.
	 *
	 * @return bool
	 */
	private static function table_exists( $table ) {
		if ( isset( self::$exists_cache[ $table ] ) ) {
			return self::$exists_cache[ $table ];
		}

		global $wpdb;

		// $table is built from $wpdb->prefix and a class constant, never user input.
		$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		self::$exists_cache[ $table ] = ( $found === $table );

		return self::$exists_cache[ $table ];
	}

	/**
	 * Drop the three scanner tables for the CURRENT site and delete the
	 * version option. Intended for uninstall; callers are responsible for
	 * switch_to_blog() iteration on multisite.
	 *
	 * @return void
	 */
	public static function drop() {
		global $wpdb;

		foreach ( array( self::references_table(), self::items_table(), self::runs_table() ) as $table ) {
			// Table name is plugin-controlled (prefix + constant), not user input.
			$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared
		}

		delete_option( self::VERSION_OPTION );

		self::$exists_cache = array();
	}
}
