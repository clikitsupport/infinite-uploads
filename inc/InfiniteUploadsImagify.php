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

		if ( ! empty( $data['src'] ) && is_array( $data['src'] ) ) {
			$data['src'] = $this->mark_nextgen_formats( $data['src'] );
		}

		if ( ! empty( $data['srcset'] ) && is_array( $data['srcset'] ) ) {
			foreach ( $data['srcset'] as $index => $srcset_source ) {
				if ( is_array( $srcset_source ) ) {
					$data['srcset'][ $index ] = $this->mark_nextgen_formats( $srcset_source );
				}
			}
		}

		return $data;
	}

	/**
	 * Mark WebP/AVIF sidecars as present on cloud storage.
	 *
	 * @param array $source Source data.
	 *
	 * @return array
	 */
	private function mark_nextgen_formats( array $source ) {
		foreach ( [ 'webp', 'avif' ] as $format ) {
			$url_key    = $format . '_url';
			$exists_key = $format . '_exists';
			$path_key   = $format . '_path';

			if ( empty( $source[ $url_key ] ) ) {
				continue;
			}

			$cloud_path = self::cloud_path_from_url( $source[ $url_key ] );
			if ( ! $cloud_path || 0 !== strpos( $cloud_path, 'iu://' ) ) {
				continue;
			}

			$source[ $path_key ]   = $cloud_path;
			$source[ $exists_key ] = file_exists( $cloud_path );
		}

		return $source;
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
