<?php

namespace ClikIT\InfiniteUploads;

class InfiniteUploadsHelper {
	/**
	 * Check if a given path is in the list of excluded paths.
	 *
	 * @param  string  $path  The file path to check.
	 *
	 * @return bool True if the path is excluded, false otherwise.
	 */
	public static function is_path_excluded( $path, $url = false ) {
		error_log( '[INFINITE_UPLOADS >> is_path_excluded] Checking if path is excluded: ' . $path );

		if ( $url ) {
			$local_url  = self::get_local_file_url( $path );
			$local_path = self::get_local_path_from_url( $local_url );
		} else {
			$local_path = self::get_local_file_path( $path );
		}

		error_log( '[INFINITE_UPLOADS >> is_path_excluded] Checking if local path is excluded: ' . $local_path );

		$excluded_files_array = self::get_excluded_paths();

		//error_log( '[INFINITE_UPLOADS] Excluded paths: ' . print_r( $excluded_files_array, true ) );

		if ( empty( $excluded_files_array ) ) {
			return false;
		}

		foreach ( $excluded_files_array as $excluded_file ) {
			if ( stripos( $local_path, $excluded_file ) !== false ) {
				error_log( '[INFINITE_UPLOADS >> is_path_excluded] Local Path Is Excluded: ' . $local_path );

				return true;
			}
		}

		return false;
	}

	public static function get_local_path_from_url( $url ) {
		$local_upload_url = self::get_local_upload_url();
		$local_path       = str_replace( $local_upload_url, self::get_local_upload_path(), $url );

		return $local_path;
	}

	/**
	 * Get the list of excluded files from the WordPress option.
	 *
	 * @return array|false An array of excluded file paths.
	 */
	public static function get_excluded_paths() {
		$excluded_files_array = get_site_option( 'iup_excluded_files', '' );
		if ( ! is_array( $excluded_files_array ) ) {
			$excluded_files_array = [];
		}

		return array_map( 'trim', $excluded_files_array );
	}

	public static function get_original_upload_dir_root() {
		$siteurl     = get_option( 'siteurl' );
		$upload_path = trim( get_option( 'upload_path' ) );

		if ( empty( $upload_path ) || 'wp-content/uploads' === $upload_path ) {
			$dir = WP_CONTENT_DIR . '/uploads';
		} elseif ( 0 !== strpos( $upload_path, ABSPATH ) ) {
			// $dir is absolute, $upload_path is (maybe) relative to ABSPATH.
			$dir = path_join( ABSPATH, $upload_path );
		} else {
			$dir = $upload_path;
		}

		$url = get_option( 'upload_url_path' );
		if ( ! $url ) {
			if ( empty( $upload_path ) || ( 'wp-content/uploads' === $upload_path ) || ( $upload_path == $dir ) ) {
				$url = WP_CONTENT_URL . '/uploads';
			} else {
				$url = trailingslashit( $siteurl ) . $upload_path;
			}
		}

		/*
		 * Honor the value of UPLOADS. This happens as long as ms-files rewriting is disabled.
		 * We also sometimes obey UPLOADS when rewriting is enabled -- see the next block.
		 */
		if ( defined( 'UPLOADS' ) && ! ( is_multisite() && get_site_option( 'ms_files_rewriting' ) ) ) {
			$dir = ABSPATH . UPLOADS;
			$url = trailingslashit( $siteurl ) . UPLOADS;
		}

		// If multisite (and if not the main site in a post-MU network).
		if ( is_multisite() && ! ( is_main_network() && is_main_site() && defined( 'MULTISITE' ) ) ) {
			if ( get_site_option( 'ms_files_rewriting' ) && defined( 'UPLOADS' ) && ! ms_is_switched() ) {
				/*
				 * Handle the old-form ms-files.php rewriting if the network still has that enabled.
				 * When ms-files rewriting is enabled, then we only listen to UPLOADS when:
				 * 1) We are not on the main site in a post-MU network, as wp-content/uploads is used
				 *    there, and
				 * 2) We are not switched, as ms_upload_constants() hardcodes these constants to reflect
				 *    the original blog ID.
				 *
				 * Rather than UPLOADS, we actually use BLOGUPLOADDIR if it is set, as it is absolute.
				 * (And it will be set, see ms_upload_constants().) Otherwise, UPLOADS can be used, as
				 * as it is relative to ABSPATH. For the final piece: when UPLOADS is used with ms-files
				 * rewriting in multisite, the resulting URL is /files. (#WP22702 for background.)
				 */

				$dir = ABSPATH . untrailingslashit( UPLOADBLOGSDIR );
				$url = trailingslashit( $siteurl ) . 'files';
			}
		}

		$basedir = $dir;
		$baseurl = $url;

		return array(
			'basedir' => $basedir,
			'baseurl' => $baseurl,
		);
	}

	public static function get_valid_file_path( $file_path ) {
		if ( self::is_path_excluded( $file_path ) ) {
			return self::get_local_file_path( $file_path );
		}

		return self::get_cloud_file_path( $file_path );
	}

	public static function get_valid_file_url( $url ) {
		$local_upload_url = self::get_local_upload_url();
		$local_url        = str_replace( self::get_cloud_upload_url(), $local_upload_url, $url );

		if ( self::is_path_excluded( $local_url ) ) {
			return $local_url;
		}

		return str_replace( self::get_local_upload_url(), self::get_cloud_upload_url(), $url );
	}

