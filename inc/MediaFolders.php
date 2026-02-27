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
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );

		// Elementor resets $wp_scripts with a fresh empty object inside its own
		// enqueue_scripts() call, wiping everything registered via admin_enqueue_scripts.
		// We must re-enqueue inside Elementor's own hook so our script ends up in
		// the new $wp_scripts instance that actually gets output to the page.
		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_for_elementor' ] );

		// Brizy runs its editor inside a frontend iframe (URL param `is-editor-iframe`).
		// It calls wp_enqueue_media() inside that iframe at wp_enqueue_scripts priority 9999,
		// creating its own wp.media instance in the iframe window.  We must enqueue our
		// script inside the same iframe so _extendAttachmentsBrowser patches the right
		// window context.  PHP_INT_MAX ensures we run after Brizy's wp_enqueue_media() call.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_for_brizy_iframe' ], PHP_INT_MAX );

		// Beaver Builder fires fl_builder_ui_enqueue_scripts inside its editor iframe
		// after it has cleared the wp_scripts queue and re-enqueued its own assets
		// (including wp_enqueue_media()).  The editor also runs on the frontend
		// so is_admin() is false and admin_enqueue_scripts never fires there.
		add_action( 'fl_builder_ui_enqueue_scripts', [ $this, 'enqueue_for_beaver_builder' ] );


