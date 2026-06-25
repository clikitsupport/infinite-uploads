<?php
/**
 * Possible Duplicate Images tab.
 *
 * Rendered by ClikIT\InfiniteUploads\MediaUsage\Duplicates::render().
 * Expects in scope: $assets_url, $active_tab, $show_duplicates_tab, $can_admin,
 * $enabled, $settings (array), $results (array), $ignored (array), $show_ignored,
 * $uploaders (array<int,string> attachment_id => uploader display name).
 *
 * @package InfiniteUploads
 */

namespace ClikIT\InfiniteUploads\MediaUsage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$iu_dup_label = function ( $label ) {
	switch ( $label ) {
		case DuplicateFinder::LABEL_LIKELY:
			return __( 'Likely Duplicate', 'infinite-uploads' );
		case DuplicateFinder::LABEL_POSSIBLE:
			return __( 'Possible Duplicate', 'infinite-uploads' );
		case DuplicateFinder::LABEL_NEEDS_REVIEW:
			return __( 'Needs Review', 'infinite-uploads' );
		default:
			return __( 'Weak match', 'infinite-uploads' );
	}
};
?>
<div class="wrap iu-media-usage">

	<?php require __DIR__ . '/media-usage-header.php'; ?>

	<?php if ( ! $can_admin ) : ?>

		<div class="iu-mu-panel iu-mu-hero">
			<p class="iu-mu-lead"><?php esc_html_e( 'Possible Duplicate detection is available to administrators only.', 'infinite-uploads' ); ?></p>
		</div>

	<?php elseif ( ! $enabled ) : ?>

		<div class="iu-mu-panel iu-mu-hero">
			<h2><?php esc_html_e( 'Find possible duplicate images', 'infinite-uploads' ); ?></h2>
			<p class="iu-mu-lead"><?php esc_html_e( 'Spot images that may have been uploaded more than once, using fast, low-risk signals from your Media Library. Nothing is deleted, and uploads are never slowed down.', 'infinite-uploads' ); ?></p>
			<ul class="iu-mu-features">
				<li><?php esc_html_e( 'Groups images by similar filename, dimensions, type and size.', 'infinite-uploads' ); ?></li>
				<li><?php esc_html_e( 'Runs only when you click Scan — never on upload.', 'infinite-uploads' ); ?></li>
				<li><?php esc_html_e( 'Optional exact-match check hashes only the files in a group.', 'infinite-uploads' ); ?></li>
			</ul>
			<p>
				<button type="button" class="iu-btn iu-btn-primary iu-btn-lg" id="iu-dup-enable"><?php esc_html_e( 'Enable Possible Duplicate Detection', 'infinite-uploads' ); ?></button>
			</p>
		</div>

	<?php else : ?>

		<?php
		$iu_dup_groups   = isset( $results['groups'] ) ? $results['groups'] : array();
		$iu_dup_shown    = 0;
		$iu_dup_images   = 0;
		$iu_dup_ignored  = 0;
		foreach ( $iu_dup_groups as $iu_g ) {
			if ( isset( $ignored[ $iu_g['key'] ] ) ) {
				$iu_dup_ignored++;
				continue;
			}
			$iu_dup_shown++;
			$iu_dup_images += count( $iu_g['members'] );
		}
		$iu_dup_last = ! empty( $results['last_ran'] )
			/* translators: %s: date/time of last scan. */
			? sprintf( esc_html__( 'Last scan: %s', 'infinite-uploads' ), esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $results['last_ran'] ) ) )
			: esc_html__( 'Not scanned yet.', 'infinite-uploads' );
		$iu_dup_base = add_query_arg( 'tab', 'duplicates', menu_page_url( 'iu-media-usage', false ) );
		?>

		<div class="iu-mu-panel">
			<div class="iu-mu-summary">
				<div class="iu-mu-card iu-mu-card-total">
					<span class="iu-mu-num"><?php echo esc_html( number_format_i18n( $iu_dup_shown ) ); ?></span>
					<span class="iu-mu-label"><?php esc_html_e( 'Duplicate groups', 'infinite-uploads' ); ?></span>
				</div>
				<div class="iu-mu-card">
					<span class="iu-mu-num"><?php echo esc_html( number_format_i18n( $iu_dup_images ) ); ?></span>
					<span class="iu-mu-label"><?php esc_html_e( 'Images to review', 'infinite-uploads' ); ?></span>
				</div>
				<div class="iu-mu-card">
					<span class="iu-mu-num"><?php echo esc_html( number_format_i18n( $iu_dup_ignored ) ); ?></span>
					<span class="iu-mu-label"><?php esc_html_e( 'Ignored groups', 'infinite-uploads' ); ?></span>
				</div>
			</div>

			<div class="iu-mu-controls">
				<button type="button" class="iu-btn iu-btn-primary" id="iu-dup-scan"><?php echo empty( $results['last_ran'] ) ? esc_html__( 'Scan for Possible Duplicates', 'infinite-uploads' ) : esc_html__( 'Refresh Scan', 'infinite-uploads' ); ?></button>
				<?php if ( $iu_dup_ignored ) : ?>
					<a href="<?php echo esc_url( $show_ignored ? $iu_dup_base : add_query_arg( 'show_ignored', '1', $iu_dup_base ) ); ?>" class="iu-btn iu-btn-ghost"><?php echo $show_ignored ? esc_html__( 'Hide Ignored Groups', 'infinite-uploads' ) : esc_html__( 'Show Ignored Groups', 'infinite-uploads' ); ?></a>
				<?php endif; ?>
				<span class="iu-mu-last-scan"><?php echo $iu_dup_last; // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped -- built from esc_html above. ?></span>

				<div class="iu-mu-progress" style="display:none;">
					<div class="iu-mu-progress-bar"><span class="iu-mu-progress-fill" style="width:0;"></span></div>
					<p class="iu-mu-progress-msg"></p>
				</div>
			</div>
		</div>

		<?php if ( ! empty( $results['summary']['truncated'] ) ) : ?>
			<div class="iu-mu-panel">
				<p class="iu-dup-truncated">
					<?php
					printf(
						/* translators: 1: shown group count, 2: total group count. */
						esc_html__( 'Showing the first %1$s of %2$s duplicate groups. Resolve or ignore some, then rescan to see the rest.', 'infinite-uploads' ),
						number_format_i18n( count( $iu_dup_groups ) ),
						number_format_i18n( (int) $results['summary']['total_groups'] )
					);
					?>
				</p>
			</div>
		<?php endif; ?>

		<?php if ( empty( $iu_dup_groups ) && ! empty( $results['last_ran'] ) ) : ?>
			<div class="iu-mu-panel"><p><?php esc_html_e( 'No possible duplicates found. Nice and tidy.', 'infinite-uploads' ); ?></p></div>
		<?php endif; ?>

		<?php
		foreach ( $iu_dup_groups as $iu_g ) :
			$iu_is_ignored = isset( $ignored[ $iu_g['key'] ] );
			if ( $iu_is_ignored && ! $show_ignored ) {
				continue;
			}
			?>
			<div class="iu-mu-panel iu-dup-group <?php echo $iu_is_ignored ? 'iu-dup-ignored' : ''; ?>" data-key="<?php echo esc_attr( $iu_g['key'] ); ?>">
				<div class="iu-dup-group-head">
					<span class="iu-dup-badge iu-dup-<?php echo esc_attr( $iu_g['label'] ); ?>"><?php echo esc_html( $iu_dup_label( $iu_g['label'] ) ); ?></span>
					<span class="iu-dup-reason"><?php echo esc_html( $iu_g['reason'] ); ?></span>
					<?php if ( $iu_is_ignored ) : ?><span class="iu-dup-ignored-tag"><?php esc_html_e( 'Ignored', 'infinite-uploads' ); ?></span><?php endif; ?>
					<span class="iu-dup-actions">
						<?php if ( 'yes' === $settings['allow_exact_hash'] ) : ?>
							<button type="button" class="iu-btn iu-btn-ghost iu-dup-verify" data-key="<?php echo esc_attr( $iu_g['key'] ); ?>"><?php esc_html_e( 'Verify Exact Match', 'infinite-uploads' ); ?></button>
						<?php endif; ?>
						<button type="button" class="iu-btn iu-btn-ghost iu-dup-ignore" data-key="<?php echo esc_attr( $iu_g['key'] ); ?>" data-ignored="<?php echo $iu_is_ignored ? '0' : '1'; ?>"><?php echo $iu_is_ignored ? esc_html__( 'Unignore', 'infinite-uploads' ) : esc_html__( 'Ignore Group', 'infinite-uploads' ); ?></button>
					</span>
				</div>
				<div class="iu-dup-verify-result" style="display:none;"></div>
				<div class="iu-dup-members">
					<?php foreach ( $iu_g['members'] as $iu_m ) : ?>
						<?php
						$iu_edit     = get_edit_post_link( (int) $iu_m['id'] );
						$iu_dims     = ( $iu_m['width'] && $iu_m['height'] ) ? ( (int) $iu_m['width'] . ' × ' . (int) $iu_m['height'] ) : '—';
						$iu_size     = $iu_m['size'] ? size_format( (int) $iu_m['size'] ) : '—';
						$iu_uploader = isset( $uploaders[ (int) $iu_m['id'] ] ) ? $uploaders[ (int) $iu_m['id'] ] : __( 'Unknown', 'infinite-uploads' );
						?>
						<div class="iu-dup-member">
							<div class="iu-dup-thumb"><?php echo wp_get_attachment_image( (int) $iu_m['id'], array( 70, 70 ), true ); // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped -- core returns safe markup. ?></div>
							<div class="iu-dup-meta">
								<strong><?php echo $iu_edit ? '<a href="' . esc_url( $iu_edit ) . '">' . esc_html( $iu_m['name'] ) . '</a>' : esc_html( $iu_m['name'] ); // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped -- escaped inline. ?></strong>
								<span class="iu-dup-sub">
									<?php
									printf(
										/* translators: 1: attachment ID, 2: dimensions, 3: file size, 4: upload date, 5: uploader name. */
										esc_html__( 'ID %1$s · %2$s · %3$s · uploaded %4$s by %5$s', 'infinite-uploads' ),
										(int) $iu_m['id'],
										esc_html( $iu_dims ),
										esc_html( $iu_size ),
										esc_html( $iu_m['date'] ),
										esc_html( $iu_uploader )
									);
									?>
								</span>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>

		<details class="iu-mu-panel iu-dup-settings">
			<summary><?php esc_html_e( 'Duplicate detection settings', 'infinite-uploads' ); ?></summary>
			<p>
				<label><input type="checkbox" id="iu-dup-set-weak" <?php checked( 'yes', $settings['show_weak'] ); ?> /> <?php esc_html_e( 'Show weak matches', 'infinite-uploads' ); ?></label>
			</p>
			<p>
				<label><input type="checkbox" id="iu-dup-set-hash" <?php checked( 'yes', $settings['allow_exact_hash'] ); ?> /> <?php esc_html_e( 'Allow exact match verification', 'infinite-uploads' ); ?></label>
			</p>
			<p>
				<label><?php esc_html_e( 'Batch size', 'infinite-uploads' ); ?> <input type="number" id="iu-dup-set-batch" min="20" max="500" value="<?php echo esc_attr( (int) $settings['batch_size'] ); ?>" style="width:90px;" /></label>
			</p>
			<p>
				<button type="button" class="iu-btn iu-btn-primary" id="iu-dup-save-settings"><?php esc_html_e( 'Save settings', 'infinite-uploads' ); ?></button>
				<button type="button" class="iu-btn iu-btn-ghost" id="iu-dup-disable"><?php esc_html_e( 'Disable duplicate detection', 'infinite-uploads' ); ?></button>
				<span id="iu-dup-settings-saved" class="iu-dup-saved" style="display:none;"><?php esc_html_e( 'Saved.', 'infinite-uploads' ); ?></span>
			</p>
		</details>

	<?php endif; ?>

	<div class="iu-mu-footer">
		<strong><?php esc_html_e( 'The Cloud by Infinite Uploads', 'infinite-uploads' ); ?></strong>
	</div>
</div>
