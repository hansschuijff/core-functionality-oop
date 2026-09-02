<?php
/**
 * The Events Calendar Verification Guard.
 *
 * @package DeWittePrins\Environment\Plugins
 * @since   4.0.0
 */

namespace DeWittePrins\Environment\Plugins;

use DeWittePrins\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class TheEventsCalendar
 *
 * Validates the runtime availability of the core The Events Calendar environment
 * bypassing empty class hulls by invoking functional endpoint tests.
 *
 * @since 4.0.0
 */
class TheEventsCalendar {

	/**
	 * Verifies if the plugin is operational and its functional endpoints exist.
	 *
	 * @since 4.0.0
	 * @return bool True if fully accessible, false otherwise.
	 */
	public function is_available(): bool {
		$mapper   = Core::get_service( 'environment_mapper' );
		$basename = $mapper ? $mapper->get_basename( 'the_events_calendar' ) : '';

		// CIRCUIT BREAKER: Halt execution instantly if the allocation mapping is missing.
		if ( empty( $basename ) ) {
			return false;
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! is_plugin_active( $basename ) ) {
			return false;
		}

		// Core method verification checks preventing deep autoloader deception.
		return function_exists( 'tribe_get_events' );
	}
}
