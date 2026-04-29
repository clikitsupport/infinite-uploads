<?php

namespace ClikIT\InfiniteUploads;

use Imagify\Context\ContextInterface;

/**
 * Imagify compatibility for Infinite Uploads offloaded media.
 */
class InfiniteUploadsImagify {
	/**
	 * Singleton instance.
	 *
	 * @var InfiniteUploadsImagify|null
	 */
	private static $instance = null;

	/**
	 * Infinite Uploads instance.
	 *
	 * @var InfiniteUploads
	 */
	private $infinite_uploads;

	/**
	 * Get singleton instance.
	 *
	 * @return InfiniteUploadsImagify
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->infinite_uploads = InfiniteUploads::get_instance();
	}

	/**
	 * Register Imagify compatibility hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'imagify_cdn', [ $this, 'register_cdn' ], 10, 3 );
		add_filter( 'imagify_cdn_source', [ $this, 'register_cdn_source' ] );
		add_filter( 'imagify_webp_picture_process_image', [ $this, 'mark_picture_sources' ], 10, 2 );
		add_filter( 'option_imagify_settings', [ $this, 'force_picture_delivery' ] );
		add_filter( 'site_option_imagify_settings', [ $this, 'force_picture_delivery' ] );

		// Optimization lifecycle: Imagify needs files on real disk (curl_file_create
		// doesn't support stream wrappers). Download from iu:// before the API call,
		// push optimized + WebP/AVIF sidecars back after.
		add_filter( 'imagify_before_optimize_size', [ $this, 'before_optimize_size' ], 10, 7 );
		add_action( 'imagify_after_optimize', [ $this, 'after_optimize' ], 10, 2 );
		add_action( 'imagify_after_restore_media', [ $this, 'after_restore_media' ], 10, 4 );
	}

	/**
	 * Ensure the file to optimize exists on local disk. Imagify uses curl_file_create()
	 * to POST images to its API — stream wrappers (iu://) are not supported there.
	 *
	 * @param  null|\WP_Error                                              $response   Filter in-flight.
	 * @param  \Imagify\Optimization\Process\ProcessInterface              $process    Imagify optimization process.
	 * @param  \Imagify\Optimization\File                                  $file       File being optimized.
	 * @param  string                                                      $thumb_size Thumbnail size name.
	 * @param  int                                                         $level      Optimization level.
	 * @param  bool                                                        $next_gen   Whether this is a WebP/AVIF pass.
	 * @param  bool                                                        $disabled   Whether optimization is disabled for this size.
	 *
	 * @return null|\WP_Error
	 */
	public function before_optimize_size( $response, $process, $file, $thumb_size, $level, $next_gen, $disabled ) {
		if ( is_wp_error( $response ) || 'wp' !== $process->get_media()->get_context() ) {
			return $response;
		}

		$cdn = $process->get_media()->get_cdn();
		if ( ! $cdn instanceof InfiniteUploadsImagifyCDN ) {
			return $response;
		}

		$local_path = $file->get_path();
		if ( ! $local_path || file_exists( $local_path ) ) {
			return $response;
		}

		$result = $cdn->get_files_from_cdn( [ $local_path ] );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $response;
	}

	/**
	 * After Imagify finishes optimizing, push the optimized files and any generated
	 * WebP/AVIF sidecars from local disk back to iu://.
	 *
	 * @param  \Imagify\Optimization\Process\ProcessInterface  $process  Optimization process.
	 * @param  array                                           $item     Queue item data.
	 */
	public function after_optimize( $process, $item ) {
		if ( 'wp' !== $process->get_media()->get_context() ) {
			return;
		}

		$cdn = $process->get_media()->get_cdn();
		if ( ! $cdn instanceof InfiniteUploadsImagifyCDN ) {
			return;
		}

		$cdn->send_to_cdn( ! empty( $item['data']['is_new_upload'] ) );
	}

