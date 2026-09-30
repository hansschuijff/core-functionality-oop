<?php
/**
 * Plugin Framework Integrity Watchdog Service.
 *
 * @package DeWittePrins\CoreFunctionality\Services
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use DeWittePrins\CoreFunctionality\Plugin;
use DeWittePrins\CoreFunctionality\Notifiers\Notices;

/**
 * Class PluginIntegrityWatch
 *
 * Audits framework architecture rules, aggregates structural violations,
 * logs issues directly to the server, and triggers graceful deactivation notices.
 *
 * @since 1.0.0
 */
class PluginIntegrityWatch {

		private Settings $settings;
		private Notices $notices;

	/**
	 * Multi-dimensional registry tracking structural framework violations.
	 *
	 * @var array
	 */
	private array $violations = array(
		'non_existent_modules'  => array(),
		'non_existent_features' => array(),
		'duplicate_features'    => array(),
	);

	/**
	 * PluginIntegrityWatch Constructor.
	 *
	 * @since 1.0.0
	 * @param Settings $settings The central settings repository engine.
	 * @param Notices $notices The centralized notification service.
	 */
	public function __construct(
		private readonly Plugin $plugin,
	) {
		$this->settings = $this->plugin->settings;
		$this->notices  = $this->plugin->notices;
	}

	/**
	 * Executes the complete framework architectural audit layer.
	 *
	 * @since  1.0.0
	 * @return bool True if integrity is cleared, false if structural violations occurred.
	 */
	public function monitor_integrity(): bool {
		$this->duplicate_id_check();

		$active_violations = array_filter( $this->violations );
		if ( empty( $active_violations ) ) {
			return true; // Alles is loepzuiver!
		}

		// LOGGEN: Schrijf alle details direct en gestructureerd weg naar de server log!
		$this->write_detailed_error_log();

		// NOTIFY: Meld de nette waarschuwing aan bij de berichtendienst (Geen wp_die!).
		$this->notices->add(
			'error',
			'<strong>Core Functionality Beheer Gedeactiveerd</strong><br />' .
			'Vanwege een interne structuurfout is de plugin uit voorzorg tijdelijk uitgeschakeld om de stabiliteit van uw website te garanderen. ' .
			'De exacte technische details zijn weggeschreven naar de server logs. Controleer uw logbestanden om dit te herstellen.',
			false // Niet wegdrukbaar, want de code is immers corrupt en deactiveert.
		);

		return false;
	}

	/**
	 * Audits the active framework tree mapping for validation, structural placement, and type stability.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	private function duplicate_id_check(): void {
		$registered_modules = $this->plugin->components_register->get();

		if ( ! is_array( $registered_modules ) ) {
			return;
		}

		$tracked_features = array();

		foreach ( $registered_modules as $module_fqcn => $features_fqcn ) {

			$module_id = $module_fqcn::get_id();

			foreach ( $features_fqcn as $feature_fqcn ) {

				$compound_key = $module_id . '-' . $feature_fqcn::get_id();

				if ( array_key_exists( $compound_key, $tracked_features ) ) {
					$this->violations['duplicate_features'][] = array(
						'id'       => $compound_key,
						'original' => $tracked_features[ $compound_key ],
						'conflict' => $feature_fqcn,
					);
				}

				$tracked_features[ $compound_key ] = $feature_fqcn;
			}
		}
	}

	/**
	 * Writes comprehensive structural audit logs to the native PHP error_log.
	 *
	 * @since 1.0.0
	 */
	private function write_detailed_error_log(): void {
		$prefix = '[CoreFunctionality Integrity Watchdog] 🛑 ';

		// Is now being prevented by ComponentsRegister
		if ( ! empty( $this->violations['non_existent_modules'] ) ) {
			foreach ( $this->violations['non_existent_modules'] as $mod ) {
				error_log( $prefix . 'Missing/Mismatched module class found in index: ' . $mod ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}

		// Is now being prevented by ComponentsRegister
		if ( ! empty( $this->violations['non_existent_features'] ) ) {
			foreach ( $this->violations['non_existent_features'] as $feat ) {
				error_log( $prefix . 'Missing feature class inside module ' . $feat['module'] . ': ' . $feat['class'] ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}

		if ( ! empty( $this->violations['duplicate_features'] ) ) {
			foreach ( $this->violations['duplicate_features'] as $dup ) {
				error_log( $prefix . 'Duplicate feature identifier detected! Key: "' . $dup['id'] . '" claimed by both: [' . $dup['original'] . '] and [' . $dup['conflict'] . ']' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}
	}
}
