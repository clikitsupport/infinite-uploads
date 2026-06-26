<?php
/**
 * Plugin Name: Infinite Uploads — E2E Test Helpers
 * Description: Test-only REST endpoints used by Playwright specs to set up
 *              and tear down state without driving the UI for every step.
 *              DO NOT INSTALL IN PRODUCTION.
 * Version: 1.0.0
 * Requires PHP: 8.0
 *
 * @package ClikIT\InfiniteUploads\Tests
 */

// Refuse to load outside an explicit test environment.
if ( ! defined( 'WP_ENVIRONMENT_TYPE' ) || ! in_array( WP_ENVIRONMENT_TYPE, [ 'local', 'development' ], true ) ) {
	return;
}

add_action( 'rest_api_init', function () {
	register_rest_route(
		'iu-test/v1',
		'/reset',
		[
			'methods'             => 'POST',
			'callback'            => 'iu_test_reset_state',
			'permission_callback' => 'iu_test_permission_check',
		]
	);

	register_rest_route(
		'iu-test/v1',
		'/folders',
		[
			'methods'             => 'POST',
			'callback'            => 'iu_test_create_folder',
			'permission_callback' => 'iu_test_permission_check',
			'args'                => [
				'name'      => [ 'required' => true, 'type' => 'string' ],
				'parent_id' => [ 'required' => false, 'type' => 'integer', 'default' => 0 ],
			],
		]
	);

	register_rest_route(
		'iu-test/v1',
		'/folders/(?P<id>\d+)/media',
		[
			'methods'             => 'POST',
			'callback'            => 'iu_test_attach_media_to_folder',
			'permission_callback' => 'iu_test_permission_check',
			'args'                => [
				'id'            => [ 'required' => true, 'type' => 'integer' ],
				'attachment_id' => [ 'required' => true, 'type' => 'integer' ],
			],
		]
	);

	register_rest_route(
		'iu-test/v1',
		'/upload-folder',
		[
			'methods'             => 'POST',
			'callback'            => 'iu_test_set_upload_folder',
			'permission_callback' => 'iu_test_permission_check',
			'args'                => [
				'folder_id' => [ 'required' => true, 'type' => 'integer' ],
				'user_id'   => [ 'required' => false, 'type' => 'integer', 'default' => 1 ],
			],
		]
	);

	register_rest_route(
		'iu-test/v1',
		'/folder-count',
		[
			'methods'             => 'GET',
			'callback'            => 'iu_test_count_folders',
			'permission_callback' => 'iu_test_permission_check',
		]
	);

	register_rest_route(
		'iu-test/v1',
		'/attachment/(?P<id>\d+)/folder',
		[
			'methods'             => 'GET',
			'callback'            => 'iu_test_get_attachment_folder',
			'permission_callback' => 'iu_test_permission_check',
			'args'                => [
				'id' => [ 'required' => true, 'type' => 'integer' ],
			],
		]
	);

	// ------------------------------------------------------------------
	// Connection state — fake the "connected to IU cloud" state by seeding
	// the site options the plugin reads at setup() time. After /connect,
	// subsequent page requests see infinite_uploads_enabled() === true and
	// the URL rewriter activates.
	// ------------------------------------------------------------------
	register_rest_route(
		'iu-test/v1',
		'/connect',
		[
			'methods'             => 'POST',
			'callback'            => 'iu_test_connect',
			'permission_callback' => 'iu_test_permission_check',
			'args'                => [
				'cdn_host' => [ 'required' => false, 'type' => 'string', 'default' => 'test-cdn.iu-tests.local' ],
			],
		]
	);

	register_rest_route(
		'iu-test/v1',
		'/disconnect',
		[
			'methods'             => 'POST',
			'callback'            => 'iu_test_disconnect',
			'permission_callback' => 'iu_test_permission_check',
		]
	);

	// ------------------------------------------------------------------
	// Mark an attachment as "synced" — inserts a row into
	// {prefix}infinite_uploads_files with synced=1. Used to simulate the
	// state where IU has actually uploaded a file to its cloud.
	// ------------------------------------------------------------------
	register_rest_route(
		'iu-test/v1',
		'/mark-synced',
		[
			'methods'             => 'POST',
			'callback'            => 'iu_test_mark_synced',
			'permission_callback' => 'iu_test_permission_check',
			'args'                => [
				'attachment_id' => [ 'required' => true, 'type' => 'integer' ],
			],
		]
	);

	// ------------------------------------------------------------------
	// File exclusion controls — set/clear the iup_excluded_files option
	// plus the iu_file_exclusion_enabled toggle.
	// ------------------------------------------------------------------
	register_rest_route(
		'iu-test/v1',
		'/excluded-paths',
		[
			'methods'             => 'POST',
			'callback'            => 'iu_test_set_excluded_paths',
			'permission_callback' => 'iu_test_permission_check',
			'args'                => [
				'paths' => [ 'required' => true, 'type' => 'array', 'items' => [ 'type' => 'string' ] ],
			],
		]
	);

	// ------------------------------------------------------------------
	// Upload a bundled fixture file as a WP attachment — avoids dealing
	// with REST nonce / multipart auth from the Playwright side. Files
	// must live under tests/e2e/iu-test-helpers/fixtures/.
	// ------------------------------------------------------------------
	register_rest_route(
		'iu-test/v1',
		'/upload-fixture',
		[
			'methods'             => 'POST',
			'callback'            => 'iu_test_upload_fixture',
			'permission_callback' => 'iu_test_permission_check',
			'args'                => [
				'filename' => [ 'required' => true, 'type' => 'string' ],
			],
		]
	);

	// ------------------------------------------------------------------
	// Quick post creation helper — cheaper than the wp/v2/posts REST
	// endpoint because it sidesteps Gutenberg block validation.
	// ------------------------------------------------------------------
	register_rest_route(
		'iu-test/v1',
		'/posts',
		[
			'methods'             => 'POST',
			'callback'            => 'iu_test_create_post',
			'permission_callback' => 'iu_test_permission_check',
			'args'                => [
				'title'   => [ 'required' => true, 'type' => 'string' ],
				'content' => [ 'required' => true, 'type' => 'string' ],
				'status'  => [ 'required' => false, 'type' => 'string', 'default' => 'publish' ],
			],
		]
	);
} );

