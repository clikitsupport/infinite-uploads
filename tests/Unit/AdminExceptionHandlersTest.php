<?php
/**
 * Tests for InfiniteUploadsAdmin's exception handlers.
 *
 * The catch(\Exception $e) fix (fully-qualified type hint, replacing the
 * silently-broken unqualified `Exception` that resolved to the non-existent
 * ClikIT\InfiniteUploads\Exception in this namespaced file) makes twelve
 * previously-dormant catch blocks reachable for the first time. The three
 * shared handlers (handle_transfer_exception, handle_multipart_exception,
 * handle_download_exception) also grew retire-on-permanent-failure logic so
 * a single bad S3 key (NoSuchKey, AccessDenied, ...) doesn't poison the
 * whole batch of otherwise-healthy files.
 *
 * These tests cover both:
 *   1. The type-hint fix — a real \Exception reaches each handler (would
 *      have thrown a TypeError before the fix).
 *   2. The retire logic — permanent-failure AWS codes bump errors to 3,
 *      transient codes don't touch the DB, and non-AWS exceptions produce
 *      the generic fallback message.
 *
 * The handlers are private, so tests use ReflectionClass to instantiate the
 * Admin class without its (very heavy) constructor and to invoke the
 * methods directly.
 *
 * @package ClikIT\InfiniteUploads\Tests\Unit
 */

declare( strict_types=1 );

namespace ClikIT\InfiniteUploads\Tests\Unit;

use Brain\Monkey\Functions;
use ClikIT\InfiniteUploads\InfiniteUploadsAdmin;
use ClikIT\InfiniteUploads\InfiniteUploadsHelper;
use ClikIT\InfiniteUploads\Tests\TestCase;
use Mockery;
use ReflectionClass;
use stdClass;

class AdminExceptionHandlersTest extends TestCase {

	/**
	 * @var InfiniteUploadsAdmin
	 */
	private $admin;

	/**
	 * @var ReflectionClass<InfiniteUploadsAdmin>
	 */
	private $reflection;

	/**
	 * @var Mockery\MockInterface  Global $wpdb mock (set on setUp via mock_wpdb).
	 */
	private $wpdb;

	protected function setUp(): void {
		parent::setUp();

		// Load the class without executing constructor side effects
		// (add_action wiring, WP function calls, etc.). Also load the
		// helper — the handlers delegate to it for retire decisions.
		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsHelper.php';
		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsAdmin.php';

		$this->reflection = new ReflectionClass( InfiniteUploadsAdmin::class );
		$this->admin      = $this->reflection->newInstanceWithoutConstructor();

		// The handlers reference $this->iup_instance->bucket. Provide a
		// minimal stdClass stand-in that the handlers can read from.
		$stub_iup          = new stdClass();
		$stub_iup->bucket  = 'iup-test-bucket/';
		$iup_prop          = $this->reflection->getProperty( 'iup_instance' );
		$iup_prop->setAccessible( true );
		$iup_prop->setValue( $this->admin, $stub_iup );

		$this->wpdb = $this->mock_wpdb();

		// WP function stubs used by the handlers.
		Functions\when( 'esc_html' )->returnArg();
		Functions\when( 'esc_html__' )->returnArg();
		Functions\when( 'trailingslashit' )->alias( function ( $s ) {
			return rtrim( (string) $s, '/' ) . '/';
		} );
		Functions\when( 'untrailingslashit' )->alias( function ( $s ) {
			return rtrim( (string) $s, '/' );
		} );
	}

	// ---------------------------------------------------------------------
	// Type-hint fix — real \Exception reaches each handler (no TypeError).
	// ---------------------------------------------------------------------

	public function test_handle_transfer_exception_accepts_real_exception_without_type_error(): void {
		// The bug: `Exception $e` in a namespaced file resolved to the non-
		// existent ClikIT\InfiniteUploads\Exception, so any real \Exception
		// tripped a TypeError before this handler could run. Verify the
		// fully-qualified `\Exception` type hint accepts the built-in class.
		$errors = [];
		$this->invoke_private( 'handle_transfer_exception', [
			$this->wpdb,
			new \Exception( 'plain PHP exception, no getRequest' ),
			&$errors,
		] );

		$this->assertSame(
			[ 'Error uploading file. Queued for retry.' ],
			$errors,
			'Generic message expected when the exception has no getRequest()'
		);
	}

