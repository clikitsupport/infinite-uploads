<?php

namespace ClikIT\InfiniteUploads;

use ClikIT\Infinite_Uploads\Aws\S3\S3Client;
use ClikIT\Infinite_Uploads\Aws\Multipart\UploadState;
use ClikIT\Infinite_Uploads\Aws\ResultInterface;

class InfiniteUploads {

    private static $instance;
    public $original_upload_dir;
    public $bucket; //includes customer prefix
    public $bucket_url;
    public $capability;
    private $key;
    private $secret;
    private $region;
    private $admin;
    private $api;
    public $stream_api_call_count = [];
    public $stream_plugin_api_call_count = [];
    public $stream_file_cache = [];
    public $stream;
    public $s3;

    public function __construct() {
        /**
         * Filters the capability that is checked for access to Infinite Uploads settings page.
         *
         * @param  {string}  $capability  The capability checked for access and editing settings. Default `manage_network_options` or `manage_options` depending on if multisite.
         *
         * @return {string}  $capability  The capability checked for access and editing settings.
         * @since  1.0
         * @hook   infinite_uploads_settings_capability
         *
         */
        $this->capability = apply_filters( 'infinite_uploads_settings_capability', ( is_multisite() ? 'manage_network_options' : 'manage_options' ) );

        $this->stream_api_call_count = [ 'total' => 0, 'commands' => [] ];

        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'wp_ajax_infinite-uploads-sync-status', [ $this, 'check_sync_status' ] );
    }

    /**
     *
     * @return Infinite_Uploads
     */
    public static function get_instance() {
        if ( ! self::$instance ) {
            self::$instance = new InfiniteUploads();
        }

        return self::$instance;
    }

    /**
     * Creates an UploadState object for a multipart upload by querying
     * for the specified uploads' information. This allows us to continue a
     * multipart upload across multiple requests if we store the UploadId.
     *
     * @param  string  $key        Object key for the multipart upload.
     * @param  string  $upload_id  Upload ID for the multipart upload.
     *
     * @return UploadState
     */
    public function get_multipart_upload_state( $key, $upload_id ) {
        $state = new UploadState( [ 'Bucket' => $this->get_s3_bucket(), 'Key' => $key, 'UploadId' => $upload_id ] );
        foreach ( $this->s3()->getPaginator( 'ListParts', $state->getId() ) as $result ) {
            if ( ! $state->getPartSize() ) {
                $state->setPartSize( $result->search( 'Parts[0].Size' ) );
            }
            foreach ( $result['Parts'] as $part ) {
                $state->markPartAsUploaded( $part['PartNumber'], [
                        'PartNumber' => $part['PartNumber'],
                        'ETag'       => $part['ETag'],
                ] );
            }
        }
        $state->setStatus( UploadState::INITIATED );

        return $state;
    }

    /**
     * Parses filename for the filelist db table from an AWS upload result.
     *
     * @param  ResultInterface  $result  AWS result object.
     *
     * @return string
     */
    public function get_file_from_result( ResultInterface $result ) {
        return '/' . urldecode( strstr( substr( $result['@metadata']["effectiveUri"], ( strrpos( $result['@metadata']["effectiveUri"], $this->bucket ) + strlen( $this->bucket ) ) ), '?', true ) ?: substr( $result['@metadata']["effectiveUri"], ( strrpos( $result['@metadata']["effectiveUri"], $this->bucket ) + strlen( $this->bucket ) ) ) );
    }

    /**
     * Setup the hooks, urls filtering etc for Infinite Uploads
     */
    public function setup() {
        $this->admin  = InfiniteUploadsAdmin::get_instance();
        $this->api    = InfiniteUploadsApiHandler::get_instance();
        $this->stream = InfiniteUploadsVideo::get_instance();

        // Media Folders is independent of cloud sync — runs regardless of connection state.
        if ( InfiniteUploadsHelper::is_media_folders_enabled() ) {
            MediaFolders::get_instance();
            MediaFoldersGallery::get_instance();
        }

        $api_data = $this->api->get_site_data();
        if ( $api_data && isset( $api_data->site ) && ! empty( $api_data->site->upload_key ) && ! empty( $api_data->site->upload_secret ) ) {
            $this->bucket     = $api_data->site->upload_bucket;
            $this->key        = $api_data->site->upload_key;
            $this->secret     = $api_data->site->upload_secret;
            $this->bucket_url = $api_data->site->cdn_url;
            $this->region     = $api_data->site->upload_region;
            add_filter( 'infinite_uploads_s3_client_params', function ( $params ) use ( $api_data ) {
                $params['endpoint']                    = $api_data->site->upload_endpoint;
                $params['use_path_style_endpoint']     = true;
                $params['use_aws_shared_config_files'] = false;
                return $params;
            } );
        } else {
            // No cloud data — force cloud mode off so downstream code
            // can rely on infinite_uploads_enabled() reflecting real capability.
            if ( infinite_uploads_enabled() ) {
                $this->toggle_cloud( false );
            }
        }

        add_filter( 'infinite_uploads_sync_exclusions', [ $this, 'compatibility_exclusions' ] );
        // User-configured exclusions must reach the scan-time filter too —
        // without this, a full re-scan uploads files the user has explicitly
        // marked as excluded (wasting bandwidth/cloud storage), and — worse —
        // makes those already-synced-but-excluded rows eligible for "Free Up
        // Local Storage" deletion, producing 404 media (the rewriter keeps
        // serving the local URL because the path is excluded).
        add_filter( 'infinite_uploads_sync_exclusions', [ $this, 'user_exclusions' ] );

        if ( ! $this->api->has_token() ) {
            add_action( 'admin_notices', [ $this, 'setup_notice' ] );
            add_action( 'network_admin_notices', [ $this, 'setup_notice' ] );

            return true;
        }

        // Register new attachments into the sync table the moment WordPress
        // creates them. Runs regardless of whether cloud rewriting is enabled
        // yet — the entire ticket class this fixes (see support ticket #11637)
        // is uploads that land locally between "connect" and "enable CDN",
        // which then never get synced because the sync table was populated
        // from an earlier scan snapshot and no `add_attachment` hook existed
        // to add newcomers. Handlers self-guard: if the file doesn't exist
        // on the ORIGINAL local path (because it was written via the iu://
        // stream wrapper when enabled), the INSERT is skipped.
        add_action( 'add_attachment', [ $this, 'register_attachment_for_sync' ] );
        add_filter( 'wp_generate_attachment_metadata', [ $this, 'register_attachment_metadata_for_sync' ], 10, 2 );

        // don't register all this until we've enabled rewriting.
        if ( ! infinite_uploads_enabled() ) {
            return false;
        }

        $this->register_stream_wrapper();
        add_action( 'shutdown', [ $this, 'stream_wrapper_debug' ] );

        $uploads_url = $this->get_original_upload_dir(); //prime the cached value before filtering

        // Set the priority to 1 so it runs before other upload_dir filters.
        add_filter( 'upload_dir', [ $this, 'filter_upload_dir' ], 1 );

        //bypass cloud during updates
        add_action( 'load-update.php', [ $this, 'tear_down' ] );
        //block uploads if permissions are only read/delete
        if ( ! $api_data->site->upload_writeable ) {
            add_filter( 'pre-upload-ui', [ $this, 'blocked_uploads_header' ] );
            add_filter( 'wp_handle_upload_prefilter', [ $this, 'block_uploads' ] );
            add_filter( 'rest_pre_dispatch', [ $this, 'block_rest_upload' ], 10, 3 );
            add_filter( 'wp_save_image_editor_file', '__return_false' );
        }

        //block uploads if permissions are only read/delete
        if ( ! $api_data->site->cdn_enabled ) {
            add_filter( 'admin_notices', [ $this, 'cdn_disabled_header' ] );
            add_filter( 'network_admin_notices', [ $this, 'cdn_disabled_header' ] );
        }

        add_filter( 'wp_image_editors', [ $this, 'filter_editors' ], 9 );
        add_action( 'delete_attachment', [ $this, 'delete_attachment_files' ] );
        add_filter( 'wp_read_image_metadata', [ $this, 'wp_filter_read_image_metadata' ], 10, 2 );
        add_filter( 'wp_update_attachment_metadata', [ $this, 'update_attachment_metadata' ], 10, 2 );
        add_filter( 'wp_get_attachment_metadata', [ $this, 'get_attachment_metadata' ] );
        add_filter( '_wp_relative_upload_path', [ $this, 'filter_wp_relative_upload_path' ], 10, 2 );
        add_filter( 'get_attached_file', [ $this, 'filter_get_attached_file' ], 10, 2 );
        add_filter( 'wp_resource_hints', [ $this, 'wp_filter_resource_hints' ], 10, 2 );
        remove_filter( 'admin_notices', 'wpthumb_errors' );

        add_filter( 'pre_wp_unique_filename_file_list', [ $this, 'get_files_for_unique_filename_file_list' ], 10, 3 );

        // Add filters to "wrap" the wp_privacy_personal_data_export_file function call as we need to
        // switch out the personal_data directory to a local temp folder, and then upload after it's
        // complete, as Core tries to write directly to the ZipArchive which won't work with the
        // IU streamWrapper.
        add_action( 'wp_privacy_personal_data_export_file', [ $this, 'before_export_personal_data', 9 ] );
        add_action( 'wp_privacy_personal_data_export_file', [ $this, 'after_export_personal_data', 11 ] );
        add_action( 'wp_privacy_personal_data_export_file_created', [ $this, 'move_temp_personal_data_to_s3', 1000 ] );

        $this->plugin_compatibility();

        if ( ! defined( 'INFINITE_UPLOADS_DISABLE_REPLACE_UPLOAD_URL' ) || ! INFINITE_UPLOADS_DISABLE_REPLACE_UPLOAD_URL ) {
            //makes this work with pre 3.5 MU ms_files rewriting (ie domain.com/files/filename.jpg)
            $original_root_dirs = $this->get_original_upload_dir_root();
            $replacements       = [ $original_root_dirs['baseurl'] ];
            //if we have a custom domain add original cdn url for replacement
            if ( $this->get_s3_url() !== 'https://' . $api_data->site->cname ) {
                $replacements[] = 'https://' . $api_data->site->cname;
            }

            //makes this work with pre 3.5 MU ms_files rewriting (ie domain.com/files/filename.jpg)
            if ( is_multisite() && substr_compare( $original_root_dirs['baseurl'], '/files', - strlen( '/files' ) ) === 0 ) {
                $new_dirs = wp_get_upload_dir();
                $cdn_url  = str_replace( 'iu://' . untrailingslashit( $this->bucket ), $api_data->site->cname, $new_dirs['basedir'] );
            } else {
                $cdn_url = $this->get_s3_url();
            }
            // Instantiate the rewriter regardless of $cdn_enabled. Reason: filter_upload_dir()
            // unconditionally replaces wp_upload_dir()['baseurl'] with the CDN URL, which
            // causes Smush to emit malformed next-gen URLs (https:/smush-webp/…) via its
            // dirname(baseurl) derivation when the CDN URL is a host-only vanity domain.
            // The rewriter's str_replace pass repairs those URLs — and it must run even
            // when cdn_enabled is false, because Smush's output depends on the modified
            // baseurl, not on whether the CDN is active.
            new InfiniteUploadsRewriter( $original_root_dirs['baseurl'], $replacements, $cdn_url );
        }

    }

    public function check_sync_status() {
        $do_sync_complete     = get_site_option( 'iup_do_sync_complete', 'no' );
        $do_download_complete = get_site_option( 'iup_do_download_complete', 'no' );

        $data = [];
        if ( $do_sync_complete === 'yes' && $do_download_complete === 'yes' ) {
            $data['is_done'] = true;

            update_site_option( 'iup_do_sync_complete', 'no' );
            update_site_option( 'iup_do_download_complete', 'no' );
        }

        wp_send_json_success( $data );
    }

    public function enqueue_assets() {
        wp_register_script( 'iup-sync-status', plugins_url( 'assets/js/infinite-uploads-sync-status.js', __FILE__ ), [ 'jquery' ], INFINITE_UPLOADS_VERSION, true );
        wp_enqueue_script( 'iup-sync-status' );

        wp_localize_script( 'iup-sync-status', 'iup_sync_status_params', [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'iup_sync_status' ),
        ] );
    }

    /**
     * Enable or disable cloud stream wrapper and url rewriting.
     *
     * @param  bool  $enabled
     */
    public function toggle_cloud( $enabled ) {
        if ( is_multisite() ) {
            update_site_option( 'iup_enabled', $enabled );
        } else {
            update_option( 'iup_enabled', $enabled, true );
        }
        if ( $enabled ) {
            $this->api->call( "site/" . $this->api->get_site_id() . "/enable", [], 'POST', [
                    'timeout'  => 0.01,
                    'blocking' => false,
            ] );

            //not ideal but such a dramatic change of replacing upload dirs and urls can break some plugins/themes
            wp_cache_flush();

            //Hummingbird plugin
            do_action( 'wphb_clear_page_cache' );

            //WP rocket plugin
            if ( function_exists( 'rocket_clean_domain' ) ) {
                rocket_clean_domain();
            }
        }
    }

    /**
     * Register the stream wrapper for s3
     */
    public function register_stream_wrapper() {
        /**
         * INFINITE_UPLOADS_USE_LOCAL define. If true will use the local stream wrapper to write files to local directory instead of cloud.
         *
         * @constant {boolean} INFINITE_UPLOADS_USE_LOCAL
         * @default false
         */
        if ( defined( 'INFINITE_UPLOADS_USE_LOCAL' ) && INFINITE_UPLOADS_USE_LOCAL ) {
            stream_wrapper_register( 'iu', 'InfiniteUploadsLocalStreamWrapper', STREAM_IS_URL );
        } else {
            InfiniteUploadsStreamWrapper::register( $this->s3() );
            /**
             * INFINITE_UPLOADS_OBJECT_ACL define. If set will override the object ACL for new objects stored in the cloud.
             *
             * @constant {string} INFINITE_UPLOADS_OBJECT_ACL
             * @default `public-read`
             */
            $objectAcl = defined( 'INFINITE_UPLOADS_OBJECT_ACL' ) ? INFINITE_UPLOADS_OBJECT_ACL : 'public-read';
            stream_context_set_option( stream_context_get_default(), 'iu', 'ACL', $objectAcl );

            stream_context_set_option( stream_context_get_default(), 'iu', 'iup_instance', $this );
        }

        stream_context_set_option( stream_context_get_default(), 'iu', 'seekable', true );
    }

    /**
     * Writes total info to debug log if feature is defined.
     */
    public function stream_wrapper_debug() {
        if ( $this->stream_api_call_count['total'] ) {
            error_log( sprintf( "[INFINITE_UPLOADS Stream Debug] Stream wrapper API calls in %ss: %s", timer_stop(), json_encode( $this->stream_api_call_count, JSON_PRETTY_PRINT ) ) );
        }
        if ( count( $this->stream_plugin_api_call_count ) ) {
            error_log( sprintf( "[INFINITE_UPLOADS Stream Debug] Stream wrapper API calls by plugin: %s", json_encode( $this->stream_plugin_api_call_count, JSON_PRETTY_PRINT ) ) );
        }
    }

    /**
     * @return ClikIT\Infinite_Uploads\Aws\S3\S3Client
     */
    public function s3() {
        if ( ! empty( $this->s3 ) ) {
            return $this->s3;
        }

        $params = [ 'version' => 'latest' ];

        if ( $this->key && $this->secret ) {
            $params['credentials']['key']    = $this->key;
            $params['credentials']['secret'] = $this->secret;
        }

        if ( $this->region ) {
            $params['signature'] = 'v4';
            $params['region']    = $this->region;
        }

        if ( defined( 'WP_PROXY_HOST' ) && defined( 'WP_PROXY_PORT' ) ) {
            $proxy_auth    = '';
            $proxy_address = WP_PROXY_HOST . ':' . WP_PROXY_PORT;

            if ( defined( 'WP_PROXY_USERNAME' ) && defined( 'WP_PROXY_PASSWORD' ) ) {
                $proxy_auth = WP_PROXY_USERNAME . ':' . WP_PROXY_PASSWORD . '@';
            }

            $params['request.options']['proxy'] = $proxy_auth . $proxy_address;
        }

        /**
         * Filter the parameters passed when creating the  via the AWS PHP SDK.
         * See; https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_configuration.html
         *
         * @param  {array} $params S3Client::_construct() parameters.
         *
         * @return {array} $params S3Client::_construct() parameters.
         * @since  1.0
         * @hook   infinite_uploads_s3_client_params
         *
         */
        $params   = apply_filters( 'infinite_uploads_s3_client_params', $params );
        $this->s3 = new S3Client( $params );

        return $this->s3;
    }

    /*
     *
     */
    public function get_original_upload_dir() {
        if ( empty( $this->original_upload_dir ) ) {
            $this->original_upload_dir = wp_get_upload_dir();
        }

        return $this->original_upload_dir;
    }

    /**
     * Get root upload dir for multisite. Based on _wp_upload_dir().
     *
     * @return array See wp_upload_dir()
     */
    public function get_original_upload_dir_root() {
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

    public function setup_notice() {
        if ( ! current_user_can( $this->capability ) ) {
            return;
        }

        if ( get_current_screen()->id == 'toplevel_page_infinite_uploads' || get_current_screen()->id == 'toplevel_page_infinite_uploads-network' ) {
            return;
        }
        ?>
        <div class="notice notice-info" style="white-space: nowrap;padding: 10px 15px 10px 10px;">
			<span style="display: inline-block;vertical-align: middle;white-space: normal;width: 80%;font-size: 15px;">
				<strong><?php
                    esc_html_e( 'Infinite Uploads is almost ready!', 'infinite-uploads' ); ?></strong>
				<?php
                esc_html_e( 'Create or connect your account to move your images, audio, and video to the cloud - with a click!', 'infinite-uploads' );
                ?>
			</span>
            <span style="display: inline-block;vertical-align: middle;width: 20%;text-align: right;">
				<a class="button button-primary" href="<?php
                echo esc_url( $this->admin->settings_url() ); ?>" style="font-size: 15px;"><?php
                    echo $this->api->has_token() ? esc_html__( 'Finish Sync', 'infinite-uploads' ) : esc_html__( 'Connect', 'infinite-uploads' ); ?></a>
			</span>
        </div>
        <?php
    }

    /**
     * Tear down the hooks, url filtering etc for Infinite Uploads
     */
    public function tear_down() {
        // Priority must match the value used in add_filter() — without it remove_filter() silently fails.
        remove_filter( 'upload_dir', [ $this, 'filter_upload_dir' ], 1 );
        remove_filter( 'wp_image_editors', [ $this, 'filter_editors' ], 9 );
        remove_filter( 'pre_wp_unique_filename_file_list', [ $this, 'get_files_for_unique_filename_file_list' ], 10 );
        remove_action( 'delete_attachment', [ $this, 'delete_attachment_files' ] );

        // Remove file-exclusion upload filters. Plugin/theme ZIP installs go through
        // wp_handle_upload() which fires these filters. Without removing them,
        // handle_upload() converts the local ZIP path to iu:// after the upload_dir
        // filter is removed — pclzip then receives an iu:// path with the stream
        // wrapper already unregistered, causing "archive incompatible" errors.
        $admin = InfiniteUploadsAdmin::get_instance();
        remove_filter( 'wp_handle_upload', [ $admin, 'handle_upload' ], 10 );
        remove_filter( 'pre_move_uploaded_file', [ $admin, 'set_the_new_file_path' ], 10 );

        // Unregister the iu:// stream wrapper so plugin/theme ZIPs are never routed through cloud storage.
        if ( in_array( 'iu', stream_get_wrappers(), true ) ) {
            stream_wrapper_unregister( 'iu' );
        }
    }

    public function get_sync_stats() {
        global $wpdb;

        // $deletable must match what "Free Up Local Storage" would actually
        // delete — otherwise the UI advertises freeable space that BB cache /
        // user-excluded carve-outs will keep on disk. Same carve-out is applied
        // to the delete loops (ajax_delete_old, ajax_delete, WP-CLI files delete).
        $carve_out = InfiniteUploadsHelper::deletable_files_where_carveout();

        $total     = $wpdb->get_row( "SELECT count(*) AS files, SUM(`size`) as size, SUM(`transferred`) as transferred FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE 1" );
        $local     = $wpdb->get_row( "SELECT count(*) AS files, SUM(`size`) as size, SUM(`transferred`) as transferred FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE deleted = 0" );
        $synced    = $wpdb->get_row( "SELECT count(*) AS files, SUM(`size`) as size, SUM(`transferred`) as transferred FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE synced = 1" );
        $deletable = $wpdb->get_row( "SELECT count(*) AS files, SUM(`size`) as size, SUM(`transferred`) as transferred FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE synced = 1 AND deleted = 0{$carve_out}" );
        $deleted   = $wpdb->get_row( "SELECT count(*) AS files, SUM(`size`) as size, SUM(`transferred`) as transferred FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE synced = 1 AND deleted = 1" );

        $progress = (array) get_site_option( 'iup_files_scanned' );

        return array_merge( $progress, [
                'is_data'         => (bool) $total->files,
                'total_files'     => number_format_i18n( (int) $total->files ),
                'total_size'      => size_format( (int) $total->size, 2 ),
                'local_files'     => number_format_i18n( (int) $local->files ),
                'local_size'      => size_format( (int) $local->size, 2 ),
                'cloud_files'     => number_format_i18n( (int) $synced->files ),
                'cloud_size'      => size_format( (int) $synced->size, 2 ),
                'deletable_files' => number_format_i18n( (int) $deletable->files ),
                'deletable_size'  => size_format( (int) $deletable->size, 2 ),
                'deleted_files'   => number_format_i18n( (int) $deleted->files ),
                'deleted_size'    => size_format( (int) $deleted->size, 2 ),
                'remaining_files' => number_format_i18n( max( $total->files - $synced->files, 0 ) ),
                'remaining_size'  => size_format( max( $total->size - $total->transferred, 0 ), 2 ),
                'pcnt_complete'   => ( $local->size ? min( 100, round( ( $total->transferred / $total->size ) * 100, 2 ) ) : 0 ),
                'pcnt_downloaded' => ( $synced->size ? min( 100, round( 100 - ( ( $deleted->size / $synced->size ) * 100 ), 2 ) ) : 0 ),
        ] );
    }

    public function get_filetypes( $is_chart = false, $cloud_types = false ) {
        global $wpdb;

        if ( false !== $cloud_types ) {
            if ( empty( $cloud_types ) ) { //estimate if sync was fresh
                $types = $wpdb->get_results( "SELECT type, count(*) AS files, SUM(`size`) as size FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE synced = 1 GROUP BY type ORDER BY size DESC" );
            } else {
                $types = $cloud_types;
            }
        } else {
            $types = $wpdb->get_results( "SELECT type, count(*) AS files, SUM(`size`) as size FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE deleted = 0 GROUP BY type ORDER BY size DESC" );
        }

        $data = [];
        foreach ( $types as $type ) {
            $data[ $type->type ] = (object) [
                    'color' => $this->get_file_type_format( $type->type, 'color' ),
                    'label' => $this->get_file_type_format( $type->type, 'label' ),
                    'size'  => $type->size,
                    'files' => $type->files,
            ];
        }

        $chart = [];
        if ( $is_chart ) {
            foreach ( $data as $item ) {
                $chart['datasets'][0]['data'][]            = $item->size;
                $chart['datasets'][0]['backgroundColor'][] = $item->color;
                $chart['labels'][]                         = $item->label . ": " . sprintf( _n( '%s file totalling %s', '%s files totalling %s', $item->files, 'infinite-uploads' ), number_format_i18n( $item->files ), size_format( $item->size, 1 ) );
            }

            $total_size     = array_sum( wp_list_pluck( $data, 'size' ) );
            $total_files    = array_sum( wp_list_pluck( $data, 'files' ) );
            $chart['total'] = sprintf( _n( '%s / %s File', '%s / %s Files', $total_files, 'infinite-uploads' ), size_format( $total_size, 2 ), number_format_i18n( $total_files ) );

            return $chart;
        }

        return $data;
    }

    public function get_file_type_format( $type, $key ) {
        $labels = [
                'image'    => [ 'color' => '#26A9E0', 'label' => esc_html__( 'Images', 'infinite-uploads' ) ],
                'audio'    => [ 'color' => '#00A167', 'label' => esc_html__( 'Audio', 'infinite-uploads' ) ],
                'video'    => [ 'color' => '#C035E2', 'label' => esc_html__( 'Video', 'infinite-uploads' ) ],
                'document' => [ 'color' => '#EE7C1E', 'label' => esc_html__( 'Documents', 'infinite-uploads' ) ],
                'archive'  => [ 'color' => '#EC008C', 'label' => esc_html__( 'Archives', 'infinite-uploads' ) ],
                'code'     => [ 'color' => '#EFED27', 'label' => esc_html__( 'Code', 'infinite-uploads' ) ],
                'other'    => [ 'color' => '#F1F1F1', 'label' => esc_html__( 'Other', 'infinite-uploads' ) ],
        ];

        if ( isset( $labels[ $type ] ) ) {
            return $labels[ $type ][ $key ];
        } else {
            return $labels['other'][ $key ];
        }
    }

    public function get_file_type( $filename ) {
        $extensions = [
                'image'    => [
                        'jpg',
                        'jpeg',
                        'jpe',
                        'gif',
                        'png',
                        'bmp',
                        'tif',
                        'tiff',
                        'ico',
                        'svg',
                        'svgz',
                        'webp',
                        'avif',
                ],
                'audio'    => [
                        'aac',
                        'ac3',
                        'aif',
                        'aiff',
                        'flac',
                        'm3a',
                        'm4a',
                        'm4b',
                        'mka',
                        'mp1',
                        'mp2',
                        'mp3',
                        'ogg',
                        'oga',
                        'ram',
                        'wav',
                        'wma',
                ],
                'video'    => [
                        '3g2',
                        '3gp',
                        '3gpp',
                        'asf',
                        'avi',
                        'divx',
                        'dv',
                        'flv',
                        'm4v',
                        'mkv',
                        'mov',
                        'mp4',
                        'mpeg',
                        'mpg',
                        'mpv',
                        'ogm',
                        'ogv',
                        'qt',
                        'rm',
                        'vob',
                        'wmv',
                        'webm',
                ],
                'document' => [
                        'log',
                        'asc',
                        'csv',
                        'tsv',
                        'txt',
                        'doc',
                        'docx',
                        'docm',
                        'dotm',
                        'odt',
                        'pages',
                        'pdf',
                        'xps',
                        'oxps',
                        'rtf',
                        'wp',
                        'wpd',
                        'psd',
                        'xcf',
                        'swf',
                        'key',
                        'ppt',
                        'pptx',
                        'pptm',
                        'pps',
                        'ppsx',
                        'ppsm',
                        'sldx',
                        'sldm',
                        'odp',
                        'numbers',
                        'ods',
                        'xls',
                        'xlsx',
                        'xlsm',
                        'xlsb',
                ],
                'archive'  => [
                        'bz2',
                        'cab',
                        'dmg',
                        'gz',
                        'rar',
                        'sea',
                        'sit',
                        'sqx',
                        'tar',
                        'tgz',
                        'zip',
                        '7z',
                        'data',
                        'bin',
                        'bak',
                ],
                'code'     => [ 'css', 'htm', 'html', 'php', 'js', 'md' ],
        ];

        $ext = preg_replace( '/^.+?\.([^.]+)$/', '$1', $filename );
        if ( ! empty( $ext ) ) {
            $ext = strtolower( $ext );
            foreach ( $extensions as $type => $exts ) {
                if ( in_array( $ext, $exts, true ) ) {
                    return $type;
                }
            }
        }

        return 'other';
    }

    /**
     * Override the files used for wp_unique_filename() comparisons
     *
     * @param  array|null  $files
     * @param  string      $dir
     *
     * @return array
     */
    public function get_files_for_unique_filename_file_list( $files, $dir, $filename ) {
        $name = pathinfo( $filename, PATHINFO_FILENAME );
        // The iu:// streamwrapper support listing by partial prefixes with wildcards.
        // For example, scandir( iu://bucket/2019/06/my-image* )
        return scandir( trailingslashit( $dir ) . $name . '*' );
    }

    public function filter_upload_dir( $dirs ) {
        $root_dirs = $this->get_original_upload_dir_root();

        $dirs['path']    = str_replace( $root_dirs['basedir'], 'iu://' . untrailingslashit( $this->bucket ), $dirs['path'] );
        $dirs['basedir'] = str_replace( $root_dirs['basedir'], 'iu://' . untrailingslashit( $this->bucket ), $dirs['basedir'] );

        if ( ! defined( 'INFINITE_UPLOADS_DISABLE_REPLACE_UPLOAD_URL' ) || ! INFINITE_UPLOADS_DISABLE_REPLACE_UPLOAD_URL ) {
            if ( defined( 'INFINITE_UPLOADS_USE_LOCAL' ) && INFINITE_UPLOADS_USE_LOCAL ) {
                $dirs['url']     = str_replace( 'iu://' . untrailingslashit( $this->bucket ), $dirs['baseurl'] . '/iu/' . $this->bucket, $dirs['path'] );
                $dirs['baseurl'] = str_replace( 'iu://' . untrailingslashit( $this->bucket ), $dirs['baseurl'] . '/iu/' . $this->bucket, $dirs['basedir'] );
            } else {
                $dirs['url']     = str_replace( 'iu://' . untrailingslashit( $this->bucket ), $this->get_s3_url(), $dirs['path'] );
                $dirs['baseurl'] = str_replace( 'iu://' . untrailingslashit( $this->bucket ), $this->get_s3_url(), $dirs['basedir'] );
            }
        }

        return $dirs;
    }

    public function get_s3_url() {
        if ( $this->bucket_url ) {
            return 'https://' . $this->bucket_url;
        }

        $bucket = strtok( $this->bucket, '/' );
        $path   = substr( $this->bucket, strlen( $bucket ) );

        return apply_filters( 'infinite_uploads_bucket_url', 'https://' . $bucket . '.s3.amazonaws.com' . $path );
    }

    /**
     * Delete all attachment files from S3 when an attachment is deleted.
     *
     * WordPress Core's handling of deleting files for attachments via
     * wp_delete_attachment_files is not compatible with remote streams, as
     * it makes many assumptions about local file paths. The hooks also do
     * not exist to be able to modify their behavior. As such, we just clean
     * up the s3 files when an attachment is removed, and leave WordPress to try
     * a failed attempt at mangling the iu:// urls.
     *
     * UPDATE deletes seem to get issued properly now, only use this for purging from CDN.
     *
     * @param $post_id
     */
    public function delete_attachment_files( $post_id ) {
        $meta = wp_get_attachment_metadata( $post_id );
        $file = get_attached_file( $post_id );

        $to_purge = [];
        if ( ! empty( $meta['sizes'] ) ) {
            foreach ( $meta['sizes'] as $sizeinfo ) {
                $intermediate_file = str_replace( basename( $file ), $sizeinfo['file'], $file );
                $to_purge[] = $intermediate_file;
            }
        }

        wp_delete_file( $file );
        $to_purge[] = $file;

        $dirs = wp_get_upload_dir();
        foreach ( $to_purge as $key => $file ) {
            $to_purge[ $key ] = str_replace( $dirs['basedir'], $dirs['baseurl'], $file );
        }

        if ( interface_exists( '\Imagify\CDN\PushCDNInterface' ) ) {
            $to_purge = array_merge( $to_purge, InfiniteUploadsImagify::get_attachment_nextgen_urls( $post_id ) );
        }

        $to_purge = array_values( array_unique( $to_purge ) );
        $this->api->purge( $to_purge );
    }

    /**
     * Register a newly-created attachment's main file in the sync table.
     *
     * Fires on `add_attachment`, before image sub-sizes have been generated.
     * Sub-sizes are picked up by `register_attachment_metadata_for_sync()`
     * below, which fires from `wp_generate_attachment_metadata` a moment
     * later — the two together cover every file WordPress creates for an
     * attachment.
     *
     * Idempotent: uses INSERT … ON DUPLICATE KEY UPDATE so re-firing is
     * harmless (rescanning uses the same insert path — see
     * InfiniteUploadsFilelist::flush_to_db()).
     *
     * Self-guards on iu://: when cloud mode is fully enabled, uploads go
     * to the stream wrapper directly and the file does not exist on the
     * original local path — the file_exists() check in
     * `sync_register_local_files()` skips the INSERT in that case.
     *
     * @param  int  $attachment_id
     */
    public function register_attachment_for_sync( $attachment_id ) {
        // Attempt to resolve directly from the DB rather than through
        // get_attached_file(), which is filtered to return iu:// paths when
        // cloud mode is enabled — we specifically want the LOCAL path so
        // we can tell whether the file needs syncing.
        $meta_file = get_post_meta( $attachment_id, '_wp_attached_file', true );
        if ( ! $meta_file ) {
            return;
        }
        $this->sync_register_local_files( [ '/' . ltrim( $meta_file, '/' ) ] );
    }

    /**
     * Register an attachment's generated sub-sizes (thumbnail, medium, etc)
     * in the sync table. Fires from `wp_generate_attachment_metadata`, which
     * is called once WordPress has produced the intermediate images for an
     * upload but before the metadata is saved. Every file this filter sees
     * is a fresh write to disk that our sync table needs to know about.
     *
     * Returns the metadata untouched — we're only piggybacking to observe.
     *
     * @param  array  $metadata
     * @param  int    $attachment_id
     *
     * @return array
     */
    public function register_attachment_metadata_for_sync( $metadata, $attachment_id ) {
        if ( empty( $metadata ) || ! is_array( $metadata ) ) {
            return $metadata;
        }

        $relative_paths = [];

        // Main file path is relative to uploads root, e.g. "2026/07/foo.png".
        if ( ! empty( $metadata['file'] ) ) {
            $relative_paths[] = '/' . ltrim( $metadata['file'], '/' );
        }

        // Sub-size filenames are basename-only, e.g. "foo-150x150.png". They
        // live in the same directory as the main file — derive that dir
        // once and prepend it to each size's `file`.
        if ( ! empty( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) && ! empty( $metadata['file'] ) ) {
            $dir = trailingslashit( dirname( $metadata['file'] ) );
            foreach ( $metadata['sizes'] as $size ) {
                if ( empty( $size['file'] ) ) {
                    continue;
                }
                $relative_paths[] = '/' . ltrim( $dir . $size['file'], '/' );
            }
        }

        if ( ! empty( $relative_paths ) ) {
            $this->sync_register_local_files( $relative_paths );
        }

        return $metadata;
    }

    /**
     * Bulk-INSERT the given uploads-relative file paths into the sync table
     * with synced=0, so the next sync tick picks them up. Skips paths whose
     * local file doesn't actually exist (e.g. iu://-served uploads when
     * cloud mode is enabled, or a size WordPress decided not to generate).
     *
     * Uses ON DUPLICATE KEY UPDATE so re-firing over a row that already
     * exists (whether from a prior scan or a previous hook firing on the
     * same request) just refreshes size/modified/type and resets errors to
     * 0 — matching the semantics of `InfiniteUploadsFilelist::flush_to_db()`.
     * The `synced` column is intentionally untouched: a row that's already
     * `synced=1` stays that way; a row that's `synced=0` (or a fresh row)
     * stays eligible for the next sync tick.
     *
     * @param  array  $relative_paths  Paths under uploads root, each with leading slash.
     */
    protected function sync_register_local_files( array $relative_paths ) {
        global $wpdb;
        if ( empty( $relative_paths ) ) {
            return;
        }

        $root   = $this->get_original_upload_dir_root();
        $basedir = isset( $root['basedir'] ) ? untrailingslashit( $root['basedir'] ) : '';
        if ( '' === $basedir ) {
            return;
        }

        $values = [];
        foreach ( array_unique( $relative_paths ) as $rel ) {
            $rel = '/' . ltrim( (string) $rel, '/' );
            $absolute = $basedir . $rel;

            // Skip if the file doesn't exist locally. When cloud mode is
            // enabled, uploads land via the iu:// stream wrapper and never
            // touch the local filesystem — nothing to register.
            if ( ! @is_file( $absolute ) ) {
                continue;
            }

            $size  = @filesize( $absolute );
            $mtime = @filemtime( $absolute );
            $type  = wp_check_filetype( $absolute );
            $type  = isset( $type['type'] ) && $type['type'] ? $type['type'] : 'application/octet-stream';

            $values[] = $wpdb->prepare(
                    '(%s, %d, %d, %s, 0, 0)',
                    $rel,
                    (int) $size,
                    (int) $mtime,
                    $type
            );
        }

        if ( empty( $values ) ) {
            return;
        }

        $query = "INSERT INTO {$wpdb->base_prefix}infinite_uploads_files (file, size, modified, type, synced, errors) VALUES ";
        $query .= implode( ",\n", $values );
        // Mirror InfiniteUploadsFilelist::flush_to_db()'s ON DUPLICATE clause,
        // plus we DO NOT touch `synced` — a re-registration for a row that's
        // already synced=1 must not un-sync it.
        $query .= " ON DUPLICATE KEY UPDATE size = VALUES(size), modified = VALUES(modified), type = VALUES(type), errors = 0";
        $wpdb->query( $query );
    }

    /**
     * Get the S3 bucket name
     *
     * @return string
     */
    public function get_s3_bucket() {
        return $bucket = strtok( $this->bucket, '/' );
    }

    /**
     * Get the S3 bucket name
     *
     * @return string
     */
    public function get_s3_prefix() {
        return untrailingslashit( str_replace( $this->get_s3_bucket() . '/', '', $this->bucket ) );
    }

    /**
     * Ge the S3 bucket region
     *
     * @return string
     */
    public function get_s3_bucket_region() {
        return $this->region;
    }

    /**
     * Show error on uploads screen when readonly.
     */
    public function blocked_uploads_header() {
        if ( current_user_can( $this->capability ) ) {
            ?>
            <div class="notice notice-error">
            <p><?php
                printf( __( "Files can't be uploaded due to a billing issue with your Infinite Uploads account. <a href='%s'>Please resolve the issue</a> to resume uploading.", 'infinite-uploads' ), esc_url( $this->admin->api_url( '/account/billing/' ) ) ); ?></p>
            </div><?php
        } else {
            ?>
            <div class="notice notice-error"><p><?php
                esc_html_e( "Files can't be uploaded due to a billing issue with your Infinite Uploads account.", 'infinite-uploads' ); ?></p>
            </div><?php
        }
    }

    /**
     * Show error on all screens.
     */
    public function cdn_disabled_header() {
        if ( current_user_can( $this->capability ) ) {
            if ( get_current_screen()->id == 'media_page_infinite_uploads'
                 || get_current_screen()->id == 'settings_page_infinite_uploads-network'
                 || ( get_current_screen()->id == 'media' && get_current_screen()->action == 'add' ) ) {
                return;
            }
            ?>
            <div class="notice notice-error">
            <p><?php
                printf( __( "Files can't be uploaded and your CDN is disabled due to a billing issue with your Infinite Uploads account. <a href='%s'>Please resolve the issue</a> to resume uploading. <a href='%s'>Already fixed?</a>", 'infinite-uploads' ), esc_url( $this->admin->api_url( '/account/billing/' ) ), esc_url( $this->admin->settings_url( [ 'refresh' => 1 ] ) ) ); ?></p>
            </div><?php
        }
    }

    /**
     * Return an error to display before trying to save newly uploaded media.
     *
     * @param $file
     *
     * @return array
     */
    public function block_uploads( $file ) {
        $file['error'] = esc_html__( "Files can't be uploaded due to a billing issue with your Infinite Uploads account.", 'infinite-uploads' );

        return $file;
    }

    /**
     * Block editing media in Gutenberg WP 5.5+ block.
     *
     * @param                   $result null
     * @param  WP_REST_Server   $server
     * @param  WP_REST_Request  $request
     *
     * @return mixed|WP_Error
     */
    function block_rest_upload( $result, $server, $request ) {
        if ( preg_match( '%/wp/v2/media/\d+/edit%', $request->get_route() ) ) {
            $result = new WP_Error(
                    'rest_cant_upload',
                    __( "Files can't be uploaded due to a billing issue with your Infinite Uploads account.", 'infinite-uploads' ),
                    [ 'status' => 403 ]
            );
        }

        return $result;
    }

    public function filter_editors( $editors ) {
        if ( ( $position = array_search( 'WP_Image_Editor_Imagick', $editors ) ) !== false ) {
            unset( $editors[ $position ] );
        }
        if ( ( $position = array_search( 'WP_Image_Editor_GD', $editors ) ) !== false ) {
            unset( $editors[ $position ] );
        }

        require_once __DIR__ . '/InfiniteUploadsImageEditorImagick.php';
        require_once __DIR__ . '/InfiniteUploadsImageEditorGD.php';

        // Prefer Imagick when available (test() will fall through to GD if not).
        array_unshift( $editors, '\ClikIT\InfiniteUploads\InfiniteUploadsImageEditorGD' );
        array_unshift( $editors, '\ClikIT\InfiniteUploads\InfiniteUploadsImageEditorImagick' );

        return $editors;
    }

    /**
     * Filters wp_read_image_metadata. exif_read_data() doesn't work on
     * file streams so we need to make a temporary local copy to extract
     * exif data from.
     *
     * @param  array   $meta
     * @param  string  $file
     *
     * @return array|bool
     */
    public function wp_filter_read_image_metadata( $meta, $file ) {
        remove_filter( 'wp_read_image_metadata', [ $this, 'wp_filter_read_image_metadata' ], 10 );
        $temp_file = $this->copy_image_from_s3( $file );
        $meta      = wp_read_image_metadata( $temp_file );
        add_filter( 'wp_read_image_metadata', [ $this, 'wp_filter_read_image_metadata' ], 10, 2 );
        unlink( $temp_file );

        return $meta;
    }

    /**
     * Get a local copy of the file.
     *
     * @param  string  $file
     *
     * @return string
     */
    public function copy_image_from_s3( $file ) {
        if ( ! function_exists( 'wp_tempnam' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }
        $temp_filename = wp_tempnam( $file );
        copy( $file, $temp_filename );

        return $temp_filename;
    }

    /**
     * Filters the attachment meta data. wp_prepare_attachment_for_js triggers a HeadObject to get filesize, usually uncached
     * on media grid and sometimes on frontend with some things, increasing TTFB a lot. Instead cache it when attachment is updated or created.
     *
     * @param  array  $data           Array of updated attachment meta data.
     * @param  int    $attachment_id  Attachment post ID.
     *
     * @return array
     */
    function update_attachment_metadata( $data, $attachment_id ) {
        $attached_file = get_attached_file( $attachment_id );
        if ( file_exists( $attached_file ) ) {
            $data['filesize'] = filesize( $attached_file );
        }

        // Normalize the 'file' key to a relative path to prevent full local paths from being stored.
        if ( ! empty( $data['file'] ) ) {
            $data['file'] = $this->normalize_attachment_file_path( $data['file'] );
        }

        return $data;
    }

    /**
     * Filters the attachment meta data. wp_prepare_attachment_for_js triggers a HeadObject to get filesize, usually uncached
     * on media grid and sometimes on frontend with some things, increasing TTFB a lot.
     *
     * @param  array  $data  Array of meta data for the given attachment.
     *
     * @return array
     */
    function get_attachment_metadata( $data ) {
        if ( ! isset( $data['filesize'] ) ) {
            $data['filesize'] = '';
        }

        // Normalize the 'file' key to a relative path.
        // Plugins like Smush Pro read this to construct file URLs and paths.
        if ( ! empty( $data['file'] ) ) {
            $data['file'] = $this->normalize_attachment_file_path( $data['file'] );
        }

        return $data;
    }

    /**
     * Normalize an attachment file path to be relative to the uploads directory.
     *
     * When the upload_dir basedir is set to the iu:// stream wrapper, WordPress's
     * _wp_relative_upload_path() cannot strip the local basedir from local filesystem paths.
     * This causes plugins like Smush Pro to construct incorrect URLs by concatenating the
     * cloud CDN URL with a full local filesystem path.
     *
     * @param string $file The file path to normalize.
     *
     * @return string The normalized relative file path.
     */
    private function normalize_attachment_file_path( $file ) {
        if ( empty( $file ) ) {
            return $file;
        }

        // If the path doesn't start with '/' and doesn't start with 'iu://', it's already relative.
        if ( 0 !== strpos( $file, '/' ) && 0 !== strpos( $file, 'iu://' ) ) {
            return $file;
        }

        // Handle iu:// stream wrapper paths.
        $iu_basedir = 'iu://' . untrailingslashit( $this->bucket );
        if ( 0 === strpos( $file, $iu_basedir ) ) {
            $normalized = ltrim( substr( $file, strlen( $iu_basedir ) ), '/' );
            if ( ! empty( $normalized ) ) {
                return $normalized;
            }
        }

        // Handle full local filesystem paths.
        if ( 0 === strpos( $file, '/' ) ) {
            $root_dirs     = $this->get_original_upload_dir_root();
            $local_basedir = trailingslashit( $root_dirs['basedir'] );
            if ( 0 === strpos( $file, $local_basedir ) ) {
                return substr( $file, strlen( $local_basedir ) );
            }
        }

        return $file;
    }

    /**
     * Filter _wp_relative_upload_path to handle local filesystem paths when IU is active.
     *
     * When a full local path is passed to _wp_relative_upload_path() but the upload basedir
     * is iu://, WordPress cannot strip the basedir. We strip the local basedir instead.
     *
     * @param string $new_path The relative path after WordPress processing.
     * @param string $path     The original full path.
     *
     * @return string The properly relative upload path.
     */
    function filter_wp_relative_upload_path( $new_path, $path ) {
        // If the path is already relative (doesn't start with / or stream wrapper), nothing to do.
        if ( 0 !== strpos( $new_path, '/' ) ) {
            return $new_path;
        }

        // Path still has a leading slash, meaning WordPress couldn't strip the basedir.
        // This happens when a local path is passed while basedir is iu://
        $root_dirs     = $this->get_original_upload_dir_root();
        $local_basedir = trailingslashit( $root_dirs['basedir'] );
        if ( 0 === strpos( $new_path, $local_basedir ) ) {
            $new_path = substr( $new_path, strlen( $local_basedir ) );
        }

        return $new_path;
    }

    /**
     * Filter get_attached_file to return local paths for excluded files.
     *
     * When file exclusion is enabled, excluded files reside on the local server,
     * not on the cloud. This filter ensures get_attached_file() returns the local
     * filesystem path instead of the iu:// stream wrapper path.
     *
     * @param string $file          The file path.
     * @param int    $attachment_id The attachment ID.
     *
     * @return string The corrected file path.
     */
    function filter_get_attached_file( $file, $attachment_id ) {
        if ( ! InfiniteUploadsHelper::is_file_exclusion_enabled() ) {
            return $file;
        }

        if ( InfiniteUploadsHelper::is_path_excluded( $file ) ) {
            return InfiniteUploadsHelper::get_local_file_path( $file );
        }

        return $file;
    }

    /**
     * Filter Smush Pro media item sizes to use local paths for excluded files.
     *
     * Smush constructs file paths and URLs from wp_upload_dir() which always returns
     * cloud values when IU is active. For excluded files that reside locally, we need
     * to replace the size object with one using local dir and base_url.
     *
     * @param object $size       The Media_Item_Size object.
     * @param string $key        The size key.
     * @param array  $metadata   The size metadata.
     * @param object $media_item The Media_Item object.
     *
     * @return object The corrected Media_Item_Size object.
     */
    function filter_smush_media_item_size( $size, $key, $metadata, $media_item ) {
        // IF file exclusion is disabled, do not need to change the file path to local.
        if ( ! InfiniteUploadsHelper::is_file_exclusion_enabled() ) {
            return $size;
        }

        $root_dirs    = $this->get_original_upload_dir_root();
        $relative_dir = $media_item->get_relative_file_dir();
        $local_dir    = trailingslashit( $root_dirs['basedir'] ) . $relative_dir . '/';

        // If file path is not excluded, do not need to change the file path to local.
        if ( ! InfiniteUploadsHelper::is_path_excluded( $local_dir ) ) {
            return $size;
        }

        $local_base_url = trailingslashit( $root_dirs['baseurl'] ) . $relative_dir . '/';

        return new \Smush\Core\Media\Media_Item_Size(
            $key,
            $media_item->get_id(),
            $local_dir,
            $local_base_url,
            $metadata
        );
    }

    /**
     * Add the DNS address for the S3 Bucket to list for DNS prefetch.
     *
     * @param $hints
     * @param $relation_type
     *
     * @return array
     */
    function wp_filter_resource_hints( $hints, $relation_type ) {
        if ( 'dns-prefetch' === $relation_type ) {
            $hints[] = $this->get_s3_url();
        }

        return $hints;
    }

    /**
     * Setup the filters for wp_privacy_exports_dir to use a temp folder location.
     */
    function before_export_personal_data() {
        add_filter( 'wp_privacy_exports_dir', [ $this, 'set_wp_privacy_exports_dir' ] );
    }

    /**
     * Remove the filters for wp_privacy_exports_dir as we only want it added in some cases.
     */
    function after_export_personal_data() {
        remove_filter( 'wp_privacy_exports_dir', [ $this, 'set_wp_privacy_exports_dir' ] );
    }

    /**
     * Override the wp_privacy_exports_dir location
     *
     * We don't want to use the default uploads folder location, as with Infinite Uploads this is
     * going to the a iu:// custom URL handler, which is going to fail with the use of ZipArchive.
     * Instgead we set to to sys_get_temp_dir and move the fail in the wp_privacy_personal_data_export_file_created
     * hook.
     *
     * @param  string  $dir
     *
     * @return string
     */
    function set_wp_privacy_exports_dir( $dir ) {
        if ( strpos( $dir, 'iu://' ) !== 0 ) {
            return $dir;
        }
        $dir = sys_get_temp_dir() . '/wp_privacy_exports_dir/';
        if ( ! is_dir( $dir ) ) {
            mkdir( $dir );
            file_put_contents( $dir . 'index.html', '' );
        }

        return $dir;
    }

    /**
     * Move the tmp personal data file to the true uploads location
     *
     * Once a personal data file has been written, move it from the overriden "temp"
     * location to the S3 location where it should have been stored all along, and where
     * the "natural" Core URL is going to be pointing to.
     */
    function move_temp_personal_data_to_s3( $archive_pathname ) {
        if ( strpos( $archive_pathname, sys_get_temp_dir() ) !== 0 ) {
            return;
        }
        $upload_dir  = wp_upload_dir();
        $exports_dir = trailingslashit( $upload_dir['basedir'] ) . 'wp-personal-data-exports/';
        $destination = $exports_dir . pathinfo( $archive_pathname, PATHINFO_FILENAME ) . '.' . pathinfo( $archive_pathname, PATHINFO_EXTENSION );
        copy( $archive_pathname, $destination );
        unlink( $archive_pathname );
    }

    /**
     * EWWW Image Optimizer compatibility: copy iu:// files to a local temp path so EWWW
     * can optimize them. Hooked to `ewww_image_optimizer_remote_fetched`.
     *
     * @param  string|false  $filename  Local path resolved so far (false when not yet found).
     * @param  int           $id        Attachment post ID.
     * @param  array         $meta      Attachment metadata.
     *
     * @return string|false  Local path to the full-size image, or original $filename on failure.
     */
    public function ewww_remote_fetch( $filename, $id, $meta ) {
        $iu_path = get_attached_file( $id );
        if ( ! $iu_path || strpos( $iu_path, 'iu://' ) !== 0 ) {
            return $filename;
        }

        $root_dirs  = $this->get_original_upload_dir_root();
        $iu_basedir = $this->get_s3_basedir();
        if ( ! $iu_basedir ) {
            return $filename;
        }

        // Map iu:// path → local path.
        $local_path = str_replace( $iu_basedir, $root_dirs['basedir'], $iu_path );
        if ( $local_path === $iu_path ) {
            return $filename;
        }

        if ( ! is_dir( dirname( $local_path ) ) ) {
            wp_mkdir_p( dirname( $local_path ) );
        }

        // Copy full-size image to local.
        if ( ! file_exists( $local_path ) ) {
            copy( $iu_path, $local_path );
        }

        // Copy resized versions to local so EWWW can optimize them too.
        if ( isset( $meta['sizes'] ) && is_array( $meta['sizes'] ) ) {
            $base_iu    = trailingslashit( dirname( $iu_path ) );
            $base_local = trailingslashit( dirname( $local_path ) );
            foreach ( $meta['sizes'] as $size => $data ) {
                if ( empty( $data['file'] ) ) {
                    continue;
                }
                $iu_resize    = $base_iu . wp_basename( $data['file'] );
                $local_resize = $base_local . wp_basename( $data['file'] );
                if ( ! file_exists( $local_resize ) ) {
                    copy( $iu_resize, $local_resize );
                }
            }
        }

        return file_exists( $local_path ) ? $local_path : $filename;
    }

    /**
     * EWWW Image Optimizer compatibility: push the locally-optimized files back to iu://
     * and remove the local copies. Hooked to `ewww_image_optimizer_after_optimize_attachment`.
     *
     * Also pushes any WebP/AVIF sidecars EWWW generated next to each image
     * (e.g. image.png.webp, image-150x150.png.webp). Without that, only the original
     * .png/.jpg makes it back to cloud and the next-gen siblings stay on local disk —
     * the CDN ends up serving the original format and EWWW's <picture>/JS rewrites
     * 404 on the missing webp/avif URLs.
     *
     * @param  int    $id    Attachment post ID.
     * @param  array  $meta  Attachment metadata.
     */
    public function ewww_remote_push( $id, $meta ) {
        $iu_path = get_attached_file( $id );
        if ( ! $iu_path || strpos( $iu_path, 'iu://' ) !== 0 ) {
            return;
        }

        $root_dirs  = $this->get_original_upload_dir_root();
        $iu_basedir = $this->get_s3_basedir();
        if ( ! $iu_basedir ) {
            return;
        }

        $local_path = str_replace( $iu_basedir, $root_dirs['basedir'], $iu_path );
        if ( $local_path === $iu_path ) {
            return;
        }

        // Push full-size + its webp/avif sidecars back to iu://.
        $this->ewww_push_file_with_sidecars( $local_path, $iu_path );

        // Push resized versions + their webp/avif sidecars back to iu://.
        if ( isset( $meta['sizes'] ) && is_array( $meta['sizes'] ) ) {
            $base_iu    = trailingslashit( dirname( $iu_path ) );
            $base_local = trailingslashit( dirname( $local_path ) );
            foreach ( $meta['sizes'] as $size => $data ) {
                if ( empty( $data['file'] ) ) {
                    continue;
                }
                $local_resize = $base_local . wp_basename( $data['file'] );
                $iu_resize    = $base_iu . wp_basename( $data['file'] );
                $this->ewww_push_file_with_sidecars( $local_resize, $iu_resize );
            }
        }
    }

    /**
     * Copy a single file from local → iu:// and unlink the local copy. Also handles
     * the `.webp` and `.avif` sidecars EWWW writes next to each image.
     *
     * @param  string  $local_path  Local filesystem path of the source file.
     * @param  string  $iu_path     Equivalent iu:// destination path.
     */
    private function ewww_push_file_with_sidecars( $local_path, $iu_path ) {
        if ( file_exists( $local_path ) ) {
            if ( @copy( $local_path, $iu_path ) ) {
                @unlink( $local_path );
            }
        }

        foreach ( [ '.webp', '.avif' ] as $ext ) {
            $local_sidecar = $local_path . $ext;
            $iu_sidecar    = $iu_path . $ext;
            if ( file_exists( $local_sidecar ) ) {
                if ( @copy( $local_sidecar, $iu_sidecar ) ) {
                    @unlink( $local_sidecar );
                }
            }
        }
    }

    /**
     * Add the IU CDN URL to EWWW's allowed-urls list. EWWW's Picture_Webp / JS_Webp
     * rewriters only consider `<img>` elements whose URL matches one of these entries;
     * without this, images served from the IU CDN are silently skipped.
     *
     * @param  array  $urls
     *
     * @return array
     */
    public function ewww_allowed_urls( $urls ) {
        $cdn_url = $this->get_s3_url();
        if ( $cdn_url ) {
            $urls[] = trailingslashit( $cdn_url );
        }

        return $urls;
    }

    /**
     * Add the IU CDN host to EWWW's allowed-domains list. Used in EWWW's cdn_to_local()
     * translation, so URLs on our CDN resolve back to valid attachment paths.
     *
     * @param  array  $domains
     *
     * @return array
     */
    public function ewww_allowed_domains( $domains ) {
        $cdn_url  = $this->get_s3_url();
        $cdn_host = $cdn_url ? wp_parse_url( $cdn_url, PHP_URL_HOST ) : '';
        if ( $cdn_host && ! in_array( $cdn_host, $domains, true ) ) {
            $domains[] = $cdn_host;
        }

        return $domains;
    }

    /**
     * Force EWWW's `webp_force` option on at runtime (DB value untouched) so its
     * rewriter uses the allowed_urls match path for IU-hosted images. Required
     * because EWWW's normal path calls is_file() on translated paths, which the
     * IU stream wrapper's is_file() rejects for anything containing `://`.
     *
     * Hooked to `pre_option_ewww_image_optimizer_webp_force`.
     *
     * @return string
     */
    public function ewww_force_webp_at_runtime() {
        return '1';
    }

    /**
     * Opt out specific images from EWWW's WebP rewrite. With `webp_force` on (see
     * ewww_force_webp_at_runtime) EWWW would wrap every IU-CDN image in a <picture>
     * tag; for images EWWW never converted, the sibling `.webp` doesn't exist and
     * the source would 404. Consult `wp_ewwwio_images` and skip anything not in it.
     *
     * Non-IU-CDN URLs defer to EWWW's default behavior (native AS3CF/S3 sites etc.).
     *
     * Hooked to `ewww_image_optimizer_skip_webp_rewrite`.
     *
     * @param  bool    $skip   Current skip decision from EWWW.
     * @param  string  $image  The image URL being evaluated.
     *
     * @return bool
     */
    public function ewww_skip_webp_rewrite( $skip, $image ) {
        // Respect an existing skip from another handler.
        if ( $skip ) {
            return $skip;
        }

        $cdn_url = $this->get_s3_url();
        if ( ! $cdn_url || strpos( $image, $cdn_url ) === false ) {
            // Not an IU CDN URL — let EWWW's normal logic decide.
            return $skip;
        }

        // Strip query string and CDN prefix to get the upload-root-relative path.
        $path = strtok( $image, '?' );
        $path = str_replace( trailingslashit( $cdn_url ), '', $path );
        if ( $path === '' ) {
            return true;
        }

        return $this->ewww_has_converted_webp( wp_basename( $path ) ) ? false : true;
    }

    /**
     * Check if EWWW has a successful WebP conversion on record for a given filename.
     * Results are memoized for the request to avoid a query per `<img>` on the page.
     *
     * @param  string  $basename  File basename, e.g. "sitting-6@2x-285x300.png".
     *
     * @return bool
     */
    private function ewww_has_converted_webp( $basename ) {
        global $wpdb;
        static $converted = null;

        if ( $converted === null ) {
            $converted = [];
            $table     = $wpdb->prefix . 'ewwwio_images';
            if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
                $rows = $wpdb->get_col( "SELECT path FROM {$table} WHERE webp_size > 0 AND webp_error = 0" );
                foreach ( (array) $rows as $row ) {
                    $converted[ wp_basename( $row ) ] = true;
                }
            }
        }

        return isset( $converted[ $basename ] );
    }

    /**
     * Tracks whether we opened an output buffer for the current Media Library column
     * render. Keyed by attachment ID so concurrent column callbacks don't interfere.
     *
     * @var array<int, true>
     */
    private $ewww_column_buffered = [];

    /**
     * Start an output buffer around EWWW's column callback for iu://-hosted attachments,
     * so we can rewrite EWWW's "Could not retrieve file path" failure into a useful
     * IU-aware status. Hooked to `manage_media_custom_column` at priority 9.
     *
     * @param  string  $column_name
     * @param  int     $id
     */
    public function ewww_column_buffer_start( $column_name, $id ) {
        if ( 'ewww-image-optimizer' !== $column_name ) {
            return;
        }
        $file = get_attached_file( (int) $id );
        if ( ! $file || strpos( $file, 'iu://' ) !== 0 ) {
            return;
        }
        ob_start();
        $this->ewww_column_buffered[ (int) $id ] = true;
    }

    /**
     * Close the output buffer opened by ewww_column_buffer_start(). If EWWW emitted
     * its "Could not retrieve file path" failure (because ewwwio_is_file() rejects
     * iu:// paths), replace it with a status block that reports IU-cloud storage
     * plus the EWWW optimization stats from `wp_ewwwio_images`. Otherwise pass
     * EWWW's normal output through unchanged.
     *
     * Hooked to `manage_media_custom_column` at priority 11.
     *
     * @param  string  $column_name
     * @param  int     $id
     */
    public function ewww_column_buffer_end( $column_name, $id ) {
        if ( 'ewww-image-optimizer' !== $column_name ) {
            return;
        }
        $id = (int) $id;
        if ( empty( $this->ewww_column_buffered[ $id ] ) ) {
            return;
        }
        unset( $this->ewww_column_buffered[ $id ] );

        $output = ob_get_clean();

        if ( strpos( $output, 'Could not retrieve file path' ) === false ) {
            // EWWW rendered something useful — let it stand.
            echo $output; // phpcs:ignore WordPress.Security.EscapeOutput
            return;
        }

        // Replace the error with IU-aware status. Same wrapper EWWW uses so its
        // CSS / JS hooks (the per-row spinner, debug button) keep working.
        $stats = $this->ewww_attachment_stats( $id );

        $html  = '<div id="ewww-media-status-' . $id . '" class="ewww-media-status" data-id="' . $id . '">';
        $html .= '<div>' . esc_html__( 'Infinite Uploads (cloud-stored)', 'infinite-uploads' ) . '</div>';

        if ( $stats['rows'] > 0 ) {
            $saved = max( 0, (int) $stats['orig'] - (int) $stats['opt'] );
            $pct   = $stats['orig'] > 0 ? round( ( $saved / $stats['orig'] ) * 100, 1 ) : 0;
            $html .= '<div>' . sprintf(
                /* translators: 1: number of image sizes, 2: humanized bytes, 3: percentage */
                esc_html__( 'Optimized %1$d sizes — saved %2$s (%3$s%%)', 'infinite-uploads' ),
                (int) $stats['rows'],
                esc_html( size_format( $saved ) ),
                esc_html( (string) $pct )
            ) . '</div>';

            if ( $stats['webp_rows'] > 0 ) {
                $html .= '<div>' . sprintf(
                    /* translators: %d: number of WebP sidecars */
                    esc_html__( 'WebP: %d files', 'infinite-uploads' ),
                    (int) $stats['webp_rows']
                ) . '</div>';
            }
        } else {
            $html .= '<div>' . esc_html__( 'Not yet optimized.', 'infinite-uploads' ) . '</div>';
        }

        $html .= '</div>';

        echo $html; // phpcs:ignore WordPress.Security.EscapeOutput
    }

    /**
     * Aggregate optimization stats for an attachment from `wp_ewwwio_images`.
     *
     * @param  int  $id
     *
     * @return array{rows:int, orig:int, opt:int, webp_rows:int}
     */
    private function ewww_attachment_stats( $id ) {
        global $wpdb;
        static $cache = [];

        if ( isset( $cache[ $id ] ) ) {
            return $cache[ $id ];
        }

        $defaults = [ 'rows' => 0, 'orig' => 0, 'opt' => 0, 'webp_rows' => 0 ];
        $table    = $wpdb->prefix . 'ewwwio_images';

        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
            $cache[ $id ] = $defaults;

            return $defaults;
        }

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT
                    COUNT(*) AS rows_count,
                    COALESCE(SUM(orig_size), 0) AS orig,
                    COALESCE(SUM(image_size), 0) AS opt,
                    SUM(CASE WHEN webp_size > 0 AND webp_error = 0 THEN 1 ELSE 0 END) AS webp_rows
                FROM {$table}
                WHERE attachment_id = %d",
                $id
            ),
            ARRAY_A
        );

        $stats = $row ? [
            'rows'      => (int) $row['rows_count'],
            'orig'      => (int) $row['orig'],
            'opt'       => (int) $row['opt'],
            'webp_rows' => (int) $row['webp_rows'],
        ] : $defaults;

        $cache[ $id ] = $stats;

        return $stats;
    }

    /**
     * ShortPixel compatibility: tell ShortPixel that iu:// paths are valid stateless
     * remote files (via PHP stream wrapper). ShortPixel's `pathIsUrl()` flags every
     * string containing `://` as a URL and routes the FileModel through `UrlToPath()`,
     * which leaves `exists`/`is_readable`/`is_file` all false unless this filter claims
     * the URL. Returning the integer VIRTUAL_STATELESS value (2) — same constant
     * ShortPixel uses internally — sets exists=true, is_readable=true, is_file=true,
     * is_writable=true, and lets ShortPixel proceed to the actual read/copy/optimize
     * operations, all of which work transparently against iu:// via the stream wrapper.
     *
     * Hooked to `shortpixel/image/urltopath`.
     *
     * @param  mixed   $result   Current handler result. False if no handler claimed it.
     * @param  string  $url      The URL/path being resolved.
     * @param  string  $rawpath  The original raw path (pre-stripping).
     *
     * @return mixed
     */
    public function shortpixel_urltopath( $result, $url, $rawpath ) {
        if ( $result !== false ) {
            return $result; // another handler already claimed it
        }
        if ( strpos( (string) $url, 'iu://' ) === 0 || strpos( (string) $rawpath, 'iu://' ) === 0 ) {
            // VIRTUAL_STATELESS = 2 (FileModel::$VIRTUAL_STATELESS).
            return 2;
        }

        return $result;
    }

    /**
     * ShortPixel compatibility: populate FileModel::$filesize for iu:// virtual files,
     * so getFileSize() returns the real size instead of the -1 sentinel that virtual
     * files normally yield. Without this, FileModel::copy() bails at the
     * `getFileSize() <= 0` guard for any iu:// source — even though the file exists
     * and PHP's copy() can read it via the stream wrapper.
     *
     * Hooked to `shortpixel/file/exists` (which is the only filter giving us the
     * FileModel instance early enough). Uses reflection because $filesize is
     * protected; populates once per FileModel and short-circuits on subsequent calls.
     *
     * @param  bool    $exists    Current exists value.
     * @param  string  $fullpath  Full file path.
     * @param  mixed   $file      ShortPixel FileModel.
     *
     * @return bool
     */
    public function shortpixel_prefill_filesize( $exists, $fullpath, $file ) {
        if ( ! is_object( $file ) || strpos( (string) $fullpath, 'iu://' ) !== 0 ) {
            return $exists;
        }

        try {
            $reflection = new \ReflectionObject( $file );
            if ( ! $reflection->hasProperty( 'filesize' ) ) {
                return $exists;
            }

            $prop = $reflection->getProperty( 'filesize' );
            $prop->setAccessible( true );
            if ( $prop->getValue( $file ) !== null ) {
                return $exists; // already populated this request
            }

            $size = @filesize( $fullpath ); // hits stream wrapper, cached per-request
            if ( $size !== false && $size > 0 ) {
                $prop->setValue( $file, (int) $size );
            }
        } catch ( \Throwable $e ) {
            // Reflection or stat failure — leave the FileModel as-is.
        }

        return $exists;
    }

    /**
     * ShortPixel compatibility: redirect the backup directory to local disk.
     *
     * ShortPixel computes its backup base from `wp_get_upload_dir()['basedir']`, which
     * IU has filtered to `iu://bucket`. ShortPixel then tries to create the backup
     * directory and copy the original file there before optimizing — but on stream
     * wrappers, mkdir/chmod/is_writable behave differently than ShortPixel's
     * DirectoryModel::check() expects, so the backup write fails with the user-facing
     * "Could not create backup. Please check file permissions" message.
     *
     * Strategy: build the same `<base>/ShortpixelBackups/<relative_path>` structure
     * but rooted at the *original local* uploads basedir, not the iu:// override.
     * `/ShortpixelBackups/` is already added to `infinite_uploads_sync_exclusions`
     * via `compatibility_exclusions()`, so these backups stay local and never get
     * pushed to cloud.
     *
     * Hooked to `shortpixel/file/backup_folder`.
     *
     * @param  mixed  $directory  ShortPixel DirectoryModel for the computed backup folder.
     * @param  mixed  $file       ShortPixel FileModel for the source image.
     *
     * @return mixed  Replacement DirectoryModel pointing at a local-disk backup folder,
     *                or the original $directory if we can't safely override.
     */
    public function shortpixel_backup_folder( $directory, $file ) {
        if ( ! function_exists( 'wpSPIO' ) || ! is_object( $directory ) || ! method_exists( $directory, 'getPath' ) ) {
            return $directory;
        }

        $current_path = (string) $directory->getPath();
        $marker       = 'ShortpixelBackups';

        $idx = strrpos( $current_path, $marker );
        if ( $idx === false ) {
            return $directory;
        }

        // Everything after `ShortpixelBackups/` — preserves ShortPixel's own subdir layout.
        $relative_subdir = ltrim( substr( $current_path, $idx + strlen( $marker ) ), '/' . DIRECTORY_SEPARATOR );

        $local_uploads_root = $this->get_original_upload_dir_root();
        if ( empty( $local_uploads_root['basedir'] ) ) {
            return $directory;
        }

        $local_backup_path = trailingslashit( $local_uploads_root['basedir'] )
                             . $marker
                             . ( $relative_subdir !== '' ? '/' . $relative_subdir : '/' );

        // Already pointing at the same local target — nothing to do (don't recurse).
        if ( $current_path === $local_backup_path ) {
            return $directory;
        }

        try {
            $fs        = wpSPIO()->filesystem();
            $local_dir = $fs->getDirectory( $local_backup_path );
            if ( method_exists( $local_dir, 'check' ) && $local_dir->check( true ) ) {
                return $local_dir;
            }
        } catch ( \Throwable $e ) {
            // Fall through to original directory on any unexpected ShortPixel error.
        }

        return $directory;
    }

    /**
     * When file exclusion is enabled, convert CDN URLs for excluded paths to local server URLs.
     * If the local file doesn't yet exist (was written to cloud before exclusion was configured),
     * it is copied from the cloud stream to local disk on first access.
     *
     * Hooked to `style_loader_src` and `script_loader_src`.
     *
     * @param  string  $src  Asset URL as registered with wp_enqueue_style/script.
     * @return string
     */
    public function filter_excluded_asset_src( $src ) {
        if ( ! InfiniteUploadsHelper::is_file_exclusion_enabled() ) {
            return $src;
        }

        // Bail early if the URL is not under the uploads directory or the IU CDN.
        // Without this, every enqueued theme/plugin/core asset on the page runs
        // through the exclusion checks below.
        $local_uploads_url = InfiniteUploadsHelper::get_local_upload_url();
        $cloud_uploads_url = InfiniteUploadsHelper::get_cloud_upload_url();
        if ( stripos( $src, $local_uploads_url ) === false
             && stripos( $src, $cloud_uploads_url ) === false
        ) {
            return $src;
        }

        // Strip query string (?ver=...) before path/file comparisons; restore later.
        $query     = '';
        $clean_src = $src;
        if ( false !== strpos( $src, '?' ) ) {
            [ $clean_src, $query ] = explode( '?', $src, 2 );
            $query = '?' . $query;
        }

        // Normalize to https before comparison — the cloud URL is always https
        // but WordPress may enqueue assets with http:// (non-SSL or mixed-content).
        $normalized = set_url_scheme( $clean_src, 'https' );
        $local_url  = InfiniteUploadsHelper::get_valid_file_url( $normalized, true );

        if ( $local_url === $normalized ) {
            return $src;
        }

        // The URL was converted to local — ensure the file exists on disk.
        $local_path = InfiniteUploadsHelper::get_local_path_from_url( $local_url );
        if ( ! file_exists( $local_path ) ) {
            $cloud_path = InfiniteUploadsHelper::get_cloud_file_path( $local_path );
            if ( $cloud_path !== $local_path ) {
                if ( ! is_dir( dirname( $local_path ) ) ) {
                    wp_mkdir_p( dirname( $local_path ) );
                }
                @copy( $cloud_path, $local_path );
            }
        }

        return file_exists( $local_path ) ? $local_url . $query : $src;
    }

    /**
     * Get the iu:// basedir (e.g. "iu://bucket-name/path/to/uploads").
     *
     * @return string|false
     */
    private function get_s3_basedir() {
        $upload_dir = wp_upload_dir();
        $basedir    = $upload_dir['basedir'];
        if ( strpos( $basedir, 'iu://' ) !== 0 ) {
            return false;
        }

        return untrailingslashit( $basedir );
    }

    /**
     * Handle compatibility for various third party plugins
     */
    function plugin_compatibility() {
        //WPCF7 form file uploads
        if ( ! defined( 'WPCF7_UPLOADS_TMP_DIR' ) ) {
            define( 'WPCF7_UPLOADS_TMP_DIR', WP_CONTENT_DIR . '/wpcf7_uploads' );
        }

        //WP Migrate DB
        add_filter( 'wpmdb_upload_info', array( $this, 'wpmdb_upload_info' ) );

        // Smush Pro: redirect excluded file sizes to local paths.
        add_filter( 'wp_smush_media_item_size', [ $this, 'filter_smush_media_item_size' ], 10, 4 );

        // EWWW Image Optimizer: provide local copies of iu:// files for optimization.
        add_filter( 'ewww_image_optimizer_remote_fetched', [ $this, 'ewww_remote_fetch' ], 10, 3 );
        add_action( 'ewww_image_optimizer_after_optimize_attachment', [ $this, 'ewww_remote_push' ], 10, 2 );

        // EWWW Image Optimizer: tell EWWW that the IU CDN hosts images so its
        // Picture_Webp / JS_Webp rewriters actually consider our URLs.
        add_filter( 'webp_allowed_urls', [ $this, 'ewww_allowed_urls' ] );
        add_filter( 'webp_allowed_domains', [ $this, 'ewww_allowed_domains' ] );

        // EWWW's non-forced rewrite path calls url_to_path_exists() → is_file() on the
        // local disk, which fails for iu:// (the stream wrapper is rejected by is_file).
        // Turn `webp_force` on at runtime so EWWW uses the allowed_urls match path
        // instead of the local-disk check. Non-persistent: doesn't touch the DB option.
        add_filter( 'pre_option_ewww_image_optimizer_webp_force', [ $this, 'ewww_force_webp_at_runtime' ] );

        // With webp_force on, EWWW would blindly wrap every CDN image in a <picture>
        // tag — including ones EWWW never converted, which would 404 on fetch. Opt out
        // per-image by consulting wp_ewwwio_images.
        add_filter( 'ewww_image_optimizer_skip_webp_rewrite', [ $this, 'ewww_skip_webp_rewrite' ], 10, 2 );

        // EWWW's Media Library "Image Optimizer" column emits "Could not retrieve file
        // path" for iu://-hosted attachments because its hard-coded CDN recognition
        // (Amazon_S3_And_CloudFront / S3_Uploads / WP Stateless / Azure) doesn't include
        // Infinite Uploads, and ewwwio_is_file() rejects any path containing '://'. We
        // wrap EWWW's column callback in an output buffer and rewrite that specific
        // error into a useful IU-aware status read from wp_ewwwio_images.
        add_action( 'manage_media_custom_column', [ $this, 'ewww_column_buffer_start' ], 9, 2 );
        add_action( 'manage_media_custom_column', [ $this, 'ewww_column_buffer_end' ], 11, 2 );

        // ShortPixel: claim iu:// paths so ShortPixel's FileModel treats them as readable
        // virtual files. ShortPixel's FileModel constructor calls pathIsUrl() — which
        // returns true for ANY string containing "://" — and routes the file through
        // UrlToPath(). Without an `shortpixel/image/urltopath` handler returning truthy,
        // ShortPixel sets exists=false, is_readable=false, is_file=false, and every
        // subsequent file op (copy(), getFileSize(), createBackup()) bails out early.
        // Returning VIRTUAL_STATELESS marks the file as a stream-wrapper-backed remote
        // that *is* writable — ShortPixel will then attempt the actual operations,
        // which work because PHP's copy()/file_exists()/filesize() handle iu:// via
        // the stream wrapper.
        add_filter( 'shortpixel/image/urltopath', [ $this, 'shortpixel_urltopath' ], 10, 3 );

        // ShortPixel: populate FileModel::$filesize from the iu:// stream so virtual files
        // don't bail in copy() and other size-aware checks. ShortPixel's getFileSize()
        // returns -1 for any virtual file; copy() then rejects the source ("Source file
        // in copy has a filesize of zero"). There's no `getFileSize` filter, but the
        // `shortpixel/file/exists` filter receives the FileModel instance — we use
        // reflection to set `$filesize` once (cheap once-per-request HEAD via the stream
        // wrapper's own stat cache), so getFileSize()'s first branch returns it directly.
        add_filter( 'shortpixel/file/exists', [ $this, 'shortpixel_prefill_filesize' ], 10, 3 );

        // ShortPixel: redirect backup folder to local disk. ShortPixel computes its backup
        // base as wp_get_upload_dir()['basedir'] . '/ShortpixelBackups', which IU has
        // filtered to iu://bucket — directory creation and copy fail under stream wrappers
        // (the mkdir/chmod/is_writable semantics on iu:// paths don't satisfy ShortPixel's
        // pre-flight check). Override the backup directory to the original local uploads
        // path; /ShortpixelBackups/ is already in IU's sync exclusions, so backups stay
        // local and never get pushed to cloud.
        add_filter( 'shortpixel/file/backup_folder', [ $this, 'shortpixel_backup_folder' ], 10, 2 );

        // Elementor and other plugins that write CSS/JS to iu:// basedir: serve from local URL.
        add_filter( 'style_loader_src', [ $this, 'filter_excluded_asset_src' ] );
        add_filter( 'script_loader_src', [ $this, 'filter_excluded_asset_src' ] );

        // Imagify: support offloaded media and next-gen picture-tag delivery on IU CDN.
        if ( interface_exists( '\Imagify\CDN\PushCDNInterface' ) ) {
            InfiniteUploadsImagify::get_instance()->init();
        }

        //Handle WooCommerce CSV imports
        add_filter( 'woocommerce_product_csv_importer_check_import_file_path', '__return_false' );

        //BuddyPress/BuddyBoss
        $original = $this->get_original_upload_dir();
        if ( ! defined( 'BP_AVATAR_UPLOAD_PATH' ) ) {
            define( 'BP_AVATAR_UPLOAD_PATH', $original['basedir'] );
        }
        if ( ! defined( 'BP_AVATAR_URL' ) ) {
            define( 'BP_AVATAR_URL', $original['baseurl'] );
        }
        add_filter( 'bp_attachments_uploads_dir_get', [ $this, 'bp_attachments_uploads_dir_get' ], 10, 2 );

        // WP Webhooks Pro: its integration modules are PHP files it downloads into
        // uploads/wp-webhooks-pro/ and loads with require_once(). It builds that
        // path from wp_upload_dir(), which IU rewrites to iu:// — and PHP refuses
        // to include through a URL stream wrapper unless allow_url_include is
        // enabled (which it must never be: it would let anyone able to place a
        // .php file in the bucket execute it). Filter the folder base back to the
        // original local uploads path. We hook `get_wpwh_folder/folder_base`
        // rather than the later `get_integrations_folder` filter because
        // get_wpwh_folder() (verified in Pro 6.3.4) runs wp_mkdir_p() and
        // index.php creation immediately after this filter but before the
        // end-of-function ones — hooking here keeps those writes on local disk
        // too, and covers every subfolder WP Webhooks derives from the base.
        // /wp-webhooks-pro/ is in our sync exclusions so the files stay local.
        add_filter( 'wpwhpro/integrations/get_wpwh_folder/folder_base', [ $this, 'wpwh_folder_base' ] );

        // Ajax Load More: its "repeater templates" are PHP files it writes into
        // uploads/alm_templates/ and later loads with include(). It builds that
        // path from wp_upload_dir(), which IU rewrites to iu://, and both halves
        // break there. The write is fopen( $file, 'w+' ) — a mode our stream
        // wrapper rejects — so fopen() returns false and ALM's unchecked
        // fwrite() throws a fatal TypeError while the plugin is activating. The
        // read is the same allow_url_include problem as WP Webhooks Pro above.
        // Filter the repeater directory back to the original local uploads path;
        // alm_get_repeater_path() (verified in ALM 8.0.1) is the only place the
        // plugin calls wp_upload_dir(), so this one filter covers activation,
        // the admin template editor and the front-end alm_loop() alike.
        // /alm_templates/ is in our sync exclusions so the templates stay local.
        add_filter( 'alm_repeater_path', [ $this, 'alm_repeater_path' ] );
        // Ajax Load More Cache add-on: same treatment for its static query
        // cache. See alm_cache_path() for why this one is written against the
        // published filter rather than the (commercial) add-on source.
        add_filter( 'alm_cache_path', [ $this, 'alm_cache_path' ] );

        // WP All Import Pro: its Function Editor writes a PHP file to
        // uploads/wpallimport/functions.php and loads it with require_once() on
        // every admin request that renders an import screen. Same
        // allow_url_include wall as WP Webhooks Pro above, but this one fatals
        // in the admin rather than on the front end, which locks the user out
        // of All Import entirely. `import_functions_file_path` (verified in Pro
        // 4.11) is applied at all four sites that touch the file --
        // setup_allimport_dir()'s @touch, CodeBox::requireFunctionsFile()'s
        // require_once, CodeBox::revertToFunctionsFile()'s rename, and the
        // wp_ajax_save_import_functions save handler -- so this single filter
        // covers create, read, write and revert. The _backup.php sibling is
        // derived from the filtered value, so it follows automatically.
        // /wpallimport/ is in our sync exclusions so the whole tree stays local.
        add_filter( 'import_functions_file_path', [ $this, 'wpai_functions_file_path' ] );

        // Elementor "Apply Website Template" / kit importer: downloads the
        // template kit as a ZIP into uploads/elementor/tmp/ and calls
        // ZipArchive::open() + extractTo() on it. ZipArchive uses libzip's
        // raw filesystem I/O — NOT PHP streams — so it cannot read a file
        // path under our iu:// wrapper. open() fails silently, every
        // subsequent call throws "Invalid or uninitialized Zip object", and
        // Elementor's UI blames "a conflict with one or more third-party
        // plugins" without saying which.
        //
        // Elementor's Uploads_Manager exposes the temp-dir path through the
        // `elementor/files/temp-dir` filter (since 3.7.0) at exactly the
        // one place its whole file-writing pipeline gets the directory from
        // — get_temp_dir() at core/files/uploads-manager.php:406. Route it
        // back to local disk so ZipArchive is happy. `temp-file-path` is
        // filtered on top of that same path so hooking it too is redundant
        // in practice, but covers the case where an add-on / future code
        // path builds a temp path without going through get_temp_dir().
        //
        // /elementor/tmp/ is in our sync exclusions so the extracted kit
        // never lands in the bucket in the first place.
        add_filter( 'elementor/files/temp-dir', [ $this, 'elementor_temp_dir' ] );
        add_filter( 'elementor/files/temp-file-path', [ $this, 'elementor_temp_dir' ] );

        // Koko Analytics writes a per-page-view buffer and aggregator state
        // under uploads/koko-analytics/. Two problems on iu://:
        //   1. Every page view does file_put_contents(FILE_APPEND) — turning
        //      each hit into an S3 round-trip.
        //   2. Koko's "Optimized endpoint" mode installs a standalone PHP
        //      file at ABSPATH/koko-analytics-collect.php that runs WITHOUT
        //      loading WordPress. The upload-dir path is baked into that
        //      file at endpoint-install time via a `define()` in Koko's
        //      own template — so runtime filters on wp_upload_dir / stream
        //      wrappers can't reach it once the endpoint is installed.
        //
        // Koko's get_upload_dir() (src/Resources/functions/collect.php:127)
        // checks defined( 'KOKO_ANALYTICS_UPLOAD_DIR' ) FIRST — before it
        // ever asks wp_upload_dir(). Define it here at plugins_loaded time
        // (both plugins load at priority 10, and 'infinite-uploads' <
        // 'koko-analytics' alphabetically, so we're first), so when Koko's
        // endpoint installer captures the path, it bakes our local value
        // into the standalone endpoint file. From then on every page view
        // — through the optimized endpoint OR the REST fallback — writes
        // to local disk.
        //
        // Note for existing sites that already installed Koko while IU was
        // active: the standalone endpoint file was baked with the iu:// path
        // and this runtime define can't reach it. Users must trigger a
        // one-time regeneration by reactivating Koko Analytics or clicking
        // "Reinstall endpoint" in Koko → Settings → Advanced. New installs
        // get it right on Koko's first activation.
        if ( ! defined( 'KOKO_ANALYTICS_UPLOAD_DIR' ) ) {
            $original = $this->get_original_upload_dir_root();
            define(
                'KOKO_ANALYTICS_UPLOAD_DIR',
                rtrim( $original['basedir'], '/' ) . '/koko-analytics'
            );
        }
    }

    /**
     * If using the "Export" or "Backup" features in WP Migrate DB Pro we will need to write files to the local filesystem.
     * Defines a custom folder to write to.
     */
    function wpmdb_upload_info() {
        return array(
                'path' => WP_CONTENT_DIR . '/wp-migrate-db', // note missing end trailing slash
                'url'  => WP_CONTENT_URL . '/wp-migrate-db', // note missing end trailing slash
        );
    }

    /**
     * Filter BuddyPress uploads dir
     */
    function bp_attachments_uploads_dir_get( $retval, $data ) {
        $attachments_dir = 'buddypress';

        if ( 'dir' === $data ) {
            $retval = $attachments_dir;
        } else {
            $upload_data = $this->get_original_upload_dir_root();

            // Return empty string, if Uploads data are not available.
            if ( ! $upload_data ) {
                return $retval;
            }

            // Build the Upload data array for BuddyPress attachments.
            foreach ( $upload_data as $key => $value ) {
                if ( 'basedir' === $key || 'baseurl' === $key ) {
                    $upload_data[ $key ] = trailingslashit( $value ) . $attachments_dir;

                    // Fix for HTTPS.
                    if ( 'baseurl' === $key && is_ssl() ) {
                        $upload_data[ $key ] = str_replace( 'http://', 'https://', $upload_data[ $key ] );
                    }
                } else {
                    unset( $upload_data[ $key ] );
                }
            }

            // Add the dir to the array.
            $upload_data['dir'] = $attachments_dir;

            if ( empty( $data ) ) {
                $retval = $upload_data;
            } elseif ( isset( $upload_data[ $data ] ) ) {
                $retval = $upload_data[ $data ];
            }
        }

        return $retval;
    }

    /**
     * Map a path under our own iu:// bucket back to its local-disk equivalent.
     *
     * Shared by the compatibility filters for plugins that keep files in
     * wp_upload_dir() but need them on the real filesystem: PHP can't
     * include()/require() through a URL stream wrapper (allow_url_include is
     * off, and must stay off — executing PHP out of the bucket would be RCE),
     * and the wrapper doesn't implement every fopen() mode.
     *
     * Paths that aren't under our bucket are returned untouched, so a directory
     * the site has already relocated via the same filter at an earlier priority
     * passes through. The subpath after the bucket is preserved as-is, which
     * keeps the multisite /sites/{id} segment intact.
     *
     * @param string $path Absolute path, possibly under iu://<bucket>.
     *
     * @return string Local-disk equivalent of $path, or $path unchanged.
     */
    private function cloud_path_to_local( $path ) {
        $cloud_base = 'iu://' . untrailingslashit( $this->bucket );
        if ( 0 !== strpos( (string) $path, $cloud_base ) ) {
            return $path;
        }

        $root_dirs = $this->get_original_upload_dir_root();

        return $root_dirs['basedir'] . substr( $path, strlen( $cloud_base ) );
    }

    /**
     * Map the WP Webhooks Pro content folder from the iu:// stream wrapper back
     * to the original local uploads path.
     *
     * The free WP Webhooks keeps its integrations in its own plugin directory,
     * so that install passes through cloud_path_to_local() untouched.
     *
     * Hooked to `wpwhpro/integrations/get_wpwh_folder/folder_base`.
     *
     * @param string $folder Absolute base path WP Webhooks intends to store/load content from.
     *
     * @return string Local-disk equivalent of $folder, or $folder unchanged.
     */
    public function wpwh_folder_base( $folder ) {
        return $this->cloud_path_to_local( $folder );
    }

    /**
     * Map the Ajax Load More repeater template directory from the iu:// stream
     * wrapper back to the original local uploads path.
     *
     * Covers the Custom Repeaters / ALM Templates add-on too: every branch of
     * its admin save routine builds the target from alm_get_repeater_path().
     * The legacy Custom Repeaters v1 / Unlimited paths live in those add-ons'
     * own plugin directories and pass through untouched.
     *
     * Hooked to `alm_repeater_path`.
     *
     * @param string $path Absolute path Ajax Load More stores and loads repeater templates from.
     *
     * @return string Local-disk equivalent of $path, or $path unchanged.
     */
    public function alm_repeater_path( $path ) {
        return $this->cloud_path_to_local( $path );
    }

    /**
     * Map the Ajax Load More Cache add-on directory from the iu:// stream
     * wrapper back to the original local uploads path.
     *
     * The Cache add-on writes per-query static files under uploads/alm-cache/.
     * Left on iu:// that is at best a cache whose every read is a round trip to
     * object storage — slower than no cache at all, which defeats the reason
     * anyone installs it — and at worst the same fatal as the repeater
     * templates, since this codebase reaches for fopen( $f, 'w+' ) everywhere
     * and our wrapper rejects that mode.
     *
     * NOTE: the Cache add-on is commercial and not on wordpress.org, so unlike
     * alm_repeater_path() this is written against Connekt's published docs for
     * the `alm_cache_path` filter rather than against the add-on source. It is
     * safe either way: a filter nothing ever applies costs nothing, and a cache
     * directory that turns out to live outside the bucket is returned
     * unchanged. Worth re-verifying if we ever get a Pro licence.
     *
     * Hooked to `alm_cache_path`.
     *
     * @param string $path Absolute path the Cache add-on stores cache files in.
     *
     * @return string Local-disk equivalent of $path, or $path unchanged.
     */
    public function alm_cache_path( $path ) {
        return $this->cloud_path_to_local( $path );
    }

    /**
     * Map the WP All Import Pro functions file from the iu:// stream wrapper
     * back to the original local uploads path.
     *
     * WP All Import builds this as uploads/wpallimport/functions.php from
     * wp_upload_dir() and require_once()s it from
     * Wpai\Integrations\CodeBox::requireFunctionsFile() on admin_init, so a
     * cloud-backed path takes down every All Import admin screen with
     * "iu:// wrapper is disabled in the server configuration by
     * allow_url_include=0" rather than failing quietly.
     *
     * The filter also fires on the create/save/revert paths, so the file is
     * authored on local disk in the first place and never lands in the bucket.
     * The free WP All Import has no Function Editor and never applies this
     * filter, so that install is unaffected.
     *
     * Hooked to `import_functions_file_path`.
     *
     * @param string $functions Absolute path to WP All Import's functions.php.
     *
     * @return string Local-disk equivalent of $functions, or $functions unchanged.
     */
    public function wpai_functions_file_path( $functions ) {
        return $this->cloud_path_to_local( $functions );
    }

    /**
     * Map Elementor's uploads temp directory (and any temp file path built
     * from it) back to the local uploads path.
     *
     * Elementor uses this directory as the extract target for kit / website
     * template ZIPs; PHP's ZipArchive::open() cannot read files whose path
     * lives under a stream wrapper, so a cloud-backed temp dir turns every
     * "Apply Website Template" click into the generic
     * "Invalid or uninitialized Zip object" fatal and the surface-level
     * "conflict with one or more third-party plugins" message in Elementor's UI.
     *
     * Hooked to `elementor/files/temp-dir` AND `elementor/files/temp-file-path`.
     *
     * @param string $path Path Elementor is about to extract into or write a temp file at.
     *
     * @return string Local-disk equivalent of $path, or $path unchanged.
     */
    public function elementor_temp_dir( $path ) {
        return $this->cloud_path_to_local( $path );
    }

    /**
     * Merge the user's own excluded-paths list (option `iup_excluded_files`)
     * into the sync-exclusion filter. This is what makes full re-scans stop
     * queuing user-excluded files for upload; per-file un-exclude still
     * re-syncs via process_added_removed_excluded_files() → add_files_to_sync(),
     * which iterates $paths_left directly and does not consult is_excluded(),
     * so re-syncing an un-excluded file still works.
     */
    function user_exclusions( $exclusions ) {
        $user = InfiniteUploadsHelper::get_excluded_paths();
        if ( ! empty( $user ) ) {
            $exclusions = array_merge( $exclusions, $user );
        }

        return $exclusions;
    }

    /**
     * Exclude specific dirs for various plugins
     */
    function compatibility_exclusions( $exclusions ) {
        //BuddyPress
        if ( function_exists( 'bp_is_active' ) ) {
            $exclusions[] = '/avatars/';
            $exclusions[] = '/group-avatars/';
            $exclusions[] = '/blog-avatars/';
            $exclusions[] = '/buddypress/';
        }

        $exclusions[] = '/bb-plugin/';
        $exclusions[] = '/ShortpixelBackups/';
        // WP Webhooks Pro downloads PHP integration modules here; they must stay
        // local because PHP can't require_once() through the iu:// wrapper. See
        // wpwh_folder_base().
        $exclusions[] = '/wp-webhooks-pro/';
        // Ajax Load More writes PHP repeater templates here; they must stay
        // local because PHP can't include() through the iu:// wrapper. See
        // alm_repeater_path().
        $exclusions[] = '/alm_templates/';
        // Ajax Load More Cache add-on's per-query static cache — local-only,
        // and pointless to sync. See alm_cache_path().
        $exclusions[] = '/alm-cache/';
        // WP All Import Pro's working tree. functions.php must stay local
        // because PHP can't require_once() through the iu:// wrapper (see
        // wpai_functions_file_path()), and the sibling logs/, files/, temp/,
        // uploads/ and history/ folders are import scratch space: chunked
        // writes, large source files and per-run logs that are churn to sync
        // and are deleted again once the import finishes.
        $exclusions[] = '/wpallimport/';
        // Elementor's kit / website template import extracts a ZIP here.
        // The extract path is already routed to local disk via the
        // elementor/files/temp-dir filter (see elementor_temp_dir()); this
        // exclusion is the belt to that suspenders — makes sure a stray
        // scan doesn't try to sync a half-extracted kit tree mid-import.
        // Only /tmp/ is excluded; Elementor's persistent subfolders under
        // /elementor/ (css/, thumbs/, animations/) legitimately want CDN
        // delivery and stay in the sync scope.
        $exclusions[] = '/elementor/tmp/';
        // Koko Analytics's per-page-view buffer + aggregator state. Never
        // wanted in the bucket — the KOKO_ANALYTICS_UPLOAD_DIR define at
        // plugin init keeps writes local, and this exclusion prevents any
        // stray scan-time sweep from grabbing the transient buffer files.
        $exclusions[] = '/koko-analytics/';

        return $exclusions;
    }
}

