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
	 *
	 * True when the attachment's authoritative copy lives on iu://. Based on
	 * `get_attached_file()` (which IU filters to `iu://` for offloaded media),
	 * not on `get_file_path()` — the latter intentionally returns a local path
	 * so Imagify's optimization pipeline can work with real filesystem paths.
	 */
	public function media_is_on_cdn() {
		$cloud_path = InfiniteUploadsImagify::get_attachment_cloud_path( $this->attachment_id );

		return $cloud_path && 0 === strpos( $cloud_path, 'iu://' );
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
	 *
	 * Imagify writes the optimized files and their WebP/AVIF sidecars to local disk.
	 * Push each of those to iu://, then delete the local copy — IU is the authoritative
	 * store and the local copy is only a temp produced during optimization. File
	 * exclusion is respected: excluded paths stay local and are not unlinked.
	 */
	public function send_to_cdn( $is_new_upload ) {
		$errors            = [];
		$exclusion_enabled = InfiniteUploadsHelper::is_file_exclusion_enabled();

		foreach ( InfiniteUploadsImagify::get_attachment_cloud_paths( $this->attachment_id, true ) as $cloud_path ) {
			$local_path = InfiniteUploadsHelper::get_local_file_path( $cloud_path );

			if ( ! $local_path || ! file_exists( $local_path ) ) {
				continue;
			}

			if ( $local_path === $cloud_path ) {
				continue;
			}

			// Excluded paths are supposed to stay local — don't push or delete.
			if ( $exclusion_enabled && InfiniteUploadsHelper::is_path_excluded( $local_path ) ) {
				continue;
			}

			wp_mkdir_p( dirname( $cloud_path ) );

			if ( ! copy( $local_path, $cloud_path ) ) {
				$errors[] = $local_path;
				continue;
			}

			@unlink( $local_path );
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
	 *
	 * Returns a LOCAL filesystem path, not the `iu://` stream path. Imagify's
	 * optimization pipeline sends files to the Imagify API via curl_file_create(),
	 * which does not support stream wrappers — the file must live on real disk
	 * during optimization. Upload/download between local and iu:// is orchestrated
	 * by the `imagify_before_optimize_size` / `imagify_after_optimize` hooks.
	 */
	public function get_file_path( $file_name = '' ) {
		if ( 'original' === $file_name && function_exists( 'wp_get_original_image_path' ) ) {
			$cloud_path = InfiniteUploadsImagify::cloud_path_from_path( wp_get_original_image_path( $this->attachment_id ) );

			return $cloud_path ? InfiniteUploadsHelper::get_local_file_path( $cloud_path ) : '';
		}

		$full_path = InfiniteUploadsImagify::get_attachment_cloud_path( $this->attachment_id );
		if ( ! $full_path ) {
			return '';
		}

		$local_full = InfiniteUploadsHelper::get_local_file_path( $full_path );

		if ( ! $file_name ) {
			return $local_full;
		}

		return trailingslashit( dirname( $local_full ) ) . ltrim( $file_name, '/' );
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

		// Relative filename — resolve against the attachment's cloud directory.
		$cloud_full = InfiniteUploadsImagify::get_attachment_cloud_path( $this->attachment_id );
		if ( ! $cloud_full ) {
			return false;
		}

		return trailingslashit( dirname( $cloud_full ) ) . ltrim( $file_path, '/' );
	}
}
