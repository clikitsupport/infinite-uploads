<?php
/**
 * Media Library Usage Scanner — main results screen.
 *
 * Rendered from ClikIT\InfiniteUploads\MediaUsage\Scanner::render_page().
 * Available scope: $summary (array), $run (object|null), $can_admin (bool),
 * $assets_url (string, URL to inc/assets/).
 *
 * @package InfiniteUploads
 */

namespace ClikIT\InfiniteUploads\MediaUsage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$last_scan_text = '';
if ( $run && ! empty( $run->completed_at ) && '0000-00-00 00:00:00' !== $run->completed_at ) {
	$ts = strtotime( $run->completed_at . ' UTC' );
	if ( $ts ) {
		/* translators: %s: human-readable time difference. */
		$last_scan_text = sprintf( esc_html__( 'Last scan completed %s ago.', 'infinite-uploads' ), human_time_diff( $ts ) );
	}
}

$active_run = Store::get_active_run();
$is_running = ( $active_run && Store::RUN_RUNNING === $active_run->status );
$is_paused  = ( $active_run && Store::RUN_PAUSED === $active_run->status );
$export_url = wp_nonce_url( admin_url( 'admin-post.php?action=infinite_uploads_media_usage_export' ), 'iup_media_usage_export' );
?>
<div class="wrap iu-media-usage">

	<?php require __DIR__ . '/media-usage-header.php'; ?>

	<p class="iu-mu-intro"><?php esc_html_e( 'See which files are used on your site and which may be safe to remove. No files are deleted automatically — review before deleting.', 'infinite-uploads' ); ?></p>

	<div class="iu-mu-panel">
		<div class="iu-mu-summary">
			<div class="iu-mu-card iu-mu-card-total">
				<span class="iu-mu-num"><?php echo esc_html( number_format_i18n( $summary['total'] ) ); ?></span>
				<span class="iu-mu-label"><?php esc_html_e( 'Total items', 'infinite-uploads' ); ?></span>
			</div>
			<div class="iu-mu-card iu-usage-referenced">
				<span class="iu-mu-num"><?php echo esc_html( number_format_i18n( $summary['referenced'] ) ); ?></span>
				<span class="iu-mu-label"><?php esc_html_e( 'In use', 'infinite-uploads' ); ?></span>
			</div>
			<div class="iu-mu-card iu-usage-possibly_unused">
				<span class="iu-mu-num"><?php echo esc_html( number_format_i18n( $summary['possibly_unused'] ) ); ?></span>
				<span class="iu-mu-label"><?php esc_html_e( 'Possibly unused', 'infinite-uploads' ); ?></span>
			</div>
			<div class="iu-mu-card iu-usage-unknown">
				<span class="iu-mu-num"><?php echo esc_html( number_format_i18n( $summary['unknown'] ) ); ?></span>
				<span class="iu-mu-label"><?php esc_html_e( 'Unknown', 'infinite-uploads' ); ?></span>
			</div>
		</div>

		<div class="iu-mu-controls">
			<button type="button" class="iu-btn iu-btn-primary" id="iu-mu-run" style="<?php echo ( $is_running || $is_paused ) ? 'display:none;' : ''; ?>"><?php esc_html_e( 'Run scan', 'infinite-uploads' ); ?></button>
			<button type="button" class="iu-btn iu-btn-ghost" id="iu-mu-pause" style="<?php echo $is_running ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Pause', 'infinite-uploads' ); ?></button>
			<button type="button" class="iu-btn iu-btn-primary" id="iu-mu-resume" style="<?php echo $is_paused ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Resume', 'infinite-uploads' ); ?></button>
			<button type="button" class="iu-btn iu-btn-ghost" id="iu-mu-cancel" style="<?php echo ( $is_running || $is_paused ) ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Cancel', 'infinite-uploads' ); ?></button>
			<a href="<?php echo esc_url( $export_url ); ?>" class="iu-btn iu-btn-accent"><?php esc_html_e( 'Export CSV', 'infinite-uploads' ); ?></a>
			<span class="iu-mu-last-scan"><?php echo esc_html( $last_scan_text ); ?></span>

			<div class="iu-mu-progress" style="<?php echo ( $is_running || $is_paused ) ? '' : 'display:none;'; ?>" data-active="<?php echo $is_running ? '1' : '0'; ?>">
				<div class="iu-mu-progress-bar"><span class="iu-mu-progress-fill" style="width:0;"></span></div>
				<p class="iu-mu-progress-msg"></p>
				<p class="iu-mu-keepopen" style="display:none;"><?php esc_html_e( 'Your site’s background tasks (WP-Cron) don’t appear to be running, so this scan is running in your browser — keep this tab open until it finishes.', 'infinite-uploads' ); ?></p>
			</div>
		</div>
	</div>

	<div class="iu-mu-panel iu-mu-table-panel">
		<form method="get">
			<input type="hidden" name="page" value="iu-media-usage" />
			<?php
			$table = new ListTable();
			$table->prepare_items();
			$table->views();
			$table->search_box( esc_html__( 'Search files', 'infinite-uploads' ), 'iu-mu-search' );
			$table->display();
			?>
		</form>
	</div>

	<div class="iu-mu-panel iu-mu-support">
		<h3><?php esc_html_e( 'Hit a problem?', 'infinite-uploads' ); ?></h3>
		<p><?php esc_html_e( 'Download a diagnostic report and send it to Infinite Uploads support. It captures your environment and what the scanner did, so you don’t have to describe or remember what happened. No media files or passwords are included.', 'infinite-uploads' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="iu-mu-bugreport-form">
			<input type="hidden" name="action" value="infinite_uploads_media_usage_bug_report" />
			<?php wp_nonce_field( 'iup_media_usage_bug_report' ); ?>
			<textarea name="note" rows="2" maxlength="2000" class="iu-mu-bugreport-note" placeholder="<?php esc_attr_e( 'Optional: what were you doing when the problem happened?', 'infinite-uploads' ); ?>"></textarea>
			<button type="submit" class="iu-btn iu-btn-ghost"><?php esc_html_e( 'Download Bug Report', 'infinite-uploads' ); ?></button>
		</form>
	</div>

	<div class="iu-mu-footer">
		<strong><?php esc_html_e( 'The Cloud by Infinite Uploads', 'infinite-uploads' ); ?></strong>
	</div>

	<div id="iu-mu-detail" class="iu-mu-detail" style="display:none;">
		<div class="iu-mu-detail-inner">
			<button type="button" class="iu-mu-detail-close" aria-label="<?php esc_attr_e( 'Close', 'infinite-uploads' ); ?>">&times;</button>
			<h2><?php esc_html_e( 'Found references', 'infinite-uploads' ); ?></h2>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Source type', 'infinite-uploads' ); ?></th>
						<th><?php esc_html_e( 'Source', 'infinite-uploads' ); ?></th>
						<th><?php esc_html_e( 'How it\'s used', 'infinite-uploads' ); ?></th>
					</tr>
				</thead>
				<tbody class="iu-mu-detail-rows"></tbody>
			</table>
		</div>
	</div>
</div>
