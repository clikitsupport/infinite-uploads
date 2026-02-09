<?php

namespace ClikIT\InfiniteUploads;

/**
 * Local streamwrapper that writes files to the upload dir
 *
 * This is for the most part taken from Drupal, with some modifications.
 */
class InfiniteUploadsLocalStreamWrapper {
	/**
	 * Stream context resource.
	 *
	 * @var resource
	 */
	public $context;

	/**
	 * A generic resource handle.
	 *
	 * @var resource
	 */
	public $handle = null;

	/**
	 * Instance URI (stream).
	 *
	 * A stream is referenced as "scheme://target".
	 *
	 * @var string
	 */
	protected $uri;

	/**
	 * Gets the path that the wrapper is responsible for.
	 *
	 * @return string
	 *   String specifying the path.
	 */
	static function getDirectoryPath() {
		$upload_dir = InfiniteUploads::get_instance()->get_original_upload_dir();

		return $upload_dir['basedir'] . '/iu';
	}

	function setUri( $uri ) {
		$this->uri = $uri;
	}

	function getUri() {
		return $this->uri;
	}

	/**
	 * Returns the local writable target of the resource within the stream.
	 *
	 * This function should be used in place of calls to realpath() or similar
	 * functions when attempting to determine the location of a file. While
	 * functions like realpath() may return the location of a read-only file, this
	 * method may return a URI or path suitable for writing that is completely
	 * separate from the URI used for reading.
	 *
	 * @param  string  $uri
	 *   Optional URI.
	 *
	 * @return string|bool
	 *   Returns a string representing a location suitable for writing of a file,
	 *   or FALSE if unable to write to the file such as with read-only streams.
	 */
	protected function getTarget( $uri = null ) {
		if ( ! isset( $uri ) ) {
			$uri = $this->uri;
		}

		list( $scheme, $target ) = explode( '://', $uri, 2 );

		// Remove erroneous leading or trailing, forward-slashes and backslashes.
		return trim( $target, '\/' );
	}

	static function getMimeType( $uri, $mapping = null ) {
		$extension  = '';
		$file_parts = explode( '.', basename( $uri ) );

		// Remove the first part: a full filename should not match an extension.
		array_shift( $file_parts );

		// Iterate over the file parts, trying to find a match.
		// For my.awesome.image.jpeg, we try:
		//   - jpeg
		//   - image.jpeg, and
		//   - awesome.image.jpeg
		while ( $additional_part = array_pop( $file_parts ) ) {
			$extension = strtolower( $additional_part . ( $extension ? '.' . $extension : '' ) );
			if ( isset( $mapping['extensions'][ $extension ] ) ) {
				return $mapping['mimetypes'][ $mapping['extensions'][ $extension ] ];
			}
		}

		return 'application/octet-stream';
	}

	function chmod( $mode ) {
		$output = @chmod( $this->getLocalPath(), $mode );
		// We are modifying the underlying file here, so we have to clear the stat
		// cache so that PHP understands that URI has changed too.
		clearstatcache( true, $this->getLocalPath() );

		return $output;
	}

	function realpath() {
		return $this->getLocalPath();
	}

	/**
	 * Returns the canonical absolute path of the URI, if possible.
	 *
	 * @param  string  $uri
	 *   (optional) The stream wrapper URI to be converted to a canonical
	 *   absolute path. This may point to a directory or another type of file.
	 *
	 * @return string|bool
	 *   If $uri is not set, returns the canonical absolute path of the URI
	 *   previously. If $uri is set and valid for this class, returns its canonical absolute
	 *   path, as determined by the realpath() function. If $uri is set but not
	 *   valid, returns FALSE.
	 */
	protected function getLocalPath( $uri = null ) {
		if ( ! isset( $uri ) ) {
			$uri = $this->uri;
		}
		$path     = $this->getDirectoryPath() . '/' . $this->getTarget( $uri );
		$realpath = $path;

		$directory = realpath( $this->getDirectoryPath() );

		if ( ! $realpath || ! $directory || strpos( $realpath, $directory ) !== 0 ) {
			return false;
		}

		return $realpath;
	}

