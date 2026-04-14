<?php

namespace ClikIT\InfiniteUploads;

use Imagify\CDN\PushCDNInterface;
use WP_Error;

/**
 * Imagify PushCDN adapter for Infinite Uploads.
 */
class InfiniteUploadsImagifyCDN implements PushCDNInterface {
	/**
	 * Attachment ID.
	 *
	 * @var int
	 */
	private $attachment_id;

	/**
	 * Constructor.
	 *
	 * @param int $attachment_id Attachment ID.
	 */
	public function __construct( $attachment_id ) {
		$this->attachment_id = (int) $attachment_id;
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_ready() {
		return function_exists( 'infinite_uploads_enabled' ) && infinite_uploads_enabled();
	}

	/**
	 * {@inheritDoc}
	 */
	public function media_is_on_cdn() {
		$path = $this->get_file_path();

		return $path && 0 === strpos( $path, 'iu://' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_files_from_cdn( $file_paths ) {
		$errors = [];

		foreach ( (array) $file_paths as $file_path ) {
			$cloud_path = $this->resolve_cloud_path( $file_path );

			if ( ! $cloud_path || ! file_exists( $cloud_path ) ) {
				$errors[] = $file_path;
				continue;
			}

			wp_mkdir_p( dirname( $file_path ) );

			if ( ! copy( $cloud_path, $file_path ) ) {
				$errors[] = $file_path;
			}
		}

		if ( $errors ) {
			return new WP_Error( 'iu_imagify_cdn_copy_failed', __( 'File(s) could not be copied from Infinite Uploads cloud storage.', 'infinite-uploads' ), $errors );
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function remove_files_from_cdn( $file_paths ) {
		$errors = [];

		foreach ( (array) $file_paths as $file_path ) {
			$cloud_path = $this->resolve_cloud_path( $file_path );

			if ( ! $cloud_path ) {
				continue;
			}

			if ( file_exists( $cloud_path ) && ! unlink( $cloud_path ) ) {
				$errors[] = $file_path;
			}
		}

		if ( $errors ) {
			return new WP_Error( 'iu_imagify_cdn_delete_failed', __( 'File(s) could not be removed from Infinite Uploads cloud storage.', 'infinite-uploads' ), $errors );
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function send_to_cdn( $is_new_upload ) {
		$errors = [];

		foreach ( InfiniteUploadsImagify::get_attachment_cloud_paths( $this->attachment_id, true ) as $cloud_path ) {
			$local_path = InfiniteUploadsHelper::get_local_file_path( $cloud_path );

			if ( ! $local_path || ! file_exists( $local_path ) ) {
				continue;
			}

			if ( $local_path === $cloud_path ) {
				continue;
			}

			wp_mkdir_p( dirname( $cloud_path ) );

			if ( ! copy( $local_path, $cloud_path ) ) {
				$errors[] = $local_path;
			}
		}

		if ( $errors ) {
			return new WP_Error( 'iu_imagify_cdn_upload_failed', __( 'File(s) could not be uploaded to Infinite Uploads cloud storage.', 'infinite-uploads' ), $errors );
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_file_url( $file_name = '' ) {
		$file_url = wp_get_attachment_url( $this->attachment_id );

		if ( ! $file_url ) {
			return '';
		}

		if ( ! $file_name ) {
			return $file_url;
		}

		return trailingslashit( dirname( $file_url ) ) . ltrim( $file_name, '/' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_file_path( $file_name = '' ) {
		if ( 'original' === $file_name && function_exists( 'wp_get_original_image_path' ) ) {
			return InfiniteUploadsImagify::cloud_path_from_path( wp_get_original_image_path( $this->attachment_id ) );
		}

		$full_path = InfiniteUploadsImagify::get_attachment_cloud_path( $this->attachment_id );

		if ( ! $file_name || ! $full_path ) {
			return $full_path ? $full_path : '';
		}

		return trailingslashit( dirname( $full_path ) ) . ltrim( $file_name, '/' );
	}

	/**
	 * Resolve a local or cloud file path to its cloud storage path.
	 *
	 * @param string $file_path File path or file name.
	 *
	 * @return string|false
	 */
	private function resolve_cloud_path( $file_path ) {
		if ( empty( $file_path ) || ! is_string( $file_path ) ) {
			return false;
		}

		if ( 0 === strpos( $file_path, 'iu://' ) ) {
			return $file_path;
		}

		if ( 0 === strpos( $file_path, '/' ) ) {
			return InfiniteUploadsImagify::cloud_path_from_path( $file_path );
		}

		$full_path = $this->get_file_path();
		if ( ! $full_path ) {
			return false;
		}

		return trailingslashit( dirname( $full_path ) ) . ltrim( $file_path, '/' );
	}
}
