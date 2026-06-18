<?php
/**
 * Tests for the MediaFolders AJAX folder-CRUD handlers.
 *
 * The constructor registers dozens of WP hooks at instantiation time, so we
 * use newInstanceWithoutConstructor() to skip it. WP's wp_send_json_success
 * and wp_send_json_error normally call wp_die() to terminate the response;
 * in tests we replace them with stubs that throw a SuccessException or
 * ErrorException so the test can capture the response payload.
 *
 * The most important behavioural assertion across these handlers is that the
 * iu_bb_folder_options cache (used by inc/gallery/integrations/BeaverModule.php)
 * is invalidated on every mutation — create / rename / delete / bulk-delete.
 * Without that, the BB module dropdown shows stale options for up to 5 minutes
 * after a change.
 *
 * @package ClikIT\InfiniteUploads\Tests\Unit
 */

declare( strict_types=1 );

namespace ClikIT\InfiniteUploads\Tests\Unit;

use Brain\Monkey\Functions;
use ClikIT\InfiniteUploads\MediaFolders;
use ClikIT\InfiniteUploads\Tests\TestCase;
use ReflectionClass;

/**
 * Thrown by our wp_send_json_success stub. Carries the response data so
 * tests can assert against it.
 */
class _MediaFoldersTestSuccessException extends \Exception {
	public $data;

	public function __construct( $data ) {
		parent::__construct( 'wp_send_json_success called' );
		$this->data = $data;
	}
}

/**
 * Thrown by our wp_send_json_error stub.
 */
class _MediaFoldersTestErrorException extends \Exception {
	public $data;

	public function __construct( $data ) {
		parent::__construct( 'wp_send_json_error called' );
		$this->data = $data;
	}
}

class MediaFoldersTest extends TestCase {

	/**
	 * @var MediaFolders
	 */
	private $folders;