	public function test_handle_multipart_exception_accepts_real_exception_without_type_error(): void {
		$errors  = [];
		$to_sync = (object) [ 'file' => '/2026/06/img.jpg', 'errors' => 1 ];

		$this->invoke_private( 'handle_multipart_exception', [
			$this->wpdb,
			$to_sync,
			new \Exception( 'plain' ),
			&$errors,
		] );

		// esc_html__ is stubbed to return its format string, and sprintf then
		// interpolates the file path. The key assertion here is that (a) no
		// TypeError was thrown by the \Exception type hint, and (b) we took
		// the retry-not-exceeded branch (errors = 1 < 3).
		$this->assertCount( 1, $errors );
		$this->assertStringContainsString( '/2026/06/img.jpg', $errors[0] );
		$this->assertStringContainsString( 'Queued for retry', $errors[0] );
	}

	public function test_handle_download_exception_accepts_real_exception_without_type_error(): void {
		$errors = [];
		$path   = [ 'basedir' => '/var/www/html/wp-content/uploads' ];

		$this->invoke_private( 'handle_download_exception', [
			$this->wpdb,
			new \Exception( 'plain' ),
			$path,
			'iup-test-bucket/',
			&$errors,
		] );

		$this->assertSame(
			[ 'Error downloading file. Queued for retry.' ],
			$errors
		);
	}

	// ---------------------------------------------------------------------
	// is_permanent_aws_failure — code coverage.
	// ---------------------------------------------------------------------

	/**
	 * @dataProvider provide_permanent_failure_codes
	 */
	public function test_is_permanent_aws_failure_true_for_known_permanent_codes( string $code ): void {
		$aws_exception = $this->build_aws_exception( '/bucket/f', $code );
		$this->assertTrue(
			InfiniteUploadsHelper::is_permanent_aws_failure( $aws_exception ),
			"AWS code {$code} should be classified as permanent failure"
		);
	}

	public function provide_permanent_failure_codes(): array {
		return [
			'NoSuchKey'                          => [ 'NoSuchKey' ],
			'NoSuchBucket'                       => [ 'NoSuchBucket' ],
			'AccessDenied'                       => [ 'AccessDenied' ],
			'InvalidBucketName'                  => [ 'InvalidBucketName' ],
			'InvalidRequest'                     => [ 'InvalidRequest' ],
			'InvalidBucketAclWithObjectOwnership' => [ 'InvalidBucketAclWithObjectOwnership' ],
			'RequestTimeTooSkewed'               => [ 'RequestTimeTooSkewed' ],
		];
	}

	/**
	 * @dataProvider provide_transient_or_unknown_codes
	 */
	public function test_is_permanent_aws_failure_false_for_transient_or_unknown_codes( string $code ): void {
		$aws_exception = $this->build_aws_exception( '/bucket/f', $code );
		$this->assertFalse(
			InfiniteUploadsHelper::is_permanent_aws_failure( $aws_exception )
		);
	}

	public function provide_transient_or_unknown_codes(): array {
		return [
			'Throttling'        => [ 'Throttling' ],
			'SlowDown'          => [ 'SlowDown' ],
			'InternalError'     => [ 'InternalError' ],
			'ServiceUnavailable' => [ 'ServiceUnavailable' ],
			'unknown code'      => [ 'SomethingWeNeverSaw' ],
		];
	}

	public function test_is_permanent_aws_failure_false_for_non_aws_exception(): void {
		// Plain \Exception — no getAwsErrorCode method.
		$this->assertFalse(
			InfiniteUploadsHelper::is_permanent_aws_failure( new \Exception( 'plain' ) )
		);
	}

	public function test_is_permanent_aws_failure_false_for_empty_or_non_string_code(): void {
		$empty_string_code = $this->build_aws_exception( '/bucket/f', '' );
		$this->assertFalse(
			InfiniteUploadsHelper::is_permanent_aws_failure( $empty_string_code )
		);
	}

	// ---------------------------------------------------------------------
	// handle_transfer_exception — retire vs. don't retire.
	// ---------------------------------------------------------------------

