<?php
/**
 * Coordinator for the Possible Duplicate Images sub-feature.
 *
 * Owns the duplicate-detection settings, the batched (manual) scan, result
 * caching in per-site options, ignored groups, the on-demand exact-hash
 * verification, and rendering the "Possible Duplicates" tab. Admin-only
 * (manage_options); never runs on upload or on normal page loads.
 *
 * @package InfiniteUploads
 */

namespace ClikIT\InfiniteUploads\MediaUsage;

use ClikIT\InfiniteUploads\InfiniteUploadsHelper;

/**
 * Class Duplicates
 */
class Duplicates {

	/**
	 * Singleton instance.
	 *
	 * @var Duplicates|null
	 */
	private static $instance = null;

	/**
	 * Per-site option keys.
	 */
	const RESULTS_OPTION = 'iup_media_usage_dup_results';
	const STATE_OPTION   = 'iup_media_usage_dup_state';
	const IGNORED_OPTION = 'iup_media_usage_dup_ignored';
	const CHUNK_PREFIX   = 'iup_media_usage_dup_chunk_';

	/**
	 * Hard ceiling for v1 (option-based storage). Larger libraries are asked to
	 * wait for the custom-table version rather than risk a silent failure.
	 */
	const MAX_IMAGES = 50000;

	/**
	 * Cap on stored/rendered groups so neither the option nor the page grows
	 * without bound (groups are stored strongest-first).
	 */
	const MAX_GROUPS = 500;

	/**
	 * Capability required for every duplicate action.
	 */
	const CAP = 'manage_options';

	/**
	 * Get (and lazily create) the singleton.
	 *
	 * @return Duplicates
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register AJAX endpoints. Enable/settings are available to admins so the
	 * feature can be turned on; scan/ignore/verify additionally require the
	 * feature to be enabled (re-checked in each handler).
	 */
	private function __construct() {
		add_action( 'wp_ajax_infinite-uploads-media-usage-dup-enable', array( $this, 'ajax_enable' ) );
		add_action( 'wp_ajax_infinite-uploads-media-usage-dup-settings', array( $this, 'ajax_settings' ) );
		add_action( 'wp_ajax_infinite-uploads-media-usage-dup-scan', array( $this, 'ajax_scan' ) );
		add_action( 'wp_ajax_infinite-uploads-media-usage-dup-ignore', array( $this, 'ajax_ignore' ) );
		add_action( 'wp_ajax_infinite-uploads-media-usage-dup-verify', array( $this, 'ajax_verify' ) );
	}

	/* ---------------------------------------------------------------------
	 * Rendering
	 * ------------------------------------------------------------------- */

	/**
	 * Render the Possible Duplicates tab.
	 *
	 * @param string $assets_url URL to inc/assets/.
	 * @param bool   $show_duplicates_tab Whether the tab is shown (admin).
	 *
	 * @return void
	 */
	public function render( $assets_url, $show_duplicates_tab ) {
		$active_tab = 'duplicates';
		$can_admin  = current_user_can( self::CAP );
		$enabled    = InfiniteUploadsHelper::is_duplicates_enabled();
		$settings   = InfiniteUploadsHelper::get_duplicates_settings();
		$results    = $this->get_results();
		$ignored    = $this->get_ignored();
		$uploaders  = $this->build_uploader_map( $results );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display toggle.
		$show_ignored = isset( $_GET['show_ignored'] ) && '1' === sanitize_key( wp_unslash( $_GET['show_ignored'] ) );

		require __DIR__ . '/../templates/media-usage-duplicates.php';
	}

