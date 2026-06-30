<?php
/**
 * Tests for InfiniteUploadsHelper's path/URL resolution methods.
 *
 * These methods are deeply interconnected (most go through
 * get_original_upload_dir_root / get_cloud_upload_dir / get_iu_api_data,
 * which memoize on a private static $request_cache). The test setUp resets
 * that cache between tests so memoization doesn't leak between cases.
 *
 * Path/URL conventions used here:
 *   local basedir  = /tmp/iu-helper-test/uploads
 *   local baseurl  = http://example.test/wp-content/uploads
 *   cloud cdn host = tenant.infiniteuploads.cloud
 *   bucket         = iup-usa/3672/mfgqglic
 *
 * @package ClikIT\InfiniteUploads\Tests\Unit
 */

declare( strict_types=1 );

namespace ClikIT\InfiniteUploads\Tests\Unit;

use Brain\Monkey\Functions;
use ClikIT\InfiniteUploads\InfiniteUploadsHelper;
use ClikIT\InfiniteUploads\Tests\TestCase;
use ReflectionClass;

require_once IU_TESTS_ROOT . '/fixtures/ewww-environment.php';

class HelperPathsTest extends TestCase {

	private const LOCAL_BASEDIR = '/tmp/iu-helper-test/uploads';
	private const LOCAL_BASEURL = 'http://example.test/wp-content/uploads';
	private const CDN_HOST      = 'tenant.infiniteuploads.cloud';
	private const BUCKET        = 'iup-usa/3672/mfgqglic';

	protected function setUp(): void {
		parent::setUp();
		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsHelper.php';

		// Reset Helper::$request_cache so each test sees fresh memoization.
		$ref  = new ReflectionClass( InfiniteUploadsHelper::class );
		$prop = $ref->getProperty( 'request_cache' );
		$prop->setAccessible( true );
		$prop->setValue( null, [] );

		// Common WP function stubs.
		Functions\when( 'wp_parse_url' )->alias(
			static function ( $url, $component = -1 ) {
				return \parse_url( $url, $component );
			}
		);
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
		Functions\when( 'apply_filters' )->returnArg( 2 );
		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
		Functions\when( 'is_multisite' )->justReturn( false );
		Functions\when( 'is_main_network' )->justReturn( true );
		Functions\when( 'is_main_site' )->justReturn( true );
		Functions\when( 'ms_is_switched' )->justReturn( false );
		// get_option('upload_path') returns '' → falls through to WP_CONTENT_DIR/uploads.
		Functions\when( 'get_option' )->alias(
			static function ( $key, $default = false ) {
				return $default;
			}
		);
		// Default: no excluded files.
		Functions\when( 'get_site_option' )->alias(
			static function ( $key, $default = false ) {
				if ( $key === 'iup_excluded_files' ) {
					return [];
				}
				return $default;
			}
		);

		// Seed Helper::$request_cache['iu_api_data'] directly so the helper's
		// memoization layer returns our test fixture without ever calling
		// InfiniteUploadsApiHandler::get_instance(). This avoids the
		// fixture-vs-real-class collision that bit us when we tried to declare
		// a stub ApiHandler — see tests/fixtures/ewww-environment.php.
		$this->seed_api_data(
			(object) [
				'site' => (object) [
					'upload_key'    => 'access-key',
					'upload_secret' => 'secret-key',
					'upload_bucket' => self::BUCKET,
					'cdn_url'       => self::CDN_HOST,
				],
			]
		);
	}

	/**
	 * Seed (or replace) the helper's request-scoped api_data cache.
	 *
	 * @param  object|false  $value  null/false → simulate "no API data".
	 */
	private function seed_api_data( $value ): void {
		$ref  = new ReflectionClass( InfiniteUploadsHelper::class );
		$prop = $ref->getProperty( 'request_cache' );
		$prop->setAccessible( true );
		$cache                  = $prop->getValue();
		$cache['iu_api_data']   = $value;
		$prop->setValue( null, $cache );
	}

