<?php

function infinite_uploads_get_videos( $params = [] ) {
	// Is Infinite_Uploads_Video class loaded?
	if ( ! class_exists( 'InfiniteUploadsVideo' ) ) {
		require_once dirname( __FILE__ ) . '/inc/InfiniteUploadsVideo.php';
	}

	$iup_videos_instance = \ClikIT\InfiniteUploads\InfiniteUploadsVideo::get_instance();

	return $iup_videos_instance->get_videos( $params );
}