/**
 * Check if a file is already offloaded to S3.
 *
 * @param string $url The URL of the file to check.
 *
 * @return bool True if the file is offloaded, false otherwise.
 */
function infinite_uploads_check_offloaded( $url ) {
    global $wpdb;
    $parsed = wp_parse_url( $url );

    if ( isset( $parsed['path'] ) ) {
        // Check if the file is already offloaded to S3.
        $total     = $wpdb->get_row( "SELECT * FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE file LIKE '%{$parsed['path']}%'" );
        if($total && isset( $total->synced ) && $total->synced == 1 ) {
            return true;
        } else {
            return false;

        }
    } else {
        return false;
    }
}


/**
 * Fix to not sync the WooCommerce Error Log Directory.
 *
 * @param  string  $dir  Path to the wc-logs file.
 */
function infinite_uploads_wc_uploads( $dir ) {
    $dir = WP_CONTENT_DIR . '/uploads/wc-logs';

    return $dir;
}

add_filter( 'woocommerce_log_directory', '\ClikIT\InfiniteUploads\infinite_uploads_wc_uploads' );


/**
 * Fix to allow CSV exports from WooCommerce
 */
add_action( 'admin_init', '\ClikIT\InfiniteUploads\wc_iu_export_fix' );

function wc_iu_export_fix() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX && current_user_can( 'manage_options' ) ) {
        if ( isset( $_POST['action'] ) && $_POST['action'] == 'woocommerce_do_ajax_product_export' && class_exists( '\ClikIT\InfiniteUploads\InfiniteUploads' ) ) {
            remove_filter( 'upload_dir', array( InfiniteUploads::get_instance(), 'filter_upload_dir' ), 1 );
        }
    }

    if ( isset( $_GET['page'] ) && $_GET['page'] == 'product_exporter' ) {
        if ( class_exists( '\ClikIT\InfiniteUploads\InfiniteUploads' ) ) {
            remove_filter( 'upload_dir', array( InfiniteUploads::get_instance(), 'filter_upload_dir' ), 1 );
        }
    }
}



