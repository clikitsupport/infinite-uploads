<?php
/**
 * Tests for the continuous-iterator sync/download Generators added to fix
 * the batch-barrier throughput collapse (previously the Transfer manager
 * drained its concurrency pool to zero between fixed 12MB batches, and the
 * deadline was only checked AFTER a batch completed — measurably
 * overshooting by 30+ seconds mid-multipart).
 *
 * Both build_sync_upload_iterator() and build_download_iterator() are
 * private, so tests use ReflectionMethod to invoke them and consume the
 * yielded Generator with iterator_to_array().
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
use stdClass;

class SyncIteratorTest extends TestCase {

	/**
	 * @var InfiniteUploadsAdmin
	 */
	private $admin;

	/**
	 * @var ReflectionClass<InfiniteUploadsAdmin>
	 */
	private $reflection;

	/**
	 * @var Mockery\MockInterface
	 */
	private $wpdb;

	/**
	 * @var string  Absolute path to a real temp directory used as basedir for
	 *              path-validation tests. Cleaned up in tearDown.
	 */
	private $basedir;

	protected function setUp(): void {
		parent::setUp();

		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsAdmin.php';

		if ( ! defined( 'INFINITE_UPLOADS_SYNC_ITERATOR_PAGE_SIZE' ) ) {
			define( 'INFINITE_UPLOADS_SYNC_ITERATOR_PAGE_SIZE', 200 );
		}

		$this->reflection = new ReflectionClass( InfiniteUploadsAdmin::class );
		$this->admin      = $this->reflection->newInstanceWithoutConstructor();

		$stub_iup         = new stdClass();
		$stub_iup->bucket = 'iup-test-bucket/';
		$iup_prop         = $this->reflection->getProperty( 'iup_instance' );
		$iup_prop->setAccessible( true );
		$iup_prop->setValue( $this->admin, $stub_iup );

		$this->wpdb = $this->mock_wpdb();

		// A real, disposable basedir so realpath() (a PHP built-in that
		// cannot be stubbed) succeeds for the valid-file cases. Individual
		// tests touch() files inside it as needed.
		$this->basedir = sys_get_temp_dir() . '/iu-iterator-test-' . uniqid();
		mkdir( $this->basedir, 0777, true );

		// WP function stubs used by the iterators.
		Functions\when( 'esc_html' )->returnArg();
		Functions\when( 'esc_html__' )->returnArg();
		Functions\when( 'untrailingslashit' )->alias( function ( $s ) {
			return rtrim( (string) $s, '/' );
		} );
	}

	protected function tearDown(): void {
		$this->rrmdir( $this->basedir );
		parent::tearDown();
	}

	// ---------------------------------------------------------------------
	// build_sync_upload_iterator
	// ---------------------------------------------------------------------

	public function test_upload_iterator_yields_nothing_when_queue_is_empty(): void {
		Functions\when( 'timer_stop' )->justReturn( 0.5 );

		$this->wpdb->shouldReceive( 'get_results' )->once()->andReturn( [] );
		$this->wpdb->shouldReceive( 'query' )->never();

		$yielded = $this->collect_upload_iterator( 60 );

		$this->assertSame( [], $yielded );
	}

	public function test_upload_iterator_stops_before_first_yield_when_deadline_already_past(): void {
		Functions\when( 'timer_stop' )->justReturn( 999.0 );

		// Deadline check runs BEFORE the SQL, so no query should fire.
		$this->wpdb->shouldReceive( 'get_results' )->never();

		$yielded = $this->collect_upload_iterator( 60 );

		$this->assertSame( [], $yielded );
	}

	public function test_upload_iterator_yields_valid_files_and_preincrements_errors(): void {
		Functions\when( 'timer_stop' )->justReturn( 0.5 );

		// Create three real files so realpath() succeeds and the
		// directory-traversal guard passes.
		mkdir( $this->basedir . '/2026', 0777, true );
		file_put_contents( $this->basedir . '/2026/a.jpg', 'a' );
		file_put_contents( $this->basedir . '/2026/b.jpg', 'b' );
		file_put_contents( $this->basedir . '/2026/c.jpg', 'c' );

		$this->wpdb->shouldReceive( 'get_results' )
			->once()
			->andReturn( [
				(object) [ 'file' => '/2026/a.jpg', 'size' => 100 ],
				(object) [ 'file' => '/2026/b.jpg', 'size' => 200 ],
				(object) [ 'file' => '/2026/c.jpg', 'size' => 300 ],
			] );
		// Second refresh returns empty — end of queue.
		$this->wpdb->shouldReceive( 'get_results' )->once()->andReturn( [] );

		// One error-preincrement per yielded file.
		$this->wpdb->shouldReceive( 'query' )
			->times( 3 )
			->with( Mockery::pattern( '/SET errors = \( errors \+ 1 \)/' ) );

		$yielded = $this->collect_upload_iterator( 60 );

		$this->assertSame(
			[
				$this->basedir . '/2026/a.jpg',
				$this->basedir . '/2026/b.jpg',
				$this->basedir . '/2026/c.jpg',
			],
			$yielded
		);
	}

	public function test_upload_iterator_skips_files_failing_path_validation_without_yielding(): void {
		Functions\when( 'timer_stop' )->justReturn( 0.5 );

		// Do NOT create a file for /2026/missing.jpg — realpath() will
		// return false and the iterator should skip it (no yield, no
		// error-preincrement query for that row) but still mark it as
		// "yielded" so it's excluded from the next query.
		mkdir( $this->basedir . '/2026', 0777, true );
		file_put_contents( $this->basedir . '/2026/valid.jpg', 'valid' );

		$this->wpdb->shouldReceive( 'get_results' )
			->once()
			->andReturn( [
				(object) [ 'file' => '/2026/missing.jpg', 'size' => 100 ],
				(object) [ 'file' => '/2026/valid.jpg', 'size' => 200 ],
			] );
		$this->wpdb->shouldReceive( 'get_results' )->once()->andReturn( [] );

		// Only the VALID file gets pre-incremented.
		$this->wpdb->shouldReceive( 'query' )
			->once()
			->with( Mockery::pattern( '/valid\.jpg/' ) );

		$yielded = $this->collect_upload_iterator( 60 );

		$this->assertSame( [ $this->basedir . '/2026/valid.jpg' ], $yielded );
	}

	public function test_upload_iterator_stops_mid_page_when_deadline_arrives(): void {
		mkdir( $this->basedir . '/2026', 0777, true );
		file_put_contents( $this->basedir . '/2026/a.jpg', 'a' );
		file_put_contents( $this->basedir . '/2026/b.jpg', 'b' );
		file_put_contents( $this->basedir . '/2026/c.jpg', 'c' );

		// Return elapsed times so the second yield's deadline check trips.
		// Sequence: [top-of-loop, before-a, before-b, before-c...]. The
		// generator's flow is: check deadline → query → for each row
		// { check deadline → validate → increment → yield }.
		// We want the FIRST yield to succeed and the second to bail.
		$call_count = 0;
		Functions\when( 'timer_stop' )->alias( function () use ( &$call_count ) {
			$call_count ++;
			// Elapsed grows: 1s, 2s, 3s, 999s (trip on 4th call)
			$sequence = [ 1.0, 2.0, 999.0 ];
			return $sequence[ min( $call_count - 1, count( $sequence ) - 1 ) ];
		} );

		$this->wpdb->shouldReceive( 'get_results' )
			->once()
			->andReturn( [
				(object) [ 'file' => '/2026/a.jpg', 'size' => 100 ],
				(object) [ 'file' => '/2026/b.jpg', 'size' => 200 ],
				(object) [ 'file' => '/2026/c.jpg', 'size' => 300 ],
			] );

		$this->wpdb->shouldReceive( 'query' )->atLeast()->once();

		$yielded = $this->collect_upload_iterator( 60 );

		// The iterator should stop somewhere BEFORE consuming all three.
		// Under the sequence above, first yield happens at elapsed=2s
		// (inside the row loop's own timer check), then the second check
		// sees 999s and bails.
		$this->assertLessThan( 3, count( $yielded ) );
		$this->assertGreaterThanOrEqual( 1, count( $yielded ) );
	}

	// ---------------------------------------------------------------------
	// build_download_iterator
	// ---------------------------------------------------------------------

	public function test_download_iterator_yields_nothing_when_queue_is_empty(): void {
		Functions\when( 'timer_stop' )->justReturn( 0.5 );

		$this->wpdb->shouldReceive( 'get_results' )->once()->andReturn( [] );
		$this->wpdb->shouldReceive( 'query' )->never();

		$errors = [];
		$yielded = $this->collect_download_iterator( 60, $errors );

		$this->assertSame( [], $yielded );
		$this->assertSame( [], $errors );
	}

	public function test_download_iterator_yields_s3_uris_and_preincrements_errors(): void {
		Functions\when( 'timer_stop' )->justReturn( 0.5 );

		$this->wpdb->shouldReceive( 'get_results' )
			->once()
			->andReturn( [
				(object) [ 'file' => '/2026/a.jpg', 'size' => 100 ],
				(object) [ 'file' => '/2026/b.jpg', 'size' => 200 ],
			] );
		$this->wpdb->shouldReceive( 'get_results' )->once()->andReturn( [] );

		$this->wpdb->shouldReceive( 'query' )
			->times( 2 )
			->with( Mockery::pattern( '/SET errors = \( errors \+ 1 \)/' ) );

		$errors  = [];
		$yielded = $this->collect_download_iterator( 60, $errors );

		$this->assertSame(
			[
				's3://iup-test-bucket/2026/a.jpg',
				's3://iup-test-bucket/2026/b.jpg',
			],
			$yielded
		);
	}

	public function test_download_iterator_bails_with_error_when_basedir_unresolvable(): void {
		Functions\when( 'timer_stop' )->justReturn( 0.5 );

		$path = [ 'basedir' => '/nonexistent/path/under/test/' . uniqid() ];

		$method = $this->reflection->getMethod( 'build_download_iterator' );
		$method->setAccessible( true );

		$errors    = [];
		$generator = $method->invokeArgs(
			$this->admin,
			[ $this->wpdb, 'iup-test-bucket/', $path, 60, &$errors ]
		);

		$yielded = iterator_to_array( $generator, false );

		$this->assertSame( [], $yielded );
		$this->assertNotEmpty( $errors );
		$this->assertStringContainsString( 'base directory', $errors[0] );
	}

	// ---------------------------------------------------------------------
	// Test helpers
	// ---------------------------------------------------------------------

	/**
	 * Invoke build_sync_upload_iterator and materialise the generator.
	 *
	 * @param  int $deadline
	 *
	 * @return array<string>
	 */
	private function collect_upload_iterator( int $deadline ): array {
		$method = $this->reflection->getMethod( 'build_sync_upload_iterator' );
		$method->setAccessible( true );

		$path      = [ 'basedir' => $this->basedir ];
		$generator = $method->invokeArgs( $this->admin, [ $this->wpdb, $path, $deadline ] );

		return iterator_to_array( $generator, false );
	}

	/**
	 * Invoke build_download_iterator and materialise the generator.
	 *
	 * @param  int    $deadline
	 * @param  array &$errors
	 *
	 * @return array<string>
	 */
	private function collect_download_iterator( int $deadline, array &$errors ): array {
		$method = $this->reflection->getMethod( 'build_download_iterator' );
		$method->setAccessible( true );

		$path      = [ 'basedir' => $this->basedir ];
		$generator = $method->invokeArgs(
			$this->admin,
			[ $this->wpdb, 'iup-test-bucket/', $path, $deadline, &$errors ]
		);

		return iterator_to_array( $generator, false );
	}

	private function rrmdir( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		$items = scandir( $dir );
		foreach ( $items as $item ) {
			if ( $item === '.' || $item === '..' ) {
				continue;
			}
			$path = $dir . '/' . $item;
			if ( is_dir( $path ) ) {
				$this->rrmdir( $path );
			} else {
				@unlink( $path );
			}
		}
		@rmdir( $dir );
	}
}
