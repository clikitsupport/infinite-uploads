<?php
/**
 * PHPUnit bootstrap.
 *
 * Brain Monkey provides function-level mocks for WordPress core functions used
 * by the plugin under test, so we don't have to spin up MySQL or load WordPress
 * itself. Mockery is used (via Brain Monkey) for object doubles — global $wpdb,
 * S3 client, etc.
 *
 * Load order matters: Patchwork (Brain Monkey's underlying function-redefiner)
 * must be the FIRST thing required so it can hook into PHP's autoloader before
 * any code-under-test gets loaded. Without that, attempting to mock a function
 * that has already been declared yields a Patchwork DefinedTooEarly exception.
 *
 * Source files are NOT autoloaded en-masse here. Each test class requires only
 * the file it exercises. This keeps test runs fast and avoids load-order
 * surprises in files that register hooks at file-load time.
 *
 * @package ClikIT\InfiniteUploads\Tests
 */

declare( strict_types=1 );

if ( ! defined( 'IU_TESTS_ROOT' ) ) {
	define( 'IU_TESTS_ROOT', __DIR__ );
}
if ( ! defined( 'IU_PLUGIN_ROOT' ) ) {
	define( 'IU_PLUGIN_ROOT', dirname( __DIR__ ) );
}

// Patchwork first — it must register its autoloader before any user code is
// loaded so functions can be redefined later by Brain Monkey.
require_once IU_PLUGIN_ROOT . '/vendor/antecedent/patchwork/Patchwork.php';

require_once IU_PLUGIN_ROOT . '/vendor/autoload.php';

// WordPress sometimes defines these as constants; the plugin files reference
// them. Stub them so source can be required cleanly into the test process.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', IU_PLUGIN_ROOT . '/' );
}
if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	define( 'WP_CONTENT_DIR', sys_get_temp_dir() . '/iu-tests-wp-content' );
}
if ( ! defined( 'WP_CONTENT_URL' ) ) {
	define( 'WP_CONTENT_URL', 'http://example.test/wp-content' );
}
if ( ! defined( 'INFINITE_UPLOADS_VERSION' ) ) {
	define( 'INFINITE_UPLOADS_VERSION', '3.2.5' );
}
if ( ! defined( 'MINUTE_IN_SECONDS' ) ) {
	define( 'MINUTE_IN_SECONDS', 60 );
}
if ( ! defined( 'YEAR_IN_SECONDS' ) ) {
	define( 'YEAR_IN_SECONDS', 31536000 );
}
if ( ! defined( 'MB_IN_BYTES' ) ) {
	define( 'MB_IN_BYTES', 1048576 );
}
if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}
if ( ! defined( 'ARRAY_N' ) ) {
	define( 'ARRAY_N', 'ARRAY_N' );
}
if ( ! defined( 'OBJECT' ) ) {
	define( 'OBJECT', 'OBJECT' );
}
