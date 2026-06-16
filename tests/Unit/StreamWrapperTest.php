<?php
/**
 * Tests for InfiniteUploadsStreamWrapper — only the testable pure-logic
 * helpers. Full stream-wrapper integration (open/read/write/close, S3
 * communication) is out of scope for unit tests and would need either a
 * heavy AWS SDK mock harness or a real S3 endpoint.
 *
 * Methods covered here:
 *   - getBucketKey()          — path → ['Bucket' => ..., 'Key' => ...]
 *   - initProtocol()          — extracts protocol from path
 *   - normalizeSmushPath()    — early-return behaviour for non-smush paths
 *   - calculate_chunk_size()  — multipart upload chunk-size heuristics
 *
 * @package ClikIT\InfiniteUploads\Tests\Unit
 */

declare( strict_types=1 );

namespace ClikIT\InfiniteUploads\Tests\Unit;

use Brain\Monkey\Functions;
use ClikIT\InfiniteUploads\InfiniteUploadsStreamWrapper;
use ClikIT\InfiniteUploads\Tests\TestCase;
use ReflectionClass;

class StreamWrapperTest extends TestCase {

	/**
	 * @var InfiniteUploadsStreamWrapper
	 */
	private $wrapper;

	/**
	 * @var ReflectionClass
	 */
	private $reflection;

	protected function setUp(): void {
		parent::setUp();
		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsStreamWrapper.php';

		$this->reflection = new ReflectionClass( InfiniteUploadsStreamWrapper::class );
		$this->wrapper    = $this->reflection->newInstanceWithoutConstructor();
	}

	private function invoke_private( string $method, array $args = [] ) {
		$m = $this->reflection->getMethod( $method );
		$m->setAccessible( true );
		return $m->invokeArgs( $this->wrapper, $args );
	}

	private function get_prop( string $name ) {
		$p = $this->reflection->getProperty( $name );
		$p->setAccessible( true );
		return $p->getValue( $this->wrapper );
	}

	// -------------------------------------------------------------------------
	// getBucketKey() — pure path parsing
	// -------------------------------------------------------------------------

	/**
	 * @dataProvider provide_bucket_key_paths
	 */
	public function test_get_bucket_key_parses_correctly( string $label, string $path, string $expected_bucket, $expected_key ): void {
		$result = $this->invoke_private( 'getBucketKey', [ $path ] );

		$this->assertSame( $expected_bucket, $result['Bucket'], "case: {$label}" );
		$this->assertSame( $expected_key, $result['Key'], "case: {$label}" );
	}

	public function provide_bucket_key_paths(): array {
		return [
			'bucket + key'            => [ 'b+k', 'iu://my-bucket/some/key.jpg', 'my-bucket', 'some/key.jpg' ],
			'bucket only'             => [ 'b only', 'iu://my-bucket/', 'my-bucket', '' ],
			'bucket only no trailing' => [ 'b no /', 'iu://my-bucket', 'my-bucket', null ],
			'nested bucket prefix'    => [ 'nested', 'iu://bucket/user_id/site_id/file.png', 'bucket', 'user_id/site_id/file.png' ],
			'deep key'                => [ 'deep', 'iu://b/a/b/c/d/e.jpg', 'b', 'a/b/c/d/e.jpg' ],
		];
	}

	// -------------------------------------------------------------------------
	// initProtocol()
	// -------------------------------------------------------------------------

	public function test_init_protocol_sets_iu_for_iu_url(): void {
		$this->invoke_private( 'initProtocol', [ 'iu://bucket/key' ] );
		$this->assertSame( 'iu', $this->get_prop( 'protocol' ) );
	}

	public function test_init_protocol_handles_custom_scheme(): void {
		$this->invoke_private( 'initProtocol', [ 's3://bucket/key' ] );
		$this->assertSame( 's3', $this->get_prop( 'protocol' ) );
	}

	public function test_init_protocol_defaults_to_iu_when_missing(): void {
		// Path without scheme — explode returns empty first part, default
		// kicks in (the ?: 'iu' guard).
		$this->invoke_private( 'initProtocol', [ '://bucket/key' ] );
		$this->assertSame( 'iu', $this->get_prop( 'protocol' ) );
	}

	// -------------------------------------------------------------------------
	// normalizeSmushPath() — early-return paths
	// -------------------------------------------------------------------------

	public function test_normalize_smush_path_returns_unchanged_for_non_iu_protocol(): void {
		$path   = 'https://example.test/wp-content/uploads/photo.jpg';
		$result = $this->invoke_private( 'normalizeSmushPath', [ $path ] );

		$this->assertSame( $path, $result, 'non-iu paths must be returned unchanged' );
	}

	public function test_normalize_smush_path_returns_unchanged_for_non_smush_iu_path(): void {
		$path   = 'iu://my-bucket/2026/03/photo.jpg';
		$result = $this->invoke_private( 'normalizeSmushPath', [ $path ] );

		$this->assertSame( $path, $result, 'non-smush iu paths must be returned unchanged' );
	}

	public function test_normalize_smush_path_returns_unchanged_when_no_site_id_resolvable(): void {
		// Smush path, but getOption() returns no iup_instance — should still
		// short-circuit and return the original path.
		$path   = 'iu://bucket/user_id/smush-webp/foo.webp';
		$result = $this->invoke_private( 'normalizeSmushPath', [ $path ] );

		$this->assertSame( $path, $result );
	}

	// -------------------------------------------------------------------------
	// calculate_chunk_size()
	// -------------------------------------------------------------------------

	public function test_calculate_chunk_size_returns_20mb_when_max_upload_is_larger(): void {
		Functions\when( 'wp_max_upload_size' )->justReturn( 100 * 1024 * 1024 ); // 100 MB

		$this->assertSame(
			20 * 1024 * 1024,
			$this->wrapper->calculate_chunk_size(),
			'Default chunk size is 20 MB when WP allows large uploads'
		);
	}

	public function test_calculate_chunk_size_scales_down_to_80_percent_of_low_max_upload(): void {
		// 10 MB max upload — chunk size should be 80% = 8 MB.
		Functions\when( 'wp_max_upload_size' )->justReturn( 10 * 1024 * 1024 );

		$this->assertSame(
			(int) ( 10 * 1024 * 1024 * 0.8 ),
			(int) $this->wrapper->calculate_chunk_size()
		);
	}

	public function test_calculate_chunk_size_respects_big_file_uploads_constant(): void {
		if ( ! defined( 'BIG_FILE_UPLOADS_CHUNK_SIZE_KB' ) ) {
			define( 'BIG_FILE_UPLOADS_CHUNK_SIZE_KB', 4096 );
		}
		// wp_max_upload_size is irrelevant when the constant is defined.
		Functions\when( 'wp_max_upload_size' )->justReturn( 100 * 1024 * 1024 );

		$this->assertSame(
			4096 * 1024,
			(int) $this->wrapper->calculate_chunk_size(),
			'When BIG_FILE_UPLOADS_CHUNK_SIZE_KB is defined we use it verbatim (converted to bytes)'
		);
	}
}
