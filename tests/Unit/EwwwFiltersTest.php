<?php
/**
 * Tests for the EWWW Image Optimizer integration filters.
 *
 * Covers:
 *   - infinite_uploads_get_cdn_url_for_ewww() resolution under all conditions
 *   - filter_eio_s3_active() callback (host extraction)
 *   - filter_eio_s3_object_prefix() callback (path extraction)
 *   - Easy IO guard — exactdn option short-circuits both filters
 *
 * Implementation note: the production callbacks are named functions (not
 * anonymous closures) so tests can invoke them directly. apply_filters() can't
 * be used to drive these tests because Brain Monkey records hook calls without
 * actually executing the registered callbacks.
 *
 * @package ClikIT\InfiniteUploads\Tests\Unit
 */

declare( strict_types=1 );

namespace ClikIT\InfiniteUploads\Tests\Unit;

use Brain\Monkey\Functions;
use ClikIT\InfiniteUploads\InfiniteUploads;
use ClikIT\InfiniteUploads\Tests\TestCase;

require_once IU_TESTS_ROOT . '/fixtures/ewww-environment.php';

class EwwwFiltersTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();

		Functions\when( 'wp_parse_url' )->alias(
			static function ( $url, $component = -1 ) {
				return \parse_url( $url, $component );
			}
		);

		// Default: IU plugin enabled. Tests that need disabled override.
		Functions\when( 'infinite_uploads_enabled' )->justReturn( true );
		// Default: Easy IO off. Tests that need it on override.
		Functions\when( 'ewww_image_optimizer_get_option' )->justReturn( false );
		Functions\when( 'get_option' )->justReturn( false );

		// Default fake IU instance with a vanity-host CDN.
		$instance          = new InfiniteUploads();
		$instance->bucket  = 'iup-usa/3672/mfgqglic/';
		$instance->cdn_url = 'https://s37u5z.infiniteuploads.cloud';
		InfiniteUploads::reset_instance_for_tests( $instance );

		// Load production integration AFTER Brain Monkey has stubbed
		// add_filter — plain `require` so the add_filter() calls re-run on
		// every setUp (Brain Monkey's tearDown clears tracked callbacks). The
		// function declarations are guarded by function_exists so they're only
		// declared once across the test process.
		require IU_PLUGIN_ROOT . '/inc/ewww-integration.php';
	}

	protected function tearDown(): void {
		InfiniteUploads::reset_instance_for_tests( null );
		parent::tearDown();
	}

	// -------------------------------------------------------------------------
	// infinite_uploads_get_cdn_url_for_ewww()
	// -------------------------------------------------------------------------

	public function test_returns_cdn_url_when_iu_enabled_and_easy_io_off(): void {
		$this->assertSame(
			'https://s37u5z.infiniteuploads.cloud',
			\ClikIT\InfiniteUploads\infinite_uploads_get_cdn_url_for_ewww()
		);
	}

	public function test_returns_empty_when_iu_disabled(): void {
		Functions\when( 'infinite_uploads_enabled' )->justReturn( false );
		$this->assertSame( '', \ClikIT\InfiniteUploads\infinite_uploads_get_cdn_url_for_ewww() );
	}

	public function test_returns_empty_when_bucket_blank(): void {
		$instance          = new InfiniteUploads();
		$instance->bucket  = '';
		$instance->cdn_url = 'https://s37u5z.infiniteuploads.cloud';
		InfiniteUploads::reset_instance_for_tests( $instance );

		$this->assertSame( '', \ClikIT\InfiniteUploads\infinite_uploads_get_cdn_url_for_ewww() );
	}

	public function test_returns_empty_when_cdn_url_blank(): void {
		$instance          = new InfiniteUploads();
		$instance->bucket  = 'something';
		$instance->cdn_url = '';
		InfiniteUploads::reset_instance_for_tests( $instance );

		$this->assertSame( '', \ClikIT\InfiniteUploads\infinite_uploads_get_cdn_url_for_ewww() );
	}

	// -------------------------------------------------------------------------
	// Easy IO guard
	// -------------------------------------------------------------------------

	public function test_easy_io_guard_via_ewww_helper_function(): void {
		Functions\when( 'ewww_image_optimizer_get_option' )->justReturn( true );

		$this->assertSame(
			'',
			\ClikIT\InfiniteUploads\infinite_uploads_get_cdn_url_for_ewww(),
			'When Easy IO is active we must defer to EWWW (return empty so filters short-circuit)'
		);
	}

	public function test_easy_io_off_via_helper_still_returns_cdn(): void {
		Functions\when( 'ewww_image_optimizer_get_option' )->justReturn( false );

		$this->assertSame(
			'https://s37u5z.infiniteuploads.cloud',
			\ClikIT\InfiniteUploads\infinite_uploads_get_cdn_url_for_ewww()
		);
	}

	// -------------------------------------------------------------------------
	// filter_eio_s3_active() callback
	// -------------------------------------------------------------------------

	public function test_eio_s3_active_filter_is_registered_on_load(): void {
		$this->assertNotFalse(
			\has_filter( 'eio_s3_active' ),
			'eio_s3_active filter should be registered when ewww-integration.php is required'
		);
	}

	public function test_filter_eio_s3_active_returns_host_when_iu_enabled(): void {
		$this->assertSame(
			's37u5z.infiniteuploads.cloud',
			\ClikIT\InfiniteUploads\filter_eio_s3_active( false )
		);
	}

	public function test_filter_eio_s3_active_returns_input_when_iu_disabled(): void {
		Functions\when( 'infinite_uploads_enabled' )->justReturn( false );

		$this->assertFalse( \ClikIT\InfiniteUploads\filter_eio_s3_active( false ) );
		$this->assertSame(
			'other.cdn.com',
			\ClikIT\InfiniteUploads\filter_eio_s3_active( 'other.cdn.com' )
		);
	}

	public function test_filter_eio_s3_active_returns_input_when_easy_io_active(): void {
		Functions\when( 'ewww_image_optimizer_get_option' )->justReturn( true );

		$this->assertFalse( \ClikIT\InfiniteUploads\filter_eio_s3_active( false ) );
		$this->assertSame( 'preserved', \ClikIT\InfiniteUploads\filter_eio_s3_active( 'preserved' ) );
	}

	// -------------------------------------------------------------------------
	// filter_eio_s3_object_prefix() callback
	// -------------------------------------------------------------------------

	public function test_eio_s3_object_prefix_filter_is_registered_on_load(): void {
		$this->assertNotFalse( \has_filter( 'eio_s3_object_prefix' ) );
	}

	public function test_filter_eio_s3_object_prefix_returns_empty_for_vanity_host(): void {
		// Default fixture: https://s37u5z.infiniteuploads.cloud (no path).
		$this->assertSame( '', \ClikIT\InfiniteUploads\filter_eio_s3_object_prefix( '' ) );
	}

	public function test_filter_eio_s3_object_prefix_returns_path_for_path_style_cdn(): void {
		$instance          = new InfiniteUploads();
		$instance->bucket  = 'iup-usa/3672/mfgqglic/';
		$instance->cdn_url = 'https://cdn.infiniteuploads.cloud/123/456';
		InfiniteUploads::reset_instance_for_tests( $instance );

		$this->assertSame( '123/456', \ClikIT\InfiniteUploads\filter_eio_s3_object_prefix( '' ) );
	}

	public function test_filter_eio_s3_object_prefix_strips_trailing_slash(): void {
		$instance          = new InfiniteUploads();
		$instance->bucket  = 'iup-usa/3672/mfgqglic/';
		$instance->cdn_url = 'https://cdn.infiniteuploads.cloud/123/456/';
		InfiniteUploads::reset_instance_for_tests( $instance );

		$this->assertSame( '123/456', \ClikIT\InfiniteUploads\filter_eio_s3_object_prefix( '' ) );
	}

	public function test_filter_eio_s3_object_prefix_preserves_input_when_iu_disabled(): void {
		Functions\when( 'infinite_uploads_enabled' )->justReturn( false );

		$this->assertSame( '', \ClikIT\InfiniteUploads\filter_eio_s3_object_prefix( '' ) );
		$this->assertSame(
			'preserved/value',
			\ClikIT\InfiniteUploads\filter_eio_s3_object_prefix( 'preserved/value' )
		);
	}

	public function test_filter_eio_s3_object_prefix_preserves_input_when_easy_io_active(): void {
		Functions\when( 'ewww_image_optimizer_get_option' )->justReturn( true );

		$this->assertSame(
			'preserved',
			\ClikIT\InfiniteUploads\filter_eio_s3_object_prefix( 'preserved' )
		);
	}
}
