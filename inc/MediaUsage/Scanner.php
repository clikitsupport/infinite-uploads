<?php
/**
 * Coordinator for the Media Library Usage Scanner feature.
 *
 * Owns all WordPress integration: gating, the Media submenu page, the Media
 * Library "Usage" column, and the AJAX endpoints. Instantiated only when the
 * site is connected to Infinite Uploads; the heavier hooks (column, scan
 * endpoints) register only when the feature toggle is also on. Every state-
 * changing endpoint re-checks connection + feature + nonce + capability
 * server-side, so the UI can never be the only line of defence.
 *
 * @package InfiniteUploads
 */

namespace ClikIT\InfiniteUploads\MediaUsage;

use ClikIT\InfiniteUploads\InfiniteUploads;
use ClikIT\InfiniteUploads\InfiniteUploadsHelper;

/**
 * Class Scanner
 */
class Scanner {

	/**
	 * Singleton instance.
	 *
	 * @var Scanner|null
	 */
	private static $instance = null;

	/**
	 * Admin page hook suffix for the scanner screen.
	 *
	 * @var string
	 */
	private $page_hook = '';

	/**
	 * Request cache of attachment_id => verdict row for column rendering.
	 *
	 * @var array
	 */
	private $item_cache = array();

	/**
	 * Get (and lazily create) the singleton.
	 *
	 * @return Scanner
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Wire hooks. Constructor is only reached when the site is connected
	 * (gated in InfiniteUploads::setup()).
	 */
	private function __construct() {
		// Admin page + the "enable feature" endpoint are available whenever the
		// site is connected, so connected-but-disabled sites get the enable screen.
		add_action( 'admin_menu', array( $this, 'register_admin_page' ) );
		add_action( 'wp_ajax_infinite-uploads-media-usage-enable', array( $this, 'ajax_enable' ) );

		// Feature-on hooks. Gated here (once per request); toggling requires a
		// reload before these register/unregister, matching the folder feature.
		if ( InfiniteUploadsHelper::is_media_usage_scanner_enabled() ) {
			add_filter( 'manage_media_columns', array( $this, 'add_usage_column' ) );
			add_action( 'manage_media_custom_column', array( $this, 'render_usage_column' ), 10, 2 );

			// Batch-load verdicts for the whole Media Library list in one query so
			// the Usage column doesn't issue a query per row.
			add_filter( 'the_posts', array( $this, 'prime_usage_column_cache' ), 10, 2 );

			// Background scan worker (Action Scheduler). Registered outside the
			// admin gate so it also fires during cron/loopback requests.
			add_action( self::SCAN_HOOK, array( $this, 'run_background_scan' ) );

			// Scan control + data endpoints (admin-ajax).
			add_action( 'wp_ajax_infinite-uploads-media-usage-start', array( $this, 'ajax_start' ) );
			add_action( 'wp_ajax_infinite-uploads-media-usage-status', array( $this, 'ajax_status' ) );
			add_action( 'wp_ajax_infinite-uploads-media-usage-step', array( $this, 'ajax_step' ) );
			add_action( 'wp_ajax_infinite-uploads-media-usage-pause', array( $this, 'ajax_pause' ) );
			add_action( 'wp_ajax_infinite-uploads-media-usage-resume', array( $this, 'ajax_resume' ) );
			add_action( 'wp_ajax_infinite-uploads-media-usage-cancel', array( $this, 'ajax_cancel' ) );
			add_action( 'wp_ajax_infinite-uploads-media-usage-ignore', array( $this, 'ajax_ignore' ) );
			add_action( 'wp_ajax_infinite-uploads-media-usage-detail', array( $this, 'ajax_detail' ) );
			add_action( 'wp_ajax_infinite-uploads-media-usage-rescan', array( $this, 'ajax_rescan' ) );

			// CSV export (admin-post so the browser downloads a file).
			add_action( 'admin_post_infinite_uploads_media_usage_export', array( $this, 'handle_export' ) );

			// Diagnostic bug-report download (admin-post so the browser downloads a file).
			add_action( 'admin_post_infinite_uploads_media_usage_bug_report', array( $this, 'handle_bug_report' ) );

			// Possible Duplicate Images sub-feature (registers its own AJAX).
			Duplicates::get_instance();
		}
	}

