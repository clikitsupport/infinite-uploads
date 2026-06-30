<?php
/**
 * Tests for infinite_uploads_bb_carveout_backfill — the chunked one-time
 * backfill cron handler added in 3.2.4.
 *
 * The handler walks /wp-content/uploads/bb-plugin/cache/ via DirectoryIterator,
 * skips entries already in the DB (the "cursor"), bulk-inserts up to 500 rows
 * per tick, and either sets the iup_bb_carveout_backfilled flag (iteration
 * completed) or re-schedules itself (budget exceeded).
 *
 * Tests use a real temp directory for the cache scan (cheaper than mocking
 * DirectoryIterator) and Mockery for $wpdb.
 *
 * @package ClikIT\InfiniteUploads\Tests\Unit
 */

declare( strict_types=1 );

namespace ClikIT\InfiniteUploads\Tests\Unit;

use Brain\Monkey\Functions;
use ClikIT\InfiniteUploads\Tests\TestCase;

class BackfillTest extends TestCase {

	/**
	 * @var string Path to the temp /uploads/bb-plugin/cache/ directory.
	 */
	private $cache_dir;

	/**
	 * @var string Path to the temp uploads basedir.
	 */
	private $base_dir;

	protected function setUp(): void {
		parent::setUp();

		// Build a fake WP_CONTENT_DIR with bb-plugin/cache/ inside.
		$root            = sys_get_temp_dir() . '/iu-backfill-test-' . uniqid();
		$this->base_dir  = $root . '/uploads';
		$this->cache_dir = $this->base_dir . '/bb-plugin/cache';
		mkdir( $this->cache_dir, 0755, true );

		// Override WP_CONTENT_DIR so backfill walks our temp tree. We can't
		// redefine a constant, so we use a runtime constant lookup that
		// already-defined value of WP_CONTENT_DIR points at sys_get_temp_dir
		// from bootstrap; override via Brain Monkey by mocking the helper
		// that consumes it instead.
		//
		// Cleaner approach: write the cache_dir directly under WP_CONTENT_DIR
		// using a unique subdir, but the production function hard-codes
		// `WP_CONTENT_DIR . '/uploads/bb-plugin/cache/'`. So instead we mock
		// InfiniteUploadsHelper::get_original_upload_dir_root and create the
		// expected directory under the constant value used by bootstrap.
		// Reconcile by writing cache_dir IN the bootstrap WP_CONTENT_DIR.
		$this->cache_dir = WP_CONTENT_DIR . '/uploads/bb-plugin/cache';
		$this->base_dir  = WP_CONTENT_DIR . '/uploads';
		if ( ! is_dir( $this->cache_dir ) ) {
			mkdir( $this->cache_dir, 0755, true );
		}
		// Clean any leftovers from a previous test run.
		foreach ( glob( $this->cache_dir . '/*' ) as $f ) {
			@unlink( $f );
		}

		// Common stubs.
		Functions\when( 'wp_parse_url' )->alias(
			static function ( $url, $component = -1 ) {
				return \parse_url( $url, $component );
			}
		);
		Functions\when( 'untrailingslashit' )->alias(
			static function ( $s ) {
				return rtrim( (string) $s, '/' );
			}
		);
		Functions\when( 'wp_check_filetype' )->alias(
			static function ( $file ) {
				$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
				$map = [
					'jpg'  => 'image/jpeg',
					'jpeg' => 'image/jpeg',
					'png'  => 'image/png',
					'webp' => 'image/webp',
					'css'  => 'text/css',
					'js'   => 'application/javascript',
				];
				return [ 'ext' => $ext, 'type' => $map[ $ext ] ?? 'application/octet-stream' ];
			}
		);
		Functions\when( 'infinite_uploads_enabled' )->justReturn( true );
		Functions\when( 'wp_next_scheduled' )->justReturn( false );
		Functions\when( 'wp_schedule_single_event' )->justReturn( true );

		// Load the integration so the function is available. Function
		// declarations are guarded by function_exists, safe to require
		// multiple times.
		require_once IU_TESTS_ROOT . '/fixtures/ewww-environment.php';
		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsHelper.php';
		require IU_PLUGIN_ROOT . '/inc/bb-cache-integration.php';

		// Mock InfiniteUploadsHelper::get_original_upload_dir_root so it
		// returns the base dir we set up.
		Functions\when( 'get_site_option' )->justReturn( 'no' );
	}

	protected function tearDown(): void {
		// Clean up the temp cache files.
		foreach ( glob( $this->cache_dir . '/*' ) as $f ) {
			@unlink( $f );
		}
		parent::tearDown();
	}

