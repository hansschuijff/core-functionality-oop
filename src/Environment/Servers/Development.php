<?php
/**
 * Development Environment Verification Guard.
 *
 * @package DeWittePrins\Environment\Server
 * @since   4.0.0
 */

namespace DeWittePrins\Environment\Servers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Development
 *
 * Verifies if the active server infrastructure is running in a local or
 * development context using native WordPress environment detection.
 *
 * @since 4.0.0
 */
class Development {

	/**
	 * Checks the native WordPress environment status metrics.
	 *
	 * @since 4.0.0
	 * @return bool True if matching local or development setups, false otherwise.
	 */
	public function is_available(): bool {
		if ( function_exists( 'wp_get_environment_type' ) ) {
			$env = wp_get_environment_type();
			return 'development' === $env || 'local' === $env;
		}
		return false;
	}
}
