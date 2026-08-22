<?php
/**
 * Tests for InfiniteUploadsDeviceConnect — the plugin half of the device-flow
 * domain-verification handshake.
 *
 * The security property under test: verify-connect answers 200 only for the
 * exact client_state this site generated for an in-progress attempt, and
 * returns an indistinguishable 403 for both a wrong value and no attempt at
 * all (so it can't be used to probe connect state). client_state_matches is
 * the constant-time core; the REST handler is the transport around it.
 *
 * @package ClikIT\InfiniteUploads\Tests\Unit
 */

declare( strict_types=1 );

namespace ClikIT\InfiniteUploads\Tests\Unit;

use Brain\Monkey\Functions;
use ClikIT\InfiniteUploads\InfiniteUploadsDeviceConnect;
use ClikIT\InfiniteUploads\Tests\TestCase;
use ReflectionClass;
use WP_Error;

class DeviceConnectTest extends TestCase {

	/**
	 * @var InfiniteUploadsDeviceConnect
	 */
	private $device;

	protected function setUp(): void {
		parent::setUp();

		Functions\when( '__' )->returnArg();

		require_once IU_PLUGIN_ROOT . '/tests/fixtures/wp-error-stub.php';
		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsDeviceConnect.php';

		$this->device = ( new ReflectionClass( InfiniteUploadsDeviceConnect::class ) )->newInstanceWithoutConstructor();
	}

	public function test_matches_the_stored_attempt(): void {
		Functions\when( 'get_site_transient' )->justReturn( hash( 'sha256', 'the-real-state' ) );

		$this->assertTrue( $this->device->client_state_matches( 'the-real-state' ) );
	}

	public function test_rejects_a_different_value(): void {
		Functions\when( 'get_site_transient' )->justReturn( hash( 'sha256', 'the-real-state' ) );

		$this->assertFalse( $this->device->client_state_matches( 'a-guess' ) );
	}

	public function test_rejects_when_no_attempt_in_progress(): void {
		Functions\when( 'get_site_transient' )->justReturn( false );

		$this->assertFalse( $this->device->client_state_matches( 'anything' ) );
	}

	public function test_begin_attempt_stores_the_hash_and_returns_plaintext(): void {
		Functions\when( 'wp_generate_password' )->justReturn( 'GENERATEDSTATE' );
		Functions\expect( 'set_site_transient' )
			->once()
			->with( 'iup_device_client_state', hash( 'sha256', 'GENERATEDSTATE' ), 900 );

		$this->assertSame( 'GENERATEDSTATE', $this->device->begin_attempt() );
	}

	public function test_verifies_a_matching_state(): void {
		Functions\when( 'get_site_transient' )->justReturn( hash( 'sha256', 'abcdefghabcdefghabcdefghabcdefgh' ) );

		$this->assertSame(
			200,
			$this->device->evaluate_verify( 'abcdefghabcdefghabcdefghabcdefgh' )['status']
		);
	}

	// No-attempt and wrong-value must be the SAME 403 so the endpoint can't
	// reveal whether a site is mid-connect.
	public function test_identical_403_for_mismatch_and_no_attempt(): void {
		Functions\when( 'get_site_transient' )->justReturn( hash( 'sha256', 'abcdefghabcdefghabcdefghabcdefgh' ) );
		$mismatch = $this->device->evaluate_verify( 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz' );

		Functions\when( 'get_site_transient' )->justReturn( false );
		$no_attempt = $this->device->evaluate_verify( 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz' );

		$this->assertSame( 403, $mismatch['status'] );
		$this->assertSame( 403, $no_attempt['status'] );
		$this->assertSame( $mismatch, $no_attempt );
	}

	public function test_rejects_malformed_input_before_checking_state(): void {
		// A malformed value must be refused without ever touching stored state
		// (no get_site_transient expectation is set).
		$decision = $this->device->evaluate_verify( 'short' );

		$this->assertSame( 400, $decision['status'] );
		$this->assertSame( 'iu_invalid_request', $decision['code'] );
	}
}
