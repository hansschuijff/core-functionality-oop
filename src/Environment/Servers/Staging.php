<?php
/**
 * Staging Environment Verification Guard.
 *
 * @package DeWittePrins\Environment\Server
 * @since   4.0.0
 */

namespace DeWittePrins\Environment\Servers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Staging
 *
 * Verifies if the active server infrastructure is running in a testing or
 * staging context using native WordPress environment detection.
 *
 * @since 4.0.0
 */
class Staging {

	/**
	 * Checks the native WordPress environment status metrics.
	 *
	 * @since 4.0.0
	 * @return bool True if matching staging setups, false otherwise.
	 */
	public function is_available(): bool {
		if ( function_exists( 'wp_get_environment_type' ) ) {
			return 'staging' === wp_get_environment_type();
		}
		return false;
	}
}
