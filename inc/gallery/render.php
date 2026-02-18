<?php

namespace ClikIT\InfiniteUploads;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared gallery renderer used by Gutenberg block, shortcode, and page builder integrations.
 *
 * @param array $args {
 *   @type int    $folderId  Folder ID (0 = all). Default 0.
 *   @type int    $columns   Number of columns. Default 3.
 *   @type string $imageSize Image size slug. Default 'medium'.
 *   @type string $linkTo    'none', 'file', or 'attachment'. Default 'none'.
 *   @type string $orderby   'date', 'title', or 'rand'. Default 'date'.
 *   @type string $order     'DESC' or 'ASC'. Default 'DESC'.
 *   @type bool   $lightbox  Whether to enable PhotoSwipe lightbox. Default false.
 *   @type bool   $caption   Whether to show captions. Default false.
 * }
 * @return string HTML output.
 */
function iu_render_gallery( array $args ): string {
	$defaults = [
		'folderId'  => 0,
		'columns'   => 3,
		'imageSize' => 'medium',
		'linkTo'    => 'none',
		'orderby'   => 'date',
		'order'     => 'DESC',
		'lightbox'  => false,
		'caption'   => false,
	];

	$args    = array_merge( $defaults, $args );
	$columns = max( 1, min( 6, (int) $args['columns'] ) );
	$order   = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

	$allowed_orderby = [ 'date', 'title', 'rand', 'menu_order', 'ID' ];
	$orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'date';

	$folder_id = (int) $args['folderId'];

	// Retrieve attachment IDs for the folder.
	$media_folders = MediaFolders::get_instance();
	$attachment_ids = $media_folders->get_attachment_ids_for_folder( $folder_id > 0 ? $folder_id : null );

	if ( empty( $attachment_ids ) ) {
		return '<p class="iu-gallery-empty">' . esc_html__( 'No images found in this folder.', 'infinite-uploads' ) . '</p>';
	}

	$query_args = [
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'post__in'       => $attachment_ids,
		'posts_per_page' => -1,
		'orderby'        => $orderby,
		'order'          => $order,
	];

	if ( $orderby === 'rand' ) {
		unset( $query_args['order'] );
	}

	$query = new \WP_Query( $query_args );

	if ( ! $query->have_posts() ) {
		return '<p class="iu-gallery-empty">' . esc_html__( 'No images found in this folder.', 'infinite-uploads' ) . '</p>';
	}

	// Enqueue PhotoSwipe if lightbox is enabled.
	if ( $args['lightbox'] ) {
		iu_enqueue_photoswipe();
	}

	$link_to  = in_array( $args['linkTo'], [ 'none', 'file', 'attachment' ], true ) ? $args['linkTo'] : 'none';
	$img_size = sanitize_text_field( $args['imageSize'] ?: 'medium' );

	$html = sprintf(
		'<div class="iu-gallery iu-gallery-columns-%d"%s>',
		$columns,
		$args['lightbox'] ? ' data-iu-lightbox="1"' : ''
	);
	$html .= sprintf( '<style>.iu-gallery.iu-gallery-columns-%d{--iu-columns:%d}</style>', $columns, $columns );

	foreach ( $query->posts as $attachment ) {
		$img_src   = wp_get_attachment_image_src( $attachment->ID, $img_size );
		$full_src  = wp_get_attachment_image_src( $attachment->ID, 'full' );
		$alt_text  = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );
		$alt_text  = $alt_text ?: $attachment->post_title;
		$caption_text = $attachment->post_excerpt;

		if ( ! $img_src ) {
			continue;
		}

		$img_tag = sprintf(
			'<img src="%s" alt="%s" class="iu-gallery-img" loading="lazy" width="%d" height="%d" />',
			esc_url( $img_src[0] ),
			esc_attr( $alt_text ),
			(int) $img_src[1],
			(int) $img_src[2]
		);

		$html .= '<figure class="iu-gallery-item">';

		if ( $link_to === 'file' || $args['lightbox'] ) {
			$href          = $full_src ? $full_src[0] : $img_src[0];
			$pswp_attrs    = '';
			if ( $args['lightbox'] && $full_src ) {
				$pswp_attrs = sprintf(
					' data-pswp-src="%s" data-pswp-width="%d" data-pswp-height="%d"',
					esc_url( $full_src[0] ),
					(int) $full_src[1],
					(int) $full_src[2]
				);
			}
			$html .= sprintf(
				'<a href="%s" class="iu-gallery-link"%s>%s</a>',
				esc_url( $href ),
				$pswp_attrs,
				$img_tag
			);
		} elseif ( $link_to === 'attachment' ) {
			$html .= sprintf(
				'<a href="%s" class="iu-gallery-link">%s</a>',
				esc_url( get_permalink( $attachment->ID ) ),
				$img_tag
			);
		} else {
			$html .= $img_tag;
		}

		if ( $args['caption'] && ! empty( $caption_text ) ) {
			$html .= '<figcaption class="iu-gallery-caption">' . esc_html( $caption_text ) . '</figcaption>';
		}

		$html .= '</figure>';
	}

	$html .= '</div>';

	return $html;
}

