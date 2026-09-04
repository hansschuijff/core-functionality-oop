<?php
/**
 * Central Module and Feature Lifecycle Loader Service.
 *
 * @package DeWittePrins\CoreFunctionality\Services
 * @since   4.0.0
 */

namespace DeWittePrins\CoreFunctionality\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use DeWittePrins\CoreFunctionality\Plugin;

/**
 * Class ModuleLoader
 *
 * Interrogates the active configuration matrix against database options
 * to dynamically instantiate and launch authorized micro-features.
 *
 * @since 4.0.0
 */
class ModuleLoader {

	/**
	 * Central plugin controller reference.
	 *
	 * @since 4.0.0
	 * @var Plugin
	 */
	private Plugin $plugin;

	/**
	 * Database settings layer storage service reference.
	 *
	 * @since 4.0.0
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * ModuleLoader Constructor.
	 *
	 * @since 4.0.0
	 * @param Plugin   $plugin   The central orchestrator instance.
	 * @param Settings $settings The isolated database storage kluis.
	 */
	public function __construct( Plugin $plugin, Settings $settings ) {
		$this->plugin   = $plugin;
		$this->settings = $settings;
	}

	/**
	 * Scans the registered configuration and dynamically boots active modules and features.
	 *
	 * Enforces strict cascading priorities: parent module state rules over child features [INDEX].
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function launch(): void {
		// Haal de geregistreerde module blauwdrukken op van de schijf via de publieke Config poort.
		$registered_modules = $this->plugin->config->get( 'modules' );

		if ( ! is_array( $registered_modules ) ) {
			return;
		}

		foreach ( $registered_modules as $module_class ) {
			if ( ! class_exists( $module_class ) ) {
				continue;
			}

			$module_id = $module_class::get_id();

			// PIPELINE CHECK 1: Staat de overkoepelende hoofdmodule wel op ACTIEF in de kluis [INDEX]?
			if ( ! $this->settings->is_module_enabled( $module_id ) ) {
				continue; // Sla de complete module en al zijn geneste features geruisloos over [INDEX]!
			}

			// De module mag starten! We initialiseren hem en geven de plugin-core mee.
			$module_instance = new $module_class( $this->plugin );

			// Start de specifieke runtime hooks van de hoofdmodule zelf op.
			$module_instance->launch();

			// PIPELINE CHECK 2: Loop door de ingekapselde features van deze actieve module.
			$features = $module_class::get_features();

			if ( is_array( $features ) ) {
				foreach ( $features as $feature_class ) {
					if ( ! class_exists( $feature_class ) ) {
						continue;
					}

					$feature_id = $feature_class::get_id();

					// PIPELINE CHECK 3: Staat dit specifieke onderdeel aan in de kluis [INDEX]?
					if ( ! $this->settings->is_feature_enabled( $module_id, $feature_id ) ) {
						continue; // Schakel dit onderdeel geruisloos uit en laad de code niet in [INDEX].
					}

					// Het onderdeel mag starten! We geven de zojuist gemaakte ouder-module mee aan zijn constructor.
					$feature_instance = new $feature_class( $module_instance );

					// Activeer de specifieke WordPress filters en acties van de feature.
					$feature_instance->launch();
				}
			}
		}
	}
}
