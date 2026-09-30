<?php
/**
 * Core Framework Dependency Evaluation Contract.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Environment\Interfaces
 * @author     Hans Schuijff <@hansschuijff>
 * @license    GPL-2.0
 * @since      1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Environment\Interfaces;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface DependencyInterface
 *
 * Enforces evaluation criteria to verify if a required external asset is available.
 *
 * @since 1.0.0
 */
interface DependencyInterface {

	/**
	 * Unique identifier token for this specific dependency resource.
	 *
	 * @since 1.0.0
	 * @return string The unique criteria key.
	 */
	public function get_id(): string;

	/**
	 * Evaluates the runtime readiness of the targeted resource.
	 *
	 * @since 1.0.0
	 * @return bool True if the asset is active and operational, false otherwise.
	 */
	public function is_ready(): bool;

	/**
	 * Verifies if the dependency has active proof of life within the runtime environment.
	 *
	 * @since 1.0.0
	 * @return bool True if loaded proof is met, false otherwise.
	 */
	public function has_proof_of_life(): bool;
}
