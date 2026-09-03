<?php
/**
 * Structural Contract for Core Application Modules.
 *
 * @package DeWittePrins\CoreFunctionality\Contracts
 * @since   4.0.0
 */

namespace DeWittePrins\CoreFunctionality\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! interface_exists( __NAMESPACE__ . '\ModuleInterface' ) ) {
	/**
	 * Interface ModuleInterface
	 *
	 * Enforces the required boot cycle methodology for master application modules.
	 * Facilitates passive pre-flight requirement checking and sub-feature mapping.
	 *
	 * @since 4.0.0
	 */
	interface ModuleInterface {


		/**
		 * Requests the structural environment requirements for the module base layer.
		 *
		 * Must return a multi-dimensional array mapping an 'and' or 'or' matrix
		 * of clean snake_case system environment identifiers.
		 * Example: array( 'and' => array( 'woocommerce', 'production_environment' ) )
		 *
		 * @since 4.0.0
		 * @return array Multi-dimensional array tracking environmental requirements.
		 */
		public function get_preflight_checks(): array;

		/**
		 * Gathers the fully qualified class names of encapsulated micro-features.
		 *
		 * The central ModuleLoader iterates through this collection to inspect
		 * and deploy sub-features individually based on user preferences.
		 * Example: array( Features\RemoveCommentSupport::class, Features\PreventPostHijacking::class )
		 *
		 * @since 4.0.0
		 * @return array List of fully qualified feature class strings.
		 */
		public function get_features(): array;

		/**
		 * Initiates the module base configuration layer.
		 *
		 * Executed exclusively by the central loader orchestrator once the module's
		 * pre-flight validation matrices return an absolute true state.
		 *
		 * @since 4.0.0
		 * @return void
		 */
		public function launch(): void;


		/**
		 * Resolves the central plugin metadata shell core context.
		 *
		 * @return \DeWittePrins\CoreFunctionality\Plugin The active verkeersleider instance.
		 */
		public function get_plugin(): \DeWittePrins\CoreFunctionality\Plugin;
	}
}
