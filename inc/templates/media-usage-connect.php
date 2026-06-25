<?php
/**
 * Media Library Usage Scanner — connection-required gate.
 *
 * Defensive fallback shown if the screen is reached while the site is not
 * connected to Infinite Uploads. Scope: $assets_url (URL to inc/assets/).
 *
 * @package InfiniteUploads
 */

namespace ClikIT\InfiniteUploads\MediaUsage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$connect_url = is_multisite() ? network_admin_url( 'admin.php?page=infinite_uploads' ) : admin_url( 'admin.php?page=infinite_uploads' );
?>
<div class="wrap iu-media-usage">

	<div class="iu-mu-brandbar">
		<img class="iu-mu-logo" src="<?php echo esc_url( $assets_url . 'img/iu-logo-words.svg' ); ?>" alt="<?php esc_attr_e( 'Infinite Uploads', 'infinite-uploads' ); ?>" />
		<div class="iu-mu-brandbar-text">
			<h1><?php esc_html_e( 'Media Cleanup', 'infinite-uploads' ); ?></h1>
		</div>
	</div>

	<div class="iu-mu-panel iu-mu-hero">
		<h2><?php esc_html_e( 'Connect your site to use this feature', 'infinite-uploads' ); ?></h2>
		<p class="iu-mu-lead"><?php esc_html_e( 'Media Cleanup is available once your site is connected to Infinite Uploads.', 'infinite-uploads' ); ?></p>
		<p>
			<a href="<?php echo esc_url( $connect_url ); ?>" class="iu-btn iu-btn-primary iu-btn-lg"><?php esc_html_e( 'Connect to Infinite Uploads', 'infinite-uploads' ); ?></a>
		</p>
	</div>

	<div class="iu-mu-footer">
		<strong><?php esc_html_e( 'The Cloud by Infinite Uploads', 'infinite-uploads' ); ?></strong>
	</div>
</div>