	/**
	 * After Imagify restores a media from backup: push restored originals to iu://,
	 * then remove any WebP/AVIF sidecars from cloud storage (the restored original is
	 * un-optimized and its old sidecars are no longer valid).
	 *
	 * @param  \Imagify\Optimization\Process\ProcessInterface  $process   Optimization process.
	 * @param  bool|\WP_Error                                  $response  Restore result.
	 * @param  array                                           $files     Files before restoration.
	 * @param  array                                           $data      Optimization data before deletion.
	 */
	public function after_restore_media( $process, $response, $files, $data ) {
		if ( 'wp' !== $process->get_media()->get_context() ) {
			return;
		}

		$cdn = $process->get_media()->get_cdn();
		if ( ! $cdn instanceof InfiniteUploadsImagifyCDN ) {
			return;
		}

		if ( is_wp_error( $response ) && 'copy_failed' === $response->get_error_code() ) {
			// Nothing was restored, nothing to push.
			return;
		}

		$cdn->send_to_cdn( false );

		// Collect WebP/AVIF sidecar paths that existed before restore and delete them from cloud.
		if ( empty( $files ) || ! is_array( $files ) ) {
			return;
		}

		$webp_suffix = defined( get_class( $process ) . '::WEBP_SUFFIX' ) ? $process::WEBP_SUFFIX : '@imagify-webp';
		$avif_suffix = defined( get_class( $process ) . '::AVIF_SUFFIX' ) ? $process::AVIF_SUFFIX : '@imagify-avif';
		$nextgen_files = [];

		foreach ( $files as $size_name => $size_info ) {
			if ( empty( $size_info['path'] ) || empty( $size_info['mime-type'] ) ) {
				continue;
			}
			if ( 0 !== strpos( $size_info['mime-type'], 'image/' ) ) {
				continue;
			}
			$base_path = $size_info['path'];
			foreach ( [ [ $webp_suffix, 'webp' ], [ $avif_suffix, 'avif' ] ] as $suffix_pair ) {
				list( $size_suffix, $ext ) = $suffix_pair;
				if ( empty( $data['sizes'][ $size_name . $size_suffix ]['success'] ) ) {
					continue;
				}
				$nextgen_files[] = $base_path . '.' . $ext;
			}
		}

		if ( $nextgen_files ) {
			$cdn->remove_files_from_cdn( $nextgen_files );
		}
	}

	/**
	 * Force picture delivery for Imagify next-gen output while IU CDN is enabled.
	 *
	 * @param mixed $options Imagify options.
	 *
	 * @return mixed
	 */
	public function force_picture_delivery( $options ) {
		if ( ! $this->is_active() || ! is_array( $options ) ) {
			return $options;
		}

		$options['display_nextgen_method'] = 'picture';
		$options['display_webp_method']    = 'picture';

		return $options;
	}

	/**
	 * Register Infinite Uploads as Imagify CDN provider.
	 *
	 * @param mixed            $cdn      Current CDN instance.
	 * @param int              $media_id Attachment ID.
	 * @param ContextInterface $context  Imagify context.
	 *
	 * @return mixed
	 */
	public function register_cdn( $cdn, $media_id, $context ) {
		if ( ! $this->is_active() || ! $context instanceof ContextInterface || 'wp' !== $context->get_name() ) {
			return $cdn;
		}

		$cloud_path = self::get_attachment_cloud_path( $media_id );
		if ( ! $cloud_path || 0 !== strpos( $cloud_path, 'iu://' ) ) {
			return $cdn;
		}

		if ( InfiniteUploadsHelper::is_file_exclusion_enabled() && InfiniteUploadsHelper::is_path_excluded( $cloud_path ) ) {
			return $cdn;
		}

		return new InfiniteUploadsImagifyCDN( $media_id );
	}

	/**
	 * Tell Imagify which CDN URL maps to Infinite Uploads.
	 *
	 * @param array $source Current source data.
	 *
	 * @return array
	 */
	public function register_cdn_source( $source ) {
		if ( ! $this->is_active() ) {
			return $source;
		}

		return [
			'name' => 'Infinite Uploads',
			'url'  => trailingslashit( $this->infinite_uploads->get_s3_url() ),
		];
	}

	/**
	 * Mark Imagify picture-tag sources as existing when the sidecars live in cloud storage.
	 *
	 * @param array  $data  Image data assembled by Imagify.
	 * @param string $image Original image HTML.
	 *
	 * @return array
	 */
	public function mark_picture_sources( $data, $image ) {
		if ( ! $this->is_active() || ! is_array( $data ) ) {
			return $data;
		}

		$attachment_id = $this->find_attachment_id( $data );

		if ( ! empty( $data['src'] ) && is_array( $data['src'] ) ) {
			$data['src'] = $this->mark_nextgen_formats( $data['src'], $attachment_id, 'full' );
		}

		if ( ! empty( $data['srcset'] ) && is_array( $data['srcset'] ) ) {
			$size_map = $this->build_size_map_by_filename( $attachment_id );
			foreach ( $data['srcset'] as $index => $srcset_source ) {
				if ( ! is_array( $srcset_source ) ) {
					continue;
				}
				$size_name = null;
				if ( $size_map && ! empty( $srcset_source['src'] ) ) {
					$size_name = $size_map[ wp_basename( $srcset_source['src'] ) ] ?? null;
				}
				$data['srcset'][ $index ] = $this->mark_nextgen_formats( $srcset_source, $attachment_id, $size_name );
			}
		}

		return $data;
	}

