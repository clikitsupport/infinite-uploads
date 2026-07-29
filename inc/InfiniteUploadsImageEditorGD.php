<?php

namespace ClikIT\InfiniteUploads;

/**
 * GD image editor wrapper for Infinite Uploads stream paths.
 *
 * WordPress core's WP_Image_Editor_GD::_save() ultimately calls
 * WP_Image_Editor::make_image(), which detects stream wrappers via wp_is_stream()
 * and switches to an ob_start()-based capture path (wp-includes/class-wp-image-editor.php:571).
 * That path conflicts with output handlers active in some hosting environments and
 * raises a fatal:
 *   "Cannot use output buffering in output buffering display handlers".
 *
 * To avoid the buggy branch we override _save() to write to a real local temp file
 * (so wp_is_stream() returns false inside core's make_image()), then copy the
 * resulting bytes over to the iu:// destination using PHP's stream-wrapper-aware
 * copy(). This mirrors the InfiniteUploadsImageEditorImagick workaround used for
 * the Imagick backend.
 *
 * load() is similarly overridden because GD's load uses imagecreatefromwebp() /
 * imagecreatefromavif() which require a real local path; for non-iu paths we just
 * call parent::load().
 */
class InfiniteUploadsImageEditorGD extends \WP_Image_Editor_GD {

	/** @var string|null Original (iu://) filename, restored after load(). */
	protected $remote_filename = null;

	/** @var array Temp local files created during this editor's lifetime. */
	protected $temp_files_to_cleanup = [];

	/**
	 * Loads image from $this->file. If the file lives under the IU upload dir
	 * (i.e. an iu:// stream path), copy it to a local temp file first so GD's
	 * imagecreatefromwebp/imagecreatefromavif have a real filesystem path.
	 *
	 * @return true|\WP_Error True if loaded; WP_Error on failure.
	 */
	public function load() {
		if ( $this->image ) {
			return true;
		}

		if ( ! is_file( $this->file ) && ! preg_match( '|^https?://|', $this->file ) ) {
			return new \WP_Error( 'error_loading_image', __( 'File does not exist?' ), $this->file );
		}

		$upload_dir = wp_upload_dir();

		// Only intercept files that live in the (iu://) uploads dir.
		if ( strpos( $this->file, $upload_dir['basedir'] ) !== 0 ) {
			return parent::load();
		}

		$temp_filename                 = tempnam( get_temp_dir(), 'infinite-uploads' );
		$this->temp_files_to_cleanup[] = $temp_filename;

		copy( $this->file, $temp_filename );
		$this->remote_filename = $this->file;
		$this->file            = $temp_filename;

		$result = parent::load();

		// Restore the public-facing path so subsequent ops still reference iu://.
		$this->file = $this->remote_filename;

		return $result;
	}

	/**
	 * Save the rendered image. Writes to a local temp file (so core's stream
	 * branch isn't triggered), then copies to the iu:// destination.
	 */
	protected function _save( $image, $filename = null, $mime_type = null ) {
		list( $filename, $extension, $mime_type ) = $this->get_output_format( $filename, $mime_type );

		if ( ! $filename ) {
			$filename = $this->generate_filename( null, null, $extension );
		}

		$temp_filename = tempnam( get_temp_dir(), 'infinite-uploads' );

		$save = parent::_save( $image, $temp_filename, $mime_type );

		if ( is_wp_error( $save ) ) {
			@unlink( $temp_filename );

			return $save;
		}

		$copy_result = copy( $save['path'], $filename );

		// parent::_save() may have re-derived $save['path'] via get_output_format()
		// to a path that differs from $temp_filename (e.g. extension change). Clean
		// up both, but only if they actually exist.
		if ( file_exists( $save['path'] ) ) {
			@unlink( $save['path'] );
		}
		if ( file_exists( $temp_filename ) ) {
			@unlink( $temp_filename );
		}

		if ( ! $copy_result ) {
			return new \WP_Error( 'unable-to-copy-to-cloud', __( 'Unable to copy the temp image to the cloud', 'infinite-uploads' ) );
		}

		return [
			'path'      => $filename,
			'file'      => wp_basename( apply_filters( 'image_make_intermediate_size', $filename ) ),
			'width'     => $this->size['width'],
			'height'    => $this->size['height'],
			'mime-type' => $mime_type,
		];
	}

	public function __destruct() {
		array_map( 'unlink', $this->temp_files_to_cleanup );
		parent::__destruct();
	}
}
