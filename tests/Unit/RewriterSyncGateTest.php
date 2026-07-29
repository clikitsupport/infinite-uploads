<?php
/**
 * Tests for InfiniteUploadsRewriter::is_bb_cache_synced — the BB cache image
 * sync gate added in 3.2.4. This is the safety net that prevents the rewriter
 * from pointing at a CDN URL for a BB cropped image that hasn't actually been
 * uploaded yet (avoids visitor-facing 404s during the offload window).
 *
 * The method is protected on a class with a heavy constructor (registers hooks,
 * instantiates Filelist, parses URLs). Tests use ReflectionClass to instantiate
 * without constructor and to invoke the protected method directly.
 *
 * @package ClikIT\InfiniteUploads\Tests\Unit
 */

declare( strict_types=1 );

namespace ClikIT\InfiniteUploads\Tests\Unit;

use Brain\Monkey\Functions;
use ClikIT\InfiniteUploads\InfiniteUploadsRewriter;
use ClikIT\InfiniteUploads\Tests\TestCase;
use ReflectionClass;

class RewriterSyncGateTest extends TestCase {

	/**
	 * @var InfiniteUploadsRewriter
	 */
	private $rewriter;

	/**
	 * @var ReflectionClass
	 */
	private $reflection;

	protected function setUp(): void {
		parent::setUp();

		// Avoid loading any WordPress-y dependency just to instantiate the Rewriter.
		// Heavy constructor side-effects (add_action, apply_filters, Filelist init)
		// are skipped by newInstanceWithoutConstructor.
		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsRewriter.php';

		$this->reflection = new ReflectionClass( InfiniteUploadsRewriter::class );
		$this->rewriter   = $this->reflection->newInstanceWithoutConstructor();

		// wp_cache stubs — no real persistent cache in unit tests.
		Functions\when( 'wp_cache_get' )->justReturn( false );
		Functions\when( 'wp_cache_set' )->justReturn( true );
		Functions\when( 'wp_cache_delete' )->justReturn( true );
	}

	/**
	 * Helper: invoke the protected is_bb_cache_synced method with a regex
	 * match array shaped like the live caller produces.
	 */
	private function invoke_sync_gate( array $matches ): bool {
		$method = $this->reflection->getMethod( 'is_bb_cache_synced' );
		$method->setAccessible( true );
		return $method->invoke( $this->rewriter, $matches );
	}

	/**
	 * Helper: load the protected bb_cache_synced cache property without
	 * triggering the DB-read path (by preseeding wp_cache).
	 */
	private function preseed_synced_cache( array $synced_relative_paths ): void {
		$prop = $this->reflection->getProperty( 'bb_cache_synced' );
		$prop->setAccessible( true );
		// `bb_cache_synced` stores a hashset keyed by leading-slash relative path.
		$prop->setValue( $this->rewriter, array_flip( $synced_relative_paths ) );
	}

	// -------------------------------------------------------------------------
	// Pre-loaded cache → no DB call
	// -------------------------------------------------------------------------

	public function test_returns_true_when_path_in_synced_cache(): void {
		$this->preseed_synced_cache( [ '/bb-plugin/cache/653-thumb-300x200.jpg' ] );

		$this->assertTrue(
			$this->invoke_sync_gate( [
				0 => 'https://example.test/wp-content/uploads/bb-plugin/cache/653-thumb-300x200.jpg',
				1 => 'https://example.test/wp-content/uploads/',
				2 => 'bb-plugin/cache/653-thumb-300x200.jpg',
			] )
		);
	}

	public function test_returns_false_when_path_not_in_synced_cache(): void {
		$this->preseed_synced_cache( [ '/bb-plugin/cache/already-synced.jpg' ] );

		$this->assertFalse(
			$this->invoke_sync_gate( [
				0 => 'https://example.test/wp-content/uploads/bb-plugin/cache/pending.jpg',
				1 => 'https://example.test/wp-content/uploads/',
				2 => 'bb-plugin/cache/pending.jpg',
			] )
		);
	}

	public function test_returns_false_when_cache_is_empty(): void {
		$this->preseed_synced_cache( [] );

		$this->assertFalse(
			$this->invoke_sync_gate( [
				0 => '',
				1 => '',
				2 => 'bb-plugin/cache/x.jpg',
			] )
		);
	}