/**
 * Enqueue PhotoSwipe assets and the init script.
 */
function iu_enqueue_photoswipe() {
	static $enqueued = false;
	if ( $enqueued ) {
		return;
	}
	$enqueued   = true;
	$plugin_url = plugins_url( '', dirname( __DIR__ ) );
	$plugin_dir = dirname( __DIR__ );

	$pswp_js  = $plugin_url . '/inc/assets/photoswipe/photoswipe.min.js';
	$pswp_ui  = $plugin_url . '/inc/assets/photoswipe/photoswipe-ui-default.min.js';
	$pswp_css = $plugin_url . '/inc/assets/photoswipe/photoswipe.css';
	$pswp_skin = $plugin_url . '/inc/assets/photoswipe/default-skin/default-skin.css';

	$pswp_js_ver  = file_exists( $plugin_dir . '/inc/assets/photoswipe/photoswipe.min.js' ) ? filemtime( $plugin_dir . '/inc/assets/photoswipe/photoswipe.min.js' ) : '4.1.3';
	$pswp_css_ver = file_exists( $plugin_dir . '/inc/assets/photoswipe/photoswipe.css' ) ? filemtime( $plugin_dir . '/inc/assets/photoswipe/photoswipe.css' ) : '4.1.3';

	wp_enqueue_script( 'iu-photoswipe', $pswp_js, [], $pswp_js_ver, true );
	wp_enqueue_script( 'iu-photoswipe-ui', $pswp_ui, [ 'iu-photoswipe' ], $pswp_js_ver, true );
	wp_enqueue_style( 'iu-photoswipe', $pswp_css, [], $pswp_css_ver );
	wp_enqueue_style( 'iu-photoswipe-skin', $pswp_skin, [ 'iu-photoswipe' ], $pswp_css_ver );

	wp_add_inline_script( 'iu-photoswipe-ui', iu_photoswipe_init_js(), 'after' );
}

/**
 * Returns inline JS to initialize PhotoSwipe on .iu-gallery[data-iu-lightbox] clicks.
 */
function iu_photoswipe_init_js(): string {
	return <<<'JS'
(function(){
	function openPhotoSwipe(index, galleryEl) {
		var pswpEl = document.querySelector('.pswp');
		if (!pswpEl) return;
		var links = galleryEl.querySelectorAll('.iu-gallery-link[data-pswp-src]');
		var items = [];
		links.forEach(function(link) {
			items.push({
				src:  link.getAttribute('data-pswp-src'),
				w:    parseInt(link.getAttribute('data-pswp-width'), 10)  || 0,
				h:    parseInt(link.getAttribute('data-pswp-height'), 10) || 0,
				title: link.querySelector('img') ? link.querySelector('img').getAttribute('alt') : ''
			});
		});
		var gallery = new PhotoSwipe(pswpEl, PhotoSwipeUI_Default, items, {
			index: index,
			bgOpacity: 0.85,
			showHideOpacity: true,
			history: false
		});
		gallery.init();
	}

	document.addEventListener('click', function(e) {
		var link = e.target.closest('.iu-gallery[data-iu-lightbox] .iu-gallery-link[data-pswp-src]');
		if (!link) return;
		e.preventDefault();
		var galleryEl = link.closest('.iu-gallery');
		var links = Array.from(galleryEl.querySelectorAll('.iu-gallery-link[data-pswp-src]'));
		openPhotoSwipe(links.indexOf(link), galleryEl);
	});
})();
JS;
}
