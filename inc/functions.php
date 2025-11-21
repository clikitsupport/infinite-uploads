<?php

function infinite_uploads_get_videos( $params = [] ) {
	// Is Infinite_Uploads_Video class loaded?
	if ( ! class_exists( 'Infinite_Uploads_Video' ) ) {
		require_once dirname( __FILE__ ) . '/inc/class-infinite-uploads-video.php';
	}

	$iup_videos_instance = Infinite_Uploads_Video::get_instance();

	return $iup_videos_instance->get_videos( $params );
}