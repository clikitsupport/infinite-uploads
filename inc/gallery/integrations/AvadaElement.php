<?php

namespace ClikIT\InfiniteUploads;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Avada / Fusion Builder integration for IU Media Folder Gallery.
 * Loaded only when FusionBuilder class or AVADA_VERSION constant is present.
 */

// Register the shortcode that Fusion Builder will call.
add_shortcode( 'fusion_iu_gallery', function ( $atts ) {
	$atts = shortcode_atts( [
		'folder_id'  => 0,
		'columns'    => 3,
		'image_size' => 'medium',
		'link_to'    => 'none',
		'orderby'    => 'date',
		'order'      => 'DESC',
		'lightbox'   => 0,
		'caption'    => 0,
	], $atts, 'fusion_iu_gallery' );

	return iu_render_gallery( [
		'folderId'  => (int) $atts['folder_id'],
		'columns'   => (int) $atts['columns'],
		'imageSize' => sanitize_text_field( $atts['image_size'] ),
		'linkTo'    => sanitize_text_field( $atts['link_to'] ),
		'orderby'   => sanitize_text_field( $atts['orderby'] ),
		'order'     => sanitize_text_field( $atts['order'] ),
		'lightbox'  => (bool) $atts['lightbox'],
		'caption'   => (bool) $atts['caption'],
	] );
} );

// Map the element inside Fusion Builder.
add_action( 'fusion_builder_before_init', function () {
	if ( ! function_exists( 'fusion_builder_map' ) ) {
		return;
	}

	global $wpdb;
	$table   = $wpdb->prefix . 'infinite_uploads_media_folders';
	$rows    = $wpdb->get_results( "SELECT id, name FROM {$table} ORDER BY name ASC", ARRAY_A );
	$folders = [ [ 'label' => esc_html__( '— Select a folder —', 'infinite-uploads' ), 'value' => '0' ] ];
	foreach ( (array) $rows as $row ) {
		$folders[] = [ 'label' => $row['name'], 'value' => (string) $row['id'] ];
	}

	fusion_builder_map( [
		'name'       => esc_html__( 'IU Media Folder Gallery', 'infinite-uploads' ),
		'shortcode'  => 'fusion_iu_gallery',
		'icon'       => 'fusiona-images',
		'params'     => [
			[
				'type'        => 'select',
				'heading'     => esc_html__( 'Folder', 'infinite-uploads' ),
				'param_name'  => 'folder_id',
				'value'       => $folders,
				'default'     => '0',
			],
			[
				'type'        => 'range',
				'heading'     => esc_html__( 'Columns', 'infinite-uploads' ),
				'param_name'  => 'columns',
				'value'       => '3',
				'min'         => '1',
				'max'         => '6',
				'step'        => '1',
			],
			[
				'type'        => 'select',
				'heading'     => esc_html__( 'Image Size', 'infinite-uploads' ),
				'param_name'  => 'image_size',
				'value'       => [
					[ 'label' => esc_html__( 'Thumbnail', 'infinite-uploads' ), 'value' => 'thumbnail' ],
					[ 'label' => esc_html__( 'Medium', 'infinite-uploads' ),    'value' => 'medium'    ],
					[ 'label' => esc_html__( 'Large', 'infinite-uploads' ),     'value' => 'large'     ],
					[ 'label' => esc_html__( 'Full Size', 'infinite-uploads' ), 'value' => 'full'      ],
				],
				'default'     => 'medium',
			],
			[
				'type'        => 'select',
				'heading'     => esc_html__( 'Link To', 'infinite-uploads' ),
				'param_name'  => 'link_to',
				'value'       => [
					[ 'label' => esc_html__( 'None', 'infinite-uploads' ),            'value' => 'none'       ],
					[ 'label' => esc_html__( 'Media File', 'infinite-uploads' ),      'value' => 'file'       ],
					[ 'label' => esc_html__( 'Attachment Page', 'infinite-uploads' ), 'value' => 'attachment' ],
				],
				'default'     => 'none',
			],
			[
				'type'        => 'select',
				'heading'     => esc_html__( 'Order By', 'infinite-uploads' ),
				'param_name'  => 'orderby',
				'value'       => [
					[ 'label' => esc_html__( 'Date', 'infinite-uploads' ),   'value' => 'date'  ],
					[ 'label' => esc_html__( 'Title', 'infinite-uploads' ),  'value' => 'title' ],
					[ 'label' => esc_html__( 'Random', 'infinite-uploads' ), 'value' => 'rand'  ],
				],
				'default'     => 'date',
			],
			[
				'type'        => 'select',
				'heading'     => esc_html__( 'Order', 'infinite-uploads' ),
				'param_name'  => 'order',
				'value'       => [
					[ 'label' => esc_html__( 'Descending', 'infinite-uploads' ), 'value' => 'DESC' ],
					[ 'label' => esc_html__( 'Ascending', 'infinite-uploads' ),  'value' => 'ASC'  ],
				],
				'default'     => 'DESC',
			],
			[
				'type'        => 'select',
				'heading'     => esc_html__( 'Enable Lightbox', 'infinite-uploads' ),
				'param_name'  => 'lightbox',
				'value'       => [
					[ 'label' => esc_html__( 'No', 'infinite-uploads' ),  'value' => '0' ],
					[ 'label' => esc_html__( 'Yes', 'infinite-uploads' ), 'value' => '1' ],
				],
				'default'     => '0',
			],
			[
				'type'        => 'select',
				'heading'     => esc_html__( 'Show Captions', 'infinite-uploads' ),
				'param_name'  => 'caption',
				'value'       => [
					[ 'label' => esc_html__( 'No', 'infinite-uploads' ),  'value' => '0' ],
					[ 'label' => esc_html__( 'Yes', 'infinite-uploads' ), 'value' => '1' ],
				],
				'default'     => '0',
			],
		],
	] );
} );
