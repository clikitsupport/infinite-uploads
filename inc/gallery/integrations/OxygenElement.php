<?php

namespace ClikIT\InfiniteUploads;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Oxygen Builder integration for IU Media Folder Gallery.
 *
 * Oxygen does not expose a simple element/widget API comparable to other builders,
 * so the recommended approach is:
 *   1. The [iu_gallery] shortcode works inside Oxygen's Code Block element.
 *   2. We enqueue IU folder scripts in the Oxygen builder UI so media pickers
 *      show IU folders when users browse media inside Oxygen.
 */

// Enqueue IU admin assets in Oxygen builder context.
add_action( 'oxygen_enqueue_ui_scripts', function () {
	MediaFolders::get_instance()->enqueue_assets( 'upload.php' );
} );
