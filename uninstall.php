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
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}iu_media_folder_relationships" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}iu_media_folders" );
