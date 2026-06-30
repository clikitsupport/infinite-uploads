<?php
/**
 * Tests for ClikIT\InfiniteUploads\InfiniteUploadsHelper.
 *
 * Scope: pure-function behaviour and option-driven setters/getters. The
 * carve-out predicate is the single most-called helper in the BB cache flow,
 * so its truth-table is exhaustively covered here.
 *
 * @package ClikIT\InfiniteUploads\Tests\Unit
 */

declare( strict_types=1 );

namespace ClikIT\InfiniteUploads\Tests\Unit;

use Brain\Monkey\Functions;
use ClikIT\InfiniteUploads\InfiniteUploadsHelper;
use ClikIT\InfiniteUploads\Tests\TestCase;

require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsHelper.php';

class HelperTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		// wp_parse_url is used inside is_offloadable_bb_cache_image — Brain
		// Monkey alias to PHP's built-in works for all our test paths.
		Functions\when( 'wp_parse_url' )->alias(
			static function ( $url, $component = -1 ) {
				return \parse_url( $url, $component );
			}
		);
	}

	// -------------------------------------------------------------------------
	// is_offloadable_bb_cache_image — the BB carve-out predicate
	// -------------------------------------------------------------------------

	/**
	 * @dataProvider provide_bb_cache_image_truth_table
	 */
	public function test_is_offloadable_bb_cache_image( string $label, $path, bool $expected ): void {
		$this->assertSame(
			$expected,
			InfiniteUploadsHelper::is_offloadable_bb_cache_image( $path ),
			"case: {$label}"
		);
	}

	public function provide_bb_cache_image_truth_table(): array {
		return [
			'cropped jpg by path'           => [ 'cropped jpg', '/wp-content/uploads/bb-plugin/cache/653-thumb-300x200.jpg', true ],
			'cropped jpeg by path'          => [ 'cropped jpeg', '/wp-content/uploads/bb-plugin/cache/foo.jpeg', true ],
			'cropped png by path'           => [ 'cropped png', '/wp-content/uploads/bb-plugin/cache/653-hero-large.png', true ],
			'cropped webp by path'          => [ 'cropped webp', '/wp-content/uploads/bb-plugin/cache/760-bg-2560x1707.webp', true ],
			'cropped avif by path'          => [ 'cropped avif', '/wp-content/uploads/bb-plugin/cache/x.avif', true ],
			'cropped gif by path'           => [ 'cropped gif', '/wp-content/uploads/bb-plugin/cache/x.gif', true ],
			'cropped upper-case JPEG'       => [ 'upper-case ext', '/wp-content/uploads/bb-plugin/cache/X.JPEG', true ],
			'cropped jpg with query string' => [ 'jpg?ver', '/wp-content/uploads/bb-plugin/cache/x.jpg?ver=2', true ],
			'cropped jpg as URL form'       => [ 'jpg url', 'https://cdn.example.com/wp-content/uploads/bb-plugin/cache/x.jpg', true ],

			'layout css'                    => [ 'layout css', '/wp-content/uploads/bb-plugin/cache/653-layout.css', false ],
			'layout js'                     => [ 'layout js', '/wp-content/uploads/bb-plugin/cache/653-layout.js', false ],
			'layout draft css'              => [ 'draft css', '/wp-content/uploads/bb-plugin/cache/653-layout-draft.css', false ],

			'image outside bb-plugin'       => [ 'random jpg', '/wp-content/uploads/2026/03/photo.jpg', false ],
			'image in some other cache'     => [ 'other cache', '/wp-content/uploads/some-cache/foo.jpg', false ],
			'image in bb-plugin but not cache' => [ 'bb-plugin/other', '/wp-content/uploads/bb-plugin/other/foo.jpg', false ],

			'empty string'                  => [ 'empty', '', false ],
			'non-string null'               => [ 'null', null, false ],
			'non-string int'                => [ 'int', 42, false ],
			'no extension'                  => [ 'no ext', '/wp-content/uploads/bb-plugin/cache/Makefile', false ],
		];
	}

	// -------------------------------------------------------------------------
	// File exclusion settings
	// -------------------------------------------------------------------------

	public function test_get_file_exclusion_setting_default_no(): void {
		Functions\when( 'get_site_option' )->justReturn( 'no' );
		$this->assertSame( 'no', InfiniteUploadsHelper::get_file_exclusion_setting() );
	}

	public function test_is_file_exclusion_enabled_returns_true_when_setting_yes(): void {
		Functions\expect( 'get_site_option' )
			->once()
			->with( 'iu_file_exclusion_enabled', 'no' )
			->andReturn( 'yes' );

		$this->assertTrue( InfiniteUploadsHelper::is_file_exclusion_enabled() );
	}

	public function test_is_file_exclusion_enabled_returns_false_when_setting_no(): void {
		Functions\expect( 'get_site_option' )
			->once()
			->with( 'iu_file_exclusion_enabled', 'no' )
			->andReturn( 'no' );

		$this->assertFalse( InfiniteUploadsHelper::is_file_exclusion_enabled() );
	}

	public function test_set_file_exclusion_setting_accepts_yes_and_no(): void {
		Functions\expect( 'update_site_option' )
			->once()
			->with( 'iu_file_exclusion_enabled', 'yes' )
			->andReturn( true );

		$this->assertTrue( InfiniteUploadsHelper::set_file_exclusion_setting( 'yes' ) );
	}

	public function test_set_file_exclusion_setting_rejects_other_values(): void {
		// No update_site_option call expected — we should reject before persistence.
		$this->assertFalse( InfiniteUploadsHelper::set_file_exclusion_setting( 'maybe' ) );
		$this->assertFalse( InfiniteUploadsHelper::set_file_exclusion_setting( '' ) );
		$this->assertFalse( InfiniteUploadsHelper::set_file_exclusion_setting( 1 ) );
	}

	// -------------------------------------------------------------------------
	// Media folders setting
	// -------------------------------------------------------------------------

	public function test_is_media_folders_enabled_true_when_setting_yes(): void {
		Functions\when( 'get_site_option' )->justReturn( 'yes' );
		$this->assertTrue( InfiniteUploadsHelper::is_media_folders_enabled() );
	}

	public function test_is_media_folders_enabled_false_when_setting_no(): void {
		Functions\when( 'get_site_option' )->justReturn( 'no' );
		$this->assertFalse( InfiniteUploadsHelper::is_media_folders_enabled() );
	}
}
