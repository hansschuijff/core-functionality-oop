<?php
/**
 * The Events Calendar Verification Guard.
 *
 * @package DeWittePrins\CoreFunctionality\Environment\Plugins
 * @since   4.0.0
 */

namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Core;
use DeWittePrins\CoreFunctionality\Contracts\EnvironmentInterface;

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
class TheEventsCalendar implements EnvironmentInterface {

	/**
	 * Unique identifier token for this specific ecosystem component.
	 *
	 * @since 4.0.0
	 * @return string The blueprint identification token.
	 */
	public function get_id(): string {
		return 'the_events_calendar';
	}

	/**
	 * Verifies if the plugin is operational and its functional endpoints exist.
	 *
	 * @since 4.0.0
	 * @return bool True if fully accessible, false otherwise.
	 */
	public function is_ready(): bool {
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

	/**
	 * Returns custom administration bar node attributes for immediate rendering.
	 *
	 * @since 4.0.0
	 * @return array The node data array.
	 */
	public function get_node_data(): array {
		return array(
			'id'     => 'the_events_calendar',
			'title'  => __( 'The Events Calendar', 'core-functionality-oop' ),
			'parent' => 'plugins',
			'href'   => admin_url( 'plugins.php#the-events-calendar' ),
		);
	}
}