	public static function get_local_upload_url() {
		$local_upload_dir = self::get_original_upload_dir_root();

		return untrailingslashit( $local_upload_dir['baseurl'] );
	}

	public static function get_cloud_upload_url() {
		$cloud_upload_dir = self::get_cloud_upload_dir();

		return untrailingslashit( $cloud_upload_dir['baseurl'] );
	}

	public static function get_local_file_path( $file_path ) {
		$local_upload_path = self::get_local_upload_path();
		$cloud_upload_path = self::get_cloud_upload_path();

		$local_path = str_replace( $cloud_upload_path, $local_upload_path, $file_path );

		return $local_path;
	}

	public static function get_local_file_url( $url ) {
		$local_upload_url = self::get_local_upload_url();
		$cloud_upload_url = self::get_cloud_upload_url();

		$local_url = str_replace( $cloud_upload_url, $local_upload_url, $url );

		return $local_url;
	}

	public static function get_cloud_file_path( $file_path ) {
		$cloud_upload_dir = self::get_cloud_upload_dir();

		$cloud_upload_path = $cloud_upload_dir['basedir'];

		$cloud_path = str_replace( self::get_local_upload_path(), $cloud_upload_path, $file_path );

		return $cloud_path;
	}

	public static function get_file_name_from_url( $url ) {
		// Check if url is local or cloud
		if ( self::is_path_excluded( $url, true ) ) {
			$local_path = self::get_local_file_path( $url );

			return basename( $local_path );
		} else {
			$cloud_path = self::get_cloud_file_path( $url );

			return basename( $cloud_path );
		}
	}

	public static function get_file_name_from_path( $path ) {
		if ( self::is_path_excluded( $path ) ) {
			$local_path = self::get_local_file_path( $path );

			return basename( $local_path );
		} else {
			$cloud_path = self::get_cloud_file_path( $path );

			return basename( $cloud_path );
		}
	}

	public static function get_iu_api_data() {
		$api      = InfiniteUploadsApiHandler::get_instance();
		$api_data = $api->get_site_data();

		return $api_data;
	}

	public static function get_cloud_upload_dir() {
		$root_dirs = self::get_original_upload_dir_root();

		// error_log( 'Root Dirs: ' . print_r( $root_dirs, true ) );

		$api_data = self::get_iu_api_data();
		$dirs     = $root_dirs;
		if ( $api_data && isset( $api_data->site ) && ! empty( $api_data->site->upload_key ) && ! empty( $api_data->site->upload_secret ) ) {
			$bucket = $api_data->site->upload_bucket;

			$dirs['basedir'] = str_replace( $root_dirs['basedir'], 'iu://' . untrailingslashit( $bucket ), $root_dirs['basedir'] );

			if ( ! defined( 'INFINITE_UPLOADS_DISABLE_REPLACE_UPLOAD_URL' ) || ! INFINITE_UPLOADS_DISABLE_REPLACE_UPLOAD_URL ) {
				if ( defined( 'INFINITE_UPLOADS_USE_LOCAL' ) && INFINITE_UPLOADS_USE_LOCAL ) {
					$dirs['baseurl'] = str_replace( 'iu://' . untrailingslashit( $bucket ), $root_dirs['baseurl'] . '/iu/' . $bucket, $dirs['basedir'] );
				} else {
					$dirs['baseurl'] = str_replace( 'iu://' . untrailingslashit( $bucket ), self::get_s3_url(), $dirs['basedir'] );
				}
			}
		}

		return $dirs;
	}

	public static function get_local_upload_path() {
		$root_dirs = self::get_original_upload_dir_root();

		return untrailingslashit( $root_dirs['basedir'] );
	}

	public static function get_cloud_upload_path() {
		$root_dirs = self::get_cloud_upload_dir();

		return untrailingslashit( $root_dirs['basedir'] );
	}

	public static function get_s3_url() {
		$api_data = self::get_iu_api_data();

		$bucket_url = '';
		if ( $api_data && isset( $api_data->site ) && ! empty( $api_data->site->upload_key ) && ! empty( $api_data->site->upload_secret ) ) {
			$bucket_url = $api_data->site->cdn_url;
			$bucket     = $api_data->site->upload_bucket;
		}

		if ( $bucket_url ) {
			return 'https://' . $bucket_url;
		}

		$bucket = strtok( $bucket, '/' );
		$path   = substr( $bucket, strlen( $bucket ) );

		return apply_filters( 'infinite_uploads_bucket_url', 'https://' . $bucket . '.s3.amazonaws.com' . $path );
	}

	public static function is_file_exclusion_enabled() {
		$file_exclusion_setting = self::get_file_exclusion_setting();

		if ( $file_exclusion_setting === 'yes' ) {
			return true;
		}

		return false;
	}

	public static function get_file_exclusion_setting() {
		return get_site_option( 'iu_file_exclusion_enabled', 'no' );
	}

	public static function set_file_exclusion_setting( $value ) {
		if ( $value !== 'yes' && $value !== 'no' ) {
			return false;
		}

		return update_site_option( 'iu_file_exclusion_enabled', $value );
	}
}