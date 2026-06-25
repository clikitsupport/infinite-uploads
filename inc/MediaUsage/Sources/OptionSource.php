<?php
/**
 * Scans wp_options for attachment references.
 *
 * Covers the Customizer (theme_mods_), widgets (widget_ and sidebars_widgets),
 * site logo, and any plugin/theme option storing an upload URL. Transients are
 * skipped (volatile and high-volume).
 *
 * @package InfiniteUploads
 */

namespace ClikIT\InfiniteUploads\MediaUsage\Sources;

use ClikIT\InfiniteUploads\MediaUsage\Matcher;

/**
 * Class OptionSource
 */
class OptionSource extends Source {

	/**
	 * Volatile / high-volume options that never hold media references. Excluded
	 * to avoid scanning large churny blobs. Transients are excluded separately.
	 */
	const SKIP_NAMES = "'cron','rewrite_rules','iup_media_usage_scan_state','iup_media_usage_debug_log','iup_media_usage_lock'";

	/**
	 * SQL fragment excluding transients and the volatile options above.
	 *
	 * @return string
	 */
	private function exclusion_sql() {
		return "option_name NOT LIKE '\_transient\_%' AND option_name NOT LIKE '\_site\_transient\_%' AND option_name NOT LIKE '\_wc\_session\_%' AND option_name NOT IN ( " . self::SKIP_NAMES . ' )';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_type() {
		return 'option';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_label() {
		return __( 'Checking options & theme settings', 'infinite-uploads' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function count_rows() {
		global $wpdb;

		return (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} WHERE " . $this->exclusion_sql()
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function scan_batch( $after_id, $limit ) {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_id, option_name, option_value FROM {$wpdb->options}
				WHERE option_id > %d AND " . $this->exclusion_sql() . '
				ORDER BY option_id ASC LIMIT %d',
				(int) $after_id,
				(int) $limit
			)
		);

		$last_id   = (int) $after_id;
		$processed = 0;

		foreach ( (array) $rows as $row ) {
			$last_id = (int) $row->option_id;
			$processed++;

			$option_value = (string) $row->option_value;
			$option_name  = (string) $row->option_name;
			$found        = array();

			foreach ( Matcher::find_url_references( $option_value ) as $attachment_id => $matched ) {
				$found[ $attachment_id ] = array( $this->reference_type_for_option( $option_name ), $matched );
			}

			// Bricks global elements/components store builder JSON whose image
			// refs survive only as ids (URLs are re-derived at render time).
			if ( $this->is_bricks_option( $option_name ) ) {
				foreach ( Matcher::find_bricks_references( $option_value ) as $attachment_id => $matched ) {
					if ( ! isset( $found[ $attachment_id ] ) ) {
						$found[ $attachment_id ] = array( 'bricks', $matched );
					}
				}
			}

			// ACF options-page Image/File/Gallery values store bare/serialized
			// attachment ids, with a `_{name}` companion option holding the field
			// key (field_xxxx); the key's type confirms it is a media field.
			if ( '_' !== substr( $option_name, 0, 1 ) && Matcher::looks_like_id_candidate( $option_value ) && $this->is_acf_media_option( $option_name ) ) {
				foreach ( Matcher::find_serialized_id_references( $option_value ) as $attachment_id => $matched ) {
					if ( ! isset( $found[ $attachment_id ] ) ) {
						$found[ $attachment_id ] = array( 'acf', $matched );
					}
				}
			}

			foreach ( $found as $attachment_id => $info ) {
				$this->record(
					$attachment_id,
					array(
						'source_type'    => 'option',
						'source_id'      => 0,
						'source_label'   => $option_name,
						'reference_type' => $info[0],
						'confidence'     => Matcher::CONFIDENCE_HIGH,
						'matched_value'  => $info[1],
					)
				);
			}
		}

		return array(
			'last_id'   => $last_id,
			'processed' => $processed,
			'done'      => ( $processed < (int) $limit ),
		);
	}

	/**
	 * Confirm an option is an ACF options-page Image/File/Gallery field by
	 * reading the `_{name}` companion option (field_xxxx) and resolving it to a
	 * media field type. Non-media ACF fields (e.g. Number) are excluded.
	 *
	 * @param string $option_name Option name.
	 *
	 * @return bool
	 */
	private function is_acf_media_option( $option_name ) {
		return Matcher::acf_key_is_media( get_option( '_' . $option_name ) );
	}

	/**
	 * Whether an option holds Bricks global builder data (elements/components),
	 * which can embed image references as bare attachment ids.
	 *
	 * @param string $option_name Option name.
	 *
	 * @return bool
	 */
	private function is_bricks_option( $option_name ) {
		return in_array(
			$option_name,
			array( 'bricks_global_elements', 'bricks_components', 'bricks_global_settings', 'bricks_theme_styles' ),
			true
		);
	}

	/**
	 * Map an option name to a friendly reference type.
	 *
	 * @param string $option_name Option name.
	 *
	 * @return string
	 */
	private function reference_type_for_option( $option_name ) {
		if ( 0 === strpos( $option_name, 'theme_mods_' ) ) {
			return 'customizer';
		}
		if ( 0 === strpos( $option_name, 'widget_' ) || 'sidebars_widgets' === $option_name ) {
			return 'widget';
		}

		return 'option';
	}
}