	/**
	 * Build an attachment_id => uploader display-name map for every member across
	 * the result groups, in two batched queries (post_author lookup + user names),
	 * so the template can show who uploaded each item without per-row queries.
	 *
	 * @param array $results Result set from get_results().
	 *
	 * @return array<int,string>
	 */
	private function build_uploader_map( $results ) {
		$map = array();

		if ( empty( $results['groups'] ) ) {
			return $map;
		}

		$ids = array();
		foreach ( $results['groups'] as $group ) {
			if ( empty( $group['members'] ) ) {
				continue;
			}
			foreach ( $group['members'] as $member ) {
				$ids[] = (int) $member['id'];
			}
		}

		$ids = array_values( array_unique( array_filter( $ids ) ) );
		if ( empty( $ids ) ) {
			return $map;
		}

		global $wpdb;

		// $ids are cast to int, so the IN list is safe to interpolate.
		$in   = implode( ',', array_map( 'intval', $ids ) );
		$rows = $wpdb->get_results( "SELECT ID, post_author FROM {$wpdb->posts} WHERE ID IN ( {$in} )" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- ids cast to int above.

		$by_attachment = array();
		$author_ids    = array();
		foreach ( (array) $rows as $row ) {
			$author                            = (int) $row->post_author;
			$by_attachment[ (int) $row->ID ] = $author;
			if ( $author ) {
				$author_ids[ $author ] = true;
			}
		}

		$names = array();
		if ( ! empty( $author_ids ) ) {
			$users = get_users(
				array(
					'include' => array_keys( $author_ids ),
					'fields'  => array( 'ID', 'display_name' ),
				)
			);
			foreach ( $users as $user ) {
				$names[ (int) $user->ID ] = $user->display_name;
			}
		}

		foreach ( $by_attachment as $attachment_id => $author ) {
			$map[ $attachment_id ] = isset( $names[ $author ] ) ? $names[ $author ] : __( 'Unknown', 'infinite-uploads' );
		}

		return $map;
	}

	/* ---------------------------------------------------------------------
	 * AJAX: enable + settings
	 * ------------------------------------------------------------------- */

	/**
	 * Turn the feature on.
	 *
	 * @return void
	 */
	public function ajax_enable() {
		$this->verify_admin();

		$settings            = InfiniteUploadsHelper::get_duplicates_settings();
		$settings['enabled'] = 'yes';
		InfiniteUploadsHelper::update_duplicates_settings( $settings );

		wp_send_json_success();
	}

	/**
	 * Save settings (also used to disable the feature).
	 *
	 * @return void
	 */
	public function ajax_settings() {
		$this->verify_admin();

		$settings = array(
			'enabled'          => ( isset( $_POST['enabled'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['enabled'] ) ) ) ? 'yes' : 'no',
			'show_weak'        => ( isset( $_POST['show_weak'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['show_weak'] ) ) ) ? 'yes' : 'no',
			'allow_exact_hash' => ( isset( $_POST['allow_exact_hash'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['allow_exact_hash'] ) ) ) ? 'yes' : 'no',
			'batch_size'       => isset( $_POST['batch_size'] ) ? absint( wp_unslash( $_POST['batch_size'] ) ) : 100,
		);

		InfiniteUploadsHelper::update_duplicates_settings( $settings );

		wp_send_json_success();
	}

	/* ---------------------------------------------------------------------
	 * AJAX: scan
	 * ------------------------------------------------------------------- */

	/**
	 * Run one batch of the duplicate scan (manual only).
	 *
	 * @return void
	 */
	public function ajax_scan() {
		$this->verify_enabled();

		$settings = InfiniteUploadsHelper::get_duplicates_settings();
		$batch    = (int) $settings['batch_size'];
		$start    = isset( $_POST['start'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['start'] ) );

		global $wpdb;

		if ( $start ) {
			$this->cleanup_chunks(); // Clear any leftovers from an interrupted run.

			$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%'" );
			if ( $total > self::MAX_IMAGES ) {
				wp_send_json_error(
					array(
						'message' => sprintf(
							/* translators: %s: maximum supported image count. */
							esc_html__( 'This Media Library has more than %s images. Possible Duplicate detection (v1) is built for smaller libraries; large-library support is planned for a future version.', 'infinite-uploads' ),
							number_format_i18n( self::MAX_IMAGES )
						),
					)
				);
			}

			$state = array(
				'cursor'  => 0,
				'total'   => $total,
				'scanned' => 0,
				'chunks'  => 0,
			);
		} else {
			$state = get_option( self::STATE_OPTION );
			if ( ! is_array( $state ) ) {
				wp_send_json_success( array( 'status' => 'idle' ) );
			}
		}

		$result = DuplicateFinder::scan_batch( (int) $state['cursor'], $batch );

		if ( ! empty( $result['error'] ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'A database error interrupted the scan. Please try again.', 'infinite-uploads' ) ) );
		}

