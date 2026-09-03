<?php
/**
 * Core Framework Dependency Evaluation Contract.
 *
 * @package DeWittePrins\CoreFunctionality\Contracts
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface DependencyInterface
 *
 * Enforces evaluation criteria to verify if a required external asset is available.
 */
interface DependencyInterface {

	/**
	 * Unique identifier token for this specific dependency resource.
	 *
	 * @return string The unique criteria key.
	 */
	public function get_id(): string;

	/**
	 * Evaluates the runtime readiness of the targeted resource.
	 *
	 * @return bool True if the asset is active and operational, false otherwise.
	 */
	public function is_ready(): bool;
}
