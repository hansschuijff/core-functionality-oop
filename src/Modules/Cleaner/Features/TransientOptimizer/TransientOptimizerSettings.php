<?php
/**
 * Transient Optimizer Dashboard Settings Tab Section.
 *
 * Scans, analyzes, and registers manual purge controls over database transient rows.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Modules\Cleaner\Features\TransientOptimizer
 * @author     Hans Schuijff <@hansschuijff>
 * @license    GPL-2.0-or-later
 * @since      1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Modules\Cleaner\Features\TransientOptimizer;

use DeWittePrins\CoreFunctionality\Admin\Services\Settings\Interfaces\SettingsSectionInterface;
use DeWittePrins\CoreFunctionality\Modules\Cleaner\Features\TransientOptimizer\TransientOptimizer;
use DeWittePrins\CoreFunctionality\Plugin;
use WP_Error;
use Override;

// Import global PHP and WordPress core abstraction functions.
use function __;
use function esc_html__;
use function esc_html;
use function esc_attr;
use function current_time;
use function is_array;
use function sprintf;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TransientOptimizerSettings
 *
 * Implements the database metrics dashboard panel for identifying and flushing
 * expired or orphaned core transient rows clusters.
 *
 * @since 1.0.0
 */
class TransientOptimizerSettings implements SettingsSectionInterface {

	/**
	 * Central root plugin container object.
	 *
	 * @var Plugin
	 */
	private Plugin $plugin;

	/**
	 * TransientOptimizerSettings Constructor.
	 *
	 * @since 1.0.0
	 * @param Plugin $plugin The central framework orchestrator instance.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	#[Override]
	public static function get_id(): string {
		return 'cf-transient-optimizer-settings';
	}

	#[Override]
	public static function get_name(): string {
		return __( 'Transient Optimizer', 'dwp-cf' );
	}

	#[Override]
	public static function get_description(): string {
		return __( 'Analyze your database options overhead. Scan for expired cache entries and orphaned transient keys left behind by deactivated plugins.', 'dwp-cf' );
	}

	#[Override]
	public static function get_icon(): string {
		return 'dashicons-performance';
	}

	#[Override]
	public static function get_stylesheets(): array {
		return array();
	}

	#[Override]
	public static function get_scripts(): array {
		return array();
	}

	/**
	 * Outputs the data metrics collection grid metrics parameters.
	 *
	 * @since  1.0.0
	 * @param  bool $reset_to_defaults Optional. Bypass database values. Default false.
	 * @return void
	 */
	#[Override]
	public function render( bool $reset_to_defaults = false ): void {
		global $wpdb;

		$now_timestamp = current_time( 'timestamp' );

		// 1. SCAN EXPIRED: Count main rows whose timeout markers have passed
		$count_expired_query = $wpdb->prepare(
			"SELECT COUNT(*)
			 FROM {$wpdb->options} o1
			 INNER JOIN {$wpdb->options} o2
				ON o1.option_name = REPLACE(o2.option_name, '_transient_timeout_', '_transient_')
			 WHERE o2.option_name LIKE '_transient_timeout_%%'
			   AND o2.option_value < %d",
			$now_timestamp
		);
		$expired_count = (int) $wpdb->get_var( $count_expired_query );

		// 2. SCAN ORPHANS: Count standalone timeout markers whose main data row has been deleted
		$count_orphans_query = "
			SELECT COUNT(*)
			FROM {$wpdb->options}
			WHERE option_name LIKE '_transient_timeout_%%'
			  AND REPLACE(option_name, '_transient_timeout_', '_transient_') NOT IN (
				  SELECT option_name FROM (SELECT option_name FROM {$wpdb->options}) as sub
			  )";
		$orphans_count = (int) $wpdb->get_var( $count_orphans_query );

		$total_garbage_rows = $expired_count + $orphans_count;

		// UI Presentation Parameters Layout
		$lbl_status   = esc_html__( 'Database Cache Status Analysis', 'dwp-cf' );
		$lbl_expired  = esc_html__( 'Expired Transients Found', 'dwp-cf' );
		$lbl_orphans  = esc_html__( 'Orphaned Timeout Markers', 'dwp-cf' );
		$lbl_total    = esc_html__( 'Total Removable Row Overhead', 'dwp-cf' );
		$lbl_healthy  = esc_html__( 'Database cache is in optimal health! No transient clutter detected.', 'dwp-cf' );
		$lbl_warning  = esc_html__( 'Database clutter detected. Use the manual optimization trigger below to clean the option rows.', 'dwp-cf' );

		echo '<div class="dwp-transient-metrics-panel" style="margin-top: 15px;">';
		echo '<h4 style="margin: 0 0 12px 0; color: #1e293b; font-size: 14px; font-weight: 600;">' . $lbl_status . '</h4>';

		if ( 0 === $total_garbage_rows ) {
			echo '<div style="background: #f0fdf4; border-left: 4px solid #16a34a; padding: 12px 15px; color: #14532d; font-size: 13px; border-radius: 0 3px 3px 0;">' . $lbl_healthy . '</div>';
		} else {
			echo '<div style="background: #fffbeb; border-left: 4px solid #d97706; padding: 12px 15px; color: #78350f; font-size: 13px; border-radius: 0 3px 3px 0; margin-bottom: 15px;">' . $lbl_warning . '</div>';
		}

		echo '<div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 15px;">';

		// Card 1: Expired Rows
		echo '<div style="flex: 1; min-width: 180px; background: #fff; border: 1px solid #cbd5e1; padding: 15px; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">';
		echo '<span style="display: block; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase;">' . $lbl_expired . '</span>';
		echo '<strong style="display: block; font-size: 24px; color: #1e293b; margin-top: 5px;">' . $expired_count . '</strong>';
		echo '</div>';

		// Card 2: Orphaned Rows
		echo '<div style="flex: 1; min-width: 180px; background: #fff; border: 1px solid #cbd5e1; padding: 15px; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">';
		echo '<span style="display: block; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase;">' . $lbl_orphans . '</span>';
		echo '<strong style="display: block; font-size: 24px; color: #1e293b; margin-top: 5px;">' . $orphans_count . '</strong>';
		echo '</div>';

		// Card 3: Combined Overhead
		$total_color = $total_garbage_rows > 0 ? '#b91c1c' : '#1e293b';
		echo '<div style="flex: 1; min-width: 180px; background: #fff; border: 1px solid #cbd5e1; padding: 15px; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border-right: 4px solid ' . $total_color . ';">';
		echo '<span style="display: block; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase;">' . $lbl_total . '</span>';
		echo '<strong style="display: block; font-size: 24px; color: ' . $total_color . '; margin-top: 5px;">' . $total_garbage_rows . '</strong>';
		echo '</div>';

		echo '</div></div>';
	}