	public function test_transfer_exception_bumps_errors_to_3_on_NoSuchKey(): void {
		// The core "one bad key poisoning the batch" scenario. NoSuchKey is
		// intrinsic to the file — no retry will produce it in S3 — so retire
		// this file immediately (errors = 3) instead of letting the whole
		// batch keep re-attempting and dragging healthy buddies to retry
		// exhaustion.
		$e = $this->build_aws_exception( '/iup-test-bucket/2026/06/missing.jpg', 'NoSuchKey' );

		$this->wpdb
			->shouldReceive( 'update' )
			->once()
			->with(
				'wp_infinite_uploads_files',
				[ 'errors' => 3 ],
				[ 'file' => '/2026/06/missing.jpg' ],
				[ '%d' ],
				[ '%s' ]
			);

		// After the retire update, the SELECT reads errors = 3 for the
		// "Retries exceeded" branch of the message.
		$this->wpdb
			->shouldReceive( 'get_var' )
			->andReturn( 3 );

		$errors = [];
		$this->invoke_private( 'handle_transfer_exception', [ $this->wpdb, $e, &$errors ] );

		$this->assertCount( 1, $errors );
		$this->assertStringContainsString( 'Retries exceeded', $errors[0] );
	}

	public function test_transfer_exception_does_NOT_bump_errors_on_transient_failure(): void {
		// Throttling / SlowDown / etc. WILL improve on retry. Leave the
		// pre-increment errors count alone and let the natural retry loop
		// (WHERE errors < 3) grind — bumping to 3 here would prematurely
		// retire files that would otherwise succeed on the next request.
		$e = $this->build_aws_exception( '/iup-test-bucket/2026/06/img.jpg', 'Throttling' );

		// No update() call for errors=3 in this path.
		$this->wpdb->shouldNotReceive( 'update' );

		// SELECT still runs to pick the retry-vs-exceeded message copy.
		$this->wpdb
			->shouldReceive( 'get_var' )
			->andReturn( 1 );

		$errors = [];
		$this->invoke_private( 'handle_transfer_exception', [ $this->wpdb, $e, &$errors ] );

		$this->assertCount( 1, $errors );
		$this->assertStringContainsString( 'Queued for retry', $errors[0] );
	}

	public function test_transfer_exception_no_getRequest_produces_generic_message_and_no_db_write(): void {
		$this->wpdb->shouldNotReceive( 'update' );
		$this->wpdb->shouldNotReceive( 'get_var' );

		$errors = [];
		$this->invoke_private( 'handle_transfer_exception', [
			$this->wpdb,
			new \Exception( 'unknown network glitch, no request context' ),
			&$errors,
		] );

		$this->assertSame( [ 'Error uploading file. Queued for retry.' ], $errors );
	}

	// ---------------------------------------------------------------------
	// handle_multipart_exception — retire vs. don't retire.
	// ---------------------------------------------------------------------

	public function test_multipart_exception_bumps_errors_and_updates_to_sync_object_on_permanent_failure(): void {
		$to_sync = (object) [ 'file' => '/2026/06/big.mp4', 'errors' => 1 ];
		$e       = $this->build_aws_exception( '/iup-test-bucket/2026/06/big.mp4', 'NoSuchBucket' );

		$this->wpdb
			->shouldReceive( 'update' )
			->once()
			->with(
				'wp_infinite_uploads_files',
				[ 'errors' => 3 ],
				[ 'file' => '/2026/06/big.mp4' ],
				[ '%d' ],
				[ '%s' ]
			);

		$errors = [];
		$this->invoke_private( 'handle_multipart_exception', [
			$this->wpdb,
			$to_sync,
			$e,
			&$errors,
		] );

		// The in-memory $to_sync object should mirror the DB — its errors
		// field is used downstream to pick the message branch, so leaving
		// it stale would say "Queued for retry" while the DB row is retired.
		$this->assertSame( 3, $to_sync->errors );
		$this->assertCount( 1, $errors );
		$this->assertStringContainsString( 'Retries exceeded', $errors[0] );
	}

	public function test_multipart_exception_does_not_touch_db_on_transient_failure(): void {
		$to_sync = (object) [ 'file' => '/2026/06/big.mp4', 'errors' => 1 ];
		$e       = $this->build_aws_exception( '/iup-test-bucket/2026/06/big.mp4', 'SlowDown' );

		$this->wpdb->shouldNotReceive( 'update' );

		$errors = [];
		$this->invoke_private( 'handle_multipart_exception', [
			$this->wpdb,
			$to_sync,
			$e,
			&$errors,
		] );

		$this->assertSame( 1, $to_sync->errors, 'errors count untouched for transient failures' );
		$this->assertCount( 1, $errors );
		$this->assertStringContainsString( 'Queued for retry', $errors[0] );
	}

