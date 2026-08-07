<?php
/**
 * Tests for the Ajax Load More repeater-path compatibility filter.
 *
 * Ajax Load More stores its repeater templates as PHP files under
 * uploads/alm_templates/, writing them with fopen( $file, 'w+' ) and loading
 * them with include(). The path comes from wp_upload_dir(), which IU rewrites
 * to iu://. The stream wrapper rejects the 'w+' mode (ALM's unchecked fwrite()
 * then fatals on activation) and PHP blocks includes through URL stream
 * wrappers (allow_url_include=0), so alm_repeater_path() must map the
 * directory back to local disk. It hooks `alm_repeater_path`, the single filter
 * AjaxLoadMore::alm_get_repeater_path() applies (verified in ALM 8.0.1), which
 * covers the activation write, alm_get_default_repeater() and the front-end
 * template lookup alike.
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
 * is where alm_repeater_path() and compatibility_exclusions() live.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class AlmRepeaterPathTest extends TestCase {

	private const BUCKET = 'iup-usa/4202/wv9s0vwv';

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

	public function test_maps_cloud_repeater_path_to_local_uploads_path(): void {
		$this->assertSame(
			$this->local_basedir() . '/alm_templates',
			$this->instance->alm_repeater_path( 'iu://' . self::BUCKET . '/alm_templates' )
		);
	}

	public function test_preserves_multisite_subpath_under_the_bucket(): void {
		$this->assertSame(
			$this->local_basedir() . '/sites/7/alm_templates',
			$this->instance->alm_repeater_path( 'iu://' . self::BUCKET . '/sites/7/alm_templates' )
		);
	}

	public function test_handles_bucket_with_trailing_slash(): void {
		$this->instance->bucket = self::BUCKET . '/';

		$this->assertSame(
			$this->local_basedir() . '/alm_templates',
			$this->instance->alm_repeater_path( 'iu://' . self::BUCKET . '/alm_templates' )
		);
	}

	public function test_leaves_local_path_untouched(): void {
		$local = $this->local_basedir() . '/alm_templates';

		$this->assertSame( $local, $this->instance->alm_repeater_path( $local ) );
	}

	public function test_leaves_other_buckets_untouched(): void {
		$other = 'iu://iup-eu/9999/otherbucket/alm_templates';

		$this->assertSame( $other, $this->instance->alm_repeater_path( $other ) );
	}

	public function test_sync_exclusions_include_alm_templates_folder(): void {
		$this->assertContains( '/alm_templates/', $this->instance->compatibility_exclusions( [] ) );
	}

	// ---------------------------------------------------------------------
	// Cache add-on (`alm_cache_path`). Commercial, not on wordpress.org, so
	// these pin our side of the contract only: the documented filter name and
	// the uploads/alm-cache default. If the add-on turns out to keep its cache
	// outside the bucket, the pass-through cases below are what save us.
	// ---------------------------------------------------------------------

	public function test_maps_cloud_cache_path_to_local_uploads_path(): void {
		$this->assertSame(
			$this->local_basedir() . '/alm-cache',
			$this->instance->alm_cache_path( 'iu://' . self::BUCKET . '/alm-cache' )
		);
	}

	public function test_preserves_trailing_slash_on_cache_path(): void {
		$this->assertSame(
			$this->local_basedir() . '/alm-cache/',
			$this->instance->alm_cache_path( 'iu://' . self::BUCKET . '/alm-cache/' )
		);
	}

	public function test_leaves_cache_path_outside_the_bucket_untouched(): void {
		// e.g. if the add-on defaults to wp-content/ rather than uploads/.
		$outside = WP_CONTENT_DIR . '/alm-cache';

		$this->assertSame( $outside, $this->instance->alm_cache_path( $outside ) );
	}

	public function test_sync_exclusions_include_alm_cache_folder(): void {
		$this->assertContains( '/alm-cache/', $this->instance->compatibility_exclusions( [] ) );
	}

	/**
	 * The three compatibility filters share cloud_path_to_local(); this pins
	 * that they stay behaviourally identical so a future edit to one can't
	 * silently diverge from the others.
	 */
	public function test_all_compat_path_filters_agree(): void {
		$cloud = 'iu://' . self::BUCKET . '/some-plugin-dir';
		$local = $this->local_basedir() . '/some-plugin-dir';

		$this->assertSame( $local, $this->instance->alm_repeater_path( $cloud ) );
		$this->assertSame( $local, $this->instance->alm_cache_path( $cloud ) );
		$this->assertSame( $local, $this->instance->wpwh_folder_base( $cloud ) );
	}
}
