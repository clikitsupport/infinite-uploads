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
     * @param $url
     *
     * @return array|mixed|string|string[]
     */
    public function serve_media_url( $url ) {
        /**
         * TODO: Implement a code to check following conditions.
         *
         * 1. Check if $url is a local server url or a cloud url.
         * 2. If it is a local server url, check if the file exists on the local server.
         * 3. If the file exists on the local server, return the local server url.
         * 4. If the file does not exist on the local server, check if it exists on the cloud.
         * 5. If it exists on the cloud, return the cloud url.
         * 6. If it does not exist on the cloud, return the local server url (which will result in a 404).
         * 7. If it is a cloud url, check whether this is synced to cloud or not.
         * 8. If it is synced to cloud, return the cloud url.
         * 9. If it is not synced to cloud, check if the file exists on the local server.
         * 10. If the file exists on the local server, return the local server url.
         * 11. If the file does not exist on the local server, return the cloud url (which will result in a 404).
         */

        // If the file is excluded, always return local URL.
        if ( InfiniteUploadsHelper::is_path_excluded( $url, true ) ) {
            $url = InfiniteUploadsHelper::get_local_file_url( $url );
        }

        // Get root directories for comparison
        $root_dirs = $this->iup_instance->get_original_upload_dir_root();
        $base_url  = $root_dirs['baseurl'];
        $base_dir  = $root_dirs['basedir'];
        $cloud_url = untrailingslashit( $this->iup_instance->get_s3_url() );

        $is_local_url = ( strpos( $url, $base_url ) !== false );

        if ( $is_local_url ) {
            $file_path = str_replace( $base_url, $base_dir, $url );

            if ( file_exists( $file_path ) ) {
                return $url;
            }
            $relative_path = str_replace( $base_url, '', $url );
        } else {
            $relative_path = str_replace( $cloud_url, '', $url );
        }

        global $wpdb;
        $is_synced = $wpdb->get_var( $wpdb->prepare(
                "SELECT synced FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE file = %s AND synced = 1",
                $relative_path
        ) );

        if ( $is_local_url ) {
            return $is_synced ? ( $cloud_url . $relative_path ) : $url;
        } else {
            if ( $is_synced ) {
                return $url;
            }

            $local_path = $base_dir . $relative_path;

            return file_exists( $local_path ) ? ( $base_url . $relative_path ) : $url;
        }
    }

    public function set_the_new_file_path( $uploaded, $file, $new_file, $type ) {
        // error_log( 'Set New File Path Called for: >>>> ' . $new_file );

        // Check if the file is excluded
        if ( InfiniteUploadsHelper::is_path_excluded( $new_file ) ) {
            $new_file = InfiniteUploadsHelper::get_local_file_path( $new_file );
        } else {
            $new_file = InfiniteUploadsHelper::get_cloud_file_path( $new_file );
        }

        error_log( '[set_the_new_file_path] New File Path To Move: >>>> ' . $new_file );

        $move_new_file = @move_uploaded_file( $file['tmp_name'], $new_file );

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

    public function ajax_status() {
        // check caps
        if ( ! current_user_can( $this->iup_instance->capability ) ) {
            wp_send_json_error( esc_html__( 'Permissions Error: Please refresh the page and try again.', 'infinite-uploads' ) );
        }

        wp_send_json_success( $this->iup_instance->get_sync_stats() );
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
        //validate path is within uploads dir to prevent path traversal
        if ( isset( $_POST['remaining_dirs'] ) && is_array( $_POST['remaining_dirs'] ) ) {
            foreach ( $_POST['remaining_dirs'] as $dir ) {
                $realpath = realpath( $path . $dir );
                if ( 0 === strpos( $realpath, $path ) ) { //check that parsed path begins with upload dir
                    $remaining_dirs[] = $dir;
                }
            }
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

        $data  = compact( 'this_file_count', 'is_done', 'remaining_dirs', 'nonce' );
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
        } catch ( Exception $e ) {
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
                } catch ( Exception $e ) {
                    $this->sync_debug_log( "Transfer sync exception: " . $e->__toString() );
                    if ( method_exists( $e, 'getRequest' ) ) {
                        $file        = str_replace( trailingslashit( $this->iup_instance->bucket ), '', $e->getRequest()->getRequestTarget() );
                        $error_count = $wpdb->get_var( $wpdb->prepare( "SELECT errors FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE file = %s", $file ) );
                        if ( $error_count >= 3 ) {
                            $errors[] = sprintf( esc_html__( 'Error uploading %s. Retries exceeded.', 'infinite-uploads' ), $file );
                        } else {
                            $errors[] = sprintf( esc_html__( 'Error uploading %s. Queued for retry.', 'infinite-uploads' ), $file );
                        }
                    } else { //I don't know which error case trigger this but it's common
                        $errors[] = esc_html__( 'Error uploading file. Queued for retry.', 'infinite-uploads' );
                    }
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
                                'before_upload' => function ( \ClikIT\Infinite_Uploads\Aws\Command $command ) use ( &$parts_started, $uploaded, $errors ) {
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
                                        'before_upload' => function ( ClikIT\Infinite_Uploads\Aws\Command $command ) use ( $wpdb ) {
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

                    } catch ( Exception $e ) {
                        $this->sync_debug_log( "Get multipart UploadState exception: " . $e->__toString() );
                        if ( ( $to_sync->errors ) >= 3 ) {
                            $errors[] = sprintf( esc_html__( 'Error uploading %s. Retries exceeded.', 'infinite-uploads' ), $to_sync->file );
                        } else {
                            $errors[] = sprintf( esc_html__( 'Error uploading %s. Queued for retry.', 'infinite-uploads' ), $to_sync->file );
                        }
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

        // PERFORMANCE: Optimize time limit calculation
        $max_execution_time   = (int) ini_get( 'max_execution_time' );
        $this->ajax_timelimit = max( 20, floor( $max_execution_time * 0.6666 ) );
        $this->sync_debug_log( "Ajax time limit: {$this->ajax_timelimit}" );

        // Initialize counters
        $uploaded = 0;
        $errors   = [];
        $break    = false;
        $is_done  = false;

        // SECURITY: Validate and sanitize paths
        $path = $this->iup_instance->get_original_upload_dir_root();
        if ( empty( $path['basedir'] ) || ! is_dir( $path['basedir'] ) ) {
            wp_send_json_error( esc_html__( 'Error: Invalid upload directory.', 'infinite-uploads' ), 500 );
        }

        $s3 = $this->iup_instance->s3();

        while ( ! $break ) {
            // PERFORMANCE: Use prepared statement with proper escaping
            $to_sync = $wpdb->get_results(
                    $wpdb->prepare(
                            "SELECT file, size FROM `{$wpdb->base_prefix}infinite_uploads_files` 
                WHERE synced = 0 AND errors < 3 AND transfer_status IS NULL 
                ORDER BY errors ASC, file ASC LIMIT %d",
                            INFINITE_UPLOADS_SYNC_PER_LOOP
                    )
            );

            if ( $to_sync ) {
                $this->process_batch_sync( $wpdb, $to_sync, $path, $s3, $uploaded, $errors );
            } else {
                // Process multipart uploads
                $to_sync = $wpdb->get_row(
                        "SELECT file, size, errors, transfer_status as upload_id 
                FROM `{$wpdb->base_prefix}infinite_uploads_files` 
                WHERE synced = 0 AND errors < 3 AND transfer_status IS NOT NULL 
                ORDER BY errors ASC, file ASC LIMIT 1"
                );

                if ( $to_sync ) {
                    $this->process_multipart_sync( $wpdb, $to_sync, $path, $s3, $uploaded, $errors );
                } else {
                    $is_done = true;
                }
            }

            // Check if we should break the loop
            if ( $is_done || timer_stop() >= $this->ajax_timelimit ) {
                $break            = true;
                $permanent_errors = 0;

                if ( $is_done ) {
                    // PERFORMANCE: More efficient count query
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
        }
    }

    /**
     * PERFORMANCE: Extracted batch sync logic into separate method
     */
    private function process_batch_sync( $wpdb, $to_sync, $path, $s3, &$uploaded, &$errors ) {
        $to_sync_full = [];
        $to_sync_size = 0;
        $to_sync_sql  = [];

        foreach ( $to_sync as $file ) {
            $to_sync_size += $file->size;

            // Upload at minimum one file even if it's huge
            if ( count( $to_sync_full ) && $to_sync_size > INFINITE_UPLOADS_SYNC_MAX_BYTES ) {
                break;
            }

            // SECURITY: Validate file path to prevent directory traversal
            $file_path = $path['basedir'] . $file->file;
            $real_path = realpath( $file_path );

            if ( $real_path === false || strpos( $real_path, realpath( $path['basedir'] ) ) !== 0 ) {
                $this->sync_debug_log( "Security: Invalid file path detected: {$file->file}" );
                continue;
            }

            $to_sync_full[] = $file_path;
            $to_sync_sql[]  = $file->file; // Will be escaped in prepare()
        }

        if ( empty( $to_sync_full ) ) {
            return;
        }

        // PERFORMANCE: Use prepared statement for bulk update
        $placeholders = implode( ',', array_fill( 0, count( $to_sync_sql ), '%s' ) );
        $wpdb->query(
                $wpdb->prepare(
                        "UPDATE `{$wpdb->base_prefix}infinite_uploads_files` 
            SET errors = (errors + 1) 
            WHERE file IN ($placeholders)",
                        ...$to_sync_sql
                )
        );

        $this->sync_debug_log( sprintf(
                "Transfer manager batch size %s, %d files.",
                size_format( $to_sync_size, 2 ),
                count( $to_sync_full )
        ) );

        $concurrency = count( $to_sync_full ) > 1
                ? INFINITE_UPLOADS_SYNC_CONCURRENCY
                : INFINITE_UPLOADS_SYNC_MULTIPART_CONCURRENCY;

        $obj  = new \ArrayObject( $to_sync_full );
        $from = $obj->getIterator();

        $transfer_args = [
                'concurrency' => $concurrency,
                'base_dir'    => $path['basedir'],
                'before'      => $this->create_transfer_middleware( $wpdb, $uploaded, $errors ),
        ];

        try {
            $manager = new Transfer(
                    $s3,
                    $from,
                    's3://' . $this->iup_instance->bucket . '/',
                    $transfer_args
            );
            $manager->transfer();
        } catch ( Exception $e ) {
            $this->handle_transfer_exception( $wpdb, $e, $errors );
        }
    }

    /**
     * PERFORMANCE: Extracted multipart sync logic
     */
    private function process_multipart_sync( $wpdb, $to_sync, $path, $s3, &$uploaded, &$errors ) {
        $this->sync_debug_log( "Continuing multipart upload: {$to_sync->file}" );

        // Preset error count
        $wpdb->query(
                $wpdb->prepare(
                        "UPDATE `{$wpdb->base_prefix}infinite_uploads_files` 
            SET errors = (errors + 1) 
            WHERE file = %s",
                        $to_sync->file
                )
        );
        $to_sync->errors ++;

        // SECURITY: Validate file path
        $source      = $path['basedir'] . $to_sync->file;
        $real_source = realpath( $source );

        if ( $real_source === false || strpos( $real_source, realpath( $path['basedir'] ) ) !== 0 ) {
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
            $progress       = round( ( ( $uploaded_parts * $part_size ) / $to_sync->size ) * 100 );

            $this->sync_debug_log( sprintf(
                    'Uploaded %s%% of file (%d parts, %s each)',
                    $progress,
                    $uploaded_parts,
                    size_format( $part_size )
            ) );

            // PERFORMANCE: Single update query
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
                    'before_upload' => $this->create_multipart_middleware( $wpdb ),
            ] );

            // Handle multipart upload with retry logic
            $result = $this->execute_multipart_upload( $wpdb, $uploader, $s3, $source, $to_sync, $uploaded, $errors );

        } catch ( Exception $e ) {
            $this->handle_multipart_exception( $wpdb, $to_sync, $e, $errors );
        }
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
     * SECURITY: Centralized exception handling with proper error messages
     */
    private function handle_transfer_exception( $wpdb, Exception $e, &$errors ) {
        $this->sync_debug_log( "Transfer sync exception: " . $e->getMessage() );

        if ( method_exists( $e, 'getRequest' ) ) {
            $file = str_replace(
                    trailingslashit( $this->iup_instance->bucket ),
                    '',
                    $e->getRequest()->getRequestTarget()
            );

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
     * Handle multipart exception with proper cleanup
     */
    private function handle_multipart_exception( $wpdb, $to_sync, Exception $e, &$errors ) {
        $this->sync_debug_log( "Multipart upload exception: " . $e->getMessage() );

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
        while ( ! $break ) {
            $to_delete = $wpdb->get_col( "SELECT file FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE synced = 1 AND deleted = 0 LIMIT 500" );
            foreach ( $to_delete as $file ) {
                @unlink( $path['basedir'] . $file );
                $wpdb->update( "{$wpdb->base_prefix}infinite_uploads_files", [ 'deleted' => 1 ], [ 'file' => $file ] );
                $deleted ++;
            }

            $is_done = ! (bool) $wpdb->get_var( "SELECT count(*) FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE synced = 1 AND deleted = 0" );
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

        while ( ! $break ) {
            // PERFORMANCE: Optimized query with proper preparation
            $to_delete = $wpdb->get_results(
                    $wpdb->prepare(
                            "SELECT file FROM `{$wpdb->base_prefix}infinite_uploads_files` 
                WHERE synced = 1 AND deleted = 0 
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
                WHERE synced = 1 AND deleted = 0"
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
            } catch ( Exception $e ) {
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

        // PERFORMANCE: Set time limit if not already set
        if ( ! isset( $this->ajax_timelimit ) ) {
            $max_execution_time   = (int) ini_get( 'max_execution_time' );
            $this->ajax_timelimit = max( 20, floor( $max_execution_time * 0.6666 ) );
        }

        $this->sync_debug_log( "Ajax download time limit: {$this->ajax_timelimit}" );

        $downloaded = 0;
        $errors     = [];
        $break      = false;
        $s3         = $this->iup_instance->s3();

        while ( ! $break ) {
            // PERFORMANCE: Optimized query with proper preparation
            $to_sync = $wpdb->get_results(
                    $wpdb->prepare(
                            "SELECT file, size FROM `{$wpdb->base_prefix}infinite_uploads_files` 
                WHERE synced = 1 AND deleted = 1 AND errors < 3 
                ORDER BY errors ASC, file ASC 
                LIMIT %d",
                            INFINITE_UPLOADS_SYNC_PER_LOOP
                    )
            );

            if ( ! empty( $to_sync ) ) {
                $this->process_download_batch( $wpdb, $to_sync, $path, $s3, $downloaded, $errors );
            }

            // PERFORMANCE: More efficient count check
            $remaining = (int) $wpdb->get_var(
                    "SELECT COUNT(*) FROM `{$wpdb->base_prefix}infinite_uploads_files` 
            WHERE synced = 1 AND deleted = 1 AND errors < 3"
            );

            $is_done = ( $remaining === 0 );

            if ( $is_done || timer_stop() >= $this->ajax_timelimit ) {
                $break = true;

                if ( $is_done ) {
                    $progress                      = get_site_option( 'iup_files_scanned', [] );
                    $progress['download_finished'] = time();
                    update_site_option( 'iup_files_scanned', $progress );
                    delete_transient( 'iup_files_download_progress' );

                    // PERFORMANCE: Only disconnect if API is set
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
        }
    }

    /**
     * PERFORMANCE & SECURITY: Process file downloads in batches
     *
     * @param  wpdb    $wpdb        WordPress database object
     * @param  array   $files       Array of files to download
     * @param  array   $path        Upload directory path info
     * @param  object  $s3          S3 client instance
     * @param  int    &$downloaded  Counter for downloaded files (by reference)
     * @param  array  &$errors      Array of error messages (by reference)
     */
    private function process_download_batch( $wpdb, $files, $path, $s3, &$downloaded, &$errors ) {
        $to_sync_full = [];
        $to_sync_size = 0;
        $to_sync_sql  = [];

        // SECURITY: Validate bucket name
        $bucket = $this->iup_instance->bucket;
        if ( empty( $bucket ) ) {
            $errors[] = esc_html__( 'Error: Invalid S3 bucket configuration.', 'infinite-uploads' );

            return;
        }

        $base_dir = realpath( $path['basedir'] );
        if ( $base_dir === false ) {
            $errors[] = esc_html__( 'Error: Unable to resolve base directory.', 'infinite-uploads' );

            return;
        }

        // Build full paths and validate
        foreach ( $files as $file ) {
            $to_sync_size += $file->size;

            // Download at minimum one file even if it's huge
            if ( count( $to_sync_full ) && $to_sync_size > INFINITE_UPLOADS_SYNC_MAX_BYTES ) {
                break;
            }

            // SECURITY: Validate file path to prevent directory traversal
            $destination_path = $path['basedir'] . $file->file;
            $real_destination = realpath( dirname( $destination_path ) );

            // Check if parent directory exists or can be created
            if ( $real_destination === false ) {
                // Try to create parent directories
                $parent_dir = dirname( $destination_path );
                if ( ! $this->create_directory_recursive( $parent_dir, $base_dir ) ) {
                    $this->sync_debug_log( "Failed to create directory for: {$file->file}" );
                    $errors[] = sprintf(
                            esc_html__( 'Error: Cannot create directory for %s', 'infinite-uploads' ),
                            esc_html( $file->file )
                    );
                    continue;
                }
                $real_destination = realpath( dirname( $destination_path ) );
            }

            // SECURITY: Ensure destination is within base directory
            if ( $real_destination === false || strpos( $real_destination, $base_dir ) !== 0 ) {
                $this->sync_debug_log( "Security: Invalid destination path for file: {$file->file}" );
                $errors[] = sprintf(
                        esc_html__( 'Security: Invalid file path: %s', 'infinite-uploads' ),
                        esc_html( $file->file )
                );
                continue;
            }

            // SECURITY: Sanitize S3 path
            $s3_path        = 's3://' . untrailingslashit( $bucket ) . $file->file;
            $to_sync_full[] = $s3_path;
            $to_sync_sql[]  = $file->file; // Will be escaped in prepare()
        }

        if ( empty( $to_sync_full ) ) {
            $this->sync_debug_log( "No valid files to download in this batch" );

            return;
        }

        // PERFORMANCE: Preset error count using prepared statement
        $placeholders = implode( ',', array_fill( 0, count( $to_sync_sql ), '%s' ) );
        $wpdb->query(
                $wpdb->prepare(
                        "UPDATE `{$wpdb->base_prefix}infinite_uploads_files` 
            SET errors = (errors + 1) 
            WHERE file IN ($placeholders)",
                        ...$to_sync_sql
                )
        );

        $this->sync_debug_log( sprintf(
                "Download batch size %s, %d files.",
                size_format( $to_sync_size, 2 ),
                count( $to_sync_full )
        ) );

        $obj  = new \ArrayObject( $to_sync_full );
        $from = $obj->getIterator();

        $transfer_args = [
                'concurrency' => INFINITE_UPLOADS_SYNC_CONCURRENCY,
                'base_dir'    => 's3://' . $bucket,
                'before'      => $this->create_download_middleware( $wpdb, $downloaded ),
        ];

        try {
            $manager = new Transfer( $s3, $from, $path['basedir'], $transfer_args );
            $manager->transfer();
        } catch ( \Exception $e ) {
            error_log( "Error Downloading Files From IU Server: " . $e->getMessage() );
            $this->handle_download_exception( $wpdb, $e, $path, $bucket, $errors );
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
    private function handle_download_exception( $wpdb, Exception $e, $path, $bucket, &$errors ) {
        $this->sync_debug_log( "Download exception: " . $e->getMessage() );

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
        if ( ! current_user_can( $this->iup_instance->capability ) || ! wp_verify_nonce( $_POST['nonce'], 'iup_toggle' ) ) {
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
                'saveExcludedFiles' => wp_create_nonce( 'iu_excluded_files_nonce' ),
                'getTree'           => wp_create_nonce( 'get_tree_nonce' ),
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

        // Scan real filesystem.
        foreach ( scandir( $dir ) as $file ) {
            if ( $file === '.' || $file === '..' ) {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $file;

            $node = [
                    "text"  => $file,
                    "icon"  => is_dir( $path ) ? "jstree-folder" : "jstree-file",
                    "data"  => [ "path" => $path ],
                    "state" => [
                            "opened"   => false,
                            "selected" => in_array( $path, $preselected, true ),
                    ],
            ];

            if ( is_dir( $path ) ) {
                $node["children"] = $this->prepare_directory_tree( $path, $preselected, $virtual_paths, $root_dir );
            }

            $result[] = $node;
        }

        // Inject virtual directories ONLY at root level.
        if ( $dir === $root_dir ) {
            foreach ( $virtual_paths as $virtual ) {
                $virtual           = trim( $virtual, DIRECTORY_SEPARATOR );
                $full_virtual_path = $root_dir . DIRECTORY_SEPARATOR . $virtual;

                // If it already exists on disk, do nothing.
                if ( is_dir( $full_virtual_path ) ) {
                    continue;
                }

                $parts = explode( DIRECTORY_SEPARATOR, $virtual );

                $current_path = $dir;
                $current      =& $result;

                foreach ( $parts as $part ) {
                    $current_path .= DIRECTORY_SEPARATOR . $part;

                    $found = false;
                    foreach ( $current as &$node ) {
                        if ( $node['text'] === $part ) {
                            $found = true;
                            if ( ! isset( $node['children'] ) ) {
                                $node['children'] = [];
                            }
                            $current =& $node['children'];
                            break;
                        }
                    }

                    if ( ! $found ) {
                        $new_node = [
                                "text"     => $part,
                                "icon"     => "jstree-folder",
                                "data"     => [ "path" => $current_path ],
                                "state"    => [
                                        "opened"   => false,
                                        "selected" => in_array( $current_path, $preselected, true ),
                                ],
                                "children" => [],
                        ];

                        $current[] = $new_node;
                        $current   =& $current[ count( $current ) - 1 ]['children'];
                    }
                }
            }
        }

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

        $sub_dir       = $dir['subdir'];
        $virtual_paths = [ $sub_dir ];

        // Get synced files to include as virtual paths.
        $synced_files = $this->get_synced_files();

        if ( ! empty( $synced_files ) ) {
            $virtual_paths = array_merge( $virtual_paths, $synced_files );
        }

        $tree = $this->prepare_directory_tree( $upload_dir, $excluded_files, $virtual_paths );
        //wp_send_json_success( $tree );

        echo json_encode( $tree );
        die();

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

        $excluded_files_array           = isset( $_POST['excluded_files'] ) ? $_POST['excluded_files'] : [];
        $current_file_exclusion_setting = isset( $_POST['enabled_excluded_files'] ) ? $_POST['enabled_excluded_files'] : 'no';

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

    public function process_added_removed_excluded_files( $files_to_resync, $files_to_download_from_infinite_upload_server ) {
        global $wpdb;

        if ( ! empty( $files_to_resync ) ) {
            error_log( 'There are files to resync' );
            update_site_option( 'iup_do_sync_complete', 'no' );
            $path = $this->iup_instance->get_original_upload_dir_root();
            $path = $path['basedir'];

            $filelist = new InfiniteUploadsFilelist( $path, 20, $files_to_resync );
            $filelist->add_files_to_sync();

            as_schedule_single_action( time(), 'infinite-uploads-do-sync' );
        }

        if ( ! empty( $files_to_download_from_infinite_upload_server ) ) {
            $files_to_download = get_site_option( 'iup_files_to_downloads', '' );
            error_log( 'There are files to download from server' );
            update_site_option( 'iup_do_download_complete', 'no' );

            // error_log( 'Files TO Download Before Merge: ' . print_r( $files_to_download, true ) );
            if ( ! is_array( $files_to_download ) ) {
                $files_to_download = [];
            }

            $files_to_download = array_merge( $files_to_download, $files_to_download_from_infinite_upload_server );

            // error_log( 'Files TO Download After Merge: ' . print_r( $files_to_download, true ) );

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

        error_log( 'Add Files To Download' );
        // error_log( '[INFINITE_UPLOADS] Add files to download' );
        // error_log( '[INFINITE_UPLOADS] Add files to download >> Step 1' );

        $path          = $this->iup_instance->get_original_upload_dir_root();
        $base_dir_path = $path['basedir'];

        $files = get_site_option( 'iup_files_to_downloads', '' );

        error_log('Files To Download >>>> ' . print_r( $files, true ) );

        /// error_log( "Files to Download: " . print_r( $files, true ) );
        if ( empty( $files ) || ! is_array( $files ) ) {
            error_log( "No Files To Download >>>>> " );

            return false;
        }
        $dirs_to_download = [];

        // error_log( "Setup File Downloads >>>>> " );
        foreach ( $files as $key => $file ) {
            error_log("Processing File To Download >>>> " . $file );
            if ( $this->is_dir( $file ) ) {
                error_log("Directory To Download >>>> " . $file );
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

        error_log('Dirs TO Download >>>> ' . print_r( $dirs_to_download, true ) );

        // error_log( "Dirs to download >>>>> " . print_r( $dirs_to_download, true ) );
        // Now process directories
        if ( ! empty( $dirs_to_download ) ) {
            error_log( 'Dirs To Download' );
            update_site_option( 'iup_dirs_to_downloads', $dirs_to_download );
            as_schedule_single_action( time(), 'infinite-uploads-fetch-s3-files-from-directory-to-download' );
        }

        update_site_option( 'iup_files_to_downloads', $files );
        as_schedule_single_action( time(), 'infinite-uploads-do-download' );

        return true;
    }

    public function fetch_s3_files_from_directory_to_download() {
        global $wpdb;

        error_log( 'Fetch S3 Files To Download' );
        $dirs = get_site_option( 'iup_dirs_to_downloads', '' );

        $this->sync_debug_log( '[INFINITE_UPLOADS] Fetch S3 files from directory to download' );
        $this->sync_debug_log( '[INFINITE_UPLOADS] Fetch S3 files from directory to download >> Step 1' );
        $this->sync_debug_log( "Dirs to download >>>>> " . print_r( $dirs, true ) );

        if ( empty( $dirs ) ) {
            // error_log( "No Dirs To Download >>>>> " );

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
            // error_log( 'Start fetching S3 files from directory to download' );
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
                    // error_log( 'Cloud Only Files >>>> ' . print_r( $cloud_only_files, true ) );
                    $values = [];
                    foreach ( $cloud_only_files as $file ) {
                        $values[] = $wpdb->prepare( "(%s,%d,%d,%s,%d,1,1)", $file['name'], $file['size'], $file['mtime'], $file['type'], $file['size'] );
                    }

                    $query = "INSERT INTO {$wpdb->base_prefix}infinite_uploads_files (file, size, modified, type, transferred, synced, deleted) VALUES ";
                    $query .= implode( ",\n", $values );
                    $query .= " ON DUPLICATE KEY UPDATE size = VALUES(size), modified = VALUES(modified), type = VALUES(type), transferred = VALUES(transferred), synced = 1, deleted = 1, errors = 0";
                    $wpdb->query( $query );
                }

                // error_log( 'File Count Processed in this request: ' );

                if ( ( $timer = timer_stop() ) >= $timelimit ) {
                    as_schedule_single_action( time(), 'infinite-uploads-fetch-s3-files-from-directory-to-download' );
                    break;
                }
            }

            if ( $is_done ) {
                update_site_option( 'iup_dirs_to_downloads', '' );
                as_schedule_single_action( time(), 'infinite-uploads-do-download' );
            }
        } catch ( Exception $e ) {
            wp_send_json_error( $e->getMessage() );
        }

    }

    public function do_download() {
        global $wpdb;

        error_log( '[Download] Do Download Started.' );
        $downloaded = 0;
        $errors     = [];
        $break      = false;
        $path       = $this->iup_instance->get_original_upload_dir_root();
        $s3         = $this->iup_instance->s3();
        $is_done    = false;
        $timelimit  = max( 20, floor( ini_get( 'max_execution_time' ) * .6666 ) );
        while ( ! $break ) {
            // Insert/Update query to add files to be downloaded. set deleted = 1 for these files.

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

            // error_log( 'Step 2 - Files to be downloaded in this batch>>>>' . print_r( $to_sync_full, true ) );
            $obj  = new \ArrayObject( $to_sync_full );
            $from = $obj->getIterator();

            $transfer_args = [
                    'concurrency' => INFINITE_UPLOADS_SYNC_CONCURRENCY,
                    'base_dir'    => 's3://' . $this->iup_instance->bucket,
                    'before'      => function ( \ClikIT\Infinite_Uploads\Aws\Command $command ) use ( $wpdb, &$downloaded ) {//add middleware to intercept result of each file upload
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
            } catch ( Exception $e ) {
                if ( method_exists( $e, 'getRequest' ) ) {
                    // error_log( 'Step 3 - Error while downloading files>>>>' . $e->getMessage() );
                    $file = str_replace( untrailingslashit( $path['basedir'] ), '', str_replace( trailingslashit( $this->iup_instance->bucket ), '', $e->getRequest()->getRequestTarget() ) );
                    // TODO: Remove file from the download list if 404.
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

            if ( $is_done ) {
                $break = true;
            }

            if ( timer_stop() >= $timelimit ) {
                as_schedule_single_action( time(), 'infinite-uploads-do-download' );
                $break = true;
            }
        }

        if ( $is_done ) {
            error_log( '[Download] Do Download Completed.' );
            update_site_option( 'iup_do_download_complete', 'yes', true );
        } else {
            error_log( '[Download] Do Download Paused. Will resume shortly.' );
        }
    }

    public function do_sync() {
        global $wpdb;

        error_log( '[Sync] Do Sync Started.' );
        //this loop has a parallel status check, so we make the timeout 2/3 of max execution time.
        $timelimit = max( 20, floor( ini_get( 'max_execution_time' ) * .6666 ) );
        $this->sync_debug_log( "Ajax time limit: " . $timelimit );
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
                        'before'      => function ( \ClikIT\Infinite_Uploads\Aws\Command $command ) use ( $wpdb, &$uploaded, &$errors, &$part_sizes ) {
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
                } catch ( Exception $e ) {
                    $this->sync_debug_log( "Transfer sync exception: " . $e->__toString() );
                    if ( method_exists( $e, 'getRequest' ) ) {
                        $file        = str_replace( trailingslashit( $this->iup_instance->bucket ), '', $e->getRequest()->getRequestTarget() );
                        $error_count = $wpdb->get_var( $wpdb->prepare( "SELECT errors FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE file = %s", $file ) );
                        if ( $error_count >= 3 ) {
                            $errors[] = sprintf( esc_html__( 'Error uploading %s. Retries exceeded.', 'infinite-uploads' ), $file );
                        } else {
                            $errors[] = sprintf( esc_html__( 'Error uploading %s. Queued for retry.', 'infinite-uploads' ), $file );
                        }
                    } else { //I don't know which error case trigger this but it's common
                        $errors[] = esc_html__( 'Error uploading file. Queued for retry.', 'infinite-uploads' );
                    }
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
                                'before_upload' => function ( \ClikIT\Infinite_Uploads\Aws\Command $command ) use ( &$parts_started, $uploaded, $errors ) {
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
                                        'before_upload' => function ( \ClikIT\Infinite_Uploads\Aws\Command $command ) use ( $wpdb ) {
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

                    } catch ( Exception $e ) {
                        $this->sync_debug_log( "Get multipart UploadState exception: " . $e->__toString() );
                        if ( ( $to_sync->errors ) >= 3 ) {
                            $errors[] = sprintf( esc_html__( 'Error uploading %s. Retries exceeded.', 'infinite-uploads' ), $to_sync->file );
                        } else {
                            $errors[] = sprintf( esc_html__( 'Error uploading %s. Queued for retry.', 'infinite-uploads' ), $to_sync->file );
                        }
                    }

                } else {
                    $is_done = true;
                }
            }

            if ( $is_done ) {
                $break = true;
            } elseif ( timer_stop() >= $timelimit ) {
                // Still more to do, but out of time
                $this->sync_debug_log( "Sync time limit reached, exiting and rescheduling." );
                wp_clear_scheduled_hook( 'infinite-uploads-do-sync' );
                as_schedule_single_action( time() + 5, 'infinite-uploads-do-sync' );
                $break = true;
            }
        }

        if ( $is_done ) {
            error_log( '[Sync] All files synced.' );
            update_site_option( 'iup_do_sync_complete', 'yes', true );
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