	/**
	 * Mark WebP/AVIF sidecars as present on cloud storage. Uses Imagify's own
	 * `_imagify_data` postmeta as the primary evidence (single DB lookup per
	 * request) and falls back to `file_exists('iu://...')` only when metadata
	 * is missing — which is considerably cheaper than a HEAD request per image.
	 *
	 * @param  array      $source        Source data.
	 * @param  int|false  $attachment_id Attachment ID (false if not resolvable).
	 * @param  string|null $size_name    Imagify size key (e.g. "full", "medium").
	 *
	 * @return array
	 */
	private function mark_nextgen_formats( array $source, $attachment_id = false, $size_name = null ) {
		$imagify_data = $attachment_id ? $this->get_imagify_meta( $attachment_id ) : null;

		foreach ( [ 'webp', 'avif' ] as $format ) {
			$url_key    = $format . '_url';
			$exists_key = $format . '_exists';
			$path_key   = $format . '_path';

			if ( empty( $source[ $url_key ] ) || ! empty( $source[ $exists_key ] ) ) {
				continue;
			}

			$cloud_path = self::cloud_path_from_url( $source[ $url_key ] );
			if ( ! $cloud_path || 0 !== strpos( $cloud_path, 'iu://' ) ) {
				continue;
			}

			$source[ $path_key ] = $cloud_path;

			// Prefer Imagify's own record of successful conversions — avoids a HEAD-per-image.
			$success = null;
			if ( $imagify_data && $size_name ) {
				$suffix        = 'webp' === $format ? '@imagify-webp' : '@imagify-avif';
				$imagify_key   = $size_name . $suffix;
				if ( isset( $imagify_data['sizes'][ $imagify_key ]['success'] ) ) {
					$success = (bool) $imagify_data['sizes'][ $imagify_key ]['success'];
				}
			}

			if ( $success === null ) {
				// Fallback when metadata is absent (older optimizations, 3rd-party converters).
				$success = file_exists( $cloud_path );
			}

			$source[ $exists_key ] = $success;
		}

		return $source;
	}

	/**
	 * Get Imagify's stored optimization metadata for an attachment, request-memoized.
	 *
	 * @param  int  $attachment_id
	 *
	 * @return array|false
	 */
	private function get_imagify_meta( $attachment_id ) {
		static $cache = [];

		if ( array_key_exists( $attachment_id, $cache ) ) {
			return $cache[ $attachment_id ];
		}

		$meta             = get_post_meta( $attachment_id, '_imagify_data', true );
		$cache[ $attachment_id ] = is_array( $meta ) ? $meta : false;

		return $cache[ $attachment_id ];
	}

	/**
	 * Build a filename → Imagify size-name map for fast srcset lookup.
	 *
	 * @param  int|false  $attachment_id
	 *
	 * @return array
	 */
	private function build_size_map_by_filename( $attachment_id ) {
		if ( ! $attachment_id ) {
			return [];
		}

		static $cache = [];
		if ( isset( $cache[ $attachment_id ] ) ) {
			return $cache[ $attachment_id ];
		}

		$meta = wp_get_attachment_metadata( $attachment_id, true );
		$map  = [];

		if ( ! empty( $meta['file'] ) ) {
			$map[ wp_basename( $meta['file'] ) ] = 'full';
		}

		if ( ! empty( $meta['sizes'] ) && is_array( $meta['sizes'] ) ) {
			foreach ( $meta['sizes'] as $size_name => $size_info ) {
				if ( ! empty( $size_info['file'] ) ) {
					$map[ $size_info['file'] ] = $size_name;
				}
			}
		}

		$cache[ $attachment_id ] = $map;

		return $map;
	}

