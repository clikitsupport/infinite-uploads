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
			'description'   => __( 'Returns the connection state, plan, CDN URL, feature flags, file sync statistics, background job state, and the next action needed to finish setup for this site. Call this first to learn what Infinite Uploads can do here, whenever someone asks how a transfer is going, and any time you pick up a site you have not seen before — next_step tells you exactly where it was left off, including when a finished sync is waiting for someone to approve switching the site over.', 'infinite-uploads' ),
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
					'jobs'                       => [
						'type'       => 'object',
						'properties' => [
							'scan_in_progress'   => [ 'type' => 'boolean' ],
							'sync_queued'        => [ 'type' => 'boolean' ],
							'sync_complete'      => [ 'type' => 'boolean' ],
							'sync_remaining'     => [ 'type' => 'integer' ],
							'download_queued'    => [ 'type' => 'boolean' ],
							'download_complete'  => [ 'type' => 'boolean' ],
							'download_remaining' => [ 'type' => 'integer' ],
							'wp_cron_disabled'   => [ 'type' => 'boolean' ],
						],
					],
					'next_step_detail'           => [
						'type'        => 'string',
						'description' => __( 'The same pending action written out for the agent, naming the exact ability to run. Relay it rather than acting past it — when it says to ask the site owner first, ask.', 'infinite-uploads' ),
					],
					'next_step'                  => [
						'type'        => 'string',
						'enum'        => [ 'connect', 'scan', 'sync', 'enable', 'done' ],
						'description' => __( 'The one action still needed to finish setting this site up: connect the site to an account, scan local files, sync them to the cloud, enable cloud serving, or done. Offloading is deliberately two steps — move the files, then switch the site over — so this reports "enable" once a sync has finished and is waiting on a person to approve the switch.', 'infinite-uploads' ),
					],
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
			'description'   => __( 'Runs one time-boxed pass of the local uploads filesystem scan and records the results in the file table. Each pass is bounded by a time limit, so large libraries need many passes: if the response reports done=false, simply call again — the scan resumes automatically where it left off, no arguments needed. Works before the site is connected. Refuses if a previous scan already produced file data and no scan is in progress, because starting over discards that tracking data; use rescan for that deliberately. The remote cloud comparison pass is not included; use the admin UI or WP-CLI for that after reconnecting an existing site.', 'infinite-uploads' ),
			'input_schema'  => [
				'type'                 => 'object',
				'additionalProperties' => false,
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

		$this->register_ability( 'rescan', [
			'label'         => __( 'Restart the Local File Scan', 'infinite-uploads' ),
			'description'   => __( 'Discards all local file tracking data and starts the scan over from the beginning, then runs the first time-boxed pass. Continue with the scan ability until it reports done. This is destructive: it empties the file tracking table, which loses the record of which files are already synced, and permanently forgets cloud-only files whose local copies were removed to free up storage — a local scan cannot rediscover those. Only use it when the tracking data is known to be wrong; a normal interrupted scan should be continued with the scan ability instead.', 'infinite-uploads' ),
			'input_schema'  => [
				'type'                 => 'object',
				'additionalProperties' => false,
			],
			'output_schema' => [
				'type'       => 'object',
				'properties' => [
					'done'                  => [ 'type' => 'boolean' ],
					'files_found_this_pass' => [ 'type' => 'integer' ],
					'sync'                  => [ 'type' => 'object' ],
				],
			],
			'execute_callback' => [ $this, 'rescan' ],
			'annotations'      => [
				'readonly'    => false,
				'destructive' => true,
				'idempotent'  => false,
			],
		] );

		$this->register_ability( 'sync', [
			'label'         => __( 'Sync Files to the Cloud', 'infinite-uploads' ),
			'description'   => __( 'Queues the background upload of scanned local files to the Infinite Uploads cloud and starts the queue working. It returns as soon as the job is running — it does not wait for the transfer, which continues on the server whether or not this conversation stays open. Large libraries routinely take hours, so do not sit in a polling loop: tell the person how much there is to move (get-status reports total_size and total_files), that they can close the conversation, and that they can ask for progress whenever they like. Syncing does NOT switch the site over to serving media from the cloud. That stays a separate, deliberate step, exactly as it is on the plugin\'s own screen where an Enable button appears once the transfer finishes: when the sync completes, get-status reports next_step as "enable" — offer it to the person then, and call toggle-cloud only if they agree. If progress stalls and get-status reports jobs.wp_cron_disabled, the host is not running WP-Cron and `wp infinite-uploads sync` is the reliable path; it also transfers everything in one process and is far faster for bulk migrations. Requires a connected site and completed scan data.', 'infinite-uploads' ),
			'input_schema'  => [
				'type'                 => 'object',
				'additionalProperties' => false,
			],
			'output_schema' => [
				'type'       => 'object',
				'properties' => [
					'queued'           => [ 'type' => 'boolean' ],
					'already_queued'   => [ 'type' => 'boolean' ],
					'sync'             => [ 'type' => 'object' ],
					'next_step_detail' => [ 'type' => 'string' ],
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
			'description'   => __( 'Queues the background download of cloud-only files back to the local uploads directory and starts the queue working. It returns as soon as the job is running and does not wait for the transfer, which can take hours on a large library — report that to the person rather than polling in a loop, and check back with get-status, where jobs.download_remaining counts down and jobs.download_complete turns true at the end. The same WP-Cron caveat as sync applies (see jobs.wp_cron_disabled), and `wp infinite-uploads download` is the faster path for bulk restores. Requires a connected site.', 'infinite-uploads' ),
			'input_schema'  => [
				'type'                 => 'object',
				'additionalProperties' => false,
			],
			'output_schema' => [
				'type'       => 'object',
				'properties' => [
					'queued'           => [ 'type' => 'boolean' ],
					'already_queued'   => [ 'type' => 'boolean' ],
					'sync'             => [ 'type' => 'object' ],
					'next_step_detail' => [ 'type' => 'string' ],
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
			'description'   => __( 'Enables or disables serving media from the Infinite Uploads cloud (URL rewriting plus streaming of new uploads). The CDN itself is provisioned and managed by the platform once a site connects; this only switches whether the site uses it. This is the deliberate second half of offloading and the counterpart to the Enable button on the plugin\'s own screen: it is never automatic when a sync finishes, and it changes how media URLs are served across the whole site, so confirm with the person before enabling rather than doing it on their behalf. get-status reports next_step as "enable" when a completed sync is waiting for that decision. Enabling requires a connected site and normally a completed sync.', 'infinite-uploads' ),
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
	 * Whether an Action Scheduler action is scheduled for any of the hooks.
	 *
	 * @param  string[]  $hooks
	 *
	 * @return bool
	 */
	private function is_job_queued( array $hooks ) {
		if ( ! function_exists( 'as_next_scheduled_action' ) ) {
			return false;
		}

		foreach ( $hooks as $hook ) {
			if ( false !== as_next_scheduled_action( $hook ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Background job state, so a polling agent can tell "still working" from
	 * "stalled".
	 *
	 * The sync and download engines run as Action Scheduler jobs, which are
	 * driven by WP-Cron: Action Scheduler's own async runner only dispatches
	 * from admin requests (is_admin()), and REST calls are not admin context,
	 * so nothing an agent does directly advances the queue. Where WP-Cron is
	 * disabled and the host has no system cron calling wp-cron.php, a queued
	 * job never runs at all — hence exposing wp_cron_disabled rather than
	 * leaving the agent to poll unchanging numbers forever.
	 *
	 * @return array
	 */
	private function get_job_state( array $remaining, $has_data ) {
		return [
			'scan_in_progress'  => $this->is_scan_in_progress(),
			'sync_queued'       => $this->is_job_queued( [ 'infinite-uploads-do-sync' ] ),
			'sync_complete'     => $has_data && 0 === $remaining['sync'],
			'sync_remaining'    => $remaining['sync'],
			'download_queued'   => $this->is_job_queued( [
				'infinite-uploads-do-download',
				'infinite-uploads-add-files-to-download',
				'infinite-uploads-fetch-s3-files-from-directory-to-download',
			] ),
			'download_complete'  => $has_data && 0 === $remaining['download'],
			'download_remaining' => $remaining['download'],
			'wp_cron_disabled'   => defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON,
		];
	}

	/**
	 * @return bool
	 */
	private function is_scan_in_progress() {
		return ! empty( get_site_option( 'iup_scan_remaining_dirs', [] ) );
	}

	/**
	 * Files still eligible for transfer, mirroring the queries the sync and
	 * download engines themselves use to decide they are finished (files
	 * retired at errors >= 3 are excluded, exactly as in do_sync/do_download).
	 *
	 * Derived from the file table rather than the iup_do_*_complete options
	 * because those are an acknowledgement flag the admin UI consumes and
	 * resets once a browser has seen the completion
	 * (InfiniteUploads::check_sync_status), so they read "no" again afterwards
	 * — useless to an agent asking hours later whether the transfer finished.
	 *
	 * @return array
	 */
	private function get_remaining_counts() {
		global $wpdb;

		return [
			'sync'     => (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE synced = 0 AND errors < 3" ),
			'download' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->base_prefix}infinite_uploads_files` WHERE synced = 1 AND deleted = 1 AND errors < 3" ),
		];
	}

	/**
	 * The one thing a person needs to do next to finish setting this site up.
	 *
	 * Offloading is deliberately a two-step job — transfer the files, then
	 * switch the site over to serving them — and the second step stays a
	 * human decision, mirroring the plugin's own screen where an Enable
	 * button appears once the sync finishes. In the admin UI that button is
	 * the affordance; an agent has no screen, so this field is the
	 * equivalent: whenever anyone asks about the site, in this conversation
	 * or a different one days later, the pending step is unmistakable.
	 *
	 * @return string One of connect, scan, sync, enable, done.
	 */
	private function get_next_step( $connected, $has_data, $remaining_sync, $cloud_enabled ) {
		if ( ! $connected ) {
			return 'connect';
		}

		if ( ! $has_data || $this->is_scan_in_progress() ) {
			return 'scan';
		}

		if ( $remaining_sync > 0 ) {
			return 'sync';
		}

		return $cloud_enabled ? 'done' : 'enable';
	}

	/**
	 * The same instruction in words, carried in the response body.
	 *
	 * next_step on its own is a bare enum, and the sentence explaining it
	 * lives in the output schema — which many MCP clients never surface to
	 * the model at call time. An agent picking this site up hours later
	 * would see "enable" and have to infer both what it means and that the
	 * ability implementing it is called toggle-cloud. Spelling it out here
	 * removes that inference, and because get-status is what gets polled,
	 * it re-asserts itself on every check — the equivalent of the plugin's
	 * own Enable modal, which reappears until someone acts on it.
	 *
	 * @param  string  $step
	 * @param  int     $remaining_sync
	 *
	 * @return string
	 */
	private function get_next_step_detail( $step, $remaining_sync ) {
		switch ( $step ) {
			case 'connect':
				return __( 'This site is not connected to an Infinite Uploads account yet, and nothing can move to the cloud until it is. Run the infinite-uploads/get-connect-instructions ability and give the person the steps: connecting needs a human in a browser to sign in and approve a plan, and cannot be done for them.', 'infinite-uploads' );

			case 'scan':
				return __( 'The local media library has not been catalogued yet. Run the infinite-uploads/scan ability repeatedly until it reports done=true — each call resumes where the previous one stopped.', 'infinite-uploads' );

			case 'sync':
				return sprintf(
					/* translators: %s: number of files still to upload, already formatted for the locale. */
					__( '%s files are catalogued but have not been uploaded to the cloud yet. Run the infinite-uploads/sync ability to start the transfer. It runs in the background on the site\'s own server and can take hours on a large library, so tell the person it continues without them waiting, and check back with get-status rather than polling in a loop.', 'infinite-uploads' ),
					number_format_i18n( $remaining_sync )
				);

			case 'enable':
				return __( 'The file transfer has finished, but this site is still serving media from its local disk: switching over is a separate step that has not happened yet. That decision belongs to the site owner, not to you. Ask them whether they want to start serving media from the Infinite Uploads cloud and CDN now, and only if they agree, run the infinite-uploads/toggle-cloud ability with enabled=true.', 'infinite-uploads' );

			case 'done':
			default:
				return __( 'Setup is complete: media is stored in the Infinite Uploads cloud and served from the CDN. Nothing further is needed.', 'infinite-uploads' );
		}
	}

	/**
	 * Ask Action Scheduler to start working the queue right now.
	 *
	 * Without this, a queued job waits for WP-Cron: Action Scheduler's own
	 * async runner is dispatched from the shutdown hook only when
	 * is_admin() is true (ActionScheduler_QueueRunner::maybe_dispatch_async_request),
	 * and REST requests are not admin context, so nothing an agent does
	 * advances the queue. Dispatching the runner directly sends a
	 * non-blocking loopback to admin-ajax.php, which IS admin context — so
	 * that request, and each one it spawns on shutdown, keeps the chain
	 * going without a browser and without WP-Cron.
	 *
	 * maybe_dispatch() self-guards: it no-ops unless there is pending work
	 * due and no batch is already running.
	 *
	 * @return void
	 */
	private function kick_queue_runner() {
		if ( ! class_exists( '\ActionScheduler' ) || ! class_exists( '\ActionScheduler_AsyncRequest_QueueRunner' ) ) {
			return;
		}

		try {
			$async = new \ActionScheduler_AsyncRequest_QueueRunner( \ActionScheduler::store() );
			$async->maybe_dispatch();
		} catch ( \Exception $e ) {
			// Best effort: the job stays queued for WP-Cron either way.
			return;
		}
	}

	/**
	 * Connection, plan, and sync status.
	 *
	 * @return array
	 */
	public function get_status() {
		$connected     = $this->api->has_token();
		$cloud_enabled = (bool) infinite_uploads_enabled();
		$stats         = $this->iup_instance->get_sync_stats();
		$has_data      = ! empty( $stats['is_data'] );
		$remaining     = $this->get_remaining_counts();
		$next_step     = $this->get_next_step( $connected, $has_data, $remaining['sync'], $cloud_enabled );

		$status = [
			'connected'                  => $connected,
			'cloud_serving_enabled'      => $cloud_enabled,
			'image_optimization_enabled' => InfiniteUploadsHelper::is_image_optimization_enabled(),
			'business_plan'              => false,
			'plan'                       => null,
			'cdn_url'                    => null,
			'plugin_version'             => INFINITE_UPLOADS_VERSION,
			'sync'                       => $stats,
			'jobs'                       => $this->get_job_state( $remaining, $has_data ),
			'next_step'                  => $next_step,
			'next_step_detail'           => $this->get_next_step_detail( $next_step, $remaining['sync'] ),
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
	 * One time-boxed local filesystem scan pass, resuming automatically.
	 *
	 * Resumability rides on the same iup_scan_remaining_dirs option the admin
	 * UI uses, so a scan started here can be finished from the admin UI and
	 * vice versa.
	 *
	 * Guard: InfiniteUploadsFilelist::start() treats an empty resume list as
	 * "fresh scan" and TRUNCATEs the file table. That is correct for the admin
	 * UI, whose JavaScript drives one fresh pass followed by continue passes
	 * and the remote compare in a single user gesture, but it is a trap for an
	 * agent calling one stateless ability repeatedly: every call would wipe the
	 * table, losing which files are already synced, permanently forgetting
	 * cloud-only files whose local copies were deleted (a local scan cannot
	 * rediscover those), and restarting the scan forever on a large library.
	 * So a scan that has nothing to resume is only allowed to run when there is
	 * no tracked file data to lose; otherwise the caller must choose rescan.
	 *
	 * @return array|WP_Error
	 */
	public function scan() {
		if ( empty( get_site_option( 'iup_scan_remaining_dirs', [] ) ) ) {
			$stats = $this->iup_instance->get_sync_stats();
			if ( ! empty( $stats['is_data'] ) ) {
				return new WP_Error( 'iu_rescan_required', __( 'A scan has already been completed and no scan is in progress, so there is nothing to resume. Starting over would discard the existing file tracking data, including the record of cloud-only files. Use the rescan ability if that is genuinely what you want.', 'infinite-uploads' ) );
			}
		}

		return $this->run_scan_pass( false );
	}

	/**
	 * Discard tracking data and run the first pass of a brand new scan.
	 *
	 * @return array
	 */
	public function rescan() {
		return $this->run_scan_pass( true );
	}

	/**
	 * Shared scan pass.
	 *
	 * @param  bool  $restart  True to start fresh (truncates the file table via
	 *                         InfiniteUploadsFilelist), false to resume.
	 *
	 * @return array
	 */
	private function run_scan_pass( $restart ) {
		$remaining_dirs = $restart ? [] : (array) get_site_option( 'iup_scan_remaining_dirs', [] );

		$path = $this->iup_instance->get_original_upload_dir_root();

		$filelist = new InfiniteUploadsFilelist( $path['basedir'], InfiniteUploadsAdmin::get_instance()->ajax_timelimit, $remaining_dirs );
		$filelist->start();

		if ( $filelist->is_done ) {
			delete_site_option( 'iup_scan_remaining_dirs' );
		} else {
			update_site_option( 'iup_scan_remaining_dirs', $filelist->paths_left );
		}

		return [
			'done' => (bool) $filelist->is_done,
			// file_count, not count( file_list ): flush_to_db() empties
			// file_list as it writes each batch, so by the time start()
			// returns the list holds only what a failed insert left behind.
			'files_found_this_pass' => (int) $filelist->file_count,
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

		$already_queued = $this->is_job_queued( [ 'infinite-uploads-do-sync' ] );
		if ( ! $already_queued ) {
			as_schedule_single_action( time(), 'infinite-uploads-do-sync' );
		}

		$this->kick_queue_runner();

		return [
			// Queued, not started: the transfer runs later, in an Action
			// Scheduler job driven by WP-Cron. See get_job_state().
			'queued'           => true,
			'already_queued'   => $already_queued,
			'sync'             => $stats,
			'next_step_detail' => __( 'The transfer is now running in the background on the site\'s own server and continues whether or not this conversation stays open — tell the person that rather than waiting on it. Check progress with infinite-uploads/get-status. When the transfer finishes, that ability reports next_step as "enable": serving media from the cloud is a separate change the site owner has to agree to, so ask them first and only then run infinite-uploads/toggle-cloud.', 'infinite-uploads' ),
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

		$already_queued = $this->is_job_queued( [ 'infinite-uploads-do-download' ] );
		if ( ! $already_queued ) {
			as_schedule_single_action( time(), 'infinite-uploads-do-download' );
		}

		$this->kick_queue_runner();

		return [
			'queued'           => true,
			'already_queued'   => $already_queued,
			'sync'             => $this->iup_instance->get_sync_stats(),
			'next_step_detail' => __( 'The download is now running in the background on the site\'s own server and continues whether or not this conversation stays open. Check progress with infinite-uploads/get-status, where jobs.download_remaining counts down to zero.', 'infinite-uploads' ),
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