	protected function setUp(): void {
		parent::setUp();

		require_once IU_PLUGIN_ROOT . '/inc/MediaFolders.php';

		$reflection    = new ReflectionClass( MediaFolders::class );
		$this->folders = $reflection->newInstanceWithoutConstructor();

		// Default permission + nonce passes.
		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'sanitize_text_field' )->returnArg( 1 );
		Functions\when( 'wp_unslash' )->returnArg( 1 );
		Functions\when( '__' )->returnArg( 1 );
		Functions\when( '_e' )->returnArg( 1 );
		Functions\when( 'esc_html__' )->returnArg( 1 );
		Functions\when( 'wp_cache_delete' )->justReturn( true );
		Functions\when( 'wp_cache_get' )->justReturn( false );
		Functions\when( 'wp_cache_set' )->justReturn( true );
		Functions\when( 'absint' )->alias(
			static function ( $n ) {
				return \abs( (int) $n );
			}
		);
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'current_time' )->justReturn( '2026-06-16 12:00:00' );

		// wp_send_json_* throw exceptions so tests can assert on the response.
		Functions\when( 'wp_send_json_success' )->alias(
			static function ( $data = null ) {
				throw new _MediaFoldersTestSuccessException( $data );
			}
		);
		Functions\when( 'wp_send_json_error' )->alias(
			static function ( $data = null ) {
				throw new _MediaFoldersTestErrorException( $data );
			}
		);

		// Permission-check helper used by some handlers — default to true.
		Functions\when( 'apply_filters' )->returnArg( 2 );

		$_POST = [];
	}

	protected function tearDown(): void {
		$_POST = [];
		parent::tearDown();
	}

	/**
	 * Reflection helper — set a protected/private property on the test
	 * folders instance.
	 */
	private function set_folders_prop( string $name, $value ): void {
		$ref  = new ReflectionClass( MediaFolders::class );
		$prop = $ref->getProperty( $name );
		$prop->setAccessible( true );
		$prop->setValue( $this->folders, $value );
	}

	// -------------------------------------------------------------------------
	// ajax_create_folder
	// -------------------------------------------------------------------------

	public function test_create_folder_inserts_row_and_invalidates_bb_cache(): void {
		$_POST = [
			'name'   => 'New Folder',
			'parent' => 0,
			'nonce'  => 'x',
		];

		$wpdb              = $this->mock_wpdb();
		$wpdb->insert_id   = 42;
		$wpdb->shouldReceive( 'get_var' )->andReturn( 0 );
		$wpdb->shouldReceive( 'insert' )
			->once()
			->andReturn( 1 );

		$invalidated = [];
		Functions\when( 'wp_cache_delete' )->alias(
			static function ( $key, $group ) use ( &$invalidated ) {
				$invalidated[] = [ 'key' => $key, 'group' => $group ];
				return true;
			}
		);

		try {
			$this->folders->ajax_create_folder();
			$this->fail( 'Expected wp_send_json_success to be called' );
		} catch ( _MediaFoldersTestSuccessException $e ) {
			$this->assertSame( 42, $e->data['folder_id'] );
			$this->assertSame( 'New Folder', $e->data['text'] );
		}

		$this->assertContains(
			[ 'key' => 'iu_bb_folder_options', 'group' => 'infinite_uploads' ],
			$invalidated,
			'BB folder dropdown cache must be invalidated when a folder is created'
		);
	}

	public function test_create_folder_rejects_empty_name(): void {
		$_POST = [ 'name' => '', 'parent' => 0 ];

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldNotReceive( 'insert' );

		$this->expectException( _MediaFoldersTestErrorException::class );

		$this->folders->ajax_create_folder();
	}

	public function test_create_folder_rejects_unauthorised_user(): void {
		Functions\when( 'current_user_can' )->justReturn( false );

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldNotReceive( 'insert' );

		$this->expectException( _MediaFoldersTestErrorException::class );

		$this->folders->ajax_create_folder();
	}

	// -------------------------------------------------------------------------
	// ajax_rename_folder
	// -------------------------------------------------------------------------

	public function test_rename_folder_updates_row_and_invalidates_bb_cache(): void {
		$_POST = [
			'term_id' => 7,
			'name'    => 'Renamed',
		];

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldReceive( 'update' )->once()->andReturn( 1 );

		// user_can_manage_folder is a private helper called inside the handler.
		// In production it checks capability + ownership. Stub via current_user_can.
		Functions\when( 'current_user_can' )->justReturn( true );

		$invalidated = [];
		Functions\when( 'wp_cache_delete' )->alias(
			static function ( $key, $group ) use ( &$invalidated ) {
				$invalidated[] = $key;
				return true;
			}
		);

		try {
			$this->folders->ajax_rename_folder();
			$this->fail( 'Expected success exception' );
		} catch ( _MediaFoldersTestSuccessException $e ) {
			$this->assertSame( 7, $e->data['term_id'] );
			$this->assertSame( 'Renamed', $e->data['name'] );
		}

		$this->assertContains(
			'iu_bb_folder_options',
			$invalidated,
			'BB folder dropdown cache must be invalidated when a folder is renamed'
		);
	}

	public function test_rename_folder_rejects_invalid_input(): void {
		$_POST = [ 'term_id' => 0, 'name' => '' ];

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldNotReceive( 'update' );

		$this->expectException( _MediaFoldersTestErrorException::class );

		$this->folders->ajax_rename_folder();
	}

	// -------------------------------------------------------------------------
	// ajax_delete_folder
	// -------------------------------------------------------------------------

	public function test_delete_folder_removes_row_and_invalidates_bb_cache(): void {
		$_POST = [ 'term_id' => 9 ];

		$wpdb = $this->mock_wpdb();
		// Get-row for the folder pre-delete.
		$wpdb->shouldReceive( 'get_row' )->andReturn( (object) [
			'id'        => 9,
			'parent_id' => 0,
			'name'      => 'Folder to delete',
		] );
		// One UPDATE to reparent children, two DELETEs (relationships + folder).
		$wpdb->shouldReceive( 'update' )->andReturn( 0 );
		$wpdb->shouldReceive( 'delete' )->twice()->andReturn( 1 );

		$invalidated = [];
		Functions\when( 'wp_cache_delete' )->alias(
			static function ( $key, $group ) use ( &$invalidated ) {
				$invalidated[] = $key;
				return true;
			}
		);

		try {
			$this->folders->ajax_delete_folder();
			$this->fail( 'Expected success exception' );
		} catch ( _MediaFoldersTestSuccessException $e ) {
			$this->assertSame( 9, $e->data['deleted'] );
		}

		$this->assertContains( 'iu_bb_folder_options', $invalidated );
	}

	public function test_delete_folder_rejects_when_folder_not_found(): void {
		$_POST = [ 'term_id' => 999 ];

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldReceive( 'get_row' )->andReturn( null );
		$wpdb->shouldNotReceive( 'delete' );

		$this->expectException( _MediaFoldersTestErrorException::class );

		$this->folders->ajax_delete_folder();
	}

	// -------------------------------------------------------------------------
	// ajax_bulk_delete_folders
	// -------------------------------------------------------------------------

	public function test_bulk_delete_invalidates_cache_when_at_least_one_deleted(): void {
		$_POST = [ 'term_ids' => [ 1, 2, 3 ] ];

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldReceive( 'get_row' )->andReturn( (object) [
			'id'        => 1,
			'parent_id' => 0,
		] );
		$wpdb->shouldReceive( 'update' )->andReturn( 0 );
		$wpdb->shouldReceive( 'delete' )->andReturn( 1 );
		$wpdb->shouldReceive( 'query' )->andReturn( 0 );

		$invalidated = [];
		Functions\when( 'wp_cache_delete' )->alias(
			static function ( $key, $group ) use ( &$invalidated ) {
				$invalidated[] = $key;
				return true;
			}
		);

		try {
			$this->folders->ajax_bulk_delete_folders();
			$this->fail( 'Expected success exception' );
		} catch ( _MediaFoldersTestSuccessException $e ) {
			$this->assertNotEmpty( $e->data['deleted'] );
		}

		$this->assertContains( 'iu_bb_folder_options', $invalidated );
	}

	public function test_bulk_delete_rejects_empty_ids(): void {
		$_POST = [ 'term_ids' => [] ];

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldNotReceive( 'delete' );

		$this->expectException( _MediaFoldersTestErrorException::class );

		$this->folders->ajax_bulk_delete_folders();
	}

	// -------------------------------------------------------------------------
	// ajax_move_folder — reparenting
	// -------------------------------------------------------------------------

	public function test_move_folder_updates_parent_id(): void {
		$_POST = [ 'term_id' => 5, 'parent' => 10 ];

		$wpdb = $this->mock_wpdb();
		// is_descendant_of inside ajax_move_folder issues a SELECT we don't care about here.
		$wpdb->shouldReceive( 'get_var' )->andReturn( null );
		$wpdb->shouldReceive( 'get_row' )->andReturn( null );
		$wpdb->shouldReceive( 'update' )->once()->andReturn( 1 );

		try {
			$this->folders->ajax_move_folder();
			$this->fail( 'Expected success exception' );
		} catch ( _MediaFoldersTestSuccessException $e ) {
			$this->assertSame( 5, $e->data['term_id'] );
			$this->assertSame( 10, $e->data['parent'] );
		}
	}

	public function test_move_folder_rejects_invalid_folder_id(): void {
		$_POST = [ 'term_id' => 0, 'parent' => 0 ];

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldNotReceive( 'update' );

		$this->expectException( _MediaFoldersTestErrorException::class );

		$this->folders->ajax_move_folder();
	}

	// -------------------------------------------------------------------------
	// ajax_bulk_move_folders
	// -------------------------------------------------------------------------

	public function test_bulk_move_folders_updates_multiple(): void {
		$_POST = [ 'term_ids' => [ 1, 2, 3 ], 'parent' => 7 ];

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldReceive( 'get_var' )->andReturn( null );
		$wpdb->shouldReceive( 'get_row' )->andReturn( null );

		$update_count = 0;
		$wpdb->shouldReceive( 'update' )->andReturnUsing(
			static function () use ( &$update_count ) {
				$update_count++;
				return 1;
			}
		);

		try {
			$this->folders->ajax_bulk_move_folders();
			$this->fail( 'Expected success exception' );
		} catch ( _MediaFoldersTestSuccessException $e ) {
			$this->assertSame( [ 1, 2, 3 ], $e->data['moved'] );
		}
		$this->assertSame( 3, $update_count, 'one UPDATE per folder moved' );
	}

	public function test_bulk_move_folders_skips_self_into_self(): void {
		// Trying to move folder 5 to parent 5.
		$_POST = [ 'term_ids' => [ 5 ], 'parent' => 5 ];

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldReceive( 'get_var' )->andReturn( null );
		$wpdb->shouldReceive( 'get_row' )->andReturn( null );
		$wpdb->shouldNotReceive( 'update' );

		try {
			$this->folders->ajax_bulk_move_folders();
			$this->fail( 'Expected success exception' );
		} catch ( _MediaFoldersTestSuccessException $e ) {
			$this->assertSame( [], $e->data['moved'] );
			$this->assertSame( [ 5 ], $e->data['skipped'] );
		}
	}

	// -------------------------------------------------------------------------
	// ajax_set_folder_color
	// -------------------------------------------------------------------------

	public function test_set_folder_color_updates_row(): void {
		$_POST = [ 'folder_id' => 4, 'color' => '#aabbcc' ];

		Functions\when( 'sanitize_hex_color' )->returnArg( 1 );

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldReceive( 'update' )->once()->andReturn( 1 );

		try {
			$this->folders->ajax_set_folder_color();
			$this->fail( 'Expected success exception' );
		} catch ( _MediaFoldersTestSuccessException $e ) {
			$this->assertSame( 4, $e->data['folder_id'] );
			$this->assertSame( '#aabbcc', $e->data['color'] );
		}
	}

	public function test_set_folder_color_rejects_invalid_id(): void {
		$_POST = [ 'folder_id' => 0, 'color' => '#fff' ];

		Functions\when( 'sanitize_hex_color' )->returnArg( 1 );
		$wpdb = $this->mock_wpdb();
		$wpdb->shouldNotReceive( 'update' );

		$this->expectException( _MediaFoldersTestErrorException::class );

		$this->folders->ajax_set_folder_color();
	}

	// -------------------------------------------------------------------------
	// ajax_set_upload_folder — persists target to user meta
	// -------------------------------------------------------------------------

	public function test_set_upload_folder_stores_in_user_meta(): void {
		$_POST = [ 'folder_id' => 12 ];

		$saved_meta = null;
		Functions\when( 'update_user_meta' )->alias(
			static function ( $user_id, $key, $value ) use ( &$saved_meta ) {
				$saved_meta = [ 'user' => $user_id, 'key' => $key, 'value' => $value ];
				return 1;
			}
		);
		Functions\when( 'delete_user_meta' )->justReturn( true );

		$this->expectException( _MediaFoldersTestSuccessException::class );
		try {
			$this->folders->ajax_set_upload_folder();
		} finally {
			$this->assertSame( 1, $saved_meta['user'] );
			$this->assertSame( 'iu_upload_folder', $saved_meta['key'] );
			$this->assertSame( 12, $saved_meta['value'] );
		}
	}

	public function test_set_upload_folder_deletes_meta_when_zero(): void {
		$_POST = [ 'folder_id' => 0 ];

		$deleted = false;
		Functions\when( 'update_user_meta' )->justReturn( 1 );
		Functions\when( 'delete_user_meta' )->alias(
			static function ( $user_id, $key ) use ( &$deleted ) {
				if ( $key === 'iu_upload_folder' ) {
					$deleted = true;
				}
				return true;
			}
		);

		$this->expectException( _MediaFoldersTestSuccessException::class );
		try {
			$this->folders->ajax_set_upload_folder();
		} finally {
			$this->assertTrue( $deleted, 'Selecting folder 0 must clear the user meta' );
		}
	}

	// -------------------------------------------------------------------------
	// ajax_sort_folders — updates sort_order on a list of ids
	// -------------------------------------------------------------------------

	public function test_sort_folders_updates_sort_order_for_each_id(): void {
		// ajax_sort_folders absint()s each id, so pass numeric values
		// (real frontend strips the 'folder_' prefix before posting).
		$_POST = [ 'order' => [ 3, 1, 2 ] ];

		$wpdb = $this->mock_wpdb();
		$update_count = 0;
		$wpdb->shouldReceive( 'update' )->andReturnUsing(
			static function () use ( &$update_count ) {
				$update_count++;
				return 1;
			}
		);

		try {
			$this->folders->ajax_sort_folders();
			$this->fail( 'Expected success exception' );
		} catch ( _MediaFoldersTestSuccessException $e ) {
			// Doesn't matter what data the success contains; we just want the
			// UPDATEs to fire.
		}

		$this->assertSame( 3, $update_count, 'One UPDATE per folder id in the order array' );
	}

	// -------------------------------------------------------------------------
	// ajax_move_media — assigns attachments to a folder
	// -------------------------------------------------------------------------

	public function test_move_media_writes_relationships(): void {
		$_POST = [
			'attachment_ids' => [ 100, 101, 102 ],
			'folder_id'      => 5,
		];

		$wpdb = $this->mock_wpdb();
		$wpdb->shouldReceive( 'get_var' )->andReturn( 1 );
		$wpdb->shouldReceive( 'get_row' )->andReturn( null );
		$wpdb->shouldReceive( 'get_col' )->andReturn( [] );
		$wpdb->shouldReceive( 'get_results' )->andReturn( [] );
		$wpdb->shouldReceive( 'delete' )->andReturn( 1 );
		$wpdb->shouldReceive( 'query' )->andReturn( 1 );
		$wpdb->shouldReceive( 'insert' )->andReturn( 1 );
		$wpdb->shouldReceive( 'replace' )->andReturn( 1 );
		$wpdb->shouldReceive( 'update' )->andReturn( 1 );

		$success_thrown = false;
		try {
			$this->folders->ajax_move_media();
		} catch ( _MediaFoldersTestSuccessException $e ) {
			$success_thrown = true;
		} catch ( _MediaFoldersTestErrorException $e ) {
			$this->fail( 'Expected success, got error: ' . print_r( $e->data, true ) );
		}

		$this->assertTrue( $success_thrown, 'ajax_move_media must signal success when given valid input' );
	}
}
