<?php
/**
 * Shared header for the Media Library Usage page: brand bar + tab nav.
 *
 * Expects in scope: $assets_url (string), $active_tab ('usage'|'duplicates'),
 * $show_duplicates_tab (bool).
 *
 * @package InfiniteUploads
 */

namespace ClikIT\InfiniteUploads\MediaUsage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$iu_mu_base = menu_page_url( 'iu-media-usage', false );
?>
<?php
// WordPress moves every plugin's admin notice to just before the first
// `.wp-header-end` element in the .wrap. Placing the marker here — above our
// brand bar — pins global notices (e.g. WP Staging's) to the very top of the
// page, the standard WordPress location, instead of inside or below our header.
?>
<hr class="wp-header-end" />
<div class="iu-mu-brandbar">
	<img class="iu-mu-logo" src="<?php echo esc_url( $assets_url . 'img/iu-logo-words.svg' ); ?>" alt="<?php esc_attr_e( 'Infinite Uploads', 'infinite-uploads' ); ?>" />
	<div class="iu-mu-brandbar-text">
		<h1><?php esc_html_e( 'Media Cleanup', 'infinite-uploads' ); ?><span class="iu-mu-beta"><?php esc_html_e( 'Beta', 'infinite-uploads' ); ?></span></h1>
	</div>
</div>

<h2 class="nav-tab-wrapper iu-mu-tabs">
	<a href="<?php echo esc_url( $iu_mu_base ); ?>" class="nav-tab <?php echo ( 'usage' === $active_tab ) ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Unused Files', 'infinite-uploads' ); ?></a>
	<?php if ( $show_duplicates_tab ) : ?>
		<a href="<?php echo esc_url( add_query_arg( 'tab', 'duplicates', $iu_mu_base ) ); ?>" class="nav-tab <?php echo ( 'duplicates' === $active_tab ) ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Possible Duplicates', 'infinite-uploads' ); ?></a>
	<?php endif; ?>
</h2>
