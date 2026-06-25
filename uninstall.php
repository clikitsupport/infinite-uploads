<?php
// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

//delete options except site_id
if ( is_multisite() ) {
	delete_site_option( 'iup_installed' );
	delete_site_option( 'iup_files_scanned' );
	delete_site_option( 'iup_enabled' );
	delete_site_option( 'iup_apitoken' );
	delete_site_option( 'iup_api_data' );
} else {
	delete_option( 'iup_installed' );
	delete_option( 'iup_files_scanned' );
	delete_option( 'iup_enabled' );
	delete_option( 'iup_apitoken' );
	delete_option( 'iup_api_data' );
}

//remove cronjob
wp_unschedule_hook( 'infinite_uploads_sync' );

// drop custom database tables
global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}infinite_uploads_files" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}infinite_uploads_media_folder_relationships" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}infinite_uploads_media_folders" );

/**
 * Drop the per-site Media Library Usage Scanner tables.
 *
 * These tables use $wpdb->prefix (one set per subsite), so on multisite we
 * must iterate every site and switch context to drop the correct tables and
 * delete the per-site schema-version option.
 */
function infinite_uploads_uninstall_media_usage_tables() {
	global $wpdb;

	$tables = array(
		"{$wpdb->prefix}infinite_uploads_media_references",
		"{$wpdb->prefix}infinite_uploads_media_scan_items",
		"{$wpdb->prefix}infinite_uploads_media_scan_runs",
	);

	foreach ( $tables as $table ) {
		$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
	}

	delete_option( 'iup_media_usage_db_version' );
	delete_option( 'iup_media_usage_scan_state' );
	delete_option( 'iup_media_usage_debug_log' );
	delete_option( 'iup_media_usage_lock' );
}

// Remove the network-wide Media Library Usage Scanner toggle.
if ( is_multisite() ) {
	delete_site_option( 'iu_media_usage_scanner_enabled' );
} else {
	delete_option( 'iu_media_usage_scanner_enabled' );
}

if ( is_multisite() ) {
	$iup_site_ids = get_sites( array(
		'fields' => 'ids',
		'number' => 0,
	) );

	foreach ( $iup_site_ids as $iup_site_id ) {
		switch_to_blog( $iup_site_id );
		infinite_uploads_uninstall_media_usage_tables();
		restore_current_blog();
	}
} else {
	infinite_uploads_uninstall_media_usage_tables();
}
