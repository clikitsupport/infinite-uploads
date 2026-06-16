<?php
/**
 * Tests for InfiniteUploadsFilelist::is_excluded — the scanner's
 * include/exclude decision for each file it walks.
 *
 * The BB carve-out (added in 3.2.4) is the most important behavioural
 * branch: cropped images under /bb-plugin/cache/ short-circuit the exclusion
 * list so they get offloaded, while the same folder's .css/.js stays excluded.
 *
 * @package ClikIT\InfiniteUploads\Tests\Unit
 */

declare( strict_types=1 );

namespace ClikIT\InfiniteUploads\Tests\Unit;

use Brain\Monkey\Functions;
use ClikIT\InfiniteUploads\InfiniteUploadsFilelist;
use ClikIT\InfiniteUploads\Tests\TestCase;
use ReflectionClass;

class FilelistTest extends TestCase {

	/**
	 * @var InfiniteUploadsFilelist
	 */
	private $filelist;

	/**
	 * @var ReflectionClass
	 */
	private $reflection;

	protected function setUp(): void {
		parent::setUp();

		// Constructor calls InfiniteUploads::get_instance() — bypass it with
		// newInstanceWithoutConstructor.
		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsHelper.php';
		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsFilelist.php';

		$this->reflection = new ReflectionClass( InfiniteUploadsFilelist::class );
		$this->filelist   = $this->reflection->newInstanceWithoutConstructor();

		Functions\when( 'wp_parse_url' )->alias(
			static function ( $url, $component = -1 ) {
				return \parse_url( $url, $component );
			}
		);

		// apply_filters('infinite_uploads_sync_exclusions', ...) is invoked
		// from is_excluded — pass through unchanged.
		Functions\when( 'apply_filters' )->returnArg( 2 );
	}

	private function invoke_is_excluded( string $path ): bool {
		$method = $this->reflection->getMethod( 'is_excluded' );
		$method->setAccessible( true );
		return $method->invoke( $this->filelist, $path );
	}

	// -------------------------------------------------------------------------
	// Default exclusions still trigger
	// -------------------------------------------------------------------------

	/**
	 * @dataProvider provide_default_exclusion_paths
	 */
	public function test_default_exclusions_match( string $label, string $path ): void {
		$this->assertTrue(
			$this->invoke_is_excluded( $path ),
			"path '{$path}' ({$label}) should be excluded by the default rules"
		);
	}

	public function provide_default_exclusion_paths(): array {
		return [
			[ 'et-cache', '/var/www/wp-content/uploads/et-cache/whatever.css' ],
			[ 'et_temp', '/var/www/wp-content/uploads/et_temp/x' ],
			[ 'imports', '/var/www/wp-content/uploads/imports/x.json' ],
			[ 'wp-defender', '/var/www/wp-content/uploads/wp-defender/log.txt' ],
			[ 'DS_Store', '/var/www/wp-content/uploads/.DS_Store' ],
			[ 'wc-logs', '/var/www/wp-content/uploads/wc-logs/woocommerce.log' ],
			[ 'php-errors', '/var/www/wp-content/uploads/php-errors/x' ],
			[ 'error_log', '/var/www/wp-content/error_log' ],
			[ 'debug.log', '/var/www/wp-content/debug.log' ],
			[ '.log suffix', '/var/www/wp-content/uploads/whatever.log' ],
			[ '/logs/ dir', '/var/www/wp-content/uploads/logs/anything' ],
			[ '/tmp/ dir', '/var/www/wp-content/uploads/tmp/anything' ],
			[ '/temp/ dir', '/var/www/wp-content/uploads/temp/anything' ],
			[ '/cache/ dir', '/var/www/wp-content/uploads/cache/file.png' ],
		];
	}

	// -------------------------------------------------------------------------
	// Non-excluded paths pass through
	// -------------------------------------------------------------------------

