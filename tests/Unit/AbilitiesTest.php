<?php
/**
 * Tests for InfiniteUploadsAbilities — registration surface and gates.
 *
 * Registration: the category registers on its own hook, all eight abilities
 * register on the main site, only the site-agnostic three register on a
 * subsite, rescan is the only one flagged destructive, and everything is
 * inert where the Abilities API doesn't exist (WP < 6.9).
 *
 * Gates: sync/download/toggle/purge refuse cleanly (WP_Error with a stable
 * code) when the site is unconnected or the plan doesn't allow the action —
 * these mirror the admin-ajax handlers and must never drift from them.
 *
 * The class is instantiated without its constructor (it resolves the plugin
 * singletons) and its api/iup_instance properties are replaced with mocks,
 * following the AdminExceptionHandlersTest pattern.
 *
 * NOTE: test order matters for test_registration_inert_without_abilities_api.
 * Brain Monkey defines stubbed functions process-wide, so the inert test must
 * run before any test that stubs wp_register_ability — it is declared first.
 *
 * @package ClikIT\InfiniteUploads\Tests\Unit
 */

declare( strict_types=1 );

namespace ClikIT\InfiniteUploads\Tests\Unit;

use Brain\Monkey\Functions;
use ClikIT\InfiniteUploads\InfiniteUploadsAbilities;
use ClikIT\InfiniteUploads\Tests\TestCase;
use Mockery;
use ReflectionClass;
use WP_Error;

class AbilitiesTest extends TestCase {

	/**
	 * @var InfiniteUploadsAbilities
	 */
	private $abilities;

	/**
	 * @var ReflectionClass<InfiniteUploadsAbilities>
	 */
	private $reflection;

	/**
	 * @var Mockery\MockInterface
	 */
	private $api;

	/**
	 * @var Mockery\MockInterface
	 */
	private $iup;

	protected function setUp(): void {
		parent::setUp();

		Functions\when( '__' )->returnArg();

		require_once IU_PLUGIN_ROOT . '/tests/fixtures/wp-error-stub.php';
		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsAbilities.php';

		$this->reflection = new ReflectionClass( InfiniteUploadsAbilities::class );
		$this->abilities  = $this->reflection->newInstanceWithoutConstructor();

		$this->api = Mockery::mock();
		$this->iup = Mockery::mock();

		$this->set_prop( 'api', $this->api );
		$this->set_prop( 'iup_instance', $this->iup );
	}

	private function set_prop( string $name, $value ): void {
		$prop = $this->reflection->getProperty( $name );
		$prop->setAccessible( true );
		$prop->setValue( $this->abilities, $value );
	}

	/**
	 * Expect $times registrations and collect the ability names into $names.
	 *
	 * @param  int         $times  Expected number of registrations.
	 * @param  array|null  $names  Collector, passed by reference.
	 */
	private function expect_registrations( int $times, ?array &$names ): void {
		$names = [];

		Functions\expect( 'wp_register_ability' )
			->times( $times )
			->andReturnUsing( function ( $name ) use ( &$names ) {
				$names[] = $name;

				return true;
			} );
	}

	// Must run first: once another test stubs wp_register_ability, the
	// function exists for the rest of the process. See class docblock.
	public function test_registration_inert_without_abilities_api(): void {
		$this->assertFalse( function_exists( 'wp_register_ability' ) );

		$this->abilities->register_abilities();

		$this->assertFalse( function_exists( 'wp_register_ability' ) );
	}

	public function test_registers_category_on_categories_hook(): void {
		$captured = null;

		Functions\expect( 'wp_register_ability_category' )
			->once()
			->andReturnUsing( function ( $slug, $args ) use ( &$captured ) {
				$captured = [ $slug, $args ];

				return true;
			} );

		$this->abilities->register_category();

		$this->assertSame( 'infinite-uploads', $captured[0] );
		$this->assertNotEmpty( $captured[1]['label'] );
	}

	public function test_registers_all_abilities_on_main_site(): void {
		Functions\when( 'is_main_site' )->justReturn( true );
		$this->expect_registrations( 8, $names );

		$this->abilities->register_abilities();

		$this->assertSame( [
			'infinite-uploads/get-status',
			'infinite-uploads/get-connect-instructions',
			'infinite-uploads/purge-cache',
			'infinite-uploads/scan',
			'infinite-uploads/rescan',
			'infinite-uploads/sync',
			'infinite-uploads/download',
			'infinite-uploads/toggle-cloud',
		], $names );
	}

	// The destructive annotation is what approval-gating agent clients key on
	// before wiping the file table, so it must survive the shared meta merge.
	public function test_only_rescan_is_annotated_destructive(): void {
		Functions\when( 'is_main_site' )->justReturn( true );
		$destructive = [];

		Functions\expect( 'wp_register_ability' )
			->times( 8 )
			->andReturnUsing( function ( $name, $args ) use ( &$destructive ) {
				if ( ! empty( $args['meta']['annotations']['destructive'] ) ) {
					$destructive[] = $name;
				}

				return true;
			} );

		$this->abilities->register_abilities();

		$this->assertSame( [ 'infinite-uploads/rescan' ], $destructive );
	}

	public function test_registers_site_agnostic_abilities_on_subsite(): void {
		Functions\when( 'is_main_site' )->justReturn( false );
		$this->expect_registrations( 3, $names );

		$this->abilities->register_abilities();

		$this->assertSame( [
			'infinite-uploads/get-status',
			'infinite-uploads/get-connect-instructions',
			'infinite-uploads/purge-cache',
		], $names );
	}

