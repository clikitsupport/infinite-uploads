<?php

namespace ClikIT\InfiniteUploads;

use ClikIT\Infinite_Uploads\Aws\S3\Transfer;
use ClikIT\Infinite_Uploads\Aws\Middleware;
use ClikIT\Infinite_Uploads\Aws\ResultInterface;
use ClikIT\Infinite_Uploads\Aws\CommandPool;
use ClikIT\Infinite_Uploads\Aws\Command;
use ClikIT\Infinite_Uploads\Aws\Exception\AwsException;
use ClikIT\Infinite_Uploads\Aws\S3\Exception\S3Exception;
use ClikIT\Infinite_Uploads\Aws\S3\MultipartUploader;
use ClikIT\Infinite_Uploads\Aws\Exception\MultipartUploadException;

class InfiniteUploadsAdmin {

    private static $instance;
    public $ajax_timelimit = 20;
    private $iup_instance;
    private $api;
    private $video;
    private $auth_error;

    public function __construct() {
        $this->iup_instance = InfiniteUploads::get_instance();
        $this->api          = InfiniteUploadsApiHandler::get_instance();
        $this->video        = InfiniteUploadsVideo::get_instance();

        if ( is_multisite() ) {
            //multisite
            add_action( 'network_admin_menu', [ &$this, 'admin_menu' ] );
            add_filter( 'network_admin_plugin_action_links_infinite-uploads/infinite-uploads.php', [
                    &$this,
                    'plugins_list_links',
            ] );
            add_action( 'load-toplevel_page_infinite_uploads', [ &$this, 'intercept_auth' ] );
        } else {
            //single site
            add_action( 'admin_menu', [ &$this, 'admin_menu' ] );
            add_action( 'load-toplevel_page_infinite_uploads', [ &$this, 'intercept_auth' ] );
            add_filter( 'plugin_action_links_infinite-uploads/infinite-uploads.php', [ &$this, 'plugins_list_links' ] );
        }

        add_action( 'admin_init', [ &$this, 'privacy_policy' ] );
        add_action( 'deactivate_plugin', [ &$this, 'block_bulk_deactivate' ] );

        add_action( 'wp_ajax_save_iu_excluded_files', [ $this, 'infinite_uploads_save_excluded_files' ] );
        add_action( 'wp_ajax_get_directory_tree', [ $this, 'get_direcotry_tree' ] );
        add_action( 'wp_ajax_save_iu_media_folders_setting', [ $this, 'save_media_folders_setting' ] );
        add_action( 'wp_ajax_save_iu_image_optimization', [ $this, 'save_image_optimization_setting' ] );
        add_action( 'wp_ajax_iu_purge_cdn_cache', [ $this, 'purge_cdn_cache' ] );

        // Handle it via Action Schedular.
        add_action( 'infinite-uploads-do-sync', [ $this, 'do_sync' ] );
        add_action( 'infinite-uploads-add-files-to-download', [ $this, 'add_files_to_download' ] );
        add_action( 'infinite-uploads-fetch-s3-files-from-directory-to-download', [
                $this,
                'fetch_s3_files_from_directory_to_download',
        ] );
        add_action( 'infinite-uploads-do-download', [ $this, 'do_download' ] );

        if ( is_main_site() ) {
            add_action( 'wp_ajax_infinite-uploads-filelist', [ &$this, 'ajax_filelist' ] );
            add_action( 'wp_ajax_infinite-uploads-remote-filelist', [ &$this, 'ajax_remote_filelist' ] );
            add_action( 'wp_ajax_infinite-uploads-sync', [ &$this, 'ajax_sync' ] );
            add_action( 'wp_ajax_infinite-uploads-sync-errors', [ &$this, 'ajax_sync_errors' ] );
            add_action( 'wp_ajax_infinite-uploads-reset-errors', [ &$this, 'ajax_reset_errors' ] );
            add_action( 'wp_ajax_infinite-uploads-delete', [ &$this, 'ajax_delete' ] );
            add_action( 'wp_ajax_infinite-uploads-download', [ &$this, 'ajax_download' ] );
            add_action( 'wp_ajax_infinite-uploads-toggle', [ &$this, 'ajax_toggle' ] );
            add_action( 'wp_ajax_infinite-uploads-status', [ &$this, 'ajax_status' ] );


            if ( ! wp_next_scheduled( 'infinite_uploads_do_sync' ) ) {
                wp_schedule_event( time(), 'daily', 'infinite_uploads_do_sync' );
            }

            add_action( 'infinite_uploads_do_sync', [ $this, 'do_sync' ] );

            // This is to handle file exclusions.
            if ( InfiniteUploadsHelper::is_file_exclusion_enabled() ) {
                add_filter( 'wp_get_attachment_url', [ $this, 'filter_attachment_url' ], 10, 2 );
                add_filter( 'wp_calculate_image_srcset', [ $this, 'calculate_image_srcset' ], 10, 5 );
                add_filter( 'pre_move_uploaded_file', [ $this, 'set_the_new_file_path' ], 10, 4 );
                add_filter( 'wp_handle_upload', [ $this, 'handle_upload' ], 10, 2 );
            }
        }
    }

    /**
     * Calculate image srcset to serve from local or cloud based on file existence and sync status.
     *
     * @param $sources
     * @param $size_array
     * @param $image_src
     * @param $image_meta
     * @param $attachment_id
     *
     * @return array
     */
    public function calculate_image_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
        foreach ( $sources as $key => $source ) {
            $sources[ $key ]['url'] = $this->serve_media_url( $source['url'] );
        }

