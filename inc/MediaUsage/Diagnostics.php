<?php
/**
 * Diagnostics for the Media Library Usage Scanner.
 *
 * Auto-captures scan lifecycle + errors into a small rolling log, and assembles
 * a self-contained "bug report" (environment + scanner state + the log) that an
 * alpha tester can download and send to support. Nothing is transmitted from
 * here — the report is produced as a downloadable file by Scanner::handle_bug_report().
 *
 * Secrets (the API token, anything key-named token/secret/password/etc.) are
 * scrubbed from every section before it is stored or rendered.
 *
 * @package InfiniteUploads
 */

namespace ClikIT\InfiniteUploads\MediaUsage;

use ClikIT\InfiniteUploads\InfiniteUploadsHelper;

/**
 * Class Diagnostics
 */
class Diagnostics {

	/**
	 * Option holding the rolling diagnostic log (per-site, not autoloaded).
	 */
	const LOG_OPTION = 'iup_media_usage_debug_log';

	/**
	 * Cap on retained log entries (oldest are dropped first).
	 */
	const MAX_ENTRIES = 100;

	/**
	 * Hard byte cap on the stored log so the option can never bloat the DB.
	 */
	const MAX_BYTES = 65536;

	/**
	 * Append an entry to the rolling diagnostic log.
	 *
	 * @param string $level   One of info|warning|error.
	 * @param string $message Human-readable message.
	 * @param array  $context Optional structured context (scrubbed before save).
	 *
	 * @return void
	 */
	public static function log( $level, $message, $context = array() ) {
		$log = get_option( self::LOG_OPTION );
		if ( ! is_array( $log ) ) {
			$log = array();
		}

		$log[] = array(
			'time'    => gmdate( 'Y-m-d H:i:s' ),
			'level'   => (string) $level,
			'message' => (string) $message,
			'context' => self::scrub( (array) $context ),
		);

		// Trim by count, then by serialized size (drop oldest first).
		if ( count( $log ) > self::MAX_ENTRIES ) {
			$log = array_slice( $log, - self::MAX_ENTRIES );
		}
		while ( count( $log ) > 1 && strlen( (string) wp_json_encode( $log ) ) > self::MAX_BYTES ) {
			array_shift( $log );
		}

		update_option( self::LOG_OPTION, $log, false );
	}

	/**
	 * Convenience: log at info level.
	 *
	 * @param string $message Message.
	 * @param array  $context Context.
	 *
	 * @return void
	 */
	public static function info( $message, $context = array() ) {
		self::log( 'info', $message, $context );
	}

	/**
	 * Convenience: log at error level.
	 *
	 * @param string $message Message.
	 * @param array  $context Context.
	 *
	 * @return void
	 */
	public static function error( $message, $context = array() ) {
		self::log( 'error', $message, $context );
	}

	/**
	 * Read the rolling diagnostic log.
	 *
	 * @return array
	 */
	public static function get_log() {
		$log = get_option( self::LOG_OPTION );

		return is_array( $log ) ? $log : array();
	}

	/**
	 * Clear the diagnostic log.
	 *
	 * @return void
	 */
	public static function clear() {
		delete_option( self::LOG_OPTION );
	}

	/**
	 * Register a shutdown handler (once per request) to capture fatals that occur
	 * during the background scan — memory/timeout fatals bypass try/catch and
	 * would otherwise vanish into Action Scheduler's failed-actions table.
	 *
	 * @return void
	 */
	public static function arm_fatal_handler() {
		static $armed = false;
		if ( $armed ) {
			return;
		}
		$armed = true;

		register_shutdown_function( array( __CLASS__, 'on_shutdown' ) );
	}

	/**
	 * Shutdown handler: log a fatal that ended the scan worker and pause the run
	 * so it does not loop the same fatal on the next cron tick.
	 *
	 * @return void
	 */
	public static function on_shutdown() {
		$err = error_get_last();

		$fatal_types = array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR );
		if ( ! is_array( $err ) || ! in_array( $err['type'], $fatal_types, true ) ) {
			return;
		}

		self::error(
			'Fatal during background scan: ' . $err['message'],
			array(
				'file' => $err['file'] . ':' . $err['line'],
				'type' => $err['type'],
			)
		);

