<?php
/**
 * WordPress Abilities API integration.
 *
 * Registers a machine-discoverable, capability-checked surface so AI agents
 * and other Abilities API consumers (WP 6.9+, or the Abilities API feature
 * plugin) can inspect and operate Infinite Uploads without resorting to raw
 * option or database writes. Each ability is a thin wrapper over the same
 * logic the admin UI and WP-CLI already use — no new business logic lives
 * here.
 *
 * On WordPress versions without the Abilities API the registration hook
 * never fires and this class is inert.
 */

namespace ClikIT\InfiniteUploads;

use WP_Error;

class InfiniteUploadsAbilities {
	private static $instance;

	/**
	 * @var InfiniteUploads
	 */
	private $iup_instance;

	/**
	 * @var InfiniteUploadsApiHandler
	 */
	private $api;

	public function __construct() {
		$this->iup_instance = InfiniteUploads::get_instance();
		$this->api          = InfiniteUploadsApiHandler::get_instance();

		add_action( 'wp_abilities_api_categories_init', [ $this, 'register_category' ] );
		add_action( 'wp_abilities_api_init', [ $this, 'register_abilities' ] );
	}

	/**
	 * @return InfiniteUploadsAbilities
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new InfiniteUploadsAbilities();
		}

		return self::$instance;
	}

	/**
	 * Register the shared ability category.
	 *
	 * Core only accepts category registrations on the dedicated
	 * wp_abilities_api_categories_init action — registering from
	 * wp_abilities_api_init is rejected with _doing_it_wrong, and every
	 * ability referencing the missing category then fails silently.
	 */
	public function register_category() {
		if ( ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}

		wp_register_ability_category( 'infinite-uploads', [
			'label'       => __( 'Infinite Uploads', 'infinite-uploads' ),
			'description' => __( 'Cloud media offloading, sync, and CDN operations from the Infinite Uploads plugin.', 'infinite-uploads' ),
		] );
	}

	/**
	 * Register all abilities.
	 *
	 * Sync-flow abilities (scan/sync/download/toggle) are main-site only,
	 * mirroring the wp_ajax registrations in InfiniteUploadsAdmin — the sync
	 * engine operates on the network-wide file table and the main site's
	 * uploads root.
	 */
	public function register_abilities() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		$this->register_ability( 'get-status', [
			'label'         => __( 'Get Infinite Uploads Status', 'infinite-uploads' ),
			'description'   => __( 'Returns the connection state, plan, CDN URL, feature flags, and file sync statistics for this site. Call this first to learn what Infinite Uploads can do here.', 'infinite-uploads' ),
			'output_schema' => [
				'type'                 => 'object',
				'properties'           => [
					'connected'                  => [ 'type' => 'boolean' ],
					'cloud_serving_enabled'      => [ 'type' => 'boolean' ],
					'image_optimization_enabled' => [ 'type' => 'boolean' ],
					'business_plan'              => [ 'type' => 'boolean' ],
					'plan'                       => [ 'type' => [ 'object', 'null' ] ],
					'cdn_url'                    => [ 'type' => [ 'string', 'null' ] ],
					'plugin_version'             => [ 'type' => 'string' ],
					'sync'                       => [ 'type' => 'object' ],
				],
				'additionalProperties' => true,
			],
			'execute_callback' => [ $this, 'get_status' ],
			'annotations'      => [
				'readonly'   => true,
				'idempotent' => true,
			],
		] );

		$this->register_ability( 'get-connect-instructions', [
			'label'         => __( 'Get Connect Instructions', 'infinite-uploads' ),
			'description'   => __( 'Explains how to connect this site to the Infinite Uploads cloud. Connecting requires a human: account login, plan selection, and billing consent happen on infiniteuploads.com in a browser and cannot be completed by an agent.', 'infinite-uploads' ),
			'output_schema' => [
				'type'       => 'object',
				'properties' => [
					'connected'    => [ 'type' => 'boolean' ],
					'settings_url' => [ 'type' => 'string' ],
					'instructions' => [
						'type'  => 'array',
						'items' => [ 'type' => 'string' ],
					],
				],
			],
			'execute_callback' => [ $this, 'get_connect_instructions' ],
			'annotations'      => [
				'readonly'   => true,
				'idempotent' => true,
			],
		] );