	/**
	 * @dataProvider provide_non_excluded_paths
	 */
	public function test_paths_outside_exclusion_rules_pass_through( string $label, string $path ): void {
		$this->assertFalse(
			$this->invoke_is_excluded( $path ),
			"path '{$path}' ({$label}) should NOT be excluded"
		);
	}

	public function provide_non_excluded_paths(): array {
		return [
			[ 'attachment jpg', '/var/www/wp-content/uploads/2026/03/photo.jpg' ],
			[ 'attachment pdf', '/var/www/wp-content/uploads/2026/03/doc.pdf' ],
			[ 'subdir png', '/var/www/wp-content/uploads/sites/2/photo.png' ],
		];
	}

	// -------------------------------------------------------------------------
	// BB carve-out
	// -------------------------------------------------------------------------

	/**
	 * Cropped image inside /bb-plugin/cache/ overrides BOTH the default
	 * '/cache/' exclusion and the compat '/bb-plugin/' exclusion — the carve
	 * happens before the exclusion-list loop.
	 *
	 * @dataProvider provide_bb_cache_carved_out
	 */
	public function test_bb_cache_image_short_circuits_exclusions( string $label, string $path ): void {
		$this->assertFalse(
			$this->invoke_is_excluded( $path ),
			"BB cache image '{$path}' ({$label}) should be carved out and INCLUDED for offload"
		);
	}

	public function provide_bb_cache_carved_out(): array {
		return [
			[ 'cropped jpg', '/var/www/wp-content/uploads/bb-plugin/cache/653-thumb-300x200.jpg' ],
			[ 'cropped png', '/var/www/wp-content/uploads/bb-plugin/cache/653-hero-large.png' ],
			[ 'cropped webp', '/var/www/wp-content/uploads/bb-plugin/cache/760-bg-2560x1707.webp' ],
			[ 'cropped jpeg', '/var/www/wp-content/uploads/bb-plugin/cache/x.jpeg' ],
			[ 'cropped avif', '/var/www/wp-content/uploads/bb-plugin/cache/x.avif' ],
			[ 'uppercase ext', '/var/www/wp-content/uploads/bb-plugin/cache/X.JPEG' ],
		];
	}

	/**
	 * Layout assets under /bb-plugin/cache/ stay EXCLUDED — BB regenerates
	 * these frequently and offloading would cause sync churn.
	 *
	 * @dataProvider provide_bb_cache_still_excluded
	 */
	public function test_bb_cache_layout_assets_remain_excluded( string $label, string $path ): void {
		$this->assertTrue(
			$this->invoke_is_excluded( $path ),
			"BB cache layout asset '{$path}' ({$label}) should stay excluded"
		);
	}

	public function provide_bb_cache_still_excluded(): array {
		return [
			[ 'layout css', '/var/www/wp-content/uploads/bb-plugin/cache/653-layout.css' ],
			[ 'layout js', '/var/www/wp-content/uploads/bb-plugin/cache/653-layout.js' ],
			[ 'draft css', '/var/www/wp-content/uploads/bb-plugin/cache/653-layout-draft.css' ],
			[ 'partial js', '/var/www/wp-content/uploads/bb-plugin/cache/653-layout-partial.js' ],
		];
	}

	// -------------------------------------------------------------------------
	// Filter integration — third-party plugins can extend the exclusion list
	// -------------------------------------------------------------------------

	public function test_custom_exclusions_added_via_filter_are_honoured(): void {
		// Override the apply_filters mock to inject a custom exclusion.
		Functions\when( 'apply_filters' )->alias(
			static function ( $hook, $value ) {
				if ( $hook === 'infinite_uploads_sync_exclusions' && is_array( $value ) ) {
					$value[] = '/custom-skip/';
				}
				return $value;
			}
		);

		$this->assertTrue(
			$this->invoke_is_excluded( '/var/www/wp-content/uploads/custom-skip/file.txt' )
		);
		// And confirm an unrelated path is still passed through.
		$this->assertFalse(
			$this->invoke_is_excluded( '/var/www/wp-content/uploads/2026/03/x.jpg' )
		);
	}
}
