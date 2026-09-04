<?php
/**
 * Module Interface Contract.
 *
 * @package DeWittePrins\CoreFunctionality\Contracts
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Contracts;

use DeWittePrins\CoreFunctionality\Plugin;
use DeWittePrins\CoreFunctionality\Data\ModuleConfigSchema;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! interface_exists( __NAMESPACE__ . '\ModuleInterface' ) ) {
	/**
	 * Interface ModuleInterface
	 *
	 * Defines the contract for all core modules. Requires static metadata
	 * generation for registry inventory and dynamic activation lifecycles.
	 *
	 * @since 1.0.0
	 */
	interface ModuleInterface {

		/**
		 * Retrieves the unique string identifier for the module.
		 *
		 * @since  1.0.0
		 * @return string Unique module key (slug syntax).
		 */
		public static function get_id(): string;

		/**
		 * Retrieves the human-readable name of the module.
		 *
		 * @since  1.0.0
		 * @return string Module title.
		 */
		public static function get_name(): string;

		/**
		 * Retrieves the contextual description of what the module provides.
		 *
		 * @since  1.0.0
		 * @return string Module description.
		 */
		public static function get_description(): string;

		/**
		 * Gathers the fully qualified class names of encapsulated micro-features.
		 *
		 * The central ModuleLoader iterates through this collection to inspect
		 * and deploy sub-features individually based on user preferences.
		 * Example: array( Features\RemoveCommentSupport::class, Features\PreventPostHijacking::class )
		 *
		 * @since  4.0.0
		 * @return array List of fully qualified feature class strings.
		 */
		public static function get_features(): array;

		/**
		 * Requests the structural environment requirements for the module base layer.
		 *
		 * Must return a multi-dimensional array mapping an 'and' or 'or' matrix
		 * of clean snake_case system environment identifiers.
		 * Example: array( 'and' => array( 'woocommerce', 'production_environment' ) )
		 *
		 * Note: Made static so the loader can run pre-flights before instantiation.
		 *
		 * @since  4.0.0
		 * @return array Multi-dimensional array tracking environmental requirements.
		 */
		public static function get_preflight_checks(): array;

		/**
		 * Defines core permission roles or capabilities needed to launch.
		 *
		 * @since  1.0.0
		 * @return array List of required WordPress capabilities.
		 */
		public static function get_required_capabilities(): array;

		/**
		 * Initiates the module base configuration layer.
		 *
		 * Executed exclusively by the central loader orchestrator once the module's
		 * pre-flight validation matrices return an absolute true state.
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function launch(): void;

		/**
		 * Resolves the central plugin metadata shell core context.
		 *
		 * @since  4.0.0
		 * @return \DeWittePrins\CoreFunctionality\Plugin The active verkeersleider instance.
		 */
		public function get_plugin(): Plugin;

		/**
		 * Defines the settings schema schema mapping for this specific module layer.
		 *
		 * @since  4.0.0
		 * @return ModuleConfigSchema Compiled schema mapping object.
		 */
		public static function get_config_schema(): ModuleConfigSchema;
	}
}
