<?php

namespace ClikIT\InfiniteUploads;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce integration for IU Media Folder Gallery.
 *
 * Enqueues the IU Media Folders sidebar on WooCommerce product edit pages so
 * the folder tree is available when adding/selecting product gallery images
 * via the WordPress media picker.
 */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || $screen->post_type !== 'product' ) {
		return;
	}

	// Reuse the MediaFolders enqueue_assets method (it checks the hook itself,
	// so we call the underlying enqueue logic directly).
	MediaFolders::get_instance()->enqueue_assets( $hook );
}, 20 );