	/**
	 * Support for fopen(), file_get_contents(), file_put_contents() etc.
	 *
	 * @see http://php.net/manual/streamwrapper.stream-open.php
	 *
	 * @param  int     $mode
	 *   The file mode ("r", "wb" etc.).
	 * @param  int     $options
	 *   A bit mask of STREAM_USE_PATH and STREAM_REPORT_ERRORS.
	 * @param  string  $opened_path
	 *   A string containing the path actually opened.
	 *
	 * @param  string  $uri
	 *   A string containing the URI to the file to open.
	 *
	 * @return bool
	 *   Returns TRUE if file was opened successfully.
	 *
	 */
	public function stream_open( $uri, $mode, $options, &$opened_path ) {
		$this->uri    = $uri;
		$path         = $this->getLocalPath();
		$this->handle = ( $options & STREAM_REPORT_ERRORS ) ? fopen( $path, $mode ) : @fopen( $path, $mode );

		if ( (bool) $this->handle && $options & STREAM_USE_PATH ) {
			$opened_path = $path;
		}

		return (bool) $this->handle;
	}

	/**
	 * Support for chmod(), chown(), etc.
	 *
	 * @see http://php.net/manual/streamwrapper.stream-metadata.php
	 * @return bool
	 *   Returns TRUE on success or FALSE on failure.
	 *
	 */
	public function stream_metadata() {
		return true;
	}

	/**
	 * Support for flock().
	 *
	 * @see http://php.net/manual/streamwrapper.stream-lock.php
	 *
	 * @param  int  $operation
	 *     One of the following:
	 *     - LOCK_SH to acquire a shared lock (reader).
	 *     - LOCK_EX to acquire an exclusive lock (writer).
	 *     - LOCK_UN to release a lock (shared or exclusive).
	 *     - LOCK_NB if you don't want flock() to block while locking (not
	 *     supported on Windows).
	 *
	 * @return bool
	 *   Always returns TRUE at the present time.
	 *
	 */
	public function stream_lock( $operation ) {
		if ( in_array( $operation, [ LOCK_SH, LOCK_EX, LOCK_UN, LOCK_NB ] ) ) {
			return flock( $this->handle, $operation );
		}

		return true;
	}

	/**
	 * Support for fread(), file_get_contents() etc.
	 *
	 * @see http://php.net/manual/streamwrapper.stream-read.php
	 *
	 * @param  int  $count
	 *   Maximum number of bytes to be read.
	 *
	 * @return string|bool
	 *   The string that was read, or FALSE in case of an error.
	 *
	 */
	public function stream_read( $count ) {
		return fread( $this->handle, $count );
	}

	/**
	 * Support for fwrite(), file_put_contents() etc.
	 *
	 * @see http://php.net/manual/streamwrapper.stream-write.php
	 *
	 * @param  string  $data
	 *   The string to be written.
	 *
	 * @return int
	 *   The number of bytes written.
	 *
	 */
	public function stream_write( $data ) {
		return fwrite( $this->handle, $data );
	}

	/**
	 * Support for feof().
	 *
	 * @see http://php.net/manual/streamwrapper.stream-eof.php
	 * @return bool
	 *   TRUE if end-of-file has been reached.
	 *
	 */
	public function stream_eof() {
		return feof( $this->handle );
	}

	/**
	 * Support for fseek().
	 *
	 * @see http://php.net/manual/streamwrapper.stream-seek.php
	 *
	 * @param  int  $whence
	 *   SEEK_SET, SEEK_CUR, or SEEK_END.
	 *
	 * @param  int  $offset
	 *   The byte offset to got to.
	 *
	 * @return bool
	 *   TRUE on success.
	 *
	 */
	public function stream_seek( $offset, $whence ) {
		// fseek returns 0 on success and -1 on a failure.
		// stream_seek   1 on success and  0 on a failure.
		return ! fseek( $this->handle, $offset, $whence );
	}

