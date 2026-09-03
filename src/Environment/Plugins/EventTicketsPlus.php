<?php
/**
 * Event Tickets Plus Premium Extension Verification Guard.
 *
 * @package DeWittePrins\CoreFunctionality\Environment\Plugins
 * @since   4.0.0
 */

namespace DeWittePrins\CoreFunctionality\Environment;

use DeWittePrins\CoreFunctionality\Core;
use DeWittePrins\CoreFunctionality\Contracts\EnvironmentInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class EventTicketsPlus
 *
 * Inspects execution states checking if the premium Event Tickets Plus add-on
 * is fully operational utilizing the translation mapping pipeline.
 *
 * @since 4.0.0
 */
class EventTicketsPlus implements EnvironmentInterface {

	/**
	 * Unique identifier token for this specific ecosystem component.
	 *
	 * @since 4.0.0
	 * @return string The blueprint identification token.
	 */
	public function get_id(): string {
		return 'event_tickets_plus';
	}

	/**
	 * Performs runtime active validation tracking on premium parameters.
	 *
	 * @since 4.0.0
	 * @return bool True if accessible and activated, false otherwise.
	 */
	public function is_ready(): bool {
		$mapper   = Core::get_service( 'environment_mapper' );
		$basename = $mapper ? $mapper->get_basename( 'event_tickets_plus' ) : '';

		// CIRCUIT BREAKER: Avoid empty system lookups if mapping keys are absent.
		if ( empty( $basename ) ) {
			return false;
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( $basename );
	}

	/**
	 * Returns custom administration bar node attributes for immediate rendering.
	 *
	 * @since 4.0.0
	 * @return array The admin toolbar node data.
	 */
	public function get_node_data(): array {
		return array(
			'id'     => 'event_tickets_plus',
			'title'  => __( 'Event Tickets Plus', 'core-functionality-oop' ),
			'parent' => 'plugins',
			'href'   => admin_url( 'plugins.php#event-tickets-plus' ),
		);
	}
}
