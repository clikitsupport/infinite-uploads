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
