<?php
/**
 * Base test case — wires up Brain Monkey + Mockery per test.
 *
 * @package ClikIT\InfiniteUploads\Tests
 */

declare( strict_types=1 );

namespace ClikIT\InfiniteUploads\Tests;

use Brain\Monkey;
use Mockery;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		Mockery::close();
		parent::tearDown();
	}

	/**
	 * Stub a global $wpdb mock with the given table prefix.
	 *
	 * Tests that exercise DB-touching code call this in setUp and then set
	 * expectations on the returned mock (->shouldReceive('query')->...).
	 */
	protected function mock_wpdb( string $base_prefix = 'wp_' ) {
		$wpdb              = Mockery::mock();
		$wpdb->base_prefix = $base_prefix;
		$wpdb->prefix      = $base_prefix;
		$wpdb->posts       = $base_prefix . 'posts';
		$wpdb->postmeta    = $base_prefix . 'postmeta';
		$wpdb->users       = $base_prefix . 'users';
		$wpdb->usermeta    = $base_prefix . 'usermeta';
		$wpdb->shouldReceive( 'prepare' )->andReturnUsing(
			function ( $query, ...$args ) {
				// Crude but sufficient for assertion-level prepare: substitute
				// %s with quoted string, %d with int. Real wpdb does more, but
				// we're not asserting against the prepared SQL itself, just
				// the value flow.
				foreach ( $args as $arg ) {
					$replacement = is_int( $arg ) || is_float( $arg )
						? (string) $arg
						: "'" . addslashes( (string) $arg ) . "'";
					$query = preg_replace( '/%[sd]/', $replacement, $query, 1 );
				}
				return $query;
			}
		)->byDefault();
		$GLOBALS['wpdb'] = $wpdb;
		return $wpdb;
	}
}
