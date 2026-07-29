<?php
/**
 * EWWW Image Optimizer integration — declare Infinite Uploads to EWWW so its
 * JS WebP and Picture WebP rewriting modes can auto-detect the IU CDN.
 *
 * EWWW already auto-detects WP Offload Media, S3 Uploads, and WP Stateless. The
 * `eio_s3_active` / `eio_s3_object_prefix` filters (introduced in EWWW commit
 * cb5c737) are the opt-in path for every other offloader. Declaring ourselves
 * via these filters means EWWW's JS WebP and Picture WebP rewriting modes can
 * rewrite to our CDN automatically — without it, users have to manually toggle
 * "Force WebP" and paste the CDN domain into EWWW's settings.
 *
 * IU's CDN URL has two shapes depending on the account:
 *   1. Vanity host  → https://tenant.infiniteuploads.cloud/         (no path)
 *   2. Generic path → https://<cdn-host>/<user_id>/<site_id>/...    (path prefix)
 * `get_s3_url()` already resolves to whichever applies; we just split it into
 * host (for eio_s3_active) and path-prefix (for eio_s3_object_prefix).
 *
 * `eio_s3_object_versioning_enabled` stays at its default of false — IU does
 * not embed numeric versioning segments in its CDN URLs.
 *
 * Easy IO guard: EWWW commit cb5c737 added the eio_s3_* filters but left two
 * local variables ($s3_scheme and $s3_domain) undefined on the filter-driven
 * code path inside Base::content_url(). When `s3_active` is truthy, line 1857
 * of that method does `$this->site_url = $s3_scheme . '://' . $s3_domain`,
 * which yields the malformed string '://' if those locals weren't populated by
 * one of the built-in detection blocks (WPOM / S3 Uploads / WP Stateless).
 * That broken site_url then flows into Easy IO's activate_site() call, which
 * registers a blank domain with the Easy IO backend (the visible symptom is a
 * blank exactdn_site_url on the EWWW settings page). Until EWWW patches that
 * bug upstream, we skip our filter when Easy IO is active so its detection
 * path is taken instead of ours. Easy IO additionally disables JS WebP and
 * Picture WebP modes when it's active (see common.php lines 226 and 237), so
 * skipping the filter here costs nothing functionally — the integration only
 * matters when Easy IO is off.
 *
 * @package ClikIT\InfiniteUploads
 */

namespace ClikIT\InfiniteUploads;

if ( ! function_exists( __NAMESPACE__ . '\\infinite_uploads_get_cdn_url_for_ewww' ) ) {

	/**
	 * Resolve the CDN URL we should expose to EWWW, or '' to defer to EWWW's
	 * own detection.
	 *
	 * @return string CDN URL (e.g. https://tenant.infiniteuploads.cloud) or ''.
	 */
	function infinite_uploads_get_cdn_url_for_ewww() {
		if ( ! \infinite_uploads_enabled() ) {
			return '';
		}
		// Easy IO conflict — see docblock above. Treat as "not applicable" so
		// both filter callbacks below early-return without modifying their
		// inputs.
		//
		// No static cache: EWWW's Base::content_url() can be invoked very
		// early (during EWWW's own init), and option state may not be settled
		// at that point. A static would freeze a stale answer for the rest of
		// the request; the get_option calls below are cheap, so we just
		// re-check.
		if ( \function_exists( 'ewww_image_optimizer_get_option' ) ) {
			if ( \ewww_image_optimizer_get_option( 'ewww_image_optimizer_exactdn' ) ) {
				return '';
			}
		} elseif ( \get_option( 'ewww_image_optimizer_exactdn' ) ) {
			// Fallback if EWWW's helper isn't loaded yet at the moment we're
			// checked.
			return '';
		}
		$instance = InfiniteUploads::get_instance();
		if ( empty( $instance->bucket ) ) {
			return '';
		}
		$url = $instance->get_s3_url();
		return \is_string( $url ) ? $url : '';
	}
}

if ( ! function_exists( __NAMESPACE__ . '\\filter_eio_s3_active' ) ) {

	/**
	 * Callback for the eio_s3_active filter. Named (not anonymous) so it can
	 * be unit-tested directly and so remove_filter() works against it if a
	 * site needs to opt out.
	 *
	 * @param  bool|string  $s3_active  Existing filter value from EWWW.
	 *
	 * @return bool|string
	 */
	function filter_eio_s3_active( $s3_active ) {
		$cdn_url = infinite_uploads_get_cdn_url_for_ewww();
		if ( $cdn_url === '' ) {
			return $s3_active;
		}
		$host = \wp_parse_url( $cdn_url, PHP_URL_HOST );
		return $host ? $host : $s3_active;
	}
}

if ( ! function_exists( __NAMESPACE__ . '\\filter_eio_s3_object_prefix' ) ) {

	/**
	 * Callback for the eio_s3_object_prefix filter. Named for the same
	 * reasons as filter_eio_s3_active above.
	 *
	 * @param  string  $prefix  Existing filter value from EWWW.
	 *
	 * @return string
	 */
	function filter_eio_s3_object_prefix( $prefix ) {
		$cdn_url = infinite_uploads_get_cdn_url_for_ewww();
		if ( $cdn_url === '' ) {
			return $prefix;
		}
		$path = \wp_parse_url( $cdn_url, PHP_URL_PATH );
		if ( ! \is_string( $path ) || $path === '' || $path === '/' ) {
			return $prefix;
		}
		return \trim( $path, '/' );
	}
}

\add_filter( 'eio_s3_active', __NAMESPACE__ . '\\filter_eio_s3_active' );
\add_filter( 'eio_s3_object_prefix', __NAMESPACE__ . '\\filter_eio_s3_object_prefix' );
