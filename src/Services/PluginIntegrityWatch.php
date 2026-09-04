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

/**
 * Class PluginIntegrityWatch
 *
 * Audits framework architecture rules, aggregates structural violations,
 * logs issues directly to the server, and triggers graceful deactivation notices.
 *
 * @since 1.0.0
 */
class PluginIntegrityWatch {

	/**
	 * Config repository metadata service context.
	 *
	 * @var Config
	 */
	private Config $config;

	/**
	 * Universal administrative notifications registry.
	 *
	 * @var Notice
	 */
	private Notice $notice;

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
	 * @param Config $config The central config repository engine.
	 * @param Notice $notice The centralized notification service.
	 */
	public function __construct( Config $config, Notice $notice ) {
		$this->config = $config;
		$this->notice = $notice;
	}

	/**
	 * Executes the complete framework architectural audit layer.
	 *
	 * @since  1.0.0
	 * @return bool True if integrity is cleared, false if structural violations occurred.
	 */
	public function monitor_integrity(): bool {
		$this->audit_framework_tree();

		$active_violations = array_filter( $this->violations );
		if ( empty( $active_violations ) ) {
			return true; // Alles is loepzuiver!
		}

		// LOGGEN: Schrijf alle details direct en gestructureerd weg naar de server log!
		$this->write_detailed_error_log();

		// NOTIFY: Meld de nette waarschuwing aan bij de berichtendienst (Geen wp_die!).
		$this->notice->add(
			'error',
			'<strong>Core Functionality Beheer Gedeactiveerd</strong><br />' .
			'Vanwege een interne structuurfout is de plugin uit voorzorg tijdelijk uitgeschakeld om de stabiliteit van uw website te garanderen. ' .
			'De exacte technische details zijn weggeschreven naar de server logs. Controleer uw logbestanden om dit te herstellen.',
			false // Niet wegdrukbaar, want de code is immers corrupt en deactiveert.
		);

		return false;
	}

	/**
	 * Audits the full operational module and feature tree against structural requirements.
	 *
	 * @since 1.0.0
	 */
	private function audit_framework_tree(): void {
		$registered_modules = $this->config->get( 'modules' );
		if ( ! is_array( $registered_modules ) ) {
			return;
		}

		$tracked_features = array();

		foreach ( $registered_modules as $module_class ) {
			if ( ! class_exists( $module_class ) ) {
				$this->violations['non_existent_modules'][] = $module_class;
				continue;
			}

			$module_id = $module_class::get_id();
			$features  = $module_class::get_features();

			if ( ! is_array( $features ) ) {
				continue;
			}

			foreach ( $features as $feature_class ) {
				if ( ! class_exists( $feature_class ) ) {
					$this->violations['non_existent_features'][] = array(
						'module' => $module_class,
						'class'  => $feature_class,
					);
					continue;
				}

				$compound_key = $module_id . '-' . $feature_class::get_id();

				if ( array_key_exists( $compound_key, $tracked_features ) ) {
					$this->violations['duplicate_features'][] = array(
						'id'       => $compound_key,
						'original' => $tracked_features[ $compound_key ],
						'conflict' => $feature_class,
					);
				}

				$tracked_features[ $compound_key ] = $feature_class;
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

		if ( ! empty( $this->violations['non_existent_modules'] ) ) {
			foreach ( $this->violations['non_existent_modules'] as $mod ) {
				error_log( $prefix . 'Missing/Mismatched module class found in index: ' . $mod ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}

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