		$state = get_option( Engine::STATE_OPTION );
		if ( is_array( $state ) && ! empty( $state['run_id'] ) ) {
			Store::update_run( (int) $state['run_id'], array( 'status' => Store::RUN_PAUSED ) );
		}
	}

	/**
	 * Assemble the full downloadable bug report.
	 *
	 * @param string $note Optional free-text note from the tester.
	 *
	 * @return array
	 */
	public static function build_report( $note = '' ) {
		return array(
			'meta'             => array(
				'report_type'    => 'media-cleanup',
				'plugin_version' => defined( 'INFINITE_UPLOADS_VERSION' ) ? INFINITE_UPLOADS_VERSION : '',
				'generated_gmt'  => gmdate( 'Y-m-d H:i:s' ),
				'site_url'       => network_site_url(),
				'note'           => (string) $note,
			),
			'environment'      => self::environment(),
			'scanner'          => self::scanner_state(),
			'action_scheduler' => self::action_scheduler_state(),
			'log'              => self::get_log(),
		);
	}

	/**
	 * Environment snapshot (versions, limits, active theme + plugins).
	 *
	 * @return array
	 */
	private static function environment() {
		global $wp_version, $wpdb;

		$theme = wp_get_theme();

		return self::scrub(
			array(
				'wp_version'         => $wp_version,
				'php_version'        => PHP_VERSION,
				'mysql_version'      => $wpdb->db_version(),
				'web_server'         => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '',
				'is_multisite'       => is_multisite(),
				'locale'             => get_locale(),
				'php_memory_limit'   => ini_get( 'memory_limit' ),
				'wp_memory_limit'    => defined( 'WP_MEMORY_LIMIT' ) ? WP_MEMORY_LIMIT : '',
				'wp_max_memory'      => defined( 'WP_MAX_MEMORY_LIMIT' ) ? WP_MAX_MEMORY_LIMIT : '',
				'max_execution_time' => ini_get( 'max_execution_time' ),
				'wp_debug'           => defined( 'WP_DEBUG' ) && WP_DEBUG,
				'connected'          => InfiniteUploadsHelper::is_connected(),
				'active_theme'       => array(
					'name'     => $theme->get( 'Name' ),
					'version'  => $theme->get( 'Version' ),
					'template' => $theme->get_template(),
				),
				'active_plugins'     => self::active_plugins(),
			)
		);
	}

	/**
	 * List active plugins as "Name Version" strings (network-active included).
	 *
	 * @return array
	 */
	private static function active_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all    = get_plugins();
		$active = (array) get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$active = array_merge( $active, array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) ) );
		}

		$list = array();
		foreach ( array_unique( $active ) as $file ) {
			if ( isset( $all[ $file ] ) ) {
				$list[] = trim( $all[ $file ]['Name'] . ' ' . $all[ $file ]['Version'] );
			} else {
				$list[] = (string) $file;
			}
		}

		return $list;
	}

	/**
	 * Scanner feature state: toggle, counts, scan state, runs.
	 *
	 * @return array
	 */
	private static function scanner_state() {
		$active = Store::get_active_run();
		$latest = Store::get_latest_run();

		return self::scrub(
			array(
				'enabled'    => InfiniteUploadsHelper::is_media_usage_scanner_enabled(),
				'summary'    => Store::get_summary(),
				'scan_state' => get_option( Engine::STATE_OPTION ),
				'active_run' => $active ? (array) $active : null,
				'latest_run' => $latest ? (array) $latest : null,
			)
		);
	}

	/**
	 * Action Scheduler signals for the scan worker (availability, whether one is
	 * queued, and the count of failed scan actions).
	 *
	 * @return array
	 */
	private static function action_scheduler_state() {
		$state = array(
			'available'        => function_exists( 'as_has_scheduled_action' ),
			'worker_scheduled' => function_exists( 'as_has_scheduled_action' )
				? as_has_scheduled_action( Scanner::SCAN_HOOK, array(), 'infinite-uploads' )
				: null,
		);

		if ( function_exists( 'as_get_scheduled_actions' ) && class_exists( 'ActionScheduler_Store' ) ) {
			try {
				$failed = as_get_scheduled_actions(
					array(
						'hook'     => Scanner::SCAN_HOOK,
						'status'   => \ActionScheduler_Store::STATUS_FAILED,
						'per_page' => 10,
						'orderby'  => 'date',
						'order'    => 'DESC',
					),
					'ids'
				);

				$state['failed_action_count'] = is_array( $failed ) ? count( $failed ) : 0;
			} catch ( \Throwable $e ) {
				$state['failed_action_error'] = $e->getMessage();
			}
		}

		return $state;
	}

	/**
	 * Recursively redact secrets from a report section.
	 *
	 * @param mixed $data Data to scrub.
	 *
	 * @return mixed
	 */
	private static function scrub( $data ) {
		$token = (string) get_site_option( 'iup_apitoken' );

		return self::scrub_recursive( $data, $token );
	}

	/**
	 * Worker for scrub(): redact by key name and by the literal token value.
	 *
	 * @param mixed  $data  Data.
	 * @param string $token API token to redact wherever it appears.
	 *
	 * @return mixed
	 */
	private static function scrub_recursive( $data, $token ) {
		if ( is_array( $data ) ) {
			$out = array();
			foreach ( $data as $key => $value ) {
				if ( is_string( $key ) && preg_match( '/token|secret|password|pwd|api[_-]?key|auth|nonce/i', $key ) ) {
					$out[ $key ] = '[redacted]';
					continue;
				}
				$out[ $key ] = self::scrub_recursive( $value, $token );
			}

			return $out;
		}

		if ( is_string( $data ) && '' !== $token && false !== strpos( $data, $token ) ) {
			return str_replace( $token, '[redacted]', $data );
		}

		return $data;
	}
}