//		add_action( 'wp_enqueue_scripts', function() {
//
//			if ( ! function_exists( 'et_core_is_fb_enabled' ) ) {
//				return;
//			}
//
//			if ( et_core_is_fb_enabled() ) {
//				$this->enqueue_for_divi();
//			}
//
//		});

		// AJAX handlers for folder operations.
		add_action( 'wp_ajax_iu_get_folders', [ $this, 'ajax_get_folders' ] );
		add_action( 'wp_ajax_iu_create_folder', [ $this, 'ajax_create_folder' ] );
		add_action( 'wp_ajax_iu_rename_folder', [ $this, 'ajax_rename_folder' ] );
		add_action( 'wp_ajax_iu_delete_folder', [ $this, 'ajax_delete_folder' ] );
		add_action( 'wp_ajax_iu_move_folder', [ $this, 'ajax_move_folder' ] );
		add_action( 'wp_ajax_iu_move_media', [ $this, 'ajax_move_media' ] );
		add_action( 'wp_ajax_iu_sort_folders', [ $this, 'ajax_sort_folders' ] );
		add_action( 'wp_ajax_iu_get_folder_counts', [ $this, 'ajax_get_folder_counts' ] );
		add_action( 'wp_ajax_iu_set_upload_folder', [ $this, 'ajax_set_upload_folder' ] );
		add_action( 'wp_ajax_iu_bulk_delete_folders', [ $this, 'ajax_bulk_delete_folders' ] );
		add_action( 'wp_ajax_iu_bulk_move_folders',   [ $this, 'ajax_bulk_move_folders' ] );

		// Assign newly uploaded attachments to the user's selected folder.
		add_action( 'add_attachment', [ $this, 'on_add_attachment' ] );

		// Filter media library queries.
		add_action( 'pre_get_posts', [ $this, 'filter_media_query' ] );

		// Add folder filter to AJAX queries (grid view).
		add_filter( 'ajax_query_attachments_args', [ $this, 'filter_ajax_attachments_args' ] );

		// Clean up relationships when an attachment is deleted.
		add_action( 'delete_attachment', [ $this, 'on_delete_attachment' ] );

		// Folder column in the media list table (list mode).
		add_filter( 'manage_upload_columns', [ $this, 'add_folder_column' ] );
		add_action( 'manage_media_custom_column', [ $this, 'render_folder_column' ], 10, 2 );
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

	public function is_media_folders_js_required() {
		$is_brizy = isset( $_GET['action'] ) && $_GET['action'] === 'in-front-editor';

		if ( $is_brizy ) {
			return true;
		}

		// Is Oxygen builder? Oxygen uses upload.php for its media picker, but with a special query var.
		$is_oxygen = isset( $_GET['ct_builder'] ) && $_GET['ct_builder'] == true;

		if ( $is_oxygen ) {
			return true;
		}

		// Is Bricks builder? Bricks uses upload.php for its media picker, but with a special query var.
		$is_bricks = isset( $_GET['bricks'] ) && $_GET['bricks'] === 'run';

		if ( $is_bricks ) {
			return true;
		}

		// Is Divi builder? Divi's media picker runs on upload.php but doesn't use a special query var, so we have to check if we're on upload.php and if Divi is active.
		if ( ! function_exists( 'et_core_is_fb_enabled' ) ) {
			return false;
		}

		if ( et_core_is_fb_enabled() ) {
			return true;
		}

		return false;
	}
	/**
	 * Enqueue assets on all admin pages where the current user can manage media.
	 * Page builders (Elementor, Avada, Oxygen, etc.) run on their own hooks, not
	 * upload.php, so we must be hook-agnostic to inject the sidebar into their
	 * media picker modals.
	 */
	public function enqueue_assets( $hook ) {
		if ( ! is_admin() && ! $this->is_media_folders_js_required() ) {
			return;
		}

		$plugin_url = plugins_url( '', dirname( __FILE__ ) );

		// Tailwind CSS (CDN) — only on upload.php where the sidebar is embedded in
		// the page. post.php may serve Elementor's editor (post.php?action=elementor)
		// where Tailwind's preflight reset would break the builder's UI.
		if ( $hook === 'upload.php' ) {
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
			[ 'jquery', 'media-views' ],
			INFINITE_UPLOADS_VERSION,
			true
		);

		wp_localize_script( 'iu-media-folders', 'iuMediaFolders', [
			'ajax_url'           => admin_url( 'admin-ajax.php' ),
			'nonce'              => wp_create_nonce( 'iu_media_folders' ),
			'all_label'          => __( 'All Files', 'infinite-uploads' ),
			'uncat_label'        => __( 'Uncategorized', 'infinite-uploads' ),
			'new_folder'         => __( 'New Folder', 'infinite-uploads' ),
			'new_subfolder'      => __( 'New Subfolder', 'infinite-uploads' ),
			'rename'             => __( 'Rename', 'infinite-uploads' ),
			'cut'                => __( 'Cut', 'infinite-uploads' ),
			'paste'              => __( 'Paste', 'infinite-uploads' ),
			'delete'             => __( 'Delete', 'infinite-uploads' ),
			'confirm_delete'     => __( 'Delete this folder? Media files inside will be moved to Uncategorized.', 'infinite-uploads' ),
			'confirm_bulk_delete' => __( 'Delete %d folders? Media files inside will be moved to Uncategorized.', 'infinite-uploads' ),
			'delete_bulk'         => __( 'Delete (%d)', 'infinite-uploads' ),
			'search_folders'     => __( 'Enter folder name…', 'infinite-uploads' ),
			'sort_az'            => __( 'Sort A-Z', 'infinite-uploads' ),
			'sort_za'            => __( 'Sort Z-A', 'infinite-uploads' ),
			'expand_all'         => __( 'Expand All', 'infinite-uploads' ),
			'collapse_all'       => __( 'Collapse All', 'infinite-uploads' ),
			'folders_title'      => __( 'Folders', 'infinite-uploads' ),
			'more'               => __( 'More', 'infinite-uploads' ),
			'choose_folder'      => __( 'Upload to folder:', 'infinite-uploads' ),
			'upload_folder_none' => __( 'No folder (Uncategorized)', 'infinite-uploads' ),
			// is_list_mode is only meaningful on upload.php; never on post.php/post-new.php.
			'is_list_mode'       => $hook === 'upload.php' && (
				( isset( $_GET['mode'] ) && $_GET['mode'] === 'list' ) ||
				( ! isset( $_GET['mode'] ) && get_user_option( 'media_library_mode' ) === 'list' )
			),
			// is_upload_page lets JS know whether to expect an already-rendered
			// .media-frame in the DOM (upload.php grid) or always defer injection.
			'is_upload_page'     => $hook === 'upload.php',
			'is_media_new_page'  => $hook === 'media-new.php',
		] );
	}

	/**
	 * Re-enqueue our assets inside Elementor's editor script queue.
	 *
	 * Elementor calls `$wp_scripts = new \WP_Scripts()` in its enqueue_scripts()
	 * method, which discards everything that was registered via admin_enqueue_scripts.
	 * This hook fires after Elementor has enqueued its own scripts (including
	 * wp_enqueue_media()), so media-views is already registered.
	 */
	public function enqueue_for_elementor() {
		// Re-use enqueue_assets with a neutral hook value so none of the
		// upload.php / media-new.php specific flags are set.
		$this->enqueue_assets( '_elementor' );
	}

	/**
	 * Enqueue our script inside Brizy's frontend editor iframe.
	 *
	 * Brizy runs the page builder inside a frontend iframe identified by the
	 * `is-editor-iframe` request parameter.  It calls wp_enqueue_media() inside
	 * that iframe (at wp_enqueue_scripts priority 9999), so wp.media and
	 * media-views exist in the iframe window context.  We enqueue here at
	 * PHP_INT_MAX so the dependency is already registered.
	 *
	 * We cannot go through enqueue_assets() because is_admin() returns false
	 * inside the frontend iframe, causing an early return.
	 */
	public function enqueue_for_brizy_iframe() {
		if ( empty( $_REQUEST['is-editor-iframe'] ) ) {
			return;
		}

		if ( ! is_user_logged_in() || ! current_user_can( 'upload_files' ) ) {
			return;
		}

		$plugin_url = plugins_url( '', dirname( __FILE__ ) );

		wp_enqueue_style(
			'iu-media-folders',
			$plugin_url . '/inc/assets/css/media-folders.css',
			[],
			INFINITE_UPLOADS_VERSION
		);

		// Brizy calls wp_enqueue_media() at priority 9999 so 'media-views' is
		// already registered when we fire at PHP_INT_MAX.
		wp_enqueue_script(
			'iu-media-folders',
			$plugin_url . '/inc/assets/js/media-folders.js',
			[ 'jquery', 'media-views' ],
			INFINITE_UPLOADS_VERSION,
			true
		);

		wp_localize_script( 'iu-media-folders', 'iuMediaFolders', [
			'ajax_url'            => admin_url( 'admin-ajax.php' ),
			'nonce'               => wp_create_nonce( 'iu_media_folders' ),
			'all_label'           => __( 'All Files', 'infinite-uploads' ),
			'uncat_label'         => __( 'Uncategorized', 'infinite-uploads' ),
			'new_folder'          => __( 'New Folder', 'infinite-uploads' ),
			'new_subfolder'       => __( 'New Subfolder', 'infinite-uploads' ),
			'rename'              => __( 'Rename', 'infinite-uploads' ),
			'cut'                 => __( 'Cut', 'infinite-uploads' ),
			'paste'               => __( 'Paste', 'infinite-uploads' ),
			'delete'              => __( 'Delete', 'infinite-uploads' ),
			'confirm_delete'      => __( 'Delete this folder? Media files inside will be moved to Uncategorized.', 'infinite-uploads' ),
			'confirm_bulk_delete' => __( 'Delete %d folders? Media files inside will be moved to Uncategorized.', 'infinite-uploads' ),
			'delete_bulk'         => __( 'Delete (%d)', 'infinite-uploads' ),
			'search_folders'      => __( 'Enter folder name…', 'infinite-uploads' ),
			'sort_az'             => __( 'Sort A-Z', 'infinite-uploads' ),
			'sort_za'             => __( 'Sort Z-A', 'infinite-uploads' ),
			'expand_all'          => __( 'Expand All', 'infinite-uploads' ),
			'collapse_all'        => __( 'Collapse All', 'infinite-uploads' ),
			'folders_title'       => __( 'Folders', 'infinite-uploads' ),
			'more'                => __( 'More', 'infinite-uploads' ),
			'choose_folder'       => __( 'Upload to folder:', 'infinite-uploads' ),
			'upload_folder_none'  => __( 'No folder (Uncategorized)', 'infinite-uploads' ),
			// The iframe is always grid mode; none of the page-specific flags apply.
			'is_list_mode'        => false,
			'is_upload_page'      => false,
			'is_media_new_page'   => false,
		] );
	}

	/**
	 * Enqueue our script inside the Beaver Builder editor iframe.
	 *
	 * Beaver Builder fires fl_builder_ui_enqueue_scripts inside its content
	 * iframe after clearing the wp_scripts queue and re-enqueuing its own
	 * assets (including wp_enqueue_media()).  The editor runs on the frontend
	 * so is_admin() is false and enqueue_assets() would bail; we enqueue
	 * directly here instead.
	 */
	public function enqueue_for_beaver_builder() {
		if ( ! current_user_can( 'upload_files' ) ) {
			return;
		}

		$plugin_url = plugins_url( '', dirname( __FILE__ ) );

		wp_enqueue_style(
			'iu-media-folders',
			$plugin_url . '/inc/assets/css/media-folders.css',
			[],
			INFINITE_UPLOADS_VERSION
		);

		// BB has already called wp_enqueue_media() so 'media-views' is registered.
		wp_enqueue_script(
			'iu-media-folders',
			$plugin_url . '/inc/assets/js/media-folders.js',
			[ 'jquery', 'media-views' ],
			INFINITE_UPLOADS_VERSION,
			true
		);

		wp_localize_script( 'iu-media-folders', 'iuMediaFolders', [
			'ajax_url'            => admin_url( 'admin-ajax.php' ),
			'nonce'               => wp_create_nonce( 'iu_media_folders' ),
			'all_label'           => __( 'All Files', 'infinite-uploads' ),
			'uncat_label'         => __( 'Uncategorized', 'infinite-uploads' ),
			'new_folder'          => __( 'New Folder', 'infinite-uploads' ),
			'new_subfolder'       => __( 'New Subfolder', 'infinite-uploads' ),
			'rename'              => __( 'Rename', 'infinite-uploads' ),
			'cut'                 => __( 'Cut', 'infinite-uploads' ),
			'paste'               => __( 'Paste', 'infinite-uploads' ),
			'delete'              => __( 'Delete', 'infinite-uploads' ),
			'confirm_delete'      => __( 'Delete this folder? Media files inside will be moved to Uncategorized.', 'infinite-uploads' ),
			'confirm_bulk_delete' => __( 'Delete %d folders? Media files inside will be moved to Uncategorized.', 'infinite-uploads' ),
			'delete_bulk'         => __( 'Delete (%d)', 'infinite-uploads' ),
			'search_folders'      => __( 'Enter folder name…', 'infinite-uploads' ),
			'sort_az'             => __( 'Sort A-Z', 'infinite-uploads' ),
			'sort_za'             => __( 'Sort Z-A', 'infinite-uploads' ),
			'expand_all'          => __( 'Expand All', 'infinite-uploads' ),
			'collapse_all'        => __( 'Collapse All', 'infinite-uploads' ),
			'folders_title'       => __( 'Folders', 'infinite-uploads' ),
			'more'                => __( 'More', 'infinite-uploads' ),
			'choose_folder'       => __( 'Upload to folder:', 'infinite-uploads' ),
			'upload_folder_none'  => __( 'No folder (Uncategorized)', 'infinite-uploads' ),
			// BB editor is always grid mode; none of the page-specific flags apply.
			'is_list_mode'        => false,
			'is_upload_page'      => false,
			'is_media_new_page'   => false,
		] );
	}

	public function enqueue_for_divi() {
		$this->enqueue_assets( '_divi' );
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
	// AJAX: Bulk delete folders
	// -----------------------------------------------------------------

	public function ajax_bulk_delete_folders() {
		check_ajax_referer( 'iu_media_folders', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		$term_ids = array_filter( array_map( 'absint', (array) ( $_POST['term_ids'] ?? [] ) ) );

		if ( empty( $term_ids ) ) {
			wp_send_json_error( __( 'No folders specified.', 'infinite-uploads' ) );
		}

		global $wpdb;

		$deleted = [];

		foreach ( $term_ids as $folder_id ) {
			$folder = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM {$this->folders_table()} WHERE id = %d",
				$folder_id
			) );

			if ( ! $folder ) {
				continue;
			}

			// Reparent direct children to this folder's parent.
			$wpdb->update(
				$this->folders_table(),
				[ 'parent_id' => $folder->parent_id ],
				[ 'parent_id' => $folder_id ],
				[ '%d' ],
				[ '%d' ]
			);

			// Remove media relationships (files become Uncategorized).
			$wpdb->delete( $this->relationships_table(), [ 'folder_id' => $folder_id ], [ '%d' ] );

			// Delete the folder.
			$wpdb->delete( $this->folders_table(), [ 'id' => $folder_id ], [ '%d' ] );

			$deleted[] = $folder_id;
		}

		// Fix orphaned children whose new parent was also in the deleted batch.
		if ( ! empty( $deleted ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $deleted ), '%d' ) );
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( $wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				"UPDATE {$this->folders_table()} SET parent_id = 0 WHERE parent_id IN ($placeholders)",
				...$deleted
			) );
		}

		wp_send_json_success( [ 'deleted' => $deleted ] );
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

	// -----------------------------------------------------------------
	// AJAX: Bulk move folders (change parent for multiple)
	// -----------------------------------------------------------------

	public function ajax_bulk_move_folders() {
		check_ajax_referer( 'iu_media_folders', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		$term_ids  = array_filter( array_map( 'absint', (array) ( $_POST['term_ids'] ?? [] ) ) );
		$parent_id = absint( $_POST['parent'] ?? 0 );

		if ( empty( $term_ids ) ) {
			wp_send_json_error( __( 'No folders specified.', 'infinite-uploads' ) );
		}

		global $wpdb;

		$moved   = [];
		$skipped = [];

		foreach ( $term_ids as $folder_id ) {
			// Cannot move a folder into itself.
			if ( $folder_id === $parent_id ) {
				$skipped[] = $folder_id;
				continue;
			}

			// Prevent circular nesting.
			if ( $parent_id && $this->is_descendant_of( $parent_id, $folder_id ) ) {
				$skipped[] = $folder_id;
				continue;
			}

			$wpdb->update(
				$this->folders_table(),
				[ 'parent_id' => $parent_id ],
				[ 'id' => $folder_id ],
				[ '%d' ],
				[ '%d' ]
			);

			$moved[] = $folder_id;
		}

		$data = [ 'moved' => $moved ];

		if ( ! empty( $skipped ) ) {
			$data['skipped'] = $skipped;
			$data['message'] = __( 'Some folders could not be moved (circular nesting).', 'infinite-uploads' );
		}

		wp_send_json_success( $data );
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
	// AJAX: Save the user's preferred upload folder
	// -----------------------------------------------------------------

	public function ajax_set_upload_folder() {
		check_ajax_referer( 'iu_media_folders', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		$folder_id = absint( $_POST['folder_id'] ?? 0 );

		if ( $folder_id > 0 ) {
			update_user_meta( get_current_user_id(), 'iu_upload_folder', $folder_id );
		} else {
			delete_user_meta( get_current_user_id(), 'iu_upload_folder' );
		}

		wp_send_json_success();
	}

	// -----------------------------------------------------------------
	// Auto-assign newly uploaded attachments to the selected folder
	// -----------------------------------------------------------------

	public function on_add_attachment( $attachment_id ) {
		if ( ! is_admin() ) {
			return;
		}

		$folder_id = (int) get_user_meta( get_current_user_id(), 'iu_upload_folder', true );
		if ( $folder_id <= 0 ) {
			return;
		}

		global $wpdb;

		// Remove any existing folder assignment for this attachment.
		$wpdb->delete(
			$this->relationships_table(),
			[ 'attachment_id' => $attachment_id ],
			[ '%d' ]
		);

		// Assign to the selected folder.
		$wpdb->replace(
			$this->relationships_table(),
			[
				'folder_id'     => $folder_id,
				'attachment_id' => $attachment_id,
			],
			[ '%d', '%d' ]
		);
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
	// Media list table: Folder column
	// -----------------------------------------------------------------

	/**
	 * Cache of attachment_id → folder row; populated once per page request.
	 *
	 * @var array|null
	 */
	private $folder_cache = null;

	/**
	 * Add "Folder" column to the media list table.
	 *
	 * WordPress automatically adds the column to Screen Options so users
	 * can show/hide it via the standard Show/hide columns panel.
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function add_folder_column( $columns ) {
		$new = [];

		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( $key === 'title' ) {
				$new['iu_folder'] = __( 'Folder', 'infinite-uploads' );
			}
		}

		return $new;
	}

	/**
	 * Render the folder column content for each attachment row.
	 *
	 * @param string $column_name
	 * @param int    $post_id
	 */
	public function render_folder_column( $column_name, $post_id ) {
		if ( $column_name !== 'iu_folder' ) {
			return;
		}

		$folder = $this->get_folder_for_attachment( $post_id );

		if ( $folder ) {
			$url = add_query_arg( 'iu_folder', $folder->id, admin_url( 'upload.php' ) );
			printf(
				'<a href="%s" class="iu-folder-col-link"><span class="dashicons dashicons-open-folder"></span>%s</a>',
				esc_url( $url ),
				esc_html( $folder->name )
			);
		} else {
			echo '<span class="iu-folder-col-empty">—</span>';
		}
	}

	/**
	 * Get the folder for a single attachment, using a per-request batch cache.
	 *
	 * On first call we load folder data for every attachment currently on the
	 * page in a single query, so we never issue more than one DB query total
	 * regardless of how many rows the table has.
	 *
	 * @param int $attachment_id
	 *
	 * @return object|null  Row with properties `id` and `name`, or null.
	 */
	private function get_folder_for_attachment( $attachment_id ) {
		if ( $this->folder_cache === null ) {
			global $wp_query, $wpdb;

			$ids = ! empty( $wp_query->posts )
				? array_column( (array) $wp_query->posts, 'ID' )
				: [];

			$this->folder_cache = [];

			if ( ! empty( $ids ) ) {
				$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$results = $wpdb->get_results( $wpdb->prepare(
					"SELECT r.attachment_id, f.id, f.name
					 FROM {$this->folders_table()} f
					 INNER JOIN {$this->relationships_table()} r ON r.folder_id = f.id
					 WHERE r.attachment_id IN ($placeholders)",
					...$ids
				) );

				foreach ( $results as $row ) {
					$this->folder_cache[ (int) $row->attachment_id ] = $row;
				}
			}
		}

		return $this->folder_cache[ (int) $attachment_id ] ?? null;
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
