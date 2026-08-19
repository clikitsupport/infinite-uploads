<?php

/**
 * Public wrapper for {@see InfiniteUploadsVideo::get_videos()} — thin
 * template-tag entry point so themes can query cloud-hosted videos without
 * touching the class directly. Lazy-loads the class file on first call.
 *
 * @param  array  $params  Passed through to InfiniteUploadsVideo::get_videos().
 *
 * @return mixed  Whatever InfiniteUploadsVideo::get_videos() returns.
 */
function infinite_uploads_get_videos( $params = [] ) {
	if ( ! class_exists( '\ClikIT\InfiniteUploads\InfiniteUploadsVideo' ) ) {
		require_once dirname( __FILE__ ) . '/inc/InfiniteUploadsVideo.php';
	}

	$iup_videos_instance = \ClikIT\InfiniteUploads\InfiniteUploadsVideo::get_instance();

	return $iup_videos_instance->get_videos( $params );
}