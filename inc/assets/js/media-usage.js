/**
 * Media Library Usage Scanner admin UI.
 *
 * Drives the batched scan loop, the enable action, and the row actions
 * (ignore, copy URL, reference detail). All requests carry the nonce and are
 * re-checked server-side; this script is only progressive enhancement.
 */
( function ( $ ) {
	'use strict';

	if ( ! window.iu_media_usage ) {
		return;
	}

	var cfg     = window.iu_media_usage;
	var running = false;
	var lastSig = '';   // progress signature, for stall detection
	var stalls  = 0;    // consecutive polls with no progress

	function post( action, data ) {
		data = data || {};
		data.action = action;
		data.nonce  = cfg.nonce;
		return $.post( cfg.ajaxurl, data );
	}

	/* ----- Enable feature ----- */
	$( '#iu-mu-enable' ).on( 'click', function () {
		var $btn = $( this ).prop( 'disabled', true );
		post( 'infinite-uploads-media-usage-enable' )
			.done( function ( res ) {
				if ( res && res.success ) {
					window.location.reload();
				} else {
					$btn.prop( 'disabled', false );
					window.alert( ( res && res.data && res.data.message ) || cfg.strings.error );
				}
			} )
			.fail( function () {
				$btn.prop( 'disabled', false );
				window.alert( cfg.strings.error );
			} );
	} );

	/* ----- Scan loop (background worker + status polling) ----- */
	var $run      = $( '#iu-mu-run' );
	var $cancel   = $( '#iu-mu-cancel' );
	var $pause    = $( '#iu-mu-pause' );
	var $resume   = $( '#iu-mu-resume' );
	var $progress = $( '.iu-mu-progress' );
	var $fill     = $( '.iu-mu-progress-fill' );
	var $msg      = $( '.iu-mu-progress-msg' );
	var $keepopen = $( '.iu-mu-keepopen' );

	function setProgress( percent, message ) {
		$fill.css( 'width', ( percent || 0 ) + '%' );
		$msg.text( message || '' );
	}

	function showControls( state ) {
		// state: 'idle' | 'running' | 'paused'
		$run.toggle( state === 'idle' );
		$cancel.toggle( state !== 'idle' );
		$pause.toggle( state === 'running' );
		$resume.toggle( state === 'paused' );
		$progress.toggle( state !== 'idle' );
	}

	function poll() {
		if ( ! running ) {
			return;
		}
		// Drive a batch in the browser and read progress. This advances the scan
		// even when WP-Cron / Action Scheduler isn't running on the host; a
		// server-side lock keeps it from colliding with the background worker.
		post( 'infinite-uploads-media-usage-step' )
			.done( function ( res ) {
				if ( ! res || ! res.success ) {
					stopScan( true );
					return;
				}
				var d = res.data || {};
				if ( 'complete' === d.status ) {
					setProgress( 100, cfg.strings.complete );
					running = false;
					window.location.reload();
					return;
				}
				if ( 'idle' === d.status ) {
					stopScan( false );
					return;
				}
				if ( 'paused' === d.status ) {
					showControls( 'paused' );
					$keepopen.hide();
					setProgress( d.percent, d.message || cfg.strings.paused );
					return; // stop polling until resumed
				}

				// Stall detection: if nothing about the run changes across many
				// steps, the batch isn't advancing — show a clear message rather
				// than spinning forever.
				var sig = [ d.phase, d.source_index, d.cursor, d.scanned, d.percent ].join( '|' );
				if ( sig === lastSig ) {
					stalls++;
				} else {
					stalls  = 0;
					lastSig = sig;
				}
				if ( stalls >= 15 ) {
					running = false;
					showControls( 'paused' );
					$keepopen.hide();
					setProgress( d.percent, cfg.strings.stalled );
					return;
				}

				// When the background worker can't carry the scan (cron not
				// running), tell the user to keep this tab open.
				if ( 'stuck' === d.background ) {
					$keepopen.show();
				} else {
					$keepopen.hide();
				}

				setProgress( d.percent, d.message || cfg.strings.scanning );
				window.setTimeout( poll, 600 );
			} )
			.fail( function () {
				stopScan( true );
			} );
	}

	function startScan() {
		running = true;
		showControls( 'running' );
		setProgress( 1, cfg.strings.scanning );
		post( 'infinite-uploads-media-usage-start' )
			.done( function ( res ) {
				if ( res && res.success ) {
					window.setTimeout( poll, 1000 );
				} else {
					stopScan( true );
				}
			} )
			.fail( function () {
				stopScan( true );
			} );
	}

	function stopScan( isError ) {
		running = false;
		showControls( 'idle' );
		if ( isError ) {
			$progress.show();
			$msg.text( cfg.strings.error );
		}
	}

	$run.on( 'click', function () {
		if ( running ) {
			return;
		}
		if ( window.confirm( cfg.strings.confirm_run ) ) {
			startScan();
		}
	} );

	$pause.on( 'click', function () {
		post( 'infinite-uploads-media-usage-pause' ).always( function () {
			running = false;
			showControls( 'paused' );
			$keepopen.hide();
			$msg.text( cfg.strings.paused );
		} );
	} );

	$resume.on( 'click', function () {
		post( 'infinite-uploads-media-usage-resume' ).always( function () {
			running = true;
			showControls( 'running' );
			window.setTimeout( poll, 1000 );
		} );
	} );

	$cancel.on( 'click', function () {
		post( 'infinite-uploads-media-usage-cancel' ).always( function () {
			stopScan( false );
		} );
	} );

	// If a scan is already running when the page loads, resume polling.
	if ( $progress.length && $progress.data( 'active' ) ) {
		running = true;
		showControls( 'running' );
		window.setTimeout( poll, 1000 );
	}

	/* ----- Row actions ----- */
	$( document ).on( 'click', '.iu-mu-ignore', function ( e ) {
		e.preventDefault();
		var $link = $( this );
		post( 'infinite-uploads-media-usage-ignore', {
			attachment_id: $link.data( 'id' ),
			ignored: $link.data( 'ignored' )
		} ).always( function () {
			window.location.reload();
		} );
	} );

	$( document ).on( 'click', '.iu-mu-rescan', function ( e ) {
		e.preventDefault();
		var $link = $( this );
		if ( $link.data( 'busy' ) ) {
			return;
		}
		$link.data( 'busy', true );

		// The Rescan link lives in the row-actions, which WordPress hides unless
		// the row is hovered — so changing its text gives no visible feedback.
		// Show a spinner + label in the always-visible Usage cell instead.
		var $status = $link.closest( 'tr' ).find( '.column-usage_status' );
		if ( $status.length ) {
			$status.empty()
				.append( $( '<span class="spinner is-active"></span>' ).css( { 'float': 'none', margin: '0 6px 0 0', 'vertical-align': 'middle' } ) )
				.append( document.createTextNode( cfg.strings.rescanning ) );
		} else {
			$link.text( cfg.strings.rescanning );
		}

		post( 'infinite-uploads-media-usage-rescan', {
			attachment_id: $link.data( 'id' )
		} ).always( function () {
			window.location.reload();
		} );
	} );

	$( document ).on( 'click', '.iu-mu-copy', function ( e ) {
		e.preventDefault();
		var url = String( $( this ).data( 'url' ) || '' );
		if ( ! url ) {
			return;
		}
		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			navigator.clipboard.writeText( url );
		} else {
			window.prompt( '', url );
		}
	} );

	var $detail = $( '#iu-mu-detail' );

	$( document ).on( 'click', '.iu-mu-detail', function ( e ) {
		e.preventDefault();
		var id = $( this ).data( 'id' );
		$detail.find( '.iu-mu-detail-rows' ).html( '' );
		post( 'infinite-uploads-media-usage-detail', { attachment_id: id } ).done( function ( res ) {
			if ( res && res.success ) {
				$detail.find( '.iu-mu-detail-rows' ).html( res.data.rows );
				$detail.show();
			}
		} );
	} );

	$detail.on( 'click', '.iu-mu-detail-close', function () {
		$detail.hide();
	} );

	/* ----- Bug report modal ----- */
	var $bugreport = $( '#iu-mu-bugreport' );

	$( document ).on( 'click', '#iu-mu-bugreport-open', function ( e ) {
		e.preventDefault();
		$bugreport.show();
	} );

	$bugreport.on( 'click', '.iu-mu-bugreport-close', function () {
		$bugreport.hide();
	} );

	// Close when clicking the dimmed backdrop, but not the dialog itself.
	$bugreport.on( 'click', function ( e ) {
		if ( e.target === this ) {
			$bugreport.hide();
		}
	} );

	// The form posts to admin-post.php, which replies with a file download, so
	// the page stays put. Close the modal shortly after submit for confirmation.
	$bugreport.on( 'submit', '.iu-mu-bugreport-form', function () {
		setTimeout( function () {
			$bugreport.hide();
		}, 600 );
	} );

	/* ----- Possible Duplicates tab ----- */
	var dupRunning = false;

	$( '#iu-dup-enable' ).on( 'click', function () {
		var $btn = $( this ).prop( 'disabled', true );
		post( 'infinite-uploads-media-usage-dup-enable' )
			.done( function ( res ) {
				if ( res && res.success ) {
					window.location.reload();
				} else {
					$btn.prop( 'disabled', false );
					window.alert( ( res && res.data && res.data.message ) || cfg.strings.error );
				}
			} )
			.fail( function () {
				$btn.prop( 'disabled', false );
				window.alert( cfg.strings.error );
			} );
	} );

	var $dupScan     = $( '#iu-dup-scan' );
	var $dupProgress = $dupScan.closest( '.iu-mu-controls' ).find( '.iu-mu-progress' );
	var $dupFill     = $dupProgress.find( '.iu-mu-progress-fill' );
	var $dupMsg      = $dupProgress.find( '.iu-mu-progress-msg' );

	function dupStep( start ) {
		post( 'infinite-uploads-media-usage-dup-scan', { start: start ? '1' : '0' } )
			.done( function ( res ) {
				if ( ! res || ! res.success ) {
					dupRunning = false;
					$dupMsg.text( ( res && res.data && res.data.message ) || cfg.strings.error );
					$dupScan.prop( 'disabled', false );
					return;
				}
				var d = res.data || {};
				if ( 'complete' === d.status ) {
					$dupFill.css( 'width', '100%' );
					window.location.reload();
					return;
				}
				if ( 'idle' === d.status ) {
					dupRunning = false;
					$dupProgress.hide();
					$dupScan.prop( 'disabled', false );
					return;
				}
				$dupFill.css( 'width', ( d.percent || 1 ) + '%' );
				$dupMsg.text( cfg.strings.dup_scanning );
				window.setTimeout( function () { dupStep( false ); }, 200 );
			} )
			.fail( function () {
				dupRunning = false;
				$dupMsg.text( cfg.strings.error );
				$dupScan.prop( 'disabled', false );
			} );
	}

	$dupScan.on( 'click', function () {
		if ( dupRunning || ! window.confirm( cfg.strings.dup_confirm ) ) {
			return;
		}
		dupRunning = true;
		$dupScan.prop( 'disabled', true );
		$dupProgress.show();
		$dupFill.css( 'width', '1%' );
		$dupMsg.text( cfg.strings.dup_scanning );
		dupStep( true );
	} );

	$( document ).on( 'click', '.iu-dup-ignore', function ( e ) {
		e.preventDefault();
		var $link = $( this );
		post( 'infinite-uploads-media-usage-dup-ignore', {
			group_key: $link.data( 'key' ),
			ignored: $link.data( 'ignored' )
		} ).always( function () {
			window.location.reload();
		} );
	} );

	$( document ).on( 'click', '.iu-dup-verify', function ( e ) {
		e.preventDefault();
		var $btn = $( this ).prop( 'disabled', true );
		var $res = $btn.closest( '.iu-dup-group' ).find( '.iu-dup-verify-result' );
		$res.show().removeAttr( 'data-result' ).text( cfg.strings.verifying );
		post( 'infinite-uploads-media-usage-dup-verify', { group_key: $btn.data( 'key' ) } )
			.done( function ( res ) {
				$btn.prop( 'disabled', false );
				if ( res && res.success ) {
					$res.text( res.data.message ).attr( 'data-result', res.data.result );
				} else {
					$res.text( ( res && res.data && res.data.message ) || cfg.strings.error );
				}
			} )
			.fail( function () {
				$btn.prop( 'disabled', false );
				$res.text( cfg.strings.error );
			} );
	} );

	function dupSettings( enabled ) {
		return post( 'infinite-uploads-media-usage-dup-settings', {
			enabled: enabled ? '1' : '0',
			show_weak: $( '#iu-dup-set-weak' ).is( ':checked' ) ? '1' : '0',
			allow_exact_hash: $( '#iu-dup-set-hash' ).is( ':checked' ) ? '1' : '0',
			batch_size: parseInt( $( '#iu-dup-set-batch' ).val(), 10 ) || 100
		} );
	}

	$( '#iu-dup-save-settings' ).on( 'click', function () {
		var $btn = $( this ).prop( 'disabled', true );
		dupSettings( true ).always( function () {
			$btn.prop( 'disabled', false );
			$( '#iu-dup-settings-saved' ).fadeIn();
			window.setTimeout( function () { $( '#iu-dup-settings-saved' ).fadeOut(); }, 3000 );
		} );
	} );

	$( '#iu-dup-disable' ).on( 'click', function () {
		dupSettings( false ).always( function () {
			window.location.reload();
		} );
	} );
}( jQuery ) );
