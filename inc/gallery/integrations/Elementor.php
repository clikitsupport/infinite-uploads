<?php

namespace ClikIT\InfiniteUploads;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
	return;
}

/**
 * Elementor widget for IU Media Folder Gallery.
 */
class IU_Gallery_Elementor_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'iu_gallery';
	}

	public function get_title() {
		return esc_html__( 'IU Media Folder Gallery', 'infinite-uploads' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_categories() {
		return [ 'media' ];
	}

	public function get_keywords() {
		return [ 'gallery', 'media', 'folder', 'infinite uploads', 'images' ];
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_gallery', [
			'label' => esc_html__( 'Gallery Settings', 'infinite-uploads' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'folder_id', [
			'label'   => esc_html__( 'Folder', 'infinite-uploads' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => $this->get_folder_options(),
			'default' => '0',
		] );

		$this->add_control( 'columns', [
			'label'   => esc_html__( 'Columns', 'infinite-uploads' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'min'     => 1,
			'max'     => 6,
			'default' => 3,
		] );

		$this->add_control( 'image_size', [
			'label'   => esc_html__( 'Image Size', 'infinite-uploads' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => [
				'thumbnail' => esc_html__( 'Thumbnail', 'infinite-uploads' ),
				'medium'    => esc_html__( 'Medium', 'infinite-uploads' ),
				'large'     => esc_html__( 'Large', 'infinite-uploads' ),
				'full'      => esc_html__( 'Full Size', 'infinite-uploads' ),
			],
			'default' => 'medium',
		] );

		$this->add_control( 'link_to', [
			'label'   => esc_html__( 'Link To', 'infinite-uploads' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => [
				'none'       => esc_html__( 'None', 'infinite-uploads' ),
				'file'       => esc_html__( 'Media File', 'infinite-uploads' ),
				'attachment' => esc_html__( 'Attachment Page', 'infinite-uploads' ),
			],
			'default' => 'none',
		] );

		$this->add_control( 'orderby', [
			'label'   => esc_html__( 'Order By', 'infinite-uploads' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => [
				'date'  => esc_html__( 'Date', 'infinite-uploads' ),
				'title' => esc_html__( 'Title', 'infinite-uploads' ),
				'rand'  => esc_html__( 'Random', 'infinite-uploads' ),
			],
			'default' => 'date',
		] );

		$this->add_control( 'order', [
			'label'   => esc_html__( 'Order', 'infinite-uploads' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => [
				'DESC' => esc_html__( 'Descending', 'infinite-uploads' ),
				'ASC'  => esc_html__( 'Ascending', 'infinite-uploads' ),
			],
			'default' => 'DESC',
		] );

		$this->add_control( 'lightbox', [
			'label'        => esc_html__( 'Enable Lightbox', 'infinite-uploads' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'label_on'     => esc_html__( 'Yes', 'infinite-uploads' ),
			'label_off'    => esc_html__( 'No', 'infinite-uploads' ),
			'return_value' => 'yes',
			'default'      => '',
		] );

		$this->add_control( 'caption', [
			'label'        => esc_html__( 'Show Captions', 'infinite-uploads' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'label_on'     => esc_html__( 'Yes', 'infinite-uploads' ),
			'label_off'    => esc_html__( 'No', 'infinite-uploads' ),
			'return_value' => 'yes',
			'default'      => '',
		] );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		echo iu_render_gallery( [
			'folderId'  => (int) ( $settings['folder_id'] ?? 0 ),
			'columns'   => (int) ( $settings['columns'] ?? 3 ),
			'imageSize' => $settings['image_size'] ?? 'medium',
			'linkTo'    => $settings['link_to'] ?? 'none',
			'orderby'   => $settings['orderby'] ?? 'date',
			'order'     => $settings['order'] ?? 'DESC',
			'lightbox'  => ( $settings['lightbox'] ?? '' ) === 'yes',
			'caption'   => ( $settings['caption'] ?? '' ) === 'yes',
		] );
	}

	private function get_folder_options() {
		global $wpdb;
		$table = $wpdb->prefix . 'infinite_uploads_media_folders';
		$rows  = $wpdb->get_results( "SELECT id, name FROM {$table} ORDER BY name ASC", ARRAY_A );
		$opts  = [ '0' => esc_html__( '— Select a folder —', 'infinite-uploads' ) ];
		foreach ( (array) $rows as $row ) {
			$opts[ (string) $row['id'] ] = $row['name'];
		}
		return $opts;
	}
}

