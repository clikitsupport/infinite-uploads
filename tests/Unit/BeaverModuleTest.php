<?php
/**
 * Tests for _iu_bb_get_folder_options() — the Beaver Builder gallery module
 * dropdown helper. Lives in inc/gallery/integrations/BeaverModule.php and
 * caches its result for 5 minutes (added in 3.2.4 as a perf fix; previously
 * it ran a SELECT on every WP request that loaded the integration).
 *
 * Loading the source file requires a stub FLBuilderModule because the file
 * early-returns when that class isn't present.
 *
 * @package ClikIT\InfiniteUploads\Tests\Unit
 */

declare( strict_types=1 );

namespace ClikIT\InfiniteUploads\Tests\Unit;

use Brain\Monkey\Functions;
use ClikIT\InfiniteUploads\Tests\TestCase;

// FLBuilderModule stub — must be declared BEFORE the BeaverModule.php file
// is required, since that file early-returns if FLBuilderModule isn't loaded.
if ( ! class_exists( '\FLBuilderModule' ) ) {
	class_alias( __NAMESPACE__ . '\\_BeaverModuleTestFLBuilderModule', 'FLBuilderModule' );
}

class _BeaverModuleTestFLBuilderModule {
	public function __construct( $config = [] ) {}
}

class BeaverModuleTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();

		// Function declarations inside BeaverModule.php (`_iu_bb_get_folder_options`)
		// are loaded via `require` so the function declaration is guarded by
		// function_exists — multiple test setUp() calls are safe.
		Functions\when( 'esc_html__' )->returnArg( 1 );
		Functions\when( 'esc_html' )->returnArg( 1 );
		Functions\when( 'plugins_url' )->justReturn( 'http://example.test/plugin' );
		Functions\when( 'apply_filters' )->returnArg( 2 );
		Functions\when( 'add_action' )->justReturn( true );

		require_once IU_PLUGIN_ROOT . '/inc/gallery/integrations/BeaverModule.php';
	}

	// -------------------------------------------------------------------------
	// Cache hit path — no DB query
	// -------------------------------------------------------------------------

	public function test_returns_cached_value_without_querying_db(): void {
		$cached = [
			'0' => '— Select a folder —',
			'1' => 'Cached folder A',
			'2' => 'Cached folder B',
		];

		Functions\when( 'wp_cache_get' )->alias(
			static function ( $key, $group ) use ( $cached ) {
				if ( $key === 'iu_bb_folder_options' && $group === 'infinite_uploads' ) {
					return $cached;
				}
				return false;
			}
		);

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldNotReceive( 'get_results' );

		$result = \ClikIT\InfiniteUploads\_iu_bb_get_folder_options();

		$this->assertSame( $cached, $result );
	}

	// -------------------------------------------------------------------------
	// Cache miss path — DB query + cache set
	// -------------------------------------------------------------------------

	public function test_queries_db_and_caches_on_first_call(): void {
		Functions\when( 'wp_cache_get' )->justReturn( false );

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldReceive( 'get_results' )
			->once()
			->andReturn( [
				[ 'id' => 1, 'name' => 'Folder A' ],
				[ 'id' => 2, 'name' => 'Folder B' ],
			] );

		$cached_key   = '';
		$cached_value = null;
		$cached_group = '';
		$cached_ttl   = 0;
		Functions\when( 'wp_cache_set' )->alias(
			static function ( $key, $value, $group, $ttl ) use ( &$cached_key, &$cached_value, &$cached_group, &$cached_ttl ) {
				$cached_key   = $key;
				$cached_value = $value;
				$cached_group = $group;
				$cached_ttl   = $ttl;
				return true;
			}
		);

		$result = \ClikIT\InfiniteUploads\_iu_bb_get_folder_options();

		$this->assertArrayHasKey( '0', $result, 'placeholder option always present at key "0"' );
		$this->assertArrayHasKey( '1', $result );
		$this->assertArrayHasKey( '2', $result );
		$this->assertSame( 'Folder A', $result['1'] );
		$this->assertSame( 'Folder B', $result['2'] );

		// wp_cache_set sanity — same key, group, and a TTL up to 5 minutes.
		$this->assertSame( 'iu_bb_folder_options', $cached_key );
		$this->assertSame( 'infinite_uploads', $cached_group );
		$this->assertSame( $result, $cached_value );
		// MINUTE_IN_SECONDS is stubbed at bootstrap to 60. The cache TTL is
		// MINUTE_IN_SECONDS * 5 = 300 seconds.
		$this->assertSame( 300, $cached_ttl );
	}

	public function test_includes_placeholder_when_no_folders_exist(): void {
		Functions\when( 'wp_cache_get' )->justReturn( false );
		Functions\when( 'wp_cache_set' )->justReturn( true );

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldReceive( 'get_results' )->andReturn( [] );

		$result = \ClikIT\InfiniteUploads\_iu_bb_get_folder_options();

		$this->assertCount( 1, $result );
		$this->assertArrayHasKey( '0', $result );
		$this->assertSame( '— Select a folder —', $result['0'] );
	}

	// -------------------------------------------------------------------------
	// Folder name escaping (defence in depth)
	// -------------------------------------------------------------------------

	public function test_folder_names_are_html_escaped(): void {
		Functions\when( 'wp_cache_get' )->justReturn( false );
		Functions\when( 'wp_cache_set' )->justReturn( true );

		// Pretend a malicious folder name was saved to the DB.
		$dangerous = 'My folder <script>alert(1)</script>';
		$expected  = 'My folder &lt;script&gt;alert(1)&lt;/script&gt;';

		Functions\when( 'esc_html' )->alias(
			static function ( $s ) {
				return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' );
			}
		);

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldReceive( 'get_results' )->andReturn( [
			[ 'id' => 7, 'name' => $dangerous ],
		] );

		$result = \ClikIT\InfiniteUploads\_iu_bb_get_folder_options();

		$this->assertSame( $expected, $result['7'] );
		$this->assertStringNotContainsString( '<script>', $result['7'] );
	}

	// -------------------------------------------------------------------------
	// Query shape — alphabetically ordered, only id + name selected
	// -------------------------------------------------------------------------

	public function test_db_query_selects_id_and_name_ordered_by_name(): void {
		Functions\when( 'wp_cache_get' )->justReturn( false );
		Functions\when( 'wp_cache_set' )->justReturn( true );

		$captured = '';
		$wpdb     = $this->mock_wpdb();
		$wpdb->shouldReceive( 'get_results' )
			->andReturnUsing(
				static function ( $sql ) use ( &$captured ) {
					$captured = $sql;
					return [];
				}
			);

		\ClikIT\InfiniteUploads\_iu_bb_get_folder_options();

		$this->assertStringContainsString( 'SELECT id, name', $captured );
		$this->assertStringContainsString( 'ORDER BY name ASC', $captured );
		$this->assertStringContainsString( 'infinite_uploads_media_folders', $captured );
	}
}
