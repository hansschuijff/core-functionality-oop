<?php
/**
 * Staging Environment Verification Guard.
 *
 * @package DeWittePrins\CoreFunctionality\Environment\Server
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Environment\Servers;

use DeWittePrins\CoreFunctionality\Environment\Interfaces\DependencyInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Staging
 *
 * Verifies if the active server infrastructure is running in a testing or
 * staging context using native WordPress environment detection.
 *
 * @since 1.0.0
 */
class Staging implements DependencyInterface {

	/**
	 * Unique identifier token for this specific ecosystem component.
	 *
	 * @return string The blueprint identification token.
	 */
	public function get_id(): string {
		return 'staging_server';
	}

	/**
	 * Checks the native WordPress environment status metrics.
	 *
	 * @since 1.0.0
	 * @return bool True if matching staging setups, false otherwise.
	 */
	public function is_ready(): bool {
		if ( function_exists( 'wp_get_environment_type' ) ) {
			return 'staging' === wp_get_environment_type();
		}
		return false;
	}

	/**
	 * Default fallback proof of life check for plugins.
	 *
	 * Returns true by default. Extended classes should override this to check for
	 * specific classes, functions or constants without triggering autoloaders.
	 *
	 * @since 1.0.0
	 * @return bool Always true unless overridden.
	 */
	public function has_proof_of_life(): bool {
		return true;
	}
}
