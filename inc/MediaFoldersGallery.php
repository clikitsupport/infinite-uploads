<?php

namespace ClikIT\InfiniteUploads;

/**
 * MediaFoldersGallery - Main controller for gallery block, REST API, shortcode, and page builder integrations.
 */
class MediaFoldersGallery {

	private static $instance;

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		require_once __DIR__ . '/gallery/render.php';

		// Gutenberg block.
		add_action( 'init', [ $this, 'register_block' ] );

		// REST API endpoints.
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

		// Shortcode.
		add_shortcode( 'iu_gallery', [ $this, 'shortcode_handler' ] );

		// Page builder integrations (deferred so builder classes are loaded).
		add_action( 'plugins_loaded', [ $this, 'init_integrations' ], 20 );

		// PhotoSwipe footer HTML (printed once when needed).
		add_action( 'wp_footer', [ $this, 'print_photoswipe_html' ] );
	}

	// -------------------------------------------------------------------------
	// Block Registration
	// -------------------------------------------------------------------------

	public function register_block() {
		$plugin_dir = dirname( __DIR__ );
		$build_dir  = $plugin_dir . '/build';

		if ( ! file_exists( $build_dir . '/gallery-block.js' ) ) {
			return;
		}

		$plugin_url = plugins_url( '', dirname( __FILE__ ) );

		wp_register_script(
			'iu-gallery-block-editor',
			$plugin_url . '/build/gallery-block.js',
			[ 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-block-editor', 'wp-api-fetch' ],
			filemtime( $build_dir . '/gallery-block.js' ),
			true
		);

		if ( file_exists( $build_dir . '/gallery-block.css' ) ) {
			wp_register_style(
				'iu-gallery-block-editor-style',
				$plugin_url . '/build/gallery-block.css',
				[],
				filemtime( $build_dir . '/gallery-block.css' )
			);
		}

		if ( file_exists( $build_dir . '/style-gallery-block.css' ) ) {
			wp_register_style(
				'iu-gallery-block-style',
				$plugin_url . '/build/style-gallery-block.css',
				[],
				filemtime( $build_dir . '/style-gallery-block.css' )
			);
		}

		register_block_type( __DIR__ . '/gallery/block/block.json', [
			'render_callback' => [ $this, 'render_block' ],
			'editor_script'   => 'iu-gallery-block-editor',
			'editor_style'    => 'iu-gallery-block-editor-style',
			'style'           => 'iu-gallery-block-style',
		] );
	}

	public function render_block( $attributes ) {
		return iu_render_gallery( $attributes );
	}

	// -------------------------------------------------------------------------
	// REST API
	// -------------------------------------------------------------------------

	public function register_rest_routes() {
		register_rest_route( 'infinite-uploads/v1', '/gallery/folders', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'rest_get_folders' ],
			'permission_callback' => function () {
				return current_user_can( 'upload_files' );
			},
		] );

		register_rest_route( 'infinite-uploads/v1', '/gallery/images', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'rest_get_images' ],
			'permission_callback' => function () {
				return current_user_can( 'upload_files' );
			},
		] );
	}

	public function rest_get_folders() {
		global $wpdb;
		$table = $wpdb->prefix . 'infinite_uploads_media_folders';

		$rows = $wpdb->get_results(
			"SELECT id, name, parent_id FROM {$table} ORDER BY sort_order ASC, name ASC",
			ARRAY_A
		);

		if ( ! $rows ) {
			return rest_ensure_response( [] );
		}

		return rest_ensure_response( array_map( function ( $row ) {
			return [
				'id'        => (int) $row['id'],
				'name'      => $row['name'],
				'parent_id' => (int) $row['parent_id'],
			];
		}, $rows ) );
	}

	public function rest_get_images( \WP_REST_Request $request ) {
		$folder_id = (int) $request->get_param( 'folder_id' );
		$size      = sanitize_text_field( $request->get_param( 'size' ) ?: 'medium' );
		$orderby   = sanitize_text_field( $request->get_param( 'orderby' ) ?: 'date' );
		$order     = strtoupper( sanitize_text_field( $request->get_param( 'order' ) ?: 'DESC' ) );
		$limit     = min( (int) ( $request->get_param( 'limit' ) ?: 20 ), 100 );

		if ( ! in_array( $order, [ 'ASC', 'DESC' ], true ) ) {
			$order = 'DESC';
		}

		$media_folders = MediaFolders::get_instance();
		$ids           = $media_folders->get_attachment_ids_for_folder( $folder_id ?: null );

		if ( empty( $ids ) ) {
			return rest_ensure_response( [] );
		}

		$query = new \WP_Query( [
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post__in'       => $ids,
			'orderby'        => $orderby === 'rand' ? 'rand' : $orderby,
			'order'          => $order,
			'posts_per_page' => $limit,
		] );

		$images = [];
		foreach ( $query->posts as $post ) {
			$src = wp_get_attachment_image_src( $post->ID, $size );
			if ( ! $src ) {
				continue;
			}
			$images[] = [
				'id'  => $post->ID,
				'src' => $src[0],
				'alt' => get_post_meta( $post->ID, '_wp_attachment_image_alt', true ) ?: $post->post_title,
			];
		}

		return rest_ensure_response( $images );
	}

	// -------------------------------------------------------------------------
	// Shortcode
	// -------------------------------------------------------------------------

	public function shortcode_handler( $atts ) {
		$atts = shortcode_atts( [
			'folder_id' => 0,
			'columns'   => 3,
			'size'      => 'medium',
			'link'      => 'none',
			'orderby'   => 'date',
			'order'     => 'DESC',
			'lightbox'  => 0,
			'caption'   => 0,
		], $atts, 'iu_gallery' );

		return iu_render_gallery( [
			'folderId'  => (int) $atts['folder_id'],
			'columns'   => (int) $atts['columns'],
			'imageSize' => sanitize_text_field( $atts['size'] ),
			'linkTo'    => sanitize_text_field( $atts['link'] ),
			'orderby'   => sanitize_text_field( $atts['orderby'] ),
			'order'     => sanitize_text_field( $atts['order'] ),
			'lightbox'  => (bool) $atts['lightbox'],
			'caption'   => (bool) $atts['caption'],
		] );
	}

	// -------------------------------------------------------------------------
	// PhotoSwipe footer
	// -------------------------------------------------------------------------

	private static $photoswipe_printed = false;

	public function print_photoswipe_html() {
		if ( self::$photoswipe_printed ) {
			return;
		}
		// Only print if PhotoSwipe was enqueued (lightbox=true on at least one gallery).
		if ( ! wp_script_is( 'iu-photoswipe', 'enqueued' ) ) {
			return;
		}
		self::$photoswipe_printed = true;
		echo '<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="pswp__bg"></div>
			<div class="pswp__scroll-wrap">
				<div class="pswp__container">
					<div class="pswp__item"></div>
					<div class="pswp__item"></div>
					<div class="pswp__item"></div>
				</div>
				<div class="pswp__ui pswp__ui--hidden">
					<div class="pswp__top-bar">
						<div class="pswp__counter"></div>
						<button class="pswp__button pswp__button--close" title="Close (Esc)"></button>
						<button class="pswp__button pswp__button--share" title="Share"></button>
						<button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>
						<button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
						<div class="pswp__preloader">
							<div class="pswp__preloader__icn">
							  <div class="pswp__preloader__cut">
							    <div class="pswp__preloader__donut"></div>
							  </div>
							</div>
						</div>
					</div>
					<div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
						<div class="pswp__share-tooltip"></div>
					</div>
					<button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)"></button>
					<button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)"></button>
					<div class="pswp__caption">
						<div class="pswp__caption__center"></div>
					</div>
				</div>
			</div>
		</div>' . "\n";
	}

	// -------------------------------------------------------------------------
	// Page builder integrations
	// -------------------------------------------------------------------------

	public function init_integrations() {
		$integrations_dir = __DIR__ . '/gallery/integrations/';

		// Elementor
		if ( did_action( 'elementor/loaded' ) ) {
			require_once $integrations_dir . 'Elementor.php';
		}

		// Divi
		if ( class_exists( 'ET_Builder_Element' ) ) {
			require_once $integrations_dir . 'DiviModule.php';
		}

		// Beaver Builder
		if ( class_exists( 'FLBuilderLoader' ) ) {
			require_once $integrations_dir . 'BeaverModule.php';
		}

		// Bricks
		if ( defined( 'BRICKS_VERSION' ) ) {
			require_once $integrations_dir . 'BricksElement.php';
		}

		// Oxygen
		if ( defined( 'CT_VERSION' ) ) {
			require_once $integrations_dir . 'OxygenElement.php';
		}

		// Avada
		if ( class_exists( 'FusionBuilder' ) || defined( 'AVADA_VERSION' ) ) {
			require_once $integrations_dir . 'AvadaElement.php';
		}

		// WooCommerce
		if ( class_exists( 'WooCommerce' ) || defined( 'WC_VERSION' ) ) {
			require_once $integrations_dir . 'WooCommerce.php';
		}
	}
}
