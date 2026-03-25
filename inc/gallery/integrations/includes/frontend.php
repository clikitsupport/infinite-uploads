<?php
// Beaver Builder frontend template for IU Gallery module.
namespace ClikIT\InfiniteUploads;

echo iu_render_gallery( [
	'folderId'  => (int) $module->settings->folder_id,
	'columns'   => (int) $module->settings->columns,
	'imageSize' => $module->settings->image_size,
	'linkTo'    => $module->settings->link_to,
	'orderby'   => $module->settings->orderby,
	'order'     => $module->settings->order,
	'lightbox'  => (bool) $module->settings->lightbox,
	'caption'   => (bool) $module->settings->caption,
] );