		$this->register_ability( 'purge-cache', [
			'label'         => __( 'Purge CDN Cache', 'infinite-uploads' ),
			'description'   => __( 'Requests a full-zone CDN cache purge. Requires the Business plan with Image Optimization enabled, and is limited to once per hour. Per-URL purges on media deletion happen automatically on every plan and do not need this ability.', 'infinite-uploads' ),
			'input_schema'  => [
				'type'                 => 'object',
				'additionalProperties' => false,
			],
			'output_schema' => [
				'type'       => 'object',
				'properties' => [
					'purged'  => [ 'type' => 'boolean' ],
					'message' => [ 'type' => 'string' ],
				],
			],
			'execute_callback' => [ $this, 'purge_cache' ],
			'annotations'      => [
				'readonly'   => false,
				'idempotent' => false,
			],
		] );

		if ( ! is_main_site() ) {
			return;
		}

		$this->register_ability( 'scan', [
			'label'         => __( 'Scan Local Files', 'infinite-uploads' ),
			'description'   => __( 'Runs one time-boxed pass of the local uploads filesystem scan and records the results in the file table. If the response reports it is not done, call again with continue=true until it is. Works before the site is connected. The remote cloud comparison pass is not included; use the admin UI or WP-CLI for that after reconnecting an existing site.', 'infinite-uploads' ),
			'input_schema'  => [
				'type'       => 'object',
				'properties' => [
					'continue' => [
						'type'        => 'boolean',
						'default'     => false,
						'description' => __( 'Set true to resume a scan that reported done=false; omit or false to start a fresh scan.', 'infinite-uploads' ),
					],
				],
			],
			'output_schema' => [
				'type'       => 'object',
				'properties' => [
					'done'                  => [ 'type' => 'boolean' ],
					'files_found_this_pass' => [ 'type' => 'integer' ],
					'sync'                  => [ 'type' => 'object' ],
				],
			],
			'execute_callback' => [ $this, 'scan' ],
			'annotations'      => [
				'readonly'   => false,
				'idempotent' => false,
			],
		] );

		$this->register_ability( 'sync', [
			'label'         => __( 'Sync Files to the Cloud', 'infinite-uploads' ),
			'description'   => __( 'Starts (or resumes) the background upload of scanned local files to the Infinite Uploads cloud via Action Scheduler. Returns immediately; poll get-status and watch the sync statistics for progress. Requires a connected site and completed scan data.', 'infinite-uploads' ),
			'input_schema'  => [
				'type'                 => 'object',
				'additionalProperties' => false,
			],
			'output_schema' => [
				'type'       => 'object',
				'properties' => [
					'started'        => [ 'type' => 'boolean' ],
					'already_queued' => [ 'type' => 'boolean' ],
					'sync'           => [ 'type' => 'object' ],
				],
			],
			'execute_callback' => [ $this, 'start_sync' ],
			'annotations'      => [
				'readonly'   => false,
				'idempotent' => true,
			],
		] );

		$this->register_ability( 'download', [
			'label'         => __( 'Download Cloud Files to Local', 'infinite-uploads' ),
			'description'   => __( 'Starts (or resumes) the background download of cloud-only files back to the local uploads directory via Action Scheduler. Returns immediately; poll get-status for progress. Requires a connected site.', 'infinite-uploads' ),
			'input_schema'  => [
				'type'                 => 'object',
				'additionalProperties' => false,
			],
			'output_schema' => [
				'type'       => 'object',
				'properties' => [
					'started'        => [ 'type' => 'boolean' ],
					'already_queued' => [ 'type' => 'boolean' ],
					'sync'           => [ 'type' => 'object' ],
				],
			],
			'execute_callback' => [ $this, 'start_download' ],
			'annotations'      => [
				'readonly'   => false,
				'idempotent' => true,
			],
		] );