	/**
	 * Action Scheduler hook for the background scan worker.
	 */
	const SCAN_HOOK = 'infinite-uploads-media-usage-scan';

	/* ---------------------------------------------------------------------
	 * Admin page
	 * ------------------------------------------------------------------- */

	/**
	 * Register the "Media Library Usage" submenu under Media.
	 *
	 * @return void
	 */
	public function register_admin_page() {
		$this->page_hook = add_submenu_page(
			'upload.php',
			__( 'Media Cleanup', 'infinite-uploads' ),
			// Menu title carries a small inline "Beta" badge (self-contained so it
			// needs no admin-menu stylesheet; the page title above stays plain text).
			__( 'Media Cleanup', 'infinite-uploads' ) . ' <span style="display:inline-block;padding:0 6px;font-size:9px;font-weight:600;line-height:16px;text-transform:uppercase;letter-spacing:0.4px;color:#fff;background:#ee7c1e;border-radius:8px;vertical-align:text-top;">' . esc_html__( 'Beta', 'infinite-uploads' ) . '</span>',
			'upload_files',
			'iu-media-usage',
			array( $this, 'render_page' )
		);

		if ( $this->page_hook ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		}
	}

	/**
	 * URL to a file under inc/assets/. Scanner.php lives in inc/MediaUsage/, so
	 * the base is dirname(__FILE__) (= inc/MediaUsage), which plugin_basename
	 * then steps up from to reach inc/ — i.e. inc/assets/<path>.
	 *
	 * @param string $path Path relative to inc/assets/.
	 *
	 * @return string
	 */
	public static function asset_url( $path ) {
		return plugins_url( 'assets/' . ltrim( $path, '/' ), dirname( __FILE__ ) );
	}

	/**
	 * Cache-busting version for an asset: its file mtime, falling back to the
	 * plugin version. Ensures edits to the scanner CSS/JS bust the browser
	 * cache even when the plugin version number hasn't changed.
	 *
	 * @param string $path Path relative to inc/assets/.
	 *
	 * @return string|int
	 */
	private static function asset_ver( $path ) {
		$file = dirname( __DIR__ ) . '/assets/' . ltrim( $path, '/' );
		$mtime = @filemtime( $file ); // phpcs:ignore WordPress.PHP.NoSilentErrors -- missing file falls back below.

		return $mtime ? $mtime : INFINITE_UPLOADS_VERSION;
	}

