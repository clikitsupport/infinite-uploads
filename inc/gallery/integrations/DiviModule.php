<?php

namespace ClikIT\InfiniteUploads;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Divi Builder module for IU Media Folder Gallery.
 * Loaded only when ET_Builder_Element is available.
 */
add_action( 'et_builder_ready', function () {
	if ( ! class_exists( 'ET_Builder_Module' ) ) {
		return;
	}

	class IU_Gallery_Divi_Module extends \ET_Builder_Module {

		public $slug       = 'iu_gallery';
		public $vb_support = 'on';

		protected $module_credits = [
			'module_uri' => '',
			'author'     => 'Infinite Uploads',
			'author_uri' => '',
		];

		public function init() {
			$this->name = esc_html__( 'IU Media Folder Gallery', 'infinite-uploads' );
		}

		public function get_fields() {
			global $wpdb;
			$table   = $wpdb->prefix . 'infinite_uploads_media_folders';
			$rows    = $wpdb->get_results( "SELECT id, name FROM {$table} ORDER BY name ASC", ARRAY_A );
			$folders = [ '0' => esc_html__( '— Select a folder —', 'infinite-uploads' ) ];
			foreach ( (array) $rows as $row ) {
				$folders[ (string) $row['id'] ] = $row['name'];
			}

			return [
				'folder_id'  => [
					'label'           => esc_html__( 'Folder', 'infinite-uploads' ),
					'type'            => 'select',
					'option_category' => 'basic_option',
					'options'         => $folders,
					'default'         => '0',
					'description'     => esc_html__( 'Select a media folder.', 'infinite-uploads' ),
				],
				'columns'    => [
					'label'           => esc_html__( 'Columns', 'infinite-uploads' ),
					'type'            => 'range',
					'option_category' => 'basic_option',
					'range_settings'  => [ 'min' => 1, 'max' => 6, 'step' => 1 ],
					'default'         => '3',
				],
				'image_size' => [
					'label'           => esc_html__( 'Image Size', 'infinite-uploads' ),
					'type'            => 'select',
					'option_category' => 'basic_option',
					'options'         => [
						'thumbnail' => esc_html__( 'Thumbnail', 'infinite-uploads' ),
						'medium'    => esc_html__( 'Medium', 'infinite-uploads' ),
						'large'     => esc_html__( 'Large', 'infinite-uploads' ),
						'full'      => esc_html__( 'Full Size', 'infinite-uploads' ),
					],
					'default' => 'medium',
				],
				'link_to'    => [
					'label'           => esc_html__( 'Link To', 'infinite-uploads' ),
					'type'            => 'select',
					'option_category' => 'basic_option',
					'options'         => [
						'none'       => esc_html__( 'None', 'infinite-uploads' ),
						'file'       => esc_html__( 'Media File', 'infinite-uploads' ),
						'attachment' => esc_html__( 'Attachment Page', 'infinite-uploads' ),
					],
					'default' => 'none',
				],
				'orderby'    => [
					'label'           => esc_html__( 'Order By', 'infinite-uploads' ),
					'type'            => 'select',
					'option_category' => 'basic_option',
					'options'         => [
						'date'  => esc_html__( 'Date', 'infinite-uploads' ),
						'title' => esc_html__( 'Title', 'infinite-uploads' ),
						'rand'  => esc_html__( 'Random', 'infinite-uploads' ),
					],
					'default' => 'date',
				],
				'order'      => [
					'label'           => esc_html__( 'Order', 'infinite-uploads' ),
					'type'            => 'select',
					'option_category' => 'basic_option',
					'options'         => [
						'DESC' => esc_html__( 'Descending', 'infinite-uploads' ),
						'ASC'  => esc_html__( 'Ascending', 'infinite-uploads' ),
					],
					'default' => 'DESC',
				],
				'lightbox'   => [
					'label'           => esc_html__( 'Enable Lightbox', 'infinite-uploads' ),
					'type'            => 'yes_no_button',
					'option_category' => 'basic_option',
					'options'         => [ 'off' => esc_html__( 'No', 'infinite-uploads' ), 'on' => esc_html__( 'Yes', 'infinite-uploads' ) ],
					'default'         => 'off',
				],
				'caption'    => [
					'label'           => esc_html__( 'Show Captions', 'infinite-uploads' ),
					'type'            => 'yes_no_button',
					'option_category' => 'basic_option',
					'options'         => [ 'off' => esc_html__( 'No', 'infinite-uploads' ), 'on' => esc_html__( 'Yes', 'infinite-uploads' ) ],
					'default'         => 'off',
				],
			];
		}

		public function render( $attrs, $content, $render_slug ) {
			return iu_render_gallery( [
				'folderId'  => (int) $this->props['folder_id'],
				'columns'   => (int) $this->props['columns'],
				'imageSize' => $this->props['image_size'],
				'linkTo'    => $this->props['link_to'],
				'orderby'   => $this->props['orderby'],
				'order'     => $this->props['order'],
				'lightbox'  => $this->props['lightbox'] === 'on',
				'caption'   => $this->props['caption'] === 'on',
			] );
		}
	}
} );