		$this->register_ability( 'toggle-cloud', [
			'label'         => __( 'Toggle Cloud Serving', 'infinite-uploads' ),
			'description'   => __( 'Enables or disables serving media from the Infinite Uploads cloud (URL rewriting plus streaming of new uploads). The CDN itself is provisioned and managed by the platform once a site connects; this only switches whether the site uses it. Enabling requires a connected site, normally after a completed sync.', 'infinite-uploads' ),
			'input_schema'  => [
				'type'       => 'object',
				'properties' => [
					'enabled' => [
						'type'        => 'boolean',
						'description' => __( 'True to serve media from the cloud, false to serve from the local uploads directory.', 'infinite-uploads' ),
					],
				],
				'required'   => [ 'enabled' ],
			],
			'output_schema' => [
				'type'       => 'object',
				'properties' => [
					'cloud_serving_enabled' => [ 'type' => 'boolean' ],
				],
			],
			'execute_callback' => [ $this, 'toggle_cloud' ],
			'annotations'      => [
				'readonly'   => false,
				'idempotent' => true,
			],
		] );
	}

	/**
	 * Register one ability under the shared category with shared meta.
	 *
	 * @param  string  $slug  Ability slug without the namespace prefix.
	 * @param  array   $args  Ability args; 'annotations' is lifted into meta.
	 */
	private function register_ability( $slug, array $args ) {
		$annotations = $args['annotations'];
		unset( $args['annotations'] );

		$args['category']            = 'infinite-uploads';
		$args['permission_callback'] = [ $this, 'can_manage' ];
		$args['meta']                = [
			'annotations'  => array_merge( [ 'destructive' => false ], $annotations ),
			'show_in_rest' => true,
			// MCP adapters hide abilities without an explicit public flag.
			'mcp'          => [ 'public' => true ],
		];

		$registered = wp_register_ability( 'infinite-uploads/' . $slug, $args );

		// Registration fails silently (null) on invalid args or duplicate
		// slugs — surface it in debug logs so it can't go unnoticed.
		if ( null === $registered && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf( '[Infinite Uploads] Failed to register ability infinite-uploads/%s', $slug ) );
		}
	}

	/**
	 * Shared permission callback: same capability as the settings screen
	 * (manage_options, or manage_network_options on multisite).
	 *
	 * @return bool
	 */
	public function can_manage() {
		return current_user_can( $this->iup_instance->capability );
	}

	/**
	 * Connection, plan, and sync status.
	 *
	 * @return array
	 */
	public function get_status() {
		$connected = $this->api->has_token();

		$status = [
			'connected'                  => $connected,
			'cloud_serving_enabled'      => (bool) infinite_uploads_enabled(),
			'image_optimization_enabled' => InfiniteUploadsHelper::is_image_optimization_enabled(),
			'business_plan'              => false,
			'plan'                       => null,
			'cdn_url'                    => null,
			'plugin_version'             => INFINITE_UPLOADS_VERSION,
			'sync'                       => $this->iup_instance->get_sync_stats(),
		];

		if ( $connected ) {
			$status['business_plan'] = $this->api->is_business_plan();

			// Cherry-pick plan fields only — the cached site data also carries
			// the storage access key/secret, which must never leave the server.
			$data = $this->api->get_site_data();
			if ( $data && isset( $data->plan ) ) {
				$status['plan'] = [
					'name'             => $data->plan->name ?? $data->plan->label ?? null,
					'storage_limit_gb' => $data->plan->storage_limit ?? null,
				];
			}

			$cdn_url = InfiniteUploadsHelper::get_s3_url();
			if ( $cdn_url ) {
				$status['cdn_url'] = $cdn_url;
			}
		}

		return $status;
	}

	/**
	 * The human handoff for connecting a site.
	 *
	 * @return array
	 */
	public function get_connect_instructions() {
		$connected    = $this->api->has_token();
		$settings_url = InfiniteUploadsAdmin::get_instance()->settings_url();

		if ( $connected ) {
			$instructions = [
				__( 'This site is already connected to the Infinite Uploads cloud. Use get-status to see plan and sync details.', 'infinite-uploads' ),
			];
		} else {
			$instructions = [
				/* translators: %s: URL of the Infinite Uploads settings page in wp-admin. */
				sprintf( __( 'A human with an administrator account must open the Infinite Uploads settings page in a browser: %s', 'infinite-uploads' ), $settings_url ),
				__( 'Click the Connect button. It leads to infiniteuploads.com to sign in or create an account, pick a plan, and approve billing.', 'infinite-uploads' ),
				__( 'After approval the browser is redirected back to wp-admin and the connection completes automatically.', 'infinite-uploads' ),
				__( 'Once connected, agents can finish setup with the scan, sync, and toggle-cloud abilities.', 'infinite-uploads' ),
			];
		}

		return [
			'connected'    => $connected,
			'settings_url' => $settings_url,
			'instructions' => $instructions,
		];
	}

	/**
	 * One time-boxed local filesystem scan pass.
	 *
	 * Mirrors the ajax_filelist loop: resumable via the same
	 * iup_scan_remaining_dirs option, so a scan started here can even be
	 * finished from the admin UI and vice versa.
	 *
	 * @param  array  $input
	 *
	 * @return array
	 */
	public function scan( $input ) {
		$is_continuing  = ! empty( $input['continue'] );
		$remaining_dirs = $is_continuing ? get_site_option( 'iup_scan_remaining_dirs', [] ) : [];

		$path = $this->iup_instance->get_original_upload_dir_root();

		$filelist = new InfiniteUploadsFilelist( $path['basedir'], InfiniteUploadsAdmin::get_instance()->ajax_timelimit, $remaining_dirs );
		$filelist->start();

		if ( $filelist->is_done ) {
			delete_site_option( 'iup_scan_remaining_dirs' );
		} else {
			update_site_option( 'iup_scan_remaining_dirs', $filelist->paths_left );
		}

		return [
			'done'                  => (bool) $filelist->is_done,
			'files_found_this_pass' => count( $filelist->file_list ),
			'sync'                  => $this->iup_instance->get_sync_stats(),
		];
	}

	/**
	 * Start or resume the background cloud sync.
	 *
	 * @return array|WP_Error
	 */
	public function start_sync() {
		if ( ! $this->api->has_token() || ! $this->api->get_site_data() ) {
			return new WP_Error( 'iu_not_connected', __( 'This site is not connected to the Infinite Uploads cloud. Use get-connect-instructions for the connection steps.', 'infinite-uploads' ) );
		}

		$stats = $this->iup_instance->get_sync_stats();
		if ( empty( $stats['is_data'] ) ) {
			return new WP_Error( 'iu_scan_required', __( 'No scanned file data found. Run the scan ability until it reports done, then start the sync.', 'infinite-uploads' ) );
		}

		update_site_option( 'iup_do_sync_complete', 'no' );

		$already_queued = false !== as_next_scheduled_action( 'infinite-uploads-do-sync' );
		if ( ! $already_queued ) {
			as_schedule_single_action( time(), 'infinite-uploads-do-sync' );
		}

		return [
			'started'        => true,
			'already_queued' => $already_queued,
			'sync'           => $stats,
		];
	}

	/**
	 * Start or resume the background download of cloud-only files.
	 *
	 * @return array|WP_Error
	 */
	public function start_download() {
		if ( ! $this->api->has_token() || ! $this->api->get_site_data() ) {
			return new WP_Error( 'iu_not_connected', __( 'This site is not connected to the Infinite Uploads cloud. Use get-connect-instructions for the connection steps.', 'infinite-uploads' ) );
		}

		update_site_option( 'iup_do_download_complete', 'no' );

		$already_queued = false !== as_next_scheduled_action( 'infinite-uploads-do-download' );
		if ( ! $already_queued ) {
			as_schedule_single_action( time(), 'infinite-uploads-do-download' );
		}

		return [
			'started'        => true,
			'already_queued' => $already_queued,
			'sync'           => $this->iup_instance->get_sync_stats(),
		];
	}

	/**
	 * Enable or disable serving media from the cloud.
	 *
	 * Wraps toggle_cloud(), so the platform enable ping and cache flushes
	 * fire exactly as they do from the admin UI and WP-CLI.
	 *
	 * @param  array  $input
	 *
	 * @return array|WP_Error
	 */
	public function toggle_cloud( $input ) {
		$enabled = ! empty( $input['enabled'] );

		if ( $enabled && empty( $this->iup_instance->bucket ) ) {
			return new WP_Error( 'iu_not_connected', __( 'This site is not connected to the Infinite Uploads cloud, so cloud serving cannot be enabled. Use get-connect-instructions for the connection steps.', 'infinite-uploads' ) );
		}

		$this->iup_instance->toggle_cloud( $enabled );

		return [
			'cloud_serving_enabled' => (bool) infinite_uploads_enabled(),
		];
	}

	/**
	 * Full-zone CDN purge with the same gates as the admin UI button.
	 *
	 * @return array|WP_Error
	 */
	public function purge_cache() {
		if ( ! $this->api->has_token() ) {
			return new WP_Error( 'iu_not_connected', __( 'Not connected', 'infinite-uploads' ) );
		}

		if ( ! $this->api->is_business_plan() || ! InfiniteUploadsHelper::is_image_optimization_enabled() ) {
			return new WP_Error( 'iu_requires_business_plan', __( 'CDN purge is available on the Business plan with Image Optimization enabled.', 'infinite-uploads' ) );
		}

		if ( get_site_transient( 'iup_purge_cdn_cooldown' ) ) {
			return new WP_Error( 'iu_purge_cooldown', __( 'The CDN cache was purged recently. Please wait up to an hour before purging again.', 'infinite-uploads' ) );
		}

		$cdn_url = InfiniteUploadsHelper::get_s3_url();
		if ( ! $cdn_url ) {
			return new WP_Error( 'iu_no_cdn_url', __( 'Unable to determine the CDN address for this site.', 'infinite-uploads' ) );
		}

		$this->api->purge( [ trailingslashit( $cdn_url ) . '*' ] );

		set_site_transient( 'iup_purge_cdn_cooldown', time(), HOUR_IN_SECONDS );

		return [
			'purged'  => true,
			'message' => __( 'Purge requested. The CDN cache will rebuild automatically as files are visited.', 'infinite-uploads' ),
		];
	}
}
