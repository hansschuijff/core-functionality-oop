<?php
/**
 * De Witte Prins Core Functionality Module Loader Orchestrator.
 *
 * @package DeWittePrins\Core
 * @since   4.0.0
 */

namespace DeWittePrins\Core;

use DeWittePrins\Core\Contracts\ModuleInterface;
use DeWittePrins\Core\Contracts\FeatureInterface;
use DeWittePrins\Core\Plugin;
use DeWittePrins\Core;
use DeWittePrins\Environment;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class ModuleLoader
 *
 * Responsible for the sequential initialization of framework modules and features.
 * Validates dependencies via the Environment engine before loading any components.
 *
 * @since 4.0.0
 */
class ModuleLoader {

	/**
	 * Central root plugin container object.
	 *
	 * @since 4.0.0
	 * @var \DeWittePrins\Core\Plugin
	 */
	private Plugin $plugin;

	/**
	 * Active feature configuration settings pulled from the WordPress options database.
	 *
	 * @since 4.0.0
	 * @var array
	 */
	private array $enabled_features_settings = array();

	/**
	 * Dynamically configured execution timing rules (hook and execution priority).
	 *
	 * @since 4.0.0
	 * @var array
	 */
	private array $load_timing = array();

	/**
	 * ModuleLoader constructor.
	 *
	 * Sets up default timing and hooks the core initialization into plugins_loaded.
	 *
	 * @since 4.0.0
	 * @param \DeWittePrins\Core\Plugin $plugin The global core plugin metadata shell.
	 * @see add_action()
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		// Fetch dynamic core loading configuration with a reliable system fallback.
		$this->load_timing = get_option( 'dwp_flight_timing', array(
			'hook'     => 'init',
			'priority' => 11,
		));

		// Hook core setup immediately during the standard WordPress plugin loading sequence.
		add_action( 'plugins_loaded', array( $this, 'init' ), 1 );
	}

	/**
	 * Prepares core settings and schedules the final module execution.
	 *
	 * @since 4.0.0
	 * @return void
	 */
	public function init(): void {
		$this->enabled_features_settings = get_option( 'dwp_enabled_features', array() );

		// Load the central mapping file containing all active module classes.
		$modules_config = include dirname( dirname( __DIR__ ) ) . '/config/modules.php';

		$hook     = $this->load_timing['hook'];
		$priority = $this->load_timing['priority'];

		// Initialize and register the environment mapping utility early.
		$mapper = new \DeWittePrins\Core\Services\EnvironmentMapper();
		Core::register_service( 'platform_mapper', $mapper );

		// Schedule the final module evaluation and loading sequence.
		add_action( $hook, function() use ( $modules_config ) {
			Core::set_cf_ready();
			$this->load_active_modules( $modules_config );
		}, $priority );
	}

	/**
	 * Evaluates environmental readiness and loops through core components to load them.
	 *
	 * @since 4.0.0
	 * @param array $modules_config Registered module class keys and targets.
	 * @return void
	 * @see do_action()
	 */
	private function load_active_modules( array $modules_config ): void {
		foreach ( $modules_config as $module_id => $module_class ) {
			if ( ! class_exists( $module_class ) ) {
				continue;
			}

			// Pass the root plugin object via Dependency Injection to the module.
			/** @var ModuleInterface $module */
			$module      = new $module_class( $this->plugin );
			$base_checks = $module->get_preflight_checks();

			// Ask the decoupled Environment engine if the context is ready for this module.
			if ( ! Environment::is_ready( $base_checks ) ) {
				continue; // Skip the module if requirements are not met.
			}

			// Launch the module base layer.
			$module->launch();
			Core::register_active_module( $module_id );

			// Process all micro-features bound to this active module.
			foreach ( $module->get_features() as $feature_class ) {
				if ( ! class_exists( $feature_class ) ) {
					continue;
				}

				/** @var FeatureInterface $feature */
				$feature    = new $feature_class( $module );
				$feature_id = $feature->get_id();

				// Skip if the user has disabled this specific feature toggle in the admin panel.
				if ( empty( $this->enabled_features_settings[ $feature_id ] ) ) {
					continue;
				}

				// Merge parent module requirements with feature requirements.
				$combined_checks = array_merge_recursive( $base_checks, $feature->get_preflight_checks() );

				// Activate the feature if environment readiness checks clear.
				if ( Environment::is_ready( $combined_checks ) ) {
					$feature->launch();
					// Schoon, helder en zonder verwarring met de echte file-logger!
					Core::register_active_feature( $module_id, $feature_id );
				}
			}
		}

		// BROADCAST VOLUIT: Signal that all modules are ready.
		do_action( 'dwp_core_functionality_ready' );
	}
}
