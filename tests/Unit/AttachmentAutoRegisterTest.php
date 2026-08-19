<?php
/**
 * Tests for the auto-registration of newly-created WordPress attachments
 * into the sync table (added to close support ticket #11637's failure mode:
 * uploads that land locally between "connect" and "enable CDN" never got
 * synced because the sync table was populated from an earlier scan
 * snapshot and no `add_attachment` hook existed to add newcomers).
 *
 * Also covers the `InfiniteUploadsFilelist::MODE_RECONCILE` addition —
 * the same class runs in two modes now, and MODE_RECONCILE must never
 * TRUNCATE the sync table (that would destroy sync progress on every
 * cron tick).
 *
 * @package ClikIT\InfiniteUploads\Tests\Unit
 */

declare( strict_types=1 );

namespace ClikIT\InfiniteUploads\Tests\Unit;

use Brain\Monkey\Functions;
use ClikIT\InfiniteUploads\InfiniteUploads;
use ClikIT\InfiniteUploads\InfiniteUploadsFilelist;
use ClikIT\InfiniteUploads\Tests\TestCase;
use Mockery;
use ReflectionClass;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 * Runs each case in an isolated PHP process because the ewww fixture
 * (tests/fixtures/ewww-environment.php) declares a STUB InfiniteUploads
 * class that conflicts with the real one we need here. Process isolation
 * avoids the collision without touching the ewww suite.
 */
class AttachmentAutoRegisterTest extends TestCase {

	/**
	 * @var InfiniteUploads
	 */
	private $iup;

	/**
	 * @var ReflectionClass<InfiniteUploads>
	 */
	private $reflection;

	/**
	 * @var Mockery\MockInterface
	 */
	private $wpdb;

	/**
	 * @var string Absolute path to a real temp basedir used to simulate uploads/.
	 */
	private $basedir;

	protected function setUp(): void {
		parent::setUp();

		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploads.php';

		$this->reflection = new ReflectionClass( InfiniteUploads::class );
		$this->iup        = $this->reflection->newInstanceWithoutConstructor();

		$this->wpdb = $this->mock_wpdb();

		// Place the temp basedir UNDER IU_PLUGIN_ROOT so it matches the
		// bootstrap's ABSPATH — otherwise get_original_upload_dir_root's
		// path_join(ABSPATH, upload_path) branch would rewrite it and the
		// files we touch below would live at a different path than the
		// production code resolves to.
		$this->basedir = IU_PLUGIN_ROOT . '/.tests-tmp-attach-' . uniqid();
		mkdir( $this->basedir, 0777, true );

		// Point get_original_upload_dir_root() at our temp dir. It reads
		// from WP options, so stub those.
		Functions\when( 'get_option' )->alias( function ( $name, $default = false ) {
			if ( 'upload_path' === $name ) {
				// Absolute path; get_original_upload_dir_root's own logic
				// preserves it verbatim in the "else" branch.
				return $this->basedir;
			}
			if ( 'upload_url_path' === $name ) {
				return 'https://example.test/uploads';
			}
			if ( 'siteurl' === $name ) {
				return 'https://example.test';
			}
			return $default;
		} );

		// path_join is a WP function; use its behaviour.
		Functions\when( 'path_join' )->alias( function ( $base, $path ) {
			return rtrim( (string) $base, '/' ) . '/' . ltrim( (string) $path, '/' );
		} );
		Functions\when( 'trailingslashit' )->alias( function ( $s ) {
			return rtrim( (string) $s, '/' ) . '/';
		} );
		Functions\when( 'untrailingslashit' )->alias( function ( $s ) {
			return rtrim( (string) $s, '/' );
		} );
		Functions\when( 'is_multisite' )->justReturn( false );
		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
		Functions\when( 'wp_check_filetype' )->alias( function ( $file ) {
			return [ 'type' => 'image/png', 'ext' => 'png' ];
		} );
		Functions\when( 'get_post_meta' )->alias( function ( $id, $key, $single = false ) {
			return null;
		} );
	}

	protected function tearDown(): void {
		$this->rrmdir( $this->basedir );
		parent::tearDown();
	}

	// ---------------------------------------------------------------------
	// sync_register_local_files — the core INSERT helper.
	// ---------------------------------------------------------------------

