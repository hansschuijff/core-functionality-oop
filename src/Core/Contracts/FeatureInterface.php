<?php
/**
 * Structural Contract for Micro-Features and Contextual Sub-Modules.
 *
 * @package DeWittePrins\Core\Contracts
 * @since   4.0.0
 */

namespace DeWittePrins\Core\Contracts;

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
		 * Retrieves the unique identifier key for automated dashboard mapping.
		 *
		 * Used directly by the options configuration builder to render management fields.
		 * Example: 'tec_remove_comment_support'
		 *
		 * @since 4.0.0
		 * @return string Unique snake_case alphanumeric setting identifier token.
		 */
		public function get_id(): string;

		/**
		 * Requests additional structural dependencies unique to this specific functionality.
		 *
		 * Merged recursively with the parent module checks to perform a combined validation pass.
		 * Example: array( 'and' => array( 'event_tickets_plus' ) )
		 *
		 * @since 4.0.0
		 * @return array Multi-dimensional array tracking environmental requirements.
		 */
		public function get_preflight_checks(): array;

		/**
		 * Initiates the specific filters, action hooks, and layout alterations.
		 *
		 * Executed exclusively by the central loader orchestrator once the feature's
		 * combined pre-flight evaluation and database activation conditions clear successfully.
		 *
		 * @since 4.0.0
		 * @return void
		 */
		public function launch(): void;
	}
}