	/**
	 * Enqueue scanner assets only on the scanner screen.
	 *
	 * @param string $hook Current admin page hook.
	 *
	 * @return void
	 */
	public function enqueue_assets( $hook ) {
		// On the Media Library list screen, load just the CSS so the "Usage"
		// column badges are styled (the column only renders when enabled).
		if ( 'upload.php' === $hook ) {
			if ( InfiniteUploadsHelper::is_media_usage_scanner_enabled() ) {
				wp_enqueue_style( 'iu-media-usage', self::asset_url( 'css/media-usage.css' ), array(), self::asset_ver( 'css/media-usage.css' ) );
			}

			return;
		}

		if ( $hook !== $this->page_hook ) {
			return;
		}

		wp_enqueue_style(
			'iu-media-usage',
			self::asset_url( 'css/media-usage.css' ),
			array(),
			self::asset_ver( 'css/media-usage.css' )
		);

		wp_enqueue_script(
			'iu-media-usage',
			self::asset_url( 'js/media-usage.js' ),
			array( 'jquery' ),
			self::asset_ver( 'js/media-usage.js' ),
			true
		);

		wp_localize_script(
			'iu-media-usage',
			'iu_media_usage',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'iup_media_usage' ),
				// Plain __() (not esc_html__) because these are injected into the
				// DOM via jQuery .text()/alert(), which do not interpret HTML;
				// wp_localize_script already encodes them safely for JS.
				'strings' => array(
					'scanning'     => __( 'Scanning…', 'infinite-uploads' ),
					'rescanning'   => __( 'Rescanning…', 'infinite-uploads' ),
					'paused'       => __( 'Paused', 'infinite-uploads' ),
					'stalled'      => __( 'The scan isn’t progressing. Your server may be blocking background tasks — reload the page to retry, and contact support if it keeps happening.', 'infinite-uploads' ),
					'complete'     => __( 'Scan complete.', 'infinite-uploads' ),
					'error'        => __( 'A server error occurred. The scan was paused; you can resume it.', 'infinite-uploads' ),
					'confirm_run'  => __( 'Start a full Media Library usage scan now?', 'infinite-uploads' ),
					'dup_confirm'  => __( 'Scan the Media Library for possible duplicate images now?', 'infinite-uploads' ),
					'dup_scanning' => __( 'Scanning for duplicates…', 'infinite-uploads' ),
					'verifying'    => __( 'Verifying…', 'infinite-uploads' ),
				),
			)
		);
	}

	/**
	 * Render the scanner screen: connect gate, enable screen, or the scanner.
	 *
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( 'upload_files' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'infinite-uploads' ) );
		}

		// Shared by all branches for the Infinite Uploads logo / brand chrome.
		$assets_url = self::asset_url( '' );

		// Defensive: the menu only registers when connected, but re-check live.
		if ( ! InfiniteUploadsHelper::is_connected() ) {
			require __DIR__ . '/../templates/media-usage-connect.php';

			return;
		}

		if ( ! InfiniteUploadsHelper::is_media_usage_scanner_enabled() ) {
			require __DIR__ . '/../templates/media-usage-enable.php';

			return;
		}

		// The Possible Duplicates tab is admin-only.
		$show_duplicates_tab = current_user_can( 'manage_options' );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab selector.
		$requested_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
		$active_tab    = ( 'duplicates' === $requested_tab && $show_duplicates_tab ) ? 'duplicates' : 'usage';

		if ( 'duplicates' === $active_tab ) {
			Duplicates::get_instance()->render( $assets_url, $show_duplicates_tab );

			return;
		}

		$summary   = Store::get_summary();
		$run       = Store::get_latest_run();
		$can_admin = current_user_can( InfiniteUploads::get_instance()->capability );

		require __DIR__ . '/../templates/media-usage.php';
	}

	/* ---------------------------------------------------------------------
	 * Media Library column (Step 4)
	 * ------------------------------------------------------------------- */

	/**
	 * Add the "Usage" column to the Media Library list table.
	 *
	 * @param array $columns Existing columns.
	 *
	 * @return array
	 */
	public function add_usage_column( $columns ) {
		$columns['iu_media_usage'] = __( 'Usage', 'infinite-uploads' );

		return $columns;
	}

	/**
	 * Render the "Usage" cell for an attachment.
	 *
	 * @param string $column_name Column id.
	 * @param int    $attachment_id Attachment id.
	 *
	 * @return void
	 */
	public function render_usage_column( $column_name, $attachment_id ) {
		if ( 'iu_media_usage' !== $column_name ) {
			return;
		}

		$attachment_id = (int) $attachment_id;
		// array_key_exists (not isset) so a primed "miss" (null) isn't re-queried.
		if ( ! array_key_exists( $attachment_id, $this->item_cache ) ) {
			$this->item_cache[ $attachment_id ] = Store::get_item( $attachment_id );
		}
		$item = $this->item_cache[ $attachment_id ];

		if ( ! $item ) {
			echo '<span class="iu-usage-badge iu-usage-unscanned">' . esc_html__( 'Not scanned', 'infinite-uploads' ) . '</span>';

			return;
		}

		if ( (int) $item->ignored ) {
			echo '<span class="iu-usage-badge iu-usage-ignored">' . esc_html__( 'Ignored', 'infinite-uploads' ) . '</span>';

			return;
		}

		$label = self::status_label( $item->usage_status, (int) $item->reference_count );
		printf(
			'<span class="iu-usage-badge iu-usage-%1$s">%2$s</span>',
			esc_attr( $item->usage_status ),
			esc_html( $label )
		);
	}

	/**
	 * Batch-prime the Usage-column cache for every attachment in a Media Library
	 * list query, in one query, so render_usage_column() reads from cache instead
	 * of querying per row. Runs only on the upload.php screen; returns the posts
	 * untouched (it's a passive `the_posts` filter).
	 *
	 * @param array     $posts Posts returned by the query.
	 * @param \WP_Query $query The query (unused).
	 *
	 * @return array
	 */
	public function prime_usage_column_cache( $posts, $query ) {
		if ( empty( $posts ) || ! is_admin() || 'upload.php' !== ( isset( $GLOBALS['pagenow'] ) ? $GLOBALS['pagenow'] : '' ) ) {
			return $posts;
		}

		$ids = array();
		foreach ( $posts as $post ) {
			if ( isset( $post->post_type, $post->ID ) && 'attachment' === $post->post_type && ! array_key_exists( (int) $post->ID, $this->item_cache ) ) {
				$ids[ (int) $post->ID ] = true;
			}
		}

		$ids = array_keys( $ids );
		if ( empty( $ids ) ) {
			return $posts;
		}

		foreach ( Store::get_items_for_ids( $ids ) as $item ) {
			$this->item_cache[ (int) $item->attachment_id ] = $item;
		}

		// Cache misses as null so render_usage_column() doesn't re-query them.
		foreach ( $ids as $id ) {
			if ( ! array_key_exists( $id, $this->item_cache ) ) {
				$this->item_cache[ $id ] = null;
			}
		}

		return $posts;
	}

	/**
	 * Friendly label for a usage status.
	 *
	 * @param string $status Usage status.
	 * @param int    $reference_count Number of references found.
	 *
	 * @return string
	 */
	public static function status_label( $status, $reference_count = 0 ) {
		switch ( $status ) {
			case Store::STATUS_REFERENCED:
				if ( $reference_count > 0 ) {
					/* translators: %s: number of places the file is used. */
					return sprintf( _n( 'In use (%s place)', 'In use (%s places)', $reference_count, 'infinite-uploads' ), number_format_i18n( $reference_count ) );
				}

				return __( 'In use', 'infinite-uploads' );
			case Store::STATUS_POSSIBLY_UNUSED:
				return __( 'Possibly unused', 'infinite-uploads' );
			case Store::STATUS_BROKEN:
				return __( 'Broken reference', 'infinite-uploads' );
			default:
				return __( 'Unknown', 'infinite-uploads' );
		}
	}

	/**
	 * Translate a stored reference_type machine value to a display label.
	 *
	 * @param string $type Reference type.
	 *
	 * @return string
	 */
	public static function reference_type_label( $type ) {
		$labels = array(
			'featured_image'      => __( 'Featured image', 'infinite-uploads' ),
			'woocommerce_gallery' => __( 'WooCommerce gallery', 'infinite-uploads' ),
			'elementor'           => __( 'Elementor', 'infinite-uploads' ),
			'beaver_builder'      => __( 'Beaver Builder', 'infinite-uploads' ),
			'bricks'              => __( 'Bricks', 'infinite-uploads' ),
			'oxygen'              => __( 'Oxygen', 'infinite-uploads' ),
			'acf'                 => __( 'ACF field', 'infinite-uploads' ),
			'block_image'         => __( 'Block image', 'infinite-uploads' ),
			'gallery'             => __( 'Gallery', 'infinite-uploads' ),
			'content_url'         => __( 'Content URL', 'infinite-uploads' ),
			'rendered_html'       => __( 'Rendered HTML', 'infinite-uploads' ),
			'postmeta'            => __( 'Post meta', 'infinite-uploads' ),
			'option'              => __( 'Option', 'infinite-uploads' ),
			'customizer'          => __( 'Customizer', 'infinite-uploads' ),
			'widget'              => __( 'Widget', 'infinite-uploads' ),
			'term_meta'           => __( 'Term meta', 'infinite-uploads' ),
			'user_meta'           => __( 'User meta', 'infinite-uploads' ),
		);

		return isset( $labels[ $type ] ) ? $labels[ $type ] : ucwords( str_replace( '_', ' ', (string) $type ) );
	}

	/**
	 * Translate a stored source_type (a post type slug, or option/term/user).
	 *
	 * @param string $type Source type.
	 *
	 * @return string
	 */
	public static function source_type_label( $type ) {
		$fixed = array(
			'option'   => __( 'Option', 'infinite-uploads' ),
			'term'     => __( 'Term', 'infinite-uploads' ),
			'user'     => __( 'User', 'infinite-uploads' ),
			'frontend' => __( 'Front-end page', 'infinite-uploads' ),
		);
		if ( isset( $fixed[ $type ] ) ) {
			return $fixed[ $type ];
		}

		$post_type = get_post_type_object( $type );
		if ( $post_type && isset( $post_type->labels->singular_name ) ) {
			return $post_type->labels->singular_name;
		}

		return ucwords( str_replace( array( '_', '-' ), ' ', (string) $type ) );
	}

	/* ---------------------------------------------------------------------
	 * AJAX endpoints
	 * ------------------------------------------------------------------- */

	/**
	 * Enable the feature from the enable screen (admin only).
	 *
	 * @return void
	 */
	public function ajax_enable() {
		check_ajax_referer( 'iup_media_usage', 'nonce' );

		if ( ! current_user_can( InfiniteUploads::get_instance()->capability ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Insufficient permissions.', 'infinite-uploads' ) ) );
		}

		if ( ! InfiniteUploadsHelper::is_connected() ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Connect your site to Infinite Uploads first.', 'infinite-uploads' ) ) );
		}

		InfiniteUploadsHelper::set_media_usage_scanner_setting( 'yes' );
		Schema::maybe_upgrade();

		wp_send_json_success();
	}

	/**
	 * Start a new scan run and queue the background worker.
	 *
	 * @return void
	 */
	public function ajax_start() {
		$this->verify_scan_request();

		// Refuse to start a second scan while one is active: starting again would
		// truncate references and orphan the in-flight run. Cancel first to restart.
		if ( Store::get_active_run() ) {
			wp_send_json_error( array( 'message' => esc_html__( 'A scan is already running. Cancel it before starting a new one.', 'infinite-uploads' ) ) );
		}

		$run_id = Engine::start_run( get_current_user_id() );
		if ( ! $run_id ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Could not start the scan.', 'infinite-uploads' ) ) );
		}

		Diagnostics::info( 'Scan started', array( 'run_id' => (int) $run_id ) );

		$this->queue_worker();

		wp_send_json_success( array( 'run_id' => $run_id ) );
	}

	/**
	 * Poll scan progress. If a run is active but the worker is not scheduled
	 * (e.g. a stalled cron queue), re-queue it so progress survives cron issues.
	 *
	 * @return void
	 */
	public function ajax_status() {
		$this->verify_scan_request();

		$run = Store::get_active_run();
		if ( ! $run ) {
			$latest = Store::get_latest_run();
			wp_send_json_success(
				array(
					'status'  => $latest && Store::RUN_COMPLETED === $latest->status ? 'complete' : 'idle',
					'percent' => $latest && Store::RUN_COMPLETED === $latest->status ? 100 : 0,
				)
			);
		}

		if ( Store::RUN_RUNNING === $run->status && ! $this->worker_scheduled() ) {
			$this->queue_worker();
		}

		wp_send_json_success( Engine::get_progress_snapshot( $run ) );
	}

	/**
	 * Pause the active scan (the worker stops rescheduling itself).
	 *
	 * @return void
	 */
	public function ajax_pause() {
		$this->verify_scan_request();

		$run = Store::get_active_run();
		if ( $run && Store::RUN_RUNNING === $run->status ) {
			Store::update_run( (int) $run->id, array( 'status' => Store::RUN_PAUSED ) );
			$this->unqueue_worker();
		}

		wp_send_json_success();
	}

	/**
	 * Resume a paused scan and re-queue the worker.
	 *
	 * @return void
	 */
	public function ajax_resume() {
		$this->verify_scan_request();

		$run = Store::get_active_run();
		if ( $run && Store::RUN_PAUSED === $run->status ) {
			Store::update_run( (int) $run->id, array( 'status' => Store::RUN_RUNNING ) );
			$this->queue_worker();
		}

		wp_send_json_success();
	}

	/**
	 * Cancel the active scan.
	 *
	 * @return void
	 */
	public function ajax_cancel() {
		$this->verify_scan_request();

		Engine::cancel();
		$this->unqueue_worker();
		wp_send_json_success();
	}

	/**
	 * Re-scan a single attachment on demand.
	 *
	 * @return void
	 */
	public function ajax_rescan() {
		$this->verify_scan_request();

		$attachment_id = isset( $_POST['attachment_id'] ) ? absint( $_POST['attachment_id'] ) : 0;
		if ( ! $attachment_id || ! Engine::rescan_attachment( $attachment_id ) ) {
			wp_send_json_error();
		}

		$item = Store::get_item( $attachment_id );
		wp_send_json_success(
			array(
				'status' => $item ? $item->usage_status : '',
				'label'  => $item ? self::status_label( $item->usage_status, (int) $item->reference_count ) : '',
			)
		);
	}

	/* ---------------------------------------------------------------------
	 * Background worker (Action Scheduler)
	 * ------------------------------------------------------------------- */

	/**
	 * Action Scheduler worker: process a batch, then reschedule until the run
	 * completes, is paused, or is cancelled.
	 *
	 * @return void
	 */
	public function run_background_scan() {
		// Re-check the gate inside the background context (no nonce: not a request).
		if ( ! InfiniteUploadsHelper::is_media_usage_scanner_enabled() ) {
			return;
		}

		$progress = $this->drive_one_batch();

		if ( isset( $progress['status'] ) && 'running' === $progress['status'] ) {
			// Reschedule unconditionally: we are *inside* the running action, so
			// worker_scheduled() would see ourselves and wrongly skip. This is
			// the self-driving background path (matches the do_sync worker).
			$this->schedule_worker();
		}
	}

	/**
	 * Process one scan batch with fatal/exception capture. Shared by the Action
	 * Scheduler worker and the browser-driven step (ajax_step). On failure the
	 * run is paused — so it stops cleanly instead of looping the same error — and
	 * a paused descriptor is returned.
	 *
	 * @return array Progress descriptor.
	 */
	private function drive_one_batch() {
		// Capture fatals (memory/timeout) that would otherwise vanish silently.
		Diagnostics::arm_fatal_handler();

		try {
			$progress = Engine::process_batch_guarded();
		} catch ( \Throwable $e ) {
			$state = get_option( Engine::STATE_OPTION );
			Diagnostics::error(
				'Scan batch failed: ' . $e->getMessage(),
				array(
					'exception' => get_class( $e ),
					'file'      => $e->getFile() . ':' . $e->getLine(),
					'state'     => is_array( $state ) ? $state : null,
				)
			);

			if ( is_array( $state ) && ! empty( $state['run_id'] ) ) {
				Store::update_run( (int) $state['run_id'], array( 'status' => Store::RUN_PAUSED ) );
			}

			return array(
				'status'  => 'paused',
				'message' => __( 'The scan was paused after a server error. You can resume it.', 'infinite-uploads' ),
				'percent' => 0,
			);
		}

		if ( isset( $progress['status'] ) && 'complete' === $progress['status'] ) {
			Diagnostics::info( 'Scan completed', array( 'summary' => Store::get_summary() ) );
		}

		return $progress;
	}

	/**
	 * Drive one scan batch from the browser and return progress. This is the
	 * fallback that keeps scans moving on hosts where WP-Cron / Action Scheduler
	 * is disabled or not running, where the background worker would never fire.
	 *
	 * @return void
	 */
	public function ajax_step() {
		$this->verify_scan_request();

		$run = Store::get_active_run();
		if ( ! $run ) {
			$latest = Store::get_latest_run();
			wp_send_json_success(
				array(
					'status'  => $latest && Store::RUN_COMPLETED === $latest->status ? 'complete' : 'idle',
					'percent' => $latest && Store::RUN_COMPLETED === $latest->status ? 100 : 0,
				)
			);
		}

		// Only a running scan advances; paused/other states just report status.
		if ( Store::RUN_RUNNING !== $run->status ) {
			wp_send_json_success( Engine::get_progress_snapshot( $run ) );
		}

		// Keep a background worker queued too, so the scan can continue if the tab
		// is closed on hosts where WP-Cron is healthy.
		if ( ! $this->worker_scheduled() ) {
			$this->queue_worker();
		}

		$progress = $this->drive_one_batch();

		// Tell the UI whether the background worker can carry the scan if the tab
		// closes. If WP-Cron isn't running, it can't — so the page should ask the
		// user to keep the tab open.
		if ( isset( $progress['status'] ) && 'running' === $progress['status'] ) {
			$progress['background'] = self::cron_appears_stuck() ? 'stuck' : 'ok';
		}

		wp_send_json_success( $progress );
	}

	/**
	 * Heuristic: are WordPress background tasks (WP-Cron) actually running?
	 *
	 * If the earliest-due cron event is overdue by more than a couple of minutes,
	 * WP-Cron isn't firing — commonly because the site is behind HTTP auth /
	 * password protection (which 401s WP-Cron's loopback request), or the host
	 * disabled cron without a replacement. The background scan worker can't run
	 * in that case, so the scan must be driven from the open browser tab.
	 *
	 * @return bool True if cron appears stuck.
	 */
	private static function cron_appears_stuck() {
		if ( ! function_exists( '_get_cron_array' ) ) {
			return false;
		}

		$crons = _get_cron_array();
		if ( empty( $crons ) || ! is_array( $crons ) ) {
			return false;
		}

		$earliest = min( array_keys( $crons ) );

		return ( (int) $earliest < ( time() - 120 ) );
	}

	/**
	 * Schedule the background worker if one is not already pending/running.
	 * Used by start/resume/poll, where avoiding a duplicate is the goal.
	 *
	 * @return void
	 */
	private function queue_worker() {
		if ( ! $this->worker_scheduled() ) {
			$this->schedule_worker();
		}
	}

	/**
	 * Schedule the background worker unconditionally.
	 *
	 * @return void
	 */
	private function schedule_worker() {
		if ( function_exists( 'as_schedule_single_action' ) ) {
			as_schedule_single_action( time(), self::SCAN_HOOK, array(), 'infinite-uploads' );
		}
	}

	/**
	 * Whether the background worker is already scheduled.
	 *
	 * @return bool
	 */
	private function worker_scheduled() {
		return function_exists( 'as_has_scheduled_action' ) && as_has_scheduled_action( self::SCAN_HOOK, array(), 'infinite-uploads' );
	}

	/**
	 * Cancel any scheduled background worker.
	 *
	 * @return void
	 */
	private function unqueue_worker() {
		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			as_unschedule_all_actions( self::SCAN_HOOK, array(), 'infinite-uploads' );
		}
	}

	/* ---------------------------------------------------------------------
	 * CSV export (admin-post)
	 * ------------------------------------------------------------------- */

	/**
	 * Stream the scan results as a CSV download.
	 *
	 * @return void
	 */
	public function handle_export() {
		check_admin_referer( 'iup_media_usage_export' );

		if ( ! current_user_can( 'upload_files' ) || ! InfiniteUploadsHelper::is_media_usage_scanner_enabled() ) {
			wp_die( esc_html__( 'You do not have permission to export scanner results.', 'infinite-uploads' ) );
		}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=media-library-usage-' . gmdate( 'Y-m-d' ) . '.csv' );

		$out = fopen( 'php://output', 'w' );
		Exporter::stream( $out );
		fclose( $out );
		exit;
	}

	/**
	 * Stream a diagnostic bug report as a downloadable JSON file.
	 *
	 * Bundles environment, scanner state and the auto-captured diagnostic log so
	 * an alpha tester can send it to support without having to describe (or
	 * remember) what happened. Secrets are scrubbed in Diagnostics::build_report().
	 *
	 * @return void
	 */
	public function handle_bug_report() {
		check_admin_referer( 'iup_media_usage_bug_report' );

		if ( ! current_user_can( 'upload_files' ) || ! InfiniteUploadsHelper::is_media_usage_scanner_enabled() ) {
			wp_die( esc_html__( 'You do not have permission to download a bug report.', 'infinite-uploads' ) );
		}

		$note = isset( $_POST['note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['note'] ) ) : '';

		$host     = wp_parse_url( network_site_url(), PHP_URL_HOST );
		$host     = $host ? preg_replace( '/[^a-z0-9.\-]/i', '', $host ) : 'site';
		$filename = 'media-cleanup-report-' . $host . '-' . gmdate( 'Y-m-d-His' ) . '.json';

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );

		echo wp_json_encode( Diagnostics::build_report( $note ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		exit;
	}

	/**
	 * Toggle the "ignored" flag for one attachment.
	 *
	 * @return void
	 */
	public function ajax_ignore() {
		$this->verify_scan_request();

		$attachment_id = isset( $_POST['attachment_id'] ) ? absint( $_POST['attachment_id'] ) : 0;
		$ignored       = isset( $_POST['ignored'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['ignored'] ) );

		if ( ! $attachment_id || 'attachment' !== get_post_type( $attachment_id ) ) {
			wp_send_json_error();
		}

		Store::set_ignored( $attachment_id, $ignored );
		wp_send_json_success();
	}

	/**
	 * Return the reference detail (pre-escaped HTML) for one attachment.
	 *
	 * @return void
	 */
	public function ajax_detail() {
		$this->verify_scan_request();

		$attachment_id = isset( $_POST['attachment_id'] ) ? absint( $_POST['attachment_id'] ) : 0;
		if ( ! $attachment_id || 'attachment' !== get_post_type( $attachment_id ) ) {
			wp_send_json_error();
		}

		$references = Store::get_references( $attachment_id );
		$rows       = '';
		foreach ( (array) $references as $ref ) {
			$rows .= sprintf(
				'<tr><td>%1$s</td><td>%2$s</td><td>%3$s</td></tr>',
				esc_html( self::source_type_label( $ref->source_type ) ),
				$ref->source_url ? '<a href="' . esc_url( $ref->source_url ) . '" target="_blank" rel="noopener">' . esc_html( $ref->source_label ) . '</a>' : esc_html( $ref->source_label ),
				esc_html( self::reference_type_label( $ref->reference_type ) )
			);
		}

		if ( '' === $rows ) {
			$rows = '<tr><td colspan="3">' . esc_html__( 'No references recorded.', 'infinite-uploads' ) . '</td></tr>';
		}

		wp_send_json_success( array( 'rows' => $rows ) );
	}

	/**
	 * Shared guard for every scan endpoint: nonce + capability + live
	 * connection + feature-enabled. Dies with a JSON error on any failure.
	 *
	 * @return void
	 */
	private function verify_scan_request() {
		check_ajax_referer( 'iup_media_usage', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Insufficient permissions.', 'infinite-uploads' ) ) );
		}

		if ( ! InfiniteUploadsHelper::is_media_usage_scanner_enabled() ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Media Cleanup is not available.', 'infinite-uploads' ) ) );
		}
	}
}