	/**
	 * Support for fflush().
	 *
	 * @see http://php.net/manual/streamwrapper.stream-flush.php
	 * @return bool
	 *   TRUE if data was successfully stored (or there was no data to store).
	 *
	 */
	public function stream_flush() {
		$result = fflush( $this->handle );

		$params = [
			'Bucket' => InfiniteUploads::get_instance()->bucket,
			'Key'    => trim( str_replace( InfiniteUploads::get_instance()->bucket, '', $this->getTarget() ), '/' ),
		];

		/**
		 * Action when a new object has been uploaded to b2.
		 *
		 * @param  array  $params  S3Client::putObject parameters.
		 */
		do_action( 'infinite_uploads_putObject', $params );

		return $result;
	}

	/**
	 * Support for ftell().
	 *
	 * @see http://php.net/manual/streamwrapper.stream-tell.php
	 * @return bool
	 *   The current offset in bytes from the beginning of file.
	 *
	 */
	public function stream_tell() {
		return ftell( $this->handle );
	}

	/**
	 * Support for fstat().
	 *
	 * @see http://php.net/manual/streamwrapper.stream-stat.php
	 * @return bool
	 *   An array with file status, or FALSE in case of an error - see fstat()
	 *   for a description of this array.
	 *
	 */
	public function stream_stat() {
		return fstat( $this->handle );
	}

	/**
	 * Support for fclose().
	 *
	 * @see http://php.net/manual/streamwrapper.stream-close.php
	 * @return bool
	 *   TRUE if stream was successfully closed.
	 *
	 */
	public function stream_close() {
		return fclose( $this->handle );
	}

	/**
	 * Gets the underlying stream resource for stream_select().
	 *
	 * @see http://php.net/manual/streamwrapper.stream-cast.php
	 *
	 * @param  int  $cast_as
	 *   Can be STREAM_CAST_FOR_SELECT or STREAM_CAST_AS_STREAM.
	 *
	 * @return resource|false
	 *   The underlying stream resource or FALSE if stream_select() is not
	 *   supported.
	 *
	 */
	public function stream_cast( $cast_as ) {
		return false;
	}

	/**
	 * Support for unlink().
	 *
	 * @see http://php.net/manual/streamwrapper.unlink.php
	 *
	 * @param  string  $uri
	 *   A string containing the URI to the resource to delete.
	 *
	 * @return bool
	 *   TRUE if resource was successfully deleted.
	 *
	 */
	public function unlink( $uri ) {
		$this->uri = $uri;

		return unlink( $this->getLocalPath() );
	}

	/**
	 * Support for rename().
	 *
	 * @see http://php.net/manual/streamwrapper.rename.php
	 *
	 * @param  string  $to_uri
	 *                            The new URI for file.
	 *
	 * @param  string  $from_uri  ,
	 *                            The URI to the file to rename.
	 *
	 * @return bool
	 *   TRUE if file was successfully renamed.
	 *
	 */
	public function rename( $from_uri, $to_uri ) {
		return rename( $this->getLocalPath( $from_uri ), $this->getLocalPath( $to_uri ) );
	}