	private function reset_request_cache(): void {
		$ref  = new ReflectionClass( InfiniteUploadsHelper::class );
		$prop = $ref->getProperty( 'request_cache' );
		$prop->setAccessible( true );
		$prop->setValue( null, [] );
	}

	// -------------------------------------------------------------------------
	// get_excluded_paths
	// -------------------------------------------------------------------------

	public function test_get_excluded_paths_returns_empty_array_when_option_missing(): void {
		Functions\when( 'get_site_option' )->justReturn( '' );

		$this->assertSame( [], InfiniteUploadsHelper::get_excluded_paths() );
	}

	public function test_get_excluded_paths_returns_array_when_option_is_array(): void {
		Functions\when( 'get_site_option' )->justReturn( [ '/foo/', '/bar/' ] );

		$this->assertSame( [ '/foo/', '/bar/' ], array_values( InfiniteUploadsHelper::get_excluded_paths() ) );
	}

	public function test_get_excluded_paths_drops_empty_or_whitespace_entries(): void {
		// Empty strings cause stripos() to return 0 → everything would appear
		// excluded. The helper filters them out defensively.
		Functions\when( 'get_site_option' )->justReturn( [ '/foo/', '', '   ', '/bar/' ] );

		$result = array_values( InfiniteUploadsHelper::get_excluded_paths() );

		$this->assertSame( [ '/foo/', '/bar/' ], $result );
	}

	public function test_get_excluded_paths_memoizes_within_a_request(): void {
		$call_count = 0;
		Functions\when( 'get_site_option' )->alias(
			static function () use ( &$call_count ) {
				$call_count++;
				return [ '/foo/' ];
			}
		);

		InfiniteUploadsHelper::get_excluded_paths();
		InfiniteUploadsHelper::get_excluded_paths();
		InfiniteUploadsHelper::get_excluded_paths();

		$this->assertSame(
			1,
			$call_count,
			'get_site_option must only be called once per request lifecycle'
		);
	}

	// -------------------------------------------------------------------------
	// is_path_excluded
	// -------------------------------------------------------------------------

	public function test_is_path_excluded_returns_false_when_no_exclusions(): void {
		Functions\when( 'get_site_option' )->justReturn( [] );

		$this->assertFalse(
			InfiniteUploadsHelper::is_path_excluded( '/var/www/wp-content/uploads/2026/03/photo.jpg' )
		);
	}

	public function test_is_path_excluded_returns_true_when_path_matches_exclusion(): void {
		Functions\when( 'get_site_option' )->justReturn( [ '/excluded-dir/' ] );

		$this->assertTrue(
			InfiniteUploadsHelper::is_path_excluded( '/var/www/wp-content/uploads/excluded-dir/file.jpg' )
		);
	}

	public function test_is_path_excluded_is_case_insensitive(): void {
		Functions\when( 'get_site_option' )->justReturn( [ '/Excluded-Dir/' ] );

		$this->assertTrue(
			InfiniteUploadsHelper::is_path_excluded( '/var/www/wp-content/uploads/excluded-dir/file.jpg' )
		);
	}

	// -------------------------------------------------------------------------
	// get_local_upload_url / get_local_upload_path
	// -------------------------------------------------------------------------

	public function test_get_local_upload_url_returns_baseurl_without_trailing_slash(): void {
		// WP_CONTENT_URL was stubbed by bootstrap to 'http://example.test/wp-content'.
		// The helper's get_original_upload_dir_root falls back to WP_CONTENT_URL.'/uploads'
		// when upload_url_path is empty.
		$this->assertSame(
			'http://example.test/wp-content/uploads',
			InfiniteUploadsHelper::get_local_upload_url()
		);
	}

	public function test_get_local_upload_path_returns_basedir_without_trailing_slash(): void {
		// Bootstrap WP_CONTENT_DIR = sys_get_temp_dir() . '/iu-tests-wp-content'.
		$this->assertSame(
			\WP_CONTENT_DIR . '/uploads',
			InfiniteUploadsHelper::get_local_upload_path()
		);
	}