/**
 * Only let logged-in admins hit these endpoints — basic safeguard.
 */
function iu_test_permission_check() {
	return current_user_can( 'manage_options' );
}

/**
 * Wipe all IU folders + relationships. Called from test setUp to start
 * each spec from a known state.
 */
function iu_test_reset_state() {
	global $wpdb;

	$wpdb->query( "DELETE FROM {$wpdb->prefix}infinite_uploads_media_folder_relationships" );
	$wpdb->query( "DELETE FROM {$wpdb->prefix}infinite_uploads_media_folders" );

	// Clear the per-user "upload to this folder" meta for the default admin.
	delete_user_meta( 1, 'iu_upload_folder' );

	// Invalidate any cached folder dropdown options (BB module).
	wp_cache_delete( 'iu_bb_folder_options', 'infinite_uploads' );

	return rest_ensure_response( [ 'reset' => true ] );
}

/**
 * Create a folder directly via DB so tests can seed state cheaply.
 */
function iu_test_create_folder( $request ) {
	global $wpdb;

	$wpdb->insert(
		"{$wpdb->prefix}infinite_uploads_media_folders",
		[
			'name'       => $request['name'],
			'parent_id'  => $request['parent_id'],
			'sort_order' => 0,
			'created_by' => 1,
		],
		[ '%s', '%d', '%d', '%d' ]
	);

	return rest_ensure_response( [ 'id' => (int) $wpdb->insert_id, 'name' => $request['name'] ] );
}

function iu_test_attach_media_to_folder( $request ) {
	global $wpdb;

	$wpdb->replace(
		"{$wpdb->prefix}infinite_uploads_media_folder_relationships",
		[
			'folder_id'     => $request['id'],
			'attachment_id' => $request['attachment_id'],
		],
		[ '%d', '%d' ]
	);

	return rest_ensure_response( [ 'ok' => true ] );
}

function iu_test_set_upload_folder( $request ) {
	update_user_meta(
		$request['user_id'],
		'iu_upload_folder',
		(int) $request['folder_id']
	);

	return rest_ensure_response( [ 'ok' => true ] );
}

