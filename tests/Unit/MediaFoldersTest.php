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
}
