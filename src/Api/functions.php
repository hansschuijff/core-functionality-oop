<?php
/**
 * Procedural Shortcut Wrapper Functions inside the API Namespace.
 *
 * @package DeWittePrins\CoreFunctionality\Api
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Api;

use DeWittePrins\CoreFunctionality\Api\CoreFunctionality as CF;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Procedural bridge checking framework core readiness state.
 *
 * Usage: \DeWittePrins\CoreFunctionality\Api\is_ready();
 *
 * @since  1.0.0
 * @return bool True if operational, false otherwise.
 */
function is_ready(): bool {
	return CF::is_ready();
}

/**
 * Procedural bridge to interrogate module activation whitelists.
 *
 * Usage: \DeWittePrins\CoreFunctionality\Api\is_module_active( 'AdminToolbar\AdminToolbar' );
 *
 * @since  1.0.0
 * @param  string $module_class_name FQN class string of the module.
 * @return bool                      True if active, false otherwise.
 */
function is_module_active( string $module_class_name ): bool {
	return CF::is_module_active( $module_class_name );
}

/**
 * Procedural bridge to safely query environment path configurations.
 *
 * Usage: \DeWittePrins\CoreFunctionality\Api\get_info( 'version' );
 *
 * @since  1.0.0
 * @param  string $key Target metadata array key.
 * @return string      Resolved infrastructure string.
 */
function get_info( string $key ): string {
	return CF::get_info( $key );
}