	/**
	 * Resolve the attachment ID for an image in the HTML output — needed to look up
	 * Imagify metadata. Uses the full-size src URL's filename against the postmeta index.
	 *
	 * @param  array  $data
	 *
	 * @return int|false
	 */
	private function find_attachment_id( $data ) {
		if ( empty( $data['src']['src'] ) ) {
			return false;
		}

		static $cache = [];
		$src = strtok( $data['src']['src'], '?#' );
		if ( isset( $cache[ $src ] ) ) {
			return $cache[ $src ];
		}

		global $wpdb;
		$filename = wp_basename( $src );
		$id       = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s LIMIT 1",
				'%/' . $wpdb->esc_like( $filename )
			)
		);

		$cache[ $src ] = $id > 0 ? $id : false;

		return $cache[ $src ];
	}

	/**
	 * Build a list of attachment image paths in cloud storage.
	 *
	 * @param int  $attachment_id Attachment ID.
	 * @param bool $include_nextgen Include WebP/AVIF sidecars.
	 *
	 * @return array
	 */
	public static function get_attachment_cloud_paths( $attachment_id, $include_nextgen = false ) {
		$paths     = [];
		$full_path = self::get_attachment_cloud_path( $attachment_id );
		$meta      = wp_get_attachment_metadata( $attachment_id, true );

		if ( $full_path ) {
			$paths[] = $full_path;
		}

		if ( function_exists( 'wp_get_original_image_path' ) ) {
			$original_path = wp_get_original_image_path( $attachment_id );
			$original_path = self::cloud_path_from_path( $original_path );

			if ( $original_path ) {
				$paths[] = $original_path;
			}
		}

		if ( $full_path && ! empty( $meta['sizes'] ) && is_array( $meta['sizes'] ) ) {
			$full_dir = trailingslashit( dirname( $full_path ) );

			foreach ( $meta['sizes'] as $size ) {
				if ( empty( $size['file'] ) ) {
					continue;
				}

				$paths[] = $full_dir . ltrim( $size['file'], '/' );
			}
		}

		$paths = array_values( array_unique( array_filter( $paths ) ) );

		if ( ! $include_nextgen ) {
			return $paths;
		}

		$nextgen_paths = [];
		foreach ( $paths as $path ) {
			foreach ( [ 'webp', 'avif' ] as $format ) {
				$nextgen_paths[] = $path . '.' . $format;
			}
		}

		return array_values( array_unique( array_merge( $paths, $nextgen_paths ) ) );
	}

	/**
	 * Build next-gen attachment URLs on the IU CDN.
	 *
	 * @param int $attachment_id Attachment ID.
	 *
	 * @return array
	 */
	public static function get_attachment_nextgen_urls( $attachment_id ) {
		$urls       = [];
		$cloud_path = InfiniteUploadsHelper::get_cloud_upload_path();
		$cloud_url  = untrailingslashit( InfiniteUploadsHelper::get_cloud_upload_url() );

		foreach ( self::get_attachment_cloud_paths( $attachment_id, false ) as $path ) {
			foreach ( [ 'webp', 'avif' ] as $format ) {
				$urls[] = str_replace( $cloud_path, $cloud_url, $path . '.' . $format );
			}
		}

		return array_values( array_unique( $urls ) );
	}

	/**
	 * Get the cloud path for an attachment full-size file.
	 *
	 * @param int $attachment_id Attachment ID.
	 *
	 * @return string|false
	 */
	public static function get_attachment_cloud_path( $attachment_id ) {
		$path = get_attached_file( $attachment_id, true );

		return self::cloud_path_from_path( $path );
	}

	/**
	 * Convert a URL to the corresponding Infinite Uploads stream path.
	 *
	 * @param string $url File URL.
	 *
	 * @return string|false
	 */
	public static function cloud_path_from_url( $url ) {
		if ( empty( $url ) || ! is_string( $url ) ) {
			return false;
		}

		$url  = strtok( $url, '?#' );
		$path = wp_parse_url( $url, PHP_URL_PATH );

		if ( empty( $path ) ) {
			return false;
		}

		$local_base_path = wp_parse_url( InfiniteUploadsHelper::get_local_upload_url(), PHP_URL_PATH );
		$cloud_base_path = wp_parse_url( InfiniteUploadsHelper::get_cloud_upload_url(), PHP_URL_PATH );

		if ( $cloud_base_path && 0 === strpos( $path, $cloud_base_path ) ) {
			return InfiniteUploadsHelper::get_cloud_upload_path() . substr( $path, strlen( $cloud_base_path ) );
		}

		if ( $local_base_path && 0 === strpos( $path, $local_base_path ) ) {
			return InfiniteUploadsHelper::get_cloud_upload_path() . substr( $path, strlen( $local_base_path ) );
		}

		return false;
	}

	/**
	 * Convert a local attachment path to its Infinite Uploads stream path.
	 *
	 * @param string $path File path.
	 *
	 * @return string|false
	 */
	public static function cloud_path_from_path( $path ) {
		if ( empty( $path ) || ! is_string( $path ) ) {
			return false;
		}

		if ( 0 === strpos( $path, 'iu://' ) ) {
			return $path;
		}

		if ( 0 !== strpos( $path, '/' ) ) {
			$path = trailingslashit( InfiniteUploadsHelper::get_local_upload_path() ) . ltrim( $path, '/' );
		}

		return InfiniteUploadsHelper::get_cloud_file_path( $path );
	}

	/**
	 * Tell if IU + Imagify integration is available.
	 *
	 * @return bool
	 */
	private function is_active() {
		return function_exists( 'infinite_uploads_enabled' )
			&& infinite_uploads_enabled()
			&& interface_exists( '\Imagify\CDN\PushCDNInterface' )
			&& function_exists( 'get_imagify_option' );
	}
}
