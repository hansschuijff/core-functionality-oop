<?php
/**
 * Structural Contract for Micro-Features and Contextual Sub-Modules.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Interfaces
 * @author     Hans Schuijff <@hansschuijff>
 * @license    GPL-2.0-or-later
 * @since      1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Interfaces;

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\Plugin;

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
	 * @since 1.0.0
	 */
	interface FeatureInterface {

		/**
		 * Retrieves the absolute, fully qualified system identifier key for this live instance.
		 *
		 * @since  1.0.0
		 * @return string Unique identifier token.
		 */
		public static function get_id(): string;

		/**
		 * Retrieves the human-readable name of the feature.
		 *
		 * @since  1.0.0
		 * @return string Feature title.
		 */
		public static function get_name(): string;

		/**
		 * Retrieves a short description of the feature.
		 *
		 * @since  1.0.0
		 * @return string Short feature description.
		 */
		public static function get_description(): string;

		/**
		 * FeatureInterface Constructor.
		 *
		 * Receives its parent module dependency early for runtime contextual reference.
		 *
		 * @since 1.0.0
		 * @param ModuleInterface $module The instantiating parent module object layer.
		 * @param Plugin          $plugin The central framework orchestrator core reference.
		 */
		public function __construct( ModuleInterface $module, Plugin $plugin );

		/**
		 * Retrieves feature-specific environment prerequisites.
		 *
		 * @since  1.0.0
		 * @return string|array Required plugins and themes for this feature.
		 */
		public static function get_dependencies(): string|array;

		/**
		 * Requests structural dependencies to other features in order to perform an interdependency check.
		 *
		 * @since  1.0.0
		 * @return array<string> An array containing the Fully Qualified Class Names of other features.
		 */
		public static function uses_features(): array;

		/**
		 * Requests the static usage-target of the feature (Frontend, Admin, Both).
		 *
		 * @since  1.0.0
		 * @return Orientation The static environment context enum.
		 */
		public static function get_orientation(): Orientation;

		/**
		 * Initiates the specific filters, action hooks, and layout alterations.
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function launch(): void;

		/**
		 * Retrieves the parent module instance container shell.
		 *
		 * @since  1.0.0
		 * @return ModuleInterface The active initialized parent module container.
		 */
		public function get_module(): ModuleInterface;
	}
}
