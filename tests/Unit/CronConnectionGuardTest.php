<?php
/**
 * Tests for the connection guard in do_sync() / do_download().
 *
 * do_sync() is hooked to a daily wp-cron event scheduled unconditionally in
 * InfiniteUploadsAdmin::__construct() — there is no check that the site has
 * ever connected. Before this guard, both functions called
 * InfiniteUploads::s3() as their first real action, which throws an
 * uncaught InvalidArgumentException ("Missing required client configuration
 * options: region") on any site without a live connection, since region is
 * only populated once InfiniteUploads::setup() has real API site data. That
 * fatal kills the whole wp-cron / Action Scheduler run.
 *
 * These tests assert both functions return immediately — before ever
 * touching $this->iup_instance (and therefore before s3() can be called) —
 * when the site isn't connected.
 *
 * @package ClikIT\InfiniteUploads\Tests\Unit
 */

declare( strict_types=1 );

namespace ClikIT\InfiniteUploads\Tests\Unit;

use ClikIT\InfiniteUploads\InfiniteUploadsAdmin;
use ClikIT\InfiniteUploads\Tests\TestCase;
use Mockery;
use ReflectionClass;

class CronConnectionGuardTest extends TestCase {

	/**
	 * @var InfiniteUploadsAdmin
	 */
	private $admin;

	/**
	 * @var ReflectionClass
	 */
	private $reflection;

	protected function setUp(): void {
		parent::setUp();
		require_once IU_PLUGIN_ROOT . '/inc/InfiniteUploadsAdmin.php';

		$this->reflection = new ReflectionClass( InfiniteUploadsAdmin::class );
		$this->admin       = $this->reflection->newInstanceWithoutConstructor();
	}

	/**
	 * Inject a mock into a private property via reflection.
	 */
	private function set_prop( string $name, $value ): void {
		$prop = $this->reflection->getProperty( $name );
		$prop->setAccessible( true );
		$prop->setValue( $this->admin, $value );
	}

	/**
	 * $iup_instance deliberately gets NO stub for s3(). If the guard fails
	 * to return early, calling ->s3() on this bare mock throws a Mockery
	 * "method does not exist" error — which fails the test with a clear
	 * signal of exactly what leaked past the guard.
	 */
	private function untouchable_iup_instance() {
		return Mockery::mock();
	}

	public function test_do_sync_returns_before_s3_when_no_token(): void {
		$api = Mockery::mock();
		$api->shouldReceive( 'has_token' )->once()->andReturn( false );
		$api->shouldNotReceive( 'get_site_data' );

		$this->set_prop( 'api', $api );
		$this->set_prop( 'iup_instance', $this->untouchable_iup_instance() );

		$this->assertNull(
			$this->admin->do_sync(),
			'do_sync() must hit the early "return;" guard — reaching the sync loop would call the untouchable iup_instance mock and throw.'
		);
	}

	public function test_do_sync_returns_before_s3_when_token_but_no_site_data(): void {
		// has_token() true but get_site_data() false covers a stale/cleared
		// token whose cached site data never resolved — same missing-region
		// crash risk as never having connected at all.
		$api = Mockery::mock();
		$api->shouldReceive( 'has_token' )->once()->andReturn( true );
		$api->shouldReceive( 'get_site_data' )->once()->andReturn( false );

		$this->set_prop( 'api', $api );
		$this->set_prop( 'iup_instance', $this->untouchable_iup_instance() );

		$this->assertNull(
			$this->admin->do_sync(),
			'do_sync() must hit the early "return;" guard when site data never resolved.'
		);
	}

	public function test_do_download_returns_before_s3_when_no_token(): void {
		$api = Mockery::mock();
		$api->shouldReceive( 'has_token' )->once()->andReturn( false );
		$api->shouldNotReceive( 'get_site_data' );

		$this->set_prop( 'api', $api );
		$this->set_prop( 'iup_instance', $this->untouchable_iup_instance() );

		$this->assertNull(
			$this->admin->do_download(),
			'do_download() must hit the early "return;" guard — reaching the download loop would call the untouchable iup_instance mock and throw.'
		);
	}

	public function test_do_download_returns_before_s3_when_token_but_no_site_data(): void {
		$api = Mockery::mock();
		$api->shouldReceive( 'has_token' )->once()->andReturn( true );
		$api->shouldReceive( 'get_site_data' )->once()->andReturn( false );

		$this->set_prop( 'api', $api );
		$this->set_prop( 'iup_instance', $this->untouchable_iup_instance() );

		$this->assertNull(
			$this->admin->do_download(),
			'do_download() must hit the early "return;" guard when site data never resolved.'
		);
	}
}
