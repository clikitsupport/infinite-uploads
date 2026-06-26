<?php
/**
 * Tests for the Rewriter constructor — specifically the Smush URL fix-pair
 * construction logic added in 3.2.2.
 *
 * The constructor builds a map of "broken Smush URL" → "corrected URL"
 * pairs based on the shape of the configured CDN URL:
 *   - Vanity host (https://tenant.infiniteuploads.cloud) — `dirname()`
 *     degenerates to "https:", producing junk URLs like "https:/smush-webp/...".
 *   - Path-style CDN (https://cdn/user_id/site_id) — `dirname()` strips
 *     site_id, producing the wrong base path for Smush.
 *
 * Both shapes need their own fix pair so the rewriter can str_replace the
 * malformed URL back to a correct CDN URL.
 *
 * @package ClikIT\InfiniteUploads\Tests\Unit
 */

declare( strict_types=1 );

namespace ClikIT\InfiniteUploads\Tests\Unit;

use Brain\Monkey\Functions;
use ClikIT\InfiniteUploads\InfiniteUploadsRewriter;
use ClikIT\InfiniteUploads\Tests\TestCase;
use ReflectionClass;

require_once IU_TESTS_ROOT . '/fixtures/ewww-environment.php';

class RewriterConstructorTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsHelper.php';
		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsFilelist.php';
		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsRewriter.php';

		Functions\when( 'trailingslashit' )->alias(
			static function ( $s ) {
				return rtrim( (string) $s, '/' ) . '/';
			}
		);
		Functions\when( 'untrailingslashit' )->alias(
			static function ( $s ) {
				return rtrim( (string) $s, '/' );
			}
		);
		Functions\when( 'wp_parse_url' )->alias(
			static function ( $url, $component = -1 ) {
				return \parse_url( $url, $component );
			}
		);
		// Filelist constructor expects an InfiniteUploads instance to be
		// available — fixture provides it.
		Functions\when( 'apply_filters' )->returnArg( 2 );
		Functions\when( 'add_action' )->justReturn( true );
		Functions\when( 'add_filter' )->justReturn( true );
		Functions\when( 'wp_cache_get' )->justReturn( false );
		Functions\when( 'wp_cache_set' )->justReturn( true );
	}

	/**
	 * @return array  smush_url_fix_pairs property of a freshly-constructed
	 *                Rewriter with the given CDN URL.
	 */
	private function build_pairs_for_cdn( string $cdn_url ): array {
		$rewriter = new InfiniteUploadsRewriter(
			'http://example.test/wp-content/uploads',
			[ 'http://example.test/wp-content/uploads' ],
			$cdn_url
		);

		$ref  = new ReflectionClass( InfiniteUploadsRewriter::class );
		$prop = $ref->getProperty( 'smush_url_fix_pairs' );
		$prop->setAccessible( true );
		return $prop->getValue( $rewriter );
	}

	// -------------------------------------------------------------------------
	// Vanity host (no path) — degenerate scheme: case
	// -------------------------------------------------------------------------

	public function test_vanity_host_produces_https_degenerate_fix_pairs(): void {
		$pairs = $this->build_pairs_for_cdn( 'https://tenant.infiniteuploads.cloud' );

		// Two pairs expected (one for smush-webp, one for smush-avif), both
		// fixing the degenerate "https:/smush-{webp,avif}/" form.
		$this->assertArrayHasKey( 'https:/smush-webp/', $pairs );
		$this->assertSame(
			'https://tenant.infiniteuploads.cloud/smush-webp/',
			$pairs['https:/smush-webp/']
		);

		$this->assertArrayHasKey( 'https:/smush-avif/', $pairs );
		$this->assertSame(
			'https://tenant.infiniteuploads.cloud/smush-avif/',
			$pairs['https:/smush-avif/']
		);
	}

	public function test_vanity_host_with_trailing_slash_normalised(): void {
		$pairs = $this->build_pairs_for_cdn( 'https://tenant.infiniteuploads.cloud/' );

		$this->assertSame(
			'https://tenant.infiniteuploads.cloud/smush-webp/',
			$pairs['https:/smush-webp/']
		);
	}

	public function test_http_scheme_vanity_host_produces_http_degenerate(): void {
		$pairs = $this->build_pairs_for_cdn( 'http://tenant.infiniteuploads.cloud' );

		$this->assertArrayHasKey( 'http:/smush-webp/', $pairs );
		$this->assertSame(
			'http://tenant.infiniteuploads.cloud/smush-webp/',
			$pairs['http:/smush-webp/']
		);
	}

	// -------------------------------------------------------------------------
	// Path-style CDN — dirname strips the trailing path segment
	// -------------------------------------------------------------------------

	public function test_path_style_cdn_produces_dirname_strip_fix_pairs(): void {
		$pairs = $this->build_pairs_for_cdn( 'https://cdn.example.test/user_id/site_id' );

		// Case 1 pair: dirname strips 'site_id' from the CDN URL, leaving
		// 'https://cdn.example.test/user_id'. Smush appends '/smush-webp/'
		// to that → bad URL has user_id but not site_id.
		$this->assertArrayHasKey(
			'https://cdn.example.test/user_id/smush-webp/',
			$pairs,
			'path-style CDN should produce a fix pair for the dirname-stripped form'
		);
		$this->assertSame(
			'https://cdn.example.test/user_id/site_id/smush-webp/',
			$pairs['https://cdn.example.test/user_id/smush-webp/']
		);
	}

	public function test_path_style_cdn_also_produces_degenerate_pair(): void {
		$pairs = $this->build_pairs_for_cdn( 'https://cdn.example.test/user_id/site_id' );

		// Path-style URLs ALSO get the degenerate pair because the constructor
		// adds it whenever scheme+host are both present (defensive — some PHP
		// builds may emit the degenerate form too).
		$this->assertArrayHasKey( 'https:/smush-webp/', $pairs );
	}

	// -------------------------------------------------------------------------
	// Edge case: same-as-good fix pair should not be registered
	// -------------------------------------------------------------------------

	public function test_does_not_register_identity_fix_pair(): void {
		// If a future code path produced bad == good, we'd recursively
		// str_replace into the same string. The constructor guards with
		// `if ( $bad !== $good )` — verify no such entries exist.
		$pairs = $this->build_pairs_for_cdn( 'https://tenant.infiniteuploads.cloud' );

		foreach ( $pairs as $bad => $good ) {
			$this->assertNotSame( $bad, $good, "Fix pair must not be identity: {$bad}" );
		}
	}
}
