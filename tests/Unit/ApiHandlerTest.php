<?php
/**
 * Tests for InfiniteUploadsApiHandler — the HTTP client used to communicate
 * with the IU backend.
 *
 * Coverage focuses on the methods that are most likely to break silently:
 *   - token / site_id storage + retrieval (option round-trip)
 *   - URL construction (`rest_url`, `network_site_url`, `network_home_url`)
 *   - `has_token` checks
 *   - `get_site_data` caching behaviour (fresh-cache hit, force-refresh,
 *     lock contention fallback, no-token early return)
 *
 * Constructor side-effects (cron scheduling, InfiniteUploads::get_instance,
 * action registration) are skipped via newInstanceWithoutConstructor().
 *
 * @package ClikIT\InfiniteUploads\Tests\Unit
 */

declare( strict_types=1 );

namespace ClikIT\InfiniteUploads\Tests\Unit;

use Brain\Monkey\Functions;
use ClikIT\InfiniteUploads\InfiniteUploadsApiHandler;
use ClikIT\InfiniteUploads\Tests\TestCase;
use ReflectionClass;

if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
	define( 'HOUR_IN_SECONDS', 3600 );
}

class ApiHandlerTest extends TestCase {

	/**
	 * @var InfiniteUploadsApiHandler
	 */
	private $api;

	/**
	 * @var ReflectionClass
	 */
	private $reflection;

	protected function setUp(): void {
		parent::setUp();

		// Load the REAL class (replacing the fixture stub we used elsewhere).
		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsApiHandler.php';

		$this->reflection = new ReflectionClass(
			'\\ClikIT\\InfiniteUploads\\InfiniteUploadsApiHandler'
		);
		$this->api        = $this->reflection->newInstanceWithoutConstructor();

		// Default property values (constructor would set them).
		$this->set_prop( 'server_root', 'https://infiniteuploads.com/' );
		$this->set_prop( 'rest_api', 'api/v1/' );
		$this->set_prop( 'server_url', 'https://infiniteuploads.com/api/v1/' );
		$this->set_prop( 'api_token', '' );
		$this->set_prop( 'api_site_id', '' );

		Functions\when( 'trailingslashit' )->alias(
			static function ( $s ) {
				return rtrim( (string) $s, '/' ) . '/';
			}
		);
		Functions\when( 'apply_filters' )->returnArg( 2 );
	}

	private function set_prop( string $name, $value ): void {
		$prop = $this->reflection->getProperty( $name );
		$prop->setAccessible( true );
		$prop->setValue( $this->api, $value );
	}

	// -------------------------------------------------------------------------
	// has_token / get_token / set_token
	// -------------------------------------------------------------------------

	public function test_has_token_returns_false_when_empty(): void {
		$this->set_prop( 'api_token', '' );
		$this->assertFalse( $this->api->has_token() );
	}

	public function test_has_token_returns_true_when_set(): void {
		$this->set_prop( 'api_token', 'abc123' );
		$this->assertTrue( $this->api->has_token() );
	}

	public function test_get_token_returns_stored_value(): void {
		$this->set_prop( 'api_token', 'my-token' );
		$this->assertSame( 'my-token', $this->api->get_token() );
	}

	public function test_set_token_updates_property_and_site_option(): void {
		$saved_value = null;
		Functions\when( 'update_site_option' )->alias(
			static function ( $key, $value ) use ( &$saved_value ) {
				if ( $key === 'iup_apitoken' ) {
					$saved_value = $value;
				}
				return true;
			}
		);

		$this->api->set_token( 'new-token-xyz' );

		$this->assertSame( 'new-token-xyz', $this->api->get_token() );
		$this->assertSame( 'new-token-xyz', $saved_value, 'token must persist via site option' );
	}

	// -------------------------------------------------------------------------
	// site_id getter / setter
	// -------------------------------------------------------------------------

	public function test_set_site_id_updates_property_and_site_option(): void {
		$saved = null;
		Functions\when( 'update_site_option' )->alias(
			static function ( $key, $value ) use ( &$saved ) {
				if ( $key === 'iup_site_id' ) {
					$saved = $value;
				}
				return true;
			}
		);

		$this->api->set_site_id( 4242 );

		$this->assertSame( 4242, $this->api->get_site_id() );
		$this->assertSame( 4242, $saved );
	}

