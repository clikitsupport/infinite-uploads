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

	// Note: a stub for InfiniteUploadsApiHandler used to live here, but
	// ApiHandlerTest needs the REAL class — and PHP forbids two declarations
	// with the same FQCN in one process. Tests that need to control what
	// InfiniteUploadsHelper::get_iu_api_data() returns should seed
	// Helper::$request_cache['iu_api_data'] directly via reflection.
}
