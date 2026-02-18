<?php

namespace ClikIT\InfiniteUploads;

class MediaFolders {

	private static $instance;

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );

		// AJAX handlers for folder operations.
		add_action( 'wp_ajax_iu_get_folders', [ $this, 'ajax_get_folders' ] );
		add_action( 'wp_ajax_iu_create_folder', [ $this, 'ajax_create_folder' ] );
		add_action( 'wp_ajax_iu_rename_folder', [ $this, 'ajax_rename_folder' ] );
		add_action( 'wp_ajax_iu_delete_folder', [ $this, 'ajax_delete_folder' ] );
		add_action( 'wp_ajax_iu_move_folder', [ $this, 'ajax_move_folder' ] );
		add_action( 'wp_ajax_iu_move_media', [ $this, 'ajax_move_media' ] );
		add_action( 'wp_ajax_iu_sort_folders', [ $this, 'ajax_sort_folders' ] );
		add_action( 'wp_ajax_iu_get_folder_counts', [ $this, 'ajax_get_folder_counts' ] );

		// Filter media library queries.
		add_action( 'pre_get_posts', [ $this, 'filter_media_query' ] );

		// Add folder filter to AJAX queries (grid view).
		add_filter( 'ajax_query_attachments_args', [ $this, 'filter_ajax_attachments_args' ] );

		// Clean up relationships when an attachment is deleted.
		add_action( 'delete_attachment', [ $this, 'on_delete_attachment' ] );
	}

	/**
	 * Get the folders table name.
	 *
	 * @return string
	 */
	private function folders_table() {
		global $wpdb;

		return $wpdb->prefix . 'infinite_uploads_media_folders';
	}

	/**
	 * Get the relationships table name.
	 *
	 * @return string
	 */
	private function relationships_table() {
		global $wpdb;

		return $wpdb->prefix . 'infinite_uploads_media_folder_relationships';
	}

	/**
	 * Enqueue assets only on media library pages.
	 */
	public function enqueue_assets( $hook ) {
		if ( ! in_array( $hook, [ 'upload.php', 'post.php', 'post-new.php' ], true ) ) {
			return;
		}

		$plugin_url = plugins_url( '', dirname( __FILE__ ) );

		// Tailwind CSS (CDN) - skip on WooCommerce product pages to avoid style conflicts.
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || $screen->post_type !== 'product' ) {
			wp_enqueue_script(
				'iu-tailwind',
				'https://cdn.tailwindcss.com',
				[],
				null,
				false
			);
		}

		// Media folders.
		wp_enqueue_style(
			'iu-media-folders',
			$plugin_url . '/inc/assets/css/media-folders.css',
			[],
			INFINITE_UPLOADS_VERSION
		);
		wp_enqueue_script(
			'iu-media-folders',
			$plugin_url . '/inc/assets/js/media-folders.js',
			[ 'jquery' ],
			INFINITE_UPLOADS_VERSION,
			true
		);

		wp_localize_script( 'iu-media-folders', 'iuMediaFolders', [
			'ajax_url'       => admin_url( 'admin-ajax.php' ),
			'nonce'          => wp_create_nonce( 'iu_media_folders' ),
			'all_label'      => __( 'All Files', 'infinite-uploads' ),
			'uncat_label'    => __( 'Uncategorized', 'infinite-uploads' ),
			'new_folder'     => __( 'New Folder', 'infinite-uploads' ),
			'new_subfolder'  => __( 'New Subfolder', 'infinite-uploads' ),
			'rename'         => __( 'Rename', 'infinite-uploads' ),
			'cut'            => __( 'Cut', 'infinite-uploads' ),
			'paste'          => __( 'Paste', 'infinite-uploads' ),
			'delete'         => __( 'Delete', 'infinite-uploads' ),
			'confirm_delete' => __( 'Delete this folder? Media files inside will be moved to Uncategorized.', 'infinite-uploads' ),
			'search_folders' => __( 'Enter folder name…', 'infinite-uploads' ),
			'sort_az'        => __( 'Sort A-Z', 'infinite-uploads' ),
			'sort_za'        => __( 'Sort Z-A', 'infinite-uploads' ),
			'expand_all'     => __( 'Expand All', 'infinite-uploads' ),
			'collapse_all'   => __( 'Collapse All', 'infinite-uploads' ),
			'folders_title'  => __( 'Folders', 'infinite-uploads' ),
			'more'           => __( 'More', 'infinite-uploads' ),
			'is_list_mode'   => ( isset( $_GET['mode'] ) && $_GET['mode'] === 'list' ) || ( ! isset( $_GET['mode'] ) && get_user_option( 'media_library_mode' ) === 'list' ),
		] );
	}

	// -----------------------------------------------------------------
	// AJAX: Get folders
	// -----------------------------------------------------------------

	public function ajax_get_folders() {
		check_ajax_referer( 'iu_media_folders', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		global $wpdb;

		$sort = sanitize_text_field( $_POST['sort'] ?? 'custom' );

		switch ( $sort ) {
			case 'az':
				$order_clause = 'ORDER BY name ASC';
				break;
			case 'za':
				$order_clause = 'ORDER BY name DESC';
				break;
			default:
				$order_clause = 'ORDER BY sort_order ASC, name ASC';
				break;
		}

		$folders = $wpdb->get_results(
			"SELECT * FROM {$this->folders_table()} {$order_clause}"
		);

		$counts = $this->get_folder_counts();
		$tree   = [];

		foreach ( $folders as $folder ) {
			$tree[] = [
				'id'     => 'folder_' . $folder->id,
				'text'   => esc_html( $folder->name ),
				'parent' => $folder->parent_id ? 'folder_' . $folder->parent_id : '#',
				'data'   => [
					'folder_id'   => (int) $folder->id,
					'folder_name' => $folder->name,
					'count'       => $counts[ (int) $folder->id ] ?? 0,
				],
			];
		}

		wp_send_json_success( [
			'folders'              => $tree,
			'total_count'          => $this->get_total_count(),
			'uncategorized_count'  => $this->get_uncategorized_count(),
			'counts'               => $counts,
		] );
	}

	// -----------------------------------------------------------------
	// AJAX: Create folder
	// -----------------------------------------------------------------

	public function ajax_create_folder() {
		check_ajax_referer( 'iu_media_folders', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		$name      = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
		$parent_id = absint( $_POST['parent'] ?? 0 );

		if ( empty( $name ) ) {
			wp_send_json_error( __( 'Folder name cannot be empty.', 'infinite-uploads' ) );
		}

		global $wpdb;

		// Determine sort order (append to end).
		$max_order = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT MAX(sort_order) FROM {$this->folders_table()} WHERE parent_id = %d",
			$parent_id
		) );

		$inserted = $wpdb->insert(
			$this->folders_table(),
			[
				'name'       => $name,
				'parent_id'  => $parent_id,
				'sort_order' => $max_order + 1,
				'created_by' => get_current_user_id(),
			],
			[ '%s', '%d', '%d', '%d' ]
		);

		if ( ! $inserted ) {
			wp_send_json_error( __( 'Failed to create folder.', 'infinite-uploads' ) );
		}

		$folder_id = (int) $wpdb->insert_id;

		wp_send_json_success( [
			'id'        => 'folder_' . $folder_id,
			'text'      => esc_html( $name ),
			'parent'    => $parent_id ? 'folder_' . $parent_id : '#',
			'folder_id' => $folder_id,
			'data'      => [
				'folder_id'   => $folder_id,
				'folder_name' => $name,
				'count'       => 0,
			],
		] );
	}

	// -----------------------------------------------------------------
	// AJAX: Rename folder
	// -----------------------------------------------------------------

	public function ajax_rename_folder() {
		check_ajax_referer( 'iu_media_folders', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		$folder_id = absint( $_POST['term_id'] ?? 0 );
		$name      = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );

		if ( ! $folder_id || empty( $name ) ) {
			wp_send_json_error( __( 'Invalid folder or name.', 'infinite-uploads' ) );
		}

		global $wpdb;

		$updated = $wpdb->update(
			$this->folders_table(),
			[ 'name' => $name ],
			[ 'id' => $folder_id ],
			[ '%s' ],
			[ '%d' ]
		);

		if ( false === $updated ) {
			wp_send_json_error( __( 'Failed to rename folder.', 'infinite-uploads' ) );
		}

		wp_send_json_success( [ 'term_id' => $folder_id, 'name' => $name ] );
	}

	// -----------------------------------------------------------------
	// AJAX: Delete folder
	// -----------------------------------------------------------------

	public function ajax_delete_folder() {
		check_ajax_referer( 'iu_media_folders', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		$folder_id = absint( $_POST['term_id'] ?? 0 );

		if ( ! $folder_id ) {
			wp_send_json_error( __( 'Invalid folder.', 'infinite-uploads' ) );
		}

		global $wpdb;

		// Get the folder to find its parent.
		$folder = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$this->folders_table()} WHERE id = %d",
			$folder_id
		) );

		if ( ! $folder ) {
			wp_send_json_error( __( 'Folder not found.', 'infinite-uploads' ) );
		}

		// Reassign child folders to this folder's parent.
		$wpdb->update(
			$this->folders_table(),
			[ 'parent_id' => $folder->parent_id ],
			[ 'parent_id' => $folder_id ],
			[ '%d' ],
			[ '%d' ]
		);

		// Remove all media-to-folder relationships for this folder.
		$wpdb->delete(
			$this->relationships_table(),
			[ 'folder_id' => $folder_id ],
			[ '%d' ]
		);

		// Delete the folder.
		$wpdb->delete(
			$this->folders_table(),
			[ 'id' => $folder_id ],
			[ '%d' ]
		);

		wp_send_json_success( [ 'deleted' => $folder_id ] );
	}

	// -----------------------------------------------------------------
	// AJAX: Move folder (change parent)
	// -----------------------------------------------------------------

	public function ajax_move_folder() {
		check_ajax_referer( 'iu_media_folders', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		$folder_id = absint( $_POST['term_id'] ?? 0 );
		$parent_id = absint( $_POST['parent'] ?? 0 );

		if ( ! $folder_id ) {
			wp_send_json_error( __( 'Invalid folder.', 'infinite-uploads' ) );
		}

		// Prevent moving a folder into itself or its own descendant.
		if ( $parent_id && $this->is_descendant_of( $parent_id, $folder_id ) ) {
			wp_send_json_error( __( 'Cannot move a folder into its own subfolder.', 'infinite-uploads' ) );
		}

		global $wpdb;

		$wpdb->update(
			$this->folders_table(),
			[ 'parent_id' => $parent_id ],
			[ 'id' => $folder_id ],
			[ '%d' ],
			[ '%d' ]
		);

		wp_send_json_success( [ 'term_id' => $folder_id, 'parent' => $parent_id ] );
	}

	/**
	 * Check if $child_id is a descendant of $ancestor_id.
	 */
	private function is_descendant_of( $child_id, $ancestor_id ) {
		global $wpdb;

		$current = $child_id;
		$visited = [];

		while ( $current ) {
			if ( $current == $ancestor_id ) {
				return true;
			}
			if ( in_array( $current, $visited, true ) ) {
				break; // Circular reference guard.
			}
			$visited[] = $current;

			$current = $wpdb->get_var( $wpdb->prepare(
				"SELECT parent_id FROM {$this->folders_table()} WHERE id = %d",
				$current
			) );
		}

		return false;
	}

	// -----------------------------------------------------------------
	// AJAX: Move media to folder
	// -----------------------------------------------------------------

	public function ajax_move_media() {
		check_ajax_referer( 'iu_media_folders', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		$attachment_ids = array_map( 'absint', (array) ( $_POST['attachment_ids'] ?? [] ) );
		$folder_id      = intval( $_POST['folder_id'] ?? - 1 );

		if ( empty( $attachment_ids ) ) {
			wp_send_json_error( __( 'No media selected.', 'infinite-uploads' ) );
		}

		global $wpdb;

		foreach ( $attachment_ids as $attachment_id ) {
			if ( ! $attachment_id ) {
				continue;
			}

			// Remove existing folder assignments for this attachment.
			$wpdb->delete(
				$this->relationships_table(),
				[ 'attachment_id' => $attachment_id ],
				[ '%d' ]
			);

			// Assign to new folder (folder_id > 0 means a real folder; 0 = uncategorized).
			if ( $folder_id > 0 ) {
				$wpdb->replace(
					$this->relationships_table(),
					[
						'folder_id'     => $folder_id,
						'attachment_id' => $attachment_id,
					],
					[ '%d', '%d' ]
				);
			}
		}

		wp_send_json_success( [
			'moved'                => count( $attachment_ids ),
			'folder_id'            => $folder_id,
			'counts'               => $this->get_folder_counts(),
			'total_count'          => $this->get_total_count(),
			'uncategorized_count'  => $this->get_uncategorized_count(),
		] );
	}

	// -----------------------------------------------------------------
	// AJAX: Get folder counts
	// -----------------------------------------------------------------

	public function ajax_get_folder_counts() {
		check_ajax_referer( 'iu_media_folders', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		wp_send_json_success( $this->get_folder_counts() );
	}

	// -----------------------------------------------------------------
	// AJAX: Sort folders (save custom order)
	// -----------------------------------------------------------------

	public function ajax_sort_folders() {
		check_ajax_referer( 'iu_media_folders', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		$order = array_map( 'absint', (array) ( $_POST['order'] ?? [] ) );

		if ( empty( $order ) ) {
			wp_send_json_error( __( 'No folder order provided.', 'infinite-uploads' ) );
		}

		global $wpdb;

		foreach ( $order as $position => $folder_id ) {
			if ( ! $folder_id ) {
				continue;
			}
			$wpdb->update(
				$this->folders_table(),
				[ 'sort_order' => $position ],
				[ 'id' => $folder_id ],
				[ '%d' ],
				[ '%d' ]
			);
		}

		wp_send_json_success( [ 'sorted' => count( $order ) ] );
	}

	// -----------------------------------------------------------------
	// Helper methods
	// -----------------------------------------------------------------

	/**
	 * Get total count of all media attachments.
	 *
	 * @return int
	 */
	private function get_total_count() {
		global $wpdb;

		return (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'"
		);
	}

	/**
	 * Get count of uncategorized media (not in any folder).
	 *
	 * @return int
	 */
	private function get_uncategorized_count() {
		global $wpdb;

		return (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} p
			 WHERE p.post_type = 'attachment'
			   AND p.ID NOT IN (
			       SELECT r.attachment_id FROM {$this->relationships_table()} r
			   )"
		);
	}

	/**
	 * Get attachment counts per folder.
	 *
	 * @return array Associative array of folder_id => count.
	 */
	private function get_folder_counts() {
		global $wpdb;

		$results = $wpdb->get_results(
			"SELECT r.folder_id, COUNT(*) AS cnt
			 FROM {$this->relationships_table()} r
			 INNER JOIN {$wpdb->posts} p ON p.ID = r.attachment_id AND p.post_type = 'attachment'
			 GROUP BY r.folder_id"
		);

		$counts = [];
		foreach ( $results as $row ) {
			$counts[ (int) $row->folder_id ] = (int) $row->cnt;
		}

		return $counts;
	}

	// -----------------------------------------------------------------
	// Query filters: list mode (pre_get_posts) + grid mode (AJAX)
	// -----------------------------------------------------------------

	/**
	 * Filter media library query in list mode.
	 *
	 * @param \WP_Query $query
	 */
	public function filter_media_query( $query ) {
		global $pagenow;

		if ( ! is_admin() || $pagenow !== 'upload.php' || ! $query->is_main_query() ) {
			return;
		}

		if ( $query->get( 'post_type' ) !== 'attachment' ) {
			return;
		}

		$folder = $_GET['iu_folder'] ?? '';

		if ( $folder === '' ) {
			return;
		}

		$post_ids = $this->get_attachment_ids_for_folder( $folder );

		if ( $post_ids === null ) {
			return; // Show all.
		}

		if ( empty( $post_ids ) ) {
			// No attachments in this folder – force empty result.
			$query->set( 'post__in', [ 0 ] );
		} else {
			$query->set( 'post__in', $post_ids );
		}
	}

	/**
	 * Filter AJAX attachment query args (grid mode).
	 *
	 * @param array $query
	 *
	 * @return array
	 */
	public function filter_ajax_attachments_args( $query ) {
		$folder = $_REQUEST['iu_folder'] ?? '';

		if ( $folder === '' ) {
			return $query;
		}

		$post_ids = $this->get_attachment_ids_for_folder( $folder );

		if ( $post_ids === null ) {
			return $query;
		}

		if ( empty( $post_ids ) ) {
			$query['post__in'] = [ 0 ];
		} else {
			$query['post__in'] = $post_ids;
		}

		return $query;
	}

	/**
	 * Get attachment IDs for a given folder filter value.
	 *
	 * @param string $folder Folder ID or 'uncategorized'.
	 *
	 * @return int[]|null Array of attachment IDs, or null for "show all".
	 */
	public function get_attachment_ids_for_folder( $folder ) {
		global $wpdb;

		if ( $folder === 'uncategorized' ) {
			// Attachments NOT in any folder.
			$ids = $wpdb->get_col(
				"SELECT p.ID FROM {$wpdb->posts} p
				 WHERE p.post_type = 'attachment'
				   AND p.ID NOT IN (
				       SELECT r.attachment_id FROM {$this->relationships_table()} r
				   )"
			);

			return array_map( 'intval', $ids );
		}

		if ( is_numeric( $folder ) && (int) $folder > 0 ) {
			$ids = $wpdb->get_col( $wpdb->prepare(
				"SELECT attachment_id FROM {$this->relationships_table()} WHERE folder_id = %d",
				(int) $folder
			) );

			return array_map( 'intval', $ids );
		}

		return null;
	}

	// -----------------------------------------------------------------
	// Cleanup on attachment deletion
	// -----------------------------------------------------------------

	/**
	 * Remove folder relationships when an attachment is deleted.
	 *
	 * @param int $attachment_id
	 */
	public function on_delete_attachment( $attachment_id ) {
		global $wpdb;

		$wpdb->delete(
			$this->relationships_table(),
			[ 'attachment_id' => $attachment_id ],
			[ '%d' ]
		);
	}

	/**
	 * Backup folder structure to site options for potential restore on reconnect.
	 * Then delete all folder data from the database tables.
	 */
	public static function backup_and_cleanup() {
		global $wpdb;

		$folders_table       = $wpdb->prefix . 'infinite_uploads_media_folders';
		$relationships_table = $wpdb->prefix . 'infinite_uploads_media_folder_relationships';

		// Backup folders.
		$folders = $wpdb->get_results( "SELECT * FROM `{$folders_table}`", ARRAY_A );
		if ( ! empty( $folders ) ) {
			update_site_option( 'iu_media_folders_backup', $folders );
		}

		// Backup relationships.
		$relationships = $wpdb->get_results( "SELECT * FROM `{$relationships_table}`", ARRAY_A );
		if ( ! empty( $relationships ) ) {
			update_site_option( 'iu_media_folder_relationships_backup', $relationships );
		}

		// Store backup timestamp.
		update_site_option( 'iu_media_folders_backup_time', time() );

		// Delete all folder data.
		$wpdb->query( "TRUNCATE TABLE `{$folders_table}`" );
		$wpdb->query( "TRUNCATE TABLE `{$relationships_table}`" );

		// Disable media folders feature.
		InfiniteUploadsHelper::set_media_folders_setting( 'no' );
	}

	/**
	 * Restore folder structure from backup after reconnect.
	 *
	 * @return bool True if restore succeeded, false if no backup found.
	 */
	public static function restore_from_backup() {
		global $wpdb;

		$folders       = get_site_option( 'iu_media_folders_backup', [] );
		$relationships = get_site_option( 'iu_media_folder_relationships_backup', [] );

		if ( empty( $folders ) ) {
			return false;
		}

		$folders_table       = $wpdb->prefix . 'infinite_uploads_media_folders';
		$relationships_table = $wpdb->prefix . 'infinite_uploads_media_folder_relationships';

		// Only restore if tables are currently empty (avoid duplicates).
		$existing = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$folders_table}`" );
		if ( $existing > 0 ) {
			self::delete_backup();
			return false;
		}

		// Restore folders.
		foreach ( $folders as $folder ) {
			$wpdb->insert( $folders_table, $folder );
		}

		// Restore relationships — only for attachments that still exist.
		if ( ! empty( $relationships ) ) {
			foreach ( $relationships as $rel ) {
				if ( get_post_type( $rel['attachment_id'] ) === 'attachment' ) {
					$wpdb->insert( $relationships_table, $rel );
				}
			}
		}

		// Re-enable media folders feature.
		InfiniteUploadsHelper::set_media_folders_setting( 'yes' );

		// Clean up backup data.
		self::delete_backup();

		return true;
	}

	/**
	 * Delete backup data from site options.
	 */
	public static function delete_backup() {
		delete_site_option( 'iu_media_folders_backup' );
		delete_site_option( 'iu_media_folder_relationships_backup' );
		delete_site_option( 'iu_media_folders_backup_time' );
	}

	/**
	 * Check if a folder backup exists.
	 *
	 * @return bool
	 */
	public static function has_backup() {
		return (bool) get_site_option( 'iu_media_folders_backup', false );
	}
}