/**
 * Fix Complainz plugin error.
 */
function infinite_uploads_complainz_fix() {
    if ( is_plugin_active( 'complianz-gdpr/complianz-gpdr.php' ) ) {
        $file_path  = WP_CONTENT_DIR . '/uploads/complianz/maxmind/GeoLite2-Country.mmdb';
        $upload_dir = WP_CONTENT_DIR . '/uploads/complianz/maxmind/';
        if ( ! is_dir( $upload_dir ) ) {
            mkdir( $upload_dir, 0755, true );
        }
        $name             = 'GeoLite2-Country.tar.gz';
        $tar_file_name    = str_replace( '.gz', '', $name );
        $result_file_name = str_replace( '.tar.gz', '.mmdb', $name );
        $unzipped         = $upload_dir . $result_file_name;
        $db_url           = 'https://cookiedatabase.org/maxmind/GeoLite2-Country.tar.gz';
        $zip_file_name    = apply_filters( 'cmplz_zip_file_path', $upload_dir . $name );
        if ( ! file_exists( $file_path ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            $name    = 'GeoLite2-Country.tar.gz';
            $tmpfile = download_url( $db_url, $timeout = 25 );
            if ( ! file_exists( $zip_file_name ) ) {
                copy( $tmpfile, $zip_file_name );
            }
            try {
                $phar = new \PharData( $zip_file_name );
                $phar->extractTo( $upload_dir );
            } catch ( Exception $e ) {
            }
            foreach ( glob( $upload_dir . "*" ) as $file ) {
                if ( is_dir( $file ) ) {
                    copy( trailingslashit( $file ) . $result_file_name, $upload_dir . $result_file_name );
                    unlink( trailingslashit( $file ) . $result_file_name );
                    foreach ( glob( $file . '/*' ) as $txt_file ) {
                        unlink( $txt_file );
                    }
                    rmdir( $file );
                }
            }
            update_option( 'cmplz_geo_ip_file', WP_CONTENT_DIR . '/uploads/complianz/maxmind/GeoLite2-Country.mmdb' );
            if ( file_exists( $zip_file_name ) ) {
                unlink( $zip_file_name );
            }

            if ( file_exists( $tar_file_name ) ) {
                unlink( $tar_file_name );
            }
        }
    }
}

add_action( 'init', '\ClikIT\InfiniteUploads\infinite_uploads_complainz_fix' );

/**
 * Exclude beaver builder cache directories from being synced.
 *
 * @param  array  $dir_info  Directory information array containing 'path' and 'url'.
 *
 * @return array
 */
function infinite_uploads_filter_bb_cache_dir( $dir_info ) {
    $dir_info = array(
            'path' => WP_CONTENT_DIR . '/uploads/bb-plugin/cache/',
            'url'  => WP_CONTENT_URL . '/uploads/bb-plugin/cache/',
    );

    return $dir_info;
}

add_filter( 'fl_builder_get_cache_dir', '\ClikIT\InfiniteUploads\infinite_uploads_filter_bb_cache_dir', 999 );

// Beaver Builder cache integration — extracted to its own file for clarity
// and unit-testability. See inc/bb-cache-integration.php for the photo-cropped
// hook, the cron push handler, and the upgrade backfill handler.
require_once __DIR__ . '/bb-cache-integration.php';


/**
 * Initialize WPForo working directories.
 *
 * @param  array  $dir  Working directories.
 *
 * @return array
 */
function psx_wpforo_init( $dir ) {
    $dir['assets']['dir'] = WP_CONTENT_DIR . '/wpforo/assets/';
    $dir['upload']['dir'] = WP_CONTENT_DIR . '/wpforo/wpforo/';
    $dir['cache']['dir']  = WP_CONTENT_DIR . '/wpforo/cache/';

    return $dir;
}

add_filter( 'wpforo_working_folders', '\ClikIT\InfiniteUploads\psx_wpforo_init', 99, 1 );

/**
 * Initialize WPDiscuz working directories.
 */
function psx_wpdiscuz_init() {
    if ( ( class_exists( 'WpdiscuzCore' ) && is_single() ) || defined( 'PT_CV_PATH' ) ) {
        remove_filter( 'upload_dir', array( InfiniteUploads::get_instance(), 'filter_upload_dir' ), 1 );
    }
}

add_action( 'template_redirect', '\ClikIT\InfiniteUploads\psx_wpdiscuz_init' );

// Disable Smush filter on Media Library.
add_action( 'admin_init', '\ClikIT\InfiniteUploads\disable_smush_on_media_library' );

function disable_smush_on_media_library() {
    if ( class_exists( '\Smush\App\Media_Library' ) ) {
        $wp_smush               = \WP_Smush::get_instance();
        $media_library_instance = $wp_smush->library();

        if ( $media_library_instance instanceof \Smush\App\Media_Library ) {
            remove_filter( 'wp_prepare_attachment_for_js', array( $media_library_instance, 'smush_send_status' ), 99 );
        }
    }
}

// EWWW Image Optimizer integration — extracted to its own file for clarity
// and so it can be unit-tested in isolation. See inc/ewww-integration.php
// for the full docblock + reasoning.
require_once __DIR__ . '/ewww-integration.php';