	public function test_inserts_file_that_exists_locally(): void {
		mkdir( $this->basedir . '/2026/07', 0777, true );
		file_put_contents( $this->basedir . '/2026/07/foo.png', 'x' );

		$this->wpdb->shouldReceive( 'query' )
			->once()
			->with( Mockery::pattern( '/INSERT INTO.*infinite_uploads_files.*ON DUPLICATE KEY UPDATE/s' ) );

		$this->invoke_private( 'sync_register_local_files', [ [ '/2026/07/foo.png' ] ] );
		$this->assertTrue( true, 'once()-expectation is verified in tearDown' );
	}

	public function test_skips_file_that_does_not_exist_locally(): void {
		// Do NOT create /2026/07/foo.png — simulates the iu://-only case
		// where cloud mode is enabled and the upload never touched local disk.
		$this->wpdb->shouldReceive( 'query' )->never();

		$this->invoke_private( 'sync_register_local_files', [ [ '/2026/07/foo.png' ] ] );
		// Mockery::close() (via tearDown) verifies the ->never() expectation;
		// add an explicit assertion so PHPUnit doesn't flag this as "no
		// assertions" (risky).
		$this->assertTrue( true, 'never()-expectation is verified in tearDown' );
	}

	public function test_bulk_inserts_multiple_files_in_one_query(): void {
		mkdir( $this->basedir . '/2026/07', 0777, true );
		file_put_contents( $this->basedir . '/2026/07/a.png', 'a' );
		file_put_contents( $this->basedir . '/2026/07/b.png', 'b' );
		file_put_contents( $this->basedir . '/2026/07/c.png', 'c' );

		$this->wpdb->shouldReceive( 'query' )
			->once()
			->with( Mockery::pattern( '#a\.png.+b\.png.+c\.png#s' ) );

		$this->invoke_private( 'sync_register_local_files', [ [
			'/2026/07/a.png',
			'/2026/07/b.png',
			'/2026/07/c.png',
		] ] );
		$this->assertTrue( true, 'once()-expectation is verified in tearDown' );
	}

	public function test_deduplicates_repeated_paths(): void {
		mkdir( $this->basedir . '/2026/07', 0777, true );
		file_put_contents( $this->basedir . '/2026/07/a.png', 'a' );

		$captured = null;
		$this->wpdb->shouldReceive( 'query' )
			->once()
			->with( Mockery::on( function ( $q ) use ( &$captured ) {
				$captured = $q;
				return true;
			} ) );

		$this->invoke_private( 'sync_register_local_files', [ [
			'/2026/07/a.png',
			'/2026/07/a.png',
		] ] );

		// Only one VALUES row for a.png even though we passed it twice.
		$this->assertSame( 1, substr_count( (string) $captured, 'a.png' ) );
	}

	public function test_no_query_when_paths_empty(): void {
		$this->wpdb->shouldReceive( 'query' )->never();
		$this->invoke_private( 'sync_register_local_files', [ [] ] );
		$this->assertTrue( true, 'never()-expectation is verified in tearDown' );
	}

	// ---------------------------------------------------------------------
	// register_attachment_metadata_for_sync — returns metadata untouched
	// and registers every file WP generated (main + sub-sizes).
	// ---------------------------------------------------------------------

	public function test_metadata_hook_registers_main_and_subsizes(): void {
		mkdir( $this->basedir . '/2026/07', 0777, true );
		file_put_contents( $this->basedir . '/2026/07/img.png', 'x' );
		file_put_contents( $this->basedir . '/2026/07/img-150x150.png', 'x' );
		file_put_contents( $this->basedir . '/2026/07/img-300x300.png', 'x' );

		$captured = null;
		$this->wpdb->shouldReceive( 'query' )
			->once()
			->with( Mockery::on( function ( $q ) use ( &$captured ) {
				$captured = (string) $q;
				return true;
			} ) );

		$metadata = [
			'file'  => '2026/07/img.png',
			'sizes' => [
				'thumbnail' => [ 'file' => 'img-150x150.png' ],
				'medium'    => [ 'file' => 'img-300x300.png' ],
			],
		];

		$returned = $this->iup->register_attachment_metadata_for_sync( $metadata, 42 );

		// Filter must return the metadata unchanged.
		$this->assertSame( $metadata, $returned );

		// Every file appears in the INSERT — main and both sub-sizes.
		$this->assertStringContainsString( '2026/07/img.png', (string) $captured );
		$this->assertStringContainsString( 'img-150x150.png', (string) $captured );
		$this->assertStringContainsString( 'img-300x300.png', (string) $captured );
	}

