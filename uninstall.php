<?php
/**
 * Plugin uninstall handler. WordPress runs this when the plugin is deleted
 * (not on deactivation) and only after loading it with WP_UNINSTALL_PLUGIN
 * defined. Removes plugin-owned site options, the recurring cron, and the
 * three custom tables. The IU account's `site_id` is deliberately NOT
 * removed — reinstalling should reconnect to the same cloud account rather
 * than provision a fresh one.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

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

wp_unschedule_hook( 'infinite_uploads_sync' );

global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}infinite_uploads_files" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}infinite_uploads_media_folder_relationships" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}infinite_uploads_media_folders" );
