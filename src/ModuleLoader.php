<?php
/**
 * Core Framework Module Loader.
 *
 * @package DeWittePrins\CoreFunctionality
 * @since   4.0.0
 */

namespace DeWittePrins\CoreFunctionality;

use DeWittePrins\CoreFunctionality\Contracts\ModuleInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ModuleLoader
 *
 * Responsible solely for translating the module configuration array into
 * active object instances. Contains zero lifecycle hooks or execution rules.
 */
class ModuleLoader {

	/**
	 * The central plugin orchestrator instance.
	 *
	 * @var Plugin
	 */
	private Plugin $plugin;

	/**
	 * ModuleLoader constructor.
	 *
	 * @param Plugin $plugin De globale verkeersleider context.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Instantiates registered module classes safely from the configurations.
	 *
	 * @return ModuleInterface[] Array of fully instantiated modules.
	 */
	public function load_modules(): array {
		$registered_modules = $this->plugin->get_config( 'modules' );
		$active_instances   = array();

		if ( ! is_array( $registered_modules ) ) {
			return $active_instances;
		}

		foreach ( $registered_modules as $module_id => $class_name ) {
			if ( ! class_exists( $class_name ) ) {
				continue;
			}

			$module = new $class_name( $this->plugin );

			if ( $module instanceof ModuleInterface ) {
				$active_instances[ $module_id ] = $module;
			}
		}

		return $active_instances;
	}
}
