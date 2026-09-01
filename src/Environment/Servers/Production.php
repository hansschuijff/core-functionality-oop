<?php
/**
 * Production Environment Verification Guard.
 *
 * @package DeWittePrins\Environment\Server
 * @since   4.0.0
 */

namespace DeWittePrins\Environment\Servers;

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
class Production {

	/**
	 * Checks the native WordPress environment status metrics.
	 *
	 * @since 4.0.0
	 * @return bool True if production or default fallback state, false otherwise.
	 */
	public function is_available(): bool {
		if ( function_exists( 'wp_get_environment_type' ) ) {
			return 'production' === wp_get_environment_type();
		}
		return true; // Safe fail-safe default.
	}
}
