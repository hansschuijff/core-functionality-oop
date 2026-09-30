<?php
/**
 * Dependency Checker Service Class.
 *
 * @package DeWittePrins\CoreFunctionality\Services
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use DeWittePrins\CoreFunctionality\Diagnostics\Logger;

/**
 * Class InterDependenciesGuard
 *
 * Shared service responsible for filtering and validating components
 * based on their internal inter-feature dependencies. Can be utilized
 * both during runtime (Kernel) and build-time (Settings/Scanner).
 *
 * @since 1.0.0
 */
class InterDependenciesGuard {

	/**
	 * InterDependenciesGuard Constructor.
	 *
	 * @since 1.0.0
	 * @param Logger $logger A flexible diagnostic logger wrapper service.
	 */
	public function __construct( private readonly Logger $logger ) {
	}

	/**
	 * Removes all components from an array of components whose interdependencies are not met.
	 *
	 * @since  1.0.0
	 * @param  array<string, mixed> $components Array with FQCN (classname) as KEY. Value is the object or true.
	 * @return array<string, mixed> The cleaned array containing only valid remaining keys.
	 */
	public function clean_missing_interdependencies( array $components ): array {
		$all_is_well = false;

		// After every removal, we need to recheck the component list,
		// since earlier components may depend on the removed component.
		while ( ! $all_is_well ) {
			// Initialize to prevent static analysis warnings/errors if the array is empty.
			$ready = true;

			foreach ( $components as $feature => $value ) {
				// Ask component what other features it needs and uses.
				$required_features = $feature::uses_features();

				$ready = $this->all_required_features_ready( $components, $feature, $required_features );

				if ( ! $ready ) {
					// The interdependency check of this feature failed, so remove it from $components.
					unset( $components[ $feature ] );
					break;
				}
			}

			if ( $ready || empty( $components ) ) {
				$all_is_well = true;
			}
		}

		return $components;
	}

	/**
	 * Checks if the features a component needs for its proper functioning are available
	 * in the array of active components.
	 *
	 * @since  1.0.0
	 * @param  array         $active_components  An array containing the Fully Qualified Class Names of available components.
	 * @param  string        $current_component  The FQCN key (classname) of the class whose dependencies we are checking.
	 * @param  array<string> $required_features  Array containing class strings (FQCN) required for the proper functioning of current component.
	 * @return bool                             True if all dependent features are available.
	 */
	private function all_required_features_ready( array $active_components, string $current_component, array $required_features ): bool {
		foreach ( $required_features as $required_feature ) {

			if ( ! isset( $active_components[ $required_feature ] ) ) {

				$this->logger->developer_error(
					__METHOD__,
					sprintf(
						'Feature "%s" skipped because required class "%s" is not active or disabled.',
						$current_component,
						$required_feature
					)
				);

				// One or more features are not available.
				return false;
			}
		}

		// All required features are there.
		return true;
	}
}
