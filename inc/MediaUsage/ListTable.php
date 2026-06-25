<?php
/**
 * Admin list table for the Media Library Usage Scanner results.
 *
 * @package InfiniteUploads
 */

namespace ClikIT\InfiniteUploads\MediaUsage;

// WP_List_Table is not autoloaded; pull it in before this class is defined.
if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class ListTable
 */
class ListTable extends \WP_List_Table {

	/**
	 * File size (bytes) above which a file is considered "large".
	 */
	const LARGE_FILE_BYTES = 5242880; // 5 MB.

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'iu_media_usage_item',
				'plural'   => 'iu_media_usage_items',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Column definitions.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'thumbnail'       => __( 'Thumbnail', 'infinite-uploads' ),
			'title'           => __( 'File', 'infinite-uploads' ),
			'attachment_id'   => __( 'ID', 'infinite-uploads' ),
			'file_type'       => __( 'Type', 'infinite-uploads' ),
			'file_size'       => __( 'Size', 'infinite-uploads' ),
			'usage_status'    => __( 'Usage', 'infinite-uploads' ),
			'last_scanned_at' => __( 'Last scanned', 'infinite-uploads' ),
		);
	}

	/**
	 * Sortable columns.
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array(
			'attachment_id'   => array( 'attachment_id', false ),
			'file_size'       => array( 'file_size', false ),
			'usage_status'    => array( 'usage_status', false ),
			'last_scanned_at' => array( 'last_scanned_at', true ),
		);
	}

	/**
	 * Status filter links with counts.
	 *
	 * @return array
	 */
	public function get_views() {
		$summary = Store::get_summary();
		$current = $this->get_status_filter();
		$base    = menu_page_url( 'iu-media-usage', false );

		$make = function ( $key, $label, $count ) use ( $current, $base ) {
			$url   = ( '' === $key ) ? $base : add_query_arg( 'usage', $key, $base );
			$class = ( $current === $key ) ? ' class="current"' : '';

			return sprintf(
				'<a href="%s"%s>%s <span class="count">(%s)</span></a>',
				esc_url( $url ),
				$class,
				esc_html( $label ),
				number_format_i18n( $count )
			);
		};

		return array(
			''                           => $make( '', __( 'All', 'infinite-uploads' ), $summary['total'] ),
			Store::STATUS_REFERENCED      => $make( Store::STATUS_REFERENCED, __( 'In use', 'infinite-uploads' ), $summary['referenced'] ),
			Store::STATUS_POSSIBLY_UNUSED => $make( Store::STATUS_POSSIBLY_UNUSED, __( 'Possibly unused', 'infinite-uploads' ), $summary['possibly_unused'] ),
			Store::STATUS_UNKNOWN         => $make( Store::STATUS_UNKNOWN, __( 'Unknown', 'infinite-uploads' ), $summary['unknown'] ),
		);
	}

	/**
	 * The validated usage status filter from the query string.
	 *
	 * @return string
	 */
	private function get_status_filter() {
		$value = isset( $_GET['usage'] ) ? sanitize_key( wp_unslash( $_GET['usage'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$valid = array( Store::STATUS_REFERENCED, Store::STATUS_POSSIBLY_UNUSED, Store::STATUS_UNKNOWN );

		return in_array( $value, $valid, true ) ? $value : '';
	}

	/**
	 * The validated file-type filter from the query string.
	 *
	 * @return string
	 */
	private function get_type_filter() {
		$value = isset( $_GET['file_type'] ) ? sanitize_key( wp_unslash( $_GET['file_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$valid = array( 'images', 'videos', 'documents', 'large' );

		return in_array( $value, $valid, true ) ? $value : '';
	}

	/**
	 * Render the type filter dropdown above the table.
	 *
	 * @param string $which Top or bottom.
	 *
	 * @return void
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		$current = $this->get_type_filter();
		$options = array(
			''          => __( 'All file types', 'infinite-uploads' ),
			'images'    => __( 'Images', 'infinite-uploads' ),
			'videos'    => __( 'Videos', 'infinite-uploads' ),
			'documents' => __( 'PDFs / documents', 'infinite-uploads' ),
			'large'     => __( 'Large files', 'infinite-uploads' ),
		);
		?>
		<div class="alignleft actions">
			<label class="screen-reader-text" for="iu-mu-type"><?php esc_html_e( 'Filter by file type', 'infinite-uploads' ); ?></label>
			<select name="file_type" id="iu-mu-type">
				<?php foreach ( $options as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php submit_button( __( 'Filter', 'infinite-uploads' ), '', 'filter_action', false ); ?>
		</div>
		<?php
	}

	/**
	 * Build, query and paginate the rows.
	 *
	 * @return void
	 */
	public function prepare_items() {
		global $wpdb;

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

		$per_page = 50;
		$paged    = max( 1, (int) $this->get_pagenum() );
		$offset   = ( $paged - 1 ) * $per_page;

		$items_table = Schema::items_table();

		// WHERE clauses. All comparison values are whitelisted constants or
		// bound parameters; nothing here is interpolated from raw user input.
		$where  = array( '1=1' );
		$params = array();

		$status = $this->get_status_filter();
		if ( '' !== $status ) {
			$where[]  = 'i.usage_status = %s';
			$params[] = $status;
		}

		switch ( $this->get_type_filter() ) {
			case 'images':
				$where[] = "i.file_type LIKE 'image/%'";
				break;
			case 'videos':
				$where[] = "i.file_type LIKE 'video/%'";
				break;
			case 'documents':
				$where[] = "( i.file_type LIKE 'application/%' OR i.file_type LIKE 'text/%' )";
				break;
			case 'large':
				$where[]  = 'i.file_size > %d';
				$params[] = self::LARGE_FILE_BYTES;
				break;
		}

		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( '' !== $search ) {
			$like     = '%' . $wpdb->esc_like( $search ) . '%';
			$where[]  = '( i.file_path LIKE %s OR p.post_title LIKE %s )';
			$params[] = $like;
			$params[] = $like;
		}

		$where_sql = implode( ' AND ', $where );

		// Ordering (whitelisted column + direction).
		// Default to a stable sort (attachment id) so a single rescan — which
		// updates last_scanned_at — doesn't jump that row to the top of the list.
		$orderby_input = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'attachment_id'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order_input   = ( isset( $_GET['order'] ) && 'asc' === strtolower( sanitize_key( wp_unslash( $_GET['order'] ) ) ) ) ? 'ASC' : 'DESC'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$orderby_map   = array(
			'attachment_id'   => 'i.attachment_id',
			'file_size'       => 'i.file_size',
			'usage_status'    => 'i.usage_status',
			'reference_count' => 'i.reference_count',
			'last_scanned_at' => 'i.last_scanned_at',
		);
		$orderby_sql   = isset( $orderby_map[ $orderby_input ] ) ? $orderby_map[ $orderby_input ] : 'i.attachment_id';

		$join = " FROM {$items_table} i LEFT JOIN {$wpdb->posts} p ON p.ID = i.attachment_id WHERE {$where_sql}";

		// Total for pagination.
		$count_sql = "SELECT COUNT(*) {$join}";
		$total     = (int) ( $params ? $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) ) : $wpdb->get_var( $count_sql ) );

		// Page of rows.
		$select      = "SELECT i.*, p.post_title {$join} ORDER BY {$orderby_sql} {$order_input} LIMIT %d OFFSET %d";
		$page_params = array_merge( $params, array( $per_page, $offset ) );
		$this->items = $wpdb->get_results( $wpdb->prepare( $select, $page_params ) );

		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'per_page'    => $per_page,
				'total_pages' => (int) ceil( $total / $per_page ),
			)
		);
	}

	/**
	 * Thumbnail cell.
	 *
	 * @param object $item Row.
	 *
	 * @return string
	 */
	public function column_thumbnail( $item ) {
		$image = wp_get_attachment_image( (int) $item->attachment_id, array( 50, 50 ), true );

		return $image ? $image : '<span class="dashicons dashicons-media-default"></span>';
	}

	/**
	 * File / title cell with row actions.
	 *
	 * @param object $item Row.
	 *
	 * @return string
	 */
	public function column_title( $item ) {
		$attachment_id = (int) $item->attachment_id;
		$title         = $item->post_title ? $item->post_title : wp_basename( (string) $item->file_path );
		$edit_link     = get_edit_post_link( $attachment_id );

		$name = $edit_link
			? '<a href="' . esc_url( $edit_link ) . '"><strong>' . esc_html( $title ) . '</strong></a>'
			: '<strong>' . esc_html( $title ) . '</strong>';

		$ignored = (int) $item->ignored;

		$actions = array(
			'view'    => $edit_link ? '<a href="' . esc_url( $edit_link ) . '">' . esc_html__( 'View attachment', 'infinite-uploads' ) . '</a>' : '',
			'fileurl' => $item->file_url ? '<a href="' . esc_url( $item->file_url ) . '" target="_blank" rel="noopener">' . esc_html__( 'View file URL', 'infinite-uploads' ) . '</a>' : '',
			'copy'    => $item->file_url ? '<a href="#" class="iu-mu-copy" data-url="' . esc_attr( $item->file_url ) . '">' . esc_html__( 'Copy file URL', 'infinite-uploads' ) . '</a>' : '',
			'detail'  => '<a href="#" class="iu-mu-detail" data-id="' . esc_attr( $attachment_id ) . '">' . esc_html__( 'Found references', 'infinite-uploads' ) . '</a>',
			'rescan'  => '<a href="#" class="iu-mu-rescan" data-id="' . esc_attr( $attachment_id ) . '">' . esc_html__( 'Rescan', 'infinite-uploads' ) . '</a>',
			'ignore'  => '<a href="#" class="iu-mu-ignore" data-id="' . esc_attr( $attachment_id ) . '" data-ignored="' . ( $ignored ? '0' : '1' ) . '">' . ( $ignored ? esc_html__( 'Unignore', 'infinite-uploads' ) : esc_html__( 'Mark as ignored', 'infinite-uploads' ) ) . '</a>',
		);

		return $name . $this->row_actions( array_filter( $actions ) );
	}

	/**
	 * Attachment id cell.
	 *
	 * @param object $item Row.
	 *
	 * @return string
	 */
	public function column_attachment_id( $item ) {
		return (int) $item->attachment_id;
	}

	/**
	 * File type cell.
	 *
	 * @param object $item Row.
	 *
	 * @return string
	 */
	public function column_file_type( $item ) {
		return esc_html( $item->file_type );
	}

	/**
	 * File size cell.
	 *
	 * @param object $item Row.
	 *
	 * @return string
	 */
	public function column_file_size( $item ) {
		return $item->file_size ? esc_html( size_format( (int) $item->file_size ) ) : '&mdash;';
	}

	/**
	 * Usage status cell.
	 *
	 * @param object $item Row.
	 *
	 * @return string
	 */
	public function column_usage_status( $item ) {
		if ( (int) $item->ignored ) {
			return '<span class="iu-usage-badge iu-usage-ignored">' . esc_html__( 'Ignored', 'infinite-uploads' ) . '</span>';
		}

		return sprintf(
			'<span class="iu-usage-badge iu-usage-%1$s">%2$s</span>',
			esc_attr( $item->usage_status ),
			esc_html( Scanner::status_label( $item->usage_status, (int) $item->reference_count ) )
		);
	}

	/**
	 * Last scanned cell.
	 *
	 * @param object $item Row.
	 *
	 * @return string
	 */
	public function column_last_scanned_at( $item ) {
		if ( empty( $item->last_scanned_at ) || '0000-00-00 00:00:00' === $item->last_scanned_at ) {
			return '&mdash;';
		}

		$ts = strtotime( $item->last_scanned_at . ' UTC' );

		/* translators: %s: human-readable time difference. */
		return $ts ? esc_html( sprintf( __( '%s ago', 'infinite-uploads' ), human_time_diff( $ts ) ) ) : '&mdash;';
	}

	/**
	 * Fallback cell renderer.
	 *
	 * @param object $item Row.
	 * @param string $column_name Column id.
	 *
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		return isset( $item->$column_name ) ? esc_html( $item->$column_name ) : '';
	}

	/**
	 * Message when there are no rows.
	 *
	 * @return void
	 */
	public function no_items() {
		esc_html_e( 'No scan results yet. Run a scan to populate usage data.', 'infinite-uploads' );
	}
}