function iu_test_count_folders() {
	global $wpdb;

	$count = (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->prefix}infinite_uploads_media_folders"
	);

	return rest_ensure_response( [ 'count' => $count ] );
}

function iu_test_get_attachment_folder( $request ) {
	global $wpdb;

	$folder_id = $wpdb->get_var( $wpdb->prepare(
		"SELECT folder_id FROM {$wpdb->prefix}infinite_uploads_media_folder_relationships WHERE attachment_id = %d",
		$request['id']
	) );

	return rest_ensure_response( [
		'folder_id' => $folder_id !== null ? (int) $folder_id : null,
	] );
}

// -----------------------------------------------------------------------------
// Connection state helpers
// -----------------------------------------------------------------------------

/**
 * Seed every option InfiniteUploads::setup() reads so the plugin enters its
 * "connected" code path on the NEXT page request:
 *
 *   - iup_apitoken   — non-empty so InfiniteUploadsApiHandler::has_token() is true
 *   - iup_site_id    — non-zero so get_site_data() will look up cached api data
 *   - iup_api_data   — JSON-encoded fake site config; freshness < 12h satisfies
 *                      InfiniteUploadsApiHandler::get_site_data() cache check
 *   - iup_enabled    — toggles infinite_uploads_enabled() to true
 *
 * The CDN host defaults to test-cdn.iu-tests.local. Tests can override via the
 * `cdn_host` param. We DON'T need the host to actually resolve — Playwright
 * just inspects the `src` attribute, not the loaded image.
 */
function iu_test_connect( $request ) {
	$cdn_host = $request['cdn_host'];

	update_site_option( 'iup_apitoken', 'fake-test-token-' . wp_generate_password( 8, false ) );
	update_site_option( 'iup_site_id', 999 );
	update_site_option(
		'iup_api_data',
		wp_json_encode( [
			'refreshed' => time(),
			'site'      => [
				'upload_key'       => 'fake-access-key',
				'upload_secret'    => 'fake-secret-key',
				'upload_bucket'    => 'iu-test-bucket/999/test',
				'cdn_url'          => $cdn_host,
				'cname'            => $cdn_host,
				'upload_endpoint'  => 'https://s3.iu-tests.local',
				'upload_region'    => 'us-east-1',
				'cdn_enabled'      => true,
				'upload_writeable' => true,
			],
		] )
	);
	update_site_option( 'iup_enabled', 1 );

	return rest_ensure_response( [
		'connected' => true,
		'cdn_host'  => $cdn_host,
	] );
}

/**
 * Clear every option /connect set, restoring the plugin to its "fresh install,
 * not connected" state.
 */
function iu_test_disconnect() {
	delete_site_option( 'iup_apitoken' );
	delete_site_option( 'iup_site_id' );
	delete_site_option( 'iup_api_data' );
	delete_site_option( 'iup_enabled' );

	return rest_ensure_response( [ 'disconnected' => true ] );
}

// -----------------------------------------------------------------------------
// Sync state — pretend an attachment has been offloaded to the cloud
// -----------------------------------------------------------------------------

/**
 * Insert a row into {prefix}infinite_uploads_files with synced=1 for the
 * given attachment. Used to simulate the post-first-sync state where the
 * rewriter / sync-gate believes the file is already in the cloud.
 */
function iu_test_mark_synced( $request ) {
	global $wpdb;

	$attachment_id = (int) $request['attachment_id'];
	$file_path     = get_attached_file( $attachment_id );

	if ( ! $file_path || ! file_exists( $file_path ) ) {
		return new WP_Error(
			'attachment_not_found',
			"No file on disk for attachment {$attachment_id}",
			[ 'status' => 404 ]
		);
	}

	$uploads  = wp_upload_dir();
	$relative = '/' . ltrim( str_replace( $uploads['basedir'], '', $file_path ), '/' );
	$mime     = get_post_mime_type( $attachment_id ) ?: 'application/octet-stream';
	$size     = filesize( $file_path );

	$wpdb->query(
		$wpdb->prepare(
			"INSERT INTO {$wpdb->base_prefix}infinite_uploads_files
			 (file, size, modified, type, transferred, synced, deleted, errors)
			 VALUES (%s, %d, %d, %s, %d, 1, 0, 0)
			 ON DUPLICATE KEY UPDATE size = VALUES(size), modified = VALUES(modified),
			                         transferred = VALUES(transferred), synced = 1,
			                         deleted = 0, errors = 0",
			$relative,
			$size,
			(int) filemtime( $file_path ),
			$mime,
			$size
		)
	);

	return rest_ensure_response( [
		'marked'        => true,
		'attachment_id' => $attachment_id,
		'file'          => $relative,
		'size'          => $size,
	] );
}

