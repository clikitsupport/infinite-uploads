<?php
/**
 * Tests for the WP All Import Pro functions-file compatibility filter.
 *
 * WP All Import Pro's Function Editor stores user PHP in
 * uploads/wpallimport/functions.php and loads it with require_once() from
 * Wpai\Integrations\CodeBox::requireFunctionsFile(), which runs on admin_init
 * for every All Import screen. The path comes from wp_upload_dir(), which IU
 * rewrites to iu://, and PHP refuses to include through a URL stream wrapper
 * unless allow_url_include is on -- which it must never be, since that would
 * let anyone able to place a .php file in the bucket execute it. The result is
 * a hard fatal that locks the user out of All Import entirely, so
 * wpai_functions_file_path() must map the file back to local disk.
 *
 * It hooks `import_functions_file_path`, the single filter WP All Import
 * applies at all four sites that touch the file (verified in Pro 4.11):
 * setup_allimport_dir()'s @touch, CodeBox::requireFunctionsFile()'s
 * require_once, CodeBox::revertToFunctionsFile()'s rename, and the
 * wp_ajax_save_import_functions save handler.
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
 * is where wpai_functions_file_path() and compatibility_exclusions() live.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class WpaiFunctionsFilePathTest extends TestCase {

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

	/**
	 * The reported failure: the exact path from the customer's fatal.
	 */
	public function test_maps_cloud_functions_file_to_local_uploads_path(): void {
		$this->assertSame(
			$this->local_basedir() . '/wpallimport/functions.php',
			$this->instance->wpai_functions_file_path(
				'iu://' . self::BUCKET . '/wpallimport/functions.php'
			)
		);
	}

	/**
	 * CodeBox derives the backup file by str_replace()ing '.php' on the already
	 * filtered value, so it must map cleanly too.
	 */
	public function test_maps_the_backup_functions_file(): void {
		$this->assertSame(
			$this->local_basedir() . '/wpallimport/functions_backup.php',
			$this->instance->wpai_functions_file_path(
				'iu://' . self::BUCKET . '/wpallimport/functions_backup.php'
			)
		);
	}

	public function test_preserves_multisite_subpath_under_the_bucket(): void {
		$this->assertSame(
			$this->local_basedir() . '/sites/7/wpallimport/functions.php',
			$this->instance->wpai_functions_file_path(
				'iu://' . self::BUCKET . '/sites/7/wpallimport/functions.php'
			)
		);
	}

	public function test_handles_bucket_with_trailing_slash(): void {
		$this->instance->bucket = self::BUCKET . '/';

		$this->assertSame(
			$this->local_basedir() . '/wpallimport/functions.php',
			$this->instance->wpai_functions_file_path(
				'iu://' . self::BUCKET . '/wpallimport/functions.php'
			)
		);
	}

	/**
	 * A site that is not offloaded, or one where the user has already pointed
	 * the filter somewhere else, must pass through untouched.
	 */
	public function test_leaves_local_path_untouched(): void {
		$local = $this->local_basedir() . '/wpallimport/functions.php';

		$this->assertSame( $local, $this->instance->wpai_functions_file_path( $local ) );
	}

	public function test_leaves_other_buckets_untouched(): void {
		$other = 'iu://iup-eu/9999/otherbucket/wpallimport/functions.php';

		$this->assertSame( $other, $this->instance->wpai_functions_file_path( $other ) );
	}

	public function test_sync_exclusions_include_wpallimport_folder(): void {
		$this->assertContains( '/wpallimport/', $this->instance->compatibility_exclusions( [] ) );
	}

	/**
	 * The compatibility path filters all share cloud_path_to_local(); this pins
	 * that they stay behaviourally identical so a future edit to one can't
	 * silently diverge from the others.
	 */
	public function test_all_compat_path_filters_agree(): void {
		$cloud = 'iu://' . self::BUCKET . '/some-plugin-dir';
		$local = $this->local_basedir() . '/some-plugin-dir';

		$this->assertSame( $local, $this->instance->wpai_functions_file_path( $cloud ) );
		$this->assertSame( $local, $this->instance->alm_repeater_path( $cloud ) );
		$this->assertSame( $local, $this->instance->alm_cache_path( $cloud ) );
		$this->assertSame( $local, $this->instance->wpwh_folder_base( $cloud ) );
	}
}