	public function test_returns_false_when_relative_path_missing_from_match(): void {
		$this->preseed_synced_cache( [ '/bb-plugin/cache/x.jpg' ] );

		// No $matches[2] in the input → method should defensively return false.
		$this->assertFalse(
			$this->invoke_sync_gate( [
				0 => 'https://example.test/whatever',
				1 => 'https://example.test/',
			] )
		);
	}

	// -------------------------------------------------------------------------
	// Query string + fragment handling
	// -------------------------------------------------------------------------

	public function test_strips_query_string_before_lookup(): void {
		$this->preseed_synced_cache( [ '/bb-plugin/cache/x.jpg' ] );

		$this->assertTrue(
			$this->invoke_sync_gate( [
				0 => 'https://example.test/wp-content/uploads/bb-plugin/cache/x.jpg?ver=2',
				1 => 'https://example.test/wp-content/uploads/',
				2 => 'bb-plugin/cache/x.jpg?ver=2',
			] ),
			'Sync gate must compare path without query string'
		);
	}

	public function test_strips_fragment_before_lookup(): void {
		$this->preseed_synced_cache( [ '/bb-plugin/cache/x.jpg' ] );

		$this->assertTrue(
			$this->invoke_sync_gate( [
				0 => 'https://example.test/wp-content/uploads/bb-plugin/cache/x.jpg#anchor',
				1 => 'https://example.test/wp-content/uploads/',
				2 => 'bb-plugin/cache/x.jpg#anchor',
			] )
		);
	}

	// -------------------------------------------------------------------------
	// DB load path
	// -------------------------------------------------------------------------

	public function test_loads_from_db_when_cache_uninitialised(): void {
		// Don't preseed the property; let the method fall through to wpdb.
		$wpdb = $this->mock_wpdb( 'wp_' );
		$wpdb->shouldReceive( 'get_col' )
			->once()
			->andReturn( [
				'/bb-plugin/cache/uploaded.jpg',
				'/bb-plugin/cache/another.png',
			] );

		// First call populates the in-instance cache from DB results.
		$this->assertTrue(
			$this->invoke_sync_gate( [
				0 => '',
				1 => '',
				2 => 'bb-plugin/cache/uploaded.jpg',
			] )
		);

		// Second call must NOT re-query — the in-instance property is now set.
		// (Mock's ->once() expectation enforces this; an extra call would fail
		// the assertion.)
		$this->assertTrue(
			$this->invoke_sync_gate( [
				0 => '',
				1 => '',
				2 => 'bb-plugin/cache/another.png',
			] )
		);
	}

	public function test_db_load_uses_anchored_like_pattern(): void {
		$wpdb = $this->mock_wpdb( 'wp_' );
		$wpdb->shouldReceive( 'get_col' )
			->once()
			->with( \Mockery::on( static function ( $sql ) {
				// The 3.2.5 perf fix anchored the LIKE to use the PRIMARY KEY.
				// Regression-protect against accidental reintroduction of the
				// leading wildcard (full table scan).
				return strpos( $sql, "LIKE '/bb-plugin/cache/%'" ) !== false
					&& strpos( $sql, "LIKE '%/bb-plugin/cache/%'" ) === false;
			} ) )
			->andReturn( [] );

		// Empty DB result → method returns false. The real assertion under
		// test is the SQL-shape Mockery check above; this assertion just
		// exercises the code path and keeps PHPUnit from flagging the test
		// as risky.
		$this->assertFalse(
			$this->invoke_sync_gate( [
				0 => '',
				1 => '',
				2 => 'bb-plugin/cache/x.jpg',
			] )
		);
	}

	public function test_uses_persistent_object_cache_when_available(): void {
		// Persistent cache returns a hit → no DB query expected.
		Functions\when( 'wp_cache_get' )->justReturn(
			[ '/bb-plugin/cache/cached-hit.jpg' => 0 ]
		);
		$wpdb = $this->mock_wpdb( 'wp_' );
		$wpdb->shouldNotReceive( 'get_col' );

		$this->assertTrue(
			$this->invoke_sync_gate( [
				0 => '',
				1 => '',
				2 => 'bb-plugin/cache/cached-hit.jpg',
			] )
		);
	}
}