	#[Override]
	public function validate( array $posted_data ): bool|WP_Error {
		return true;
	}

	/**
	 * Intercepts manual form post actions to execute synchronous optimization routines.
	 *
	 * @since  1.0.0
	 * @param  array $posted_data The raw form context parameters forwarder.
	 * @return string|bool        Localized success notification string text, false otherwise.
	 */
	#[Override]
	public function save( array $posted_data ): string|bool {
		if ( ! isset( $posted_data['dwp_cf_transient_purge_execute'] ) ) {
			return true;
		}

		// FIXED INTERACTION POOL: Retrieve the active running singleton straight from the Kernel instance vault
		$optimizer_feature = $this->plugin->kernel->get_instance( TransientOptimizer::class );

		if ( ! $optimizer_feature instanceof TransientOptimizer ) {
			return new WP_Error( 'missing_feature', __( 'Critical Error: Transient Optimizer feature instance not loaded by Kernel.', 'dwp-cf' ) );
		}

		$rows_scrubbed = $optimizer_feature->execute_bulk_transient_purge();

		return sprintf(
			/* translators: %d: total records deleted out of the database */
			__( 'Database cleanup successful! Atomic sweep completely purged %d expired option parameters rows.', 'dwp-cf' ),
			$rows_scrubbed
		);
	}

	/**
	 * Supplies the dedicated optimization execution action button block to the factory footer container.
	 *
	 * @since  1.0.0
	 * @return array<string, string> Keyed HTML code block mapping elements.
	 */
	#[Override]
	public function get_action_buttons(): array {
		$btn_text = esc_attr__( 'Optimize Transient Cache Now', 'dwp-cf' );

		return array(
			'purge_transients' => <<<HTML
				<button type="submit" name="dwp_cf_transient_purge_execute" value="1" class="button button-primary" style="height: 35px; background: #dc2626; border-color: #b91c1c; box-shadow: none;">
					⚡ {$btn_text}
				</button>
			HTML,
		);
	}
}
