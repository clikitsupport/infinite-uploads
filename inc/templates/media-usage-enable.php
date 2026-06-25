<?php
/**
 * Media Library Usage Scanner — enable screen.
 *
 * Shown when the site is connected but the feature toggle is off. Rendered from
 * Scanner::render_page(). Admins get an enable button; others are told to ask
 * an administrator. Scope: $assets_url (URL to inc/assets/).
 *
 * @package InfiniteUploads
 */

namespace ClikIT\InfiniteUploads\MediaUsage;

use ClikIT\InfiniteUploads\InfiniteUploads;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$can_admin = current_user_can( InfiniteUploads::get_instance()->capability );
?>
<div class="wrap iu-media-usage">

	<div class="iu-mu-brandbar">
		<img class="iu-mu-logo" src="<?php echo esc_url( $assets_url . 'img/iu-logo-words.svg' ); ?>" alt="<?php esc_attr_e( 'Infinite Uploads', 'infinite-uploads' ); ?>" />
		<div class="iu-mu-brandbar-text">
			<h1><?php esc_html_e( 'Media Cleanup', 'infinite-uploads' ); ?></h1>
		</div>
	</div>

	<div class="iu-mu-panel iu-mu-hero">
		<h2><?php esc_html_e( 'Enable Media Cleanup', 'infinite-uploads' ); ?></h2>
		<p class="iu-mu-lead"><?php esc_html_e( 'Find files that may no longer be used on your site, so you can review and tidy up your Media Library. No files are deleted automatically.', 'infinite-uploads' ); ?></p>
		<ul class="iu-mu-features">
			<li><?php esc_html_e( 'Checks post content, page builder data, WooCommerce galleries, options and theme settings.', 'infinite-uploads' ); ?></li>
			<li><?php esc_html_e( 'Runs in the background in small batches to avoid slowing your site.', 'infinite-uploads' ); ?></li>
			<li><?php esc_html_e( 'Flags items as In use, Possibly unused, or Unknown — never as definitely unused.', 'infinite-uploads' ); ?></li>
		</ul>

		<?php if ( $can_admin ) : ?>
			<p>
				<button type="button" class="iu-btn iu-btn-primary iu-btn-lg" id="iu-mu-enable"><?php esc_html_e( 'Enable Media Cleanup', 'infinite-uploads' ); ?></button>
			</p>
			<p class="iu-mu-muted"><?php esc_html_e( 'You can disable this feature at any time from the Infinite Uploads settings.', 'infinite-uploads' ); ?></p>
		<?php else : ?>
			<p class="iu-mu-muted"><?php esc_html_e( 'This feature is currently disabled. Please ask a site administrator to enable it.', 'infinite-uploads' ); ?></p>
		<?php endif; ?>
	</div>

	<div class="iu-mu-footer">
		<strong><?php esc_html_e( 'The Cloud by Infinite Uploads', 'infinite-uploads' ); ?></strong>
	</div>
</div>
