<?php
/**
 * Structural Contract for Micro-Features and Contextual Sub-Modules.
 *
 * @package DeWittePrins\CoreFunctionality\Contracts
 * @since   4.0.0
 */

namespace DeWittePrins\CoreFunctionality\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! interface_exists( __NAMESPACE__ . '\FeatureInterface' ) ) {
	/**
	 * Interface FeatureInterface
	 *
	 * Enforces the required toggle, guard, and execution architecture for
	 * small-scale contextual alterations and custom business logic extensions.
	 *
	 * @since 4.0.0
	 */
	interface FeatureInterface {

		/**
		 * Retrieves the absolute, fully qualified system identifier key for this live instance.
		 *
		 * Combines parent module ID with the local feature ID to guarantee absolute uniqueness.
		 * Example: 'ticket_system-pdf_export'
		 *
		 * @since  4.0.0
		 * @return string Unique compound system identifier token.
		 */
		public static function get_id(): string;

		/**
		 * Retrieves the human-readable name of the feature.
		 *
		 * Used directly by the configuration builder to render dashboard labels.
		 *
		 * @since  4.0.0
		 * @return string Feature title.
		 */
		public static function get_name(): string;

		/**
		 * FeatureInterface Constructor.
		 *
		 * Receives its parent module dependency early for runtime contextual reference.
		 *
		 * @since 4.0.0
		 * @param ModuleInterface $module The instantiating parent module object layer.
		 */
		public function __construct( ModuleInterface $module );

		/**
		 * Requests additional structural dependencies unique to this specific functionality.
		 *
		 * Note: Made static so pre-flights can be inspected statically.
		 *
		 * @since  4.0.0
		 * @return array Multi-dimensional array tracking environmental requirements.
		 */
		public static function get_preflight_checks(): array;

		/**
		 * Initiates the specific filters, action hooks, and layout alterations.
		 *
		 * Executed exclusively once user preferences and environments clear validation.
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function launch(): void;
	}
}