	// Regression guard: scan used to default to a fresh pass, and a fresh pass
	// TRUNCATEs the file table (InfiniteUploadsFilelist::start). An agent
	// looping on scan would have wiped sync state on every call and never
	// finished a large scan. It must now refuse and point at rescan.
	public function test_scan_refuses_to_restart_over_existing_data(): void {
		Functions\expect( 'get_site_option' )
			->once()
			->with( 'iup_scan_remaining_dirs', [] )
			->andReturn( [] );
		$this->iup->shouldReceive( 'get_sync_stats' )->andReturn( [ 'is_data' => true ] );

		$result = $this->abilities->scan();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'iu_rescan_required', $result->get_error_code() );
	}

	/**
	 * The next_step state machine is how an agent — in this conversation or a
	 * different one days later — discovers that a finished sync is sitting
	 * there waiting for someone to approve switching the site over. Enabling
	 * is never automatic, so this signal is the only thing standing between a
	 * completed transfer and a customer whose media never moves.
	 *
	 * @dataProvider next_step_cases
	 */
	public function test_next_step_reports_the_pending_action( bool $connected, bool $has_data, int $remaining, bool $enabled, bool $scanning, string $expected ): void {
		Functions\when( 'get_site_option' )->justReturn( $scanning ? [ '/some/dir' ] : [] );

		$method = $this->reflection->getMethod( 'get_next_step' );
		$method->setAccessible( true );

		$this->assertSame(
			$expected,
			$method->invoke( $this->abilities, $connected, $has_data, $remaining, $enabled )
		);
	}

	public static function next_step_cases(): array {
		return [
			// connected, has_data, remaining, enabled, scanning, expected
			'fresh install'          => [ false, false, 0, false, false, 'connect' ],
			'connected but unscanned'=> [ true, false, 0, false, false, 'scan' ],
			'scan still running'     => [ true, true, 5, false, true, 'scan' ],
			'scanned, nothing synced'=> [ true, true, 900, false, false, 'sync' ],
			'sync done, not switched'=> [ true, true, 0, false, false, 'enable' ],
			'fully set up'           => [ true, true, 0, true, false, 'done' ],
			// A site that never connected is told to connect even if it has
			// stale scan data from a previous life.
			'disconnected with data' => [ false, true, 0, false, false, 'connect' ],
		];
	}

	public function test_sync_requires_connection(): void {
		$this->api->shouldReceive( 'has_token' )->andReturn( false );

		$result = $this->abilities->start_sync();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'iu_not_connected', $result->get_error_code() );
	}

	public function test_sync_requires_scan_data(): void {
		$this->api->shouldReceive( 'has_token' )->andReturn( true );
		$this->api->shouldReceive( 'get_site_data' )->andReturn( (object) [ 'site' => [] ] );
		$this->iup->shouldReceive( 'get_sync_stats' )->andReturn( [ 'is_data' => false ] );

		$result = $this->abilities->start_sync();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'iu_scan_required', $result->get_error_code() );
	}

	public function test_sync_schedules_background_action(): void {
		$this->api->shouldReceive( 'has_token' )->andReturn( true );
		$this->api->shouldReceive( 'get_site_data' )->andReturn( (object) [ 'site' => [] ] );
		$this->iup->shouldReceive( 'get_sync_stats' )->andReturn( [ 'is_data' => true ] );

		Functions\expect( 'update_site_option' )->once()->with( 'iup_do_sync_complete', 'no' );
		Functions\expect( 'as_next_scheduled_action' )->once()->with( 'infinite-uploads-do-sync' )->andReturn( false );
		Functions\expect( 'as_schedule_single_action' )->once()->with( Mockery::type( 'int' ), 'infinite-uploads-do-sync' );

		$result = $this->abilities->start_sync();

		$this->assertTrue( $result['queued'] );
		$this->assertFalse( $result['already_queued'] );
	}

	public function test_sync_does_not_double_schedule(): void {
		$this->api->shouldReceive( 'has_token' )->andReturn( true );
		$this->api->shouldReceive( 'get_site_data' )->andReturn( (object) [ 'site' => [] ] );
		$this->iup->shouldReceive( 'get_sync_stats' )->andReturn( [ 'is_data' => true ] );

		Functions\expect( 'update_site_option' )->once();
		Functions\expect( 'as_next_scheduled_action' )->once()->andReturn( time() + 5 );
		Functions\expect( 'as_schedule_single_action' )->never();

		$result = $this->abilities->start_sync();

		$this->assertTrue( $result['already_queued'] );
	}

	public function test_download_requires_connection(): void {
		$this->api->shouldReceive( 'has_token' )->andReturn( false );

		$result = $this->abilities->start_download();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'iu_not_connected', $result->get_error_code() );
	}

	public function test_purge_requires_business_plan(): void {
		$this->api->shouldReceive( 'has_token' )->andReturn( true );
		$this->api->shouldReceive( 'is_business_plan' )->andReturn( false );

		$result = $this->abilities->purge_cache();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'iu_requires_business_plan', $result->get_error_code() );
	}

	public function test_toggle_enable_requires_connected_bucket(): void {
		$this->iup->bucket = '';

		$result = $this->abilities->toggle_cloud( [ 'enabled' => true ] );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'iu_not_connected', $result->get_error_code() );
	}

	public function test_toggle_disable_works_without_connection(): void {
		$this->iup->bucket = '';
		$this->iup->shouldReceive( 'toggle_cloud' )->once()->with( false );
		Functions\when( 'infinite_uploads_enabled' )->justReturn( false );

		$result = $this->abilities->toggle_cloud( [ 'enabled' => false ] );

		$this->assertFalse( $result['cloud_serving_enabled'] );
	}
}