        return $sources;
    }
    /**
     * Filter attachment URL to serve from local or cloud based on file existence and sync status.
     *
     * @param $url
     * @param $post_id
     *
     * @return array|mixed|string|string[]
     */
    public function filter_attachment_url( $url, $post_id ) {
        return $this->serve_media_url( $url );
    }

    /**
     * Serve media URL based on file existence and sync status.
     *
     * Resolution order:
     *   1. If path is in the exclusion list, prefer the local URL (the user's intent).
     *   2. For whichever URL-form we end up with, verify the file actually exists at
     *      that location; if it doesn't, fall back to the other side (cloud ↔ local).
     *   3. If neither side has the file, return the URL we started with so the
     *      browser gets the natural 404 at the expected location.
     *
     * Cloud existence is verified via the stream wrapper's `file_exists('iu://...')`
     * which reuses a HeadObject result cache within a request. That's authoritative —
     * the old code relied on `wp_infinite_uploads_files.synced = 1`, but that table
     * can be out-of-sync (missing rows for files written directly via the stream
     * wrapper, rows marked `deleted = 1`, etc.), causing false 404s for files that
     * are actually sitting on the CDN.
     *
     * @param string $url Attachment URL (local or cloud).
     *
     * @return string
     */
    public function serve_media_url( $url ) {
        if ( empty( $url ) || ! is_string( $url ) ) {
            return $url;
        }

        // Exclusion: user wants this path served locally, so rewrite CDN → local first.
        if ( InfiniteUploadsHelper::is_path_excluded( $url, true ) ) {
            $url = InfiniteUploadsHelper::get_local_file_url( $url );
        }

        $root_dirs    = $this->iup_instance->get_original_upload_dir_root();
        $base_url     = $root_dirs['baseurl'];
        $base_dir     = $root_dirs['basedir'];
        $cloud_url    = untrailingslashit( $this->iup_instance->get_s3_url() );
        $is_local_url = ( strpos( $url, $base_url ) !== false );

        if ( $is_local_url ) {
            $relative_path = str_replace( $base_url, '', $url );
            $local_path    = $base_dir . $relative_path;

            if ( file_exists( $local_path ) ) {
                return $url;
            }

            // Local missing — fall back to cloud if the file is actually there.
            if ( self::cloud_file_exists( $relative_path ) ) {
                return $cloud_url . $relative_path;
            }

            return $url;
        }

        // Cloud URL path
        $relative_path = str_replace( $cloud_url, '', $url );

        if ( self::cloud_file_exists( $relative_path ) ) {
            return $url;
        }

        // Cloud missing — try local.
        $local_path = $base_dir . $relative_path;
        if ( file_exists( $local_path ) ) {
            return $base_url . $relative_path;
        }

        return $url;
    }

    /**
     * Check whether a given uploads-relative path exists on the IU cloud. Uses the
     * stream wrapper's per-request stat cache and an additional static memo here so
     * the same URL checked multiple times on one page costs at most one HeadObject.
     *
     * @param string $relative_path Uploads-relative path, leading slash required (e.g. "/2026/04/file.jpg").
     *
     * @return bool
     */
    private static function cloud_file_exists( $relative_path ) {
        static $cache = [];

        if ( $relative_path === '' ) {
            return false;
        }
        if ( array_key_exists( $relative_path, $cache ) ) {
            return $cache[ $relative_path ];
        }

        $cloud_path = InfiniteUploadsHelper::get_cloud_upload_path() . $relative_path;
        $exists     = false;

        // Guard: get_cloud_upload_path() may return local path when IU isn't connected.
        if ( 0 === strpos( $cloud_path, 'iu://' ) ) {
            $exists = @file_exists( $cloud_path );
        }

        $cache[ $relative_path ] = $exists;

        return $exists;
    }

    public function set_the_new_file_path( $uploaded, $file, $new_file, $type ) {
        // Only intercept excluded files to move them to the local path.
        // Non-excluded files should fall through to WordPress's normal handling
        // so the stream wrapper routes them to cloud as usual.
        if ( ! InfiniteUploadsHelper::is_path_excluded( $new_file ) ) {
            return $uploaded;
        }

        $new_file = InfiniteUploadsHelper::get_local_file_path( $new_file );

        // Ensure the destination directory exists.
        wp_mkdir_p( dirname( $new_file ) );

        // Try move_uploaded_file first (works for standard HTTP POST uploads).
        // Fall back to rename() for files not in PHP's upload tmp (e.g. Big File Uploads plugin chunks in bfu-temp).
        $move_new_file = @move_uploaded_file( $file['tmp_name'], $new_file );

        if ( false === $move_new_file ) {
            $move_new_file = @rename( $file['tmp_name'], $new_file );
        }

        if ( false === $move_new_file ) {
            return wp_handle_upload_error(
                    $file,
                    sprintf(
                    /* translators: %s: Destination file path. */
                            __( 'The uploaded file could not be moved to %s.' ),
                            $new_file
                    )
            );
        }

        return true;
    }

    public function handle_upload( $file_data, $action ) {
        $file = $file_data['file'];
        $url  = $file_data['url'];

        $file_data['file'] = InfiniteUploadsHelper::get_valid_file_path( $file );
        $file_data['url']  = InfiniteUploadsHelper::get_valid_file_url( $url );

        return $file_data;
    }


    /**
     *
     * @return InfiniteUploadsAdmin
     */
    public static function get_instance() {
        if ( ! self::$instance ) {
            self::$instance = new InfiniteUploadsAdmin();
        }

        return self::$instance;
    }

    /**
     * Adds a privacy policy statement.
     */
    function privacy_policy() {
        if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
            return;
        }
        $content = '<p>'
                   . sprintf(
                           esc_html__( 'When you upload files on this site, your files are transferred to and stored in the Infinite Uploads cloud. When you visit pages on this site media files may be downloaded from the Infinite Uploads cloud CDN which stores web log information including IP, User Agent, referrer, Location, and ISP info of site visitors for 7 days. The Infinite Uploads privacy policy is %1$s here %2$s.', 'infinite-uploads' ),
                           '<a href="https://infiniteuploads.com/privacy/?utm_source=iup_plugin&utm_medium=privacy_policy&utm_campaign=iup_plugin" target="_blank">', '</a>'
                   ) . '</p>';
        wp_add_privacy_policy_content( esc_html__( 'Infinite Uploads', 'infinite-uploads' ), wp_kses_post( wpautop( $content, false ) ) );
    }

    /**
     * Logs a debugging line.
     */
    function sync_debug_log( $message ) {
        if ( defined( 'INFINITE_UPLOADS_API_DEBUG' ) && INFINITE_UPLOADS_API_DEBUG ) {
            $log = '[INFINITE_UPLOADS Sync Debug] %s %s';

            $msg = sprintf(
                    $log,
                    INFINITE_UPLOADS_VERSION,
                    $message
            );
            error_log( $msg );
        }
    }

    /**
     * Emit a single-line diagnostic when a transfer aborts. Unlike
     * sync_debug_log() this always writes to PHP's standard error log
     * regardless of INFINITE_UPLOADS_API_DEBUG — the point is that when a
     * user reports "Too many server errors" there is at least ONE entry
     * to grep for. Includes the AwsException class name, AWS error code
     * (when present), and short message; enough to identify the failure
     * mode without needing the debug flag flipped on first.
     *
     * @param  string      $phase  "Transfer", "Multipart", or "Download".
     * @param  \Throwable  $e
     * @param  string|null $file   Optional file the exception applied to.
     */
    private function log_transfer_failure( $phase, $e, $file = null ) {
        $code    = method_exists( $e, 'getAwsErrorCode' ) ? (string) $e->getAwsErrorCode() : '';
        $klass   = get_class( $e );
        $message = trim( preg_replace( '/\s+/', ' ', (string) $e->getMessage() ) );
        $prefix  = null === $file
                ? sprintf( '[infinite-uploads] %s aborted (%s%s): %s', $phase, $klass, '' !== $code ? " code={$code}" : '', $message )
                : sprintf( '[infinite-uploads] %s aborted for %s (%s%s): %s', $phase, $file, $klass, '' !== $code ? " code={$code}" : '', $message );
        error_log( $prefix );
    }

    public function ajax_status() {
        // check caps
        if ( ! current_user_can( $this->iup_instance->capability ) ) {
            wp_send_json_error( esc_html__( 'Permissions Error: Please refresh the page and try again.', 'infinite-uploads' ) );
        }

        wp_send_json_success( $this->iup_instance->get_sync_stats() );
    }

    public function download_image( $image_path ) {
        global $wpdb;

        if ( ! current_user_can( $this->iup_instance->capability )  ) {
            wp_send_json_error( esc_html__( 'Permissions Error: Please refresh the page and try again.', 'infinite-uploads' ) );
        }

        $upload_dir = wp_upload_dir();

        $to_sync[] = (object) [
                'file' => $image_path,
                'size' => file_exists( $upload_dir['basedir'] . $image_path ) ? filesize( $upload_dir['basedir'] . $image_path ) : 0
        ];

        $downloaded = 0;
        $errors     = [];
        $break      = false;
        $path       = $this->iup_instance->get_original_upload_dir_root();
        $s3         = $this->iup_instance->s3();

        //build full paths
        $to_sync_full = [];
        $to_sync_size = 0;
        $to_sync_sql  = [];
        foreach ( $to_sync as $file ) {
            $to_sync_size += $file->size;
            if ( count( $to_sync_full ) && $to_sync_size > INFINITE_UPLOADS_SYNC_MAX_BYTES ) { //upload at minimum one file even if it's huuuge
                break;
            }
            $to_sync_full[] = 's3://' . untrailingslashit( $this->iup_instance->bucket ) . $file->file;
            $to_sync_sql[]  = esc_sql( $file->file );
        }

        $obj  = new ArrayObject( $to_sync_full );
        $from = $obj->getIterator();

        $transfer_args = [
                'concurrency' => INFINITE_UPLOADS_SYNC_CONCURRENCY,
                'base_dir'    => 's3://' . $this->iup_instance->bucket,
                'before'      => function ( Command $command ) use ( $wpdb, &$downloaded ) {//add middleware to intercept result of each file upload
                    if ( in_array( $command->getName(), [ 'GetObject' ], true ) ) {
                        $command->getHandlerList()->appendSign(
                                Middleware::mapResult( function ( ResultInterface $result ) use ( $wpdb, &$downloaded ) {
                                    $downloaded ++;
                                    $file = $this->iup_instance->get_file_from_result( $result );
                                    return $result;
                                } )
                        );
                    }
                },
        ];
        try {
            $manager = new Transfer( $s3, $from, $path['basedir'], $transfer_args );
            $manager->transfer();
        } catch ( \Exception $e ) {
            //echo $e->__toString();
            if ( method_exists( $e, 'getRequest' ) ) {
                $file        = str_replace( untrailingslashit( $path['basedir'] ), '', str_replace( trailingslashit( $this->iup_instance->bucket ), '', $e->getRequest()->getRequestTarget() ) );
            } else {
                $errors[] = esc_html__( 'Error downloading file. Queued for retry.', 'infinite-uploads' );
            }
        }
    }

    public function offload_image( $image_path ) {
        global $wpdb;
        $uploaded = 0;
        $errors   = [];
        $break    = false;
        $is_done  = false;
        $path     = $this->iup_instance->get_original_upload_dir_root();
        $s3       = $this->iup_instance->s3();
        if ( ! current_user_can( $this->iup_instance->capability ) ) {
            wp_send_json_error( esc_html__( 'Permissions Error: Please refresh the page and try again.', 'infinite-uploads' ) );
        }
        $orig_path = $this->iup_instance->get_original_upload_dir_root();
        $to_sync[] = (object) [
                'file' => $image_path,
                'size' => file_exists( $orig_path['basedir'] . '/' . $image_path ) ? filesize( $orig_path['basedir'] . '/' . $image_path ) : 0
        ];

        if ( $to_sync ) {
            //build full paths
            $to_sync_full = [];
            $to_sync_size = 0;
            $to_sync_sql  = [];
            foreach ( $to_sync as $file ) {
                $to_sync_size += $file->size;
                if ( count( $to_sync_full ) && $to_sync_size > INFINITE_UPLOADS_SYNC_MAX_BYTES ) { //upload at minimum one file even if it's huuuge
                    break;
                }
                $to_sync_full[] = $path['basedir'] . $file->file;
                $to_sync_sql[]  = esc_sql( $file->file );
            }
            $concurrency = count( $to_sync_full ) > 1 ? INFINITE_UPLOADS_SYNC_CONCURRENCY : INFINITE_UPLOADS_SYNC_MULTIPART_CONCURRENCY;
            $obj         = new ArrayObject( $to_sync_full );
            $from        = $obj->getIterator();

            $transfer_args = [
                    'concurrency' => $concurrency,
                    'base_dir'    => $path['basedir'],
                    'before'      => function ( Command $command ) use ( $wpdb, &$uploaded, &$errors, &$part_sizes ) {
                        //add middleware to modify object headers
                        if ( in_array( $command->getName(), [ 'PutObject', 'CreateMultipartUpload' ], true ) ) {
                            /// Expires:
                            if ( defined( 'INFINITE_UPLOADS_HTTP_EXPIRES' ) ) {
                                $command['Expires'] = INFINITE_UPLOADS_HTTP_EXPIRES;
                            }
                            // Cache-Control:
                            if ( defined( 'INFINITE_UPLOADS_HTTP_CACHE_CONTROL' ) ) {
                                if ( is_numeric( INFINITE_UPLOADS_HTTP_CACHE_CONTROL ) ) {
                                    $command['CacheControl'] = 'max-age=' . INFINITE_UPLOADS_HTTP_CACHE_CONTROL;
                                } else {
                                    $command['CacheControl'] = INFINITE_UPLOADS_HTTP_CACHE_CONTROL;
                                }
                            }
                        }

                        if ( in_array( $command->getName(), [ 'PutObject' ], true ) ) {
                            $this->sync_debug_log( "Uploading key {$command['Key']}" );
                        }

                    },
            ];
            try {
                $manager = new Transfer( $s3, $from, 's3://' . $this->iup_instance->bucket . '/', $transfer_args );
                $manager->transfer();
            } catch ( \Exception $e ) {
                $this->sync_debug_log( "Transfer sync exception: " . $e->__toString() );
                //echo $e->__toString();
                if ( method_exists( $e, 'getRequest' ) ) {
                    $file        = str_replace( trailingslashit( $this->iup_instance->bucket ), '', $e->getRequest()->getRequestTarget() );
                } else { //I don't know which error case trigger this but it's common
                    $errors[] = esc_html__( 'Error uploading file. Queued for retry.', 'infinite-uploads' );
                }
            }
        }
    }


    public function ajax_sync_errors() {
        global $wpdb;

        // check caps
        if ( ! current_user_can( $this->iup_instance->capability ) ) {
            wp_send_json_error( esc_html__( 'Permissions Error: Please refresh the page and try again.', 'infinite-uploads' ) );
        }

        $html       = '';
        $error_list = $wpdb->get_results( "SELECT file, size FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE synced = 0 AND errors >= 3" );
        foreach ( $error_list as $error ) {
            $html .= sprintf( '<li class="list-group-item list-group-item-warning">%s - %s</li>', esc_html( $error->file ), size_format( $error->size, 2 ) ) . PHP_EOL;
        }
        wp_send_json_success( $html );
    }

    /**
     * AJAX handler to reset error counts.
     *
     * @return void
     */
    public function ajax_reset_errors() {
        global $wpdb;

        // check caps
        if ( ! current_user_can( $this->iup_instance->capability ) ) {
            wp_send_json_error( esc_html__( 'Permissions Error: Please refresh the page and try again.', 'infinite-uploads' ) );
        }

        $result = $wpdb->query( "UPDATE `{$wpdb->base_prefix}infinite_uploads_files` SET errors = 0, transferred = 0 WHERE synced = 0 AND errors >= 3" );

        wp_send_json_success( $result );
    }

    /**
     * AJAX handler to get file list.
     *
     * @return void
     */
    public function ajax_filelist() {
        global $wpdb;

        // check caps
        if ( ! current_user_can( $this->iup_instance->capability ) || ! wp_verify_nonce( $_POST['nonce'], 'iup_scan' ) ) {
            wp_send_json_error( esc_html__( 'Permissions Error: Please refresh the page and try again.', 'infinite-uploads' ) );
        }

        $this->sync_debug_log( "Ajax time limit: " . $this->ajax_timelimit );

        $path = $this->iup_instance->get_original_upload_dir_root();
        $path = $path['basedir'];

        $remaining_dirs = [];
        $is_continuing  = ! empty( $_POST['scan_continue'] );

        if ( $is_continuing ) {
            // Retrieve remaining dirs from server-side storage to avoid large POST payloads.
            $remaining_dirs = get_site_option( 'iup_scan_remaining_dirs', [] );
        } elseif ( ! empty( $this->iup_instance->bucket ) ) {
            //If we are starting a new filesync and are logged into cloud storage abort any unfinished multipart uploads
            $to_abort = $wpdb->get_results( "SELECT file, transfer_status as upload_id FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE transfer_status IS NOT NULL" );
            if ( $to_abort ) {
                $s3       = $this->iup_instance->s3();
                $prefix   = $this->iup_instance->get_s3_prefix();
                $bucket   = $this->iup_instance->get_s3_bucket();
                $commands = [];
                foreach ( $to_abort as $file ) {
                    $key = $prefix . $file->file;
                    // Abort the multipart upload.
                    $commands[] = $s3->getCommand( 'abortMultipartUpload', [
                            'Bucket'   => $bucket,
                            'Key'      => $key,
                            'UploadId' => $file->upload_id,
                    ] );
                    $this->sync_debug_log( "Aborting multipart upload for {$file->file} UploadId {$file->upload_id}" );
                }
                // Create a command pool
                $pool = new CommandPool( $s3, $commands );

                // Begin asynchronous execution of the commands
                $promise = $pool->promise();
            }
        }

        $filelist = new InfiniteUploadsFilelist( $path, $this->ajax_timelimit, $remaining_dirs );
        $filelist->start();
        $this_file_count = count( $filelist->file_list );
        $remaining_dirs  = $filelist->paths_left;
        $is_done         = $filelist->is_done;
        $nonce           = wp_create_nonce( 'iup_scan' );

        // Store remaining dirs server-side to avoid large POST payloads.
        if ( ! $is_done ) {
            update_site_option( 'iup_scan_remaining_dirs', $remaining_dirs );
        } else {
            delete_site_option( 'iup_scan_remaining_dirs' );
        }

        $data  = compact( 'this_file_count', 'is_done', 'nonce' );
        $stats = $this->iup_instance->get_sync_stats();
        if ( $stats ) {
            $data = array_merge( $data, $stats );
        }

        // Force the abortMultipartUpload pool to complete synchronously just in case it hasn't finished
        if ( isset( $promise ) ) {
            $promise->wait();
        }

        wp_send_json_success( $data );
    }

    public function ajax_remote_filelist() {
        global $wpdb;

        // check caps
        if ( ! current_user_can( $this->iup_instance->capability ) || ! wp_verify_nonce( $_POST['nonce'], 'iup_scan' ) ) {
            wp_send_json_error( esc_html__( 'Permissions Error: Please refresh the page and try again.', 'infinite-uploads' ) );
        }

        $this->sync_debug_log( "Ajax time limit: " . $this->ajax_timelimit );

        $s3     = $this->iup_instance->s3();
        $prefix = $this->iup_instance->get_s3_prefix();

        $args = [
                'Bucket' => $this->iup_instance->get_s3_bucket(),
                'Prefix' => trailingslashit( $prefix ),
        ];

        if ( ! empty( $_POST['next_token'] ) ) {
            $args['ContinuationToken'] = sanitize_text_field( $_POST['next_token'] );
        } else {
            $progress                    = get_site_option( 'iup_files_scanned' );
            $progress['compare_started'] = time();
            update_site_option( 'iup_files_scanned', $progress );
        }

        try {
            $results    = $s3->getPaginator( 'ListObjectsV2', $args );
            $req_count  = $file_count = 0;
            $is_done    = false;
            $next_token = null;
            foreach ( $results as $result ) {
                $req_count ++;
                $is_done          = ! $result['IsTruncated'];
                $next_token       = isset( $result['NextContinuationToken'] ) ? $result['NextContinuationToken'] : null;
                $cloud_only_files = [];
                if ( $result['Contents'] ) {
                    foreach ( $result['Contents'] as $object ) {
                        $file_count ++;
                        $local_key = str_replace( $prefix, '', $object['Key'] );
                        $file      = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->base_prefix}infinite_uploads_files WHERE file = %s", $local_key ) );
                        if ( $file && ! $file->synced && $file->size == $object['Size'] ) {
                            $this->sync_debug_log( "Already synced file found: $local_key " . size_format( $file->size, 2 ) );
                            $wpdb->update( "{$wpdb->base_prefix}infinite_uploads_files", [
                                    'synced'      => 1,
                                    'transferred' => $file->size,
                            ], [ 'file' => $local_key ] );
                        }
                        if ( ! $file ) {
                            $this->sync_debug_log( "Cloud only file found: $local_key " . size_format( $object['Size'], 2 ) );
                            $cloud_only_files[] = [
                                    'name'  => $local_key,
                                    'size'  => $object['Size'],
                                    'mtime' => strtotime( $object['LastModified']->__toString() ),
                                    'type'  => $this->iup_instance->get_file_type( $local_key ),
                            ];
                        }
                    }
                }

                //flush new files to db
                if ( count( $cloud_only_files ) ) {
                    $values = [];
                    foreach ( $cloud_only_files as $file ) {
                        $values[] = $wpdb->prepare( "(%s,%d,%d,%s,%d,1,1)", $file['name'], $file['size'], $file['mtime'], $file['type'], $file['size'] );
                    }

                    $query = "INSERT INTO {$wpdb->base_prefix}infinite_uploads_files (file, size, modified, type, transferred, synced, deleted) VALUES ";
                    $query .= implode( ",\n", $values );
                    $query .= " ON DUPLICATE KEY UPDATE size = VALUES(size), modified = VALUES(modified), type = VALUES(type), transferred = VALUES(transferred), synced = 1, deleted = 1, errors = 0";
                    $wpdb->query( $query );
                }

                if ( ( $timer = timer_stop() ) >= $this->ajax_timelimit ) {
                    break;
                }
            }

            if ( $is_done ) {
                $progress                     = get_site_option( 'iup_files_scanned' );
                $progress['compare_finished'] = time();
                update_site_option( 'iup_files_scanned', $progress );
            }


            $nonce = wp_create_nonce( 'iup_scan' );
            $data  = compact( 'file_count', 'req_count', 'is_done', 'next_token', 'timer', 'nonce' );
            $stats = $this->iup_instance->get_sync_stats();
            if ( $stats ) {
                $data = array_merge( $data, $stats );
            }

            wp_send_json_success( $data );
        } catch ( \Exception $e ) {
            wp_send_json_error( $e->getMessage() );
        }
    }

    public function ajax_syn_oldc() {
        global $wpdb;

        if ( ! current_user_can( $this->iup_instance->capability ) || ! wp_verify_nonce( $_POST['nonce'], 'iup_sync' ) ) {
            wp_send_json_error( esc_html__( 'Permissions Error: Please refresh the page and try again.', 'infinite-uploads' ) );
        }

        $progress = get_site_option( 'iup_files_scanned' );
        if ( ! $progress['sync_started'] ) {
            $progress['sync_started'] = time();
            update_site_option( 'iup_files_scanned', $progress );
        }

        //this loop has a parallel status check, so we make the timeout 2/3 of max execution time.
        $this->ajax_timelimit = max( 20, floor( ini_get( 'max_execution_time' ) * .6666 ) );
        $this->sync_debug_log( "Ajax time limit: " . $this->ajax_timelimit );
        $uploaded = 0;
        $errors   = [];
        $break    = false;
        $is_done  = false;
        $path     = $this->iup_instance->get_original_upload_dir_root();
        $s3       = $this->iup_instance->s3();
        while ( ! $break ) {
            $to_sync = $wpdb->get_results( $wpdb->prepare( "SELECT file, size FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE synced = 0 AND errors < 3 AND transfer_status IS NULL ORDER BY errors ASC, file ASC LIMIT %d", INFINITE_UPLOADS_SYNC_PER_LOOP ) );
            if ( $to_sync ) {
                //build full paths
                $to_sync_full = [];
                $to_sync_size = 0;
                $to_sync_sql  = [];
                foreach ( $to_sync as $file ) {
                    $to_sync_size += $file->size;
                    if ( count( $to_sync_full ) && $to_sync_size > INFINITE_UPLOADS_SYNC_MAX_BYTES ) { //upload at minimum one file even if it's huuuge
                        break;
                    }
                    $to_sync_full[] = $path['basedir'] . $file->file;
                    $to_sync_sql[]  = esc_sql( $file->file );
                }
                //preset the error count in case request times out. Successful sync will clear error count.
                $wpdb->query( "UPDATE `{$wpdb->base_prefix}infinite_uploads_files` SET errors = ( errors + 1 ) WHERE file IN ('" . implode( "','", $to_sync_sql ) . "')" );

                $this->sync_debug_log( "Transfer manager batch size " . size_format( $to_sync_size, 2 ) . ", " . count( $to_sync_full ) . " files." );
                $concurrency = count( $to_sync_full ) > 1 ? INFINITE_UPLOADS_SYNC_CONCURRENCY : INFINITE_UPLOADS_SYNC_MULTIPART_CONCURRENCY;
                $obj         = new \ArrayObject( $to_sync_full );
                $from        = $obj->getIterator();

                $transfer_args = [
                        'concurrency' => $concurrency,
                        'base_dir'    => $path['basedir'],
                        'before'      => function ( Command $command ) use ( $wpdb, &$uploaded, &$errors, &$part_sizes ) {
                            //add middleware to modify object headers
                            if ( in_array( $command->getName(), [ 'PutObject', 'CreateMultipartUpload' ], true ) ) {
                                /// Expires:
                                if ( defined( 'INFINITE_UPLOADS_HTTP_EXPIRES' ) ) {
                                    $command['Expires'] = INFINITE_UPLOADS_HTTP_EXPIRES;
                                }
                                // Cache-Control:
                                if ( defined( 'INFINITE_UPLOADS_HTTP_CACHE_CONTROL' ) ) {
                                    if ( is_numeric( INFINITE_UPLOADS_HTTP_CACHE_CONTROL ) ) {
                                        $command['CacheControl'] = 'max-age=' . INFINITE_UPLOADS_HTTP_CACHE_CONTROL;
                                    } else {
                                        $command['CacheControl'] = INFINITE_UPLOADS_HTTP_CACHE_CONTROL;
                                    }
                                }
                            }

                            if ( in_array( $command->getName(), [ 'PutObject' ], true ) ) {
                                $this->sync_debug_log( "Uploading key {$command['Key']}" );
                            }

                            //add middleware to intercept result of each file upload
                            if ( in_array( $command->getName(), [ 'PutObject', 'CompleteMultipartUpload' ], true ) ) {
                                $command->getHandlerList()->appendSign(
                                        Middleware::mapResult( function ( ResultInterface $result ) use ( $wpdb, &$uploaded, $command ) {
                                            $this->sync_debug_log( "Finished uploading file: " . $command['Key'] );
                                            $uploaded ++;
                                            $file = $this->iup_instance->get_file_from_result( $result );
                                            $wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->base_prefix}infinite_uploads_files` SET transferred = size, synced = 1, errors = 0, transfer_status = null WHERE file = %s", $file ) );

                                            return $result;
                                        } )
                                );
                            }

                            //add middleware to intercept result and record the uploadId for resuming later
                            if ( in_array( $command->getName(), [ 'CreateMultipartUpload' ], true ) ) {
                                $this->sync_debug_log( "Starting multipart upload for key {$command['Key']}" );
                                $command->getHandlerList()->appendSign(
                                        Middleware::mapResult( function ( ResultInterface $result ) use ( $wpdb ) {
                                            $file = $this->iup_instance->get_file_from_result( $result );
                                            $wpdb->update( "{$wpdb->base_prefix}infinite_uploads_files", [
                                                    'synced'          => 0,
                                                    'transfer_status' => $result['UploadId'],
                                            ], [ 'file' => $file ], [ '%d', '%s' ] );

                                            return $result;
                                        } )
                                );
                            }

                            //add middleware to check if we should bail before each new upload part
                            if ( in_array( $command->getName(), [ 'UploadPart' ], true ) ) {
                                $this->sync_debug_log( "Uploading key {$command['Key']} part {$command['PartNumber']}" );
                                $command->getHandlerList()->appendSign(
                                        Middleware::mapResult( function ( ResultInterface $result ) use ( $command ) {
                                            global $wpdb;
                                            $this->sync_debug_log( "Finished Uploading key {$command['Key']} part {$command['PartNumber']}" );

                                            $file = $this->iup_instance->get_file_from_result( $result );
                                            $wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->base_prefix}infinite_uploads_files` SET transferred = ( transferred + %d ), synced = 0, errors = 0 WHERE file = %s", $command['ContentLength'], $file ) );

                                            return $result;
                                        } )
                                );
                            }
                        },
                ];
                try {
                    $manager = new Transfer( $s3, $from, 's3://' . $this->iup_instance->bucket . '/', $transfer_args );
                    $manager->transfer();
                } catch ( \Exception $e ) {
                    // Route through the shared handler so this catch benefits
                    // from the permanent-failure retire logic (bumps errors to
                    // 3 on NoSuchKey / AccessDenied / etc. so one bad key
                    // doesn't keep aborting batches of healthy files).
                    $this->handle_transfer_exception( $wpdb, $e, $errors );
                }

            } else { // we are done with transfer manager, continue any unfinished multipart uploads one by one

                $to_sync = $wpdb->get_row( "SELECT file, size, errors, transfer_status as upload_id FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE synced = 0 AND errors < 3 AND transfer_status IS NOT NULL ORDER BY errors ASC, file ASC LIMIT 1" );
                if ( $to_sync ) {
                    $this->sync_debug_log( "Continuing multipart upload: " . $to_sync->file );

                    //preset the error count in case request times out. Successful sync will clear error count.
                    $wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->base_prefix}infinite_uploads_files` SET errors = ( errors + 1 ) WHERE file = %s", $to_sync->file ) );
                    $to_sync->errors ++; //increment error result so it's accurate

                    $key = $this->iup_instance->get_s3_prefix() . $to_sync->file;
                    try {
                        $upload_state = $this->iup_instance->get_multipart_upload_state( $key, $to_sync->upload_id );
                        $progress     = round( ( ( count( $upload_state->getUploadedParts() ) * $upload_state->getPartSize() ) / $to_sync->size ) * 100 );
                        $this->sync_debug_log( sprintf( 'Uploaded %s%% of file (%d, %s parts)', $progress, count( $upload_state->getUploadedParts() ), size_format( $upload_state->getPartSize() ) ) );
                        $wpdb->update( "{$wpdb->base_prefix}infinite_uploads_files", [ 'transferred' => ( count( $upload_state->getUploadedParts() ) * $upload_state->getPartSize() ) ], [ 'file' => $to_sync->file ], [ '%d' ] );

                        $parts_started = [];
                        $source        = $path['basedir'] . $to_sync->file;
                        $uploader      = new MultipartUploader( $s3, $source, [
                                'concurrency'   => INFINITE_UPLOADS_SYNC_MULTIPART_CONCURRENCY,
                                'state'         => $upload_state,
                                'before_upload' => function ( \Command $command ) use ( &$parts_started, $uploaded, $errors ) {
                                    $this->sync_debug_log( "Uploading key {$command['Key']} part {$command['PartNumber']}" );

                                    $command->getHandlerList()->appendSign(
                                            Middleware::mapResult( function ( ResultInterface $result ) use ( $command, &$parts_started, $uploaded, $errors ) {
                                                global $wpdb;
                                                $this->sync_debug_log( "Finished Uploading key {$command['Key']} part {$command['PartNumber']}" );

                                                $file = $this->iup_instance->get_file_from_result( $result );
                                                $wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->base_prefix}infinite_uploads_files` SET transferred = ( transferred + %d ), synced = 0, errors = 0 WHERE file = %s", $command['ContentLength'], $file ) );

                                                return $result;
                                            } )
                                    );
                                },
                        ] );

                        //Recover from errors
                        do {
                            try {
                                $result = $uploader->upload();
                            } catch ( MultipartUploadException $e ) {
                                $uploader = new MultipartUploader( $s3, $source, [
                                        'state'         => $e->getState(),
                                        'before_upload' => function ( Command $command ) use ( $wpdb ) {
                                            $this->sync_debug_log( "Uploading key {$command['Key']} part {$command['PartNumber']}" );
                                            $command->getHandlerList()->appendSign(
                                                    Middleware::mapResult( function ( ResultInterface $result ) use ( $wpdb, $command ) {
                                                        global $wpdb;
                                                        $this->sync_debug_log( "Finished Uploading key {$command['Key']} part {$command['PartNumber']}" );

                                                        $file = $this->iup_instance->get_file_from_result( $result );
                                                        $wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->base_prefix}infinite_uploads_files` SET transferred = ( transferred + %d ), synced = 0, errors = 0 WHERE file = %s", $command['ContentLength'], $file ) );

                                                        return $result;
                                                    } )
                                            );
                                        },
                                ] );
                            }
                        } while ( ! isset( $result ) );

                        //Abort a multipart upload if failed a second time
                        try {
                            $result = $uploader->upload();
                            $this->sync_debug_log( "Finished multipart file upload: " . $to_sync->file );
                            $uploaded ++;
                            $wpdb->update( "{$wpdb->base_prefix}infinite_uploads_files", [
                                    'transferred'     => $to_sync->size,
                                    'synced'          => 1,
                                    'errors'          => 0,
                                    'transfer_status' => null,
                            ], [ 'file' => $to_sync->file ], [ '%d', '%d', '%d', null ] );
                        } catch ( MultipartUploadException $e ) {
                            $params = $e->getState()->getId();
                            $result = $s3->abortMultipartUpload( $params );
                            //restart the multipart
                            $wpdb->update( "{$wpdb->base_prefix}infinite_uploads_files", [
                                    'transferred'     => 0,
                                    'synced'          => 0,
                                    'transfer_status' => null,
                            ], [ 'file' => $to_sync->file ], [ '%d', null ] );
                            $this->sync_debug_log( "Get multipart retry UploadState exception: " . $e->__toString() );
                            if ( ( $to_sync->errors ) >= 3 ) {
                                $errors[] = sprintf( esc_html__( 'Error uploading %s. Retries exceeded.', 'infinite-uploads' ), $to_sync->file );
                            } else {
                                $errors[] = sprintf( esc_html__( 'Error uploading %s. Queued for retry.', 'infinite-uploads' ), $to_sync->file );
                            }
                        }

                    } catch ( \Exception $e ) {
                        // Route through the shared handler for permanent-
                        // failure retire (NoSuchKey / AccessDenied bump the
                        // file's errors to 3 immediately, freeing subsequent
                        // multipart passes from re-attempting a doomed key).
                        $this->handle_multipart_exception( $wpdb, $to_sync, $e, $errors );
                    }

                } else {
                    $is_done = true;
                }
            }

            if ( $is_done || timer_stop() >= $this->ajax_timelimit ) {
                $break            = true;
                $permanent_errors = false;

                if ( $is_done ) {
                    $permanent_errors          = (int) $wpdb->get_var( "SELECT count(*) FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE synced = 0 AND errors >= 3" );
                    $progress                  = get_site_option( 'iup_files_scanned' );
                    $progress['sync_finished'] = time();
                    update_site_option( 'iup_files_scanned', $progress );
                }

                $nonce = wp_create_nonce( 'iup_sync' );
                wp_send_json_success( array_merge( compact( 'uploaded', 'is_done', 'errors', 'permanent_errors', 'nonce' ), $this->iup_instance->get_sync_stats() ) );
            }
        }
    }


    public function ajax_sync() {
        global $wpdb;

        // SECURITY: Enhanced nonce and permission checks
        if ( ! current_user_can( $this->iup_instance->capability ) ) {
            wp_send_json_error( esc_html__( 'Permissions Error: Insufficient privileges.', 'infinite-uploads' ), 403 );
        }

        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'iup_sync' ) ) {
            wp_send_json_error( esc_html__( 'Security Error: Invalid nonce. Please refresh and try again.', 'infinite-uploads' ), 403 );
        }

        // SECURITY: Check AJAX referer
        check_ajax_referer( 'iup_sync', 'nonce' );

        // PERFORMANCE: Use transients instead of site options for frequently updated data
        $progress = get_transient( 'iup_files_sync_progress' );
        if ( false === $progress ) {
            $progress = get_site_option( 'iup_files_scanned', [] );
        }

        if ( empty( $progress['sync_started'] ) ) {
            $progress['sync_started'] = time();
            update_site_option( 'iup_files_scanned', $progress );
            set_transient( 'iup_files_sync_progress', $progress, HOUR_IN_SECONDS );
        }

        // Cap the per-request budget well under common gateway timeouts
        // (Cloudflare 100s, nginx default 60s, most shared hosts 60-120s).
        // Prior versions scaled ajax_timelimit to max_execution_time * 0.6666,
        // but PHP's max_execution_time excludes network I/O on Unix, so it
        // never actually bounded this request. Requests routinely overshot
        // the gateway timeout and got silently killed mid-upload with no PHP
        // error log entry — surfacing to users as "Too many server errors."
        //
        // 45s (not 60s) because when the Generator returns at the deadline,
        // Transfer::transfer() still waits for its in-flight pool to drain
        // — measured p99 file duration is 8-13s, so a request that hits
        // the deadline mid-flight typically returns 8-13s later. 45s + a
        // 15s drain reservation lands comfortably under nginx's 60s. Sites
        // with a higher gateway ceiling can raise via the filter.
        $default_timelimit    = min( 45, max( 20, (int) floor( ini_get( 'max_execution_time' ) * 0.6666 ) ) );
        $this->ajax_timelimit = (int) apply_filters( 'infinite_uploads_ajax_timelimit', $default_timelimit );
        $this->sync_debug_log( "Ajax time limit: {$this->ajax_timelimit}" );

        // Initialize counters
        $uploaded = 0;
        $errors   = [];

        // SECURITY: Validate and sanitize paths
        $path = $this->iup_instance->get_original_upload_dir_root();
        if ( empty( $path['basedir'] ) || ! is_dir( $path['basedir'] ) ) {
            wp_send_json_error( esc_html__( 'Error: Invalid upload directory.', 'infinite-uploads' ), 500 );
        }

        $s3 = $this->iup_instance->s3();

        // Feed the Transfer manager a continuous Generator instead of fixed
        // ~12MB batches so its concurrency pool stays saturated end-to-end —
        // the next file starts the moment any completes instead of every N
        // files draining to zero at the batch barrier while everyone waits
        // on the p99 straggler. The Generator also checks the deadline
        // before each yield, so budget expiry interrupts cleanly at a file
        // boundary (never mid-multipart, which used to overshoot by 30+s).
        $iterator = $this->build_sync_upload_iterator( $wpdb, $path, $this->ajax_timelimit );

        try {
            $transfer_args = [
                    'concurrency' => INFINITE_UPLOADS_SYNC_CONCURRENCY,
                    'base_dir'    => $path['basedir'],
                    'before'      => $this->create_transfer_middleware( $wpdb, $uploaded, $errors ),
            ];
            $manager       = new Transfer(
                    $s3,
                    $iterator,
                    's3://' . $this->iup_instance->bucket . '/',
                    $transfer_args
            );
            $manager->transfer();
        } catch ( \Exception $e ) {
            $this->handle_transfer_exception( $wpdb, $e, $errors );
        }

        // Drain any leftover multipart continuations (files whose
        // CreateMultipartUpload initiated in Phase 1 or a previous request
        // but never completed). One at a time; if a single multipart eats
        // the remaining budget, the outer wp-cron/JS retry loop picks up
        // where we left off next request.
        //
        // Require ≥5s of remaining budget before starting a fresh multipart
        // continuation — a multipart cannot be interrupted mid-flight, and
        // starting one with 1-2s left routinely overshoots the deadline
        // (and, cascading, the gateway timeout).
        while ( timer_stop() + 5 < $this->ajax_timelimit ) {
            $pending = $wpdb->get_row(
                    "SELECT file, size, errors, transfer_status AS upload_id
                       FROM `{$wpdb->base_prefix}infinite_uploads_files`
                      WHERE synced = 0 AND errors < 3 AND transfer_status IS NOT NULL
                      ORDER BY errors ASC, file ASC
                      LIMIT 1"
            );
            if ( ! $pending ) {
                break;
            }
            $this->process_multipart_sync( $wpdb, $pending, $path, $s3, $uploaded, $errors );
        }

        // Determine whether the whole queue drained.
        $remaining = (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM `{$wpdb->base_prefix}infinite_uploads_files`
                  WHERE synced = 0 AND errors < 3"
        );
        $is_done          = ( 0 === $remaining );
        $permanent_errors = 0;

        if ( $is_done ) {
            $permanent_errors = (int) $wpdb->get_var(
                    "SELECT COUNT(*) FROM `{$wpdb->base_prefix}infinite_uploads_files`
                      WHERE synced = 0 AND errors >= 3"
            );

            $progress                  = get_site_option( 'iup_files_scanned', [] );
            $progress['sync_finished'] = time();
            update_site_option( 'iup_files_scanned', $progress );
            delete_transient( 'iup_files_sync_progress' );
        }

        // SECURITY: Regenerate nonce for next request
        $nonce = wp_create_nonce( 'iup_sync' );

        wp_send_json_success(
                array_merge(
                        compact( 'uploaded', 'is_done', 'errors', 'permanent_errors', 'nonce' ),
                        $this->iup_instance->get_sync_stats()
                )
        );
    }

    /**
     * Continuous Generator that feeds the Transfer manager one file at a
     * time, drawn from the DB on demand. Replaces the pre-batching
     * ArrayObject in the old flow, which caused the concurrency pool to
     * drain to zero between batches. Pre-increments errors per file (so
     * an interrupted request penalises only files that were actually
     * attempted, unlike the old bulk `errors + 1` on the whole batch);
     * middleware clears errors back to 0 on success. Yields absolute
     * local file paths; halts when the deadline elapses or the eligible
     * queue is empty.
     *
     * @param  \wpdb  $wpdb
     * @param  array  $path      From get_original_upload_dir_root().
     * @param  int    $deadline  Seconds budget (compared against timer_stop()).
     *
     * @return \Generator<string>
     */
    private function build_sync_upload_iterator( $wpdb, $path, $deadline ) {
        $base_real = realpath( $path['basedir'] );
        if ( false === $base_real ) {
            return;
        }
        // Rolling window of recently-yielded file names, carried as a
        // NOT IN clause on the next page refresh so a still-in-flight
        // file can't be re-yielded when the fresh (errors=0) pool nears
        // exhaustion. Bounded so the placeholder list can't blow up on
        // large syncs — see INFINITE_UPLOADS_SYNC_ITERATOR_INFLIGHT_WINDOW.
        $inflight_window = [];

        while ( true ) {
            if ( timer_stop() >= $deadline ) {
                return;
            }

            $exclude_sql  = '';
            $prepare_args = [];

            if ( ! empty( $inflight_window ) ) {
                $placeholders = implode( ',', array_fill( 0, count( $inflight_window ), '%s' ) );
                $exclude_sql  = "AND file NOT IN ($placeholders)";
                $prepare_args = array_values( $inflight_window );
            }
            $prepare_args[] = INFINITE_UPLOADS_SYNC_ITERATOR_PAGE_SIZE;

            $rows = $wpdb->get_results(
                    $wpdb->prepare(
                            "SELECT file, size FROM `{$wpdb->base_prefix}infinite_uploads_files`
                              WHERE synced = 0 AND errors < 3 AND transfer_status IS NULL $exclude_sql
                              ORDER BY errors ASC, file ASC
                              LIMIT %d",
                            ...$prepare_args
                    )
            );

            if ( empty( $rows ) ) {
                return;
            }

            foreach ( $rows as $row ) {
                if ( timer_stop() >= $deadline ) {
                    return;
                }

                // SECURITY: Validate file path to prevent directory traversal.
                $file_path = $path['basedir'] . $row->file;
                $real_path = realpath( $file_path );
                if ( false === $real_path || 0 !== strpos( $real_path, $base_real ) ) {
                    $this->sync_debug_log( "Security: Invalid file path detected: {$row->file}" );
                    // Track it in the window so the next refresh doesn't
                    // return the same invalid row and re-invoke this branch.
                    $this->push_inflight_window( $inflight_window, $row->file );
                    continue;
                }

                // Pre-increment error count so an interrupted upload counts
                // toward retry-retirement; middleware resets to 0 on success.
                $wpdb->query(
                        $wpdb->prepare(
                                "UPDATE `{$wpdb->base_prefix}infinite_uploads_files`
                                    SET errors = ( errors + 1 )
                                  WHERE file = %s",
                                $row->file
                        )
                );

                $this->push_inflight_window( $inflight_window, $row->file );
                yield $file_path;
            }
        }
    }

    /**
     * Append a file name to the rolling in-flight window and drop the
     * oldest entries once the window exceeds
     * INFINITE_UPLOADS_SYNC_ITERATOR_INFLIGHT_WINDOW. The window only
     * needs to be large enough to cover files still in the Transfer
     * manager's concurrency pool at any moment — older entries either
     * completed (excluded by SELECT `synced = 0`) or failed (sit behind
     * fresh files in the ORDER BY tail).
     *
     * @param  array  &$window
     * @param  string  $file
     */
    private function push_inflight_window( &$window, $file ) {
        $window[] = $file;
        $limit    = (int) INFINITE_UPLOADS_SYNC_ITERATOR_INFLIGHT_WINDOW;
        $overflow = count( $window ) - $limit;
        if ( $overflow > 0 ) {
            $window = array_slice( $window, $overflow );
        }
    }

    /**
     * PERFORMANCE: Extracted batch sync logic into separate method
     */
    /**
     * Continue a previously-initiated multipart upload that was interrupted
     * (either mid-flight in an earlier request that hit its deadline, or
     * because the CreateMultipartUpload middleware ran but the completing
     * UploadPart / CompleteMultipartUpload commands didn't).
     *
     * The historical implementation of this method here delegated to two
     * helpers (create_multipart_middleware, execute_multipart_upload) that
     * were never defined — so any request that fell into this branch hit a
     * fatal "Call to undefined method" and surfaced to the user as "Too
     * many server errors. Please try again." from the JS retry counter,
     * with nothing in debug.log. This body is the working equivalent from
     * do_sync(), inlined so it actually runs.
     */
    private function process_multipart_sync( $wpdb, $to_sync, $path, $s3, &$uploaded, &$errors ) {
        $this->sync_debug_log( "Continuing multipart upload: {$to_sync->file}" );

        // Preset the error count in case the request times out mid-flight.
        // Successful upload clears it back to 0 via the middleware below.
        $wpdb->query(
                $wpdb->prepare(
                        "UPDATE `{$wpdb->base_prefix}infinite_uploads_files`
                            SET errors = ( errors + 1 )
                          WHERE file = %s",
                        $to_sync->file
                )
        );
        $to_sync->errors ++;

        // SECURITY: Validate file path
        $source      = $path['basedir'] . $to_sync->file;
        $real_source = realpath( $source );
        $base_real   = realpath( $path['basedir'] );

        if ( false === $real_source || false === $base_real || 0 !== strpos( $real_source, $base_real ) ) {
            $this->sync_debug_log( "Security: Invalid multipart file path: {$to_sync->file}" );
            $errors[] = sprintf(
                    esc_html__( 'Security error for file %s.', 'infinite-uploads' ),
                    esc_html( $to_sync->file )
            );

            return;
        }

        $key = $this->iup_instance->get_s3_prefix() . $to_sync->file;

        try {
            $upload_state   = $this->iup_instance->get_multipart_upload_state( $key, $to_sync->upload_id );
            $uploaded_parts = count( $upload_state->getUploadedParts() );
            $part_size      = $upload_state->getPartSize();
            $progress       = $to_sync->size > 0
                    ? round( ( ( $uploaded_parts * $part_size ) / $to_sync->size ) * 100 )
                    : 0;

            $this->sync_debug_log( sprintf(
                    'Uploaded %s%% of file (%d parts, %s each)',
                    $progress,
                    $uploaded_parts,
                    size_format( $part_size )
            ) );

            $wpdb->update(
                    "{$wpdb->base_prefix}infinite_uploads_files",
                    [ 'transferred' => $uploaded_parts * $part_size ],
                    [ 'file' => $to_sync->file ],
                    [ '%d' ],
                    [ '%s' ]
            );

            $uploader = new MultipartUploader( $s3, $source, [
                    'concurrency'   => INFINITE_UPLOADS_SYNC_MULTIPART_CONCURRENCY,
                    'state'         => $upload_state,
                    'before_upload' => $this->build_multipart_before_upload( $wpdb ),
            ] );

            // Attempt the multipart. On a first MultipartUploadException we
            // rebuild the uploader from the exception's state (which
            // includes the parts that DID complete) and retry once. If
            // the retry also fails, we abort the multipart and either
            // report the error or retire the file per its retry count.
            try {
                $result = $uploader->upload();
            } catch ( MultipartUploadException $e ) {
                $this->sync_debug_log( "Multipart retry after: " . $e->getMessage() );
                $uploader = new MultipartUploader( $s3, $source, [
                        'state'         => $e->getState(),
                        'before_upload' => $this->build_multipart_before_upload( $wpdb ),
                ] );

                try {
                    $result = $uploader->upload();
                } catch ( MultipartUploadException $retry_e ) {
                    // Both attempts failed — abort the multipart so a fresh
                    // one can be initiated next request (a stale UploadId
                    // would keep failing indefinitely otherwise).
                    $s3->abortMultipartUpload( $retry_e->getState()->getId() );
                    $wpdb->update(
                            "{$wpdb->base_prefix}infinite_uploads_files",
                            [
                                    'transferred'     => 0,
                                    'synced'          => 0,
                                    'transfer_status' => null,
                            ],
                            [ 'file' => $to_sync->file ],
                            [ '%d', '%d', null ],
                            [ '%s' ]
                    );
                    $this->sync_debug_log( "Multipart abort after retry: " . $retry_e->__toString() );

                    $errors[] = ( $to_sync->errors >= 3 )
                            ? sprintf( esc_html__( 'Error uploading %s. Retries exceeded.', 'infinite-uploads' ), esc_html( $to_sync->file ) )
                            : sprintf( esc_html__( 'Error uploading %s. Queued for retry.', 'infinite-uploads' ), esc_html( $to_sync->file ) );

                    return;
                }
            }

            $this->sync_debug_log( "Finished multipart file upload: " . $to_sync->file );
            $uploaded ++;
            $wpdb->update(
                    "{$wpdb->base_prefix}infinite_uploads_files",
                    [
                            'transferred'     => $to_sync->size,
                            'synced'          => 1,
                            'errors'          => 0,
                            'transfer_status' => null,
                    ],
                    [ 'file' => $to_sync->file ],
                    [ '%d', '%d', '%d', null ],
                    [ '%s' ]
            );

        } catch ( \Exception $e ) {
            // Route through the shared handler for permanent-failure retire
            // (NoSuchKey / AccessDenied bump the file's errors to 3 so
            // subsequent multipart passes don't re-attempt a doomed key).
            $this->handle_multipart_exception( $wpdb, $to_sync, $e, $errors );
        }
    }

    /**
     * Build the `before_upload` closure used by the MultipartUploader for
     * both the initial attempt and the retry attempt. Logs each part and
     * increments the row's `transferred` counter as parts complete.
     */
    private function build_multipart_before_upload( $wpdb ) {
        return function ( Command $command ) use ( $wpdb ) {
            $this->sync_debug_log( "Uploading key {$command['Key']} part {$command['PartNumber']}" );

            $command->getHandlerList()->appendSign(
                    Middleware::mapResult( function ( ResultInterface $result ) use ( $wpdb, $command ) {
                        $this->sync_debug_log( "Finished Uploading key {$command['Key']} part {$command['PartNumber']}" );

                        $file = $this->iup_instance->get_file_from_result( $result );
                        $wpdb->query(
                                $wpdb->prepare(
                                        "UPDATE `{$wpdb->base_prefix}infinite_uploads_files`
                                            SET transferred = ( transferred + %d ),
                                                synced = 0,
                                                errors = 0
                                          WHERE file = %s",
                                        $command['ContentLength'],
                                        $file
                                )
                        );

                        return $result;
                    } )
            );
        };
    }

    /**
     * PERFORMANCE: Reusable middleware factory
     */
    private function create_transfer_middleware( $wpdb, &$uploaded, &$errors ) {
        return function ( Command $command ) use ( $wpdb, &$uploaded, &$errors ) {
            // Add object header middleware
            if ( in_array( $command->getName(), [ 'PutObject', 'CreateMultipartUpload' ], true ) ) {
                if ( defined( 'INFINITE_UPLOADS_HTTP_EXPIRES' ) ) {
                    $command['Expires'] = INFINITE_UPLOADS_HTTP_EXPIRES;
                }
                if ( defined( 'INFINITE_UPLOADS_HTTP_CACHE_CONTROL' ) ) {
                    $command['CacheControl'] = is_numeric( INFINITE_UPLOADS_HTTP_CACHE_CONTROL )
                            ? 'max-age=' . INFINITE_UPLOADS_HTTP_CACHE_CONTROL
                            : INFINITE_UPLOADS_HTTP_CACHE_CONTROL;
                }
            }

            // Log uploads
            if ( $command->getName() === 'PutObject' ) {
                $this->sync_debug_log( "Uploading key {$command['Key']}" );
            }

            // Handle completion
            if ( in_array( $command->getName(), [ 'PutObject', 'CompleteMultipartUpload' ], true ) ) {
                $command->getHandlerList()->appendSign(
                        Middleware::mapResult( function ( ResultInterface $result ) use ( $wpdb, &$uploaded, $command ) {
                            $this->sync_debug_log( "Finished uploading file: {$command['Key']}" );
                            $uploaded ++;

                            $file = $this->iup_instance->get_file_from_result( $result );
                            $wpdb->query(
                                    $wpdb->prepare(
                                            "UPDATE `{$wpdb->base_prefix}infinite_uploads_files` 
                            SET transferred = size, synced = 1, errors = 0, transfer_status = NULL 
                            WHERE file = %s",
                                            $file
                                    )
                            );

                            return $result;
                        } )
                );
            }

            // Handle multipart initialization
            if ( $command->getName() === 'CreateMultipartUpload' ) {
                $this->sync_debug_log( "Starting multipart upload for key {$command['Key']}" );
                $command->getHandlerList()->appendSign(
                        Middleware::mapResult( function ( ResultInterface $result ) use ( $wpdb ) {
                            $file = $this->iup_instance->get_file_from_result( $result );
                            $wpdb->update(
                                    "{$wpdb->base_prefix}infinite_uploads_files",
                                    [
                                            'synced'          => 0,
                                            'transfer_status' => $result['UploadId'],
                                    ],
                                    [ 'file' => $file ],
                                    [ '%d', '%s' ],
                                    [ '%s' ]
                            );

                            return $result;
                        } )
                );
            }

            // Handle part uploads
            if ( $command->getName() === 'UploadPart' ) {
                $this->sync_debug_log( "Uploading key {$command['Key']} part {$command['PartNumber']}" );
                $command->getHandlerList()->appendSign(
                        Middleware::mapResult( function ( ResultInterface $result ) use ( $wpdb, $command ) {
                            $this->sync_debug_log( "Finished uploading key {$command['Key']} part {$command['PartNumber']}" );

                            $file = $this->iup_instance->get_file_from_result( $result );
                            $wpdb->query(
                                    $wpdb->prepare(
                                            "UPDATE `{$wpdb->base_prefix}infinite_uploads_files` 
                            SET transferred = (transferred + %d), synced = 0, errors = 0 
                            WHERE file = %s",
                                            $command['ContentLength'],
                                            $file
                                    )
                            );

                            return $result;
                        } )
                );
            }
        };
    }

    /**
     * SECURITY: Centralized exception handling with proper error messages.
     *
     * The batch upload path pre-increments `errors` for every file in a batch
     * BEFORE the Transfer starts and only resets on per-file success. On any
     * mid-batch exception the whole Transfer aborts, so batch-buddies never
     * get an attempt — they'd otherwise silently climb errors → 3 → retired,
     * despite never having been given a clean try. When the exception names
     * a specific file AND the failure is one that won't improve on retry
     * (NoSuchKey, AccessDenied, etc.), retire just THAT file immediately by
     * bumping its errors to 3, so the next batch's `WHERE errors < 3` select
     * excludes it and the healthy buddies get a fresh attempt.
     */
    private function handle_transfer_exception( $wpdb, \Exception $e, &$errors ) {
        $this->sync_debug_log( "Transfer sync exception: " . $e->getMessage() );
        // Also emit to the standard PHP error log unconditionally — the
        // sync_debug_log call above is a no-op unless INFINITE_UPLOADS_API_DEBUG
        // is defined, so on production sites there was no diagnosable trail
        // when a transfer aborted (users just saw "Too many server errors").
        $this->log_transfer_failure( 'Transfer', $e );

        if ( method_exists( $e, 'getRequest' ) ) {
            $file = str_replace(
                    trailingslashit( $this->iup_instance->bucket ),
                    '',
                    $e->getRequest()->getRequestTarget()
            );

            if ( InfiniteUploadsHelper::is_permanent_aws_failure( $e ) ) {
                $wpdb->update(
                        "{$wpdb->base_prefix}infinite_uploads_files",
                        [ 'errors' => 3 ],
                        [ 'file' => $file ],
                        [ '%d' ],
                        [ '%s' ]
                );
                $this->sync_debug_log( sprintf(
                        'Retired %s as permanent failure (%s)',
                        $file,
                        $e->getAwsErrorCode()
                ) );
            }

            $error_count = (int) $wpdb->get_var(
                    $wpdb->prepare(
                            "SELECT errors FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE file = %s",
                            $file
                    )
            );

            $message = $error_count >= 3
                    ? sprintf( esc_html__( 'Error uploading %s. Retries exceeded.', 'infinite-uploads' ), esc_html( $file ) )
                    : sprintf( esc_html__( 'Error uploading %s. Queued for retry.', 'infinite-uploads' ), esc_html( $file ) );

            $errors[] = $message;
        } else {
            $errors[] = esc_html__( 'Error uploading file. Queued for retry.', 'infinite-uploads' );
        }
    }

    /**
     * Handle multipart exception with proper cleanup. Retires the file
     * permanently when the underlying AWS error is one that won't recover on
     * retry — see handle_transfer_exception() for the reasoning.
     */
    private function handle_multipart_exception( $wpdb, $to_sync, \Exception $e, &$errors ) {
        $this->sync_debug_log( "Multipart upload exception: " . $e->getMessage() );
        $this->log_transfer_failure( 'Multipart', $e, isset( $to_sync->file ) ? $to_sync->file : null );

        if ( InfiniteUploadsHelper::is_permanent_aws_failure( $e ) ) {
            $wpdb->update(
                    "{$wpdb->base_prefix}infinite_uploads_files",
                    [ 'errors' => 3 ],
                    [ 'file' => $to_sync->file ],
                    [ '%d' ],
                    [ '%s' ]
            );
            $to_sync->errors = 3;
            $this->sync_debug_log( sprintf(
                    'Retired %s as permanent multipart failure (%s)',
                    $to_sync->file,
                    $e->getAwsErrorCode()
            ) );
        }

        $message = $to_sync->errors >= 3
                ? sprintf( esc_html__( 'Error uploading %s. Retries exceeded.', 'infinite-uploads' ), esc_html( $to_sync->file ) )
                : sprintf( esc_html__( 'Error uploading %s. Queued for retry.', 'infinite-uploads' ), esc_html( $to_sync->file ) );

        $errors[] = $message;
    }

    public function ajax_delete_old() {
        global $wpdb;

        if ( ! current_user_can( $this->iup_instance->capability ) || ! wp_verify_nonce( $_POST['nonce'], 'iup_delete' ) ) {
            wp_send_json_error( esc_html__( 'Permissions Error: Please refresh the page and try again.', 'infinite-uploads' ) );
        }

        $deleted = 0;
        $errors  = [];
        $path    = $this->iup_instance->get_original_upload_dir_root();
        $break   = false;
        // SQL-side carve-outs: BB cache images (regeneration churn) and user-excluded
        // paths (rewriter serves the LOCAL URL, so removing the local copy would 404
        // the media). Applied at SELECT level so the loop progresses and the count
        // matches the actual delete targets. See
        // InfiniteUploadsHelper::deletable_files_where_carveout().
        $carve_out_sql = InfiniteUploadsHelper::deletable_files_where_carveout();
        while ( ! $break ) {
            $to_delete = $wpdb->get_col( "SELECT file FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE synced = 1 AND deleted = 0{$carve_out_sql} LIMIT 500" );
            foreach ( $to_delete as $file ) {
                @unlink( $path['basedir'] . $file );
                $wpdb->update( "{$wpdb->base_prefix}infinite_uploads_files", [ 'deleted' => 1 ], [ 'file' => $file ] );
                $deleted ++;
            }

            $is_done = ! (bool) $wpdb->get_var( "SELECT count(*) FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE synced = 1 AND deleted = 0{$carve_out_sql}" );
            if ( $is_done || timer_stop() >= $this->ajax_timelimit ) {
                $break = true;
                wp_send_json_success( array_merge( compact( 'deleted', 'is_done', 'errors' ), $this->iup_instance->get_sync_stats() ) );
            }
        }
    }

    public function ajax_delete() {
        global $wpdb;

        // SECURITY: Enhanced permission and nonce checks
        if ( ! current_user_can( $this->iup_instance->capability ) ) {
            wp_send_json_error(
                    esc_html__( 'Permissions Error: Insufficient privileges.', 'infinite-uploads' ),
                    403
            );
        }

        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'iup_delete' ) ) {
            wp_send_json_error(
                    esc_html__( 'Security Error: Invalid nonce. Please refresh and try again.', 'infinite-uploads' ),
                    403
            );
        }

        // SECURITY: Additional AJAX referer check
        check_ajax_referer( 'iup_delete', 'nonce' );

        // SECURITY: Validate upload directory exists and is writable
        $path = $this->iup_instance->get_original_upload_dir_root();
        if ( empty( $path['basedir'] ) || ! is_dir( $path['basedir'] ) ) {
            wp_send_json_error(
                    esc_html__( 'Error: Invalid upload directory.', 'infinite-uploads' ),
                    500
            );
        }

        // Initialize counters
        $deleted    = 0;
        $errors     = [];
        $break      = false;
        $batch_size = 500;

        // PERFORMANCE: Set time limit if not already set
        if ( ! isset( $this->ajax_timelimit ) ) {
            $max_execution_time   = (int) ini_get( 'max_execution_time' );
            $this->ajax_timelimit = max( 20, floor( $max_execution_time * 0.6666 ) );
        }

        // Match the ajax_delete_old carve-outs so BB cache images (churn) and
        // user-excluded paths (rewriter serves the LOCAL URL — deleting the
        // local copy would 404 the media) stay on disk. See
        // InfiniteUploadsHelper::deletable_files_where_carveout().
        $carve_out_sql = InfiniteUploadsHelper::deletable_files_where_carveout();

        while ( ! $break ) {
            // PERFORMANCE: Optimized query with proper preparation
            $to_delete = $wpdb->get_results(
                    $wpdb->prepare(
                            "SELECT file FROM `{$wpdb->base_prefix}infinite_uploads_files`
                WHERE synced = 1 AND deleted = 0{$carve_out_sql}
                LIMIT %d",
                            $batch_size
                    ),
                    ARRAY_A
            );

            if ( empty( $to_delete ) ) {
                $break   = true;
                $is_done = true;
            } else {
                // PERFORMANCE: Process batch with transaction-like behavior
                $this->process_delete_batch( $wpdb, $to_delete, $path, $deleted, $errors );

                // PERFORMANCE: More efficient count check
                $remaining = (int) $wpdb->get_var(
                        "SELECT COUNT(*) FROM `{$wpdb->base_prefix}infinite_uploads_files`
                WHERE synced = 1 AND deleted = 0{$carve_out_sql}"
                );

                $is_done = ( $remaining === 0 );

                // Check if we should break the loop
                if ( $is_done || timer_stop() >= $this->ajax_timelimit ) {
                    $break = true;
                }
            }
        }

        // SECURITY: Regenerate nonce for next request
        $nonce = wp_create_nonce( 'iup_delete' );

        wp_send_json_success(
                array_merge(
                        compact( 'deleted', 'is_done', 'errors', 'nonce' ),
                        $this->iup_instance->get_sync_stats()
                )
        );
    }

    /**
     * PERFORMANCE & SECURITY: Process file deletion in batches
     *
     * @param  wpdb    $wpdb     WordPress database object
     * @param  array   $files    Array of files to delete
     * @param  array   $path     Upload directory path info
     * @param  int    &$deleted  Counter for deleted files (by reference)
     * @param  array  &$errors   Array of error messages (by reference)
     */
    private function process_delete_batch( $wpdb, $files, $path, &$deleted, &$errors ) {
        $base_dir = realpath( $path['basedir'] );
        if ( $base_dir === false ) {
            $errors[] = esc_html__( 'Error: Unable to resolve base directory.', 'infinite-uploads' );

            return;
        }

        $successfully_deleted = [];
        $failed_deletes       = [];

        foreach ( $files as $file_row ) {
            $file = $file_row['file'];

            // SECURITY: Validate file path to prevent directory traversal
            $file_path = $path['basedir'] . $file;
            $real_path = realpath( $file_path );

            // Check if file exists first
            if ( ! file_exists( $file_path ) ) {
                // File doesn't exist, mark as deleted anyway
                $successfully_deleted[] = $file;
                $this->sync_debug_log( "File already deleted or not found: {$file}" );
                continue;
            }

            // SECURITY: Ensure the resolved path is within the base directory
            if ( $real_path === false || strpos( $real_path, $base_dir ) !== 0 ) {
                $errors[] = sprintf(
                        esc_html__( 'Security: Invalid file path detected: %s', 'infinite-uploads' ),
                        esc_html( $file )
                );
                $this->sync_debug_log( "Security: Path traversal attempt detected for file: {$file}" );
                $failed_deletes[] = $file;
                continue;
            }

            // SECURITY: Additional check - ensure it's a file, not a directory
            if ( ! is_file( $real_path ) ) {
                $errors[] = sprintf(
                        esc_html__( 'Error: Not a file: %s', 'infinite-uploads' ),
                        esc_html( $file )
                );
                $this->sync_debug_log( "Attempted to delete non-file: {$file}" );
                $failed_deletes[] = $file;
                continue;
            }

            // SECURITY: Check if file is writable before attempting deletion
            if ( ! is_writable( $real_path ) ) {
                $errors[] = sprintf(
                        esc_html__( 'Error: File not writable: %s', 'infinite-uploads' ),
                        esc_html( $file )
                );
                $this->sync_debug_log( "File not writable: {$file}" );
                $failed_deletes[] = $file;
                continue;
            }

            // Attempt to delete the file
            if ( @unlink( $real_path ) ) {
                $successfully_deleted[] = $file;
                $deleted ++;
                $this->sync_debug_log( "Successfully deleted file: {$file}" );
            } else {
                // SECURITY: Log the error without exposing full path
                $error    = error_get_last();
                $errors[] = sprintf(
                        esc_html__( 'Error deleting file: %s', 'infinite-uploads' ),
                        esc_html( $file )
                );
                $this->sync_debug_log( "Failed to delete file: {$file}. Error: " . ( $error['message'] ?? 'Unknown' ) );
                $failed_deletes[] = $file;
            }

            // PERFORMANCE: Clean up empty parent directories
            $this->cleanup_empty_directories( $real_path, $base_dir );
        }

        // PERFORMANCE: Bulk update database for successfully deleted files
        if ( ! empty( $successfully_deleted ) ) {
            $this->bulk_update_deleted_status( $wpdb, $successfully_deleted, true );
        }

        // PERFORMANCE: Mark failed deletes separately (optional - for retry logic)
        if ( ! empty( $failed_deletes ) ) {
            // You could implement a retry counter here
            // For now, we'll just log them
            $this->sync_debug_log( "Failed to delete " . count( $failed_deletes ) . " files" );
        }
    }

    /**
     * PERFORMANCE: Bulk update deleted status in database
     *
     * @param  wpdb   $wpdb     WordPress database object
     * @param  array  $files    Array of file paths
     * @param  bool   $deleted  Deleted status (1 or 0)
     */
    private function bulk_update_deleted_status( $wpdb, $files, $deleted = true ) {
        if ( empty( $files ) ) {
            return;
        }

        // PERFORMANCE: Use single query with IN clause for bulk update
        $placeholders  = implode( ',', array_fill( 0, count( $files ), '%s' ) );
        $deleted_value = $deleted ? 1 : 0;

        $query = $wpdb->prepare(
                "UPDATE `{$wpdb->base_prefix}infinite_uploads_files` 
        SET deleted = {$deleted_value} 
        WHERE file IN ($placeholders)",
                ...$files
        );

        $result = $wpdb->query( $query );

        if ( $result === false ) {
            $this->sync_debug_log( "Database error during bulk update: " . $wpdb->last_error );
        } else {
            $this->sync_debug_log( "Bulk updated {$result} records as deleted" );
        }
    }

    /**
     * PERFORMANCE: Clean up empty parent directories after file deletion
     *
     * @param  string  $file_path  Full path to the deleted file
     * @param  string  $base_dir   Base directory path (don't delete above this)
     */
    private function cleanup_empty_directories( $file_path, $base_dir ) {
        $dir = dirname( $file_path );

        // SECURITY: Ensure we don't go above base directory
        if ( strpos( $dir, $base_dir ) !== 0 || $dir === $base_dir ) {
            return;
        }

        // Check if directory is empty
        $files = @scandir( $dir );
        if ( $files === false ) {
            return;
        }

        // Remove . and .. from the list
        $files = array_diff( $files, [ '.', '..' ] );

        // If directory is empty, try to remove it
        if ( empty( $files ) ) {
            if ( @rmdir( $dir ) ) {
                $this->sync_debug_log( "Cleaned up empty directory: {$dir}" );

                // PERFORMANCE: Recursively clean up parent directories
                $this->cleanup_empty_directories( $dir, $base_dir );
            }
        }
    }


    public function ajax_download_old() {
        global $wpdb;

        if ( ! current_user_can( $this->iup_instance->capability ) || ! wp_verify_nonce( $_POST['nonce'], 'iup_download' ) ) {
            wp_send_json_error( esc_html__( 'Permissions Error: Please refresh the page and try again.', 'infinite-uploads' ) );
        }

        $progress = get_site_option( 'iup_files_scanned' );
        if ( empty( $progress['download_started'] ) ) {
            $progress['download_started'] = time();
            update_site_option( 'iup_files_scanned', $progress );
        }


        $downloaded = 0;
        $errors     = [];
        $break      = false;
        $path       = $this->iup_instance->get_original_upload_dir_root();
        $s3         = $this->iup_instance->s3();

        while ( ! $break ) {
            $to_sync = $wpdb->get_results( $wpdb->prepare( "SELECT file, size FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE synced = 1 AND deleted = 1 AND errors < 3 ORDER BY errors ASC, file ASC LIMIT %d", INFINITE_UPLOADS_SYNC_PER_LOOP ) );
            //build full paths
            $to_sync_full = [];
            $to_sync_size = 0;
            $to_sync_sql  = [];
            foreach ( $to_sync as $file ) {
                $to_sync_size += $file->size;
                if ( count( $to_sync_full ) && $to_sync_size > INFINITE_UPLOADS_SYNC_MAX_BYTES ) { //upload at minimum one file even if it's huuuge
                    break;
                }
                $to_sync_full[] = 's3://' . untrailingslashit( $this->iup_instance->bucket ) . $file->file;
                $to_sync_sql[]  = esc_sql( $file->file );
            }
            //preset the error count in case request times out. Successful sync will clear error count.
            $wpdb->query( "UPDATE `{$wpdb->base_prefix}infinite_uploads_files` SET errors = ( errors + 1 ) WHERE file IN ('" . implode( "','", $to_sync_sql ) . "')" );

            $obj  = new \ArrayObject( $to_sync_full );
            $from = $obj->getIterator();

            $transfer_args = [
                    'concurrency' => INFINITE_UPLOADS_SYNC_CONCURRENCY,
                    'base_dir'    => 's3://' . $this->iup_instance->bucket,
                    'before'      => function ( Command $command ) use ( $wpdb, &$downloaded ) {//add middleware to intercept result of each file upload
                        if ( in_array( $command->getName(), [ 'GetObject' ], true ) ) {
                            $command->getHandlerList()->appendSign(
                                    Middleware::mapResult( function ( ResultInterface $result ) use ( $wpdb, &$downloaded ) {
                                        $downloaded ++;
                                        $file = $this->iup_instance->get_file_from_result( $result );
                                        $wpdb->update( "{$wpdb->base_prefix}infinite_uploads_files", [
                                                'deleted' => 0,
                                                'errors'  => 0,
                                        ], [ 'file' => $file ] );

                                        return $result;
                                    } )
                            );
                        }
                    },
            ];
            try {
                $manager = new Transfer( $s3, $from, $path['basedir'], $transfer_args );
                $manager->transfer();
            } catch ( \Exception $e ) {
                if ( method_exists( $e, 'getRequest' ) ) {
                    $file        = str_replace( untrailingslashit( $path['basedir'] ), '', str_replace( trailingslashit( $this->iup_instance->bucket ), '', $e->getRequest()->getRequestTarget() ) );
                    $error_count = $wpdb->get_var( $wpdb->prepare( "SELECT errors FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE file = %s", $file ) );
                    if ( $error_count >= 3 ) {
                        $errors[] = sprintf( esc_html__( 'Error downloading %s. Retries exceeded.', 'infinite-uploads' ), $file );
                    } else {
                        $errors[] = sprintf( esc_html__( 'Error downloading %s. Queued for retry.', 'infinite-uploads' ), $file );
                    }
                } else {
                    $errors[] = esc_html__( 'Error downloading file. Queued for retry.', 'infinite-uploads' );
                }
            }

            $is_done = ! (bool) $wpdb->get_var( "SELECT count(*) FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE synced = 1 AND deleted = 1 AND errors < 3" );
            if ( $is_done || timer_stop() >= $this->ajax_timelimit ) {
                $break = true;

                if ( $is_done ) {
                    $progress                      = get_site_option( 'iup_files_scanned' );
                    $progress['download_finished'] = time();
                    update_site_option( 'iup_files_scanned', $progress );

                    $this->api->disconnect();
                }

                $nonce = wp_create_nonce( 'iup_download' );
                wp_send_json_success( array_merge( compact( 'downloaded', 'is_done', 'errors', 'nonce' ), $this->iup_instance->get_sync_stats() ) );
            }
        }
    }

    public function ajax_download() {
        global $wpdb;

        // SECURITY: Enhanced permission and nonce checks
        if ( ! current_user_can( $this->iup_instance->capability ) ) {
            wp_send_json_error(
                    esc_html__( 'Permissions Error: Insufficient privileges.', 'infinite-uploads' ),
                    403
            );
        }

        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'iup_download' ) ) {
            wp_send_json_error(
                    esc_html__( 'Security Error: Invalid nonce. Please refresh and try again.', 'infinite-uploads' ),
                    403
            );
        }

        // SECURITY: Additional AJAX referer check
        check_ajax_referer( 'iup_download', 'nonce' );

        // PERFORMANCE: Use transients for frequently updated data
        $progress = get_transient( 'iup_files_download_progress' );
        if ( false === $progress ) {
            $progress = get_site_option( 'iup_files_scanned', [] );
        }

        if ( empty( $progress['download_started'] ) ) {
            $progress['download_started'] = time();
            update_site_option( 'iup_files_scanned', $progress );
            set_transient( 'iup_files_download_progress', $progress, HOUR_IN_SECONDS );
        }

        // SECURITY: Validate paths and S3 instance
        $path = $this->iup_instance->get_original_upload_dir_root();
        if ( empty( $path['basedir'] ) || ! is_dir( $path['basedir'] ) ) {
            wp_send_json_error(
                    esc_html__( 'Error: Invalid upload directory.', 'infinite-uploads' ),
                    500
            );
        }

        // SECURITY: Check if directory is writable
        if ( ! is_writable( $path['basedir'] ) ) {
            wp_send_json_error(
                    esc_html__( 'Error: Upload directory is not writable.', 'infinite-uploads' ),
                    500
            );
        }

        // Cap the per-request budget under common gateway timeouts — see
        // ajax_sync() for the full rationale. Same 45s ceiling, same
        // filter override.
        if ( ! isset( $this->ajax_timelimit ) ) {
            $default_timelimit    = min( 45, max( 20, (int) floor( ini_get( 'max_execution_time' ) * 0.6666 ) ) );
            $this->ajax_timelimit = (int) apply_filters( 'infinite_uploads_ajax_timelimit', $default_timelimit );
        }
        $this->sync_debug_log( "Ajax download time limit: {$this->ajax_timelimit}" );

        $downloaded = 0;
        $errors     = [];
        $s3         = $this->iup_instance->s3();

        $bucket = $this->iup_instance->bucket;
        if ( empty( $bucket ) ) {
            wp_send_json_error(
                    esc_html__( 'Error: Invalid S3 bucket configuration.', 'infinite-uploads' ),
                    500
            );
        }

        // Continuous Generator, mirroring ajax_sync's upload path — the
        // Transfer manager pulls new files as soon as any completes, so the
        // concurrency pool never drains to zero at a batch barrier.
        $iterator = $this->build_download_iterator( $wpdb, $bucket, $path, $this->ajax_timelimit, $errors );

        try {
            $transfer_args = [
                    'concurrency' => INFINITE_UPLOADS_SYNC_CONCURRENCY,
                    'base_dir'    => 's3://' . $bucket,
                    'before'      => $this->create_download_middleware( $wpdb, $downloaded ),
            ];
            $manager       = new Transfer( $s3, $iterator, $path['basedir'], $transfer_args );
            $manager->transfer();
        } catch ( \Exception $e ) {
            $this->handle_download_exception( $wpdb, $e, $path, $bucket, $errors );
        }

        // Determine whether the whole queue drained.
        $remaining = (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM `{$wpdb->base_prefix}infinite_uploads_files`
                  WHERE synced = 1 AND deleted = 1 AND errors < 3"
        );
        $is_done = ( 0 === $remaining );

        if ( $is_done ) {
            $progress                      = get_site_option( 'iup_files_scanned', [] );
            $progress['download_finished'] = time();
            update_site_option( 'iup_files_scanned', $progress );
            delete_transient( 'iup_files_download_progress' );

            if ( isset( $this->api ) && is_object( $this->api ) ) {
                $this->api->disconnect();
            }
        }

        // SECURITY: Regenerate nonce for next request
        $nonce = wp_create_nonce( 'iup_download' );

        wp_send_json_success(
                array_merge(
                        compact( 'downloaded', 'is_done', 'errors', 'nonce' ),
                        $this->iup_instance->get_sync_stats()
                )
        );
    }

    /**
     * Continuous Generator that feeds the Transfer manager one s3:// URI at
     * a time, drawn from the DB on demand. Handles path validation and
     * parent-directory creation before each yield. Halts when the deadline
     * elapses or the eligible queue is empty. See build_sync_upload_iterator
     * for the mirror-image upload version — the two share a shape but not
     * a signature (URI form, WHERE clause, and pre-increment target differ).
     *
     * @param  \wpdb   $wpdb
     * @param  string  $bucket
     * @param  array   $path      From get_original_upload_dir_root().
     * @param  int     $deadline  Seconds budget (compared against timer_stop()).
     * @param  array  &$errors    Accumulator for per-file security/dir errors.
     *
     * @return \Generator<string>
     */
    private function build_download_iterator( $wpdb, $bucket, $path, $deadline, &$errors ) {
        $base_real = realpath( $path['basedir'] );
        if ( false === $base_real ) {
            $errors[] = esc_html__( 'Error: Unable to resolve base directory.', 'infinite-uploads' );

            return;
        }
        $bucket_naked    = untrailingslashit( $bucket );
        // Bounded rolling window — see build_sync_upload_iterator() for the
        // rationale. Same shape as the upload path.
        $inflight_window = [];

        while ( true ) {
            if ( timer_stop() >= $deadline ) {
                return;
            }

            $exclude_sql  = '';
            $prepare_args = [];

            if ( ! empty( $inflight_window ) ) {
                $placeholders = implode( ',', array_fill( 0, count( $inflight_window ), '%s' ) );
                $exclude_sql  = "AND file NOT IN ($placeholders)";
                $prepare_args = array_values( $inflight_window );
            }
            $prepare_args[] = INFINITE_UPLOADS_SYNC_ITERATOR_PAGE_SIZE;

            $rows = $wpdb->get_results(
                    $wpdb->prepare(
                            "SELECT file, size FROM `{$wpdb->base_prefix}infinite_uploads_files`
                              WHERE synced = 1 AND deleted = 1 AND errors < 3 $exclude_sql
                              ORDER BY errors ASC, file ASC
                              LIMIT %d",
                            ...$prepare_args
                    )
            );

            if ( empty( $rows ) ) {
                return;
            }

            foreach ( $rows as $row ) {
                if ( timer_stop() >= $deadline ) {
                    return;
                }

                // Ensure parent directory exists (Transfer manager writes
                // into path['basedir'] . $row->file, so its parent must be
                // present or writable).
                $destination = $path['basedir'] . $row->file;
                $parent      = dirname( $destination );
                if ( ! $this->create_directory_recursive( $parent, $base_real ) ) {
                    $this->sync_debug_log( "Failed to create directory for: {$row->file}" );
                    $errors[] = sprintf(
                            esc_html__( 'Error: Cannot create directory for %s', 'infinite-uploads' ),
                            esc_html( $row->file )
                    );
                    $this->push_inflight_window( $inflight_window, $row->file );
                    continue;
                }

                // SECURITY: Ensure resolved parent is under base.
                $real_parent = realpath( $parent );
                if ( false === $real_parent || 0 !== strpos( $real_parent, $base_real ) ) {
                    $this->sync_debug_log( "Security: Invalid destination for file: {$row->file}" );
                    $errors[] = sprintf(
                            esc_html__( 'Security: Invalid file path: %s', 'infinite-uploads' ),
                            esc_html( $row->file )
                    );
                    $this->push_inflight_window( $inflight_window, $row->file );
                    continue;
                }

                // Pre-increment the error count so an interrupted download
                // counts toward retry-retirement. Middleware clears on success.
                $wpdb->query(
                        $wpdb->prepare(
                                "UPDATE `{$wpdb->base_prefix}infinite_uploads_files`
                                    SET errors = ( errors + 1 )
                                  WHERE file = %s",
                                $row->file
                        )
                );

                $this->push_inflight_window( $inflight_window, $row->file );
                yield 's3://' . $bucket_naked . $row->file;
            }
        }
    }

    /**
     * PERFORMANCE: Create download middleware for transfer
     *
     * @param  wpdb  $wpdb        WordPress database object
     * @param  int  &$downloaded  Counter for downloaded files (by reference)
     *
     * @return callable Middleware function
     */
    private function create_download_middleware( $wpdb, &$downloaded ) {
        return function ( Command $command ) use ( $wpdb, &$downloaded ) {
            if ( $command->getName() === 'GetObject' ) {
                $this->sync_debug_log( "Downloading key: {$command['Key']}" );

                $command->getHandlerList()->appendSign(
                        Middleware::mapResult( function ( ResultInterface $result ) use ( $wpdb, &$downloaded, $command ) {
                            $this->sync_debug_log( "Finished downloading: {$command['Key']}" );
                            $downloaded ++;

                            $file = $this->iup_instance->get_file_from_result( $result );

                            // PERFORMANCE: Single update query
                            $wpdb->update(
                                    "{$wpdb->base_prefix}infinite_uploads_files",
                                    [
                                            'deleted' => 0,
                                            'errors'  => 0,
                                    ],
                                    [ 'file' => $file ],
                                    [ '%d', '%d' ],
                                    [ '%s' ]
                            );

                            return $result;
                        } )
                );
            }
        };
    }

    /**
     * SECURITY: Create directory recursively with validation
     *
     * @param  string  $directory  Directory path to create
     * @param  string  $base_dir   Base directory (don't create above this)
     *
     * @return bool True on success, false on failure
     */
    private function create_directory_recursive( $directory, $base_dir ) {
        // SECURITY: Ensure we're not trying to create directories above base
        $real_dir = realpath( $directory );
        if ( $real_dir !== false && strpos( $real_dir, $base_dir ) !== 0 ) {
            $this->sync_debug_log( "Security: Attempted to create directory outside base: {$directory}" );

            return false;
        }

        // Check if directory already exists
        if ( is_dir( $directory ) ) {
            return is_writable( $directory );
        }

        // Create directory with proper permissions
        if ( ! @mkdir( $directory, 0755, true ) ) {
            $error = error_get_last();
            $this->sync_debug_log( "Failed to create directory: {$directory}. Error: " . ( $error['message'] ?? 'Unknown' ) );

            return false;
        }

        $this->sync_debug_log( "Created directory: {$directory}" );

        return true;
    }

    /**
     * SECURITY: Handle download exceptions with proper error messages
     *
     * @param  wpdb       $wpdb    WordPress database object
     * @param  Exception  $e       The exception
     * @param  array      $path    Upload directory path info
     * @param  string     $bucket  S3 bucket name
     * @param  array     &$errors  Array of error messages (by reference)
     */
    private function handle_download_exception( $wpdb, \Exception $e, $path, $bucket, &$errors ) {
        $this->sync_debug_log( "Download exception: " . $e->getMessage() );
        $this->log_transfer_failure( 'Download', $e );

        if ( method_exists( $e, 'getRequest' ) ) {
            // Extract file path from request
            $request_target = $e->getRequest()->getRequestTarget();
            $file           = str_replace(
                    untrailingslashit( $path['basedir'] ),
                    '',
                    str_replace( trailingslashit( $bucket ), '', $request_target )
            );

            // SECURITY: Sanitize file name for output
            $file = ltrim( $file, '/' );

            // Retire the file permanently when the underlying AWS error won't
            // improve on retry (e.g. NoSuchKey — S3 doesn't have it, so no
            // amount of retrying will produce it). Otherwise the file stays
            // eligible for the next batch, poisoning batch-buddies every
            // time. See handle_transfer_exception() for the full rationale.
            if ( InfiniteUploadsHelper::is_permanent_aws_failure( $e ) ) {
                $wpdb->update(
                        "{$wpdb->base_prefix}infinite_uploads_files",
                        [ 'errors' => 3 ],
                        [ 'file' => $file ],
                        [ '%d' ],
                        [ '%s' ]
                );
                $this->sync_debug_log( sprintf(
                        'Retired %s as permanent download failure (%s)',
                        $file,
                        $e->getAwsErrorCode()
                ) );
            }

            $error_count = (int) $wpdb->get_var(
                    $wpdb->prepare(
                            "SELECT errors FROM `{$wpdb->base_prefix}infinite_uploads_files`
                WHERE file = %s",
                            $file
                    )
            );

            $message = $error_count >= 3
                    ? sprintf(
                            esc_html__( 'Error downloading %s. Retries exceeded.', 'infinite-uploads' ),
                            esc_html( $file )
                    )
                    : sprintf(
                            esc_html__( 'Error downloading %s. Queued for retry.', 'infinite-uploads' ),
                            esc_html( $file )
                    );

            $errors[] = $message;
        } else {
            $errors[] = esc_html__( 'Error downloading file. Queued for retry.', 'infinite-uploads' );
        }
    }

    /**
     * Enable or disable url rewriting
     */
    public function ajax_toggle() {
        check_ajax_referer( 'iup_toggle', 'nonce' );

        if ( ! current_user_can( $this->iup_instance->capability ) ) {
            wp_send_json_error( esc_html__( 'Permissions Error: Please refresh the page and try again.', 'infinite-uploads' ) );
        }

        $enabled = (bool) $_REQUEST['enabled'];
        $this->iup_instance->toggle_cloud( $enabled );

        wp_send_json_success();
    }

    /**
     * Identical to WP core size_format() function except it returns "0 GB" instead of false on failure.
     *
     * @param  int|string  $bytes     Number of bytes. Note max integer size for integers.
     * @param  int         $decimals  Optional. Precision of number of decimal places. Default 0.
     *
     * @return string Number string on success.
     */
    function size_format_zero( $bytes, $decimals = 0 ) {
        if ( $bytes > 0 ) {
            return size_format( $bytes, $decimals );
        } else {
            return '0 GB';
        }
    }

    /**
     * Adds settings links to plugin row.
     */
    function plugins_list_links( $actions ) {
        // Build and escape the URL.
        $url = esc_url( $this->settings_url() );

        // Create the link.
        $custom_links = [];
        if ( $this->api->has_token() ) {
            $custom_links['settings'] = "<a href='$url'>" . esc_html__( 'Settings', 'infinite-uploads' ) . '</a>';
        } else {
            $custom_links['connect'] = "<a href='$url' style='color: #EE7C1E;'>" . esc_html__( 'Connect', 'infinite-uploads' ) . '</a>';
        }
        $custom_links['support'] = '<a href="' . esc_url( $this->api_url( '/support/?utm_source=iup_plugin&utm_medium=plugin&utm_campaign=iup_plugin&utm_term=support&utm_content=meta' ) ) . '">' . esc_html__( 'Support', 'infinite-uploads' ) . '</a>';

        // Replace deactivate link if they haven't disconnected yet.
        if ( array_key_exists( 'deactivate', $actions ) ) {
            if ( $this->api->has_token() && $this->api->get_site_data() ) {
                $actions['deactivate'] = sprintf(
                        '<a href="%s" aria-label="%s">%s</a>',
                        $url . "&deactivate-notice=1",
                        /* translators: %s: Plugin name. */
                        esc_attr( sprintf( _x( 'Deactivate %s', 'plugin' ), __( 'Infinite Uploads', 'infinite-uploads' ) ) ),
                        __( 'Deactivate' )
                );
            }
        }

        // Adds the links to the beginning of the array.
        return array_merge( $custom_links, $actions );
    }

    /**
     * Get the settings url with optional url args.
     *
     * @param  array  $args  Optional. Same as for add_query_arg()
     *
     * @return string Unescaped url to settings page.
     */
    function settings_url( $args = [] ) {
        if ( is_multisite() ) {
            $base = network_admin_url( 'admin.php?page=infinite_uploads' );
        } else {
            $base = admin_url( 'admin.php?page=infinite_uploads' );
        }

        return add_query_arg( $args, $base );
    }

    /**
     * Get a url to the public Infinite Uploads site.
     *
     * @param  string  $path  Optional path on the site.
     *
     * @return Infinite_Uploads_Api_Handler|string
     */
    function api_url( $path = '' ) {
        $url = trailingslashit( $this->api->server_root );

        if ( $path && is_string( $path ) ) {
            $url .= ltrim( $path, '/' );
        }

        return $url;
    }

    /**
     * Registers a new settings page under Settings.
     */
    function admin_menu() {
        $page = add_menu_page(
                __( 'Infinite Uploads', 'infinite-uploads' ),
                __( 'Infinite Uploads', 'infinite-uploads' ),
                $this->iup_instance->capability,
                'infinite_uploads',
                [
                        $this,
                        'settings_page',
                ],
                plugins_url( 'assets/img/iu-logo-blue-sm.svg', __FILE__ )
        );

        add_action( 'admin_print_scripts-' . $page, [ &$this, 'admin_scripts' ] );
        add_action( 'admin_print_styles-' . $page, [ &$this, 'admin_styles' ] );
    }

    /**
     *
     */
    function admin_scripts() {
        wp_enqueue_script( 'iup-bootstrap', plugins_url( 'assets/bootstrap/js/bootstrap.bundle.min.js', __FILE__ ), [ 'jquery' ], INFINITE_UPLOADS_VERSION );
        wp_enqueue_script( 'iup-chartjs', plugins_url( 'assets/js/Chart.min.js', __FILE__ ), [], INFINITE_UPLOADS_VERSION );
        wp_enqueue_script( 'iup-js', plugins_url( 'assets/js/infinite-uploads.js', __FILE__ ), [ 'wp-color-picker' ], INFINITE_UPLOADS_VERSION );

        wp_enqueue_script(
                'jstree',
                plugins_url( 'assets/jstree/jstree.min.js', __FILE__ ),
                [ 'jquery' ],
                INFINITE_UPLOADS_VERSION,
                true
        );
        wp_enqueue_style(
                'jstree-style',
                plugins_url( 'assets/jstree/themes/default/style.min.css', __FILE__ ),
                [],
                INFINITE_UPLOADS_VERSION
        );

        $data            = [];
        $data['strings'] = [
                'leave_confirm'      => esc_html__( 'Are you sure you want to leave this tab? The current bulk action will be canceled and you will need to continue where it left off later.', 'infinite-uploads' ),
                'ajax_error'         => esc_html__( 'Too many server errors. Please try again.', 'infinite-uploads' ),
                'leave_confirmation' => esc_html__( 'If you leave this page the sync will be interrupted and you will have to continue where you left off later.', 'infinite-uploads' ),
        ];

        $data['local_types'] = $this->iup_instance->get_filetypes( true );

        $api_data = $this->api->get_site_data();
        if ( $this->api->has_token() && $api_data ) {
            $data['cloud_types'] = $this->iup_instance->get_filetypes( true, $api_data->stats->site->types );
        }

        $data['nonce'] = [
                'scan'              => wp_create_nonce( 'iup_scan' ),
                'sync'              => wp_create_nonce( 'iup_sync' ),
                'delete'            => wp_create_nonce( 'iup_delete' ),
                'download'          => wp_create_nonce( 'iup_download' ),
                'toggle'            => wp_create_nonce( 'iup_toggle' ),
                'video'             => wp_create_nonce( 'iup_video' ),
                'saveExcludedFiles'    => wp_create_nonce( 'iu_excluded_files_nonce' ),
                'getTree'              => wp_create_nonce( 'get_tree_nonce' ),
                'saveMediaFolders'     => wp_create_nonce( 'iu_media_folders_nonce' ),
                'saveImageOptimization' => wp_create_nonce( 'iu_image_optimization_nonce' ),
                'purgeCdn'              => wp_create_nonce( 'iu_purge_cdn_nonce' ),
        ];

        $data['excludedFiles'] = get_site_option( 'iup_excluded_files', '' );

        wp_localize_script( 'iup-js', 'iup_data', $data );
    }

    /**
     * Disable the bulk Deactivate button from Plugins list.
     */
    function block_bulk_deactivate( $plugin ) {
        if ( ( ( isset( $_POST['action'] ) && 'deactivate-selected' === $_POST['action'] ) || ( isset( $_POST['action2'] ) && 'deactivate-selected' === $_POST['action2'] ) ) && 'infinite-uploads/infinite-uploads.php' === $plugin ) {
            if ( $this->api->has_token() && $this->api->get_site_data() ) {
                wp_redirect( $this->settings_url( [ 'deactivate-notice' => 1 ] ) );
                exit;
            }
        }
    }

    /**
     *
     */
    function admin_styles() {
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style( 'iup-bootstrap', plugins_url( 'assets/bootstrap/css/bootstrap.min.css', __FILE__ ), false, INFINITE_UPLOADS_VERSION );
        wp_enqueue_style( 'iup-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), [ 'iup-bootstrap' ], INFINITE_UPLOADS_VERSION );

        //hide all admin notices from another source on these pages
        //remove_all_actions( 'admin_notices' );
        //remove_all_actions( 'network_admin_notices' );
        //remove_all_actions( 'all_admin_notices' );
    }

    /**
     * Checks for temp_token in url and processes auth if present.
     */
    function intercept_auth() {
        if ( ! current_user_can( $this->iup_instance->capability ) ) {
            wp_die( esc_html__( 'Permissions Error: Please refresh the page and try again.', 'infinite-uploads' ) );
        }

        if ( ! empty( $_GET['temp_token'] ) ) {
            $result = $this->api->authorize( $_GET['temp_token'] );
            if ( ! $result ) {
                $this->auth_error = $this->api->api_error;
            } else {
                // Restore folder structure from backup if available.
                if ( MediaFolders::has_backup() ) {
                    MediaFolders::restore_from_backup();
                }
                wp_safe_redirect( $this->settings_url() );
            }
        }

        if ( isset( $_GET['clear'] ) ) {
            delete_site_option( 'iup_files_scanned' );
            wp_safe_redirect( $this->settings_url() );
        }

        if ( isset( $_GET['refresh'] ) ) {
            $this->api->get_site_data( true );
            wp_safe_redirect( $this->settings_url() );
        }

        if ( isset( $_GET['reinstall'] ) ) {
            infinite_uploads_install();
            wp_safe_redirect( $this->settings_url() );
        }
    }

    /**
     * Settings page display callback.
     */
    function settings_page() {
        global $wpdb;

        $region_labels = [
                'US' => esc_html__( 'United States', 'infinite-uploads' ),
                'EU' => esc_html__( 'Europe', 'infinite-uploads' ),
        ];

        $stats    = $this->iup_instance->get_sync_stats();
        $api_data = $this->api->get_site_data();
        ?>
        <div id="iup-settings-page" class="wrap iup-background">

            <h1>
                <img src="<?php
                echo esc_url( plugins_url( '/assets/img/iu-logo-words.svg', __FILE__ ) ); ?>"
                     alt="Infinite Uploads Logo" height="50" width="200"/>
            </h1>

            <?php
            if ( $this->auth_error ) { ?>
                <div class="alert alert-danger mt-1 alert-dismissible fade show" role="alert">
                    <?php
                    echo esc_html( $this->auth_error ); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php
            } ?>

            <div id="iup-error" class="alert alert-danger mt-1" role="alert"></div>

            <?php
            if ( isset( $api_data->site ) && ! $api_data->site->cdn_enabled ) { ?>
                <div class="alert alert-warning mt-1" role="alert">
                    <?php
                    printf( __( "Files can't be uploaded and your CDN is disabled due to a billing issue with your Infinite Uploads account. Please <a href='%s' class='alert-link'>visit your account page</a> to fix, or disconnect this site from the cloud. Images and links to media on your site may be broken until you take action. <a href='%s' class='alert-link' data-toggle='tooltip' title='Refresh account data'>Already fixed?</a>", 'infinite-uploads' ), esc_url( $this->api_url( '/account/billing/?utm_source=iup_plugin&utm_medium=plugin&utm_campaign=iup_plugin' ) ), esc_url( $this->settings_url( [ 'refresh' => 1 ] ) ) ); ?>
                </div>
                <?php
            } elseif ( isset( $api_data->site ) && ! $api_data->site->upload_writeable ) { ?>
                <div class="alert alert-warning mt-1" role="alert">
                    <?php
                    printf( __( "Files can't be uploaded and your CDN will be disabled soon due to a billing issue with your Infinite Uploads account. Please <a href='%s' class='alert-link'>visit your account page</a> to fix, or disconnect this site from the cloud. <a href='%s' class='alert-link' data-toggle='tooltip' title='Refresh account data'>Already fixed?</a>", 'infinite-uploads' ), esc_url( $this->api_url( '/account/billing/?utm_source=iup_plugin&utm_medium=plugin&utm_campaign=iup_plugin' ) ), esc_url( $this->settings_url( [ 'refresh' => 1 ] ) ) ); ?>
                </div>
                <?php
            } ?>

            <?php
            if ( isset( $_GET['deactivate-notice'] ) && $this->api->has_token() && $api_data ) { ?>
                <div class="alert alert-warning mt-1" role="alert">
                    <div class="row align-items-center">
                        <div class="col-md col-12 mb-md-0 mb-2">
                            <?php
                            _e( "There is uploaded media from your site that may only exist in the Infinite Uploads cloud. <strong>You MUST download your media files before deactivating this plugin to prevent data loss!</strong>", 'infinite-uploads' ); ?>
                        </div>
                        <div class="col-sm-4 col-lg-3 text-md-right">
                            <button class="btn text-nowrap btn-info" data-toggle="modal"
                                    data-target="#scan-remote-modal" data-next="download"><?php
                                esc_html_e( 'Download & Disconnect', 'infinite-uploads' ); ?></button>
                        </div>
                    </div>
                </div>
                <?php
            } ?>

            <?php
            if ( $this->api->has_token() && $api_data ) {
                if ( ! $api_data->stats->site->files ) {
                    $synced           = $wpdb->get_row( "SELECT count(*) AS files, SUM(`size`) as size FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE synced = 1" );
                    $cloud_size       = $synced->size;
                    $cloud_files      = $synced->files;
                    $cloud_total_size = $api_data->stats->cloud->storage + $synced->size;
                } else {
                    $cloud_size       = $api_data->stats->site->storage;
                    $cloud_files      = $api_data->stats->site->files;
                    $cloud_total_size = $api_data->stats->cloud->storage;
                }

                $file_exclusion_setting = InfiniteUploadsHelper::get_file_exclusion_setting();

                // Get folder count for media folders toggle warning.
                $iu_folders_table = $wpdb->prefix . 'infinite_uploads_media_folders';
                $iu_folder_count  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$iu_folders_table}`" );

                require_once( dirname( __FILE__ ) . '/templates/header-columns.php' );

                if ( ! infinite_uploads_enabled() ) {
                    require_once( dirname( __FILE__ ) . '/templates/modal-scan.php' );
                    if ( isset( $api_data->site ) && $api_data->site->upload_writeable ) {
                        require_once( dirname( __FILE__ ) . '/templates/modal-upload.php' );
                        require_once( dirname( __FILE__ ) . '/templates/modal-enable.php' );
                    }
                }

                require_once( dirname( __FILE__ ) . '/templates/settings.php' );

                require_once( dirname( __FILE__ ) . '/templates/modal-remote-scan.php' );
                require_once( dirname( __FILE__ ) . '/templates/modal-delete.php' );
                require_once( dirname( __FILE__ ) . '/templates/modal-download.php' );

            } else {
                if ( ! empty( $stats['files_finished'] ) && $stats['files_finished'] >= ( time() - DAY_IN_SECONDS ) ) {
                    $to_sync = $wpdb->get_row( "SELECT count(*) AS files, SUM(`size`) as size FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE deleted = 0" );
                    require_once( dirname( __FILE__ ) . '/templates/connect.php' );
                } else {
                    //Make sure table is installed so we can show an error if not.
                    if ( ! $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->base_prefix}infinite_uploads_files'" ) ) {
                        infinite_uploads_install();
                        if ( ! $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->base_prefix}infinite_uploads_files'" ) ) {
                            require_once( dirname( __FILE__ ) . '/templates/install-error.php' );
                        } else {
                            require_once( dirname( __FILE__ ) . '/templates/welcome.php' );
                        }
                    } else {
                        require_once( dirname( __FILE__ ) . '/templates/welcome.php' );
                    }
                }
                require_once( dirname( __FILE__ ) . '/templates/modal-scan.php' );
            }
            ?>
        </div>
        <?php
        require_once( dirname( __FILE__ ) . '/templates/footer.php' );
    }

    /**
     * Prepares a directory tree for jstree from a given directory.
     *
     * @param  string  $dir            The directory to scan.
     * @param  array   $preselected    Array of preselected paths.
     * @param  array   $virtual_paths  Array of virtual paths to inject.
     * @param  string  $root_dir       The root directory for virtual path injection.
     *
     * @return array The prepared directory tree.
     */
    public function prepare_directory_tree( $dir, $preselected = [], $virtual_paths = [], $root_dir = null ) {
        $result = [];

        // Set root dir on first call.
        if ( $root_dir === null ) {
            $root_dir = $dir;
        }

        $preselected_map = array_flip( $preselected );

        // Compute relative prefix for this $dir relative to $root_dir.
        $rel_prefix = '';
        if ( $dir !== $root_dir ) {
            $rel_prefix = ltrim( substr( $dir, strlen( $root_dir ) ), DIRECTORY_SEPARATOR );
        }

        // Track existing names at this level to avoid duplicates with virtual paths.
        $existing_names = [];

        // Scan real filesystem — single level only (no recursion).
        try {
            $iterator = new \FilesystemIterator( $dir, \FilesystemIterator::SKIP_DOTS );
        } catch ( \UnexpectedValueException $e ) {
            // Directory doesn't exist (could be a virtual-only path); continue to inject virtuals below.
            $iterator = [];
        }

        foreach ( $iterator as $file_info ) {
            $path   = $file_info->getPathname();
            $is_dir = $file_info->isDir();

            $node = [
                    "text"  => $file_info->getFilename(),
                    "icon"  => $is_dir ? "jstree-folder" : "jstree-file",
                    "data"  => [ "path" => $path ],
                    "state" => [
                            "opened"   => false,
                            "selected" => isset( $preselected_map[ $path ] ),
                    ],
            ];

            if ( $is_dir ) {
                // Lazy-load marker: jstree will fire a new AJAX request on expand.
                $node["children"] = true;

                // If this directory is not itself selected but has excluded descendants,
                // mark it as undetermined so the checkbox shows the partial-selection state
                // even before the user opens the node (lazy-loaded children).
                if ( ! isset( $preselected_map[ $path ] ) ) {
                    $dir_prefix = $path . DIRECTORY_SEPARATOR;
                    foreach ( $preselected as $excluded_path ) {
                        if ( strpos( $excluded_path, $dir_prefix ) === 0 ) {
                            $node["state"]["undetermined"] = true;
                            break;
                        }
                    }
                }
            }

            $existing_names[ $file_info->getFilename() ] = true;
            $result[] = $node;
        }

        // Inject virtual directory children at this level.
        $injected_names = [];
        foreach ( $virtual_paths as $virtual ) {
            $virtual = trim( $virtual, DIRECTORY_SEPARATOR );
            if ( $virtual === '' ) {
                continue;
            }

            // For root level ($rel_prefix is ''), the virtual path itself is what we examine.
            // For deeper levels, the virtual path must start with $rel_prefix/.
            if ( $rel_prefix !== '' ) {
                if ( strpos( $virtual, $rel_prefix . DIRECTORY_SEPARATOR ) !== 0 && $virtual !== $rel_prefix ) {
                    continue;
                }
                $remaining = substr( $virtual, strlen( $rel_prefix ) + 1 );
            } else {
                $remaining = $virtual;
            }

            if ( $remaining === '' || $remaining === false ) {
                continue;
            }

            // Take only the first path segment (direct child at this level).
            $segments   = explode( DIRECTORY_SEPARATOR, $remaining );
            $child_name = $segments[0];

            // Skip if already exists on disk or already injected.
            if ( isset( $existing_names[ $child_name ] ) || isset( $injected_names[ $child_name ] ) ) {
                continue;
            }

            $child_path = $dir . DIRECTORY_SEPARATOR . $child_name;
            $has_deeper = count( $segments ) > 1;

            // A virtual node is definitively a folder when the relative virtual path has
            // deeper segments below it. is_dir() alone isn't safe — after "Free Up Local
            // Storage" wipes local copies, the intermediate directory may no longer exist
            // on disk even though it's logically a folder in the cloud filelist.
            $is_path_dir = $has_deeper || is_dir( $child_path );
            $new_node = [
                    "text"     => $child_name,
                    "icon"     => $is_path_dir ? "jstree-folder" : "jstree-file",
                    "data"     => [ "path" => $child_path ],
                    "state"    => [
                            "opened"   => false,
                            "selected" => isset( $preselected_map[ $child_path ] ),
                    ],
                    "children" => $has_deeper ? true : false,
            ];

            // Mark virtual directories with excluded descendants as undetermined.
            if ( $has_deeper && ! isset( $preselected_map[ $child_path ] ) ) {
                $dir_prefix = $child_path . DIRECTORY_SEPARATOR;
                foreach ( $preselected as $excluded_path ) {
                    if ( strpos( $excluded_path, $dir_prefix ) === 0 ) {
                        $new_node["state"]["undetermined"] = true;
                        break;
                    }
                }
            }

            $injected_names[ $child_name ] = true;
            $result[] = $new_node;
        }

        // FilesystemIterator returns entries in filesystem order, and virtual
        // paths are appended after them, so the tree reads as unordered. Sort
        // directories first, then files, each alphabetically.
        usort( $result, function ( $a, $b ) {
            // Use the icon rather than "children": a virtual directory with no
            // deeper virtual segments is still a directory but carries children = false.
            $a_is_dir = ( "jstree-folder" === $a["icon"] );
            $b_is_dir = ( "jstree-folder" === $b["icon"] );

            if ( $a_is_dir !== $b_is_dir ) {
                return $a_is_dir ? -1 : 1;
            }

            return strnatcasecmp( $a["text"], $b["text"] );
        } );

        return $result;
    }

    /**
     * Get synced files from database.
     *
     * @return array
     */
    public function get_synced_files() {
        global $wpdb;

        $synced_files = $wpdb->get_col( "SELECT DISTINCT file FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE synced = 1" );

        return $synced_files;
    }

    /**
     * AJAX handler to get directory tree
     */
    public function get_direcotry_tree() {
        // Verify nonce.
        check_ajax_referer( 'get_tree_nonce', 'nonce' );

        $dir = $this->iup_instance->get_original_upload_dir();

        $excluded_files = $this->get_excluded_files();
        // Get the existing excluded files from options
        $upload_dir = $dir['basedir'];

        // Determine which directory to scan (supports lazy loading of subdirectories).
        $scan_dir = $upload_dir;
        if ( ! empty( $_REQUEST['dir'] ) ) {
            $requested_dir = sanitize_text_field( wp_unslash( $_REQUEST['dir'] ) );

            // Security: reject path traversal attempts.
            if ( strpos( $requested_dir, '..' ) !== false ) {
                wp_send_json_error( 'Invalid directory path.' );
            }

            $requested_dir   = rtrim( $requested_dir, DIRECTORY_SEPARATOR . '/' );
            $real_upload_dir = realpath( $upload_dir );
            if ( $real_upload_dir === false ) {
                wp_send_json_error( 'Uploads directory is invalid.' );
            }

            // If the requested path exists on disk, resolve via realpath (canonical, handles
            // symlinks). If it doesn't exist — e.g. a virtual folder whose local copy was
            // wiped by "Free Up Local Storage" — validate lexically against the uploads
            // basedir so the user can still expand it and see its cloud-only children.
            $real_requested = realpath( $requested_dir );
            if ( $real_requested !== false ) {
                if ( strpos( $real_requested, $real_upload_dir ) !== 0 ) {
                    wp_send_json_error( 'Directory is outside the uploads folder.' );
                }
                $scan_dir = $real_requested;
            } else {
                if ( strpos( $requested_dir, $real_upload_dir ) !== 0 ) {
                    wp_send_json_error( 'Directory is outside the uploads folder.' );
                }
                // prepare_directory_tree() already handles missing dirs and falls through
                // to virtual-path injection.
                $scan_dir = $requested_dir;
            }
        }

        $sub_dir       = $dir['subdir'];
        $virtual_paths = [ $sub_dir ];

        // Get synced files to include as virtual paths.
        $synced_files = $this->get_synced_files();

        if ( ! empty( $synced_files ) ) {
            $virtual_paths = array_merge( $virtual_paths, $synced_files );
        }

        $tree = $this->prepare_directory_tree( $scan_dir, $excluded_files, $virtual_paths, $upload_dir );

        // jstree's core.data AJAX loader expects a raw JSON array — not WordPress's
        // {"success":true,"data":[...]} envelope — so use wp_send_json() directly.
        wp_send_json( $tree );

    }

    /**
     * Get excluded files from options.
     *
     * @return array
     */
    public function get_excluded_files() {
        $excluded_files = get_site_option( 'iup_excluded_files', '' );
        if ( ! is_array( $excluded_files ) ) {
            $excluded_files = [];
        }

        return $excluded_files;
    }

    /**
     * Save excluded files from settings page.
     *
     * @return void
     */
    public function infinite_uploads_save_excluded_files() {
        // Verify nonce.
        check_ajax_referer( 'iu_excluded_files_nonce', 'nonce' );

        // Check user capabilities.
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Insufficient permissions' );
        }

        $raw_excluded                   = isset( $_POST['excluded_files'] ) ? (array) $_POST['excluded_files'] : [];
        $current_file_exclusion_setting = isset( $_POST['enabled_excluded_files'] ) ? $_POST['enabled_excluded_files'] : 'no';

        // Sanitize each path fragment: strip whitespace, remove traversal sequences,
        // and reject entries that resolve outside the uploads directory.
        $upload_basedir       = $this->iup_instance->get_original_upload_dir()['basedir'];
        $real_upload_basedir  = realpath( $upload_basedir ) ?: $upload_basedir;
        $excluded_files_array = [];
        foreach ( $raw_excluded as $entry ) {
            $entry = sanitize_text_field( wp_unslash( $entry ) );
            // Reject entries that contain path traversal sequences.
            if ( strpos( $entry, '..' ) !== false ) {
                continue;
            }
            // Reject absolute paths that escape the uploads directory.
            if ( path_is_absolute( $entry ) ) {
                $real_entry = realpath( $entry );
                if ( $real_entry === false || strpos( $real_entry, $real_upload_basedir ) !== 0 ) {
                    continue;
                }
            }
            $excluded_files_array[] = $entry;
        }

        $previous_file_exclusion_setting = InfiniteUploadsHelper::get_file_exclusion_setting();

        /**
         * Possible values for $previous_file_exclusion_setting and $file_exclusion_setting:
         * 'no'  - File exclusion is disabled.
         * 'yes' - File exclusion is enabled.
         *
         * Possible transitions:
         *
         * 1. 'no'  -> 'no'  : No change, file exclusion remains disabled.
         * 2. 'no'  -> 'yes' : File exclusion is being enabled.
         * 3. 'yes' -> 'no'  : File exclusion is being disabled.
         * 4. 'yes' -> 'yes' : No change, file exclusion remains enabled.
         *
         * We need to handle cases 2 and 3 specifically.
         *
         * Case 2 ('no' -> 'yes'): We simply enable file exclusion, no special action needed. Any files already in the excluded list will be respected.
         * Case 3 ('yes' -> 'no'): We need to clear the excluded files list since file exclusion is being disabled. And also resync any files that were previously excluded.
         *
         * Case 1 and 4: No action needed, just update the setting.
         */
        $current_excluded_files_array                  = $this->get_excluded_files();
        $files_to_resync                               = array_diff( $current_excluded_files_array, $excluded_files_array );
        $files_to_download_from_infinite_upload_server = array_diff( $excluded_files_array, $current_excluded_files_array );

        update_site_option( 'iup_do_sync_complete', 'yes' );
        update_site_option( 'iup_do_download_complete', 'yes' );
        if ( 'no' === $previous_file_exclusion_setting && 'yes' === $current_file_exclusion_setting ) {
            // Case 2: File exclusion is being enabled.
            // No special action needed, just update the setting below.
            $this->process_added_removed_excluded_files( $files_to_resync, $files_to_download_from_infinite_upload_server );

        } elseif ( 'yes' === $previous_file_exclusion_setting && 'no' === $current_file_exclusion_setting ) {
            // Case 3: File exclusion is being disabled.
            // Clear the excluded files list.
            $excluded_files_array = [];
            $this->process_added_removed_excluded_files( $files_to_resync, [] );
        } elseif ( 'no' === $current_file_exclusion_setting && 'no' === $previous_file_exclusion_setting ) {
            $excluded_files_array = [];
        } elseif ( 'yes' === $current_file_exclusion_setting && 'yes' === $previous_file_exclusion_setting ) {
            $this->process_added_removed_excluded_files( $files_to_resync, $files_to_download_from_infinite_upload_server );
        }

        update_site_option( 'iup_excluded_files', $excluded_files_array );

        InfiniteUploadsHelper::set_file_exclusion_setting( $current_file_exclusion_setting );

        wp_send_json_success();
    }

    /**
     * Save media folders enabled/disabled setting.
     *
     * @return void
     */
    public function save_media_folders_setting() {
        check_ajax_referer( 'iu_media_folders_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Insufficient permissions' );
        }

        $value = isset( $_POST['enabled'] ) ? sanitize_text_field( $_POST['enabled'] ) : 'yes';
        InfiniteUploadsHelper::set_media_folders_setting( $value );

        wp_send_json_success();
    }

    /**
     * AJAX: persist the per-site image optimization settings, then best-effort push them
     * to the IU API so the IU edge fleet can apply them. The local option is the source
     * of truth for the UI; the API push is additive and tolerated to fail until the
     * server-side endpoint ships (see IMAGE-OPTIMIZATION-API-SPEC.md).
     */
    public function save_image_optimization_setting() {
        check_ajax_referer( 'iu_image_optimization_nonce', 'nonce' );

        if ( ! current_user_can( $this->iup_instance->capability ) ) {
            wp_send_json_error( 'Insufficient permissions' );
        }

        if ( ! $this->api->has_token() ) {
            wp_send_json_error( 'Not connected' );
        }

        // Business-tier feature. The API enforces this authoritatively; refuse here too so
        // a non-Business site never stores settings the edge would ignore anyway.
        if ( ! $this->api->is_business_plan() ) {
            wp_send_json_error( 'Image optimization is available on the Business plan.' );
        }

        $settings = [
            'enabled'        => isset( $_POST['enabled'] ) ? sanitize_text_field( wp_unslash( $_POST['enabled'] ) ) : 'no',
            'level'          => isset( $_POST['level'] ) ? sanitize_text_field( wp_unslash( $_POST['level'] ) ) : 'balanced',
            'avif'           => isset( $_POST['avif'] ) ? sanitize_text_field( wp_unslash( $_POST['avif'] ) ) : 'no',
            'webp'           => isset( $_POST['webp'] ) ? sanitize_text_field( wp_unslash( $_POST['webp'] ) ) : 'no',
            'max_width'      => isset( $_POST['max_width'] ) ? (int) $_POST['max_width'] : 2560,
            'strip_metadata' => isset( $_POST['strip_metadata'] ) ? sanitize_text_field( wp_unslash( $_POST['strip_metadata'] ) ) : 'no',
            'exclusions'     => isset( $_POST['exclusions'] ) ? sanitize_textarea_field( wp_unslash( $_POST['exclusions'] ) ) : '',
        ];

        // Persist locally (authoritative for the UI). Helper re-sanitizes and clamps.
        InfiniteUploadsHelper::set_image_optimization_settings( $settings );
        $saved = InfiniteUploadsHelper::get_image_optimization_settings();

        // Best-effort sync to the API/edge. Safe no-op until the endpoint exists.
        $pushed = $this->api->push_optimization_settings( $saved );

        wp_send_json_success( [
            'settings' => $saved,
            'synced'   => (bool) $pushed,
        ] );
    }

    /**
     * AJAX handler to purge the entire CDN cache for this site.
     *
     * Sends a wildcard purge through the IU API so changed settings (like image
     * optimization) apply to files the CDN has already cached. Rate limited to
     * once per hour: a full purge makes the edge re-fetch every file, so
     * back-to-back purges only slow the site down for no benefit.
     */
    public function purge_cdn_cache() {
        check_ajax_referer( 'iu_purge_cdn_nonce', 'nonce' );

        if ( ! current_user_can( $this->iup_instance->capability ) ) {
            wp_send_json_error( esc_html__( 'Insufficient permissions', 'infinite-uploads' ) );
        }

        if ( ! $this->api->has_token() ) {
            wp_send_json_error( esc_html__( 'Not connected', 'infinite-uploads' ) );
        }

        // Business-tier feature, and only useful once image optimization is on.
        if ( ! $this->api->is_business_plan() || ! InfiniteUploadsHelper::is_image_optimization_enabled() ) {
            wp_send_json_error( esc_html__( 'CDN purge is available on the Business plan with Image Optimization enabled.', 'infinite-uploads' ) );
        }

        if ( get_site_transient( 'iup_purge_cdn_cooldown' ) ) {
            wp_send_json_error( esc_html__( 'The CDN cache was purged recently. Please wait up to an hour before purging again.', 'infinite-uploads' ) );
        }

        $cdn_url = InfiniteUploadsHelper::get_s3_url();
        if ( ! $cdn_url ) {
            wp_send_json_error( esc_html__( 'Unable to determine the CDN address for this site.', 'infinite-uploads' ) );
        }

        $this->api->purge( [ trailingslashit( $cdn_url ) . '*' ] );

        set_site_transient( 'iup_purge_cdn_cooldown', time(), HOUR_IN_SECONDS );

        wp_send_json_success( [
            'message' => esc_html__( 'Purge requested. The CDN cache will rebuild automatically as files are visited.', 'infinite-uploads' ),
        ] );
    }

    public function process_added_removed_excluded_files( $files_to_resync, $files_to_download_from_infinite_upload_server ) {
        global $wpdb;

        if ( ! empty( $files_to_resync ) ) {
            update_site_option( 'iup_do_sync_complete', 'no' );
            $path = $this->iup_instance->get_original_upload_dir_root();
            $path = $path['basedir'];

            $filelist = new InfiniteUploadsFilelist( $path, 20, $files_to_resync );
            $filelist->add_files_to_sync();

            as_schedule_single_action( time(), 'infinite-uploads-do-sync' );
        }

        if ( ! empty( $files_to_download_from_infinite_upload_server ) ) {
            $files_to_download = get_site_option( 'iup_files_to_downloads', '' );
            update_site_option( 'iup_do_download_complete', 'no' );

            if ( ! is_array( $files_to_download ) ) {
                $files_to_download = [];
            }

            $files_to_download = array_merge( $files_to_download, $files_to_download_from_infinite_upload_server );
            $files_to_download = array_unique( $files_to_download );

            update_site_option( 'iup_files_to_downloads', $files_to_download );
            as_schedule_single_action( time(), 'infinite-uploads-add-files-to-download' );
        }
    }

    /**
     * Check if path is a directory.
     *
     * @param $path
     *
     * @return bool
     */
    public function is_dir( $path ) {
        $path = rtrim( $path ); // trim spaces

        // If it ends with a slash → directory
        if ( str_ends_with( $path, '/' ) || substr( $path, - 1 ) === DIRECTORY_SEPARATOR ) {
            return true;
        }

        // Get last part of the path
        $basename = basename( $path );

        // If it has an extension → file
        if ( pathinfo( $basename, PATHINFO_EXTENSION ) ) {
            return false;
        }

        // Otherwise → assume directory
        return true;
    }

    public function add_files_to_download() {
        global $wpdb;

        $path          = $this->iup_instance->get_original_upload_dir_root();
        $base_dir_path = $path['basedir'];

        $files = get_site_option( 'iup_files_to_downloads', '' );

        if ( empty( $files ) || ! is_array( $files ) ) {
            return false;
        }
        $dirs_to_download = [];

        foreach ( $files as $key => $file ) {
            if ( $this->is_dir( $file ) ) {
                $file                      = '/' . ltrim( trim( $file, $base_dir_path ), '/' );
                $dirs_to_download[ $file ] = 1;
                unset( $files[ $key ] );
                continue;
            }

            if ( file_exists( $file ) ) {
                unset( $files[ $key ] );
                continue;
            }

            $file = '/' . ltrim( trim( $file, $base_dir_path ), '/' );

            $wpdb->query( $wpdb->prepare( "INSERT INTO `{$wpdb->base_prefix}infinite_uploads_files` (file, size, synced, deleted, errors) VALUES (%s, 0, 1, 1, 1) ON DUPLICATE KEY UPDATE deleted = 1, errors = 1", $file ) );
        }

        if ( ! empty( $dirs_to_download ) ) {
            update_site_option( 'iup_dirs_to_downloads', $dirs_to_download );
            as_schedule_single_action( time(), 'infinite-uploads-fetch-s3-files-from-directory-to-download' );
        }

        update_site_option( 'iup_files_to_downloads', $files );
        as_schedule_single_action( time(), 'infinite-uploads-do-download' );

        return true;
    }

    public function fetch_s3_files_from_directory_to_download() {
        global $wpdb;

        $dirs = get_site_option( 'iup_dirs_to_downloads', '' );

        $this->sync_debug_log( '[INFINITE_UPLOADS] Fetch S3 files from directory to download' );
        $this->sync_debug_log( '[INFINITE_UPLOADS] Fetch S3 files from directory to download >> Step 1' );
        $this->sync_debug_log( "Dirs to download >>>>> " . print_r( $dirs, true ) );

        if ( empty( $dirs ) ) {
            return;
        }

        $s3     = $this->iup_instance->s3();
        $prefix = $this->iup_instance->get_s3_prefix();

        $args = [
                'Bucket' => $this->iup_instance->get_s3_bucket(),
                'Prefix' => trailingslashit( $prefix ),
        ];

        $next_token = get_site_option( 'iup_s3_next_token_to_download', '' );

        if ( ! empty( $next_token ) ) {
            $args['ContinuationToken'] = sanitize_text_field( $next_token );
        }

        $timelimit = max( 20, floor( ini_get( 'max_execution_time' ) * .6666 ) );
        try {
            $results    = $s3->getPaginator( 'ListObjectsV2', $args );
            $req_count  = $file_count = 0;
            $is_done    = false;
            $next_token = null;
            foreach ( $results as $result ) {
                $is_done    = ! $result['IsTruncated'];
                $next_token = isset( $result['NextContinuationToken'] ) ? $result['NextContinuationToken'] : '';
                update_site_option( 'iup_s3_next_token_to_download', $next_token );
                $cloud_only_files = [];
                if ( $result['Contents'] ) {
                    foreach ( $result['Contents'] as $object ) {
                        $file_count ++;
                        $local_key = str_replace( $prefix, '', $object['Key'] );

                        // Check if the file is in one of the directories to download
                        $in_dir = false;
                        foreach ( $dirs as $dir => $v ) {
                            $position = strpos( $local_key, trailingslashit( ltrim( $dir, '/' ) ) );
                            if ( $position ) {
                                $in_dir = true;
                                break;
                            }
                        }

                        if ( ! $in_dir ) {
                            continue;
                        }

                        $file = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->base_prefix}infinite_uploads_files WHERE file = %s", $local_key ) );


                        if ( $file && ! $file->synced && $file->size == $object['Size'] ) {
                            $this->sync_debug_log( "Already synced file found: $local_key " . size_format( $file->size, 2 ) );
                            $wpdb->update( "{$wpdb->base_prefix}infinite_uploads_files", [
                                    'synced'      => 1,
                                    'transferred' => $file->size,
                            ], [ 'file' => $local_key ] );
                        }

                        if ( ! $file ) {
                            $this->sync_debug_log( "Cloud only file found: $local_key " . size_format( $object['Size'], 2 ) );
                            $cloud_only_files[] = [
                                    'name'  => $local_key,
                                    'size'  => $object['Size'],
                                    'mtime' => strtotime( $object['LastModified']->__toString() ),
                                    'type'  => $this->iup_instance->get_file_type( $local_key ),
                            ];
                        }
                    }
                }

                //flush new files to db
                if ( count( $cloud_only_files ) ) {
                    $values = [];
                    foreach ( $cloud_only_files as $file ) {
                        $values[] = $wpdb->prepare( "(%s,%d,%d,%s,%d,1,1)", $file['name'], $file['size'], $file['mtime'], $file['type'], $file['size'] );
                    }

                    $query = "INSERT INTO {$wpdb->base_prefix}infinite_uploads_files (file, size, modified, type, transferred, synced, deleted) VALUES ";
                    $query .= implode( ",\n", $values );
                    $query .= " ON DUPLICATE KEY UPDATE size = VALUES(size), modified = VALUES(modified), type = VALUES(type), transferred = VALUES(transferred), synced = 1, deleted = 1, errors = 0";
                    $wpdb->query( $query );
                }

                if ( ( $timer = timer_stop() ) >= $timelimit ) {
                    as_schedule_single_action( time(), 'infinite-uploads-fetch-s3-files-from-directory-to-download' );
                    break;
                }
            }

            if ( $is_done ) {
                update_site_option( 'iup_dirs_to_downloads', '' );
                as_schedule_single_action( time(), 'infinite-uploads-do-download' );
            }
        } catch ( \Exception $e ) {
            wp_send_json_error( $e->getMessage() );
        }

    }

    public function do_download() {
        global $wpdb;

        // Bail before touching S3. On a site that has never connected (or has
        // since disconnected), key/secret/region are never populated, and
        // building the S3 client throws an uncaught InvalidArgumentException
        // that kills the whole Action Scheduler run.
        if ( ! $this->api->has_token() || ! $this->api->get_site_data() ) {
            return;
        }

        $downloaded = 0;
        $errors     = [];
        $path       = $this->iup_instance->get_original_upload_dir_root();
        $s3         = $this->iup_instance->s3();
        $bucket     = $this->iup_instance->bucket;

        if ( empty( $bucket ) ) {
            return;
        }

        // Cap the per-request budget under common gateway timeouts — see
        // ajax_sync() for the full rationale.
        $default_timelimit    = min( 45, max( 20, (int) floor( ini_get( 'max_execution_time' ) * 0.6666 ) ) );
        $this->ajax_timelimit = (int) apply_filters( 'infinite_uploads_ajax_timelimit', $default_timelimit );
        $this->sync_debug_log( "Do-download time limit: {$this->ajax_timelimit}" );

        $iterator = $this->build_download_iterator( $wpdb, $bucket, $path, $this->ajax_timelimit, $errors );

        try {
            $transfer_args = [
                    'concurrency' => INFINITE_UPLOADS_SYNC_CONCURRENCY,
                    'base_dir'    => 's3://' . $bucket,
                    'before'      => $this->create_download_middleware( $wpdb, $downloaded ),
            ];
            $manager       = new Transfer( $s3, $iterator, $path['basedir'], $transfer_args );
            $manager->transfer();
        } catch ( \Exception $e ) {
            $this->handle_download_exception( $wpdb, $e, $path, $bucket, $errors );
        }

        $remaining = (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM `{$wpdb->base_prefix}infinite_uploads_files`
                  WHERE synced = 1 AND deleted = 1 AND errors < 3"
        );
        $is_done   = ( 0 === $remaining );

        if ( $is_done ) {
            update_site_option( 'iup_do_download_complete', 'yes', true );
        } else {
            // Still more to do — reschedule for the next cron tick.
            as_schedule_single_action( time(), 'infinite-uploads-do-download' );
        }
    }

    public function do_sync() {
        global $wpdb;

        // Bail before touching S3. do_sync() is hooked to a daily cron event
        // scheduled unconditionally in the constructor (no connection check),
        // so it fires on every site regardless of whether it has ever
        // connected. On a site that has never connected (or has since
        // disconnected), key/secret/region are never populated, and building
        // the S3 client throws an uncaught InvalidArgumentException that
        // kills the whole wp-cron run.
        if ( ! $this->api->has_token() || ! $this->api->get_site_data() ) {
            return;
        }

        // Cap the per-request budget under common gateway timeouts — see
        // ajax_sync() for the full rationale. Same 45s ceiling and filter.
        $default_timelimit    = min( 45, max( 20, (int) floor( ini_get( 'max_execution_time' ) * 0.6666 ) ) );
        $this->ajax_timelimit = (int) apply_filters( 'infinite_uploads_ajax_timelimit', $default_timelimit );
        $this->sync_debug_log( "Do-sync time limit: {$this->ajax_timelimit}" );

        $uploaded = 0;
        $errors   = [];
        $path     = $this->iup_instance->get_original_upload_dir_root();
        $s3       = $this->iup_instance->s3();

        // Phase 1: continuous upload via the shared Generator (mirrors ajax_sync).
        $iterator = $this->build_sync_upload_iterator( $wpdb, $path, $this->ajax_timelimit );

        try {
            $transfer_args = [
                    'concurrency' => INFINITE_UPLOADS_SYNC_CONCURRENCY,
                    'base_dir'    => $path['basedir'],
                    'before'      => $this->create_transfer_middleware( $wpdb, $uploaded, $errors ),
            ];
            $manager       = new Transfer(
                    $s3,
                    $iterator,
                    's3://' . $this->iup_instance->bucket . '/',
                    $transfer_args
            );
            $manager->transfer();
        } catch ( \Exception $e ) {
            $this->handle_transfer_exception( $wpdb, $e, $errors );
        }

        // Phase 2: drain unfinished multiparts one at a time.
        while ( timer_stop() < $this->ajax_timelimit ) {
            $pending = $wpdb->get_row(
                    "SELECT file, size, errors, transfer_status AS upload_id
                       FROM `{$wpdb->base_prefix}infinite_uploads_files`
                      WHERE synced = 0 AND errors < 3 AND transfer_status IS NOT NULL
                      ORDER BY errors ASC, file ASC
                      LIMIT 1"
            );
            if ( ! $pending ) {
                break;
            }
            $this->process_multipart_sync( $wpdb, $pending, $path, $s3, $uploaded, $errors );
        }

        $remaining = (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM `{$wpdb->base_prefix}infinite_uploads_files`
                  WHERE synced = 0 AND errors < 3"
        );
        $is_done   = ( 0 === $remaining );

        if ( $is_done ) {
            update_site_option( 'iup_do_sync_complete', 'yes', true );
        } else {
            // Still more to do — reschedule the cron for the next tick.
            $this->sync_debug_log( "Sync time limit reached, rescheduling." );
            wp_clear_scheduled_hook( 'infinite-uploads-do-sync' );
            as_schedule_single_action( time() + 5, 'infinite-uploads-do-sync' );
        }
    }

    public function remove_downloaded_files_from_list() {
        $files = get_site_option( 'iup_files_to_downloads', '' );

        if ( empty( $files ) || ! is_array( $files ) ) {
            return false;
        }

        $files_to_keep = [];
        foreach ( $files as $file ) {
            $full_path = $this->iup_instance->get_original_upload_dir_root();
            $full_path = $full_path['basedir'] . '/' . ltrim( $file, '/' );
            if ( ! file_exists( $full_path ) ) {
                $files_to_keep[] = $file;
            }
        }

        update_site_option( 'iup_files_to_downloads', $files_to_keep );

        return true;
    }

}
