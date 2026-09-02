<?php
/**
 * Event Tickets Plus Premium Extension Verification Guard.
 *
 * @package DeWittePrins\Environment\Plugins
 * @since   4.0.0
 */

namespace DeWittePrins\Environment;

use DeWittePrins\Core;

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
class EventTicketsPlus {

	/**
	 * Performs runtime active validation tracking on premium parameters.
	 *
	 * @since 4.0.0
	 * @return bool True if accessible and activated, false otherwise.
	 */
	public function is_available(): bool {
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
}
