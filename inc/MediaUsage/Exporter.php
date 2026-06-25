<?php
/**
 * CSV exporter for Media Library Usage Scanner results.
 *
 * Shared by the admin download (Scanner::handle_export) and the WP-CLI export
 * command so the two can never drift. Streams with keyset pagination to scale
 * to large libraries.
 *
 * @package InfiniteUploads
 */

namespace ClikIT\InfiniteUploads\MediaUsage;

/**
 * Class Exporter
 */
class Exporter {

	/**
	 * Write the full results CSV (header + all rows) to an open file handle.
	 *
	 * @param resource $handle Writable stream (php://output, a file, etc.).
	 *
	 * @return int Number of data rows written.
	 */
	public static function stream( $handle ) {
		fputcsv(
			$handle,
			array(
				__( 'Attachment ID', 'infinite-uploads' ),
				__( 'File', 'infinite-uploads' ),
				__( 'Type', 'infinite-uploads' ),
				__( 'Size (bytes)', 'infinite-uploads' ),
				__( 'Usage status', 'infinite-uploads' ),
				__( 'References', 'infinite-uploads' ),
				__( 'Ignored', 'infinite-uploads' ),
				__( 'File URL', 'infinite-uploads' ),
				__( 'Last scanned (UTC)', 'infinite-uploads' ),
			)
		);

		if ( ! Schema::tables_exist() ) {
			return 0;
		}

		global $wpdb;
		$items_table = Schema::items_table();
		$last_id     = 0;
		$count       = 0;

		do {
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT i.*, p.post_title FROM {$items_table} i LEFT JOIN {$wpdb->posts} p ON p.ID = i.attachment_id WHERE i.id > %d ORDER BY i.id ASC LIMIT %d",
					$last_id,
					500
				)
			);

			foreach ( (array) $rows as $row ) {
				$last_id = (int) $row->id;
				$count++;
				fputcsv(
					$handle,
					array(
						(int) $row->attachment_id,
						self::csv_safe( $row->post_title ? $row->post_title : wp_basename( (string) $row->file_path ) ),
						self::csv_safe( $row->file_type ),
						(int) $row->file_size,
						self::csv_safe( Scanner::status_label( $row->usage_status, (int) $row->reference_count ) ),
						(int) $row->reference_count,
						( (int) $row->ignored ) ? __( 'Yes', 'infinite-uploads' ) : __( 'No', 'infinite-uploads' ),
						self::csv_safe( $row->file_url ),
						self::csv_safe( $row->last_scanned_at ),
					)
				);
			}
		} while ( ! empty( $rows ) );

		return $count;
	}

	/**
	 * Neutralize CSV formula injection: a cell beginning with =, +, -, or @ is
	 * executed as a formula by spreadsheet apps. Prefix such cells with a quote.
	 *
	 * @param string $value Cell value.
	 *
	 * @return string
	 */
	private static function csv_safe( $value ) {
		$value = (string) $value;
		if ( '' !== $value && in_array( $value[0], array( '=', '+', '-', '@' ), true ) ) {
			return "'" . $value;
		}

		return $value;
	}
}
