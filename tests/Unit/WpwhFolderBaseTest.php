<?php
/**
 * Tests for the WP Webhooks Pro folder-base compatibility filter.
 *
 * WP Webhooks Pro stores PHP integration modules under
 * uploads/wp-webhooks-pro/ and require_once()s them from a path built on
 * wp_upload_dir() — which IU rewrites to iu://. PHP blocks includes through
 * URL stream wrappers (allow_url_include=0), so wpwh_folder_base() must map
 * the folder back to local disk. It hooks
 * `wpwhpro/integrations/get_wpwh_folder/folder_base` (verified in Pro 6.3.4),
 * which fires before WP Webhooks runs wp_mkdir_p()/index.php creation on the
 * path, so those writes land on local disk too.
 *
 * Path conventions (from tests/bootstrap.php):
 *   WP_CONTENT_DIR = sys_get_temp_dir() . '/iu-tests-wp-content'
 *   local basedir  = WP_CONTENT_DIR . '/uploads'
 *
 * @package ClikIT\InfiniteUploads\Tests\Unit
 */

declare( strict_types=1 );

namespace ClikIT\InfiniteUploads\Tests\Unit;

use Brain\Monkey\Functions;
use ClikIT\InfiniteUploads\InfiniteUploads;
use ClikIT\InfiniteUploads\Tests\TestCase;

/**
 * Runs in separate processes: the rest of the unit suite loads the lightweight
 * InfiniteUploads stub from tests/fixtures/ewww-environment.php, and PHP can't
 * hold both that stub and the real class (same FQCN) in one process. A fresh
 * process lets the autoloader resolve the REAL inc/InfiniteUploads.php, which
 * is where wpwh_folder_base() and compatibility_exclusions() live.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class WpwhFolderBaseTest extends TestCase {

	private const BUCKET = 'iup-usa/3672/mfgqglic';

	/**
	 * @var InfiniteUploads
	 */
	private $instance;

	protected function setUp(): void {
		parent::setUp();

		Functions\when( 'untrailingslashit' )->alias(
			static function ( $s ) {
				return rtrim( (string) $s, '/' );
			}
		);
		Functions\when( 'is_multisite' )->justReturn( false );
		// get_option('upload_path') returns '' → basedir falls through to WP_CONTENT_DIR/uploads.
		Functions\when( 'get_option' )->alias(
			static function ( $key, $default = false ) {
				return $default;
			}
		);

		$this->instance         = new InfiniteUploads();
		$this->instance->bucket = self::BUCKET;
	}

	private function local_basedir(): string {
		return WP_CONTENT_DIR . '/uploads';
	}

	public function test_maps_cloud_folder_base_to_local_uploads_path(): void {
		$this->assertSame(
			$this->local_basedir() . '/wp-webhooks-pro',
			$this->instance->wpwh_folder_base( 'iu://' . self::BUCKET . '/wp-webhooks-pro' )
		);
	}

	public function test_preserves_subpaths_under_the_bucket(): void {
		$this->assertSame(
			$this->local_basedir() . '/wp-webhooks-pro/integrations',
			$this->instance->wpwh_folder_base( 'iu://' . self::BUCKET . '/wp-webhooks-pro/integrations' )
		);
	}

	public function test_handles_bucket_with_trailing_slash(): void {
		$this->instance->bucket = self::BUCKET . '/';

		$this->assertSame(
			$this->local_basedir() . '/wp-webhooks-pro',
			$this->instance->wpwh_folder_base( 'iu://' . self::BUCKET . '/wp-webhooks-pro' )
		);
	}

	public function test_leaves_local_folder_untouched(): void {
		$local = $this->local_basedir() . '/wp-webhooks-pro';

		$this->assertSame( $local, $this->instance->wpwh_folder_base( $local ) );
	}

	public function test_leaves_other_buckets_untouched(): void {
		$other = 'iu://iup-eu/9999/otherbucket/wp-webhooks-pro';

		$this->assertSame( $other, $this->instance->wpwh_folder_base( $other ) );
	}

	public function test_sync_exclusions_include_wp_webhooks_folder(): void {
		$this->assertContains( '/wp-webhooks-pro/', $this->instance->compatibility_exclusions( [] ) );
	}
}