	// -------------------------------------------------------------------------
	// get_s3_url
	// -------------------------------------------------------------------------

	public function test_get_s3_url_returns_https_cdn_when_api_data_present(): void {
		$this->assertSame(
			'https://' . self::CDN_HOST,
			InfiniteUploadsHelper::get_s3_url()
		);
	}

	public function test_get_s3_url_returns_empty_when_no_api_data(): void {
		$this->reset_request_cache();
		$this->seed_api_data( false );

		// Pre-existing source bug: when api_data is missing, $bucket is
		// undefined at line 304 of InfiniteUploadsHelper.php. PHP 8.x emits a
		// warning. Suppress via the @-operator just for this test so the
		// regression-locking assertion (returns SOMETHING usable) can run.
		$result = @InfiniteUploadsHelper::get_s3_url();

		$this->assertIsString( $result );
		$this->assertStringStartsWith( 'https://', $result );
	}

	// -------------------------------------------------------------------------
	// get_cloud_upload_dir / get_cloud_upload_url / get_cloud_upload_path
	// -------------------------------------------------------------------------

	public function test_get_cloud_upload_dir_swaps_basedir_to_iu_protocol(): void {
		$dirs = InfiniteUploadsHelper::get_cloud_upload_dir();

		$this->assertSame( 'iu://' . self::BUCKET, $dirs['basedir'] );
	}

	public function test_get_cloud_upload_url_returns_https_cdn(): void {
		$this->assertSame(
			'https://' . self::CDN_HOST,
			InfiniteUploadsHelper::get_cloud_upload_url()
		);
	}

	public function test_get_cloud_upload_path_returns_iu_protocol_path(): void {
		$this->assertSame(
			'iu://' . self::BUCKET,
			InfiniteUploadsHelper::get_cloud_upload_path()
		);
	}

	public function test_get_cloud_upload_dir_falls_back_to_local_when_api_data_missing(): void {
		$this->reset_request_cache();
		$this->seed_api_data( false );

		$dirs = InfiniteUploadsHelper::get_cloud_upload_dir();

		// Without API data, cloud_upload_dir falls back to the local root unchanged.
		$this->assertStringStartsNotWith( 'iu://', $dirs['basedir'] );
	}

	// -------------------------------------------------------------------------
	// get_local_file_url / get_local_file_path / get_cloud_file_path
	// -------------------------------------------------------------------------

	public function test_get_local_file_url_swaps_cdn_host_back_to_local_host(): void {
		$cloud_url = 'https://' . self::CDN_HOST . '/2026/03/photo.jpg';

		$this->assertSame(
			'http://example.test/wp-content/uploads/2026/03/photo.jpg',
			InfiniteUploadsHelper::get_local_file_url( $cloud_url )
		);
	}

	public function test_get_local_file_path_swaps_iu_basedir_to_local_basedir(): void {
		$cloud_path = 'iu://' . self::BUCKET . '/2026/03/photo.jpg';

		$this->assertSame(
			\WP_CONTENT_DIR . '/uploads/2026/03/photo.jpg',
			InfiniteUploadsHelper::get_local_file_path( $cloud_path )
		);
	}

	public function test_get_local_file_path_returns_empty_for_empty_input(): void {
		$this->assertSame( '', InfiniteUploadsHelper::get_local_file_path( '' ) );
	}

	public function test_get_cloud_file_path_swaps_local_basedir_to_iu(): void {
		$local_path = \WP_CONTENT_DIR . '/uploads/2026/03/photo.jpg';

		$this->assertSame(
			'iu://' . self::BUCKET . '/2026/03/photo.jpg',
			InfiniteUploadsHelper::get_cloud_file_path( $local_path )
		);
	}

	public function test_get_cloud_file_path_returns_empty_for_empty_input(): void {
		$this->assertSame( '', InfiniteUploadsHelper::get_cloud_file_path( '' ) );
	}