// -----------------------------------------------------------------------------
// File exclusion controls
// -----------------------------------------------------------------------------

/**
 * Set or clear the file exclusion list. Passing an empty array also disables
 * the global toggle so the rewriter doesn't even check exclusions.
 */
function iu_test_set_excluded_paths( $request ) {
	$paths = array_values( array_filter( (array) $request['paths'], 'strlen' ) );

	update_site_option( 'iup_excluded_files', $paths );
	update_site_option( 'iu_file_exclusion_enabled', ! empty( $paths ) ? 'yes' : 'no' );

	// Helper::get_excluded_paths() memoizes per request. We can't clear that
	// from here (different process), but the next request will see fresh state.
	return rest_ensure_response( [
		'paths_set'        => $paths,
		'exclusion_toggle' => ! empty( $paths ) ? 'yes' : 'no',
	] );
}

// -----------------------------------------------------------------------------
// Test attachment factory — uploads a fixture file as a real WP attachment
// -----------------------------------------------------------------------------

/**
 * Copy a fixture from `tests/e2e/iu-test-helpers/fixtures/<filename>` into the
 * WP uploads directory and register it as an attachment. Returns the new
 * attachment ID + source URL so the Playwright spec can drive further state.
 *
 * We do this server-side instead of via wp/v2/media because WP REST media
 * uploads require an X-WP-Nonce header that's awkward to obtain from the
 * Playwright APIRequestContext.
 */
function iu_test_upload_fixture( $request ) {
	$filename = basename( (string) $request['filename'] ); // strip any path traversal
	$source   = __DIR__ . '/fixtures/' . $filename;

	if ( ! file_exists( $source ) ) {
		return new WP_Error(
			'fixture_not_found',
			"Fixture not bundled with the test plugin: {$filename}",
			[ 'status' => 404, 'source_path' => $source ]
		);
	}

	$uploads = wp_upload_dir();
	if ( ! empty( $uploads['error'] ) ) {
		return new WP_Error( 'upload_dir_error', $uploads['error'], [ 'status' => 500 ] );
	}

	$unique_name = wp_unique_filename( $uploads['path'], $filename );
	$dest        = $uploads['path'] . '/' . $unique_name;

	if ( ! @copy( $source, $dest ) ) {
		return new WP_Error( 'copy_failed', "Failed to copy {$source} → {$dest}", [ 'status' => 500 ] );
	}

	$filetype      = wp_check_filetype( $dest, null );
	$attachment_id = wp_insert_attachment(
		[
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', $unique_name ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		],
		$dest
	);

	if ( is_wp_error( $attachment_id ) ) {
		return $attachment_id;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	$attach_data = wp_generate_attachment_metadata( $attachment_id, $dest );
	wp_update_attachment_metadata( $attachment_id, $attach_data );

	return rest_ensure_response( [
		'attachment_id' => $attachment_id,
		'source_url'    => wp_get_attachment_url( $attachment_id ),
		'file'          => $dest,
		'relative'      => '/' . ltrim( str_replace( $uploads['basedir'], '', $dest ), '/' ),
	] );
}

// -----------------------------------------------------------------------------
// Post factory — sidesteps Gutenberg block validation that the REST media
// embed flow expects.
// -----------------------------------------------------------------------------

function iu_test_create_post( $request ) {
	$post_id = wp_insert_post(
		[
			'post_title'   => $request['title'],
			'post_content' => $request['content'],
			'post_status'  => $request['status'],
			'post_type'    => 'post',
			'post_author'  => 1,
		],
		true
	);

	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}

	return rest_ensure_response( [
		'id'        => $post_id,
		'permalink' => get_permalink( $post_id ),
	] );
}
