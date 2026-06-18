<?php
/**
 * Tests for infinite_uploads_bb_photo_cropped — the fl_builder_photo_cropped
 * action handler added in 3.2.4. Fires immediately after BB's
 * FLBuilderPhoto::crop() saves a freshly-cropped image.
 *
 * Behaviour we lock here:
 *   - Defensive validation: input must be an array with a non-empty 'path'
 *     that exists on disk and is an offloadable BB cache image.
 *   - Skips entirely when IU isn't enabled.
 *   - Converts absolute path → relative-to-uploads form (leading slash).
 *   - INSERTs into infinite_uploads_files with synced=0 (ON DUPLICATE KEY
 *     UPDATE so a re-crop with the same filename refreshes the row).
 *   - Invalidates the rewriter's iu_bb_cache_synced_paths cache.
 *   - Schedules the iu_bb_cache_push cron event, idempotently.
 *
 * @package ClikIT\InfiniteUploads\Tests\Unit
 */

declare( strict_types=1 );

namespace ClikIT\InfiniteUploads\Tests\Unit;

use Brain\Monkey\Functions;
use ClikIT\InfiniteUploads\Tests\TestCase;

class BbCachePhotoCroppedTest extends TestCase {

	/**
	 * @var string  Absolute path under WP_CONTENT_DIR/uploads/ where we drop
	 *              test cropped files.
	 */
	private $cache_dir;

	/**
	 * @var string  Absolute path of the uploads basedir.
	 */
	private $base_dir;