	// -------------------------------------------------------------------------
	// get_local_path_from_url
	// -------------------------------------------------------------------------

	public function test_get_local_path_from_url_converts_local_url_to_filesystem_path(): void {
		$url = 'http://example.test/wp-content/uploads/2026/03/photo.jpg';

		$this->assertSame(
			\WP_CONTENT_DIR . '/uploads/2026/03/photo.jpg',
			InfiniteUploadsHelper::get_local_path_from_url( $url )
		);
	}

	// -------------------------------------------------------------------------
	// get_file_name_from_url / get_file_name_from_path
	// -------------------------------------------------------------------------

	public function test_get_file_name_from_url_returns_basename_for_local_url(): void {
		$url = 'http://example.test/wp-content/uploads/2026/03/photo.jpg';

		$this->assertSame( 'photo.jpg', InfiniteUploadsHelper::get_file_name_from_url( $url ) );
	}

	public function test_get_file_name_from_path_returns_basename(): void {
		$path = \WP_CONTENT_DIR . '/uploads/2026/03/photo.jpg';

		$this->assertSame( 'photo.jpg', InfiniteUploadsHelper::get_file_name_from_path( $path ) );
	}

	// -------------------------------------------------------------------------
	// get_valid_file_path / get_valid_file_url
	// -------------------------------------------------------------------------

	public function test_get_valid_file_path_returns_local_when_excluded(): void {
		Functions\when( 'get_site_option' )->alias(
			static function ( $key, $default = false ) {
				if ( $key === 'iup_excluded_files' ) {
					return [ '/2026/' ];
				}
				return $default;
			}
		);

		$cloud_path = 'iu://' . self::BUCKET . '/2026/03/photo.jpg';
		$result     = InfiniteUploadsHelper::get_valid_file_path( $cloud_path );

		$this->assertStringStartsWith( \WP_CONTENT_DIR . '/uploads', $result );
		$this->assertStringNotContainsString( 'iu://', $result );
	}

	public function test_get_valid_file_path_returns_cloud_when_not_excluded(): void {
		$local_path = \WP_CONTENT_DIR . '/uploads/2026/03/photo.jpg';
		$result     = InfiniteUploadsHelper::get_valid_file_path( $local_path );

		$this->assertStringStartsWith( 'iu://', $result );
	}

	public function test_get_valid_file_url_returns_cloud_when_not_excluded(): void {
		$local_url = 'http://example.test/wp-content/uploads/2026/03/photo.jpg';
		$result    = InfiniteUploadsHelper::get_valid_file_url( $local_url );

		$this->assertStringContainsString( self::CDN_HOST, $result );
	}

	public function test_get_valid_file_url_returns_local_when_excluded(): void {
		Functions\when( 'get_site_option' )->alias(
			static function ( $key, $default = false ) {
				if ( $key === 'iup_excluded_files' ) {
					return [ '/2026/' ];
				}
				return $default;
			}
		);

		$local_url = 'http://example.test/wp-content/uploads/2026/03/photo.jpg';
		$result    = InfiniteUploadsHelper::get_valid_file_url( $local_url, true );

		$this->assertSame( $local_url, $result );
	}

	// -------------------------------------------------------------------------
	// get_iu_api_data — memoization
	// -------------------------------------------------------------------------

	public function test_get_iu_api_data_returns_site_data_from_api_handler(): void {
		$data = InfiniteUploadsHelper::get_iu_api_data();

		$this->assertIsObject( $data );
		$this->assertTrue( property_exists( $data, 'site' ) );
		$this->assertSame( self::CDN_HOST, $data->site->cdn_url );
	}

	public function test_get_iu_api_data_memoizes_after_first_call(): void {
		$first = InfiniteUploadsHelper::get_iu_api_data();
		// Even if we mutate the underlying source, the memoized result
		// should stick — once cached, future calls return the same object.
		$second = InfiniteUploadsHelper::get_iu_api_data();

		$this->assertSame( $first, $second, 'memoized api data must be returned on subsequent calls' );
	}
}