	// -------------------------------------------------------------------------
	// rest_url — URL construction
	// -------------------------------------------------------------------------

	public function test_rest_url_appends_endpoint_to_server_url(): void {
		$this->assertSame(
			'https://infiniteuploads.com/api/v1/site/42',
			$this->api->rest_url( 'site/42' )
		);
	}

	public function test_rest_url_passes_absolute_urls_through(): void {
		$this->assertSame(
			'https://example.test/other-api/endpoint',
			$this->api->rest_url( 'https://example.test/other-api/endpoint' )
		);
		$this->assertSame(
			'http://insecure.test/x',
			$this->api->rest_url( 'http://insecure.test/x' )
		);
	}

	// -------------------------------------------------------------------------
	// network_site_url / network_home_url — constant overrides
	// -------------------------------------------------------------------------

	public function test_network_site_url_returns_wp_value_by_default(): void {
		Functions\when( 'network_site_url' )->justReturn( 'http://test-site.example/' );

		$this->assertSame( 'http://test-site.example/', $this->api->network_site_url() );
	}

	public function test_network_home_url_returns_wp_value_by_default(): void {
		Functions\when( 'network_home_url' )->justReturn( 'http://test-home.example/' );

		$this->assertSame( 'http://test-home.example/', $this->api->network_home_url() );
	}

	// -------------------------------------------------------------------------
	// get_site_data — caching behaviour
	// -------------------------------------------------------------------------

	public function test_get_site_data_returns_false_when_no_token(): void {
		$this->set_prop( 'api_token', '' );
		$this->set_prop( 'api_site_id', 1 );

		$this->assertFalse( $this->api->get_site_data() );
	}

	public function test_get_site_data_returns_false_when_no_site_id(): void {
		$this->set_prop( 'api_token', 'token' );
		$this->set_prop( 'api_site_id', '' );

		$this->assertFalse( $this->api->get_site_data() );
	}

	public function test_get_site_data_returns_cached_data_when_fresh(): void {
		$this->set_prop( 'api_token', 'token' );
		$this->set_prop( 'api_site_id', 100 );

		$fresh_cache = (object) [
			'refreshed' => time() - 60, // 1 minute ago — well within 12-hour window
			'site'      => (object) [ 'cdn_url' => 'cached.example.com' ],
		];

		Functions\when( 'get_site_option' )->alias(
			static function ( $key ) use ( $fresh_cache ) {
				if ( $key === 'iup_api_data' ) {
					return json_encode( $fresh_cache );
				}
				return false;
			}
		);

		$result = $this->api->get_site_data();

		$this->assertIsObject( $result );
		$this->assertSame( 'cached.example.com', $result->site->cdn_url );
	}

	public function test_get_site_data_returns_false_when_lock_held_and_no_cache(): void {
		$this->set_prop( 'api_token', 'token' );
		$this->set_prop( 'api_site_id', 100 );

		Functions\when( 'get_site_option' )->justReturn( false );
		// Lock present → another request is already fetching.
		Functions\when( 'get_transient' )->justReturn( 1 );

		$this->assertFalse( $this->api->get_site_data() );
	}

	public function test_get_site_data_returns_stale_cache_when_lock_held(): void {
		$this->set_prop( 'api_token', 'token' );
		$this->set_prop( 'api_site_id', 100 );

		$stale_cache = (object) [
			'refreshed' => time() - ( 24 * 3600 ),  // 24 hours ago — past the 12-hour TTL
			'site'      => (object) [ 'cdn_url' => 'stale.example.com' ],
		];

		Functions\when( 'get_site_option' )->alias(
			static function ( $key ) use ( $stale_cache ) {
				return $key === 'iup_api_data' ? json_encode( $stale_cache ) : false;
			}
		);
		Functions\when( 'get_transient' )->justReturn( 1 );

		$result = $this->api->get_site_data();

		$this->assertIsObject( $result );
		$this->assertSame(
			'stale.example.com',
			$result->site->cdn_url,
			'Lock-held + stale cache must return the stale data rather than re-fetching'
		);
	}
}
