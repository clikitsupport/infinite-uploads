<?php

namespace ClikIT\InfiniteUploads;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bricks Builder element for IU Media Folder Gallery.
 * Loaded only when BRICKS_VERSION is defined.
 */
if ( ! class_exists( '\Bricks\Element' ) ) {
	return;
}

class IU_Gallery_Bricks_Element extends \Bricks\Element {

	public $category = 'media';
	public $name     = 'iu-gallery';
	public $icon     = 'ti-layout-grid3';
	public $scripts  = [];

	public function get_label() {
		return esc_html__( 'IU Media Folder Gallery', 'infinite-uploads' );
	}

	public function set_controls() {
		global $wpdb;
		$table   = $wpdb->prefix . 'infinite_uploads_media_folders';
		$rows    = $wpdb->get_results( "SELECT id, name FROM {$table} ORDER BY name ASC", ARRAY_A );
		$folders = [ '0' => esc_html__( '— Select a folder —', 'infinite-uploads' ) ];
		foreach ( (array) $rows as $row ) {
			$folders[ (string) $row['id'] ] = $row['name'];
		}

		$this->controls['folder_id'] = [
			'tab'     => 'content',
			'label'   => esc_html__( 'Folder', 'infinite-uploads' ),
			'type'    => 'select',
			'options' => $folders,
			'default' => '0',
		];

		$this->controls['columns'] = [
			'tab'     => 'content',
			'label'   => esc_html__( 'Columns', 'infinite-uploads' ),
			'type'    => 'number',
			'min'     => 1,
			'max'     => 6,
			'default' => 3,
		];

		$this->controls['image_size'] = [
			'tab'     => 'content',
			'label'   => esc_html__( 'Image Size', 'infinite-uploads' ),
			'type'    => 'select',
			'options' => [
				'thumbnail' => esc_html__( 'Thumbnail', 'infinite-uploads' ),
				'medium'    => esc_html__( 'Medium', 'infinite-uploads' ),
				'large'     => esc_html__( 'Large', 'infinite-uploads' ),
				'full'      => esc_html__( 'Full Size', 'infinite-uploads' ),
			],
			'default' => 'medium',
		];

		$this->controls['link_to'] = [
			'tab'     => 'content',
			'label'   => esc_html__( 'Link To', 'infinite-uploads' ),
			'type'    => 'select',
			'options' => [
				'none'       => esc_html__( 'None', 'infinite-uploads' ),
				'file'       => esc_html__( 'Media File', 'infinite-uploads' ),
				'attachment' => esc_html__( 'Attachment Page', 'infinite-uploads' ),
			],
			'default' => 'none',
		];

		$this->controls['orderby'] = [
			'tab'     => 'content',
			'label'   => esc_html__( 'Order By', 'infinite-uploads' ),
			'type'    => 'select',
			'options' => [
				'date'  => esc_html__( 'Date', 'infinite-uploads' ),
				'title' => esc_html__( 'Title', 'infinite-uploads' ),
				'rand'  => esc_html__( 'Random', 'infinite-uploads' ),
			],
			'default' => 'date',
		];

		$this->controls['order'] = [
			'tab'     => 'content',
			'label'   => esc_html__( 'Order', 'infinite-uploads' ),
			'type'    => 'select',
			'options' => [
				'DESC' => esc_html__( 'Descending', 'infinite-uploads' ),
				'ASC'  => esc_html__( 'Ascending', 'infinite-uploads' ),
			],
			'default' => 'DESC',
		];

		$this->controls['lightbox'] = [
			'tab'     => 'content',
			'label'   => esc_html__( 'Enable Lightbox', 'infinite-uploads' ),
			'type'    => 'checkbox',
			'default' => false,
		];

		$this->controls['caption'] = [
			'tab'     => 'content',
			'label'   => esc_html__( 'Show Captions', 'infinite-uploads' ),
			'type'    => 'checkbox',
			'default' => false,
		];
	}

	public function render() {
		echo iu_render_gallery( [
			'folderId'  => (int) ( $this->settings['folder_id'] ?? 0 ),
			'columns'   => (int) ( $this->settings['columns'] ?? 3 ),
			'imageSize' => $this->settings['image_size'] ?? 'medium',
			'linkTo'    => $this->settings['link_to'] ?? 'none',
			'orderby'   => $this->settings['orderby'] ?? 'date',
			'order'     => $this->settings['order'] ?? 'DESC',
			'lightbox'  => ! empty( $this->settings['lightbox'] ),
			'caption'   => ! empty( $this->settings['caption'] ),
		] );
	}
}

// Register the element with Bricks.
add_filter( 'bricks/elements/registered_elements', function ( $elements ) {
	$elements['iu-gallery'] = 'ClikIT\InfiniteUploads\IU_Gallery_Bricks_Element';
	return $elements;
} );
