<?php
/**
 * Fixture: minimal in-namespace stubs so tests can load
 * inc/ewww-integration.php without bringing in the full plugin bootstrap.
 *
 * The real implementations live in:
 *   - infinite-uploads.php (infinite_uploads_enabled() — global namespace)
 *   - inc/InfiniteUploads.php (ClikIT\InfiniteUploads\InfiniteUploads class)
 *
 * Both pull in heavy dependencies (AWS SDK, action scheduler, WP) that we
 * don't want loaded during unit tests. Stand-ins below give us symbol
 * presence without the cost.
 *
 * @package ClikIT\InfiniteUploads\Tests\Fixtures
 */

declare( strict_types=1 );

namespace {
	if ( ! function_exists( 'infinite_uploads_enabled' ) ) {
		function infinite_uploads_enabled() {
			// Each test overrides this via Brain Monkey's Functions\when().
			return false;
		}
	}
}

namespace ClikIT\InfiniteUploads {
	if ( ! class_exists( __NAMESPACE__ . '\\InfiniteUploads', false ) ) {
		class InfiniteUploads {
			public $bucket  = '';
			public $cdn_url = '';
			private static $instance;

			public static function get_instance(): self {
				if ( self::$instance === null ) {
					self::$instance = new self();
				}
				return self::$instance;
			}

			public static function reset_instance_for_tests( ?self $instance = null ): void {
				self::$instance = $instance;
			}

			public function get_s3_url(): string {
				return $this->cdn_url;
			}
		}
	}

	if ( ! class_exists( __NAMESPACE__ . '\\InfiniteUploadsApiHandler', false ) ) {
		class InfiniteUploadsApiHandler {
			/** @var object|null  $site_data — set by tests via reset_for_tests() */
			public $site_data;

			private static $instance;

			public static function get_instance(): self {
				if ( self::$instance === null ) {
					self::$instance = new self();
				}
				return self::$instance;
			}

			public static function reset_for_tests( ?object $site_data = null ): void {
				self::$instance            = new self();
				self::$instance->site_data = $site_data;
			}

			public function get_site_data() {
				return $this->site_data ? (object) [ 'site' => $this->site_data ] : false;
			}

			public function has_token(): bool {
				return true;
			}
		}
	}
}