	/**
	 * Helper: write a fake file in the cache dir with some content.
	 */
	private function write_cache_file( string $name, string $body = 'x' ): string {
		$path = $this->cache_dir . '/' . $name;
		file_put_contents( $path, $body );
		return $path;
	}

	private function mock_get_original_upload_dir_root(): void {
		// InfiniteUploadsHelper::get_original_upload_dir_root calls a chain
		// of WP functions; stub them so the helper resolves to
		// WP_CONTENT_DIR . '/uploads' (where our test files live).
		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
		Functions\when( 'is_multisite' )->justReturn( false );
		Functions\when( 'is_main_network' )->justReturn( true );
		Functions\when( 'is_main_site' )->justReturn( true );
		Functions\when( 'ms_is_switched' )->justReturn( false );
		Functions\when( 'apply_filters' )->returnArg( 2 );
		// get_option('upload_path') returns empty → fall through to WP_CONTENT_DIR.
		Functions\when( 'get_option' )->alias(
			static function ( $key, $default = false ) {
				return $key === 'upload_path' ? '' : $default;
			}
		);
		Functions\when( 'wp_upload_dir' )->justReturn( [
			'basedir' => $this->base_dir,
			'baseurl' => 'http://example.test/wp-content/uploads',
			'path'    => $this->base_dir,
			'url'     => 'http://example.test/wp-content/uploads',
		] );
	}

	// -------------------------------------------------------------------------
	// Single-tick completion (small directory)
	// -------------------------------------------------------------------------

	public function test_sets_completion_flag_when_directory_empty(): void {
		$this->mock_get_original_upload_dir_root();
		$wpdb = $this->mock_wpdb();
		$wpdb->shouldReceive( 'get_col' )->andReturn( [] );

		$flag_set = false;
		Functions\when( 'update_site_option' )->alias(
			static function ( $key, $value ) use ( &$flag_set ) {
				if ( $key === 'iup_bb_carveout_backfilled' ) {
					$flag_set = true;
				}
				return true;
			}
		);

		\ClikIT\InfiniteUploads\infinite_uploads_bb_carveout_backfill();

		$this->assertTrue( $flag_set, 'Backfill must set the completion flag when there is nothing to do' );
	}

	public function test_inserts_image_files_and_sets_completion_flag(): void {
		$this->mock_get_original_upload_dir_root();
		$this->write_cache_file( 'crop-300x200.jpg' );
		$this->write_cache_file( 'crop-hero.png' );
		$this->write_cache_file( 'layout.css' ); // should be skipped by predicate
		$this->write_cache_file( 'layout.js' );  // should be skipped by predicate

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldReceive( 'get_col' )->andReturn( [] );

		// One INSERT for the batch (2 image rows).
		$insert_count = 0;
		$wpdb->shouldReceive( 'query' )
			->andReturnUsing(
				static function ( $sql ) use ( &$insert_count ) {
					$insert_count++;
					return 2;
				}
			);

		$flag_set = false;
		Functions\when( 'update_site_option' )->alias(
			static function ( $key, $value ) use ( &$flag_set ) {
				if ( $key === 'iup_bb_carveout_backfilled' ) {
					$flag_set = true;
				}
				return true;
			}
		);

		\ClikIT\InfiniteUploads\infinite_uploads_bb_carveout_backfill();

		$this->assertSame( 1, $insert_count, 'Both image rows should be flushed in a single bulk INSERT' );
		$this->assertTrue( $flag_set );
	}

	public function test_skips_files_already_in_db_cursor(): void {
		$this->mock_get_original_upload_dir_root();
		$this->write_cache_file( 'already-synced.jpg' );
		$this->write_cache_file( 'new-crop.jpg' );

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldReceive( 'get_col' )
			->andReturn( [ '/bb-plugin/cache/already-synced.jpg' ] );

		$inserted_sql = '';
		$wpdb->shouldReceive( 'query' )
			->andReturnUsing(
				static function ( $sql ) use ( &$inserted_sql ) {
					$inserted_sql = $sql;
					return 1;
				}
			);

		Functions\when( 'update_site_option' )->justReturn( true );

		\ClikIT\InfiniteUploads\infinite_uploads_bb_carveout_backfill();

		$this->assertStringContainsString( 'new-crop.jpg', $inserted_sql );
		$this->assertStringNotContainsString( 'already-synced.jpg', $inserted_sql );
	}

