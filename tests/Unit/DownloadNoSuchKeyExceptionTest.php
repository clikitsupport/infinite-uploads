<?php
/**
 * Tests for handle_download_exception()'s NoSuchKey handling.
 *
 * On master, handle_download_exception( $wpdb, Exception $e, ... ) declares
 * an UNQUALIFIED `Exception` type hint. The file has no `use Exception;`, so
 * that hint resolves to ClikIT\InfiniteUploads\Exception — a class that does
 * not exist. The one caller passes a real (fully-qualified) \Exception from
 * a `catch ( \Exception $e )` block, so every call throws a TypeError before
 * a single line of the function body runs. That's the actual mechanism
 * behind "Download & Disconnect died with Too many server errors": ANY
 * exception during download — not just a missing key — reached this
 * function and immediately fataled.
 *
 * The fix qualifies only this one signature (`\Exception $e`), which is
 * enough to make the function callable again. It deliberately does NOT add
 * a file-wide `use Exception;` — that would also silently revive twelve
 * unrelated `catch ( Exception $e )` blocks elsewhere in this file whose
 * behavior has never been exercised in production and needs separate
 * review. This test's first assertion is that very fact: the fully-
 * qualified type hint is both necessary and sufficient — the function is
 * callable, and every other unqualified catch in the file is untouched.
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
use ReflectionMethod;

/**
 * Stand-in for the AWS SDK exceptions handle_download_exception() actually
 * receives (S3Exception / AwsException). Real AWS exceptions expose
 * getRequest() (PSR-7 RequestInterface, itself exposing getRequestTarget())
 * and getAwsErrorCode(). This fixture implements just enough of both to
 * drive the function under test — it IS a real \Exception subclass, which
 * is the whole point: the unqualified type hint on master rejects this
 * exact shape of object.
 */
class FakeS3DownloadException extends \Exception {

	private $request_target;
	private $aws_error_code;

	public function __construct( string $message, string $request_target, ?string $aws_error_code ) {
		parent::__construct( $message );
		$this->request_target = $request_target;
		$this->aws_error_code = $aws_error_code;
	}

	public function getRequest() {
		return new class( $this->request_target ) {
			private $target;

			public function __construct( string $target ) {
				$this->target = $target;
			}

			public function getRequestTarget(): string {
				return $this->target;
			}
		};
	}

	public function getAwsErrorCode(): ?string {
		return $this->aws_error_code;
	}
}

class DownloadNoSuchKeyExceptionTest extends TestCase {

	/** @var InfiniteUploadsAdmin */
	private $admin;

	/** @var ReflectionMethod */
	private $method;

	protected function setUp(): void {
		parent::setUp();
		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsAdmin.php';

		Functions\when( 'esc_html__' )->returnArg( 1 );
		Functions\when( 'esc_html' )->returnArg( 1 );
		Functions\when( 'untrailingslashit' )->alias(
			static function ( $s ) {
				return rtrim( (string) $s, '/' );
			}
		);
		Functions\when( 'trailingslashit' )->alias(
			static function ( $s ) {
				return rtrim( (string) $s, '/' ) . '/';
			}
		);

		$ref         = new ReflectionClass( InfiniteUploadsAdmin::class );
		$this->admin = $ref->newInstanceWithoutConstructor();

		$this->method = $ref->getMethod( 'handle_download_exception' );
		$this->method->setAccessible( true );
	}

	/**
	 * Call the private method under test.
	 *
	 * @return array The $errors array after the call (passed by reference).
	 */
	private function invoke( $wpdb, \Exception $e, array $path, string $bucket ): array {
		$errors = [];
		$this->method->invokeArgs( $this->admin, [ $wpdb, $e, $path, $bucket, &$errors ] );

		return $errors;
	}

