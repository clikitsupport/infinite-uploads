jQuery(document).ready(function ($) {
	// Poll for sync/download status every 10 seconds and show a notice when complete
	var pollInterval = 1000;
	var poller = setInterval(function () {
		$.post(iup_sync_status_params.ajax_url, {
			action: 'infinite-uploads-sync-status',
			nonce: iup_sync_status_params.nonce // or .download for download status
		}, function (response) {
			if (response.success && response.data && response.data.is_done) {
				var notice = '<div id="message" class="notice is-dismissible updated"><p>Sync/Download Complete successfully!</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

				// Add the notice to the top of the page.
				$('#wpbody-content .wrap').prepend(notice);

				// Don't add notice if it already exists.
				if ($('#message').length === 0) {
					$('#wpbody-content .wrap').prepend(notice);
				}

				// Stop polling.
				clearInterval(poller);
			}
		});
	}, pollInterval);
});