	// ---------------------------------------------------------------------
	// handle_download_exception — retire vs. don't retire.
	// ---------------------------------------------------------------------

	public function test_download_exception_bumps_errors_to_3_on_NoSuchKey(): void {
		$path = [ 'basedir' => '/var/www/html/wp-content/uploads' ];
		// getRequestTarget for downloads includes the bucket prefix; the
		// handler strips both the bucket and the basedir.
		$e = $this->build_aws_exception(
			'/iup-test-bucket/var/www/html/wp-content/uploads/2026/06/gone.jpg',
			'NoSuchKey'
		);

		$this->wpdb
			->shouldReceive( 'update' )
			->once()
			->with(
				'wp_infinite_uploads_files',
				[ 'errors' => 3 ],
				[ 'file' => '2026/06/gone.jpg' ],
				[ '%d' ],
				[ '%s' ]
			);

		$this->wpdb
			->shouldReceive( 'get_var' )
			->andReturn( 3 );

		$errors = [];
		$this->invoke_private( 'handle_download_exception', [
			$this->wpdb,
			$e,
			$path,
			'iup-test-bucket/',
			&$errors,
		] );

		$this->assertCount( 1, $errors );
		$this->assertStringContainsString( 'Retries exceeded', $errors[0] );
	}

	public function test_download_exception_does_NOT_bump_errors_on_transient_failure(): void {
		$path = [ 'basedir' => '/var/www/html/wp-content/uploads' ];
		$e    = $this->build_aws_exception(
			'/iup-test-bucket/var/www/html/wp-content/uploads/2026/06/img.jpg',
			'InternalError'
		);

		$this->wpdb->shouldNotReceive( 'update' );
		$this->wpdb->shouldReceive( 'get_var' )->andReturn( 1 );

		$errors = [];
		$this->invoke_private( 'handle_download_exception', [
			$this->wpdb,
			$e,
			$path,
			'iup-test-bucket/',
			&$errors,
		] );

		$this->assertCount( 1, $errors );
		$this->assertStringContainsString( 'Queued for retry', $errors[0] );
	}

	// ---------------------------------------------------------------------
	// Helpers.
	// ---------------------------------------------------------------------

	/**
	 * Invoke a private method on the Admin instance. Params passed by reference
	 * must be assigned via reference in the caller — PHP's ReflectionMethod's
	 * invokeArgs preserves reference semantics for by-ref parameters.
	 *
	 * @param  string  $name
	 * @param  array   $args
	 *
	 * @return mixed
	 */
	private function invoke_private( string $name, array $args ) {
		$method = $this->reflection->getMethod( $name );
		$method->setAccessible( true );

		return $method->invokeArgs( $this->admin, $args );
	}

	/**
	 * Build a mocked AWS-shaped exception with getRequest()->getRequestTarget()
	 * and getAwsErrorCode(). Extends \Exception so it satisfies the handlers'
	 * `\Exception` type hints; adds the two AWS-only methods via a stub
	 * subclass. Real AwsException / S3Exception have much richer surfaces but
	 * we exercise only what our code reads.
	 *
	 * @param  string  $request_target  What getRequest()->getRequestTarget() returns.
	 * @param  string  $error_code       What getAwsErrorCode() returns.
	 *
	 * @return \Exception
	 */
	private function build_aws_exception( string $request_target, string $error_code ): \Exception {
		return new class( $request_target, $error_code ) extends \Exception {
			private $request_target;
			private $error_code;

			public function __construct( string $request_target, string $error_code ) {
				parent::__construct( 'stub AWS exception: ' . $error_code );
				$this->request_target = $request_target;
				$this->error_code     = $error_code;
			}

			public function getRequest() {
				return new class( $this->request_target ) {
					private $target;
					public function __construct( string $target ) { $this->target = $target; }
					public function getRequestTarget(): string { return $this->target; }
				};
			}

			public function getAwsErrorCode(): string {
				return $this->error_code;
			}
		};
	}
}
