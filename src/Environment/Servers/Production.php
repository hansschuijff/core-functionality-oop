<?php
/**
 * Production Environment Verification Guard.
 *
 * @package DeWittePrins\CoreFunctionality\Environment\Server
 * @since   4.0.0
 */

namespace DeWittePrins\CoreFunctionality\Environment\Servers;

use DeWittePrins\CoreFunctionality\Contracts\DependencyInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Production
 *
 * Verifies if the active server infrastructure is running in a live production
 * context using native WordPress environment detection.
 *
 * @since 4.0.0
 */
class Production implements DependencyInterface {

	/**
	 * Unique identifier token for this specific ecosystem component.
	 *
	 * @return string The blueprint identification token.
	 */
	public function get_id(): string {
		return 'production_server';
	}

	/**
	 * Checks the native WordPress environment status metrics.
	 *
	 * @since 4.0.0
	 * @return bool True if production or default fallback state, false otherwise.
	 */
	public function is_ready(): bool {
		if ( function_exists( 'wp_get_environment_type' ) ) {
			return 'production' === wp_get_environment_type();
		}
		return true; // Safe fail-safe default.
	}
}
