<?php

namespace ClikIT\InfiniteUploads;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder module for IU Media Folder Gallery.
 * Loaded only when FLBuilderLoader is available.
 */
if ( ! class_exists( 'FLBuilderModule' ) ) {
	return;
}

class IU_Gallery_BB_Module extends \FLBuilderModule {

	public function __construct() {
		parent::__construct( [
			'name'            => esc_html__( 'IU Media Folder Gallery', 'infinite-uploads' ),
			'description'     => esc_html__( 'Display images from an Infinite Uploads media folder.', 'infinite-uploads' ),
			'group'           => esc_html__( 'Media', 'infinite-uploads' ),
			'category'        => esc_html__( 'Media', 'infinite-uploads' ),
			'dir'             => __DIR__ . '/',
			'url'             => plugins_url( '', __FILE__ ),
			'icon'            => 'format-gallery.svg',
			'partial_refresh' => true,
		] );
	}
}

// Build folder options for the form.
function _iu_bb_get_folder_options() {
	global $wpdb;
	$table = $wpdb->prefix . 'infinite_uploads_media_folders';
	$rows  = $wpdb->get_results( "SELECT id, name FROM {$table} ORDER BY name ASC", ARRAY_A );
	$opts  = [ '0' => esc_html__( '— Select a folder —', 'infinite-uploads' ) ];
	foreach ( (array) $rows as $row ) {
		$opts[ (string) $row['id'] ] = $row['name'];
	}
	return $opts;
}

\FLBuilder::register_module( 'ClikIT\InfiniteUploads\IU_Gallery_BB_Module', [
	'general' => [
		'title'    => esc_html__( 'General', 'infinite-uploads' ),
		'sections' => [
			'main' => [
				'title'  => esc_html__( 'Gallery Settings', 'infinite-uploads' ),
				'fields' => [
					'folder_id'  => [
						'type'    => 'select',
						'label'   => esc_html__( 'Folder', 'infinite-uploads' ),
						'default' => '0',
						'options' => _iu_bb_get_folder_options(),
					],
					'columns'    => [
						'type'    => 'unit',
						'label'   => esc_html__( 'Columns', 'infinite-uploads' ),
						'default' => '3',
					],
					'image_size' => [
						'type'    => 'select',
						'label'   => esc_html__( 'Image Size', 'infinite-uploads' ),
						'default' => 'medium',
						'options' => [
							'thumbnail' => esc_html__( 'Thumbnail', 'infinite-uploads' ),
							'medium'    => esc_html__( 'Medium', 'infinite-uploads' ),
							'large'     => esc_html__( 'Large', 'infinite-uploads' ),
							'full'      => esc_html__( 'Full Size', 'infinite-uploads' ),
						],
					],
					'link_to'    => [
						'type'    => 'select',
						'label'   => esc_html__( 'Link To', 'infinite-uploads' ),
						'default' => 'none',
						'options' => [
							'none'       => esc_html__( 'None', 'infinite-uploads' ),
							'file'       => esc_html__( 'Media File', 'infinite-uploads' ),
							'attachment' => esc_html__( 'Attachment Page', 'infinite-uploads' ),
						],
					],
					'orderby'    => [
						'type'    => 'select',
						'label'   => esc_html__( 'Order By', 'infinite-uploads' ),
						'default' => 'date',
						'options' => [
							'date'  => esc_html__( 'Date', 'infinite-uploads' ),
							'title' => esc_html__( 'Title', 'infinite-uploads' ),
							'rand'  => esc_html__( 'Random', 'infinite-uploads' ),
						],
					],
					'order'      => [
						'type'    => 'select',
						'label'   => esc_html__( 'Order', 'infinite-uploads' ),
						'default' => 'DESC',
						'options' => [
							'DESC' => esc_html__( 'Descending', 'infinite-uploads' ),
							'ASC'  => esc_html__( 'Ascending', 'infinite-uploads' ),
						],
					],
					'lightbox'   => [
						'type'    => 'select',
						'label'   => esc_html__( 'Enable Lightbox', 'infinite-uploads' ),
						'default' => '0',
						'options' => [
							'0' => esc_html__( 'No', 'infinite-uploads' ),
							'1' => esc_html__( 'Yes', 'infinite-uploads' ),
						],
					],
					'caption'    => [
						'type'    => 'select',
						'label'   => esc_html__( 'Show Captions', 'infinite-uploads' ),
						'default' => '0',
						'options' => [
							'0' => esc_html__( 'No', 'infinite-uploads' ),
							'1' => esc_html__( 'Yes', 'infinite-uploads' ),
						],
					],
				],
			],
		],
	],
] );

// Create the frontend template file if it doesn't exist.
$tpl_file = __DIR__ . '/beaver-frontend.php';
if ( ! file_exists( $tpl_file ) ) {
	file_put_contents( $tpl_file, '<?php
// Beaver Builder frontend template for IU Gallery module.
namespace ClikIT\InfiniteUploads;
echo iu_render_gallery([
    \'folderId\'  => (int) $module->settings->folder_id,
    \'columns\'   => (int) $module->settings->columns,
    \'imageSize\' => $module->settings->image_size,
    \'linkTo\'    => $module->settings->link_to,
    \'orderby\'   => $module->settings->orderby,
    \'order\'     => $module->settings->order,
    \'lightbox\'  => (bool) $module->settings->lightbox,
    \'caption\'   => (bool) $module->settings->caption,
]);
' );
}
