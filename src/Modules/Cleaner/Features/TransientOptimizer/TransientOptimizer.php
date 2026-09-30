<?php
/**
 * Automated Database Transient Optimizer Feature.
 *
 * Sweeps expired and orphaned WordPress transients clusters using atomic SQL bulk actions.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Modules\Cleaner\Features\TransientOptimizer
 * @author     Hans Schuijff <@hansschuijff>
 * @license    GPL-2.0-or-later
 * @since      1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Modules\Cleaner\Features\TransientOptimizer;

use DeWittePrins\CoreFunctionality\Interfaces\FeatureInterface;
use DeWittePrins\CoreFunctionality\Interfaces\ModuleInterface;
use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\Plugin;
use Override;

// Import global PHP and WordPress core abstraction functions.
use function __;
use function wp_next_scheduled;
use function wp_schedule_event;
use function wp_clear_scheduled_hook;
use function current_time;
use function add_action;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TransientOptimizer
 *
 * Orchestrates the automated cron scheduling lifecycle and heavy bulk DB pruning routines.
 *
 * @since 1.0.0
 */
class TransientOptimizer implements FeatureInterface {

	/**
	 * The unique identification hook string handle mapped for cron event triggers.
	 *
	 * @var string
	 */
	private string $cron_hook_identifier = 'dwp_cf_cleaner_transient_cleanup_cron';

	#[Override]
	public static function get_id(): string {
		return 'cleaner_transient_optimizer';
	}

	#[Override]
	public static function get_name(): string {
		return __( 'Transient Optimizer Feature', 'dwp-cf' );
	}

	#[Override]
	public static function get_description(): string {
		return __( 'Monitors, indexes, and flushes expired or orphaned core transients clusters using automated background crons to optimize the options table.', 'dwp-cf' );
	}

	#[Override]
	public static function get_dependencies(): string|array {
		return array();
	}

	#[Override]
	public static function uses_features(): array {
		return array();
	}

	/**
	 * TransientOptimizer Constructor.
	 *
	 * @since 1.0.0
	 * @param ModuleInterface $module Parent module context reference.
	 */
	public function __construct(
		private readonly ModuleInterface $module,
		private readonly Plugin $plugin
	) {}

	#[Override]
	public static function get_orientation(): Orientation {
		return Orientation::BOTH;
	}

	#[Override]
	public function launch(): void {
		// Register the settings page section.
		$this->register_settings_section();

		// Register the background asynchronous cleanup action worker
		add_action( $this->cron_hook_identifier, array( $this, 'execute_bulk_transient_purge' ) );

		// Establish safety schedule gates: Hook a weekly optimization sweep event if currently vacant
		if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'weekly', $this->cron_hook_identifier );
		}
	}

	/**
	 * Register the settings section of this feature.
	 *
	 * @return void
	 */
	private function register_settings_section() {
		// Securely register the configuration panel into the host page factory autonomously.
		add_action(
			'dwp_cf_register_settings_sections',
			function( $settings_factory ) {
				$settings_factory->register_section( TransientOptimizerSettings::class );
			}
		);
	}

	/**
	 * Performs a hard database sweep query executing garbage collection over expired options.
	 *
	 * Purges database rows and forcefully invalidates object caches to maintain real-time sync.
	 *
	 * @since  1.0.0
	 * @return int Total combined records successfully scrubbed from the options table.
	 */
	public function execute_bulk_transient_purge(): int {
		global $wpdb;

		$now_timestamp = (int) current_time( 'timestamp' );

		// 1. SELECT EXPIRED NAMES: Fetch the exact option names whose timeout markers have genuinely lapsed
		$expired_transients_query = $wpdb->prepare(
			"SELECT option_name
			 FROM {$wpdb->options}
			 WHERE option_name LIKE '_transient_timeout_%%'
			   AND CAST(option_value AS UNSIGNED) < %d",
			$now_timestamp
		);

		$expired_timeouts = $wpdb->get_col( $expired_transients_query );

		if ( empty( $expired_timeouts ) || ! is_array( $expired_timeouts ) ) {
			$expired_timeouts = array();
		}

		$deleted_rows = 0;

		// 2. SQL TRANSACTION 1: Purge timeout and data rows in bulk
		if ( ! empty( $expired_timeouts ) ) {
			$option_names_to_delete = array();
			foreach ( $expired_timeouts as $timeout_name ) {
				$option_names_to_delete[] = $timeout_name;
				$option_names_to_delete[] = str_replace( '_transient_timeout_', '_transient_', $timeout_name );

				// CLEAN IN-MEMORY RUNTIME CACHE: Forcefully clean individual options cache paths
				$transient_pure_key = str_replace( '_transient_timeout_', '', $timeout_name );
				wp_cache_delete( $transient_pure_key, 'transient' );
			}

			$format_string = implode( ',', array_fill( 0, count( $option_names_to_delete ), '%s' ) );
			$delete_query  = $wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name IN ($format_string)",
				$option_names_to_delete
			);

			$result = $wpdb->query( $delete_query );
			if ( $result ) {
				$deleted_rows += (int) $result;
			}
		}

		// 3. SQL TRANSACTION 2: Scrub standalone orphaned timeouts
		$purge_orphans_query = "
			DELETE FROM {$wpdb->options}
			WHERE option_name LIKE '_transient_timeout_%%'
			  AND REPLACE(option_name, '_transient_timeout_', '_transient_') NOT IN (
				  SELECT option_name FROM (SELECT option_name FROM {$wpdb->options}) as sub
			  )";

		$orphan_result = $wpdb->query( $purge_orphans_query );
		if ( $orphan_result ) {
			$deleted_rows += (int) $orphan_result;
		}

		// 4. GLOBAL OBJECT CACHE FLUSH: Force Redis/Memcached/APCu to instantly drop stored transients
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}

		return $deleted_rows;
	}

	/**
	 * De-registers scheduled events upon safe component dismantling lifecycles.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function deactivate(): void {
		wp_clear_scheduled_hook( $this->cron_hook_identifier );
	}

	#[Override]
	public function get_module(): ModuleInterface {
		return $this->module;
	}
}