	public function test_does_not_insert_when_only_non_image_files_present(): void {
		$this->mock_get_original_upload_dir_root();
		$this->write_cache_file( 'just-a-layout.css' );
		$this->write_cache_file( 'and-some.js' );

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldReceive( 'get_col' )->andReturn( [] );

		$query_calls = 0;
		$wpdb->shouldReceive( 'query' )
			->andReturnUsing(
				static function () use ( &$query_calls ) {
					$query_calls++;
					return 0;
				}
			);

		Functions\when( 'update_site_option' )->justReturn( true );

		\ClikIT\InfiniteUploads\infinite_uploads_bb_carveout_backfill();

		$this->assertSame( 0, $query_calls, 'Layout-only directory should not produce any INSERTs' );
	}

	// -------------------------------------------------------------------------
	// Cache dir doesn't exist → immediate completion (no work to do)
	// -------------------------------------------------------------------------

	public function test_marks_complete_when_cache_dir_missing(): void {
		// Remove the cache dir entirely.
		rmdir( $this->cache_dir );

		$flag_set = false;
		Functions\when( 'update_site_option' )->alias(
			static function ( $key, $value ) use ( &$flag_set ) {
				if ( $key === 'iup_bb_carveout_backfilled' ) {
					$flag_set = true;
				}
				return true;
			}
		);

		\ClikIT\InfiniteUploads\infinite_uploads_bb_carveout_backfill();

		// Recreate so tearDown's glob doesn't error.
		mkdir( $this->cache_dir, 0755, true );

		$this->assertTrue( $flag_set, 'Missing cache dir is "nothing to do" — flag should be set' );
	}

	// -------------------------------------------------------------------------
	// Anchored LIKE in the cursor query — regression-protect 3.2.5 fix
	// -------------------------------------------------------------------------

	public function test_db_cursor_uses_anchored_like_pattern(): void {
		$this->mock_get_original_upload_dir_root();
		$captured_sql = '';
		$wpdb         = $this->mock_wpdb();
		$wpdb->shouldReceive( 'get_col' )
			->andReturnUsing(
				static function ( $sql ) use ( &$captured_sql ) {
					$captured_sql = $sql;
					return [];
				}
			);

		Functions\when( 'update_site_option' )->justReturn( true );

		\ClikIT\InfiniteUploads\infinite_uploads_bb_carveout_backfill();

		$this->assertStringContainsString(
			"LIKE '/bb-plugin/cache/%'",
			$captured_sql,
			'Cursor query must use anchored LIKE (3.2.5 perf fix)'
		);
		$this->assertStringNotContainsString(
			"LIKE '%/bb-plugin/cache/%'",
			$captured_sql,
			'Cursor query must NOT use leading wildcard (regression guard)'
		);
	}

	// -------------------------------------------------------------------------
	// Cron-push kick after successful insert
	// -------------------------------------------------------------------------

	public function test_schedules_push_after_inserting_when_iu_enabled(): void {
		$this->mock_get_original_upload_dir_root();
		$this->write_cache_file( 'crop.jpg' );

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldReceive( 'get_col' )->andReturn( [] );
		$wpdb->shouldReceive( 'query' )->once();

		$scheduled_hooks = [];
		Functions\when( 'wp_schedule_single_event' )->alias(
			static function ( $timestamp, $hook ) use ( &$scheduled_hooks ) {
				$scheduled_hooks[] = $hook;
				return true;
			}
		);

		Functions\when( 'update_site_option' )->justReturn( true );

		\ClikIT\InfiniteUploads\infinite_uploads_bb_carveout_backfill();

		$this->assertContains(
			'iu_bb_cache_push',
			$scheduled_hooks,
			'iu_bb_cache_push must be scheduled after a successful backfill insert'
		);
	}

	public function test_does_not_schedule_push_when_iu_disabled(): void {
		$this->mock_get_original_upload_dir_root();
		$this->write_cache_file( 'crop.jpg' );

		// IU not enabled — rows get queued but push is NOT scheduled.
		Functions\when( 'infinite_uploads_enabled' )->justReturn( false );

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldReceive( 'get_col' )->andReturn( [] );
		$wpdb->shouldReceive( 'query' )->once();

		$scheduled_hooks = [];
		Functions\when( 'wp_schedule_single_event' )->alias(
			static function ( $timestamp, $hook ) use ( &$scheduled_hooks ) {
				$scheduled_hooks[] = $hook;
				return true;
			}
		);

		Functions\when( 'update_site_option' )->justReturn( true );

		\ClikIT\InfiniteUploads\infinite_uploads_bb_carveout_backfill();

		$this->assertNotContains(
			'iu_bb_cache_push',
			$scheduled_hooks,
			'iu_bb_cache_push must NOT be scheduled when sync is disabled'
		);
	}
}