		// Write this batch's records as their own option (write-once), instead
		// of rewriting one ever-growing blob each batch.
		if ( ! empty( $result['records'] ) ) {
			update_option( self::CHUNK_PREFIX . (int) $state['chunks'], $result['records'], false );
			$state['chunks'] = (int) $state['chunks'] + 1;
		}
		$state['cursor']  = (int) $result['last_id'];
		$state['scanned'] = (int) $state['scanned'] + (int) $result['processed'];

		if ( $result['done'] ) {
			$records = $this->collect_chunks( (int) $state['chunks'] );
			$groups  = DuplicateFinder::group( $records, 'yes' === $settings['show_weak'] );
			$this->store_results( $groups );
			$this->cleanup_chunks( (int) $state['chunks'] );
			delete_option( self::STATE_OPTION );

			wp_send_json_success(
				array(
					'status'  => 'complete',
					'percent' => 100,
				)
			);
		}

		update_option( self::STATE_OPTION, $state, false );

		$total   = max( 1, (int) $state['total'] );
		$percent = min( 99, max( 1, (int) round( ( (int) $state['scanned'] / $total ) * 100 ) ) );

		wp_send_json_success(
			array(
				'status'  => 'running',
				'percent' => $percent,
				'scanned' => (int) $state['scanned'],
				'total'   => (int) $state['total'],
			)
		);
	}

	/* ---------------------------------------------------------------------
	 * AJAX: ignore + verify
	 * ------------------------------------------------------------------- */

	/**
	 * Add or remove a group from the ignored list.
	 *
	 * @return void
	 */
	public function ajax_ignore() {
		$this->verify_enabled();

		$key     = isset( $_POST['group_key'] ) ? sanitize_text_field( wp_unslash( $_POST['group_key'] ) ) : '';
		$ignored = isset( $_POST['ignored'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['ignored'] ) );

		if ( '' === $key ) {
			wp_send_json_error();
		}

		$list = $this->get_ignored();
		if ( $ignored ) {
			$list[ $key ] = 1;
		} else {
			unset( $list[ $key ] );
		}
		update_option( self::IGNORED_OPTION, $list, false );

		wp_send_json_success();
	}

	/**
	 * Verify whether a group's files are byte-for-byte identical (SHA256).
	 * Runs only on demand for the one selected group.
	 *
	 * @return void
	 */
	public function ajax_verify() {
		$this->verify_enabled();

		$settings = InfiniteUploadsHelper::get_duplicates_settings();
		if ( 'yes' !== $settings['allow_exact_hash'] ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Exact match verification is disabled.', 'infinite-uploads' ) ) );
		}

		$key = isset( $_POST['group_key'] ) ? sanitize_text_field( wp_unslash( $_POST['group_key'] ) ) : '';
		$group = $this->find_group( $key );
		if ( ! $group ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Group not found. Re-run the scan.', 'infinite-uploads' ) ) );
		}

		$hashes  = array();
		$skipped = 0;
		foreach ( $group['members'] as $member ) {
			$path = get_attached_file( (int) $member['id'] );
			$hash = $path ? @hash_file( 'sha256', $path ) : false; // phpcs:ignore WordPress.PHP.NoSilentErrors -- missing/remote file -> false, handled below.
			if ( false === $hash ) {
				$skipped++;
				continue;
			}
			$hashes[] = $hash;
		}

		if ( count( $hashes ) < 2 ) {
			wp_send_json_success(
				array(
					'result'  => 'unknown',
					'message' => esc_html__( 'Could not read enough files in this group to verify.', 'infinite-uploads' ),
				)
			);
		}

		$identical = ( 1 === count( array_unique( $hashes ) ) );

		if ( $identical && 0 === $skipped ) {
			$result  = 'exact';
			$message = esc_html__( 'Exact duplicate — these files are byte-for-byte identical.', 'infinite-uploads' );
		} elseif ( $identical ) {
			$result  = 'exact';
			$message = esc_html__( 'The files that could be read are exact duplicates (some files could not be read).', 'infinite-uploads' );
		} else {
			$result  = 'similar';
			$message = esc_html__( 'Similar file, not an exact duplicate.', 'infinite-uploads' );
		}

		wp_send_json_success(
			array(
				'result'  => $result,
				'message' => $message,
			)
		);
	}

	/* ---------------------------------------------------------------------
	 * Storage helpers
	 * ------------------------------------------------------------------- */

	/**
	 * Cached scan results, or an empty structure.
	 *
	 * @return array{groups:array,summary:array,last_ran:int}
	 */
	public function get_results() {
		$results = get_option( self::RESULTS_OPTION );
		if ( ! is_array( $results ) || ! isset( $results['groups'] ) ) {
			return array(
				'groups'   => array(),
				'summary'  => array(
					'groups' => 0,
					'images' => 0,
				),
				'last_ran' => 0,
			);
		}

		return $results;
	}

	/**
	 * Map of ignored group keys.
	 *
	 * @return array<string,int>
	 */
	public function get_ignored() {
		$list = get_option( self::IGNORED_OPTION, array() );

		return is_array( $list ) ? $list : array();
	}

	/**
	 * Merge all per-batch chunk options back into one records array.
	 *
	 * @param int $count Number of chunks written.
	 *
	 * @return array
	 */
	private function collect_chunks( $count ) {
		$records = array();
		for ( $i = 0; $i < (int) $count; $i++ ) {
			$chunk = get_option( self::CHUNK_PREFIX . $i );
			if ( is_array( $chunk ) ) {
				$records = array_merge( $records, $chunk );
			}
		}

		return $records;
	}

	/**
	 * Delete per-batch chunk options. With no count, reads the stored state's
	 * chunk count (plus a small buffer to catch a crash-window orphan).
	 *
	 * @param int|null $count Number of chunks, or null to derive from state.
	 *
	 * @return void
	 */
	private function cleanup_chunks( $count = null ) {
		if ( null === $count ) {
			$old   = get_option( self::STATE_OPTION );
			$count = ( is_array( $old ) && isset( $old['chunks'] ) ) ? (int) $old['chunks'] + 2 : 0;
		}
		for ( $i = 0; $i < (int) $count; $i++ ) {
			delete_option( self::CHUNK_PREFIX . $i );
		}
	}

	/**
	 * Persist grouped results with a summary + timestamp. Caps the number of
	 * stored groups so the option (and the rendered page) can't grow unbounded.
	 *
	 * @param array $groups Groups from DuplicateFinder::group() (strongest first).
	 *
	 * @return void
	 */
	private function store_results( array $groups ) {
		$total_groups = count( $groups );
		$truncated    = false;
		if ( $total_groups > self::MAX_GROUPS ) {
			$groups    = array_slice( $groups, 0, self::MAX_GROUPS );
			$truncated = true;
		}

		$images = 0;
		foreach ( $groups as $g ) {
			$images += count( $g['members'] );
		}

		update_option(
			self::RESULTS_OPTION,
			array(
				'groups'   => $groups,
				'summary'  => array(
					'groups'       => count( $groups ),
					'images'       => $images,
					'total_groups' => $total_groups,
					'truncated'    => $truncated,
				),
				'last_ran' => time(),
			),
			false
		);
	}

	/**
	 * Find a stored group by its key.
	 *
	 * @param string $key Group key.
	 *
	 * @return array|null
	 */
	private function find_group( $key ) {
		if ( '' === $key ) {
			return null;
		}
		foreach ( $this->get_results()['groups'] as $group ) {
			if ( isset( $group['key'] ) && $group['key'] === $key ) {
				return $group;
			}
		}

		return null;
	}

	/* ---------------------------------------------------------------------
	 * Request guards
	 * ------------------------------------------------------------------- */

	/**
	 * Nonce + admin capability. Dies with a JSON error on failure.
	 *
	 * @return void
	 */
	private function verify_admin() {
		check_ajax_referer( 'iup_media_usage', 'nonce' );

		if ( ! current_user_can( self::CAP ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Insufficient permissions.', 'infinite-uploads' ) ) );
		}
	}

	/**
	 * Admin capability AND the feature must be enabled.
	 *
	 * @return void
	 */
	private function verify_enabled() {
		$this->verify_admin();

		if ( ! InfiniteUploadsHelper::is_duplicates_enabled() ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Possible Duplicate detection is not enabled.', 'infinite-uploads' ) ) );
		}
	}
}