	public function test_the_fix_is_scoped_to_this_one_function(): void {
		$source = file_get_contents( IU_PLUGIN_ROOT . '/inc/InfiniteUploadsAdmin.php' );

		$this->assertStringNotContainsString(
			"\nuse Exception;\n",
			$source,
			'No file-wide `use Exception;` import — that would revive the eleven unrelated dead catch blocks.'
		);

		$this->assertSame(
			12,
			substr_count( $source, 'catch ( Exception $e )' ),
			'All twelve unqualified catch blocks elsewhere in the file must remain untouched.'
		);

		$this->assertSame(
			1,
			substr_count( $source, 'catch ( \\Exception $e )' ),
			'Exactly one fully-qualified catch exists (the one that calls handle_download_exception) — unchanged by this fix.'
		);
	}

	public function test_function_is_now_callable_with_a_real_exception(): void {
		// This is the actual regression: on master, this call throws a
		// TypeError before reaching the assertion — the unqualified
		// `Exception $e` hint doesn't match a real \Exception instance.
		$wpdb = $this->mock_wpdb();
		$wpdb->shouldReceive( 'get_var' )->andReturn( 0 );

		$e = new FakeS3DownloadException(
			'Generic S3 error',
			'/bucket-name/2026/07/photo.jpg',
			null // Not a NoSuchKey — exercises the generic (non-early-return) branch.
		);

		$errors = $this->invoke(
			$wpdb,
			$e,
			[ 'basedir' => '/var/www/html/wp-content/uploads' ],
			'bucket-name'
		);

		$this->assertCount( 1, $errors, 'A generic download exception should still queue exactly one error message.' );
	}

	public function test_nosuchkey_retires_the_file_instead_of_poisoning_the_batch(): void {
		$wpdb = $this->mock_wpdb();

		// The fix's UPDATE ... SET errors = 3 must fire for the missing key.
		$wpdb->shouldReceive( 'query' )
			->once()
			->with( Mockery::pattern( '/SET errors = 3.*photo\\.jpg/s' ) )
			->andReturn( 1 );

		// Generic error_count lookup must NOT fire — NoSuchKey returns early.
		$wpdb->shouldNotReceive( 'get_var' );

		$e = new FakeS3DownloadException(
			'Not Found',
			'/bucket-name/2026/07/photo.jpg',
			'NoSuchKey'
		);

		$errors = $this->invoke(
			$wpdb,
			$e,
			[ 'basedir' => '/var/www/html/wp-content/uploads' ],
			'bucket-name'
		);

		$this->assertCount( 1, $errors );
		$this->assertStringContainsString( 'missing from cloud storage', $errors[0] );
	}

	public function test_nosuchkey_removes_the_small_error_body_stub_but_not_a_real_file(): void {
		$upload_dir = sys_get_temp_dir() . '/iu-download-exc-test-' . uniqid();
		mkdir( $upload_dir . '/2026/07', 0777, true );

		// A small file under the 1KB threshold: the 404 XML body the SDK
		// streamed to disk before throwing. Must be deleted.
		$stub_path = $upload_dir . '/2026/07/stub.jpg';
		file_put_contents( $stub_path, '<Error>NoSuchKey</Error>' );

		// A real, larger file at a different path: must survive untouched.
		$real_path = $upload_dir . '/2026/07/real.jpg';
		file_put_contents( $real_path, str_repeat( 'x', 2048 ) );

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldReceive( 'query' )->andReturn( 1 );

		$e = new FakeS3DownloadException( 'Not Found', '/bucket-name/2026/07/stub.jpg', 'NoSuchKey' );
		$this->invoke( $wpdb, $e, [ 'basedir' => $upload_dir ], 'bucket-name' );

		$this->assertFileDoesNotExist( $stub_path, 'The sub-1KB error-body stub must be deleted.' );
		$this->assertFileExists( $real_path, 'Files at other paths must be untouched.' );

		unlink( $real_path );
		rmdir( $upload_dir . '/2026/07' );
		rmdir( $upload_dir . '/2026' );
		rmdir( $upload_dir );
	}
}
