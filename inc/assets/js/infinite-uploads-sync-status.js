jQuery(document).ready(function ($) {
	let pollInterval = 5000; // start with 5 second
	const maxInterval = 300000; // max 5 minutes

	function poll() {
		$.post(iup_sync_status_params.ajax_url, {
			action: 'infinite-uploads-sync-status',
			nonce: iup_sync_status_params.nonce
		}, function (response) {
			if (response.success && response.data && response.data.is_done) {
				const notice = `
					<div id="message" class="notice is-dismissible updated">
						<p>Sync/Download completed successfully!</p>
						<button type="button" class="notice-dismiss">
							<span class="screen-reader-text">Dismiss this notice.</span>
						</button>
					</div>
				`;

				if ($('#message').length === 0) {
					$('#wpbody-content .wrap').prepend(notice);
				}

				return; // stop polling
			}

			// Not done yet: increase interval exponentially up to max.
			pollInterval = Math.min(pollInterval * 2, maxInterval);

			if (pollInterval === maxInterval) {
				return; // stop polling when max interval is reached
			}
			// Schedule next poll
			setTimeout(poll, pollInterval);
		}).fail(function () {
			// Optional: retry with same interval if request fails
			console.warn('Polling failed, retrying in ' + pollInterval / 1000 + ' seconds');
			setTimeout(poll, pollInterval);
		});
	}

	// Start polling
	poll();
});