	/**
	 * Support for mkdir().
	 *
	 * @see http://php.net/manual/streamwrapper.mkdir.php
	 *
	 * @param  int     $mode
	 *   Permission flags - see mkdir().
	 * @param  int     $options
	 *   A bit mask of STREAM_REPORT_ERRORS and STREAM_MKDIR_RECURSIVE.
	 *
	 * @param  string  $uri
	 *   A string containing the URI to the directory to create.
	 *
	 * @return bool
	 *   TRUE if directory was successfully created.
	 *
	 */
	public function mkdir( $uri, $mode, $options ) {
		$this->uri = $uri;
		$recursive = (bool) ( $options & STREAM_MKDIR_RECURSIVE );
		if ( $recursive ) {
			// $this->getLocalPath() fails if $uri has multiple levels of directories
			// that do not yet exist.
			$localpath = $this->getDirectoryPath() . '/' . $this->getTarget( $uri );
		} else {
			$localpath = $this->getLocalPath( $uri );
		}
		if ( $options & STREAM_REPORT_ERRORS ) {
			return mkdir( $localpath, $mode, $recursive );
		} else {
			return @mkdir( $localpath, $mode, $recursive );
		}
	}

	/**
	 * Support for rmdir().
	 *
	 * @see http://php.net/manual/streamwrapper.rmdir.php
	 *
	 * @param  int     $options
	 *   A bit mask of STREAM_REPORT_ERRORS.
	 *
	 * @param  string  $uri
	 *   A string containing the URI to the directory to delete.
	 *
	 * @return bool
	 *   TRUE if directory was successfully removed.
	 *
	 */
	public function rmdir( $uri, $options ) {
		$this->uri = $uri;
		if ( $options & STREAM_REPORT_ERRORS ) {
			return rmdir( $this->getLocalPath() );
		} else {
			return @rmdir( $this->getLocalPath() );
		}
	}

	/**
	 * Support for stat().
	 *
	 * @see http://php.net/manual/streamwrapper.url-stat.php
	 *
	 * @param  int     $flags
	 *   A bit mask of STREAM_URL_STAT_LINK and STREAM_URL_STAT_QUIET.
	 *
	 * @param  string  $uri
	 *   A string containing the URI to get information about.
	 *
	 * @return array
	 *   An array with file status, or FALSE in case of an error - see fstat()
	 *   for a description of this array.
	 *
	 */
	public function url_stat( $uri, $flags ) {
		$this->uri = $uri;
		$path      = $this->getLocalPath();
		// Suppress warnings if requested or if the file or directory does not
		// exist. This is consistent with PHP's plain filesystem stream wrapper.
		if ( $flags & STREAM_URL_STAT_QUIET || ! file_exists( $path ) ) {
			return @stat( $path );
		} else {
			return stat( $path );
		}
	}

	/**
	 * Support for opendir().
	 *
	 * @see http://php.net/manual/streamwrapper.dir-opendir.php
	 *
	 * @param  int     $options
	 *   Unknown (parameter is not documented in PHP Manual).
	 *
	 * @param  string  $uri
	 *   A string containing the URI to the directory to open.
	 *
	 * @return bool
	 *   TRUE on success.
	 *
	 */
	public function dir_opendir( $uri, $options ) {
		$this->uri    = $uri;
		$this->handle = opendir( $this->getLocalPath() );

		return (bool) $this->handle;
	}

	/**
	 * Support for readdir().
	 *
	 * @see http://php.net/manual/streamwrapper.dir-readdir.php
	 * @return string
	 *   The next filename, or FALSE if there are no more files in the directory.
	 *
	 */
	public function dir_readdir() {
		return readdir( $this->handle );
	}

	/**
	 * Support for rewinddir().
	 *
	 * @see http://php.net/manual/streamwrapper.dir-rewinddir.php
	 * @return bool
	 *   TRUE on success.
	 *
	 */
	public function dir_rewinddir() {
		rewinddir( $this->handle );
		// We do not really have a way to signal a failure as rewinddir() does not
		// have a return value and there is no way to read a directory handler
		// without advancing to the next file.
		return true;
	}

	/**
	 * Support for closedir().
	 *
	 * @see http://php.net/manual/streamwrapper.dir-closedir.php
	 * @return bool
	 *   TRUE on success.
	 *
	 */
	public function dir_closedir() {
		closedir( $this->handle );
		// We do not really have a way to signal a failure as closedir() does not
		// have a return value.
		return true;
	}
}
