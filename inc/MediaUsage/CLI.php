<?php
/**
 * WP-CLI commands for the Media Library Usage Scanner.
 *
 * Registered as `wp infinite-uploads media-usage <subcommand>`:
 *   - scan [--attachment_id=<id>]   Run a full scan, or rescan one attachment.
 *   - export [<file>]               Write results to a CSV file (or STDOUT).
 *   - clear                         Delete all stored scan results.
 *
 * @package InfiniteUploads
 */

namespace ClikIT\InfiniteUploads\MediaUsage;

use ClikIT\InfiniteUploads\InfiniteUploadsHelper;

/**
 * Class CLI
 */
class CLI extends \WP_CLI_Command {

	/**
	 * Scan the Media Library for usage, or rescan a single attachment.
	 *
	 * ## OPTIONS
	 *
	 * [--attachment_id=<id>]
	 * : Rescan only this attachment instead of the whole library.
	 *
	 * @subcommand scan
	 * @synopsis [--attachment_id=<id>]
	 *
	 * @param array $args Positional args.
	 * @param array $assoc_args Flags.
	 *
	 * @return void
	 */
	public function scan( $args, $assoc_args ) {
		if ( ! $this->gate_ok() ) {
			return;
		}

		$assoc_args = wp_parse_args( $assoc_args, array( 'attachment_id' => 0 ) );

		if ( ! empty( $assoc_args['attachment_id'] ) ) {
			$id = absint( $assoc_args['attachment_id'] );
			if ( ! Engine::rescan_attachment( $id ) ) {
				\WP_CLI::error( sprintf( 'Could not rescan attachment %d.', $id ) );
			}
			$item = Store::get_item( $id );
			\WP_CLI::success( sprintf( 'Rescanned attachment %d: %s', $id, $item ? $item->usage_status : 'unknown' ) );

			return;
		}

		$run_id = Engine::start_run( 0 );
		if ( ! $run_id ) {
			\WP_CLI::error( 'Could not start the scan.' );
		}

		\WP_CLI::log( 'Scanning Media Library usage…' );

		$last_message = '';
		do {
			$progress = Engine::process_batch();
			$message  = isset( $progress['message'] ) ? $progress['message'] : '';
			if ( $message && $message !== $last_message ) {
				\WP_CLI::log( sprintf( '[%d%%] %s', isset( $progress['percent'] ) ? (int) $progress['percent'] : 0, $message ) );
				$last_message = $message;
			}

			// This loop runs the whole scan in one PHP process, so clear the
			// per-batch request cache each iteration to bound memory.
			Matcher::reset_cache();
		} while ( isset( $progress['status'] ) && 'running' === $progress['status'] );

		$summary = Store::get_summary();
		\WP_CLI::success(
			sprintf(
				'Scan complete. %d total, %d referenced, %d possibly unused, %d unknown.',
				$summary['total'],
				$summary['referenced'],
				$summary['possibly_unused'],
				$summary['unknown']
			)
		);
	}

	/**
	 * Export scan results to CSV.
	 *
	 * ## OPTIONS
	 *
	 * [<file>]
	 * : Destination file path. Defaults to STDOUT.
	 *
	 * @subcommand export
	 * @synopsis [<file>]
	 *
	 * @param array $args Positional args.
	 * @param array $assoc_args Flags.
	 *
	 * @return void
	 */
	public function export( $args, $assoc_args ) {
		if ( ! $this->gate_ok() ) {
			return;
		}

		$file   = isset( $args[0] ) ? $args[0] : 'php://stdout';
		$handle = fopen( $file, 'w' );
		if ( ! $handle ) {
			\WP_CLI::error( sprintf( 'Could not open %s for writing.', $file ) );
		}

		$count = Exporter::stream( $handle );
		fclose( $handle );

		if ( 'php://stdout' !== $file ) {
			\WP_CLI::success( sprintf( 'Exported %d rows to %s', $count, $file ) );
		}
	}

	/**
	 * Delete all stored scan results for this site.
	 *
	 * @subcommand clear
	 *
	 * @param array $args Positional args.
	 * @param array $assoc_args Flags.
	 *
	 * @return void
	 */
	public function clear( $args, $assoc_args ) {
		if ( ! $this->gate_ok() ) {
			return;
		}

		Engine::cancel();
		Store::clear_all();
		\WP_CLI::success( 'Cleared all Media Library Usage Scanner results.' );
	}

	/**
	 * Ensure the site is connected and the feature is enabled.
	 *
	 * @return bool
	 */
	private function gate_ok() {
		if ( ! InfiniteUploadsHelper::is_connected() ) {
			\WP_CLI::error( 'This site is not connected to Infinite Uploads.', false );

			return false;
		}

		if ( ! InfiniteUploadsHelper::is_media_usage_scanner_enabled() ) {
			\WP_CLI::error( 'The Media Library Usage Scanner is not enabled.', false );

			return false;
		}

		return true;
	}
}