	public function test_metadata_hook_returns_untouched_on_empty(): void {
		$this->wpdb->shouldReceive( 'query' )->never();

		$this->assertSame( [], $this->iup->register_attachment_metadata_for_sync( [], 42 ) );
		$this->assertSame( null, $this->iup->register_attachment_metadata_for_sync( null, 42 ) );
	}

	// ---------------------------------------------------------------------
	// Filelist MODE_RECONCILE — must NOT truncate the sync table.
	// ---------------------------------------------------------------------

	public function test_filelist_reconcile_mode_does_not_truncate(): void {
		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsFilelist.php';

		// The filelist constructor calls InfiniteUploads::get_instance(). We
		// bypass by using newInstanceWithoutConstructor and setting fields
		// directly via reflection.
		$refl     = new ReflectionClass( InfiniteUploadsFilelist::class );
		$filelist = $refl->newInstanceWithoutConstructor();

		$mode = $refl->getProperty( 'mode' );
		$mode->setAccessible( true );
		$mode->setValue( $filelist, InfiniteUploadsFilelist::MODE_RECONCILE );

		$paths_left = $refl->getProperty( 'paths_left' );
		$paths_left->setAccessible( true );
		$paths_left->setValue( $filelist, [] );

		$root_path = $refl->getProperty( 'root_path' );
		$root_path->setAccessible( true );
		$root_path->setValue( $filelist, $this->basedir );

		// Sanity: MODE_RECONCILE must NOT issue TRUNCATE or DELETE against
		// the sync table. If it does, the query mock records it and this
		// assertion fires (Mockery is strict by default with shouldReceive).
		$this->wpdb->shouldReceive( 'query' )
			->with( Mockery::pattern( '/TRUNCATE|DELETE FROM/i' ) )
			->never();

		// flush_to_db's own INSERT may or may not fire depending on files
		// present — accept either.
		$this->wpdb->shouldReceive( 'query' )->zeroOrMoreTimes();
		$this->wpdb->shouldReceive( 'prepare' )->zeroOrMoreTimes()->andReturnUsing(
			function ( $q, ...$args ) {
				return $q;
			}
		);

		Functions\when( 'apply_filters' )->returnArg( 2 );
		Functions\when( 'get_site_option' )->justReturn( [] );
		Functions\when( 'update_site_option' )->justReturn( true );

		$filelist->start();
		$this->assertTrue( true, 'never()-expectation is verified in tearDown' );
	}

	public function test_filelist_scan_mode_still_truncates(): void {
		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsFilelist.php';

		$refl     = new ReflectionClass( InfiniteUploadsFilelist::class );
		$filelist = $refl->newInstanceWithoutConstructor();

		$mode = $refl->getProperty( 'mode' );
		$mode->setAccessible( true );
		$mode->setValue( $filelist, InfiniteUploadsFilelist::MODE_SCAN );

		$paths_left = $refl->getProperty( 'paths_left' );
		$paths_left->setAccessible( true );
		$paths_left->setValue( $filelist, [] );

		$root_path = $refl->getProperty( 'root_path' );
		$root_path->setAccessible( true );
		$root_path->setValue( $filelist, $this->basedir );

		// SCAN mode issues TRUNCATE (or DELETE fallback) exactly once
		// at the start of a fresh run.
		$this->wpdb->shouldReceive( 'query' )
			->with( Mockery::pattern( '/TRUNCATE.*infinite_uploads_files/i' ) )
			->once()
			->andReturn( 1 );
		$this->wpdb->shouldReceive( 'query' )->zeroOrMoreTimes();
		$this->wpdb->shouldReceive( 'prepare' )->zeroOrMoreTimes()->andReturnUsing(
			function ( $q, ...$args ) {
				return $q;
			}
		);

		Functions\when( 'apply_filters' )->returnArg( 2 );
		Functions\when( 'get_site_option' )->justReturn( [] );
		Functions\when( 'update_site_option' )->justReturn( true );

		$filelist->start();
		$this->assertTrue( true, 'once()-expectation is verified in tearDown' );
	}

	// ---------------------------------------------------------------------
	// Helpers
	// ---------------------------------------------------------------------

	private function invoke_private( string $name, array $args ) {
		$method = $this->reflection->getMethod( $name );
		$method->setAccessible( true );
		return $method->invokeArgs( $this->iup, $args );
	}

	private function rrmdir( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		foreach ( scandir( $dir ) as $item ) {
			if ( $item === '.' || $item === '..' ) {
				continue;
			}
			$p = $dir . '/' . $item;
			is_dir( $p ) ? $this->rrmdir( $p ) : @unlink( $p );
		}
		@rmdir( $dir );
	}
}
