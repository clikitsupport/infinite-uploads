<?php
/**
 * Tests for InfiniteUploadsRewriter core URL replacement behaviour.
 *
 * Covers:
 *   - protocolize_url()         — protocol prefixing and trailing-slash normalisation
 *   - relative_url()            — full URL → protocol-relative form
 *   - rewrite()                 — full HTML rewriting pass (regex pipeline)
 *   - Smush URL fix pairs       — string replacement of malformed Smush URLs
 *   - rewrite_the_content()     — wp content filter wrapper
 *
 * The Rewriter's constructor is heavy (instantiates Filelist, calls
 * apply_filters, registers hooks). Tests build instances via
 * newInstanceWithoutConstructor() and inject only the properties the test
 * cares about.
 *
 * @package ClikIT\InfiniteUploads\Tests\Unit
 */

declare( strict_types=1 );

namespace ClikIT\InfiniteUploads\Tests\Unit;

use Brain\Monkey\Functions;
use ClikIT\InfiniteUploads\InfiniteUploadsRewriter;
use ClikIT\InfiniteUploads\Tests\TestCase;
use ReflectionClass;

// Minimal WP_REST_Response stub used by the REST-filter tests. Defined
// here (in this namespace) and then aliased into the global namespace so
// rewrite_rest_attachment's `$response instanceof \WP_REST_Response` check
// passes against our doubles.
class _RewriterTestRestResponse {
	private $data;

	public function set_data( $data ): void {
		$this->data = $data;
	}

	public function get_data() {
		return $this->data;
	}
}

if ( ! class_exists( '\WP_REST_Response' ) ) {
	class_alias( __NAMESPACE__ . '\\_RewriterTestRestResponse', 'WP_REST_Response' );
}

class RewriterTest extends TestCase {

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
		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsRewriter.php';