	protected function setUp(): void {
		parent::setUp();

		$this->cache_dir = WP_CONTENT_DIR . '/uploads/bb-plugin/cache';
		$this->base_dir  = WP_CONTENT_DIR . '/uploads';
		if ( ! is_dir( $this->cache_dir ) ) {
			mkdir( $this->cache_dir, 0755, true );
		}
		foreach ( glob( $this->cache_dir . '/*' ) as $f ) {
			@unlink( $f );
		}

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
				];
				return [ 'ext' => $ext, 'type' => $map[ $ext ] ?? 'application/octet-stream' ];
			}
		);
		Functions\when( 'infinite_uploads_enabled' )->justReturn( true );
		Functions\when( 'wp_next_scheduled' )->justReturn( false );
		Functions\when( 'wp_schedule_single_event' )->justReturn( true );
		Functions\when( 'wp_cache_delete' )->justReturn( true );

		// get_original_upload_dir_root chain (see BackfillTest for the same set).
		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
		Functions\when( 'is_multisite' )->justReturn( false );
		Functions\when( 'is_main_network' )->justReturn( true );
		Functions\when( 'is_main_site' )->justReturn( true );
		Functions\when( 'ms_is_switched' )->justReturn( false );
		Functions\when( 'apply_filters' )->returnArg( 2 );
		Functions\when( 'get_option' )->alias(
			static function ( $key, $default = false ) {
				return $default;
			}
		);
		Functions\when( 'get_site_option' )->alias(
			static function ( $key, $default = false ) {
				if ( $key === 'iup_excluded_files' ) {
					return [];
				}
				return $default;
			}
		);

		// Make sure InfiniteUploadsHelper state isn't leaking from another test.
		require_once IU_TESTS_ROOT . '/fixtures/ewww-environment.php';
		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsHelper.php';
		require IU_PLUGIN_ROOT . '/inc/bb-cache-integration.php';
	}

	protected function tearDown(): void {
		foreach ( glob( $this->cache_dir . '/*' ) as $f ) {
			@unlink( $f );
		}
		parent::tearDown();
	}

	private function write_cache_file( string $name ): string {
		$path = $this->cache_dir . '/' . $name;
		file_put_contents( $path, 'fake image bytes' );
		return $path;
	}

	// -------------------------------------------------------------------------
	// Happy path
	// -------------------------------------------------------------------------

	public function test_inserts_row_invalidates_cache_and_schedules_push(): void {
		$path = $this->write_cache_file( 'crop-300x200.jpg' );

		$wpdb       = $this->mock_wpdb();
		$query_sql  = '';
		$wpdb->shouldReceive( 'query' )->andReturnUsing(
			static function ( $sql ) use ( &$query_sql ) {
				$query_sql = $sql;
				return 1;
			}
		);

		$cache_invalidated = false;
		Functions\when( 'wp_cache_delete' )->alias(
			static function ( $key, $group ) use ( &$cache_invalidated ) {
				if ( $key === 'iu_bb_cache_synced_paths' && $group === 'infinite_uploads' ) {
					$cache_invalidated = true;
				}
				return true;
			}
		);

		$scheduled = [];
		Functions\when( 'wp_schedule_single_event' )->alias(
			static function ( $ts, $hook ) use ( &$scheduled ) {
				$scheduled[] = $hook;
				return true;
			}
		);

		\ClikIT\InfiniteUploads\infinite_uploads_bb_photo_cropped( [ 'path' => $path ] );

		$this->assertStringContainsString( 'INSERT INTO wp_infinite_uploads_files', $query_sql );
		$this->assertStringContainsString( 'crop-300x200.jpg', $query_sql );
		$this->assertStringContainsString( 'ON DUPLICATE KEY UPDATE', $query_sql );
		$this->assertTrue( $cache_invalidated, 'iu_bb_cache_synced_paths must be invalidated' );
		$this->assertContains( 'iu_bb_cache_push', $scheduled );
	}

	// -------------------------------------------------------------------------
	// Defensive input validation
	// -------------------------------------------------------------------------

	public function test_no_op_when_input_is_not_array(): void {
		$wpdb = $this->mock_wpdb();
		$wpdb->shouldNotReceive( 'query' );

		\ClikIT\InfiniteUploads\infinite_uploads_bb_photo_cropped( null );
		\ClikIT\InfiniteUploads\infinite_uploads_bb_photo_cropped( 'not-an-array' );
		\ClikIT\InfiniteUploads\infinite_uploads_bb_photo_cropped( 42 );

		$this->assertTrue( true ); // sentinel — Mockery enforces shouldNotReceive
	}

	public function test_no_op_when_path_key_missing_or_empty(): void {
		$wpdb = $this->mock_wpdb();
		$wpdb->shouldNotReceive( 'query' );

		\ClikIT\InfiniteUploads\infinite_uploads_bb_photo_cropped( [] );
		\ClikIT\InfiniteUploads\infinite_uploads_bb_photo_cropped( [ 'path' => '' ] );
		\ClikIT\InfiniteUploads\infinite_uploads_bb_photo_cropped( [ 'url' => 'http://x/y.jpg' ] );

		$this->assertTrue( true );
	}

	public function test_no_op_when_file_does_not_exist_on_disk(): void {
		$wpdb = $this->mock_wpdb();
		$wpdb->shouldNotReceive( 'query' );

		\ClikIT\InfiniteUploads\infinite_uploads_bb_photo_cropped( [
			'path' => $this->cache_dir . '/nonexistent.jpg',
		] );

		$this->assertTrue( true );
	}

	public function test_no_op_when_file_is_not_an_offloadable_image(): void {
		$path = $this->write_cache_file( 'layout.css' );

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldNotReceive( 'query' );

		\ClikIT\InfiniteUploads\infinite_uploads_bb_photo_cropped( [ 'path' => $path ] );

		$this->assertTrue( true );
	}

	public function test_no_op_when_iu_not_enabled(): void {
		Functions\when( 'infinite_uploads_enabled' )->justReturn( false );

		$path = $this->write_cache_file( 'crop.jpg' );

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldNotReceive( 'query' );

		\ClikIT\InfiniteUploads\infinite_uploads_bb_photo_cropped( [ 'path' => $path ] );

		$this->assertTrue( true );
	}

	public function test_no_op_when_path_is_outside_uploads_basedir(): void {
		// Write the file outside WP_CONTENT_DIR/uploads/. The handler
		// guards with strpos check — should not produce an INSERT.
		$elsewhere = sys_get_temp_dir() . '/iu-elsewhere-bb-plugin-cache';
		if ( ! is_dir( $elsewhere ) ) {
			mkdir( $elsewhere . '/bb-plugin/cache/', 0755, true );
		}
		$path = $elsewhere . '/bb-plugin/cache/crop.jpg';
		file_put_contents( $path, 'x' );

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldNotReceive( 'query' );

		\ClikIT\InfiniteUploads\infinite_uploads_bb_photo_cropped( [ 'path' => $path ] );

		// Clean up.
		@unlink( $path );
		@rmdir( $elsewhere . '/bb-plugin/cache' );
		@rmdir( $elsewhere . '/bb-plugin' );
		@rmdir( $elsewhere );

		$this->assertTrue( true );
	}

	// -------------------------------------------------------------------------
	// Idempotent re-scheduling
	// -------------------------------------------------------------------------

	public function test_does_not_double_schedule_when_event_already_pending(): void {
		// wp_next_scheduled returns truthy → schedule call must be skipped.
		Functions\when( 'wp_next_scheduled' )->justReturn( time() + 60 );

		$path = $this->write_cache_file( 'crop.jpg' );

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldReceive( 'query' )->andReturn( 1 );

		$schedule_calls = 0;
		Functions\when( 'wp_schedule_single_event' )->alias(
			static function ( $ts, $hook ) use ( &$schedule_calls ) {
				$schedule_calls++;
				return true;
			}
		);

		\ClikIT\InfiniteUploads\infinite_uploads_bb_photo_cropped( [ 'path' => $path ] );

		$this->assertSame(
			0,
			$schedule_calls,
			'wp_schedule_single_event must NOT be called when an iu_bb_cache_push is already queued'
		);
	}
}
