<?php
/**
 * Tests for InfiniteUploadsAdmin::cloud_file_exists()'s sync-table lookup.
 *
 * serve_media_url() consults this per URL when file-exclusion mode is on, so
 * a Media Library grid drives ~270 calls (main file plus sub-sizes for ~68
 * attachments). The method therefore has two hard requirements that pull in
 * opposite directions:
 *
 *   1. It must not hit S3 for files already known to be synced. A HeadObject
 *      costs 100-500ms on a real network; 270 of them is a 20-second grid.
 *   2. It must not scale its memory with library size. Loading every synced
 *      path into a hashset satisfied (1) but cost ~116MB at 100k files,
 *      ~247MB at 300k, and OOM'd past that on a 256M limit — the same
 *      O(library) failure the exclusion tree was fixed for.
 *
 * The resolution is a per-path lookup against the table's PRIMARY KEY, which
 * is both cheaper and constant-memory: ~270 lookups measured 0.013s / 0MB
 * against a 242k-row table, versus 0.200s / 139MB to load the table.
 *
 * These tests guard requirement (2) specifically, since it is the one that
 * regresses silently — a full-table load behaves identically on a small test
 * site and only fails on the large libraries that can least afford it.
 *
 * cloud_file_exists() is private static and memoizes in a function-level
 * static, which cannot be reset between tests. Each test therefore uses a
 * distinct path so the memo from one never satisfies another.
 *
 * @package ClikIT\InfiniteUploads\Tests\Unit
 */

declare( strict_types=1 );

namespace ClikIT\InfiniteUploads\Tests\Unit;

use Brain\Monkey\Functions;
use ClikIT\InfiniteUploads\InfiniteUploadsAdmin;
use ClikIT\InfiniteUploads\Tests\TestCase;
use Mockery;
use ReflectionClass;

class CloudFileExistsTest extends TestCase {

	/**
	 * @var ReflectionClass<InfiniteUploadsAdmin>
	 */
	private $reflection;

	/**
	 * @var Mockery\MockInterface
	 */
	private $wpdb;

	protected function setUp(): void {
		parent::setUp();

		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsHelper.php';
		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsAdmin.php';

		$this->reflection = new ReflectionClass( InfiniteUploadsAdmin::class );
		$this->wpdb       = $this->mock_wpdb();

		// Stubs for the S3 fallback branch. Nothing here should be reached by
		// a synced path, but a regression that stops resolving from the sync
		// table will fall through to it — and these keep that showing up as a
		// readable assertion failure instead of a fatal on a missing WP
		// function. get_cloud_upload_path() returns a non-iu:// value so the
		// fallback's own guard skips the HeadObject.
		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
		Functions\when( 'untrailingslashit' )->alias( function ( $s ) {
			return rtrim( (string) $s, '/' );
		} );
		Functions\when( 'wp_upload_dir' )->justReturn( [
			'basedir' => '/var/www/html/wp-content/uploads',
			'baseurl' => 'https://example.test/wp-content/uploads',
			'subdir'  => '',
		] );
	}

	/**
	 * Invoke the private static under test.
	 *
	 * @param  string  $relative_path
	 *
	 * @return bool
	 */
	private function cloud_file_exists( string $relative_path ) {
		$method = $this->reflection->getMethod( 'cloud_file_exists' );
		$method->setAccessible( true );

		return $method->invoke( null, $relative_path );
	}

	public function test_synced_file_resolves_without_touching_s3(): void {
		$this->wpdb->shouldReceive( 'get_var' )->once()->andReturn( '1' );

		$this->assertTrue(
			$this->cloud_file_exists( '/2026/04/synced-hit.jpg' ),
			'A file marked synced=1 should resolve straight from the sync table'
		);
	}

	public function test_lookup_is_scoped_to_one_path_not_the_whole_table(): void {
		// The regression guard. The previous implementation ran
		// `SELECT file ... WHERE synced = 1` with no file predicate and
		// array_flip()'d the result, which is O(library) in memory.
		$seen = '';
		$this->wpdb->shouldReceive( 'get_var' )->once()->andReturnUsing(
			function ( $sql ) use ( &$seen ) {
				$seen = (string) $sql;

				return '1';
			}
		);

		$this->cloud_file_exists( '/2026/04/scoped-query.jpg' );

		$this->assertStringContainsString(
			'WHERE file =',
			$seen,
			'The sync-table check must filter on the specific path'
		);
		$this->assertStringContainsString(
			'/2026/04/scoped-query.jpg',
			$seen,
			'The requested path must be bound into the query'
		);
		$this->assertStringContainsString(
			'LIMIT 1',
			$seen,
			'A single-row existence check must be bounded'
		);
	}

	public function test_does_not_bulk_load_the_sync_table(): void {
		// get_col() is how the full-table load was issued. Nothing in this
		// path should ever fetch a column of every synced file again.
		$this->wpdb->shouldNotReceive( 'get_col' );
		$this->wpdb->shouldReceive( 'get_var' )->once()->andReturn( '1' );

		$this->assertTrue( $this->cloud_file_exists( '/2026/04/no-bulk-load.jpg' ) );
	}

	public function test_repeat_lookups_of_same_path_hit_the_memo(): void {
		// One query for the first call; the static memo serves the rest, which
		// is what keeps ~270 grid lookups down to the number of unique paths.
		$this->wpdb->shouldReceive( 'get_var' )->once()->andReturn( '1' );

		$path = '/2026/04/memoized.jpg';
		$this->assertTrue( $this->cloud_file_exists( $path ) );
		$this->assertTrue( $this->cloud_file_exists( $path ) );
		$this->assertTrue( $this->cloud_file_exists( $path ) );
	}

	public function test_empty_path_short_circuits_before_any_query(): void {
		$this->wpdb->shouldNotReceive( 'get_var' );

		$this->assertFalse( $this->cloud_file_exists( '' ) );
	}
}