		$this->reflection = new ReflectionClass( InfiniteUploadsRewriter::class );
		$this->rewriter   = $this->reflection->newInstanceWithoutConstructor();

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
		Functions\when( 'apply_filters' )->returnArg( 2 );
		Functions\when( 'wp_cache_get' )->justReturn( false );
		Functions\when( 'wp_cache_set' )->justReturn( true );
		Functions\when( 'wp_cache_delete' )->justReturn( true );
		// wp_json_encode in WP wraps PHP's json_encode (which escapes slashes
		// by default — '/'  → '\/'). The production rewrite_rest_attachment
		// loop uses str_replace on the encoded JSON string, so the unescaped
		// form of slashes is what's actually searchable. Using
		// JSON_UNESCAPED_SLASHES here keeps the encoded string searchable for
		// `https:/smush-webp/` style patterns.
		Functions\when( 'wp_json_encode' )->alias(
			static function ( $data ) {
				return json_encode( $data, JSON_UNESCAPED_SLASHES );
			}
		);
		// File exclusion check inside rewrite_url calls
		// InfiniteUploadsHelper::is_file_exclusion_enabled → get_site_option.
		Functions\when( 'get_site_option' )->justReturn( 'no' );
	}

	/**
	 * Set a protected/private property on the test rewriter.
	 */
	private function set_prop( string $name, $value ): void {
		$prop = $this->reflection->getProperty( $name );
		$prop->setAccessible( true );
		$prop->setValue( $this->rewriter, $value );
	}

	/**
	 * Configure the rewriter to rewrite //example.test/wp-content/uploads/...
	 * URLs to https://cdn.example.test/.
	 */
	private function configure_for_rewriting(): void {
		$this->set_prop( 'uploads_path', '/wp-content/uploads/' );
		$this->set_prop( 'replacements', [ '//example.test/wp-content/uploads/' ] );
		$this->set_prop( 'cdn_url', 'https://cdn.example.test/' );
		$this->set_prop( 'exclusions', [] );
		$this->set_prop( 'smush_url_fix_pairs', [] );
		$this->set_prop( 'bb_cache_synced', [] );
	}

	// -------------------------------------------------------------------------
	// protocolize_url()
	// -------------------------------------------------------------------------

	/**
	 * @dataProvider provide_protocolize_cases
	 */
	public function test_protocolize_url( string $label, string $input, string $expected ): void {
		$this->assertSame(
			$expected,
			$this->rewriter->protocolize_url( $input ),
			"case: {$label}"
		);
	}

	public function provide_protocolize_cases(): array {
		return [
			'plain hostname'         => [ 'plain', 'example.test', 'https://example.test/' ],
			'with path'              => [ 'path', 'example.test/foo', 'https://example.test/foo/' ],
			'already https'          => [ 'https', 'https://example.test', 'https://example.test/' ],
			'already http'           => [ 'http', 'http://example.test', 'http://example.test/' ],
			'protocol-relative'      => [ 'protocol-relative', '//example.test', '//example.test/' ],
			'site-root path'         => [ 'rooted', '/wp-content', '/wp-content/' ],
			'already trailing-slash' => [ 'tslash', 'example.test/', 'https://example.test/' ],
		];
	}

	// -------------------------------------------------------------------------
	// relative_url() — protected
	// -------------------------------------------------------------------------

	public function test_relative_url_strips_scheme(): void {
		$method = $this->reflection->getMethod( 'relative_url' );
		$method->setAccessible( true );

		$this->assertSame(
			'//example.test/wp-content/uploads/',
			$method->invoke( $this->rewriter, 'https://example.test/wp-content/uploads/' )
		);
		$this->assertSame(
			'//example.test/wp-content/uploads/',
			$method->invoke( $this->rewriter, 'http://example.test/wp-content/uploads/' )
		);
	}

	// -------------------------------------------------------------------------
	// rewrite() — full HTML pipeline
	// -------------------------------------------------------------------------

	public function test_rewrite_replaces_full_url_in_img_src(): void {
		$this->configure_for_rewriting();
		$html = '<img src="https://example.test/wp-content/uploads/2026/03/photo.jpg" alt="x">';

		$result = $this->rewriter->rewrite( $html );

		$this->assertStringContainsString(
			'src="https://cdn.example.test/2026/03/photo.jpg"',
			$result
		);
		$this->assertStringNotContainsString( '//example.test/wp-content/uploads/', $result );
	}

	public function test_rewrite_replaces_protocol_relative_url(): void {
		$this->configure_for_rewriting();
		$html = '<img src="//example.test/wp-content/uploads/x.png">';

		$result = $this->rewriter->rewrite( $html );

		$this->assertStringContainsString( 'cdn.example.test/x.png', $result );
	}

	public function test_rewrite_replaces_relative_path_after_quote(): void {
		$this->configure_for_rewriting();
		// Relative path that begins right after a quote — matches the regex's
		// (?<=[(\"\'=\s]) lookbehind branch.
		$html = '<img src="/wp-content/uploads/2026/03/photo.jpg">';

		$result = $this->rewriter->rewrite( $html );

		// The regex matches '/wp-content/uploads/' inside an attribute. The
		// callback substitutes the matched prefix with the CDN URL.
		$this->assertStringContainsString( 'cdn.example.test/2026/03/photo.jpg', $result );
	}

	public function test_rewrite_leaves_unrelated_urls_alone(): void {
		$this->configure_for_rewriting();
		$html = '<a href="https://other-site.test/page">link</a><script src="/wp-includes/js/foo.js"></script>';

		$result = $this->rewriter->rewrite( $html );

		$this->assertSame( $html, $result, 'URLs outside the uploads dir should be untouched' );
	}

	public function test_rewrite_skips_urls_under_excluded_dirs(): void {
		$this->configure_for_rewriting();
		$this->set_prop( 'exclusions', [
			'/cache/' => 'https://example.test/wp-content/uploads/cache',
		] );
		$html = '<img src="https://example.test/wp-content/uploads/cache/foo.css">';

		$result = $this->rewriter->rewrite( $html );

		$this->assertStringContainsString( 'example.test/wp-content/uploads/cache/foo.css', $result );
		$this->assertStringNotContainsString( 'cdn.example.test', $result );
	}

	// -------------------------------------------------------------------------
	// Smush URL fix pairs
	// -------------------------------------------------------------------------

	public function test_rewrite_applies_smush_url_fix_pairs(): void {
		$this->configure_for_rewriting();
		$this->set_prop( 'smush_url_fix_pairs', [
			'https:/smush-webp/' => 'https://cdn.example.test/smush-webp/',
		] );
		$html = '<img src="https:/smush-webp/2026/03/photo.png.webp">';

		$result = $this->rewriter->rewrite( $html );

		$this->assertStringContainsString(
			'https://cdn.example.test/smush-webp/2026/03/photo.png.webp',
			$result
		);
		$this->assertStringNotContainsString( 'https:/smush-webp/', $result );
	}

	public function test_rewrite_skips_smush_replacement_when_pattern_absent(): void {
		$this->configure_for_rewriting();
		$this->set_prop( 'smush_url_fix_pairs', [
			'https:/smush-webp/' => 'https://cdn.example.test/smush-webp/',
		] );
		$html = '<img src="https://example.test/wp-content/uploads/x.png">';

		$result = $this->rewriter->rewrite( $html );

		// Should rewrite via the main pass but not apply Smush fix.
		$this->assertStringContainsString( 'cdn.example.test/x.png', $result );
	}

	// -------------------------------------------------------------------------
	// rewrite_the_content() — content filter wrapper
	// -------------------------------------------------------------------------

	public function test_rewrite_the_content_delegates_to_rewrite(): void {
		$this->configure_for_rewriting();
		$html = '<p><img src="https://example.test/wp-content/uploads/x.jpg"></p>';

		$result = $this->rewriter->rewrite_the_content( $html );

		$this->assertStringContainsString( 'cdn.example.test/x.jpg', $result );
	}

	// -------------------------------------------------------------------------
	// rewrite_rest_attachment() — REST API filter
	// -------------------------------------------------------------------------

	public function test_rewrite_rest_attachment_substitutes_smush_pairs_in_payload(): void {
		$this->set_prop( 'smush_url_fix_pairs', [
			'https:/smush-webp/' => 'https://cdn.example.test/smush-webp/',
		] );

		$response = new \WP_REST_Response();
		$response->set_data( [
			'source_url' => 'https:/smush-webp/foo.webp',
			'meta'       => [ 'note' => 'unrelated' ],
		] );

		$post    = (object) [ 'ID' => 123 ];
		$request = (object) [];

		$result   = $this->rewriter->rewrite_rest_attachment( $response, $post, $request );
		$captured = $result->get_data();

		$this->assertSame( $response, $result, 'Filter must return the response object' );
		$this->assertIsArray( $captured );
		$this->assertSame(
			'https://cdn.example.test/smush-webp/foo.webp',
			$captured['source_url']
		);
		$this->assertSame( 'unrelated', $captured['meta']['note'] );
	}

	public function test_rewrite_rest_attachment_passes_through_when_no_fix_pairs(): void {
		$this->set_prop( 'smush_url_fix_pairs', [] );

		$original_data = [ 'source_url' => 'https://cdn.example.test/foo.jpg' ];
		$response      = new \WP_REST_Response();
		$response->set_data( $original_data );

		$post    = (object) [ 'ID' => 123 ];
		$request = (object) [];

		$result = $this->rewriter->rewrite_rest_attachment( $response, $post, $request );

		$this->assertSame( $response, $result );
		$this->assertSame( $original_data, $result->get_data(), 'Data must be unchanged when no fix pairs are set' );
	}
}
